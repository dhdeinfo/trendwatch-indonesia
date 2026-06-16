# TrendWatch Indonesia

TrendWatch Indonesia adalah aplikasi PHP Native + SQLite untuk memantau tren pencarian, mengelola data tren, menampilkan grafik tren, membaca peluang konten SEO, dan melakukan sinkronisasi data dari Google Trends RSS Indonesia.

Versi ini adalah **Step 15 Final**, yaitu versi finalisasi tampilan setelah proses pengembangan bertahap dari Step 1 sampai Step 10, lalu langsung dilanjutkan ke Step 15.

## Status proyek

- Nama aplikasi: TrendWatch Indonesia
- Bahasa pemrograman: PHP Native
- Database: SQLite
- Frontend: HTML, CSS, JavaScript
- Chart: Chart.js
- Sumber data eksternal: Google Trends RSS Indonesia
- Mode data: manual dan sinkron RSS
- Status: final UI tahap awal

## Akun default

Gunakan akun berikut setelah menjalankan `setup.php`.

```text
Email: admin@gmail.com
Password: admin123
```

Ganti password default jika aplikasi akan dipakai untuk kebutuhan serius.

## Struktur folder utama

```text
trendwatch/
├── admin/
│   ├── dashboard.php
│   ├── trends.php
│   ├── trend_add.php
│   ├── trend_edit.php
│   ├── trend_detail.php
│   ├── trend_delete.php
│   ├── charts.php
│   └── sync_google_trends.php
│
├── assets/
│   ├── css/
│   │   └── admin.css
│   ├── js/
│   │   └── app.js
│   └── img/
│
├── auth/
│   ├── login.php
│   └── logout.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   └── database.php
│
├── database/
│   └── trendwatch.sqlite
│
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── google_trends_sync.php
│
├── check_connection.php
├── setup.php
├── sync_google_trends_cron.php
├── FIX_ERROR.txt
├── INSTALL.txt
├── README.md
└── index.php
```

## Cara instalasi

1. Extract file ZIP.
2. Copy folder `trendwatch` ke folder Laragon:

```text
C:\laragon\www\
```

3. Pastikan struktur folder menjadi:

```text
C:\laragon\www\trendwatch\index.php
C:\laragon\www\trendwatch\setup.php
C:\laragon\www\trendwatch\auth\login.php
C:\laragon\www\trendwatch\admin\dashboard.php
```

4. Jalankan setup database:

```text
http://localhost/trendwatch/setup.php
```

5. Login admin:

```text
http://localhost/trendwatch/auth/login.php
```

6. Masukkan akun default:

```text
Email: admin@gmail.com
Password: admin123
```

Jika folder diletakkan di subfolder, sesuaikan URL. Contoh:

```text
C:\laragon\www\trend\trendwatch\
```

Maka URL menjadi:

```text
http://localhost/trend/trendwatch/
```

## Ekstensi PHP yang dibutuhkan

Pastikan ekstensi berikut aktif di Laragon.

```ini
extension=pdo_sqlite
extension=sqlite3
extension=simplexml
extension=curl
```

Jika mengubah `php.ini`, restart Laragon.

## Changelog lengkap

### Step 1: Struktur awal project

- Membuat struktur dasar aplikasi PHP Native.
- Menambahkan folder `config`, `database`, `auth`, `admin`, `includes`, `assets/css`, dan `assets/js`.
- Menambahkan file awal `index.php`.
- Menambahkan placeholder halaman login.
- Menambahkan placeholder halaman dashboard.
- Menambahkan file CSS dan JS dasar.
- Menyiapkan konsep aplikasi TrendWatch Indonesia sebagai aplikasi pemantau tren dan ide konten SEO.

### Step 2: Setup database SQLite

- Mengganti rancangan database dari MySQL ke SQLite.
- Menambahkan file database `database/trendwatch.sqlite`.
- Menambahkan koneksi database melalui `config/database.php`.
- Menambahkan file `setup.php`.
- Membuat tabel `admins`.
- Membuat tabel `trends`.
- Membuat tabel `trend_histories`.
- Menambahkan admin default.
- Menambahkan data contoh tren awal.
- Menyiapkan database agar aplikasi dapat berjalan tanpa phpMyAdmin.

