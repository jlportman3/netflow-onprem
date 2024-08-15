<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitializeNetflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sonar:netflow:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Netflow and register in Sonar';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
