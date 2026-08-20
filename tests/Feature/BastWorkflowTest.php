<?php

use App\Models\Bast;
use App\Models\BastParty;
use App\Models\BastType;
use App\Models\Department;
use App\Models\DocumentSequence;
use App\Models\Role;
use App\Models\User;

function createWorkflowRole(
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

function createWorkflowDepartment(): Department
{
    return Department::query()->create([
        'name' => 'Workflow Department',
        'code' => 'WF',
        'description' => null,
        'is_active' => true,
    ]);
}

function createWorkflowBast(
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

        'title' => 'Workflow BAST',
        'description' => null,

        'document_date' => '2026-08-20',
        'handover_date' => '2026-08-20',
        'handover_place' => 'Cianjur',

        'status' => Bast::STATUS_DRAFT,
    ]);

    $bast->parties()->createMany([
        [
            'party_type' => BastParty::TYPE_FIRST_PARTY,
            'name' => 'Pihak Pertama',
            'institution' => 'Instansi A',
            'sort_order' => 1,
        ],
        [
            'party_type' => BastParty::TYPE_SECOND_PARTY,
            'name' => 'Pihak Kedua',
            'institution' => 'Instansi B',
            'sort_order' => 2,
        ],
    ]);

    $bast->items()->create([
        'name' => 'Laptop',
        'quantity' => 1,
        'condition' => 'baik',
        'sort_order' => 1,
    ]);

    return $bast;
}

beforeEach(function () {
    $this->workflowType = BastType::query()
        ->create([
            'name' => 'Workflow Type',
            'slug' => 'workflow-type',
            'description' => null,
            'is_active' => true,
        ]);

    $this->workflowDepartment =
        createWorkflowDepartment();

    $this->workflowRole =
        createWorkflowRole('staff');

    $this->workflowUser =
        User::factory()->create([
            'role_id' => $this
                ->workflowRole
                ->id,

            'department_id' => $this
                ->workflowDepartment
                ->id,
        ]);
});

test('owner can finalize draft bast', function () {
    $bast = createWorkflowBast(
        $this->workflowUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $this->actingAs(
        $this->workflowUser,
    )
        ->post(
            route(
                'bast.finalize',
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
        ->toBe(Bast::STATUS_FINALIZED)
        ->and($bast->sequence_number)
        ->toBe(1)
        ->and($bast->document_number)
        ->toBe(
            '001/BAST/DISKOMINFO/VIII/2026',
        )
        ->and($bast->finalized_by)
        ->toBe($this->workflowUser->id);

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
            'action' => 'BAST_FINALIZED',
        ],
    );
});

test('finalized bast receives sequential document numbers', function () {
    $firstBast = createWorkflowBast(
        $this->workflowUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $secondBast = createWorkflowBast(
        $this->workflowUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $this->actingAs(
        $this->workflowUser,
    )->post(
        route(
            'bast.finalize',
            $firstBast,
        ),
    );

    $this->actingAs(
        $this->workflowUser,
    )->post(
        route(
            'bast.finalize',
            $secondBast,
        ),
    );

    $firstBast->refresh();
    $secondBast->refresh();

    expect($firstBast->document_number)
        ->toBe(
            '001/BAST/DISKOMINFO/VIII/2026',
        )
        ->and($secondBast->document_number)
        ->toBe(
            '002/BAST/DISKOMINFO/VIII/2026',
        );

    expect(
        DocumentSequence::query()
            ->where(
                'document_code',
                'BAST',
            )
            ->where(
                'year',
                2026,
            )
            ->value('last_number'),
    )->toBe(2);
});

test('finalized bast cannot be finalized twice', function () {
    $bast = createWorkflowBast(
        $this->workflowUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $this->actingAs(
        $this->workflowUser,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->workflowUser,
    )
        ->post(
            route(
                'bast.finalize',
                $bast,
            ),
        )
        ->assertForbidden();

    expect(
        DocumentSequence::query()
            ->value('last_number'),
    )->toBe(1);
});

test('owner can soft delete draft bast', function () {
    $bast = createWorkflowBast(
        $this->workflowUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $this->actingAs(
        $this->workflowUser,
    )
        ->delete(
            route(
                'bast.destroy',
                $bast,
            ),
        )
        ->assertRedirect(
            route('bast.index'),
        );

    $this->assertSoftDeleted(
        'basts',
        [
            'id' => $bast->id,
        ],
    );

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,
            'subject_id' => $bast->id,
            'action' => 'BAST_DELETED',
        ],
    );
});

test('finalized bast cannot be deleted', function () {
    $bast = createWorkflowBast(
        $this->workflowUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $this->actingAs(
        $this->workflowUser,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->workflowUser,
    )
        ->delete(
            route(
                'bast.destroy',
                $bast,
            ),
        )
        ->assertForbidden();

    $this->assertDatabaseHas(
        'basts',
        [
            'id' => $bast->id,
            'deleted_at' => null,
        ],
    );
});

test('staff cannot finalize another staffs bast', function () {
    $otherUser = User::factory()->create([
        'role_id' => $this
            ->workflowRole
            ->id,

        'department_id' => $this
            ->workflowDepartment
            ->id,
    ]);

    $bast = createWorkflowBast(
        $otherUser,
        $this->workflowDepartment,
        $this->workflowType,
    );

    $this->actingAs(
        $this->workflowUser,
    )
        ->post(
            route(
                'bast.finalize',
                $bast,
            ),
        )
        ->assertForbidden();

    expect(
        $bast->fresh()?->status,
    )->toBe(Bast::STATUS_DRAFT);
});
