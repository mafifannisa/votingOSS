# Aplikasi E-Voting Pemilihan Ketua OSIS
### Tema Desain Antarmuka: Paper Card

Aplikasi sistem E-Voting Pemilihan Ketua & Wakil Ketua OSIS berbasis web yang dirancang khusus untuk berjalan cepat, aman, dan mudah digunakan pada jaringan lokal (LAN / Wi-Fi) dengan dukungan **8 perangkat bersamaan** (4 tablet pemilih + 4 laptop panitia).

---

## 🚀 Fitur Utama

1. **Bilik Suara Digital (Pemilih):**
   * **Pemindai Barcode / QR Webcam:** Pemilih dapat langsung menghadapkan barcode/QR kartu pelajar ke kamera webcam untuk login otomatis (*auto-detect* + feedback audio *beep*).
   * **Input Manual NISN:** Pilihan fleksibel menggunakan keyboard atau keypad angka.
   * Perlindungan **Anti-Double-Voting** tingkat database (transaksi atomik).
   * Desain kartu suara bertema **Paper Card** (responsif untuk tablet & laptop).
   * Dialog konfirmasi pilihan untuk mencegah salah klik.
   * Pemusnahan sesi otomatis (*auto-logout*) segera setelah suara tersimpan.

2. **Panel Panitia (Admin):**
   * Autentikasi aman panitia pemilihan.
   * **Manajemen Pasangan Calon (CRUD):** Nomor urut, data ketua & wakil, visi, misi, dan upload foto paslon.
   * **Manajemen DPT & Import Excel/CSV:** Unggah file `.xlsx` / `.csv` langsung tanpa dependensi pihak ketiga yang rumit. Tersedia fitur unduh template.
   * **Dashboard Statistik Real-time:** Menampilkan total DPT, jumlah sudah memilih, belum memilih, dan persentase partisipasi.

3. **Pleno Pengumuman Hasil Pemilihan:**
   * Terkunci dengan **Kode Akses** resmi panitia (*password hashed*).
   * Animasi penghitungan suara (*countdown*) **60 detik** dramatis sebelum hasil ditampilkan.
   * Visualisasi persentase suara dan penanda pemenang secara otomatis.

4. **Keamanan & Integritas Data (Defense in Depth):**
   * **Secret Ballot (Kerahasiaan Suara):** Tabel identitas pemilih (`voters`) dan tabel kotak suara (`votes`) dipisahkan secara struktural.
   * **HMAC-SHA256 untuk NISN:** NISN di-hash secara aman untuk lookup tanpa menyimpan plaintext NISN.
   * **100% Prepared Statements / Parameterized Queries:** Bebas dari celah SQL Injection.
   * **CSRF Token:** Diterapkan di semua mutasi data dan voting.
   * **Session Fixation Prevention:** Regenerasi ID sesi secara berkala.

---

## 🛠️ Kredensial Default

* **Panel Admin:**
  * URL: `/admin/login`
  * Username: `admin`
  * Password: `admin123`
* **Kode Akses Hasil Pemilihan:**
  * Kode: `osis2026`
* **Sample Data Pemilih (DPT) untuk Uji Coba Langsung:**
  * NISN `0051234567` (Ahmad Pratama - X RPL 1)
  * NISN `0051234568` (Budi Santoso - XI TKJ 2)
  * NISN `0051234569` (Citra Lestari - XII DKV 1)
  * NISN `0051234570` (Dinda Kirana - X AKL 2)

---

## 💻 Cara Menjalankan Aplikasi

### 1. Menggunakan PHP Built-in Server (Sangat Praktis & Cepat)
Jalankan perintah berikut di terminal pada folder proyek:
```bash
php -S 0.0.0.0:8000 -t public
```
Aplikasi dapat diakses melalui:
* Komputer server: `http://localhost:8000`
* Tablet / Laptop lain di jaringan Wi-Fi yang sama: `http://<IP_SERVER_LOKAL>:8000` (contoh: `http://192.168.1.50:8000`)

### 2. Menggunakan Apache / XAMPP
* Arahkan *DocumentRoot* atau VirtualHost ke folder `public/`.
* Modul `mod_rewrite` Apache otomatis bekerja membaca `.htaccess` yang telah disediakan.

---

## 📂 Struktur Folder MVC

```text
VotingOSS/
├── app/
│   ├── controllers/      # AuthController, VotingController, CandidateController, VoterController, DashboardController, ResultController
│   ├── core/             # Controller, Model, Database (PDO Singleton), Router, Security, Session, ExcelParser
│   ├── models/           # Admin, Candidate, Election, Vote, Voter
│   └── views/            # Template Paper Card (auth, voter, admin, candidates, results, layouts)
├── config/               # app.php, database.php, security.php
├── database/             # schema.sql, seed.php
├── public/               # index.php (Front Controller), assets (css, js, images)
├── routes/               # web.php
└── storage/              # uploads, logs
```
