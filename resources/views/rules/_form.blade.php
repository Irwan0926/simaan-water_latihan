<div class="row g-3">
    <div class="col-12">
        <label class="label-app" for="kode_rule">Kode Aturan</label>
        <input type="text" name="kode_rule" id="kode_rule" class="field" x-model="form.kode_rule" required maxlength="20" placeholder="R01">
        <p class="text-mute mt-1 mb-0" style="font-size:11px;">Urutan pengecekan Forward Chaining mengikuti kode aturan.</p>
    </div>
    <div class="col-4">
        <label class="label-app" for="penjualan">Penjualan</label>
        <select name="penjualan" id="penjualan" class="select-dark" x-model="form.penjualan" required>
            <option value="">— Pilih —</option>
            @foreach(\App\Models\Rule::PENJUALAN_OPTIONS as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label class="label-app" for="stok">Stok</label>
        <select name="stok" id="stok" class="select-dark" x-model="form.stok" required>
            <option value="">— Pilih —</option>
            @foreach(\App\Models\Rule::STOK_OPTIONS as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-4">
        <label class="label-app" for="tren">Tren</label>
        <select name="tren" id="tren" class="select-dark" x-model="form.tren" required>
            <option value="">— Pilih —</option>
            @foreach(\App\Models\Rule::TREN_OPTIONS as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
        <p class="text-mute mt-1 mb-0" style="font-size:11px;">Wajib spesifik (Naik/Stabil/Turun). Tidak boleh “Semua”.</p>
    </div>
    <div class="col-12">
        <label class="label-app" for="rekomendasi">Saran (jika cocok)</label>
        <input type="text" name="rekomendasi" id="rekomendasi" class="field" x-model="form.rekomendasi" required maxlength="100" placeholder="Contoh: Segera Restock">
    </div>
    <div class="col-12">
        <label class="label-app" for="kategori">Kategori</label>
        <select name="kategori" id="kategori" class="select-dark" x-model="form.kategori">
            <option value="">—</option>
            @foreach(\App\Models\Rule::KATEGORI_OPTIONS as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12">
        <label class="label-app" for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" class="field" rows="2" x-model="form.keterangan" maxlength="500"></textarea>
    </div>
    <div class="col-12">
        <label class="d-flex align-items-center gap-2 text-13 text-ink-soft mb-0">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input-app" x-model="form.is_active">
            Rule aktif (ikut di inference)
        </label>
    </div>
</div>
