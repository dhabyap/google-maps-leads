# Issues Radar Klien — audit 2026-08-27

## P0
### 1. Tambah auth login dashboard (P0)
Dashboard + data HP/alamat lead kelihatan publik kalau di-deploy. Belum ada login sama sekali.
AC:
- Buat route login/logout + middleware `auth` untuk GET `/`.
- Password dari env `DASHBOARD_PASSWORD` (bcrypt). Jangan hardcode.
- Session Laravel biasa (file driver). Setelah login baru bisa lihat tabel.
- Endpoint API scraper (`/api/leads/*`) tetap pakai `X-Api-Key`, jangan ikut auth session.

### 2. Fix rating kosong (P0 / bug)
Di DB `rating` = 0 untuk semua 66 lead (rating>0 = 0), padahal kolom ada & scraper punya parser.
AC:
- Cek scraper.py:128 (parse aria-label "X,X (N)") benar saat jalan / data masuk `rating` & `review_count`.
- Cek LeadController upsert: `rating` default `0.0` — pastikan tidak menimpa nilai asli jadi 0.
- Setelah fix, re-scrape sample & pastikan minimal sebagian lead punya rating > 0.
- Tambah kolom rating + review_count ke tabel Blade.

## P1
### 3. Filter "tanpa website" (P1)
33/66 lead sudah punya website_url. Value prop "bisnis tanpa web" gak bisa disaring.
AC:
- Tambah toggle/filter "Tanpa Website" di toolbar.
- Filter by `website_url IS NULL OR website_url = ''`.
- Stat card "Tanpa Web" opsional.

### 4. Kolom rating + sort prioritas prospek (P1)
Lead score (rating × review_count) buat ranking prospek terbaik belum ada.
AC:
- Tampilkan kolom Rating (bintang) + Review di tabel.
- Tambah sort: klik header urutkan asc/desc, atau dropdown "Prospek Terbaik" (rating*review desc).
- Tidak mengubah status manual.

### 5. Export CSV (P1)
Follow-up di luar web butuh tarik data.
AC:
- Route `GET /export` (atau `/api/leads/export`) → download CSV.
- Kolom: business_name, category, phone_number, website_url, address, rating, review_count, status, notes.
- Format HP ke 62xxx biar bisa di-import ke WA blast.

### 6. Klik Chat WA auto-set status contacted (P1)
Sekarang user harus manual ganti dropdown setelah chat.
AC:
- Saat klik "Chat WA", fire POST `/leads/{id}/status` dengan `contacted` (kalau status masih `new`).
- Update UI badge + counts tanpa reload.

## P2
### 7. Peta interaktif (P2)
Nama "Radar Klien" minta map. Sekarang cuma link per-row.
AC:
- Tambah Leaflet map, marker per lead pakai lat/lng.
- Klik marker highlight row di tabel (dan sebaliknya).

### 8. Pagination (P2)
Belum ada. 66 masih oke, ribuan nanti berat (dashboard `->get()` semua).
AC:
- Gunakan paginate() di RadarController, tampilkan pagination di bawah tabel.
- Filter tetap jalan per halaman.

### 9. Delete / edit lead (P2)
Salah scrape gak bisa dihapus/ubah.
AC:
- Tambah tombol delete per row (confirm dulu), route `DELETE /leads/{id}`.
- Edit manual nama/alamat via modal (opsional).

### 10. Search cover HP & alamat (P2)
Frontend cuma cari nama+jenis. DB support phone/address tapi gak dipakai.
AC:
- Update `applyFilter()` di Blade: cocokkan juga `data-hp` & `data-alamat`.
- Tambah `data-hp` / `data-alamat` di `<tr>`.

### 11. Fix bug highlight kartu "Semua" (P2 / bug)
Klik kartu Total/Baru malah highlight "Baru" terus.
AC:
- Di `recount()`, kartu aktif harus ikut `currentStatusFilter` ('' = Total, bukan 'new').
- Cek radar.blade.php:~578.

### 12. Timestamp "terakhir dihubungi" (P2)
Cuma notes, gak tau kapan terakhir kontak.
AC:
- Tambah kolom `last_contacted_at` (nullable timestamp) di model + migration.
- Isi saat status → contacted. Tampilkan di tabel.
