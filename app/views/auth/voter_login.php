<?php
use App\Core\Security;
?>

<div class="row justify-content-center align-items-center py-3">
    <div class="col-md-7 col-lg-5">
        <div class="paper-card shadow-sm p-4 p-md-5 position-relative">
            <!-- Tombol Layar Penuh (Fullscreen Kiosk) -->
            <button 
                type="button" 
                id="btnToggleLoginFS" 
                class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-3 rounded-pill px-2.5 py-1 d-flex align-items-center gap-1 shadow-xs" 
                title="Mode Layar Penuh Bilik Suara"
                style="z-index: 10;"
            >
                <i class="bi bi-arrows-fullscreen" id="loginFsIcon"></i>
                <span class="small fw-semibold d-none d-sm-inline" id="loginFsText">Layar Penuh</span>
            </button>

            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 84px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.08));">
                </div>
                <h3 class="fw-bold mb-1">Masuk Pemilih</h3>
                <p class="text-muted small">Pemilihan Ketua & Wakil Ketua OSIS Periode 2026/2027</p>
            </div>

            <!-- Tab Pemilihan Metode Masuk -->
            <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-4 border" id="authMethodTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link active rounded-pill fw-bold" 
                        id="manual-tab" 
                        data-bs-toggle="pill" 
                        data-bs-target="#tab-manual" 
                        type="button" 
                        role="tab"
                        aria-controls="tab-manual" 
                        aria-selected="true"
                    >
                        <i class="bi bi-keyboard me-1"></i> Ketik NISN
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button 
                        class="nav-link rounded-pill fw-bold" 
                        id="scanner-tab" 
                        data-bs-toggle="pill" 
                        data-bs-target="#tab-scanner" 
                        type="button" 
                        role="tab"
                        aria-controls="tab-scanner" 
                        aria-selected="false"
                    >
                        <i class="bi bi-qr-code-scan me-1"></i> Scan Barcode
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="authMethodTabContent">
                <!-- 1. TAB INPUT MANUAL -->
                <div class="tab-pane fade show active" id="tab-manual" role="tabpanel" aria-labelledby="manual-tab">
                    <form action="/login/process" method="POST" autocomplete="off" id="formManual">
                        <?= Security::csrfField() ?>

                        <div class="mb-4">
                            <label for="nisn" class="form-label fw-bold small text-uppercase text-secondary">
                                Nomor Induk Siswa Nasional (NISN)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-card-text text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    name="nisn" 
                                    id="nisn" 
                                    class="form-control form-control-paper border-start-0" 
                                    placeholder="Masukkan 10 digit NISN Anda"
                                    pattern="[0-9]*" 
                                    inputmode="numeric" 
                                    required
                                >
                            </div>
                            <div class="form-text mt-2 small text-muted">
                                <i class="bi bi-info-circle me-1"></i> NISN Anda hanya dapat digunakan <strong>1 kali</strong> untuk memilih.
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-paper-vote py-3">
                                <i class="bi bi-box-arrow-in-right me-2"></i> MASUK KE BILIK SUARA
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. TAB SCANNER WEBCAM -->
                <div class="tab-pane fade" id="tab-scanner" role="tabpanel" aria-labelledby="scanner-tab">
                    <!-- Dropdown Kamera (jika ada lebih dari 1 kamera) -->
                    <div class="mb-3" id="cameraSelectGroup" style="display: none;">
                        <label for="cameraSelect" class="form-label small fw-bold text-muted">Pilih Perangkat Kamera:</label>
                        <select id="cameraSelect" class="form-select form-select-sm"></select>
                    </div>

                    <!-- Viewport Kamera Scanner -->
                    <div class="scanner-viewport-wrapper mb-3" id="scannerWrapper">
                        <div id="reader"></div>
                        <div class="scanner-laser-line" id="scannerLaser" style="display: none;"></div>
                        
                        <!-- Overlay Standby / Placeholder -->
                        <div id="scannerPlaceholder" class="scanner-placeholder-overlay text-white">
                            <i class="bi bi-camera-video text-secondary display-4 mb-3"></i>
                            <h6 class="fw-bold mb-1">Pemindai Barcode / QR Kartu Siswa</h6>
                            <p class="small text-muted mb-3" style="max-width: 280px;">
                                Klik tombol di bawah untuk mengaktifkan webcam dan memindai barcode kartu.
                            </p>
                            <button type="button" class="btn btn-paper-primary btn-sm px-3" id="btnStartCamera">
                                <i class="bi bi-camera-fill me-1"></i> Aktifkan Kamera
                            </button>
                        </div>
                    </div>

                    <!-- Feedback Status Pemindaian -->
                    <div id="scanStatusMsg" class="alert alert-light border small text-center mb-3">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Arahkan barcode atau QR Code kartu pelajar ke depan kamera webcam.
                    </div>

                    <!-- Form Tersembunyi untuk Submit Otomatis -->
                    <form action="/login/process" method="POST" id="formScanSubmit">
                        <?= Security::csrfField() ?>
                        <input type="hidden" name="nisn" id="scannedNisnInput" value="">
                    </form>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnStopCamera" style="display: none;">
                            <i class="bi bi-stop-circle me-1"></i> Matikan Kamera
                        </button>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted ms-auto" id="btnSwitchToManual">
                            <i class="bi bi-keyboard me-1"></i> Ketik Manual
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 pt-3 border-top">
                <small class="text-muted">
                    Bermasalah dengan NISN atau Kartu? Hubungi panitia pemilihan di bilik utama.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Library Barcode / QR Scanner -->
