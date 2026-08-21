<?php

use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function presentationRole(): Role
{
    return Role::query()->create([
        'name' => 'Admin Presentation',

        'slug' => 'admin',

        'description' => null,

        'is_active' => true,
    ]);
}

function presentationDepartment(): Department
{
    return Department::query()->create([
        'name' => 'Presentation Department',

        'code' => 'PRS',

        'description' => null,

        'is_active' => true,
    ]);
}

function presentationBastType(): BastType
{
    return BastType::query()->create([
        'name' => 'Presentation Type',

        'slug' => 'presentation-type',

        'description' => null,

        'is_active' => true,
    ]);
}

function createPresentationBast(
    User $user,
    Department $department,
    BastType $type,
    string $title,
    string $status,
    ?int $sequenceNumber = null,
): Bast {
    return Bast::query()->create([
        'document_number' => $sequenceNumber === null
            ? null
            : sprintf(
                '%03d/BAST/DISKOMINFO/VIII/2026',
                $sequenceNumber,
            ),

        'sequence_number' => $sequenceNumber,

        'document_code' => 'BAST',

        'document_month' => 8,

        'document_year' => 2026,

        'bast_type_id' => $type->id,

        'department_id' => $department->id,

        'created_by' => $user->id,

        'title' => $title,

        'description' => null,

        'document_date' => '2026-08-21',

        'handover_date' => '2026-08-21',

        'handover_place' => 'Cianjur',

        'status' => $status,

        'finalized_at' => in_array(
            $status,
            [
                Bast::STATUS_FINALIZED,
                Bast::STATUS_COMPLETED,
                Bast::STATUS_ARCHIVED,
                Bast::STATUS_CANCELLED,
            ],
            true,
        )
            ? now()
            : null,

        'finalized_by' => in_array(
            $status,
            [
                Bast::STATUS_FINALIZED,
                Bast::STATUS_COMPLETED,
                Bast::STATUS_ARCHIVED,
                Bast::STATUS_CANCELLED,
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
    $this->presentationRole =
        presentationRole();

    $this->presentationDepartment =
        presentationDepartment();

    $this->presentationType =
        presentationBastType();

    $this->presentationUser =
        User::factory()->create([
            'role_id' => $this
                ->presentationRole
                ->id,

            'department_id' => $this
                ->presentationDepartment
                ->id,
        ]);
});

test('archived bast is separated from main bast list', function () {
    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'BAST Aktif',
        Bast::STATUS_DRAFT,
    );

    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'BAST Arsip',
        Bast::STATUS_ARCHIVED,
        1,
    );

    $this->actingAs(
        $this->presentationUser,
    )
        ->get(
            route(
                'bast.index',
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'bast/index',
                )
                ->where(
                    'basts.total',
                    1,
                )
                ->where(
                    'basts.data.0.title',
                    'BAST Aktif',
                )
                ->where(
                    'basts.data.0.status',
                    'draft',
                ),
        );

    $this->actingAs(
        $this->presentationUser,
    )
        ->get(
            route(
                'archive.index',
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'archive/index',
                )
                ->where(
                    'basts.total',
                    1,
                )
                ->where(
                    'basts.data.0.title',
                    'BAST Arsip',
                ),
        );
});

test('normal draft and revision draft have different presentation statuses', function () {
    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'Draft Biasa',
        Bast::STATUS_DRAFT,
    );

    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'Draft Revisi',
        Bast::STATUS_DRAFT,
        7,
    );

    $this->actingAs(
        $this->presentationUser,
    )
        ->get(
            route(
                'bast.index',
                [
                    'status' => 'draft',
                ],
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where(
                    'basts.total',
                    1,
                )
                ->where(
                    'basts.data.0.title',
                    'Draft Biasa',
                )
                ->where(
                    'basts.data.0.status',
                    'draft',
                )
                ->where(
                    'filters.status',
                    'draft',
                ),
        );

    $this->actingAs(
        $this->presentationUser,
    )
        ->get(
            route(
                'bast.index',
                [
                    'status' => 'revision',
                ],
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->where(
                    'basts.total',
                    1,
                )
                ->where(
                    'basts.data.0.title',
                    'Draft Revisi',
                )
                ->where(
                    'basts.data.0.status',
                    'revision',
                )
                ->where(
                    'filters.status',
                    'revision',
                ),
        );
});

test('dashboard separates draft and revision draft status', function () {
    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'Draft Biasa',
        Bast::STATUS_DRAFT,
    );

    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'Draft Revisi Dashboard',
        Bast::STATUS_DRAFT,
        8,
    );

    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'BAST Final',
        Bast::STATUS_FINALIZED,
        9,
    );

    createPresentationBast(
        $this->presentationUser,
        $this->presentationDepartment,
        $this->presentationType,
        'BAST Arsip Dashboard',
        Bast::STATUS_ARCHIVED,
        10,
    );

    $this->actingAs(
        $this->presentationUser,
    )
        ->get(
            route(
                'dashboard',
            ),
        )
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component(
                    'dashboard',
                )
                ->where(
                    'stats.total',
                    4,
                )
                ->where(
                    'stats.draft',
                    1,
                )
                ->where(
                    'stats.revision',
                    1,
                )
                ->where(
                    'stats.archived',
                    1,
                )
                ->where(
                    'statusBreakdown.0.key',
                    'draft',
                )
                ->where(
                    'statusBreakdown.0.count',
                    1,
                )
                ->where(
                    'statusBreakdown.1.key',
                    'revision',
                )
                ->where(
                    'statusBreakdown.1.label',
                    'Draft Revisi',
                )
                ->where(
                    'statusBreakdown.1.count',
                    1,
                )
                ->has(
                    'recentBasts',
                    3,
                )
                ->where(
                    'recentBasts',
                    function (
                        $recentBasts,
                    ): bool {
                        $items = collect(
                            $recentBasts,
                        );

                        $hasRevision = $items
                            ->contains(
                                fn ($item): bool => data_get(
                                    $item,
                                    'title',
                                ) === 'Draft Revisi Dashboard'
                                    && data_get(
                                        $item,
                                        'status',
                                    ) === 'revision',
                            );

                        $hasArchived = $items
                            ->contains(
                                fn ($item): bool => data_get(
                                    $item,
                                    'title',
                                ) === 'BAST Arsip Dashboard',
                            );

                        return $hasRevision
                            && ! $hasArchived;
                    },
                ),
        );
});
