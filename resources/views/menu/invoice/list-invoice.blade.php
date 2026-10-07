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

  .button-add {
    justify-content: center;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    border-radius: 8px;
    background: #000;
    color: #FFF;
    height: 34px;
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
<script src="{{ (asset('public/assets/easy-number-separator.js' )) }}"></script>
<script>
  var baseUrl = "{{ url('/') }}";

  var returnBack = localStorage.getItem('returnBack');
  var retrievedFilterData = localStorage.getItem('filter');
  var retrievedScrollYData = localStorage.getItem('scroll');
  var parsedFilterData = retrievedFilterData ? JSON.parse(retrievedFilterData) : null;
  var parsedScrollYData = retrievedScrollYData ? JSON.parse(retrievedScrollYData) : null;

  $(window).scroll(function() {
    checkScroll();
  });

  /*menu*/
  function menu() {
    var guserpk = $('#guserpk').val();
    var userpk = "{{ session('userpk') }}";
    var row = $('#dg-spb').datagrid('getSelected');
    if (row) {
      $('#myHeader').show();
      if (guserpk == 7) {
        if (row.stsinvs > 0) {
          $('#mprint').hide();
          $('#mprintpack').hide();
        }else{
          $('#mprint').show();
          $('#mprintpack').show();
        }
        $('#cancel').hide();
      } else {
        $('#mprint').show();
        $('#mprintpack').hide();
        if (row.stsinvs > 0) {
          if(row.userpk == userpk){
            $('#cancel').hide();
            $('#uncancel').show();
          }else{
            $('#cancel').hide();
            $('#uncancel').hide();
          }
        } else {
          if(row.userpk == userpk){
            $('#cancel').show();
            $('#uncancel').hide();
          }else{
            $('#cancel').hide();
            $('#uncancel').hide();
          }
        }
      }
      $('#mclose').show();
      // $('#count').css('margin-right', '60%');
      // count_row();
    } else {
      $('#myHeader').hide();
      $('#dg').datagrid('clearSelections');
    }
  }

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
    if(row.stsinvs>0){
      return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-left:1px solid #ededed;border-top-left-radius:5px;' +
      'border-bottom-left-radius:5px;height:35px;color:#afafaf;';
    }else{
      return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-left:1px solid #ededed;border-top-left-radius:5px;' +
      'border-bottom-left-radius:5px;height:35px;';
    }
  }

  function styler2(index, row) {
    if(row.stsinvs>0){
      return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;color:#afafaf;';
    }else{
      return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;';
    }
  }

  function styler3(index, row) {
    if(row.stsinvs>0){
      return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-right:1px solid #ededed;border-top-right-radius:5px;' +
      'border-bottom-right-radius:5px;color:#afafaf;';
    }else{
      return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-right:1px solid #ededed;border-top-right-radius:5px;' +
      'border-bottom-right-radius:5px;';
    }
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
      '<li><a class="dropdown-item dropdown-item-custom" id="btn-create-' + (row.index || '') + '" onclick="CreateInv(' + (row.spbpk || 'null') + ');" style="color:#359DD9;">Create Invoice</a></li>' +
      hr +
      '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-' + (row.index || '') + '" onclick="KlikDetail(' + (row.spbpk || 'null') + ');" style="color:#359DD9;">Detail SPB</a></li>' +
      hr +
      '<li><a class="dropdown-item disabled" style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#ModalInvoiceXXX">Print SPB</a></li>' +
      '</ul>' +
      '</div>';
  }

  /*filterByMonth*/
  $(function() {
    $('#filterByMonth').combobox({
      limitToList: true,
      multiple: false,
      editable: false,
      panelHeight: 300,
      onChange: function(newValue, oldValue) {
        // alert('test 1');
        console.log(`filterBybulan oldValue : ${oldValue}`);
        console.log(`filterBybulan newValue : ${newValue}`);

        if (newValue == oldValue) return;

        // var returnBack = localStorage.getItem('returnBack');
        // if (returnBack) return;

        loadDataByFilter();
      }
    });
  });
  /*end filterByMonth*/

  /*filterByInput*/
  $(function() {
    let oldValue = '';

    $('#searchByInput').on('keyup', function() {
      const newValue = $(this).val();
      console.log(`filterByInput oldValue : ${oldValue}`);
      console.log(`filterByInput newValue : ${newValue}`);

      if (newValue === oldValue) return;

      oldValue = newValue;

      const returnBack = localStorage.getItem('returnBack');
      if (returnBack) return;

      Search();
    });
  });
  /*end filterByInput*/

  $(function() {
    if (returnBack) {
      if (parsedFilterData && parsedFilterData.tahun && parsedFilterData.tahun != '') {
        console.log("parsedFilterData.tahun : " + parsedFilterData.tahun);

        $('#filterByYear').combobox('setValue', parsedFilterData.tahun);
      }
      if (parsedFilterData && parsedFilterData.bulan && parsedFilterData.bulan != '') {
        console.log("parsedFilterData.bulan : " + parsedFilterData.bulan);

        $('#filterByMonth').combobox('setValue', parsedFilterData.bulan);
      }
      if (parsedFilterData && parsedFilterData.inputan && parsedFilterData.inputan != '') {
        console.log("parsedFilterData.input : " + parsedFilterData.inputan);

        $('#searchByInput').val(parsedFilterData.inputan);
      }
    } else {
      var tahunini = new Date().getFullYear();
      var bulanini = new Date().getMonth() + 1;
      $('#filterByYear').combobox('setValue', tahunini);
      $('#filterByMonth').combobox('setValue', bulanini);
    }
    loadDataByFilter();
  });


  function loadDataByFilter() {
    // alert('test');
    var filter = {};
    filter.tahun = $('#filterByYear').combobox('getValue');
    filter.bulan = $('#filterByMonth').combobox('getValue');
    filter.inputan = $('#searchByInput').val();
    localStorage.setItem('filter', JSON.stringify(filter));

    $('#dg-spb').datagrid({
      url: "{{ route('listinvoice') }}",
      queryParams: {
        searchByInput: $('#searchByInput').val(),
        bulan: $('#filterByMonth').combobox('getValue'),
        tahun: $('#filterByYear').combobox('getValue'),
        sortlistByDate: $('#sortlistByDate').val(),
      }
    });
  }

  function checkScroll() {
    var scrollYPosition = window.pageYOffset;

    var scroll = {};
    scroll.scrollYPosition = scrollYPosition;
    localStorage.setItem('scroll', JSON.stringify(scroll));
    // localStorage.setItem('scroll', JSON.stringify(scroll));
  }


  //FILTER SORT
  function sortlistByDate(value) {
    document.getElementById('sortlistByDate').value = value;
    if (value == 11) {
      $('#Invdatedesc').removeClass('d-none');
      $('#Invdateasc').addClass('d-none');
    } else if (value == 12) {
      $('#Invdatedesc').addClass('d-none');
      $('#Invdateasc').removeClass('d-none');
    } else {
      $('#Invdatedesc').removeClass('d-none');
      $('#Invdateasc').addClass('d-none');
    }
    loadDataByFilter();
  }

  function KlikDetail(spbpk) {
    window.open(baseUrl + "/detail-spb/" + spbpk);
    // window.open("../create-invoice-beacukai/" + spbpk);
  }

  function input_noinv() {
    var userpk = "{{ session('userpk') }}";
    $.ajax({
      type: "POST",
      url: "{{ route('input_noinv') }}",
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function(data) {
        tahun = $('#filterByYear').combobox('getValue');
        console.log(data);
        window.location.href = baseUrl + "/list-invoice/dtinvoice/input/" + data.invspk + "/" + userpk;
      },
      error: function(error) {
        console.log(error);
      }
    });
  }

  function cancelinvs(nilai) {
    var selectedRow = $('#dg-spb').datagrid('getSelected');
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    var invspk = selectedRow.invspk;

    if(nilai > 0){
      var msgku = 'Are you sure you want to cancel the selected items?';
    }else{
      var msgku = 'Are you sure you want to Uncancel the selected items?';
    }

    // // Tambahkan dialog konfirmasi
    $.messager.confirm('Confirm', msgku, function(r) {
      if (r) {
        // Jika pengguna mengkonfirmasi penghapusan
        $.ajax({
          type: 'POST',
          url: "{{ route('cancelinvs') }}",
          data: {
            invspk: invspk,
            sts : nilai
          }, // Kirim sebagai array
          headers: {
            'X-CSRF-TOKEN': csrfToken
          },
          success: function(response) {
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
            $('#dg-spb').datagrid('reload');
            $('#myHeader').hide();
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
            $('#dg-spb').datagrid('reload');
          }
        });
      }
    });
  }

  function exportexcel() {
    var tahun = $('#filterByYear').val();
    var bulan = $('#filterByMonth').val();

    var url = "{{ route('invoice.export', ['tahun' => '__TAHUN__', 'bulan' => '__BULAN__']) }}";

    // Ganti placeholder dengan nilai tahun dan bulan yang dipilih
    url = url.replace('__TAHUN__', tahun).replace('__BULAN__', bulan);

    window.location.href = url;
  }

  function print(nilai) {
    var row = $('#dg-spb').datagrid('getSelected');
    if (row && row.invspk) {
      let invspk = row.invspk;

      // Buat URL dengan parameter dinamis
      let url = "{{ route('get_invoice_cetak_pdf', ['invspk' => ':invspk', 'nilai' => ':nilai']) }}"
        .replace(':invspk', encodeURIComponent(invspk))
        .replace(':nilai', encodeURIComponent(nilai));

      // Buka di tab baru
      window.open(url, '_blank');
    } else {
      alert('No rows selected or invspk is missing');
    }
  }

  function onresi(nilai) {
    var fileUrl = "{{ asset('public/resi') }}/" + nilai;
    window.open(fileUrl, '_blank', 'fullscreen=yes');
    return false;
  }

  function onSortColumn(sort, order) {
    setTimeout(function() {
      var dg = $('#dg-spb');
      dg.datagrid('getPanel').find('.datagrid-view2 .datagrid-body tr').each(function(index) {
        $(this).find('td[field="no"] div').text(index + 1);
      });
    }, 0);
  }

  $(function() {
    $('#dg-spb').datagrid({
      onLoadSuccess: function(data) {
        console.log(`onLoadSuccess dg spb here >>>>>>>>>>>>>>>>>>>>>>>>`);
        console.log(`returnBack: ${returnBack}`);
        if (returnBack) {
          localStorage.removeItem('scroll');
          localStorage.removeItem('filter');
          localStorage.removeItem('returnBack');
        }
      }
    });
  });

  //STICKY
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
</script>
@endsection

