<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreItemCategoryRequest;
use App\Http\Requests\Master\UpdateItemCategoryRequest;
use App\Models\ActivityLog;
use App\Models\ItemCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItemCategoryController extends Controller
{
    public function store(
        StoreItemCategoryRequest $request,
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

                $category = ItemCategory::query()->create([
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
                    $category,
                    'MASTER_ITEM_CATEGORY_CREATED',
                    sprintf(
                        'Menambahkan kategori item "%s".',
                        $category->name,
                    ),
                    null,
                    $this->snapshot($category),
                );
            },
        );

        return back()->with(
            'success',
            'Kategori item berhasil ditambahkan.',
        );
    }

    public function update(
        UpdateItemCategoryRequest $request,
        ItemCategory $itemCategory,
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
                $itemCategory,
            ): void {
                $oldValues = $this->snapshot(
                    $itemCategory,
                );

                $name = (string) $validated['name'];

                $itemCategory->update([
                    'name' => $name,

                    'slug' => $this->uniqueSlug(
                        $name,
                        $itemCategory->id,
                    ),

                    'description' => $this->nullableString(
                        $validated['description'] ?? null,
                    ),
                ]);

                $itemCategory->refresh();

                $this->log(
                    $request,
                    $user,
                    $itemCategory,
                    'MASTER_ITEM_CATEGORY_UPDATED',
                    sprintf(
                        'Memperbarui kategori item "%s".',
                        $itemCategory->name,
                    ),
                    $oldValues,
                    $this->snapshot($itemCategory),
                );
            },
        );

        return back()->with(
            'success',
            'Kategori item berhasil diperbarui.',
        );
    }

    public function toggle(
        Request $request,
        ItemCategory $itemCategory,
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
                $itemCategory,
            ): void {
                $oldValues = $this->snapshot(
                    $itemCategory,
                );

                $itemCategory->update([
                    'is_active' => ! $itemCategory->is_active,
                ]);

                $itemCategory->refresh();

                $this->log(
                    $request,
                    $user,
                    $itemCategory,
                    'MASTER_ITEM_CATEGORY_STATUS_CHANGED',
                    sprintf(
                        '%s kategori item "%s".',
                        $itemCategory->is_active
                            ? 'Mengaktifkan'
                            : 'Menonaktifkan',
                        $itemCategory->name,
                    ),
                    $oldValues,
                    $this->snapshot($itemCategory),
                );
            },
        );

        return back()->with(
            'success',
            $itemCategory->is_active
                ? 'Kategori item berhasil diaktifkan.'
                : 'Kategori item berhasil dinonaktifkan.',
        );
    }

    private function uniqueSlug(
        string $name,
        ?int $ignoreId = null,
    ): string {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'kategori-item';
        }

        $slug = $base;
        $suffix = 2;

        while (
            ItemCategory::query()
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
        ItemCategory $category,
    ): array {
        return [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'is_active' => $category->is_active,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $oldValues
     * @param  array<string, mixed>|null  $newValues
     */
    private function log(
        Request $request,
        User $user,
        ItemCategory $category,
        string $action,
        string $description,
        ?array $oldValues,
        ?array $newValues,
    ): void {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'description' => $description,

            'subject_type' => ItemCategory::class,
            'subject_id' => $category->id,

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
