{{-- Dokumentasi muat & keluar. Dipakai 2x: tab Seal (foto selesai stuffing s/d seal terpasang + video seal)
     dan tab Gate out (foto container keluar + jam keluar). Langsung tersimpan saat diambil (initOutMedia di index.blade.php).
     Pakai: @include('...out-media', ['kinds' => [...subset $outKinds], 'video' => bool, 'field' => 'out_media', 'title' => '...', 'intro' => '...'])
     Satu baris per jenis foto: judul + jumlah, lalu deretan thumbnail kecil (ketuk = lihat besar, tombol pojok = hapus)
     dan kotak "+" untuk ambil foto berikutnya. --}}
@php
    $m = $s->out_media;
    $url = fn($f) => $f ? asset('public/upload/gate/' . $s->contpk . '/' . $f) : null;
    $withExit = isset($kinds['out_exit']);
@endphp
<section class="sg-section" id="sec-{{ $field }}" data-out-media
    data-upload-url="{{ route('security.gate.upload', $s->contpk) }}"
    data-delete-url="{{ route('security.gate.media-delete', $s->contpk) }}"
    data-chunk="{{ $chunkSize }}" data-max="{{ $maxOutFoto }}">
    <h2 class="sg-section__title">{{ $title }}</h2>
    <p class="sg-hint" style="margin:0">{{ $intro }}</p>
    <div class="sg-field" data-field="{{ $field }}">
        <div class="sg-orows" data-gallery>
            @foreach ($kinds as $kind => [$label, $hint, $required])
                @php $n = count($m[$kind]); @endphp
                <div class="sg-orow {{ $n ? 'is-done' : '' }}" data-out-upload="{{ $kind }}" @unless ($required) data-optional @endunless>
                    <div class="sg-orow__head">
                        <span class="sg-orow__title"><span class="sg-upload__title">{{ $label }}</span>@if ($required)<span class="sg-req">*</span>@else<small class="sg-upload__opt">opsional</small>@endif</span>
                        <span class="sg-count {{ $n ? 'is-done' : '' }}" data-count-bar>{{ $n }}/{{ $maxOutFoto }}</span>
                    </div>
                    <span class="sg-upload__hint" data-hint="{{ $hint }}">{{ $hint }}</span>
                    <span class="sg-progress" hidden><span></span></span>
                    <div class="sg-orow__strip">
                        <div class="sg-orow__pics" data-out-thumbs>
                            @foreach ($m[$kind] as $f)
                                <div class="sg-thumb" data-kind="{{ $kind }}" data-file="{{ $f }}">
                                    <a href="{{ $url($f) }}" target="_blank" rel="noopener" data-lightbox><img src="{{ $url($f) }}" alt="{{ $label }} {{ $loop->iteration }}" loading="lazy"></a>
                                    <button type="button" class="sg-thumb__del" aria-label="Hapus foto {{ Str::lower($label) }} {{ $loop->iteration }}">@include('menu.exim-export.security.partials.icon', ['name' => 'trash'])</button>
                                </div>
                            @endforeach
                        </div>
                        <label class="sg-orow__add" data-add {{ $n >= $maxOutFoto ? 'hidden' : '' }}>
                            @include('menu.exim-export.security.partials.icon', ['name' => 'camera'])
                            <span data-add-label>{{ $n ? 'Tambah' : 'Ambil foto' }}</span>
                            <input type="file" accept="image/*" capture="environment" aria-label="Ambil foto {{ Str::lower($label) }}" {{ $n >= $maxOutFoto ? 'disabled' : '' }}>
                        </label>
                    </div>
                </div>
            @endforeach
            @if ($video)
                <div class="sg-orow {{ $m['out_video'] ? 'is-done' : '' }}" data-out-upload="out_video">
                    <div class="sg-orow__head">
                        <span class="sg-orow__title"><span class="sg-upload__title">Video seal sudah terpasang dengan baik</span><span class="sg-req">*</span></span>
                        <span class="sg-count {{ $m['out_video'] ? 'is-done' : '' }}" data-video-state>{{ $m['out_video'] ? 'Tersimpan' : 'Belum ada' }}</span>
                    </div>
                    <span class="sg-upload__hint" data-hint="Pemasangan seal, maks 60 detik">Pemasangan seal, maks 60 detik</span>
                    <span class="sg-progress" hidden><span></span></span>
                    <div class="sg-orow__strip">
                        <a class="sg-orow__video" data-out-video data-video="Video pemasangan seal" href="{{ $url($m['out_video']) }}" target="_blank" rel="noopener" {{ $m['out_video'] ? '' : 'hidden' }}>
                            @include('menu.exim-export.security.partials.icon', ['name' => 'play'])<span>Putar</span>
                        </a>
                        <label class="sg-orow__add">
                            @include('menu.exim-export.security.partials.icon', ['name' => 'video'])
                            <span data-video-add>{{ $m['out_video'] ? 'Ganti' : 'Rekam' }}</span>
                            <input type="file" accept="video/*" capture="environment" aria-label="Rekam video seal sudah terpasang dengan baik">
                        </label>
                    </div>
                </div>
            @endif
        </div>
        <span class="sg-hint" hidden></span>
    </div>
    @if ($withExit)
        {{-- jam keluar factory otomatis tersimpan saat foto container keluar diunggah --}}
        <div class="sg-exit-at" data-out-exit-at {{ $m['out_exit_at'] ? '' : 'hidden' }}>
            @include('menu.exim-export.security.partials.icon', ['name' => 'clock'])
            <span>Jam keluar factory <strong data-out-exit-time>{{ $m['out_exit_at'] ? \Carbon\Carbon::parse($m['out_exit_at'])->isoFormat('HH:mm, D MMM YYYY') : '' }}</strong>
                <small>Tersimpan otomatis dari foto container keluar terakhir</small></span>
        </div>
    @endif
</section>
