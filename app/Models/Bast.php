<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'document_number',
    'sequence_number',
    'document_code',
    'document_month',
    'document_year',
    'bast_type_id',
    'department_id',
    'created_by',
    'title',
    'description',
    'document_date',
    'handover_date',
    'handover_place',
    'status',
    'finalized_at',
    'finalized_by',
    'completed_at',
    'completed_by',
    'archived_at',
    'archived_by',
    'cancelled_at',
    'cancelled_by',
    'cancellation_reason',
])]
class Bast extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINALIZED = 'finalized';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_ARCHIVED = 'archived';

    public const STATUS_CANCELLED = 'cancelled';

    protected static function booted(): void
    {
        static::creating(function (Bast $bast): void {
            if (blank($bast->uuid)) {
                $bast->uuid = Str::uuid()->toString();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'document_date' => 'date',
            'handover_date' => 'date',

            'finalized_at' => 'datetime',
            'completed_at' => 'datetime',
            'archived_at' => 'datetime',
            'cancelled_at' => 'datetime',

            'sequence_number' => 'integer',
            'document_month' => 'integer',
            'document_year' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return BelongsTo<BastType, $this>
     */
    public function bastType(): BelongsTo
    {
        return $this->belongsTo(BastType::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * @return HasMany<BastParty, $this>
     */
    public function parties(): HasMany
    {
        return $this->hasMany(BastParty::class);
    }

    /**
     * @return HasMany<BastItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BastItem::class)
            ->orderBy('sort_order');
    }

    /**
     * @return HasMany<BastAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(BastAttachment::class);
    }

    /**
     * @return MorphMany<ActivityLog, $this>
     */
    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'subject');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }
}
