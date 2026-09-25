<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Vote;
use App\Models\Voter;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAdmin();

        $voterModel = new Voter();
        $candidateModel = new Candidate();
        $voteModel = new Vote();
        $electionModel = new Election();

        $totalVoters = $voterModel->countAll();
        $totalVoted = $voterModel->countVoted();
        $totalNotVoted = $voterModel->countNotVoted();
        $totalVotesInBox = $voteModel->getTotalVotes();

        $participationRate = $totalVoters > 0 
            ? round(($totalVoted / $totalVoters) * 100, 1) 
            : 0.0;

        $candidates = $candidateModel->getAll();
        $electionConfig = $electionModel->getConfig();

        $this->render('admin/dashboard', [
            'pageTitle' => 'Dashboard Statistik - E-Voting OSIS',
            'totalVoters' => $totalVoters,
            'totalVoted' => $totalVoted,
            'totalNotVoted' => $totalNotVoted,
            'totalVotesInBox' => $totalVotesInBox,
            'participationRate' => $participationRate,
            'candidates' => $candidates,
            'electionConfig' => $electionConfig
        ]);
    }
}
