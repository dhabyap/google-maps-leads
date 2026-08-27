# Radar Klien

Dashboard prospek website dari hasil scrape Google Maps. Backend Laravel 13,
frontend Blade (vanilla JS), scraper Python + Playwright.

Cocok buat: cari bisnis di suatu kota yang **belum punya website**, lalu di-follow
up lewat WhatsApp.

---

## Fitur sekarang

- Tabel lead: nama bisnis, jenis, no HP, lokasi (link Google Maps), status, catatan, aksi.
- Stat card: Total / Sudah Dihubungi / Deal / Tidak Lanjut (bisa di-klik buat filter).
- Filter: search teks + dropdown status + tab kategori.
- Edit status & catatan langsung tersimpan ke DB (auto-save, tanpa reload).
- Tombol "Chat WA" → buka wa.me dengan pesan jualan otomatis.
- API ingestion buat scraper (`POST /api/leads/upsert`).

> Lihat daftar yang mau dibikin di tab **Issues** (label P0 / P1 / P2).

---

## Setup lokal (junior, ikuti urut)

### 1. Prereq
- PHP >= 8.2 + Composer
- MySQL (Laragon punya ini)
- Python 3.10+ + pip (buat scraper)

### 2. Clone
```bash
git clone https://github.com/dhabyap/google-maps-leads.git
cd google-maps-leads
```

### 3. Composer install
```bash
composer install
```

### 4. Env
```bash
cp .env.example .env
php artisan key:generate
```
Lalu edit `.env`, set DB + API key:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=google_maps_leads
DB_USERNAME=root
DB_PASSWORD=

SCRAPER_API_KEY=gm-leads-scraper-2026   # harus sama dengan yang dipakai scraper
```
> `SCRAPER_API_KEY` WAJIB ada & cocok dengan yang di-pakai scraper (lihat bawah).
> Jangan commit `.env` — sudah di-ignore.

### 5. Buat database + migration
```bash
# di MySQL: CREATE DATABASE google_maps_leads;
php artisan migrate
```
Migration `create_google_map_clients_table` akan buat tabel `google_map_clients`.

### 6. Jalanin server
```bash
php artisan serve --host=127.0.0.1 --port=8002
```
Buka http://127.0.0.1:8002/

---

## Scraper (isi data)

Scraper pakai Playwright (bukan API Google resmi) → butuh Chromium.

```bash
pip install playwright
python -m playwright install chromium
```

Jalanin:
```bash
# default API target: http://127.0.0.1:8021/api/leads/upsert
python scraper.py --query "Laundry di Bandung" --limit 50

# dry run (cuma print, gak upload):
python scraper.py --query "Apotek di Bandung" --limit 30 --no-upload

# lihat browser (debug):
python scraper.py --query "Kafe di Bandung" --limit 20 --headful
```

### Port mismatch (PENTING)
Scraper default nembak `:8021`, tapi `php artisan serve` di atas jalan di `:8002`.
Dua cara bikin cocok:

**Cara A — jalanin serve di 8021:**
```bash
php artisan serve --host=127.0.0.1 --port=8021
```

**Cara B — override URL scraper:**
```bash
python scraper.py --query "Laundry di Bandung" --limit 50 \
  --api-url http://127.0.0.1:8002/api/leads/upsert
```

API key diambil dari arg `--api-key` atau env `SCRAPER_API_KEY` (default `gm-leads-scraper-2026`).
Harus sama dengan `SCRAPER_API_KEY` di `.env` Laravel.

---

## Struktur

```
app/Http/Controllers/
  RadarController.php   # dashboard GET / + update status/notes
  LeadController.php    # API upsert + list (protected X-Api-Key)
app/Models/
  GoogleMapClient.php   # model tabel google_map_clients
resources/views/
  radar.blade.php       # tampilan dashboard (Blade + vanilla JS)
routes/
  web.php  api.php
scraper.py              # Playwright scraper -> POST /api/leads/upsert
```

## DB: tabel `google_map_clients`

| kolom | keterangan |
|-------|-----------|
| google_place_id | unique key dari Google Maps (upsert key) |
| business_name | nama bisnis |
| category | jenis bisnis |
| phone_number | no HP (bisa kosong) |
| website_url | website (kosong = prospek "tanpa web") |
| address, latitude, longitude | lokasi |
| rating, review_count | kualitas (lihat issue #2) |
| search_keyword | query saat scrape |
| status | new / contacted / deal / rejected |
| notes | catatan manual |

## Catatan keamanan (lihat issue #1)
Dashboard belum pakai login. Jangan expose ke publik sebelum auth dibikin.
Data lead (HP/alamat) akan kelihatan semua orang.
