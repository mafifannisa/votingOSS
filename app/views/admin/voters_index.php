<?php
use App\Core\Security;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Daftar Pemilih Tetap (DPT)</h2>
        <p class="text-muted mb-0">Total <?= number_format($totalRecords, 0, ',', '.') ?> siswa terdaftar sebagai pemilih sah.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/admin/voters/print" target="_blank" class="btn btn-paper-outline-primary" title="Cetak Kartu Pemilih DPT dengan QR Code (Format Kotak / Square Siap Gunting)">
            <i class="bi bi-printer me-1"></i> Cetak Kartu DPT
        </a>
        <a href="/admin/voters/import" class="btn btn-paper-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Excel / CSV
        </a>
        <a href="/admin/voters/create" class="btn btn-paper-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Manual
        </a>
    </div>
</div>

<!-- Voter Stats Badges -->
<div class="row g-2 mb-4 align-items-center">
    <div class="col-auto">
        <div class="badge bg-light text-dark border p-2 px-3 fs-6">
            Total DPT: <strong class="text-primary"><?= number_format($totalRecords, 0, ',', '.') ?></strong>
        </div>
    </div>
    <div class="col-auto">
        <div class="badge bg-light text-dark border p-2 px-3 fs-6">
            Sudah Memilih: <strong class="text-success"><?= number_format($totalVoted, 0, ',', '.') ?></strong>
        </div>
    </div>
    <div class="col-auto">
        <div class="badge bg-light text-dark border p-2 px-3 fs-6">
            Belum Memilih: <strong class="text-danger"><?= number_format($totalNotVoted, 0, ',', '.') ?></strong>
        </div>
    </div>
    <?php if ($totalVoted > 0): ?>
        <div class="col-auto ms-sm-auto">
            <button type="button" class="btn btn-sm btn-outline-warning text-dark border-warning fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalResetAllVoters" title="Reset status hak pilih seluruh pemilih kembali ke belum memilih">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Semua Hak Pilih
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
$fromRecord = $totalRecords > 0 ? (($page - 1) * $limit) + 1 : 0;
$toRecord = min(($page - 1) * $limit + count($voters), $totalRecords);
?>

