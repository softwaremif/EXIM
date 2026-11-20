{{-- Tanda tangan digital langsung di layar (jari / stylus) sebelum catat keluar. Pad dijalankan oleh initSignPad() di index.blade.php.
     Tanda tangan yang sudah tersimpan (tombol Simpan seal) tampil sebagai gambar; Ulangi = tanda tangan baru menggantikannya. --}}
<div class="sg-signs">
    @foreach ($signers as $key => $label)
        @php $saved = !empty($s->signs[$key]) ? asset('public/upload/gate/' . $s->contpk . '/' . $s->signs[$key]) : null; @endphp
        <div class="sg-field" data-field="sign_{{ $key }}">
            <div class="sg-sign__head">
                <span class="sg-label" id="lbl-sign-{{ $key }}">{{ $label }} <span class="sg-req">*</span></span>
                <span class="sg-sign__status" data-sign-status {{ $saved ? '' : 'hidden' }}>@include('menu.exim-export.security.partials.icon', ['name' => 'check']) Tersimpan</span>
                <button type="button" class="sg-btn sg-btn--sm sg-btn--ghost" data-sign-clear hidden>Ulangi</button>
            </div>
            <div class="sg-sign" data-sign="{{ $key }}" data-label="{{ $label }}" @if ($saved) data-saved="{{ $saved }}" @endif>
                <canvas width="900" height="300" role="img" aria-labelledby="lbl-sign-{{ $key }}"></canvas>
                <span class="sg-sign__line"></span>
                <span class="sg-sign__placeholder">Tanda tangan di sini</span>
            </div>
            <span class="sg-hint" hidden></span>
        </div>
    @endforeach
</div>
