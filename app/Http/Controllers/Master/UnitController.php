<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreUnitRequest;
use App\Http\Requests\Master\UpdateUnitRequest;
use App\Models\ActivityLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnitController extends Controller
{
    public function store(
        StoreUnitRequest $request,
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
                $unit = Unit::query()->create([
                    'name' => (string) $validated['name'],

                    'symbol' => $this->nullableString(
                        $validated['symbol'] ?? null,
                    ),

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),

                    'is_active' => true,
                ]);

                $this->log(
                    $request,
                    $user,
                    $unit,
                    'MASTER_UNIT_CREATED',
                    sprintf(
                        'Menambahkan satuan "%s".',
                        $unit->name,
                    ),
                    null,
                    $this->snapshot($unit),
                );
            },
        );

        return back()->with(
            'success',
            'Satuan berhasil ditambahkan.',
        );
    }

    public function update(
        UpdateUnitRequest $request,
        Unit $unit,
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
                $unit,
            ): void {
                $oldValues = $this->snapshot(
                    $unit,
                );

                $unit->update([
                    'name' => (string) $validated['name'],

                    'symbol' => $this->nullableString(
                        $validated['symbol'] ?? null,
                    ),

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),
                ]);

                $unit->refresh();

                $this->log(
                    $request,
                    $user,
                    $unit,
                    'MASTER_UNIT_UPDATED',
                    sprintf(
                        'Memperbarui satuan "%s".',
                        $unit->name,
                    ),
                    $oldValues,
                    $this->snapshot($unit),
                );
            },
        );

        return back()->with(
            'success',
            'Satuan berhasil diperbarui.',
        );
    }

    public function toggle(
        Request $request,
        Unit $unit,
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

        DB::transaction(
            function () use (
                $request,
                $user,
                $unit,
            ): void {
                $oldValues = $this->snapshot(
                    $unit,
                );

                $unit->update([
                    'is_active' => ! $unit->is_active,
                ]);

                $unit->refresh();

                $this->log(
                    $request,
                    $user,
                    $unit,
                    'MASTER_UNIT_STATUS_CHANGED',
                    sprintf(
                        '%s satuan "%s".',
                        $unit->is_active
                            ? 'Mengaktifkan'
                            : 'Menonaktifkan',
                        $unit->name,
                    ),
                    $oldValues,
                    $this->snapshot($unit),
                );
            },
        );

        return back()->with(
            'success',
            $unit->is_active
                ? 'Satuan berhasil diaktifkan.'
                : 'Satuan berhasil dinonaktifkan.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        Unit $unit,
    ): array {
        return [
            'name' => $unit->name,
            'symbol' => $unit->symbol,
            'description' => $unit->description,
            'is_active' => $unit->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function log(
        Request $request,
        User $user,
        Unit $unit,
        string $action,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,

            'subject_type' => Unit::class,
            'subject_id' => $unit->id,

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
