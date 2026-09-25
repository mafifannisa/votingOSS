<?php
use App\Core\Security;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape($pageTitle ?? 'Cetak Kartu Pemilih DPT (Square) - E-Voting OSIS') ?></title>
    <!-- Favicon OSIS -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/Logo_OSIS.svg">
    <link rel="alternate icon" href="/assets/images/Logo_OSIS.svg">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Paper Card Theme CSS -->
    <link rel="stylesheet" href="/assets/css/paper-card.css">
    <!-- QR Code Generator Offline Library -->
    <script src="/assets/js/qrcode.min.js"></script>

    <style>
        /* ==========================================================================
           Print & Screen Styles for Square DPT Cards
           ========================================================================== */
        
        :root {
            --card-size: 61mm;
            --card-gap: 3.5mm;
        }

        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            color: #0f172a;
        }

        /* Top Action Toolbar (Screen Only) */
        .print-toolbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        /* Preview Canvas (Screen View) */
        .print-container {
            max-width: 216mm;
            margin: 1.5rem auto 3rem auto;
            padding: 0;
        }

        .sheet-a4 {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto 2rem auto;
            padding: 10mm 9mm;
            box-sizing: border-box;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        /* 3 Columns Grid for 12 Square Cards per A4 Sheet */
        .card-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--card-gap);
            justify-content: center;
        }

        /* Square Voter Card (Format Kotak Presisi) */
        .voter-card-square {
            width: 100%;
            height: var(--card-size);
            aspect-ratio: 1 / 1;
            box-sizing: border-box;
            background: #ffffff;
            border: 1.5px dashed #94a3b8;
            border-radius: 6px;
            padding: 2.8mm 3mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            position: relative;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Corner Cut Marker Indicator */
        .voter-card-square::before {
            content: "✂";
            position: absolute;
            top: -7px;
            right: 4px;
            font-size: 9px;
            color: #94a3b8;
            background: #ffffff;
            padding: 0 2px;
            line-height: 1;
        }

        /* Card Header */
        .card-header-mini {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            width: 100%;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1.5mm;
            margin-bottom: 1mm;
        }

        .card-header-mini img {
            height: 15px;
            width: auto;
            object-fit: contain;
        }

        .card-header-titles {
            text-align: left;
            line-height: 1.1;
        }

        .card-header-title {
            font-size: 7.5pt;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.3px;
        }

        .card-header-sub {
            font-size: 5.5pt;
            font-weight: 600;
            color: #64748b;
        }

        /* QR Code Container */
        .card-qr-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: auto 0;
            background: #ffffff;
            padding: 2px;
        }

        .card-qr-wrapper canvas,
        .card-qr-wrapper img {
            display: block !important;
            margin: 0 auto;
            max-width: 88px !important;
            max-height: 88px !important;
            width: 88px !important;
            height: 88px !important;
        }

        /* Identity Details (Di Bawah QR Code) */
        .card-identity {
            width: 100%;
            border-top: 1px solid #f1f5f9;
            padding-top: 1.2mm;
            line-height: 1.15;
        }

        .voter-nisn-badge {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 8pt;
            font-weight: 700;
            color: #0f172a;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 1px 6px;
            letter-spacing: 0.5px;
            margin-bottom: 1.5px;
        }

        .voter-name {
            font-size: 8pt;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 55mm;
            margin: 0 auto;
        }

        .voter-meta {
            font-size: 6.5pt;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 55mm;
            margin: 0 auto;
        }

        .card-footer-tip {
            font-size: 5pt;
            color: #94a3b8;
            letter-spacing: 0.2px;
            margin-top: 1px;
        }

        /* ==========================================================================
           PRINT SPECIFIC RULES (@media print)
           ========================================================================== */
        @media print {
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print,
            .print-toolbar {
                display: none !important;
            }

            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .sheet-a4 {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 8mm 6mm !important;
                box-shadow: none !important;
                page-break-after: always;
                break-after: page;
            }

            .sheet-a4:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .voter-card-square {
                border: 1.5px dashed #64748b !important;
                box-shadow: none !important;
            }

            @page {
                size: A4 portrait;
                margin: 6mm 6mm;
            }
        }
    </style>
