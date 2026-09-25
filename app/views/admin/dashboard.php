<?php
use App\Core\Security;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
        <img src="/assets/images/Logo_OSIS.svg" alt="Logo OSIS" style="height: 52px; width: auto; object-fit: contain;">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Panitia</h2>
            <p class="text-muted mb-0"><?= Security::escape($electionConfig['election_name'] ?? 'Pemilihan Ketua OSIS') ?></p>
        </div>
    </div>
    <div>
        <a href="/admin/results" class="btn btn-warning fw-bold px-4 py-2 shadow-sm">
            <i class="bi bi-trophy-fill me-1"></i> LIHAT HASIL PEMILIHAN
        </a>
    </div>
</div>

<!-- Statistic Paper Cards -->
<div class="row g-3 mb-4">
    <!-- Total DPT -->
    <div class="col-sm-6 col-lg-3">
        <div class="paper-card p-4 h-100 border-start border-primary border-4">
            <div class="text-muted small text-uppercase fw-bold mb-1">Total Pemilih (DPT)</div>
            <div class="display-6 fw-bold text-dark mb-1"><?= number_format($totalVoters, 0, ',', '.') ?></div>
            <small class="text-muted">
                <a href="/admin/voters" class="text-decoration-none">Kelola DPT &rarr;</a>
            </small>
        </div>
    </div>

    <!-- Sudah Memilih -->
    <div class="col-sm-6 col-lg-3">
        <div class="paper-card p-4 h-100 border-start border-success border-4">
            <div class="text-muted small text-uppercase fw-bold mb-1">Sudah Memilih</div>
            <div class="display-6 fw-bold text-success mb-1"><?= number_format($totalVoted, 0, ',', '.') ?></div>
            <small class="text-muted">Suara sah tercatat</small>
        </div>
    </div>

    <!-- Belum Memilih -->
    <div class="col-sm-6 col-lg-3">
        <div class="paper-card p-4 h-100 border-start border-danger border-4">
            <div class="text-muted small text-uppercase fw-bold mb-1">Belum Memilih</div>
            <div class="display-6 fw-bold text-danger mb-1"><?= number_format($totalNotVoted, 0, ',', '.') ?></div>
            <small class="text-muted">Belum menggunakan hak</small>
        </div>
    </div>

    <!-- Partisipasi (%) -->
    <div class="col-sm-6 col-lg-3">
        <div class="paper-card p-4 h-100 border-start border-info border-4">
            <div class="text-muted small text-uppercase fw-bold mb-1">Partisipasi</div>
            <div class="display-6 fw-bold text-primary mb-1"><?= $participationRate ?>%</div>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, $participationRate) ?>%;" aria-valuenow="<?= $participationRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>
    </div>
</div>

<!-- Overview Paslon & Shortcut Actions -->
<div class="row g-4">
    <!-- Daftar Paslon Terdaftar -->
    <div class="col-lg-8">
        <div class="paper-card h-100">
            <div class="paper-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Pasangan Calon Terdaftar</span>
                <a href="/admin/candidates/create" class="btn btn-sm btn-paper-primary">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Paslon
                </a>
            </div>
            <div class="p-4">
                <?php if (empty($candidates)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-person-x display-5 d-block mb-2"></i>
                        Belum ada pasangan calon yang didaftarkan.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">No. Urut</th>
                                    <th>Kandidat</th>
                                    <th>Jurusan</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidates as $c): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                0<?= Security::escape($c['nomor_urut']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= Security::escape($c['nama_ketua']) ?></div>
                                            <div class="text-muted small">& <?= Security::escape($c['nama_wakil']) ?></div>
                                        </td>
                                        <td class="small text-muted">
                                            <div><?= Security::escape($c['jurusan_ketua']) ?></div>
                                            <div><?= Security::escape($c['jurusan_wakil']) ?></div>
                                        </td>
                                        <td>
                                            <?php if ((int)$c['status'] === 1): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="/admin/candidates/edit/<?= (int)$c['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & Server Info -->
    <div class="col-lg-4">
        <div class="paper-card mb-4">
            <div class="paper-card-header">
                <i class="bi bi-lightning-charge me-2"></i>Tindakan Cepat
            </div>
            <div class="p-3 d-grid gap-2">
                <a href="/admin/voters/import" class="btn btn-paper-secondary text-start py-2">
                    <i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i> Import Data Pemilih (Excel)
                </a>
                <a href="/admin/voters/create" class="btn btn-paper-secondary text-start py-2">
                    <i class="bi bi-person-plus me-2 text-primary"></i> Tambah Pemilih Manual
                </a>
                <a href="/admin/candidates/create" class="btn btn-paper-secondary text-start py-2">
                    <i class="bi bi-person-badge me-2 text-warning"></i> Tambah Paslon Baru
                </a>
                <a href="/admin/backup" class="btn btn-paper-secondary text-start py-2">
                    <i class="bi bi-database-gear me-2 text-info"></i> Backup & Restore Database
                </a>
                <a href="/" target="_blank" class="btn btn-outline-primary text-start py-2">
                    <i class="bi bi-box-arrow-up-right me-2"></i> Buka Bilik Pemilih (Tab Baru)
                </a>
            </div>
        </div>

        <div class="paper-card p-3 bg-light">
            <h6 class="fw-bold small text-muted text-uppercase mb-2">
                <i class="bi bi-hdd-network me-1"></i> Jaringan Multi-Perangkat (LAN)
            </h6>
            <p class="small text-muted mb-0">
                Sistem melayani hingga 8 perangkat (4 tablet + 4 laptop) secara bersamaan melalui server lokal ini.
            </p>
        </div>
    </div>
</div>
