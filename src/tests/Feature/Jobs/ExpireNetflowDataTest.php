<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ExpireNetflowData;
use App\Models\NetflowOnPremise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ExpireNetflowDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function nothing_processed_if_netflow_not_initialized(): void
    {
        $this->assertEquals(0, NetflowOnPremise::count());
        $this->assertTrue(Storage::exists("/testData/2024/08/28/nfcapd.202408281445"));

        $process = new ExpireNetflowData("/var/www/html/storage/app/testData");
        $process->handle();

        $this->assertTrue(Storage::exists("/testData/2024/08/28/nfcapd.202408281445"));
    }

    // The file being tested is 3821 bytes and contains data from 2024-08-28 14:26:41 - 2024-08-28 14:44:34
    #[Test]
    #[TestWith(["1000w","1", true])]
    #[TestWith(["1H","100000", true])]
    #[TestWith(["1H","1", true])]
    #[TestWith(["1000w","100000", false])]
    public function expiration_honors_max_life_and_size_settings(
        string $maxLife,
        string $maxSize,
        bool $shouldRemove
    ): void {
        NetflowOnPremise::factory()->create();
        $fullPath = Storage::path("/testData/2024/08/28/nfcapd.202408281445");
        $this->assertTrue(File::exists($fullPath));

        Config::set("sonar.netflow.max_life", $maxLife);
        Config::set("sonar.netflow.max_size", $maxSize);

        $process = new ExpireNetflowData("/var/www/html/storage/app/testData");
        $process->handle();

        if ($shouldRemove) {
            $this->assertFalse(File::exists($fullPath));
            Storage::copy(
                "/testData/ORIG-nfcapd.202408281445",
                "/testData/2024/08/28/nfcapd.202408281445"
            );
        } else {
            $this->assertTrue(File::exists($fullPath));
        }
    }
}
