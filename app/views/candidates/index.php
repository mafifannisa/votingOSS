<?php
use App\Core\Security;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold mb-1">Daftar Pasangan Calon</h2>
        <p class="text-muted mb-0">Kelola informasi kandidat calon Ketua dan Wakil Ketua OSIS.</p>
    </div>
    <div>
        <a href="/admin/candidates/create" class="btn btn-paper-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Paslon Baru
        </a>
    </div>
</div>

<?php if (empty($candidates)): ?>
    <div class="paper-card p-5 text-center my-4">
        <i class="bi bi-people display-4 text-muted mb-3 d-block"></i>
        <h4>Belum Ada Paslon</h4>
        <p class="text-muted mb-4">Tambahkan data pasangan calon untuk memulai pemilihan.</p>
        <a href="/admin/candidates/create" class="btn btn-paper-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Paslon Sekarang
        </a>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($candidates as $cand): ?>
            <div class="col-md-6 col-lg-4">
                <div class="paper-card h-100 d-flex flex-column">
                    <div class="paper-card-header d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary fs-6 px-3 py-1">
                            Nomor 0<?= Security::escape($cand['nomor_urut']) ?>
                        </span>
                        <?php if ((int)$cand['status'] === 1): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                        <?php endif; ?>
                    </div>

                    <div class="p-3 text-center">
                        <div class="candidate-photo-wrapper mb-3" style="max-height: 180px;">
                            <?php if (!empty($cand['foto'])): ?>
                                <img src="<?= Security::escape($cand['foto']) ?>" alt="Foto Paslon" class="candidate-photo">
                            <?php else: ?>
                                <i class="bi bi-person-circle display-4 text-muted"></i>
                            <?php endif; ?>
                        </div>

                        <h5 class="fw-bold mb-0"><?= Security::escape($cand['nama_ketua']) ?></h5>
                        <small class="text-muted d-block mb-2"><?= Security::escape($cand['jurusan_ketua']) ?></small>

                        <h6 class="fw-semibold text-secondary mb-0"><?= Security::escape($cand['nama_wakil']) ?></h6>
                        <small class="text-muted d-block mb-3"><?= Security::escape($cand['jurusan_wakil']) ?></small>

                        <div class="text-start bg-light p-3 rounded small mb-3 border">
                            <strong>Visi:</strong>
                            <p class="mb-1 text-muted text-truncate"><?= Security::escape($cand['visi']) ?></p>
                        </div>
                    </div>

                    <div class="mt-auto p-3 border-top bg-light d-flex justify-content-end gap-2">
                        <a href="/admin/candidates/edit/<?= (int)$cand['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <form action="/admin/candidates/delete/<?= (int)$cand['id'] ?>" method="POST"
                              data-confirm="Apakah Anda yakin ingin menghapus paslon 0<?= Security::escape($cand['nomor_urut']) ?> (<?= Security::escape($cand['nama_ketua']) ?>)? Semua perolehan suara untuk paslon ini juga akan terhapus."
                              data-confirm-title="Hapus Pasangan Calon?"
                              data-confirm-btn="Ya, Hapus Paslon"
                              data-confirm-danger="true">
                            <?= Security::csrfField() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
