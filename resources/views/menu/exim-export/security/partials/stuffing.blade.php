{{-- Form kertas "Berita Acara Stuffing" & "Dokumen Pengiriman Barang" saat muat & seal.
     Data yang sudah ada (nopol, buyer, driver, jumlah carton, lokasi, pelabuhan) tampil otomatis; sisanya diisi. --}}
@php
    $lokasi = $s->gate_lokasi ?: $s->lokasi_auto;
    $tanggal = \Carbon\Carbon::parse($s->gate_load_at ?: ($s->gate_open_at ?: now()));
    $cartonText = $s->carton_count === null ? 'Data carton tidak bisa diambil' : $s->carton_count . ' carton';
@endphp
<section class="sg-section" id="sec-stuffing" data-stuffing>
    <h2 class="sg-section__title">Berita acara stuffing</h2>
    <dl class="sg-card__meta" style="margin:0">
        <dt>Tanggal</dt><dd>{{ $tanggal->isoFormat('dddd, D MMM YYYY') }}</dd>
        <dt>Truck / Nopol</dt><dd class="sg-mono">{{ $s->nopol ?: '-' }}</dd>
        <dt>Buyer</dt><dd>{{ $s->buyer ?: '-' }}</dd>
        <dt>Total carton</dt><dd>{{ $cartonText }}</dd>
    </dl>
    <div class="sg-field" data-field="gate_lokasi">
        <label class="sg-label" for="gate_lokasi">Lokasi <span class="sg-req">*</span></label>
        <input class="sg-input" id="gate_lokasi" name="gate_lokasi" value="{{ $lokasi }}" maxlength="50" autocomplete="off" placeholder="MIF 2">
        @if ($s->lokasi_auto)
            <span class="sg-hint">Otomatis dari carton yang dimuat: {{ $s->lokasi_auto }}</span>
        @else
            <span class="sg-hint" hidden></span>
        @endif
    </div>
    <div class="sg-grid2">
        @include('menu.exim-export.security.partials.time-field', ['name' => 'gate_open_at', 'label' => 'Jam buka container', 'value' => $s->gate_open_at, 'required' => true])
        {{-- jam mulai muat tidak diinput security: terisi otomatis saat finishing melakukan action muat (Api\ShipplanController::updateShipment) --}}
        <div class="sg-field" data-field="gate_load_at" @unless ($s->gate_load_at) data-load-missing @endunless>
            <span class="sg-label">Jam mulai muat <span class="sg-req">*</span></span>
            @if ($s->gate_load_at)
                <span class="sg-readonly">{{ \Carbon\Carbon::parse($s->gate_load_at)->isoFormat('HH:mm · D MMM YYYY') }}</span>
                <span class="sg-hint">Otomatis dari finishing saat mulai muat.</span>
            @else
                <span class="sg-readonly is-empty">Belum mulai muat</span>
                <span class="sg-hint">Terisi otomatis saat finishing melakukan action muat. Wajib terisi sebelum Catat keluar.</span>
            @endif
        </div>
    </div>
    <div class="sg-field" data-field="gate_stuffing_note">
        <label class="sg-label" for="gate_stuffing_note">Catatan</label>
        <input class="sg-input" id="gate_stuffing_note" name="gate_stuffing_note" value="{{ $s->gate_stuffing_note }}" maxlength="255" autocomplete="off" placeholder="Contoh: muat barang jadi, scan carton">
        <span class="sg-hint" hidden></span>
    </div>
</section>

<section class="sg-section" data-stuffing>
    <h2 class="sg-section__title">Dokumen pengiriman barang</h2>
    <dl class="sg-card__meta" style="margin:0">
        <dt>Driver</dt><dd>{{ $s->gate_driver ?: '-' }}</dd>
        <dt>Jumlah carton</dt><dd>{{ $cartonText }}</dd>
        <dt>Berangkat dari MIF</dt><dd>{{ $s->departed_at ? $s->departed_at->isoFormat('HH:mm · D MMM YYYY') : 'Otomatis saat Catat keluar' }}</dd>
        <dt>Foto tanggal</dt><dd>{{ $tanggal->isoFormat('D MMM YYYY') }}</dd>
    </dl>
    <div class="sg-field" data-field="gate_country">
        <label class="sg-label" for="gate_country">Kirim ke negara <span class="sg-req">*</span></label>
        <input class="sg-input" id="gate_country" name="gate_country" value="{{ $s->gate_country }}" maxlength="100" list="dlCountry" autocapitalize="characters" autocomplete="off" placeholder="USA">
        <span class="sg-hint" hidden></span>
    </div>
    @include('menu.exim-export.security.partials.people-list', ['name' => 'gate_loaders', 'label' => 'Petugas muat', 'max' => $maxLoaders, 'people' => $s->loaders, 'required' => true])
    @include('menu.exim-export.security.partials.people-list', ['name' => 'gate_guards', 'label' => 'Petugas security', 'max' => $maxGuards, 'people' => $s->guards, 'required' => true])
    @include('menu.exim-export.security.partials.time-field', [
        'name' => 'gate_port_at', 'label' => 'Tiba di ' . ($s->port_name ?: 'pelabuhan / bandara'), 'value' => $s->gate_port_at, 'required' => false,
        'hint' => 'Boleh diisi nanti lewat menu Edit setelah truk keluar.',
    ])
    @include('menu.exim-export.security.partials.person-slot', ['name' => 'gate_escort_nrk', 'label' => 'Pengawal dari MIF', 'person' => $s->escort, 'required' => false])
</section>
<datalist id="dlCountry">@foreach ($suggest['country'] ?? [] as $v)<option value="{{ $v }}">@endforeach</datalist>
