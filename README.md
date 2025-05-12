# Pengelola Layanan Laravel FrankenPHP

Proyek ini adalah alat bantu sederhana untuk memudahkan pengaturan layanan Laravel FrankenPHP di Linux. Dengan tool ini, pengembang dan admin sistem dapat:

- Memeriksa status layanan staging dan production
- Menjalankan layanan
- Menghentikan layanan
- Me-restart layanan

Keunggulan utamanya adalah:

- Menggantikan perintah `systemctl` yang rumit
- Menghemat waktu
- Mengurangi kesalahan pengetikan

Tersedia dua cara penggunaan:

1. Antarmuka baris perintah (CLI)
2. Antarmuka web menggunakan Streamlit yang lebih mudah dan menarik

## Fitur Utama

- **Pemeriksaan Status**: Memeriksa status layanan Laravel FrankenPHP (staging dan production)
- **Pengelolaan Layanan**: Memulai, menghentikan, dan me-restart layanan
- **Tampilan Berwarna**: Output berwarna untuk memudahkan pembacaan status
- **Format Tabel**: Menampilkan informasi dalam format tabel yang rapi
- **Dua Antarmuka**: CLI (Command Line Interface) dan Web Interface menggunakan Streamlit

## Teknologi yang Digunakan

- Python 3.8+ (direkomendasikan untuk fitur terbaru Streamlit)
- Colorama untuk output berwarna
- Tabulate untuk tampilan tabel
- Subproses untuk eksekusi perintah shell
- Argparse untuk pengelolaan parameter command line
- Streamlit untuk web interface dengan fitur modern
- PIL (Pillow) untuk generasi captcha

## Persyaratan Sistem

- Python 3.8 atau lebih tinggi (direkomendasikan 3.10+ untuk semua fitur terbaru Streamlit)
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

3. Berikan izin eksekusi pada skrip:

   ```bash
   chmod +x cekservice.py
   ```

4. (Opsional) Buat file konfigurasi secrets.toml untuk fitur otentikasi dan koneksi database:

   ```bash
   mkdir -p .streamlit
   touch .streamlit/secrets.toml
   ```

   Dan tambahkan konfigurasi yang diperlukan:

   ```toml
   # Konfigurasi otentikasi (jika menggunakan fitur login)
   [auth]
   redirect_uri = "http://localhost:8501/oauth2callback"
   cookie_secret = "random_secret_key"
   client_id = "your_client_id"
   client_secret = "your_client_secret"

   # Konfigurasi database (jika menggunakan koneksi database)
   [connections.db]
   type = "sql"
   url = "sqlite:///database.db"
   ```

## Penggunaan

### Command Line Interface (CLI)

Skrip ini dapat dijalankan dengan berbagai parameter:

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

Aplikasi ini juga menyediakan antarmuka web dengan Streamlit agar lebih mudah digunakan dan tampilannya lebih menarik:

```bash
streamlit run cekservice_streamlit.py
```

Fitur web interface:

- **Antarmuka Visual**: UI web yang intuitif dan modern dengan fitur-fitur terbaru Streamlit
- **Sistem Login**: Terlindungi dengan username, password, dan captcha
- **Dashboard**:
  - Tampilan status layanan dengan indikator warna
  - Tabel data interaktif dengan fitur pencarian dan pengurutan
  - Status container untuk menampilkan proses yang sedang berjalan
- **Manajemen Layanan**:
  - Kontrol penuh untuk start, stop, dan restart layanan
  - Feedback visual langsung untuk setiap aksi
  - Notifikasi toast untuk konfirmasi aksi
- **Multi-layanan**:
  - Mengelola layanan staging dan production sekaligus atau secara individual
  - Tampilan status real-time
  - Layout responsif dengan kolom yang dapat disesuaikan
- **Fitur Terbaru Streamlit**:
  - Antarmuka chat (`st.chat_input` dan `st.chat_message`) untuk interaksi lebih intuitif
  - Editor data (`st.data_editor`) untuk visualisasi dan pengeditan data interaktif
  - Koneksi database bawaan (`st.connection`) untuk integrasi SQL, PostgreSQL, atau Snowflake dengan mudah
  - Otentikasi pengguna (`st.login`) untuk mengamankan aplikasi dengan Google atau Microsoft
  - Navigasi halaman otomatis (`st.switch_page`) untuk pengalaman multi-halaman yang lancar
  - Caching yang dioptimalkan (`st.cache_data` dan `st.cache_resource`) untuk performa yang lebih baik
  - Akses ke rahasia (`st.secrets`) untuk menyimpan kredensial API dengan aman

#### Login Credentials (Default)

- Username: admin
- Password: sinara123
- Captcha: Angka yang ditampilkan dalam gambar captcha

## Struktur Kode

- `cekservice.py`: Skrip utama berisi fungsi-fungsi untuk mengelola layanan

  - `run_command()`: Menjalankan perintah shell
  - `check_service_status()`: Memeriksa status layanan
  - `manage_service()`: Mengelola layanan (start, stop, restart)
  - `print_header()`: Mencetak header aplikasi
  - `main()`: Fungsi utama yang menjalankan aplikasi

- `cekservice_streamlit.py`: Antarmuka web yang dibuat menggunakan Streamlit
  - `run_command()`: Menjalankan perintah shell
  - `check_service_status()`: Memeriksa status layanan dengan output yang disesuaikan untuk web
  - `manage_service()`: Mengelola layanan dengan output yang disesuaikan untuk web
  - `generate_captcha_image()`: Menghasilkan gambar captcha untuk keamanan login
  - `login_page()`: Menampilkan halaman login dengan autentikasi
  - `main()`: Fungsi utama yang menjalankan aplikasi web

## Keamanan

- Skrip ini hanya menjalankan perintah `systemctl` yang sudah ditentukan.
- Validasi input untuk mencegah injeksi perintah
- Diperlukan akses sudo untuk menjalankan perintah systemctl
- Antarmuka web dilindungi dengan:
  - Sistem login dengan username dan password
  - Captcha untuk mencegah brute force
  - Manajemen sesi untuk melacak status otentikasi

## Pemecahan Masalah

### Masalah Akses Sudo

Jika ada kesalahan terkait izin, pastikan pengguna yang menjalankan skrip memiliki akses `sudo` untuk perintah `systemctl`:

1. Tambahkan pengguna ke grup sudo:

   ```bash
   sudo usermod -aG sudo username
   ```

2. Atau, konfigurasikan `sudoers` agar dapat mengakses `systemctl` tanpa kata sandi:

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

4. Untuk masalah koneksi database, pastikan konfigurasi URL database di file secrets.toml sudah benar:

   ```toml
   [connections.db]
   type = "sql"
   url = "sqlite:///database.db"
   ```

5. Jika fitur otentikasi tidak berfungsi, periksa kredensial dan URL redirect di file secrets.toml:

   ```bash
   streamlit run cekservice_streamlit.py --server.enableCORS=false --server.enableXsrfProtection=false
   ```

6. Jika mengalami masalah caching (`st.cache_data` atau `st.cache_resource`), Anda dapat menghapus cache:

   ```python
   st.cache_data.clear()
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
