<?php
namespace App\Sockets\Controllers;

use Beebo\Concerns\Rooms;
use Beebo\SocketIO\Controller;
use Beebo\SocketIO\Event;

class ButtonController extends Controller
{
  use Rooms;

  protected $i = 0;

  function getState()
  {
    return $this->i;
  }

  function increment(Event $request)
  {
    $this->in('examples')->emit('Button.state', ++$this->i);
  }
}