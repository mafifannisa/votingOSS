<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use PDO;
use Exception;

class Vote extends Model
{
    /**
     * Catat suara pemilih dengan Database Transaction dan Atomic Conditional Update
     * Mencegah Double Voting secara mutlak (Race Condition Safe).
     */
    public function recordVote(int $voterId, int $candidateId): bool
    {
        try {
            Database::beginTransaction();

            // 1. Kunci dan perbarui status pemilih jika dan hanya jika belum memilih
            $stmtVoter = $this->db->prepare(
                'UPDATE voters SET has_voted = 1 WHERE id = ? AND has_voted = 0'
            );
            $stmtVoter->execute([$voterId]);

            // Jika rowCount !== 1, berarti pemilih sudah pernah memilih atau ID tidak valid
            if ($stmtVoter->rowCount() !== 1) {
                Database::rollBack();
                return false;
            }

            // 2. Simpan suara ke tabel votes (Secret Ballot: tanpa referensi voterId)
            $stmtVote = $this->db->prepare(
                'INSERT INTO votes (candidate_id, created_at) VALUES (?, NOW())'
            );
            $stmtVote->execute([$candidateId]);

            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            error_log('Vote recording error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan perolehan suara setiap paslon (termasuk paslon yang belum mendapat suara)
     */
    public function getResults(): array
    {
        $sql = 'SELECT 
                    c.id, 
                    c.nomor_urut, 
                    c.nama_ketua, 
                    c.nama_wakil, 
                    c.jurusan_ketua, 
                    c.jurusan_wakil, 
                    c.foto, 
                    c.status,
                    COUNT(v.id) AS total_suara
                FROM candidates c
                LEFT JOIN votes v ON c.id = v.candidate_id
                GROUP BY c.id
                ORDER BY c.nomor_urut ASC';

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Total seluruh suara yang sudah masuk ke kotak suara digital
     */
    public function getTotalVotes(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM votes')->fetchColumn();
    }

    /**
     * Reset seluruh data suara
     */
    public function resetVotes(): bool
    {
        try {
            Database::beginTransaction();
            $this->db->exec('DELETE FROM votes');
            $this->db->exec('UPDATE voters SET has_voted = 0');
            Database::commit();
            return true;
        } catch (Exception $e) {
            Database::rollBack();
            return false;
        }
    }
}
