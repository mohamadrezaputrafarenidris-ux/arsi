<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDosenPaRequest;
use App\Models\DosenPa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenPaController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $dosenPas = DosenPa::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w
                    ->where('nip', 'like', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%"));
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.dosen.index', ['dosenPas' => $dosenPas, 'q' => $q]);
    }

    public function create(): View
    {
        return view('admin.dosen.create');
    }

    public function store(StoreDosenPaRequest $request): RedirectResponse
    {
        DosenPa::create($request->safe()->only(['nip', 'nama']));

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', 'Data dosen PA berhasil ditambahkan.');
    }

    public function edit(DosenPa $dosen): View
    {
        return view('admin.dosen.edit', ['dosen' => $dosen]);
    }

    public function update(StoreDosenPaRequest $request, DosenPa $dosen): RedirectResponse
    {
        $dosen->update($request->safe()->only(['nip', 'nama']));

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', 'Data dosen PA berhasil diperbarui.');
    }

    public function destroy(Request $request, DosenPa $dosen): RedirectResponse
    {
        // Akun login yang dikaitkan tidak ikut terhapus (FK nullOnDelete).
        $dosen->delete();

        return redirect()
            ->route('admin.dosen.index')
            ->with('success', 'Data dosen PA berhasil dihapus.');
    }
}
