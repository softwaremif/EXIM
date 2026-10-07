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
        var dgjang = $('#dg-jang').datagrid();
        dgjang.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
        dgjang.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
        dgjang.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
        dgjang.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        dgjang.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
        dgjang.datagrid('getPanel').css('border', 'none');
        dgjang.datagrid('getPanel').addClass('lines-no');
        dgjang.datagrid('getPanel').addClass('lines-no3');
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

        $('#dg-jang').datagrid('load', {
            searchByInput: searchByInput,
            sortlistByName: sortlistByName,
        })
        console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
    }

    //FILTER SORT
    function sortlistByName(value) {
        document.getElementById('sortlistByName').value = value;
        if (value == 11) {
            $('#JangAsc').removeClass('d-none');
            $('#JangDesc').addClass('d-none');
        } else if (value == 12) {
            $('#JangAsc').addClass('d-none');
            $('#JangDesc').removeClass('d-none');
        } else {
            $('#JangAsc').removeClass('d-none');
            $('#JangDesc').addClass('d-none');
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
                <span class="txt-000000-700-24-28">List Jenis Angkutan</span>
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
                <button onclick="AddJang('create')" class="btn btn-dark btn-sm">&#10010; Create Jenis Angkutan</button>
                @endif

                <script>
                    function formatAttribute(value, row, index) {
                        return `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditJang('${row.jangpk}')">Edit</a>`;
                    }

                    function AddJang() {
                        console.log(`onAddJang is running...`);
                        $('#ModalCreateOrEditJang').modal('show');
                        $('.jang-create').removeClass('d-none');
                        $('.jang-edit').addClass('d-none');
                    }

                    function onEditJang(jangpk) {
                        console.log(`onEditJang is running...`);

                        $('#ModalCreateOrEditJang').modal('show');

                        $('.jang-edit').removeClass('d-none');
                        $('.jang-create').addClass('d-none');

                        let url = "{{ url('api/jang/detail/:jangpk') }}";
                        url = url.replace(':jangpk', jangpk);

                        $.ajax({
                            url: url,
                            method: 'GET',
                            success: function(response) {
                                console.log(`success onEditJang here >>>>>`);
                                console.log(response);

                                $('#jangpk').val(response.data.jangpk);
                                $('#jangid').val(response.data.jangid);
                                $('#jangnm').val(response.data.jangnm);
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onEditJang here >>>>>`);
                                console.log(xhr);
                            }
                        })
                    }

                    function onCancelJang() {
                        resetAng();
                        $('#ModalCreateOrEditJang').modal('hide');
                        $('#formCreateOrEditJang').form('reset');
                    }

                    function resetAng() {
                        $('#jangpk').val(null);
                        $('.jang-edit').addClass('d-none');
                        $('.jang-create').addClass('d-none');
                        $('#ermsg-jangid').text(null);
                        $('#ermsg-jangnm').text(null);
                        $('#loading-edit-jang').addClass('d-none');
                        $('#loading-create-jang').addClass('d-none');
                    }

                    function validateListJang() {
                        console.log(`validateListJang is running...`);

                        var jangid = $('#jangid').val();
                        if (!jangid) $('#ermsg-jangid').text('Kode Angkutan wajib diisi');

                        var jangnm = $('#jangnm').val();
                        if (!jangnm) $('#ermsg-jangnm').text('Nama Jenis Angkutan wajib diisi')

                        setTimeout(() => {
                            $('#ermsg-jangid').text(null);
                            $('#ermsg-jangnm').text(null);
                        }, 5000);

                        if ((!jangid || !jangnm)) return false;

                        console.log(`validateListJang is valid`);

                        return true;
                    }

                    function onSubmitJang() {
                        console.log(`onSubmitJang is running...`);

                        if (!validateListJang()) return;

                        $('#loading-create-jang').removeClass('d-none');

                        let url = "{{ url('api/jang/store') }}";
                        console.log(`url: ${url}`);

                        let formdata = new FormData();
                        formdata.append('jangid', $('#jangid').val());
                        formdata.append('jangnm', $('#jangnm').val());

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
                                console.log(`success onSubmitJang here >>>>>`);
                                console.log(response);

                                onCancelJang();
                                $('#dg-jang').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onSubmitJang here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }

                    function onUpdateJang() {
                        console.log(`onUpdateJang is running...`);

                        var jangpk = $('#jangpk').val();
                        if (!jangpk) return;

                        if (!validateListJang()) return;

                        $('#loading-edit-jang').removeClass('d-none');

                        let url = "{{ url('api/jang/update/:jangpk') }}";
                        url = url.replace(':jangpk', jangpk);
                        console.log(`url: ${url}`);

                        let formdata = new FormData();

                        formdata.append('jangid', $('#jangid').val());
                        formdata.append('jangnm', $('#jangnm').val());
                        formdata.append('jangpk', jangpk);

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
                                console.log(`success onUpdateJang here >>>>>`);
                                console.log(response);

                                onCancelJang();
                                $('#dg-jang').datagrid('reload');
                            },
                            error: function(xhr, status, error) {
                                console.log(`error onUpdateJang here >>>>>`);
                                console.log(xhr);
                            }
                        });
                    }
                </script>

                <div class="modal fade" id="ModalCreateOrEditJang" data-bs-backdrop="static" aria-labelledby="ModalCreateOrEditJangLabel" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header border-0">
                                <h1 class="txt-000000-700-18-24-100" id="ModalCreateOrEditJangLabel"><span class="jang-create d-none">Create New</span><span class="jang-edit d-none">Edit</span> Jenis Angkutan</h1>
                            </div>
                            <div class="modal-body">
                                <form id="formCreateOrEditJang" class="d-flex flex-column gap-2">
                                    <input type="text" id="jangpk" hidden>
                                    <div class="mb-3">
                                        <label for="jangid" class="col-form-label">Kode Angkutan:</label>
                                        <input type="text" class="form-control" id="jangid">
                                        <span id="ermsg-jangid" class="text-danger text-blink"></span>
                                    </div>

                                    <div class="mb-3">
                                        <label for="jangnm" class="col-form-label">Nama Jenis Angkutan:</label>
                                        <input type="text" class="form-control" id="jangnm">
                                        <span id="ermsg-jangnm" class="text-danger text-blink"></span>
                                    </div>

                                </form>
                            </div>

                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-light" onclick="onCancelJang()">Cancel</button>
                                <button type="button" class="btn btn-dark jang-create d-none" onclick="onSubmitJang()">Add
                                    <span id="loading-create-jang" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
                                </button>

                                <button type="button" class="btn btn-dark jang-edit d-none" onclick="onUpdateJang()">Update
                                    <span id="loading-edit-jang" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
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
                            <span id="jangnmDesc">Nama Angkutan (desc)</span>
                            <span id="jangnmAsc" class="d-none">Nama Angkutan (asc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByName(11)">Nama Angkutan (descending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByName(12)">Nama Angkutan (ascending)</a></li>
                        </ul>
                    </div>
                </div>

            </div>


            <div class="p-0">
                <table id="dg-jang" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="jangpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-jang2')}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="jangid" styler="styler2">Kode</th>
                            <th field="jangnm" styler="styler2">Nama Jenis Angkutan</th>
                            <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection