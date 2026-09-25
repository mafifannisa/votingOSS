<?php

declare(strict_types=1);

echo "===============================================" . PHP_EOL;
echo "   E-VOTING OSIS - DATABASE INSTALL & RESTORE  " . PHP_EOL;
echo "===============================================" . PHP_EOL;

$config = require __DIR__ . '/../config/database.php';

$host = $config['host'] ?? '127.0.0.1';
$port = (int)($config['port'] ?? 3306);
$dbname = $config['database'] ?? 'voting_oss';
$user = $config['username'] ?? 'root';
$pass = $config['password'] ?? '';

// Parsing command line arguments jika ada
$options = getopt('', ['host:', 'port:', 'db:', 'user:', 'pass:', 'demo::']);
if (isset($options['host'])) $host = (string)$options['host'];
if (isset($options['port'])) $port = (int)$options['port'];
if (isset($options['db'])) $dbname = (string)$options['db'];
if (isset($options['user'])) $user = (string)$options['user'];
if (isset($options['pass'])) $pass = (string)$options['pass'];
$seedDemo = isset($options['demo']) ? true : true;

echo "Target Server : {$host}:{$port}" . PHP_EOL;
echo "Nama Database : {$dbname}" . PHP_EOL;
echo "User MySQL    : {$user}" . PHP_EOL;
echo "-----------------------------------------------" . PHP_EOL;

try {
    echo "1. Menghubungkan ke MySQL server... ";
    $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "OK" . PHP_EOL;

    echo "2. Membuat database '{$dbname}' (jika belum ada)... ";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "OK" . PHP_EOL;

    echo "3. Menghubungkan ke database '{$dbname}'... ";
    $pdoDb = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    echo "OK" . PHP_EOL;

    echo "4. Mengimpor tabel dari database/schema.sql... ";
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File {$schemaFile} tidak ditemukan!");
    }
    $schemaSql = file_get_contents($schemaFile);
    $pdoDb->exec($schemaSql);
    echo "OK" . PHP_EOL;

    if ($seedDemo) {
        echo "5. Mengisi data demo paslon & pemilih... ";
        $seedFile = __DIR__ . '/seed.php';
        if (file_exists($seedFile)) {
            ob_start();
            include $seedFile;
            ob_end_clean();
        }
        echo "OK" . PHP_EOL;
    }

    // Tulis lock file
    @file_put_contents(__DIR__ . '/../storage/.installed', date('Y-m-d H:i:s'));

    echo "-----------------------------------------------" . PHP_EOL;
    echo "SUKSES: Database berhasil diinisialisasi dan siap digunakan!" . PHP_EOL;
    echo "Login Panitia: username 'admin' | password 'admin123'" . PHP_EOL;
    echo "Kode Akses Hasil: 'osis2026'" . PHP_EOL;
    echo "===============================================" . PHP_EOL;

} catch (PDOException $e) {
    echo PHP_EOL . "GAGAL: Terjadi error database: " . $e->getMessage() . PHP_EOL;
    exit(1);
} catch (Exception $e) {
    echo PHP_EOL . "GAGAL: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
