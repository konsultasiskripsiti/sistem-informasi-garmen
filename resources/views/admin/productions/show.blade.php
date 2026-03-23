<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Production Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan transaksi produksi product dan pemakaian bahan bakunya.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('productions.edit', $production) }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    Edit
                </a>
                <form method="POST" action="{{ route('productions.destroy', $production) }}" onsubmit="return confirm('Delete this production?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-xl border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10">
                        Delete
                    </button>
                </form>
                <a href="{{ route('productions.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    Back to Productions
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Tanggal Produksi</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $production->production_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Product</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $production->product->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Jumlah Produksi</p>
                    <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($production->production_quantity) }} {{ $production->product->unit }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Keterangan</p>
                    <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $production->notes ?: '-' }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Detail Bahan Baku</h2>

            <div class="mt-4 grid gap-4">
                @foreach ($production->items as $item)
                    <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Bahan</p>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $item->rawMaterial->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Satuan</p>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $item->unit }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Qty</p>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $item->quantity_used, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Stok Sebelum</p>
                                <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $item->stock_before, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Sisa</p>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format((float) $item->stock_after, 2) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
