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
      max-width: 1100px;
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

    /* Stat Cards */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
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


    /* Category Tabs */
    .category-tabs {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 8px;
        padding-bottom: 10px; /* For scrollbar */
        margin-bottom: 20px;
        -webkit-overflow-scrolling: touch; /* iOS smooth scrolling */
    }
    .category-tabs::-webkit-scrollbar {
        height: 6px;
    }
    .category-tabs::-webkit-scrollbar-track {
        background: #2a2d39;
        border-radius: 10px;
    }
    .category-tabs::-webkit-scrollbar-thumb {
        background: var(--amber);
        border-radius: 10px;
    }
    .category-tab-btn {
        flex-shrink: 0;
        background: var(--panel);
        color: var(--text);
        border: 1px solid var(--line);
        padding: 8px 15px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.85rem;
        transition: all 0.2s ease-in-out;
    }
    .category-tab-btn.active {
        background: var(--amber);
        border-color: var(--amber);
        color: var(--bg);
        font-weight: bold;
    }
    .category-tab-btn:hover:not(.active) {
        background: var(--line);
    }


    .toolbar {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 18px;
    }

    .toolbar input,
    .toolbar select {
      background: var(--panel);
      border: 1px solid var(--line);
      color: var(--text);
      padding: 10px 14px;
      border-radius: 9px;
      font-family: 'Inter';
      font-size: 0.9rem;
    }

    .toolbar input {
      flex: 1;
      min-width: 200px;
    }

    .toolbar input:focus,
    .toolbar select:focus {
      outline: 1px solid var(--amber);
    }

    .panel {
      background: var(--panel);
      border: 1px solid var(--line);
      border-radius: 14px;
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed; /* Fix kolom */
    }

    th {
      background: #151824;
      text-align: left;
      padding: 13px 16px;
      font-size: 0.72rem;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: var(--muted);
      font-weight: 600;
      border-bottom: 1px solid var(--line);
      position: sticky; /* Sticky Header */
      top: 0;
      z-index: 10;
    }

    td {
      padding: 10px 16px; /* Compact Rows */
      border-bottom: 1px solid var(--line);
      font-size: 0.88rem; /* Compact Font */
      vertical-align: middle;
      word-wrap: break-word; /* Wrap long text */
    }

    tr:last-child td {
      border-bottom: none;
    }

    tr.row:hover {
      background: var(--panel-2);
    }

    .biz {
      font-weight: 600;
      width: 20%; /* Lebar kolom nama bisnis */
    }
    .jenis { width: 15%; }
    .hp { width: 12%; }
    .loc { width: 20%; }
    .status { width: 10%; }
    .catatan { width: 15%; }
    .aksi { width: 8%; }


    .jenis {
      color: var(--muted);
      font-size: 0.82rem;
    }

    .hp {
      font-family: 'JetBrains Mono', monospace;
      font-size: 0.85rem;
      color: var(--muted);
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 11px;
      border-radius: 999px;
      font-size: 0.76rem;
      font-weight: 600;
      border: 1px solid transparent;
      cursor: pointer;
    }

    .badge.baru {
      background: #ffffff10;
      color: var(--muted);
      border-color: var(--line);
    }

    .badge.dihubungi {
      background: var(--amber-dim);
      color: var(--amber);
      border-color: #e8a33d40;
    }

    .badge.deal {
      background: var(--green-dim);
      color: var(--green);
      border-color: #3ecf8e40;
    }

    .badge.ga_lanjut {
      background: var(--red-dim);
      color: var(--red);
      border-color: #e2574c40;
    }

    .badge.new {
      background: #ffffff10;
      color: var(--muted);
      border-color: var(--line);
    }

    .badge.contacted {
      background: var(--amber-dim);
      color: var(--amber);
      border-color: #e8a33d40;
    }

    .badge.deal {
      background: var(--green-dim);
      color: var(--green);
      border-color: #3ecf8e40;
    }

    .badge.rejected {
      background: var(--red-dim);
      color: var(--red);
      border-color: #e2574c40;
    }

    .actions {
      display: flex;
      gap: 8px;
      align-items: center;
    }

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

    .wa-btn:hover {
      filter: brightness(1.08);
    }

    .map-link {
      color: var(--amber);
      text-decoration: none;
      display: inline-block;
      font-size: 0.8rem;
    }

    .map-link:hover {
      color: #fff;
      text-decoration: underline;
    }

    .loc {
      max-width: 200px;
      font-size: 0.8rem; /* biar muat di kolom */
    }

    .notes-input {
      width: 100%;
      min-height: 50px;
      padding: 8px;
      border: 1px solid var(--line);
      border-radius: 8px;
      background: var(--panel-2);
      color: var(--text);
      resize: vertical;
      font-size: 0.8rem;
    }

    /* Override colspan for empty row */
    td.empty {
      colspan: 7;
    }

    .empty {
      text-align: center;
      color: var(--muted);
      padding: 50px 20px;
      font-size: 0.9rem;
    }

    .note {
      margin-top: 18px;
      color: var(--muted);
      font-size: 0.78rem;
      text-align: center;
    }

    @media (max-width: 640px) {
      .stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      .toolbar {
        flex-direction: column;
      }
      .toolbar input {
        width: 100%;
        min-width: unset;
      }
      .toolbar select {
        width: 100%;
      }

      table,
      thead,
      tbody,
      th,
      td,
      tr {
        display: block;
      }

      thead {
        display: none;
      }

      tr.row {
        border-bottom: 1px solid var(--line);
        padding: 12px 4px;
      }

      td {
        border: none;
        padding: 4px 12px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
      }

      td::before {
        content: attr(data-label);
        color: var(--muted);
        font-size: 0.72rem;
        text-transform: uppercase;
      }
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
           <a href="/cara_jualan.txt" target="_blank" style="color:var(--jade); font-weight:bold; text-decoration:none;">[Cek Jurus Jualan Sat Set]</a>
        </div>
      </div>
    </header>

    <div class="stats-grid">
      <div class="stat-card new" onclick="filterByStatus('')">
        <div class="n" id="cTotal">{{ $total }}</div>
        <div class="l">Total Klien</div>
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
      <input id="search" type="text" placeholder="Cari nama bisnis atau jenis...">
      <select id="filterStatus">
        <option value="">Semua status</option>
        <option value="new">Baru</option>
        <option value="contacted">Sudah Dihubungi</option>
        <option value="deal">Deal</option>
        <option value="rejected">Tidak Lanjut</option>
      </select>
    </div>

    <div class="category-tabs" id="categoryTabs">
        <button class="category-tab-btn active" onclick="filterByCategory('')">Semua Kategori</button>
        @foreach($categories as $cat)
        <button class="category-tab-btn" onclick="filterByCategory('{{ $cat }}')">{{ $cat }}</button>
        @endforeach
    </div>

    <div class="panel">
      <table id="clientTable">
        <thead>
          <tr>
            <th>Nama Bisnis</th>
            <th>Jenis</th>
            <th>No. HP</th>
            <th>Lokasi</th>
            <th>Status</th>
            <th>Catatan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clients as $c)
          <tr class="row" data-name="{{ strtolower($c->business_name) }}"
            data-jenis="{{ strtolower($c->category ?? '') }}" data-status="{{ $c->status }}"
            data-lat="{{ $c->latitude }}" data-lng="{{ $c->longitude }}">
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
            <td data-label="Aksi" class="actions">
              @if($c->phone_number)
              @php
              $digits = preg_replace('/\D/', '', $c->phone_number);
              if (str_starts_with($digits, '0')) { $digits = '62' . substr($digits, 1); }
              elseif (!str_starts_with($digits, '62')) { $digits = '62' . $digits; }
              $waMsg = "Halo Kak, selamat siang. Saya Putra. Saya lihat {$c->business_name} di Google Maps ulasannya sudah bagus! Kebetulan saya lihat di profilnya belum ada link website. Saya bisa bantu buatin website simpel—supaya pelanggan baru bisa langsung cek daftar harga/layanan dan ada tombol langsung terhubung ke WhatsApp Kakak. Boleh saya kirim contoh tampilan websitenya, Kak? Barangkali cocok untuk {$c->business_name}.";
              $waLink = "https://wa.me/{$digits}?text=" . urlencode($waMsg);
              @endphp
              <a class="wa-btn" target="_blank" href="{{ $waLink }}">Chat WA</a>
              @else
              <span class="wa-btn" style="background:#3a3f52;color:#8b8fa3;cursor:not-allowed">No WA</span>
              @endif
            </td>
          </tr>
          @empty
          <tr class="row">
            <td colspan="7" class="empty">Belum ada data. Jalankan scraper dulu.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="note">
      Data real dari MySQL — status auto-save ke DB. 
      Scrape: python scraper.py --query "Jenis bisnis di Bandung" --limit N
    </div>
  </div>

  <script>
    const search = document.getElementById('search');
