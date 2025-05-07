<?php

/**
 * File Interface untuk menjalankan perintah systemctl
 * File ini berkomunikasi dengan script command_interface.sh yang berjalan sebagai user alwyzon
 */

// Direktori untuk komunikasi
define('COMMAND_DIR', '/tmp/service_commands');
define('RESULT_DIR', '/tmp/service_results');

// Pastikan direktori ada
if (!is_dir(COMMAND_DIR)) {
    mkdir(COMMAND_DIR, 0755, true);
}
if (!is_dir(RESULT_DIR)) {
    mkdir(RESULT_DIR, 0755, true);
}

/**
 * Menjalankan perintah systemctl
 * 
 * @param string $action Action (status, start, stop, restart)
 * @param string $service Nama service
 * @param int $timeout Timeout dalam detik
 * @return array ['success' => bool, 'output' => string, 'error' => string]
 */
function run_systemctl_command($action, $service, $timeout = 30)
{
    // Validasi input
    $allowed_actions = ['status', 'start', 'stop', 'restart'];
    $allowed_services = ['laravel-frankenphp-staging', 'laravel-frankenphp-production'];

    if (!in_array($action, $allowed_actions)) {
        return ['success' => false, 'error' => 'Action tidak diizinkan', 'output' => ''];
    }

    if (!in_array($service, $allowed_services)) {
        return ['success' => false, 'error' => 'Service tidak diizinkan', 'output' => ''];
    }

    // Buat ID file unik
    $file_id = uniqid('cmd_') . '.txt';
    $command_file = COMMAND_DIR . '/' . $file_id;
    $result_file = RESULT_DIR . '/' . $file_id;

    // Tulis perintah ke file
    file_put_contents($command_file, $action . "\n" . $service);
    chmod($command_file, 0644);

    // Tunggu hasil
    $start_time = time();
    while (!file_exists($result_file) && (time() - $start_time < $timeout)) {
        usleep(100000); // 0.1 detik
    }

    // Cek hasil
    if (file_exists($result_file)) {
        $output = file_get_contents($result_file);
        unlink($result_file); // Bersihkan file hasil
        return ['success' => true, 'output' => $output, 'error' => ''];
    } else {
        // Bersihkan file perintah jika timeout
        if (file_exists($command_file)) {
            unlink($command_file);
        }
        return ['success' => false, 'error' => 'Timeout menunggu hasil perintah', 'output' => ''];
    }
}

/**
 * Contoh penggunaan:
 * 
 * $result = run_systemctl_command('status', 'laravel-frankenphp-staging');
 * if ($result['success']) {
 *     echo $result['output'];
 * } else {
 *     echo "Error: " . $result['error'];
 * }
 */
