<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pantau penjualan, pembelian, produksi, dan posisi stok gudang.</p>
            </div>
            <div class="inline-flex w-fit items-center rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                {{ $periodLabel }}
            </div>
        </div>
    </x-slot>

    @php
        $metricCards = [
            [
                'label' => 'Penjualan Hari Ini',
                'value' => 'Rp'.number_format($metrics['sales_today'], 0, ',', '.'),
                'caption' => 'Total invoice tanggal hari ini',
                'icon_class' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
            ],
            [
                'label' => 'Penjualan Bulan Ini',
                'value' => 'Rp'.number_format($metrics['sales_this_month'], 0, ',', '.'),
                'caption' => 'Akumulasi penjualan bulan berjalan',
                'icon_class' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
            ],
            [
                'label' => 'Pembelian Bulan Ini',
                'value' => 'Rp'.number_format($metrics['purchases_this_month'], 0, ',', '.'),
                'caption' => 'Akumulasi pembelian raw material',
                'icon_class' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            ],
            [
                'label' => 'Produksi Bulan Ini',
                'value' => number_format($metrics['productions_this_month']).' Pcs',
                'caption' => 'Total product masuk stok',
                'icon_class' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
            ],
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($metricCards as $card)
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $card['value'] }}</p>
                    </div>
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ $card['icon_class'] }}">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 19V5M4 19H20M8 16V11M12 16V7M16 16V9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">{{ $card['caption'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Stok Bahan Baku</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total stok dan bahan dengan kuantitas terendah.</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format((float) $metrics['raw_material_stock'], 2) }}</p>
                    <p class="text-xs text-red-500">{{ $metrics['low_raw_materials_count'] }} item <= 10</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($lowRawMaterials as $material)
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <div>
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $material->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $material->raw_material_code }}</p>
                        </div>
                        <p class="font-medium text-gray-700 text-theme-sm dark:text-gray-300">{{ number_format((float) $material->quantity, 2) }} {{ $material->unit }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada raw material.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Stok Product</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Total product siap jual dan stok terendah.</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($metrics['product_stock']) }}</p>
                    <p class="text-xs text-red-500">{{ $metrics['low_products_count'] }} item <= 5</p>
                </div>
            </div>

            <div class="mt-5 space-y-3">
                @forelse ($lowProducts as $product)
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <div>
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Size {{ $product->size }}</p>
                        </div>
                        <p class="font-medium text-gray-700 text-theme-sm dark:text-gray-300">{{ number_format($product->stock_quantity) }} {{ $product->unit }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada product.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Penjualan Terbaru</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[560px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Invoice</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Buyer</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentSales as $sale)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $sale->sale_date->format('d M Y') }}</td>
                                <td class="px-3 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $sale->invoice_number }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $sale->buyer_name }}</td>
                                <td class="px-3 py-3 text-right text-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-400">Belum ada penjualan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pembelian Terbaru</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[560px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Supplier</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Catatan</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPurchases as $purchase)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-3 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $purchase->purchase_date->format('d M Y') }}</td>
                                <td class="px-3 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $purchase->supplier->name }}</td>
                                <td class="px-3 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->notes ?: '-' }}</td>
                                <td class="px-3 py-3 text-right text-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-400">Belum ada pembelian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Top Product Terjual Bulan Ini</h2>
            <div class="mt-4 space-y-3">
                @forelse ($topProducts as $product)
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <div>
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Size {{ $product->size }} | Rp{{ number_format($product->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <p class="font-semibold text-gray-800 dark:text-white/90">{{ number_format($product->total_quantity) }} Pcs</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada penjualan bulan ini.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Pemakaian Bahan Baku Bulan Ini</h2>
            <div class="mt-4 space-y-3">
                @forelse ($rawMaterialUsage as $material)
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $material->name }}</p>
                        <p class="font-semibold text-gray-800 dark:text-white/90">{{ number_format((float) $material->total_quantity, 2) }} {{ $material->unit }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada pemakaian bahan bulan ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