</head>
<body>

    <!-- 1. FLOATING CONTROL TOOLBAR (SCREEN ONLY) -->
    <div class="print-toolbar no-print">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="/admin/voters" class="btn btn-sm btn-outline-secondary" title="Kembali ke Daftar DPT">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-qr-code-scan text-primary me-1"></i> Cetak Kartu Pemilih DPT (Square)
                    </h5>
                    <small class="text-muted">
                        Total <strong><?= count($voters) ?></strong> kartu siap cetak • Format Kotak (Square) Siap Gunting
                    </small>
                </div>
            </div>

            <!-- Filter Kelas & Search -->
            <form action="/admin/voters/print" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-1">
                    <span class="small text-muted">Kelas:</span>
                    <select name="kelas" class="form-select form-select-sm form-select-paper w-auto" onchange="this.form.submit()">
                        <option value="">Semua Kelas (<?= count($voters) ?>)</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= Security::escape($c) ?>" <?= $selectedClass === $c ? 'selected' : '' ?>>
                                Kelas <?= Security::escape($c) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!empty($selectedClass)): ?>
                    <a href="/admin/voters/print" class="btn btn-sm btn-link text-muted text-decoration-none">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                <?php endif; ?>

                <button type="button" onclick="window.print()" class="btn btn-sm btn-paper-success ms-md-2 px-3 shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak / Simpan PDF
                </button>
            </form>
        </div>

        <!-- Printing Tips Alert -->
        <div class="container-fluid mt-2">
            <div class="alert alert-info py-2 px-3 mb-0 small border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Tips Hasil Cetak Terbaik:</strong> Pada dialog Print browser, pilih Kertas <strong>A4</strong>, Orientasi <strong>Portrait</strong>, Margins: <strong>None</strong> atau <strong>Minimum</strong>, dan aktifkan opsi <strong>Background graphics</strong>.
                </div>
                <div class="text-secondary fw-semibold">
                    <i class="bi bi-scissors me-1"></i> 1 Lembar A4 memuat 12 Kartu Kotak (3x4 Grid)
                </div>
            </div>
        </div>
    </div>

    <!-- 2. PRINT SHEETS PREVIEW (A4 PAGES) -->
    <div class="print-container">
        <?php if (empty($voters)): ?>
            <div class="sheet-a4 d-flex flex-column align-items-center justify-content-center text-center p-5">
                <i class="bi bi-inbox text-muted display-4 mb-3"></i>
                <h5 class="fw-bold">Tidak ada data pemilih untuk dicetak</h5>
                <p class="text-muted small">Silakan pilih kelas lain atau tambahkan data pemilih terlebih dahulu.</p>
                <a href="/admin/voters" class="btn btn-paper-primary btn-sm">Kembali ke Data Pemilih</a>
            </div>
        <?php else: ?>
            <?php 
            // Pecah data pemilih menjadi grup 12 kartu per lembar A4
            $cardsPerPage = 12;
            $chunks = array_chunk($voters, $cardsPerPage);
            $totalSheets = count($chunks);

            foreach ($chunks as $sheetIndex => $pageVoters): 
            ?>
                <div class="sheet-a4">
                    <div class="card-grid">
                        <?php foreach ($pageVoters as $voter): ?>
                            <?php 
                            $nisnVal = !empty($voter['nisn']) ? $voter['nisn'] : (string)$voter['id'];
                            ?>
                            <div class="voter-card-square">
                                <!-- Card Header -->
                                <div class="card-header-mini">
                                    <img src="/assets/images/Logo_OSIS.svg" alt="OSIS">
                                    <div class="card-header-titles">
                                        <div class="card-header-title">KARTU PEMILIH DPT</div>
                                        <div class="card-header-sub">E-VOTING OSIS</div>
                                    </div>
                                </div>

                                <!-- QR Code Canvas Container -->
                                <div class="card-qr-wrapper">
                                    <div class="qr-target" data-nisn="<?= Security::escape($nisnVal) ?>"></div>
                                </div>

                                <!-- Identity Details Below QR Code -->
                                <div class="card-identity">
                                    <div class="voter-nisn-badge">
                                        NISN: <?= Security::escape($nisnVal) ?>
                                    </div>
                                    <div class="voter-name" title="<?= Security::escape($voter['nama']) ?>">
                                        <?= Security::escape($voter['nama']) ?>
                                    </div>
                                    <div class="voter-meta" title="<?= Security::escape($voter['kelas'] . ' - ' . $voter['jurusan']) ?>">
                                        <?= Security::escape($voter['kelas']) ?> &bull; <?= Security::escape($voter['jurusan']) ?>
                                    </div>
                                    <div class="card-footer-tip">
                                        Pindai QR saat pencoblosan suara
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 3. CLIENT SCRIPT FOR RENDERING QR CODES -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qrElements = document.querySelectorAll('.qr-target');
            qrElements.forEach(el => {
                const nisn = el.getAttribute('data-nisn');
                if (nisn) {
                    try {
                        new QRCode(el, {
                            text: nisn,
                            width: 88,
                            height: 88,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    } catch (e) {
                        console.error('Gagal generate QR untuk NISN:', nisn, e);
                    }
                }
            });
        });
    </script>
</body>
</html>
