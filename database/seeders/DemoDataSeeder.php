<?php

namespace Database\Seeders;

use App\Models\Bast;
use App\Models\BastType;
use App\Models\Department;
use App\Models\ItemCategory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\BastLifecycleService;
use App\Services\BastService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('bast.super_admin.password');

        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException(
                'BAST_SUPER_ADMIN_PASSWORD harus dikonfigurasi sebelum membuat data demo.',
            );
        }

        try {
            Storage::disk('local')->deleteDirectory('bast');

            $this->seedDepartments();

            $users = $this->seedUsers(
                $password,
            );

            $this->seedBasts(
                $users,
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    private function seedDepartments(): void
    {
        $departments = [
            [
                'code' => 'ADM',
                'name' => 'Administrasi dan Umum',
                'description' => 'Unit administrasi, tata usaha, dan kebutuhan operasional internal.',
                'is_active' => true,
            ],
            [
                'code' => 'TI',
                'name' => 'Infrastruktur Teknologi Informasi',
                'description' => 'Unit pengelolaan infrastruktur, perangkat, jaringan, dan layanan teknologi informasi.',
                'is_active' => true,
            ],
            [
                'code' => 'LAYANAN',
                'name' => 'Layanan Informasi Publik',
                'description' => 'Unit pengelolaan pelayanan dan penyampaian informasi publik.',
                'is_active' => true,
            ],
            [
                'code' => 'DATA',
                'name' => 'Data dan Statistik',
                'description' => 'Unit pengelolaan data, statistik, dan dokumentasi pendukung.',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::query()->updateOrCreate(
                [
                    'code' => $department['code'],
                ],
                $department,
            );
        }
    }

    /**
     * @return array<string, User>
     */
    private function seedUsers(
        string $password,
    ): array {
        $adminRole = Role::query()
            ->where('slug', 'admin')
            ->firstOrFail();

        $staffRole = Role::query()
            ->where('slug', 'staff')
            ->firstOrFail();

        $hashedPassword = Hash::make(
            $password,
        );

        $definitions = [
            [
                'key' => 'rina',
                'name' => 'Rina Maharani',
                'nip' => 'DUMMY-ADM-001',
                'email' => 'rina.maharani@bast.local',
                'position' => 'Administrator Operasional',
                'department' => 'ADM',
                'role_id' => $adminRole->id,
                'status' => 'active',
            ],
            [
                'key' => 'dedi',
                'name' => 'Dedi Kurniawan',
                'nip' => 'DUMMY-TI-001',
                'email' => 'dedi.kurniawan@bast.local',
                'position' => 'Administrator Teknologi Informasi',
                'department' => 'TI',
                'role_id' => $adminRole->id,
                'status' => 'active',
            ],
            [
                'key' => 'andika',
                'name' => 'Andika Pratama',
                'nip' => 'DUMMY-TI-002',
                'email' => 'andika.pratama@bast.local',
                'position' => 'Staf Infrastruktur TI',
                'department' => 'TI',
                'role_id' => $staffRole->id,
                'status' => 'active',
            ],
            [
                'key' => 'siti',
                'name' => 'Siti Nurhaliza',
                'nip' => 'DUMMY-LAY-001',
                'email' => 'siti.nurhaliza@bast.local',
                'position' => 'Staf Layanan Informasi',
                'department' => 'LAYANAN',
                'role_id' => $staffRole->id,
                'status' => 'active',
            ],
            [
                'key' => 'fajar',
                'name' => 'Fajar Ramadhan',
                'nip' => 'DUMMY-DATA-001',
                'email' => 'fajar.ramadhan@bast.local',
                'position' => 'Staf Data dan Statistik',
                'department' => 'DATA',
                'role_id' => $staffRole->id,
                'status' => 'active',
            ],
            [
                'key' => 'maya',
                'name' => 'Maya Lestari',
                'nip' => 'DUMMY-ADM-002',
                'email' => 'maya.lestari@bast.local',
                'position' => 'Staf Administrasi',
                'department' => 'ADM',
                'role_id' => $staffRole->id,
                'status' => 'active',
            ],
            [
                'key' => 'yusuf',
                'name' => 'Yusuf Maulana',
                'nip' => 'DUMMY-TI-003',
                'email' => 'yusuf.maulana@bast.local',
                'position' => 'Staf Dukungan Teknis',
                'department' => 'TI',
                'role_id' => $staffRole->id,
                'status' => 'inactive',
            ],
        ];

        $users = [];

        foreach ($definitions as $definition) {
            $department = Department::query()
                ->where(
                    'code',
                    $definition['department'],
                )
                ->firstOrFail();

            $user = User::query()->updateOrCreate(
                [
                    'email' => $definition['email'],
                ],
                [
                    'name' => $definition['name'],
                    'nip' => $definition['nip'],
                    'email_verified_at' => now(),
                    'position' => $definition['position'],
                    'phone' => null,
                    'status' => $definition['status'],
                    'role_id' => $definition['role_id'],
                    'department_id' => $department->id,
                    'password' => $hashedPassword,
                ],
            );

            $users[$definition['key']] = $user;
        }

        return $users;
    }

    /**
     * @param  array<string, User>  $users
     */
    private function seedBasts(
        array $users,
    ): void {
        $bastService = app(
            BastService::class,
        );

        $lifecycleService = app(
            BastLifecycleService::class,
        );

        $admin = $users['rina'] ?? null;

        if (! $admin instanceof User) {
            throw new RuntimeException(
                'User Admin data demo tidak ditemukan.',
            );
        }

        foreach (
            $this->bastDefinitions() as $index => $definition
        ) {
            $creatorKey = (string) $definition['creator'];

            $creator = $users[$creatorKey] ?? null;

            if (! $creator instanceof User) {
                throw new RuntimeException(
                    sprintf(
                        'User data demo "%s" tidak ditemukan.',
                        $creatorKey,
                    ),
                );
            }

            $department = Department::query()
                ->where(
                    'code',
                    (string) $definition['department'],
                )
                ->firstOrFail();

            $bastType = BastType::query()
                ->where(
                    'slug',
                    (string) $definition['type'],
                )
                ->firstOrFail();

            $createdAt = Carbon::parse(
                (string) $definition['created_at'],
            );

            Carbon::setTestNow(
                $createdAt,
            );

            $items = $this->buildItems(
                $definition['items'] ?? [],
            );

            $externalParty = $this->externalParty(
                $index,
            );

            $data = [
                'bast_type_id' => $bastType->id,
                'department_id' => $department->id,

                'title' => (string) $definition['title'],

                'description' => (string) $definition['description'],

                'document_date' => $createdAt->toDateString(),

                'handover_date' => $createdAt
                    ->copy()
                    ->addDays(2)
                    ->toDateString(),

                'handover_place' => (string) $definition['place'],

                'parties' => [
                    'first_party' => $this->internalParty(
                        $creator,
                        $department,
                    ),

                    'second_party' => $externalParty,
                ],

                'items' => $items,
            ];

            $bast = $bastService->createDraft(
                $creator,
                $data,
                '127.0.0.1',
                'BAST Demo Seeder',
            );

            if (
                ($definition['attachment'] ?? false)
                === true
            ) {
                $this->seedAttachment(
                    $bast,
                    $creator,
                    $createdAt
                        ->copy()
                        ->addHours(2),
                    $index + 1,
                );
            }

            $target = (string) $definition['target'];

            if ($target === 'draft') {
                continue;
            }

            Carbon::setTestNow(
                $createdAt
                    ->copy()
                    ->addDay(),
            );

            $bast = $bastService->finalize(
                $creator,
                $bast,
                '127.0.0.1',
                'BAST Demo Seeder',
            );

            if ($target === 'finalized') {
                continue;
            }

            if ($target === 'completed') {
                Carbon::setTestNow(
                    $createdAt
                        ->copy()
                        ->addDays(3),
                );

                $lifecycleService->complete(
                    $creator,
                    $bast,
                    '127.0.0.1',
                    'BAST Demo Seeder',
                );

                continue;
            }

            if ($target === 'archived') {
                Carbon::setTestNow(
                    $createdAt
                        ->copy()
                        ->addDays(3),
                );

                $bast = $lifecycleService->complete(
                    $creator,
                    $bast,
                    '127.0.0.1',
                    'BAST Demo Seeder',
                );

                Carbon::setTestNow(
                    $createdAt
                        ->copy()
                        ->addDays(7),
                );

                $lifecycleService->archive(
                    $admin,
                    $bast,
                    '127.0.0.1',
                    'BAST Demo Seeder',
                );

                continue;
            }

            if ($target === 'cancelled') {
                Carbon::setTestNow(
                    $createdAt
                        ->copy()
                        ->addDays(3),
                );

                $lifecycleService->cancel(
                    $admin,
                    $bast,
                    (string) (
                        $definition['cancellation_reason']
                        ?? 'Dokumen dibatalkan pada data demo.'
                    ),
                    '127.0.0.1',
                    'BAST Demo Seeder',
                );

                continue;
            }

            if ($target === 'revision') {
                Carbon::setTestNow(
                    $createdAt
                        ->copy()
                        ->addDays(3),
                );

                $bast = $lifecycleService->reopen(
                    $admin,
                    $bast,
                    '127.0.0.1',
                    'BAST Demo Seeder',
                );

                Carbon::setTestNow(
                    $createdAt
                        ->copy()
                        ->addDays(4),
                );

                $data['description'] =
                    (string) $definition['description']
                    .' Dokumen sedang direvisi setelah pemeriksaan administrasi.';

                $bastService->updateDraft(
                    $creator,
                    $bast,
                    $data,
                    '127.0.0.1',
                    'BAST Demo Seeder',
                );

                continue;
            }

            throw new RuntimeException(
                sprintf(
                    'Target status data demo "%s" tidak dikenali.',
                    $target,
                ),
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function bastDefinitions(): array
    {
        return [
            [
                'creator' => 'andika',
                'department' => 'TI',
                'type' => 'perangkat',
                'title' => 'Serah Terima Laptop Operasional Tim Infrastruktur',
                'description' => 'Serah terima perangkat laptop untuk mendukung kegiatan operasional dan pemeliharaan infrastruktur teknologi informasi.',
                'place' => 'Ruang Infrastruktur Teknologi Informasi',
                'created_at' => '2026-06-03 09:00:00',
                'target' => 'archived',
                'attachment' => true,
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Laptop Operasional',
                        'code' => 'LT-OPS-2026',
                        'inventory_number' => 'INV-DEMO-LT-001',
                        'serial_number' => null,
                        'quantity' => 5,
                        'condition' => 'Baik',
                        'value' => 42500000,
                        'description' => 'Laptop operasional untuk tim infrastruktur TI.',
                    ],
                    [
                        'category' => 'perlengkapan',
                        'unit' => 'Unit',
                        'name' => 'Docking Station',
                        'code' => 'DS-OPS-2026',
                        'inventory_number' => 'INV-DEMO-DS-001',
                        'serial_number' => null,
                        'quantity' => 5,
                        'condition' => 'Baik',
                        'value' => 7500000,
                        'description' => 'Perlengkapan pendukung laptop operasional.',
                    ],
                ],
            ],
            [
                'creator' => 'fajar',
                'department' => 'DATA',
                'type' => 'dokumen',
                'title' => 'Serah Terima Dokumen Rekapitulasi Data Semester I',
                'description' => 'Penyerahan berkas rekapitulasi data dan statistik semester pertama tahun 2026.',
                'place' => 'Ruang Data dan Statistik',
                'created_at' => '2026-06-18 10:15:00',
                'target' => 'archived',
                'attachment' => true,
                'items' => [
                    [
                        'category' => 'dokumen',
                        'unit' => 'Berkas',
                        'name' => 'Berkas Rekapitulasi Data Semester I',
                        'code' => 'DOC-DATA-S1-2026',
                        'inventory_number' => null,
                        'serial_number' => null,
                        'quantity' => 12,
                        'condition' => 'Lengkap',
                        'value' => null,
                        'description' => 'Berkas laporan dan rekapitulasi data semester pertama.',
                    ],
                ],
            ],
            [
                'creator' => 'dedi',
                'department' => 'TI',
                'type' => 'pekerjaan-jasa',
                'title' => 'Serah Terima Hasil Pemeliharaan Server Aplikasi',
                'description' => 'Serah terima hasil pekerjaan pemeliharaan berkala server aplikasi internal.',
                'place' => 'Ruang Server',
                'created_at' => '2026-07-04 08:45:00',
                'target' => 'completed',
                'attachment' => true,
                'items' => [
                    [
                        'category' => 'pekerjaan-jasa',
                        'unit' => 'Paket',
                        'name' => 'Paket Pemeliharaan Server Aplikasi',
                        'code' => 'SRV-MAINT-2026',
                        'inventory_number' => null,
                        'serial_number' => null,
                        'quantity' => 1,
                        'condition' => 'Selesai',
                        'value' => 18500000,
                        'description' => 'Pemeriksaan, pembaruan, dan pemeliharaan server aplikasi.',
                    ],
                ],
            ],
            [
                'creator' => 'siti',
                'department' => 'LAYANAN',
                'type' => 'dokumen',
                'title' => 'Serah Terima Arsip Dokumentasi Informasi Publik',
                'description' => 'Penyerahan arsip dokumentasi publikasi dan pelayanan informasi.',
                'place' => 'Ruang Layanan Informasi Publik',
                'created_at' => '2026-07-13 13:00:00',
                'target' => 'completed',
                'attachment' => false,
                'items' => [
                    [
                        'category' => 'dokumen',
                        'unit' => 'Berkas',
                        'name' => 'Arsip Dokumentasi Informasi Publik',
                        'code' => 'ARSIP-LAY-2026',
                        'inventory_number' => null,
                        'serial_number' => null,
                        'quantity' => 24,
                        'condition' => 'Lengkap',
                        'value' => null,
                        'description' => 'Dokumentasi kegiatan publikasi dan pelayanan informasi.',
                    ],
                ],
            ],
            [
                'creator' => 'maya',
                'department' => 'ADM',
                'type' => 'barang-aset',
                'title' => 'Serah Terima Perlengkapan Ruang Rapat',
                'description' => 'Penyerahan perlengkapan dan perangkat pendukung ruang rapat internal.',
                'place' => 'Ruang Rapat Utama',
                'created_at' => '2026-07-21 09:30:00',
                'target' => 'completed',
                'attachment' => false,
                'items' => [
                    [
                        'category' => 'perlengkapan',
                        'unit' => 'Buah',
                        'name' => 'Kursi Rapat',
                        'code' => 'KR-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-KR-001',
                        'serial_number' => null,
                        'quantity' => 20,
                        'condition' => 'Baik',
                        'value' => 12000000,
                        'description' => 'Kursi untuk ruang rapat utama.',
                    ],
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Proyektor Ruang Rapat',
                        'code' => 'PJ-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-PJ-001',
                        'serial_number' => 'DEMO-PJ-001',
                        'quantity' => 1,
                        'condition' => 'Baik',
                        'value' => 8500000,
                        'description' => 'Perangkat presentasi ruang rapat.',
                    ],
                ],
            ],
            [
                'creator' => 'andika',
                'department' => 'TI',
                'type' => 'perangkat',
                'title' => 'Serah Terima Printer Layanan Internal',
                'description' => 'Rencana penyerahan printer untuk mendukung kebutuhan layanan administrasi internal.',
                'place' => 'Ruang Infrastruktur Teknologi Informasi',
                'created_at' => '2026-07-28 14:10:00',
                'target' => 'cancelled',
                'attachment' => true,
                'cancellation_reason' => 'Spesifikasi perangkat yang diterima tidak sesuai dengan dokumen kebutuhan sehingga proses serah terima dibatalkan.',
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Printer Multifungsi',
                        'code' => 'PRN-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-PRN-001',
                        'serial_number' => 'DEMO-PRN-001',
                        'quantity' => 2,
                        'condition' => 'Perlu Verifikasi',
                        'value' => 9600000,
                        'description' => 'Printer multifungsi untuk kebutuhan layanan internal.',
                    ],
                ],
            ],
            [
                'creator' => 'rina',
                'department' => 'ADM',
                'type' => 'barang-aset',
                'title' => 'Serah Terima Laptop Administrasi',
                'description' => 'Penyerahan laptop baru untuk mendukung pekerjaan administrasi dan tata usaha.',
                'place' => 'Ruang Administrasi dan Umum',
                'created_at' => '2026-08-03 08:30:00',
                'target' => 'finalized',
                'attachment' => true,
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Laptop Administrasi',
                        'code' => 'LT-ADM-2026',
                        'inventory_number' => 'INV-DEMO-ADM-001',
                        'serial_number' => null,
                        'quantity' => 3,
                        'condition' => 'Baik',
                        'value' => 25500000,
                        'description' => 'Laptop operasional untuk pekerjaan administrasi.',
                    ],
                ],
            ],
            [
                'creator' => 'andika',
                'department' => 'TI',
                'type' => 'perangkat',
                'title' => 'Serah Terima Switch Jaringan Lantai Dua',
                'description' => 'Penyerahan perangkat switch jaringan untuk peningkatan konektivitas jaringan lokal.',
                'place' => 'Ruang Infrastruktur Teknologi Informasi',
                'created_at' => '2026-08-07 10:00:00',
                'target' => 'finalized',
                'attachment' => false,
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Managed Network Switch',
                        'code' => 'SW-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-SW-001',
                        'serial_number' => 'DEMO-SW-001',
                        'quantity' => 2,
                        'condition' => 'Baik',
                        'value' => 14000000,
                        'description' => 'Perangkat switch untuk distribusi jaringan lantai dua.',
                    ],
                ],
            ],
            [
                'creator' => 'fajar',
                'department' => 'DATA',
                'type' => 'dokumen',
                'title' => 'Serah Terima Dokumen Laporan Statistik Bulanan',
                'description' => 'Penyerahan laporan statistik operasional dan rekap data bulanan.',
                'place' => 'Ruang Data dan Statistik',
                'created_at' => '2026-08-10 11:20:00',
                'target' => 'finalized',
                'attachment' => true,
                'items' => [
                    [
                        'category' => 'dokumen',
                        'unit' => 'Berkas',
                        'name' => 'Laporan Statistik Bulanan',
                        'code' => 'STAT-08-2026',
                        'inventory_number' => null,
                        'serial_number' => null,
                        'quantity' => 8,
                        'condition' => 'Lengkap',
                        'value' => null,
                        'description' => 'Dokumen laporan statistik untuk periode berjalan.',
                    ],
                ],
            ],
            [
                'creator' => 'siti',
                'department' => 'LAYANAN',
                'type' => 'perangkat',
                'title' => 'Serah Terima Perangkat Display Informasi Publik',
                'description' => 'Penyerahan perangkat display digital untuk penyampaian informasi publik di area layanan.',
                'place' => 'Ruang Layanan Informasi Publik',
                'created_at' => '2026-08-12 09:10:00',
                'target' => 'revision',
                'attachment' => true,
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Display Informasi Digital',
                        'code' => 'DISPLAY-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-DSP-001',
                        'serial_number' => 'DEMO-DSP-001',
                        'quantity' => 2,
                        'condition' => 'Baik',
                        'value' => 18000000,
                        'description' => 'Display digital untuk area pelayanan informasi.',
                    ],
                ],
            ],
            [
                'creator' => 'maya',
                'department' => 'ADM',
                'type' => 'barang-aset',
                'title' => 'Serah Terima Monitor Ruang Administrasi',
                'description' => 'Draft penyerahan monitor tambahan untuk workstation administrasi.',
                'place' => 'Ruang Administrasi dan Umum',
                'created_at' => '2026-08-17 08:40:00',
                'target' => 'draft',
                'attachment' => false,
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Monitor LED',
                        'code' => 'MON-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-MON-001',
                        'serial_number' => null,
                        'quantity' => 4,
                        'condition' => 'Baik',
                        'value' => 10000000,
                        'description' => 'Monitor tambahan untuk workstation administrasi.',
                    ],
                ],
            ],
            [
                'creator' => 'fajar',
                'department' => 'DATA',
                'type' => 'dokumen',
                'title' => 'Serah Terima Berkas Dokumentasi Kegiatan',
                'description' => 'Draft penyerahan berkas dokumentasi kegiatan untuk kebutuhan pengarsipan data.',
                'place' => 'Ruang Data dan Statistik',
                'created_at' => '2026-08-19 13:30:00',
                'target' => 'draft',
                'attachment' => false,
                'items' => [
                    [
                        'category' => 'dokumen',
                        'unit' => 'Berkas',
                        'name' => 'Berkas Dokumentasi Kegiatan',
                        'code' => 'DOK-KEG-2026',
                        'inventory_number' => null,
                        'serial_number' => null,
                        'quantity' => 6,
                        'condition' => 'Dalam Pemeriksaan',
                        'value' => null,
                        'description' => 'Dokumentasi kegiatan yang sedang dipersiapkan untuk serah terima.',
                    ],
                ],
            ],
            [
                'creator' => 'andika',
                'department' => 'TI',
                'type' => 'perangkat',
                'title' => 'Serah Terima Perangkat Penyimpanan Cadangan',
                'description' => 'Draft penyerahan perangkat penyimpanan untuk kebutuhan backup data operasional.',
                'place' => 'Ruang Server',
                'created_at' => '2026-08-20 15:00:00',
                'target' => 'draft',
                'attachment' => false,
                'items' => [
                    [
                        'category' => 'perangkat',
                        'unit' => 'Unit',
                        'name' => 'Network Attached Storage',
                        'code' => 'NAS-DEMO-2026',
                        'inventory_number' => 'INV-DEMO-NAS-001',
                        'serial_number' => 'DEMO-NAS-001',
                        'quantity' => 2,
                        'condition' => 'Baik',
                        'value' => 24000000,
                        'description' => 'Perangkat penyimpanan cadangan untuk data operasional.',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function internalParty(
        User $user,
        Department $department,
    ): array {
        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'nip' => $user->nip,
            'position' => $user->position,
            'department' => $department->name,
            'institution' => 'Diskominfo Kabupaten Cianjur',
            'address' => 'Kabupaten Cianjur, Jawa Barat',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function externalParty(
        int $index,
    ): array {
        $parties = [
            [
                'name' => 'Bima Saputra',
                'position' => 'Koordinator Penyedia',
                'institution' => 'PT Mitra Teknologi Nusantara (Data Demo)',
                'address' => 'Cianjur, Jawa Barat',
            ],
            [
                'name' => 'Nadia Putri',
                'position' => 'Administrasi Proyek',
                'institution' => 'CV Solusi Data Mandiri (Data Demo)',
                'address' => 'Bandung, Jawa Barat',
            ],
            [
                'name' => 'Rizky Hidayat',
                'position' => 'Penanggung Jawab Teknis',
                'institution' => 'PT Integrasi Sistem Digital (Data Demo)',
                'address' => 'Bogor, Jawa Barat',
            ],
        ];

        $party = $parties[
            $index % count($parties)
        ];

        return [
            'user_id' => null,
            'name' => $party['name'],
            'nip' => null,
            'position' => $party['position'],
            'department' => null,
            'institution' => $party['institution'],
            'address' => $party['address'],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(
        mixed $items,
    ): array {
        if (! is_array($items)) {
            throw new RuntimeException(
                'Data item demo tidak valid.',
            );
        }

        $result = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                throw new RuntimeException(
                    'Data item demo tidak valid.',
                );
            }

            $category = ItemCategory::query()
                ->where(
                    'slug',
                    (string) $item['category'],
                )
                ->firstOrFail();

            $unit = Unit::query()
                ->where(
                    'name',
                    (string) $item['unit'],
                )
                ->firstOrFail();

            $result[] = [
                'item_category_id' => $category->id,
                'unit_id' => $unit->id,

                'name' => (string) $item['name'],

                'code' => $item['code'] ?? null,

                'inventory_number' => $item['inventory_number']
                    ?? null,

                'serial_number' => $item['serial_number']
                    ?? null,

                'quantity' => $item['quantity'] ?? 1,

                'condition' => $item['condition']
                    ?? null,

                'value' => $item['value']
                    ?? null,

                'description' => $item['description']
                    ?? null,
            ];
        }

        return $result;
    }

    private function seedAttachment(
        Bast $bast,
        User $uploader,
        Carbon $timestamp,
        int $number,
    ): void {
        Carbon::setTestNow(
            $timestamp,
        );

        $storedName =
            Str::uuid()->toString()
            .'.pdf';

        $originalName = sprintf(
            'dokumen-pendukung-demo-%02d.pdf',
            $number,
        );

        $path = sprintf(
            'bast/%s/%s',
            $bast->uuid,
            $storedName,
        );

        $safeTitle = htmlspecialchars(
            $bast->title,
            ENT_QUOTES,
            'UTF-8',
        );

        $pdfContent = Pdf::loadHTML(
            <<<HTML
            <!DOCTYPE html>
            <html lang="id">
                <head>
                    <meta charset="UTF-8">
                    <title>Dokumen Pendukung Demo</title>
                </head>
                <body style="font-family: sans-serif; padding: 32px;">
                    <h2>Dokumen Pendukung BAST</h2>
                    <p><strong>{$safeTitle}</strong></p>
                    <p>
                        File ini dibuat otomatis sebagai lampiran data demo
                        untuk kebutuhan pengembangan dan portfolio BAST.
                    </p>
                    <p>
                        Dokumen ini tidak merupakan dokumen resmi instansi.
                    </p>
                </body>
            </html>
            HTML,
        )->output();

        Storage::disk('local')->put(
            $path,
            $pdfContent,
        );

        $attachment = $bast
            ->attachments()
            ->create([
                'uploaded_by' => $uploader->id,

                'category' => 'supporting_document',

                'original_name' => $originalName,

                'stored_name' => $storedName,

                'disk' => 'local',

                'path' => $path,

                'mime_type' => 'application/pdf',

                'file_size' => strlen(
                    $pdfContent,
                ),

                'description' => 'Dokumen pendukung otomatis untuk data demo portfolio.',
            ]);

        $bast
            ->activityLogs()
            ->create([
                'user_id' => $uploader->id,

                'action' => 'ATTACHMENT_UPLOADED',

                'description' => sprintf(
                    'Mengunggah lampiran "%s" pada BAST "%s".',
                    $attachment->original_name,
                    $bast->title,
                ),

                'ip_address' => '127.0.0.1',

                'user_agent' => 'BAST Demo Seeder',

                'old_values' => null,

                'new_values' => [
                    'attachment_id' => $attachment->id,

                    'original_name' => $attachment->original_name,

                    'category' => $attachment->category,
                ],
            ]);
    }
}
