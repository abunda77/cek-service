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

def main():
    # Mengatur parser untuk argumen baris perintah
    parser = argparse.ArgumentParser(
        description="Manajemen layanan Laravel FrankenPHP",
        add_help=False
    )
    parser.add_argument(
        "--service",
        choices=["staging", "production", "pams", "bosco", "all"],
        default="all",
        help="Pilih layanan: staging, production, pams, bosco, atau all (default: all)"
    )
    parser.add_argument(
        "--action",
        choices=["status", "start", "stop", "restart"],
        default="status",
        help="Aksi: status, start, stop, atau restart (default: status)"
    )
    parser.add_argument(
        "-h", "--help",
        action="help",
        default=argparse.SUPPRESS,
        help="Tampilkan bantuan penggunaan"
    )
    
    args = parser.parse_args()

    # Mencetak header
    print_header()
 # Daftar layanan yang akan dikelola
    services = {
        "staging": "laravel-frankenphp-staging",
        "production": "laravel-frankenphp-production",
        "pams": "laravel-frankenphp-pams",
        "bosco": "laravel-frankenphp-bosco"
    }

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
