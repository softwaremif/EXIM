@extends('layout.main')

@section('css_custom')

<style>
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
</style>
@endsection

@section('js_custom')
<script>
    $(function() {
        $('#dg-shipment').datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
        $('#dg-shipment').datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
        $('#dg-shipment').datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
        $('#dg-shipment').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg-shipment').datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
        $('#dg-shipment').datagrid('getPanel').css('border', 'none');
        $('#dg-shipment').datagrid('getPanel').addClass('lines-no');
        $('#dg-shipment').datagrid('getPanel').addClass('lines-no3');
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
        var filterByYear = $('#filterByYear').val();
        var filterByMonth = $('#filterByMonth').val();
        var sortlistByDate = $('#sortlistByDate').val();

        console.log(`searchByInput : ${searchByInput}`);
        $('#dg-shipment').datagrid('load', {
            searchByInput: searchByInput,
            filterByYear: filterByYear,
            filterByMonth: filterByMonth,
            sortlistByDate: sortlistByDate,
        })

        console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
    }

    //FILTER SORT
    function sortlistByDate(value) {
        document.getElementById('sortlistByDate').value = value;
        if (value == 11) {
            $('#DateDesc').removeClass('d-none');
            $('#DateAsc').addClass('d-none');
        } else if (value == 12) {
            $('#DateDesc').addClass('d-none');
            $('#DateAsc').removeClass('d-none');
        } else {
            $('#DateDesc').removeClass('d-none');
            $('#DateAsc').addClass('d-none');
        }
        loadDataByFilter();
    }

    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d);
    }

    function myparser(s) {
        if (!s) return new Date();

        var ss = (s.split('-'));
        var y = parseInt(ss[0], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[2], 10);
        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) return new Date(y, m - 1, d);
        else return new Date();
    }
</script>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="container-fluid p-4">
        <div class="d-flex flex-column">
            <div class="p-2">
                <span class="txt-000000-700-24-28">List Shipment</span>
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

                <select class="easyui-combobox" id="filterByMonth" data-options="editable:false, onChange:loadDataByFilter" style="width:150px; height:34px;">
                    <?php
                    $months = [
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ];

                    $currentMonth = date('m');
                    foreach ($months as $key => $month) {
                        $selected = ($key == $currentMonth) ? 'selected' : '';
                        echo "<option value='$key' $selected>$month</option>";
                    }
                    ?>
                </select>

                <div class="p-0">
                    <select class="easyui-combobox" id="filterByYear" data-options="editable:false, panelHeight:'auto',onChange:loadDataByFilter"
                        style="width:100px;">
                        <?php
                        for ($i = date('Y'); $i >= date('Y') - 6; $i -= 1) { ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="ms-auto">
                    <div id="sortlist-grup" class="p-1 pb-0 responsive">
                        <input type="hidden" id="sortlistByDate" name="sortlistByDate" size="5">
                        <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
                            <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
                            <span id="DateDesc">Ex Fac Date (desc)</span>
                            <span id="DateAsc" class="d-none">Ex Fac Date (asc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByDate(11)">Ex Fac Date (descending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByDate(12)">Ex Fac Date (ascending)</a></li>
                        </ul>
                    </div>
                </div>

            </div>


            <div class="p-0">
                <table id="dg-shipment" class="easyui-datagrid" title="" style="width:99%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="spbdtpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-shipment')}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="tanggal" styler="styler2">Ex Fac</th>
                            <th field="est_ship" styler="styler2">Est Ship</th>
                            <th field="noop" styler="styler2">OP</th>
                            <th field="buyer" styler="styler2">Buyer</th>
                            <th field="orderno" styler="styler2">PO Buyer</th>
                            <th field="qty" styler="styler2">Quantity</th>
                            <th field="carton" styler="styler2">Carton</th>
                            <th field="forwarder" styler="styler2">Via Forwarder</th>
                            <th field="gudang" styler="styler2">Gudang</th>
                            <th field="emkl" styler="styler2">EMKL</th>
                            <th field="truck" styler="styler3">Truck</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection