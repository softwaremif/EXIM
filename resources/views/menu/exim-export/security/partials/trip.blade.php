{{-- truk LCL dipakai beberapa shipment plan: daftar SP di truk ini, pisahkan / gabung SP (lihat GateController::TRIP_COLUMN) --}}
@php
    $tripSps = $s->members ?? collect();
    $tripPending = $s->pending ?? collect();
    $tripEdit = $ready && !$s->departed_at;
@endphp
@if ($tripSps->count() > 1 || ($tripEdit && $tripPending->isNotEmpty()))
    <section class="sg-section" id="secTrip" data-join-url="{{ route('security.gate.trip.join', $s->contpk) }}" data-split-url="{{ route('security.gate.trip.split', $s->contpk) }}">
        <div>
            <h2 class="sg-section__title">Shipment di truk ini <span class="sg-trip__count">({{ $tripSps->count() }} SP)</span></h2>
            <p class="sg-hint">Data truk cukup diisi sekali, otomatis tersimpan di semua SP. Ceklis carton tetap per SP.</p>
        </div>
        <ul class="sg-trip">
            @foreach ($tripSps as $m)
                <li class="sg-trip__item">
                    <div class="sg-trip__info">
                        <strong class="sg-mono">{{ $m->idship }}</strong>
                        <span>{{ $m->buyer ?: '-' }} · PIC {{ $m->pic ?: '-' }}</span>
                        <span class="sg-trip__po" title="{{ $m->POno }}">PO {{ $m->POno ?: '-' }}</span>
                    </div>
                    @if ($tripEdit && $m->contpk != $s->contpk)
                        <button type="button" class="sg-btn sg-btn--ghost sg-btn--sm" data-trip-split="{{ $m->contpk }}" data-trip-label="{{ $m->idship }}">Pisahkan</button>
                    @elseif ($m->contpk == $s->contpk && $tripSps->count() > 1)
                        <span class="sg-trip__tag">Induk</span>
                    @endif
                </li>
            @endforeach
        </ul>
        @if ($tripEdit && $tripPending->isNotEmpty())
            <div class="sg-alert sg-alert--warn" role="status">
                @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                <div>
                    <strong>SP baru dengan plat & ECA yang sama</strong>
                    Ditambahkan EXIM setelah truk ini mulai diisi. Gabungkan kalau ikut truk ini; data truk akan tersalin ke SP tersebut.
                    <ul class="sg-trip sg-trip--pending">
                        @foreach ($tripPending as $m)
                            <li class="sg-trip__item">
                                <div class="sg-trip__info">
                                    <strong class="sg-mono">{{ $m->idship }}</strong>
                                    <span>{{ $m->buyer ?: '-' }} · PIC {{ $m->pic ?: '-' }}</span>
                                </div>
                                <button type="button" class="sg-btn sg-btn--sm" data-trip-join="{{ $m->contpk }}" data-trip-label="{{ $m->idship }}">Gabungkan</button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </section>
    <script>
    (function () {
        const sec = document.getElementById('secTrip');
        if (!sec || sec.dataset.bound) return;
        sec.dataset.bound = '1';
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        sec.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-trip-join], [data-trip-split]');
            if (!btn) return;
            const join = btn.hasAttribute('data-trip-join');
            const label = btn.dataset.tripLabel;
            const ok = await window.sgConfirm(join
                ? `Gabungkan ${label} ke truk ini? Data truk (jam tiba, foto, checklist, dst.) ikut tersimpan di ${label}.`
                : `Pisahkan ${label} dari truk ini? Data truk di ${label} dikosongkan dan ${label} tampil sebagai truk sendiri.`,
                { title: join ? 'Gabungkan SP?' : 'Pisahkan SP?', okText: join ? 'Gabungkan' : 'Pisahkan', type: join ? 'question' : 'warning' });
            if (!ok) return;
            btn.disabled = true;
            try {
                const fd = new FormData();
                fd.append('sp', join ? btn.dataset.tripJoin : btn.dataset.tripSplit);
                const res = await fetch(join ? sec.dataset.joinUrl : sec.dataset.splitUrl, {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd,
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || !json.success) throw new Error(json.message || 'Gagal menyimpan.');
                location.reload();
            } catch (err) {
                btn.disabled = false;
                window.sgAlert(err.message);
            }
        });
    })();
    </script>
@endif
