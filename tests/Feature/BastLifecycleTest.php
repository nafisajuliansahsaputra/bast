<?php

use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

function lifecycleRole(
    string $slug,
): Role {
    return Role::query()->create([
        'name' => str($slug)
            ->headline()
            ->toString(),

        'slug' => $slug,
        'description' => null,
        'is_active' => true,
    ]);
}

function lifecycleDepartment(): Department
{
    return Department::query()->create([
        'name' => 'Lifecycle Department',
        'code' => 'LIFE',
        'description' => null,
        'is_active' => true,
    ]);
}

function lifecycleType(): BastType
{
    return BastType::query()->create([
        'name' => 'Lifecycle Type',
        'slug' => 'lifecycle-type',
        'description' => null,
        'is_active' => true,
    ]);
}

function lifecycleBast(
    User $user,
    Department $department,
    BastType $type,
    string $status,
): Bast {
    return Bast::query()->create([
        'document_number' => '900/BAST/DISKOMINFO/VIII/2026',

        'sequence_number' => 900,

        'document_code' => 'BAST',

        'document_month' => 8,
        'document_year' => 2026,

        'bast_type_id' => $type->id,

        'department_id' => $department->id,

        'created_by' => $user->id,

        'title' => 'Lifecycle Test BAST',

        'description' => null,

        'document_date' => '2026-08-20',

        'handover_date' => '2026-08-20',

        'handover_place' => 'Cianjur',

        'status' => $status,

        'finalized_at' => now(),

        'finalized_by' => $user->id,

        'completed_at' => in_array(
            $status,
            [
                Bast::STATUS_COMPLETED,
                Bast::STATUS_ARCHIVED,
            ],
            true,
        )
                ? now()
                : null,

        'completed_by' => in_array(
            $status,
            [
                Bast::STATUS_COMPLETED,
                Bast::STATUS_ARCHIVED,
            ],
            true,
        )
                ? $user->id
                : null,

        'archived_at' => $status === Bast::STATUS_ARCHIVED
                ? now()
                : null,

        'archived_by' => $status === Bast::STATUS_ARCHIVED
                ? $user->id
                : null,
    ]);
}

beforeEach(function () {
    $this->lifecycleRole =
        lifecycleRole('staff');

    $this->lifecycleDepartment =
        lifecycleDepartment();

    $this->lifecycleType =
        lifecycleType();

    $this->lifecycleUser =
        User::factory()->create([
            'role_id' => $this
                ->lifecycleRole
                ->id,

            'department_id' => $this
                ->lifecycleDepartment
                ->id,
        ]);
});

test('owner can complete finalized bast', function () {
    $bast = lifecycleBast(
        $this->lifecycleUser,
        $this->lifecycleDepartment,
        $this->lifecycleType,
        Bast::STATUS_FINALIZED,
    );

    $this->actingAs(
        $this->lifecycleUser,
    )
        ->post(
            route(
                'bast.complete',
                $bast,
            ),
        )
        ->assertRedirect(
            route(
                'bast.show',
                $bast,
            ),
        );

    $bast->refresh();

    expect($bast->status)
        ->toBe(
            Bast::STATUS_COMPLETED,
        )
        ->and($bast->completed_by)
        ->toBe(
            $this->lifecycleUser->id,
        )
        ->and($bast->completed_at)
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
            'action' => 'BAST_COMPLETED',
        ],
    );
});

test('owner can archive completed bast', function () {
    $bast = lifecycleBast(
        $this->lifecycleUser,
        $this->lifecycleDepartment,
        $this->lifecycleType,
        Bast::STATUS_COMPLETED,
    );

    $this->actingAs(
        $this->lifecycleUser,
    )
        ->post(
            route(
                'bast.archive',
                $bast,
            ),
        )
        ->assertRedirect(
            route(
                'bast.show',
                $bast,
            ),
        );

    $bast->refresh();

    expect($bast->status)
        ->toBe(
            Bast::STATUS_ARCHIVED,
        )
        ->and($bast->archived_by)
        ->toBe(
            $this->lifecycleUser->id,
        )
        ->and($bast->archived_at)
        ->not
        ->toBeNull();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
            'action' => 'BAST_ARCHIVED',
        ],
    );
});

test('admin can restore archived bast', function () {
    $adminRole =
        lifecycleRole('admin');

    $admin = User::factory()
        ->create([
            'role_id' => $adminRole->id,

            'department_id' => $this
                ->lifecycleDepartment
                ->id,
        ]);

    $bast = lifecycleBast(
        $this->lifecycleUser,
        $this->lifecycleDepartment,
        $this->lifecycleType,
        Bast::STATUS_ARCHIVED,
    );

    $this->actingAs($admin)
        ->post(
            route(
                'bast.restore',
                $bast,
            ),
        )
        ->assertRedirect(
            route(
                'bast.show',
                $bast,
            ),
        );

    $bast->refresh();

    expect($bast->status)
        ->toBe(
            Bast::STATUS_COMPLETED,
        )
        ->and($bast->archived_at)
        ->toBeNull()
        ->and($bast->archived_by)
        ->toBeNull();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
            'action' => 'BAST_RESTORED',
        ],
    );
});

test('staff cannot restore archived bast', function () {
    $bast = lifecycleBast(
        $this->lifecycleUser,
        $this->lifecycleDepartment,
        $this->lifecycleType,
        Bast::STATUS_ARCHIVED,
    );

    $this->actingAs(
        $this->lifecycleUser,
    )
        ->post(
            route(
                'bast.restore',
                $bast,
            ),
        )
        ->assertForbidden();

    expect(
        $bast->fresh()?->status,
    )->toBe(
        Bast::STATUS_ARCHIVED,
    );
});

test('draft cannot be marked completed', function () {
    $bast = lifecycleBast(
        $this->lifecycleUser,
        $this->lifecycleDepartment,
        $this->lifecycleType,
        Bast::STATUS_FINALIZED,
    );

    $bast->update([
        'status' => Bast::STATUS_DRAFT,
        'document_number' => null,
        'sequence_number' => null,
        'finalized_at' => null,
        'finalized_by' => null,
    ]);

    $this->actingAs(
        $this->lifecycleUser,
    )
        ->post(
            route(
                'bast.complete',
                $bast,
            ),
        )
        ->assertForbidden();
});
