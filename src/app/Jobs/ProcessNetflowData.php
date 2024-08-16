<?php

namespace App\Jobs;

use App\Helpers\GraphQL;
use App\Models\NetflowOnPremise;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessNetflowData implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function __construct(
        public string $file,
    ) {
        //
    }

    public function handle(): void
    {
        $netflow = NetflowOnPremise::first();

        if (is_null($netflow)) {
            return;
        }

        $filename = basename($this->file);
        $size = Storage::size($this->file);

        if ($filename === "" || $size === 0) {
            return;
        }

        // If we have never processed files then initialize statistics and set the last processed filename to the one
        // prior to what we received.
        if (is_null($netflow->last_processed_filename)) {
            $this->initializeNetflow($netflow, $filename);;
        }
        $statistics = $netflow->statistics;

        do {
            $dumpFile = $this->incrementFile($netflow);
            if ($dumpFile === "") {
                throw new Exception("Corrupt last processed filename, cannot continue");
            }
            $statistics["last"]["bytes_in"] = 0;
            $statistics["last"]["bytes_out"] = 0;
            $statistics["last"]["flows"] = 0;

            $netflow->last_processed_timestamp = Carbon::now("UTC")->toDateTimeString();
            $statistics = $this->adjustTotals($statistics);
            $netflow->statistics = $statistics;
            $this->updateProgress($netflow);
        } while ($netflow->last_processed_filename !== $filename);
    }

    public function updateProgress(NetflowOnPremise $netflow): void
    {
        $gql = new GraphQL();
        $response = $gql->post($netflow->updateMutation());
        $body = json_decode($response->getBody());
        if (
            ! $response->getStatusCode() === 200
            || is_null($body?->data?->updateNetflowOnPremise)
        ) {
            throw new Exception($body->errors[0]->message ?? "Could not update progress!");
        }
        # $netflow->save();
    }

    private function adjustTotals(array $statistics): array
    {
        $statistics["total"]["files"]++;
        $statistics["total"]["bytes_in"] += $statistics["last"]["bytes_in"];
        $statistics["total"]["bytes_out"] += $statistics["last"]["bytes_out"];
        $statistics["total"]["flows"] += $statistics["last"]["flows"];

        return $statistics;
    }

    private function incrementFile(NetflowOnPremise $netflow): string
    {
        $fileParts = explode(".", $netflow->last_processed_filename);
        if (count($fileParts) !== 2 || $fileParts[0] !== "nfcapd") {
            return "";
        }
        $lastDate = Carbon::parse($fileParts[1]);
        $lastDate->addMinutes(5);

        $netflow->last_processed_filename = "nfcapd." . $lastDate->format("YmdHi");
        $filename = "/netflowData/" . $lastDate->year . "/" . $lastDate->format("m") . "/" .
            $lastDate->format("d") . "/" . $netflow->last_processed_filename;
        //  This attempts to skip missing filenames until we are in the future
        if (! Storage::exists($filename)) {
            if ($lastDate->isFuture()) {
                return "";
            }
            return $this->incrementFile($netflow);
        }
        $netflow->last_processed_size = Storage::size($filename);
        return $filename;
    }

    private function initializeNetflow(NetflowOnPremise $netflow, string $filename): void
    {
        $statistics = [
            "last" => [
                "bytes_in" => 0,
                "bytes_out" => 0,
                "flows" => 0,
            ],
            "total" => [
                "files" => 0,
                "bytes_in" => 0,
                "bytes_out" => 0,
                "flows" => 0,
            ],
        ];
        $fileParts = explode(".", $filename);
        if (count($fileParts) !== 2 || $fileParts[0] !== "nfcapd") {
            throw new Exception("Cannot initialize application, unknown filename format: '{$filename}'");
        }
        $lastDate = Carbon::parse($fileParts[1]);
        $lastDate->subMinutes(5);

        $netflow->last_processed_filename = "nfcapd." . $lastDate->format("YmdHi");
        $netflow->last_processed_size = 0;
        $netflow->statistics = $statistics;
    }
}
