<?php
namespace Beebo\Concerns;

use Beebo\Contracts\Stateful;
use Beebo\SimpleState;

trait State
{
  /**
   * @var SimpleState
   */
  public $_state;

  protected function initializeState()
  {
    if (!$this instanceof Stateful) {
      throw new \Exception("State trait cannot be applied to an object that does not implement " . Stateful::class);
    }

    $this->_state = new SimpleState();

    $this->_state->on('change', function() {
      $this->emit($this->getName().'/state', $this->_state->toArray());
    });
  }

  /**
   * @return $this
   */
  protected function init()
  {
    // nothing to do
    return $this;
  }

  /**
   * @return array
   */
  function all()
  {
    return $this->_state->all();
  }

  /**
   * Count the data at $path, if it exists; if it does not
   * exist, return 0.
   * @param string $path
   * @return int
   */
  function count($path)
  {
    return $this->_state->count($path);
  }

  /**
   * @param string $path
   * @param mixed|null $default
   * @return $mixed
   */
  function get($path, $default = null)
  {
    return $this->_state->get($path, $default);
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
    return $this->_state->getArray($path, $default, $failReason, $code);
  }

  /**
   * @param $path
   * @return $this
   */
  function push($path, $value)
  {
    $this->_state->push($path, $value);
    return $this;
  }

  /**
   * @param array|string $path
   * @param mixed|null $value
   * @param $overwrite true
   * @return $this
   */
  function set($path, $value = null, $overwrite = true)
  {
    $this->_state->set($path, $value, $overwrite);
    return $this;
  }

  /**
   * @param $path
   * @return mixed
   */
  function pop($path)
  {
    return $this->_state->pop($path);
  }

  /**
   * @param $path
   * @return mixed
   */
  function shift($path)
  {
    return $this->_state->shift($path);
  }

  /**
   * @param $path
   * @param $value
   * @return $this
   */
  function unshift($path, $value)
  {
    $this->_state->unshift($path, $value);
    return $this;
  }

  /**
   * @param $path
   * @param $i
   * @param null $length
   * @return mixed
   */
  function slice($path, $i, $length = null)
  {
    return $this->_state->slice($path, $i, $length);
  }

  /**
   * @param $path
   * @param $array
   * @return $this
   */
  function merge($path, $array)
  {
    $this->_state->merge($path, $array);
    return $this;
  }

  /**
   * @param $path
   * @param int $i
   * @return $this
   */
  function increment($path, $i = 1)
  {
    $this->set($path,$this->get($path, 0) + $i);
    return $this;
  }

  /**
   * @param $path
   * @param int $d
   * @return $this
   */
  function decrement($path, $d = 1)
  {
    $this->set($path,$this->get($path, 0) - $d);
    return $this;
  }

  /**
   * @param $path
   * @return bool
   */
  function isEmpty($path)
  {
    return $this->_state->isEmpty($path);
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
   * @return State
   */
  function __set($name, $value)
  {
    return $this->set($name, $value);
  }

  public function toArray()
  {
    return [
      'id' => $this->getId(),
      'name' => $this->getName(),
      'type' => get_class($this),
      'data' => $this->all(),
    ];
  }
}