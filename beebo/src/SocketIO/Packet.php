<?php
namespace Beebo\SocketIO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Ratchet\RFC6455\Messaging\MessageInterface;

class Packet implements Arrayable {

  const TYPE_CONNECT = 0;
  const TYPE_DISCONNECT = 1;
  const TYPE_EVENT = 2;
  const TYPE_ACK = 3;
  const TYPE_CONNECT_ERROR = 4;
  const TYPE_BINARY_EVENT = 5;
  const TYPE_BINARY_ACK = 6;

  /**
   * @var int
   */
  protected $enginePacketType;

  /**
   * @var int|null
   */
  protected $messagePacketType;

  /**
   * @var mixed|null
   */
  protected $data;

  /**
   * @var Collection<Attachment>
   */
  protected $attachments;

  /**
   * @var string|null
   */
  protected $namespace;

  /**
   * @var int|null
   */
  protected $id;

  /**
   * @var Message
   */
  protected $rawMessage;

  /**
   * @var \Closure
   */
  protected $callback;

  /**
   * Packet constructor.
   */
  private function __construct() {
    $this->attachments = collect([]);
  }

  /**
   * @param int|null $enginePacketType
   * @param int|null $messagePacketType
   * @param mixed $data
   * @return Packet
   */
  static function make(?int $enginePacketType, ?int $messagePacketType = null, $data = null)
  {
    $message = new self;
    $message->enginePacketType = $enginePacketType;
    $message->setMessagePacketType($messagePacketType);
    $message->data = $data;

    return $message;
  }

  /**
   * @return \Closure
   */
  public function getCallback()
  {
    return $this->callback;
  }

  /**
   * @return bool
   */
  public function hasCallback()
  {
    return !is_null($this->callback);
  }

  /**
   * Invoke the callback attached to this Packet
   * @param mixed ...$data
   * @return mixed
   * @throws \Exception
   */
  public function callback(...$data)
  {
    if (!$this->hasCallback()) {
      throw new \Exception("This Packet does not have a callback");
    }
    $callback = $this->callback;
    return $callback(...$data);
  }

  /**
   * @param \Closure|null $callback
   * @return $this
   */
  public function setCallback($callback)
  {
    $this->callback = $callback;
    return $this;
  }

  /**
   * @return Message
   */
  public function getRawMessage()
  {
    return $this->rawMessage;
  }

  /**
   * @param Message $rawMessage
   * @return $this
   */
  public function setRawMessage(Message $rawMessage)
  {
    $this->rawMessage = $rawMessage;
    return $this;
  }

  /**
   * @return int
   */
  public function getEnginePacketType()
  {
    return $this->enginePacketType;
  }

  /**
   * @param int $enginePacketType
   * @return $this
   */
  public function setEnginePacketType(?int $enginePacketType)
  {
    $this->enginePacketType = $enginePacketType;
    return $this;
  }

  /**
   * @param int|array $type
   * @return bool
   */
  function isEngineType($type)
  {
    if (is_array($type)) {
      return in_array($this->enginePacketType, $type);
    } else {
      return $type === $this->enginePacketType;
    }
  }

  /**
   * @return int|null
   */
  public function getMessagePacketType()
  {
    return $this->messagePacketType;
  }

  /**
   * @return int|null
   */
  function getType()
  {
    return $this->getMessagePacketType();
  }

  /**
   * @param int|null $messagePacketType
   * @return $this
   */
  public function setMessagePacketType(?int $messagePacketType)
  {
    $this->messagePacketType = $messagePacketType;
    return $this;
  }

  /**
   * @param int|array $type
   * @return bool
   */
  function isType($type)
  {
    if (is_array($type)) {
      return in_array($this->messagePacketType, $type);
    } else {
      return $type === $this->messagePacketType;
    }
  }

  /**
   * @param int $type
   * @return $this
   * @throws \Exception
   */
  function setType(int $type)
  {
    if ($type < 0 || $type > 6) {
      throw new \Exception("Invalid packet type [{$type}]");
    }

    $this->messagePacketType = $type;
    return $this;
  }

  /**
   * @param $namespace
   * @return $this
   */
  function setNamespace($namespace)
  {
    $this->namespace = $namespace;
    return $this;
  }

