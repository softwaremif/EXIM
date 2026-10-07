@extends('layout.main')

@section('css_custom')
    <style>
        /* ==== dari modal shipment plan ==== */
        #DgImport td,
        #DgImport th {
            word-break: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            vertical-align: top;
        }

        /* PAKSA section langsung di dalam modal-body mengikuti flow block normal,
                                                                                                                                                                                                                                                                                                                                           KECUALI #tabel2b (dikontrol JS), .row (butuh flex), dan header modal (butuh flex) */
        #shipmentPlanModal .modal-body>div:not(#tabel2b):not(.row):not(#shipmentPlanHeader) {
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

        /* ==== dari table-ship (list shipment) ==== */
        .ship-table th,
        .ship-table td {
            vertical-align: top;
            word-break: break-word;
            white-space: normal;
        }

        .ship-table thead th {
            vertical-align: middle !important;
        }

        .ship-table td:nth-child(8),
        /* Qty */
        .ship-table td:nth-child(9),
        /* Unit */
        .ship-table td:nth-child(13),
        /* ETD */
        .ship-table td:nth-child(14) {
            /* Status */
            white-space: nowrap;
        }

        .ship-update-dropdown {
            position: relative;
            display: inline-block;
        }

        .ship-update-trigger {
            display: inline-block;
            color: #359DD9;
            text-decoration: none;
            cursor: pointer;
        }

        .ship-update-menu {
            position: absolute;
            margin-top: 0px;
            min-width: 200px;
            background: #FFFFFF;
            border: 1px solid #D9D9D9;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            z-index: 9999;
            display: none;
            text-align: left;

        }

        .ship-update-menu a {
            display: block;
            padding: 8px 10px;
            font-size: 14px;
            line-height: 1.3;
            color: #111111;
            text-decoration: none;
            border-bottom: 1px solid #D9D9D9;
            background: #fff;
            white-space: nowrap;
        }

        .ship-update-menu a:last-child {
            border-bottom: none;
        }

        .ship-update-menu a:hover {
            background: #F8F8F8;
        }

        .ship-update-menu a.danger {
            color: #111111;
        }

        .ship-update-dropdown:hover .ship-update-menu {
            display: block;
        }

        #tabel1,
        #tabel1 .table-responsive,
        .ship-table,
        .ship-table tbody,
        .ship-table tr,
        .ship-table td {
            overflow: visible !important;
        }

        .container-cari-inline {
            position: static;
            width: 100%;
        }

        .frame-cari-inline {
            position: static;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #F5F5F5;
            border-radius: 8px;
            padding: 8px 12px;
            width: 20%;
        }

        .frame-cari-inline .input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 14px;
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

        .update-status-trigger {
            display: inline-block;
            font-weight: 700;
            font-size: 14px;

        }

        .update-status-menu {
            position: absolute;
            margin-top: 8px;
            min-width: 230px;
            background: #FFFFFF;
            border: 1px solid #D9D9D9;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            z-index: 10000;
            display: none;
            text-align: left;
        }

        .update-status-menu a {

            display: block;
            padding: 12px 16px;
            font-size: 14px;
            line-height: 1.3;
            color: #111111;
            text-decoration: none;
            border-bottom: 1px solid #D9D9D9;
            background: #fff;
            white-space: nowrap;
        }

        .update-status-menu a:hover {
            background: #F8F8F8;
        }

        .update-status-menu.show {
            display: block;
        }

        #cancelConfirmBtn {
            text-decoration: none !important;
            position: absolute !important;
            left: auto !important;
            right: 35px !important;
            bottom: 25px !important;
            width: 150px !important;
        }

        .modal-backdrop {
            z-index: 10000
        }

        .modal {
            z-index: 10001
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

        .view-mode{
            display: block;
            margin-top: 4px;
        }

        .ship-table-scroll {
            max-height: 450px;   /* fixed px lebih stabil daripada vh, sesuaikan sesuai selera */
            min-height: 250px;   /* opsional, biar tidak terlalu kecil */
            overflow-y: auto;
            overflow-x: auto;
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 8px;   /* opsional, biar mirip rounded container di referensi */
        }

        .ship-table-scroll .ship-table {
            margin-bottom: 0;
        }

        .ship-table-scroll thead.ship-thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background-color: #fff;
            box-shadow: 0 1px 0 #dee2e6;
        }

        /* opsional: styling scrollbar tipis biar mirip referensi (khusus Chrome/Edge) */
        .ship-table-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .ship-table-scroll::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }

        .material-table-scroll {
            max-height: 450px;
            overflow-y: auto;
            overflow-x: auto;
            position: relative;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .material-table-scroll table {
            margin-bottom: 0;
        }

        .material-table-scroll thead.ship-thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            box-shadow: 0 1px 0 #dee2e6;
        }

        .material-table-scroll::-webkit-scrollbar {
            width: 8px;
        }
        .material-table-scroll::-webkit-scrollbar-thumb {
            background-color: #c1c1c1;
            border-radius: 4px;
        }
    </style>
@endsection

