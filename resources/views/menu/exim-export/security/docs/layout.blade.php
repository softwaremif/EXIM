{{-- Layout dokumen cetak (A4) dari data security gate. Variabel: $s (container), $title --}}
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ $s->contno_fmt ?: $s->idship }}</title>
    <link rel="shortcut icon" href="{!! asset('public/css/images/morich.gif') !!}">
    <style>
        :root { --ink: #1d1d1b; --muted: #5f5d57; --line: #bdb9ae; --soft: #f3f1ec; --bad: #a32a1d; --paper: #fff; --bg: #e9e7e1; }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--ink); font: 14px/1.45 "Segoe UI", Arial, sans-serif; }
        .toolbar { position: sticky; top: 0; z-index: 2; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; padding: 10px 16px; background: #2b2a27; }
        .toolbar a, .toolbar button { min-height: 40px; padding: 0 16px; border: 1px solid #77746c; border-radius: 8px; background: #3a3935; color: #fff; font: 600 14px/38px inherit; font-family: inherit; text-decoration: none; cursor: pointer; }
        .toolbar a.is-active { background: #fff; color: #1d1d1b; border-color: #fff; }
        .toolbar button { background: #f0c75e; border-color: #f0c75e; color: #1d1d1b; }
        .page { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 14mm 16mm; background: var(--paper); box-shadow: 0 2px 10px rgba(0,0,0,.15); }
        .kop { display: flex; align-items: center; gap: 14px; padding-bottom: 8px; border-bottom: 2px solid var(--ink); }
        .kop img { width: 64px; height: 64px; object-fit: contain; }
        .kop h1 { margin: 0; font: 700 24px/1.1 Arial, sans-serif; letter-spacing: .06em; }
        .kop p { margin: 2px 0 0; font-size: 11px; line-height: 1.35; }
        .doc-title { margin: 22px 0 18px; text-align: center; font: 700 20px/1.2 Georgia, "Times New Roman", serif; letter-spacing: .02em; text-transform: uppercase; }
        .fields { display: grid; grid-template-columns: 170px 12px 1fr; gap: 6px 0; margin: 0 0 14px; }
        .fields dt { color: var(--ink); }
        .fields dt::after { content: ""; }
        .fields .colon { text-align: center; }
        .fields dd { margin: 0; min-height: 21px; border-bottom: 1px solid var(--line); font-weight: 600; }
        .fields dd.empty { color: var(--muted); font-weight: 400; }
        .fields--2 { grid-template-columns: 120px 12px 1fr 24px 100px 12px 1fr; }
        .sub { font-size: 12px; color: var(--muted); font-weight: 400; }
        ol.lines { margin: 0; padding-left: 22px; }
        ol.lines li { min-height: 21px; border-bottom: 1px solid var(--line); font-weight: 600; }
        ol.lines li.blank { font-weight: 400; }
        table { width: 100%; border-collapse: collapse; margin: 0 0 12px; font-size: 12.5px; }
        th, td { padding: 4px 6px; border: 1px solid var(--line); vertical-align: top; text-align: left; }
        th { background: var(--soft); font-weight: 700; }
        td.c, th.c { width: 46px; text-align: center; }
        td.no { width: 28px; text-align: center; color: var(--muted); }
        tr.group td { background: var(--soft); font-weight: 700; }
        tr.issue td { background: #fbeee9; }
        .mark { font-weight: 700; }
        h3 { margin: 16px 0 6px; font-size: 14px; text-transform: uppercase; letter-spacing: .03em; }
        .result { display: flex; gap: 24px; align-items: center; margin: 6px 0 4px; font-weight: 700; }
        .box { display: inline-block; width: 15px; height: 15px; margin-right: 6px; border: 1.5px solid var(--ink); vertical-align: -2px; text-align: center; font-size: 12px; line-height: 12px; }
        .signs { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px 16px; margin-top: 18px; text-align: center; font-size: 12px; break-inside: avoid; }
        .signs > div { min-width: 0; }
        /* gambar tanda tangan (latar putih) lebih pendek dari kotak supaya tidak menutupi garis */
        .signs .sig { display: flex; align-items: flex-end; justify-content: center; height: 70px; padding-bottom: 3px; margin-bottom: 4px; border-bottom: 1px solid var(--ink); }
        .signs img { display: block; max-width: 100%; max-height: 64px; mix-blend-mode: multiply; }
        .note { margin-top: 16px; padding: 8px 10px; border: 1px solid var(--line); background: var(--soft); font-size: 12px; }
        .footer { margin-top: 26px; text-align: center; font: 700 18px/1 Georgia, serif; }
        .gen { margin-top: 18px; font-size: 10.5px; color: var(--muted); text-align: right; }
        tr, h3 { break-inside: avoid; }
        h3 { break-after: avoid; }
        /* layar HP saja (bukan cetak): field 2 kolom jadi 1 kolom -> pasangan label : isi tetap berurutan */
        @media screen and (max-width: 860px) { .page { width: auto; min-height: 0; margin: 0; padding: 16px; box-shadow: none; } .fields, .fields--2 { grid-template-columns: 130px 10px 1fr; } .fields--2 .gap { display: none; } .signs { grid-template-columns: repeat(2, 1fr); } }
        @media print { body { background: #fff; } .toolbar { display: none; } .page { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; } @page { size: A4; margin: 12mm 14mm; } }
    </style>
    @if (request()->routeIs('security.gate.doc'))
        <script src="{{ asset('public/js/security-fullscreen.js') }}?v={{ filemtime(public_path('js/security-fullscreen.js')) }}"></script>
    @endif
</head>
<body>
    @php
        // dibuka dari aplikasi Security Gate (security/gate/...): tetap di dalam aplikasi + tombol kembali
        $fromGate = request()->routeIs('security.gate.doc');
        $docRoute = $fromGate ? 'security.gate.doc' : 'container.doc';
    @endphp
    <nav class="toolbar" aria-label="Dokumen container">
        @if ($fromGate)
            <a href="{{ route('security.gate.index', ['cont' => $s->contpk]) }}">‹ Kembali</a>
        @endif
        @foreach (['checklist' => 'Checklist pemeriksaan', 'stuffing' => 'Berita acara stuffing', 'pengiriman' => 'Dokumen pengiriman'] as $key => $label)
            @if ($key === 'checklist' || !$s->is_rejected)
                <a href="{{ route($docRoute, [$s->contpk, $key]) }}" class="{{ request()->route('doc') === $key ? 'is-active' : '' }}">{{ $label }}</a>
            @endif
        @endforeach
        <button type="button" onclick="window.print()">Cetak</button>
    </nav>
    <main class="page">
        <header class="kop">
            <img src="{{ asset('public/css/images/morich.png') }}" alt="">
            <div>
                <h1>PT MORICH INDO FASHION</h1>
                <p>JL. RAYA KARANGJATI KM. 25, DESA GEMBONGAN - BERGAS, SEMARANG - INDONESIA<br>
                    PHONE : 0298 - 523523 (6 LINES) FAX. : 0298 - 522095 · www.ptmorich.com</p>
            </div>
        </header>
        <h2 class="doc-title">{{ $title }}</h2>
        @yield('content')
        <p class="gen">Dicetak dari sistem Security Gate · {{ now()->isoFormat('D MMM YYYY, HH:mm') }} · {{ $s->idship }} · container {{ $s->contno_fmt ?: '-' }}</p>
    </main>
</body>
</html>
