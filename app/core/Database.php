<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $instance = null;

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
        }

        return self::$instance;
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
