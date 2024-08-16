<?php

namespace Tests\Feature\Commands;

use App\Models\NetflowOnPremise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class InitializeNetflowTest extends TestCase
{
    use RefreshDatabase;
    #[Test]
    public function initializing_netflow_succeeds_if_environment_variables_setup(): void
    {
        $this->assertEquals(0, NetflowOnPremise::count());

        $this->artisan("sonar:netflow:initialize")
            ->assertSuccessful();

        $netflow = NetflowOnPremise::first();
        $this->assertEquals(env("SONAR_NETFLOW_NAME", "Env SONAR_NETFLOW_NAME unset"), $netflow->name);
        $this->assertEquals(env("SONAR_NETFLOW_IP", "127.0.0.1"), $netflow->ip);
        $this->assertNull($netflow->last_processed_timestamp);
        $this->assertNull($netflow->last_processed_filename);
        $this->assertNull($netflow->last_processed_size);
    }

    #[Test]
    public function running_command_a_second_time_fails(): void
    {
        $this->fail("TODO: LW Test needed");
    }

    #[Test]
    public function running_command_a_second_time_with_force_succeeds(): void
    {
        $this->fail("TODO: LW Test needed");
    }
}
