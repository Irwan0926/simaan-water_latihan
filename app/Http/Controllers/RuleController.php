<?php

namespace App\Http\Controllers;

use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule as ValidationRule;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::orderBy('kode_rule')->get();

        return view('rules.index', [
            'rules' => $rules,
            'totalKombinasi' => Rule::TOTAL_KOMBINASI,
            'count' => $rules->count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Rule::create($data);

        return redirect()
            ->route('rules.index')
            ->with('success', 'Rule ' . $data['kode_rule'] . ' berhasil ditambahkan.');
    }

    public function show(Rule $rule)
    {
        $konflik = Rule::where('id', '!=', $rule->id)
            ->where('penjualan', $rule->penjualan)
            ->where('stok', $rule->stok)
            ->where('tren', $rule->tren)
            ->orderBy('kode_rule')
            ->get();

        // First Match memindai aturan urut kode_rule, jadi aturan dengan kode
        // lebih kecil dan premis identik akan selalu menang lebih dulu.
        $lebihDulu = Rule::where('id', '!=', $rule->id)
            ->where('is_active', true)
            ->where('penjualan', $rule->penjualan)
            ->where('stok', $rule->stok)
            ->where('tren', $rule->tren)
            ->where('kode_rule', '<', $rule->kode_rule)
            ->exists();

        return view('rules.show', [
            'rule' => $rule,
            'konflik' => $konflik,
            'tertutup' => $lebihDulu,
        ]);
    }

    public function update(Request $request, Rule $rule)
    {
        $data = $this->validated($request, $rule->id);

        $rule->update($data);

        return redirect()
            ->route('rules.index')
            ->with('success', 'Rule ' . $rule->kode_rule . ' berhasil diperbarui.');
    }

    public function destroy(Rule $rule)
    {
        $kode = $rule->kode_rule;
        $rule->delete();

        return redirect()
            ->route('rules.index')
            ->with('success', "Rule {$kode} dihapus.");
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $uniqueKode = ValidationRule::unique('rules', 'kode_rule');
        if ($ignoreId) {
            $uniqueKode = $uniqueKode->ignore($ignoreId);
        }

        $validated = $request->validate([
            'kode_rule' => ['required', 'string', 'max:20', $uniqueKode],
            // Premis wajib spesifik — tidak boleh "Semua" / null (hindari bug First Match longgar)
            'penjualan' => ['required', ValidationRule::in(Rule::PENJUALAN_OPTIONS)],
            'stok' => ['required', ValidationRule::in(Rule::STOK_OPTIONS)],
            'tren' => ['required', ValidationRule::in(Rule::TREN_OPTIONS)],
            'rekomendasi' => ['required', 'string', 'max:100'],
            'kategori' => ['nullable', ValidationRule::in(Rule::KATEGORI_OPTIONS)],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode_rule.unique' => 'Kode aturan sudah dipakai.',
            'penjualan.required' => 'Kondisi penjualan wajib dipilih (tidak boleh Semua).',
            'stok.required' => 'Kondisi stok wajib dipilih (tidak boleh Semua).',
            'tren.required' => 'Kondisi tren wajib dipilih (tidak boleh Semua / apa saja).',
        ]);

        // 'kategori' opsional: bisa tidak dikirim sama sekali, atau dikirim
        // kosong. Keduanya disimpan sebagai NULL.
        $validated['kategori'] = ($validated['kategori'] ?? null) ?: null;
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
