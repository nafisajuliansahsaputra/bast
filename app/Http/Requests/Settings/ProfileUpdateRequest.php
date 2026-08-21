<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    protected function prepareForValidation(): void
    {
        $phone = trim(
            (string) $this->input(
                'phone',
                '',
            ),
        );

        $this->merge([
            'name' => trim(
                (string) $this->input(
                    'name',
                    '',
                ),
            ),

            'email' => Str::lower(
                trim(
                    (string) $this->input(
                        'email',
                        '',
                    ),
                ),
            ),

            'phone' => $phone !== ''
                ? $phone
                : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->profileRules(
            $this->user()->id,
        );
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'email.max' => 'Email maksimal 255 karakter.',

            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
        ];
    }
}
