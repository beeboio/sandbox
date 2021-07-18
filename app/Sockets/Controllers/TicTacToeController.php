<?php
namespace App\Sockets\Controllers;

use App\Models\User;
use App\Sockets\Rooms\TicTacToe;
use Beebo\Concerns\Rooms;
use Beebo\Exceptions\AssertionException;
use Beebo\SocketIO\Controller;
use Beebo\SocketIO\Event;

class TicTacToeController extends Controller
{
  use Rooms;

  protected $roomClass = TicTacToe::class;

  /**
   * User wants to play.
   * @param User $user
   * @param TicTacToe $room
   * @throws AssertionException When there aren't any seats left
   */
  public function play(User $user, TicTacToe $room)
  {
    $room->addPlayer($user);
  }

  /**
   * User places their Mark on the board.
   * @param Event $request
   * @param User $user
   * @param TicTacToe $room
   * @param $space
   * @throws AssertionException
   */
  public function mark(User $user, TicTacToe $room, $space)
  {
    $room->assertMyTurn($user)->mark($user, $space);
  }
}