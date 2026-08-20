<?php

namespace App\Http\Requests;

use App\Models\Bast;

class UpdateBastRequest extends StoreBastRequest
{
    public function authorize(): bool
    {
        $bast = $this->route('bast');

        return $bast instanceof Bast
            && ($this->user()?->can('update', $bast) ?? false);
    }
}
