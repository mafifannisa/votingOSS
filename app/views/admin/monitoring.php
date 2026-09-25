<?php
use App\Core\Security;
?>

<!-- Include Chart.js (Local offline file with CDN fallback) -->
<script src="/assets/js/chart.min.js"></script>
<script>
    if (typeof Chart === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"><\/script>');
    }
</script>

<div id="monitoringContainer" class="monitoring-fullscreen-wrapper">

    <!-- 1. HEADER TOOLBAR -->
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-1 pb-1">
        <div class="d-flex align-items-center gap-2.5">
            <img src="/assets/images/Logo_OSIS.svg" alt="OSIS" style="height: 36px; width: auto; object-fit: contain;">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Pemantauan Suara Masuk</h4>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 d-inline-flex align-items-center gap-1.5" id="liveBadge">
                        <span class="spinner-grow spinner-grow-sm text-success" style="width: 7px; height: 7px;" role="status"></span>
                        <span class="small fw-semibold" style="font-size: 0.75rem;">Live Polling</span>
                    </span>
                </div>
                <small class="text-muted d-block" style="font-size: 0.78rem;">
                    <?= Security::escape($electionConfig['election_name'] ?? 'Pemilihan Ketua OSIS') ?> &bull; Update: <strong id="lastUpdated"><?= date('H:i:s') ?> WIB</strong>
                </small>
            </div>
        </div>

        <!-- Tombol Aksi Kanan -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Mode Chart Switcher -->
            <div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="Pilihan Tampilan Chart">
                <button type="button" class="btn btn-paper-outline-primary active" id="btnModePercentage" title="Tampilkan Grafik Persentase (%)">
                    <i class="bi bi-percent"></i> Partisipasi
                </button>
                <button type="button" class="btn btn-paper-outline-primary" id="btnModeCount" title="Tampilkan Grafik Jumlah Surat Suara">
                    <i class="bi bi-bar-chart-fill"></i> Volume Suara
                </button>
            </div>

            <!-- Tombol Segarkan Manual -->
            <button type="button" id="btnRefreshManual" class="btn btn-sm btn-paper-secondary shadow-sm" title="Segarkan Data Sekarang">
                <i class="bi bi-arrow-clockwise" id="refreshIcon"></i>
            </button>

            <!-- Tombol Buka Hasil Rapat Pleno -->
            <?php if ($isUnlocked): ?>
                <a href="/admin/results" id="btnGoToResults" class="btn btn-sm btn-warning text-dark fw-bold px-2.5 shadow-sm" title="Hasil Pemilihan Sudah Terbuka">
                    <i class="bi bi-trophy-fill me-1"></i> Hasil Pleno
                </a>
            <?php else: ?>
                <button type="button" class="btn btn-sm btn-warning text-dark fw-bold px-2.5 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUnlockResults" title="Buka Hasil Rekapitulasi Paslon">
                    <i class="bi bi-shield-lock-fill me-1"></i> Buka Hasil
                </button>
            <?php endif; ?>

            <!-- Tombol Toggle Fullscreen -->
            <button type="button" id="btnToggleFullscreen" class="btn btn-sm btn-paper-primary fw-bold px-3 shadow-sm" title="Mode Layar Penuh (Pas Layar & Tanpa Scrollbar)">
                <i class="bi bi-arrows-fullscreen me-1" id="fsIcon"></i>
                <span id="fsText">Layar Penuh</span>
            </button>
        </div>
    </div>

    <!-- 2. KARTU METRIK UTAMA (4 METRICS IN A COMPACT ROW) -->
    <div class="row g-2 g-md-3">
        <!-- Metric 1: Total DPT -->
        <div class="col-6 col-md-3">
            <div class="paper-card metric-card-paper p-2.5 p-md-3 h-100 border-start border-primary border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Total DPT</div>
                        <div class="metric-number fw-bold text-dark mt-0.5" id="valTotalVoters">
                            <?= number_format($totalVoters, 0, ',', '.') ?>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Siswa pemilih sah</small>
                    </div>
                    <div class="metric-icon-box bg-primary-subtle text-primary p-2 rounded-3">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 2: Total Suara Masuk -->
        <div class="col-6 col-md-3">
            <div class="paper-card metric-card-paper p-2.5 p-md-3 h-100 border-start border-success border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Suara Masuk</div>
                        <div class="metric-number fw-bold text-success mt-0.5" id="valTotalVoted">
                            <?= number_format($totalVoted, 0, ',', '.') ?>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Kotak suara digital</small>
                    </div>
                    <div class="metric-icon-box bg-success-subtle text-success p-2 rounded-3">
                        <i class="bi bi-box-seam-fill fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 3: Belum Memilih -->
        <div class="col-6 col-md-3">
            <div class="paper-card metric-card-paper p-2.5 p-md-3 h-100 border-start border-danger border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Belum Memilih</div>
                        <div class="metric-number fw-bold text-danger mt-0.5" id="valTotalNotVoted">
                            <?= number_format($totalNotVoted, 0, ',', '.') ?>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Belum menggunakan hak</small>
                    </div>
                    <div class="metric-icon-box bg-danger-subtle text-danger p-2 rounded-3">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric 4: Tingkat Partisipasi (%) -->
        <div class="col-6 col-md-3">
            <div class="paper-card metric-card-paper p-2.5 p-md-3 h-100 border-start border-info border-4 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Partisipasi DPT</div>
                        <div class="metric-number fw-bold text-primary mt-0.5">
                            <span id="valParticipationRate"><?= $participationRate ?></span>%
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">Tingkat kehadiran</small>
                    </div>
                    <div class="metric-icon-box bg-info-subtle text-info p-2 rounded-3">
                        <i class="bi bi-pie-chart-fill fs-5"></i>
                    </div>
                </div>
                <div class="progress mt-1.5" style="height: 5px;">
                    <div id="valProgressBar" class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= min(100, $participationRate) ?>%;" aria-valuenow="<?= $participationRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. GRAFIK UTAMA: PARTISIPASI SUARA BERDASARKAN KELAS (EXPANDS TO FILL HEIGHT) -->
    <div class="paper-card chart-card-paper p-3 p-md-4 shadow-sm">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="bi bi-bar-chart-steps text-primary"></i> Partisipasi Suara Berdasarkan Kelas
                </h6>
                <small class="text-muted" style="font-size: 0.75rem;">
                    Grafik perolehan kehadiran pemilih di setiap kelas secara langsung
                </small>
            </div>

            <!-- Legend Status Colors -->
            <div class="d-flex align-items-center gap-2 small flex-wrap" style="font-size: 0.75rem;">
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="width: 10px; height: 10px; background-color: #10b981; border-radius: 2px;"></span>
                    <span class="text-muted">100% Selesai</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="width: 10px; height: 10px; background-color: #1e3a8a; border-radius: 2px;"></span>
                    <span class="text-muted">Sedang Memilih</span>
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <span style="width: 10px; height: 10px; background-color: #cbd5e1; border-radius: 2px;"></span>
                    <span class="text-muted">Belum Mulai</span>
                </span>
            </div>
        </div>

        <!-- Canvas Container (Responsive Full Fill) -->
        <div class="chart-canvas-box flex-grow-1">
            <canvas id="classChart"></canvas>
        </div>
    </div>

