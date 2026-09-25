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
            <?php if ((int)$voter['has_voted'] === 1): ?>
                <div class="card bg-warning-subtle border-warning-subtle mb-4 shadow-sm">
                    <div class="card-body py-2.5 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="badge bg-success mb-1">
                                <i class="bi bi-check-circle me-1"></i> Sudah Memilih
                            </span>
                            <div class="small text-dark">Siswa ini telah menggunakan hak suaranya.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark border-warning fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalResetEditVoter">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Hak Pilih
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="card bg-light border-0 mb-4">
                    <div class="card-body py-2.5 px-3 d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            <i class="bi bi-clock me-1"></i> Belum Memilih
                        </span>
                        <span class="small text-muted">Siswa ini belum menggunakan hak suaranya.</span>
                    </div>
                </div>
            <?php endif; ?>

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

<?php if ((int)$voter['has_voted'] === 1): ?>
<!-- Modal Reset Hak Pilih Siswa Ini -->
<div class="modal fade" id="modalResetEditVoter" tabindex="-1" aria-labelledby="modalResetEditVoterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning-subtle text-warning-emphasis p-2 rounded-3 d-inline-flex">
                        <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalResetEditVoterLabel">Reset Hak Pilih Siswa</h5>
                        <small class="text-muted">Kembalikan hak suara <?= Security::escape($voter['nama']) ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="/admin/voters/reset/<?= (int)$voter['id'] ?>">
                <?= Security::csrfField() ?>
                <input type="hidden" name="redirect_to" value="/admin/voters/edit/<?= (int)$voter['id'] ?>">
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-warning border-0 small d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6 mt-0.5 text-warning flex-shrink-0"></i>
                        <div>
                            Tindakan ini akan mengembalikan status pemilih <strong><?= Security::escape($voter['nama']) ?></strong> (<?= Security::escape($voter['kelas']) ?>) menjadi <strong>Belum Memilih</strong> sehingga dapat login dan memberikan suara kembali.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resetEditAccessCode" class="form-label fw-bold small text-uppercase text-secondary">
                            Kode Akses Keamanan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="access_code" 
                                   id="resetEditAccessCode" 
                                   class="form-control form-control-paper" 
                                   placeholder="Masukkan kode akses (osis2026)" 
                                   required 
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="resetEditAccessCode" title="Lihat / Sembunyikan Kode">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text small text-muted">
                            <i class="bi bi-shield-lock me-1"></i>Ketik <strong>osis2026</strong> untuk mengonfirmasi reset status.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-paper-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Konfirmasi Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            const icon = btn.querySelector('i');
            if (targetInput && icon) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    icon.className = 'bi bi-eye-slash';
                } else {
                    targetInput.type = 'password';
                    icon.className = 'bi bi-eye';
                }
            }
        });
    });
});
</script>
<?php endif; ?>
