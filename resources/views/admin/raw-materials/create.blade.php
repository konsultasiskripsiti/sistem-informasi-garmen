<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Create Raw Material</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan bahan baku baru ke master data.</p>
            </div>
            <a href="{{ route('raw-materials.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Raw Materials
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('raw-materials.store') }}" class="space-y-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="raw_material_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Raw Material</label>
                    <input id="raw_material_code" name="raw_material_code" type="text" value="{{ old('raw_material_code', 'KDM001') }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('raw_material_code')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Raw Material</label>
                    <input id="name" name="name" type="text" value="{{ old('name', 'Kain Linen') }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('name')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                    <input type="text" value="0.00" disabled class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                    <p class="mt-2 text-xs text-gray-400">Jumlah akan bertambah dari transaksi pembelian bahan baku.</p>
                </div>

                <div>
                    <label for="unit" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
                    <select id="unit" name="unit" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="Meter" {{ old('unit', 'Meter') === 'Meter' ? 'selected' : '' }}>Meter</option>
                    </select>
                    @error('unit')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                <textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('description', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.') }}</textarea>
                @error('description')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('raw-materials.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Create Raw Material</button>
            </div>
        </form>
    </div>
</x-app-layout>
