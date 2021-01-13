<?php
namespace Beebo\SocketIO\Parsers;

use Beebo\SocketIO\Message;
use Beebo\SocketIO\Packet;
use Beebo\SocketIO\Engine\Packet as EnginePacket;
use Illuminate\Support\Collection;

/**
 * Default Socket.io parser
 * Class DefaultParser
 * @package Beebo\SocketIO\Parsers
 * @see https://github.com/socketio/socket.io-parser/blob/444520d6cdc78b1abbe3bd684dc3723b5e22d196/lib/index.ts#L238
 */
class DefaultParser extends Parser
{
  protected $separator = "\x1e";

  /**
   * @param Packet $packet
   * @return string
   */
  function encode(Packet $packet): string
  {
    if ($packet->isType([Packet::TYPE_EVENT, Packet::TYPE_ACK])) {
      // TODO: test for binary content
      // TODO: encode as binary
    }

    return $this->encodeAsString($packet);
  }

  /**
   * @param Packet $packet
   * @return string
   */
  protected function encodeAsString(Packet $packet)
  {
    $string = '' . $packet->getEnginePacketType() . $packet->getMessagePacketType();

    if ($packet->isType([Packet::TYPE_EVENT, Packet::TYPE_ACK])) {
      if ($packet->getAttachments()->count()) {
        $string .= $packet->getAttachments()->count() . '-';
      }
    }

    if ($packet->getNamespace() && $packet->getNamespace() !== '/') {
      $string .= $packet->getNamespace() . ',';
    }

    if (!is_null($packet->getId())) {
      $string .= $packet->getId();
    }

    if ($data = $packet->getData()) {
      $string .= json_encode($data);
    }

    return $string;
  }

  /**
   * @param Message $msg
   * @return Collection<Packet>
   */
  function decode(Message $msg): Collection
  {
    return collect(explode($this->separator, $msg->getPayload()))->map(function($input) use ($msg) {
      $enginePacketType = ((int) $input[0]) ?? null;

      if ($enginePacketType === EnginePacket::TYPE_MESSAGE) {
        return $this->decodeString(substr($input, 1))->setRawMessage($msg);
      } else {
        return Packet::make($enginePacketType)->setRawMessage($msg);
      }
    });
  }

  /**
   * @param $input
   * @return Packet
   * @throws \Exception
   */
  function decodeString($input): Packet
  {
    $i = 0;

    // TODO: if the first character is a "b", then the payload contains binary data

    $packet = Packet::make(EnginePacket::TYPE_MESSAGE, $input[$i]);

    // count attachments in binary messages
    $attachmentCount = null;
    if ($packet->isType([Packet::TYPE_BINARY_EVENT, Packet::TYPE_BINARY_ACK])) {
      $start = $i + 1;
      while ($input[++$i] !== '-' && $i !== strlen($input)) {
        // incrementing $i...
      }
      $buffer = substr($input, $start, $i-$start);
      if (!is_numeric($buffer) || $input[$i] !== '-') {
        throw new \Exception("Illegal attachments");
      }
      $attachmentCount = (int) $buffer;
    }

    // lookup namespace (if any)
    if ('/' === ($input[$i + 1] ?? null)) {
      $start = $i + 1;
      while (++$i) {
        $c = $input[$i] ?? null;
        if (',' === $c) break;
        if ($i === strlen($input)) break;
      }
      $packet->setNamespace(substr($input, $start, $i-$start));
    } else {
      $packet->setNamespace('/');
    }

    // look up id
    $next = $input[$i + 1] ?? null;
    if ('' !== $next && is_numeric($next)) {
      $start = $i + 1;
      while (++$i) {
        $c = $input[$i] ?? null;
        if (is_null($c) || !is_numeric($c)) {
          --$i;
          break;
        }
        if ($i === strlen($input)) break;
      }
      $packet->setId((int) substr($input, $start, $i + 1 - $start));
    }

    // look up json
    if (!is_null($input[++$i] ?? null)) {
      $payload = json_decode(substr($input, $i), $assoc = false, $depth = 512, JSON_THROW_ON_ERROR);
      if ($this->isPayloadValid($packet->getType(), $payload)) {
        $packet->setData($payload);
      } else {
        throw new \Exception("invalid payload");
      }
    }

    return $packet;
  }

  /**
   * @return bool
   */
  private function isPayloadValid($type, $payload)
  {
    switch ($type) {
      case Packet::TYPE_CONNECT:
        return is_array($payload) || is_object($payload);
      case Packet::TYPE_DISCONNECT:
        return empty($payload);
      case Packet::TYPE_CONNECT_ERROR:
        return is_string($payload) || is_array($payload) || is_object($payload);
      case Packet::TYPE_EVENT:
      case Packet::TYPE_BINARY_EVENT:
        return is_array($payload) && is_string($payload[0] ?? null);
      case Packet::TYPE_ACK:
      case Packet::TYPE_BINARY_ACK:
        return is_array($payload);
    }
  }
}