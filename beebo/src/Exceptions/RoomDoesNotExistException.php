<?php
namespace Beebo\Exceptions;

class RoomDoesNotExistException extends \Exception {

  public $roomName;

  public $roomClass;

  /**
   * RoomDoesNotExistException constructor.
   * @param $roomName
   * @param null $roomClass
   */
  public function __construct($roomName, $roomClass = null)
  {
    $this->roomName = $roomName;
    $this->roomClass = $roomClass;

    if (is_null($roomClass)) {
      parent::__construct("Room [{$roomName}] does not exist.");
    } else {
      parent::__construct("Room [{$roomName}] of type [{$roomClass}] does not exist.");
    }
  }

}