{{-- <link href="{{ url('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css')}}" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous"> --}}
<link rel="stylesheet" type="text/css" href="{{ asset('public/datepicker/daterangepicker.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('public/bootstrap-5.3.2-dist/css/bootstrap.min.css') }}">
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('public/css/styleku.css') }}">
<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('public/css/style.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('public/media/jquery-easyui-1.9.15/themes/gray/easyui.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('public/media/jquery-easyui-1.9.15/themes/icon.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('public/media/jquery-easyui-1.9.15/themes/color.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    .dropdown-toggle-custom {
        border-style: none !important;
    }

    input::-ms-reveal,
    input::-ms-clear {
        display: none;
    }

    .toast.text-bg-success {
        background-color: #19B715 !important;
        /* width: fit-content !important; */
    }

    .dropdown-w-hover:hover>.dropdown-menu {
        display: block;
    }

    .dropdown-w-hover>.dropdown-toggle:active {
        /*Without this, clicking will make it sticky*/
        pointer-events: none;
    }

    .dropdown-menu-custom-global {
        box-shadow: 0px 0px 16px -4px rgba(0, 0, 0, 0.12);
        border-style: none;
        border-radius: 8px;
    }

    .dropdown-toggle-custom-sortlist {
        border-style: none !important;
    }

    .dropdown-toggle-custom-sortlist::after {
        display: none;
        border-style: none !important;
    }

    .dropdown-item-custom-global:active {
        background-color: transparent;
    }

    .dropdown-item-custom-global.dropdown-item-custom-global-start:active {
        border-radius: 8px 8px 0 0;
    }

    .dropdown-item-custom-global.dropdown-item-custom-global-end:active {
        border-radius: 0 0 8px 8px;
    }

    .dropdown-item-custom-global.dropdown-item-custom-global-start:hover {
        border-radius: 8px 8px 0 0;
    }

    .dropdown-item-custom-global.dropdown-item-custom-global-end:hover {
        border-radius: 0 0 8px 8px;
    }

    .title-password {
        font-family: 'Arial';
        font-style: normal;
        font-weight: 700;
        font-size: 18px;
        line-height: 24px;
    }

    .label-password {
        font-family: 'Arial';
        font-style: normal;
        font-weight: 700;
        font-size: 14px;
    }

    img#polygon {
        height: 12px;
        width: 12px;
        filter: brightness(0%);
    }

    img#logo-brand {
        height: 24px;
        width: 24px;
    }

    textarea,
    input,
    select {
        border-color: black !important;
    }

    textarea:focus,
    input:focus,
    select:focus {
        border-color: black !important;
        outline: none !important;
        box-shadow: none !important;
    }

    input.w-validation:invalid,
    select.w-validation:invalid,
    textarea.w-validation:invalid {
        background-color: #ffdddd;
        border-style: none;
        outline: 1px solid red;
    }

    input.w-validation:invalid:focus,
    select.w-validation:invalid:focus,
    textarea.w-validation:invalid:focus {
        background-color: #ffdddd;
        border-style: solid;
    }

    input.w-validation:invalid:active,
    select.w-validation:invalid:active,
    textarea.w-validation:invalid:active {
        background-color: #ffdddd;
        border-style: solid;
    }

    select.form-select.form-select-black {
        border-color: #000000;
        background-image: url("public/css/images/polygon.png");
        background-size: 12px;
    }

    @media screen and (max-width: 430px) {
        .nav-title {
            font-size: 14px;
        }

        .dropdown-toggle-custom {
            font-size: 12px;
        }
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
</style>
