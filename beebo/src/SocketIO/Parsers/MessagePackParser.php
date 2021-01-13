<?php
namespace Beebo\SocketIO\Parsers;

use Beebo\SocketIO\Message;
use Beebo\SocketIO\Packet;
use Illuminate\Support\Collection;

class MessagePackParser extends Parser
{
  function encode(Packet $packet): string
  {
    // TODO: Implement encode() method.
  }

  /**
   * @param Message $msg
   * @return Collection<Packet>
   */
  function decode(Message $msg): Collection
  {
    // TODO: Implement decode() method.
  }

  /**
   * @param $message
   * @return string
   * @throws \Exception
   */
  protected function pack($message)
  {
    if (function_exists('msgpack_pack')) {
      return msgpack_pack($message);
    } else if (class_exists('MessagePack\MessagePack')) {
      return \MessagePack\MessagePack::pack($message);
    } else {
      throw new \Exception("MessagePack is not available");
    }
  }

  /**
   * @param $message
   * @return mixed
   * @throws \Exception
   */
  protected function unpack($message)
  {
    if (function_exists('msgpack_unpack')) {
      return msgpack_unpack($message);
    } else if (class_exists('MessagePack\MessagePack')) {
      return \MessagePack\MessagePack::unpack($message);
    } else {
      throw new \Exception("MessagePack is not available");
    }
  }
}