<?php

namespace App\Console\Commands\Make;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {name} {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user';

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
        $password = $this->secret('Create password:');

        $user = new User([
          'name' => $this->argument('name'),
          'email' => $this->argument('email'),
          'password' => \Hash::make($password),
        ]);

        $user->save();

        $this->info("User #{$user->id} created");
    }
}
