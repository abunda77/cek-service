<?php

/**
 * Error Handler untuk aplikasi Cek Service
 * File ini berisi fungsi-fungsi untuk menangani error dan exception
 */

// Pastikan class Logger tersedia
require_once __DIR__ . '/Logger.php';

// Inisialisasi logger
$logger = new Logger();

/**
 * Fungsi untuk menangani error PHP
 */
function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    global $logger;

    // Klasifikasi level error
    $level = 'ERROR';
    switch ($errno) {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            $level = 'CRITICAL';
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
        case E_USER_WARNING:
            $level = 'WARNING';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $level = 'INFO';
            break;
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            $level = 'DEBUG';
            break;
    }

    // Log error
    $errorMessage = "$errstr in $errfile on line $errline";
    $logger->log($level, $errorMessage, [
        'errno' => $errno,
        'code' => getErrorCode($errno)
    ]);

    // Kembalikan false untuk memungkinkan PHP menjalankan error handler bawaan
    // Untuk error serius, kembali menjalankan error handler bawaan akan menghentikan eksekusi
    return false;
}

/**
 * Fungsi untuk menangani exception yang tidak tertangkap
 */
function customExceptionHandler($exception)
{
    global $logger;

    // Log exception
    $logger->critical("Uncaught Exception: {$exception->getMessage()}", [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    // Tampilkan halaman error 500
    http_response_code(500);
    include __DIR__ . '/../error/500.php';
    exit;
}

/**
 * Fungsi untuk dijalankan saat script PHP selesai
 */
function shutdownFunction()
{
    global $logger;

    // Cek apakah ada error fatal
    $error = error_get_last();
    if ($error !== null && ($error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR))) {
        // Log error fatal
        $logger->critical("Fatal Error: {$error['message']}", [
            'file' => $error['file'],
            'line' => $error['line']
        ]);

        // Tampilkan halaman error 500 jika belum ada output
        if (!headers_sent()) {
            http_response_code(500);
            include __DIR__ . '/../error/500.php';
        }
    }
}

/**
 * Fungsi bantuan untuk mendapatkan kode error yang lebih mudah dibaca
 */
function getErrorCode($errno)
{
    $errorCodes = [
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_PARSE => 'E_PARSE',
        E_NOTICE => 'E_NOTICE',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_CORE_WARNING => 'E_CORE_WARNING',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_COMPILE_WARNING => 'E_COMPILE_WARNING',
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_STRICT => 'E_STRICT',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
        E_DEPRECATED => 'E_DEPRECATED',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_ALL => 'E_ALL'
    ];

    return isset($errorCodes[$errno]) ? $errorCodes[$errno] : "Unknown Error ($errno)";
}

// Set error handler, exception handler, dan shutdown function
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
register_shutdown_function('shutdownFunction');

// Atur level error reporting (tampilkan semua error kecuali notice dan deprecated pada production)
// Anda bisa mengubah nilai ini menjadi E_ALL untuk development
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
