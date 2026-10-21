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

        .sp-page {
            max-width: 1400px;
        }

        .sp-page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            margin-bottom: 20px;
        }

        .sp-eyebrow {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #98A2B3;
        }

        .sp-title {
            font-weight: 700;
            color: #101828;
            margin: 2px 0 0;
        }

        .sp-idship-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 2px;
        }

        .sp-idship-main {
            font-weight: 700;
            font-size: .82rem;
        }

        .sp-idship-pic {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: .68rem;
            color: #98A2B3;
            font-weight: 500;
            text-transform: uppercase;
        }

        .sp-idship-pic i {
            font-size: 10px;
        }

        /* tombol create (dipakai lagi dari komponen sebelumnya) */
        .btn-create-container {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 22px 10px 14px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            /* background: linear-gradient(135deg, #2F6FED 0%, #1E4FD1 100%); */
            background: linear-gradient(135deg, #2F6FED 0%, #212529 100%);
            box-shadow: 0 4px 14px rgba(47, 111, 237, .35);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-create-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 111, 237, .45);
        }

        .cc-icon-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cc-icon-box {
            font-size: 19px;
            color: #fff;
        }

        .cc-icon-plus {
            position: absolute;
            bottom: -4px;
            right: -4px;
            font-size: 15px;
            color: #34D399;
            background: #fff;
            border-radius: 50%;
            line-height: 1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            transition: transform .25s ease;
        }

        .btn-create-container:hover .cc-icon-plus {
            transform: scale(1.15) rotate(90deg);
        }

        .cc-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }

        .cc-title {
            font-weight: 700;
            font-size: .96rem;
            color: #fff;
        }

        .cc-subtitle {
            font-size: .74rem;
            color: rgba(255, 255, 255, .75);
        }

        .cc-arrow {
            font-size: 16px;
            color: rgba(255, 255, 255, .85);
            margin-left: 2px;
        }

        /* ================= STAT CARDS ================= */
        .sp-stats-row {
            display: flex;
            gap: 14px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .sp-stat-card {
            flex: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
            gap: 14px;
            background: #fff;
            border: 1px solid #EAECF0;
            border-radius: 14px;
            padding: 16px 18px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .05);
        }

        .sp-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }

        .sp-stat-blue {
            background: #EAF2FF;
            color: #2F6FED;
        }

        .sp-stat-orange {
            background: #FFF4E5;
            color: #B98900;
        }

        .sp-stat-green {
            background: #E9F9EE;
            color: #12A150;
        }

        .sp-stat-value {
            font-weight: 700;
            font-size: 1.3rem;
            color: #101828;
            line-height: 1.1;
        }

        .sp-stat-label {
            font-size: .78rem;
            color: #98A2B3;
            margin-top: 2px;
        }

        /* ================= TOOLBAR ================= */
        .sp-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* tombol aksi per PO (hapus PO / download packing list) */
        .sp-po-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .sp-po-action {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            font-size: 15px;
            line-height: 1;
            text-decoration: none !important;
            transition: background-color .15s;
        }

        .sp-po-action.text-danger:hover {
            background-color: rgba(220, 53, 69, .1);
        }

        .sp-po-action.text-primary:hover {
            background-color: rgba(13, 110, 253, .1);
        }

        .sp-po-action-badge {
            position: absolute;
            right: -2px;
            bottom: -2px;
            min-width: 12px;
            height: 12px;
            padding: 0 2px;
            border-radius: 6px;
            background: #0d6efd;
            color: #fff;
            font-size: 9px;
            font-weight: 600;
            line-height: 12px;
            text-align: center;
        }

        .sp-search-box {
            position: relative;
            /* flex: 1; */
            min-width: 240px;
        }

        .sp-search-box i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #98A2B3;
            font-size: .85rem;
        }

        .sp-search-box input {
            width: 300px;
            height: 42px;
            border: 1.5px solid #D0D5DD !important;
            border-radius: 10px;
            padding: 0 14px 0 36px;
            font-size: .87rem;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .sp-search-box input:focus {
            border-color: #2F6FED;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, .13);
        }

        .sp-filter-select+.textbox {
            height: 42px !important;
            border: 1.5px solid #D0D5DD !important;
            border-radius: 10px !important;
            background: #fff !important;
        }

        .sp-filter-select+.textbox .textbox-text {
            height: 40px !important;
            line-height: 40px !important;
            padding: 0 14px !important;
            font-size: .85rem !important;
            color: #344054 !important;
            border-radius: 10px !important;
            background: #fff !important;
        }

        .sp-filter-select+.textbox .textbox-addon {
            height: 40px !important;
        }

        .sp-filter-select+.textbox .textbox-addon .textbox-icon {
            height: 40px !important;
            line-height: 40px !important;
        }

        /*Combobox*/
        .sp-filter-select-cbb+.textbox {
            height: 42px !important;
            border: 1.5px solid #D0D5DD !important;
            border-radius: 10px !important;
            background: #fff !important;
        }

        .sp-filter-select-cbb+.textbox .textbox-text {
            height: 40px !important;
            line-height: 40px !important;
            padding: 0 14px !important;
            font-size: .85rem !important;
            border-radius: 10px !important;
            background: #fff !important;
        }

        .sp-filter-select-cbb+.textbox .textbox-addon {
            height: 40px !important;
        }

        .sp-filter-select-cbb+.textbox .textbox-addon .textbox-icon {
            height: 40px !important;
            line-height: 40px !important;
        }

        .sp-bulk-btn {
            height: 42px;
            padding: 0 16px;
            border-radius: 10px;
            border: none;
            background: #12A150;
            color: #fff;
            font-weight: 700;
            font-size: .84rem;
            display: flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(18, 161, 80, .3);
            transition: background .15s ease;
            animation: bulkIn .2s ease;
        }

        .sp-bulk-btn:hover {
            background: #0E8A44;
        }

        @keyframes bulkIn {
            from {
                opacity: 0;
                transform: scale(.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .sp-refresh-btn {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1.5px solid #D0D5DD;
            background: #fff;
            color: #475467;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease;
        }

        /* .sp-refresh-btn:hover {
                                        background: #F9FAFB;
                                    }

                                    .sp-refresh-btn:active i {
                                        transform: rotate(180deg);
                                    } */

        .sp-refresh-btn i {
            transition: transform .3s ease;
        }

        .sp-refresh-btn img {
            width: 30px;
            height: 30px;
            object-fit: contain;
            pointer-events: none;
            /* biar area klik tetap seluruh tombol, bukan cuma gambarnya */
        }

        .sp-refresh-btn:hover {
            background: #f3f6fb;
            border-color: #c7ccd4;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
        }

        .sp-refresh-btn:active {
            background: #e9edf3;
            transform: scale(0.96);
        }

        .sp-refresh-btn:focus-visible {
            outline: 2px solid #4c8bf5;
            outline-offset: 2px;
        }

        .sp-refresh-btn:disabled {
            opacity: .5;
            cursor: not-allowed;
            box-shadow: none;
        }

        .sp-grid-wrap {
            background: #fff;
            border: 1px solid #EAECF0;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .05);
        }

        .datagrid-row-selected {
            background: none !important;
            color: inherit !important;
        }

        .lines-no3 .datagrid-body td {
            border-right: 1px dotted transparent;
            border-bottom: 1px dotted transparent;
        }

        .datagrid-row:hover {
            background-color: transparent !important;
            /* hilangkan hover */
        }

        .datagrid-row-selected {
            background-color: transparent !important;
            /* warna tetap saat dipilih */
        }

        .datagrid-body td {
            vertical-align: center !important;
        }

        .datagrid-header .datagrid-cell {
            text-align: center !important;
        }

        .dg-empty-info {
            margin-top: 30px;
            text-align: center;
            color: #888;
            font-size: 14px;
        }

        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .expand-text {
            white-space: normal !important;
        }

        .sticky {
            position: fixed;
            top: 0;
            width: 100%;
        }

        .sticky+.contenta {
            padding-top: 102px;
        }

        /* selector pakai ID supaya menang atas `.header` (position:absolute; top:58px)
                                       yang dideklarasikan ulang di bawah blok .sticky ini */
        #myHeader2.sticky {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
        }

        /* ================= ISI SEL ================= */
        .sp-cell-ordno {
            color: #101828;
            font-size: .85rem;
        }

        .sp-cell-po {
            color: #101828;
            font-size: .85rem;
        }

        .sp-cell-buyer {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sp-buyer-avatar {
            width: 26px;
            height: 26px;
            border-radius: 7px;
            background: #EAF2FF;
            color: #2F6FED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: .7rem;
            flex-shrink: 0;
        }

        .sp-cell-date {
            font-size: .82rem;
            color: #344054;
        }

        .sp-date-empty {
            color: #C4C9D4;
            font-style: italic;
        }

        .sp-cell-mono {
            color: #101828;
            font-size: .85rem;
        }

        .sp-vassal-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #F1F2F4;
            color: #475467;
            font-size: .74rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
        }

        .sp-cell-num {
            font-weight: 700;
            color: #101828;
            font-size: .86rem;
        }

        .sp-cell-num-sub {
            font-size: .68rem;
            color: #98A2B3;
            margin-left: 3px;
        }

        .sp-checkbox {
            width: 17px;
            height: 17px;
            accent-color: #2F6FED;
            cursor: pointer;
        }

        /* ================= PAGINATION ================= */
        .datagrid-pager {
            background: #F9FAFB !important;
            border: none !important;
            border-top: 1.5px solid #EAECF0 !important;
            padding: 10px 14px !important;
        }

        @media (max-width: 768px) {
            .sp-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .sp-stats-row {
                flex-direction: column;
            }
        }

        /*menu dropdown*/
        .sp-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #667085;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .sp-action-btn:hover {
            background: #F1F2F4;
            color: #101828;
        }

        .sp-action-btn.active {
            background: #EAF2FF;
            color: #2F6FED;
        }

        .sp-action-menu {
            position: fixed;
            z-index: 1060;
            min-width: 190px;
            background: #fff;
            border: 1px solid #EAECF0;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(16, 24, 40, .18);
            padding: 8px;
            display: none;
            animation: spMenuIn .15s ease;
        }

        .sp-action-menu.show {
            display: block;
        }

        @keyframes spMenuIn {
            from {
                opacity: 0;
                transform: translateY(-4px) scale(.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .sp-action-item {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 10px;
            background: transparent;
            border: none;
            padding: 10px 12px;
            border-radius: 9px;
            font-size: .86rem;
            font-weight: 500;
            color: #344054;
            cursor: pointer;
            text-align: left;
            transition: background .12s ease;
        }

        .sp-action-disabled {
            background-color: #f2f4f7 !important;
            color: #98a2b3 !important;
            border-color: #d0d5dd !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }

        .sp-action-disabled i {
            color: #98a2b3 !important;
        }

        .sp-action-item:hover {
            background: #F5F8FF;
            color: #2F6FED;
        }

        .sp-action-disabled:hover {
            background-color: #f2f4f7 !important;
            color: #98a2b3 !important;
        }

        .sp-action-item i {
            font-size: 15px;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
            color: #667085;
        }

        .sp-action-item:hover i {
            color: #2F6FED;
        }

        .sp-action-item.sp-action-danger {
            color: #D92D20;
        }

        .sp-action-item.sp-action-danger i {
            color: #D92D20;
        }

        .sp-action-item.sp-action-danger:hover {
            background: #FEF3F2;
        }

        .sp-action-divider {
            height: 1px;
            background: #F1F2F4;
            margin: 6px 4px;
        }

        /*css untuk packing plan modal dan isinya*/
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

        #PackingPlanModal .modal-dialog {
            max-width: 95%;
            height: 95vh;
            margin: 1.75rem auto;
            margin-top: 10px;
        }

        #PackingPlanModal .modal-content {
            height: 100%;
        }

        #PackingPlanModal .modal-body {
            overflow: auto;
        }

        /*STYLE MODAL CONFIRM*/
        .cr-modal {
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(16, 24, 40, .25);
            animation: mcPopIn .25s cubic-bezier(.34, 1.5, .64, 1);
        }

        .cr-body {
            padding: 28px 26px 20px;
            text-align: center;
        }

        .cr-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #FEF3F2;
            color: #D92D20;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 14px;
        }

        .cr-title {
            font-weight: 700;
            font-size: 1.05rem;
            color: #101828;
            margin-bottom: 8px;
        }

        .cr-desc {
            font-size: .86rem;
            color: #667085;
            margin: 0;
            line-height: 1.5;
        }

        .cr-footer {
            display: flex;
            gap: 10px;
            padding: 16px 24px 22px;
        }

        .cr-btn {
            flex: 1;
            height: 42px;
            border-radius: 10px;
            border: 1.5px solid transparent;
            font-size: .87rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all .15s ease;
        }

        .cr-btn-ghost {
            background: #fff;
            border-color: #D0D5DD;
            color: #344054;
        }

        .cr-btn-ghost:hover {
            background: #F9FAFB;
        }

        .cr-btn-danger {
            background: #D92D20;
            color: #fff;
            box-shadow: 0 2px 8px rgba(217, 45, 32, .3);
        }

        .cr-btn-danger:hover {
            background: #B42318;
        }

        .cr-btn-danger:disabled {
            opacity: .6;
            cursor: not-allowed;
        }

        /* tambahan warna tombol konfirmasi dinamis, dipasang lewat JS via class */
        .cr-btn-publish-confirm {
            background: #12A150;
            color: #fff;
            box-shadow: 0 2px 8px rgba(18, 161, 80, .3);
        }

        .cr-btn-publish-confirm:hover {
            background: #0E8A44;
        }

        .cr-btn-unpublish-confirm {
            background: #F79009;
            color: #fff;
            box-shadow: 0 2px 8px rgba(247, 144, 9, .3);
        }

        .cr-btn-unpublish-confirm:hover {
            background: #DC7609;
        }


        .btn-update-shipment {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 22px 10px 14px;
            border: 1.5px solid #D0D5DD;
            border-radius: 14px;
            cursor: pointer;
            background: #fff;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06);
            transition: border-color .18s ease, background .18s ease, transform .18s ease;
        }

        .btn-update-shipment:hover {
            border-color: #2F6FED;
            background: #F9FBFF;
            transform: translateY(-1px);
        }

        .btn-update-shipment:active {
            transform: translateY(0) scale(.99);
        }

        .us-icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #EAF2FF;
            color: #2F6FED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            transition: background .18s ease;
        }

        .btn-update-shipment:hover .us-icon-wrap {
            background: #DCEBFF;
        }

        .us-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }

        .us-title {
            font-weight: 700;
            font-size: .96rem;
            color: #101828;
        }

        .us-subtitle {
            font-size: .74rem;
            color: #98A2B3;
        }

        .us-arrow {
            font-size: 16px;
            color: #98A2B3;
            margin-left: 2px;
            transition: transform .2s ease, color .2s ease;
        }

        .btn-update-shipment:hover .us-arrow {
            color: #2F6FED;
            transform: translateX(4px);
        }

        /*Toaster*/
        .ld-toast {
            border: none;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(16, 24, 40, .18);
            overflow: hidden;
            min-width: 320px;
        }

        .ld-toast-body {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 14px 14px 16px;
            background: #fff;
            border-left: 4px solid #12A150;
        }

        .ld-toast.ld-toast-error .ld-toast-body {
            border-left-color: #D92D20;
        }

        .ld-toast-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #E9F9EE;
            color: #12A150;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .ld-toast.ld-toast-error .ld-toast-icon {
            background: #FEF3F2;
            color: #D92D20;
        }

        .ld-toast-text {
            flex: 1;
            min-width: 0;
        }

        .ld-toast-title {
            font-weight: 700;
            font-size: .88rem;
            color: #101828;
        }

        .ld-toast-message {
            font-size: .8rem;
            color: #667085;
            margin-top: 2px;
        }

        .ld-toast-close {
            border: none;
            background: transparent;
            color: #98A2B3;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
            flex-shrink: 0;
            padding: 2px;
        }

        .ld-toast-close:hover {
            color: #475467;
        }

        .truncate-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .expand-text {
            white-space: normal !important;
        }

        /*Untuk badge status*/
        .sp-status-chip {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 1px;
            font-size: .74rem;
            font-weight: 700;
            padding: 5px 10px;
            border-radius: 12px;
            line-height: 1.3;
        }

        .sp-chip-main {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .sp-chip-main i {
            font-size: 11px;
        }

        .sp-chip-sub {
            font-weight: 600;
            font-size: .64rem;
            opacity: .8;
            padding-left: 16px;
            /* sejajar dengan teks label di atasnya, bukan icon */
        }

        .sp-status-published {
            background: #E9F9EE;
            color: #12A150;
        }

        .sp-status-draft {
            background: #FFF4E5;
            color: #B98900;
        }

        .sp-status-lock {
            background: #F1F2F4;
            color: #667085;
        }

        .sp-status-ship {
            background: #e6f1fc;
            color: #2563eb;
            cursor: pointer
        }

        .sp-status-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }

        .sp-status-chip-sm {
            font-size: .64rem;
            padding: 2px 8px;
        }

        .sp-status-chip-sm i {
            font-size: 9px;
        }

        .sp-status-lock {
            background: #F1F2F4;
            color: #667085;
        }

        .sp-status-session {
            background: #EAF2FF;
            color: #2F6FED;
        }

        .sp-status-session-complete {
            background: #E9F9EE;
            color: #12A150;
        }

        .shipid-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            margin-right: 6px;
        }

        .shipid-link:hover {
            text-decoration: underline;
        }

        .shipid-item {
            display: inline-block;
            margin: 3px 0;
        }

        /*untuk modal ship plan*/
        /* HEADER */
        .mc-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 24px 28px;
            background: linear-gradient(135deg, #2F6FED 0%, #1E4FD1 100%);
            position: relative;
        }

        .mc-header-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            flex-shrink: 0;
        }

        .mc-title {
            color: #fff;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
        }

        .mc-subtitle {
            color: rgba(255, 255, 255, .78);
            font-size: .82rem;
            margin: 2px 0 0;
        }

        .mc-close {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }

        .mc-close:hover {
            background: rgba(255, 255, 255, .28);
            transform: rotate(90deg);
        }

        /* TABS */
        .mc-tabs {
            position: relative;
            display: flex;
            gap: 4px;
            padding: 10px 20px 0;
            background: #fff;
            border-bottom: 1px solid #EAECF0;
        }

        .mc-tab {
            display: flex;
            align-items: center;
            gap: 7px;
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 10px 16px;
            font-size: .87rem;
            font-weight: 600;
            color: #98A2B3;
            border-radius: 10px 10px 0 0;
            transition: color .15s ease, background .15s ease;
        }

        .mc-tab:hover {
            color: #475467;
            background: #F9FAFB;
        }

        .mc-tab.active {
            color: #2F6FED;
        }

        .mc-tab-indicator {
            position: absolute;
            bottom: -1px;
            left: 20px;
            height: 2.5px;
            width: 148px;
            background: #2F6FED;
            border-radius: 2px;
            transition: transform .28s cubic-bezier(.65, 0, .35, 1), width .28s ease;
        }

        /* BODY / PANES */
        .mc-body {
            padding: 26px 28px 10px;
            min-height: 300px;
        }

        .mc-pane {
            display: none;
            animation: mcFadeSlide .25s ease;
        }

        .mc-pane.active {
            display: block;
        }

        @keyframes mcFadeSlide {
            from {
                opacity: 0;
                transform: translateX(8px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .mc-label {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: .85rem;
            font-weight: 600;
            color: #344054;
            margin-bottom: 6px;
        }

        .mc-label i {
            color: #2F6FED;
            font-size: .9rem;
        }

        .mc-input,
        textarea.mc-input {
            width: 100%;
            height: 42px;
            border: 1.5px solid #D0D5DD;
            border-radius: 10px;
            padding: 0 14px;
            font-size: .9rem;
            color: #101828;
            background: #fff;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        textarea.mc-input {
            height: auto;
            padding: 10px 14px;
            resize: none;
        }

        .mc-input:hover {
            border-color: #98A2B3;
        }

        .mc-input:focus {
            border-color: #2F6FED;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, .14);
        }

        .mc-input-suffix {
            position: relative;
        }

        .mc-input-suffix input {
            padding-right: 46px;
        }

        .mc-input-suffix span {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: .8rem;
            color: #98A2B3;
            font-weight: 600;
        }

        /* pilihan tipe container ala segmented control */
        .mc-choice-group {
            display: flex;
            gap: 8px;
        }

        .mc-choice {
            flex: 1;
            text-align: center;
            cursor: pointer;
            border: 1.5px solid #D0D5DD;
            border-radius: 10px;
            padding: 9px 6px;
            font-size: .85rem;
            font-weight: 600;
            color: #475467;
            transition: all .15s ease;
        }

        .mc-choice input {
            display: none;
        }

        .mc-choice:has(input:checked) {
            border-color: #2F6FED;
            background: #EAF2FF;
            color: #2F6FED;
        }

        /* preview gauge kapasitas */
        .mc-gauge-preview {
            background: #F9FAFB;
            border: 1px solid #EAECF0;
            border-radius: 12px;
            padding: 16px;
        }

        .mc-gauge-row {
            display: flex;
            justify-content: space-between;
            font-size: .8rem;
            color: #475467;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .mc-gauge-track {
            height: 9px;
            background: #E4E7EC;
            border-radius: 999px;
            overflow: hidden;
        }

        .mc-gauge-fill {
            height: 100%;
            background: #2F6FED;
            border-radius: 999px;
            transition: width .35s ease;
        }

        .mc-gauge-fill.fill-purple {
            background: #7C4DFF;
        }

        /* FOOTER */
        .mc-footer {
            padding: 16px 28px;
            border-top: 1px solid #EAECF0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .mc-step-dots {
            display: flex;
            gap: 6px;
        }

        .mc-step-dots .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #E4E7EC;
            transition: all .2s ease;
        }

        .mc-step-dots .dot.active {
            background: #2F6FED;
            width: 20px;
            border-radius: 4px;
        }

        .mc-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 40px;
            padding: 0 18px;
            border-radius: 10px;
            font-size: .87rem;
            font-weight: 600;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all .15s ease;
        }

        .mc-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .mc-btn-ghost {
            background: transparent;
            color: #667085;
        }

        .mc-btn-ghost:hover {
            background: #F9FAFB;
        }

        .mc-btn-outline {
            background: #fff;
            border-color: #D0D5DD;
            color: #344054;
        }

        .mc-btn-outline:hover:not(:disabled) {
            background: #F9FAFB;
            border-color: #98A2B3;
        }

        .mc-btn-primary {
            background: #2F6FED;
            color: #fff;
            box-shadow: 0 2px 8px rgba(47, 111, 237, .35);
        }

        .mc-btn-primary:hover {
            background: #1E4FD1;
        }

        .mc-btn-success {
            background: #12A150;
            color: #fff;
            box-shadow: 0 2px 8px rgba(18, 161, 80, .35);
        }

        .mc-btn-success:hover {
            background: #0E8A44;
        }

        /* ============ BAGIAN BARU: MULTI CONTAINER ============ */
        /* ============ BAGIAN BARU: MULTI CONTAINER ============ */
        .mc-pane-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .mc-pane-toolbar-text {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .82rem;
            color: #667085;
        }

        .mc-pane-toolbar-text i {
            color: #F79009;
        }

        .mc-count-pill {
            background: #EAF2FF;
            color: #2F6FED;
            font-weight: 700;
            font-size: .78rem;
            padding: 5px 12px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .container-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .container-item {
            position: relative;
            border: 1.5px solid #EAECF0;
            border-radius: 14px;
            padding: 18px 18px 16px;
            background: #FCFCFD;
            transition: border-color .18s ease, box-shadow .18s ease;
            animation: itemSlideIn .28s cubic-bezier(.34, 1.2, .64, 1);
        }

        .container-item:hover {
            border-color: #C7D7FE;
            box-shadow: 0 2px 10px rgba(47, 111, 237, .08);
        }

        .container-item.removing {
            animation: itemSlideOut .22s ease forwards;
        }

        .container-item-readonly {
            background: #F9FAFB;
        }

        .container-arrival-vessel {
            margin-left: auto;
        }

        .container-arrival-vessel::before {
            content: "Vessel: ";
            color: #98A2B3;
        }

        .container-foto-vessel {
            margin-left: auto;
        }

        .container-foto-vessel::before {
            content: "No. Container: ";
            color: #98A2B3;
        }


        @keyframes itemSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes itemSlideOut {
            to {
                opacity: 0;
                transform: translateX(24px) scale(.96);
                margin-top: -100px;
            }
        }

        .container-item-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .container-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: linear-gradient(135deg, #2F6FED, #1E4FD1);
            color: #fff;
            font-weight: 700;
            font-size: .8rem;
            padding: 6px 14px;
            border-radius: 999px;
        }

        .btn-remove-container {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1.5px solid #FEE4E2;
            background: #FEF3F2;
            color: #D92D20;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }

        .btn-remove-container:hover {
            background: #FEE4E2;
            transform: scale(1.06);
        }

        .btn-remove-container:disabled {
            opacity: .35;
            cursor: not-allowed;
            transform: none;
        }

        .btn-add-container {
            width: 100%;
            margin-top: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1.5px dashed #A9C3FA;
            border-radius: 12px;
            background: #F5F8FF;
            color: #2F6FED;
            font-weight: 700;
            font-size: .88rem;
            padding: 12px;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease, transform .1s ease;
        }

        .btn-add-container:hover {
            background: #EAF2FF;
            border-color: #2F6FED;
        }

        .btn-add-container:active {
            transform: scale(.99);
        }

        .mc-pane-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .mc-pane-toolbar-text {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: .82rem;
            color: #667085;
        }

        .mc-pane-toolbar-text i {
            color: #F79009;
        }

        .mc-count-pill {
            background: #EAF2FF;
            color: #2F6FED;
            font-weight: 700;
            font-size: .78rem;
            padding: 5px 12px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .sp-cell-po {
            display: flex;
            flex-direction: column;
            line-height: 1.3;
        }

        .sp-cell-invs {
            font-size: 11px;
            color: #667085;
            margin-top: 2px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .74rem;
            font-weight: 700;
            padding: 4px 11px;
            border-radius: 999px;
            white-space: nowrap;
        }

        .status-badge i {
            font-size: 11px;
        }

        .status-complete {
            background: #E9F9EE;
            color: #12A150;
        }

        .status-confirm {
            background: #EAF2FF;
            color: #2F6FED;
        }

        .status-plan {
            background: #FFF4E5;
            color: #B98900;
        }

        .status-cancel {
            background: #FDECEC;
            color: #D92D20;
        }

        .status-empty {
            background: #F1F2F4;
            color: #98A2B3;
            font-weight: 600;
        }

        /* Baris shipment yang sudah di-cancel: tampil abu-abu & tidak bisa dipilih */
        .sp-row-cancelled td {
            cursor: not-allowed;
            background: #FAFAFA !important;
            opacity: .55;
        }

        .sp-row-cancelled:hover td {
            border-color: #EAECF0 !important;
            background: #FAFAFA !important;
        }

        /* badge status & tombol aksi tetap terbaca jelas */
        .sp-row-cancelled td .status-badge,
        .sp-row-cancelled td .sp-action-btn {
            opacity: 1;
        }

        .sp-row-cancelled .datagrid-cell-check input[type=checkbox] {
            cursor: not-allowed;
            pointer-events: none;
        }

        /*css modal conformasi delete po*/
        .dpo-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(16, 24, 40, .25);
        }

        .dpo-body {
            padding: 28px 28px 8px;
            text-align: center;
        }

        .dpo-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: #FEE4E2;
            color: #D92D20;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 14px;
        }

        .dpo-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #101828;
            margin: 0 0 10px;
        }

        .dpo-po {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 999px;
            background: #F2F4F7;
            color: #344054;
            font-size: .85rem;
            margin-bottom: 16px;
        }

        .dpo-list {
            list-style: none;
            margin: 0 0 8px;
            padding: 12px 14px;
            text-align: left;
            background: #F9FAFB;
            border: 1px solid #EAECF0;
            border-radius: 10px;
        }

        .dpo-list li {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            font-size: .85rem;
            color: #475467;
            padding: 5px 0;
        }

        .dpo-list li i {
            color: #D92D20;
            margin-top: 2px;
        }

        .dpo-error {
            margin-top: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: .85rem;
            background: #FEF3F2;
            color: #B42318;
            border: 1px solid #FECDCA;
            text-align: left;
        }

        .dpo-footer {
            display: flex;
            gap: 10px;
            padding: 16px 28px 24px;
        }

        .dpo-btn {
            flex: 1;
            height: 42px;
            border-radius: 10px;
            font-weight: 600;
            font-size: .9rem;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: background .15s, box-shadow .15s;
        }

        .dpo-btn-ghost {
            background: #fff;
            color: #344054;
            border-color: #D0D5DD;
        }

        .dpo-btn-ghost:hover {
            background: #F9FAFB;
        }

        .dpo-btn-danger {
            background: #D92D20;
            color: #fff;
        }

        .dpo-btn-danger:hover {
            background: #B42318;
        }

        .dpo-btn:disabled {
            opacity: .65;
            cursor: not-allowed;
        }

        .dpo-btn:focus-visible {
            outline: 0;
            box-shadow: 0 0 0 4px rgba(217, 45, 32, .2);
        }
    </style>
@endsection

@section('js_custom')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // cek toast dari sessionStorage (kalau sudah ada dari fitur sebelumnya)
            const msg = sessionStorage.getItem('ldToastMessage');
            if (msg) {
                showToast(msg, sessionStorage.getItem('ldToastType') || 'success');
                sessionStorage.removeItem('ldToastMessage');
                sessionStorage.removeItem('ldToastType');
            }

            // cek query param untuk auto-buka modal
            const params = new URLSearchParams(window.location.search);
            if (params.get('open_modal') === 'container') {
                openEditShipmentModal();
            }
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        function myformatter(date) {
            var d = new Date(date || Date.now()),
                month = '' + (d.getMonth() + 1),
                day = '' + d.getDate(),
                year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [day, month, year].join('/');
        }

        function myparser(s) {
            if (!s) return new Date();
            var ss = (s.split('/'));
            var y = parseInt(ss[0], 10);
            var m = parseInt(ss[1], 10);
            var d = parseInt(ss[2], 10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                return new Date(d, m - 1, y);
            } else {
                return new Date();
            }
        }

        function myformatter2(date) {
            if (!date) {
                date = new Date();
            }

            var d = new Date(date);

            var month = '' + (d.getMonth() + 1);
            var day = '' + d.getDate();
            var year = d.getFullYear();

            var hour = '' + d.getHours();
            var minute = '' + d.getMinutes();

            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            if (hour.length < 2) hour = '0' + hour;
            if (minute.length < 2) minute = '0' + minute;

            return day + '/' + month + '/' + year + ' ' + hour + ':' + minute;
        }

        function myparser2(s) {
            if (!s) {
                return new Date();
            }

            // Format: dd/mm/yyyy HH:mm
            var parts = s.split(' ');

            var datePart = parts[0];
            var timePart = parts[1] || '00:00';

            var dateParts = datePart.split('/');
            var timeParts = timePart.split(':');

            var day = parseInt(dateParts[0], 10);
            var month = parseInt(dateParts[1], 10);
            var year = parseInt(dateParts[2], 10);

            var hour = parseInt(timeParts[0], 10) || 0;
            var minute = parseInt(timeParts[1], 10) || 0;

            if (
                !isNaN(day) &&
                !isNaN(month) &&
                !isNaN(year)
            ) {
                return new Date(
                    year,
                    month - 1,
                    day,
                    hour,
                    minute
                );
            }

            return new Date();
        }

        function myparser3(s) {
            if (!s) {
                const now = new Date();

                // Default jam 17:00
                now.setHours(17, 0, 0, 0);

                return now;
            }

            var parts = s.split(' ');
            var dateParts = parts[0].split('/');

            var day = parseInt(dateParts[0], 10);
            var month = parseInt(dateParts[1], 10);
            var year = parseInt(dateParts[2], 10);

            // Kalau jam tidak ada, default 17:00
            var hour = 17;
            var minute = 0;

            if (parts[1]) {
                var timeParts = parts[1].split(':');

                hour = parseInt(timeParts[0], 10);
                minute = parseInt(timeParts[1], 10) || 0;
            }

            return new Date(
                year,
                month - 1,
                day,
                hour,
                minute
            );
        }

        //Toaster
        // helper toast, taruh di dalam IIFE yang sama
        const toastEl = document.getElementById('ldToast');
        const bsToast = new bootstrap.Toast(toastEl, {
            delay: 3500
        });

        (function() {

            function onLoadGrid() {
                var dg = $('#dgShipmentPlan');
                var rows = dg.datagrid('getRows');

                console.log('rows:', rows);

                if (!rows || rows.length === 0) return;

                // 1. bangun data merges
                var merges = buildMerges(rows);
                console.log('merges:', merges);

                if (merges.length === 0) return;

                // 2. kolom yang mau di-merge
                var fields = ['exportpk', 'idship', 'contdate', 'GAC', 'exdate', 'buyer', 'vassal', 'exdate', 'status',
                    'action', 'type'
                ];

                // 3. eksekusi merge
                merges.forEach(function(m) {
                    fields.forEach(function(f) {
                        dg.datagrid('mergeCells', {
                            index: m.index,
                            field: f,
                            rowspan: m.rowspan
                        });
                    });
                });
            }

            function norm(v) {
                return (v ?? '').toString().trim();
            }

            function buildMerges(rows) {
                var merges = [];
                var start = 0;

                for (var i = 1; i <= rows.length; i++) {
                    if (
                        i === rows.length ||
                        norm(rows[i].exportpk) !== norm(rows[start].exportpk) ||
                        norm(rows[i].idship) !== norm(rows[start].idship) ||
                        norm(rows[i].contdate) !== norm(rows[start].contdate) ||
                        norm(rows[i].GAC) !== norm(rows[start].GAC) ||
                        norm(rows[i].exdate) !== norm(rows[start].exdate) ||
                        norm(rows[i].buyer) !== norm(rows[start].buyer) ||
                        norm(rows[i].vassal) !== norm(rows[start].vassal) ||
                        norm(rows[i].exdate) !== norm(rows[start].exdate) ||
                        norm(rows[i].status) !== norm(rows[start].status) ||
                        norm(rows[i].type) !== norm(rows[start].type) ||
                        norm(rows[i].action) !== norm(rows[start].action)
                    ) {
                        var rowspan = i - start;
                        if (rowspan > 1) {
                            merges.push({
                                index: start,
                                rowspan: rowspan
                            });
                        }
                        start = i;
                    }
                }
                return merges;
            }

            let selectedPlanIds = new Set();

            function isRowCancelled(row) {
                const guserpk = {{ (int) Session::get('guserpk', 0) }};
                if (!row) {
                    return false;
                }
                return row.stscancel == 1 || row.guserpk != guserpk;
            }

            // Checkbox baris cancelled di-disable setelah render supaya benar-benar
            // tidak bisa diklik, bukan cuma diblokir lewat onBeforeCheck.
            function disableCancelledCheckboxes(dg) {
                dg.datagrid('getRows').forEach(function(row, index) {
                    if (!isRowCancelled(row)) return;

                    dg.datagrid('getPanel')
                        .find('div.datagrid-body table.datagrid-btable tr[datagrid-row-index="' +
                            index + '"]')
                        .find('input[type="checkbox"]')
                        .prop('disabled', true);
                });
            }

            function initGrid() {
                $('#dgShipmentPlan').datagrid({
                    url: "{{ route('shipplan.getListExport') }}", // sesuaikan dengan route controller kamu nanti
                    width: '100%',
                    height: 'auto',
                    fitColumns: true, // WAJIB true untuk kasus ini
                    rownumbers: false,
                    singleSelect: false,
                    showHeader: true,
                    striped: true,
                    border: false,
                    pagination: true,
                    pageSize: 100,
                    pageList: [100, 200, 300, 400, 500],
                    method: 'get',
                    columns: [
                        [{
                                field: 'ck',
                                title: '',
                                width: 50,
                                checkbox: 'true'
                            },
                            {
                                field: 'po_count',
                                title: '',
                                width: 80,
                                formatter: function(v, row) {
                                    let html = '';

                                    if (v > 1 && row.stscancel < 1) {
                                        html += `<a href="javascript:void(0)" class="sp-po-action text-danger"`
                                            + ` data-bs-toggle="tooltip" title="Delete PO number"`
                                            + ` onclick="delete_po(${row.poexportpk}, '${(row.POno || '').replace(/'/g, "\\'")}')">`
                                            + `<i class="bi bi-trash"></i></a>`;
                                    }

                                    // row.po_mif: daftar po.mif (mysql_andon) PO ini, mis. [2] / [1, 2] -> 1 tombol per MIF
                                    const mifs = Array.isArray(row.po_mif) && row.po_mif.length ? row.po_mif : [2];
                                    mifs.forEach(mif => {
                                        const label = mifs.length > 1 ? `<span class="sp-po-action-badge">${mif}</span>` : '';
                                        html += `<a href="${packingListPdfUrl(row, mif)}" target="_blank" rel="noopener" class="sp-po-action text-primary"`
                                            + ` data-bs-toggle="tooltip" title="Download Packing List"`
                                            + ` onclick="event.stopPropagation()">`
                                            + `<i class="bi bi-file-earmark-pdf"></i>${label}</a>`;
                                    });

                                    return `<div class="sp-po-actions">${html}</div>`;
                                }
                            },
                            // {
                            //     field: 'exportpk',
                            //     title: '',
                            //     width: 50,
                            //     formatter: function(v, row) {
                            //         if (row.stscancel < 1) {
                            //              return `<a href="javascript:void(0)" class="text-success"
                            //                 data-bs-toggle="tooltip"
                            //                 title="Add PO number"
                            //                 onclick="add_po(${row.exportpk}, '${(row.buyer || '').replace(/'/g, "\\'")}')">
                            //                 <i class="bi bi-plus-square"></i></a>`;
                            //         }
                            //     }
                            // },
                            {
                                field: 'ordno',
                                title: 'OP/Style',
                                width: 250,
                                align: 'center',
                                formatter: formatOrderInfo2,
                            },
                            {
                                field: 'idship',
                                title: 'ID Shipplan<br>/PIC',
                                align: 'center',
                                width: 130,
                                formatter: function(v, row) {
                                    const idShip = v ? escapeHtml(v) : '-';
                                    const pic = row.login ? escapeHtml(row.login) : '-';
                                    const guserpk = {{ (int) Session::get('guserpk', 0) }};

                                    return `
                                        <div class="sp-idship-stack justify-content-center align-items-center">
                                            <span class="sp-cell-mono sp-idship-main">${idShip}</span>
                                            ${
                                                guserpk === 7
                                                    ? `<span class="sp-idship-pic">
                                                                                        <i class="bi bi-person-fill"></i> ${pic}
                                                                                </span>`
                                                    : ''
                                            }
                                        </div>
                                    `;
                                },
                            },
                            {
                                field: 'contdate',
                                title: 'Container<br>Arrival',
                                width: 120,
                                formatter: v => v ? `<span class="sp-cell-date">${formatDate(v)}</span>` :
                                    `<span class="sp-cell-date sp-date-empty">-</span>`,
                            },
                            {
                                field: 'extdate',
                                title: 'Actual<br>Ex-Factory',
                                width: 120,
                                formatter: v => v ? `<span class="sp-cell-date">${formatDate(v)}</span>` :
                                    `<span class="sp-cell-date sp-date-empty">-</span>`,
                            },
                            {
                                field: 'GAC',
                                title: 'Buyer<br>Ex-Factory',
                                width: 120,
                                formatter: v => v ? `<span class="sp-cell-date">${formatDate(v)}</span>` :
                                    `<span class="sp-cell-date sp-date-empty">-</span>`,
                            },
                            // {
                            //     field: 'buyer',
                            //     title: 'Buyer',
                            //     width: 170,
                            //     formatter: function(v) {
                            //         const initials = (v || '-').substring(0, 2).toUpperCase();
                            //         return `<div class="sp-cell-buyer"><span class="sp-buyer-avatar">${initials}</span><span>${escapeHtml(v || '-')}</span></div>`;
                            //     },
                            // },
                            // {
                            //     field: 'POno',
                            //     title: 'Nomor PO/<br>Invs No',
                            //     width: 200,
                            //     align: 'center',
                            //     formatter: function(v, row) {
                            //         return `
                        //             <div class="sp-cell-po">
                        //                 <span>${escapeHtml(v || '-')}</span>
                        //                 <small class="sp-cell-invs">
                        //                     ${escapeHtml(row.invs || '-')}
                        //                 </small>
                        //             </div>
                        //         `;
                            //     },
                            // },
                            {
                                field: 'qty',
                                title: 'Qty<br>(PCS)',
                                width: 100,
                                align: 'center',
                                formatter: formatQty,
                            },
                            {
                                field: 'pebno',
                                title: 'PEB No',
                                width: 130,
                                formatter: v =>
                                    `<span class="sp-cell-mono">${v ? escapeHtml(v) : '-'}</span>`,
                            },
                            {
                                field: 'type',
                                title: 'Container<br>Type',
                                align: 'center',
                                width: 150,
                                formatter: v => formatContainerTypeCell(v),
                            },
                            {
                                field: 'vassal',
                                title: 'Shipment<br>Method',
                                align: 'center',
                                width: 140,
                                formatter: v => {
                                    if (!v) return '-';

                                    let icon = 'bi-box2-fill';

                                    if (v.toLowerCase() === 'by air') {
                                        icon = 'bi-airplane-engines-fill';
                                    } else if (v.toLowerCase() === 'by courier') {
                                        icon = 'bi-truck';
                                    } else if (v.toLowerCase() === 'by sea') {
                                        icon = 'bi-water';
                                    }

                                    return `<span class="sp-vassal-chip">
                                            <i class="bi ${icon}"></i> ${escapeHtml(v)}
                                            </span>`;
                                },
                            },
                            {
                                field: 'mif',
                                title: 'Factory',
                                width: 100,
                                align: 'center',
                                formatter: function(v) {
                                    return `<div class="sp-cell-buyer"><span>${escapeHtml( v || '-')}</span></div>`;
                                },
                            },
                            {
                                field: 'status',
                                title: 'Status',
                                width: 150,
                                align: 'center',
                                formatter: function(v, row, index) {

                                    const plan =
                                        row.condate ||
                                        row.etdvess ||
                                        row.vassalpk;

                                    const confirm = row.exdate;
                                    const complete = row.actexdate;

                                    // Cancel adalah status terminal, dicek paling awal.
                                    if (row.stscancel == 1) {
                                        return `<span class="status-badge status-cancel"><i class="bi bi-x-octagon-fill"></i> Cancelled</span>`;
                                    }

                                    if (row.is_segel == 1) {
                                        return `<span class="status-badge status-complete"><i class="bi bi-check-circle-fill"></i> Complete</span>`;
                                    }

                                    if (confirm) {
                                        return `<span class="status-badge status-confirm"><i class="bi bi-hourglass-split"></i> Confirm</span>`;
                                    }

                                    if (plan) {
                                        return `<span class="status-badge status-plan"><i class="bi bi-pencil-fill"></i> Plan</span>`;
                                    }

                                    return `<span class="status-badge status-empty">-</span>`;
                                },
                            },
                            {
                                field: 'action',
                                title: '',
                                width: 50,
                                align: 'center',
                                formatter: function(val, row) {
                                    return `
                                    <button type="button" class="sp-action-btn pe-4" onclick="event.stopPropagation(); toggleActionMenu(event, ${row.exportpk}, ${row.stsexp}, ${row.lockship}, '${row.endship}', ${row.stscancel}, ${row.stsstart}, '${row.buyer}')">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                `;
                                },
                            },
                        ]
                    ],
                    rowStyler: function(index, row) {
                        // WAJIB bentuk objek: rowStyler yang mengembalikan string
                        // diperlakukan EasyUI sebagai inline style, bukan class.
                        return isRowCancelled(row) ? {
                            'class': 'sp-row-cancelled'
                        } : '';
                    },
                    onBeforeCheck: function(index, row) {
                        return !isRowCancelled(row);
                    },
                    onBeforeSelect: function(index, row) {
                        return !isRowCancelled(row);
                    },
                    onLoadSuccess: function(data) {
                        onLoadGrid();
                        disableCancelledCheckboxes($(this));
                        updateStats(data.rows || []);
                        updateBulkButton();
                        fixHeaderAlignment(); // panggil setelah data ter-render
                    },
                    onCheck: function() {
                        menu2();
                    },
                    onCheckAll: function(rows) {
                        // checkAll EasyUI mem-bypass onBeforeCheck, jadi baris
                        // cancelled harus dilepas kembali secara manual.
                        const dg = $(this);
                        rows.forEach(function(row, index) {
                            if (isRowCancelled(row)) dg.datagrid('uncheckRow', index);
                        });
                        menu2();
                    },
                    onUncheck: function() {
                        menu2();
                    },
                    onUncheckAll: function() {
                        menu2();
                    },

                    onResize: fixHeaderAlignment
                });
            }

            function formatOrderInfo2(value, row) {
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
                        <div class="cs-main">${row.OP ?? '-'}</div>
                        <div class="cs-sub">${row.buyer ?? '-'}</div>
                        <div class="cs-sub">PO/Style : ${row.POno ? row.POno : row.style}</div>
                        <div class="cs-sub">POref : ${row.poref ?? '-'}</div>
                        <div class="cs-sub"><b>${row.invs ? 'INV : '+row.invs : 'INV : -'}</b></div>
                    </div>
                </div>
            `;
            }

            function formatPO(value, row, index) {
                if (!value) return '';

                return value
                    .toString()
                    .split(',')
                    .map(item => item.trim())
                    .join('<br>');
            }

            //formatter container
            function containerTypeIcon(sizeLabel) {
                const isForty = /40/.test(String(sizeLabel));
                const w = isForty ? 26 : 15; // 40FT digambar ~2x lebar 20FT
                const ridgeCount = Math.max(1, Math.floor(w / 5));
                const ridges = Array.from({
                    length: ridgeCount
                }, (_, i) => {
                    const x = ((i + 1) * w) / (ridgeCount + 1);
                    return `<line x1="${x.toFixed(1)}" y1="1.5" x2="${x.toFixed(1)}" y2="11.5" stroke="currentColor" stroke-width="0.6" stroke-opacity="0.45"/>`;
                }).join('');

                return `
                    <svg width="${w}" height="13" viewBox="0 0 ${w} 13" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;display:block;">
                        <rect x="0.5" y="0.5" width="${w - 1}" height="12" rx="1.5"
                            fill="currentColor" fill-opacity="0.12"
                            stroke="currentColor" stroke-width="1"/>
                        ${ridges}
                    </svg>
                `;
            }

            function formatContainerTypeCell(v) {
                if (v === null || v === undefined || v === '') return '-';

                // Normalisasi: v bisa array, atau string digabung koma/titik-koma
                const rawList = Array.isArray(v) ?
                    v :
                    String(v).split(/[,;]+/).map(s => s.trim()).filter(Boolean);

                if (!rawList.length) return '-';

                // Hitung jumlah tiap tipe container, urutan kemunculan pertama dipertahankan
                const counts = new Map();
                rawList.forEach(type => {
                    counts.set(type, (counts.get(type) || 0) + 1);
                });

                const chips = Array.from(counts.entries()).map(([type, count]) => {
                    const label = escapeHtml(type);
                    const countBadge = count > 1 ?
                        `<span style="opacity:.6;font-weight:600;margin-left:2px;">x${count}</span>` :
                        '';

                    return `
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:1px 6px;border-radius:5px;background:rgba(0,0,0,.045);margin:1px 3px 1px 0;font-size:.78rem;line-height:1.6;"
                            title="${label}${count > 1 ? ` (${count} unit)` : ''}">
                            ${containerTypeIcon(type)}
                            <span>${label}</span>${countBadge}
                        </span>
                    `;
                }).join('');

                return `<span class="sp-cell-mono" style="display:inline-flex;flex-wrap:wrap;align-items:center;">${chips}</span>`;
            }

            // paksa header & body pakai lebar kolom yang identik, kompensasi lebar scrollbar
            function fixHeaderAlignment() {
                setTimeout(function() {
                    const panel = $('#dgShipmentPlan').datagrid('getPanel');
                    const bodyEl = panel.find('.datagrid-body')[0];
                    if (!bodyEl) return;

                    const scrollbarWidth = bodyEl.offsetWidth - bodyEl.clientWidth;
                    panel.find('.datagrid-header-row').css('margin-right', scrollbarWidth + 'px');
                }, 0);
            }

            window.toggleSpRow = function(exportpk) {
                const id = String(exportpk);
                if (selectedPlanIds.has(id)) selectedPlanIds.delete(id);
                else selectedPlanIds.add(id);
                updateBulkButton();
            };

            function updateBulkButton() {
                const btn = document.getElementById('btnBulkAction');
                const countEl = document.getElementById('bulkCount');
                countEl.textContent = selectedPlanIds.size;
                btn.classList.toggle('d-none', selectedPlanIds.size === 0);
            }

            function updateStats(rows) {
                const uniqueExportpk = new Set(rows.map(r => r.exportpk));

                document.getElementById('statTotalPlan').textContent = uniqueExportpk.size;
                document.getElementById('statTotalCtn').textContent = numberFormat(rows.reduce((s, r) => s + (Number(r
                    .totctn) || 0), 0));
                document.getElementById('statTotalQty').textContent = numberFormat(rows.reduce((s, r) => s + (Number(r
                    .qty) || 0), 0));
            }

            // PDF packing list per PO dari aplikasi finishing-packing
            // (lewat proxy EXIM, tidak perlu login di finishing-packing)
            window.packingListPdfUrl = function(row, mif) {
                return '{{ route('export.packing-list-pdf') }}?' + $.param({
                    po: row.POno || '',
                    op: row.OP || '',
                    poref: row.poref || '',
                    mif: mif,
                });
            };

            window.reloadSpGrid = function() {
                $('#dgShipmentPlan').datagrid('load', {
                    q: document.getElementById('spSearch').value || '',
                    buyer: document.getElementById('spFilterBuyer').value || '',
                    vassal: document.getElementById('spFilterVassal').value || '',
                    sortlistByDate: document.getElementById('sortlistByDate').value || '',
                    status: document.getElementById('spFilterStatus').value || '',
                    searchByRangeDay: document.getElementById('filterday').value || '',
                });
            };

            let searchTimer = null;
            window.onSpSearch = function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(reloadSpGrid, 350);
            };

            /*export LIST SHipment*/
            window.exportSpExcel = function() {
                const params = {
                    q: document.getElementById('spSearch').value || '',
                    buyer: document.getElementById('spFilterBuyer').value || '',
                    vassal: document.getElementById('spFilterVassal').value || '',
                    sortlistByDate: document.getElementById('sortlistByDate').value || '',
                    status: document.getElementById('spFilterStatus').value || '',
                    searchByRangeDay: document.getElementById('filterday').value || '',
                };

                window.location.href = '{{ route('export.excel') }}?' + $.param(params);
            };

            window.onBulkAction = function() {
                if (!selectedPlanIds.size) return;
                console.log('Export terpilih:', [...selectedPlanIds]);
                // TODO: arahkan ke aksi bulk sesuai kebutuhan (export excel, print, dsb.)
            };

            function escapeHtml(str) {
                return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            function numberFormat(n) {
                return new Intl.NumberFormat('id-ID').format(Number(n) || 0);
            }

            initGrid();

            //Untuk menu drodown tabel
            let activeActionRow = null;
            let activeActionBuyer = null;
            let activeActionBtn = null;

            window.toggleActionMenu = function(event, exportpk, stsexp, lockship, endship, stscancel, stsstart, buyer) {
                const btn = event.currentTarget;
                const menu = document.getElementById('spActionMenu');

                // Shipment yang sudah di-cancel bersifat read-only: hanya boleh
                // dilihat / di-export, tidak bisa publish, lock, atau di-cancel lagi.
                const isCancelled = stscancel == 1;
                $('#spActionMenu .sp-action-divider').toggleClass('d-none', isCancelled);
                $('#remove').toggleClass('d-none', isCancelled);
                $('#menu_add_pono').toggleClass('d-none', isCancelled);
                $('#cancelled').toggleClass('d-none', !isCancelled);

                if (isCancelled) {
                    $('#menu_publish').addClass('d-none');
                    $('#menu_unpublish').addClass('d-none');
                    $('#locked').addClass('d-none');
                    $('#unlocked').addClass('d-none');
                    $('#complete_shipment').addClass('d-none');
                } else if (stsexp == 1) {
                    $('#menu_publish').addClass('d-none');
                    $('#menu_unpublish').addClass('d-none');
                    if (lockship == 1) {
                        // Locked
                        $('#locked').removeClass('d-none');
                        $('#unlocked').addClass('d-none');
                        $('#menu_unpublish').addClass('d-none');

                    } else if (lockship == 'null' && endship == 'null') {
                        // Belum ada lock dan belum ada endship
                        $('#locked').addClass('d-none');
                        $('#unlocked').addClass('d-none');

                    } else if (endship != 'null' && String(endship).trim() !== '') {
                        // Sudah ada endship, tapi belum locked
                        $('#locked').addClass('d-none');
                        $('#unlocked').addClass('d-none');
                    } else {
                        // Default
                        $('#locked').addClass('d-none');
                        $('#unlocked').addClass('d-none');
                    }
                } else {
                    if (stsstart == 1) {
                        $('#complete_shipment').addClass('d-none');
                    } else {
                        $('#complete_shipment').addClass('d-none');
                    }

                    // Belum publish
                    $('#menu_publish').addClass('d-none');
                    $('#menu_unpublish').addClass('d-none');
                    $('#locked').addClass('d-none');
                    $('#unlocked').addClass('d-none');
                }

                // kalau klik tombol yang sama saat menu sudah terbuka -> tutup
                if (activeActionBtn === btn && menu.classList.contains('show')) {
                    closeActionMenu();
                    return;
                }

                activeActionRow = exportpk;
                activeActionBuyer = buyer;
                activeActionBtn = btn;

                // hitung posisi tombol, tampilkan menu tepat di bawahnya (rata kanan)
                const rect = btn.getBoundingClientRect();
                const menuWidth = 200;

                let left = rect.right - menuWidth;
                if (left < 8) left = 8; // jaga jangan sampai keluar layar kiri

                let top = rect.bottom + 6;
                // kalau menu bakal kepotong di bawah viewport, tampilkan di ATAS tombol
                const menuHeightEstimate = 180;
                if (top + menuHeightEstimate > window.innerHeight) {
                    top = rect.top - menuHeightEstimate - 6;
                }

                menu.style.left = left + 'px';
                menu.style.top = top + 'px';
                menu.classList.add('show');
                btn.classList.add('active');
            };

            function closeActionMenu() {
                const menu = document.getElementById('spActionMenu');
                menu.classList.remove('show');
                if (activeActionBtn) activeActionBtn.classList.remove('active');
                activeActionBtn = null;
                activeActionRow = null;
                activeActionBuyer = null;
            }

            window.onActionClick = function(action) {

                const exportpk = activeActionRow;
                const buyer = activeActionBuyer;

                closeActionMenu();

                if (!exportpk) return;

                switch (action) {
                    case 'view':
                        window.location.href = `{{ url('exim-export/shipplan/detail_ship') }}/${exportpk}`;
                        break;
                    case 'export_excel':
                        window.open(`{{ url('/export') }}/${exportpk}/print`, '_blank');
                        break;
                    case 'export_si':
                        window.open(`{{ url('/export') }}/si/${exportpk}`, '_blank');
                        break;
                    case 'add_pono':
                        add_po(exportpk, buyer);
                        break;
                    case 'remove':
                        removeListShipplan(exportpk);
                        break;
                    case 'publish':
                        publish(exportpk);
                        break;
                    case 'unpublish':
                        unpublish(exportpk);
                        break;
                    case 'lock':
                        lockship(exportpk);
                        break;
                    case 'complete_shipment':
                        openEditShipmentModal(exportpk, 'schedule', true);
                        break;
                }
            };

            // tutup menu saat klik di luar panel/tombol manapun
            document.addEventListener('click', function(e) {
                const menu = document.getElementById('spActionMenu');
                if (!menu.contains(e.target) && !e.target.closest('.sp-action-btn')) {
                    closeActionMenu();
                }
            });

            // tutup menu juga saat scroll tabel atau resize window (posisi jadi tidak valid)
            document.querySelector('.datagrid-body')?.addEventListener('scroll', closeActionMenu);
            window.addEventListener('resize', closeActionMenu);

            function confirmRemoveShipmentPlan(exportpk) {
                // TODO: tampilkan modal konfirmasi (pola sama seperti modalConfirmRemove sebelumnya),
                // lalu fetch DELETE ke endpoint remove shipment plan
                console.log('Remove shipment plan:', exportpk);
            }
        })();

        function openPackingPlanModal() {
            $('#PackingPlanModal').modal('show');

            $('#PackingPlanModal').on('shown.bs.modal', function() {
                $('#dgOrder').datagrid('resize');
                $('#dgOrder').datagrid('clearSelections');
                $('#dgOrder').datagrid('clearChecked');
            });
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

        // function formatDate(dateStr) {
        //     const d = new Date(dateStr);
        //     if (isNaN(d)) return dateStr;
        //     return d.toLocaleDateString('id-ID', {
        //         day: '2-digit',
        //         month: 'short',
        //         year: 'numeric'
        //     });
        // }

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

        function formatShipID(value, row, index) {
            if (!value) {
                return '-';
            }

            const shipids = String(value)
                .split(',')
                .map(id => id.trim())
                .filter(id => id !== '');

            return shipids.map(shipid => {
                const shipno = 'SP' + String(shipid).padStart(4, '0');

                return `
                <span class="sp-status-chip sp-status-ship shipid-item">
                    <a href="javascript:void(0);"
                    class="shipid-link"
                    onclick="window.location.href='{{ url('/exim-export/shipplan/detail_ship') }}/${shipid}'">
                        ${shipno}
                    </a>
                </span>
            `;
            }).join('<br>');
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

        function formatQty(value, row) {
            if (!value) return '';

            return value
                .toString()
                .split(',')
                .map(item => {
                    item = item.trim();

                    // format angka dengan pemisah ribuan titik
                    if (!isNaN(item) && item !== '') {
                        return Number(item).toLocaleString('id-ID');
                    }

                    return item;
                })
                .join('<br>');
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

        function joinUnique(arr) {
            return [...new Set(arr.filter(Boolean))].join(',');
        }

        let pendingShipmentData = null; // simpan sementara data hasil dari datagrid, dipakai saat submit modal

        function set_shipplan() {
            var dg = $('#dgOrder');

            if (!dg.length || !$.data(dg[0], 'datagrid')) {
                $.messager.alert('Error', 'DataGrid belum siap.', 'error');
                return;
            }

            var rows = dg.datagrid('getChecked');

            if (!rows || rows.length === 0) {
                $.messager.alert('Warning', 'Silakan pilih data terlebih dahulu.', 'warning');
                return;
            }

            const selected = rows.map(row => ({
                POno: row.POno,
                OP: row.OP,
                poref: row.poref,
                qty: row.qty,
                mif: row.mif,
                GAC: row.GAC,
            }));

            console.log(selected);

            // var ordpks = joinUnique(rows.map(r => r.ordpk));
            // var buyers = joinUnique(rows.map(r => r.buyer));
            // var POnos = joinUnique(rows.map(r => r.POno));
            // var OPs = joinUnique(rows.map(r => r.OP));
            // var popks2 = joinUnique(rows.map(r => r.popks2));
            var ordpks = rows.map(r => r.ordpk).join(',');
            var buyers = joinUnique(rows.map(r => r.buyer));
            var POnos = rows.map(r => r.POno).join(',');
            var mif = rows.map(r => r.mif).join(',');
            var OPs = rows.map(r => r.OP).join(',');
            var popks2 = rows.map(r => r.popks2).join(',');

            var qtyList = rows.map(r => Number(r.qty) || 0);
            var qtyListStr = qtyList.join(','); // "28420,2400,95"

            var totqty = rows.reduce((sum, r) => sum + (Number(r.qty) || 0), 0);
            var totctn = rows.reduce((sum, r) => sum + (Number(r.packing_ctn) || 0), 0);

            var exdates = rows.map(r => r.GAC).filter(Boolean);
            var exdate = exdates.length ? exdates.sort()[0] : null;

            // simpan dulu, JANGAN langsung AJAX -> tunggu user lengkapi modal
            pendingShipmentData = {
                ordpk: ordpks,
                buyer: buyers,
                POno: POnos,
                OP: OPs,
                factory: mif,
                popks: popks2,
                qty_list: qtyListStr,
                totqty: totqty,
                totctn: totctn,
                GAC: exdate,
                selectedRows: JSON.stringify(selected),
            };

            // // tampilkan ringkasan di header modal
            // document.getElementById('ssOrderCount').textContent = rows.length;
            // document.getElementById('ssTotalCtn').textContent = totctn;

            // // reset input form tiap kali modal dibuka
            // document.getElementById('ssExFactoryDate').value = '';
            // document.getElementById('ssEtd').value = '';
            // document.getElementById('ssVassal').value = '';

            // new bootstrap.Modal(document.getElementById('modalShipmentDetail')).show();

            // pastikan TIDAK ADA modal lain yang masih terbuka sebelum buka modal ini
            document.querySelectorAll('.modal.show').forEach(function(openModal) {
                const instance = bootstrap.Modal.getInstance(openModal);
                if (instance) instance.hide();
            });
            // beri jeda sedikit supaya modal lama selesai animasi tertutup dulu
            setTimeout(function() {
                document.getElementById('ssOrderCount').textContent = rows.length;
                document.getElementById('ssTotalCtn').textContent = totctn;
                document.getElementById('ssExFactoryDate').value = '';
                document.getElementById('ssEtd').value = '';
                document.getElementById('ssVassal').value = '';

                new bootstrap.Modal(document.getElementById('modalShipmentDetail')).show();
            }, 300); // durasi sedikit lebih lama dari animasi hide bawaan bootstrap (~150ms)
        }

        document.getElementById('btnSubmitShipmentDetail').addEventListener('click', function() {
            const exFactoryDate = document.getElementById('ssExFactoryDate').value;
            const etd = document.getElementById('ssEtd').value;
            const vassal = document.getElementById('ssVassal').value;

            if (!exFactoryDate || !etd || !vassal) {
                showToast('Lengkapi semua field terlebih dahulu.', 'error');
                return;
            }

            if (!pendingShipmentData) {
                showToast('Data order tidak ditemukan, silakan pilih ulang.', 'error');
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

            $.ajax({
                url: "{{ route('set_shipplan') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ...pendingShipmentData,
                    contdate: exFactoryDate,
                    etd: etd,
                    vassal: vassal,
                },
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    if (res.success) {

                        // 1. Close modal
                        const modalElement = document.getElementById('modalShipmentDetail');
                        const modal = bootstrap.Modal.getInstance(modalElement);

                        if (modal) {
                            modal.hide();
                        }
                        $('#ssExFactoryDate').value = '';
                        $('#ssEtd').value = '';
                        $('#ssVassal').value = '';
                        $('#dgShipmentPlan').datagrid('reload');
                        showToast(
                            res.message ?? 'Shipment plan berhasil dibuat.',
                            'success'
                        );

                    } else {
                        showToast(
                            res.message ?? 'Gagal membuat export.',
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    showToast(xhr.responseJSON?.message ?? 'Gagal membuat export.', 'error');
                }
            });
        });
        // end JS packing plan

        //FILTER SORT
        function sortlistByDate(value) {
            document.getElementById('sortlistByDate').value = value;
            onSpSearch()

            if (value == 11) {
                $('#newest').removeClass('d-none');
                $('#oldest').addClass('d-none');
            } else if (value == 12) {
                $('#newest').addClass('d-none');
                $('#oldest').removeClass('d-none');
            } else {
                $('#newest').removeClass('d-none');
                $('#oldest').addClass('d-none');
            }
        }
        //END

        //Hapus list 
        const confirmModalEl = document.getElementById('modalConfirmRemove');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        const crConfirmBtn = document.getElementById('crConfirmBtn');

        function removeListShipplan(exportpk) {
            // tampilkan modal konfirmasi, isi info carton yang mau dihapus
            confirmModal.show();

            // pasang handler baru tiap kali modal dibuka, hindari numpuk listener lama
            crConfirmBtn.onclick = function() {
                confirmModal.hide();
                doRemoveList(exportpk);
            };
        }

        function doRemoveList(exportpk) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            crConfirmBtn.disabled = true;
            const originalHtml = crConfirmBtn.innerHTML;
            crConfirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

            fetch("{{ route('shipplan.removeList') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        exportpk
                    }),
                })
                .then(async res => {
                    const text = await res.text();
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch {
                        throw new Error('Server mengembalikan respons tidak valid (status ' + res.status + ')');
                    }
                    if (!res.ok) throw new Error(result.message || 'Gagal mengeluarkan carton.');

                    document.getElementById('ldToastMessage').textContent = result.message;
                    bsToast.show();
                    $('#dgShipmentPlan').datagrid('reload');
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    crConfirmBtn.disabled = false;
                    crConfirmBtn.innerHTML = originalHtml;
                });
        }
        //end hapus list

        //publish
        function publish(exportpk) {
            const confirmModalEl = document.getElementById('modalConfirmPublish');
            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const cpIcon = document.getElementById('cpIcon');
            const cpTitle = document.getElementById('cpTitle');
            const cpDesc = document.getElementById('cpDesc');

            confirmModal.show();

            cpIcon.innerHTML = '<i class="bi bi-send-fill"></i>';
            cpIcon.style.background = '#E9F9EE';
            cpIcon.style.color = '#12A150';
            cpTitle.textContent = 'Publish Shipment Plan?';
            cpDesc.textContent = 'Shipment plan ini akan dikirim ke tim gudang dan mulai bisa diproses.';
            cpConfirmBtn.className = 'cr-btn cr-btn-publish-confirm';

            cpConfirmBtn.onclick = function() {
                confirmModal.hide();
                doTogglePublish(exportpk, 1);
            };
        }

        //Un publish
        function unpublish(exportpk) {
            const confirmModalEl = document.getElementById('modalConfirmPublish');
            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const cpIcon = document.getElementById('cpIcon');
            const cpTitle = document.getElementById('cpTitle');
            const cpDesc = document.getElementById('cpDesc');

            confirmModal.show();

            cpIcon.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
            cpIcon.style.background = '#FFF4E5';
            cpIcon.style.color = '#DC7609';
            cpTitle.textContent = 'Unpublish Shipment Plan?';
            cpDesc.textContent = 'Shipment plan ini akan ditarik kembali dan tidak lagi terlihat oleh tim gudang.';
            cpConfirmBtn.className = 'cr-btn cr-btn-unpublish-confirm';

            cpConfirmBtn.onclick = function() {
                confirmModal.hide();
                doTogglePublish(exportpk, 0);
            };
        }


        function doTogglePublish(exportpk, stsexp) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            fetch("{{ route('shipment-plan.toggle-publish') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        exportpk,
                        stsexp
                    }),
                })
                .then(async res => {
                    const text = await res.text();
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch {
                        showLdToast('Server mengembalikan respons tidak valid (status ' + res.status + ').',
                            'error');
                        return;
                    }
                    if (!res.ok) {
                        showLdToast(result.message || 'Gagal mengubah status publish.', 'error');
                        return;
                    }
                    showLdToast('Data berhasil diupdate.', 'success');
                    $('#dgShipmentPlan').datagrid('reload');
                })
                .catch(err => {
                    showLdToast(err.message || 'Terjadi kesalahan saat menghubungi server.',
                        'error'); // FIX -- showLdToast, bukan showToast
                });
        }

        //publish
        function lockship(exportpk) {
            const confirmModalEl = document.getElementById('modalConfirmPublish');
            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const cpIcon = document.getElementById('cpIcon');
            const cpTitle = document.getElementById('cpTitle');
            const cpDesc = document.getElementById('cpDesc');

            confirmModal.show();

            cpIcon.innerHTML = '<i class="bi bi-lock-fill"></i>';
            cpIcon.style.background = '#E9F9EE';
            cpIcon.style.color = '#12A150';
            cpTitle.textContent = 'Lock Shipment Plan?';
            cpDesc.textContent = 'Semua carton pada PO/session ini akan dikunci.';
            cpConfirmBtn.className = 'cr-btn cr-btn-publish-confirm';

            cpConfirmBtn.onclick = function() {
                confirmModal.hide();
                doToggleLock(exportpk);
            };
        }

        function doToggleLock(exportpk) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch("{{ route('shipment-plan.toggle-lock') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        exportpk,
                    }),
                })
                .then(async res => {
                    const text = await res.text();
                    let result;
                    try {
                        result = JSON.parse(text);
                    } catch {
                        throw new Error('Server mengembalikan respons tidak valid (status ' + res.status +
                            ')');
                    }
                    if (!res.ok) throw new Error(result.message || 'Gagal mengubah status publish.');

                    document.getElementById('ldToastMessage').textContent = "Data berhasil diupdate.";
                    bsToast.show();
                    $('#dgShipmentPlan').datagrid('reload');
                })
                .catch(err => {
                    showToast(err.message, 'error');
                    btn.disabled = false;
                });
        }

        /*MENU*/
        window.onscroll = function() {
            myFunctionb();
            myFunctiona();
            checkScroll();
        };


        //JS untuk modal packing plan
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
                $('#setship').hide();
                return;
            }

            $('#myHeader').removeClass('d-none');
            $('#count').removeClass('d-none');

            // ==========================================
            // CEK BUYER
            // ==========================================
            let sameBuyer = rows.length > 0 &&
                rows.every(r => r.buyer === rows[0].buyer);

            if (sameBuyer) {
                $('#setship').show();
            } else {
                $('#setship').hide();
            }

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

        function close_all() {
            $('#myHeader').addClass('d-none');
            $('#dgOrder').datagrid('reload');
            $('#dgOrder').datagrid('clearSelections');
            $('#dgOrder').datagrid('clearChecked');
        }

        /*end*/

        function menu2() {
            const rows = $('#dgShipmentPlan').datagrid('getChecked');
            const count = rows.length;

            console.log('Checked rows:', rows);
            console.log('Checked count:', count);

            // Tidak ada yang dicentang
            if (count === 0) {
                $('#myHeader2').addClass('d-none');
                $('#count2').addClass('d-none');
                $('#set_inv').hide();
                $('#set_peb').hide();

                $('#dgShipmentPlan').datagrid('clearSelections');

                count_row();
                return;
            }

            // Ada row yang dicentang
            $('#myHeader2').removeClass('d-none');
            $('#count2').removeClass('d-none');

            // Cek apakah semua exportpk sama
            const firstExportpk = rows[0].exportpk;

            const bedaExportpk = rows.some(row =>
                row.exportpk !== firstExportpk
            );

            if (bedaExportpk) {
                $('#set_inv').hide();
                $('#set_peb').hide();
            } else {
                $('#set_inv').show();
                $('#set_peb').show();
            }

            count_row2();
        }

        function count_row2() {
            var count = $('#dgShipmentPlan').datagrid('getChecked').length;
            if (count) {
                $('#myHeader2').removeClass('d-none');
                document.getElementById("count2").innerHTML = count + " items selected";
            } else {
                $('#myHeader2').addClass('d-none');
                $('#dgShipmentPlan').datagrid('clearSelections');
                document.getElementById("count2").innerHTML = 0 + " items selected";
            }

            // header baru muncul di sini; kalau halaman sudah ter-scroll, langsung
            // evaluasi sticky-nya tanpa menunggu event scroll berikutnya
            myFunctionb();
        }

        function close_menu() {
            $('#myHeader2').addClass('d-none');
            $('#dgShipmentPlan').datagrid('clearSelections');
            $('#dgShipmentPlan').datagrid('clearChecked');
        }

        //JS untuk modal packing plan
        var header2 = document.getElementById("myHeader2");
        var sticky2 = header2.offsetTop;

        // offsetTop hanya valid saat header terlihat DAN belum sticky.
        // saat load pertama #myHeader2 masih d-none (offsetTop = 0), dan saat sudah
        // position:fixed nilainya juga tidak mewakili posisi aslinya di halaman.
        function getStickyOffset2() {
            if (!header2.classList.contains('d-none') && !header2.classList.contains('sticky')) {
                sticky2 = header2.offsetTop;
            }
            return sticky2;
        }

        function myFunctionb() {
            if (window.pageYOffset > getStickyOffset2()) {
                header2.classList.add("sticky");
            } else {
                header2.classList.remove("sticky");
            }
        }

        function checkScroll() {
            var scrollYPosition = window.pageYOffset;

            var scroll = {};
            scroll.scrollYPosition = scrollYPosition;
            localStorage.setItem('scroll', JSON.stringify(scroll));
            // localStorage.setItem('scroll', JSON.stringify(scroll));
        }

        //SET_INVOICE LAMA
        let pendingDataPO = null;

        function set_inv() {
            var dg = $('#dgShipmentPlan');

            if (!dg.length || !$.data(dg[0], 'datagrid')) {
                $.messager.alert('Error', 'DataGrid belum siap.', 'error');
                return;
            }

            var rows = dg.datagrid('getChecked');

            if (!rows || rows.length === 0) {
                $.messager.alert('Warning', 'Silakan pilih data terlebih dahulu.', 'warning');
                return;
            }

            var poexportpk = rows.map(r => r.poexportpk).join(',');

            pendingDataPO = {
                poexportpk: poexportpk,
            };

            document.querySelectorAll('.modal.show').forEach(function(openModal) {
                const instance = bootstrap.Modal.getInstance(openModal);
                if (instance) instance.hide();
            });
            // beri jeda sedikit supaya modal lama selesai animasi tertutup dulu
            setTimeout(function() {
                document.getElementById('ssListCount').textContent = rows.length;

                new bootstrap.Modal(document.getElementById('modalInvoice')).show();
            }, 300);
        }

        document.getElementById('btnSubmitInvoice').addEventListener('click', function() {
            const invs = document.getElementById('ssInvs').value;

            if (!invs) {
                showToast('Silahkan isi nomor invoice terlebih dahulu.', 'error');
                return;
            }

            if (!pendingDataPO) {
                showToast('Data order tidak ditemukan, silakan pilih ulang.', 'error');
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

            $.ajax({
                url: "{{ route('set_invs') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ...pendingDataPO,
                    invs: invs,
                },
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    if (res.success) {

                        // 1. Close modal
                        const modalElement = document.getElementById('modalInvoice');
                        const modal = bootstrap.Modal.getInstance(modalElement);

                        if (modal) {
                            modal.hide();
                        }
                        $('#ssInvs').value = '';
                        $('#dgShipmentPlan').datagrid('reload');
                        showToast(
                            res.message ?? 'Nomor Invoice berhasil dibuat.',
                            'success'
                        );
                        close_menu();

                    } else {
                        showToast(
                            res.message ?? 'Gagal membuat nomor invoice.',
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    showToast(xhr.responseJSON?.message ?? 'Gagal membuat nomor invoice.', 'error');
                }
            });
        });

        function set_peb() {
            var dg = $('#dgShipmentPlan');

            if (!dg.length || !$.data(dg[0], 'datagrid')) {
                $.messager.alert('Error', 'DataGrid belum siap.', 'error');
                return;
            }

            var rows = dg.datagrid('getChecked');

            if (!rows || rows.length === 0) {
                $.messager.alert('Warning', 'Silakan pilih data terlebih dahulu.', 'warning');
                return;
            }

            var poexportpk = rows.map(r => r.poexportpk).join(',');

            pendingDataPO = {
                poexportpk: poexportpk,
            };

            document.querySelectorAll('.modal.show').forEach(function(openModal) {
                const instance = bootstrap.Modal.getInstance(openModal);
                if (instance) instance.hide();
            });
            // beri jeda sedikit supaya modal lama selesai animasi tertutup dulu
            setTimeout(function() {
                document.getElementById('ssListCount').textContent = rows.length;

                new bootstrap.Modal(document.getElementById('modalPEB')).show();
            }, 300);
        }

        document.getElementById('btnSubmitPEB').addEventListener('click', function() {
            const pebno = document.getElementById('ssPEB').value;

            if (!pebno) {
                showToast('Silahkan isi nomor invoice terlebih dahulu.', 'error');
                return;
            }

            if (!pendingDataPO) {
                showToast('Data order tidak ditemukan, silakan pilih ulang.', 'error');
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

            $.ajax({
                url: "{{ route('set_peb') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    ...pendingDataPO,
                    pebno: pebno,
                },
                success: function(res) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    if (res.success) {

                        // 1. Close modal
                        const modalElement = document.getElementById('modalPEB');
                        const modal = bootstrap.Modal.getInstance(modalElement);

                        if (modal) {
                            modal.hide();
                        }
                        $('#ssPEB').value = '';
                        $('#dgShipmentPlan').datagrid('reload');
                        showToast(
                            res.message ?? 'Nomor PEB berhasil dibuat.',
                            'success'
                        );
                        close_menu();

                    } else {
                        showToast(
                            res.message ?? 'Gagal membuat nomor PEB.',
                            'error'
                        );
                    }
                },
                error: function(xhr) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    showToast(xhr.responseJSON?.message ?? 'Gagal membuat nomor PEB.', 'error');
                }
            });
        });

        //Open modal edit shipment plan
        (function() {
            /* ================= TAB NAVIGATION ================= */
            const order = ['shipplan', 'capacity', 'info', 'schedule'];
            let current = 0;
            const tabs = document.querySelectorAll('#mcTabs .mc-tab');
            const panes = document.querySelectorAll('.mc-pane');
            const dots = document.querySelectorAll('.mc-step-dots .dot');
            const indicator = document.querySelector('.mc-tab-indicator');
            const prevBtn = document.getElementById('mcPrevBtn');
            const nextBtn = document.getElementById('mcNextBtn');
            const submitBtn = document.getElementById('mcSubmitBtn');

            function goTo(index) {
                current = index;
                const key = order[index];
                tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === key));
                panes.forEach(p => p.classList.toggle('active', p.dataset.pane === key));
                dots.forEach(d => d.classList.toggle('active', d.dataset.dot === key));
                const activeTab = document.querySelector(`.mc-tab[data-tab="${key}"]`);
                if (activeTab && indicator) {
                    indicator.style.width = activeTab.offsetWidth + 'px';
                    indicator.style.transform = `translateX(${activeTab.offsetLeft - 20}px)`;
                }
                prevBtn.disabled = index === 0;
                nextBtn.classList.toggle('d-none', index === order.length - 1);
                submitBtn.classList.toggle('d-none', index !== order.length - 1);

                refreshPaneWidgets(key);
            }

            // .mc-pane itu display:none selama tidak aktif, dan easyui merender widget yang
            // diinisialisasi di dalam elemen tersembunyi dengan lebar 0 — kotaknya kelihatan
            // tapi tombol kalendernya tidak bisa diklik. Jadi datetimebox-nya sengaja BARU
            // diinisialisasi saat panenya benar-benar tampil.
            function initArrivalDatetimebox(input) {
                try {
                    if ($(input).data('datetimebox')) {
                        $(input).datetimebox('resize', 250);
                        return;
                    }

                    $(input).datetimebox({
                        formatter: myformatter2,
                        parser: myparser2,
                        width: 250,
                        height: 40,
                    });

                    // nilai awal dititipkan di dataset oleh syncContainerArrivalTab(),
                    // karena saat itu widget-nya memang belum ada
                    const preset = input.dataset.presetValue || '';
                    if (preset) {
                        $(input).datetimebox('setValue', preset);
                    }
                } catch (err) {
                    console.error('[shipplan] init datetimebox actcontdate gagal:', err);
                }
            }

            function refreshPaneWidgets(paneKey) {
                const pane = document.querySelector(`.mc-pane[data-pane="${paneKey}"]`);
                if (!pane || !pane.classList.contains('active')) return;

                pane.querySelectorAll('[data-field="actcontdate"]').forEach(initArrivalDatetimebox);
            }
            window.mcNav = (dir) => {
                const n = current + dir;
                if (n >= 0 && n < order.length) goTo(n);
            };
            tabs.forEach((tab, i) => tab.addEventListener('click', () => goTo(i)));

            /* ================= MULTI CONTAINER: tambah/hapus card ================= */
            const containerList = document.getElementById('containerList');
            const template = document.getElementById('containerCardTemplate');
            const btnAdd = document.getElementById('btnAddContainer');
            const countPill = document.getElementById('containerCountPill');

            function renumber() {
                const items = containerList.querySelectorAll('.container-item');

                items.forEach((item, i) => {
                    item.querySelector('.container-badge-num').textContent = i + 1;

                    item.querySelectorAll('[data-field]').forEach(input => {
                        const field = input.dataset.field;
                        input.name = `containers[${i}][${field}]`;
                    });

                    item.querySelectorAll('input[type="radio"]').forEach(r => {
                        r.name = `containers[${i}][type]`;
                    });

                    let contpkInput = item.querySelector('input[data-field="contpk"]');
                    if (!contpkInput) {
                        contpkInput = document.createElement('input');
                        contpkInput.type = 'hidden';
                        contpkInput.dataset.field = 'contpk';
                        item.appendChild(contpkInput);
                    }
                    contpkInput.name = `containers[${i}][contpk]`;
                    contpkInput.value = item.dataset.contpk || '';

                    item.querySelector('.btn-remove-container').disabled = items.length === 1;
                });

                countPill.textContent = items.length + ' Container';

                // sinkronkan ke tab 3 setiap kali container di tab 1 berubah
                syncContainerArrivalTab();
                syncContainerFotoTab();
            }

            // field teks biasa di kartu arrival; actcontdate ditangani terpisah karena easyui.
            // noseal ada di kartu tab foto, bukan di sini.
            const ARRIVAL_TEXT_FIELDS = ['contno', 'nopol'];

            function syncContainerArrivalTab() {
                const arrivalList = document.getElementById('containerArrivalList');
                const arrivalTemplate = document.getElementById('containerArrivalCardTemplate');

                const arrivalCountPill = document.getElementById('containerArrivalCountPill');

                const sourceItems = containerList.querySelectorAll('.container-item');

                const existingValues = {};

                arrivalList.querySelectorAll('.container-item').forEach(el => {
                    const i = el.dataset.uid; // key per card, bukan nomor urut
                    const row = {};

                    ARRIVAL_TEXT_FIELDS.forEach(field => {
                        const input = el.querySelector(`[data-field="${field}"]`);
                        if (input) row[field] = input.value;
                    });

                    // datetimebox menyimpan nilai di widget-nya, bukan di input aslinya.
                    // kalau widget belum sempat dibuat (pane belum pernah dibuka), nilainya
                    // masih menunggu di dataset.presetValue
                    const actInput = el.querySelector('[data-field="actcontdate"]');
                    if (actInput) {
                        row.actcontdate = $(actInput).data('datetimebox') ?
                            $(actInput).datetimebox('getValue') :
                            (actInput.value || actInput.dataset.presetValue || '');
                    }

                    existingValues[i] = row;
                });

                // panel kalender easyui di-append ke <body>, bukan ke kartunya.
                // tanpa destroy, mengosongkan innerHTML meninggalkan panel yatim yang menumpuk.
                arrivalList.querySelectorAll('[data-field="actcontdate"]').forEach(input => {
                    if ($(input).data('datetimebox')) {
                        $(input).datetimebox('destroy');
                    }
                });

                arrivalList.innerHTML = '';

                // =====================================================
                // GENERATE
                // =====================================================

                sourceItems.forEach((sourceItem, i) => {
                    // =================================================
                    // ARRIVAL
                    // =================================================

                    const arrivalClone =
                        arrivalTemplate.content.cloneNode(true);

                    const arrivalEl =
                        arrivalClone.querySelector('.container-item');

                    if (!arrivalEl) {
                        return;
                    }
                    // Nomor
                    const numEl =
                        arrivalEl.querySelector('.container-arrival-num');

                    if (numEl) {
                        numEl.textContent = i + 1;
                    }

                    // Vessel
                    const vessnameInput =
                        sourceItem.querySelector(
                            '[data-field="vessname"]'
                        );

                    const vessLabel =
                        arrivalEl.querySelector(
                            '.container-arrival-vessel'
                        );

                    if (vessLabel) {
                        vessLabel.textContent =
                            vessnameInput?.value || '-';
                    }

                    // Field teks: contno, nopol, noseal
                    arrivalEl.dataset.uid = sourceItem.dataset.uid;
                    const saved = existingValues[sourceItem.dataset.uid] || {};

                    ARRIVAL_TEXT_FIELDS.forEach(field => {
                        const input = arrivalEl.querySelector(`[data-field="${field}"]`);
                        if (!input) return;

                        input.name = `containers[${i}][${field}]`;

                        if (saved[field]) {
                            input.value = saved[field];
                        } else if (field === 'contno' && sourceItem.dataset.contno) {
                            input.value = sourceItem.dataset.contno;
                        } else {
                            input.value = sourceItem.dataset[field] || '';
                        }
                    });

                    // Append ARRIVAL — harus sebelum init easyui, karena easyui butuh
                    // elemennya sudah ada di DOM untuk menghitung lebar & menempelkan panelnya
                    arrivalList.appendChild(arrivalEl);

                    // Actual Container Arrival (easyui-datetimebox).
                    // isi <template> tidak pernah ikut di-parse easyui saat DOM ready, jadi kartu
                    // hasil clone harus diinisialisasi manual — tapi BUKAN di sini, karena pane
                    // "info" masih display:none. initArrivalDatetimebox() yang mengerjakannya
                    // lewat refreshPaneWidgets() begitu panenya tampil.
                    const actInput = arrivalEl.querySelector('[data-field="actcontdate"]');

                    if (actInput) {
                        // name harus dipasang SEBELUM init: easyui memindahkan atribut name
                        // ke hidden input .textbox-value miliknya, dan itulah yang ikut ter-submit
                        actInput.name = `containers[${i}][actcontdate]`;
                        actInput.dataset.presetValue = saved.actcontdate ||
                            sourceItem.dataset.actcontdate || '';
                    }

                    // kartu tab foto TIDAK dibangun di sini — syncContainerFotoTab() pemilik
                    // tunggal #containerFotoList. Dulu fungsi ini ikut membangunnya lalu langsung
                    // ditimpa, dan itu menghapus preview foto serta isi No. Seal.
                });

                arrivalCountPill.textContent =
                    sourceItems.length + ' Container';

                // kalau pane "info" kebetulan sedang tampil, widget-nya langsung dibuat.
                // kalau tidak, ini no-op dan goTo() yang akan mengerjakannya nanti.
                refreshPaneWidgets('info');
            }

            // panggil sync juga saat user mengetik vessel name di tab 1, supaya label di tab 3 ikut update real-time
            containerList.addEventListener('input', function(e) {
                if (e.target.dataset.field === 'vessname') {
                    syncContainerArrivalTab();
                }
            });

            /*ini untuk control foto upload*/
            // tiap container punya 5 slot foto: foto1..foto5.
            // foto1 disimpan di kolom `foto`, sisanya di kolom foto2..foto5.
            const FOTO_SLOTS = ['foto1', 'foto2', 'foto3', 'foto4', 'foto5'];

            // key semua Map/Set di bawah = `${indexContainer}:${slot}`, contoh "0:foto3"
            const containerFotoFiles = new Map(); // file baru yang dipilih user
            const containerFotoUrls = new Map(); // foto lama dari server (mode edit)
            const containerFotoRemoved = new Set(); // slot yang sengaja dikosongkan user

            // key = `${uid}:${slot}`; uid = id tetap per card container (lihat addContainerCard),
            // BUKAN nomor urut, supaya tidak bergeser saat ada card yang dihapus
            const fotoKey = (uid, slot) => `${uid}:${slot}`;
            let containerUidSeq = 0;

            // tombol "Tambah Foto" cuma relevan selama masih ada slot yang tersembunyi
            function refreshAddFotoBtn(cardEl) {
                const wrap = cardEl.querySelector('.container-foto-add-wrap');
                if (!wrap) return;

                const hiddenLeft = cardEl.querySelectorAll('.container-foto-slot.d-none').length;
                wrap.classList.toggle('d-none', hiddenLeft === 0);
            }

            function clearFotoSlot(slotEl) {
                slotEl.querySelector('.input-camera').value = '';
                slotEl.querySelector('.file-name-camera').textContent = '';
                slotEl.querySelector('.close-camera').classList.add('d-none');
                slotEl.querySelector('.container-foto-preview').innerHTML = '';
            }

            function syncContainerFotoTab() {
                const fotoList = document.getElementById('containerFotoList');
                const fotoTemplate = document.getElementById('containerFotoCardTemplate');
                const fotoCountPill = document.getElementById('containerFotoCountPill');
                const arrivalListEl = document.getElementById('containerArrivalList');

                if (!fotoList || !fotoTemplate) return;

                const sourceItems = containerList.querySelectorAll('.container-item');
                // FIX: jangan panggil querySelectorAll kalau arrivalListEl null (tidak ada di halaman index)
                const arrivalItems = arrivalListEl ? arrivalListEl.querySelectorAll('.container-item') : [];

                // simpan No. Seal yang sudah diketik, karena kartu dibangun ulang dari template
                const existingNoseal = {};
                fotoList.querySelectorAll('.container-item').forEach((el, i) => {
                    const input = el.querySelector('[data-field="noseal"]');
                    if (input) existingNoseal[el.dataset.uid] = input.value;
                });

                fotoList.innerHTML = '';

                sourceItems.forEach((sourceItem, i) => {
                    const clone = fotoTemplate.content.cloneNode(true);
                    const el = clone.querySelector('.container-item');

                    el.dataset.index = i;
                    el.dataset.uid = sourceItem.dataset
                        .uid; // penting: uid dipakai untuk mapping ke containerFotoFiles

                    const nosealInput = el.querySelector('[data-field="noseal"]');
                    if (nosealInput) {
                        nosealInput.name = `containers[${i}][noseal]`;
                        nosealInput.value = existingNoseal[sourceItem.dataset.uid] || sourceItem.dataset
                            .noseal || '';
                    }

                    el.querySelector('.container-foto-num').textContent = i + 1;

                    const arrivalItem = arrivalItems[i];
                    const contnoInput = arrivalItem?.querySelector('[data-field="contno"]');
                    el.querySelector('.container-foto-vessel').textContent = contnoInput?.value || '-';

                    const start_ship = sourceItem.querySelector('[data-field="start_ship"]');
                    const btnfoto = el.querySelector('#btn-camera');

                    if (btnfoto) {
                        btnfoto.disabled = !start_ship?.value?.trim();
                    }

                    // isi ulang tiap slot foto sesuai state terakhir.
                    // prioritas 1: file baru yang dipilih user, prioritas 2: foto lama dari server
                    FOTO_SLOTS.forEach((slot, slotIdx) => {
                        const slotEl = el.querySelector(`[data-photo-slot="${slot}"]`);
                        if (!slotEl) return;

                        const key = fotoKey(sourceItem.dataset.uid, slot);
                        const file = containerFotoFiles.get(key);
                        const url = file ? null : containerFotoUrls.get(key);

                        // slot 1 selalu tampil, slot lain baru dibuka kalau memang sudah ada isinya
                        if (slotIdx === 0 || file || url) {
                            slotEl.classList.remove('d-none');
                        }

                        if (file) {
                            slotEl.querySelector('.file-name-camera').textContent = file.name;
                            slotEl.querySelector('.close-camera').classList.remove('d-none');
                            renderFotoPreview(slotEl, file);
                        } else if (url) {
                            slotEl.querySelector('.file-name-camera').textContent = 'Foto tersimpan';
                            slotEl.querySelector('.close-camera').classList.remove('d-none');
                            slotEl.querySelector('.container-foto-preview').innerHTML =
                                `<img src="${url}" style="max-width:120px;max-height:120px;border-radius:8px;border:1px solid #EAECF0;">`;
                        }
                    });

                    refreshAddFotoBtn(el);

                    fotoList.appendChild(el);
                });

                if (fotoCountPill) fotoCountPill.textContent = sourceItems.length + ' Container';
            }

            // preview thumbnail kecil di bawah tombol upload (per slot foto, bukan per card)
            function renderFotoPreview(slotEl, file) {
                const previewWrap = slotEl.querySelector('.container-foto-preview');
                if (!previewWrap) return;

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewWrap.innerHTML =
                        `<img src="${e.target.result}" style="max-width:120px;max-height:120px;border-radius:8px;border:1px solid #EAECF0;">`;
                };
                reader.readAsDataURL(file);
            }

            /* ================= EVENT DELEGATION — 1 listener untuk semua card foto ================= */

            document.addEventListener('click', function(e) {
                const fotoList = document.getElementById('containerFotoList');
                if (!fotoList || !fotoList.contains(e.target)) return;

                const cardEl = e.target.closest('.container-item');
                if (!cardEl) return;

                // tombol "Tambah Foto" -> buka satu slot tersembunyi berikutnya
                if (e.target.closest('.btn-add-foto')) {
                    const nextHidden = cardEl.querySelector('.container-foto-slot.d-none');
                    if (nextHidden) nextHidden.classList.remove('d-none');

                    refreshAddFotoBtn(cardEl);
                    return;
                }

                // sisanya harus di-scope ke slot fotonya, bukan ke card,
                // karena satu card sekarang punya 5 tombol/input yang kelasnya sama
                const slotEl = e.target.closest('.container-foto-slot');
                if (!slotEl) return;

                const uid = cardEl.dataset.uid;
                const slot = slotEl.dataset.photoSlot;

                if (e.target.closest('.btn-camera')) {
                    slotEl.querySelector('.input-camera').click();
                    return;
                }

                if (e.target.closest('.close-camera')) {
                    const key = fotoKey(uid, slot);

                    containerFotoFiles.delete(key);
                    containerFotoUrls.delete(key); // hapus juga referensi foto lama

                    clearFotoSlot(slotEl);

                    // tandai bahwa user SENGAJA mengosongkan slot ini, supaya backend set kolomnya null
                    containerFotoRemoved.add(key);
                }
            });

            // event 'change' pada input file -> event delegation juga, karena input di-clone dinamis
            document.addEventListener('change', function(e) {
                if (!e.target.classList.contains('input-camera')) return;

                const cardEl = e.target.closest('.container-item');
                const slotEl = e.target.closest('.container-foto-slot');
                if (!cardEl || !slotEl) return;

                const uid = cardEl.dataset.uid;
                const slot = slotEl.dataset.photoSlot;
                const file = e.target.files[0];
                if (!file) return;

                // validasi sederhana: harus gambar, maksimal 5MB
                if (!file.type.startsWith('image/')) {
                    showToast('File harus berupa gambar.', 'error');
                    e.target.value = '';
                    return;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showToast('Ukuran file maksimal 5MB.', 'error');
                    e.target.value = '';
                    return;
                }

                const key = fotoKey(uid, slot);

                containerFotoFiles.set(key, file);
                containerFotoRemoved.delete(key); // user ganti pikiran: slot ini diisi lagi

                slotEl.querySelector('.file-name-camera').textContent = file.name;
                slotEl.querySelector('.close-camera').classList.remove('d-none');
                renderFotoPreview(slotEl, file);
            });
            /*END*/

            function addContainerCard(data = null) {
                const clone = template.content.cloneNode(true);
                const el = clone.querySelector('.container-item');

                const volumeMap = {
                    '20ft': 33,
                    '40ft': 67,
                    '40hc': 76
                };

                const volumeInput = el.querySelector('[data-field="volume"]');
                const volumeContainer = el.querySelector('#volumeContainer');

                // simpan value LCL asli (dari server saat edit) di dataset, TERPISAH dari volumeInput.value
                // supaya tidak ikut tertimpa saat user pindah-pindah radio
                let savedLclVolume = '';

                function setVolume(type, isEdit = false) {
                    if (type === 'LCL') {
                        volumeContainer.classList.remove('d-none');
                        volumeInput.readOnly = false;

                        // restore value LCL yang tersimpan, BUKAN cek volumeInput.value saat ini
                        volumeInput.value = savedLclVolume;
                        return;
                    }

                    // sebelum pindah ke tipe LAIN, simpan dulu value LCL SAAT INI (kalau memang lagi di LCL)
                    // supaya nanti bisa dikembalikan persis saat balik ke LCL
                    if (volumeContainer && !volumeContainer.classList.contains('d-none')) {
                        savedLclVolume = volumeInput.value;
                    }

                    volumeContainer.classList.add('d-none');

                    if (volumeMap[type] !== undefined) {
                        volumeInput.value = volumeMap[type];
                        volumeInput.readOnly = true;
                    } else {
                        volumeInput.value = '';
                        volumeInput.readOnly = false;
                    }
                }

                el.querySelectorAll('input[name="ctype"]').forEach(radio => {
                    radio.addEventListener('change', function() {
                        setVolume(this.value);
                    });
                });

                // =========================
                // REMOVE BUTTON
                // =========================
                el.querySelector('.btn-remove-container').addEventListener(
                    'click',
                    () => removeContainerCard(el)
                );

                // =========================
                // MODE EDIT
                // =========================
                if (data) {
                    console.log('data container:', data);

                    // Isi semua field
                    el.querySelectorAll('[data-field]').forEach(input => {
                        const field = input.dataset.field;
                        if (data[field] !== undefined && data[field] !== null) {
                            input.value = data[field];
                        }
                    });

                    // Set radio tipe container
                    if (data.type) {
                        const radio = el.querySelector(`input[type="radio"][value="${data.type}"]`);
                        if (radio) {
                            radio.checked = true;
                        }
                        // kalau tipenya LCL, tampilkan input volume dan isi dengan value dari database
                        if (data.type === 'LCL') {
                            // const volumeInput = el.querySelector('[data-field="volume"]');
                            // const volumeContainer = el.querySelector('#volumeContainer');

                            // if (volumeContainer) {
                            //     volumeContainer.classList.remove('d-none');
                            // }

                            // if (volumeInput && data.volume !== undefined && data.volume !== null) {
                            //     volumeInput.value = data.volume;
                            //     volumeInput.readOnly = false; // LCL boleh diisi manual, bukan auto dari volumeMap
                            // }
                            savedLclVolume = data.volume ?? '';
                            setVolume('LCL', true);
                        }
                    }

                    // Simpan contpk & field kartu arrival untuk dipakai saat sync ke tab 3
                    if (data.contpk) {
                        el.dataset.contpk = data.contpk;
                    }
                    ['contno', 'nopol', 'noseal', 'actcontdate'].forEach(field => {
                        if (data[field]) {
                            el.dataset[field] = data[field];
                        }
                    });
                }

                // =========================
                // APPEND
                // =========================
                // id tetap per card -> key foto tidak bergeser saat card lain dihapus
                el.dataset.uid = String(++containerUidSeq);

                // URL foto lama dari server (mode edit), dibaca syncContainerFotoTab()
                const fotoUrls = (data && data.foto_urls) || {};
                FOTO_SLOTS.forEach(slot => {
                    if (fotoUrls[slot]) {
                        containerFotoUrls.set(fotoKey(el.dataset.uid, slot), fotoUrls[slot]);
                    }
                });

                containerList.appendChild(el);

                renumber();
            }

            function removeContainerCard(el) {
                if (containerList.querySelectorAll('.container-item').length === 1) return;
                el.classList.add('removing');
                el.addEventListener('animationend', () => {
                    const prefix = `${el.dataset.uid}:`;
                    [containerFotoFiles, containerFotoUrls].forEach(m => {
                        [...m.keys()].forEach(k => k.startsWith(prefix) && m.delete(k));
                    });
                    [...containerFotoRemoved].forEach(k => k.startsWith(prefix) && containerFotoRemoved.delete(
                        k));

                    el.remove();
                    renumber();
                }, {
                    once: true
                });
            }

            btnAdd.addEventListener('click', addContainerCard);

            document.getElementById('modalCreateContainer').addEventListener('shown.bs.modal', function() {
                // hanya tambahkan card kosong otomatis kalau mode CREATE dan list masih kosong
                const targetTabIndex = order.indexOf(window.__pendingTargetTab || 'schedule');
                goTo(targetTabIndex >= 0 ? targetTabIndex : 0);

                setModalLockMode(!!window.__pendingLockMode);

                window.__pendingTargetTab = null;
                window.__pendingLockMode = null;

                if (currentMode === 'create' && containerList.children.length === 0) {
                    addContainerCard();
                }
                syncContainerArrivalTab();
                syncContainerFotoTab();

                $('#exdate').datetimebox({
                    onChange: function() {
                        onExdateChange();
                    },
                });


                $('#closetime').datetimebox({
                    editable: false,
                    onChange: function(newValue) {
                        validateAndCorrectCloseTime(newValue);
                    },
                });

                // kalau ada data pending dari mode edit, isi SEKARANG (widget sudah pasti siap)
                if (window.__pendingExdateEditData) {
                    const {
                        exdate,
                        closetime
                    } = window.__pendingExdateEditData;

                    const [datePart, timePart] = exdate.split(' ');
                    const [year, month, day] = datePart.split('-');
                    const formatted = `${day}/${month}/${year} ${timePart.substring(0, 5)}`;
                    if (exdate) {
                        $('#exdate').datetimebox('setValue', formatted);
                    }

                    // WAJIB: enable dulu closetime SEBELUM setValue, kalau tidak akan diabaikan
                    if (exdate) {
                        $('#closetime').datetimebox('enable');
                    }

                    const [datePart2, timePart2] = closetime.split(' ');
                    const [year2, month2, day2] = datePart2.split('-');
                    const formatted2 = `${day2}/${month2}/${year2} ${timePart2.substring(0, 5)}`;
                    // alert(formatted2);
                    if (closetime) {
                        $('#closetime').datetimebox('setValue', formatted2);
                    }

                    window.__pendingExdateEditData = null; // bersihkan setelah dipakai
                }

                onExdateChange();
            });

            document.getElementById('modalCreateContainer').addEventListener('hidden.bs.modal', function() {
                currentMode = 'create';
                currentExportpk = null;
                containerList.innerHTML = '';
                containerFotoFiles.clear();
                containerFotoUrls.clear();
                containerFotoRemoved.clear();

                form.reset();

                document.getElementById('ordnop').textContent = 'No Order Number';
                document.getElementById('buyerp').textContent = 'No Buyer Information';
                document.getElementById('POnop').textContent = 'No PO Number';

                // reset lock mode supaya modal berikutnya (misal dibuka dari detail_ship) kembali normal
                setModalLockMode(false);
            });

            /*tambahan untuk edit*/
            let currentMode = 'create'; // 'create' atau 'edit' — dipakai di handler shown.bs.modal
            let currentExportpk = null;

            // dipanggil dari tombol "Edit" di halaman (ganti trigger create biasa)
            window.openEditShipmentModal = function(exportpk, targetTab = 'schedule', lockOtherTabs = false) {
                currentMode = 'edit';
                currentExportpk = exportpk;

                // reset dulu state foto dari sesi sebelumnya
                containerFotoFiles.clear();
                containerFotoUrls.clear();
                containerFotoRemoved.clear();

                // dipakai .catch() untuk membedakan gagal ambil data vs gagal merender modal,
                // dan menunjuk tahap mana yang gagal (error object saja sering tampil kosong)
                let dataLoaded = false;
                let stage = 'fetch';

                const storeContainerUrl =
                    "{{ route('export.detail-json', ['exportpk' => '__EXPORTPK__']) }}";
                fetch(storeContainerUrl.replace('__EXPORTPK__', exportpk), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        return res.json();
                    })
                    .then(data => {
                        dataLoaded = true;
                        stage = 'header';
                        document.getElementById('exportpk').value = exportpk;

                        $('#ordnop').text(data.OP ? 'ORDER NUMBER : ' + data.OP : 'No Order Number');
                        $('#buyerp').text(data.buyer ? 'BUYER : ' + data.buyer : 'No Buyer Information');
                        $('#POnop').text(data.POno ? 'PO : ' + String(data.POno).split(',').map(s => s.trim())
                            .filter(Boolean).join(', ') : 'No PO Number');

                        // // isi field header
                        const pebno = form.querySelector('[name="pebno"]');
                        if (pebno) pebno.value = data.pebno ?? '';

                        const totqty = form.querySelector('[name="totqty"]');
                        if (totqty) totqty.value = data.totqty ?? '';

                        const totnw = form.querySelector('[name="totnw"]');
                        if (totnw) totnw.value = data.totnw ?? '';

                        const totgw = form.querySelector('[name="totgw"]');
                        if (totgw) totgw.value = data.totgw ?? '';

                        const totvol = form.querySelector('[name="totvol"]');
                        if (totvol) totvol.value = data.totvol ?? '';

                        const ukctn = form.querySelector('[name="ukctn"]');
                        if (ukctn) ukctn.value = data.ukctn ?? '';

                        const totctn = form.querySelector('[name="totctn"]');
                        if (totctn) totctn.value = data.totctn ?? '';

                        // const vassalSelect = form.querySelector('[name="vassal"]');
                        // if (vassalSelect) vassalSelect.value = data.vassal ?? '';
                        stage = 'combobox';
                        const vassalCombo = document.getElementById('vassal');
                        if (vassalCombo && data.vassalpk !== undefined && data.vassalpk !== null) {
                            if ($(vassalCombo).data('combobox')) {
                                $('#vassal').combobox('setValue', data.vassalpk);
                            } else {
                                vassalCombo.value = data.vassalpk;
                            }
                        }

                        const forwarderCombo = document.getElementById('fwd');
                        if (forwarderCombo && data.fwdpk !== undefined && data.fwdpk !== null) {
                            if ($(forwarderCombo).data('combobox')) {
                                $('#fwd').combobox('setValue', data.fwdpk);
                            } else {
                                forwarderCombo.value = data.fwdpk;
                            }
                        }

                        const plbCombo = document.getElementById('plb');
                        if (plbCombo && data.plbpk !== undefined && data.plbpk !== null) {
                            if ($(plbCombo).data('combobox')) {
                                $('#plb').combobox('setValue', data.plbpk);
                            } else {
                                plbCombo.value = data.plbpk;
                            }
                        }

                        stage = 'tanggal';
                        const contdate = form.querySelector('[name="contdate"]');
                        if (contdate && data.contdate) {
                            const [year, month, day] = data.contdate.split('-');
                            $('#contdate').datebox(
                                'setValue',
                                `${day}/${month}/${year}`
                            );
                        }

                        const actcontdate = form.querySelector('[name="actcontdate"]');
                        if (actcontdate && data.actcontdate) {
                            const [year, month, day] = data.actcontdate.split('-');
                            $('#actcontdate').datebox(
                                'setValue',
                                `${day}/${month}/${year}`
                            );
                        }


                        const etdvess = form.querySelector('[name="etdvess"]');
                        if (etdvess && data.etdvess) {
                            const [year, month, day] = data.etdvess.split('-');

                            $('#etdvess').datebox(
                                'setValue',
                                `${day}/${month}/${year}`
                            );
                        }

                        const etavess = form.querySelector('[name="etavess"]');
                        if (etavess && data.etavess) {
                            const [year, month, day] = data.etavess.split('-');
                            $('#etavess').datebox(
                                'setValue',
                                `${day}/${month}/${year}`
                            );
                        }

                        const closetime = form.querySelector('[name="closetime"]');
                        if (closetime && data.closetime) {
                            const [datePart, timePart] = data.closetime.split(' ');

                            const [year, month, day] = datePart.split('-');

                            $('#closetime').datetimebox('setValue',
                                `${day}/${month}/${year} ${timePart}`
                            );
                        }

                        const exdate = form.querySelector('[name="exdate"]');
                        if (exdate && data.exdate) {
                            const [datePart, timePart] = data.exdate.split(' ');

                            const [year, month, day] = datePart.split('-');

                            $('#exdate').datetimebox('setValue',
                                `${day}/${month}/${year} ${timePart}`
                            );
                        }

                        const remark = form.querySelector('[name="remark"]');
                        if (remark) remark.value = data.remark ?? '';

                        const contno = form.querySelector('[name="contno"]');
                        if (contno) contno.value = data.contno ?? '';

                        // // kosongkan list container lama, lalu isi ulang sesuai data dari server
                        stage = 'containers';
                        containerList.innerHTML = '';
                        if (data.containers && data.containers.length) {
                            // simpan URL foto lama (kalau ada) ke Map DULUAN, dipakai syncContainerFotoTab().
                            // harus sebelum addContainerCard(), karena addContainerCard() -> renumber()
                            // sudah memanggil syncContainerFotoTab() dan membaca Map ini.

                            data.containers.forEach(c => addContainerCard(c));
                        } else {
                            addContainerCard(); // fallback: minimal 1 card kosong
                        }

                        const modalTitle = document.querySelector('.mc-title');
                        if (modalTitle) modalTitle.textContent = 'Edit Shipment Plan';

                        const submitBtnLabel = document.getElementById('mcSubmitBtn');
                        if (submitBtnLabel) submitBtnLabel.innerHTML =
                            '<i class="bi bi-check2-circle"></i> Simpan Perubahan';


                        stage = 'show-modal';
                        new bootstrap.Modal(document.getElementById('modalCreateContainer')).show();

                        window.__pendingExdateEditData = {
                            exdate: data.exdate,
                            closetime: data.closetime
                        };

                        window.__pendingTargetTab = targetTab;
                        window.__pendingLockMode = lockOtherTabs; // simpan flag, dipakai di shown.bs.modal

                    })
                    .catch(err => {
                        // .catch() ini juga menangkap error JS dari blok .then() di atas.
                        // sebelumnya semuanya dilaporkan sebagai "gagal memuat", padahal datanya
                        // sudah sampai dan yang gagal adalah proses merendernya.
                        // err sering tampil kosong di console (bukan objek Error), jadi
                        // pesannya dirakit manual dan tahapnya dilaporkan terpisah.
                        const detail = err ?
                            (err.stack || err.message || String(err)) :
                            String(err);

                        console.error('[shipplan] gagal di tahap "' + stage + '":', detail);
                        console.error('[shipplan] objek error mentah:', err);

                        showToast(
                            dataLoaded ?
                            'Gagal menampilkan modal di tahap: ' + stage :
                            'Gagal memuat data shipment plan.',
                            'error'
                        );
                    });
            };
            /*end edit*/

            $('input[name="ctype"]').on('change', function() {
                const ctype = this.value;
                const volumeInput = $('input[data-field="volume"]');

                const volume = {
                    '20ft': 33,
                    '40ft': 67,
                    '40hc': 76
                };

                if (volume[ctype] !== undefined) {
                    volumeInput.val(volume[ctype]).prop('readonly', true);
                } else {
                    volumeInput.val('').prop('readonly', false).focus();
                }
            });

            /*ex-factory dan closetime*/
            window.onExdateChange = function() {
                if (!$('#exdate').data('datetimebox') || !$('#closetime').data('datetimebox')) {
                    console.warn('Widget datetimebox belum siap, skip onExdateChange.');
                    return;
                }

                const exdateValue = $('#exdate').datetimebox('getValue');

                if (!exdateValue) {
                    $('#closetime').datetimebox('setValue', '');
                    $('#closetime').datetimebox('disable');
                    return;
                }

                $('#closetime').datetimebox('enable');

                const minDate = typeof myparser3 === 'function' ? myparser3(exdateValue) : new Date(exdateValue);
                if (!minDate || isNaN(minDate.getTime())) return;
                minDate.setHours(0, 0, 0, 0);

                // paksa kalender internal di-refresh dengan disabledDate terbaru
                const closeCal = $('#closetime').datetimebox('calendar');
                if (closeCal.length) {
                    closeCal.calendar({
                        disabledDate: function(date) {
                            return date < minDate;
                        },
                    });
                }

                // simpan minDate ke elemen, dipakai validateAndCorrectCloseTime nanti
                document.getElementById('closetime').dataset.minDate = minDate.getTime();

                // const currentCloseValue = $('#closetime').datetimebox('getValue');
                // if (currentCloseValue) {
                //     const currentCloseDate = typeof myparser3 === 'function' ? myparser3(currentCloseValue) :
                //         new Date(currentCloseValue);
                //     if (currentCloseDate && !isNaN(currentCloseDate.getTime()) && currentCloseDate < minDate) {
                //         $('#closetime').datetimebox('setValue', '');
                //         if (typeof showToast === 'function') {
                //             showToast('Close Time direset karena lebih awal dari Ex-Factory yang baru.', 'error');
                //         }
                //     }
                // }
            };

            let isCorrectingCloseTime = false;

            function validateAndCorrectCloseTime(newValue) {
                if (isCorrectingCloseTime) return;
                if (!newValue) return;

                const exdateValue = $('#exdate').datetimebox('getValue');
                if (!exdateValue) return;

                const minDate = typeof myparser3 === 'function' ? myparser3(exdateValue) : new Date(exdateValue);
                if (!minDate || isNaN(minDate.getTime())) return;
                minDate.setHours(0, 0, 0, 0);

                const closeDate = typeof myparser3 === 'function' ? myparser3(newValue) : new Date(newValue);
                if (!closeDate || isNaN(closeDate.getTime())) return;

                if (closeDate < minDate) {
                    isCorrectingCloseTime = true;
                    $('#closetime').datetimebox('setValue', '');
                    isCorrectingCloseTime = false;

                    showToast('Close Time tidak boleh lebih awal dari Ex-Factory Date.', 'error');
                }
            }

            function convertDateFields(formData, fields) {
                fields.forEach(field => {
                    const el = document.querySelector(`[name="${field}"]`);

                    if (!el) return;

                    let value;

                    // datetimebox
                    if ($(el).hasClass('easyui-datetimebox')) {
                        value = $(el).datetimebox('getValue');
                    }
                    // datebox
                    else if ($(el).hasClass('easyui-datebox')) {
                        value = $(el).datebox('getValue');
                    }

                    if (!value) return;

                    const parts = value.split(' ');
                    const date = parts[0];
                    const time = parts[1];

                    const [day, month, year] = date.split('/');

                    // Jika ada jam → datetime
                    const converted = time ?
                        `${year}-${month}-${day} ${time}` :
                        `${year}-${month}-${day}`;

                    formData.set(field, converted);
                });
            }

            /* ================= SUBMIT (AJAX) ================= */
            // penting: definisikan form, csrfToken di scope yang SAMA dengan handler submit
            const form = document.getElementById('formCreateContainer');

            // input tidak valid di tab yang sedang tersembunyi membuat browser membatalkan
            // submit tanpa pesan apa pun -> pindah ke tab-nya supaya pesannya terlihat
            form.addEventListener('invalid', function(e) {
                const pane = e.target.closest('.mc-pane');
                if (!pane || pane.classList.contains('active')) return;

                const idx = order.indexOf(pane.dataset.pane);
                if (idx < 0) return;

                goTo(idx);
                setTimeout(() => e.target.reportValidity(), 50);
            }, true);
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearValidationErrors();

                const exportpk = currentExportpk;
                const formData = new FormData(form);

                // file foto dikirim manual, karena input file-nya di-clone dinamis dari <template>
                // dan sengaja tidak punya atribut name (biar tidak ikut FormData kosongan)
                // key foto pakai uid card -> terjemahkan ke nomor urut card saat ini
                const uidToIndex = {};
                containerList.querySelectorAll('.container-item').forEach((item, i) => {
                    uidToIndex[item.dataset.uid] = i;
                });

                containerFotoFiles.forEach((file, key) => {
                    const [uid, slot] = key.split(':');
                    if (uidToIndex[uid] === undefined) return; // card sudah dihapus
                    formData.append(`containers[${uidToIndex[uid]}][${slot}]`, file);
                });

                // tandai slot foto mana yang sengaja dikosongkan user (tanpa upload foto baru)
                containerFotoRemoved.forEach(key => {
                    const [uid, slot] = key.split(':');
                    if (uidToIndex[uid] === undefined) return;
                    formData.append(`containers[${uidToIndex[uid]}][${slot}_removed]`, '1');
                });

                convertDateFields(formData, [
                    'exdate',
                    'closetime',
                    'etdvess',
                    'etavess',
                    'contdate',
                    'actcontdate'
                ]);

                submitBtn.disabled = true;
                const originalSubmitHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

                const storeContainerUrl =
                    "{{ route('export.containers.store', ['exportpk' => '__EXPORTPK__']) }}";
                fetch(storeContainerUrl.replace('__EXPORTPK__', exportpk), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: formData,
                    })
                    .then(async (res) => {
                        const result = await res.json();
                        if (res.status === 422) return showValidationErrors(result.errors);
                        if (!res.ok) throw new Error(result.message || 'Terjadi kesalahan pada server');

                        // showToast(result.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalCreateContainer'))
                            .hide();
                        form.reset();
                        containerList.innerHTML = '';

                        sessionStorage.setItem('ldToastMessage', result.message ||
                            `Data berhasil di update.`);
                        sessionStorage.setItem('ldToastType', 'success');
                        location.reload();

                    })
                    .catch((err) => showToast(err.message, 'error'))
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalSubmitHtml;
                    });
            });

            function clearValidationErrors() {
                document.querySelectorAll('.mc-input.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                document.querySelectorAll('.mc-error-text').forEach(el => el.remove());
            }

            function showValidationErrors(errors) {
                Object.entries(errors).forEach(([key, messages]) => {
                    const parts = key.split('.');
                    let selectorName = parts[0];
                    for (let i = 1; i < parts.length; i++) selectorName += `[${parts[i]}]`;

                    const input = form.querySelector(`[name="${selectorName}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const err = document.createElement('div');
                        err.className = 'mc-error-text';
                        err.textContent = messages[0];
                        input.closest('.col-md-6, .col-md-7, .col-md-5, .col-12')?.appendChild(err);
                    }else {
                        // error tanpa input terkait (mis. container masih berisi carton)
                        showToast(messages[0], 'error');
                    }
                });

                const firstErrorKey = Object.keys(errors)[0] || '';
                if (firstErrorKey.startsWith('containers')) goTo(0);
                else if (firstErrorKey === 'etd' || firstErrorKey === 'eta') goTo(2);
            }

            // function showToast(message, type) {
            //     alert(message); // ganti dengan komponen toast/alert milikmu
            // }

            function setModalLockMode(isLocked) {
                // cukup kunci TAB NAV-nya saja, supaya user tidak bisa pindah ke tab 1-3
                document.querySelectorAll('#mcTabs .mc-tab').forEach(tab => {
                    if (tab.dataset.tab === 'schedule') return; // tab schedule selalu bisa diklik
                    tab.disabled = isLocked;
                    tab.classList.toggle('mc-tab-locked', isLocked);
                });

                // footer: kalau locked, hilangkan navigasi Kembali/Lanjut/step-dots, submit selalu tampil
                const footerDots = document.querySelector('.mc-step-dots');
                const prevBtn = document.getElementById('mcPrevBtn');
                const nextBtn = document.getElementById('mcNextBtn');
                const submitBtn = document.getElementById('mcSubmitBtn');

                if (isLocked) {
                    if (footerDots) footerDots.classList.add('d-none');
                    if (prevBtn) prevBtn.classList.add('d-none');
                    if (nextBtn) nextBtn.classList.add('d-none');
                    if (submitBtn) submitBtn.classList.remove('d-none');
                } else {
                    if (footerDots) footerDots.classList.remove('d-none');
                    if (prevBtn) prevBtn.classList.remove('d-none');
                    if (nextBtn) nextBtn.classList.remove('d-none');
                }
            }
        })();

        //SET INOVICE BARU
        (function() {
            const modalEl = document.getElementById('modalInvoice');
            const finalInput = document.getElementById('ssInvs');
            const selectEl = document.getElementById('ssInvsExisting');
            const genBtn = document.getElementById('btnGenerateInvs');
            const genHint = document.getElementById('ssInvsGenHint');
            const panelExisting = document.getElementById('ssInvsPanelExisting');
            const panelAuto = document.getElementById('ssInvsPanelAuto');

            const urlOptions = "{{ route('invoice.options') }}";
            const urlGenerate = "{{ route('invoice.generate') }}";

            function currentMode() {
                return modalEl.querySelector('input[name="ssInvsMode"]:checked').value;
            }

            async function loadExistingOptions() {
                selectEl.innerHTML = '<option value="">Memuat...</option>';
                try {
                    const res = await fetch(urlOptions, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const list = await res.json();

                    selectEl.innerHTML = '<option value="">-- Pilih nomor invoice --</option>';
                    list.forEach(function(item) {
                        const opt = document.createElement('option');
                        opt.value = item.invs;
                        opt.textContent = item.invs + ' (' + item.total_po + ' PO)';
                        selectEl.appendChild(opt);
                    });
                    if (!list.length) {
                        selectEl.innerHTML = '<option value="">Belum ada nomor invoice</option>';
                    }
                } catch (e) {
                    console.error('[invoice] gagal memuat daftar invoice:', e);
                    selectEl.innerHTML = '<option value="">Gagal memuat daftar</option>';
                }
            }

            async function generateNext() {
                genBtn.disabled = true;
                genHint.textContent = 'Menghitung nomor berikutnya...';
                try {
                    const res = await fetch(urlGenerate, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();

                    finalInput.value = data.invs;
                    genHint.textContent = data.last ?
                        'Nomor terakhir: ' + data.last :
                        'Belum ada invoice tahun ini, mulai dari 1';
                } catch (e) {
                    console.error('[invoice] gagal generate nomor:', e);
                    finalInput.value = '';
                    genHint.textContent = 'Gagal generate nomor invoice.';
                } finally {
                    genBtn.disabled = false;
                }
            }

            function applyMode() {
                const mode = currentMode();
                panelExisting.classList.toggle('d-none', mode !== 'existing');
                panelAuto.classList.toggle('d-none', mode !== 'auto');
                finalInput.value = '';

                if (mode === 'existing') {
                    selectEl.value = '';
                } else {
                    generateNext();
                }
            }

            modalEl.querySelectorAll('input[name="ssInvsMode"]').forEach(function(radio) {
                radio.addEventListener('change', applyMode);
            });

            selectEl.addEventListener('change', function() {
                finalInput.value = selectEl.value;
            });

            genBtn.addEventListener('click', generateNext);

            // setiap modal dibuka: reset ke mode "pilih yang sudah ada" + muat ulang daftar
            modalEl.addEventListener('show.bs.modal', function() {
                modalEl.querySelector('input[name="ssInvsMode"][value="existing"]').checked = true;
                loadExistingOptions();
                applyMode();
            });
        })();

        (function() {
            const modalEl = document.getElementById('modalDeletePo');
            const btnConfirm = document.getElementById('btnConfirmDeletePo');
            const btnCancel = document.getElementById('btnCancelDeletePo');
            const spinner = document.getElementById('dpoSpinner');
            const btnText = document.getElementById('dpoBtnText');
            const errorBox = document.getElementById('dpoError');

            let pendingPk = null;

            function setLoading(on) {
                btnConfirm.disabled = on;
                btnCancel.disabled = on;
                spinner.classList.toggle('d-none', !on);
                btnText.textContent = on ? 'Menghapus...' : 'Ya, Hapus';
            }

            function showError(msg) {
                errorBox.textContent = msg;
                errorBox.classList.remove('d-none');
            }

            // dipanggil dari tombol/menu: delete_po(row.poexportpk, row.POno)
            window.delete_po = function(poexportpk, pono) {
                pendingPk = poexportpk;
                document.getElementById('dpoPoNo').textContent = pono || '-';
                errorBox.classList.add('d-none');
                setLoading(false);

                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            };

            btnConfirm.addEventListener('click', function() {
                if (pendingPk === null) return;

                const tokenEl = document.querySelector('meta[name="csrf-token"]');
                errorBox.classList.add('d-none');
                setLoading(true);

                fetch("{{ route('export.po-delete') }}", {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': tokenEl ? tokenEl.content : ''
                        },
                        body: JSON.stringify({
                            poexportpk: pendingPk
                        })
                    })
                    .then(async function(res) {
                        const data = await res.json().catch(function() {
                            return {};
                        });
                        if (!res.ok || !data.success) {
                            throw new Error(data.message || ('HTTP ' + res.status));
                        }
                        return data;
                    })
                    .then(function(data) {
                        bootstrap.Modal.getInstance(modalEl).hide();
                        if (typeof showToast === 'function') showToast(data.message, 'success');
                        reloadSpGrid();
                    })
                    .catch(function(err) {
                        console.error('[delete_po]', err);
                        showError(err.message);
                        setLoading(false);
                    });
            });
        })();

        //ADD PO
        (function() {
            const csrf = () => document.querySelector('meta[name="csrf-token"]').content;
            let apoExportpk = null;

            function ensureModal() {
                if (document.getElementById('modalAddPo')) return;
                document.body.insertAdjacentHTML('beforeend', `
                    <div class="modal fade" id="modalAddPo" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Tambah PO ke <span id="apoIdship"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="text" id="apoSearch" class="form-control form-control-sm mb-2" placeholder="Cari POno...">
                            <div id="apoError" class="alert alert-danger py-2 d-none"></div>
                            <table class="table table-sm table-hover align-middle mb-0">
                            <thead><tr>
                                <th style="width:32px"><input type="checkbox" id="apoCheckAll"></th>
                                <th>POno</th><th>OP</th><th>PO Ref</th><th class="text-end">Carton</th><th class="text-end">Qty</th>
                            </tr></thead>
                            <tbody id="apoBody"><tr><td colspan="6" class="text-center text-muted">Memuat...</td></tr></tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <span class="me-auto small text-muted"><span id="apoCount">0</span> PO dipilih</span>
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary" id="btnSaveAddPo">
                            <span class="spinner-border spinner-border-sm d-none me-1" id="apoSpin"></span>Simpan
                            </button>
                        </div>
                        </div>
                    </div>
                    </div>`);

                let t;
                document.getElementById('apoSearch').addEventListener('input', function() {
                    clearTimeout(t);
                    t = setTimeout(loadList, 300);
                });
                document.getElementById('apoCheckAll').addEventListener('change', function() {
                    document.querySelectorAll('#apoBody .apo-chk').forEach(c => c.checked = this.checked);
                    updateCount();
                });
                document.getElementById('apoBody').addEventListener('change', updateCount);
                document.getElementById('btnSaveAddPo').addEventListener('click', save);
            }

            function updateCount() {
                document.getElementById('apoCount').textContent =
                    document.querySelectorAll('#apoBody .apo-chk:checked').length;
            }

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, m => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                } [m]));
            }

            async function loadList() {
                const body = document.getElementById('apoBody');
                body.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Memuat...</td></tr>';
                const q = encodeURIComponent(document.getElementById('apoSearch').value.trim());
                try {
                    const urlTpl = '{{ route('export.available-po', ['exportpk' => '__ID__']) }}';
                    const url = urlTpl.replace('__ID__', apoExportpk) + '?q=' + q;
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const json = await res.json();
                    const rows = json.data || [];
                    if (!rows.length) {
                        body.innerHTML =
                            '<tr><td colspan="6" class="text-center text-muted">Tidak ada PO yang tersedia.</td></tr>';
                        return;
                    }
                    body.innerHTML = rows.map((r, i) => `
                    <tr>
                    <td><input type="checkbox" class="apo-chk" data-i="${i}"></td>
                    <td>${esc(r.POno)}</td><td>${esc(r.OP)}</td><td>${esc(r.poref)}</td>
                    <td class="text-end">${r.ctn > 0 ? r.ctn : '<span class="badge bg-secondary">belum ada pack</span>'}</td>
                    <td class="text-end">${r.ctn > 0 ? Number(r.qty || 0).toLocaleString('id-ID') : '-'}</td>
                    </tr>`).join('');
                    body._rows = rows;
                    document.getElementById('apoCheckAll').checked = false;
                    updateCount();
                } catch (e) {
                    body.innerHTML =
                        '<tr><td colspan="6" class="text-center text-danger">Gagal memuat data.</td></tr>';
                }
            }

            async function save() {
                const body = document.getElementById('apoBody');
                const err = document.getElementById('apoError');
                const items = [];
                document.querySelectorAll('#apoBody .apo-chk:checked').forEach(c => {
                    const r = body._rows[c.dataset.i];
                    items.push({
                        POno: r.POno,
                        OP: r.OP || '',
                        poref: r.poref
                    });
                });
                err.classList.add('d-none');
                if (!items.length) {
                    err.textContent = 'Pilih minimal satu PO.';
                    err.classList.remove('d-none');
                    return;
                }

                const btn = document.getElementById('btnSaveAddPo');
                const spin = document.getElementById('apoSpin');
                btn.disabled = true;
                spin.classList.remove('d-none');
                try {
                    const res = await fetch('{{ route('export.po-add') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf()
                        },
                        body: JSON.stringify({
                            exportpk: apoExportpk,
                            items
                        })
                    });
                    const json = await res.json();
                    if (json.buyer) {
                        document.getElementById('apoIdship').textContent =
                            'SP' + String(apoExportpk).padStart(4, '0') + ' (' + json.buyer + ')';
                    }
                    if (json.success === false) {
                        body.innerHTML = '<tr><td colspan="6" class="text-center text-danger">' + esc(json
                            .message) + '</td></tr>';
                        return;
                    }
                    if (!res.ok || !json.success) {
                        err.textContent = json.message || 'Gagal menyimpan.';
                        err.classList.remove('d-none');
                        return;
                    }
                    bootstrap.Modal.getInstance(document.getElementById('modalAddPo')).hide();
                    showToast(json.message, 'success');
                    reloadSpGrid();
                } catch (e) {
                    err.textContent = 'Terjadi kesalahan jaringan.';
                    err.classList.remove('d-none');
                } finally {
                    btn.disabled = false;
                    spin.classList.add('d-none');
                }
            }

            // panggil dari menu row: add_po(exportpk)
            window.add_po = function(exportpk, buyer) {
                ensureModal();
                apoExportpk = exportpk;
                document.getElementById('apoIdship').textContent = 'SP' + String(exportpk).padStart(4, '0') + ' (' + buyer + ')';
                document.getElementById('apoSearch').value = '';
                document.getElementById('apoError').classList.add('d-none');
                const el = document.getElementById('modalAddPo');
                (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el)).show();
                loadList();
            };
        })();
    </script>
