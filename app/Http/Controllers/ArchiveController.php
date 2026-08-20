<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        Gate::authorize(
            'viewAny',
            Bast::class,
        );

        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $search = trim(
            (string) $request->query(
                'search',
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

        $department = trim(
            (string) $request->query(
                'department',
                '',
            ),
        );

        $baseQuery = Bast::query()
            ->where(
                'status',
                Bast::STATUS_ARCHIVED,
            );

        if ($user->isStaff()) {
            $baseQuery->where(
                'created_by',
                $user->id,
            );
        }

        $years = (clone $baseQuery)
            ->whereNotNull('document_year')
            ->select('document_year')
            ->distinct()
            ->orderByDesc('document_year')
            ->pluck('document_year')
            ->values();

        $query = clone $baseQuery;

        $query
            ->with([
                'bastType:id,name',

                'department:id,name,code',

                'creator:id,name',

                'archivedBy:id,name',
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

        if (
            $department !== ''
            && ctype_digit($department)
        ) {
            $query->where(
                'department_id',
                (int) $department,
            );
        }

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
            'archive/index',
            [
                'basts' => $query
                    ->orderByDesc('archived_at')
                    ->paginate(10)
                    ->withQueryString(),

                'filters' => [
                    'search' => $search,
                    'type' => $type,
                    'year' => $year,
                    'department' => $department,
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

                'departments' => $departments
                    ->get([
                        'id',
                        'name',
                        'code',
                    ]),

                'years' => $years,

                'canRestore' => $user->isSuperAdmin()
                    || $user->isAdmin(),
            ],
        );
    }
}
