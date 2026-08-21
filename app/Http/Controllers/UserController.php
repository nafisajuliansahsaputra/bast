<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResetUserPasswordRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(
        Request $request,
    ): Response {
        $actor = $this->superAdmin(
            $request,
        );

        $search = trim(
            (string) $request->query(
                'search',
                '',
            ),
        );

        $role = trim(
            (string) $request->query(
                'role',
                '',
            ),
        );

        $status = trim(
            (string) $request->query(
                'status',
                '',
            ),
        );

        $department = trim(
            (string) $request->query(
                'department',
                '',
            ),
        );

        $query = User::query()
            ->with([
                'role:id,name,slug,is_active',
                'department:id,name,code,is_active',
            ])
            ->withCount([
                'createdBasts',
                'activityLogs',
            ]);

        if ($search !== '') {
            $query->where(
                function (
                    Builder $builder,
                ) use ($search): void {
                    $builder
                        ->where(
                            'name',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'nip',
                            'like',
                            "%{$search}%",
                        )
                        ->orWhere(
                            'position',
                            'like',
                            "%{$search}%",
                        );
                },
            );
        }

        if (
            $role !== ''
            && ctype_digit($role)
        ) {
            $query->where(
                'role_id',
                (int) $role,
            );
        }

        if (
            in_array(
                $status,
                [
                    'active',
                    'inactive',
                ],
                true,
            )
        ) {
            $query->where(
                'status',
                $status,
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

        return Inertia::render(
            'users/index',
            [
                'users' => $query
                    ->orderByRaw(
                        'CASE WHEN id = ? THEN 0 ELSE 1 END',
                        [
                            $actor->id,
                        ],
                    )
                    ->orderBy('name')
                    ->paginate(10)
                    ->withQueryString(),

                'filters' => [
                    'search' => $search,
                    'role' => $role,
                    'status' => $status,
                    'department' => $department,
                ],

                'roles' => Role::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'slug',
                        'is_active',
                    ]),

                'departments' => Department::query()
                    ->orderBy('name')
                    ->get([
                        'id',
                        'name',
                        'code',
                        'is_active',
                    ]),

                'temporaryCredential' => $request
                    ->session()
                    ->get(
                        'temporary_credential',
                    ),
            ],
        );
    }

    public function show(
        Request $request,
        User $user,
    ): Response {
        $this->superAdmin(
            $request,
        );

        $user->load([
            'role:id,name,slug,is_active',
            'department:id,name,code,is_active',
        ]);

        $user->loadCount([
            'createdBasts',
            'activityLogs',
        ]);

        return Inertia::render(
            'users/show',
            [
                'managedUser' => $user,

                'recentBasts' => $user
                    ->createdBasts()
                    ->with([
                        'bastType:id,name',
                        'department:id,name,code',
                    ])
                    ->latest('updated_at')
                    ->limit(8)
                    ->get(),

                'recentActivities' => $user
                    ->activityLogs()
                    ->latest('created_at')
                    ->limit(10)
                    ->get([
                        'id',
                        'action',
                        'description',
                        'subject_type',
                        'subject_id',
                        'created_at',
                    ]),

                'temporaryCredential' => $request
                    ->session()
                    ->get(
                        'temporary_credential',
                    ),
            ],
        );
    }

    public function store(
        StoreUserRequest $request,
    ): RedirectResponse {
        $actor = $this->superAdmin(
            $request,
        );

        $validated = $request->validated();

        $temporaryPassword = $this
            ->temporaryPassword();

        $managedUser = DB::transaction(
            function () use (
                $request,
                $actor,
                $validated,
                $temporaryPassword,
            ): User {
                $managedUser = User::query()
                    ->create([
                        'role_id' => (int) $validated['role_id'],

                        'department_id' => (int) $validated['department_id'],

                        'name' => (string) $validated['name'],

                        'nip' => $validated['nip'] ?? null,

                        'email' => (string) $validated['email'],

                        'position' => $validated['position'] ?? null,

                        'phone' => $validated['phone'] ?? null,

                        'status' => 'active',

                        'password' => $temporaryPassword,
                    ]);

                $managedUser->markEmailAsVerified();

                $this->log(
                    $request,
                    $actor,
                    $managedUser,
                    'USER_CREATED',
                    sprintf(
                        'Membuat akun pengguna "%s".',
                        $managedUser->name,
                    ),
                    null,
                    $this->snapshot(
                        $managedUser,
                    ),
                );

                return $managedUser;
            },
        );

        $this->flashTemporaryCredential(
            $request,
            $managedUser,
            $temporaryPassword,
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Pengguna berhasil dibuat.',
            );
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
    ): RedirectResponse {
        $actor = $this->superAdmin(
            $request,
        );

        $validated = $request->validated();

        $user->loadMissing(
            'role:id,name,slug,is_active',
        );

        if (
            $actor->is($user)
            && (int) $validated['role_id'] !== $user->role_id
        ) {
            return back()->withErrors([
                'role_id' => 'Anda tidak dapat mengubah role akun sendiri.',
            ]);
        }

        if (
            $this->isLastActiveSuperAdmin(
                $user,
            )
            && ! $this->roleIsSuperAdmin(
                (int) $validated['role_id'],
            )
        ) {
            return back()->withErrors([
                'role_id' => 'Minimal satu Super Admin aktif harus tetap tersedia.',
            ]);
        }

        DB::transaction(
            function () use (
                $request,
                $actor,
                $validated,
                $user,
            ): void {
                $oldValues = $this->snapshot(
                    $user,
                );

                $emailChanged =
                    $user->email !==
                    (string) $validated['email'];

                $shouldVerifyEmail =
                    $emailChanged
                    || $user->email_verified_at === null;

                $user->update([
                    'role_id' => (int) $validated['role_id'],

                    'department_id' => (int) $validated['department_id'],

                    'name' => (string) $validated['name'],

                    'nip' => $validated['nip'] ?? null,

                    'email' => (string) $validated['email'],

                    'position' => $validated['position'] ?? null,

                    'phone' => $validated['phone'] ?? null,
                ]);

                if ($shouldVerifyEmail) {
                    $user->markEmailAsVerified();
                }

                $user->refresh();

                $this->log(
                    $request,
                    $actor,
                    $user,
                    'USER_UPDATED',
                    sprintf(
                        'Memperbarui akun pengguna "%s".',
                        $user->name,
                    ),
                    $oldValues,
                    $this->snapshot(
                        $user,
                    ),
                );
            },
        );

        return back()->with(
            'success',
            'Pengguna berhasil diperbarui.',
        );
    }

    public function toggleStatus(
        Request $request,
        User $user,
    ): RedirectResponse {
        $actor = $this->superAdmin(
            $request,
        );

        if ($actor->is($user)) {
            return back()->withErrors([
                'user_status' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        $user->loadMissing([
            'role:id,name,slug,is_active',
            'department:id,name,code,is_active',
        ]);

        if (
            $this->isLastActiveSuperAdmin(
                $user,
            )
        ) {
            return back()->withErrors([
                'user_status' => 'Minimal satu Super Admin aktif harus tetap tersedia.',
            ]);
        }

        if (! $user->isActive()) {
            if (
                $user->role === null
                || ! $user->role->is_active
            ) {
                return back()->withErrors([
                    'user_status' => 'Role pengguna sedang nonaktif. Aktifkan role terlebih dahulu.',
                ]);
            }

            if (
                $user->department === null
                || ! $user->department->is_active
            ) {
                return back()->withErrors([
                    'user_status' => 'Unit atau bidang pengguna sedang nonaktif. Aktifkan atau pindahkan unit terlebih dahulu.',
                ]);
            }
        }

        DB::transaction(
            function () use (
                $request,
                $actor,
                $user,
            ): void {
                $oldValues = $this->snapshot(
                    $user,
                );

                $newStatus = $user->isActive()
                    ? 'inactive'
                    : 'active';

                $user->update([
                    'status' => $newStatus,

                    'remember_token' => Str::random(
                        60,
                    ),
                ]);

                $user->refresh();

                if (
                    $newStatus === 'inactive'
                ) {
                    $this->deleteDatabaseSessions(
                        $user,
                    );
                }

                $this->log(
                    $request,
                    $actor,
                    $user,
                    $newStatus === 'active'
                        ? 'USER_ACTIVATED'
                        : 'USER_DEACTIVATED',

                    sprintf(
                        '%s akun pengguna "%s".',
                        $newStatus === 'active'
                            ? 'Mengaktifkan'
                            : 'Menonaktifkan',
                        $user->name,
                    ),

                    $oldValues,

                    $this->snapshot(
                        $user,
                    ),
                );
            },
        );

        return back()->with(
            'success',
            $user->isActive()
                ? 'Pengguna berhasil diaktifkan.'
                : 'Pengguna berhasil dinonaktifkan.',
        );
    }

    public function resetPassword(
        ResetUserPasswordRequest $request,
        User $user,
    ): RedirectResponse {
        $actor = $this->superAdmin(
            $request,
        );

        if ($actor->is($user)) {
            return back()->withErrors([
                'password_reset' => 'Gunakan menu Security untuk mengubah kata sandi akun sendiri.',
            ]);
        }

        $temporaryPassword = $this
            ->temporaryPassword();

        DB::transaction(
            function () use (
                $request,
                $actor,
                $user,
                $temporaryPassword,
            ): void {
                $hadTwoFactor =
                    $user->two_factor_secret !== null;

                $user->update([
                    'password' => $temporaryPassword,

                    'two_factor_secret' => null,

                    'two_factor_recovery_codes' => null,

                    'two_factor_confirmed_at' => null,

                    'remember_token' => Str::random(
                        60,
                    ),
                ]);

                $this->deleteDatabaseSessions(
                    $user,
                );

                $this->log(
                    $request,
                    $actor,
                    $user,
                    'USER_PASSWORD_RESET',
                    sprintf(
                        'Mereset kata sandi pengguna "%s".',
                        $user->name,
                    ),
                    [
                        'two_factor_enabled' => $hadTwoFactor,
                    ],
                    [
                        'two_factor_enabled' => false,
                    ],
                );
            },
        );

        $this->flashTemporaryCredential(
            $request,
            $user,
            $temporaryPassword,
        );

        return back()->with(
            'success',
            'Kata sandi pengguna berhasil direset.',
        );
    }

    private function superAdmin(
        Request $request,
    ): User {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && $user->isActive()
                && $user->isSuperAdmin(),
            403,
        );

        return $user;
    }

    private function temporaryPassword(): string
    {
        return sprintf(
            'BAST-%s',
            Str::upper(
                Str::random(10),
            ),
        );
    }

    private function flashTemporaryCredential(
        Request $request,
        User $user,
        string $password,
    ): void {
        $request->session()->flash(
            'temporary_credential',
            [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ],
        );
    }

    private function isLastActiveSuperAdmin(
        User $user,
    ): bool {
        $user->loadMissing(
            'role:id,slug',
        );

        if (
            ! $user->isActive()
            || ! $user->isSuperAdmin()
        ) {
            return false;
        }

        return User::query()
            ->where(
                'status',
                'active',
            )
            ->whereHas(
                'role',
                fn (Builder $query) => $query->where(
                    'slug',
                    'super-admin',
                ),
            )
            ->count() <= 1;
    }

    private function roleIsSuperAdmin(
        int $roleId,
    ): bool {
        return Role::query()
            ->whereKey(
                $roleId,
            )
            ->where(
                'slug',
                'super-admin',
            )
            ->exists();
    }

    private function deleteDatabaseSessions(
        User $user,
    ): void {
        if (
            config('session.driver') !==
            'database'
        ) {
            return;
        }

        DB::table('sessions')
            ->where(
                'user_id',
                $user->id,
            )
            ->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        User $user,
    ): array {
        return [
            'name' => $user->name,
            'nip' => $user->nip,
            'email' => $user->email,
            'position' => $user->position,
            'phone' => $user->phone,
            'status' => $user->status,
            'role_id' => $user->role_id,
            'department_id' => $user->department_id,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function log(
        Request $request,
        User $actor,
        User $subject,
        string $action,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ActivityLog::query()->create([
            'user_id' => $actor->id,

            'action' => $action,

            'description' => $description,

            'subject_type' => User::class,

            'subject_id' => $subject->id,

            'ip_address' => $request->ip(),

            'user_agent' => $request
                ->userAgent(),

            'old_values' => $oldValues,

            'new_values' => $newValues,
        ]);
    }
}
