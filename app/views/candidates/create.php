<?php
use App\Core\Security;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">Tambah Pasangan Calon Baru</h3>
            <a href="/admin/candidates" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="paper-card p-4 p-md-5">
            <form action="/admin/candidates/store" method="POST" enctype="multipart/form-data">
                <?= Security::csrfField() ?>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="nomor_urut" class="form-label fw-bold">Nomor Urut <span class="text-danger">*</span></label>
                        <input type="number" name="nomor_urut" id="nomor_urut" class="form-control form-control-paper" min="1" max="99" required placeholder="Contoh: 1">
                    </div>
                    <div class="col-md-8">
                        <label for="foto" class="form-label fw-bold">Foto Paslon (JPG, PNG, WEBP max 2MB)</label>
                        <input type="file" name="foto" id="foto" class="form-control form-control-paper" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>

                <div class="card p-3 mb-4 border bg-light">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-1"></i> Data Calon Ketua</h6>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="nama_ketua" class="form-label fw-bold">Nama Lengkap Ketua <span class="text-danger">*</span></label>
                            <input type="text" name="nama_ketua" id="nama_ketua" class="form-control form-control-paper" required placeholder="Nama Ketua">
                        </div>
                        <div class="col-md-5">
                            <label for="jurusan_ketua" class="form-label fw-bold">Jurusan / Kelas Ketua <span class="text-danger">*</span></label>
                            <input type="text" name="jurusan_ketua" id="jurusan_ketua" class="form-control form-control-paper" required placeholder="Contoh: XI RPL 1">
                        </div>
                    </div>
                </div>

                <div class="card p-3 mb-4 border bg-light">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person me-1"></i> Data Calon Wakil Ketua</h6>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="nama_wakil" class="form-label fw-bold">Nama Lengkap Wakil <span class="text-danger">*</span></label>
                            <input type="text" name="nama_wakil" id="nama_wakil" class="form-control form-control-paper" required placeholder="Nama Wakil">
                        </div>
                        <div class="col-md-5">
                            <label for="jurusan_wakil" class="form-label fw-bold">Jurusan / Kelas Wakil <span class="text-danger">*</span></label>
                            <input type="text" name="jurusan_wakil" id="jurusan_wakil" class="form-control form-control-paper" required placeholder="Contoh: XI DKV 2">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="visi" class="form-label fw-bold">Visi <span class="text-danger">*</span></label>
                    <textarea name="visi" id="visi" rows="3" class="form-control form-control-paper" required placeholder="Tuliskan visi pasangan calon..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="misi" class="form-label fw-bold">Misi <span class="text-danger">*</span></label>
                    <textarea name="misi" id="misi" rows="4" class="form-control form-control-paper" required placeholder="Tuliskan poin-poin misi pasangan calon..."></textarea>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="status" name="status" value="1" checked>
                    <label class="form-check-label fw-semibold" for="status">Pasangan Calon Aktif (tampil di surat suara)</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/admin/candidates" class="btn btn-paper-secondary">Batal</a>
                    <button type="submit" class="btn btn-paper-primary">
                        <i class="bi bi-save me-1"></i> Simpan Paslon
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
