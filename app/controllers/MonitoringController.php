<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Voter;
use App\Models\Vote;
use App\Models\Election;

class MonitoringController extends Controller
{
    private Voter $voterModel;
    private Vote $voteModel;
    private Election $electionModel;

    public function __construct()
    {
        $this->voterModel = new Voter();
        $this->voteModel = new Vote();
        $this->electionModel = new Election();
    }

    /**
     * Tampilan utama halaman khusus pemantauan suara masuk (Live Turnout)
     */
    public function index(): void
    {
        $this->requireAdmin();

        $totalVoters = $this->voterModel->countAll();
        $totalVoted = $this->voterModel->countVoted();
        $totalNotVoted = $this->voterModel->countNotVoted();
        $totalVotes = $this->voteModel->getTotalVotes();

        $participationRate = $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 1) : 0.0;
        $classStats = $this->voterModel->getStatsByClass();
        $isUnlocked = Session::get('results_unlocked', false);
        $electionConfig = $this->electionModel->getConfig();

        $this->render('admin/monitoring', [
            'pageTitle' => 'Pemantauan Suara Masuk (Live Turnout) - E-Voting OSIS',
            'totalVoters' => $totalVoters,
            'totalVoted' => $totalVoted,
            'totalNotVoted' => $totalNotVoted,
            'totalVotes' => $totalVotes,
            'participationRate' => $participationRate,
            'classStats' => $classStats,
            'isUnlocked' => $isUnlocked,
            'electionConfig' => $electionConfig
        ]);
    }

    /**
     * Endpoint API JSON untuk pembaruan real-time (Polling Live)
     * CATATAN KEAMANAN: Endpoint ini HANYA mengembalikan statistik partisipasi dan suara masuk,
     * SAMA SEKALI TIDAK MEMBUKA perolehan suara masing-masing paslon untuk menjaga asas rahasia.
     */
    public function getData(): void
    {
        $this->requireAdmin();

        $totalVoters = $this->voterModel->countAll();
        $totalVoted = $this->voterModel->countVoted();
        $totalNotVoted = $this->voterModel->countNotVoted();
        $totalVotes = $this->voteModel->getTotalVotes();
        $participationRate = $totalVoters > 0 ? round(($totalVoted / $totalVoters) * 100, 1) : 0.0;
        $classStats = $this->voterModel->getStatsByClass();

        $this->json([
            'success' => true,
            'total_voters' => $totalVoters,
            'total_voted' => $totalVoted,
            'total_not_voted' => $totalNotVoted,
            'total_votes' => $totalVotes,
            'participation_rate' => $participationRate,
            'class_stats' => $classStats,
            'updated_at' => date('H:i:s') . ' WIB'
        ]);
    }
}
