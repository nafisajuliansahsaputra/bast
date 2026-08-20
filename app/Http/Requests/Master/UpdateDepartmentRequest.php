<?php

namespace App\Http\Requests\Master;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge([
                'code' => Str::upper(
                    trim(
                        (string) $this->input('code'),
                    ),
                ),
            ]);
        }
    }

    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User
            && $user->isActive()
            && (
                $user->isSuperAdmin()
                || $user->isAdmin()
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $department = $this->route(
            'department',
        );

        $uniqueCode = Rule::unique(
            'departments',
            'code',
        );

        if (
            $department instanceof Department
        ) {
            $uniqueCode->ignore(
                $department->id,
            );
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:30',
                $uniqueCode,
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama unit atau bidang wajib diisi.',
            'code.required' => 'Kode unit atau bidang wajib diisi.',
            'code.unique' => 'Kode unit atau bidang sudah digunakan.',
            'code.max' => 'Kode maksimal 30 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
