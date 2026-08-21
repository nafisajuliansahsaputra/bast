<?php

namespace App\Policies;

use App\Models\Bast;
use App\Models\User;

class BastPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    public function view(
        User $user,
        Bast $bast,
    ): bool {
        if (
            $user->isSuperAdmin()
            || $user->isAdmin()
        ) {
            return true;
        }

        return $user->isStaff()
            && $bast->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && $user->hasRole(
                'super-admin',
                'admin',
                'staff',
            );
    }

    public function update(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isDraft()
            && $this->canManage(
                $user,
                $bast,
            );
    }

    public function delete(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isDraft()
            && $bast->sequence_number === null
            && $this->canManage(
                $user,
                $bast,
            );
    }

    public function finalize(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isDraft()
            && $this->canManage(
                $user,
                $bast,
            );
    }

    public function manageAttachments(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isDraft()
            && $this->canManage(
                $user,
                $bast,
            );
    }

    public function reopen(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isFinalized()
            && (
                $user->isSuperAdmin()
                || $user->isAdmin()
            );
    }

    public function cancel(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isFinalized()
            && (
                $user->isSuperAdmin()
                || $user->isAdmin()
            );
    }

    public function complete(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isFinalized()
            && $this->canManage(
                $user,
                $bast,
            );
    }

    public function archive(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isCompleted()
            && $this->canManage(
                $user,
                $bast,
            );
    }

    public function restoreArchive(
        User $user,
        Bast $bast,
    ): bool {
        return $bast->isArchived()
            && (
                $user->isSuperAdmin()
                || $user->isAdmin()
            );
    }

    public function previewDocument(
        User $user,
        Bast $bast,
    ): bool {
        return $this->view(
            $user,
            $bast,
        );
    }

    public function downloadPdf(
        User $user,
        Bast $bast,
    ): bool {
        if (! $this->view($user, $bast)) {
            return false;
        }

        return in_array(
            $bast->status,
            [
                Bast::STATUS_FINALIZED,
                Bast::STATUS_COMPLETED,
                Bast::STATUS_ARCHIVED,
                Bast::STATUS_CANCELLED,
            ],
            true,
        );
    }

    public function restore(
        User $user,
        Bast $bast,
    ): bool {
        return false;
    }

    public function forceDelete(
        User $user,
        Bast $bast,
    ): bool {
        return false;
    }

    private function canManage(
        User $user,
        Bast $bast,
    ): bool {
        if (
            $user->isSuperAdmin()
            || $user->isAdmin()
        ) {
            return true;
        }

        return $user->isStaff()
            && $bast->created_by === $user->id;
    }
}