<script src="/assets/js/html5-qrcode.min.js"></script>

<script>
window.initVoterLoginView = function() {
    let html5QrCode = null;
    let isScanning = false;
    let audioCtx = null;

    const scannerTab = document.getElementById('scanner-tab');
    const manualTab = document.getElementById('manual-tab');
    const btnStartCamera = document.getElementById('btnStartCamera');
    const btnStopCamera = document.getElementById('btnStopCamera');
    const btnSwitchToManual = document.getElementById('btnSwitchToManual');
    const scannerPlaceholder = document.getElementById('scannerPlaceholder');
    const scannerLaser = document.getElementById('scannerLaser');
    const scanStatusMsg = document.getElementById('scanStatusMsg');
    const cameraSelect = document.getElementById('cameraSelect');
    const cameraSelectGroup = document.getElementById('cameraSelectGroup');

    const formManual = document.getElementById('formManual');
    const formScanSubmit = document.getElementById('formScanSubmit');
    const manualInput = document.getElementById('nisn');
    const btnToggleLoginFS = document.getElementById('btnToggleLoginFS');

    // Bunyikan nada bip sukses pemindaian (Web Audio API murni tanpa file eksternal)
    function playBeep() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // 880 Hz (A5)
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.2);
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.2);
        } catch (e) {
            console.warn('Audio feedback error:', e);
        }
    }

    let availableCameras = null;

    // Inisialisasi daftar kamera yang tersedia
    async function initCameras() {
        if (availableCameras !== null) {
            return availableCameras;
        }

        try {
            // Gunakan enumerateDevices terlebih dahulu untuk deteksi perangkat tanpa error/prompt
            if (navigator.mediaDevices && typeof navigator.mediaDevices.enumerateDevices === 'function') {
                const devs = await navigator.mediaDevices.enumerateDevices();
                const videoDevs = devs.filter(d => d.kind === 'videoinput');
                if (videoDevs.length === 0) {
                    availableCameras = [];
                    return [];
                }
            }

            if (window.Html5Qrcode && typeof Html5Qrcode.getCameras === 'function') {
                const devices = await Html5Qrcode.getCameras();
                if (devices && devices.length > 0) {
                    availableCameras = devices;
                    if (cameraSelect) {
                        cameraSelect.innerHTML = '';
                        devices.forEach((dev, index) => {
                            const opt = document.createElement('option');
                            opt.value = dev.id;
                            opt.textContent = dev.label || `Kamera ${index + 1}`;
                            cameraSelect.appendChild(opt);
                        });

                        if (devices.length > 1 && cameraSelectGroup) {
                            cameraSelectGroup.style.display = 'block';
                        }
                    }
                    return availableCameras;
                }
            }
            availableCameras = [];
            return [];
        } catch (err) {
            // Tangani secara tenang jika perangkat tidak memiliki webcam fisik yang terpasang
            availableCameras = [];
            return [];
        }
    }

    // Deteksi keberadaan perangkat webcam
    async function detectCameraAvailability() {
        try {
            if (navigator.mediaDevices && typeof navigator.mediaDevices.enumerateDevices === 'function') {
                const devs = await navigator.mediaDevices.enumerateDevices();
                return devs.some(d => d.kind === 'videoinput');
            }
            const cams = await initCameras();
            return Array.isArray(cams) && cams.length > 0;
        } catch (e) {
            return false;
        }
    }

    async function startScanner() {
        if (isScanning) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        if (scannerPlaceholder) scannerPlaceholder.classList.add('d-none');
        if (scannerLaser) scannerLaser.style.display = 'block';
        if (btnStopCamera) btnStopCamera.style.display = 'inline-block';
        if (scanStatusMsg) {
            scanStatusMsg.className = 'alert alert-info border small text-center mb-3';
            scanStatusMsg.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan webcam... Arahkan barcode kartu Anda.';
        }

        const config = {
            fps: 15,
            qrbox: { width: 280, height: 160 },
            aspectRatio: 1.333333,
            formatsToSupport: [
                Html5QrcodeSupportedFormats.QR_CODE,
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.ITF
            ]
        };

        let cameraParam = cameraSelect ? cameraSelect.value : null;
        if (!cameraParam) {
            const devices = await initCameras();
            if (devices && devices.length > 0) {
                cameraParam = devices[0].id;
            }
        }

        // Jika tidak ada kamera yang terdeteksi pada perangkat ini
        if (!cameraParam) {
            await stopScanner();
            if (scanStatusMsg) {
                scanStatusMsg.className = 'alert alert-warning border small text-center mb-3';
                scanStatusMsg.innerHTML = '<i class="bi bi-camera-video-off text-warning me-1"></i> Perangkat kamera / webcam tidak ditemukan. Silakan gunakan opsi <strong>Ketik Manual NISN</strong>.';
            }
            if (window.PaperToast) {
                window.PaperToast.fire({
                    icon: 'info',
                    title: 'Webcam tidak terdeteksi. Silakan ketik NISN manual.'
                });
            }
            return;
        }

        try {
            await html5QrCode.start(
                cameraParam,
                config,
                onScanSuccess,
                () => {}
            );

            isScanning = true;
            if (scanStatusMsg) {
                scanStatusMsg.className = 'alert alert-light border small text-center mb-3';
                scanStatusMsg.innerHTML = '<i class="bi bi-camera-fill text-success me-1"></i> Kamera aktif. Arahkan barcode kartu siswa ke dalam kotak pemindai.';
            }
        } catch (err) {
            await stopScanner();
            if (scanStatusMsg) {
                scanStatusMsg.className = 'alert alert-danger border small text-center mb-3';
                scanStatusMsg.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Kamera tidak dapat diaktifkan: ' + (err.message || 'Izin kamera ditolak') + '. Silakan gunakan opsi <strong>Ketik Manual NISN</strong>.';
            }
            if (window.PaperToast) {
                window.PaperToast.fire({
                    icon: 'warning',
                    title: 'Kamera tidak dapat diakses. Silakan gunakan opsi Ketik NISN.'
                });
            }
        }
    }

    async function stopScanner() {
        if (html5QrCode && isScanning) {
            try {
                await html5QrCode.stop();
            } catch (err) {
                console.warn('Error stopping scanner:', err);
            }
        }
        isScanning = false;
        if (scannerPlaceholder) scannerPlaceholder.classList.remove('d-none');
        if (scannerLaser) scannerLaser.style.display = 'none';
        if (btnStopCamera) btnStopCamera.style.display = 'none';
    }

    // Fungsi Utama Autentikasi Pemilih secara Asynchronous (Mulus Tanpa Jeda / Reload)
    async function submitVoterLogin(formEl, nisnVal) {
        if (!nisnVal) {
            if (window.PaperAlert) {
                window.PaperAlert.warning('Silakan masukkan NISN Anda terlebih dahulu.', 'NISN Kosong');
            } else {
                alert('Silakan masukkan NISN Anda terlebih dahulu.');
            }
            if (manualInput) manualInput.focus();
            return;
        }

        const submitBtn = formEl ? formEl.querySelector('button[type="submit"]') : null;
        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memverifikasi...';
        }

        try {
            const formData = new FormData(formEl);
            formData.set('nisn', nisnVal);

            const response = await fetch('/login/process', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json().catch(() => null);

            if (!response.ok || !data || !data.success) {
                const errorMsg = (data && data.message) ? data.message : 'NISN tidak valid atau belum terdaftar.';
                if (window.PaperAlert) {
                    window.PaperAlert.error(errorMsg, 'Gagal Masuk');
                } else {
                    alert(errorMsg);
                }

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }

                if (manualInput) {
                    manualInput.select();
                    manualInput.focus();
                }
                return;
            }

            // Hentikan pemindai webcam sebelum berpindah tampilan
            await stopScanner();

            // Berpindah ke bilik suara (halaman /vote) secara seamless tanpa reload!
            // Fullscreen tetap aktif terus-menerus tanpa jeda atau keluar layar!
            if (window.seamlessNavigate) {
                await window.seamlessNavigate(data.redirect || '/vote');
            } else {
                window.location.href = data.redirect || '/vote';
            }
        } catch (err) {
            console.error('Login submit error:', err);
            // Fallback submit standar jika ada error fetch fatal
            if (formEl) formEl.submit();
        }
    }

    function onScanSuccess(decodedText) {
        if (!isScanning) return;

        // Ambil urutan angka NISN dari hasil pemindaian
        const matched = decodedText.match(/[0-9]{8,15}/);
        const nisnVal = matched ? matched[0] : decodedText.replace(/[^0-9]/g, '');

        if (!nisnVal || nisnVal.length < 8) {
            if (scanStatusMsg) {
                scanStatusMsg.className = 'alert alert-warning border small text-center mb-3';
                scanStatusMsg.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> Barcode terbaca: "${decodedText}", namun bukan format NISN yang valid.`;
            }
            if (window.PaperToast) {
                window.PaperToast.fire({
                    icon: 'warning',
                    title: 'Format barcode tidak valid sebagai NISN.'
                });
            }
            return;
        }

        // Hentikan kamera dan putar feedback suara
        stopScanner();
        playBeep();

        if (scanStatusMsg) {
            scanStatusMsg.className = 'alert alert-success border small text-center mb-3';
            scanStatusMsg.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> NISN Terdeteksi: <strong>${nisnVal}</strong>. Masuk ke bilik suara...`;
        }

        const scannedInput = document.getElementById('scannedNisnInput');
        if (scannedInput) scannedInput.value = nisnVal;

        setTimeout(() => {
            submitVoterLogin(formScanSubmit, nisnVal);
        }, 300);
    }

    // Submit form manual
    if (formManual) {
        formManual.onsubmit = (e) => {
            e.preventDefault();
            const val = manualInput ? manualInput.value.trim() : '';
            submitVoterLogin(formManual, val);
        };
    }

    // Tombol Layar Penuh (Fullscreen Kiosk)
    if (btnToggleLoginFS) {
        btnToggleLoginFS.onclick = () => {
            if (window.toggleEvotingFullscreen) {
                window.toggleEvotingFullscreen();
            }
        };
    }

    // Event Listeners Kamera Scanner
    if (btnStartCamera) btnStartCamera.addEventListener('click', startScanner);
    if (btnStopCamera) btnStopCamera.addEventListener('click', stopScanner);

    if (cameraSelect) {
        cameraSelect.addEventListener('change', () => {
            if (isScanning) {
                stopScanner().then(() => startScanner());
            }
        });
    }

    // Otomatis siapkan kamera saat tab scanner dibuka
    if (scannerTab) {
        scannerTab.addEventListener('shown.bs.tab', () => {
            startScanner();
        });
    }

    // Matikan kamera jika user berpindah ke tab manual
    if (manualTab) {
        manualTab.addEventListener('shown.bs.tab', () => {
            stopScanner();
            if (manualInput) manualInput.focus();
        });
    }

    if (btnSwitchToManual) {
        btnSwitchToManual.addEventListener('click', () => {
            if (manualTab && typeof bootstrap !== 'undefined') {
                const bsTab = new bootstrap.Tab(manualTab);
                bsTab.show();
            }
        });
    }

    // Cleanup hook ketika halaman berpindah
    window._cleanupCurrentView = () => {
        stopScanner();
    };

    // Tentukan tab default secara cerdas:
    // Jika ada webcam terdeteksi -> default ke 'Scan Barcode' dan nyalakan kamera
    // Jika tidak ada webcam -> default tetap di 'Ketik NISN' dan beri fokus ke input
    detectCameraAvailability().then((hasCamera) => {
        if (hasCamera) {
            if (scannerTab && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                const bsTab = bootstrap.Tab.getOrCreateInstance ? bootstrap.Tab.getOrCreateInstance(scannerTab) : new bootstrap.Tab(scannerTab);
                bsTab.show();
            } else if (scannerTab) {
                scannerTab.click();
            }
        } else {
            if (manualInput) {
                setTimeout(() => {
                    try { manualInput.focus({ preventScroll: true }); } catch (e) {}
                }, 100);
            }
        }
    }).catch(() => {
        if (manualInput) {
            setTimeout(() => {
                try { manualInput.focus({ preventScroll: true }); } catch (e) {}
            }, 100);
        }
    });

    // Sinkronkan status tombol Fullscreen
    const isFS = !!(document.fullscreenElement || sessionStorage.getItem('evoting_fullscreen') === '1');
    if (window.setEvotingFullscreenUI) {
        window.setEvotingFullscreenUI(isFS);
    }
};

// Initial run
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initVoterLoginView);
} else {
    window.initVoterLoginView();
}
</script>
