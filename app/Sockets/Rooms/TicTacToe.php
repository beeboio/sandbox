<?php
namespace App\Sockets\Rooms;

use App\Models\User;
use Beebo\Concerns\Assertive;
use Beebo\Concerns\State;
use Beebo\Contracts\Stateful;
use Beebo\Exceptions\AssertionException;
use Beebo\SocketIO\Room;

class TicTacToe extends Room implements Stateful
{
  use State, Assertive;

  const TURN_PLAYER1 = 1;
  const TURN_PLAYER2 = 2;

  protected $state = [
    'players' => [],
    'turn' => null,
    'grid' => [],
    'playing' => false,
  ];

  /**
   * @param User $user
   * @return $this
   * @throws AssertionException
   */
  function addPlayer(User $user)
  {
    return $this
      ->assert($this->count('players') < 2, "There are no more seats.")
      ->push('players', $user);
  }

  /**
   * Mark an empty space using the User's marker.
   * @param User $user
   * @param int $space
   * @return $this
   * @throws AssertionException
   */
  function mark(User $user, int $space)
  {
    return $this
      ->assertBetween($space, 0, 5)
      ->assert($this->isEmpty("grid.{$space}"))
      ->set("grid.{$space}", '');
  }

  /**
   * @param User $user
   * @return bool
   */
  function isMyTurn(User $user)
  {
    return (bool) $this->get("players.{$this->turn}")->is($user);
  }

  /**
   * @param User $user
   * @return $this
   * @throws AssertionException
   */
  function assertMyTurn(User $user)
  {
    return $this->assert($this->isMyTurn($user));
  }
}