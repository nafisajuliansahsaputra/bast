<?php

namespace App\Http\Requests\Master;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
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
        $unit = $this->route('unit');

        $uniqueName = Rule::unique(
            'units',
            'name',
        );

        if ($unit instanceof Unit) {
            $uniqueName->ignore(
                $unit->id,
            );
        }

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $uniqueName,
            ],

            'symbol' => [
                'nullable',
                'string',
                'max:30',
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
            'name.required' => 'Nama satuan wajib diisi.',
            'name.unique' => 'Nama satuan sudah digunakan.',
            'symbol.max' => 'Simbol satuan maksimal 30 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
