<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Create Purchase</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan transaksi pembelian bahan baku baru.</p>
            </div>
            <a href="{{ route('purchases.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Purchases
            </a>
        </div>
    </x-slot>

    @php
        $materials = $rawMaterials->map(fn ($rawMaterial) => [
            'id' => (string) $rawMaterial->id,
            'name' => $rawMaterial->name,
            'unit' => $rawMaterial->unit,
        ])->values();
        $initialDetails = collect(old('details', [['raw_material_id' => '', 'quantity' => '', 'unit_price' => '']]))
            ->map(fn (array $detail) => [
                'raw_material_id' => isset($detail['raw_material_id']) ? (string) $detail['raw_material_id'] : '',
                'quantity' => $detail['quantity'] ?? '',
                'unit_price' => isset($detail['unit_price']) ? (string) $detail['unit_price'] : '',
            ])
            ->values();
    @endphp

    <div
        x-data="{
            materials: @js($materials),
            details: @js($initialDetails),
            init() {
                this.details = this.details.map((detail) => this.normalizeDetail(detail));
            },
            normalizeDetail(detail) {
                const unitPrice = String(detail.unit_price || '').replace(/[^0-9]/g, '');

                return {
                    raw_material_id: String(detail.raw_material_id || ''),
                    quantity: detail.quantity || '',
                    unit_price: unitPrice,
                    unit_price_display: unitPrice ? this.formatCurrency(unitPrice) : '',
                };
            },
            addDetail() {
                this.details.push({ raw_material_id: '', quantity: '', unit_price: '', unit_price_display: '' });
            },
            removeDetail(index) {
                if (this.details.length === 1) {
                    this.details[0] = { raw_material_id: '', quantity: '', unit_price: '', unit_price_display: '' };
                    return;
                }

                this.details.splice(index, 1);
            },
            formatCurrency(value) {
                const digits = String(value || '').replace(/[^0-9]/g, '');
                return digits === '' ? '' : 'Rp ' + Number(digits).toLocaleString('id-ID');
            },
            updateUnitPrice(index, value) {
                const digits = String(value || '').replace(/[^0-9]/g, '');
                this.details[index].unit_price = digits;
                this.details[index].unit_price_display = digits === '' ? '' : this.formatCurrency(digits);
            },
            materialUnit(id) {
                const material = this.materials.find((item) => String(item.id) === String(id));
                return material ? material.unit : '-';
            },
            detailTotal(detail) {
                const quantity = parseFloat(detail.quantity || 0);
                const unitPrice = parseInt(detail.unit_price || 0, 10);

                return Math.round(quantity * unitPrice) || 0;
            },
            grandTotal() {
                return this.details.reduce((sum, detail) => sum + this.detailTotal(detail), 0);
            },
        }"
        class="space-y-6"
    >
        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Informasi Dasar</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lengkapi data utama transaksi pembelian bahan baku.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="purchase_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian</label>
                        <input id="purchase_date" name="purchase_date" type="date" value="{{ old('purchase_date', now()->toDateString()) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @error('purchase_date')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="supplier_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Supplier</label>
                        <select id="supplier_id" name="supplier_id" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">Pilih supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ (string) old('supplier_id') === (string) $supplier->id ? 'selected' : '' }}>{{ $supplier->supplier_code }} - {{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="person_in_charge_id" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Person in Charge</label>
                        <select id="person_in_charge_id" name="person_in_charge_id" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">Pilih PIC</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" {{ (string) old('person_in_charge_id', auth()->id()) === (string) $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('person_in_charge_id')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2 xl:col-span-1">
                        <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                        <textarea id="notes" name="notes" rows="1" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('notes') }}</textarea>
                        @error('notes')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="grid items-start gap-6 lg:grid-cols-3 xl:grid-cols-4">        
                <div class="lg:col-span-2 xl:col-span-3 space-y-6">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Details Bahan Baku</h2>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambah satu atau lebih bahan baku dalam transaksi ini.</p>
                            </div>
                            <button type="button" @click="addDetail()" class="inline-flex items-center justify-center rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/40 dark:text-brand-400 dark:hover:bg-brand-500/10">
                                + Add Field
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(detail, index) in details" :key="index">
                                <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12 items-start">
                                        
                                        <div class="lg:col-span-4">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Bahan Baku</label>
                                            <select :name="`details[${index}][raw_material_id]`" x-model="detail.raw_material_id" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                                <option value="">Pilih bahan baku</option>
                                                <template x-for="material in materials" :key="material.id">
                                                    <option :value="material.id" x-text="material.name"></option>
                                                </template>
                                            </select>
                                            <p class="mt-2 text-xs text-gray-400">Satuan: <span x-text="materialUnit(detail.raw_material_id)"></span></p>
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">QTY</label>
                                            <input :name="`details[${index}][quantity]`" x-model="detail.quantity" type="number" step="0.01" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan</label>
                                            <input type="text" :value="detail.unit_price_display" @input="updateUnitPrice(index, $event.target.value)" placeholder="Rp 0" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                            <input type="hidden" :name="`details[${index}][unit_price]`" :value="detail.unit_price">
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Total Harga</label>
                                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-[13px] xl:text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 truncate" 
                                            :title="formatCurrency(detailTotal(detail))"
                                            x-text="formatCurrency(detailTotal(detail)) || 'Rp 0'">
                                            </div>                                            </div>
                                        <div class="lg:col-span-2">
                                            <label class="mb-2 hidden text-sm font-medium lg:block">&nbsp;</label>
                                            <button type="button" @click="removeDetail(index)" class="w-full rounded-xl border border-red-200 px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10">
                                                Remove
                                            </button>
                                            <p class="mt-2 hidden text-xs lg:block">&nbsp;</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-24 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Ringkasan Pembayaran</h2>
                        
                        <div class="mt-6 rounded-2xl bg-brand-50 px-2 py-8 text-center dark:bg-brand-500/10 overflow-hidden">
                            <p class="text-xs font-medium uppercase tracking-widest text-brand-500">Grand Total</p>
                            
                            <p class="mt-2 text-xl sm:text-2xl font-bold text-brand-700 dark:text-brand-300 truncate px-2" 
                            :title="formatCurrency(grandTotal())"
                            x-text="formatCurrency(grandTotal()) || 'Rp 0'">
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('purchases.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Create Purchase</button>
            </div>
        </form>
    </div>
</x-app-layout>