@section('content')
<div class="main-wrapper">
  <div class="container-fluid p-4">
    <div class="d-flex flex-column">
      <input type="hidden" id="guserpk" name="guserpk" value="{{ Session::get('guserpk') }}">
      <div class="p-2">
        <span class="txt-000000-700-24-28">List Invoice</span>
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

        <!--Bulan-->
        <div class="p-0">
          <input class="easyui-combobox" id="filterByMonth" method="get" data-options="valueField:'value',textField:'bulan',url:'{{ route('bulan') }}',prompt:'Bulan'" style="width:150px;height:34px;">
        </div>

        <div class="p-0">
       <select class="easyui-combobox" id="filterByYear"
            data-options="editable:false, panelHeight:'auto',onChange:loadDataByFilter"
            style="width:100px;">

        <?php
        $startYear = date('Y') + 1; // supaya 2026 ikut
        $endYear   = date('Y') - 6;

        for ($i = $startYear; $i >= $endYear; $i--) {
            echo "<option value='{$i}'>{$i}</option>";
        }
        ?>
        </select>
        </div>

        @if (Session::get('fupkgis') || Session::get('userpk') == 39 || Session::get('userpk') == 46 || Session::get('userpk') == 24)
        <a id="input_noinv" onclick="input_noinv();">
          <input type="button" name="submit" value="&#10010; Create Invoice" class="button-add">
        </a>
        @endif
        <a id="export" onclick="exportexcel();">
          <input type="button" name="submit" value="Export Excel" class="button-add">
        </a>
        <div class="ms-auto">
          <div id="sortlist-grup" class="p-1 pb-0 responsive">
            <input type="hidden" id="sortlistByDate" name="sortlistByDate" size="5">
            <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
              <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
              <span id="Invdatedesc">Invoice date (desc)</span>
              <span id="Invdateasc" class="d-none">Invoice date (asc)</span>
            </button>
            <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
              <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByDate(11)">Invoice date (descending)</a></li>
              <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByDate(12)">Invoice date (ascending)</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="p-0">
        <table id="dg-spb" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb"
          striped="false" pageSize="100" pageList="[100,200,300,500]" idField="invspk" pagination="true" rownumbers="false"
          singleSelect="true" collapsible="false" method="get"
          url="" data-options="onSelect:function(){menu();},fitColumns:true,remoteSort:false,multiSort:true,onSortColumn: onSortColumn">
          <thead>
            <tr>
              <th field="no" styler="styler1">No</th>
              <th field="tglinvs" styler="styler2">Tanggal</th>
              <th field="noinvs" styler="styler2">No Invoice</th>
               <th field="dbno" styler="styler2">No Debitnote</th>
              <th field="funm" styler="styler2">MD</th>
              <th field="byrnm" styler="styler2" sortable="true">Buyer</th>
              <th field="sendate" styler="styler2" sortable="true">Send Date</th>
              <th field="shipno" styler="styler2">Shipment No.</th>
              <th field="shiptonm" styler="styler2">Ship To</th>
              <th field="tqty" styler="styler2">Total Qty</th>
              <th field="act" styler="styler3" align="right">Act</th>
            </tr>
          </thead>
        </table>
      </div>

      <!-- menu header -->
      <div class="header" id="myHeader" onclick="" style="display:none;">
        <div class="delete" id="mprintpack" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true"
            onclick="print('2');" style="color: #FFFFFF; text-decoration: none;">Packing List</a></div>
        <div class="delete" id="mprint" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true"
            onclick="print('1');" style="color: #FFFFFF; text-decoration: none;"> Print Invoice</a></div>
        <div class="delete" id="cancel" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true"
            onclick="cancelinvs(1);" style="color: #FFFFFF; text-decoration: none;"> Cancel </a></div>
        <div class="delete" id="uncancel" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true"
            onclick="cancelinvs(0);" style="color: #FFFFFF; text-decoration: none;"> Uncancel </a></div>
        <div class="delete" id="mclose" style="width:auto;"><a href="javascript:void(0)" plain="true"
            onclick="$('#myHeader').hide(); $('#dg').datagrid('clearSelections'); $('#dg').datagrid('clearChecked'); closemenu();"
            style="color: #FFFFFF; text-decoration: none;"> Close menu </a></div>
      </div>

    </div>
  </div>
</div>

@endsection