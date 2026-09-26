<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use App\Models\Admin;
use App\Models\Voter;

class AuthController extends Controller
{
    /**
     * Tampilan login pemilih (NISN)
     */
    public function showVoterLogin(): void
    {
        // Jika pemilih sudah login dan belum voting, langsung ke halaman vote
        if (Session::has('voter_id')) {
            $this->redirect('/vote');
        }

        $this->render('auth/voter_login', [
            'pageTitle' => 'Masuk Pemilih - E-Voting OSIS'
        ]);
    }

    /**
     * Proses autentikasi pemilih dengan NISN
     */
    public function loginVoter(): void
    {
        $this->validateCsrf();

        $nisn = trim($_POST['nisn'] ?? '');
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));

        $sendError = function (string $message) use ($isAjax) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                exit;
            }
            Session::setFlash('error', $message);
            $this->redirect('/');
        };

        // Validasi format NISN (hanya digit, minimal 8 - 15 karakter)
        if (empty($nisn)) {
            $sendError('Silakan masukkan NISN Anda.');
        }

        if (!preg_match('/^[0-9]{8,15}$/', $nisn)) {
            $sendError('Format NISN tidak valid. Masukkan angka NISN yang benar.');
        }

        $nisnHash = Security::hashNisn($nisn);
        $voterModel = new Voter();
        $voter = $voterModel->findByNisnHash($nisnHash);

        if (!$voter) {
            $sendError('NISN tidak terdaftar dalam DPT (Daftar Pemilih Tetap).');
        }

        if ((int) $voter['has_voted'] === 1) {
            $sendError('NISN ini sudah digunakan untuk memilih. Pemilihan hanya dapat dilakukan satu kali.');
        }

        // Regenerasi sesi untuk mencegah session fixation
        Session::regenerate();
        Session::set('voter_id', (int) $voter['id']);
        Session::set('voter_nama', $voter['nama']);
        Session::set('voter_kelas', $voter['kelas']);
        Session::set('voter_jurusan', $voter['jurusan']);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'redirect' => '/vote',
                'voter' => [
                    'nama' => $voter['nama'],
                    'kelas' => $voter['kelas'],
                    'jurusan' => $voter['jurusan']
                ]
            ]);
            exit;
        }

        $this->redirect('/vote');
    }

    /**
     * Tampilan login admin
     */
    public function showAdminLogin(): void
    {
        if (Session::has('admin_id')) {
            $this->redirect('/admin/dashboard');
        }

        $this->render('auth/admin_login', [
            'pageTitle' => 'Login Panitia - E-Voting OSIS'
        ]);
    }

    /**
     * Proses autentikasi admin
     */
    public function loginAdmin(): void
    {
        $this->validateCsrf();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            Session::setFlash('error', 'Username dan password wajib diisi.');
            $this->redirect('/admin/login');
        }

        $adminModel = new Admin();
        $admin = $adminModel->findByUsername($username);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            Session::setFlash('error', 'Username atau password panitia salah.');
            $this->redirect('/admin/login');
        }

        Session::regenerate();
        Session::set('admin_id', (int) $admin['id']);
        Session::set('admin_username', $admin['username']);

        Session::setFlash('success', 'Selamat datang, ' . Security::escape($admin['username']) . '!');
        $this->redirect('/admin/dashboard');
    }

    /**
     * Logout admin
     */
    public function logoutAdmin(): void
    {
        $this->validateCsrf();
        Session::remove('admin_id');
        Session::remove('admin_username');
        Session::regenerate();
        Session::setFlash('info', 'Anda telah keluar dari panel panitia.');
        $this->redirect('/admin/login');
    }
}
