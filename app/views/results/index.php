<?php
use App\Core\Security;
?>

<div id="resultsMainContainer" class="results-fullscreen-wrapper">
    <div class="row justify-content-center w-100 m-0">
        <div class="col-lg-11 col-xl-10 p-0">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2.5">
                    <img src="/assets/images/Logo_OSIS.svg" alt="OSIS" style="height: 36px; width: auto; object-fit: contain;">
                    <div>
                        <h3 class="fw-bold mb-0 text-dark">Hasil Perolehan Suara</h3>
                        <p class="text-muted small mb-0">Rapat Pleno & Pengumuman Resmi Pemilihan Ketua OSIS</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if ($isUnlocked): ?>
                        <form action="/admin/results/lock" method="POST" class="d-inline m-0" id="formLockResults">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="is_fullscreen" id="lockIsFullscreen" value="0">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Kunci kembali hasil dan kembali ke pemantauan">
                                <i class="bi bi-lock me-1"></i> Kunci Kembali
                            </button>
                        </form>
                    <?php endif; ?>
                    <a href="/admin/monitoring" id="btnBackToMonitoring" class="btn btn-sm btn-paper-secondary" title="Kembali ke Halaman Pemantauan Suara">
                        <i class="bi bi-broadcast me-1"></i> Pantau Suara
                    </a>
                    <a href="/admin/dashboard" id="btnBackToDashboard" class="btn btn-sm btn-paper-secondary" title="Kembali ke Dashboard">
                        <i class="bi bi-arrow-left me-1"></i> Dashboard
                    </a>
                    <button type="button" id="btnToggleFullscreen" class="btn btn-sm btn-paper-primary fw-bold shadow-sm" title="Mode Layar Penuh (Presentasi Rapat Pleno)">
                        <i class="bi bi-arrows-fullscreen me-1" id="fsIcon"></i>
                        <span id="fsText">Layar Penuh</span>
                    </button>
                </div>
            </div>

        <?php if (!$isUnlocked): ?>
            <!-- FORM INPUT KODE AKSES HASIL -->
            <div class="row justify-content-center py-4 my-auto w-100" id="lockedResultsBox">
                <div class="col-md-6 col-lg-5">
                    <div class="paper-card p-4 p-md-5 text-center shadow-sm">
                        <div class="mb-3">
                            <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 75px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.08));">
                        </div>
                        <h4 class="fw-bold mb-2">Akses Terkunci</h4>
                        <p class="text-muted small mb-4">
                            Untuk menjaga kerahasiaan suara pemilih, masukkan kode akses resmi panitia untuk membuka hasil perolehan suara paslon.
                        </p>

                        <form action="/admin/results/unlock" method="POST" id="formResultsUnlock">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="is_fullscreen" id="resultsUnlockIsFullscreen" value="0">

                            <div class="mb-4">
                                <label for="access_code" class="form-label fw-bold small text-uppercase text-secondary">
                                    Kode Akses Pengumuman
                                </label>
                                <input 
                                    type="password" 
                                    name="access_code" 
                                    id="access_code" 
                                    class="form-control form-control-paper text-center fs-5" 
                                    placeholder="••••••••" 
                                    required 
                                    autofocus
                                >
                                <div class="form-text small text-muted mt-2">
                                    <i class="bi bi-key-fill me-1"></i>Kode otorisasi default: <strong>osis2026</strong>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-warning text-dark py-2 fw-bold shadow-sm">
                                    <i class="bi bi-unlock-fill me-1"></i> BUKA HASIL PEMILIHAN
                                </button>
                            </div>

                            <div class="pt-3 border-top">
                                <a href="/admin/monitoring" id="linkLockedBackToMonitoring" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-broadcast me-1"></i> Masuk ke Halaman Pemantauan Suara (Live)
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- AREA HASIL TERBUKA: ANIMASI COUNTDOWN & HASIL SUARA -->
            
            <!-- 1. Bagian Countdown 60 Detik -->
            <div id="countdownContainer" class="paper-card p-5 text-center my-3">
                <h4 class="text-uppercase tracking-wide text-secondary fw-bold mb-2">
                    <i class="bi bi-hourglass-split text-warning me-2"></i> Rekapitulasi Suara Berlangsung
                </h4>
                <p class="text-muted mb-4">
                    Sistem sedang memproses sinkronisasi dan memvalidasi seluruh kotak suara digital...
                </p>

                <div class="countdown-box my-4 mx-auto" style="max-width: 320px;">
                    <div class="countdown-timer" id="timerDisplay">60</div>
                    <div class="text-uppercase small fw-bold text-muted mt-2">Detik Menuju Hasil</div>
                </div>

                <div class="progress my-4 mx-auto" style="max-width: 480px; height: 10px;">
                    <div id="countdownBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: 100%;"></div>
                </div>

                <div class="d-flex justify-content-center gap-2 mt-4">
                    <button type="button" id="btnSkipCountdown" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-fast-forward me-1"></i> Lewati Hitung Mundur (Mode Cepat)
                    </button>
                </div>
            </div>

            <!-- 2. Bagian Hasil Akhir (Awalnya Disembunyikan) -->
            <div id="resultsContainer" style="display: none;">
                <div class="alert alert-success border-0 shadow-sm p-4 text-center mb-4">
                    <div class="mb-2">
                        <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 64px; width: auto; object-fit: contain;">
                    </div>
                    <h3 class="fw-bold mb-1"><i class="bi bi-check-circle-fill me-2"></i> HASIL AKHIR REKAPITULASI RESMI</h3>
                    <p class="mb-0 text-muted">Pemilihan Ketua & Wakil Ketua OSIS Periode 2026/2027</p>
                </div>

                <!-- Ringkasan Statistik Rekap -->
                <div class="row g-3 mb-4 text-center">
                    <div class="col-md-4">
                        <div class="paper-card p-3">
                            <span class="text-muted small text-uppercase">Total Pemilih (DPT)</span>
                            <h4 class="fw-bold mt-1 mb-0" id="resTotalVoters">-</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="paper-card p-3">
                            <span class="text-muted small text-uppercase">Total Suara Masuk</span>
                            <h4 class="fw-bold text-success mt-1 mb-0" id="resTotalVotes">-</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="paper-card p-3">
                            <span class="text-muted small text-uppercase">Tingkat Partisipasi</span>
                            <h4 class="fw-bold text-primary mt-1 mb-0" id="resParticipation">-</h4>
                        </div>
                    </div>
                </div>

                <!-- Kartu Hasil Setiap Paslon -->
                <div class="row g-4 justify-content-center" id="candidateResultsList">
                    <!-- Dynamic rendering via JS -->
                </div>

                <div class="text-center mt-5">
                    <button type="button" class="btn btn-outline-primary" onclick="restartCountdown()">
                        <i class="bi bi-arrow-repeat me-1"></i> Ulangi Animasi Countdown
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Script Engine untuk Halaman Hasil Pleno -->
<script>
window.initResultsView = function() {
    // 0. Cleanup previous timers
    if (typeof window._cleanupCurrentView === 'function') {
        window._cleanupCurrentView();
        window._cleanupCurrentView = null;
    }

    const isUnlocked = <?= $isUnlocked ? 'true' : 'false' ?>;

    if (isUnlocked) {
        let timerSeconds = 60;
        let timerInterval = null;

        function startCountdown() {
            const timerDisplay = document.getElementById('timerDisplay');
            const countdownBar = document.getElementById('countdownBar');
            if (!timerDisplay || !countdownBar) return;

            timerSeconds = 60;
            timerDisplay.textContent = timerSeconds;
            countdownBar.style.width = '100%';

            if (timerInterval) clearInterval(timerInterval);

            timerInterval = setInterval(() => {
                timerSeconds--;
                if (timerDisplay) timerDisplay.textContent = timerSeconds;
                const percent = (timerSeconds / 60) * 100;
                if (countdownBar) countdownBar.style.width = percent + '%';

                if (timerSeconds <= 0) {
                    clearInterval(timerInterval);
                    revealResults();
                }
            }, 1000);
            window._resultsCountdownTimer = timerInterval;
        }

        function revealResults() {
            const cBox = document.getElementById('countdownContainer');
            const rBox = document.getElementById('resultsContainer');
            if (cBox) cBox.style.display = 'none';
            if (rBox) rBox.style.display = 'block';
            fetchResultsData();
        }

        window.restartCountdown = function() {
            const cBox = document.getElementById('countdownContainer');
            const rBox = document.getElementById('resultsContainer');
            if (rBox) rBox.style.display = 'none';
            if (cBox) cBox.style.display = 'block';
            startCountdown();
        };

        const btnSkip = document.getElementById('btnSkipCountdown');
        if (btnSkip) {
            btnSkip.onclick = () => {
                if (timerInterval) clearInterval(timerInterval);
                revealResults();
            };
        }

        async function fetchResultsData() {
            try {
                const response = await fetch('/admin/results/data');
                const data = await response.json();

                if (!data.success) {
                    if (window.PaperAlert) {
                        window.PaperAlert.error(data.error || 'Gagal memuat data hasil.', 'Gagal Memuat Hasil');
                    } else {
                        alert(data.error || 'Gagal memuat data hasil.');
                    }
                    return;
                }

                const resTotalVoters = document.getElementById('resTotalVoters');
                const resTotalVotes = document.getElementById('resTotalVotes');
                const resPart = document.getElementById('resParticipation');
                if (resTotalVoters) resTotalVoters.textContent = data.total_voters.toLocaleString('id-ID');
                if (resTotalVotes) resTotalVotes.textContent = data.total_votes.toLocaleString('id-ID');
                const part = data.total_voters > 0 ? ((data.total_votes / data.total_voters) * 100).toFixed(1) : 0;
                if (resPart) resPart.textContent = part + '%';

                const container = document.getElementById('candidateResultsList');
                if (!container) return;
                container.innerHTML = '';

                const totalSuara = data.total_votes;
                let maxSuara = -1;
                data.candidates.forEach(c => {
                    const s = parseInt(c.total_suara) || 0;
                    if (s > maxSuara) maxSuara = s;
                });

                data.candidates.forEach(cand => {
                    const suara = parseInt(cand.total_suara) || 0;
                    const persentase = totalSuara > 0 ? ((suara / totalSuara) * 100).toFixed(1) : 0;
                    const isLeading = suara > 0 && suara === maxSuara;

                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-6';
                    col.innerHTML = `
                        <div class="paper-card h-100 p-4 border-2 ${isLeading ? 'border-primary shadow' : ''}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="paslon-badge">0${cand.nomor_urut}</div>
                                ${isLeading ? '<span class="badge bg-warning text-dark fs-6 px-3 py-1"><i class="bi bi-trophy-fill me-1"></i> Suara Terbanyak</span>' : ''}
                            </div>
                            <div class="text-center mb-3">
                                ${cand.foto ? `<img src="${cand.foto}" class="img-fluid rounded mb-3 border" style="max-height: 180px; object-fit: cover;">` : ''}
                                <h4 class="fw-bold mb-1">${cand.nama_ketua}</h4>
                                <h5 class="fw-semibold text-secondary mb-3">& ${cand.nama_wakil}</h5>
                            </div>
                            <div class="bg-light p-3 rounded border text-center mb-3">
                                <div class="text-muted small text-uppercase">Perolehan Suara</div>
                                <div class="display-5 fw-bold text-primary">${suara.toLocaleString('id-ID')}</div>
                                <div class="fw-semibold text-muted">${persentase}% dari total suara sah</div>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar ${isLeading ? 'bg-primary' : 'bg-secondary'}" role="progressbar" style="width: ${persentase}%"></div>
                            </div>
                        </div>
                    `;
                    container.appendChild(col);
                });
            } catch (e) {
                console.error('Error fetching results:', e);
                if (window.PaperAlert) {
                    window.PaperAlert.error('Terjadi gangguan jaringan saat mengambil data hasil pemilihan.', 'Koneksi Terputus');
                }
            }
        }

        // Jalankan countdown saat view dibuka
        startCountdown();

        // Lock form submit interception
        const formLock = document.getElementById('formLockResults');
        if (formLock) {
            formLock.onsubmit = async (e) => {
                const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
                if (!isFS) return;
                e.preventDefault();

                try {
                    const formData = new FormData(formLock);
                    formData.set('is_fullscreen', '1');

                    const res = await fetch('/admin/results/lock', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (window.seamlessNavigate) {
                        window.seamlessNavigate(data.redirect || '/admin/monitoring');
                    } else {
                        window.location.href = data.redirect || '/admin/monitoring';
                    }
                } catch (err) {
                    formLock.submit();
                }
            };
        }
    } else {
        // Locked results view
        const formResultsUnlock = document.getElementById('formResultsUnlock');
        if (formResultsUnlock) {
            formResultsUnlock.onsubmit = async (e) => {
                const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
                if (!isFS) return;
                e.preventDefault();

                try {
                    const formData = new FormData(formResultsUnlock);
                    formData.set('is_fullscreen', '1');

                    const res = await fetch('/admin/results/unlock', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (window.seamlessNavigate) {
                            window.seamlessNavigate(data.redirect || '/admin/results');
                        } else {
                            window.location.href = data.redirect || '/admin/results';
                        }
                    } else {
                        if (window.PaperAlert) {
                            window.PaperAlert.error(data.error || 'Kode akses salah.', 'Akses Ditolak');
                        } else {
                            alert(data.error || 'Kode akses salah.');
                        }
                    }
                } catch (err) {
                    formResultsUnlock.submit();
                }
            };
        }

        const linkLocked = document.getElementById('linkLockedBackToMonitoring');
        if (linkLocked) {
            linkLocked.onclick = (e) => {
                const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
                if (isFS && window.seamlessNavigate) {
                    e.preventDefault();
                    window.seamlessNavigate('/admin/monitoring');
                }
            };
        }
    }

    // Navigation buttons
    const btnBackMonitoring = document.getElementById('btnBackToMonitoring');
    if (btnBackMonitoring) {
        btnBackMonitoring.onclick = (e) => {
            const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
            if (isFS && window.seamlessNavigate) {
                e.preventDefault();
                window.seamlessNavigate('/admin/monitoring');
            }
        };
    }

    const btnDashboard = document.getElementById('btnBackToDashboard');
    if (btnDashboard) {
        btnDashboard.onclick = () => {
            try { sessionStorage.removeItem('evoting_fullscreen'); } catch (e) {}
        };
    }

    const btnFullscreen = document.getElementById('btnToggleFullscreen');
    if (btnFullscreen) {
        btnFullscreen.onclick = () => {
            if (window.toggleEvotingFullscreen) {
                window.toggleEvotingFullscreen();
            }
        };
    }

    // Cleanup hook
    window._cleanupCurrentView = () => {
        if (window._resultsCountdownTimer) {
            clearInterval(window._resultsCountdownTimer);
            window._resultsCountdownTimer = null;
        }
    };

    // Update fullscreen UI status
    const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
    if (window.setEvotingFullscreenUI) {
        window.setEvotingFullscreenUI(isFS);
    }
};

// Initial run
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initResultsView);
} else {
    window.initResultsView();
}
</script>
