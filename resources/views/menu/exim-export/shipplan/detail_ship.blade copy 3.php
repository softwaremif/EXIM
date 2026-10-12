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

        .border-1 { border: 1px solid red; }
        .btn-black { color: #fff; background: #000; }
        .btn-transparent { color: #000; background: transparent; }
        .rounded { border-radius: 8px; }
        .bg-white { color: #000; background: #fff; border: 1px solid black; }
        .input-group-search { width: 160px; }
        th { text-align: center !important; }
        thead { margin-bottom: 20px; }
        table { margin-top: 0 !important; background-color: #fff !important; }
        table.table-custom { border-collapse: separate; border-spacing: 0 8px; }
        .btn-outline-dark:hover { background-color: transparent; color: #000; }
        table.table-custom tbody tr, table.table-custom tbody tr td { border-radius: 8px; }
        table.table-custom tbody tr { box-shadow: 0px 0px 16px -4px #0000001F; }
        table.table-custom thead tr, table.table-custom thead tr th { border-radius: 8px; margin-bottom: 3px; }
        table.table-custom thead tr { box-shadow: 0px 0px 16px -4px #0000001F; }
        .blue { color: #359DD9; }
        .red { color: red; }
        .pointer { cursor: pointer; }
        .grey { color: rgba(0, 0, 0, 0.38); }
        .bg-black { color: #fff; background: #000; }
        .font-small { font-size: 12px; }
        .height_input { height: 32px; }
        .bg-light-grey { background: var(--Text-Light-grey, rgba(0, 0, 0, 0.38)); }
        .bg-dark-grey { background: var(--Text-Grey, rgba(0, 0, 0, 0.60)); }
        .color-view-blue { color: rgb(53, 157, 217); }
        .sticky { position: fixed; top: 0; width: 100%; }
        .sticky+.contenta { padding-top: 102px; }
        .datagrid-cell { font-style: normal; font-weight: 400; font-size: 13px; line-height: 18px; white-space: normal; }
        .datagrid-body { background: transparent !important; overflow-y: auto !important; max-height: 100%; }
        .text-biru { color: #359DD9; font-weight: 700; cursor: pointer; }

        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0; } 100% { opacity: 1; } }
        .warning-blink { font-size: 12px; font-weight: bold; color: red; text-align: center; animation: blink 2s infinite; }
        .warning-blink-1 { font-size: 14px; font-weight: bold; color: red; text-align: center; }
        .font-warning { font-size: 12px; font-weight: bold; color: red; text-align: left; }

        .shipment-card {
            background: #ffffff;
            border: 1px solid #EAECF0;
            border-radius: 16px;
            padding: 15px 32px 32px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06), 0 1px 2px rgba(16, 24, 40, .04);
            max-width: 1400px;
        }

        .shipment-header { display: flex; align-items: center; gap: 7px; margin-bottom: 5px; }
        .btn-back {
            width: 38px; height: 38px; border-radius: 50%; border: 1px solid #E4E7EC; background: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px; color: #101828;
            cursor: pointer; transition: background .15s ease, transform .15s ease; flex-shrink: 0;
        }
        .btn-back:hover { background: #F2F4F7; transform: translateX(-2px); }
        .shipment-title { font-weight: 700; font-size: 1.25rem; color: #101828; margin: 0; }
        .shipment-title2 { font-weight: 700; font-size: 1rem; color: hsl(228, 2%, 57%); margin: 0; }

        .header-actions { display: flex; gap: 10px; flex-shrink: 0; }
        .btn-action {
            display: inline-flex; align-items: center; gap: 7px; height: 40px; padding: 0 18px;
            border-radius: 8px; font-size: .92rem; font-weight: 600; border: 1px solid transparent;
            cursor: pointer; transition: background .15s ease, transform .1s ease, box-shadow .15s ease;
        }
        .btn-action:active { transform: scale(.97); }
        .btn-edit { background: #fff; border-color: #D0D5DD; color: #344054; }
        .btn-edit:hover { background: #F9FAFB; border-color: #98A2B3; }
        .btn-save { background: #12A150; color: #fff; box-shadow: 0 1px 2px rgba(16, 24, 40, .06); }
        .btn-save:hover { background: #0E8A44; }

        .field-row { display: grid; grid-template-columns: 34px 145px 1fr; align-items: center; column-gap: 12px; margin-bottom: 14px; }
        .field-chip {
            width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-size: 15px; flex-shrink: 0; transition: transform .15s ease;
        }
        .field-row:hover .field-chip { transform: scale(1.08); }
        .chip-blue { background: #EAF2FF; color: #2F6FED; }
        .chip-purple { background: #F1ECFE; color: #7C4DFF; }
        .chip-teal { background: #E6F7F5; color: #12B5A6; }
        .chip-pink { background: #FDECF3; color: #E4589A; }
        .chip-orange { background: #FFF4E5; color: #F79009; }
        .chip-green { background: #E9F9EE; color: #12A150; }
        .chip-amber { background: #FEF6E7; color: #B98900; }
        .chip-slate { background: #F1F2F4; color: #475467; }

        .field-label { font-size: .92rem; font-weight: 500; color: #344054; line-height: 1.25; }
        .field-label2 { max-width: 135px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .field-input, select.field-input {
            width: 100%; height: 38px; border: 1px solid #D0D5DD; border-radius: 8px; padding: 0 12px;
            font-size: .92rem; color: #101828; background: #fff; outline: none;
            transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .field-input:hover:not(:disabled) { border-color: #98A2B3; }
        .field-input:focus { border-color: #2F6FED; box-shadow: 0 0 0 3px rgba(47, 111, 237, .15); }
        .field-input:disabled { background: #F9FAFB; border-style: dashed; color: #475467; cursor: default; }
        select.field-input {
            cursor: pointer; appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23667085' stroke-width='2'><polyline points='6 9 12 15 18 9'/></svg>");
            background-repeat: no-repeat; background-position: right 10px center; padding-right: 32px;
        }

        @media (max-width: 767px) {
            .field-row { grid-template-columns: 30px 120px 1fr; }
            .shipment-card { padding: 20px; }
            .shipment-header { flex-wrap: wrap; }
            .header-actions { width: 100%; justify-content: flex-end; }
        }

        .btn-create-container {
            position: relative; overflow: hidden; display: inline-flex; align-items: center; gap: 14px;
            padding: 10px 22px 10px 14px; border: none; border-radius: 14px; cursor: pointer;
            background: linear-gradient(135deg, #2F6FED 0%, #1E4FD1 100%);
            box-shadow: 0 4px 14px rgba(47, 111, 237, .35), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .btn-create-container:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(47, 111, 237, .45), inset 0 1px 0 rgba(255, 255, 255, .2); }
        .btn-create-container:active { transform: translateY(0) scale(.98); }

        .cc-icon-wrap {
            position: relative; width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, .16);
            display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .cc-icon-box { font-size: 19px; color: #fff; transition: transform .35s cubic-bezier(.34, 1.56, .64, 1); }
        .btn-create-container:hover .cc-icon-box { transform: rotate(-8deg) scale(1.05); }
        .cc-icon-plus {
            position: absolute; bottom: -4px; right: -4px; font-size: 15px; color: #34D399; background: #fff;
            border-radius: 50%; line-height: 1; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transition: transform .25s ease;
        }
        .btn-create-container:hover .cc-icon-plus { transform: scale(1.15) rotate(90deg); }
        .cc-text { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; }
        .cc-title { font-weight: 700; font-size: .96rem; color: #fff; letter-spacing: .2px; }
        .cc-subtitle { font-size: .74rem; color: rgba(255, 255, 255, .75); font-weight: 400; }
        .cc-arrow { font-size: 16px; color: rgba(255, 255, 255, .85); transition: transform .2s ease; margin-left: 2px; }
        .btn-create-container:hover .cc-arrow { transform: translateX(4px); }
        .cc-ripple {
            position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .55); transform: scale(0);
            animation: cc-ripple-anim .55s ease-out; pointer-events: none;
        }
        @keyframes cc-ripple-anim { to { transform: scale(3.2); opacity: 0; } }

        .btn-create-container.compact { padding: 8px 16px 8px 10px; border-radius: 10px; gap: 10px; }
        .btn-create-container.compact .cc-icon-wrap { width: 30px; height: 30px; border-radius: 8px; }
        .btn-create-container.compact .cc-icon-box { font-size: 15px; }
        .btn-create-container.compact .cc-subtitle { display: none; }
        .btn-create-container.compact .cc-title { font-size: .88rem; }

        #modalCreateContainer .modal-content.mc-content {
            border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 24px 60px rgba(16, 24, 40, .25);
            animation: mcPopIn .28s cubic-bezier(.34, 1.56, .64, 1);
        }
        @keyframes mcPopIn { from { transform: scale(.92) translateY(14px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }

        .mc-header {
            display: flex; align-items: center; gap: 16px; padding: 24px 28px;
            background: linear-gradient(135deg, #2F6FED 0%, #1E4FD1 100%); position: relative;
        }
        .mc-header-icon {
            width: 48px; height: 48px; border-radius: 12px; background: rgba(255, 255, 255, .16);
            display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; flex-shrink: 0;
        }
        .mc-title { color: #fff; font-weight: 700; font-size: 1.15rem; margin: 0; }
        .mc-subtitle { color: rgba(255, 255, 255, .78); font-size: .82rem; margin: 2px 0 0; }
        .mc-close {
            width: 34px; height: 34px; border-radius: 50%; border: none; background: rgba(255, 255, 255, .16);
            color: #fff; font-size: 14px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .15s ease, transform .15s ease;
        }
        .mc-close:hover { background: rgba(255, 255, 255, .28); transform: rotate(90deg); }

        .mc-tabs { position: relative; display: flex; gap: 4px; padding: 10px 20px 0; background: #fff; border-bottom: 1px solid #EAECF0; }
        .mc-tab {
            display: flex; align-items: center; gap: 7px; border: none; background: transparent; cursor: pointer;
            padding: 10px 16px; font-size: .87rem; font-weight: 600; color: #98A2B3; border-radius: 10px 10px 0 0;
            transition: color .15s ease, background .15s ease;
        }
        .mc-tab:hover { color: #475467; background: #F9FAFB; }
        .mc-tab.active { color: #2F6FED; }
        .mc-tab-indicator {
            position: absolute; bottom: -1px; left: 20px; height: 2.5px; width: 148px; background: #2F6FED;
            border-radius: 2px; transition: transform .28s cubic-bezier(.65, 0, .35, 1), width .28s ease;
        }

        .mc-body { padding: 26px 28px 10px; min-height: 300px; }
        .mc-pane { display: none; animation: mcFadeSlide .25s ease; }
        .mc-pane.active { display: block; }
        @keyframes mcFadeSlide { from { opacity: 0; transform: translateX(8px); } to { opacity: 1; transform: translateX(0); } }

        .mc-label { display: flex; align-items: center; gap: 6px; font-size: .85rem; font-weight: 600; color: #344054; margin-bottom: 6px; }
        .mc-label i { color: #2F6FED; font-size: .9rem; }

        .mc-input, textarea.mc-input {
            width: 100%; height: 42px; border: 1.5px solid #D0D5DD; border-radius: 10px; padding: 0 14px;
            font-size: .9rem; color: #101828; background: #fff; outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        textarea.mc-input { height: auto; padding: 10px 14px; resize: none; }
        .mc-input:hover { border-color: #98A2B3; }
        .mc-input:focus { border-color: #2F6FED; box-shadow: 0 0 0 4px rgba(47, 111, 237, .14); }

        .mc-input-suffix { position: relative; }
        .mc-input-suffix input { padding-right: 46px; }
        .mc-input-suffix span { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); font-size: .8rem; color: #98A2B3; font-weight: 600; }

        .mc-choice-group { display: flex; gap: 8px; }
        .mc-choice {
            flex: 1; text-align: center; cursor: pointer; border: 1.5px solid #D0D5DD; border-radius: 10px;
            padding: 9px 6px; font-size: .85rem; font-weight: 600; color: #475467; transition: all .15s ease;
        }
        .mc-choice input { display: none; }
        .mc-choice:has(input:checked) { border-color: #2F6FED; background: #EAF2FF; color: #2F6FED; }

        .mc-gauge-preview { background: #F9FAFB; border: 1px solid #EAECF0; border-radius: 12px; padding: 16px; }
        .mc-gauge-row { display: flex; justify-content: space-between; font-size: .8rem; color: #475467; font-weight: 600; margin-bottom: 6px; }
        .mc-gauge-track { height: 9px; background: #E4E7EC; border-radius: 999px; overflow: hidden; }
        .mc-gauge-fill { height: 100%; background: #2F6FED; border-radius: 999px; transition: width .35s ease; }
        .mc-gauge-fill.fill-purple { background: #7C4DFF; }

        .mc-footer { padding: 16px 28px; border-top: 1px solid #EAECF0; display: flex; align-items: center; justify-content: space-between; }
        .mc-step-dots { display: flex; gap: 6px; }
        .mc-step-dots .dot { width: 8px; height: 8px; border-radius: 50%; background: #E4E7EC; transition: all .2s ease; }
        .mc-step-dots .dot.active { background: #2F6FED; width: 20px; border-radius: 4px; }

        .mc-btn {
            display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 18px; border-radius: 10px;
            font-size: .87rem; font-weight: 600; border: 1.5px solid transparent; cursor: pointer; transition: all .15s ease;
        }
        .mc-btn:disabled { opacity: .4; cursor: not-allowed; }
        .mc-btn-ghost { background: transparent; color: #667085; }
        .mc-btn-ghost:hover { background: #F9FAFB; }
        .mc-btn-outline { background: #fff; border-color: #D0D5DD; color: #344054; }
        .mc-btn-outline:hover:not(:disabled) { background: #F9FAFB; border-color: #98A2B3; }
        .mc-btn-primary { background: #2F6FED; color: #fff; box-shadow: 0 2px 8px rgba(47, 111, 237, .35); }
        .mc-btn-primary:hover { background: #1E4FD1; }
        .mc-btn-success { background: #12A150; color: #fff; box-shadow: 0 2px 8px rgba(18, 161, 80, .35); }
        .mc-btn-success:hover { background: #0E8A44; }

        .mc-pane-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
        .mc-pane-toolbar-text { display: flex; align-items: center; gap: 7px; font-size: .82rem; color: #667085; }
        .mc-pane-toolbar-text i { color: #F79009; }
        .mc-count-pill { background: #EAF2FF; color: #2F6FED; font-weight: 700; font-size: .78rem; padding: 5px 12px; border-radius: 999px; white-space: nowrap; }

        .container-list { display: flex; flex-direction: column; gap: 14px; }
        .container-item {
            position: relative; border: 1.5px solid #EAECF0; border-radius: 14px; padding: 18px 18px 16px;
            background: #FCFCFD; transition: border-color .18s ease, box-shadow .18s ease;
            animation: itemSlideIn .28s cubic-bezier(.34, 1.2, .64, 1);
        }
        .container-item:hover { border-color: #C7D7FE; box-shadow: 0 2px 10px rgba(47, 111, 237, .08); }
        .container-item.removing { animation: itemSlideOut .22s ease forwards; }
        @keyframes itemSlideIn { from { opacity: 0; transform: translateY(-10px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes itemSlideOut { to { opacity: 0; transform: translateX(24px) scale(.96); margin-top: -100px; } }

        .container-item-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .container-badge {
            display: inline-flex; align-items: center; gap: 7px; background: linear-gradient(135deg, #2F6FED, #1E4FD1);
            color: #fff; font-weight: 700; font-size: .8rem; padding: 6px 14px; border-radius: 999px;
        }
        .btn-remove-container {
            width: 30px; height: 30px; border-radius: 8px; border: 1.5px solid #FEE4E2; background: #FEF3F2;
            color: #D92D20; display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }
        .btn-remove-container:hover { background: #FEE4E2; transform: scale(1.06); }
        .btn-remove-container:disabled { opacity: .35; cursor: not-allowed; transform: none; }

        .btn-add-container {
            width: 100%; margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 8px;
            border: 1.5px dashed #A9C3FA; border-radius: 12px; background: #F5F8FF; color: #2F6FED; font-weight: 700;
            font-size: .88rem; padding: 12px; cursor: pointer; transition: background .15s ease, border-color .15s ease, transform .1s ease;
        }
        .btn-add-container:hover { background: #EAF2FF; border-color: #2F6FED; }
        .btn-add-container:active { transform: scale(.99); }

        .mc-empty-hint { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 50px 20px; color: #98A2B3; }
        .mc-empty-hint i { font-size: 34px; margin-bottom: 10px; color: #C7D7FE; }
        .mc-empty-hint p { margin: 0; font-size: .86rem; }

        .datagrid-view2, .datagrid-view { height: 100% !important; margin-left: 0 !important; padding-left: 0 !important; }

        @media (max-width: 576px) {
            #modalLoadCarton .modal-dialog { max-height: 96vh; }
            #modalLoadCarton .modal-content.ld-modal { max-height: 96vh; }
            .mc-header, .mc-body, .mc-footer { padding-left: 18px; padding-right: 18px; }
            .mc-tabs { overflow-x: auto; }
            .mc-footer { flex-direction: column; gap: 12px; align-items: stretch; }
            .mc-footer .d-flex { justify-content: space-between; }
        }

        .mc-input.is-invalid { border-color: #D92D20 !important; box-shadow: 0 0 0 3px rgba(217, 45, 32, .12) !important; }
        .mc-error-text { color: #D92D20; font-size: .76rem; margin-top: 4px; }
        .fwd-combo+.combo { border: 1px solid #000 !important; border-radius: 10px; }

        .ld-card {
            background: #fff; border: 1px solid #EAECF0; border-radius: 16px; padding: 16px;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06); display: flex; flex-direction: column; height: 100%;
            transition: box-shadow .18s ease, transform .18s ease;
        }
        .ld-card:hover { box-shadow: 0 8px 20px rgba(16, 24, 40, .1); transform: translateY(-2px); }
        .ld-card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .ld-badge { font-family: 'IBM Plex Mono', monospace; font-size: .72rem; font-weight: 700; background: #EAF2FF; color: #2F6FED; padding: 4px 10px; border-radius: 999px; }
        .ld-badge.volume { font-family: 'IBM Plex Mono', monospace; font-size: .72rem; font-weight: 700; background: #f9f7f7; color: #ed2f2f; padding: 4px 10px; border-radius: 999px; }
        .ld-status { font-size: .72rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; text-transform: capitalize; }
        .ld-status-loading { background: #fff9e5; color: #B98900; }
        .ld-status-sealed { background: #E9F9EE; color: #12A150; }
        .ld-status-shipped { background: #F1F2F4; color: #475467; }
        .ld-status-plan { background: #f1c280; color: #0d0d0d; }
        .ld-status-start { background: #fd6e07; color: #ffffff; }
        .ld-status-finish { background: #04d446; color: #feffff; }
        .ld-status-shipped { background: #6c6e71; color: #f5f5f7; }

        .ld-container-visual {
            border: none; background: linear-gradient(135deg, #DCEBFF, #EAF2FF); border-radius: 12px;
            padding: 14px 10px 10px; display: flex; flex-direction: column; align-items: center; gap: 8px;
            cursor: pointer; width: 100%; transition: background .2s ease, transform .15s ease;
        }
        .ld-container-visual:hover { background: linear-gradient(135deg, #CBDEFF, #DCEBFF); transform: scale(1.015); }
        .ld-container-visual:active { transform: scale(.985); }
        .ld-container-locked { cursor: not-allowed !important; opacity: 0.55; filter: grayscale(0.8); }
        .ld-container-locked:hover { transform: none !important; }
        .ld-container-locked .ld-visual-label { color: #98a2b3; }
        .ld-container-svg { width: 100%; max-width: 190px; height: auto; }
        .ld-svg-body { fill: #2F6FED; }
        .ld-svg-ribs { fill: none; stroke: rgba(255, 255, 255, .25); stroke-width: 2; stroke-dasharray: 2 8; }
        .ld-svg-door { fill: #EAF2FF; stroke: #1E4FD1; stroke-width: 1.5; stroke-dasharray: 5 4; transition: fill .2s ease; }
        .ld-container-visual:hover .ld-svg-door { fill: #fff; }
        .ld-svg-plus { font-size: 26px; font-weight: 700; fill: #2F6FED; }
        .ld-visual-label { font-size: .74rem; font-weight: 600; color: #2F6FED; display: flex; align-items: center; gap: 5px; }

        .ld-card-body { padding: 12px 2px 4px; }
        .ld-mono { font-family: 'IBM Plex Mono', monospace; }
        .ld-container-no { font-weight: 700; font-size: .95rem; color: #101828; }
        .ld-meta { font-size: .8rem; color: #98A2B3; margin-bottom: 0px; }
        .ld-progress-row { display: flex; justify-content: space-between; font-size: .76rem; font-weight: 600; color: #475467; margin-bottom: 5px; }
        .ld-progress-track { height: 7px; background: #EAECF0; border-radius: 999px; overflow: hidden; }
        .ld-progress-fill { height: 100%; background: linear-gradient(90deg, #2F6FED, #34D399); border-radius: 999px; transition: width .35s ease; }

        .ld-btn-action {
            margin-top: 10px; width: 100%; border: 1.5px solid #2F6FED; background: #fff; color: #2F6FED;
            font-weight: 700; font-size: .85rem; padding: 9px; border-radius: 10px; display: flex; align-items: center;
            justify-content: center; gap: 7px; cursor: pointer; transition: background .15s ease, color .15s ease;
        }
        .ld-btn-action:hover { background: #2F6FED; color: #fff; }
        .ld-btn-locked { background-color: #f2f4f7 !important; color: #98a2b3 !important; border-color: #d0d5dd !important; cursor: not-allowed !important; opacity: 0.7; }
        .ld-btn-locked:hover { background-color: #f2f4f7 !important; color: #98a2b3 !important; }

        .ld-empty { text-align: center; padding: 40px 20px; color: #98A2B3; }
        .ld-empty i { font-size: 32px; color: #C7D7FE; margin-bottom: 8px; display: block; }
        .ld-container-image { width: 250px; height: 200px; object-fit: contain; display: block; }

        #modalLoadCarton .modal-dialog { max-height: 90vh; display: flex; align-items: center; }
        #modalLoadCarton .modal-content.ld-modal { max-height: 90vh; display: flex; flex-direction: column; border: none; border-radius: 18px; overflow: hidden; }
        @keyframes ldPop { from { transform: scale(.94) translateY(10px); opacity: 0; } to { transform: scale(1) translateY(0); opacity: 1; } }

        .ld-modal-header { display: flex; align-items: center; gap: 14px; padding: 20px 24px; background: linear-gradient(135deg, #2F6FED, #1E4FD1); }
        .ld-modal-header, .ld-modal-toolbar, .ld-modal-footer { flex-shrink: 0; }
        .ld-modal-header-icon {
            width: 42px; height: 42px; border-radius: 11px; background: rgba(255, 255, 255, .16); display: flex;
            align-items: center; justify-content: center; color: #fff; font-size: 19px; flex-shrink: 0;
        }
        .ld-modal-title { color: #fff; font-weight: 700; margin: 0; font-size: 1.05rem; }
        .ld-modal-subtitle { color: rgba(255, 255, 255, .8); font-size: .8rem; margin: 2px 0 0; }
        .ld-close {
            width: 32px; height: 32px; border-radius: 50%; border: none; background: rgba(255, 255, 255, .16);
            color: #fff; cursor: pointer; transition: background .15s ease, transform .15s ease;
        }
        .ld-close:hover { background: rgba(255, 255, 255, .28); transform: rotate(90deg); }

        .ld-modal-toolbar { display: flex; gap: 10px; padding: 16px 24px 0; }
        .ld-search-box { flex: 1; position: relative; }
        .ld-search-box i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #98A2B3; font-size: .85rem; }
        .ld-search-box input {
            width: 100%; height: 40px; border: 1.5px solid #D0D5DD; border-radius: 10px; padding: 0 14px 0 36px;
            font-size: .87rem; outline: none; transition: border-color .15s ease, box-shadow .15s ease;
        }
        .ld-search-box input:focus { border-color: #2F6FED; box-shadow: 0 0 0 4px rgba(47, 111, 237, .13); }

        .ld-select-all {
            border: 1.5px solid #D0D5DD; background: #fff; border-radius: 10px; padding: 0 16px; font-size: .82rem;
            font-weight: 600; color: #344054; display: flex; align-items: center; gap: 6px; cursor: pointer;
            white-space: nowrap; transition: background .15s ease, border-color .15s ease;
        }
        .ld-select-all:hover { background: #F9FAFB; border-color: #98A2B3; }
        .ld-select-all.active { background: #EAF2FF; border-color: #2F6FED; color: #2F6FED; }

        .ld-modal-body { flex: 1 1 auto; min-height: 0; padding: 14px 5px 1px; overflow: hidden; display: flex; flex-direction: column; }
        .ld-loading-state { text-align: center; padding: 46px 10px; color: #98A2B3; }
        .ld-spinner { color: #2F6FED; width: 2rem; height: 2rem; margin-bottom: 10px; }

        .ld-carton-row:hover { border-color: #C7D7FE; background: #F9FBFF; }
        .ld-carton-row:has(.ld-carton-checkbox:checked) { border-color: #2F6FED; background: #EAF2FF; }
        .ld-carton-checkbox { width: 18px; height: 18px; accent-color: #2F6FED; flex-shrink: 0; cursor: pointer; }
        .ld-carton-icon {
            width: 34px; height: 34px; border-radius: 9px; background: #FFF4E5; color: #B98900; display: flex;
            align-items: center; justify-content: center; flex-shrink: 0; font-size: 15px;
        }
        .ld-carton-info { flex: 1; min-width: 0; }
        .ld-carton-no { font-family: 'IBM Plex Mono', monospace; font-weight: 700; font-size: .85rem; color: #101828; }
        .ld-carton-sub { font-size: .76rem; color: #98A2B3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ld-carton-metrics { display: flex; flex-direction: column; align-items: flex-end; gap: 2px; flex-shrink: 0; }
        .ld-carton-qty { font-size: .8rem; font-weight: 700; color: #344054; }
        .ld-carton-weight { font-size: .72rem; color: #98A2B3; }

        .ld-modal-footer { padding: 14px 24px; border-top: 1px solid #EAECF0; display: flex; align-items: center; justify-content: space-between; }
        .ld-selected-summary { display: flex; flex-direction: column; }
        .ld-selected-badge { font-weight: 700; font-size: .88rem; color: #2F6FED; }
        .ld-selected-sub { font-size: .74rem; color: #98A2B3; }

        .ld-btn {
            display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 18px; border-radius: 10px;
            font-size: .87rem; font-weight: 600; border: 1.5px solid transparent; cursor: pointer; transition: all .15s ease;
        }
        .ld-btn:disabled { opacity: .4; cursor: not-allowed; }
        .ld-btn-ghost { background: transparent; color: #667085; }
        .ld-btn-ghost:hover { background: #F9FAFB; }
        .ld-btn-primary { background: #2F6FED; color: #fff; box-shadow: 0 2px 8px rgba(47, 111, 237, .35); }
        .ld-btn-primary:hover:not(:disabled) { background: #1E4FD1; }

        .ld-refresh-btn {
            width: 40px; height: 40px; border-radius: 10px; border: 1.5px solid #D0D5DD; background: #fff;
            color: #475467; display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: background .15s ease, transform .3s ease;
        }
        .ld-refresh-btn:hover { background: #F9FAFB; }
        .ld-refresh-btn:active i { transform: rotate(180deg); }
        .ld-refresh-btn i { transition: transform .3s ease; }

        #dgDetailCarton, .datagrid-view, .datagrid-wrap { border: none !important; background: transparent !important; }
        #dgDetailCarton { flex: 1; min-height: 0; }
        .datagrid-view { border: 1px solid #EAECF0 !important; border-left: 1px solid #EAECF0 !important; border-right: 1px solid #0758fb !important; border-radius: 12px; overflow: hidden; padding-left: 0px; }

        .datagrid-btable { border-collapse: separate !important; border-spacing: 0 10px !important; }
        .datagrid-row { cursor: pointer; }
        .datagrid-row td { border-top: 1.5px solid #EAECF0 !important; border-bottom: 1.5px solid #EAECF0 !important; background: #fff !important; padding: 12px 14px !important; vertical-align: middle !important; }
        .datagrid-row td:first-child { border-left: 1.5px solid #EAECF0 !important; border-top-left-radius: 14px; border-bottom-left-radius: 14px; width: 48px !important; text-align: center; }
        .datagrid-row td:last-child { border-right: 1.5px solid #EAECF0 !important; border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
        .datagrid-row:hover td { border-color: #C7D7FE !important; background: #F9FBFF !important; }
        .datagrid-row-checked td { border-color: none !important; background: #EAF2FF !important; }
        .datagrid-row input[type="checkbox"] { width: 18px; height: 18px; accent-color: #2F6FED; cursor: pointer; }

        .dg-card { display: flex; align-items: center; gap: 12px; }
        .dg-card-icon { width: 34px; height: 34px; border-radius: 9px; background: #FFF4E5; color: #B98900; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
        .dg-card-main { flex: 1; min-width: 0; }
        .dg-card-no { font-family: 'IBM Plex Mono', monospace; font-weight: 700; font-size: .88rem; color: #101828; }
        .dg-card-style { font-size: .78rem; color: #98A2B3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dg-card-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 0px; flex-shrink: 0; }
        .dg-card-meta2 { display: flex; flex-direction: column; align-items: center; gap: 4px; }
        .dg-card-qty { font-size: .82rem; font-weight: 700; color: #344054; }
        .dg-card-status { font-size: .68rem; font-weight: 700; padding: 2px 9px; border-radius: 999px; text-transform: capitalize; }
        .dg-card-status.ready { background: #E9F9EE; color: #12A150; }
        .dg-card-status.held { background: #FEF3F2; color: #D92D20; }
        .dg-card-status.default { background: #F1F2F4; color: #667085; }
        .dg-card-status.Complete { background: #1ede3e; color: #fafafa; }
        .dg-card-status.Segel { background: #000e00; color: #ffffff; }
        .dg-card-session { font-size: .68rem; font-weight: 700; padding: 2px 9px; border-radius: 999px; text-transform: capitalize; background: #e9def2; color: #a562fc; }

        .dg-bundle-tag { display: inline-block; font-size: .6rem; font-weight: 700; background: #F1ECFE; color: #7C4DFF; padding: 2px 7px; border-radius: 999px; margin-left: 6px; vertical-align: middle; }
        .dg-bundle-badge { font-size: .7rem; color: #7C4DFF; background: #F8F6FF; border: 1px dashed #D8CCFA; border-radius: 6px; padding: 4px 8px; margin-top: 4px; }
        .dg-bundle-list { list-style: none; margin: 4px 0 0; padding: 0; }
        .dg-bundle-list li { font-size: .68rem; color: #7C4DFF; padding: 1px 0; }
        .dg-carton-poop { font-size: .68rem; color: #667085; opacity: .85; margin-top: 1px; }

        .datagrid-body::-webkit-scrollbar { width: 8px; }
        .datagrid-body::-webkit-scrollbar-thumb { background: hsl(0, 2%, 39%); border-radius: 999px; }

        .status-chip { display: inline-block; font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 999px; }
        .status-chip.ready { background: #E9F9EE; color: #12A150; }
        .status-chip.held { background: #FEF3F2; color: #D92D20; }

        .dg-empty-msg { position: absolute; top: 60px; left: 0; right: 0; text-align: center; color: #98A2B3; padding: 30px 10px; }
        .dg-empty-msg i { font-size: 32px; color: #C7D7FE; margin-bottom: 8px; display: block; }
        .dg-empty-msg p { margin: 0; font-size: .86rem; }
        .datagrid-view { position: relative; }

        .dg-card-sizes { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; }
        .dg-size-item { display: inline-block; padding: 3px 8px; background: #f1f1f1; border-radius: 4px; font-size: 12px; font-weight: 500; color: #000; }
        .dg-card-disabled { opacity: .55; cursor: not-allowed; }
        .dg-check-disabled { background: #F1F2F4 !important; border-color: #D0D5DD !important; color: #98A2B3 !important; }
        .dg-card-status.loaded { background: #F1F2F4; color: #667085; }
        .ld-card-row-disabled td { cursor: not-allowed; background: #FAFAFA !important; }
        .ld-card-row-disabled:hover td { border-color: #EAECF0 !important; background: #FAFAFA !important; }

        .ld-loaded-section { margin: 10px 0 12px; }
        .ld-loaded-toggle {
            width: 100%; display: flex; align-items: center; gap: 8px; background: #F9FAFB; border: 1px solid #EAECF0;
            border-radius: 9px; padding: 8px 12px; cursor: pointer; font-size: .8rem; font-weight: 600; color: #475467;
            transition: background .15s ease;
        }
        .ld-loaded-toggle:hover { background: #F2F4F7; }
        .ld-loaded-toggle i.bi-box-seam { color: #B98900; }
        .ld-loaded-toggle span { flex: 1; text-align: left; }
        .ld-loaded-chevron { transition: transform .2s ease; }
        .ld-loaded-toggle.open .ld-loaded-chevron { transform: rotate(180deg); }
        .ld-loaded-chips {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; padding: 10px; background: #FCFCFD;
            border: 1px dashed #EAECF0; border-radius: 9px; max-height: 140px; overflow-y: auto;
        }
        .ld-chip {
            display: inline-flex; align-items: center; gap: 6px; background: #EAF2FF; border: 1px solid #C7D7FE;
            border-radius: 999px; padding: 4px 6px 4px 10px; font-size: .74rem; animation: chipIn .18s ease;
        }
        @keyframes chipIn { from { opacity: 0; transform: scale(.9); } to { opacity: 1; transform: scale(1); } }
        .ld-chip-no { font-family: 'IBM Plex Mono', monospace; font-weight: 700; color: #1E4FD1; }
        .ld-chip-qty { color: #667085; }
        .ld-chip-loading { opacity: .65; background: #ace7c4 !important; border-color: #D0D5DD !important; cursor: not-allowed; }
        .ld-chip-shipped { opacity: .65; background: #F1F2F4 !important; border-color: #D0D5DD !important; cursor: not-allowed; }
        .ld-chip-remove {
            width: 16px; height: 16px; border-radius: 50%; border: none; background: rgba(47, 111, 237, .15);
            color: #2F6FED; display: flex; align-items: center; justify-content: center; font-size: 11px;
            cursor: pointer; transition: background .15s ease;
        }
        .ld-chip-remove:hover { background: #D92D20; color: #fff; }
        .ld-chip-empty { font-size: .78rem; color: #98A2B3; padding: 4px; }

        .ld-toast { border: none; border-radius: 14px; box-shadow: 0 12px 32px rgba(16, 24, 40, .18); overflow: hidden; min-width: 320px; }
        .ld-toast-body { display: flex; align-items: flex-start; gap: 12px; padding: 14px 14px 14px 16px; background: #fff; border-left: 4px solid #12A150; }
        .ld-toast.ld-toast-error .ld-toast-body { border-left-color: #D92D20; }
        .ld-toast-icon { width: 32px; height: 32px; border-radius: 50%; background: #E9F9EE; color: #12A150; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .ld-toast.ld-toast-error .ld-toast-icon { background: #FEF3F2; color: #D92D20; }
        .ld-toast-text { flex: 1; min-width: 0; }
        .ld-toast-title { font-weight: 700; font-size: .88rem; color: #101828; }
        .ld-toast-message { font-size: .8rem; color: #667085; margin-top: 2px; }
        .ld-toast-close { border: none; background: transparent; color: #98A2B3; font-size: 16px; line-height: 1; cursor: pointer; flex-shrink: 0; padding: 2px; }
        .ld-toast-close:hover { color: #475467; }

        .cr-modal { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(16, 24, 40, .25); animation: mcPopIn .25s cubic-bezier(.34, 1.5, .64, 1); }
        .cr-body { padding: 28px 26px 20px; text-align: center; }
        .cr-icon { width: 56px; height: 56px; border-radius: 50%; background: #FEF3F2; color: #D92D20; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 14px; }
        .cr-title { font-weight: 700; font-size: 1.05rem; color: #101828; margin-bottom: 8px; }
        .cr-desc { font-size: .86rem; color: #667085; margin: 0; line-height: 1.5; }
        .cr-footer { display: flex; gap: 10px; padding: 16px 24px 22px; }
        .cr-btn {
            flex: 1; height: 42px; border-radius: 10px; border: 1.5px solid transparent; font-size: .87rem;
            font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;
            transition: all .15s ease;
        }
        .cr-btn-ghost { background: #fff; border-color: #D0D5DD; color: #344054; }
        .cr-btn-ghost:hover { background: #F9FAFB; }
        .cr-btn-danger { background: #D92D20; color: #fff; box-shadow: 0 2px 8px rgba(217, 45, 32, .3); }
        .cr-btn-danger:hover { background: #B42318; }
        .cr-btn-danger:disabled { opacity: .6; cursor: not-allowed; }

        .btn-update-shipment {
            position: relative; display: inline-flex; align-items: center; gap: 14px; padding: 10px 22px 10px 14px;
            border: 1.5px solid #D0D5DD; border-radius: 14px; cursor: pointer; background: #fff;
            box-shadow: 0 1px 3px rgba(16, 24, 40, .06); transition: border-color .18s ease, background .18s ease, transform .18s ease;
        }
        .btn-update-shipment:hover { border-color: #2F6FED; background: #F9FBFF; transform: translateY(-1px); }
        .btn-update-shipment:active { transform: translateY(0) scale(.99); }
        .us-icon-wrap {
            width: 40px; height: 40px; border-radius: 10px; background: #EAF2FF; color: #2F6FED; display: flex;
            align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; transition: background .18s ease;
        }
        .btn-update-shipment:hover .us-icon-wrap { background: #DCEBFF; }
        .us-text { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; }
        .us-title { font-weight: 700; font-size: .96rem; color: #101828; }
        .us-subtitle { font-size: .74rem; color: #98A2B3; }
        .us-arrow { font-size: 16px; color: #98A2B3; margin-left: 2px; transition: transform .2s ease, color .2s ease; }
        .btn-update-shipment:hover .us-arrow { color: #2F6FED; transform: translateX(4px); }

        .btn-publish {
            position: relative; overflow: hidden; display: inline-flex; align-items: center; gap: 14px;
            padding: 10px 22px 10px 14px; border: none; border-radius: 14px; cursor: pointer;
            background: linear-gradient(135deg, #12A150 0%, #0E8A44 100%);
            box-shadow: 0 4px 14px rgba(18, 161, 80, .32), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .btn-publish:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(18, 161, 80, .42), inset 0 1px 0 rgba(255, 255, 255, .2); }
        .btn-publish:active { transform: translateY(0) scale(.98); }
        .btn-publish:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: 0 2px 8px rgba(18, 161, 80, .2); }

        .btn-publish-locked { position: relative; overflow: hidden; display: inline-flex; align-items: center; gap: 14px; padding: 10px 22px 10px 14px; border: none; border-radius: 14px; cursor: pointer; background: #f2f4f7; color: #667085; transition: transform .18s ease, box-shadow .18s ease; }
        .btn-publish-locked .pb-icon-wrap { display: flex; align-items: center; justify-content: center; font-size: 20px; color: #98a2b3; }
        .btn-publish-locked .pb-text { display: flex; flex-direction: column; align-items: flex-start; }
        .btn-publish-locked .pb-title { font-weight: 600; color: #667085; }
        .btn-publish-locked .pb-subtitle { font-size: 12px; color: #98a2b3; }

        .pb-icon-wrap { position: relative; width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, .16); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .pb-icon-main { font-size: 17px; color: #fff; transition: transform .3s cubic-bezier(.34, 1.56, .64, 1); }
        .btn-publish:hover .pb-icon-main { transform: translateX(2px) translateY(-2px) rotate(8deg); }
        .pb-icon-check {
            position: absolute; bottom: -4px; right: -4px; font-size: 15px; color: #12A150; background: #fff;
            border-radius: 50%; line-height: 1; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transform: scale(0);
            transition: transform .25s ease .1s;
        }
        .btn-publish:hover .pb-icon-check { transform: scale(1); }
        .pb-text { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; }
        .pb-title { font-weight: 700; font-size: .96rem; color: #fff; letter-spacing: .2px; }
        .pb-subtitle { font-size: .74rem; color: rgba(255, 255, 255, .75); font-weight: 400; }
        .pb-ripple { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .5); transform: scale(0); animation: pbRippleAnim .55s ease-out; pointer-events: none; }
        @keyframes pbRippleAnim { to { transform: scale(3.2); opacity: 0; } }

        .btn-unpublish {
            position: relative; overflow: hidden; display: inline-flex; align-items: center; gap: 14px;
            padding: 10px 22px 10px 14px; border: none; border-radius: 14px; cursor: pointer;
            background: linear-gradient(135deg, #F79009 0%, #DC7609 100%);
            box-shadow: 0 4px 14px rgba(247, 144, 9, .32), inset 0 1px 0 rgba(255, 255, 255, .15);
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .btn-unpublish:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(247, 144, 9, .42), inset 0 1px 0 rgba(255, 255, 255, .2); }
        .btn-unpublish:active { transform: translateY(0) scale(.98); }
        .btn-unpublish:disabled { opacity: .6; cursor: not-allowed; transform: none; box-shadow: 0 2px 8px rgba(247, 144, 9, .2); }

        .ub-icon-wrap { position: relative; width: 40px; height: 40px; border-radius: 10px; background: rgba(255, 255, 255, .16); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .ub-icon-main { font-size: 17px; color: #fff; transition: transform .3s cubic-bezier(.34, 1.56, .64, 1); }
        .btn-unpublish:hover .ub-icon-main { transform: translateX(-2px) translateY(2px) rotate(-8deg); }
        .ub-icon-x {
            position: absolute; bottom: -4px; right: -4px; font-size: 15px; color: #DC7609; background: #fff;
            border-radius: 50%; line-height: 1; box-shadow: 0 1px 3px rgba(0, 0, 0, .25); transform: scale(0);
            transition: transform .25s ease .1s;
        }
        .btn-unpublish:hover .ub-icon-x { transform: scale(1); }
        .ub-text { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; }
        .ub-title { font-weight: 700; font-size: .96rem; color: #fff; letter-spacing: .2px; }
        .ub-subtitle { font-size: .74rem; color: rgba(255, 255, 255, .75); font-weight: 400; }
        .ub-ripple { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .5); transform: scale(0); animation: ubRippleAnim .55s ease-out; pointer-events: none; }
        @keyframes ubRippleAnim { to { transform: scale(3.2); opacity: 0; } }

        .btn-unpublish-compact { gap: 8px; padding: 0 18px; height: 40px; border-radius: 10px; font-size: .87rem; font-weight: 700; color: #fff; }
        .btn-unpublish-compact i { font-size: 14px; }

        .cr-btn-publish-confirm { background: #12A150; color: #fff; box-shadow: 0 2px 8px rgba(18, 161, 80, .3); }
        .cr-btn-publish-confirm:hover { background: #0E8A44; }
        .cr-btn-unpublish-confirm { background: #F79009; color: #fff; box-shadow: 0 2px 8px rgba(247, 144, 9, .3); }
        .cr-btn-unpublish-confirm:hover { background: #DC7609; }

        .sp-status-chip { display: inline-flex; align-items: center; gap: 5px; font-size: .74rem; font-weight: 700; padding: 4px 11px; border-radius: 999px; }
        .sp-status-chip i { font-size: 11px; }
        .sp-status-draft { background: #FFF4E5; color: #B98900; }
        .sp-status-published { background: #E9F9EE; color: #12A150; }

        .sp-filter-select { height: 42px; border: 1.5px solid #D0D5DD; border-radius: 10px; padding: 0 12px; font-size: .82rem; color: #344054; background: #fff; cursor: pointer; max-width: 150px; }

        .ld-range-box { position: relative; display: flex; align-items: center; border: 1.5px solid #D0D5DD; border-radius: 10px; overflow: hidden; height: 42px; background: #fff; }
        .ld-range-box i.bi-hash { padding: 0 8px 0 12px; color: #98A2B3; font-size: .82rem; }
        .ld-range-box input { border: none; outline: none; font-size: .82rem; width: 140px; padding: 0 6px 0 0; color: #344054; }
        .ld-range-apply { height: 100%; width: 38px; border: none; border-left: 1.5px solid #EAECF0; background: #F5F8FF; color: #2F6FED; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .15s ease; flex-shrink: 0; }
        .ld-range-apply:hover { background: #EAF2FF; }

        .container-item-readonly { background: #F9FAFB; }
        .container-arrival-vessel { margin-left: auto; }
        .container-arrival-vessel::before { content: "Vessel: "; color: #98A2B3; }
        .container-foto-vessel { margin-left: auto; }
        .container-foto-vessel::before { content: "No. Container: "; color: #98A2B3; }

        .ld-loaded-header-row { display: flex; align-items: center; gap: 8px; }
        .ld-loaded-header-row .ld-loaded-toggle { flex: 1; margin-bottom: 0; }
        .ld-remove-all-btn {
            flex-shrink: 0; width: 38px; height: 38px; border-radius: 9px; border: 1px solid #FEE4E2; background: #FEF3F2;
            color: #D92D20; display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }
        .ld-remove-all-btn:hover { background: #FEE4E2; transform: scale(1.05); }
        .ld-remove-all-btn:disabled { opacity: .35; cursor: not-allowed; transform: none; }

        .ld-view-progress-btn {
            width: 100%; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 7px;
            border: 1.5px solid #D0D5DD; background: #fff; color: #344054; font-weight: 600; font-size: .83rem;
            padding: 9px; border-radius: 10px; cursor: pointer; transition: background .15s ease, border-color .15s ease;
        }
        .ld-view-progress-btn:hover { background: #F9FAFB; border-color: #98A2B3; }

        .cp-modal { border: none; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 60px rgba(16, 24, 40, .25); }
        .cp-header { display: flex; align-items: center; gap: 14px; padding: 20px 24px; background: linear-gradient(135deg, #2F6FED 0%, #1E4FD1 100%); }
        .cp-header-icon { width: 42px; height: 42px; border-radius: 11px; background: rgba(255, 255, 255, .16); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 19px; flex-shrink: 0; }
        .cp-title { color: #fff; font-weight: 700; margin: 0; font-size: 1.05rem; }
        .cp-subtitle { color: rgba(255, 255, 255, .8); font-size: .8rem; margin: 2px 0 0; }
        .cp-close { width: 32px; height: 32px; border-radius: 50%; border: none; background: rgba(255, 255, 255, .16); color: #fff; cursor: pointer; transition: background .15s ease, transform .15s ease; }
        .cp-close:hover { background: rgba(255, 255, 255, .28); transform: rotate(90deg); }

        .cp-legend { display: flex; gap: 18px; padding: 12px 24px; background: #F9FAFB; border-bottom: 1px solid #EAECF0; flex-wrap: wrap; }
        .cp-legend-item { display: flex; align-items: center; gap: 6px; font-size: .78rem; color: #475467; }
        .cp-dot { width: 12px; height: 12px; border-radius: 3px; display: inline-block; }
        .cp-dot-loading { background: #F79009; }
        .cp-dot-loaded { background: #12A150; }
        .cp-dot-shipped { background: #98A2B3; }

        .cp-body { padding: 20px 24px; max-height: 55vh; overflow-y: auto; min-height: 200px; position: relative; }
        .cp-loading-state, .cp-empty-state { text-align: center; padding: 50px 20px; color: #98A2B3; }
        .cp-empty-state i { font-size: 34px; color: #C7D7FE; margin-bottom: 10px; display: block; }

        .cp-carton-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(108px, 1fr)); gap: 10px; }
        .cp-carton-box {
            aspect-ratio: unset; min-height: 92px; border-radius: 10px; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 1px; padding: 8px 6px; cursor: default;
            transition: transform .12s ease, box-shadow .12s ease; position: relative; border: 1.5px solid transparent; text-align: center;
        }
        .cp-carton-box:hover { transform: translateY(-2px) scale(1.04); box-shadow: 0 4px 10px rgba(16, 24, 40, .15); z-index: 2; }
        .cp-carton-icon { font-size: 14px; margin-bottom: 2px; }
        .cp-carton-label { font-size: .72rem; font-weight: 700; font-family: 'IBM Plex Mono', monospace; line-height: 1.2; }
        .cp-carton-po { font-size: .62rem; font-weight: 600; line-height: 1.2; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .cp-carton-op { font-size: .6rem; opacity: .75; line-height: 1.2; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .cp-carton-qty { font-size: .64rem; font-weight: 700; margin-top: 2px; line-height: 1.2; }

        .cp-status-loading { background: #FFF4E5; color: #B98900; border-color: #F79009; animation: cpPulse 1.6s ease-in-out infinite; }
        @keyframes cpPulse { 0%, 100% { opacity: 1; } 50% { opacity: .55; } }
        .cp-status-loaded { background: #E9F9EE; color: #12A150; border-color: #12A150; }
        .cp-status-shipped { background: #F1F2F4; color: #667085; border-color: #D0D5DD; }

        .cp-tooltip {
            position: fixed; z-index: 1070; background: #101828; color: #fff; font-size: .76rem; padding: 8px 12px;
            border-radius: 8px; box-shadow: 0 8px 20px rgba(0, 0, 0, .25); pointer-events: none; max-width: 220px; line-height: 1.5;
        }
        .cp-tooltip.show { display: block; }

        .cp-footer { padding: 14px 24px; border-top: 1px solid #EAECF0; display: flex; align-items: center; justify-content: space-between; }
        .cp-summary { font-size: .82rem; color: #667085; font-weight: 600; }
    </style>
@endsection

@section('js_custom')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const msg = sessionStorage.getItem('ldToastMessage');
            if (msg) {
                showToast(msg, sessionStorage.getItem('ldToastType') || 'success');
                sessionStorage.removeItem('ldToastMessage');
                sessionStorage.removeItem('ldToastType');
            }

            const params = new URLSearchParams(window.location.search);
            if (params.get('open_modal') === 'container') {
                openEditShipmentModal();
            }
        });

        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        function myformatter(date) {
            var d = new Date(date || Date.now()), month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [day, month, year].join('/');
        }

        function myparser(s) {
            if (!s) return new Date();
            var ss = (s.split('/'));
            var y = parseInt(ss[0], 10), m = parseInt(ss[1], 10), d = parseInt(ss[2], 10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)) return new Date(d, m - 1, y);
            return new Date();
        }

        function myformatter2(date) {
            if (!date) date = new Date();
            var d = new Date(date);
            var month = '' + (d.getMonth() + 1), day = '' + d.getDate(), year = d.getFullYear();
            var hour = '' + d.getHours(), minute = '' + d.getMinutes();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            if (hour.length < 2) hour = '0' + hour;
            if (minute.length < 2) minute = '0' + minute;
            return day + '/' + month + '/' + year + ' ' + hour + ':' + minute;
        }

        function myparser2(s) {
            if (!s) return new Date();
            var parts = s.split(' ');
            var datePart = parts[0], timePart = parts[1] || '00:00';
            var dateParts = datePart.split('/'), timeParts = timePart.split(':');
            var day = parseInt(dateParts[0], 10), month = parseInt(dateParts[1], 10), year = parseInt(dateParts[2], 10);
            var hour = parseInt(timeParts[0], 10) || 0, minute = parseInt(timeParts[1], 10) || 0;
            if (!isNaN(day) && !isNaN(month) && !isNaN(year)) return new Date(year, month - 1, day, hour, minute);
            return new Date();
        }

        function myparser3(s) {
            if (!s) {
                const now = new Date();
                now.setHours(17, 0, 0, 0);
                return now;
            }
            var parts = s.split(' ');
            var dateParts = parts[0].split('-');
            var day = parseInt(dateParts[0], 10), month = parseInt(dateParts[1], 10), year = parseInt(dateParts[2], 10);
            var hour = 17, minute = 0;
            if (parts[1]) {
                var timeParts = parts[1].split(':');
                hour = parseInt(timeParts[0], 10);
                minute = parseInt(timeParts[1], 10) || 0;
            }
            return new Date(year, month - 1, day, hour, minute);
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
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;border-left:1px solid #ededed;border-top-left-radius:5px;border-bottom-left-radius:5px;height:35px;';
        }
        function styler2(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;';
        }
        function styler3(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;border-right:1px solid #ededed !important;border-top-right-radius:5px;border-bottom-right-radius:5px;';
        }

        function validate_import() {
            var totalRows = $('#dg').datagrid('getRows').length;
            onSubmitNewUpload();
        }

        function enableEditMode() {
            document.querySelectorAll('.shipment-card .field-input').forEach(function(el) { el.disabled = false; });
            document.getElementById('btnEdit').classList.add('d-none');
            document.getElementById('btnSave').classList.remove('d-none');
            if (window.$ && $.fn.combobox) $('#vassal').combobox('enable');
            if (window.$ && $.fn.datebox) $('.easyui-datebox').datebox('enable');
        }

        function saveShipment() {
            const data = {};
            document.querySelectorAll('.shipment-card .field-input[name]').forEach(function(el) { data[el.name] = el.value; });
            console.log('Data tersimpan:', data);
            document.querySelectorAll('.shipment-card .field-input').forEach(function(el) { el.disabled = true; });
            document.getElementById('btnSave').classList.add('d-none');
            document.getElementById('btnEdit').classList.remove('d-none');
            if (window.$ && $.fn.combobox) $('#vassal').combobox('disable');
            if (window.$ && $.fn.datebox) $('.easyui-datebox').datebox('disable');
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

                // BARU (dari Production) -- widget datetimebox tab "info" baru
                // diinisialisasi SAAT tab itu benar-benar tampil, bukan saat DOM
                // dibuat -- easyui gagal menghitung lebar widget di elemen yang
                // masih display:none.
                refreshPaneWidgets(key);
            }

            function initArrivalDatetimebox(input) {
                try {
                    if ($(input).data('datetimebox')) {
                        $(input).datetimebox('resize', 250);
                        return;
                    }
                    $(input).datetimebox({ formatter: myformatter2, parser: myparser2, width: 250, height: 40 });
                    const preset = input.dataset.presetValue || '';
                    if (preset) $(input).datetimebox('setValue', preset);
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
                    item.querySelectorAll('input[type="radio"]').forEach(r => { r.name = `containers[${i}][type]`; });
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
                syncContainerArrivalTab();
                syncContainerFotoTab();
            }

            function syncContainerArrivalTab() {
                const arrivalList = document.getElementById('containerArrivalList');
                const arrivalTemplate = document.getElementById('containerArrivalCardTemplate');
                const arrivalCountPill = document.getElementById('containerArrivalCountPill');
                const sourceItems = containerList.querySelectorAll('.container-item');

                const existingValues = {};
                arrivalList.querySelectorAll('.container-item').forEach((el, i) => {
                    existingValues[i] = {};
                    el.querySelectorAll('[data-field]').forEach(input => {
                        const field = input.dataset.field;
                        if (input.classList.contains('easyui-datetimebox') && $(input).data('datetimebox')) {
                            existingValues[i][field] = $(input).datetimebox('getValue');
                        } else {
                            existingValues[i][field] = input.value || input.dataset.presetValue || '';
                        }
                    });
                });

                arrivalList.querySelectorAll('[data-field="actcontdate"]').forEach(input => {
                    if ($(input).data('datetimebox')) $(input).datetimebox('destroy');
                });

                arrivalList.innerHTML = '';

                sourceItems.forEach((sourceItem, i) => {
                    const arrivalClone = arrivalTemplate.content.cloneNode(true);
                    const arrivalEl = arrivalClone.querySelector('.container-item');
                    if (!arrivalEl) return;

                    const numEl = arrivalEl.querySelector('.container-arrival-num');
                    if (numEl) numEl.textContent = i + 1;

                    const vessnameInput = sourceItem.querySelector('[data-field="vessname"]');
                    const vessLabel = arrivalEl.querySelector('.container-arrival-vessel');
                    if (vessLabel) vessLabel.textContent = vessnameInput?.value || '-';

                    const saved = existingValues[i] || {};
                    ['contno', 'nopol'].forEach(field => {
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

                    arrivalList.appendChild(arrivalEl);

                    const actInput = arrivalEl.querySelector('[data-field="actcontdate"]');
                    if (actInput) {
                        actInput.name = `containers[${i}][actcontdate]`;
                        actInput.dataset.presetValue = saved.actcontdate || sourceItem.dataset.actcontdate || '';
                    }
                });

                arrivalCountPill.textContent = sourceItems.length + ' Container';
                refreshPaneWidgets('info');
            }

            containerList.addEventListener('input', function(e) {
                if (e.target.dataset.field === 'vessname') syncContainerArrivalTab();
            });

            /* ---- multi-slot foto upload (foto1..foto5) ---- */
            const FOTO_SLOTS = ['foto1', 'foto2', 'foto3', 'foto4', 'foto5'];
            const containerFotoFiles = new Map();
            const containerFotoUrls = new Map();
            const containerFotoRemoved = new Set();
            const fotoKey = (index, slot) => `${index}:${slot}`;

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
                const arrivalItems = arrivalListEl ? arrivalListEl.querySelectorAll('.container-item') : [];

                const existingNoseal = {};
                fotoList.querySelectorAll('.container-item').forEach((el, i) => {
                    const input = el.querySelector('[data-field="noseal"]');
                    if (input) existingNoseal[i] = input.value;
                });

                fotoList.innerHTML = '';

                sourceItems.forEach((sourceItem, i) => {
                    const clone = fotoTemplate.content.cloneNode(true);
                    const el = clone.querySelector('.container-item');
                    el.dataset.index = i;

                    const nosealInput = el.querySelector('[data-field="noseal"]');
                    if (nosealInput) {
                        nosealInput.name = `containers[${i}][noseal]`;
                        nosealInput.value = existingNoseal[i] || sourceItem.dataset.noseal || '';
                    }

                    el.querySelector('.container-foto-num').textContent = i + 1;

                    const arrivalItem = arrivalItems[i];
                    const contnoInput = arrivalItem?.querySelector('[data-field="contno"]');
                    el.querySelector('.container-foto-vessel').textContent = contnoInput?.value || '-';

                    const start_ship = sourceItem.querySelector('[data-field="start_ship"]');
                    const btnfoto = el.querySelector('#btn-camera');
                    if (btnfoto) btnfoto.disabled = !start_ship?.value?.trim();

                    FOTO_SLOTS.forEach((slot, slotIdx) => {
                        const slotEl = el.querySelector(`[data-photo-slot="${slot}"]`);
                        if (!slotEl) return;
                        const key = fotoKey(i, slot);
                        const file = containerFotoFiles.get(key);
                        const url = file ? null : containerFotoUrls.get(key);

                        if (slotIdx === 0 || file || url) slotEl.classList.remove('d-none');

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

            function renderFotoPreview(slotEl, file) {
                const previewWrap = slotEl.querySelector('.container-foto-preview');
                if (!previewWrap) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewWrap.innerHTML = `<img src="${e.target.result}" style="max-width:120px;max-height:120px;border-radius:8px;border:1px solid #EAECF0;">`;
                };
                reader.readAsDataURL(file);
            }

            document.addEventListener('click', function(e) {
                const fotoList = document.getElementById('containerFotoList');
                if (!fotoList || !fotoList.contains(e.target)) return;
                const cardEl = e.target.closest('.container-item');
                if (!cardEl) return;

                if (e.target.closest('.btn-add-foto')) {
                    const nextHidden = cardEl.querySelector('.container-foto-slot.d-none');
                    if (nextHidden) nextHidden.classList.remove('d-none');
                    refreshAddFotoBtn(cardEl);
                    return;
                }

                const slotEl = e.target.closest('.container-foto-slot');
                if (!slotEl) return;
                const index = parseInt(cardEl.dataset.index, 10);
                const slot = slotEl.dataset.photoSlot;

                if (e.target.closest('.btn-camera')) {
                    slotEl.querySelector('.input-camera').click();
                    return;
                }

                if (e.target.closest('.close-camera')) {
                    const key = fotoKey(index, slot);
                    containerFotoFiles.delete(key);
                    containerFotoUrls.delete(key);
                    clearFotoSlot(slotEl);
                    containerFotoRemoved.add(key);
                }
            });

            document.addEventListener('change', function(e) {
                if (!e.target.classList.contains('input-camera')) return;
                const cardEl = e.target.closest('.container-item');
                const slotEl = e.target.closest('.container-foto-slot');
                if (!cardEl || !slotEl) return;
                const index = parseInt(cardEl.dataset.index, 10);
                const slot = slotEl.dataset.photoSlot;
                const file = e.target.files[0];
                if (!file) return;

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

                const key = fotoKey(index, slot);
                containerFotoFiles.set(key, file);
                containerFotoRemoved.delete(key);
                slotEl.querySelector('.file-name-camera').textContent = file.name;
                slotEl.querySelector('.close-camera').classList.remove('d-none');
                renderFotoPreview(slotEl, file);
            });

            function addContainerCard(data = null) {
                const clone = template.content.cloneNode(true);
                const el = clone.querySelector('.container-item');
                const volumeMap = { '20ft': 33, '40ft': 67, '40hc': 76 };
                const volumeInput = el.querySelector('[data-field="volume"]');
                const volumeContainer = el.querySelector('#volumeContainer');
                let savedLclVolume = '';

                function setVolume(type, isEdit = false) {
                    if (type === 'LCL') {
                        volumeContainer.classList.remove('d-none');
                        volumeInput.readOnly = false;
                        volumeInput.value = savedLclVolume;
                        return;
                    }
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
                    radio.addEventListener('change', function() { setVolume(this.value); });
                });

                el.querySelector('.btn-remove-container').addEventListener('click', () => removeContainerCard(el));

                if (data) {
                    el.querySelectorAll('[data-field]').forEach(input => {
                        const field = input.dataset.field;
                        if (data[field] !== undefined && data[field] !== null) input.value = data[field];
                    });

                    if (data.type) {
                        const radio = el.querySelector(`input[type="radio"][value="${data.type}"]`);
                        if (radio) radio.checked = true;
                        if (data.type === 'LCL') {
                            savedLclVolume = data.volume ?? '';
                            setVolume('LCL', true);
                        }
                    }

                    if (data.contpk) el.dataset.contpk = data.contpk;
                    ['contno', 'nopol', 'noseal', 'actcontdate'].forEach(field => {
                        if (data[field]) el.dataset[field] = data[field];
                    });
                }

                containerList.appendChild(el);
                renumber();
            }

            function removeContainerCard(el) {
                if (containerList.querySelectorAll('.container-item').length === 1) return;
                el.classList.add('removing');
                el.addEventListener('animationend', () => { el.remove(); renumber(); }, { once: true });
            }

            btnAdd.addEventListener('click', addContainerCard);

            document.getElementById('modalCreateContainer').addEventListener('shown.bs.modal', function() {
                if (currentMode === 'create' && containerList.children.length === 0) addContainerCard();
                syncContainerArrivalTab();
                syncContainerFotoTab();
                goTo(0);

                $('#exdate').datetimebox({ onChange: function() { onExdateChange(); } });
                $('#closetime').datetimebox({ editable: false, onChange: function(newValue) { validateAndCorrectCloseTime(newValue); } });

                if (window.__pendingExdateEditData) {
                    const { exdate, closetime } = window.__pendingExdateEditData;
                    const [datePart, timePart] = exdate.split(' ');
                    const [year, month, day] = datePart.split('-');
                    const formatted = `${day}/${month}/${year} ${timePart.substring(0, 5)}`;
                    if (exdate) $('#exdate').datetimebox('setValue', formatted);
                    if (exdate) $('#closetime').datetimebox('enable');

                    const [datePart2, timePart2] = closetime.split(' ');
                    const [year2, month2, day2] = datePart2.split('-');
                    const formatted2 = `${day2}/${month2}/${year2} ${timePart2.substring(0, 5)}`;
                    if (closetime) $('#closetime').datetimebox('setValue', formatted2);

                    window.__pendingExdateEditData = null;
                }

                onExdateChange();
            });

            let currentMode = 'create';
            let currentExportpk = null;

            window.openEditShipmentModal = function(exportpk) {
                currentMode = 'edit';
                currentExportpk = exportpk;

                containerFotoFiles.clear();
                containerFotoUrls.clear();
                containerFotoRemoved.clear();

                let dataLoaded = false;
                let stage = 'fetch';

                const storeContainerUrl = "{{ route('export.detail-json', ['exportpk' => '__EXPORTPK__']) }}";
                fetch(storeContainerUrl.replace('__EXPORTPK__', exportpk), { headers: { 'Accept': 'application/json' } })
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
                        $('#POnop').text(data.POno ? 'PO : ' + data.POno : 'No PO Number');

                        const pebno = form.querySelector('[name="pebno"]'); if (pebno) pebno.value = data.pebno ?? '';
                        const totqty = form.querySelector('[name="totqty"]'); if (totqty) totqty.value = data.totqty ?? '';
                        const totnw = form.querySelector('[name="totnw"]'); if (totnw) totnw.value = data.totnw ?? '';
                        const totgw = form.querySelector('[name="totgw"]'); if (totgw) totgw.value = data.totgw ?? '';
                        const totvol = form.querySelector('[name="totvol"]'); if (totvol) totvol.value = data.totvol ?? '';
                        const ukctn = form.querySelector('[name="ukctn"]'); if (ukctn) ukctn.value = data.ukctn ?? '';
                        const totctn = form.querySelector('[name="totctn"]'); if (totctn) totctn.value = data.totctn ?? '';

                        stage = 'combobox';
                        const vassalCombo = document.getElementById('vassal');
                        if (vassalCombo && data.vassalpk !== undefined && data.vassalpk !== null) {
                            if ($(vassalCombo).data('combobox')) $('#vassal').combobox('setValue', data.vassalpk);
                            else vassalCombo.value = data.vassalpk;
                        }

                        const forwarderCombo = document.getElementById('fwd');
                        if (forwarderCombo && data.fwdpk !== undefined && data.fwdpk !== null) {
                            if ($(forwarderCombo).data('combobox')) $('#fwd').combobox('setValue', data.fwdpk);
                            else forwarderCombo.value = data.fwdpk;
                        }

                        const plbCombo = document.getElementById('plb');
                        if (plbCombo && data.plbpk !== undefined && data.plbpk !== null) {
                            if ($(plbCombo).data('combobox')) $('#plb').combobox('setValue', data.plbpk);
                            else plbCombo.value = data.plbpk;
                        }

                        stage = 'tanggal';
                        const contdate = form.querySelector('[name="contdate"]');
                        if (contdate && data.contdate) {
                            const [year, month, day] = data.contdate.split('-');
                            $('#contdate').datebox('setValue', `${day}/${month}/${year}`);
                        }

                        const actcontdate = form.querySelector('[name="actcontdate"]');
                        if (actcontdate && data.actcontdate) {
                            const [year, month, day] = data.actcontdate.split('-');
                            $('#actcontdate').datebox('setValue', `${day}/${month}/${year}`);
                        }

                        const etdvess = form.querySelector('[name="etdvess"]');
                        if (etdvess && data.etdvess) {
                            const [year, month, day] = data.etdvess.split('-');
                            $('#etdvess').datebox('setValue', `${day}/${month}/${year}`);
                        }

                        const etavess = form.querySelector('[name="etavess"]');
                        if (etavess && data.etavess) {
                            const [year, month, day] = data.etavess.split('-');
                            $('#etavess').datebox('setValue', `${day}/${month}/${year}`);
                        }

                        const closetime = form.querySelector('[name="closetime"]');
                        if (closetime && data.closetime) {
                            const [datePart, timePart] = data.closetime.split(' ');
                            const [year, month, day] = datePart.split('-');
                            $('#closetime').datetimebox('setValue', `${day}/${month}/${year} ${timePart}`);
                        }

                        const exdate = form.querySelector('[name="exdate"]');
                        if (exdate && data.exdate) {
                            const [datePart, timePart] = data.exdate.split(' ');
                            const [year, month, day] = datePart.split('-');
                            $('#exdate').datetimebox('setValue', `${day}/${month}/${year} ${timePart}`);
                        }

                        const remark = form.querySelector('[name="remark"]'); if (remark) remark.value = data.remark ?? '';
                        const contno = form.querySelector('[name="contno"]'); if (contno) contno.value = data.contno ?? '';

                        stage = 'containers';
                        containerList.innerHTML = '';
                        if (data.containers && data.containers.length) {
                            data.containers.forEach((c, index) => {
                                addContainerCard(c);
                                const urls = c.foto_urls || {};
                                FOTO_SLOTS.forEach(slot => {
                                    if (urls[slot]) containerFotoUrls.set(fotoKey(index, slot), urls[slot]);
                                });
                            });
                        } else {
                            addContainerCard();
                        }

                        const modalTitle = document.querySelector('.mc-title');
                        if (modalTitle) modalTitle.textContent = 'Edit Shipment Plan';

                        const submitBtnLabel = document.getElementById('mcSubmitBtn');
                        if (submitBtnLabel) submitBtnLabel.innerHTML = '<i class="bi bi-check2-circle"></i> Simpan Perubahan';

                        stage = 'show-modal';
                        new bootstrap.Modal(document.getElementById('modalCreateContainer')).show();

                        window.__pendingExdateEditData = { exdate: data.exdate, closetime: data.closetime };
                    })
                    .catch(err => {
                        const detail = err ? (err.stack || err.message || String(err)) : String(err);
                        console.error('[shipplan] gagal di tahap "' + stage + '":', detail);
                        console.error('[shipplan] objek error mentah:', err);

                        showToast(
                            dataLoaded ? 'Gagal menampilkan modal di tahap: ' + stage : 'Gagal memuat data shipment plan.',
                            'error'
                        );
                    });
            };

            $('input[name="ctype"]').on('change', function() {
                const ctype = this.value;
                const volumeInput = $('input[data-field="volume"]');
                const volume = { '20ft': 33, '40ft': 67, '40hc': 76 };
                if (volume[ctype] !== undefined) volumeInput.val(volume[ctype]).prop('readonly', true);
                else volumeInput.val('').prop('readonly', false).focus();
            });

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

                const closeCal = $('#closetime').datetimebox('calendar');
                if (closeCal.length) {
                    closeCal.calendar({ disabledDate: function(date) { return date < minDate; } });
                }
                document.getElementById('closetime').dataset.minDate = minDate.getTime();
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

            document.getElementById('modalCreateContainer').addEventListener('hidden.bs.modal', function() {
                currentMode = 'create';
                currentExportpk = null;
                containerList.innerHTML = '';
                form.reset();
                const modalTitle = document.querySelector('.mc-title');
                if (modalTitle) modalTitle.textContent = 'Create New Shipment Plan';
                const submitBtnLabel = document.getElementById('mcSubmitBtn');
                if (submitBtnLabel) submitBtnLabel.innerHTML = '<i class="bi bi-check2-circle"></i> Buat Shipment Plan';
            });

            function convertDateFields(formData, fields) {
                fields.forEach(field => {
                    const el = document.querySelector(`[name="${field}"]`);
                    if (!el) return;
                    let value;
                    if ($(el).hasClass('easyui-datetimebox')) value = $(el).datetimebox('getValue');
                    else if ($(el).hasClass('easyui-datebox')) value = $(el).datebox('getValue');
                    if (!value) return;
                    const parts = value.split(' ');
                    const date = parts[0], time = parts[1];
                    const [day, month, year] = date.split('/');
                    const converted = time ? `${year}-${month}-${day} ${time}` : `${year}-${month}-${day}`;
                    formData.set(field, converted);
                });
            }

            const form = document.getElementById('formCreateContainer');
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMeta ? csrfMeta.content : '';

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearValidationErrors();

                const exportpk = @json($header->exportpk);
                const formData = new FormData(form);

                containerFotoFiles.forEach((file, key) => {
                    const [index, slot] = key.split(':');
                    formData.append(`containers[${index}][${slot}]`, file);
                });

                containerFotoRemoved.forEach(key => {
                    const [index, slot] = key.split(':');
                    formData.append(`containers[${index}][${slot}_removed]`, '1');
                });

                convertDateFields(formData, ['exdate', 'closetime', 'etdvess', 'etavess', 'contdate', 'actcontdate']);

                submitBtn.disabled = true;
                const originalSubmitHtml = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

                const storeContainerUrl = "{{ route('export.containers.store', ['exportpk' => '__EXPORTPK__']) }}";
                fetch(storeContainerUrl.replace('__EXPORTPK__', exportpk), {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: formData,
                    })
                    .then(async (res) => {
                        const result = await res.json();
                        if (res.status === 422) return showValidationErrors(result.errors);
                        if (!res.ok) throw new Error(result.message || 'Terjadi kesalahan pada server');

                        bootstrap.Modal.getInstance(document.getElementById('modalCreateContainer')).hide();
                        form.reset();
                        containerList.innerHTML = '';

                        sessionStorage.setItem('ldToastMessage', result.message || `Data berhasil di update.`);
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

            let contpk = null, exportpk = null, ordpk = null, poNo = null, ordno = null, contno = null;
            let currentGridUrl = null, gridInitialized = false;

            window.openLoadingModal = function(_contpk, _contno, _exportpk, _ordpk, _poNo, _ordno) {
                contpk = _contpk; contno = _contno; exportpk = _exportpk; ordpk = _ordpk; poNo = _poNo; ordno = _ordno;

                const availableCartonsUrl = "{{ route('export.cartons.available', ['exportpk' => '__EXPORTPK__', 'contpk' => '__CONTPK__']) }}";
                currentGridUrl = availableCartonsUrl.replace('__EXPORTPK__', exportpk).replace('__CONTPK__', contpk) +
                    `?ordpk=${ordpk}&pono=${encodeURIComponent(poNo)}&q=`;

                setText('modalContainerNo', contno);
                setText('modalPoNo', poNo || '-');
                setText('modalNoOp', ordno || '-');

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
                const defaultLabel = select.options[0];
                select.innerHTML = '';
                select.appendChild(defaultLabel);
                const values = [...new Set((commaSeparatedValues || '').split(',').map(v => v.trim()).filter(Boolean))];
                values.forEach(val => {
                    const opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = val;
                    select.appendChild(opt);
                });
            }

            function setText(id, value) {
                const el = document.getElementById(id);
                if (!el) { console.warn(`Elemen #${id} tidak ditemukan di DOM.`); return; }
                el.textContent = value;
            }

            modalEl.addEventListener('shown.bs.modal', function() {
                if (!gridInitialized) { initGrid(); gridInitialized = true; }
                else { $('#dgDetailCarton').datagrid({ url: currentGridUrl }); }
                reloadCartonGrid();
            });

            function initGrid() {
                $('#dgDetailCarton').datagrid({
                    url: currentGridUrl, width: '100%', height: 300, fit: false, fitColumns: true,
                    rownumbers: false, singleSelect: false, checkOnSelect: true, selectOnCheck: true,
                    showHeader: false, striped: false, border: false, nowrap: false, method: 'get',
                    loadMsg: 'Memuat daftar carton siap muat...',
                    rowStyler: function() { return 'ld-card-row'; },
                    columns: [
                        [
                            { field: 'ck', checkbox: true, width: 20 },
                            {
                                field: 'info', title: '', width: 'auto',
                                formatter: function(val, row) {
                                    const part = row.part ? (Number(row.part) === 10 ? "Session Complete" : "Session " + row.part) : "";
                                    const statusVal = Number(row.status);
                                    const statusClass = statusVal === 4 ? 'Complete' : statusVal === 5 ? 'Segel' : 'default';
                                    const packpks = Array.isArray(row.packpks) ? row.packpks : [];
                                    const checked = packpks.some(function(packpk) { return selectedIds.has(String(packpk)); });
                                    const isLoaded = Number(row.exportdtpk) > 0;

                                    let sizesHtml = '';
                                    (row.details || []).forEach(function(item) {
                                        sizesHtml += `
                                            <span class="dg-size-item">
                                                ${item.material ? escapeHtml(item.material) + ' / ' : ''}
                                                ${escapeHtml(item.size)} :
                                                ${item.qty}
                                            </span>
                                        `;
                                    });

                                    const packpkJson = JSON.stringify(packpks).replace(/"/g, '&quot;');

                                    const poOpLabel = !row.is_bundle
                                        ? `<div class="dg-carton-poop">PO: ${escapeHtml(row.POno || '-')} / #OP: ${escapeHtml(row.OP || '-')}</div>`
                                        : '';

                                    const bundleLabel = row.is_bundle
                                        ? `<div class="dg-bundle-badge">
                                            <i class="bi bi-boxes"></i> Bundle dari ${row.bundle_cartons.length} carton:
                                            <ul class="dg-bundle-list">
                                                ${row.bundle_cartons.map(c => `
                                                    <li>Ctn.${escapeHtml(c.carton)} — PO: ${escapeHtml(c.POno || '-')} / #OP: ${escapeHtml(c.OP || '-')}</li>
                                                `).join('')}
                                            </ul>
                                        </div>`
                                        : '';

                                    return `
                                        <div class="dg-card ${isLoaded ? 'dg-card-disabled' : ''}"
                                            data-packpk="${row.packpk}"
                                            ${isLoaded ? '' : `onclick="toggleCartonRow(${packpkJson})"`}>

                                            <span class="dg-card-check ${checked ? 'checked' : ''} ${isLoaded ? 'dg-check-disabled' : ''}">
                                                ${isLoaded ? '<i class="bi bi-lock-fill"></i>' : '<i class="bi bi-check-lg"></i>'}
                                            </span>

                                            <div class="dg-card-icon"><i class="bi bi-box-seam"></i></div>

                                            <div class="dg-card-main">
                                                <div class="dg-card-no">
                                                    ${row.is_bundle ? 'Bundle Carton' : 'Carton Number'} : ${escapeHtml(String(row.carton))}
                                                    ${row.is_bundle ? '<span class="dg-bundle-tag">BUNDLE</span>' : ''}
                                                </div>
                                                ${poOpLabel}
                                                <div class="dg-card-style dg-card-sizes">${sizesHtml}</div>
                                                ${bundleLabel}
                                            </div>

                                            <div class="dg-card-meta">
                                                <span class="dg-card-qty">Qty Per Carton : ${row.pcsp ?? 0} pcs</span>
                                                ${isLoaded
                                                    ? `<span class="dg-card-status loaded">Sudah Dimuat</span>
                                                       <span class="dg-card-session ${row.part > 0 ? '' : 'd-none'}">${part}</span>`
                                                    : `<span class="dg-card-status ${statusClass}">${statusClass}</span>
                                                       <span class="dg-card-session ${row.part > 0 ? '' : 'd-none'}">${part}</span>`
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
                    onSelect: updateSummary,
                    onUnselect: updateSummary,
                    onBeforeCheck: function(index, row) { return Number(row.exportdtpk) <= 0; },
                    onBeforeSelect: function(index, row) { return Number(row.exportdtpk) <= 0; },
                    onLoadSuccess: function(data) {
                        const dg = $(this);
                        dg.datagrid('getRows').forEach(function(row, index) {
                            if (Number(row.exportdtpk) > 0) {
                                const tr = dg.datagrid('getPanel').find('div.datagrid-body table.datagrid-btable tr[datagrid-row-index="' + index + '"]');
                                tr.find('input[type="checkbox"]').prop('disabled', true);
                            }
                        });

                        resetSummary();

                        const noData = !data.rows || data.rows.length === 0;
                        const view = $('#dgDetailCarton').datagrid('getPanel').find('.datagrid-view');
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
                        if (isLoaded) return;
                        const id = String(row.packpk);
                        if (selectedIds.has(id)) selectedIds.delete(id);
                        else selectedIds.add(id);
                        $('#dgDetailCarton').datagrid('refreshRow', index);
                        updateSummary();
                    },
                    rowStyler: function(index, row) {
                        const isLoaded = Number(row.exportdtpk) > 0;
                        if (isLoaded) return 'ld-card-row ld-card-row-disabled';
                        return selectedIds.has(String(row.packpk)) ? 'ld-card-row ld-card-row-checked' : 'ld-card-row';
                    },
                });
            }

            function escapeHtml(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
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

            window.toggleSelectAllTest = function() {
                const dg = $('#dgDetailCarton');
                const allRows = dg.datagrid('getRows');
                const selectableRows = allRows.filter(r => !(Number(r.exportdtpk) > 0));
                const allChecked = selectableRows.length > 0 && selectableRows.every(r => selectedIds.has(String(r.packpk)));

                selectableRows.forEach(row => {
                    const id = String(row.packpk);
                    const index = allRows.findIndex(r => String(r.packpk) === id);
                    if (allChecked) {
                        selectedIds.delete(id);
                        if (index >= 0) dg.datagrid('uncheckRow', index);
                    } else {
                        selectedIds.add(id);
                        if (index >= 0) dg.datagrid('checkRow', index);
                    }
                });

                btnSelectAll.classList.toggle('active', !allChecked);
                updateSummary();
            };

            window.toggleCartonRow = function(packpk) {
                if (selectedIds.has(packpk)) selectedIds.delete(packpk);
                else selectedIds.add(packpk);
                const rows = $('#dgDetailCarton').datagrid('getRows');
                const index = rows.findIndex(r => String(r.packpk) === packpk);
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

            window.submitLoadCarton = function() {
                const checked = $('#dgDetailCarton').datagrid('getChecked');
                if (!checked.length) return;
                const cartonIds = checked.map(row => row.packpk).flat().map(Number);

                btnSubmit.disabled = true;
                const originalHtml = btnSubmit.innerHTML;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memuat...';

                fetch("{{ route('containers.load') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ contpk: contpk, exportpk: exportpk, ordpk: ordpk, packpk: cartonIds }),
                    })
                    .then(async res => {
                        const text = await res.text();
                        let result;
                        try { result = JSON.parse(text); }
                        catch { throw new Error('Server mengembalikan respons tidak valid (status ' + res.status + ')'); }
                        if (!res.ok) throw new Error(result.message || 'Gagal memuat carton ke container.');

                        try { showChipToDOM(contpk, result.loaded_items); } catch (e) { console.error('showChipToDOM error:', e); }
                        try { updateHeaderTotals(result.totqty, result.totctn, result.totnw, result.totgw, result.totvol); } catch (e) { console.error('updateHeaderTotals error:', e); }
                        try { updateContainerImage(contpk); } catch (e) { console.error('updateContainerImage error:', e); }
                        try { markRowsAsLoaded(cartonIds); } catch (e) { console.error('markRowsAsLoaded error:', e); }

                        modal.hide();
                        showToast(result.message || `${cartonIds.length} carton berhasil dimuat ke container.`, 'success');
                    })
                    .catch(err => alert(err.message))
                    .finally(() => {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = originalHtml;
                    });
            };

            function computeChipGroupKey(item) {
                const raw = item.bundlepk ? `bundle_${item.bundlepk}` : `${item.carton ?? ''}|${item.POno ?? ''}|${item.OP ?? ''}`;
                return raw.replace(/[^A-Za-z0-9]/g, '_');
            }

            function roundCbm(value) { return Number((Number(value) || 0).toFixed(6)); }

            function smallCartonKey(sc) { return `${sc.carton ?? ''}|${sc.POno ?? ''}|${sc.OP ?? ''}`; }

            function splitCsv(value) {
                return String(value ?? '').split(',').map(v => v.trim()).filter(Boolean);
            }

            function showChipToDOM(contpk, items) {
                const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
                if (!chipsWrap || !items || !items.length) return;

                const emptyMsg = chipsWrap.querySelector('.ld-chip-empty');
                if (emptyMsg) emptyMsg.remove();

                const groupedByCarton = {};
                items.forEach(item => {
                    const isBundle = !!item.bundlepk;
                    const groupKey = computeChipGroupKey(item);

                    if (!groupedByCarton[groupKey]) {
                        groupedByCarton[groupKey] = {
                            exportdtpks: [], packpks: [], totalPcs: 0, totalCbm: 0, isBundle,
                            bundlepk: item.bundlepk || null,
                            displayCarton: isBundle ? (item.bundle_carton ?? item.carton) : item.carton,
                            POno: item.POno || '', OP: item.OP || '', smallCartons: new Map(),
                        };
                    }

                    const g = groupedByCarton[groupKey];
                    if (item.exportdtpk) g.exportdtpks.push(item.exportdtpk);
                    if (item.packpk) g.packpks.push(item.packpk);
                    g.totalPcs += Number(item.pcsp) || 0;
                    g.totalCbm += Number(item.t_cbm) || 0;

                    if (isBundle) {
                        g.smallCartons.set(smallCartonKey(item), {
                            carton: item.carton, POno: item.POno ?? '', OP: item.OP ?? '', pcsp: Number(item.pcsp) || 0,
                        });
                    }
                });

                Object.keys(groupedByCarton).forEach(groupKey => {
                    const newData = groupedByCarton[groupKey];
                    const chipId = `chip-carton-${groupKey}-${contpk}`;
                    const existingChip = document.getElementById(chipId);

                    if (existingChip) {
                        const existingExportdtpks = splitCsv(existingChip.dataset.exportdtpks);
                        const mergedExportdtpks = [...new Set([...existingExportdtpks, ...newData.exportdtpks.map(String)])];

                        const existingPackpks = splitCsv(existingChip.dataset.packpks);
                        const mergedPackpks = [...new Set([...existingPackpks, ...newData.packpks.map(String)])];

                        const qtyEl = existingChip.querySelector('.ld-chip-qty');
                        const existingQty = parseInt(qtyEl?.textContent) || 0;
                        const mergedQty = existingQty + newData.totalPcs;
                        const existingCbm = parseFloat(existingChip.dataset.cbm) || 0;
                        const mergedCbm = roundCbm(existingCbm + newData.totalCbm);

                        existingChip.dataset.exportdtpks = mergedExportdtpks.join(',');
                        existingChip.dataset.packpks = mergedPackpks.join(',');
                        existingChip.dataset.cbm = mergedCbm;
                        if (qtyEl) qtyEl.textContent = `${mergedQty} pcs`;

                        if (newData.isBundle) mergeBundleDetailIntoChip(existingChip, newData.smallCartons);
                    } else {
                        chipsWrap.appendChild(buildChipWrapEl(chipId, contpk, newData, groupKey));
                    }
                });

                refreshContainerStats(contpk);
            }

            function buildChipWrapEl(chipId, contpk, data, groupKey) {
                const wrap = document.createElement('div');
                wrap.className = 'ld-chip-wrap';
                wrap.style.display = 'inline-flex';
                wrap.style.flexDirection = 'column';
                wrap.style.alignItems = 'stretch';
                wrap.style.verticalAlign = 'top';

                const chip = document.createElement('span');
                chip.className = 'ld-chip';
                chip.id = chipId;
                chip.dataset.exportdtpks = data.exportdtpks.join(',');
                chip.dataset.packpks = data.packpks.join(',');
                chip.dataset.contpk = contpk;
                chip.dataset.exportpk = {{ $header->exportpk }};
                chip.dataset.cartonNo = data.displayCarton;
                chip.dataset.groupKey = groupKey;
                chip.dataset.isBundle = data.isBundle ? '1' : '0';
                chip.dataset.bundlepk = data.bundlepk ?? '';
                chip.dataset.shipped = '0';
                chip.dataset.cbm = roundCbm(data.totalCbm);
                chip.dataset.smallCartons = JSON.stringify(data.isBundle ? Array.from(data.smallCartons.values()) : []);

                const bundleBadge = data.isBundle
                    ? `<span class="ld-chip-bundle-badge" style="font-size:.65rem;opacity:.75;" title="Gabungan ${data.smallCartons.size} carton kecil">📦 x${data.smallCartons.size}</span>`
                    : '';

                const poOpLabel = [data.POno, data.OP].filter(Boolean).join(' / ');
                const poOpEl = poOpLabel ? `<span class="ld-chip-poop" style="font-size:.68rem;opacity:.7;">${poOpLabel}</span>` : 'x';

                chip.title = poOpLabel || 'PO/OP tidak diketahui';

                chip.innerHTML = `
                    <span class="ld-chip-no">Ctn.${data.displayCarton}</span>
                    ${bundleBadge}
                    ${poOpEl}
                    <span class="ld-chip-qty">${data.totalPcs} pcs</span>
                    <button type="button" class="ld-chip-remove" title="Keluarkan carton ini">
                        <i class="bi bi-x"></i>
                    </button>
                `;

                wrap.appendChild(chip);

                if (data.isBundle && data.smallCartons.size) wrap.appendChild(buildBundleDetailEl(data.smallCartons));

                return wrap;
            }

            function buildBundleDetailEl(smallCartonsMap) {
                const details = document.createElement('details');
                details.className = 'ld-chip-bundle-detail';
                details.style.marginTop = '.25rem';
                details.style.fontSize = '.7rem';
                details.style.background = '#fff';
                details.style.border = '1px solid rgba(0,0,0,.08)';
                details.style.borderRadius = '8px';
                details.style.padding = '.35rem .5rem';
                details.style.boxShadow = '0 1px 3px rgba(0,0,0,.06)';

                details.innerHTML = `
                    <summary style="cursor:pointer;opacity:.75;list-style:none;"></summary>
                    <div class="ld-chip-bundle-list" style="margin-top:.3rem;display:flex;flex-direction:column;gap:.15rem;"></div>
                `;

                renderBundleDetailContent(details, smallCartonsMap);
                return details;
            }

            function renderBundleDetailContent(detailsEl, smallCartonsMap) {
                const summary = detailsEl.querySelector('summary');
                const list = detailsEl.querySelector('.ld-chip-bundle-list');
                summary.textContent = `Detail ${smallCartonsMap.size} carton kecil`;
                list.innerHTML = Array.from(smallCartonsMap.values()).map(sc => {
                    const poOp = [sc.POno, sc.OP].filter(Boolean).join(' / ');
                    const poOpHtml = poOp ? ` <span style="opacity:.7;">(${poOp})</span>` : '';
                    return `<div class="ld-chip-bundle-item" style="opacity:.85;white-space:nowrap;">Ctn.${sc.carton}${poOpHtml} &mdash; ${sc.pcsp} pcs</div>`;
                }).join('');
            }

            function refreshContainerStats(contpk) {
                const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
                if (!chipsWrap) return;

                const chips = chipsWrap.querySelectorAll('.ld-chip');

                const emptyMsg = chipsWrap.querySelector('.ld-chip-empty');
                if (chips.length === 0 && !emptyMsg) {
                    const el = document.createElement('div');
                    el.className = 'ld-chip-empty';
                    el.textContent = 'Belum ada carton yang dimuat.';
                    chipsWrap.appendChild(el);
                } else if (chips.length > 0 && emptyMsg) {
                    emptyMsg.remove();
                }

                let totalCbm = 0;
                chips.forEach(chip => { totalCbm += parseFloat(chip.dataset.cbm) || 0; });

                const toggleLabel = chipsWrap.previousElementSibling?.querySelector('span');
                if (toggleLabel) toggleLabel.textContent = `${chips.length} carton dimuat`;

                const countEl = document.getElementById(`loadedCount-${contpk}`);
                if (countEl) countEl.textContent = `${chips.length} ctn (${totalCbm.toFixed(3)} cbm)`;

                const barEl = document.getElementById(`loadedBar-${contpk}`);
                if (barEl) {
                    const target = Number(barEl.dataset.target) || 1;
                    barEl.style.width = Math.min(100, (totalCbm / target) * 100) + '%';
                }

                updateRemoveAllButtonState(contpk);
            }

            window.computeChipGroupKey = computeChipGroupKey;
            window.renderBundleDetailContent = renderBundleDetailContent;
            window.roundCbm = roundCbm;
            window.splitCsv = splitCsv;
            window.smallCartonKey = smallCartonKey;
            window.refreshContainerStats = refreshContainerStats;

            function mergeBundleDetailIntoChip(existingChip, newSmallCartonsMap) {
                let stored = new Map();
                readSmallCartons(existingChip).forEach(sc => { stored.set(smallCartonKey(sc), sc); });
                newSmallCartonsMap.forEach((sc) => { stored.set(smallCartonKey(sc), sc); });

                existingChip.dataset.smallCartons = JSON.stringify(Array.from(stored.values()));

                const wrap = existingChip.closest('.ld-chip-wrap') || existingChip.parentElement;
                const badge = existingChip.querySelector('.ld-chip-bundle-badge');
                if (badge) {
                    badge.textContent = `📦 x${stored.size}`;
                    badge.title = `Gabungan ${stored.size} carton kecil`;
                }

                let detailsEl = wrap ? wrap.querySelector('.ld-chip-bundle-detail') : null;
                if (detailsEl) renderBundleDetailContent(detailsEl, stored);
                else if (wrap) wrap.appendChild(buildBundleDetailEl(stored));
            }

            const toastEl = document.getElementById('ldToast');
            const bsToast = new bootstrap.Toast(toastEl, { delay: 3500 });

            document.addEventListener('DOMContentLoaded', function() {
                const msg = sessionStorage.getItem('ldToastMessage');
                if (msg) {
                    showToast(msg, sessionStorage.getItem('ldToastType') || 'success');
                    sessionStorage.removeItem('ldToastMessage');
                    sessionStorage.removeItem('ldToastType');
                }
            });

            window.selectCartonByRange = function() {
                const input = document.getElementById('cartonRangeInput');
                const rawValue = input.value.trim();

                if (!rawValue) {
                    showToast('Masukkan nomor carton atau range terlebih dahulu.', 'error');
                    return;
                }

                const targetCartons = parseCartonRange(rawValue);
                if (targetCartons === null) {
                    showToast('Format range tidak valid. Gunakan contoh: 1-50 atau 1,5,10-20', 'error');
                    return;
                }

                const dg = $('#dgDetailCarton');
                const allRows = dg.datagrid('getRows');

                let matchedCount = 0, skippedLocked = 0;

                allRows.forEach((row, index) => {
                    const cartonNum = parseInt(row.carton, 10);
                    if (!targetCartons.has(cartonNum)) return;

                    if (Number(row.exportdtpk) > 0) { skippedLocked++; return; }

                    const packpkArr = Array.isArray(row.packpks) ? row.packpks : (row.packpks != null ? [row.packpks] : []);
                    packpkArr.forEach(pk => { selectedIds.add(String(pk)); });

                    dg.datagrid('checkRow', index);
                    matchedCount++;
                });

                allRows.forEach((row, index) => {
                    const cartonNum = parseInt(row.carton, 10);
                    if (targetCartons.has(cartonNum)) dg.datagrid('refreshRow', index);
                });

                updateSummary();

                if (matchedCount === 0) {
                    showToast('Tidak ada carton yang cocok dengan range tersebut.', 'error');
                } else {
                    let msg = `${matchedCount} carton berhasil dipilih.`;
                    if (skippedLocked > 0) msg += ` (${skippedLocked} carton dilewati karena sudah dimuat)`;
                    showToast(msg, 'success');
                }
            };

            function parseCartonRange(rawValue) {
                const result = new Set();
                const parts = rawValue.split(',').map(p => p.trim()).filter(Boolean);
                if (parts.length === 0) return null;

                for (const part of parts) {
                    if (part.includes('-')) {
                        const [startStr, endStr] = part.split('-').map(s => s.trim());
                        const start = parseInt(startStr, 10), end = parseInt(endStr, 10);
                        if (isNaN(start) || isNaN(end) || start > end) return null;
                        for (let i = start; i <= end; i++) result.add(i);
                    } else {
                        const num = parseInt(part, 10);
                        if (isNaN(num)) return null;
                        result.add(num);
                    }
                }
                return result.size > 0 ? result : null;
            }
        })();

        function toggleLoadedList(contpk) {
            const chips = document.getElementById(`loadedChips-${contpk}`);
            const btn = chips.previousElementSibling;
            chips.classList.toggle('d-none');
            btn.classList.toggle('open');
        }

        const confirmModalEl = document.getElementById('modalConfirmRemove');
        const confirmModal = new bootstrap.Modal(confirmModalEl);
        const crConfirmBtn = document.getElementById('crConfirmBtn');

        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.ld-chip-remove');
            if (!btn) return;
            const chip = btn.closest('.ld-chip');
            if (!chip) return;
            const packpks = chip.dataset.packpks;
            const contpk = chip.dataset.contpk;
            const exportpk = {{ $header->exportpk }};
            const cartonNo = chip.dataset.cartonNo;
            const groupKey = chip.dataset.groupKey;
            removeLoadedCarton(packpks, contpk, exportpk, cartonNo, groupKey);
        });

        window.removeLoadedCarton = function(packpks, contpk, exportpk, cartonNo, groupKey) {
            document.getElementById('crModalTitle').textContent = 'Keluarkan Carton?';
            document.getElementById('crModalDesc').innerHTML =
                `Carton <span id="crCartonNo" class="ld-mono fw-bold">Ctn.${cartonNo}</span> akan dikeluarkan dari container.`;

            confirmModal.show();
            crConfirmBtn.onclick = function() {
                confirmModal.hide();
                doRemoveCarton(packpks, contpk, exportpk, cartonNo, groupKey);
            };
        };

        function doRemoveCarton(packpkList, contpk, exportpk, cartonNo, groupKey) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const packpks = packpkList.split(',').map(id => parseInt(id, 10));

            crConfirmBtn.disabled = true;
            const originalHtml = crConfirmBtn.innerHTML;
            crConfirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

            fetch("{{ route('containers.unload') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ packpk: packpks, contpk: contpk, exportpk: exportpk }),
                })
                .then(async res => {
                    const text = await res.text();
                    let result;
                    try { result = JSON.parse(text); }
                    catch { throw new Error('Server mengembalikan respons tidak valid (status ' + res.status + ')'); }
                    if (!res.ok) throw new Error(result.message || 'Gagal mengeluarkan carton.');

                    try {
                        removeChipsFromDOM(contpk, result.unloaded_items ?? packpks.map(pk => ({ packpk: pk })));
                    } catch (e) {
                        console.error('removeChipsFromDOM error:', e);
                    }

                    try { updateContainerImage(contpk); } catch (e) { console.error('Error update image:', e); }
                    try { updateHeaderTotals(result.totqty, result.totctn, result.totnw, result.totgw, result.totvol); } catch (e) { console.error('Error update header totals:', e); }

                    showToast(result.message || 'Carton berhasil dikeluarkan dari container.', 'success');
                })
                .catch(err => showToast(err.message, 'error'))
                .finally(() => {
                    crConfirmBtn.disabled = false;
                    crConfirmBtn.innerHTML = originalHtml;
                });
        }

        function removeChipsFromDOM(contpk, items) {
            const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
            if (!chipsWrap || !items || !items.length) return;

            const removedByCarton = {};
            items.forEach(item => {
                const groupKey = computeChipGroupKey(item);
                if (!removedByCarton[groupKey]) {
                    removedByCarton[groupKey] = { packpks: [], exportdtpks: [], smallKeys: [], totalPcs: 0, totalCbm: 0 };
                }
                const g = removedByCarton[groupKey];
                if (item.packpk !== undefined && item.packpk !== null) g.packpks.push(String(item.packpk));
                if (item.exportdtpk !== undefined && item.exportdtpk !== null) g.exportdtpks.push(String(item.exportdtpk));
                g.smallKeys.push(smallCartonKey(item));
                g.totalPcs += Number(item.pcsp) || 0;
                g.totalCbm += Number(item.t_cbm) || 0;
            });

            Object.keys(removedByCarton).forEach(groupKey => {
                const removeData = removedByCarton[groupKey];
                const chip = findChipForRemoval(chipsWrap, contpk, groupKey, removeData.packpks);
                if (!chip) return;

                const remainingPackpks = splitCsv(chip.dataset.packpks).filter(pk => !removeData.packpks.includes(pk));

                if (remainingPackpks.length === 0) {
                    (chip.closest('.ld-chip-wrap') || chip).remove();
                    return;
                }

                chip.dataset.packpks = remainingPackpks.join(',');
                chip.dataset.exportdtpks = splitCsv(chip.dataset.exportdtpks).filter(id => !removeData.exportdtpks.includes(id)).join(',');

                const qtyEl = chip.querySelector('.ld-chip-qty');
                const existingQty = parseInt(qtyEl?.textContent) || 0;
                const newQty = Math.max(0, existingQty - removeData.totalPcs);
                if (qtyEl) qtyEl.textContent = `${newQty} pcs`;

                const stored = readSmallCartons(chip).filter(sc => !removeData.smallKeys.includes(smallCartonKey(sc)));
                chip.dataset.smallCartons = JSON.stringify(stored);

                const existingCbm = parseFloat(chip.dataset.cbm) || 0;
                const remainingCbm = removeData.totalCbm > 0
                    ? existingCbm - removeData.totalCbm
                    : (existingQty > 0 ? (existingCbm / existingQty) * newQty : 0);
                chip.dataset.cbm = roundCbm(Math.max(0, remainingCbm));

                const badge = chip.querySelector('.ld-chip-bundle-badge');
                if (badge) {
                    badge.textContent = `📦 x${stored.length}`;
                    badge.title = `Gabungan ${stored.length} carton kecil`;
                }

                const wrapEl = chip.closest('.ld-chip-wrap');
                const detailsEl = wrapEl ? wrapEl.querySelector('.ld-chip-bundle-detail') : null;
                if (detailsEl) {
                    if (stored.length) renderBundleDetailContent(detailsEl, new Map(stored.map(sc => [smallCartonKey(sc), sc])));
                    else detailsEl.remove();
                }
            });

            refreshContainerStats(contpk);
        }

        function findChipForRemoval(chipsWrap, contpk, groupKey, packpks) {
            const byId = document.getElementById(`chip-carton-${groupKey}-${contpk}`);
            if (byId) return byId;
            return Array.from(chipsWrap.querySelectorAll('.ld-chip')).find(chip =>
                splitCsv(chip.dataset.packpks).some(pk => packpks.includes(pk))
            ) || null;
        }

        function readSmallCartons(chip) {
            try {
                const parsed = JSON.parse(chip.dataset.smallCartons || '[]');
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        }

        function updateContainerImage(contpk) {
            const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
            const containerCard = document.querySelector(`[data-contpk="${contpk}"]`);
            if (!chipsWrap || !containerCard) return;

            const image = containerCard.querySelector('.ld-container-image');
            if (!image) return;

            let totalCbm = 0;
            chipsWrap.querySelectorAll('.ld-chip').forEach(chip => { totalCbm += parseFloat(chip.dataset.cbm) || 0; });

            const capacityCbm = parseFloat(containerCard.dataset.capacityCbm) || 0;
            const percent = capacityCbm > 0 ? Math.min(100, (totalCbm / capacityCbm) * 100) : 0;

            let level;
            if (percent >= 100) level = 100;
            else if (percent >= 75) level = 75;
            else if (percent >= 50) level = 50;
            else if (percent >= 25) level = 25;
            else if (percent > 0) level = 5;
            else level = 0;

            image.src = "{{ asset('public/css/images/') }}/container_" + level + ".png";
        }

        function updateHeaderTotals(totqty, totctn, totnw, totgw, totvol) {
            const totQtyEl = document.getElementById('headerTotQty');
            const totCtnEl = document.getElementById('headerTotCtn');
            const totNwEl = document.getElementById('headerTotNw');
            const totGwEl = document.getElementById('headerTotGw');
            const totVolEl = document.getElementById('headerTotVol');

            if (totQtyEl) totQtyEl.textContent = totqty ?? 0;
            if (totCtnEl) totCtnEl.textContent = totctn ?? 0;
            if (totNwEl) totNwEl.textContent = totnw ?? 0;
            if (totGwEl) totGwEl.textContent = totgw ?? 0;
            if (totVolEl) totVolEl.textContent = totvol ?? 0;
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

        (function() {
            const confirmModalEl = document.getElementById('modalConfirmPublish');
            const confirmModal = new bootstrap.Modal(confirmModalEl);
            const cpConfirmBtn = document.getElementById('cpConfirmBtn');
            const cpIcon = document.getElementById('cpIcon');
            const cpTitle = document.getElementById('cpTitle');
            const cpDesc = document.getElementById('cpDesc');

            window.handlePublishToggle = function(btn) {
                const exportpk = btn.dataset.exportpk;
                const currentStatus = btn.dataset.status;
                const willPublish = currentStatus === 'draft';

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
                    cpDesc.textContent = 'Shipment plan ini akan ditarik kembali dan tidak lagi terlihat oleh tim gudang.';
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
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ exportpk, stsexp: willPublish ? 1 : 0 }),
                    })
                    .then(async res => {
                        const text = await res.text();
                        let result;
                        try { result = JSON.parse(text); }
                        catch { throw new Error('Server mengembalikan respons tidak valid (status ' + res.status + ')'); }
                        if (!res.ok) throw new Error(result.message || 'Gagal mengubah status publish.');

                        sessionStorage.setItem('ldToastMessage', result.message || 'Status berhasil diperbarui.');
                        sessionStorage.setItem('ldToastType', 'success');
                        location.reload();
                    })
                    .catch(err => {
                        showToast(err.message, 'error');
                        btn.disabled = false;
                    });
            }

            const progressModalEl = document.getElementById('modalContainerProgress');
            const progressModal = new bootstrap.Modal(progressModalEl);
            const cpGrid = document.getElementById('cpCartonGrid');
            const cpLoadingState = document.getElementById('cpLoadingState');
            const cpEmptyState = document.getElementById('cpEmptyState');
            const cpTooltip = document.getElementById('cpTooltip');

            window.openContainerProgressModal = function(contpk, contno) {
                document.getElementById('cpContno').textContent = contno;
                cpGrid.innerHTML = '';
                cpLoadingState.classList.remove('d-none');
                cpEmptyState.classList.add('d-none');
                document.getElementById('cpSummaryText').textContent = '0 carton total';

                progressModal.show();
                loadContainerProgress(contpk);
            };

            function loadContainerProgress(contpk) {
                fetch(`{{ route('containers.progress', ['contpk' => '__CONT_PK__']) }}`.replace('__CONT_PK__', contpk), { headers: { 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(data => {
                        cpLoadingState.classList.add('d-none');
                        renderCartonGrid(data.cartons || []);
                    })
                    .catch(() => {
                        cpLoadingState.classList.add('d-none');
                        cpEmptyState.classList.remove('d-none');
                        cpEmptyState.querySelector('p').textContent = 'Gagal memuat data progres.';
                    });
            }

            function renderCartonGrid(cartons) {
                cpGrid.innerHTML = '';
                if (!cartons.length) {
                    cpEmptyState.classList.remove('d-none');
                    document.getElementById('cpSummaryText').textContent = '0 carton total';
                    return;
                }
                cpEmptyState.classList.add('d-none');

                let countLoading = 0, countLoaded = 0, countShipped = 0;

                cartons.forEach(carton => {
                    const box = document.createElement('div');
                    let statusClass, icon;

                    switch (carton.status) {
                        case 'processing': statusClass = 'cp-status-loading'; icon = 'bi-hourglass-split'; countLoading++; break;
                        case 'loaded': statusClass = 'cp-status-loaded'; icon = 'bi-check-lg'; countLoaded++; break;
                        case 'shipped': statusClass = 'cp-status-shipped'; icon = 'bi-lock-fill'; countShipped++; break;
                        default: statusClass = 'cp-status-loading'; icon = 'bi-question-lg';
                    }

                    const bundleTag = carton.is_bundle
                        ? `<span class="cp-bundle-tag" title="Gabungan ${carton.bundle_cartons.length} carton kecil">📦 x${carton.bundle_cartons.length}</span>`
                        : '';

                    box.className = `cp-carton-box ${statusClass}`;
                    box.innerHTML = `
                        <i class="bi ${icon} cp-carton-icon"></i>
                        <span class="cp-carton-label">Ctn.${escapeHtml(carton.carton)} ${bundleTag}</span>
                        <span class="cp-carton-po">${escapeHtml(carton.POno || '-')}</span>
                        <span class="cp-carton-op">${escapeHtml(carton.OP || '-')}</span>
                        <span class="cp-carton-qty">${carton.pcsp ?? 0} pcs</span>
                    `;

                    box.addEventListener('mouseenter', (e) => showTooltip(e, carton));
                    box.addEventListener('mousemove', (e) => moveTooltip(e));
                    box.addEventListener('mouseleave', hideTooltip);

                    cpGrid.appendChild(box);
                });

                document.getElementById('cpSummaryText').textContent =
                    `${cartons.length} carton total · ${countLoading} proses · ${countLoaded} masuk · ${countShipped} shipped`;
            }

            function showTooltip(e, carton) {
                let bundleInfo = '';
                if (carton.is_bundle && carton.bundle_cartons.length) {
                    bundleInfo = '<br><strong>Carton kecil:</strong><br>' +
                        carton.bundle_cartons.map(sc =>
                            `Ctn.${escapeHtml(sc.carton)} (${escapeHtml(sc.POno || '-')}/${escapeHtml(sc.OP || '-')}) — ${sc.pcsp} pcs`
                        ).join('<br>');
                }

                cpTooltip.innerHTML = `
                    <strong>Ctn. ${escapeHtml(carton.carton)}</strong><br>
                    PO: ${escapeHtml(carton.POno || '-')}<br>
                    OP: ${escapeHtml(carton.OP || '-')}<br>
                    Qty: ${carton.pcsp || 0} pcs
                    ${bundleInfo}
                `;
                cpTooltip.classList.remove('d-none');
                cpTooltip.classList.add('show');
                moveTooltip(e);
            }

            function moveTooltip(e) {
                cpTooltip.style.left = (e.clientX + 14) + 'px';
                cpTooltip.style.top = (e.clientY + 14) + 'px';
            }

            function hideTooltip() {
                cpTooltip.classList.add('d-none');
                cpTooltip.classList.remove('show');
            }

            function escapeHtml(str) {
                return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }
        })();

        $(document).on('click', '.btn-remove-all-cartons', function() {
            const btn = $(this);
            if (btn.prop('disabled')) return;
            const contpk = btn.data('contpk');
            const exportpk = @json($header->exportpk);
            const allChips = $(`#loadedChips-${contpk} .ld-chip`);

            const chips = allChips.filter(function() { return $(this).data('shipped') != 1; });
            const shippedCount = allChips.length - chips.length;

            if (!chips.length) {
                showToast(shippedCount > 0
                    ? 'Semua carton di container ini sudah Shipped, tidak bisa dikeluarkan.'
                    : 'Tidak ada carton yang dimuat.', 'error');
                return;
            }

            let packpks = [];
            chips.each(function() {
                const value = $(this).data('packpks');
                if (value) packpks.push(String(value).split(',').map(x => x.trim()).filter(x => x));
            });
            packpks = [...new Set(packpks.flat())];

            if (!packpks.length) {
                showToast('Data carton tidak ditemukan.', 'error');
                return;
            }

            document.getElementById('crModalTitle').textContent = 'Keluarkan Semua Carton?';
            document.getElementById('crModalDesc').innerHTML = shippedCount > 0
                ? `<strong>${chips.length}</strong> carton akan dikeluarkan. <span class="text-muted">(${shippedCount} carton sudah Shipped, dilewati.)</span>`
                : `Semua <strong>${chips.length}</strong> carton di container ini akan dikeluarkan.`;

            confirmModal.show();

            crConfirmBtn.onclick = function() {
                confirmModal.hide();
                doUnloadAllCartons(btn, contpk, exportpk, packpks, chips);
            };
        });

        function doUnloadAllCartons(btn, contpk, exportpk, packpks, chips) {
            $.ajax({
                url: "{{ route('containers.unload_all') }}",
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', contpk: contpk, exportpk: exportpk, packpk: packpks },
                beforeSend: function() {
                    btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');
                },
                success: function(res) {
                    if (res.success) {
                        showToast(res.message || 'Carton berhasil dikeluarkan.', 'success');
                        location.reload();
                    } else {
                        showToast(res.message || 'Gagal mengeluarkan carton.', 'error');
                    }
                    updateRemoveAllButtonState(contpk);
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Terjadi kesalahan saat mengeluarkan carton.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-trash3"></i>');
                }
            });
        }

        function updateRemoveAllButtonState(contpk) {
            const chipsWrap = document.getElementById(`loadedChips-${contpk}`);
            const removeAllBtn = document.querySelector(`.btn-remove-all-cartons[data-contpk="${contpk}"]`);
            if (!chipsWrap || !removeAllBtn) return;

            const chipCount = chipsWrap.querySelectorAll('.ld-chip').length;
            removeAllBtn.disabled = chipCount === 0;

            const existingTooltip = bootstrap.Tooltip.getInstance(removeAllBtn);
            if (existingTooltip) existingTooltip.dispose();
            new bootstrap.Tooltip(removeAllBtn);
        }

        /*ADD FOTO*/
        function OpenCamera() {
            let input = document.getElementById('image_camera');
            input.value = '';
            input.click();
        }

        const cameraInput = document.getElementById('image_camera');
        const btnCamera = document.getElementById('btn_camera');
        const nameCamera = document.getElementById('file_name_camera');
        const closeCamera = document.getElementById('close_camera');

        cameraInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                let fileName = this.files[0].name.trim();
                $('#foto').val(fileName);
                $('#foto2').val(fileName);
                if (fileName.length > 20) fileName = fileName.substring(0, 20) + '...';
                $('#file_name_camera').text(fileName);
                const hasFoto = fileName.length > 0;
                if (hasFoto) $('#close_camera').removeAttr('hidden');
                else $('#close_camera').attr('hidden', true);
                closeCamera.removeAttribute("hidden");
            } else {
                resetAll();
            }
        });

        function resetAll() {
            $('#foto').val('');
            btnCamera.disabled = false;
            nameCamera.textContent = '';
            cameraInput.value = '';
            closeCamera.setAttribute("hidden", true);
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
                            <div class="shipment-header">
                                <button type="button" class="btn-back" onclick="BackToList();">
                                    <i class="bi bi-arrow-left"></i>
                                </button>
                                <h4 class="shipment-title flex-fill">Informasi Detail Shipment</h4>
                            </div>

                            @php
                                // GABUNGAN -- lock mode "kepemilikan user" dari Production (lebih baru
                                // dari sekadar cek lockship). Kalau user yang membuka BUKAN pembuat
                                // shipment plan ini (userpk beda dari session userpk), semua field/tombol
                                // Update & Muat Carton dikunci.
                                $guserpk = $header->guserpk;
                                $s_guserpk= Session::get('guserpk');
                                $isPublished = !empty($header->stsexp) && (int) $header->stsexp === 1;
                                $isLocked = $guserpk != $s_guserpk ? 1 : null;
                            @endphp
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
                                        <div class="field-label" style="width: 90px;">Buyer Ex-Factory</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->GAC ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">Estimasi Container Arrival</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->contdate ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-diagram-3"></i></span>
                                        <div class="field-label" style="width: 90px;">Vassal</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->typenm ?? '-' }}</div>
                                    </div>
                                </div>

                                <!-- ===================== KOLOM 2 ===================== -->
                                <div class="col-md-4 d-flex flex-column">
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-boxes"></i></span>
                                        <div class="field-label" style="width: 90px;">Total Qty (PCS)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label" id="headerTotQty">{{ $total['totqty'] ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-box-seam"></i></span>
                                        <div class="field-label" style="width: 90px;">Total CTN</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label" id="headerTotCtn">{{ $total['totctn'] ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-speedometer"></i></span>
                                        <div class="field-label" style="width: 90px;">Total GW (Kg)</div>
                                        <div class="me-2">:</div>
                                        {{-- GABUNGAN -- FIX UTAMA: lengkapi migrasi $total[] yang di
                                            Production baru separuh jalan (totqty/totctn/totvol sudah pakai
                                            $total[], totgw/totnw masih $header-> lama/statis). Sekarang
                                            SEMUA lima nilai konsisten pakai $total[] (live dari
                                            computeContainerTotals() via API Packing). --}}
                                        <div class="fw-medium field-label" id="headerTotGw">{{ $total['totgw'] ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-speedometer"></i></span>
                                        <div class="field-label" style="width: 90px;">Total NW (Kg)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label" id="headerTotNw">{{ $total['totnw'] ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-purple me-2"><i class="bi bi-rulers"></i></span>
                                        <div class="field-label" style="width: 90px;">Measurement CTN</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->ukctn ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-red me-2"><i class="bi bi-bounding-box-circles"></i></span>
                                        <div class="field-label" style="width: 90px;">Total Volume (CBM)</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label" id="headerTotVol">{{ $total['totvol'] ?? '-' }}</div>
                                    </div>
                                </div>

                                <!-- ===================== KOLOM 3 ===================== -->
                                <div class="col-md-4 d-flex flex-column">
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-signpost-fill"></i></span>
                                        <div class="field-label" style="width: 90px;">Forwarder</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $container->first()->fwdnm ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-geo-alt"></i></span>
                                        <div class="field-label" style="width: 90px;">Port of Loading</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->plbnm ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">Ex-Factory Deadline</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->exdate ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-blue me-2"><i class="bi bi-clock-history"></i></span>
                                        <div class="field-label" style="width: 90px;">Forwarder Close Time</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->closetime ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-orange me-2"><i class="bi bi-calendar-event"></i></span>
                                        <div class="field-label" style="width: 90px;">ETD Vessel</div>
                                        <div class="me-2">:</div>
                                        <div class="fw-medium field-label">{{ $header->etdvess ?? '-' }}</div>
                                    </div>
                                    <div class="field-row d-flex align-items-center">
                                        <span class="field-chip chip-green me-2"><i class="bi bi-calendar-check"></i></span>
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
                                @php
                                    if ($c->loading_status == 'shipped') {
                                        $status = 'Shipped';
                                    } elseif ($c->loading_status == 'start') {
                                        $status = 'Start Loading';
                                    } elseif ($c->loading_status == 'finish') {
                                        $status = 'Finish Loading';
                                    } else {
                                        $status = 'Planning';
                                    }
                                    $guserpk = $header->guserpk;
                                    $s_guserpk= Session::get('guserpk');
                                    // GABUNGAN -- kunci per-container: shipped ATAU bukan pemilik.
                                    $islockCont = $c->loading_status === 'shipped' || $guserpk != $s_guserpk ? 1 : 0;
                                @endphp
                                <div class="col-md-6 col-lg-4">
                                    <div class="ld-card" data-contpk="{{ $c->contpk }}"
                                        data-capacity-cbm="{{ $c->volume ?? 0 }}">
                                        <div class="ld-card-top">
                                            <span class="ld-badge">{{ strtoupper($c->type ?? '40HC') }}</span>
                                            <span class="ld-badge volume">{{ strtoupper($c->volume ? 'Vol. Container: ' . $c->volume . ' Cbm' : '') }}</span>
                                            <span class="ld-status ld-status-{{ $c->loading_status ?? 'loading' }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </div>

                                        @php
                                            $loadedForContainer = $loadedCartons[$c->contpk] ?? collect();

                                            $groupedByCarton = $loadedForContainer->keyBy(function ($item) {
                                                $raw = !empty($item['bundlepk'])
                                                    ? 'bundle_' . $item['bundlepk']
                                                    : ($item['carton'] ?? '') . '|' . ($item['POno'] ?? '') . '|' . ($item['OP'] ?? '');
                                                return preg_replace('/[^A-Za-z0-9]/', '_', $raw);
                                            });

                                            $totalCbmAll = $groupedByCarton->sum(function ($item) {
                                                return $item['t_cbm'] ?? 0;
                                            });
                                            $capacityCbm = $c->volume ?? 0;
                                            $cbmPercent = $capacityCbm > 0 ? min(100, ($totalCbmAll / $capacityCbm) * 100) : 0;
                                            if ($cbmPercent >= 100) {
                                                $imageLevel = 100;
                                            } elseif ($cbmPercent >= 75) {
                                                $imageLevel = 75;
                                            } elseif ($cbmPercent >= 50) {
                                                $imageLevel = 50;
                                            } elseif ($cbmPercent >= 25) {
                                                $imageLevel = 25;
                                            } elseif ($cbmPercent > 0) {
                                                $imageLevel = 5;
                                            } else {
                                                $imageLevel = 0;
                                            }
                                            $image = "container_{$imageLevel}.png";
                                        @endphp

                                        <button type="button"
                                            class="ld-container-visual {{ $islockCont ? 'ld-container-locked' : '' }}"
                                            @if (!$islockCont) onclick="openLoadingModal(
                                                    {{ $c->contpk }},
                                                    '{{ $c->contno ?? '' }}',
                                                    {{ $header->exportpk }},
                                                    {{ $header->ordpk }},
                                                    '{{ $header->POno ?? '' }}',
                                                    '{{ $header->ordno ?? '' }}'
                                                )"
                                            @else
                                                disabled @endif>
                                            <img src="{!! asset('public/css/images/' . $image) !!}" class="ld-container-image" alt="Container">
                                            <span class="ld-visual-label">
                                                <i class="bi bi-hand-index-thumb"></i> Klik untuk muat carton
                                            </span>
                                        </button>

                                        <div class="ld-card-body">
                                            <div class="ld-mono ld-container-no">No. Container: {{ $c->contno }}</div>
                                            <div class="ld-meta">Vessel Name: <b>{{ $c->vessname ?? 'Vessel belum diisi' }}</b>
                                            <div class="ld-meta">No. Truk: <b>{{ $c->nopol ?? '-' }}</b></div>
                                            <div class="ld-meta">
                                                Act Arrival Date:
                                                <b>{{ $c->actcontdate ? \Carbon\Carbon::parse($c->actcontdate)->format('d/m/Y H:i:s') : '-' }}</b>
                                            </div>
                                            <div class="ld-meta">No. Seal: <b>{{ $c->noseal ?? '-' }}</b></div>
                                            </div>

                                            <div class="ld-loaded-section">
                                                <div class="ld-loaded-header-row">
                                                    <button type="button" class="ld-loaded-toggle" onclick="toggleLoadedList({{ $c->contpk }})">
                                                        <i class="bi bi-box-seam"></i>
                                                        <span>{{ $groupedByCarton->count() }} carton dimuat</span>
                                                        <i class="bi bi-chevron-down ld-loaded-chevron" id="chevron-{{ $c->contpk }}"></i>
                                                    </button>
                                                    <button type="button"
                                                        class="ld-remove-all-btn btn-remove-all-cartons {{ $islockCont ? 'd-none' : '' }}"
                                                        data-contpk="{{ $c->contpk }}" data-bs-toggle="tooltip"
                                                        data-bs-placement="top" title="Keluarkan semua carton"
                                                        {{ $groupedByCarton->count() === 0 ? 'disabled' : '' }}>
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>

                                                <div class="ld-loaded-chips d-none" id="loadedChips-{{ $c->contpk }}">
                                                    @forelse ($groupedByCarton as $groupKey => $item)
                                                        @php
                                                            $totalPcs = $item['pcsp'] ?? 0;
                                                            $packpksForChip = collect($item['packpks'] ?? [])->implode(',');
                                                            $cartonNo = $item['carton'] ?? '-';
                                                            $safeGroupKey = preg_replace('/[^A-Za-z0-9]/', '_', $groupKey);
                                                            $poOpLabel = trim(($item['POno'] ?? '') . ' / ' . ($item['OP'] ?? ''), ' /');
                                                            $shipStatus = $item['ship_status'] ?? '';
                                                            $isloading = !empty($item['shipped']);
                                                            $totalCbmPerCarton = $item['t_cbm'] ?? 0;
                                                            $isBundleChip = !empty($item['is_bundle']);
                                                            $smallCartons = $item['small_cartons'] ?? [];
                                                            $exportdtpksForChip = collect($item['exportdtpks'] ?? [])->implode(',');
                                                            $smallCartonsData = collect($smallCartons)
                                                                ->map(fn($sc) => [
                                                                    'carton' => $sc['carton'] ?? '',
                                                                    'POno' => $sc['POno'] ?? '',
                                                                    'OP' => $sc['OP'] ?? '',
                                                                    'pcsp' => (int) ($sc['pcsp'] ?? 0),
                                                                ])
                                                                ->values()
                                                                ->all();
                                                        @endphp
                                                        <div class="ld-chip-wrap" style="display:inline-flex;flex-direction:column;align-items:stretch;vertical-align:top;">
                                                            <span
                                                                class="ld-chip  {{ $shipStatus == 'loading' ? 'ld-chip-loading' : ($shipStatus == 'shipped' ? 'ld-chip-shipped' : '') }}"
                                                                id="chip-carton-{{ $safeGroupKey }}-{{ $c->contpk }}"
                                                                data-packpks="{{ $packpksForChip }}"
                                                                data-exportdtpks="{{ $exportdtpksForChip }}"
                                                                data-contpk="{{ $c->contpk }}"
                                                                data-exportpk="{{ $header->exportpk }}"
                                                                data-carton-no="{{ $cartonNo }}"
                                                                data-group-key="{{ $safeGroupKey }}"
                                                                data-is-bundle="{{ $isBundleChip ? 1 : 0 }}"
                                                                data-bundlepk="{{ $item['bundlepk'] ?? '' }}"
                                                                data-small-cartons="{{ json_encode($smallCartonsData) }}"
                                                                data-shipped="{{ $shipStatus == 'loading' || $shipStatus == 'shipped' ? 1 : 0 }}"
                                                                data-cbm="{{ $totalCbmPerCarton }}"
                                                                title="{{ $shipStatus == 'loading' || $shipStatus == 'shipped' ? 'Carton sudah loading ke container, tidak dapat dikeluarkan' : ($poOpLabel ?: 'PO/OP tidak diketahui') }}">
                                                                <span class="ld-chip-no">Ctn.{{ $cartonNo }}</span>
                                                                @if ($isBundleChip)
                                                                    <span class="ld-chip-bundle-badge" style="font-size:.65rem;opacity:.75;"
                                                                        title="Gabungan {{ count($smallCartonsData) }} carton kecil">
                                                                        📦 x{{ count($smallCartonsData) }}
                                                                    </span>
                                                                @endif
                                                                @if ($poOpLabel)
                                                                    <span class="ld-chip-poop" style="font-size:.68rem;opacity:.7;">{{ $poOpLabel }}</span>
                                                                @endif
                                                                <span class="ld-chip-qty">{{ $totalPcs }} pcs</span>
                                                                @if ($shipStatus == 'loading' || $shipStatus == 'shipped')
                                                                    <i class="bi bi-lock-fill" style="font-size:.7rem;opacity:.6;" title="Sudah Shipped"></i>
                                                                @else
                                                                    <button type="button" class="ld-chip-remove {{ $islockCont ? 'd-none' : '' }}" title="Keluarkan carton ini">
                                                                        <i class="bi bi-x"></i>
                                                                    </button>
                                                                @endif
                                                            </span>

                                                            @if ($isBundleChip && !empty($smallCartonsData))
                                                                <details class="ld-chip-bundle-detail"
                                                                    style="margin-top:.25rem;font-size:.7rem;background:#fff;border:1px solid rgba(0,0,0,.08);border-radius:8px;padding:.35rem .5rem;box-shadow:0 1px 3px rgba(0,0,0,.06);">
                                                                    <summary style="cursor:pointer;opacity:.75;list-style:none;">
                                                                        Detail {{ count($smallCartonsData) }} carton kecil
                                                                    </summary>
                                                                    <div class="ld-chip-bundle-list" style="margin-top:.3rem;display:flex;flex-direction:column;gap:.15rem;">
                                                                        @foreach ($smallCartonsData as $sc)
                                                                            @php
                                                                                $scPoOp = trim(($sc['POno'] ?? '') . ' / ' . ($sc['OP'] ?? ''), ' /');
                                                                            @endphp
                                                                            <div class="ld-chip-bundle-item" style="opacity:.85;white-space:nowrap;">
                                                                                Ctn.{{ $sc['carton'] }}
                                                                                @if ($scPoOp)
                                                                                    <span style="opacity:.7;">({{ $scPoOp }})</span>
                                                                                @endif
                                                                                &mdash; {{ $sc['pcsp'] }} pcs
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </details>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="ld-chip-empty">Belum ada carton yang dimuat.</div>
                                                    @endforelse
                                                </div>
                                            </div>
                                            <div class="ld-progress-row">
                                                <span>Carton dimuat</span>
                                                <span class="ld-loaded-count" id="loadedCount-{{ $c->contpk }}">
                                                    {{ $groupedByCarton->count() }} ctn ({{ number_format($totalCbmAll, 3, '.', '') }} cbm)
                                                </span>
                                            </div>
                                            <div class="ld-progress-track">
                                                <div class="ld-progress-fill" id="loadedBar-{{ $c->contpk }}"
                                                    data-target="{{ $c->volume ?? 1 }}"
                                                    style="width: {{ min(100, ($totalCbmAll / max($c->volume ?? 1, 1)) * 100) }}%">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="button"
                                            class="ld-btn-action {{ $islockCont ? 'ld-btn-locked' : '' }}"
                                            @if (!$islockCont) onclick="openLoadingModal(
                                                    {{ $c->contpk }},
                                                    '{{ $c->contno ?? '' }}',
                                                    {{ $header->exportpk }},
                                                    {{ $header->ordpk }},
                                                    '{{ $header->POno ?? '' }}',
                                                    '{{ $header->ordno ?? '' }}'
                                                )"
                                            @else
                                                disabled @endif>
                                            <i class="bi {{ $islockCont ? 'bi-lock-fill' : 'bi-box-arrow-in-down' }}"></i>
                                            {{ $islockCont ? 'Locked' : 'Muat Carton' }}
                                        </button>

                                        <button type="button" class="ld-view-progress-btn"
                                            onclick="openContainerProgressModal({{ $c->contpk }}, '{{ $c->contno }}')">
                                            <i class="bi bi-grid-3x3-gap-fill"></i> Lihat Progres Muat
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

            </div>
        </div>
    </div>

    @include('menu.exim-export.shipplan.modal.modalContainer')
    @include('menu.exim-export.shipplan.modal.modalLoading')
@endsection