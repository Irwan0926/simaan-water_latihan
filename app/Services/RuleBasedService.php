<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductThreshold;
use App\Models\Rule;

class RuleBasedService
{
    public function getAturanKondisiPenjualan(): array
    {
        $result = [];

        foreach (Product::orderBy('nama_produk')->get() as $product) {
            $thresholds = $this->thresholds($product->id, ProductThreshold::TIPE_PENJUALAN);

            if (!isset($thresholds[1], $thresholds[2])) {
                continue;
            }

            $result[$product->nama_produk] = [
                'rendah' => $thresholds[1],
                'sedang' => $thresholds[2],
                'tinggi' => '> ' . $thresholds[2],
            ];
        }

        return $result;
    }

    public function getAturanKondisiStok(): array
    {
        $result = [];

        foreach (Product::orderBy('nama_produk')->get() as $product) {
            $thresholds = $this->thresholds($product->id, ProductThreshold::TIPE_STOK);

            if (!isset($thresholds[1], $thresholds[2])) {
                continue;
            }

            $result[$product->nama_produk] = [
                'sedikit' => $thresholds[1],
                'aman' => $thresholds[2],
                'banyak' => '> ' . $thresholds[2],
            ];
        }

        return $result;
    }

    /**
     * Aturan tren (perbandingan sederhana, sumber: PLAN.md).
     * Tidak memakai persentase — cukup membandingkan penjualan
     * periode ini vs periode sebelumnya.
     */
    public function getAturanTren(): array
    {
        return [
            'naik' => [
                'label' => 'Penjualan periode ini lebih besar dari periode sebelumnya',
            ],
            'stabil' => [
                'label' => 'Penjualan hampir sama',
            ],
            'turun' => [
                'label' => 'Penjualan periode ini lebih kecil dari periode sebelumnya',
            ],
        ];
    }

    /**
     * Ambang batas tampilan untuk kondisi stok per produk (label + batas).
     * Dipakai view "Info lanjutan" agar label mengikuti data PLAN.md.
     */
    public function getAturanKondisiStokDisplay(): array
    {
        $result = [];

        foreach (Product::orderBy('nama_produk')->get() as $product) {
            $rows = ProductThreshold::where('product_id', $product->id)
                ->where('tipe', ProductThreshold::TIPE_STOK)
                ->orderBy('level')
                ->get();

            if ($rows->count() < 3) {
                continue;
            }

            $result[$product->nama_produk] = $rows->map(fn($r) => [
                'label' => $r->label,
                'batas' => $r->batas,
                'level' => $r->level,
            ])->values()->all();
        }

        return $result;
    }

    public function getKondisiPenjualan(string $produk, int $jumlahPenjualan): string
    {
        $thresholds = $this->getAturanKondisiPenjualan()[$produk] ?? null;

        if (!$thresholds) {
            throw new \Exception("Produk '{$produk}' belum memiliki aturan kondisi penjualan.");
        }

        if ($jumlahPenjualan <= $thresholds['rendah']) {
            return 'Rendah';
        }

        if ($jumlahPenjualan <= $thresholds['sedang']) {
            return 'Sedang';
        }

        return 'Tinggi';
    }

    public function getKondisiStok(string $produk, int $stok): string
    {
        $thresholds = $this->getAturanKondisiStok()[$produk] ?? null;

        if (!$thresholds) {
            throw new \Exception("Produk '{$produk}' belum memiliki aturan kondisi stok.");
        }

        if ($stok <= $thresholds['sedikit']) {
            return 'Sedikit';
        }

        if ($stok <= $thresholds['aman']) {
            return 'Aman';
        }

        return 'Banyak';
    }

    /**
     * Tentukan tren dengan membandingkan penjualan periode ini vs sebelumnya.
     * Aturan (PLAN.md): Naik = lebih besar, Stabil = hampir sama, Turun = lebih kecil.
     * Persentase tetap dihitung untuk keperluan tampilan saja.
     */
    public function getTren(int $penjualanSaatIni, int $penjualanSebelumnya): array
    {
        if ($penjualanSebelumnya == 0) {
            $persentase = $penjualanSaatIni > 0 ? 100.0 : 0.0;
        } else {
            $persentase = (($penjualanSaatIni - $penjualanSebelumnya) / $penjualanSebelumnya) * 100;
        }

        if ($penjualanSaatIni > $penjualanSebelumnya) {
            return ['tren' => 'Naik', 'persentase' => $persentase];
        }

        if ($penjualanSaatIni < $penjualanSebelumnya) {
            return ['tren' => 'Turun', 'persentase' => $persentase];
        }

        return ['tren' => 'Stabil', 'persentase' => $persentase];
    }

