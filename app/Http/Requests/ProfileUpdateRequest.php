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
            'province_code' => ['required', 'string', 'size:2'],
            'agency_name' => ['required', 'string', 'max:255'],
            'instance_type' => ['required', 'in:1,2'], // 1=Pusat, 2=Daerah
            'gender' => ['required', 'in:1,2'],
        ];
    }
}