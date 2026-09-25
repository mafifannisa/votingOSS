<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;

    $relativeClass = substr($class, $len);
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $subDir = strtolower(implode('/', $parts));
    $file = $baseDir . ($subDir ? $subDir . '/' : '') . $className . '.php';

    if (file_exists($file)) require_once $file;
});

use App\Core\Security;
use App\Models\Candidate;
use App\Models\Voter;
use App\Models\Vote;

echo "Menyiapkan data awal (seeding)..." . PHP_EOL;

$candidateModel = new Candidate();
$voterModel = new Voter();
$voteModel = new Vote();

// Reset data voting sebelumnya
$voteModel->resetVotes();

// Bersihkan calon
foreach ($candidateModel->getAll() as $c) {
    $candidateModel->delete((int)$c['id']);
}

// Tambah Paslon 1
$candidateModel->create([
    'nomor_urut' => 1,
    'nama_ketua' => 'Fathan Al-Ghifari',
    'nama_wakil' => 'Nabila Putri',
    'jurusan_ketua' => 'XII RPL 1',
    'jurusan_wakil' => 'XI AKL 2',
    'foto' => null,
    'visi' => "Mewujudkan OSIS yang aspiratif, inklusif, dan berdaya saing melalui pemanfaatan ekosistem digital sekolah.",
    'misi' => "1. Membangun ruang apresiasi dan inovasi siswa berbasis kreativitas digital.\n2. Mengadakan forum aspirasi terbuka secara berkala antara siswa dan panitia OSIS.\n3. Menguatkan kegiatan kolaboratif lintas bidang untuk solidaritas sekolah.",
    'status' => 1
]);

// Tambah Paslon 2
$candidateModel->create([
    'nomor_urut' => 2,
    'nama_ketua' => 'Rayhan Maulana',
    'nama_wakil' => 'Zahra Anindya',
    'jurusan_ketua' => 'XII TKJ 2',
    'jurusan_wakil' => 'XI DKV 1',
    'foto' => null,
    'visi' => "Menjadikan OSIS sebagai pelopor aksi nyata, empati sosial, dan pengembangan potensi minat bakat siswa.",
    'misi' => "1. Menyelenggarakan festival minat bakat seni, olahraga, dan teknologi secara terpadu.\n2. Menjalankan program sosial kepedulian lingkungan dan bakti masyarakat.\n3. Mengoptimalkan peran ekstrakurikuler sebagai wadah pembentukan karakter unggul.",
    'status' => 1
]);

// Reset dan isi sample pemilih (DPT)
$voterModel->deleteAll();

$sampleStudents = [
    ['nisn' => '0051234567', 'nama' => 'Ahmad Pratama', 'kelas' => 'X RPL 1', 'jurusan' => 'Rekayasa Perangkat Lunak'],
    ['nisn' => '0051234568', 'nama' => 'Budi Santoso', 'kelas' => 'XI TKJ 2', 'jurusan' => 'Teknik Komputer & Jaringan'],
    ['nisn' => '0051234569', 'nama' => 'Citra Lestari', 'kelas' => 'XII DKV 1', 'jurusan' => 'Desain Komunikasi Visual'],
    ['nisn' => '0051234570', 'nama' => 'Dinda Kirana', 'kelas' => 'X AKL 2', 'jurusan' => 'Akuntansi'],
    ['nisn' => '0051234571', 'nama' => 'Eko Prasetyo', 'kelas' => 'XI RPL 2', 'jurusan' => 'Rekayasa Perangkat Lunak'],
    ['nisn' => '0051234572', 'nama' => 'Farhan Hidayat', 'kelas' => 'XII TKJ 1', 'jurusan' => 'Teknik Komputer & Jaringan'],
    ['nisn' => '0051234573', 'nama' => 'Gita Permata', 'kelas' => 'X DKV 2', 'jurusan' => 'Desain Komunikasi Visual'],
    ['nisn' => '0051234574', 'nama' => 'Hadi Firmansyah', 'kelas' => 'XI AKL 1', 'jurusan' => 'Akuntansi'],
];

foreach ($sampleStudents as $student) {
    $voterModel->create([
        'nisn_hash' => Security::hashNisn($student['nisn']),
        'nama' => $student['nama'],
        'kelas' => $student['kelas'],
        'jurusan' => $student['jurusan']
    ]);
}

echo "Seeding selesai berhasil! 2 Paslon dan 8 Data Pemilih (DPT) telah ditambahkan." . PHP_EOL;
