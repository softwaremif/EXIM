@extends('layout.main')

@section('css_custom')
    {{-- <style>
        .ship-thead th {
            background: #F5F5F5 !important;
            vertical-align: middle;
        }

        .material-ship-thead th {
            background: #F5F5F5 !important;
            vertical-align: middle;
        }

        .custom-dropdown {
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }

        .custom-dropdown-trigger {
            width: 100%;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            background: #FFFFFF;
            text-align: left;
            padding: 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .custom-dropdown-trigger span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            color: #000;
        }

        .custom-dropdown-icon {
            font-size: 12px;
            color: #666666;
            flex-shrink: 0;
            margin-left: 6px;
        }

        .custom-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            background: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            overflow-y: auto;
            max-height: 300px;
            padding: 4px 0;
        }

        .custom-dropdown-item {
            padding: 8px 8px;
            color: #000000;
            font-size: 14px;
            cursor: pointer;
            white-space: normal;
        }

        .custom-dropdown-item:hover {
            background: #EAF4FC;
        }

        .custom-dropdown-item.selected {
            background: #EAF4FC;
        }

        .field-error {
            border: 1px solid #e53935 !important;
            background-color: #fff5f5;
        }

        .field-error-text {
            color: #e53935;
            font-size: 12px;
            margin-top: 2px;
            display: block;
        }

        .modal-backdrop {
            z-index: 10000;
        }

        .modal {
            z-index: 10001;
        }

        .new-entry,
        .old-entry {
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 0 !important;
        }

        .new-entry a,
        .old-entry a {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 8px 16px !important;
            text-decoration: none !important;
        }

        .new-entry a:hover,
        .old-entry a:hover {
            background-color: #F1F1F1 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        /* #btnShipmentPlan.disabled {
        color: rgba(255,255,255,.5) !important;
        pointer-events: none;
        cursor: not-allowed;
        } */
    </style> --}}

    <style>
        .ship-thead th {
            background: #F5F5F5 !important;
            vertical-align: middle;
            position: sticky;
            top: 0;
            z-index: 5;
            box-shadow: 0 1px 0 #dee2e6;
        }

        .material-ship-thead th {
            background: #F5F5F5 !important;
            vertical-align: middle;
        }

        .ship-table-scroll {
            max-height: 450px;
            min-height: 250px;
            overflow-y: auto;
            overflow-x: auto;
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .ship-table-scroll table {
            margin-bottom: 0;
        }

        .ship-table-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .ship-table-scroll::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }

        .custom-dropdown {
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }

        .custom-dropdown-trigger {
            width: 100%;
            height: 34px;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            background: #FFFFFF;
            text-align: left;
            padding: 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .custom-dropdown-trigger span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 14px;
            color: #000;
        }

        .custom-dropdown-icon {
            font-size: 12px;
            color: #666666;
            flex-shrink: 0;
            margin-left: 6px;
        }

        .custom-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            background: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            overflow-y: auto;
            max-height: 300px;
            padding: 4px 0;
        }

        .custom-dropdown-item {
            padding: 8px 8px;
            color: #000000;
            font-size: 14px;
            cursor: pointer;
            white-space: normal;
        }

        .custom-dropdown-item:hover {
            background: #EAF4FC;
        }

        .custom-dropdown-item.selected {
            background: #EAF4FC;
        }

        .field-error {
            border: 1px solid #e53935 !important;
            background-color: #fff5f5;
        }

        .field-error-text {
            color: #e53935;
            font-size: 12px;
            margin-top: 2px;
            display: block;
        }

        .modal-backdrop {
            z-index: 10000;
        }

        .modal {
            z-index: 10001;
        }

        .new-entry,
        .old-entry {
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 0 !important;
        }

        .new-entry a,
        .old-entry a {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
            padding: 8px 16px !important;
            text-decoration: none !important;
        }

        .new-entry a:hover,
        .old-entry a:hover {
            background-color: #F1F1F1 !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }

        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .po-toggle-wrap {
            cursor: pointer;
            color: #000;
        }

        .po-collapsed {
            max-width: 90px;
        }

        .po-expanded div {
            white-space: normal;
            word-break: break-word;
            padding: 1px 0;
        }
    </style>
@endsection

@section('js_custom')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        function toggleActionBar() {
            const checkedBoxes = document.querySelectorAll('.order-check:checked');
            const checked = checkedBoxes.length;
            const total = document.querySelectorAll('.order-check').length;
            const header = document.getElementById('myHeader');
            const countp = document.getElementById('countp');
            const approve = document.getElementById('approve');
            const checkAll = document.getElementById('check-all');

            if (checked > 0) {
                header.style.display = 'flex';
                countp.innerText = checked + ' items selected';

                // ==== replikasi logic native menu() ====
                const suppliers = new Set();
                const statuses = new Set();

                checkedBoxes.forEach(cb => {
                    suppliers.add(cb.dataset.supnm);
                    statuses.add(cb.dataset.status);
                });

                // native: hide approve kalau supplier campur ATAU salah satu yg dipilih statusnya "Processed" (2)
                const hasMultipleSuppliers = suppliers.size > 1;
                const hasProcessedStatus = Array.from(statuses).some(s => s === '2');

                if (hasMultipleSuppliers || hasProcessedStatus) {
                    approve.style.display = 'none';
                } else {
                    approve.style.display = 'block';
                }
            } else {
                header.style.display = 'none';
                countp.innerText = '0 items selected';
            }

            if (checkAll) {
                checkAll.checked = (checked === total && total > 0);
            }
        }

        function clearOrderNoticeIfNeeded(checkbox) {
            if (!checkbox.checked) return;

            const row = checkbox.closest('tr');
            const noticeCell = row.querySelector('.tpo2d-indicator');

            if (!noticeCell) return; // tidak ada garis biru, tidak perlu apa-apa

            const popk = checkbox.value;

            fetch(`{{ route('exim.import.clearNotice', ':popk') }}`.replace(':popk', popk), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        // Jangan remove() elemen <td>-nya, cukup kosongkan isi & lepas class-nya
                        noticeCell.classList.remove('tpo2d-indicator');
                        noticeCell.innerHTML = '';
                    }
                })
                .catch(err => console.error(err));
        }

        let filterTimer;
        let filterAbortController = null;

        // =========================
        // AJAX FILTER/SORT/SEARCH (no page reload)
        // =========================
        function collectFilterParams() {
            const form = document.getElementById('filterForm');
            return new URLSearchParams(new FormData(form));
        }

        function fetchOrders(params, pushHistory = true) {
            if (filterAbortController) filterAbortController.abort();
            filterAbortController = new AbortController();

            const query = params.toString();
            const dataUrl = `{{ route('exim.import.order.data') }}${query ? '?' + query : ''}`;
            const pageUrl = `{{ route('exim.import.order') }}${query ? '?' + query : ''}`;

            const tableWrapper = document.getElementById('tabel1');

            // preserve focus/cursor if the user is mid-typing in the search box
            const active = document.activeElement;
            const wasSearchFocused = !!(active && active.id === 'cari1');
            const selStart = wasSearchFocused ? active.selectionStart : null;
            const selEnd = wasSearchFocused ? active.selectionEnd : null;

            // preserve which custom-dropdown menu is currently open, if any
            const openMenu = document.querySelector('.custom-dropdown-menu[style*="block"]');
            const openMenuId = openMenu ? openMenu.id : null;

            if (tableWrapper) tableWrapper.style.opacity = '0.5';

            fetch(dataUrl, {
                    signal: filterAbortController.signal
                })
                .then(res => res.json())
                .then(data => {
                    const filterWrapper = document.getElementById('filterBarWrapper');
                    const sortWrapper = document.getElementById('frame-filter');
                    const currentTableWrapper = document.getElementById('tabel1');

                    if (filterWrapper && data.filterHtml) filterWrapper.outerHTML = data.filterHtml;
                    if (sortWrapper && data.sortHtml) sortWrapper.outerHTML = data.sortHtml;
                    if (currentTableWrapper && data.tableHtml) currentTableWrapper.outerHTML = data.tableHtml;

                    if (wasSearchFocused) {
                        const newSearch = document.getElementById('cari1');
                        if (newSearch) {
                            newSearch.focus();
                            if (selStart !== null) newSearch.setSelectionRange(selStart, selEnd);
                        }
                    }

                    if (openMenuId) {
                        const newMenu = document.getElementById(openMenuId);
                        if (newMenu) newMenu.style.display = 'block';
                    }

                    if (pushHistory) {
                        history.pushState({
                            eximOrderFilters: true
                        }, '', pageUrl);
                    }
                })
                .catch(err => {
                    if (err.name === 'AbortError') return;
                    console.error(err);
                    const table = document.getElementById('tabel1');
                    if (table) table.style.opacity = '1';
                });
        }

        function goToOrderPage(page) {
            if (page < 1) return;
            document.getElementById('page').value = page;
            submitFilter();
        }

        function onOrderPageSizeChange() {
            const size = document.getElementById('orderPageSize').value;
            document.getElementById('rows').value = size;
            document.getElementById('page').value = 1; // reset ke halaman 1 tiap ganti page size
            submitFilter();
        }

        function submitFilter() {
            fetchOrders(collectFilterParams());
        }

        function submitFilterDelay() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(function() {
                submitFilter();
            }, 500);
        }

        window.addEventListener('popstate', function() {
            fetchOrders(new URLSearchParams(window.location.search), false);
        });

        // document.addEventListener('click', function(e) {
        //     const link = e.target.closest('.filter-chip-link, .clear-all-link, #reloadTableBtn');
        //     if (!link) return;

        //     e.preventDefault();
        //     const url = new URL(link.href, window.location.origin);
        //     fetchOrders(url.searchParams);
        // });
       // Khusus reloadTableBtn: sync status dulu, baru reload tabel
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('#reloadTableBtn');
            if (!btn) return;

            e.preventDefault();

            fetch("{{ route('exim.import.order.syncStatus') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
            })
                .then(res => res.json())
                .then(result => {
                    if (!result.success) {
                        console.error(result.message || 'Sync gagal');
                    }
                })
                .catch(err => {
                    console.error(err);
                })
                .finally(() => {
                    // apapun hasil sync-nya, tetap reload tabel
                    const url = new URL(btn.href, window.location.origin);
                    fetchOrders(url.searchParams);
                });
        });

        function toggleSupplier() {
            const chek = document.getElementById('chek');
            chek.value = chek.value == '1' ? '0' : '1';
            submitFilter();
        }

        // function openShipmentModal() {
        //     const selected = Array.from(document.querySelectorAll('.order-check:checked'))
        //         .map(checkbox => checkbox.value);

        //     if (selected.length === 0) {
        //         alert('No PO data selected');
        //         return;
        //     }

        //     document.getElementById('popka').value = selected.join(',');
        //     document.getElementById('dialog-backdrop').style.display = 'block';
        //     document.getElementById('dialog-forma').style.display = 'block';

        //     fetch("{{ route('exim.import.get-po-ship') }}", {
        //             method: "POST",
        //             headers: {
        //                 "Content-Type": "application/json",
        //                 "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        //             },
        //             body: JSON.stringify({
        //                 popks: selected
        //             })
        //         })
        //         .then(response => response.json())
        //         .then(result => {
        //             if (!result.success) {
        //                 alert(result.message);
        //                 return;
        //             }
        //             currentPoShipItems = result.data;
        //             renderPoShipTable(result.data);
        //         })
        //         .catch(error => {
        //             console.error(error);
        //             alert('Failed to load shipment data');
        //         });
        // }

        // function openShipmentModal() {
        //     const selected = Array.from(document.querySelectorAll('.order-check:checked'))
        //         .map(checkbox => checkbox.value);

        //     if (selected.length === 0) {
        //         alert('No PO data selected');
        //         return;
        //     }

        //     document.getElementById('popka').value = selected.join(',');
        //     document.getElementById('dialog-backdrop').style.display = 'block';
        //     document.getElementById('dialog-forma').style.display = 'block';

        //     fetch("{{ route('exim.import.get-po-ship') }}", {
        //             method: "POST",
        //             headers: {
        //                 "Content-Type": "application/json",
        //                 "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        //             },
        //             body: JSON.stringify({
        //                 popks: selected
        //             })
        //         })
        //         .then(response => response.json())
        //         .then(result => {
        //             if (!result.success) {
        //                 alert(result.message);
        //                 return;
        //             }
        //             currentPoShipItems = result.data;
        //             renderPoShipTable(result.data);
        //         })
        //         .catch(error => {
        //             console.error(error);
        //             alert('Failed to load shipment data');
        //         });
        // }
        function openShipmentModal() {
            const checkedBoxes = Array.from(document.querySelectorAll('.order-check:checked')); // BARU: pakai elemen, bukan cuma value
            const selected = checkedBoxes.map(checkbox => checkbox.value);

            if (selected.length === 0) {
                alert('No PO data selected');
                return;
            }

            // BARU: simpan tanggal Supplier ETD dari PO yang dipilih, untuk auto-fill ETD/ETA/EIH nanti
            selectedSupplierEtdDates = checkedBoxes
                .map(cb => parseSupplierEtd(cb.dataset.etd))
                .filter(d => d !== null);

            document.getElementById('popka').value = selected.join(',');
            document.getElementById('dialog-backdrop').style.display = 'block';
            document.getElementById('dialog-forma').style.display = 'block';

            fetch("{{ route('exim.import.get-po-ship') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify({ popks: selected })
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        alert(result.message);
                        return;
                    }
                    currentPoShipItems = result.data;
                    renderPoShipTable(result.data);
                })
                .catch(error => {
                    console.error(error);
                    alert('Failed to load shipment data');
                });
        }

        function closeShipmentModal() {
            document.getElementById('dialog-forma').style.display = 'none';
            document.getElementById('dialog-backdrop').style.display = 'none';
        }

        // Klik di luar modal (area gelap) akan menutup modal,
        // tapi klik di dalam modal tidak akan ikut menutupnya
        document.getElementById('dialog-backdrop').addEventListener('click', function(e) {
            if (e.target === this) {
                closeShipmentModal();
            }
        });

        // function renderPoShipTable(items) {
        //     const tbody = document.getElementById('poShipBody');
        //     tbody.innerHTML = '';

        //     if (items.length === 0) {
        //         tbody.innerHTML = `
        //     <tr>
        //         <td colspan="8" style="text-align:center;">No data found</td>
        //     </tr>
        //     `;
        //         return;
        //     }

        //     const grouped = {};

        //     items.forEach(item => {
        //         if (!grouped[item.popk]) {
        //             grouped[item.popk] = {
        //                 pono: item.pono ?? '-',
        //                 supnm: item.supnm ?? '-',
        //                 byrnm: item.byrnm ?? '-',
        //                 ordno: item.ordno ?? '-',
        //                 totalQty: 0,
        //                 unit: item.unit ?? '',
        //                 rows: []
        //             };
        //         }

        //         grouped[item.popk].totalQty += Number(item.pocons ?? 0);
        //         grouped[item.popk].rows.push(item);
        //     });

        //     Object.values(grouped).forEach(group => {
        //         const totalQty = Number(group.totalQty).toLocaleString('en-US');

        //         tbody.innerHTML += `
        //     <tr>
        //         <td colspan="8" style="font-weight:700; padding:12px 14px; background:#fff;">
        //             ${group.pono}
        //             <br>
        //             <span style="font-size:14px; font-weight:400;">
        //                 Supplier: ${group.supnm}
        //             </span>
        //             &nbsp;&nbsp;&nbsp;
        //             <span style="font-size:14px; font-weight:400;">
        //                 Buyer: ${group.byrnm}
        //             </span>
        //             &nbsp;&nbsp;&nbsp;
        //             <span style="font-size:14px; font-weight:400;">
        //                 OP: ${group.ordno}
        //             </span>
        //             &nbsp;&nbsp;&nbsp;
        //             <span style="font-size:14px; font-weight:700;">
        //                 Total Qty: ${totalQty} ${group.unit}
        //             </span>
        //         </td>
        //     </tr>
        //     `;

        //         group.rows.forEach(item => {
        //             const qty = Number(item.pocons ?? 0).toLocaleString('en-US');
        //             const balance = Number(item.balance ?? item.pocons ?? 0).toLocaleString('en-US');
        //             const unit = item.unit ?? '';

        //             tbody.innerHTML += `
        //             <tr>
        //                 <td>${item.item ?? '-'}</td>
        //                 <td>${item.spec ?? '-'}</td>
        //                 <td>${item.supnm ?? '-'}</td>
        //                 <td>${item.colname ?? '-'}</td>
        //                 <td>${item.price ?? '-'}</td>
        //                 <td>${qty} ${unit}</td>
        //                 <td>${balance} ${unit}</td>
        //                 <td>
        //                     <input type="text"
        //                         class="qty-ship-input"
        //                         name="qtys[${item.podtpk}]"
        //                         value="${balance} ${unit}"
        //                         style="width:100%; height:100%; border:1px solid #b2b2b2; border-radius:8px; padding:8px;">
        //                 </td>
        //             </tr>
        //             `;
        //         });
        //     });
        //     // TAMBAHAN: pasang listener & hitung status awal
        //     document.querySelectorAll('.qty-ship-input').forEach(input => {
        //         input.addEventListener('input', checkShipEntirePo);
        //     });

        //     checkShipEntirePo();
        // }

        let originalQtyValues = {}; // simpan value asli tiap input, key = podtpk
        function renderPoShipTable(items) {
            const tbody = document.getElementById('poShipBody');
            tbody.innerHTML = '';
            originalQtyValues = {}; // BARU: reset tiap kali render ulang PO baru

            if (items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" style="text-align:center;">No data found</td>
                    </tr>
                    `;
                return;
            }

            const grouped = {};

            items.forEach(item => {
                if (!grouped[item.popk]) {
                    grouped[item.popk] = {
                        pono: item.pono ?? '-',
                        supnm: item.supnm ?? '-',
                        byrnm: item.byrnm ?? '-',
                        ordno: item.ordno ?? '-',
                        totalQty: 0,
                        unit: item.unit ?? '',
                        rows: []
                    };
                }

                grouped[item.popk].totalQty += Number(item.pocons ?? 0);
                grouped[item.popk].rows.push(item);
            });

            Object.values(grouped).forEach(group => {
                const totalQty = Number(group.totalQty).toLocaleString('en-US');

                tbody.innerHTML += `
                    <tr>
                        <td colspan="8" style="font-weight:700; padding:12px 14px; background:#fff;">
                            ${group.pono}
                            <br>
                            <span style="font-size:14px; font-weight:400;">
                                Supplier: ${group.supnm}
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            <span style="font-size:14px; font-weight:400;">
                                Buyer: ${group.byrnm}
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            <span style="font-size:14px; font-weight:400;">
                                OP: ${group.ordno}
                            </span>
                            &nbsp;&nbsp;&nbsp;
                            <span style="font-size:14px; font-weight:700;">
                                Total Qty: ${totalQty} ${group.unit}
                            </span>
                        </td>
                    </tr>
                    `;

                // group.rows.forEach(item => {
                //     const qty = Number(item.pocons ?? 0).toLocaleString('en-US');
                //     const balance = Number(item.balance ?? item.pocons ?? 0).toLocaleString('en-US');
                //     const unit = item.unit ?? '';

                //     originalQtyValues[item.podtpk] = balance; // BARU: simpan nilai asli

                //     tbody.innerHTML += `
                //             <tr>
                //                 <td>${item.item ?? '-'}</td>
                //                 <td>${item.spec ?? '-'}</td>
                //                 <td>${item.supnm ?? '-'}</td>
                //                 <td>${item.colname ?? '-'}</td>
                //                 <td>${item.price ?? '-'}</td>
                //                 <td>${qty} ${unit}</td>
                //                 <td>${balance} ${unit}</td>
                //                 <td>
                //                     <input type="text"
                //                         class="qty-ship-input"
                //                         name="qtys[${item.podtpk}]"
                //                         value="${balance}"
                //                         inputmode="numeric"
                //                         style="width:100%; height:100%; border:1px solid #b2b2b2; border-radius:8px; padding:8px;">
                //                 </td>
                //             </tr>
                //             `;
                // });
                group.rows.forEach(item => {
                    const qty = Number(item.pocons ?? 0).toLocaleString('en-US');
                    const balanceNum = Number(item.balance ?? item.pocons ?? 0); // BARU: angka mentah
                    const balance = balanceNum.toLocaleString('en-US');
                    const unit = item.unit ?? '';

                    originalQtyValues[item.podtpk] = balance;

                    tbody.innerHTML += `
                            <tr>
                                <td>${item.item ?? '-'}</td>
                                <td>${item.spec ?? '-'}</td>
                                <td>${item.supnm ?? '-'}</td>
                                <td>${item.colname ?? '-'}</td>
                                <td>${item.price ?? '-'}</td>
                                <td>${qty} ${unit}</td>
                                <td>${balance} ${unit}</td>
                                <td>
                                    <input type="text"
                                        class="qty-ship-input"
                                        name="qtys[${item.podtpk}]"
                                        data-balance="${balanceNum}"
                                        value="${balance}"
                                        inputmode="numeric"
                                        style="width:100%; height:100%; border:1px solid #b2b2b2; border-radius:8px; padding:8px;">
                                </td>
                            </tr>
                            `;
                });
            });

            // BARU: pasang 3 listener sekaligus (input, keypress, paste)
            document.querySelectorAll('.qty-ship-input').forEach(input => {
                input.addEventListener('input', function () {
                    formatQtyInput(this);
                    checkShipEntirePo();
                });
                input.addEventListener('keypress', function (e) {
                    if (!/[0-9]/.test(e.key)) e.preventDefault();
                });
                input.addEventListener('paste', function (e) {
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    if (/\D/.test(pasteData.replace(/,/g, ''))) e.preventDefault();
                });
            });

            checkShipEntirePo();
        }

        // function formatQtyInput(input) {
        //     const rawLength = input.value.length;
        //     const cursorFromEnd = rawLength - input.selectionStart;

        //     let digitsOnly = input.value.replace(/\D/g, '');

        //     if (digitsOnly === '') {
        //         input.value = '';
        //         return;
        //     }

        //     const formatted = Number(digitsOnly).toLocaleString('en-US');
        //     input.value = formatted;

        //     const newPos = formatted.length - cursorFromEnd;
        //     input.setSelectionRange(newPos, newPos);
        // }
        // function formatQtyInput(input) {
        //     const rawLength = input.value.length;
        //     const cursorFromEnd = rawLength - input.selectionStart;

        //     let digitsOnly = input.value.replace(/\D/g, '');

        //     if (digitsOnly === '') {
        //         input.value = '';
        //         return;
        //     }

        //     let numValue = Number(digitsOnly);

        //     // BARU: clamp ke balance kalau melebihi
        //     const maxBalance = Number(input.dataset.balance ?? 0);
        //     if (numValue > maxBalance) {
        //         numValue = maxBalance;
        //     }

        //     const formatted = numValue.toLocaleString('en-US');
        //     input.value = formatted;

        //     const newPos = formatted.length - cursorFromEnd;
        //     input.setSelectionRange(newPos, newPos);
        // }
        
        //format QTYInput bisa manual user
        function formatQtyInput(input) {
            const rawLength = input.value.length;
            const cursorFromEnd = rawLength - input.selectionStart;

            let digitsOnly = input.value.replace(/\D/g, '');

            if (digitsOnly === '') {
                input.value = '';
                return;
            }

            const numValue = Number(digitsOnly);
            const formatted = numValue.toLocaleString('en-US');
            input.value = formatted;

            const newPos = formatted.length - cursorFromEnd;
            input.setSelectionRange(newPos, newPos);
        }

        // function getNumericValue(str) {
        //     if (!str) return 0;
        //     const match = String(str).replace(/,/g, '').match(/-?\d+(\.\d+)?/);
        //     return match ? parseFloat(match[0]) : 0;
        // }
        function getNumericValue(str) {
            if (!str) return 0;
            const cleaned = String(str).replace(/,/g, '');
            const num = parseFloat(cleaned);
            return isNaN(num) ? 0 : num;
        }

        function checkShipEntirePo() {
            let totalInput = 0;
            document.querySelectorAll('#poShipBody input[name^="qtys"]').forEach(input => {
                totalInput += getNumericValue(input.value);
            });

            let totalPoQty = 0;
            currentPoShipItems.forEach(item => {
                // totalPoQty += Number(item.pocons ?? 0);
                totalPoQty += Number(item.balance ?? item.pocons ?? 0);
            });

            const isSame = totalInput === totalPoQty;
            document.getElementById('dtqty').value = isSame ? 'SAMA' : 'BEDA';
            document.getElementById('ck1').checked = isSame;
        }

        let currentPoShipItems = [];
        let selectedSupplierEtdDates = []; // BARU: simpan Supplier ETD dari PO yang dipilih

        // function openCreateShipmentModal() {
        //     const popks = document.getElementById('popka').value;

        //     fetch("{{ route('exim.import.ship.createDraft') }}", {
        //             method: "POST",
        //             headers: {
        //                 "Content-Type": "application/json",
        //                 "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        //             },
        //             body: JSON.stringify({
        //                 popks: popks.split(',')
        //             })
        //         })
        //         .then(response => response.json())
        //         .then(result => {
        //             if (!result.success) {
        //                 alert(result.message || 'Failed to create shipment draft');
        //                 return;
        //             }

        //             document.getElementById('importpkb').value = result.importpk;

        //             document.getElementById('dialog-forma').style.display = 'none';
        //             document.getElementById('dialog-formb').style.display = 'block';

        //             resetShipmentFormFields();       // ⬅️ BARU, taruh sebelum reset tanggal
        //             resetShipmentDateConstraints();

        //             const suppliers = [...new Set(currentPoShipItems.map(item => item.supnm))];
        //             const ponos = [...new Set(currentPoShipItems.map(item => item.pono))];

        //             document.getElementById('suppa').innerText = suppliers.join(', ');
        //             document.getElementById('poa').innerText = ponos.join(', ');

        //             loadShipOptions();
        //         })
        //         .catch(error => {
        //             console.error(error);
        //             alert('Failed to create shipment draft');
        //         });
        // }

        function openCreateShipmentModal() {
            const popks = document.getElementById('popka').value;

            // BARU: kumpulkan semua nilai qty yang diketik user
            const qtys = {};
            document.querySelectorAll('#poShipBody input[name^="qtys"]').forEach(input => {
                const match = input.name.match(/qtys\[(.+)\]/);
                const podtpk = match ? match[1] : null;
                if (podtpk) {
                    qtys[podtpk] = getNumericValue(input.value); // angka murni, tanpa koma
                }
            });

            fetch("{{ route('exim.import.ship.createDraft') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify({
                        popks: popks.split(','),
                        qtys: qtys // BARU: ikut dikirim
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        alert(result.message || 'Failed to create shipment draft');
                        return;
                    }

                    document.getElementById('importpkb').value = result.importpk;

                    document.getElementById('dialog-forma').style.display = 'none';
                    document.getElementById('dialog-formb').style.display = 'block';

                    resetShipmentFormFields();
                    resetShipmentDateConstraints();

                    const suppliers = [...new Set(currentPoShipItems.map(item => item.supnm))];
                    const ponos = [...new Set(currentPoShipItems.map(item => item.pono))];

                    document.getElementById('suppa').innerText = suppliers.join(', ');
                    document.getElementById('poa').innerText = ponos.join(', ');

                    loadShipOptions();
                })
                .catch(error => {
                    console.error(error);
                    alert('Failed to create shipment draft');
                });
        }


        function closeCreateShipmentModal() {
            const importpk = document.getElementById('importpkb').value;

            if (importpk) {
                fetch(`{{ url('/exim/import/ship/draft') }}/${importpk}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                }).catch(err => console.error(err));
            }

            resetShipmentFormFields(); // BARU

            document.getElementById('dialog-formb').style.display = 'none';
            document.getElementById('dialog-backdrop').style.display = 'none';
        }

        function openConfirmShipmentModal() {
            document.getElementById('dialog-formc').style.display = 'block';
        }

        function createShipmentPlan() {
            const importpk = document.getElementById('importpkc').value;
            const formData = new FormData(document.getElementById('formc'));
            const payload = Object.fromEntries(formData.entries());

            fetch(`{{ url('/exim/import/ship') }}/${importpk}/confirm`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        alert(result.message || 'Failed to save shipment plan');
                        return;
                    }

                    document.getElementById('formb').reset();

                    resetShipmentDateConstraints();

                    document.getElementById('dialog-formc').style.display = 'none';
                    document.getElementById('dialog-formb').style.display = 'none';
                    document.getElementById('dialog-backdrop').style.display = 'none';

                    const toast = document.getElementById('toastNotif');
                    toast.style.display = 'block';
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 3000);

                    document.querySelectorAll('.order-check').forEach(cb => cb.checked = false);
                    document.getElementById('myHeader').style.display = 'none';

                    location.reload();
                })
                .catch(error => {
                    console.error(error);
                    alert('Failed to save shipment plan');
                });
        }

        function myFunction(event) {
            if (event) event.stopPropagation();

            const dropdown = document.getElementById('myDropdown');
            if (!dropdown) return;

            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function closeOrderSortDropdown() {
            const dropdown = document.getElementById('myDropdown');
            if (dropdown) {
                dropdown.style.display = 'none';
            }
        }

        // function setSort(type) {
        //     document.getElementById('cari5').value = type === 'newest' ? 1 : '';
        //     document.getElementById('cari6').value = type === 'oldest' ? 1 : '';
        //     document.getElementById('cari7').value = type === 'recent' ? 1 : '';
        //     document.getElementById('cari8').value = type === 'pay' ? 1 : '';

        //     closeOrderSortDropdown();
        //     submitFilter();
        // }

        // function Searcha() {
        //     setSort('newest');
        // }

        // function Searchb() {
        //     setSort('oldest');
        // }

        // function Searchd() {
        //     setSort('recent');
        // }

        // function Searche() {
        //     setSort('pay');
        // }

        function setSort(type) {
            document.getElementById('cari5').value = type === 'newest' ? 1 : '';
            document.getElementById('cari6').value = type === 'oldest' ? 1 : '';
            document.getElementById('cari7').value = type === 'recent' ? 1 : '';
            document.getElementById('cari8').value = type === 'etdAsc' ? 1 : '';
            document.getElementById('cari9').value = type === 'etdDsc' ? 1 : '';

            // used to rely on a full reload to re-render this label from Blade
            const sortLabels = {
                newest: 'Newest entry',
                oldest: 'Oldest entry',
                recent: 'Recently modify',
                etdAsc: 'Supplier ETD (Awal)',
                etdDsc: 'Supplier ETD (Akhir)',
            };
            const trigger = document.querySelector('#orderSortTrigger span');
            if (trigger) trigger.textContent = sortLabels[type] || 'Newest entry';

            document.getElementById('page').value = 1; // TAMBAHAN: reset ke halaman 1
            closeOrderSortDropdown();
            submitFilter();
        }

        function Searcha() {
            setSort('newest');
        }

        function Searchb() {
            setSort('oldest');
        }

        function Searchd() {
            setSort('recent');
        }

        function Searche() {
            setSort('etdAsc');
        }

        function Searchf() {
            setSort('etdDsc');
        }

        let shipOptionsData = null;

        function loadShipOptions() {
            if (shipOptionsData) {
                populateShipOptionSelects();
                return;
            }

            fetch("{{ route('exim.import.ship.options') }}")
                .then(response => response.json())
                .then(result => {
                    shipOptionsData = result;
                    populateShipOptionSelects();
                })
                .catch(error => {
                    console.error(error);
                    alert('Failed to load shipment options');
                });
        }


        function populateShipOptionSelects() {
            // ==== Vassal type ====
            const vastypeMenu = document.getElementById('vastypeMenu');
            vastypeMenu.innerHTML = shipOptionsData.vassal.map(v =>
                `<div class="custom-dropdown-item" onclick="selectFormDropdownOption('vastype','vastypeLabel','vastypeMenu','${v.vassalpk}','${escapeHtml(v.typenm)}', this)">${escapeHtml(v.typenm)}</div>`
            ).join('');

            // ==== Arrival city ====
            const arrivalMenu = document.getElementById('arrivalMenu');
            arrivalMenu.innerHTML = shipOptionsData.arrival.map(a =>
                `<div class="custom-dropdown-item" onclick="selectFormDropdownOption('arrival','arrivalLabel','arrivalMenu','${a.arrivalpk}','${escapeHtml(a.city)}', this)">${escapeHtml(a.city)}</div>`
            ).join('');

            // Port dikosongkan dulu sampai arrival dipilih
            document.getElementById('portMenu').innerHTML = '';
            document.getElementById('port').value = '';
            document.getElementById('portLabel').textContent = 'Choose port';
        }

        function populatePortOptions(arrivalpk) {
            const portMenu = document.getElementById('portMenu');
            const portLabel = document.getElementById('portLabel');

            document.getElementById('port').value = '';
            portLabel.textContent = 'Choose port';

            if (!arrivalpk) {
                portMenu.innerHTML = '';
                return;
            }

            const filteredPorts = shipOptionsData.port.filter(p => String(p.arrivalpk) === String(arrivalpk));

            portMenu.innerHTML = filteredPorts.map(p =>
                `<div class="custom-dropdown-item" onclick="selectFormDropdownOption('port','portLabel','portMenu','${p.portpk}','${escapeHtml(p.portnm)}', this)">${escapeHtml(p.portnm)}</div>`
            ).join('');
        }

        // function validateShipmentForm() {
        //     let isValid = true;

        //     const vastype = document.getElementById('vastype');
        //     const vastypeTrigger = document.querySelector('#vastype-dropdown .custom-dropdown-trigger');
        //     const vastypeError = document.getElementById('vastype-error');
        //     if (!vastype.value) {
        //         vastypeTrigger.classList.add('field-error');
        //         vastypeError.style.display = 'block';
        //         isValid = false;
        //     } else {
        //         vastypeTrigger.classList.remove('field-error');
        //         vastypeError.style.display = 'none';
        //     }

        //     const etd = document.getElementById('etd');
        //     const etdError = document.getElementById('etd-error');
        //     if (!etd.value) {
        //         etd.classList.add('field-error');
        //         etdError.style.display = 'block';
        //         isValid = false;
        //     } else {
        //         etd.classList.remove('field-error');
        //         etdError.style.display = 'none';
        //     }

        //     return isValid;
        // }
        // function validateShipmentForm() {
        //     let isValid = true;

        //     const vastype = document.getElementById('vastype');
        //     const vastypeTrigger = document.querySelector('#vastype-dropdown .custom-dropdown-trigger');
        //     const vastypeError = document.getElementById('vastype-error');
        //     if (!vastype.value) {
        //         vastypeTrigger.classList.add('field-error');
        //         vastypeError.style.display = 'block';
        //         isValid = false;
        //     } else {
        //         vastypeTrigger.classList.remove('field-error');
        //         vastypeError.style.display = 'none';
        //     }

        //     // ETD - wajib diisi + tidak boleh lebih lama dari 7 hari lalu
        //     const etd = document.getElementById('etd');
        //     const etdError = document.getElementById('etd-error');
        //     const etdDate = getShipmentFieldDate('etd');

        //     if (!etd.value) {
        //         setShipmentFieldError(etd, etdError, 'ETD is required');
        //         isValid = false;
        //     } else if (!etdDate || etdDate < shipmentMinDate) {
        //         setShipmentFieldError(etd, etdError, 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
        //         isValid = false;
        //     } else {
        //         clearShipmentFieldError(etd, etdError);
        //     }

        //     // ETA - opsional, tapi jika diisi tidak boleh lebih lama dari 7 hari lalu
        //     const eta = document.getElementById('eta');
        //     const etaError = document.getElementById('eta-error');
        //     const etaDate = getShipmentFieldDate('eta');

        //     clearShipmentFieldError(eta, etaError);
        //     if (eta.value && (!etaDate || etaDate < shipmentMinDate)) {
        //         setShipmentFieldError(eta, etaError, 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
        //         isValid = false;
        //     }

        //     // EIH - opsional, tapi jika diisi tidak boleh lebih lama dari 7 hari lalu
        //     const eih = document.getElementById('eih');
        //     const eihError = document.getElementById('eih-error');
        //     const eihDate = getShipmentFieldDate('eih');

        //     clearShipmentFieldError(eih, eihError);
        //     if (eih.value && (!eihDate || eihDate < shipmentMinDate)) {
        //         setShipmentFieldError(eih, eihError, 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
        //         isValid = false;
        //     }

        //     // Validasi urutan: ETD <= ETA <= EIH
        //     if (!validateShipmentDateOrder()) {
        //         isValid = false;
        //     }

        //     return isValid;
        // }
        function validateShipmentForm() {
            let isValid = true;

            const vastype = document.getElementById('vastype');
            const vastypeTrigger = document.querySelector('#vastype-dropdown .custom-dropdown-trigger');
            const vastypeError = document.getElementById('vastype-error');
            if (!vastype.value) {
                vastypeTrigger.classList.add('field-error');
                vastypeError.style.display = 'block';
                isValid = false;
            } else {
                vastypeTrigger.classList.remove('field-error');
                vastypeError.style.display = 'none';
            }

            // ETD - wajib diisi + tunduk pada currentShipmentMinDate (bisa null kalau Supplier ETD ada)
            const etd = document.getElementById('etd');
            const etdError = document.getElementById('etd-error');
            const etdDate = getShipmentFieldDate('etd');

            if (!etd.value) {
                setShipmentFieldError(etd, etdError, 'ETD is required');
                isValid = false;
            } else if (currentShipmentMinDate && etdDate && etdDate < currentShipmentMinDate) {
                setShipmentFieldError(etd, etdError, 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
                isValid = false;
            } else {
                clearShipmentFieldError(etd, etdError);
            }

            // ETA - opsional, tunduk pada currentShipmentMinDate
            const eta = document.getElementById('eta');
            const etaError = document.getElementById('eta-error');
            const etaDate = getShipmentFieldDate('eta');

            clearShipmentFieldError(eta, etaError);
            if (eta.value && currentShipmentMinDate && etaDate && etaDate < currentShipmentMinDate) {
                setShipmentFieldError(eta, etaError, 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
                isValid = false;
            }

            // EIH - opsional, tunduk pada currentShipmentMinDate
            const eih = document.getElementById('eih');
            const eihError = document.getElementById('eih-error');
            const eihDate = getShipmentFieldDate('eih');

            clearShipmentFieldError(eih, eihError);
            if (eih.value && currentShipmentMinDate && eihDate && eihDate < currentShipmentMinDate) {
                setShipmentFieldError(eih, eihError, 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
                isValid = false;
            }

            // Validasi urutan: ETD <= ETA <= EIH (tidak berubah)
            if (!validateShipmentDateOrder()) {
                isValid = false;
            }

            return isValid;
        }

        function openConfirmShipmentModal() {
            if (!validateShipmentForm()) {
                return;
            }

            document.getElementById('importpkc').value = document.getElementById('importpkb').value;
            document.getElementById('vastype1').value = document.getElementById('vastype').value;
            document.getElementById('vastnm1').value = document.getElementById('vastnm').value;
            document.getElementById('bill1').value = document.getElementById('bill').value;
            document.getElementById('arrivalpk1').value = document.getElementById('arrival').value;
            document.getElementById('portpk1').value = document.getElementById('port').value;
            document.getElementById('weight1').value = document.getElementById('weight').value;
            document.getElementById('dp1').value = document.getElementById('dp').value;
            document.getElementById('dl1').value = document.getElementById('dl').value;
            document.getElementById('dt1').value = document.getElementById('dt').value;
            document.getElementById('amount1').value = document.getElementById('amount').value;
            document.getElementById('etd1').value = document.getElementById('etd').value;
            document.getElementById('eta1').value = document.getElementById('eta').value;
            document.getElementById('eih1').value = document.getElementById('eih').value;
            document.getElementById('popkall').value = document.getElementById('popka').value;

            document.getElementById('dialog-formb').style.display = 'none';
            document.getElementById('dialog-formc').style.display = 'block';
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('myDropdown');
            const trigger = document.getElementById('orderSortTrigger');
            const icon = document.querySelector('.imgsort');

            if (!dropdown) return;

            const insideDropdown = dropdown.contains(event.target);
            const insideTrigger = trigger && trigger.contains(event.target);
            const insideIcon = icon && icon.contains(event.target);

            if (!insideDropdown && !insideTrigger && !insideIcon) {
                dropdown.style.display = 'none';
            }
        });

        let positionHeader;
        let lastScrollTop = 0;

        // ==== Shipment date validation helpers (Order page only) ====

        function getShipmentMinDate() {
            const min = new Date();
            min.setHours(0, 0, 0, 0);
            min.setDate(min.getDate() - 7);
            return min;
        }

        const shipmentMinDate = getShipmentMinDate();
        const shipmentFlatpickrs = {};
        let currentShipmentMinDate = shipmentMinDate; // BARU: minDate aktif saat ini, bisa berubah tergantung ada/tidaknya Supplier ETD

        // BARU: parsing tanggal Supplier ETD dari data-etd (format DB umumnya "YYYY-MM-DD" atau "YYYY-MM-DD HH:MM:SS")
        function parseSupplierEtd(str) {
            if (!str) return null;
            const trimmed = str.trim();
            if (trimmed === '' || trimmed.startsWith('0000-00-00')) return null;

            const datePart = trimmed.split(' ')[0];
            const [y, m, d] = datePart.split('-').map(Number);
            if (!y || !m || !d) return null;

            return new Date(y, m - 1, d);
        }

        // BARU: hitung ETD/ETA/EIH otomatis dari Supplier ETD
        // ETD = Supplier ETD + 1 hari, ETA = ETD + 5 hari, EIH = ETA + 14 hari
        function computeAutoShipmentDates(baseEtd) {
            if (!baseEtd) return null;

            const addDays = (date, days) => {
                const d = new Date(date);
                d.setDate(d.getDate() + days);
                return d;
            };

            const etd = addDays(baseEtd, 1);
            const eta = addDays(etd, 5);
            const eih = addDays(eta, 14);

            return { etd, eta, eih };
        }

        // BARU: isi otomatis field ETD/ETA/EIH di modal Create shipment plan
        // function autoFillShipmentDates() {
        //     if (!selectedSupplierEtdDates || selectedSupplierEtdDates.length === 0) return;

        //     // Kalau PO yang dipilih lebih dari satu dengan Supplier ETD berbeda,
        //     // pakai yang paling akhir sebagai acuan
        //     const baseEtd = new Date(Math.max(...selectedSupplierEtdDates.map(d => d.getTime())));

        //     const auto = computeAutoShipmentDates(baseEtd);
        //     if (!auto) return;

        //     // setDate(..., true) akan trigger onChange bawaan flatpickr,
        //     // otomatis menjalankan syncShipmentDateConstraints() & validasi yang sudah ada
        //     if (shipmentFlatpickrs.etd) shipmentFlatpickrs.etd.setDate(auto.etd, true);
        //     if (shipmentFlatpickrs.eta) shipmentFlatpickrs.eta.setDate(auto.eta, true);
        //     if (shipmentFlatpickrs.eih) shipmentFlatpickrs.eih.setDate(auto.eih, true);
        // }
        function autoFillShipmentDates() {
            if (!selectedSupplierEtdDates || selectedSupplierEtdDates.length === 0) return;

            // BARU: jangan timpa kalau ETD sudah pernah diisi (baik otomatis maupun manual)
            const etdInput = document.getElementById('etd');
            if (etdInput && etdInput.value) return;

            const baseEtd = new Date(Math.max(...selectedSupplierEtdDates.map(d => d.getTime())));

            const auto = computeAutoShipmentDates(baseEtd);
            if (!auto) return;

            if (shipmentFlatpickrs.etd) shipmentFlatpickrs.etd.setDate(auto.etd, true);
            if (shipmentFlatpickrs.eta) shipmentFlatpickrs.eta.setDate(auto.eta, true);
            if (shipmentFlatpickrs.eih) shipmentFlatpickrs.eih.setDate(auto.eih, true);
        }

        function setShipmentFieldError(input, errorEl, message) {
            input.classList.add('field-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }
        }

        function clearShipmentFieldError(input, errorEl) {
            input.classList.remove('field-error');
            if (errorEl) errorEl.style.display = 'none';
        }

        function getShipmentFieldDate(id) {
            const fp = shipmentFlatpickrs[id];
            return (fp && fp.selectedDates.length) ? fp.selectedDates[0] : null;
        }

        // Sinkronisasi batas antar field secara dinamis di kalender itu sendiri
        function syncShipmentDateConstraints(changedId, date) {
            if (!date) return;

            if (changedId === 'etd' && shipmentFlatpickrs.eta) {
                shipmentFlatpickrs.eta.set('minDate', date); // ETA >= ETD
            }
            if (changedId === 'eta') {
                if (shipmentFlatpickrs.etd) shipmentFlatpickrs.etd.set('maxDate', date); // ETD <= ETA
                if (shipmentFlatpickrs.eih) shipmentFlatpickrs.eih.set('minDate', date); // EIH >= ETA
            }
            if (changedId === 'eih' && shipmentFlatpickrs.eta) {
                shipmentFlatpickrs.eta.set('maxDate', date); // ETA <= EIH
            }
        }

        // Validasi urutan tanggal, dipanggil saat submit maupun saat onChange
        function validateShipmentDateOrder() {
            const etdDate = getShipmentFieldDate('etd');
            const etaDate = getShipmentFieldDate('eta');
            const eihDate = getShipmentFieldDate('eih');

            const eta = document.getElementById('eta');
            const etaError = document.getElementById('eta-error');
            const eih = document.getElementById('eih');
            const eihError = document.getElementById('eih-error');

            let isValid = true;

            if (etdDate && etaDate && etdDate > etaDate) {
                setShipmentFieldError(eta, etaError, 'ETA tidak boleh lebih kecil dari ETD');
                isValid = false;
            }
            if (etaDate && eihDate && etaDate > eihDate) {
                setShipmentFieldError(eih, eihError, 'EIH tidak boleh lebih kecil dari ETA');
                isValid = false;
            }
            if (!etaDate && etdDate && eihDate && etdDate > eihDate) {
                setShipmentFieldError(eih, eihError, 'EIH tidak boleh lebih kecil dari ETD');
                isValid = false;
            }

            return isValid;
        }

        // Reset constraint & value setiap kali modal Create Shipment Plan dibuka ulang
        // function resetShipmentDateConstraints() {
        //     ['etd', 'eta', 'eih'].forEach(id => {
        //         const fp = shipmentFlatpickrs[id];
        //         if (fp) {
        //             fp.set('minDate', shipmentMinDate);
        //             fp.set('maxDate', null);
        //             fp.clear();
        //         }
        //         const errorEl = document.getElementById(id + '-error');
        //         const input = document.getElementById(id);
        //         if (errorEl) errorEl.style.display = 'none';
        //         if (input) input.classList.remove('field-error');
        //     });
        // }
        function resetShipmentDateConstraints() {
            ['etd', 'eta', 'eih'].forEach(id => {
                const fp = shipmentFlatpickrs[id];
                if (fp) {
                    fp.set('maxDate', null);
                    fp.clear();
                }
                const errorEl = document.getElementById(id + '-error');
                const input = document.getElementById(id);
                if (errorEl) errorEl.style.display = 'none';
                if (input) input.classList.remove('field-error');
            });

            applyShipmentMinDateRule(); // BARU: minDate ditentukan sesuai ada/tidaknya Supplier ETD
        }

        // BARU: reset Vassal type & field form lain (selain tanggal) tiap kali modal dibuka utk PO/OP baru
        function resetShipmentFormFields() {
            // Vassal type
            document.getElementById('vastype').value = '';
            document.getElementById('vastypeLabel').textContent = 'Choose vassal';
            document.querySelector('#vastype-dropdown .custom-dropdown-trigger').classList.remove('field-error');
            document.getElementById('vastype-error').style.display = 'none';

            const vastypeMenu = document.getElementById('vastypeMenu');
            if (vastypeMenu) {
                vastypeMenu.querySelectorAll('.custom-dropdown-item').forEach(item => item.classList.remove('selected'));
            }

            // Field text lain
            document.getElementById('vastnm').value = '';
            document.getElementById('bill').value = '';
            document.getElementById('weight').value = '';
            document.getElementById('dp').value = '';
            document.getElementById('dl').value = '';
            document.getElementById('dt').value = '';
            document.getElementById('amount').value = '';

            // Arrival & Port
            document.getElementById('arrival').value = '';
            document.getElementById('arrivalLabel').textContent = 'Choose arrival';
            document.getElementById('port').value = '';
            document.getElementById('portLabel').textContent = 'Choose port';
            document.getElementById('portMenu').innerHTML = '';

            const arrivalMenu = document.getElementById('arrivalMenu');
            if (arrivalMenu) {
                arrivalMenu.querySelectorAll('.custom-dropdown-item').forEach(item => item.classList.remove('selected'));
            }
        }

        // BARU: tentukan minDate ETD/ETA/EIH — longgar kalau ada Supplier ETD, ketat (7 hari lalu) kalau tidak ada
        function applyShipmentMinDateRule() {
            const hasSupplierEtd = selectedSupplierEtdDates && selectedSupplierEtdDates.length > 0;

            // Kalau Supplier ETD ada, minDate mengikuti rumus (tidak dibatasi 7 hari ke belakang dari hari ini)
            // Kalau tidak ada, tetap pakai aturan lama
            currentShipmentMinDate = hasSupplierEtd ? null : shipmentMinDate;

            ['etd', 'eta', 'eih'].forEach(id => {
                const fp = shipmentFlatpickrs[id];
                if (fp) fp.set('minDate', currentShipmentMinDate);
            });
        }

        // document.addEventListener('DOMContentLoaded', function() {
        //     const header = document.getElementById('myHeader');
        //     const mainHeader = document.querySelector('nav.navbar');

        //     if (header && header.parentElement !== document.body) {
        //         document.body.appendChild(header);
        //     }

        //     function getScrollTop(target) {
        //         if (target === document || target === window) {
        //             return window.scrollY || window.pageYOffset;
        //         }
        //         return target.scrollTop || 0;
        //     }

        //     positionHeader = function(scrollTop) {
        //         if (!mainHeader || !header) return;
        //         const mainHeaderHeight = mainHeader.offsetHeight;
        //         const top = Math.max(mainHeaderHeight - scrollTop, 0);
        //         header.style.top = top + 'px';
        //     };

        //     tangkap scroll dari MANAPUN sumbernya (window ataupun div manapun yang scroll)
        //     document.addEventListener('scroll', function(e) {
        //         // Abaikan scroll yang terjadi di dalam modal shipment
        //         const modal = document.getElementById('dialog-forma');
        //         if (modal && modal.contains(e.target)) {
        //             return;
        //         }

        //         const target = e.target === document ? window : e.target;
        //         const scrollTop = getScrollTop(target);
        //         lastScrollTop = scrollTop;
        //         positionHeader(scrollTop);
        //     }, true);

        //     window.addEventListener('resize', function() {
        //         positionHeader(lastScrollTop);
        //     });

        //     positionHeader(0);

        //     const closeMenu = document.getElementById('cls');

        //     // delegated (not bound directly) because #check-all is inside #tabel1,
        //     // which gets replaced wholesale on every AJAX filter/sort/search update
        //     document.addEventListener('change', function(e) {
        //         if (e.target.id !== 'check-all') return;

        //         document.querySelectorAll('.order-check').forEach(function(checkbox) {
        //             checkbox.checked = e.target.checked;
        //         });

        //         toggleActionBar();
        //     });

        //     closeMenu.addEventListener('click', function() {
        //         document.querySelectorAll('.order-check').forEach(function(checkbox) {
        //             checkbox.checked = false;
        //         });

        //         const checkAll = document.getElementById('check-all');
        //         if (checkAll) checkAll.checked = false;
        //         document.getElementById('myHeader').style.display = 'none';
        //         document.getElementById('countp').innerText = '0 items selected';
        //         document.getElementById('approve').style.display = 'block';
        //     });

        //     // ['etd', 'eta', 'eih'].forEach(id => {
        //     //     const el = document.getElementById(id);
        //     //     if (!el) return;

        //     //     flatpickr(`#${id}`, {
        //     //         dateFormat: 'd/m/Y',
        //     //         allowInput: true,
        //     //         clickOpens: false, // kontrol buka lewat ikon, bukan klik input langsung
        //     //         onReady: function(selectedDates, dateStr, instance) {
        //     //             const footer = document.createElement('div');
        //     //             footer.style.cssText =
        //     //                 'display:flex; justify-content:space-between; padding:8px 12px; border-top:1px solid #eee;';

        //     //             const todayBtn = document.createElement('a');
        //     //             todayBtn.textContent = 'Today';
        //     //             todayBtn.href = 'javascript:void(0)';
        //     //             todayBtn.style.cssText =
        //     //                 'color:#359DD9; text-decoration:none; font-weight:600;';
        //     //             todayBtn.addEventListener('click', function() {
        //     //                 instance.setDate(new Date(), true);
        //     //             });

        //     //             const closeBtn = document.createElement('a');
        //     //             closeBtn.textContent = 'Close';
        //     //             closeBtn.href = 'javascript:void(0)';
        //     //             closeBtn.style.cssText =
        //     //                 'color:#000; text-decoration:none; font-weight:600;';
        //     //             closeBtn.addEventListener('click', function() {
        //     //                 instance.close();
        //     //             });

        //     //             footer.appendChild(todayBtn);
        //     //             footer.appendChild(closeBtn);
        //     //             instance.calendarContainer.appendChild(footer);
        //     //         },
        //     //     });
        //     // });
        //     ['etd', 'eta', 'eih'].forEach(id => {
        //         const el = document.getElementById(id);
        //         if (!el) return;

        //         shipmentFlatpickrs[id] = flatpickr(`#${id}`, {
        //             dateFormat: 'd/m/Y',
        //             allowInput: true,
        //             clickOpens: false,
        //             minDate: shipmentMinDate, // batas bawah: 7 hari sebelum hari ini
        //             onReady: function(selectedDates, dateStr, instance) {
        //                 const footer = document.createElement('div');
        //                 footer.style.cssText =
        //                     'display:flex; justify-content:space-between; padding:8px 12px; border-top:1px solid #eee;';

        //                 const todayBtn = document.createElement('a');
        //                 todayBtn.textContent = 'Today';
        //                 todayBtn.href = 'javascript:void(0)';
        //                 todayBtn.style.cssText =
        //                     'color:#359DD9; text-decoration:none; font-weight:600;';
        //                 todayBtn.addEventListener('click', function() {
        //                     instance.setDate(new Date(), true);
        //                 });

        //                 const closeBtn = document.createElement('a');
        //                 closeBtn.textContent = 'Close';
        //                 closeBtn.href = 'javascript:void(0)';
        //                 closeBtn.style.cssText =
        //                     'color:#000; text-decoration:none; font-weight:600;';
        //                 closeBtn.addEventListener('click', function() {
        //                     instance.close();
        //                 });

        //                 footer.appendChild(todayBtn);
        //                 footer.appendChild(closeBtn);
        //                 instance.calendarContainer.appendChild(footer);
        //             },
        //             onChange: function(selectedDates) {
        //                 syncShipmentDateConstraints(id, selectedDates[0]);

        //                 const input = document.getElementById(id);
        //                 const errorEl = document.getElementById(id + '-error');
        //                 clearShipmentFieldError(input, errorEl);

        //                 validateShipmentDateOrder();
        //             },
        //         });
        //     });

        //     // ikon kalender buka datepicker
        //     document.querySelectorAll('.date-icon-trigger').forEach(icon => {
        //         icon.addEventListener('click', function() {
        //             const targetId = this.dataset.target;
        //             const input = document.getElementById(targetId);
        //             if (input && input._flatpickr) {
        //                 input._flatpickr.open();
        //             }
        //         });
        //     });
        // });

        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('myHeader');

            if (header && header.parentElement !== document.body) {
                document.body.appendChild(header);
            }

            const closeMenu = document.getElementById('cls');

            // delegated (not bound directly) because #check-all is inside #tabel1,
            // which gets replaced wholesale on every AJAX filter/sort/search update
            document.addEventListener('change', function(e) {
                if (e.target.id !== 'check-all') return;

                document.querySelectorAll('.order-check').forEach(function(checkbox) {
                    checkbox.checked = e.target.checked;
                });

                toggleActionBar();
            });

            closeMenu.addEventListener('click', function() {
                document.querySelectorAll('.order-check').forEach(function(checkbox) {
                    checkbox.checked = false;
                });

                const checkAll = document.getElementById('check-all');
                if (checkAll) checkAll.checked = false;
                document.getElementById('myHeader').style.display = 'none';
                document.getElementById('countp').innerText = '0 items selected';
                document.getElementById('approve').style.display = 'block';
            });

            ['etd', 'eta', 'eih'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;

                shipmentFlatpickrs[id] = flatpickr(`#${id}`, {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    clickOpens: false,
                    minDate: shipmentMinDate, // batas bawah: 7 hari sebelum hari ini
                    onReady: function(selectedDates, dateStr, instance) {
                        const footer = document.createElement('div');
                        footer.style.cssText =
                            'display:flex; justify-content:space-between; padding:8px 12px; border-top:1px solid #eee;';

                        const todayBtn = document.createElement('a');
                        todayBtn.textContent = 'Today';
                        todayBtn.href = 'javascript:void(0)';
                        todayBtn.style.cssText =
                            'color:#359DD9; text-decoration:none; font-weight:600;';
                        todayBtn.addEventListener('click', function() {
                            instance.setDate(new Date(), true);
                        });

                        const closeBtn = document.createElement('a');
                        closeBtn.textContent = 'Close';
                        closeBtn.href = 'javascript:void(0)';
                        closeBtn.style.cssText =
                            'color:#000; text-decoration:none; font-weight:600;';
                        closeBtn.addEventListener('click', function() {
                            instance.close();
                        });

                        footer.appendChild(todayBtn);
                        footer.appendChild(closeBtn);
                        instance.calendarContainer.appendChild(footer);
                    },
                    onChange: function(selectedDates) {
                        syncShipmentDateConstraints(id, selectedDates[0]);

                        const input = document.getElementById(id);
                        const errorEl = document.getElementById(id + '-error');
                        clearShipmentFieldError(input, errorEl);

                        validateShipmentDateOrder();
                    },
                });
            });

            // ikon kalender buka datepicker
            document.querySelectorAll('.date-icon-trigger').forEach(icon => {
                icon.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const input = document.getElementById(targetId);
                    if (input && input._flatpickr) {
                        input._flatpickr.open();
                    }
                });
            });
        });


        function toggleCustomDropdown(event, menuId) {
            event.stopPropagation();

            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                if (menu.id !== menuId) menu.style.display = 'none';
            });

            const menu = document.getElementById(menuId);
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function selectCustomOption(hiddenInputId, labelId, menuId, value, label, el) {
            document.getElementById(hiddenInputId).value = value;
            document.getElementById(labelId).textContent = label;
            document.getElementById(menuId).style.display = 'none';

            el.closest('.custom-dropdown-menu').querySelectorAll('.custom-dropdown-item').forEach(item => {
                item.classList.remove('selected');
            });
            el.classList.add('selected');

            submitFilter();
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        // function selectFormDropdownOption(hiddenInputId, labelId, menuId, value, label, el) {
        //     document.getElementById(hiddenInputId).value = value;
        //     document.getElementById(labelId).textContent = label;
        //     document.getElementById(menuId).style.display = 'none';

        //     el.closest('.custom-dropdown-menu').querySelectorAll('.custom-dropdown-item').forEach(item => {
        //         item.classList.remove('selected');
        //     });
        //     el.classList.add('selected');

        //     if (hiddenInputId === 'vastype' && value) {
        //         document.querySelector('#vastype-dropdown .custom-dropdown-trigger').classList.remove('field-error');
        //         document.getElementById('vastype-error').style.display = 'none';
        //     }

        //     if (hiddenInputId === 'arrival') {
        //         populatePortOptions(value);
        //     }
        // }
        function selectFormDropdownOption(hiddenInputId, labelId, menuId, value, label, el) {
            document.getElementById(hiddenInputId).value = value;
            document.getElementById(labelId).textContent = label;
            document.getElementById(menuId).style.display = 'none';

            el.closest('.custom-dropdown-menu').querySelectorAll('.custom-dropdown-item').forEach(item => {
                item.classList.remove('selected');
            });
            el.classList.add('selected');

            if (hiddenInputId === 'vastype' && value) {
                document.querySelector('#vastype-dropdown .custom-dropdown-trigger').classList.remove('field-error');
                document.getElementById('vastype-error').style.display = 'none';

                autoFillShipmentDates(); // BARU: begitu Vassal type terisi, baru tanggal otomatis muncul
            }

            if (hiddenInputId === 'arrival') {
                populatePortOptions(value);
            }
        }

        document.addEventListener('click', function(event) {
            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                const dropdown = menu.closest('.custom-dropdown');
                if (dropdown && !dropdown.contains(event.target)) {
                    menu.style.display = 'none';
                }
            });
        });

        document.getElementById('vastype').addEventListener('change', function() {
            if (this.value && this.value !== 'Choose vassal') {
                this.classList.remove('field-error');
                document.getElementById('vastype-error').style.display = 'none';
            }
        });

        document.getElementById('etd').addEventListener('change', function() {
            if (this.value) {
                this.classList.remove('field-error');
                document.getElementById('etd-error').style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            const row = e.target.closest('tr.order-row');
            if (!row) return;

            // Kalau yang diklik adalah link, checkbox, atau elemen interaktif lain -- biarkan perilaku aslinya jalan
            if (e.target.closest('a, input, button')) {
                return;
            }

            const checkbox = row.querySelector('.order-check');
            if (!checkbox) return;

            checkbox.checked = !checkbox.checked;

            // Trigger event 'change' supaya listener onchange yang sudah ada (toggleActionBar, clearOrderNoticeIfNeeded) ikut jalan
            checkbox.dispatchEvent(new Event('change'));
        });

        document.getElementById('ck1').addEventListener('click', function () {
            if (this.checked) {
                // user mencentang manual -> kembalikan semua input ke nilai asli
                document.querySelectorAll('#poShipBody input[name^="qtys"]').forEach(input => {
                    const match = input.name.match(/qtys\[(.+)\]/);
                    const podtpk = match ? match[1] : null;

                    if (podtpk && originalQtyValues.hasOwnProperty(podtpk)) {
                        input.value = originalQtyValues[podtpk];
                    }
                });

                checkShipEntirePo();
            }
        });

        function updateShipmentPlanButtonState() {
            const btn = document.getElementById('btnShipmentPlan');
            if (!btn) return; // role selain exim/import tidak punya tombol ini

            const checkedRows = document.querySelectorAll('.order-check:checked');

            let disable = false;

            if (checkedRows.length === 0) {
                disable = true;
            } else {
                checkedRows.forEach(function (chk) {
                    const etd = chk.getAttribute('data-etd');
                    const status = chk.getAttribute('data-status');

                    const etdEmpty = !etd || etd.trim() === '' || etd.startsWith('0000-00-00');
                    const isOnHold = status !== '1' && status !== '2'; // selain Partial ship & Processed = On hold

                    if (etdEmpty && isOnHold) {
                        disable = true;
                    }
                });
            }

            if (disable) {
                btn.classList.add('disabled');
            } else {
                btn.classList.remove('disabled');
            }
        }

        document.addEventListener('DOMContentLoaded', updateShipmentPlanButtonState);

        function togglePoExpand(event, wrap) {
            event.stopPropagation(); // jangan trigger buka detail order di <tr>

            const collapsed = wrap.querySelector('.po-collapsed');
            const expanded = wrap.querySelector('.po-expanded');

            const isExpanded = expanded.style.display === 'block';

            if (isExpanded) {
                expanded.style.display = 'none';
                collapsed.style.display = 'block';
            } else {
                expanded.style.display = 'block';
                collapsed.style.display = 'none';
            }
        }
    </script>
@endsection

@section('content')
    @include('menu.exim-import.prt.header')
    @include('menu.exim-import.prt.filter-order')
    @include('menu.exim-import.prt.sort-order')
    @include('menu.exim-import.prt.table-order')
    @include('menu.exim-import.prt.action-order')
    @include('menu.exim-import.prt.modal-adjustment')
    @include('menu.exim-import.prt.modal-create-shipment')
    @include('menu.exim-import.prt.modal-confirm')

    <div class="contenta"></div>
@endsection
