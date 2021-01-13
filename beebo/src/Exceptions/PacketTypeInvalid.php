<?php
namespace Beebo\Exceptions;

use Beebo\SocketIO\Packet;

class PacketTypeInvalid extends \Exception
{
  protected $packet;

  function __construct(Packet $packet)
  {
    $this->packet = $packet;
    $type = $this->packet->getType();

    if (empty($type)) {
      parent::__construct('Packet type not defined; raw payload: [' . $this->packet->getRawMessage()->getPayload() . ']');
    } else {
      parent::__construct("Packet type not supported: [{$type}]");
    }
  }
}