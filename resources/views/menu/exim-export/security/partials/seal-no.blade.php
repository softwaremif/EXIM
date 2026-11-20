{{-- No. seal diisi EXIM di shipment plan; security hanya mencocokkan dengan seal di pintu --}}
<div class="sg-field" data-field="noseal">
    <span class="sg-label">No. seal</span>
    @if ($s->noseal)
        <span class="sg-seal-no sg-mono">{{ $s->noseal }}</span>
        <span class="sg-hint">Dari shipment plan EXIM. Cocokkan dengan seal yang terpasang di pintu.</span>
    @else
        <div class="sg-alert sg-alert--warn" role="status">
            @include('menu.exim-export.security.partials.icon', ['name' => 'warn'])
            <div><strong>No. seal belum diisi EXIM</strong>Minta EXIM mengisi nomor seal di shipment plan sebelum truk keluar.</div>
        </div>
        <span class="sg-hint" hidden></span>
    @endif
</div>
