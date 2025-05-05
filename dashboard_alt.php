<?php
// Catatan: File ini adalah versi alternatif dari dashboard.php yang menggunakan
// script wrapper service_control.sh. Gunakan ini jika metode lain gagal.

// Muat error handler terlebih dahulu
require_once 'includes/error_handler.php';

// Mulai session
session_start();

// Logger untuk halaman dashboard
$logger = new Logger();
$logger->info('Akses halaman dashboard alternatif');

// Path ke script wrapper
$service_script = __DIR__ . "/service_control.sh";

// Pastikan script memiliki izin eksekusi
if (!is_executable($service_script)) {
    chmod($service_script, 0755);
    $logger->info('Mengatur izin eksekusi untuk script service control');
}

try {
    // Memuat library phpdotenv
    require_once 'vendor/autoload.php';

    // Coba muat file .env
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();
        $logger->debug('File .env berhasil dimuat');
    } catch (Exception $e) {
        $logger->error('Gagal memuat file .env: ' . $e->getMessage());
        throw new Exception('Konfigurasi aplikasi tidak ditemukan.');
    }

    // Cek apakah user sudah login
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        $logger->warning('Akses tidak sah ke dashboard', ['ip' => $_SERVER['REMOTE_ADDR']]);
        header('Location: index.php');
        exit;
    }

    // Proses restart service jika diminta
    if (isset($_POST['restart_service']) && !empty($_POST['service_name'])) {
        $service_name = $_POST['service_name'];
        $allowed_services = ['laravel-frankenphp-staging', 'laravel-frankenphp-production'];

        if (in_array($service_name, $allowed_services)) {
            try {
                // Gunakan script wrapper
                $command = "sudo $service_script restart " . escapeshellarg($service_name) . " 2>&1";
                $output = shell_exec($command);
                $logger->info('Service di-restart dengan wrapper', ['service' => $service_name, 'output' => $output]);
                $success_message = "Service $service_name berhasil di-restart.";
            } catch (Exception $e) {
                $logger->error('Gagal me-restart service', ['service' => $service_name, 'error' => $e->getMessage()]);
                $error_message = "Gagal me-restart service $service_name: " . $e->getMessage();
            }
        } else {
            $logger->warning('Percobaan restart service tidak diizinkan', ['service' => $service_name]);
            $error_message = "Service tidak diizinkan untuk di-restart.";
        }
    }

    // Proses stop service jika diminta
    if (isset($_POST['stop_service']) && !empty($_POST['service_name'])) {
        $service_name = $_POST['service_name'];
        $allowed_services = ['laravel-frankenphp-staging', 'laravel-frankenphp-production'];

        if (in_array($service_name, $allowed_services)) {
            try {
                // Gunakan script wrapper
                $command = "sudo $service_script stop " . escapeshellarg($service_name) . " 2>&1";
                $output = shell_exec($command);
                $logger->info('Service dihentikan dengan wrapper', ['service' => $service_name, 'output' => $output]);
                $success_message = "Service $service_name berhasil dihentikan.";
            } catch (Exception $e) {
                $logger->error('Gagal menghentikan service', ['service' => $service_name, 'error' => $e->getMessage()]);
                $error_message = "Gagal menghentikan service $service_name: " . $e->getMessage();
            }
        } else {
            $logger->warning('Percobaan stop service tidak diizinkan', ['service' => $service_name]);
            $error_message = "Service tidak diizinkan untuk dihentikan.";
        }
    }

    // Proses start service jika diminta
    if (isset($_POST['start_service']) && !empty($_POST['service_name'])) {
        $service_name = $_POST['service_name'];
        $allowed_services = ['laravel-frankenphp-staging', 'laravel-frankenphp-production'];

        if (in_array($service_name, $allowed_services)) {
            try {
                // Gunakan script wrapper
                $command = "sudo $service_script start " . escapeshellarg($service_name) . " 2>&1";
                $output = shell_exec($command);
                $logger->info('Service dimulai dengan wrapper', ['service' => $service_name, 'output' => $output]);
                $success_message = "Service $service_name berhasil dimulai.";
            } catch (Exception $e) {
                $logger->error('Gagal memulai service', ['service' => $service_name, 'error' => $e->getMessage()]);
                $error_message = "Gagal memulai service $service_name: " . $e->getMessage();
            }
        } else {
            $logger->warning('Percobaan start service tidak diizinkan', ['service' => $service_name]);
            $error_message = "Service tidak diizinkan untuk dimulai.";
        }
    }

    // Proses logout
    if (isset($_GET['logout'])) {
        $logger->info('Pengguna logout', ['username' => $_SESSION['username'] ?? 'unknown']);
        // Hapus semua data session
        session_unset();
        session_destroy();

        // Redirect ke halaman login
        header('Location: index.php');
        exit;
    }

    // Username dari session
    $username = $_SESSION['username'] ?? 'Pengguna';
    $logger->info('Dashboard diakses oleh pengguna', ['username' => $username]);

    // Fungsi untuk mendapatkan status systemctl via wrapper script
    function getSystemctlStatus($service)
    {
        global $service_script;

        try {
            // Gunakan script wrapper
            $command = "sudo $service_script status " . escapeshellarg($service) . " 2>&1";
            $output = shell_exec($command);

            // Debugging: log output raw jika kosong
            if (empty($output)) {
                error_log("getSystemctlStatus output kosong untuk service: $service");
            }

            // Parse output untuk mendapatkan status
            $active = false;
            $status = "unknown";
            $description = "";

            if (strpos($output, 'Active: active (running)') !== false) {
                $active = true;
                $status = "active";
            } elseif (strpos($output, 'Active: inactive') !== false) {
                $status = "inactive";
            } elseif (strpos($output, 'Active: failed') !== false) {
                $status = "failed";
            }

            // Ekstrak informasi tambahan
            preg_match('/Description: (.+)$/m', $output, $descMatches);
            if (isset($descMatches[1])) {
                $description = trim($descMatches[1]);
            }

            // Ekstrak waktu uptime
            $uptime = "";
            if (preg_match('/Active: active \(running\) since (.+);/U', $output, $uptimeMatches)) {
                $uptime = trim($uptimeMatches[1]);
            }

            return [
                'active' => $active,
                'status' => $status,
                'description' => $description,
                'uptime' => $uptime,
                'raw_output' => $output
            ];
        } catch (Exception $e) {
            error_log("Error in getSystemctlStatus: " . $e->getMessage());
            return [
                'active' => false,
                'status' => 'error',
                'description' => $e->getMessage(),
                'uptime' => '',
                'raw_output' => ''
            ];
        }
    }

    // Fungsi untuk memeriksa status port spesifik
    function checkPort($port)
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }

    // Cek status port
    $port8111Status = checkPort(8111);
    $port8112Status = checkPort(8112);

    // Cek status layanan Laravel FrankenPHP
    $stagingStatus = getSystemctlStatus('laravel-frankenphp-staging');
    $productionStatus = getSystemctlStatus('laravel-frankenphp-production');

    // Ambil data layanan dummy untuk contoh
    $services = [
        ['name' => 'API Gateway', 'status' => 'online', 'uptime' => '99.9%', 'requests' => '245'],
        ['name' => 'Authentication Service', 'status' => 'online', 'uptime' => '99.8%', 'requests' => '187'],
        ['name' => 'Database Cluster', 'status' => 'online', 'uptime' => '99.9%', 'requests' => '320']
    ];
} catch (Exception $e) {
    $logger->critical('Error pada halaman dashboard: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
    ]);
    $errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi nanti atau hubungi administrator.';
}

// Sisanya sama dengan dashboard.php, hanya bagian PHP yang berubah
// Anda bisa menyalin HTML dan JavaScript dari dashboard.php yang asli
?>

<div style="text-align: center; padding: 20px; background-color: #f3f4f6; margin: 20px; border-radius: 8px;">
    <h1>Dashboard Alternatif</h1>
    <p>Ini adalah versi alternatif dari dashboard yang menggunakan script wrapper untuk systemctl.</p>
    <p>Salin bagian HTML dari dashboard.php asli ke sini untuk melengkapi halaman.</p>
    <p>Atau cek file <code>check_systemctl.php</code> untuk diagnosis masalah.</p>
    <p>
        Status Laravel FrankenPHP Staging:
        <strong><?php echo $stagingStatus['active'] ? 'Active' : 'Inactive'; ?></strong>
    </p>
    <p>
        Status Laravel FrankenPHP Production:
        <strong><?php echo $productionStatus['active'] ? 'Active' : 'Inactive'; ?></strong>
    </p>
    <p>
        <a href="check_systemctl.php" style="background: #4f46e5; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px;">
            Jalankan Diagnostik Sistem
        </a>
    </p>
</div>