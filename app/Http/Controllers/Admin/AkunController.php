<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAkunRequest;
use App\Http\Requests\UpdateAkunRequest;
use App\Models\DosenPa;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AkunController extends Controller
{
    public function index(): View
    {
        $akuns = User::with(['mahasiswa', 'dosenPa'])
            ->orderBy('username')
            ->get();

        return view('admin.akun.index', ['akuns' => $akuns]);
    }

    public function create(): View
    {
        return view('admin.akun.create', $this->formData());
    }

    public function store(StoreAkunRequest $request): RedirectResponse
    {
        User::create($this->payload($request));

        return redirect()
            ->route('admin.akun.index')
            ->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(User $akun): View
    {
        return view('admin.akun.edit', ['akun' => $akun] + $this->formData($akun));
    }

    public function update(UpdateAkunRequest $request, User $akun): RedirectResponse
    {
        $data = $this->payload($request);
        $password = $data['password'] ?? null;
        unset($data['password']);

        $akun->fill($data);

        // Password kosong saat edit berarti tidak diubah.
        if ($password !== null && $password !== '') {
            $akun->password = $password;
        }

        $akun->save();

        return redirect()
            ->route('admin.akun.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $akun): RedirectResponse
    {
        if ($akun->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $akun->delete();

        // Data mahasiswa / dosen PA tidak ikut terhapus: FK memakai nullOnDelete.
        return redirect()
            ->route('admin.akun.index')
            ->with('success', 'Akun berhasil dihapus. Data mahasiswa/dosen PA tetap tersimpan.');
    }

    /**
     * Susun payload sesuai role: kaitan data dibersihkan bila tidak relevan.
     *
     * @return array<string, mixed>
     */
    private function payload($request): array
    {
        $role = $request->input('role');

        return [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
            'role' => $role,
            'mahasiswa_id' => $role === Role::Mahasiswa->value ? $request->integer('mahasiswa_id') : null,
            'dosen_pa_id' => $role === Role::DosenPa->value ? $request->integer('dosen_pa_id') : null,
        ];
    }

    /**
     * Daftar mahasiswa dan dosen PA yang belum memiliki akun
     * (ditambah data yang sudah dikaitkan dengan akun yang sedang diedit).
     *
     * @return array<string, mixed>
     */
    private function formData(?User $akun = null): array
    {
        $terpakaiMahasiswa = User::whereNotNull('mahasiswa_id')->pluck('mahasiswa_id');
        $terpakaiDosen = User::whereNotNull('dosen_pa_id')->pluck('dosen_pa_id');

        $mahasiswas = Mahasiswa::orderBy('nama')
            ->get()
            ->filter(fn (Mahasiswa $m) => ! $terpakaiMahasiswa->contains($m->id) || $akun?->mahasiswa_id === $m->id)
            ->values();

        $dosenPas = DosenPa::orderBy('nama')
            ->get()
            ->filter(fn (DosenPa $d) => ! $terpakaiDosen->contains($d->id) || $akun?->dosen_pa_id === $d->id)
            ->values();

        return [
            'mahasiswas' => $mahasiswas,
            'dosenPas' => $dosenPas,
            'roleOptions' => collect(Role::cases())
                ->mapWithKeys(fn (Role $role) => [$role->value => $role->label()]),
        ];
    }
}
