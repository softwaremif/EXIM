@extends('layout.main')

@section('css_custom')
@endsection

@section('js_custom')
<script>
  var guserid = "{{ Session::get('guserid') }}";
  var fupkgis = "{{ Session::get('fupkgis') }}";

  $(function() {
    $('.dropdown-menu-sortlist a').on('click', function(event) {
      event.preventDefault();
      
      var buttonText = $(this).text();
      var imagePath = "{{ asset('public/css/images/sort.jpg') }}";

      $('#title-sortlistByDate').text(buttonText);
    });
  })

  function styleDGDbnote() {
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
    // $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-header').addClass('txt-000000-700-14-18-38');
    $('#dg-cinvoice').datagrid('getPanel').find('div.datagrid-body').addClass('lines-no');
  }

  function styler1(value, row, index) {
    return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-left:1px solid #ededed;border-top-left-radius:5px;border-bottom-left-radius:5px;height:35px;';
  }

  function styler2(value, row, index) {
    return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;';
  }

  function styler3(value, row, index) {
    return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed;border-right:1px solid #ededed !important;border-top-right-radius:5px;border-bottom-right-radius:5px;';
  }

  function attrBillTo(value, row, index) {
    var alamatbill = value ? value.replace(/\n/g, '<br>') : '';

    return `<span class="text-wrap">${alamatbill}</span>`;
  }

  function attrPobyr(value, row, index) {
    return `<span class="text-wrap">${row.pobyr}</span>`;
  }

  function attrAct(value, row, index) {
    let url = "{{ url('commercial-invoice/edit/:dbnotepk') }}";
    url = url.replace(':dbnotepk', row.dbnotepk);

    let baseurl = "{{ url('/') }}";

    return `<a href="${url}"><img src="${baseurl}/public/css/images/Carrot.png" width="15px" height="15px" style="cursor:pointer;"></a>`;
  }

  function sortlistByDate(value) {
    document.getElementById("sortlistByDate").value = value;

    loadDataByFilter();
  }

  function loadDataByFilter() {
    console.log(`loadDataByFilter is running`);

    var filterByMonth = $('#filterByMonth').combobox('getValue');
    var filterByYear = $('#filterByYear').combobox('getValue');
    var sortlistByDate = $('#sortlistByDate').val();

    $('#dg-cinvoice').datagrid({
      url: "{{ url('api/commercial-invoice/list') }}",
      method: 'get',
      queryParams: {
        fupkgis: fupkgis,
        guserid: guserid,
        filterByMonth: filterByMonth,
        filterByYear: filterByYear,
        sortlistByDate: sortlistByDate,
      }
    });
  }

  $(function() {
    loadDataByFilter();

    $('#filterByMonth').combobox({
      onChange: function(value){
        if ($(this).data('ignoreChange')) return false;
        loadDataByFilter();
        console.log(`filterByMonth onchange running with value: ${value}`);
      },
      onBeforeLoad: function(){
        console.log(`filterByMonth url ${$(this).combobox('options').url}`);
        const month = (new Date().getMonth() + 1).toString().padStart(2, '0');
        $(this).data('ignoreChange', true);
        $(this).combobox('setValue', month);
        $(this).data('ignoreChange', false);
      }
    })
    
    $('#filterByYear').combobox({
      onChange: function(value){
        if ($(this).data('ignoreChange')) return false;
        loadDataByFilter();
        console.log(`filterByYear onchange running with value: ${value}`);
      },
      onBeforeLoad: function(){
        console.log(`filterByYear url ${$(this).combobox('options').url}`);
        const year = new Date().getFullYear();
        $(this).data('ignoreChange', true);
        $(this).combobox('setValue', year);
        $(this).data('ignoreChange', false);
      }
    })

    $('#dg-cinvoice').datagrid({
      onLoadSuccess: function(data) {
        styleDGDbnote();
      }
    });
  })
</script>
@endsection

@section('content')
<div class="main-wrapper">
  <div class="container-fluid p-4">
    <div class="d-grid">
      <div class="p-2 pe-0">
        <span class="txt-000000-700-24-28-100">List Commercial Invoice</span>
      </div>
      <div class="d-flex">
        <div class="d-flex flex-grow-1 flex-wrap p-2 pe-0 pt-0 gap-2 align-items-center">
          <div class="p-0">
            <div class="input-group flex-nowrap input-group-search" style="height:34px;">
              <span class="input-group-text search">
                <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
              </span>
              <input type="text" class="form-control search" id="searchByInput" onkeyup="loadDataByFilter()" placeholder="Search...">
            </div>
          </div>
          <div class="p-0">
            <input id="filterByMonth" class="easyui-combobox" style="width: 100%;" method="get" data-options="valueField:'monthval',textField:'monthnm',url:'{{ url('api/utility/get-month') }}',editable:false">
          </div>
          <div class="p-0">
            <input id="filterByYear" class="easyui-combobox" style="width: 100%;" method="get" data-options="valueField:'year',textField:'year',url:'{{ url('api/master/invs/get-year') }}',editable:false">
          </div>
          @if (Session::get('guserid') == 'FU' || Session::get('guserid') == 'SPR')
            <div class="p-0">
              <a href="{{ url('commercial-invoice/store') }}" class="btn btn-dark btn-sm">&#10010; Create Commercial Invoice</a>
            </div>
          @endif
        </div>
        <div class="p-0 d-flex align-items-center">
          <div class="btn-group"> 
            <input type="hidden" id="sortlistByDate" name="sortlistByDate" value="{{11}}" size="5">
            <div class="btn-group">
              <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true"> 
                <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort"> 
                <span id="title-sortlistByDate">Tanggal (asc)</span>
              </button>
              <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByDate(11)">Tanggal (asc)</a></li>
                <li><hr class="dropdown-divider mt-0 mb-0"></li>
                <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByDate(12)">Tanggal (desc)</a></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="p-2 pe-0">
        <input type="text" id="url-dg-cinvoice" value="{{ url('api/commercial-invoice/list') }}" hidden>
        <table id="dg-cinvoice" class="easyui-datagrid" title="" style="height: auto;" align="center" toolbar="#tb" striped="false" idField="dbnotepk" pageSize="100" pageList="[100,200,300,500]" pagination="true" rownumbers="false" multiple="true" collapsible="true" data-options="multiSort:true, remoteSort:false, border:false">
          <thead>
            <tr>
              <th field="no" styler="styler1">No</th>
              <th field="tgldb" styler="styler2">Tanggal</th>
              <th field="dbno" styler="styler2" sortable="true">Debitnote No</th>
              <th field="brand" styler="styler2">Brand</th>
              <th field="alamatbill" styler="styler2" formatter="attrBillTo">Bill To</th>
              <th field="pobyr" styler="styler2" formatter="attrPobyr">SXS PO No</th>
              <th field="act" styler="styler3" formatter="attrAct"></th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection