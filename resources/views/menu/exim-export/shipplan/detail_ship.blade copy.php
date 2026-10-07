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
            /* Blue */
            background: #359DD9;
            /* W - Drop Shadow */
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

        .border-1 {
            border: 1px solid red;
        }

        .btn-black {
            color: #fff;
            background: #000;
        }

        .btn-transparent {
            color: #000;
            background: transparent;
        }

        .rounded {
            border-radius: 8px;
        }

        .bg-white {
            color: #000;
            background: #fff;
            border: 1px solid black;
        }

        .input-group-search {
            width: 160px;
        }

        th {
            text-align: center !important;
        }

        thead {
            margin-bottom: 20px;
        }

        table {
            margin-top: 0 !important;
            background-color: #fff !important;
        }

        table.table-custom {
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .btn-outline-dark:hover {
            background-color: transparent;
            color: #000;
        }

        table.table-custom tbody tr,
        table.table-custom tbody tr td {
            border-radius: 8px;
        }

        table.table-custom tbody tr {
            box-shadow: 0px 0px 16px -4px #0000001F;
        }

        table.table-custom thead tr,
        table.table-custom thead tr th {
            border-radius: 8px;
            margin-bottom: 3px;
        }

        table.table-custom thead tr {
            box-shadow: 0px 0px 16px -4px #0000001F;
        }

        .blue {
            color: #359DD9;
        }

        .red {
            color: red;
        }

        .pointer {
            cursor: pointer;
        }

        .grey {
            color: rgba(0, 0, 0, 0.38);
        }

        .bg-black {
            color: #fff;
            background: #000;
        }

        .font-small {
            font-size: 12px;
        }

        .height_input {
            height: 32px;
        }

        .bg-light-grey {
            background: var(--Text-Light-grey, rgba(0, 0, 0, 0.38));
        }

        .bg-dark-grey {
            background: var(--Text-Grey, rgba(0, 0, 0, 0.60));
        }

        .color-view-blue {
            color: rgb(53, 157, 217);
        }

        .sticky {
            position: fixed;
            top: 0;
            width: 100%;
        }

        .sticky+.contenta {
            padding-top: 102px;
        }

        .datagrid-cell {
            /* font-family: 'Arial'; */
            font-style: normal;
            font-weight: 400;
            font-size: 13px;
            line-height: 18px;
            white-space: normal;
        }

        .datagrid-body {
            background: transparent !important;
            overflow-y: auto !important;
            max-height: 100%;
        }

        .text-biru {
            color: #359DD9;
            font-weight: 700;
            cursor: pointer;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        .warning-blink {
            font-size: 12px;
            font-weight: bold;
            color: red;
            text-align: center;
            animation: blink 2s infinite;
        }

        .warning-blink-1 {
            font-size: 14px;
            font-weight: bold;
            color: red;
            text-align: center;
        }

        .font-warning {
            font-size: 12px;
            font-weight: bold;
            color: red;
            text-align: left;
        }

        /* new */
        .shipment-card {
            background: #ffffff;
            border: 1px solid #EAECF0;
            border-radius: 16px;
            padding: 15px 32px 32px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06), 0 1px 2px rgba(16, 24, 40, .04);
            max-width: 1400px;
        }

        /* header */
        .shipment-header {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 5px;
        }

        .btn-back {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid #E4E7EC;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #101828;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
            flex-shrink: 0;
        }

        .btn-back:hover {
            background: #F2F4F7;
            transform: translateX(-2px);
        }

        .shipment-title {
            font-weight: 700;
            font-size: 1.25rem;
            color: #101828;
            margin: 0;
        }

        .shipment-title2 {
            font-weight: 700;
            font-size: 1rem;
            color: hsl(228, 2%, 57%);
            margin: 0;
        }

        /* tombol edit & save */
        .header-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 40px;
            padding: 0 18px;
            border-radius: 8px;
            font-size: .92rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background .15s ease, transform .1s ease, box-shadow .15s ease;
        }

        .btn-action:active {
            transform: scale(.97);
        }

        .btn-edit {
            background: #fff;
            border-color: #D0D5DD;
            color: #344054;
        }

        .btn-edit:hover {
            background: #F9FAFB;
            border-color: #98A2B3;
        }

        .btn-save {
            background: #12A150;
            color: #fff;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .06);
        }

        .btn-save:hover {
            background: #0E8A44;
        }

        /* satu baris field: chip icon + label + input */
        .field-row {
            display: grid;
            grid-template-columns: 34px 145px 1fr;
            align-items: center;
            column-gap: 12px;
            margin-bottom: 14px;
        }

        /* chip icon bulat berwarna */
        .field-chip {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            transition: transform .15s ease;
        }

        .field-row:hover .field-chip {
            transform: scale(1.08);
        }

        .chip-blue {
            background: #EAF2FF;
            color: #2F6FED;
        }

        .chip-purple {
            background: #F1ECFE;
            color: #7C4DFF;
        }

        .chip-teal {
            background: #E6F7F5;
            color: #12B5A6;
        }

        .chip-pink {
            background: #FDECF3;
            color: #E4589A;
        }

        .chip-orange {
            background: #FFF4E5;
            color: #F79009;
        }

        .chip-green {
            background: #E9F9EE;
            color: #12A150;
        }

        .chip-amber {
            background: #FEF6E7;
            color: #B98900;
        }

        .chip-slate {
            background: #F1F2F4;
            color: #475467;
        }

        /* label */
        .field-label {
            font-size: .92rem;
            font-weight: 500;
            color: #344054;
            line-height: 1.25;
        }

        .field-label2 {
            max-width: 135px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* input & select seragam */
        .field-input,
        select.field-input {
            width: 100%;
            height: 38px;
            border: 1px solid #D0D5DD;
            border-radius: 8px;
            padding: 0 12px;
            font-size: .92rem;
            color: #101828;
            background: #fff;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .field-input:hover:not(:disabled) {
            border-color: #98A2B3;
        }

        .field-input:focus {
            border-color: #2F6FED;
            box-shadow: 0 0 0 3px rgba(47, 111, 237, .15);
        }

        /* tampilan saat mode lihat (disabled) supaya tetap terbaca jelas, bukan abu pucat */
        .field-input:disabled {
            background: #F9FAFB;
            border-style: dashed;
            color: #475467;
            cursor: default;
        }

        select.field-input {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23667085' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            padding-right: 32px;
        }

        @media (max-width: 767px) {
            .field-row {
                grid-template-columns: 30px 120px 1fr;
            }

            .shipment-card {
                padding: 20px;
            }

            .shipment-header {
                flex-wrap: wrap;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        /*Button container*/
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
            background: linear-gradient(135deg, #2F6FED 0%, #1E4FD1 100%);
            box-shadow: 0 4px 14px rgba(47, 111, 237, .35), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-create-container:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(47, 111, 237, .45), inset 0 1px 0 rgba(255, 255, 255, .2);
        }

        .btn-create-container:active {
            transform: translateY(0) scale(.98);
        }

        /* icon container + badge plus kekinian, saling tumpuk */
        .cc-icon-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .cc-icon-box {
            font-size: 19px;
            color: #fff;
            transition: transform .35s cubic-bezier(.34, 1.56, .64, 1);
        }

        .btn-create-container:hover .cc-icon-box {
            transform: rotate(-8deg) scale(1.05);
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

        /* teks dua baris: judul + sub label */
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
            letter-spacing: .2px;
        }

        .cc-subtitle {
            font-size: .74rem;
            color: rgba(255, 255, 255, .75);
            font-weight: 400;
        }

        /* panah kanan meluncur dikit saat hover */
        .cc-arrow {
            font-size: 16px;
            color: rgba(255, 255, 255, .85);
            transition: transform .2s ease;
            margin-left: 2px;
        }

        .btn-create-container:hover .cc-arrow {
            transform: translateX(4px);
        }

        /* efek ripple saat diklik */
        .cc-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .55);
            transform: scale(0);
            animation: cc-ripple-anim .55s ease-out;
            pointer-events: none;
        }

        @keyframes cc-ripple-anim {
            to {
                transform: scale(3.2);
                opacity: 0;
            }
        }

        /* versi compact, kalau butuh tombol kecil (mis. di toolbar/tabel) */
        .btn-create-container.compact {
            padding: 8px 16px 8px 10px;
            border-radius: 10px;
            gap: 10px;
        }

        .btn-create-container.compact .cc-icon-wrap {
            width: 30px;
            height: 30px;
            border-radius: 8px;
        }

        .btn-create-container.compact .cc-icon-box {
            font-size: 15px;
        }

        .btn-create-container.compact .cc-subtitle {
            display: none;
        }

        .btn-create-container.compact .cc-title {
            font-size: .88rem;
        }

        /* ============ MODAL SHELL ============ */
        #modalCreateContainer .modal-content.mc-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(16, 24, 40, .25);
            animation: mcPopIn .28s cubic-bezier(.34, 1.56, .64, 1);
        }

        @keyframes mcPopIn {
            from {
                transform: scale(.92) translateY(14px);
                opacity: 0;
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

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

        .mc-empty-hint {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 50px 20px;
            color: #98A2B3;
        }

        .mc-empty-hint i {
            font-size: 34px;
            margin-bottom: 10px;
            color: #C7D7FE;
        }

        .mc-empty-hint p {
            margin: 0;
            font-size: .86rem;
        }

        .datagrid-view2,
        .datagrid-view {
            height: 100% !important;
            margin-left: 0 !important;
            padding-left: 0 !important;
        }

        @media (max-width: 576px) {

            #modalLoadCarton .modal-dialog {
                max-height: 96vh;
            }

            #modalLoadCarton .modal-content.ld-modal {
                max-height: 96vh;
            }

            .mc-header,
            .mc-body,
            .mc-footer {
                padding-left: 18px;
                padding-right: 18px;
            }

            .mc-tabs {
                overflow-x: auto;
            }

            .mc-footer {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }

            .mc-footer .d-flex {
                justify-content: space-between;
            }
        }

        .mc-input.is-invalid {
            border-color: #D92D20 !important;
            box-shadow: 0 0 0 3px rgba(217, 45, 32, .12) !important;
        }

        .mc-error-text {
            color: #D92D20;
            font-size: .76rem;
            margin-top: 4px;
        }

        .fwd-combo+.combo {
            border: 1px solid #000 !important;
            border-radius: 10px;
        }

        /*CONTAINER LOADING*/
        /* ===================== CARD CONTAINER ===================== */
        .ld-card {
            background: #fff;
            border: 1px solid #EAECF0;
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06);
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: box-shadow .18s ease, transform .18s ease;
        }

        .ld-card:hover {
            box-shadow: 0 8px 20px rgba(16, 24, 40, .1);
            transform: translateY(-2px);
        }

        .ld-card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .ld-badge {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .72rem;
            font-weight: 700;
            background: #EAF2FF;
            color: #2F6FED;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .ld-badge.volume {
            font-family: 'IBM Plex Mono', monospace;
            font-size: .72rem;
            font-weight: 700;
            background: #f9f7f7;
            color: #ed2f2f;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .ld-status {
            font-size: .72rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
            text-transform: capitalize;
        }

        .ld-status-loading {
            background: #FFF4E5;
            color: #B98900;
        }

        .ld-status-sealed {
            background: #E9F9EE;
            color: #12A150;
        }

        .ld-status-shipped {
            background: #F1F2F4;
            color: #475467;
        }

        /* ===================== VISUAL CONTAINER (TRIGGER) ===================== */
        .ld-container-visual {
            border: none;
            background: linear-gradient(135deg, #DCEBFF, #EAF2FF);
            border-radius: 12px;
            padding: 14px 10px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            width: 100%;
            transition: background .2s ease, transform .15s ease;
        }

        .ld-container-visual:hover {
            background: linear-gradient(135deg, #CBDEFF, #DCEBFF);
            transform: scale(1.015);
        }

        .ld-container-visual:active {
            transform: scale(.985);
        }

        .ld-container-locked {
            cursor: not-allowed !important;
            opacity: 0.55;
            filter: grayscale(0.8);
        }

        .ld-container-locked:hover {
            transform: none !important;
        }

        .ld-container-locked .ld-visual-label {
            color: #98a2b3;
        }

        .ld-container-svg {
            width: 100%;
            max-width: 190px;
            height: auto;
        }

        .ld-svg-body {
            fill: #2F6FED;
        }

        .ld-svg-ribs {
            fill: none;
            stroke: rgba(255, 255, 255, .25);
            stroke-width: 2;
            stroke-dasharray: 2 8;
        }

        .ld-svg-door {
            fill: #EAF2FF;
            stroke: #1E4FD1;
            stroke-width: 1.5;
            stroke-dasharray: 5 4;
            transition: fill .2s ease;
        }

        .ld-container-visual:hover .ld-svg-door {
            fill: #fff;
        }

        .ld-svg-plus {
            font-size: 26px;
            font-weight: 700;
            fill: #2F6FED;
        }

        .ld-visual-label {
            font-size: .74rem;
            font-weight: 600;
            color: #2F6FED;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* ===================== CARD BODY ===================== */
        .ld-card-body {
            padding: 12px 2px 4px;
        }

        .ld-mono {
            font-family: 'IBM Plex Mono', monospace;
        }

        .ld-container-no {
            font-weight: 700;
            font-size: .95rem;
            color: #101828;
        }

        .ld-meta {
            font-size: .8rem;
            color: #98A2B3;
            margin-bottom: 10px;
        }

        .ld-progress-row {
            display: flex;
            justify-content: space-between;
            font-size: .76rem;
            font-weight: 600;
            color: #475467;
            margin-bottom: 5px;
        }

        .ld-progress-track {
            height: 7px;
            background: #EAECF0;
            border-radius: 999px;
            overflow: hidden;
        }

        .ld-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2F6FED, #34D399);
            border-radius: 999px;
            transition: width .35s ease;
        }

        /* ===================== BUTTON MUAT ===================== */
        .ld-btn-action {
            margin-top: 10px;
            width: 100%;
            border: 1.5px solid #2F6FED;
            background: #fff;
            color: #2F6FED;
            font-weight: 700;
            font-size: .85rem;
            padding: 9px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            cursor: pointer;
            transition: background .15s ease, color .15s ease;
        }

        .ld-btn-action:hover {
            background: #2F6FED;
            color: #fff;
        }

        .ld-btn-locked {
            background-color: #f2f4f7 !important;
            color: #98a2b3 !important;
            border-color: #d0d5dd !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }

        .ld-btn-locked:hover {
            background-color: #f2f4f7 !important;
            color: #98a2b3 !important;
        }

        .ld-empty {
            text-align: center;
            padding: 40px 20px;
            color: #98A2B3;
        }

        .ld-empty i {
            font-size: 32px;
            color: #C7D7FE;
            margin-bottom: 8px;
            display: block;
        }

        .ld-container-image {
            width: 250px;
            height: 200px;
            object-fit: contain;
            display: block;
        }

        /* ===================== MODAL ===================== */
        #modalLoadCarton .modal-dialog {
            max-height: 90vh;
            display: flex;
            align-items: center;
        }

        #modalLoadCarton .modal-content.ld-modal {
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            border: none;
            border-radius: 18px;
            overflow: hidden;
            /* biar radius tetap kepotong rapi, scroll ada di dalam modal-body */
        }

        @keyframes ldPop {
            from {
                transform: scale(.94) translateY(10px);
                opacity: 0;
            }

            to {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .ld-modal-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            background: linear-gradient(135deg, #2F6FED, #1E4FD1);
        }

        /* header & toolbar tidak boleh ikut mengecil/scroll */
        .ld-modal-header,
        .ld-modal-toolbar,
        .ld-modal-footer {
            flex-shrink: 0;
        }

        .ld-modal-header-icon {
            width: 42px;
            height: 42px;
            border-radius: 11px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 19px;
            flex-shrink: 0;
        }

        .ld-modal-title {
            color: #fff;
            font-weight: 700;
            margin: 0;
            font-size: 1.05rem;
        }

        .ld-modal-subtitle {
            color: rgba(255, 255, 255, .8);
            font-size: .8rem;
            margin: 2px 0 0;
        }

        .ld-close {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, .16);
            color: #fff;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }

        .ld-close:hover {
            background: rgba(255, 255, 255, .28);
            transform: rotate(90deg);
        }

        .ld-modal-toolbar {
            display: flex;
            gap: 10px;
            padding: 16px 24px 0;
        }

        .ld-search-box {
            flex: 1;
            position: relative;
        }

        .ld-search-box i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #98A2B3;
            font-size: .85rem;
        }

        .ld-search-box input {
            width: 100%;
            height: 40px;
            border: 1.5px solid #D0D5DD;
            border-radius: 10px;
            padding: 0 14px 0 36px;
            font-size: .87rem;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .ld-search-box input:focus {
            border-color: #2F6FED;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, .13);
        }

        .ld-select-all {
            border: 1.5px solid #D0D5DD;
            background: #fff;
            border-radius: 10px;
            padding: 0 16px;
            font-size: .82rem;
            font-weight: 600;
            color: #344054;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease;
        }

        .ld-select-all:hover {
            background: #F9FAFB;
            border-color: #98A2B3;
        }

        .ld-select-all.active {
            background: #EAF2FF;
            border-color: #2F6FED;
            color: #2F6FED;
        }

        /* body yang scroll, sisanya diisi datagrid */
        .ld-modal-body {
            flex: 1 1 auto;
            min-height: 0;
            /* penting: supaya flex child bisa mengecil & scroll, bukan overflow ke luar */
            padding: 14px 5px 1px;
            overflow: hidden;
            /* scroll sesungguhnya terjadi di dalam .datagrid-body, bukan di sini */
            display: flex;
            flex-direction: column;
        }

        .ld-loading-state {
            text-align: center;
            padding: 46px 10px;
            color: #98A2B3;
        }

        .ld-spinner {
            color: #2F6FED;
            width: 2rem;
            height: 2rem;
            margin-bottom: 10px;
        }

        /* .ld-carton-list {
                                                                                                                                                                                                                                                                                                                                        display: flex;
                                                                                                                                                                                                                                                                                                                                        flex-direction: column;
                                                                                                                                                                                                                                                                                                                                        gap: 35px;
                                                                                                                                                                                                                                                                                                                                    }

                                                                                                                                                                                                                                                                                                                                    .ld-carton-row {
                                                                                                                                                                                                                                                                                                                                        display: flex;
                                                                                                                                                                                                                                                                                                                                        align-items: center;
                                                                                                                                                                                                                                                                                                                                        gap: 12px;
                                                                                                                                                                                                                                                                                                                                        border: 1.5px solid #EAECF0;
                                                                                                                                                                                                                                                                                                                                        border-radius: 12px;
                                                                                                                                                                                                                                                                                                                                        padding: 10px 14px;
                                                                                                                                                                                                                                                                                                                                        cursor: pointer;
                                                                                                                                                                                                                                                                                                                                        transition: border-color .15s ease, background .15s ease;
                                                                                                                                                                                                                                                                                                                                        background-color: #03d106;
                                                                                                                                                                                                                                                                                                                                    } */

        .ld-carton-row:hover {
            border-color: #C7D7FE;
            background: #F9FBFF;
        }

        .ld-carton-row:has(.ld-carton-checkbox:checked) {
            border-color: #2F6FED;
            background: #EAF2FF;
        }

        .ld-carton-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #2F6FED;
            flex-shrink: 0;
            cursor: pointer;
        }

        .ld-carton-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #FFF4E5;
            color: #B98900;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        .ld-carton-info {
            flex: 1;
            min-width: 0;
        }

        .ld-carton-no {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 700;
            font-size: .85rem;
            color: #101828;
        }

        .ld-carton-sub {
            font-size: .76rem;
            color: #98A2B3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .ld-carton-metrics {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            flex-shrink: 0;
        }

        .ld-carton-qty {
            font-size: .8rem;
            font-weight: 700;
            color: #344054;
        }

        .ld-carton-weight {
            font-size: .72rem;
            color: #98A2B3;
        }

        .ld-modal-footer {
            padding: 14px 24px;
            border-top: 1px solid #EAECF0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ld-selected-summary {
            display: flex;
            flex-direction: column;
        }

        .ld-selected-badge {
            font-weight: 700;
            font-size: .88rem;
            color: #2F6FED;
        }

        .ld-selected-sub {
            font-size: .74rem;
            color: #98A2B3;
        }

        .ld-btn {
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

        .ld-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .ld-btn-ghost {
            background: transparent;
            color: #667085;
        }

        .ld-btn-ghost:hover {
            background: #F9FAFB;
        }

        .ld-btn-primary {
            background: #2F6FED;
            color: #fff;
            box-shadow: 0 2px 8px rgba(47, 111, 237, .35);
        }

        .ld-btn-primary:hover:not(:disabled) {
            background: #1E4FD1;
        }

        /* ... (style ld-modal, ld-modal-header, dst yang sudah ada sebelumnya tetap dipakai) ... */

        /* .modal-xl .ld-modal-body {
                                                                                                                                                                                                                                                                                                                                                    padding: 14px 24px 10px;
                                                                                                                                                                                                                                                                                                                                                    height: 500px !important;
                                                                                                                                                                                                                                                                                                                                                } */

        .ld-refresh-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 1.5px solid #D0D5DD;
            background: #fff;
            color: #475467;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease, transform .3s ease;
        }

        .ld-refresh-btn:hover {
            background: #F9FAFB;
        }

        .ld-refresh-btn:active i {
            transform: rotate(180deg);
        }

        .ld-refresh-btn i {
            transition: transform .3s ease;
        }

        /* override tampilan datagrid easyui biar senada dengan design system */
        #dgDetailCarton,
        .datagrid-view,
        .datagrid-wrap {
            border: none !important;
            background: transparent !important;
        }

        #dgDetailCarton {
            flex: 1;
            min-height: 0;
        }

        .datagrid-view {
            border: 1px solid #EAECF0 !important;
            border-left: 1px solid #EAECF0 !important;
            border-right: 1px solid #0758fb !important;
            border-radius: 12px;
            overflow: hidden;
            padding-left: 0px;
        }

        /*tambahan*/

        /* .datagrid-header {
                                                                                                                                                                                                                        display: none !important;
                                                                                                                                                                                                                    } */

        .datagrid-btable {
            border-collapse: separate !important;
            border-spacing: 0 10px !important;
        }


        .datagrid-row {
            cursor: pointer;
        }

        .datagrid-row td {
            border-top: 1.5px solid #EAECF0 !important;
            border-bottom: 1.5px solid #EAECF0 !important;
            background: #fff !important;
            padding: 12px 14px !important;
            vertical-align: middle !important;

        }

        /* sudut kiri kartu (kolom checkbox) */
        .datagrid-row td:first-child {
            border-left: 1.5px solid #EAECF0 !important;
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
            width: 48px !important;
            text-align: center;
        }

        /* sudut kanan kartu (kolom info) */
        .datagrid-row td:last-child {
            border-right: 1.5px solid #EAECF0 !important;
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
        }

        .datagrid-row:hover td {
            border-color: #C7D7FE !important;
            background: #F9FBFF !important;
        }

        .datagrid-row-checked td {
            border-color: none !important;
            background: #EAF2FF !important;
        }

        /* checkbox biar lebih besar & berwarna */
        .datagrid-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #2F6FED;
            cursor: pointer;
        }

        /* ================= ISI KARTU (dari formatter) ================= */
        .dg-card {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .dg-card-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background: #FFF4E5;
            color: #B98900;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .dg-card-main {
            flex: 1;
            min-width: 0;
        }

        .dg-card-no {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 700;
            font-size: .88rem;
            color: #101828;
        }

        .dg-card-style {
            font-size: .78rem;
            color: #98A2B3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dg-card-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0px;
            flex-shrink: 0;
        }

        .dg-card-meta2 {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .dg-card-qty {
            font-size: .82rem;
            font-weight: 700;
            color: #344054;
        }

        .dg-card-status {
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 999px;
            text-transform: capitalize;
        }

        .dg-card-status.ready {
            background: #E9F9EE;
            color: #12A150;
        }

        .dg-card-status.held {
            background: #FEF3F2;
            color: #D92D20;
        }

        .dg-card-status.default {
            background: #F1F2F4;
            color: #667085;
        }

        .dg-card-status.Complete {
            background: #1ede3e;
            color: #fafafa;
        }

        .dg-card-status.Segel {
            background: #000e00;
            color: #ffffff;
        }

        .dg-card-session {
            font-size: .68rem;
            font-weight: 700;
            padding: 2px 9px;
            border-radius: 999px;
            text-transform: capitalize;
            background: #e9def2;
            color: #a562fc;
        }

        /* scrollbar body datagrid biar rapi */
        .datagrid-body::-webkit-scrollbar {
            width: 8px;
        }

        .datagrid-body::-webkit-scrollbar-thumb {
            background: hsl(0, 2%, 39%);
            border-radius: 999px;
        }

        .status-chip {
            display: inline-block;
            font-size: .72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
        }

        .status-chip.ready {
            background: #E9F9EE;
            color: #12A150;
        }

        .status-chip.held {
            background: #FEF3F2;
            color: #D92D20;
        }

        .dg-empty-msg {
            position: absolute;
            top: 60px;
            left: 0;
            right: 0;
            text-align: center;
            color: #98A2B3;
            padding: 30px 10px;
        }

        .dg-empty-msg i {
            font-size: 32px;
            color: #C7D7FE;
            margin-bottom: 8px;
            display: block;
        }

        .dg-empty-msg p {
            margin: 0;
            font-size: .86rem;
        }

        .datagrid-view {
            position: relative;
        }

        .dg-card-sizes {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 6px;
        }

        .dg-size-item {
            display: inline-block;
            padding: 3px 8px;
            background: #f1f1f1;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            color: #000;
        }

        .dg-card-disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .dg-check-disabled {
            background: #F1F2F4 !important;
            border-color: #D0D5DD !important;
            color: #98A2B3 !important;
        }

        .dg-card-status.loaded {
            background: #F1F2F4;
            color: #667085;
        }

        .ld-card-row-disabled td {
            cursor: not-allowed;
            background: #FAFAFA !important;
        }

        .ld-card-row-disabled:hover td {
            border-color: #EAECF0 !important;
            background: #FAFAFA !important;
            /* override hover, tetap abu-abu, tidak biru */
        }

        /*tambahan diplay container*/
        .ld-loaded-section {
            margin: 10px 0 12px;
        }

        .ld-loaded-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #F9FAFB;
            border: 1px solid #EAECF0;
            border-radius: 9px;
            padding: 8px 12px;
            cursor: pointer;
            font-size: .8rem;
            font-weight: 600;
            color: #475467;
            transition: background .15s ease;
        }

        .ld-loaded-toggle:hover {
            background: #F2F4F7;
        }

        .ld-loaded-toggle i.bi-box-seam {
            color: #B98900;
        }

        .ld-loaded-toggle span {
            flex: 1;
            text-align: left;
        }

        .ld-loaded-chevron {
            transition: transform .2s ease;
        }

        .ld-loaded-toggle.open .ld-loaded-chevron {
            transform: rotate(180deg);
        }

        .ld-loaded-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
            padding: 10px;
            background: #FCFCFD;
            border: 1px dashed #EAECF0;
            border-radius: 9px;
            max-height: 140px;
            overflow-y: auto;
        }

        .ld-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #EAF2FF;
            border: 1px solid #C7D7FE;
            border-radius: 999px;
            padding: 4px 6px 4px 10px;
            font-size: .74rem;
            animation: chipIn .18s ease;
        }

        @keyframes chipIn {
            from {
                opacity: 0;
                transform: scale(.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .ld-chip-no {
            font-family: 'IBM Plex Mono', monospace;
            font-weight: 700;
            color: #1E4FD1;
        }

        .ld-chip-qty {
            color: #667085;
        }

        .ld-chip-remove {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: none;
            background: rgba(47, 111, 237, .15);
            color: #2F6FED;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            cursor: pointer;
            transition: background .15s ease;
        }

        .ld-chip-remove:hover {
            background: #D92D20;
            color: #fff;
        }

        .ld-chip-empty {
            font-size: .78rem;
            color: #98A2B3;
            padding: 4px;
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

        /*button publish*/
        .btn-publish {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 22px 10px 14px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            background: linear-gradient(135deg, #12A150 0%, #0E8A44 100%);
            box-shadow: 0 4px 14px rgba(18, 161, 80, .32), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-publish:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(18, 161, 80, .42), inset 0 1px 0 rgba(255, 255, 255, .2);
        }

        .btn-publish:active {
            transform: translateY(0) scale(.98);
        }

        .btn-publish:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(18, 161, 80, .2);
        }

        .btn-publish-locked {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 22px 10px 14px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            background: #f2f4f7;
            color: #667085;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-publish-locked .pb-icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #98a2b3;
        }

        .btn-publish-locked .pb-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .btn-publish-locked .pb-title {
            font-weight: 600;
            color: #667085;
        }

        .btn-publish-locked .pb-subtitle {
            font-size: 12px;
            color: #98a2b3;
        }

        .pb-icon-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pb-icon-main {
            font-size: 17px;
            color: #fff;
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
        }

        .btn-publish:hover .pb-icon-main {
            transform: translateX(2px) translateY(-2px) rotate(8deg);
        }

        .pb-icon-check {
            position: absolute;
            bottom: -4px;
            right: -4px;
            font-size: 15px;
            color: #12A150;
            background: #fff;
            border-radius: 50%;
            line-height: 1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            transform: scale(0);
            transition: transform .25s ease .1s;
        }

        .btn-publish:hover .pb-icon-check {
            transform: scale(1);
        }

        .pb-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }

        .pb-title {
            font-weight: 700;
            font-size: .96rem;
            color: #fff;
            letter-spacing: .2px;
        }

        .pb-subtitle {
            font-size: .74rem;
            color: rgba(255, 255, 255, .75);
            font-weight: 400;
        }

        /* ripple effect */
        .pb-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .5);
            transform: scale(0);
            animation: pbRippleAnim .55s ease-out;
            pointer-events: none;
        }

        @keyframes pbRippleAnim {
            to {
                transform: scale(3.2);
                opacity: 0;
            }
        }

        .btn-unpublish {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 22px 10px 14px;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            background: linear-gradient(135deg, #F79009 0%, #DC7609 100%);
            box-shadow: 0 4px 14px rgba(247, 144, 9, .32), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .btn-unpublish:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(247, 144, 9, .42), inset 0 1px 0 rgba(255, 255, 255, .2);
        }

        .btn-unpublish:active {
            transform: translateY(0) scale(.98);
        }

        .btn-unpublish:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
            box-shadow: 0 2px 8px rgba(247, 144, 9, .2);
        }

        .ub-icon-wrap {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .16);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .ub-icon-main {
            font-size: 17px;
            color: #fff;
            transition: transform .3s cubic-bezier(.34, 1.56, .64, 1);
        }

        .btn-unpublish:hover .ub-icon-main {
            transform: translateX(-2px) translateY(2px) rotate(-8deg);
        }

        .ub-icon-x {
            position: absolute;
            bottom: -4px;
            right: -4px;
            font-size: 15px;
            color: #DC7609;
            background: #fff;
            border-radius: 50%;
            line-height: 1;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .25);
            transform: scale(0);
            transition: transform .25s ease .1s;
        }

        .btn-unpublish:hover .ub-icon-x {
            transform: scale(1);
        }

        .ub-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.15;
        }

        .ub-title {
            font-weight: 700;
            font-size: .96rem;
            color: #fff;
            letter-spacing: .2px;
        }

        .ub-subtitle {
            font-size: .74rem;
            color: rgba(255, 255, 255, .75);
            font-weight: 400;
        }

        /* ripple effect */
        .ub-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .5);
            transform: scale(0);
            animation: ubRippleAnim .55s ease-out;
            pointer-events: none;
        }

        @keyframes ubRippleAnim {
            to {
                transform: scale(3.2);
                opacity: 0;
            }
        }

        /* ===================== VARIAN COMPACT ===================== */
        .btn-unpublish-compact {
            gap: 8px;
            padding: 0 18px;
            height: 40px;
            border-radius: 10px;
            font-size: .87rem;
            font-weight: 700;
            color: #fff;
        }

        .btn-unpublish-compact i {
            font-size: 14px;
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

        .sp-status-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: .74rem;
            font-weight: 700;
            padding: 4px 11px;
            border-radius: 999px;
        }

        .sp-status-chip i {
            font-size: 11px;
        }

        .sp-status-draft {
            background: #FFF4E5;
            color: #B98900;
        }

        .sp-status-published {
            background: #E9F9EE;
            color: #12A150;
        }

        .sp-filter-select {
            height: 42px;
            border: 1.5px solid #D0D5DD;
            border-radius: 10px;
            padding: 0 12px;
            font-size: .82rem;
            color: #344054;
            background: #fff;
            cursor: pointer;
            max-width: 150px;
        }

        .ld-range-box {
            position: relative;
            display: flex;
            align-items: center;
            border: 1.5px solid #D0D5DD;
            border-radius: 10px;
            overflow: hidden;
            height: 42px;
            background: #fff;
        }

        .ld-range-box i.bi-hash {
            padding: 0 8px 0 12px;
            color: #98A2B3;
            font-size: .82rem;
        }

        .ld-range-box input {
            border: none;
            outline: none;
            font-size: .82rem;
            width: 140px;
            padding: 0 6px 0 0;
            color: #344054;
        }

        .ld-range-apply {
            height: 100%;
            width: 38px;
            border: none;
            border-left: 1.5px solid #EAECF0;
            background: #F5F8FF;
            color: #2F6FED;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s ease;
            flex-shrink: 0;
        }

        .ld-range-apply:hover {
            background: #EAF2FF;
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

        function formatDateTime(date) {
            if (!date) return '';

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            const hour = 17;
            const minute = String(date.getMinutes()).padStart(2, '0');
            const second = String(date.getSeconds()).padStart(2, '0');

            return `${day}/${month}/${year} ${hour}:${minute}:${second}`;
        }

        function formatDateTime2(date) {
            if (!date) return '';

            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            const hour = String(date.getHours()).padStart(2, '0');
            const minute = String(date.getMinutes()).padStart(2, '0');
            const second = String(date.getSeconds()).padStart(2, '0');

            return `${day}/${month}/${year} ${hour}:${minute}:${second}`;
        }

        function parseDateTime(value) {
            if (!value) return null;

            const [datePart, timePart = '00:00:00'] = value.split(' ');

            const [day, month, year] = datePart.split('/').map(Number);
            const [hour, minute, second] = timePart.split(':').map(Number);

            return new Date(
                year,
                month - 1,
                day,
                hour || 0,
                minute || 0,
                second || 0
            );
        }

        function AttrDtgdtpk(value, row) {
            var hiddenSave = '<input type="text" id="isEdit-' + row.invsdtpk + '" value="false">';
            return hiddenSave;
        }

        function back_input_barang() {
            $('#ConfirmCancel').modal('show');
            $('#dg').datagrid('reload');
        }

        function styler1(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;border-left:1px solid #ededed;border-top-left-radius:5px;' +
                'border-bottom-left-radius:5px;height:35px;';
        }

        function styler2(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;';
        }

        function styler3(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;border-right:1px solid #ededed !important;border-top-right-radius:5px;' +
                'border-bottom-right-radius:5px;';
        }

        function validate_import() {
            var totalRows = $('#dg').datagrid('getRows').length;
            // if (!totalRows) {
            onSubmitNewUpload();
            // } else {
            //     $('#validate-import-excel').modal('show');
            //     $('#ModalImportExcel').modal('hide');
            //     return;
            // }
        }

        function enableEditMode() {
            document.querySelectorAll('.shipment-card .field-input').forEach(function(el) {
                el.disabled = false;
            });
            document.getElementById('btnEdit').classList.add('d-none');
            document.getElementById('btnSave').classList.remove('d-none');

            // kalau pakai easyui, refresh biar combobox/datebox ikut ter-enable
            if (window.$ && $.fn.combobox) {
                $('#vassal').combobox('enable');
            }
            if (window.$ && $.fn.datebox) {
                $('.easyui-datebox').datebox('enable');
            }
        }

        // simpan data: kumpulkan value, kunci lagi field-nya, kembali ke mode lihat
        function saveShipment() {
            const data = {};
            document.querySelectorAll('.shipment-card .field-input[name]').forEach(function(el) {
                data[el.name] = el.value;
            });

            // TODO: ganti bagian ini dengan pemanggilan API/ajax simpan ke server
            console.log('Data tersimpan:', data);

            document.querySelectorAll('.shipment-card .field-input').forEach(function(el) {
                el.disabled = true;
            });
            document.getElementById('btnSave').classList.add('d-none');
            document.getElementById('btnEdit').classList.remove('d-none');

            if (window.$ && $.fn.combobox) {
                $('#vassal').combobox('disable');
            }
            if (window.$ && $.fn.datebox) {
                $('.easyui-datebox').datebox('disable');
            }
        }

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
            }

            // bangun ulang daftar di tab 3 berdasarkan jumlah & data vessel name dari tab 1
            // function syncContainerArrivalTab() {
            //     const arrivalList = document.getElementById('containerArrivalList');
            //     const arrivalTemplate = document.getElementById('containerArrivalCardTemplate');
            //     const arrivalCountPill = document.getElementById('containerArrivalCountPill');
            //     const sourceItems = containerList.querySelectorAll('.container-item');

            //     // BUG 2 diperbaiki: query ke arrivalList (bukan arrivalTemplate)
            //     const existingValues = {};
            //     arrivalList.querySelectorAll('.container-item').forEach((el, i) => {
            //         const input = el.querySelector('[data-field="contno"]');
            //         if (input && input.value) existingValues[i] = input.value;
            //     });

            //     arrivalList.innerHTML = '';

            //     sourceItems.forEach((sourceItem, i) => {
            //         const clone = arrivalTemplate.content.cloneNode(true);
            //         const el = clone.querySelector('.container-item');

            //         el.querySelector('.container-arrival-num').textContent = i + 1;

            //         const vessnameInput = sourceItem.querySelector('[data-field="vessname"]');
            //         const vessLabel = el.querySelector('.container-arrival-vessel');
            //         vessLabel.textContent = vessnameInput?.value || '-';

            //         const contnoInput = el.querySelector('[data-field="contno"]');
            //         contnoInput.name = `containers[${i}][contno]`;

            //         // BUG 1 diperbaiki: fallback ke dataset.contno kalau existingValues kosong
            //         if (existingValues[i]) {
            //             contnoInput.value = existingValues[i];
            //         } else if (sourceItem.dataset.contno) {
            //             contnoInput.value = sourceItem.dataset.contno;
            //         }

            //         arrivalList.appendChild(el);
            //     });

            //     arrivalCountPill.textContent = sourceItems.length + ' Container';
            // }

            function syncContainerArrivalTab() {
                const arrivalList = document.getElementById('containerArrivalList');
                const arrivalTemplate = document.getElementById('containerArrivalCardTemplate');

                const fotoList = document.getElementById('containerFotoList');
                const fotoTemplate = document.getElementById('containerFotoCardTemplate');

                const arrivalCountPill = document.getElementById('containerArrivalCountPill');

                const sourceItems = containerList.querySelectorAll('.container-item');

                // =====================================================
                // SIMPAN VALUE CONTAINER NO YANG SUDAH ADA
                // =====================================================
                const existingValues = {};

                arrivalList.querySelectorAll('.container-item').forEach((el, i) => {
                    const input = el.querySelector('[data-field="contno"]');

                    if (input) {
                        existingValues[i] = input.value;
                    }
                });

                // =====================================================
                // SIMPAN VALUE FOTO YANG SUDAH ADA
                // =====================================================
                const existingValues2 = {};

                fotoList.querySelectorAll('.container-foto').forEach((el, i) => {
                    const input = el.querySelector('[data-field="foto"]');

                    if (input) {
                        existingValues2[i] = input.value;
                    }
                });

                // =====================================================
                // KOSONGKAN LIST
                // =====================================================
                arrivalList.innerHTML = '';
                fotoList.innerHTML = '';

                // =====================================================
                // BUAT ULANG BERDASARKAN SOURCE CONTAINER
                // =====================================================
                sourceItems.forEach((sourceItem, i) => {

                    // =================================================
                    // ARRIVAL
                    // =================================================
                    const clone = arrivalTemplate.content.cloneNode(true);
                    const el = clone.querySelector('.container-item');

                    if (!el) return;

                    // Nomor container
                    const numEl = el.querySelector('.container-arrival-num');

                    if (numEl) {
                        numEl.textContent = i + 1;
                    }

                    // Vessel
                    const vessnameInput = sourceItem.querySelector(
                        '[data-field="vessname"]'
                    );

                    const vessLabel = el.querySelector(
                        '.container-arrival-vessel'
                    );

                    if (vessLabel) {
                        vessLabel.textContent = vessnameInput?.value || '-';
                    }

                    // Container No
                    const contnoInput = el.querySelector(
                        '[data-field="contno"]'
                    );

                    if (contnoInput) {

                        contnoInput.name = `containers[${i}][contno]`;

                        // Prioritas:
                        // 1. value yang sudah diinput user
                        // 2. dataset dari source
                        // 3. kosong
                        if (existingValues[i] !== undefined) {

                            contnoInput.value = existingValues[i];

                        } else if (sourceItem.dataset.contno) {

                            contnoInput.value = sourceItem.dataset.contno;

                        } else {

                            contnoInput.value = '';
                        }
                    }

                    // Tambahkan arrival
                    arrivalList.appendChild(el);


                    // =================================================
                    // FOTO
                    // =================================================
                    const fotoClone = fotoTemplate.content.cloneNode(true);
                    const fotoEl = fotoClone.querySelector('.container-foto');

                    if (fotoEl) {

                        // Simpan index container
                        fotoEl.dataset.index = i;

                        // Nomor container foto
                        const fotoNum = fotoEl.querySelector(
                            '.container-foto-num'
                        );

                        if (fotoNum) {
                            fotoNum.textContent = i + 1;
                        }

                        // Input foto
                        const fotoInput = fotoEl.querySelector(
                            '[data-field="foto"]'
                        );

                        if (fotoInput) {

                            // Name mengikuti container
                            fotoInput.name = `containers[${i}][foto]`;

                            // Restore value lama
                            if (existingValues2[i] !== undefined) {
                                fotoInput.value = existingValues2[i];
                            }
                        }

                        // Tambahkan ke FOTO LIST
                        fotoList.appendChild(fotoEl);
                    }
                });

                // =====================================================
                // UPDATE COUNT
                // =====================================================
                arrivalCountPill.textContent =
                    sourceItems.length + ' Container';
            }

            // panggil sync juga saat user mengetik vessel name di tab 1, supaya label di tab 3 ikut update real-time
            containerList.addEventListener('input', function (e) {
                if (e.target.dataset.field === 'vessname') {
                    syncContainerArrivalTab();
                }
            });                 

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

                function setVolume(type, isEdit = false) {

                    // LCL → tampilkan volume input
                    if (type === 'LCL') {
                        volumeContainer.classList.remove('d-none');

                        volumeInput.readOnly = false;

                        if (!isEdit) {
                            volumeInput.value = '';
                        }

                        return;
                    }

                    // Container standard → sembunyikan volume
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
                    radio.addEventListener('change', function () {
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
                    }

                    // Simpan contpk & contno untuk dipakai saat sync ke tab 3
                    if (data.contpk) {
                        el.dataset.contpk = data.contpk;
                    }
                    if (data.contno) {
                        el.dataset.contno = data.contno;
                    }
                }

                // =========================
                // APPEND
                // =========================
                containerList.appendChild(el);

                renumber();
            }

            function removeContainerCard(el) {
                if (containerList.querySelectorAll('.container-item').length === 1) return;
                el.classList.add('removing');
                el.addEventListener('animationend', () => {
                    el.remove();
                    renumber();
                }, { once: true });
            }

            btnAdd.addEventListener('click', addContainerCard);

            // document.getElementById('modalCreateContainer').addEventListener('shown.bs.modal', function() {
            //     if (containerList.children.length === 0) addContainerCard();
            //     goTo(0);
            // });
            document.getElementById('modalCreateContainer').addEventListener('shown.bs.modal', function() {
                // hanya tambahkan card kosong otomatis kalau mode CREATE dan list masih kosong
                if (currentMode === 'create' && containerList.children.length === 0) {
                    addContainerCard();
                }
                    syncContainerArrivalTab();
                    goTo(0);
            });

            /*tambahan untuk edit*/
            let currentMode = 'create'; // 'create' atau 'edit'
            let currentExportpk = null;

            // dipanggil dari tombol "Edit" di halaman (ganti trigger create biasa)
            window.openEditShipmentModal = function(exportpk) {
                currentMode = 'edit';
                currentExportpk = exportpk;

                const storeContainerUrl =
                    "{{ route('export.detail-json', ['exportpk' => '__EXPORTPK__']) }}";
                fetch(storeContainerUrl.replace('__EXPORTPK__', exportpk), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        document.getElementById('exportpk').value = exportpk;

                        // isi field header
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
                        containerList.innerHTML = '';
                        if (data.containers && data.containers.length) {
                            data.containers.forEach(c => addContainerCard(c));
                        } else {
                            addContainerCard(); // fallback: minimal 1 card kosong
                        }

                        const modalTitle = document.querySelector('.mc-title');
                        if (modalTitle) modalTitle.textContent = 'Edit Shipment Plan';

                        const submitBtnLabel = document.getElementById('mcSubmitBtn');
                        if (submitBtnLabel) submitBtnLabel.innerHTML =
                            '<i class="bi bi-check2-circle"></i> Simpan Perubahan';

                        new bootstrap.Modal(document.getElementById('modalCreateContainer')).show();
                    })
                    .catch(() => showToast('Gagal memuat data shipment plan.', 'error'));
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

            /*hidden modal edit*/
            document.getElementById('modalCreateContainer').addEventListener('hidden.bs.modal', function() {
                currentMode = 'create';
                currentExportpk = null;
                containerList.innerHTML = '';

                form.reset();

                const modalTitle = document.querySelector('.mc-title');
                if (modalTitle) modalTitle.textContent = 'Create New Shipment Plan';

                const submitBtnLabel = document.getElementById('mcSubmitBtn');
                if (submitBtnLabel) submitBtnLabel.innerHTML =
                    '<i class="bi bi-check2-circle"></i> Buat Shipment Plan';
            });

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
                    const converted = time
                        ? `${year}-${month}-${day} ${time}`
                        : `${year}-${month}-${day}`;

                    formData.set(field, converted);
                });
            }

            /* ================= SUBMIT (AJAX) ================= */
            // penting: definisikan form, csrfToken di scope yang SAMA dengan handler submit
            const form = document.getElementById('formCreateContainer');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearValidationErrors();

                const exportpk = @json($header->exportpk);
                const formData = new FormData(form);

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
                    }
                });

                const firstErrorKey = Object.keys(errors)[0] || '';
                if (firstErrorKey.startsWith('containers')) goTo(0);
                else if (firstErrorKey === 'etd' || firstErrorKey === 'eta') goTo(2);
            }

            function showToast(message, type) {
                alert(message); // ganti dengan komponen toast/alert milikmu
            }
        })();

        /*Untuk lookup carton*/
        (function() {
            const selectedIds = new Set();
            window.__debugSelectedIds = selectedIds;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const modalEl = document.getElementById('modalLoadCarton');
            const modal = new bootstrap.Modal(modalEl);
            const btnSubmit = document.getElementById('btnLoadSubmit');
            const btnSelectAll = document.getElementById('btnSelectAll');

            let contpk = null;
            let exportpk = null;
            let ordpk = null;
            let poNo = null;
            let ordno = null;
            let contno = null;
            let currentGridUrl = null;
            let gridInitialized = false;

            /* ================= BUKA MODAL ================= */
            window.openLoadingModal = function(_contpk, _contno, _exportpk, _ordpk, _poNo, _ordno) {
                contpk = _contpk;
                contno = _contno;
                exportpk = _exportpk;
                ordpk = _ordpk;
                poNo = _poNo;
                ordno = _ordno;

                const availableCartonsUrl =
                    "{{ route('export.cartons.available', ['exportpk' => '__EXPORTPK__', 'contpk' => '__CONTPK__']) }}";

                currentGridUrl = availableCartonsUrl.replace('__EXPORTPK__', exportpk).replace('__CONTPK__',
                        contpk) +
                    `?ordpk=${ordpk}&pono=${encodeURIComponent(poNo)}&q=`;

                setText('modalContainerNo', contno);
                setText('modalPoNo', poNo || '-');
                setText('modalNoOp', ordno || '-');

                // isi dropdown filter PO & Order No dari string koma yang sama seperti di header
                populateFilterOptions('cartonFilterPo', poNo);
                populateFilterOptions('cartonFilterOrdno', ordno);

                const searchInput = document.getElementById('cartonSearch');
                if (searchInput) searchInput.value = '';

                const rangeInput = document.getElementById('cartonRangeInput');
                if (rangeInput) rangeInput.value = '';

                resetSummary();
                modal.show();
            };

            function populateFilterOptions(selectId, commaSeparatedValues) {
                const select = document.getElementById(selectId);
                if (!select) return;

                const defaultLabel = select.options[0]; // simpan opsi "Semua ..." paling pertama
                select.innerHTML = '';
                select.appendChild(defaultLabel);

                const values = [...new Set(
                    (commaSeparatedValues || '')
                    .split(',')
                    .map(v => v.trim())
                    .filter(Boolean)
                )];

                values.forEach(val => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    select.appendChild(opt);
                });
            }

            function setText(id, value) {
                const el = document.getElementById(id);
                if (!el) {
                    console.warn(`Elemen #${id} tidak ditemukan di DOM.`);
                    return;
                }
                el.textContent = value;
            }

            /* ================= INIT DATAGRID (mode CARD) ================= */
            modalEl.addEventListener('shown.bs.modal', function() {
                if (!gridInitialized) {
                    initGrid();
                    gridInitialized = true;
                } else {
                    $('#dgDetailCarton').datagrid({
                        url: currentGridUrl
                    });
                }
                reloadCartonGrid();
            });

            function initGrid() {
                $('#dgDetailCarton').datagrid({
                    url: currentGridUrl,
                    width: '100%',
                    height: 300,
                    fit: false,
                    fitColumns: true,
                    rownumbers: false,
                    singleSelect: false,
                    checkOnSelect: true, // klik baris ikut nge-toggle checkbox
                    selectOnCheck: true,
                    showHeader: false, // hilangkan header tabel, biar terasa list card
                    striped: false,
                    border: false,
                    nowrap: false,
                    method: 'get',
                    loadMsg: 'Memuat daftar carton siap muat...',
                    rowStyler: function() {
                        return 'ld-card-row';
                    },
                    columns: [
                        [{
                                field: 'ck',
                                checkbox: true,
                                width: 20,
                            },
                            {
                                field: 'info',
                                title: '',
                                width: 'auto',
                                formatter: function(val, row) {
                                    const part = row.part ?
                                        (Number(row.part) === 10 ?
                                            "Session Complete" :
                                            "Session " + row.part) :
                                        "";

                                    const statusVal = Number(row.status);

                                    const statusClass =
                                        statusVal === 4 ?
                                        'Complete' :
                                        statusVal === 5 ?
                                        'Segel' :
                                        'default';
                                    // ==========================================
                                    // PACKPK
                                    // ==========================================
                                    const packpks = Array.isArray(row.packpks) ?
                                        row.packpks : [];
                                    // ==========================================
                                    // CHECKED
                                    // ==========================================
                                    const checked = packpks.some(function(packpk) {
                                        return selectedIds.has(String(packpk));
                                    });
                                    // ==========================================
                                    // SUDAH DIMUAT
                                    // ==========================================
                                    const isLoaded =
                                        Number(row.exportdtpk) > 0;
                                    // ==========================================
                                    // DETAILS
                                    // ==========================================
                                    let sizesHtml = '';

                                    (row.details || []).forEach(function(item) {
                                        sizesHtml += `
                                            <span class="dg-size-item">
                                                ${
                                                    item.material
                                                        ? escapeHtml(item.material) + ' / '
                                                        : ''
                                                }

                                                ${escapeHtml(item.size)} :
                                                ${item.qty}
                                            </span>
                                        `;
                                    });

                                    // ==========================================
                                    // PACKPK JSON
                                    // ==========================================
                                    const packpkJson = JSON.stringify(packpks)
                                        .replace(/"/g, '&quot;');
                                    return `
                                        <div class="dg-card ${isLoaded ? 'dg-card-disabled' : ''}"
                                            data-packpk="${row.packpk}"
                                            ${isLoaded
                                                ? ''
                                                : `onclick="toggleCartonRow(${packpkJson})"`
                                            }>

                                            <span class="
                                                    dg-card-check
                                                    ${checked ? 'checked' : ''}
                                                    ${isLoaded ? 'dg-check-disabled' : ''}
                                                ">
                                                ${isLoaded ? '<i class="bi bi-lock-fill"></i>' : '<i class="bi bi-check-lg"></i>'}
                                            </span>

                                            <div class="dg-card-icon">
                                                <i class="bi bi-box-seam"></i>
                                            </div>

                                            <div class="dg-card-main">
                                                <div class="dg-card-no">Carton Number : ${escapeHtml(String(row.carton ?? ''))}</div>
                                                <div class="dg-card-style dg-card-sizes">${sizesHtml}</div>
                                            </div>

                                            <div class="dg-card-meta">
                                                <span class="dg-card-qty">Qty Per Carton : ${row.pcsp ?? 0} pcs</span>

                                                ${isLoaded
                                                    ? `
                                                        <span class="dg-card-status loaded">
                                                            Sudah Dimuat
                                                        </span>
                                                        <span class="dg-card-session ${row.part > 0 ? '' : 'd-none'}">
                                                            ${part}
                                                        </span>  
                                                                                            `
                                                    : `
                                                        <span class="dg-card-status ${statusClass}">
                                                            ${statusClass}
                                                        </span> 
                                                        <span class="dg-card-session ${row.part > 0 ? '' : 'd-none'}">
                                                            ${part}
                                                        </span>                                                             `
                                                }
                                            </div>
                                        </div>
                                    `;
                                },
                            },
                        ]
                    ],
                    onCheck: updateSummary,
                    onUncheck: updateSummary,
                    // onCheckAll: updateSummary,
                    // onUncheckAll: updateSummary,
                    onSelect: updateSummary,
                    onUnselect: updateSummary,
                    onBeforeCheck: function(index, row) {
                        return Number(row.exportdtpk) <= 0;
                    },

                    onBeforeSelect: function(index, row) {
                        return Number(row.exportdtpk) <= 0;
                    },
                    onLoadSuccess: function(data) {
                        const dg = $(this);

                        dg.datagrid('getRows').forEach(function(row, index) {

                            if (Number(row.exportdtpk) > 0) {

                                const tr = dg.datagrid('getPanel')
                                    .find(
                                        'div.datagrid-body table.datagrid-btable tr[datagrid-row-index="' +
                                        index +
                                        '"]'
                                    );

                                tr.find('input[type="checkbox"]')
                                    .prop('disabled', true);
                            }
                        });

                        resetSummary();

                        const noData = !data.rows || data.rows.length === 0;
                        const view = $('#dgDetailCarton').datagrid('getPanel').find('.datagrid-view');

                        // hapus pesan kosong lama kalau ada, sebelum render ulang
                        view.find('.dg-empty-msg').remove();

                        if (noData) {
                            view.append(`
                            <div class="dg-empty-msg">
                                <i class="bi bi-inbox"></i>
                                <p>Tidak ada carton siap muat.</p>
                            </div>
                        `);
                        }
                    },
                    onClickRow: function(index, row) {
                        const isLoaded = Number(row.exportdtpk) > 0;
                        if (isLoaded) return; // baris ini sudah dimuat, tidak bisa dipilih lagi

                        const id = String(row.packpk);
                        if (selectedIds.has(id)) selectedIds.delete(id);
                        else selectedIds.add(id);

                        $('#dgDetailCarton').datagrid('refreshRow', index);
                        updateSummary();
                    },
                    rowStyler: function(index, row) {
                        const isLoaded = Number(row.exportdtpk) > 0;
                        if (isLoaded) return 'ld-card-row ld-card-row-disabled';
                        return selectedIds.has(String(row.packpk)) ? 'ld-card-row ld-card-row-checked' :
                            'ld-card-row';
                    },
                });
            }

            // cegah XSS sederhana untuk konten yang di-render lewat innerHTML formatter
            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
            }

            window.reloadCartonGrid = function() {
                if (!currentGridUrl) return;
                const searchInput = document.getElementById('cartonSearch');
                const filterPo = document.getElementById('cartonFilterPo')?.value || '';
                const filterOrdno = document.getElementById('cartonFilterOrdno')?.value || '';
                $('#dgDetailCarton').datagrid('load', {
                    q: searchInput ? searchInput.value : '',
                    filter_po: filterPo,
                    filter_ordno: filterOrdno,
                });
            };

            let searchTimer = null;
            window.onCartonSearch = function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(reloadCartonGrid, 350);
            };

            // window.toggleSelectAll = function() {
            //     const rows = $('#dgDetailCarton').datagrid('getRows');
            //     const checked = $('#dgDetailCarton').datagrid('getChecked');
            //     const allChecked = checked.length === rows.length && rows.length > 0;

            //     $('#dgDetailCarton').datagrid(allChecked ? 'uncheckAll' : 'checkAll');
            //     btnSelectAll.classList.toggle('active', !allChecked);
            //     updateSummary();
            // };
            window.toggleSelectAllTest = function() {
                console.log('toggleSelectAllTest DIPANGGIL');
                console.log(
                    'selectedIds === window.__debugSelectedIds:',
                    selectedIds === window.__debugSelectedIds
                );

                const dg = $('#dgDetailCarton');
                const allRows = dg.datagrid('getRows');
                const selectableRows = allRows.filter(
                    r => !(Number(r.exportdtpk) > 0)
                );
                const allChecked =
                    selectableRows.length > 0 &&
                    selectableRows.every(r =>
                        selectedIds.has(String(r.packpk))
                    );

                selectableRows.forEach(row => {
                    const id = String(row.packpk);
                    // cari index row berdasarkan packpk
                    const index = allRows.findIndex(
                        r => String(r.packpk) === id
                    );

                    if (allChecked) {
                        // =========================
                        // UNCHECK
                        // =========================
                        selectedIds.delete(id);
                        if (index >= 0) {
                            dg.datagrid('uncheckRow', index);
                        }
                    } else {
                        // =========================
                        // CHECK
                        // =========================
                        selectedIds.add(id);
                        if (index >= 0) {
                            dg.datagrid('checkRow', index);
                        }
                    }
                });

                console.log(
                    'selectedIds.size setelah forEach:',
                    selectedIds.size
                );

                console.log(
                    'EasyUI checked rows:',
                    dg.datagrid('getChecked')
                );

                btnSelectAll.classList.toggle(
                    'active',
                    !allChecked
                );
                updateSummary();
            };

            window.toggleCartonRow = function(packpk) {
                if (selectedIds.has(packpk)) selectedIds.delete(packpk);
                else selectedIds.add(packpk);

                const rows = $('#dgDetailCarton').datagrid('getRows');
                const index = rows.findIndex(r => String(r.packpk) === packpk); // <-- CEK INI
                if (index > -1) $('#dgDetailCarton').datagrid('refreshRow', index);

                updateSummary();
            };

            function updateSummary() {
                const checked = $('#dgDetailCarton').datagrid('getChecked');
                const totalQty = checked.reduce((sum, row) => sum + (Number(row.pcsp) || 0), 0);

                setText('selectedCount', `${checked.length} carton dipilih`);
                setText('selectedTotals', `Total ${totalQty} pcs`);
                btnSubmit.disabled = checked.length === 0;
            }

            function resetSummary() {
                setText('selectedCount', '0 carton dipilih');
                setText('selectedTotals', 'Total 0 pcs');
                btnSubmit.disabled = true;
                btnSelectAll.classList.remove('active');
            }

            /* ================= SUBMIT ================= */
            window.submitLoadCarton = function() {
                const checked = $('#dgDetailCarton').datagrid('getChecked');
                if (!checked.length) return;

                const cartonIds = checked
                    .map(row => row.packpk)
                    .flat()
                    .map(Number);

                btnSubmit.disabled = true;
                const originalHtml = btnSubmit.innerHTML;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memuat...';

                fetch("{{ route('containers.load') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            contpk: contpk,
                            exportpk: exportpk,
                            ordpk: ordpk,
                            packpk: cartonIds,
                        }),
                    })
                    .then(async res => {
                        const text = await res.text();
                        let result;
                        try {
                            result = JSON.parse(text);
                        } catch {
                            throw new Error('Server mengembalikan respons tidak valid (status ' + res
                                .status + ')');
                        }
                        if (!res.ok) throw new Error(result.message || 'Gagal memuat carton ke container.');

                        // simpan pesan sukses, lalu reload halaman
                        sessionStorage.setItem('ldToastMessage', result.message ||
                            `${cartonIds.length} carton berhasil dimuat ke container.`);
                        sessionStorage.setItem('ldToastType', 'success');
                        location.reload();
                        // showChipToDOM(contpk, result.loaded_items);
                        // updateHeaderTotals(result.totqty, result.totctn);
                        // updateContainerImage(contpk);
                        // modal.hide();
                        // showToast(result.message || 'Carton berhasil ditambahkan ke dalam container.',
                        //     'success');

                    })
                    .catch(err => alert(err.message))
                    .finally(() => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalHtml;
                    });
            };

            function showChipToDOM(contpk, items) {
                const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
                if (!chipsWrap || !items || !items.length) return;

                const emptyMsg = chipsWrap.querySelector('.ld-chip-empty');
                if (emptyMsg) emptyMsg.remove();

                // group dulu items yang baru masuk berdasarkan nomor carton (jaga-jaga kalau
                // 1 kali submit ada beberapa pack dengan carton sama)
                const groupedByCarton = {};
                items.forEach(item => {
                    const cartonNo = item.carton;
                    if (!groupedByCarton[cartonNo]) {
                        groupedByCarton[cartonNo] = {
                            exportdtpks: [],
                            totalPcs: 0
                        };
                    }
                    groupedByCarton[cartonNo].exportdtpks.push(item.exportdtpk);
                    groupedByCarton[cartonNo].totalPcs += Number(item.pcsp) || 0;
                });

                Object.keys(groupedByCarton).forEach(cartonNo => {
                    const chipId = `chip-carton-${cartonNo}-${contpk}`;
                    const newData = groupedByCarton[cartonNo];
                    const existingChip = document.getElementById(chipId);

                    if (existingChip) {
                        // chip carton ini SUDAH ADA di DOM (dimuat sebelumnya) -> gabungkan qty & exportdtpk
                        const existingExportdtpks = existingChip.dataset.exportdtpks.split(',').map(Number);
                        const mergedExportdtpks = [...existingExportdtpks, ...newData.exportdtpks];
                        const existingQty = parseInt(existingChip.querySelector('.ld-chip-qty').textContent) ||
                            0;
                        const mergedQty = existingQty + newData.totalPcs;

                        existingChip.dataset.exportdtpks = mergedExportdtpks.join(',');
                        existingChip.querySelector('.ld-chip-qty').textContent = `${mergedQty} pcs`;
                    } else {
                        // bagian pembuatan chip baru di dalam showChipToDOM, ganti innerHTML-nya:
                        const chip = document.createElement('span');
                        chip.className = 'ld-chip';
                        chip.id = chipId;
                        chip.dataset.exportdtpks = newData.exportdtpks.join(',');
                        chip.dataset.contpk = contpk;
                        chip.dataset.cartonNo = cartonNo;
                        chip.innerHTML = `
                            <span class="ld-chip-no">Ctn.${cartonNo}</span>
                            <span class="ld-chip-qty">${newData.totalPcs} pcs</span>
                            <button type="button" class="ld-chip-remove" title="Keluarkan carton ini">
                                <i class="bi bi-x"></i>
                            </button>
                        `;
                        chipsWrap.appendChild(chip);
                    }
                });

                // update label toggle "X carton dimuat" — hitung jumlah CHIP (bukan jumlah item),
                // karena sekarang 1 chip bisa mewakili beberapa exportdtpk
                const toggleBtn = chipsWrap.previousElementSibling;
                const toggleLabel = toggleBtn?.querySelector('span');
                if (toggleLabel) {
                    const remainingChips = chipsWrap.querySelectorAll('.ld-chip').length;
                    toggleLabel.textContent = `${remainingChips} carton dimuat`;
                }

                // counter & progress bar tetap dihitung dari JUMLAH ITEM yang baru dimuat (bukan jumlah chip),
                // karena ini merepresentasikan jumlah carton fisik yang bertambah, sesuai qty asli
                const countEl = document.getElementById(`loadedCount-${contpk}`);
                const barEl = document.getElementById(`loadedBar-${contpk}`);

                if (countEl) {
                    const text = countEl.textContent.trim();

                    // Ambil jumlah carton saat ini
                    const countMatch = text.match(/^(\d+)/);
                    const currentCount = countMatch ? parseInt(countMatch[1]) : 0;

                    // Ambil total CBM saat ini
                    const cbmMatch = text.match(/\(([\d.]+)\s*cbm\)/i);
                    const currentCbm = cbmMatch ? parseFloat(cbmMatch[1]) : 0;

                    // Group berdasarkan carton
                    const cartons = new Map();

                    items.forEach(item => {
                        const carton = item.carton;

                        if (!cartons.has(carton)) {
                            cartons.set(carton, Number(item.t_cbm) || 0);
                        }
                    });

                    // Jumlah carton baru
                    const addedCount = cartons.size;

                    // CBM baru, hanya dihitung 1x per carton
                    const addedCbm = Array.from(cartons.values())
                        .reduce((total, cbm) => total + cbm, 0);

                    // Total
                    const totalCount = currentCount + addedCount;
                    const totalCbm = currentCbm + addedCbm;

                    countEl.textContent =
                        `${totalCount} ctn (${totalCbm.toFixed(3)} cbm)`;

                    // Progress berdasarkan jumlah carton
                    if (barEl) {

                        const target = Number(barEl.dataset.target) || 1;
                        const pct = Math.min(100, (totalCbm / target) * 100);

                        barEl.style.width = pct + '%';
                    }
                }
            }

            //Toaster
            // helper toast, taruh di dalam IIFE yang sama
            const toastEl = document.getElementById('ldToast');
            const bsToast = new bootstrap.Toast(toastEl, {
                delay: 3500
            });

            function showToast(message, type = 'success') {
                toastEl.classList.remove('ld-toast-error');
                const icon = toastEl.querySelector('.ld-toast-icon i');
                const title = toastEl.querySelector('.ld-toast-title');

                if (type === 'error') {
                    toastEl.classList.add('ld-toast-error');
                    icon.className = 'bi bi-x-lg';
                    title.textContent = 'Gagal';
                } else {
                    icon.className = 'bi bi-check-lg';
                    title.textContent = 'Berhasil';
                }

                document.getElementById('ldToastMessage').textContent = message;
                bsToast.show();
            }

            document.addEventListener('DOMContentLoaded', function() {
                const msg = sessionStorage.getItem('ldToastMessage');
                if (msg) {
                    showToast(msg, sessionStorage.getItem('ldToastType') || 'success');
                    sessionStorage.removeItem('ldToastMessage');
                    sessionStorage.removeItem('ldToastType');
                }
            });

            //action untuk range
           window.selectCartonByRange = function() {
                const input = document.getElementById('cartonRangeInput');
                const rawValue = input.value.trim();

                if (!rawValue) {
                    showToast(
                        'Masukkan nomor carton atau range terlebih dahulu.',
                        'error'
                    );
                    return;
                }

                const targetCartons = parseCartonRange(rawValue);

                if (targetCartons === null) {
                    showToast(
                        'Format range tidak valid. Gunakan contoh: 1-50 atau 1,5,10-20',
                        'error'
                    );
                    return;
                }

                const dg = $('#dgDetailCarton');
                const allRows = dg.datagrid('getRows');

                let matchedCount = 0;
                let skippedLocked = 0;

                allRows.forEach((row, index) => {

                    const cartonNum = parseInt(row.carton, 10);

                    if (!targetCartons.has(cartonNum)) {
                        return;
                    }

                    // sudah dimuat
                    if (Number(row.exportdtpk) > 0) {
                        skippedLocked++;
                        return;
                    }

                    // ==========================================
                    // AMBIL PACKPK
                    // ==========================================
                    const packpkArr = Array.isArray(row.packpks)
                        ? row.packpks
                        : row.packpks != null
                            ? [row.packpks]
                            : [];

                    console.log(
                        'Carton:',
                        row.carton,
                        'Packpk:',
                        packpkArr
                    );

                    // ==========================================
                    // MASUKKAN KE selectedIds
                    // ==========================================
                    packpkArr.forEach(pk => {
                        selectedIds.add(String(pk));
                    });

                    // ==========================================
                    // CHECK EASYUI
                    // ==========================================
                    dg.datagrid('checkRow', index);

                    matchedCount++;
                });

                // ==========================================
                // UPDATE VISUAL CARD
                // ==========================================
                allRows.forEach((row, index) => {
                    const cartonNum = parseInt(row.carton, 10);

                    if (targetCartons.has(cartonNum)) {
                        dg.datagrid('refreshRow', index);
                    }
                });

                updateSummary();

                if (matchedCount === 0) {
                    showToast(
                        'Tidak ada carton yang cocok dengan range tersebut.',
                        'error'
                    );
                } else {
                    let msg = `${matchedCount} carton berhasil dipilih.`;
                    if (skippedLocked > 0) {
                        msg += ` (${skippedLocked} carton dilewati karena sudah dimuat)`;
                    }
                    showToast(msg, 'success');
                }
            };
            /**
             * Parse string range seperti "1-50", "1,5,10-20", "3" jadi Set berisi semua angka carton.
             * Return null kalau formatnya tidak valid sama sekali.
             */
            function parseCartonRange(rawValue) {
                const result = new Set();
                const parts = rawValue.split(',').map(p => p.trim()).filter(Boolean);

                if (parts.length === 0) return null;

                for (const part of parts) {
                    if (part.includes('-')) {
                        const [startStr, endStr] = part.split('-').map(s => s.trim());
                        const start = parseInt(startStr, 10);
                        const end = parseInt(endStr, 10);

                        if (isNaN(start) || isNaN(end) || start > end) {
                            return null; // format range tidak valid, misal "50-1" atau "abc-10"
                        }

                        for (let i = start; i <= end; i++) {
                            result.add(i);
                        }
                    } else {
                        const num = parseInt(part, 10);
                        if (isNaN(num)) return null;
                        result.add(num);
                    }
                }

                return result.size > 0 ? result : null;
            }


        })();

        //isi container/action untuk carton
        function toggleLoadedList(contpk) {
            const chips = document.getElementById(`loadedChips-${contpk}`);
            const btn = chips.previousElementSibling;
            chips.classList.toggle('d-none');
            btn.classList.toggle('open');
        }

        const confirmModalEl = document.getElementById('modalConfirmRemove');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        const crConfirmBtn = document.getElementById('crConfirmBtn');


        // pasang SATU listener di level document, menangkap klik pada SEMUA .ld-chip-remove
        // baik yang dari Blade (page load) maupun yang dibuat dinamis via JS (showChipToDOM)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.ld-chip-remove');
            if (!btn) return;

            const chip = btn.closest('.ld-chip');
            if (!chip) return;

            const exportdtpks = chip.dataset.exportdtpks;
            const contpk = chip.dataset.contpk;
            const cartonNo = chip.dataset.cartonNo;

            removeLoadedCarton(exportdtpks, contpk, cartonNo);
        });

        window.removeLoadedCarton = function(exportdtpkList, contpk, cartonNo) {
            document.getElementById('crCartonNo').textContent = `Ctn.${cartonNo}`;
            confirmModal.show();

            crConfirmBtn.onclick = function() {
                confirmModal.hide();
                doRemoveCarton(exportdtpkList, contpk, cartonNo);
            };
        };

        function doRemoveCarton(exportdtpkList, contpk, cartonNo) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const exportdtpks = exportdtpkList.split(',').map(id => parseInt(id, 10));

            crConfirmBtn.disabled = true;
            const originalHtml = crConfirmBtn.innerHTML;
            crConfirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

            fetch("{{ route('containers.unload') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        exportdtpk: exportdtpks
                    }), // sekarang kirim ARRAY, bukan single value
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

                    // masing-masing dibungkus try/catch sendiri, supaya kalau salah satu error,
                    // toast TETAP muncul dan proses lain tetap jalan
                    // try {
                    //     removeChipFromDOM(exportdtpks, contpk, cartonNo);
                    // } catch (e) {
                    //     console.error('removeChipFromDOM error:', e);
                    // }
                    // try {
                    //     updateContainerImage(contpk);
                    // } catch (e) {
                    //     console.error('updateContainerImage error:', e);
                    // }
                    // try {
                    //     updateHeaderTotals(result.totqty, result.totctn);
                    // } catch (e) {
                    //     console.error('updateHeaderTotals error:', e);
                    // }
                    location.reload();

                    showToast(result.message || 'Carton berhasil dikeluarkan dari container.', 'success');
                })
                .catch(err => {
                    showToast(err.message, 'error');
                })
                .finally(() => {
                    crConfirmBtn.disabled = false;
                    crConfirmBtn.innerHTML = originalHtml;
                });
        }

        function updateContainerImage(contpk) {

            const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
            const containerCard = document.querySelector(`[data-contpk="${contpk}"]`);
            if (!chipsWrap || !containerCard) return;

            const image = containerCard.querySelector('.ld-container-image');
            if (!image) return;

            const cartonCount = chipsWrap.querySelectorAll('.ld-chip').length;

            image.src = cartonCount > 0 ?
                "{{ asset('public/css/images/container_isi.png') }}" :
                "{{ asset('public/css/images/container1.png') }}";
        }

        function updateHeaderTotals(totqty, totctn) {
            const totQtyEl = document.getElementById('headerTotQty');
            const totCtnEl = document.getElementById('headerTotCtn');

            if (totQtyEl) totQtyEl.textContent = totqty ?? 0;
            if (totCtnEl) totCtnEl.textContent = totctn ?? 0;
        }

        // hapus chip dari DOM + update counter/toggle label/progress bar terkait, tanpa reload
        function removeChipFromDOM(removedExportdtpks, contpk, cartonNo) {
            console.log("Nilai dari exportdtpks, countpk, cartonNo:", removedExportdtpks, contpk, cartonNo);
            const chipId = `chip-carton-${cartonNo}-${contpk}`;
            const chip = document.getElementById(chipId);
            const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
            if (!chip || !chipsWrap) return;

            // removedExportdtpks bisa string "101,102" atau array [101,102] -> normalisasi jadi array number
            const removedIds = Array.isArray(removedExportdtpks) ?
                removedExportdtpks.map(Number) :
                String(removedExportdtpks).split(',').map(Number);

            const existingIds = (chip.dataset.exportdtpks || '').split(',').map(Number);
            const remainingIds = existingIds.filter(id => !removedIds.includes(id));

            console.log("Nilai remainingIds:", remainingIds.length);

            if (remainingIds.length === 0) {
                // semua exportdtpk milik carton ini sudah dihapus -> hapus chip sepenuhnya
                chip.remove();
            } else {
                // masih ada sisa exportdtpk untuk carton ini -> jangan hapus chip, cuma update datanya
                // (kasus ini jarang terjadi kalau tombol hapus selalu mengirim SEMUA exportdtpk milik carton,
                //  tapi tetap ditangani untuk jaga-jaga)
                chip.dataset.exportdtpks = remainingIds.join(',');
                chip.querySelector('.ld-chip-remove').setAttribute(
                    'onclick',
                    `removeLoadedCarton('${remainingIds.join(',')}', ${contpk}, '${cartonNo}')`
                );
                // catatan: qty per exportdtpk tidak disimpan terpisah di DOM, jadi qty tidak bisa
                // dikurangi presisi di sini tanpa data tambahan — biasanya kasus ini tidak terjadi
                // karena hapus selalu sekaligus semua baris milik carton yang sama
            }

            // kalau sudah tidak ada chip tersisa sama sekali, tampilkan lagi pesan "belum ada carton"
            const remainingChips = chipsWrap.querySelectorAll('.ld-chip');
            if (remainingChips.length === 0) {
                const emptyMsg = document.createElement('div');
                emptyMsg.className = 'ld-chip-empty';
                emptyMsg.textContent = 'Belum ada carton yang dimuat.';
                chipsWrap.appendChild(emptyMsg);
            }

            // update angka "X carton dimuat" di label toggle -> hitung jumlah CHIP (carton unik), bukan baris
            const toggleBtn = chipsWrap.previousElementSibling;
            const toggleLabel = toggleBtn?.querySelector('span');
            if (toggleLabel) {
                toggleLabel.textContent = `${remainingChips.length} carton dimuat`;
            }

            // update counter "X ctn" dan progress bar -> dikurangi sejumlah exportdtpk yang benar-benar dihapus
            const countEl = document.getElementById(`loadedCount-${contpk}`);
            const barEl = document.getElementById(`loadedBar-${contpk}`);
            if (countEl) {
                const current = Math.max(0, (parseInt(countEl.textContent) || 0) - removedIds.length);
                countEl.textContent = current + ' ctn';

                if (barEl) {
                    const target = Number(barEl.dataset.target) || 1;
                    const pct = Math.min(100, (current / target) * 100);
                    barEl.style.width = pct + '%';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const msg = sessionStorage.getItem('ldToastMessage');
            if (msg) {
                showToast(msg, sessionStorage.getItem('ldToastType') || 'success');
                sessionStorage.removeItem('ldToastMessage');
                sessionStorage.removeItem('ldToastType');
            }
        });

        function BackToList() {
            window.location.href = `{{ url('exim-export/shipplan/index') }}`;
        }

        //Untuk publish dan unpublish
        (function() {
            const confirmModalEl = document.getElementById('modalConfirmPublish');
            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const cpConfirmBtn = document.getElementById('cpConfirmBtn');
            const cpIcon = document.getElementById('cpIcon');
            const cpTitle = document.getElementById('cpTitle');
            const cpDesc = document.getElementById('cpDesc');

            window.handlePublishToggle = function(btn) {
                const exportpk = btn.dataset.exportpk;
                const currentStatus = btn.dataset.status; // 'draft' atau 'published'
                const willPublish = currentStatus === 'draft'; // true = mau publish, false = mau unpublish

                // isi modal sesuai arah aksi
                if (willPublish) {
                    cpIcon.innerHTML = '<i class="bi bi-send-fill"></i>';
                    cpIcon.style.background = '#E9F9EE';
                    cpIcon.style.color = '#12A150';
                    cpTitle.textContent = 'Publish Shipment Plan?';
                    cpDesc.textContent = 'Shipment plan ini akan dikirim ke tim gudang dan mulai bisa diproses.';
                    cpConfirmBtn.className = 'cr-btn cr-btn-publish-confirm';
                } else {
                    cpIcon.innerHTML = '<i class="bi bi-arrow-counterclockwise"></i>';
                    cpIcon.style.background = '#FFF4E5';
                    cpIcon.style.color = '#DC7609';
                    cpTitle.textContent = 'Unpublish Shipment Plan?';
                    cpDesc.textContent =
                        'Shipment plan ini akan ditarik kembali dan tidak lagi terlihat oleh tim gudang.';
                    cpConfirmBtn.className = 'cr-btn cr-btn-unpublish-confirm';
                }

                confirmModal.show();

                cpConfirmBtn.onclick = function() {
                    confirmModal.hide();
                    doTogglePublish(exportpk, willPublish, btn);
                };
            };

            function doTogglePublish(exportpk, willPublish, btn) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

                btn.disabled = true;

                fetch("{{ route('shipment-plan.toggle-publish') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            exportpk,
                            stsexp: willPublish ? 1 : 0
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

                        sessionStorage.setItem('ldToastMessage', result.message ||
                            'Status berhasil diperbarui.');
                        sessionStorage.setItem('ldToastType', 'success');
                        location.reload();
                    })
                    .catch(err => {
                        showToast(err.message, 'error');
                        btn.disabled = false;
                    });
            }
        })();

        //hapus all carton
        $(document).on('click', '.btn-remove-all-cartons', function() {

            const btn = $(this);
            const contpk = btn.data('contpk');

            const chips = $(`#loadedChips-${contpk} .ld-chip`);

            if (!chips.length) {
                alert('Tidak ada carton yang dimuat.');
                return;
            }

            if (!confirm(`Yakin ingin mengeluarkan semua ${chips.length} carton?`)) {
                return;
            }

            // Ambil semua exportdtpk dari seluruh carton
            let exportdtpks = [];

            chips.each(function() {
                const value = $(this).data('exportdtpks');

                if (value) {
                    exportdtpks.push(
                        String(value)
                        .split(',')
                        .map(x => x.trim())
                        .filter(x => x)
                    );
                }
            });

            // Flatten + unique
            exportdtpks = [...new Set(exportdtpks.flat())];

            console.log('contpk:', contpk);
            console.log('exportdtpks:', exportdtpks);

            if (!exportdtpks.length) {
                alert('Data carton tidak ditemukan.');
                return;
            }

            $.ajax({
                url: "{{ route('containers.unload_all') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    contpk: contpk,
                    exportdtpks: exportdtpks
                },

                beforeSend: function() {
                    btn.prop('disabled', true)
                        .html('<i class="spinner-border spinner-border-sm"></i> Menghapus...');
                },

                success: function(res) {

                    console.log('remove all response:', res);

                    if (res.success) {

                        // Hapus semua chip
                        chips.remove();

                        // Update tampilan kosong
                        $(`#loadedChips-${contpk}`).html(
                            '<div class="ld-chip-empty">Belum ada carton yang dimuat.</div>'
                        );

                        // Reload page jika memang diperlukan
                        window.location.reload();

                    } else {
                        alert(res.message || 'Gagal mengeluarkan carton.');
                    }
                },

                error: function(xhr) {

                    console.error('remove all error:', xhr);

                    alert(
                        xhr.responseJSON?.message ||
                        'Terjadi kesalahan saat mengeluarkan carton.'
                    );
                },

                complete: function() {
                    btn.prop('disabled', false)
                        .html('<i class="bi bi-trash"></i> Keluarkan Semua');
                }
            });
        });

        /*ADD FOTO*/
        function OpenCamera() {
            let input = document.getElementById('image_camera');

            input.value = '';
            input.click();
        }


        const cameraInput = document.getElementById('image_camera');
        // const galleryInput = document.getElementById('image_gallery');

        const btnCamera = document.getElementById('btn_camera');
        // const btnGallery = document.getElementById('btn_gallery');

        const nameCamera = document.getElementById('file_name_camera');
        // const nameGallery = document.getElementById('file_name_galery');

        const closeCamera = document.getElementById('close_camera');
        // const closeGalery = document.getElementById('close_galery');


        // CAMERA CHANGE
        cameraInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                let fileName = this.files[0].name.trim();
                $('#foto').val(fileName);
                $('#foto2').val(fileName);

                if (fileName.length > 20) {
                    fileName = fileName.substring(0, 20) + '...';
                }
                $('#file_name_camera').text(fileName);
                // cek apakah ada foto
                const hasFoto = fileName.length > 0;

                if (hasFoto) {
                    $('#close_camera').removeAttr('hidden');
                } else {
                    $('#close_camera').attr('hidden', true);
                }
                // disable gallery
                // btnGallery.disabled = true;
                closeCamera.removeAttribute("hidden");
            } else {
                resetAll();
            }
        });


        // FUNCTION RESET
        function resetAll() {
            $('#foto').val('');
            btnCamera.disabled = false;
            // btnGallery.disabled = false;
            nameCamera.textContent = '';
            // nameGallery.textContent = '';
            cameraInput.value = '';
            // galleryInput.value = '';
            closeCamera.setAttribute("hidden", true);
            // closeGalery.setAttribute("hidden", true);
        }

        let baseUrl = "{{ asset('public/uploads/foto_reject') }}";

        $('#file_name_camera').on('click', function() {

            let foto = $('#foto').val();

            if (!foto) return;

            window.open(baseUrl + '/' + foto, '_blank');
        });
    </script>
