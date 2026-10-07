@extends('layout.main')

@section('css_custom')
@endsection

@section('js_custom')
<script>
  /* Global Variable */
  var baseurl = "{{ url('/') }}";
  var guserid = "{{ Session::get('guserid') }}";
  var userpk = "{{ Session::get('userpk') }}";

  $(function() {
    window.addEventListener('resize', settingDisplay);
    
    $('.dropdown-menu-sortlist a').on('click', function(event) {
      event.preventDefault();
      
      var buttonText = $(this).text();
      var imagePath = "{{ asset('public/css/images/sort.jpg') }}";

      $('#title-sortlistByDate').text(buttonText);
    });

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
  })

  function settingDisplay() {
    $('#dg-listdb').datagrid('resize');
  }

  function styleDGListdb() {
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-body .datagrid-btable').addClass('w-100');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header .datagrid-header-inner').addClass('w-100');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header .datagrid-header-inner .datagrid-htable').addClass('w-100');

    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-body').addClass('lines-no');
    $('#dg-listdb').datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
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

  function attrKeterangan(value, row, index) {
    return `<span class="text-wrap">${row.keterangan ?? ''}</span>`;
  }

  function attrAct(value, row, index) {
    // let button = !row.dbnotepk ? `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditListdb('${row.listdbpk}')">edit</a>` : ``;
    let button = `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditListdb('${row.listdbpk}')">Edit</a>`;

    return button;
  }

  function onCreateListdb() {
    console.log(`onCreateListdb is running...`);

    $('#createOrEditListdb').modal('show');

    $('.listdb-create').removeClass('d-none');
    $('.listdb-edit').addClass('d-none');
  }

  function onEditListdb(listdbpk) {
    console.log(`onEditListdb is running...`);
    
    $('#createOrEditListdb').modal('show');
    
    $('.listdb-edit').removeClass('d-none');
    $('.listdb-create').addClass('d-none');

    let url = "{{ url('api/debitnote/detail/:listdbpk') }}";
    url = url.replace(':listdbpk', listdbpk);

    $.ajax({
      url: url,
      method: 'GET',
      success: function(response) {
        console.log(`success onEditListdb here >>>>>`);
        console.log(response);

        $('#listdbpk').val(response.data.listdb);
        $('#userr').val(response.data.user);
        $('#nomor').val(response.data.nomor);
        $('#jenis').val(response.data.jenis);
        $('#keterangan').val(response.data.keterangan);
      },
      error: function(xhr, status, error) {
        console.log(`error onEditListdb here >>>>>`);
        console.log(xhr);
      }
    })
  }

  function onCancelListdb() {
    resetListdb();
    $('#createOrEditListdb').modal('hide');
    $('#formCreateOrEditListdb').form('reset');

    if (guserid != 'FU') $('#userr').val(null);
  }

  function resetListdb() {
    $('#listdbpk').val(null);
    $('.listdb-edit').addClass('d-none');
    $('.listdb-create').addClass('d-none');
    $('#ermsg-keterangan').text(null);
    $('#ermsg-userr').text(null);
    $('#loading-edit-listdb').addClass('d-none');
    $('#loading-create-listdb').addClass('d-none');
  }

  function validateListdb() {
    console.log(`validateListdb is running...`);

    var keterangan = $('#keterangan').val();
    if (!keterangan && guserid != 'FIN' && guserid != 'SPR') $('#ermsg-keterangan').text('Keterangan wajib diisi');

    var user = $('#userr').val();
    if (!user && guserid != 'FIN' && guserid != 'SPR') $('#ermsg-userr').text('User wajib diisi')

    setTimeout(() => {
      $('#ermsg-keterangan').text(null);
      $('#ermsg-userr').text(null);
    }, 5000);
    
    if ((!keterangan || !user) && guserid != 'FIN' && guserid != 'SPR') return false;

    console.log(`validateListdb is valid`);

    return true;
  }

  function onUpdateListdb() {
    console.log(`onUpdateListdb is running...`);

    var listdbpk = $('#listdbpk').val();
    if (!listdbpk) return;

    if(!validateListdb()) return;

    $('#loading-edit-listdb').removeClass('d-none');

    let url = "{{ url('api/debitnote/update/:listdbpk') }}";
    url = url.replace(':listdbpk', listdbpk);
    console.log(`url: ${url}`);

    let formdata = new FormData();
    if ($('#jenis').length) formdata.append('jenis', $('#jenis').val());
    formdata.append('keterangan', $('#keterangan').val());
    formdata.append('user', $('#userr').val());
    if (guserid != 'SPR' && guserid != 'FIN') formdata.append('userpk', userpk);
    
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
        console.log(`success onUpdateListdb here >>>>>`);
        console.log(response);

        onCancelListdb();
        $('#dg-listdb').datagrid('reload');
      },
      error: function(xhr, status, error) {
        console.log(`error onUpdateListdb here >>>>>`);
        console.log(xhr);
      }
    });
  }

  function onSubmitListdb() {
    console.log(`onSubmitListdb is running...`);

    if(!validateListdb()) return;
    
    $('#loading-create-listdb').removeClass('d-none');

    let url = "{{ url('api/debitnote/store') }}";
    console.log(`url: ${url}`);
    
    let formdata = new FormData();
    if ($('#jenis').length) formdata.append('jenis', $('#jenis').val());
    formdata.append('keterangan', $('#keterangan').val());
    formdata.append('user', $('#userr').val());
    if (guserid != 'SPR' && guserid != 'FIN') formdata.append('userpk', userpk);
    
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
        console.log(`success onSubmitListdb here >>>>>`);
        console.log(response);

        onCancelListdb();
        $('#dg-listdb').datagrid('reload');
        $('.dt-nomor').text(response.data.nomor);
        $('#informationAlert').modal('show');
      },
      error: function(xhr, status, error) {
        console.log(`error onSubmitListdb here >>>>>`);
        console.log(xhr);
      }
    });
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

    $('#dg-listdb').datagrid({
      url: "{{ url('api/debitnote/list') }}",
      method: 'get',
      queryParams: {
        userpk: userpk,
        guserid: guserid,
        filterByMonth: filterByMonth,
        filterByYear: filterByYear,
        sortlistByDate: sortlistByDate,
      }
    });
  }

  $(function() {
    loadDataByFilter();

    $('#dg-listdb').datagrid({
      onLoadSuccess: function(data) {
        styleDGListdb();
        $(this).datagrid('resize');
      }
    });
  });
