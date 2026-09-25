<?php

declare(strict_types=1);

// Set timezone
$appConfig = require_once __DIR__ . '/../config/app.php';
date_default_timezone_set($appConfig['timezone'] ?? 'Asia/Jakarta');

// Error reporting sesuai environment
if ($appConfig['debug'] ?? false) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Autoloader sederhana PSR-4
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    
    // Pecah path dan periksa struktur folder
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $subDir = strtolower(implode('/', $parts));
    
    $file = $baseDir . ($subDir ? $subDir . '/' : '') . $className . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Router;
use App\Core\Session;

// Inisialisasi session aman
Session::start();

// Inisialisasi Router
$router = new Router();

// Muat definisi routes
require_once __DIR__ . '/../routes/web.php';

// Dispatch request saat ini
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($requestMethod, $requestUri);
