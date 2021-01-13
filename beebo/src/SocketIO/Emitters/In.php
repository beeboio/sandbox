<?php
namespace Beebo\SocketIO\Emitters;

use Beebo\SocketIO\Packet;
use Beebo\SocketIO\Room;
use Beebo\SocketIO\Socket;

/**
 * The "In" emitter sends messages from a source to
 * all of the clients in the given Rooms, including the source.
 * Class To
 * @package Beebo\SocketIO\Emitters
 */
class In extends Emitter
{
  static function make($from)
  {
    $emitter = new self;
    $emitter->from = $from;
    return $emitter;
  }

  function in($room)
  {
    $room = $this->getRoom($room);
    $this->rooms[$room->getName()] = $room;
    return $this;
  }

  function emit($eventName, ...$data)
  {
    if (count($this->rooms)) {
      $sockets = $this->rooms->map(function(Room $room) {
        return $room->getSockets();
      })->flatten()->unique(function(Socket $socket) {
        return $socket->getId();
      });

      if (count($sockets)) {
        $event = Packet::event($eventName, ...$data);
        $sockets->each->transmit($event);
      }
    }

    return $this;
  }
}