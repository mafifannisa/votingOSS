-- Schema E-Voting OSIS Paper Card
-- Database: voting_oss

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS election_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    election_name VARCHAR(255) NOT NULL,
    result_code_hash VARCHAR(255) NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_urut INT NOT NULL UNIQUE,
    nama_ketua VARCHAR(150) NOT NULL,
    nama_wakil VARCHAR(150) NOT NULL,
    jurusan_ketua VARCHAR(100) NOT NULL,
    jurusan_wakil VARCHAR(100) NOT NULL,
    foto VARCHAR(255) DEFAULT NULL,
    visi TEXT NOT NULL,
    misi TEXT NOT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS voters (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nisn VARCHAR(30) NULL,
    nisn_hash VARCHAR(128) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    kelas VARCHAR(50) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    has_voted TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_has_voted (has_voted),
    INDEX idx_nisn_hash (nisn_hash),
    INDEX idx_nisn (nisn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS votes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    candidate_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_votes_candidate FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    INDEX idx_candidate_id (candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Admin (username: admin, password: admin123)
INSERT INTO admins (username, password_hash)
SELECT 'admin', '$2y$12$f8qaVXWNrZv.AtWKzdh9nO5IVG9aKY94Pwrxk9huAXG4o3Rps.SYW'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = 'admin');

-- Default Election Configuration (kode akses default: osis2026)
INSERT INTO election_config (id, election_name, result_code_hash, status)
SELECT 1, 'Pemilihan Ketua & Wakil Ketua OSIS 2026/2027', '$2y$12$NRkWAwvyfXBAJSoIAM8fQOKittBkKVRJ02l5X7O2qUrkdNESwzLeO', 1
WHERE NOT EXISTS (SELECT 1 FROM election_config WHERE id = 1);
