<?php

namespace Database\Factories;

use App\Enums\KrsStatus;
use App\Models\Krs;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Krs>
 */
class KrsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'status' => KrsStatus::Draft,
        ];
    }

    public function menunggu(): static
    {
        return $this->state(fn () => [
            'status' => KrsStatus::MenungguPersetujuan,
            'diajukan_pada' => now(),
        ]);
    }

    public function disetujui(): static
    {
        return $this->state(fn () => [
            'status' => KrsStatus::Disetujui,
            'diajukan_pada' => now(),
            'diputuskan_pada' => now(),
        ]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => [
            'status' => KrsStatus::Ditolak,
            'diajukan_pada' => now(),
            'diputuskan_pada' => now(),
        ]);
    }
}
