<?php

namespace Beebo\Policies;

use App\Models\User;
use Beebo\SocketIO\Room;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoomPolicy
{
  use HandlesAuthorization;

  /**
   * Can this user create Rooms?
   * @param User $user
   * @return bool
   */
  function create(User $user)
  {
    return true;
  }

  /**
   * Can this user join the given Room?
   * @param User $user
   * @param Room $room
   * @return bool
   */
  function join(User $user, Room $room)
  {
    return !$room->isFull();
  }
}
