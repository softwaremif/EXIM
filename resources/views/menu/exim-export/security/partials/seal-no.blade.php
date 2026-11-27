{{-- No. seal dari shipment plan EXIM: hanya informasi (tidak wajib untuk catat keluar) --}}
<div class="sg-field" data-field="noseal">
    <span class="sg-label">No. seal</span>
    @if ($s->noseal)
        <span class="sg-seal-no sg-mono">{{ $s->noseal }}</span>
        <span class="sg-hint">Dari shipment plan EXIM. Cocokkan dengan seal yang terpasang di pintu.</span>
    @else
        <span class="sg-seal-no sg-mono">-</span>
        <span class="sg-hint">Tidak diisi di shipment plan EXIM.</span>
    @endif
</div>
