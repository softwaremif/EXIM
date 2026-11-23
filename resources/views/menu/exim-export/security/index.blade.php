@php
    $hasSel = (bool) $selected;
    $listUrl = route('security.gate.index', array_filter(['filter' => $filter, 'q' => $q]));
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Gate · Morich Indo Fashion</title>
    <link rel="shortcut icon" href="{!! asset('public/css/images/morich.gif') !!}">
    {{-- aplikasi layar utama (tablet security): layar penuh tanpa bar browser, lihat GateAppController --}}
    <link rel="manifest" href="{{ route('security.gate.manifest') }}">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1c1f22" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Security Gate">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="{{ asset('public/img/security-gate/apple-touch-icon.png') }}">
    <script>
        // tema gelap untuk shift malam, disimpan per perangkat
        try { if (localStorage.getItem('sg-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (e) {}
        // daftar container disembunyikan (tablet / layar lebar), disimpan per perangkat
        try { if (localStorage.getItem('sg-list') === 'closed') document.documentElement.classList.add('sg-list-closed'); } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/css/security-gate.css') }}?v={{ filemtime(public_path('css/security-gate.css')) }}">
    {{-- layar penuh menetap antar halaman (mode kiosk tablet security) --}}
    <script src="{{ asset('public/js/security-fullscreen.js') }}?v={{ filemtime(public_path('js/security-fullscreen.js')) }}"></script>
</head>
<body>
<div class="sg-screen">
    <header class="sg-appbar">
        @if ($hasSel)
            <a class="sg-btn sg-btn--ghost sg-btn--icon sg-appbar__back" href="{{ $listUrl }}" aria-label="Kembali ke daftar">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</a>
        @endif
        <h1 class="sg-appbar__title">
            Container masuk
            <span class="sg-appbar__sub">{{ now()->isoFormat('dddd, D MMM YYYY') }}</span>
        </h1>
        <span class="sg-appbar__shift">{{ $login }}</span>
        {{-- muncul kalau browser siap menginstal aplikasi (HTTPS + manifest + service worker), lihat GateAppController --}}
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="btnInstall" aria-label="Instal aplikasi Security Gate" title="Instal aplikasi" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'download'])</button>
        <a class="sg-btn sg-btn--ghost sg-btn--icon" href="{{ route('security.gate.calendar') }}" aria-label="Kalender ETA container" title="Kalender ETA">@include('menu.exim-export.security.partials.icon', ['name' => 'calendar'])</a>
        @if ($dockBadge)
            {{-- pintu: angka = pintu terisi / jumlah pintu (pos MIF security) --}}
            <a class="sg-btn sg-btn--ghost sg-btn--icon sg-appbar__dock" href="{{ route('security.gate.dock') }}" aria-label="Pintu, {{ $dockBadge }} terisi" title="Pintu">
                @include('menu.exim-export.security.partials.icon', ['name' => 'dock'])<span class="sg-appbar__badge">{{ $dockBadge }}</span>
            </a>
        @endif
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-stf-open aria-label="Scan QR container stuffing" title="Scan stuffing">@include('menu.exim-export.security.partials.icon', ['name' => 'qr'])</button>
        {{-- suara dialog konfirmasi / alert, disimpan per perangkat (dialogSound di bawah) --}}
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="btnSound" aria-pressed="true" aria-label="Matikan suara dialog" title="Suara dialog: nyala">
            <span data-sound-icon="on">@include('menu.exim-export.security.partials.icon', ['name' => 'sound'])</span>
            <span data-sound-icon="off" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'mute'])</span>
        </button>
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="btnTheme" aria-label="Ganti tema terang/gelap">
            <span data-theme-icon="moon">@include('menu.exim-export.security.partials.icon', ['name' => 'moon'])</span>
            <span data-theme-icon="sun" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'sun'])</span>
        </button>
        {{-- disembunyikan JS kalau browser tidak mendukung layar penuh (mis. Safari iPhone) --}}
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="btnFullscreen" aria-label="Layar penuh" title="Layar penuh" aria-pressed="false" hidden>
            <span data-fs-icon="expand">@include('menu.exim-export.security.partials.icon', ['name' => 'expand'])</span>
            <span data-fs-icon="shrink" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'shrink'])</span>
        </button>
        <a class="sg-btn sg-btn--ghost sg-btn--icon" href="{{ route('security.gate.logout') }}" aria-label="Keluar">@include('menu.exim-export.security.partials.icon', ['name' => 'logout'])</a>
    </header>

    <div class="sg-split {{ $hasSel ? 'has-selection' : '' }}">
        {{-- Daftar container dari shipment plan --}}
        <div class="sg-split__list" id="sgList">
            <div class="sg-main">
                <div class="sg-alert sg-alert--danger" id="offlineAlert" role="status" hidden>
                    @include('menu.exim-export.security.partials.icon', ['name' => 'wifi-off'])
                    <div><strong>Offline</strong>Data belum bisa dikirim. Tunggu sinyal kembali.</div>
                </div>

                @unless ($ready)
                    <div class="sg-alert sg-alert--warn" role="status">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                        <div>
                            <strong>Gate in, edit & catat keluar belum bisa dipakai</strong>
                            Struktur database belum lengkap{{ $missing ? ': kolom ' . implode(', ', $missing) . ' belum ada di table cont' : ' (table ' . 'cont_gate_history' . ')' }}. Hubungi IT. Daftar container tetap bisa dilihat.
                        </div>
                    </div>
                @endunless

                {{-- pencarian + filter menempel di atas daftar saat discroll (.sg-listbar) --}}
                <span class="sg-listbar__sentinel" aria-hidden="true"></span>
                <div class="sg-listbar">
                <div class="stf-row">
                    <form method="get" action="{{ route('security.gate.index') }}" role="search">
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <label class="sg-search">
                            @include('menu.exim-export.security.partials.icon', ['name' => 'search'])
                            <input type="search" name="q" value="{{ $q }}" placeholder="Cari no. container / plat / shipment" aria-label="Cari container">
                        </label>
                    </form>
                    <button type="button" class="sg-btn sg-btn--primary" data-stf-open title="Scan QR container dari sistem stuffing">@include('menu.exim-export.security.partials.icon', ['name' => 'qr']) Scan</button>
                </div>

                <nav class="sg-chips" aria-label="Filter container">
                    @foreach ($filters as $key => $label)
                        <a class="sg-chip {{ $filter === $key ? 'is-active' : '' }}" href="{{ route('security.gate.index', array_filter(['filter' => $key, 'q' => $q])) }}">
                            {{ $label }} <span class="sg-chip__count">({{ $counts[$key] }})</span>
                        </a>
                    @endforeach
                </nav>
                </div>

                {{-- kartu 1 / 2 / 3 kolom mengikuti lebar panel daftar (security-gate.css .sg-cards) --}}
                <div class="sg-cards">
                @forelse ($containers as $c)
                    @include('menu.exim-export.security.partials.card', ['c' => $c])
                @empty
                    <div class="sg-empty">
                        <strong>Tidak ada container</strong>
                        {{ $q !== '' ? 'Tidak ada yang cocok dengan "' . $q . '".' : 'Belum ada container ' . Str::lower($filters[$filter] ?? '') . ' dari shipment plan.' }}
                    </div>
                @endforelse
                </div>
            </div>
        </div>

        {{-- Detail: form gate in / ringkasan kedatangan --}}
        <div class="sg-split__detail">
            {{-- handle di tepi panel: buka / tutup daftar container (hanya tablet & layar lebar; di HP daftar dan form sudah bergantian) --}}
            <div class="sg-pane-handle">
                <button type="button" id="btnList" aria-controls="sgList" aria-expanded="true" aria-label="Sembunyikan daftar container" title="Sembunyikan daftar">
                    <span data-pane-icon="close">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</span>
                    <span data-pane-icon="open">@include('menu.exim-export.security.partials.icon', ['name' => 'next'])</span>
                </button>
            </div>
            @if (!$hasSel)
                <div class="sg-main">
                    <div class="sg-empty">
                        <strong>Pilih container</strong>
                        Ketuk container di daftar untuk mencatat kedatangan.
                        <p class="sg-list-reopen"><button type="button" class="sg-btn" data-list-open>@include('menu.exim-export.security.partials.icon', ['name' => 'sidebar']) Tampilkan daftar container</button></p>
                    </div>
                </div>
            @elseif ($selected->arrived_at && !$editing)
                @include('menu.exim-export.security.partials.done')
            @elseif (!$ready)
                <div class="sg-main">
                    <div class="sg-alert sg-alert--danger" role="alert">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                        <div><strong>Gate in belum bisa dipakai</strong>Kolom data gate di table container belum lengkap. Hubungi IT.</div>
                    </div>
                </div>
            @else
                @include('menu.exim-export.security.partials.form')
            @endif
        </div>
    </div>
</div>

@if ($hasSel && $ready)
    {{-- Scanner ID card karyawan untuk petugas seal. Kamera live hanya jalan di HTTPS; di HTTP pakai foto QR --}}
    <div class="sg-scan" id="scanner" role="dialog" aria-modal="true" aria-labelledby="scanTitle" hidden
        data-url="{{ route('security.gate.employee') }}" data-search-url="{{ route('security.gate.employee.search') }}" data-lib="{{ asset('public/js/vendor/jsQR.min.js') }}">
        <div class="sg-scan__panel">
            <div class="sg-scan__head">
                <h2 class="sg-scan__title" id="scanTitle">Scan ID card<small data-scan-title></small></h2>
                <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-scan-close aria-label="Tutup scanner">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
            </div>
            <div class="sg-scan__view" data-scan-live hidden>
                <video playsinline muted></video>
                <span class="sg-scan__frame"></span>
            </div>
            <label class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" data-scan-photo hidden>
                @include('menu.exim-export.security.partials.icon', ['name' => 'camera']) Foto QR code di ID card
                <input class="sg-scan__file" type="file" accept="image/*" capture="environment">
            </label>
            <p class="sg-scan__status" data-scan-status role="status"></p>
            {{-- tanpa ID card / QR rusak: cari nama atau NRK; NRK + Enter (juga scanner barcode) langsung dipilih --}}
            <form class="sg-scan__manual" data-scan-manual novalidate>
                <label class="sg-label" for="scanNrk">Cari nama / NRK</label>
                <div class="sg-scan__row">
                    <input class="sg-input" id="scanNrk" type="search" autocomplete="off" placeholder="Nama atau NRK, mis. 003940">
                    <button type="submit" class="sg-btn">Cari</button>
                </div>
                <p class="sg-scan__note" data-scan-warn hidden></p>
                <ul class="sg-scan__results" data-scan-results hidden></ul>
            </form>
        </div>
    </div>
@endif

{{-- dialog konfirmasi / pemberitahuan pengganti confirm() & alert() browser; dipakai lewat sgConfirm() / sgAlert() --}}
{{-- pilih sumber foto / video: ambil langsung (kamera) atau upload file dari galeri; lihat mediaPick di script --}}
<div class="sg-pick" id="mediaPick" role="dialog" aria-modal="true" aria-labelledby="mediaPickTitle" hidden>
    <div class="sg-pick__panel">
        <p class="sg-pick__title" id="mediaPickTitle">Tambah foto</p>
        <button type="button" class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" data-pick="camera">
            <span data-pick-icon="camera">@include('menu.exim-export.security.partials.icon', ['name' => 'camera'])</span>
            <span data-pick-icon="video" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'video'])</span>
            <span data-pick-text="camera">Ambil foto</span>
        </button>
        <button type="button" class="sg-btn sg-btn--lg sg-btn--block" data-pick="file">
            @include('menu.exim-export.security.partials.icon', ['name' => 'upload'])
            <span data-pick-text="file">Pilih dari galeri / file</span>
        </button>
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--block" data-pick="cancel">Batal</button>
    </div>
</div>

<div class="sg-dialog" id="sgDialog" data-voice="{{ asset('public/media/voice/universfield-warning-notification-199277.mp3') }}"
    data-voice-missing="{{ asset('public/media/voice/36505577-ui-8-warning-sound-effect-336254.mp3') }}" role="alertdialog" aria-modal="true" aria-labelledby="sgDialogTitle" aria-describedby="sgDialogMsg" hidden>
    <div class="sg-dialog__panel">
        <span class="sg-dialog__icon" data-dialog-icon></span>
        <h2 class="sg-dialog__title" id="sgDialogTitle"></h2>
        <p class="sg-dialog__msg" id="sgDialogMsg"></p>
        <div class="sg-dialog__actions">
            <button type="button" class="sg-btn sg-btn--lg" data-dialog-result="0"></button>
            <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" data-dialog-result="1"></button>
        </div>
    </div>
</div>
<template data-dialog-icon="question">@include('menu.exim-export.security.partials.icon', ['name' => 'info'])</template>
<template data-dialog-icon="warning">@include('menu.exim-export.security.partials.icon', ['name' => 'warn'])</template>
<template data-dialog-icon="danger">@include('menu.exim-export.security.partials.icon', ['name' => 'trash'])</template>
<template data-dialog-icon="error">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</template>
<template data-dialog-icon="success">@include('menu.exim-export.security.partials.icon', ['name' => 'check'])</template>

@if ($hasSel)
    <div class="sg-lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Lihat foto" hidden>
        <div class="sg-lightbox__bar">
            <span class="sg-lightbox__cap" data-lb-caption></span>
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-lb-close aria-label="Tutup">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
        </div>
        <div class="sg-lightbox__stage"><img alt=""><video controls playsinline preload="metadata" hidden></video></div>
        <button type="button" class="sg-lightbox__nav sg-lightbox__nav--prev" data-lb-prev aria-label="Foto sebelumnya">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</button>
        <button type="button" class="sg-lightbox__nav sg-lightbox__nav--next" data-lb-next aria-label="Foto berikutnya">@include('menu.exim-export.security.partials.icon', ['name' => 'next'])</button>
    </div>
@endif

