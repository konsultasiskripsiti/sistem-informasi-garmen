<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Supplier Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan data supplier yang dipilih.</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Suppliers
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-xl font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                {{ strtoupper(substr($supplier->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $supplier->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kode Supplier: {{ $supplier->supplier_code }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Person In Charge: {{ $supplier->person_in_charge }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Supplier ID: {{ $supplier->id }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Alamat</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $supplier->address }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kontak</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $supplier->phone_number }}</p>
            </div>
        </div>

        <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</h3>
            <span class="{{ $supplier->is_active ? 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }} inline-flex rounded-full px-3 py-1 text-sm font-medium">
                {{ $supplier->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
    </div>
</x-app-layout>
