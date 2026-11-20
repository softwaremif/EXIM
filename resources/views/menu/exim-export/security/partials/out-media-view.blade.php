{{-- Dokumentasi muat / keluar (baca saja) setelah truk keluar. Pakai: @include('...out-media-view', ['kinds' => [...], 'video' => bool, 'title' => '...'])
     Satu baris per jenis foto dengan thumbnail kecil; ketuk = lihat besar (lightbox di index.blade.php) --}}
@php $m = $s->out_media; @endphp
<section class="sg-section">
    <h2 class="sg-section__title">{{ $title }}</h2>
    <div class="sg-orows" data-gallery>
        @foreach ($kinds as $kind => [$label])
            <div class="sg-orow sg-orow--view">
                <div class="sg-orow__head">
                    <span class="sg-orow__title"><span class="sg-upload__title">{{ $label }}</span></span>
                    <span class="sg-count {{ $m[$kind] ? 'is-done' : '' }}">{{ count($m[$kind]) }} foto</span>
                </div>
                @if ($m[$kind])
                    <div class="sg-orow__pics">
                        @foreach ($m[$kind] as $f)
                            <a class="sg-thumb" href="{{ $media($f) }}" target="_blank" rel="noopener" data-lightbox><img src="{{ $media($f) }}" alt="{{ $label }} {{ $loop->iteration }}" loading="lazy"></a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
        @if ($video)
            <div class="sg-orow sg-orow--view">
                <div class="sg-orow__head">
                    <span class="sg-orow__title"><span class="sg-upload__title">Video dokumentasi seal</span></span>
                    <span class="sg-count {{ $m['out_video'] ? 'is-done' : '' }}">{{ $m['out_video'] ? 'Ada' : 'Tidak ada' }}</span>
                </div>
                @if ($m['out_video'])
                    <video class="sg-orow__player" src="{{ $media($m['out_video']) }}" controls preload="metadata"></video>
                @endif
            </div>
        @endif
    </div>
</section>
