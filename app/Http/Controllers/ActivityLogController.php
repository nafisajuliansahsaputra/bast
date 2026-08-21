<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $viewer = $this->viewer($request);

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $userId = trim((string) $request->query('user', ''));
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));

        $query = ActivityLog::query()
            ->with([
                'user:id,name,email',
                'subject',
            ])
            ->latest('created_at');

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas(
                        'user',
                        function (Builder $userQuery) use ($search): void {
                            $userQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        },
                    );
            });
        }

        $this->applyCategoryFilter(
            $query,
            $category,
        );

        if ($userId !== '' && ctype_digit($userId)) {
            $query->where(
                'user_id',
                (int) $userId,
            );
        }

        if ($this->validDate($dateFrom)) {
            $query->whereDate(
                'created_at',
                '>=',
                $dateFrom,
            );
        }

        if ($this->validDate($dateTo)) {
            $query->whereDate(
                'created_at',
                '<=',
                $dateTo,
            );
        }

        $activityLogs = $query
            ->paginate(15)
            ->withQueryString();

        $activityLogs->through(
            fn (ActivityLog $activityLog): array => $this->serializeLog(
                $activityLog,
                $viewer,
                false,
            ),
        );

        return Inertia::render(
            'activity-logs/index',
            [
                'activityLogs' => $activityLogs,

                'filters' => [
                    'search' => $search,
                    'category' => $category,
                    'user' => $userId,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                ],

                'users' => User::query()
                    ->whereHas('activityLogs')
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'email',
                    ]),
            ],
        );
    }

    public function show(
        Request $request,
        ActivityLog $activityLog,
    ): Response {
        $viewer = $this->viewer($request);

        $activityLog->load([
            'user:id,name,email',
            'subject',
        ]);

        return Inertia::render(
            'activity-logs/show',
            [
                'activityLog' => $this->serializeLog(
                    $activityLog,
                    $viewer,
                    true,
                ),
            ],
        );
    }

    /**
     * @param  Builder<ActivityLog>  $query
     */
    private function applyCategoryFilter(
        Builder $query,
        string $category,
    ): void {
        match ($category) {
            'bast' => $query->where(
                'action',
                'like',
                'BAST_%',
            ),

            'users' => $query->where(
                function (Builder $builder): void {
                    $builder
                        ->where(
                            'action',
                            'like',
                            'USER_%',
                        )
                        ->orWhereIn(
                            'action',
                            [
                                'PROFILE_UPDATED',
                                'PASSWORD_UPDATED',
                            ],
                        );
                },
            ),

            'master' => $query->where(
                'action',
                'like',
                'MASTER_%',
            ),

            'attachments' => $query->where(
                'action',
                'like',
                'ATTACHMENT_%',
            ),

            'documents' => $query->where(
                function (Builder $builder): void {
                    $builder
                        ->where(
                            'action',
                            'like',
                            'PDF_%',
                        )
                        ->orWhere(
                            'action',
                            'like',
                            'DOCUMENT_%',
                        );
                },
            ),

            'auth' => $query->where(
                function (Builder $builder): void {
                    $builder
                        ->where(
                            'action',
                            'like',
                            'AUTH_%',
                        )
                        ->orWhereIn(
                            'action',
                            [
                                'USER_LOGIN',
                                'USER_LOGOUT',
                            ],
                        );
                },
            ),

            default => null,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeLog(
        ActivityLog $activityLog,
        User $viewer,
        bool $includeDetails,
    ): array {
        $category = $this->categoryForAction(
            $activityLog->action,
        );

        $data = [
            'id' => $activityLog->id,

            'action' => $activityLog->action,

            'action_label' => $this->actionLabel(
                $activityLog->action,
            ),

            'description' => $activityLog->description,

            'category' => $category,

            'user' => $activityLog->user === null
                ? null
                : [
                    'id' => $activityLog->user->id,
                    'name' => $activityLog->user->name,
                    'email' => $activityLog->user->email,
                ],

            'subject' => $this->subjectSummary(
                $activityLog,
                $viewer,
            ),

            'ip_address' => $activityLog->ip_address,

            'created_at' => $activityLog
                ->created_at
                ->toISOString(),
        ];

        if ($includeDetails) {
            $data['user_agent'] = $activityLog->user_agent;
            $data['old_values'] = $activityLog->old_values;
            $data['new_values'] = $activityLog->new_values;
        }

        return $data;
    }

    /**
     * @return array{
     *     key: string,
     *     label: string
     * }
     */
    private function categoryForAction(
        string $action,
    ): array {
        if (str_starts_with($action, 'BAST_')) {
            return [
                'key' => 'bast',
                'label' => 'BAST',
            ];
        }

        if (str_starts_with($action, 'MASTER_')) {
            return [
                'key' => 'master',
                'label' => 'Data Master',
            ];
        }

        if (str_starts_with($action, 'ATTACHMENT_')) {
            return [
                'key' => 'attachments',
                'label' => 'Lampiran',
            ];
        }

        if (
            str_starts_with($action, 'PDF_')
            || str_starts_with($action, 'DOCUMENT_')
        ) {
            return [
                'key' => 'documents',
                'label' => 'Dokumen / PDF',
            ];
        }

        if (
            str_starts_with($action, 'AUTH_')
            || in_array(
                $action,
                [
                    'USER_LOGIN',
                    'USER_LOGOUT',
                ],
                true,
            )
        ) {
            return [
                'key' => 'auth',
                'label' => 'Autentikasi',
            ];
        }

        if (
            str_starts_with($action, 'USER_')
            || in_array(
                $action,
                [
                    'PROFILE_UPDATED',
                    'PASSWORD_UPDATED',
                ],
                true,
            )
        ) {
            return [
                'key' => 'users',
                'label' => 'Pengguna',
            ];
        }

        return [
            'key' => 'other',
            'label' => 'Lainnya',
        ];
    }

    private function actionLabel(
        string $action,
    ): string {
        $labels = [
            'BAST_CREATED' => 'BAST dibuat',
            'BAST_UPDATED' => 'BAST diperbarui',
            'BAST_FINALIZED' => 'BAST difinalisasi',
            'BAST_DELETED' => 'Draft BAST dihapus',
            'BAST_COMPLETED' => 'BAST ditandai selesai',
            'BAST_ARCHIVED' => 'BAST diarsipkan',
            'BAST_RESTORED' => 'BAST dipulihkan',
            'BAST_REOPENED' => 'BAST dibuka kembali',
            'BAST_CANCELLED' => 'BAST dibatalkan',

            'ATTACHMENT_UPLOADED' => 'Lampiran diunggah',
            'ATTACHMENT_DELETED' => 'Lampiran dihapus',

            'PDF_GENERATED' => 'PDF dihasilkan',

            'USER_CREATED' => 'Pengguna dibuat',
            'USER_UPDATED' => 'Pengguna diperbarui',
            'USER_ACTIVATED' => 'Pengguna diaktifkan',
            'USER_DEACTIVATED' => 'Pengguna dinonaktifkan',
            'USER_PASSWORD_RESET' => 'Kata sandi pengguna direset',
            'USER_LOGIN' => 'Pengguna masuk',
            'USER_LOGOUT' => 'Pengguna keluar',

            'MASTER_BAST_TYPE_CREATED' => 'Jenis BAST ditambahkan',
            'MASTER_BAST_TYPE_UPDATED' => 'Jenis BAST diperbarui',
            'MASTER_BAST_TYPE_STATUS_CHANGED' => 'Status Jenis BAST diubah',

            'MASTER_DEPARTMENT_CREATED' => 'Unit / Bidang ditambahkan',
            'MASTER_DEPARTMENT_UPDATED' => 'Unit / Bidang diperbarui',
            'MASTER_DEPARTMENT_STATUS_CHANGED' => 'Status Unit / Bidang diubah',

            'MASTER_ITEM_CATEGORY_CREATED' => 'Kategori Item ditambahkan',
            'MASTER_ITEM_CATEGORY_UPDATED' => 'Kategori Item diperbarui',
            'MASTER_ITEM_CATEGORY_STATUS_CHANGED' => 'Status Kategori Item diubah',

            'MASTER_UNIT_CREATED' => 'Satuan ditambahkan',
            'MASTER_UNIT_UPDATED' => 'Satuan diperbarui',
            'MASTER_UNIT_STATUS_CHANGED' => 'Status Satuan diubah',

            'PROFILE_UPDATED' => 'Profil diperbarui',
            'PASSWORD_UPDATED' => 'Kata sandi diperbarui',
        ];

        return $labels[$action]
            ?? Str::headline(
                Str::lower($action),
            );
    }

    /**
     * @return array{
     *     type: string,
     *     label: string,
     *     href: string|null
     * }
     */
    private function subjectSummary(
        ActivityLog $activityLog,
        User $viewer,
    ): array {
        $subject = $activityLog->subject;

        if ($subject instanceof Bast) {
            return [
                'type' => 'BAST',

                'label' => $subject->document_number
                    ?? $subject->title,

                'href' => route(
                    'bast.show',
                    $subject,
                ),
            ];
        }

        if ($subject instanceof User) {
            return [
                'type' => 'Pengguna',
                'label' => $subject->name,

                'href' => $viewer->isSuperAdmin()
                    ? route(
                        'users.show',
                        $subject,
                    )
                    : null,
            ];
        }

        if ($subject instanceof BastType) {
            return [
                'type' => 'Jenis BAST',
                'label' => $subject->name,
                'href' => route('master.index'),
            ];
        }

        if ($subject instanceof Department) {
            return [
                'type' => 'Unit / Bidang',
                'label' => $subject->name,
                'href' => route('master.index'),
            ];
        }

        if ($subject instanceof ItemCategory) {
            return [
                'type' => 'Kategori Item',
                'label' => $subject->name,
                'href' => route('master.index'),
            ];
        }

        if ($subject instanceof Unit) {
            return [
                'type' => 'Satuan',
                'label' => $subject->name,
                'href' => route('master.index'),
            ];
        }

        $type = $activityLog->subject_type !== null
            ? class_basename(
                $activityLog->subject_type,
            )
            : 'Sistem';

        return [
            'type' => $type,

            'label' => $activityLog->subject_id !== null
                ? "{$type} #{$activityLog->subject_id}"
                : 'Tidak ada subject',

            'href' => null,
        ];
    }

    private function validDate(
        string $value,
    ): bool {
        if ($value === '') {
            return false;
        }

        $date = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value,
        );

        return $date !== false
            && $date->format('Y-m-d') === $value;
    }

    private function viewer(
        Request $request,
    ): User {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->isActive()
                && (
                    $user->isSuperAdmin()
                    || $user->isAdmin()
                ),
            403,
        );

        return $user;
    }
}
