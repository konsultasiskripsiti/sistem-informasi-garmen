<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Role Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan role dan permission yang dimiliki.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Roles
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $role->name }}</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Role ID: {{ $role->id }}</p>

        <div class="mt-6">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Permissions</h3>
            <div class="flex flex-wrap gap-2">
                @forelse ($role->permissions as $permission)
                    <span class="rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">{{ $permission->name }}</span>
                @empty
                    <span class="text-sm text-gray-400">No permissions assigned.</span>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
