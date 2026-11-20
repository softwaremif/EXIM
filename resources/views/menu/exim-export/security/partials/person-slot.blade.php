{{-- Satu petugas dari scan ID card (QR berisi NRK) atau cari nama / NRK di dialog yang sama. Pakai: @include('...person-slot', ['name' => 'gate_escort_nrk', 'label' => 'Pengawal', 'person' => $obj, 'required' => false]) --}}
@php
    $nrk = $person->karid ?? null;
    $meta = 'Scan QR code di ID card atau cari nama / NRK';
    if ($nrk) {
        $meta = 'NRK ' . $nrk . (!empty($person->depnm) ? ' · ' . $person->depnm : '');
    } elseif ($person) {
        $meta = 'Data lama tanpa NRK · scan / cari ulang';
    }
@endphp
<div class="sg-field" data-field="{{ $name }}">
    <span class="sg-label" id="lbl-{{ $name }}">{{ $label }} @if ($required ?? true)<span class="sg-req">*</span>@endif</span>
    <div class="sg-person {{ $nrk ? 'is-filled' : '' }}" data-person data-label="{{ $label }}">
        <input type="hidden" name="{{ $name }}" value="{{ $nrk }}">
        <span class="sg-person__icon">@include('menu.exim-export.security.partials.icon', ['name' => $nrk ? 'check' : 'id'])</span>
        <span class="sg-person__body" aria-live="polite">
            <strong data-person-name>{{ $person->nama ?? 'Belum dipilih' }}</strong>
            <small data-person-meta>{{ $meta }}</small>
        </span>
        <button type="button" class="sg-btn" data-person-scan aria-describedby="lbl-{{ $name }}">
            @include('menu.exim-export.security.partials.icon', ['name' => 'qr'])<span data-person-btn>{{ $nrk ? 'Ganti' : 'Scan / cari' }}</span>
        </button>
    </div>
    <span class="sg-hint" hidden></span>
</div>
