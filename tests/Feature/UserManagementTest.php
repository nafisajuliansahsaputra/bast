<?php

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->userManagementSuperAdminRole =
        Role::query()->create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => null,
            'is_active' => true,
        ]);

    $this->userManagementAdminRole =
        Role::query()->create([
            'name' => 'Admin',
            'slug' => 'admin',
            'description' => null,
            'is_active' => true,
        ]);

    $this->userManagementStaffRole =
        Role::query()->create([
            'name' => 'Staff',
            'slug' => 'staff',
            'description' => null,
            'is_active' => true,
        ]);

    $this->userManagementDepartment =
        Department::query()->create([
            'name' => 'Administrasi Test',
            'code' => 'USR',
            'description' => null,
            'is_active' => true,
        ]);

    $this->userManagementSuperAdmin =
        User::factory()->create([
            'role_id' => $this
                ->userManagementSuperAdminRole
                ->id,

            'department_id' => $this
                ->userManagementDepartment
                ->id,
        ]);

    $this->userManagementStaff =
        User::factory()->create([
            'role_id' => $this
                ->userManagementStaffRole
                ->id,

            'department_id' => $this
                ->userManagementDepartment
                ->id,
        ]);
});

test('super admin can view users page', function () {
    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->get(
            route('users.index'),
        )
        ->assertOk();
});

test('staff cannot view users page', function () {
    $this->actingAs(
        $this->userManagementStaff,
    )
        ->get(
            route('users.index'),
        )
        ->assertForbidden();
});

test('super admin can create user and receives temporary credential', function () {
    $response = $this
        ->actingAs(
            $this->userManagementSuperAdmin,
        )
        ->post(
            route('users.store'),
            [
                'name' => 'Staff Baru',

                'nip' => '1987654321',

                'email' => 'staff.baru@example.test',

                'position' => 'Pranata Komputer',

                'phone' => '08123456789',

                'role_id' => $this
                    ->userManagementStaffRole
                    ->id,

                'department_id' => $this
                    ->userManagementDepartment
                    ->id,
            ],
        );

    $response
        ->assertRedirect(
            route('users.index'),
        )
        ->assertSessionHas(
            'temporary_credential',
        );

    $managedUser = User::query()
        ->where(
            'email',
            'staff.baru@example.test',
        )
        ->firstOrFail();

    $credential = session(
        'temporary_credential',
    );

    expect($credential)
        ->toBeArray()
        ->and(
            $credential['email'],
        )
        ->toBe(
            'staff.baru@example.test',
        )
        ->and(
            Hash::check(
                $credential['password'],
                $managedUser->password,
            ),
        )
        ->toBeTrue()
        ->and(
            $managedUser
                ->email_verified_at,
        )
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'action' => 'USER_CREATED',

            'subject_type' => User::class,

            'subject_id' => $managedUser->id,
        ],
    );
});

test('super admin can update another user', function () {
    $managedUser =
        User::factory()->create([
            'role_id' => $this
                ->userManagementStaffRole
                ->id,

            'department_id' => $this
                ->userManagementDepartment
                ->id,

            'name' => 'Nama Lama',

            'email' => 'lama@example.test',
        ]);

    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->put(
            route(
                'users.update',
                $managedUser,
            ),
            [
                'name' => 'Nama Baru',

                'nip' => '111222333',

                'email' => 'baru@example.test',

                'position' => 'Administrator',

                'phone' => '081111111',

                'role_id' => $this
                    ->userManagementAdminRole
                    ->id,

                'department_id' => $this
                    ->userManagementDepartment
                    ->id,
            ],
        )
        ->assertRedirect();

    $managedUser->refresh();

    expect($managedUser->name)
        ->toBe('Nama Baru')
        ->and($managedUser->email)
        ->toBe(
            'baru@example.test',
        )
        ->and($managedUser->role_id)
        ->toBe(
            $this->userManagementAdminRole->id,
        );

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'action' => 'USER_UPDATED',

            'subject_type' => User::class,

            'subject_id' => $managedUser->id,
        ],
    );
});

