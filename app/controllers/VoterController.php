<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ExcelParser;
use App\Core\Security;
use App\Core\Session;
use App\Core\Database;
use App\Models\Voter;

class VoterController extends Controller
{
    private Voter $voterModel;

    public function __construct()
    {
        $this->voterModel = new Voter();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $search = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $totalRecords = $this->voterModel->countAll($search);
        $totalPages = (int) ceil($totalRecords / $limit);
        $voters = $this->voterModel->getAll($limit, $offset, $search);

        $totalVoted = $this->voterModel->countVoted();
        $totalNotVoted = $this->voterModel->countNotVoted();

        $this->render('admin/voters_index', [
            'pageTitle' => 'Data Pemilih (DPT) - E-Voting OSIS',
            'voters' => $voters,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRecords' => $totalRecords,
            'totalVoted' => $totalVoted,
            'totalNotVoted' => $totalNotVoted
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->render('admin/voter_create', [
            'pageTitle' => 'Tambah Pemilih Manual - E-Voting OSIS'
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $nisn = trim($_POST['nisn'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');

        if (empty($nisn) || empty($nama) || empty($kelas) || empty($jurusan)) {
            Session::setFlash('error', 'Semua kolom wajib diisi.');
            $this->redirect('/admin/voters/create');
        }

        if (!preg_match('/^[0-9]{8,15}$/', $nisn)) {
            Session::setFlash('error', 'Format NISN harus berupa angka (8-15 digit).');
            $this->redirect('/admin/voters/create');
        }

        $nisnHash = Security::hashNisn($nisn);

        if ($this->voterModel->findByNisnHash($nisnHash)) {
            Session::setFlash('error', 'NISN ini sudah terdaftar dalam sistem.');
            $this->redirect('/admin/voters/create');
        }

        $this->voterModel->create([
            'nisn_hash' => $nisnHash,
            'nama' => $nama,
            'kelas' => $kelas,
            'jurusan' => $jurusan
        ]);

        Session::setFlash('success', 'Data pemilih berhasil ditambahkan.');
        $this->redirect('/admin/voters');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();

        $voter = $this->voterModel->findById((int) $id);
        if (!$voter) {
            Session::setFlash('error', 'Data pemilih tidak ditemukan.');
            $this->redirect('/admin/voters');
        }

        $this->render('admin/voter_edit', [
            'pageTitle' => 'Edit Data Pemilih - E-Voting OSIS',
            'voter' => $voter
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $voterId = (int) $id;
        $voter = $this->voterModel->findById($voterId);
        if (!$voter) {
            Session::setFlash('error', 'Data pemilih tidak ditemukan.');
            $this->redirect('/admin/voters');
        }

        $nama = trim($_POST['nama'] ?? '');
        $kelas = trim($_POST['kelas'] ?? '');
        $jurusan = trim($_POST['jurusan'] ?? '');

        if (empty($nama) || empty($kelas) || empty($jurusan)) {
            Session::setFlash('error', 'Nama, kelas, dan jurusan wajib diisi.');
            $this->redirect('/admin/voters/edit/' . $voterId);
        }

        $this->voterModel->update($voterId, [
            'nama' => $nama,
            'kelas' => $kelas,
            'jurusan' => $jurusan
        ]);

        Session::setFlash('success', 'Data pemilih berhasil diperbarui.');
        $this->redirect('/admin/voters');
    }

    public function delete(string $id): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $voterId = (int) $id;
        $this->voterModel->delete($voterId);

        Session::setFlash('success', 'Data pemilih berhasil dihapus.');
        $this->redirect('/admin/voters');
    }

    public function showImport(): void
    {
        $this->requireAdmin();

        $this->render('admin/voters_import', [
            'pageTitle' => 'Import Data Pemilih Excel - E-Voting OSIS'
        ]);
    }

    public function downloadTemplate(): void
    {
        $this->requireAdmin();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="template_pemilih_osis.csv"');

        $output = fopen('php://output', 'w');
        // BOM for UTF-8 Excel support
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['nisn', 'nama', 'kelas', 'jurusan']);
        fputcsv($output, ['0051234567', 'Ahmad Pratama', 'X RPL 1', 'Rekayasa Perangkat Lunak']);
        fputcsv($output, ['0051234568', 'Budi Santoso', 'XI TKJ 2', 'Teknik Komputer & Jaringan']);
        fputcsv($output, ['0051234569', 'Citra Lestari', 'XII DKV 1', 'Desain Komunikasi Visual']);
        fclose($output);
        exit;
    }

    public function processImport(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Session::setFlash('error', 'Silakan pilih file Excel (.xlsx) atau CSV yang valid.');
            $this->redirect('/admin/voters/import');
        }

        $tmpPath = $_FILES['file']['tmp_name'];
        $origName = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'csv'], true)) {
            Session::setFlash('error', 'Hanya file dengan ekstensi .xlsx dan .csv yang diperbolehkan.');
            $this->redirect('/admin/voters/import');
        }

        $parseResult = ExcelParser::parse($tmpPath, $ext);

        if (!$parseResult['success']) {
            Session::setFlash('error', $parseResult['error']);
            $this->redirect('/admin/voters/import');
        }

        $dataRows = $parseResult['data'];
        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        try {
            Database::beginTransaction();

            $pdo = Database::getConnection();
            $stmtCheck = $pdo->prepare('SELECT id FROM voters WHERE nisn_hash = ? LIMIT 1');
            $stmtInsert = $pdo->prepare('INSERT INTO voters (nisn_hash, nama, kelas, jurusan, has_voted, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            $stmtUpdate = $pdo->prepare('UPDATE voters SET nama = ?, kelas = ?, jurusan = ? WHERE id = ?');

            foreach ($dataRows as $item) {
                $nisn = $item['nisn'];
                $nisnHash = Security::hashNisn($nisn);

                $stmtCheck->execute([$nisnHash]);
                $existing = $stmtCheck->fetch();

                if ($existing) {
                    $stmtUpdate->execute([$item['nama'], $item['kelas'], $item['jurusan'], $existing['id']]);
                    $updated++;
                } else {
                    $stmtInsert->execute([$nisnHash, $item['nama'], $item['kelas'], $item['jurusan']]);
                    $inserted++;
                }
            }

            Database::commit();

            Session::setFlash('success', "Import berhasil! {$inserted} data baru ditambahkan, {$updated} data diperbarui.");
            $this->redirect('/admin/voters');
        } catch (\Exception $e) {
            Database::rollBack();
            error_log('Import error: ' . $e->getMessage());
            Session::setFlash('error', 'Terjadi kesalahan saat memproses data import ke database: ' . $e->getMessage());
            $this->redirect('/admin/voters/import');
        }
    }
}
