<?php
namespace Beebo\Pusher;

use Beebo\Contracts\Listener;
use Beebo\SocketIO\Server;
use Beebo\Concerns\Bootable;
use Beebo\Concerns\Listens;
use Beebo\Concerns\PingPong;
use Beebo\Concerns\Timers;
use Ratchet\Client\WebSocket as Connection;
use Ratchet\RFC6455\Messaging\MessageInterface;

class Socket implements Listener
{
  use Bootable, Timers, PingPong, Listens;

  /**
   * @var Connection
   */
  protected $conn;

  /**
   * @var string
   */
  protected $id;

  /**
   * @var int
   */
  protected $pingInterval;

  /**
   * Socket constructor.
   */
  private function __construct()
  {
    $this->bootIfNotBooted();
    $this->initializeTraits();
  }

  /**
   * @param Server $server
   * @param Connection $conn
   * @return Socket
   * @throws \Exception
   */
  public static function make(Server $server, Connection $conn = null)
  {
    $socket = new self;

    if (!is_null($conn)) {
      $socket->setConnection($conn);
    }

    return $socket;
  }

  /**
   * @param Connection $conn
   */
  public function setConnection(Connection $conn)
  {
    $this->conn = $conn;

    $this->conn->on('message', function(MessageInterface $message) {
      $this->handleMessage($message);
    });

    $this->trigger('connect');

    return $this;
  }

  function getId()
  {
    return $this->id;
  }

  /**
   * @param MessageInterface $message
   * @return $this
   */
  function handleMessage(MessageInterface $message)
  {
    $this->wakeup();

    $event = Event::decode($message);

    if ($event->isType(Event::TYPE_CONNECTION_ESTABLISHED)) {
      $this->handleConnect($event);
    } else if ($event->isType(Event::TYPE_PONG)) {
      $this->handlePong($event);
    }

    $this->trigger($event->getType(), $event->all());

    // https://pusher.com/docs/channels/library_auth_reference/auth-signatures

    return $this;
  }

  /**
   * Ping the server
   * @return Socket
   */
  function ping()
  {
    $this->lastPingAt = $this->now();
    return $this->transmit(Event::ping());
  }

  /**
   * @return int
   */
  function getPingInterval()
  {
    return $this->pingInterval;
  }

  /**
   * @param Event $event
   * @return $this
   */
  function handleConnect(Event $event)
  {
    // map in the ID
    $this->id = $event->socket_id;
    // map in the timeout
    $this->pingInterval = $event->activity_timeout;
    // keep alive
    $this->every(
      $this->getPingInterval() - 0.1,
      function() {
        $this->keepAlive();
      }
    );

    return $this;
  }

  /**
   * @param Event $event
   * @return $this
   */
  function handlePong(Event $event)
  {
    $this->lastPongAt = $this->now();
    return $this;
  }

  function close()
  {
    $this->trigger('close');
    return $this;
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
        $this->trigger('disconnect');
      } else {
        $this->trigger('reconnect');
      }
    }

    return $this;
  }

  /**
   * @param $channelName
   * @param bool $private
   * @return $this
   */
  function subscribe($channelName, $private = false)
  {
    $this->transmit(Event::subscribe($channelName, $private));
    return $this;
  }

  /**
   * @param Event $event
   * @return $this
   */
  function transmit(Event $event)
  {
    $this->conn->send($event->toJson());
    return $this;
  }
}