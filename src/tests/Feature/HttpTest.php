<?php

namespace Tests\Feature;

use App\Jobs\ProcessNetflowData;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HttpTest extends TestCase
{
    #[Test]
    public function application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    #[Test]
    public function calling_process_data_accepts_filename_and_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->postJson(
            "/api/process_data",
            [
                "file" => "nfcapd.202408150000",
            ],
            [
                "Accept" => "application/json",
                "Content-type" => "application/json",
            ]
        );
        $response->assertStatus(200);

        Queue::assertPushedOn(
            "default",
            ProcessNetflowData::class,
            function ($job) {
                return $job->file === "nfcapd.202408150000";
            }
        );

    }
}
