<?php

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->accountRole =
        Role::query()->create([
            'name' => 'Staff Account Test',

            'slug' => 'staff',

            'description' => null,

            'is_active' => true,
        ]);

    $this->accountDepartment =
        Department::query()->create([
            'name' => 'Account Test',

            'code' => 'ACC',

            'description' => null,

            'is_active' => true,
        ]);

    $this->accountUser =
        User::factory()->create([
            'role_id' => $this
                ->accountRole
                ->id,

            'department_id' => $this
                ->accountDepartment
                ->id,

            'nip' => '19990001',

            'position' => 'Pranata Komputer',

            'phone' => '08123456789',
        ]);
});

test('user cannot change organizational profile fields from self profile', function () {
    $otherRole =
        Role::query()->create([
            'name' => 'Other Role',

            'slug' => 'other-role',

            'description' => null,

            'is_active' => true,
        ]);

    $otherDepartment =
        Department::query()->create([
            'name' => 'Other Department',

            'code' => 'OTH',

            'description' => null,

            'is_active' => true,
        ]);

    $this->actingAs(
        $this->accountUser,
    )
        ->patch(
            route(
                'profile.update',
            ),
            [
                'name' => 'Updated Name',

                'email' => $this
                    ->accountUser
                    ->email,

                'phone' => '089999999999',

                'nip' => '99999999',

                'position' => 'Kepala Dinas',

                'role_id' => $otherRole->id,

                'department_id' => $otherDepartment->id,
            ],
        )
        ->assertSessionHasNoErrors();

    $this->accountUser
        ->refresh();

    expect(
        $this
            ->accountUser
            ->name,
    )->toBe(
        'Updated Name',
    )
        ->and(
            $this
                ->accountUser
                ->phone,
        )->toBe(
            '089999999999',
        )
        ->and(
            $this
                ->accountUser
                ->nip,
        )->toBe(
            '19990001',
        )
        ->and(
            $this
                ->accountUser
                ->position,
        )->toBe(
            'Pranata Komputer',
        )
        ->and(
            $this
                ->accountUser
                ->role_id,
        )->toBe(
            $this
                ->accountRole
                ->id,
        )
        ->and(
            $this
                ->accountUser
                ->department_id,
        )->toBe(
            $this
                ->accountDepartment
                ->id,
        );
});

test('profile deletion route is not available', function () {
    $this->actingAs(
        $this->accountUser,
    )
        ->delete(
            '/settings/profile',
            [
                'password' => 'password',
            ],
        )
        ->assertMethodNotAllowed();

    expect(
        $this
            ->accountUser
            ->fresh(),
    )->not->toBeNull();
});

test('updating password creates activity log', function () {
    $this->actingAs(
        $this->accountUser,
    )
        ->put(
            route(
                'user-password.update',
            ),
            [
                'current_password' => 'password',

                'password' => 'new-password',

                'password_confirmation' => 'new-password',
            ],
        )
        ->assertSessionHasNoErrors();

    $this->accountUser
        ->refresh();

    expect(
        Hash::check(
            'new-password',
            $this
                ->accountUser
                ->password,
        ),
    )->toBeTrue();

    expect(
        ActivityLog::query()
            ->where(
                'user_id',
                $this
                    ->accountUser
                    ->id,
            )
            ->where(
                'action',
                'PASSWORD_UPDATED',
            )
            ->exists(),
    )->toBeTrue();
});

test('profile update cannot make internal user unverified', function () {
    $this->actingAs(
        $this->accountUser,
    )
        ->patch(
            route(
                'profile.update',
            ),
            [
                'name' => $this
                    ->accountUser
                    ->name,

                'email' => 'new-internal@example.com',

                'phone' => $this
                    ->accountUser
                    ->phone,
            ],
        )
        ->assertSessionHasNoErrors();

    expect(
        $this
            ->accountUser
            ->refresh()
            ->email_verified_at,
    )->not->toBeNull();
});
