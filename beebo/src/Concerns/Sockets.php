<?php
namespace Beebo\Concerns;

use Beebo\Exceptions\RoomDoesNotExistException;
use Beebo\SocketIO\Emitters\In;
use Beebo\SocketIO\Room;
use Beebo\SocketIO\Server;
use Beebo\SocketIO\Socket;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Implements Beebo\Contracts\Controller
 * Trait Sockets
 * @package Beebo\Traits
 */
trait Sockets
{

  /**
   * @var string This controller's name
   */
  protected $_name;

  /**
   * cached copy of this Controller's public methods
   * @var Collection<string>
   */
  protected $_methods;

  /**
   * @var Server
   */
  protected $_server;

  /**
   * @param Server $_server
   * @return $this
   */
  function _setServer(Server $_server)
  {
    $this->_server = $_server;
    return $this;
  }

  /**
   * @return Server
   */
  function _getServer()
  {
    return $this->_server;
  }

  /**
   * @return string
   */
  function _getName()
  {
    if (is_null($this->_name)) {
      $name = (new \ReflectionClass($this))->getShortName();
      if (Str::endsWith(strtolower($name), 'controller')) {
        $name = substr($name, 0, strlen($name)-10);
      }
      $this->_name = $name;
    }
    return $this->_name;
  }

  /**
   * @return Collection<string>
   * @thows \Exception
   */
  function _getPublicMethods()
  {
    if (is_null($this->_methods)) {
      $reflection = new \ReflectionClass($this);
      $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
      $this->_methods = collect($methods)->map(function(\ReflectionMethod $method) {
        return $method->getName();
      });
    }
    return $this->_methods;
  }

  /**
   * @param string $roomName
   * @return In
   */
  function in($roomName)
  {
    return $this->_getServer()->in($roomName);
  }

  /**
   * @param string $roomName
   * @param string|null $roomClass
   * @return \Beebo\SocketIO\Room
   * @throws \InvalidArgumentException
   */
  function makeRoom($roomName, $roomClass = null)
  {
    return $this->_getServer()->makeRoom($roomName, $roomClass = null);
  }

  /**
   * @param Socket $socket
   * @param $roomName
   * @param null $roomClass
   * @return \Beebo\SocketIO\Room
   */
  function makeAndJoinRoom(Socket $socket, $roomName, $roomClass = null)
  {
    $room = $this->makeRoom($roomName, $roomClass);
    $this->_getServer()->join($socket, $room);
    return $room;
  }

  /**
   * @param Room|string $roomName
   * @param null $roomClass
   * @return Room
   * @throws RoomDoesNotExistException
   */
  function join(Socket $socket, $roomName, $roomClass = null)
  {
    if ($this->_getServer()->hasRoom($roomName, $roomClass)) {
      return $this->_getServer()->join($socket, $roomName, $roomClass);
    }

    throw new RoomDoesNotExistException($roomName, $roomClass);
  }

  /**
   * @param Socket $socket
   * @param Room|string $roomName
   * @return Room
   */
  function leave(Socket $socket, $roomName)
  {
    return $this->_getServer()->leave($socket, $roomName);
  }

}