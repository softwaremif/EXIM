{{-- Berita Acara Stuffing (form kertas) dari data muat security --}}
@extends('menu.exim-export.security.docs.layout')
@section('content')
    @php
        $tgl = $s->gate_load_at ?? null ?: ($s->gate_open_at ?? null);
        $jam = fn($v) => $v ? \Carbon\Carbon::parse($v)->format('H.i') . ' WIB' : '';
    @endphp
    <dl class="fields">
        <dt>Tanggal</dt><span class="colon">:</span><dd>{{ $tgl ? \Carbon\Carbon::parse($tgl)->isoFormat('dddd, DD-MM-YYYY') : '' }}</dd>
        <dt>Truck / Nopol</dt><span class="colon">:</span><dd>{{ $s->nopol }}</dd>
    </dl>
    <dl class="fields fields--2">
        <dt>Lokasi</dt><span class="colon">:</span><dd>{{ $s->gate_lokasi ?? '' }}</dd>
        <span class="gap"></span><dt>Buyer</dt><span class="colon">:</span><dd>{{ $s->buyer }}</dd>
        <dt>Jam buka</dt><span class="colon">:</span><dd>{{ $jam($s->gate_open_at ?? null) }}</dd>
        <span class="gap"></span><dt>Jam muat</dt><span class="colon">:</span><dd>{{ $jam($s->gate_load_at ?? null) }}</dd>
    </dl>
    <dl class="fields">
        <dt>Total carton</dt><span class="colon">:</span><dd>{{ $s->carton_count !== null ? $s->carton_count . ' carton' : '' }} <span class="sub">dari scan carton</span></dd>
        <dt>Catatan</dt><span class="colon">:</span><dd class="{{ empty($s->gate_stuffing_note) ? 'empty' : '' }}">{{ $s->gate_stuffing_note ?? '' ?: '-' }}</dd>
    </dl>
    @if (empty($s->gate_open_at))
        <p class="note">Data muat belum diisi security.</p>
    @endif
@endsection
