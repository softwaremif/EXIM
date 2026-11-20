{{-- Langkah pemasangan seal (VVTT) sebagai daftar checkbox; semua wajib dicentang. Jumlah dicentang dihitung JS ([data-seal-check] di index.blade.php) --}}
@php $sealDone = count(array_intersect(array_keys($sealSteps), $s->seal_steps)); @endphp
<div class="sg-field" data-field="seal_check" data-seal-check>
    <div class="sg-checklist__head">
        <span class="sg-label" id="sealCheckLabel">Pemasangan seal <span class="sg-req">*</span></span>
        <span class="sg-checklist__count {{ $sealDone === count($sealSteps) ? 'is-done' : '' }}" data-seal-count>{{ $sealDone }}/{{ count($sealSteps) }}</span>
    </div>
    <div class="sg-checklist" role="group" aria-labelledby="sealCheckLabel">
        @foreach ($sealSteps as $step => [$label, $hint])
            <label class="sg-checkrow">
                <input type="checkbox" name="seal_check[]" value="{{ $step }}" {{ in_array($step, $s->seal_steps, true) ? 'checked' : '' }}>
                <span class="sg-checkrow__box" aria-hidden="true"></span>
                <span class="sg-checkrow__text">
                    <strong>{{ $label }}</strong>
                    <small>{{ $hint }}</small>
                </span>
            </label>
        @endforeach
    </div>
    <span class="sg-hint" hidden></span>
</div>
