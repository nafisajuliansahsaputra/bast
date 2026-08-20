<?php

namespace Database\Seeders;

use App\Models\BastType;
use App\Models\ItemCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBastTypes();
        $this->seedUnits();
        $this->seedItemCategories();
    }

    private function seedBastTypes(): void
    {
        $bastTypes = [
            [
                'name' => 'Serah Terima Barang/Aset',
                'slug' => 'barang-aset',
                'description' => 'Berita acara untuk proses serah terima barang atau aset.',
                'is_active' => true,
            ],
            [
                'name' => 'Serah Terima Dokumen',
                'slug' => 'dokumen',
                'description' => 'Berita acara untuk proses serah terima dokumen atau berkas.',
                'is_active' => true,
            ],
            [
                'name' => 'Serah Terima Pekerjaan/Jasa',
                'slug' => 'pekerjaan-jasa',
                'description' => 'Berita acara untuk proses serah terima hasil pekerjaan atau jasa.',
                'is_active' => true,
            ],
            [
                'name' => 'Serah Terima Perangkat',
                'slug' => 'perangkat',
                'description' => 'Berita acara untuk proses serah terima perangkat atau peralatan.',
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'slug' => 'lainnya',
                'description' => 'Jenis berita acara serah terima di luar kategori utama.',
                'is_active' => true,
            ],
        ];

        foreach ($bastTypes as $bastType) {
            BastType::updateOrCreate(
                ['slug' => $bastType['slug']],
                $bastType,
            );
        }
    }

    private function seedUnits(): void
    {
        $units = [
            [
                'name' => 'Unit',
                'symbol' => 'unit',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Buah',
                'symbol' => 'buah',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Set',
                'symbol' => 'set',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Paket',
                'symbol' => 'paket',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Berkas',
                'symbol' => 'berkas',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Lembar',
                'symbol' => 'lembar',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Box',
                'symbol' => 'box',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Meter',
                'symbol' => 'm',
                'description' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'description' => null,
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['name' => $unit['name']],
                $unit,
            );
        }
    }

    private function seedItemCategories(): void
    {
        $categories = [
            [
                'name' => 'Barang/Aset',
                'slug' => 'barang-aset',
                'description' => 'Barang inventaris atau aset yang diserahterimakan.',
                'is_active' => true,
            ],
            [
                'name' => 'Dokumen',
                'slug' => 'dokumen',
                'description' => 'Dokumen, surat, arsip, atau berkas yang diserahterimakan.',
                'is_active' => true,
            ],
            [
                'name' => 'Perangkat',
                'slug' => 'perangkat',
                'description' => 'Perangkat elektronik, komputer, jaringan, atau peralatan teknis.',
                'is_active' => true,
            ],
            [
                'name' => 'Perlengkapan',
                'slug' => 'perlengkapan',
                'description' => 'Perlengkapan operasional atau pendukung.',
                'is_active' => true,
            ],
            [
                'name' => 'Pekerjaan/Jasa',
                'slug' => 'pekerjaan-jasa',
                'description' => 'Hasil pekerjaan atau jasa yang menjadi objek serah terima.',
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'slug' => 'lainnya',
                'description' => 'Objek serah terima di luar kategori utama.',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            ItemCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }
    }
}
