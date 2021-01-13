<?php
namespace Beebo\Traits;

use Hashids\Hashids;

trait Unique
{
  /**
   * @var int
   */
  private static $__unique_id_pool = 0;

  /**
   * @return string
   * @throws \Exception
   */
  static function makeId()
  {
    static $hash;
    if (is_null($hash)) {
      $hash = new Hashids(uniqid(), 12);
    }
    return $hash->encode(++self::$__unique_id_pool);
  }
}