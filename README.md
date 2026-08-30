# 🏢 AsramaApp - Sistem Informasi & Manajemen Asrama (Desktop Application)

Aplikasi desktop mandiri berbasis **NativePHP (Electron)** dan **Laravel 12** untuk pengelolaan dan administrasi asrama mahasiswa/penghuni, pembukuan kas keuangan, kontrol matriks iuran bulanan, serta rekapitulasi pelaporan secara offline.

---

## 🌟 Fitur Utama

1. **📊 Dashboard Eksekutif:**
   - Ringkasan statistik *real-time*: jumlah penghuni aktif, ketersediaan kamar, saldo kas asrama, dan total pengeluaran.
   - Tabel transaksi kas terbaru dan monitoring status kapasitas kamar.
   - Tombol pintasan (*quick actions*) untuk akses kilat ke modul-modul asrama.

2. **🛏️ Manajemen Data Penghuni & Kamar:**
   - Pendataan kamar lengkap dengan status ketersediaan (*Tersedia, Penuh, Gudang, Perbaikan*) dan fasilitas.
   - Pendaftaran & biodata penghuni asrama dengan integrasi nomor HP, kampus, tanggal masuk, dan catatan.
   - **Dropdown Terstruktur 5 Kecamatan & 50 Kampung Kabupaten Mahakam Ulu:**
     - **Kecamatan Long Bagun** (11 Kampung)
     - **Kecamatan Long Hubung** (11 Kampung)
     - **Kecamatan Laham** (5 Kampung)
     - **Kecamatan Long Apari** (10 Kampung)
     - **Kecamatan Long Pahangai** (13 Kampung)
     - *Luar Daerah / Lainnya*
   - Fitur penghuni keluar (*checkout*) dan aktivasi ulang (*reactivate*) riwayat penghuni.
   - Tombol proteksi aksi (*toggle ON/OFF* kolom tombol aksi untuk keamanan data).

3. **💰 Riwayat Transaksi Keuangan Kas:**
   - Pencatatan transaksi kas masuk (*Pemasukan*) dan kas keluar (*Pengeluaran*).
   - **Kategori Dinamis Otomatis:** Kategori menyesuaikan secara cerdas berdasarkan tipe transaksi (Iuran Bulanan, Listrik & Air, WiFi, Sampah, Kebersihan, Perbaikan, Donasi, dll).
   - Sinkronisasi otomatis alokasi iuran bulanan penghuni ke dalam matriks pembayaran.
   - Filter pencarian instan dan penyaringan tipe transaksi.
   - **Export Laporan Lokal:** Unduh rekapitulasi transaksi kas ke dalam format **Excel (.csv)** dan **PDF**.

4. **📈 Matriks Iuran Bulanan:**
   - Tabel matriks visual 12 bulan untuk memantau status kelunasan iuran seluruh penghuni secara akurat.
   - Penyesuaian tarif bulanan default (*customizable*).
   - Indikator otomatis status lunas / nominal tunggakan per penghuni.
   - Monitoring biaya operasional fasilitas bersama (WiFi & Sampah).
   - **Export Matriks PDF & Excel (.csv)** siap cetak.

5. **🎨 Desain & Pengalaman Pengguna (UX):**
   - Tampilan *Modern Dark Theme* yang elegan dan ramah di mata.
   - **Sticky Global Header:** Navigasi selalu melekat di bagian atas saat halaman digulir.
   - Notifikasi Toast interaktif dan modal konfirmasi aksi.
   - Performa instan berbasis database lokal SQLite (*WAL mode & Normal sync*).

---

## 🛠️ Tech Stack

- **Framework:** Laravel 12 (PHP 8.3)
- **Desktop Runtime:** NativePHP for Electron (Node.js 20+)
- **Database:** SQLite (Super ringan, mandiri, tanpa perlu setup server MySQL terpisah)
- **Reporting:** DomPDF & Native CSV Exporter
- **Frontend / Styling:** Vanilla CSS3 Variables, Blade Templating

---

## 🚀 Cara Menjalankan Aplikasi

### 1. Prasyarat
- PHP >= 8.2
- Composer
- Node.js >= 18

### 2. Instalasi & Persiapan
```bash
# Clone repository
git clone https://github.com/Komecitos/Asrama_app.git
cd Asrama_app

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Setup file environment
cp .env.example .env
php artisan key:generate

# Migrasi Database SQLite
php artisan migrate
```

### 3. Menjalankan Mode Desktop (Development)
```bash
php artisan native:serve
```
*Aplikasi desktop akan otomatis terbuka dalam jendela Electron.*

### 4. Mem-build Menjadi File Installer `.exe`
```bash
php artisan native:build
```
*File installer executable (.exe) akan dihasilkan di dalam folder `dist/`.*

---

## 👨‍💻 Developer & Lisensi

- **Developer:** Irga Prayoga
- **Tahun Rilis:** 2026
- **Repository:** [github.com/Komecitos/Asrama_app](https://github.com/Komecitos/Asrama_app)
- **Lisensi:** Open-source software under the [MIT license](LICENSE).

