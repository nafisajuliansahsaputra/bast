<?php

namespace App\Http\Requests\Master;

use App\Models\BastType;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBastTypeRequest extends FormRequest
{
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
        $bastType = $this->route('bastType');

        $uniqueName = Rule::unique(
            'bast_types',
            'name',
        );

        if ($bastType instanceof BastType) {
            $uniqueName->ignore(
                $bastType->id,
            );
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
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
            'name.required' => 'Nama jenis BAST wajib diisi.',
            'name.unique' => 'Nama jenis BAST sudah digunakan.',
            'name.max' => 'Nama jenis BAST maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
