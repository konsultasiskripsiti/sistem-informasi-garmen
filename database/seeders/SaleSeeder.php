<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            [
                'invoice_number' => 'INV-20260321-001',
                'sale_date' => '2026-03-21',
                'buyer_name' => 'Made Surya',
                'buyer_phone' => '+628123450001',
                'buyer_address' => 'Jl. Raya Ubud No. 12, Ubud, Gianyar, Bali',
                'notes' => 'Pembelian untuk kebutuhan retail butik.',
                'details' => [
                    ['product_name' => 'Kaos Linen Oversize', 'size' => 'L', 'quantity' => 3],
                    ['product_name' => 'Kemeja Drill Teknisi', 'size' => 'XL', 'quantity' => 2],
                ],
            ],
            [
                'invoice_number' => 'INV-20260321-002',
                'sale_date' => '2026-03-21',
                'buyer_name' => 'Dewi Lestari',
                'buyer_phone' => '+628123450002',
                'buyer_address' => 'Perum Taman Griya Asri Blok C7, Denpasar, Bali',
                'notes' => 'Pembelian campuran untuk reseller online.',
                'details' => [
                    ['product_name' => 'Jogger Pants Rayon', 'size' => 'All Size', 'quantity' => 4],
                    ['product_name' => 'Kaos Linen Oversize', 'size' => 'L', 'quantity' => 2],
                ],
            ],
            [
                'invoice_number' => 'INV-20260322-001',
                'sale_date' => '2026-03-22',
                'buyer_name' => 'Komang Arta',
                'buyer_phone' => '+628123450003',
                'buyer_address' => 'Jl. Gunung Soputan No. 88, Denpasar Barat, Bali',
                'notes' => 'Pembelian satuan untuk customer langsung.',
                'details' => [
                    ['product_name' => 'Jogger Pants Rayon', 'size' => 'All Size', 'quantity' => 2],
                    ['product_name' => 'Kemeja Drill Teknisi', 'size' => 'XL', 'quantity' => 1],
                ],
            ],
        ];

        foreach ($transactions as $transaction) {
            if (Sale::query()->where('invoice_number', $transaction['invoice_number'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($transaction) {
                $items = collect($transaction['details'])->map(function (array $detail) {
                    $product = Product::query()
                        ->where('name', $detail['product_name'])
                        ->where('size', $detail['size'])
                        ->first();

                    if (! $product) {
                        return null;
                    }

                    $quantity = (int) $detail['quantity'];
                    $minimumStock = $quantity + 5;

                    if ((int) $product->stock_quantity < $minimumStock) {
                        $product->update([
                            'stock_quantity' => $minimumStock,
                        ]);
                    }

                    $product->refresh();

                    return [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => (int) $product->unit_price,
                        'total_price' => $quantity * (int) $product->unit_price,
                    ];
                })->filter()->values();

                if ($items->isEmpty()) {
                    return;
                }

                $sale = Sale::create([
                    'invoice_number' => $transaction['invoice_number'],
                    'sale_date' => $transaction['sale_date'],
                    'buyer_name' => $transaction['buyer_name'],
                    'buyer_phone' => $transaction['buyer_phone'],
                    'buyer_address' => $transaction['buyer_address'],
                    'notes' => $transaction['notes'],
                    'total_amount' => $items->sum('total_price'),
                ]);

                $sale->items()->createMany($items->all());

                foreach ($items as $item) {
                    Product::query()
                        ->whereKey($item['product_id'])
                        ->decrement('stock_quantity', $item['quantity']);
                }
            });
        }
    }
}
