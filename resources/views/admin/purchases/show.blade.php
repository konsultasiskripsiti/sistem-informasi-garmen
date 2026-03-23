<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Purchase Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan transaksi pembelian bahan baku.</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Purchases
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Pembelian</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $purchase->purchase_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Supplier</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $purchase->supplier->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Person In Charge</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $purchase->personInCharge->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Total Harga</p>
                    <p class="mt-1 text-lg font-semibold text-brand-600 dark:text-brand-400">Rp{{ number_format($purchase->total_amount, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-5">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Keterangan</p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $purchase->notes ?: '-' }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Details Bahan Baku</h2>

            <div class="mt-4 grid gap-4">
                @foreach ($purchase->items as $item)
                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                        <div class="grid gap-4 md:grid-cols-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Bahan</p>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $item->rawMaterial->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">QTY</p>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $item->quantity, 2) }} {{ $item->rawMaterial->unit }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Harga Satuan</p>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Total Harga</p>
                                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-white/90">Rp{{ number_format($item->total_price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
