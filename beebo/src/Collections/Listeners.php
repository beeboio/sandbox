<?php
namespace Beebo\Collections;

use Illuminate\Support\Collection;
use Beebo\EventHandler;

class Listeners extends Collection
{
  /**
   * Gather the Listeners that listen to the given event
   * @param $eventName
   * @return Listeners
   */
  function listensTo($eventName)
  {
    return $this->filter(function(EventHandler $listener) use ($eventName) {
      return $listener->listensTo($eventName);
    });
  }

  /**
   * Gather the Listeners that listen to the given event
   * @param $eventName
   * @return Listeners
   */
  function triggerListeners($eventName, ...$args)
  {
    $justOnce = $this->listensTo($eventName)
      ->filter(function(EventHandler $listener) use ($args) {
        $listener(...$args);
        return $listener->once();
      });

    if (count($justOnce)) {
      return $this->removeListeners($justOnce);
    } else {
      return $this;
    }
  }

  /**
   * Remove one or more Listeners
   * @param mixed ...$listeners
   * @return $this
   */
  function removeListeners(...$listeners)
  {
    $ids = collect($listeners)->map(function(EventHandler $listener) {
      return $listener->getId();
    });
    $this->items = $this->reject(function(EventHandler $listening) use ($ids) {
      return $ids->contains($listening->getId());
    })->toArray();
    return $this;
  }

  /**
   * Remove all Listeners; optionally, remove only listeners that listen to the given event
   * @param string|null $eventName
   * @return $this
   */
  function removeAllListeners($eventName = null)
  {
    if ($eventName) {
      $this->items = $this->reject(function (EventHandler $listener) use ($eventName) {
        return $listener->listensTo($eventName);
      })->toArray();
    } else {
      $this->items = [];
    }
    return $this;
  }
}