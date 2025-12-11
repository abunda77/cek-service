import subprocess
import sys
import argparse
import os
from colorama import init, Fore, Style
from tabulate import tabulate

# Inisialisasi colorama untuk output berwarna
init(autoreset=True)

def load_services_config(config_file="services.txt"):
    """Membaca konfigurasi service dari file eksternal."""
    services = {}
    
    # Cari file config di direktori yang sama dengan script
    script_dir = os.path.dirname(os.path.abspath(__file__))
    config_path = os.path.join(script_dir, config_file)
    
    if not os.path.exists(config_path):
        print(f"{Fore.RED}✖ File konfigurasi '{config_file}' tidak ditemukan!{Style.RESET_ALL}")
        print(f"{Fore.YELLOW}Buat file '{config_file}' dengan format:{Style.RESET_ALL}")
        print(f"  nama_pendek=nama_service_systemd")
        print(f"  Contoh: staging=laravel-frankenphp-staging")
        sys.exit(1)
    
    try:
        with open(config_path, 'r') as f:
            for line in f:
                line = line.strip()
                # Skip baris kosong dan komentar
                if not line or line.startswith('#'):
                    continue
                if '=' in line:
                    key, value = line.split('=', 1)
                    services[key.strip()] = value.strip()
    except Exception as e:
        print(f"{Fore.RED}✖ Gagal membaca file konfigurasi: {e}{Style.RESET_ALL}")
        sys.exit(1)
    
    if not services:
        print(f"{Fore.RED}✖ Tidak ada service yang dikonfigurasi di '{config_file}'!{Style.RESET_ALL}")
        sys.exit(1)
    
    return services

def run_command(command):
    """Menjalankan perintah shell dan mengembalikan outputnya."""
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True)
        return result.stdout, result.stderr, result.returncode
    except Exception as e:
        return "", str(e), 1

def check_service_status(service_name):
    """Memeriksa status layanan menggunakan systemctl."""
    command = f"systemctl status {service_name}"
    stdout, stderr, returncode = run_command(command)
    
    if returncode == 0:
        status_lines = stdout.splitlines()
        for line in status_lines:
            if "Active:" in line:
                status = line.strip()
                if "active (running)" in status:
                    return f"{Fore.GREEN}{Style.BRIGHT}● {status}{Style.RESET_ALL}"
                elif "inactive" in status or "dead" in status:
                    return f"{Fore.RED}■ {status}{Style.RESET_ALL}"
                else:
                    return f"{Fore.YELLOW}▲ {status}{Style.RESET_ALL}"
    elif "Unit" in stderr and "could not be found" in stderr:
        return f"{Fore.RED}✖ Layanan {service_name} tidak ditemukan.{Style.RESET_ALL}"
    else:
        return f"{Fore.RED}✖ Gagal memeriksa status {service_name}: {stderr}{Style.RESET_ALL}"

def manage_service(service_name, action):
    """Mengelola layanan (start, stop, restart)."""
    command = f"sudo systemctl {action} {service_name}"
    stdout, stderr, returncode = run_command(command)
    
    if returncode == 0:
        return f"{Fore.GREEN}{Style.BRIGHT}✔ Berhasil {action} layanan {service_name}.{Style.RESET_ALL}"
    else:
        return f"{Fore.RED}✖ Gagal {action} layanan {service_name}: {stderr}{Style.RESET_ALL}"

def print_header(services):
    """Mencetak header aplikasi dengan tabel ASCII."""
    header_table = [
        [f"{Fore.CYAN}{Style.BRIGHT}Laravel FrankenPHP Manager{Style.RESET_ALL}"]
    ]
    print(tabulate(header_table, tablefmt="double_grid", headers=[""], colalign=("center",)))
    
    service_choices = ", ".join(list(services.keys()) + ["all"])
    options_table = [
        ["--service", service_choices, "Default: all"],
        ["--action", "status, start, stop, restart", "Default: status"]
    ]
    
    print(f"\n{Fore.YELLOW}Opsi yang tersedia:{Style.RESET_ALL}")
    print(tabulate(options_table, headers=["Opsi", "Pilihan", "Keterangan"], 
                   tablefmt="pretty", colalign=("left", "left", "left")))
    
    print(f"\n{Fore.YELLOW}Contoh penggunaan:{Style.RESET_ALL}")
    print(f"  {Fore.GREEN}python3 cekservice.py --service staging --action restart{Style.RESET_ALL}\n")

