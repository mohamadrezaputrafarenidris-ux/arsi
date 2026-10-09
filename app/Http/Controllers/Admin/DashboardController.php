<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KrsStatus;
use App\Http\Controllers\Controller;
use App\Models\DosenPa;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $krsPerStatus = Krs::query()
            ->selectRaw('status, COUNT(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $statusCounts = [];
        foreach (KrsStatus::cases() as $status) {
            $statusCounts[$status->value] = (int) ($krsPerStatus[$status->value] ?? 0);
        }

        return view('admin.dashboard', [
            'jumlahMahasiswa' => Mahasiswa::count(),
            'jumlahDosen' => DosenPa::count(),
            'jumlahMataKuliah' => MataKuliah::count(),
            'krsPerStatus' => $statusCounts,
            'jumlahKrs' => array_sum($statusCounts),
        ]);
    }
}
