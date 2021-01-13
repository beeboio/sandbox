<?php

namespace App\Console\Commands\Test;

use App\Models\User;
use Illuminate\Console\Command;
use App\Notifications\TestBroadcast as TestBroadcastNotification;

class TestBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:broadcast {to} {message} {--field=email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test Broadcast message';

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
        $user = User::where($this->option('field'), $this->argument('to'))->firstOrFail();

        $user->notify(new TestBroadcastNotification($this->argument('message')));
    }
}
