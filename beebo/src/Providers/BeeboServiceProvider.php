<?php
namespace Beebo\Providers;

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
  }

  public function boot()
  {

  }

}