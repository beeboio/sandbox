<?php
namespace Beebo\Contracts;

interface Stateful
{
  /**
   * @return array
   */
  function all();

  /**
   * @param string $path
   * @return int
   */
  function count($path);

  /**
   * @param string $path
   * @param null $default
   * @return mixed
   */
  function get($path, $default = null);

  /**
   * @param $path
   * @param array|null $default
   * @param null $failReason
   * @param int $code
   * @return array
   */
  function getArray($path, array $default = null, $failReason = null, $code = 0);

  /**
   * @param string $path
   * @param null $newValue
   * @param bool $overwrite
   * @return $this
   */
  function set($path, $newValue = null, $overwrite = true);

  /**
   * @param $path
   * @param $value
   * @return $this
   */
  function push($path, $value);

  /**
   * @param $path
   * @return mixed
   */
  function pop($path);

  /**
   * @param $path
   * @return mixed
   */
  function shift($path);

  /**
   * @param $path
   * @param $value
   * @return $this
   */
  function unshift($path, $value);

  /**
   * @param $path
   * @param $offset
   * @param $length
   */
  function slice($path, $offset, $length = null);

  /**
   * @param $path
   * @param mixed ...$arrays
   * @return $this
   */
  function merge($path, ...$arrays);

  /**
   * @param $path
   * @param int $i
   * @return $this
   */
  function increment($path, $i = 1);

  /**
   * @param $path
   * @param int $d
   * @return $this
   */
  function decrement($path, $d = 1);

  /**
   * @param $path
   * @return bool
   */
  function isEmpty($path);

}