### Step 3: Pengecekan koneksi database

- Menambahkan halaman `check_connection.php`.
- Menambahkan pemeriksaan ekstensi PDO SQLite.
- Menambahkan pemeriksaan file database SQLite.
- Menambahkan pemeriksaan folder database.
- Menambahkan pemeriksaan tabel `admins`, `trends`, dan `trend_histories`.
- Menampilkan contoh data tren dari database.
- Mengupdate halaman beranda agar lebih informatif.

### Step 4: Login admin

- Menambahkan halaman login admin.
- Menambahkan proses validasi email dan password.
- Menggunakan `password_hash()` untuk password admin default.
- Menggunakan `password_verify()` saat login.
- Menambahkan session admin.
- Menambahkan `session_regenerate_id(true)` setelah login berhasil.
- Menambahkan logout admin.
- Menambahkan proteksi halaman dashboard.
- Menambahkan dashboard awal setelah login.
- Menampilkan ringkasan total tren, tren aktif, dan volume tertinggi.

### Step 5: Layout admin

- Menambahkan layout admin yang lebih rapi.
- Menambahkan sidebar admin.
- Menambahkan topbar admin.
- Merapikan halaman dashboard.
- Menambahkan halaman data tren.
- Menampilkan data tren dari SQLite.
- Menambahkan tampilan responsive untuk desktop dan mobile.
- Menambahkan CSS admin yang lebih profesional.
- Memastikan tombol logout tetap tersedia.

### Step 6: Dashboard tren lengkap

- Mengembangkan dashboard menjadi lebih lengkap.
- Menambahkan ringkasan total tren.
- Menambahkan ringkasan tren aktif.
- Menambahkan total volume pencarian.
- Menambahkan rata-rata volume pencarian.
- Menambahkan grafik volume tren dengan Chart.js.
- Menambahkan grafik distribusi kategori.
- Menambahkan grafik pergerakan top tren.
- Menambahkan tabel peluang konten SEO.
- Menambahkan daftar tren dengan kenaikan tercepat.
- Menambahkan data contoh SQLite yang lebih banyak.

### Step 6 Revisi: Perbaikan path dan instalasi

- Memperbaiki path agar lebih aman saat folder project berada di subfolder.
- Mengurangi ketergantungan pada nama folder yang kaku.
- Menambahkan `INSTALL.txt`.
- Menambahkan `README.md` awal.
- Memeriksa ulang file `setup.php`.
- Mengecek syntax file PHP.
- Menjaga dashboard Step 6 tetap tersedia.
- Menjaga data SQLite tetap tersedia.

### Step 7: CRUD data tren

- Menambahkan fitur tambah data tren.
- Menambahkan fitur edit data tren.
- Menambahkan fitur detail data tren.
- Menambahkan fitur hapus data tren dengan konfirmasi.
- Menambahkan filter berdasarkan keyword.
- Menambahkan filter berdasarkan kategori.
- Menambahkan filter berdasarkan status.
- Menambahkan validasi form.
- Menambahkan proteksi CSRF pada form penting.
- Menambahkan flash message untuk sukses dan gagal.
- Menambahkan update histori grafik otomatis saat tambah data.
- Menambahkan update histori grafik otomatis saat edit data.

### Step 8: Detail tren lengkap

- Mengembangkan halaman detail tren.
- Menambahkan analisis prioritas konten.
- Menambahkan intent pencarian otomatis.
- Menampilkan keyword terkait dalam bentuk chip.
- Menambahkan ide judul artikel otomatis berbasis template.
- Menambahkan ide konten Instagram atau TikTok berbasis template.
- Menambahkan grafik histori tren.
- Menambahkan tabel riwayat volume per jam.
- Menambahkan tombol cetak halaman detail.
- Mengecek ulang file PHP agar tidak ada syntax error.

