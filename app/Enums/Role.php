<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Mahasiswa = 'mahasiswa';
    case DosenPa = 'dosen_pa';

    /**
     * Nama tampilan role.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Mahasiswa => 'Mahasiswa',
            self::DosenPa => 'Dosen PA',
        };
    }

    /**
     * Nama route tujuan setelah login sesuai role.
     */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Mahasiswa => 'mahasiswa.krs.index',
            self::DosenPa => 'dosen.pending.index',
        };
    }
}