<script>
(function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    // ---------- tema ----------
    const themeBtn = document.getElementById('btnTheme');
    const syncThemeIcon = () => {
        const dark = document.documentElement.dataset.theme === 'dark';
        themeBtn.querySelector('[data-theme-icon="moon"]').hidden = dark;
        themeBtn.querySelector('[data-theme-icon="sun"]').hidden = !dark;
    };
    themeBtn.addEventListener('click', () => {
        const dark = document.documentElement.dataset.theme !== 'dark';
        if (dark) document.documentElement.dataset.theme = 'dark'; else delete document.documentElement.dataset.theme;
        try { localStorage.setItem('sg-theme', dark ? 'dark' : 'light'); } catch (e) {}
        syncThemeIcon();
    });
    syncThemeIcon();

    // ---------- aplikasi terinstal: service worker + tombol "Instal aplikasi" -- HANYA di HTTPS sungguhan.
    // Di http:// (walau Chrome diberi flag "insecure origin as secure") aplikasi terinstal tetap menampilkan
    // bar Chrome & membuat pilihan "Chrome / Security Gate"; di HTTP cukup pintasan "Tambahkan ke layar utama".
    const realHttps = location.protocol === 'https:';
    if ('serviceWorker' in navigator && realHttps) {
        navigator.serviceWorker.register(@json(route('security.gate.sw')), { scope: @json(route('security.gate.index')) }).catch(() => {});
    } else if ('serviceWorker' in navigator && navigator.serviceWorker.getRegistrations) {
        // service worker lama (terdaftar saat flag Chrome aktif) dilepas
        navigator.serviceWorker.getRegistrations().then(rs => rs.forEach(r => r.unregister())).catch(() => {});
    }
    const installBtn = document.getElementById('btnInstall');
    let installPrompt = null;
    window.addEventListener('beforeinstallprompt', e => {
        e.preventDefault();
        if (!realHttps) return;
        installPrompt = e;
        installBtn.hidden = false;
    });
    installBtn.addEventListener('click', async () => {
        if (!installPrompt) return;
        installPrompt.prompt();
        await installPrompt.userChoice;
        installPrompt = null;
        installBtn.hidden = true;
    });
    window.addEventListener('appinstalled', () => { installBtn.hidden = true; });

    // ---------- buka / tutup daftar container (kelas sg-list-closed di <html>, lihat security-gate.css)
    const listBtn = document.getElementById('btnList');
    const syncListBtn = () => {
        const closed = document.documentElement.classList.contains('sg-list-closed');
        listBtn.setAttribute('aria-expanded', String(!closed));
        listBtn.setAttribute('aria-label', closed ? 'Tampilkan daftar container' : 'Sembunyikan daftar container');
        listBtn.title = closed ? 'Tampilkan daftar' : 'Sembunyikan daftar';
    };
    const setList = (open) => {
        document.documentElement.classList.toggle('sg-list-closed', !open);
        try { localStorage.setItem('sg-list', open ? 'open' : 'closed'); } catch (e) {}
        syncListBtn();
    };
    listBtn.addEventListener('click', () => setList(document.documentElement.classList.contains('sg-list-closed')));
    document.querySelectorAll('[data-list-open]').forEach(b => b.addEventListener('click', () => setList(true)));
    syncListBtn();

    // ---------- bar pencarian + filter: garis bawah hanya saat menempel (sentinel tepat di atas bar sudah lewat)
    const listbar = document.querySelector('.sg-listbar');
    const sentinel = document.querySelector('.sg-listbar__sentinel');
    if (listbar && sentinel && 'IntersectionObserver' in window) {
        const appbarH = document.querySelector('.sg-appbar').offsetHeight;
        new IntersectionObserver(([e]) => listbar.classList.toggle('is-stuck', !e.isIntersecting && e.boundingClientRect.top < appbarH + 1),
            { rootMargin: `-${appbarH}px 0px 0px 0px` }).observe(sentinel);
    }

    // ---------- chip filter: posisi geser deretan chip dipertahankan setelah pindah filter
    // (chip berupa link -> halaman dimuat ulang dan deretan chip balik ke awal)
    const chips = document.querySelector('.sg-chips');
    if (chips) {
        try {
            const saved = sessionStorage.getItem('sg-chips-scroll');
            if (saved !== null) chips.scrollLeft = Number(saved) || 0;
            sessionStorage.removeItem('sg-chips-scroll');
        } catch (e) {}
        chips.addEventListener('click', (e) => {
            if (!e.target.closest('.sg-chip')) return;
            try { sessionStorage.setItem('sg-chips-scroll', String(chips.scrollLeft)); } catch (e) {}
        });
    }

    // ---------- layar penuh (tombol di header); webkit untuk Safari iPad ----------
    const fsBtn = document.getElementById('btnFullscreen');
    const fsRoot = document.documentElement;
    const fsElement = () => document.fullscreenElement || document.webkitFullscreenElement;
    const fsRequest = fsRoot.requestFullscreen || fsRoot.webkitRequestFullscreen;
    const fsExit = document.exitFullscreen || document.webkitExitFullscreen;
    // dibuka dari ikon layar utama (manifest display fullscreen/standalone) sudah penuh: tombol tidak perlu
    const installed = window.matchMedia('(display-mode: fullscreen), (display-mode: standalone)').matches || navigator.standalone === true;
    if (!installed && fsRequest && (document.fullscreenEnabled || document.webkitFullscreenEnabled)) {
        fsBtn.hidden = false;
        const syncFs = () => {
            const on = !!fsElement();
            fsBtn.querySelector('[data-fs-icon="expand"]').hidden = on;
            fsBtn.querySelector('[data-fs-icon="shrink"]').hidden = !on;
            fsBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
            fsBtn.setAttribute('aria-label', on ? 'Keluar layar penuh' : 'Layar penuh');
            fsBtn.title = on ? 'Keluar layar penuh' : 'Layar penuh';
        };
        // layar penuh menetap: setelah dinyalakan, pindah halaman cukup sentuh layar sekali (security-fullscreen.js);
        // mati hanya lewat tombol ini
        fsBtn.addEventListener('click', async () => {
            try {
                if (fsElement()) window.SgFullscreen.off(); else await window.SgFullscreen.on();
            } catch (e) {}
            syncFs();
        });
        document.addEventListener('fullscreenchange', syncFs);
        document.addEventListener('webkitfullscreenchange', syncFs);
        syncFs();
    }

    // ---------- offline ----------
    const offline = document.getElementById('offlineAlert');
    const syncOnline = () => { offline.hidden = navigator.onLine; };
    window.addEventListener('online', syncOnline);
    window.addEventListener('offline', syncOnline);
    syncOnline();

    // ---------- efek suara dialog; on/off lewat tombol di header, disimpan per perangkat (localStorage 'dialog-sound' = 'off').
    // Dialog konfirmasi (ada tombol Batal): file suara di #sgDialog[data-voice]; data wajib terlewat saat simpan:
    // #sgDialog[data-voice-missing] (missingSound, dipanggil errorBox).
    // Pemberitahuan (error / sukses): nada pendek Web Audio, [frekuensi Hz, mulai detik]: sukses = tiga nada naik,
    // error = dua nada turun. Nada juga jadi cadangan kalau file suara gagal diputar.
    const soundOn = () => { try { return localStorage.getItem('dialog-sound') !== 'off'; } catch (e) { return true; } };
    const loadVoice = src => {
        if (!src || !window.Audio) return null;
        const a = new Audio(src);
        a.preload = 'auto';
        return a;
    };
    const voice = loadVoice(document.getElementById('sgDialog').dataset.voice);
    const missingVoice = loadVoice(document.getElementById('sgDialog').dataset.voiceMissing);
    // putar dari awal; kalau gagal (file belum termuat / diblokir browser) pakai cadangan
    function playVoice(a, fallback) {
        if (!a) return fallback();
        try {
            a.pause();
            a.currentTime = 0;
            const p = a.play();
            if (p && p.catch) p.catch(fallback);
        } catch (e) {
            fallback();
        }
    }
    const dialogSound = (() => {
        const NOTES = {
            question: [[659, 0], [880, .11]],
            success:  [[523, 0], [659, .09], [784, .18]],
            warning:  [[440, 0], [440, .16]],
            danger:   [[523, 0], [392, .14]],
            error:    [[440, 0], [311, .16]],
        };
        let ctx = null;
        function tone(type) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return;
            try {
                ctx = ctx || new AC();
                if (ctx.state === 'suspended') ctx.resume();
                const wave = type === 'question' || type === 'success' ? 'sine' : 'triangle';
                (NOTES[type] || NOTES.question).forEach(([freq, at]) => {
                    const t = ctx.currentTime + at;
                    const osc = ctx.createOscillator(), gain = ctx.createGain();
                    osc.type = wave;
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0.0001, t);
                    gain.gain.exponentialRampToValueAtTime(0.18, t + 0.015);
                    gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.22);
                    osc.connect(gain).connect(ctx.destination);
                    osc.start(t);
                    osc.stop(t + 0.25);
                });
            } catch (e) {}
        }
        // confirm: dialog konfirmasi (file suara), selain itu nada pemberitahuan
        return (type, confirm) => {
            if (!soundOn()) return;
            if (!confirm) return tone(type);
            playVoice(voice, () => tone(type));
        };
    })();
    // data wajib belum diisi / terlewat saat simpan
    const missingSound = () => {
        if (!soundOn()) return;
        if (voice) voice.pause();
        playVoice(missingVoice, () => dialogSound('warning'));
    };
    const soundBtn = document.getElementById('btnSound');
    const syncSoundBtn = () => {
        const on = soundOn();
        soundBtn.querySelector('[data-sound-icon="on"]').hidden = !on;
        soundBtn.querySelector('[data-sound-icon="off"]').hidden = on;
        soundBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
        soundBtn.setAttribute('aria-label', on ? 'Matikan suara dialog' : 'Nyalakan suara dialog');
        soundBtn.title = on ? 'Suara dialog: nyala' : 'Suara dialog: mati';
    };
    soundBtn.addEventListener('click', () => {
        const on = !soundOn();
        try { localStorage.setItem('dialog-sound', on ? 'on' : 'off'); } catch (e) {}
        syncSoundBtn();
        if (on) dialogSound('question', true);   // contoh suara konfirmasi saat dinyalakan
    });
    syncSoundBtn();

    // ---------- dialog konfirmasi / pemberitahuan (pengganti confirm() & alert() browser) ----------
    // sgConfirm(pesan, { title, type, okText, cancelText }) -> Promise<boolean>; sgAlert(pesan, { title, type }) -> Promise
    // type: question | warning | danger | error | success. Esc / ketuk di luar = batal.
    const sgDialog = (() => {
        const dlg = document.getElementById('sgDialog');
        const icon = dlg.querySelector('[data-dialog-icon]');
        const title = dlg.querySelector('.sg-dialog__title');
        const msg = dlg.querySelector('.sg-dialog__msg');
        const cancelBtn = dlg.querySelector('[data-dialog-result="0"]');
        const okBtn = dlg.querySelector('[data-dialog-result="1"]');
        const titles = { question: 'Konfirmasi', warning: 'Perhatian', danger: 'Konfirmasi', error: 'Gagal', success: 'Berhasil' };
        let done = null, opener = null;
        const queue = [];

        function close(result) {
            if (!done) return;
            const resolve = done;
            done = null;
            dlg.classList.remove('is-open');
            setTimeout(() => {
                dlg.hidden = true;
                if (!document.querySelector('.sg-scan:not([hidden]), .sg-lightbox:not([hidden]), .ck-modal:not([hidden]), .ck-sheet:not([hidden])')) {
                    document.body.classList.remove('sg-noscroll');
                }
                if (opener && document.contains(opener)) opener.focus();
                resolve(result);
                if (queue.length) show(queue.shift());
            }, 160);
        }
        function show(job) {
            if (done) { queue.push(job); return; }
            const type = titles[job.type] ? job.type : 'question';
            dlg.dataset.type = type;
            icon.innerHTML = document.querySelector(`template[data-dialog-icon="${type}"]`).innerHTML;
            title.textContent = job.title || titles[type];
            // html: pesan sudah di-escape pemanggil (mis. ceklis carton dengan <b>)
            if (job.html) msg.innerHTML = job.message || ''; else msg.textContent = job.message || '';
            msg.hidden = !job.message;
            okBtn.textContent = job.okText;
            okBtn.className = 'sg-btn sg-btn--lg ' + (type === 'danger' || type === 'error' ? 'sg-btn--danger-solid' : 'sg-btn--primary');
            cancelBtn.textContent = job.cancelText || '';
            cancelBtn.hidden = !job.cancelText;
            opener = document.activeElement;
            done = job.resolve;
            dlg.hidden = false;
            document.body.classList.add('sg-noscroll');
            requestAnimationFrame(() => dlg.classList.add('is-open'));
            okBtn.focus();
            dialogSound(type, !!job.cancelText);
        }
        dlg.addEventListener('click', e => {
            const btn = e.target.closest('[data-dialog-result]');
            if (btn) close(btn.dataset.dialogResult === '1');
            else if (e.target === dlg) close(false);
        });
        document.addEventListener('keydown', e => {
            if (dlg.hidden) return;
            if (e.key === 'Escape') { e.stopImmediatePropagation(); close(false); }
            // fokus tetap di dalam dialog
            if (e.key === 'Tab') {
                const btns = [cancelBtn, okBtn].filter(b => !b.hidden);
                const i = btns.indexOf(document.activeElement);
                e.preventDefault();
                btns[(i + (e.shiftKey ? -1 : 1) + btns.length) % btns.length].focus();
            }
        }, true);
        return opts => new Promise(resolve => show(Object.assign({ okText: 'OK', cancelText: null }, opts, { resolve })));
    })();
    const sgConfirm = (message, opts) => sgDialog(Object.assign({ type: 'question', okText: 'Ya', cancelText: 'Batal' }, opts, { message }));
    const sgAlert = (message, opts) => sgDialog(Object.assign({ type: 'error' }, opts, { message }));
    // dipakai juga script partial (ceklis carton)
    window.sgConfirm = sgConfirm;
    window.sgAlert = sgAlert;

    // ---------- foto / video: pilih ambil langsung (kamera) atau upload file ----------
    // Semua input file foto / video memakai capture="environment" (langsung kamera). Di HP / tablet, ketukan pada input
    // ditahan dulu untuk menampilkan pilihan; setelah dipilih, input dibuka lagi dengan / tanpa capture.
    // Proses setelah file dipilih (kompres, cek durasi video, upload) tetap lewat event change masing-masing input.
    // Di komputer (tanpa layar sentuh) dialog file langsung terbuka seperti biasa.
    const pick = document.getElementById('mediaPick');
    if (pick && window.matchMedia('(pointer: coarse)').matches) {
        let pickInput = null, pickOpener = null;
        const closePick = () => {
            pick.hidden = true;
            document.body.classList.remove('sg-noscroll');
            if (pickOpener && document.contains(pickOpener)) pickOpener.focus();
        };
        document.addEventListener('click', e => {
            const input = e.target.closest('input[type=file]');
            if (!input || input.disabled || input.dataset.pickPass === '1') return;
            // simpan nilai capture asli (sekali) supaya bisa dipasang / dilepas
            if (input.dataset.capture === undefined) {
                if (!input.hasAttribute('capture')) return;   // input tanpa kamera: biarkan
                input.dataset.capture = input.getAttribute('capture') || 'environment';
            }
            e.preventDefault();
            pickInput = input;
            pickOpener = input.closest('label') || input;
            const video = (input.getAttribute('accept') || '').startsWith('video');
            pick.querySelector('#mediaPickTitle').textContent = input.getAttribute('aria-label') || (video ? 'Tambah video' : 'Tambah foto');
            pick.querySelector('[data-pick-icon="camera"]').hidden = video;
            pick.querySelector('[data-pick-icon="video"]').hidden = !video;
            pick.querySelector('[data-pick-text="camera"]').textContent = video ? 'Rekam video' : 'Ambil foto';
            pick.querySelector('[data-pick-text="file"]').textContent = video ? 'Pilih video dari galeri / file' : 'Pilih foto dari galeri / file';
            pick.hidden = false;
            document.body.classList.add('sg-noscroll');
            pick.querySelector('[data-pick="camera"]').focus();
        }, true);
        pick.addEventListener('click', e => {
            const btn = e.target.closest('[data-pick]');
            if (!btn && e.target !== pick) return;
            const choice = btn ? btn.dataset.pick : 'cancel';
            const input = pickInput;
            closePick();
            pickInput = null;
            if (!input || choice === 'cancel') return;
            if (choice === 'camera') input.setAttribute('capture', input.dataset.capture);
            else input.removeAttribute('capture');
            // dibuka dari ketukan tombol pilihan (masih dianggap aksi pengguna oleh browser)
            input.dataset.pickPass = '1';
            input.click();
            delete input.dataset.pickPass;
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !pick.hidden) { pickInput = null; closePick(); } });
    }

    // ---------- lihat foto besar (a[data-lightbox]); geser antar foto dalam satu [data-gallery] ----------
    // dibuka di halaman ini supaya aplikasi layar penuh tidak pindah ke tab baru; link tetap jadi cadangan.
    // Video (a[data-video]) diputar di sini juga.
    const lb = document.getElementById('lightbox');
    if (lb) {
        const lbImg = lb.querySelector('img');
        const lbVideo = lb.querySelector('video');
        const lbCap = lb.querySelector('[data-lb-caption]');
        const lbPrev = lb.querySelector('[data-lb-prev]');
        const lbNext = lb.querySelector('[data-lb-next]');
        let items = [], at = 0, lbOpener = null;
        const show = i => {
            at = (i + items.length) % items.length;
            const a = items[at];
            lbImg.hidden = false;
            lbVideo.hidden = true;
            lbImg.src = a.href;
            lbImg.alt = (a.querySelector('img') || {}).alt || '';
            lbCap.textContent = (lbImg.alt ? lbImg.alt + ' · ' : '') + `${at + 1}/${items.length}`;
            lbPrev.hidden = lbNext.hidden = items.length < 2;
        };
        const close = () => {
            lb.hidden = true;
            lbImg.removeAttribute('src');
            lbVideo.pause();
            lbVideo.removeAttribute('src');
            lbVideo.load();
            document.body.classList.remove('sg-noscroll');
            if (lbOpener && document.contains(lbOpener)) lbOpener.focus();
        };
        document.addEventListener('click', e => {
            const v = e.target.closest('a[data-video]');
            if (v && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                items = [];
                lbOpener = v;
                lbImg.hidden = true;
                lbImg.removeAttribute('src');
                lbVideo.hidden = false;
                lbVideo.src = v.href;
                lbCap.textContent = v.dataset.video || 'Video';
                lbPrev.hidden = lbNext.hidden = true;
                lb.hidden = false;
                document.body.classList.add('sg-noscroll');
                lb.querySelector('[data-lb-close]').focus();
                lbVideo.play().catch(() => {});
                return;
            }
            const a = e.target.closest('a[data-lightbox]');
            if (!a || e.ctrlKey || e.metaKey) return;
            e.preventDefault();
            const group = a.closest('[data-gallery]') || document;
            items = Array.from(group.querySelectorAll('a[data-lightbox]'));
            lbOpener = a;
            show(items.indexOf(a));
            lb.hidden = false;
            document.body.classList.add('sg-noscroll');
            lb.querySelector('[data-lb-close]').focus();
        });
        lbPrev.addEventListener('click', () => show(at - 1));
        lbNext.addEventListener('click', () => show(at + 1));
        lb.querySelector('[data-lb-close]').addEventListener('click', close);
        lb.addEventListener('click', e => { if (e.target === lb || e.target.classList.contains('sg-lightbox__stage')) close(); });
        document.addEventListener('keydown', e => {
            if (lb.hidden) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowLeft' && items.length > 1) show(at - 1);
            else if (e.key === 'ArrowRight' && items.length > 1) show(at + 1);
        });
        // geser kiri / kanan di layar sentuh
        let touchX = null;
        lb.addEventListener('touchstart', e => { touchX = e.touches.length === 1 ? e.touches[0].clientX : null; }, { passive: true });
        lb.addEventListener('touchend', e => {
            if (touchX === null || items.length < 2) return;
            const dx = e.changedTouches[0].clientX - touchX;
            if (Math.abs(dx) > 50) show(at + (dx < 0 ? 1 : -1));
            touchX = null;
        });
    }

    // ---------- request helper ----------
    async function post(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body,
        });
        let json = {};
        try { json = await res.json(); } catch (e) {}
        if (!res.ok) {
            const err = new Error(json.message || 'Gagal terhubung ke server. Coba lagi.');
            err.errors = json.errors;
            throw err;
        }
        return json;
    }
    function setLoading(btn, on) {
        btn.classList.toggle('is-loading', on);
        btn.disabled = on;
    }
    function upperOnInput(input) {
        const pos = input.selectionStart;
        input.value = input.value.toUpperCase();
        input.setSelectionRange(pos, pos);
    }

    // ---------- lokasi stuffing (kartu di tab Muat): aktifkan pintu, pindah MIF, kosongkan pintu ----------
    const doorCard = document.querySelector('[data-door]');
    if (doorCard) {
        const ident = doorCard.dataset.ident;
        doorCard.addEventListener('click', async e => {
            const act = e.target.closest('[data-door-activate]');
            const move = e.target.closest('[data-door-move]');
            const cancel = e.target.closest('[data-door-cancel]');
            const btn = act || move || cancel;
            if (!btn || btn.disabled) return;
            const fd = new FormData();
            let url, question, title;
            if (act) {
                url = doorCard.dataset.activateUrl;
                fd.append('mif', act.dataset.mif);
                fd.append('pintu', act.dataset.pintu);
                title = 'Aktifkan pintu?';
                question = `${ident} masuk ke MIF ${act.dataset.mif} pintu ${act.dataset.pintu} mulai sekarang.`;
            } else if (move) {
                url = doorCard.dataset.moveUrl;
                const note = doorCard.querySelector('[data-door-note]').value.trim();
                fd.append('tujuan_mif', move.dataset.tujuan);
                fd.append('catatan', note);
                title = `Pindah ke MIF ${move.dataset.tujuan}?`;
                question = `Jam pindah ${ident} dicatat sekarang.` + (note ? ` Catatan: ${note}` : '');
            } else {
                url = doorCard.dataset.cancelUrl;
                title = 'Kosongkan pintu?';
                question = `Pintu dikosongkan, ${ident} dianggap belum masuk pintu (salah pilih).`;
            }
            if (!await sgConfirm(question, { title })) return;
            setLoading(btn, true);
            try {
                await post(url, fd);
                location.reload();
            } catch (err) {
                setLoading(btn, false);
                sgAlert(err.message);
            }
        });
    }

    // tab form gate in (diisi di bagian tab di bawah); dipakai errorBox supaya error di tab lain terlihat
    const gateTabs = { reveal() {}, sync() {} };

    // ---------- error per field ----------
    function errorBox(formEl, alertBox, alertList) {
        function clear() {
            alertBox.hidden = true;
            alertList.innerHTML = '';
            formEl.querySelectorAll('.sg-field.is-error').forEach(f => {
                f.classList.remove('is-error');
                const hint = f.querySelector('.sg-hint');
                if (hint) {
                    if (hint.dataset.orig !== undefined) hint.textContent = hint.dataset.orig; else hint.hidden = true;
                }
            });
            gateTabs.sync();
        }
        // missing: error karena data wajib belum diisi (validasi), bukan gangguan koneksi / server -> bunyi peringatan
        // ('general' saja = gangguan, mis. HRD tidak bisa diakses / tunggu upload: tanpa bunyi)
        function show(errors, missing = true) {
            if (missing && Object.keys(errors || {}).some(k => k !== 'general')) missingSound();
            const general = [];
            Object.entries(errors || {}).forEach(([name, msgs]) => {
                const field = formEl.querySelector(`[data-field="${name}"]`);
                if (!field) { general.push(...msgs); return; }
                field.classList.add('is-error');
                const hint = field.querySelector('.sg-hint');
                if (!hint.hidden && hint.dataset.orig === undefined) hint.dataset.orig = hint.textContent;
                hint.textContent = '⚠ ' + msgs[0];
                hint.hidden = false;
            });
            gateTabs.sync();
            if (general.length) {
                alertList.innerHTML = general.map(m => `<li>${m.replace(/</g, '&lt;')}</li>`).join('');
                alertBox.hidden = false;
                alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                const firstField = formEl.querySelector('.sg-field.is-error');
                if (firstField) gateTabs.reveal(firstField);
                const first = formEl.querySelector('.sg-field.is-error input');
                if (first) first.focus();
            }
        }
        return { clear, show };
    }

    // ---------- tombol "Sekarang" di isian jam (datetime-local, waktu perangkat) ----------
    document.addEventListener('click', e => {
        const btn = e.target.closest('[data-now]');
        if (!btn) return;
        const input = btn.parentNode.querySelector('input');
        const d = new Date(Date.now() - new Date().getTimezoneOffset() * 60000);
        input.value = d.toISOString().slice(0, 16);
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });

    // ---------- jumlah langkah pemasangan seal yang sudah dicentang (partial seal-check) ----------
    document.querySelectorAll('[data-seal-check]').forEach(field => {
        const count = field.querySelector('[data-seal-count]');
        const boxes = Array.from(field.querySelectorAll('input[type=checkbox]'));
        const sync = () => {
            const n = boxes.filter(b => b.checked).length;
            count.textContent = `${n}/${boxes.length}`;
            count.classList.toggle('is-done', n === boxes.length);
        };
        field.addEventListener('change', sync);
        sync();
    });

    // ---------- pad tanda tangan (canvas, jari / stylus) ----------
    // gambar disimpan berlatar putih supaya tetap terbaca di tema gelap.
    // Tanda tangan yang sudah tersimpan di server (data-saved) dikunci; Ulangi = tanda tangan baru.
    function initSignPad(box) {
        const canvas = box.querySelector('canvas');
        const ctx = canvas.getContext('2d');
        const field = box.closest('.sg-field');
        const clearBtn = field.querySelector('[data-sign-clear]');
        const status = field.querySelector('[data-sign-status]');
        let drawing = false, strokes = 0, last = null, saved = false;

        function setSaved(on) {
            saved = on;
            box.classList.toggle('is-saved', on);
            status.hidden = !on;
        }
        function reset() {
            ctx.fillStyle = '#fff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            strokes = 0;
            setSaved(false);
            box.classList.remove('is-signed');
            clearBtn.hidden = true;
        }
        // posisi layar -> koordinat canvas (canvas 900x300, tampil mengikuti lebar layar)
        function point(e) {
            const r = canvas.getBoundingClientRect();
            return { x: (e.clientX - r.left) * canvas.width / r.width, y: (e.clientY - r.top) * canvas.height / r.height };
        }
        function lineTo(p) {
            ctx.beginPath();
            ctx.moveTo(last.x, last.y);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
            last = p;
        }
        canvas.addEventListener('pointerdown', e => {
            e.preventDefault();
            if (saved) return;
            if (canvas.setPointerCapture) canvas.setPointerCapture(e.pointerId);
            drawing = true;
            last = point(e);
            ctx.strokeStyle = ctx.fillStyle = '#111';
            ctx.lineWidth = 5;
            ctx.lineCap = ctx.lineJoin = 'round';
            ctx.beginPath();
            ctx.arc(last.x, last.y, 2.5, 0, Math.PI * 2);
            ctx.fill();
        });
        canvas.addEventListener('pointermove', e => {
            if (!drawing) return;
            const events = e.getCoalescedEvents ? e.getCoalescedEvents() : [];
            (events.length ? events : [e]).forEach(ev => lineTo(point(ev)));
        });
        const end = () => {
            if (!drawing) return;
            drawing = false;
            strokes++;
            box.classList.add('is-signed');
            clearBtn.hidden = false;
            field.classList.remove('is-error');
            const hint = field.querySelector('.sg-hint');
            if (hint) hint.hidden = true;
            box.dispatchEvent(new Event('input', { bubbles: true }));
        };
        ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(t => canvas.addEventListener(t, end));
        clearBtn.addEventListener('click', () => {
            reset();
            box.dispatchEvent(new Event('input', { bubbles: true }));
        });
        reset();
        if (box.dataset.saved) {
            const img = new Image();
            img.onload = () => ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            img.src = box.dataset.saved;
            setSaved(true);
            box.classList.add('is-signed');
            clearBtn.hidden = false;
        }

        return {
            key: box.dataset.sign,
            label: box.dataset.label,
            empty: () => strokes === 0 && !saved,
            // hanya tanda tangan baru yang dikirim; yang tersimpan tetap dipakai server
            changed: () => strokes > 0,
            data: () => canvas.toDataURL('image/png'),
            markSaved: () => { if (strokes > 0) { strokes = 0; setSaved(true); } },
        };
    }

    // ---------- kompres foto di perangkat (maks 1280px, JPEG 75%) ----------
    function compressImage(file) {
        return new Promise(resolve => {
            const img = new Image();
            const url = URL.createObjectURL(file);
            img.onload = () => {
                const scale = Math.min(1, 1280 / Math.max(img.width, img.height));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(img.width * scale);
                canvas.height = Math.round(img.height * scale);
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);
                canvas.toBlob(b => resolve(b && b.size < file.size ? b : file), 'image/jpeg', 0.75);
            };
            img.onerror = () => { URL.revokeObjectURL(url); resolve(file); };
            img.src = url;
        });
    }

    // ---------- kompres video di perangkat ----------
    // Video diputar ulang tanpa suara ke canvas (sisi pendek maks 720px) lalu direkam ulang ~1,2 Mbps,
    // jadi lamanya sama dengan durasi video. Kalau browser tidak mendukung, layar ditinggal, atau hasilnya
    // tidak lebih kecil, file asli yang diunggah.
    const VIDEO_BITRATE = 1200000;
    function recorderMime() {
        if (!window.MediaRecorder || !HTMLCanvasElement.prototype.captureStream) return '';
        return ['video/mp4;codecs=avc1,mp4a.40.2', 'video/mp4', 'video/webm;codecs=vp8,opus', 'video/webm']
            .find(t => MediaRecorder.isTypeSupported(t)) || '';
    }
    function compressVideo(file, host, onProgress) {
        const mime = recorderMime();
        if (!mime) return Promise.resolve(file);
        return new Promise(resolve => {
            const url = URL.createObjectURL(file);
            const v = document.createElement('video');
            const chunks = [];
            let rec = null, timer = null, done = false;

            const finish = blob => {
                if (done) return;
                done = true;
                clearTimeout(timer);
                document.removeEventListener('visibilitychange', onHide);
                v.pause();
                v.remove();
                URL.revokeObjectURL(url);
                resolve(blob && blob.size > 0 && blob.size < file.size * 0.9 ? blob : file);
            };
            const abort = () => {
                if (rec && rec.state !== 'inactive') { rec.onstop = null; rec.stop(); }
                finish(null);
            };
            const onHide = () => { if (document.hidden) abort(); };

            v.muted = true;
            v.playsInline = true;
            v.className = 'sg-upload__thumb';
            v.onerror = abort;
            v.onloadedmetadata = async () => {
                const dur = v.duration;
                // sudah kecil (bitrate rendah) -> tidak perlu dikompres
                if (!isFinite(dur) || !dur || !v.videoWidth || file.size * 8 / dur <= VIDEO_BITRATE * 1.5) return finish(null);

                const scale = Math.min(1, 720 / Math.min(v.videoWidth, v.videoHeight));
                const canvas = document.createElement('canvas');
                canvas.width = Math.round(v.videoWidth * scale / 2) * 2;
                canvas.height = Math.round(v.videoHeight * scale / 2) * 2;
                const ctx = canvas.getContext('2d');
                const stream = canvas.captureStream(30);
                try {
                    const capture = v.captureStream ? v.captureStream() : (v.mozCaptureStream ? v.mozCaptureStream() : null);
                    host.appendChild(v);   // tampil di kotak upload; video yang terlihat tetap diputar browser
                    await v.play();
                    if (capture) capture.getAudioTracks().forEach(t => stream.addTrack(t));
                    rec = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: VIDEO_BITRATE, audioBitsPerSecond: 64000 });
                } catch (e) {
                    return abort();
                }
                rec.ondataavailable = e => { if (e.data && e.data.size) chunks.push(e.data); };
                rec.onstop = () => finish(new Blob(chunks, { type: mime.split(';')[0] }));
                rec.onerror = abort;
                v.onended = () => { ctx.drawImage(v, 0, 0, canvas.width, canvas.height); rec.stop(); };
                document.addEventListener('visibilitychange', onHide);
                timer = setTimeout(abort, (dur * 1.5 + 20) * 1000);
                rec.start(1000);

                const draw = () => {
                    if (done || v.ended) return;
                    ctx.drawImage(v, 0, 0, canvas.width, canvas.height);
                    onProgress(Math.min(1, v.currentTime / dur));
                    requestAnimationFrame(draw);
                };
                draw();
            };
            v.src = url;
        });
    }

    // foto -> kompres; video -> cek durasi lalu kompres. Mengembalikan blob yang siap diunggah.
    async function prepareMedia(file, isVideo, maxSec, tile, hint) {
        if (!isVideo) return compressImage(file);
        const dur = await videoDuration(file);
        if (dur > maxSec + 5) {
            throw new Error(maxSec >= 60 && maxSec % 60 === 0
                ? `Video maksimal ${maxSec / 60} menit. Rekam ulang lebih singkat.`
                : `Video maksimal ${maxSec} detik. Rekam ulang lebih singkat.`);
        }
        tile.classList.add('is-uploading');
        const bar = tile.querySelector('.sg-progress');
        const fill = bar.firstElementChild;
        bar.hidden = false;
        fill.style.width = '0%';
        try {
            return await compressVideo(file, tile, p => {
                fill.style.width = Math.round(p * 100) + '%';
                hint.textContent = `Mengompres · ${Math.round(p * 100)}% · jangan tinggalkan layar`;
            });
        } finally {
            hint.textContent = hint.dataset.hint || hint.textContent;
        }
    }
    function videoDuration(file) {
        return new Promise(resolve => {
            const v = document.createElement('video');
            const url = URL.createObjectURL(file);
            v.preload = 'metadata';
            v.onloadedmetadata = () => { URL.revokeObjectURL(url); resolve(v.duration); };
            v.onerror = () => { URL.revokeObjectURL(url); resolve(0); };
            v.src = url;
        });
    }

    // ---------- upload bertahap (form gate in & dokumentasi keluar) ----------
    async function uploadChunked(url, chunkSize, kind, blob, onProgress, extra) {
        const id = (Date.now().toString(36) + Math.random().toString(36).slice(2, 12)).replace(/[^a-z0-9]/g, '').slice(0, 24);
        const total = Math.max(1, Math.ceil(blob.size / chunkSize));
        let last;
        for (let i = 0; i < total; i++) {
            const fd = new FormData();
            fd.append('kind', kind);
            Object.entries(extra || {}).forEach(([k, val]) => fd.append(k, val));
            fd.append('upload_id', id);
            fd.append('index', i);
            fd.append('total', total);
            fd.append('chunk', blob.slice(i * chunkSize, (i + 1) * chunkSize), 'chunk');
            last = await post(url, fd);
            onProgress((i + 1) / total);
        }
        return last;
    }


    // ---------- dokumentasi muat & keluar: foto selesai stuffing s/d seal + video (tab Muat & seal), container keluar (tab Gate out) ----------
    // langsung diunggah saat diambil; server mengembalikan daftar terbaru (mediaPayload().out)
    function initOutMedia(sec) {
        const max = parseInt(sec.dataset.max, 10);
        const chunk = parseInt(sec.dataset.chunk, 10);
        const field = sec.querySelector('.sg-field[data-field]');
        const busy = new Set();

        function render(out) {
            Object.keys(out).forEach(kind => {
                const tile = sec.querySelector(`[data-out-upload="${kind}"]`);
                if (!tile) return;
                if (kind === 'out_video') {
                    const has = !!out.out_video;
                    tile.classList.toggle('is-done', has);
                    const state = tile.querySelector('[data-video-state]');
                    state.textContent = has ? 'Tersimpan' : 'Belum ada';
                    state.classList.toggle('is-done', has);
                    tile.querySelector('[data-video-add]').textContent = has ? 'Ganti' : 'Rekam';
                    const link = tile.querySelector('[data-out-video]');
                    link.hidden = !has;
                    if (has) link.href = out.out_video;
                    return;
                }
                const list = out[kind];
                const title = tile.querySelector('.sg-upload__title').textContent.trim();
                tile.classList.toggle('is-done', list.length > 0);
                const count = tile.querySelector('[data-count-bar]');
                count.textContent = `${list.length}/${max}`;
                count.classList.toggle('is-done', list.length > 0);
                // kotak "+" hilang kalau sudah penuh
                const full = list.length >= max;
                tile.querySelector('[data-add]').hidden = full;
                tile.querySelector('input').disabled = full;
                tile.querySelector('[data-add-label]').textContent = list.length ? 'Tambah' : 'Ambil foto';
                // ketuk foto = lihat besar, tombol pojok = hapus
                tile.querySelector('[data-out-thumbs]').innerHTML = list.map((f, i) => `
                    <div class="sg-thumb" data-kind="${kind}" data-file="${f.file}">
                        <a href="${f.url}" target="_blank" rel="noopener" data-lightbox><img src="${f.url}" alt="${title} ${i + 1}"></a>
                        <button type="button" class="sg-thumb__del" aria-label="Hapus foto ${title.toLowerCase()} ${i + 1}">${document.querySelector('[data-icon-trash]').innerHTML}</button>
                    </div>`).join('');
            });
            const exitBox = sec.querySelector('[data-out-exit-at]');
            if (exitBox) {
                exitBox.hidden = !out.out_exit_at;
                exitBox.querySelector('[data-out-exit-time]').textContent = out.out_exit_at || '';
            }
            // jam tutup container = waktu foto seal terpasang terakhir (ditampilkan di bagian Selesai stuffing)
            const closeAt = document.querySelector('[data-close-at]');
            if (closeAt) {
                closeAt.textContent = out.out_close_at || 'Belum ada foto seal terpasang';
                closeAt.classList.toggle('is-empty', !out.out_close_at);
            }
            // status tab (stepper) ikut diperbarui setelah foto/video bertambah atau terhapus
            sec.dispatchEvent(new Event('sg:media', { bubbles: true }));
            if (complete()) {
                field.classList.remove('is-error');
                field.querySelector(':scope > .sg-hint').hidden = true;
            }
        }
        const required = () => Array.from(sec.querySelectorAll('[data-out-upload]:not([data-optional])'));
        const complete = () => required().every(t => t.classList.contains('is-done'));

        sec.querySelectorAll('[data-out-upload]').forEach(tile => {
            const kind = tile.dataset.outUpload;
            const input = tile.querySelector('input[type=file]');
            const bar = tile.querySelector('.sg-progress');
            const hint = tile.querySelector('.sg-upload__hint');
            input.addEventListener('change', async () => {
                const file = input.files[0];
                input.value = '';
                if (!file) return;
                tile.classList.remove('is-error');
                const wasDone = tile.classList.contains('is-done');
                busy.add(tile);
                try {
                    const blob = await prepareMedia(file, kind === 'out_video', 60, tile, hint);
                    tile.classList.add('is-uploading');
                    tile.classList.remove('is-done');
                    bar.hidden = false;
                    const fill = bar.firstElementChild;
                    fill.style.width = '0%';
                    const res = await uploadChunked(sec.dataset.uploadUrl, chunk, kind, blob, p => {
                        fill.style.width = Math.round(p * 100) + '%';
                        hint.textContent = `Mengunggah · ${Math.round(p * 100)}%`;
                    });
                    hint.textContent = hint.dataset.hint;
                    render(res.out);
                } catch (err) {
                    hint.textContent = '⚠ ' + err.message;
                    tile.classList.add('is-error');
                    tile.classList.toggle('is-done', wasDone);
                } finally {
                    busy.delete(tile);
                    tile.classList.remove('is-uploading');
                    bar.hidden = true;
                }
            });
        });

        sec.addEventListener('click', async e => {
            const btn = e.target.closest('.sg-thumb__del');
            if (!btn) return;
            if (!await sgConfirm('Foto akan dihapus dari dokumentasi dan tidak bisa dikembalikan.', { type: 'danger', title: 'Hapus foto ini?', okText: 'Ya, hapus' })) return;
            const thumb = btn.closest('.sg-thumb');
            const fd = new FormData();
            fd.append('kind', thumb.dataset.kind);
            fd.append('file', thumb.dataset.file);
            try {
                render((await post(sec.dataset.deleteUrl, fd)).out);
            } catch (err) {
                sgAlert(err.message, { title: 'Foto belum terhapus' });
            }
        });

        return {
            busy: () => busy.size > 0,
            // pesan untuk jenis yang belum ada (cek cepat sebelum kirim; server tetap memeriksa)
            field: field.dataset.field,
            missing: () => required()
                .filter(t => !t.classList.contains('is-done'))
                .map(t => t.dataset.outUpload === 'out_video' ? 'Rekam video dokumentasi seal.' : `Ambil foto ${t.querySelector('.sg-upload__title').firstChild.textContent.trim()}.`),
        };
    }

    // ---------- tab tahap container (Gate in, Muat & seal, Gate out) di halaman container yang sudah tiba ----------
    // satu tahap bisa terdiri dari beberapa elemen [data-panel] (ringkasan carton di luar form catat keluar)
    const doneStepper = document.getElementById('doneStepper');
    if (doneStepper) {
        const main = document.getElementById('doneMain');
        const tabs = Array.from(doneStepper.querySelectorAll('[data-tab]'));
        const parts = name => Array.from(main.querySelectorAll(`[data-panel="${name}"]`));
        let activeTab = null;
        function showTab(name, scroll) {
            activeTab = name;
            tabs.forEach(li => {
                const on = li.dataset.tab === name;
                li.classList.toggle('is-active', on);
                li.querySelector('a').setAttribute('aria-selected', on ? 'true' : 'false');
            });
            main.querySelectorAll('[data-panel]').forEach(p => { p.hidden = p.dataset.panel !== name; });
            // tombol bar bawah sesuai tab (Simpan data muat / Simpan data seal / Catat keluar); tab lain: kembali ke daftar
            const actions = Array.from(document.querySelectorAll('[data-tab-action]'));
            actions.forEach(b => { b.hidden = !b.dataset.tabAction.split(' ').includes(name); });
            const fallback = document.querySelector('[data-tab-fallback]');
            if (fallback) fallback.hidden = actions.some(b => !b.hidden);
            if (scroll) doneStepper.scrollIntoView({ block: 'start' });
        }
        // tombol sebelumnya / berikutnya di bawah elemen terakhir tiap tab
        const names = tabs.map(li => li.dataset.tab).filter(n => parts(n).length);
        const label = n => doneStepper.querySelector(`[data-tab="${n}"] a`).textContent.replace(/^\d+\.\s*/, '');
        names.forEach((n, i) => {
            const nav = document.createElement('div');
            nav.className = 'sg-panel__nav';
            const prev = names[i - 1], next = names[i + 1];
            nav.innerHTML = (prev ? `<button type="button" class="sg-btn" data-go="${prev}">‹ ${label(prev)}</button>` : '<span></span>')
                + (next ? `<button type="button" class="sg-btn sg-btn--primary" data-go="${next}">${label(next)} ›</button>` : '');
            const last = parts(n).pop();
            if (last.classList.contains('sg-panel')) last.appendChild(nav); else last.after(nav);
            nav.dataset.panel = n;
        });
        main.addEventListener('click', e => {
            const btn = e.target.closest('[data-go]');
            if (btn) showTab(btn.dataset.go, true);
        });
        doneStepper.addEventListener('click', e => {
            const li = e.target.closest('[data-tab]');
            if (!li) return;
            e.preventDefault();
            showTab(li.dataset.tab, true);
        });
        gateTabs.reveal = el => {
            const panel = el.closest('[data-panel]');
            if (panel && panel.dataset.panel !== activeTab) showTab(panel.dataset.panel, false);
        };
        gateTabs.sync = () => tabs.forEach(li => {
            li.classList.toggle('has-error', parts(li.dataset.tab).some(p => p.querySelector('.is-error')));
        });
        showTab(names.includes(main.dataset.startTab) ? main.dataset.startTab : names[0], false);
    }

    // ---------- catat keluar (halaman container yang sudah tiba) ----------
    const outForm = document.getElementById('outForm');
    if (outForm) {
        const outErr = errorBox(outForm, document.getElementById('outAlert'), document.getElementById('outAlertList'));
        const btnOut = document.getElementById('btnOut');
        const pads = Array.from(outForm.querySelectorAll('[data-sign]')).map(initSignPad);
        // dokumentasi tab Muat & seal (out_media) dan tab Gate out (out_exit_media)
        const outMedia = Array.from(outForm.querySelectorAll('[data-out-media]')).map(initOutMedia);
        const mediaOf = name => outMedia.find(m => m.field === name);
        // isian yang belum disimpan, per tab (load / seal); foto & video langsung tersimpan saat diambil
        const dirtyTabs = new Set();
        const markDirty = e => {
            if (e.target.type === 'file') return;
            const panel = e.target.closest('[data-panel]');
            if (panel) dirtyTabs.add(panel.dataset.panel);
        };
        outForm.addEventListener('input', markDirty);
        outForm.addEventListener('change', markDirty);
        window.addEventListener('beforeunload', e => { if (dirtyTabs.size) { e.preventDefault(); e.returnValue = ''; } });

        // simpan per tab tanpa catat keluar: Muat (jam buka, petugas muat, dst.) dan Seal (langkah VVTT,
        // petugas seal, tanda tangan). Isian yang belum lengkap boleh disimpan dulu.
        function saveButton(btn, url, tab, label, extra) {
            if (!btn) return;
            btn.addEventListener('click', async () => {
                outErr.clear();
                setLoading(btn, true);
                try {
                    const fd = new FormData(outForm);
                    if (extra) extra(fd);
                    const res = await post(url, fd);
                    dirtyTabs.delete(tab);
                    if (tab === 'seal') pads.forEach(p => p.markSaved());
                    btn.lastElementChild.textContent = res.message || 'Tersimpan';
                    setTimeout(() => { btn.lastElementChild.textContent = label; }, 2000);
                } catch (err) {
                    outErr.show(err.errors || { general: [err.message] }, !!err.errors);
                } finally {
                    setLoading(btn, false);
                }
            });
        }
        saveButton(document.getElementById('btnStuffSave'), outForm.dataset.stuffingUrl, 'load', 'Simpan data muat');
        saveButton(document.getElementById('btnSealSave'), outForm.dataset.sealUrl, 'seal', 'Simpan data seal',
            fd => pads.filter(p => p.changed()).forEach(p => fd.append('sign_' + p.key, p.data())));

        // warna tab: hijau = isian tab lengkap, kuning = tahap berikutnya yang perlu dikerjakan.
        // Gate out baru hijau setelah Catat keluar (halaman berganti ke tampilan baca saja)
        function syncSteps() {
            if (!doneStepper || outForm.dataset.rejected === '1') return;
            const filled = n => !!outForm.elements[n] && outForm.elements[n].value.trim() !== '';
            const people = n => outForm.querySelectorAll(`input[name="${n}[]"]`).length > 0;
            const sealPanel = outForm.querySelector('#panel-seal');
            const boxes = Array.from(outForm.querySelectorAll('input[name="seal_check[]"]'));
            const done = {
                load: !outForm.querySelector('[data-stuffing]') || (['gate_lokasi', 'gate_country', 'gate_open_at'].every(filled)
                    && !outForm.querySelector('[data-load-missing]') && people('gate_loaders') && people('gate_guards')),
                seal: !!sealPanel && boxes.length > 0 && boxes.every(b => b.checked)
                    && Array.from(sealPanel.querySelectorAll('[data-person] input[type=hidden]')).every(i => i.value.trim() !== '')
                    && !sealPanel.querySelector('[data-field="noseal"] .sg-alert')
                    && (!mediaOf('out_media') || !mediaOf('out_media').missing().length)
                    && pads.every(p => !p.empty()),
                out: false,
            };
            let current = false;
            Object.entries(done).forEach(([name, ok]) => {
                const li = doneStepper.querySelector(`[data-tab="${name}"]`);
                if (!li) return;
                li.classList.toggle('is-done', ok);
                li.classList.toggle('is-current', !ok && !current);
                if (!ok) current = true;
            });
        }
        ['input', 'change', 'sg:media'].forEach(t => outForm.addEventListener(t, syncSteps));
        syncSteps();

        btnOut.addEventListener('click', async () => {
            outErr.clear();
            const unsigned = {};
            if (outMedia.some(m => m.busy())) unsigned.general = ['Tunggu upload foto/video dokumentasi selesai.'];
            // urut sesuai halaman: error pertama yang ditampilkan (tab Muat & seal dulu, lalu Gate out)
            if (outForm.querySelector('[data-load-missing]')) unsigned.gate_load_at = ['Jam mulai muat belum terisi. Tunggu finishing melakukan action muat, lalu muat ulang halaman.'];
            const loadMissing = mediaOf('out_media') ? mediaOf('out_media').missing() : [];
            if (loadMissing.length) unsigned.out_media = loadMissing;
            pads.filter(p => p.empty()).forEach(p => { unsigned['sign_' + p.key] = [`Tanda tangan ${p.label} belum diisi.`]; });
            const exitMissing = mediaOf('out_exit_media') ? mediaOf('out_exit_media').missing() : [];
            if (exitMissing.length) unsigned.out_exit_media = exitMissing;
            if (Object.keys(unsigned).length) {
                outErr.show(unsigned);
                const first = outForm.querySelector(`[data-field="${Object.keys(unsigned).find(k => k !== 'general')}"]`);
                if (first) {
                    gateTabs.reveal(first);
                    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            const rejectedOut = outForm.dataset.rejected === '1';
            const exitTime = (outForm.querySelector('[data-out-exit-time]') || {}).textContent || 'saat ini';
            const ok = await sgConfirm(rejectedOut
                ? 'Jam keluar diambil saat ini dan tidak bisa diubah.'
                : `Jam keluar: ${exitTime}.\nPastikan pemuatan selesai dan seal terpasang. Jam keluar tidak bisa diubah.`,
                { title: rejectedOut ? 'Catat truk keluar sekarang?' : 'Catat container keluar?', okText: 'Ya, catat keluar' });
            if (!ok) return;
            setLoading(btnOut, true);
            try {
                const fd = new FormData(outForm);
                pads.filter(p => p.changed()).forEach(p => fd.append('sign_' + p.key, p.data()));
                const res = await post(outForm.dataset.url, fd);
                dirtyTabs.clear();
                window.location.href = res.redirect;
            } catch (err) {
                outErr.show(err.errors || { general: [err.message] }, !!err.errors);
                setLoading(btnOut, false);
            }
        });
    }

    // ---------- scan ID card karyawan (QR berisi NRK) untuk petugas seal ----------
    // HTTPS: kamera live; HTTP: foto QR. Pembaca QR dimuat di awal, foto diperkecil saat dibuka,
    // dan data karyawan yang sudah pernah dicari disimpan supaya scan ulang langsung terisi.
    const scanDlg = document.getElementById('scanner');
    if (scanDlg) {
        const video = scanDlg.querySelector('video');
        const liveBox = scanDlg.querySelector('[data-scan-live]');
        const photoBtn = scanDlg.querySelector('[data-scan-photo]');
        const photoInput = photoBtn.querySelector('input');
        const statusEl = scanDlg.querySelector('[data-scan-status]');
        const titleEl = scanDlg.querySelector('[data-scan-title]');
        const manualForm = scanDlg.querySelector('[data-scan-manual]');
        const manualInput = manualForm.querySelector('input');
        const warnEl = scanDlg.querySelector('[data-scan-warn]');
        const resultsEl = scanDlg.querySelector('[data-scan-results]');
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d', { willReadFrequently: true });
        // getUserMedia & BarcodeDetector hanya tersedia di HTTPS / localhost
        const canLive = window.isSecureContext && !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        let detector = null;
        try { if ('BarcodeDetector' in window) detector = new window.BarcodeDetector({ formats: ['qr_code'] }); } catch (e) {}
        // pick(emp) mengisi target (slot / daftar petugas); kembalikan pesan error kalau ditolak
        let pick = null, opener = null, stream = null, timer = null, looking = false, lastBad = null, libLoading = null;
        // hl = target yang sedang diisi boleh karyawan harian lepas (petugas muat)
        let opts = { hl: false };
        const known = {};    // sumber|NRK => data karyawan yang sudah pernah dicari di halaman ini

        const loadLib = () => window.jsQR ? Promise.resolve() : (libLoading = libLoading || new Promise((resolve, reject) => {
            const tag = document.createElement('script');
            tag.src = scanDlg.dataset.lib;
            tag.onload = resolve;
            tag.onerror = () => { libLoading = null; tag.remove(); reject(new Error('Pembaca QR gagal dimuat. Periksa sinyal lalu coba lagi.')); };
            document.head.appendChild(tag);
        }));
        // muat pembaca QR di awal supaya scan pertama tidak menunggu download
        (window.requestIdleCallback || (fn => setTimeout(fn, 800)))(() => loadLib().catch(() => {}));

        function setStatus(msg, kind) {
            statusEl.textContent = msg;
            statusEl.className = 'sg-scan__status' + (kind ? ' is-' + kind : '');
        }

        // ---- membaca QR ----
        // pembaca bawaan browser: undefined = tidak tersedia / gagal, null = tidak ada QR
        async function nativeScan(source) {
            if (!detector) return undefined;
            try {
                const found = await detector.detect(source);
                return found.length && found[0].rawValue.trim() ? found[0].rawValue.trim() : null;
            } catch (e) { return undefined; }
        }
        function jsqrScan(source, sx, sy, sw, sh, maxSide, inversion) {
            const scale = Math.min(1, maxSide / Math.max(sw, sh));
            canvas.width = Math.round(sw * scale);
            canvas.height = Math.round(sh * scale);
            ctx.drawImage(source, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height);
            const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const code = window.jsQR(img.data, canvas.width, canvas.height, { inversionAttempts: inversion });
            return code && code.data.trim() ? code.data.trim() : null;
        }
        // foto 12 MP diperkecil saat dibuka (createImageBitmap jauh lebih cepat dari <img>)
        async function loadPhoto(file) {
            if (window.createImageBitmap) {
                try { return await createImageBitmap(file, { resizeWidth: 1600, resizeQuality: 'medium' }); } catch (e) {}
            }
            const url = URL.createObjectURL(file);
            try {
                return await new Promise((resolve, reject) => {
                    const im = new Image();
                    im.onload = () => resolve(im);
                    im.onerror = () => reject(new Error('Foto tidak bisa dibaca. Ulangi.'));
                    im.src = url;
                });
            } finally {
                URL.revokeObjectURL(url);
            }
        }
        // urutan dari yang tercepat: utuh kecil -> bagian tengah (QR biasanya di tengah) -> utuh besar
        async function readPhoto(file) {
            const img = await loadPhoto(file);
            try {
                const native = await nativeScan(img);
                if (native) return native;
                await loadLib();
                const w = img.naturalWidth || img.width, h = img.naturalHeight || img.height, c = Math.min(w, h) * 0.6;
                return jsqrScan(img, 0, 0, w, h, 800, 'dontInvert')
                    || jsqrScan(img, (w - c) / 2, (h - c) / 2, c, c, 800, 'dontInvert')
                    || jsqrScan(img, 0, 0, w, h, 1600, 'attemptBoth');
            } finally {
                if (img.close) img.close();
            }
        }

        // ---- data karyawan & slot petugas ----
        async function fetchEmployee(karid) {
            const key = (opts.hl ? 'hl|' : 'hrd|') + karid;
            if (known[key]) return known[key];
            const res = await fetch(`${scanDlg.dataset.url}?karid=${encodeURIComponent(karid)}${opts.hl ? '&hl=1' : ''}`, { headers: { 'Accept': 'application/json' } });
            let json = {};
            try { json = await res.json(); } catch (e) {}
            if (!res.ok) throw new Error(json.message || 'Gagal terhubung ke server. Coba lagi.');
            return (known[key] = json.employee);
        }
        // keterangan di bawah nama (sama dengan GateController::personMeta)
        function personMeta(emp) {
            return ['NRK ' + emp.karid, emp.src === 'hl' ? 'Harian lepas' : '', emp.depnm].filter(Boolean).join(' · ');
        }
        // ---- daftar petugas (orang muat, security): tambah dari scan, hapus per orang ----
        function syncPeople(list) {
            const n = list.querySelectorAll('[data-people-list] li').length;
            const count = list.closest('.sg-field').querySelector('[data-people-count]');
            count.textContent = `${n}/${list.dataset.max}`;
            count.classList.toggle('is-done', n > 0);
            list.querySelector('[data-people-add]').hidden = n >= parseInt(list.dataset.max, 10);
        }
        function addPerson(list, emp) {
            const inputs = Array.from(list.querySelectorAll('input[type=hidden]'));
            const token = emp.token || emp.karid;
            if (inputs.some(i => i.value === token)) return `${emp.nama} sudah ada di daftar ${list.dataset.label.toLowerCase()}.`;
            if (inputs.length >= parseInt(list.dataset.max, 10)) return `${list.dataset.label} maksimal ${list.dataset.max} orang.`;
            const li = document.createElement('li');
            li.className = 'sg-people__item' + (emp.src === 'hl' ? ' is-hl' : '');
            li.innerHTML = `<input type="hidden"><span class="sg-person__body"><strong></strong><small></small></span>
                <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-people-remove>${document.querySelector('[data-icon-close]').innerHTML}</button>`;
            const input = li.querySelector('input');
            input.name = list.dataset.name + '[]';
            input.value = token;
            li.querySelector('strong').textContent = emp.nama;
            li.querySelector('small').textContent = personMeta(emp);
            li.querySelector('button').setAttribute('aria-label', 'Hapus ' + emp.nama);
            list.querySelector('[data-people-list]').appendChild(li);
            syncPeople(list);
            const field = list.closest('.sg-field');
            field.classList.remove('is-error');
            const hint = field.querySelector('.sg-hint');
            if (hint) hint.hidden = true;
            list.dispatchEvent(new Event('input', { bubbles: true }));
            return null;
        }

        function fillSlot(el, emp) {
            el.querySelector('input[type=hidden]').value = emp.karid;
            el.querySelector('[data-person-name]').textContent = emp.nama;
            el.querySelector('[data-person-meta]').textContent = 'NRK ' + emp.karid + (emp.depnm ? ' · ' + emp.depnm : '');
            el.querySelector('[data-person-btn]').textContent = 'Ganti';
            el.querySelector('.sg-person__icon').innerHTML = document.querySelector('[data-icon-check]').innerHTML;
            el.classList.add('is-filled');
            const field = el.closest('.sg-field');
            field.classList.remove('is-error');
            const hint = field.querySelector('.sg-hint');
            if (hint) hint.hidden = true;
            el.dispatchEvent(new Event('input', { bubbles: true }));
        }
        // ---- dialog (kamera live / foto ulang / ketik NRK) ----
        // isi QR ID card -> NRK: tanpa spasi; lebih dari 6 karakter diambil 6 terakhir (sama dengan server)
        function normalizeKarid(raw) {
            const v = String(raw).replace(/\s+/g, '');
            return v.length > 6 ? v.slice(-6) : v;
        }
        async function lookup(raw) {
            const karid = normalizeKarid(raw);
            looking = true;
            setStatus(`Mencari NRK ${karid}…`);
            try {
                const problem = pick(await fetchEmployee(karid));
                if (problem) throw new Error(problem);
                closeScanner();
            } catch (err) {
                setStatus(err.message, 'error');
                // QR yang sama tidak dicari ulang terus-menerus selama masih di depan kamera
                lastBad = karid;
                setTimeout(() => { if (lastBad === karid) lastBad = null; }, 3000);
            } finally {
                looking = false;
            }
        }
        function stopLive() {
            clearTimeout(timer);
            if (stream) stream.getTracks().forEach(t => t.stop());
            stream = null;
            video.srcObject = null;
        }
        function tick() {
            timer = setTimeout(async () => {
                if (!stream) return;
                if (!looking && video.readyState >= 2) {
                    let code = await nativeScan(video);
                    if (code === undefined && window.jsQR) {
                        // cukup area tengah (kotak bidik) supaya cepat
                        const w = video.videoWidth, h = video.videoHeight, c = Math.min(w, h) * 0.75;
                        code = jsqrScan(video, (w - c) / 2, (h - c) / 2, c, c, 640, 'dontInvert');
                    }
                    if (code && normalizeKarid(code) !== lastBad) await lookup(code);
                }
                if (stream) tick();
            }, 120);
        }
        async function startLive() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }, audio: false });
                if (scanDlg.hidden) { stopLive(); return; }
                video.srcObject = stream;
                await video.play();
                setStatus('Arahkan kamera ke QR code di ID card karyawan.');
                tick();
            } catch (e) {
                stopLive();
                liveBox.hidden = true;
                photoBtn.hidden = false;
                setStatus('Kamera tidak bisa dibuka. Pakai tombol foto.', 'warn');
            }
        }
        // o.hl: boleh karyawan harian lepas (petugas muat)
        function openScanner(label, onPick, from, o = {}) {
            pick = onPick;
            opener = from;
            lastBad = null;
            opts = { hl: !!o.hl };
            titleEl.textContent = label;
            manualInput.value = '';
            resetSearch();
            liveBox.hidden = !canLive;
            photoBtn.hidden = canLive;
            setStatus(canLive ? 'Membuka kamera…' : 'Foto QR code di ID card dari dekat, pastikan tidak buram.');
            scanDlg.hidden = false;
            document.body.classList.add('sg-noscroll');
            scanDlg.querySelector('[data-scan-close]').focus();
            // kamera langsung dinyalakan; pembaca QR dimuat paralel (tick menunggu sampai siap)
            if (canLive) {
                loadLib().catch(err => setStatus(err.message, 'error'));
                startLive();
            }
        }
        function closeScanner() {
            stopLive();
            scanDlg.hidden = true;
            document.body.classList.remove('sg-noscroll');
            if (opener && document.contains(opener)) opener.focus();
            pick = opener = null;
        }

        document.addEventListener('click', e => {
            const slotBtn = e.target.closest('[data-person-scan]');
            if (slotBtn) {
                const el = slotBtn.closest('[data-person]');
                openScanner(el.dataset.label, emp => { fillSlot(el, emp); return null; }, slotBtn);
                return;
            }
            const addBtn = e.target.closest('[data-people-scan]');
            if (addBtn) {
                const list = addBtn.closest('[data-people]');
                openScanner(list.dataset.label, emp => addPerson(list, emp), addBtn, { hl: list.dataset.hl === '1' });
                return;
            }
            const removeBtn = e.target.closest('[data-people-remove]');
            if (removeBtn) {
                const list = removeBtn.closest('[data-people]');
                removeBtn.closest('li').remove();
                syncPeople(list);
                list.dispatchEvent(new Event('input', { bubbles: true }));
                list.querySelector('[data-people-scan]').focus();
            }
        });
        scanDlg.querySelector('[data-scan-close]').addEventListener('click', closeScanner);
        scanDlg.addEventListener('click', e => { if (e.target === scanDlg) closeScanner(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !scanDlg.hidden) closeScanner(); });
        photoInput.addEventListener('change', async () => {
            const file = photoInput.files[0];
            photoInput.value = '';
            if (!file) return;
            setStatus('Membaca QR code…');
            try {
                const code = await readPhoto(file);
                if (!code) throw new Error('QR code tidak terbaca. Foto ulang lebih dekat, tidak buram dan tidak silau.');
                await lookup(code);
            } catch (err) {
                setStatus(err.message, 'error');
            }
        });
        // ---- cari nama / NRK (petugas tanpa ID card) ----
        let searchTimer = null, searchSeq = 0;
        const searchCache = {};   // sumber|kata kunci => hasil
        function resetSearch() {
            clearTimeout(searchTimer);
            searchSeq++;
            resultsEl.hidden = true;
            resultsEl.innerHTML = '';
            warnEl.hidden = true;
        }
        async function search(q) {
            const seq = ++searchSeq;
            const key = (opts.hl ? 'hl|' : 'hrd|') + q.toLowerCase();
            setStatus('Mencari…');
            try {
                let json = searchCache[key];
                if (!json) {
                    const res = await fetch(`${scanDlg.dataset.searchUrl}?q=${encodeURIComponent(q)}${opts.hl ? '&hl=1' : ''}`, { headers: { 'Accept': 'application/json' } });
                    try { json = await res.json(); } catch (e) { json = {}; }
                    if (!res.ok) throw new Error(json.message || 'Gagal terhubung ke server. Coba lagi.');
                    searchCache[key] = json;
                }
                if (seq !== searchSeq) return;   // sudah ada ketikan baru
                renderResults(json.results || [], q);
                warnEl.textContent = json.warning || '';
                warnEl.hidden = !json.warning;
            } catch (err) {
                if (seq === searchSeq) setStatus(err.message, 'error');
            }
        }
        function renderResults(list, q) {
            resultsEl.innerHTML = '';
            list.forEach(emp => {
                const li = document.createElement('li');
                li.innerHTML = '<button type="button" class="sg-scan__result"><strong></strong><dl></dl></button>';
                li.querySelector('strong').textContent = emp.nama + (emp.src === 'hl' ? ' (harian lepas)' : '');
                const dl = li.querySelector('dl');
                [['NRK', emp.karid], ['Department', emp.depnm]]
                    .forEach(([k, v]) => {
                        const dt = document.createElement('dt'), dd = document.createElement('dd');
                        dt.textContent = k;
                        dd.textContent = v || '-';
                        dl.append(dt, dd);
                    });
                li.querySelector('button').addEventListener('click', () => choose(emp));
                resultsEl.appendChild(li);
            });
            resultsEl.hidden = !list.length;
            setStatus(list.length
                ? `${list.length} karyawan cocok. Ketuk nama untuk memilih.`
                : `Tidak ada karyawan aktif yang cocok dengan "${q}".`, list.length ? '' : 'warn');
        }
        function choose(emp) {
            const problem = pick(emp);
            if (problem) { setStatus(problem, 'error'); return; }
            closeScanner();
        }
        manualInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const v = manualInput.value.trim();
            if (v.length < 2) { resetSearch(); setStatus(''); return; }
            searchTimer = setTimeout(() => search(v), 300);
        });
        // NRK + Enter (juga scanner barcode USB/Bluetooth) langsung dipilih; selain itu cari nama
        manualForm.addEventListener('submit', e => {
            e.preventDefault();
            const v = manualInput.value.trim();
            if (!v || looking) return;
            if (/^\d{3,}$/.test(v.replace(/\s+/g, ''))) { resetSearch(); lookup(v); return; }
            if (v.length >= 2) { clearTimeout(searchTimer); search(v); }
        });
    }

    const form = document.getElementById('gateForm');
    if (!form) return;

    const editing = form.dataset.mode === 'edit';   // perbaiki data container yang sudah tiba / keluar
    const chunkSize = parseInt(form.dataset.chunk, 10);
    const btnDraft = document.getElementById('btnDraft');
    const btnSubmit = document.getElementById('btnSubmit');
    const thumbs = document.getElementById('contThumbs');
    const busy = new Set();          // upload yang sedang berjalan
    let dirty = false;
    const { clear: clearErrors, show: showErrors } = errorBox(form, document.getElementById('formAlert'), document.getElementById('formAlertList'));

    // ---------- tab per bagian (Container, Truk & driver, Pemeriksaan, ...) ----------
    // menu langkah di atas form berfungsi sebagai tab: hanya bagian yang dipilih yang tampil
    const stepper = document.getElementById('gateStepper');
    const tabs = Array.from(stepper.querySelectorAll('[data-tab]'));
    const panels = Array.from(form.querySelectorAll('[data-panel]'));
    let activeTab = null;
    function showTab(name, scroll) {
        activeTab = name;
        tabs.forEach(li => {
            const on = li.dataset.tab === name;
            li.classList.toggle('is-active', on);
            li.querySelector('a').setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(p => { p.hidden = p.dataset.panel !== name; });
        if (scroll) stepper.scrollIntoView({ block: 'start' });
    }
    // tombol sebelumnya / berikutnya di bawah tiap tab
    panels.forEach((panel, i) => {
        const nav = document.createElement('div');
        nav.className = 'sg-panel__nav';
        const prev = panels[i - 1], next = panels[i + 1];
        const label = p => stepper.querySelector(`[data-tab="${p.dataset.panel}"] a`).textContent.replace(/^\d+\.\s*/, '');
        nav.innerHTML = (prev ? `<button type="button" class="sg-btn" data-go="${prev.dataset.panel}">‹ ${label(prev)}</button>` : '<span></span>')
            + (next ? `<button type="button" class="sg-btn sg-btn--primary" data-go="${next.dataset.panel}">${label(next)} ›</button>` : '');
        panel.appendChild(nav);
    });
    form.addEventListener('click', e => {
        const btn = e.target.closest('[data-go]');
        if (btn) showTab(btn.dataset.go, true);
    });
    stepper.addEventListener('click', e => {
        const li = e.target.closest('[data-tab]');
        if (!li) return;
        e.preventDefault();
        showTab(li.dataset.tab, true);
    });
    gateTabs.reveal = el => {
        const panel = el.closest('[data-panel]');
        if (panel && panel.dataset.panel !== activeTab) showTab(panel.dataset.panel, false);
    };
    // tab yang berisi error / pertanyaan belum dijawab diberi tanda merah
    gateTabs.sync = () => tabs.forEach(li => {
        const panel = form.querySelector(`[data-panel="${li.dataset.tab}"]`);
        li.classList.toggle('has-error', !!(panel && panel.querySelector('.is-error, .is-missing')));
    });

    // ---------- checklist pemeriksaan ----------
    const checks = Array.from(form.querySelectorAll('[data-check]'));
    const summary = document.getElementById('checkSummary');
    function checkState(item) {
        const picked = item.querySelector('input[type=radio]:checked');
        const v = picked ? picked.value : null;
        return { v, issue: !!v && v !== item.dataset.expect, note: item.querySelector('.sg-check__note input').value.trim() };
    }
    function checkComplete(st) {
        return !!st.v && (!st.issue || !!st.note);
    }
    function syncChecklist() {
        let answered = 0, issues = 0;
        checks.forEach(item => {
            const st = checkState(item);
            item.classList.toggle('is-ok', !!st.v && !st.issue);
            item.classList.toggle('is-issue', st.issue);
            item.querySelector('.sg-check__note').hidden = !st.issue;
            if (checkComplete(st)) item.classList.remove('is-missing');
            if (st.v) answered++;
            if (st.issue) issues++;
        });
        form.querySelectorAll('[data-check-section]').forEach(sec => {
            const items = Array.from(sec.querySelectorAll('[data-check]'));
            const done = items.filter(i => checkState(i).v).length;
            const badge = sec.querySelector('[data-check-count]');
            badge.textContent = `${done}/${items.length}`;
            badge.classList.toggle('is-done', done === items.length);
        });
        const left = checks.length - answered;
        summary.textContent = left
            ? `${left} dari ${checks.length} pertanyaan pemeriksaan belum dijawab.` + (issues ? ` ${issues} temuan sejauh ini.` : '')
            : (issues ? `Semua pertanyaan dijawab · ${issues} temuan. Periksa lagi sebelum memilih Lulus.` : 'Semua pertanyaan dijawab · tidak ada temuan.');
        summary.classList.toggle('is-warn', issues > 0);
    }
    function checklistJson() {
        const out = {};
        checks.forEach(item => {
            const st = checkState(item);
            if (st.v) out[item.dataset.check] = st.issue && st.note ? { v: st.v, n: st.note } : { v: st.v };
        });
        return JSON.stringify(out);
    }
    // tandai pertanyaan yang belum dijawab / temuan tanpa catatan; kembalikan pesan error
    function checklistProblems() {
        const missing = [], noNote = [];
        checks.forEach(item => {
            const st = checkState(item);
            item.classList.remove('is-missing');
            if (!st.v) missing.push(item); else if (!checkComplete(st)) noNote.push(item);
        });
        missing.concat(noNote).forEach(i => i.classList.add('is-missing'));
        const msgs = [];
        if (missing.length) msgs.push(`Jawab semua pertanyaan pemeriksaan (${missing.length} belum dijawab).`);
        if (noNote.length) msgs.push(`Isi catatan untuk setiap temuan (${noNote.length} belum ada catatan).`);
        return { msgs, first: missing[0] || noNote[0] };
    }

    // ---------- stepper ----------
    function val(name) {
        if (form.querySelector(`input[type=radio][name="${name}"]`)) {
            const picked = form.querySelector(`input[name="${name}"]:checked`);
            return picked ? picked.value : '';
        }
        const el = form.elements[name];
        return el ? el.value.trim() : '';
    }
    function syncStepper() {
        const done = {
            truck: ['gate_driver', 'gate_sim_no', 'gate_sim_exp'].every(n => val(n))
                && form.querySelector('[data-upload="id"]').classList.contains('is-done'),
            check: checks.every(i => checkComplete(checkState(i))),
            // container lama yang sudah tiba tetap sah dengan min. 3 foto lama
            media: Array.from(form.querySelectorAll('[data-upload]:not([data-upload="id"])')).every(t => t.classList.contains('is-done')
                || (t.dataset.upload === 'cont' && editing && thumbs.children.length >= 3)),
            result: (val('gate_result') === '1' || (val('gate_result') === '0' && !!val('gate_result_note')))
                // petugas security gate in (kalau bagiannya ada; edit data lama boleh kosong)
                && (editing || !form.querySelector('[data-field="gate_in_guards"]') || !!form.querySelector('input[name="gate_in_guards[]"]')),
        };
        let currentSet = false;
        Object.entries(done).forEach(([step, ok]) => {
            const li = document.querySelector(`[data-step="${step}"]`);
            li.classList.toggle('is-done', ok);
            li.classList.toggle('is-current', !ok && !currentSet);
            if (!ok) currentSet = true;
        });
    }

    const simExpired = document.getElementById('simExpired');
    const resultNote = form.querySelector('[data-field="gate_result_note"]');
    const localToday = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    function syncAll() {
        syncChecklist();
        resultNote.hidden = val('gate_result') !== '0';
        simExpired.hidden = !(val('gate_sim_exp') && val('gate_sim_exp') < localToday);
        syncStepper();
        gateTabs.sync();
    }
    form.addEventListener('input', e => {
        dirty = true;
        if (e.target.name === 'contno') upperOnInput(e.target);
        syncAll();
    });
    form.addEventListener('change', syncAll);
    syncAll();
    showTab('container', false);

    function fields() {
        const fd = new FormData();
        ['contno', 'gate_unit', 'gate_driver', 'gate_driver_hp', 'gate_sim_no', 'gate_sim_exp',
            'gate_result', 'gate_result_note'].forEach(n => {
            if (form.elements[n]) fd.append(n, n === 'gate_result_note' && val('gate_result') !== '0' ? '' : val(n));
        });
        fd.append('checklist', checklistJson());
        form.querySelectorAll('input[name="gate_in_guards[]"]').forEach(i => fd.append(i.name, i.value));
        // data seal hanya ada di mode edit setelah truk keluar
        ['gate_seal_receiver_nrk', 'gate_seal_installer_nrk', 'gate_seal_supervisor_nrk', 'gate_seal_inspector_nrk'].forEach(n => {
            if (form.elements[n]) fd.append(n, val(n));
        });
        form.querySelectorAll('input[name="seal_check[]"]:checked').forEach(cb => fd.append('seal_check[]', cb.value));
        // data muat (berita acara stuffing & dokumen pengiriman) di mode edit setelah keluar
        form.querySelectorAll('[data-stuffing] input[name]').forEach(i => fd.append(i.name, i.value));
        return fd;
    }

    function renderMedia(data) {
        const idTile = form.querySelector('[data-upload="id"]');
        const idImg = idTile.querySelector('.sg-upload__thumb');
        idTile.classList.toggle('is-done', !!data.id);
        idImg.hidden = !data.id;
        if (data.id) idImg.src = data.id;

        // foto 5 sisi container: { depan: url|null, ... }
        form.querySelectorAll('[data-upload="cont"]').forEach(tile => {
            const url = data.cont[tile.dataset.slot];
            const img = tile.querySelector('.sg-upload__thumb');
            tile.classList.toggle('is-done', !!url);
            img.hidden = !url;
            if (url) img.src = url;
        });
        document.getElementById('contOld').hidden = !data.cont_old.length;
        thumbs.innerHTML = data.cont_old.map((f, i) => `
            <div class="sg-thumb" data-file="${f.file}">
                <img src="${f.url}" alt="Foto container lama ${i + 1}">
                <button type="button" class="sg-thumb__del" aria-label="Hapus foto">${document.querySelector('[data-icon-trash]').innerHTML}</button>
            </div>`).join('');

        const vTile = form.querySelector('[data-upload="video"]');
        vTile.classList.toggle('is-done', !!data.video);
        const link = document.getElementById('videoLink');
        link.hidden = !data.video;
        if (data.video) link.href = data.video;
        syncStepper();
    }

    form.querySelectorAll('[data-upload]').forEach(tile => {
        const kind = tile.dataset.upload;
        const input = tile.querySelector('input[type=file]');
        const bar = tile.querySelector('.sg-progress');
        const hint = tile.querySelector('.sg-upload__hint');
        if (!input) return;   // identitas driver: lewat dialog Foto SIM

        input.addEventListener('change', async () => {
            const file = input.files[0];
            input.value = '';
            if (!file) return;
            clearErrors();
            tile.classList.remove('is-error');

            const wasDone = tile.classList.contains('is-done');
            busy.add(tile);
            try {
                const blob = await prepareMedia(file, kind === 'video', parseInt(tile.dataset.maxSec || '60', 10), tile, hint);

                tile.classList.add('is-uploading');
                tile.classList.remove('is-done');
                bar.hidden = false;
                const fill = bar.firstElementChild;
                fill.style.width = '0%';
                const res = await uploadChunked(form.dataset.uploadUrl, chunkSize, kind, blob, p => {
                    fill.style.width = Math.round(p * 100) + '%';
                    hint.textContent = `Mengunggah · ${Math.round(p * 100)}%`;
                }, tile.dataset.slot ? { slot: tile.dataset.slot } : null);
                hint.textContent = hint.dataset.hint;
                renderMedia(res);
            } catch (err) {
                hint.textContent = '⚠ ' + err.message;
                tile.classList.add('is-error');
                tile.classList.toggle('is-done', wasDone);
            } finally {
                busy.delete(tile);
                tile.classList.remove('is-uploading');
                bar.hidden = true;
            }
        });
    });

    // ---------- Foto SIM driver (dialog #simDlg) ----------
    // Seperti scan ID card: kamera live, SIM diarahkan ke bingkai; begitu gambar diam, frame dikirim ke server untuk dibaca
    // (App\Services\SimOcr). Terbaca (no. SIM + nama / masa berlaku) -> foto dibekukan & data terisi; belum -> coba lagi.
    // Tombol Ambil foto = ambil manual. Tanpa kamera live (HTTP): kamera bawaan / galeri.
    // Security mencocokkan & memperbaiki -> Simpan: foto diunggah & data masuk ke form.
    // Sebelum Simpan tidak ada yang tersimpan; Batal membuang foto.
    const simDlg = document.getElementById('simDlg');
    const idTile = form.querySelector('[data-sim-open]');
    if (simDlg && idTile) {
        const q = sel => simDlg.querySelector(sel);
        const liveBox = q('[data-sim-live]'), video = liveBox.querySelector('video');
        const shotBox = q('[data-sim-shot]'), shotImg = shotBox.querySelector('img');
        const captureBtn = q('[data-sim-capture]'), cameraBtn = q('[data-sim-camera]'), retakeBtn = q('[data-sim-retake]');
        const statusEl = q('[data-sim-status]'), noteEl = q('[data-sim-note]'), saveBtn = q('[data-sim-save]');
        const canLive = window.isSecureContext && !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        // field dialog -> field form
        const FIELDS = { nama: ['simNama', 'gate_driver'], sim_no: ['simNo', 'gate_sim_no'], sim_exp: ['simExp', 'gate_sim_exp'] };
        const dlgInput = key => document.getElementById(FIELDS[key][0]);
        let stream = null, shot = null, shotUrl = null, seq = 0, saving = false;
        // scan otomatis: frame kecil untuk cek gambar diam, jeda antar percobaan baca
        const probe = document.createElement('canvas');
        probe.width = 80;
        probe.height = 60;
        const probeCtx = probe.getContext('2d', { willReadFrequently: true });
        let autoTimer = null, reading = false, prevFrame = null, still = 0, nextTry = 0, tries = 0;

        const setStatus = (msg, kind) => {
            statusEl.textContent = msg || '';
            statusEl.className = 'sg-scan__status' + (kind ? ' is-' + kind : '');
        };
        function fieldError(key, msg) {
            const field = q(`[data-sim-field="${key}"]`);
            const hintEl = field.querySelector('.sg-hint');
            field.classList.toggle('is-error', !!msg);
            hintEl.hidden = !msg;
            hintEl.textContent = msg || '';
        }
        function markAuto(key, on) {
            q(`[data-sim-field="${key}"]`).classList.toggle('is-ocr', on);
            noteEl.hidden = !simDlg.querySelector('.sg-sim__fields .is-ocr');
        }
        function stopLive() {
            clearTimeout(autoTimer);
            autoTimer = null;
            liveBox.classList.remove('is-reading');
            if (stream) stream.getTracks().forEach(t => t.stop());
            stream = null;
            video.srcObject = null;
        }
        // tampilan: kamera (belum ada foto) atau hasil foto
        function showCamera() {
            seq++;
            shot = null;
            shotBox.hidden = true;
            retakeBtn.hidden = true;
            liveBox.hidden = !canLive;
            captureBtn.hidden = !canLive;
            cameraBtn.hidden = canLive;
            saveBtn.disabled = true;
            if (!canLive) {
                // HTTP tanpa izin kamera browser: preview langsung tidak bisa, pakai kamera bawaan tablet
                setStatus('Preview kamera tidak tersedia di koneksi ini (perlu HTTPS / izin kamera Chrome). Tekan Foto SIM untuk membuka kamera tablet.', 'warn');
                return;
            }
            setStatus('Membuka kamera…');
            captureBtn.disabled = true;
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment', width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false })
                .then(async s => {
                    if (simDlg.hidden || shot) { s.getTracks().forEach(t => t.stop()); return; }
                    stream = s;
                    video.srcObject = s;
                    await video.play().catch(() => {});
                    captureBtn.disabled = false;
                    setStatus('Arahkan kamera ke SIM di dalam bingkai, tahan sebentar. Data terbaca otomatis.');
                    prevFrame = null;
                    still = tries = 0;
                    nextTry = Date.now() + 600;
                    autoTick();
                })
                .catch(err => {
                    stopLive();
                    liveBox.hidden = captureBtn.hidden = true;
                    cameraBtn.hidden = false;
                    const denied = err && (err.name === 'NotAllowedError' || err.name === 'SecurityError');
                    setStatus(denied ? 'Izin kamera ditolak. Izinkan kamera untuk situs ini di Chrome, atau pakai tombol Foto SIM.'
                        : 'Kamera tidak bisa dibuka. Pakai tombol Foto SIM.', 'warn');
                });
        }
        // bekukan foto di dialog (kamera dimatikan)
        function showShot(blob) {
            stopLive();
            shot = blob;
            if (shotUrl) URL.revokeObjectURL(shotUrl);
            shotUrl = URL.createObjectURL(blob);
            shotImg.src = shotUrl;
            shotBox.hidden = false;
            liveBox.hidden = captureBtn.hidden = cameraBtn.hidden = true;
            retakeBtn.hidden = false;
            saveBtn.disabled = false;
        }
        // hasil baca -> isian dialog (biru = otomatis); kembalikan jumlah yang terisi
        function fillFields(r) {
            let got = 0;
            Object.keys(FIELDS).forEach(key => {
                if (!r[key]) return;
                dlgInput(key).value = r[key];
                markAuto(key, true);
                fieldError(key, null);
                got++;
            });
            setStatus(got === 3 ? 'Data SIM terbaca. Cocokkan dengan kartu, lalu Simpan.'
                : got ? 'Sebagian data terbaca. Lengkapi yang kosong atau Foto ulang, lalu Simpan.'
                : 'Data SIM tidak terbaca. Isi manual atau Foto ulang lebih dekat.', got === 3 ? null : 'warn');
            return got;
        }
        function readSim(blob) {
            const fd = new FormData();
            fd.append('foto', blob, 'sim.jpg');
            return post(form.dataset.ocrUrl, fd);
        }
        function grabFrame() {
            const c = document.createElement('canvas');
            c.width = video.videoWidth;
            c.height = video.videoHeight;
            c.getContext('2d').drawImage(video, 0, 0);
            return new Promise(resolve => c.toBlob(resolve, 'image/jpeg', 0.92));
        }
        // ambil manual (tombol / kamera bawaan / galeri): langsung dibekukan lalu dibaca
        async function useShot(file) {
            const my = ++seq;
            const blob = await compressImage(file);
            if (my !== seq) return;
            showShot(blob);
            setStatus('Membaca data SIM…');
            try {
                const r = await readSim(blob);
                if (my === seq) fillFields(r);
            } catch (err) {
                if (my === seq) setStatus((err.message || 'SIM tidak bisa dibaca.') + ' Isi data manual.', 'warn');
            }
        }
        // scan otomatis: tiap 250 ms bandingkan frame kecil; diam +-0.75 dtk & cukup terang -> kirim ke server
        function autoTick() {
            autoTimer = setTimeout(async () => {
                if (!stream) return;
                if (!reading && video.readyState >= 2) {
                    probeCtx.drawImage(video, 0, 0, probe.width, probe.height);
                    const px = probeCtx.getImageData(0, 0, probe.width, probe.height).data;
                    const gray = new Uint8Array(px.length / 4);
                    let sum = 0;
                    for (let i = 0; i < gray.length; i++) { gray[i] = (px[i * 4] + px[i * 4 + 1] + px[i * 4 + 2]) / 3; sum += gray[i]; }
                    let diff = 0;
                    if (prevFrame) for (let i = 0; i < gray.length; i++) diff += Math.abs(gray[i] - prevFrame[i]);
                    diff = prevFrame ? diff / gray.length : 99;
                    prevFrame = gray;
                    still = diff < 7 && sum / gray.length > 40 ? still + 1 : 0;
                    if (still >= 3 && Date.now() >= nextTry) await autoRead();
                }
                if (stream) autoTick();
            }, 250);
        }
        async function autoRead() {
            const my = seq;
            reading = true;
            liveBox.classList.add('is-reading');
            setStatus('Membaca SIM… tahan posisi kamera.');
            try {
                const blob = await compressImage(await grabFrame());
                const r = await readSim(blob);
                if (my !== seq || !stream) return;
                if (r.sim_no && (r.nama || r.sim_exp)) {
                    showShot(blob);
                    fillFields(r);
                    return;
                }
                tries++;
                setStatus(tries >= 3
                    ? 'SIM belum terbaca. Dekatkan kamera, hindari pantulan cahaya, atau tekan Ambil foto lalu isi manual.'
                    : 'SIM belum terbaca jelas. Pastikan seluruh kartu di dalam bingkai dan tidak silau.', 'warn');
            } catch (err) {
                if (my === seq && stream) setStatus(err.message || 'SIM tidak bisa dibaca. Coba lagi.', 'warn');
            } finally {
                reading = false;
                liveBox.classList.remove('is-reading');
                still = 0;
                nextTry = Date.now() + 800;
            }
        }
        function openSim(camera = true) {
            // data driver yang sudah ada di form jadi isian awal
            Object.keys(FIELDS).forEach(key => {
                dlgInput(key).value = document.getElementById(FIELDS[key][1]).value;
                markAuto(key, false);
                fieldError(key, null);
            });
            simDlg.hidden = false;
            document.body.classList.add('sg-noscroll');
            if (camera) showCamera();
            q('[data-sim-close]').focus();
        }
        function closeSim() {
            if (saving) return;
            seq++;
            stopLive();
            shot = null;
            if (shotUrl) { URL.revokeObjectURL(shotUrl); shotUrl = null; }
            shotImg.removeAttribute('src');
            simDlg.hidden = true;
            document.body.classList.remove('sg-noscroll');
            idTile.focus();
        }

        idTile.addEventListener('click', () => { if (!busy.has(idTile)) openSim(); });
        idTile.addEventListener('keydown', e => { if ((e.key === 'Enter' || e.key === ' ') && !busy.has(idTile)) { e.preventDefault(); openSim(); } });
        simDlg.querySelectorAll('[data-sim-close]').forEach(b => b.addEventListener('click', closeSim));
        simDlg.addEventListener('click', e => { if (e.target === simDlg) closeSim(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !simDlg.hidden) closeSim(); });
        retakeBtn.addEventListener('click', showCamera);
        // upload foto SIM dari galeri / file (tombol di bawah kotak identitas): langsung ke dialog tanpa kamera
        const fileBtn = form.querySelector('[data-sim-file]');
        if (fileBtn) fileBtn.addEventListener('change', () => {
            const file = fileBtn.files[0];
            fileBtn.value = '';
            if (!file || busy.has(idTile)) return;
            openSim(false);
            useShot(file);
        });
        captureBtn.addEventListener('click', async () => {
            if (!stream || video.readyState < 2) return;
            const b = await grabFrame();
            if (b) useShot(b);
        });
        simDlg.querySelectorAll('input[type=file]').forEach(input => input.addEventListener('change', () => {
            const file = input.files[0];
            input.value = '';
            if (file) useShot(file);
        }));
        Object.keys(FIELDS).forEach(key => dlgInput(key).addEventListener('input', () => {
            markAuto(key, false);
            fieldError(key, null);
        }));
        dlgInput('nama').addEventListener('input', e => upperOnInput(e.target));

        saveBtn.addEventListener('click', async () => {
            if (!shot) return;
            let bad = false;
            Object.keys(FIELDS).forEach(key => {
                const empty = !dlgInput(key).value.trim();
                fieldError(key, empty ? 'Wajib diisi.' : null);
                bad = bad || empty;
            });
            if (bad) { setStatus('Lengkapi data driver dulu.', 'error'); return; }

            saving = true;
            setLoading(saveBtn, true);
            busy.add(idTile);
            try {
                const res = await uploadChunked(form.dataset.uploadUrl, chunkSize, 'id', shot,
                    p => setStatus(`Menyimpan foto · ${Math.round(p * 100)}%`));
                renderMedia(res);
                // preview langsung dari foto di perangkat (tidak menunggu diambil ulang dari server)
                const thumb = idTile.querySelector('.sg-upload__thumb');
                if (thumb.dataset.localUrl) URL.revokeObjectURL(thumb.dataset.localUrl);
                thumb.dataset.localUrl = URL.createObjectURL(shot);
                thumb.src = thumb.dataset.localUrl;
                thumb.hidden = false;
                Object.keys(FIELDS).forEach(key => {
                    const input = document.getElementById(FIELDS[key][1]);
                    input.value = dlgInput(key).value.trim();
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                });
                saving = false;
                closeSim();
            } catch (err) {
                setStatus(err.message || 'Gagal menyimpan. Coba lagi.', 'error');
            } finally {
                saving = false;
                busy.delete(idTile);
                setLoading(saveBtn, false);
            }
        });
    }

    thumbs.addEventListener('click', async e => {
        const btn = e.target.closest('.sg-thumb__del');
        if (!btn) return;
        if (!await sgConfirm('Foto lama akan dihapus dan tidak bisa dikembalikan.', { type: 'danger', title: 'Hapus foto ini?', okText: 'Ya, hapus' })) return;
        const fd = new FormData();
        fd.append('kind', 'cont');
        fd.append('file', btn.closest('.sg-thumb').dataset.file);
        try {
            renderMedia(await post(form.dataset.deleteUrl, fd));
        } catch (err) {
            showErrors({ media: [err.message] }, false);
        }
    });

    // ---------- simpan draft / kedatangan / perubahan ----------
    if (btnDraft) btnDraft.addEventListener('click', async () => {
        clearErrors();
        setLoading(btnDraft, true);
        try {
            await post(form.dataset.draftUrl, fields());
            dirty = false;
            btnDraft.textContent = 'Draft tersimpan';
            setTimeout(() => { btnDraft.textContent = 'Simpan draft'; }, 2000);
        } catch (err) {
            showErrors(err.errors || { media: [err.message] }, !!err.errors);
        } finally {
            setLoading(btnDraft, false);
        }
    });

    btnSubmit.addEventListener('click', async () => {
        clearErrors();
        if (busy.size) {
            showErrors({ media: ['Tunggu upload foto/video selesai.'] }, false);
            return;
        }
        const problems = checklistProblems();
        if (problems.msgs.length) {
            showErrors({ checklist: problems.msgs });
            gateTabs.reveal(problems.first);
            gateTabs.sync();
            problems.first.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        // [judul, pesan, jenis, tombol]
        let ask;
        if (editing) {
            const from = form.dataset.origResult, to = val('gate_result');
            ask = from !== to && to === '0'
                ? ['Ubah hasil menjadi TIDAK LULUS?', 'Container tidak boleh dimuat dan EXIM harus mengganti container ini.', 'warning', 'Ya, tidak lulus']
                : from !== to && to === '1'
                    ? ['Ubah hasil menjadi LULUS?', 'Container boleh dimuat EXIM.', 'question', 'Ya, lulus']
                    : ['Simpan perubahan?', 'Jam tiba dan jam keluar tidak berubah.', 'question', 'Ya, simpan'];
        } else {
            ask = val('gate_result') === '0'
                ? ['Catat kedatangan TIDAK LULUS?', 'Container tidak boleh dimuat. Jam kedatangan diambil saat ini dan tidak bisa diubah.', 'warning', 'Ya, catat']
                : ['Catat kedatangan sekarang?', 'Jam kedatangan diambil saat ini dan tidak bisa diubah.', 'question', 'Ya, catat'];
        }
        if (!await sgConfirm(ask[1], { title: ask[0], type: ask[2], okText: ask[3] })) return;
        setLoading(btnSubmit, true);
        try {
            const res = await post(editing ? form.dataset.updateUrl : form.dataset.storeUrl, fields());
            dirty = false;
            window.location.href = res.redirect;
        } catch (err) {
            showErrors(err.errors || { media: [err.message] }, !!err.errors);
            setLoading(btnSubmit, false);
        }
    });

    window.addEventListener('beforeunload', e => {
        if (dirty || busy.size) { e.preventDefault(); e.returnValue = ''; }
    });
})();
</script>
<template data-icon-trash>@include('menu.exim-export.security.partials.icon', ['name' => 'trash'])</template>
<template data-icon-check>@include('menu.exim-export.security.partials.icon', ['name' => 'check'])</template>
<template data-icon-close>@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</template>
@include('menu.exim-export.security.partials.stuffing-scan')
@include('menu.exim-export.security.partials.realtime')
<script>
// Realtime websocket (window.ShipRT): event "cont" = container berubah (Security Gate HP lain,
// mulai / selesai stuffing, shipment plan) -> daftar container & jumlah filter dimuat ulang tanpa
// reload halaman; event "pack" = carton berubah (scan stuffing) -> ceklis carton container terpilih.
(function () {
    const RT = window.ShipRT;
    const SEL = @json($hasSel ? ['contpk' => (int) $selected->contpk, 'exportpk' => (int) ($selected->exportpk ?? 0)] : null);
    const timers = {};
    const later = (key, fn) => { clearTimeout(timers[key]); timers[key] = setTimeout(fn, 200 + Math.random() * 500); };
    let listBusy = false;

    async function refreshList() {
        if (listBusy) return;
        listBusy = true;
        try {
            const res = await fetch(location.href, { headers: { 'Accept': 'text/html' }, cache: 'no-store' });
            if (!res.ok || res.redirected) return; // sesi habis -> biarkan, halaman berikutnya minta login
            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const cur = document.querySelector('.sg-cards'), next = doc.querySelector('.sg-cards');
            if (cur && next && cur.innerHTML !== next.innerHTML) cur.innerHTML = next.innerHTML;
            const nextCounts = doc.querySelectorAll('.sg-chip__count');
            document.querySelectorAll('.sg-chip__count').forEach((el, i) => {
                if (nextCounts[i] && el.textContent !== nextCounts[i].textContent) el.textContent = nextCounts[i].textContent;
            });
            const badge = document.querySelector('.sg-appbar__badge'), nextBadge = doc.querySelector('.sg-appbar__badge');
            if (badge && nextBadge) badge.textContent = nextBadge.textContent;
        } catch (e) {
        } finally {
            listBusy = false;
        }
    }

    // container yang sedang dibuka diubah di perangkat lain: jangan timpa isian, cukup beri tahu
    function notifyChanged() {
        if (document.getElementById('sgRtChanged')) return;
        const host = document.querySelector('.sg-split__detail .sg-main') || document.querySelector('.sg-split__detail');
        if (!host) return;
        const box = document.createElement('div');
        box.id = 'sgRtChanged';
        box.className = 'sg-alert sg-alert--warn';
        box.setAttribute('role', 'status');
        box.innerHTML = '<div><strong>Data container ini baru diubah di perangkat lain</strong>'
            + 'Muat ulang supaya yang tampil data terbaru. <button type="button" class="sg-btn" style="margin-top:6px">Muat ulang</button></div>';
        box.querySelector('button').addEventListener('click', () => location.reload());
        host.prepend(box);
    }

    RT.on(function (ev, d) {
        const mine = SEL && (!d.exportpk || d.exportpk === SEL.exportpk);
        if (ev === 'pack') {
            if (mine && window.sgCartonsReload) later('ck', window.sgCartonsReload);
            return;
        }
        if (!d.self) later('list', refreshList);
        if (mine && window.sgCartonsReload) later('ck', window.sgCartonsReload); // mulai / selesai stuffing ikut mengunci ceklis
        if (SEL && d.contpk === SEL.contpk && !d.self) notifyChanged();
    });
    // baru tersambung lagi setelah putus: kejar perubahan selama offline
    let wasOn = false;
    RT.onState(function (on) {
        if (on && wasOn) later('list', refreshList);
        wasOn = wasOn || on;
    });
})();
</script>
</body>
</html>
