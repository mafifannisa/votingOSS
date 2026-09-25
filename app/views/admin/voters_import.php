<?php
use App\Core\Security;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="fw-bold mb-0">Import Data Pemilih (DPT)</h3>
            <a href="/admin/voters" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
        </div>

        <div class="paper-card p-4 p-md-5 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-1">Unggah Berkas Excel / CSV</h5>
                    <p class="text-muted small mb-0">
                        Format yang didukung: <strong>.xlsx</strong> (Excel) dan <strong>.csv</strong>
                    </p>
                </div>
                <a href="/admin/voters/template" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download me-1"></i> Unduh Contoh Template CSV
                </a>
            </div>

            <form action="/admin/voters/import/process" method="POST" enctype="multipart/form-data">
                <?= Security::csrfField() ?>

                <div class="mb-4">
                    <label for="file" class="form-label fw-bold">Pilih Berkas Excel/CSV <span class="text-danger">*</span></label>
                    <input type="file" name="file" id="file" class="form-control form-control-paper p-3" accept=".xlsx,.csv" required>
                </div>

                <div class="alert alert-info border-0 small mb-4">
                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i> Ketentuan Struktur Kolom:</h6>
                    <ul class="mb-0 ps-3">
                        <li>File wajib memiliki header pada baris pertama dengan kolom: <code>nisn</code>, <code>nama</code>, <code>kelas</code>, <code>jurusan</code>.</li>
                        <li>Nilai NISN harus berupa angka unik (8-15 digit). Baris tanpa NISN akan otomatis dilewati.</li>
                        <li>Jika NISN sudah ada di database, data nama/kelas/jurusan akan diperbarui (*update*). Jika belum, data baru akan ditambahkan (*insert*).</li>
                        <li>Sistem secara otomatis meng-hash NISN menggunakan HMAC-SHA256 sebelum disimpan ke database.</li>
                    </ul>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="/admin/voters" class="btn btn-paper-secondary">Batal</a>
                    <button type="submit" class="btn btn-paper-primary px-4">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Mulai Proses Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
