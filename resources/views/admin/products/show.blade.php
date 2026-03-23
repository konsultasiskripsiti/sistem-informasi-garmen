<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Product Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan data product dan bahan bakunya.</p>
            </div>
            <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start gap-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-xl font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                    {{ strtoupper(substr($product->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ukuran: {{ $product->size }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Satuan: {{ $product->unit }}</p>
                    <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan: Rp{{ number_format($product->unit_price, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Detail Bahan Baku</h3>
            <div class="mt-4 grid gap-4">
                @forelse ($product->rawMaterials as $rawMaterial)
                    <div class="grid gap-4 rounded-2xl border border-gray-200 p-4 dark:border-gray-700 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Nama Bahan</p>
                            <p class="mt-1 text-sm font-medium text-gray-800 dark:text-white/90">{{ $rawMaterial->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Satuan</p>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $rawMaterial->unit }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Qty</p>
                            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $rawMaterial->pivot->quantity, 2) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">Belum ada bahan baku terpasang.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
