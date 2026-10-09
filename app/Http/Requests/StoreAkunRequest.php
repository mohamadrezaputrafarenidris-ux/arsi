<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAkunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::enum(Role::class)],
            'mahasiswa_id' => [
                'nullable',
                'integer',
                Rule::requiredIf($this->input('role') === Role::Mahasiswa->value),
                Rule::exists('mahasiswas', 'id'),
            ],
            'dosen_pa_id' => [
                'nullable',
                'integer',
                Rule::requiredIf($this->input('role') === Role::DosenPa->value),
                Rule::exists('dosen_pas', 'id'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.enum' => 'Role tidak valid.',
            'mahasiswa_id.required' => 'Data mahasiswa wajib dipilih untuk role Mahasiswa.',
            'mahasiswa_id.exists' => 'Data mahasiswa tidak ditemukan.',
            'dosen_pa_id.required' => 'Data dosen PA wajib dipilih untuk role Dosen PA.',
            'dosen_pa_id.exists' => 'Data dosen PA tidak ditemukan.',
        ];
    }

    public function after(): array
    {
        return [$this->assertDataNotAlreadyLinked()];
    }

    /**
     * Satu data mahasiswa / dosen PA hanya boleh dipakai oleh satu akun,
     * dan hanya untuk role yang sesuai.
     */
    protected function assertDataNotAlreadyLinked(): callable
    {
        return function (Validator $validator): void {
            $role = $this->input('role');

            if ($role === Role::Mahasiswa->value && $this->filled('mahasiswa_id')) {
                $taken = User::where('mahasiswa_id', $this->integer('mahasiswa_id'))
                    ->when($this->route('akun')?->id, fn ($query, $id) => $query->whereKeyNot($id))
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('mahasiswa_id', 'Data mahasiswa tersebut sudah memiliki akun.');
                }
            }

            if ($role === Role::DosenPa->value && $this->filled('dosen_pa_id')) {
                $taken = User::where('dosen_pa_id', $this->integer('dosen_pa_id'))
                    ->when($this->route('akun')?->id, fn ($query, $id) => $query->whereKeyNot($id))
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('dosen_pa_id', 'Data dosen PA tersebut sudah memiliki akun.');
                }
            }
        };
    }
}
