<?php

namespace App\Console\Commands;

use App\Helpers\GraphQL;
use App\Models\NetflowOnPremise;
use Illuminate\Console\Command;

class InitializeNetflow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "sonar:netflow:initialize {--force : Force execution}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Initialize Netflow and register in Sonar";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $netflow = NetflowOnPremise::first();
        if (
            isset($netflow)
            && ! $this->option("force")
        ) {
            $this->error("Application appears to be initialized! Use --force option to override");
            exit;
        }

        $gql = new GraphQL();

        if ($this->option("force")) {
            $gql->post($netflow->deleteMutation());
            NetflowOnPremise::truncate();
        }


        $netflow = new NetflowOnPremise([
            "name" => env("SONAR_NETFLOW_NAME", "Env SONAR_NETFLOW_NAME unset"),
            "ip" => env("SONAR_NETFLOW_IP", "127.0.0.1"),
            "statistics" => [],
        ]);
        $response = $gql->post($netflow->createMutation());
        $body = json_decode($response->getBody());
        if (
            ! $response->getStatusCode() === 200
            || is_null($body->data->createNetflowOnPremise)
        ) {
            $this->error($body->errors[0]->message ?? "An unknown error has occurred");
            exit;
        }
        $netflow->id = $body->data->createNetflowOnPremise->id ?? null;
        $netflow->save();
    }
}
