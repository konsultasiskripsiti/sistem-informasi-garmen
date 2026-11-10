<?php

namespace App\Support;

use App\Models\Product;
use App\Models\Production;
use App\Models\Purchase;
use App\Models\RawMaterial;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use Dompdf\Canvas;
use Dompdf\FontMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ReportBuilder
{
    public static function types(): array
    {
        return [
            'raw-material-purchases' => __('app.reports.types.raw-material-purchases'),
            'product-productions' => __('app.reports.types.product-productions'),
            'product-sales' => __('app.reports.types.product-sales'),
            'raw-material-stocks' => __('app.reports.types.raw-material-stocks'),
            'product-stocks' => __('app.reports.types.product-stocks'),
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

    public static function periodLabel(array $report): string
    {
        $format = fn (string $date, string $fallback) => $date === ''
            ? $fallback
            : rescue(fn () => Carbon::parse($date)->translatedFormat('d F Y'), $date, false);

        return $format($report['dateFrom'], __('app.reports.start_data'))
            .' - '
            .$format($report['dateTo'], __('app.reports.end_data'));
    }

    public static function pdf(array $report, string $printedBy): DomPdf
    {
        $title = $report['reportLabel'] ?: __('app.reports.title');
        $period = self::periodLabel($report);

        $pdf = Pdf::loadView('admin.reports.pdf', [
            ...$report,
            'title' => $title,
            'period' => $period,
            'printedBy' => $printedBy,
            'printedAt' => now(),
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isFontSubsettingEnabled', true);

        // Footer digambar lewat canvas karena dompdf tidak mendukung counter(pages).
        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_script(function (int $page, int $total, Canvas $canvas, FontMetrics $metrics) use ($title, $period) {
            $font = $metrics->getFont('DejaVu Sans');
            $size = 7;
            $color = [0.42, 0.45, 0.5];
            $left = 24;
            $right = $canvas->get_width() - 24;
            $y = $canvas->get_height() - 26;
            $pageText = __('app.reports.page_of', ['page' => $page, 'total' => $total]);

            $canvas->line($left, $y - 6, $right, $y - 6, [0.9, 0.91, 0.92], 0.75);
            $canvas->text($left, $y, $title.' · '.$period, $font, $size, $color);
            $canvas->text($right - $metrics->getTextWidth($pageText, $font, $size), $y, $pageText, $font, $size, $color);
        });

        return $pdf;
    }
}
