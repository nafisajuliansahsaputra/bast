<?php

namespace App\Http\Requests\Master;

use App\Models\ItemCategory;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemCategoryRequest extends FormRequest
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
        $category = $this->route(
            'itemCategory',
        );

        $uniqueName = Rule::unique(
            'item_categories',
            'name',
        );

        if (
            $category instanceof ItemCategory
        ) {
            $uniqueName->ignore(
                $category->id,
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
            'name.required' => 'Nama kategori item wajib diisi.',
            'name.unique' => 'Nama kategori item sudah digunakan.',
            'name.max' => 'Nama kategori item maksimal 255 karakter.',
            'description.max' => 'Deskripsi maksimal 2000 karakter.',
        ];
    }
}
