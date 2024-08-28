<?php

namespace Tests\Feature\Commands;

use App\Jobs\ProcessDataUsage;
use App\Models\DataUsage;
use App\Models\NetflowOnPremise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class PostDataUsageTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function job_is_not_submitted_if_initialization_has_not_happened(): void
    {
        Queue::fake();
        $this->assertEquals(0, NetflowOnPremise::count());

        $this->artisan("sonar:post:data-usage")
            ->assertSuccessful();

        Queue::assertNotPushed(ProcessDataUsage::class);
    }

    #[Test]
    public function job_is_not_submitted_if_no_data_usage(): void
    {
        Queue::fake();
        NetflowOnPremise::factory()->create();
        $this->assertEquals(1, NetflowOnPremise::count());

        $this->assertEquals(0, DataUsage::count());

        $this->artisan("sonar:post:data-usage")
            ->assertSuccessful();

        Queue::assertNotPushed(ProcessDataUsage::class);
    }

    #[Test]
    public function job_is_submitted_if_initialized_and_data_usage(): void
    {
        Queue::fake();
        NetflowOnPremise::factory()->create();
        $this->assertEquals(1, NetflowOnPremise::count());

        DataUsage::factory()->create();
        $this->assertEquals(1, DataUsage::count());

        $this->artisan("sonar:post:data-usage")
            ->assertSuccessful();

        Queue::assertPushedOn(
            "default",
            ProcessDataUsage::class,
        );
    }
}
