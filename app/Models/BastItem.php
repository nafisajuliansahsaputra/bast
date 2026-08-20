<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bast_id',
    'item_category_id',
    'unit_id',
    'name',
    'code',
    'inventory_number',
    'serial_number',
    'quantity',
    'condition',
    'value',
    'description',
    'sort_order',
])]
class BastItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'value' => 'decimal:2',
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
     * @return BelongsTo<ItemCategory, $this>
     */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
