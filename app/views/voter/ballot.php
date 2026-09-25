<?php
use App\Core\Security;

$candidateCount = count($candidates);
?>

<script>
document.body.classList.add('ballot-kiosk-active');
</script>

<div class="ballot-kiosk-wrapper">
    <!-- 1. Header Kiosk Surat Suara -->
    <header class="ballot-kiosk-header">
        <div class="d-flex align-items-center gap-2.5">
            <img src="/assets/images/Logo_OSIS.svg" alt="OSIS" style="height: 38px; width: auto; object-fit: contain;">
            <div>
                <h4 class="fw-bold mb-0 text-dark" style="font-size: 1.15rem; letter-spacing: -0.01em;">Surat Suara Digital</h4>
                <small class="text-muted d-block" style="font-size: 0.76rem;">
                    Pemilihan Ketua &amp; Wakil Ketua OSIS Periode 2026/2027
                </small>
            </div>
        </div>

        <div class="d-none d-lg-flex align-items-center gap-2 px-3 py-1 bg-white border rounded-pill shadow-xs">
            <i class="bi bi-info-circle-fill text-primary"></i>
            <span class="small text-muted">Sentuh atau klik tombol <strong>PILIH PASLON</strong> untuk memberikan hak suara Anda</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="p-1.5 px-3 bg-white border rounded-pill shadow-xs d-flex align-items-center gap-2">
                <span class="text-muted small">Pemilih:</span>
                <strong class="text-dark small text-truncate" style="max-width: 180px;"><?= Security::escape($voter['nama']) ?></strong>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.72rem;"><?= Security::escape($voter['kelas']) ?></span>
            </div>

            <!-- Tombol Layar Penuh Bilik Suara -->
            <button type="button" id="btnToggleBallotFS" class="btn btn-sm btn-paper-secondary shadow-xs px-2.5" title="Mode Layar Penuh Bilik Suara">
                <i class="bi bi-arrows-fullscreen" id="ballotFsIcon"></i>
            </button>
        </div>
    </header>

    <!-- 2. Area Paslon (Mengisi Seluruh Sisa Layar Tanpa Scroll) -->
    <?php if (empty($candidates)): ?>
        <div class="paper-card p-5 text-center my-auto mx-auto shadow-sm" style="max-width: 500px;">
            <i class="bi bi-exclamation-circle text-warning display-4 mb-3"></i>
            <h4 class="fw-bold">Belum Ada Pasangan Calon</h4>
            <p class="text-muted mb-0">Panitia belum mengaktifkan data pasangan calon pemilihan.</p>
        </div>
    <?php else: ?>
        <div class="ballot-candidates-grid" style="--cand-count: <?= max(1, $candidateCount) ?>;">
            <?php foreach ($candidates as $cand): ?>
                <div class="ballot-card">
                    <!-- Card Top Header -->
                    <div class="ballot-card-header">
                        <span class="text-uppercase fw-bold text-secondary tracking-wide" style="font-size: 0.78rem;">
                            Pasangan Calon
                        </span>
                        <div class="paslon-badge">
                            0<?= Security::escape($cand['nomor_urut']) ?>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="ballot-card-body">
                        <!-- Candidate Photo -->
                        <div class="ballot-photo-box">
                            <?php if (!empty($cand['foto'])): ?>
                                <img src="<?= Security::escape($cand['foto']) ?>" alt="Paslon 0<?= Security::escape($cand['nomor_urut']) ?>">
                            <?php else: ?>
                                <div class="ballot-photo-placeholder">
                                    <i class="bi bi-people-fill display-4 mb-1"></i>
                                    <span class="small fw-semibold">Foto Paslon 0<?= Security::escape($cand['nomor_urut']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Candidate Names -->
                        <div class="ballot-names-box">
                            <div class="cand-ketua-name"><?= Security::escape($cand['nama_ketua']) ?></div>
                            <div class="cand-ketua-role">Calon Ketua &bull; <?= Security::escape($cand['jurusan_ketua']) ?></div>
                            
                            <div class="cand-wakil-name mt-1"><?= Security::escape($cand['nama_wakil']) ?></div>
                            <div class="cand-wakil-role">Calon Wakil &bull; <?= Security::escape($cand['jurusan_wakil']) ?></div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="ballot-actions-box">
                            <button 
                                type="button" 
                                class="btn btn-sm btn-outline-secondary w-100 py-1.5 rounded-3 btn-view-vision"
                                data-candidate-id="<?= (int)$cand['id'] ?>"
                            >
                                <i class="bi bi-file-text me-1"></i> Visi &amp; Misi Paslon 0<?= Security::escape($cand['nomor_urut']) ?>
                            </button>

                            <button 
                                type="button" 
                                class="btn btn-paper-vote-kiosk w-100 py-2.5 py-xl-3 fs-5 btn-vote-action" 
                                data-candidate-id="<?= (int)$cand['id'] ?>"
                                data-candidate-number="0<?= Security::escape($cand['nomor_urut']) ?>"
                                data-candidate-names="<?= Security::escape($cand['nama_ketua'] . ' & ' . $cand['nama_wakil']) ?>"
                            >
                                <i class="bi bi-check2-circle me-1"></i> PILIH PASLON 0<?= Security::escape($cand['nomor_urut']) ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Form Submit Suara Pemilih -->
<form action="/vote/submit" method="POST" id="voteForm" class="d-none">
    <?= Security::csrfField() ?>
    <input type="hidden" name="candidate_id" id="modalCandidateId" value="">
</form>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const candidatesData = <?= json_encode($candidates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> || [];

    // 1. Modal Lihat Visi & Misi Paslon
    document.querySelectorAll('.btn-view-vision').forEach(btn => {
        btn.addEventListener('click', () => {
            const candId = btn.getAttribute('data-candidate-id');
            const cand = candidatesData.find(c => c.id == candId);
            if (!cand) return;

            const num = '0' + cand.nomor_urut;
            const names = cand.nama_ketua + ' & ' + cand.nama_wakil;
            const visi = cand.visi || 'Belum diisi';
            const misi = cand.misi || 'Belum diisi';

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            const formattedVisi = escapeHtml(visi).replace(/\n/g, '<br>');
            const formattedMisi = escapeHtml(misi).replace(/\n/g, '<br>');

            if (window.SwalPaper) {
                window.SwalPaper.fire({
                    title: `Visi & Misi Paslon ${num}`,
                    html: `
                        <div class="text-start py-2">
                            <div class="text-center mb-3">
                                <div class="paslon-badge mb-2 mx-auto" style="width: 48px; height: 48px; font-size: 1.25rem;">
                                    ${num}
                                </div>
                                <h5 class="fw-bold text-dark mb-0">${escapeHtml(names)}</h5>
                            </div>
                            <div class="mb-3">
                                <h6 class="fw-bold text-uppercase small text-secondary mb-1">
                                    <i class="bi bi-lightbulb-fill text-warning me-1"></i> Visi
                                </h6>
                                <div class="p-3 bg-light rounded-3 border small text-muted">
                                    ${formattedVisi}
                                </div>
                            </div>
                            <div>
                                <h6 class="fw-bold text-uppercase small text-secondary mb-1">
                                    <i class="bi bi-check2-circle text-success me-1"></i> Misi
                                </h6>
                                <div class="p-3 bg-light rounded-3 border small text-muted" style="max-height: 220px; overflow-y: auto;">
                                    ${formattedMisi}
                                </div>
                            </div>
                        </div>
                    `,
                    confirmButtonText: 'Tutup',
                    customClass: {
                        popup: 'paper-swal-popup',
                        confirmButton: 'swal2-confirm btn-paper-primary px-4'
                    }
                });
            } else {
                alert(`Visi & Misi Paslon ${num} (${names}):\n\nVISI:\n${visi}\n\nMISI:\n${misi}`);
            }
        });
    });

    // 2. Tombol Konfirmasi Pilihan Suara
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

    // 3. Toggle Fullscreen Native Kiosk Bilik Suara
    const btnFS = document.getElementById('btnToggleBallotFS');
    const fsIcon = document.getElementById('ballotFsIcon');

    function updateBallotFSIcon() {
        if (!fsIcon) return;
        if (document.fullscreenElement) {
            fsIcon.className = 'bi bi-fullscreen-exit';
        } else {
            fsIcon.className = 'bi bi-arrows-fullscreen';
        }
    }

    if (btnFS) {
        btnFS.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(() => {});
                }
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            }
        });
    }

    document.addEventListener('fullscreenchange', updateBallotFSIcon);
});
</script>
