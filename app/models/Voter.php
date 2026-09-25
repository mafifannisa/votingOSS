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
                'SELECT id, nisn, nama, kelas, jurusan, has_voted, created_at 
                 FROM voters 
                 WHERE nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ? OR nisn LIKE ?
                 ORDER BY id DESC LIMIT ? OFFSET ?'
            );
            $queryParam = '%' . $search . '%';
            $stmt->bindValue(1, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(2, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(3, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(4, $queryParam, PDO::PARAM_STR);
            $stmt->bindValue(5, $limit, PDO::PARAM_INT);
            $stmt->bindValue(6, $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare(
            'SELECT id, nisn, nama, kelas, jurusan, has_voted, created_at 
             FROM voters 
             ORDER BY id DESC LIMIT ? OFFSET ?'
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllForPrint(?string $kelas = null, ?string $search = null): array
    {
        $sql = 'SELECT id, nisn, nama, kelas, jurusan, has_voted, created_at FROM voters WHERE 1=1';
        $params = [];

        if (!empty($kelas)) {
            $sql .= ' AND kelas = ?';
            $params[] = $kelas;
        }

        if (!empty($search)) {
            $sql .= ' AND (nama LIKE ? OR nisn LIKE ? OR jurusan LIKE ?)';
            $p = '%' . $search . '%';
            $params[] = $p;
            $params[] = $p;
            $params[] = $p;
        }

        $sql .= ' ORDER BY kelas ASC, nama ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDistinctClasses(): array
    {
        return $this->db->query('SELECT DISTINCT kelas FROM voters WHERE kelas IS NOT NULL AND kelas != "" ORDER BY kelas ASC')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function countAll(?string $search = null): int
    {
        if ($search) {
            $stmt = $this->db->prepare(
                'SELECT COUNT(*) FROM voters 
                 WHERE nama LIKE ? OR kelas LIKE ? OR jurusan LIKE ? OR nisn LIKE ?'
            );
            $queryParam = '%' . $search . '%';
            $stmt->execute([$queryParam, $queryParam, $queryParam, $queryParam]);
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
            'INSERT INTO voters (nisn, nisn_hash, nama, kelas, jurusan, has_voted, created_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW())'
        );
        $stmt->execute([
            $data['nisn'] ?? null,
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

    /**
     * Reset status hak pilih satu siswa (mengembalikan has_voted ke 0)
     */
    public function resetVoteStatus(int $id): bool
    {
        $stmt = $this->db->prepare('UPDATE voters SET has_voted = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Reset status hak pilih semua siswa (mengembalikan has_voted ke 0)
     */
    public function resetAllVoteStatus(): bool
    {
        return (bool) $this->db->exec('UPDATE voters SET has_voted = 0');
    }
}
