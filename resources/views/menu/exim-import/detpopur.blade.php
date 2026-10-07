@extends('layout.main')

@section('js_custom')
    {{-- <script>
        function searchMaterialDetail() {
            let keyword = document.getElementById('caria').value.toLowerCase();
            let rows = document.querySelectorAll('#materialTable tbody tr');

            rows.forEach(function(row) {
                row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
            });
        }

        function openViewPdfModal() {
            document.getElementById('viewPdfModal').style.display = 'block';
        }

        function closeViewPdfModal() {
            document.getElementById('viewPdfModal').style.display = 'none';
        }

        function openPoPdf() {
            let cek = document.querySelector('input[name="pdf_cek"]:checked').value;
            let pono = "{{ $detail->pono }}";

            let url = '';

            if (cek == 1) {
                url = "http://192.168.0.8/EXIM/pdf/pdf_po?po=" + pono + "&cek=" + cek;
            } else {
                url = "http://192.168.0.8/EXIM/pdf/pdf_po2?po=" + pono + "&cek=" + cek;
            }

            window.open(url, '_blank');
            closeViewPdfModal();
        }
    </script> --}}

     <script>
        function searchMaterialDetail() {
            let keyword = document.getElementById('caria').value.toLowerCase();
            let rows = document.querySelectorAll('#materialTable tbody tr');

            rows.forEach(function(row) {
                row.style.display = row.innerText.toLowerCase().includes(keyword) ? '' : 'none';
            });
        }

        function openViewPdfModal() {
            document.getElementById('viewPdfModal').style.display = 'block';
        }

        function closeViewPdfModal() {
            document.getElementById('viewPdfModal').style.display = 'none';
        }

        function openPoPdf() {
            let cek = document.querySelector('input[name="pdf_cek"]:checked').value;
            let pono = "{{ $detail->pono }}";

            let url = '';

            if (cek == 1) {
                url = "http://192.168.0.8/EXIM/pdf/pdf_po?po=" + pono + "&cek=" + cek;
            } else {
                url = "http://192.168.0.8/EXIM/pdf/pdf_po2?po=" + pono + "&cek=" + cek;
            }

            window.open(url, '_blank');
            closeViewPdfModal();
        }
    </script>
@endsection

