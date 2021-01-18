<?php
namespace App\Sockets\Controllers;

use App\Sockets\Servers\Chat;
use Beebo\Contracts\Messenger;
use Beebo\SocketIO\Controller;
use Beebo\SocketIO\Event;
use Illuminate\Foundation\Application as Laravel;

class ChatController extends Controller implements Messenger
{
  function message(Event $request, $message)
  {
    $request->cancelBubble()->socket->to('chat')->send($message);
  }

  function version()
  {
    return [
      'chat' => Chat::VERSION,
      'laravel' => Laravel::VERSION,
    ];
  }

  protected $i = 0;

  function getState()
  {
    return $this->i;
  }

  function increment(Event $request)
  {
    $this->in('chat')->emit('Chat.state', ++$this->i);
  }
}