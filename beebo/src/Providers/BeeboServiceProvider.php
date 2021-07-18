<?php
namespace Beebo\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Beebo\Console\Commands;

class BeeboServiceProvider extends ServiceProvider
{

  public function register()
  {
    $this->commands([
      Commands\Restart::class,
      Commands\Serve::class,
    ]);

    Collection::macro('toAssoc', function () {
      return $this->reduce(function ($assoc, $keyValuePair) {
        list($key, $value) = $keyValuePair;
        $assoc[$key] = $value;
        return $assoc;
      }, new static);
    });

    Collection::macro('mapToAssoc', function ($callback) {
      return $this->map($callback)->toAssoc();
    });
  }

  public function boot()
  {

  }

}