<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Stok Opname Product</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hitung stok fisik product dan simpan koreksi stok dengan histori adjustment.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('products.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    Master Product
                </a>
                <a href="{{ route('stock-opname.products.create') }}" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    Input Opname
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-900/30 dark:bg-green-500/10 dark:text-green-400">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/30 dark:bg-red-500/10 dark:text-red-400">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="mb-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Product</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($summary['products_count']) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Stok Sistem</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($summary['total_stock']) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Opname</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($summary['adjustment_count']) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-sm text-gray-500 dark:text-gray-400">Terakhir Opname</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ $summary['last_opname_date'] ? \Illuminate\Support\Carbon::parse($summary['last_opname_date'])->format('d M Y H:i') : '-' }}
            </p>
        </div>
    </div>

    <div
        x-data="{
            modalOpen: {{ old('product_id') ? 'true' : 'false' }},
            selected: {
                id: '{{ old('product_id') }}',
                name: '',
                size: '',
                unit: '',
                quantity: '{{ old('system_quantity') }}'
            },
            openOpname(product) {
                this.selected = product;
                this.modalOpen = true;
                this.$nextTick(() => this.$refs.physicalQuantity?.focus());
            }
        }"
    >
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" action="{{ route('stock-opname.products') }}" class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full max-w-xl">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" fill="currentColor"/>
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, size, or unit..." class="w-full rounded-xl border border-gray-200 bg-white py-3 pl-12 pr-4 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('stock-opname.products') }}" class="rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div>
                <div class="grid gap-4 lg:hidden">
                    @forelse ($products as $product)
                        @php $lastOpname = $product->stockOpnames->first(); @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ $product->size }}</p>
                            <h3 class="mt-1 text-base font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</h3>
                            <div class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                                <p><span class="font-medium text-gray-700 dark:text-gray-300">Stok Sistem:</span> {{ number_format($product->stock_quantity) }} {{ $product->unit }}</p>
                                <p><span class="font-medium text-gray-700 dark:text-gray-300">Opname Terakhir:</span> {{ $lastOpname ? $lastOpname->opname_date->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-gray-400 dark:border-gray-800 dark:bg-white/[0.03]">Belum ada product.</div>
                    @endforelse
                </div>

                <div class="hidden overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] lg:block">
                    <div class="max-w-full overflow-x-auto">
                        <table class="w-full min-w-[900px]">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Product</p></th>
                                    <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Size</p></th>
                                    <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Stok Sistem</p></th>
                                    <th class="px-5 py-3 text-left sm:px-6"><p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Last Opname</p></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    @php $lastOpname = $product->stockOpnames->first(); @endphp
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-5 py-4 sm:px-6"><p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $product->name }}</p></td>
                                        <td class="px-5 py-4 sm:px-6"><span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">{{ $product->size }}</span></td>
                                        <td class="px-5 py-4 sm:px-6">
                                            <p class="font-medium text-gray-700 text-theme-sm dark:text-gray-300">{{ number_format($product->stock_quantity) }}</p>
                                            <p class="text-gray-500 text-theme-xs dark:text-gray-400">{{ $product->unit }}</p>
                                        </td>
                                        <td class="px-5 py-4 sm:px-6">
                                            @if ($lastOpname)
                                                <p class="text-gray-700 text-theme-sm dark:text-gray-300">{{ $lastOpname->opname_date->format('d M Y H:i') }}</p>
                                                <p class="{{ $lastOpname->adjustment_quantity >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} text-theme-xs">Selisih {{ number_format($lastOpname->adjustment_quantity) }}</p>
                                            @else
                                                <p class="text-gray-400 text-theme-sm">Belum pernah</p>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-5 py-10 text-center text-gray-400 sm:px-6">Belum ada product.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-white/[0.03] md:flex-row md:items-center md:justify-between">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products</div>
                    <div>{{ $products->onEachSide(1)->links() }}</div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Histori Terbaru</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($recentOpnames as $opname)
                        <div class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0 dark:border-gray-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $opname->product->name }} - {{ $opname->product->size }}</p>
                                    <p class="mt-1 text-gray-500 text-theme-xs dark:text-gray-400">{{ $opname->opname_date->format('d M Y H:i') }} oleh {{ $opname->personInCharge->name }}</p>
                                </div>
                                <span class="{{ $opname->adjustment_quantity >= 0 ? 'bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400' }} rounded-full px-2.5 py-1 text-xs font-medium">
                                    {{ $opname->adjustment_quantity >= 0 ? '+' : '' }}{{ number_format($opname->adjustment_quantity) }}
                                </span>
                            </div>
                            <p class="mt-2 text-gray-500 text-theme-xs dark:text-gray-400">Sistem {{ number_format($opname->system_quantity) }} ke fisik {{ number_format($opname->physical_quantity) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">Belum ada histori stok opname.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
            <div @click.outside="modalOpen = false" class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-gray-400" x-text="selected.size"></p>
                        <h2 class="mt-1 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="selected.name || 'Input Opname'"></h2>
                    </div>
                    <button type="button" @click="modalOpen = false" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"><path d="M6 6L18 18M18 6L6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('stock-opname.products.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" :value="selected.id">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="opname_date" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Opname</label>
                            <input id="opname_date" name="opname_date" type="datetime-local" value="{{ old('opname_date', now()->format('Y-m-d\TH:i')) }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Sistem</label>
                            <input type="text" :value="`${selected.quantity || '0'} ${selected.unit || ''}`" readonly class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        </div>
                    </div>

                    <div>
                        <label for="physical_quantity" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stok Fisik</label>
                        <input x-ref="physicalQuantity" id="physical_quantity" name="physical_quantity" type="number" step="1" min="0" value="{{ old('physical_quantity') }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    </div>

                    <div>
                        <label for="notes" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                        <textarea id="notes" name="notes" rows="3" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="modalOpen = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">Simpan Opname</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
