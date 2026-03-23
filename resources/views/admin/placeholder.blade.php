<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-white/[0.03]">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none">
                <path d="M12 8V12L14.5 14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/>
            </svg>
        </div>

        <h2 class="mt-5 text-lg font-semibold text-gray-800 dark:text-white/90">Halaman placeholder</h2>
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
    </div>
</x-app-layout>
