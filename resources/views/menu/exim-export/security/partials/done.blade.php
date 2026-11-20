@php
    $s = $selected;
    $media = fn($f) => $f ? asset('public/upload/gate/' . $s->contpk . '/' . $f) : null;
    $deltaClass = '';
    $deltaText = null;
    if ($s->eta) {
        $days = $s->eta->diffInDays($s->arrived_at->copy()->startOfDay(), false);
        if ($days > 0) {
            [$deltaClass, $deltaText] = ['is-late', $days . ' hari setelah ETA ' . $s->eta->isoFormat('D MMM')];
        } elseif ($days < 0) {
            [$deltaClass, $deltaText] = ['is-ontime', abs($days) . ' hari sebelum ETA ' . $s->eta->isoFormat('D MMM')];
        } else {
            [$deltaClass, $deltaText] = ['is-ontime', 'Sesuai ETA hari ini'];
        }
    }
    $rejected = $ready && $s->gate_result !== null && (int) $s->gate_result === 0;
    $canOut = $ready && !$s->departed_at;
    // data muat (berita acara stuffing & dokumen pengiriman) hanya untuk container lulus
    $withStuffing = $stuffReady && !$rejected;
    $withOutMedia = $outReady && !$rejected;
    $outLocked = !$signReady || (!$rejected && (!$stuffReady || !$outReady));
    $stay = null;
    if ($s->departed_at) {
        $mins = $s->arrived_at->diffInMinutes($s->departed_at);
        $stay = ($mins >= 60 ? intdiv($mins, 60) . ' jam ' : '') . ($mins % 60) . ' menit di area';
    }
    // kode => [pertanyaan, jawaban aman, bagian, kelompok]
    $items = [];
    foreach ($checklist as $key => $section) {
        foreach ($section['groups'] as $group) {
            foreach ($group['items'] as $code => [$question, $expect]) {
                $items[$code] = [$question, $expect, $key . '. ' . $section['title'], $group['title']];
            }
        }
    }
    $answerText = ['Y' => 'Ya', 'T' => 'Tidak'];
    // dokumentasi: foto container keluar di tab Gate out, sisanya (selesai stuffing s/d seal) di tab Muat & seal
    $exitKinds = array_intersect_key($outKinds, array_flip($outExitKinds));
    $loadKinds = array_diff_key($outKinds, $exitKinds);
@endphp
{{-- tahap container sebagai tab (sama seperti form gate in); satu tahap bisa terdiri dari beberapa elemen [data-panel] --}}
@php
    // container ditolak tidak dimuat & tidak di-seal: tanpa tab Seal
    $tabs = ['in' => ['Gate in', 'is-done'], 'load' => [$rejected ? 'Ditolak' : 'Muat', $s->departed_at ? 'is-done' : 'is-current']]
        + ($rejected ? [] : ['seal' => ['Seal', $s->departed_at ? 'is-done' : '']])
        + ['out' => ['Gate out', $s->departed_at ? 'is-done' : '']];
@endphp
<ol class="sg-stepper" id="doneStepper" role="tablist" aria-label="Tahap container">
    @foreach ($tabs as $tab => [$label, $state])
        <li class="sg-step {{ $state }}" data-tab="{{ $tab }}">
            <a class="sg-step__label" href="#panel-{{ $tab }}" role="tab" aria-controls="panel-{{ $tab }}">{{ $loop->iteration }}. {{ $label }}</a>
        </li>
    @endforeach
