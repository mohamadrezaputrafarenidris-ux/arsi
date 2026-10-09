<?php

namespace Database\Factories;

use App\Models\DosenPa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DosenPa>
 */
class DosenPaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nip' => fake()->unique()->numerify('19################'),
            'nama' => fake()->name(),
        ];
    }
}