const filterStatus = document.getElementById('filterStatus');
const categoryTabs = document.getElementById('categoryTabs');
const rows = document.querySelectorAll('#clientTable tbody tr.row');

let currentStatusFilter = '';
let currentCategoryFilter = '';

function applyFilter(){
  const q = search.value.toLowerCase().trim();
  rows.forEach(r => {
    const matchesText = r.dataset.name.includes(q) || r.dataset.jenis.includes(q);
    const matchesStatus = !currentStatusFilter || r.dataset.status === currentStatusFilter;
    const matchesKategori = !currentCategoryFilter || r.dataset.jenis === currentCategoryFilter.toLowerCase();
    r.style.display = (matchesText && matchesStatus && matchesKategori) ? '' : 'none';
  });
  recount();
}

function recount(){
  const visible = [...rows].filter(r => r.style.display !== 'none');
  document.getElementById('cTotal').textContent = visible.length;
  document.getElementById('cDihubungi').textContent = visible.filter(r => r.dataset.status === 'contacted').length;
  document.getElementById('cDeal').textContent = visible.filter(r => r.dataset.status === 'deal').length;
  document.getElementById('cGagal').textContent = visible.filter(r => r.dataset.status === 'rejected').length;

  // Update active status for stat cards
  document.querySelectorAll('.stat-card').forEach(card => card.classList.remove('active'));
  // Determine which stat card should be active based on current filters
  let activeStatCard = '';
  if (currentStatusFilter === '') activeStatCard = 'new'; // 'new' status is total leads initially
  else activeStatCard = currentStatusFilter;
  const targetCard = document.querySelector(`.stat-card.${activeStatCard}`);
  if (targetCard) {
    targetCard.classList.add('active');
  }
}

