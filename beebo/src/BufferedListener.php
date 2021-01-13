<?php
namespace Beebo;

use Beebo\Contracts\Listener;
use Beebo\Traits\Bootable;
use Beebo\Traits\Listens;

/**
 * A BufferedListener can be used in place of some other Listener implementation
 * until that implementation is ready to be swapped-out.
 * Class BufferedListener
 * @package Beebo
 */
class BufferedListener implements Listener
{
  use Bootable, Listens;

  protected $events = [];

  private function __construct()
  {
    $this->bootIfNotBooted();
    $this->initializeTraits();
  }

  /**
   * @return BufferedListener
   */
  static function make()
  {
    return new self;
  }

  /**
   * @param $eventName
   * @param mixed ...$data
   * @return Listener|void
   */
  public function trigger($eventName, ...$data)
  {
    $this->events[] = [$eventName, $data];
    return $this;
  }

  /**
   * @param BufferedListener $oldListener
   * @param Listener $newListener
   * @return Listener $oldListener
   */
  static function swap(BufferedListener &$oldListener, Listener $newListener)
  {
    // merge the two sets of listeners into the new listener
    $newListener->setListeners($oldListener->getListeners()->merge($newListener->getListeners()));
    // empty the old list
    $oldListener->removeAllListeners();
    // empty the buffer
    foreach($oldListener->events as $event) {
      $newListener->trigger($event[0], ...$event[1]);
    }
    // then swap over
    $oldListener = $newListener;

    return $oldListener;
  }
}