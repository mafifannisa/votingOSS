<?php
use App\Core\Session;
use App\Core\Security;

$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
$flashInfo = Session::getFlash('info');

$isAdmin = Session::has('admin_id');
$isVoter = Session::has('voter_id');

$appJsFile = dirname(__DIR__, 3) . '/public/assets/js/app.js';
$appJsVer = file_exists($appJsFile) ? (string) filemtime($appJsFile) : (string) time();
$cssFile = dirname(__DIR__, 3) . '/public/assets/css/paper-card.css';
$cssVer = file_exists($cssFile) ? (string) filemtime($cssFile) : (string) time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle ?? ($appName ?? 'E-Voting OSIS')) ?></title>
    <!-- Favicon OSIS -->
    <link rel="icon" type="image/svg+xml" href="<?= Security::escape($appLogo ?? '/assets/images/Logo_OSIS.svg') ?>">
    <link rel="alternate icon" href="<?= Security::escape($appLogo ?? '/assets/images/Logo_OSIS.svg') ?>">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Paper Card Theme CSS -->
    <link rel="stylesheet" href="/assets/css/paper-card.css?v=<?= $cssVer ?>">
</head>
<body class="bg-paper d-flex flex-column min-vh-100">
    <script>
        (function() {
            try {
                var isFs = sessionStorage.getItem('evoting_fullscreen') === '1' || window.location.search.indexOf('fs=1') !== -1;
                var path = window.location.pathname;
                if (isFs) {
                    if (path.indexOf('/admin/monitoring') !== -1) {
                        document.body.classList.add('monitoring-fs-active');
                        sessionStorage.setItem('evoting_fullscreen', '1');
                    } else if (path.indexOf('/admin/results') !== -1) {
                        document.body.classList.add('results-fs-active');
                        sessionStorage.setItem('evoting_fullscreen', '1');
                    } else if (path.indexOf('/vote') !== -1) {
                        document.body.classList.add('ballot-kiosk-active');
                        sessionStorage.setItem('evoting_fullscreen', '1');
                    } else if (path === '/' || path.indexOf('/login') !== -1) {
                        sessionStorage.setItem('evoting_fullscreen', '1');
                    }
                } else if (path.indexOf('/admin/monitoring') === -1 && path.indexOf('/admin/results') === -1 && path.indexOf('/vote') === -1 && path !== '/' && path.indexOf('/login') === -1) {
                    sessionStorage.removeItem('evoting_fullscreen');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Paper Navbar -->
    <?php
    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $isDashboardActive = str_starts_with($reqPath, '/admin/dashboard');
    $isCandidatesActive = str_starts_with($reqPath, '/admin/candidates');
    $isVotersActive = str_starts_with($reqPath, '/admin/voters');
    $isMonitoringActive = str_starts_with($reqPath, '/admin/monitoring');
    $isResultsActive = str_starts_with($reqPath, '/admin/results');
    $isBackupActive = str_starts_with($reqPath, '/admin/backup');
    $isSettingsActive = str_starts_with($reqPath, '/admin/settings');
    ?>
    <nav class="navbar navbar-expand-xl navbar-paper py-2.5 sticky-top">
        <div class="container-fluid px-3 px-lg-4 px-xl-5">
            <a class="navbar-brand navbar-brand-paper me-2 me-xl-4" href="/">
                <?php if (!empty($appLogo)): ?>
                    <img src="<?= Security::escape($appLogo) ?>" alt="Logo" class="navbar-logo">
                <?php endif; ?>
                <span><?= Security::escape($appName ?? 'E-VOTING OSIS') ?></span>
            </a>
            
            <button class="navbar-toggler border-0 shadow-none px-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $isDashboardActive ? 'active' : '' ?>" href="/admin/dashboard">
                                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isCandidatesActive ? 'active' : '' ?>" href="/admin/candidates">
                                <i class="bi bi-people"></i> <span>Paslon</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isVotersActive ? 'active' : '' ?>" href="/admin/voters">
                                <i class="bi bi-person-lines-fill"></i> <span>Data Pemilih</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isMonitoringActive ? 'active' : '' ?>" href="/admin/monitoring">
                                <i class="bi bi-broadcast"></i> <span>Pantau Suara</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isResultsActive ? 'active' : '' ?>" href="/admin/results">
                                <i class="bi bi-trophy"></i> <span>Hasil Pleno</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isBackupActive ? 'active' : '' ?>" href="/admin/backup">
                                <i class="bi bi-database-gear"></i> <span>Backup & Restore</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?= $isSettingsActive ? 'active' : '' ?>" href="/admin/settings">
                                <i class="bi bi-gear"></i> <span>Pengaturan</span>
                            </a>
                        </li>
                        <li class="nav-item ms-xl-2 border-top-mobile">
                            <form action="/admin/logout" method="POST" class="d-inline m-0">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 py-1.5 rounded-pill d-inline-flex align-items-center gap-1.5 fw-semibold" title="Keluar dari Panel Admin">
                                    <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    <?php elseif ($isVoter): ?>
                        <li class="nav-item">
                            <span class="badge bg-light text-dark border p-2 d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-person-badge"></i> <?= Security::escape(Session::get('voter_nama')) ?>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-muted small" href="/admin/login">
                                <i class="bi bi-shield-lock"></i> <span>Login Panitia</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="container my-4 flex-grow-1">
        <!-- Flash Messages untuk SweetAlert2 -->
        <?php if ($flashSuccess || $flashError || $flashInfo): ?>
            <div id="flashData" class="d-none"
                data-success="<?= Security::escape($flashSuccess ?? '') ?>"
                data-error="<?= Security::escape($flashError ?? '') ?>"
                data-info="<?= Security::escape($flashInfo ?? '') ?>"
            ></div>
            <noscript>
                <?php if ($flashSuccess): ?>
                    <div class="alert alert-success border-0 shadow-sm mb-3">
                        <i class="bi bi-check-circle-fill me-2"></i> <?= Security::escape($flashSuccess) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashError): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= Security::escape($flashError) ?>
                    </div>
                <?php endif; ?>
                <?php if ($flashInfo): ?>
                    <div class="alert alert-info border-0 shadow-sm mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i> <?= Security::escape($flashInfo) ?>
                    </div>
                <?php endif; ?>
            </noscript>
        <?php endif; ?>

        <!-- Dynamic Content -->
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted border-top bg-white mt-auto">
        <div class="container small d-flex align-items-center justify-content-center gap-2 flex-wrap">
            <?php if (!empty($footerLogo)): ?>
                <img src="<?= Security::escape($footerLogo) ?>" alt="Logo Footer" style="height: 22px; width: auto; object-fit: contain; vertical-align: middle;">
            <?php endif; ?>
            <span><?= Security::escape($footerText ?? ('© ' . date('Y') . ' Pemilihan Ketua OSIS • Sistem E-Voting Paper Card')) ?></span>
        </div>
    </footer>

    <!-- SweetAlert2 (Local offline support with CDN fallback) -->
    <script src="/assets/js/sweetalert2.all.min.js"></script>
    <!-- Chart.js (Local offline support) -->
    <script src="/assets/js/chart.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js?v=<?= $appJsVer ?>"></script>
</body>
</html>
