<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\DosenPa;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1 Admin
        User::create([
            'username' => 'admin',
            'password' => 'admin123',
            'role' => Role::Admin,
        ]);

        // 1 Dosen PA
        $dosen = DosenPa::create([
            'nip' => '198501012010011001',
            'nama' => 'Dr. Ahmad Rasyid, M.Kom',
        ]);

        User::create([
            'username' => 'dosen1',
            'password' => 'dosen123',
            'role' => Role::DosenPa,
            'dosen_pa_id' => $dosen->id,
        ]);

        // 3 Mahasiswa
        $mahasiswas = [
            ['nim' => '531424001', 'nama' => 'Andi Pratama', 'username' => 'mhs1'],
            ['nim' => '531424002', 'nama' => 'Siti Rahma', 'username' => 'mhs2'],
            ['nim' => '531424003', 'nama' => 'Budi Santoso', 'username' => 'mhs3'],
        ];

        foreach ($mahasiswas as $data) {
            $mahasiswa = Mahasiswa::create([
                'nim' => $data['nim'],
                'nama' => $data['nama'],
                'prodi' => 'Sistem Informasi',
                'semester' => 5,
            ]);

            User::create([
                'username' => $data['username'],
                'password' => 'mhs123',
                'role' => Role::Mahasiswa,
                'mahasiswa_id' => $mahasiswa->id,
            ]);
        }

        // 5 Mata Kuliah
        MataKuliah::insert([
            [
                'kode' => 'MK001', 'nama' => 'Pemrograman Web', 'sks' => 3,
                'hari' => 'Senin', 'jam_mulai' => '08:00', 'jam_selesai' => '10:30',
                'ruang' => 'Lab Komputer 1',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'kode' => 'MK002', 'nama' => 'Basis Data', 'sks' => 3,
                'hari' => 'Senin', 'jam_mulai' => '10:00', 'jam_selesai' => '12:30',
                'ruang' => 'Lab Komputer 2',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'kode' => 'MK003', 'nama' => 'Rekayasa Perangkat Lunak', 'sks' => 3,
                'hari' => 'Selasa', 'jam_mulai' => '08:00', 'jam_selesai' => '10:30',
                'ruang' => 'R. 201',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'kode' => 'MK004', 'nama' => 'Interaksi Manusia dan Komputer', 'sks' => 3,
                'hari' => 'Selasa', 'jam_mulai' => '10:30', 'jam_selesai' => '13:00',
                'ruang' => 'R. 202',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'kode' => 'MK005', 'nama' => 'Sistem Informasi Manajemen', 'sks' => 3,
                'hari' => 'Rabu', 'jam_mulai' => '13:00', 'jam_selesai' => '15:30',
                'ruang' => 'R. 203',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // Belum ada KRS: dibuat otomatis saat mahasiswa membuka menu KRS.
    }
}
