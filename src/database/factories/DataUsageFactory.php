<?php

namespace Database\Factories;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DataUsage>
 */
class DataUsageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "end_time" => Carbon::now()->toISOString(),
            "bytes_in" => fake()->randomNumber(7),
            "bytes_out" => fake()->randomNumber(7),
            'account_id' => function () {
                return (Account::first() ?? Account::factory()->create())->id;
            },
        ];
    }
}
