#!/bin/bash
# Script untuk mengontrol service Laravel FrankenPHP dengan user alwyzon
# Membaca kredensial dari file .env

# Log untuk debugging
LOG_FILE="/tmp/service_control.log"
echo "$(date): Service Control dipanggil dengan argumen: $*" >> $LOG_FILE

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

# Baca kredensial dari file .env
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

# Ambil username dan password alwyzon dari file .env
ALWYZON_USERNAME=$(read_env_var "ALWYZON_USERNAME" "alwyzon")
ALWYZON_PASSWORD=$(read_env_var "ALWYZON_PASSWORD" "")

if [ -z "$ALWYZON_PASSWORD" ]; then
    echo "Password untuk user alwyzon tidak ditemukan di file .env"
    echo "$(date): Password untuk user alwyzon tidak ditemukan di file .env" >> $LOG_FILE
    exit 1
fi

# Eksekusi perintah melalui su - alwyzon
echo "$(date): Menjalankan perintah sebagai $ALWYZON_USERNAME: systemctl $ACTION $SERVICE" >> $LOG_FILE

# Gunakan expect atau sshpass jika tersedia
if command -v expect > /dev/null; then
    # Menggunakan expect (install dengan: apt-get install expect)
    expect_script=$(cat <<EOF
#!/usr/bin/expect
spawn su - $ALWYZON_USERNAME
expect "Password:"
send "$ALWYZON_PASSWORD\r"
expect "\\\$"
send "sudo /bin/systemctl $ACTION $SERVICE\r"
expect "\\\$"
send "exit\r"
expect eof
EOF
    )
    echo "$expect_script" | expect
    RESULT=$?
else
    # Jika expect tidak tersedia, gunakan metode echo password | su
    # PERHATIAN: Metode ini kurang aman karena password terlihat di process list
    echo "$(date): Expect tidak ditemukan, menggunakan metode alternatif (kurang aman)" >> $LOG_FILE
    echo "$ALWYZON_PASSWORD" | su - $ALWYZON_USERNAME -c "sudo /bin/systemctl $ACTION $SERVICE"
    RESULT=$?
fi

# Log hasil
echo "$(date): Hasil eksekusi ($RESULT)" >> $LOG_FILE

exit $RESULT 