  /**
   * @return string|null
   */
  function getNamespace()
  {
    return $this->namespace;
  }

  /**
   * @return bool
   */
  function hasNamespace()
  {
    return $this->namespace && $this->namespace !== '/';
  }

  /**
   * @return int|null
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return bool
   */
  public function hasId()
  {
    return !is_null($this->getId());
  }

  /**
   * @param int|null $id
   */
  public function setId($id)
  {
    $this->id = $id;
    return $this;
  }

  /**
   * @param Attachment $attachment
   * @return $this
   */
  public function addAttachment(Attachment $attachment)
  {
    $this->attachments->push($attachment);
    return $this;
  }

  /**
   * @return Collection
   */
  public function getAttachments()
  {
    return $this->attachments;
  }

  /**
   * @param Collection<Attachment> $attachments
   * @return $this
   */
  public function setAttachments(Collection $attachments)
  {
    $this->attachments = $attachments;
    return $this;
  }

  /**
   * @return mixed|null
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * @param mixed|null $data
   * @return $this
   */
  public function setData($data)
  {
    $this->data = $data;
    return $this;
  }

  /**
   * @param string $sid
   * @param array $upgrades
   * @param int|float $pingInterval in seconds
   * @param int|float $pingTimeout in seconds
   * @return Packet
   */
  static function handshake($sid, array $upgrades, $pingInterval, $pingTimeout): self
  {
    return self::make(Engine\Packet::TYPE_OPEN, null, [
      'sid' => $sid,
      'upgrades' => $upgrades,
      // socket.io requires these to be expressed in milliseconds:
      'pingTimeout' => $pingTimeout * 1000,
      'pingInterval' => $pingInterval * 1000,
    ]);
  }

  /**
   * @param $sid
   * @param mixed|null $data
   * @return Packet
   */
  static function connect($sid, $data = null): self
  {
    return self::make(
      Engine\Packet::TYPE_MESSAGE,
      self::TYPE_CONNECT,
      array_merge(
        [
          'sid' => $sid,
        ],
        $data
      )
    );
  }

  /**
   * @return Packet
   */
  static function ping()
  {
    return self::make(Engine\Packet::TYPE_PING);
  }

  /**
   * @param $event
   * @param mixed ...$data
   * @return Packet
   */
  static function event($eventName, ...$data): self
  {
    // TODO: reservered event names; throw error if used
//    connect
//    connect_error
//    disconnect
//    disconnecting
//    newListener
//    removeListener

    // TODO: analyze $data for binary, or revisit $attachments concept

    // if $data contains a callback, extract it and put it in
    // the packet where the encoder can find it
    $callback = null;
    if (!empty($data)) {
      $last = $data[count($data)-1];
      if ($last instanceof \Closure) {
        $callback = array_pop($data);
      }
    }

    return self::make(
      Engine\Packet::TYPE_MESSAGE,
      self::TYPE_EVENT,
      array_merge(
        [$eventName],
        $data
      )
    )->setCallback($callback);
  }

  /**
   * @param Packet $packet
   * @param mixed ...$data
   * @return Packet
   */
  static function ack(Packet $packet, ...$data): self
  {
    return self::make(
      Engine\Packet::TYPE_MESSAGE,
      self::TYPE_ACK,
      $data
    )->setId($packet->getId());
  }

  /**
   * @param $message
   * @param array|null $data
   * @return Packet
   */
  static function error($message, array $data = null): self
  {
    return self::make(
      Engine\Packet::TYPE_MESSAGE,
      self::TYPE_CONNECT_ERROR,
      [
        'message' => $message,
        'data' => (object) ($data ?? []),
      ]
    );
  }

  /**
   * Create a JSON-P response packet.
   * @param $j
   * @return string
   */
  function toJSONP($j)
  {
    return sprintf('___eio[%d](%s)', $j, json_encode($this->toArray()));
  }

  public function toArray()
  {

  }

  /**
   * @return Packet
   */
  static function close()
  {
    return self::disconnect();
  }

  /**
   * @return Packet
   */
  static function disconnect(): self
  {
    return self::make(Engine\Packet::TYPE_CLOSE, null);
  }

}