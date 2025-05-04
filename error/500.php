<?php
// Dapatkan error terakhir jika tersedia
$error = error_get_last();

// Log error dengan informasi tambahan
$logMessage = date('[Y-m-d H:i:s]') . ' 500 Server Error - ' . $_SERVER['REQUEST_URI'] . "\n";
if ($error) {
    $logMessage .= "Type: {$error['type']}, Message: {$error['message']}, File: {$error['file']}, Line: {$error['line']}\n";
}
$logMessage .= "IP: {$_SERVER['REMOTE_ADDR']}, User Agent: {$_SERVER['HTTP_USER_AGENT']}\n";
$logMessage .= "---------------------------------------------------\n";

// Tulis ke file log
$logDir = dirname(__DIR__) . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logDir . '/error.log', $logMessage, FILE_APPEND);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - Cek Service</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Roboto, -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #6b7280, #1f2937);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .error-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            padding: 30px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dc2626;
        }

        p {
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .button {
            display: inline-block;
            background-color: #4f46e5;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #4338ca;
        }

        .debug {
            margin-top: 20px;
            text-align: left;
            background: rgba(0, 0, 0, 0.2);
            padding: 15px;
            border-radius: 5px;
            font-size: 14px;
            display: none;
        }

        .toggle-debug {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: underline;
            cursor: pointer;
            margin-top: 20px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <h1>Maaf, server sedang mengalami kendala</h1>
        <p>Sistem kami mengalami masalah teknis saat memproses permintaan Anda. Kesalahan ini telah dicatat dan tim teknis kami sedang menyelesaikannya.</p>
        <p>Silakan coba beberapa saat lagi atau hubungi administrator sistem jika masalah berlanjut.</p>

        <a href="/" class="button">Kembali ke Halaman Utama</a>

        <?php if ($error): ?>
            <div class="toggle-debug" onclick="toggleDebug()">Tampilkan Informasi Debug</div>
            <div class="debug" id="debug-info">
                <p><strong>Error Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>Request URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></p>
                <p><strong>Error Type:</strong> <?php echo htmlspecialchars($error['type']); ?></p>
                <p><strong>Error Message:</strong> <?php echo htmlspecialchars($error['message']); ?></p>
                <p><strong>File:</strong> <?php echo htmlspecialchars($error['file']); ?></p>
                <p><strong>Line:</strong> <?php echo htmlspecialchars($error['line']); ?></p>
            </div>
            <script>
                function toggleDebug() {
                    var debugInfo = document.getElementById('debug-info');
                    if (debugInfo.style.display === 'block') {
                        debugInfo.style.display = 'none';
                    } else {
                        debugInfo.style.display = 'block';
                    }
                }
            </script>
        <?php endif; ?>
    </div>
</body>

</html>