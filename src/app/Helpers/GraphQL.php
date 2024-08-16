<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use stdClass;

class GraphQL
{
    public ?Client $client = null;

    public function __construct()
    {
        $this->client = new Client([
            "base_uri" => env("SONAR_URL") . "/api/graphql",
            "timeout" => 60,
            "headers" => [
                "Accept" => "application/json",
                "Authorization" => "Bearer " . env("SONAR_TOKEN"),
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
