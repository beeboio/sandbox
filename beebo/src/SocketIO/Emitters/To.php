<?php
namespace Beebo\SocketIO\Emitters;

use Beebo\SocketIO\Packet;
use Beebo\SocketIO\Room;
use Beebo\SocketIO\Socket;

/**
 * The "To" emitter sends messages from a source to
 * all of the other clients in the given Rooms.
 * Class To
 * @package Beebo\SocketIO\Emitters
 */
class To extends Emitter
{
  static function make($from)
  {
    $emitter = new self;
    $emitter->from = $from;
    return $emitter;
  }

  function to($room)
  {
    $room = $this->getRoom($room);
    $this->rooms[$room->getName()] = $room;
    return $this;
  }

  function emit($eventName, ...$data)
  {
    if (count($this->rooms)) {

      // TODO: cache this flattened and de-duped list

      $sockets = $this->rooms->map(function(Room $room) {
        return $room->getSockets();
      })->flatten()->unique(function(Socket $socket) {
        return $socket->getId();
      });

      if (count($sockets)) {
        $event = Packet::event($eventName, ...$data);
        $sockets->each(function(Socket $socket) use ($event) {
          if (!$this->isFromSocket() || $socket->getId() !== $this->from->getId()) {
            $socket->transmit($event);
          }
        });
      }
    }

    return $this;
  }
}