    /**
     * Evaluasi seluruh rule (untuk tabel di modal).
     */
    public function evaluateAllRules(array $fakta): array
    {
        $rows = [];

        foreach (Rule::active()->orderBy('kode_rule')->orderBy('id')->get() as $rule) {
            // Premis wajib spesifik — null tidak lagi berarti "apa saja"
            $penjualanMatch = $rule->penjualan !== null && $rule->penjualan === $fakta['penjualan'];
            $stokMatch = $rule->stok !== null && $rule->stok === $fakta['stok'];
            $trenMatch = $rule->tren !== null && $rule->tren === $fakta['tren'];
            $cocok = $penjualanMatch && $stokMatch && $trenMatch;

            $alasans = [];
            if (!$penjualanMatch) {
                $alasans[] = "Penjualan tidak cocok (fakta: {$fakta['penjualan']}, aturan: " . ($rule->penjualan ?? 'kosong') . ')';
            }
            if (!$stokMatch) {
                $alasans[] = "Stok tidak cocok (fakta: {$fakta['stok']}, aturan: " . ($rule->stok ?? 'kosong') . ')';
            }
            if (!$trenMatch) {
                $alasans[] = "Tren tidak cocok (fakta: {$fakta['tren']}, aturan: " . ($rule->tren ?? 'kosong') . ')';
            }

            $rows[] = [
                'kode_rule' => $rule->kode_rule,
                'penjualan' => $rule->penjualan,
                'stok' => $rule->stok,
                'tren' => $rule->tren,
                'rekomendasi' => $rule->rekomendasi,
                'kategori' => $rule->kategori,
                'cocok' => $cocok,
                'alasan' => $alasans,
            ];
        }

        return $rows;
    }

    /**
     * Forward Chaining — First Match (urut kode rule).
     * Premis penjualan, stok, dan tren wajib spesifik (tidak ada wildcard null).
     */
    public function forwardChaining(array $fakta): array
    {
        $log = [];
        $rules = Rule::active()->orderBy('kode_rule')->orderBy('id')->get();
        $langkah = 0;

        $log[] = 'Metode: Forward Chaining';
        $log[] = 'Strategi: First Match (aturan pertama yang cocok dipilih)';
        $log[] = 'Urutan cek: kode rule menaik';
        $log[] = 'Premis: penjualan, stok, tren harus cocok persis (tanpa “apa saja”)';
        $log[] = '';

        foreach ($rules as $rule) {
            $langkah++;
            $log[] = '------------------------------------------------';
            $log[] = "Langkah {$langkah}: memeriksa aturan {$rule->kode_rule}";
            $log[] = '  JIKA penjualan = ' . ($rule->penjualan ?? '(kosong)');
            $log[] = '  DAN stok = ' . ($rule->stok ?? '(kosong)');
            $log[] = '  DAN tren = ' . ($rule->tren ?? '(kosong)');
            $log[] = "  MAKA saran = {$rule->rekomendasi}";
            $log[] = '';

            $penjualanMatch = $rule->penjualan !== null && $rule->penjualan === $fakta['penjualan'];
            $stokMatch = $rule->stok !== null && $rule->stok === $fakta['stok'];
            $trenMatch = $rule->tren !== null && $rule->tren === $fakta['tren'];

            $log[] = '  Cek penjualan : ' . ($penjualanMatch ? 'cocok' : 'tidak cocok') .
                " (data: {$fakta['penjualan']})";
            $log[] = '  Cek stok      : ' . ($stokMatch ? 'cocok' : 'tidak cocok') .
                " (data: {$fakta['stok']})";
            $log[] = '  Cek tren      : ' . ($trenMatch ? 'cocok' : 'tidak cocok') .
                " (data: {$fakta['tren']})";

            if ($penjualanMatch && $stokMatch && $trenMatch) {
                $log[] = '';
                $log[] = "  HASIL: aturan {$rule->kode_rule} COCOK → dijalankan (FIRE)";
                $log[] = '  Forward Chaining berhenti (First Match).';
                $log[] = "  Saran akhir: {$rule->rekomendasi}";

                return [
                    'rule' => $rule,
                    'log' => $log,
                    'langkah' => $langkah,
                    'total_dicek' => $langkah,
                ];
            }

            $log[] = '  HASIL: dilewati, lanjut ke aturan berikutnya.';
            $log[] = '';
        }

        $log[] = '========================================';
        $log[] = 'Tidak ada aturan yang cocok dengan data.';
        $log[] = '========================================';

        return [
            'rule' => null,
            'log' => $log,
            'langkah' => $langkah,
            'total_dicek' => $langkah,
        ];
    }

