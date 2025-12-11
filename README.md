# Pengelola Layanan Laravel FrankenPHP

Proyek ini adalah alat bantu untuk memudahkan pengelolaan layanan Laravel FrankenPHP di lingkungan Linux. Alat ini membantu pengembang dan administrator sistem untuk:

- Memeriksa status layanan _staging_ dan _production_.
- Menjalankan layanan.
- Menghentikan layanan.
- Memulai ulang (me-restart) layanan.

Keunggulan utama dari alat ini adalah:

- Menyederhanakan penggunaan perintah `systemctl` yang terkadang kompleks.
- Menghemat waktu dalam pengelolaan layanan.
- Mengurangi potensi kesalahan pengetikan perintah.

Alat ini menawarkan dua mode penggunaan:

1. Antarmuka baris perintah (CLI) yang efisien.
2. Antarmuka web berbasis Streamlit yang intuitif dan menarik secara visual.

## Fitur Utama

- **Pemeriksaan Status**: Memantau status layanan Laravel FrankenPHP (_staging_ dan _production_) secara _real-time_.
- **Manajemen Layanan**: Kontrol penuh untuk memulai, menghentikan, dan memulai ulang layanan.
- **Tampilan Berwarna (CLI)**: _Output_ berwarna pada CLI untuk memudahkan identifikasi status layanan.
- **Format Tabel (CLI)**: Penyajian informasi dalam format tabel yang terstruktur dan mudah dibaca.
- **Dua Pilihan Antarmuka**: Fleksibilitas penggunaan melalui CLI atau antarmuka web Streamlit.

## Teknologi yang Digunakan

- Python 3.8+ (direkomendasikan versi 3.10+ untuk menikmati fitur terbaru Streamlit).
- `colorama`: Untuk _output_ teks berwarna pada antarmuka CLI.
- `tabulate`: Untuk menampilkan data dalam format tabel yang rapi pada CLI.
- Modul `subprocess`: Untuk eksekusi perintah _shell_ dari dalam skrip Python.
- Modul `argparse`: Untuk pengelolaan argumen dan parameter pada antarmuka CLI.
- Streamlit: Untuk membangun antarmuka web yang interaktif dan modern.
- PIL (Pillow): Untuk generasi gambar Captcha pada fitur login antarmuka web.

## Persyaratan Sistem

- Python 3.8 atau versi yang lebih baru (direkomendasikan Python 3.10+ untuk kompatibilitas penuh dengan fitur Streamlit terkini).
- Sistem operasi Linux dengan SystemD sebagai manajer layanan.

## Instalasi

1. Clone repositori ini:

   ```bash
   git clone https://github.com/username/laravel-frankenphp-service-manager.git
   cd laravel-frankenphp-service-manager
   ```

2. Instal dependensi Python yang diperlukan:

   ```bash
   pip install colorama tabulate streamlit pillow
   ```

3. Berikan izin eksekusi pada skrip utama:

   ```bash
   chmod +x cekservice.py
   ```

4. (Opsional) Buat direktori dan file konfigurasi `secrets.toml` jika Anda ingin menggunakan fitur otentikasi dan koneksi basis data pada antarmuka web:

   ```bash
   mkdir -p .streamlit
   touch .streamlit/secrets.toml
   ```

   Kemudian, tambahkan konfigurasi yang relevan ke dalam file `secrets.toml`:

   ```toml
   # Contoh Konfigurasi Otentikasi (jika menggunakan fitur login)
   [auth]
   redirect_uri = "http://localhost:8501/oauth2callback"
   cookie_secret = "kunci_rahasia_acak_anda"
   client_id = "id_klien_anda"
   client_secret = "rahasia_klien_anda"

   # Contoh Konfigurasi Koneksi Basis Data (jika diperlukan)
   [connections.db]
   type = "sql"
   url = "sqlite:///database.db"
   ```

## Penggunaan

### Antarmuka Baris Perintah (CLI)

Skrip CLI dapat dijalankan dengan berbagai parameter untuk mengelola layanan:

```bash
python3 cekservice.py --service [staging|production|all] --action [status|start|stop|restart]
```

Parameter yang tersedia:

- `--service`: Menentukan target layanan yang akan dikelola.

  - `staging`: Mengelola layanan _staging_ (laravel-frankenphp-staging).
  - `production`: Mengelola layanan _production_ (laravel-frankenphp-production).
  - `all`: Mengelola semua layanan (nilai _default_ jika parameter ini tidak disertakan).

- `--action`: Menentukan tindakan yang akan dilakukan pada layanan.
  - `status`: Memeriksa status layanan (tindakan _default_ jika parameter ini tidak disertakan).
  - `start`: Memulai layanan.
  - `stop`: Menghentikan layanan.
  - `restart`: Memulai ulang layanan.

