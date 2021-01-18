<?php
namespace Beebo\SocketIO;

use Beebo\Concerns\Unique;
use Illuminate\Support\Arr;

/**
 * Simple wrapper for Socket.IO events (messages) data.
 * Class Event
 * @package Beebo\SocketIO
 */
class Event
{
  use Unique;

  /**
   * @var Socket
   */
  public $socket;

  /**
   * @var Packet
   */
  public $packet;

  /**
   * @var string
   */
  public $name;

  /**
   * @var bool
   */
  protected $canceled = false;

  /**
   * @var bool
   */
  protected $called = false;

  /**
   * @var string
   */
  private $id;


  /**
   * Event constructor.
   */
  private function __construct() {}

  /**
   * @param string $eventName
   * @param Socket $socket
   * @param Packet $packet
   * @return Event
   * @throws \Exception
   */
  static function make($eventName, Socket $socket, Packet $packet)
  {
    $event = new static;
    $event->id = self::makeId();
    $event->name = $eventName;
    $event->socket = $socket;
    $event->packet = $packet;
    return $event;
  }

  function hasCallback()
  {
    return $this->packet->hasCallback();
  }

  /**
   * Get all of the input and files for the request.
   *
   * @param  array|mixed|null  $keys
   * @return array
   */
  function all($keys = null)
  {
    $input = $this->packet->getData();

    if (! $keys) {
      return $input;
    }

    $results = [];

    foreach (is_array($keys) ? $keys : func_get_args() as $key) {
      Arr::set($results, $key, Arr::get($input, $key));
    }

    return $results;
  }

  /**
   * Retrieve an input item from the request.
   *
   * @param  string|null  $key
   * @param  mixed  $default
   * @return mixed
   */
  public function input($key = null, $default = null)
  {
    return data_get(
      $this->packet->getData(), $key, $default
    );
  }

  // TODO: implement most of the functions in Illuminate\Http\Concerns\InteractsWithInput

  /**
   * Invoke the callback attached to the Packet, if it has not been called already
   * @param mixed ...$data
   * @return $this
   * @throws \Exception
   */
  function callback(...$data)
  {
    if (!$this->called) {
      $this->packet->callback(...$data);
      $this->called = true;
    }
    return $this;
  }

  /**
   * @return $this
   */
  function cancelBubble()
  {
    $this->canceled = true;
    return $this;
  }

  /**
   * @return bool
   */
  function canceled()
  {
    return $this->canceled;
  }

}