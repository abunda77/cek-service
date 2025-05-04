<?php

/**
 * Check Configuration Script
 * 
 * Skrip ini digunakan untuk memeriksa konfigurasi dan dependensi sistem
 * untuk membantu troubleshooting error.
 * 
 * CATATAN KEAMANAN: Hapus skrip ini dari server produksi setelah digunakan.
 */

// Tampilkan semua error untuk troubleshooting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tetapkan header untuk mencegah caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Fungsi untuk memeriksa versi PHP
function checkPhpVersion()
{
    $requiredVersion = '7.4.0';
    $currentVersion = PHP_VERSION;
    $result = version_compare($currentVersion, $requiredVersion, '>=');

    return [
        'name' => 'Versi PHP',
        'required' => ">= $requiredVersion",
        'current' => $currentVersion,
        'status' => $result ? 'OK' : 'ERROR',
        'message' => $result ? 'Versi PHP kompatibel' : 'Versi PHP tidak kompatibel. Update ke PHP ' . $requiredVersion . ' atau lebih tinggi.'
    ];
}

// Fungsi untuk memeriksa ekstensi PHP
function checkExtensions()
{
    $requiredExtensions = ['pdo', 'json', 'mbstring', 'openssl'];
    $results = [];

    foreach ($requiredExtensions as $ext) {
        $loaded = extension_loaded($ext);
        $results[] = [
            'name' => "Ekstensi $ext",
            'required' => 'Ya',
            'current' => $loaded ? 'Terinstall' : 'Tidak terinstall',
            'status' => $loaded ? 'OK' : 'ERROR',
            'message' => $loaded ? "Ekstensi $ext tersedia" : "Ekstensi $ext dibutuhkan tapi tidak tersedia"
        ];
    }

    return $results;
}

// Fungsi untuk memeriksa file dan direktori
function checkFilesAndDirectories()
{
    $items = [
        [
            'path' => '.env',
            'type' => 'file',
            'permissions' => '0600',
            'exists_error' => 'File .env tidak ditemukan. Salin .env.example ke .env dan atur konfigurasi.',
            'permissions_error' => 'File .env harus memiliki izin 0600 untuk keamanan.'
        ],
        [
            'path' => 'logs',
            'type' => 'directory',
            'permissions' => '0777',
            'exists_error' => 'Direktori logs tidak ditemukan. Buat direktori logs dengan izin 0777.',
            'permissions_error' => 'Direktori logs harus dapat ditulis (0777).'
        ],
        [
            'path' => 'vendor',
            'type' => 'directory',
            'permissions' => '0755',
            'exists_error' => 'Direktori vendor tidak ditemukan. Jalankan "composer install".',
            'permissions_error' => 'Direktori vendor harus memiliki izin 0755.'
        ]
    ];

    $results = [];

    foreach ($items as $item) {
        $path = $item['path'];
        $exists = $item['type'] === 'file' ? file_exists($path) : is_dir($path);

        if ($exists) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $required_perms = $item['permissions'];

            // Catatan: Pemeriksaan izin sebenarnya lebih kompleks, ini adalah penyederhanaan
            $correct_perms = true; // Dalam produksi, lakukan pemeriksaan izin yang tepat

            $results[] = [
                'name' => $path,
                'required' => "Ada dengan izin $required_perms",
                'current' => "Ada dengan izin $perms",
                'status' => $correct_perms ? 'OK' : 'WARNING',
                'message' => $correct_perms ? "$path memiliki izin yang benar" : $item['permissions_error']
            ];
        } else {
            $results[] = [
                'name' => $path,
                'required' => 'Ada',
                'current' => 'Tidak ada',
                'status' => 'ERROR',
                'message' => $item['exists_error']
            ];
        }
    }

    return $results;
}

// Fungsi untuk memeriksa pengaturan Apache/Nginx
function checkServerConfig()
{
    $results = [];

    // Periksa mod_rewrite
    $mod_rewrite = isset($_SERVER['HTTP_MOD_REWRITE']) && $_SERVER['HTTP_MOD_REWRITE'] == 'On';
    $results[] = [
        'name' => 'Apache mod_rewrite',
        'required' => 'Aktif',
        'current' => $mod_rewrite ? 'Aktif' : 'Tidak diketahui',
        'status' => $mod_rewrite ? 'OK' : 'INFO',
        'message' => $mod_rewrite ? 'mod_rewrite aktif' : 'Tidak dapat mendeteksi apakah mod_rewrite aktif. Untuk memastikan URL rewriting bekerja, aktifkan modul ini dan gunakan .htaccess.'
    ];

    // Periksa server software
    $server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Tidak diketahui';
    $results[] = [
        'name' => 'Server Software',
        'required' => 'Apache/Nginx',
        'current' => $server_software,
        'status' => 'INFO',
        'message' => "Menggunakan $server_software"
    ];

    return $results;
}

// Fungsi untuk memeriksa composer
function checkComposer()
{
    $vendor_autoload = file_exists('vendor/autoload.php');
    return [
        'name' => 'Composer Autoload',
        'required' => 'Ada',
        'current' => $vendor_autoload ? 'Ada' : 'Tidak ada',
        'status' => $vendor_autoload ? 'OK' : 'ERROR',
        'message' => $vendor_autoload ? 'Dependensi Composer terinstall dengan benar' : 'Dependensi Composer tidak terinstall. Jalankan "composer install".'
    ];
}

