{{-- Check List Pemeriksaan Kontainer (form kertas) dari data gate in, seal & tanda tangan --}}
@extends('menu.exim-export.security.docs.layout')
@section('content')
    @php
        $ans = ['Y' => 'Ya', 'T' => 'Tidak'];
        $rejected = $s->is_rejected;
        $passed = $s->gate_result !== null && !$rejected;
    @endphp
    <dl class="fields fields--2">
        <dt>Tanggal</dt><span class="colon">:</span><dd>{{ $s->arrived_at->isoFormat('dddd, D MMM YYYY') }}</dd>
        <span class="gap"></span><dt>Jam tiba</dt><span class="colon">:</span><dd>{{ $s->arrived_at->format('H:i') }}</dd>
        <dt>No. container</dt><span class="colon">:</span><dd>{{ $s->contno_fmt ?: '-' }}</dd>
        <span class="gap"></span><dt>Tipe</dt><span class="colon">:</span><dd>{{ $s->type_label }}</dd>
        <dt>No. polisi</dt><span class="colon">:</span><dd>{{ $s->nopol ?: '-' }}</dd>
        <span class="gap"></span><dt>Unit pemuat</dt><span class="colon">:</span><dd>{{ $s->gate_unit ?? '' ?: '-' }}</dd>
        <dt>Perusahaan angkutan</dt><span class="colon">:</span><dd>{{ (($s->gate_transporter ?? '') ?: ($s->fwdnm ?? '')) ?: '-' }}</dd>
        <span class="gap"></span><dt>Buyer</dt><span class="colon">:</span><dd>{{ $s->buyer ?: '-' }}</dd>
        <dt>Nama driver</dt><span class="colon">:</span><dd>{{ $s->gate_driver ?? '' ?: '-' }}</dd>
        <span class="gap"></span><dt>No. SIM</dt><span class="colon">:</span><dd>{{ $s->gate_sim_no ?? '' ?: '-' }}{!! !empty($s->gate_sim_exp) ? ' <span class="sub">s/d ' . e(\Carbon\Carbon::parse($s->gate_sim_exp)->isoFormat('D MMM YYYY')) . '</span>' : '' !!}</dd>
    </dl>

    @foreach ($checklist as $key => $section)
        <h3>{{ $key }}. {{ $section['title'] }}</h3>
        <table>
            <thead><tr><th class="no">No</th><th>Pemeriksaan</th><th class="c">Ya</th><th class="c">Tidak</th><th style="width:30%">Catatan</th></tr></thead>
            <tbody>
                @foreach ($section['groups'] as $group)
                    @if ($group['title'])
                        <tr class="group"><td class="no">{{ $loop->iteration }}</td><td colspan="4">{{ $group['title'] }}</td></tr>
                    @endif
                    @foreach ($group['items'] as $code => [$question, $expect])
                        @php
                            $v = $s->answers[$code]['v'] ?? null;
                            $issue = $v && $v !== $expect;
                        @endphp
                        <tr class="{{ $issue ? 'issue' : '' }}">
                            <td class="no">{{ $group['title'] ? '' : $loop->iteration }}</td>
                            <td>{{ $question }}</td>
                            <td class="c mark">{{ $v === 'Y' ? '✓' : '' }}</td>
                            <td class="c mark">{{ $v === 'T' ? '✓' : '' }}</td>
                            <td>{{ $s->answers[$code]['n'] ?? '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @endforeach

    @unless ($rejected)
        <h3>D. Pemasangan seal (VVTT)</h3>
        <table>
            <thead><tr><th>Langkah</th><th>Keterangan</th><th class="c">✓</th></tr></thead>
            <tbody>
                @foreach ($sealSteps as $step => [$label, $hint])
                    <tr><td>{{ $label }}</td><td>{{ $hint }}</td><td class="c mark">{{ in_array($step, $s->seal_steps, true) ? '✓' : '' }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <dl class="fields">
            <dt>No. seal</dt><span class="colon">:</span><dd>{{ $s->noseal ?: '-' }}</dd>
            @foreach ($sealRoles as $col => $label)
                @php $p = $s->seal_people[$col] ?? null; @endphp
                <dt>{{ $label }}</dt><span class="colon">:</span>
                <dd class="{{ $p ? '' : 'empty' }}">{{ $p->nama ?? '-' }}{!! $p && $p->karid ? ' <span class="sub">NRK ' . e($p->karid) . ($p->depnm ? ' · ' . e($p->depnm) : '') . '</span>' : '' !!}</dd>
            @endforeach
        </dl>
    @endunless

    <h3>Hasil pemeriksaan</h3>
    <div class="result">
        <span><span class="box">{{ $passed ? '✓' : '' }}</span>Lulus untuk pemuatan barang</span>
        <span><span class="box">{{ $rejected ? '✓' : '' }}</span>Tidak lulus</span>
    </div>
    @if ($rejected && $s->gate_result_note)
        <p>Alasan: <strong>{{ $s->gate_result_note }}</strong></p>
    @endif
    @if ($s->issues)
        <p class="sub">{{ count($s->issues) }} temuan, lihat baris yang diberi warna dan catatannya.</p>
    @endif
    {{-- petugas security saat gate in (kolom gatein_guard) --}}
    @if (!empty($s->in_guards))
        <dl class="fields">
            <dt>Petugas security</dt><span class="colon">:</span>
            <dd>
                @foreach ($s->in_guards as $p)
                    {{ $p['nama'] }} <span class="sub">NRK {{ $p['karid'] }}</span>{{ $loop->last ? '' : ',' }}
                @endforeach
            </dd>
        </dl>
    @endif

    <div class="signs">
        @foreach ($signers as $key => $label)
            <div>
                <div class="sig">
                    @if (!empty($s->signs[$key]))
                        <img src="{{ $media($s->signs[$key]) }}" alt="Tanda tangan {{ $label }}">
                    @endif
                </div>
                {{ $label }}
            </div>
        @endforeach
    </div>
    @unless ($s->signs)
        <p class="note">Tanda tangan diambil saat truk dicatat keluar MIF.</p>
    @endunless
@endsection
