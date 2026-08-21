<?php

use App\Models\Bast;
use App\Models\BastParty;
use App\Models\BastType;
use App\Models\Department;
use App\Models\DocumentSequence;
use App\Models\Role;
use App\Models\User;

function advancedLifecycleRole(
    string $slug,
): Role {
    return Role::query()->create([
        'name' => str(
            $slug,
        )
            ->headline()
            ->append(' Advanced')
            ->toString(),

        'slug' => $slug,

        'description' => null,

        'is_active' => true,
    ]);
}

function advancedLifecycleDepartment(): Department
{
    return Department::query()->create([
        'name' => 'Advanced Lifecycle Department',

        'code' => 'ALC',

        'description' => null,

        'is_active' => true,
    ]);
}

function advancedLifecycleType(): BastType
{
    return BastType::query()->create([
        'name' => 'Advanced Lifecycle Type',

        'slug' => 'advanced-lifecycle-type',

        'description' => null,

        'is_active' => true,
    ]);
}

function advancedLifecycleDraft(
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

        'title' => 'Advanced Lifecycle BAST',

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
    $this->advancedStaffRole =
        advancedLifecycleRole(
            'staff',
        );

    $this->advancedAdminRole =
        advancedLifecycleRole(
            'admin',
        );

    $this->advancedDepartment =
        advancedLifecycleDepartment();

    $this->advancedType =
        advancedLifecycleType();

    $this->advancedStaff =
        User::factory()->create([
            'role_id' => $this
                ->advancedStaffRole
                ->id,

            'department_id' => $this
                ->advancedDepartment
                ->id,
        ]);

    $this->advancedAdmin =
        User::factory()->create([
            'role_id' => $this
                ->advancedAdminRole
                ->id,

            'department_id' => $this
                ->advancedDepartment
                ->id,
        ]);
});

test('admin can reopen finalized bast and issued number is preserved', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
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

    $documentNumber =
        $bast->document_number;

    $sequenceNumber =
        $bast->sequence_number;

    $this->actingAs(
        $this->advancedAdmin,
    )
        ->post(
            route(
                'bast.reopen',
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

    expect(
        $bast->status,
    )->toBe(
        Bast::STATUS_DRAFT,
    )
        ->and(
            $bast->document_number,
        )->toBe(
            $documentNumber,
        )
        ->and(
            $bast->sequence_number,
        )->toBe(
            $sequenceNumber,
        )
        ->and(
            $bast->finalized_at,
        )->toBeNull()
        ->and(
            $bast->finalized_by,
        )->toBeNull();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,

            'subject_id' => $bast->id,

            'action' => 'BAST_REOPENED',
        ],
    );
});

test('staff cannot reopen finalized bast', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->advancedStaff,
    )
        ->post(
            route(
                'bast.reopen',
                $bast,
            ),
        )
        ->assertForbidden();

    expect(
        $bast->fresh()?->status,
    )->toBe(
        Bast::STATUS_FINALIZED,
    );
});

test('admin can cancel finalized bast with reason', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $documentNumber =
        $bast->document_number;

    $reason =
        'Dokumen dibatalkan karena terdapat kesalahan administratif.';

    $this->actingAs(
        $this->advancedAdmin,
    )
        ->post(
            route(
                'bast.cancel',
                $bast,
            ),
            [
                'cancellation_reason' => $reason,
            ],
        )
        ->assertRedirect(
            route(
                'bast.show',
                $bast,
            ),
        );

    $bast->refresh();

    expect(
        $bast->status,
    )->toBe(
        Bast::STATUS_CANCELLED,
    )
        ->and(
            $bast->document_number,
        )->toBe(
            $documentNumber,
        )
        ->and(
            $bast->cancellation_reason,
        )->toBe(
            $reason,
        )
        ->and(
            $bast->cancelled_by,
        )->toBe(
            $this
                ->advancedAdmin
                ->id,
        )
        ->and(
            $bast->cancelled_at,
        )->not->toBeNull()
        ->and(
            $bast->finalized_at,
        )->not->toBeNull();

    $this->assertDatabaseHas(
        'activity_logs',
        [
            'subject_type' => Bast::class,

            'subject_id' => $bast->id,

            'action' => 'BAST_CANCELLED',
        ],
    );
});

test('cancelling bast requires a reason', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->advancedAdmin,
    )
        ->post(
            route(
                'bast.cancel',
                $bast,
            ),
            [
                'cancellation_reason' => '',
            ],
        )
        ->assertSessionHasErrors(
            'cancellation_reason',
        );

    expect(
        $bast->fresh()?->status,
    )->toBe(
        Bast::STATUS_FINALIZED,
    );
});

test('staff cannot cancel finalized bast', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->advancedStaff,
    )
        ->post(
            route(
                'bast.cancel',
                $bast,
            ),
            [
                'cancellation_reason' => 'Staff tidak boleh membatalkan dokumen ini.',
            ],
        )
        ->assertForbidden();

    expect(
        $bast->fresh()?->status,
    )->toBe(
        Bast::STATUS_FINALIZED,
    );
});

test('reopened bast that already has official number cannot be deleted', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->advancedAdmin,
    )->post(
        route(
            'bast.reopen',
            $bast,
        ),
    );

    $bast->refresh();

    $this->actingAs(
        $this->advancedStaff,
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

test('reopened bast reuses sequence number when finalized again', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    expect(
        $bast->sequence_number,
    )->toBe(1)
        ->and(
            $bast->document_number,
        )->toBe(
            '001/BAST/DISKOMINFO/VIII/2026',
        );

    $this->actingAs(
        $this->advancedAdmin,
    )->post(
        route(
            'bast.reopen',
            $bast,
        ),
    );

    $bast->refresh();

    $bast->update([
        'document_date' => '2026-09-10',

        'document_month' => 9,

        'document_year' => 2026,
    ]);

    $this->actingAs(
        $this->advancedStaff,
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

    expect(
        $bast->status,
    )->toBe(
        Bast::STATUS_FINALIZED,
    )
        ->and(
            $bast->sequence_number,
        )->toBe(1)
        ->and(
            $bast->document_number,
        )->toBe(
            '001/BAST/DISKOMINFO/IX/2026',
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
            ->value(
                'last_number',
            ),
    )->toBe(1);
});

test('cancelled bast keeps official document identity', function () {
    $bast = advancedLifecycleDraft(
        $this->advancedStaff,
        $this->advancedDepartment,
        $this->advancedType,
    );

    $this->actingAs(
        $this->advancedStaff,
    )->post(
        route(
            'bast.finalize',
            $bast,
        ),
    );

    $bast->refresh();

    $documentNumber =
        $bast->document_number;

    $sequenceNumber =
        $bast->sequence_number;

    $this->actingAs(
        $this->advancedAdmin,
    )->post(
        route(
            'bast.cancel',
            $bast,
        ),
        [
            'cancellation_reason' => 'Dokumen dibatalkan untuk kebutuhan pengujian.',
        ],
    );

    $bast->refresh();

    expect(
        $bast->document_number,
    )->toBe(
        $documentNumber,
    )
        ->and(
            $bast->sequence_number,
        )->toBe(
            $sequenceNumber,
        );
});
