<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\SalesPeriodService;
use App\Support\AppTimezone;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function __construct(
        private SalesPeriodService $periodService,
        private AppTimezone $timezone
    ) {}

    /**
     * Menampilkan laporan penjualan dengan filter periode.
     * Hanya admin (middleware route).
     */
    public function index(Request $request)
    {
        $filter = $this->resolveReportFilter($request);
        [$sales, $labelPeriode, $rentangLabel] = $this->buildFilteredSales($filter);

        [$totalTransaksi, $totalPendapatan, $totalProdukTerjual] = $this->reportTotals(
            $sales,
            $filter['product_id']
        );
        $products = Product::orderBy('nama_produk')->get();

        $periodeOptions = [
            '' => SalesPeriodService::PERIODE_OPTIONS[''],
            'hari_ini' => SalesPeriodService::PERIODE_OPTIONS['hari_ini'],
            '7_hari' => SalesPeriodService::PERIODE_OPTIONS['7_hari'],
            '1_bulan' => [
                'label' => '1 bulan terakhir',
                'deskripsi' => 'Rolling 1 bulan termasuk hari ini.',
            ],
            'minggu_ini' => SalesPeriodService::PERIODE_OPTIONS['minggu_ini'],
            'bulan_ini' => SalesPeriodService::PERIODE_OPTIONS['bulan_ini'],
            'bulan' => SalesPeriodService::PERIODE_OPTIONS['bulan'],
            'kustom' => SalesPeriodService::PERIODE_OPTIONS['kustom'],
        ];

        return view(
            'reports.index',
            compact(
                'sales',
                'totalTransaksi',
                'totalPendapatan',
                'totalProdukTerjual',
                'filter',
                'labelPeriode',
                'rentangLabel',
                'periodeOptions',
                'products'
            )
        );
    }

    /**
     * Export laporan penjualan ke PDF (filter sama dengan index).
     */
    public function exportPdf(Request $request)
    {
        $filter = $this->resolveReportFilter($request);
        [$sales, $labelPeriode, $rentangLabel] = $this->buildFilteredSales($filter, true);

        [$totalTransaksi, $totalPendapatan, $totalProdukTerjual] = $this->reportTotals(
            $sales,
            $filter['product_id']
        );
        $kosong = $sales->isEmpty();
        $selectedProduct = $filter['product_id']
            ? Product::find($filter['product_id'])
            : null;
        $productLabel = $selectedProduct?->nama_produk ?? 'Semua';
        $isAllProducts = $selectedProduct === null;
        $ringkasanProduk = $isAllProducts
            ? $this->productSalesSummary($sales)
            : collect();

        $namaBulan = $rentangLabel
            ? $labelPeriode.' ('.$rentangLabel.')'
            : $labelPeriode;

        $pdf = Pdf::loadView('reports.export-pdf', compact(
            'sales',
            'totalTransaksi',
            'totalPendapatan',
            'totalProdukTerjual',
            'namaBulan',
            'kosong',
            'productLabel',
            'isAllProducts',
            'ringkasanProduk'
        ));

        $slug = $filter['periode'] ?: 'semua';
        if ($slug === 'bulan' && $filter['bulan']) {
            $slug = $filter['bulan'];
        } elseif ($slug === 'kustom' && $filter['dari'] && $filter['sampai']) {
            $slug = $filter['dari'].'_'.$filter['sampai'];
        }

        return $pdf->download('laporan-penjualan-'.$slug.'.pdf');
    }

    /**
     * Export PDF gabungan: Harian + Mingguan + Bulanan dalam satu file.
     */
    public function exportCombinedPdf()
    {
        $sections = [];

        foreach ([
            'hari_ini' => 'Harian',
            'minggu_ini' => 'Mingguan',
            'bulan_ini' => 'Bulanan',
        ] as $periode => $judul) {
            $filter = ['periode' => $periode, 'bulan' => null, 'dari' => null, 'sampai' => null];
            [$sales, $labelPeriode, $rentangLabel] = $this->buildFilteredSales($filter, true);

            $sections[] = [
                'judul' => $judul,
                'label' => $rentangLabel
                    ? $labelPeriode.' ('.$rentangLabel.')'
                    : $labelPeriode,
                'sales' => $sales,
                'totalTransaksi' => $sales->count(),
                'totalPendapatan' => $sales->sum('total_harga'),
                'totalProdukTerjual' => $this->reportTotals($sales)[2],
                'kosong' => $sales->isEmpty(),
            ];
        }

        $pdf = Pdf::loadView('reports.export-combined-pdf', compact('sections'));

        return $pdf->download('laporan-gabungan-'.$this->timezone->today()->format('Y-m-d').'.pdf');
    }

    private function resolveReportFilter(Request $request): array
    {
        $filter = $this->periodService->resolveFilter($request);

        if (! $request->hasAny(['periode', 'bulan', 'dari', 'sampai'])) {
            $filter['periode'] = '1_bulan';
        }

        $productId = $request->input('product_id');
        $filter['product_id'] = is_scalar($productId)
            && ctype_digit((string) $productId)
            && Product::whereKey((int) $productId)->exists()
                ? (int) $productId
                : null;

        return $filter;
    }

    /**
     * @return array{0: Collection, 1: string, 2: string|null}
     */
    private function buildFilteredSales(array $filter, bool $ordered = false): array
    {
        $query = Sale::query();
        $productId = $filter['product_id'] ?? null;

        if ($productId) {
            $query->whereHas('details', fn ($detailQuery) => $detailQuery
                ->where('product_id', $productId));
        }

        if ($ordered) {
            $query->with([
                'user',
                'details' => function ($detailQuery) use ($productId) {
                    if ($productId) {
                        $detailQuery->where('product_id', $productId);
                    }

                    $detailQuery->with('product');
                },
            ]);
        }

        $this->periodService->applyToQuery($query, $filter);

        $range = $this->periodService->resolveRange($filter);
        $labelPeriode = $range['label'];
        $rentangLabel = $range['rentang'];

        if ($ordered) {
            $sales = $query->orderBy('tanggal')->get();
        } else {
            $sales = $query->latest('tanggal')->get();
        }

        return [$sales, $labelPeriode, $rentangLabel];
    }

    /**
     * @return array{0: int, 1: int|float, 2: int|float}
     */
    private function reportTotals(Collection $sales, ?int $productId = null): array
    {
        if ($sales->isEmpty()) {
            return [0, 0, 0];
        }

        $detailQuery = SaleDetail::whereIn('sale_id', $sales->pluck('id'));

        if ($productId) {
            $detailQuery->where('product_id', $productId);
        }

        $totalPendapatan = $productId
            ? (clone $detailQuery)->sum('subtotal')
            : $sales->sum('total_harga');

        return [
            $sales->count(),
            $totalPendapatan,
            $detailQuery->sum('qty'),
        ];
    }

    private function productSalesSummary(Collection $sales): Collection
    {
        if ($sales->isEmpty()) {
            return collect();
        }

        return SaleDetail::query()
            ->join('products', 'products.id', '=', 'sale_details.product_id')
            ->whereIn('sale_details.sale_id', $sales->pluck('id'))
            ->select('products.id', 'products.nama_produk')
            ->selectRaw('SUM(sale_details.qty) as total_terjual')
            ->groupBy('products.id', 'products.nama_produk')
            ->orderBy('products.nama_produk')
            ->get();
    }
}
