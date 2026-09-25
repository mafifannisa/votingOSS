<?php
use App\Core\Security;
?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">Edit Data Pemilih</h3>
            <a href="/admin/voters" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="paper-card p-4 p-md-5">
            <form action="/admin/voters/update/<?= (int)$voter['id'] ?>" method="POST">
                <?= Security::csrfField() ?>

                <div class="alert alert-light border small mb-4">
                    <i class="bi bi-shield-check text-success me-1"></i> NISN tersimpan dalam format hash HMAC yang aman dan tidak dapat diubah demi menjaga integritas data pemilih.
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label fw-bold">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control form-control-paper" value="<?= Security::escape($voter['nama']) ?>" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="kelas" class="form-label fw-bold">Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="kelas" id="kelas" class="form-control form-control-paper" value="<?= Security::escape($voter['kelas']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="jurusan" class="form-label fw-bold">Jurusan <span class="text-danger">*</span></label>
                        <input type="text" name="jurusan" id="jurusan" class="form-control form-control-paper" value="<?= Security::escape($voter['jurusan']) ?>" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/admin/voters" class="btn btn-paper-secondary">Batal</a>
                    <button type="submit" class="btn btn-paper-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
