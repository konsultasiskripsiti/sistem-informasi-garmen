<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.title') }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.dashboard.subtitle') }}</p>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div>
                    <label for="date_from" class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.from') }}</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:w-40">
                </div>
                <div>
                    <label for="date_to" class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.to') }}</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 sm:w-40">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">{{ __('app.actions.filter') }}</button>
                    <a href="{{ route('dashboard') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">{{ __('app.actions.reset') }}</a>
                </div>
            </form>
        </div>
    </x-slot>

    @php
        $metricCards = [
            [
                'label' => __('app.dashboard.sales_today'),
                'value' => 'Rp'.number_format($metrics['sales_today'], 0, ',', '.'),
                'caption' => __('app.dashboard.sales_today_caption'),
                'icon_class' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400',
            ],
            [
                'label' => __('app.dashboard.sales_period'),
                'value' => 'Rp'.number_format($metrics['sales_in_period'], 0, ',', '.'),
                'caption' => __('app.dashboard.sales_period_caption'),
                'icon_class' => 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400',
            ],
            [
                'label' => __('app.dashboard.purchases_period'),
                'value' => 'Rp'.number_format($metrics['purchases_in_period'], 0, ',', '.'),
                'caption' => __('app.dashboard.purchases_period_caption'),
                'icon_class' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
            ],
            [
                'label' => __('app.dashboard.productions_period'),
                'value' => number_format($metrics['productions_in_period']).' Pcs',
                'caption' => __('app.dashboard.productions_period_caption'),
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
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.raw_material_stock') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.dashboard.raw_material_stock_caption') }}</p>
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
                    <p class="text-sm text-gray-400">{{ __('app.dashboard.no_raw_materials') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.product_stock') }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.dashboard.product_stock_caption') }}</p>
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
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.common.size') }} {{ $product->size }}</p>
                        </div>
                        <p class="font-medium text-gray-700 text-theme-sm dark:text-gray-300">{{ number_format($product->stock_quantity) }} {{ $product->unit }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('app.dashboard.no_products') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.recent_sales') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[560px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.date') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.invoice') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.buyer') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.total') }}</th>
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
                            <tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-400">{{ __('app.dashboard.no_sales') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.recent_purchases') }}</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[560px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.date') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.supplier') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.notes') }}</th>
                            <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.total') }}</th>
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
                            <tr><td colspan="4" class="px-3 py-8 text-center text-sm text-gray-400">{{ __('app.dashboard.no_purchases') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.top_products') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $periodLabel }}</p>
            <div class="mt-4 space-y-3">
                @forelse ($topProducts as $product)
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <div>
                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $product->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.common.size') }} {{ $product->size }} | Rp{{ number_format($product->total_amount, 0, ',', '.') }}</p>
                        </div>
                        <p class="font-semibold text-gray-800 dark:text-white/90">{{ number_format($product->total_quantity) }} Pcs</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('app.dashboard.no_period_sales') }}</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.dashboard.raw_material_usage') }}</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $periodLabel }}</p>
            <div class="mt-4 space-y-3">
                @forelse ($rawMaterialUsage as $material)
                    <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3 dark:bg-gray-800">
                        <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $material->name }}</p>
                        <p class="font-semibold text-gray-800 dark:text-white/90">{{ number_format((float) $material->total_quantity, 2) }} {{ $material->unit }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">{{ __('app.dashboard.no_period_usage') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
