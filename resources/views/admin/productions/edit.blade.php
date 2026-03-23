<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Production</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui transaksi produksi product dari template bahan baku.</p>
            </div>
            <a href="{{ route('productions.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Productions
            </a>
        </div>
    </x-slot>

    @php
        $productsData = $products->map(fn ($product) => [
            'id' => (string) $product->id,
            'name' => $product->name,
            'size' => $product->size,
            'unit' => $product->unit,
            'materials' => $product->rawMaterials->map(fn ($rawMaterial) => [
                'id' => (string) $rawMaterial->id,
                'name' => $rawMaterial->name,
                'unit' => $rawMaterial->unit,
                'bom_quantity' => (float) $rawMaterial->pivot->quantity,
                'stock' => (float) $rawMaterial->quantity,
            ])->values(),
        ])->values();
    @endphp

    <div
        x-data="{
            products: @js($productsData),
            productId: '{{ old('product_id', $production->product_id) }}',
            productionQuantity: '{{ old('production_quantity', $production->production_quantity) }}',
            selectedProduct() {
                return this.products.find((product) => String(product.id) === String(this.productId));
            },
            productionMaterials() {
                const product = this.selectedProduct();
                const quantity = Math.max(parseInt(this.productionQuantity || 0, 10) || 0, 0);

                if (!product) {
                    return [];
                }

                return product.materials.map((material) => {
                    const qty = Number(material.bom_quantity) * quantity;
                    const remaining = Number(material.stock) - qty;

                    return {
                        ...material,
                        quantity_needed: qty,
                        remaining_stock: remaining,
                    };
                });
            },
            formattedNumber(value) {
                return Number(value || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                });
            },
        }"
        class="space-y-6"
    >
        <form method="POST" action="{{ route('productions.update', $production) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Informasi Dasar</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui informasi utama transaksi produksi product.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="production_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Produksi</label>
                        <input id="production_date" name="production_date" type="date" value="{{ old('production_date', $production->production_date->toDateString()) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @error('production_date')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="product_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Product</label>
                        <select id="product_id" name="product_id" x-model="productId" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">Pilih product</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" {{ (string) old('product_id', $production->product_id) === (string) $product->id ? 'selected' : '' }}>{{ $product->name }} - {{ $product->size }}</option>
                            @endforeach
                        </select>
                        @error('product_id')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="production_quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Produksi</label>
                        <input id="production_quantity" name="production_quantity" x-model="productionQuantity" type="number" min="1" step="1" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @error('production_quantity')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2 xl:col-span-1">
                        <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea id="notes" name="notes" rows="1" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('notes', $production->notes) }}</textarea>
                        @error('notes')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Detail Bahan Baku</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Otomatis diambil dari template product pada master data.</p>
                </div>

                <div class="space-y-4" x-show="productionMaterials().length > 0">
                    <template x-for="material in productionMaterials()" :key="material.id">
                        <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                <div>
                                    <p class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Bahan</p>
                                    <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" x-text="material.name"></div>
                                </div>
                                <div>
                                    <p class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</p>
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="material.unit"></div>
                                </div>
                                <div>
                                    <p class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Qty</p>
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300" x-text="formattedNumber(material.quantity_needed)"></div>
                                </div>
                                <div>
                                    <p class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Sisa</p>
                                    <div
                                        class="rounded-xl border px-4 py-3 text-sm font-medium"
                                        :class="material.remaining_stock < 0
                                            ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400'
                                            : 'border-green-200 bg-green-50 text-green-700 dark:border-green-900/30 dark:bg-green-500/10 dark:text-green-400'"
                                        x-text="formattedNumber(material.remaining_stock)"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="productionMaterials().length === 0" class="rounded-2xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-400 dark:border-gray-700">
                    Pilih product terlebih dahulu untuk melihat detail bahan bakunya.
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('productions.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
