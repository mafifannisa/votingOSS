<?php

declare(strict_types=1);

/** @var \App\Core\Router $router */

use App\Controllers\AuthController;
use App\Controllers\BackupController;
use App\Controllers\CandidateController;
use App\Controllers\DashboardController;
use App\Controllers\InstallController;
use App\Controllers\ResultController;
use App\Controllers\VoterController;
use App\Controllers\VotingController;

// --- AREA PEMILIH ---
$router->get('/', [AuthController::class, 'showVoterLogin']);
$router->get('/login', [AuthController::class, 'showVoterLogin']);
$router->post('/login/process', [AuthController::class, 'loginVoter']);

$router->get('/vote', [VotingController::class, 'showBallot']);
$router->post('/vote/submit', [VotingController::class, 'submitVote']);

// --- SETUP & INSTALLER (SERVER BARU) ---
$router->get('/install', [InstallController::class, 'index']);
$router->post('/install/process', [InstallController::class, 'process']);

// --- AREA ADMIN (PANITIA) ---
$router->get('/admin', function () {
    header('Location: /admin/dashboard');
    exit;
});
$router->get('/admin/login', [AuthController::class, 'showAdminLogin']);
$router->post('/admin/login/process', [AuthController::class, 'loginAdmin']);
$router->post('/admin/logout', [AuthController::class, 'logoutAdmin']);

$router->get('/admin/dashboard', [DashboardController::class, 'index']);

// Data Pasangan Calon
$router->get('/admin/candidates', [CandidateController::class, 'index']);
$router->get('/admin/candidates/create', [CandidateController::class, 'create']);
$router->post('/admin/candidates/store', [CandidateController::class, 'store']);
$router->get('/admin/candidates/edit/{id}', [CandidateController::class, 'edit']);
$router->post('/admin/candidates/update/{id}', [CandidateController::class, 'update']);
$router->post('/admin/candidates/delete/{id}', [CandidateController::class, 'delete']);

// Data Pemilih (DPT)
$router->get('/admin/voters', [VoterController::class, 'index']);
$router->get('/admin/voters/create', [VoterController::class, 'create']);
$router->post('/admin/voters/store', [VoterController::class, 'store']);
$router->get('/admin/voters/edit/{id}', [VoterController::class, 'edit']);
$router->post('/admin/voters/update/{id}', [VoterController::class, 'update']);
$router->post('/admin/voters/delete/{id}', [VoterController::class, 'delete']);
$router->post('/admin/voters/reset/{id}', [VoterController::class, 'resetStatus']);
$router->post('/admin/voters/reset-all', [VoterController::class, 'resetAllStatus']);
$router->get('/admin/voters/print', [VoterController::class, 'printCards']);

// Import Data Pemilih Excel / CSV
$router->get('/admin/voters/import', [VoterController::class, 'showImport']);
$router->post('/admin/voters/import/process', [VoterController::class, 'processImport']);
$router->get('/admin/voters/template', [VoterController::class, 'downloadTemplate']);

// Hasil Pemilihan & Countdown
$router->get('/admin/results', [ResultController::class, 'index']);
$router->post('/admin/results/unlock', [ResultController::class, 'unlock']);
$router->post('/admin/results/lock', [ResultController::class, 'lock']);
$router->get('/admin/results/data', [ResultController::class, 'getData']);

// Backup & Restore Database
$router->get('/admin/backup', [BackupController::class, 'index']);
$router->get('/admin/backup/export', [BackupController::class, 'export']);
$router->post('/admin/backup/restore', [BackupController::class, 'restore']);
$router->post('/admin/backup/reset-votes', [BackupController::class, 'resetVotes']);
