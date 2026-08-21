<?php

use App\Models\ActivityLog;
use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()
        ->create();

    $this->actingAs($user)
        ->get(
            route(
                'profile.edit',
            ),
        )
        ->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()
        ->create([
            'phone' => null,
        ]);

    $this->actingAs($user)
        ->patch(
            route(
                'profile.update',
            ),
            [
                'name' => 'Test User',

                'email' => 'test@example.com',

                'phone' => '081234567890',
            ],
        )
        ->assertSessionHasNoErrors()
        ->assertRedirect(
            route(
                'profile.edit',
            ),
        );

    $user->refresh();

    expect(
        $user->name,
    )->toBe(
        'Test User',
    )
        ->and(
            $user->email,
        )->toBe(
            'test@example.com',
        )
        ->and(
            $user->phone,
        )->toBe(
            '081234567890',
        )
        ->and(
            $user->email_verified_at,
        )->not->toBeNull();
});

test('changed profile email remains verified', function () {
    $user = User::factory()
        ->create();

    $oldVerifiedAt =
        $user->email_verified_at;

    $this->actingAs($user)
        ->patch(
            route(
                'profile.update',
            ),
            [
                'name' => $user->name,

                'email' => 'changed@example.com',

                'phone' => $user->phone,
            ],
        )
        ->assertSessionHasNoErrors();

    $user->refresh();

    expect(
        $user->email,
    )->toBe(
        'changed@example.com',
    )
        ->and(
            $user->email_verified_at,
        )->not->toBeNull();

    expect(
        $oldVerifiedAt,
    )->not->toBeNull();
});

test('profile update creates activity log', function () {
    $user = User::factory()
        ->create();

    $this->actingAs($user)
        ->patch(
            route(
                'profile.update',
            ),
            [
                'name' => 'Updated Profile',

                'email' => $user->email,

                'phone' => '081111111111',
            ],
        )
        ->assertSessionHasNoErrors();

    expect(
        ActivityLog::query()
            ->where(
                'user_id',
                $user->id,
            )
            ->where(
                'action',
                'PROFILE_UPDATED',
            )
            ->exists(),
    )->toBeTrue();
});