    public function analisis(
        string $produk,
        int $penjualanSaatIni,
        int $penjualanSebelumnya,
        int $stokSaatIni,
        ?string $labelPeriodeSaatIni = null,
        ?string $labelPeriodeSebelumnya = null
    ): array {
        $kondisiPenjualan = $this->getKondisiPenjualan($produk, $penjualanSaatIni);
        $kondisiStok = $this->getKondisiStok($produk, $stokSaatIni);
        $hasilTren = $this->getTren($penjualanSaatIni, $penjualanSebelumnya);
        $tren = $hasilTren['tren'];
        $persentaseTren = $hasilTren['persentase'];
        $persentaseFormatted = number_format($persentaseTren, 2) . '%';

        $fakta = [
            'penjualan' => $kondisiPenjualan,
            'stok' => $kondisiStok,
            'tren' => $tren,
        ];

        $hasilForward = $this->forwardChaining($fakta);
        $rule = $hasilForward['rule'];
        $log = $hasilForward['log'];
        $rekomendasi = $rule?->rekomendasi ?? 'Belum ada saran yang cocok. Periksa data penjualan atau atur ulang aturan.';
        $totalRule = Rule::active()->count();
        $penjelasanTren = $this->penjelasanTren(
            $tren,
            $persentaseTren,
            $labelPeriodeSaatIni,
            $labelPeriodeSebelumnya
        );
        $penjelasanSaran = $rule?->keterangan
            ?? 'Sistem memilih aturan pertama yang cocok dengan kondisi produk.';

        $inferenceTrace =
            "====================================================
HASIL ANALISIS RULE BASE
====================================================

[RINGKASAN SINGKAT]
Produk        : {$produk}
Saran         : {$rekomendasi}
Aturan dipakai: " . ($rule?->kode_rule ?? '-') . "

====================================================
PROSES ANALISIS RULE BASED
====================================================
Berikut langkah kerja sistem (Forward Chaining):

LANGKAH 1 — Baca data produk
----------------------------------------
  • Nama produk              : {$produk}
  • Jumlah terjual (periode) : {$penjualanSaatIni} unit
  • Penjualan periode lalu   : {$penjualanSebelumnya} unit
  • Stok saat ini            : {$stokSaatIni} unit

LANGKAH 2 — Ubah angka menjadi kondisi mudah dibaca
----------------------------------------
  • Penjualan  → {$kondisiPenjualan}
  • Stok       → {$kondisiStok}
  • Tren       → {$tren} (perubahan {$persentaseFormatted})
  • Alasan tren: {$penjelasanTren}

LANGKAH 3 — Siapkan memori kerja (working memory)
----------------------------------------
  Data yang dipakai mesin inferensi:
  • penjualan = {$kondisiPenjualan}
  • stok      = {$kondisiStok}
  • tren      = {$tren}

LANGKAH 4 — Muat basis pengetahuan (knowledge base)
----------------------------------------
  Jumlah aturan aktif: {$totalRule}
  Metode             : Forward Chaining
  Cara pilih aturan  : First Match (aturan pertama yang cocok)

LANGKAH 5 — Jalankan Forward Chaining
----------------------------------------
" . implode("\n", $log) . '

LANGKAH 6 — Kesimpulan
----------------------------------------
  Aturan terpilih : ' . ($rule?->kode_rule ?? 'tidak ada') . "
  Saran untuk Anda: {$rekomendasi}
  Penjelasan      : {$penjelasanSaran}
";

        return [
            'kondisi_penjualan' => $kondisiPenjualan,
            'kondisi_stok' => $kondisiStok,
            'tren' => $tren,
            'persentase_tren' => $persentaseFormatted,
            'kode_rule' => $rule?->kode_rule,
            'rekomendasi' => $rekomendasi,
            'penjelasan' => $penjelasanSaran,
            'inference_trace' => $inferenceTrace,
        ];
    }

    private function penjelasanTren(
        string $tren,
        float $persentase,
        ?string $labelPeriodeSaatIni,
        ?string $labelPeriodeSebelumnya
    ): string {
        $formatted = number_format($persentase, 2) . '%';
        $periodeSaatIni = $labelPeriodeSaatIni
            ? "periode {$labelPeriodeSaatIni}"
            : 'periode ini';
        $periodeSebelumnya = $labelPeriodeSebelumnya
            ? "periode {$labelPeriodeSebelumnya}"
            : 'periode sebelumnya';

        return match ($tren) {
            'Naik' => "Penjualan {$periodeSaatIni} lebih besar dari {$periodeSebelumnya} (naik {$formatted}).",
            'Turun' => "Penjualan {$periodeSaatIni} lebih kecil dari {$periodeSebelumnya} (turun {$formatted}).",
            default => "Penjualan {$periodeSaatIni} hampir sama dengan {$periodeSebelumnya} ({$formatted}).",
        };
    }

    /**
     * Ambil batas (integer) per level untuk (product_id, tipe).
     *
     * @return array<int, int|null> level => batas
     */
    private function thresholds(int $productId, string $tipe): array
    {
        return ProductThreshold::where('product_id', $productId)
            ->where('tipe', $tipe)
            ->orderBy('level')
            ->pluck('batas', 'level')
            ->all();
    }
}
