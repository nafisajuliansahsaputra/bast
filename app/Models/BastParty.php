<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bast_id',
    'user_id',
    'party_type',
    'name',
    'nip',
    'position',
    'department',
    'institution',
    'address',
    'sort_order',
])]
class BastParty extends Model
{
    public const TYPE_FIRST_PARTY = 'first_party';

    public const TYPE_SECOND_PARTY = 'second_party';

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
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
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
