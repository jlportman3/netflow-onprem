<?php

namespace Tests\Feature\Jobs;

use App\Helpers\GraphQL;
use App\Jobs\ProcessDataUsage;
use App\Jobs\ProcessNetflowData;
use App\Models\Account;
use App\Models\DataUsage;
use App\Models\NetflowOnPremise;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProcessDataUsageTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function nothing_processed_if_netflow_not_initialized(): void
    {
        $this->assertEquals(0, NetflowOnPremise::count());
        Account::factory()->create([
            "id" => 3,
        ]);
        DataUsage::factory()->create([
            "end_time" => Carbon::now("UTC")->toISOString(),
            "account_id" => 3,
        ]);
        $this->assertEquals(1, DataUsage::count());

        $process = new ProcessDataUsage();
        $process->handle();

        $this->assertEquals(1, DataUsage::count());
    }

    #[Test]
    public function once_initialized_data_is_processed_and_statistics_updated(): void
    {
        $this->artisan("sonar:netflow:initialize")
            ->assertSuccessful();
        $netflow = NetflowOnPremise::first();

        $this->assertEquals(0, DataUsage::count());

        $process = new ProcessNetflowData("/testData/2024/08/28/nfcapd.202408281445", "/testData/");
        $process->handle();

        $netflow->refresh();
        $statistics = $netflow->statistics;
        $this->assertEquals(2, $statistics["usage_records"]);
        $this->assertEquals(2, DataUsage::count());

        $process = new ProcessDataUsage();
        $process->handle();

        // Statistics not checked again as they are only updated in the process of netflow data
        $this->assertEquals(0, DataUsage::count());

        $this->appCleanup();
    }

    private function appCleanup(): void
    {
        $gql = new GraphQL();
        $netflow = NetflowOnPremise::first();
        $gql->post($netflow->deleteMutation());
    }
}
