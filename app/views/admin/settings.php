<?php
use App\Core\Security;

$currentAppName = $config['app_name'] ?? 'E-VOTING OSIS';
$currentAppLogo = !empty($config['app_logo']) ? $config['app_logo'] : '/assets/images/Logo_OSIS.svg';
$currentFooterText = $config['footer_text'] ?? '© 2026 Pemilihan Ketua OSIS • Sistem E-Voting Paper Card';
$currentFooterLogo = $config['footer_logo'] ?? '/assets/images/Logo_OSIS.svg';
$currentElectionName = $config['election_name'] ?? 'Pemilihan Ketua & Wakil Ketua OSIS 2026/2027';

// Tentukan mode footer logo saat ini
$footerLogoMode = 'custom';
if (empty($currentFooterLogo)) {
    $footerLogoMode = 'hidden';
} elseif ($currentFooterLogo === $currentAppLogo) {
    $footerLogoMode = 'same';
} elseif ($currentFooterLogo === '/assets/images/Logo_OSIS.svg') {
    $footerLogoMode = 'default';
}
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">
        
        <!-- Header Halaman -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-gear-wide-connected text-primary me-2"></i>Pengaturan Identitas & Footer
                </h3>
                <p class="text-muted small mb-0">
                    Kustomisasi judul sistem, logo navbar, teks hak cipta footer, dan logo footer aplikasi E-Voting.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="/admin/dashboard" class="btn btn-sm btn-paper-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
                <a href="/" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Halaman Pemilih di Tab Baru">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Bilik Suara
                </a>
            </div>
        </div>

        <!-- PRATINJAU LANGSUNG (LIVE PREVIEW) -->
        <div class="paper-card mb-4 border-primary border-top border-3">
            <div class="paper-card-header bg-light d-flex justify-content-between align-items-center px-4 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary rounded-circle p-1.5 d-inline-flex align-items-center justify-content-center">
                        <i class="bi bi-eye-fill text-white small"></i>
                    </span>
                    <span class="fw-bold text-dark">
                        Pratinjau Tampilan Langsung (Live Preview)
                    </span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 small">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Real-time
                </span>
            </div>
            
            <div class="p-3 p-md-4 bg-light bg-opacity-50">
                <!-- Browser Mockup Window -->
                <div class="shadow-sm rounded-3 overflow-hidden border bg-white">
                    <!-- Browser Window Header Bar -->
                    <div class="bg-light border-bottom px-3.5 py-2.5 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="rounded-circle d-inline-block" style="width: 11px; height: 11px; background-color: #ef4444;"></span>
                            <span class="rounded-circle d-inline-block" style="width: 11px; height: 11px; background-color: #f59e0b;"></span>
                            <span class="rounded-circle d-inline-block" style="width: 11px; height: 11px; background-color: #10b981;"></span>
                        </div>
                        <div class="bg-white px-3 py-1 rounded-pill border small text-muted text-truncate mx-2 shadow-2xs d-flex align-items-center gap-1.5" style="max-width: 380px; font-size: 0.76rem;">
                            <i class="bi bi-shield-check text-success"></i>
                            <span class="font-monospace text-secondary">https://e-voting.sekolah.local/</span>
                        </div>
                        <div class="small text-muted fw-semibold d-none d-sm-flex align-items-center gap-1">
                            <i class="bi bi-display text-primary"></i> <span style="font-size: 0.78rem;">Simulasi Layar</span>
                        </div>
                    </div>

                    <!-- 1. Pratinjau Header / Navbar dengan Padding Lega -->
                    <div class="bg-white border-bottom px-4 py-3.5 d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-2.5">
                            <img id="previewNavLogo" src="<?= Security::escape($currentAppLogo) ?>" alt="Navbar Logo" style="height: 38px; width: auto; object-fit: contain;">
                            <span id="previewNavText" class="fw-bold text-dark fs-5 mb-0" style="letter-spacing: -0.01em;">
                                <?= Security::escape($currentAppName) ?>
                            </span>
                        </div>
                        <div class="d-none d-md-flex align-items-center gap-2">
                            <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold small">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold small">
                                <i class="bi bi-people me-1"></i> Paslon
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold small">
                                <i class="bi bi-person-lines-fill me-1"></i> Data Pemilih
                            </span>
                            <span class="badge bg-light text-secondary border px-3 py-1.5 fw-semibold small">
                                <i class="bi bi-broadcast me-1"></i> Pantau Suara
                            </span>
                        </div>
                    </div>

                    <!-- 2. Konten Simulasi Tengah (Memberikan visual ruang yang proporsional) -->
                    <div class="p-4 p-md-5 bg-paper text-center">
                        <div class="p-3.5 border border-2 border-dashed rounded-3 bg-white bg-opacity-75 text-muted small mx-auto shadow-2xs" style="max-width: 520px;">
                            <i class="bi bi-layout-text-window text-primary fs-3 d-block mb-1.5"></i>
                            <span class="fw-bold text-dark d-block mb-1" style="font-size: 0.95rem;">Area Konten Sistem E-Voting</span>
                            <span class="text-secondary">Bagian atas menampilkan Header Navbar Brand, dan bagian bawah menampilkan Teks &amp; Logo Footer.</span>
                        </div>
                    </div>

                    <!-- 3. Pratinjau Footer Halaman dengan Padding Lega -->
                    <div class="bg-white border-top px-4 py-4 text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2.5 flex-wrap">
                            <img id="previewFooterLogo" src="<?= Security::escape($currentFooterLogo) ?>" alt="Footer Logo" style="height: 24px; width: auto; object-fit: contain; vertical-align: middle; <?= empty($currentFooterLogo) ? 'display: none !important;' : '' ?>">
                            <span id="previewFooterText" class="text-muted small fw-medium">
                                <?= Security::escape($currentFooterText) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORMULIR PENGATURAN UTAMA -->
        <form action="/admin/settings/update" method="POST" enctype="multipart/form-data" id="formSettings">
            <?= Security::csrfField() ?>

            <!-- 1. IDENTITAS APLIKASI (NAVBAR & HEADER) -->
            <div class="paper-card mb-4">
                <div class="paper-card-header d-flex align-items-center gap-2">
                    <i class="bi bi-window-sidebar text-primary"></i>
                    <span class="fw-bold">1. Identitas Header &amp; Navbar Brand</span>
                </div>
                <div class="p-4">
                    <!-- Nama Aplikasi -->
                    <div class="mb-4">
                        <label for="app_name" class="form-label fw-bold small text-uppercase text-secondary">
                            Nama Aplikasi / Judul Navbar <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-pencil-square"></i></span>
                            <input 
                                type="text" 
                                name="app_name" 
                                id="app_name" 
                                class="form-control form-control-paper" 
                                value="<?= Security::escape($currentAppName) ?>" 
                                placeholder="Contoh: E-VOTING OSIS" 
                                maxlength="150" 
                                required
                            >
                        </div>
                        <div class="form-text small">
                            Teks ini tampil di logo kiri atas (Navbar), kartu suara cetak, dan judul tab browser.
                        </div>
                    </div>

                    <!-- Logo Aplikasi / Navbar -->
                    <div class="row align-items-center g-3">
                        <div class="col-sm-auto text-center">
                            <label class="form-label fw-bold small text-uppercase text-secondary d-block">
                                Logo Saat Ini
                            </label>
                            <div class="border rounded p-2 bg-white d-inline-block shadow-xs" style="min-width: 90px; min-height: 80px;">
                                <img 
                                    id="currentAppLogoImg" 
                                    src="<?= Security::escape($currentAppLogo) ?>" 
                                    alt="Logo Utama" 
                                    style="max-height: 60px; max-width: 120px; object-fit: contain;"
                                >
                            </div>
                        </div>
                        <div class="col-sm">
                            <label for="app_logo" class="form-label fw-bold small text-uppercase text-secondary">
                                Unggah Logo Navbar Baru (Opsional)
                            </label>
                            <input 
                                type="file" 
                                name="app_logo" 
                                id="app_logo" 
                                class="form-control form-control-paper mb-2" 
                                accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml"
                            >
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                                <div class="form-text small m-0">
                                    Format: SVG, PNG, JPG, WEBP. Maksimal 2MB. Disarankan berlatar transparan.
                                </div>
                                <div class="form-check m-0">
                                    <input class="form-check-input" type="checkbox" name="reset_app_logo" id="reset_app_logo" value="1">
                                    <label class="form-check-label small text-muted" for="reset_app_logo">
                                        Reset ke Logo OSIS Default
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. PENGATURAN FOOTER -->
            <div class="paper-card mb-4">
                <div class="paper-card-header d-flex align-items-center gap-2">
                    <i class="bi bi-card-text text-primary"></i>
                    <span class="fw-bold">2. Pengaturan Footer &amp; Hak Cipta</span>
                </div>
                <div class="p-4">
                    <!-- Teks Footer -->
                    <div class="mb-4">
                        <label for="footer_text" class="form-label fw-bold small text-uppercase text-secondary">
                            Teks Footer / Hak Cipta <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-c-circle"></i></span>
                            <input 
                                type="text" 
                                name="footer_text" 
                                id="footer_text" 
                                class="form-control form-control-paper" 
                                value="<?= Security::escape($currentFooterText) ?>" 
                                placeholder="Contoh: © 2026 Pemilihan Ketua OSIS • Sistem E-Voting Paper Card" 
                                maxlength="255" 
                                required
                            >
                        </div>
                        <div class="form-text small">
                            Teks ini tampil di bagian paling bawah pada seluruh halaman sistem.
                        </div>
                    </div>

                    <!-- Logo Footer -->
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary d-block">
                            Logo Footer
                        </label>

                        <!-- Pilihan Tipe Logo Footer -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6 col-md-3">
                                <label class="card h-100 p-2.5 text-center border cursor-pointer option-logo-card <?= $footerLogoMode === 'same' ? 'border-primary bg-primary-subtle' : '' ?>" for="modeSame">
                                    <input class="form-check-input d-none" type="radio" name="footer_logo_mode" id="modeSame" value="same" <?= $footerLogoMode === 'same' ? 'checked' : '' ?>>
                                    <i class="bi bi-link-45deg fs-4 text-primary d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Sama dengan Navbar</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Otomatis ikuti logo utama</small>
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="card h-100 p-2.5 text-center border cursor-pointer option-logo-card <?= $footerLogoMode === 'custom' ? 'border-primary bg-primary-subtle' : '' ?>" for="modeCustom">
                                    <input class="form-check-input d-none" type="radio" name="footer_logo_mode" id="modeCustom" value="custom" <?= $footerLogoMode === 'custom' ? 'checked' : '' ?>>
                                    <i class="bi bi-cloud-arrow-up fs-4 text-primary d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Unggah Logo Khusus</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Logo tersendiri di footer</small>
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="card h-100 p-2.5 text-center border cursor-pointer option-logo-card <?= $footerLogoMode === 'default' ? 'border-primary bg-primary-subtle' : '' ?>" for="modeDefault">
                                    <input class="form-check-input d-none" type="radio" name="footer_logo_mode" id="modeDefault" value="default" <?= $footerLogoMode === 'default' ? 'checked' : '' ?>>
                                    <i class="bi bi-shield-check fs-4 text-primary d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Logo OSIS Bawaan</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Logo OSIS standar</small>
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-3">
                                <label class="card h-100 p-2.5 text-center border cursor-pointer option-logo-card <?= $footerLogoMode === 'hidden' ? 'border-primary bg-primary-subtle' : '' ?>" for="modeHidden">
                                    <input class="form-check-input d-none" type="radio" name="footer_logo_mode" id="modeHidden" value="hidden" <?= $footerLogoMode === 'hidden' ? 'checked' : '' ?>>
                                    <i class="bi bi-eye-slash fs-4 text-secondary d-block mb-1"></i>
                                    <span class="fw-bold small d-block">Tanpa Logo</span>
                                    <small class="text-muted d-block" style="font-size: 0.72rem;">Hanya tampilkan teks</small>
                                </label>
                            </div>
                        </div>

                        <!-- Upload Logo Footer Khusus (Tampil jika mode custom aktif) -->
                        <div id="customFooterLogoBox" class="p-3 bg-light rounded border <?= $footerLogoMode === 'custom' ? '' : 'd-none' ?>">
                            <div class="row align-items-center g-3">
                                <div class="col-sm-auto text-center">
                                    <div class="border rounded p-2 bg-white d-inline-block shadow-xs" style="min-width: 60px;">
                                        <img 
                                            id="currentFooterLogoImg" 
                                            src="<?= !empty($currentFooterLogo) ? Security::escape($currentFooterLogo) : '/assets/images/Logo_OSIS.svg' ?>" 
                                            alt="Logo Footer" 
                                            style="height: 35px; width: auto; object-fit: contain;"
                                        >
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <label for="footer_logo_file" class="form-label fw-bold small text-uppercase text-secondary mb-1">
                                        Pilih File Logo Khusus Footer
                                    </label>
                                    <input 
                                        type="file" 
                                        name="footer_logo_file" 
                                        id="footer_logo_file" 
                                        class="form-control form-control-paper" 
                                        accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml"
                                    >
                                    <div class="form-text small">
                                        Format: SVG, PNG, JPG, WEBP. Maksimal 2MB.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. NAMA ACARA & KEAMANAN PLENO (INFORMASI TAMBAHAN) -->
            <div class="paper-card mb-4">
                <div class="paper-card-header d-flex align-items-center gap-2">
                    <i class="bi bi-calendar2-event text-primary"></i>
                    <span class="fw-bold">3. Informasi Pemilihan &amp; Akses Pleno</span>
                </div>
                <div class="p-4">
                    <!-- Nama Acara Pemilihan -->
                    <div class="mb-4">
                        <label for="election_name" class="form-label fw-bold small text-uppercase text-secondary">
                            Nama Acara Pemilihan / Periode
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-trophy"></i></span>
                            <input 
                                type="text" 
                                name="election_name" 
                                id="election_name" 
                                class="form-control form-control-paper" 
                                value="<?= Security::escape($currentElectionName) ?>" 
                                placeholder="Contoh: Pemilihan Ketua & Wakil Ketua OSIS 2026/2027" 
                                maxlength="255"
                            >
                        </div>
                        <div class="form-text small">
                            Ditampilkan pada dashboard panitia, pemantauan live turnout, bilik suara pemilih, dan rapat pleno.
                        </div>
                    </div>

                    <!-- Ganti Kode Akses Hasil Pleno -->
                    <div>
                        <label for="result_code" class="form-label fw-bold small text-uppercase text-secondary">
                            Ganti Kode Akses Buka Hasil Pleno (Opsional)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input 
                                type="password" 
                                name="result_code" 
                                id="result_code" 
                                class="form-control form-control-paper" 
                                placeholder="Biarkan kosong jika tidak ingin mengubah kode akses rapat pleno" 
                                autocomplete="new-password"
                            >
                        </div>
                        <div class="form-text small">
                            Kode akses ini digunakan panitia untuk membuka perolehan suara di layar Rapat Pleno dan otorisasi tindakan sensitif.
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOMBOL SIMPAN & RESET -->
            <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger" id="btnResetDefaults">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset ke Setelan Default
                </button>
                <div class="d-flex gap-2">
                    <a href="/admin/dashboard" class="btn btn-paper-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-paper-primary px-4 fw-bold shadow-sm" id="btnSubmit">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>

        <!-- FORM TERSEMBUNYI UNTUK RESET DEFAULT -->
        <form action="/admin/settings/reset" method="POST" id="formResetDefaults" class="d-none">
            <?= Security::csrfField() ?>
        </form>

    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}
