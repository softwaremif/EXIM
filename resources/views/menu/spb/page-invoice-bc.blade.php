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
</style>
@endsection

@section('js_custom')
<script>
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

    $(function() {
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg_tablePO').datagrid('getPanel').css('border', 'none');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dg_tablePO').datagrid('getPanel').addClass('lines-no');
        $('#dg_tablePO').datagrid('getPanel').addClass('lines-no3');
        // $('#dg_tablePO').datagrid('getPanel').addClass('col');
    });

    $(function() {
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg_tablePodt').datagrid('getPanel').css('border', 'none');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dg_tablePodt').datagrid('getPanel').addClass('lines-no');
        $('#dg_tablePodt').datagrid('getPanel').addClass('lines-no3');
    });
</script>
@endsection

@section('content')

<div class="main-wrapper min-vh-100">
    <div class="container-fluid">
        <div class="d-flex flex-column p-4 m-3">
            <form id="form_dtg">


                <div class="d-flex gap-3">

                    <div class="d-flex flex-column flex-fill">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex fw-bold ">No. LPB : </div>
                        </div>

                        <div class="d-flex my-3">
                            <div class="d-flex flex-column flex-fill">
                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No Inv</div>
                                    <!-- <input id="tgldtg" name="tgldtg" class="easyui-datebox" placeholder="YYYY-MM-DD"
                                        data-options="formatter:myformatter, parser:myparser" value=""> -->
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Input Nomor AKB" aria-label="Username">
                                        <span class="input-group-text">/</span>
                                        <span class="input-group-text">AKB</span>
                                        <span class="input-group-text">/</span>
                                        <span class="input-group-text">MIF</span>
                                        <span class="input-group-text">/</span>
                                        <span class="input-group-text">2024</span>
                                        <!-- <input type="text" class="form-control" placeholder="Server" aria-label="Server"> -->
                                    </div>
                                </div>


                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Supplier</div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </form>

            <div class="p-0">
                <table id="dg" class="easyui-datagrid" title="" align="center" toolbar="#tb" striped="false"
                    pagination="false" method="get" url=""
                    rownumbers="false" singleSelect="false" collapsible="true" fitColumns="false" idField="dtgdtpk"
                    data-options="onCheck:function(){menu();},onCheckAll:function(){menu();},onUncheck:function(){menu();},onUncheckAll:function(){menu2();},
                    multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false,
                    rowStyler:function(index,row){
                        if (row.copy==3){
                            return 'color:#000000;background-color:#D6D6D6;';
                        }
                    }">
                    <thead frozen="true" width="1000">
                        <tr>
                            <th field="ck" checkbox="true" hidden="true"></th>
                            <th field="podtpk" hidden="true" width="50">podtpk</th>
                            <th field="dtgdtpk" width="50" hidden="true" formatter="AttrDtgdtpk">dtgdtpk</th>
                            <th field="no" width="30">No</th>
                            <th field="tglfaktur" width="100" editor="text">Tgl. Faktur</th>
                            <th field="nofaktur" width="100" editor="text">No. Faktur</th>
                            <th field="po" width="100">PO</th>
                            <th field="kodebrgdok" width="100">Barcode</th>
                        </tr>
                    </thead>
                    <thead>
                        <tr>
                            <th field="brg" width="120">Barang</th>
                            <th field="warna" width="120">Warna</th>
                            <th field="ukuran" width="120">Ukuran</th>
                            <th field="op" width="80">OP</th>
                            <th field="qtypo" width="100">Qty PO</th>
                            <th field="qrs" width="100">QRS</th>
                            <th field="unit" width="100">Unit</th>
                            <th field="qtyih" width="100">Qty IH</th>
                            <th field="balqty" width="100" formatter="formatter_balance">balance</th>
                            <th field="qtydtg" editor="text" width="100">Qty<br>Surat Jalan</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection