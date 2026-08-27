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
    $waMsg = "Halo Kak, numpang tanya — {$c->business_name} belum ada website ya? Kalau mau, saya bisa bantu buatin yang simpel aja: langsung ada tombol WhatsApp, pelanggan bisa langsung lihat harga/jadwal. Gratis konsultasi desain dulu, Kak. Boleh minta 5 menit?";
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
