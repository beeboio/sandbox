<?php
namespace Beebo\Concerns;

use Log;
use Beebo\Contracts\ControlsSockets;
use Illuminate\Support\Collection;
use Beebo\Contracts\Messenger;
use Beebo\Exceptions\ControllerHandlerNotFound;
use Beebo\SocketIO\Event;

/**
 * Trait Controllers
 * @package Beebo\Traits
 */
trait Controllers
{
  /**
   * @var Collection<Sockets>
   */
  protected $bootedControllers;

  /**
   * By default, if no Controller or Method can be found to handle
   * an event, that condition is ignored. This fits the emitter
   * pattern on which this router is based.
   * @return bool
   */
  function allowControllerMethodNotFound()
  {
    return !!($this->allowControllerMethodNotFound ?? true);
  }

  /**
   * Initialize the Controllers trait
   */
  protected function initializeControllers()
  {
    $this->bootedControllers = collect($this->controllers ?? [])
      ->map(function($class) {
        // TODO make sure $class implements ControlsSockets
        return app($class)->_setServer($this);
      });

    $this->on('event', function(Event $event, ...$data) {
      try {
        $this->tryToRouteEvent($event, ...$data);
      } catch (ControllerHandlerNotFound $e) {
        if (!$this->allowControllerMethodNotFound()) {
          throw $e;
        } else {
          Log::error($e);
        }
      }
    });
  }

  /**
   * Try to route the given Event to a controller
   * @param Event $event
   * @param mixed ...$data
   * @throws ControllerHandlerNotFound
   */
  protected function tryToRouteEvent(Event $event, ...$data)
  {
    // TODO: look into other ways (maybe faster) of making this safer
    $route = explode('.', preg_replace('/[^\w\.]/', '', $event->name));
    $namespace = trim($route[0] ?? null);
    $method = trim($route[1] ?? null);

    if ($namespace && $method) {
      $controller = $this->bootedControllers
        ->filter(function(ControlsSockets $controller) use ($namespace, $method) {
          return
            $controller->_getName() === $namespace
            && $controller->_getPublicMethods()->contains($method);
        })
        ->first();

      if ($controller) {
        $response = $controller->$method($event, ...$data);
        if ($event->hasCallback()) {
          $event->callback($response);
        }
      } else {
        throw new ControllerHandlerNotFound($event->name);
      }

    } else if ($namespace === 'message') {
      $controller = $this->bootedControllers
        ->filter(function(ControlsSockets $controller) {
          return $controller instanceof Messenger;
        })
        ->first();

      if ($controller && $controller instanceof Messenger) {
        $controller->message($event, ...$data);
      } else {
        throw new ControllerHandlerNotFound($event->name);
      }

    } else {
      throw new ControllerHandlerNotFound($event->name);
    }
  }

}