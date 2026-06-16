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

---

## Step 16 - SEO Content Brief Generator

Step 16 menambahkan fitur **SEO Content Brief Generator**. Fitur ini membuat brief artikel otomatis dari data tren yang sudah ada di database.

### Perubahan utama

- Menambahkan menu baru **SEO Brief** di sidebar admin.
- Menambahkan halaman `admin/content_briefs.php`.
- Menambahkan halaman `admin/content_brief_detail.php`.
- Menambahkan file generator `includes/seo_brief_generator.php`.
- Menambahkan tabel baru `content_briefs` di SQLite.
- Menambahkan tombol **SEO Brief** pada halaman Data Tren.
- Menambahkan tombol **Buat SEO Brief** pada halaman Detail Tren.
- Menambahkan tampilan kartu brief terbaru.
- Menambahkan halaman detail brief yang bisa dicetak.
- Menambahkan fitur regenerate brief.
- Menambahkan fitur copy brief lengkap melalui textarea.

### Output brief yang dibuat

Setiap brief berisi:

- Keyword utama
- Keyword turunan
- Search intent
- Target audiens
- Format konten yang disarankan
- Opsi judul artikel
- Meta description
- Angle konten
- Opening hook
- Outline SEO H1 dan H2
- FAQ
- Ide distribusi konten untuk Instagram, TikTok/Reels, X/Threads, dan blog
- Target jumlah kata
- Skor prioritas konten

### Catatan penting

Generator pada Step 16 memakai **template otomatis PHP**, bukan API AI berbayar. Jadi fitur ini gratis, bisa berjalan lokal, dan tetap bisa dipakai tanpa internet.

Nanti fitur ini masih bisa dikembangkan menjadi mode AI opsional dengan Gemini API, OpenRouter, OpenAI API, atau Ollama lokal.

### Cara memakai fitur SEO Brief

1. Login admin.
2. Buka menu **SEO Brief**.
3. Pilih salah satu tren.
4. Klik **Generate**.
5. Sistem akan membuat brief otomatis.
6. Buka halaman detail brief.
7. Cetak atau salin brief ke dokumen kerja.

### Changelog Step 16

- `config/database.php`: menambahkan schema tabel `content_briefs`.
- `setup.php`: menambahkan proses pembuatan tabel `content_briefs`.
- `check_connection.php`: menambahkan pengecekan tabel `content_briefs`.
- `includes/sidebar.php`: menambahkan menu SEO Brief.
- `includes/seo_brief_generator.php`: menambahkan logika generator brief.
- `admin/content_briefs.php`: halaman daftar dan generator brief.
- `admin/content_brief_detail.php`: halaman detail brief.
- `admin/trends.php`: menambahkan tombol SEO Brief di daftar tren.
- `admin/trend_detail.php`: menambahkan tombol Buat SEO Brief.
- `assets/css/admin.css`: menambahkan style untuk brief card, copy textarea, dan form inline.



## Step 17 - AI Optional Generator

Step 17 menambahkan mode AI opsional untuk fitur SEO Content Brief. Sistem tetap aman karena template gratis masih menjadi fallback utama.

### Fitur Step 17

- Menambahkan menu **AI Optional**.
- Menambahkan halaman `admin/ai_settings.php`.
- Menambahkan file `includes/ai_content_generator.php`.
- Menambahkan tabel `app_settings` untuk menyimpan konfigurasi AI.
- Menambahkan tabel `ai_generation_logs` untuk mencatat aktivitas generate AI.
- Menambahkan pilihan provider:
  - Template Gratis
  - Gemini API
  - OpenAI API
  - OpenRouter
  - Ollama Lokal
  - Custom OpenAI-Compatible Endpoint
- Menambahkan tombol tes koneksi AI.
- Menambahkan fallback otomatis ke template gratis jika AI gagal.
- Menambahkan informasi generator pada detail SEO Brief.
- Menambahkan log respons AI terbaru.
- Menambahkan kolom baru pada `content_briefs`: `generator_source`, `ai_provider`, `ai_status`, dan `ai_message`.

### Cara memakai AI Optional

1. Login sebagai admin.
2. Buka menu **AI Optional**.
3. Aktifkan checkbox **Aktifkan AI Optional**.
4. Pilih provider.
5. Isi model, API key, dan endpoint jika diperlukan.
6. Klik **Simpan Pengaturan**.
7. Klik **Tes AI Sekarang** untuk mengecek koneksi.
8. Buka menu **SEO Brief**.
9. Klik **Generate** atau **Regenerate**.

Jika AI tidak aktif atau request gagal, sistem tetap membuat brief memakai template gratis.

### Catatan keamanan Step 17

API key disimpan di SQLite lokal agar mudah dipakai saat pengembangan. Untuk aplikasi production, simpan API key di file `.env`, environment variable, atau storage yang tidak berada di folder public.

### Changelog Step 17

- `config/database.php`: menambahkan schema `app_settings`, `ai_generation_logs`, dan kolom AI pada `content_briefs`.
- `setup.php`: menambahkan setup tabel dan default setting AI.
- `check_connection.php`: menambahkan pengecekan tabel AI.
- `includes/ai_content_generator.php`: menambahkan integrasi provider AI dan fallback template.
- `includes/seo_brief_generator.php`: menambahkan penyimpanan metadata generator.
- `admin/ai_settings.php`: halaman pengaturan AI optional.
- `admin/content_briefs.php`: memakai generator AI optional saat mode AI aktif.
- `admin/content_brief_detail.php`: memakai generator AI optional saat regenerate dan menampilkan status generator.
- `includes/sidebar.php`: menambahkan menu AI Optional.
- `assets/css/admin.css`: menambahkan style untuk form AI, status, dan preview respons.


## SEO Brief Quality Fix

Update ini memperbaiki kualitas hasil generator SEO Brief, terutama untuk data dari Google Trends RSS. Sistem kini membersihkan keyword turunan dari judul berita panjang, nama situs, URL, dan potongan kalimat yang tidak cocok sebagai keyword. Topik kebencanaan seperti gempa, tsunami, BMKG, banjir, erupsi, dan longsor juga mendapat intent, outline, FAQ, meta description, dan ide platform yang lebih sesuai.

Contoh perbaikan untuk tren `gempa palu`:

- Sebelumnya keyword turunan bisa berisi judul berita panjang dan nama situs.
- Sekarang keyword menjadi lebih bersih, seperti `gempa palu`, `gempa palu hari ini`, `gempa palu terbaru`, `info BMKG gempa palu`, `pusat gempa palu`, `gempa Sulawesi Tengah`, dan `gempa Sigi`.

Catatan: untuk isu bencana, angka aktual seperti magnitudo, korban, dampak, dan lokasi detail harus tetap diverifikasi dari sumber resmi sebelum artikel dipublikasikan.
