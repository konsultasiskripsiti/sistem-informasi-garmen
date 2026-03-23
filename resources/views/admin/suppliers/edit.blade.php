<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Supplier</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui data supplier yang dipilih.</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Suppliers
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="supplier_code" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kode Supplier</label>
                    <input id="supplier_code" name="supplier_code" type="text" value="{{ old('supplier_code', $supplier->supplier_code) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('supplier_code')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Supplier</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $supplier->name) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('name')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="person_in_charge" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Person In Charge</label>
                <input id="person_in_charge" name="person_in_charge" type="text" value="{{ old('person_in_charge', $supplier->person_in_charge) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                @error('person_in_charge')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="address" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Alamat</label>
                <textarea id="address" name="address" rows="4" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('address', $supplier->address) }}</textarea>
                @error('address')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label for="phone_number" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
                    <input id="phone_number" name="phone_number" type="text" value="{{ old('phone_number', $supplier->phone_number) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    @error('phone_number')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="is_active" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <select id="is_active" name="is_active" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="1" {{ (string) old('is_active', (int) $supplier->is_active) === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ (string) old('is_active', (int) $supplier->is_active) === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('is_active')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('suppliers.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
