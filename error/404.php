<?php
// Log error 404
$logMessage = date('[Y-m-d H:i:s]') . ' 404 Not Found - ' . $_SERVER['REQUEST_URI'] . "\n";
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
    <title>Halaman Tidak Ditemukan - Cek Service</title>
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
            font-size: 72px;
            margin-bottom: 20px;
            color: #4f46e5;
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

        .requested-url {
            margin-top: 20px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="icon">404</div>
        <h1>Halaman Tidak Ditemukan</h1>
        <p>Maaf, halaman yang Anda cari tidak dapat ditemukan. Halaman mungkin telah dipindahkan, dihapus, atau URL yang Anda masukkan salah.</p>

        <a href="/" class="button">Kembali ke Halaman Utama</a>

        <div class="requested-url">
            URL yang diminta: <?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>
        </div>
    </div>
</body>

</html>