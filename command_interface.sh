#!/bin/bash
# Script interface yang berjalan sebagai user alwyzon
# Simpan di home directory alwyzon
# Jadwalkan dengan cron untuk berjalan setiap menit:
# * * * * * /home/alwyzon/command_interface.sh

# Direktori untuk file perintah dan hasil
COMMAND_DIR="/tmp/service_commands"
RESULT_DIR="/tmp/service_results"

# Pastikan direktori ada
mkdir -p "$COMMAND_DIR"
mkdir -p "$RESULT_DIR"

# Pastikan akses file aman
chmod 755 "$COMMAND_DIR"
chmod 755 "$RESULT_DIR"

# Cek jika ada file perintah
if [ "$(ls -A $COMMAND_DIR)" ]; then
    for CMD_FILE in "$COMMAND_DIR"/*; do
        # Ambil ID file
        FILE_ID=$(basename "$CMD_FILE")
        
        # Log
        echo "Memproses file perintah: $FILE_ID"
        
        # Baca perintah
        CMD_ACTION=$(sed -n '1p' "$CMD_FILE")
        CMD_SERVICE=$(sed -n '2p' "$CMD_FILE")
        
        # Validasi action
        case "$CMD_ACTION" in
            "status"|"restart"|"start"|"stop")
                # Action valid
                ;;
            *)
                echo "Perintah tidak diizinkan: $CMD_ACTION" > "$RESULT_DIR/$FILE_ID"
                rm "$CMD_FILE"
                continue
                ;;
        esac
        
        # Validasi service
        case "$CMD_SERVICE" in
            "laravel-frankenphp-staging"|"laravel-frankenphp-production")
                # Service valid
                ;;
            *)
                echo "Service tidak diizinkan: $CMD_SERVICE" > "$RESULT_DIR/$FILE_ID"
                rm "$CMD_FILE"
                continue
                ;;
        esac
        
        # Jalankan perintah
        sudo /bin/systemctl "$CMD_ACTION" "$CMD_SERVICE" > "$RESULT_DIR/$FILE_ID" 2>&1
        
        # Hapus file perintah setelah selesai
        rm "$CMD_FILE"
    done
fi

exit 0 