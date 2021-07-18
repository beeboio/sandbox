<?php
namespace Beebo;

use Beebo\Concerns\Bootable;
use Beebo\Concerns\Listens;
use Beebo\Contracts\Stateful;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class SimpleState implements Arrayable, Stateful
{
  use Bootable, Listens;

  protected $data;

  public function __construct(array $data = [])
  {
    $this->bootIfNotBooted();
    $this->initializeTraits();

    $this->data = $data;
  }

  function all()
  {
    return $this->data;
  }

  /**
   * Count the data at $path, if it exists; if it does not
   * exist, return 0.
   * @param string $path
   * @return int
   */
  function count($path)
  {
    return count(data_get($this->data, $path) ?: []);
  }

  /**
   * @param $path
   * @return $mixed
   */
  function get($path, $default = null)
  {
    return data_get($this->data, $path, $default);
  }

  /**
   * @param $path
   * @param array|null $default
   * @param string|null $failReason
   * @param int $code
   * @return array
   */
  function getArray($path, array $default = null, $failReason = null, $code = 0)
  {
    $array = $this->get($path, $default);
    if (!is_array($array)) {
      throw new \InvalidArgumentException($failReason ?? "{$path} is expected to be an array", $code);
    }
    return $array;
  }

  /**
   * @param $path
   * @param $value
   * @return $this
   */
  function push($path, $value)
  {
    $array = $this->getArray($path, []);
    $array[] = $value;
    $this->set($path, $array);
    return $this;
  }

  /**
   * @param array|string $path
   * @param mixed|null $newValue
   * @param bool $overwrite true
   * @return $this
   */
  function set($path, $newValue = null, $overwrite = true)
  {
    if (is_array($path)) {
      $oldValues = Arr::only($this->data, array_keys($path));
      $this->data = $path;
      $this->trigger('change', $path, $oldValues);
    } else {
      $oldValue = data_get($this->data, $path);
      data_set($this->data, $path, $newValue, $overwrite);
      $this->trigger('change', $this->get($path), $oldValue);
    }
    return $this;
  }

  /**
   * @param $path
   * @return mixed
   */
  function pop($path)
  {
    $array = $this->getArray($path, []);
    $value = array_pop($array);
    $this->set($path, $array);
    return $value;
  }

  /**
   * @param $path
   * @return mixed
   */
  function shift($path)
  {
    $array = $this->getArray($path, []);
    $value = array_shift($array);
    $this->set($path, $array);
    return $value;
  }

  /**
   * @param $path
   * @param $value
   * @return $this
   */
  function unshift($path, $value)
  {
    $array = $this->getArray($path, []);
    array_unshift($array, $value);
    $this->set($path, $array);
    return $value;
  }

  /**
   * @param $path
   * @param $offset
   * @param $length
   */
  function slice($path, $offset, $length = null)
  {
    $array = $this->getArray($path, []);
    $value = array_slice($array, $offset, $length);
    $this->set($path, $array);
    return $value;
  }

  /**
   * @param $path
   * @param mixed ...$arrays
   * @return $this
   */
  function merge($path, ...$arrays)
  {
    $array = array_merge($this->getArray($path, []), ...$arrays);
    $this->set($path, $array);
    return $this;
  }

  /**
   * @param $path
   * @param int $i
   * @return $this
   */
  function increment($path, $i = 1)
  {
    return $this->set($path, $this->get($path, 0) + $i);
  }

  /**
   * @param $path
   * @param int $d
   * @return $this
   */
  function decrement($path, $d = 1)
  {
    return $this->set($path, $this->get($path, 0) - $d);
  }

  /**
   * @param $path
   * @return bool
   */
  function isEmpty($path)
  {
    $value = $this->get($path);
    return empty($value);
  }

  /**
   * @param $name
   * @return mixed
   */
  function __get($name)
  {
    return $this->get($name);
  }

  /**
   * @param $name
   * @param $value
   * @return $this
   */
  function __set($name, $value)
  {
    return $this->set($name, $value);
  }

  /**
   * @return array
   */
  public function toArray()
  {
    return $this->all();
  }

}