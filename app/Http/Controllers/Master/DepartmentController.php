<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreDepartmentRequest;
use App\Http\Requests\Master\UpdateDepartmentRequest;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function store(
        StoreDepartmentRequest $request,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $user,
                $validated,
            ): void {
                $department = Department::query()->create([
                    'name' => (string) $validated['name'],
                    'code' => (string) $validated['code'],

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),

                    'is_active' => true,
                ]);

                $this->log(
                    $request,
                    $user,
                    $department,
                    'MASTER_DEPARTMENT_CREATED',
                    sprintf(
                        'Menambahkan unit atau bidang "%s".',
                        $department->name,
                    ),
                    null,
                    $this->snapshot($department),
                );
            },
        );

        return back()->with(
            'success',
            'Unit atau bidang berhasil ditambahkan.',
        );
    }

    public function update(
        UpdateDepartmentRequest $request,
        Department $department,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User,
            403,
        );

        $validated = $request->validated();

        DB::transaction(
            function () use (
                $request,
                $user,
                $validated,
                $department,
            ): void {
                $oldValues = $this->snapshot(
                    $department,
                );

                $department->update([
                    'name' => (string) $validated['name'],
                    'code' => (string) $validated['code'],

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),
                ]);

                $department->refresh();

                $this->log(
                    $request,
                    $user,
                    $department,
                    'MASTER_DEPARTMENT_UPDATED',
                    sprintf(
                        'Memperbarui unit atau bidang "%s".',
                        $department->name,
                    ),
                    $oldValues,
                    $this->snapshot($department),
                );
            },
        );

        return back()->with(
            'success',
            'Unit atau bidang berhasil diperbarui.',
        );
    }

    public function toggle(
        Request $request,
        Department $department,
    ): RedirectResponse {
        $user = $request->user();

        abort_unless(
            $user instanceof User
                && (
                    $user->isSuperAdmin()
                    || $user->isAdmin()
                ),
            403,
        );

        if (
            $department->is_active
            && $department->users()
                ->where(
                    'status',
                    'active',
                )
                ->exists()
        ) {
            return back()->withErrors([
                'department' => 'Unit atau bidang masih digunakan oleh pengguna aktif. Pindahkan atau nonaktifkan pengguna terlebih dahulu.',
            ]);
        }

        DB::transaction(
            function () use (
                $request,
                $user,
                $department,
            ): void {
                $oldValues = $this->snapshot(
                    $department,
                );

                $department->update([
                    'is_active' => ! $department->is_active,
                ]);

                $department->refresh();

                $this->log(
                    $request,
                    $user,
                    $department,
                    'MASTER_DEPARTMENT_STATUS_CHANGED',
                    sprintf(
                        '%s unit atau bidang "%s".',
                        $department->is_active
                            ? 'Mengaktifkan'
                            : 'Menonaktifkan',
                        $department->name,
                    ),
                    $oldValues,
                    $this->snapshot($department),
                );
            },
        );

        return back()->with(
            'success',
            $department->is_active
                ? 'Unit atau bidang berhasil diaktifkan.'
                : 'Unit atau bidang berhasil dinonaktifkan.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        Department $department,
    ): array {
        return [
            'name' => $department->name,
            'code' => $department->code,
            'description' => $department->description,
            'is_active' => $department->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function log(
        Request $request,
        User $user,
        Department $department,
        string $action,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,

            'subject_type' => Department::class,
            'subject_id' => $department->id,

            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),

            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    private function nullableString(
        mixed $value,
    ): ?string {
        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            return null;
        }

        return trim((string) $value);
    }
}
