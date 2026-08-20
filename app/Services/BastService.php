<?php

namespace App\Services;

use App\Models\Bast;
use App\Models\BastParty;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class BastService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(
        User $user,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Bast {
        return DB::transaction(function () use (
            $user,
            $data,
            $ipAddress,
            $userAgent,
        ): Bast {
            $documentDate = CarbonImmutable::parse(
                (string) $data['document_date'],
            );

            $departmentId = $user->isStaff()
                ? $user->department_id
                : (int) $data['department_id'];

            if ($departmentId === null) {
                throw ValidationException::withMessages([
                    'department_id' => 'Akun Staff belum memiliki unit atau bidang.',
                ]);
            }

            $bast = Bast::query()->create([
                'document_number' => null,
                'sequence_number' => null,
                'document_code' => 'BAST',

                'document_month' => $documentDate->month,
                'document_year' => $documentDate->year,

                'bast_type_id' => (int) $data['bast_type_id'],
                'department_id' => $departmentId,
                'created_by' => $user->id,

                'title' => (string) $data['title'],
                'description' => $this->nullableString(
                    $data['description'] ?? null,
                ),

                'document_date' => $documentDate->toDateString(),

                'handover_date' => CarbonImmutable::parse(
                    (string) $data['handover_date'],
                )->toDateString(),

                'handover_place' => (string) $data['handover_place'],

                'status' => Bast::STATUS_DRAFT,
            ]);

            $this->createParties(
                $bast,
                $data['parties'] ?? null,
            );

            $this->createItems(
                $bast,
                $data['items'] ?? null,
            );

            $bast->activityLogs()->create([
                'user_id' => $user->id,
                'action' => 'BAST_CREATED',
                'description' => sprintf(
                    'Membuat draft BAST "%s".',
                    $bast->title,
                ),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'old_values' => null,
                'new_values' => [
                    'title' => $bast->title,
                    'status' => $bast->status,
                    'department_id' => $bast->department_id,
                    'bast_type_id' => $bast->bast_type_id,
                ],
            ]);

            return $bast;
        });
    }

    private function createParties(
        Bast $bast,
        mixed $parties,
    ): void {
        if (! is_array($parties)) {
            throw new InvalidArgumentException(
                'BAST parties data must be an array.',
            );
        }

        $definitions = [
            BastParty::TYPE_FIRST_PARTY => 1,
            BastParty::TYPE_SECOND_PARTY => 2,
        ];

        foreach ($definitions as $type => $sortOrder) {
            $party = $parties[$type] ?? null;

            if (! is_array($party)) {
                throw new InvalidArgumentException(
                    sprintf('Party data for %s is invalid.', $type),
                );
            }

            $userId = isset($party['user_id'])
                && $party['user_id'] !== ''
                    ? (int) $party['user_id']
                    : null;

            $bast->parties()->create([
                'user_id' => $userId,
                'party_type' => $type,

                'name' => (string) ($party['name'] ?? ''),
                'nip' => $this->nullableString(
                    $party['nip'] ?? null,
                ),
                'position' => $this->nullableString(
                    $party['position'] ?? null,
                ),
                'department' => $this->nullableString(
                    $party['department'] ?? null,
                ),

                'institution' => (string) (
                    $party['institution'] ?? ''
                ),

                'address' => $this->nullableString(
                    $party['address'] ?? null,
                ),

                'sort_order' => $sortOrder,
            ]);
        }
    }

    private function createItems(
        Bast $bast,
        mixed $items,
    ): void {
        if (! is_array($items)) {
            throw new InvalidArgumentException(
                'BAST items data must be an array.',
            );
        }

        foreach (array_values($items) as $index => $item) {
            if (! is_array($item)) {
                throw new InvalidArgumentException(
                    'BAST item data is invalid.',
                );
            }

            $categoryId = isset($item['item_category_id'])
                && $item['item_category_id'] !== ''
                    ? (int) $item['item_category_id']
                    : null;

            $unitId = isset($item['unit_id'])
                && $item['unit_id'] !== ''
                    ? (int) $item['unit_id']
                    : null;

            $value = isset($item['value'])
                && $item['value'] !== ''
                    ? (float) $item['value']
                    : null;

            $bast->items()->create([
                'item_category_id' => $categoryId,
                'unit_id' => $unitId,

                'name' => (string) ($item['name'] ?? ''),

                'code' => $this->nullableString(
                    $item['code'] ?? null,
                ),

                'inventory_number' => $this->nullableString(
                    $item['inventory_number'] ?? null,
                ),

                'serial_number' => $this->nullableString(
                    $item['serial_number'] ?? null,
                ),

                'quantity' => (float) (
                    $item['quantity'] ?? 1
                ),

                'condition' => $this->nullableString(
                    $item['condition'] ?? null,
                ),

                'value' => $value,

                'description' => $this->nullableString(
                    $item['description'] ?? null,
                ),

                'sort_order' => $index + 1,
            ]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
