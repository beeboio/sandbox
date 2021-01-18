<?php
namespace Beebo\Concerns;

use Beebo\SocketIO\Server;
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

}