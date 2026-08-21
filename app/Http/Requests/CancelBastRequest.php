<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class CancelBastRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cancellation_reason' => trim(
                (string) $this->input(
                    'cancellation_reason',
                    '',
                ),
            ),
        ]);
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => [
                'required',
                'string',
                'min:10',
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
            'cancellation_reason.required' => 'Alasan pembatalan wajib diisi.',
            'cancellation_reason.min' => 'Alasan pembatalan minimal 10 karakter.',
            'cancellation_reason.max' => 'Alasan pembatalan maksimal 2000 karakter.',
        ];
    }
}
