<?php
namespace Beebo\Concerns;

use Beebo\Contracts\Stateful;
use Beebo\Exceptions\AssertionException;

trait Assertive
{
  protected function initializeAssertive()
  {
    if (!$this instanceof Stateful) {
      throw new \Exception("Assertive trait cannot be applied an object that does not implement " . Stateful::class);
    }
  }

  /**
   * @param $test
   * @return $this
   * @throws AssertionException
   */
  function assert($test, $failReason = null, $code = 0)
  {
    if (!(bool) $test) {
      throw AssertionException::make($failReason, $code);
    }
    return $this;
  }

  /**
   * @param int|float $value
   * @param int|float $min
   * @param int|float $max
   * @param null $failReason
   * @param int $code
   * @return $this
   * @throws AssertionException
   */
  function assertBetween($value, $min, $max, $failReason = null, $code = 0)
  {
    return $this->assert($value >= $min && $value <= $max, $failReason, $code);
  }
}