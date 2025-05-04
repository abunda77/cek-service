<?php
session_start();

// Memuat library phpdotenv untuk membaca file .env
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Cek apakah user sudah login
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Proses login
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi input
    if (empty($username) || empty($password)) {
        $errors[] = 'Username dan password harus diisi.';
    } else {
        // Ambil kredensial dari .env
        $env_username = $_ENV['APP_USERNAME'] ?? '';
        $env_password = $_ENV['APP_PASSWORD'] ?? '';

        // Verifikasi kredensial
        if ($username === $env_username && $password === $env_password) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Username atau password salah.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - System Dashboard</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-8">
            <i class="fas fa-server text-4xl text-indigo-500 mb-4"></i>
            <h1 class="login-title">System Dashboard</h1>
            <p class="text-gray-300">Silahkan login untuk melanjutkan</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div id="error-container" class="login-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div id="error-container" class="login-error" style="display: none;"></div>
        <?php endif; ?>

        <form id="login-form" method="POST" class="space-y-6">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user mr-2"></i>Username
                </label>
                <input
                    type="text"
                    name="username"
                    id="username"
                    class="form-input"
                    autocomplete="username"
                    value="<?php echo htmlspecialchars($username ?? ''); ?>" />
            </div>
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock mr-2"></i>Password
                </label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-input"
                    autocomplete="current-password" />
            </div>
            <button
                type="submit"
                class="btn btn-primary">
                <i class="fas fa-sign-in-alt mr-2"></i>Login
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-gray-400">
            <p>&copy; <?php echo date('Y'); ?> System Dashboard</p>
            <p class="mt-2">Versi 1.0</p>
        </div>
    </div>

    <!-- Custom JavaScript -->
    <script src="assets/js/main.js"></script>
</body>

</html>