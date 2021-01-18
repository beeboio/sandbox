<?php
namespace Beebo\SocketIO;

use Beebo\Rooms\Personal;
use Illuminate\Validation\ValidationException;
use Validator;
use Beebo\Exceptions\ConnectionException;
use Beebo\Exceptions\PacketTypeInvalid;
use Beebo\SocketIO\Emitters\To;
use Beebo\Concerns\Timers;
use Beebo\Concerns\PingPong;
use Beebo\Concerns\Unique;
use Beebo\WebSocketApp;
use Beebo\Concerns\Listens;
use Beebo\Concerns\Bootable;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use BeyondCode\LaravelWebSockets\QueryParameters;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Ratchet\ConnectionInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Illuminate\Support\Facades\Route as WebRouter;

/**
 * SocketIO server API
 * Class Socket
 * @package Beebo\SocketIO
 */
class Socket
{
  use Unique, Bootable, Listens, PingPong, Timers;

  /**
   * @var Server
   */
  protected $server;

  /**
   * @var ConnectionInterface
   */
  protected $conn;

  /**
   * @var QueryParameters
   */
  protected $query;

  /**
   * @var Collection<Room>
   */
  protected $rooms;


  /**
   * Socket constructor.
   */
  private function __construct() {
    $this->rooms = collect([]);

    $this->bootIfNotBooted();

    $this->initializeTraits();
  }

  /**
   * Socket factory.
   * @param string $socketClass
   * @param Server $server
   * @param ConnectionInterface $conn
   * @return Socket
   * @throws \InvalidArgumentException
   */
  static function make($socketClass, Server $server, ConnectionInterface $conn)
  {
    $socket = new $socketClass;

    if (!$socket instanceof self) {
      throw new \InvalidArgumentException("{$socketClass} is not a " . get_called_class());
    }

    $socket->server = $server;
    $socket->conn = $conn;

    return $socket;
  }

  public function getId()
  {
    return $this->server->getSid($this->getConnection());
  }

  /**
   * @return WebSocketApp
   */
  public function getApp()
  {
    $app = $this->getConnection()->app ?? null;
    if (empty($app)) {
      throw new \Exception("Something is very wrong: the ConnectionInterface reference is missing the \$app property; should be an instance of WebSocketApp");
    }
    return $app;
  }


  /**
   * @param string $roomName
   * @param string|null $roomClass
   * @return $this
   */
  function join($roomName, $roomClass = null)
  {
    $this->getServer()->join($this, $roomName, $roomClass);
    return $this;
  }

  /**
   * @param $roomName
   * @return $this
   */
  function leave($roomName)
  {
    $this->getServer()->leave($this, $roomName);
    return $this;
  }

  /**
   * @param $listener
   */
  function offAny($listener)
  {
    return $this->off(Listener::make($listener, null));
  }

  /**
   * Close the socket connection.
   */
  public function close()
  {
    // TODO: send Close packet

    $this->getConnection()->close();
    return $this->handleClose();
  }

  /**
   * @return null|mixed|QueryParameters
   */
  function query($field = null)
  {
    if (empty($this->query)) {
      $this->query = QueryParameters::create($this->getConnection()->httpRequest);
    }
    return $field ? $this->query->get($field) : $this->query;
  }

  /**
   * @return Server
   */
  public function getServer()
  {
    return $this->server;
  }

  /**
   * @param Server $server
   */
  public function setServer($server)
  {
    $this->server = $server;
    return $this;
  }

  /**
   * @return ConnectionInterface
   */
  public function getConnection()
  {
    return $this->conn;
  }

  /**
   * @param ConnectionInterface $conn
   */
  public function setConnection($conn)
  {
    $this->conn = $conn;
    return $this;
  }

  /**
   * Ping timeout, in seconds
   * @return int|float
   */
  function getPingTimeout()
  {
    return $this->getServer()->getPingTimeout();
  }

  /**
   * Ping interval, in seconds
   * @return int|float
   */
  function getPingInterval()
  {
    return $this->getServer()->getPingInterval();
  }

