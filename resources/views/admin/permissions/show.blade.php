<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Permission Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan permission yang dipilih.</p>
            </div>
            <a href="{{ route('permissions.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Permissions
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $permission->name }}</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Guard: {{ $permission->guard_name }}</p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Permission ID: {{ $permission->id }}</p>
    </div>
</x-app-layout>
