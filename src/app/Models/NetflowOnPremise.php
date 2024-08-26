<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function readQuery(): array {
        $query = <<<GQL
query netflowOnPremises {
  netflow_on_premises(id: {$this->id}) {
    entities {
      id
      name
      ip
      last_processed_filename
      last_processed_size
      last_processed_timestamp
      statistics
    }
  }
}
GQL;

        return [
            "query" => $query,
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

    public function updateMutation(): array
    {
        $query = <<<GQL
mutation updateMutation(\$input: UpdateNetflowOnPremiseMutationInput) {
  updateNetflowOnPremise(id: {$this->id} input: \$input) {
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
                "statistics" => json_encode($this->statistics),
            ]
        ];
        if (isset($this->last_processed_timestamp)) {
            $variables["input"]["last_processed_timestamp"] = Carbon::parse($this->last_processed_timestamp)->toDateTimeString();
        } else {
            $variables["input"]["unset_last_processed_timestamp"] = true;
        }
        if (isset($this->last_processed_filename)) {
            $variables["input"]["last_processed_filename"] = $this->last_processed_filename;
        } else {
            $variables["input"]["unset_last_processed_filename"] = true;
        }
        if (isset($this->last_processed_size)) {
            $variables["input"]["last_processed_size"] = $this->last_processed_size;
        } else {
            $variables["input"]["unset_last_processed_size"] = true;
        }

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
