<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Sale Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan transaksi penjualan product.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sales.edit', $sale) }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    Edit
                </a>
                <form method="POST" action="{{ route('sales.destroy', $sale) }}" onsubmit="return confirm('Delete this sale?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10">
                        Delete
                    </button>
                </form>
                <a href="{{ route('sales.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    Back to Sales
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">No Invoice</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $sale->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Penjualan</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $sale->sale_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Total Harga</p>
                    <p class="mt-1 text-lg font-semibold text-brand-600 dark:text-brand-400">Rp{{ number_format($sale->total_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Pembeli</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $sale->buyer_name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nomor Telp</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $sale->buyer_phone ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Keterangan</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $sale->notes ?: '-' }}</p>
                </div>
            </div>

            <div class="mt-5">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Alamat Pembeli</p>
                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $sale->buyer_address ?: '-' }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Detail Penjualan</h2>

            <div class="mt-4 grid gap-4">
                @foreach ($sale->items as $item)
                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Product</p>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $item->product->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Qty</p>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ number_format($item->quantity) }}</p>
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
