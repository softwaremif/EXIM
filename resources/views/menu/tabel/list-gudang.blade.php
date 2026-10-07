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
        var dggudang = $('#dg-gudang').datagrid();
        dggudang.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
        dggudang.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
        dggudang.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
        dggudang.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        dggudang.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
        dggudang.datagrid('getPanel').css('border', 'none');
        dggudang.datagrid('getPanel').addClass('lines-no');
        dggudang.datagrid('getPanel').addClass('lines-no3');
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

        $('#dg-gudang').datagrid('load', {
            searchByInput: searchByInput,
            sortlistByName: sortlistByName,
        })
        console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
    }

    //FILTER SORT
    function sortlistByName(value) {
        document.getElementById('sortlistByName').value = value;
        if (value == 11) {
            $('#GudnmAsc').removeClass('d-none');
            $('#GudnmDesc').addClass('d-none');
        } else if (value == 12) {
            $('#GudnmAsc').addClass('d-none');
            $('#GudnmDesc').removeClass('d-none');
        } else {
            $('#GudnmAsc').removeClass('d-none');
            $('#GudnmDesc').addClass('d-none');
        }
        loadDataByFilter();
    }
</script>

<script>
                    function formatAttribute(value, row, index) {
                        return `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditGudang('${row.gudpk}')">Edit</a>`;
                    }

                    function AddGudang() {
                        console.log(`onAddGudang is running...`);
                        $('#ModalCreateOrEditGudang').modal('show');
                        $('.gudang-create').removeClass('d-none');
                        $('.gudang-edit').addClass('d-none');
                    }

                    function onEditGudang(gudpk) {
                        console.log(`onEditGudang is running...`);

                        $('#ModalCreateOrEditGudang').modal('show');

                        $('.gudang-edit').removeClass('d-none');
                        $('.gudang-create').addClass('d-none');

                        let url = "{{ url('api/gudang/detail/:gudpk') }}";
                        url = url.replace(':gudpk', gudpk);

                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(response) {
                                console.log(`success onEditGudang here >>>>>`);
                                console.log(response);

                                $('#gudpk').val(response.data.gudpk);
                                $('#gudid').val(response.data.gudid);
                                $('#gudnm').val(response.data.gudnm);
                                $('#alamat').val(response.data.alamat);
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onEditGudang here >>>>>`);
                                console.log(xhr);
                            }
                        })
                    }

                    function onCancelGudang() {
                        resetGudang();
                        $('#ModalCreateOrEditGudang').modal('hide');
                        $('#formCreateOrEditGudang').form('reset');
                    }

                    function resetGudang() {
                        $('#gudpk').val(null);
                        $('.gudang-edit').addClass('d-none');
                        $('.gudang-create').addClass('d-none');
                        $('#ermsg-gudid').text(null);
                        $('#ermsg-gudnm').text(null);
                        $('#ermsg-alamat').text(null);
                        $('#loading-edit-gudang').addClass('d-none');
                        $('#loading-create-gudang').addClass('d-none');
                    }

                    function validateListGudang() {
                        console.log(`validateListGudang is running...`);

                        var gudid = $('#gudid').val();
                        if (!gudid) $('#ermsg-gudid').text('Kode Gudang wajib diisi');

                        var gudnm = $('#gudnm').val();
                        if (!gudnm) $('#ermsg-gudnm').text('Nama Gudang wajib diisi')

                        var alamat = $('#alamat').val();
                        if (!alamat) $('#ermsg-alamat').text('Alamat Gudang wajib diisi')

                        setTimeout(() => {
                            $('#ermsg-gudid').text(null);
                            $('#ermsg-gudnm').text(null);
                            $('#ermsg-alamat').text(null);
                        }, 5000);

                        if ((!gudid || !gudnm || !alamat)) return false;

                        console.log(`validateListGudang is valid`);

                        return true;
                    }

                    function onSubmitGudang() {
                        console.log(`onSubmitGudang is running...`);

                        if (!validateListGudang()) return;

                        $('#loading-create-gudang').removeClass('d-none');

                        let url = "{{ url('api/gudang/store') }}";
                        console.log(`url: ${url}`);

                        let formdata = new FormData();
                        formdata.append('gudid', $('#gudid').val());
                        formdata.append('gudnm', $('#gudnm').val());
                        formdata.append('alamat', $('#alamat').val());

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
                                console.log(`success onSubmitGudang here >>>>>`);
                                console.log(response);

                                onCancelGudang();
                                $('#dg-gudang').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onSubmitGudang here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }

                    function onUpdateGudang() {
                        console.log(`onUpdateGudang is running...`);

                        var gudpk = $('#gudpk').val();
                        if (!gudpk) return;

                        if (!validateListGudang()) return;

                        $('#loading-edit-gudang').removeClass('d-none');

                        let url = "{{ url('api/gudang/update/:gudpk') }}";
                        url = url.replace(':gudpk', gudpk);
                        console.log(`url: ${url}`);

                        let formdata = new FormData();

                        formdata.append('gudid', $('#gudid').val());
                        formdata.append('gudnm', $('#gudnm').val());
                        formdata.append('alamat', $('#alamat').val());
                        formdata.append('gudpk', gudpk);

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
                                console.log(`success onUpdateGudang here >>>>>`);
                                console.log(response);

                                onCancelGudang();
                                $('#dg-gudang').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onUpdateGudang here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }
                </script>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="container-fluid p-4">
        <div class="d-flex flex-column">
            <div class="p-2">
                <span class="txt-000000-700-24-28">List Gudang</span>
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
                <button onclick="AddGudang('create')" class="btn btn-dark btn-sm">&#10010; Create Gudang</button>
                @endif

                <div class="modal fade" id="ModalCreateOrEditGudang" data-bs-backdrop="static" aria-labelledby="ModalCreateOrEditGudangLabel" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h1 class="txt-000000-700-18-24-100" id="ModalCreateOrEditGudangLabel"><span class="gudang-create d-none">Create New</span><span class="gudang-edit d-none">Edit</span> Gudang</h1>
                            </div>
                            <div class="modal-body">
                                <form id="formCreateOrEditGudang" class="d-flex flex-column gap-2">
                                    <input type="text" id="gudpk" hidden>
                                    <div class="mb-3">
                                        <label for="gudid" class="col-form-label">Kode Gudang:</label>
                                        <input type="text" class="form-control" id="gudid">
                                        <span id="ermsg-gudid" class="text-danger text-blink"></span>
                                    </div>

                                    <div class="mb-3">
                                        <label for="gudnm" class="col-form-label">Nama Gudang:</label>
                                        <input type="text" class="form-control" id="gudnm">
                                        <span id="ermsg-gudnm" class="text-danger text-blink"></span>

                                    </div>

                                    <div class="mb-3">
                                        <label for="alamat" class="col-form-label">Alamat:</label>
                                        <textarea class="form-control" id="alamat"></textarea>
                                        <span id="ermsg-alamat" class="text-danger text-blink"></span>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" onclick="onCancelGudang()">Cancel</button>
                                <button type="button" class="btn btn-dark gudang-create d-none" onclick="onSubmitGudang()">Add
                                    <span id="loading-create-gudang" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
                                </button>

                                <button type="button" class="btn btn-dark gudang-edit d-none" onclick="onUpdateGudang()">Update
                                    <span id="loading-edit-gudang" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
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
                            <span id="GudnmAsc">Nama Gudang (asc)</span>
                            <span id="GudnmDesc" class="d-none">Nama Gudang (desc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByName(11)">Nama Gudang (ascending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByName(12)">Nama Gudang (descending)</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="p-0">
                <table id="dg-gudang" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="gudpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-gudang2')}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="gudid" styler="styler2">Kode</th>
                            <th field="gudnm" styler="styler2">Nama Gudang</th>
                            <th field="alamat" styler="styler2">Alamat</th>
                            <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection