<?php
use App\Core\Security;
?>

<div class="row justify-content-center align-items-center py-3">
    <div class="col-md-7 col-lg-5">
        <div class="paper-card shadow-sm p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="d-inline-flex p-3 rounded-circle bg-light border mb-3">
                    <i class="bi bi-person-badge-fill text-primary" style="font-size: 2.3rem;"></i>
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
                                    autofocus
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
document.addEventListener('DOMContentLoaded', () => {
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

    // Inisialisasi daftar kamera yang tersedia
    async function initCameras() {
        if (!window.Html5Qrcode) {
            console.error('Html5Qrcode library not loaded.');
            return [];
        }

        try {
            const devices = await Html5Qrcode.getCameras();
            if (devices && devices.length > 0) {
                cameraSelect.innerHTML = '';
                devices.forEach((dev, index) => {
                    const opt = document.createElement('option');
                    opt.value = dev.id;
                    opt.textContent = dev.label || `Kamera ${index + 1}`;
                    cameraSelect.appendChild(opt);
                });

                if (devices.length > 1) {
                    cameraSelectGroup.style.display = 'block';
                }
                return devices;
            }
        } catch (err) {
            console.warn('Gagal memuat list kamera otomatis:', err);
        }
        return [];
    }

    async function startScanner() {
        if (isScanning) return;

        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("reader");
        }

        // Sembunyikan placeholder menggunakan d-none
        scannerPlaceholder.classList.add('d-none');
        scannerLaser.style.display = 'block';
        btnStopCamera.style.display = 'inline-block';
        scanStatusMsg.className = 'alert alert-info border small text-center mb-3';
        scanStatusMsg.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan webcam... Arahkan barcode kartu Anda.';

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

        let cameraParam = cameraSelect.value;
        if (!cameraParam) {
            const devices = await initCameras();
            if (devices.length > 0) {
                cameraParam = devices[0].id;
            }
        }
        if (!cameraParam) {
            cameraParam = { facingMode: "user" };
        }

        try {
            await html5QrCode.start(
                cameraParam,
                config,
                onScanSuccess,
                (errorMessage) => {
                    // Scanning in progress...
                }
            );

            isScanning = true;
            scanStatusMsg.className = 'alert alert-light border small text-center mb-3';
            scanStatusMsg.innerHTML = '<i class="bi bi-camera-fill text-success me-1"></i> Kamera aktif. Arahkan barcode kartu siswa ke dalam kotak pemindai.';
        } catch (err) {
            console.error('Error starting camera:', err);
            stopScanner();
            scanStatusMsg.className = 'alert alert-danger border small text-center mb-3';
            scanStatusMsg.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Kamera tidak dapat diaktifkan: ' + (err.message || 'Izin kamera ditolak') + '. Silakan gunakan opsi <strong>Ketik NISN</strong>.';
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
        scannerPlaceholder.classList.remove('d-none');
        scannerLaser.style.display = 'none';
        btnStopCamera.style.display = 'none';
    }

    function onScanSuccess(decodedText, decodedResult) {
        if (!isScanning) return;

        // Ambil urutan angka NISN dari hasil pemindaian (ekstrak angka)
        const matched = decodedText.match(/[0-9]{8,15}/);
        const nisnVal = matched ? matched[0] : decodedText.replace(/[^0-9]/g, '');

        if (!nisnVal || nisnVal.length < 8) {
            scanStatusMsg.className = 'alert alert-warning border small text-center mb-3';
            scanStatusMsg.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> Barcode terbaca: "${decodedText}", namun tidak ditemukan format NISN yang valid.`;
            return;
        }

        // Hentikan pemindaian dan berikan feedback
        stopScanner();
        playBeep();

        scanStatusMsg.className = 'alert alert-success border small text-center mb-3';
        scanStatusMsg.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Berhasil memindai NISN: <strong>${nisnVal}</strong>. Masuk ke bilik suara...`;

        // Masukkan ke input form dan submit otomatis
        document.getElementById('scannedNisnInput').value = nisnVal;
        
        setTimeout(() => {
            document.getElementById('formScanSubmit').submit();
        }, 600);
    }

    // Event Listeners
    btnStartCamera.addEventListener('click', startScanner);
    btnStopCamera.addEventListener('click', stopScanner);

    cameraSelect.addEventListener('change', () => {
        if (isScanning) {
            stopScanner().then(() => startScanner());
        }
    });

    // Otomatis siapkan kamera saat tab scanner dibuka
    scannerTab.addEventListener('shown.bs.tab', () => {
        initCameras();
        startScanner();
    });

    // Matikan kamera jika user berpindah ke tab manual
    manualTab.addEventListener('shown.bs.tab', () => {
        stopScanner();
        const manualInput = document.getElementById('nisn');
        if (manualInput) manualInput.focus();
    });

    btnSwitchToManual.addEventListener('click', () => {
        const bsTab = new bootstrap.Tab(manualTab);
        bsTab.show();
    });
});
</script>
