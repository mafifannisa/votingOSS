<?php
use App\Core\Security;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Backup & Restore Sistem</h2>
        <p class="text-muted mb-0">Kelola cadangan data pemilihan, foto pasangan calon, logo sistem, dan reset kotak suara.</p>
    </div>
    <div>
        <a href="/admin/dashboard" class="btn btn-sm btn-paper-secondary">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- Status Data & Aset Saat Ini -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="paper-card p-3 text-center h-100">
            <span class="text-muted small text-uppercase">Total Pemilih (DPT)</span>
            <h4 class="fw-bold mt-1 mb-0"><?= number_format($totalVoters, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="paper-card p-3 text-center h-100">
            <span class="text-muted small text-uppercase">Pasangan Calon</span>
            <h4 class="fw-bold text-primary mt-1 mb-0"><?= $totalCandidates ?> Paslon</h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="paper-card p-3 text-center h-100">
            <span class="text-muted small text-uppercase">Foto Paslon & Logo</span>
            <h4 class="fw-bold text-info-emphasis mt-1 mb-0"><?= $candidateImgCount ?> Foto / <?= $logoCount ?> Logo</h4>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="paper-card p-3 text-center h-100">
            <span class="text-muted small text-uppercase">Suara Masuk</span>
            <h4 class="fw-bold text-success mt-1 mb-0"><?= number_format($totalVotes, 0, ',', '.') ?> Suara</h4>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- 1. UNDUH CADANGAN (BACKUP) -->
    <div class="col-lg-6">
        <div class="paper-card h-100 p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center mb-3">
                    <div class="p-2 rounded-circle bg-primary-subtle text-primary me-3">
                        <i class="bi bi-cloud-arrow-down-fill fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Cadangkan Sistem (Backup)</h5>
                        <small class="text-muted">Unduh arsip lengkap database dan foto fisik</small>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Fitur cadangan ini mengemas seluruh data pemilihan dan berkas gambar paslon sehingga dapat dipulihkan atau dipindahkan ke server lain secara utuh tanpa risiko foto hilang.
                </p>

                <div class="bg-light p-3 rounded mb-4 border">
                    <div class="fw-semibold small mb-2 text-dark">Isi Paket Cadangan Lengkap:</div>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Seluruh tabel & data database (<code>database.sql</code>)</li>
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Seluruh berkas foto paslon (<strong><?= $candidateImgCount ?></strong> berkas gambar)</li>
                        <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-2"></i>Logo aplikasi & logo footer (<strong><?= $logoCount ?></strong> berkas gambar)</li>
                        <li><i class="bi bi-check-circle-fill text-success me-2"></i>Manifest riwayat sistem (<code>manifest.json</code>)</li>
                    </ul>
                </div>
            </div>

            <div>
                <a href="/admin/backup/export" class="btn btn-paper-primary py-3 fw-bold w-100 mb-2 shadow-sm">
                    <i class="bi bi-file-earmark-zip-fill me-2 fs-5"></i> UNDUH CADANGAN LENGKAP (.ZIP)
                </a>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="text-muted small">Memerlukan berkas SQL saja?</span>
                    <a href="/admin/backup/export?type=sql" class="btn btn-link btn-sm text-decoration-none p-0">
                        <i class="bi bi-filetype-sql me-1"></i> Unduh Hanya Database (.SQL)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. PULIHKAN DATA (RESTORE) -->
    <div class="col-lg-6">
        <div class="paper-card h-100 p-4 p-md-5 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex align-items-center mb-3">
                    <div class="p-2 rounded-circle bg-warning-subtle text-warning-emphasis me-3">
                        <i class="bi bi-arrow-counterclockwise fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">Pulihkan Sistem (Restore)</h5>
                        <small class="text-muted">Kembalikan data dari berkas .ZIP atau .SQL</small>
                    </div>
                </div>

                <p class="text-muted small mb-3">
                    Pilih berkas arsip <code>.zip</code> cadangan lengkap atau berkas <code>.sql</code>. Sistem akan otomatis memulihkan struktur database dan menempatkan foto paslon serta logo ke direktori gambar aslinya.
                </p>

                <div class="bg-warning-subtle p-3 rounded mb-4 border border-warning-subtle text-warning-emphasis small">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>Tips Pemulihan:</strong>
                    Unggah berkas <code>.zip</code> hasil unduhan cadangan lengkap untuk memastikan foto pasangan calon langsung tampil dan tidak hilang.
                </div>
            </div>

            <form action="/admin/backup/restore" method="POST" enctype="multipart/form-data"
                  data-confirm="PERINGATAN: Memulihkan cadangan akan menimpa data dan foto paslon yang ada saat ini. Anda yakin ingin melanjutkan proses restore?"
                  data-confirm-title="Pulihkan Data Sekarang?"
                  data-confirm-btn="Ya, Pulihkan Data"
                  data-confirm-danger="true"
                  data-confirm-icon="warning">
                <?= Security::csrfField() ?>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Pilih Berkas Cadangan (.ZIP atau .SQL):</label>
                    <input type="file" name="backup_file" class="form-control form-control-paper" accept=".zip,.sql" required>
                    <small class="text-muted d-block mt-1">Format yang diterima: <strong>.zip</strong> (Lengkap) atau <strong>.sql</strong> (Database).</small>
                </div>

                <button type="submit" class="btn btn-warning w-100 py-3 fw-bold text-dark shadow-sm">
                    <i class="bi bi-upload me-2"></i> PULIHKAN / RESTORE SEKARANG
                </button>
            </form>
        </div>
    </div>
</div>

<!-- 3. RESET KOTAK SUARA UNTUK GLADI BERSIH / MULAI RESMI -->
<div class="paper-card p-4 mt-4 border-danger-subtle bg-light">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h6 class="fw-bold text-danger mb-1">
                <i class="bi bi-exclamation-octagon-fill me-1"></i> Bersihkan Kotak Suara (Reset Voting)
            </h6>
            <p class="text-muted small mb-0">
                Gunakan fitur ini setelah simulasi / gladi bersih pemilihan selesai untuk mengembalikan suara ke <strong>0</strong> dan mereset status seluruh siswa menjadi <strong>belum memilih</strong>. Data DPT dan data Paslon tetap utuh.
            </p>
        </div>
        <div>
            <form action="/admin/backup/reset-votes" method="POST"
                  data-confirm="PERINGATAN KRITIS: Anda akan menghapus SELURUH suara yang sudah masuk dan mengembalikan status seluruh pemilih menjadi BELUM MEMILIH. Lanjutkan?"
                  data-confirm-title="Kosongkan Kotak Suara?"
                  data-confirm-btn="Ya, Kosongkan Kotak Suara"
                  data-confirm-danger="true"
                  data-confirm-icon="warning">
                <?= Security::csrfField() ?>
                <button type="submit" class="btn btn-outline-danger btn-sm px-3 py-2 fw-bold">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan Kotak Suara
                </button>
            </form>
        </div>
    </div>
</div>
