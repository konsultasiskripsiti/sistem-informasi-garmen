<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ __('app.reports.title') }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.reports.subtitle') }}</p>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-6">
            <div class="grid gap-5 lg:grid-cols-3">
                <div class="lg:col-span-1">
                    <label for="report_type" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.reports.type') }}</label>
                    <select id="report_type" name="report_type" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="">{{ __('app.reports.choose_type') }}</option>
                        @foreach ($reportTypes as $value => $label)
                            <option value="{{ $value }}" {{ $reportType === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.reports.period_from') }}</label>
                    <input id="date_from" name="date_from" type="date" value="{{ $dateFrom }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                </div>

                <div>
                    <label for="date_to" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.reports.period_to') }}</label>
                    <input id="date_to" name="date_to" type="date" value="{{ $dateTo }}" class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-700 outline-none transition focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('reports.index') }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                    {{ __('app.actions.reset') }}
                </a>
                <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600">
                    {{ __('app.reports.show') }}
                </button>
            </div>
        </form>
    </div>

    @if ($reportType)
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ($summary as $label => $value)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $reportTypes[$reportType] }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('app.reports.period') }} {{ $dateFrom ?: __('app.reports.start_data') }} - {{ $dateTo ?: __('app.reports.end_data') }}
                    </p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('reports.view', request()->query()) }}" target="_blank" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                        {{ __('app.actions.view') }}
                    </a>
                    <a href="{{ route('reports.download.csv', request()->query()) }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                        {{ __('app.actions.download_csv') }}
                    </a>
                    <a href="{{ route('reports.download.pdf', request()->query()) }}" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-300">
                        {{ __('app.actions.download_pdf') }}
                    </a>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    @if ($reportType === 'raw-material-purchases')
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.date') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Supplier</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">PIC</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Raw Material</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.price') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['date'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['supplier'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['pic'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $row['raw_material'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ number_format($row['quantity'], 2) }} {{ $row['unit'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($row['total_price'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">{{ __('app.common.empty_report') }}</td></tr>
                            @endforelse
                        </tbody>
                    @elseif ($reportType === 'product-productions')
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.date') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Product</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Size</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Qty Produksi</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Bahan Terpakai</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['date'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $row['product'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['size'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ number_format($row['quantity']) }} {{ $row['unit'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['raw_materials'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['notes'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">{{ __('app.common.empty_report') }}</td></tr>
                            @endforelse
                        </tbody>
                    @elseif ($reportType === 'product-sales')
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.date') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Invoice</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Buyer</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Product</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.price') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['date'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['invoice_number'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['buyer_name'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $row['product'] }} - {{ $row['size'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ number_format($row['quantity']) }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">Rp{{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">Rp{{ number_format($row['total_price'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-10 text-center text-gray-400">{{ __('app.common.empty_report') }}</td></tr>
                            @endforelse
                        </tbody>
                    @elseif ($reportType === 'raw-material-stocks')
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Code</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Raw Material</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.stock') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Last Opname</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Last Adjustment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['code'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $row['name'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ number_format($row['quantity'], 2) }} {{ $row['unit'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['last_opname_date'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['last_adjustment'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">{{ __('app.common.empty_report') }}</td></tr>
                            @endforelse
                        </tbody>
                    @elseif ($reportType === 'product-stocks')
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Product</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.size') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.common.stock') }}</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Last Opname</th>
                                <th class="px-5 py-3 text-left text-theme-xs font-medium text-gray-500 dark:text-gray-400">Last Adjustment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-4 text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $row['name'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ $row['size'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-700 dark:text-gray-300">{{ number_format($row['quantity']) }} {{ $row['unit'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['last_opname_date'] }}</td>
                                    <td class="px-5 py-4 text-theme-sm text-gray-500 dark:text-gray-400">{{ $row['last_adjustment'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">{{ __('app.common.empty_report') }}</td></tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    @else
        <div class="mt-6 rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ __('app.reports.choose_parameters') }}</h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('app.reports.choose_parameters_hint') }}</p>
        </div>
    @endif
</x-app-layout>
