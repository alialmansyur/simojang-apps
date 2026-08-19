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

        // Ambil clean path dan manual query jika ada
        $parsedUrl = parse_url($path);
        $cleanPath = $parsedUrl['path'] ?? $path;
        $manualQuery = $parsedUrl['query'] ?? null;
        
        $normalizedPath = ltrim($cleanPath, '/');

        if (isset($cache[$normalizedPath])) {
            return $cache[$normalizedPath];
        }

        $absolutePath = rtrim(FCPATH, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPath);

        // Jika file ada di disk, gunakan waktu modifikasi terakhir (filemtime) untuk cache busting otomatis
        $fileMtime = is_file($absolutePath) ? filemtime($absolutePath) : null;
        
        if ($fileMtime !== null) {
            $version = (string) $fileMtime;
            if (!empty($manualQuery)) {
                parse_str($manualQuery, $queryParams);
                if (!empty($queryParams['v'])) {
                    $version .= '.' . rawurlencode((string) $queryParams['v']);
                }
            }
        } else {
            $version = (string) time();
            if (!empty($manualQuery)) {
                parse_str($manualQuery, $queryParams);
                if (!empty($queryParams['v'])) {
                    $version = rawurlencode((string) $queryParams['v']);
                }
            }
        }

        // Kembalikan URL bersih ditambah auto-versioning
        return $cache[$normalizedPath] = base_url($normalizedPath) . '?v=' . $version;
    }
}
