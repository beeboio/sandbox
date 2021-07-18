<?php
namespace Beebo\Console\Commands;

use Beebo\SocketIO\Server;
use BeyondCode\LaravelWebSockets\Console\StartWebSocketServer;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use BeyondCode\LaravelWebSockets\Server\WebSocketServerFactory;

class Serve extends StartWebSocketServer
{
  protected $signature = 'beebo:serve {--host=} {--port=} {--debug : Forces the loggers to be enabled and thereby overriding the app.debug config setting } ';

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

  protected function startWebSocketServer()
  {
      $host = $this->option('host') ?: env('LARAVEL_WEBSOCKETS_HOST', '0.0.0.0');

      $port = $this->option('port') ?: env('LARAVEL_WEBSOCKETS_PORT');

      $this->info("Starting the Beebo server on port {$port}...");

      $routes = WebSocketsRouter::getRoutes();

      /* 🛰 Start the server 🛰  */
      (new WebSocketServerFactory())
          ->setLoop($this->loop)
          ->useRoutes($routes)
          ->setHost($host)
          ->setPort($port)
          ->setConsoleOutput($this->output)
          ->createServer()
          ->run();
  }
}