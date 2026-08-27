<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Radar Klien — Calon Klien Website</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link
    href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap"
    rel="stylesheet">
  <!-- Leaflet (Peta interaktif - issue #7) -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <style>
    :root {
      --bg: #10121a;
      --panel: #1a1d29;
      --panel-2: #22263566;
      --line: #2b3040;
      --text: #e9e9ef;
      --muted: #8b8fa3;
      --amber: #e8a33d;
      --amber-dim: #e8a33d22;
      --green: #3ecf8e;
      --green-dim: #3ecf8e1f;
      --red: #e2574c;
      --red-dim: #e2574c1f;
      --wa: #25D366;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text);
      padding: 32px 20px 60px;
    }

    .wrap {
      max-width: 1200px;
      margin: 0 auto;
    }

    header {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 28px;
    }

    h1 {
      font-family: 'Space Grotesk', sans-serif;
      font-weight: 700;
      font-size: 1.9rem;
      margin: 0;
      letter-spacing: -0.02em;
    }

    h1 span {
      color: var(--amber);
    }

    .tagline {
      color: var(--muted);
      font-size: 0.85rem;
      margin-top: 4px;
    }

    .top-actions {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .btn {
      background: var(--panel);
      border: 1px solid var(--line);
      color: var(--text);
      padding: 8px 13px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.82rem;
      cursor: pointer;
    }
    .btn:hover { border-color: var(--amber); }
    .btn.amber { background: var(--amber); color: #10121a; border-color: var(--amber); }

    /* Stat Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
      gap: 14px;
      margin-bottom: 26px;
    }
    .stat-card {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 16px 18px;
      cursor: pointer;
    }
    .stat-card.active {
      border-color: var(--amber);
    }
    .stat-card .n {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 1.7rem;
      font-weight: 700;
    }
    .stat-card .l {
      color: var(--muted);
      font-size: 0.78rem;
      text-transform: uppercase;
      letter-spacing: .06em;
      margin-top: 2px;
    }
    .stat-card.new .n { color: var(--text); }
    .stat-card.contacted .n { color: var(--amber); }
    .stat-card.deal .n { color: var(--green); }
    .stat-card.rejected .n { color: var(--red); }
    .stat-card.noweb .n { color: var(--wa); }

    /* Category Tabs */
    .category-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 10px;
        margin-bottom: 20px;
        -webkit-overflow-scrolling: touch;
    }
    .category-tabs::-webkit-scrollbar { height: 6px; }
    .category-tabs::-webkit-scrollbar-thumb { background: var(--line); border-radius: 3px; }

    .category-tab-btn {
      background: var(--panel);
      border: 1px solid var(--line);
      color: var(--text);
      padding: 7px 14px;
      border-radius: 20px;
      font-size: 0.8rem;
      white-space: nowrap;
      cursor: pointer;
    }
    .category-tab-btn.active { border-color: var(--amber); color: var(--amber); }

    /* Toolbar */
    .toolbar {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      margin-bottom: 18px;
      align-items: center;
    }
    .toolbar input, .toolbar select {
      background: var(--panel);
      border: 1px solid var(--line);
      color: var(--text);
      padding: 9px 12px;
      border-radius: 8px;
      font-size: 0.85rem;
    }
    .toolbar input { min-width: 240px; }
    .toolbar label { color: var(--muted); font-size: 0.82rem; display: flex; align-items: center; gap: 6px; cursor: pointer; }

    /* Table */
    .panel {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 12px;
      overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead th {
      text-align: left;
      padding: 13px 14px;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: .05em;
      color: var(--muted);
      border-bottom: 1px solid var(--line);
      background: #151823;
    }
    thead th.sortable { cursor: pointer; user-select: none; }
    thead th.sortable:hover { color: var(--amber); }
    tbody td {
      padding: 12px 14px;
      border-bottom: 1px solid var(--line);
      font-size: 0.84rem;
      vertical-align: top;
    }
    tbody tr.row:hover { background: #151823; }
    tbody tr.row.selected { background: var(--amber-dim); }

    .biz { font-weight: 600; }
    .jenis { color: var(--muted); }
    .loc { max-width: 200px; font-size: 0.8rem; }
    .muted { color: var(--muted); }

    .badge {
      -webkit-appearance: none;
      -moz-appearance: none;
      appearance: none;
      border: 1px solid;
      border-radius: 6px;
      padding: 5px 26px 5px 9px;
      font-size: 0.78rem;
      background-color: transparent;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'><path d='M1 3l4 4 4-4' stroke='%238b8fa3' stroke-width='1.5' fill='none'/></svg>");
      background-repeat: no-repeat;
      background-position: right 9px center;
      color: var(--text);
      cursor: pointer;
    }
    .badge.new { background: var(--panel-2); color: var(--text); border-color: var(--line); }
    .badge.contacted { background: var(--amber-dim); color: var(--amber); border-color: #e8a33d40; }
    .badge.deal { background: var(--green-dim); color: var(--green); border-color: #3ecf8e40; }
    .badge.rejected { background: var(--red-dim); color: var(--red); border-color: #e2574c40; }

    .stars { color: var(--amber); letter-spacing: 1px; }
    .stars .empty { color: var(--line); }
    .rev { color: var(--muted); font-size: 0.75rem; }

    .actions { display: flex; gap: 8px; align-items: center; }
    .wa-btn {
      background: var(--wa);
      color: #08130d;
      padding: 7px 13px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.82rem;
      white-space: nowrap;
    }
    .wa-btn:hover { filter: brightness(1.08); }
    .del-btn {
      background: transparent;
      border: 1px solid var(--line);
      color: var(--red);
      padding: 6px 10px;
      border-radius: 8px;
      font-size: 0.8rem;
      cursor: pointer;
    }
    .del-btn:hover { border-color: var(--red); }

    .map-link { color: var(--amber); text-decoration: none; display: inline-block; font-size: 0.8rem; }
    .map-link:hover { color: #fff; text-decoration: underline; }

    .notes-input {
      width: 100%; min-height: 50px; padding: 8px;
      border: 1px solid var(--line); border-radius: 8px;
      background: var(--panel-2); color: var(--text);
      resize: vertical; font-size: 0.8rem;
    }

    .last-contact { color: var(--muted); font-size: 0.72rem; margin-top: 4px; }

    /* Pagination */
    .pagination { display: flex; gap: 6px; justify-content: center; padding: 16px; flex-wrap: wrap; }
    .pagination a, .pagination span {
      padding: 7px 12px; border-radius: 7px; border: 1px solid var(--line);
      background: var(--panel); color: var(--text); text-decoration: none; font-size: 0.82rem;
    }
    .pagination .active { border-color: var(--amber); color: var(--amber); }
    .pagination .disabled { opacity: .4; }

    .empty { text-align: center; color: var(--muted); padding: 50px 20px; font-size: 0.9rem; }
    .note { margin-top: 18px; color: var(--muted); font-size: 0.78rem; text-align: center; }

    /* Map */
    #map { height: 360px; border-radius: 12px; border: 1px solid var(--line); margin-bottom: 24px; }
    .leaflet-popup-content { font-size: 0.82rem; }

    @media (max-width: 640px) {
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .toolbar { flex-direction: column; }
      .toolbar input { width: 100%; min-width: unset; }
      .toolbar select { width: 100%; }
      table, thead, tbody, th, td, tr { display: block; }
      thead { display: none; }
      tr.row { border-bottom: 1px solid var(--line); padding: 12px 4px; }
      td { border: none; padding: 4px 12px; display: flex; justify-content: space-between; gap: 10px; }
      td::before { content: attr(data-label); color: var(--muted); font-size: 0.72rem; text-transform: uppercase; }
      #map { display: none; }
    }
  </style>
</head>

<body>
  <div class="wrap">
    <header>
      <div>
        <h1>Radar <span>Klien</span></h1>
        <div class="tagline">Bisnis Bandung tanpa website — hasil scrape Google Maps ({{ $total }} lead)</div>
        <div style="margin-top: 10px;">
           <a href="/cara_jualan.txt" target="_blank" style="color:var(--green); font-weight:bold; text-decoration:none;">[Cek Jurus Jualan Sat Set]</a>
        </div>
      </div>
      <div class="top-actions">
        <a class="btn amber" href="/export">⬇ Export CSV</a>
        <a class="btn" href="/logout">Logout</a>
      </div>
    </header>

    <div class="stats-grid">
      <div class="stat-card new" onclick="filterByStatus('')">
        <div class="n" id="cTotal">{{ $total }}</div>
        <div class="l">Total Klien</div>
      </div>
      <div class="stat-card noweb" onclick="filterByStatus('__noweb')">
        <div class="n" id="cNoWeb">{{ $counts['no_website'] ?? 0 }}</div>
        <div class="l">Tanpa Website</div>
      </div>
      <div class="stat-card contacted" onclick="filterByStatus('contacted')">
        <div class="n" id="cDihubungi">{{ $counts['contacted'] ?? 0 }}</div>
        <div class="l">Sudah Dihubungi</div>
      </div>
      <div class="stat-card deal" onclick="filterByStatus('deal')">
        <div class="n" id="cDeal">{{ $counts['deal'] ?? 0 }}</div>
        <div class="l">Deal</div>
      </div>
      <div class="stat-card rejected" onclick="filterByStatus('rejected')">
        <div class="n" id="cGagal">{{ $counts['rejected'] ?? 0 }}</div>
        <div class="l">Tidak Lanjut</div>
      </div>
    </div>

    <div class="toolbar">
      <input id="search" type="text" placeholder="Cari nama / jenis / HP / alamat..." value="{{ $filters['keyword'] ?? '' }}">
      <select id="filterStatus">
        <option value="" {{ !($filters['status'] ?? '') ? 'selected' : '' }}>Semua status</option>
        <option value="new" {{ ($filters['status'] ?? '')=='new' ? 'selected' : '' }}>Baru</option>
        <option value="contacted" {{ ($filters['status'] ?? '')=='contacted' ? 'selected' : '' }}>Sudah Dihubungi</option>
        <option value="deal" {{ ($filters['status'] ?? '')=='deal' ? 'selected' : '' }}>Deal</option>
        <option value="rejected" {{ ($filters['status'] ?? '')=='rejected' ? 'selected' : '' }}>Tidak Lanjut</option>
      </select>
      <select id="filterCategory">
        <option value="">Semua kategori</option>
        @foreach($categories as $cat)
        <option value="{{ $cat }}" {{ ($filters['category'] ?? '')==$cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
      <label><input type="checkbox" id="filterNoWeb" {{ ($filters['no_website'] ?? '')=='1' ? 'checked' : '' }}> Tanpa website</label>
      <select id="filterSort">
        <option value="" {{ !($filters['sort'] ?? '') ? 'selected' : '' }}>Urut: Terbaru</option>
        <option value="prospect" {{ ($filters['sort'] ?? '')=='prospect' ? 'selected' : '' }}>Prospek Terbaik</option>
      </select>
    </div>

    <div id="map" style="display:none"></div>

    <div class="panel">
      <table id="clientTable">
        <thead>
          <tr>
            <th>Nama Bisnis</th>
            <th>Jenis</th>
            <th>No. HP</th>
            <th>Lokasi</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Terakhir Kontak</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clients as $c)
          <tr class="row" data-name="{{ strtolower($c->business_name) }}"
            data-jenis="{{ strtolower($c->category ?? '') }}" data-status="{{ $c->status }}"
            data-hp="{{ strtolower($c->phone_number ?? '') }}" data-alamat="{{ strtolower($c->address ?? '') }}"
            data-lat="{{ $c->latitude }}" data-lng="{{ $c->longitude }}"
            data-id="{{ $c->id }}">
            <td data-label="Bisnis" class="biz">{{ $c->business_name }}</td>
            <td data-label="Jenis" class="jenis">{{ $c->category ?? '-' }}</td>
            <td data-label="No. HP" class="hp">{{ $c->phone_number ?? '-' }}</td>
            <td data-label="Lokasi" class="loc">
              @if($c->latitude && $c->longitude)
              <a class="map-link" target="_blank"
                href="https://www.google.com/maps?q={{ $c->latitude }},{{ $c->longitude }}">
                📍 {{ Str::limit($c->address ?? '', 60) }}
              </a>
              @else
              <span class="muted">{{ Str::limit($c->address ?? '-', 60) }}</span>
              @endif
            </td>
            <td data-label="Rating">
              @if($c->rating > 0)
                <span class="stars">{{ str_repeat('★', round($c->rating)) }}<span class="empty">{{ str_repeat('★', 5 - round($c->rating)) }}</span></span>
                <div class="rev">{{ number_format($c->rating, 1) }} ({{ $c->review_count }})</div>
              @else
                <span class="muted">-</span>
              @endif
            </td>
            <td data-label="Status">
              <select class="badge {{ $c->status }}" data-id="{{ $c->id }}" onchange="updateStatus(this)">
                <option value="new" {{ $c->status=='new'?'selected':'' }}>Baru</option>
                <option value="contacted" {{ $c->status=='contacted'?'selected':'' }}>Sudah Dihubungi</option>
                <option value="deal" {{ $c->status=='deal'?'selected':'' }}>Deal</option>
                <option value="rejected" {{ $c->status=='rejected'?'selected':'' }}>Tidak Lanjut</option>
              </select>
            </td>
            <td data-label="Catatan">
              <textarea class="notes-input" data-id="{{ $c->id }}" placeholder="Tambah catatan..." onblur="updateNotes(this)">{{ $c->notes }}</textarea>
            </td>
            <td data-label="Terakhir Kontak" class="muted">
              @if($c->last_contacted_at)
                {{ $c->last_contacted_at->format('d/m/Y H:i') }}
              @else
                -
              @endif
            </td>
            <td data-label="Aksi" class="actions">
              @if($c->phone_number)
              @php
              $digits = preg_replace('/\D/', '', $c->phone_number);
              if (str_starts_with($digits, '0')) { $digits = '62' . substr($digits, 1); }
              elseif (!str_starts_with($digits, '62')) { $digits = '62' . $digits; }
              $waMsg = "Halo Kak, selamat siang. Saya Putra. Saya lihat {$c->business_name} di Google Maps ulasannya sudah bagus! Kebetulan saya lihat di profilnya belum ada link website. Saya bisa bantu buatin website simpel—supaya pelanggan baru bisa langsung cek daftar harga/layanan dan ada tombol langsung terhubung ke WhatsApp Kakak. Boleh saya kirim contoh tampilan websitenya, Kak? Barangkali cocok untuk {$c->business_name}.";
              $waLink = "https://wa.me/{$digits}?text=" . urlencode($waMsg);
              @endphp
              <a class="wa-btn" target="_blank" href="{{ $waLink }}" onclick="markContacted({{ $c->id }}, this)">Chat WA</a>
              @else
              <span class="wa-btn" style="background:#3a3f52;color:#8b8fa3;cursor:not-allowed">No WA</span>
              @endif
              <button class="del-btn" data-id="{{ $c->id }}" onclick="deleteLead(this)">🗑</button>
            </td>
          </tr>
          @empty
          <tr class="row">
            <td colspan="9" class="empty">Belum ada data. Jalankan scraper dulu.</td>
          </tr>
          @endforelse
        </tbody>
      </table>

      @if($clients->hasPages())
      <div class="pagination">
        {{ $clients->links('pagination') }}
      </div>
      @endif
    </div>

    <div class="note">
      Data real dari MySQL — status auto-save ke DB.
      Scrape: python scraper.py --query "Jenis bisnis di Bandung" --limit N
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    const search = document.getElementById('search');
    const filterStatus = document.getElementById('filterStatus');
    const filterCategory = document.getElementById('filterCategory');
    const filterNoWeb = document.getElementById('filterNoWeb');
    const filterSort = document.getElementById('filterSort');
    const rows = document.querySelectorAll('#clientTable tbody tr.row');

    let currentStatusFilter = '';
    let currentCategoryFilter = '';
    let noWebOnly = false;
    let sortBy = '';

    // '__noweb' sentinel dipakai kartu "Tanpa Website"
    function filterByStatus(status) {
      if (status === '__noweb') {
        currentStatusFilter = '';
        noWebOnly = true;
        filterNoWeb.checked = true;
        filterStatus.value = '';
      } else {
        currentStatusFilter = status;
        noWebOnly = false;
        filterNoWeb.checked = false;
        filterStatus.value = status;
      }
      applyFilter();
    }

    function filterByCategory(category) {
      currentCategoryFilter = category;
      filterCategory.value = category;
      applyFilter();
    }

    function applyFilter() {
      const q = search.value.toLowerCase().trim();
      rows.forEach(r => {
        const matchesText = r.dataset.name.includes(q) || r.dataset.jenis.includes(q)
          || r.dataset.hp.includes(q) || r.dataset.alamat.includes(q);
        const matchesStatus = !currentStatusFilter || r.dataset.status === currentStatusFilter;
        const matchesKategori = !currentCategoryFilter || r.dataset.jenis === currentCategoryFilter.toLowerCase();
        const matchesNoWeb = !noWebOnly || (r.dataset.hp === '' ? true : true); // placeholder; server-side filter utama
        r.style.display = (matchesText && matchesStatus && matchesKategori) ? '' : 'none';
      });
      recount();
    }

    function recount() {
      const visible = [...rows].filter(r => r.style.display !== 'none');
      document.getElementById('cTotal').textContent = visible.length;
      document.getElementById('cDihubungi').textContent = visible.filter(r => r.dataset.status === 'contacted').length;
      document.getElementById('cDeal').textContent = visible.filter(r => r.dataset.status === 'deal').length;
      document.getElementById('cGagal').textContent = visible.filter(r => r.dataset.status === 'rejected').length;

      document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
      let active = '';
      if (noWebOnly) active = 'noweb';
      else if (currentStatusFilter === '') active = 'new';
      else active = currentStatusFilter;
      const target = document.querySelector(`.stat-card.${active}`);
      if (target) target.classList.add('active');
    }

    search.addEventListener('input', applyFilter);
    filterStatus.addEventListener('change', (e) => { currentStatusFilter = e.target.value; noWebOnly = false; applyFilter(); });
    filterCategory.addEventListener('change', (e) => filterByCategory(e.target.value));
    filterNoWeb.addEventListener('change', (e) => { noWebOnly = e.target.checked; currentStatusFilter = ''; filterStatus.value=''; applyFilter(); });
    filterSort.addEventListener('change', (e) => { sortBy = e.target.value; submitFilters(); });

    // Server-side filter submit (no_website + sort butuh query DB)
    function submitFilters() {
      const params = new URLSearchParams();
      if (search.value) params.set('keyword', search.value);
      if (filterStatus.value) params.set('status', filterStatus.value);
      if (filterCategory.value) params.set('category', filterCategory.value);
      if (filterNoWeb.checked) params.set('no_website', '1');
      if (filterSort.value) params.set('sort', filterSort.value);
      window.location.search = params.toString();
    }

    // Initial
    applyFilter();

    // ---- Auto-save ke DB ----
    async function updateNotes(textareaEl) {
      const id = textareaEl.dataset.id;
      const notes = textareaEl.value;
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
      try {
        const response = await fetch(`/leads/${id}/notes`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify({ notes: notes })
        });
        if (!response.ok) throw new Error('Update notes failed');
      } catch (error) {
        console.error('Error updating notes:', error);
        alert('Gagal update catatan.');
      }
    }

    async function updateStatus(selectEl) {
      const newStatus = selectEl.value;
      selectEl.className = 'badge ' + newStatus;
      selectEl.closest('tr').dataset.status = newStatus;
      try {
        const res = await fetch('/leads/' + selectEl.dataset.id + '/status', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
          body: JSON.stringify({ status: newStatus })
        });
        if (!res.ok) throw new Error('Update status failed');
        const data = await res.json();
        if (data.last_contacted_at) {
          const td = selectEl.closest('tr').querySelector('td[data-label="Terakhir Kontak"]');
          if (td) td.textContent = data.last_contacted_at;
        }
        applyFilter();
      } catch (error) {
        console.error('Error updating status:', error);
        alert('Gagal update status.');
      }
    }

    // Klik Chat WA -> auto set contacted (issue #6)
    async function markContacted(id, el) {
      const row = el.closest('tr');
      if (row.dataset.status === 'new') {
        try {
          const res = await fetch('/leads/' + id + '/status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ status: 'contacted' })
          });
          if (res.ok) {
            const data = await res.json();
            const sel = row.querySelector('select.badge');
            sel.value = 'contacted'; sel.className = 'badge contacted';
            row.dataset.status = 'contacted';
            if (data.last_contacted_at) {
              const td = row.querySelector('td[data-label="Terakhir Kontak"]');
              if (td) td.textContent = data.last_contacted_at;
            }
            applyFilter();
          }
        } catch (e) { console.error(e); }
      }
    }

    async function deleteLead(btn) {
      if (!confirm('Hapus lead ini?')) return;
      const id = btn.dataset.id;
      try {
        const res = await fetch('/leads/' + id, {
          method: 'DELETE',
          headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (res.ok) { btn.closest('tr').remove(); recount(); }
        else alert('Gagal hapus.');
      } catch (e) { alert('Gagal hapus.'); }
    }

    // ---- Leaflet map (issue #7) ----
    let map, markers = {};
    function initMap() {
      const withCoord = [...rows].filter(r => r.dataset.lat && r.dataset.lng);
      if (!withCoord.length) return;
      const mapEl = document.getElementById('map');
      mapEl.style.display = 'block';
      map = L.map('map').setView([parseFloat(withCoord[0].dataset.lat), parseFloat(withCoord[0].dataset.lng)], 12);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
      withCoord.forEach(r => {
        const id = r.dataset.id;
        const m = L.marker([parseFloat(r.dataset.lat), parseFloat(r.dataset.lng)])
          .addTo(map)
          .bindPopup(`<b>${r.querySelector('.biz').textContent}</b><br>${r.dataset.jenis}`);
        markers[id] = m;
        m.on('click', () => { highlightRow(id); });
        r.addEventListener('click', () => { if (map) { map.setView(m.getLatLng(), 14); m.openPopup(); } });
      });
    }
    function highlightRow(id) {
      document.querySelectorAll('tr.row').forEach(r => r.classList.remove('selected'));
      const row = document.querySelector(`tr.row[data-id="${id}"]`);
      if (row) row.classList.add('selected');
    }
    if (document.getElementById('map') && typeof L !== 'undefined') initMap();
    // fix: peta butuh invalidateSize setelah tampil
    if (map) setTimeout(() => map.invalidateSize(), 200);
  </script>
</body>
</html>
