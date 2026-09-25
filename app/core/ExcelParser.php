<?php

declare(strict_types=1);

namespace App\Core;

use ZipArchive;
use RuntimeException;

class ExcelParser
{
    /**
     * Parse file .xlsx atau .csv menjadi array baris data pemilih
     * Mengembalikan: ['success' => bool, 'error' => string, 'data' => array]
     */
    public static function parse(string $filePath, string $extension): array
    {
        $ext = strtolower($extension);
        if ($ext === 'csv') {
            return self::parseCsv($filePath);
        } elseif ($ext === 'xlsx') {
            return self::parseXlsx($filePath);
        }

        return [
            'success' => false,
            'error' => 'Format file tidak didukung. Harap gunakan file .xlsx atau .csv.',
            'data' => []
        ];
    }

    private static function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'error' => 'Gagal membaca file CSV.', 'data' => []];
        }

        // Baca baris pertama untuk deteksi delimiter dan sep= directive
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return ['success' => false, 'error' => 'File CSV kosong.', 'data' => []];
        }

        // Hapus BOM UTF-8 jika ada di awal file
        $bom = pack('CCC', 0xef, 0xbb, 0xbf);
        if (str_starts_with($firstLine, $bom)) {
            $firstLine = substr($firstLine, 3);
        }

        $delimiter = ',';
        $trimmedFirstLine = trim($firstLine);

        // Cek apakah ada direktif Excel "sep=X"
        if (preg_match('/^sep=([,;\t|])/i', $trimmedFirstLine, $matches)) {
            $delimiter = $matches[1];
            // Lanjut ke baris berikutnya untuk header sebenarnya
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                fclose($handle);
                return ['success' => false, 'error' => 'File CSV tidak memiliki baris data atau header.', 'data' => []];
            }
        } else {
            // Auto-detect delimiter berdasarkan jumlah kemunculan di baris pertama
            $semicolonCount = substr_count($firstLine, ';');
            $commaCount = substr_count($firstLine, ',');
            $tabCount = substr_count($firstLine, "\t");

            if ($semicolonCount > $commaCount && $semicolonCount >= $tabCount) {
                $delimiter = ';';
            } elseif ($tabCount > $commaCount && $tabCount > $semicolonCount) {
                $delimiter = "\t";
            } else {
                $delimiter = ',';
            }
        }

        // Parse header dari baris header pertama
        $headerRaw = str_getcsv(trim($firstLine), $delimiter, '"', "\\");
        $header = array_map(fn($col) => strtolower(trim((string)$col)), $headerRaw);

        $rows = [];
        while (($row = fgetcsv($handle, 1000, $delimiter, '"', "\\")) !== false) {
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $mappedRow = [];
            foreach ($header as $idx => $colName) {
                $mappedRow[$colName] = trim((string)($row[$idx] ?? ''));
            }
            $rows[] = $mappedRow;
        }

        fclose($handle);
        return self::mapAndValidateRows($rows);
    }

    private static function parseXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return ['success' => false, 'error' => 'Ekstensi ZipArchive PHP tidak aktif.', 'data' => []];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ['success' => false, 'error' => 'Gagal membuka file XLSX.', 'data' => []];
        }

        $sharedStrings = self::readSharedStrings($zip);
        
        // Cari sheet pertama
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            return ['success' => false, 'error' => 'Sheet1 tidak ditemukan dalam file XLSX.', 'data' => []];
        }

        $xml = simplexml_load_string($sheetXml);
        $zip->close();

        if ($xml === false) {
            return ['success' => false, 'error' => 'Struktur XML file XLSX tidak valid.', 'data' => []];
        }

        $xml->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rowNodes = $xml->xpath('//m:sheetData/m:row');

        if (!is_array($rowNodes) || empty($rowNodes)) {
            return ['success' => false, 'error' => 'File XLSX kosong atau tidak memiliki data.', 'data' => []];
        }

        $rawGrid = [];
        foreach ($rowNodes as $rowNode) {
            $rowNum = (int)$rowNode['r'];
            $cellNodes = $rowNode->xpath('./*[local-name()="c"]');
            if (!is_array($cellNodes)) continue;

            $colIndex = 0;
            foreach ($cellNodes as $cell) {
                $ref = (string)$cell['r'];
                $val = '';
                $v = $cell->xpath('./*[local-name()="v"]');

                if (is_array($v) && isset($v[0])) {
                    $raw = (string)$v[0];
                    if ((string)$cell['t'] === 's') {
                        $idx = (int)$raw;
                        $val = $sharedStrings[$idx] ?? '';
                    } else {
                        $val = $raw;
                    }
                } elseif ((string)$cell['t'] === 'inlineStr') {
                    $inline = $cell->xpath('.//*[local-name()="t"]');
                    if (is_array($inline) && isset($inline[0])) {
                        $val = (string)$inline[0];
                    }
                }

                $rawGrid[$rowNum][$colIndex] = trim((string)$val);
                $colIndex++;
            }
        }

        if (empty($rawGrid)) {
            return ['success' => false, 'error' => 'Tidak ada data terbaca dalam file.', 'data' => []];
        }

        ksort($rawGrid);
        $header = null;
        $rows = [];

        foreach ($rawGrid as $row) {
            if ($header === null) {
                $header = array_map(fn($v) => strtolower(trim((string)$v)), $row);
                continue;
            }

            if (empty(array_filter($row))) {
                continue;
            }

            $mappedRow = [];
            foreach ($header as $idx => $colName) {
                $mappedRow[$colName] = trim((string)($row[$idx] ?? ''));
            }
            $rows[] = $mappedRow;
        }

        return self::mapAndValidateRows($rows);
    }

    private static function readSharedStrings(ZipArchive $zip): array
    {
        $strings = [];
        $xmlContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlContent === false) {
            return $strings;
        }

        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) {
            return $strings;
        }

        $xml->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $items = $xml->xpath('//m:si');

        if (is_array($items)) {
            foreach ($items as $si) {
                $texts = $si->xpath('.//*[local-name()="t"]');
                $str = '';
                if (is_array($texts)) {
                    foreach ($texts as $t) {
                        $str .= (string)$t;
                    }
                }
                $strings[] = $str;
            }
        }

        return $strings;
    }

    private static function mapAndValidateRows(array $rows): array
    {
        if (empty($rows)) {
            return ['success' => false, 'error' => 'File tidak memiliki baris data.', 'data' => []];
        }

        // Cari index kolom yang cocok untuk: nisn, nama, kelas, jurusan
        $firstRow = $rows[0];
        $keys = array_keys($firstRow);

        $nisnKey = self::findKey($keys, ['nisn', 'nomor_induk_siswa_nasional', 'no_nisn', 'nis']);
        $namaKey = self::findKey($keys, ['nama', 'nama_siswa', 'nama_lengkap', 'name']);
        $kelasKey = self::findKey($keys, ['kelas', 'tingkat', 'rombel', 'class']);
        $jurusanKey = self::findKey($keys, ['jurusan', 'kompetensi_keahlian', 'program_keahlian', 'prodi']);

        if (!$nisnKey || !$namaKey) {
            return [
                'success' => false,
                'error' => 'Struktur kolom tidak sesuai. Kolom NISN dan Nama wajib ada dalam file.',
                'data' => []
            ];
        }

        $sanitizedData = [];
        $seenNisn = [];

        foreach ($rows as $index => $row) {
            $rawNisn = trim((string)($row[$nisnKey] ?? ''));
            // Hapus karakter non-angka (seperti spasi atau petik)
            $cleanNisn = preg_replace('/[^0-9]/', '', $rawNisn);

            if (empty($cleanNisn)) {
                continue; // Lewati baris tanpa NISN
            }

            if (isset($seenNisn[$cleanNisn])) {
                continue; // Hindari duplikat dalam 1 file
            }
            $seenNisn[$cleanNisn] = true;

            $nama = trim((string)($row[$namaKey] ?? ''));
            $kelas = $kelasKey ? trim((string)($row[$kelasKey] ?? '-')) : '-';
            $jurusan = $jurusanKey ? trim((string)($row[$jurusanKey] ?? '-')) : '-';

            if (empty($nama)) {
                continue;
            }

            $sanitizedData[] = [
                'nisn' => $cleanNisn,
                'nama' => $nama,
                'kelas' => $kelas ?: '-',
                'jurusan' => $jurusan ?: '-'
            ];
        }

        if (empty($sanitizedData)) {
            return ['success' => false, 'error' => 'Tidak ditemukan data siswa yang valid dalam file.', 'data' => []];
        }

        return [
            'success' => true,
            'error' => '',
            'data' => $sanitizedData
        ];
    }

    private static function findKey(array $keys, array $candidates): ?string
    {
        foreach ($keys as $k) {
            $cleanK = strtolower(str_replace([' ', '_', '-'], '', $k));
            foreach ($candidates as $c) {
                $cleanC = strtolower(str_replace([' ', '_', '-'], '', $c));
                if ($cleanK === $cleanC || str_contains($cleanK, $cleanC)) {
                    return $k;
                }
            }
        }
        return null;
    }

    /**
     * Membuat template file .xlsx sederhana secara native (menggunakan ZipArchive bawaan PHP)
     * Mengembalikan path ke file temporary yang dihasilkan.
     */
    public static function createXlsxTemplate(array $headers, array $rows): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'tpl_xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuat file template XLSX sementara.');
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="DPT Pemilih" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $allRows = array_merge([$headers], $rows);
        $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>'
            . '<col min="1" max="1" width="18" customWidth="1"/>'
            . '<col min="2" max="2" width="28" customWidth="1"/>'
            . '<col min="3" max="3" width="14" customWidth="1"/>'
            . '<col min="4" max="4" width="32" customWidth="1"/>'
            . '</cols>'
            . '<sheetData>';

        foreach ($allRows as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $sheetXml .= '<row r="' . $rowNum . '">';
            foreach ($row as $cIdx => $cellVal) {
                $colLetter = $colLetters[$cIdx] ?? 'A';
                $escaped = htmlspecialchars((string)$cellVal, ENT_XML1, 'UTF-8');
                // Menggunakan inlineStr agar angka NISN tetap bertipe teks dan angka 0 di depan tidak dipotong oleh Excel
                $sheetXml .= '<c r="' . $colLetter . $rowNum . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
            }
            $sheetXml .= '</row>';
        }
        $sheetXml .= '</sheetData></worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/workbook.xml', $wb);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return $tempFile;
    }
}
