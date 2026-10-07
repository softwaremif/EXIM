<div id="tabel1" style="margin-top:4px;">
    @if (count($activeFilters))
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2" style="padding-bottom:12px;">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span style="color:#AFAFAF; font-size:14px;">Selected filters</span>

                @foreach ($activeFilters as $filter)
                    <a href="{{ route('exim.import.ship') }}?{{ http_build_query($filter['query']) }}"
                        class="badge rounded-pill bg-light text-dark d-inline-flex align-items-center gap-1 border filter-chip-link"
                        style="text-decoration:none; padding:6px 10px; font-weight:400; font-size:13px;">
                        {{ $filter['label'] }}
                        <span aria-hidden="true" style="font-weight:700;">&times;</span>
                    </a>
                @endforeach
            </div>

            <a href="{{ route('exim.import.ship') }}" class="clear-all-link"
                style="color:#359DD9; font-size:14px; text-decoration:none; cursor:pointer; flex-shrink:0;">
                Clear All
            </a>
        </div>
    @endif


    <div class="ship-table-scroll" style="{{ $ships->total() == 0 ? 'max-height:250px;' : '' }}">
        <table class="table table-bordered ship-table" style="width:100%; font-size:13px; margin-bottom:0;">
            <colgroup>
                <col style="width:20px;"> {{-- checkbox --}}
                <col style="width:70px;"> {{-- PO --}}
                <col style="width:100px;"> {{-- Supplier --}}
                <col style="width:100px;"> {{-- Buyer --}}
                <col style="width:70px;"> {{-- Type --}}
                <col style="width:128px;"> {{-- Vassal name --}}
                <col style="width:105px;"> {{-- AirwayBill --}}
                <col style="width:80px;"> {{-- Qty --}}
                <col style="width:70px;"> {{-- Unit --}}
                {{-- <col style="width:90px;">  --}} {{-- Payment terms --}}
                <col style="width:115px;"> {{-- Supplier ETD --}}
                <col style="width:105px;"> {{-- Delivery --}}
                <col style="width:105px;"> {{-- Arrival --}}
                <col style="width:105px;"> {{-- In house --}}
                <col style="width:130px;"> {{-- Status --}}
                <col style="width:40px;"> {{-- Act --}}
                {{-- <col style="width:62px;">  --}}{{-- Action View --}}
                {{-- <col style="width:80px;">  --}}{{-- Action Cancel --}}
                {{-- <col style="width:10px;">  --}} {{-- spacer kanan --}}
            </colgroup>
            {{-- <thead class="ship-thead">
                <tr>
                    <th rowspan="2" style="text-align:center;">
                        <input type="checkbox" id="check-all">
                    </th>

                    <th rowspan="2">Type</th>
                    <th rowspan="2">Vassal name</th>
                    <th rowspan="2">PO</th>
                    <th rowspan="2">AirwayBill</th>
                    <th rowspan="2">Supplier</th>
                    <th rowspan="2">Buyer</th>
                    <th rowspan="2">Qty</th>
                    <th rowspan="2">Unit</th>
                    <th rowspan="2">Payment terms</th>
                    <th rowspan="2">Supplier ETD</th>
                    <th rowspan="2">Delivery</th>
                    <th rowspan="2">Arrival</th>
                    <th rowspan="2">In house</th>
                    <th rowspan="2">Status delivery</th>

                    <th colspan="2" style="text-align:center;"><b>Action</b></th>

                    <th rowspan="2"></th>
                </tr>
                <tr>
                    <th></th>
                    <th></th>
                </tr>
            </thead> --}}
            <thead class="ship-thead">
                <tr>
                    <th style="text-align:center;">
                        <input type="checkbox" id="check-all">
                    </th>
                    <th>PO</th>
                    <th>Supplier</th>
                    <th>Buyer</th>
                    <th>Type</th>
                    <th>Vassal name</th>
                    <th>AirwayBill</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    {{-- <th>Payment terms</th> --}}
                    <th>Supplier ETD</th>
                    <th>Delivery</th>
                    <th>Arrival</th>
                    <th>In house</th>
                    <th>Status shipment</th>
                    <th style="text-align:center;">Act</th>
                </tr>
            </thead>

            <tbody>
                {{-- @foreach ($ships->groupBy($chek == 1 ? 'supnm' : 'group_key') as $group => $items) --}}
                {{-- @foreach ($ships->getCollection()->groupBy($chek == 1 ? 'supnm' : 'group_key') as $group => $items)
                    @if ($items->count() > 0)
                        <tr>
                            <td colspan="15" style="font-weight:700; background:#fff;">
                                @if (request('chek') == 1)
                                    {{ $group }}
                                @else
                                    @if (empty($group) || \Illuminate\Support\Str::startsWith($group, '0000-00-00'))
                                        -
                                    @else
                                        @php
                                            $isToday = \Carbon\Carbon::parse($group)->isToday();
                                        @endphp
                                        {{ $isToday ? 'Today' : \Carbon\Carbon::parse($group)->format('d M Y') }}
                                    @endif
                                @endif
                            </td>
                        </tr> --}}
                @if ($ships->total() == 0)
                        <tr>
                            <td colspan="15" style="text-align:center; padding:40px 0; border:none;">
                                <div style="display:flex; flex-direction:column; align-items:center; gap:10px;">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21 21l-4.35-4.35" stroke="#BDBDBD" stroke-width="1.8" stroke-linecap="round"/>
                                        <circle cx="10.5" cy="10.5" r="7.5" stroke="#BDBDBD" stroke-width="1.8"/>
                                    </svg>
                                    <span style="color:#8A8A8A; font-size:14px;">No data found</span>
                                </div>
                            </td>
                        </tr>
                @else
                    @foreach ($ships->getCollection()->groupBy($chek == 1 ? 'supnm' : fn() => 'all')->all() as $group => $items)
                        @if ($items->count() > 0)
                            @if ($chek == 1)
                                <tr>
                                    <td colspan="15" style="font-weight:700; background:#fff;">
                                        {{ $group }}
                                    </td>
                                </tr>
                            @endif

                            @foreach ($items as $ship)
                            {{-- @foreach ($items->sortByDesc('ETDV') as $ship) --}}
                                @php
                                    $statusText = 'On hold';
                                    $statusColor = '#BDBDBD';

                                    if ($ship->stsimpk == 1) {
                                        $statusText = 'En Route';
                                        $statusColor = '#333333';
                                    } elseif ($ship->stsimpk == 2) {
                                        $statusText = 'Arrived at Port';
                                        $statusColor = '#359DD9';
                                    } elseif ($ship->stsimpk == 3) {
                                        $statusText = 'In house';
                                        $statusColor = '#19B715';
                                    }

                                    // $etdText =
                                    //     !empty($ship->ETDV) && $ship->ETDV != '0000-00-00'
                                    //         ? \Carbon\Carbon::parse($ship->ETDV)->format('d/m/Y')
                                    //         : '-';

                                    // Arrival: ETD & ATD
                                    $etdvText = !empty($ship->ETDV) && $ship->ETDV != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->ETDV)->format('d/m/y')
                                        : '-';

                                    $atdText = !empty($ship->ATD) && $ship->ATD != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->ATD)->format('d/m/y')
                                        : '-';

                                    // Arrival: ETA & ATA
                                    $etaText = !empty($ship->ETDA) && $ship->ETDA != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->ETDA)->format('d/m/y')
                                        : '-';

                                    $ataText = !empty($ship->ATA) && $ship->ATA != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->ATA)->format('d/m/y')
                                        : '-';

                                    // In house: EIH & AIH
                                    $eihText = !empty($ship->EIH) && $ship->EIH != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->EIH)->format('d/m/y')
                                        : '-';

                                    $ihaText = !empty($ship->iha_actual) && $ship->iha_actual != '0000-00-00'
                                    ? \Carbon\Carbon::parse($ship->iha_actual)->format('d/m/y')
                                    :'-';

                                    $qtyText = isset($ship->qtys) ? number_format($ship->qtys, 0, '.', ',') : '-';

                                    // Nilai full-year (d/m/Y) untuk keperluan prefill & validasi bulk edit dates
                                    $etaFullText = !empty($ship->ETDA) && $ship->ETDA != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->ETDA)->format('d/m/Y')
                                        : '';

                                    $eihFullText = !empty($ship->EIH) && $ship->EIH != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->EIH)->format('d/m/Y')
                                        : '';

                                    $ataFullText = !empty($ship->ATA) && $ship->ATA != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->ATA)->format('d/m/Y')
                                        : '';

                                    $aihFullText = !empty($ship->iha_actual) && $ship->iha_actual != '0000-00-00'
                                        ? \Carbon\Carbon::parse($ship->iha_actual)->format('d/m/Y')
                                        : '';
                                @endphp

                                {{-- <tr class="ship-row" data-importpk="{{ $ship->importpk }}" onclick="handleRowClick(event, this)"
                                    style="cursor:pointer;"> --}}
                                <tr class="ship-row" data-importpk="{{ $ship->importpk }}"
                                    data-vassalnm="{{ trim($ship->vassalnm ?? '') }}"
                                    data-eta="{{ $etaFullText }}"
                                    data-eih="{{ $eihFullText }}"
                                    data-ata="{{ $ataFullText }}"
                                    data-aih="{{ $aihFullText }}"
                                    onclick="handleRowClick(event, this)" style="cursor:pointer;">

                                    {{-- 1 checkbox --}}
                                    <td style="text-align:center; vertical-align:middle;">
                                        <input type="checkbox" class="ship-check" value="{{ $ship->importpk }}"
                                            onchange="toggleActionBar()" style="margin:0;">
                                    </td>

                                    {{-- 4 PO --}}
                                    {{-- <td>{{ $ship->pono_display ?? ($ship->pono ?? '-') }}</td> --}}
                                    <td style="max-width:90px; vertical-align:top;">
                                        @php
                                            $poList = $ship->pono ? array_map('trim', explode(',', $ship->pono)) : [];
                                        @endphp

                                        @if (count($poList) > 1)
                                            <div class="po-toggle-wrap" onclick="togglePoExpand(event, this)">
                                                <div class="po-collapsed truncate-text">
                                                    {{ $poList[0] }},...
                                                </div>
                                                <div class="po-expanded" style="display:none;">
                                                    @foreach ($poList as $po)
                                                        <div>{{ $po }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="truncate-text">
                                                {{ $ship->pono_display ?: '-' }}
                                            </div>
                                        @endif
                                    </td>
                                    {{-- <td style="max-width:90px; vertical-align:top;">
                                        @php
                                            $poList = $ship->pono ? array_map('trim', explode(',', $ship->pono)) : [];

                                            $dateCreateText = !empty($ship->datecreate) && !\Illuminate\Support\Str::startsWith($ship->datecreate, '0000-00-00')
                                                ? \Carbon\Carbon::parse($ship->datecreate)->format('d/m/Y')
                                                : '-';
                                        @endphp

                                        @if (count($poList) > 1)
                                            <div class="po-toggle-wrap" onclick="togglePoExpand(event, this)">
                                                <div class="po-collapsed truncate-text">
                                                    {{ $poList[0] }},...
                                                    <div style="font-size:11px; color:#666;">{{ $dateCreateText }}</div>
                                                </div>
                                                <div class="po-expanded" style="display:none;">
                                                    @foreach ($poList as $po)
                                                        <div>
                                                            {{ $po }}
                                                            <div style="font-size:11px; color:#666;">{{ $dateCreateText }}</div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="truncate-text">
                                                {{ $ship->pono_display ?: '-' }}
                                                <div style="font-size:11px; color:#666;">{{ $dateCreateText }}</div>
                                            </div>
                                        @endif
                                    </td> --}}

                                    {{-- 6 Supplier --}}
                                    <td>{{ $ship->supnm ?: '-' }}</td>

                                    {{-- 7 Buyer --}}
                                    <td>{{ $ship->byrnm ?: '-' }}</td>

                                    {{-- 2 Type --}}
                                    {{-- <td>{{ $ship->typenm ?: '-' }}</td> --}}
                                    <td>
                                        {{ $ship->typenm ?: '-' }}
                                        @php
                                            $dateCreateText = !empty($ship->datecreate) && !\Illuminate\Support\Str::startsWith($ship->datecreate, '0000-00-00')
                                                ? \Carbon\Carbon::parse($ship->datecreate)->format('d/m/y')
                                                : '-';
                                        @endphp
                                        <div style="font-size:10px; color:#666;">{{ $dateCreateText }}</div>
                                    </td>

                                    {{-- 3 Vassal name --}}
                                    <td>{{ $ship->vassalnm ?: '-' }}</td>

                                    {{-- 5 AirwayBill --}}
                                    <td>{{ $ship->AWB ?: '-' }}</td>

                                    {{-- 8 Qty --}}
                                    <td>{{ $qtyText }}</td>

                                    {{-- 9 Unit --}}
                                    <td>{{ $ship->unit ?: '-' }}</td>

                                    {{-- 10 Payment terms --}}
                                    {{-- <td>{{ $ship->potop ?: '-' }}</td> --}}

                                    {{-- 11 Supplier ETD --}}
                                    <td style="font-size:12px; line-height:1.4;">
                                        @if (empty($ship->etd) || \Illuminate\Support\Str::startsWith($ship->etd, '0000-00-00'))
                                            -
                                        @else
                                            {{ \Carbon\Carbon::parse($ship->etd)->format('d M y') }}
                                        @endif
                                    </td>

                                    {{-- 12 Delivery --}}
                                    <td style="font-size:10px; line-height:1.4;">
                                        E: {{ $etdvText }}<br>
                                        A: {{ $atdText }}
                                    </td>

                                    {{-- 13 Arrival --}}
                                    <td style="font-size:10px; line-height:1.4;">
                                        E: {{ $etaText }}<br>
                                        A: {{ $ataText }}
                                    </td>

                                    {{-- 14 In house --}}
                                    <td style="font-size:10px; line-height:1.4;">
                                        E: {{ $eihText }}<br>
                                        A: {{ $ihaText }}
                                    </td>

                                    {{-- 15 Status --}}
                                    <td>
                                        <span
                                            style="
                                                    padding:2px 4px;
                                                    text-align:center;
                                                    border-radius:6px;
                                                    font-weight:400;
                                                    font-size:14px;
                                                    color:#FFFFFF;
                                                    background:{{ $statusColor }};
                                                    display:inline-block;
                                                    min-width:90px;">
                                            {{ $statusText }}
                                        </span>
                                    </td>

                                    {{-- 16 Act --}}
                                    {{-- @php
                                        // On hold = stsimpk belum 1 (En Route), 2 (Arrived), atau 3 (In house)
                                        $isOnHold = !in_array($ship->stsimpk, [1, 2, 3]);
                                    @endphp
                                    <td style="text-align:right; position:relative;">
                                        <div class="dropdown">
                                            <button type="button" class="btn dropdown-toggle-custom-sortlist pt-0"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <img src="{{ asset('public/css/images/More.png') }}" height="15px" width="5px">
                                            </button>
                                            <ul class="dropdown-menu pt-1 pb-1">
                                                <li>
                                                    <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)"
                                                        onclick="openShipmentPlan({{ $ship->importpk }})" style="color:#359DD9;">
                                                        View
                                                    </a>
                                                </li>

                                                <li><hr class="dropdown-divider mt-1 mb-1"></li>
                                                <li>
                                                    <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)"
                                                        onclick="openShipmentDatesModal({{ $ship->importpk }})" style="color:#359DD9;">
                                                        Edit Shipment dates
                                                    </a>
                                                </li>

                                                @if (Session::get('login') == 'exim' || Session::get('login') == 'import')
                                                    <li><hr class="dropdown-divider mt-1 mb-1"></li>
                                                    <li>
                                                        @if ($isOnHold)
                                                            <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)"
                                                                onclick="cancelShipment({{ $ship->importpk }})" style="color:#359DD9;">
                                                                Cancel
                                                            </a>
                                                        @else
                                                            <a class="dropdown-item dropdown-item-custom text-muted"
                                                                style="pointer-events:none; opacity:0.5;" title="Shipment sudah diproses">
                                                                Cancel
                                                            </a>
                                                        @endif
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td> --}}
                                    @php
                                        // On hold = stsimpk belum 1 (En Route), 2 (Arrived), atau 3 (In house)
                                        $isOnHold = !in_array($ship->stsimpk, [1, 2, 3]);
                                    @endphp
                                    <td style="text-align:right; position:relative;">
                                        <div class="dropdown">
                                            <button type="button" class="btn dropdown-toggle-custom-sortlist pt-0"
                                                data-bs-toggle="dropdown" aria-expanded="false">
                                                <img src="{{ asset('public/css/images/More.png') }}" height="15px" width="5px">
                                            </button>
                                            <ul class="dropdown-menu pt-1 pb-1">
                                                <li>
                                                    <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)"
                                                        onclick="openShipmentPlan({{ $ship->importpk }})" style="color:#359DD9;">
                                                        View
                                                    </a>
                                                </li>

                                                @if (Session::get('login') == 'exim' || Session::get('login') == 'import')
                                                    <li><hr class="dropdown-divider mt-1 mb-1"></li>
                                                    <li>
                                                        <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)"
                                                            onclick="openShipmentDatesModal({{ $ship->importpk }})" style="color:#359DD9;">
                                                            Edit Shipment dates
                                                        </a>
                                                    </li>

                                                    <li><hr class="dropdown-divider mt-1 mb-1"></li>
                                                    <li>
                                                        @if ($isOnHold)
                                                            <a class="dropdown-item dropdown-item-custom" href="javascript:void(0)"
                                                                onclick="cancelShipment({{ $ship->importpk }})" style="color:#359DD9;">
                                                                Cancel
                                                            </a>
                                                        @else
                                                            <a class="dropdown-item dropdown-item-custom text-muted"
                                                                style="pointer-events:none; opacity:0.5;" title="Shipment sudah diproses">
                                                                Cancel
                                                            </a>
                                                        @endif
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>

                                    {{-- 16 Action View --}}
                                    {{-- <td style="text-align:center;">
                                        <a href="javascript:void(0)" onclick="openShipmentPlan({{ $ship->importpk }})"
                                            style="text-decoration:none; color:#359DD9;">
                                            View
                                        </a>
                                    </td> --}}

                                    {{-- 17 Action Cancel --}}
                                    {{-- <td style="text-align:center; position:relative; overflow:visible;">

                                        @if (Session::get('login') == 'exim' || Session::get('login') == 'import')

                                            @php
                                                // On hold = stsimpk belum 1 (En Route), 2 (Arrived), atau 3 (In house)
                                                $isOnHold = !in_array($ship->stsimpk, [1, 2, 3]);
                                            @endphp

                                            @if ($isOnHold)
                                                <a href="javascript:void(0)"
                                                    onclick="cancelShipment({{ $ship->importpk }})"
                                                    class="danger"
                                                    style="text-decoration:none; color:#359DD9; cursor:pointer;">
                                                    Cancel
                                                </a>
                                            @else
                                                <span style="color:#BDBDBD; cursor:not-allowed;" title="Shipment sudah diproses">
                                                    Cancel
                                                </span>
                                            @endif

                                        @endif

                                    </td> --}}

                                    {{-- 18 spacer kanan --}}
                                    {{-- <td class="no-border-y" style="position:relative;">
                                        <div
                                            style="
                                    position:absolute;
                                    top:-1px;
                                    bottom:-1px;
                                    left:0;
                                    right:0;
                                    background:#fff;
                                    z-index:1;">
                                        </div>
                                    </td> --}}
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    <div style="
        width:100%;
        margin-bottom:16px;">
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <select id="shipPageSize" class="form-select form-select-sm" style="width:80px;"
                onchange="onShipPageSizeChange()">
                @foreach ([50, 100, 200, 300, 500] as $size)
                    <option value="{{ $size }}" {{ (int) request('rows', 50) === $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>

            <button type="button" class="btn btn-sm btn-light" onclick="goToShipPage(1)"
                {{ $ships->currentPage() <= 1 ? 'disabled' : '' }}>«</button>
            <button type="button" class="btn btn-sm btn-light" onclick="goToShipPage({{ $ships->currentPage() - 1 }})"
                {{ $ships->currentPage() <= 1 ? 'disabled' : '' }}>‹</button>

            <div style="display:flex; gap:4px;">
                @foreach ($ships->getUrlRange(max($ships->currentPage() - 2, 1), min($ships->currentPage() + 2, max($ships->lastPage(), 1))) as $page => $url)
                    <button type="button"
                        class="btn btn-sm {{ $page == $ships->currentPage() ? 'btn-dark' : 'btn-light' }}"
                        onclick="goToShipPage({{ $page }})">
                        {{ $page }}
                    </button>
                @endforeach
            </div>

            <button type="button" class="btn btn-sm btn-light" onclick="goToShipPage({{ $ships->currentPage() + 1 }})"
                {{ $ships->currentPage() >= $ships->lastPage() ? 'disabled' : '' }}>›</button>
            <button type="button" class="btn btn-sm btn-light" onclick="goToShipPage({{ $ships->lastPage() }})"
                {{ $ships->currentPage() >= $ships->lastPage() ? 'disabled' : '' }}>»</button>
        </div>

        <div style="color:#666; font-size:13px;">
            Showing {{ $ships->firstItem() ?? 0 }}–{{ $ships->lastItem() ?? 0 }} of {{ $ships->total() }} shipments
        </div>
    </div>
</div>
