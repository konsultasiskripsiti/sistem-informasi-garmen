<?php

namespace Database\Seeders;

use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class RawMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            // KELOMPOK KAIN (KDM) - Stok Habis/Nol
            [
                'raw_material_code' => 'KDM001',
                'name' => 'Kain Linen Natural',
                'quantity' => 0,
                'unit' => 'Meter',
                'description' => 'Stok kosong - Menunggu pengiriman supplier.',
            ],
            [
                'raw_material_code' => 'KDM002',
                'name' => 'Kain Katun Combed 30s (Black)',
                'quantity' => 0,
                'unit' => 'Meter',
                'description' => 'Bahan kaos hitam reaktif.',
            ],
            [
                'raw_material_code' => 'KDM003',
                'name' => 'Kain Denim 12oz',
                'quantity' => 0,
                'unit' => 'Meter',
                'description' => 'Bahan celana jeans standar.',
            ],
            [
                'raw_material_code' => 'KDM004',
                'name' => 'Kain Drill Navy',
                'quantity' => 0,
                'unit' => 'Meter',
                'description' => 'Bahan seragam kerja/PDL.',
            ],
            [
                'raw_material_code' => 'KDM005',
                'name' => 'Kain Rayon Viscose',
                'quantity' => 0,
                'unit' => 'Meter',
                'description' => 'Bahan daster/pakaian santai.',
            ],

            // KELOMPOK BENANG (BJM) - Stok Habis/Nol
            [
                'raw_material_code' => 'BJM001',
                'name' => 'Benang Astra Putih',
                'quantity' => 0,
                'unit' => 'Roll',
                'description' => 'Benang jahit utama 40/2.',
            ],
            [
                'raw_material_code' => 'BJM002',
                'name' => 'Benang Astra Hitam',
                'quantity' => 0,
                'unit' => 'Roll',
                'description' => 'Benang jahit utama 40/2.',
            ],
            [
                'raw_material_code' => 'BJM003',
                'name' => 'Benang Obras Merah',
                'quantity' => 0,
                'unit' => 'Roll',
                'description' => 'Benang khusus mesin obras.',
            ],

            // KELOMPOK AKSESORIS (AKS) - Stok Habis/Nol
            [
                'raw_material_code' => 'AKS001',
                'name' => 'Kancing Kemeja 18L',
                'quantity' => 0,
                'unit' => 'Pcs',
                'description' => 'Kancing plastik standar.',
            ],
            [
                'raw_material_code' => 'AKS002',
                'name' => 'Resleting YKK 7 Inch',
                'quantity' => 0,
                'unit' => 'Pcs',
                'description' => 'Zipper metal celana.',
            ],
            [
                'raw_material_code' => 'AKS003',
                'name' => 'Label Size S',
                'quantity' => 0,
                'unit' => 'Pcs',
                'description' => 'Label ukuran woven.',
            ],
            [
                'raw_material_code' => 'AKS004',
                'name' => 'Label Size M',
                'quantity' => 0,
                'unit' => 'Pcs',
                'description' => 'Label ukuran woven.',
            ],
            [
                'raw_material_code' => 'AKS005',
                'name' => 'Karet Elastis 3cm',
                'quantity' => 0,
                'unit' => 'Roll',
                'description' => 'Karet pinggang kolor.',
            ],
            [
                'raw_material_code' => 'AKS006',
                'name' => 'Interlining Kerah',
                'quantity' => 0,
                'unit' => 'Meter',
                'description' => 'Kain keras pelapis kerah.',
            ],
        ];

        foreach ($materials as $material) {
            RawMaterial::updateOrCreate(
                ['raw_material_code' => $material['raw_material_code']],
                $material
            );
        }
    }
}