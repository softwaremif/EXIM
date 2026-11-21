<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Kalender ETA · Security Gate</title>
    <link rel="shortcut icon" href="{!! asset('public/css/images/morich.gif') !!}">
    <link rel="manifest" href="{{ route('security.gate.manifest') }}">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1c1f22" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <script>
        try {
            if (localStorage.getItem('sg-theme') === 'dark') document.documentElement.dataset.theme = 'dark';
            // sidebar ditutup di layar lebar: dipasang sebelum render supaya tidak berkedip
            if (localStorage.getItem('cal-sidebar') === 'closed') document.documentElement.classList.add('cal-side-closed');
            if (localStorage.getItem('cal-detail') === 'closed') document.documentElement.classList.add('cal-detail-closed');
        } catch (e) {}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/css/security-gate.css') }}?v={{ filemtime(public_path('css/security-gate.css')) }}">
    <link rel="stylesheet" href="{{ asset('public/css/security-calendar.css') }}?v={{ filemtime(public_path('css/security-calendar.css')) }}">
    @unless ($embed ?? false)
        <script src="{{ asset('public/js/security-fullscreen.js') }}?v={{ filemtime(public_path('js/security-fullscreen.js')) }}"></script>
    @endunless
</head>
<body>
<div class="sg-screen">
    <header class="sg-appbar">
        {{-- di modal Shipment Plan EXIM (embed) tidak ada tombol kembali ke daftar security --}}
        @unless ($embed ?? false)
            <a class="sg-btn sg-btn--ghost sg-btn--icon" href="{{ route('security.gate.index') }}" aria-label="Kembali ke daftar container" title="Daftar container">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</a>
        @endunless
        <h1 class="sg-appbar__title">
            Kalender ETA
            <span class="sg-appbar__sub">Jadwal kedatangan container</span>
        </h1>
        <span class="sg-appbar__shift">{{ $login }}</span>
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="calRefresh" aria-label="Muat ulang data" title="Muat ulang">@include('menu.exim-export.security.partials.icon', ['name' => 'refresh'])</button>
        <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" id="btnTheme" aria-label="Ganti tema terang/gelap">
            <span data-theme-icon="moon">@include('menu.exim-export.security.partials.icon', ['name' => 'moon'])</span>
            <span data-theme-icon="sun" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'sun'])</span>
        </button>
    </header>

    <div class="cal">
        {{-- sidebar: mini kalender, pencarian, filter, ringkasan; bisa ditutup (tombol sidebar di header) --}}
        <aside class="cal-side" id="calSide" aria-label="Filter kalender">
            <div class="cal-side__head">
                <span>Filter</span>
                <button type="button" class="cal-link" data-side-close>Tutup</button>
            </div>

            <section class="cal-mini" id="calMini" aria-label="Pilih tanggal"></section>

            <section class="cal-side__sec">
                <label class="cal-side__label" for="calQ">Cari</label>
                <input type="search" class="cal-input" id="calQ" placeholder="Container, plat, shipment, buyer, PO, OP, PIC" autocomplete="off">
            </section>

            <section class="cal-side__sec">
                <h2 class="cal-side__label">Status</h2>
                <div class="cal-opts" id="calStatus" role="radiogroup" aria-label="Status"></div>
            </section>

            <section class="cal-side__sec">
                <h2 class="cal-side__label">Jenis muatan</h2>
                <div class="cal-opts" id="calKind" role="radiogroup" aria-label="Jenis muatan"></div>
            </section>

            <section class="cal-side__sec">
                <h2 class="cal-side__label" id="calSumTitle">Ringkasan bulan</h2>
                <dl class="cal-sum" id="calSum"></dl>
            </section>

            <p class="cal-side__foot" id="calUpdated"></p>
        </aside>

        {{-- handle di tepi sidebar: buka / tutup --}}
        <button type="button" class="cal-handle" id="calSideToggle" aria-controls="calSide" aria-expanded="true" aria-label="Sembunyikan sidebar" title="Sembunyikan sidebar">
            <span data-pane-icon="close">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</span>
            <span data-pane-icon="open">@include('menu.exim-export.security.partials.icon', ['name' => 'next'])</span>
        </button>

        <main class="cal-main">
            <div class="cal-toolbar">
                <button type="button" class="cal-tbtn" id="calToday">Hari ini</button>
                <button type="button" class="cal-tbtn cal-tbtn--icon" id="calPrev" aria-label="Sebelumnya">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</button>
                <button type="button" class="cal-tbtn cal-tbtn--icon" id="calNext" aria-label="Berikutnya">@include('menu.exim-export.security.partials.icon', ['name' => 'next'])</button>
                <label class="cal-title" title="Pilih bulan">
                    <span id="calTitle">&nbsp;</span>
                    <input type="month" id="calMonth" aria-label="Pilih bulan">
                </label>
                <span class="cal-active-filter" id="calActive"></span>
                <div class="cal-tabs" role="tablist" aria-label="Tampilan kalender">
                    <button type="button" role="tab" data-view="month">Bulan</button>
                    <button type="button" role="tab" data-view="week">Minggu</button>
                    <button type="button" role="tab" data-view="agenda">Agenda</button>
                </div>
            </div>

            <div class="cal-error" id="calError" role="alert" hidden>
                <span><strong>Data kalender gagal dimuat.</strong> <span id="calErrorText">Periksa koneksi lalu coba lagi.</span></span>
                <button type="button" class="cal-tbtn" id="calRetry">Coba lagi</button>
            </div>

            <div class="cal-view" id="calView" aria-live="polite"></div>
        </main>

        {{-- detail hari terpilih: kolom kanan (layar lebar) / panel bawah (tablet & HP) --}}
        <aside class="cal-detail" id="calDetail" aria-label="Detail hari"></aside>
        {{-- handle di tepi panel detail (layar lebar): buka / tutup --}}
        <button type="button" class="cal-handle cal-handle--right" id="calDetailToggle" aria-controls="calDetail" aria-expanded="true" aria-label="Sembunyikan detail hari" title="Sembunyikan detail hari">
            <span data-pane-icon="close">@include('menu.exim-export.security.partials.icon', ['name' => 'next'])</span>
            <span data-pane-icon="open">@include('menu.exim-export.security.partials.icon', ['name' => 'back'])</span>
        </button>
        <div class="cal-scrim" id="calScrim" hidden></div>
    </div>
