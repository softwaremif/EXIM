{{-- resources/views/menu/exim-export/shipplan/partials/container-cards.blade.php --}}
@forelse ($container as $c)
    <div class="col-md-6 col-lg-4">
        <div class="ld-card" data-contpk="{{ $c->contpk }}">
            <div class="ld-card-top">
                <span class="ld-badge">{{ strtoupper($c->type ?? '40HC') }}</span>
                <span class="ld-badge volume">{{ strtoupper($c->volume ? 'Volume COntainer: ' . $c->volume . ' Cbm' : '') }}</span>
                <span class="ld-status ld-status-{{ $c->status ?? 'loading' }}">
                    {{ ucfirst($c->status ?? 'loading') }}
                </span>
            </div>
            @php
                $loadedForContainer = $loadedCartons[$c->contpk] ?? collect();
                $groupedByCarton = $loadedForContainer->keyBy(
                    fn ($item) => ($item['carton'] ?? '') . '|' . ($item['POno'] ?? '') . '|' . ($item['OP'] ?? '')
                );
                $totalCbmAll = $loadedForContainer->sum(fn ($item) => $item['t_cbm'] ?? 0);
                $image = $groupedByCarton->count() > 0 ? 'container_isi.png' : 'container1.png';
            @endphp
            <button type="button"
                class="ld-container-visual {{ $isLocked ? 'ld-container-locked' : '' }}"
                @if (!$isLocked) onclick="openLoadingModal(
                        {{ $c->contpk }},
                        {{ json_encode($c->contno ?? '') }},
                        {{ $header->exportpk }},
                        {{ $header->ordpk }},
                        {{ json_encode($header->POno ?? '') }},
                        {{ json_encode($header->ordno ?? '') }}
                    )"
                @else
                    disabled @endif>
                <img src="{!! asset('public/css/images/' . $image) !!}" class="ld-container-image" alt="Container">
                <span class="ld-visual-label">
                    <i class="bi bi-hand-index-thumb"></i> Klik untuk muat carton
                </span>
            </button>
            <div class="ld-card-body">
                <div class="ld-mono ld-container-no">No. Container: {{ $c->contno }}</div>
                <div class="ld-meta">Vessel Name: {{ $c->vessname ?? 'Vessel belum diisi' }}</div>

                <div class="ld-loaded-section">
                    <div class="ld-loaded-header-row">
                        <button type="button" class="ld-loaded-toggle" onclick="toggleLoadedList({{ $c->contpk }})">
                            <i class="bi bi-box-seam"></i>
                            <span>{{ $groupedByCarton->count() }} carton dimuat</span>
                            <i class="bi bi-chevron-down ld-loaded-chevron" id="chevron-{{ $c->contpk }}"></i>
                        </button>
                        <button type="button"
                            class="ld-remove-all-btn btn-remove-all-cartons {{ $isLocked ? 'd-none' : '' }}"
                            data-contpk="{{ $c->contpk }}"
                            data-bs-toggle="tooltip" data-bs-placement="top" title="Keluarkan semua carton"
                            {{ $groupedByCarton->count() === 0 ? 'disabled' : '' }}>
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>

                    <div class="ld-loaded-chips d-none" id="loadedChips-{{ $c->contpk }}">
                        @forelse ($groupedByCarton as $groupKey => $item)
                            @php
                                $totalPcs = $item['pcsp'] ?? 0;
                                $packpksForChip = collect($item['packpks'] ?? [])->implode(',');
                                $safeGroupKey = preg_replace('/[^A-Za-z0-9]/', '_', $groupKey);
                                $poOpLabel = trim(($item['POno'] ?? '') . ' / ' . ($item['OP'] ?? ''), ' /');
                            @endphp
                            <span class="ld-chip"
                                id="chip-carton-{{ $safeGroupKey }}-{{ $c->contpk }}"
                                data-packpks="{{ $packpksForChip }}"
                                data-contpk="{{ $c->contpk }}"
                                data-exportpk="{{ $header->exportpk }}"
                                data-carton-no="{{ $item['carton'] ?? '' }}"
                                title="{{ $poOpLabel ?: 'PO/OP tidak diketahui' }}">
                                <span class="ld-chip-no">Ctn.{{ $item['carton'] ?? '-' }}</span>
                                {{-- @if ($poOpLabel)
                                    <span class="ld-chip-poop" style="font-size:.68rem;opacity:.7;">{{ $poOpLabel }}</span>
                                @endif --}}
                                <span class="ld-chip-qty">{{ $totalPcs }} pcs</span>
                                <button type="button" class="ld-chip-remove {{ $isLocked ? 'd-none' : '' }}" title="Keluarkan carton ini">
                                    <i class="bi bi-x"></i>
                                </button>
                            </span>
                        @empty
                            <div class="ld-chip-empty">Belum ada carton yang dimuat.</div>
                        @endforelse
                    </div>
                </div>
                <div class="ld-progress-row">
                    <span>Carton dimuat</span>
                    <span class="ld-loaded-count" id="loadedCount-{{ $c->contpk }}">
                        {{ $loadedForContainer->count() }} ctn ({{ number_format($totalCbmAll, 3, '.', ',') }} cbm)
                    </span>
                </div>
                <div class="ld-progress-track">
                    <div class="ld-progress-fill" id="loadedBar-{{ $c->contpk }}"
                        data-target="{{ $c->volume ?? 1 }}"
                        style="width: {{ min(100, ($totalCbmAll / max($c->volume ?? 1, 1)) * 100) }}%">
                    </div>
                </div>
            </div>
            <button type="button"
                class="ld-btn-action {{ $isLocked ? 'ld-btn-locked' : '' }}"
                @if (!$isLocked) onclick="openLoadingModal(
                        {{ $c->contpk }},
                        {{ json_encode($c->contno ?? '') }},
                        {{ $header->exportpk }},
                        {{ $header->ordpk }},
                        {{ json_encode($header->POno ?? '') }},
                        {{ json_encode($header->ordno ?? '') }}
                    )"
                @else
                    disabled @endif>
                <i class="bi {{ $isLocked ? 'bi-lock-fill' : 'bi-box-arrow-in-down' }}"></i>
                {{ $isLocked ? 'Locked' : 'Muat Carton' }}
            </button>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="ld-empty">
            <i class="bi bi-inboxes"></i>
            <p>Belum ada container untuk shipment plan ini.</p>
        </div>
    </div>
@endforelse