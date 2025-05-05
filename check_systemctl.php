<?php
// Nonaktifkan batas waktu eksekusi
set_time_limit(0);

// Tampilkan semua error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fungsi untuk menampilkan header bagian
function printHeader($title)
{
    echo "<h2 style='background-color: #4a5568; color: white; padding: 10px; border-radius: 5px;'>$title</h2>";
}

// Fungsi untuk menampilkan pesan status
function printStatus($message, $status)
{
    $color = ($status === "OK") ? "#10b981" : "#ef4444";
    echo "<div style='margin-bottom: 10px;'>";
    echo "<strong>$message:</strong> ";
    echo "<span style='color: $color; font-weight: bold;'>$status</span>";
    echo "</div>";
}

// Fungsi untuk mencoba menjalankan perintah
function runCommand($command)
{
    echo "<pre style='background-color: #1e293b; color: #e2e8f0; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo "$ $command\n";
    $output = shell_exec("$command 2>&1");
    echo htmlspecialchars($output ?: "Tidak ada output atau perintah gagal dijalankan.");
    echo "</pre>";
}

// Fungsi untuk memeriksa apakah fungsi PHP diaktifkan
function isFunctionEnabled($function_name)
{
    $disabled_functions = explode(',', ini_get('disable_functions'));
    return !in_array($function_name, $disabled_functions);
}

// Tampilkan HTML header
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostik Systemctl</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .info {
            background-color: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        .warning {
            background-color: #fef9c3;
            border-left: 4px solid #eab308;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        .error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 10px 15px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #e5e7eb;
        }

        th,
        td {
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Diagnostik Systemctl untuk PHP</h1>
        <div class="info">
            <p>
                Tool ini akan memeriksa konfigurasi PHP dan sistem Anda untuk mendiagnosis masalah dengan perintah systemctl.
                Informasi sensitif mungkin ditampilkan - gunakan dengan hati-hati dan hanya pada lingkungan pengembangan.
            </p>
        </div>

        <?php
        // 1. Informasi Sistem
        printHeader("Informasi Sistem");
        echo "<table>";
        echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
        echo "<tr><td>User PHP</td><td>" . exec('whoami') . "</td></tr>";
        echo "<tr><td>Server</td><td>" . $_SERVER['SERVER_SOFTWARE'] . "</td></tr>";
        echo "<tr><td>OS</td><td>" . php_uname() . "</td></tr>";
        echo "</table>";

        // 2. Periksa apakah fungsi shell diaktifkan
        printHeader("Fungsi PHP yang Dibutuhkan");
        $functions = ['shell_exec', 'exec', 'system', 'passthru', 'escapeshellarg', 'escapeshellcmd'];
        echo "<table>";
        echo "<tr><th>Fungsi</th><th>Status</th></tr>";
        foreach ($functions as $function) {
            $status = isFunctionEnabled($function) ? "Diaktifkan ✅" : "Dinonaktifkan ❌";
            $color = isFunctionEnabled($function) ? "#10b981" : "#ef4444";
            echo "<tr><td>$function()</td><td style='color: $color;'>$status</td></tr>";
        }
        echo "</table>";

        // 3. Periksa path systemctl 
        printHeader("Path Systemctl");
        runCommand("which systemctl");
        runCommand("whereis systemctl");

        // 4. Periksa sudo
        printHeader("Konfigurasi Sudo");
        echo "<div class='info'>Catatan: Output kosong mungkin berarti masalah izin.</div>";
        runCommand("sudo -l");

        // 5. Periksa service status
        printHeader("Tes Status Service");
        runCommand("systemctl status nginx 2>&1");
        runCommand("/bin/systemctl status nginx 2>&1");
        runCommand("sudo systemctl status nginx 2>&1");

        // 6. Periksa error log
        printHeader("Error Log PHP Terakhir");
        $log_file = ini_get('error_log');
        if ($log_file && file_exists($log_file)) {
            runCommand("tail -n 20 " . escapeshellarg($log_file));
        } else {
            echo "<div class='warning'>File log PHP tidak ditemukan atau tidak dapat diakses.</div>";
        }

        // 7. Rekomendasi
        printHeader("Rekomendasi");
        ?>
        <div class="info">
            <ol>
                <li>
                    <strong>Masalah Izin Sudo:</strong> Tambahkan izin sudo untuk pengguna web server dengan mengedit file sudoers:
                    <pre>sudo visudo</pre>
                    Tambahkan baris berikut (ganti www-data dengan pengguna web server Anda):
                    <pre>www-data ALL=(ALL) NOPASSWD: /bin/systemctl status laravel-frankenphp-staging, /bin/systemctl status laravel-frankenphp-production, /bin/systemctl restart laravel-frankenphp-staging, /bin/systemctl restart laravel-frankenphp-production, /bin/systemctl start laravel-frankenphp-staging, /bin/systemctl start laravel-frankenphp-production, /bin/systemctl stop laravel-frankenphp-staging, /bin/systemctl stop laravel-frankenphp-production</pre>
                </li>
                <li>
                    <strong>Aktifkan Fungsi:</strong> Jika shell_exec dinonaktifkan, edit php.ini dan hapus shell_exec dari disable_functions:
                    <pre>disable_functions = ... (hapus shell_exec, exec, dll dari daftar)</pre>
                </li>
                <li>
                    <strong>Gunakan Path Absolut:</strong> Ubah kode PHP Anda untuk menggunakan path absolut ke systemctl:
                    <pre>$command = "/bin/systemctl status " . escapeshellarg($service_name) . " 2>&1";</pre>
                </li>
                <li>
                    <strong>Buat Script Wrapper:</strong> Alternatif, buat script bash yang dijalankan melalui sudo:
                    <pre>
#!/bin/bash
# /usr/local/bin/service_control.sh
case "$1" in
    "status"|"restart"|"start"|"stop")
        case "$2" in
            "laravel-frankenphp-staging"|"laravel-frankenphp-production")
                systemctl $1 $2
                ;;
            *)
                echo "Service tidak diizinkan"
                exit 1
                ;;
        esac
        ;;
    *)
        echo "Perintah tidak diizinkan"
        exit 1
        ;;
esac
            </pre>
                    Kemudian tambahkan sudoers:
                    <pre>www-data ALL=(ALL) NOPASSWD: /usr/local/bin/service_control.sh</pre>
                    Dan panggil dari PHP:
                    <pre>$command = "sudo /usr/local/bin/service_control.sh restart " . escapeshellarg($service_name) . " 2>&1";</pre>
                </li>
            </ol>
        </div>

    </div>
</body>

</html>
<?php
// Akhir file
?>