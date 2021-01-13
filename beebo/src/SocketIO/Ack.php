<?php
namespace Beebo\SocketIO;

class Ack
{
  /**
   * @var int
   */
  protected static $pool = -1;

  /**
   * @var array
   */
  protected static $buffer = [];

  /**
   * @var \Closure
   */
  private $callback;

  private $id;

  /**
   * Acknowledgement constructor.
   * @param \Closure $callback
   */
  private function __construct(\Closure $callback)
  {
    $this->callback = $callback;
    $this->id = ++self::$pool;
  }

  /**
   * @return int
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @param \Closure $callback
   */
  static function remember(\Closure $callback)
  {
    $ack = new self($callback);
    self::$buffer[$ack->getId()] = $ack;
    return $ack;
  }

  /**
   * @param $id
   * @return Ack|null
   */
  static function recall($id)
  {
    if (!empty(self::$buffer[$id])) {
      return self::$buffer[$id];
    }
  }

  /**
   * @param mixed ...$data
   * @return mixed
   */
  public function __invoke(...$data)
  {
    return $this->call(...$data);
  }

  /**
   * @param mixed ...$data
   * @return mixed
   */
  public function call(...$data)
  {
    $callback = $this->callback;
    return $callback(...$data);
  }
}