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
            $this->redirect('/vote');
        };

        if ($candidateId <= 0) {
            $sendError('Silakan tentukan pasangan calon pilihan Anda.');
        }

        $candidateModel = new Candidate();
        $candidate = $candidateModel->findById($candidateId);

        if (!$candidate || (int) $candidate['status'] !== 1) {
            $sendError('Pasangan calon yang dipilih tidak valid atau tidak aktif.');
        }

        $voteModel = new Vote();
        $success = $voteModel->recordVote($voterId, $candidateId);

        if ($success) {
            // Hentikan sesi pemilih sesuai PRD AC-13 & SEC-05
            Session::destroy();
            
            // Buka sesi baru hanya untuk membawa flash message ke halaman login
            Session::start();
            $msg = 'Terima kasih! Suara Anda telah berhasil direkam secara aman.';
            Session::setFlash('success', $msg);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => $msg,
                    'redirect' => '/'
                ]);
                exit;
            }

            $this->redirect('/');
        } else {
            $err = 'Gagal memproses suara. Anda mungkin sudah tercatat memilih atau terjadi gangguan.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $err
                ]);
                exit;
            }
            Session::setFlash('error', $err);
            $this->redirect('/');
        }
    }
}