</div>

<!-- ========================================================================
     MODAL BUKA HASIL PEMILIHAN DENGAN KODE AKSES (RAPAT PLENO)
     ======================================================================== -->
<div class="modal fade" id="modalUnlockResults" tabindex="-1" aria-labelledby="modalUnlockResultsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-warning-subtle text-warning-emphasis p-2 rounded-3 d-inline-flex">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalUnlockResultsLabel">Buka Hasil Pemilihan</h5>
                        <small class="text-muted">Otorisasi Resmi Rapat Pleno Panitia OSIS</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="/admin/results/unlock" id="formUnlockResults">
                <?= Security::csrfField() ?>
                <input type="hidden" name="redirect_to" value="/admin/monitoring">
                <input type="hidden" name="is_fullscreen" id="unlockIsFullscreen" value="0">
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-warning border-0 small d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill fs-6 mt-0.5 text-warning flex-shrink-0"></i>
                        <div>
                            <strong>Perhatian:</strong> Membuka hasil pemilihan akan menampilkan perolehan suara masing-masing pasangan calon dan menentukan pemenang. Masukkan kode akses resmi pengumuman.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modalAccessCode" class="form-label fw-bold small text-uppercase text-secondary">
                            Kode Akses Pengumuman <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" 
                                   name="access_code" 
                                   id="modalAccessCode" 
                                   class="form-control form-control-paper text-center fs-5" 
                                   placeholder="••••••••" 
                                   required 
                                   autocomplete="current-password"
                                   autofocus>
                            <button class="btn btn-outline-secondary" type="button" id="btnToggleUnlockCode" title="Lihat / Sembunyikan Kode">
                                <i class="bi bi-eye" id="toggleUnlockIcon"></i>
                            </button>
                        </div>
                        <div class="form-text small text-muted text-center mt-2">
                            <i class="bi bi-key-fill me-1"></i>Ketik <strong>osis2026</strong> untuk membuka hasil suara paslon.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-paper-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold px-3">
                        <i class="bi bi-unlock-fill me-1"></i> Buka Hasil Pemilihan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================
     CLIENT JAVASCRIPT: FULLSCREEN ENGINE, CHART.JS & LIVE POLLING
     ======================================================================== -->
