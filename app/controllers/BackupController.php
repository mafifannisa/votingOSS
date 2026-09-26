<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;
use App\Core\Session;
use App\Models\Vote;
use App\Models\Voter;
use App\Models\Candidate;
use PDO;
use Exception;
use ZipArchive;

class BackupController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $voterModel = new Voter();
        $candidateModel = new Candidate();
        $voteModel = new Vote();

        $totalVoters = $voterModel->countAll();
        $totalCandidates = count($candidateModel->getAll());
        $totalVotes = $voteModel->getTotalVotes();

        $basePublic = dirname(__DIR__, 2) . '/public';
        $candidateImgCount = $this->countDirFiles($basePublic . '/assets/images/candidates');
        $logoCount = $this->countDirFiles($basePublic . '/assets/images/logos');

        $this->render('admin/backup', [
            'pageTitle' => 'Backup & Restore Database - E-Voting OSIS',
            'totalVoters' => $totalVoters,
            'totalCandidates' => $totalCandidates,
            'totalVotes' => $totalVotes,
            'candidateImgCount' => $candidateImgCount,
            'logoCount' => $logoCount,
            'zipAvailable' => class_exists('ZipArchive')
        ]);
    }

    /**
     * Download dump cadangan (default ZIP lengkap: SQL + Foto Paslon + Logo, atau SQL saja)
     */
    public function export(): void
    {
        $this->requireAdmin();
        @set_time_limit(300);

        $type = strtolower(trim((string)($_GET['type'] ?? $_GET['format'] ?? 'zip')));
        $sqlDump = $this->generateSqlDump();

        // Pilihan 1: Unduh hanya file .SQL murni
        if ($type === 'sql') {
            $fileName = 'backup_voting_oss_' . date('Y-m-d_His') . '.sql';

            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . (string)strlen($sqlDump));

            echo $sqlDump;
            exit;
        }

        // Pilihan 2 (Default): Unduh paket .ZIP Lengkap (SQL + Foto Paslon + Logo)
        if (!class_exists('ZipArchive')) {
            // Fallback otomatis ke .sql jika ZipArchive tidak tersedia
            $fileName = 'backup_voting_oss_' . date('Y-m-d_His') . '.sql';
            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . (string)strlen($sqlDump));
            echo $sqlDump;
            exit;
        }

        $tempBase = tempnam(sys_get_temp_dir(), 'evote_bkp_');
        $zipFile = $tempBase . '.zip';
        @unlink($tempBase);

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Session::setFlash('error', 'Gagal membuat arsip cadangan ZIP.');
            $this->redirect('/admin/backup');
        }

        // 1. Masukkan database.sql
        $zip->addFromString('database.sql', $sqlDump);

        // 2. Masukkan seluruh foto paslon
        $basePublic = dirname(__DIR__, 2) . '/public';
        $candidateDir = $basePublic . '/assets/images/candidates';
        $candidateCount = 0;
        if (is_dir($candidateDir)) {
            $files = scandir($candidateDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                    continue;
                }
                $filePath = $candidateDir . '/' . $file;
                if (is_file($filePath)) {
                    $zip->addFile($filePath, 'images/candidates/' . $file);
                    $candidateCount++;
                }
            }
        }

        // 3. Masukkan seluruh aset logo kustom & default
        $logoDir = $basePublic . '/assets/images/logos';
        $logoCount = 0;
        if (is_dir($logoDir)) {
            $files = scandir($logoDir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                    continue;
                }
                $filePath = $logoDir . '/' . $file;
                if (is_file($filePath)) {
                    $zip->addFile($filePath, 'images/logos/' . $file);
                    $logoCount++;
                }
            }
        }

        $defaultLogoPath = $basePublic . '/assets/images/Logo_OSIS.svg';
        if (file_exists($defaultLogoPath)) {
            $zip->addFile($defaultLogoPath, 'images/logos/Logo_OSIS.svg');
        }

        // 4. Masukkan manifest.json
        $voterModel = new Voter();
        $candidateModel = new Candidate();
        $voteModel = new Vote();

        $manifest = [
            'app' => 'E-Voting OSIS',
            'version' => '2.0',
            'created_at' => date('Y-m-d H:i:s'),
            'summary' => [
                'total_voters' => $voterModel->countAll(),
                'total_candidates' => count($candidateModel->getAll()),
                'total_votes' => $voteModel->getTotalVotes(),
                'candidate_images' => $candidateCount,
                'logo_images' => $logoCount,
            ]
        ];
        $zip->addFromString('manifest.json', (string)json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $zip->close();

        $downloadFileName = 'backup_voting_oss_lengkap_' . date('Y-m-d_His') . '.zip';

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
        header('Content-Length: ' . (string)filesize($zipFile));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($zipFile);
        @unlink($zipFile);
        exit;
    }

    /**
     * Restore database dan foto paslon dari berkas .zip atau .sql yang diunggah
     */
    public function restore(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();
        @set_time_limit(300);

        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Silakan pilih berkas cadangan (.zip atau .sql) yang valid.');
            $this->redirect('/admin/backup');
        }

        $file = $_FILES['backup_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['zip', 'sql'], true)) {
            Session::setFlash('error', 'Hanya berkas cadangan berekstensi .zip (Lengkap) atau .sql (Database) yang dapat dipulihkan.');
            $this->redirect('/admin/backup');
        }

        $pdo = Database::getConnection();

        // 1. Restore dari berkas .SQL murni
        if ($ext === 'sql') {
            $sqlContent = file_get_contents($file['tmp_name']);
            if (empty($sqlContent)) {
                Session::setFlash('error', 'Berkas SQL kosong atau tidak dapat dibaca.');
                $this->redirect('/admin/backup');
            }

            try {
                $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', trim($sqlContent));
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
                $pdo->exec($sqlContent);
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                Database::selfHeal($pdo, true);

                Session::setFlash('success', 'Database berhasil di-restore secara sempurna dari berkas cadangan SQL!');
            } catch (Exception $e) {
                Session::setFlash('error', 'Gagal me-restore database: ' . $e->getMessage());
            }

            $this->redirect('/admin/backup');
        }

        // 2. Restore dari paket .ZIP Lengkap (Database + Foto Paslon + Logo)
        if (!class_exists('ZipArchive')) {
            Session::setFlash('error', 'Ekstensi PHP ZipArchive tidak aktif pada server ini.');
            $this->redirect('/admin/backup');
        }

        $zip = new ZipArchive();
        if ($zip->open($file['tmp_name']) !== true) {
            Session::setFlash('error', 'Gagal membuka berkas arsip ZIP. Berkas kemungkinan rusak.');
            $this->redirect('/admin/backup');
        }

        // Cari file SQL di dalam berkas ZIP
        $sqlContent = null;
        $sqlEntryName = null;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if ($entry === false) continue;
            $cleanName = str_replace('\\', '/', $entry);

            if (strtolower(basename($cleanName)) === 'database.sql') {
                $sqlContent = $zip->getFromIndex($i);
                $sqlEntryName = $cleanName;
                break;
            }
        }

        if ($sqlContent === null) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if ($entry === false) continue;
                $cleanName = str_replace('\\', '/', $entry);

                if (strtolower(pathinfo($cleanName, PATHINFO_EXTENSION)) === 'sql') {
                    $sqlContent = $zip->getFromIndex($i);
                    $sqlEntryName = $cleanName;
                    break;
                }
            }
        }

        if (empty($sqlContent)) {
            $zip->close();
            Session::setFlash('error', 'Tidak ditemukan berkas database SQL (.sql) di dalam arsip ZIP.');
            $this->redirect('/admin/backup');
        }

        try {
            // A. Eksekusi skrip SQL
            $sqlContent = preg_replace('/^\xEF\xBB\xBF/', '', trim($sqlContent));
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($sqlContent);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            Database::selfHeal($pdo, true);

            // B. Ekstrak aset foto paslon dan logo
            $basePublic = dirname(__DIR__, 2) . '/public';
            $candidateDir = $basePublic . '/assets/images/candidates';
            $logoDir = $basePublic . '/assets/images/logos';

            if (!is_dir($candidateDir)) {
                @mkdir($candidateDir, 0755, true);
            }
            if (!is_dir($logoDir)) {
                @mkdir($logoDir, 0755, true);
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $restoredCandidates = 0;
            $restoredLogos = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entry = $zip->getNameIndex($i);
                if ($entry === false) continue;

                $entryName = str_replace('\\', '/', $entry);

                // Keamanan: Tolak directory traversal & lewati direktori folder
                if (str_contains($entryName, '..') || substr($entryName, -1) === '/') {
                    continue;
                }

                $fileExt = strtolower(pathinfo($entryName, PATHINFO_EXTENSION));
                if (!in_array($fileExt, $allowedExtensions, true)) {
                    continue;
                }

                $baseFilename = basename($entryName);

                // Cek apakah berkas foto paslon
                if (preg_match('#(?:^|/)candidates/[^/]+$#i', $entryName)) {
                    $imgData = $zip->getFromIndex($i);
                    if ($imgData !== false) {
                        file_put_contents($candidateDir . '/' . $baseFilename, $imgData);
                        $restoredCandidates++;
                    }
                }
                // Cek apakah berkas logo
                elseif (preg_match('#(?:^|/)logos/[^/]+$#i', $entryName)) {
                    $imgData = $zip->getFromIndex($i);
                    if ($imgData !== false) {
                        file_put_contents($logoDir . '/' . $baseFilename, $imgData);
                        $restoredLogos++;
                    }
                }
            }

            $zip->close();

            $msg = 'Database dan seluruh berkas aset ';
            if ($restoredCandidates > 0 || $restoredLogos > 0) {
                $msg .= "({$restoredCandidates} foto paslon, {$restoredLogos} logo) ";
            }
            $msg .= 'berhasil dipulihkan secara utuh dari paket cadangan ZIP!';

            Session::setFlash('success', $msg);
        } catch (Exception $e) {
            $zip->close();
            Session::setFlash('error', 'Gagal memulihkan cadangan: ' . $e->getMessage());
        }

        $this->redirect('/admin/backup');
    }

    /**
     * Reset perolehan suara (menjadikan kotak suara 0 dan status voter belum memilih)
     */
    public function resetVotes(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $voteModel = new Vote();
        if ($voteModel->resetVotes()) {
            Session::setFlash('success', 'Seluruh data suara berhasil di-reset. Kotak suara telah bersih untuk pemilu baru.');
        } else {
            Session::setFlash('error', 'Gagal mereset data suara.');
        }

        $this->redirect('/admin/backup');
    }

    /**
     * Menghasilkan dump SQL dari seluruh tabel pemilihan
     */
    private function generateSqlDump(): string
    {
        $pdo = Database::getConnection();
        $tables = ['admins', 'election_config', 'candidates', 'voters', 'votes'];

        $sqlDump = "-- ============================================================\n";
        $sqlDump .= "-- Backup Database E-Voting OSIS\n";
        $sqlDump .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
        $sqlDump .= "-- ============================================================\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Dapatkan struktur tabel
            $stmt = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($createTable && isset($createTable['Create Table'])) {
                $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlDump .= $createTable['Create Table'] . ";\n\n";
            }

            // Dapatkan data tabel
            $stmtData = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $stmtData->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $columns = array_keys($rows[0]);
                $colNames = implode('`, `', $columns);

                foreach ($rows as $row) {
                    $values = array_map(function ($val) use ($pdo) {
                        if ($val === null) return "NULL";
                        return $pdo->quote((string)$val);
                    }, array_values($row));

                    $valStr = implode(', ', $values);
                    $sqlDump .= "INSERT INTO `{$table}` (`{$colNames}`) VALUES ({$valStr});\n";
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sqlDump;
    }

    /**
     * Menghitung berkas dalam suatu direktori (mengabaikan .gitkeep dan folder)
     */
    private function countDirFiles(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }
        $files = scandir($dir);
        $count = 0;
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitkeep') {
                continue;
            }
            if (is_file($dir . '/' . $file)) {
                $count++;
            }
        }
        return $count;
    }
}
