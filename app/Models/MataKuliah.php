<?php

namespace App\Models;

use Database\Factories\MataKuliahFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['kode', 'nama', 'sks', 'hari', 'jam_mulai', 'jam_selesai', 'ruang'])]
class MataKuliah extends Model
{
    /** @use HasFactory<MataKuliahFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sks' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<MataKuliah, $this>
     */
    public function krs(): BelongsToMany
    {
        return $this->belongsToMany(Krs::class, 'krs_mata_kuliah');
    }

    /**
     * Nama hari yang diperbolehkan.
     *
     * @return list<string>
     */
    public static function hariOptions(): array
    {
        return ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    }
}
