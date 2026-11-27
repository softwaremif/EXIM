{{-- Halaman Pintu (ikon di header Security Gate): pintu per MIF, container menuju / menunggu pintu, riwayat hari ini.
     Data: GateDoorController::board. Pintu kosong di MIF pos security bisa langsung diisi (aktifkan lewat security.gate.door.activate).
     Pintu terisi: Selesai muat (security.gate.door.finish, pintu kosong, truk menunggu gate out) / Keluar pabrik: pilih jam
     keluar lalu langsung simpan (security.gate.door.out; keluar cepat, hanya urutan jam yang dicek). --}}
@php
    $dur = function ($at) {
        $m = (int) $at->diffInMinutes(now());
        return $m < 60 ? $m . ' mnt' : intdiv($m, 60) . ' j ' . ($m % 60) . ' mnt';
    };
    $link = fn($contpk) => route('security.gate.index', ['cont' => $contpk]);
    $canAct = fn($mif) => $posMif === null || $posMif === $mif;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pintu · Security Gate</title>
    <link rel="shortcut icon" href="{!! asset('public/css/images/morich.gif') !!}">
    <link rel="manifest" href="{{ route('security.gate.manifest') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <script>
        try { if (localStorage.getItem('sg-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/css/security-gate.css') }}?v={{ filemtime(public_path('css/security-gate.css')) }}">
