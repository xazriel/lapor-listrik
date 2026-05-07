<?php

namespace App\Services;

class ClassificationService
{
    public function classifyUrgensi($jenis, $dampak, $durasi): string
    {
        // KATEGORI 1: GANGGUAN KRITIS & BERBAHAYA (SAFETY FIRST)
        // Apapun dampaknya, jika berbahaya bagi nyawa, langsung TINGGI.
        if ($jenis === 'Kabel Putus' || $jenis === 'Trafo Meledak') {
            return 'Tinggi';
        }

        // KATEGORI 2: ANALISIS PADAM TOTAL
        if ($jenis === 'Padam Total') {
            // Jika satu desa mati total, sangat kritis
            if ($dampak === 'Seluruh Desa') {
                return 'Tinggi';
            }
            // Jika di fasilitas umum dan sudah lebih dari 2 jam
            if ($dampak === 'Fasilitas Umum' && $durasi >= 2) {
                return 'Tinggi';
            }
            // Jika hanya satu rumah tapi sudah sangat lama (misal > 12 jam)
            if ($dampak === 'Satu Rumah' && $durasi > 12) {
                return 'Sedang';
            }
            
            return 'Sedang';
        }

        // KATEGORI 3: ANALISIS DURASI DAN DAMPAK (INTERAKSI VARIABEL)
        // Ini inti dari C4.5: mencari ambang batas (threshold)
        if ($durasi >= 5) {
            // Meskipun gangguan ringan, kalau sudah > 5 jam di area luas jadi Tinggi
            return ($dampak === 'Seluruh Desa' || $dampak === 'Satu RT') ? 'Tinggi' : 'Sedang';
        }

        if ($durasi >= 3) {
            // Durasi menengah
            if ($dampak === 'Seluruh Desa' || $dampak === 'Fasilitas Umum') {
                return 'Tinggi';
            }
            return 'Sedang';
        }

        // KATEGORI 4: GANGGUAN RINGAN (MISAL: LAMPU JALAN MATI)
        if ($jenis === 'Lampu Jalan Mati') {
            // Lampu jalan hanya jadi prioritas jika areanya luas (Seluruh Desa)
            return ($dampak === 'Seluruh Desa') ? 'Sedang' : 'Rendah';
        }

        // Default jika tidak masuk kategori kritis
        return 'Rendah';
    }
}