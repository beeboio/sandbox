<?php
namespace Beebo\SocketIO;

use Beebo\Concerns\Bootable;
use Beebo\Concerns\Unique;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Room implements Arrayable
{
  use Bootable, Unique;

  protected $_name;

  protected $_server;

  protected $_id;

  /**
   * @var int Default capacity is 10,000 connections
   */
  protected $_capacity = 10000;

  /**
   * @var Collection<Socket>
   */
  protected $_sockets;

  public function __construct()
  {
    // TODO: setup interval to check room membership and dispose

    $this->_sockets = collect([]);

    $this->bootIfNotBooted();

    $this->initializeTraits();

    $this->_id = self::makeId();
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->_id;
  }


  /**
   * Do something later
   * @param \Closure $closure
   * @return $this
   */
  public function later(\Closure $closure)
  {
    // TODO: dispatch to the home server

    return $this;
  }

  /**
   * @param $_capacity
   * @return $this
   */
  public function setCapacity($_capacity)
  {
    $this->_capacity = $_capacity;
    return $this;
  }

  /**
   * @return int
   */
  public function getCapacity()
  {
    return $this->_capacity;
  }

  /**
   * @return bool
   */
  public function isFull()
  {
    // TODO: filter sockets to only connected ones, allowing for latent cleanup
    return $this->_sockets->count() >= $this->_capacity;
  }

  /**
   * @param $roomClass
   * @param $name
   * @param Server $server
   * @return mixed
   * @throws \InvalidArgumentException
   */
  static function make($roomClass, $name, Server $server)
  {
    $room = new $roomClass;
    if (!$room instanceof self) {
      throw new \InvalidArgumentException("{$roomClass} is not a " . get_called_class());
    }

    $room->_name = $name;
    $room->_server = $server;

    return $room;
  }

  /**
   * @return Collection
   */
  function getSockets()
  {
    return $this->_sockets;
  }

  /**
   * @param $eventName
   * @param mixed ...$data
   */
  function emit($eventName, ...$data)
  {
    $this->getSockets()->each->emit($eventName, ...$data);
  }

  /**
   * @return string
   */
  function getName()
  {
    return $this->_name;
  }

  /**
   * @param Socket $socket
   * @return $this
   */
  public final function join(Socket $socket)
  {
    $this->_sockets[$socket->getId()] = $socket;
    $socket->handleJoin($this);
    return $this;
  }

  /**
   * @param Socket $socket
   * @return $this
   */
  public final function leave(Socket $socket)
  {
    $this->_sockets->forget($socket->getId());
    $socket->handleLeave($this);
    return $this;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return "\"{$this->getName()}\"#{$this->getId()}";
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'type' => get_class($this),
    ];
  }


}