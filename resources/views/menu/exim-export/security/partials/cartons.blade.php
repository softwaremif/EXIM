{{-- Ceklis carton oleh security per container. Data & aturan di sistem Packing List (API):
     tim stuffing scan / proses manual dulu (cekdate1), lalu security ceklis di sini (cekdate2)
     -> carton baru dianggap masuk container. Di halaman: ringkasan; ceklis di modal full layar
     berupa grid tile. Ketuk tile = detail isi carton; lingkaran pojok = ceklis cepat. Selalu konfirmasi.
     Mode "Pilih": ketuk beberapa tile lalu ceklis / batalkan ceklis sekaligus (massal). --}}
<style>
    /* ---------- ringkasan di halaman ---------- */
    .ck-card { display: flex; flex-direction: column; gap: var(--space-3); }
    .ck-prog { height: 10px; border-radius: var(--radius-pill); background: var(--surface-sunken); overflow: hidden; display: flex; }
    .ck-prog i { display: block; height: 100%; transition: width .3s ease; }
    .ck-prog .p-ok { background: var(--ok); }
    .ck-prog .p-scan { background: var(--info); opacity: .55; }
    .ck-prog-text { display: flex; justify-content: space-between; font-size: 13px; color: var(--ink-muted); }
    .ck-prog-text b { color: var(--ink); }
    .ck-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-2); }
    .ck-stat { border: 1px solid var(--line); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); background: var(--surface-raised); text-align: left; cursor: pointer; font: inherit; color: inherit; min-height: var(--touch-min); }
    .ck-stat b { display: block; font: 700 22px/28px var(--font-sans); }
    .ck-stat span { font-size: 12px; color: var(--ink-muted); }
    .ck-stat.is-scan b { color: var(--info); }
    .ck-stat.is-ok b { color: var(--ok); }

    /* ---------- modal full layar ---------- */
    .ck-modal { position: fixed; inset: 0; z-index: 50; display: flex; flex-direction: column; background: var(--surface); }
    .ck-head { padding: calc(var(--space-2) + env(safe-area-inset-top)) var(--space-3) var(--space-2); background: var(--surface-raised); border-bottom: 1px solid var(--line); display: flex; flex-direction: column; gap: var(--space-2); }
    .ck-head__row { display: flex; align-items: center; gap: var(--space-1); }
    .ck-head__title { flex: 1; min-width: 0; margin: 0; font: 600 17px/22px var(--font-sans); }
    .ck-head__title small { display: block; font: 400 13px/18px var(--font-mono); color: var(--ink-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ck-head__meta { display: flex; justify-content: space-between; font-size: 12.5px; color: var(--ink-muted); }
    .ck-head__meta b { color: var(--ink); }
    .ck-spin .sg-icon { animation: ck-spin 0.8s linear infinite; }
    @keyframes ck-spin { to { transform: rotate(360deg); } }
    .ck-tools { display: flex; gap: var(--space-2); align-items: center; }
    .ck-search { position: relative; flex: 1; min-width: 0; }
    .ck-search input { width: 100%; min-height: 44px; padding: 0 var(--space-3) 0 40px; border: 1px solid var(--line-strong); border-radius: var(--radius-md); background: var(--surface); color: var(--ink); font: 400 16px/20px var(--font-sans); }
    .ck-search .sg-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: var(--ink-muted); }
    /* ringkasan shipment di bawah judul */
    .ck-ship { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: var(--space-2) var(--space-4); margin: 0; padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); background: var(--surface-sunken); }
    .ck-ship div { min-width: 0; }
    .ck-ship dt { font: 500 11.5px/16px var(--font-sans); color: var(--ink-muted); }
    .ck-ship dd { margin: 0; font: 600 14px/20px var(--font-sans); color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ck-ship dd.sg-mono { font-family: var(--font-mono); }
    @media (max-width: 599px) {
        /* HP: satu baris yang bisa digeser */
        .ck-ship { grid-auto-flow: column; grid-auto-columns: max-content; grid-template-columns: none; overflow-x: auto; scrollbar-width: none; }
        .ck-ship::-webkit-scrollbar { display: none; }
        .ck-ship dd { max-width: 60vw; }
    }
    /* filter: status (kiri) + MIF (kanan) dalam SATU baris; di layar sempit bisa digeser */
    .ck-filters { display: flex; align-items: center; gap: var(--space-2); overflow-x: auto; scrollbar-width: none; }
    .ck-filters::-webkit-scrollbar { display: none; }
    .ck-filters .ck-tabs { flex: none; overflow: visible; }
    .ck-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 6px; vertical-align: 0; }
    .ck-seg { flex: none; margin-left: auto; display: inline-flex; align-items: center; gap: 2px; padding: 3px; border-radius: var(--radius-pill); border: 1px solid var(--line-strong); background: var(--surface); }
    .ck-seg__label { padding: 0 6px 0 8px; font: 500 12px/16px var(--font-sans); color: var(--ink-muted); }
    .ck-seg button { border: 0; border-radius: var(--radius-pill); padding: 6px 12px; min-height: 32px; background: transparent; color: var(--ink-muted); font: 600 13px/18px var(--font-sans); cursor: pointer; white-space: nowrap; }
    .ck-seg button b { margin-left: 4px; font-weight: 600; opacity: .75; }
    .ck-seg button.is-on { background: var(--signal-soft); color: var(--signal-ink); box-shadow: inset 0 0 0 1px var(--signal); }
    .ck-tabs { display: flex; gap: var(--space-1); overflow-x: auto; scrollbar-width: none; }
    .ck-tabs::-webkit-scrollbar { display: none; }
    .ck-tab { flex: none; border: 0; border-radius: var(--radius-pill); padding: 8px 12px; min-height: 36px; background: var(--surface-sunken); color: var(--ink-muted); font: 600 13px/18px var(--font-sans); cursor: pointer; }
    .ck-tab b { margin-left: 4px; color: var(--ink); }
    .ck-tab.is-on { background: var(--ink); color: var(--surface-raised); }
    .ck-tab.is-on b { color: var(--surface-raised); }
    .ck-body { flex: 1; overflow-y: auto; padding: var(--space-3); -webkit-overflow-scrolling: touch; }
    .ck-alert { margin: var(--space-2) var(--space-3) 0; }
    /* hasil scan barcode carton */
    .ck-scanmsg { display: flex; align-items: center; gap: var(--space-2); padding: 10px var(--space-3); font: 600 14px/20px var(--font-sans); border-bottom: 1px solid var(--line); background: var(--surface-sunken); color: var(--ink-muted); }
    .ck-scanmsg .sg-icon { width: 20px; height: 20px; flex: none; }
    .ck-scanmsg.is-ok { background: var(--ok-soft); color: var(--ok); }
    .ck-scanmsg.is-err { background: var(--danger-soft, #fde8e8); color: var(--danger, #b91c1c); }
    .ck-scanmsg.is-info { background: var(--info-soft); color: var(--info); }
    .ck-scanmsg.is-busy { opacity: .7; }
    .ck-tile.is-flash { animation: ck-flash 1.6s ease; }
    @keyframes ck-flash { 0%, 40% { box-shadow: 0 0 0 4px var(--ok); } 100% { box-shadow: none; } }

    /* grid tile carton */
    .ck-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(158px, 1fr)); gap: var(--space-2); }
    .ck-tile { position: relative; display: flex; flex-direction: column; gap: 2px; min-height: 96px; padding: 9px 10px 8px; border: 1.5px solid var(--line); border-radius: var(--radius-md); background: var(--surface-raised); cursor: pointer; text-align: left; font: inherit; color: var(--ink); -webkit-tap-highlight-color: transparent; }
    .ck-tile:active { transform: scale(.97); }
    .ck-tile.st-scanned { border-color: var(--info); background: var(--info-soft); }
    .ck-tile.st-checked, .ck-tile.st-locked, .ck-tile.st-old { border-color: var(--ok); background: var(--ok-soft); }
    .ck-tile.st-pending { background: var(--surface-sunken); color: var(--ink-muted); border-style: dashed; }
    .ck-tile.is-busy { opacity: .45; pointer-events: none; }
    .ck-tile__no { font: 700 18px/22px var(--font-mono); padding-right: 30px; overflow-wrap: anywhere; }
    .ck-tile__meta { font-size: 11.5px; line-height: 15px; color: var(--ink-muted); }
    .ck-tile__po { padding-right: 26px; font: 500 11.5px/15px var(--font-mono); color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ck-tile__sub { font-size: 11.5px; line-height: 15px; color: var(--ink-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ck-tile__foot { display: flex; align-items: center; gap: 6px; margin-top: auto; padding-top: 3px; font-size: 11.5px; line-height: 15px; color: var(--ink-muted); }
    .ck-mif { display: inline-block; padding: 0 6px; border-radius: var(--radius-pill); border: 1px solid var(--line-strong); background: var(--surface); color: var(--ink); font: 600 10.5px/16px var(--font-sans); white-space: nowrap; }
    .ck-mif.m2 { border-color: var(--signal); color: var(--signal-ink); background: var(--signal-soft); }
    .ck-mifbar { display: flex; flex-wrap: wrap; gap: 4px 14px; font-size: 12.5px; color: var(--ink-muted); }
    .ck-mifbar b { color: var(--ink); }
    .ck-tile__kind { display: inline-block; padding: 0 5px; border-radius: var(--radius-pill); background: var(--signal-soft); color: var(--signal-ink); font: 600 10.5px/16px var(--font-sans); }
    .ck-mark { position: absolute; top: 6px; right: 6px; width: 30px; height: 30px; border-radius: 50%; border: 2px solid var(--info); background: var(--surface-raised); display: flex; align-items: center; justify-content: center; color: transparent; padding: 0; cursor: pointer; }
    .ck-mark .sg-icon { width: 18px; height: 18px; stroke-width: 3; }
    .st-checked .ck-mark, .st-locked .ck-mark, .st-old .ck-mark { background: var(--ok); border-color: var(--ok); color: #fff; }
    .st-pending .ck-mark { border: 2px dashed var(--line-strong); background: transparent; cursor: not-allowed; }
    .st-locked .ck-mark, .st-old .ck-mark { cursor: default; }
    @media (max-width: 359px) { .ck-grid { grid-template-columns: 1fr; } }
    .ck-empty { text-align: center; color: var(--ink-muted); padding: var(--space-8) var(--space-4); grid-column: 1 / -1; }

    .ck-foot { padding: var(--space-2) var(--space-3) calc(var(--space-2) + env(safe-area-inset-bottom)); background: var(--surface-raised); border-top: 1px solid var(--line); display: flex; flex-direction: column; gap: var(--space-2); }
    /* mode pilih (ceklis massal) */
    .ck-tools .sg-btn[aria-pressed="true"] { background: var(--ink); color: var(--surface-raised); border-color: var(--ink); }
    .ck-modal.is-select .ck-tile { cursor: pointer; }
    .ck-modal.is-select .ck-tile:not(.is-pickable) { opacity: .5; }
    .ck-modal.is-select .ck-mark { border-radius: 8px; border: 2px solid var(--line-strong); background: var(--surface); color: transparent; }
    .ck-modal.is-select .ck-tile:not(.is-pickable) .ck-mark { visibility: hidden; }
    .ck-tile.is-picked { outline: 3px solid var(--ink); outline-offset: 1px; }
    .ck-modal.is-select .ck-tile.is-picked .ck-mark { background: var(--ink); border-color: var(--ink); color: var(--surface-raised); }
    .ck-selbar { display: flex; flex-direction: column; gap: var(--space-2); }
    .ck-selbar__row { display: flex; align-items: center; gap: var(--space-2); font-size: 14px; }
    .ck-selbar__row b { margin-right: auto; }
    .ck-selbar__act { display: flex; gap: var(--space-2); }
    .ck-selbar__act .sg-btn { flex: 1; }
    .ck-legend { display: flex; flex-wrap: wrap; justify-content: center; gap: var(--space-3); font-size: 12px; color: var(--ink-muted); }
    .ck-legend i { display: inline-block; width: 12px; height: 12px; border-radius: 3px; margin-right: 4px; vertical-align: -1px; border: 1.5px solid; }

    /* sheet detail & konfirmasi (di atas modal) */
    .ck-sheet { position: fixed; inset: 0; z-index: 60; display: flex; align-items: flex-end; justify-content: center; background: var(--scrim); }
    .ck-sheet__panel { width: 100%; max-height: 88dvh; overflow-y: auto; background: var(--surface-raised); border-radius: var(--radius-lg) var(--radius-lg) 0 0; padding: var(--space-4) var(--space-4) calc(var(--space-4) + env(safe-area-inset-bottom)); display: flex; flex-direction: column; gap: var(--space-3); }
    @media (min-width: 600px) {
        .ck-sheet { align-items: center; padding: var(--space-4); }
        .ck-sheet__panel { max-width: 560px; border-radius: var(--radius-lg); }
    }
    .ck-sheet__head { display: flex; align-items: flex-start; gap: var(--space-2); }
    .ck-sheet__title { flex: 1; margin: 0; font: 700 20px/26px var(--font-mono); }
    .ck-sheet__title small { display: block; font: 400 13px/18px var(--font-sans); color: var(--ink-muted); }
    .ck-badge { display: inline-block; padding: 2px 10px; border-radius: var(--radius-pill); font: 600 12px/18px var(--font-sans); }
    .ck-badge.st-scanned { background: var(--info-soft); color: var(--info); }
    .ck-badge.st-checked, .ck-badge.st-locked, .ck-badge.st-old { background: var(--ok-soft); color: var(--ok); }
    .ck-badge.st-pending { background: var(--surface-sunken); color: var(--ink-muted); }
    .ck-kv { display: grid; grid-template-columns: auto 1fr; gap: 4px var(--space-3); font-size: 13.5px; margin: 0; }
    .ck-kv dt { color: var(--ink-muted); }
    .ck-kv dd { margin: 0; }
    .ck-line { border: 1px solid var(--line); border-radius: var(--radius-md); padding: var(--space-2) var(--space-3); }
    .ck-line__head { font-weight: 600; font-size: 14px; }
    .ck-line__sub { font-size: 12.5px; color: var(--ink-muted); }
    .ck-sizes { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
    .ck-size { border: 1px solid var(--line); border-radius: var(--radius-sm); padding: 2px 8px; font: 500 13px/18px var(--font-mono); background: var(--surface); }
    .ck-size b { margin-left: 4px; }
    .ck-size i { font-style: normal; color: var(--ink-muted); }
    .ck-size.is-miss { border-color: var(--signal); background: var(--signal-soft); }
    .ck-miss { color: var(--signal-ink); font-weight: 700; }
    .ck-qr { display: flex; justify-content: center; padding: var(--space-3); background: #fff; border-radius: var(--radius-md); border: 1px solid var(--line); }
    .ck-qr img, .ck-qr canvas { width: 240px; height: 240px; image-rendering: pixelated; }
    .ck-qr-url { font: 400 12px/17px var(--font-mono); color: var(--ink-muted); overflow-wrap: anywhere; margin: 0; text-align: center; }
</style>

@php
    // QR untuk tim stuffing (sistem Packing List): container tiba & belum keluar -> scan -> langsung ke container ini.
    $stuffQrUrl = !$s->departed_at && $s->exportpk
        ? rtrim((string) config('services.packing.url'), '/') . '/shipment/scanner?exportpk=' . $s->exportpk . '&cont=' . $s->contpk
        : null;
@endphp
{{-- $panel: tab halaman detail tempat ringkasan ini tampil; modal di bawah sengaja di luar tab supaya tetap bisa dibuka (scan stuffing) --}}
<section class="sg-section" id="ckSection" @isset($panel) data-panel="{{ $panel }}" role="tabpanel" @endisset data-url="{{ route('security.gate.cartons', $s->contpk) }}"
    data-check-url="{{ route('security.gate.cartons.check', $s->contpk) }}" data-detail-url="{{ route('security.gate.cartons.detail', $s->contpk) }}">
    <div class="sg-section__head">
        <h2 class="sg-section__title">Ceklis carton</h2>
        @if ($stuffQrUrl)
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--sm" id="ckQrOpen" title="QR untuk tim stuffing: scan dari sistem stuffing untuk langsung membuka container ini">@include('menu.exim-export.security.partials.icon', ['name' => 'qr']) QR stuffing</button>
        @endif
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="ckReload" aria-label="Muat ulang">@include('menu.exim-export.security.partials.icon', ['name' => 'refresh'])</button>
    </div>
    <div class="ck-card">
        <div class="ck-prog" aria-hidden="true"><i class="p-ok" data-prog-ok style="width:0"></i><i class="p-scan" data-prog-scan style="width:0"></i></div>
        <div class="ck-prog-text"><span data-prog-text>Memuat carton...</span><span data-prog-pct></span></div>
        <div class="ck-mifbar" data-mifbar hidden></div>
        <div class="ck-stats">
            <button type="button" class="ck-stat is-scan" data-open="scanned"><b data-n="scanned">-</b><span>Siap diceklis</span></button>
            <button type="button" class="ck-stat" data-open="pending"><b data-n="pending">-</b><span>Belum discan stuffing</span></button>
            <button type="button" class="ck-stat is-ok" data-open="checked"><b data-n="checked">-</b><span>Sudah diceklis</span></button>
        </div>
        <div class="sg-alert sg-alert--danger" id="ckCardAlert" role="alert" hidden>
            @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])<div data-msg></div>
        </div>
        <button type="button" class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" id="ckOpen" disabled>
            @include('menu.exim-export.security.partials.icon', ['name' => 'check'])<span data-open-label>Buka ceklis carton</span>
        </button>
    </div>
</section>

{{-- Modal full layar --}}
<div class="ck-modal" id="ckModal" role="dialog" aria-modal="true" aria-labelledby="ckTitle" hidden>
    <div class="ck-head">
        <div class="ck-head__row">
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-ck-close aria-label="Tutup">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</button>
            <h2 class="ck-head__title" id="ckTitle">Ceklis carton<small>{{ $s->is_lcl ? 'LCL' : 'Container' }} {{ $s->ident ?: '-' }} · {{ $s->idship }}</small></h2>
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="ckReloadIn" aria-label="Muat ulang data">@include('menu.exim-export.security.partials.icon', ['name' => 'refresh'])</button>
        </div>
        @php
            $fmtJam = fn($t) => $t ? \Carbon\Carbon::parse($t)->isoFormat('D MMM, HH:mm') : null;
            $poList = array_values(array_filter(array_map('trim', explode(',', (string) $s->POno))));
            $shipInfo = array_filter([
                ['Shipment', $s->idship, true],
                ['Buyer', $s->buyer, false],
                ['PO', $poList ? $poList[0] . (count($poList) > 1 ? ' +' . (count($poList) - 1) . ' PO' : '') : null, true, implode(', ', $poList)],
                ['Tujuan', $s->port_name ?: $s->gate_country, false],
                ['Jenis', $s->type_label, false],
                ['Nopol truk', $s->nopol, true],
                ['No. seal', $s->noseal, true],
                ['Forwarder', $s->fwdnm, false],
                ['Ekspedisi', $s->ekspedisinm ?? null, false],
                ['Tiba', $s->arrived_at ? $s->arrived_at->isoFormat('D MMM, HH:mm') : null, false],
                ['Mulai muat', $fmtJam($s->gate_load_at ?? null) ?: $fmtJam($s->start_ship ?? null), false],
            ], fn($r) => filled($r[1]));
        @endphp
        <dl class="ck-ship" aria-label="Detail shipment">
            @foreach ($shipInfo as $row)
                <div><dt>{{ $row[0] }}</dt><dd class="{{ $row[2] ? 'sg-mono' : '' }}" title="{{ $row[3] ?? $row[1] }}">{{ $row[1] }}</dd></div>
            @endforeach
        </dl>
        <div class="ck-prog" aria-hidden="true"><i class="p-ok" data-prog-ok style="width:0"></i><i class="p-scan" data-prog-scan style="width:0"></i></div>
        <div class="ck-head__meta"><span data-prog-text></span><span data-sync></span></div>
        <div class="ck-tools">
            <div class="ck-search">
                @include('menu.exim-export.security.partials.icon', ['name' => 'search'])
                <input type="search" id="ckQ" placeholder="Scan / cari barcode, no carton, PO, OP" autocomplete="off" enterkeyhint="go">
            </div>
            <button type="button" class="sg-btn" id="ckSelect" aria-pressed="false" title="Pilih beberapa carton untuk diceklis / dibatalkan sekaligus">@include('menu.exim-export.security.partials.icon', ['name' => 'check']) Pilih</button>
        </div>
        <div class="ck-filters">
            <div class="ck-tabs" id="ckTabs" role="tablist" aria-label="Status carton"></div>
            <div class="ck-seg" id="ckMifTabs" role="group" aria-label="Filter MIF" hidden></div>
        </div>
    </div>
    <div class="ck-scanmsg" id="ckScanMsg" role="status" aria-live="assertive">
        @include('menu.exim-export.security.partials.icon', ['name' => 'qr'])<span data-msg>Scan barcode carton dengan scanner untuk ceklis langsung.</span>
    </div>
    <div class="sg-alert sg-alert--danger ck-alert" id="ckAlert" role="alert" hidden>
        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])<div data-msg></div>
    </div>
    <div class="ck-body" id="ckBody"><div class="ck-grid" id="ckGrid"></div></div>
    <div class="ck-foot">
        <button type="button" class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" id="ckAll" hidden></button>
        <div class="ck-selbar" id="ckSelBar" hidden>
            <div class="ck-selbar__row">
                <b id="ckSelInfo">0 dipilih</b>
                <button type="button" class="sg-btn sg-btn--ghost sg-btn--sm" id="ckSelAll">Pilih semua yang tampil</button>
                <button type="button" class="sg-btn sg-btn--ghost sg-btn--sm" id="ckSelClear">Kosongkan</button>
            </div>
            <div class="ck-selbar__act">
                <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" id="ckSelCheck" hidden></button>
                <button type="button" class="sg-btn sg-btn--danger sg-btn--lg" id="ckSelUncheck" hidden></button>
            </div>
        </div>
        <div class="ck-legend">
            <span><i style="background:var(--info-soft);border-color:var(--info)"></i>Siap diceklis</span>
            <span><i style="background:var(--ok-soft);border-color:var(--ok)"></i>Sudah diceklis</span>
            <span><i style="background:var(--surface-sunken);border-color:var(--line-strong);border-style:dashed"></i>Belum discan stuffing</span>
        </div>
    </div>
</div>

{{-- Detail isi carton --}}
<div class="ck-sheet" id="ckDetail" role="dialog" aria-modal="true" aria-labelledby="ckDetTitle" hidden>
    <div class="ck-sheet__panel">
        <div class="ck-sheet__head">
            <h3 class="ck-sheet__title" id="ckDetTitle">Carton<small></small></h3>
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-det-close aria-label="Tutup detail">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
        </div>
        <div id="ckDetBody"></div>
        <div id="ckDetAction"></div>
    </div>
</div>

@if ($stuffQrUrl)
    {{-- QR container untuk tim stuffing --}}
    <div class="ck-sheet" id="ckQrSheet" role="dialog" aria-modal="true" aria-labelledby="ckQrTitle" hidden
        data-qr="{{ $stuffQrUrl }}" data-lib="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js">
        <div class="ck-sheet__panel">
            <div class="ck-sheet__head">
                <h3 class="ck-sheet__title" id="ckQrTitle">{{ $s->ident ?: '-' }}<small>QR stuffing · {{ $s->idship }}</small></h3>
                <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-qr-close aria-label="Tutup">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
            </div>
            <div class="ck-qr" id="ckQrBox"></div>
            <p class="ck-line__sub" style="margin:0;text-align:center">Tim stuffing scan QR ini dari <b>Packing List › Shipment</b> (kotak scan / scanner HP) atau kamera HP yang sudah login Packing List -- langsung membuka shipment & container ini.</p>
            <p class="ck-qr-url">{{ $stuffQrUrl }}</p>
            <button type="button" class="sg-btn sg-btn--lg sg-btn--block" id="ckQrPrint">Print QR</button>
        </div>
    </div>
@endif

<script>
(function () {
    const sec = document.getElementById('ckSection');
    if (!sec) return;
    const modal = document.getElementById('ckModal');
    const detail = document.getElementById('ckDetail');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const time = t => t ? String(t).slice(11, 16) : '';
    const dt = t => t ? String(t).slice(8, 10) + '/' + String(t).slice(5, 7) + ' ' + time(t) : '-';
    const CHECK_ICON = '<svg class="sg-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7"/></svg>';
    const DONE = ['checked', 'locked', 'old'];
    const STATE = { scanned: 'Siap diceklis', pending: 'Belum discan stuffing', checked: 'Sudah diceklis', locked: 'Diceklis · terkunci', old: 'Sudah masuk (data lama)' };
    // [key, label, warna titik] -- warna sama dengan tile & keterangan di bawah
    const TABS = [['scanned', 'Siap diceklis', 'var(--info)'], ['pending', 'Belum discan', 'var(--line-strong)'], ['checked', 'Sudah diceklis', 'var(--ok)'], ['all', 'Semua', '']];
    let mifFilter = ''; // '' = semua MIF
    let data = null, tab = 'scanned', busy = false, loadingP = null, syncedAt = null, lastErr = '', lastMsg = '';
    let selectMode = false, lastRows = [];
    const picked = new Set(); // key carton yang dipilih (mode pilih)
    // dibuka dari QR container stuffing (?ck=1): langsung buka modal ceklis
    let autoOpen = new URLSearchParams(location.search).get('ck') === '1';

    function alertMsg(id, msg) { const a = $(id); a.querySelector('[data-msg]').textContent = msg || ''; a.hidden = !msg; }
    const mifOf = c => (c.mif || []).map(m => 'MIF ' + m).join('/');
    const inMif = c => !mifFilter || (c.mif || []).includes(mifFilter);
    const tally = list => ({
        scanned: list.filter(c => c.state === 'scanned').length,
        pending: list.filter(c => c.state === 'pending').length,
        checked: list.filter(c => DONE.includes(c.state)).length,
        all: list.length,
    });
    const counts = () => tally(data.cartons);
    const poText = p => `OP ${p.OP} · ${p.POno}`;
    // Qty "aktual/plan" (pcs / pcsp); disorot kalau aktual tidak sama dengan plan.
    const qty = (a, p) => { a = Number(a) || 0; p = Number(p) || 0; return !p ? `${a}` : `<span class="${a === p ? '' : 'ck-miss'}">${a}/${p}</span>`; };
    const pickable = c => !!c && !data.locked && (c.state === 'scanned' || c.state === 'checked');
    const qrSheet = document.getElementById('ckQrSheet');
    const lockBody = () => document.body.classList.toggle('sg-noscroll', !modal.hidden || !detail.hidden || (qrSheet && !qrSheet.hidden));

    // ---------- konfirmasi (Promise<boolean>): dialog global Security Gate (sgConfirm di index.blade.php) ----------
    // msg berisi HTML yang sudah di-escape (esc) untuk menebalkan jumlah / no. carton
    function ask(msg, yesLabel) {
        const undo = /batal/i.test(yesLabel || '');
        return window.sgConfirm(msg, {
            html: true, okText: yesLabel || 'Ya',
            title: undo ? 'Batalkan ceklis carton?' : 'Ceklis carton masuk container?',
            type: undo ? 'warning' : 'question',
        });
    }

    // ---------- data ----------
    function load() {
        return loadingP || (loadingP = loadNow().finally(() => { loadingP = null; }));
    }
    async function loadNow() {
        ['ckReload', 'ckReloadIn'].forEach(id => $(id).classList.add('ck-spin'));
        try {
            const res = await fetch(sec.dataset.url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'Data carton tidak bisa dimuat.');
            data = json;
            syncedAt = new Date();
            alertMsg('ckCardAlert', '');
            alertMsg('ckAlert', '');
            render();
            if (autoOpen) {
                autoOpen = false;
                try { const u = new URL(location.href); u.searchParams.delete('ck'); history.replaceState(null, '', u); } catch (e) {}
                if (modal.hidden) openModal();
            }
        } catch (e) {
            alertMsg('ckCardAlert', e.message);
            alertMsg('ckAlert', e.message);
        } finally {
            ['ckReload', 'ckReloadIn'].forEach(id => $(id).classList.remove('ck-spin'));
        }
    }

    function render() {
        const n = counts(), t = n.all || 1, locked = !!data.locked;
        document.querySelectorAll('[data-prog-ok]').forEach(el => el.style.width = (n.checked / t * 100) + '%');
        document.querySelectorAll('[data-prog-scan]').forEach(el => el.style.width = (n.scanned / t * 100) + '%');
        document.querySelectorAll('[data-prog-text]').forEach(el => el.innerHTML = n.all
            ? `<b>${n.checked}</b> / ${n.all} carton masuk container` : 'Belum ada carton di container ini.');
        sec.querySelector('[data-prog-pct]').textContent = n.all ? Math.round(n.checked / n.all * 100) + '%' : '';
        sec.querySelectorAll('[data-n]').forEach(el => el.textContent = n[el.dataset.n]);
        const mifs = data.mifs || [];
        const bar = sec.querySelector('[data-mifbar]');
        bar.hidden = mifs.length < 2;
        bar.innerHTML = mifs.map(m => {
            const t = tally(data.cartons.filter(c => (c.mif || []).includes(m)));
            return `<span><span class="ck-mif ${m === '2' ? 'm2' : ''}">MIF ${esc(m)}</span> <b>${t.checked}</b>/${t.all} masuk${t.scanned ? ` · ${t.scanned} siap` : ''}</span>`;
        }).join('');
        modal.querySelector('[data-sync]').textContent = syncedAt ? 'diperbarui ' + syncedAt.toTimeString().slice(0, 5) : '';
        $('ckOpen').disabled = !n.all;
        sec.querySelector('[data-open-label]').textContent = locked ? 'Lihat ceklis carton' : (n.scanned ? `Buka ceklis carton (${n.scanned} siap)` : 'Buka ceklis carton');
        if (!modal.hidden) renderGrid();
    }

    function renderGrid() {
        const locked = !!data.locked;
        const mifs = data.mifs || [];
        if (mifFilter && !mifs.includes(mifFilter)) mifFilter = '';
        $('ckMifTabs').hidden = mifs.length < 2;
        $('ckMifTabs').innerHTML = '<span class="ck-seg__label">MIF</span>' + [['', 'Semua', data.cartons.length], ...mifs.map(m => [m, 'MIF ' + m, data.cartons.filter(c => (c.mif || []).includes(m)).length])]
            .map(([k, l, cnt]) => `<button type="button" class="${mifFilter === k ? 'is-on' : ''}" data-mif="${esc(k)}" aria-pressed="${mifFilter === k}">${esc(l)}<b>${cnt}</b></button>`).join('');
        const scope = data.cartons.filter(inMif);
        const n = tally(scope);
        const q = ($('ckQ').value || '').trim().toLowerCase();
        $('ckTabs').innerHTML = TABS.map(([k, l, color]) => `<button type="button" role="tab" class="ck-tab ${tab === k ? 'is-on' : ''}" data-tab="${k}" aria-selected="${tab === k}">${color ? `<i class="ck-dot" style="background:${color}"></i>` : ''}${l}<b>${n[k]}</b></button>`).join('');
        const rows = scope.filter(c => (tab === 'all' || (tab === 'checked' ? DONE.includes(c.state) : c.state === tab))
            && (!q || [c.carton, c.nobar, mifOf(c), ...(c.po_op || []), ...(c.colors || []), ...(c.members || []), ...(c.buyer || []), ...(c.style || [])].join(' ').toLowerCase().includes(q)));
        lastRows = rows;
        // buang pilihan yang statusnya sudah berubah / tidak ada lagi
        [...picked].forEach(k => { if (!pickable(cartonBy(k))) picked.delete(k); });
        $('ckGrid').innerHTML = rows.length ? rows.map(c => {
            const kind = c.type === 'bundle' ? `Besar·${c.members.length}` : (c.type === 'mix' ? 'Mix' : '');
            const when = c.state === 'scanned' ? time(c.cekdate1) : (c.cekdate2 ? time(c.cekdate2) : '');
            const markable = !locked && (c.state === 'scanned' || c.state === 'checked');
            const pos = c.pos || [];
            const refs = [...new Set(pos.map(p => p.poref).filter(Boolean))];
            const pickCls = selectMode ? (pickable(c) ? ' is-pickable' : '') + (picked.has(c.key) ? ' is-picked' : '') : '';
            return `<div class="ck-tile st-${c.state}${pickCls}" role="button" tabindex="0" data-key="${esc(c.key)}" aria-label="Carton ${esc(c.carton)}, ${STATE[c.state]}"${selectMode ? ` aria-pressed="${picked.has(c.key)}"` : ''}
                    title="${esc(pos.map(p => poText(p) + (p.poref ? ' · ' + p.poref : '')).join('\n'))}">
                <span class="ck-tile__no">${esc(c.carton)}</span>
                <span class="ck-tile__po">${pos.length ? esc(poText(pos[0])) + (pos.length > 1 ? ` <b>+${pos.length - 1}</b>` : '') : '-'}</span>
                <span class="ck-tile__sub">${esc(refs.join(', ') || '-')}${(c.colors || []).length ? ' · ' + esc(c.colors.join(', ')) : ''}</span>
                <span class="ck-tile__foot">${(c.mif || []).map(m => `<span class="ck-mif ${m === '2' ? 'm2' : ''}">MIF ${esc(m)}</span>`).join('')}${kind ? `<span class="ck-tile__kind">${kind}</span>` : ''}<span title="Aktual ${c.pcs} / plan ${c.pcsp || '-'} pcs">${qty(c.pcs, c.pcsp)} pcs${when ? ' · ' + when : ''}</span></span>
                <button type="button" class="ck-mark" data-mark ${markable ? '' : 'disabled'} aria-label="${c.state === 'scanned' ? 'Ceklis' : 'Batalkan ceklis'} carton ${esc(c.carton)}">${CHECK_ICON}</button>
            </div>`;
        }).join('') : `<div class="ck-empty">${q ? 'Tidak ada carton yang cocok.' : (tab === 'scanned' ? 'Belum ada carton yang discan tim stuffing.' : 'Tidak ada carton.')}</div>`;
        const all = $('ckAll');
        all.hidden = locked || !n.scanned || selectMode;
        all.textContent = `Ceklis semua yang sudah discan${mifFilter ? ' MIF ' + mifFilter : ''} (${n.scanned} carton)`;
        if (locked && selectMode) selectMode = false;
        $('ckSelect').hidden = locked;
        renderSel();
    }

    // ---------- mode pilih: ceklis / batal ceklis massal ----------
    function renderSel() {
        modal.classList.toggle('is-select', selectMode);
        $('ckSelect').setAttribute('aria-pressed', String(selectMode));
        $('ckSelBar').hidden = !selectMode;
        if (!selectMode) return;
        const list = [...picked].map(cartonBy).filter(Boolean);
        const toCheck = list.filter(c => c.state === 'scanned'), toUncheck = list.filter(c => c.state === 'checked');
        $('ckSelInfo').textContent = list.length ? `${list.length} carton dipilih` : 'Ketuk carton untuk memilih';
        $('ckSelCheck').hidden = !toCheck.length;
        $('ckSelCheck').textContent = `Ceklis ${toCheck.length} carton`;
        $('ckSelUncheck').hidden = !toUncheck.length;
        $('ckSelUncheck').textContent = `Batalkan ceklis ${toUncheck.length}`;
        const visible = lastRows.filter(pickable);
        $('ckSelAll').disabled = !visible.length;
        $('ckSelAll').textContent = visible.length && visible.every(c => picked.has(c.key)) ? 'Batal pilih yang tampil' : `Pilih semua yang tampil (${visible.length})`;
        $('ckSelClear').disabled = !picked.size;
    }
    function setSelect(on) {
        selectMode = !!on && !data?.locked;
        picked.clear();
        if (data) renderGrid();
    }
    function pickTile(c) {
        if (!pickable(c)) {
            return alertMsg('ckAlert', c && c.state === 'pending'
                ? `Carton ${c.carton} belum discan / diproses tim stuffing, belum bisa dipilih.` : 'Carton ini tidak bisa diubah.');
        }
        alertMsg('ckAlert', '');
        picked.has(c.key) ? picked.delete(c.key) : picked.add(c.key);
        renderGrid();
    }
    async function saveMany(check) {
        const list = [...picked].map(cartonBy).filter(c => c && c.state === (check ? 'scanned' : 'checked'));
        if (!list.length || busy) return;
        const pcs = list.reduce((a, c) => a + (Number(c.pcs) || 0), 0);
        const names = list.slice(0, 12).map(c => esc(c.carton)).join(', ') + (list.length > 12 ? ` dan ${list.length - 12} lainnya` : '');
        const ok = await ask(check
            ? `Ceklis <b>${list.length} carton</b> (${pcs} pcs) sudah masuk container?<br><small>${names}</small>`
            : `Batalkan ceklis <b>${list.length} carton</b>? Carton kembali tercatat belum masuk container.<br><small>${names}</small>`,
            check ? `Ya, ceklis ${list.length}` : `Ya, batalkan ${list.length}`);
        if (!ok) return;
        if (await save({ keys: list.map(c => c.key), checked: check ? 1 : 0 }, null)) {
            scanMsg('ok', esc(lastMsg || `${list.length} carton ${check ? 'diceklis' : 'dibatalkan'}.`));
            setSelect(false);
        }
    }

    const cartonBy = key => (data?.cartons || []).find(c => c.key === key);

    // ---------- ceklis (selalu konfirmasi) ----------
    async function toggle(c, tile) {
        if (!c || busy || data.locked) return;
        if (c.state === 'pending') return alertMsg('ckAlert', `Carton ${c.carton} belum discan / diproses tim stuffing, belum bisa diceklis.`);
        if (c.state !== 'scanned' && c.state !== 'checked') return;
        const check = c.state === 'scanned';
        const ok = await ask(check
            ? `Ceklis carton <b>${esc(c.carton)}</b> (${qty(c.pcs, c.pcsp)} pcs) sudah masuk container?`
            : `Batalkan ceklis carton <b>${esc(c.carton)}</b>? Carton kembali tercatat belum masuk container.`,
            check ? 'Ya, ceklis' : 'Ya, batalkan');
        if (!ok) return false;
        return save({ keys: [c.key], checked: check ? 1 : 0 }, tile);
    }

    async function save(body, tile) {
        if (busy) return false;
        busy = true;
        alertMsg('ckAlert', '');
        if (tile) tile.classList.add('is-busy');
        const fd = new FormData();
        Object.entries(body).forEach(([k, v]) => Array.isArray(v) ? v.forEach(x => fd.append(k + '[]', x)) : fd.append(k, v));
        let okSave = false;
        lastErr = '';
        try {
            const res = await fetch(sec.dataset.checkUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: fd });
            const json = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(json.message || 'Ceklis gagal disimpan.');
            lastMsg = json.message || '';
            okSave = true;
        } catch (e) {
            lastErr = e.message;
            alertMsg('ckAlert', e.message);
        } finally {
            busy = false;
            await load();
        }
        return okSave;
    }

    // ---------- detail isi carton ----------
    async function openDetail(c) {
        if (!c) return;
        $('ckDetTitle').innerHTML = `${esc(c.carton)}<small>${STATE[c.state]}</small>`;
        $('ckDetBody').innerHTML = '<p class="ck-line__sub" style="margin:0">Memuat isi carton...</p>';
        $('ckDetAction').innerHTML = '';
        detail.hidden = false;
        lockBody();
        try {
            const res = await fetch(sec.dataset.detailUrl + '?key=' + encodeURIComponent(c.key), { headers: { 'Accept': 'application/json' } });
            const d = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(d.message || 'Detail carton tidak bisa dimuat.');
            const kind = d.type === 'bundle' ? `Carton besar · isi ${d.members.length} carton (${d.members.join(', ')})` : (d.type === 'mix' ? 'Carton mix (lintas PO)' : 'Carton');
            $('ckDetTitle').innerHTML = `${esc(d.carton)}<small>${esc(kind)}</small>`;
            $('ckDetBody').innerHTML = `
                <div style="margin-bottom:var(--space-3)"><span class="ck-badge st-${d.state}">${STATE[d.state] || d.state}</span></div>
                <dl class="ck-kv" style="margin-bottom:var(--space-3)">
                    <dt>MIF</dt><dd>${(d.mif || []).map(m => `<span class="ck-mif ${m === '2' ? 'm2' : ''}">MIF ${esc(m)}</span>`).join(' ') || '-'}</dd>
                    <dt>PO / OP</dt><dd>${(d.pos || []).map(p => `<span class="sg-mono">${esc(p.POno)}</span> · OP ${esc(p.OP)}${p.poref ? ' · POref ' + esc(p.poref) : ''}`).join('<br>') || '-'}</dd>
                    ${[...new Set((d.lines || []).map(l => l.buyer).filter(Boolean))].length ? `<dt>Buyer</dt><dd>${esc([...new Set(d.lines.map(l => l.buyer).filter(Boolean))].join(', '))}</dd>` : ''}
                    ${[...new Set((d.lines || []).map(l => l.style).filter(Boolean))].length ? `<dt>Style</dt><dd>${esc([...new Set(d.lines.map(l => l.style).filter(Boolean))].join(', '))}</dd>` : ''}
                    <dt>Barcode</dt><dd class="sg-mono">${esc(d.nobar || '-')}</dd>
                    <dt>Isi (aktual/plan)</dt><dd><b>${qty(d.pcs, d.pcsp)}</b> pcs${d.pcsp && d.pcs !== d.pcsp ? ` <span class="ck-miss">(${d.pcs < d.pcsp ? 'kurang ' + (d.pcsp - d.pcs) : 'lebih ' + (d.pcs - d.pcsp)})</span>` : ''}</dd>
                    ${d.nw || d.gw ? `<dt>NW / GW</dt><dd>${d.nw ?? '-'} / ${d.gw ?? '-'} kg</dd>` : ''}
                    ${d.dim ? `<dt>Ukuran</dt><dd>${esc(d.dim)}</dd>` : ''}
                    <dt>Discan stuffing</dt><dd>${dt(d.cekdate1)}</dd>
                    <dt>Diceklis security</dt><dd>${dt(d.cekdate2)}</dd>
                </dl>
                ${(d.lines || []).map(l => `<div class="ck-line" style="margin-bottom:var(--space-2)">
                    <div class="ck-line__head">${esc(l.color || '-')}${l.secsz ? ' · ' + esc(l.secsz) : ''}${l.source ? ` <span style="font-size:11px;font-weight:600;border-radius:999px;padding:1px 8px;background:${l.is_cmt ? '#ede9fe' : '#f1f5f9'};color:${l.is_cmt ? '#5b21b6' : '#475569'};">${l.is_cmt ? 'Subkon ' : ''}${esc(l.source)}</span>` : ''} <span class="ck-line__sub">· ${qty(l.pcs, l.pcsp)} pcs${d.type === 'bundle' ? ' · ctn ' + esc(l.carton) : ''}</span></div>
                    <div class="ck-line__sub">PO ${esc(l.POno || '-')} · OP ${esc(l.OP || '-')}${l.poref ? ' · ' + esc(l.poref) : ''}${l.style ? ' · ' + esc(l.style) : ''}${l.buyer ? ' · ' + esc(l.buyer) : ''}</div>
                    <div class="ck-sizes">${(l.sizes || []).map(s => `<span class="ck-size ${s.plan && s.qty !== s.plan ? 'is-miss' : ''}" title="Aktual ${s.qty} / plan ${s.plan}">${esc(s.label)}<b>${s.qty}</b>${s.plan ? `<i>/${s.plan}</i>` : ''}</span>`).join('') || '<span class="ck-line__sub">Belum ada qty</span>'}</div>
                </div>`).join('')}`;
            const locked = !!data.locked;
            const fresh = cartonBy(c.key) || c;
            $('ckDetAction').innerHTML = locked
                ? '<p class="ck-line__sub" style="margin:0;text-align:center">Container sudah keluar MIF -- ceklis hanya bisa dilihat.</p>'
                : fresh.state === 'scanned' ? '<button type="button" class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" data-det-toggle>Ceklis masuk container</button>'
                : fresh.state === 'checked' ? '<button type="button" class="sg-btn sg-btn--danger sg-btn--lg sg-btn--block" data-det-toggle>Batalkan ceklis</button>'
                : fresh.state === 'pending' ? '<p class="ck-line__sub" style="margin:0;text-align:center">Belum discan / diproses tim stuffing -- belum bisa diceklis.</p>' : '';
            const btn = $('ckDetAction').querySelector('[data-det-toggle]');
            if (btn) btn.addEventListener('click', async () => {
                if (await toggle(cartonBy(c.key) || c, null)) closeDetail();
            });
        } catch (e) {
            $('ckDetBody').innerHTML = `<div class="sg-alert sg-alert--danger" role="alert"><div>${esc(e.message)}</div></div>`;
        }
    }
    function closeDetail() { detail.hidden = true; lockBody(); }

    // ---------- modal ----------
    function openModal(t) {
        if (!data) return;
        const n = counts();
        tab = t || (n.scanned ? 'scanned' : (n.checked ? 'checked' : 'all'));
        $('ckQ').value = '';
        alertMsg('ckAlert', '');
        selectMode = false;
        picked.clear();
        modal.hidden = false;
        lockBody();
        renderGrid();
        $('ckBody').scrollTop = 0;
        load(); // data terbaru saat dibuka
    }
    function closeModal() { modal.hidden = true; selectMode = false; picked.clear(); lockBody(); }

    // ---------- scan barcode carton -> ceklis langsung (tanpa konfirmasi: scan = konfirmasi) ----------
    let audio = null;
    function beep(ok) {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            const o = audio.createOscillator(), g = audio.createGain();
            o.frequency.value = ok ? 1250 : 330;
            o.type = ok ? 'sine' : 'square';
            g.gain.value = 0.15;
            o.connect(g); g.connect(audio.destination);
            o.start(); o.stop(audio.currentTime + (ok ? 0.12 : 0.35));
        } catch (e) {}
        if (!ok && navigator.vibrate) navigator.vibrate(200);
    }
    function scanMsg(kind, html, key) {
        const el = $('ckScanMsg');
        el.className = 'ck-scanmsg' + (kind ? ' is-' + kind : '');
        el.querySelector('[data-msg]').innerHTML = html;
        if (kind === 'ok' || kind === 'err') beep(kind === 'ok');
        if (key) {
            const t = $('ckGrid').querySelector(`.ck-tile[data-key="${CSS.escape(key)}"]`);
            if (t) { t.classList.add('is-flash'); t.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }
        }
    }
    const byNobar = (list, code) => data.cartons.find(c => (c[list] || []).some(n => String(n).trim().toUpperCase() === code));
    const scanQueue = [];
    let scanning = false;
    async function processScan(raw) {
        const code = String(raw).trim().toUpperCase();
        scanMsg('busy', `Memproses <b>${esc(raw)}</b>...`);
        let c = byNobar('nobars', code);
        if (!c || c.state === 'pending') { await load(); c = data && byNobar('nobars', code); } // status terbaru dari stuffing
        if (!data) return scanMsg('err', 'Data carton belum termuat. Tekan muat ulang.');
        if (!c) {
            const m = byNobar('member_nobars', code);
            return scanMsg('err', m ? `<b>${esc(raw)}</b> adalah isi Carton Besar <b>${esc(m.carton)}</b> -- scan barcode carton besarnya.`
                : `Barcode <b>${esc(raw)}</b> tidak ada di container ini.`);
        }
        if (data.locked) return scanMsg('err', 'Container sudah keluar MIF -- ceklis terkunci.');
        if (c.state === 'pending') return scanMsg('err', `Carton <b>${esc(c.carton)}</b> belum discan tim stuffing.`, c.key);
        if (DONE.includes(c.state)) return scanMsg('info', `Carton <b>${esc(c.carton)}</b> sudah diceklis${c.cekdate2 ? ' jam ' + time(c.cekdate2) : ''}.`, c.key);
        if (await save({ keys: [c.key], checked: 1 }, null)) {
            scanMsg('ok', `Carton <b>${esc(c.carton)}</b> ✓ masuk container (${qty(c.pcs, c.pcsp)} pcs).`, c.key);
        } else {
            scanMsg('err', esc(lastErr || 'Ceklis gagal disimpan.'));
        }
    }
    async function drainScans() {
        if (scanning) return;
        scanning = true;
        while (scanQueue.length) {
            try { await processScan(scanQueue.shift()); } catch (e) { scanMsg('err', esc(e.message)); }
        }
        scanning = false;
    }
    // dipanggil dari partial stuffing-scan (scanner HID / QR). code null = cukup buka modal.
    window.sgCartonScan = function (code) {
        if (modal.hidden) { if (data) openModal(); else autoOpen = true; }
        if (!code) return;
        scanQueue.push(code);
        drainScans();
    };

    // ---------- QR stuffing (container tiba & belum keluar) ----------
    if (qrSheet) {
        let qrLib = null;
        const loadQrLib = () => window.QRCode ? Promise.resolve() : (qrLib = qrLib || new Promise((resolve, reject) => {
            const tag = document.createElement('script');
            tag.src = qrSheet.dataset.lib;
            tag.onload = resolve;
            tag.onerror = () => { qrLib = null; tag.remove(); reject(new Error('Pembuat QR gagal dimuat. Periksa sinyal lalu coba lagi.')); };
            document.head.appendChild(tag);
        }));
        const closeQr = () => { qrSheet.hidden = true; lockBody(); };
        $('ckQrOpen').addEventListener('click', async () => {
            qrSheet.hidden = false;
            lockBody();
            const box = $('ckQrBox');
            if (box.childElementCount) return;
            box.textContent = 'Membuat QR...';
            try {
                await loadQrLib();
                box.textContent = '';
                new window.QRCode(box, { text: qrSheet.dataset.qr, width: 480, height: 480, correctLevel: window.QRCode.CorrectLevel.M });
            } catch (e) {
                box.textContent = e.message;
            }
        });
        qrSheet.querySelector('[data-qr-close]').addEventListener('click', closeQr);
        qrSheet.addEventListener('click', e => { if (e.target === qrSheet) closeQr(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !qrSheet.hidden) closeQr(); });
        $('ckQrPrint').addEventListener('click', () => {
            const c = $('ckQrBox').querySelector('canvas');
            const src = c ? c.toDataURL('image/png') : $('ckQrBox').querySelector('img')?.src;
            if (!src) return;
            // dicetak lewat iframe tersembunyi: aplikasi layar penuh tidak pindah ke tab / jendela Chrome baru
            const title = $('ckQrTitle').firstChild.textContent.trim();
            document.getElementById('ckQrPrintFrame')?.remove();
            const frame = document.createElement('iframe');
            frame.id = 'ckQrPrintFrame';
            frame.setAttribute('aria-hidden', 'true');
            frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;visibility:hidden';
            frame.srcdoc = `<!doctype html><html><head><title>QR stuffing ${esc(title)}</title><style>body{font-family:system-ui,sans-serif;text-align:center;padding:24px}
                h1{font:700 30px ui-monospace,monospace;margin:0 0 4px}p{margin:4px 0;color:#52525b}img{width:300px;height:300px;margin:16px 0;image-rendering:pixelated}</style></head>
                <body><h1>${esc(title)}</h1><p>${esc($('ckQrTitle').querySelector('small').textContent)}</p><img src="${src}" alt="QR">
                <p><b>Scan dari sistem stuffing</b> (Packing List › Shipment)</p></body></html>`;
            frame.onload = () => {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            };
            document.body.appendChild(frame);
        });
    }

    // ---------- event ----------
    $('ckOpen').addEventListener('click', () => openModal());
    sec.querySelectorAll('[data-open]').forEach(b => b.addEventListener('click', () => openModal(b.dataset.open)));
    $('ckReload').addEventListener('click', load);
    $('ckReloadIn').addEventListener('click', load);
    modal.querySelector('[data-ck-close]').addEventListener('click', closeModal);
    detail.querySelector('[data-det-close]').addEventListener('click', closeDetail);
    detail.addEventListener('click', e => { if (e.target === detail) closeDetail(); });
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape') return;
        if (!$('sgDialog').hidden) return; // ditangani dialog konfirmasi
        if (!detail.hidden) closeDetail(); else if (!modal.hidden && selectMode) setSelect(false); else if (!modal.hidden) closeModal();
    });
    $('ckTabs').addEventListener('click', e => {
        const t = e.target.closest('[data-tab]');
        if (t) { tab = t.dataset.tab; renderGrid(); $('ckBody').scrollTop = 0; }
    });
    $('ckMifTabs').addEventListener('click', e => {
        const t = e.target.closest('[data-mif]');
        if (t) { mifFilter = t.dataset.mif; renderGrid(); $('ckBody').scrollTop = 0; }
    });
    $('ckQ').addEventListener('input', renderGrid);
    $('ckQ').addEventListener('keydown', e => {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const v = $('ckQ').value.trim();
        if (!v) return;
        $('ckQ').value = '';
        renderGrid();
        (window.sgHandleScan || window.sgCartonScan)(v);
    });
    // lingkaran pojok = ceklis cepat; bagian lain tile = detail isi carton
    $('ckGrid').addEventListener('click', e => {
        const tile = e.target.closest('.ck-tile');
        if (!tile) return;
        const c = cartonBy(tile.dataset.key);
        if (selectMode) { pickTile(c); return; } // mode pilih: ketuk = pilih / batal pilih
        if (e.target.closest('[data-mark]')) { e.stopPropagation(); toggle(c, tile); return; }
        openDetail(c);
    });
    $('ckGrid').addEventListener('keydown', e => {
        const tile = e.target.closest('.ck-tile');
        if (tile && (e.key === 'Enter' || e.key === ' ') && e.target === tile) {
            e.preventDefault();
            selectMode ? pickTile(cartonBy(tile.dataset.key)) : openDetail(cartonBy(tile.dataset.key));
        }
    });
    $('ckSelect').addEventListener('click', () => setSelect(!selectMode));
    $('ckSelAll').addEventListener('click', () => {
        const visible = lastRows.filter(pickable);
        const allOn = visible.length && visible.every(c => picked.has(c.key));
        visible.forEach(c => allOn ? picked.delete(c.key) : picked.add(c.key));
        renderGrid();
    });
    $('ckSelClear').addEventListener('click', () => { picked.clear(); renderGrid(); });
    $('ckSelCheck').addEventListener('click', () => saveMany(true));
    $('ckSelUncheck').addEventListener('click', () => saveMany(false));
    $('ckAll').addEventListener('click', async () => {
        const targets = data.cartons.filter(c => inMif(c) && c.state === 'scanned');
        const n = targets.length;
        if (!n || !(await ask(`Ceklis <b>${n} carton</b>${mifFilter ? ' <b>MIF ' + esc(mifFilter) + '</b>' : ''} yang sudah discan tim stuffing sebagai sudah masuk container?`, 'Ya, ceklis semua'))) return;
        save(mifFilter ? { keys: targets.map(c => c.key), checked: 1 } : { all: 1, checked: 1 });
    });
    load();
})();
</script>
