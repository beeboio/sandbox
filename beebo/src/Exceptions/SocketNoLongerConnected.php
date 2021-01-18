<?php
namespace Beebo\Exceptions;

class SocketNoLongerConnected extends \Exception
{
  protected $sid;

  public function __construct($sid)
  {
    $this->sid = $sid;

    parent::__construct("Socket [{$this->sid}] is no longer connected.");
  }
}