@endsection

@section('content')
    <div class="main-wrapper min-vh-100">
        <div class="container-fluid">
            <div class="d-flex flex-column p-4 m-3 mt-0">

                <!-- FORM ATAS -->
                <form id="form_invs">
                    @csrf
                    <div class="shipment-card">
                        <div class="d-flex justify-content-between align-items-center pb-3">
                            <!-- HEADER -->
                            <div class="shipment-header">
                                <button type="button" class="btn-back" onclick="BackToList();">
                                    <i class="bi bi-arrow-left"></i>
                                </button>
                                <h4 class="shipment-title flex-fill">Informasi Detail Shipment</h4>
                            </div>

                            @php
                                $isPublished = !empty($header->stsexp) && (int) $header->stsexp === 1;
                                $isLocked = !empty($header->lockship) && (int) $header->lockship === 1;
                            @endphp
                            {{-- <div class="d-flex align-items-center">
                                <div class="pe-3 {{ $isPublished ? 'd-none' : '' }}">
                                    <span class="sp-status-chip sp-status-draft">
                                        <i class="bi bi-pencil-fill"></i> Draft
                                    </span>
                                </div>
                                <div class="pe-3 {{ $isPublished ? '' : 'd-none' }}">
                                    <span class="sp-status-chip sp-status-published">
                                        <i class="bi bi-check-circle-fill"></i> Publish
                                    </span>
                                </div>

                                @if ($isLocked)
                                    <button type="button" id="btnPublishToggle" class="btn-publish-locked" disabled>
                                        <span class="pb-icon-wrap">
                                            <i class="bi bi-lock-fill"></i>
                                        </span>

                                        <span class="pb-text">
                                            <span class="pb-title">Locked</span>
                                            <span class="pb-subtitle">
                                                Shipment plan ini sudah dikunci
                                            </span>
                                        </span>
                                    </button>
                                @else
                                    <button type="button" id="btnPublishToggle"
                                        class="{{ $isPublished ? 'btn-unpublish' : 'btn-publish' }}"
                                        data-exportpk="{{ $header->exportpk }}"
                                        data-status="{{ $isPublished ? 'published' : 'draft' }}"
                                        onclick="handlePublishToggle(this)">

                                        <span class="pb-icon-wrap">
                                            @if ($isPublished)
                                                <i class="bi bi-send-fill ub-icon-main"></i>
                                                <i class="bi bi-x-circle-fill ub-icon-x"></i>
                                            @else
                                                <i class="bi bi-send-fill pb-icon-main"></i>
                                                <i class="bi bi-check-circle-fill pb-icon-check"></i>
                                            @endif
                                        </span>

                                        <span class="pb-text">
                                            <span class="pb-title">
                                                {{ $isPublished ? 'Unpublish' : 'Publish' }}
                                            </span>

                                            <span class="pb-subtitle">
                                                {{ $isPublished ? 'Tarik kembali shipment plan ini' : 'Kirim shipment plan ke tim gudang' }}
                                            </span>
                                        </span>
                                    </button>
                                @endif
                            </div> --}}
                        </div>


                        <div class="shipment-card pt-3">
                            <div class="pb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="shipment-title2 flex-fill">Shipment Plan</h4>
                                </div>
                                @php
                                    $hidden = $isLocked ? 'd-none' : '';
                                @endphp
                                <div class="{{ $hidden }}">
                                    <button type="button" class="btn-update-shipment"
                                        onclick="openEditShipmentModal({{ $header->exportpk }})">
                                        <span class="us-icon-wrap">
                                            <i class="bi bi-pencil-square"></i>
                                        </span>
                                        <span class="us-text">
                                            <span class="us-title">Update Shipment Plan</span>
                                            <span class="us-subtitle">Lengkapi master data shipment plan</span>
                                        </span>
                                        {{-- <i class="bi bi-arrow-right us-arrow"></i> --}}
                                    </button>
                                </div>
                            </div>

                            <div class="row gx-4">
                                <!-- ===================== KOLOM 1 ===================== -->
                                <div class="col-md-4 d-flex flex-column">

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-receipt"></i></span>
                                        <div class="field-label" style="width: 90px;">Order No</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">
                                            {{ implode(', ', array_unique(array_map('trim', explode(',', $header->ordno ?? '')))) ?: '-' }}
                                        </div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-purple me-2"><i class="bi bi-receipt"></i></span>
                                        <div class="field-label" style="width: 90px;">Buyer</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->buyer ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-receipt"></i></span>
                                        <div class="field-label" style="width: 90px;">Nomor PO</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label2" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ trim($header->POno ?? '-') }}">
                                            {{ trim($header->POno ?? '-') }}
                                        </div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">Buyer Ext. Factory</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->GAC ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">Actual Ext. Factory</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->exdate ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-boxes"></i></span>
                                        <div class="field-label" style="width: 90px;">Total Qty (PCS)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label" id="headerTotQty">{{ $header->totqty ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-box-seam"></i></span>
                                        <div class="field-label" style="width: 90px;">Total CTN</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label" id="headerTotCtn">{{ $header->totctn ?? '-' }}
                                        </div>
                                    </div>

                                </div>

                                <!-- ===================== KOLOM 2 ===================== -->
                                <div class="col-md-4 d-flex flex-column">

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-speedometer"></i></span>
                                        <div class="field-label" style="width: 90px;">Total GW (Kg)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->totgw ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-speedometer"></i></span>
                                        <div class="field-label" style="width: 90px;">Total NW (Kg)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->totnw ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-purple me-2"><i class="bi bi-rulers"></i></span>
                                        <div class="field-label" style="width: 90px;">Measurement CTN</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->ukctn ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-red me-2"><i
                                                class="bi bi-bounding-box-circles"></i></span>
                                        <div class="field-label" style="width: 90px;">Total Volume (CBM)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->totvol ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-diagram-3"></i></span>
                                        <div class="field-label" style="width: 90px;">Vassal</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->typenm ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-geo-alt"></i></span>
                                        <div class="field-label" style="width: 90px;">Port of Loading</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->plbnm ?? '-' }}</div>
                                    </div>

                                </div>

                                <!-- ===================== KOLOM 3 ===================== -->
                                <div class="col-md-4 d-flex flex-column">

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i
                                                class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">Container Arrival</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->contdate ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i
                                                class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">Actual Container Arrival</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->actcontdate ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-clock-history"></i></span>
                                        <div class="field-label" style="width: 90px;">Close Time</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->closetime ?? '-' }}</div>
                                    </div>

                                    {{-- <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-purple me-2"><i class="bi bi-truck"></i></span>
                                        <div class="field-label" style="width: 90px;">Vessel Name</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->vessname ?? '-' }}</div>
                                    </div> --}}

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i
                                                class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">ETD Vessel</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->etdvess ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i
                                                class="bi bi-calendar-check"></i></span>
                                        <div class="field-label" style="width: 90px;">ETA Vessel</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->etavess ?? '-' }}</div>
                                    </div>

                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-receipt"></i></span>
                                        <div class="field-label" style="width: 90px;">PEB No</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->pebno ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="shipment-card mt-2">
                    <div class="shipment-header">
                        <h4 class="shipment-title2 flex-fill">Container loading details</h4>
                    </div>
                    <div class="p-1">
                        <div class="row g-3" id="containerGrid">
                            @forelse ($container as $c)
                                <div class="col-md-6 col-lg-4">
                                    <div class="ld-card" data-contpk="{{ $c->contpk }}">
                                        <div class="ld-card-top">
                                            <span class="ld-badge">{{ strtoupper($c->type ?? '40HC') }}</span>
                                            <span
                                                class="ld-badge volume">{{ strtoupper($c->volume ? 'Volume COntainer: ' . $c->volume . ' Cbm' : '') }}</span>
                                            <span class="ld-status ld-status-{{ $c->status ?? 'loading' }}">
                                                {{ ucfirst($c->status ?? 'loading') }}
                                            </span>
                                        </div>

                                        @php
                                            $loaded = $loadedCartons[$c->contpk] ?? collect();
                                            $groupedByCarton = $loaded->groupBy('carton');
                                            $totalCbmAll = $groupedByCarton->sum(function ($items) {
                                                return $items->first()->t_cbm ?? 0;
                                            });
                                        @endphp

                                        @if ($groupedByCarton->count() > 0)
                                            @php $image = "container_isi.png"; @endphp
                                        @else
                                            @php $image = "container1.png"; @endphp
                                        @endif


                                        <!-- ================= ICON CONTAINER (KLIK UNTUK LOOKUP CARTON) ================= -->
                                        <button type="button"
                                            class="ld-container-visual {{ $isLocked ? 'ld-container-locked' : '' }}"
                                            _contpk, _contno, _exportpk, _ordpk, _poNo, _ordno
                                            @if (!$isLocked) onclick="openLoadingModal(
                                                    {{ $c->contpk }},
                                                    '{{ $c->contno ?? '' }}',
                                                    {{ $header->exportpk }},
                                                    {{ $header->ordpk }},
                                                    '{{ $header->POno ?? '' }}',
                                                    '{{ $header->ordno ?? '' }}'
                                                )"
                                            @else
                                                disabled @endif>
                                            {{-- <svg viewBox="0 0 120 70" class="ld-container-svg">
                                                <rect x="4" y="10" width="112" height="52" rx="4"
                                                    class="ld-svg-body" />
                                                <rect x="4" y="10" width="112" height="52" rx="4"
                                                    class="ld-svg-ribs" />
                                                <rect x="10" y="16" width="100" height="40" rx="2"
                                                    class="ld-svg-door" />
                                                <text x="60" y="40" text-anchor="middle" class="ld-svg-plus">+</text>
                                            </svg> --}}
                                            <img src="{!! asset('public/css/images/' . $image) !!}" class="ld-container-image"
                                                alt="Container">
                                            <span class="ld-visual-label">
                                                <i class="bi bi-hand-index-thumb"></i> Klik untuk muat carton
                                            </span>
                                        </button>

                                        <div class="ld-card-body">
                                            <div class="ld-mono ld-container-no">No. Container: {{ $c->contno }}</div>
                                            <div class="ld-meta">Vessel Name: {{ $c->vessname ?? 'Vessel belum diisi' }}
                                            </div>

                                            {{-- ================= CARTON YANG SUDAH DIMUAT ================= --}}

                                            <div class="ld-loaded-section">
                                                <button type="button" class="ld-loaded-toggle"
                                                    onclick="toggleLoadedList({{ $c->contpk }})">
                                                    <i class="bi bi-box-seam"></i>
                                                    <span>{{ $groupedByCarton->count() }} carton dimuat</span>
                                                    <i class="bi bi-chevron-down ld-loaded-chevron"
                                                        id="chevron-{{ $c->contpk }}"></i>
                                                </button>

                                                <div class="d-flex justify-content-end mb-2">
                                                    <button type="button"
                                                        class="btn btn-sm btn-outline-danger btn-remove-all-cartons {{ $isLocked ? 'd-none' : '' }}"
                                                        data-contpk="{{ $c->contpk }}">
                                                        <i class="bi bi-trash"></i>
                                                        Keluarkan Semua
                                                    </button>
                                                </div>

                                                <div class="ld-loaded-chips d-none" id="loadedChips-{{ $c->contpk }}">
                                                    @forelse ($groupedByCarton as $cartonNo => $items)
                                                        @php
                                                            $totalPcs = $items->sum('pcsp');
                                                            $totalCbm = $items->first()->t_cbm ?? 0;
                                                            // $exportdtpks = $items->pluck('exportdtpk')->implode(',');
                                                            $packpks = $items->pluck('packpk')->implode(',');
                                                        @endphp
                                                        @php
                                                            $packpksForChip = $items->pluck('packpk')->implode(',');
                                                        @endphp
                                                        <span class="ld-chip"
                                                            id="chip-carton-{{ $cartonNo }}-{{ $c->contpk }}"
                                                            data-packpks="{{ $packpksForChip }}"
                                                            data-contpk="{{ $c->contpk }}"
                                                            data-exportpk="{{ $header->exportpk }}"
                                                            data-carton-no="{{ $cartonNo }}">
                                                            <span class="ld-chip-no">Ctn.{{ $cartonNo }}</span>
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
                                                <span class="ld-loaded-count"
                                                    id="loadedCount-{{ $c->contpk }}">{{ $loaded->count() }}
                                                    ctn ({{ number_format($totalCbmAll, 3, '.', ',') }} cbm)</span>
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
                                                    '{{ $c->contno ?? '' }}',
                                                    {{ $header->exportpk }},
                                                    {{ $header->ordpk }},
                                                    '{{ $header->POno ?? '' }}',
                                                    '{{ $header->ordno ?? '' }}'
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
                        </div>
                    </div>
                </div>


                <!-- Toaster -->
                <div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 1080;">
                    <div id="ldToast" class="toast ld-toast" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="ld-toast-body">
                            <span class="ld-toast-icon"><i class="bi bi-check-lg"></i></span>
                            <div class="ld-toast-text">
                                <div class="ld-toast-title">Berhasil</div>
                                <div class="ld-toast-message" id="ldToastMessage">Carton berhasil dimuat ke container.
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

    @include('menu.exim-export.shipplan.modal.modalContainer')
    @include('menu.exim-export.shipplan.modal.modalLoading')
@endsection