.option-logo-card {
    transition: all 0.2s ease-in-out;
}
.option-logo-card:hover {
    border-color: #0d6efd !important;
    background-color: #f8faff;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputAppName = document.getElementById('app_name');
    var inputFooterText = document.getElementById('footer_text');
    var previewNavText = document.getElementById('previewNavText');
    var previewFooterText = document.getElementById('previewFooterText');
    
    var inputAppLogo = document.getElementById('app_logo');
    var previewNavLogo = document.getElementById('previewNavLogo');
    var currentAppLogoImg = document.getElementById('currentAppLogoImg');
    var chkResetAppLogo = document.getElementById('reset_app_logo');
    
    var previewFooterLogo = document.getElementById('previewFooterLogo');
    var inputFooterLogoFile = document.getElementById('footer_logo_file');
    var currentFooterLogoImg = document.getElementById('currentFooterLogoImg');
    var customFooterLogoBox = document.getElementById('customFooterLogoBox');

    var defaultLogoUrl = '/assets/images/Logo_OSIS.svg';
    var activeAppLogoUrl = previewNavLogo.src;

    // 1. Live update Nama Aplikasi di Navbar
    if (inputAppName && previewNavText) {
        inputAppName.addEventListener('input', function() {
            var val = this.value.trim();
            previewNavText.textContent = val !== '' ? val : 'E-VOTING OSIS';
        });
    }

    // 2. Live update Teks Footer
    if (inputFooterText && previewFooterText) {
        inputFooterText.addEventListener('input', function() {
            var val = this.value.trim();
            previewFooterText.textContent = val !== '' ? val : '© 2026 Pemilihan Ketua OSIS • Sistem E-Voting Paper Card';
        });
    }

    // 3. Live update Logo Utama saat upload file
    if (inputAppLogo) {
        inputAppLogo.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    activeAppLogoUrl = e.target.result;
                    previewNavLogo.src = activeAppLogoUrl;
                    if (currentAppLogoImg) currentAppLogoImg.src = activeAppLogoUrl;
                    if (chkResetAppLogo) chkResetAppLogo.checked = false;
                    syncFooterLogoPreview();
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // Checkbox reset app logo
    if (chkResetAppLogo) {
        chkResetAppLogo.addEventListener('change', function() {
            if (this.checked) {
                if (inputAppLogo) inputAppLogo.value = '';
                activeAppLogoUrl = defaultLogoUrl;
                previewNavLogo.src = defaultLogoUrl;
                if (currentAppLogoImg) currentAppLogoImg.src = defaultLogoUrl;
                syncFooterLogoPreview();
            }
        });
    }

    // 4. Pengaturan Mode Logo Footer
    var radioModes = document.querySelectorAll('input[name="footer_logo_mode"]');
    var optionCards = document.querySelectorAll('.option-logo-card');

    function syncFooterLogoPreview() {
        var selectedMode = 'same';
        radioModes.forEach(function(r) {
            if (r.checked) selectedMode = r.value;
        });

        // Update card visual
        optionCards.forEach(function(card) {
            var radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                card.classList.add('border-primary', 'bg-primary-subtle');
            } else {
                card.classList.remove('border-primary', 'bg-primary-subtle');
            }
        });

        if (selectedMode === 'hidden') {
            previewFooterLogo.style.display = 'none';
            customFooterLogoBox.classList.add('d-none');
        } else if (selectedMode === 'same') {
            previewFooterLogo.style.display = 'inline-block';
            previewFooterLogo.src = activeAppLogoUrl;
            customFooterLogoBox.classList.add('d-none');
        } else if (selectedMode === 'default') {
            previewFooterLogo.style.display = 'inline-block';
            previewFooterLogo.src = defaultLogoUrl;
            customFooterLogoBox.classList.add('d-none');
        } else if (selectedMode === 'custom') {
            previewFooterLogo.style.display = 'inline-block';
            if (currentFooterLogoImg && currentFooterLogoImg.src) {
                previewFooterLogo.src = currentFooterLogoImg.src;
            }
            customFooterLogoBox.classList.remove('d-none');
        }
    }

    radioModes.forEach(function(radio) {
        radio.addEventListener('change', syncFooterLogoPreview);
    });

    // Upload custom footer logo
    if (inputFooterLogoFile) {
        inputFooterLogoFile.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewFooterLogo.src = e.target.result;
                    if (currentFooterLogoImg) currentFooterLogoImg.src = e.target.result;
                    previewFooterLogo.style.display = 'inline-block';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }

    // 5. Konfirmasi Reset Defaults
    var btnResetDefaults = document.getElementById('btnResetDefaults');
    var formResetDefaults = document.getElementById('formResetDefaults');
    if (btnResetDefaults && formResetDefaults) {
        btnResetDefaults.addEventListener('click', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Kembalikan ke Setelan Awal?',
                    text: 'Nama aplikasi, logo, dan teks footer akan dikembalikan ke standar awal sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Reset',
                    cancelButtonText: 'Batal'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        formResetDefaults.submit();
                    }
                });
            } else {
                if (confirm('Kembalikan semua pengaturan nama, teks, dan logo ke setelan awal sistem?')) {
                    formResetDefaults.submit();
                }
            }
        });
    }

    // Initial sync
    syncFooterLogoPreview();
});
</script>
