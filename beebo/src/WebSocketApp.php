<?php
namespace Beebo;

use Beebo\SocketIO\Parsers\Parser;
use Beebo\SocketIO\Parsers\DefaultParser;
use Beebo\SocketIO\Parsers\MessagePackParser;
use BeyondCode\LaravelWebSockets\Apps\App;

/**
 * Enhances Laravel WebSocket's App object by adding a configuration
 * option for SocketIO parser type.
 * Class WebSocketApp
 * @package Beebo
 */
class WebSocketApp extends App
{
  /**
   * @var string
   */
  protected $socketIOParser = 'default';

  /**
   * @param string $parser
   * @return $this
   */
  function setSocketIOParser($parser)
  {
    $this->socketIOParser = $parser;
    return $this;
  }

  /**
   * @return Parser
   * @throws \Exception
   */
  function getSocketIOParser(): Parser
  {
    if (Parser::TYPE_DEFAULT === $this->socketIOParser) {
      return new DefaultParser;
    } else if (Parser::TYPE_MESSAGEPACK === $this->socketIOParser) {
      return new MessagePackParser;
    } else {
      throw new \Exception("Unsupported parser type [{$this->socketIOParser}]");
    }
  }

}