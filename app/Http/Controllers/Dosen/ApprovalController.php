<?php

namespace App\Http\Controllers\Dosen;

use App\Enums\KrsStatus;
use App\Http\Controllers\Controller;
use App\Models\Krs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ApprovalController extends Controller
{
    /**
     * Daftar KRS berstatus Menunggu Persetujuan.
     * Semua Dosen PA melihat semua KRS yang menunggu.
     */
    public function index(): View
    {
        $krsList = Krs::where('status', KrsStatus::MenungguPersetujuan->value)
            ->with('mahasiswa')
            ->withCount('mataKuliahs')
            ->orderBy('created_at')
            ->get();

        return view('dosen.pending', ['krsList' => $krsList]);
    }

    /**
     * Detail KRS untuk ditinjau.
     */
    public function show(Krs $krs): View
    {
        $krs->load(['mahasiswa', 'mataKuliahs']);

        return view('dosen.show', ['krs' => $krs]);
    }

    public function setujui(Request $request, Krs $krs): RedirectResponse
    {
        return $this->putuskan($request, $krs, KrsStatus::Disetujui, 'KRS berhasil disetujui.');
    }

    public function tolak(Request $request, Krs $krs): RedirectResponse
    {
        return $this->putuskan($request, $krs, KrsStatus::Ditolak, 'KRS berhasil ditolak.');
    }

    /**
     * Keputusan hanya boleh pada KRS Menunggu Persetujuan,
     * dijalankan di dalam transaksi DB, dan bersifat final.
     */
    private function putuskan(Request $request, Krs $krs, KrsStatus $status, string $pesanSukses): RedirectResponse
    {
        DB::transaction(function () use ($krs, $status) {
            $krs = Krs::whereKey($krs->id)->lockForUpdate()->firstOrFail();

            if ($krs->status !== KrsStatus::MenungguPersetujuan) {
                throw ValidationException::withMessages([
                    'krs' => 'Keputusan hanya dapat diberikan pada KRS berstatus Menunggu Persetujuan.',
                ]);
            }

            $krs->update([
                'status' => $status,
                'diputuskan_pada' => now(),
            ]);
        });

        return redirect()
            ->route('dosen.pending.index')
            ->with('success', $pesanSukses);
    }
}
