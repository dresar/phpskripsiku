# Smart Latex Quality Monitoring System

Dashboard monitoring IoT untuk penelitian skripsi. Arsitektur: **ESP32 → MQTT Broker → Backend PHP → SQLite → Dashboard Web (Realtime)**.

## Struktur Project

```
/project-root
├── api/
│   ├── latest.php      # 1 data terakhir
│   ├── history.php     # 50 data terakhir (+ filter tanggal)
│   ├── stats.php       # Rata-rata, total, distribusi status
│   ├── export.php      # Export CSV
│   └── mqtt_status.php # Status koneksi MQTT
├── config/
│   ├── database.php
│   └── mqtt.php
├── database/
│   └── monitoring.db   # SQLite (auto-create)
├── logs/               # Log MQTT subscriber (optional)
├── mqtt/
│   └── subscriber.php  # MQTT subscriber → insert ke DB
├── public/
│   └── index.php       # Dashboard
├── scripts/
│   ├── simulate_insert.php   # Testing: insert sample data
│   └── seed_mqtt_heartbeat.php
├── src/
│   ├── Database.php
│   └── ReadingRepository.php
├── bootstrap.php
├── composer.json
├── router.php          # Development server router
└── tailwind.config.js
```

## Requirement

- PHP 8.0+
- Composer
- (Opsional) Broker MQTT mis. Mosquitto di localhost

## Instalasi

### 1. Dependency PHP

Pastikan [Composer](https://getcomposer.org/) terinstall. Lalu:

```bash
cd "c:\Users\eka\Videos\SKRIPSI2025\codingan skipsi\php"
composer install
```

Ini akan menginstall **php-mqtt/client** dan membuat folder `vendor/` serta file `database/monitoring.db` (saat pertama kali akses API atau subscriber).

### 2. Konfigurasi MQTT (opsional)

Edit `config/mqtt.php` atau set environment variable:

- `MQTT_HOST` (default: localhost)
- `MQTT_PORT` (default: 1883)
- `MQTT_CLIENT_ID` (default: latex-monitoring-subscriber)
- `MQTT_USER` / `MQTT_PASS` jika broker pakai auth

## Menjalankan di Localhost

### 1. Jalankan PHP built-in server (dari folder project root)

```bash
php -S localhost:8000 router.php
```

- Dashboard: **http://localhost:8000/**
- API contoh: http://localhost:8000/api/latest.php

### 2. (Opsional) Jalankan MQTT Subscriber

Di terminal terpisah, dengan broker MQTT sudah jalan:

```bash
php mqtt/subscriber.php
```

Subscriber akan subscribe ke topic `latex/monitoring`, decode JSON, lalu insert ke SQLite. Log ke `logs/mqtt_subscriber.log`.

### 3. Testing tanpa ESP32 (simulasi data)

Insert sample data langsung ke SQLite:

```bash
php scripts/simulate_insert.php
```

Agar indikator MQTT “Aktif” (opsional):

```bash
php scripts/seed_mqtt_heartbeat.php
```

Lalu buka **http://localhost:8000/** dan pastikan kartu summary, grafik, tabel, dan filter tanggal berfungsi.

## API Endpoint

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/api/latest.php` | GET | 1 data terakhir (JSON) |
| `/api/history.php` | GET | 50 data terakhir. Query: `limit`, `date_from`, `date_to` |
| `/api/stats.php` | GET | Rata-rata pH/TDS/suhu, total, distribusi status. Query: `date_from`, `date_to` |
| `/api/export.php` | GET | Download CSV. Query: `date_from`, `date_to` |
| `/api/mqtt_status.php` | GET | Status MQTT (aktif jika ada data &lt; 2 menit) |

## Format Payload MQTT

Topic: `latex/monitoring`

```json
{
  "ph": 6.85,
  "tds": 520,
  "suhu": 29.5,
  "status": "Mutu Prima"
}
```

Status yang didukung: Mutu Prima, Mutu Rendah, Terawetkan, Oplos Air, Kontaminasi (warna di dashboard sudah disesuaikan).

## Fitur Dashboard

- **4 kartu**: pH, TDS, Suhu, Status Mutu (warna per kategori)
- **Grafik line**: pH dan TDS realtime (Chart.js)
- **Pie chart**: Distribusi status mutu
- **Tabel**: 10 data terakhir
- **Polling**: AJAX tiap 3 detik ke `latest.php` + history + stats
- **Filter tanggal** dan **Export CSV**
- **Indikator MQTT**: Aktif / Tidak aktif

## Migrasi ke MySQL

1. Ganti isi `config/database.php` dengan kredensial MySQL dan DSN.
2. Buat tabel `readings` di MySQL (ganti `AUTOINCREMENT` dengan `AUTO_INCREMENT`, `REAL` dengan `DOUBLE`, `DATETIME` tetap).
3. Di `src/Database.php`, tambahkan branch untuk driver `mysql` (sudah ada contoh di config) dan buat koneksi PDO sesuai DSN MySQL.

## Security

- Prepared statement PDO di semua query
- Validasi & sanitasi input (tanggal, limit)
- Validasi JSON di subscriber sebelum insert

## License

Untuk keperluan skripsi / pendidikan.
