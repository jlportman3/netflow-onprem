<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetflowOnPremise extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_processed_timestamp' => 'datetime',
            'statistics' => 'array',
        ];
    }

    public function createMutation(): array
    {
        $query = <<<GQL
mutation createMutation(\$input: CreateNetflowOnPremiseMutationInput) {
  createNetflowOnPremise(input: \$input) {
    id
    name
    ip
  }
}
GQL;

        $variables = [
            "input" => [
                "name" => $this->name,
                "ip" => $this->ip,
            ]
        ];

        return [
            "query" => $query,
            "variables" => $variables
        ];
    }

    public function deleteMutation(): array
    {
        $query = <<<GQL
mutation deleteMutation {
  deleteNetflowOnPremise(id: {$this->id}) {
    success
    message
  }
}
GQL;

        return [
            "query" => $query,
        ];
    }
}
