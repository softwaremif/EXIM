{{-- Lokasi stuffing (factory & pintu) di tab Muat; data: $s->stuffing (GateController::stuffingState), $posMif.
     Aksi lewat GateDoorController: aktifkan pintu, pindah MIF (cukup jam pindah), kosongkan pintu yang salah pilih. --}}
@php
    $gate = \App\Http\Controllers\Security\GateController::class;
    $st = $s->stuffing;
    $open = !$s->departed_at;
    $since = function ($at) {
        $m = (int) $at->diffInMinutes(now());
        return $m < 60 ? $m . ' mnt' : intdiv($m, 60) . ' j ' . ($m % 60) . ' mnt';
    };
    $from = $st->mif ?? $posMif;
    // security hanya mencatat untuk pos MIF-nya; akun lain (null) boleh semua
    $canMove = $open && $from !== null && ($posMif === null || $posMif === $from);
    $canActivate = $open && !$st->active;
    $activateMifs = $canActivate ? ($posMif !== null ? [$posMif] : array_keys($gate::DOORS)) : [];
    $statusText = ['aktif' => 'Di pintu', 'pindah' => 'Pindah', 'keluar' => 'Keluar pabrik', 'batal' => 'Dibatalkan', 'selesai' => 'Selesai muat'];
@endphp
<section class="sg-section sg-door" id="sec-door" data-panel="load" data-door
    data-activate-url="{{ route('security.gate.door.activate', $s->contpk) }}"
    data-move-url="{{ route('security.gate.door.move', $s->contpk) }}"
    data-cancel-url="{{ route('security.gate.door.cancel', $s->contpk) }}"
    data-ident="{{ $s->ident ?: 'container ini' }}">
    <div class="sg-section__head">
        <h2 class="sg-section__title">Lokasi stuffing</h2>
        @if ($posMif)
            <span class="sg-door__pos">Pos MIF {{ $posMif }}</span>
        @endif
    </div>

    {{-- posisi sekarang --}}
    <div class="sg-door__now {{ $st->active ? 'is-active' : ($st->transit ? 'is-transit' : '') }}">
        @include('menu.exim-export.security.partials.icon', ['name' => $st->active ? 'check' : ($st->transit ? 'truck' : 'info')])
        <div>
            @if ($s->departed_at)
                <strong>Sudah keluar pabrik</strong>
                <span>{{ $s->departed_at->isoFormat('HH:mm, D MMM') }}{{ $st->last ? ' · terakhir di MIF ' . $st->last->mif . ($st->last->pintu ? ' pintu ' . $st->last->pintu : '') : '' }}</span>
            @elseif ($st->active)
                <strong>MIF {{ $st->active->mif }} · Pintu {{ $st->active->pintu }}</strong>
                <span>Sejak {{ $st->active->masuk->format('H:i') }} ({{ $since($st->active->masuk) }})</span>
            @elseif (!empty($st->finished))
                <strong>Selesai muat · MIF {{ $st->last->mif }}</strong>
                <span>Keluar dari pintu {{ $st->last->pintu }} jam {{ $st->last->keluar->format('H:i') }} · menunggu gate out</span>
            @elseif ($st->transit)
                <strong>Pindah ke MIF {{ $st->last->tujuan_mif }}</strong>
                <span>Dari MIF {{ $st->last->mif }} jam {{ $st->last->keluar->format('H:i') }} · belum masuk pintu</span>
            @elseif ($from)
                <strong>Di area MIF {{ $from }}</strong>
                <span>Belum masuk pintu</span>
            @else
                <strong>Belum masuk pintu</strong>
                <span>Pilih pintu saat container mulai stuffing</span>
            @endif
        </div>
    </div>

    @if ($open && $posMif !== null && $from !== null && $posMif !== $from && !$canActivate)
        <p class="sg-hint" style="margin:0">Container sedang di MIF {{ $from }}. Pindah / kosongkan pintu dicatat oleh security MIF {{ $from }}.</p>
    @endif

    {{-- aktifkan / terima di pintu --}}
    @foreach ($activateMifs as $mif)
        <div class="sg-field">
            <span class="sg-label">{{ $st->transit && (int) $st->last->tujuan_mif === $mif ? 'Terima di pintu' : 'Aktifkan di pintu' }} · MIF {{ $mif }}</span>
            <div class="sg-door__grid">
                @foreach (range(1, $gate::DOORS[$mif]) as $p)
                    @php $busy = $st->occupied[$mif][$p] ?? null; @endphp
                    <button type="button" class="sg-door__btn" data-door-activate data-mif="{{ $mif }}" data-pintu="{{ $p }}" {{ $busy ? 'disabled' : '' }}>
                        <strong>Pintu {{ $p }}</strong>
                        <small>{{ $busy ? 'Dipakai ' . $busy : 'Kosong' }}</small>
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- pindah factory (cukup jam pindah) / kosongkan pintu --}}
    @if ($canMove)
        <div class="sg-field sg-door__move">
            <label class="sg-label" for="doorNote">Pindah factory</label>
            <input class="sg-input" id="doorNote" maxlength="255" autocomplete="off" placeholder="Catatan (opsional), mis. barang MIF {{ $from }} belum siap" data-door-note>
            <div class="sg-row">
                @foreach (array_keys($gate::DOORS) as $mif)
                    @continue($mif === $from)
                    <button type="button" class="sg-btn" data-door-move data-tujuan="{{ $mif }}">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'truck']) Pindah ke MIF {{ $mif }}
                    </button>
                @endforeach
                @if ($st->active)
                    <button type="button" class="sg-btn sg-btn--ghost" data-door-cancel>Salah pintu? Kosongkan</button>
                @endif
            </div>
        </div>
    @endif

    {{-- riwayat kunjungan --}}
    @if ($st->history->isNotEmpty())
        <details class="sg-door__log" {{ $s->departed_at ? 'open' : '' }}>
            <summary>Riwayat lokasi ({{ $st->history->count() }})</summary>
            <ol>
                @foreach ($st->history as $v)
                    <li class="is-{{ $v->status }}">
                        <strong>MIF {{ $v->mif }} · {{ $v->pintu ? 'Pintu ' . $v->pintu : 'tanpa pintu' }}</strong>
                        <span>
                            {{ $v->masuk->isoFormat('D MMM HH:mm') }} – {{ $v->keluar ? $v->keluar->isoFormat(($v->keluar->isSameDay($v->masuk) ? '' : 'D MMM ') . 'HH:mm') : 'sekarang' }}
                            · {{ $statusText[$v->status] ?? $v->status }}{{ $v->status === 'pindah' && $v->tujuan_mif ? ' ke MIF ' . $v->tujuan_mif : '' }}
                            {{ $v->masuk_by ? '· ' . $v->masuk_by : '' }}
                        </span>
                        @if ($v->catatan)
                            <em>{{ $v->catatan }}</em>
                        @endif
                    </li>
                @endforeach
            </ol>
        </details>
    @endif
</section>