  /**
   * @return Socket
   */
  public function handleOpen()
  {
    $this->wakeup()->transmit(
      Packet::handshake(
        $this->getId(),
        [ Server::TRANSPORT_WEBSOCKET ],
        $this->getPingTimeout(),
        $this->getPingInterval()
      )
    );

    return $this;
  }

  /**
   * @param Packet $packet
   */
  public function handleConnect(Packet $packet)
  {
    if ($packet->hasNamespace()) {

      // TODO: authorize namespace

      throw (new ConnectionException("Namespaces not yet supported"))
        ->withPacket($packet);
    }

    $this->join($this->getId(), Personal::class)->transmit(
      Packet::connect($this->getId(), value(function() {
        $debug = null;
        if (config('app.debug')) {
          $debug = [
            'm' => memory_get_usage(),
          ];
        }
        return $debug;
      }))
    );

    // keep alive
    $this->every(
      $this->getPingInterval() - 0.1,
      function() {
        $this->keepAlive();
      }
    );

    $this->createdAt = $this->now();
    $this->getServer()->onConnection($this);

    return $this;
  }

  /**
   * Handle events produced by this socket
   * @param Packet $packet
   * @return $this
   * @throws \Exception
   */
  public final function handleEvent(Packet $packet)
  {
    $data = $packet->getData();

    if (empty($data)) {
      throw new \Exception("Can't trigger Event: missing event name");
    }

    $eventName = array_shift($data);

    // TODO: consider moving this to the decoder...
    if ($packet->hasId()) {
      $callback = function(...$data) use ($packet) {
        $this->transmit(Packet::ack($packet, ...$data));
      };
      $packet->setCallback($callback);
    }

    $event = Event::make($eventName, $this, $packet);

    // trigger Server-attached listeners first,
    // which is somewhat confusing because it feels like
    // Controllers, which are attached at the server level,
    // should receive events only after the socket's own
    // handlers have fired
    $this->getServer()->trigger(
      'event',
      $event,
      ...$data
    );

    // trigger Socket listeners second, but only if
    // the Server-attached listeners didn't cancel them
    if (!$event->canceled()) {
      $this->trigger(
        $eventName,
        $event,
        ...$data
      );
    }

    return $this;
  }

  /**
   * @return $this
   */
  public function handleClose()
  {
    // leave all the rooms
    $this->rooms->each->leave($this);
    // detach and say good-bye!
    $this->getServer()->detach($this)->onClosed($this);
    return $this;
  }

  /**
   * @return $this
   */
  public function handlePong(Packet $packet)
  {
    $this->lastPongAt = $this->now();

    return $this;
  }

  public function handleJoin(Room $room)
  {
    $this->rooms[$room->getName()] = $room;

    return $this;
  }

  public function handleLeave(Room $room)
  {
    $this->rooms->forget($room->getName());

    return $this;
  }

  /**
   * Process a raw incoming Message from the Client
   * @param MessageInterface $msg
   * @return $this
   * @throws \Exception
   */
  public function handleMessage(MessageInterface $msg)
  {
    $this
      ->wakeup()
      ->decode($msg)
      ->each(function(Packet $packet) {
        $this->handlePacket($packet);
      });

    return $this;
  }

  /**
   * Send a message to the given Room
   * @param Room|string $room
   * @return To
   */
  public final function to($room)
  {
    return To::make($this)->to($room);
  }

  /**
   * @param Packet $packet
   * @return $this
   * @throws \Exception
   */
  public function handlePacket(Packet $packet)
  {
    if ($packet->isType(Packet::TYPE_CONNECT)) {
      $this->handleConnect($packet);
    } else if ($packet->isEngineType(Engine\Packet::TYPE_PONG)) {
      $this->handlePong($packet);
    } else if ($packet->isType(Packet::TYPE_EVENT)) {
      $this->handleEvent($packet);
    } else {
      throw new PacketTypeInvalid($packet);
    }

    return $this;
  }

