<?php
// Muat error handler terlebih dahulu
require_once 'includes/error_handler.php';

// Mulai session
session_start();

// Logger untuk halaman dashboard
$logger = new Logger();
$logger->info('Akses halaman dashboard');

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

    // Disini bisa ditambahkan pemanggilan fungsi yang mengambil data aktual
    // dari sistem operasi Linux untuk monitoring port dan service

    // Fungsi untuk mendapatkan status layanan (dummy function)
    function getServiceStatus($logger)
    {
        // Ini adalah contoh data statis, dalam implementasi sebenarnya
        // fungsi ini akan memanggil perintah sistem seperti 'systemctl'
        try {
            // Contoh untuk implementasi sebenarnya:
            // $output = shell_exec('systemctl status nginx 2>&1');
            // return parseServiceOutput($output);

            return [
                ['name' => 'API Gateway', 'status' => 'online', 'uptime' => '99.9%', 'requests' => '245'],
                ['name' => 'Authentication Service', 'status' => 'online', 'uptime' => '99.8%', 'requests' => '187'],
                ['name' => 'Payment Gateway', 'status' => 'online', 'uptime' => '99.7%', 'requests' => '156'],
                ['name' => 'Database Cluster', 'status' => 'online', 'uptime' => '99.9%', 'requests' => '320'],
                ['name' => 'File Storage', 'status' => 'maintenance', 'uptime' => '97.2%', 'requests' => '68']
            ];
        } catch (Exception $e) {
            $logger->error('Gagal mendapatkan status layanan: ' . $e->getMessage());
            return [];
        }
    }

    // Ambil data layanan
    $services = getServiceStatus($logger);
} catch (Exception $e) {
    $logger->critical('Error pada halaman dashboard: ' . $e->getMessage(), [
        'trace' => $e->getTraceAsString()
    ]);
    $errorMessage = 'Terjadi kesalahan sistem. Silakan coba lagi nanti atau hubungi administrator.';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - System Dashboard</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>

<body>
    <!-- Header/Navbar -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="brand">System Dashboard</div>
                <div class="user-info">
                    <span class="user-greeting">
                        <i class="fas fa-user mr-2"></i>Selamat datang, <?php echo htmlspecialchars($username); ?>
                    </span>
                    <a href="?logout=1" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($errorMessage)): ?>
        <!-- Error Message -->
        <div class="container" style="padding-top: 2rem;">
            <div class="login-error">
                <p><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container" style="padding-top: 2rem;">
        <div class="card-grid">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Layanan Aktif</h3>
                    <div class="card-icon icon-green">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
                <p class="card-value">8</p>
                <p class="card-subtitle">Sedang berjalan normal</p>
            </div>

            <!-- Card 2 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Total Request</h3>
                    <div class="card-icon icon-blue">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                </div>
                <p class="card-value">12,345</p>
                <p class="card-subtitle">Dalam 24 jam terakhir</p>
            </div>

            <!-- Card 3 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Sistem</h3>
                    <div class="card-icon icon-purple">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
                <p class="card-value">Normal</p>
                <p class="card-subtitle">Performa optimal</p>
            </div>
        </div>

        <!-- Service Status Table -->
        <div class="table-container">
            <h2 class="table-title">Status Layanan</h2>
            <div class="overflow-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Layanan</th>
                            <th>Status</th>
                            <th>Uptime</th>
                            <th>Request/Menit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($services) && !empty($services)): ?>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($service['name']); ?></td>
                                    <td>
                                        <?php if ($service['status'] === 'online'): ?>
                                            <span class="badge badge-success">Online</span>
                                        <?php elseif ($service['status'] === 'maintenance'): ?>
                                            <span class="badge badge-warning">Maintenance</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Offline</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($service['uptime']); ?></td>
                                    <td><?php echo htmlspecialchars($service['requests']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Data layanan tidak tersedia</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-container">
            <h2 class="actions-title">Aksi Cepat</h2>
            <div class="actions-grid">
                <button id="refresh-status" class="action-btn btn-indigo">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh Status
                </button>
                <button id="download-report" class="action-btn btn-blue">
                    <i class="fas fa-download mr-2"></i> Download Laporan
                </button>
                <button id="settings-btn" class="action-btn btn-green">
                    <i class="fas fa-cog mr-2"></i> Pengaturan
                </button>
                <button id="notification-btn" class="action-btn btn-purple">
                    <i class="fas fa-bell mr-2"></i> Notifikasi
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> System Dashboard. Semua hak dilindungi.</p>
        </div>
    </footer>

    <!-- Custom JavaScript -->
    <script src="assets/js/dashboard.js"></script>
</body>

</html>