<?php
namespace Beebo\Exceptions;

use Beebo\SocketIO\Packet;

class ConnectionException extends \Exception
{
  protected $data;

  protected $packet;

  function withData(...$data)
  {
    $this->data = $data;
    return $this;
  }

  function withPacket(Packet $packet)
  {
    $this->packet = $packet;
    return $this;
  }

  function getData()
  {
    return $this->data;
  }

  function getPacket(): Packet
  {
    return $this->packet;
  }
}