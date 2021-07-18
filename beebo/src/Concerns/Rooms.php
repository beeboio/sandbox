<?php
namespace Beebo\Concerns;

use Beebo\Exceptions\RoomDoesNotExistException;
use Beebo\SocketIO\Emitters\In;
use Beebo\SocketIO\Event;
use Beebo\SocketIO\Socket;

/**
 * Add general room management functionality to a Controller.
 * @package Beebo\Concerns
 */
trait Rooms
{
  /**
   * Get the room class for this Controller
   * @return string
   */
  protected function getRoomClass()
  {
    if (!empty($this->roomClass)) {
      return $this->roomClass;
    } else {
      return $this->_getServer()->getDefaultRoomClass();
    }
  }

  /**
   * @return string
   * @throws \Exception
   */
  public function createRoom(Event $request)
  {
    if ($this->uses(Authorizes::class)) {
      $this->authorize($request->socket, 'create', $this->getRoomClass());
    }

    // TODO: Keep track of which socket created a room

    $room = $this->makeRoom(Unique::makeId(), $this->getRoomClass());

    $request->callback($room->getName());

    $this->in($room)->send("{$request->socket->user()} created room {$room}.");

    $this->joinRoom($request, $room->getName());
  }

  /**
   * @param Event $request
   * @param $roomName
   * @throws \Beebo\Exceptions\RoomDoesNotExistException
   */
  public function joinRoom(Event $request, $roomName)
  {
    if (!$room = $this->_getServer()->rooms()->get($roomName)) {
      throw new RoomDoesNotExistException($roomName);
    }

    if ($this->uses(Authorizes::class)) {
      $this->authorize($request->socket, 'join', $room);
    }

    $this->join($request->socket, $room);

    $this->in($room)->send("{$request->socket->user()} joined room {$room}.");

    if ($room->uses(State::class)) {
      $request->socket->emit('state', $room->all());
    }
  }

  /**
   * @param Event $request
   * @param $roomId
   */
  public function leaveRoom(Event $request, $roomId)
  {
    if ($room = $this->leave($request->socket, $roomId)) {
      $this->in($room)->send("{$request->socket->user()} left room {$room}.");
    }
  }

  /**
   * @param string $roomName
   * @return In
   */
  protected function in($roomName)
  {
    return $this->_getServer()->in($roomName);
  }

  /**
   * @param string $roomName
   * @param string|null $roomClass
   * @return \Beebo\SocketIO\Room
   * @throws \InvalidArgumentException
   */
  protected function makeRoom($roomName, $roomClass = null)
  {
    return $this->_getServer()->makeRoom($roomName, $roomClass ?: $this->getRoomClass());
  }

  /**
   * @param Room|string $roomName
   * @param null $roomClass
   * @return Room
   * @throws RoomDoesNotExistException
   */
  protected function join(Socket $socket, $roomName, $roomClass = null)
  {
    if ($this->_getServer()->hasRoom($roomName, $roomClass ?: $this->getRoomClass())) {
      return $this->_getServer()->join($socket, $roomName, $roomClass ?: $this->getRoomClass());
    }

    throw new RoomDoesNotExistException($roomName, $roomClass ?: $this->getRoomClass());
  }

  /**
   * @param Socket $socket
   * @param Room|string $roomName
   * @return Room
   */
  protected function leave(Socket $socket, $roomName)
  {
    return $this->_getServer()->leave($socket, $roomName);
  }
}