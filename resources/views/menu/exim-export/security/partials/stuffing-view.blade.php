{{-- Ringkasan "Berita Acara Stuffing" & "Dokumen Pengiriman Barang" setelah truk keluar --}}
@php
    $fmt = fn($v) => $v ? \Carbon\Carbon::parse($v)->isoFormat('HH:mm · D MMM YYYY') : '-';
    $tanggal = $s->gate_load_at ?: $s->gate_open_at;
    $cartonText = $s->carton_count === null ? '-' : $s->carton_count . ' carton';
@endphp
<section class="sg-section">
    <h2 class="sg-section__title">Berita acara stuffing</h2>
    <dl class="sg-card__meta" style="margin:0">
        <dt>Tanggal</dt><dd>{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->isoFormat('dddd, D MMM YYYY') : '-' }}</dd>
        <dt>Truck / Nopol</dt><dd class="sg-mono">{{ $s->nopol ?: '-' }}</dd>
        <dt>Lokasi</dt><dd>{{ $s->gate_lokasi ?: '-' }}</dd>
        <dt>Buyer</dt><dd>{{ $s->buyer ?: '-' }}</dd>
        <dt>Jam buka</dt><dd>{{ $fmt($s->gate_open_at) }}</dd>
        <dt>Jam muat</dt><dd>{{ $fmt($s->gate_load_at) }}</dd>
        <dt>Total carton</dt><dd>{{ $cartonText }}</dd>
        @if ($s->gate_stuffing_note)
            <dt>Catatan</dt><dd>{{ $s->gate_stuffing_note }}</dd>
        @endif
    </dl>
</section>
<section class="sg-section">
    <h2 class="sg-section__title">Dokumen pengiriman barang</h2>
    <dl class="sg-card__meta" style="margin:0">
        <dt>Kirim ke negara</dt><dd>{{ $s->gate_country ?: '-' }}</dd>
        <dt>Jam buka container</dt><dd>{{ $fmt($s->gate_open_at) }}</dd>
        <dt>Driver</dt><dd>{{ $s->gate_driver ?: '-' }}</dd>
        @foreach (['Orang muat' => $s->loaders, 'Petugas security' => $s->guards] as $label => $people)
            <dt>{{ $label }}</dt>
            <dd>
                @if ($people)
                    <ol class="sg-people__view">
                        @foreach ($people as $p)
                            <li>{{ $p['nama'] }} <span class="sg-person__sub">{{ \App\Http\Controllers\Security\GateController::personMeta($p) }}</span></li>
                        @endforeach
                    </ol>
                @else
                    -
                @endif
            </dd>
        @endforeach
        <dt>Jumlah carton</dt><dd>{{ $cartonText }}</dd>
        <dt>Berangkat dari MIF</dt><dd>{{ $fmt($s->gate_out_at) }}</dd>
        <dt>Tiba di {{ $s->port_name ?: 'pelabuhan / bandara' }}</dt><dd>{{ $s->gate_port_at ? $fmt($s->gate_port_at) : 'Belum diisi' }}</dd>
        <dt>Pengawal dari MIF</dt>
        <dd>
            {{ $s->escort->nama ?? '-' }}
            @if ($s->escort)
                <span class="sg-person__sub">NRK <span class="sg-mono">{{ $s->escort->karid }}</span>{{ $s->escort->depnm ? ' · ' . $s->escort->depnm : '' }}</span>
            @endif
        </dd>
        <dt>Foto tanggal</dt><dd>{{ $tanggal ? \Carbon\Carbon::parse($tanggal)->isoFormat('D MMM YYYY') : '-' }}</dd>
    </dl>
</section>