### Contoh Penggunaan CLI

1. Memeriksa status semua layanan (staging dan production):

   ```bash
   python3 cekservice.py
   ```

2. Memeriksa status layanan staging saja:

   ```bash
   python3 cekservice.py --service staging
   ```

3. Memulai ulang layanan production:

   ```bash
   python3 cekservice.py --service production --action restart
   ```

### Antarmuka Web (Streamlit)

Untuk pengalaman pengguna yang lebih visual dan interaktif, Anda dapat menggunakan antarmuka web yang dibangun dengan Streamlit:

```bash
streamlit run cekservice_streamlit.py
```

Fitur antarmuka web meliputi:

- **Antarmuka Visual Modern**: UI yang intuitif dan responsif, memanfaatkan berbagai komponen Streamlit.
- **Sistem Login Aman**: Perlindungan akses menggunakan _username_ dan _password_.
- **Dasbor Informatif**:
  - Tampilan status layanan secara visual dengan indikator warna yang jelas.
  - Tabel data interaktif yang mendukung pencarian dan pengurutan data layanan.
  - Informasi mengenai status kontainer untuk memantau proses yang berjalan (jika relevan).
- **Manajemen Layanan Interaktif**:
  - Kontrol penuh (start, stop, restart) layanan langsung dari antarmuka web.
  - Umpan balik visual secara instan untuk setiap tindakan yang dilakukan.
  - Notifikasi _toast_ untuk konfirmasi keberhasilan atau kegagalan aksi.
- **Dukungan Multi-Layanan**: Kemampuan untuk mengelola beberapa layanan (_staging_ dan _production_) dari satu tempat.
- **Performa Optimal**: Pemanfaatan _caching_ (`st.cache_data` dan `st.cache_resource`) untuk meningkatkan responsivitas aplikasi.
- **Keamanan Kredensial**: Akses aman ke `st.secrets` untuk menyimpan kredensial API atau konfigurasi sensitif lainnya.

#### Kredensial Login Bawaan (Default)

- Nama Pengguna (_Username_): `admin`
- Kata Sandi (_Password_): `sinara123`
- Captcha: Selesaikan tantangan Captcha berupa angka yang ditampilkan pada gambar.

## Struktur Kode

- `cekservice.py`: Skrip utama untuk fungsionalitas CLI, berisi fungsi-fungsi inti untuk pengelolaan layanan.

  - `run_command()`: Mengeksekusi perintah _shell_.
  - `check_service_status()`: Memeriksa status layanan yang ditentukan.
  - `manage_service()`: Melakukan tindakan (start, stop, restart) pada layanan.
  - `print_header()`: Mencetak _header_ informasi aplikasi pada CLI.
  - `main()`: Fungsi utama yang mengatur alur eksekusi skrip CLI.

- `cekservice_streamlit.py`: Skrip untuk antarmuka web berbasis Streamlit.
  - `run_command()`: Mengeksekusi perintah _shell_ (serupa dengan versi CLI).
  - `check_service_status()`: Memeriksa status layanan dengan _output_ yang disesuaikan untuk antarmuka web.
  - `manage_service()`: Mengelola layanan dengan _output_ dan interaksi yang disesuaikan untuk antarmuka web.
  - `generate_captcha_image()`: Menghasilkan gambar Captcha untuk proses login.
  - `login_page()`: Mengatur tampilan dan logika halaman login, termasuk otentikasi pengguna.
  - `main()`: Fungsi utama yang menjalankan dan mengatur alur aplikasi web Streamlit.

## Keamanan

- Skrip ini dirancang untuk hanya menjalankan perintah `systemctl` yang telah didefinisikan secara spesifik, guna meminimalkan risiko.
- Penerapan validasi _input_ untuk mencegah potensi serangan injeksi perintah.
- Penggunaan antarmuka web dilindungi dengan mekanisme berikut:
  - Sistem login yang memerlukan _username_ dan _password_.
  - Implementasi Captcha untuk mencegah serangan _brute-force_.
  - Manajemen sesi pengguna untuk melacak status otentikasi secara aman.
- Akses `sudo` tetap diperlukan untuk menjalankan perintah `systemctl` yang fundamental.

## Pemecahan Masalah (Troubleshooting)

### Masalah Akses Sudo

Jika Anda mengalami kesalahan terkait izin (`permission denied`) saat menjalankan skrip, pastikan pengguna yang menjalankan skrip memiliki hak akses `sudo` yang memadai untuk perintah `systemctl`.

1. Tambahkan pengguna Anda ke grup `sudo` (jika belum):

   ```bash
   sudo usermod -aG sudo nama_pengguna_anda
   ```

   _(Ganti `nama_pengguna_anda` dengan nama pengguna Linux Anda)_

