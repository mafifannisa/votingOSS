<?php
use App\Core\Security;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Backup & Restore Database</h2>
        <p class="text-muted mb-0">Kelola cadangan data pemilihan, pemulihan database, dan reset kotak suara.</p>
    </div>
    <div>
        <a href="/admin/dashboard" class="btn btn-sm btn-paper-secondary">
            <i class="bi bi-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<!-- Status Data Saat Ini -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="paper-card p-3 text-center">
            <span class="text-muted small text-uppercase">Total Pemilih (DPT)</span>
            <h4 class="fw-bold mt-1 mb-0"><?= number_format($totalVoters, 0, ',', '.') ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="paper-card p-3 text-center">
            <span class="text-muted small text-uppercase">Pasangan Calon</span>
            <h4 class="fw-bold text-primary mt-1 mb-0"><?= $totalCandidates ?> Paslon</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="paper-card p-3 text-center">
            <span class="text-muted small text-uppercase">Suara Masuk Saat Ini</span>
            <h4 class="fw-bold text-success mt-1 mb-0"><?= number_format($totalVotes, 0, ',', '.') ?> Suara</h4>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- 1. UNDUH BACKUP SQL -->
    <div class="col-lg-6">
        <div class="paper-card h-100 p-4 p-md-5 d-flex flex-column">
            <div class="d-flex align-items-center mb-3">
                <div class="p-2 rounded-circle bg-primary-subtle text-primary me-3">
                    <i class="bi bi-cloud-arrow-down-fill fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Cadangkan Database (Backup)</h5>
                    <small class="text-muted">Unduh arsip SQL berisi seluruh tabel & data</small>
                </div>
            </div>

            <p class="text-muted small mb-4 flex-grow-1">
                Sangat disarankan untuk mengunduh cadangan database ini sebelum pemilihan dimulai (setelah data siswa diinput) dan setelah rekapitulasi pemilihan selesai.
            </p>

            <a href="/admin/backup/export" class="btn btn-paper-primary py-3 fw-bold">
                <i class="bi bi-download me-2"></i> UNDUH CADANGAN (.SQL)
            </a>
        </div>
    </div>

    <!-- 2. RESTORE DARI FILE SQL -->
    <div class="col-lg-6">
        <div class="paper-card h-100 p-4 p-md-5 d-flex flex-column">
            <div class="d-flex align-items-center mb-3">
                <div class="p-2 rounded-circle bg-warning-subtle text-warning-emphasis me-3">
                    <i class="bi bi-arrow-counterclockwise fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0">Pulihkan Database (Restore)</h5>
                    <small class="text-muted">Kembalikan data dari berkas .sql cadangan</small>
                </div>
            </div>

            <p class="text-muted small mb-3">
                Pilih berkas cadangan <code>.sql</code> yang sebelumnya diunduh untuk memulihkan seluruh struktur dan data database.
            </p>

            <form action="/admin/backup/restore" method="POST" enctype="multipart/form-data"
                  data-confirm="PERINGATAN: Memulihkan database akan menimpa data yang ada saat ini dengan data cadangan. Anda yakin ingin melanjutkan proses restore?"
                  data-confirm-title="Pulihkan Database Sekarang?"
                  data-confirm-btn="Ya, Pulihkan Database"
                  data-confirm-danger="true"
                  data-confirm-icon="warning">
                <?= Security::csrfField() ?>

                <div class="mb-3">
                    <input type="file" name="backup_file" class="form-control form-control-paper" accept=".sql" required>
                </div>

                <button type="submit" class="btn btn-warning w-100 py-3 fw-bold text-dark">
                    <i class="bi bi-upload me-2"></i> PULIHKAN / RESTORE DATABASE
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
