<?php

namespace App\Services;

use App\Models\Bast;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BastLifecycleService
{
    public function complete(
        User $user,
        Bast $bast,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Bast {
        return DB::transaction(function () use (
            $user,
            $bast,
            $ipAddress,
            $userAgent,
        ): Bast {
            $lockedBast = Bast::query()
                ->whereKey($bast->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedBast->isFinalized()) {
                throw ValidationException::withMessages([
                    'bast' => 'Hanya BAST berstatus Finalized yang dapat ditandai selesai.',
                ]);
            }

            $lockedBast->update([
                'status' => Bast::STATUS_COMPLETED,
                'completed_at' => now(),
                'completed_by' => $user->id,
            ]);

            $lockedBast->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'BAST_COMPLETED',

                'description' => sprintf(
                    'Menandai BAST "%s" sebagai selesai.',
                    $lockedBast->title,
                ),

                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,

                'old_values' => [
                    'status' => Bast::STATUS_FINALIZED,
                    'completed_at' => null,
                    'completed_by' => null,
                ],

                'new_values' => [
                    'status' => Bast::STATUS_COMPLETED,
                    'completed_at' => $lockedBast
                        ->completed_at
                        ?->toISOString(),

                    'completed_by' => $user->id,
                ],
            ]);

            return $lockedBast->fresh()
                ?? $lockedBast;
        });
    }

    public function archive(
        User $user,
        Bast $bast,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Bast {
        return DB::transaction(function () use (
            $user,
            $bast,
            $ipAddress,
            $userAgent,
        ): Bast {
            $lockedBast = Bast::query()
                ->whereKey($bast->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedBast->isCompleted()) {
                throw ValidationException::withMessages([
                    'bast' => 'Hanya BAST berstatus Selesai yang dapat diarsipkan.',
                ]);
            }

            $lockedBast->update([
                'status' => Bast::STATUS_ARCHIVED,
                'archived_at' => now(),
                'archived_by' => $user->id,
            ]);

            $lockedBast->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'BAST_ARCHIVED',

                'description' => sprintf(
                    'Mengarsipkan BAST "%s".',
                    $lockedBast->title,
                ),

                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,

                'old_values' => [
                    'status' => Bast::STATUS_COMPLETED,
                    'archived_at' => null,
                    'archived_by' => null,
                ],

                'new_values' => [
                    'status' => Bast::STATUS_ARCHIVED,

                    'archived_at' => $lockedBast
                        ->archived_at
                        ?->toISOString(),

                    'archived_by' => $user->id,
                ],
            ]);

            return $lockedBast->fresh()
                ?? $lockedBast;
        });
    }

    public function restoreArchive(
        User $user,
        Bast $bast,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Bast {
        return DB::transaction(function () use (
            $user,
            $bast,
            $ipAddress,
            $userAgent,
        ): Bast {
            $lockedBast = Bast::query()
                ->whereKey($bast->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedBast->isArchived()) {
                throw ValidationException::withMessages([
                    'bast' => 'Hanya BAST yang sudah diarsipkan yang dapat dipulihkan.',
                ]);
            }

            $oldArchivedAt = $lockedBast
                ->archived_at
                ?->toISOString();

            $oldArchivedBy = $lockedBast
                ->archived_by;

            $lockedBast->update([
                'status' => Bast::STATUS_COMPLETED,
                'archived_at' => null,
                'archived_by' => null,
            ]);

            $lockedBast->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'BAST_RESTORED',

                'description' => sprintf(
                    'Memulihkan BAST "%s" dari arsip.',
                    $lockedBast->title,
                ),

                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,

                'old_values' => [
                    'status' => Bast::STATUS_ARCHIVED,
                    'archived_at' => $oldArchivedAt,
                    'archived_by' => $oldArchivedBy,
                ],

                'new_values' => [
                    'status' => Bast::STATUS_COMPLETED,
                    'archived_at' => null,
                    'archived_by' => null,
                ],
            ]);

            return $lockedBast->fresh()
                ?? $lockedBast;
        });
    }
}
