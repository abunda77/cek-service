#!/bin/bash
# Script wrapper menggunakan pkexec
# Perlu mengatur policy di /etc/polkit-1/rules.d/

# Log untuk debugging
LOG_FILE="/tmp/pkexec_service.log"
echo "$(date): PKExec Service Control dipanggil dengan argumen: $*" >> $LOG_FILE

# Validasi input
if [ $# -lt 2 ]; then
    echo "Penggunaan: $0 <action> <service>"
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
        exit 1
        ;;
esac

# Eksekusi dengan pkexec
pkexec /bin/systemctl "$ACTION" "$SERVICE"
exit $? 