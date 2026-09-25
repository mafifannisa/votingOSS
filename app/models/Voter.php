<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Voter extends Model
{
    public function findByNisnHash(string $nisnHash): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM voters WHERE nisn_hash = ? LIMIT 1');
        $stmt->execute([$nisnHash]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM voters WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getAll(int $limit = 50, int $offset = 0, ?string $search = null): array
    {
        if ($search) {
            $stmt = $this->db->prepare(
                'SELECT id, nama, kelas, jurusan, has_voted, created_at 
                 FROM voters 
                 WHERE nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ? 
                 ORDER BY id DESC LIMIT ? OFFSET ?'
            );
            $queryParam = '%' . $search . '%';
            $stmt->bindValue(1, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(2, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(3, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(4, $limit, PDO::PARAM_INT);
            $stmt->bindValue(5, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare(
            'SELECT id, nama, kelas, jurusan, has_voted, created_at 
             FROM voters 
             ORDER BY id DESC LIMIT ? OFFSET ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countAll(?string $search = null): int
    {
        if ($search) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM voters 
                 WHERE nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ?'
            );
            $queryParam = '%' . $search . '%';
            $stmt->execute([$queryParam, $queryParam, $queryParam]);
            return (int) $stmt->fetchColumn();
        }

        return (int) $this->db->query('SELECT COUNT(*) FROM voters')->fetchColumn();
    }

    public function countVoted(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM voters WHERE has_voted = 1')->fetchColumn();
    }

    public function countNotVoted(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM voters WHERE has_voted = 0')->fetchColumn();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO voters (nisn_hash, nama, kelas, jurusan, has_voted, created_at)
             VALUES (?, ?, ?, ?, 0, NOW())'
        );
        $stmt->execute([
            $data['nisn_hash'],
            $data['nama'],
            $data['kelas'],
            $data['jurusan']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE voters SET nama = ?, kelas = ?, jurusan = ? WHERE id = ?'
        );
        return $stmt->execute([
            $data['nama'],
            $data['kelas'],
            $data['jurusan'],
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM voters WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function deleteAll(): bool
    {
        return (bool) $this->db->exec('DELETE FROM voters');
    }
}