2. Atau, untuk pendekatan yang lebih granular, konfigurasikan file `sudoers` agar pengguna dapat menjalankan perintah `systemctl` spesifik tanpa perlu memasukkan kata sandi setiap saat. **Hati-hati saat mengedit file `sudoers`.**

   Buat file baru di direktori `/etc/sudoers.d/` (misalnya, `service-manager`):

   ```bash
   echo "nama_pengguna_anda ALL=(ALL) NOPASSWD: /bin/systemctl status laravel-frankenphp-*, /bin/systemctl start laravel-frankenphp-*, /bin/systemctl stop laravel-frankenphp-*, /bin/systemctl restart laravel-frankenphp-*" | sudo tee /etc/sudoers.d/service-manager
   sudo chmod 440 /etc/sudoers.d/service-manager
   ```

   _(Ganti `nama_pengguna_anda` dengan nama pengguna Linux Anda)_

### Layanan Tidak Ditemukan

Jika skrip melaporkan bahwa layanan tidak ditemukan, pastikan:

- Nama layanan yang Anda targetkan sudah benar (misalnya, `laravel-frankenphp-staging` atau `laravel-frankenphp-production`).
- Layanan tersebut sudah terdaftar dan aktif di SystemD. Anda bisa memeriksanya dengan:
  ```bash
  systemctl list-units --type=service | grep frankenphp
  ```

### Masalah Umum Antarmuka Web

1.  **Streamlit Tidak Terinstal/Versi Lama**: Pastikan Streamlit terinstal dan versi terbaru.
    ```bash
    pip install streamlit --upgrade
    ```
2.  **Masalah Captcha (Pillow)**: Jika Captcha tidak muncul atau error, pastikan pustaka Pillow terinstal.
    ```bash
    pip install pillow --upgrade
    ```
3.  **Port Sudah Digunakan**: Jika port _default_ Streamlit (biasanya 8501) sudah digunakan, jalankan dengan port lain:
    ```bash
    streamlit run cekservice_streamlit.py --server.port 8502
    ```
4.  **Koneksi Basis Data (jika menggunakan `secrets.toml`)**: Periksa kembali konfigurasi URL basis data dalam `secrets.toml`.
    ```toml
    [connections.db]
    type = "sql"
    url = "sqlite:///database.db" # Pastikan path dan format URL benar
    ```
5.  **Fitur Otentikasi (jika menggunakan `secrets.toml`)**: Verifikasi kredensial, `redirect_uri`, dan konfigurasi lainnya di `secrets.toml`. Untuk _debugging_, Anda mungkin perlu menjalankan Streamlit dengan opsi tertentu (gunakan dengan hati-hati):
    ```bash
    # Opsi ini dapat mengurangi keamanan, gunakan hanya untuk debugging lokal jika diperlukan
    streamlit run cekservice_streamlit.py --server.enableCORS=false --server.enableXsrfProtection=false
    ```
6.  **Masalah Caching**: Jika Anda mencurigai adanya masalah dengan data yang di-_cache_ oleh Streamlit (`st.cache_data` atau `st.cache_resource`), Anda dapat mencoba membersihkan _cache_ secara manual dalam kode atau dengan memulai ulang aplikasi sepenuhnya. Untuk membersihkan _cache_ secara terprogram (tambahkan jika diperlukan untuk _debugging_ dalam skrip Anda):
    ```python
    # Contoh membersihkan semua cache data
    # st.cache_data.clear()
    # st.cache_resource.clear()
    ```

## Kontribusi

Kontribusi untuk pengembangan proyek ini sangat kami hargai! Jika Anda ingin berkontribusi, silakan ikuti langkah-langkah berikut:

1. _Fork_ repositori ini ke akun GitHub Anda.
2. Buat _branch_ baru untuk fitur atau perbaikan yang Anda kerjakan (`git checkout -b nama-fitur-atau-perbaikan`).
3. Lakukan perubahan dan _commit_ kode Anda (`git commit -m 'Deskripsi singkat perubahan Anda'`).
4. _Push_ perubahan ke _branch_ Anda di repositori _fork_ (`git push origin nama-fitur-atau-perbaikan`).
5. Buat _Pull Request_ ke repositori utama.

## Lisensi

Proyek ini dilisensikan di bawah [Lisensi MIT](LICENSE).

## Kontak

Jika Anda memiliki pertanyaan, saran, atau membutuhkan dukungan lebih lanjut, jangan ragu untuk menghubungi melalui:

- Email: admin@example.com (Harap ganti dengan alamat email kontak yang valid)
- Isu GitHub: [YourUsername]/[RepoName]/issues (Harap ganti dengan tautan isu GitHub yang sesuai)

## CREATE FLASK KEY

python3 -c "import secrets; print('FLASK_SECRET_KEY=' + secrets.token_hex(32))" >> .env
