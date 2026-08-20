<?php

namespace App\Http\Requests;

use App\Models\Bast;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBastAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bast = $this->route('bast');

        return $bast instanceof Bast
            && (
                $this->user()?->can(
                    'manageAttachments',
                    $bast,
                ) ?? false
            );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx',
            ],

            'category' => [
                'nullable',
                'string',
                Rule::in([
                    'photo',
                    'supporting_document',
                    'assignment_letter',
                    'official_note',
                    'signature',
                    'other',
                ]),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File lampiran wajib dipilih.',
            'file.file' => 'Lampiran yang dipilih tidak valid.',
            'file.max' => 'Ukuran lampiran maksimal 10 MB.',
            'file.mimes' => 'Format lampiran harus PDF, gambar, Word, atau Excel.',
            'category.in' => 'Kategori lampiran tidak valid.',
            'description.max' => 'Keterangan lampiran maksimal 1000 karakter.',
        ];
    }
}
