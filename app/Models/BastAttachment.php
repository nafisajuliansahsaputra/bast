<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $bast_id
 * @property int|null $uploaded_by
 * @property string|null $category
 * @property string $original_name
 * @property string $stored_name
 * @property string $disk
 * @property string $path
 * @property string|null $mime_type
 * @property int|null $file_size
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Bast $bast
 * @property-read User|null $uploader
 */
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
        return $this->belongsTo(
            User::class,
            'uploaded_by',
        );
    }
}
