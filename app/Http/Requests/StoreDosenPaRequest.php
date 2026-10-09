<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDosenPaRequest extends FormRequest
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
            'nip' => [
                'required', 'string', 'max:20', 'alpha_dash',
                Rule::unique('dosen_pas', 'nip')->ignore($this->route('dosen')?->id),
            ],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nip.alpha_dash' => 'NIP hanya boleh berisi huruf, angka, tanda hubung, atau garis bawah.',
            'nama.required' => 'Nama wajib diisi.',
        ];
    }
}
