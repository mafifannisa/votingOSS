<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use App\Models\Election;

class SettingsController extends Controller
{
    private Election $electionModel;

    public function __construct()
    {
        $this->electionModel = new Election();
    }

    /**
     * Tampilkan halaman formulir Pengaturan Tampilan & Sistem
     */
    public function index(): void
    {
        $this->requireAdmin();

        $config = $this->electionModel->getConfig(true);

        $this->render('admin/settings', [
            'pageTitle' => 'Pengaturan Tampilan & Footer - E-Voting OSIS',
            'config' => $config
        ]);
    }

    /**
     * Simpan pembaruan pengaturan
     */
    public function update(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $currentConfig = $this->electionModel->getConfig(true);

        $appName = trim($_POST['app_name'] ?? '');
        $footerText = trim($_POST['footer_text'] ?? '');
        $electionName = trim($_POST['election_name'] ?? '');
        $resultCode = trim($_POST['result_code'] ?? '');
        $resetAppLogo = !empty($_POST['reset_app_logo']);
        $footerLogoMode = trim($_POST['footer_logo_mode'] ?? 'same');

        // Validasi teks dasar
        if (empty($appName)) {
            $appName = 'E-VOTING OSIS';
        }
        if (empty($footerText)) {
            $footerText = '© ' . date('Y') . ' Pemilihan Ketua OSIS • Sistem E-Voting Paper Card';
        }
        if (empty($electionName)) {
            $electionName = $currentConfig['election_name'] ?? 'Pemilihan Ketua & Wakil Ketua OSIS 2026/2027';
        }

        $logoDir = dirname(__DIR__, 2) . '/public/assets/images/logos';
        if (!is_dir($logoDir)) {
            @mkdir($logoDir, 0755, true);
        }

        // 1. Proses Logo Aplikasi (Navbar / Header)
        $appLogoPath = $currentConfig['app_logo'] ?? '/assets/images/Logo_OSIS.svg';
        if ($resetAppLogo) {
            $appLogoPath = '/assets/images/Logo_OSIS.svg';
        } elseif (isset($_FILES['app_logo']) && $_FILES['app_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
            [$valid, $error, $ext] = Security::validateImageUpload($_FILES['app_logo']);
            if (!$valid) {
                Session::setFlash('error', 'Logo Aplikasi: ' . $error);
                $this->redirect('/admin/settings');
            }

            $fileName = 'logo_app_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $destination = $logoDir . '/' . $fileName;

            if (!move_uploaded_file($_FILES['app_logo']['tmp_name'], $destination)) {
                Session::setFlash('error', 'Gagal mengunggah file logo aplikasi.');
                $this->redirect('/admin/settings');
            }

            $appLogoPath = '/assets/images/logos/' . $fileName;
        }

        // 2. Proses Logo Footer
        $footerLogoPath = $currentConfig['footer_logo'] ?? '/assets/images/Logo_OSIS.svg';
        if ($footerLogoMode === 'same') {
            $footerLogoPath = $appLogoPath;
        } elseif ($footerLogoMode === 'default') {
            $footerLogoPath = '/assets/images/Logo_OSIS.svg';
        } elseif ($footerLogoMode === 'hidden') {
            $footerLogoPath = '';
        } elseif ($footerLogoMode === 'custom') {
            if (isset($_FILES['footer_logo_file']) && $_FILES['footer_logo_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                [$valid, $error, $ext] = Security::validateImageUpload($_FILES['footer_logo_file']);
                if (!$valid) {
                    Session::setFlash('error', 'Logo Footer: ' . $error);
                    $this->redirect('/admin/settings');
                }

                $fileName = 'logo_footer_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $destination = $logoDir . '/' . $fileName;

                if (!move_uploaded_file($_FILES['footer_logo_file']['tmp_name'], $destination)) {
                    Session::setFlash('error', 'Gagal mengunggah file logo footer.');
                    $this->redirect('/admin/settings');
                }

                $footerLogoPath = '/assets/images/logos/' . $fileName;
            }
        }

        $dataToUpdate = [
            'app_name' => $appName,
            'app_logo' => $appLogoPath,
            'footer_text' => $footerText,
            'footer_logo' => $footerLogoPath,
            'election_name' => $electionName,
        ];

        // Jika panitia memasukkan kode akses baru untuk hasil rapat pleno
        if (!empty($resultCode)) {
            if (strlen($resultCode) < 4) {
                Session::setFlash('error', 'Kode akses rapat pleno minimal 4 karakter.');
                $this->redirect('/admin/settings');
            }
            $dataToUpdate['result_code_hash'] = password_hash($resultCode, PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $success = $this->electionModel->updateSettings($dataToUpdate);

        if ($success) {
            Session::setFlash('success', 'Pengaturan aplikasi, judul navbar, teks footer, dan logo berhasil disimpan.');
        } else {
            Session::setFlash('error', 'Gagal menyimpan perubahan pengaturan ke database.');
        }

        $this->redirect('/admin/settings');
    }

    /**
     * Kembalikan pengaturan ke setelan awal sistem (default)
     */
    public function resetDefaults(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $success = $this->electionModel->resetDefaults();

        if ($success) {
            Session::setFlash('success', 'Pengaturan berhasil dikembalikan ke standar awal.');
        } else {
            Session::setFlash('error', 'Gagal mereset pengaturan.');
        }

        $this->redirect('/admin/settings');
    }
}
