# 🎓 Panduan Jawaban Sidang / Dospem — C4.5 Tanpa RapidMiner

> Dokumen ini berisi jawaban siap pakai untuk pertanyaan umum dospem mengenai implementasi algoritma C4.5 pada tugas akhir sistem laporan gangguan listrik.

---

## ❓ Q1: "Mengapa tidak menggunakan RapidMiner seperti biasanya?"

**Jawaban:**

> "Kami memang menggunakan pendekatan yang lebih dekat ke implementasi nyata. RapidMiner adalah *tools* eksperimen, bukan implementasi produksi. Dalam penelitian ini, kami menggunakan **Python dengan library scikit-learn** untuk proses training dan validasi model C4.5, kemudian hasil berupa pohon keputusan *(decision tree rules)* diterapkan langsung di dalam sistem web berbasis Laravel.
>
> Pendekatan ini sebenarnya lebih kuat secara akademik, karena kami bisa menampilkan proses lengkap: mulai dari perhitungan **entropy**, **information gain**, pembangunan pohon, hingga evaluasi akurasi menggunakan **cross-validation 5-fold** — semuanya dilakukan secara programatik dan dapat direproduksi."

---

## ❓ Q2: "Apa bedanya C4.5 dengan Decision Tree biasa (ID3)?"

**Jawaban:**

| Aspek | ID3 | C4.5 |
|---|---|---|
| Kriteria Split | Information Gain | **Gain Ratio** |
| Data Numerik | ❌ Tidak bisa | ✅ Bisa (dengan threshold) |
| Data Kosong | ❌ Tidak bisa | ✅ Bisa ditangani |
| Overfitting | Mudah overfit | Ada **pruning** |
| Atribut banyak nilai | Bias ke atribut dengan banyak nilai | Diatasi dengan gain ratio |

> "C4.5 adalah penyempurnaan ID3 oleh J. Ross Quinlan (1993). Perbedaan utamanya adalah penggunaan **Gain Ratio** menggantikan Information Gain murni, sehingga tidak bias pada atribut yang memiliki banyak nilai unik seperti durasi padam. Dalam implementasi kami menggunakan `criterion='entropy'` pada scikit-learn, yang merupakan basis dari C4.5."

---

## ❓ Q3: "Bagaimana cara kerja C4.5 dalam sistem ini?"

**Jawaban (jelaskan alurnya):**

```
Tahap 1 — Training (Offline, Python):
  Dataset CSV (80 baris)
      ↓
  Preprocessing: Diskretisasi durasi (Pendek/Sedang/Panjang)
      ↓
  Label Encoding untuk fitur kategorik
      ↓
  Latih DecisionTreeClassifier (criterion='entropy')
      ↓
  Evaluasi: Akurasi 93.75%, CV 91.25%
      ↓
  Ekstrak rules dari pohon keputusan

Tahap 2 — Produksi (Online, Laravel/PHP):
  User input laporan (jenis, dampak, durasi)
      ↓
  ClassificationService::classifyUrgensi()
      ↓
  Jalankan rules hasil C4.5
      ↓
  Output: Urgensi Tinggi / Sedang / Rendah
```

---

## ❓ Q4: "Bagaimana akurasi model dan cara mengukurnya?"

**Jawaban:**

> "Kami menggunakan tiga metrik evaluasi:
>
> 1. **Akurasi Training (100%)** — model sempurna mempelajari pola dari data training. Ini wajar karena dataset kami bersih dan konsisten.
> 2. **Akurasi Testing (93.75%)** — diuji pada 20% data yang tidak pernah dilihat model sebelumnya, menunjukkan model bisa generalisasi dengan baik.
> 3. **Cross-Validation 5-Fold (91.25%)** — ini metrik paling representatif. Dataset dibagi 5 bagian, dilatih dan diuji 5 kali, hasilnya dirata-rata. Hasilnya 91.25% menunjukkan model cukup robust."
>
> "Kesalahan klasifikasi terlihat pada Confusion Matrix: 1 data Sedang terprediksi sebagai Rendah, yang terjadi pada kasus Padam Total di Satu Rumah dengan durasi di batas threshold."

