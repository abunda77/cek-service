# Pengelola Layanan Laravel FrankenPHP

Pengelola Layanan Laravel FrankenPHP adalah alat command line sederhana untuk memonitor dan mengelola layanan Laravel FrankenPHP di lingkungan server Linux. Aplikasi ini memudahkan administrator untuk mengecek status, memulai, menghentikan, atau me-restart layanan Laravel FrankenPHP tanpa perlu mengetikkan perintah systemctl yang panjang.

## Fitur Utama

- **Pemeriksaan Status**: Memeriksa status layanan Laravel FrankenPHP (staging dan production)
- **Pengelolaan Layanan**: Dapat melakukan start, stop, dan restart layanan
- **Tampilan Berwarna**: Output berwarna untuk memudahkan pembacaan status
- **Format Tabel**: Menampilkan informasi dalam format tabel yang rapi
- **Dua Antarmuka**: CLI (Command Line Interface) dan Web Interface menggunakan Streamlit

## Teknologi yang Digunakan

- Python 3.6+
- Colorama untuk output berwarna
- Tabulate untuk tampilan tabel
- Subproses untuk eksekusi perintah shell
- Argparse untuk pengelolaan parameter command line
- Streamlit untuk web interface
- PIL (Pillow) untuk generasi captcha

## Persyaratan Sistem

- Python 3.6 atau lebih tinggi
- Sistem operasi Linux dengan SystemD
- Akses sudo untuk menjalankan perintah systemctl
- Paket Python: colorama, tabulate, streamlit, pillow

## Instalasi

1. Clone repositori ini:

   ```bash
   git clone https://github.com/username/laravel-frankenphp-service-manager.git
   cd laravel-frankenphp-service-manager
   ```

2. Instal dependensi Python:

   ```bash
   pip install colorama tabulate streamlit pillow
   ```

3. Berikan izin eksekusi ke script:

   ```bash
   chmod +x cekservice.py
   ```

## Penggunaan

### Command Line Interface (CLI)

Script dapat dijalankan dengan berbagai parameter:

```bash
python3 cekservice.py --service [staging|production|all] --action [status|start|stop|restart]
```

Parameter yang tersedia:

- `--service`: Menentukan layanan yang akan dikelola

  - `staging`: Hanya layanan staging (laravel-frankenphp-staging)
  - `production`: Hanya layanan production (laravel-frankenphp-production)
  - `all`: Semua layanan (default)

- `--action`: Menentukan aksi yang akan dilakukan
  - `status`: Memeriksa status layanan (default)
  - `start`: Memulai layanan
  - `stop`: Menghentikan layanan
  - `restart`: Me-restart layanan

### Contoh Penggunaan CLI

1. Memeriksa status semua layanan:

   ```bash
   python3 cekservice.py
   ```

2. Memeriksa status layanan staging:

   ```bash
   python3 cekservice.py --service staging
   ```

3. Me-restart layanan production:

   ```bash
   python3 cekservice.py --service production --action restart
   ```

### Web Interface

Aplikasi juga menyediakan antarmuka web berbasis Streamlit untuk penggunaan yang lebih visual dan user-friendly:

```bash
streamlit run cekservice_streamlit.py
```

Fitur web interface:

- **Antarmuka Visual**: UI web yang intuitif dan modern
- **Sistem Login**: Terlindungi dengan username, password, dan captcha
- **Dashboard**: Tampilan status layanan dengan indikator warna
- **Manajemen Layanan**: Kontrol penuh untuk start, stop, dan restart layanan
- **Multi-layanan**: Mengelola layanan staging dan production sekaligus atau secara individual

#### Login Credentials (Default)

- Username: admin
- Password: sinara123
- Captcha: Angka yang ditampilkan dalam gambar captcha

## Struktur Kode

- `cekservice.py`: Script utama dengan fungsi-fungsi untuk mengelola layanan

  - `run_command()`: Menjalankan perintah shell
  - `check_service_status()`: Memeriksa status layanan
  - `manage_service()`: Mengelola layanan (start, stop, restart)
  - `print_header()`: Mencetak header aplikasi
  - `main()`: Fungsi utama yang menjalankan aplikasi

- `cekservice_streamlit.py`: Web interface menggunakan Streamlit
  - `run_command()`: Menjalankan perintah shell
  - `check_service_status()`: Memeriksa status layanan dengan output yang disesuaikan untuk web
  - `manage_service()`: Mengelola layanan dengan output yang disesuaikan untuk web
  - `generate_captcha_image()`: Menghasilkan gambar captcha untuk keamanan login
  - `login_page()`: Menampilkan halaman login dengan autentikasi
  - `main()`: Fungsi utama yang menjalankan aplikasi web

## Keamanan

- Script hanya menjalankan perintah systemctl yang terdefinisi
- Validasi input untuk mencegah injeksi perintah
- Diperlukan akses sudo untuk menjalankan perintah systemctl
- Web interface dilindungi dengan:
  - Sistem login dengan username dan password
  - Captcha untuk mencegah brute force
  - Manajemen sesi untuk melacak status otentikasi

## Pemecahan Masalah

### Masalah Akses Sudo

Jika terjadi kesalahan terkait izin, pastikan pengguna yang menjalankan script memiliki akses sudo ke perintah systemctl:

1. Tambahkan pengguna ke grup sudo:

   ```bash
   sudo usermod -aG sudo username
   ```

2. Atau konfigurasikan sudoers untuk mengizinkan akses tanpa password ke systemctl:

   ```bash
   echo "username ALL=(ALL) NOPASSWD: /bin/systemctl status laravel-frankenphp-*, /bin/systemctl start laravel-frankenphp-*, /bin/systemctl stop laravel-frankenphp-*, /bin/systemctl restart laravel-frankenphp-*" | sudo tee /etc/sudoers.d/service-manager
   sudo chmod 440 /etc/sudoers.d/service-manager
   ```

### Layanan Tidak Ditemukan

Jika layanan tidak ditemukan, pastikan nama layanan sudah benar dan layanan sudah terdaftar di systemd:

```bash
systemctl list-units --type=service | grep frankenphp
```

### Masalah Web Interface

1. Pastikan Streamlit sudah terinstal dengan benar:

   ```bash
   pip install streamlit --upgrade
   ```

2. Jika ada masalah dengan captcha, pastikan Pillow terinstal:

   ```bash
   pip install pillow --upgrade
   ```

3. Jika mengalami masalah port yang digunakan Streamlit, gunakan opsi port spesifik:

   ```bash
   streamlit run cekservice_streamlit.py --server.port 8501
   ```

## Kontribusi

Kontribusi sangat dipersilakan! Untuk berkontribusi:

1. Fork repositori ini
2. Buat branch fitur baru (`git checkout -b fitur-baru`)
3. Commit perubahan Anda (`git commit -m 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin fitur-baru`)
5. Buat Pull Request

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## Kontak

Untuk pertanyaan atau dukungan lebih lanjut, silakan hubungi:

- Email: admin@example.com
- GitHub: [YourUsername](https://github.com/yourusername)