</ol>
<div class="sg-main" id="doneMain" data-start-tab="{{ $s->departed_at ? 'in' : 'load' }}">
    @unless ($ready)
        <div class="sg-alert sg-alert--warn" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
            <div><strong>Edit & catat keluar belum bisa dipakai</strong>Struktur database belum lengkap. Hubungi IT.</div>
        </div>
    @endunless
    @if (request('saved'))
        <div class="sg-alert sg-alert--ok" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'check'])
            <div><strong>Kedatangan tersimpan</strong>Jam kedatangan sudah tercatat di shipment plan {{ $s->idship }}.</div>
        </div>
    @endif
    @if (request('edited'))
        <div class="sg-alert sg-alert--ok" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'check'])
            <div><strong>Perubahan tersimpan</strong>Data container sudah diperbarui. Jam tiba dan jam keluar tidak berubah.</div>
        </div>
    @endif
    @if (request('out'))
        <div class="sg-alert sg-alert--ok" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'check'])
            <div><strong>Keluar tersimpan</strong>Jam keluar MIF sudah tercatat.</div>
        </div>
    @endif
    @if ($rejected)
        <div class="sg-alert sg-alert--danger" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
            <div><strong>Tidak lulus pemeriksaan</strong>Container tidak boleh dimuat.{{ $s->gate_result_note ? ' Alasan: ' . $s->gate_result_note : '' }} Menunggu container pengganti dari EXIM.</div>
        </div>
    @endif
    @if ($canOut)
        {{-- di luar tab supaya error catat keluar selalu terlihat --}}
        <div class="sg-alert sg-alert--danger" id="outAlert" role="alert" hidden>
            @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
            <div><strong>Data belum lengkap</strong><ul id="outAlertList"></ul></div>
        </div>
    @endif

    {{-- tab 1: gate in --}}
    <div class="sg-panel" id="panel-in" data-panel="in" role="tabpanel">
    <div class="sg-arrival">
        <span class="sg-arrival__label">Actual arrival</span>
        <span class="sg-arrival__time">{{ $s->arrived_at->format('H:i') }}</span>
        <span class="sg-arrival__date">{{ $s->arrived_at->isoFormat('dddd, D MMM YYYY') }}</span>
        @if ($deltaText)
            <span class="sg-arrival__delta {{ $deltaClass }}">@include('menu.exim-export.security.partials.icon', ['name' => 'clock']){{ $deltaText }}</span>
        @endif
        @if ($s->departed_at)
            <div class="sg-arrival__out">
                <span class="sg-arrival__label">Keluar MIF</span>
                <span class="sg-arrival__time sg-arrival__time--sm">{{ $s->departed_at->format('H:i') }}</span>
                <span class="sg-arrival__date">{{ $s->departed_at->isoFormat('dddd, D MMM YYYY') }} · {{ $stay }}</span>
            </div>
        @endif
    </div>

    <section class="sg-section">
        <div class="sg-section__head">
            <h2 class="sg-section__title">Ringkasan</h2>
            @if ($ready)
                <a class="sg-btn sg-btn--sm" href="{{ route('security.gate.index', array_filter(['filter' => $filter, 'q' => $q, 'cont' => $s->contpk, 'edit' => 1])) }}">
                    @include('menu.exim-export.security.partials.icon', ['name' => 'edit']) Edit data
                </a>
            @endif
        </div>
        @if ($s->edited_at)
            <p class="sg-edited">Terakhir diubah {{ $s->edited_at->isoFormat('D MMM YYYY, HH:mm') }}{{ $s->editor ? ' oleh ' . $s->editor : '' }}</p>
        @endif
        <dl class="sg-card__meta" style="margin:0">
            <dt>Jenis</dt><dd><span class="sg-kind {{ $s->is_lcl ? 'sg-kind--lcl' : '' }}">{{ $s->is_lcl ? 'LCL' : 'Container' }}</span> {{ $s->is_lcl ? '' : $s->type_label }}</dd>
            @if (!$s->is_lcl || $s->contno_fmt)
                <dt>Container</dt><dd class="sg-mono">{{ $s->contno_fmt ?: '-' }}</dd>
            @endif
            <dt>No. seal</dt><dd class="sg-mono">{{ $s->noseal ?: 'Belum diisi EXIM' }}</dd>
            <dt>Shipment</dt><dd class="sg-mono">{{ $s->idship }}</dd>
            <dt>Buyer</dt><dd>{{ $s->buyer ?: '-' }}</dd>
            <dt>Plat</dt><dd class="sg-mono">{{ $s->nopol ?: '-' }}</dd>
            @if ($ready)
                <dt>Angkutan</dt><dd>{{ ($s->gate_transporter ?: $s->fwdnm) ?: '-' }}</dd>
                <dt>Ekspedisi</dt><dd>{{ ($s->ekspedisinm ?? null) ?: '-' }}</dd>
                <dt>Unit pemuat</dt><dd>{{ $s->gate_unit ?: '-' }}</dd>
            @endif
            <dt>Driver</dt><dd>{{ ($s->gate_driver ?? null) ?: '-' }}{{ ($s->gate_driver_hp ?? null) ? ' · ' . $s->gate_driver_hp : '' }}</dd>
            @if ($ready)
                <dt>SIM</dt>
                <dd>
                    <span class="sg-mono">{{ $s->gate_sim_no ?: '-' }}</span>
                    @if ($s->gate_sim_exp)
                        · s/d {{ \Carbon\Carbon::parse($s->gate_sim_exp)->isoFormat('D MMM YYYY') }}
                    @endif
                </dd>
                <dt>Identitas</dt>
                <dd data-gallery>
                    @if ($s->gate_foto_id)
                        <a class="sg-thumb sg-thumb--id" href="{{ $media($s->gate_foto_id) }}" target="_blank" rel="noopener" data-lightbox><img src="{{ $media($s->gate_foto_id) }}" alt="Foto identitas driver" loading="lazy"></a>
                    @else
                        -
                    @endif
                </dd>
            @endif
            @if ($inGuardsReady ?? false)
                <dt>Petugas security</dt>
                <dd>
                    @if ($s->in_guards)
                        <ol class="sg-people__view">
                            @foreach ($s->in_guards as $p)
                                <li>{{ $p['nama'] }} <span class="sg-person__sub">{{ \App\Http\Controllers\Security\GateController::personMeta($p) }}</span></li>
                            @endforeach
                        </ol>
                    @else
                        -
                    @endif
                </dd>
            @endif
            @if ($ready)
                <dt>Bukti</dt>
                @php $sides = count(array_filter(array_intersect_key($s->cont_photos, $contSlots))); @endphp
                <dd>Container {{ $s->cont_photos['lama'] && !$sides ? count($s->cont_photos['lama']) . ' foto' : $sides . '/' . count($contSlots) . ' sisi' }} · Video {{ $s->gate_video ? '✓' : '–' }}</dd>
            @endif
        </dl>
        <div class="sg-docs">
            <span class="sg-label">Dokumen</span>
            <div class="sg-row">
                <a class="sg-btn sg-btn--sm" href="{{ route('security.gate.doc', [$s->contpk, 'checklist']) }}">Checklist pemeriksaan</a>
                @unless ($rejected)
                    <a class="sg-btn sg-btn--sm" href="{{ route('security.gate.doc', [$s->contpk, 'stuffing']) }}">Berita acara stuffing</a>
                    <a class="sg-btn sg-btn--sm" href="{{ route('security.gate.doc', [$s->contpk, 'pengiriman']) }}">Dokumen pengiriman</a>
                @endunless
            </div>
        </div>
    </section>

    @if ($s->answers)
        <section class="sg-section">
            <h2 class="sg-section__title">Hasil pemeriksaan</h2>
            <div class="sg-result {{ $rejected ? 'sg-result--fail' : 'sg-result--ok' }}">
                @include('menu.exim-export.security.partials.icon', ['name' => $rejected ? 'warn' : 'check'])
                <span>{{ $rejected ? 'Tidak lulus' : 'Lulus untuk pemuatan barang' }}</span>
                <span class="sg-result__meta">{{ count($s->answers) }} item · {{ count($s->issues) ?: 'tanpa' }} temuan</span>
            </div>
            @if ($s->issues)
                <ul class="sg-findings">
                    @foreach ($s->issues as $code)
                        <li>
                            <span class="sg-findings__where">{{ $items[$code][2] }}{{ $items[$code][3] ? ' · ' . $items[$code][3] : '' }}</span>
                            <span>{{ $items[$code][0] }} <strong>{{ $answerText[$s->answers[$code]['v']] }}</strong></span>
                            @if (!empty($s->answers[$code]['n']))
                                <span class="sg-findings__note">“{{ $s->answers[$code]['n'] }}”</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
            <details class="sg-details">
                <summary>Lihat semua jawaban</summary>
                <ul class="sg-answers">
                    @foreach ($items as $code => [$question, $expect])
                        @php $v = $s->answers[$code]['v'] ?? null; @endphp
                        <li class="{{ $v && $v !== $expect ? 'is-issue' : '' }}">
                            <span>{{ $question }}</span>
                            <strong>{{ $v ? $answerText[$v] : '–' }}</strong>
                        </li>
                    @endforeach
                </ul>
            </details>
        </section>
    @endif

    @if ($ready && ($s->fotos || $s->gate_video))
        <section class="sg-section">
            <h2 class="sg-section__title">Foto &amp; video container</h2>
            <div class="sg-orow__pics sg-orow__pics--labeled" data-gallery>
                @foreach ($contSlots as $slot => [$label])
                    @if ($s->cont_photos[$slot])
                        <a class="sg-thumb" href="{{ $media($s->cont_photos[$slot]) }}" target="_blank" rel="noopener" data-lightbox>
                            <img src="{{ $media($s->cont_photos[$slot]) }}" alt="{{ $label }}" loading="lazy">
                            <span class="sg-thumb__label">{{ $label }}</span>
                        </a>
                    @endif
                @endforeach
                @foreach ($s->cont_photos['lama'] as $f)
                    <a class="sg-thumb" href="{{ $media($f) }}" target="_blank" rel="noopener" data-lightbox><img src="{{ $media($f) }}" alt="Foto container {{ $loop->iteration }}" loading="lazy"></a>
                @endforeach
            </div>
            @if ($s->gate_video)
                <video class="sg-orow__player" src="{{ $media($s->gate_video) }}" controls preload="metadata"></video>
            @endif
        </section>
    @endif
    </div>

    {{-- tab 2: muat -- lokasi stuffing (factory & pintu), lalu ceklis carton masuk container (data di Packing List) --}}
    @if (!$rejected && !empty($s->stuffing))
        @include('menu.exim-export.security.partials.door')
    @endif
    @if (!$rejected)
        @include('menu.exim-export.security.partials.cartons', ['panel' => 'load'])
    @endif

    @if ($s->departed_at)
    <div class="sg-panel" id="panel-load" data-panel="load" role="tabpanel">
    @if ($rejected)
        <p class="sg-hint" style="margin:0">Container tidak lulus pemeriksaan, truk keluar tanpa pemuatan.</p>
    @endif

    @if ($withStuffing)
        @include('menu.exim-export.security.partials.stuffing-view')
    @endif
    @unless ($rejected)
    </div>

    {{-- tab 3: seal (selesai stuffing, dokumentasi, tanda tangan); container ditolak: tanda tangan di tab Ditolak --}}
    <div class="sg-panel" id="panel-seal" data-panel="seal" role="tabpanel">
    @endunless

    @if (array_filter($s->seal_people))
        <section class="sg-section">
            <h2 class="sg-section__title">Selesai stuffing</h2>
            <div class="sg-row">
                @foreach ($sealSteps as $step => [$label])
                    <span class="sg-badge {{ in_array($step, $s->seal_steps, true) ? 'sg-badge--arrived' : '' }}">
                        @include('menu.exim-export.security.partials.icon', ['name' => in_array($step, $s->seal_steps, true) ? 'check' : 'warn']){{ $label }}
                    </span>
                @endforeach
            </div>
            <dl class="sg-card__meta" style="margin:0">
                @if ($withStuffing)
                    <dt>Tutup container</dt><dd>{{ $s->gate_close_at ? \Carbon\Carbon::parse($s->gate_close_at)->isoFormat('HH:mm · D MMM YYYY') : '-' }}</dd>
                @endif
                @foreach ($sealRoles as $col => $label)
                    @php $p = $s->seal_people[$col] ?? null; @endphp
                    <dt>{{ $label }}</dt>
                    <dd>
                        {{ $p->nama ?? '-' }}
                        @if ($p && $p->karid)
                            <span class="sg-person__sub">NRK <span class="sg-mono">{{ $p->karid }}</span>{{ $p->depnm ? ' · ' . $p->depnm : '' }}</span>
                        @endif
                    </dd>
                @endforeach
            </dl>
        </section>
    @endif

    @if ($withOutMedia)
        @include('menu.exim-export.security.partials.out-media-view', ['kinds' => $loadKinds, 'video' => true, 'title' => 'Dokumentasi stuffing & seal'])
    @endif

    @if ($s->signs)
        <section class="sg-section">
            <h2 class="sg-section__title">Tanda tangan</h2>
            <div class="sg-signs sg-signs--view">
                @foreach ($signers as $key => $label)
                    <figure>
                        @if (!empty($s->signs[$key]))
                            <img src="{{ $media($s->signs[$key]) }}" alt="Tanda tangan {{ $label }}" loading="lazy">
                        @else
                            <span class="sg-signs__empty">Tidak ada</span>
                        @endif
                        <figcaption>{{ $label }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif
    </div>

    {{-- tab 4: gate out --}}
    <div class="sg-panel" id="panel-out" data-panel="out" role="tabpanel">
    <div class="sg-exit-at">
        @include('menu.exim-export.security.partials.icon', ['name' => 'clock'])
        <span>Keluar factory <strong>{{ $s->departed_at->isoFormat('HH:mm, D MMM YYYY') }}</strong>
            <small>{{ $stay }}</small></span>
    </div>
    @if ($withOutMedia)
        @include('menu.exim-export.security.partials.out-media-view', ['kinds' => $exitKinds, 'video' => false, 'title' => 'Foto container keluar'])
    @endif
    </div>
    @elseif ($canOut)
        <form id="outForm" class="sg-out" novalidate data-url="{{ route('security.gate.out', $s->contpk) }}" data-stuffing-url="{{ route('security.gate.stuffing', $s->contpk) }}" data-seal-url="{{ route('security.gate.seal', $s->contpk) }}" data-rejected="{{ $rejected ? 1 : 0 }}">
            {{-- tab 2: muat (berita acara stuffing & dokumen pengiriman) --}}
            <div class="sg-panel" id="panel-load" data-panel="load" role="tabpanel">
            @if ($withStuffing)
                @include('menu.exim-export.security.partials.stuffing')
            @elseif (!$rejected)
                <div class="sg-alert sg-alert--warn" role="status">
                    @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                    <div><strong>Data muat & catat keluar belum bisa dipakai</strong>Kolom data muat (berita acara stuffing & dokumen pengiriman) belum ada di database. Hubungi IT.</div>
                </div>
            @endif

            @if ($rejected)
                <p class="sg-hint" style="margin:0">Container tidak lulus pemeriksaan, truk keluar tanpa pemuatan. Isi tanda tangan, lalu catat keluar di tab Gate out.</p>
            @else
            </div>

            {{-- tab 3: seal (selesai stuffing, dokumentasi, tanda tangan); container ditolak: tanda tangan di tab Ditolak --}}
            <div class="sg-panel" id="panel-seal" data-panel="seal" role="tabpanel">
                <section class="sg-section">
                    <h2 class="sg-section__title">Selesai stuffing</h2>
                    <p class="sg-hint" style="margin:0">Isi setelah pemuatan selesai dan seal terpasang. Bisa disimpan bertahap dengan tombol Simpan data seal.</p>
                    @include('menu.exim-export.security.partials.seal-no')
                    @include('menu.exim-export.security.partials.seal-check')
                    @include('menu.exim-export.security.partials.seal-people')
                    @if ($withStuffing)
                        {{-- jam tutup container tidak diinput: waktu foto seal terpasang terakhir (GateController::upload) --}}
                        <div class="sg-field" data-field="gate_close_at">
                            <span class="sg-label">Jam tutup container</span>
                            <span class="sg-readonly {{ $s->gate_close_at ? '' : 'is-empty' }}" data-close-at>{{ $s->gate_close_at ? \Carbon\Carbon::parse($s->gate_close_at)->isoFormat('HH:mm, D MMM YYYY') : 'Belum ada foto seal terpasang' }}</span>
                            <span class="sg-hint">Otomatis dari waktu foto Seal terpasang diunggah.</span>
                        </div>
                    @endif
                </section>

                @if ($withOutMedia)
                    @include('menu.exim-export.security.partials.out-media', ['kinds' => $loadKinds, 'video' => true, 'field' => 'out_media',
                        'title' => 'Dokumentasi stuffing & seal', 'intro' => 'Ambil berurutan: selesai stuffing (pintu dibuka), pintu ditutup, lalu seal terpasang. Foto & video langsung tersimpan.'])
                @else
                    <div class="sg-alert sg-alert--warn" role="status">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                        <div><strong>Catat keluar belum bisa dipakai</strong>Kolom dokumentasi keluar (gate_out_media) belum ada di database. Hubungi IT.</div>
                    </div>
                @endif
            @endif

            <section class="sg-section">
                <h2 class="sg-section__title">Tanda tangan</h2>
                @if ($signReady)
                    <p class="sg-hint" style="margin:0">Masing-masing tanda tangan langsung di layar dengan jari atau stylus.</p>
                    @include('menu.exim-export.security.partials.signatures')
                @else
                    <div class="sg-alert sg-alert--warn" role="status">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                        <div><strong>Catat keluar belum bisa dipakai</strong>Kolom tanda tangan (gate_signs) belum ada di database. Hubungi IT.</div>
                    </div>
                @endif
            </section>
            </div>

            {{-- tab 4: gate out (foto container keluar = jam keluar factory) --}}
            <div class="sg-panel" id="panel-out" data-panel="out" role="tabpanel">
            @if ($rejected)
                <section class="sg-section">
                    <h2 class="sg-section__title">Keluar factory</h2>
                    <p class="sg-hint" style="margin:0">Truk keluar tanpa pemuatan. Jam keluar diambil saat tombol Catat keluar ditekan.</p>
                </section>
            @elseif ($withOutMedia)
                @include('menu.exim-export.security.partials.out-media', ['kinds' => $exitKinds, 'video' => false, 'field' => 'out_exit_media',
                    'title' => 'Container keluar', 'intro' => 'Foto truk & container di gerbang saat keluar. Jam keluar factory tersimpan otomatis dari foto ini.'])
            @endif
            </div>
        </form>
    @else
        {{-- struktur database belum lengkap: catat keluar tidak tersedia --}}
        @if ($rejected)
            <div class="sg-panel" id="panel-load" data-panel="load" role="tabpanel">
                <p class="sg-hint" style="margin:0">Container tidak lulus pemeriksaan, tidak ada pemuatan.</p>
            </div>
        @else
            <div class="sg-panel" id="panel-seal" data-panel="seal" role="tabpanel">
                <p class="sg-hint" style="margin:0">Data seal belum bisa dipakai. Hubungi IT.</p>
            </div>
        @endif
        <div class="sg-panel" id="panel-out" data-panel="out" role="tabpanel">
            <p class="sg-hint" style="margin:0">Truk belum keluar. Catat keluar belum bisa dipakai.</p>
        </div>
    @endif
</div>
<div class="sg-bottombar">
    @if ($canOut)
        {{-- satu aksi per tab, diatur showTab() di index.blade.php: Muat = simpan data muat, Seal = simpan data seal, Gate out = catat keluar --}}
        <a class="sg-btn sg-btn--lg sg-btn--block" data-tab-fallback href="{{ route('security.gate.index', array_filter(['filter' => $filter, 'q' => $q])) }}">Kembali ke daftar</a>
        @if ($withStuffing)
            <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" id="btnStuffSave" data-tab-action="load" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'check'])<span>Simpan data muat</span></button>
        @endif
        @if (!$rejected && $signReady)
            <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" id="btnSealSave" data-tab-action="seal" hidden>@include('menu.exim-export.security.partials.icon', ['name' => 'check'])<span>Simpan data seal</span></button>
        @endif
        <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" id="btnOut" data-tab-action="out" hidden {{ $outLocked ? 'disabled' : '' }}>@include('menu.exim-export.security.partials.icon', ['name' => 'truck'])<span>Catat keluar</span></button>
    @else
        <a class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" href="{{ route('security.gate.index', array_filter(['filter' => request('saved') || request('out') ? 'today' : $filter, 'q' => $q])) }}">Kembali ke daftar</a>
    @endif
</div>
