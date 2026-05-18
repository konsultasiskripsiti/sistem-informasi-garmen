<x-app-layout>
    @php
        $sortUrl = function (string $column) use ($sort, $direction) {
            return route('permissions.index', array_merge(request()->query(), [
                'sort' => $column,
                'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
            ]));
        };

        $sortIcon = function (string $column) use ($sort, $direction) {
            if ($sort !== $column) {
                return '↕';
            }

            return $direction === 'asc' ? '↑' : '↓';
        };
    @endphp
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Permissions</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Daftar permission dengan pencarian, pagination, dan aksi massal.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Total: {{ $permissions->total() }} permissions
                </div>
                <a href="{{ route('permissions.create') }}" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    Add Permission
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/30 dark:bg-green-500/10 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    <div x-data="{
        selected: [],
        toggleAll(event) {
            this.selected = event.target.checked
                ? Array.from(this.$root.querySelectorAll('input[data-bulk-item=\'permission\']')).map((input) => input.value)
                : [];
        },
        syncSelection(event) {
            if (event.target.checked) {
                if (!this.selected.includes(event.target.value)) {
                    this.selected.push(event.target.value);
                }
                return;
            }

            this.selected = this.selected.filter((id) => id !== event.target.value);
        },
        submitBulkDelete() {
            if (this.selected.length === 0) {
                return;
            }

            if (confirm(`Delete ${this.selected.length} selected permission(s)?`)) {
                this.$refs.bulkDeleteForm.submit();
            }
        }
    }">
        <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] lg:flex-row lg:items-center lg:justify-between">
            <form method="GET" action="{{ route('permissions.index') }}" class="flex w-full max-w-xl flex-col gap-3 sm:flex-row sm:items-center">
                <div class="relative w-full">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by permission or guard..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-12 pr-4 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                    >
                </div>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                    Search
                </button>
                @if ($search !== '')
                    <a href="{{ route('permissions.index') }}" class="rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                        Reset
                    </a>
                @endif
            </form>

            <form
                x-ref="bulkDeleteForm"
                method="POST"
                action="{{ route('permissions.bulk-destroy') }}"
                class="flex items-center justify-end"
            >
                @csrf
                @method('DELETE')

                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="permission_ids[]" :value="id">
                </template>

                <button
                    type="button"
                    @click="submitBulkDelete()"
                    :disabled="selected.length === 0"
                    class="rounded-xl border border-red-200 px-4 py-3 text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10"
                >
                    Bulk Delete
                </button>
            </form>
        </div>

        <div class="grid gap-4 lg:hidden">
            @forelse ($permissions as $permission)
                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-start gap-3">
                        <input
                            type="checkbox"
                            data-bulk-item="permission"
                            value="{{ $permission->id }}"
                            @change="syncSelection($event)"
                            :checked="selected.includes('{{ $permission->id }}')"
                            class="mt-1 h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                        >
                        <div class="min-w-0 flex-1">
                            <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $permission->name }}</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Guard: {{ $permission->guard_name }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('permissions.show', $permission) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">View</a>
                        <a href="{{ route('permissions.edit', $permission) }}" class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Edit</a>
                        <form method="POST" action="{{ route('permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this permission?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center rounded-lg border border-red-200 px-3 py-2 text-sm text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-400 dark:border-gray-800 dark:bg-white/[0.03]">
                    Belum ada permission.
                </div>
            @endforelse
        </div>

        <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] lg:block">
            <div class="max-w-full overflow-x-auto custom-scrollbar">
                <table class="w-full text-left border-collapse">    
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left sm:px-6">
                                <input type="checkbox" @change="toggleAll($event)" class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <a href="{{ $sortUrl('name') }}" class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Permission {{ $sortIcon('name') }}</a>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <a href="{{ $sortUrl('guard_name') }}" class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Guard {{ $sortIcon('guard_name') }}</a>
                            </th>
                            <th class="px-5 py-3 text-left sm:px-6">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Actions</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4 sm:px-6">
                                    <input
                                        type="checkbox"
                                        data-bulk-item="permission"
                                        value="{{ $permission->id }}"
                                        @change="syncSelection($event)"
                                        :checked="selected.includes('{{ $permission->id }}')"
                                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500"
                                    >
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 text-sm font-semibold text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                            {{ strtoupper(substr($permission->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="block font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $permission->name }}
                                            </span>
                                            <span class="block text-gray-500 text-theme-xs dark:text-gray-400">
                                                ID: {{ $permission->id }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                        {{ $permission->guard_name }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('permissions.show', $permission) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300" title="View">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M2 12S5.63636 5 12 5s10 7 10 7-3.6364 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('permissions.edit', $permission) }}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-600 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300" title="Edit">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M14.06 4.94L17.81 8.69" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('permissions.destroy', $permission) }}" onsubmit="return confirm('Delete this permission?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 text-red-600 transition hover:bg-red-50 dark:border-red-900/30 dark:text-red-400 dark:hover:bg-red-500/10" title="Delete">
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                    <path d="M3 6H21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M8 6V4.8C8 4.11994 8.11929 3.7799 8.38388 3.53531C8.64847 3.29071 9.01523 3.2 9.75 3.2H14.25C14.9848 3.2 15.3515 3.29071 15.6161 3.53531C15.8807 3.7799 16 4.11994 16 4.8V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M18 6V18.2C18 19.3201 18 19.8802 17.782 20.308C17.5903 20.6843 17.2843 20.9903 16.908 21.182C16.4802 21.4 15.9201 21.4 14.8 21.4H9.2C8.0799 21.4 7.51984 21.4 7.09202 21.182C6.71569 20.9903 6.40973 20.6843 6.21799 20.308C6 19.8802 6 19.3201 6 18.2V6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M10 10.5V16.5M14 10.5V16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-gray-400 sm:px-6">
                                    Belum ada permission.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-white/[0.03] md:flex-row md:items-center md:justify-between">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ $permissions->firstItem() ?? 0 }} to {{ $permissions->lastItem() ?? 0 }} of {{ $permissions->total() }} permissions
            </div>
            <div>
                {{ $permissions->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
