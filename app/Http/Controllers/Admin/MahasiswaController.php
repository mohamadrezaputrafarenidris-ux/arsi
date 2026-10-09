<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KrsStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMahasiswaRequest;
use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $mahasiswas = Mahasiswa::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w
                    ->where('nim', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%"));
            })
            ->withCount(['krs'])
            ->orderBy('nim')
            ->paginate(10)
            ->withQueryString();

        return view('admin.mahasiswa.index', ['mahasiswas' => $mahasiswas, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.mahasiswa.create');
    }

    public function store(StoreMahasiswaRequest $request): RedirectResponse
    {
        Mahasiswa::create($request->safe()->only(['nim', 'nama', 'prodi', 'semester']));

        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil ditambahkan.');
    }

    public function edit(Mahasiswa $mahasiswa): View
    {
        return view('admin.mahasiswa.edit', ['mahasiswa' => $mahasiswa]);
    }

    public function update(StoreMahasiswaRequest $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->update($request->safe()->only(['nim', 'nama', 'prodi', 'semester']));

        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $krs = $mahasiswa->krs;

        // Mahasiswa dengan KRS yang sudah diajukan / diputuskan tidak boleh dihapus.
        if ($krs !== null && $krs->status !== KrsStatus::Draft) {
            return back()->with(
                'error',
                'Mahasiswa tidak dapat dihapus karena KRS-nya sudah diajukan atau diputuskan.'
            );
        }

        DB::transaction(function () use ($mahasiswa) {
            // KRS berstatus Draft ikut terhapus (FK cascadeOnDelete + pivot).
            $mahasiswa->delete();
        });

        return redirect()
            ->route('admin.mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil dihapus.');
    }
}