  /**
   * @param \Exception $e
   * @return $this
   */
  public function handleError(\Exception $e)
  {
    if ($e instanceof ConnectionException) {
      $this->transmit(
        Packet::error($e->getMessage(), $e->getData())->setNamespace(
          $e->getPacket()->getNamespace()
        )
      );
    } else {
      // ?
    }

    return $this;
  }

  /**
   * @return bool
   */
  public function isOffline()
  {
    return $this->offline;
  }

  /**
   * Toggle the offline status of this Socket
   * @param null|bool $offline
   */
  public function offline($offline = null)
  {
    $currentState = $this->offline;
    $this->offline = $offline || is_null($offline) ? true : false;

    if ($this->offline !== $currentState) {
      if ($this->offline) {
        $this->getServer()->onDisconnect($this);
      } else {
        $this->getServer()->onReconnect($this);
      }
    }

    return $this;
  }


  /**
   * @param MessageInterface $msg
   * @return Collection<Packet>
   * @throws \Exception
   */
  public function decode(MessageInterface $msg): Collection
  {
    return $this->getApp()->getSocketIOParser()->decode(Message::adapt($msg, $this));
  }

  /**
   * @Param ConnectionInterface $conn
   * @return string
   * @throws \Exception
   */
  function encode(Packet $packet)
  {

    // TODO: change Parser such that it mutates Packet, thereby
    // caching the encoded content so that subsequent calls don't
    // encode a second time

    // The parser, which is the same for all Sockets connected to
    // the same Server, is what decides what an encoded Packet
    // looks like, so stop thinking that the encoding would some
    // how be different based on which Socket the data is being
    // sent to. Instead, just focus on if the Packet requires ACK,
    // how to make sure the ACK is registered by all Sockets

    return $this->getApp()->getSocketIOParser()->encode($packet);
  }

  /**
   * Send an event to the client
   * @param $eventName
   * @param mixed ...$data
   */
  function emit($eventName, ...$data)
  {
    return $this->transmit(Packet::event($eventName, ...$data));
  }

  /**
   * Send a message to this client
   * @param mixed ...$messageData
   */
  function send(...$messageData)
  {
    return $this->emit('message', ...$messageData);
  }

  /**
   * Send something to every client
   * @param $eventName
   * @param mixed ...$data
   */
  function broadcast($eventName, ...$data)
  {
    $this->getServer()->getAllSockets()->each->emit($eventName, ...$data);
  }

  /**
   * Ping the client
   * @return Socket
   */
  function ping()
  {
    $this->lastPingAt = $this->now();
    return $this->transmit(Packet::ping());
  }

  /**
   * Send a Packet to the client
   * @param Packet $packet
   * @return $this
   */
  function transmit(Packet $packet)
  {
    // TODO: use a buffer here when isOffline(), except
    // for Ping packets, which should always be sent

    $this->transmitEncoded($this->encode($packet));
    return $this;
  }

  /**
   * Send an encoded packet to the client
   * @param string $encodedPacket
   * @return $this
   */
  function transmitEncoded($encodedPacket)
  {
    $this->getConnection()->send($encodedPacket);
    return $this;
  }

  /**
   * @param $uri
   * @param $serverClass
   * @throws \Exception
   */
  static function route($uri, $serverClass)
  {
    if (!is_subclass_of($serverClass, Server::class)) {
      throw new \Exception("Class [{$serverClass}] must be a subclass of " . Server::class);
    }

    /**
     * Polling requests
     */
    WebRouter::get($uri, function(Request $request) use ($uri, $serverClass) {

      // validate the polling request
      $validator = Validator::make($request->all(), [
        'appKey' => 'required|string',
        'EIO' => 'required|numeric',
        'transport' => 'required|in:polling',
        't' => 'required',
        'j' => 'sometimes|string|nullable',
      ]);

      if ($validator->failed()) {
        throw new ValidationException($validator);
      }

      return (new $serverClass)->onPoll($request);
    });

    // must add suffixing "/" for the UrlRouter in ReactPHP
    if (!Str::endsWith($uri, '/')) {
      $uri .= '/';
    }

    return WebSocketsRouter::webSocket($uri, $serverClass);
  }

}