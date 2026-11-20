@php
    $isSelected = $selected && $selected->contpk == $c->contpk;
    $etaText = 'ETA belum diisi';
    $action = 'Ketuk untuk gate in ›';
    if ($c->status === 'departed') {
        $etaText = 'Keluar ' . $c->departed_at->format('H:i') . ' · ' . $c->departed_at->isoFormat('D MMM');
        $action = 'Lihat data ›';
    } elseif ($c->arrived_at) {
        $etaText = 'Tiba ' . $c->arrived_at->format('H:i') . ' · ' . $c->arrived_at->isoFormat('D MMM');
        $action = 'Catat keluar ›';
    } elseif ($c->eta) {
        if ($c->eta_days < 0) {
            $etaText = 'Lewat ' . abs($c->eta_days) . ' hari · ETA ' . $c->eta->isoFormat('D MMM');
        } elseif ($c->eta_days === 0) {
            $etaText = 'ETA hari ini';
        } elseif ($c->eta_days === 1) {
            $etaText = 'ETA besok · ' . $c->eta->isoFormat('D MMM');
        } else {
            $etaText = 'ETA ' . $c->eta->isoFormat('ddd, D MMM');
        }
    }
@endphp
{{-- seluruh card bisa diketuk (link utama melebar lewat ::after); tombol Edit di atasnya --}}
<div class="sg-card {{ $isSelected ? 'is-selected' : '' }}">
    <div class="sg-card__top">
        <div>
            {{-- acuan: no. container; LCL (tanpa container) pakai plat nomor truk --}}
            <a class="sg-card__link" href="{{ route('security.gate.index', array_filter(['filter' => $filter, 'q' => $q, 'cont' => $c->contpk])) }}">
                @if ($c->ident)
                    <p class="sg-card__no">{{ $c->ident }}</p>
                @else
                    <p class="sg-card__no is-empty">{{ $c->is_lcl ? 'Plat truk belum diisi EXIM' : 'No. container belum diisi' }}</p>
                @endif
            </a>
            <span class="sg-card__type">
                <span class="sg-kind {{ $c->is_lcl ? 'sg-kind--lcl' : '' }}">{{ $c->is_lcl ? 'LCL' : 'Container' }}</span>
                {{ $c->is_lcl ? 'Plat truk' : $c->type_label }} · Export
            </span>
        </div>
        <span class="sg-card__badges">
            @include('menu.exim-export.security.partials.badge', ['status' => $c->status])
            @if (!empty($c->door))
                <span class="sg-door-tag">MIF {{ $c->door->mif }} · Pintu {{ $c->door->pintu }}</span>
            @endif
        </span>
    </div>
    <dl class="sg-card__meta">
        <dt>Shipment</dt><dd class="sg-mono">{{ $c->idship }}</dd>
        <dt>Buyer</dt><dd>{{ $c->buyer ?: '-' }}</dd>
        <dt>Forwarder</dt><dd>{{ $c->fwdnm ?: '-' }}</dd>
        <dt>Ekspedisi</dt><dd>{{ ($c->ekspedisinm ?? null) ?: '-' }}</dd>
        <dt>PIC EXIM</dt><dd>{{ $c->pic ?: '-' }}</dd>
        @if ($c->is_lcl)
            @if ($c->contno_fmt)
                <dt>No. container</dt><dd class="sg-mono">{{ $c->contno_fmt }}</dd>
            @endif
        @elseif ($c->nopol)
            <dt>Plat</dt><dd class="sg-mono">{{ $c->nopol }}</dd>
        @endif
    </dl>
    <div class="sg-card__foot">
        <span class="sg-card__eta {{ $c->status === 'late' ? 'is-late' : '' }}">
            @include('menu.exim-export.security.partials.icon', ['name' => $c->arrived_at ? 'check' : 'clock']){{ $etaText }}
        </span>
        <span class="sg-card__actions">
            @if ($c->arrived_at && $ready)
                <a class="sg-card__edit" href="{{ route('security.gate.index', array_filter(['filter' => $filter, 'q' => $q, 'cont' => $c->contpk, 'edit' => 1])) }}" aria-label="Edit data {{ $c->ident ?: 'container' }}">
                    @include('menu.exim-export.security.partials.icon', ['name' => 'edit'])Edit
                </a>
            @endif
            <span>{{ $action }}</span>
        </span>
    </div>
</div>
