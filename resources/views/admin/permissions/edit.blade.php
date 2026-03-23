<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Edit Permission</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Perbarui nama permission sesuai kebutuhan otorisasi.</p>
            </div>
            <a href="{{ route('permissions.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Permissions
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('permissions.update', $permission) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Permission Name</label>
                <input id="name" name="name" type="text" value="{{ old('name', $permission->name) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                @error('name')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                Guard: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $permission->guard_name }}</span>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('permissions.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
