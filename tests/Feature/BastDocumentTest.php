<?php

use App\Models\Bast;
use App\Models\BastParty;
use App\Models\BastType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;

function documentRole(): Role
{
    return Role::query()->create([
        'name' => 'Document Staff',
        'slug' => 'staff',
        'description' => null,
        'is_active' => true,
    ]);
}

function documentDepartment(): Department
{
    return Department::query()->create([
        'name' => 'Document Department',
        'code' => 'DOC',
        'description' => null,
        'is_active' => true,
    ]);
}

function documentType(): BastType
{
    return BastType::query()->create([
        'name' => 'Serah Terima Barang',
        'slug' => 'document-test',
        'description' => null,
        'is_active' => true,
    ]);
}

function documentBast(
    User $user,
    Department $department,
    BastType $type,
    string $status,
): Bast {
    $bast = Bast::query()->create([
        'document_number' => $status === Bast::STATUS_DRAFT
                ? null
                : '901/BAST/DISKOMINFO/VIII/2026',

        'sequence_number' => $status === Bast::STATUS_DRAFT
                ? null
                : 901,

        'document_code' => 'BAST',

        'document_month' => 8,
        'document_year' => 2026,

        'bast_type_id' => $type->id,

        'department_id' => $department->id,

        'created_by' => $user->id,

        'title' => 'Document Test BAST',

        'description' => 'Dokumen pengujian.',

        'document_date' => '2026-08-20',

        'handover_date' => '2026-08-20',

        'handover_place' => 'Cianjur',

        'status' => $status,

        'finalized_at' => $status === Bast::STATUS_DRAFT
                ? null
                : now(),

        'finalized_by' => $status === Bast::STATUS_DRAFT
                ? null
                : $user->id,
    ]);

    $bast->parties()->createMany([
        [
            'party_type' => BastParty::TYPE_FIRST_PARTY,

            'name' => 'Pihak Pertama',

            'nip' => '111',

            'position' => 'Staff',

            'department' => 'Bidang A',

            'institution' => 'Diskominfo Cianjur',

            'sort_order' => 1,
        ],

        [
            'party_type' => BastParty::TYPE_SECOND_PARTY,

            'name' => 'Pihak Kedua',

            'nip' => '222',

            'position' => 'Staff',

            'department' => 'Bidang B',

            'institution' => 'Diskominfo Cianjur',

            'sort_order' => 2,
        ],
    ]);

    $bast->items()->create([
        'name' => 'Laptop Test',

        'quantity' => 1,

        'condition' => 'baik',

        'sort_order' => 1,
    ]);

    return $bast;
}

beforeEach(function () {
    $this->documentRole =
        documentRole();

    $this->documentDepartment =
        documentDepartment();

    $this->documentType =
        documentType();

    $this->documentUser =
        User::factory()->create([
            'role_id' => $this
                ->documentRole
                ->id,

            'department_id' => $this
                ->documentDepartment
                ->id,
        ]);
});

test('owner can preview bast document', function () {
    $bast = documentBast(
        $this->documentUser,
        $this->documentDepartment,
        $this->documentType,
        Bast::STATUS_FINALIZED,
    );

    $this->actingAs(
        $this->documentUser,
    )
        ->get(
            route(
                'bast.preview',
                $bast,
            ),
        )
        ->assertOk()
        ->assertSee(
            'Berita Acara Serah Terima',
        )
        ->assertSee(
            '901/BAST/DISKOMINFO/VIII/2026',
        )
        ->assertSee(
            'Laptop Test',
        );
});

test('owner can download finalized bast pdf', function () {
    $bast = documentBast(
        $this->documentUser,
        $this->documentDepartment,
        $this->documentType,
        Bast::STATUS_FINALIZED,
    );

    $this->actingAs(
        $this->documentUser,
    )
        ->get(
            route(
                'bast.pdf',
                $bast,
            ),
        )
        ->assertOk()
        ->assertHeader(
            'content-type',
            'application/pdf',
        );

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
            'action' => 'PDF_GENERATED',
        ],
    );
});

test('draft bast cannot be downloaded as pdf', function () {
    $bast = documentBast(
        $this->documentUser,
        $this->documentDepartment,
        $this->documentType,
        Bast::STATUS_DRAFT,
    );

    $this->actingAs(
        $this->documentUser,
    )
        ->get(
            route(
                'bast.pdf',
                $bast,
            ),
        )
        ->assertForbidden();
});
