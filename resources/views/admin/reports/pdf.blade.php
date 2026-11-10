@php
    // Kolom angka dirata kanan; kolom tanggal/kode tidak dipotong ke baris baru.
    $numericHeaders = ['Qty', 'Qty Produksi', 'Harga', 'Total', 'Stok', 'Last Adjustment'];
    $nowrapHeaders = ['Tanggal', 'Invoice', 'Code', 'Last Opname'];

    $columnClasses = collect($table['headers'])
        ->map(fn ($header) => trim(
            (in_array($header, $numericHeaders, true) ? 'num ' : '')
            .(in_array($header, $nowrapHeaders, true) ? 'nowrap' : '')
        ))
        ->all();

    $summaryCount = max(count($summary), 1);
    $summaryGap = 1.5;
    $summaryWidth = (100 - ($summaryCount - 1) * $summaryGap) / $summaryCount;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 32px 32px 56px;
        }

        body {
            color: #1f2937;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.4;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .header {
            border-bottom: 2px solid #465fff;
            margin-bottom: 14px;
        }

        .header td {
            padding-bottom: 10px;
            vertical-align: bottom;
        }

        .title {
            color: #111827;
            font-size: 17px;
            font-weight: bold;
        }

        .subtitle {
            color: #4b5563;
            font-size: 10px;
            margin-top: 3px;
        }

        .meta {
            color: #6b7280;
            font-size: 8.5px;
            text-align: right;
        }

        .summary {
            margin-bottom: 16px;
        }

        .summary .card {
            background: #f5f7ff;
            border: 1px solid #dfe5ff;
            padding: 8px 10px;
            vertical-align: top;
        }

        .summary-label {
            color: #6b7280;
            font-size: 8px;
            text-transform: uppercase;
        }

        .summary-value {
            color: #111827;
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        .data th {
            background: #465fff;
            border: 1px solid #465fff;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            padding: 6px;
            text-align: left;
        }

        .data td {
            border: 1px solid #e5e7eb;
            padding: 5px 6px;
            vertical-align: top;
        }

        .data tbody tr:nth-child(even) td {
            background: #f9fafb;
        }

        .data tr {
            page-break-inside: avoid;
        }

        .data .no {
            color: #6b7280;
            text-align: center;
            width: 22px;
        }

        .data th.no {
            color: #ffffff;
        }

        .num {
            text-align: right !important;
            white-space: nowrap;
        }

        .nowrap {
            white-space: nowrap;
        }

        .empty {
            color: #9ca3af;
            padding: 18px !important;
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="title">{{ $title }}</div>
                <div class="subtitle">{{ __('app.reports.period') }}: {{ $period }}</div>
            </td>
            <td class="meta">
                {{ __('app.reports.printed_by') }}: {{ $printedBy }}<br>
                {{ __('app.reports.printed_at') }}: {{ $printedAt->translatedFormat('d F Y H:i') }}
            </td>
        </tr>
    </table>

    @if (count($summary) > 0)
        <table class="summary">
            <tr>
                @foreach ($summary as $label => $value)
                    <td class="card" style="width: {{ $summaryWidth }}%;">
                        <div class="summary-label">{{ $label }}</div>
                        <div class="summary-value">{{ $value }}</div>
                    </td>
                    @unless ($loop->last)
                        <td style="width: {{ $summaryGap }}%;"></td>
                    @endunless
                @endforeach
            </tr>
        </table>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th class="no">No</th>
                @foreach ($table['headers'] as $index => $header)
                    <th class="{{ $columnClasses[$index] }}">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($table['rows'] as $row)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    @foreach (array_values($row) as $index => $value)
                        <td class="{{ $columnClasses[$index] ?? '' }}">{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ count($table['headers']) + 1 }}">{{ __('app.common.empty_report') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
