<?php

use App\Models\ActivityLog;
use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;

function createBastRole(string $slug): Role
{
    return Role::query()->create([
        'name' => str($slug)->headline()->toString(),
        'slug' => $slug,
        'description' => null,
        'is_active' => true,
    ]);
}

function createBastDepartment(
    string $code,
): Department {
    return Department::query()->create([
        'name' => "Department {$code}",
        'code' => $code,
        'description' => null,
        'is_active' => true,
    ]);
}

/**
 * @return array<string, mixed>
 */
function validBastPayload(
    Department $department,
    BastType $type,
    ItemCategory $category,
    Unit $unit,
): array {
    return [
        'bast_type_id' => $type->id,
        'department_id' => $department->id,

        'title' => 'Serah Terima Laptop',
        'description' => 'Dokumen pengujian BAST.',

        'document_date' => '2026-08-20',
        'handover_date' => '2026-08-20',
        'handover_place' => 'Kantor Diskominfo',

        'parties' => [
            'first_party' => [
                'user_id' => null,
                'name' => 'Pihak Pertama',
                'nip' => '10001',
                'position' => 'Staff',
                'department' => 'Bidang A',
                'institution' => 'Instansi A',
                'address' => null,
            ],

            'second_party' => [
                'user_id' => null,
                'name' => 'Pihak Kedua',
                'nip' => '10002',
                'position' => 'Staff',
                'department' => 'Bidang B',
                'institution' => 'Instansi B',
                'address' => null,
            ],
        ],

        'items' => [
            [
                'item_category_id' => $category->id,
                'unit_id' => $unit->id,
                'name' => 'Laptop',
                'code' => 'LPT-001',
                'inventory_number' => 'INV-001',
                'serial_number' => 'SN-001',
                'quantity' => 1,
                'condition' => 'baik',
                'value' => 10000000,
                'description' => null,
            ],
        ],
    ];
}

beforeEach(function () {
    $this->type = BastType::query()->create([
        'name' => 'Serah Terima Barang',
        'slug' => 'test-barang',
        'description' => null,
        'is_active' => true,
    ]);

    $this->category = ItemCategory::query()->create([
        'name' => 'Perangkat Test',
        'slug' => 'perangkat-test',
        'description' => null,
        'is_active' => true,
    ]);

    $this->unit = Unit::query()->create([
        'name' => 'Unit Test',
        'symbol' => 'unit-test',
        'description' => null,
        'is_active' => true,
    ]);
});

test('authenticated user can open bast index', function () {
    $role = createBastRole('staff');
    $department = createBastDepartment('IDX');

    $user = User::factory()->create([
        'role_id' => $role->id,
        'department_id' => $department->id,
    ]);

    $this->actingAs($user)
        ->get('/bast')
        ->assertOk();
});

test('staff can create bast draft with parties and items', function () {
    $role = createBastRole('staff');
    $department = createBastDepartment('STAFF');

    $user = User::factory()->create([
        'role_id' => $role->id,
        'department_id' => $department->id,
    ]);

    $payload = validBastPayload(
        $department,
        $this->type,
        $this->category,
        $this->unit,
    );

    $response = $this->actingAs($user)
        ->post('/bast', $payload);

    $bast = Bast::query()->firstOrFail();

    $response->assertRedirect(
        route('bast.show', $bast),
    );

    expect($bast->status)
        ->toBe(Bast::STATUS_DRAFT)
        ->and($bast->department_id)
        ->toBe($department->id)
        ->and($bast->created_by)
        ->toBe($user->id);

    $this->assertDatabaseCount(
        'bast_parties',
        2,
    );

    $this->assertDatabaseCount(
        'bast_items',
        1,
    );

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'user_id' => $user->id,
            'action' => 'BAST_CREATED',
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
        ],
    );
});

test('staff cannot force bast into another department', function () {
    $role = createBastRole('staff');

    $ownDepartment =
        createBastDepartment('OWN');

    $otherDepartment =
        createBastDepartment('OTHER');

    $user = User::factory()->create([
        'role_id' => $role->id,
        'department_id' => $ownDepartment->id,
    ]);

    $payload = validBastPayload(
        $otherDepartment,
        $this->type,
        $this->category,
        $this->unit,
    );

    $this->actingAs($user)
        ->post('/bast', $payload)
        ->assertRedirect();

    $bast = Bast::query()->firstOrFail();

    expect($bast->department_id)
        ->toBe($ownDepartment->id);
});

test('staff cannot view bast owned by another staff', function () {
    $role = createBastRole('staff');
    $department = createBastDepartment('VIEW');

    $firstUser = User::factory()->create([
        'role_id' => $role->id,
        'department_id' => $department->id,
    ]);

    $secondUser = User::factory()->create([
        'role_id' => $role->id,
        'department_id' => $department->id,
    ]);

    $bast = Bast::query()->create([
        'document_number' => null,
        'sequence_number' => null,
        'document_code' => 'BAST',
        'document_month' => 8,
        'document_year' => 2026,
        'bast_type_id' => $this->type->id,
        'department_id' => $department->id,
        'created_by' => $firstUser->id,
        'title' => 'Private Staff BAST',
        'description' => null,
        'document_date' => '2026-08-20',
        'handover_date' => '2026-08-20',
        'handover_place' => 'Cianjur',
        'status' => Bast::STATUS_DRAFT,
    ]);

    $this->actingAs($secondUser)
        ->get(route('bast.show', $bast))
        ->assertForbidden();
});

test('admin can view bast created by other user', function () {
    $staffRole = createBastRole('staff');
    $adminRole = createBastRole('admin');

    $department =
        createBastDepartment('ADMIN');

    $staff = User::factory()->create([
        'role_id' => $staffRole->id,
        'department_id' => $department->id,
    ]);

    $admin = User::factory()->create([
        'role_id' => $adminRole->id,
        'department_id' => $department->id,
    ]);

    $bast = Bast::query()->create([
        'document_number' => null,
        'sequence_number' => null,
        'document_code' => 'BAST',
        'document_month' => 8,
        'document_year' => 2026,
        'bast_type_id' => $this->type->id,
        'department_id' => $department->id,
        'created_by' => $staff->id,
        'title' => 'Visible To Admin',
        'description' => null,
        'document_date' => '2026-08-20',
        'handover_date' => '2026-08-20',
        'handover_place' => 'Cianjur',
        'status' => Bast::STATUS_DRAFT,
    ]);

    $this->actingAs($admin)
        ->get(route('bast.show', $bast))
        ->assertOk();
});

test('bast creation requires party and item information', function () {
    $role = createBastRole('staff');
    $department = createBastDepartment('VALIDATE');

    $user = User::factory()->create([
        'role_id' => $role->id,
        'department_id' => $department->id,
    ]);

    $this->actingAs($user)
        ->post('/bast', [
            'bast_type_id' => $this->type->id,
            'department_id' => $department->id,
            'title' => 'Incomplete BAST',
            'document_date' => '2026-08-20',
            'handover_date' => '2026-08-20',
            'handover_place' => 'Cianjur',
        ])
        ->assertSessionHasErrors([
            'parties',
            'items',
        ]);

    expect(Bast::query()->count())
        ->toBe(0);

    expect(ActivityLog::query()->count())
        ->toBe(0);
});
