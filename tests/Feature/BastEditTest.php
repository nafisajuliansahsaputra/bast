<?php

use App\Models\Bast;
use App\Models\BastParty;
use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;

function editDraftRole(): Role
{
    return Role::query()->create([
        'name' => 'Staff Edit',
        'slug' => 'staff',
        'description' => null,
        'is_active' => true,
    ]);
}

function editDraftDepartment(
    string $code,
): Department {
    return Department::query()->create([
        'name' => "Edit Department {$code}",
        'code' => $code,
        'description' => null,
        'is_active' => true,
    ]);
}

function editDraftBast(
    User $user,
    Department $department,
    BastType $type,
): Bast {
    $bast = Bast::query()->create([
        'document_number' => null,
        'sequence_number' => null,
        'document_code' => 'BAST',

        'document_month' => 8,
        'document_year' => 2026,

        'bast_type_id' => $type->id,
        'department_id' => $department->id,
        'created_by' => $user->id,

        'title' => 'Draft Lama',
        'description' => 'Deskripsi lama',

        'document_date' => '2026-08-20',
        'handover_date' => '2026-08-20',
        'handover_place' => 'Lokasi Lama',

        'status' => Bast::STATUS_DRAFT,
    ]);

    $bast->parties()->createMany([
        [
            'party_type' => BastParty::TYPE_FIRST_PARTY,
            'name' => 'Pihak Lama A',
            'institution' => 'Instansi Lama A',
            'sort_order' => 1,
        ],

        [
            'party_type' => BastParty::TYPE_SECOND_PARTY,
            'name' => 'Pihak Lama B',
            'institution' => 'Instansi Lama B',
            'sort_order' => 2,
        ],
    ]);

    $bast->items()->create([
        'name' => 'Item Lama',
        'quantity' => 1,
        'condition' => 'baik',
        'sort_order' => 1,
    ]);

    return $bast;
}

beforeEach(function () {
    $this->editRole =
        editDraftRole();

    $this->editDepartment =
        editDraftDepartment('EDIT');

    $this->editType =
        BastType::query()->create([
            'name' => 'Edit Type',
            'slug' => 'edit-type',
            'description' => null,
            'is_active' => true,
        ]);

    $this->editCategory =
        ItemCategory::query()->create([
            'name' => 'Edit Category',
            'slug' => 'edit-category',
            'description' => null,
            'is_active' => true,
        ]);

    $this->editUnit =
        Unit::query()->create([
            'name' => 'Unit Edit',
            'symbol' => 'unit',
            'description' => null,
            'is_active' => true,
        ]);

    $this->editUser =
        User::factory()->create([
            'role_id' => $this
                ->editRole
                ->id,

            'department_id' => $this
                ->editDepartment
                ->id,
        ]);

    $this->editBast =
        editDraftBast(
            $this->editUser,
            $this->editDepartment,
            $this->editType,
        );
});

/**
 * @return array<string, mixed>
 */
function validEditDraftPayload(
    Department $department,
    BastType $type,
    ItemCategory $category,
    Unit $unit,
): array {
    return [
        'bast_type_id' => $type->id,
        'department_id' => $department->id,

        'title' => 'Draft Sudah Diubah',

        'description' => 'Deskripsi baru',

        'document_date' => '2026-09-01',

        'handover_date' => '2026-09-02',

        'handover_place' => 'Lokasi Baru',

        'parties' => [
            'first_party' => [
                'user_id' => null,
                'name' => 'Pihak Baru A',
                'nip' => '111',
                'position' => 'Staff A',
                'department' => 'Bidang A',
                'institution' => 'Instansi Baru A',
                'address' => null,
            ],

            'second_party' => [
                'user_id' => null,
                'name' => 'Pihak Baru B',
                'nip' => '222',
                'position' => 'Staff B',
                'department' => 'Bidang B',
                'institution' => 'Instansi Baru B',
                'address' => null,
            ],
        ],

        'items' => [
            [
                'item_category_id' => $category->id,
                'unit_id' => $unit->id,
                'name' => 'Laptop Baru',
                'code' => 'NEW-001',
                'inventory_number' => null,
                'serial_number' => null,
                'quantity' => 2,
                'condition' => 'baik',
                'value' => 12000000,
                'description' => null,
            ],

            [
                'item_category_id' => $category->id,
                'unit_id' => $unit->id,
                'name' => 'Monitor Baru',
                'code' => 'NEW-002',
                'inventory_number' => null,
                'serial_number' => null,
                'quantity' => 1,
                'condition' => 'baik',
                'value' => 3000000,
                'description' => null,
            ],
        ],
    ];
}

test('owner can open draft edit page', function () {
    $this->actingAs(
        $this->editUser,
    )
        ->get(
            route(
                'bast.edit',
                $this->editBast,
            ),
        )
        ->assertOk();
});

test('owner can update draft bast', function () {
    $payload =
        validEditDraftPayload(
            $this->editDepartment,
            $this->editType,
            $this->editCategory,
            $this->editUnit,
        );

    $this->actingAs(
        $this->editUser,
    )
        ->put(
            route(
                'bast.update',
                $this->editBast,
            ),
            $payload,
        )
        ->assertRedirect(
            route(
                'bast.show',
                $this->editBast,
            ),
        );

    $this->editBast->refresh();

    expect($this->editBast->title)
        ->toBe('Draft Sudah Diubah')
        ->and(
            $this->editBast
                ->document_month,
        )
        ->toBe(9)
        ->and(
            $this->editBast
                ->document_year,
        )
        ->toBe(2026);

    $this->assertDatabaseHas(
        'bast_parties',
        [
            'bast_id' => $this
                ->editBast
                ->id,

            'name' => 'Pihak Baru A',
        ],
    );

    $this->assertDatabaseMissing(
        'bast_parties',
        [
            'bast_id' => $this
                ->editBast
                ->id,

            'name' => 'Pihak Lama A',
        ],
    );

    expect(
        $this->editBast
            ->items()
            ->count(),
    )->toBe(2);

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $this
                ->editBast
                ->id,

            'action' => 'BAST_UPDATED',
        ],
    );
});

test('staff cannot move draft to another department', function () {
    $otherDepartment =
        editDraftDepartment('OTHER');

    $payload =
        validEditDraftPayload(
            $otherDepartment,
            $this->editType,
            $this->editCategory,
            $this->editUnit,
        );

    $this->actingAs(
        $this->editUser,
    )->put(
        route(
            'bast.update',
            $this->editBast,
        ),
        $payload,
    );

    expect(
        $this->editBast
            ->fresh()
            ?->department_id,
    )->toBe(
        $this->editDepartment->id,
    );
});

test('finalized bast cannot be edited', function () {
    $this->editBast->update([
        'status' => Bast::STATUS_FINALIZED,

        'document_number' => '999/BAST/DISKOMINFO/VIII/2026',

        'sequence_number' => 999,

        'finalized_at' => now(),

        'finalized_by' => $this->editUser->id,
    ]);

    $this->actingAs(
        $this->editUser,
    )
        ->get(
            route(
                'bast.edit',
                $this->editBast,
            ),
        )
        ->assertForbidden();

    $payload =
        validEditDraftPayload(
            $this->editDepartment,
            $this->editType,
            $this->editCategory,
            $this->editUnit,
        );

    $this->actingAs(
        $this->editUser,
    )
        ->put(
            route(
                'bast.update',
                $this->editBast,
            ),
            $payload,
        )
        ->assertForbidden();
});
