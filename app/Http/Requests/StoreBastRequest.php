<?php

namespace App\Http\Requests;

use App\Models\Bast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Bast::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'bast_type_id' => [
                'required',
                'integer',
                Rule::exists('bast_types', 'id')
                    ->where('is_active', true),
            ],

            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where('is_active', true),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'document_date' => [
                'required',
                'date',
            ],

            'handover_date' => [
                'required',
                'date',
                'after_or_equal:document_date',
            ],

            'handover_place' => [
                'required',
                'string',
                'max:255',
            ],

            'parties' => [
                'required',
                'array',
            ],

            'parties.first_party' => [
                'required',
                'array',
            ],

            'parties.first_party.user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'parties.first_party.name' => [
                'required',
                'string',
                'max:255',
            ],

            'parties.first_party.nip' => [
                'nullable',
                'string',
                'max:50',
            ],

            'parties.first_party.position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'parties.first_party.department' => [
                'nullable',
                'string',
                'max:255',
            ],

            'parties.first_party.institution' => [
                'required',
                'string',
                'max:255',
            ],

            'parties.first_party.address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'parties.second_party' => [
                'required',
                'array',
            ],

            'parties.second_party.user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'parties.second_party.name' => [
                'required',
                'string',
                'max:255',
            ],

            'parties.second_party.nip' => [
                'nullable',
                'string',
                'max:50',
            ],

            'parties.second_party.position' => [
                'nullable',
                'string',
                'max:255',
            ],

            'parties.second_party.department' => [
                'nullable',
                'string',
                'max:255',
            ],

            'parties.second_party.institution' => [
                'required',
                'string',
                'max:255',
            ],

            'parties.second_party.address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*.item_category_id' => [
                'nullable',
                'integer',
                Rule::exists('item_categories', 'id')
                    ->where('is_active', true),
            ],

            'items.*.unit_id' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')
                    ->where('is_active', true),
            ],

            'items.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'items.*.code' => [
                'nullable',
                'string',
                'max:100',
            ],

            'items.*.inventory_number' => [
                'nullable',
                'string',
                'max:150',
            ],

            'items.*.serial_number' => [
                'nullable',
                'string',
                'max:150',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999999',
            ],

            'items.*.condition' => [
                'nullable',
                'string',
                Rule::in([
                    'baik',
                    'rusak_ringan',
                    'rusak_berat',
                ]),
            ],

            'items.*.value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'items.*.description' => [
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
            'bast_type_id.required' => 'Jenis BAST wajib dipilih.',
            'department_id.required' => 'Unit atau bidang wajib dipilih.',
            'title.required' => 'Judul BAST wajib diisi.',
            'document_date.required' => 'Tanggal dokumen wajib diisi.',
            'handover_date.required' => 'Tanggal serah terima wajib diisi.',
            'handover_date.after_or_equal' => 'Tanggal serah terima tidak boleh sebelum tanggal dokumen.',
            'handover_place.required' => 'Tempat serah terima wajib diisi.',

            'parties.first_party.name.required' => 'Nama Pihak Pertama wajib diisi.',
            'parties.first_party.institution.required' => 'Instansi Pihak Pertama wajib diisi.',
            'parties.second_party.name.required' => 'Nama Pihak Kedua wajib diisi.',
            'parties.second_party.institution.required' => 'Instansi Pihak Kedua wajib diisi.',

            'items.required' => 'Minimal satu item harus ditambahkan.',
            'items.min' => 'Minimal satu item harus ditambahkan.',
            'items.*.name.required' => 'Nama item wajib diisi.',
            'items.*.quantity.required' => 'Jumlah item wajib diisi.',
            'items.*.quantity.gt' => 'Jumlah item harus lebih dari 0.',
        ];
    }
}
