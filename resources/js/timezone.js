/**
 * Deteksi zona waktu perangkat pengguna.
 *
 * Server berjalan dan menyimpan data dalam UTC. Frontend hanya
 * memberi tahu server zona waktu perangkat, supaya:
 *  - tanggal ditampilkan dalam waktu lokal pengguna, dan
 *  - pengelompokan harian (laporan/grafik) memakai batas hari lokal.
 *
 * Nilai dikirim lewat cookie `tz` sehingga ikut pada setiap request,
 * termasuk saat halaman dibuka pertama kali.
 */

function detectTimezone() {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || null;
    } catch {
        return null;
    }
}

function readCookie(name) {
    return document.cookie
        .split('; ')
        .find((row) => row.startsWith(`${name}=`))
        ?.split('=')[1];
}

function syncTimezone() {
    const tz = detectTimezone();

    if (!tz) {
        return;
    }

    const encoded = encodeURIComponent(tz);

    if (readCookie('tz') === encoded) {
        return;
    }

    // 1 tahun, berlaku untuk seluruh aplikasi.
    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `tz=${encoded}; path=/; max-age=31536000; SameSite=Lax${secure}`;

    // Cookie gagal ditulis (diblokir browser) → jangan reload sama sekali,
    // server akan memakai zona waktu fallback dari konfigurasi.
    if (readCookie('tz') !== encoded) {
        return;
    }

    // Muat ulang SEKALI per sesi agar tampilan langsung memakai zona waktu
    // yang benar. Penanda disimpan di sessionStorage supaya tidak terjadi
    // reload berulang.
    try {
        if (sessionStorage.getItem('tz-synced') === encoded) {
            return;
        }
        sessionStorage.setItem('tz-synced', encoded);
    } catch {
        return;
    }

    // Hanya untuk navigasi biasa; hindari reload setelah submit form.
    if (document.readyState !== 'loading') {
        return;
    }

    window.location.reload();
}

syncTimezone();

/**
 * Format elemen bertanda [data-utc] ke waktu lokal perangkat.
 * Berguna untuk konten yang di-cache di sisi klien.
 */
export function renderLocalTimes(root = document) {
    root.querySelectorAll('[data-utc]').forEach((el) => {
        const iso = el.getAttribute('data-utc');
        if (!iso) return;

        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return;

        el.textContent = new Intl.DateTimeFormat('id-ID', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);
    });
}

document.addEventListener('DOMContentLoaded', () => renderLocalTimes());
