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

    // Proses restart service jika diminta
    if (isset($_POST['restart_service']) && !empty($_POST['service_name'])) {
        $service_name = $_POST['service_name'];
        $allowed_services = ['laravel-frankenphp-staging', 'laravel-frankenphp-production'];

        if (in_array($service_name, $allowed_services)) {
            try {
                // Gunakan path absolut dan tambahkan sudo
                $command = "sudo /bin/systemctl restart " . escapeshellarg($service_name) . " 2>&1";
                $output = shell_exec($command);
                $logger->info('Service di-restart', ['service' => $service_name, 'output' => $output]);
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
                // Gunakan path absolut dan tambahkan sudo
                $command = "sudo /bin/systemctl stop " . escapeshellarg($service_name) . " 2>&1";
                $output = shell_exec($command);
                $logger->info('Service dihentikan', ['service' => $service_name, 'output' => $output]);
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
                // Gunakan path absolut dan tambahkan sudo
                $command = "sudo /bin/systemctl start " . escapeshellarg($service_name) . " 2>&1";
                $output = shell_exec($command);
                $logger->info('Service dimulai', ['service' => $service_name, 'output' => $output]);
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

    // Fungsi untuk mendapatkan semua port yang terbuka
    function getOpenPorts()
    {
        try {
            // Menggunakan netstat untuk mendapatkan daftar port yang terbuka
            // -t: tcp, -u: udp, -l: listening, -n: numeric, -p: program/PID
            $command = "netstat -tuln | grep LISTEN | awk '{print \$4}' | awk -F: '{print \$NF}' | sort -n";
            $output = shell_exec($command);

            if ($output) {
                // Parse output untuk mendapatkan daftar port
                $ports = array_filter(explode("\n", $output), 'strlen');
                return array_unique($ports); // Hapus duplikat
            }

            return [];
        } catch (Exception $e) {
            // Jika terjadi error, kembalikan array kosong
            return [];
        }
    }

    // Fungsi untuk memeriksa status port
    function checkPort($port)
    {
        $connection = @fsockopen('127.0.0.1', $port, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }

    // Fungsi untuk mendapatkan proses yang menggunakan port tertentu
    function getProcessByPort($port)
    {
        try {
            $command = "lsof -i :$port -n -P | grep LISTEN";
            $output = shell_exec($command);

            if ($output) {
                // Parse output untuk mendapatkan nama proses
                $lines = explode("\n", trim($output));
                if (!empty($lines)) {
                    // Format output: COMMAND  PID USER  ... 
                    $parts = preg_split('/\s+/', $lines[0]);
                    if (count($parts) >= 2) {
                        return [
                            'name' => $parts[0],
                            'pid' => $parts[1]
                        ];
                    }
                }
            }

            return ['name' => 'Unknown', 'pid' => 'N/A'];
        } catch (Exception $e) {
            return ['name' => 'Error', 'pid' => 'N/A'];
        }
    }

    // Fungsi untuk mendapatkan status systemctl
    function getSystemctlStatus($service)
    {
        try {
            // Gunakan path absolut dan tambahkan sudo
            $command = "sudo /bin/systemctl status " . escapeshellarg($service) . " 2>&1";
            $output = shell_exec($command);

            // Debugging: log output raw jika kosong
            if (empty($output)) {
                error_log("getSystemctlStatus output kosong untuk service: $service");
                // Coba alternatif tanpa sudo
                $command_alt = "/bin/systemctl status " . escapeshellarg($service) . " 2>&1";
                $output = shell_exec($command_alt);
                if (empty($output)) {
                    error_log("Alternatif juga gagal untuk service: $service");
                }
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

    // Cek status port
    $port8111Status = checkPort(8111);
    $port8112Status = checkPort(8112);

    // Cek status layanan Laravel FrankenPHP
    $stagingStatus = getSystemctlStatus('laravel-frankenphp-staging');
    $productionStatus = getSystemctlStatus('laravel-frankenphp-production');

    // Dapatkan semua port yang terbuka
    $openPorts = getOpenPorts();
    // Informasi proses untuk port spesifik
    $port8111Process = $port8111Status ? getProcessByPort(8111) : null;
    $port8112Process = $port8112Status ? getProcessByPort(8112) : null;

    // Ambil jumlah port yang terbuka
    $openPortCount = count($openPorts);

    // Ambil data layanan dummy
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
    <style>
        /* Style tambahan untuk bagian monitoring port dan service */
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-active {
            background-color: #10b981;
        }

        .status-inactive {
            background-color: #dc2626;
        }

        .status-table {
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .status-table th {
            text-align: left;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .status-table td {
            padding: 12px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .restart-form {
            display: inline;
        }

        .restart-btn {
            background-color: #f59e0b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .restart-btn:hover {
            background-color: #d97706;
        }

        .stop-btn {
            background-color: #ef4444;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 4px;
        }

        .stop-btn:hover {
            background-color: #dc2626;
        }

        .start-btn {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-right: 4px;
        }

        .start-btn:hover {
            background-color: #059669;
        }

        .action-btns {
            white-space: nowrap;
        }

        .status-section {
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .status-section h3 {
            margin-top: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 10px;
        }

        .message-success {
            background-color: rgba(16, 185, 129, 0.2);
            color: #ecfdf5;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .message-error {
            background-color: rgba(220, 38, 38, 0.2);
            color: #fef2f2;
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .ports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            grid-gap: 10px;
            margin-top: 15px;
        }

        .port-tile {
            background-color: rgba(79, 70, 229, 0.1);
            border: 1px solid rgba(79, 70, 229, 0.3);
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }

        .small-text {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .expander {
            background: none;
            border: none;
            color: #4f46e5;
            cursor: pointer;
            padding: 5px;
            text-decoration: underline;
        }
    </style>
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
        <?php if (isset($success_message)): ?>
            <div class="message-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="message-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="status-section" style="margin-bottom: 20px;">
            <h3><i class="fas fa-info-circle mr-2"></i> Manajemen Service Laravel FrankenPHP</h3>
            <p>Dashboard ini memungkinkan Anda memantau dan mengelola service Laravel FrankenPHP untuk lingkungan staging dan production.</p>
            <div style="margin-top: 10px;">
                <span class="badge badge-success" style="margin-right: 15px;"><i class="fas fa-play"></i> Start: Memulai service yang sedang tidak aktif</span>
                <span class="badge badge-danger" style="margin-right: 15px;"><i class="fas fa-stop"></i> Stop: Menghentikan service yang sedang berjalan</span>
                <span class="badge badge-warning"><i class="fas fa-sync-alt"></i> Restart: Memulai ulang service tanpa menghilangkan konfigurasi</span>
            </div>
        </div>

        <div class="card-grid">
            <!-- Card 1 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Port Terbuka</h3>
                    <div class="card-icon icon-green">
                        <i class="fas fa-plug"></i>
                    </div>
                </div>
                <p class="card-value"><?php echo $openPortCount; ?></p>
                <p class="card-subtitle">Total port aktif</p>
            </div>

            <!-- Card 2 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Service Aktif</h3>
                    <div class="card-icon icon-blue">
                        <i class="fas fa-server"></i>
                    </div>
                </div>
                <p class="card-value"><?php echo ($stagingStatus['active'] ? '1' : '0') + ($productionStatus['active'] ? '1' : '0'); ?>/2</p>
                <p class="card-subtitle">Laravel FrankenPHP</p>
            </div>

            <!-- Card 3 -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Status Sistem</h3>
                    <div class="card-icon icon-purple">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
                <p class="card-value"><?php echo (($port8111Status && $port8112Status && $stagingStatus['active'] && $productionStatus['active']) ? 'Normal' : 'Perhatian'); ?></p>
                <p class="card-subtitle"><?php echo date('d M Y H:i'); ?></p>
            </div>
        </div>

        <!-- All Open Ports Section -->
        <div class="status-section">
            <h3><i class="fas fa-network-wired mr-2"></i> Semua Port Terbuka</h3>

            <div class="ports-grid" id="ports-grid">
                <?php if (!empty($openPorts)): ?>
                    <?php foreach (array_slice($openPorts, 0, 20) as $port): ?>
                        <div class="port-tile">
                            <div><strong><?php echo htmlspecialchars($port); ?></strong></div>
                            <?php
                            if ($port == 8111 || $port == 8112) {
                                $processInfo = $port == 8111 ? $port8111Process : $port8112Process;
                                if ($processInfo) {
                                    echo '<div class="small-text">' . htmlspecialchars($processInfo['name']) . '</div>';
                                }
                            }
                            ?>
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($openPorts) > 20): ?>
                        <button class="expander" id="show-more-ports">
                            Tampilkan <?php echo count($openPorts) - 20; ?> port lainnya...
                        </button>
                    <?php endif; ?>
                <?php else: ?>
                    <p>Tidak ada port yang terbuka atau tidak dapat membaca informasi port.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Port Status Section -->
        <div class="status-section">
            <h3><i class="fas fa-network-wired mr-2"></i> Status Port Monitoring</h3>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Port</th>
                        <th>Status</th>
                        <th>Proses</th>
                        <th>PID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>8111</td>
                        <td>
                            <span class="status-indicator <?php echo $port8111Status ? 'status-active' : 'status-inactive'; ?>"></span>
                            <?php echo $port8111Status ? 'Terhubung' : 'Tidak Terhubung'; ?>
                        </td>
                        <td><?php echo $port8111Status && $port8111Process ? htmlspecialchars($port8111Process['name']) : '-'; ?></td>
                        <td><?php echo $port8111Status && $port8111Process ? htmlspecialchars($port8111Process['pid']) : '-'; ?></td>
                    </tr>
                    <tr>
                        <td>8112</td>
                        <td>
                            <span class="status-indicator <?php echo $port8112Status ? 'status-active' : 'status-inactive'; ?>"></span>
                            <?php echo $port8112Status ? 'Terhubung' : 'Tidak Terhubung'; ?>
                        </td>
                        <td><?php echo $port8112Status && $port8112Process ? htmlspecialchars($port8112Process['name']) : '-'; ?></td>
                        <td><?php echo $port8112Status && $port8112Process ? htmlspecialchars($port8112Process['pid']) : '-'; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Service Status Section -->
        <div class="status-section">
            <h3><i class="fas fa-cogs mr-2"></i> Status Service Laravel FrankenPHP</h3>
            <table class="status-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Uptime</th>
                        <th>Detail</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Laravel FrankenPHP Staging</td>
                        <td>
                            <span class="status-indicator <?php echo $stagingStatus['active'] ? 'status-active' : 'status-inactive'; ?>"></span>
                            <?php echo ucfirst($stagingStatus['status']); ?>
                        </td>
                        <td><?php echo $stagingStatus['uptime'] ?: '-'; ?></td>
                        <td class="small-text">
                            <?php
                            if ($stagingStatus['active']) {
                                echo htmlspecialchars($stagingStatus['description']);
                                echo '<br>Port: 8111';
                                echo $port8111Status ? ' <span class="badge badge-success">Terhubung</span>' : ' <span class="badge badge-danger">Tidak Terhubung</span>';
                            } else {
                                echo 'Service tidak aktif';
                            }
                            ?>
                        </td>
                        <td class="action-btns">
                            <?php if ($stagingStatus['active']): ?>
                                <form method="post" action="" class="restart-form">
                                    <input type="hidden" name="service_name" value="laravel-frankenphp-staging">
                                    <button type="submit" name="stop_service" class="stop-btn" title="Hentikan service">
                                        <i class="fas fa-stop"></i> Stop
                                    </button>
                                </form>
                                <form method="post" action="" class="restart-form">
                                    <input type="hidden" name="service_name" value="laravel-frankenphp-staging">
                                    <button type="submit" name="restart_service" class="restart-btn" title="Restart service">
                                        <i class="fas fa-sync-alt"></i> Restart
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="" class="restart-form">
                                    <input type="hidden" name="service_name" value="laravel-frankenphp-staging">
                                    <button type="submit" name="start_service" class="start-btn" title="Mulai service">
                                        <i class="fas fa-play"></i> Start
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Laravel FrankenPHP Production</td>
                        <td>
                            <span class="status-indicator <?php echo $productionStatus['active'] ? 'status-active' : 'status-inactive'; ?>"></span>
                            <?php echo ucfirst($productionStatus['status']); ?>
                        </td>
                        <td><?php echo $productionStatus['uptime'] ?: '-'; ?></td>
                        <td class="small-text">
                            <?php
                            if ($productionStatus['active']) {
                                echo htmlspecialchars($productionStatus['description']);
                                echo '<br>Port: 8112';
                                echo $port8112Status ? ' <span class="badge badge-success">Terhubung</span>' : ' <span class="badge badge-danger">Tidak Terhubung</span>';
                            } else {
                                echo 'Service tidak aktif';
                            }
                            ?>
                        </td>
                        <td class="action-btns">
                            <?php if ($productionStatus['active']): ?>
                                <form method="post" action="" class="restart-form">
                                    <input type="hidden" name="service_name" value="laravel-frankenphp-production">
                                    <button type="submit" name="stop_service" class="stop-btn" title="Hentikan service">
                                        <i class="fas fa-stop"></i> Stop
                                    </button>
                                </form>
                                <form method="post" action="" class="restart-form">
                                    <input type="hidden" name="service_name" value="laravel-frankenphp-production">
                                    <button type="submit" name="restart_service" class="restart-btn" title="Restart service">
                                        <i class="fas fa-sync-alt"></i> Restart
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post" action="" class="restart-form">
                                    <input type="hidden" name="service_name" value="laravel-frankenphp-production">
                                    <button type="submit" name="start_service" class="start-btn" title="Mulai service">
                                        <i class="fas fa-play"></i> Start
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
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
                <button id="refresh-status" class="action-btn btn-indigo" onclick="window.location.reload()">
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
    <script>
        // JavaScript untuk menampilkan lebih banyak port
        document.addEventListener('DOMContentLoaded', function() {
            // Konfirmasi sebelum melakukan tindakan pada service
            const actionForms = document.querySelectorAll('.restart-form');
            actionForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    let serviceName = this.querySelector('input[name="service_name"]').value;
                    let actionType = '';

                    if (this.querySelector('button[name="restart_service"]')) {
                        actionType = 'restart';
                    } else if (this.querySelector('button[name="stop_service"]')) {
                        actionType = 'stop';
                    } else if (this.querySelector('button[name="start_service"]')) {
                        actionType = 'start';
                    }

                    let actionText = {
                        'restart': 'me-restart',
                        'stop': 'menghentikan',
                        'start': 'memulai'
                    };

                    let confirmMessage = `Apakah Anda yakin ingin ${actionText[actionType]} service ${serviceName}?`;

                    if (confirm(confirmMessage)) {
                        this.submit();
                    }
                });
            });

            const showMoreButton = document.getElementById('show-more-ports');
            if (showMoreButton) {
                showMoreButton.addEventListener('click', function() {
                    // Mengubah teks tombol menjadi loading
                    showMoreButton.textContent = 'Memuat...';
                    showMoreButton.disabled = true;

                    // Memuat semua port dengan AJAX
                    fetch('get_all_ports.php')
                        .then(response => response.json())
                        .then(data => {
                            const portsGrid = document.getElementById('ports-grid');

                            // Hapus semua konten kecuali tombol show-more
                            while (portsGrid.firstChild) {
                                portsGrid.removeChild(portsGrid.firstChild);
                            }

                            // Tampilkan semua port
                            if (data.length > 0) {
                                data.forEach(portInfo => {
                                    const portTile = document.createElement('div');
                                    portTile.className = 'port-tile';

                                    let portContent = `<div><strong>${portInfo.port}</strong></div>`;

                                    // Tambahkan info proses jika tersedia
                                    if (portInfo.process) {
                                        portContent += `<div class="small-text">${portInfo.process}</div>`;
                                        if (portInfo.pid) {
                                            portContent += `<div class="small-text">PID: ${portInfo.pid}</div>`;
                                        }
                                    }

                                    portTile.innerHTML = portContent;
                                    portsGrid.appendChild(portTile);
                                });
                            } else {
                                const noPortsMsg = document.createElement('p');
                                noPortsMsg.textContent = 'Tidak ada port yang terbuka atau tidak dapat membaca informasi port.';
                                portsGrid.appendChild(noPortsMsg);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showMoreButton.textContent = 'Gagal memuat data';
                        });
                });
            }
        });
    </script>
</body>

</html>