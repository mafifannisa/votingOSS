<?php
use App\Core\Session;
use App\Core\Security;

$flashSuccess = Session::getFlash('success');
$flashError = Session::getFlash('error');
$flashInfo = Session::getFlash('info');

$isAdmin = Session::has('admin_id');
$isVoter = Session::has('voter_id');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle ?? 'E-Voting OSIS') ?></title>
    <!-- Favicon OSIS -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/Logo_OSIS.svg">
    <link rel="alternate icon" href="/assets/images/Logo_OSIS.svg">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Paper Card Theme CSS -->
    <link rel="stylesheet" href="/assets/css/paper-card.css">
</head>
<body class="bg-paper d-flex flex-column min-vh-100">

    <!-- Paper Navbar -->
    <nav class="navbar navbar-expand-lg navbar-paper py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-paper" href="/">
                <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" class="navbar-logo me-2" style="height: 38px; width: auto; object-fit: contain;">
                <span>E-VOTING OSIS</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="/admin/dashboard">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="/admin/candidates">
                                <i class="bi bi-people me-1"></i> Paslon
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="/admin/voters">
                                <i class="bi bi-person-lines-fill me-1"></i> Data Pemilih
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="/admin/monitoring">
                                <i class="bi bi-broadcast me-1"></i> Pantau Suara
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="/admin/results">
                                <i class="bi bi-trophy me-1"></i> Hasil Pleno
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold" href="/admin/backup">
                                <i class="bi bi-database-gear me-1"></i> Backup & Restore
                            </a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <form action="/admin/logout" method="POST" class="d-inline">
                                <?= Security::csrfField() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger px-3">
                                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                                </button>
                            </form>
                        </li>
                    <?php elseif ($isVoter): ?>
                        <li class="nav-item">
                            <span class="badge bg-light text-dark border p-2">
                                <i class="bi bi-person-badge me-1"></i> <?= Security::escape(Session::get('voter_nama')) ?>
                            </span>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-muted small" href="/admin/login">
                                <i class="bi bi-shield-lock me-1"></i> Login Panitia
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
        <div class="container small d-flex align-items-center justify-content-center gap-2">
            <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 22px; width: auto; object-fit: contain; vertical-align: middle;">
            <span>&copy; <?= date('Y') ?> Pemilihan Ketua OSIS &bull; Sistem E-Voting Paper Card</span>
        </div>
    </footer>

    <!-- SweetAlert2 (Local offline support with CDN fallback) -->
    <script src="/assets/js/sweetalert2.all.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
