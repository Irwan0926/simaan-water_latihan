<?php

namespace App\Http\Controllers;

use App\Models\AnalysisResult;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Services\RuleBasedService;
use App\Services\SalesPeriodService;
use App\Support\AppTimezone;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private SalesPeriodService $periodService,
        private AppTimezone $timezone
    ) {}

    public function index(Request $request)
    {
        $totalProduk = Product::count();

        $totalPenjualan = (float) Sale::sum('total_harga');
        $totalTransaksi = Sale::count();
        $totalUnitTerjual = (int) SaleDetail::sum('qty');

        $ruleService = new RuleBasedService;

        // Stok menipis = kondisi stok "Sedikit" dari ambang batas per produk (bukan ≤50 hardcode)
        $produkStokMenipis = Product::orderBy('nama_produk')
            ->get()
            ->filter(function ($product) use ($ruleService) {
                try {
                    return $ruleService->getKondisiStok(
                        $product->nama_produk,
                        (int) $product->stok
                    ) === 'Sedikit';
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->sortBy('stok', SORT_NUMERIC)
            ->values();

        $stokMenipis = $produkStokMenipis->count();

        // Snapshot Analisis Rule Based otomatis terbaru (periode harian)
        $snapshotOtomatis = AnalysisResult::otomatis()
            ->where('periode', 'harian')
            ->with('product')
            ->latest()
            ->get()
            ->unique('product_id')
            ->values();

        $produkPerluPromosi = $snapshotOtomatis
            ->filter(fn ($r) => str_contains(strtolower((string) $r->rekomendasi), 'promosi'))
            ->values();

        $produkButuhRestock = $snapshotOtomatis
            ->filter(function ($r) {
                $rek = strtolower((string) $r->rekomendasi);

                return str_contains($rek, 'restock')
                    || str_contains($rek, 'tambah stok');
            })
            ->values();

        $perluPromosi = $produkPerluPromosi->count();
        $butuhRestock = $produkButuhRestock->count();

        /*
         * Tren 7 hari terakhir.
         * Pengelompokan hari memakai fungsi zona waktu bawaan database
         * (CONVERT_TZ / AT TIME ZONE) supaya batas hari mengikuti kalender
         * pengguna, bukan kalender UTC server. Data tetap disimpan UTC.
         */
        $tz = $this->timezone->current();
        $localToday = $this->timezone->today($tz);

        $hari = collect();
        for ($i = 6; $i >= 0; $i--) {
            $hari->push($localToday->subDays($i));
        }

        $rawTrend = Sale::query()
            ->selectRaw($this->timezone->rawLocalDate('tanggal', $tz) . ' as tgl')
            ->selectRaw('SUM(total_harga) as total')
            ->where('tanggal', '>=', $this->timezone->startOfDayUtc($hari->first(), $tz))
            ->where('tanggal', '<=', $this->timezone->endOfDayUtc($localToday, $tz))
            ->groupBy('tgl')
            ->pluck('total', 'tgl');

        $namaHari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        $trendPenjualan = $hari->map(function ($d) use ($rawTrend, $namaHari) {
            $key = $d->toDateString();

            return [
                'label' => $namaHari[(int) $d->format('w')],
                'tanggal' => $d->format('d/m'),
                'total' => (float) ($rawTrend[$key] ?? 0),
            ];
        })->values();

        $trendMax = $trendPenjualan->max('total') ?: 1;
        $trendTotal = $trendPenjualan->sum('total');

        $topProduk = Product::select('products.id', 'products.nama_produk', 'products.stok', 'products.harga')
            ->selectRaw('COALESCE(SUM(sale_details.qty), 0) as terjual')
            ->leftJoin('sale_details', 'sale_details.product_id', '=', 'products.id')
            ->groupBy('products.id', 'products.nama_produk', 'products.stok', 'products.harga')
            ->orderByDesc('terjual')
            ->limit(5)
            ->get();

        $topTerjualMax = $topProduk->max('terjual') ?: 1;

        $transaksiTerakhir = Sale::with('user')
            ->latest('tanggal')
            ->limit(5)
            ->get();

        $stokRendah = $produkStokMenipis->take(5)->values();

        // Hasil penjualan per periode — hanya admin
        $salesFilter = null;
        $salesLabelPeriode = null;
        $salesRentangLabel = null;
        $salesPeriodeOptions = null;
        $salesTotalTransaksi = null;
        $salesTotalPendapatan = null;
        $salesTotalUnit = null;

        if (auth()->check() && auth()->user()->role === 'admin') {
            $salesFilter = $this->periodService->resolveFilter($request);
            // Default: Seluruh periode (periode kosong)

            $range = $this->periodService->resolveRange($salesFilter);
            $salesLabelPeriode = $range['label'];
            $salesRentangLabel = $range['rentang'];
            $salesPeriodeOptions = SalesPeriodService::PERIODE_OPTIONS;

            $salesQuery = Sale::query();
            $this->periodService->applyToQuery($salesQuery, $salesFilter);

            $salesTotalTransaksi = (clone $salesQuery)->count();
            $salesTotalPendapatan = (float) (clone $salesQuery)->sum('total_harga');

            $saleIds = (clone $salesQuery)->pluck('id');
            $salesTotalUnit = $saleIds->isEmpty()
                ? 0
                : (int) SaleDetail::whereIn('sale_id', $saleIds)->sum('qty');
        }

        return view(
            'dashboard',
            compact(
                'totalProduk',
                'totalPenjualan',
                'totalTransaksi',
                'totalUnitTerjual',
                'stokMenipis',
                'perluPromosi',
                'butuhRestock',
                'trendPenjualan',
                'trendMax',
                'trendTotal',
                'topProduk',
                'topTerjualMax',
                'transaksiTerakhir',
                'stokRendah',
                'produkStokMenipis',
                'produkPerluPromosi',
                'produkButuhRestock',
                'salesFilter',
                'salesLabelPeriode',
                'salesRentangLabel',
                'salesPeriodeOptions',
                'salesTotalTransaksi',
                'salesTotalPendapatan',
                'salesTotalUnit'
            )
        );
    }
}
