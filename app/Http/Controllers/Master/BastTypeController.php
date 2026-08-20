<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreBastTypeRequest;
use App\Http\Requests\Master\UpdateBastTypeRequest;
use App\Models\ActivityLog;
use App\Models\BastType;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BastTypeController extends Controller
{
    public function store(
        StoreBastTypeRequest $request,
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
                $name = (string) $validated['name'];

                $bastType = BastType::query()->create([
                    'name' => $name,

                    'slug' => $this->uniqueSlug(
                        $name,
                    ),

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),

                    'is_active' => true,
                ]);

                $this->log(
                    $request,
                    $user,
                    $bastType,
                    'MASTER_BAST_TYPE_CREATED',
                    sprintf(
                        'Menambahkan jenis BAST "%s".',
                        $bastType->name,
                    ),
                    null,
                    $this->snapshot($bastType),
                );
            },
        );

        return back()->with(
            'success',
            'Jenis BAST berhasil ditambahkan.',
        );
    }

    public function update(
        UpdateBastTypeRequest $request,
        BastType $bastType,
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
                $bastType,
            ): void {
                $oldValues = $this->snapshot(
                    $bastType,
                );

                $name = (string) $validated['name'];

                $bastType->update([
                    'name' => $name,

                    'slug' => $this->uniqueSlug(
                        $name,
                        $bastType->id,
                    ),

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),
                ]);

                $bastType->refresh();

                $this->log(
                    $request,
                    $user,
                    $bastType,
                    'MASTER_BAST_TYPE_UPDATED',
                    sprintf(
                        'Memperbarui jenis BAST "%s".',
                        $bastType->name,
                    ),
                    $oldValues,
                    $this->snapshot($bastType),
                );
            },
        );

        return back()->with(
            'success',
            'Jenis BAST berhasil diperbarui.',
        );
    }

    public function toggle(
        Request $request,
        BastType $bastType,
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
                $bastType,
            ): void {
                $oldValues = $this->snapshot(
                    $bastType,
                );

                $bastType->update([
                    'is_active' => ! $bastType->is_active,
                ]);

                $bastType->refresh();

                $this->log(
                    $request,
                    $user,
                    $bastType,
                    'MASTER_BAST_TYPE_STATUS_CHANGED',
                    sprintf(
                        '%s jenis BAST "%s".',
                        $bastType->is_active
                            ? 'Mengaktifkan'
                            : 'Menonaktifkan',
                        $bastType->name,
                    ),
                    $oldValues,
                    $this->snapshot($bastType),
                );
            },
        );

        return back()->with(
            'success',
            $bastType->is_active
                ? 'Jenis BAST berhasil diaktifkan.'
                : 'Jenis BAST berhasil dinonaktifkan.',
        );
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null,
    ): string {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'jenis-bast';
        }

        $slug = $base;
        $suffix = 2;

        while (
            BastType::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $ignoreId,
                    ),
                )
                ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(
        BastType $bastType,
    ): array {
        return [
            'name' => $bastType->name,
            'slug' => $bastType->slug,
            'description' => $bastType->description,
            'is_active' => $bastType->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function log(
        Request $request,
        User $user,
        BastType $bastType,
        string $action,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,

            'subject_type' => BastType::class,
            'subject_id' => $bastType->id,

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
