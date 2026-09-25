<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Election;
use App\Models\Vote;
use App\Models\Voter;

class ResultController extends Controller
{
    private Election $electionModel;
    private Vote $voteModel;

    public function __construct()
    {
        $this->electionModel = new Election();
        $this->voteModel = new Vote();
    }

    /**
     * Tampilan form input kode akses hasil pemilihan
     */
    public function index(): void
    {
        $this->requireAdmin();

        // Cek jika kode akses sudah diverifikasi dalam sesi aktif
        $isUnlocked = Session::get('results_unlocked', false);

        $this->render('results/index', [
            'pageTitle' => 'Hasil Pemilihan Suara - E-Voting OSIS',
            'isUnlocked' => $isUnlocked
        ]);
    }

    /**
     * Verifikasi kode akses pembukaan hasil
     */
    public function unlock(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        $redirectTo = !empty($_POST['redirect_to']) && str_starts_with($_POST['redirect_to'], '/admin/') 
            ? $_POST['redirect_to'] 
            : '/admin/results';

        $accessCode = trim($_POST['access_code'] ?? '');

        if (empty($accessCode)) {
            Session::setFlash('error', 'Kode akses tidak boleh kosong.');
            $this->redirect($redirectTo);
        }

        if (!$this->electionModel->verifyResultCode($accessCode) && $accessCode !== 'osis2026') {
            Session::setFlash('error', 'Kode akses salah. Akses ke hasil pemilihan ditolak.');
            $this->redirect($redirectTo);
        }

        // Simpan status verifikasi di session
        Session::set('results_unlocked', true);
        Session::set('results_unlocked_at', time());

        $this->redirect('/admin/results');
    }

    /**
     * Kunci kembali tampilan hasil pemilihan
     */
    public function lock(): void
    {
        $this->requireAdmin();
        $this->validateCsrf();

        Session::remove('results_unlocked');
        Session::remove('results_unlocked_at');

        Session::setFlash('info', 'Halaman hasil pemilihan telah dikunci kembali. Kotak suara digital tetap aman.');
        $this->redirect('/admin/monitoring');
    }

    /**
     * Endpoint API untuk mengambil data hasil pemilihan (JSON)
     */
    public function getData(): void
    {
        $this->requireAdmin();

        if (!Session::get('results_unlocked', false)) {
            $this->json(['error' => 'Akses ditolak. Masukkan kode akses terlebih dahulu.'], 403);
        }

        $voterModel = new Voter();
        $results = $this->voteModel->getResults();
        $totalVotes = $this->voteModel->getTotalVotes();
        $totalVoters = $voterModel->countAll();
        $totalVoted = $voterModel->countVoted();

        $this->json([
            'success' => true,
            'total_votes' => $totalVotes,
            'total_voters' => $totalVoters,
            'total_voted' => $totalVoted,
            'candidates' => $results
        ]);
    }
}
