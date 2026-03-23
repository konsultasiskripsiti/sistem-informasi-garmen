<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Production;
use App\Models\RawMaterial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactions = [
            [
                'production_date' => '2026-03-18',
                'product_name' => 'Kaos Linen Oversize',
                'size' => 'L',
                'production_quantity' => 12,
                'notes' => 'Produksi batch awal untuk stok display toko.',
            ],
            [
                'production_date' => '2026-03-19',
                'product_name' => 'Kemeja Drill Teknisi',
                'size' => 'XL',
                'production_quantity' => 8,
                'notes' => 'Produksi seragam pesanan workshop teknisi.',
            ],
            [
                'production_date' => '2026-03-20',
                'product_name' => 'Jogger Pants Rayon',
                'size' => 'All Size',
                'production_quantity' => 10,
                'notes' => 'Produksi tambahan untuk koleksi santai mingguan.',
            ],
        ];

        foreach ($transactions as $transaction) {
            $product = Product::query()
                ->with('rawMaterials')
                ->where('name', $transaction['product_name'])
                ->where('size', $transaction['size'])
                ->first();

            if (! $product || $product->rawMaterials->isEmpty()) {
                continue;
            }

            $production = Production::query()->firstOrNew([
                'production_date' => $transaction['production_date'],
                'product_id' => $product->id,
            ]);

            if ($production->exists) {
                continue;
            }

            $productionQuantity = (int) $transaction['production_quantity'];

            DB::transaction(function () use ($product, $production, $productionQuantity, $transaction) {
                $items = $product->rawMaterials->map(function (RawMaterial $rawMaterial) use ($productionQuantity) {
                    $quantityUsed = round((float) $rawMaterial->pivot->quantity * $productionQuantity, 2);
                    $minimumStock = $quantityUsed + 25;

                    if ((float) $rawMaterial->quantity < $minimumStock) {
                        $rawMaterial->update([
                            'quantity' => $minimumStock,
                        ]);
                    }

                    $stockBefore = round((float) $rawMaterial->fresh()->quantity, 2);
                    $stockAfter = round($stockBefore - $quantityUsed, 2);

                    return [
                        'raw_material_id' => $rawMaterial->id,
                        'unit' => $rawMaterial->unit,
                        'quantity_used' => $quantityUsed,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                    ];
                });

                $production->fill([
                    'production_quantity' => $productionQuantity,
                    'notes' => $transaction['notes'],
                ])->save();

                $production->items()->createMany($items->all());

                foreach ($items as $item) {
                    RawMaterial::query()
                        ->whereKey($item['raw_material_id'])
                        ->decrement('quantity', $item['quantity_used']);
                }

                $product->increment('stock_quantity', $productionQuantity);
            });
        }
    }
}