@endsection

@section('content')
    <div class="main-wrapper min-vh-100">
        <div class="container-fluid">
            <div class="sp-page ps-2 pt-3">

                {{-- ================= HEADER ================= --}}
                <div class="sp-page-header align-items-center">
                    <div>
                        <h4 class="sp-title">Shipment Plan</h4>
                    </div>
                    @if ((int) Session::get('userpk', 0) !== 18)
                    <button type="button" class="btn-create-container" onclick="openPackingPlanModal();">
                        <span class="cc-icon-wrap">
                            <i class="bi bi-box-seam cc-icon-box"></i>
                            <i class="bi bi-plus-circle-fill cc-icon-plus"></i>
                        </span>
                        <span class="cc-text">
                            <span class="cc-title">Create Shipment Plan</span>
                            <span class="cc-subtitle">Mulai loading baru.</span>
                        </span>
                        <i class="bi bi-arrow-right cc-arrow"></i>
                    </button>
                    @endif
                </div>

                {{-- ================= RINGKASAN MINI ================= --}}
                <div class="sp-stats-row">
                    <div class="sp-stat-card">
                        <div class="sp-stat-icon sp-stat-blue"><i class="bi bi-clipboard-data"></i></div>
                        <div>
                            <div class="sp-stat-value" id="statTotalPlan">0</div>
                            <div class="sp-stat-label">Total Shipment Plan</div>
                        </div>
                    </div>
                    <div class="sp-stat-card d-none">
                        <div class="sp-stat-icon sp-stat-orange"><i class="bi bi-box-seam"></i></div>
                        <div>
                            <div class="sp-stat-value" id="statTotalCtn">0</div>
                            <div class="sp-stat-label">Total Carton</div>
                        </div>
                    </div>
                    <div class="sp-stat-card">
                        <div class="sp-stat-icon sp-stat-green"><i class="bi bi-graph-up"></i></div>
                        <div>
                            <div class="sp-stat-value" id="statTotalQty">0</div>
                            <div class="sp-stat-label">Total Qty (pcs)</div>
                        </div>
                    </div>
                </div>

                {{-- ================= TOOLBAR ================= --}}
                <div class="sp-toolbar align-items-center justify-content-between">
                    <div class="d-flex">
                        <div class="sp-search-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="spSearch" placeholder="Cari ID Shipplan / Order No / PO / Peb No / vessname..."
                                onkeyup="onSpSearch()">
                        </div>

                        <div class="ps-2">
                            <input class="easyui-combobox sp-filter-select-cbb ps-2" name="spFilterBuyer" id="spFilterBuyer"
                                style="width:175px;"
                                data-options="
                                valueField:'byrnm',
                                textField:'byrnm',
                                url:'{{ route('getByr') }}',
                                method:'get',
                                prompt:'Pilih Buyer',
                                panelHeight:'auto',
                                panelMaxHeight:200,
                                onChange:onSpSearch
                            ">
                        </div>

                        <div class="ps-2">
                            <input class="easyui-combobox sp-filter-select-cbb" name="spFilterVassal" id="spFilterVassal"
                                style="width:150px;"
                                data-options="
                                valueField:'vassalpk',
                                textField:'typenm',
                                url:'{{ route('getVassal') }}',
                                method:'get',
                                prompt:'Shipment Method',
                                panelHeight:'auto',
                                panelMaxHeight:200,
                                onChange:onSpSearch
                            ">
                        </div>

                        <div class="ps-2">
                            <input id="filterday" name="filterday" class="easyui-combobox sp-filter-select-cbb"
                                editable="false" singleSelect="true"
                                data-options="valueField:'value', textField:'text', url:'{{ route('filterday') }}', prompt:'Ex-Factory', panelHeight:'auto', onChange:onSpSearch"
                                method="get" style="width:150px;">
                        </div>
                        <div class="ps-2">
                            <select class="easyui-combobox sp-filter-select-cbb" name="spFilterStatus" id="spFilterStatus"
                                style="width:150px;"
                                data-options="
                                prompt:'Status',
                                panelHeight:'auto',
                                panelMaxHeight:200,
                                onChange:onSpSearch
                            ">
                                <option value=""></option>
                                <option value="1">First Plan</option>
                                <option value="2">Forwarder Confirm</option>
                                <option value="3">Complete</option>
                                <option value="4">Cancel Shipment</option>
                            </select>
                        </div>

                        <div class="ps-2">
                            <button type="button" class="sp-refresh-btn" onclick="reloadSpGrid()" title="Muat ulang">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <div class="ps-2">
                            <button type="button" class="sp-refresh-btn" onclick="exportSpExcel()"
                                title="Export List Shipment Plan">
                                <img src="{{ asset('public/css/images/iconExcel.png') }}" alt="Export Excel">
                            </button>
                        </div>
                    </div>

                    <div class="pe-2">
                        <!-- SORT FILTER -->
                        <div id="sortlist-grup" class="p-1 pt-4 pb-0 responsive">
                            <div class="btn-group">
                                <input type="hidden" id="sortlistByDate" name="sortlistByDate" size="5" readonly>
                                <div class="btn-group">
                                    <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0"
                                        data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
                                        <img src="{{ asset('public/css/images/sort.png') }}" alt=""
                                            class="imgsort">
                                        <span id="newest">Newest Entry</span>
                                        <span id="oldest" class="d-none">Oldest Entry</span>
                                    </button>
                                    <ul id="menu-sortlist"
                                        class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                                        <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2"
                                                onclick="sortlistByDate(11)">Newest Entry</a></li>
                                        <!-- <li><hr class="dropdown-divider mt-0 mb-0"></li> -->
                                        <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2"
                                                onclick="sortlistByDate(12)">Oldest Entry</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= DATAGRID (skin: card list) ================= --}}
                <div class="sp-grid-wrap">
                    <table id="dgShipmentPlan">
                    </table>
                </div>

                <div id="spActionMenu" class="sp-action-menu">
                    <button type="button" class="sp-action-item" onclick="onActionClick('view')">
                        <i class="bi bi-eye"></i> View Details
                    </button>
                    <button type="button" class="sp-action-item" onclick="onActionClick('export_excel')">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export Packing List
                    </button>
                    <button type="button" class="sp-action-item" onclick="onActionClick('export_si')"">
                        <i class="bi bi-file-earmark-richtext"></i> Shipping Instruction
                    </button>
                    <button type="button" id="menu_add_pono" class="sp-action-item" onclick="onActionClick('add_pono')">
                        <i class="bi bi-plus-square"></i> Add PO Number
                    </button>
                    <button type="button" id="menu_publish" class="sp-action-item" onclick="onActionClick('publish')">
                        <i class="bi bi-send-check"></i> Publish
                    </button>
                    <button type="button" id="menu_unpublish" class="sp-action-item d-none"
                        onclick="onActionClick('unpublish')">
                        <i class="bi bi-eye-slash"></i> Unpublish
                    </button>
                    <button type="button" id="unlocked" class="sp-action-item d-none" onclick="onActionClick('lock')">
                        <i class="bi bi-unlock"></i> Lock Shipment
                    </button>
                    <button type="button" id="locked" class="sp-action-item sp-action-disabled d-none">
                        <i class="bi bi-lock"></i> Locked
                    </button>
                    <button type="button" id="complete_shipment" class="sp-action-item d-none"
                        onclick="onActionClick('complete_shipment')">
                        <i class="bi bi-truck"></i> Complete Shipment
                    </button>
                    <div class="sp-action-divider"></div>
                    <button type="button" id="remove" class="sp-action-item sp-action-danger"
                        onclick="onActionClick('remove')">
                        <i class="bi bi-exclamation-octagon-fill"></i> Cancel Shipment
                    </button>
                    <button type="button" id="cancelled" class="sp-action-item sp-action-disabled d-none">
                        <i class="bi bi-x-octagon-fill"></i> Cancelled
                    </button>
                </div>

                <!-- Toaster -->
                <div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 1080;">
                    <div id="ldToast" class="toast ld-toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="ld-toast-body">
                            <span class="ld-toast-icon"><i class="bi bi-check-lg"></i></span>
                            <div class="ld-toast-text">
                                <div class="ld-toast-title">Success</div>
                                <div class="ld-toast-message" id="ldToastMessage">
                                </div>
                            </div>
                            <button type="button" class="ld-toast-close" data-bs-dismiss="toast" aria-label="Close">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- menu header -->
    <div class="header d-flex p-4 justify-content-between align-items-center w-100 d-none" id="myHeader2" style="">
        <div id="count2" class="d-none"></div>
        <div class="d-flex gap-4">
            <div class="delete" id="set_peb" style="width:auto;display:none;">
                <a href="javascript:void(0)" plain="true" onclick="set_peb();"
                    style="color: #FFFFFF; text-decoration: none;">
                    Input PEB
                </a>
            </div>
            <div class="delete" id="set_inv" style="width:auto;display:none;">
                <a href="javascript:void(0)" plain="true" onclick="set_inv();"
                    style="color: #FFFFFF; text-decoration: none;">
                    Create Invoice
                </a>
            </div>
            <div class="delete" id="mclose" style="width:auto;">
                <a href="javascript:void(0)" plain="true" onclick="close_menu()"
                    style="color: #FFFFFF; text-decoration: none;"> Close menu
                </a>
            </div>
        </div>
    </div>


    @include('menu.exim-export.shipplan.modal.modalPackingPlan')
    @include('menu.exim-export.shipplan.modal.modalContainer')
@endsection
