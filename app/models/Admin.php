<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Admin extends Model
{
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, username, created_at FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function updatePassword(int $id, string $newPasswordHash): bool
    {
        $stmt = $this->db->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        return $stmt->execute([$newPasswordHash, $id]);
    }
}
