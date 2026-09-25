<?php

declare(strict_types=1);

namespace App\Core;

class Security
{
    private static ?array $config = null;

    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/security.php';
        }
        return self::$config;
    }

    /**
     * Hash NISN menggunakan HMAC-SHA256 untuk pencarian deterministik yang aman
     */
    public static function hashNisn(string $nisn): string
    {
        $normalized = trim($nisn);
        $secret = self::getConfig()['hmac_secret'];
        return hash_hmac('sha256', $normalized, $secret);
    }

    /**
     * Dapatkan atau generate CSRF Token pada session
     */
    public static function generateCsrfToken(): string
    {
        Session::start();
        $token = Session::get('_csrf_token');
        if (!$token || !is_string($token)) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    /**
     * Validasi CSRF Token dengan timing-safe comparison
     */
    public static function validateCsrfToken(?string $token): bool
    {
        Session::start();
        $sessionToken = Session::get('_csrf_token');
        if (!$token || !$sessionToken) {
            return false;
        }
        return hash_equals($sessionToken, $token);
    }

    /**
     * Render hidden input field untuk form CSRF
     */
    public static function csrfField(): string
    {
        $tokenName = self::getConfig()['csrf_token_name'] ?? '_csrf_token';
        $token = self::generateCsrfToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($tokenName, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Escape output HTML untuk mencegah XSS
     */
    public static function escape(null|string|int|float $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validasi upload file gambar foto paslon (ekstensi, mime type, ukuran)
     * Format diizinkan: JPG, JPEG, PNG, WEBP. Maksimum: 2MB.
     */
    public static function validateImageUpload(array $file, int $maxBytes = 2097152): array
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            return [false, 'Parameter upload tidak valid.', ''];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return [false, 'Tidak ada file yang diupload.', ''];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return [false, 'Ukuran file melebihi batas yang diizinkan (maksimal 2MB).', ''];
            default:
                return [false, 'Terjadi kesalahan saat mengunggah file.', ''];
        }

        if ($file['size'] > $maxBytes) {
            return [false, 'Ukuran file terlalu besar. Maksimum 2MB.', ''];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp'
        ];

        $ext = array_search($mime, $allowedMimes, true);
        if ($ext === false) {
            return [false, 'Format file tidak diizinkan. Hanya JPG, PNG, dan WEBP yang diperbolehkan.', ''];
        }

        return [true, '', $ext];
    }
}
