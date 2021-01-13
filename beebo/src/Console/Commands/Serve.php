<?php
namespace Beebo\Console\Commands;

use Beebo\SocketIO\Server;
use BeyondCode\LaravelWebSockets\Console\StartWebSocketServer;

class Serve extends StartWebSocketServer
{
  protected $signature = 'beebo:serve {--host=0.0.0.0} {--port=6001} {--debug : Forces the loggers to be enabled and thereby overriding the app.debug config setting } ';

  protected $description = 'Start the Beebo Server';

  public function __construct()
  {
      parent::__construct();

      // the loop lives in our lower-level Server implementation;
      // that way, it can be accessed from all websocket handlers
      // so that they can create their own keep-alives and other
      // important intervals
      $this->loop = Server::loop();
  }
}