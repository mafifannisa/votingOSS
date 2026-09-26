<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;
use PDOException;

class Election extends Model
{
    private static ?array $cachedConfig = null;

    public const DEFAULT_CONFIG = [
        'id' => 1,
        'app_name' => 'E-VOTING OSIS',
        'app_logo' => '/assets/images/Logo_OSIS.svg',
        'footer_text' => '© 2026 Pemilihan Ketua OSIS • Sistem E-Voting Paper Card',
        'footer_logo' => '/assets/images/Logo_OSIS.svg',
        'election_name' => 'Pemilihan Ketua & Wakil Ketua OSIS 2026/2027',
        'result_code_hash' => '$2y$12$NRkWAwvyfXBAJSoIAM8fQOKittBkKVRJ02l5X7O2qUrkdNESwzLeO',
        'status' => 1
    ];

    public function getConfig(bool $forceRefresh = false): array
    {
        if (self::$cachedConfig !== null && !$forceRefresh) {
            return self::$cachedConfig;
        }

        try {
            $stmt = $this->db->query('SELECT * FROM election_config WHERE id = 1 LIMIT 1');
            $result = $stmt->fetch();
            if ($result) {
                // Pastikan key-key penting tidak kosong
                $merged = array_merge(self::DEFAULT_CONFIG, array_filter($result, fn($v) => $v !== null && $v !== ''));
                self::$cachedConfig = $merged;
                return $merged;
            }
        } catch (PDOException $e) {
            Database::selfHeal($this->db, true);
            try {
                $stmt = $this->db->query('SELECT * FROM election_config WHERE id = 1 LIMIT 1');
                $result = $stmt->fetch();
                if ($result) {
                    $merged = array_merge(self::DEFAULT_CONFIG, array_filter($result, fn($v) => $v !== null && $v !== ''));
                    self::$cachedConfig = $merged;
                    return $merged;
                }
            } catch (PDOException $e2) {
                // Abaikan dan gunakan default
            }
        }

        self::$cachedConfig = self::DEFAULT_CONFIG;
        return self::DEFAULT_CONFIG;
    }

    public function updateSettings(array $data): bool
    {
        try {
            $res = $this->doUpdateSettings($data);
            if ($res) {
                self::$cachedConfig = null;
            }
            return $res;
        } catch (PDOException $e) {
            Database::selfHeal($this->db, true);
            $res = $this->doUpdateSettings($data);
            if ($res) {
                self::$cachedConfig = null;
            }
            return $res;
        }
    }

    private function doUpdateSettings(array $data): bool
    {
        $allowed = ['app_name', 'app_logo', 'footer_text', 'footer_logo', 'election_name', 'result_code_hash', 'status'];
        $fields = [];
        $values = [];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "`{$key}` = ?";
                $values[] = $data[$key];
            }
        }

        if (empty($fields)) {
            return true;
        }

        $sql = 'UPDATE election_config SET ' . implode(', ', $fields) . ' WHERE id = 1';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function updateConfig(string $name, ?string $resultCodeHash = null): bool
    {
        $data = ['election_name' => $name];
        if ($resultCodeHash !== null && $resultCodeHash !== '') {
            $data['result_code_hash'] = $resultCodeHash;
        }
        return $this->updateSettings($data);
    }

    public function resetDefaults(): bool
    {
        return $this->updateSettings([
            'app_name' => self::DEFAULT_CONFIG['app_name'],
            'app_logo' => self::DEFAULT_CONFIG['app_logo'],
            'footer_text' => self::DEFAULT_CONFIG['footer_text'],
            'footer_logo' => self::DEFAULT_CONFIG['footer_logo'],
            'election_name' => self::DEFAULT_CONFIG['election_name'],
        ]);
    }

    public function verifyResultCode(string $inputCode): bool
    {
        $config = $this->getConfig();
        if (empty($config['result_code_hash'])) {
            return false;
        }

        return password_verify($inputCode, $config['result_code_hash']);
    }
}
