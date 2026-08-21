<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'nip' => $this->nullableTrimmedString(
                $this->input('nip'),
            ),
            'email' => Str::lower(
                trim((string) $this->input('email')),
            ),
            'position' => $this->nullableTrimmedString(
                $this->input('position'),
            ),
            'phone' => $this->nullableTrimmedString(
                $this->input('phone'),
            ),
        ]);
    }

    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && $user->isActive()
            && $user->isSuperAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(
                    'users',
                    'nip',
                ),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email',
                ),
            ],

            'position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::exists(
                    'roles',
                    'id',
                )->where(
                    'is_active',
                    true,
                ),
            ],

            'department_id' => [
                'required',
                'integer',
                Rule::exists(
                    'departments',
                    'id',
                )->where(
                    'is_active',
                    true,
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.max' => 'Nama pengguna maksimal 255 karakter.',

            'nip.unique' => 'NIP sudah digunakan oleh pengguna lain.',
            'nip.max' => 'NIP maksimal 50 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',

            'position.max' => 'Jabatan maksimal 255 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',

            'role_id.required' => 'Role pengguna wajib dipilih.',
            'role_id.exists' => 'Role yang dipilih tidak tersedia.',

            'department_id.required' => 'Unit atau bidang wajib dipilih.',
            'department_id.exists' => 'Unit atau bidang yang dipilih tidak tersedia.',
        ];
    }

    private function nullableTrimmedString(
        mixed $value,
    ): ?string {
        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            return null;
        }

        return trim((string) $value);
    }
}
