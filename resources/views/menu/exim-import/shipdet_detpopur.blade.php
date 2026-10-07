@extends('layout.main')

@section('css_custom')
    <style>
        #DgImport td,
        #DgImport th {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            vertical-align: top;
        }

        /* PAKSA section langsung di dalam modal-body mengikuti flow block normal,
        KECUALI #tabel2b (dikontrol JS) dan .row (butuh flex) */
        #shipmentPlanModal .modal-body>div:not(#tabel2b):not(.row) {
            position: static !important;
            float: none !important;
            display: block !important;
            width: 100% !important;
            clear: both !important;
        }

        /* #tabel2b ikut flow normal, visibility diserahkan ke JS/inline style */
        #tabel2b {
            position: static !important;
            float: none !important;
            width: 100% !important;
            clear: both !important;
        }

        /* .row tetap flex supaya .col sejajar horizontal */
        #shipmentPlanModal .modal-body .row {
            display: flex !important;
            flex-wrap: wrap !important;
            width: 100% !important;
            clear: both !important;
        }

        .ship-thead th {
            background: #F5F5F5 !important;
            vertical-align: middle;
        }
    </style>
@endsection

@section('js_custom')
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
            let url = cek == 1 ?
                "http://192.168.0.8/EXIM/pdf/pdf_po?po=" + pono + "&cek=" + cek :
                "http://192.168.0.8/EXIM/pdf/pdf_po2?po=" + pono + "&cek=" + cek;
            window.open(url, '_blank');
            closeViewPdfModal();
        }

        // function openShipmentPlanModal(
        //     shipLabel, supplier, importpk, countPono,
        //     vassalType, vassalName, awb, arrivalCity, portName,
        //     etd, eta, eih
        // ) {
        //     document.getElementById('modal_importpk').value = importpk ?? '';
        //     document.getElementById('modal_supplier').textContent = supplier ?? '-';
        //     document.getElementById('countpono').textContent = countPono ?? 0;

        //     document.getElementById('modal_vassal_type').textContent = vassalType ?? '-';
        //     document.getElementById('modal_vassal_name').textContent = vassalName ?? '-';
        //     document.getElementById('modal_awb').textContent = awb ?? '-';
        //     document.getElementById('modal_arrival_city').textContent = arrivalCity ?? '-';
        //     document.getElementById('modal_port_name').textContent = portName ?? '-';

        //     document.getElementById('modal_etd').textContent = etd ?? '-';
        //     document.getElementById('modal_eta').textContent = eta ?? '-';
        //     document.getElementById('modal_eih').textContent = eih ?? '-';

        //     // reset state tabel detail PO setiap modal dibuka ulang
        //     const tabel = document.getElementById('tabel2b');
        //     tabel.style.display = 'none';
        //     document.getElementById('shw').style.display = 'inline';
        //     document.getElementById('hdn').style.display = 'none';
        //     document.getElementById('dgImportBody').innerHTML =
        //         '<tr><td colspan="7" style="color:#999;">Belum ada detail PO.</td></tr>';

        //     loadShipmentLogs(importpk);

        //     let modal = new bootstrap.Modal(document.getElementById('shipmentPlanModal'));
        //     modal.show();
        // }

        function openShipmentPlanModal(
            shipLabel, supplier, importpk, countPono,
            vassalType, vassalName, awb, arrivalCity, portName,
            etd, eta, eih, atd, ata, aih
        ) {
            document.getElementById('modal_importpk').value = importpk ?? '';
            document.getElementById('modal_supplier').textContent = supplier ?? '-';
            document.getElementById('countpono').textContent = countPono ?? 0;

            document.getElementById('modal_vassal_type').textContent = vassalType ?? '-';
            document.getElementById('modal_vassal_name').textContent = vassalName ?? '-';
            document.getElementById('modal_awb').textContent = awb ?? '-';
            document.getElementById('modal_arrival_city').textContent = arrivalCity ?? '-';
            document.getElementById('modal_port_name').textContent = portName ?? '-';

            document.getElementById('modal_etd').textContent = etd ?? '-';
            document.getElementById('modal_eta').textContent = eta ?? '-';
            document.getElementById('modal_eih').textContent = eih ?? '-';

            document.getElementById('modal_atd').textContent = atd ?? '-';
            document.getElementById('modal_ata').textContent = ata ?? '-';
            document.getElementById('modal_aih').textContent = aih ?? '-';

            // reset state tabel detail PO setiap modal dibuka ulang
            const tabel = document.getElementById('tabel2b');
            tabel.style.display = 'none';
            document.getElementById('shw').style.display = 'inline';
            document.getElementById('hdn').style.display = 'none';
            document.getElementById('dgImportBody').innerHTML =
                '<tr><td colspan="7" style="color:#999;">Belum ada detail PO.</td></tr>';

            loadShipmentLogs(importpk);

            let modal = new bootstrap.Modal(document.getElementById('shipmentPlanModal'));
            modal.show();
        }

        function loadShipmentLogs(importpk) {
            const tbody = document.getElementById('shipment-log-body');

            if (!importpk) {
                tbody.innerHTML = `<tr><td colspan="2" style="color:#999;">Belum ada log.</td></tr>`;
                return;
            }

            fetch(`{{ route('exim.import.shipmentLogs', ':importpk') }}`.replace(':importpk', importpk))
                .then(res => res.json())
                .then(res => {
                    if (!res.success || !res.logs.length) {
                        tbody.innerHTML = `<tr><td colspan="2" style="color:#999;">Belum ada log.</td></tr>`;
                        return;
                    }

                    tbody.innerHTML = res.logs.map(log => `
                <tr>
                    <td>${log.date ?? '-'}</td>
                    <td>${log.changes ?? '-'}</td>
                </tr>
            `).join('');
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = `<tr><td colspan="2" style="color:#999;">Gagal memuat log.</td></tr>`;
                });
        }

        function viewdtl() {
            const tabel = document.getElementById('tabel2b');
            const shw = document.getElementById('shw');
            const hdn = document.getElementById('hdn');
            const importpk = document.getElementById('modal_importpk').value;

            const isHidden = tabel.style.display === 'none' || tabel.style.display === '';

            if (isHidden) {
                tabel.style.display = 'block';
                shw.style.display = 'none';
                hdn.style.display = 'inline';
                loadDetailPo(importpk);
            } else {
                tabel.style.display = 'none';
                shw.style.display = 'inline';
                hdn.style.display = 'none';
            }
        }

        function loadDetailPo(importpk) {
            const tbody = document.getElementById('dgImportBody');
            tbody.innerHTML = '<tr><td colspan="7" style="color:#999;">Memuat data...</td></tr>';

            fetch(`{{ url('/exim/import/detail-po') }}/${importpk}`)
                .then(res => res.json())
                .then(json => {
                    if (!json.success || !json.data.length) {
                        tbody.innerHTML = '<tr><td colspan="7" style="color:#999;">Belum ada detail PO.</td></tr>';
                        return;
                    }
                    renderDetailPoTable(json.data);
                })
                .catch(() => {
                    tbody.innerHTML = '<tr><td colspan="7" style="color:#999;">Gagal memuat detail PO.</td></tr>';
                });
        }

        function renderDetailPoTable(rows) {
            const tbody = document.getElementById('dgImportBody');
            tbody.innerHTML = '';

            const grouped = rows.reduce((acc, row) => {
                (acc[row.pono] = acc[row.pono] || []).push(row);
                return acc;
            }, {});

            Object.keys(grouped).forEach(pono => {
                tbody.innerHTML +=
                    `<tr><td colspan="7" style="font-weight:700;">${pono}</td></tr>`;

                grouped[pono].forEach(item => {
                    const itemCode = (item.item ?? '-').split(' - ')[0];
                    tbody.innerHTML += `
                <tr>
                    <td title="${item.item ?? '-'}">${itemCode}</td>
                    <td>${item.spec ?? '-'}</td>
                    <td>${item.supnm ?? '-'}</td>
                    <td>${item.colname ?? '-'}</td>
                    <td>$ ${Number(item.price ?? 0).toFixed(2)}</td>
                    <td>${item.qtypo ?? 0} ${item.unit ?? 'PCS'}</td>
                    <td>${Number(item.shipment_qty ?? 0).toFixed(2)}</td>
                </tr>`;
                });
            });
        }
    </script>
