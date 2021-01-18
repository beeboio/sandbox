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
   * @param string|null Room class
   * @return Room
   */
  function getRoom($room, $roomClass = null)
  {
    if (!$room instanceof Room) {
      $room = $this->getServer()->makeRoom($roomName = $room, $roomClass);
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

  /**
   * @param $roomName
   * @param null $roomClass
   * @throws \Exception
   */
  function makeRoom($roomName, $roomClass = null)
  {
    $this->getServer()->makeRoom($roomName, $roomClass);
  }

  function send(...$messageData)
  {
    return $this->emit('message', ...$messageData);
  }

  abstract function emit($eventName, ...$data);
}