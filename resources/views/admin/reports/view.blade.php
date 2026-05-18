<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reportLabel ?: __('app.reports.title') }}</title>
    <style>
        body {
            color: #111827;
            font-family: Arial, sans-serif;
            margin: 32px;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 6px;
        }

        p {
            margin: 0;
        }

        .meta {
            color: #6b7280;
            font-size: 13px;
            margin-bottom: 24px;
        }

        .summary {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, 1fr);
            margin-bottom: 24px;
        }

        .summary-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
        }

        .summary-label {
            color: #6b7280;
            font-size: 12px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
            margin-top: 6px;
        }

        table {
            border-collapse: collapse;
            font-size: 12px;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f9fafb;
            color: #374151;
        }

        @media print {
            body {
                margin: 16px;
            }
        }
    </style>
</head>
<body>
    <h1>{{ $reportLabel ?: __('app.reports.title') }}</h1>
    <p class="meta">{{ __('app.reports.period') }} {{ $dateFrom ?: __('app.reports.start_data') }} - {{ $dateTo ?: __('app.reports.end_data') }}</p>

    <div class="summary">
        @foreach ($summary as $label => $value)
            <div class="summary-item">
                <p class="summary-label">{{ $label }}</p>
                <p class="summary-value">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($table['headers'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($table['rows'] as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ max(count($table['headers']), 1) }}">{{ __('app.common.empty_report') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
