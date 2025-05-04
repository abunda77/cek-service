<?php

/**
 * Class Logger
 * Kelas untuk mengelola pencatatan log dalam aplikasi
 */
class Logger
{
    /**
     * Path ke file log
     * @var string
     */
    private $logFile;

    /**
     * Level minimum log yang akan dicatat
     * @var string
     */
    private $logLevel;

    /**
     * Level log yang tersedia (dari yang paling rendah ke yang paling tinggi)
     * @var array
     */
    private $availableLevels = ['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'];

    /**
     * Konstruktor
     * @param string $logFile Path ke file log
     * @param string $logLevel Level minimum log yang akan dicatat
     */
    public function __construct($logFile = null, $logLevel = 'DEBUG')
    {
        if ($logFile === null) {
            $logFile = dirname(__DIR__) . '/logs/app.log';
        }

        $this->logFile = $logFile;
        $this->logLevel = strtoupper($logLevel);

        // Pastikan direktori log ada
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }

    /**
     * Cek apakah level log yang diberikan harus dicatat berdasarkan konfigurasi
     * @param string $level Level log untuk dicek
     * @return bool
     */
    private function shouldLog($level)
    {
        $currentLevelIndex = array_search($this->logLevel, $this->availableLevels);
        $givenLevelIndex = array_search($level, $this->availableLevels);

        return $givenLevelIndex >= $currentLevelIndex;
    }

    /**
     * Tulis log dengan level tertentu
     * @param string $level Level log (DEBUG, INFO, WARNING, ERROR, CRITICAL)
     * @param string $message Pesan log
     * @param array $context Data tambahan untuk log
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $level = strtoupper($level);

        if (!in_array($level, $this->availableLevels)) {
            $level = 'INFO';
        }

        if (!$this->shouldLog($level)) {
            return;
        }

        // Format waktu
        $timestamp = date('Y-m-d H:i:s');

        // Format pesan
        $formattedMessage = "[$timestamp] [$level] $message";

        // Tambahkan konteks jika ada
        if (!empty($context)) {
            $formattedMessage .= ' ' . json_encode($context);
        }

        // Tambahkan informasi request jika tersedia
        if (isset($_SERVER['REQUEST_URI'])) {
            $formattedMessage .= " (URI: {$_SERVER['REQUEST_URI']})";
        }

        // Tambahkan informasi IP
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $formattedMessage .= " (IP: {$_SERVER['REMOTE_ADDR']})";
        }

        $formattedMessage .= PHP_EOL;

        // Tulis ke file log
        file_put_contents($this->logFile, $formattedMessage, FILE_APPEND);
    }

    /**
     * Tulis log debug
     * @param string $message Pesan log
     * @param array $context Data tambahan untuk log
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Tulis log info
     * @param string $message Pesan log
     * @param array $context Data tambahan untuk log
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Tulis log warning
     * @param string $message Pesan log
     * @param array $context Data tambahan untuk log
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Tulis log error
     * @param string $message Pesan log
     * @param array $context Data tambahan untuk log
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Tulis log critical
     * @param string $message Pesan log
     * @param array $context Data tambahan untuk log
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Log error PHP terakhir
     * @param string $additionalMessage Pesan tambahan untuk log
     * @return void
     */
    public function logLastError($additionalMessage = '')
    {
        $error = error_get_last();
        if ($error) {
            $message = "PHP Error: {$error['message']} in {$error['file']} on line {$error['line']}";
            if ($additionalMessage) {
                $message = "$additionalMessage - $message";
            }
            $this->error($message);
        }
    }
}
