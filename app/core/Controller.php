<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Render view template dalam layout
     */
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        extract($data);

        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file tidak ditemukan: {$viewPath}");
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutPath = __DIR__ . '/../views/layouts/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout file tidak ditemukan: {$layoutPath}");
        }

        require $layoutPath;
    }

    /**
     * Response JSON
     */
    protected function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect ke path tertentu
     */
    protected function redirect(string $path): void
    {
        header("Location: {$path}");
        exit;
    }

    /**
     * Verifikasi token CSRF pada request POST
     */
    protected function validateCsrf(): void
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            die('Invalid CSRF Token. Permintaan ditolak demi keamanan.');
        }
    }

    /**
     * Pastikan pengguna adalah Admin yang sudah login
     */
    protected function requireAdmin(): void
    {
        Session::start();
        if (!Session::has('admin_id')) {
            Session::setFlash('error', 'Silakan login sebagai admin terlebih dahulu.');
            $this->redirect('/admin/login');
        }
    }

    /**
     * Pastikan pemilih sudah login dengan NISN
     */
    protected function requireVoter(): void
    {
        Session::start();
        if (!Session::has('voter_id')) {
            Session::setFlash('error', 'Silakan masukkan NISN Anda terlebih dahulu.');
            $this->redirect('/');
        }
    }
}
