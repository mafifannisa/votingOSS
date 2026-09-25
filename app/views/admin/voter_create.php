<?php
use App\Core\Security;
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">Tambah Pemilih Manual</h3>
            <a href="/admin/voters" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="paper-card p-4 p-md-5">
            <form action="/admin/voters/store" method="POST">
                <?= Security::csrfField() ?>

                <div class="mb-3">
                    <label for="nisn" class="form-label fw-bold">NISN (10 Digit Angka) <span class="text-danger">*</span></label>
                    <input type="text" name="nisn" id="nisn" class="form-control form-control-paper" placeholder="Contoh: 0051234567" pattern="[0-9]{8,15}" required autofocus>
                    <div class="form-text small">NISN akan di-hash secara aman menggunakan algoritma HMAC-SHA256.</div>
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label fw-bold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control form-control-paper" placeholder="Nama Siswa" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="kelas" class="form-label fw-bold">Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="kelas" id="kelas" class="form-control form-control-paper" placeholder="Contoh: X RPL 1" required>
                    </div>
                    <div class="col-md-6">
                        <label for="jurusan" class="form-label fw-bold">Jurusan <span class="text-danger">*</span></label>
                        <input type="text" name="jurusan" id="jurusan" class="form-control form-control-paper" placeholder="Contoh: Rekayasa Perangkat Lunak" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/admin/voters" class="btn btn-paper-secondary">Batal</a>
                    <button type="submit" class="btn btn-paper-primary">
                        <i class="bi bi-save me-1"></i> Simpan Data Pemilih
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
