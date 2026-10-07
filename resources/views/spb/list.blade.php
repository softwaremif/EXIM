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
    var dgspb = $('#dg-spb').datagrid();
    dgspb.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
    dgspb.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
    dgspb.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
    dgspb.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
    dgspb.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
    dgspb.datagrid('getPanel').css('border', 'none');
    dgspb.datagrid('getPanel').addClass('lines-no');
    dgspb.datagrid('getPanel').addClass('lines-no3');
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

  function formatAttribute(value, row) {
    var hr = '<li><hr class="dropdown-divider mt-1 mb-1"></li>';
    var imageUrl = "{{ asset('public/css/images/More.png') }}"; // Pastikan asset ini tersedia

    return '<div>' +
      '<button id="button-action-' + (row.index || '') + '" type="button" class="btn dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown">' +
      '<img src="' + imageUrl + '" height="15px" width="5px">' +
      '</button>' +
      (value || '') +
      '<ul id="dropdown-menu-action-' + (row.index || '') + '" class="dropdown-menu pt-1 pb-1">' +
      '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-' + (row.index || '') + '" onclick="KlikDetail(' + (row.spbpk || 'null') + ');">Create Invoice BC</a></li>' +
      hr +
      '<li><a class="dropdown-item dropdown-item-custom" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#ModalInvoice">Print Invoice</a></li>' +
      '</ul>' +
      '</div>';
  }


  function loadDataByFilter() {
    console.log(`loadDataByFilter RUNNING >>>>>>>>>>>>>>`);
    var searchByInput = $('#searchByInput').val();
    var filterByYear = $('#filterByYear').val();
    var filterByMonth = $('#filterByMonth').val();
    console.log(`searchByInput : ${searchByInput}`);
    $('#dg-spb').datagrid('load', {
      searchByInput: searchByInput,
      filterByYear: filterByYear,
      filterByMonth: filterByMonth,
    })
    console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
  }

  function KlikDetail(spbpk) {
    window.open("../create-invoice-beacukai/" + spbpk);
  }

</script>
@endsection

@section('content')
<div class="main-wrapper">
  <div class="container-fluid p-4">
    <div class="d-flex flex-column">

      <div class="d-flex flex-wrap p-0 pb-2 gap-2 align-items-center">
        <div class="p-0">
          <div class="input-group flex-nowrap input-group-search" style="height:34px;">
            <span class="input-group-text search">
              <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
            </span>
            <input type="text" class="form-control search" id="searchByInput" onkeyup="loadDataByFilter()" placeholder="Search...">
          </div>
        </div>

        <select class="easyui-combobox" id="filterByMonth" data-options="editable:false, panelHeight:'auto',onChange:loadDataByFilter"
          style="width:100px;">
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
          foreach ($months as $key => $month) { ?>
            <option value="<?php echo $key; ?>"><?php echo $month; ?></option>
          <?php } ?>
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

      </div>

      <div class="p-0">
        <table id="dg-spb" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="spbpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
          url="{{ route('api.get-spb')}}" data-options="fitColumns:true, sortable:true,">
          <thead>
            <tr>
              <th field="index" styler="styler1">No</th>
              <th field="tanggal" styler="styler2">Tanggal</th>
              <th field="nobukti" styler="styler2">Nomor</th>
              <th field="totalsatuan" styler="styler2">Total Pcs</th>
              <th field="gudang" styler="styler2">Gudang</th>
              <th field="jangkutan" styler="styler2">Jenis Angkutan</th>
              <th field="pangkutan" styler="styler2">Perusahaan Angkutan</th>
              <th field="contno" styler="styler2">Cont No</th>
              <th field="sealno" styler="styler2">Seal No</th>
              <th field="notruk" styler="styler2">No Truk</th>
              <th field="fwdnm" styler="styler2">Forwarder</th>
              <!-- <th field="emklnm" styler="styler2">EMKL</th> -->
              <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ModalInvoice" tabindex="-1" aria-labelledby="exampleModalLabelKita" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<b>Print Breakdown Order no </b>
				</div>
				<div class="modal-body">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" checked name="cek" id="cek" value="1">
						<label class="form-check-label" for="inlineRadio1">PDF Invoice Beacukai</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="cek" id="cek" value="2">
						<label class="form-check-label" for="inlineRadio2">PDF Invoice Buyer</label>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-dark" onclick="cetak();">Export</button>
				</div>
			</div>
		</div>
	</div>
@endsection