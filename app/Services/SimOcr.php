<?php

namespace App\Services;

use Carbon\Carbon;
use Symfony\Component\Process\Process;

/**
 * Baca SIM Indonesia dari foto (Tesseract OCR di server, bahasa ind+eng) untuk form Gate In Security Gate:
 * nama driver, nomor SIM, masa berlaku. Hasil hanya usulan pengisian -- security tetap memeriksa.
 *
 * Foto diperbesar 2x & grayscale (GD), lalu dibaca 2 kali: psm 3 (blok teks, kuat untuk nomor SIM) dan
 * psm 11 (teks tersebar, kuat untuk nama & tanggal di kartu yang difoto di atas meja / latar ramai).
 *   - nomor SIM : deret 12-16 digit (lama "1426-6103-000354", baru "3374050709780001")
 *   - nama      : baris setelah label "Nama/Name" (SIM baru) atau "1. NAMA" (SIM lama)
 *   - berlaku   : tanggal dd-mm-yyyy paling akhir di kartu (tanggal lahir selalu lebih awal)
 * SIM baru mencetak tanggal berlaku di bawah QR code; dari foto utuh sering terlewat, jadi bila belum ketemu
 * area kanan bawah kartu (dihitung dari posisi nomor SIM) dibaca ulang tersendiri.
 */
class SimOcr
{
    const BIN = '/usr/bin/tesseract';
    const TIMEOUT = 25;   // detik per pembacaan

    public static function available(): bool
    {
        return is_executable(self::BIN) && function_exists('imagecreatefromstring');
    }

    /** @return array{nama:?string, sim_no:?string, sim_exp:?string, text:string} */
    public static function read(string $path): array
    {
        $prepared = self::prepare($path);
        try {
            [$text11, $words] = self::tesseractTsv($prepared, 11);
            $text = self::tesseract($prepared, 3) . "\n" . $text11;
            $r = self::parse($text);
            if (!$r['sim_exp']) {
                $text .= "\n" . self::readExpiryArea($prepared, $words);
                $r = self::parse($text);
            }
        } finally {
            @unlink($prepared);
        }
        return $r + ['text' => $text];
    }

