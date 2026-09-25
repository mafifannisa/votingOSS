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
                <i class="bi bi-box-seam-fill text-primary"></i>
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
                            <a class="nav-link fw-semibold" href="/admin/results">
                                <i class="bi bi-trophy me-1"></i> Hasil
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
        <!-- Flash Messages -->
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= Security::escape($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($flashError): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= Security::escape($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($flashInfo): ?>
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?= Security::escape($flashInfo) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Dynamic Content -->
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="py-3 text-center text-muted border-top bg-white mt-auto">
        <div class="container small">
            <span>&copy; <?= date('Y') ?> Pemilihan Ketua OSIS &bull; Sistem E-Voting Paper Card</span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
</body>
</html>
