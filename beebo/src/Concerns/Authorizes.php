<?php
namespace Beebo\Concerns;

use Beebo\SocketIO\Socket;
use Illuminate\Contracts\Auth\Access\Gate;

trait Authorizes
{
  /**
   * Authorize a given action for the user attached via the given Socket.
   *
   * @param  Socket  $socket
   * @param  mixed  $ability
   * @param  mixed|array  $arguments
   * @return \Illuminate\Auth\Access\Response
   *
   * @throws \Illuminate\Auth\Access\AuthorizationException
   */
  public function authorize(Socket $socket, $ability, $arguments = [])
  {
    [$ability, $arguments] = $this->parseAbilityAndArguments($ability, $arguments);

    return app(Gate::class)->forUser($socket->user())->authorize($ability, $arguments);
  }

  /**
   * Guesses the ability's name if it wasn't provided.
   *
   * @param  mixed  $ability
   * @param  mixed|array  $arguments
   * @return array
   */
  protected function parseAbilityAndArguments($ability, $arguments)
  {
    if (is_string($ability) && strpos($ability, '\\') === false) {
      return [$ability, $arguments];
    }

    $method = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'];

    return [$this->normalizeGuessedAbilityName($method), $ability];
  }

  /**
   * Normalize the ability name that has been guessed from the method name.
   *
   * @param  string  $ability
   * @return string
   */
  protected function normalizeGuessedAbilityName($ability)
  {
    $map = $this->resourceAbilityMap();

    return $map[$ability] ?? $ability;
  }

  /**
   * Get the map of resource methods to ability names.
   *
   * @return array
   */
  protected function resourceAbilityMap()
  {
    return [
      'index' => 'viewAny',
      'show' => 'view',
      'create' => 'create',
      'store' => 'create',
      'edit' => 'update',
      'update' => 'update',
      'destroy' => 'delete',
    ];
  }

  /**
   * Get the list of resource methods which do not have model parameters.
   *
   * @return array
   */
  protected function resourceMethodsWithoutModels()
  {
    return ['index', 'create', 'store'];
  }
}