    /**
     * Baca ulang area tanggal berlaku (kanan bawah, di bawah QR). Patokan: kotak nomor SIM (lebar W) -- di SIM
     * tanggal berlaku ada sekitar 1.2-2.6 W ke kanan & 0.9-1.6 W ke bawah dari awal nomor. Tanpa patokan: pojok foto.
     */
    protected static function readExpiryArea(string $png, array $words): string
    {
        $im = @imagecreatefrompng($png);
        if (!$im) {
            return '';
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $boxes = [];
        foreach ($words as $wd) {
            if (preg_match('/^\d{12,16}$/', preg_replace('/\D/', '', $wd['text'])) && $wd['width'] > 0) {
                $W = $wd['width'];
                $boxes[] = [$wd['left'] + 1.2 * $W, $wd['top'] + 0.9 * $W, $wd['left'] + 2.6 * $W, $wd['top'] + 1.6 * $W];
                break;
            }
        }
        $boxes[] = [0.7 * $w, 0.8 * $h, $w, $h];
        $boxes[] = [0.6 * $w, 0.7 * $h, $w, $h];

        $out = '';
        foreach ($boxes as [$x1, $y1, $x2, $y2]) {
            $x1 = (int) max(0, $x1);
            $y1 = (int) max(0, $y1);
            $cw = (int) min($w, $x2) - $x1;
            $ch = (int) min($h, $y2) - $y1;
            if ($cw < 40 || $ch < 20) {
                continue;
            }
            $crop = imagecrop($im, ['x' => $x1, 'y' => $y1, 'width' => $cw, 'height' => $ch]);
            $file = tempnam(sys_get_temp_dir(), 'simocr') . '.png';
            imagepng($crop, $file);
            imagedestroy($crop);
            try {
                $t = self::tesseract($file, 11);
            } finally {
                @unlink($file);
            }
            $out .= $t . "\n";
            if (preg_match('/\b\d{2}[-.\/]\d{2}[-.\/]\d{4}\b/', $t)) {
                break;
            }
        }
        imagedestroy($im);
        return $out;
    }

    // perbesar 2x (maks sisi 3200 px) & grayscale ke PNG sementara
    protected static function prepare(string $path): string
    {
        $im = @imagecreatefromstring((string) file_get_contents($path));
        if (!$im) {
            throw new \RuntimeException('Foto identitas tidak bisa dibaca.');
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $scale = min(2, 3200 / max($w, $h));
        $dst = imagecreatetruecolor((int) round($w * $scale), (int) round($h * $scale));
        imagecopyresampled($dst, $im, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $w, $h);
        imagefilter($dst, IMG_FILTER_GRAYSCALE);
        $out = tempnam(sys_get_temp_dir(), 'simocr') . '.png';
        imagepng($dst, $out);
        imagedestroy($im);
        imagedestroy($dst);
        return $out;
    }

    protected static function tesseract(string $png, int $psm): string
    {
        $p = new Process([self::BIN, $png, 'stdout', '-l', 'ind+eng', '--psm', (string) $psm]);
        $p->setTimeout(self::TIMEOUT);
        $p->run();
        return $p->isSuccessful() ? $p->getOutput() : '';
    }

    /** teks + posisi kata (TSV) dalam satu pembacaan */
    protected static function tesseractTsv(string $png, int $psm): array
    {
        $base = tempnam(sys_get_temp_dir(), 'simocr');
        $p = new Process([self::BIN, $png, $base, '-l', 'ind+eng', '--psm', (string) $psm, 'txt', 'tsv']);
        $p->setTimeout(self::TIMEOUT);
        $p->run();
        $text = (string) @file_get_contents($base . '.txt');
        $words = [];
        foreach (array_slice(file($base . '.tsv', FILE_IGNORE_NEW_LINES) ?: [], 1) as $row) {
            $c = explode("\t", $row);
            if (count($c) >= 12 && trim($c[11]) !== '') {
                $words[] = ['left' => (int) $c[6], 'top' => (int) $c[7], 'width' => (int) $c[8], 'height' => (int) $c[9], 'text' => trim($c[11])];
            }
        }
        @unlink($base);
        @unlink($base . '.txt');
        @unlink($base . '.tsv');
        return [$text, $words];
    }

    public static function parse(string $text): array
    {
        $lines = array_values(array_filter(array_map(fn($l) => trim(preg_replace('/\s+/', ' ', $l)), preg_split('/\R/', $text)), 'strlen'));

        // nomor SIM: deret 12-16 digit (boleh dipisah - atau spasi), bukan bagian tanggal
        $simNo = null;
        foreach ($lines as $l) {
            if (preg_match_all('/(?<![\d-])(\d{4}[- ]?\d{4}[- ]?\d{4,8})(?![\d-])/', $l, $m)) {
                foreach ($m[1] as $cand) {
                    $digits = preg_replace('/\D/', '', $cand);
                    if (strlen($digits) >= 12 && strlen($digits) <= 16) {
                        $simNo = preg_replace('/\s+/', '', $cand);
                        break 2;
                    }
                }
            }
        }

        // nama: setelah label Nama / Name, atau baris "1. NAMA" (SIM lama)
        // nama masuk akal: huruf saja, min. 4 huruf, 2 kata atau lebih / 1 kata min. 5 huruf (buang potongan OCR "BN," "AN")
        $isName = function ($v) {
            $v = rtrim($v, ' .,-');
            $letters = strlen(preg_replace('/[^A-Z]/', '', $v));
            $words = count(array_filter(explode(' ', $v), fn($w) => strlen(preg_replace('/[^A-Z]/', '', $w)) >= 2));
            return preg_match('/^[A-Z][A-Z .,\'-]{2,60}$/', $v) && $letters >= 4 && ($words >= 2 || $letters >= 5)
                && !preg_match('/\b(NAMA|NAME|INDONESIA|SURAT|IZIN|MENGEMUDI|DRIVING|LICENSE|TEMPAT|LAHIR|POLRES|POLRESTA|POLRESTABES|POLDA|UMUM|PRIA|WANITA)\b/', $v);
        };
        // potongan OCR di ujung tanpa huruf vokal (mis. "TSS" dari gambar peta di belakang nama) dibuang
        $clean = function ($v) {
            $words = preg_split('/\s+/', trim(rtrim($v, ' .,-')));
            while (count($words) > 1 && !preg_match('/[AIUEO]/', end($words))) {
                array_pop($words);
            }
            return implode(' ', $words);
        };
        // semua kandidat dari tiap label (teks berisi 2 pembacaan psm 3 & 11): pilih yang paling sering muncul,
        // sama banyak -> yang terpendek (paling bersih)
        $cands = [];
        foreach ($lines as $i => $l) {
            if (!preg_match('/(?<![a-z])(?:n\s?a\s?m\s?a\s*[:\/]?\s*(?:name)?|name)(?![a-z])\s*:?\s*(.*)$/i', $l, $m)) {
                continue;
            }
            $same = $clean(strtoupper(trim($m[1] ?? '')));
            if ($same !== '' && $isName($same)) {
                $cands[] = $same;
                continue;
            }
            for ($j = $i + 1; $j < min($i + 11, count($lines)); $j++) {
                $v = $clean(strtoupper(trim($lines[$j])));
                if ($isName($v)) {
                    $cands[] = $v;
                    break;
                }
            }
        }
        if (!$cands) {
            foreach ($lines as $l) {
                if (preg_match('/^1\s*[.,]\s*([A-Z][A-Z .,\'-]{2,60})$/', strtoupper($l), $m) && $isName($clean(trim($m[1])))) {
                    $cands[] = $clean(trim($m[1]));
                }
            }
        }
        $nama = null;
        if ($cands) {
            $count = array_count_values($cands);
            uksort($count, fn($a, $b) => ($count[$b] <=> $count[$a]) ?: (strlen($a) <=> strlen($b)));
            $nama = array_key_first($count);
        }

        // masa berlaku: tanggal paling akhir (tanggal lahir / terbit selalu lebih awal)
        $exp = null;
        if (preg_match_all('/\b(\d{2})[-.\/](\d{2})[-.\/](\d{4})\b/', $text, $m, PREG_SET_ORDER)) {
            foreach ($m as $d) {
                if (!checkdate((int) $d[2], (int) $d[1], (int) $d[3])) {
                    continue;
                }
                $date = Carbon::create((int) $d[3], (int) $d[2], (int) $d[1])->startOfDay();
                if ($date->year >= now()->year - 10 && (!$exp || $date->gt($exp))) {
                    $exp = $date;
                }
            }
        }

        return [
            'nama'    => $nama ? trim(preg_replace('/\s+/', ' ', rtrim($nama, ' .,-'))) : null,
            'sim_no'  => $simNo,
            'sim_exp' => $exp ? $exp->toDateString() : null,
        ];
    }
}
