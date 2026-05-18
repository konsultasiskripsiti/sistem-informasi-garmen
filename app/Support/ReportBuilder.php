<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\RawMaterial;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportBuilder
{
    public static function types(): array
    {
        return [
            'raw-material-purchases' => 'Pembelian Raw Material',
            'product-productions' => 'Produksi Product',
            'product-sales' => 'Penjualan Product',
            'raw-material-stocks' => 'Stok Bahan baku',
            'product-stocks' => 'Stok Product',
        ];
    }

    public static function build(Request $request): array
    {
        $reportTypes = self::types();
        $reportType = (string) $request->string('report_type');
        $dateFrom = (string) $request->string('date_from');
        $dateTo = (string) $request->string('date_to');

        if (! array_key_exists($reportType, $reportTypes)) {
            $reportType = '';
        }

        $summary = [];
        $rows = collect();

        $applyDateRange = function ($query, string $column) use ($dateFrom, $dateTo) {
            return $query
                ->when($dateFrom !== '', fn ($dateQuery) => $dateQuery->whereDate($column, '>=', $dateFrom))
                ->when($dateTo !== '', fn ($dateQuery) => $dateQuery->whereDate($column, '<=', $dateTo));
        };

        if ($reportType === 'raw-material-purchases') {
            $purchases = $applyDateRange(
                Purchase::query()->with(['supplier', 'personInCharge', 'items.rawMaterial']),
                'purchase_date'
            )->orderBy('purchase_date')->get();

            $rows = $purchases->flatMap(fn (Purchase $purchase) => $purchase->items->map(fn ($item) => [
                'date' => $purchase->purchase_date->format('d M Y'),
                'supplier' => $purchase->supplier->name,
                'pic' => $purchase->personInCharge->name,
                'raw_material' => $item->rawMaterial->name,
                'quantity' => (float) $item->quantity,
                'unit' => $item->rawMaterial->unit,
                'unit_price' => (int) $item->unit_price,
                'total_price' => (int) $item->total_price,
            ]));

            $summary = [
                'Transaksi' => $purchases->count(),
                'Total Qty' => number_format($rows->sum('quantity'), 2),
                'Total Nilai' => 'Rp'.number_format($rows->sum('total_price'), 0, ',', '.'),
            ];
        } elseif ($reportType === 'product-productions') {
            $productions = $applyDateRange(
                Production::query()->with(['product', 'items.rawMaterial']),
                'production_date'
            )->orderBy('production_date')->get();

            $rows = $productions->map(fn (Production $production) => [
                'date' => $production->production_date->format('d M Y'),
                'product' => $production->product->name,
                'size' => $production->product->size,
                'quantity' => (int) $production->production_quantity,
                'unit' => $production->product->unit,
                'raw_materials' => $production->items
                    ->map(fn ($item) => $item->rawMaterial->name.' ('.number_format((float) $item->quantity_used, 2).' '.$item->unit.')')
                    ->implode(', '),
                'notes' => $production->notes ?: '-',
            ]);

            $summary = [
                'Transaksi' => $productions->count(),
                'Total Produksi' => number_format($rows->sum('quantity')),
                'Product Unik' => $productions->pluck('product_id')->unique()->count(),
            ];
        } elseif ($reportType === 'product-sales') {
            $sales = $applyDateRange(
                Sale::query()->with(['items.product']),
                'sale_date'
            )->orderBy('sale_date')->get();

            $rows = $sales->flatMap(fn (Sale $sale) => $sale->items->map(fn ($item) => [
                'date' => $sale->sale_date->format('d M Y'),
                'invoice_number' => $sale->invoice_number,
                'buyer_name' => $sale->buyer_name,
                'product' => $item->product->name,
                'size' => $item->product->size,
                'quantity' => (int) $item->quantity,
                'unit_price' => (int) $item->unit_price,
                'total_price' => (int) $item->total_price,
            ]));

            $summary = [
                'Transaksi' => $sales->count(),
                'Total Qty' => number_format($rows->sum('quantity')),
                'Total Nilai' => 'Rp'.number_format($rows->sum('total_price'), 0, ',', '.'),
            ];
        } elseif ($reportType === 'raw-material-stocks') {
            $rawMaterials = RawMaterial::query()->orderBy('raw_material_code')->get();

            $rows = $rawMaterials->map(function (RawMaterial $rawMaterial) use ($applyDateRange) {
                $lastOpname = $applyDateRange($rawMaterial->stockOpnames(), 'opname_date')
                    ->latest('opname_date')
                    ->latest()
                    ->first();

                return [
                    'code' => $rawMaterial->raw_material_code,
                    'name' => $rawMaterial->name,
                    'quantity' => (float) $rawMaterial->quantity,
                    'unit' => $rawMaterial->unit,
                    'last_opname_date' => $lastOpname ? $lastOpname->opname_date->format('d M Y H:i') : '-',
                    'last_adjustment' => $lastOpname ? number_format((float) $lastOpname->adjustment_quantity, 2) : '-',
                ];
            });

            $summary = [
                'Raw Material' => $rawMaterials->count(),
                'Total Stok' => number_format($rows->sum('quantity'), 2),
                'Pernah Opname' => $rows->filter(fn ($row) => $row['last_opname_date'] !== '-')->count(),
            ];
        } elseif ($reportType === 'product-stocks') {
            $products = Product::query()->orderBy('name')->orderBy('size')->get();

            $rows = $products->map(function (Product $product) use ($applyDateRange) {
                $lastOpname = $applyDateRange($product->stockOpnames(), 'opname_date')
                    ->latest('opname_date')
                    ->latest()
                    ->first();

                return [
                    'name' => $product->name,
                    'size' => $product->size,
                    'quantity' => (int) $product->stock_quantity,
                    'unit' => $product->unit,
                    'last_opname_date' => $lastOpname ? $lastOpname->opname_date->format('d M Y H:i') : '-',
                    'last_adjustment' => $lastOpname ? number_format((int) $lastOpname->adjustment_quantity) : '-',
                ];
            });

            $summary = [
                'Product' => $products->count(),
                'Total Stok' => number_format($rows->sum('quantity')),
                'Pernah Opname' => $rows->filter(fn ($row) => $row['last_opname_date'] !== '-')->count(),
            ];
        }

        return [
            'reportTypes' => $reportTypes,
            'reportType' => $reportType,
            'reportLabel' => $reportType ? $reportTypes[$reportType] : '',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $rows,
            'summary' => $summary,
            'table' => self::table($reportType, $rows),
        ];
    }

    public static function table(string $reportType, Collection $rows): array
    {
        $columns = match ($reportType) {
            'raw-material-purchases' => [
                'Tanggal' => 'date',
                'Supplier' => 'supplier',
                'PIC' => 'pic',
                'Raw Material' => 'raw_material',
                'Qty' => fn ($row) => number_format($row['quantity'], 2).' '.$row['unit'],
                'Harga' => fn ($row) => 'Rp'.number_format($row['unit_price'], 0, ',', '.'),
                'Total' => fn ($row) => 'Rp'.number_format($row['total_price'], 0, ',', '.'),
            ],
            'product-productions' => [
                'Tanggal' => 'date',
                'Product' => 'product',
                'Size' => 'size',
                'Qty Produksi' => fn ($row) => number_format($row['quantity']).' '.$row['unit'],
                'Bahan Terpakai' => 'raw_materials',
                'Catatan' => 'notes',
            ],
            'product-sales' => [
                'Tanggal' => 'date',
                'Invoice' => 'invoice_number',
                'Buyer' => 'buyer_name',
                'Product' => fn ($row) => $row['product'].' - '.$row['size'],
                'Qty' => fn ($row) => number_format($row['quantity']),
                'Harga' => fn ($row) => 'Rp'.number_format($row['unit_price'], 0, ',', '.'),
                'Total' => fn ($row) => 'Rp'.number_format($row['total_price'], 0, ',', '.'),
            ],
            'raw-material-stocks' => [
                'Code' => 'code',
                'Raw Material' => 'name',
                'Stok' => fn ($row) => number_format($row['quantity'], 2).' '.$row['unit'],
                'Last Opname' => 'last_opname_date',
                'Last Adjustment' => 'last_adjustment',
            ],
            'product-stocks' => [
                'Product' => 'name',
                'Size' => 'size',
                'Stok' => fn ($row) => number_format($row['quantity']).' '.$row['unit'],
                'Last Opname' => 'last_opname_date',
                'Last Adjustment' => 'last_adjustment',
            ],
            default => [],
        };

        return [
            'headers' => array_keys($columns),
            'rows' => $rows->map(fn ($row) => collect($columns)->map(fn ($key) => $key instanceof \Closure ? $key($row) : $row[$key])->all())->values(),
        ];
    }

    public static function filename(array $report, string $extension): string
    {
        $name = $report['reportType'] ?: 'report';

        return $name.'-'.now()->format('Ymd-His').'.'.$extension;
    }

    public static function pdf(array $report): string
    {
        $tableLines = self::pdfTableLines($report['table']['headers'], $report['table']['rows']);
        $pages = array_chunk($tableLines, 30);
        $objects = [];
        $pageIds = [];

        if ($pages === []) {
            $pages = [[]];
        }

        foreach ($pages as $pageIndex => $pageLines) {
            $content = '';
            $y = 555;

            if ($pageIndex === 0) {
                $content .= self::pdfText(40, $y, $report['reportLabel'] ?: 'Laporan', 'F2', 16);
                $y -= 18;
                $content .= self::pdfText(40, $y, 'Periode: '.($report['dateFrom'] ?: 'awal data').' s/d '.($report['dateTo'] ?: 'akhir data'), 'F1', 9);
                $y -= 14;
                $content .= self::pdfText(40, $y, 'Generated: '.now()->format('d M Y H:i'), 'F1', 9);
                $y -= 24;

                $summaryLine = collect($report['summary'])
                    ->map(fn ($value, $label) => $label.': '.$value)
                    ->implode('     ');

                $content .= self::pdfText(40, $y, $summaryLine, 'F2', 9);
                $y -= 24;
            } else {
                $content .= self::pdfText(40, $y, ($report['reportLabel'] ?: 'Laporan').' (lanjutan)', 'F2', 13);
                $y -= 24;
            }

            foreach ($pageLines as $index => $line) {
                $font = $index <= 1 && $pageIndex === 0 ? 'F3' : 'F3';
                $content .= self::pdfText(40, $y, $line, $font, 7);
                $y -= 13;
            }

            $contentId = 6 + ($pageIndex * 2);
            $pageId = $contentId + 1;
            $pageIds[] = $pageId;
            $objects[$contentId] = "<< /Length ".strlen($content)." >>\nstream\n".$content."\nendstream";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R /F2 4 0 R /F3 5 0 R >> >> /Contents {$contentId} 0 R >>";
        }

        $objects[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', array_map(fn ($id) => "{$id} 0 R", $pageIds)).'] /Count '.count($pageIds).' >>';
        $objects[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';
        $objects[5] = '<< /Type /Font /Subtype /Type1 /BaseFont /Courier >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(max(array_keys($objects)) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= max(array_keys($objects)); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        $pdf .= "trailer\n<< /Size ".(max(array_keys($objects)) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private static function pdfEscape(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $value);
    }

    private static function pdfText(int $x, int $y, string $text, string $font, int $size): string
    {
        return "BT\n/{$font} {$size} Tf\n{$x} {$y} Td\n(".self::pdfEscape($text).") Tj\nET\n";
    }

    private static function pdfTableLines(array $headers, Collection $rows): array
    {
        if ($headers === []) {
            return [];
        }

        $columnCount = count($headers);
        $availableChars = 132;
        $width = max(10, intdiv($availableChars - (($columnCount - 1) * 3), $columnCount));
        $widths = array_fill(0, $columnCount, $width);

        $lines = [];
        $lines[] = self::pdfTableRow($headers, $widths);
        $lines[] = implode('-+-', array_map(fn ($columnWidth) => str_repeat('-', $columnWidth), $widths));

        foreach ($rows as $row) {
            $lines[] = self::pdfTableRow($row, $widths);
        }

        return $lines;
    }

    private static function pdfTableRow(array $row, array $widths): string
    {
        return collect($row)
            ->values()
            ->map(fn ($value, $index) => self::pdfPad((string) $value, $widths[$index] ?? 12))
            ->implode(' | ');
    }

    private static function pdfPad(string $value, int $width): string
    {
        $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';
        $value = mb_strimwidth($value, 0, $width, '...');
        $padding = max(0, $width - mb_strwidth($value));

        return $value.str_repeat(' ', $padding);
    }
}
