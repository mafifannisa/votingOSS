<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Security;
use App\Core\Session;
use PDO;
use PDOException;
use Exception;

class InstallController extends Controller
{
    private string $lockFile;

    public function __construct()
    {
        $this->lockFile = __DIR__ . '/../../storage/.installed';
    }

    /**
     * Tampilan formulir instalasi / setup database
     */
    public function index(): void
    {
        $isInstalled = file_exists($this->lockFile);

        // Ambil konfigurasi saat ini sebagai nilai default
        $currentConfig = require __DIR__ . '/../../config/database.php';

        $this->render('install/index', [
            'pageTitle' => 'Setup & Restore Database - E-Voting OSIS',
            'isInstalled' => $isInstalled,
            'config' => $currentConfig
        ], 'main');
    }

    /**
     * Proses instalasi: tes koneksi, buat database, import tabel, simpan konfigurasi
     */
    public function process(): void
    {
        // Validasi input
        $host = trim($_POST['host'] ?? '127.0.0.1');
        $port = (int) ($_POST['port'] ?? 3306);
        $dbname = trim($_POST['database'] ?? 'voting_oss');
        $username = trim($_POST['username'] ?? 'root');
        $password = (string) ($_POST['password'] ?? '');
        $seedDemo = isset($_POST['seed_demo']);

        if (empty($host) || empty($dbname) || empty($username)) {
            Session::setFlash('error', 'Host, Nama Database, dan Username MySQL wajib diisi.');
            $this->redirect('/install');
        }

        try {
            // 1. Hubungkan ke MySQL server (tanpa memilih database terlebih dahulu)
            $dsnWithoutDb = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, $port);
            $pdo = new PDO($dsnWithoutDb, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // 2. Buat database jika belum ada
            $pdo->exec(sprintf(
                'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
                str_replace('`', '``', $dbname)
            ));

            // 3. Hubungkan ke database yang baru dibuat / dipilih
            $dsnWithDb = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $dbname);
            $pdoDb = new PDO($dsnWithDb, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // 4. Eksekusi skrip schema.sql
            $schemaFile = __DIR__ . '/../../database/schema.sql';
            if (!file_exists($schemaFile)) {
                throw new Exception('Berkas database/schema.sql tidak ditemukan.');
            }

            $sqlContent = file_get_contents($schemaFile);
            $pdoDb->exec($sqlContent);

            // 5. Isi data demo jika dicentang
            if ($seedDemo) {
                $seedFile = __DIR__ . '/../../database/seed.php';
                if (file_exists($seedFile)) {
                    // Simpan konfigurasi sementara agar seeder dapat terkoneksi
                    $this->writeConfigFile($host, $port, $dbname, $username, $password);
                    
                    // Jalankan seeder
                    ob_start();
                    include $seedFile;
                    ob_end_clean();
                }
            }

            // 6. Simpan konfigurasi permanen ke config/database.php
            $this->writeConfigFile($host, $port, $dbname, $username, $password);

            // 7. Buat file penanda lock (.installed)
            @file_put_contents($this->lockFile, date('Y-m-d H:i:s'));

            Session::setFlash('success', 'Instalasi & Restore Database berhasil! Akun panitia: admin / admin123');
            $this->redirect('/admin/login');

        } catch (PDOException $e) {
            Session::setFlash('error', 'Gagal terhubung ke MySQL Server: ' . $e->getMessage());
            $this->redirect('/install');
        } catch (Exception $e) {
            Session::setFlash('error', 'Terjadi kesalahan instalasi: ' . $e->getMessage());
            $this->redirect('/install');
        }
    }

    /**
     * Tulis pembaruan konfigurasi ke config/database.php
     */
    private function writeConfigFile(string $host, int $port, string $dbname, string $username, string $password): void
    {
        $configFile = __DIR__ . '/../../config/database.php';
        $escapedHost = addslashes($host);
        $escapedDb = addslashes($dbname);
        $escapedUser = addslashes($username);
        $escapedPass = addslashes($password);

        $code = "<?php\n\ndeclare(strict_types=1);\n\nreturn [\n"
            . "    'driver' => 'mysql',\n"
            . "    'host' => getenv('DB_HOST') ?: '{$escapedHost}',\n"
            . "    'port' => (int) (getenv('DB_PORT') ?: {$port}),\n"
            . "    'database' => getenv('DB_DATABASE') ?: '{$escapedDb}',\n"
            . "    'username' => getenv('DB_USERNAME') ?: '{$escapedUser}',\n"
            . "    'password' => getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '{$escapedPass}',\n"
            . "    'charset' => 'utf8mb4',\n"
            . "    'options' => [\n"
            . "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n"
            . "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n"
            . "        PDO::ATTR_EMULATE_PREPARES => false,\n"
            . "    ],\n"
            . "];\n";

        file_put_contents($configFile, $code);
    }
}
