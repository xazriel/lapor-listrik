# ⚡ Lapor Listrik — Sistem Pelaporan Gangguan Listrik

Sistem informasi berbasis web untuk pelaporan dan pemantauan gangguan listrik di **Desa Tanjung Durian**. Warga dapat melaporkan gangguan secara online dan memantau status penanganannya secara real-time. Petugas admin menerima notifikasi dan dapat memperbarui status laporan langsung dari dashboard.

Urgensi setiap laporan diklasifikasikan secara otomatis menggunakan algoritma **C4.5 Decision Tree**.

---

## ✨ Fitur Utama

- 📋 **Form Pelaporan Publik** — Warga mengisi laporan gangguan listrik (jenis, wilayah, durasi padam)
- 🤖 **Klasifikasi Otomatis C4.5** — Sistem menentukan tingkat urgensi (Tinggi / Sedang / Rendah) secara otomatis
- 🔍 **Cek Status Laporan** — Warga dapat memantau status laporannya menggunakan nomor HP
- 📊 **Admin Dashboard** — Panel admin dengan statistik, chart per kategori, dan tabel laporan
- ✅ **Manajemen Status** — Admin dapat mengubah status laporan: `Pending → Proses → Selesai`
- 📱 **Notifikasi WhatsApp Otomatis** — Pesan WA terkirim ke pelapor saat laporan selesai ditangani (via [Fonnte API](https://fonnte.com))

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Frontend Reaktif | Livewire Volt 3 |
| Styling | Tailwind CSS |
| Chart | Chart.js |
| Database | MySQL / SQLite |
| Notifikasi WA | Fonnte API |
| Auth | Laravel Breeze |

---

## 📸 Tampilan

| Halaman Publik | Admin Dashboard |
|---|---|
| Form pelaporan + Cek status | Chart, statistik, dan tabel laporan |

---

## 🚀 Instalasi & Setup

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL atau SQLite

### Langkah Instalasi

```bash
# 1. Clone repo
git clone https://github.com/xazriel/lapor-listrik.git
cd lapor-listrik

# 2. Install dependensi PHP
composer install

# 3. Install dependensi JS
npm install

# 4. Salin file environment
cp .env.example .env

# 5. Generate app key
php artisan key:generate

# 6. Konfigurasi database di file .env
# DB_CONNECTION=mysql
# DB_DATABASE=lapor_listrik
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Jalankan migrasi
php artisan migrate

# 8. Build asset
npm run build

# 9. Jalankan server
php artisan serve
```

Buka di browser: `http://127.0.0.1:8000`

---

## 👤 Akun Admin

Buat akun admin melalui Tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
]);
$user->is_admin = true;
$user->save();
```

Login di: `http://127.0.0.1:8000/login`
Admin panel: `http://127.0.0.1:8000/admin`

---

## 📱 Konfigurasi Notifikasi WhatsApp (Fonnte)

1. Daftar di [fonnte.com](https://fonnte.com) dan hubungkan perangkat WA
2. Salin token API kamu
3. Ganti token di `resources/views/livewire/admindashboard.blade.php`:

```php
$token = "TOKEN_FONNTE_KAMU_DISINI";
```

> Notifikasi WA hanya terkirim saat status laporan diubah ke **Selesai**.

---

## 🌳 Algoritma C4.5

Klasifikasi urgensi menggunakan atribut berikut:

| Atribut | Nilai |
|---|---|
| Jenis Gangguan | Kabel Putus, Trafo Meledak, Padam Total, Lampu Jalan Mati |
| Dampak Wilayah | Satu Rumah, Satu RT, Fasilitas Umum, Seluruh Desa |
| Durasi Padam | Dalam jam |

**Output:** `Tinggi` / `Sedang` / `Rendah`

Implementasi di: `app/Services/ClassificationService.php`

---

## 📁 Struktur Folder Penting

```
app/
├── Models/
│   └── Report.php
├── Services/
│   └── ClassificationService.php     ← Algoritma C4.5
└── Http/Middleware/
    └── IsAdmin.php

resources/views/livewire/
├── admindashboard.blade.php          ← Dashboard admin + Chart
├── reportform.blade.php              ← Form pelaporan publik
└── check-report.blade.php           ← Cek status laporan
```

---

## 📄 Lisensi

Proyek ini dibuat untuk keperluan **Final Project / Skripsi**.  
© 2026 — Sistem Informasi Geografis Gangguan Listrik
