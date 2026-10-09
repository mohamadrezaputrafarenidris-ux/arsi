<?php

namespace App\Http\Requests;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAkunRequest extends FormRequest
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
        $akun = $this->route('akun');

        return [
            'username' => [
                'required', 'string', 'max:255', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($akun?->id),
            ],
            // Password dikosongkan saat edit berarti tidak diubah.
            'password' => ['nullable', 'string', 'min:6'],
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
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.enum' => 'Role tidak valid.',
            'mahasiswa_id.required' => 'Data mahasiswa wajib dipilih untuk role Mahasiswa.',
            'dosen_pa_id.required' => 'Data dosen PA wajib dipilih untuk role Dosen PA.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $role = $this->input('role');
            $ignoreId = $this->route('akun')?->id;

            if ($role === Role::Mahasiswa->value && $this->filled('mahasiswa_id')) {
                $taken = User::where('mahasiswa_id', $this->integer('mahasiswa_id'))
                    ->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('mahasiswa_id', 'Data mahasiswa tersebut sudah memiliki akun.');
                }
            }

            if ($role === Role::DosenPa->value && $this->filled('dosen_pa_id')) {
                $taken = User::where('dosen_pa_id', $this->integer('dosen_pa_id'))
                    ->when($ignoreId, fn ($query, $id) => $query->whereKeyNot($id))
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('dosen_pa_id', 'Data dosen PA tersebut sudah memiliki akun.');
                }
            }

            // Kaitan data yang tidak sesuai role dibersihkan, bukan memicu error.
        }];
    }
}
