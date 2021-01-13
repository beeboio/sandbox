<?php
namespace Buffer\SocketIO;

use Beebo\SocketIO\Packet;
use Beebo\SocketIO\Socket;

/**
 * A socket that buffers all transmitted packets, and sends on an
 * interval defined by the instance.
 * Class BufferedSocket
 * @package App\Sockets
 */
class BufferedSocket extends Socket
{
  /**
   * @var float|int Interval at which the buffer is flushed, in seconds; defaults to 50ms
   */
  public $flushInterval = 0.050;

  /**
   * @param Packet $packet
   * @return Socket|void
   */
  public function transmit(Packet $packet)
  {

  }
}