<?php
namespace Beebo\SocketIO\Parsers;

use Illuminate\Support\Collection;
use Beebo\SocketIO\Message;
use Beebo\SocketIO\Packet;
use Beebo\SocketIO\Socket;

abstract class Parser
{
  const TYPE_DEFAULT = 'default';
  const TYPE_JSON = 'json';
  const TYPE_MESSAGEPACK = 'msgpack';
  const TYPE_SCHEMAPACK = 'schemapack';

  /**
   * @param Socket $socket
   * @param Packet $packet
   * @return string
   */
  abstract function encode(Packet $packet): string;

  /**
   * @param Message $msg
   * @return Collection<Packet>
   */
  abstract function decode(Message $msg): Collection;

}