<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class GraphQL
{
    public ?Client $client = null;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => Config::get("sonar.url") . "/api/graphql",
            "timeout" => 60,
            "headers" => [
                "Accept" => "application/json",
                "Authorization" => "Bearer " . Config::get("sonar.token"),
            ],
        ]);
    }

    public function post(array $request): ResponseInterface
    {
        return $this->client->post(
            "",
            [
              "json" => $request,
            ]
        );
    }
}