test('super admin can deactivate and reactivate another user', function () {
    $managedUser =
        User::factory()->create([
            'role_id' => $this
                ->userManagementStaffRole
                ->id,

            'department_id' => $this
                ->userManagementDepartment
                ->id,

            'status' => 'active',
        ]);

    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->patch(
            route(
                'users.toggle-status',
                $managedUser,
            ),
        )
        ->assertRedirect();

    expect(
        $managedUser
            ->fresh()
            ?->status,
    )->toBe('inactive');

    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->patch(
            route(
                'users.toggle-status',
                $managedUser,
            ),
        )
        ->assertRedirect();

    expect(
        $managedUser
            ->fresh()
            ?->status,
    )->toBe('active');
});

test('super admin cannot deactivate own account', function () {
    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->from(
            route('users.index'),
        )
        ->patch(
            route(
                'users.toggle-status',
                $this->userManagementSuperAdmin,
            ),
        )
        ->assertRedirect(
            route('users.index'),
        )
        ->assertSessionHasErrors(
            'user_status',
        );

    expect(
        $this
            ->userManagementSuperAdmin
            ->fresh()
            ?->status,
    )->toBe('active');
});

test('super admin cannot change own role', function () {
    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->from(
            route('users.index'),
        )
        ->put(
            route(
                'users.update',
                $this->userManagementSuperAdmin,
            ),
            [
                'name' => $this
                    ->userManagementSuperAdmin
                    ->name,

                'nip' => null,

                'email' => $this
                    ->userManagementSuperAdmin
                    ->email,

                'position' => null,

                'phone' => null,

                'role_id' => $this
                    ->userManagementStaffRole
                    ->id,

                'department_id' => $this
                    ->userManagementDepartment
                    ->id,
            ],
        )
        ->assertRedirect(
            route('users.index'),
        )
        ->assertSessionHasErrors(
            'role_id',
        );

    expect(
        $this
            ->userManagementSuperAdmin
            ->fresh()
            ?->role_id,
    )->toBe(
        $this
            ->userManagementSuperAdminRole
            ->id,
    );
});

test('super admin can reset another users password and two factor', function () {
    $managedUser =
        User::factory()
            ->withTwoFactor()
            ->create([
                'role_id' => $this
                    ->userManagementStaffRole
                    ->id,

                'department_id' => $this
                    ->userManagementDepartment
                    ->id,
            ]);

    $oldPassword =
        $managedUser->password;

    $response = $this
        ->actingAs(
            $this->userManagementSuperAdmin,
        )
        ->post(
            route(
                'users.reset-password',
                $managedUser,
            ),
        );

    $response
        ->assertRedirect()
        ->assertSessionHas(
            'temporary_credential',
        );

    $managedUser->refresh();

    $credential = session(
        'temporary_credential',
    );

    expect($managedUser->password)
        ->not
        ->toBe($oldPassword)
        ->and(
            Hash::check(
                $credential['password'],
                $managedUser->password,
            ),
        )
        ->toBeTrue()
        ->and(
            $managedUser
                ->two_factor_secret,
        )
        ->toBeNull()
        ->and(
            $managedUser
                ->two_factor_recovery_codes,
        )
        ->toBeNull()
        ->and(
            $managedUser
                ->two_factor_confirmed_at,
        )
        ->toBeNull();

    expect(
        ActivityLog::query()
            ->where(
                'action',
                'USER_PASSWORD_RESET',
            )
            ->where(
                'subject_id',
                $managedUser->id,
            )
            ->exists(),
    )->toBeTrue();
});

test('super admin cannot reset own password from user management', function () {
    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->from(
            route('users.index'),
        )
        ->post(
            route(
                'users.reset-password',
                $this->userManagementSuperAdmin,
            ),
        )
        ->assertRedirect(
            route('users.index'),
        )
        ->assertSessionHasErrors(
            'password_reset',
        );
});

test('super admin can view user detail', function () {
    $this->actingAs(
        $this->userManagementSuperAdmin,
    )
        ->get(
            route(
                'users.show',
                $this->userManagementStaff,
            ),
        )
        ->assertOk();
});
