<?php

namespace App\Console\Commands;

use App\Jobs\ExpireNetflowData;
use App\Models\NetflowOnPremise;
use Illuminate\Console\Command;

class NetflowDataExpire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sonar:netflow:expire";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Expire the raw Netflow data files to conserve storage";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $netflow = NetflowOnPremise::first();
        if (is_null($netflow)) {
            exit;
        }
        $job = (new ExpireNetflowData())
            ->onQueue("default");
        dispatch($job);
    }
}
