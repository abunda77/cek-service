import psutil
import socket
from tabulate import tabulate
from colorama import init, Fore, Style
import argparse

# Inisialisasi colorama untuk output berwarna
init(autoreset=True)

def get_process_name(pid):
    """Mendapatkan nama proses dari PID."""
    try:
        process = psutil.Process(pid)
        return process.name()
    except (psutil.NoSuchProcess, psutil.AccessDenied):
        return "N/A"

def check_ports():
    """Memeriksa port yang terbuka dan proses yang menggunakannya."""
    connections = psutil.net_connections(kind='inet')
    port_info = []

    for conn in connections:
        if conn.status == 'LISTEN':
            local_address = conn.laddr
            pid = conn.pid
            process_name = get_process_name(pid) if pid else "N/A"
            
            # Format alamat dan port
            if local_address.ip == '0.0.0.0' or local_address.ip == '::':
                address = f"*:{local_address.port}"
            else:
                address = f"{local_address.ip}:{local_address.port}"

            port_info.append([
                local_address.port,
                address,
                pid or "N/A",
                process_name
            ])

    # Urutkan berdasarkan nomor port
    return sorted(port_info, key=lambda x: x[0])

def print_header():
    """Mencetak header aplikasi."""
    header_table = [
        [f"{Fore.CYAN}{Style.BRIGHT}Port Scanner{Style.RESET_ALL}"]
    ]
    print(tabulate(header_table, tablefmt="double_grid", headers=[""], colalign=("center",)))

def main():
    parser = argparse.ArgumentParser(description="Port Scanner - Memeriksa port yang terbuka")
    parser.add_argument("-p", "--port", type=int, help="Filter port spesifik")
    args = parser.parse_args()

    print_header()
    print(f"\n{Fore.YELLOW}Memindai port yang terbuka...{Style.RESET_ALL}\n")

    port_info = check_ports()

    # Filter port jika diminta
    if args.port:
        port_info = [info for info in port_info if info[0] == args.port]
        if not port_info:
            print(f"{Fore.RED}Port {args.port} tidak ditemukan dalam keadaan LISTEN.{Style.RESET_ALL}")
            return

    # Tampilkan hasil dalam tabel
    headers = [
        f"{Fore.CYAN}Port{Style.RESET_ALL}",
        f"{Fore.CYAN}Address{Style.RESET_ALL}",
        f"{Fore.CYAN}PID{Style.RESET_ALL}",
        f"{Fore.CYAN}Process{Style.RESET_ALL}"
    ]
    
    print(tabulate(port_info, headers=headers, tablefmt="pretty"))

if __name__ == "__main__":
    main()
