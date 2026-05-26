<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi Model C4.5 — Lapor Listrik
|--------------------------------------------------------------------------
| File ini menyimpan hasil training model C4.5 yang dijalankan melalui
| skrip Python (python/train_c45.py) menggunakan library scikit-learn.
|
| Cara update: Jalankan python/train_c45.py, lalu salin hasil
| akurasi & confusion matrix ke sini.
|
| criterion = 'entropy' → Information Gain (dasar algoritma C4.5)
|--------------------------------------------------------------------------
*/

return [

    // ── INFO DATASET ─────────────────────────────────────────────────────
    'total_data'        => 79,
    'total_training'    => 63,
    'total_testing'     => 16,
    'total_atribut'     => 3,
    'atribut'           => ['Jenis Gangguan', 'Dampak Wilayah', 'Durasi Padam'],
    'kelas'             => ['Tinggi', 'Sedang', 'Rendah'],

    // ── AKURASI ───────────────────────────────────────────────────────────
    'akurasi_training'  => 95.24,   // %
    'akurasi_testing'   => 93.75,   // %
    'akurasi_cv'        => 84.83,   // % (5-Fold Cross Validation)
    'cv_std'            => 8.44,    // standar deviasi CV
    'cv_per_fold'       => [87.5, 87.5, 68.8, 93.8, 86.7],

    // ── CONFUSION MATRIX (Testing 16 data) ───────────────────────────────
    // Baris = Aktual, Kolom = Prediksi [Rendah, Sedang, Tinggi]
    'confusion_matrix'  => [
        'Rendah' => ['Rendah' => 3, 'Sedang' => 0, 'Tinggi' => 0],
        'Sedang' => ['Rendah' => 0, 'Sedang' => 3, 'Tinggi' => 1],
        'Tinggi' => ['Rendah' => 0, 'Sedang' => 0, 'Tinggi' => 9],
    ],

    // ── RULES POHON KEPUTUSAN C4.5 ────────────────────────────────────────
    'rules' => [
        [
            'kondisi' => 'Jenis Gangguan ∈ {Kabel Putus, Trafo Meledak, Tiang Listrik Roboh}',
            'hasil'   => 'Tinggi',
            'alasan'  => 'Berbahaya bagi keselamatan jiwa — prioritas darurat',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Seluruh Desa',
            'hasil'   => 'Tinggi',
            'alasan'  => 'Seluruh wilayah terdampak, menyentuh layanan vital',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Fasilitas Umum  AND  Durasi ≥ 3 jam',
            'hasil'   => 'Tinggi',
            'alasan'  => 'Gangguan panjang di fasilitas publik kritis',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Fasilitas Umum  AND  Durasi < 3 jam',
            'hasil'   => 'Sedang',
            'alasan'  => 'Masih dalam batas toleransi, namun perlu ditindak',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Satu RT  AND  Durasi ≥ 6 jam',
            'hasil'   => 'Tinggi',
            'alasan'  => 'Padam total RT sangat lama — eskalasi diperlukan',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Satu RT  AND  Durasi < 6 jam',
            'hasil'   => 'Sedang',
            'alasan'  => 'Area cukup luas namun durasi masih dalam toleransi',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Satu Rumah  AND  Durasi ≥ 3 jam',
            'hasil'   => 'Sedang',
            'alasan'  => 'Satu rumah terdampak namun cukup lama',
        ],
        [
            'kondisi' => 'Jenis = Padam Total  AND  Dampak = Satu Rumah  AND  Durasi < 3 jam',
            'hasil'   => 'Rendah',
            'alasan'  => 'Dampak minimal dan durasi singkat',
        ],
        [
            'kondisi' => 'Jenis = Lampu Jalan Mati  AND  Dampak ∈ {Seluruh Desa, Fasilitas Umum}  AND  Durasi ≥ 6 jam',
            'hasil'   => 'Sedang',
            'alasan'  => 'Area luas dan berlangsung lama — perlu penanganan',
        ],
        [
            'kondisi' => 'Jenis = Lampu Jalan Mati  (kondisi lainnya)',
            'hasil'   => 'Rendah',
            'alasan'  => 'Gangguan minor, tidak berdampak pada keselamatan langsung',
        ],
    ],

];
