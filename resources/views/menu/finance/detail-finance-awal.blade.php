@extends('layout.main')

@section('css_custom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
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
</style>
@endsection

@section('js_custom')
<script>
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

        function KlikDelete(invdtpk) {
        if (!invdtpk) {
            alert("ID tidak valid.");
            return;
        }

        if (confirm("Apakah Anda yakin ingin menghapus data ini?")) {
            $.ajax({
                url: '{{ route('api.delete-invoice-detail', ':invdtpk') }}'.replace(':invdtpk', invdtpk), // Ganti placeholder dengan invdtpk
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Tambahkan CSRF token ke header
                },
                success: function(response) {
                    if (response.success) {
                        alert("Data berhasil dihapus.");
                        location.reload();
                    } else {
                        alert("Gagal menghapus data: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                    alert("Terjadi kesalahan: " + xhr.responseJSON?.message || error);
                }
            });
        }
    }

    function formatAttribute(value, row) {
        return '<div align="right">' +
            '<button type="button" class="btn btn-danger btn-sm" onclick="KlikDelete(' + (row.invdtpk || 'null') + ');"><i class="fas fa-trash-alt""></i></button>' +
            '</div>';
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
        // $('#dg').datagrid('getPanel').addClass('col');
    });

    function GoBack() {
        window.location.href = "{{ URL::to('/list-finance')}}";
    }

    function KlikPrint(){
        alert('ada');
    }
</script>
@endsection

@section('content')

<div class="main-wrapper min-vh-100">
    <div class="container-fluid">
        <div class="d-flex flex-column p-4 m-3">

            <!-- FORM ATAS -->
            <form id="form_dtg">
                @csrf
                <input type="hidden" id="invspk" name="invspk" value="{{ $dt_invoice->invpk }}">
                <input type="hidden" id="shiptopk" name="shiptopk" value="">
                <input type="hidden" id="statuspage" name="statuspage">
                <input type="hidden" name="copy" id="copy">

                <div class="d-flex gap-3">
                    <div id="GoBack" class="d-flex pointer" onclick="GoBack()"><img
                            src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                    </div>

                    <div class="d-flex flex-column flex-fill">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex fw-bold ">No. Invoice : {{ $dt_invoice->noinv }} </div>
                            <div class="d-flex gap-4 d-none" id="cancel_submit">
                                <button class="btn-transparent border-0 fw-bold"
                                    onclick="event.preventDefault(); back_input_barang()">Cancel</button>
                                <button class="btn-black rounded px-3" id="save_submit"
                                    onclick="event.preventDefault(); submit_dtg('submit');">Submit</button>
                            </div>
                            <div class="d-flex gap-4 d-none" id="edit_data">
                                <button class="btn-black rounded px-5"
                                    onclick="event.preventDefault(); edit_data();">Edit</button>
                            </div>
                            <div class="d-flex gap-4 d-none" id="cancel_save_edit">
                                <button class="btn-transparent border-0 fw-bold"
                                    onclick="event.preventDefault(); cancel_edit()">Cancel</button>
                                <button class="btn-black rounded px-3 d-none" id="save_edit"
                                    onclick="event.preventDefault(); submit_dtg('save');">Save</button>
                            </div>
                        </div>
                        <div class="d-flex my-3">
                            <div class="d-flex flex-column flex-fill">
                            <!-- <button type="button" class="gap-4 btn btn-sm btn-success" onclick="KlikPrint();"><i class="fa-solid fa-print"></i> Pdf</button> -->

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Tgl. Invoice</div>
                                    <div class="mb-2">{{ $dt_invoice->tanggal }}</div>
                                    <!-- <input id="tglinv" name="tglinv" class="easyui-datebox" style="width:210px;" placeholder="YYYY-MM-DD" data-options="formatter:myformatter, parser:myparser" disabled value="{{ $dt_invoice->tglinv }}"> -->
                                    <!-- <input id="tglinv" class="easyui-validatebox height_input" disabled name="tglinv" type="date" value="{{ $dt_invoice->tglinv }}"> -->
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No SPB</div>
                                    <div class="mb-2">{{ $dt_invoice->nobukti }}</div>
                                    <!-- <input id="nobukti" class="easyui-validatebox height_input" name="nobukti" type="text" disabled value="{{ $dt_invoice->nobukti }}"> -->
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Gudang</div>
                                    {{ $dt_invoice->gudnm }}
                                    <!-- <input id="gudang" class="easyui-validatebox height_input" name="gudang" type="text" value="{{ $dt_invoice->gudnm }}"> -->
                                    <!-- <input id="gudang" class="easyui-combobox col-2" disabled name="gudang" method="get" value="{{ $dt_invoice->gudnm }}" data-options="valueField:'gudpk', textField:'gudnm', prompt:'{{ $dt_invoice->gudnm }}', url:'{{ route('api.get-gudang') }}', editable:false, limitToList:'true'"> -->
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-fill">
                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No KK</div>
                                    <input id="nokk" class="easyui-validatebox height_input" name="nokk" type="text" value="{{ $dt_invoice->nokk }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Price PPN</div>
                                    <input id="priceppn" class="easyui-validatebox height_input" name="priceppn" type="text" value="{{ $dt_invoice->priceppn }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Kurs Pajak</div>
                                    <input id="kursp" class="easyui-validatebox height_input" name="kursp" type="text" value="{{ $dt_invoice->kursp }}">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- TAmbah baris -->
            <div id="add" class="p-0 d-none">
                <div class="p-0 font-5 color-view-blue pointer fw-bold" id="adddt" onclick="tambahrow();">Add Data</div>
            </div>
            <div class="p-0">

                <table id="dg" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb"
                    striped="false" pageSize="100" pageList="[100,200,300,500]" idField="invdtpk" pagination="false"
                    rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-invoice-detail', $dt_invoice->spbpk)}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="mpo" styler="styler2">MPO</th>
                            <th field="qty" styler="styler2">QTY</th>
                            <th field="pricedt" styler="styler2">Price</th>
                            <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>
    </div>
</div>
@endsection