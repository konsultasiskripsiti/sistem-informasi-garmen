<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = RawMaterial::all()->keyBy('raw_material_code');

        $products = [
            // KATEGORI: ATASAN (Tops)
            [
                'name' => 'Kaos Linen Oversize',
                'size' => 'L',
                'unit' => 'Pcs',
                'unit_price' => 185000,
                'bom' => ['KDM001' => 1.80, 'BJM001' => 0.25, 'AKS004' => 1]
            ],
            [
                'name' => 'Kaos Polos Cotton 30s',
                'size' => 'M',
                'unit' => 'Pcs',
                'unit_price' => 85000,
                'bom' => ['KDM002' => 1.20, 'BJM002' => 0.20, 'AKS003' => 1]
            ],
            [
                'name' => 'Kemeja Drill Teknisi',
                'size' => 'XL',
                'unit' => 'Pcs',
                'unit_price' => 155000,
                'bom' => ['KDM004' => 2.00, 'BJM001' => 0.30, 'AKS001' => 7, 'AKS006' => 0.20]
            ],
            [
                'name' => 'Polo Shirt Cotton',
                'size' => 'L',
                'unit' => 'Pcs',
                'unit_price' => 110000,
                'bom' => ['KDM002' => 1.40, 'BJM001' => 0.20, 'AKS001' => 3]
            ],
            [
                'name' => 'Blouse Linen Wanita',
                'size' => 'S',
                'unit' => 'Pcs',
                'unit_price' => 195000,
                'bom' => ['KDM001' => 1.50, 'BJM001' => 0.20, 'AKS001' => 5]
            ],

            // KATEGORI: BAWAHAN (Bottoms)
            [
                'name' => 'Celana Denim Slim Fit',
                'size' => '32',
                'unit' => 'Pcs',
                'unit_price' => 350000,
                'bom' => ['KDM003' => 2.50, 'BJM002' => 0.50, 'AKS002' => 1, 'AKS001' => 1]
            ],
            [
                'name' => 'Celana Kerja Drill',
                'size' => '34',
                'unit' => 'Pcs',
                'unit_price' => 175000,
                'bom' => ['KDM004' => 2.30, 'BJM002' => 0.40, 'AKS002' => 1, 'AKS003' => 1]
            ],
            [
                'name' => 'Short Pants Casual',
                'size' => 'M',
                'unit' => 'Pcs',
                'unit_price' => 95000,
                'bom' => ['KDM002' => 1.00, 'BJM002' => 0.20, 'AKS005' => 0.85]
            ],
            [
                'name' => 'Jogger Pants Rayon',
                'size' => 'All Size',
                'unit' => 'Pcs',
                'unit_price' => 135000,
                'bom' => ['KDM005' => 2.20, 'BJM003' => 0.30, 'AKS005' => 0.90]
            ],
            [
                'name' => 'Chinno Pants Drill',
                'size' => '30',
                'unit' => 'Pcs',
                'unit_price' => 210000,
                'bom' => ['KDM004' => 2.40, 'BJM002' => 0.45, 'AKS002' => 1, 'AKS001' => 1]
            ],

            // KATEGORI: OUTER & LAINNYA
            [
                'name' => 'Jaket Denim Heavy',
                'size' => 'XL',
                'unit' => 'Pcs',
                'unit_price' => 450000,
                'bom' => ['KDM003' => 3.00, 'BJM002' => 0.70, 'AKS001' => 10]
            ],
            [
                'name' => 'Daster Rayon Bunga',
                'size' => 'All Size',
                'unit' => 'Pcs',
                'unit_price' => 75000,
                'bom' => ['KDM005' => 2.00, 'BJM003' => 0.20]
            ],
            [
                'name' => 'Kimono Linen Sleepwear',
                'size' => 'L',
                'unit' => 'Pcs',
                'unit_price' => 280000,
                'bom' => ['KDM001' => 3.20, 'BJM001' => 0.40]
            ],
            [
                'name' => 'Apron Drill Barista',
                'size' => 'All Size',
                'unit' => 'Pcs',
                'unit_price' => 125000,
                'bom' => ['KDM004' => 1.20, 'BJM002' => 0.25]
            ],
            [
                'name' => 'Tote Bag Canvas Style',
                'size' => 'Standard',
                'unit' => 'Pcs',
                'unit_price' => 45000,
                'bom' => ['KDM003' => 0.60, 'BJM002' => 0.15]
            ],
        ];

        foreach ($products as $p) {
            $product = Product::updateOrCreate(
                ['name' => $p['name'], 'size' => $p['size']],
                ['unit' => $p['unit'], 'unit_price' => $p['unit_price']]
            );

            $syncData = [];
            foreach ($p['bom'] as $code => $qty) {
                if ($materials->has($code)) {
                    $syncData[$materials[$code]->id] = ['quantity' => $qty];
                }
            }

            if (!empty($syncData)) {
                $product->rawMaterials()->sync($syncData);
            }
        }
    }
}