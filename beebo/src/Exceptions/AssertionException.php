<?php
namespace Beebo\Exceptions;

class AssertionException extends \Exception
{
  /**
   * @param $failReason
   * @param int $code
   * @return static
   */
  static function make($failReason, $code = 0)
  {
    return new static($failReason, $code);
  }
}