<?php
use App\Core\Security;
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="fw-bold mb-1">Surat Suara Digital</h2>
        <p class="text-muted mb-0">
            Tentukan pilihan Anda dengan cermat. Klik tombol <strong>PILIH PASLON</strong> untuk memberikan suara.
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="p-2 px-3 bg-white border rounded-pill d-inline-block shadow-sm">
            <span class="text-muted small">Pemilih:</span>
            <strong class="text-primary"><?= Security::escape($voter['nama']) ?></strong>
            <span class="badge bg-secondary ms-1"><?= Security::escape($voter['kelas']) ?></span>
        </div>
    </div>
</div>

<?php if (empty($candidates)): ?>
    <div class="paper-card p-5 text-center my-5">
        <i class="bi bi-exclamation-circle text-warning display-4 mb-3"></i>
        <h4>Belum Ada Pasangan Calon Aktif</h4>
        <p class="text-muted">Panitia belum mengaktifkan data pasangan calon pemilihan.</p>
    </div>
<?php else: ?>
    <div class="row g-4 justify-content-center">
        <?php foreach ($candidates as $cand): ?>
            <div class="col-md-6 col-lg-6">
                <div class="paper-card paper-card-hover h-100 d-flex flex-column">
                    <!-- Card Top Header -->
                    <div class="paper-card-header d-flex justify-content-between align-items-center">
                        <span class="text-uppercase small tracking-wide text-muted">Pasangan Calon</span>
                        <div class="paslon-badge">
                            0<?= Security::escape($cand['nomor_urut']) ?>
                        </div>
                    </div>

                    <div class="p-4 d-flex flex-column flex-grow-1">
                        <!-- Candidate Photo -->
                        <div class="candidate-photo-wrapper mb-3">
                            <?php if (!empty($cand['foto'])): ?>
                                <img src="<?= Security::escape($cand['foto']) ?>" alt="Paslon <?= Security::escape($cand['nomor_urut']) ?>" class="candidate-photo">
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="bi bi-people display-1"></i>
                                    <div class="small mt-2">Foto Belum Tersedia</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Candidate Names -->
                        <div class="text-center mb-3">
                            <h4 class="fw-bold mb-1"><?= Security::escape($cand['nama_ketua']) ?></h4>
                            <div class="badge bg-light text-secondary border mb-2">
                                Calon Ketua &bull; <?= Security::escape($cand['jurusan_ketua']) ?>
                            </div>
                            <h5 class="fw-semibold text-secondary mb-1"><?= Security::escape($cand['nama_wakil']) ?></h5>
                            <div class="badge bg-light text-secondary border">
                                Calon Wakil &bull; <?= Security::escape($cand['jurusan_wakil']) ?>
                            </div>
                        </div>

                        <hr class="my-3 text-muted">

                        <!-- Visi & Misi Accordion / Content -->
                        <div class="mb-4 flex-grow-1">
                            <h6 class="fw-bold text-dark text-uppercase small mb-2">
                                <i class="bi bi-lightbulb text-warning me-1"></i> Visi
                            </h6>
                            <p class="small text-muted mb-3 bg-light p-3 rounded border">
                                <?= nl2br(Security::escape($cand['visi'])) ?>
                            </p>

                            <h6 class="fw-bold text-dark text-uppercase small mb-2">
                                <i class="bi bi-check2-circle text-success me-1"></i> Misi
                            </h6>
                            <div class="small text-muted bg-light p-3 rounded border">
                                <?= nl2br(Security::escape($cand['misi'])) ?>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="mt-auto pt-2">
                            <button 
                                type="button" 
                                class="btn btn-paper-vote w-100 py-3" 
                                data-bs-toggle="modal" 
                                data-bs-target="#confirmVoteModal"
                                data-candidate-id="<?= (int)$cand['id'] ?>"
                                data-candidate-number="0<?= Security::escape($cand['nomor_urut']) ?>"
                                data-candidate-names="<?= Security::escape($cand['nama_ketua'] . ' & ' . $cand['nama_wakil']) ?>"
                            >
                                <i class="bi bi-check2-circle me-1"></i> PILIH PASLON 0<?= Security::escape($cand['nomor_urut']) ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal Konfirmasi Pilihan -->
<div class="modal fade" id="confirmVoteModal" tabindex="-1" aria-labelledby="confirmVoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content paper-card p-3 border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="confirmVoteModalLabel">
                    <i class="bi bi-question-circle text-primary me-2"></i> Konfirmasi Pilihan Anda
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <p class="text-muted mb-2">Apakah Anda yakin ingin memberikan suara kepada:</p>
                <div class="display-6 fw-bold text-primary mb-1" id="modalCandNum">01</div>
                <h5 class="fw-bold text-dark mb-3" id="modalCandNames">Nama Pasangan Calon</h5>
                <div class="alert alert-warning border-0 small mb-0">
                    <i class="bi bi-exclamation-triangle me-1"></i> Pilihan Anda tidak dapat diubah setelah Anda menekan tombol konfirmasi.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-paper-secondary px-4" data-bs-dismiss="modal">
                    Batal / Periksa Kembali
                </button>
                <form action="/vote/submit" method="POST" id="voteForm">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="candidate_id" id="modalCandidateId" value="">
                    <button type="submit" class="btn btn-paper-vote px-4">
                        <i class="bi bi-check-lg me-1"></i> Ya, Saya Yakin
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const confirmModal = document.getElementById('confirmVoteModal');
    if (confirmModal) {
        confirmModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const candidateId = button.getAttribute('data-candidate-id');
            const candidateNumber = button.getAttribute('data-candidate-number');
            const candidateNames = button.getAttribute('data-candidate-names');

            document.getElementById('modalCandidateId').value = candidateId;
            document.getElementById('modalCandNum').textContent = 'PASLON ' + candidateNumber;
            document.getElementById('modalCandNames').textContent = candidateNames;
        });
    }
});
</script>
