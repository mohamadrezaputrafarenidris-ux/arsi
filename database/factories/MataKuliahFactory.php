<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mulai = fake()->randomElement(['07:00', '08:00', '09:00', '10:00', '13:00']);
        $jam = (int) substr($mulai, 0, 2);

        return [
            'kode' => 'MK'.fake()->unique()->numerify('###'),
            'nama' => fake()->unique()->words(3, true),
            'sks' => fake()->numberBetween(1, 6),
            'hari' => fake()->randomElement(MataKuliah::hariOptions()),
            'jam_mulai' => $mulai,
            'jam_selesai' => sprintf('%02d:00', $jam + 2),
            'ruang' => 'R. '.fake()->numberBetween(101, 410),
        ];
    }

    /**
     * Jadwal tetap: Senin 08:00-10:30, berguna untuk menguji bentrok.
     */
    public function seninPagi(): static
    {
        return $this->state(fn () => [
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:30',
        ]);
    }

    /**
     * Jadwal berurutan tepat setelah seninPagi: Selasa 10:30-13:00.
     */
    public function selasaBerurutan(): static
    {
        return $this->state(fn () => [
            'hari' => 'Selasa',
            'jam_mulai' => '10:30',
            'jam_selesai' => '13:00',
        ]);
    }
}
