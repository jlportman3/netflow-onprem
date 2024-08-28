<?php

namespace Tests\Feature\Commands;

use App\Jobs\ExpireNetflowData;
use App\Models\NetflowOnPremise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class NetflowDataExpireTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function job_is_not_submitted_if_initialization_has_not_happened(): void
    {
        Queue::fake();
        $this->assertEquals(0, NetflowOnPremise::count());

        $this->artisan("sonar:netflow:expire")
            ->assertSuccessful();

        Queue::assertNotPushed(ExpireNetflowData::class);
    }

    #[Test]
    public function job_is_submitted_if_initialized(): void
    {
        Queue::fake();
        NetflowOnPremise::factory()->create();
        $this->assertEquals(1, NetflowOnPremise::count());

        $this->artisan("sonar:netflow:expire")
            ->assertSuccessful();

        Queue::assertPushedOn(
            "default",
            ExpireNetflowData::class,
        );
    }
}
