<?php
use App\Core\Security;
?>

<div class="row justify-content-center py-3">
    <div class="col-lg-7 col-md-9">
        <div class="paper-card shadow-sm p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="mb-3">
                    <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 75px; width: auto; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.08));">
                </div>
                <h3 class="fw-bold mb-1">Inisialisasi & Restore Database</h3>
                <p class="text-muted small">Konfigurasi otomatis database untuk server baru</p>
            </div>

            <?php if ($isInstalled): ?>
                <div class="alert alert-warning border-0 small mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <strong>Perhatian:</strong> Sistem mendeteksi database sudah pernah diinstal sebelumnya. Jika Anda melanjutkan, tabel dan konfigurasi database akan ditimpa/diperbarui.
                </div>
            <?php else: ?>
                <div class="alert alert-info border-0 small mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i>
                    Fitur ini akan secara otomatis:
                    <ol class="mb-0 ps-3 mt-1">
                        <li>Membuat database MySQL baru jika belum ada di server.</li>
                        <li>Mengimpor seluruh tabel pemilihan (<em>schema.sql</em>).</li>
                        <li>Menyiapkan akun default panitia (<strong>admin / admin123</strong>).</li>
                        <li>Menyimpan kredensial ke <code>config/database.php</code>.</li>
                    </ol>
                </div>
            <?php endif; ?>

            <form action="/install/process" method="POST" autocomplete="off">
                <div class="row g-3 mb-3">
                    <div class="col-md-8">
                        <label for="host" class="form-label fw-bold small text-uppercase text-secondary">
                            Host MySQL Server <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="host" 
                            id="host" 
                            class="form-control form-control-paper" 
                            value="<?= Security::escape($config['host'] ?? '127.0.0.1') ?>" 
                            required
                        >
                        <div class="form-text small">Gunakan <code>127.0.0.1</code> atau <code>localhost</code> jika di server lokal.</div>
                    </div>
                    <div class="col-md-4">
                        <label for="port" class="form-label fw-bold small text-uppercase text-secondary">
                            Port <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="port" 
                            id="port" 
                            class="form-control form-control-paper" 
                            value="<?= (int)($config['port'] ?? 3306) ?>" 
                            required
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label for="database" class="form-label fw-bold small text-uppercase text-secondary">
                        Nama Database <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="database" 
                        id="database" 
                        class="form-control form-control-paper" 
                        value="<?= Security::escape($config['database'] ?? 'voting_oss') ?>" 
                        required
                    >
                    <div class="form-text small">Jika database belum ada di server, sistem akan otomatis membuatnya.</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="username" class="form-label fw-bold small text-uppercase text-secondary">
                            Username MySQL <span class="text-danger">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            class="form-control form-control-paper" 
                            value="<?= Security::escape($config['username'] ?? 'root') ?>" 
                            required
                        >
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-bold small text-uppercase text-secondary">
                            Password MySQL
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control form-control-paper" 
                            placeholder="Kosongkan jika tanpa password"
                        >
                    </div>
                </div>

                <div class="form-check p-3 bg-light rounded border mb-4">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="seed_demo" id="seed_demo" value="1" checked>
                    <label class="form-check-label fw-semibold small" for="seed_demo">
                        Sertakan Data Demo / Sampel (2 Pasangan Calon & 8 Siswa DPT untuk pengujian)
                    </label>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-paper-vote py-3 fw-bold">
                        <i class="bi bi-play-circle-fill me-2"></i> PASANG & RESTORE DATABASE SEKARANG
                    </button>
                    <a href="/admin/login" class="btn btn-paper-secondary py-2">
                        Batal / Sudah Ada Database (Ke Halaman Login)
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
