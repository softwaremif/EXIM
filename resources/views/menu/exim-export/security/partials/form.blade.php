@php
    $s = $selected;
    $media = fn($f) => $f ? asset('public/upload/gate/' . $s->contpk . '/' . $f) : null;
    $simExp = $s->gate_sim_exp ? substr($s->gate_sim_exp, 0, 10) : '';
    $result = $s->gate_result === null ? null : (string) (int) $s->gate_result;
    $totalItems = collect($checklist)->flatMap(fn($sec) => collect($sec['groups'])->flatMap(fn($g) => array_keys($g['items'])))->count();
    // mode edit: container sudah tiba / keluar; setelah keluar hasil dikunci & data seal ikut diperbaiki
    $editing = $editing ?? false;
    $departed = $editing && $s->departed_at;
    $sealEdit = $departed && $result !== '0';
    $backUrl = route('security.gate.index', array_filter(['filter' => $filter, 'q' => $q, 'cont' => $s->contpk]));
@endphp
{{-- tab per bagian form; status langkah (selesai / berikutnya) dihitung ulang oleh JS (syncStepper) --}}
@php
    $tabs = ['container' => 'Container', 'truck' => 'Truk & driver', 'check' => 'Pemeriksaan', 'media' => 'Foto & video']
        + ($sealEdit ? ['load' => 'Muat & seal'] : []) + ['result' => 'Hasil'];
@endphp
<ol class="sg-stepper" id="gateStepper" role="tablist" aria-label="Bagian form">
    @foreach ($tabs as $tab => $label)
        <li class="sg-step {{ $tab === 'container' ? 'is-done' : '' }}" data-tab="{{ $tab }}" @if (!in_array($tab, ['container', 'load'], true)) data-step="{{ $tab }}" @endif>
            <a class="sg-step__label" href="#panel-{{ $tab }}" role="tab" aria-controls="panel-{{ $tab }}">{{ $loop->iteration }}. {{ $label }}</a>
        </li>
    @endforeach
</ol>

