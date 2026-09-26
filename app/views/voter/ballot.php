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
                                data-candidate-number="0<?= Security::escape($cand['nomor_urut']) ?>"
                                data-candidate-names="<?= Security::escape($cand['nama_ketua'] . ' & ' . $cand['nama_wakil']) ?>"
                                data-candidate-visi="<?= htmlspecialchars((string)($cand['visi'] ?? 'Belum diisi'), ENT_QUOTES, 'UTF-8') ?>"
                                data-candidate-misi="<?= htmlspecialchars((string)($cand['misi'] ?? 'Belum diisi'), ENT_QUOTES, 'UTF-8') ?>"
                                onclick="if(window.handleBallotVisionClick){window.handleBallotVisionClick(this);}else{alert('Visi:\n'+this.getAttribute('data-candidate-visi')+'\n\nMisi:\n'+this.getAttribute('data-candidate-misi'));}"
                            >
                                <i class="bi bi-file-text me-1"></i> Visi &amp; Misi Paslon 0<?= Security::escape($cand['nomor_urut']) ?>
                            </button>

                            <button 
                                type="button" 
                                class="btn btn-paper-vote-kiosk w-100 py-2.5 py-xl-3 fs-5 btn-vote-action" 
                                data-candidate-id="<?= (int)$cand['id'] ?>"
                                data-candidate-number="0<?= Security::escape($cand['nomor_urut']) ?>"
                                data-candidate-names="<?= Security::escape($cand['nama_ketua'] . ' & ' . $cand['nama_wakil']) ?>"
                                onclick="if(window.handleBallotVoteClick){window.handleBallotVoteClick('<?= (int)$cand['id'] ?>', '<?= Security::escape($cand['nama_ketua'] . ' & ' . $cand['nama_wakil']) ?>', '0<?= Security::escape($cand['nomor_urut']) ?>');}else{var form=document.getElementById('voteForm');if(form){document.getElementById('modalCandidateId').value='<?= (int)$cand['id'] ?>';if(confirm('Pilih Paslon 0<?= Security::escape($cand['nomor_urut']) ?>?')){form.submit();}}}"
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
(function() {
    function escapeHtmlBallot(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    if (!window.handleBallotVisionClick) {
        window.handleBallotVisionClick = function(btn) {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            if (btn && typeof btn.blur === 'function') {
                btn.blur();
            }
            if (!btn) return;
            var num = btn.getAttribute('data-candidate-number') || '01';
            var names = btn.getAttribute('data-candidate-names') || 'Pasangan Calon';
            var visi = btn.getAttribute('data-candidate-visi') || 'Belum diisi';
            var misi = btn.getAttribute('data-candidate-misi') || 'Belum diisi';
            var formattedVisi = escapeHtmlBallot(visi).replace(/\n/g, '<br>');
            var formattedMisi = escapeHtmlBallot(misi).replace(/\n/g, '<br>');

            if (window.SwalPaper) {
                window.SwalPaper.fire({
                    title: 'Visi & Misi Paslon ' + num,
                    html: '<div class="text-start py-2"><div class="text-center mb-3"><div class="paslon-badge mb-2 mx-auto" style="width: 48px; height: 48px; font-size: 1.25rem;">' + num + '</div><h5 class="fw-bold text-dark mb-0">' + escapeHtmlBallot(names) + '</h5></div><div class="mb-3"><h6 class="fw-bold text-uppercase small text-secondary mb-1"><i class="bi bi-lightbulb-fill text-warning me-1"></i> Visi</h6><div class="p-3 bg-light rounded-3 border small text-muted">' + formattedVisi + '</div></div><div><h6 class="fw-bold text-uppercase small text-secondary mb-1"><i class="bi bi-check2-circle text-success me-1"></i> Misi</h6><div class="p-3 bg-light rounded-3 border small text-muted" style="max-height: 220px; overflow-y: auto;">' + formattedMisi + '</div></div></div>',
                    confirmButtonText: 'Tutup',
                    focusConfirm: true,
                    customClass: { popup: 'paper-swal-popup', confirmButton: 'swal2-confirm btn-paper-primary px-4' }
                });
            } else {
                alert('Visi & Misi Paslon ' + num + ' (' + names + '):\n\nVISI:\n' + visi + '\n\nMISI:\n' + misi);
            }
        };
    }

    if (!window.handleBallotVoteClick) {
        window.handleBallotVoteClick = function(candidateId, candidateNames, candidateNumber) {
            if (document.activeElement && typeof document.activeElement.blur === 'function') {
                document.activeElement.blur();
            }
            if (window.SwalPaper) {
                window.SwalPaper.fire({
                    title: 'Konfirmasi Pilihan Anda',
                    html: '<div class="py-2 text-center"><p class="text-muted mb-3">Apakah Anda yakin ingin memberikan suara kepada:</p><div class="paslon-badge mb-3 mx-auto" style="width: 58px; height: 58px; font-size: 1.5rem;">' + candidateNumber + '</div><h4 class="fw-bold text-dark mb-3">' + escapeHtmlBallot(candidateNames) + '</h4><div class="paper-swal-alert-warning p-3 rounded small text-start border"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i><strong>PENTING:</strong> Pilihan Anda bersifat final dan <strong>tidak dapat diubah</strong> setelah Anda menekan tombol konfirmasi.</div></div>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Ya, Saya Yakin',
                    cancelButtonText: 'Batal / Periksa Kembali',
                    reverseButtons: true,
                    focusConfirm: true,
                    customClass: { popup: 'paper-swal-popup', confirmButton: 'swal2-confirm btn-paper-vote px-4 py-2 me-2', cancelButton: 'swal2-cancel btn-paper-secondary px-4 py-2' }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        if (window.submitBallotVoteAction) {
                            window.submitBallotVoteAction(candidateId, candidateNames, candidateNumber);
                        } else {
                            var f = document.getElementById('voteForm');
                            if (f) {
                                document.getElementById('modalCandidateId').value = candidateId;
                                f.submit();
                            }
                        }
                    }
                });
            } else {
                if (confirm('Apakah Anda yakin ingin memberikan suara kepada Paslon ' + candidateNumber + ' (' + candidateNames + ')? Pilihan tidak dapat diubah.')) {
                    if (window.submitBallotVoteAction) {
                        window.submitBallotVoteAction(candidateId, candidateNames, candidateNumber);
                    } else {
                        var f = document.getElementById('voteForm');
                        if (f) {
                            document.getElementById('modalCandidateId').value = candidateId;
                            f.submit();
                        }
                    }
                }
            }
        };
    }

    window.initBallotView = function() {
        var isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
        if (window.setEvotingFullscreenUI) {
            window.setEvotingFullscreenUI(isFS);
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initBallotView);
    } else {
        window.initBallotView();
    }
})();
</script>
