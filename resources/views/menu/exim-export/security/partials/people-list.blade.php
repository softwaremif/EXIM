{{-- Daftar petugas dari scan ID card / cari nama, maks $max orang. Item baru dibuat JS (addPerson) dengan markup yang sama.
     Sumber per daftar (HRD, harian lepas): GateController::PEOPLE_SOURCES.
     Pakai: @include('...people-list', ['name' => 'gate_loaders', 'label' => 'Petugas muat', 'max' => 20, 'people' => [...], 'required' => true]) --}}
@php
    $gate = \App\Http\Controllers\Security\GateController::class;
    $sources = $gate::PEOPLE_SOURCES[$name] ?? ['hl' => false];
@endphp
<div class="sg-field" data-field="{{ $name }}">
    <div class="sg-sign__head">
        <span class="sg-label" id="lbl-{{ $name }}">{{ $label }} @if ($required ?? true)<span class="sg-req">*</span>@endif</span>
        <span class="sg-count {{ count($people) ? 'is-done' : '' }}" data-people-count>{{ count($people) }}/{{ $max }}</span>
    </div>
    <div class="sg-people" data-people data-name="{{ $name }}" data-max="{{ $max }}" data-label="{{ $label }}"
        data-hl="{{ $sources['hl'] ? 1 : 0 }}">
        <ol class="sg-people__list" data-people-list>
            @foreach ($people as $p)
                <li class="sg-people__item {{ ($p['src'] ?? 'hrd') === 'hl' ? 'is-hl' : '' }}">
                    <input type="hidden" name="{{ $name }}[]" value="{{ $gate::personToken($p) }}">
                    <span class="sg-person__body">
                        <strong>{{ $p['nama'] }}</strong>
                        <small>{{ $gate::personMeta($p) }}</small>
                    </span>
                    <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-people-remove aria-label="Hapus {{ $p['nama'] }}">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
                </li>
            @endforeach
        </ol>
        {{-- dialog scan juga punya kolom cari nama / NRK untuk petugas tanpa ID card
             (petugas muat: HRD & harian lepas; security: HRD) --}}
        <button type="button" class="sg-btn sg-btn--block sg-people__add" data-people-add data-people-scan aria-describedby="lbl-{{ $name }}" {{ count($people) >= $max ? 'hidden' : '' }}>
            @include('menu.exim-export.security.partials.icon', ['name' => 'qr']) Scan ID card / cari nama
        </button>
    </div>
    <span class="sg-hint" hidden></span>
</div>
