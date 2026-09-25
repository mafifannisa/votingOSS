<?php
use App\Core\Security;
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h2 class="fw-bold mb-1">Hasil Perolehan Suara</h2>
                <p class="text-muted mb-0">Rapat Pleno & Pengumuman Hasil Pemilihan Ketua OSIS</p>
            </div>
            <div>
                <?php if ($isUnlocked): ?>
                    <form action="/admin/results/lock" method="POST" class="d-inline">
                        <?= Security::csrfField() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger me-1">
                            <i class="bi bi-lock me-1"></i> Kunci Kembali
                        </button>
                    </form>
                <?php endif; ?>
                <a href="/admin/monitoring" class="btn btn-sm btn-paper-primary me-1">
                    <i class="bi bi-broadcast me-1"></i> Pantau Suara
                </a>
                <a href="/admin/dashboard" class="btn btn-sm btn-paper-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>

        <?php if (!$isUnlocked): ?>
            <!-- FORM INPUT KODE AKSES HASIL -->
            <div class="row justify-content-center py-4">
                <div class="col-md-6 col-lg-5">
                    <div class="paper-card p-4 p-md-5 text-center shadow-sm">
                        <div class="mb-3">
                            <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 75px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.08));">
                        </div>
                        <h4 class="fw-bold mb-2">Akses Terkunci</h4>
                        <p class="text-muted small mb-4">
                            Untuk menjaga kerahasiaan suara pemilih, masukkan kode akses resmi panitia untuk membuka hasil perolehan suara paslon.
                        </p>

                        <form action="/admin/results/unlock" method="POST">
                            <?= Security::csrfField() ?>

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
                                <a href="/admin/monitoring" class="btn btn-sm btn-outline-primary w-100">
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

            <script>
            let timerSeconds = 60;
            let timerInterval = null;

            function startCountdown() {
                const timerDisplay = document.getElementById('timerDisplay');
                const countdownBar = document.getElementById('countdownBar');

                timerSeconds = 60;
                timerDisplay.textContent = timerSeconds;
                countdownBar.style.width = '100%';

                if (timerInterval) clearInterval(timerInterval);

                timerInterval = setInterval(() => {
                    timerSeconds--;
                    timerDisplay.textContent = timerSeconds;
                    const percent = (timerSeconds / 60) * 100;
                    countdownBar.style.width = percent + '%';

                    if (timerSeconds <= 0) {
                        clearInterval(timerInterval);
                        revealResults();
                    }
                }, 1000);
            }

            function revealResults() {
                document.getElementById('countdownContainer').style.display = 'none';
                document.getElementById('resultsContainer').style.display = 'block';
                fetchResultsData();
            }

            function restartCountdown() {
                document.getElementById('resultsContainer').style.display = 'none';
                document.getElementById('countdownContainer').style.display = 'block';
                startCountdown();
            }

            document.getElementById('btnSkipCountdown').addEventListener('click', () => {
                if (timerInterval) clearInterval(timerInterval);
                revealResults();
            });

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

                    document.getElementById('resTotalVoters').textContent = data.total_voters.toLocaleString('id-ID');
                    document.getElementById('resTotalVotes').textContent = data.total_votes.toLocaleString('id-ID');
                    const part = data.total_voters > 0 ? ((data.total_votes / data.total_voters) * 100).toFixed(1) : 0;
                    document.getElementById('resParticipation').textContent = part + '%';

                    const container = document.getElementById('candidateResultsList');
                    container.innerHTML = '';

                    const totalSuara = data.total_votes;
                    
                    // Cari suara terbanyak
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
                        window.PaperAlert.error('Terjadi gangguan jaringan saat mengambil data hasil pemilihan dari server.', 'Koneksi Terputus');
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                startCountdown();
            });
            </script>
        <?php endif; ?>
    </div>
</div>
