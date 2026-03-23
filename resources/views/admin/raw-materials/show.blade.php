<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Raw Material Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan data bahan baku yang dipilih.</p>
            </div>
            <a href="{{ route('raw-materials.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Raw Materials
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-xl font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                {{ strtoupper(substr($rawMaterial->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $rawMaterial->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kode Raw Material: {{ $rawMaterial->raw_material_code }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Raw Material ID: {{ $rawMaterial->id }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2">
            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quantity</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $rawMaterial->quantity, 2) }} {{ $rawMaterial->unit }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Satuan</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $rawMaterial->unit }}</p>
            </div>
        </div>

        <div class="mt-5 rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Keterangan</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $rawMaterial->description ?: '-' }}</p>
        </div>
    </div>
</x-app-layout>
