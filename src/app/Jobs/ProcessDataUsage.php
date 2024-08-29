<?php

namespace App\Jobs;

use App\Helpers\GraphQL;
use App\Models\DataUsage;
use App\Models\NetflowOnPremise;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessDataUsage implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public function handle(): void
    {
        $netflow = NetflowOnPremise::first();
        if (is_null($netflow)) {
            return;
        }

        $dataUsageCount = DataUsage::count();
        do {
            $dataUsages = DataUsage::orderBy("end_time", "asc")
                ->limit(1000)
                ->get();
            if (is_null($dataUsages)) {
                break;
            }
            $uploadBatch = [];
            foreach ($dataUsages as $dataUsage) {
                $uploadBatch[] = [
                    "time" => $dataUsage->end_time->toISOString(),
                    "inbytes" => $dataUsage->bytes_in,
                    "outbytes" => $dataUsage->bytes_out,
                    "account_id" => $dataUsage->account_id,
                    "data_source_identifier" => $netflow->ip,
                    "data_source_parent" => "Netflow On Premise",
                ];
            }
            $batchSent = $this->sendBatch($uploadBatch);
            if (! $batchSent) {
                break;
            }
            DataUsage::whereIn("id", $dataUsages->pluck("id"))->delete();
            $newUsageCount = DataUsage::count();
        } while ($newUsageCount > 0  && $newUsageCount !== $dataUsageCount);
    }

    private function sendBatch(array $uploadBatch): bool
    {
        $query = <<<GQL
mutation createDataUsagesMutation(\$input: [CreateDataUsageMutationInput]) {
    createDataUsages(input: \$input) { success message } }
GQL;

        $variables = [
            "input" => $uploadBatch,
        ];

        $request = [
            "query" => $query,
            "variables" => $variables
        ];

        $gql = new GraphQL();
        $response = $gql->post($request);
        $body = json_decode($response->getBody());
        if (
            ! $response->getStatusCode() === 200
            || is_null($body->data->createDataUsages)
            || $body->data->createDataUsages->success == false
        ) {
            return false;
        }
        return true;
    }
}
