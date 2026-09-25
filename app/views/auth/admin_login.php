<?php
use App\Core\Security;
?>

<div class="row justify-content-center align-items-center py-4">
    <div class="col-md-5 col-lg-4">
        <div class="paper-card shadow-sm p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 80px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.08));">
                </div>
                <h3 class="fw-bold mb-1">Login Panitia</h3>
                <p class="text-muted small">Panel Administrasi E-Voting OSIS</p>
            </div>

            <form action="/admin/login/process" method="POST" autocomplete="off">
                <?= Security::csrfField() ?>

                <div class="mb-3">
                    <label for="username" class="form-label fw-bold small text-uppercase text-secondary">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-person text-muted"></i>
                        </span>
                        <input type="text" name="username" id="username" class="form-control form-control-paper border-start-0" placeholder="admin" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-bold small text-uppercase text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-key text-muted"></i>
                        </span>
                        <input type="password" name="password" id="password" class="form-control form-control-paper border-start-0" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-paper-primary py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Panel
                    </button>
                </div>
            </form>

            <div class="text-center mt-3 pt-3 border-top">
                <a href="/" class="small text-decoration-none text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Halaman Pemilih
                </a>
            </div>
        </div>
    </div>
</div>
