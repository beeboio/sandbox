<?php
namespace App\Sockets\Controllers;

use App\Sockets\Servers\Examples;
use Beebo\Contracts\Messenger;
use Beebo\SocketIO\Controller;
use Beebo\SocketIO\Event;
use Illuminate\Foundation\Application as Laravel;

class ChatController extends Controller implements Messenger
{
  function message(Event $request, $message)
  {
    $request->cancelBubble()->socket->to('examples')->send($message);
  }

  function version()
  {
    return [
      'chat' => Examples::VERSION,
      'laravel' => Laravel::VERSION,
    ];
  }

}