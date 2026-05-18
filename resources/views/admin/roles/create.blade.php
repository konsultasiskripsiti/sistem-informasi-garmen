<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Create Role</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan role baru dan pilih permission yang diberikan.</p>
            </div>
            <a href="{{ route('roles.index') }}" class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                Back to Roles
            </a>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                @error('name')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Permissions</p>
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 dark:border-gray-700">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" {{ in_array($permission->name, old('permissions', []), true) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('permissions')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
                @error('permissions.*')<p class="mt-2 text-sm text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Create Role</button>
            </div>
        </form>
    </div>
</x-app-layout>