### Step 9: Grafik tren

- Menambahkan halaman baru `admin/charts.php`.
- Menambahkan menu baru di sidebar: Grafik Tren.
- Menambahkan grafik volume tren.
- Menambahkan grafik distribusi kategori.
- Menambahkan grafik status tren.
- Menambahkan grafik SEO score.
- Menambahkan grafik pergerakan tren pilihan.
- Menambahkan grafik komparasi histori top tren.
- Menambahkan filter grafik berdasarkan kategori.
- Menambahkan filter grafik berdasarkan status.
- Menambahkan filter grafik berdasarkan tren pilihan.
- Menambahkan tabel ranking kenaikan tren.
- Mengecek ulang file PHP agar tidak ada syntax error.

### Step 9 Fix: Perbaikan error tabel belum ada

- Menangani error `no such table: trends`.
- Menambahkan pengamanan agar tabel dibuat otomatis saat aplikasi dibuka.
- Menambahkan file `FIX_ERROR.txt`.
- Menjelaskan penyebab error karena `setup.php` belum dijalankan atau database kosong.
- Menambahkan arahan untuk menghapus `trendwatch.sqlite` jika struktur database rusak.

### Step 10: Integrasi Google Trends RSS Indonesia

- Menambahkan menu baru: Sync Google Trends.
- Menambahkan file `admin/sync_google_trends.php`.
- Menambahkan file `includes/google_trends_sync.php`.
- Menambahkan file `sync_google_trends_cron.php`.
- Mengambil data tren dari Google Trends RSS Indonesia.
- Menyimpan tren baru ke SQLite.
- Mengupdate tren lama jika sudah ada.
- Menyimpan log sinkronisasi.
- Menambahkan tabel `sync_logs` melalui setup dan auto migration.
- Menambahkan histori volume otomatis saat sinkronisasi.
- Menambahkan tombol Sync Sekarang.
- Menambahkan dukungan sync otomatis melalui cron atau Windows Task Scheduler.
- Menambahkan catatan bahwa data RSS bergantung pada koneksi internet dan perubahan dari pihak Google.

### Step 11 sampai Step 14

Step 11 sampai Step 14 belum dibuat sebagai paket ZIP terpisah karena pengembangan langsung dilanjutkan ke Step 15 sesuai permintaan.

Rencana fitur yang belum masuk penuh ke versi final ini:

- Generator Ide Konten SEO Otomatis berbasis AI API.
- Export laporan ke Excel.
- Export laporan ke PDF.
- Manajemen admin lengkap.
- Pengaturan auto sync lewat UI.

Catatan: Halaman detail tren sudah memiliki ide konten otomatis berbasis template. Namun fitur AI API seperti Gemini, OpenRouter, OpenAI, atau Ollama belum diintegrasikan pada versi ini.

### Step 15: Finalisasi tampilan

- Menambahkan landing page baru.
- Memperbarui tampilan dashboard agar lebih modern.
- Merapikan sidebar final.
- Menambahkan dark mode dan light mode.
- Menambahkan tampilan responsive untuk desktop dan mobile.
- Merapikan tabel.
- Merapikan form.
- Menyeragamkan tombol dan badge.
- Menyesuaikan grafik agar mengikuti tema tampilan.
- Memperbarui `admin.css`.
- Memperbarui `app.js`.
- Memperbarui `header.php`, `sidebar.php`, dan `footer.php`.
- Menambahkan `README.md` dan `INSTALL.txt` final.
- Mengecek ulang file PHP agar tidak ada syntax error.

## Fitur yang tersedia di versi Step 15 Final

- Landing page aplikasi.
- Login admin.
- Logout admin.
- Proteksi session admin.
- Dashboard statistik tren.
- Data tren manual.
- Tambah tren.
- Edit tren.
- Detail tren.
- Hapus tren.
- Filter data tren.
- Grafik tren.
- Grafik kategori.
- Grafik status.
- Grafik SEO score.
- Grafik histori tren.
- Sinkronisasi Google Trends RSS Indonesia.
- Log sinkronisasi.
- Histori tren.
- Dark mode dan light mode.
- Responsive layout.
- SQLite auto setup.
- CSRF token pada form penting.

