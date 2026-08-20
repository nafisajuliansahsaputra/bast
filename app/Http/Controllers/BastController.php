<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBastRequest;
use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\User;
use App\Services\BastService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class BastController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Bast::class);

        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $search = trim(
            (string) $request->query(
                'search',
                '',
            ),
        );

        $status = trim(
            (string) $request->query(
                'status',
                '',
            ),
        );

        $type = trim(
            (string) $request->query(
                'type',
                '',
            ),
        );

        $year = trim(
            (string) $request->query(
                'year',
                '',
            ),
        );

        $years = $this->visibleBasts($user)
            ->whereNotNull('document_year')
            ->select('document_year')
            ->distinct()
            ->orderByDesc('document_year')
            ->pluck('document_year')
            ->values();

        $query = $this->visibleBasts($user)
            ->with([
                'bastType:id,name',
                'department:id,name,code',
                'creator:id,name',
            ])
            ->withCount([
                'items',
                'attachments',
            ]);

        if ($search !== '') {
            $query->where(
                function (
                    Builder $builder,
                ) use ($search): void {
                    $builder
                        ->where(
                            'title',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'document_number',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhereHas(
                            'parties',
                            function (
                                Builder $partyQuery,
                            ) use ($search): void {
                                $partyQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                );
                            },
                        )
                        ->orWhereHas(
                            'items',
                            function (
                                Builder $itemQuery,
                            ) use ($search): void {
                                $itemQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                );
                            },
                        );
                },
            );
        }

        $allowedStatuses = [
            Bast::STATUS_DRAFT,
            Bast::STATUS_FINALIZED,
            Bast::STATUS_COMPLETED,
            Bast::STATUS_ARCHIVED,
            Bast::STATUS_CANCELLED,
        ];

        if (
            in_array(
                $status,
                $allowedStatuses,
                true,
            )
        ) {
            $query->where(
                'status',
                $status,
            );
        }

        if (
            $type !== ''
            && ctype_digit($type)
        ) {
            $query->where(
                'bast_type_id',
                (int) $type,
            );
        }

        if (
            $year !== ''
            && ctype_digit($year)
        ) {
            $query->where(
                'document_year',
                (int) $year,
            );
        }

        $basts = $query
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'bast/index',
            [
                'basts' => $basts,

                'filters' => [
                    'search' => $search,
                    'status' => $status,
                    'type' => $type,
                    'year' => $year,
                ],

                'bastTypes' => BastType::query()
                    ->where(
                        'is_active',
                        true,
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'years' => $years,
            ],
        );
    }

    public function create(
        Request $request,
    ): Response {
        Gate::authorize(
            'create',
            Bast::class,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $departments = Department::query()
            ->where(
                'is_active',
                true,
            )
            ->orderBy('name');

        if ($user->isStaff()) {
            $departments->whereKey(
                $user->department_id,
            );
        }

        return Inertia::render(
            'bast/create',
            [
                'bastTypes' => BastType::query()
                    ->where(
                        'is_active',
                        true,
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'departments' => $departments
                    ->get([
                        'id',
                        'name',
                        'code',
                    ]),

                'itemCategories' => ItemCategory::query()
                    ->where(
                        'is_active',
                        true,
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                    ]),

                'units' => Unit::query()
                    ->where(
                        'is_active',
                        true,
                    )
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'symbol',
                    ]),

                'defaultDepartmentId' => $user
                    ->department_id,
            ],
        );
    }

    public function store(
        StoreBastRequest $request,
        BastService $bastService,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bast = $bastService->createDraft(
            $user,
            $request->validated(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'Draft BAST berhasil disimpan.',
            );
    }

    public function show(
        Request $request,
        Bast $bast,
    ): Response {
        Gate::authorize(
            'view',
            $bast,
        );

        $bast->load([
            'bastType:id,name',

            'department:id,name,code',

            'creator:id,name,email',

            'finalizedBy:id,name',

            'parties' => fn ($query) => $query
                ->orderBy('sort_order'),

            'items' => fn ($query) => $query
                ->with([
                    'itemCategory:id,name',
                    'unit:id,name,symbol',
                ])
                ->orderBy('sort_order'),

            'attachments',
        ]);

        return Inertia::render(
            'bast/show',
            [
                'bast' => $bast,

                'permissions' => [
                    'update' => Gate::allows(
                        'update',
                        $bast,
                    ),

                    'delete' => Gate::allows(
                        'delete',
                        $bast,
                    ),

                    'finalize' => Gate::allows(
                        'finalize',
                        $bast,
                    ),

                    'manageAttachments' => Gate::allows(
                        'manageAttachments',
                        $bast,
                    ),
                ],
            ],
        );
    }

    public function finalize(
        Request $request,
        Bast $bast,
        BastService $bastService,
    ): RedirectResponse {
        Gate::authorize(
            'finalize',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bast = $bastService->finalize(
            $user,
            $bast,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route(
                'bast.show',
                $bast,
            )
            ->with(
                'success',
                'BAST berhasil difinalisasi.',
            );
    }

    public function destroy(
        Request $request,
        Bast $bast,
        BastService $bastService,
    ): RedirectResponse {
        Gate::authorize(
            'delete',
            $bast,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $bastService->deleteDraft(
            $user,
            $bast,
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route('bast.index')
            ->with(
                'success',
                'Draft BAST berhasil dihapus.',
            );
    }

    /**
     * @return Builder<Bast>
     */
    private function visibleBasts(
        User $user,
    ): Builder {
        $query = Bast::query();

        if ($user->isStaff()) {
            $query->where(
                'created_by',
                $user->id,
            );
        }

        return $query;
    }
}
