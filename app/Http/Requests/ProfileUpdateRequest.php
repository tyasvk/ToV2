<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Validasi Foto Profil
            'avatar' => ['nullable', 'image', 'max:2048'], // Max 2MB

            // Validasi Data Informasi Pendaftaran
            // Pastikan baris ini ada di dalam function rules()
'province_code' => ['nullable', 'string', 'max:255'],
'agency_name'   => ['nullable', 'string', 'max:255'],
'instance_type' => ['nullable', 'string', 'max:255'],
'gender'        => ['nullable', 'string', 'in:1,2'],
        ];
    }
}