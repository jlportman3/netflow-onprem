<?php

namespace App\Jobs;

use App\Models\NetflowOnPremise;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpireNetflowData implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $netflow = NetflowOnPremise::first();
        if (is_null($netflow)) {
            return;
        }

        $maxLife = config("sonar.netflow.max_life");
        $maxSize = config("sonar.netflow.max_size");

        exec("/usr/local/bin/nfexpire -t {$maxLife} -s {$maxSize} -e /var/www/html/storage/app/netflowData",$output,$returnVar);
        if ($returnVar !== 0)
        {
            $outputAsLine = implode(",",$output);
            throw new Exception("NFEXPIRE ERROR: {$returnVar} ({$outputAsLine})!");
        }
    }
}