</div>

@include('menu.exim-export.security.partials.realtime')
<script>
(function () {
    const DATA_URL = @json(route('security.gate.calendar.data'));
    // dibuka di modal Shipment Plan EXIM: link container ke Detail Shipment, dibuka di halaman utama (bukan di dalam modal)
    const EMBED = @json($embed ?? false);
    const LINK_ATTR = EMBED ? ' target="_top"' : '';
    const OPEN_LABEL = c => EMBED ? 'Lihat detail shipment' : (c.arrived ? 'Lihat data gate' : 'Buka gate in');
    const PAGE_URL = @json(route('security.gate.calendar'));
    const ICON = {
        @foreach (['back', 'next', 'close'] as $ic)
            '{{ $ic }}': `@include('menu.exim-export.security.partials.icon', ['name' => $ic])`,
        @endforeach
    };

    const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const MONTHS_S = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const DAYS_S = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    // status sama dengan daftar gate (GateController::decorate)
    const ST = {
        late: 'Terlambat', today: 'ETA hari ini', upcoming: 'Dijadwalkan', incomplete: 'Belum lengkap',
        arrived: 'Di area MIF', rejected: 'Ditolak', departed: 'Sudah keluar',
    };
    const FILTERS = [
        ['all', 'Semua', null, () => true],
        ['pending', 'Belum tiba', 'upcoming', s => ['today', 'upcoming', 'incomplete'].includes(s)],
        ['late', 'Terlambat', 'late', s => s === 'late'],
        ['arrived', 'Di area MIF', 'arrived', s => s === 'arrived'],
        ['departed', 'Sudah keluar', 'departed', s => s === 'departed'],
        ['rejected', 'Ditolak', 'rejected', s => s === 'rejected'],
    ];
    const KINDS = [['all', 'Semua', () => true], ['fcl', 'Container', c => !c.is_lcl], ['lcl', 'LCL', c => c.is_lcl]];
    const filterFn = key => (FILTERS.find(f => f[0] === key) || FILTERS[0])[3];
    const kindFn = key => (KINDS.find(k => k[0] === key) || KINDS[0])[2];

    // ---------- tanggal (lokal, kunci YYYY-MM-DD) ----------
    const pad = n => String(n).padStart(2, '0');
    const keyOf = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
    const parseKey = k => { const [y, m, d] = k.split('-').map(Number); return new Date(y, m - 1, d); };
    const addDays = (k, n) => { const d = parseKey(k); d.setDate(d.getDate() + n); return keyOf(d); };
    const monthOf = k => k.slice(0, 7);
    const daysBetween = (a, b) => Math.round((parseKey(b) - parseKey(a)) / 86400000);
    const fmtLong = k => { const d = parseKey(k); return `${DAYS[d.getDay()]}, ${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`; };
    const fmtShort = k => { const d = parseKey(k); return `${d.getDate()} ${MONTHS_S[d.getMonth()]}`; };
    const fmtDay = k => { const d = parseKey(k); return `${DAYS_S[d.getDay()]} ${d.getDate()} ${MONTHS_S[d.getMonth()]}`; };
    const fmtStamp = v => v ? `${fmtShort(v.slice(0, 10))}, ${v.slice(11, 16)}` : '';
    const relDay = (k, today) => {
        const n = daysBetween(today, k);
        if (n === 0) return 'hari ini';
        if (n === 1) return 'besok';
        if (n === -1) return 'kemarin';
        return n > 0 ? `${n} hari lagi` : `${-n} hari lalu`;
    };
    const esc = v => String(v == null ? '' : v).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // ---------- state ----------
    const init = @json(['month' => $month, 'day' => $day, 'view' => $view]);
    const state = { month: init.month, day: init.day, view: init.view, filter: 'all', kind: 'all', q: '', data: null };
    const cache = new Map();

    const $ = id => document.getElementById(id);
    const root = document.documentElement;
    const viewEl = $('calView');
    const detailEl = $('calDetail');
    const sideEl = $('calSide');
    const mqSide = window.matchMedia('(min-width: 1024px)');   // sidebar menempel (bukan laci)
    const mqDetail = window.matchMedia('(min-width: 1280px)'); // detail jadi kolom kanan

    // ---------- data ----------
    async function load(month, { force = false, silent = false } = {}) {
        if (!force && cache.has(month)) return cache.get(month);
        if (!silent) viewEl.classList.add('is-loading');
        try {
            const res = await fetch(`${DATA_URL}?month=${month}${EMBED ? '&embed=exim' : ''}`, { headers: { Accept: 'application/json' } });
            // sesi habis -> di-redirect ke halaman login (HTML), bukan JSON
            const isJson = (res.headers.get('content-type') || '').includes('json');
            if (res.status === 419 || res.status === 401 || (res.ok && !isJson)) throw new Error('Sesi login habis, silakan login ulang.');
            if (!res.ok) throw new Error(`Server error (${res.status}).`);
            const json = await res.json();
            cache.set(month, json);
            $('calError').hidden = true;
            return json;
        } catch (e) {
            if (!silent) {
                $('calErrorText').textContent = e.message || 'Periksa koneksi lalu coba lagi.';
                $('calError').hidden = false;
            }
            throw e;
        } finally {
            viewEl.classList.remove('is-loading');
        }
    }

    async function go(month, { force = false } = {}) {
        state.month = month;
        try {
            state.data = await load(month, { force });
        } catch (e) {
            state.data = state.data && state.data.month === month ? state.data : null;
        }
        if (!state.day || monthOf(state.day) !== month) {
            const today = state.data ? state.data.today : keyOf(new Date());
            state.day = monthOf(today) === month ? today : firstBusyDay(month) || `${month}-01`;
        }
        render();
    }

    function firstBusyDay(month) {
        const list = ((state.data && state.data.containers) || []).filter(c => c.eta && monthOf(c.eta) === month).map(c => c.eta).sort();
        return list[0] || null;
    }

    // ---------- filter ----------
    function matchQ(c) {
        if (!state.q) return true;
        return [c.ident, c.contno, c.nopol, c.idship, c.buyer, c.po, c.op, c.fwd, c.ekspedisi, c.noseal, c.vessel, c.pic].join('|').replace(/\s+/g, '').toUpperCase().includes(state.q);
    }
    const all = () => (state.data && state.data.containers) || [];
    const visible = () => all().filter(c => matchQ(c) && kindFn(state.kind)(c) && filterFn(state.filter)(c.status));
    const etaInMonth = list => list.filter(c => c.eta && monthOf(c.eta) === state.month);

    function indexBy(list) {
        const eta = {}, arr = {}, dep = {};
        const push = (map, k, c) => { (map[k] = map[k] || []).push(c); };
        list.forEach(c => {
            if (c.eta) push(eta, c.eta, c);
            if (c.arrived) push(arr, c.arrived.slice(0, 10), c);
            if (c.departed) push(dep, c.departed.slice(0, 10), c);
        });
        const by = f => (a, b) => String(a[f] || '').localeCompare(String(b[f] || ''));
        Object.values(arr).forEach(l => l.sort(by('arrived')));
        Object.values(dep).forEach(l => l.sort(by('departed')));
        return { eta, arr, dep };
    }

    // ---------- potongan tampilan ----------
    const mark = s => `<i class="cal-mk st-${s}" aria-hidden="true"></i>`;
    const stText = s => `<span class="cal-st st-${s}">${mark(s)}${ST[s]}</span>`;
    const identHtml = c => c.ident ? esc(c.ident) : `<span class="cal-muted">${c.is_lcl ? 'plat belum diisi' : 'no. container belum diisi'}</span>`;
    const kindHtml = c => c.is_lcl ? 'LCL' : esc(c.type);

    function diffHtml(c) {
        if (c.diff === null || c.diff === undefined) {
            return c.status === 'late' && c.eta_days !== null ? `<span class="cal-warn">belum tiba, lewat ${-c.eta_days} hari</span>` : '';
        }
        if (c.diff > 0) return `<span class="cal-warn">terlambat ${c.diff} hari</span>`;
        if (c.diff < 0) return `<span>${-c.diff} hari lebih awal</span>`;
        return '<span>tepat waktu</span>';
    }

    // satu container di panel detail: baris judul + tabel data
    function entry(c, ctx) {
        const rows = [
            ['ETA', c.eta ? fmtDay(c.eta) : '<span class="cal-muted">belum diisi</span>'],
            ['Tiba', c.arrived ? `${fmtStamp(c.arrived)} <small>${diffHtml(c)}</small>` : (diffHtml(c) || '<span class="cal-muted">—</span>')],
            ['Keluar', c.departed ? fmtStamp(c.departed) : '<span class="cal-muted">—</span>'],
            ['Shipment', `<span class="sg-mono">${esc(c.idship)}</span>`],
            ['Buyer', esc(c.buyer || '—')],
            ['PO', esc(c.po || '—')],
            ['OP', esc(c.op || '—')],
            ['Forwarder', esc(c.fwd || '—')],
            ['Ekspedisi', esc(c.ekspedisi || '—')],
            ['PIC EXIM', c.pic ? `${esc(c.pic)}${c.pic_email ? ` <small>${esc(c.pic_email)}</small>` : ''}` : '—'],
            c.is_lcl ? (c.contno ? ['No. container', `<span class="sg-mono">${esc(c.contno)}</span>`] : null) : ['Plat truk', c.nopol ? `<span class="sg-mono">${esc(c.nopol)}</span>` : '—'],
            ['Seal', c.noseal ? `<span class="sg-mono">${esc(c.noseal)}</span>` : '—'],
            ['Vessel', esc(c.vessel || '—')],
            ['ETD', c.etd ? fmtDay(c.etd) : '—'],
            ['Closing', c.closing ? fmtStamp(c.closing) : '—'],
        ].filter(Boolean);

        return `
        <article class="cal-entry">
            <header class="cal-entry__head">
                <div>
                    <p class="cal-entry__no">${identHtml(c)}</p>
                    <p class="cal-entry__sub">${kindHtml(c)}${ctx !== 'eta' && c.eta ? ` · ETA ${fmtDay(c.eta)}` : ''}</p>
                </div>
                ${stText(c.status)}
            </header>
            <table class="cal-kv"><tbody>${rows.map(([k, v]) => `<tr><th scope="row">${k}</th><td>${v}</td></tr>`).join('')}</tbody></table>
            <a class="cal-entry__open" href="${esc(c.url)}"${LINK_ATTR}>${OPEN_LABEL(c)} ${ICON.next}</a>
        </article>`;
    }

    // ---------- sidebar ----------
    function renderSide() {
        const d = state.data;
        const base = etaInMonth(all().filter(matchQ));
        const forStatus = base.filter(kindFn(state.kind));
        const forKind = base.filter(c => filterFn(state.filter)(c.status));

        $('calStatus').innerHTML = FILTERS.map(([key, label, st, fn]) => `
            <button type="button" role="radio" aria-checked="${state.filter === key}" class="cal-opt" data-filter="${key}">
                ${st ? mark(st) : '<i class="cal-mk cal-mk--none" aria-hidden="true"></i>'}<span>${label}</span><span class="cal-opt__n">${forStatus.filter(c => fn(c.status)).length}</span>
            </button>`).join('') + `
            <p class="cal-key">${mark('today')}ETA hari ini ${mark('upcoming')}dijadwalkan ${mark('incomplete')}belum lengkap</p>`;

        $('calKind').innerHTML = KINDS.map(([key, label, fn]) => `
            <button type="button" role="radio" aria-checked="${state.kind === key}" class="cal-opt" data-kind="${key}">
                <span>${label}</span><span class="cal-opt__n">${forKind.filter(fn).length}</span>
            </button>`).join('');

        const list = base.filter(kindFn(state.kind));
        const arrived = list.filter(c => c.arrived);
        const sum = [
            ['Container ETA', list.length],
            ['Sudah tiba', arrived.length],
            ['— tepat waktu / lebih awal', arrived.filter(c => c.diff <= 0).length],
            ['— terlambat', arrived.filter(c => c.diff > 0).length],
            ['Belum tiba, lewat ETA', list.filter(c => c.status === 'late').length],
            ['Sudah keluar', list.filter(c => c.status === 'departed').length],
        ];
        $('calSumTitle').textContent = `Ringkasan ${MONTHS[Number(state.month.slice(5)) - 1]}`;
        $('calSum').innerHTML = sum.map(([k, v]) => `<dt>${k}</dt><dd>${v}</dd>`).join('');
        $('calUpdated').textContent = d ? `Data diperbarui ${d.updated}, otomatis tiap 2 menit` : '';

        renderMini();
    }

    function renderMini() {
        const d = state.data;
        const [y, m] = state.month.split('-').map(Number);
        const busy = indexBy(visible()).eta;
        const first = `${state.month}-01`;
        const start = addDays(first, -((parseKey(first).getDay() + 6) % 7));
        let cells = '';
        for (let i = 0; i < 42; i++) {
            const k = addDays(start, i);
            if (i === 35 && monthOf(k) !== state.month) break;
            const cls = [
                monthOf(k) !== state.month ? 'is-out' : '',
                d && k === d.today ? 'is-today' : '',
                k === state.day ? 'is-sel' : '',
                busy[k] ? 'has-ev' : '',
            ].join(' ');
            cells += `<button type="button" class="${cls}" data-day="${k}" aria-label="${fmtLong(k)}${busy[k] ? `, ${busy[k].length} container` : ''}">${parseKey(k).getDate()}</button>`;
        }
        $('calMini').innerHTML = `
            <div class="cal-mini__head">
                <strong>${MONTHS[m - 1]} ${y}</strong>
                <button type="button" class="cal-tbtn cal-tbtn--icon cal-tbtn--sm" data-month-step="-1" aria-label="Bulan sebelumnya">${ICON.back}</button>
                <button type="button" class="cal-tbtn cal-tbtn--icon cal-tbtn--sm" data-month-step="1" aria-label="Bulan berikutnya">${ICON.next}</button>
            </div>
            <div class="cal-mini__grid">${['S', 'S', 'R', 'K', 'J', 'S', 'M'].map(n => `<span>${n}</span>`).join('')}${cells}</div>`;
    }

    // ---------- tampilan bulan ----------
    function renderMonth(idx) {
        const d = state.data;
        const head = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'].map(n => `<div class="cal-grid__dow">${n}</div>`).join('');
        let cells = '';
        for (let k = d.from; k <= d.to; k = addDays(k, 1)) {
            const date = parseKey(k);
            const evs = idx.eta[k] || [];
            const arr = (idx.arr[k] || []).length;
            const dep = (idx.dep[k] || []).length;
            const cls = [
                'cal-cell',
                monthOf(k) !== state.month ? 'is-out' : '',
                k === d.today ? 'is-today' : '',
                k === state.day ? 'is-sel' : '',
                date.getDay() === 0 ? 'is-wkend' : '',   // hanya Minggu diberi latar abu-abu; Sabtu putih (hari kerja)
            ].join(' ');
            const lines = evs.slice(0, 4).map(c => `<span class="cal-line st-${c.status}" title="${esc(`${c.ident || 'belum diisi'} · ${ST[c.status]} · ${c.buyer || ''}`)}">${mark(c.status)}<span>${c.ident ? esc(c.ident) : '<span class="cal-muted">belum diisi</span>'}</span>${c.is_lcl ? '<small>LCL</small>' : ''}</span>`).join('');
            const more = evs.length > 4 ? `<span class="cal-cell__more">${evs.length - 4} lainnya</span>` : '';
            const marks = evs.slice(0, 8).map(c => mark(c.status)).join('');
            const foot = [arr ? `${arr} tiba` : '', dep ? `${dep} keluar` : ''].filter(Boolean).join(' · ');
            cells += `
            <button type="button" class="${cls}" data-day="${k}" aria-label="${fmtLong(k)}: ${evs.length} container ETA${arr ? `, ${arr} tiba` : ''}${dep ? `, ${dep} keluar` : ''}">
                <span class="cal-cell__date">${date.getDate() === 1 ? `${date.getDate()} ${MONTHS_S[date.getMonth()]}` : date.getDate()}</span>
                <span class="cal-cell__lines">${lines}${more}</span>
                <span class="cal-cell__marks">${marks}</span>
                ${foot ? `<span class="cal-cell__foot">${foot}</span>` : ''}
            </button>`;
        }
        return `<div class="cal-grid">${head}${cells}</div>`;
    }

    // ---------- tampilan minggu ----------
    const weekStart = k => addDays(k, -((parseKey(k).getDay() + 6) % 7)); // Senin
    function renderWeek(idx) {
        const d = state.data;
        const start = weekStart(state.day);
        let cols = '';
        for (let i = 0; i < 7; i++) {
            const k = addDays(start, i);
            const evs = idx.eta[k] || [];
            const arr = (idx.arr[k] || []).length;
            const dep = (idx.dep[k] || []).length;
            cols += `
            <section class="cal-wk ${k === d.today ? 'is-today' : ''} ${k === state.day ? 'is-sel' : ''}">
                <button type="button" class="cal-wk__head" data-day="${k}">
                    <span class="cal-wk__dow">${DAYS[parseKey(k).getDay()]}</span>
                    <span class="cal-wk__date">${parseKey(k).getDate()}</span>
                    <span class="cal-wk__meta">${evs.length} ETA${arr ? ` · ${arr} tiba` : ''}${dep ? ` · ${dep} keluar` : ''}</span>
                </button>
                ${evs.length ? evs.map(c => `
                    <a class="cal-wk__item" href="${esc(c.url)}"${LINK_ATTR}>
                        <span class="cal-wk__no">${mark(c.status)}${identHtml(c)}</span>
                        <span class="cal-wk__sub">${kindHtml(c)} · <span class="sg-mono">${esc(c.idship)}</span></span>
                        <span class="cal-wk__sub">${esc(c.buyer || '—')}</span>
                        <span class="cal-wk__sub">${ST[c.status]}${c.arrived ? `, tiba ${c.arrived.slice(11, 16)}` : ''}${c.departed ? `, keluar ${c.departed.slice(11, 16)}` : ''}</span>
                    </a>`).join('') : '<p class="cal-wk__empty">—</p>'}
            </section>`;
        }
        return `<div class="cal-week">${cols}</div>`;
    }

    // ---------- tampilan agenda ----------
    function renderAgenda(idx) {
        const d = state.data;
        const days = Object.keys(idx.eta).filter(k => monthOf(k) === state.month).sort();
        if (!days.length) {
            return `<p class="cal-none">Tidak ada container dengan ETA di ${MONTHS[Number(state.month.slice(5)) - 1]} ${state.month.slice(0, 4)} yang cocok dengan filter.</p>`;
        }
        return `
        <table class="cal-ag">
            <thead><tr><th>Tanggal ETA</th><th>Container</th><th>Jenis</th><th>Shipment</th><th>Buyer</th><th>Forwarder</th><th>Tiba</th><th>Keluar</th><th>Status</th></tr></thead>
            ${days.map(k => `
            <tbody class="${k === d.today ? 'is-today' : ''} ${k === state.day ? 'is-sel' : ''}">
                ${idx.eta[k].map((c, i) => `
                <tr>
                    ${i === 0 ? `<th scope="rowgroup" rowspan="${idx.eta[k].length}"><button type="button" class="cal-ag__date" data-day="${k}">${fmtDay(k)}<small>${relDay(k, d.today)}</small></button></th>` : ''}
                    <td><a class="cal-ag__no" href="${esc(c.url)}"${LINK_ATTR}>${identHtml(c)}</a></td>
                    <td>${kindHtml(c)}</td>
                    <td class="sg-mono">${esc(c.idship)}</td>
                    <td>${esc(c.buyer || '—')}</td>
                    <td>${esc(c.fwd || '—')}</td>
                    <td>${c.arrived ? `${c.arrived.slice(0, 10) === k ? '' : fmtShort(c.arrived.slice(0, 10)) + ', '}${c.arrived.slice(11, 16)}` : '—'}</td>
                    <td>${c.departed ? `${c.departed.slice(0, 10) === k ? '' : fmtShort(c.departed.slice(0, 10)) + ', '}${c.departed.slice(11, 16)}` : '—'}</td>
                    <td>${stText(c.status)}</td>
                </tr>`).join('')}
            </tbody>`).join('')}
        </table>`;
    }

    // ---------- panel detail hari ----------
    function renderDetail(idx) {
        const d = state.data;
        const k = state.day;
        if (!d || !k) { detailEl.innerHTML = ''; return; }
        const eta = idx.eta[k] || [];
        const arr = idx.arr[k] || [];
        const dep = idx.dep[k] || [];
        const late = eta.filter(c => c.status === 'late').length;
        // entri ETA sudah memuat jam tiba & keluar; bagian tiba / keluar hanya untuk ETA di hari lain
        const arrOther = arr.filter(c => c.eta !== k);
        const depOther = dep.filter(c => c.eta !== k && !arrOther.includes(c));
        const section = (title, list, ctx) => `
            <section class="cal-d__sec">
                <h3>${title} <span>${list.length}</span></h3>
                ${list.map(c => entry(c, ctx)).join('')}
            </section>`;

        detailEl.innerHTML = `
            <header class="cal-d__head">
                <button type="button" class="cal-tbtn cal-tbtn--icon" data-step="-1" aria-label="Hari sebelumnya">${ICON.back}</button>
                <div class="cal-d__title">
                    <h2>${fmtLong(k)}</h2>
                    <p>${relDay(k, d.today)} · ${eta.length} ETA · ${arr.length} tiba · ${dep.length} keluar${late ? ` · <span class="cal-warn">${late} terlambat</span>` : ''}</p>
                </div>
                <button type="button" class="cal-tbtn cal-tbtn--icon" data-step="1" aria-label="Hari berikutnya">${ICON.next}</button>
                <button type="button" class="cal-tbtn cal-tbtn--icon cal-d__close" data-close aria-label="Tutup">${ICON.close}</button>
            </header>
            <div class="cal-d__body">
                ${eta.length ? section('Jadwal ETA', eta, 'eta') : '<p class="cal-none">Tidak ada container dengan ETA di tanggal ini.</p>'}
                ${arrOther.length ? section('Tiba hari ini, ETA hari lain', arrOther, 'arr') : ''}
                ${depOther.length ? section('Keluar hari ini, ETA hari lain', depOther, 'dep') : ''}
            </div>`;
    }

    // ---------- render utama ----------
    function render() {
        const [y, m] = state.month.split('-').map(Number);
        if (state.view === 'week') {
            const s = weekStart(state.day), e = addDays(s, 6);
            $('calTitle').textContent = monthOf(s) === monthOf(e)
                ? `${parseKey(s).getDate()}–${parseKey(e).getDate()} ${MONTHS[parseKey(e).getMonth()]} ${parseKey(e).getFullYear()}`
                : `${fmtShort(s)} – ${fmtShort(e)} ${parseKey(e).getFullYear()}`;
        } else {
            $('calTitle').textContent = `${MONTHS[m - 1]} ${y}`;
        }
        $('calMonth').value = state.month;
        document.querySelectorAll('.cal-tabs [data-view]').forEach(b => b.setAttribute('aria-selected', String(b.dataset.view === state.view)));
        $('calPrev').setAttribute('aria-label', state.view === 'week' ? 'Minggu sebelumnya' : 'Bulan sebelumnya');
        $('calNext').setAttribute('aria-label', state.view === 'week' ? 'Minggu berikutnya' : 'Bulan berikutnya');

        // filter aktif tetap terlihat walau sidebar ditutup
        const active = [
            state.filter !== 'all' ? FILTERS.find(f => f[0] === state.filter)[1] : '',
            state.kind !== 'all' ? KINDS.find(f => f[0] === state.kind)[1] : '',
            state.q ? `"${$('calQ').value.trim()}"` : '',
        ].filter(Boolean);
        $('calActive').innerHTML = active.length ? `Filter: ${esc(active.join(', '))} <button type="button" class="cal-link" data-reset>hapus</button>` : '';

        renderSide();
        const idx = indexBy(visible());
        viewEl.dataset.view = state.view;
        viewEl.innerHTML = !state.data ? '<p class="cal-none">Memuat kalender…</p>'
            : state.view === 'week' ? renderWeek(idx)
            : state.view === 'agenda' ? renderAgenda(idx)
            : renderMonth(idx);
        renderDetail(idx);
        syncUrl();
    }

    function syncUrl() {
        const p = new URLSearchParams({ month: state.month, view: state.view });
        if (state.day) p.set('day', state.day);
        if (EMBED) p.set('embed', 'exim');
        history.replaceState(null, '', `${PAGE_URL}?${p}`);
    }

    // ---------- sidebar & panel ----------
    function sideOpen() {
        return mqSide.matches ? !root.classList.contains('cal-side-closed') : sideEl.classList.contains('is-open');
    }
    function setSide(open) {
        if (mqSide.matches) {
            root.classList.toggle('cal-side-closed', !open);
            try { localStorage.setItem('cal-sidebar', open ? 'open' : 'closed'); } catch (e) {}
        } else {
            sideEl.classList.toggle('is-open', open);
        }
        syncHandle();
        syncScrim();
    }
    function syncHandle() {
        const open = sideOpen();
        const btn = $('calSideToggle');
        btn.classList.toggle('is-open', open);
        btn.setAttribute('aria-expanded', String(open));
        btn.setAttribute('aria-label', open ? 'Sembunyikan sidebar' : 'Tampilkan sidebar');
        btn.title = open ? 'Sembunyikan sidebar' : 'Tampilkan sidebar';
    }
    // panel detail sebagai kolom kanan (layar lebar) bisa ditutup; disimpan per perangkat
    const detailColOpen = () => !root.classList.contains('cal-detail-closed');
    function setDetailCol(open) {
        root.classList.toggle('cal-detail-closed', !open);
        try { localStorage.setItem('cal-detail', open ? 'open' : 'closed'); } catch (e) {}
        syncDetailHandle();
    }
    function syncDetailHandle() {
        const open = detailColOpen();
        const btn = $('calDetailToggle');
        btn.classList.toggle('is-open', open);
        btn.setAttribute('aria-expanded', String(open));
        btn.setAttribute('aria-label', open ? 'Sembunyikan detail hari' : 'Tampilkan detail hari');
        btn.title = open ? 'Sembunyikan detail hari' : 'Tampilkan detail hari';
    }
    function setSheet(open) {
        detailEl.classList.toggle('is-open', open && !mqDetail.matches);
        if (open) detailEl.scrollTop = 0;
        syncScrim();
    }
    function syncScrim() {
        const overlay = (!mqSide.matches && sideEl.classList.contains('is-open')) || (!mqDetail.matches && detailEl.classList.contains('is-open'));
        $('calScrim').hidden = !overlay;
        document.body.classList.toggle('cal-lock', overlay);
    }
    const closeOverlays = () => { sideEl.classList.remove('is-open'); detailEl.classList.remove('is-open'); syncHandle(); syncScrim(); };

    // ---------- interaksi ----------
    async function pick(k, { open = true } = {}) {
        state.day = k;
        if (monthOf(k) !== state.month) await go(monthOf(k)); else render();
        if (!mqSide.matches) { sideEl.classList.remove('is-open'); syncHandle(); }
        setSheet(open);
        // klik tanggal saat panel detail ditutup: buka lagi
        if (open && mqDetail.matches && !detailColOpen()) setDetailCol(true);
        if (mqDetail.matches) detailEl.scrollTop = 0;
    }

    function monthStep(dir) {
        const d = parseKey(state.month + '-01');
        d.setMonth(d.getMonth() + dir);
        state.day = null;
        return go(keyOf(d).slice(0, 7));
    }
    const shift = dir => state.view === 'week' ? pick(addDays(state.day, 7 * dir), { open: false }) : monthStep(dir);

    $('calPrev').addEventListener('click', () => shift(-1));
    $('calNext').addEventListener('click', () => shift(1));
    $('calToday').addEventListener('click', () => pick(state.data ? state.data.today : keyOf(new Date()), { open: false }));
    $('calMonth').addEventListener('change', e => {
        if (!/^\d{4}-\d{2}$/.test(e.target.value)) return;
        state.day = null;
        go(e.target.value);
    });
    $('calSideToggle').addEventListener('click', () => setSide(!sideOpen()));
    $('calDetailToggle').addEventListener('click', () => setDetailCol(!detailColOpen()));
    document.querySelectorAll('.cal-tabs [data-view]').forEach(b => b.addEventListener('click', () => { state.view = b.dataset.view; render(); }));

    document.addEventListener('click', e => {
        const t = e.target;
        const f = t.closest('[data-filter]');
        if (f) { state.filter = f.dataset.filter; render(); return; }
        const kd = t.closest('[data-kind]');
        if (kd) { state.kind = kd.dataset.kind; render(); return; }
        const ms = t.closest('[data-month-step]');
        if (ms) { monthStep(Number(ms.dataset.monthStep)); return; }
        const day = t.closest('[data-day]');
        if (day) { pick(day.dataset.day); return; }
        const step = t.closest('[data-step]');
        if (step) { pick(addDays(state.day, Number(step.dataset.step)), { open: !mqDetail.matches }); return; }
        if (t.closest('[data-reset]')) { state.filter = 'all'; state.kind = 'all'; state.q = ''; $('calQ').value = ''; render(); return; }
        if (t.closest('[data-side-close]')) { setSide(false); return; }
        if (t.closest('[data-close]') || t.id === 'calScrim') closeOverlays();
    });

    let qTimer;
    $('calQ').addEventListener('input', e => {
        clearTimeout(qTimer);
        qTimer = setTimeout(() => { state.q = e.target.value.replace(/\s+/g, '').toUpperCase(); render(); }, 200);
    });

    // keyboard: panah kiri/kanan = hari, atas/bawah = minggu, PageUp/PageDown = bulan, Esc = tutup panel
    document.addEventListener('keydown', e => {
        if (e.target.closest('input, textarea, select') || e.altKey || e.ctrlKey || e.metaKey) return;
        const moves = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 };
        if (e.key in moves && state.day) { e.preventDefault(); pick(addDays(state.day, moves[e.key]), { open: false }); }
        else if (e.key === 'PageUp') { e.preventDefault(); shift(-1); }
        else if (e.key === 'PageDown') { e.preventDefault(); shift(1); }
        else if (e.key === 'Escape') closeOverlays();
    });

    const onMq = (mq, fn) => (mq.addEventListener ? mq.addEventListener('change', fn) : mq.addListener(fn));
    onMq(mqSide, () => { sideEl.classList.remove('is-open'); syncHandle(); syncScrim(); });
    onMq(mqDetail, () => setSheet(false));
    syncHandle();
    syncDetailHandle();

    const refresh = async () => {
        try {
            state.data = await load(state.month, { force: true, silent: true });
            render();
        } catch (e) {}
    };
    $('calRefresh').addEventListener('click', () => { cache.clear(); go(state.month, { force: true }); });
    $('calRetry').addEventListener('click', () => go(state.month, { force: true }));
    // data diperbarui otomatis tiap 2 menit selama halaman terlihat
    setInterval(() => { if (document.visibilityState === 'visible') refresh(); }, 120000);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') refresh(); });
    // realtime websocket: container / ETA berubah di EXIM -> kalender langsung diperbarui
    let rtTimer = null;
    window.ShipRT.on(ev => {
        if (ev !== 'cont') return;
        clearTimeout(rtTimer);
        rtTimer = setTimeout(refresh, 300 + Math.random() * 700);
    });

    // ---------- tema ----------
    const themeBtn = $('btnTheme');
    const syncThemeIcon = () => {
        const dark = root.dataset.theme === 'dark';
        themeBtn.querySelector('[data-theme-icon="moon"]').hidden = dark;
        themeBtn.querySelector('[data-theme-icon="sun"]').hidden = !dark;
    };
    themeBtn.addEventListener('click', () => {
        const dark = root.dataset.theme !== 'dark';
        if (dark) root.dataset.theme = 'dark'; else delete root.dataset.theme;
        try { localStorage.setItem('sg-theme', dark ? 'dark' : 'light'); } catch (e) {}
        syncThemeIcon();
    });
    syncThemeIcon();

    go(state.month);
})();
</script>
</body>
</html>