</head>
<body>
<div class="sg-screen">
    <header class="sg-appbar">
        <a class="sg-btn sg-btn--ghost sg-btn--icon" href="{{ route('security.gate.index') }}" aria-label="Kembali ke daftar container" title="Daftar container">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</a>
        <h1 class="sg-appbar__title">
            Pintu
            <span class="sg-appbar__sub">{{ now()->isoFormat('dddd, D MMM YYYY') }} · diperbarui {{ now()->format('H:i') }}</span>
        </h1>
        <span class="sg-appbar__shift">{{ $login }}</span>
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" onclick="location.reload()" aria-label="Muat ulang" title="Muat ulang">@include('menu.exim-export.security.partials.icon', ['name' => 'refresh'])</button>
    </header>

    <main class="sg-main dk">
        <div class="dk-grid">
            @foreach ($mifs as $mif)
                @php
                    $b = $board[$mif];
                    $filled = count(array_filter($b['doors']));
                    // calon isi pintu: container pindahan ke MIF ini & yang sudah gate in tapi belum di pintu
                    $candidates = array_merge($b['incoming'], $b['waiting']);
                @endphp
                <section class="sg-section dk-mif {{ $posMif === $mif ? 'is-pos' : '' }}">
                    <div class="sg-section__head">
                        <h2 class="sg-section__title">MIF {{ $mif }}</h2>
                        <span class="dk-count">{{ $posMif === $mif ? 'Pos Anda · ' : '' }}{{ $filled }}/{{ count($b['doors']) }} terisi</span>
                    </div>

                    <div class="dk-docks">
                        @foreach ($b['doors'] as $p => $c)
                            @if ($c)
                                @php $mins = (int) $c->at->diffInMinutes(now()); @endphp
                                <div class="dk-dock is-busy {{ $mins >= 240 ? 'is-long' : '' }}">
                                    <a class="dk-dock__link" href="{{ $link($c->contpk) }}">
                                        <span class="dk-dock__no">Pintu {{ $p }}</span>
                                        <strong class="sg-mono">{{ $c->ident }}</strong>
                                        <span class="dk-dock__meta">{{ $c->idship }} · {{ $c->buyer ?: '-' }}</span>
                                        <span class="dk-dock__time">@include('menu.exim-export.security.partials.icon', ['name' => 'clock']) Sejak {{ $c->at->format('H:i') }} · {{ $dur($c->at) }}</span>
                                    </a>
                                    @if ($canAct($mif))
                                        {{-- keluarkan truk: selesai muat (pintu kosong, ketuk 2x) atau langsung proses gate out --}}
                                        <div class="dk-dock__actions">
                                            <button type="button" class="sg-btn" data-dock-finish data-cont="{{ $c->contpk }}" data-pintu="{{ $p }}">@include('menu.exim-export.security.partials.icon', ['name' => 'check']) Selesai muat</button>
                                            <button type="button" class="sg-btn sg-btn--primary" data-dock-out data-cont="{{ $c->contpk }}" data-ident="{{ $c->ident }}" data-since="{{ $c->at->format('Y-m-d\TH:i') }}">@include('menu.exim-export.security.partials.icon', ['name' => 'truck']) Keluar pabrik</button>
                                        </div>
                                        <p class="dk-err" hidden></p>
                                    @endif
                                </div>
                            @else
                                <div class="dk-dock">
                                    <span class="dk-dock__no">Pintu {{ $p }}</span>
                                    <strong class="dk-dock__empty">Kosong</strong>
                                    @if ($canAct($mif) && $candidates)
                                        <form class="dk-fill" data-dock-fill data-mif="{{ $mif }}" data-pintu="{{ $p }}">
                                            <select class="sg-input" aria-label="Container untuk pintu {{ $p }} MIF {{ $mif }}" required>
                                                <option value="">Pilih container…</option>
                                                @foreach ($candidates as $o)
                                                    <option value="{{ $o->contpk }}">{{ $o->ident }} · {{ $o->idship }}{{ in_array($o, $b['incoming'], true) ? ' (dari MIF ' . $o->from . ')' : '' }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="sg-btn sg-btn--primary">Aktifkan</button>
                                            <p class="dk-err" hidden></p>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="dk-list">
                        <h3>Selesai muat, menunggu keluar <span>{{ count($b['done']) }}</span></h3>
                        @forelse ($b['done'] as $c)
                            <div class="dk-done">
                                <a href="{{ $link($c->contpk) }}"><b class="sg-mono">{{ $c->ident }}</b> {{ $c->idship }} · pintu {{ $c->pintu }} · selesai {{ $c->at->format('H:i') }} ({{ $dur($c->at) }})</a>
                                <button type="button" class="sg-btn sg-btn--primary sg-btn--sm" data-dock-out data-cont="{{ $c->contpk }}" data-ident="{{ $c->ident }}" data-since="{{ $c->at->format('Y-m-d\TH:i') }}">Keluar pabrik</button>
                            </div>
                        @empty
                            <p>Tidak ada truk yang menunggu keluar.</p>
                        @endforelse
                    </div>
                    <div class="dk-list">
                        <h3>Menuju MIF {{ $mif }} <span>{{ count($b['incoming']) }}</span></h3>
                        @forelse ($b['incoming'] as $c)
                            <a href="{{ $link($c->contpk) }}"><b class="sg-mono">{{ $c->ident }}</b> {{ $c->idship }} · dari MIF {{ $c->from }} jam {{ $c->at->format('H:i') }}</a>
                        @empty
                            <p>Tidak ada container pindahan.</p>
                        @endforelse
                    </div>
                    <div class="dk-list">
                        <h3>Belum di pintu <span>{{ count($b['waiting']) }}</span></h3>
                        @forelse ($b['waiting'] as $c)
                            <a href="{{ $link($c->contpk) }}"><b class="sg-mono">{{ $c->ident }}</b> {{ $c->idship }} · {{ $c->buyer ?: '-' }} · gate in {{ $c->at->isoFormat('D MMM HH:mm') }}</a>
                        @empty
                            <p>Semua container sudah di pintu / keluar.</p>
                        @endforelse
                    </div>
                </section>
            @endforeach
        </div>

        @if ($board['unplaced'])
            <section class="sg-section">
                <h2 class="sg-section__title">Posisi belum diketahui</h2>
                <p class="sg-hint" style="margin:0">Gate in dicatat akun selain Security1 / Security2. Buka container lalu aktifkan di pintu.</p>
                <div class="dk-list">
                    @foreach ($board['unplaced'] as $c)
                        <a href="{{ $link($c->contpk) }}"><b class="sg-mono">{{ $c->ident }}</b> {{ $c->idship }} · gate in {{ $c->at->isoFormat('D MMM HH:mm') }}</a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="sg-section">
            <h2 class="sg-section__title">Riwayat hari ini</h2>
            @if ($history)
                <ol class="dk-log">
                    @foreach ($history as $e)
                        <li class="is-{{ $e->kind }}">
                            <time>{{ $e->at->format('H:i') }}</time>
                            <div>
                                <a href="{{ $link($e->contpk) }}" class="sg-mono">{{ $e->ident }}</a>
                                <span>MIF {{ $e->mif }}{{ $e->pintu ? ' · Pintu ' . $e->pintu : '' }} · {{ $e->text }}{{ $e->by ? ' · ' . $e->by : '' }}</span>
                                @if ($e->note)
                                    <em>{{ $e->note }}</em>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="sg-hint" style="margin:0">Belum ada aktivitas pintu hari ini.</p>
            @endif
        </section>
    </main>
</div>

{{-- keluar cepat: pilih jam keluar lalu simpan (tanpa proses gate out) --}}
<div class="sg-scan" id="outDlg" role="dialog" aria-modal="true" aria-labelledby="outTitle" hidden>
    <form class="sg-scan__panel" data-out-form novalidate>
        <div class="sg-scan__head">
            <h2 class="sg-scan__title" id="outTitle">Keluar pabrik<small data-out-ident></small></h2>
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-out-close aria-label="Tutup">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
        </div>
        <div class="sg-field">
            <label class="sg-label" for="outAt">Jam keluar <span class="sg-req">*</span></label>
            <input class="sg-input" type="datetime-local" id="outAt" required>
            <div class="sg-row dk-out__quick">
                <button type="button" class="sg-btn sg-btn--sm" data-out-now>Sekarang</button>
                <button type="button" class="sg-btn sg-btn--sm" data-out-minus="15">−15 mnt</button>
                <button type="button" class="sg-btn sg-btn--sm" data-out-minus="30">−30 mnt</button>
            </div>
        </div>
        <div class="sg-field">
            <label class="sg-label" for="outReason">Alasan <span class="sg-req">*</span></label>
            <select class="sg-input" id="outReason" required>
                <option value="">Pilih alasan…</option>
                @foreach (\App\Http\Controllers\Security\GateDoorController::QUICK_OUT_REASONS as $r)
                    <option>{{ $r }}</option>
                @endforeach
            </select>
        </div>
        <div class="sg-field">
            <label class="sg-label" for="outNote">Catatan keluar <span data-note-req hidden class="sg-req">*</span></label>
            <textarea class="sg-input sg-textarea" id="outNote" rows="2" maxlength="180" placeholder="Mis. sopir langsung keluar, seal belum dicatat"></textarea>
        </div>
        <p class="sg-hint" style="margin:0">Keluar cepat untuk truk yang sudah keluar tanpa dicatat di sistem, supaya pintu bisa dipakai truk lain. Jam keluar tidak boleh sebelum gate in, mulai muat, atau tutup container. Data seal, foto & tanda tangan tidak dicatat.</p>
        <div class="sg-alert sg-alert--danger" data-out-err role="alert" hidden>
            @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
            <div><strong>Belum bisa keluar</strong><ul class="dk-out__errs"></ul></div>
        </div>
        <div class="dk-out__foot">
            <button type="button" class="sg-btn sg-btn--lg" data-out-close>Batal</button>
            <button type="submit" class="sg-btn sg-btn--primary sg-btn--lg">Simpan keluar</button>
        </div>
    </form>
</div>

@include('menu.exim-export.security.partials.realtime')
<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let touched = false;
    const url = @json(route('security.gate.door.activate', '__CONT__'));
    // isi pintu kosong: pilih container lalu aktifkan
    document.querySelectorAll('[data-dock-fill]').forEach(form => form.addEventListener('submit', async e => {
        e.preventDefault();
        const sel = form.querySelector('select'), btn = form.querySelector('button'), err = form.querySelector('.dk-err');
        if (!sel.value) { sel.focus(); return; }
        const fd = new FormData();
        fd.append('mif', form.dataset.mif);
        fd.append('pintu', form.dataset.pintu);
        btn.disabled = true;
        btn.classList.add('is-loading');
        err.hidden = true;
        try {
            const res = await fetch(url.replace('__CONT__', sel.value), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd });
            let json = {};
            try { json = await res.json(); } catch (x) {}
            if (!res.ok) throw new Error(json.message || 'Gagal terhubung ke server. Coba lagi.');
            location.reload();
        } catch (x) {
            err.textContent = x.message;
            err.hidden = false;
            btn.disabled = false;
            btn.classList.remove('is-loading');
        }
    }));
    // selesai muat: ketuk sekali -> minta konfirmasi di tombol, ketuk lagi (dalam 4 detik) -> pintu dikosongkan
    const finishUrl = @json(route('security.gate.door.finish', '__CONT__'));
    document.querySelectorAll('[data-dock-finish]').forEach(btn => {
        const label = btn.innerHTML;
        let armed = null;
        btn.addEventListener('click', async () => {
            const err = btn.closest('.dk-dock').querySelector('.dk-err');
            if (!armed) {
                touched = true;
                btn.textContent = `Yakin pintu ${btn.dataset.pintu} kosong? Ketuk lagi`;
                btn.classList.add('is-armed');
                armed = setTimeout(() => { armed = null; btn.innerHTML = label; btn.classList.remove('is-armed'); touched = false; }, 4000);
                return;
            }
            clearTimeout(armed);
            btn.disabled = true;
            btn.classList.add('is-loading');
            err.hidden = true;
            try {
                const res = await fetch(finishUrl.replace('__CONT__', btn.dataset.cont), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
                let json = {};
                try { json = await res.json(); } catch (x) {}
                if (!res.ok) throw new Error(json.message || 'Gagal terhubung ke server. Coba lagi.');
                location.reload();
            } catch (x) {
                armed = null;
                err.textContent = x.message;
                err.hidden = false;
                btn.disabled = false;
                btn.classList.remove('is-loading', 'is-armed');
                btn.innerHTML = label;
                touched = false;
            }
        });
    });
    // keluar cepat: pilih jam keluar (default sekarang) lalu simpan; jam tidak urut -> pesan di dialog
    const outUrl = @json(route('security.gate.door.out', '__CONT__'));
    const outDlg = document.getElementById('outDlg');
    const outForm = outDlg.querySelector('[data-out-form]');
    const outAt = document.getElementById('outAt');
    const outErr = outDlg.querySelector('[data-out-err]');
    const outNote = document.getElementById('outNote');
    const outReason = document.getElementById('outReason');
    // alasan Lainnya: catatan wajib
    const syncNoteReq = () => { outDlg.querySelector('[data-note-req]').hidden = outReason.value !== 'Lainnya'; };
    outReason.addEventListener('change', syncNoteReq);
    const pad = n => String(n).padStart(2, '0');
    const local = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    let outBtn = null;
    function openOut(btn) {
        outBtn = btn;
        touched = true;
        outDlg.querySelector('[data-out-ident]').textContent = btn.dataset.ident;
        outAt.min = btn.dataset.since;
        outAt.max = local(new Date());
        outAt.value = local(new Date());
        outNote.value = '';
        outReason.value = '';
        syncNoteReq();
        outErr.hidden = true;
        outDlg.hidden = false;
        outAt.focus();
    }
    function closeOut() {
        outDlg.hidden = true;
        touched = false;
        if (outBtn) outBtn.focus();
    }
    document.querySelectorAll('[data-dock-out]').forEach(btn => btn.addEventListener('click', () => openOut(btn)));
    outDlg.querySelectorAll('[data-out-close]').forEach(b => b.addEventListener('click', closeOut));
    outDlg.addEventListener('click', e => { if (e.target === outDlg) closeOut(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !outDlg.hidden) closeOut(); });
    outDlg.querySelector('[data-out-now]').addEventListener('click', () => { outAt.value = local(new Date()); });
    outDlg.querySelectorAll('[data-out-minus]').forEach(b => b.addEventListener('click', () => {
        outAt.value = local(new Date(Date.now() - b.dataset.outMinus * 60000));
    }));
    outForm.addEventListener('submit', async e => {
        e.preventDefault();
        if (!outAt.value) { outAt.focus(); return; }
        if (!outReason.value) { outReason.focus(); return; }
        if (outReason.value === 'Lainnya' && !outNote.value.trim()) { outNote.focus(); return; }
        const btn = outForm.querySelector('[type=submit]');
        const fd = new FormData();
        fd.append('gate_out_at', outAt.value);
        fd.append('alasan', outReason.value);
        fd.append('catatan', outNote.value.trim());
        btn.disabled = true;
        btn.classList.add('is-loading');
        outErr.hidden = true;
        try {
            const res = await fetch(outUrl.replace('__CONT__', outBtn.dataset.cont), { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd });
            let json = {};
            try { json = await res.json(); } catch (x) {}
            if (!res.ok) {
                const list = json.errors ? [].concat(...Object.values(json.errors)) : [json.message || 'Gagal terhubung ke server. Coba lagi.'];
                const ul = outErr.querySelector('ul');
                ul.innerHTML = '';
                [...new Set(list)].forEach(m => { const li = document.createElement('li'); li.textContent = m; ul.appendChild(li); });
                outErr.hidden = false;
                return;
            }
            location.reload();
        } catch (x) {
            outErr.querySelector('ul').innerHTML = '<li>Gagal terhubung ke server. Coba lagi.</li>';
            outErr.hidden = false;
        } finally {
            btn.disabled = false;
            btn.classList.remove('is-loading');
        }
    });

    // papan diperbarui tiap menit selama terlihat, kecuali sedang memilih container / konfirmasi selesai muat
    document.addEventListener('change', () => { touched = true; });
    setInterval(() => { if (document.visibilityState === 'visible' && !touched) location.reload(); }, 60000);
    // realtime websocket: pintu / container berubah di perangkat lain -> papan langsung diperbarui
    window.ShipRT.on((ev, d) => {
        if (ev === 'cont' && !d.self && !touched) setTimeout(() => { if (!touched) location.reload(); }, 300 + Math.random() * 700);
    });
})();
</script>
</body>
</html>
