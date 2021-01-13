<?php
namespace Beebo\Traits;

use Beebo\SocketIO\Server;

trait Timers
{
  /**
   * Do something on an interval
   * @param int|float $seconds Can be expressed as a fraction of a second
   * @param \Closure $doAgain Must not throw an \Exception
   * @return $this;
   */
  public final function every($seconds, \Closure $doAgain)
  {
    Server::loop()->addPeriodicTimer($seconds, $doAgain);
    return $this;
  }

  /**
   * Wait and then do something once
   * @param int|float $seconds
   * @param \Closure $doOnce
   * @return $this;
   */
  public final function after($seconds, \Closure $doOnce)
  {
    Server::loop()->addTimer($seconds, $doOnce);
    return $this;
  }
}