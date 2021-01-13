<?php
namespace Beebo\Pusher;

use Illuminate\Contracts\Support\Jsonable;
use Ratchet\RFC6455\Messaging\MessageInterface;

class Event implements Jsonable
{
  const TYPE_CONNECTION_ESTABLISHED = 'pusher:connection_established';
  const TYPE_PING = 'pusher:ping';
  const TYPE_PONG = 'pusher:pong';
  const TYPE_SUBSCRIBE = 'pusher:subscribe';

  /**
   * @var MessageInterface
   */
  protected $rawMessage;

  /**
   * @var string
   */
  protected $type;

  /**
   * @var \stdClass
   */
  protected $data;

  /**
   * Event constructor.
   */
  private function __construct() {
    $this->data = new \stdClass;
  }

  /**
   * @param MessageInterface $rawMessage
   * @return Event
   */
  static function decode(MessageInterface $rawMessage)
  {
    $event = new self;
    $event->rawMessage = $rawMessage;

    $data = json_decode($rawMessage->getPayload());

    // TODO: $data has to be valid JSON...

    $event->type = $data->event;
    $event->data = json_decode($data->data ?? '{}');
    return $event;
  }

  /**
   * @param null $path
   * @return mixed
   */
  public function get($path = null)
  {
    return !is_null($path) ? data_get($this->data, $path) : $this->data;
  }

  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * @return \stdClass
   */
  public function all()
  {
    return $this->data;
  }

  /**
   * @param $name
   * @return mixed
   */
  public function __get($name)
  {
    return $this->get($name);
  }

  /**
   * @param $name
   * @param $value
   */
  public function __set($name, $value)
  {
    data_set($this->data, $name, $value);
  }

  /**
   * @param string $type
   * @return bool
   */
  public function isType($type)
  {
    return $this->type === $type;
  }

  /**
   * @param int $options
   * @return false|string
   */
  public function toJson($options = 0)
  {
    return json_encode([
      'event' => $this->type,
      'data' => $this->data,
    ]);
  }

  /**
   * @return Event
   */
  public static function ping()
  {
    $event = new self;
    $event->type = self::TYPE_PING;
    $event->data = (object) [];
    return $event;
  }

  /**
   * @param $channelName
   * @param bool $private
   */
  public static function subscribe($channelName, $private = false)
  {
    if ($private) {
      $channelName = 'private-' . $channelName;
    }

    $event = new self;
    $event->type = self::TYPE_SUBSCRIBE;
    $event->channel = $channelName;

    return $event;
  }

  /**
   * @return false|string
   */
  public function __toString()
  {
    return $this->toJson();
  }

  /**
   * @return mixed
   */
  public function getRawMessage()
  {
    return $this->rawMessage;
  }

}