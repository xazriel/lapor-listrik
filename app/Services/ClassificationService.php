<?php

namespace App\Services;

/**
 * ClassificationService — Algoritma C4.5
 *
 * Rules di bawah ini dihasilkan dari pohon keputusan yang
 * dilatih menggunakan Python (scikit-learn, criterion='entropy')
 * dengan 80 data training. Akurasi training: 100%, CV: 91.25%.
 *
 * Atribut Input:
 *   - jenis    : Jenis gangguan listrik (kategorik)
 *   - dampak   : Wilayah terdampak (kategorik)
 *   - durasi   : Lama padam dalam jam (numerik → didiskretisasi)
 *
 * Output: 'Tinggi' | 'Sedang' | 'Rendah'
 */
class ClassificationService
{
    /**
     * Diskretisasi durasi numerik ke kategori ordinal.
     * Sesuai preprocessing di python/train_c45.py
     *   0 = Pendek  (≤ 2 jam)
     *   1 = Sedang  (3–5 jam)
     *   2 = Panjang (≥ 6 jam)
     */
    private function kategoriDurasi(int $jam): int
    {
        if ($jam <= 2) return 0; // Pendek
        if ($jam <= 5) return 1; // Sedang
        return 2;                // Panjang
    }

    /**
     * Klasifikasi urgensi berdasarkan rules pohon keputusan C4.5.
     */
    public function classifyUrgensi(string $jenis, string $dampak, int $durasi): string
    {
        $d = $this->kategoriDurasi($durasi);

        // ── NODE 1: Jenis berbahaya bagi jiwa → selalu TINGGI ────────────
        // (Leaf murni dari pohon C4.5, gain ratio tertinggi)
        $jenisBerbahaya = ['Kabel Putus', 'Trafo Meledak', 'Tiang Listrik Roboh'];
        if (in_array($jenis, $jenisBerbahaya)) {
            return 'Tinggi';
        }

        // ── NODE 2: Padam Total ───────────────────────────────────────────
        if ($jenis === 'Padam Total') {

            // Seluruh desa terdampak → selalu TINGGI
            if ($dampak === 'Seluruh Desa') {
                return 'Tinggi';
            }

            // Fasilitas umum: tergantung durasi
            if ($dampak === 'Fasilitas Umum') {
                return ($d >= 1) ? 'Tinggi' : 'Sedang'; // durasi Sedang/Panjang → Tinggi
            }

            // Satu RT: tergantung durasi
            if ($dampak === 'Satu RT') {
                return ($d === 2) ? 'Tinggi' : 'Sedang'; // hanya Panjang (≥6 jam) → Tinggi
            }

            // Satu Rumah: tergantung durasi
            if ($dampak === 'Satu Rumah') {
                return ($d === 0) ? 'Rendah' : 'Sedang'; // Pendek → Rendah
            }
        }

        // ── NODE 3: Lampu Jalan Mati ──────────────────────────────────────
        if ($jenis === 'Lampu Jalan Mati') {

            // Area luas + durasi panjang → Sedang
            $areaLuas = in_array($dampak, ['Seluruh Desa', 'Fasilitas Umum']);
            if ($areaLuas && $d === 2) {
                return 'Sedang';
            }

            // Seluruh Desa + durasi sedang → Sedang
            if ($dampak === 'Seluruh Desa' && $d === 1) {
                return 'Sedang';
            }

            // Semua kondisi lain → Rendah
            return 'Rendah';
        }

        // ── DEFAULT FALLBACK ──────────────────────────────────────────────
        return 'Rendah';
    }
}