<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Enums\KrsStatus;
use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\MataKuliah;
use App\Services\KrsValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KrsController extends Controller
{
    /**
     * Halaman KRS milik mahasiswa yang sedang login.
     * KRS diambil dari akun login, bukan dari parameter URL.
     * Belum ada KRS? dibuat otomatis berstatus Draft.
     */
    public function index(Request $request): View
    {
        $mahasiswa = $this->mahasiswaFromAuth($request);

        $krs = DB::transaction(function () use ($mahasiswa) {
            return Krs::firstOrCreate(
                ['mahasiswa_id' => $mahasiswa->id],
                ['status' => KrsStatus::Draft->value],
            );
        });

        $krs->load('mataKuliahs');

        $terpilihIds = $krs->mataKuliahs->pluck('id');
        $tersedia = MataKuliah::whereNotIn('id', $terpilihIds)
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        return view('mahasiswa.krs', [
            'mahasiswa' => $mahasiswa,
            'krs' => $krs,
            'tersedia' => $tersedia,
            'totalSks' => (int) $krs->mataKuliahs->sum('sks'),
            'sisaSks' => KrsValidator::MAX_SKS,
        ]);
    }

    /**
     * Tambahkan mata kuliah ke KRS (hanya saat Draft).
     */
    public function store(Request $request): RedirectResponse
    {
        $mahasiswa = $this->mahasiswaFromAuth($request);
        $krs = $this->krsOf($mahasiswa);

        $this->authorizeDraft($krs);

        $validated = $request->validate([
            'mata_kuliah_id' => ['required', 'integer', 'exists:mata_kuliahs,id'],
        ], [
            'mata_kuliah_id.required' => 'Mata kuliah wajib dipilih.',
            'mata_kuliah_id.exists' => 'Mata kuliah tidak ditemukan.',
        ]);

        $sudahAda = $krs->mataKuliahs()
            ->where('mata_kuliahs.id', $validated['mata_kuliah_id'])
            ->exists();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'mata_kuliah_id' => 'Mata kuliah sudah ditambahkan ke KRS.',
            ]);
        }

        // Unique constraint (krs_id, mata_kuliah_id) tetap menjadi penjaga terakhir.
        $krs->mataKuliahs()->attach($validated['mata_kuliah_id']);

        return redirect()
            ->route('mahasiswa.krs.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan ke KRS.');
    }

    /**
     * Hapus mata kuliah dari KRS (hanya saat Draft).
     */
    public function destroy(Request $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $mahasiswa = $this->mahasiswaFromAuth($request);
        $krs = $this->krsOf($mahasiswa);

        $this->authorizeDraft($krs);

        $krs->mataKuliahs()->detach($mataKuliah->id);

        return redirect()
            ->route('mahasiswa.krs.index')
            ->with('success', 'Mata kuliah dihapus dari KRS.');
    }

    /**
     * Ajukan KRS: validasi dulu, status baru berubah bila valid.
     */
    public function ajukan(Request $request, KrsValidator $validator): RedirectResponse
    {
        $mahasiswa = $this->mahasiswaFromAuth($request);
        $krs = $this->krsOf($mahasiswa);

        $this->authorizeDraft($krs);

        $errors = $validator->validate($krs);

        if ($errors !== []) {
            // Tampilkan semua kesalahan sekaligus; status tetap Draft.
            throw ValidationException::withMessages(['krs' => $errors]);
        }

        DB::transaction(function () use ($krs) {
            $krs->update([
                'status' => KrsStatus::MenungguPersetujuan,
                'diajukan_pada' => now(),
            ]);
        });

        return redirect()
            ->route('mahasiswa.krs.index')
            ->with('success', 'KRS berhasil diajukan dan menunggu persetujuan Dosen PA.');
    }

    private function mahasiswaFromAuth(Request $request)
    {
        $mahasiswa = $request->user()?->mahasiswa;

        if ($mahasiswa === null) {
            abort(403, 'Akun Anda tidak terkait dengan data mahasiswa.');
        }

        return $mahasiswa;
    }

    private function krsOf($mahasiswa): Krs
    {
        return Krs::firstOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            ['status' => KrsStatus::Draft->value],
        );
    }

    /**
     * KRS yang sudah diajukan / diputuskan terkunci (mode baca),
     * dan status di luar Draft tidak boleh diubah lagi.
     */
    private function authorizeDraft(Krs $krs): void
    {
        if ($krs->status !== KrsStatus::Draft) {
            throw ValidationException::withMessages([
                'krs' => 'KRS sudah terkunci dan tidak dapat diubah lagi.',
            ]);
        }
    }
}
