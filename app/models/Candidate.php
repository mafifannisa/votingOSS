<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Candidate extends Model
{
    public function getAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM candidates ORDER BY nomor_urut ASC');
        return $stmt->fetchAll();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query('SELECT * FROM candidates WHERE status = 1 ORDER BY nomor_urut ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM candidates WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function findByNomorUrut(int $nomorUrut, ?int $excludeId = null): ?array
    {
        if ($excludeId !== null) {
            $stmt = $this->db->prepare('SELECT * FROM candidates WHERE nomor_urut = ? AND id != ? LIMIT 1');
            $stmt->execute([$nomorUrut, $excludeId]);
        } else {
            $stmt = $this->db->prepare('SELECT * FROM candidates WHERE nomor_urut = ? LIMIT 1');
            $stmt->execute([$nomorUrut]);
        }
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO candidates 
                (nomor_urut, nama_ketua, nama_wakil, jurusan_ketua, jurusan_wakil, foto, visi, misi, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['nomor_urut'],
            $data['nama_ketua'],
            $data['nama_wakil'],
            $data['jurusan_ketua'],
            $data['jurusan_wakil'],
            $data['foto'] ?? null,
            $data['visi'],
            $data['misi'],
            $data['status'] ?? 1
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [
            'nomor_urut = ?',
            'nama_ketua = ?',
            'nama_wakil = ?',
            'jurusan_ketua = ?',
            'jurusan_wakil = ?',
            'visi = ?',
            'misi = ?',
            'status = ?'
        ];

        $params = [
            $data['nomor_urut'],
            $data['nama_ketua'],
            $data['nama_wakil'],
            $data['jurusan_ketua'],
            $data['jurusan_wakil'],
            $data['visi'],
            $data['misi'],
            $data['status'] ?? 1
        ];

        if (array_key_exists('foto', $data) && $data['foto'] !== null) {
            $fields[] = 'foto = ?';
            $params[] = $data['foto'];
        }

        $params[] = $id;

        $sql = 'UPDATE candidates SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM candidates WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
