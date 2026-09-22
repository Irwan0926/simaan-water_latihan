<?php

namespace App\Http\Controllers;

use App\Models\AnalysisResult;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Services\RuleBasedService;
use App\Support\AppTimezone;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function __construct(
        private AppTimezone $timezone
    ) {
    }

    /**
     * =====================================================
     * GENERATE RULE BASE INSIGHT
     * =====================================================
     */

    /**
     * =====================================================
     * FORM GENERATE RULE BASE INSIGHT
     * =====================================================
     */
    public function create()
    {
        $products = \App\Models\Product::whereHas('saleDetails')
            ->orderBy('nama_produk')
            ->get();

        return view(
            'analysis.create',
            compact('products')
        );

    }

    /**
     * =====================================================
     * RESOLVE RENTANG TANGGAL PER PERIODE
     * =====================================================
     * Mengembalikan array berisi tanggal mulai & akhir untuk
     * periode "kini" (current) dan "sebelumnya" (previous).
     * Dipakai baik oleh generate() maupun generateTodayAnalysis
     * agar definisi rentang tanggal konsisten di seluruh sistem.
     *
     * ZONA WAKTU: batas hari mengikuti kalender lokal pengguna, lalu
     * dikonversi ke instan UTC untuk klausa WHERE. Kunci `*_label`
     * berisi tanggal lokal untuk keperluan tampilan.
     */
    private function resolvePeriode(string $periode, ?Carbon $today = null): array
    {
        $tz = $this->timezone->current();

        $localToday = $today
            ? CarbonImmutable::parse($today->format('Y-m-d'), $tz)->startOfDay()
            : $this->timezone->today($tz);

        [$mulaiKini, $akhirKini, $mulaiLalu, $akhirLalu] = match ($periode) {

            // Hari kalender hari ini vs kemarin (00:00–23:59 waktu lokal)
            'harian' => [
                $localToday,
                $localToday,
                $localToday->subDay(),
                $localToday->subDay(),
            ],

            '3hari' => [
                $localToday->subDays(2),
                $localToday,
                $localToday->subDays(5),
                $localToday->subDays(3),
            ],

            'mingguan' => [
                $localToday->subDays(6),
                $localToday,
                $localToday->subDays(13),
                $localToday->subDays(7),
            ],

            default => [
                $localToday->startOfMonth(),
                $localToday->endOfMonth(),
                $localToday->subMonth()->startOfMonth(),
                $localToday->subMonth()->endOfMonth(),
            ],
        };

        return [
            // Batas UTC untuk query.
            'mulai_sekarang' => $this->toUtc($mulaiKini, true, $tz),
            'akhir_sekarang' => $this->toUtc($akhirKini, false, $tz),
            'mulai_sebelumnya' => $this->toUtc($mulaiLalu, true, $tz),
            'akhir_sebelumnya' => $this->toUtc($akhirLalu, false, $tz),

            // Tanggal lokal untuk label tampilan.
            'label_mulai_sekarang' => $this->toLocalCarbon($mulaiKini),
            'label_akhir_sekarang' => $this->toLocalCarbon($akhirKini),
            'label_mulai_sebelumnya' => $this->toLocalCarbon($mulaiLalu),
            'label_akhir_sebelumnya' => $this->toLocalCarbon($akhirLalu),
        ];
    }

    private function toUtc(CarbonImmutable $localDate, bool $startOfDay, string $tz): Carbon
    {
        $instant = $startOfDay
            ? $this->timezone->startOfDayUtc($localDate, $tz)
            : $this->timezone->endOfDayUtc($localDate, $tz);

        return Carbon::instance($instant->toDateTime());
    }

    private function toLocalCarbon(CarbonImmutable $localDate): Carbon
    {
        return Carbon::instance($localDate->toDateTime());
    }

    /**
     * =====================================================
     * FORMAT LABEL RENTANG TANGGAL (Indonesia)
     * =====================================================
     * Contoh output: "13 Jul 2026" atau
     * "13 Jul 2026 – 11 Jul 2026" bila beda bulan/tahun.
     */
    private function formatRentangTgl(Carbon $mulai, Carbon $akhir): string
    {
        if ($mulai->isSameDay($akhir)) {
            return $mulai->translatedFormat('d M Y');
        }

        if ($mulai->format('Y-m') === $akhir->format('Y-m')) {
            return $mulai->format('d') . '–' . $akhir->translatedFormat('d M Y');
        }

        if ($mulai->format('Y') === $akhir->format('Y')) {
            return $mulai->translatedFormat('d M') . ' – ' . $akhir->translatedFormat('d M Y');
        }

        return $mulai->translatedFormat('d M Y') . ' – ' . $akhir->translatedFormat('d M Y');
    }

    /**
     * Alasan klasifikasi kondisi penjualan (untuk pembuktian chart skripsi/UMKM).
     */
    private function alasanKondisiPenjualan(int $qty, string $kondisi, ?array $batas): string
    {
        if (!$batas) {
            return "Qty terjual {$qty} unit diklasifikasikan sebagai {$kondisi} (ambang batas produk belum diatur).";
        }

        $rendah = (int) $batas['rendah'];
        $sedang = (int) $batas['sedang'];

        return match ($kondisi) {
            'Rendah' => "Qty terjual {$qty} unit ≤ ambang rendah ({$rendah}) → kondisi Rendah.",
            'Sedang' => "Qty terjual {$qty} unit di antara " . ($rendah + 1) . "–{$sedang} → kondisi Sedang.",
            'Tinggi' => "Qty terjual {$qty} unit > ambang sedang ({$sedang}) → kondisi Tinggi.",
            default => "Qty terjual {$qty} unit → kondisi {$kondisi}.",
        };
    }

    /**
     * Alasan klasifikasi tren (perbandingan qty periode kini vs sebelumnya).
     */
    private function alasanTren(int $qtyNow, int $qtyPrev, string $tren): string
    {
        $selisih = $qtyNow - $qtyPrev;
        $abs = abs($selisih);

        return match ($tren) {
            'Naik' => "Qty periode ini ({$qtyNow}) > periode sebelumnya ({$qtyPrev}), selisih +{$abs} unit → tren Naik.",
            'Turun' => "Qty periode ini ({$qtyNow}) < periode sebelumnya ({$qtyPrev}), selisih −{$abs} unit → tren Turun.",
            'Stabil' => "Qty periode ini ({$qtyNow}) = periode sebelumnya ({$qtyPrev}) → tren Stabil (hampir sama).",
            default => "Qty {$qtyNow} vs {$qtyPrev} → tren {$tren}.",
        };
    }

    public function generate(Request $request)
    {
        /*
        =====================================================
        VALIDASI INPUT
        =====================================================
        */

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'periode' => 'required|in:harian,3hari,mingguan,bulanan',
        ]);

        /*
        =====================================================
        AMBIL PRODUK DAN PERIODE
        =====================================================
        */

        $product = Product::findOrFail($request->product_id);

        $periode = $request->periode;

        /*
        =====================================================
        MENENTUKAN RENTANG TANGGAL
        =====================================================
        */

        $rentang = $this->resolvePeriode($periode);

        $mulaiSekarang = $rentang['mulai_sekarang'];
        $akhirSekarang = $rentang['akhir_sekarang'];
        $mulaiSebelumnya = $rentang['mulai_sebelumnya'];
        $akhirSebelumnya = $rentang['akhir_sebelumnya'];
        $labelPeriodeSaatIni = $this->formatRentangTgl(
            $rentang['label_mulai_sekarang'],
            $rentang['label_akhir_sekarang']
        );
        $labelPeriodeSebelumnya = $this->formatRentangTgl(
            $rentang['label_mulai_sebelumnya'],
            $rentang['label_akhir_sebelumnya']
        );

        /*
=====================================================
HITUNG PENJUALAN PERIODE SEKARANG
=====================================================
*/

        $penjualanSaatIni = SaleDetail::where(
            'product_id',
            $product->id
        )
            ->whereHas('sale', function ($query) use ($mulaiSekarang, $akhirSekarang) {

                $query->whereBetween(
                    'tanggal',
                    [
                        $mulaiSekarang,
                        $akhirSekarang,
                    ]
                );

            })
            ->sum('qty');

        /*
        =====================================================
        HITUNG PENJUALAN PERIODE SEBELUMNYA
        =====================================================
        */

        $penjualanSebelumnya = SaleDetail::where(
            'product_id',
            $product->id
        )
            ->whereHas('sale', function ($query) use ($mulaiSebelumnya, $akhirSebelumnya) {

                $query->whereBetween(
                    'tanggal',
                    [
                        $mulaiSebelumnya,
                        $akhirSebelumnya,
                    ]
                );

            })
            ->sum('qty');

        /*
        =====================================================
        JALANKAN RULE BASED SYSTEM
        =====================================================
        */

        $service = new RuleBasedService;

        $hasil = $service->analisis(

            $product->nama_produk,

            $penjualanSaatIni,

            $penjualanSebelumnya,

            $product->stok,

            $labelPeriodeSaatIni,

            $labelPeriodeSebelumnya

        );

        /*
        =====================================================
        SIMPAN HASIL ANALISIS MANUAL (riwayat, tidak dihapus)
        =====================================================
        */

        AnalysisResult::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'periode' => $periode,
            'sumber' => AnalysisResult::SUMBER_MANUAL,
            'kondisi_penjualan' => $hasil['kondisi_penjualan'],
            'kondisi_stok' => $hasil['kondisi_stok'],
            'tren' => $hasil['tren'],
            'qty_saat_ini' => $penjualanSaatIni,
            'qty_sebelumnya' => $penjualanSebelumnya,
            'stok_saat_analisis' => $product->stok,
            'rule_terpakai' => $hasil['kode_rule'] ?? '-',
            'rekomendasi' => $hasil['rekomendasi'],
            'inference_trace' => $hasil['inference_trace'],
        ]);

        $hasilGenerate = [
            'produk' => $product->nama_produk,
            'periode' => $periode,
            'penjualan_saat_ini' => $penjualanSaatIni,
            'penjualan_sebelumnya' => $penjualanSebelumnya,
            'stok' => $product->stok,
            'rentang_kini' => [
                'mulai' => $mulaiSekarang,
                'akhir' => $akhirSekarang,
                'label' => $labelPeriodeSaatIni,
            ],
            'rentang_sebelumnya' => [
                'mulai' => $mulaiSebelumnya,
                'akhir' => $akhirSebelumnya,
                'label' => $labelPeriodeSebelumnya,
            ],
            'kondisi_penjualan' => $hasil['kondisi_penjualan'],
            'kondisi_stok' => $hasil['kondisi_stok'],
            'tren' => $hasil['tren'],
            'rule' => $hasil['kode_rule'],
            'rekomendasi' => $hasil['rekomendasi'],
            'trace' => $hasil['inference_trace'],
        ];

        /*
        =====================================================
        KEMBALI KE HALAMAN RULE BASE INSIGHT
        =====================================================
        */

        return redirect()
            ->route('analysis.index')
            ->with(
                'success',
                'Analisis berhasil dibuat.'
            )
            ->with(
                'hasilGenerate',
                $hasilGenerate
            );
    }

    /**
     * =====================================================
     * DASHBOARD RULE BASE INSIGHT
     * =====================================================
     */

    /**
     * =====================================================
     * GENERATE ANALISIS OTOMATIS UNTUK 5 PRODUK (HARI INI)
     * =====================================================
     * Menghitung kondisi penjualan, stok, dan tren untuk
     * seluruh produk berdasarkan penjualan hari ini.
     * Tren membandingkan hari ini vs kemarin.
     * Setiap produk mendapatkan jejak inferensi penuh.
     * Hasil disimpan AnalysisResult dengan periode 'harian'.
     */
    private function generateDailyAnalysisForAllProducts(): \Illuminate\Support\Collection
    {
        // Pakai resolvePeriode('harian') agar rentang full-day (00:00–23:59)
        // konsisten dengan generate manual.
        $rentang = $this->resolvePeriode('harian');
        $mulaiSekarang = $rentang['mulai_sekarang'];
        $akhirSekarang = $rentang['akhir_sekarang'];
        $mulaiSebelumnya = $rentang['mulai_sebelumnya'];
        $akhirSebelumnya = $rentang['akhir_sebelumnya'];
        $labelPeriodeSaatIni = $this->formatRentangTgl(
            $rentang['label_mulai_sekarang'],
            $rentang['label_akhir_sekarang']
        );
        $labelPeriodeSebelumnya = $this->formatRentangTgl(
            $rentang['label_mulai_sebelumnya'],
            $rentang['label_akhir_sebelumnya']
        );

        $service = new RuleBasedService;

        $products = Product::orderBy('nama_produk')->get();

        $todayResults = collect();

        foreach ($products as $product) {

            // Penjualan hari ini
            $penjualanSaatIni = SaleDetail::where('product_id', $product->id)
                ->whereHas('sale', function ($query) use ($mulaiSekarang, $akhirSekarang) {
                    $query->whereBetween('tanggal', [$mulaiSekarang, $akhirSekarang]);
                })
                ->sum('qty');

            // Penjualan kemarin (untuk tren)
            $penjualanSebelumnya = SaleDetail::where('product_id', $product->id)
                ->whereHas('sale', function ($query) use ($mulaiSebelumnya, $akhirSebelumnya) {
                    $query->whereBetween('tanggal', [$mulaiSebelumnya, $akhirSebelumnya]);
                })
                ->sum('qty');

            // Lewati produk yang tidak punya aktivitas sama sekali
            if ($penjualanSaatIni == 0 && $penjualanSebelumnya == 0 && $product->stok == 0) {
                continue;
            }

            $hasil = $service->analisis(
                $product->nama_produk,
                $penjualanSaatIni,
                $penjualanSebelumnya,
                $product->stok,
                $labelPeriodeSaatIni,
                $labelPeriodeSebelumnya
            );

            // Hapus snapshot otomatis lama (riwayat manual tetap disimpan)
            AnalysisResult::where('product_id', $product->id)
                ->where('periode', 'harian')
                ->where('sumber', AnalysisResult::SUMBER_OTOMATIS)
                ->delete();

            $analysis = AnalysisResult::create([
                'product_id' => $product->id,
                'user_id' => null,
                'periode' => 'harian',
                'sumber' => AnalysisResult::SUMBER_OTOMATIS,
                'kondisi_penjualan' => $hasil['kondisi_penjualan'],
                'kondisi_stok' => $hasil['kondisi_stok'],
                'tren' => $hasil['tren'],
                'qty_saat_ini' => $penjualanSaatIni,
                'qty_sebelumnya' => $penjualanSebelumnya,
                'stok_saat_analisis' => $product->stok,
                'rule_terpakai' => $hasil['kode_rule'] ?? '-',
                'rekomendasi' => $hasil['rekomendasi'],
                'inference_trace' => $hasil['inference_trace'],
            ]);

            $analysis->load('product');
            $todayResults->push($analysis);
        }

        return $todayResults;
    }

    /**
     * =====================================================
     * DASHBOARD RULE BASE INSIGHT
     * =====================================================
     */
    public function index()
    {
        /*
        =====================================================
        HASIL GENERATE TERBARU (dari form manual)
        =====================================================
        */

        $hasilGenerate = session('hasilGenerate');

        /*
        =====================================================
        GENERATE ANALISIS OTOMATIS UNTUK 5 PRODUK HARI INI
        =====================================================
        */

        $todayResults = $this->generateDailyAnalysisForAllProducts();

        /*
        =====================================================
        EVALUASI SELURUH RULE PER HASIL ANALISIS HARI INI (FR-8a)
        Dihitung on-the-fly (read-only) dari kondisi tersimpan.
        =====================================================
        */

        $service = new RuleBasedService;

        /*
        =====================================================
        AMBANG BATAS ATURAN (untuk transparansi di UI chart)
        =====================================================
        */

        $aturanPenjualan = $service->getAturanKondisiPenjualan();
        $aturanStok = $service->getAturanKondisiStokDisplay();
        $aturanTren = $service->getAturanTren();

        $ruleEvaluations = [];

        foreach ($todayResults as $result) {

            $fakta = [
                'penjualan' => $result->kondisi_penjualan,
                'stok' => $result->kondisi_stok,
                'tren' => $result->tren,
            ];

            $eval = $service->evaluateAllRules($fakta);
            $ruleTerpakai = $result->rule_terpakai;

            usort($eval, function ($a, $b) use ($ruleTerpakai) {
                $aTerpakai = ($a['kode_rule'] === $ruleTerpakai) ? 0 : 1;
                $bTerpakai = ($b['kode_rule'] === $ruleTerpakai) ? 0 : 1;
                if ($aTerpakai !== $bTerpakai) {
                    return $aTerpakai <=> $bTerpakai;
                }

                return ($b['cocok'] ? 1 : 0) <=> ($a['cocok'] ? 1 : 0);
            });

            $ruleEvaluations[$result->id] = $eval;
        }

        /*
        =====================================================
        AMBIL 20 ATURAN (untuk baris di bawah tabel produk)
        =====================================================
        */

        $rules = \App\Models\Rule::orderBy('id')->get();

        /*
        =====================================================
        DATA PRODUK (untuk form generate)
        =====================================================
        */

        $products = Product::whereHas('saleDetails')
            ->orderBy('nama_produk')
            ->get();

        /*
        =====================================================
        CARD DASHBOARD (Ringkasan Insight)
        =====================================================
        */

        $totalProduk = $todayResults->count();

        $butuhRestock = $todayResults->filter(function ($r) {
            return str_contains(strtolower($r->rekomendasi), 'restock')
                || str_contains(strtolower($r->rekomendasi), 'tambah stok');
        })->count();

        $perluPromosi = $todayResults->filter(function ($r) {
            return str_contains(strtolower($r->rekomendasi), 'promosi');
        })->count();

        /*
        =====================================================
        GRAFIK DISTRIBUSI PENJUALAN
        Berdasarkan aktivitas penjualan dari 5 produk hari ini
        =====================================================
        */

        $penjualanRendah = $todayResults->where('kondisi_penjualan', 'Rendah')->count();
        $penjualanSedang = $todayResults->where('kondisi_penjualan', 'Sedang')->count();
        $penjualanTinggi = $todayResults->where('kondisi_penjualan', 'Tinggi')->count();

        /*
        =====================================================
        GRAFIK TREN PENJUALAN
        =====================================================
        */

        $trenNaik = $todayResults->where('tren', 'Naik')->count();
        $trenStabil = $todayResults->where('tren', 'Stabil')->count();
        $trenTurun = $todayResults->where('tren', 'Turun')->count();

        $rentangRingkasan = $this->resolvePeriode('harian');
        $labelRentangKini = $this->formatRentangTgl(
            $rentangRingkasan['label_mulai_sekarang'],
            $rentangRingkasan['label_akhir_sekarang']
        );
        $labelRentangSebelum = $this->formatRentangTgl(
            $rentangRingkasan['label_mulai_sebelumnya'],
            $rentangRingkasan['label_akhir_sebelumnya']
        );

        /*
        =====================================================
        PEMBUKTIAN CHART (untuk skripsi & transparansi UMKM)
        =====================================================
        Menjelaskan per produk: angka qty, ambang batas, dan
        alasan kondisi penjualan / tren naik-turun-stabil.
        */

        $buktiChart = $todayResults->map(function ($result) use ($aturanPenjualan) {
            $nama = $result->product->nama_produk ?? '-';
            $qtyNow = (int) ($result->qty_saat_ini ?? 0);
            $qtyPrev = (int) ($result->qty_sebelumnya ?? 0);
            $batas = $aturanPenjualan[$nama] ?? null;

            $alasanPenjualan = $this->alasanKondisiPenjualan(
                $qtyNow,
                $result->kondisi_penjualan,
                $batas
            );

            $alasanTren = $this->alasanTren($qtyNow, $qtyPrev, $result->tren);

            return [
                'produk' => $nama,
                'qty_saat_ini' => $qtyNow,
                'qty_sebelumnya' => $qtyPrev,
                'selisih' => $qtyNow - $qtyPrev,
                'batas_rendah' => $batas['rendah'] ?? null,
                'batas_sedang' => $batas['sedang'] ?? null,
                'batas_tinggi' => $batas['tinggi'] ?? null,
                'kondisi_penjualan' => $result->kondisi_penjualan,
                'tren' => $result->tren,
                'alasan_penjualan' => $alasanPenjualan,
                'alasan_tren' => $alasanTren,
            ];
        })->values();

        $historyCount = AnalysisResult::manual()->count();

        return view(
            'analysis.index',
            compact(
                'hasilGenerate',
                'todayResults',
                'rules',
                'products',
                'ruleEvaluations',
                'totalProduk',
                'butuhRestock',
                'perluPromosi',
                'penjualanRendah',
                'penjualanSedang',
                'penjualanTinggi',
                'trenNaik',
                'trenStabil',
                'trenTurun',
                'aturanPenjualan',
                'aturanStok',
                'aturanTren',
                'labelRentangKini',
                'labelRentangSebelum',
                'buktiChart',
                'historyCount'
            )
        );
    }

    /**
     * Halaman riwayat saran AI (generate manual admin).
     */
    public function history()
    {
        $historyResults = AnalysisResult::manual()
            ->with(['product', 'user'])
            ->latest()
            ->paginate(15);

        $service = new RuleBasedService;
        $ruleEvaluations = [];

        foreach ($historyResults as $result) {
            $fakta = [
                'penjualan' => $result->kondisi_penjualan,
                'stok' => $result->kondisi_stok,
                'tren' => $result->tren,
            ];

            $eval = $service->evaluateAllRules($fakta);
            $ruleTerpakai = $result->rule_terpakai;

            usort($eval, function ($a, $b) use ($ruleTerpakai) {
                $aTerpakai = ($a['kode_rule'] === $ruleTerpakai) ? 0 : 1;
                $bTerpakai = ($b['kode_rule'] === $ruleTerpakai) ? 0 : 1;
                if ($aTerpakai !== $bTerpakai) {
                    return $aTerpakai <=> $bTerpakai;
                }

                return ($b['cocok'] ? 1 : 0) <=> ($a['cocok'] ? 1 : 0);
            });

            $ruleEvaluations[$result->id] = $eval;
        }

        $totalRiwayat = AnalysisResult::manual()->count();

        // "Hari ini" mengikuti kalender pengguna: batas UTC dihitung dari
        // awal & akhir hari lokal, bukan DATE(created_at) versi UTC.
        $tz = $this->timezone->current();
        $localToday = $this->timezone->today($tz);

        $hariIni = AnalysisResult::manual()
            ->whereBetween('created_at', [
                $this->timezone->startOfDayUtc($localToday, $tz),
                $this->timezone->endOfDayUtc($localToday, $tz),
            ])
            ->count();

        $produkUnik = AnalysisResult::manual()->distinct('product_id')->count('product_id');

        return view('analysis.history', compact(
            'historyResults',
            'ruleEvaluations',
            'totalRiwayat',
            'hariIni',
            'produkUnik'
        ));
    }
}
