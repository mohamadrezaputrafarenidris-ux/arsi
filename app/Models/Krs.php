<?php

namespace App\Models;

use App\Enums\KrsStatus;
use Database\Factories\KrsFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['mahasiswa_id', 'status', 'diajukan_pada', 'diputuskan_pada'])]
class Krs extends Model
{
    /** @use HasFactory<KrsFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => KrsStatus::class,
            'diajukan_pada' => 'datetime',
            'diputuskan_pada' => 'datetime',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * @return BelongsToMany<MataKuliah, $this>
     */
    public function mataKuliahs(): BelongsToMany
    {
        return $this->belongsToMany(MataKuliah::class, 'krs_mata_kuliah')->withTimestamps();
    }

    /**
     * Total SKS dari mata kuliah yang dipilih.
     */
    public function totalSks(): int
    {
        return (int) $this->mataKuliahs()->sum('sks');
    }
}
