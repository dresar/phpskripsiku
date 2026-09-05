# Menjalankan Project Tanpa PHP/Composer di PATH

Jika di terminal muncul **"php is not recognized"** atau **"composer is not recognized"**, artinya PHP/Composer belum ada di **PATH** Windows. Di komputer Anda terdeteksi:

- **PHP**: `C:\xampp\php\php.exe` (XAMPP)
- **Composer**: `C:\ProgramData\ComposerSetup\bin\composer.bat`

## Solusi 1: Pakai skrip PowerShell (paling cepat)

Dari folder project, jalankan:

```powershell
# Tambah PATH sementara lalu jalankan composer
.\run-with-php.ps1 composer install

# Jalankan server
.\run-with-php.ps1 php -S localhost:8000 router.php
```

Untuk subscriber MQTT (terminal lain):

```powershell
.\run-with-php.ps1 php mqtt/subscriber.php
```

Simulasi data:

```powershell
.\run-with-php.ps1 php scripts/simulate_insert.php
```

---

## Solusi 2: Tambah PHP & Composer ke PATH permanen

Agar perintah `php` dan `composer` bisa dipanggil dari mana saja:

1. Buka **Edit environment variables for your account**:
   - Tekan `Win + R`, ketik `sysdm.cpl`, Enter
   - Tab **Advanced** → **Environment Variables**
   - Di **User variables** (atau System variables), pilih **Path** → **Edit**

2. Klik **New** dan tambahkan (sesuaikan jika beda):
   ```
   C:\xampp\php
   C:\ProgramData\ComposerSetup\bin
   ```

3. **OK** sampai semua jendela tertutup.

4. **Tutup lalu buka lagi** terminal/PowerShell (atau restart Cursor).

5. Cek:
   ```powershell
   php -v
   composer --version
   ```

Setelah itu Anda bisa langsung pakai:

```powershell
composer install
php -S localhost:8000 router.php
```

---

## Jika pakai Laragon / PHP lain

Edit file `run-with-php.ps1`, ubah baris:

```powershell
$phpPath = "C:\xampp\php"
```

menjadi path folder PHP Anda, misalnya:

- Laragon: `C:\laragon\bin\php\php-8.x.x-Win32-v16-x64`
- PHP standalone: `C:\php`

Composer biasanya tetap di `C:\ProgramData\ComposerSetup\bin` jika diinstall via installer Windows.
