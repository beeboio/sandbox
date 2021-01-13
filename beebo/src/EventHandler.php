<?php
namespace Beebo;

use Beebo\Traits\Unique;

class EventHandler
{
  use Unique;

  /**
   * @var string
   */
  protected $eventName;

  /**
   * @var \Closure
   */
  protected $callback;

  /**
   * @var string
   */
  protected $id;

  /**
   * @var bool
   */
  protected $once;

  private function __construct(\Closure $callback)
  {
    $this->id = self::makeId();
    $this->callback = $callback;
  }

  public static function makeId()
  {
    static $i;
    if (is_null($i)) {
      $i = 'a';
    }
    return $i++;
  }

  public function __clone()
  {
    $this->callback = clone $this->callback;
    $this->id = self::makeId();
  }

  /**
   * @return string
   */
  function getId()
  {
    return $this->id;
  }

  function once()
  {
    return $this->once;
  }

  private function setOnce($once)
  {
    $this->once = $once;
    return $this;
  }

  public function __invoke(...$args)
  {
    $callback = $this->callback;
    return $callback(...$args);
  }

  /**
   * @return mixed
   */
  public function getEventName()
  {
    return $this->eventName;
  }

  /**
   * @param $eventName
   * @return bool
   */
  public function listensTo($eventName)
  {
    return is_null($this->eventName) || $this->eventName === $eventName;
  }

  /**
   * @param mixed $eventName
   */
  public function setEventName($eventName)
  {
    $this->eventName = $eventName;
    return $this;
  }

  /**
   * @param EventHandler|callback $listener
   * @param null $eventName
   * @param bool $once
   * @return EventHandler
   */
  static function make($listener, $eventName = null, $once = false)
  {
    if ($listener instanceof self) {
      if ($listener->getEventName() !== $eventName || $listener->once() !== $once) {
        return (clone $listener)
          ->setEventName($eventName)
          ->setOnce($once);
      } else {
        return $listener;
      }
    } else {
      return (new EventHandler($listener))
        ->setEventName($eventName)
        ->setOnce($once);
    }
  }

}