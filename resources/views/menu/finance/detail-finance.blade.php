@extends('layout.main')

@section('css_custom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

    .datagrid-row {
        height: auto !important;
        white-space: normal !important;
    }

    .datagrid-body {
        overflow-x: hidden;
        overflow-y: hidden;
    }

    .text-biru {
        color: #359DD9;
        font-weight: 700;
        cursor: pointer;
    }

    .datagrid-editable-input33 input {
        width: 200px !important;
    }
</style>
@endsection

@section('js_custom')
<script>
    $(function() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];

        var nilai = (pathname3 === 'detail-finance');
        var invpk = $('#invpk').val();
        // var invpk = '{{ request()->segment(2) }}';

        $('#dg').edatagrid({
            saveUrl: '{{ route("api.insert-invoicedt", "") }}/' + invpk,
            updateUrl: '{{ route("api.update-invoicedt") }}',
            editing: nilai,
            onSuccess: function(index, row) {
                $(this).edatagrid('saveRow');
                $(this).datagrid('reload');
                console.log("ROW : " + row);
                console.log("nilai : " + nilai);
            },
            onDblClickRow: function(index, row) {
                var isEdit = $('#isEdit-' + row.invdtpk).val();
                var invdtpk = row.invdtpk;

                if (isEdit == 'true') {
                    document.getElementById('isEdit-' + row.invdtpk).value = false;
                    hiddenSave(index, invdtpk);
                    $(this).datagrid('endEdit', index);
                } else {
                    console.log('onedit : ' + row.invdtpk);
                    $('#isEdit-' + row.invdtpk).val('true');

                    if ($(this).edatagrid('options').editing) {
                        $(this).datagrid('beginEdit', index);
                        console.log("Double click edit mode");
                    }
                }
            },
            onBeforeEdit: function(index, row) {
                var token = $('meta[name="csrf-token"]').attr('content');
                row._token = token;
            }
        });
    });

    $(function() {
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dg').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg').datagrid('getPanel').css('border', 'none');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dg').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dg').datagrid('getPanel').addClass('lines-no');
        $('#dg').datagrid('getPanel').addClass('lines-no3');
    });

    $(function() {
        editDisplay();
    });

    function editDisplay() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];

        if (pathname3 == 'detail-finance') {
            $('#lookupgis').removeClass('d-none');
            $('#dg').edatagrid('options').editing = true;
            // $('#dg').datagrid('showColumn', 'ck');
            $('#edit_data').removeClass('d-none');
            // $('#add').addClass('d-none');
            $('#noinv').attr('disabled', true);
            $('#nokk').attr('disabled', true);
            $('#nofp').attr('disabled', true);
            $('#tglinv').datebox('disable');
            $('#tglkk').datebox('disable');
            // $('#priceppn').attr('disabled', true);
            $('#kursp').attr('disabled', true);
            $('#ppn').attr('disabled', true);
            $('#addnew').addClass('d-none');
            $('#lookupgis').addClass('d-none');
        } else 
        {
            $('#cancel_submit').addClass('d-none');
            $('#edit_data').addClass('d-none');
        }
    }

    function edit_data() {
        $('#lookupgis').removeClass('d-none');
        $('#edit_data').addClass('d-none');
        $('#cancel_save_edit').removeClass('d-none');
        $('#add').removeClass('d-none');
        $('#dg').edatagrid('options').editing = true;
        $('#dg').datagrid('showColumn', 'ck');
        $('#noinv').attr('disabled', false);
        $('#nokk').attr('disabled', false);
        $('#nofp').attr('disabled', false);
        $('#tglinv').datebox('enable');
        $('#tglkk').datebox('enable');
        // $('#priceppn').attr('disabled', false);
        $('#kursp').attr('disabled', false);
        $('#ppn').attr('disabled', false);
        $('#addnew').removeClass('d-none');
    }

    function cancel_edit() {
        $('#edit_data').removeClass('d-none');
        $('#dg').edatagrid('options').editing = false;
        $('#dg').datagrid('hideColumn', 'ck');
        $('#cancel_save_edit').addClass('d-none');
        $('#add').addClass('d-none');
        $('#lookupgis').addClass('d-none');
        
        var rows = $('#dg').datagrid('getRows');
        for (var i = 0; i < rows.length; i++) {
            $('#dg').datagrid('endEdit', i);
        }
        $('#noinv').attr('disabled', true);
        $('#nokk').attr('disabled', true);
        $('#nofp').attr('disabled', true);
        $('#tglinv').datebox('disable');
        $('#tglkk').datebox('disable');
        // $('#priceppn').attr('disabled', true);
        $('#kursp').attr('disabled', true);
        $('#ppn').attr('disabled', true);
        $('#addnew').addClass('d-none');
    }

    

    $(function() {
        $('#dg').edatagrid('options').editing = false;
        var invpk = $('#invpk').val(); 
        var url = "{{ route('api.get-invoice-detail', ['invpk' => ':invpk']) }}";
        url = url.replace(':invpk', invpk);

        $('#dg').datagrid({
            url: url,
            method: 'get',
            onLoadSuccess: function(data) {
                console.log('Data loaded successfully:', data);
            },
            onLoadError: function() {
                console.error('Failed to load data.');
            }
        });
    });

    function tambahrow() {
        $('#dg').edatagrid('addRow');
        var index = $('#dg').edatagrid('getRows').length - 1; 
        var ed = $('#dg').edatagrid('getEditors', index)[0]; 
        $(ed.target).focus();
    }

    function submit_invs(nilai) {
        var formElement = document.getElementById("form_header");

        var requiredFields = ["invpk"];
        var isValid = true;
        var errorMessage = "Please fill in all required fields:\n";

        requiredFields.forEach(function(fieldId) {
            var field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                isValid = false;
                errorMessage += `- ${fieldId}\n`;
            }
        });

        if (!isValid) {
            return;
        }

        var formData = new FormData(formElement);

        $.ajax({
            method: "POST",
            url: "{{ route('api.edit-save-header') }}",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,

            success: function(response) {
                console.log("COBA RESPONSE" + JSON.stringify(response));
                if (nilai == 'submit') {
                    $('#dg').datagrid('reload');
                    window.location.href = "{{ route('list.finance') }}";
                } else {
                    cancel_edit();
                }
            },
            error: function(xhr, status, error) {
                console.log("error add HERE >>>>>>>>>>>>>>>> " + JSON.stringify(xhr));

                if (xhr.responseJSON && xhr.responseJSON.message) showAlert(400, xhr.responseJSON.message);
                else showAlert(400, JSON.stringify(error))
            }
        });
    }

    var editingIndex = undefined;

    function onClickRowHandler(rowIndex) {
        if (editingIndex !== undefined) {
            $('#datagrid').datagrid('endEdit', editingIndex);
            $('#datagrid').datagrid('acceptChanges');
            $('#datagrid').datagrid('reload'); // Reload datagrid
        }
        editingIndex = rowIndex;
        $('#datagrid').datagrid('beginEdit', rowIndex);
    }

    // function KlikPrint() {
    //     var url = "http://192.168.0.22/SPB/detail/" + {{$dt_invoice->spbpk}} + "/finance/pdf";
    //     window.open(url, '_blank');
    // }

    function KlikPrint() {
        var baseUrl = "{{ url('/') }}"; // Mengambil URL dasar dari Laravel
        var spbpk = "{{ $dt_invoice->spbpk }}"; // Mengambil nilai SPBPK dari Blade

        var url = baseUrl + "/detail/" + spbpk + "/finance/pdf";
        window.open(url, '_blank');
    }

    function GoBack() {
        window.location.href = "{{ URL::to('/list-finance')}}";
    }

    function menu() {
        var row = $('#dg').datagrid('getChecked');
        var tchek = $('#dg').datagrid('getChecked').length;

        if (row) {
            if (tchek > 1) {
                $('#myHeader').show();
                $('#count').show();
                $('#count2').hide();
                $('#mdelete').show();
                $('#count').css('margin-right', '70%');
                count_row();
            } else {
                $('#myHeader').show();
                $('#count').show();
                $('#count2').hide();
                $('#mdelete').show();
                $('#count').css('margin-right', '70%');
                count_row();
            }
        } else {
            $('#myHeader').hide();
            $('#dg').datagrid('clearSelections');
        }
        
    }

    function count_row() {
        var count = $('#dg').datagrid('getChecked').length;
        if (count) {
            $('#myHeader').show();
            document.getElementById("count").innerHTML = count + " items selected";
            document.getElementById("totreq").value = count;
            document.getElementById("totreq2").value = count;
        } else {
            $('#myHeader').hide();
            $('#dg').datagrid('clearSelections');
            document.getElementById("count").innerHTML = 0 + " items selected";
        }
    }

    function closemenu() {
        $('#myHeader').hide();
    }

    window.onscroll = function() {
        myFunctiona();
    };

    var header = document.getElementById("myHeader");
    var sticky = header.offsetTop;

    function myFunctiona() {
        if (window.pageYOffset > sticky) {
            header.classList.add("sticky");
        } else {
            header.classList.remove("sticky");
        }
    }

    function delete_list_invsdt() {
        var selectedRow = $('#dg').datagrid('getSelected');
        if (!selectedRow) {
            return;
        }

        var checkedRows = $('#dg').datagrid('getChecked');
        if (!checkedRows || checkedRows.length === 0) {
            return;
        }

        var ids = checkedRows.map(function(row) {
            return row.invdtpk; // Ambil dtgdtpk dari setiap baris yang dicentang
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        $.messager.confirm('Confirm', 'Are you sure you want to delete the selected items?', function(r) {
            if (r) {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.delete-invoice-detail') }}",
                    data: {
                        ids: ids
                    }, 
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('#dg').datagrid('reload');
                        $.messager.show({
                            title: '',
                            msg: response.msg,
                            timeout: 3000,
                            showType: 'slide',
                            icon: 'info',
                            width: 430,
                            height: 55,
                            style: {
                                left: 0,
                                right: '',
                                top: '',
                                bottom: -document.body.scrollTop - document.documentElement.scrollTop
                            }
                        });
                        $('#dg').datagrid('reload');
                    },
                    error: function(xhr, status, error) {
                        $.messager.show({
                            title: 'Error',
                            msg: 'An error occurred: ' + (xhr.responseText || error),
                            timeout: 3000,
                            showType: 'slide',
                            icon: 'error',
                            width: 430,
                            height: 55,
                        });
                        $('#dg').datagrid('reload');
                    }
                });
            }
        });
    }

    function styler1(value, row, index) {
        return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-left:1px solid #ededed;border-top-left-radius:5px;' +
        'border-bottom-left-radius:5px;height:35px;';
    }

    function styler2(value, row, index) {
        return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;';
    }

    function styler3(value, row, index) {
        return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-right:1px solid #ededed !important;border-top-right-radius:5px;' +
        'border-bottom-right-radius:5px;';
    }

    function KlikLoopUp() {
        $('#ModalOrder').modal('show');
    }

    $(function() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];
        if (pathname3 == 'detail-finance') {
            $('#dg').datagrid('showColumn', 'ck');
        } else {
            $('#dg').datagrid('hideColumn', 'ck');
        }
        $('#ModalOrder').on('shown.bs.modal', function() {
            $('#dgOrder').datagrid('resize');
        })
    });

    function SearchOrder() {
        $('#dgOrder').datagrid('load', {
            searchByInputOrder: $('#searchByInputOrder').val(),
        });
    }

    function SubmitModalLookup(dgname) {
        var invpk = $('#invpk2').val();
       
        if (dgname == 'ORDERLIST') {
            var selectedRow = $('#dgOrder').datagrid('getSelected');
            if (!selectedRow) {
                $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }

            var checkedRows = $('#dgOrder').datagrid('getChecked');
            if (!checkedRows || checkedRows.length === 0) {
                $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }
            var rows = $('#dgOrder').datagrid('getChecked');
        }

        var formData = {
            invpk: invpk,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        if (rows.length > 0) {
            console.log("Rows selected: " + rows.length);
            console.log("Selected rows: " + JSON.stringify(rows));
        } else {
            console.log("No rows are selected.");
        }

        formData.rows = rows;

        if (dgname == 'ORDERLIST') {
            url = "{{ route('api.add-lookup-detail-finance') }}";
        }

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            success: function(data) {
                console.log("Success:", data);
                window.location.reload();
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
                console.log(xhr);
            }
        });
    }

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
  
