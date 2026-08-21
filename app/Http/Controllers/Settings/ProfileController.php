<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(
        Request $request,
    ): Response {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->isActive(),
            403,
        );

        $user->loadMissing([
            'role:id,name,slug',
            'department:id,name,code',
        ]);

        return Inertia::render(
            'settings/profile',
        );
    }

    public function update(
        ProfileUpdateRequest $request,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->isActive(),
            403,
        );

        $validated = $request->validated();

        $oldValues = $this->snapshot(
            $user,
        );

        $emailChanged =
            $user->email !==
            (string) $validated['email'];

        $user->fill([
            'name' => (string) $validated['name'],

            'email' => (string) $validated['email'],

            'phone' => $validated['phone'] ?? null,
        ]);

        $user->save();

        if (
            $emailChanged
            || $user->email_verified_at === null
        ) {
            $user->markEmailAsVerified();
        }

        $user->refresh();

        ActivityLog::query()->create([
            'user_id' => $user->id,

            'action' => 'PROFILE_UPDATED',

            'description' => sprintf(
                'Memperbarui profil akun "%s".',
                $user->name,
            ),

            'subject_type' => User::class,

            'subject_id' => $user->id,

            'ip_address' => $request->ip(),

            'user_agent' => $request
                ->userAgent(),

            'old_values' => $oldValues,

            'new_values' => $this->snapshot(
                $user,
            ),
        ]);

        Inertia::flash(
            'toast',
            [
                'type' => 'success',

                'message' => 'Profil berhasil diperbarui.',
            ],
        );

        return to_route(
            'profile.edit',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        User $user,
    ): array {
        return [
            'name' => $user->name,

            'email' => $user->email,

            'phone' => $user->phone,
        ];
    }
}
