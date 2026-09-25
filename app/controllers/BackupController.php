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

        $this->render('admin/backup', [
            'pageTitle' => 'Backup & Restore Database - E-Voting OSIS',
            'totalVoters' => $totalVoters,
            'totalCandidates' => $totalCandidates,
            'totalVotes' => $totalVotes
        ]);
    }

    /**
     * Download dump SQL lengkap database
     */
    public function export(): void
    {
        $this->requireAdmin();

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
            $createTable = $stmt->fetch();
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

        $fileName = 'backup_voting_oss_' . date('Y-m-d_His') . '.sql';

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($sqlDump));

        echo $sqlDump;
        exit;
    }

    /**
     * Restore database dari file .sql yang diunggah
     */
    public function restore(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Silakan pilih berkas backup SQL (.sql) yang valid.');
            $this->redirect('/admin/backup');
        }

        $file = $_FILES['backup_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($ext !== 'sql') {
            Session::setFlash('error', 'Hanya berkas berekstensi .sql yang dapat di-restore.');
            $this->redirect('/admin/backup');
        }

        $sqlContent = file_get_contents($file['tmp_name']);
        if (empty($sqlContent)) {
            Session::setFlash('error', 'Berkas SQL kosong atau tidak dapat dibaca.');
            $this->redirect('/admin/backup');
        }

        try {
            $pdo = Database::getConnection();
            $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");
            $pdo->exec($sqlContent);
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            Session::setFlash('success', 'Database berhasil di-restore secara sempurna dari berkas cadangan!');
        } catch (Exception $e) {
            Session::setFlash('error', 'Gagal me-restore database: ' . $e->getMessage());
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
}
