#!/bin/bash
# Script untuk mengontrol service Laravel FrankenPHP

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

# Eksekusi perintah
echo "$(date): Menjalankan: systemctl $ACTION $SERVICE" >> $LOG_FILE
/bin/systemctl $ACTION $SERVICE
RESULT=$?

# Log hasil
echo "$(date): Hasil eksekusi ($RESULT)" >> $LOG_FILE

exit $RESULT 