<?php

namespace App\Enums;

enum KrsStatus: string
{
    case Draft = 'draft';
    case MenungguPersetujuan = 'menunggu';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';

    /**
     * Nama tampilan status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::MenungguPersetujuan => 'Menunggu Persetujuan',
            self::Disetujui => 'Disetujui',
            self::Ditolak => 'Ditolak',
        };
    }

    /**
     * Kelas CSS badge status (datar dan kalem).
     */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'badge-draft',
            self::MenungguPersetujuan => 'badge-menunggu',
            self::Disetujui => 'badge-disetujui',
            self::Ditolak => 'badge-ditolak',
        };
    }

    /**
     * Status boleh diubah isi KRS-nya hanya saat Draft.
     */
    public function isEditable(): bool
    {
        return $this === self::Draft;
    }
}
