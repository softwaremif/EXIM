{{-- Dokumen Pengiriman Barang (form kertas) dari data muat & keluar security --}}
@extends('menu.exim-export.security.docs.layout')
@section('content')
    @php
        $jam = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('H.i') . ' WIB · ' . \Carbon\Carbon::parse($v)->format('d-m-Y') : '';
        $tgl = $s->gate_load_at ?? null ?: ($s->gate_open_at ?? null);
        // NRK (harian lepas ditandai)
        $person = fn($p) => e($p['nama']) . ' <span class="sub">' . e(\App\Http\Controllers\Security\GateController::personMeta(array_diff_key($p, ['depnm' => 1]))) . '</span>';
    @endphp
    <dl class="fields">
        <dt>Kirim ke negara</dt><span class="colon">:</span><dd>{{ $s->gate_country ?? '' }}</dd>
        <dt>Jam buka container</dt><span class="colon">:</span><dd>{{ $jam($s->gate_open_at ?? null) }}</dd>
        <dt>Driver</dt><span class="colon">:</span><dd>{{ $s->gate_driver ?? '' }}</dd>
        <dt>Jumlah orang muat</dt><span class="colon">:</span><dd>{{ count($s->loaders) ? count($s->loaders) . ' orang' : '' }}</dd>
    </dl>
    <ol class="lines" style="margin:0 0 14px 182px">
        {{-- form kertas 10 baris; lebih dari 10 orang muat tetap tercetak semua --}}
        @for ($i = 0; $i < max(10, count($s->loaders)); $i++)
            <li class="{{ isset($s->loaders[$i]) ? '' : 'blank' }}">{!! isset($s->loaders[$i]) ? $person($s->loaders[$i]) : '&nbsp;' !!}</li>
        @endfor
    </ol>
    <dl class="fields">
        <dt>Jumlah carton</dt><span class="colon">:</span><dd>{{ $s->carton_count !== null ? $s->carton_count . ' carton' : '' }}</dd>
        <dt>Petugas security</dt><span class="colon">:</span><dd>{{ count($s->guards) ? count($s->guards) . ' orang' : '' }}</dd>
    </dl>
    <ol class="lines" style="margin:0 0 14px 182px">
        @for ($i = 0; $i < 4; $i++)
            <li class="{{ isset($s->guards[$i]) ? '' : 'blank' }}">{!! isset($s->guards[$i]) ? $person($s->guards[$i]) : '&nbsp;' !!}</li>
        @endfor
    </ol>
    <dl class="fields">
        <dt>Tutup container</dt><span class="colon">:</span><dd>{{ $jam($s->gate_close_at ?? null) }}</dd>
        <dt>Berangkat dari MIF</dt><span class="colon">:</span><dd>{{ $jam($s->gate_out_at ?? null) }}</dd>
        <dt>Tiba di {{ $s->port_name ? Str::title(Str::before($s->port_name, ',')) : 'Pelbn / Bandara' }}</dt><span class="colon">:</span><dd>{{ $jam($s->gate_port_at ?? null) }}</dd>
        <dt>Pengawal dari MIF</dt><span class="colon">:</span><dd>{!! $s->escort ? e($s->escort->nama) . ' <span class="sub">NRK ' . e($s->escort->karid) . '</span>' : '' !!}</dd>
        <dt>Foto tanggal</dt><span class="colon">:</span><dd>{{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d-m-Y') : '' }}</dd>
    </dl>
    <p class="footer">{{ $s->gate_lokasi ?? '' ?: 'MIF' }}</p>
@endsection