def interactive_mode():
    """Mode interaktif untuk memilih service dan action."""
    # Load services dari file konfigurasi
    all_services = load_services_config()
    
    # Buat mapping nomor ke service
    services = {}
    service_table = []
    for idx, (key, value) in enumerate(all_services.items(), 1):
        services[str(idx)] = (key, value)
        service_table.append([str(idx), key.upper(), value])
    
    # Tambahkan opsi "all"
    all_idx = str(len(all_services) + 1)
    services[all_idx] = ("all", "all")
    service_table.append([all_idx, "Semua Layanan", "all"])
    
    actions = {
        "1": "status",
        "2": "start",
        "3": "stop",
        "4": "restart"
    }
    
    # Header
    print(f"\n{Fore.CYAN}{Style.BRIGHT}╔═══════════════════════════════════════╗{Style.RESET_ALL}")
    print(f"{Fore.CYAN}{Style.BRIGHT}║   Laravel FrankenPHP Manager v2.0    ║{Style.RESET_ALL}")
    print(f"{Fore.CYAN}{Style.BRIGHT}╚═══════════════════════════════════════╝{Style.RESET_ALL}\n")
    
    # Pilih Service
    print(f"{Fore.YELLOW}{Style.BRIGHT}📋 Pilih Layanan:{Style.RESET_ALL}")
    print(tabulate(service_table, headers=["No", "Nama", "Service"], tablefmt="fancy_grid"))
    
    max_choice = len(services)
    service_choice = input(f"\n{Fore.CYAN}Masukkan nomor layanan (1-{max_choice}): {Style.RESET_ALL}").strip()
    
    if service_choice not in services:
        print(f"{Fore.RED}✖ Pilihan tidak valid!{Style.RESET_ALL}")
        return
    
    selected_service_key, selected_service_name = services[service_choice]
    
    # Pilih Action
    print(f"\n{Fore.YELLOW}{Style.BRIGHT}⚙️  Pilih Aksi:{Style.RESET_ALL}")
    action_table = [
        ["1", "Status", "Cek status layanan"],
        ["2", "Start", "Jalankan layanan"],
        ["3", "Stop", "Hentikan layanan"],
        ["4", "Restart", "Restart layanan"]
    ]
    print(tabulate(action_table, headers=["No", "Aksi", "Deskripsi"], tablefmt="fancy_grid"))
    
    action_choice = input(f"\n{Fore.CYAN}Masukkan nomor aksi (1-4): {Style.RESET_ALL}").strip()
    
    if action_choice not in actions:
        print(f"{Fore.RED}✖ Pilihan tidak valid!{Style.RESET_ALL}")
        return
    
    selected_action = actions[action_choice]
    
    # Konfirmasi
    print(f"\n{Fore.YELLOW}{'='*50}{Style.RESET_ALL}")
    print(f"{Fore.CYAN}Layanan: {Style.RESET_ALL}{Fore.WHITE}{Style.BRIGHT}{selected_service_key}{Style.RESET_ALL}")
    print(f"{Fore.CYAN}Aksi: {Style.RESET_ALL}{Fore.WHITE}{Style.BRIGHT}{selected_action}{Style.RESET_ALL}")
    print(f"{Fore.YELLOW}{'='*50}{Style.RESET_ALL}")
    
    confirm = input(f"\n{Fore.YELLOW}Lanjutkan? (y/n): {Style.RESET_ALL}").strip().lower()
    
    if confirm != 'y':
        print(f"{Fore.YELLOW}✖ Dibatalkan.{Style.RESET_ALL}")
        return
    
    # Eksekusi
    print(f"\n{Fore.CYAN}⏳ Memproses...{Style.RESET_ALL}\n")
    
    if selected_service_key == "all":
        selected_services = all_services.values()
    else:
        selected_services = [selected_service_name]
    
    service_results = []
    for service in selected_services:
        if selected_action == "status":
            result = check_service_status(service)
        else:
            result = manage_service(service, selected_action)
        service_results.append([service, result])
    
    print(tabulate(service_results, 
                   headers=[f"{Fore.CYAN}Layanan{Style.RESET_ALL}", 
                            f"{Fore.CYAN}Status/Aksi{Style.RESET_ALL}"], 
                   tablefmt="pretty"))
    
    print(f"\n{Fore.GREEN}{Style.BRIGHT}✔ Selesai!{Style.RESET_ALL}\n")

def main():
    # Load services dari file konfigurasi
    services = load_services_config()
    service_choices = list(services.keys()) + ["all"]
    
    # Mengatur parser untuk argumen baris perintah
    parser = argparse.ArgumentParser(
        description="Manajemen layanan Laravel FrankenPHP",
        add_help=False
    )
    parser.add_argument(
        "--service",
        choices=service_choices,
        help=f"Pilih layanan: {', '.join(service_choices)}"
    )
    parser.add_argument(
        "--action",
        choices=["status", "start", "stop", "restart"],
        help="Aksi: status, start, stop, atau restart"
    )
    parser.add_argument(
        "-i", "--interactive",
        action="store_true",
        help="Mode interaktif dengan dialog"
    )
    parser.add_argument(
        "-h", "--help",
        action="help",
        default=argparse.SUPPRESS,
        help="Tampilkan bantuan penggunaan"
    )
    
    args = parser.parse_args()
    
    # Jika tidak ada argumen atau flag -i, jalankan mode interaktif
    if args.interactive or (args.service is None and args.action is None):
        interactive_mode()
        return

    # Mode CLI - Mencetak header
    print_header(services)

    # Set default values jika tidak ada
    if args.service is None:
        args.service = "all"
    if args.action is None:
        args.action = "status"

    # Menentukan layanan yang akan diproses
    if args.service == "all":
        selected_services = services.values()
    else:
        selected_services = [services[args.service]]

    # Memproses setiap layanan
    service_results = []
    for service in selected_services:
        if args.action == "status":
            result = check_service_status(service)
        else:
            result = manage_service(service, args.action)
        service_results.append([service, result])

    # Tampilkan hasil dalam tabel
    print(tabulate(service_results, 
                   headers=[f"{Fore.CYAN}Layanan{Style.RESET_ALL}", 
                            f"{Fore.CYAN}Status/Aksi{Style.RESET_ALL}"], 
                   tablefmt="pretty"))

if __name__ == "__main__":
    main()
