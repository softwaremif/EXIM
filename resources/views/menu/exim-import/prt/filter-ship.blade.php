<div class="contenta" id="filterBarWrapper">
    <form method="GET" action="{{ route('exim.import.ship') }}" id="filterForm">
        <input type="hidden" name="chek" id="chek" value="{{ request('chek', 0) }}">
        <input type="hidden" id="cari5" name="cari5" value="{{ request('cari5') }}">
        <input type="hidden" id="cari6" name="cari6" value="{{ request('cari6') }}">
        <input type="hidden" id="cari7" name="cari7" value="{{ request('cari7') }}">
        <input type="hidden" id="cari8" name="cari8" value="{{ request('cari8') }}">
        <input type="hidden" id="cari9" name="cari9" value="{{ request('cari9') }}">
        <input type="hidden" id="page" name="page" value="{{ request('page', 1) }}">
        <input type="hidden" id="rows" name="rows" value="{{ request('rows', 50) }}">

        <div id="frame-utama-cari" style="width:750px; justify-content:flex-start;">
            <div id="container-cari" style="position:relative; left:0; top:0; right:auto; bottom:auto; width:174px; height:30px; flex-shrink:0;">
                <div id="frame-cari">
                    <img src="{{ asset('public/css/images/Shape.png') }}" width="18" height="18">

                    {{-- <input id="cari1" name="cari1" placeholder="Search..." class="input"
                        value="{{ request('cari1') }}" onkeyup="submitFilterDelay()"> --}}
                    <input id="cari1" name="cari1" placeholder="Search..." class="input"
                    value="{{ request('cari1') }}"
                    onkeyup="document.getElementById('page').value = 1; submitFilterDelay()">
                </div>
            </div>

            {{-- STATUS --}}
            <div class="custom-dropdown" style="width:150px;">
                <button type="button" class="custom-dropdown-trigger"
                    onclick="toggleCustomDropdown(event, 'statusDropdownMenu')">
                    <span id="statusDropdownLabel" style="color:{{ count($currentStatuses) === 0 ? '#666666' : '#000' }};">{{ $statusDropdownLabel }}</span>
                    <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                </button>

                <div id="statusDropdownMenu" style="width:150px;" class="custom-dropdown-menu">
                    @foreach ($statusOptions as $value => $label)
                        <label class="custom-dropdown-item d-flex align-items-center gap-1"
                            style="cursor:pointer; margin:0;">
                            {{-- <input type="checkbox" name="cari2[]" value="{{ $value }}"
                                {{ in_array((string) $value, $currentStatuses, true) ? 'checked' : '' }}
                                onchange="submitFilter()"> --}}
                            <input type="checkbox" name="cari2[]" value="{{ $value }}"
                            {{ in_array((string) $value, $currentStatuses, true) ? 'checked' : '' }}
                            onchange="document.getElementById('page').value = 1; submitFilter()">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- SHIPMENT ETD DATE RANGE --}}
            <div class="custom-dropdown" style="width:150px;">
                <input type="hidden" name="cari4" id="cari4" value="{{ $currentDateRange }}">

                <button type="button" class="custom-dropdown-trigger"
                    onclick="toggleCustomDropdown(event, 'yearDropdownMenu')">
                    <span id="yearDropdownLabel" style="color:{{ request()->filled('cari4') ? '#000' : '#666666' }};">{{ $currentDateRangeLabel }}</span>
                    <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                </button>

                <div id="yearDropdownMenu" style="width:150px;" class="custom-dropdown-menu">
                    @foreach ($dateRangeOptions as $value => $label)
                        <div class="custom-dropdown-item {{ $currentDateRange === $value ? 'selected' : '' }}"
                            onclick="selectCustomOption('cari4', 'yearDropdownLabel', 'yearDropdownMenu', '{{ $value }}', '{{ $label }}', this)">
                            {{ $label }}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SHOW BY SUPPLIER --}}
            <button type="button" id="bysup" onclick="toggleSupplier()"
                style="
                    width:142px;
                    height:34px;
                    background-color:{{ request('chek') == 1 ? '#359DD9' : '#FFFFFF' }};
                    color:{{ request('chek') == 1 ? '#FFFFFF' : '#000000' }};
                    border:1px solid #D9D9D9;
                    border-radius:8px;
                    cursor:pointer;
                    flex-shrink:0;
                    font-size:14px;">
                Show by Supplier
            </button>

            <a href="{{ route('exim.import.ship.export') }}?{{ http_build_query(request()->except(['page', 'rows'])) }}"
                id="exportShipBtn"
                style="
                    width:100px;
                    height:34px;
                    background-color:#000000;
                    color:#FFFFFF;
                    border-radius:8px;
                    cursor:pointer;
                    flex-shrink:0;
                    font-size:14px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    text-decoration:none;">
                Export
            </a>

            <a href="{{ route('exim.import.ship') }}?{{ http_build_query(request()->all()) }}" id="reloadShipTableBtn"
                title="Reload table" style="flex-shrink:0;">
                <img src="{{ asset('public/css/images/reload1.png') }}" width="20" height="20">
            </a>
        </div>
    </form>
</div>