@section('content')
     {{-- ================= HELPERS (konsisten untuk semua field) ================= --}}
    @php
        function fmtDate($val, $format = 'd M Y') {
            if (blank($val) || $val === '0000-00-00' || $val === '0000-00-00 00:00:00') {
                return '---';
            }
            try {
                return \Carbon\Carbon::parse($val)->format($format);
            } catch (\Exception $e) {
                return '---';
            }
        }

        function fmtText($val, $default = '---') {
            return blank($val) ? $default : $val;
        }
    @endphp

    <div style="padding:40px 30px;">

        <div style="display:flex; align-items:flex-start; gap:18px;">
            <div style="margin-top: 16px">
                <a href="{{ route('exim.import.order') }}">
                    <img src="{{ asset('public/css/images/Vector2.png') }}" style="width:24px; height:24px; cursor:pointer;">
                </a>
            </div>

            <div>
                <div style="font-size:18px; font-weight:700;">
                    PO {{ $detail->pono }}

                    <span
                        style="
                    margin-left:10px;
                    padding:2px 10px;
                    border-radius:5px;
                    background:#19B715;
                    color:#fff;
                    font-size:14px;
                    font-weight:400;">
                        In house
                    </span>

                    <span
                        style="
                    margin-left:4px;
                    padding:2px 10px;
                    border-radius:5px;
                    background:#BDBDBD;
                    color:#fff;
                    font-size:14px;
                    font-weight:400;">
                        {{ fmtText($detail->pourgnm, 'NORMAL') }}
                    </span>
                </div>

                <div style="font-size:14px;">
                    Buyer: {{ fmtText($detail->byrnm, '-') }}, {{ fmtText($detail->season, '-') }}
                </div>

                <div style="font-size:14px;">
                    {{ fmtDate($detail->tglpo) }}
                </div>
            </div>

            <div style="margin-left:35%; display:flex; gap:25px;">
                <a href="javascript:void(0)" onclick="openViewPdfModal()" style="color:#359DD9; text-decoration:none;">
                    View PDF
                </a>

                <a href="http://192.168.0.11/RKB/data/admin?hal=sum&cari1={{ $detail->pono }}" target="_blank"
                    style="color:#359DD9; text-decoration:none;">
                    Status Material
                </a>
            </div>
        </div>

        <div style="margin-top:28px; display:grid; grid-template-columns: repeat(11, 1fr); gap:10px;">

            <div>
                <b style="color:rgba(0,0,0,.6);">Summary</b><br>

                <span style="font-style:normal; font-weight:400; font-size:14px; line-height:18px; width:150px;">
                    Supplier
                </span><br>

                @php
                    $supnmSafe = blank(trim($detail->supnm ?? '')) ? '-' : trim($detail->supnm);
                @endphp

                <a href="javascript:void(0)" title="{{ $supnmSafe }}"
                    style="text-decoration:none; color:#000000; cursor:default; font-size:14px; font-weight:700;">

                    @if (($detail->suppk ?? '') == 1949)
                        <span style="color:rgba(0, 0, 0, 0.38);">
                            {{ \Illuminate\Support\Str::limit($supnmSafe, 10) }}
                        </span>
                    @else
                        {{ \Illuminate\Support\Str::limit($supnmSafe, 10) }}
                    @endif

                </a>

                <input type="hidden" id="ccb2be" value="{{ $detail->suppk ?? '' }}" style="border:none;" readonly>
            </div>

            <div>
                <br>
                <span
                    style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Currency</span><br>
                <b style="font-size: 14px">{{ fmtText($detail->curid) }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">PI received
                    date</span><br>
                <b style="font-size: 14px">{{ fmtDate($detail->pidate) }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Invoice
                    no.</span><br>
                <b style="font-size: 14px">{{ blank($detail->invoiceno) ? '---' : \Illuminate\Support\Str::limit($detail->invoiceno, 10) }}</b>
            </div>

            @php
                $piAmount = $detail->amount ?? 0;
                $totalPi = ($detail->amount ?? 0) + ($detail->surcharge ?? 0) + ($detail->surcharge2 ?? 0);

                $currencySign = $detail->cursign ?? '';

                if (($detail->curid ?? '') == 'USD') {
                    $currencySign = '$';
                } elseif (($detail->curid ?? '') == 'EUR') {
                    $currencySign = '€';
                } elseif (($detail->curid ?? '') == 'GBP') {
                    $currencySign = '£';
                }

                if ($piAmount == 0) {
                    $piText = '---';
                    $totalPiText = '---';
                } else {
                    if (($detail->curid ?? '') == 'IDR') {
                        $piText = $currencySign . number_format($piAmount, 0, '.', ',');
                        $totalPiText = $currencySign . number_format($totalPi, 0, '.', ',');
                    } else {
                        $piText = $currencySign . number_format($piAmount, 2, '.', ',');
                        $totalPiText = $currencySign . number_format($totalPi, 2, '.', ',');
                    }
                }
            @endphp

            <div>
                <br>
                <span>PI amount</span><br>

                <a href="javascript:void(0)" title="{{ $piText }}"
                    style="text-decoration:none; color:#000000; cursor:default;">
                    <b style="font-size:14px;">
                        PI: {{ \Illuminate\Support\Str::limit($piText, 15) }}
                    </b>
                </a><br>

                <a href="javascript:void(0)" title="{{ $totalPiText }}"
                    style="text-decoration:none; color:#000000; cursor:default;">
                    <span style="color:rgba(0, 0, 0, 0.38); font-size:14px; font-weight:700;">
                        Total: {{ \Illuminate\Support\Str::limit($totalPiText, 7) }}
                    </span>
                </a>
            </div>

            <div>
                <br>
                <span style="font-style:normal; font-weight:400; font-size:14px; line-height:18px; width:150px;">
                    PI form
                </span><br>

                @if (!empty($detail->piform))
                    <a href="http://192.168.0.22/GIS/upload/{{ trim($detail->piform) }}" target="_blank"
                        style="font-size:14px; color:#359DD9; text-decoration:none;">
                        Download
                    </a><br>

                    <a href="javascript:void(0)" title="{{ trim($detail->piform) }}"
                        style="text-decoration:none; color:#000000; cursor:default;">
                        <span style="color:rgba(0, 0, 0, 0.38); font-size:14px; font-weight:700;">
                            {{ \Illuminate\Support\Str::limit(trim($detail->piform), 13) }}
                        </span>
                    </a>
                @else
                    <span style="font-size:14px; color:rgba(0,0,0,.38); font-weight:700;">
                        ---
                    </span>
                @endif
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Requested
                    Deliv.</span><br>
                <b style="font-size: 14px">{{ fmtDate($detail->tgldel) }}</b>
            </div>

            <div>
                <b style="color:rgba(0,0,0,.6);">Shipment</b><br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Supplier
                    ETD</span><br>
                <b style="font-size: 14px">{{ fmtDate($detail->etd) }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">ETD from
                    port</span><br>
                <b style="font-size: 14px">{{ fmtDate($etdv2) }}</b>
            </div>

            <div>
                <br>
                <span
                    style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">ETA</span><br>
                <b style="font-size: 14px">{{ fmtDate($eta2) }}</b>
            </div>

            <div>
                <br>
                <span
                    style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">EIH</span><br>
                <b style="font-size: 14px">{{ fmtDate($eih2) }}</b>
            </div>
        </div>

        <div style="margin-top:14px; display:grid; grid-template-columns: repeat(7, 1fr); gap:22px;">
            <div>
                <b style="color:rgba(0,0,0,.6);">Additional details</b><br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Bank
                    surcharge</span><br>
                <b>{{ $detail->surcharge > 0 ? ($detail->cursign ?? '') . ' ' . number_format($detail->surcharge, 2) : '---' }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Other
                    surcharge</span><br>
                <b
                    style="font-size: 14px">{{ $detail->surcharge2 > 0 ? ($detail->cursign ?? '') . ' ' . number_format($detail->surcharge2, 2) : '---' }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Payment
                    terms</span><br>
                <b style="font-size: 14px">{{ fmtText($detail->potop) }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Payment due
                    date</span><br>
                <b style="font-size: 14px">{{ fmtDate($detail->paydate) }}</b>
            </div>

            <div>
                <br>
                <span style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Freight
                    terms</span><br>
                <b style="font-size: 14px">{{ fmtText($detail->freightterm) }}</b>
            </div>

            <div>
                <br>
                <span
                    style="font-style: normal;font-weight: 400;font-size: 14px;line-height:18px; width:150px;">Remarks</span><br>
                <b style="font-size: 14px">{{ fmtText($detail->ket) }}</b>
            </div>
        </div>

        {{-- TAB MATERIAL / SHIPMENT --}}
        <div style="margin-top:28px; float:left; width:100%;">
            <a href="{{ route('exim.import.detpopur', ['po' => $detail->popk]) }}" class="link2"
                style="{{ request()->routeIs('exim.import.detpopur') ? 'border-bottom:3px solid #000000; color:#000000;' : 'color:#BDBDBD;' }}">
                Material details
            </a>

            <a href="{{ route('exim.import.shipdet_detpopur', ['po' => $detail->popk]) }}" class="link2"
                style="{{ request()->routeIs('exim.import.shipdet_detpopur')
                    ? 'border-bottom:3px solid #000000; color:#000000;'
                    : 'color:#BDBDBD;' }}">
                Shipment details
            </a>
        </div>

        {{-- TITLE --}}
        <table style="width:70%; float:left; margin-top:15px;">
            <tr>
                <td
                    style="
                            font-style:normal;
                            font-weight:700;
                            font-size:16px;
                            line-height:20px;
                            color:rgba(0, 0, 0, 0.6);">
                    Purchasing details
                </td>
            </tr>
        </table>

        {{-- SEARCH + PRICE CARDS --}}
        <div style="width:100%; display:flex; align-items:flex-start; gap:16px; margin-top:60px;">

            {{-- SEARCH --}}
            <div
                style="
                        width:175px;
                        height:34px;
                        display:flex;
                        align-items:center;
                        background:#f4f4f4;
                        border-radius:8px;
                        padding:0 10px;
                        flex-shrink:0;
                        margin-top:4px;">
                <img src="{{ asset('public/css/images/Shape.png') }}" width="18" height="18"
                    style="margin-right:8px;">

                <input id="caria" placeholder="Search..." onkeyup="searchMaterialDetail()"
                    style="
                        width:110px;
                        border:none;
                        background:transparent;
                        outline:none;
                        font-size:14px;">
            </div>

            <img src="{{ asset('public/css/images/reload1.png') }}" width="20" height="20"
                onclick="location.reload();" style="margin-top:12px; cursor:pointer;">

            {{-- CARD PER GARMENT --}}
            <div
                style="
                        width:340px;
                        min-height:86px;
                        box-sizing:border-box;
                        background:#FFFFFF;
                        border-radius:8px;
                        box-shadow:0px 0px 16px -4px rgba(0, 0, 0, 0.12);
                        padding:8px 10px;
                        margin-top: -28px;">

                <div style="color:#00000061; font-weight:700; font-size:12px; line-height:16px;">
                    Per garment
                </div>

                <table style="width:100%; margin-top:4px;">
                    <tr>
                        <td style="width:40%; color:#000000; font-size:14px;">RKB price</td>
                        <td style="width:60%; color:#000000; font-size:14px;">Actual price</td>
                    </tr>
                    <tr>
                        <td style="font-weight:700; font-size:14px;">
                            {{ $perRkb ?? 'N/A' }}
                        </td>
                        <td style="font-weight:700; font-size:14px;">
                            {{ $perPi ?? 'N/A' }}

                            @if (isset($profitPerGarment))
                                <span style="color:{{ $profitColor }}; font-size:12px;">
                                    {{ $profitPerGarmentText }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- CARD BULK PURCHASE --}}
            <div
                style="
                        width:340px;
                        min-height:86px;
                        box-sizing:border-box;
                        background:#FFFFFF;
                        border-radius:8px;
                        box-shadow:0px 0px 16px -4px rgba(0, 0, 0, 0.12);
                        padding:8px 10px;
                        margin-top: -28px;">

                <div style="color:#00000061; font-weight:700; font-size:12px; line-height:16px;">
                    Bulk purchase ({{ number_format($bqty ?? 0, 0, '.', ',') }})
                </div>

                <table style="width:100%; margin-top:4px;">
                    <tr>
                        <td style="width:40%; color:#000000; font-size:14px;">RKB price</td>
                        <td style="width:60%; color:#000000; font-size:14px;">Actual price</td>
                    </tr>
                    <tr>
                        <td style="font-weight:700; font-size:14px;">
                            {{ $bulkRkb ?? 'N/A' }}
                        </td>
                        <td style="font-weight:700; font-size:14px;">
                            {{ $bulkPi ?? 'N/A' }}

                            @if (isset($profitBulk))
                                <span style="color:{{ $profitBulkColor }}; font-size:12px;">
                                    {{ $profitBulkText }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- ATTENTION --}}
            @if ($overPrice > 0 || $overQty > 0)
                <div id="container-cari3b"
                    style="
                        width:340px;
                        height:86px;
                        box-sizing:border-box;
                        background:#FFFFFF;
                        border-radius:8px;
                        box-shadow:0px 0px 16px -4px rgba(0, 0, 0, 0.12);
                        padding:8px 10px;
                        margin-top:-28px;
                        overflow:hidden;">

                    <div style="color:#00000061; font-weight:700; font-size:12px; line-height:16px; margin-bottom:6px;">
                        Attention !
                    </div>

                    <div style="font-size:12px; line-height:18px; white-space:nowrap;">
                        <span id="overprice" style="color:#EF3B42;">{{ $overPrice }}</span>
                        <span style="color:#EF3B42;">materials</span>
                        <span style="color:#000000;">are over price in purchase</span>
                    </div>

                    <div style="font-size:12px; line-height:18px; white-space:nowrap; margin-top:4px;">
                        <span id="overqty" style="color:#FF6B00;">{{ $overQty }}</span>
                        <span style="color:#FF6B00;">materials</span>
                        <span style="color:#000000;">are over qty in purchase</span>
                    </div>
                </div>
            @endif

        </div>

        <div style="margin-top:22px; clear:both;">

            <table id="materialTable" style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="border:1px solid #ddd; padding:8px;">No</th>
                        <th style="border:1px solid #ddd; padding:8px;">Subject</th>
                        <th style="border:1px solid #ddd; padding:8px;">Order no</th>
                        <th style="border:1px solid #ddd; padding:8px;">Item</th>
                        <th style="width:200px; border:1px solid #ddd; padding:8px;">Specification</th>
                        <th style="border:1px solid #ddd; padding:8px;">Color</th>
                        <th style="border:1px solid #ddd; padding:8px;">Col code</th>
                        <th style="border:1px solid #ddd; padding:8px;">Size</th>
                        <th style="border:1px solid #ddd; padding:8px;">Units</th>
                        <th style="border:1px solid #ddd; padding:8px;">RKB Qty</th>
                        <th style="border:1px solid #ddd; padding:8px;">RKB PPS</th>
                        <th style="border:1px solid #ddd; padding:8px;">RKB price</th>
                        <th style="border:1px solid #ddd; padding:8px;">RKB Total</th>
                        <th style="border:1px solid #ddd; padding:8px;">PO qty</th>
                        <th style="border:1px solid #ddd; padding:8px;">PO PPS</th>
                        <th style="border:1px solid #ddd; padding:8px;">PO price</th>
                        <th style="border:1px solid #ddd; padding:8px;">PI Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($materials as $item)
                        <tr style="{{ $item->rowColor }}">
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->no }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->rev ?: '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->ordno ?? '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->item ?? '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->spec ?? '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->colname ?: '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->colcode ?: '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->sizegar ?? '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->unit ?? '-' }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->rpocons }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->rqrs }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->rprice }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->rtotal }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->pocons }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->qrs }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->price }}</td>
                            <td style="border:1px solid #ddd; padding:8px;">{{ $item->total }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODAL VIEW PDF --}}
    <div id="viewPdfModal"
        style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,.25);
        z-index:99999;">

        <div
            style="
            width:430px;
            min-height:170px;
            background:#FFFFFF;
            border-radius:8px;
            padding:28px 34px;
            position:absolute;
            top:140px;
            left:50%;
            transform:translateX(-50%);
            box-shadow:0px 4px 18px rgba(0,0,0,.12);">

            <div style="font-size:20px; font-weight:700; margin-bottom:22px;">
                Print PO {{ $detail->pono }}
            </div>

            <div style="display:flex; align-items:center; gap:10px; font-size:14px; margin-bottom:22px;">
                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="radio" name="pdf_cek" value="1" checked>
                    Sum Color
                </label>

                <label style="display:flex; align-items:center; gap:4px; cursor:pointer;">
                    <input type="radio" name="pdf_cek" value="0">
                    Sum Color & Size
                </label>
            </div>

            <div style="display:flex; justify-content:end; align-items:center; gap:28px;">
                <a href="javascript:void(0)" onclick="closeViewPdfModal()"
                    style="
                    text-decoration:none;
                    color:#000000;
                    font-size:14px;
                    font-weight:700;">
                    Cancel
                </a>

                <button type="button" onclick="openPoPdf()"
                    style="
                    width:115px;
                    height:38px;
                    background:#000000;
                    color:#FFFFFF;
                    border:none;
                    border-radius:8px;
                    font-size:14px;
                    cursor:pointer;">
                    Print
                </button>
            </div>
        </div>
    </div>
@endsection
