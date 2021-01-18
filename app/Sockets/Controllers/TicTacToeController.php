<?php
namespace App\Sockets\Controllers;

use Beebo\Concerns\Unique;
use Beebo\SocketIO\Controller;
use Beebo\SocketIO\Event;

class TicTacToeController extends Controller
{
  use Unique;

  /**
   * @return string
   * @throws \Exception
   */
  function createRoom(Event $request)
  {
    // TODO: authorization
    // TODO: we need a random name-maker, to reduce confusion with Unique::makeId()
    $room = $this->makeAndJoinRoom($request->socket, self::makeId());

    $request->callback($room->getName());

    // TODO: "someone" should be user's name
    $this->in($room)->send("Someone created {$room}.");
  }

  /**
   * @param Event $request
   * @param $roomName
   * @throws \Beebo\Exceptions\RoomDoesNotExistException
   */
  function joinRoom(Event $request, $roomName)
  {
    // TODO: authorization
    $room = $this->join($request->socket, $roomName);

    // TODO: "someone" should be user's name
    $this->in($roomName)->send("Someone joined {$room}.");
  }

  /**
   * @param Event $request
   * @param $roomId
   */
  function leaveRoom(Event $request, $roomId)
  {
    if ($room = $this->leave($request->socket, $roomId)) {
      // TODO: "someone" should be user's name
      $this->in($room)->send("Someone left {$room}.");
    }
  }

}