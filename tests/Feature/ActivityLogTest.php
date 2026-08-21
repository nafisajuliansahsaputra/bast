<?php

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->activitySuperAdminRole =
        Role::query()->create([
            'name' => 'Super Admin Activity',
            'slug' => 'super-admin',
            'description' => null,
            'is_active' => true,
        ]);

    $this->activityAdminRole =
        Role::query()->create([
            'name' => 'Admin Activity',
            'slug' => 'admin',
            'description' => null,
            'is_active' => true,
        ]);

    $this->activityStaffRole =
        Role::query()->create([
            'name' => 'Staff Activity',
            'slug' => 'staff',
            'description' => null,
            'is_active' => true,
        ]);

    $this->activityDepartment =
        Department::query()->create([
            'name' => 'Activity Test',
            'code' => 'ACT',
            'description' => null,
            'is_active' => true,
        ]);

    $this->activitySuperAdmin =
        User::factory()->create([
            'role_id' => $this
                ->activitySuperAdminRole
                ->id,

            'department_id' => $this
                ->activityDepartment
                ->id,
        ]);

    $this->activityAdmin =
        User::factory()->create([
            'role_id' => $this
                ->activityAdminRole
                ->id,

            'department_id' => $this
                ->activityDepartment
                ->id,
        ]);

    $this->activityStaff =
        User::factory()->create([
            'role_id' => $this
                ->activityStaffRole
                ->id,

            'department_id' => $this
                ->activityDepartment
                ->id,
        ]);
});

test('admin can view activity log page', function () {
    ActivityLog::query()->create([
        'user_id' => $this
            ->activityAdmin
            ->id,

        'action' => 'BAST_CREATED',

        'description' => 'Membuat draft BAST.',

        'subject_type' => null,
        'subject_id' => null,

        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',

        'old_values' => null,

        'new_values' => [
            'status' => 'draft',
        ],
    ]);

    $this->actingAs(
        $this->activityAdmin,
    )
        ->get(
            route(
                'activity-logs.index',
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'activity-logs/index',
                )
                ->has(
                    'activityLogs.data',
                    1,
                )
                ->where(
                    'activityLogs.data.0.action',
                    'BAST_CREATED',
                ),
        );
});

test('super admin can view activity log page', function () {
    $this->actingAs(
        $this->activitySuperAdmin,
    )
        ->get(
            route(
                'activity-logs.index',
            ),
        )
        ->assertOk();
});

test('staff cannot view activity log page', function () {
    $this->actingAs(
        $this->activityStaff,
    )
        ->get(
            route(
                'activity-logs.index',
            ),
        )
        ->assertForbidden();
});

test('activity logs can be filtered by category', function () {
    ActivityLog::query()->create([
        'user_id' => $this
            ->activityAdmin
            ->id,

        'action' => 'BAST_UPDATED',
        'description' => 'Memperbarui BAST.',

        'subject_type' => null,
        'subject_id' => null,

        'old_values' => [
            'title' => 'Lama',
        ],

        'new_values' => [
            'title' => 'Baru',
        ],
    ]);

    ActivityLog::query()->create([
        'user_id' => $this
            ->activitySuperAdmin
            ->id,

        'action' => 'USER_UPDATED',
        'description' => 'Memperbarui pengguna.',

        'subject_type' => null,
        'subject_id' => null,

        'old_values' => null,
        'new_values' => null,
    ]);

    $this->actingAs(
        $this->activityAdmin,
    )
        ->get(
            route(
                'activity-logs.index',
                [
                    'category' => 'bast',
                ],
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has(
                    'activityLogs.data',
                    1,
                )
                ->where(
                    'activityLogs.data.0.action',
                    'BAST_UPDATED',
                )
                ->where(
                    'filters.category',
                    'bast',
                ),
        );
});

test('activity logs can be filtered by user', function () {
    ActivityLog::query()->create([
        'user_id' => $this
            ->activityAdmin
            ->id,

        'action' => 'BAST_CREATED',
        'description' => 'Aktivitas admin.',

        'subject_type' => null,
        'subject_id' => null,
    ]);

    ActivityLog::query()->create([
        'user_id' => $this
            ->activitySuperAdmin
            ->id,

        'action' => 'USER_CREATED',
        'description' => 'Aktivitas super admin.',

        'subject_type' => null,
        'subject_id' => null,
    ]);

    $this->actingAs(
        $this->activityAdmin,
    )
        ->get(
            route(
                'activity-logs.index',
                [
                    'user' => $this
                        ->activityAdmin
                        ->id,
                ],
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->has(
                    'activityLogs.data',
                    1,
                )
                ->where(
                    'activityLogs.data.0.user.id',
                    $this
                        ->activityAdmin
                        ->id,
                ),
        );
});

test('admin can view activity log detail', function () {
    $activityLog =
        ActivityLog::query()->create([
            'user_id' => $this
                ->activityAdmin
                ->id,

            'action' => 'BAST_UPDATED',

            'description' => 'Memperbarui draft BAST.',

            'subject_type' => null,
            'subject_id' => null,

            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest Browser',

            'old_values' => [
                'title' => 'Judul Lama',
                'status' => 'draft',
            ],

            'new_values' => [
                'title' => 'Judul Baru',
                'status' => 'draft',
            ],
        ]);

    $this->actingAs(
        $this->activityAdmin,
    )
        ->get(
            route(
                'activity-logs.show',
                $activityLog,
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'activity-logs/show',
                )
                ->where(
                    'activityLog.id',
                    $activityLog->id,
                )
                ->where(
                    'activityLog.action',
                    'BAST_UPDATED',
                )
                ->where(
                    'activityLog.old_values.title',
                    'Judul Lama',
                )
                ->where(
                    'activityLog.new_values.title',
                    'Judul Baru',
                ),
        );
});

test('staff cannot view activity log detail', function () {
    $activityLog =
        ActivityLog::query()->create([
            'user_id' => $this
                ->activityAdmin
                ->id,

            'action' => 'BAST_CREATED',
            'description' => 'Membuat BAST.',

            'subject_type' => null,
            'subject_id' => null,
        ]);

    $this->actingAs(
        $this->activityStaff,
    )
        ->get(
            route(
                'activity-logs.show',
                $activityLog,
            ),
        )
        ->assertForbidden();
});
