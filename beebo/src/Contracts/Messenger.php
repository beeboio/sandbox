<?php
namespace Beebo\Contracts;

use Beebo\SocketIO\Event;

/**
 * A Messenger is a Controller with a method named "message"
 * Interface Messenger
 * @package Beebo\Contracts
 */
interface Messenger
{
  function message(Event $event, $message);
}