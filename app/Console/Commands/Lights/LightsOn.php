<?php

namespace App\Console\Commands\Lights;

use App\Events\LightsTurnedOn;
use Illuminate\Console\Command;

class LightsOn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lights:on';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Turn the lights on';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        LightsTurnedOn::dispatch();
    }
}
