<?php
namespace Beebo\Traits;

trait PingPong
{
  /**
   * @var int
   */
  protected $lastMessageAt;

  /**
   * @var int
   */
  protected $createdAt;

  /**
   * @var int
   */
  protected $lastPingAt;

  /**
   * @var int
   */
  protected $lastPongAt;

  /**
   * @var bool
   */
  protected $offline = false;

  /**
   * Update the record of when the last message was received,
   * and make sure we're online.
   * @return $this
   */
  protected function wakeup()
  {
    $this->offline(false)->lastMessageAt = time();
    return $this;
  }

  /**
   * @return int
   */
  function getLastMessageAt()
  {
    return $this->lastMessageAt ?? 0;
  }

  /**
   * @return int
   */
  function getLastPongAt()
  {
    return $this->lastPongAt ?? 0;
  }

  /**
   * @return int
   */
  function getLastPingAt()
  {
    return $this->lastPingAt ?? 0;
  }

  /**
   * @return bool
   */
  public function isOffline()
  {
    return $this->offline;
  }

  /**
   * Get the current time as ms
   * @return float|int
   */
  function now()
  {
    return microtime(true) * 1000;
  }

  /**
   * Ping timeout, in seconds
   * @return int|float
   */
  function getPingTimeout()
  {
    return 5;
  }

  /**
   * Ping interval, in seconds
   * @return int|float
   */
  function getPingInterval()
  {
    return 25;
  }

  /**
   * Try to keep this Socket alive
   * @return $this
   */
  function keepAlive()
  {
    $this->ping();

    $this->after(
      $this->getPingTimeout(),
      function() {
        if ($this->getLastPongAt() < $this->getLastPingAt()) {
          $this->close();
        }
      }
    );

    return $this;
  }


}