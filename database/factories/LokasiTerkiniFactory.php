<?php

namespace Database\Factories;

use App\Models\LokasiTerkini;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LokasiTerkini>
 */
class LokasiTerkiniFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_id' => 0,
            'latitude' => fake()->latitude(-8, -6),
            'longitude' => fake()->longitude(106, 112),
            'waktu_hp' => now(),
        ];
    }
}
