<?php

namespace App\Services;

use App\Models\Krs;
use App\Models\MataKuliah;
use Illuminate\Support\Collection;

/**
 * Seluruh aturan validasi pengajuan KRS berada di satu tempat
 * agar mudah diuji secara terpisah dari controller.
 */
class KrsValidator
{
    public const MAX_SKS = 24;

    /**
     * Validasi isi KRS. Mengembalikan SEMUA kesalahan sekaligus.
     *
     * @return array<int, string> daftar pesan kesalahan berbahasa Indonesia
     */
    public function validate(Krs $krs): array
    {
        return $this->validateCollection($krs->mataKuliahs()->get());
    }

    /**
     * Validasi terhadap kumpulan mata kuliah (dipakai juga oleh unit test).
     *
     * @param  Collection<int, MataKuliah>  $mataKuliahs
     * @return array<int, string>
     */
    public function validateCollection(Collection $mataKuliahs): array
    {
        $errors = [];

        if ($mataKuliahs->isEmpty()) {
            $errors[] = 'KRS minimal harus berisi 1 mata kuliah.';
        }

        $totalSks = (int) $mataKuliahs->sum('sks');

        if ($totalSks > self::MAX_SKS) {
            $errors[] = "Total SKS {$totalSks} melebihi batas maksimal ".self::MAX_SKS.' SKS.';
        }

        $duplicates = $mataKuliahs
            ->groupBy(fn (MataKuliah $mk) => $mk->kode)
            ->filter(fn (Collection $group) => $group->count() > 1);

        foreach ($duplicates as $kode => $group) {
            $errors[] = "Mata kuliah {$kode} dipilih lebih dari satu kali.";
        }

        foreach ($this->conflicts($mataKuliahs) as [$a, $b]) {
            $errors[] = sprintf(
                'Jadwal bentrok: %s %s (%s %s-%s) dengan %s %s (%s %s-%s).',
                $a->kode, $a->nama, $a->hari, $this->formatJam($a->jam_mulai), $this->formatJam($a->jam_selesai),
                $b->kode, $b->nama, $b->hari, $this->formatJam($b->jam_mulai), $this->formatJam($b->jam_selesai),
            );
        }

        return $errors;
    }

    /**
     * Cari pasangan mata kuliah yang jadwalnya saling bertabrakan.
     *
     * Bentrok = hari sama DAN mulai A < selesai B DAN mulai B < selesai A.
     * Selesai tepat saat mata kuliah lain mulai bukan bentrok.
     *
     * @param  Collection<int, MataKuliah>  $mataKuliahs
     * @return array<int, array{0: MataKuliah, 1: MataKuliah}>
     */
    public function conflicts(Collection $mataKuliahs): array
    {
        $conflicts = [];
        $items = $mataKuliahs->values();

        for ($i = 0; $i < $items->count(); $i++) {
            for ($j = $i + 1; $j < $items->count(); $j++) {
                $a = $items[$i];
                $b = $items[$j];

                if ($this->isConflict($a, $b)) {
                    $conflicts[] = [$a, $b];
                }
            }
        }

        return $conflicts;
    }

    public function isConflict(MataKuliah $a, MataKuliah $b): bool
    {
        if ($a->hari !== $b->hari) {
            return false;
        }

        $mulaiA = $this->toMinutes($a->jam_mulai);
        $selesaiA = $this->toMinutes($a->jam_selesai);
        $mulaiB = $this->toMinutes($b->jam_mulai);
        $selesaiB = $this->toMinutes($b->jam_selesai);

        return $mulaiA < $selesaiB && $mulaiB < $selesaiA;
    }

    /**
     * Ubah "HH:MM:SS" atau "HH:MM" menjadi menit sejak tengah malam.
     */
    private function toMinutes(string $jam): int
    {
        [$hour, $minute] = array_map('intval', explode(':', substr($jam, 0, 5)));

        return $hour * 60 + $minute;
    }

    private function formatJam(string $jam): string
    {
        return substr($jam, 0, 5);
    }
}
