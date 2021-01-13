<?php
namespace Beebo\Contracts;

use Beebo\EventHandler;
use Beebo\Collections\Listeners as ListenersCollection;

interface Listener
{
  /**
   * @return ListenersCollection
   */
  function getListeners();

  /**
   * @param ListenersCollection $listeners
   * @return $this
   */
  function setListeners(ListenersCollection $listeners);

  /**
   * @param $eventName
   * @param \Closure|EventHandler
   * @return $this
   */
  function on($eventName, $listener);

  /**
   * @param EventHandler $listener
   * @return $this
   */
  function off(EventHandler $listener);

  /**
   * @param null|string $eventName
   * @return $this
   */
  function removeAllListeners($eventName = null);

  /**
   * @param $eventName
   * @param \Closure $callback
   * @return $this
   */
  function once($eventName, $listener);

  /**
   * @param $listener
   * @return $this
   */
  function onAny($listener);

  /**
   * @param $listener
   * @return $this
   */
  function prependAny($listener);

  /**
   * @param $eventName
   * @param mixed ...$data
   * @return $this
   */
  function trigger($eventName, ...$data);
}