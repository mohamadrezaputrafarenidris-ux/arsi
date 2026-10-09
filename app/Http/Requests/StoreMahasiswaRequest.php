<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMahasiswaRequest extends FormRequest
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
            'nim' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('mahasiswas', 'nim')->ignore($this->route('mahasiswa')?->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
            'prodi' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'integer', 'min:1', 'max:14'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'nim.alpha_dash' => 'NIM hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.',
            'nama.required' => 'Nama wajib diisi.',
            'prodi.required' => 'Program studi wajib diisi.',
            'semester.required' => 'Semester wajib diisi.',
            'semester.min' => 'Semester minimal 1.',
            'semester.max' => 'Semester maksimal 14.',
        ];
    }
}
