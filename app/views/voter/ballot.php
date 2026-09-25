<?php
use App\Core\Security;
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8 d-flex align-items-center gap-3">
        <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 58px; width: auto; object-fit: contain;" class="d-none d-sm-block">
        <div>
            <h2 class="fw-bold mb-1">Surat Suara Digital</h2>
            <p class="text-muted mb-0">
                Tentukan pilihan Anda dengan cermat. Klik tombol <strong>PILIH PASLON</strong> untuk memberikan suara.
            </p>
        </div>
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
                                class="btn btn-paper-vote w-100 py-3 btn-vote-action" 
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

<!-- Form Submit Suara Pemilih -->
<form action="/vote/submit" method="POST" id="voteForm" class="d-none">
    <?= Security::csrfField() ?>
    <input type="hidden" name="candidate_id" id="modalCandidateId" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-vote-action').forEach(btn => {
        btn.addEventListener('click', () => {
            const candidateId = btn.getAttribute('data-candidate-id');
            const candidateNumber = btn.getAttribute('data-candidate-number');
            const candidateNames = btn.getAttribute('data-candidate-names');

            if (window.SwalPaper) {
                window.SwalPaper.fire({
                    title: 'Konfirmasi Pilihan Anda',
                    html: `
                        <div class="py-2 text-center">
                            <p class="text-muted mb-3">Apakah Anda yakin ingin memberikan suara kepada:</p>
                            <div class="paslon-badge mb-3 mx-auto" style="width: 58px; height: 58px; font-size: 1.5rem;">
                                ${candidateNumber}
                            </div>
                            <h4 class="fw-bold text-dark mb-3">${candidateNames}</h4>
                            <div class="paper-swal-alert-warning p-3 rounded small text-start border">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>
                                <strong>PENTING:</strong> Pilihan Anda bersifat final dan <strong>tidak dapat diubah</strong> setelah Anda menekan tombol konfirmasi.
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Ya, Saya Yakin',
                    cancelButtonText: 'Batal / Periksa Kembali',
                    reverseButtons: true,
                    customClass: {
                        popup: 'paper-swal-popup',
                        confirmButton: 'swal2-confirm btn-paper-vote px-4 py-2 me-2',
                        cancelButton: 'swal2-cancel btn-paper-secondary px-4 py-2'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('modalCandidateId').value = candidateId;
                        document.getElementById('voteForm').submit();
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin memberikan suara kepada Paslon ' + candidateNumber + ' (' + candidateNames + ')? Pilihan tidak dapat diubah.')) {
                    document.getElementById('modalCandidateId').value = candidateId;
                    document.getElementById('voteForm').submit();
                }
            }
        });
    });
});
</script>
