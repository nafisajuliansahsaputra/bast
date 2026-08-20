<?php

use App\Models\ActivityLog;
use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->masterAdminRole =
        Role::query()->create([
            'name' => 'Admin Master',
            'slug' => 'admin',
            'description' => null,
            'is_active' => true,
        ]);

    $this->masterStaffRole =
        Role::query()->create([
            'name' => 'Staff Master',
            'slug' => 'staff',
            'description' => null,
            'is_active' => true,
        ]);

    $this->masterDepartment =
        Department::query()->create([
            'name' => 'Master Test',
            'code' => 'MASTER',
            'description' => null,
            'is_active' => true,
        ]);

    $this->masterAdmin =
        User::factory()->create([
            'role_id' => $this
                ->masterAdminRole
                ->id,

            'department_id' => $this
                ->masterDepartment
                ->id,
        ]);

    $this->masterStaff =
        User::factory()->create([
            'role_id' => $this
                ->masterStaffRole
                ->id,

            'department_id' => $this
                ->masterDepartment
                ->id,
        ]);
});

test('admin can view master data page', function () {
    $this->actingAs(
        $this->masterAdmin,
    )
        ->get(
            route('master.index'),
        )
        ->assertOk();
});

test('staff cannot view master data page', function () {
    $this->actingAs(
        $this->masterStaff,
    )
        ->get(
            route('master.index'),
        )
        ->assertForbidden();
});

test('admin can create bast type', function () {
    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->post(
            route(
                'master.bast-types.store',
            ),
            [
                'name' => 'Serah Terima Kendaraan',
                'description' => 'BAST kendaraan dinas.',
            ],
        )
        ->assertRedirect(
            route('master.index'),
        );

    $this->assertDatabaseHas(
        'bast_types',
        [
            'name' => 'Serah Terima Kendaraan',
            'slug' => 'serah-terima-kendaraan',
            'is_active' => true,
        ],
    );

    expect(
        ActivityLog::query()
            ->where(
                'action',
                'MASTER_BAST_TYPE_CREATED',
            )
            ->exists(),
    )->toBeTrue();
});

test('admin can update bast type', function () {
    $bastType = BastType::query()
        ->create([
            'name' => 'Jenis Lama',
            'slug' => 'jenis-lama',
            'description' => null,
            'is_active' => true,
        ]);

    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->put(
            route(
                'master.bast-types.update',
                $bastType,
            ),
            [
                'name' => 'Jenis Baru',
                'description' => 'Sudah diperbarui.',
            ],
        )
        ->assertRedirect(
            route('master.index'),
        );

    $this->assertDatabaseHas(
        'bast_types',
        [
            'id' => $bastType->id,
            'name' => 'Jenis Baru',
            'slug' => 'jenis-baru',
        ],
    );
});

test('admin can create department with uppercase code', function () {
    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->post(
            route(
                'master.departments.store',
            ),
            [
                'name' => 'Teknologi Informasi',
                'code' => 'tik',
                'description' => null,
            ],
        )
        ->assertRedirect(
            route('master.index'),
        );

    $this->assertDatabaseHas(
        'departments',
        [
            'name' => 'Teknologi Informasi',
            'code' => 'TIK',
            'is_active' => true,
        ],
    );
});

test('department with active user cannot be disabled', function () {
    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->patch(
            route(
                'master.departments.toggle',
                $this->masterDepartment,
            ),
        )
        ->assertRedirect(
            route('master.index'),
        )
        ->assertSessionHasErrors(
            'department',
        );

    expect(
        $this->masterDepartment
            ->fresh()
            ?->is_active,
    )->toBeTrue();
});

test('admin can create and toggle item category', function () {
    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->post(
            route(
                'master.item-categories.store',
            ),
            [
                'name' => 'Kendaraan',
                'description' => 'Kendaraan dinas.',
            ],
        )
        ->assertRedirect(
            route('master.index'),
        );

    $category = ItemCategory::query()
        ->where(
            'name',
            'Kendaraan',
        )
        ->firstOrFail();

    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->patch(
            route(
                'master.item-categories.toggle',
                $category,
            ),
        )
        ->assertRedirect(
            route('master.index'),
        );

    expect(
        $category
            ->fresh()
            ?->is_active,
    )->toBeFalse();
});

test('admin can create update and toggle unit', function () {
    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->post(
            route(
                'master.units.store',
            ),
            [
                'name' => 'Karton',
                'symbol' => 'karton',
                'description' => null,
            ],
        )
        ->assertRedirect(
            route('master.index'),
        );

    $unit = Unit::query()
        ->where(
            'name',
            'Karton',
        )
        ->firstOrFail();

    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->put(
            route(
                'master.units.update',
                $unit,
            ),
            [
                'name' => 'Dus',
                'symbol' => 'dus',
                'description' => 'Satuan dus.',
            ],
        )
        ->assertRedirect(
            route('master.index'),
        );

    $unit->refresh();

    expect($unit->name)
        ->toBe('Dus')
        ->and($unit->symbol)
        ->toBe('dus');

    $this->actingAs(
        $this->masterAdmin,
    )
        ->from(
            route('master.index'),
        )
        ->patch(
            route(
                'master.units.toggle',
                $unit,
            ),
        )
        ->assertRedirect(
            route('master.index'),
        );

    expect(
        $unit
            ->fresh()
            ?->is_active,
    )->toBeFalse();
});

test('staff cannot mutate master data', function () {
    $this->actingAs(
        $this->masterStaff,
    )
        ->post(
            route(
                'master.bast-types.store',
            ),
            [
                'name' => 'Tidak Boleh',
                'description' => null,
            ],
        )
        ->assertForbidden();

    $this->assertDatabaseMissing(
        'bast_types',
        [
            'name' => 'Tidak Boleh',
        ],
    );
});
