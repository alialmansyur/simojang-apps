<?php

namespace App\Services\PNBP;

class PNBPHelper
{
    /**
     * Konversi angka nominal ke kalimat terbilang Bahasa Indonesia
     */
    public static function terbilang($nilai): string
    {
        $nilai = abs((float) $nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' ' . $huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = self::terbilang($nilai - 10) . ' Belas';
        } elseif ($nilai < 100) {
            $temp = self::terbilang($nilai / 10) . ' Puluh' . self::terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = ' Seratus' . self::terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = self::terbilang($nilai / 100) . ' Ratus' . self::terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = ' Seribu' . self::terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = self::terbilang($nilai / 1000) . ' Ribu' . self::terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = self::terbilang($nilai / 1000000) . ' Juta' . self::terbilang($nilai % 1000000);
        } elseif ($nilai < 1000000000000) {
            $temp = self::terbilang($nilai / 1000000000) . ' Miliar' . self::terbilang(fmod($nilai, 1000000000));
        } elseif ($nilai < 1000000000000000) {
            $temp = self::terbilang($nilai / 1000000000000) . ' Triliun' . self::terbilang(fmod($nilai, 1000000000000));
        }

        return trim($temp) . ' Rupiah';
    }

    /**
     * Format Rupiah (contoh: Rp 1.500.000,-)
     */
    public static function formatRupiah($nominal, bool $withSymbol = true): string
    {
        $res = number_format((float) $nominal, 0, ',', '.');
        return $withSymbol ? 'Rp ' . $res : $res;
    }

    /**
     * Format Tanggal Indonesia (contoh: 25 Januari 2026)
     */
    public static function formatTanggalIndo(?string $dateString, bool $withDay = false): string
    {
        if (empty($dateString) || $dateString === '0000-00-00') {
            return '-';
        }

        $timestamp = strtotime($dateString);
        if (!$timestamp) {
            return $dateString;
        }

        $bulanIndo = [
            1  => 'Januari',
            2  => 'Februari',
            3  => 'Maret',
            4  => 'April',
            5  => 'Mei',
            6  => 'Juni',
            7  => 'Juli',
            8  => 'Agustus',
            9  => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $hariIndo = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
        ];

        $dayName = $hariIndo[date('l', $timestamp)] ?? '';
        $day = date('d', $timestamp);
        $month = $bulanIndo[(int) date('m', $timestamp)] ?? '';
        $year = date('Y', $timestamp);

        $formatted = (int) $day . ' ' . $month . ' ' . $year;
        return $withDay ? $dayName . ', ' . $formatted : $formatted;
    }

    /**
     * Format Rentang Periode Tanggal (contoh: 12 s/d 15 Februari 2026)
     */
    public static function formatPeriode(?string $startDate, ?string $endDate): string
    {
        if (empty($startDate) && empty($endDate)) {
            return '-';
        }
        if (!empty($startDate) && empty($endDate)) {
            return self::formatTanggalIndo($startDate);
        }
        if (empty($startDate) && !empty($endDate)) {
            return self::formatTanggalIndo($endDate);
        }

        $t1 = strtotime($startDate);
        $t2 = strtotime($endDate);

        if (!$t1 || !$t2) {
            return $startDate . ' s/d ' . $endDate;
        }

        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $d1 = (int) date('d', $t1);
        $m1 = (int) date('m', $t1);
        $y1 = date('Y', $t1);

        $d2 = (int) date('d', $t2);
        $m2 = (int) date('m', $t2);
        $y2 = date('Y', $t2);

        if ($y1 === $y2 && $m1 === $m2 && $d1 === $d2) {
            return $d1 . ' ' . $bulanIndo[$m1] . ' ' . $y1;
        }

        if ($y1 === $y2 && $m1 === $m2) {
            return $d1 . ' s/d ' . $d2 . ' ' . $bulanIndo[$m1] . ' ' . $y1;
        }

        if ($y1 === $y2) {
            return $d1 . ' ' . $bulanIndo[$m1] . ' s/d ' . $d2 . ' ' . $bulanIndo[$m2] . ' ' . $y1;
        }

        return $d1 . ' ' . $bulanIndo[$m1] . ' ' . $y1 . ' s/d ' . $d2 . ' ' . $bulanIndo[$m2] . ' ' . $y2;
    }

    /**
     * Konversi Bulan ke Angka Romawi (contoh: 9 -> IX)
     */
    public static function bulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romawi[$bulan] ?? 'I';
    }
}
