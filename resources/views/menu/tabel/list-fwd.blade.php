@extends('layout.main')

@section('css_custom')
<style>

    .circle {
        width: 40px;
        height: 40px;
        background-color: red;
        color: white;
        font-size: 20px;
        font-weight: bold;
        text-align: center;
        line-height: 40px;
        border-radius: 50%;
        display: inline-block;
    }

    .btn-group-v1 {
        height: 34px;
        width: 140px;
    }

    .border-1 {
        border: 1px solid red;
    }

    .border-list-fltr {
        border-radius: 8px;
        border: 1px solid var(--Grey-light-3, #E0E0E0);
        background: #FFF;
    }

    .field-text {
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;
    }

    .text-qty-orders {
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 18px;
    }

    .text-filter {
        color: #000;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 18px;
    }

    .datagrid-cell {
        /* font-family: 'Arial'; */
        font-style: normal;
        font-weight: 400;
        font-size: 13px;
        line-height: 18px;
        white-space: normal;
    }

    .datagrid-row {
        height: auto !important;
        white-space: normal !important;
    }

    .lines-no3 .datagrid-body td {
        border-right: 1px dotted transparent;
        border-bottom: 1px dotted transparent;
    }

    .dropdown-menu {
        max-height: 200px;
        overflow-y: auto;
    }

    .imgsort {
        position: relative;
        width: 12px !important;
        height: 11.26px !important;
        left: 0px !important;
        top: 0px !important;
    }

    .dropdown-toggle-custom-sortlist {
        border-style: none !important;
    }

    .dropdown-toggle-custom-sortlist::after {
        display: none;
        border-style: none !important;
    }

    .dropdown-item:active {
        background-color: transparent;
        color: inherit;
        outline: none;
        box-shadow: none;
    }

    .w-filter {
        width: 165px !important;
        height: 34px !important;
    }

    .text-select-filter {
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 18px;
        width: 100px;
    }

    .text-select-filter2 {
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 18px;
        color: #359DD9;
        cursor: pointer;
    }

    .text-link {
        color: var(--Blue, #359DD9);
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 18px;
    }

    .text-title-modal {
        font-size: 16px;
        font-style: normal;
        font-weight: 700;
        line-height: 22px;
    }

    .text-subtitle-modal {
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 22px;
    }

    .multi-filter {
        width: 1000px;
    }

    .combobox-item {
        border-bottom: 1px solid #AFAFAF;
        padding: 8px;
        margin-bottom: 0px;
        /* opacity: 50% !important; */
    }

    .bi::before,
    [class^="bi-"]::before,
    [class*=" bi-"]::before {
        display: inline-block;
        font-family: bootstrap-icons !important;
        font-style: normal;
        font-weight: normal !important;
        font-variant: normal;
        text-transform: none;
        line-height: 1;
        vertical-align: -.125em;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .bi-eye-fill::before {
        content: url("{{ asset('public/css/images/caret-up-fill.svg') }}");
    }

    .bi-eye-slash-fill::before {
        content: url("{{ asset('public/css/images/caret-down-fill.svg') }}");
    }

    .bi-eye2::before {
        content: url("{{ asset('public/css/images/caret-up-fill.svg') }}");
    }

    .lines-no .datagrid-body td {
        border-right: 1px dotted transparent;
        border-bottom: 1px dotted transparent;
    }

    .lines-no2 .datagrid-header {
        border-right: 1px dotted transparent;
        border-bottom: 1px dotted transparent;
        background: #fff;
    }

    .col .datagrid-row-over,
    .col .datagrid-header td.datagrid-header-over {
        background: #fff;
        color: #000000;
        cursor: default;
    }

    .col .datagrid-row-selected {
        background: #fff;
        color: #000000;
    }
</style>
@endsection

@section('js_custom')
<script>
    $(function() {
        var dgfwd = $('#dg-fwd').datagrid();
        dgfwd.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
        dgfwd.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
        dgfwd.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
        dgfwd.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        dgfwd.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
        dgfwd.datagrid('getPanel').css('border', 'none');
        dgfwd.datagrid('getPanel').addClass('lines-no');
        dgfwd.datagrid('getPanel').addClass('lines-no3');
    })

    function styler1(index, row) {
        return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-left:1px solid #ededed;border-top-left-radius:5px;' +
            'border-bottom-left-radius:5px;height:35px;';
    }

    function styler2(index, row) {
        return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;';
    }

    function styler3(index, row) {
        return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-right:1px solid #ededed;border-top-right-radius:5px;' +
            'border-bottom-right-radius:5px;';
    }

    function loadDataByFilter() {
        console.log(`loadDataByFilter RUNNING >>>>>>>>>>>>>>`);
        var searchByInput = $('#searchByInput').val();
        var sortlistByName = $('#sortlistByName').val();

        console.log(`searchByInput : ${searchByInput}`);

        $('#dg-fwd').datagrid('load', {
            searchByInput: searchByInput,
            sortlistByName: sortlistByName,
        })
        console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
    }

    //FILTER SORT
    function sortlistByName(value) {
        document.getElementById('sortlistByName').value = value;
        if (value == 11) {
            $('#FwdnmAsc').removeClass('d-none');
            $('#FwdnmDesc').addClass('d-none');
        } else if (value == 12) {
            $('#FwdnmAsc').addClass('d-none');
            $('#FwdnmDesc').removeClass('d-none');
        } else {
            $('#FwdnmAsc').removeClass('d-none');
            $('#FwdnmDesc').addClass('d-none');
        }
        loadDataByFilter();
    }
</script>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="container-fluid p-4">
        <div class="d-flex flex-column">
            <div class="p-2">
                <span class="txt-000000-700-24-28">List Forwarder</span>
            </div>

            <div class="d-flex flex-wrap p-0 pb-2 gap-2 align-items-center">

                <div class="p-0">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                        </span>
                        <input type="text" class="form-control search" id="searchByInput" onkeyup="loadDataByFilter()" placeholder="Search...">
                    </div>
                </div>


                @if(Session::get('guserpk') == '7')
                <button onclick="AddForwarder('create')" class="btn btn-dark btn-sm">&#10010; Create Forwarder</button>
                @endif

                <script>
                    function formatAttribute(value, row, index) {
                        return `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditForwarder('${row.fwdpk}')">Edit</a>`;
                    }

                    function AddForwarder() {
                        console.log(`onAddForwarder is running...`);
                        $('#ModalCreateOrEditForwarder').modal('show');
                        $('.fwd-create').removeClass('d-none');
                        $('.fwd-edit').addClass('d-none');
                    }

                    function onEditForwarder(fwdpk) {
                        console.log(`onEditForwarder is running...`);

                        $('#ModalCreateOrEditForwarder').modal('show');

                        $('.fwd-edit').removeClass('d-none');
                        $('.fwd-create').addClass('d-none');

                        let url = "{{ url('api/fwd/detail/:fwdpk') }}";
                        url = url.replace(':fwdpk', fwdpk);

                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(response) {
                                console.log(`success onEditForwarder here >>>>>`);
                                console.log(response);

                                $('#fwdpk').val(response.data.fwdpk);
                                $('#fwdid').val(response.data.fwdid);
                                $('#fwdnm').val(response.data.fwdnm);
                                $('#kontak').val(response.data.kontak);
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onEditForwarder here >>>>>`);
                                console.log(xhr);
                            }
                        })
                    }

                    function onCancelForwarder() {
                        resetForwarder();
                        $('#ModalCreateOrEditForwarder').modal('hide');
                        $('#formCreateOrEditForwarder').form('reset');
                    }

                    function resetForwarder() {
                        $('#fwdpk').val(null);
                        $('.fwd-edit').addClass('d-none');
                        $('.fwd-create').addClass('d-none');
                        $('#ermsg-fwdid').text(null);
                        $('#ermsg-fwdnm').text(null);
                        $('#ermsg-kontak').text(null);
                        $('#loading-edit-fwd').addClass('d-none');
                        $('#loading-create-fwd').addClass('d-none');
                    }

                    function validateListForwarder() {
                        console.log(`validateListForwarder is running...`);

                        var fwdid = $('#fwdid').val();
                        if (!fwdid) $('#ermsg-fwdid').text('Kode Gudang wajib diisi');

                        var fwdnm = $('#fwdnm').val();
                        if (!fwdnm) $('#ermsg-fwdnm').text('Nama Gudang wajib diisi')

                        setTimeout(() => {
                            $('#ermsg-fwdid').text(null);
                            $('#ermsg-fwdnm').text(null);
                        }, 5000);

                        if ((!fwdid || !fwdnm)) return false;

                        console.log(`validateListForwarder is valid`);

                        return true;
                    }

                    function onSubmitForwarder() {
                        console.log(`onSubmitForwarder is running...`);

                        if (!validateListForwarder()) return;

                        $('#loading-create-fwd').removeClass('d-none');

                        let url = "{{ url('api/fwd/store') }}";
                        console.log(`url: ${url}`);

                        let formdata = new FormData();
                        formdata.append('fwdid', $('#fwdid').val());
                        formdata.append('fwdnm', $('#fwdnm').val());
                        formdata.append('kontak', $('#kontak').val());

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: formdata,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                console.log(`success onSubmitForwarder here >>>>>`);
                                console.log(response);

                                onCancelForwarder();
                                $('#dg-fwd').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onSubmitForwarder here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }

                    function onUpdateForwarder() {
                        console.log(`onUpdateForwarder is running...`);

                        var fwdpk = $('#fwdpk').val();
                        if (!fwdpk) return;

                        if (!validateListForwarder()) return;

                        $('#loading-edit-fwd').removeClass('d-none');

                        let url = "{{ url('api/fwd/update/:fwdpk') }}";
                        url = url.replace(':fwdpk', fwdpk);
                        console.log(`url: ${url}`);

                        let formdata = new FormData();

                        formdata.append('fwdid', $('#fwdid').val());
                        formdata.append('fwdnm', $('#fwdnm').val());
                        formdata.append('kontak', $('#kontak').val());
                        formdata.append('fwdpk', fwdpk);

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: formdata,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                console.log(`success onUpdateForwarder here >>>>>`);
                                console.log(response);

                                onCancelForwarder();
                                $('#dg-fwd').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onUpdateForwarder here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }
                </script>

                <div class="modal fade" id="ModalCreateOrEditForwarder" data-bs-backdrop="static" aria-labelledby="ModalCreateOrEditForwarderLabel" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h1 class="txt-000000-700-18-24-100" id="ModalCreateOrEditForwarderLabel"><span class="fwd-create d-none">Create New</span><span class="gudang-edit d-none">Edit</span> Forwarder</h1>
                            </div>
                            <div class="modal-body">
                                <form id="formCreateOrEditForwarder" class="d-flex flex-column gap-2">
                                    <input type="text" id="fwdpk" hidden>
                                    <div class="mb-3">
                                        <label for="fwdid" class="col-form-label">Kode Forwarder:</label>
                                        <input type="text" class="form-control" id="fwdid">
                                        <span id="ermsg-fwdid" class="text-danger text-blink"></span>
                                    </div>

                                    <div class="mb-3">
                                        <label for="fwdnm" class="col-form-label">Nama Forwarder:</label>
                                        <input type="text" class="form-control" id="fwdnm">
                                        <span id="ermsg-fwdnm" class="text-danger text-blink"></span>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kontak" class="col-form-label">Kontak:</label>
                                        <input type="text" class="form-control" id="kontak">
                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" onclick="onCancelForwarder()">Cancel</button>
                                <button type="button" class="btn btn-dark fwd-create d-none" onclick="onSubmitForwarder()">Add
                                    <span id="loading-create-fwd" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
                                </button>

                                <button type="button" class="btn btn-dark fwd-edit d-none" onclick="onUpdateForwarder()">Update
                                    <span id="loading-edit-fwd" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ms-auto">
                    <div id="sortlist-grup" class="p-1 pb-0 responsive">
                        <input type="hidden" id="sortlistByName" name="sortlistByName" size="5">
                        <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
                            <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
                            <span id="FwdnmAsc">Nama Forwarder (asc)</span>
                            <span id="FwdnmDesc" class="d-none">Nama Forwarder (desc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByName(11)">Nama Forwarder (ascending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByName(12)">Nama Forwarder (descending)</a></li>
                        </ul>
                    </div>
                </div>

            </div>


            <div class="p-0">
                <table id="dg-fwd" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="fwdpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-fwd2')}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="fwdid" styler="styler2">Kode</th>
                            <th field="fwdnm" styler="styler2">Nama Forwarder</th>
                            <th field="kontak" styler="styler2">Kontak</th>
                            <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection