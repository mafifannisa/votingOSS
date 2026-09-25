<?php
use App\Core\Security;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Daftar Pemilih Tetap (DPT)</h2>
        <p class="text-muted mb-0">Total <?= number_format($totalRecords, 0, ',', '.') ?> siswa terdaftar sebagai pemilih sah.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/voters/import" class="btn btn-paper-success">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Import Excel / CSV
        </a>
        <a href="/admin/voters/create" class="btn btn-paper-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Manual
        </a>
    </div>
</div>

<!-- Voter Stats Badges -->
<div class="row g-2 mb-4">
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
                <select name="limit" class="form-select form-select-sm form-control-paper w-auto" onchange="this.form.submit()">
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
        <table class="table table-hover align-middle mb-0">
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
                            <td class="text-end">
                                <a href="/admin/voters/edit/<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline-secondary me-1">
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

    <!-- Pagination & Data Summary Footer -->
    <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div class="text-muted small">
            Menampilkan <strong><?= $fromRecord ?> - <?= $toRecord ?></strong> dari <strong><?= number_format($totalRecords, 0, ',', '.') ?></strong> data pemilih
            <?php if (!empty($search)): ?>
                <span class="badge bg-light text-dark border ms-1">Pencarian: "<?= Security::escape($search) ?>"</span>
            <?php endif; ?>
        </div>

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
        <?php endif; ?>
    </div>
</div>
