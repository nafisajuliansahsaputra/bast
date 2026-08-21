<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class SecurityController extends Controller
{
    public function edit(
        TwoFactorAuthenticationRequest $request,
    ): Response {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->isActive(),
            403,
        );

        $props = [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),

            'passwordRules' => Password::defaults()
                ->toPasswordRulesString(),
        ];

        if (
            Features::canManageTwoFactorAuthentication()
        ) {
            $request->ensureStateIsValid();

            $props['twoFactorEnabled'] =
                $user->hasEnabledTwoFactorAuthentication();

            $props['requiresConfirmation'] =
                Features::optionEnabled(
                    Features::twoFactorAuthentication(),
                    'confirm',
                );
        }

        return Inertia::render(
            'settings/security',
            $props,
        );
    }

    public function update(
        PasswordUpdateRequest $request,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->isActive(),
            403,
        );

        $user->update([
            'password' => $request->password,
        ]);

        ActivityLog::query()->create([
            'user_id' => $user->id,

            'action' => 'PASSWORD_UPDATED',

            'description' => sprintf(
                'Mengubah kata sandi akun "%s".',
                $user->name,
            ),

            'subject_type' => User::class,

            'subject_id' => $user->id,

            'ip_address' => $request->ip(),

            'user_agent' => $request
                ->userAgent(),

            'old_values' => null,

            'new_values' => null,
        ]);

        Inertia::flash(
            'toast',
            [
                'type' => 'success',

                'message' => 'Kata sandi berhasil diperbarui.',
            ],
        );

        return back();
    }
}
