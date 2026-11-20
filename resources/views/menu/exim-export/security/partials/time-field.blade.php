{{-- Jam (tanggal + jam) dengan tombol "Sekarang". Pakai: @include('...time-field', ['name' => 'gate_open_at', 'label' => 'Jam buka', 'value' => $s->gate_open_at, 'required' => true, 'hint' => null]) --}}
<div class="sg-field" data-field="{{ $name }}">
    <label class="sg-label" for="{{ $name }}">{{ $label }} @if ($required ?? true)<span class="sg-req">*</span>@endif</label>
    <div class="sg-now">
        <input class="sg-input" type="datetime-local" id="{{ $name }}" name="{{ $name }}" value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}">
        <button type="button" class="sg-btn" data-now aria-label="Isi {{ Str::lower($label) }} dengan jam sekarang">Sekarang</button>
    </div>
    @if (!empty($hint))
        <span class="sg-hint">{{ $hint }}</span>
    @else
        <span class="sg-hint" hidden></span>
    @endif
</div>
