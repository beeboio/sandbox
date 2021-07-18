<?php
namespace Beebo\Concerns;

use Log;
use Beebo\Exceptions\RoomDoesNotExistException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Beebo\SocketIO\Room;
use Illuminate\Database\Eloquent\Model;
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
      ->mapToAssoc(function($class) {
        $controller = app($class)->_setServer($this);
        if (!$controller instanceof ControlsSockets) {
          throw new \Exception("All Socket controllers must implement " . ControlsSockets::class);
        }
        return [$controller->_getName(), $controller];
      });

    $this->on('event', function(Event $event, ...$data) {
      try {
        try {
          $this->tryToRouteEvent($event, ...$data);
        } catch (ControllerHandlerNotFound $e) {
          if (!$this->allowControllerMethodNotFound()) {
            throw $e;
          } else {
            Log::error($e);
          }
        }
      } catch (\Exception $e) {
        if ($event->hasCallback()) {
          $error = [
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
          ];

          $event->callback([
            'errors' => [$error],
          ]);

          // TODO: use ExceptionHandler, maybe?
          if (config('app.debug')) {
            Log::error($e);
          }
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
   * @throws \Exception
   */
  protected function tryToRouteEvent(Event $event, ...$data)
  {
    // TODO: look into other ways (maybe faster) of making this safer
    $route = explode('.', preg_replace('/[^\w\.]/', '', $event->name));
    $namespace = trim($route[0] ?? null);
    $methodName = trim($route[1] ?? null);

    if ($namespace && $methodName) {
      $method = null;
      if ($controller = data_get($this->bootedControllers, $namespace)) {
        $method = $controller->_getPublicMethods()->get($methodName);
      }
      if ($method) {
        $response = $this->routeToControllerMethod($event, $controller, $method, $event, ...$data);
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

  /**
   * @param Event $request
   * @param ControlsSockets $controller
   * @param \ReflectionMethod $method
   * @param Event $event
   * @param mixed ...$data
   * @return mixed
   */
  protected function routeToControllerMethod(Event $request, ControlsSockets $controller, \ReflectionMethod $method, $event, ...$data)
  {
    $args = collect($method->getParameters())->map(function(\ReflectionParameter $p, $i) use ($controller, $method, $request, &$data) {
      // use type hints to map in parameters
      if ($class = $p->getClass()) {
        // look for Event class, pass in $request
        if ($class->getName() === Event::class) {
          return $request;
        // look for Room class and subclasses, expect $roomName, lookup Room instance
        } else if ($class->isSubclassOf(Room::class) || $class->getName() === Room::class) {
          if (count($data) < 1) {
            throw new \InvalidArgumentException("Argument #{$i} for {$controller->_getName()}.{$method->getName()} is required.");
          }
          $roomName = array_shift($data);
          if (!$this->hasRoom($roomName)) {
            throw new RoomDoesNotExistException($roomName);
          }
          return $this->rooms()->get($roomName);
        // look for User models
        } else if ($class->isSubclassOf(Authenticatable::class)) {
          return $request->user();
        // look for Eloquent models
        } else if ($class->isSubclassOf(Model::class)) {
          // TODO: findOrFail
        }
      }
      // if we couldn't use type-hinting, and $data is empty, throw an error
      if (count($data) < 1) {
        throw new \InvalidArgumentException("Argument ${$i} for {$controller->_getName()}.{$method->getName()} is required: " . $p->getName());
      }
      // otherwise, just map in the next input
      return array_shift($data);
    });

    $methodName = $method->getName();

    return $controller->$methodName(...$args);
  }

}