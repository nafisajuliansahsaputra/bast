<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property int $id
 * @property int|null $role_id
 * @property int|null $department_id
 * @property string $name
 * @property string|null $nip
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $position
 * @property string|null $phone
 * @property string $status
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Role|null $role
 * @property-read Department|null $department
 */
#[Fillable([
    'role_id',
    'department_id',
    'name',
    'nip',
    'email',
    'position',
    'phone',
    'status',
    'password',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return HasMany<Bast, $this>
     */
    public function createdBasts(): HasMany
    {
        return $this->hasMany(Bast::class, 'created_by');
    }

    /**
     * @return HasMany<Bast, $this>
     */
    public function finalizedBasts(): HasMany
    {
        return $this->hasMany(Bast::class, 'finalized_by');
    }

    /**
     * @return HasMany<Bast, $this>
     */
    public function completedBasts(): HasMany
    {
        return $this->hasMany(Bast::class, 'completed_by');
    }

    /**
     * @return HasMany<Bast, $this>
     */
    public function archivedBasts(): HasMany
    {
        return $this->hasMany(Bast::class, 'archived_by');
    }

    /**
     * @return HasMany<Bast, $this>
     */
    public function cancelledBasts(): HasMany
    {
        return $this->hasMany(Bast::class, 'cancelled_by');
    }

    /**
     * @return HasMany<BastAttachment, $this>
     */
    public function uploadedAttachments(): HasMany
    {
        return $this->hasMany(BastAttachment::class, 'uploaded_by');
    }

    /**
     * @return HasMany<ActivityLog, $this>
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasRole(string ...$roles): bool
    {
        return $this->role !== null
            && in_array($this->role->slug, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
