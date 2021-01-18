<?php
namespace App\Sockets\Controllers;

use Beebo\SocketIO\Controller;
use Beebo\SocketIO\Event;

class ButtonController extends Controller
{
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