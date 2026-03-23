<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_code' => 'KDS001',
                'name' => 'Toko Kain A',
                'person_in_charge' => 'Tude',
                'address' => 'Jl. Raya Singapadu, Singapadu, Kec. Sukawati, Kabupaten Gianyar, Bali 80582',
                'phone_number' => '+6287761113645',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS002',
                'name' => 'CV Warna Textile',
                'person_in_charge' => 'Made Putra',
                'address' => 'Jl. Raya Batubulan No. 12, Batubulan, Kec. Sukawati, Kabupaten Gianyar, Bali 80582',
                'phone_number' => '+628123450001',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS003',
                'name' => 'UD Benang Jaya',
                'person_in_charge' => 'Komang Sari',
                'address' => 'Jl. WR Supratman No. 88, Kesiman, Denpasar Timur, Bali 80237',
                'phone_number' => '+628123450002',
                'is_active' => false,
            ],
            [
                'supplier_code' => 'KDS004',
                'name' => 'PT Bahan Bali',
                'person_in_charge' => 'Ketut Arya',
                'address' => 'Jl. Cargo Permai No. 15, Ubung Kaja, Denpasar Utara, Bali 80116',
                'phone_number' => '+628123450003',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS005',
                'name' => 'Bintang Accessories Garmen',
                'person_in_charge' => 'Ibu Laksmi',
                'address' => 'Jl. Gajah Mada No. 45, Denpasar Barat, Bali 80119',
                'phone_number' => '+6281999888005',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS006',
                'name' => 'Guna Print & Bordir',
                'person_in_charge' => 'Agus Perdana',
                'address' => 'Jl. Teuku Umar Barat No. 101, Denpasar, Bali 80113',
                'phone_number' => '+6281777666006',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS007',
                'name' => 'Kalingga Zipper & Button',
                'person_in_charge' => 'Santi Wahyuni',
                'address' => 'Kawasan Industri Gatot Subroto Tengah, Denpasar, Bali 80231',
                'phone_number' => '+6281222333007',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS008',
                'name' => 'CV Bali Silk Utama',
                'person_in_charge' => 'Wayan Juni',
                'address' => 'Jl. Bypass Ngurah Rai No. 500, Sanur, Denpasar Selatan, Bali 80227',
                'phone_number' => '+6285111222008',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS009',
                'name' => 'Toko Label Wovenindo',
                'person_in_charge' => 'Dewo Gede',
                'address' => 'Jl. Imam Bonjol No. 22, Denpasar Barat, Bali 80119',
                'phone_number' => '+6281333444009',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'KDS010',
                'name' => 'Pusat Kain Keras Pelapis',
                'person_in_charge' => 'Sari Dewi',
                'address' => 'Jl. Hasanuddin No. 12, Denpasar, Bali 80112',
                'phone_number' => '+6281555666010',
                'is_active' => false,
            ],
        ];

        foreach ($suppliers as $supplier) {
            $record = Supplier::query()->firstOrNew([
                'supplier_code' => $supplier['supplier_code'], // Menggunakan code sebagai unik identitas lebih aman
            ]);

            $record->fill($supplier);
            $record->save();
        }
    }
}