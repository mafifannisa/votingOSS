<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use App\Models\Candidate;

class CandidateController extends Controller
{
    private Candidate $candidateModel;

    public function __construct()
    {
        $this->candidateModel = new Candidate();
    }

    public function index(): void
    {
        $this->requireAdmin();

        $candidates = $this->candidateModel->getAll();

        $this->render('candidates/index', [
            'pageTitle' => 'Manajemen Pasangan Calon - E-Voting OSIS',
            'candidates' => $candidates
        ]);
    }

    public function create(): void
    {
        $this->requireAdmin();

        $this->render('candidates/create', [
            'pageTitle' => 'Tambah Pasangan Calon - E-Voting OSIS'
        ]);
    }

    public function store(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $nomorUrut = (int) ($_POST['nomor_urut'] ?? 0);
        $namaKetua = trim($_POST['nama_ketua'] ?? '');
        $namaWakil = trim($_POST['nama_wakil'] ?? '');
        $jurusanKetua = trim($_POST['jurusan_ketua'] ?? '');
        $jurusanWakil = trim($_POST['jurusan_wakil'] ?? '');
        $visi = trim($_POST['visi'] ?? '');
        $misi = trim($_POST['misi'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        // Validasi
        if ($nomorUrut <= 0 || empty($namaKetua) || empty($namaWakil) || empty($visi) || empty($misi)) {
            Session::setFlash('error', 'Semua kolom bertanda bintang wajib diisi.');
            $this->redirect('/admin/candidates/create');
        }

        // Cek duplikasi nomor urut
        if ($this->candidateModel->findByNomorUrut($nomorUrut)) {
            Session::setFlash('error', "Nomor urut {$nomorUrut} sudah digunakan oleh pasangan calon lain.");
            $this->redirect('/admin/candidates/create');
        }

        // Upload foto paslon jika ada
        $fotoPath = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$valid, $error, $ext] = Security::validateImageUpload($_FILES['foto']);
            if (!$valid) {
                Session::setFlash('error', $error);
                $this->redirect('/admin/candidates/create');
            }

            $fileName = 'paslon_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destination = __DIR__ . '/../../public/assets/images/candidates/' . $fileName;

            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                Session::setFlash('error', 'Gagal menyimpan file foto paslon.');
                $this->redirect('/admin/candidates/create');
            }

            $fotoPath = '/assets/images/candidates/' . $fileName;
        }

        $this->candidateModel->create([
            'nomor_urut' => $nomorUrut,
            'nama_ketua' => $namaKetua,
            'nama_wakil' => $namaWakil,
            'jurusan_ketua' => $jurusanKetua,
            'jurusan_wakil' => $jurusanWakil,
            'foto' => $fotoPath,
            'visi' => $visi,
            'misi' => $misi,
            'status' => $status
        ]);

        Session::setFlash('success', 'Pasangan calon berhasil ditambahkan.');
        $this->redirect('/admin/candidates');
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();

        $candidate = $this->candidateModel->findById((int) $id);
        if (!$candidate) {
            Session::setFlash('error', 'Data paslon tidak ditemukan.');
            $this->redirect('/admin/candidates');
        }

        $this->render('candidates/edit', [
            'pageTitle' => 'Edit Pasangan Calon - E-Voting OSIS',
            'candidate' => $candidate
        ]);
    }

    public function update(string $id): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $candidateId = (int) $id;
        $candidate = $this->candidateModel->findById($candidateId);
        if (!$candidate) {
            Session::setFlash('error', 'Data paslon tidak ditemukan.');
            $this->redirect('/admin/candidates');
        }

        $nomorUrut = (int) ($_POST['nomor_urut'] ?? 0);
        $namaKetua = trim($_POST['nama_ketua'] ?? '');
        $namaWakil = trim($_POST['nama_wakil'] ?? '');
        $jurusanKetua = trim($_POST['jurusan_ketua'] ?? '');
        $jurusanWakil = trim($_POST['jurusan_wakil'] ?? '');
        $visi = trim($_POST['visi'] ?? '');
        $misi = trim($_POST['misi'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        if ($nomorUrut <= 0 || empty($namaKetua) || empty($namaWakil) || empty($visi) || empty($misi)) {
            Session::setFlash('error', 'Semua kolom bertanda bintang wajib diisi.');
            $this->redirect('/admin/candidates/edit/' . $candidateId);
        }

        // Cek duplikasi nomor urut (kecuali id ini sendiri)
        if ($this->candidateModel->findByNomorUrut($nomorUrut, $candidateId)) {
            Session::setFlash('error', "Nomor urut {$nomorUrut} sudah digunakan oleh paslon lain.");
            $this->redirect('/admin/candidates/edit/' . $candidateId);
        }

        $updateData = [
            'nomor_urut' => $nomorUrut,
            'nama_ketua' => $namaKetua,
            'nama_wakil' => $namaWakil,
            'jurusan_ketua' => $jurusanKetua,
            'jurusan_wakil' => $jurusanWakil,
            'visi' => $visi,
            'misi' => $misi,
            'status' => $status
        ];

        // Jika ada foto baru yang diunggah
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$valid, $error, $ext] = Security::validateImageUpload($_FILES['foto']);
            if (!$valid) {
                Session::setFlash('error', $error);
                $this->redirect('/admin/candidates/edit/' . $candidateId);
            }

            $fileName = 'paslon_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destination = __DIR__ . '/../../public/assets/images/candidates/' . $fileName;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $destination)) {
                // Hapus foto lama jika ada
                if (!empty($candidate['foto'])) {
                    $oldFile = __DIR__ . '/../../public' . $candidate['foto'];
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $updateData['foto'] = '/assets/images/candidates/' . $fileName;
            }
        }

        $this->candidateModel->update($candidateId, $updateData);

        Session::setFlash('success', 'Data pasangan calon berhasil diperbarui.');
        $this->redirect('/admin/candidates');
    }

    public function delete(string $id): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $candidateId = (int) $id;
        $candidate = $this->candidateModel->findById($candidateId);

        if ($candidate) {
            if (!empty($candidate['foto'])) {
                $file = __DIR__ . '/../../public' . $candidate['foto'];
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            $this->candidateModel->delete($candidateId);
            Session::setFlash('success', 'Pasangan calon berhasil dihapus.');
        } else {
            Session::setFlash('error', 'Data paslon tidak ditemukan.');
        }

        $this->redirect('/admin/candidates');
    }
}
