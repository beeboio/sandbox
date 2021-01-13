<?php
namespace Beebo\Exceptions;

class ControllerHandlerNotFound extends \Exception {

  function __construct($route)
  {
    parent::__construct("No Controller method found for route [{$route}]", 404);
  }

}