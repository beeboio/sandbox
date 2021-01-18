<?php
namespace Beebo\SocketIO;

use Beebo\Exceptions\SocketNoLongerConnected;
use Beebo\SocketIO\Emitters\Emitter;
use Beebo\SocketIO\Emitters\In;
use Beebo\SocketIO\Emitters\To;
use Beebo\Concerns\Listens;
use Beebo\Concerns\Bootable;
use Beebo\Concerns\Timers;
use BeyondCode\LaravelWebSockets\Apps\App;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as LoopFactory;

/**
 * Class Server
 * @package Beebo\SocketIO\Server
 * @see https://github.com/socketio/socket.io-protocol
 */
class Server implements MessageComponentInterface
{
  use Bootable, Listens, Timers;

  const TRANSPORT_WEBSOCKET = 'websocket';

  const TRANSPORT_POLLING = 'polling';

  /**
   * RFC6455 states that during handshaking, this guid needs
   * to be sha1-hashed with the key sent in the Sec-WebSocket-Key
   * header. My guess is that because I'm only using websocket
   * transport (not polling), this step is being skipped. Either
   * way, it doesn't seem to be necessary ATM.
   */
  const WEBSOCKET_GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

  /**
   * @var float|int Ping timeout, in seconds
   */
  protected $pingTimeout = 5;

  /**
   * @var float|int Ping interval, in seconds
   */
  protected $pingInterval = 25;

  /**
   * @var Collection<Socket>
   */
  protected $sockets;

  /**
   * This loop is not exclusive to this Server; it is the loop
   * that is being used by Ratchet to host all Servers.
   * @var LoopInterface
   */
  private static $loop;

  /**
   * @var string
   */
  static protected $socketClass = Socket::class;

  /**
   * @var string
   */
  static protected $roomClass = Room::class;

  /**
   * @var Collection<Room>
   */
  protected $rooms;

  /**
   * SocketIO constructor.
   */
  public final function __construct()
  {
    $this->sockets = collect([]);

    $this->rooms = collect([]);

    $this->bootIfNotBooted();

    $this->initializeTraits();

    $this->onInitialize();
  }

  public final static function loop()
  {
    return is_null(self::$loop) ? (self::$loop = LoopFactory::create()) : self::$loop;
  }

  // the SocketIO server event API

  public function onInitialize() {}
  public function onAuthenticating(Socket $socket) {}
  public function onConnection(Socket $socket) {}
  public function onDisconnect(Socket $socket) {}
  public function onReconnect(Socket $socket) {}
  public function onClosed(Socket $socket) {}
  public function onFailure(Socket $socket, \Exception $e) {}

  /**
   * @param Request $request
   * @return Response
   */
  public function onPoll(Request $request) {}

  /**
   * @return int|float Ping timeout in seconds
   */
  public final function getPingTimeout()
  {
    return $this->pingTimeout;
  }

  /**
   * @return int|float Ping interval in seconds
   */
  public final function getPingInterval()
  {
    return $this->pingInterval;
  }

  private function setSid(ConnectionInterface $conn, $sid = null)
  {
    $conn->socketId = $sid;
    return $this;
  }

  public final function getSid(ConnectionInterface $conn)
  {
    return $conn->socketId ?? null;
  }

  /**
   * @return Collection
   */
  public final function getAllSockets()
  {
    return $this->sockets;
  }

  /**
   * @param ConnectionInterface $conn
   * @return Socket
   * @throws \Exception
   * @throws \InvalidArgumentException
   */
  protected final function socket(ConnectionInterface $conn)
  {
    if (!$this->getSid($conn)) {
      $this->setSid($conn, Socket::makeId());

      $socket = Socket::make(static::$socketClass, $this, $conn);

      // The concept of WebSocket "apps" comes from Beyond Code's Laravel WebSockets
      // package; a query parameter "app" is used to discover the App, and a reference
      // is attached to the Connection object; this is required by WebSocketsLogger,
      // and without it, the logger will throw error messages.

      if (!$appKey = $socket->query('appKey')) {
        throw new \Exception("App key is missing from request");
      }
      if (!$app = App::findByKey($appKey)) {
        throw new \Exception("No app exists for app key [{$appKey}]");
      }
      $conn->app = $app;

      $this->attach($socket);
    } else {
      $sid = $this->getSid($conn);
      $socket = $this->sockets[$this->getSid($conn)] ?? null;

      if (!$socket) {
        throw new SocketNoLongerConnected($sid);
      }
    }

    return $socket;
  }

  /**
   * @param ConnectionInterface $conn
   * @throws \Exception
   */
  public final function onOpen(ConnectionInterface $conn)
  {
    $socket = $this->authenticate($this->socket($conn));
    $this->attach($socket)->handleOpen();
  }

  /**
   * @param Socket $socket
   * @return Socket
   */
  protected final function authenticate(Socket $socket)
  {
    $this->onAuthenticating($socket);
    return $socket;
  }

  /**
   * @param ConnectionInterface $conn
   * @throws \Exception
   * @return $this
   */
  public final function onClose(ConnectionInterface $conn)
  {
    $this->socket($conn)->offline(true);
  }

  /**
   * @param Socket $socket
   * @return $this
   */
  public final function detach(Socket $socket)
  {
    $this->sockets->forget($socket->getId());
    return $this;
  }

  /**
   * @param Socket $socket
   * @return Socket
   */
  protected final function attach(Socket $socket)
  {
    $this->sockets[$socket->getId()] = $socket;
    return $socket;
  }

  /**
   * @param ConnectionInterface $conn
   * @param \Exception $e
   * @throws \Exception
   */
  public final function onError(ConnectionInterface $conn, \Exception $e)
  {
    try {
      $this->onFailure($this->socket($conn)->handleError($e), $e);
    } catch (SocketNoLongerConnected $e) {
      // this can happen if an Exception is thrown onOpen()
      Log::error($e);
      $conn->close();
    }
  }

  /**
   * @param ConnectionInterface $conn
   * @param MessageInterface $msg
   * @throws \Exception
   */
  public final function onMessage(ConnectionInterface $conn, MessageInterface $msg)
  {
    $this->socket($conn)->handleMessage($msg);
  }

  /**
   * @return Room
   * @throws \Exception;
   */
  public final function makeRoom($roomName)
  {
    if (!$this->rooms->has($roomName)) {
      $this->rooms[$roomName] = Room::make(static::$roomClass, $roomName, $this);
    }
    return $this->rooms[$roomName];
  }

  /**
   * Send a message to the given Socket
   * @param Socket|string $socket
   * @return To
   */
  public function to($socket)
  {
    if (!$socket instanceof Socket) {
      if (!$socket = $this->sockets->get($socketId = $socket->getId())) {
        throw new \InvalidArgumentException("Socket ID is invalid: [{$socketId}]");
      }
    }
    return To::make($this)->to($socket->getId());
  }

  /**
   * Send an event to every Socket in the room
   * @param Room|string $room
   * @return In
   */
  public final function in($room)
  {
     return In::make($this)->in($room);
  }

  /**
   * Send an event to every socket in a specific namespace including the Sender
   * @param string $namespace
   * @return $this
   */
  public final function of($namespace)
  {
    // TODO: use the In emitter
  }

  /**
   * Send an event to call connected clients
   * @return $this
   */
  public final function emit($eventName, ...$data)
  {
    $this->sockets->each->send(Packet::event($eventName, ...$data));
  }

  public final function join(Socket $socket, $roomName)
  {
    $this->makeRoom($roomName)->join($socket);
  }

  public final function leave(Socket $socket, $roomName)
  {
    $this->makeRoom($roomName)->leave($socket);
  }

}