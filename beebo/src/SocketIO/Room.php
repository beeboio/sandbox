<?php
namespace Beebo\SocketIO;

use Beebo\Concerns\Bootable;
use Beebo\Concerns\Unique;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class Room implements Arrayable
{
  use Bootable, Unique;

  protected $name;

  protected $server;

  protected $id;

  /**
   * @var Collection<Socket>
   */
  protected $sockets;

  private function __construct()
  {
    // TODO: setup interval to check room membership and dispose

    $this->sockets = collect([]);

    $this->bootIfNotBooted();

    $this->initializeTraits();

    $this->id = self::makeId();
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
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

    $room->name = $name;
    $room->server = $server;

    return $room;
  }

  /**
   * @return Collection
   */
  function getSockets()
  {
    return $this->sockets;
  }

  /**
   *
   */
  function getName()
  {
    return $this->name;
  }

  /**
   * @param Socket $socket
   * @return $this
   */
  public final function join(Socket $socket)
  {
    $this->sockets[$socket->getId()] = $socket;
    $socket->handleJoin($this);
    return $this;
  }

  /**
   * @param Socket $socket
   * @return $this
   */
  public final function leave(Socket $socket)
  {
    $this->sockets->forget($socket->getId());
    $socket->handleLeave($this);
    return $this;
  }

  /**
   * @return string
   */
  public function __toString()
  {
    return "{$this->getName()}#{$this->getId()}";
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