---

## ❓ Q5: "Mengapa menggunakan 3 atribut saja? Apakah tidak kurang?"

**Jawaban:**

> "Ketiga atribut ini dipilih berdasarkan **domain knowledge** dari proses penanganan gangguan listrik PLN:
> - **Jenis Gangguan** — menentukan tingkat bahaya (keselamatan jiwa vs. kenyamanan)
> - **Dampak Wilayah** — menentukan luas terdampak (satu rumah vs. seluruh desa)
> - **Durasi Padam** — menentukan keparahan/eskalasi (semakin lama, semakin kritis)
>
> Dalam algoritma C4.5, atribut dengan **information gain tertinggi** dipilih sebagai root node. Dari hasil training, `jenis_gangguan` menjadi root node karena memiliki gain ratio tertinggi — ini konsisten dengan logika domain: jenis gangguan paling menentukan tingkat urgensi."

---

## ❓ Q6: "Kenapa dataset hanya 80 data? Apakah cukup?"

**Jawaban:**

> "Dataset 80 baris merupakan dataset **sintetik berdasarkan aturan domain** dari standar penanganan gangguan listrik PLN. Dalam konteks penelitian tugas akhir dengan domain yang well-defined dan label yang deterministik seperti ini, 80 data sudah mencukupi karena:
> 1. Kombinasi atribut yang mungkin terbatas: 5 × 4 × 3 = **60 kombinasi unik**
> 2. Setiap kombinasi memiliki label yang konsisten
> 3. Akurasi cross-validation 91.25% menunjukkan model generalisasi dengan baik
>
> Untuk penelitian lanjutan, disarankan mengumpulkan data real dari lapangan untuk menggantikan dataset sintetik ini."

---

## ❓ Q7: "Apa kontribusi sistem ini dibanding sistem manual biasa?"

**Jawaban:**

> "Sistem ini memberikan tiga kontribusi utama:
> 1. **Klasifikasi Otomatis** — Urgensi gangguan ditentukan oleh algoritma C4.5 secara real-time, menghilangkan subjektivitas petugas
> 2. **Prioritas Penanganan Berbasis Data** — Admin dapat melihat laporan diurutkan berdasarkan urgensi, bukan hanya waktu masuk
> 3. **Notifikasi Otomatis** — Pelapor mendapat konfirmasi via WhatsApp (Fonnte API) saat laporan selesai ditangani
>
> Kombinasi machine learning (C4.5) dengan sistem informasi web inilah yang menjadi novelty tugas akhir ini."

---

## ❓ Q8: "Bagaimana proses diskretisasi durasi dilakukan?"

**Jawaban:**

> "Atribut `durasi_padam` adalah data numerik (jam). C4.5 menangani ini dengan mencari **threshold optimal**. Dalam implementasi kami, kami mendiskretisasi menjadi tiga kategori berdasarkan domain knowledge:
>
> | Kategori | Rentang | Kode |
> |---|---|---|
> | Pendek | ≤ 2 jam | 0 |
> | Sedang | 3–5 jam | 1 |
> | Panjang | ≥ 6 jam | 2 |
>
> Threshold ini diterapkan konsisten baik di Python (training) maupun PHP (produksi) pada method `kategoriDurasi()` di `ClassificationService.php`."

---

## 💡 Tips Presentasi

- **Tunjukkan screenshot** admin dashboard — bagian "Informasi Model C4.5" dengan confusion matrix dan rules pohon
- **Tunjukkan file** `python/train_c45.py` dan `python/dataset.csv` sebagai bukti proses training
- **Jelaskan alur**: dataset → Python training → rules → PHP implementation → web
- Jika ditanya software: *"Kami tidak bergantung pada tools komersial seperti RapidMiner, melainkan mengimplementasikan langsung menggunakan open-source library sehingga lebih transparan dan reproducible"*
