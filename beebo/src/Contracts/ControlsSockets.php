<?php
namespace Beebo\Contracts;

use Beebo\SocketIO\Server;
use Ramsey\Collection\Collection;

interface ControlsSockets
{
  /**
   * @param Server $server
   * @return $this
   */
  function _setServer(Server $server);

  /**
   * @return Server
   */
  function _getServer();

  /**
   * @return string
   */
  function _getName();

  /**
   * @return Collection<string>
   */
  function _getPublicMethods();
}