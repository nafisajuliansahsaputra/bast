<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bast_id',
    'uploaded_by',
    'category',
    'original_name',
    'stored_name',
    'disk',
    'path',
    'mime_type',
    'file_size',
    'description',
])]
class BastAttachment extends Model
{
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Bast, $this>
     */
    public function bast(): BelongsTo
    {
        return $this->belongsTo(Bast::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