@section('js_custom')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        let filterTimer = null;
        let positionHeader = null;
        let lastScrollTop = 0;

        let shipmentPlanModalInstance = null;
        let materialsListModalInstance = null;
        let currentMaterialsListPopk = null;

        let confirmChangesModalInstance = null;
        let pendingShipmentPayload = null;

        let vassalOptions = [];
        let arrivalOptions = [];
        let portOptions = [];
        let shipOptionsPromise = null;

        let shipmentDatesModalInstance = null;
        let pendingShipmentSource = null; // 'full' | 'dates' — nentuin modal mana yg direfresh setelah save
        const shipmentDatesFlatpickrs = {};
        let bulkEditDatesModalInstance = null;
        const bulkEditDatesFlatpickrs = {};
        let bulkEditImportpks = [];

        // =========================
        // SHIPMENT PLAN DATE VALIDATION (Ship page, edit mode)
        // =========================
        const shipmentPlanFlatpickrs = {};

        function getShipmentPlanMinDate() {
            const min = new Date();
            min.setHours(0, 0, 0, 0);
            min.setDate(min.getDate() - 7);
            return min;
        }

        const shipmentPlanMinDate = getShipmentPlanMinDate();
        function parseDMYDate(text) {
            if (!text || text === '-') return null;
            const parts = text.split('/');
            if (parts.length !== 3) return null;
            const [d, m, y] = parts.map(Number);
            if (!d || !m || !y) return null;
            const date = new Date(y, m - 1, d);
            date.setHours(0, 0, 0, 0);
            return date;
        }

        function openShipmentDatesModal(importpk) {
            document.getElementById('sd_importpk').value = importpk;

            const modalEl = document.getElementById('shipmentDatesModal');
            if (!shipmentDatesModalInstance) {
                shipmentDatesModalInstance = new bootstrap.Modal(modalEl);
            }
            shipmentDatesModalInstance.show();

            fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        alert(res.message || 'Gagal mengambil data shipment');
                        return;
                    }

                    const data = res.data;
                    window.currentShipDates = data;

                    document.getElementById('sd_aih').textContent = data.aih || '-';
                    resetShipmentDatesErrors();

                    ['sd_atd_input', 'sd_ata_input'].forEach(id => {
                        if (shipmentDatesFlatpickrs[id]) {
                            shipmentDatesFlatpickrs[id].set('minDate', shipmentPlanMinDate);
                            shipmentDatesFlatpickrs[id].set('maxDate', null);
                        }
                    });

                    applySdAihDateConstraint(data);

                    relaxMinDateForExistingValue(shipmentDatesFlatpickrs, 'sd_etd_input', data.etd);
                    relaxMinDateForExistingValue(shipmentDatesFlatpickrs, 'sd_eta_input', data.eta);
                    relaxMinDateForExistingValue(shipmentDatesFlatpickrs, 'sd_eih_input', data.eih);
                    relaxMinDateForExistingValue(shipmentDatesFlatpickrs, 'sd_atd_input', data.atd);
                    relaxMinDateForExistingValue(shipmentDatesFlatpickrs, 'sd_ata_input', data.ata);

                    // document.getElementById('sd_etd_input')._flatpickr?.setDate(data.etd || '', true, 'd/m/Y');
                    // document.getElementById('sd_eta_input')._flatpickr?.setDate(data.eta || '', true, 'd/m/Y');
                    // document.getElementById('sd_eih_input')._flatpickr?.setDate(data.eih || '', true, 'd/m/Y');
                    // document.getElementById('sd_atd_input')._flatpickr?.setDate(data.atd || '', true, 'd/m/Y');
                    // document.getElementById('sd_ata_input')._flatpickr?.setDate(data.ata || '', true, 'd/m/Y');
                    document.getElementById('sd_etd_input')._flatpickr?.setDate(data.etd || '', false, 'd/m/Y');
                    document.getElementById('sd_eta_input')._flatpickr?.setDate(data.eta || '', false, 'd/m/Y');
                    document.getElementById('sd_eih_input')._flatpickr?.setDate(data.eih || '', false, 'd/m/Y');
                    document.getElementById('sd_atd_input')._flatpickr?.setDate(data.atd || '', false, 'd/m/Y');
                    document.getElementById('sd_ata_input')._flatpickr?.setDate(data.ata || '', false, 'd/m/Y');

                    // sinkronkan constraint kalender antar field berdasarkan data yang baru dimuat
                    if (getSdFieldDate('sd_etd_input')) {
                        syncShipmentDatesConstraints('sd_etd_input', getSdFieldDate('sd_etd_input'));
                    }
                    if (getSdFieldDate('sd_eta_input')) {
                        syncShipmentDatesConstraints('sd_eta_input', getSdFieldDate('sd_eta_input'));
                    }
                    if (getSdFieldDate('sd_atd_input')) {
                        syncShipmentDatesConstraints('sd_atd_input', getSdFieldDate('sd_atd_input'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat mengambil shipment plan');
                });
        }

        function applySdAihDateConstraint(data) {
            const aihDate = parseDMYDate(data.aih);
            if (!aihDate) return;

            if (shipmentDatesFlatpickrs.sd_atd_input) {
                shipmentDatesFlatpickrs.sd_atd_input.set('maxDate', aihDate);
                shipmentDatesFlatpickrs.sd_atd_input.set('minDate', null);
            }
            if (shipmentDatesFlatpickrs.sd_ata_input) {
                shipmentDatesFlatpickrs.sd_ata_input.set('maxDate', aihDate);
                shipmentDatesFlatpickrs.sd_ata_input.set('minDate', null);
            }
        }

        function resetShipmentDatesErrors() {
            ['sd_etd_input', 'sd_eta_input', 'sd_eih_input', 'sd_atd_input', 'sd_ata_input'].forEach(id => {
                const input = document.getElementById(id);
                const errorEl = document.getElementById(id.replace('_input', '') + '-error');
                if (input) input.classList.remove('field-error');
                if (errorEl) errorEl.style.display = 'none';
            });
        }

        function setSdFieldError(inputId, message) {
            const input = document.getElementById(inputId);
            const errorEl = document.getElementById(inputId.replace('_input', '') + '-error');
            if (input) input.classList.add('field-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }
        }

        function getSdFieldDate(id) {
            const fp = shipmentDatesFlatpickrs[id];
            return (fp && fp.selectedDates.length) ? fp.selectedDates[0] : null;
        }

        function validateShipmentDatesFields() {
            resetShipmentDatesErrors();

            const etdInput = document.getElementById('sd_etd_input');
            const etaInput = document.getElementById('sd_eta_input');
            const eihInput = document.getElementById('sd_eih_input');
            const atdInput = document.getElementById('sd_atd_input');
            const ataInput = document.getElementById('sd_ata_input');

            const etdDate = getSdFieldDate('sd_etd_input');
            const etaDate = getSdFieldDate('sd_eta_input');
            const eihDate = getSdFieldDate('sd_eih_input');
            const atdDate = getSdFieldDate('sd_atd_input');
            const ataDate = getSdFieldDate('sd_ata_input');

            let isValid = true;
            let firstErrorId = null;

            const markError = (inputId, message) => {
                setSdFieldError(inputId, message);
                isValid = false;
                if (!firstErrorId) firstErrorId = inputId;
            };

            const aihDate = parseDMYDate(window.currentShipDates?.aih);

            // if (etdInput.value && (!etdDate || etdDate < shipmentPlanMinDate)) {
            //     markError('sd_etd_input', 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (etaInput.value && (!etaDate || etaDate < shipmentPlanMinDate)) {
            //     markError('sd_eta_input', 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (eihInput.value && (!eihDate || eihDate < shipmentPlanMinDate)) {
            //     markError('sd_eih_input', 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (!aihDate) {
            //     if (atdInput.value && (!atdDate || atdDate < shipmentPlanMinDate)) {
            //         markError('sd_atd_input', 'ATD tidak boleh lebih lama dari 7 hari yang lalu');
            //     }
            //     if (ataInput.value && (!ataDate || ataDate < shipmentPlanMinDate)) {
            //         markError('sd_ata_input', 'ATA tidak boleh lebih lama dari 7 hari yang lalu');
            //     }
            // }

            const etdUnchanged = isUnchangedFromOriginal('sd_etd_input', window.currentShipDates?.etd);
            const etaUnchanged = isUnchangedFromOriginal('sd_eta_input', window.currentShipDates?.eta);
            const eihUnchanged = isUnchangedFromOriginal('sd_eih_input', window.currentShipDates?.eih);
            const atdUnchanged = isUnchangedFromOriginal('sd_atd_input', window.currentShipDates?.atd);
            const ataUnchanged = isUnchangedFromOriginal('sd_ata_input', window.currentShipDates?.ata);

            if (!etdUnchanged && etdInput.value && (!etdDate || etdDate < shipmentPlanMinDate)) {
                markError('sd_etd_input', 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!etaUnchanged && etaInput.value && (!etaDate || etaDate < shipmentPlanMinDate)) {
                markError('sd_eta_input', 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!eihUnchanged && eihInput.value && (!eihDate || eihDate < shipmentPlanMinDate)) {
                markError('sd_eih_input', 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!aihDate) {
                if (!atdUnchanged && atdInput.value && (!atdDate || atdDate < shipmentPlanMinDate)) {
                    markError('sd_atd_input', 'ATD tidak boleh lebih lama dari 7 hari yang lalu');
                }
                if (!ataUnchanged && ataInput.value && (!ataDate || ataDate < shipmentPlanMinDate)) {
                    markError('sd_ata_input', 'ATA tidak boleh lebih lama dari 7 hari yang lalu');
                }
            }

            if (etdDate && etaDate && etdDate > etaDate) {
                markError('sd_eta_input', 'ETA tidak boleh lebih kecil dari ETD');
            }
            if (etaDate && eihDate && etaDate > eihDate) {
                markError('sd_eih_input', 'EIH tidak boleh lebih kecil dari ETA');
            }
            if (!etaDate && etdDate && eihDate && etdDate > eihDate) {
                markError('sd_eih_input', 'EIH tidak boleh lebih kecil dari ETD');
            }
            if (atdDate && ataDate && atdDate > ataDate) {
                markError('sd_ata_input', 'ATA tidak boleh lebih kecil dari ATD');
            }
            if (aihDate) {
                if (atdDate && atdDate > aihDate) {
                    markError('sd_atd_input', 'ATD tidak boleh melebihi AIH');
                }
                if (ataDate && ataDate > aihDate) {
                    markError('sd_ata_input', 'ATA tidak boleh melebihi AIH');
                }
            }

            return { isValid, firstErrorId };
        }

        function getShipmentPlanErrorElId(inputId) {
            return inputId.replace('_input', '') + '-error';
        }

        function setShipmentPlanFieldError(inputId, message) {
            const input = document.getElementById(inputId);
            const errorEl = document.getElementById(getShipmentPlanErrorElId(inputId));
            if (input) input.classList.add('field-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }
        }

        function clearShipmentPlanFieldError(inputId) {
            const input = document.getElementById(inputId);
            const errorEl = document.getElementById(getShipmentPlanErrorElId(inputId));
            if (input) input.classList.remove('field-error');
            if (errorEl) errorEl.style.display = 'none';
        }

        function clearAllShipmentPlanDateErrors() {
            ['modal_etd_input', 'modal_eta_input', 'modal_eih_input', 'modal_atd_input', 'modal_ata_input']
                .forEach(clearShipmentPlanFieldError);
        }

        function getShipmentPlanFieldDate(id) {
            const fp = shipmentPlanFlatpickrs[id];
            return (fp && fp.selectedDates.length) ? fp.selectedDates[0] : null;
        }

        function getAihDate() {
            const el = document.getElementById('modal_aih');
            if (!el) return null;

            const text = el.textContent.trim();
            if (!text || text === '-') return null;

            const parts = text.split('/'); // format d/m/Y, sama seperti field lain
            if (parts.length !== 3) return null;

            const [d, m, y] = parts.map(Number);
            if (!d || !m || !y) return null;

            const date = new Date(y, m - 1, d);
            date.setHours(0, 0, 0, 0);
            return date;
        }

        // function syncShipmentPlanDateConstraints(changedId, date) {
        //     if (!date) return;

        //     if (changedId === 'modal_etd_input' && shipmentPlanFlatpickrs.modal_eta_input) {
        //         shipmentPlanFlatpickrs.modal_eta_input.set('minDate', date);
        //     }
        //     if (changedId === 'modal_eta_input') {
        //         if (shipmentPlanFlatpickrs.modal_etd_input) shipmentPlanFlatpickrs.modal_etd_input.set('maxDate', date);
        //         if (shipmentPlanFlatpickrs.modal_eih_input) shipmentPlanFlatpickrs.modal_eih_input.set('minDate', date);
        //     }
        //     if (changedId === 'modal_eih_input' && shipmentPlanFlatpickrs.modal_eta_input) {
        //         shipmentPlanFlatpickrs.modal_eta_input.set('maxDate', date);
        //     }
        // }
        function syncShipmentPlanDateConstraints(changedId, date) {
            if (!date) return;

            if (changedId === 'modal_etd_input' && shipmentPlanFlatpickrs.modal_eta_input) {
                shipmentPlanFlatpickrs.modal_eta_input.set('minDate', date);
            }
            if (changedId === 'modal_eta_input') {
                if (shipmentPlanFlatpickrs.modal_etd_input) shipmentPlanFlatpickrs.modal_etd_input.set('maxDate', date);
                if (shipmentPlanFlatpickrs.modal_eih_input) shipmentPlanFlatpickrs.modal_eih_input.set('minDate', date);
            }
            if (changedId === 'modal_eih_input' && shipmentPlanFlatpickrs.modal_eta_input) {
                shipmentPlanFlatpickrs.modal_eta_input.set('maxDate', date);
            }

            // ATD <-> ATA (fitur baru, belum ada sebelumnya)
            if (changedId === 'modal_atd_input' && shipmentPlanFlatpickrs.modal_ata_input) {
                shipmentPlanFlatpickrs.modal_ata_input.set('minDate', date);
            }
            if (changedId === 'modal_ata_input' && shipmentPlanFlatpickrs.modal_atd_input) {
                shipmentPlanFlatpickrs.modal_atd_input.set('maxDate', date);
            }
        }

        function resetShipmentPlanDateConstraints() {
            ['modal_etd_input', 'modal_eta_input', 'modal_eih_input', 'modal_atd_input', 'modal_ata_input'].forEach(id => {
                const fp = shipmentPlanFlatpickrs[id];
                if (fp) {
                    fp.set('minDate', shipmentPlanMinDate);
                    fp.set('maxDate', null);
                }
                clearShipmentPlanFieldError(id);
            });
        }

        function validateShipmentPlanDates() {
            clearAllShipmentPlanDateErrors();

            const etdInput = document.getElementById('modal_etd_input');
            const etaInput = document.getElementById('modal_eta_input');
            const eihInput = document.getElementById('modal_eih_input');
            const atdInput = document.getElementById('modal_atd_input');
            const ataInput = document.getElementById('modal_ata_input');

            const etdDate = getShipmentPlanFieldDate('modal_etd_input');
            const etaDate = getShipmentPlanFieldDate('modal_eta_input');
            const eihDate = getShipmentPlanFieldDate('modal_eih_input');
            const atdDate = getShipmentPlanFieldDate('modal_atd_input');
            const ataDate = getShipmentPlanFieldDate('modal_ata_input');

            let isValid = true;
            let firstErrorId = null;

            const markError = (inputId, message) => {
                setShipmentPlanFieldError(inputId, message);
                isValid = false;
                if (!firstErrorId) firstErrorId = inputId;
            };

            // if (etdInput.value && (!etdDate || etdDate < shipmentPlanMinDate)) {
            //     markError('modal_etd_input', 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (etaInput.value && (!etaDate || etaDate < shipmentPlanMinDate)) {
            //     markError('modal_eta_input', 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (eihInput.value && (!eihDate || eihDate < shipmentPlanMinDate)) {
            //     markError('modal_eih_input', 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (atdInput.value && (!atdDate || atdDate < shipmentPlanMinDate)) {
            //     markError('modal_atd_input', 'ATD tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (ataInput.value && (!ataDate || ataDate < shipmentPlanMinDate)) {
            //     markError('modal_ata_input', 'ATA tidak boleh lebih lama dari 7 hari yang lalu');
            // }

            const aihDateForMinCheck = getAihDate();

            // if (etdInput.value && (!etdDate || etdDate < shipmentPlanMinDate)) {
            //     markError('modal_etd_input', 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (etaInput.value && (!etaDate || etaDate < shipmentPlanMinDate)) {
            //     markError('modal_eta_input', 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // if (eihInput.value && (!eihDate || eihDate < shipmentPlanMinDate)) {
            //     markError('modal_eih_input', 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
            // }
            // // Rule "7 hari lalu" untuk ATD/ATA di-skip kalau AIH sudah terisi,
            // // supaya user bisa backfill tanggal historis (AIH bisa jauh di masa lalu).
            // if (!aihDateForMinCheck) {
            //     if (atdInput.value && (!atdDate || atdDate < shipmentPlanMinDate)) {
            //         markError('modal_atd_input', 'ATD tidak boleh lebih lama dari 7 hari yang lalu');
            //     }
            //     if (ataInput.value && (!ataDate || ataDate < shipmentPlanMinDate)) {
            //         markError('modal_ata_input', 'ATA tidak boleh lebih lama dari 7 hari yang lalu');
            //     }
            // }

            // skip aturan "7 hari lalu" kalau nilainya sama persis dengan data original (tidak disentuh user)
            const etdUnchanged = isUnchangedFromOriginal('modal_etd_input', window.currentShip?.etd);
            const etaUnchanged = isUnchangedFromOriginal('modal_eta_input', window.currentShip?.eta);
            const eihUnchanged = isUnchangedFromOriginal('modal_eih_input', window.currentShip?.eih);
            const atdUnchanged = isUnchangedFromOriginal('modal_atd_input', window.currentShip?.atd);
            const ataUnchanged = isUnchangedFromOriginal('modal_ata_input', window.currentShip?.ata);

            if (!etdUnchanged && etdInput.value && (!etdDate || etdDate < shipmentPlanMinDate)) {
                markError('modal_etd_input', 'ETD tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!etaUnchanged && etaInput.value && (!etaDate || etaDate < shipmentPlanMinDate)) {
                markError('modal_eta_input', 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!eihUnchanged && eihInput.value && (!eihDate || eihDate < shipmentPlanMinDate)) {
                markError('modal_eih_input', 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
            }
            // Rule "7 hari lalu" untuk ATD/ATA di-skip kalau AIH sudah terisi ATAU nilainya belum diubah,
            // supaya user bisa backfill tanggal historis (AIH bisa jauh di masa lalu).
            if (!aihDateForMinCheck) {
                if (!atdUnchanged && atdInput.value && (!atdDate || atdDate < shipmentPlanMinDate)) {
                    markError('modal_atd_input', 'ATD tidak boleh lebih lama dari 7 hari yang lalu');
                }
                if (!ataUnchanged && ataInput.value && (!ataDate || ataDate < shipmentPlanMinDate)) {
                    markError('modal_ata_input', 'ATA tidak boleh lebih lama dari 7 hari yang lalu');
                }
            }

            if (etdDate && etaDate && etdDate > etaDate) {
                markError('modal_eta_input', 'ETA tidak boleh lebih kecil dari ETD');
            }
            if (etaDate && eihDate && etaDate > eihDate) {
                markError('modal_eih_input', 'EIH tidak boleh lebih kecil dari ETA');
            }
            if (!etaDate && etdDate && eihDate && etdDate > eihDate) {
                markError('modal_eih_input', 'EIH tidak boleh lebih kecil dari ETD');
            }

            // ATA tidak boleh lebih kecil dari ATD (tidak terikat ke ETD/ETA/EIH)
            if (atdDate && ataDate && atdDate > ataDate) {
                markError('modal_ata_input', 'ATA tidak boleh lebih kecil dari ATD');
            }

            // ATD & ATA tidak boleh melebihi AIH (kalau AIH sudah terisi dari WMS)
            const aihDate = getAihDate();
            if (aihDate) {
                if (atdDate && atdDate > aihDate) {
                    markError('modal_atd_input', 'ATD tidak boleh melebihi AIH');
                }
                if (ataDate && ataDate > aihDate) {
                    markError('modal_ata_input', 'ATA tidak boleh melebihi AIH');
                }
            }


            return { isValid, firstErrorId };
        }

        function scrollToShipmentPlanError(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.scrollIntoView({ behavior: 'smooth', block: 'center' });
            input.focus({ preventScroll: true });
        }

        function applyAihDateConstraint() {
            const aihDate = getAihDate();
            if (!aihDate) return;

            if (shipmentPlanFlatpickrs.modal_atd_input) {
                shipmentPlanFlatpickrs.modal_atd_input.set('maxDate', aihDate);
                shipmentPlanFlatpickrs.modal_atd_input.set('minDate', null); // longgarkan batas 7 hari
            }
            if (shipmentPlanFlatpickrs.modal_ata_input) {
                shipmentPlanFlatpickrs.modal_ata_input.set('maxDate', aihDate);
                shipmentPlanFlatpickrs.modal_ata_input.set('minDate', null); // longgarkan batas 7 hari
            }
        }

        function relaxMinDateForExistingValue(flatpickrMap, id, existingValueDMY) {
            const fp = flatpickrMap[id];
            if (!fp) return;

            const existingDate = parseDMYDate(existingValueDMY);
            if (existingDate && existingDate < shipmentPlanMinDate) {
                fp.set('minDate', existingDate);
            }
        }

        function isUnchangedFromOriginal(inputId, originalValueDMY) {
            const input = document.getElementById(inputId);
            if (!input) return false;
            const current = (input.value || '').trim();
            const original = (originalValueDMY || '').trim();
            return current === original;
        }

        function syncShipmentDatesConstraints(changedId, date) {
            if (!date) return;

            if (changedId === 'sd_etd_input' && shipmentDatesFlatpickrs.sd_eta_input) {
                shipmentDatesFlatpickrs.sd_eta_input.set('minDate', date);
            }
            if (changedId === 'sd_eta_input') {
                if (shipmentDatesFlatpickrs.sd_etd_input) shipmentDatesFlatpickrs.sd_etd_input.set('maxDate', date);
                if (shipmentDatesFlatpickrs.sd_eih_input) shipmentDatesFlatpickrs.sd_eih_input.set('minDate', date);
            }
            if (changedId === 'sd_eih_input' && shipmentDatesFlatpickrs.sd_eta_input) {
                shipmentDatesFlatpickrs.sd_eta_input.set('maxDate', date);
            }

            // ATD <-> ATA (fitur baru, belum ada sebelumnya)
            if (changedId === 'sd_atd_input' && shipmentDatesFlatpickrs.sd_ata_input) {
                shipmentDatesFlatpickrs.sd_ata_input.set('minDate', date);
            }
            if (changedId === 'sd_ata_input' && shipmentDatesFlatpickrs.sd_atd_input) {
                shipmentDatesFlatpickrs.sd_atd_input.set('maxDate', date);
            }
        }

        // =========================
        // FILTER FORM (AJAX — no page reload)
        // =========================
        let shipFilterAbortController = null;

        function collectShipFilterParams() {
            const form = document.getElementById('filterForm');
            return new URLSearchParams(new FormData(form));
        }

        function fetchShips(params, pushHistory = true) {
            if (shipFilterAbortController) shipFilterAbortController.abort();
            shipFilterAbortController = new AbortController();

            const query = params.toString();
            const dataUrl = `{{ route('exim.import.ship.data') }}${query ? '?' + query : ''}`;
            const pageUrl = `{{ route('exim.import.ship') }}${query ? '?' + query : ''}`;

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
                    signal: shipFilterAbortController.signal
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
                            eximShipFilters: true
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

        function submitFilter() {
            fetchShips(collectShipFilterParams());
        }

        function submitFilterDelay() {
            clearTimeout(filterTimer);
            filterTimer = setTimeout(() => {
                submitFilter();
            }, 500);
        }

        window.addEventListener('popstate', function() {
            fetchShips(new URLSearchParams(window.location.search), false);
        });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('.filter-chip-link, .clear-all-link, #reloadShipTableBtn');
            if (!link) return;

            e.preventDefault();
            const url = new URL(link.href, window.location.origin);
            fetchShips(url.searchParams);
        });

        function toggleSupplier() {
            const chek = document.getElementById('chek');
            const btn = document.getElementById('bysup');

            if (!chek) return;

            chek.value = chek.value === '1' ? '0' : '1';

            if (btn) {
                if (chek.value === '1') {
                    btn.style.backgroundColor = '#359DD9';
                    btn.style.color = '#FFFFFF';
                } else {
                    btn.style.backgroundColor = '#FFFFFF';
                    btn.style.color = '#000000';
                }
            }

            submitFilter();
        }

        function goToShipPage(page) {
            if (page < 1) return;
            document.getElementById('page').value = page;
            submitFilter();
        }

        function onShipPageSizeChange() {
            const size = document.getElementById('shipPageSize').value;
            document.getElementById('rows').value = size;
            document.getElementById('page').value = 1;
            submitFilter();
        }

        // =========================
        // SORT SHIP
        // =========================
        function resetShipSortInput() {
            document.getElementById('cari5').value = '';
            document.getElementById('cari6').value = '';
            document.getElementById('cari7').value = '';
            document.getElementById('cari8').value = '';
            document.getElementById('cari9').value = '';
        }


        function setShipSort(type) {
            resetShipSortInput();

            if (type === 'newest') {
                document.getElementById('cari5').value = '1';
            } else if (type === 'oldest') {
                document.getElementById('cari6').value = '1';
            } else if (type === 'recent') {
                document.getElementById('cari7').value = '1';
            } else if (type === 'etdAsc') {
                document.getElementById('cari8').value = '1';
            } else if (type === 'etdDsc') {
                document.getElementById('cari9').value = '1';
            }

            // used to rely on a full reload to re-render this label from Blade
            const sortLabels = {
                newest: 'Newest entry',
                oldest: 'Oldest entry',
                recent: 'Recently modify',
                etdAsc: 'Shipment ETD (Awal)',
                etdDsc: 'Shipment ETD (Akhir)',
            };
            const trigger = document.querySelector('#shipSortTrigger span');
            if (trigger) trigger.textContent = sortLabels[type] || 'Newest entry';

            document.getElementById('page').value = 1;
            closeShipSortDropdown();
            submitFilter();
        }

        // =========================
        // SORT SHIP - WRAPPER
        // =========================
        function Searcha() {
            setShipSort('newest');
        }

        function Searchb() {
            setShipSort('oldest');
        }

        function Searchd() {
            setShipSort('recent');
        }

        function Searche() {
            setShipSort('etdAsc');
        }

        function Searchf() {
            setShipSort('etdDsc');
        }
        // function setShipSort(type) {
        //     resetShipSortInput();

        //     if (type === 'newest') {
        //         document.getElementById('cari5').value = '1';
        //     } else if (type === 'oldest') {
        //         document.getElementById('cari6').value = '1';
        //     } else if (type === 'recent') {
        //         document.getElementById('cari7').value = '1';
        //     } else if (type === 'etd') {
        //         document.getElementById('cari8').value = '1';
        //     }

        //     closeShipSortDropdown();
        //     submitFilter();
        // }

        function toggleShipSortDropdown(event) {
            if (event) event.stopPropagation();

            const dropdown = document.getElementById('shipSortDropdown');
            if (!dropdown) return;

            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        function closeShipSortDropdown() {
            const dropdown = document.getElementById('shipSortDropdown');
            if (dropdown) dropdown.style.display = 'none';
        }
        

        // =========================
        // ACTION BAR CHECKBOX SHIP
        // =========================
        // function toggleActionBar() {
        //     const checked = document.querySelectorAll('.ship-check:checked').length;
        //     const total = document.querySelectorAll('.ship-check').length;
        //     const header = document.getElementById('myHeader');
        //     const countp = document.getElementById('countp');
        //     const approve = document.getElementById('approve');
        //     const checkAll = document.getElementById('check-all');

        //     if (checked > 0) {
        //         if (header) header.style.display = 'flex';
        //         if (countp) countp.innerText = checked + ' items selected';
        //         if (typeof positionHeader === 'function') positionHeader(lastScrollTop);
        //     } else {
        //         if (header) header.style.display = 'none';
        //         if (countp) countp.innerText = '0 items selected';
        //     }

        //     if (checkAll) {
        //         checkAll.checked = (checked === total && total > 0);
        //     }

        //     if (approve) {
        //         approve.style.display = checked > 0 ? 'block' : 'none';
        //     }

        //     // ==== highlight visual baris yang dicentang ====
        //     document.querySelectorAll('.ship-row').forEach(row => {
        //         const cb = row.querySelector('.ship-check');
        //         row.style.backgroundColor = (cb && cb.checked) ? '#EAF4FC' : '';
        //     });
        // }
        function toggleActionBar() {
            const checked = document.querySelectorAll('.ship-check:checked').length;
            const total = document.querySelectorAll('.ship-check').length;
            const header = document.getElementById('myHeader');
            const countp = document.getElementById('countp');
            const approve = document.getElementById('approve');
            const checkAll = document.getElementById('check-all');

            if (checked > 0) {
                if (header) header.style.display = 'flex';
                if (countp) countp.innerText = checked + ' items selected';
                if (typeof positionHeader === 'function') positionHeader(lastScrollTop);
            } else {
                if (header) header.style.display = 'none';
                if (countp) countp.innerText = '0 items selected';
            }

            if (checkAll) {
                checkAll.checked = (checked === total && total > 0);
            }

            if (approve) {
                approve.style.display = checked > 0 ? 'block' : 'none';
            }

            // ==== highlight visual baris yang dicentang ====
            document.querySelectorAll('.ship-row').forEach(row => {
                const cb = row.querySelector('.ship-check');
                row.style.backgroundColor = (cb && cb.checked) ? '#EAF4FC' : '';
            });

            // ==== show/hide tombol Edit dates ====
            const editBtn = document.getElementById('editDatesBtn');
            if (editBtn) {
                const rows = getCheckedShipRows();
                const eligibility = evaluateBulkEditEligibility(rows);
                editBtn.style.display = eligibility.eligible ? '' : 'none';
            }
        }

        function toggleUpdateStatusMenu() {
            document.getElementById('updateStatusMenu').classList.toggle('show');
        }

        // Tutup dropdown kalau klik di luar
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('updateStatusMenu');
            const trigger = document.querySelector('.update-status-trigger');
            if (menu && !menu.contains(e.target) && e.target !== trigger) {
                menu.classList.remove('show');
            }
        });

        function getCheckedShipIds() {
            return Array.from(document.querySelectorAll('.ship-check:checked'))
                .map(checkbox => checkbox.value);
        }

        function bulkChangeStatus(status) {
            const ids = getCheckedShipIds();

            if (ids.length === 0) {
                alert('No shipment selected');
                return;
            }

            document.getElementById('updateStatusMenu').classList.remove('show');

            const requests = ids.map(importpk =>
                fetch(`{{ url('/ship') }}/${importpk}/change-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: status
                    })
                }).then(response => response.json())
            );

            Promise.all(requests)
                .then(results => {
                    const failed = results.filter(r => !r.success);

                    document.querySelectorAll('.ship-check').forEach(cb => cb.checked = false);
                    document.getElementById('check-all').checked = false;
                    document.getElementById('myHeader').style.display = 'none';

                    if (failed.length > 0) {
                        showToastShip(`${failed.length} item(s) failed to update`, 'error');
                    } else {
                        showToastShip(`${ids.length} item successful updated`);
                    }

                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    console.error(error);
                    alert('Failed to update shipment status');
                });
        }

        function bulkCancelShipment() {
            const ids = getCheckedShipIds();

            if (ids.length === 0) {
                showToastShip('No shipment selected', 'error');
                return;
            }

            document.getElementById('updateStatusMenu').classList.remove('show');
            openCancelShipmentModal(ids);
        }

        // =========================
        // SHIP OPTIONS (vassal/arrival/port dropdown) - promise-based, anti race condition
        // =========================
        function loadShipOptions() {
            if (!shipOptionsPromise) {
                shipOptionsPromise = fetch(`{{ route('exim.import.ship.options') }}`)
                    .then(res => res.json())
                    .then(data => {
                        vassalOptions = data.vassal || [];
                        arrivalOptions = data.arrival || [];
                        portOptions = data.port || [];
                    });
            }
            return shipOptionsPromise;
        }

        function populateCustomDropdown(menuId, labelId, hiddenInputId, options, valueField, textField, selectedValue) {
            const hasSelection = selectedValue !== null && selectedValue !== undefined && selectedValue !== '';
            const menu = document.getElementById(menuId);
            const label = document.getElementById(labelId);
            const hiddenInput = document.getElementById(hiddenInputId);

            let html =
                `<div class="custom-dropdown-item ${!hasSelection ? 'selected' : ''}" onclick="selectModalDropdownOption('${hiddenInputId}','${labelId}','${menuId}','','-', this)">-</div>`;

            html += options.map(opt => {
                const isSelected = hasSelection && opt[valueField] == selectedValue;
                return `<div class="custom-dropdown-item ${isSelected ? 'selected' : ''}" onclick="selectModalDropdownOption('${hiddenInputId}','${labelId}','${menuId}','${opt[valueField]}','${escapeHtmlModal(opt[textField])}', this)">${escapeHtmlModal(opt[textField])}</div>`;
            }).join('');

            menu.innerHTML = html;

            if (hasSelection) {
                const found = options.find(o => o[valueField] == selectedValue);
                hiddenInput.value = selectedValue;
                label.textContent = found ? found[textField] : '-';
            } else {
                hiddenInput.value = '';
                label.textContent = '-';
            }
        }

        function updatePortOptions(arrivalpk, selectedPortpk) {
            const filtered = portOptions.filter(p => p.arrivalpk == arrivalpk);
            populateCustomDropdown('modal_port_name_menu', 'modal_port_name_label', 'modal_port_name_input',
                filtered, 'portpk', 'portnm', selectedPortpk);
        }

        function toggleModalDropdown(event, menuId) {
            event.stopPropagation();

            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                if (menu.id !== menuId) menu.style.display = 'none';
            });

            const menu = document.getElementById(menuId);
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function selectModalDropdownOption(hiddenInputId, labelId, menuId, value, label, el) {
            document.getElementById(hiddenInputId).value = value;
            document.getElementById(labelId).textContent = label;
            document.getElementById(menuId).style.display = 'none';

            el.closest('.custom-dropdown-menu').querySelectorAll('.custom-dropdown-item').forEach(item => {
                item.classList.remove('selected');
            });
            el.classList.add('selected');

            // cascading: kalau yang dipilih Arrival city, refresh Port options
            if (hiddenInputId === 'modal_arrival_city_input') {
                updatePortOptions(value, null);
            }
        }

        function escapeHtmlModal(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        // =========================
        // SHOW/HIDE MODE HELPERS
        // =========================
        function showEditMode() {
            document.querySelectorAll('#shipmentPlanModal .edit-mode').forEach(el => {
                el.style.display = el.dataset.display || 'block';
            });
            document.querySelectorAll('#shipmentPlanModal .view-mode').forEach(el => el.style.display = 'none');

            document.getElementById('shipmentPlanCloseBtn').style.display = 'none';
            document.getElementById('shipmentPlanCancelBtn').style.display = '';
            // document.getElementById('shipmentPlanEditBtn').style.display = 'none';
            // Pembatasan validasi user di action view
            const editBtn = document.getElementById('shipmentPlanEditBtn');
            if (editBtn) {
                editBtn.style.display = 'none';
            }
            document.getElementById('shipmentPlanSaveBtn').style.display = '';
            document.getElementById('dgImportActHead').style.display = '';
        }

        function showViewMode() {
            document.querySelectorAll('#shipmentPlanModal .edit-mode').forEach(el => el.style.display = 'none');
            document.querySelectorAll('#shipmentPlanModal .view-mode').forEach(el => el.style.display = '');

            document.getElementById('shipmentPlanCloseBtn').style.display = '';
            document.getElementById('shipmentPlanCancelBtn').style.display = 'none';
            // document.getElementById('shipmentPlanEditBtn').style.display = '';
            // Pembatasan validasi user di action view
            const editBtn = document.getElementById('shipmentPlanEditBtn');
            if (editBtn) {
                editBtn.style.display = '';
            }
            document.getElementById('shipmentPlanSaveBtn').style.display = 'none';
            document.getElementById('dgImportActHead').style.display = 'none';
        }

        // =========================
        // SHIPMENT PLAN MODAL: open, reset, render
        // =========================
        function openShipmentPlan(importpk) {
            document.getElementById('modal_importpk').value = importpk;

            resetShipmentPlanModal();

            const modalEl = document.getElementById('shipmentPlanModal');
            if (!shipmentPlanModalInstance) {
                shipmentPlanModalInstance = new bootstrap.Modal(modalEl);
            }
            shipmentPlanModalInstance.show();

            fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        alert(res.message || 'Gagal mengambil data shipment');
                        return;
                    }

                    const data = res.data;
                    window.currentShip = data;

                    document.getElementById('modal_supplier').textContent = data.supplier || '-';
                    document.getElementById('countpono').textContent = data.po_count || 0;

                    document.getElementById('modal_vassal_type').textContent = data.vassal_type || '-';
                    document.getElementById('modal_vassal_name').textContent = data.vassal_name || '-';
                    document.getElementById('modal_awb').textContent = data.awb || '-';
                    document.getElementById('modal_arrival_city').textContent = data.arrival_city || '-';
                    document.getElementById('modal_port_name').textContent = data.port_name || '-';

                    document.getElementById('modal_gw').textContent = data.gw ?? '-';
                    document.getElementById('modal_dimension').textContent = data.dimension || '-';
                    document.getElementById('modal_tpackage').textContent = data.total_package ?? '-';

                    document.getElementById('modal_etd').textContent = data.etd || '-';
                    document.getElementById('modal_eta').textContent = data.eta || '-';
                    document.getElementById('modal_eih').textContent = data.eih || '-';
                    document.getElementById('modal_atd').textContent = data.atd || '-';
                    document.getElementById('modal_ata').textContent = data.ata || '-';
                    document.getElementById('modal_aih').textContent = data.aih || '-';

                    renderShipmentPoDetails(data.po_details || [], false);
                    renderShipmentLogs(data.logs || []);
                })
                .catch(err => {
                    console.error(err);
                    alert('Terjadi kesalahan saat mengambil shipment plan');
                });
        }

        function resetShipmentPlanModal() {
            showViewMode();
            clearAllShipmentPlanDateErrors();

            document.getElementById('modal_supplier').textContent = '-';
            document.getElementById('countpono').textContent = '0';

            document.getElementById('modal_vassal_type').textContent = '-';
            document.getElementById('modal_vassal_name').textContent = '-';
            document.getElementById('modal_awb').textContent = '-';
            document.getElementById('modal_arrival_city').textContent = '-';
            document.getElementById('modal_port_name').textContent = '-';

            document.getElementById('modal_gw').textContent = '-';
            document.getElementById('modal_dimension').textContent = '-';
            document.getElementById('modal_tpackage').textContent = '-';

            document.getElementById('modal_etd').textContent = '-';
            document.getElementById('modal_eta').textContent = '-';
            document.getElementById('modal_eih').textContent = '-';
            document.getElementById('modal_atd').textContent = '-';
            document.getElementById('modal_ata').textContent = '-';
            document.getElementById('modal_aih').textContent = '-';

            document.getElementById('dgImportBody').innerHTML = `
            <tr>
                <td colspan="7" style="color:#999;">Belum ada detail PO.</td>
            </tr>
        `;

            document.getElementById('shipment-log-body').innerHTML = `
            <tr>
                <td colspan="2" style="color:#999;">Belum ada log.</td>
            </tr>
        `;

            document.getElementById('tabel2b').style.display = 'none';
            document.getElementById('shw').style.display = 'inline';
            document.getElementById('hdn').style.display = 'none';
        }

        function renderShipmentLogs(logs) {
            const tbody = document.getElementById('shipment-log-body');

            if (!logs.length) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="2" style="color:#999;">Belum ada log.</td>
                </tr>
            `;
                return;
            }

            let html = '';
            logs.forEach(log => {
                html += `
                <tr>
                    <td>${log.date ?? '-'}</td>
                    <td>${log.changes ?? '-'}</td>
                </tr>
            `;
            });

            tbody.innerHTML = html;
        }

        function viewdtl() {
            const tabel = document.getElementById('tabel2b');
            const shw = document.getElementById('shw');
            const hdn = document.getElementById('hdn');

            if (tabel.style.display === 'none' || tabel.style.display === '') {
                tabel.style.display = 'block';
                shw.style.display = 'none';
                hdn.style.display = 'inline';
            } else {
                tabel.style.display = 'none';
                shw.style.display = 'inline';
                hdn.style.display = 'none';
            }
        }

        // =========================
        // TOGGLE EDIT MODE
        // =========================
        async function enterEditMode() {
            await loadShipOptions();

            showEditMode();
            populateCustomDropdown('modal_vassal_type_menu', 'modal_vassal_type_label', 'modal_vassal_type_input',
                vassalOptions, 'vassalpk', 'typenm', window.currentShip?.vassalpk);

            populateCustomDropdown('modal_arrival_city_menu', 'modal_arrival_city_label', 'modal_arrival_city_input',
                arrivalOptions, 'arrivalpk', 'city', window.currentShip?.arrivalpk);

            updatePortOptions(window.currentShip?.arrivalpk, window.currentShip?.portpk);

            document.getElementById('modal_vassal_name_input').value = window.currentShip?.vassal_name || '';
            document.getElementById('modal_awb_input').value = window.currentShip?.awb_raw || '';
            document.getElementById('modal_gw_input').value = formatDecimal(window.currentShip?.gw_raw);
            document.getElementById('modal_dp_input').value = formatDecimal(window.currentShip?.dp_raw);
            document.getElementById('modal_dl_input').value = formatDecimal(window.currentShip?.dl_raw);
            document.getElementById('modal_dt_input').value = formatDecimal(window.currentShip?.dt_raw);
            document.getElementById('modal_tpackage_input').value = formatDecimal(window.currentShip
                ?.total_package_raw);

            resetShipmentPlanDateConstraints();
            applyAihDateConstraint();

            relaxMinDateForExistingValue(shipmentPlanFlatpickrs, 'modal_etd_input', window.currentShip?.etd);
            relaxMinDateForExistingValue(shipmentPlanFlatpickrs, 'modal_eta_input', window.currentShip?.eta);
            relaxMinDateForExistingValue(shipmentPlanFlatpickrs, 'modal_eih_input', window.currentShip?.eih);
            relaxMinDateForExistingValue(shipmentPlanFlatpickrs, 'modal_atd_input', window.currentShip?.atd);
            relaxMinDateForExistingValue(shipmentPlanFlatpickrs, 'modal_ata_input', window.currentShip?.ata);

            // document.getElementById('modal_etd_input')._flatpickr?.setDate(window.currentShip?.etd || '', true,
            //     'd/m/Y');
            // document.getElementById('modal_eta_input')._flatpickr?.setDate(window.currentShip?.eta || '', true,
            //     'd/m/Y');
            // document.getElementById('modal_eih_input')._flatpickr?.setDate(window.currentShip?.eih || '', true,
            //     'd/m/Y');
            // document.getElementById('modal_atd_input')._flatpickr?.setDate(window.currentShip?.atd || '', true,
            //     'd/m/Y');
            // document.getElementById('modal_ata_input')._flatpickr?.setDate(window.currentShip?.ata || '', true,
            //     'd/m/Y');
            document.getElementById('modal_etd_input')._flatpickr?.setDate(window.currentShip?.etd || '', false, 'd/m/Y');
            document.getElementById('modal_eta_input')._flatpickr?.setDate(window.currentShip?.eta || '', false, 'd/m/Y');
            document.getElementById('modal_eih_input')._flatpickr?.setDate(window.currentShip?.eih || '', false, 'd/m/Y');
            document.getElementById('modal_atd_input')._flatpickr?.setDate(window.currentShip?.atd || '', false, 'd/m/Y');
            document.getElementById('modal_ata_input')._flatpickr?.setDate(window.currentShip?.ata || '', false, 'd/m/Y');

            
            if (getShipmentPlanFieldDate('modal_etd_input')) {
                syncShipmentPlanDateConstraints('modal_etd_input', getShipmentPlanFieldDate('modal_etd_input'));
            }
            if (getShipmentPlanFieldDate('modal_eta_input')) {
                syncShipmentPlanDateConstraints('modal_eta_input', getShipmentPlanFieldDate('modal_eta_input'));
            }
            if (getShipmentPlanFieldDate('modal_atd_input')) {
                syncShipmentPlanDateConstraints('modal_atd_input', getShipmentPlanFieldDate('modal_atd_input'));
            }

            document.getElementById('tabel2b').style.display = 'block';
            document.getElementById('shw').style.display = 'none';
            document.getElementById('hdn').style.display = 'none';

            renderShipmentPoDetails(window.currentShip?.po_details || [], true);
        }

        function formatDecimal(value) {
            const num = parseFloat(value);
            return isNaN(num) ? '0.00' : num.toFixed(2);
        }

        function exitEditMode() {
            showViewMode();
            renderShipmentPoDetails(window.currentShip?.po_details || [], false);

            document.getElementById('tabel2b').style.display = 'none';
            document.getElementById('shw').style.display = 'inline';
            document.getElementById('hdn').style.display = 'none';
        }

        // =========================
        // RENDER PO DETAILS (dengan mode edit)
        // =========================
        function renderShipmentPoDetails(details, editMode) {
            const tbody = document.getElementById('dgImportBody');

            if (!details.length) {
                tbody.innerHTML = `<tr><td colspan="8" style="color:#999;">Belum ada detail PO.</td></tr>`;
                return;
            }

            let grouped = {};
            details.forEach(row => {
                const pono = row.pono || '-';
                if (!grouped[pono]) grouped[pono] = {
                    popk: row.popk,
                    items: []
                };
                grouped[pono].items.push(row);
            });

            let html = '';

            Object.keys(grouped).forEach(pono => {
                const group = grouped[pono];

                html += `
                <tr style="background:#f8f9fa;">
                    <td colspan="${editMode ? 8 : 7}" style="font-weight:700;">
                        ${pono}
                        ${editMode ? `<button type="button" class="btn btn-sm" style="background:#359DD9; color:#fff; margin-left:8px;" onclick="openMaterialsListModal('${group.popk}', '${pono}')">+ Materials</button>` : ''}
                    </td>
                </tr>
            `;

                group.items.forEach(row => {
                    html += `
                    <tr>
                        <td>${row.item ?? '-'}</td>
                        <td>${row.description ?? '-'}</td>
                        <td>${row.supplier ?? '-'}</td>
                        <td>${row.color ?? '-'}</td>
                        <td>${row.price ?? '-'}</td>
                        <td>${row.qty_po ?? '-'}</td>
                        <td>
                            ${editMode
                                ? `<input type="number" class="form-control form-control-sm" value="${row.shipment_qty_raw ?? 0}" onchange="updateShipmentQty('${row.importdtpk}', this.value)">`
                                : (row.shipment_qty ?? '-')
                            }
                        </td>
                        ${editMode ? `<td style="text-align:center;"><a href="javascript:void(0)" style="color:red; font-weight:700;" onclick="deleteShipmentMaterial('${row.importdtpk}')">✕</a></td>` : ''}
                    </tr>
                `;
                });
            });

            tbody.innerHTML = html;
        }

        function updateShipmentQty(importdtpk, qtys) {
            fetch(`{{ route('exim.import.ship.updateQty', ':id') }}`.replace(':id', importdtpk), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qtys: qtys,
                    _token: '{{ csrf_token() }}'
                }),
            }).catch(err => console.error(err));
        }

        let pendingDeleteImportdtpk = null;

        function deleteShipmentMaterial(importdtpk) {
            pendingDeleteImportdtpk = importdtpk;
            document.getElementById('dialog-delete-material-backdrop').style.display = 'block';
        }

        function closeDeleteMaterialConfirm() {
            pendingDeleteImportdtpk = null;
            document.getElementById('dialog-delete-material-backdrop').style.display = 'none';
        }

        function confirmDeleteMaterial() {
            const importdtpk = pendingDeleteImportdtpk;

            if (!importdtpk) {
                closeDeleteMaterialConfirm();
                return;
            }

            fetch(`{{ route('exim.import.ship.deleteMaterial', ':id') }}`.replace(':id', importdtpk), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                })
                .then(res => res.json())
                .then(res => {
                    closeDeleteMaterialConfirm();

                    if (!res.success) {
                        showToastShip(res.message || 'Failed to delete material', 'error');
                        return;
                    }

                    showToastShip(res.message);

                    const importpk = document.getElementById('modal_importpk').value;

                    fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
                        .then(r => r.json())
                        .then(r => {
                            if (r.success) {
                                window.currentShip = r.data;
                                renderShipmentPoDetails(r.data.po_details || [], true);
                            }
                        });
                })
                .catch(err => {
                    console.error(err);
                    closeDeleteMaterialConfirm();
                    showToastShip('Failed to delete material', 'error');
                });
        }

        // =========================
        // MODAL: LIST OF MATERIAL FOR SHIPMENT PLANNING PO...
        // =========================
        function openMaterialsListModal(popk, pono) {
            currentMaterialsListPopk = popk;
            document.getElementById('materialsListPono').textContent = pono;

            const modalEl = document.getElementById('materialsListModal');
            if (!materialsListModalInstance) {
                materialsListModalInstance = new bootstrap.Modal(modalEl);
            }
            materialsListModalInstance.show();

            fetch(`{{ route('exim.import.ship.materialsForPo', ':popk') }}`.replace(':popk', popk))
                .then(res => res.json())
                .then(res => {
                    if (!res.success) {
                        alert('Gagal mengambil data material');
                        return;
                    }
                    renderMaterialsListTable(res.data);
                })
                .catch(err => console.error(err));
        }

        // function renderMaterialsListTable(rows) {
        //     const tbody = document.getElementById('materialsListBody');

        //     if (!rows.length) {
        //         tbody.innerHTML = `<tr><td colspan="9" style="color:#999;">Tidak ada material.</td></tr>`;
        //         return;
        //     }

        //     tbody.innerHTML = rows.map(row => `
        //     <tr class="materials-row" onclick="handleMaterialsRowClick(event, this)" style="cursor:pointer;">
        //         <td style="text-align:center;"><input type="checkbox" class="materials-check" value="${row.podtpk}"></td>
        //         <td>${row.item}</td>
        //         <td>${row.description}</td>
        //         <td>${row.supplier}</td>
        //         <td>${row.color}</td>
        //         <td>${row.price}</td>
        //         <td>${row.qty_po}</td>
        //         <td>${row.qty_shipment}</td>
        //         <td style="${row.balance_raw < 0 ? 'color:red;' : ''}">${row.balance}</td>
        //     </tr>
        //         `).join('');
        // }
        function renderMaterialsListTable(rows) {
            const tbody = document.getElementById('materialsListBody');
            const wrapper = document.getElementById('materialsListTableWrapper'); // bungkus .table-responsive

            if (!rows.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px 0; border:none;">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:10px;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 21l-4.35-4.35" stroke="#BDBDBD" stroke-width="1.8" stroke-linecap="round"/>
                                    <circle cx="10.5" cy="10.5" r="7.5" stroke="#BDBDBD" stroke-width="1.8"/>
                                </svg>
                                <span style="color:#8A8A8A; font-size:14px;">No data found</span>
                            </div>
                        </td>
                    </tr>
                `;
                if (wrapper) wrapper.style.maxHeight = '250px';
                return;
            }

            if (wrapper) wrapper.style.maxHeight = ''; // kembalikan ke default saat ada data

            tbody.innerHTML = rows.map(row => `
                <tr class="materials-row" onclick="handleMaterialsRowClick(event, this)" style="cursor:pointer;">
                    <td style="text-align:center;"><input type="checkbox" class="materials-check" value="${row.podtpk}"></td>
                    <td>${row.item}</td>
                    <td>${row.description}</td>
                    <td>${row.supplier}</td>
                    <td>${row.color}</td>
                    <td>${row.price}</td>
                    <td>${row.qty_po}</td>
                    <td>${row.qty_shipment}</td>
                    <td style="${row.balance_raw < 0 ? 'color:red;' : ''}">${row.balance}</td>
                </tr>
            `).join('');
        }

        function handleMaterialsRowClick(event, row) {
            const interactiveTags = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'];
            if (interactiveTags.includes(event.target.tagName)) {
                return;
            }

            const checkbox = row.querySelector('.materials-check');
            if (!checkbox) return;

            checkbox.checked = !checkbox.checked;
            // checkbox.dispatchEvent(new Event('change'));
            checkbox.dispatchEvent(new Event('change', {
                bubbles: true
            }));

            // ikut update highlight background saat toggle dari klik baris
            row.style.backgroundColor = checkbox.checked ? '#EAF4FC' : '';
        }

        let materialsFilterTimer = null;

        function filterMaterialsListDelay() {
            clearTimeout(materialsFilterTimer);
            materialsFilterTimer = setTimeout(() => {
                filterMaterialsList();
            }, 300);
        }

        // function filterMaterialsList() {
        //     const keyword = document.getElementById('materialsListSearch').value.toLowerCase();
        //     document.querySelectorAll('#materialsListBody tr').forEach(tr => {
        //         tr.style.display = tr.textContent.toLowerCase().includes(keyword) ? '' : 'none';
        //     });
        // }
        function filterMaterialsList() {
            const keyword = document.getElementById('materialsListSearch').value.toLowerCase();
            const tbody = document.getElementById('materialsListBody');
            const rows = tbody.querySelectorAll('tr:not(.materials-empty-row)');

            // hapus pesan "no data" lama kalau ada, biar tidak numpuk
            const existingEmptyRow = tbody.querySelector('.materials-empty-row');
            if (existingEmptyRow) existingEmptyRow.remove();

            let visibleCount = 0;

            rows.forEach(tr => {
                const match = tr.textContent.toLowerCase().includes(keyword);
                tr.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            if (visibleCount === 0) {
                const emptyRow = document.createElement('tr');
                emptyRow.className = 'materials-empty-row';
                emptyRow.innerHTML = `
                    <td colspan="9" style="text-align:center; padding:40px 0; border:none;">
                        <div style="display:flex; flex-direction:column; align-items:center; gap:10px;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21l-4.35-4.35" stroke="#BDBDBD" stroke-width="1.8" stroke-linecap="round"/>
                                <circle cx="10.5" cy="10.5" r="7.5" stroke="#BDBDBD" stroke-width="1.8"/>
                            </svg>
                            <span style="color:#8A8A8A; font-size:14px;">No data found</span>
                        </div>
                    </td>
                `;
                tbody.appendChild(emptyRow);
            }
        }

        function buildFieldChanges(oldData, newPayload) {
            const changes = [];

            // Normalisasi: null/undefined/''/'-' semua dianggap "kosong" yang setara
            const norm = (v) => {
                if (v === null || v === undefined) return '';
                const s = String(v).trim();
                return (s === '-' || s === '') ? '' : s;
            };

            const isSameNumber = (a, b) => {
                const numA = parseFloat(a);
                const numB = parseFloat(b);
                if (isNaN(numA) && isNaN(numB)) return true;
                if (isNaN(numA) || isNaN(numB)) return false;
                return numA === numB;
            };

            const vassalTypeOld = norm(oldData?.vassal_type);
            const vassalTypeNew = norm(vassalOptions.find(v => v.vassalpk == newPayload.vassalpk)?.typenm);
            if (vassalTypeOld !== vassalTypeNew) {
                changes.push({
                    section: 'Shipment details',
                    text: `Vassal type changed from <b>${vassalTypeOld || '-'}</b> to <b>${vassalTypeNew || '-'}</b>`
                });
            }

            const vassalNameOld = norm(oldData?.vassal_name);
            const vassalNameNew = norm(newPayload.vassal_name);
            if (vassalNameOld !== vassalNameNew) {
                changes.push({
                    section: 'Shipment details',
                    text: `Items moved from <b>${vassalNameOld || '-'}</b> to <b>${vassalNameNew || '-'}</b>`
                });
            }

            const awbOld = norm(oldData?.awb_raw);
            const awbNew = norm(newPayload.awb);
            if (awbOld !== awbNew) {
                changes.push({
                    section: 'Shipment details',
                    text: `AirwayBill Number change from <b>${awbOld || '-'}</b> to <b>${awbNew || '-'}</b>`
                });
            }

            const arrivalOld = norm(oldData?.arrival_city);
            const arrivalNew = norm(arrivalOptions.find(a => a.arrivalpk == newPayload.arrivalpk)?.city);
            if (arrivalOld !== arrivalNew) {
                changes.push({
                    section: 'Shipment details',
                    text: `Arrival changed from <b>${arrivalOld || '-'}</b> to <b>${arrivalNew || '-'}</b>`
                });
            }

            const portOld = norm(oldData?.port_name);
            const portNew = norm(portOptions.find(p => p.portpk == newPayload.portpk)?.portnm);
            if (portOld !== portNew) {
                changes.push({
                    section: 'Shipment details',
                    text: `Port changed from <b>${portOld || '-'}</b> to <b>${portNew || '-'}</b>`
                });
            }

            const gwOld = oldData?.gw_raw ?? 0;
            if (!isSameNumber(gwOld, newPayload.gw)) {
                changes.push({
                    section: 'Item details',
                    text: `Gross weight changed from <b>${gwOld}</b> to <b>${newPayload.gw}</b>`
                });
            }

            const dpOld = oldData?.dp_raw ?? 0;
            const dlOld = oldData?.dl_raw ?? 0;
            const dtOld = oldData?.dt_raw ?? 0;
            if (!isSameNumber(dpOld, newPayload.dp) || !isSameNumber(dlOld, newPayload.dl) || !isSameNumber(dtOld,
                    newPayload.dt)) {
                changes.push({
                    section: 'Item details',
                    text: `Dimension changed from <b>${dpOld}</b>X<b>${dlOld}</b>X<b>${dtOld}</b> to <b>${newPayload.dp}</b>X<b>${newPayload.dl}</b>X<b>${newPayload.dt}</b>`,
                });
            }

            const amountOld = oldData?.total_package_raw ?? 0;
            if (!isSameNumber(amountOld, newPayload.total_package)) {
                changes.push({
                    section: 'Item details',
                    text: `Amount of boxes changed from <b>${amountOld}</b> to <b>${newPayload.total_package}</b>`
                });
            }

            const etdOld = norm(oldData?.etd);
            const etdNew = norm(newPayload.etd);
            if (etdOld !== etdNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `ETD changed from <b>${etdOld || '-'}</b> to <b>${etdNew || '-'}</b>`
                });
            }

            const etaOld = norm(oldData?.eta);
            const etaNew = norm(newPayload.eta);
            if (etaOld !== etaNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `ETA changed from <b>${etaOld || '-'}</b> to <b>${etaNew || '-'}</b>`
                });
            }

            // const eihOld = norm(oldData?.eih);
            // const eihNew = norm(newPayload.eih);
            // if (eihOld !== eihNew) {
            //     changes.push({
            //         section: 'Shipment dates',
            //         text: `Estimate In-House changed from <b>${eihOld || '-'}</b> to <b>${eihNew || '-'}</b>`
            //     });
            // }

            // return changes;

            const eihOld = norm(oldData?.eih);
            const eihNew = norm(newPayload.eih);
            if (eihOld !== eihNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `Estimate In-House changed from <b>${eihOld || '-'}</b> to <b>${eihNew || '-'}</b>`
                });
            }

            const atdOld = norm(oldData?.atd);
            const atdNew = norm(newPayload.atd);
            if (atdOld !== atdNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `ATD changed from <b>${atdOld || '-'}</b> to <b>${atdNew || '-'}</b>`
                });
            }

            const ataOld = norm(oldData?.ata);
            const ataNew = norm(newPayload.ata);
            if (ataOld !== ataNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `ATA changed from <b>${ataOld || '-'}</b> to <b>${ataNew || '-'}</b>`
                });
            }

            return changes;
        }

        function renderConfirmChangesList(changes) {
            const container = document.getElementById('confirmChangesList');

            if (!changes.length) {
                container.innerHTML = `<div style="color:#999;">Tidak ada perubahan.</div>`;
                return;
            }

            const sectionOrder = ['Shipment details', 'Item details', 'Shipment dates'];

            let html = '';

            sectionOrder.forEach(section => {
                const items = changes.filter(c => c.section === section);

                if (!items.length) return; // sama seperti native: section disembunyikan kalau kosong

                html += `
            <div style="color:rgba(0,0,0,.38); font-weight:400; font-size:14px; margin-top:16px; margin-bottom:6px;">
                ${section}
            </div>
            <ul style="margin:0 0 8px 0; padding-left:20px;">
                ${items.map(c => `<li style="font-size:14px; color:#000; margin-bottom:4px;">${c.text}</li>`).join('')}
            </ul>
                `;
            });

            container.innerHTML = html;
        }

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

            document.getElementById('page').value = 1;
            submitFilter();
        }

        document.addEventListener('click', function(event) {
            document.querySelectorAll('.custom-dropdown-menu').forEach(menu => {
                const dropdown = menu.closest('.custom-dropdown');
                if (dropdown && !dropdown.contains(event.target)) {
                    menu.style.display = 'none';
                }
            });
        });


        function toggleHeaderStatusDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('headerStatusDropdown');
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('headerStatusDropdown');
            if (!dropdown) return;
            const trigger = document.getElementById('approve');
            if (!dropdown.contains(event.target) && !trigger.contains(event.target)) {
                dropdown.style.display = 'none';
            }
        });

        function getCheckedShipmentIds() {
            return Array.from(document.querySelectorAll('.ship-check:checked')).map(cb => cb.value);
        }

        function toggleShipUpdateMenu(trigger) {
            const menu = trigger.nextElementSibling;
            const isOpen = menu.classList.contains('show');

            // Tutup semua dropdown lain dulu (biar cuma 1 yang terbuka)
            document.querySelectorAll('.ship-update-menu.show').forEach(m => m.classList.remove('show'));

            if (!isOpen) {
                menu.classList.add('show');
            }
        }

        function togglePoExpand(event, wrap) {
            event.stopPropagation(); // jangan trigger buka shipment plan di <tr>

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

        // Tutup dropdown per-baris kalau klik di luar
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.ship-update-dropdown')) {
                document.querySelectorAll('.ship-update-menu.show').forEach(m => m.classList.remove('show'));
            }
        });

        

        function changeShipmentStatus(importpk, status) {
            document.querySelectorAll('.ship-update-menu.show').forEach(m => m.classList.remove('show'));

            fetch(`{{ url('/ship') }}/${importpk}/change-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        status: status
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        showToastShip(result.message || 'Failed to update shipment status', 'error');
                        return;
                    }
                    showToastShip('1 item successful updated');
                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    console.error(error);
                    showToastShip('Failed to update shipment status', 'error');
                });
        }

        function cancelShipment(importpk) {
            document.querySelectorAll('.ship-update-menu.show').forEach(m => m.classList.remove('show'));
            openCancelShipmentModal([importpk]);
        }

        let pendingCancelIds = [];

        function openCancelShipmentModal(ids) {
            pendingCancelIds = ids;
            document.getElementById('dialog-cancel-backdrop').style.display = 'block';
        }

        function closeCancelShipmentModal() {
            pendingCancelIds = [];
            document.getElementById('dialog-cancel-backdrop').style.display = 'none';
        }

        function confirmCancelShipment() {
            const ids = pendingCancelIds;

            if (ids.length === 0) {
                closeCancelShipmentModal();
                return;
            }

            const requests = ids.map(importpk =>
                fetch(`{{ url('/ship') }}/${importpk}/cancel`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(response => response.json())
            );

            Promise.all(requests)
                .then(results => {
                    const failed = results.filter(r => !r.success);

                    closeCancelShipmentModal();

                    if (failed.length > 0) {
                        showToastShip(`${failed.length} item(s) failed to cancel`, 'error');
                    } else {
                        showToastShip(`${ids.length} item successful cancelled`);
                    }

                    setTimeout(() => location.reload(), 1000);
                })
                .catch(error => {
                    console.error(error);
                    closeCancelShipmentModal();
                    showToastShip('Failed to cancel shipment', 'error');
                });
        }

        function showToastShip(message, type = 'success') {
            const toast = document.getElementById('toastNotifShip');
            const msgEl = document.getElementById('toastNotifShipMessage');

            toast.style.background = (type === 'error') ? '#EF3B42' : '#19B715';
            msgEl.textContent = message;
            toast.style.display = 'block';

            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

        function handleRowClick(event, row) {
            // jangan toggle checkbox kalau yang diklik adalah link, tombol, atau elemen interaktif lain di dalam baris
            const interactiveTags = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'];
            if (interactiveTags.includes(event.target.tagName)) {
                return;
            }

            // juga jangan toggle kalau klik terjadi di dalam dropdown Update (yang mungkin nested lebih dalam)
            if (event.target.closest('.ship-update-dropdown')) {
                return;
            }

            const checkbox = row.querySelector('.ship-check');
            if (!checkbox) return;

            checkbox.checked = !checkbox.checked;

            // trigger event 'change' secara manual supaya toggleActionBar() ikut jalan
            checkbox.dispatchEvent(new Event('change'));
        }

        // =========================
        // INIT (DOM READY)
        // =========================
        // document.addEventListener('DOMContentLoaded', function() {
        //     const header = document.getElementById('myHeader');
        //     const mainHeader = document.querySelector('nav.navbar');
        //     const closeMenu = document.getElementById('cls');

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

        //     document.addEventListener('scroll', function(e) {
        //         // HANYA proses scroll dari document/window (halaman utama).
        //         // Scroll dari dalam modal (.modal-dialog-scrollable, dsb) diabaikan,
        //         // supaya header sticky tidak ikut bergeser saat modal di-scroll.
        //         if (e.target !== document && e.target !== window) {
        //             return;
        //         }

        //         const scrollTop = getScrollTop(window);
        //         lastScrollTop = scrollTop;
        //         positionHeader(scrollTop);
        //     }, true);

        //     window.addEventListener('resize', function() {
        //         positionHeader(lastScrollTop);
        //     });

        //     positionHeader(0);

        //     // delegated (not bound directly) because #check-all is inside #tabel1,
        //     // which gets replaced wholesale on every AJAX filter/sort/search update
        //     document.addEventListener('change', function(e) {
        //         if (e.target.id !== 'check-all') return;

        //         document.querySelectorAll('.ship-check').forEach(function(checkbox) {
        //             checkbox.checked = e.target.checked;
        //         });
        //         toggleActionBar();
        //     });

        //     if (closeMenu) {
        //         closeMenu.addEventListener('click', function() {
        //             document.querySelectorAll('.ship-check').forEach(function(checkbox) {
        //                 checkbox.checked = false;
        //             });

        //             const checkAll = document.getElementById('check-all');
        //             if (checkAll) checkAll.checked = false;
        //             if (header) header.style.display = 'none';

        //             const countp = document.getElementById('countp');
        //             if (countp) countp.innerText = '0 items selected';

        //             const approve = document.getElementById('approve');
        //             if (approve) approve.style.display = 'block';
        //         });
        //     }

        //     document.addEventListener('click', function(event) {
        //         const dropdown = document.getElementById('shipSortDropdown');
        //         const trigger = document.getElementById('shipSortTrigger');
        //         const icon = document.querySelector('.imgsort');

        //         if (!dropdown) return;

        //         const insideDropdown = dropdown.contains(event.target);
        //         const insideTrigger = trigger && trigger.contains(event.target);
        //         const insideIcon = icon && icon.contains(event.target);

        //         if (!insideDropdown && !insideTrigger && !insideIcon) {
        //             dropdown.style.display = 'none';
        //         }
        //     });

        //     // ['modal_etd_input', 'modal_eta_input', 'modal_eih_input'].forEach(id => {
        //     //     const el = document.getElementById(id);
        //     //     if (!el) return;

        //     //     flatpickr(`#${id}`, {
        //     //         dateFormat: 'd/m/Y',
        //     //         allowInput: true,
        //     //         clickOpens: false, // kita kontrol buka lewat ikon, bukan klik input langsung
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
        //     ['modal_etd_input', 'modal_eta_input', 'modal_eih_input', 'modal_atd_input', 'modal_ata_input'].forEach(id => {
        //         const el = document.getElementById(id);
        //         if (!el) return;

        //         shipmentPlanFlatpickrs[id] = flatpickr(`#${id}`, {
        //             dateFormat: 'd/m/Y',
        //             allowInput: true,
        //             clickOpens: false,
        //             minDate: shipmentPlanMinDate,
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
        //                 syncShipmentPlanDateConstraints(id, selectedDates[0]);
        //                 clearShipmentPlanFieldError(id);
        //                 validateShipmentPlanDates();
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

        //     // ---- preload ship options (vassal/arrival/port) ----
        //     loadShipOptions();

        //     // ---- tombol Edit/Cancel ----
        //     const shipmentPlanEditBtn = document.getElementById('shipmentPlanEditBtn');
        //     if (shipmentPlanEditBtn) {
        //         shipmentPlanEditBtn.addEventListener('click', enterEditMode);
        //     }

        //     const shipmentPlanCancelBtn = document.getElementById('shipmentPlanCancelBtn');
        //     if (shipmentPlanCancelBtn) {
        //         shipmentPlanCancelBtn.addEventListener('click', exitEditMode);
        //     }

        //     // ---- tombol Save ----
        //     document.getElementById('shipmentPlanSaveBtn').addEventListener('click', function() {
        //         const dateCheck = validateShipmentPlanDates();
        //         if (!dateCheck.isValid) {
        //             showToastShip('Ada kesalahan pada Shipment dates, silakan periksa kembali', 'error');
        //             scrollToShipmentPlanError(dateCheck.firstErrorId);
        //             return;
        //         }

        //         const payload = {
        //             vassalpk: document.getElementById('modal_vassal_type_input').value,
        //             vassal_name: document.getElementById('modal_vassal_name_input').value,
        //             awb: document.getElementById('modal_awb_input').value,
        //             arrivalpk: document.getElementById('modal_arrival_city_input').value,
        //             portpk: document.getElementById('modal_port_name_input').value,
        //             gw: document.getElementById('modal_gw_input').value,
        //             dp: document.getElementById('modal_dp_input').value,
        //             dl: document.getElementById('modal_dl_input').value,
        //             dt: document.getElementById('modal_dt_input').value,
        //             total_package: document.getElementById('modal_tpackage_input').value,
        //             etd: document.getElementById('modal_etd_input').value,
        //             eta: document.getElementById('modal_eta_input').value,
        //             eih: document.getElementById('modal_eih_input').value,
        //             atd: document.getElementById('modal_atd_input').value,
        //             ata: document.getElementById('modal_ata_input').value,
        //             _token: '{{ csrf_token() }}',
        //         };

        //         const changes = buildFieldChanges(window.currentShip, payload);
        //         renderConfirmChangesList(changes);

        //         pendingShipmentPayload = payload;
        //         pendingShipmentSource = 'full';   // ⬅️ tambahkan ini

        //         const modalEl = document.getElementById('confirmChangesModal');
        //         if (!confirmChangesModalInstance) {
        //             confirmChangesModalInstance = new bootstrap.Modal(modalEl);
        //         }
        //         confirmChangesModalInstance.show();
        //     });

        //     // document.getElementById('confirmChangesSaveBtn').addEventListener('click', function() {
        //     //     const importpk = document.getElementById('modal_importpk').value;

        //     //     fetch(`{{ route('exim.import.ship.update', ':id') }}`.replace(':id', importpk), {
        //     //             method: 'PUT',
        //     //             headers: {
        //     //                 'Content-Type': 'application/json'
        //     //             },
        //     //             body: JSON.stringify(pendingShipmentPayload),
        //     //         })
        //     //         .then(res => res.json())
        //     //         .then(res => {
        //     //             confirmChangesModalInstance.hide();

        //     //             if (res.success) {
        //     //                 showToastShip(res.message);
        //     //                 openShipmentPlan(importpk);
        //     //                 fetchShips(new URLSearchParams(window.location.search), false);
        //     //             } else {
        //     //                 showToastShip(res.message || 'Gagal menyimpan', 'error');
        //     //             }
        //     //         })
        //     //         .catch(err => {
        //     //             console.error(err);
        //     //             showToastShip('Terjadi kesalahan saat menyimpan', 'error');
        //     //         });
        //     // });

        //     document.getElementById('confirmChangesSaveBtn').addEventListener('click', function() {
        //         const importpk = pendingShipmentSource === 'dates'
        //             ? document.getElementById('sd_importpk').value
        //             : document.getElementById('modal_importpk').value;

        //         fetch(`{{ route('exim.import.ship.update', ':id') }}`.replace(':id', importpk), {
        //                 method: 'PUT',
        //                 headers: {
        //                     'Content-Type': 'application/json'
        //                 },
        //                 body: JSON.stringify(pendingShipmentPayload),
        //             })
        //             .then(res => res.json())
        //             .then(res => {
        //                 confirmChangesModalInstance.hide();

        //                 if (res.success) {
        //                     showToastShip(res.message);

        //                     if (pendingShipmentSource === 'dates') {
        //                         shipmentDatesModalInstance?.hide();
        //                     } else {
        //                         openShipmentPlan(importpk);
        //                     }

        //                     fetchShips(new URLSearchParams(window.location.search), false);
        //                 } else {
        //                     showToastShip(res.message || 'Gagal menyimpan', 'error');
        //                 }
        //             })
        //             .catch(err => {
        //                 console.error(err);
        //                 showToastShip('Terjadi kesalahan saat menyimpan', 'error');
        //             });
        //     });

        //     document.getElementById('materialsListCheckAll').addEventListener('change', function() {
        //         const isChecked = this.checked;
        //         document.querySelectorAll('.materials-check').forEach(cb => {
        //             cb.checked = isChecked;
        //             const row = cb.closest('.materials-row');
        //             if (row) row.style.backgroundColor = isChecked ? '#EAF4FC' : '';
        //         });
        //     });

        //     document.getElementById('materialsListBody').addEventListener('change', function(event) {
        //         if (event.target.classList.contains('materials-check')) {
        //             const row = event.target.closest('.materials-row');
        //             if (row) {
        //                 row.style.backgroundColor = event.target.checked ? '#EAF4FC' : '';
        //             }

        //             // sinkronkan status "check all" di header berdasarkan checkbox individual
        //             syncMaterialsCheckAll();
        //         }
        //     });

        //     function syncMaterialsCheckAll() {
        //         const allCheckboxes = document.querySelectorAll('.materials-check');
        //         const checkedCheckboxes = document.querySelectorAll('.materials-check:checked');
        //         const checkAll = document.getElementById('materialsListCheckAll');

        //         checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
        //     }

        //     document.getElementById('materialsListAddBtn').addEventListener('click', function() {
        //         const checked = Array.from(document.querySelectorAll('.materials-check:checked')).map(cb =>
        //             cb.value);

        //         if (!checked.length) {
        //             showToastShip('No material data selected', 'error');
        //             return;
        //         }

        //         const importpk = document.getElementById('modal_importpk').value;

        //         fetch(`{{ route('exim.import.ship.addMaterials', ':id') }}`.replace(':id', importpk), {
        //                 method: 'POST',
        //                 headers: {
        //                     'Content-Type': 'application/json'
        //                 },
        //                 body: JSON.stringify({
        //                     podtpks: checked,
        //                     _token: '{{ csrf_token() }}'
        //                 }),
        //             })
        //             .then(res => res.json())
        //             .then(res => {
        //                 if (!res.success) {
        //                     showToastShip(res.message || 'Failed to add materials', 'error');
        //                     return;
        //                 }

        //                 showToastShip(res.message);
        //                 materialsListModalInstance.hide();

        //                 fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
        //                     .then(r => r.json())
        //                     .then(r => {
        //                         if (r.success) {
        //                             window.currentShip = r.data;
        //                             renderShipmentPoDetails(r.data.po_details || [], true);
        //                         }
        //                     });
        //             })
        //             .catch(err => {
        //                 console.error(err);
        //                 showToastShip('Failed to add materials', 'error');
        //             });
        //     });

        //     document.getElementById('materialsListModal').addEventListener('show.bs.modal', function() {
        //         const openModalsCount = document.querySelectorAll('.modal.show').length;
        //         const zIndexBase = 10000 + (openModalsCount * 20); // disesuaikan dari 1050 -> 10000

        //         this.style.zIndex = zIndexBase + 10;

        //         setTimeout(() => {
        //             const backdrops = document.querySelectorAll('.modal-backdrop');
        //             const lastBackdrop = backdrops[backdrops.length - 1];
        //             if (lastBackdrop) {
        //                 lastBackdrop.style.zIndex = zIndexBase;
        //             }
        //         }, 0);
        //     });

        //     // Saat modal kedua ditutup, hapus backdrop tambahan supaya tidak numpuk
        //     document.getElementById('materialsListModal').addEventListener('hidden.bs.modal', function() {
        //         // biarkan Bootstrap handle penghapusan backdrop-nya sendiri,
        //         // cukup pastikan body tetap punya class modal-open kalau modal pertama masih terbuka
        //         if (document.querySelectorAll('.modal.show').length > 0) {
        //             document.body.classList.add('modal-open');
        //         }
        //     });
        // });

        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('myHeader');
            const closeMenu = document.getElementById('cls');

            if (header && header.parentElement !== document.body) {
                document.body.appendChild(header);
            }

            // delegated (not bound directly) because #check-all is inside #tabel1,
            // which gets replaced wholesale on every AJAX filter/sort/search update
            document.addEventListener('change', function(e) {
                if (e.target.id !== 'check-all') return;

                document.querySelectorAll('.ship-check').forEach(function(checkbox) {
                    checkbox.checked = e.target.checked;
                });
                toggleActionBar();
            });

            if (closeMenu) {
                closeMenu.addEventListener('click', function() {
                    document.querySelectorAll('.ship-check').forEach(function(checkbox) {
                        checkbox.checked = false;
                    });

                    const checkAll = document.getElementById('check-all');
                    if (checkAll) checkAll.checked = false;
                    if (header) header.style.display = 'none';

                    const countp = document.getElementById('countp');
                    if (countp) countp.innerText = '0 items selected';

                    const approve = document.getElementById('approve');
                    if (approve) approve.style.display = 'block';
                });
            }

            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('shipSortDropdown');
                const trigger = document.getElementById('shipSortTrigger');
                const icon = document.querySelector('.imgsort');

                if (!dropdown) return;

                const insideDropdown = dropdown.contains(event.target);
                const insideTrigger = trigger && trigger.contains(event.target);
                const insideIcon = icon && icon.contains(event.target);

                if (!insideDropdown && !insideTrigger && !insideIcon) {
                    dropdown.style.display = 'none';
                }
            });

            // ['modal_etd_input', 'modal_eta_input', 'modal_eih_input'].forEach(id => {
            //     const el = document.getElementById(id);
            //     if (!el) return;

            //     flatpickr(`#${id}`, {
            //         dateFormat: 'd/m/Y',
            //         allowInput: true,
            //         clickOpens: false, // kita kontrol buka lewat ikon, bukan klik input langsung
            //         onReady: function(selectedDates, dateStr, instance) {
            //             const footer = document.createElement('div');
            //             footer.style.cssText =
            //                 'display:flex; justify-content:space-between; padding:8px 12px; border-top:1px solid #eee;';

            //             const todayBtn = document.createElement('a');
            //             todayBtn.textContent = 'Today';
            //             todayBtn.href = 'javascript:void(0)';
            //             todayBtn.style.cssText =
            //                 'color:#359DD9; text-decoration:none; font-weight:600;';
            //             todayBtn.addEventListener('click', function() {
            //                 instance.setDate(new Date(), true);
            //             });

            //             const closeBtn = document.createElement('a');
            //             closeBtn.textContent = 'Close';
            //             closeBtn.href = 'javascript:void(0)';
            //             closeBtn.style.cssText =
            //                 'color:#000; text-decoration:none; font-weight:600;';
            //             closeBtn.addEventListener('click', function() {
            //                 instance.close();
            //             });

            //             footer.appendChild(todayBtn);
            //             footer.appendChild(closeBtn);
            //             instance.calendarContainer.appendChild(footer);
            //         },
            //     });
            // });
            ['modal_etd_input', 'modal_eta_input', 'modal_eih_input', 'modal_atd_input', 'modal_ata_input'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;

                shipmentPlanFlatpickrs[id] = flatpickr(`#${id}`, {
                    dateFormat: 'd/m/Y',
                    allowInput: true,
                    clickOpens: false,
                    minDate: shipmentPlanMinDate,
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
                        syncShipmentPlanDateConstraints(id, selectedDates[0]);
                        clearShipmentPlanFieldError(id);
                        validateShipmentPlanDates();
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

            // ---- preload ship options (vassal/arrival/port) ----
            loadShipOptions();

            // ---- tombol Edit/Cancel ----
            const shipmentPlanEditBtn = document.getElementById('shipmentPlanEditBtn');
            if (shipmentPlanEditBtn) {
                shipmentPlanEditBtn.addEventListener('click', enterEditMode);
            }

            const shipmentPlanCancelBtn = document.getElementById('shipmentPlanCancelBtn');
            if (shipmentPlanCancelBtn) {
                shipmentPlanCancelBtn.addEventListener('click', exitEditMode);
            }

            // ---- tombol Save ----
            document.getElementById('shipmentPlanSaveBtn').addEventListener('click', function() {
                const dateCheck = validateShipmentPlanDates();
                if (!dateCheck.isValid) {
                    showToastShip('Ada kesalahan pada Shipment dates, silakan periksa kembali', 'error');
                    scrollToShipmentPlanError(dateCheck.firstErrorId);
                    return;
                }

                const payload = {
                    vassalpk: document.getElementById('modal_vassal_type_input').value,
                    vassal_name: document.getElementById('modal_vassal_name_input').value,
                    awb: document.getElementById('modal_awb_input').value,
                    arrivalpk: document.getElementById('modal_arrival_city_input').value,
                    portpk: document.getElementById('modal_port_name_input').value,
                    gw: document.getElementById('modal_gw_input').value,
                    dp: document.getElementById('modal_dp_input').value,
                    dl: document.getElementById('modal_dl_input').value,
                    dt: document.getElementById('modal_dt_input').value,
                    total_package: document.getElementById('modal_tpackage_input').value,
                    etd: document.getElementById('modal_etd_input').value,
                    eta: document.getElementById('modal_eta_input').value,
                    eih: document.getElementById('modal_eih_input').value,
                    atd: document.getElementById('modal_atd_input').value,
                    ata: document.getElementById('modal_ata_input').value,
                    _token: '{{ csrf_token() }}',
                };

                const changes = buildFieldChanges(window.currentShip, payload);
                renderConfirmChangesList(changes);

                // reset info line (bukan bulk edit)
                document.getElementById('confirmChangesInfo').style.display = 'none';

                pendingShipmentPayload = payload;
                pendingShipmentSource = 'full';   // ⬅️ tambahkan ini

                const modalEl = document.getElementById('confirmChangesModal');
                if (!confirmChangesModalInstance) {
                    confirmChangesModalInstance = new bootstrap.Modal(modalEl);
                }
                confirmChangesModalInstance.show();
            });

            // document.getElementById('confirmChangesSaveBtn').addEventListener('click', function() {
            //     const importpk = document.getElementById('modal_importpk').value;

            //     fetch(`{{ route('exim.import.ship.update', ':id') }}`.replace(':id', importpk), {
            //             method: 'PUT',
            //             headers: {
            //                 'Content-Type': 'application/json'
            //             },
            //             body: JSON.stringify(pendingShipmentPayload),
            //         })
            //         .then(res => res.json())
            //         .then(res => {
            //             confirmChangesModalInstance.hide();

            //             if (res.success) {
            //                 showToastShip(res.message);
            //                 openShipmentPlan(importpk);
            //                 fetchShips(new URLSearchParams(window.location.search), false);
            //             } else {
            //                 showToastShip(res.message || 'Gagal menyimpan', 'error');
            //             }
            //         })
            //         .catch(err => {
            //             console.error(err);
            //             showToastShip('Terjadi kesalahan saat menyimpan', 'error');
            //         });
            // });

            document.getElementById('confirmChangesSaveBtn').addEventListener('click', function() {
                if (pendingShipmentSource === 'bulk') {
                    const newValues = pendingShipmentPayload;

                    const requests = bulkEditImportpks.map(importpk => {
                        return fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
                            .then(res => res.json())
                            .then(res => {
                                const data = res.success ? res.data : {};

                                const payload = {
                                    vassalpk: data.vassalpk,
                                    vassal_name: data.vassal_name,
                                    awb: data.awb_raw,
                                    arrivalpk: data.arrivalpk,
                                    portpk: data.portpk,
                                    gw: data.gw_raw ?? 0,
                                    dp: data.dp_raw ?? 0,
                                    dl: data.dl_raw ?? 0,
                                    dt: data.dt_raw ?? 0,
                                    total_package: data.total_package_raw ?? 0,
                                    etd: data.etd,
                                    eta: newValues.eta,
                                    eih: newValues.eih,
                                    atd: data.atd,
                                    ata: newValues.ata,
                                    _token: '{{ csrf_token() }}',
                                };

                                return fetch(`{{ route('exim.import.ship.update', ':id') }}`.replace(':id', importpk), {
                                    method: 'PUT',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(payload),
                                }).then(r => r.json());
                            });
                    });

                    Promise.all(requests)
                        .then(results => {
                            const failed = results.filter(r => !r.success);

                            confirmChangesModalInstance.hide();

                            document.querySelectorAll('.ship-check').forEach(cb => cb.checked = false);
                            const checkAll = document.getElementById('check-all');
                            if (checkAll) checkAll.checked = false;
                            document.getElementById('myHeader').style.display = 'none';

                            if (failed.length > 0) {
                                showToastShip(`${failed.length} item(s) failed to update`, 'error');
                            } else {
                                showToastShip(`${bulkEditImportpks.length} item successful updated`);
                            }

                            fetchShips(new URLSearchParams(window.location.search), false);
                        })
                        .catch(err => {
                            console.error(err);
                            confirmChangesModalInstance.hide();
                            showToastShip('Failed to update shipment dates', 'error');
                        });

                    return;
                }

                // ---- flow lama untuk 'full' & 'dates' ----
                const importpk = pendingShipmentSource === 'dates'
                    ? document.getElementById('sd_importpk').value
                    : document.getElementById('modal_importpk').value;

                fetch(`{{ route('exim.import.ship.update', ':id') }}`.replace(':id', importpk), {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(pendingShipmentPayload),
                    })
                    .then(res => res.json())
                    .then(res => {
                        confirmChangesModalInstance.hide();

                        if (res.success) {
                            showToastShip(res.message);

                            if (pendingShipmentSource === 'dates') {
                                shipmentDatesModalInstance?.hide();
                            } else {
                                openShipmentPlan(importpk);
                            }

                            fetchShips(new URLSearchParams(window.location.search), false);
                        } else {
                            showToastShip(res.message || 'Gagal menyimpan', 'error');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        confirmChangesModalInstance.hide();
                        showToastShip('Terjadi kesalahan saat menyimpan', 'error');
                    });
            });

            

            document.getElementById('materialsListCheckAll').addEventListener('change', function() {
                const isChecked = this.checked;
                document.querySelectorAll('.materials-check').forEach(cb => {
                    cb.checked = isChecked;
                    const row = cb.closest('.materials-row');
                    if (row) row.style.backgroundColor = isChecked ? '#EAF4FC' : '';
                });
            });

            document.getElementById('materialsListBody').addEventListener('change', function(event) {
                if (event.target.classList.contains('materials-check')) {
                    const row = event.target.closest('.materials-row');
                    if (row) {
                        row.style.backgroundColor = event.target.checked ? '#EAF4FC' : '';
                    }

                    // sinkronkan status "check all" di header berdasarkan checkbox individual
                    syncMaterialsCheckAll();
                }
            });

            function syncMaterialsCheckAll() {
                const allCheckboxes = document.querySelectorAll('.materials-check');
                const checkedCheckboxes = document.querySelectorAll('.materials-check:checked');
                const checkAll = document.getElementById('materialsListCheckAll');

                checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
            }

            document.getElementById('materialsListAddBtn').addEventListener('click', function() {
                const checked = Array.from(document.querySelectorAll('.materials-check:checked')).map(cb =>
                    cb.value);

                if (!checked.length) {
                    showToastShip('No material data selected', 'error');
                    return;
                }

                const importpk = document.getElementById('modal_importpk').value;

                fetch(`{{ route('exim.import.ship.addMaterials', ':id') }}`.replace(':id', importpk), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            podtpks: checked,
                            _token: '{{ csrf_token() }}'
                        }),
                    })
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) {
                            showToastShip(res.message || 'Failed to add materials', 'error');
                            return;
                        }

                        showToastShip(res.message);
                        materialsListModalInstance.hide();

                        fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
                            .then(r => r.json())
                            .then(r => {
                                if (r.success) {
                                    window.currentShip = r.data;
                                    renderShipmentPoDetails(r.data.po_details || [], true);
                                }
                            });
                    })
                    .catch(err => {
                        console.error(err);
                        showToastShip('Failed to add materials', 'error');
                    });
            });

            document.getElementById('materialsListModal').addEventListener('show.bs.modal', function() {
                const openModalsCount = document.querySelectorAll('.modal.show').length;
                const zIndexBase = 10000 + (openModalsCount * 20); // disesuaikan dari 1050 -> 10000

                this.style.zIndex = zIndexBase + 10;

                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    const lastBackdrop = backdrops[backdrops.length - 1];
                    if (lastBackdrop) {
                        lastBackdrop.style.zIndex = zIndexBase;
                    }
                }, 0);
            });

            // Saat modal kedua ditutup, hapus backdrop tambahan supaya tidak numpuk
            document.getElementById('materialsListModal').addEventListener('hidden.bs.modal', function() {
                // biarkan Bootstrap handle penghapusan backdrop-nya sendiri,
                // cukup pastikan body tetap punya class modal-open kalau modal pertama masih terbuka
                if (document.querySelectorAll('.modal.show').length > 0) {
                    document.body.classList.add('modal-open');
                }
            });
        });

        document.getElementById('confirmChangesModal').addEventListener('show.bs.modal', function() {
            const openModalsCount = document.querySelectorAll('.modal.show').length;
            const zIndexBase = 10000 + (openModalsCount * 20);

            this.style.zIndex = zIndexBase + 10;

            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                const lastBackdrop = backdrops[backdrops.length - 1];
                if (lastBackdrop) {
                    lastBackdrop.style.zIndex = zIndexBase;
                }
            }, 0);
        });

        document.addEventListener('click', function(event) {
            ['modal_vassal_type_menu', 'modal_arrival_city_menu', 'modal_port_name_menu'].forEach(menuId => {
                const menu = document.getElementById(menuId);
                if (!menu) return;
                const wrap = menu.closest('.custom-dropdown');
                if (wrap && !wrap.contains(event.target)) {
                    menu.style.display = 'none';
                }
            });
        });


        ['sd_etd_input', 'sd_eta_input', 'sd_eih_input', 'sd_atd_input', 'sd_ata_input'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;

            shipmentDatesFlatpickrs[id] = flatpickr(`#${id}`, {
                dateFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: false,
                minDate: shipmentPlanMinDate,
                onReady: function(selectedDates, dateStr, instance) {
                    const footer = document.createElement('div');
                    footer.style.cssText =
                        'display:flex; justify-content:space-between; padding:8px 12px; border-top:1px solid #eee;';

                    const todayBtn = document.createElement('a');
                    todayBtn.textContent = 'Today';
                    todayBtn.href = 'javascript:void(0)';
                    todayBtn.style.cssText = 'color:#359DD9; text-decoration:none; font-weight:600;';
                    todayBtn.addEventListener('click', function() {
                        instance.setDate(new Date(), true);
                    });

                    const closeBtn = document.createElement('a');
                    closeBtn.textContent = 'Close';
                    closeBtn.href = 'javascript:void(0)';
                    closeBtn.style.cssText = 'color:#000; text-decoration:none; font-weight:600;';
                    closeBtn.addEventListener('click', function() {
                        instance.close();
                    });

                    footer.appendChild(todayBtn);
                    footer.appendChild(closeBtn);
                    instance.calendarContainer.appendChild(footer);
                },
                // onChange: function(selectedDates) {
                //     if (id === 'sd_etd_input' && shipmentDatesFlatpickrs.sd_eta_input) {
                //         shipmentDatesFlatpickrs.sd_eta_input.set('minDate', selectedDates[0]);
                //     }
                //     if (id === 'sd_eta_input') {
                //         if (shipmentDatesFlatpickrs.sd_etd_input) shipmentDatesFlatpickrs.sd_etd_input.set('maxDate', selectedDates[0]);
                //         if (shipmentDatesFlatpickrs.sd_eih_input) shipmentDatesFlatpickrs.sd_eih_input.set('minDate', selectedDates[0]);
                //     }
                //     if (id === 'sd_eih_input' && shipmentDatesFlatpickrs.sd_eta_input) {
                //         shipmentDatesFlatpickrs.sd_eta_input.set('maxDate', selectedDates[0]);
                //     }

                //     validateShipmentDatesFields();
                // },
                onChange: function(selectedDates) {
                    syncShipmentDatesConstraints(id, selectedDates[0]);
                    validateShipmentDatesFields();
                },
            });
        });

        document.getElementById('shipmentDatesModal')?.addEventListener('hidden.bs.modal', function() {
            resetShipmentDatesErrors();
        });
        document.getElementById('shipmentPlanModal')?.addEventListener('hidden.bs.modal', function() {
            clearAllShipmentPlanDateErrors();
        });

        document.getElementById('shipmentDatesSaveBtn').addEventListener('click', function() {
            const dateCheck = validateShipmentDatesFields();
            if (!dateCheck.isValid) {
                showToastShip('Ada kesalahan pada Shipment dates, silakan periksa kembali', 'error');
                scrollToShipmentPlanError(dateCheck.firstErrorId);
                return;
            }

            const data = window.currentShipDates || {};

            // const payload = {
            //     vassalpk: data.vassalpk,
            //     vassal_name: data.vassal_name,
            //     awb: data.awb_raw,
            //     arrivalpk: data.arrivalpk,
            //     portpk: data.portpk,
            //     gw: data.gw_raw,
            //     dp: data.dp_raw,
            //     dl: data.dl_raw,
            //     dt: data.dt_raw,
            //     total_package: data.total_package_raw,
            //     etd: document.getElementById('sd_etd_input').value,
            //     eta: document.getElementById('sd_eta_input').value,
            //     eih: document.getElementById('sd_eih_input').value,
            //     atd: document.getElementById('sd_atd_input').value,
            //     ata: document.getElementById('sd_ata_input').value,
            //     _token: '{{ csrf_token() }}',
            // };
            const payload = {
                vassalpk: data.vassalpk,
                vassal_name: data.vassal_name,
                awb: data.awb_raw,
                arrivalpk: data.arrivalpk,
                portpk: data.portpk,
                gw: data.gw_raw ?? 0,
                dp: data.dp_raw ?? 0,
                dl: data.dl_raw ?? 0,
                dt: data.dt_raw ?? 0,
                total_package: data.total_package_raw ?? 0,
                etd: document.getElementById('sd_etd_input').value,
                eta: document.getElementById('sd_eta_input').value,
                eih: document.getElementById('sd_eih_input').value,
                atd: document.getElementById('sd_atd_input').value,
                ata: document.getElementById('sd_ata_input').value,
                _token: '{{ csrf_token() }}',
            };

            const changes = buildFieldChanges(data, payload);
            renderConfirmChangesList(changes);

            // reset info line (bukan bulk edit)
            document.getElementById('confirmChangesInfo').style.display = 'none';

            pendingShipmentPayload = payload;
            pendingShipmentSource = 'dates';

            const modalEl = document.getElementById('confirmChangesModal');
            if (!confirmChangesModalInstance) {
                confirmChangesModalInstance = new bootstrap.Modal(modalEl);
            }
            confirmChangesModalInstance.show();
        });

        function getCheckedShipRows() {
            return Array.from(document.querySelectorAll('.ship-check:checked'))
                .map(cb => cb.closest('.ship-row'))
                .filter(Boolean);
        }

        // function openBulkEditDatesModal() {
        //     const rows = getCheckedShipRows();

        //     if (!rows.length) {
        //         showToastShip('No shipment selected', 'error');
        //         return;
        //     }

        //     const vassalNames = [...new Set(rows.map(r => (r.dataset.vassalnm || '').trim()))];
        //     if (vassalNames.length > 1) {
        //         showToastShip('Vassal name harus sama untuk edit sekaligus', 'error');
        //         return;
        //     }

        //     bulkEditImportpks = rows.map(r => r.dataset.importpk);

        //     resetBulkEditDatesForm();

        //     document.getElementById('bulkEditSelectedInfo').textContent =
        //         bulkEditImportpks.length > 1
        //             ? `${bulkEditImportpks.length} shipment akan diperbarui (Vassal: ${vassalNames[0] || '-'})`
        //             : `1 shipment akan diperbarui (Vassal: ${vassalNames[0] || '-'})`;

        //     const modalEl = document.getElementById('bulkEditDatesModal');
        //     if (!bulkEditDatesModalInstance) {
        //         bulkEditDatesModalInstance = new bootstrap.Modal(modalEl);
        //     }
        //     bulkEditDatesModalInstance.show();

        //     // Kalau cuma 1 shipment dipilih, ambil datanya untuk di-prefill + tampilkan AIH.
        //     // Kalau lebih dari 1 (nilai lama bisa beda-beda antar shipment), form dibiarkan kosong.
        //     if (bulkEditImportpks.length === 1) {
        //         fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', bulkEditImportpks[0]))
        //             .then(res => res.json())
        //             .then(res => {
        //                 if (!res.success) return;
        //                 const data = res.data;
        //                 window.currentBulkEditShip = data;

        //                 document.getElementById('bed_aih').textContent = data.aih || '-';

        //                 relaxMinDateForExistingValue(bulkEditDatesFlatpickrs, 'bed_eta_input', data.eta);
        //                 relaxMinDateForExistingValue(bulkEditDatesFlatpickrs, 'bed_eih_input', data.eih);
        //                 relaxMinDateForExistingValue(bulkEditDatesFlatpickrs, 'bed_ata_input', data.ata);

        //                 document.getElementById('bed_eta_input')._flatpickr?.setDate(data.eta || '', false, 'd/m/Y');
        //                 document.getElementById('bed_eih_input')._flatpickr?.setDate(data.eih || '', false, 'd/m/Y');
        //                 document.getElementById('bed_ata_input')._flatpickr?.setDate(data.ata || '', false, 'd/m/Y');

        //                 applyBedAihDateConstraint(data);
        //             });
        //     } else {
        //         window.currentBulkEditShip = null;
        //     }
        // }
        // Cek apakah semua value di array kosong-semua atau sama-semua (mengabaikan yang kosong).
        // return { ok: boolean, value: string }  -> value = nilai yang disepakati (bisa '' kalau semua kosong)
        function checkSameOrEmpty(values) {
            const nonEmpty = values.map(v => (v || '').trim()).filter(v => v !== '');
            if (nonEmpty.length === 0) {
                return { ok: true, value: '' };
            }
            const unique = [...new Set(nonEmpty)];
            if (unique.length > 1) {
                return { ok: false, value: null };
            }
            return { ok: true, value: unique[0] };
        }

        function openBulkEditDatesModal() {
            const rows = getCheckedShipRows();
            const eligibility = evaluateBulkEditEligibility(rows);

            if (!eligibility.eligible) {
                return;
            }

            bulkEditImportpks = rows.map(r => r.dataset.importpk);

            resetBulkEditDatesForm();

            const infoText = bulkEditImportpks.length > 1
                ? `${bulkEditImportpks.length} shipment akan diperbarui (Vassal: ${eligibility.vassal})`
                : `1 shipment akan diperbarui (Vassal: ${eligibility.vassal})`;

            document.getElementById('bulkEditSelectedInfo').textContent = infoText;

            // simpan untuk dipakai lagi di confirmChangesModal
            window.pendingBulkEditInfoText = infoText;
            window.currentBulkEditOldValues = {
                eta: eligibility.eta,
                eih: eligibility.eih,
                ata: eligibility.ata,
            };

            const modalEl = document.getElementById('bulkEditDatesModal');
            if (!bulkEditDatesModalInstance) {
                bulkEditDatesModalInstance = new bootstrap.Modal(modalEl);
            }
            bulkEditDatesModalInstance.show();

            relaxMinDateForExistingValue(bulkEditDatesFlatpickrs, 'bed_eta_input', eligibility.eta);
            relaxMinDateForExistingValue(bulkEditDatesFlatpickrs, 'bed_eih_input', eligibility.eih);
            relaxMinDateForExistingValue(bulkEditDatesFlatpickrs, 'bed_ata_input', eligibility.ata);

            document.getElementById('bed_eta_input')._flatpickr?.setDate(eligibility.eta || '', false, 'd/m/Y');
            document.getElementById('bed_eih_input')._flatpickr?.setDate(eligibility.eih || '', false, 'd/m/Y');
            document.getElementById('bed_ata_input')._flatpickr?.setDate(eligibility.ata || '', false, 'd/m/Y');

            document.getElementById('bed_aih').textContent = eligibility.aih || '-';

            if (bulkEditImportpks.length === 1) {
                fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', bulkEditImportpks[0]))
                    .then(res => res.json())
                    .then(res => {
                        if (!res.success) return;
                        window.currentBulkEditShip = res.data;
                        applyBedAihDateConstraint(res.data);
                    });
            } else {
                window.currentBulkEditShip = null;
            }
        }

        function resetBulkEditDatesForm() {
            resetBulkEditDatesErrors();
            document.getElementById('bed_aih').textContent = '-';
            window.currentBulkEditShip = null;

            ['bed_eta_input', 'bed_eih_input', 'bed_ata_input'].forEach(id => {
                const fp = bulkEditDatesFlatpickrs[id];
                if (fp) {
                    fp.clear();
                    fp.set('minDate', shipmentPlanMinDate);
                    fp.set('maxDate', null);
                }
            });
        }

        function resetBulkEditDatesErrors() {
            ['bed_eta_input', 'bed_eih_input', 'bed_ata_input'].forEach(id => {
                const input = document.getElementById(id);
                const errorEl = document.getElementById(id.replace('_input', '') + '-error');
                if (input) input.classList.remove('field-error');
                if (errorEl) errorEl.style.display = 'none';
            });
        }

        function setBedFieldError(inputId, message) {
            const input = document.getElementById(inputId);
            const errorEl = document.getElementById(inputId.replace('_input', '') + '-error');
            if (input) input.classList.add('field-error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.style.display = 'block';
            }
        }

        function getBedFieldDate(id) {
            const fp = bulkEditDatesFlatpickrs[id];
            return (fp && fp.selectedDates.length) ? fp.selectedDates[0] : null;
        }

        function applyBedAihDateConstraint(data) {
            const aihDate = parseDMYDate(data?.aih);
            if (!aihDate) return;

            if (bulkEditDatesFlatpickrs.bed_ata_input) {
                bulkEditDatesFlatpickrs.bed_ata_input.set('maxDate', aihDate);
                bulkEditDatesFlatpickrs.bed_ata_input.set('minDate', null);
            }
        }

        function validateBulkEditDatesFields() {
            resetBulkEditDatesErrors();

            const etaInput = document.getElementById('bed_eta_input');
            const eihInput = document.getElementById('bed_eih_input');
            const ataInput = document.getElementById('bed_ata_input');

            const etaDate = getBedFieldDate('bed_eta_input');
            const eihDate = getBedFieldDate('bed_eih_input');
            const ataDate = getBedFieldDate('bed_ata_input');

            let isValid = true;
            let firstErrorId = null;

            const markError = (inputId, message) => {
                setBedFieldError(inputId, message);
                isValid = false;
                if (!firstErrorId) firstErrorId = inputId;
            };

            const isSingle = bulkEditImportpks.length === 1;
            const aihDate = isSingle ? parseDMYDate(window.currentBulkEditShip?.aih) : null;

            // Kalau single-select & nilai belum diubah dari original, skip aturan "7 hari lalu"
            const etaUnchanged = isSingle && isUnchangedFromOriginal('bed_eta_input', window.currentBulkEditShip?.eta);
            const eihUnchanged = isSingle && isUnchangedFromOriginal('bed_eih_input', window.currentBulkEditShip?.eih);
            const ataUnchanged = isSingle && isUnchangedFromOriginal('bed_ata_input', window.currentBulkEditShip?.ata);

            if (!etaUnchanged && etaInput.value && (!etaDate || etaDate < shipmentPlanMinDate)) {
                markError('bed_eta_input', 'ETA tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!eihUnchanged && eihInput.value && (!eihDate || eihDate < shipmentPlanMinDate)) {
                markError('bed_eih_input', 'EIH tidak boleh lebih lama dari 7 hari yang lalu');
            }
            if (!aihDate) {
                if (!ataUnchanged && ataInput.value && (!ataDate || ataDate < shipmentPlanMinDate)) {
                    markError('bed_ata_input', 'ATA tidak boleh lebih lama dari 7 hari yang lalu');
                }
            }

            if (etaDate && eihDate && etaDate > eihDate) {
                markError('bed_eih_input', 'EIH tidak boleh lebih kecil dari ETA');
            }

            if (aihDate && ataDate && ataDate > aihDate) {
                markError('bed_ata_input', 'ATA tidak boleh melebihi AIH');
            }

            return { isValid, firstErrorId };
        }

        ['bed_eta_input', 'bed_eih_input', 'bed_ata_input'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;

            bulkEditDatesFlatpickrs[id] = flatpickr(`#${id}`, {
                dateFormat: 'd/m/Y',
                allowInput: true,
                clickOpens: false,
                minDate: shipmentPlanMinDate,
                onReady: function(selectedDates, dateStr, instance) {
                    const footer = document.createElement('div');
                    footer.style.cssText =
                        'display:flex; justify-content:space-between; padding:8px 12px; border-top:1px solid #eee;';

                    const todayBtn = document.createElement('a');
                    todayBtn.textContent = 'Today';
                    todayBtn.href = 'javascript:void(0)';
                    todayBtn.style.cssText = 'color:#359DD9; text-decoration:none; font-weight:600;';
                    todayBtn.addEventListener('click', function() {
                        instance.setDate(new Date(), true);
                    });

                    const closeBtn = document.createElement('a');
                    closeBtn.textContent = 'Close';
                    closeBtn.href = 'javascript:void(0)';
                    closeBtn.style.cssText = 'color:#000; text-decoration:none; font-weight:600;';
                    closeBtn.addEventListener('click', function() {
                        instance.close();
                    });

                    footer.appendChild(todayBtn);
                    footer.appendChild(closeBtn);
                    instance.calendarContainer.appendChild(footer);
                },
                onChange: function(selectedDates) {
                    const date = selectedDates[0];
                    if (id === 'bed_eta_input' && date && bulkEditDatesFlatpickrs.bed_eih_input) {
                        bulkEditDatesFlatpickrs.bed_eih_input.set('minDate', date);
                    }
                    if (id === 'bed_eih_input' && date && bulkEditDatesFlatpickrs.bed_eta_input) {
                        bulkEditDatesFlatpickrs.bed_eta_input.set('maxDate', date);
                    }
                    validateBulkEditDatesFields();
                },
            });
        });

        document.getElementById('bulkEditDatesModal')?.addEventListener('hidden.bs.modal', function() {
            resetBulkEditDatesErrors();
        });

        // document.getElementById('bulkEditDatesSaveBtn').addEventListener('click', function() {
        //     const dateCheck = validateBulkEditDatesFields();
        //     if (!dateCheck.isValid) {
        //         showToastShip('Ada kesalahan pada Shipment dates, silakan periksa kembali', 'error');
        //         scrollToShipmentPlanError(dateCheck.firstErrorId);
        //         return;
        //     }

        //     const etaVal = document.getElementById('bed_eta_input').value;
        //     const eihVal = document.getElementById('bed_eih_input').value;
        //     const ataVal = document.getElementById('bed_ata_input').value;

        //     const requests = bulkEditImportpks.map(importpk => {
        //         return fetch(`{{ route('exim.import.ship.view', ':id') }}`.replace(':id', importpk))
        //             .then(res => res.json())
        //             .then(res => {
        //                 const data = res.success ? res.data : {};

        //                 const payload = {
        //                     vassalpk: data.vassalpk,
        //                     vassal_name: data.vassal_name,
        //                     awb: data.awb_raw,
        //                     arrivalpk: data.arrivalpk,
        //                     portpk: data.portpk,
        //                     gw: data.gw_raw ?? 0,
        //                     dp: data.dp_raw ?? 0,
        //                     dl: data.dl_raw ?? 0,
        //                     dt: data.dt_raw ?? 0,
        //                     total_package: data.total_package_raw ?? 0,
        //                     etd: data.etd,
        //                     eta: etaVal,
        //                     eih: eihVal,
        //                     atd: data.atd,
        //                     ata: ataVal,
        //                     _token: '{{ csrf_token() }}',
        //                 };

        //                 return fetch(`{{ route('exim.import.ship.update', ':id') }}`.replace(':id', importpk), {
        //                     method: 'PUT',
        //                     headers: { 'Content-Type': 'application/json' },
        //                     body: JSON.stringify(payload),
        //                 }).then(r => r.json());
        //             });
        //     });

        //     Promise.all(requests)
        //         .then(results => {
        //             const failed = results.filter(r => !r.success);

        //             bulkEditDatesModalInstance.hide();

        //             document.querySelectorAll('.ship-check').forEach(cb => cb.checked = false);
        //             const checkAll = document.getElementById('check-all');
        //             if (checkAll) checkAll.checked = false;
        //             document.getElementById('myHeader').style.display = 'none';

        //             if (failed.length > 0) {
        //                 showToastShip(`${failed.length} item(s) failed to update`, 'error');
        //             } else {
        //                 showToastShip(`${bulkEditImportpks.length} item successful updated`);
        //             }

        //             fetchShips(new URLSearchParams(window.location.search), false);
        //         })
        //         .catch(err => {
        //             console.error(err);
        //             showToastShip('Failed to update shipment dates', 'error');
        //         });
        // });
        document.getElementById('bulkEditDatesSaveBtn').addEventListener('click', function() {
            const dateCheck = validateBulkEditDatesFields();
            if (!dateCheck.isValid) {
                showToastShip('Ada kesalahan pada Shipment dates, silakan periksa kembali', 'error');
                scrollToShipmentPlanError(dateCheck.firstErrorId);
                return;
            }

            const newValues = {
                eta: document.getElementById('bed_eta_input').value,
                eih: document.getElementById('bed_eih_input').value,
                ata: document.getElementById('bed_ata_input').value,
            };

            const changes = buildBulkDatesChanges(window.currentBulkEditOldValues || {}, newValues);

            if (!changes.length) {
                showToastShip('Tidak ada perubahan untuk disimpan', 'error');
                return;
            }

            renderConfirmChangesList(changes);

            // tampilkan info vassal & jumlah shipment khusus untuk bulk edit
            const infoEl = document.getElementById('confirmChangesInfo');
            infoEl.textContent = window.pendingBulkEditInfoText || '';
            infoEl.style.display = window.pendingBulkEditInfoText ? 'block' : 'none';

            pendingShipmentPayload = newValues;
            pendingShipmentSource = 'bulk';

            // sembunyikan dulu modal edit dates, tampilkan modal konfirmasi
            bulkEditDatesModalInstance?.hide();

            const modalEl = document.getElementById('confirmChangesModal');
            if (!confirmChangesModalInstance) {
                confirmChangesModalInstance = new bootstrap.Modal(modalEl);
            }
            confirmChangesModalInstance.show();
        });

        function checkSameOrEmpty(values) {
            const nonEmpty = values.map(v => (v || '').trim()).filter(v => v !== '');
            if (nonEmpty.length === 0) {
                return { ok: true, value: '' };
            }
            const unique = [...new Set(nonEmpty)];
            if (unique.length > 1) {
                return { ok: false, value: null };
            }
            return { ok: true, value: unique[0] };
        }

        function evaluateBulkEditEligibility(rows) {
            if (!rows.length) {
                return { eligible: false };
            }

            const vassalNames = rows.map(r => (r.dataset.vassalnm || '').trim());
            if (vassalNames.some(v => v === '' || v === '-')) {
                return { eligible: false };
            }

            const uniqueVassal = [...new Set(vassalNames)];
            if (uniqueVassal.length > 1) {
                return { eligible: false };
            }

            const etaCheck = checkSameOrEmpty(rows.map(r => r.dataset.eta));
            const eihCheck = checkSameOrEmpty(rows.map(r => r.dataset.eih));
            const ataCheck = checkSameOrEmpty(rows.map(r => r.dataset.ata));

            if (!etaCheck.ok || !eihCheck.ok || !ataCheck.ok) {
                return { eligible: false };
            }

            // AIH cuma ditampilkan kalau sama/kosong; kalau beda tetap boleh edit, tampilkan '-'
            const aihCheck = checkSameOrEmpty(rows.map(r => r.dataset.aih));

            return {
                eligible: true,
                vassal: uniqueVassal[0],
                eta: etaCheck.value,
                eih: eihCheck.value,
                ata: ataCheck.value,
                aih: aihCheck.ok ? aihCheck.value : '',
            };
        }

        function buildBulkDatesChanges(oldValues, newValues) {
            const changes = [];

            const norm = (v) => {
                if (v === null || v === undefined) return '';
                const s = String(v).trim();
                return (s === '-' || s === '') ? '' : s;
            };

            const etaOld = norm(oldValues.eta);
            const etaNew = norm(newValues.eta);
            if (etaOld !== etaNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `ETA changed from <b>${etaOld || '-'}</b> to <b>${etaNew || '-'}</b>`
                });
            }

            const eihOld = norm(oldValues.eih);
            const eihNew = norm(newValues.eih);
            if (eihOld !== eihNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `Estimate In-House changed from <b>${eihOld || '-'}</b> to <b>${eihNew || '-'}</b>`
                });
            }

            const ataOld = norm(oldValues.ata);
            const ataNew = norm(newValues.ata);
            if (ataOld !== ataNew) {
                changes.push({
                    section: 'Shipment dates',
                    text: `ATA changed from <b>${ataOld || '-'}</b> to <b>${ataNew || '-'}</b>`
                });
            }

            return changes;
        }
    </script>
@endsection

@section('content')

    @include('menu.exim-import.prt.header')
    @include('menu.exim-import.prt.filter-ship')
    @include('menu.exim-import.prt.sort-ship')
    @include('menu.exim-import.prt.table-ship')
    @include('menu.exim-import.prt.action-ship')
    @include('menu.exim-import.prt.modal-shipment-plan')
    @include('menu.exim-import.prt.modal-materials-list')
    @include('menu.exim-import.prt.modal-confirm-changes')
    @include('menu.exim-import.prt.modal-cancelling-shipment')

    <div class="contenta"></div>
@endsection
