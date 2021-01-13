<?php

namespace App\Console\Commands\Lights;

use App\Events\LightsTurnedOff;
use Illuminate\Console\Command;

class LightsOff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lights:off';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Turn the lights off';

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
        LightsTurnedOff::dispatch();
    }
}
