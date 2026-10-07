@extends('layout.main')

@section('css_custom')
<style>
    .button-add {
        justify-content: center;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        border-radius: 8px;
        background: #000;
        color: #FFF;
        height: 30px;
    }

    #noinv {
        width: 40%;
    }

    #dynamic-year {
        width: 60%;
    }

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

    /* .datagrid-body {
    overflow-y: hidden !important;
    height: auto !important;
	} */

    .lines-no3 .datagrid-body td {
        border-right: 1px dotted transparent;
        border-bottom: 1px dotted transparent;
    }

    .dropdown-menu {
        max-height: 200px;
        overflow-y: auto;
        /* Add scroll if there are many checkboxes */
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
        var dgang = $('#dg-ang').datagrid();
        dgang.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
        dgang.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
        dgang.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
        dgang.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        dgang.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
        dgang.datagrid('getPanel').css('border', 'none');
        dgang.datagrid('getPanel').addClass('lines-no');
        dgang.datagrid('getPanel').addClass('lines-no3');
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

        $('#dg-ang').datagrid('load', {
            searchByInput: searchByInput,
            sortlistByName: sortlistByName,
        })
        console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
    }

    //FILTER SORT
    function sortlistByName(value) {
        document.getElementById('sortlistByName').value = value;
        if (value == 11) {
            $('#AngnmAsc').removeClass('d-none');
            $('#AngnmDesc').addClass('d-none');
        } else if (value == 12) {
            $('#AngnmAsc').addClass('d-none');
            $('#AngnmDesc').removeClass('d-none');
        } else {
            $('#AngnmAsc').removeClass('d-none');
            $('#AngnmDesc').addClass('d-none');
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
                <span class="txt-000000-700-24-28">List Perusahaan Angkutan</span>
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
                <button onclick="AddAng('create')" class="btn btn-dark btn-sm">&#10010; Create Perusahaan Angkutan</button>
                @endif

                <script>
                    function formatAttribute(value, row, index) {
                        return `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditAng('${row.angpk}')">Edit</a>`;
                    }

                    function AddAng() {
                        console.log(`onAddAng is running...`);
                        $('#ModalCreateOrEditAng').modal('show');
                        $('.ang-create').removeClass('d-none');
                        $('.ang-edit').addClass('d-none');
                    }

                    function onEditAng(angpk) {
                        console.log(`onEditAng is running...`);

                        $('#ModalCreateOrEditAng').modal('show');

                        $('.ang-edit').removeClass('d-none');
                        $('.ang-create').addClass('d-none');

                        let url = "{{ url('api/ang/detail/:angpk') }}";
                        url = url.replace(':angpk', angpk);

                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(response) {
                                console.log(`success onEditAng here >>>>>`);
                                console.log(response);

                                $('#angpk').val(response.data.angpk);
                                $('#angid').val(response.data.angid);
                                $('#angnm').val(response.data.angnm);
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onEditAng here >>>>>`);
                                console.log(xhr);
                            }
                        })
                    }

                    function onCancelAng() {
                        resetAng();
                        $('#ModalCreateOrEditAng').modal('hide');
                        $('#formCreateOrEditAng').form('reset');
                    }

                    function resetAng() {
                        $('#angpk').val(null);
                        $('.ang-edit').addClass('d-none');
                        $('.ang-create').addClass('d-none');
                        $('#ermsg-angid').text(null);
                        $('#ermsg-angnm').text(null);
                        $('#loading-edit-ang').addClass('d-none');
                        $('#loading-create-ang').addClass('d-none');
                    }

                    function validateListAng() {
                        console.log(`validateListAng is running...`);

                        var angid = $('#angid').val();
                        if (!angid) $('#ermsg-angid').text('Kode Angkutan wajib diisi');

                        var angnm = $('#angnm').val();
                        if (!angnm) $('#ermsg-angnm').text('Nama Angkutan wajib diisi')

                        setTimeout(() => {
                            $('#ermsg-angid').text(null);
                            $('#ermsg-angnm').text(null);
                        }, 5000);

                        if ((!angid || !angnm )) return false;

                        console.log(`validateListAng is valid`);

                        return true;
                    }

                    function onSubmitAng() {
                        console.log(`onSubmitAng is running...`);

                        if (!validateListAng()) return;

                        $('#loading-create-ang').removeClass('d-none');

                        let url = "{{ url('api/ang/store') }}";
                        console.log(`url: ${url}`);

                        let formdata = new FormData();
                        formdata.append('angid', $('#angid').val());
                        formdata.append('angnm', $('#angnm').val());

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
                                console.log(`success onSubmitAng here >>>>>`);
                                console.log(response);

                                onCancelAng();
                                $('#dg-ang').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onSubmitAng here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }

                    function onUpdateAng() {
                        console.log(`onUpdateAng is running...`);

                        var angpk = $('#angpk').val();
                        if (!angpk) return;

                        if (!validateListAng()) return;

                        $('#loading-edit-ang').removeClass('d-none');

                        let url = "{{ url('api/ang/update/:angpk') }}";
                        url = url.replace(':angpk', angpk);
                        console.log(`url: ${url}`);

                        let formdata = new FormData();

                        formdata.append('angid', $('#angid').val());
                        formdata.append('angnm', $('#angnm').val());
                        formdata.append('angpk', angpk);

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
                                console.log(`success onUpdateAng here >>>>>`);
                                console.log(response);

                                onCancelAng();
                                $('#dg-ang').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onUpdateAng here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }
                </script>

                <div class="modal fade" id="ModalCreateOrEditAng" data-bs-backdrop="static" aria-labelledby="ModalCreateOrEditAngLabel" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h1 class="txt-000000-700-18-24-100" id="ModalCreateOrEditAngLabel"><span class="ang-create d-none">Create New</span><span class="ang-edit d-none">Edit</span>Perusahaan Angkutan</h1>
                            </div>
                            <div class="modal-body">
                                <form id="formCreateOrEditAng" class="d-flex flex-column gap-2">
                                    <input type="text" id="angpk" hidden>
                                    <div class="mb-3">
                                        <label for="angid" class="col-form-label">Kode Perusahaan Angkutan:</label>
                                        <input type="text" class="form-control" id="angid">
                                        <span id="ermsg-angid" class="text-danger text-blink"></span>
                                    </div>

                                    <div class="mb-3">
                                        <label for="angnm" class="col-form-label">Nama Perusahaan Angkutan:</label>
                                        <input type="text" class="form-control" id="angnm">
                                        <span id="ermsg-angnm" class="text-danger text-blink"></span>

                                    </div>

                                </form>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" onclick="onCancelAng()">Cancel</button>
                                <button type="button" class="btn btn-dark ang-create d-none" onclick="onSubmitAng()">Save
                                    <span id="loading-create-ang" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
                                </button>

                                <button type="button" class="btn btn-dark ang-edit d-none" onclick="onUpdateAng()">Update
                                    <span id="loading-edit-ang" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
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
                            <span id="AngnmAsc">Nama Perusahaan Angkutan (asc)</span>
                            <span id="AngnmDesc" class="d-none">Nama Perusahaan Angkutan (desc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByName(11)">Nama Perusahaan Angkutan (ascending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByName(12)">Nama Perusahaan Angkutan (descending)</a></li>
                        </ul>
                    </div>
                </div>

            </div>


            <div class="p-0">
                <table id="dg-ang" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="angpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-ang2')}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="angid" styler="styler2">Kode</th>
                            <th field="angnm" styler="styler2">Nama Perusahaan Angkutan</th>
                            <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection