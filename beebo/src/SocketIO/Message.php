<?php
namespace Beebo\SocketIO;

use Ratchet\RFC6455\Messaging\FrameInterface;
use Ratchet\RFC6455\Messaging\MessageInterface;

class Message
{
  /**
   * @var Socket
   */
  protected $source;

  /**
   * @var MessageInterface
   */
  protected $raw;

  /**
   * @var string
   */
  protected $mockPayload;

  private function __construct() {}

  /**
   * @param MessageInterface $msg
   * @param Socket $source
   */
  static function adapt(MessageInterface $raw, Socket $source)
  {
    $message = new self;
    $message->raw = $raw;
    $message->source = $source;
    return $message;
  }

  static function mock($payload)
  {
    $message = new self;
    $message->mockPayload = $payload;
    return $message;
  }

  /**
   * @return Socket
   */
  public function getSource()
  {
    return $this->source;
  }

  public function count()
  {
    return $this->raw->count();
  }

  function isCoalesced()
  {
    return $this->raw->isCoalesced();
  }

  function getPayloadLength()
  {
    return $this->raw->getPayloadLength();
  }

  function getPayload()
  {
    if (!is_null($this->mockPayload)) {
      return $this->mockPayload;
    }
    return $this->raw->getPayload();
  }

  function getContents()
  {
    return $this->raw->getContents();
  }

  function __toString()
  {
    return $this->raw->__toString();
  }

  function addFrame(FrameInterface $fragment)
  {
    $this->raw->addFrame($fragment);
  }

  function getOpcode()
  {
    return $this->raw->getOpcode();
  }

  function isBinary()
  {
    return $this->raw->isBinary();
  }

}