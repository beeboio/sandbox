<?php
namespace Beebo\SocketIO\Engine;

class Packet
{
  const TYPE_OPEN = 0;
  const TYPE_CLOSE = 1;
  const TYPE_PING = 2;
  const TYPE_PONG = 3;
  const TYPE_MESSAGE = 4;
  const TYPE_UPGRADE = 5;
  const TYPE_NOOP = 6;
}