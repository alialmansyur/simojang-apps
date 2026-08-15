<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('asset_url')) {
    function asset_url(string $path): string
    {
        static $cache = [];

        // Hapus query string manual (seperti ?v=99) agar pengecekan file berhasil
        $parsedUrl = parse_url($path);
        $cleanPath = $parsedUrl['path'] ?? $path;
        
        $normalizedPath = ltrim($cleanPath, '/');

        if (isset($cache[$normalizedPath])) {
            return $cache[$normalizedPath];
        }

        $absolutePath = rtrim(FCPATH, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPath);

        // Jika file ada, gunakan waktu modifikasi terakhir sebagai versi
        $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';

        // Kembalikan URL bersih ditambah auto-versioning
        return $cache[$normalizedPath] = base_url($normalizedPath) . '?v=' . rawurlencode($version);
    }
}
