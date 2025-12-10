import subprocess
import sys
import argparse
from colorama import init, Fore, Style
from tabulate import tabulate

# Inisialisasi colorama untuk output berwarna
init(autoreset=True)

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

def print_header():
    """Mencetak header aplikasi dengan tabel ASCII."""
    header_table = [
        [f"{Fore.CYAN}{Style.BRIGHT}Laravel FrankenPHP Manager{Style.RESET_ALL}"]
    ]
    print(tabulate(header_table, tablefmt="double_grid", headers=[""], colalign=("center",)))
    
    options_table = [
        ["--service", "staging, production, pams, bosco, all", "Default: all"],
        ["--action", "status, start, stop, restart", "Default: status"]
    ]
    
    print(f"\n{Fore.YELLOW}Opsi yang tersedia:{Style.RESET_ALL}")
    print(tabulate(options_table, headers=["Opsi", "Pilihan", "Keterangan"], 
                   tablefmt="pretty", colalign=("left", "left", "left")))
    
    print(f"\n{Fore.YELLOW}Contoh penggunaan:{Style.RESET_ALL}")
    print(f"  {Fore.GREEN}python3 service_manager.py --service staging --action restart{Style.RESET_ALL}\n")

def interactive_mode():
    """Mode interaktif untuk memilih service dan action."""
    services = {
        "1": ("staging", "laravel-frankenphp-staging"),
        "2": ("production", "laravel-frankenphp-production"),
        "3": ("pams", "laravel-frankenphp-pams"),
        "4": ("bosco", "laravel-frankenphp-bosco"),
        "5": ("all", "all")
    }
    
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
    service_table = [
        ["1", "Staging", "laravel-frankenphp-staging"],
        ["2", "Production", "laravel-frankenphp-production"],
        ["3", "PAMS", "laravel-frankenphp-pams"],
        ["4", "BOSCO", "laravel-frankenphp-bosco"],
        ["5", "Semua Layanan", "all"]
    ]
    print(tabulate(service_table, headers=["No", "Nama", "Service"], tablefmt="fancy_grid"))
    
    service_choice = input(f"\n{Fore.CYAN}Masukkan nomor layanan (1-5): {Style.RESET_ALL}").strip()
    
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
    
    all_services = {
        "staging": "laravel-frankenphp-staging",
        "production": "laravel-frankenphp-production",
        "pams": "laravel-frankenphp-pams",
        "bosco": "laravel-frankenphp-bosco"
    }
    
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
    # Mengatur parser untuk argumen baris perintah
    parser = argparse.ArgumentParser(
        description="Manajemen layanan Laravel FrankenPHP",
        add_help=False
    )
    parser.add_argument(
        "--service",
        choices=["staging", "production", "pams", "bosco", "all"],
        help="Pilih layanan: staging, production, pams, bosco, atau all"
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
    print_header()
    
    # Daftar layanan yang akan dikelola
    services = {
        "staging": "laravel-frankenphp-staging",
        "production": "laravel-frankenphp-production",
        "pams": "laravel-frankenphp-pams",
        "bosco": "laravel-frankenphp-bosco"
    }

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
