<?php
namespace Beebo\SocketIO\Emitters;

use Beebo\SocketIO\Room;
use Beebo\SocketIO\Server;
use Beebo\SocketIO\Socket;
use Illuminate\Support\Collection;

abstract class Emitter
{
  protected $from;

  /**
   * @var Collection<Room>
   */
  protected $rooms;

  /**
   * Emitter constructor.
   */
  function __construct()
  {
    $this->rooms = collect([]);
  }

  function isFromServer()
  {
    return $this->from instanceof Server;
  }

  function isFromSocket()
  {
    return $this->from instanceof Socket;
  }

  /**
   * @param Room|string The room or room name
   * @return Room
   */
  function getRoom($room)
  {
    if (!$room instanceof Room) {
      $room = $this->getServer()->makeRoom($roomName = $room);
    }
    return $room;
  }

  /**
   * @return Server
   */
  function getServer()
  {
    return $this->isFromSocket() ? $this->from->getServer() : $this->from;
  }

  function makeRoom($roomName)
  {
    $this->getServer()->makeRoom($roomName);
  }

  function send(...$messageData)
  {
    return $this->emit('message', ...$messageData);
  }

  abstract function emit($eventName, ...$data);
}