function filterByStatus(status) {
  currentStatusFilter = status;
  // Update active state for stat cards handled in recount()
  applyFilter();
}

function filterByCategory(category) {
  currentCategoryFilter = category;
  document.querySelectorAll('.category-tab-btn').forEach(btn => btn.classList.remove('active'));
  if (category === '') document.querySelector('.category-tab-btn[onclick="filterByCategory(\'\')"]').classList.add('active'); // 'Semua Kategori' button
  else document.querySelector(`.category-tab-btn[onclick="filterByCategory('${category}')"]`).classList.add('active');
  applyFilter();
}

search.addEventListener('input', applyFilter);
filterStatus.addEventListener('change', (e) => {
    filterByStatus(e.target.value);
});

// Initial filter apply
applyFilter();


async function updateNotes(textareaEl) {
  const id = textareaEl.dataset.id;
  const notes = textareaEl.value;
  const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

  try {
    const response = await fetch(`/leads/${id}/notes`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken
      },
      body: JSON.stringify({ notes: notes })
    });
    if (!response.ok) throw new Error('Update notes failed');
    console.log(`Notes for ID ${id} updated.`);
    // Optionally: show a temporary success message
  } catch (error) {
    console.error('Error updating notes:', error);
    alert('Gagal update catatan.');
  }
}

async function updateStatus(selectEl){
  const newStatus = selectEl.value;
  selectEl.className = 'badge ' + newStatus;
  selectEl.closest('tr').dataset.status = newStatus;
  // recount(); // Recount will be called by applyFilter
  try {
    const res = await fetch('/leads/' + selectEl.dataset.id + '/status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ status: newStatus })
    });
    if (!res.ok) throw new Error('Update status failed');
    console.log(`Status for ID ${selectEl.dataset.id} updated to ${newStatus}.`);
    applyFilter(); // Re-apply filter after status change to update counts correctly
  } catch (error) {
    console.error('Error updating status:', error);
    alert('Gagal update status.');
  }
}
  </script>
</body>
</html>
