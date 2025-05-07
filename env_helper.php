<?php

/**
 * Helper untuk membaca variabel dari file .env
 */

/**
 * Membaca dan parse file .env
 * 
 * @return array Associative array dari variabel .env
 */
function read_env_file()
{
    static $env_cache = null;

    if ($env_cache !== null) {
        return $env_cache;
    }

    $env_path = __DIR__ . '/.env';
    $env_vars = [];

    if (file_exists($env_path)) {
        $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Lewati komentar
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse variabel
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Hapus quotes jika ada
                if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
                    $value = substr($value, 1, -1);
                }

                $env_vars[$name] = $value;
            }
        }
    }

    $env_cache = $env_vars;
    return $env_vars;
}

/**
 * Mendapatkan nilai variabel dari .env
 * 
 * @param string $key Nama variabel
 * @param mixed $default Nilai default jika variabel tidak ditemukan
 * @return mixed Nilai variabel
 */
function env($key, $default = null)
{
    $env_vars = read_env_file();

    if (isset($env_vars[$key])) {
        $value = $env_vars[$key];

        // Parse true/false
        if (strtolower($value) === 'true') {
            return true;
        } elseif (strtolower($value) === 'false') {
            return false;
        }

        return $value;
    }

    return $default;
}
