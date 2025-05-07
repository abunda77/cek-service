<?php

/**
 * Script diagnostik untuk menguji apakah systemctl berhasil dijalankan
 * Hanya untuk keperluan troubleshooting
 */

// Tampilkan semua error untuk debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostik Systemctl</h1>";
echo "<p>Script ini akan mencoba berbagai metode untuk menjalankan perintah systemctl dan menampilkan hasilnya.</p>";

// Definisikan layanan untuk dites
$test_service = 'laravel-frankenphp-staging';

echo "<h2>1. Metode sudo langsung</h2>";
try {
    $command_direct = "sudo systemctl status $test_service 2>&1";
    echo "<pre>Menjalankan: $command_direct</pre>";

    $output_direct = shell_exec($command_direct);
    if (!empty($output_direct)) {
        echo "<div style='background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        echo "<strong>BERHASIL!</strong> Output:<br><pre>" . htmlspecialchars($output_direct) . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        echo "<strong>GAGAL!</strong> Tidak ada output yang diterima.";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
    echo "<strong>GAGAL dengan Exception!</strong> Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h2>2. Metode service_control.sh</h2>";
try {
    $wrapper_script = __DIR__ . "/service_control.sh";

    // Periksa keberadaan dan izin file
    if (!file_exists($wrapper_script)) {
        echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        echo "<strong>GAGAL!</strong> File script tidak ditemukan: $wrapper_script";
        echo "</div>";
    } else {
        echo "<pre>Script ditemukan: $wrapper_script</pre>";

        // Periksa izin
        $perms = substr(sprintf('%o', fileperms($wrapper_script)), -4);
        echo "<pre>Izin file: $perms</pre>";

        if (!is_executable($wrapper_script)) {
            echo "<pre>Script tidak executable, mencoba chmod +x...</pre>";
            chmod($wrapper_script, 0755);
            $perms_after = substr(sprintf('%o', fileperms($wrapper_script)), -4);
            echo "<pre>Izin file setelah chmod: $perms_after</pre>";
        }

        $command_wrapper = "sudo " . escapeshellarg($wrapper_script) . " status " . escapeshellarg($test_service) . " 2>&1";
        echo "<pre>Menjalankan: $command_wrapper</pre>";

        $output_wrapper = shell_exec($command_wrapper);
        if (!empty($output_wrapper)) {
            echo "<div style='background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
            echo "<strong>BERHASIL!</strong> Output:<br><pre>" . htmlspecialchars($output_wrapper) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
            echo "<strong>GAGAL!</strong> Tidak ada output yang diterima.";
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
    echo "<strong>GAGAL dengan Exception!</strong> Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h2>3. Metode secure_service_control.sh</h2>";
try {
    $secure_script = __DIR__ . "/secure_service_control.sh";

    // Periksa keberadaan dan izin file
    if (!file_exists($secure_script)) {
        echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        echo "<strong>GAGAL!</strong> File script tidak ditemukan: $secure_script";
        echo "</div>";
    } else {
        echo "<pre>Script ditemukan: $secure_script</pre>";

        // Periksa izin
        $perms = substr(sprintf('%o', fileperms($secure_script)), -4);
        echo "<pre>Izin file: $perms</pre>";

        if (!is_executable($secure_script)) {
            echo "<pre>Script tidak executable, mencoba chmod +x...</pre>";
            chmod($secure_script, 0755);
            $perms_after = substr(sprintf('%o', fileperms($secure_script)), -4);
            echo "<pre>Izin file setelah chmod: $perms_after</pre>";
        }

        $command_secure = "sudo " . escapeshellarg($secure_script) . " status " . escapeshellarg($test_service) . " 2>&1";
        echo "<pre>Menjalankan: $command_secure</pre>";

        $output_secure = shell_exec($command_secure);
        if (!empty($output_secure)) {
            echo "<div style='background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
            echo "<strong>BERHASIL!</strong> Output:<br><pre>" . htmlspecialchars($output_secure) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
            echo "<strong>GAGAL!</strong> Tidak ada output yang diterima.";
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
    echo "<strong>GAGAL dengan Exception!</strong> Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h2>4. Metode File Interface (command_interface.sh)</h2>";
try {
    // Include file interface
    if (file_exists(__DIR__ . '/file_interface.php')) {
        require_once __DIR__ . '/file_interface.php';

        echo "<pre>File interface ditemukan. Mencoba menggunakan run_systemctl_command()...</pre>";

        // Periksa direktori
        if (!is_dir(COMMAND_DIR)) {
            echo "<pre>Direktori command tidak ditemukan. Mencoba membuat...</pre>";
            mkdir(COMMAND_DIR, 0755, true);
        }
        if (!is_dir(RESULT_DIR)) {
            echo "<pre>Direktori result tidak ditemukan. Mencoba membuat...</pre>";
            mkdir(RESULT_DIR, 0755, true);
        }

        // Coba jalankan command
        $result = run_systemctl_command('status', $test_service);

        if ($result['success']) {
            echo "<div style='background-color: #d1e7dd; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
            echo "<strong>BERHASIL!</strong> Output:<br><pre>" . htmlspecialchars($result['output']) . "</pre>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
            echo "<strong>GAGAL!</strong> Error: " . htmlspecialchars($result['error']);
            echo "</div>";

            // Periksa apakah cron job berjalan
            echo "<pre>Tip: Pastikan command_interface.sh sudah di-setup sebagai cron job untuk user alwyzon.</pre>";
            echo "<pre>Periksa dengan: grep CRON /var/log/syslog | grep command_interface</pre>";
        }
    } else {
        echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
        echo "<strong>GAGAL!</strong> File file_interface.php tidak ditemukan.";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px;'>";
    echo "<strong>GAGAL dengan Exception!</strong> Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h2>Ringkasan</h2>";
echo "<p>Jika semua metode gagal:</p>";
echo "<ol>";
echo "<li>Periksa konfigurasi sudo untuk web server (www-data/apache/etc)</li>";
echo "<li>Periksa apakah script wrapper memiliki izin eksekusi dan path yang benar</li>";
echo "<li>Periksa log di /tmp/secure_service_control.log dan /tmp/service_control.log</li>";
echo "<li>Untuk metode file interface, pastikan cron job sudah berjalan untuk user alwyzon</li>";
echo "<li>Pertimbangkan untuk menambahkan rule sudoers khusus untuk web server</li>";
echo "</ol>";

echo "<h2>Periksa Informasi Pengguna</h2>";
echo "<pre>";
echo "Current user: " . exec('whoami') . "\n";
echo "User groups: " . exec('groups') . "\n";
echo "Sudo permissions:\n";
passthru('sudo -l 2>&1');
echo "</pre>";