</script>
@endsection

@section('content')

<div class="main-wrapper min-vh-100">
    <div class="container-fluid">
        <div class="d-flex flex-column p-4 m-3">
            <!-- FORM ATAS -->
            <form id="form_header">
                @csrf
                <input type="hidden" id="invpk" name="invpk" value="{{ $dt_invoice->invpk }}">

                <div class="d-flex gap-3">

                    <div class="d-flex pointer" onclick="GoBack()"><img
                            src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                    </div>

                    <div class="d-flex flex-column flex-fill">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex fw-bold ">No. INV : {{ $dt_invoice->noinv }}</div>

                            <div class="d-flex gap-4 d-none" id="edit_data">
                                <button class="btn-black rounded px-3" onclick="event.preventDefault(); edit_data();">Edit</button>
                                <button class="btn-black rounded px-3" onclick="event.preventDefault(); KlikPrint();"><i class="fa-solid fa-print"></i> Pdf</button>
                            </div>

                            <div class="d-flex gap-4 d-none" id="cancel_save_edit">
                                <button class="btn-transparent border-0 fw-bold" onclick="event.preventDefault(); cancel_edit()">Cancel</button>
                                <button class="btn-black rounded px-3" id="save_edit" onclick="event.preventDefault(); submit_invs('save');">Save</button>
                            </div>
                        </div>

                        <div class="d-flex my-3">
                            <div class="d-flex flex-column flex-fill">

                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Tgl. Invoice</div>
                                        <div class="mb-2">&nbsp; : &nbsp; 
                                            <input id="tglinv" name="tglinv" class="easyui-datebox" style="width:210px;" placeholder="YYYY-MM-DD" data-options="formatter:myformatter, parser:myparser" value="{{ \Carbon\Carbon::parse($dt_invoice->tglinv)->format('d/m/Y') }}">
                                        </div>
                                    </div>

                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">No. Inv</div>
                                        <div class="mb-2">&nbsp; : &nbsp;                                        
                                            <input id="noinv" class="easyui-validatebox height_input" name="noinv" type="text" value="{{ $dt_invoice->noinv }}">
                                        </div>
                                    </div>

                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">No. SPB</div>
                                        <div class="mb-2">&nbsp; : &nbsp;{{ $dt_invoice->nobukti }}</div>
                                    </div>

                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Gudang</div>
                                        <div class="mb-2">&nbsp; : &nbsp;{{ $dt_invoice->gudnm }} </div>
                                    </div>

                                </div>

                            <div class="d-flex flex-column flex-fill">

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No KK</div>
                                    <div class="mb-2">&nbsp; : &nbsp;
                                        <input id="nokk" class="easyui-validatebox height_input" name="nokk" type="text" value="{{ $dt_invoice->nokk }}">
                                    </div>
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Tgl KK</div>
                                    <div class="mb-2">&nbsp; : &nbsp;
                                        <input id="tglkk" name="tglkk" class="easyui-datebox" style="width:210px;" placeholder="YYYY-MM-DD" data-options="formatter:myformatter, parser:myparser" value="{{ \Carbon\Carbon::parse($dt_invoice->tglkk)->format('d/m/Y') }}">
                                    </div>
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No Faktur Pajak</div>
                                    <div class="mb-2">&nbsp; : &nbsp;
                                        <input id="nofp" class="easyui-validatebox height_input" name="nofp" type="text" value="{{ $dt_invoice->nofp }}">
                                    </div>
                                </div>

                                <div class="d-flex p-1 d-none">
                                    <div class="p-0 w-25">Price + PPN (USD)</div>
                                    <div class="mb-2">&nbsp; : &nbsp;
                                        <input id="priceppn" class="easyui-validatebox height_input" name="priceppn" type="text" value="">
                                    </div>
                                </div>

                                @if($dt_invoice->cur == 1)
                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Kurs Pajak (IDR)</div>
                                    <div class="mb-2">&nbsp; : &nbsp;
                                        <input id="kursp" class="easyui-validatebox height_input" name="kursp" type="text" value="{{ $dt_invoice->kursp}}">
                                    </div>
                                </div>
                                @endif

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">PPN(%)</div>
                                    <div class="mb-2">&nbsp; : &nbsp;
                                        <input id="ppn" class="easyui-validatebox height_input" name="ppn" type="text" value="{{ $dt_invoice->ppn}}">
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- TAmbah baris -->
            <!-- <div id="add" class="p-0 d-none">
                <div class="p-0 font-5 color-view-blue pointer fw-bold" id="adddt" onclick="tambahrow();" style="color:#359DD9; text-decoration:none;">Add Data</div>
            </div> -->

            <div class="d-flex align-items-center gap-3 mb-3"> <!-- Wadah Flexbox -->
                <div id="add" class="p-0 d-none">
                    <div class="p-0 font-5 color-view-blue pointer fw-bold" id="adddt" onclick="tambahrow();" style="color:#359DD9; text-decoration:none;">
                        Add Data
                    </div>
                </div>

                <div id="lookupgis" class="flex-grow-0 d-none">
                    <button onclick="KlikLoopUp()" class="btn btn-sm btn-dark">
                        Lookup Order GIS
                    </button>
                </div>
            </div>


            <div class="modal fade" id="ModalOrder" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content p-4 gap-1">
                        <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                            <div class="container-fluid">
                                <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal" data-bs-target="#ChooseModal">
                                    <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="25" height="25">
                                    <span>BACK</span>
                                </div>
                            </div>
                        </div>
                        <div class="p-0">
                            <div class="p-0 fw-bold h5">Lookup From Order List</div>
                        </div>
                        <div class="p-0">
                            <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                                <span class="input-group-text search">
                                    <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                                </span>
                                <input type="text" class="form-control search" id="searchByInputOrder" onkeyup="SearchOrder()" placeholder="Search...">
                            </div>
                        </div>

                        <input type="hidden" name="invpk2" id="invpk2" value="{{ $dt_invoice->invpk }}">

                        <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                            <div>
                                <table id="dgOrder" class="easyui-datagrid" title="" style="width:100%; height:auto" url="{{ route('api.get-lookup-detail-finance') }}"
                                    align="center" toolbar="#tb" striped="true" pagination="true" pageSize="100" fitColumns="true"
                                    pageList="[100,200,300,500]" method="get" rownumbers="false" multiple="true" collapsible="true"
                                    data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                                    <thead>
                                        <tr>
                                            <th field="ck" width="35" checkbox="true"></th>
                                            <!-- <th field="ordpk" width="100">Ordpk</th> -->
                                            <th field="funm" width="100">MD</th>
                                            <th field="ordno" width="100">Order No</th>
                                            <th field="byrnm" width="100">Buyer</th>
                                            <th field="artno" width="100">Style</th>
                                            <th field="prodes" width="250">Description</th>
                                            <th field="season" width="100">Season</th>
                                            <th field="srno" width="100">SR NO</th>
                                            <th field="ordqty" width="100">Qty</th>
                                            <th field="stage" width="100">Stage</th>
                                            <th field="datecreate" width="100">Date Create</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer m-0 p-0">
                            <input type="hidden" id="nilai_unit_filter" name="nilai_unit_filter">
                            <button type="button" id="get_button" class="btn btn-secondary my-2 py-1" onclick="SubmitModalLookup('ORDERLIST')">GET</button>
                        </div>
                    </div>
                </div>
            </div>


            <div class="p-0">
                <table id="dg" class="easyui-datagrid" title="" align="center" toolbar="#tb" striped="false"
                    pagination="true" method="get"
                    rownumbers="false" pageSize="100" pageList="[100,200,300,500]" singleSelect="false" collapsible="true" fitColumns="true" idField="invdtpk"
                    data-options="onCheck:function(){menu();},onCheckAll:function(){menu();},onUncheck:function(){menu();},onUncheckAll:function(){menu2();},
                    multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false,  onClickRow: onClickRowHandler,
                    rowStyler:function(index,row){
                        if (row.copy==3){
                            return 'color:#000000;background-color:#D6D6D6;';
                        }
                    }">
                    <thead>
                        <tr>
                            <th field="ck" width="auto" styler="styler1" checkbox="true" hidden="true"></th>
                            <th field="mpo" width="auto" styler="styler2" editor="text">MPO</th>
                            <th field="mstyle" width="auto" styler="styler2" editor="text">Style</th>
                            <th field="mprodes" width="auto" styler="styler2" editor="text">Description</th>
                            <th field="qty" width="auto" styler="styler2" editor="text">QTY</th>
                            @if($dt_invoice->cur == 1)
                            <th field="pricedt" width="auto" styler="styler3" editor="text">Price Unit (USD)</th>
                            @else
                            <th field="pricedt" width="auto" styler="styler3" editor="text">Price Unit (IDR)</th>
                            @endif
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="header" id="myHeader" onclick="" style="display:none;">
                <div id="count" style="display:none;"></div>
                <div class="delete" id="mdelete" style="width:auto; display:none;">
                    <a href="javascript:void(0)" plain="true" onclick="delete_list_invsdt();" style="color: #FFFFFF; text-decoration: none;"> Delete </a>
                </div>
                <div class="delete" id="mclose" style="width:auto;"><a href="javascript:void(0)" plain="true"  onclick="$('#myHeader').hide(); $('#dg').datagrid('clearSelections'); $('#dg').datagrid('clearChecked'); closemenu();" style="color: #FFFFFF; text-decoration: none;"> Close menu </a></div>
            </div>

        </div>
    </div>
</div>
@endsection