## Catatan fitur AI

Versi ini belum memakai AI API untuk membuat ide konten. Ide konten pada detail tren masih dibuat dengan template otomatis dari PHP. Mode ini gratis dan bisa berjalan tanpa API key.

Jika ingin memakai AI, fitur yang dapat ditambahkan pada tahap berikutnya adalah:

- Gemini API.
- OpenRouter free model.
- Hugging Face Inference Providers.
- Ollama lokal.
- OpenAI API berbayar.

## Cara sync Google Trends

1. Login ke admin.
2. Buka menu Sync Google Trends.
3. Klik tombol Sync Sekarang.
4. Tunggu proses selesai.
5. Cek data pada menu Data Tren atau Dashboard.

Jika ingin menjalankan sync otomatis, gunakan file:

```text
sync_google_trends_cron.php
```

Contoh perintah umum:

```text
php C:\laragon\www\trendwatch\sync_google_trends_cron.php
```

Untuk Windows, perintah tersebut bisa dimasukkan ke Task Scheduler.

## Troubleshooting

### 1. Error 404 Not Found

Penyebab paling sering adalah folder project tidak berada pada path yang sesuai dengan URL.

Struktur yang benar:

```text
C:\laragon\www\trendwatch\setup.php
```

URL yang benar:

```text
http://localhost/trendwatch/setup.php
```

Jika folder ada di:

```text
C:\laragon\www\trend\trendwatch\setup.php
```

URL yang benar:

```text
http://localhost/trend/trendwatch/setup.php
```

### 2. Error `no such table: trends`

Penyebab:

- `setup.php` belum dijalankan.
- File SQLite kosong.
- File SQLite lama rusak.
- Aplikasi membaca database dari folder yang berbeda.

Solusi:

1. Buka `setup.php` sekali.
2. Jika masih error, hapus file berikut:

```text
database/trendwatch.sqlite
```

3. Buka lagi:

```text
http://localhost/trendwatch/setup.php
```

### 3. Error `could not find driver`

Penyebab: ekstensi SQLite belum aktif.

Aktifkan di `php.ini`:

```ini
extension=pdo_sqlite
extension=sqlite3
```

Lalu restart Laragon.

### 4. Sync Google Trends gagal

Penyebab yang mungkin:

- Internet tidak aktif.
- `curl` belum aktif.
- `simplexml` belum aktif.
- RSS Google berubah.
- Google Trends membatasi request.

Solusi:

1. Aktifkan ekstensi:

```ini
extension=simplexml
extension=curl
```

2. Restart Laragon.
3. Coba sync ulang dari halaman admin.

## Rekomendasi pengembangan berikutnya

Fitur yang bisa dibuat setelah Step 15:

1. Generator ide konten SEO berbasis AI.
2. Export laporan ke Excel.
3. Export laporan ke PDF.
4. Manajemen admin lengkap.
5. Pengaturan API key dari halaman admin.
6. Auto sync setting dari halaman admin.
7. Halaman laporan harian dan mingguan.
8. Sistem scoring SEO yang lebih rinci.
9. Caching hasil Google Trends.
10. Backup dan restore database SQLite.

## Catatan keamanan

- Jangan gunakan password default untuk produksi.
- Jangan membuka file `database/trendwatch.sqlite` secara publik pada hosting nyata.
- Tambahkan proteksi folder database melalui `.htaccess` jika aplikasi dipasang di server Apache publik.
- Batasi akses halaman admin hanya untuk pengguna yang login.
- Gunakan HTTPS jika aplikasi diunggah ke hosting.

## Ringkasan versi

```text
Versi: Step 15 Final
Basis: PHP Native + SQLite
Sumber data: manual dan Google Trends RSS
Status AI: belum memakai AI API
Status export: belum tersedia
Status UI: sudah difinalisasi
```
