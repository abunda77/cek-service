<?php
session_start();

// Memuat library phpdotenv
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Cek apakah user sudah login
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Proses logout
if (isset($_GET['logout'])) {
    // Hapus semua data session
    session_unset();
    session_destroy();

    // Redirect ke halaman login
    header('Location: index.php');
    exit;
}

// Username dari session
$username = $_SESSION['username'] ?? 'Pengguna';
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
                        <tr>
                            <td>API Gateway</td>
                            <td><span class="badge badge-success">Online</span></td>
                            <td>99.9%</td>
                            <td>245</td>
                        </tr>
                        <tr>
                            <td>Authentication Service</td>
                            <td><span class="badge badge-success">Online</span></td>
                            <td>99.8%</td>
                            <td>187</td>
                        </tr>
                        <tr>
                            <td>Payment Gateway</td>
                            <td><span class="badge badge-success">Online</span></td>
                            <td>99.7%</td>
                            <td>156</td>
                        </tr>
                        <tr>
                            <td>Database Cluster</td>
                            <td><span class="badge badge-success">Online</span></td>
                            <td>99.9%</td>
                            <td>320</td>
                        </tr>
                        <tr>
                            <td>File Storage</td>
                            <td><span class="badge badge-warning">Maintenance</span></td>
                            <td>97.2%</td>
                            <td>68</td>
                        </tr>
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