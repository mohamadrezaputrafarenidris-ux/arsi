<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMataKuliahRequest;
use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $mataKuliahs = MataKuliah::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w
                    ->where('kode', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%"));
            })
            ->withCount('krs')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->paginate(10)
            ->withQueryString();

        return view('admin.matakuliah.index', ['mataKuliahs' => $mataKuliahs, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.matakuliah.create', [
            'hariOptions' => MataKuliah::hariOptions(),
        ]);
    }

    public function store(StoreMataKuliahRequest $request): RedirectResponse
    {
        MataKuliah::create($request->safe()->only([
            'kode', 'nama', 'sks', 'hari', 'jam_mulai', 'jam_selesai', 'ruang',
        ]));

        return redirect()
            ->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mataKuliah): View
    {
        return view('admin.matakuliah.edit', [
            'mataKuliah' => $mataKuliah,
            'hariOptions' => MataKuliah::hariOptions(),
        ]);
    }

    public function update(StoreMataKuliahRequest $request, MataKuliah $mataKuliah): RedirectResponse
    {
        $mataKuliah->update($request->safe()->only([
            'kode', 'nama', 'sks', 'hari', 'jam_mulai', 'jam_selesai', 'ruang',
        ]));

        return redirect()
            ->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(MataKuliah $mataKuliah): RedirectResponse
    {
        if ($mataKuliah->krs()->exists()) {
            return back()->with('error', 'Mata kuliah tidak dapat dihapus karena sudah dipakai dalam KRS.');
        }

        $mataKuliah->delete();

        return redirect()
            ->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }
}