@endsection

@section('content')
    {{-- ================= HELPERS (konsisten untuk semua field) ================= --}}
    @php
        function fmtDate($val, $format = 'd M Y') {
            if (blank($val)) {
                return '---';
            }

            $valStr = trim((string) $val);

            if ($valStr === '0000-00-00' || $valStr === '0000-00-00 00:00:00' || $valStr === '0' || $valStr === '') {
                return '---';
            }

            try {
                $date = \Carbon\Carbon::parse($val);

                if ($date->year < 1000) {
                    return '---';
                }

                return $date->format($format);
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
        <div style="margin-top:22px; float:left; width:100%;">
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

        <div style="margin-top:64px; clear:both;">

            <table id="materialTable" style="width:100%; border-collapse:collapse; font-size:14px;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="border:1px solid #ddd; padding:8px;">Specification</th>
                        <th style="border:1px solid #ddd; padding:8px;">Color</th>
                        <th style="border:1px solid #ddd; padding:8px;">Units</th>
                        <th style="border:1px solid #ddd; padding:8px;">Qty</th>
                        @for ($i = 1; $i <= $tpo; $i++)
                            <th style="border:1px solid #ddd; padding:8px;">
                                @if ($i == 1)
                                    1st Shipment
                                @elseif($i == 2)
                                    2nd Shipment
                                @elseif($i == 3)
                                    3rd Shipment
                                @else
                                    {{ $i }}th Shipment
                                @endif
                            </th>
                        @endfor
                        <th style="border:1px solid #ddd; padding:8px;">Balance</th>
                        <th style="border:1px solid #ddd; padding:8px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @if ($materials->count())
                        @foreach ($materials as $material)
                            @php
                                $materialShipments = $shipmentMap[$material->podtpk] ?? [];
                                $totalShipment = array_sum($materialShipments);
                                $balance = $totalShipment - $material->pocons;
                            @endphp

                            <tr>
                                <td style="border:1px solid #ddd; padding:8px;">
                                    {{ $material->spec }}
                                </td>

                                <td style="border:1px solid #ddd; padding:8px;">
                                    {{ $material->colname }}
                                </td>

                                <td style="border:1px solid #ddd; padding:8px;">
                                    {{ $material->unit }}
                                </td>

                                <td style="border:1px solid #ddd; padding:8px;">
                                    {{ number_format($material->pocons, 2) }}
                                </td>

                                @for ($i = 0; $i < $tpo; $i++)
                                    <td style="border:1px solid #ddd; padding:8px;">
                                        {{ isset($materialShipments[$i]) ? number_format($materialShipments[$i], 2) : '-' }}
                                    </td>
                                @endfor

                                <td style="border:1px solid #ddd; padding:8px;">
                                    @if ($balance > 0)
                                        <span style="color:#00b050; font-weight:bold;">
                                            +{{ number_format($balance, 2) }}
                                        </span>
                                    @elseif($balance < 0)
                                        <span style="color:#ff0000; font-weight:bold;">
                                            {{ number_format($balance, 2) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="no-border-y" style="position:relative; border-right:1px solid #ddd;">
                                    <div
                                        style="
                                            position:absolute;
                                            top:-1px;
                                            bottom:-1px;
                                            left:0;
                                            right:0;
                                            z-index:1;">
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="{{ 6 + $tpo }}" style="padding:20px; border:1px solid #ddd;">
                                <div class="ms-5 d-grid">
                                    <div class="txt-000000-400-14-18-38 p-0"
                                        style="line-height:28px; color: var(--Text-Light-grey, rgba(0, 0, 0, 0.38));">
                                        No data available. Please check in again later.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div
                style="
                        width:100%;
                        border-bottom:1px solid #D9D9D9;
                        margin-bottom:24px;">
            </div>

        </div>

        <div class="row mt-4">
            <div class="col"
                style="font-style:normal; font-weight:700; font-size:16px; line-height:20px; color:rgba(0, 0, 0, 0.6);">
                Vassal Details
            </div>
        </div>

        <div class="mt-3">
            @forelse ($shipmentDetails as $index => $ship)
                @php
                    $no = $index + 1;

                    if ($no == 1) {
                        $shipLabel = '1st Shipment';
                    } elseif ($no == 2) {
                        $shipLabel = '2nd Shipment';
                    } elseif ($no == 3) {
                        $shipLabel = '3rd Shipment';
                    } else {
                        $shipLabel = $no . 'th Shipment';
                    }

                    $statusText = 'Schedule';
                    $statusColor = '#858585';
                    $dateLabel = 'Create on';
                    $dateValue = $ship->datecreate;

                    if ($ship->stsimpk == 1) {
                        $statusText = 'En route';
                        $statusColor = '#333333';
                        $dateLabel = 'Departed on';
                        $dateValue = $ship->ETDV;
                    } elseif ($ship->stsimpk == 2) {
                        $statusText = 'Arrived IDN';
                        $statusColor = '#359DD9';
                        $dateLabel = 'Arrived on';
                        $dateValue = $ship->ETDA;
                    } elseif ($ship->stsimpk == 3) {
                        $statusText = 'In house';
                        $statusColor = '#19B715';
                        $dateLabel = 'Arrived on';
                        $dateValue = $ship->EIH;
                    }

                    $formattedDate = '-';
                    if (!empty($dateValue) && $dateValue != '0000-00-00') {
                        $formattedDate = \Carbon\Carbon::parse($dateValue)->format('d M Y');
                    }

                    $poCount = isset($shipmentPoDetails[$ship->importpk])
                        ? collect($shipmentPoDetails[$ship->importpk])
                            ->pluck('pono')
                            ->unique()
                            ->count()
                        : 0;
                @endphp

                {{-- LABEL SHIPMENT + STATUS BADGE --}}
                <div
                    style="font-style:normal; font-weight:700; font-size:14px; line-height:18px; color:#359DD9; margin-top:16px;">
                    {{-- <span style="cursor:pointer; color:#359DD9;"
                        onclick="openShipmentPlanModal(
                        '{{ $shipLabel }}',
                        '{{ $detail->supnm ?? '-' }}',
                        '{{ $ship->importpk ?? '' }}',
                        '{{ $poCount }}',
                        '{{ $ship->vassal_type ?? '-' }}',
                        '{{ $ship->vassalnm ?? '-' }}',
                        '{{ $ship->AWB ?? '-' }}',
                        '{{ $ship->arrival_city ?? '-' }}',
                        '{{ $ship->port_name ?? '-' }}',
                        '{{ !empty($ship->ETDV) && $ship->ETDV != '0000-00-00' ? \Carbon\Carbon::parse($ship->ETDV)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->ETDA) && $ship->ETDA != '0000-00-00' ? \Carbon\Carbon::parse($ship->ETDA)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->EIH) && $ship->EIH != '0000-00-00' ? \Carbon\Carbon::parse($ship->EIH)->format('d M Y') : '-' }}'
                    )">
                        {{ $shipLabel }}
                    </span> --}}
                    <span style="cursor:pointer; color:#359DD9;"
                        onclick="openShipmentPlanModal(
                        '{{ $shipLabel }}',
                        '{{ $detail->supnm ?? '-' }}',
                        '{{ $ship->importpk ?? '' }}',
                        '{{ $poCount }}',
                        '{{ $ship->vassal_type ?? '-' }}',
                        '{{ $ship->vassalnm ?? '-' }}',
                        '{{ $ship->AWB ?? '-' }}',
                        '{{ $ship->arrival_city ?? '-' }}',
                        '{{ $ship->port_name ?? '-' }}',
                        '{{ !empty($ship->ETDV) && $ship->ETDV != '0000-00-00' ? \Carbon\Carbon::parse($ship->ETDV)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->ETDA) && $ship->ETDA != '0000-00-00' ? \Carbon\Carbon::parse($ship->ETDA)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->EIH) && $ship->EIH != '0000-00-00' ? \Carbon\Carbon::parse($ship->EIH)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->ATD) && $ship->ATD != '0000-00-00' ? \Carbon\Carbon::parse($ship->ATD)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->ATA) && $ship->ATA != '0000-00-00' ? \Carbon\Carbon::parse($ship->ATA)->format('d M Y') : '-' }}',
                        '{{ !empty($ship->aih_date) && $ship->aih_date != '0000-00-00' ? \Carbon\Carbon::parse($ship->aih_date)->format('d M Y') : '-' }}'
                    )">
                        {{ $shipLabel }}
                    </span>

                    &nbsp;&nbsp;

                    <input type="text" value="{{ $statusText }}" size="10"
                        style="
                        padding:2px 4px;
                        text-align:center;
                        border-radius:5px;
                        border:0;
                        font-weight:300;
                        font-size:14px;
                        color:#FFFFFF;
                        background-color:{{ $statusColor }};
                    "
                        readonly>

                    <span style="color:#00000061; font-weight:normal;">
                        {{ $dateLabel }} {{ $formattedDate }}
                    </span>
                </div>

                {{-- TABEL DETAIL VASSAL — SEKARANG DI DALAM LOOP, PER SHIPMENT --}}
                <div class="row mt-2">
                    <div class="col" style="color:#00000061; font-size:16px;">Vassal type</div>
                    <div class="col" style="color:#00000061; font-size:16px;">Vassal name</div>
                    <div class="col" style="color:#00000061; font-size:16px;">AirwayBill number</div>
                    <div class="col" style="color:#00000061; font-size:16px;">Arrival City</div>
                    <div class="col" style="color:#00000061; font-size:16px;">Port Name</div>
                    <div class="col" style="color:#00000061; font-size:16px;">ETD(Vassal)</div>
                    <div class="col" style="color:#00000061; font-size:16px;">ETA</div>
                    <div class="col" style="color:#00000061; font-size:16px;">EIH</div>
                </div>

                <div class="row mt-1 mb-3">
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ $ship->vassal_type ?: '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ $ship->vassalnm ?: '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ $ship->AWB ?: '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ $ship->arrival_city ?: '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ $ship->port_name ?: '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ !empty($ship->ETDV) && $ship->ETDV != '0000-00-00'
                            ? \Carbon\Carbon::parse($ship->ETDV)->format('d M Y')
                            : '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ !empty($ship->ETDA) && $ship->ETDA != '0000-00-00'
                            ? \Carbon\Carbon::parse($ship->ETDA)->format('d M Y')
                            : '-' }}
                    </div>
                    <div class="col" style="color:#000; font-size:14px;">
                        {{ !empty($ship->EIH) && $ship->EIH != '0000-00-00' ? \Carbon\Carbon::parse($ship->EIH)->format('d M Y') : '-' }}
                    </div>
                </div>
            @empty
                <div class="mt-2" style="color:#00000061; font-size:14px;">
                    No shipment data available.
                </div>
            @endforelse
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

    <div class="modal fade" id="shipmentPlanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:12px;">

                {{-- HEADER --}}
                <div class="modal-header" style="border:none; padding:20px 24px 0 24px;">
                    <h5 class="modal-title" style="font-weight:700; font-size:20px;">
                        Shipment plan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- BODY --}}
                <div class="modal-body" style="padding:16px 24px 24px 24px;">
                    <input type="hidden" id="modal_importpk">

                    {{-- ORDER DETAILS --}}
                    <div style="font-weight:700; font-size:16px; color:rgba(0,0,0,.6); margin-bottom:2px;">
                        Order details
                    </div>

                    <div class="mb-1">
                        {{-- Supplier --}}
                        <div class="d-flex align-items-center mb-1">
                            <div style="width:80px; color:#00000061;">Supplier</div>
                            <div id="modal_supplier">-</div>
                        </div>

                        {{-- POs + View detail --}}
                        <div class="d-flex align-items-center mb-2">
                            <div style="width:80px; color:#00000061;">
                                POs <span id="countpono" style="font-size:14px;">0</span>
                            </div>

                            <div>
                                <span id="shw" style="cursor:pointer; color:#359DD9;" onclick="viewdtl();">
                                    View detail
                                </span>

                                <span id="hdn" style="cursor:pointer; color:#359DD9; display:none;"
                                    onclick="viewdtl();">
                                    Hide detail
                                </span>
                            </div>
                        </div>
                    </div>
                    <div
                        style="
                            background:#fff;
                            border-radius:14px;
                            box-shadow:0 2px 14px rgba(0,0,0,.06);
                            padding:18px 18px 6px 18px;
                        ">

                        {{-- DETAIL PO TABLE --}}
                        <div id="tabel2b" style="display:none; mb-2;">
                            <div class="table-responsive" style="width:100%; overflow-x:auto;">
                                <table id="DgImport" class="table table-bordered align-middle mb-0"
                                    style="width:100%; table-layout:fixed; font-size:13px;">
                                    <thead class="ship-thead">
                                        <tr>
                                            <th style="width:12%;">Item</th>
                                            <th style="width:30%;">Description</th>
                                            <th style="width:14%;">Supplier</th>
                                            <th style="width:10%;">Color</th>
                                            <th style="width:10%;">Price</th>
                                            <th style="width:12%;">Qty PO</th>
                                            <th style="width:12%;">Shipment Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dgImportBody">
                                        <tr>
                                            <td colspan="7" style="color:#999;">Belum ada detail PO.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- SHIPMENT DETAILS --}}
                        <div
                            style="font-weight:700; font-size:16px; color:rgba(0,0,0,.6); margin-top:4px; margin-bottom:4px; ">
                            Shipment details
                        </div>

                        <div class="row">
                            <div class="col" style="color:#00000061;">Vassal type</div>
                            <div class="col" style="color:#00000061;">Vassal name</div>
                            <div class="col" style="color:#00000061;">AirwayBill Number</div>
                            <div class="col" style="color:#00000061;">Arrival city</div>
                            <div class="col" style="color:#00000061;">Port name</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col" id="modal_vassal_type">-</div>
                            <div class="col" id="modal_vassal_name">-</div>
                            <div class="col" id="modal_awb">-</div>
                            <div class="col" id="modal_arrival_city">-</div>
                            <div class="col" id="modal_port_name">-</div>
                        </div>

                        {{-- SHIPMENT DATES --}}
                        <div style="font-weight:700; font-size:16px; color:rgba(0,0,0,.6); margin-bottom:4px;">
                            Shipment dates
                        </div>

                        <div class="row">
                            <div class="col" style="color:#00000061;">ETD</div>
                            <div class="col" style="color:#00000061;">ETA</div>
                            <div class="col" style="color:#00000061;">EIH (Estimate in house)</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col" id="modal_etd">-</div>
                            <div class="col" id="modal_eta">-</div>
                            <div class="col" id="modal_eih">-</div>
                        </div>

                        <div class="row">
                            <div class="col" style="color:#00000061;">ATD (Actual Time of Delivery)</div>
                            <div class="col" style="color:#00000061;">ATA (Actual Time of Arrival)</div>
                            <div class="col" style="color:#00000061;">AIH (Actual In House)</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col" id="modal_atd">-</div>
                            <div class="col" id="modal_ata">-</div>
                            <div class="col" id="modal_aih">-</div>
                        </div>

                        {{-- SHIPMENT LOG --}}
                        <div style="font-weight:700; font-size:16px; color:rgba(0,0,0,.6); margin-bottom:8px;">
                            Shipment Log
                        </div>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th style="width:220px;">Date</th>
                                    <th>Changes</th>
                                </tr>
                            </thead>
                            <tbody id="shipment-log-body">
                                <tr>
                                    <td colspan="2" style="color:#999;">Belum ada log.</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