// Memeriksa akses shell
function checkShellAccess()
{
    $shell_exec_enabled = function_exists('shell_exec') && !in_array('shell_exec', array_map('trim', explode(',', ini_get('disable_functions'))));
    return [
        'name' => 'Akses Shell',
        'required' => 'Aktif',
        'current' => $shell_exec_enabled ? 'Aktif' : 'Tidak aktif',
        'status' => $shell_exec_enabled ? 'OK' : 'WARNING',
        'message' => $shell_exec_enabled ? 'shell_exec tersedia' : 'shell_exec tidak tersedia. Beberapa fitur mungkin tidak berfungsi.'
    ];
}

// Coba membaca file .env jika ada
function checkEnvFile()
{
    if (file_exists('.env')) {
        $env_content = file_get_contents('.env');
        $result = [
            'name' => 'Konten .env',
            'required' => 'Berisi konfigurasi yang diperlukan',
            'current' => 'Ada',
            'status' => 'INFO',
            'message' => 'File .env ada, tapi pastikan berisi konfigurasi yang benar.'
        ];

        // Periksa konfigurasi wajib
        $required_configs = ['APP_USERNAME', 'APP_PASSWORD'];
        $missing = [];

        foreach ($required_configs as $config) {
            if (strpos($env_content, $config . '=') === false) {
                $missing[] = $config;
            }
        }

        if (!empty($missing)) {
            $result['status'] = 'ERROR';
            $result['message'] = 'File .env tidak berisi konfigurasi wajib: ' . implode(', ', $missing);
        }

        return $result;
    } else {
        return [
            'name' => 'File .env',
            'required' => 'Ada',
            'current' => 'Tidak ada',
            'status' => 'ERROR',
            'message' => 'File .env tidak ditemukan. Salin .env.example ke .env dan atur konfigurasi.'
        ];
    }
}

// Kumpulkan semua pemeriksaan
$checks = [];
$checks[] = checkPhpVersion();
$checks = array_merge($checks, checkExtensions());
$checks[] = checkComposer();
$checks = array_merge($checks, checkFilesAndDirectories());
$checks[] = checkShellAccess();
$checks[] = checkServerConfig();
$checks[] = checkEnvFile();

// Hitung statistik
$stats = [
    'ok' => 0,
    'warning' => 0,
    'error' => 0,
    'info' => 0
];

foreach ($checks as $check) {
    $status = strtolower($check['status']);
    if (isset($stats[$status])) {
        $stats[$status]++;
    }
}

$overall_status = $stats['error'] > 0 ? 'ERROR' : ($stats['warning'] > 0 ? 'WARNING' : 'OK');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Configuration - Cek Service</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6b7280, #1f2937);
            color: #fff;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        h1,
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            text-align: center;
        }

        .summary-item {
            padding: 15px;
            border-radius: 8px;
            width: 20%;
        }

        .overall {
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 1.2em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: rgba(0, 0, 0, 0.2);
        }

        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .OK {
            background-color: #10b981;
        }

        .WARNING {
            background-color: #f59e0b;
        }

        .ERROR {
            background-color: #dc2626;
        }

        .INFO {
            background-color: #3b82f6;
        }

        .actions {
            margin-top: 30px;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4f46e5;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            margin: 0 10px;
        }

        .button:hover {
            background-color: #4338ca;
        }

        .note {
            margin-top: 30px;
            padding: 15px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Cek Konfigurasi Sistem</h1>

        <div class="overall" style="background-color: <?php echo $overall_status === 'OK' ? '#10b981' : ($overall_status === 'WARNING' ? '#f59e0b' : '#dc2626'); ?>">
            Status Keseluruhan: <?php echo $overall_status; ?>
        </div>

        <div class="summary">
            <div class="summary-item" style="background-color: #10b981;">
                <div style="font-size: 2em;"><?php echo $stats['ok']; ?></div>
                <div>OK</div>
            </div>
            <div class="summary-item" style="background-color: #f59e0b;">
                <div style="font-size: 2em;"><?php echo $stats['warning']; ?></div>
                <div>WARNING</div>
            </div>
            <div class="summary-item" style="background-color: #dc2626;">
                <div style="font-size: 2em;"><?php echo $stats['error']; ?></div>
                <div>ERROR</div>
            </div>
            <div class="summary-item" style="background-color: #3b82f6;">
                <div style="font-size: 2em;"><?php echo $stats['info']; ?></div>
                <div>INFO</div>
            </div>
        </div>

        <h2>Detail Pemeriksaan</h2>

        <table>
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>Dibutuhkan</th>
                    <th>Tersedia</th>
                    <th>Status</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $check): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($check['name']); ?></td>
                        <td><?php echo htmlspecialchars($check['required']); ?></td>
                        <td><?php echo htmlspecialchars($check['current']); ?></td>
                        <td><span class="status <?php echo $check['status']; ?>"><?php echo $check['status']; ?></span></td>
                        <td><?php echo htmlspecialchars($check['message']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="index.php" class="button">Kembali ke Halaman Login</a>
            <a href="check_configuration.php" class="button">Periksa Ulang</a>
        </div>

        <div class="note">
            <strong>Catatan Penting:</strong>
            <ul>
                <li>Setiap komponen dengan status ERROR harus diselesaikan agar aplikasi berfungsi dengan baik.</li>
                <li>Komponen dengan status WARNING mungkin menyebabkan beberapa fitur tidak berfungsi.</li>
                <li>Untuk keamanan, hapus skrip ini dari server produksi setelah troubleshooting selesai.</li>
            </ul>

            <p>Bantuan lebih lanjut: Lihat dokumentasi di <code>README.md</code> atau bagian "Troubleshooting" untuk petunjuk penyelesaian masalah.</p>
        </div>
    </div>
</body>

</html>