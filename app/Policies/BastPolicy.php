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

    public function view(User $user, Bast $bast): bool
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
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

    public function update(User $user, Bast $bast): bool
    {
        if (! $bast->isDraft()) {
            return false;
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return true;
        }

        return $user->isStaff()
            && $bast->created_by === $user->id;
    }

    public function delete(User $user, Bast $bast): bool
    {
        return $this->update($user, $bast);
    }

    public function finalize(User $user, Bast $bast): bool
    {
        return $this->update($user, $bast);
    }

    public function manageAttachments(User $user, Bast $bast): bool
    {
        return $this->update($user, $bast);
    }

    public function restore(User $user, Bast $bast): bool
    {
        return false;
    }

    public function forceDelete(User $user, Bast $bast): bool
    {
        return false;
    }
}
