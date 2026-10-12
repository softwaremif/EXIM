@extends('layout.main')
@section('css_custom')
    <style>
        .header {
            display: flex;
            flex-direction: row;
            justify-content: flex-end;
            align-items: flex-start;
            padding: 16px;
            gap: 24px;
            position: absolute;
            width: 100%;
            height: 48px;
            left: 0px;
            top: 58px;
            background: #359DD9;
            box-shadow: 0px 0px 16px -4px rgba(0, 0, 0, 0.12);
        }

        #count2 {
            width: 200px;
            height: 20px;
            font-style: normal;
            font-weight: 700;
            font-size: 14px;
            line-height: 20px;
            color: #FFFFFF;
            flex: none;
            order: 0;
            flex-grow: 0;
        }

        thead tr.group-total th {
            background: #f8fafc !important;
            border-bottom: 1px solid #e5e7eb !important;
            font-size: 12px;
        }

        .group-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
        }

        .group-value {
            font-size: 13px;
            font-weight: 700;
        }

        .group-info {
            height: 55px;
            vertical-align: middle;
        }

        .group-center {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            height: 100%;
            padding-bottom: 6px;
        }

        .group-qty {
            background: #ecfeff !important;
        }

        .group-packing {
            background: #fef9c3 !important;
        }

        .datagrid-body td[field="packing_qty"],
        .datagrid-body td[field="packing_qty_plan"],
        .datagrid-body td[field="packing_qty_balance"],
        .datagrid-body td[field="ctn"],
        .datagrid-body td[field="packing_ctn"],
        .datagrid-body td[field="ctn_balance"] {
            text-align: right !important;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background-color: #e0f2fe;
            color: #0369a1;
            font-size: 13px;
            transition: 0.2s;
            text-decoration: none;
            border: none;
            outline: none;
            cursor: pointer;
            font-family: inherit;
            vertical-align: middle;
        }

        .action-btn:hover {
            background-color: #bae6fd;
            color: #0c4a6e;
            transform: scale(1.05);
        }

        .action-btn:focus-visible {
            outline: 2px solid #0369a1;
            outline-offset: 2px;
        }

        .action-btn.action-btn-pdf {
            background-color: #fee2e2 !important;
            color: #b91c1c !important;
        }

        .action-btn.action-btn-pdf:hover {
            background-color: #fecaca !important;
            color: #7f1d1d !important;
        }

        .action-btn img {
            display: block;
            margin: auto;
            max-width: 100%;
            max-height: 100%;
        }

        .datagrid-row-segel-complete {
            background: rgba(25, 183, 21, 0.15) !important;
        }

        .datagrid-row-segel-complete:hover {
            background: rgba(25, 183, 21, 0.25) !important;
        }

        .datagrid-row-status4 {
            background: rgba(148, 163, 184, 0.18) !important;
        }

        .datagrid-row-status4:hover {
            background: rgba(148, 163, 184, 0.28) !important;
        }

        #packingDetailModal .modal-dialog {
            max-width: min(1400px, 95vw);
        }

        .sticky-action {
            cursor: pointer;
            margin-left: 10px;
            font-weight: 500;
            opacity: .9;
        }

        .sticky-action:hover {
            opacity: 1;
        }

        /* ============================================================
                                       SAMA dengan TF Finishing/Polibag -- cell gabungan Order Info
                                       & PO No.
                                       ============================================================ */
        #dgOrder .datagrid-header .datagrid-cell {
            font-weight: 600;
            color: #374151;
            font-size: 12px;
        }

        #dgOrder .datagrid-body .datagrid-cell {
            font-size: 13px;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        #dgOrder .datagrid-row:hover td {
            background-color: #f8fafc !important;
        }

        .cell-stack {
            text-align: left;
            line-height: 1.35;
        }

        .cell-stack .cs-main {
            font-weight: 600;
            font-size: 13px;
            color: #0f172a;
        }

        .cell-stack .cs-sub {
            font-size: 11px;
            color: #64748b;
        }

        .cell-stack .cs-meta {
            font-size: 10.5px;
            color: #94a3b8;
        }

        .mif-badge {
            font-size: 9px;
            margin-left: 4px;
            vertical-align: 1px;
        }

        .ctn-summary-cell {
            text-align: left;
            line-height: 1.3;
            padding: 2px 0;
        }

        .ctn-summary-total {
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
        }

        .ctn-summary-label {
            font-weight: 500;
            font-size: 10.5px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .ctn-summary-bar {
            display: flex;
            height: 6px;
            border-radius: 4px;
            overflow: hidden;
            background: #f1f5f9;
            margin: 4px 0;
            width: 100%;
            min-width: 140px;
        }

        .ctn-seg {
            display: inline-block;
            height: 100%;
        }

        .ctn-summary-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            font-size: 10.5px;
            color: #475569;
        }

        .ctn-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }

        .ctn-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        #dgOrder .datagrid-body td[field="POno"],
        #dgOrder .datagrid-header td[field="POno"] {
            overflow: visible !important;
            white-space: normal !important;
        }

        #dgOrder .datagrid-body td[field="POno"] .datagrid-cell,
        #dgOrder .datagrid-header td[field="POno"] .datagrid-cell {
            height: auto !important;
            white-space: normal !important;
            overflow: visible !important;
            word-break: break-word;
            line-height: 1.4;
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .cell-stack .cs-main,
        .cell-stack .cs-sub,
        .cell-stack .cs-meta {
            white-space: normal;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        /* .datagrid-row-over,
                        .datagrid-row-over td,
                        .datagrid-row-over .datagrid-cell {
                            background: inherit !important;
                            color: inherit !important;
                            cursor: default !important;
                        } */
    </style>
@endsection
@section('js_custom')
    <script>
        var header = document.getElementById("myHeader");
        var sticky = header.offsetTop;

        function myFunctiona() {
            console.log("window.pageYOffset:", window.pageYOffset);
            console.log("sticky:", sticky);
            if (window.pageYOffset > sticky) {
                header.classList.add("sticky");
            } else {
                header.classList.remove("sticky");
            }
        }

        function menu() {
            let rows = $('#dgOrder').datagrid('getSelections');

            let row = $('#dgOrder').datagrid('getSelected');
            let tchek = $('#dgOrder').datagrid('getChecked').length;

            if (!row) {
                $('#myHeader').addClass('d-none');
                $('#dgOrder').datagrid('clearSelections');
                return;
            }
            $('#myHeader').removeClass('d-none');
            $('#setship').show();
            $('#count').removeClass('d-none');

            count_row();
        }

        function count_row() {
            var count = $('#dgOrder').datagrid('getChecked').length;
            if (count) {
                $('#myHeader').removeClass('d-none');
                document.getElementById("count").innerHTML = count + " items selected";
            } else {
                $('#myHeader').addClass('d-none');
                $('#dgOrder').datagrid('clearSelections');
                document.getElementById("count").innerHTML = 0 + " items selected";
            }
        }

        function closemenu() {
            $('#myHeader').addClass('d-none');
        }

        function close_all() {
            $('#myHeader').addClass('d-none');
            $('#dgOrder').datagrid('clearSelections');
            $('#dgOrder').datagrid('clearChecked');
            closemenu();
        }

        const isSuperUser = @json(session('guserpk') === 34 || session('guserpk') === 7);
        const localNoImg = "{{ asset('public/css/images/no-img.png') }}";

        // ============================================================
        // FILTER STATE (index)
        // ============================================================
        function getSavedListState() {
            let raw = sessionStorage.getItem('packingListState');
            if (!raw) return null;
            sessionStorage.removeItem('packingListState');
            try {
                return JSON.parse(raw);
            } catch (e) {
                return null;
            }
        }

        function savePackingNavState() {
            let pager = $('#dgOrder').datagrid('getPager');
            let pageNumber = pager.pagination('options').pageNumber;

            let state = {
                search: $('#dgOrder_filterbar [data-dg-filter="search"]').val(),
                buyer: $('#dgOrder_filterbar [data-dg-filter="buyer"]').combobox('getValue'),
                year: $('#dgOrder_filterbar [data-dg-filter="year"]').combobox('getValue'),
                exFactoryFrom: $('#dgOrder_filterbar [data-dg-filter="ex_factory_from"]').val(),
                exFactoryTo: $('#dgOrder_filterbar [data-dg-filter="ex_factory_to"]').val(),
                planStatus: $('#dgOrder_filterbar [data-dg-filter="packing_plan_status"]').combobox('getValue'), // BARU
                page: pageNumber,
            };

            sessionStorage.setItem('packingListState', JSON.stringify(state));
        }


        // GANTI bagian restore filter di $(function() {...}) -- tambah baris baru:

        $(function() {
            let saved = getSavedListState();

            if (saved) {
                $('#dgOrder_filterbar [data-dg-filter="search"]').val(saved.search || '');
                $('#dgOrder_filterbar [data-dg-filter="buyer"]').combobox('setValue', saved.buyer || '');
                $('#dgOrder_filterbar [data-dg-filter="year"]').combobox('setValue', saved.year ?? new Date()
                    .getFullYear());
                $('#dgOrder_filterbar [data-dg-filter="ex_factory_from"]').val(saved.exFactoryFrom || '');
                $('#dgOrder_filterbar [data-dg-filter="ex_factory_to"]').val(saved.exFactoryTo || '');
                // BARU
                if (saved.planStatus) {
                    $('#dgOrder_filterbar [data-dg-filter="packing_plan_status"]').combobox('setValue', saved
                        .planStatus);
                }
            }

            $('#dgOrder').datagrid({
                onLoadSuccess: onLoadTable
            });

            if (saved?.modalOp) {
                restoredDgOrderPage = saved.page || 1;
                openPackingDetailModal(null, saved.modalPo, saved.modalOp, saved.modalPoref, saved.modalMif);
            } else if (window.EasyuiDG) {
                window.EasyuiDG.reload('dgOrder', saved?.page || 1);
            }
        });
        window.addEventListener('pageshow', function(event) {
            if (!event.persisted) return;

            if (window.EasyuiDG) {
                window.EasyuiDG.reload('dgOrder');
            }
            if ($('#packingDetailModal').hasClass('show')) {
                reloadPackingDetailModal();
            }
        });

        function formatCtnSummary(value, row) {
            const total = Number(row.ctn_plan_count || 0);
            const sealed = Number(row.ctn_sealed_count || 0);
            const full = Number(row.ctn_full_count || 0);
            const partial = Number(row.ctn_partial_count || 0);
            const notStarted = Math.max(0, total - sealed - full - partial);

            if (total === 0) {
                return `<div class="ctn-summary-cell"><span class="text-muted" style="font-size:12px;">Belum ada carton</span></div>`;
            }

            const pct = (n) => (n / total) * 100;

            // Urutan segmen SENGAJA sealed dulu -- carton yang sudah paling
            // "matang" statusnya tampil di ujung kiri bar (representasi progres).
            const segments = [{
                    label: 'Sealed',
                    value: sealed,
                    color: '#1e293b'
                },
                {
                    label: 'Full',
                    value: full,
                    color: '#22c55e'
                },
                {
                    label: 'Partial',
                    value: partial,
                    color: '#3b82f6'
                },
                {
                    label: 'Plan',
                    value: notStarted,
                    color: '#cbd5e1'
                },
            ];

            const barHtml = segments
                .filter(s => s.value > 0)
                .map(s =>
                    `<span class="ctn-seg" style="width:${pct(s.value)}%;background:${s.color};" title="${s.label}: ${s.value}"></span>`
                )
                .join('');

            const legendHtml = segments
                .filter(s => s.value > 0)
                .map(s =>
                    `<span class="ctn-legend-item"><span class="ctn-dot" style="background:${s.color};"></span>${s.value} ${s.label}</span>`
                )
                .join('');

            return `
                <div class="ctn-summary-cell">
                    <div class="ctn-summary-total">${total} <span class="ctn-summary-label">Carton</span></div>
                    <div class="ctn-summary-bar">${barHtml}</div>
                    <div class="ctn-summary-legend">${legendHtml}</div>
                </div>
            `;
        }

        function formatPackingPlanStatus(value) {
            const map = {
                pending: {
                    label: 'Pending',
                    bg: '#f1f5f9',
                    color: '#64748b'
                },
                partial: {
                    label: 'Partial',
                    bg: '#e0f2fe',
                    color: '#0369a1'
                },
                complete: {
                    label: 'Complete',
                    bg: '#dcfce7',
                    color: '#15803d'
                },
            };
            const s = map[value] || map.pending;

            return `
                <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:999px;
                    font-size:11px;font-weight:700;background:${s.bg};color:${s.color};">
                    ${s.label}
                </span>
            `;
        }

        // ============================================================
        // FORMATTER UMUM
        // ============================================================
        function formatDate(value) {
            if (!value) return '<span class="dg-empty-cell">-</span>';
            let datePart = String(value).split(' ')[0];
            let parts = datePart.split('-');
            if (parts.length !== 3) return value;
            let [year, month, day] = parts;
            return `${day}/${month}/${year}`;
        }

        function formatNumber(value) {
            return Number(value || 0).toLocaleString('id-ID');
        }

        function formatBalanceCell(value) {
            let v = Number(value || 0);
            let cls = v < 0 ? 'color:#dc3545;font-weight:bold' : (v > 0 ? 'color:#198754;font-weight:bold' :
                'color:#94a3b8');
            let text = v > 0 ? ('+' + formatNumber(v)) : formatNumber(v);
            return `<span style="${cls}">${text}</span>`;
        }

        // Ex Factory -- validasi KETAT, kosongkan (bukan "-"/NaN/undefined)
        // kalau GAC tidak valid. SAMA pola dengan TF Finishing/Polibag.
        function formatExFactory(value) {
            if (value === null || value === undefined || value === '') return '';

            const datePart = String(value).split(' ')[0].split('T')[0];
            const parts = datePart.split('-');
            if (parts.length !== 3) return '';

            const year = parseInt(parts[0], 10);
            const monthIdx = parseInt(parts[1], 10) - 1;
            const dayNum = parseInt(parts[2], 10);

            if (
                !Number.isFinite(year) || year <= 0 ||
                !Number.isFinite(monthIdx) || monthIdx < 0 || monthIdx > 11 ||
                !Number.isFinite(dayNum) || dayNum <= 0 || dayNum > 31
            ) {
                return '';
            }

            const bulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${dayNum} ${bulanSingkat[monthIdx]} ${year}`;
        }

        // PO No + Place digabung 1 cell.
        function formatPOno(value, row) {
            return `
                <div class="cell-stack">
                    <div class="cs-main">${value ?? '-'}</div>
                    <div class="cs-sub">${row.customer ?? '-'}</div>
                </div>
            `;
        }

        // Order Information -- foto, OP, Buyer, Season, Style, Qty
        // digabung 1 cell. SAMA pola dengan TF Finishing/Polibag.
        function formatOrderInfo(value, row) {
            const mifBadge = isSuperUser ?
                `<span class="badge bg-secondary-subtle text-secondary-emphasis mif-badge">mif ${row.mif}</span>` :
                '';

            const imgUrl = row.order_image || localNoImg;
            const imgHtml = `
                <img src="${imgUrl}" width="60" height="60"
                    style="object-fit:cover;border-radius:6px;flex-shrink:0;"
                    onerror="this.onerror=null;this.src='${localNoImg}';">
            `;

            return `
                <div class="d-flex align-items-start gap-2">
                    ${imgHtml}
                    <div class="cell-stack">
                        <div class="cs-main">${row.OP ?? '-'}${mifBadge}</div>
                        <div class="cs-sub">${row.buyer ?? '-'} &middot; ${row.season ?? '-'}</div>
                        <div class="cs-sub">${row.style ?? '-'}</div>
                        <div class="cs-meta">Qty: <strong style="color:#334155;">${Number(row.qty || 0).toLocaleString()}</strong></div>
                    </div>
                </div>
            `;
        }

        // ============================================================
        // KOLOM AKSI INDEX
        // ============================================================
        function formatAction(value, row, index) {
            const pdfUrl = "" +
                "?po=" + encodeURIComponent(row.POno ?? '') +
                "&op=" + encodeURIComponent(row.OP) +
                "&poref=" + encodeURIComponent(row.poref ?? '') +
                "&mif=" + row.mif;

            return `
                <div class="d-inline-flex align-items-center gap-1.5">
                    <a href="javascript:void(0)"
                        onclick='openPackingGlobal(event, ${JSON.stringify(row.POno)}, ${JSON.stringify(row.OP)}, ${JSON.stringify(row.poref)}, ${row.mif})'
                        class="action-btn"
                        title="Input Packing Global">
                        <i class="fas fa-edit"></i>
                    </a>

                    <a href="${pdfUrl}" target="_blank" class="action-btn action-btn-pdf" title="Print PDF">
                        <img src="{{ asset('/public/css/images/pdf.jpg') }}" width="18" height="18" style="display: block; object-fit: contain;">
                    </a>
                </div>
            `;
        }

        function openPackingGlobal(e, po, op, poref, mif) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            savePackingNavState();
            let url = "" +
                "?po=" + encodeURIComponent(po ?? '') +
                "&op=" + encodeURIComponent(op) +
                "&poref=" + encodeURIComponent(poref ?? '') +
                "&mif=" + mif;
            window.location.href = url;
        }

        // ============================================================
        // onLoadSuccess INDEX — summary + empty state
        // ============================================================
        function onLoadTable(data) {
            const rows = data.rows || [];
            rows.forEach((row, index) => {
                row.no = index + 1;
            });
            let panel = $('#dgOrder').datagrid('getPanel');
            let body = panel.find('.datagrid-view2 .datagrid-body');
            panel.find('.easyui-empty-state').remove();
            if (!rows.length) {
                body.append(`
                    <div class="easyui-empty-state">
                        <div style="text-align:center">
                            <img src="{{ asset('public/css/images/no-data.png') }}" width="180">
                            <div style="margin-top:8px;font-weight:600;">No Data Found</div>
                            <div style="font-size:12px;color:#9ca3af;">Try changing filter</div>
                        </div>
                    </div>
                `);
            }
        }

        function set_shipplan() {
            var dg = $('#dgOrder');

            if (!dg.length || !$.data(dg[0], 'datagrid')) {
                $.messager.alert('Error', 'DataGrid belum siap.', 'error');
                return;
            }

            var rows = dg.datagrid('getChecked');

            if (!rows || rows.length === 0) {
                $.messager.alert(
                    'Warning',
                    'Silakan pilih data terlebih dahulu.',
                    'warning'
                );
                return;
            }

            // Ambil row pertama yang dicheck
            var row = rows[0];

            console.log('ordpk:', row.ordpk);
            console.log('buyer:', row.buyer);
            console.log('POno:', row.POno);
            console.log('totqty:', row.packing_qty_plan);
            console.log('totqty:', row.GAC);
            console.log('totqty:', row.packing_ctn);

            $.ajax({
                url: "{{ route('set_shipplan') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ordpk: row.ordpk,
                    buyer: row.buyer,
                    POno: row.POno,
                    totqty: row.packing_qty_plan,
                    exdate: row.GAC,
                    totctn : row.packing_ctn
                },
                success: function(res) {

                    if (res.success) {
                        window.location.href =
                            "{{ url('/exim-export/shipplan/detail_ship') }}/" + res.exportpk;
                    } else {
                        $.messager.alert(
                            'Error',
                            res.message ?? 'Gagal membuat export.',
                            'error'
                        );
                    }

                },
                error: function(xhr) {
                    $.messager.alert(
                        'Error',
                        xhr.responseJSON?.message ?? 'Gagal membuat export.',
                        'error'
                    );
                }
            });
        }
    </script>
@endsection
@section('content')
    <div class="page-wrap ps-3 pt-3">
        <x-table-default id="dgOrder" title="Daftar Data OP" search search-name="search" search-placeholder="Search..."
            buyer buyer-name="buyer" buyer-url="{{ route('buyer-list') }}" buyer-value-field="buyer"
            buyer-text-field="buyer_name" buyer-mode="remote" year year-name="year" exfactory exfactory-name="ex_factory"
            sort-dropdown sort-asc-label="Earliest Ex Factory" sort-desc-label="Latest Ex Factory">

            {{-- <x-slot name="filters">
                <input data-dg-filter="packing_plan_status" data-dg-filter-type="select"
                    data-dg-chip-label="Status Packing List"
                    data-dg-options='[
                    {"value":"","text":"Semua Status Packing List"},
                    {"value":"pending","text":"Pending"},
                    {"value":"partial","text":"Partial"},
                    {"value":"complete","text":"Complete"}
                ]'
                    data-dg-default="" style="width:190px">
            </x-slot> --}}
            <table id="dgOrder" class="easyui-datagrid" style="width:100%;height:600px" url="{{ route('packing.list') }}"
                method="get" pagination="true" pageSize="50" pageList="[25,50,100,200,500]" rownumbers="false"
                singleSelect="false" fitColumns="false" border="false"
                data-options="onCheck:function(){menu();},onCheckAll:function(){menu();},onUncheck:function(){menu();},onUncheckAll:function(){menu();}">
                <thead frozen="true">
                    <tr>
                        <th field="ck" checkbox="true" align="center" rowspan="2">Aksi</th>
                        <th field="popks2" rowspan="2" hidden="true">popk</th>
                        <th field="OP" width="230" formatter="formatOrderInfo" rowspan="2">Order Information</th>
                        <th field="POno" width="150" formatter="formatPOno" rowspan="2">PO No</th>
                        <th field="GAC" width="100" align="center" formatter="formatExFactory" rowspan="2">Ex
                            Factory</th>
                    </tr>
                    <tr></tr>
                </thead>
                <thead>
                    <tr>
                        <th field="poref" width="150" rowspan="2">License<br>PO Ref</th>
                        <th field="packing_plan_status" width="120" align="center" rowspan="2"
                            formatter="formatPackingPlanStatus">Packing Plan</th>
                        <th width="90" colspan="3" align="right">Packing /Pcs</th>
                        <th field="ctn_summary" width="250" align="left" rowspan="2" formatter="formatCtnSummary">
                            CTN</th>
                    </tr>
                    <tr>
                        <th field="packing_qty_plan" width="80" align="right" formatter="formatNumber">Plan</th>
                        <th field="packing_qty" width="80" align="right" formatter="formatNumber">Actual</th>
                        <th field="packing_qty_balance" width="80" align="right" formatter="formatBalanceCell">Balance
                        </th>
                    </tr>
                </thead>
            </table>
        </x-table-default>
    </div>

    <!-- menu header -->
    <div class="header d-flex p-4 justify-content-between align-items-center w-100 d-none" id="myHeader" style="">
        <div id="count" class="d-none"></div>
        <div class="d-flex gap-4">
            <div class="delete" id="setship" style="width:auto;display:none;">
                <a href="javascript:void(0)" plain="true" onclick="set_shipplan();"
                    style="color: #FFFFFF; text-decoration: none;">
                    Set Shipment Plan
                </a>
            </div>
            <div class="delete" id="mclose" style="width:auto;">
                <a href="javascript:void(0)" plain="true" onclick="close_all();"
                    style="color: #FFFFFF; text-decoration: none;"> Close menu
                </a>
            </div>
        </div>
    </div>
@endsection
