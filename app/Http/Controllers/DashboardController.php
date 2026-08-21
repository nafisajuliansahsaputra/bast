<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bast;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(
        Request $request,
    ): Response {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        /** @var Builder<Bast> $bastQuery */
        $bastQuery = Bast::query();

        if ($user->isStaff()) {
            $bastQuery->where(
                'created_by',
                $user->id,
            );
        }

        $stats = [
            'total' => (clone $bastQuery)
                ->count(),

            'draft' => (clone $bastQuery)
                ->where(
                    'status',
                    Bast::STATUS_DRAFT,
                )
                ->whereNull(
                    'sequence_number',
                )
                ->count(),

            'revision' => (clone $bastQuery)
                ->where(
                    'status',
                    Bast::STATUS_DRAFT,
                )
                ->whereNotNull(
                    'sequence_number',
                )
                ->count(),

            'completed' => (clone $bastQuery)
                ->where(
                    'status',
                    Bast::STATUS_COMPLETED,
                )
                ->count(),

            'archived' => (clone $bastQuery)
                ->where(
                    'status',
                    Bast::STATUS_ARCHIVED,
                )
                ->count(),
        ];

        $statusBreakdown = [
            [
                'key' => 'draft',

                'label' => 'Draft',

                'count' => $stats['draft'],
            ],

            [
                'key' => 'revision',

                'label' => 'Draft Revisi',

                'count' => $stats['revision'],
            ],

            [
                'key' => Bast::STATUS_FINALIZED,

                'label' => 'Finalized',

                'count' => (clone $bastQuery)
                    ->where(
                        'status',
                        Bast::STATUS_FINALIZED,
                    )
                    ->count(),
            ],

            [
                'key' => Bast::STATUS_COMPLETED,

                'label' => 'Selesai',

                'count' => $stats['completed'],
            ],

            [
                'key' => Bast::STATUS_ARCHIVED,

                'label' => 'Diarsipkan',

                'count' => $stats['archived'],
            ],

            [
                'key' => Bast::STATUS_CANCELLED,

                'label' => 'Dibatalkan',

                'count' => (clone $bastQuery)
                    ->where(
                        'status',
                        Bast::STATUS_CANCELLED,
                    )
                    ->count(),
            ],
        ];

        $currentMonth = CarbonImmutable::now()
            ->startOfMonth();

        $monthlyActivity = [];

        for (
            $offset = 5;
            $offset >= 0;
            $offset--
        ) {
            $start = $currentMonth
                ->subMonths(
                    $offset,
                );

            $end = $start
                ->addMonth();

            $monthlyActivity[] = [
                'month' => $start
                    ->format(
                        'Y-m',
                    ),

                'count' => (clone $bastQuery)
                    ->where(
                        'created_at',
                        '>=',
                        $start,
                    )
                    ->where(
                        'created_at',
                        '<',
                        $end,
                    )
                    ->count(),
            ];
        }

        /*
         * Archived tidak ditampilkan pada Berita Acara Terbaru
         * karena sudah memiliki halaman Arsip tersendiri.
         */
        $recentBasts = (clone $bastQuery)
            ->where(
                'status',
                '!=',
                Bast::STATUS_ARCHIVED,
            )
            ->with([
                'bastType:id,name',

                'department:id,name',
            ])
            ->latest(
                'updated_at',
            )
            ->limit(5)
            ->get()
            ->map(
                fn (Bast $bast): array => [
                    'uuid' => $bast->uuid,

                    'documentNumber' => $bast
                        ->document_number,

                    'title' => $bast->title,

                    'status' => $this
                        ->presentationStatus(
                            $bast,
                        ),

                    'type' => $bast
                        ->bastType
                        ?->name,

                    'department' => $bast
                        ->department
                        ?->name,

                    'documentDate' => $bast
                        ->document_date
                        ->toDateString(),

                    'updatedAt' => $bast
                        ->updated_at
                        ?->toISOString(),
                ],
            )
            ->values()
            ->all();

        $activityQuery = ActivityLog::query()
            ->with(
                'user:id,name',
            )
            ->latest(
                'created_at',
            );

        if ($user->isStaff()) {
            $activityQuery->where(
                'user_id',
                $user->id,
            );
        }

        $recentActivities = $activityQuery
            ->limit(6)
            ->get()
            ->map(
                static fn (
                    ActivityLog $activity,
                ): array => [
                    'id' => $activity->id,

                    'action' => $activity->action,

                    'description' => $activity
                        ->description,

                    'userName' => $activity
                        ->user
                        ?->name,

                    'createdAt' => $activity
                        ->created_at
                        ?->toISOString(),
                ],
            )
            ->values()
            ->all();

        return Inertia::render(
            'dashboard',
            [
                'stats' => $stats,

                'statusBreakdown' => $statusBreakdown,

                'monthlyActivity' => $monthlyActivity,

                'recentBasts' => $recentBasts,

                'recentActivities' => $recentActivities,
            ],
        );
    }

    private function presentationStatus(
        Bast $bast,
    ): string {
        if (
            $bast->isDraft()
            && $bast->sequence_number !== null
        ) {
            return 'revision';
        }

        return $bast->status;
    }
}
