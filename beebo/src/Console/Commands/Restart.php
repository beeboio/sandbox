<?php
namespace Beebo\Console\Commands;

use BeyondCode\LaravelWebSockets\Console\RestartWebSocketServer;

class Restart extends RestartWebSocketServer
{
  protected $signature = 'beebo:restart';
}