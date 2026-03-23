<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Create Product</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan product baru beserta detail bahan bakunya.</p>
            </div>
            <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Products
            </a>
        </div>
    </x-slot>

    @php
        $materials = $rawMaterials->map(fn ($rawMaterial) => [
            'id' => (string) $rawMaterial->id,
            'name' => $rawMaterial->name,
            'unit' => $rawMaterial->unit,
        ])->values();
        $initialDetails = collect(old('details', [['raw_material_id' => '', 'quantity' => '']]))
            ->map(fn (array $detail) => [
                'raw_material_id' => isset($detail['raw_material_id']) ? (string) $detail['raw_material_id'] : '',
                'quantity' => $detail['quantity'] ?? '',
            ])
            ->values();
    @endphp

    <div
        x-data="{
            materials: @js($materials),
            details: @js($initialDetails),
            displayPrice: '',
            init() {
                this.formatVisiblePrice('{{ old('unit_price') }}');
            },
            addDetail() {
                this.details.push({ raw_material_id: '', quantity: '' });
            },
            removeDetail(index) {
                if (this.details.length === 1) {
                    this.details[0] = { raw_material_id: '', quantity: '' };
                    return;
                }

                this.details.splice(index, 1);
            },
            materialUnit(id) {
                const material = this.materials.find((item) => String(item.id) === String(id));
                return material ? material.unit : '-';
            },
            formatVisiblePrice(value) {
                const digits = String(value || '').replace(/[^0-9]/g, '');
                this.$refs.unitPrice.value = digits;
                this.displayPrice = digits === '' ? '' : 'Rp ' + Number(digits).toLocaleString('id-ID');
            }
        }"
        class="space-y-6"
    >
        <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Informasi Dasar</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Isi data utama product dan harga satuan.</p>
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Product</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        @error('name')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="size" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Pilih Ukuran</label>
                        <select id="size" name="size" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="">Pilih ukuran</option>
                            @foreach ($sizes as $size)
                                <option value="{{ $size }}" {{ old('size') === $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                        @error('size')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-5 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="unit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Satuan</label>
                        <select id="unit" name="unit" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            <option value="Pcs" {{ old('unit', 'Pcs') === 'Pcs' ? 'selected' : '' }}>Pcs</option>
                        </select>
                        @error('unit')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="unit_price_display" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan</label>
                        <input
                            id="unit_price_display"
                            type="text"
                            x-model="displayPrice"
                            @input="formatVisiblePrice($event.target.value)"
                            placeholder="Rp 0"
                            class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                        >
                        <input type="hidden" name="unit_price" x-ref="unitPrice" value="{{ old('unit_price') }}">
                        @error('unit_price')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Details Bahan Baku</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih bahan baku yang menyusun product ini.</p>
                    </div>
                    <button type="button" @click="addDetail()" class="rounded-xl border border-brand-200 px-4 py-2 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-800/40 dark:text-brand-400 dark:hover:bg-brand-500/10">
                        Add Field
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(detail, index) in details" :key="index">
                        <div class="grid gap-4 rounded-2xl border border-gray-200 p-4 dark:border-gray-700 lg:grid-cols-[minmax(0,2fr)_160px_160px_80px]">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Bahan</label>
                                <select :name="`details[${index}][raw_material_id]`" x-model="detail.raw_material_id" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                    <option value="">Pilih bahan baku</option>
                                    <template x-for="material in materials" :key="material.id">
                                        <option
                                            :value="material.id"
                                            :selected="String(detail.raw_material_id) === String(material.id)"
                                            x-text="material.name"
                                        ></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
                                <input type="text" :value="materialUnit(detail.raw_material_id)" readonly class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Qty</label>
                                <input :name="`details[${index}][quantity]`" x-model="detail.quantity" type="number" min="0.01" step="0.01" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                            </div>

                            <div class="flex items-end">
                                <button type="button" @click="removeDetail(index)" class="w-full rounded-xl border border-red-200 px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                @error('details')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
                @error('details.*.raw_material_id')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
                @error('details.*.quantity')<p class="mt-3 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Create Product</button>
            </div>
        </form>
    </div>
</x-app-layout>
