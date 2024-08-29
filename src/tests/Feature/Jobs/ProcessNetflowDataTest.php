<?php

namespace Tests\Feature\Jobs;

use App\Helpers\GraphQL;
use App\Jobs\ProcessNetflowData;
use App\Models\Account;
use App\Models\DataUsage;
use App\Models\NetflowOnPremise;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProcessNetflowDataTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function nothing_processed_if_netflow_not_initialized(): void
    {
        $this->assertEquals(0, NetflowOnPremise::count());
        $this->assertEquals(0, DataUsage::count());

        $process = new ProcessNetflowData("");
        $process->handle();

        $this->assertEquals(0, DataUsage::count());
    }

    #[Test]
    public function invalid_file_for_processing_causes_exception(): void
    {
        NetflowOnPremise::factory()->create();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("File does not exist: 'NOT REAL NAME'");

        $process = new ProcessNetflowData("NOT REAL NAME");
        $process->handle();
    }

    #[Test]
    public function zero_byte_files_are_quietly_ignored(): void
    {
        NetflowOnPremise::factory()->create();
        $this->assertEquals(0, DataUsage::count());

        $process = new ProcessNetflowData("testData/file.zerobytes");
        $process->handle();

        $this->assertEquals(0, DataUsage::count());
    }

    // This test (and others) rely on the fact that we have a clean demo seeded instance that the environment is pointed
    // at and of course that the token is valid so that it can access the instance.
    #[Test]
    public function a_valid_file_updates_statistics_creates_usage_and_saves_account_counters(): void
    {
        $this->artisan("sonar:netflow:initialize")
            ->assertSuccessful();
        $netflow = NetflowOnPremise::first();
        $this->assertNull($netflow->last_processed_filename);

        $this->assertEquals(0, DataUsage::count());

        $process = new ProcessNetflowData("/testData/2024/08/28/nfcapd.202408281445", "/testData/");
        $process->handle();

        $netflow->refresh();
        $statistics = $netflow->statistics;
        $this->assertEqualsCanonicalizing(
            [
                "last" => [
                    "bytes_in" => 0,
                    "bytes_out" => 2795,
                    "flows" => 2,
                ],
                "total" => [
                    "files" => 1,
                    "bytes_in" => 0,
                    "bytes_out" => 2795,
                    "flows" => 2,
                ],
                "usage_records" => 2,
            ],
            $statistics
        );

        $this->assertEquals(2, DataUsage::count());
        $this->assertEquals(0, DataUsage::sum("bytes_in"));
        $this->assertEquals(2795, DataUsage::sum("bytes_out"));
        $this->assertEquals(2, DataUsage::where("account_id", 3)->count());

        $this->assertEquals(1, Account::count());
        $account = Account::first();
        $this->assertEquals(3, $account->id);
        $this->assertEquals(0, $account->bytes_in);
        $this->assertEquals(2795, $account->bytes_out);

        $this->appCleanup();
    }

    private function appCleanup(): void
    {
        $gql = new GraphQL();
        $netflow = NetflowOnPremise::first();
        $gql->post($netflow->deleteMutation());
    }
}
