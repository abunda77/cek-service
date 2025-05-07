#!/bin/bash
# Script wrapper yang aman untuk mengontrol service Laravel FrankenPHP
# Pastikan user web server (www-data, apache, dll) memiliki izin sudo untuk file ini
# Tambahkan di /etc/sudoers:
# www-data ALL=(ALL) NOPASSWD: /path/to/secure_service_control.sh

# Log untuk debugging
LOG_FILE="/tmp/secure_service_control.log"
echo "$(date): Secure Service Control dipanggil dengan argumen: $*" >> $LOG_FILE

# Validasi input
if [ $# -lt 2 ]; then
    echo "Penggunaan: $0 <action> <service>"
    echo "  action: status, start, stop, restart"
    echo "  service: laravel-frankenphp-staging, laravel-frankenphp-production"
    exit 1
fi

ACTION=$1
SERVICE=$2

# Validasi action
case "$ACTION" in
    "status"|"restart"|"start"|"stop")
        # Action valid
        ;;
    *)
        echo "Perintah tidak diizinkan: $ACTION"
        echo "$(date): Perintah tidak diizinkan: $ACTION" >> $LOG_FILE
        exit 1
        ;;
esac

# Validasi service
case "$SERVICE" in
    "laravel-frankenphp-staging"|"laravel-frankenphp-production")
        # Service valid
        ;;
    *)
        echo "Service tidak diizinkan: $SERVICE"
        echo "$(date): Service tidak diizinkan: $SERVICE" >> $LOG_FILE
        exit 1
        ;;
esac

# Mendapatkan path absolut ke systemctl
SYSTEMCTL_PATH=$(which systemctl)
if [ -z "$SYSTEMCTL_PATH" ]; then
    SYSTEMCTL_PATH="/bin/systemctl"
fi

# Baca kredensial dari file .env jika diperlukan
# Ini hanya jika kita perlu menggunakan kredensial untuk operasi tertentu
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
ENV_FILE="$SCRIPT_DIR/.env"

# Fungsi untuk membaca variabel dari .env
read_env_var() {
    local var_name=$1
    local default_value=$2
    local value=""
    
    if [ -f "$ENV_FILE" ]; then
        value=$(grep "^$var_name=" "$ENV_FILE" | cut -d '=' -f2-)
    fi
    
    if [ -z "$value" ]; then
        value=$default_value
    fi
    
    echo "$value"
}

# Log perintah yang akan dijalankan
echo "$(date): Menjalankan: $SYSTEMCTL_PATH $ACTION $SERVICE" >> $LOG_FILE

# Eksekusi perintah dengan sudo secara langsung (tanpa password)
$SYSTEMCTL_PATH $ACTION $SERVICE 2>&1
RESULT=$?

# Log hasil
echo "$(date): Hasil eksekusi ($RESULT)" >> $LOG_FILE

exit $RESULT 