<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">User Detail</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ringkasan data user yang dipilih.</p>
            </div>
            <a href="{{ route('users.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex items-start gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-xl font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">User ID: {{ $user->id }}</p>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Roles</h3>
            <div class="flex flex-wrap gap-2">
                @forelse ($user->roles as $role)
                    <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-sm font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">{{ $role->name }}</span>
                @empty
                    <span class="text-sm text-gray-400">No roles assigned.</span>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
