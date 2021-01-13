<?php
namespace Beebo\SocketIO;

use Beebo\Contracts\ControlsSockets;
use Beebo\Traits\Sockets;

/**
 * Basic Controller, if you need to create a new one from scratch.
 * Class Controller
 * @package Beebo\SocketIO
 */
abstract class Controller implements ControlsSockets
{
  use Sockets;
}