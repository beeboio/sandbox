<?php
namespace Beebo\Concerns;

use Beebo\Collections\Listeners as ListenersCollection;
use Beebo\EventHandler;

trait Listens
{
  /**
   * @var ListenersCollection<EventHandler>
   */
  protected $listeners;

  protected function initializeListens()
  {
    $this->setListeners(new ListenersCollection);
  }

  /**
   * @return ListenersCollection
   */
  function getListeners()
  {
    return $this->listeners;
  }

  /**
   * @param ListenersCollection $listeners
   * @return $this;
   */
  function setListeners(ListenersCollection $listeners)
  {
    $this->listeners = $listeners;
    return $this;
  }

  /**
   * @param $eventName
   * @param \Closure|EventHandler
   * @return $this
   */
  function on($eventName, $listener)
  {
    $listener = EventHandler::make($listener, $eventName);
    $this->listeners[$listener->getId()] = $listener;
    return $this;
  }

  /**
   * @param EventHandler $listener
   * @return $this
   */
  function off(EventHandler $listener)
  {
    $this->listeners->removeListeners($listener);
    return $this;
  }

  /**
   * @param null|string $eventName
   * @return $this
   */
  function removeAllListeners($eventName = null)
  {
    $this->listeners->removeAllListeners($eventName);
    return $this;
  }

  /**
   * @param $eventName
   * @param \Closure $callback
   * @return $this
   */
  function once($eventName, $listener)
  {
    $listener = EventHandler::make($listener, $eventName, true);
    $this->listeners->push($listener);
    return $this;
  }

  /**
   * @param $listener
   * @return $this
   */
  function onAny($listener)
  {
    $listener = EventHandler::make($listener);
    $this->listeners->push($listener);
    return $this;
  }

  /**
   * @param $listener
   * @return $this;
   */
  function offAny($listener)
  {
    return $this->off(EventHandler::make($listener, null));
  }

  /**
   * @param $listener
   * @return $this
   */
  function prependAny($listener)
  {
    $listener = EventHandler::make($listener);
    $this->listeners->prepend($listener);
    return $this;
  }

  /**
   * @param $eventName
   * @param mixed ...$data
   * @return $this
   */
  function trigger($eventName, ...$data)
  {
    $this->listeners->triggerListeners($eventName, ...$data);
    return $this;
  }

}