<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Candidate;
use App\Models\Vote;
use App\Models\Voter;

class VotingController extends Controller
{
    public function showBallot(): void
    {
        $this->requireVoter();

        $voterId = (int) Session::get('voter_id');
        $voterModel = new Voter();
        $voter = $voterModel->findById($voterId);

        // Jika data pemilih tidak ditemukan atau sudah berstatus voting
        if (!$voter || (int) $voter['has_voted'] === 1) {
            Session::destroy();
            Session::start();
            Session::setFlash('error', 'Anda telah memberikan hak suara atau sesi telah berakhir.');
            $this->redirect('/');
        }

        $candidateModel = new Candidate();
        $candidates = $candidateModel->getActive();

        $this->render('voter/ballot', [
            'pageTitle' => 'Surat Suara Digital - E-Voting OSIS',
            'voter' => $voter,
            'candidates' => $candidates
        ]);
    }

    public function submitVote(): void
    {
        $this->requireVoter();
        $this->validateCsrf();

        $voterId = (int) Session::get('voter_id');
        $candidateId = (int) ($_POST['candidate_id'] ?? 0);

        if ($candidateId <= 0) {
            Session::setFlash('error', 'Silakan tentukan pasangan calon pilihan Anda.');
            $this->redirect('/vote');
        }

        $candidateModel = new Candidate();
        $candidate = $candidateModel->findById($candidateId);

        if (!$candidate || (int) $candidate['status'] !== 1) {
            Session::setFlash('error', 'Pasangan calon yang dipilih tidak valid atau tidak aktif.');
            $this->redirect('/vote');
        }

        $voteModel = new Vote();
        $success = $voteModel->recordVote($voterId, $candidateId);

        if ($success) {
            // Hentikan sesi pemilih sesuai PRD AC-13 & SEC-05
            Session::destroy();
            
            // Buka sesi baru hanya untuk membawa flash message ke halaman login
            Session::start();
            Session::setFlash('success', 'Terima kasih! Suara Anda telah berhasil direkam secara aman.');
            $this->redirect('/');
        } else {
            Session::setFlash('error', 'Gagal memproses suara. Anda mungkin sudah tercatat memilih atau terjadi gangguan.');
            $this->redirect('/');
        }
    }
}