</script>
@endsection

@section('content')
<div class="main-wrapper">
  <div class="container-fluid p-4">
    <div class="d-grid">
      <div class="p-2 pe-0">
        <span class="txt-000000-700-24-28-100">List Debitnote</span>
      </div>
      <div class="d-flex flex-wrap">
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
          <div class="p-0">
            <button onclick="onCreateListdb('create')" class="btn btn-dark btn-sm">&#10010; Create Debitnote</button>
          </div>
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
      <div class="d-grid p-2 pe-0">
        <table id="dg-listdb" class="easyui-datagrid" title="" style="height: auto;" align="center" toolbar="#tb" striped="false" idField="listdbpk" pageSize="100" pageList="[100,200,300,500]" pagination="true" rownumbers="false" singleSelect="true" collapsible="true" data-options="autoRowHeight:true, fitColumns:true, multiSort:true, remoteSort:false, border:false">
          <thead>
            <tr>
              <th field="no" width="auto" styler="styler1">No</th>
              <th field="datecreate" width="auto" styler="styler2">Tanggal</th>
              <th field="dbno" width="auto" styler="styler2" sortable="true">Debitnote No</th>
              <th field="jenis" width="auto" styler="styler2">Perihal</th>
              <th field="keterangan" width="auto" styler="styler2" formatter="attrKeterangan">Keterangan</th>
              <th field="user" width="auto" styler="styler2">User</th>
              <th field="act" width="auto" styler="styler3" formatter="attrAct">Act</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="createOrEditListdb" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="createOrEditListdbLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h1 class="txt-000000-700-18-24-100" id="createOrEditListdbLabel"><span class="listdb-create d-none">Create New</span><span class="listdb-edit d-none">Edit</span> Debitnote</h1>
      </div>
      <div class="modal-body">
        <div class="container-fluid p-0">
          <div class="d-flex flex-column gap-2">
            <form id="formCreateOrEditListdb" class="d-flex flex-column gap-2">
              <input type="text" id="listdbpk" hidden>
              <div class="p-0 d-flex flex-column listdb-edit d-none">
                <label for="nomor">Nomor<span class="text-danger">*</span></label>
                <input type="number" id="nomor" class="form-control" disabled>
              </div>
              @if(Session::get('guserid') == 'FIN' || Session::get('guserid') == 'SPR')
              <div class="p-0 d-flex flex-column">
                <label for="jenis">Perihal</label>
                <input type="text" id="jenis"class="form-control">
              </div>
              @endif
              <div class="p-0 d-flex flex-column @if(Session::get('guserid') == 'FIN') d-none @endif">
                <label for="keterangan">Keterangan<span class="text-danger">*</span></label>
                <input type="text" id="keterangan"class="form-control">
                <span id="ermsg-keterangan" class="text-danger text-blink"></span>
              </div>
            </form>
            {{-- <div class="p-0 d-flex flex-column">
              <label for="karid">Nrk<span class="text-danger">*</span></label>
              <input type="text" id="karid"class="form-control">
            </div> --}}
            <div class="p-0 d-flex flex-column">
              <label for="userr">User<span class="text-danger">*</span></label>
              <input type="text" id="userr"class="form-control" value="{{ Session::get('guserid') == 'FU' ? Session::get('login') : null }}">
              <span id="ermsg-userr" class="text-danger text-blink"></span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" onclick="onCancelListdb()">Cancel</button>
        <button type="button" class="btn btn-dark listdb-create d-none" onclick="onSubmitListdb()">Submit
          <span id="loading-create-listdb" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
        </button>
        <button type="button" class="btn btn-dark listdb-edit d-none" onclick="onUpdateListdb()">Update
          <span id="loading-edit-listdb" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="informationAlert" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="informationAlertLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h1 class="txt-000000-700-18-24-100" id="informationAlertLabel">Information</h1>
      </div>
      <div class="modal-body pt-0">
        <div class="container-fluid p-0">
          <div class="d-flex flex-column gap-2">
            <span class="text-center">Berikut nomor debitnote yang Anda dapatkan</span>
            <span class="text-center dt-nomor txt-000000-700-18-24-100"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 justify-content-center">
        <button class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
@endsection