<script>
window._initialClassStats = <?= json_encode($classStats) ?>;

window.initMonitoringView = function() {
    // 0. Cleanup previous timers or chart instances
    if (typeof window._cleanupCurrentView === 'function') {
        window._cleanupCurrentView();
        window._cleanupCurrentView = null;
    }

    let classStatsData = window._initialClassStats || [];
    let currentChartMode = 'percentage';
    let classChart = null;

    // 1. Inisialisasi Chart.js untuk Kelas
    const ctx = document.getElementById('classChart');
    if (ctx && typeof Chart !== 'undefined') {
        if (window._monitoringChart) {
            window._monitoringChart.destroy();
            window._monitoringChart = null;
        }

        const labels = classStatsData.map(c => c.kelas);
        const dataRates = classStatsData.map(c => parseFloat(c.rate));
        const colors = dataRates.map(r => r >= 100 ? '#10b981' : (r > 0 ? '#1e3a8a' : '#cbd5e1'));

        classChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Tingkat Partisipasi (%)',
                    data: dataRates,
                    backgroundColor: colors,
                    borderRadius: 6,
                    maxBarThickness: 42,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 500 },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 13, family: 'Plus Jakarta Sans', weight: 'bold' },
                        bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                const item = classStatsData[idx];
                                if (!item) return '';
                                if (currentChartMode === 'percentage') {
                                    return [
                                        ` Partisipasi: ${item.rate}%`,
                                        ` Sudah Memilih: ${item.voted} siswa`,
                                        ` Belum Memilih: ${item.not_voted} siswa`,
                                        ` Total DPT: ${item.total} siswa`
                                    ];
                                } else {
                                    return ` ${context.dataset.label}: ${context.raw} siswa`;
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#475569', font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(226, 232, 240, 0.7)' },
                        ticks: {
                            color: '#64748b',
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            callback: function(value) { return value + '%'; }
                        }
                    }
                }
            }
        });

        window._monitoringChart = classChart;
    }

    // Fungsi Render Ulang Chart Berdasarkan Mode
    function renderChartMode(mode) {
        if (!classChart) return;
        currentChartMode = mode;
        const labels = classStatsData.map(c => c.kelas);
        classChart.data.labels = labels;

        if (mode === 'percentage') {
            const dataRates = classStatsData.map(c => parseFloat(c.rate));
            const colors = dataRates.map(r => r >= 100 ? '#10b981' : (r > 0 ? '#1e3a8a' : '#cbd5e1'));

            classChart.data.datasets = [{
                label: 'Tingkat Partisipasi (%)',
                data: dataRates,
                backgroundColor: colors,
                borderRadius: 6,
                maxBarThickness: 42,
                borderSkipped: false
            }];
            classChart.options.scales.y.max = 100;
            classChart.options.scales.y.stacked = false;
            classChart.options.scales.x.stacked = false;
            classChart.options.scales.y.ticks.callback = function(val) { return val + '%'; };
            classChart.options.plugins.legend.display = false;
        } else {
            const votedData = classStatsData.map(c => c.voted);
            const notVotedData = classStatsData.map(c => c.not_voted);

            classChart.data.datasets = [
                {
                    label: 'Sudah Memilih',
                    data: votedData,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    maxBarThickness: 42,
                    stack: 'votesStack'
                },
                {
                    label: 'Belum Memilih',
                    data: notVotedData,
                    backgroundColor: '#e2e8f0',
                    borderRadius: 4,
                    maxBarThickness: 42,
                    stack: 'votesStack'
                }
            ];
            delete classChart.options.scales.y.max;
            classChart.options.scales.y.stacked = true;
            classChart.options.scales.x.stacked = true;
            classChart.options.scales.y.ticks.callback = function(val) {
                return Number.isInteger(val) ? val : '';
            };
            classChart.options.plugins.legend.display = true;
            classChart.options.plugins.legend.position = 'top';
            classChart.options.plugins.legend.labels = { boxWidth: 12, font: { size: 11 } };
        }
        classChart.update();
    }

    // Event Listener Switch Mode Chart
    const btnModePct = document.getElementById('btnModePercentage');
    const btnModeCnt = document.getElementById('btnModeCount');
    if (btnModePct && btnModeCnt) {
        btnModePct.onclick = () => {
            btnModePct.classList.add('active');
            btnModeCnt.classList.remove('active');
            renderChartMode('percentage');
        };
        btnModeCnt.onclick = () => {
            btnModeCnt.classList.add('active');
            btnModePct.classList.remove('active');
            renderChartMode('count');
        };
    }

    // Fullscreen Button
    const btnFullscreen = document.getElementById('btnToggleFullscreen');
    if (btnFullscreen) {
        btnFullscreen.onclick = () => {
            if (window.toggleEvotingFullscreen) {
                window.toggleEvotingFullscreen();
            }
        };
    }

    // Intercept "Hasil Pleno" button for seamless in-page transition
    const btnGoToResults = document.getElementById('btnGoToResults');
    if (btnGoToResults) {
        btnGoToResults.onclick = (e) => {
            const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
            if (isFS && window.seamlessNavigate) {
                e.preventDefault();
                window.seamlessNavigate('/admin/results');
            }
        };
    }

    // Intercept Unlock Modal form submit for seamless in-page transition
    const formUnlock = document.getElementById('formUnlockResults');
    if (formUnlock) {
        formUnlock.onsubmit = async (e) => {
            const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
            if (!isFS) return; // If normal window, default form submit works

            e.preventDefault();
            const submitBtn = formUnlock.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const formData = new FormData(formUnlock);
                formData.set('is_fullscreen', '1');

                const res = await fetch('/admin/results/unlock', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    const modalEl = document.getElementById('modalUnlockResults');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const m = bootstrap.Modal.getInstance(modalEl);
                        if (m) m.hide();
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style = '';

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
                formUnlock.submit();
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        };
    }

    // Live Polling Engine
    const valTotalVoters = document.getElementById('valTotalVoters');
    const valTotalVoted = document.getElementById('valTotalVoted');
    const valTotalNotVoted = document.getElementById('valTotalNotVoted');
    const valParticipationRate = document.getElementById('valParticipationRate');
    const valProgressBar = document.getElementById('valProgressBar');
    const lastUpdated = document.getElementById('lastUpdated');
    const btnRefresh = document.getElementById('btnRefreshManual');
    const refreshIcon = document.getElementById('refreshIcon');

    let isFetching = false;

    async function fetchMonitoringData() {
        if (isFetching) return;
        isFetching = true;
        if (refreshIcon) refreshIcon.classList.add('bi-spin');

        try {
            const response = await fetch('/admin/monitoring/data', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    if (valTotalVoters) valTotalVoters.textContent = new Intl.NumberFormat('id-ID').format(data.total_voters);
                    if (valTotalVoted) valTotalVoted.textContent = new Intl.NumberFormat('id-ID').format(data.total_voted);
                    if (valTotalNotVoted) valTotalNotVoted.textContent = new Intl.NumberFormat('id-ID').format(data.total_not_voted);
                    if (valParticipationRate) valParticipationRate.textContent = data.participation_rate;
                    if (valProgressBar) {
                        valProgressBar.style.width = Math.min(100, data.participation_rate) + '%';
                        valProgressBar.setAttribute('aria-valuenow', data.participation_rate);
                    }
                    if (lastUpdated) lastUpdated.textContent = data.updated_at;

                    if (data.class_stats && data.class_stats.length > 0) {
                        classStatsData = data.class_stats;
                        window._initialClassStats = data.class_stats;
                        renderChartMode(currentChartMode);
                    }
                }
            }
        } catch (e) {
            console.error('Gagal mengambil data monitoring live:', e);
        } finally {
            isFetching = false;
            if (refreshIcon) refreshIcon.classList.remove('bi-spin');
        }
    }

    if (btnRefresh) {
        btnRefresh.onclick = () => fetchMonitoringData();
    }

    // Auto-polling interval setiap 5 detik
    window._monitoringPollingTimer = setInterval(fetchMonitoringData, 5000);

    // Modal Password Toggle
    const toggleBtn = document.getElementById('btnToggleUnlockCode');
    const inputCode = document.getElementById('modalAccessCode');
    const toggleIcon = document.getElementById('toggleUnlockIcon');

    if (toggleBtn && inputCode && toggleIcon) {
        toggleBtn.onclick = () => {
            if (inputCode.type === 'password') {
                inputCode.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                inputCode.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        };
    }

    // Cleanup hook
    window._cleanupCurrentView = () => {
        if (window._monitoringPollingTimer) {
            clearInterval(window._monitoringPollingTimer);
            window._monitoringPollingTimer = null;
        }
        if (window._monitoringChart) {
            window._monitoringChart.destroy();
            window._monitoringChart = null;
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
    document.addEventListener('DOMContentLoaded', window.initMonitoringView);
} else {
    window.initMonitoringView();
}
</script>
