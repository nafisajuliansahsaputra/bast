<?php

namespace App\Services;

use App\Models\Bast;
use App\Models\BastParty;
use App\Models\DocumentSequence;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

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
        return DB::transaction(
            function () use (
                $user,
                $data,
                $ipAddress,
                $userAgent,
            ): Bast {
                $documentDate = CarbonImmutable::parse(
                    (string) $data['document_date'],
                );

                $departmentId = $this->resolveDepartmentId(
                    $user,
                    $data,
                );

                $bast = Bast::query()->create([
                    'document_number' => null,

                    'sequence_number' => null,

                    'document_code' => $this->documentCode(),

                    'document_month' => $documentDate->month,

                    'document_year' => $documentDate->year,

                    'bast_type_id' => (int) $data['bast_type_id'],

                    'department_id' => $departmentId,

                    'created_by' => $user->id,

                    'title' => (string) $data['title'],

                    'description' => $this->nullableString(
                        $data['description'] ?? null,
                    ),

                    'document_date' => $documentDate
                        ->toDateString(),

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

                        'department_id' => $bast
                            ->department_id,

                        'bast_type_id' => $bast
                            ->bast_type_id,
                    ],
                ]);

                return $bast;
            },
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateDraft(
        User $user,
        Bast $bast,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Bast {
        return DB::transaction(
            function () use (
                $user,
                $bast,
                $data,
                $ipAddress,
                $userAgent,
            ): Bast {
                $lockedBast = Bast::query()
                    ->whereKey(
                        $bast->id,
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedBast->isDraft()) {
                    throw ValidationException::withMessages([
                        'bast' => 'Hanya BAST berstatus Draft yang dapat diedit.',
                    ]);
                }

                $oldValues = [
                    'title' => $lockedBast->title,

                    'description' => $lockedBast
                        ->description,

                    'bast_type_id' => $lockedBast
                        ->bast_type_id,

                    'department_id' => $lockedBast
                        ->department_id,

                    'document_date' => $lockedBast
                        ->document_date
                        ->toDateString(),

                    'handover_date' => $lockedBast
                        ->handover_date
                        ->toDateString(),

                    'handover_place' => $lockedBast
                        ->handover_place,

                    'items_count' => $lockedBast
                        ->items()
                        ->count(),
                ];

                $documentDate = CarbonImmutable::parse(
                    (string) $data['document_date'],
                );

                $departmentId = $this->resolveDepartmentId(
                    $user,
                    $data,
                );

                $lockedBast->update([
                    'bast_type_id' => (int) $data['bast_type_id'],

                    'department_id' => $departmentId,

                    'document_month' => $documentDate->month,

                    'document_year' => $documentDate->year,

                    'title' => (string) $data['title'],

                    'description' => $this->nullableString(
                        $data['description'] ?? null,
                    ),

                    'document_date' => $documentDate
                        ->toDateString(),

                    'handover_date' => CarbonImmutable::parse(
                        (string) $data['handover_date'],
                    )->toDateString(),

                    'handover_place' => (string) $data['handover_place'],
                ]);

                $lockedBast
                    ->parties()
                    ->delete();

                $lockedBast
                    ->items()
                    ->delete();

                $this->createParties(
                    $lockedBast,
                    $data['parties'] ?? null,
                );

                $this->createItems(
                    $lockedBast,
                    $data['items'] ?? null,
                );

                $lockedBast->activityLogs()->create([
                    'user_id' => $user->id,

                    'action' => 'BAST_UPDATED',

                    'description' => sprintf(
                        'Memperbarui draft BAST "%s".',
                        $lockedBast->title,
                    ),

                    'ip_address' => $ipAddress,

                    'user_agent' => $userAgent,

                    'old_values' => $oldValues,

                    'new_values' => [
                        'title' => $lockedBast->title,

                        'description' => $lockedBast
                            ->description,

                        'bast_type_id' => $lockedBast
                            ->bast_type_id,

                        'department_id' => $lockedBast
                            ->department_id,

                        'document_date' => $lockedBast
                            ->document_date
                            ->toDateString(),

                        'handover_date' => $lockedBast
                            ->handover_date
                            ->toDateString(),

                        'handover_place' => $lockedBast
                            ->handover_place,

                        'items_count' => $lockedBast
                            ->items()
                            ->count(),
                    ],
                ]);

                return $lockedBast->fresh()
                    ?? $lockedBast;
            },
        );
    }

    public function finalize(
        User $user,
        Bast $bast,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Bast {
        return DB::transaction(
            function () use (
                $user,
                $bast,
                $ipAddress,
                $userAgent,
            ): Bast {
                $lockedBast = Bast::query()
                    ->whereKey(
                        $bast->id,
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedBast->isDraft()) {
                    throw ValidationException::withMessages([
                        'bast' => 'BAST ini sudah tidak berstatus Draft.',
                    ]);
                }

                if (
                    ! $lockedBast
                        ->parties()
                        ->where(
                            'party_type',
                            BastParty::TYPE_FIRST_PARTY,
                        )
                        ->exists()
                    || ! $lockedBast
                        ->parties()
                        ->where(
                            'party_type',
                            BastParty::TYPE_SECOND_PARTY,
                        )
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'bast' => 'Pihak Pertama dan Pihak Kedua wajib tersedia sebelum finalisasi.',
                    ]);
                }

                if (
                    ! $lockedBast
                        ->items()
                        ->exists()
                ) {
                    throw ValidationException::withMessages([
                        'bast' => 'Minimal satu item wajib tersedia sebelum finalisasi.',
                    ]);
                }

                $year = $lockedBast
                    ->document_year;

                $month = $lockedBast
                    ->document_month;

                if (
                    $year === null
                    || $month === null
                ) {
                    throw new RuntimeException(
                        'Tanggal dokumen BAST tidak valid.',
                    );
                }

                $existingSequenceNumber =
                    $lockedBast->sequence_number;

                $existingDocumentNumber =
                    $lockedBast->document_number;

                $documentCode =
                    $existingSequenceNumber !== null
                    && trim(
                        $lockedBast->document_code,
                    ) !== ''
                        ? strtoupper(
                            trim(
                                $lockedBast->document_code,
                            ),
                        )
                        : $this->documentCode();

                $sequenceNumber =
                    $existingSequenceNumber
                    ?? $this->nextSequenceNumber(
                        $documentCode,
                        $year,
                    );

                $documentNumber =
                    $this->formatDocumentNumber(
                        $sequenceNumber,
                        $documentCode,
                        $month,
                        $year,
                    );

                $duplicateExists = Bast::withTrashed()
                    ->where(
                        'document_number',
                        $documentNumber,
                    )
                    ->where(
                        'id',
                        '!=',
                        $lockedBast->id,
                    )
                    ->exists();

                if ($duplicateExists) {
                    throw ValidationException::withMessages([
                        'document_date' => sprintf(
                            'Nomor dokumen %s sudah digunakan. Sesuaikan tanggal dokumen sebelum finalisasi ulang.',
                            $documentNumber,
                        ),
                    ]);
                }

                if (
                    $existingSequenceNumber !== null
                ) {
                    $this->reserveSequenceNumber(
                        $documentCode,
                        $year,
                        $sequenceNumber,
                    );
                }

                $lockedBast->update([
                    'document_number' => $documentNumber,

                    'sequence_number' => $sequenceNumber,

                    'document_code' => $documentCode,

                    'status' => Bast::STATUS_FINALIZED,

                    'finalized_at' => now(),

                    'finalized_by' => $user->id,
                ]);

                $lockedBast->activityLogs()->create([
                    'user_id' => $user->id,

                    'action' => 'BAST_FINALIZED',

                    'description' => sprintf(
                        $existingSequenceNumber === null
                            ? 'Memfinalisasi BAST "%s" dengan nomor %s.'
                            : 'Memfinalisasi ulang BAST "%s" dengan nomor %s.',
                        $lockedBast->title,
                        $documentNumber,
                    ),

                    'ip_address' => $ipAddress,

                    'user_agent' => $userAgent,

                    'old_values' => [
                        'status' => Bast::STATUS_DRAFT,

                        'document_number' => $existingDocumentNumber,

                        'sequence_number' => $existingSequenceNumber,
                    ],

                    'new_values' => [
                        'status' => Bast::STATUS_FINALIZED,

                        'document_number' => $documentNumber,

                        'sequence_number' => $sequenceNumber,
                    ],
                ]);

                return $lockedBast->fresh()
                    ?? $lockedBast;
            },
        );
    }

    public function deleteDraft(
        User $user,
        Bast $bast,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        DB::transaction(
            function () use (
                $user,
                $bast,
                $ipAddress,
                $userAgent,
            ): void {
                $lockedBast = Bast::query()
                    ->whereKey(
                        $bast->id,
                    )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $lockedBast->isDraft()) {
                    throw ValidationException::withMessages([
                        'bast' => 'Hanya BAST berstatus Draft yang dapat dihapus.',
                    ]);
                }

                if (
                    $lockedBast->sequence_number !== null
                ) {
                    throw ValidationException::withMessages([
                        'bast' => 'BAST yang pernah memiliki nomor resmi tidak dapat dihapus.',
                    ]);
                }

                $lockedBast->activityLogs()->create([
                    'user_id' => $user->id,

                    'action' => 'BAST_DELETED',

                    'description' => sprintf(
                        'Menghapus draft BAST "%s".',
                        $lockedBast->title,
                    ),

                    'ip_address' => $ipAddress,

                    'user_agent' => $userAgent,

                    'old_values' => [
                        'title' => $lockedBast->title,

                        'status' => $lockedBast->status,
                    ],

                    'new_values' => null,
                ]);

                $lockedBast->delete();
            },
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveDepartmentId(
        User $user,
        array $data,
    ): int {
        $departmentId = $user->isStaff()
            ? $user->department_id
            : (int) $data['department_id'];

        if ($departmentId === null) {
            throw ValidationException::withMessages([
                'department_id' => 'Akun Staff belum memiliki unit atau bidang.',
            ]);
        }

        return $departmentId;
    }

    private function nextSequenceNumber(
        string $documentCode,
        int $year,
    ): int {
        $now = now();

        DocumentSequence::query()
            ->insertOrIgnore([
                'document_code' => $documentCode,

                'year' => $year,

                'last_number' => 0,

                'created_at' => $now,

                'updated_at' => $now,
            ]);

        $sequence = DocumentSequence::query()
            ->where(
                'document_code',
                $documentCode,
            )
            ->where(
                'year',
                $year,
            )
            ->lockForUpdate()
            ->firstOrFail();

        $nextNumber =
            $sequence->last_number + 1;

        $sequence->update([
            'last_number' => $nextNumber,
        ]);

        return $nextNumber;
    }

    private function reserveSequenceNumber(
        string $documentCode,
        int $year,
        int $sequenceNumber,
    ): void {
        $now = now();

        DocumentSequence::query()
            ->insertOrIgnore([
                'document_code' => $documentCode,

                'year' => $year,

                'last_number' => 0,

                'created_at' => $now,

                'updated_at' => $now,
            ]);

        $sequence = DocumentSequence::query()
            ->where(
                'document_code',
                $documentCode,
            )
            ->where(
                'year',
                $year,
            )
            ->lockForUpdate()
            ->firstOrFail();

        if (
            $sequence->last_number
            < $sequenceNumber
        ) {
            $sequence->update([
                'last_number' => $sequenceNumber,
            ]);
        }
    }

    private function formatDocumentNumber(
        int $sequenceNumber,
        string $documentCode,
        int $month,
        int $year,
    ): string {
        return sprintf(
            '%03d/%s/%s/%s/%d',
            $sequenceNumber,
            $documentCode,
            $this->institutionCode(),
            $this->romanMonth(
                $month,
            ),
            $year,
        );
    }

    private function documentCode(): string
    {
        $code = config(
            'bast.document.code',
        );

        if (
            ! is_string($code)
            || trim($code) === ''
        ) {
            throw new RuntimeException(
                'Konfigurasi kode dokumen BAST tidak valid.',
            );
        }

        return strtoupper(
            trim(
                $code,
            ),
        );
    }

    private function institutionCode(): string
    {
        $code = config(
            'bast.document.institution_code',
        );

        if (
            ! is_string($code)
            || trim($code) === ''
        ) {
            throw new RuntimeException(
                'Konfigurasi kode instansi BAST tidak valid.',
            );
        }

        return strtoupper(
            trim(
                $code,
            ),
        );
    }

    private function romanMonth(
        int $month,
    ): string {
        $months = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        if (
            ! isset(
                $months[$month],
            )
        ) {
            throw new InvalidArgumentException(
                'Bulan dokumen tidak valid.',
            );
        }

        return $months[$month];
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

        foreach (
            $definitions as $type => $sortOrder
        ) {
            $party =
                $parties[$type] ?? null;

            if (! is_array($party)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Party data for %s is invalid.',
                        $type,
                    ),
                );
            }

            $userId =
                isset(
                    $party['user_id'],
                )
                && $party['user_id'] !== ''
                    ? (int) $party['user_id']
                    : null;

            $bast->parties()->create([
                'user_id' => $userId,

                'party_type' => $type,

                'name' => (string) (
                    $party['name'] ?? ''
                ),

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

        foreach (
            array_values($items) as $index => $item
        ) {
            if (! is_array($item)) {
                throw new InvalidArgumentException(
                    'BAST item data is invalid.',
                );
            }

            $categoryId =
                isset(
                    $item['item_category_id'],
                )
                && $item['item_category_id'] !== ''
                    ? (int) $item['item_category_id']
                    : null;

            $unitId =
                isset(
                    $item['unit_id'],
                )
                && $item['unit_id'] !== ''
                    ? (int) $item['unit_id']
                    : null;

            $value =
                isset(
                    $item['value'],
                )
                && $item['value'] !== ''
                    ? (float) $item['value']
                    : null;

            $bast->items()->create([
                'item_category_id' => $categoryId,

                'unit_id' => $unitId,

                'name' => (string) (
                    $item['name'] ?? ''
                ),

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

    private function nullableString(
        mixed $value,
    ): ?string {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        return (string) $value;
    }
}
