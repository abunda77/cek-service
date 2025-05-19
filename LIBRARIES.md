# Dokumentasi Library

Dokumen ini berisi penjelasan detail tentang library-library yang digunakan dalam proyek Pengelola Layanan Laravel FrankenPHP.

## Streamlit

### Versi yang Digunakan
- Versi minimum: 1.31.0
- Direkomendasikan: Versi terbaru (1.32.0 atau lebih tinggi)

### Deskripsi
Streamlit adalah framework Python yang memungkinkan pembuatan aplikasi web dengan cepat dan mudah. Dalam proyek ini, Streamlit digunakan untuk membangun antarmuka web yang interaktif dan responsif.

### Fitur Utama yang Digunakan
- `st.dataframe`: Menampilkan data dalam format tabel interaktif dengan fitur pencarian dan pengurutan
- `st.status`: Menampilkan status container untuk proses yang sedang berjalan
- `st.toast`: Menampilkan notifikasi non-intrusif
- `st.cache_data`: Mengoptimalkan performa dengan menyimpan hasil komputasi
- `st.session_state`: Mengelola state aplikasi
- `st.form`: Membuat form input dengan validasi
- `st.columns`: Mengatur layout dengan sistem kolom responsif

### Penggunaan dalam Proyek
```python
import streamlit as st

# Contoh penggunaan komponen Streamlit
st.title("Dashboard Layanan")
st.dataframe(service_status_df)  # Menampilkan status layanan
with st.status("Memperbarui status..."):
    update_service_status()
st.toast("Layanan berhasil diperbarui!")
```

## Colorama

### Versi yang Digunakan
- Versi minimum: 0.4.6

### Deskripsi
Colorama adalah library yang memungkinkan penggunaan warna ANSI dalam output terminal pada Windows. Library ini digunakan untuk memberikan output berwarna pada antarmuka CLI.

### Fitur Utama yang Digunakan
- `Fore`: Mengatur warna teks (HITAM, MERAH, HIJAU, KUNING, BIRU, MAGENTA, CYAN, PUTIH)
- `Back`: Mengatur warna latar belakang
- `Style`: Mengatur gaya teks (BRIGHT, DIM, NORMAL, RESET_ALL)
- `init()`: Inisialisasi dukungan warna

### Penggunaan dalam Proyek
```python
from colorama import init, Fore, Back, Style

# Inisialisasi colorama
init()

# Contoh penggunaan
print(Fore.GREEN + "Layanan Aktif" + Style.RESET_ALL)
print(Fore.RED + "Layanan Tidak Aktif" + Style.RESET_ALL)
```

## Tabulate

### Versi yang Digunakan
- Versi minimum: 0.9.0

### Deskripsi
Tabulate adalah library untuk membuat tabel ASCII dari data Python. Library ini digunakan untuk menampilkan informasi dalam format tabel yang rapi pada CLI.

### Fitur Utama yang Digunakan
- Format tabel: 'grid', 'pipe', 'orgtbl', 'rst'
- Dukungan untuk header
- Perataan kolom otomatis
- Pengaturan lebar kolom

### Penggunaan dalam Proyek
```python
from tabulate import tabulate

# Contoh penggunaan
data = [["Staging", "Aktif", "3 hari"], ["Production", "Tidak Aktif", "1 jam"]]
headers = ["Layanan", "Status", "Uptime"]
print(tabulate(data, headers=headers, tablefmt="grid"))
```

## PIL (Python Imaging Library/Pillow)

### Versi yang Digunakan
- Versi minimum: 10.0.0

### Deskripsi
Pillow adalah fork dari PIL yang menyediakan dukungan untuk manipulasi gambar. Dalam proyek ini, Pillow digunakan untuk menghasilkan gambar CAPTCHA pada sistem login.

### Fitur Utama yang Digunakan
- Pembuatan gambar baru
- Menggambar teks pada gambar
- Manipulasi warna dan filter
- Konversi format gambar

### Penggunaan dalam Proyek
```python
from PIL import Image, ImageDraw, ImageFont

def generate_captcha():
    # Membuat gambar baru
    img = Image.new('RGB', (200, 100), color='white')
    d = ImageDraw.Draw(img)
    
    # Menambahkan teks
    font = ImageFont.truetype("arial.ttf", 36)
    d.text((20, 20), "1234", font=font, fill='black')
    
    return img
```

## Subprocess

### Versi yang Digunakan
- Modul bawaan Python (tidak perlu instalasi terpisah)

### Deskripsi
Subprocess adalah modul bawaan Python untuk menjalankan perintah shell eksternal. Dalam proyek ini, digunakan untuk mengeksekusi perintah systemctl.

### Fitur Utama yang Digunakan
- `run()`: Menjalankan perintah dan menunggu hingga selesai
- `Popen`: Menjalankan perintah secara asynchronous
- Pengaturan input/output dan error handling
- Manajemen proses child

### Penggunaan dalam Proyek
```python
import subprocess

def run_command(command):
    try:
        result = subprocess.run(
            command,
            shell=True,
            check=True,
            capture_output=True,
            text=True
        )
        return result.stdout
    except subprocess.CalledProcessError as e:
        return e.stderr
```

## Argparse

### Versi yang Digunakan
- Modul bawaan Python (tidak perlu instalasi terpisah)

### Deskripsi
Argparse adalah modul bawaan Python untuk parsing argumen baris perintah. Digunakan untuk mengelola parameter CLI dalam aplikasi.

### Fitur Utama yang Digunakan
- Pendefinisian argumen
- Validasi input otomatis
- Pembuatan pesan bantuan
- Penanganan error

### Penggunaan dalam Proyek
```python
import argparse

def parse_arguments():
    parser = argparse.ArgumentParser(
        description='Pengelola Layanan Laravel FrankenPHP'
    )
    parser.add_argument(
        '--service',
        choices=['staging', 'production', 'all'],
        default='all',
        help='Layanan yang akan dikelola'
    )
    parser.add_argument(
        '--action',
        choices=['status', 'start', 'stop', 'restart'],
        default='status',
        help='Aksi yang akan dilakukan'
    )
    return parser.parse_args()
```

## Penggunaan Library Tambahan

Selain library utama di atas, proyek ini juga memanfaatkan beberapa fitur dari Streamlit yang lebih advanced:

### Streamlit Cache
```python
@st.cache_data
def get_service_status():
    # Fungsi untuk mendapatkan status layanan
    pass
```

### Streamlit Session State
```python
if "authentication_status" not in st.session_state:
    st.session_state.authentication_status = False
```

### Streamlit Secrets
```python
# Mengakses kredensial dari secrets.toml
username = st.secrets["auth"]["username"]
password = st.secrets["auth"]["password"]
```

## Catatan Penting

1. Pastikan semua versi library yang digunakan kompatibel satu sama lain.
2. Gunakan virtual environment untuk mengisolasi dependensi proyek.
3. Selalu periksa pembaruan keamanan untuk setiap library yang digunakan.
4. Jalankan `pip freeze > requirements.txt` setelah menambah atau memperbarui library.
