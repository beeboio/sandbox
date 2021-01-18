<?php
namespace App\Sockets\Servers;

use App\Events\LightsTurnedOff;
use App\Events\LightsTurnedOn;
use Beebo\SocketIO\Event;
use Beebo\SocketIO\Server;
use Beebo\SocketIO\Socket;
use App\Sockets\Controllers\ChatController;
use Beebo\Concerns\Controllers;
use Beebo\Concerns\PubSub;
use Illuminate\Foundation\Application as Laravel;

class Chat extends Server
{
  use Controllers, PubSub;

  protected $controllers = [
    ChatController::class,
  ];

  const VERSION = '1.0.0';

  public function onInitialize()
  {
    $this->network->on('connect', function() {
      // subscribe to some event on the bus
      $this->subscribe('lights')
        ->on(LightsTurnedOn::class, function () {
          $this->in('chat')->send('The lights are on!');
        })
        ->on(LightsTurnedOff::class, function () {
          $this->in('chat')->send('The lights are off!');
        });
    });
  }

  public function onConnection(Socket $socket)
  {
    // when sockets join, join the "chat" room
    $socket->join('chat')
      ->send('Welcome to the Chat Server!');

    // These are example event handlers.
    // They are duplicated by the ChatController.
    // Unless you cancel bubble, any message will be echoed twice.
    // The client will automatically ignore the duplicate RPC response.

    // simple chat message echo
    $socket->on('message', function(Event $request, $message) {
      $request->socket->to('chat')->send($message);
    });

    // simple RPC example
    $socket->on('Chat.version', function(Event $request) {
      $request->callback([
        'chat' => self::VERSION,
        'laravel' => Laravel::VERSION,
      ]);
    });
  }
}