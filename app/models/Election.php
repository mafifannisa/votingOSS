<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use App\Core\Model;
use PDO;
use PDOException;

class Election extends Model
{
    public function getConfig(): ?array
    {
        try {
            $stmt = $this->db->query('SELECT * FROM election_config WHERE id = 1 LIMIT 1');
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            Database::selfHeal($this->db, true);
            try {
                $stmt = $this->db->query('SELECT * FROM election_config WHERE id = 1 LIMIT 1');
                $result = $stmt->fetch();
                return $result ?: null;
            } catch (PDOException $e2) {
                return null;
            }
        }
    }

    public function updateConfig(string $name, ?string $resultCodeHash = null): bool
    {
        try {
            return $this->doUpdateConfig($name, $resultCodeHash);
        } catch (PDOException $e) {
            Database::selfHeal($this->db, true);
            return $this->doUpdateConfig($name, $resultCodeHash);
        }
    }

    private function doUpdateConfig(string $name, ?string $resultCodeHash = null): bool
    {
        if ($resultCodeHash) {
            $stmt = $this->db->prepare('UPDATE election_config SET election_name = ?, result_code_hash = ? WHERE id = 1');
            return $stmt->execute([$name, $resultCodeHash]);
        }

        $stmt = $this->db->prepare('UPDATE election_config SET election_name = ? WHERE id = 1');
        return $stmt->execute([$name]);
    }

    public function verifyResultCode(string $inputCode): bool
    {
        $config = $this->getConfig();
        if (!$config || empty($config['result_code_hash'])) {
            return false;
        }

        return password_verify($inputCode, $config['result_code_hash']);
    }
}
