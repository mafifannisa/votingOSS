<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;
    private static bool $healed = false;
    private const CURRENT_SCHEMA_VERSION = '2026_09_v2';

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = sprintf(
                '%s:host=%s;port=%d;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());

                // Jika belum terinstal dan gagal koneksi, arahkan ke wizard instalasi otomatis
                $reqUri = $_SERVER['REQUEST_URI'] ?? '';
                if (!str_contains($reqUri, '/install') && !file_exists(__DIR__ . '/../../storage/.installed')) {
                    header('Location: /install');
                    exit;
                }

                $appConfig = require __DIR__ . '/../../config/app.php';
                $detail = ($appConfig['debug'] ?? true) ? ': ' . $e->getMessage() : '. Silakan periksa konfigurasi.';
                throw new RuntimeException('Koneksi database gagal' . $detail, (int) $e->getCode(), $e);
            }

            // Proactive Self-Healing: Otomatis sinkronkan kolom & tabel jika belum termutakhirkan
            self::selfHeal(self::$instance);
        }

        return self::$instance;
    }

    /**
     * Self-Healing Database:
     * Otomatis mendeteksi dan memperbaiki skema database jika ada tabel atau kolom baru yang belum dimigrasi di server.
     * Tidak membebani server karena menggunakan file cache versi schema.
     */
    public static function selfHeal(?PDO $pdo = null, bool $force = false): void
    {
        $pdo = $pdo ?? self::$instance;
        if ($pdo === null) {
            return;
        }

        if (self::$healed && !$force) {
            return;
        }

        $storageDir = __DIR__ . '/../../storage';
        $versionFile = $storageDir . '/.db_schema_version';

        if (!$force && file_exists($versionFile)) {
            $storedVersion = trim((string)@file_get_contents($versionFile));
            if ($storedVersion === self::CURRENT_SCHEMA_VERSION) {
                self::$healed = true;
                return;
            }
        }

        try {
            // 1. Periksa dan perbaiki tabel 'voters'
            $votersCheck = $pdo->query("SHOW TABLES LIKE 'voters'");
            if ($votersCheck && $votersCheck->fetchColumn() !== false) {
                $colsStmt = $pdo->query("SHOW COLUMNS FROM `voters`");
                $voterCols = $colsStmt ? $colsStmt->fetchAll(PDO::FETCH_COLUMN) : [];

                // Perbaiki kolom 'nisn' jika belum ada
                if (!in_array('nisn', $voterCols, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `voters` ADD COLUMN `nisn` VARCHAR(30) NULL AFTER `id`");
                    } catch (\Throwable $ignore) {}

                    try {
                        $pdo->exec("ALTER TABLE `voters` ADD INDEX `idx_nisn` (`nisn`)");
                    } catch (\Throwable $ignore) {}
                }

                // Perbaiki kolom 'updated_at' jika belum ada
                if (!in_array('updated_at', $voterCols, true)) {
                    try {
                        $pdo->exec("ALTER TABLE `voters` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                    } catch (\Throwable $ignore) {}
                }
            }

            // 2. Periksa dan perbaiki tabel 'election_config'
            $pdo->exec("CREATE TABLE IF NOT EXISTS `election_config` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `election_name` VARCHAR(255) NOT NULL,
                `result_code_hash` VARCHAR(255) NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            // Pastikan kolom result_code_hash ada
            $cfgColsStmt = $pdo->query("SHOW COLUMNS FROM `election_config`");
            $cfgCols = $cfgColsStmt ? $cfgColsStmt->fetchAll(PDO::FETCH_COLUMN) : [];
            if (!in_array('result_code_hash', $cfgCols, true)) {
                try {
                    $pdo->exec("ALTER TABLE `election_config` ADD COLUMN `result_code_hash` VARCHAR(255) NOT NULL DEFAULT '' AFTER `election_name`");
                } catch (\Throwable $ignore) {}
            }

            // Pastikan ada row default untuk election_config
            try {
                $cfgCount = $pdo->query("SELECT COUNT(*) FROM `election_config` WHERE `id` = 1");
                if ($cfgCount && (int)$cfgCount->fetchColumn() === 0) {
                    $defaultHash = '$2y$12$NRkWAwvyfXBAJSoIAM8fQOKittBkKVRJ02l5X7O2qUrkdNESwzLeO'; // default 'osis2026'
                    $stmt = $pdo->prepare("INSERT INTO `election_config` (`id`, `election_name`, `result_code_hash`, `status`) VALUES (1, 'Pemilihan Ketua & Wakil Ketua OSIS 2026/2027', ?, 1)");
                    $stmt->execute([$defaultHash]);
                }
            } catch (\Throwable $ignore) {}

            // 3. Pastikan tabel inti lainnya ada (candidates, votes, admins)
            $pdo->exec("CREATE TABLE IF NOT EXISTS `candidates` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `nomor_urut` INT NOT NULL UNIQUE,
                `nama_ketua` VARCHAR(150) NOT NULL,
                `nama_wakil` VARCHAR(150) NOT NULL,
                `jurusan_ketua` VARCHAR(100) NOT NULL,
                `jurusan_wakil` VARCHAR(100) NOT NULL,
                `foto` VARCHAR(255) DEFAULT NULL,
                `visi` TEXT NOT NULL,
                `misi` TEXT NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `votes` (
                `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
                `candidate_id` INT NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_candidate_id` (`candidate_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `username` VARCHAR(100) NOT NULL UNIQUE,
                `password_hash` VARCHAR(255) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            if (is_dir($storageDir) && is_writable($storageDir)) {
                @file_put_contents($versionFile, self::CURRENT_SCHEMA_VERSION);
            }

            self::$healed = true;
        } catch (\Throwable $e) {
            error_log('Database self-healing warning: ' . $e->getMessage());
        }
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }

    public static function inTransaction(): bool
    {
        return self::getConnection()->inTransaction();
    }
}