<div class="paper-card p-4">
    <!-- Search and Per-Page Filter Form -->
    <form action="/admin/voters" method="GET" class="row g-2 mb-4 align-items-center">
        <div class="col-md-6 col-lg-5">
            <div class="input-group">
                <input type="text" name="q" class="form-control form-control-paper" placeholder="Cari nama siswa, kelas, atau jurusan..." value="<?= Security::escape($search ?? '') ?>">
                <button type="submit" class="btn btn-paper-secondary">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </div>
        <div class="col-md-6 col-lg-7 d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-1">
                <span class="text-muted small">Tampilkan:</span>
                <select name="limit" class="form-select form-select-sm form-select-paper w-auto" onchange="this.form.submit()">
                    <option value="10" <?= $limit === 10 ? 'selected' : '' ?>>10 baris</option>
                    <option value="25" <?= $limit === 25 ? 'selected' : '' ?>>25 baris</option>
                    <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50 baris</option>
                    <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100 baris</option>
                </select>
            </div>
            <?php if (!empty($search)): ?>
                <a href="/admin/voters?limit=<?= $limit ?>" class="btn btn-sm btn-link text-muted text-decoration-none">
                    <i class="bi bi-x-circle me-1"></i> Reset Pencarian
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Voters Table -->
    <div class="table-responsive">
        <table class="table table-hover table-paper align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Jurusan</th>
                    <th>Status Hak Suara</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($voters)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox display-6 d-block mb-2"></i>
                            Tidak ada data pemilih yang sesuai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = (($page - 1) * $limit) + 1;
                    foreach ($voters as $v): 
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="fw-bold"><?= Security::escape($v['nama']) ?></td>
                            <td><?= Security::escape($v['kelas']) ?></td>
                            <td><?= Security::escape($v['jurusan']) ?></td>
                            <td>
                                <?php if ((int)$v['has_voted'] === 1): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Sudah Memilih
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                        <i class="bi bi-clock me-1"></i> Belum Memilih
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap">
                                <?php if ((int)$v['has_voted'] === 1): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning text-dark me-1 btn-trigger-reset-single"
                                            data-voter-id="<?= (int)$v['id'] ?>"
                                            data-voter-nama="<?= Security::escape($v['nama']) ?>"
                                            data-voter-kelas="<?= Security::escape($v['kelas']) ?>"
                                            title="Reset Hak Pilih Siswa Ini">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                <?php endif; ?>
                                <a href="/admin/voters/edit/<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline-secondary me-1" title="Edit Data Pemilih">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/admin/voters/delete/<?= (int)$v['id'] ?>" method="POST" class="d-inline"
                                      data-confirm="Apakah Anda yakin ingin menghapus data pemilih <?= Security::escape($v['nama']) ?> (<?= Security::escape($v['kelas']) ?>)?"
                                      data-confirm-title="Hapus Data Pemilih?"
                                      data-confirm-btn="Ya, Hapus"
                                      data-confirm-danger="true">
                                    <?= Security::csrfField() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pemilih">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination & Data Summary Footer Toolbar -->
    <div class="paper-table-footer">
        <div class="d-flex align-items-center gap-2 text-muted small flex-wrap">
            <span class="badge bg-white text-dark border px-2 py-1 shadow-sm">
                <i class="bi bi-people-fill text-primary me-1"></i> Data Pemilih
            </span>
            <span>
                Menampilkan <strong><?= $fromRecord ?> – <?= $toRecord ?></strong> dari <strong><?= number_format($totalRecords, 0, ',', '.') ?></strong> siswa
            </span>
            <?php if (!empty($search)): ?>
                <span class="badge bg-white text-secondary border ms-1">Filter: "<?= Security::escape($search) ?>"</span>
            <?php endif; ?>
        </div>

        <div>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navigasi Halaman Data Pemilih">
                    <ul class="pagination pagination-sm pagination-paper mb-0">
                        <?php
                        $urlForPage = function(int $targetPage) use ($search, $limit): string {
                            $params = ['page' => $targetPage];
                            if (!empty($search)) $params['q'] = $search;
                            if ($limit !== 25) $params['limit'] = $limit;
                            return '/admin/voters?' . http_build_query($params);
                        };
                        ?>

                        <!-- Tombol Halaman Sebelumnya -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $page > 1 ? $urlForPage($page - 1) : '#' ?>" aria-label="Sebelumnya" <?= $page <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                <i class="bi bi-chevron-left me-1"></i> Sebelumnya
                            </a>
                        </li>

                        <!-- Nomor Halaman -->
                        <?php 
                        $startP = max(1, $page - 2);
                        $endP = min($totalPages, $page + 2);
                        if ($startP > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $urlForPage(1) . '">1</a></li>';
                            if ($startP > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }

                        for ($p = $startP; $p <= $endP; $p++): 
                        ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $urlForPage($p) ?>"><?= $p ?></a>
                            </li>
                        <?php 
                        endfor; 

                        if ($endP < $totalPages) {
                            if ($endP < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="' . $urlForPage($totalPages) . '">' . $totalPages . '</a></li>';
                        }
                        ?>

                        <!-- Tombol Halaman Berikutnya -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $page < $totalPages ? $urlForPage($page + 1) : '#' ?>" aria-label="Berikutnya" <?= $page >= $totalPages ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                                Berikutnya <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php else: ?>
                <span class="badge bg-white text-muted border px-2.5 py-1.5 fw-normal small shadow-sm">
                    <i class="bi bi-check2-circle text-success me-1"></i> Halaman 1 dari 1 (Semua DPT)
                </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================================================
     MODAL 1: RESET STATUS HAK PILIH SISWA (INDIVIDU)
     ======================================================================== -->
<div class="modal fade" id="modalResetSingleVoter" tabindex="-1" aria-labelledby="modalResetSingleVoterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning-subtle text-warning-emphasis p-2 rounded-3 d-inline-flex">
                        <i class="bi bi-arrow-counterclockwise fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalResetSingleVoterLabel">Reset Hak Pilih Siswa</h5>
                        <small class="text-muted">Kembalikan status agar siswa dapat memilih ulang</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form id="formResetSingleVoter" method="POST" action="">
                <?= Security::csrfField() ?>
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-warning border-0 small d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6 mt-0.5 text-warning flex-shrink-0"></i>
                        <div>
                            Tindakan ini akan mengembalikan status <strong id="resetSingleVoterName" class="text-dark">-</strong> (<span id="resetSingleVoterKelas">-</span>) menjadi <strong>Belum Memilih</strong>. Siswa tersebut dapat login dan memberikan suara kembali.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resetSingleAccessCode" class="form-label fw-bold small text-uppercase text-secondary">
                            Kode Akses Keamanan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="access_code" 
                                   id="resetSingleAccessCode" 
                                   class="form-control form-control-paper" 
                                   placeholder="Masukkan kode akses (osis2026)" 
                                   required 
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="resetSingleAccessCode" title="Lihat / Sembunyikan Kode">
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

<!-- ========================================================================
     MODAL 2: RESET STATUS HAK PILIH SEMUA SISWA (MASSAL)
     ======================================================================== -->
<div class="modal fade" id="modalResetAllVoters" tabindex="-1" aria-labelledby="modalResetAllVotersLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-danger-subtle text-danger p-2 rounded-3 d-inline-flex">
                        <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0" id="modalResetAllVotersLabel">Reset Semua Hak Pilih</h5>
                        <small class="text-muted">Kembalikan seluruh pemilih menjadi Belum Memilih</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="/admin/voters/reset-all">
                <?= Security::csrfField() ?>
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-danger border-0 small d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6 mt-0.5 text-danger flex-shrink-0"></i>
                        <div>
                            <strong>Peringatan Keamanan:</strong> Tindakan ini akan mengembalikan status <strong><?= number_format($totalVoted, 0, ',', '.') ?> siswa</strong> yang sudah memilih menjadi <strong>Belum Memilih</strong>. Fitur ini cocok digunakan untuk persiapan simulasi atau gladi resik sebelum pemilu resmi dimulai.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="resetAllAccessCode" class="form-label fw-bold small text-uppercase text-secondary">
                            Kode Akses Keamanan <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="access_code" 
                                   id="resetAllAccessCode" 
                                   class="form-control form-control-paper" 
                                   placeholder="Masukkan kode akses (osis2026)" 
                                   required 
                                   autocomplete="current-password">
                            <button class="btn btn-outline-secondary btn-toggle-password" type="button" data-target="resetAllAccessCode" title="Lihat / Sembunyikan Kode">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="form-text small text-muted">
                            <i class="bi bi-shield-lock me-1"></i>Ketik <strong>osis2026</strong> untuk mengonfirmasi reset seluruh pemilih.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-paper-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold px-3">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Ya, Reset Semua Hak Pilih
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script Interaksi Modal Reset & Password Toggle -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const resetModalEl = document.getElementById('modalResetSingleVoter');
    const resetModal = resetModalEl && typeof bootstrap !== 'undefined' ? new bootstrap.Modal(resetModalEl) : null;
    const formReset = document.getElementById('formResetSingleVoter');
    const nameEl = document.getElementById('resetSingleVoterName');
    const classEl = document.getElementById('resetSingleVoterKelas');
    const inputCode = document.getElementById('resetSingleAccessCode');

    // Trigger tombol reset per siswa di tabel
    document.querySelectorAll('.btn-trigger-reset-single').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-voter-id');
            const nama = btn.getAttribute('data-voter-nama');
            const kelas = btn.getAttribute('data-voter-kelas');

            if (formReset) formReset.action = `/admin/voters/reset/${id}`;
            if (nameEl) nameEl.textContent = nama;
            if (classEl) classEl.textContent = kelas;
            if (inputCode) inputCode.value = '';

            if (resetModal) {
                resetModal.show();
                setTimeout(() => {
                    if (inputCode) inputCode.focus();
                }, 350);
            }
        });
    });

    // Toggle Lihat / Sembunyikan Kode Akses
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
