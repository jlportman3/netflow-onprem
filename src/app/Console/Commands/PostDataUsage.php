<?php

namespace App\Console\Commands;

use App\Jobs\ProcessDataUsage;
use App\Models\DataUsage;
use App\Models\NetflowOnPremise;
use Illuminate\Console\Command;

class PostDataUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sonar:post:data-usage";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Post any data usage records created to Sonar app";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $netflow = NetflowOnPremise::first();
        if (is_null($netflow)) {
            return 0;
        }

        if (DataUsage::count() === 0) {
            return 0;
        }

        $job = (new ProcessDataUsage())
            ->onQueue("default");
        dispatch($job);
        return 0;
    }
}
