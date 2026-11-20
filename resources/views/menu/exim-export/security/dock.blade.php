{{-- Halaman Pintu (ikon di header Security Gate): pintu per MIF, container menuju / menunggu pintu, riwayat hari ini.
     Data: GateDoorController::board. Pintu kosong di MIF pos security bisa langsung diisi (aktifkan lewat security.gate.door.activate). --}}
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
                                <a class="dk-dock is-busy {{ $mins >= 240 ? 'is-long' : '' }}" href="{{ $link($c->contpk) }}">
                                    <span class="dk-dock__no">Pintu {{ $p }}</span>
                                    <strong class="sg-mono">{{ $c->ident }}</strong>
                                    <span class="dk-dock__meta">{{ $c->idship }} · {{ $c->buyer ?: '-' }}</span>
                                    <span class="dk-dock__time">@include('menu.exim-export.security.partials.icon', ['name' => 'clock']) Sejak {{ $c->at->format('H:i') }} · {{ $dur($c->at) }}</span>
                                </a>
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

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
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
    // papan diperbarui tiap menit selama terlihat, kecuali sedang memilih container
    let touched = false;
    document.addEventListener('change', () => { touched = true; });
    setInterval(() => { if (document.visibilityState === 'visible' && !touched) location.reload(); }, 60000);
})();
</script>
</body>
</html>
