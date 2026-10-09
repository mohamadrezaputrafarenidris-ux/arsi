<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nim' => fake()->unique()->numerify('53142#####'),
            'nama' => fake()->name(),
            'prodi' => fake()->randomElement(['Sistem Informasi', 'Teknik Informatika', 'Manajemen Informatika']),
            'semester' => fake()->numberBetween(1, 8),
        ];
    }
}
