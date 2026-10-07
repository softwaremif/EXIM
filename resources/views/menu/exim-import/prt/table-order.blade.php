<div id="tabel1" style="margin-top:4px;">
    @if (count($activeFilters))
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2" style="padding-bottom:12px;">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span style="color:#AFAFAF; font-size:14px;">Selected filters</span>

                @foreach ($activeFilters as $filter)
                    <a href="{{ route('exim.import.order') }}?{{ http_build_query($filter['query']) }}"
                        class="badge rounded-pill bg-light text-dark d-inline-flex align-items-center gap-1 border filter-chip-link"
                        style="text-decoration:none; padding:6px 10px; font-weight:400; font-size:13px;">
                        {{ $filter['label'] }}
                        <span aria-hidden="true" style="font-weight:700;">&times;</span>
                    </a>
                @endforeach
            </div>

            <a href="{{ route('exim.import.order') }}" class="clear-all-link"
                style="color:#359DD9; font-size:14px; text-decoration:none; cursor:pointer; flex-shrink:0;">
                Clear All
            </a>
        </div>
    @endif
    <div class="ship-table-scroll" style="{{ $orders->total() == 0 ? 'max-height:250px;' : '' }}">
        <table class="table table-bordered" style="width:100%; font-size:13px; margin-bottom:0;">
            <thead class="ship-thead">
                <tr style="vertical-align:middle;">
                    <th style="width:8px"></th>
                    <th style="width:30px;">
                        <input type="checkbox" id="check-all">
                    </th>
                    <th>PO</th>
                    <th>OP</th>
                    <th>Supplier</th>
                    <th>Buyer</th>
                    <th>Qty</th>
                    <th>Units</th>
                    <th>Payment Term</th>
                    <th>Freight Term</th>
                    {{-- <th style="width:96px">Payment due date</th>
                    <th style="width:96px">Payment date</th> --}}
                    <th style="width:96px">Supplier ETD</th>
                    {{-- <th style="width:96px">Status</th> --}}
                    <th style="width:96px">Status shipment plan</th>
                    {{-- <th style="width:10px"></th> --}}
                </tr>
            </thead>

            <tbody>
                {{-- @foreach ($orders->groupBy(request('chek') == 1 ? 'supnm' : 'tglapv') as $group => $items) --}}
                {{-- @foreach ($orders->getCollection()->groupBy(request('chek') == 1 ? 'supnm' : 'tglapv') as $group => $items)
                    @if ($items->count() > 0)
                        <tr>
                            <td colspan="11" style="font-weight:700; background:#fff;">
                                @if (request('chek') == 1)
                                    {{ $group }}
                                @else
                                    @php
                                        $isEmptyDate =
                                            empty($group) || \Illuminate\Support\Str::startsWith($group, '0000-00-00');
                                    @endphp
                                    @if ($isEmptyDate)
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
                @if ($orders->total() == 0)
                    <tr>
                        <td colspan="12" style="text-align:center; padding:40px 0; border:none;">
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
                    @foreach ($orders->getCollection()->groupBy(request('chek') == 1 ? 'supnm' : fn() => 'all')->all() as $group => $items)
                        @if ($items->count() > 0)
                            @if (request('chek') == 1)
                                <tr>
                                    <td colspan="12" style="font-weight:700; background:#fff;">
                                        {{ $group }}
                                    </td>
                                </tr>
                            @endif

                            @foreach ($items as $order)
                                <tr class="order-row" style="cursor:pointer;">

                                    <td class="{{ $order->tpo2d > 0 ? 'tpo2d-indicator' : '' }}"
                                        style="width:3px; padding:0; margin-left:-10px;">
                                        @if ($order->tpo2d > 0)
                                            <img src="{{ asset('public/css/images/notif2.png') }}" width="3" height="32"
                                                style="margin-top:4px;">
                                        @endif
                                    </td>

                                    <td style="text-align:center; vertical-align:middle;">
                                        {{-- <input type="checkbox" class="order-check" value="{{ $order->popk }}"
                                            data-supnm="{{ $order->supnm }}" data-status="{{ $order->stsimp }}"
                                            onchange="toggleActionBar(); clearOrderNoticeIfNeeded(this);" style="margin:0;"> --}}
                                            <input type="checkbox" class="order-check" value="{{ $order->popk }}"
                                            data-supnm="{{ $order->supnm }}"
                                            data-status="{{ $order->stsimp }}"
                                            data-etd="{{ $order->etd }}"
                                            onchange="toggleActionBar(); clearOrderNoticeIfNeeded(this);"
                                            {{-- onchange="toggleActionBar(); clearOrderNoticeIfNeeded(this); updateShipmentPlanButtonState();" --}}
                                            style="margin:0;">
                                    </td>

                                    <td>
                                        <a href="{{ route('exim.import.detpopur', ['po' => $order->popk]) }}"
                                            style="text-decoration:none; color:#359DD9;">{{ $order->pono }}
                                        </a>
                                        <br>
                                        <span style="font-size:10px; color:#000;">
                                            @if (empty($order->tglpo) || \Illuminate\Support\Str::startsWith($order->tglpo, '0000-00-00'))
                                                -
                                            @else
                                                {{ \Carbon\Carbon::parse($order->tglpo)->format('d/m/Y') }}
                                            @endif
                                        </span>
                                    </td>

                                    {{-- <td>{{ $order->ordno }}</td> --}}
                                    {{-- OP --}}
                                    <td style="max-width:90px; vertical-align:top;">
                                        @php
                                            $opList = $order->ordno ? array_map('trim', explode(',', $order->ordno)) : [];
                                        @endphp

                                        @if (count($opList) > 1)
                                            <div class="po-toggle-wrap" onclick="togglePoExpand(event, this)">
                                                <div class="po-collapsed truncate-text">
                                                    {{ $opList[0] }},...
                                                </div>
                                                <div class="po-expanded" style="display:none;">
                                                    @foreach ($opList as $op)
                                                        <div>{{ $op }}</div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="truncate-text">
                                                {{ $order->ordno ?: '-' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $order->supnm }}</td>
                                    <td>{{ $order->byrnm }}</td>
                                    <td>{{ number_format($order->total ?? 0, 0, '.', ',') }}</td>
                                    <td>{{ $order->unit }}</td>
                                    <td>{{ $order->potop }}</td>
                                    <td>{{ $order->freightterm }}</td>
                                    {{-- <td>
                                        @if (empty($order->paydate) || $order->paydate === '0000-00-00')
                                            -
                                        @else
                                            {{ \Carbon\Carbon::parse($order->paydate)->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if (empty($order->tglbayar) || $order->tglbayar === '0000-00-00')
                                            -
                                        @else
                                            {{ \Carbon\Carbon::parse($order->tglbayar)->format('d M Y') }}
                                        @endif
                                    </td> --}}
                                    {{-- <td>
                                        @if (empty($order->etd) || $order->etd === '0000-00-00')
                                            -
                                        @else
                                            {{ \Carbon\Carbon::parse($order->etd)->format('d M Y') }}
                                        @endif
                                    </td> --}}
                                    <td>
                                        @if (empty($order->etd) || \Illuminate\Support\Str::startsWith($order->etd, '0000-00-00'))
                                            -
                                        @else
                                            {{ \Carbon\Carbon::parse($order->etd)->format('d M Y') }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($order->stsimp == 2)
                                            <span
                                                style="
                                                        padding:2px 4px;
                                                        text-align:center;
                                                        border-radius:5px;
                                                        font-weight:400;
                                                        font-size:14px;
                                                        color:#FFFFFF;
                                                        background:#19B715;
                                                        display:inline-block;
                                                        min-width:90px;">
                                                Processed
                                            </span>
                                        @elseif ($order->stsimp == 1)
                                            <span
                                                style="
                                                        padding:2px 4px;
                                                        text-align:center;
                                                        border-radius:5px;
                                                        font-weight:400;
                                                        font-size:14px;
                                                        color:#FFFFFF;
                                                        background:#FF6B00;
                                                        display:inline-block;
                                                        min-width:90px;">
                                                Partial ship
                                            </span>
                                        @else
                                            <span
                                                style="
                                                        padding:2px 4px;
                                                        text-align:center;
                                                        border-radius:5px;
                                                        font-weight:400;
                                                        font-size:14px;
                                                        color:#FFFFFF;
                                                        background:#BDBDBD;
                                                        display:inline-block;
                                                        min-width:90px;">
                                                On hold
                                            </span>
                                        @endif
                                    </td>
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

    <div style="display:flex; justify-content:space-between; align-items:center;margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <select id="orderPageSize" class="form-select form-select-sm" style="width:80px;"
                onchange="onOrderPageSizeChange()">
                @foreach ([50, 100, 200, 300, 500] as $size)
                    <option value="{{ $size }}" {{ (int) request('rows', 50) === $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>

            <button type="button" class="btn btn-sm btn-light" onclick="goToOrderPage(1)"
                {{ $orders->currentPage() <= 1 ? 'disabled' : '' }}>«</button>
            <button type="button" class="btn btn-sm btn-light" onclick="goToOrderPage({{ $orders->currentPage() - 1 }})"
                {{ $orders->currentPage() <= 1 ? 'disabled' : '' }}>‹</button>

            <div style="display:flex; gap:4px;">
                @foreach ($orders->getUrlRange(max($orders->currentPage() - 2, 1), min($orders->currentPage() + 2, $orders->lastPage())) as $page => $url)
                    <button type="button"
                        class="btn btn-sm {{ $page == $orders->currentPage() ? 'btn-dark' : 'btn-light' }}"
                        onclick="goToOrderPage({{ $page }})">
                        {{ $page }}
                    </button>
                @endforeach
            </div>

            <button type="button" class="btn btn-sm btn-light" onclick="goToOrderPage({{ $orders->currentPage() + 1 }})"
                {{ $orders->currentPage() >= $orders->lastPage() ? 'disabled' : '' }}>›</button>
            <button type="button" class="btn btn-sm btn-light" onclick="goToOrderPage({{ $orders->lastPage() }})"
                {{ $orders->currentPage() >= $orders->lastPage() ? 'disabled' : '' }}>»</button>
        </div>

        <div style="color:#666; font-size:13px;">
            Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
        </div>
    </div>
</div>
