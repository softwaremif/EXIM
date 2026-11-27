{{-- truk LCL dipakai beberapa shipment plan: daftar SP di truk ini (hanya lihat; grup truk ditentukan Manager EXIM,
     lihat GateController::TRIP_COLUMN) --}}
@php $tripSps = $s->members ?? collect(); @endphp
@if ($tripSps->count() > 1)
    <section class="sg-section" id="secTrip">
        <div>
            <h2 class="sg-section__title">Shipment di truk ini <span class="sg-trip__count">({{ $tripSps->count() }} SP)</span></h2>
            <p class="sg-hint">Data truk cukup diisi sekali, otomatis tersimpan di semua SP. Ceklis carton tetap per SP.
                SP di truk ini diatur EXIM; kalau truk yang datang tidak sesuai, hubungi EXIM.</p>
        </div>
        <ul class="sg-trip">
            @foreach ($tripSps as $m)
                <li class="sg-trip__item">
                    <div class="sg-trip__info">
                        <strong class="sg-mono">{{ $m->idship }}</strong>
                        <span>{{ $m->buyer ?: '-' }} · PIC {{ $m->pic ?: '-' }}</span>
                        <span class="sg-trip__po" title="{{ $m->POno }}">PO {{ $m->POno ?: '-' }}</span>
                    </div>
                    @if ($m->contpk == $s->contpk)
                        <span class="sg-trip__tag">Induk</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>
@endif
