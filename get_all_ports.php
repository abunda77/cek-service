<?php
// Muat error handler terlebih dahulu (opsional, bisa dikomentari jika menyebabkan error)
// require_once 'includes/error_handler.php';

// Pastikan pengguna sudah login
session_start();

// Cek otentikasi
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Function untuk mendapatkan semua port yang terbuka
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
            return array_values(array_unique($ports)); // Hapus duplikat dan reset indeks array
        }

        return [];
    } catch (Exception $e) {
        // Jika terjadi error, kembalikan array kosong
        return [];
    }
}

// Dapatkan daftar port yang terbuka
$openPorts = getOpenPorts();

// Tambahkan informasi tentang proses untuk beberapa port spesifik (opsional)
$portsWithInfo = [];
foreach ($openPorts as $port) {
    $portInfo = ['port' => $port];

    // Jika port adalah port yang sedang dimonitor khusus, tambahkan info tambahan
    if ($port == '8111' || $port == '8112') {
        $processInfo = getProcessByPort($port);
        if ($processInfo) {
            $portInfo['process'] = $processInfo['name'];
            $portInfo['pid'] = $processInfo['pid'];
        }
    }

    $portsWithInfo[] = $portInfo;
}

// Function untuk mendapatkan informasi proses yang menggunakan port tertentu
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

        return null;
    } catch (Exception $e) {
        return null;
    }
}

// Kembalikan data sebagai JSON
header('Content-Type: application/json');
echo json_encode($portsWithInfo);
