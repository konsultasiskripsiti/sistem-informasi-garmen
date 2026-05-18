<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Input Stok Opname Product</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catat hasil hitung fisik product dan koreksi stok sistem.</p>
            </div>
            <a href="{{ route('stock-opname.products') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Stock Opname
            </a>
        </div>
    </x-slot>

    @php
        $productOptions = $products->map(fn ($product) => [
            'id' => (string) $product->id,
            'name' => $product->name,
            'size' => $product->size,
            'unit' => $product->unit,
            'quantity' => (int) $product->stock_quantity,
        ])->values();

        $initialDetails = collect(old('details', [['product_id' => '', 'physical_quantity' => '']]))
            ->map(fn (array $detail) => [
                'product_id' => isset($detail['product_id']) ? (string) $detail['product_id'] : '',
                'physical_quantity' => $detail['physical_quantity'] ?? '',
            ])
            ->values();
    @endphp

    <div
        x-data="{
            products: @js($productOptions),
            details: @js($initialDetails),
            addDetail() {
                this.details.push({ product_id: '', physical_quantity: '' });
            },
            loadAllProducts() {
                this.details = this.products.map((product) => ({
                    product_id: product.id,
                    physical_quantity: product.quantity,
                }));
            },
            removeDetail(index) {
                if (this.details.length === 1) {
                    this.details[0] = { product_id: '', physical_quantity: '' };
                    return;
                }

                this.details.splice(index, 1);
            },
            productData(id) {
                return this.products.find((item) => String(item.id) === String(id));
            },
            productUnit(id) {
                return this.productData(id)?.unit || '-';
            },
            systemQuantity(id) {
                return Number(this.productData(id)?.quantity || 0);
            },
            adjustment(detail) {
                if (!detail.product_id || detail.physical_quantity === '') {
                    return 0;
                }

                return Number(detail.physical_quantity || 0) - this.systemQuantity(detail.product_id);
            },
            formatNumber(value) {
                return Number(value || 0).toLocaleString('id-ID');
            },
            totalAdjustment() {
                return this.details.reduce((sum, detail) => sum + this.adjustment(detail), 0);
            },
        }"
        class="space-y-6"
    >
        <form method="POST" action="{{ route('stock-opname.products.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Informasi Opname</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tanggal dan catatan akan dipakai untuk semua item pada form ini.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label for="opname_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Opname</label>
                        <input id="opname_date" name="opname_date" type="datetime-local" value="{{ old('opname_date', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @error('opname_date')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        <textarea id="notes" name="notes" rows="1" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200" placeholder="Contoh: opname akhir bulan, koreksi gudang, product rusak.">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-3 xl:grid-cols-4">
                <div class="space-y-6 lg:col-span-2 xl:col-span-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Detail Product</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan product yang sudah dihitung stok fisiknya.</p>
                            </div>
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <button type="button" @click="loadAllProducts()" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                                    Get All Product
                                </button>
                                <button type="button" @click="addDetail()" class="inline-flex items-center justify-center rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/40 dark:text-brand-400 dark:hover:bg-brand-500/10">
                                    + Add Field
                                </button>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(detail, index) in details" :key="index">
                                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                                        <div class="lg:col-span-4">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Product</label>
                                            <select :name="`details[${index}][product_id]`" x-model="detail.product_id" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                                <option value="">Pilih product</option>
                                                <template x-for="product in products" :key="product.id">
                                                    <option :value="product.id" x-text="`${product.name} - ${product.size}`"></option>
                                                </template>
                                            </select>
                                            <p class="mt-2 text-xs text-gray-400">Satuan: <span x-text="productUnit(detail.product_id)"></span></p>
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Sistem</label>
                                            <input type="text" :value="formatNumber(systemQuantity(detail.product_id))" readonly class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Fisik</label>
                                            <input :name="`details[${index}][physical_quantity]`" x-model="detail.physical_quantity" type="number" step="1" min="0" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Selisih</label>
                                            <div
                                                class="rounded-xl border px-4 py-3 text-sm font-medium"
                                                :class="adjustment(detail) < 0
                                                    ? 'border-red-200 bg-red-50 text-red-600 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400'
                                                    : 'border-green-200 bg-green-50 text-green-600 dark:border-green-900/30 dark:bg-green-500/10 dark:text-green-400'"
                                                x-text="`${adjustment(detail) >= 0 ? '+' : ''}${formatNumber(adjustment(detail))}`"
                                            ></div>
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 hidden text-sm font-medium lg:block">&nbsp;</label>
                                            <button type="button" @click="removeDetail(index)" class="w-full rounded-xl border border-red-200 px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10">
                                                Remove
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        @error('details')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
                        @error('details.*.product_id')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
                        @error('details.*.physical_quantity')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ringkasan</h2>

                        <div class="mt-5 space-y-4">
                            <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-800">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Item</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90" x-text="details.length"></p>
                            </div>

                            <div class="rounded-2xl bg-brand-50 p-4 dark:bg-brand-500/10">
                                <p class="text-sm text-brand-600 dark:text-brand-400">Total Selisih</p>
                                <p
                                    class="mt-1 text-2xl font-semibold"
                                    :class="totalAdjustment() < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                                    x-text="`${totalAdjustment() >= 0 ? '+' : ''}${formatNumber(totalAdjustment())}`"
                                ></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('stock-opname.products') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Simpan Opname</button>
            </div>
        </form>
    </div>
</x-app-layout>