<form id="gateForm" class="sg-main" novalidate
    data-mode="{{ $editing ? 'edit' : 'new' }}" data-orig-result="{{ $result }}"
    data-draft-url="{{ route('security.gate.draft', $s->contpk) }}"
    data-store-url="{{ route('security.gate.store', $s->contpk) }}"
    data-update-url="{{ route('security.gate.update', $s->contpk) }}"
    data-upload-url="{{ route('security.gate.upload', $s->contpk) }}"
    data-delete-url="{{ route('security.gate.media-delete', $s->contpk) }}"
    data-chunk="{{ $chunkSize }}">

    <div class="sg-alert sg-alert--danger" id="formAlert" role="alert" hidden>
        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
        <div><strong>Data belum lengkap</strong><ul id="formAlertList"></ul></div>
    </div>

    @if ($editing)
        <div class="sg-alert sg-alert--warn" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'edit'])
            <div>
                <strong>Edit data container</strong>
                Jam tiba {{ $s->arrived_at->isoFormat('HH:mm, D MMM') }}{{ $s->departed_at ? ' dan jam keluar ' . $s->departed_at->isoFormat('HH:mm, D MMM') : '' }} tidak berubah.
                Foto &amp; video langsung tersimpan saat diunggah. Data lain tersimpan setelah menekan <b>Simpan perubahan</b>.
            </div>
        </div>
    @endif

    @if ($s->replaced)
        <div class="sg-alert" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'info'])
            <div>
                <strong>Container pengganti</strong>
                Menggantikan {{ $s->replaced->contno ?: 'container' }} yang ditolak
                {{ $s->replaced->actcontdate ? \Carbon\Carbon::parse($s->replaced->actcontdate)->isoFormat('D MMM') : '' }}{{ $s->replaced->gate_result_note ? ' · ' . $s->replaced->gate_result_note : '' }}.
                Periksa ulang dari awal.
            </div>
        </div>
    @endif

    <div class="sg-panel" id="panel-container" data-panel="container" role="tabpanel">
        <section class="sg-section" id="sec-container">
            <h2 class="sg-section__title">Container</h2>
            <dl class="sg-card__meta" style="margin:0">
                @if ($s->contno)
                    <dt>No. container</dt><dd class="sg-mono">{{ $s->contno_fmt }}</dd>
                @endif
                <dt>Tipe</dt>
                <dd><span class="sg-kind {{ $s->is_lcl ? 'sg-kind--lcl' : '' }}">{{ $s->is_lcl ? 'LCL' : 'Container' }}</span> {{ $s->is_lcl ? 'tanpa container, acuan plat truk' : $s->type_label }}</dd>
                <dt>Shipment</dt><dd class="sg-mono">{{ $s->idship }}</dd>
                <dt>ETA</dt><dd>{{ $s->eta ? $s->eta->isoFormat('dddd, D MMM YYYY') : '-' }}</dd>
                <dt>Buyer</dt><dd>{{ $s->buyer ?: '-' }}</dd>
                <dt>Forwarder</dt><dd>{{ $s->fwdnm ?: '-' }}</dd>
                <dt>Ekspedisi</dt><dd>{{ ($s->ekspedisinm ?? null) ?: '-' }}</dd>
                <dt>No. seal</dt><dd class="sg-mono">{{ $s->noseal ?: 'Belum diisi EXIM' }}</dd>
            </dl>
            {{-- LCL tidak punya no. container: tidak perlu diisi --}}
            @unless ($s->contno || $s->is_lcl)
                <div class="sg-field" data-field="contno">
                    <label class="sg-label" for="contno">No. container <span class="sg-req">*</span></label>
                    <input class="sg-input sg-input--plate" id="contno" name="contno" autocapitalize="characters" autocomplete="off" placeholder="MSKU1234567">
                    <span class="sg-hint">Belum diisi EXIM. Salin dari pintu container.</span>
                </div>
            @endunless
        </section>
    </div>

    <div class="sg-panel" id="panel-truck" data-panel="truck" role="tabpanel">
        <section class="sg-section" id="sec-truck">
            <h2 class="sg-section__title">Truk</h2>
            {{-- plat nomor truk diisi EXIM di shipment plan; security hanya mencocokkan dengan truk yang datang --}}
            <div class="sg-field" data-field="nopol">
                <span class="sg-label">Plat nomor truk</span>
                @if ($s->nopol)
                    <span class="sg-seal-no sg-mono">{{ $s->nopol }}</span>
                    <span class="sg-hint">Dari shipment plan EXIM. Cocokkan dengan plat truk yang datang.</span>
                @else
                    <div class="sg-alert sg-alert--warn" role="status">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                        <div><strong>Plat truk belum diisi EXIM</strong>Minta EXIM mengisi No. Truck di shipment plan.</div>
                    </div>
                    <span class="sg-hint" hidden></span>
                @endif
            </div>
            {{-- perusahaan angkutan = forwarder (diisi otomatis saat simpan) --}}
            <div class="sg-field" data-field="gate_unit">
                <label class="sg-label" for="gate_unit">Unit pemuat</label>
                <input class="sg-input" id="gate_unit" name="gate_unit" value="{{ $s->gate_unit ?: 'Finishing' }}" list="dlUnit" autocomplete="off" placeholder="Finishing">
                <span class="sg-hint" hidden></span>
            </div>
        </section>

        <section class="sg-section">
            <h2 class="sg-section__title">Driver</h2>
            <div class="sg-field" data-field="gate_driver">
                <label class="sg-label" for="gate_driver">Nama driver <span class="sg-req">*</span></label>
                <input class="sg-input" id="gate_driver" name="gate_driver" value="{{ $s->gate_driver }}" autocomplete="off">
                <span class="sg-hint" hidden></span>
            </div>
            <div class="sg-field" data-field="gate_driver_hp">
                <label class="sg-label" for="gate_driver_hp">No. HP driver</label>
                <input class="sg-input" id="gate_driver_hp" name="gate_driver_hp" value="{{ $s->gate_driver_hp }}" inputmode="tel" autocomplete="off" placeholder="0812 3456 7890">
                <span class="sg-hint" hidden></span>
            </div>
            <div class="sg-grid2">
                <div class="sg-field" data-field="gate_sim_no">
                    <label class="sg-label" for="gate_sim_no">No. SIM <span class="sg-req">*</span></label>
                    <input class="sg-input sg-mono" id="gate_sim_no" name="gate_sim_no" value="{{ $s->gate_sim_no }}" inputmode="numeric" autocomplete="off" placeholder="1426-6103-000354">
                    <span class="sg-hint" hidden></span>
                </div>
                <div class="sg-field" data-field="gate_sim_exp">
                    <label class="sg-label" for="gate_sim_exp">Masa berlaku SIM <span class="sg-req">*</span></label>
                    <input class="sg-input" type="date" id="gate_sim_exp" name="gate_sim_exp" value="{{ $simExp }}">
                    <span class="sg-hint" hidden></span>
                </div>
            </div>
            <div class="sg-alert sg-alert--warn" id="simExpired" role="status" {{ $simExp && $simExp < now()->toDateString() ? '' : 'hidden' }}>
                @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
                <div><strong>SIM sudah habis masa berlakunya</strong>Pertimbangkan saat menentukan hasil pemeriksaan.</div>
            </div>
            <div class="sg-field">
                <span class="sg-label">Foto identitas driver <span class="sg-req">*</span></span>
                <div class="sg-uploads sg-uploads--pair">
                    {{-- identitas driver: 1 foto, ganti menimpa --}}
                    <label class="sg-upload {{ $s->gate_foto_id ? 'is-done' : '' }}" data-upload="id">
                        <img class="sg-upload__thumb" alt="" src="{{ $media($s->gate_foto_id) }}" {{ $s->gate_foto_id ? '' : 'hidden' }}>
                        @include('menu.exim-export.security.partials.icon', ['name' => 'id', 'class' => 'sg-icon--lg'])
                        <span class="sg-upload__title">Identitas driver</span>
                        <span class="sg-progress" hidden><span></span></span>
                        <span class="sg-upload__hint" data-hint="KTP atau SIM, 1 foto">KTP atau SIM, 1 foto</span>
                        <span class="sg-upload__bar">@include('menu.exim-export.security.partials.icon', ['name' => 'check']) Tersimpan<span>Ganti</span></span>
                        <input type="file" accept="image/*" capture="environment">
                    </label>
                </div>
            </div>
        </section>
    </div>

    <div class="sg-panel" id="panel-check" data-panel="check" role="tabpanel">
        @foreach ($checklist as $key => $section)
            @include('menu.exim-export.security.partials.checklist', ['key' => $key, 'section' => $section, 'answers' => $s->answers])
        @endforeach
    </div>

    <div class="sg-panel" id="panel-media" data-panel="media" role="tabpanel">
        <section class="sg-section" id="sec-media">
            <h2 class="sg-section__title">Foto &amp; video container</h2>
            {{-- foto container kosong: 1 foto per sisi (CONT_PHOTO_SLOTS), ambil ulang menimpa --}}
            <div class="sg-field">
                <span class="sg-label">Foto container kosong <span class="sg-req">*</span></span>
                <div class="sg-uploads">
                    @foreach ($contSlots as $slot => [$label, $hint])
                        @php $file = $s->cont_photos[$slot]; @endphp
                        <label class="sg-upload sg-upload--slot {{ $file ? 'is-done' : '' }}" data-upload="cont" data-slot="{{ $slot }}">
                            <img class="sg-upload__thumb" alt="Foto {{ Str::lower($label) }}" src="{{ $media($file) }}" {{ $file ? '' : 'hidden' }}>
                            <span class="sg-upload__no">{{ $loop->iteration }}</span>
                            @include('menu.exim-export.security.partials.icon', ['name' => 'camera', 'class' => 'sg-icon--lg'])
                            <span class="sg-upload__title">{{ $label }}</span>
                            <span class="sg-progress" hidden><span></span></span>
                            <span class="sg-upload__hint" data-hint="{{ $hint }}">{{ $hint }}</span>
                            <span class="sg-upload__bar"><span>@include('menu.exim-export.security.partials.icon', ['name' => 'check']) {{ $label }}</span><span>Ganti</span></span>
                            <input type="file" accept="image/*" capture="environment">
                        </label>
                    @endforeach
                </div>
                {{-- foto container dari sebelum ada 5 sisi --}}
                <div class="sg-out-thumbs" id="contOld" {{ $s->cont_photos['lama'] ? '' : 'hidden' }}>
                    <span class="sg-hint">Foto lama (sebelum ada foto 5 sisi)</span>
                    <div class="sg-thumbs" id="contThumbs">
                        @foreach ($s->cont_photos['lama'] as $f)
                            <div class="sg-thumb" data-file="{{ $f }}">
                                <img src="{{ $media($f) }}" alt="Foto container lama {{ $loop->iteration }}" loading="lazy">
                                <button type="button" class="sg-thumb__del" aria-label="Hapus foto">@include('menu.exim-export.security.partials.icon', ['name' => 'trash'])</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="sg-field">
                <span class="sg-label">Video kondisi container <span class="sg-req">*</span></span>
                <div class="sg-uploads">
                    {{-- video kondisi: 1 video, maks 5 menit, dikompres di perangkat --}}
                    <label class="sg-upload sg-upload--wide {{ $s->gate_video ? 'is-done' : '' }}" data-upload="video" data-max-sec="{{ $maxVideoSec }}">
                        @include('menu.exim-export.security.partials.icon', ['name' => 'video', 'class' => 'sg-icon--lg'])
                        <span class="sg-upload__title">Video kondisi container</span>
                        <span class="sg-progress" hidden><span></span></span>
                        @php $videoHint = 'Keliling 4 sisi + dalam, maks ' . intdiv($maxVideoSec, 60) . ' menit'; @endphp
                        <span class="sg-upload__hint" data-hint="{{ $videoHint }}">{{ $videoHint }}</span>
                        <span class="sg-upload__bar">@include('menu.exim-export.security.partials.icon', ['name' => 'check']) Video tersimpan<span>Ganti</span></span>
                        <input type="file" accept="video/*" capture="environment">
                    </label>
                </div>
                <a class="sg-hint" id="videoLink" data-video="Video kondisi container" href="{{ $media($s->gate_video) }}" target="_blank" rel="noopener" {{ $s->gate_video ? '' : 'hidden' }}>Putar video yang tersimpan ›</a>
            </div>
        </section>
    </div>

    {{-- edit setelah truk keluar (container lulus): data muat & seal ikut diperbaiki --}}
    @if ($sealEdit)
    <div class="sg-panel" id="panel-load" data-panel="load" role="tabpanel">
        @if ($stuffReady)
            @include('menu.exim-export.security.partials.stuffing')
        @endif

        <section class="sg-section" id="sec-seal">
            <h2 class="sg-section__title">Pemasangan seal</h2>
            @include('menu.exim-export.security.partials.seal-no')
            @include('menu.exim-export.security.partials.seal-check')
            @include('menu.exim-export.security.partials.seal-people')
        </section>
    </div>
    @endif

    <div class="sg-panel" id="panel-result" data-panel="result" role="tabpanel">
        <section class="sg-section" id="sec-result">
            <h2 class="sg-section__title">Hasil pemeriksaan</h2>
            <p class="sg-hint" id="checkSummary" data-total="{{ $totalItems }}" style="margin:0"></p>
            <div class="sg-field" data-field="gate_result">
                <div class="sg-choices" role="radiogroup" aria-label="Hasil pemeriksaan">
                    <label class="sg-choice sg-choice--ok">
                        <input type="radio" name="gate_result" value="1" {{ $result === '1' ? 'checked' : '' }} {{ $departed && $result !== null ? 'disabled' : '' }}>
                        <span class="sg-choice__body">
                            <strong>@include('menu.exim-export.security.partials.icon', ['name' => 'check']) Lulus</strong>
                            <small>Boleh dilakukan pemuatan barang</small>
                        </span>
                    </label>
                    <label class="sg-choice sg-choice--danger">
                        <input type="radio" name="gate_result" value="0" {{ $result === '0' ? 'checked' : '' }} {{ $departed && $result !== null ? 'disabled' : '' }}>
                        <span class="sg-choice__body">
                            <strong>@include('menu.exim-export.security.partials.icon', ['name' => 'warn']) Tidak lulus</strong>
                            <small>Container tidak boleh dimuat</small>
                        </span>
                    </label>
                </div>
                @if ($departed && $result !== null)
                    <span class="sg-hint">Hasil tidak bisa diubah karena truk sudah keluar.</span>
                @else
                    <span class="sg-hint" hidden></span>
                @endif
            </div>
            <div class="sg-field" data-field="gate_result_note" {{ $result === '0' ? '' : 'hidden' }}>
                <label class="sg-label" for="gate_result_note">Alasan tidak lulus <span class="sg-req">*</span></label>
                <input class="sg-input" id="gate_result_note" name="gate_result_note" value="{{ $s->gate_result_note }}" maxlength="255" autocomplete="off">
                <span class="sg-hint" hidden></span>
            </div>
        </section>

        {{-- petugas security yang memeriksa saat gate in (kolom gatein_guard; tampil kalau kolomnya sudah ada) --}}
        @if ($inGuardsReady ?? false)
            <section class="sg-section" id="sec-in-guards">
                <h2 class="sg-section__title">Petugas security</h2>
                @include('menu.exim-export.security.partials.people-list', ['name' => 'gate_in_guards', 'label' => 'Petugas security', 'max' => $maxGuards, 'people' => $s->in_guards ?? [], 'required' => !$editing])
            </section>
        @endif
    </div>
</form>

<datalist id="dlUnit">@foreach ($suggest['unit'] ?? [] as $v)<option value="{{ $v }}">@endforeach</datalist>

<div class="sg-bottombar">
    @if ($editing)
        <a class="sg-btn sg-btn--lg" href="{{ $backUrl }}" id="btnCancel">Batal</a>
        <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" id="btnSubmit">Simpan perubahan</button>
    @else
        <button type="button" class="sg-btn sg-btn--lg" id="btnDraft">Simpan draft</button>
        <button type="button" class="sg-btn sg-btn--primary sg-btn--lg" id="btnSubmit">Simpan kedatangan</button>
    @endif
</div>
