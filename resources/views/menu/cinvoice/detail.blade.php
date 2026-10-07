@extends('layout.main')

@section('css_custom')
<link href="{{ asset('public/select2/select2.min.css') }}" rel="stylesheet" />
{{-- <link href="{{ asset('public/fontawesome/all.min.css') }}" rel="stylesheet" /> --}}
<style>
  .sticky-top-custom {
    background-color: #359DD9;
    color: white;
    display: grid;
  }
</style>
@endsection

@section('js_custom')
<script src="{{ asset('public/select2/select2.min.js') }}"></script>
<script>
  /* Global Variabel */
  var company = "{{ $alamat['company'] }}"; 
  var st = "{{ $alamat['st'] }}"; 
  var city = "{{ $alamat['city'] }}"; 
  var country = "{{ $alamat['country'] }}"; 
  var telp = "{{ $alamat['telp'] }}";
  var fu = "{{ $alamat['fu'] }}";
  var dbnotepk = "{{ request()->segment(3) }}";
  var segment2 = "{{ request()->segment(2) }}";
  var mode = segment2 == 'edit' ? 'view' : 'edit';
  var userpk = "{{ Session::get('userpk') }}";

  $(function() {
    window.addEventListener('resize', adjustDisplay);

    $('#listInvs').on('shown.bs.modal', function () {
      loadDGInvsByFilter();
    })

    $('#alamat').on('input', function () {
      adjustTextareaHeight(this);
    });
    $('#alamatbyr').on('input', function () {
      adjustTextareaHeight(this);
    });
    $('#alamatbill').on('input', function () {
      adjustTextareaHeight(this);
    });
    $('#shipper').on('input', function () {
      adjustTextareaHeight(this);
    });

    $('.select2-buyer').select2({
      // minimumResultsForSearch: -1,
      dropdownParent: $('#newBill'),
      placeholder: 'Buyer',
      // allowClear: true,
      width: '100%',
      ajax: {
        url: "{{ url('api/master/byr/get-all') }}",
        dataType: 'json',
        processResults: function (data) {
          return {
            results: $.map(data, function (item) {
              return {
                id: item.byrpk,
                text: item.byrnm,
              };
            })
          };
        }
      }
    }).on('change', function (e) {
      validateNewBill();
    });

    $('.select2-bill').select2({
      // minimumResultsForSearch: -1,
      placeholder: 'Please select',
      // allowClear: true,
      width: '100%',
      ajax: {
        url: "{{ url('api/master/bill/get-all') }}",
        dataType: 'json',
        processResults: function (data) {
          return {
            results: $.map(data, function (item) {
              return {
                id: item.billpk,
                text: item.billnm,
                alamat: item.alamat,
              };
            })
          };
        }
      }
    }).on('change', function (e) {
      const selectedData = $(this).select2('data')[0];
      if (selectedData && selectedData.alamat) {
        console.log(`selectedData.alamat: ${selectedData.alamat}`);
        $('#alamatbill').val(selectedData.alamat);
      }
    }).on('select2:open', function () {
      if ($('.select2-dropdown .select2-footer').length === 0) {
        let footer = '<div class="select2-footer"><button class="border-0 bg-transparent p-0 txt-359DD9-400-14-18-100" onclick="onOpenNewBill()">+New Bill</button></div>';
        $('.select2-dropdown').append(footer);
      }
    });
  })

  function adjustTextareaHeight(el, id) {
    el.style.height = 'auto';
    el.style.height = (el.scrollHeight) + 'px';
  }

  function adjustDisplay() {
    resizeAllDG();
  }

  /* Adjust Mode edit/view */
  function adjustMode(value) {
    mode = value;
    styleDGDbnote(mode);

    if (mode == 'edit') {
      $('.dbnote-edit').removeClass('d-none');
      $('.dbnote-view').addClass('d-none');
    } else {
      $('.dbnote-edit').addClass('d-none');
      $('.dbnote-view').removeClass('d-none');
    }
    shipperAddressFormatter();
  }

  function resizeAllDG() {
    $('#dg-dbnote').datagrid('resize');
    $('#dg-invs').datagrid('resize');
  }

  function styleDGInvs(index) {
    if (index == 1) $('#dg-invs').datagrid('getPanel').find('div.datagrid-header input[type=checkbox]').css('display','');
    else $('#dg-invs').datagrid('getPanel').find('div.datagrid-header input[type=checkbox]').css('display','none');
  }

  function styleDGDbnote(mode) {
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
    // $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header').addClass('txt-000000-700-14-18-38');
    $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-body').addClass('lines-no');

    if (mode == 'view') {
      $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header input[type=checkbox]').css('display','none');
      $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-body input[type=checkbox]').css('display','none');
    } else {
      $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-header input[type=checkbox]').css('display','');
      $('#dg-dbnote').datagrid('getPanel').find('div.datagrid-body input[type=checkbox]').css('display','');
    }
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

  function onReturnBack() {
    let redirecturl = "{{ url('commercial-invoice/index') }}";

    if (segment2 == 'create') onCancelDbnote();
    if (segment2 == 'edit' && mode == 'edit' && !validateDbnote()) return;

    else window.location.href = redirecturl;
  }

  /* Start Shipper Control */
  function shipperAddressFormatter() {
    var address = `${company}\n${st}\n${city}\n${country}\n${telp}`;
    var addresshtml = `${company}<br>${st}<br>${city}<br>${country}<br>${telp}`;
    $('#shipper').val(address).trigger('input');
    $('.dt-shipper').html(addresshtml);
  }

  function getShipper() {
    console.log("Get Shipper running...");
  }
  /* End Shipper Control */

  /* Start Get Dbnote Detail */
  function getDbnote() {
    console.log(`getDbnote is running...`);

    let url = "{{ url('api/commercial-invoice/detail/:dbnotepk') }}";
    url = url.replace(':dbnotepk', dbnotepk);

    $.ajax({
      url: url,
      method: 'GET',
      success: function(response) {
        console.log(`success get detail dbnote here >>>>>>>>>>>`);
        console.log(response);
        fillDbnote(response);
      },
      error: function(xhr, status, error) {
        console.log(`error get detail dbnote here >>>>>>>>>>>`);
        console.log(xhr);
      }
    })
  }

  function fillDbnote(data) {
    console.log(`fillDbnote is running...`);
    console.log(data);

    let byrnm = data.byr ? data.byr.byrnm : null;
    let alamatbyr = data.dbnote.alamatbyr ?? null;
    if (!alamatbyr) $('#alamatbyr').val() ?? null;
    let kontakbyr = data.dbnote.kontakbyr ?? null;
    if (!kontakbyr) $('#kontakbyr').val() ?? null;
    let alamatshipto = data.shipto ? data.shipto.alamat : null;
    let kontakship = data.shipto ? data.shipto.kontakship : null;
    let billpk = data.dbnote.billpk ?? null;
    if (!billpk) billpk = $('#billpk').val();
    let billnm = data.bill ? data.bill.billnm : null;
    let alamatbill = data.dbnote.alamatbill ?? (data.bill ? data.bill.alamat : null);
    let dbno = data.dbnote.dbno ?? null;
    let dbnm = data.dbnote.dbnm ?? null;
    let tgldb = data.dbnote.tgldb ?? null;
    let sxspo = data.sxspo && data.sxspo.length > 0 ? data.sxspo : null;
    let season = data.dbnote.season ?? null;
    let brand = data.dbnote.brand ?? null;
    let paydate = data.dbnote.paydate ?? null;
    let prepareby = data.dbnote.prepareby ?? null;
    let bankcharge = data.dbnote.bankcharge ?? null;

    $('#alamatbyr').val(alamatbyr);
    $('#kontakbyr').val(kontakbyr);
    $('#alamatshipto').val(alamatshipto);
    $('#kontakship').val(kontakship);
    $('#alamatbill').val(alamatbill);
    $('#dbno').val(dbno);
    $('#dbnm').val(dbnm);
    $('#tgldb').val(tgldb);
    $('#sxspo').val(sxspo);
    $('#season').val(season);
    $('#brand').val(brand);
    $('#paydate').val(paydate);
    $('#prepareby').val(prepareby);
    $('#bankcharge').val(bankcharge);

    if (billpk) {
      var newOption = {
        id: billpk,
        text: billnm,
        alamat: alamatbill
      };

      const newOptionElement = new Option(newOption.text, newOption.id, false, false);
      $('#billpk').append(newOptionElement).trigger('change');
      $('#billpk').val(billpk).trigger('change');
    }

    

    $('.dt-byrnm').text(byrnm);
    $('.dt-alamatbyr').html(alamatbyr ? alamatbyr.replace(/\n/g, '<br>') : '');
    // $('.dt-alamatbyr').text(alamatbyr);
    $('.dt-kontakbyr').text(kontakbyr);
    $('.dt-alamatshipto').html(alamatshipto ? alamatshipto.replace(/\n/g, '<br>') : '');
    // $('.dt-alamatshipto').html(alamatshipto);
    $('.dt-kontakship').html(kontakship);
    $('.dt-alamatbill').html(alamatbill ? alamatbill.replace(/\n/g, '<br>') : '');
    // $('.dt-alamatbill').text(alamatbill);
    $('.dt-dbno').text(dbno);
    $('.dt-dbnm').text(dbnm);
    $('.dt-tgldb').text(tgldb);
    $('.dt-sxspo').text(sxspo);
    $('.dt-season').text(season);
    // $('.dt-sbu').text(null);
    $('.dt-brand').text(brand);
    $('.dt-paydate').text(paydate);
    $('.dt-prepareby').text(prepareby);
    $('.dt-bankcharge').text(bankcharge ? `$${bankcharge}` : null);

    company = data.shipper.company;
    st = data.shipper.st;
    city = data.shipper.city;
    country = data.shipper.country;
    telp = data.shipper.telp;

    shipperAddressFormatter();
  }
  /* End Get Dbnote Detail */

  /* Start New Bill */
  
  function onOpenNewBill() {
    $('.select2-bill').select2('close');
    $('#newBill').modal('show');
  }

  function onCloseNewBill() {
    $('#newBill').modal('hide');
    $('#form-newbill').form('reset');
    $('#byrpk').val(null).trigger('change');
    $('#errorNewBill').text(null);
    $('#btn-submit-newbill').addClass('disabled');
  }

  function validateNewBill() {
    console.log(`validateNewBill is running...`);

    let byrpk = $('#new-byrpk').val();
    let billnm = $('#billnm').val();
    let negara = $('#negara').val();
    let alamat = $('#alamat').val();

    if (!byrpk || !billnm || !negara || !alamat) {
      console.log(`validateNewBill is not valid`);
      $('#btn-submit-newbill').addClass('disabled');
      return false;
    }

    $('#btn-submit-newbill').removeClass('disabled');
    console.log(`validateNewBill is valid`);

    return true;
  }

  function onSubmitNewBill() {
    console.log(`onSubmitNewBill is running...`);

    let formdata = new FormData();
    formdata.append('byrpk', $('#new-byrpk').val());
    formdata.append('billnm', $('#billnm').val().trim());
    formdata.append('telepon', $('#telepon').val().trim());
    formdata.append('kontakbill', $('#kontakbill').val().trim());
    formdata.append('negara', $('#negara').val().trim());
    formdata.append('alamat', $('#alamat').val().trim());

    console.log("formdata new bill");
    for (let [key, value] of formdata.entries()) {
      console.log(`${key}: ${value}`);
    }

    let validate = validateNewBill();
    if (!validate) return;
    
    let url = "{{ url('api/master/bill/store') }}";
    console.log(`url: ${url}`);

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
        console.log(`success onSubmitNewBill here >>>>>`);
        console.log(response);

        onCloseNewBill();
      },
      error: function(xhr, status, error) {
        console.log(`error onSubmitNewBill here >>>>>`);
        console.log(xhr);

        $('#errorNewBill').text(error);
        if (xhr.responseJSON && xhr.responseJSON.message) $('#errorNewBill').text(xhr.responseJSON.message);
      }
    });
  }
  
  /* End New Bill */

  /* Start Dbnote Manipulation */
  
  /* By Choose Invs */
  function onCloseListInvs() {
    $('#listInvs').modal('hide');
    $('#dg-invs').datagrid('clearChecked');
  }

  function onOpenListInvs() {
    $('#listInvs').modal('show');
  }

  function checkSelectedInvs(isLoad) {
    var getAllInvsSelected = $('#dg-invs').datagrid('getChecked');
    var getAllRowDbnote = $('#dg-dbnote').datagrid('getRows');

    if (getAllInvsSelected.length > 0) {
      styleDGInvs(1);
      $('#btn-submit-invs').removeClass('disabled');
    } else {
      styleDGInvs(0);
      if (getAllRowDbnote.length < 1) $('#byrpk').val(null);
      $('#btn-submit-invs').addClass('disabled');
      if (isLoad) loadDGInvsByFilter();
    }
  }

  function onSubmitListInvs() {
    console.log(`onSubmitListInvs is running...`);
    
    let getAllInvsSelected = $('#dg-invs').datagrid('getChecked');
    console.log(getAllInvsSelected);

    let url = "{{ url('api/commercial-invoice/update/:dbnotepk') }}";
    url = url.replace(':dbnotepk', dbnotepk);
    console.log(`url: ${url}`);

    $.ajax({
      url: url,
      method: 'POST',
      data: { dtinvs: JSON.stringify(getAllInvsSelected) },
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        console.log(`success onSubmitListInvs here >>>>>`);
        console.log(response);

        getDbnote();
        $('#listInvs').modal('hide');
        $('#dg-dbnote').datagrid('reload');
      },
      error: function(xhr, status, error) {
        console.log(`error onSubmitListInvs here >>>>>`);
        console.log(xhr);


        // $('#errorNewBill').text(error);
        // if (xhr.responseJSON && xhr.responseJSON.message) $('#errorNewBill').text(xhr.responseJSON.message);
      }
    });
  }

  function loadDGInvsByFilter() {
    console.log(`loadDGInvsByFilter is running...`);

    var filterByByrpk = $('#byrpk').val();
    var searchByInput = $('#searchByInput').val();
    console.log(`filterByByrpk : ${filterByByrpk}`);

    $('#dg-invs').datagrid({
      url: "{{ url('api/master/invs/get-all') }}",
      method: 'get',
      queryParams: {
        filterByByrpk : filterByByrpk,
        searchByInput : searchByInput,
        isNullDbnotepk : true,
      }
    });
  }

  /* By Primary Data */
  function setDbnm() {
    // var season = 
  }

  function checkSelectedDbnote() {
    var getAllDbnoteChecked = $('#dg-dbnote').datagrid('getChecked');

    if (getAllDbnoteChecked.length > 0) {
      $('#displayCheckedCount').text(getAllDbnoteChecked.length);
      $('#stickTopBar').show();
    } else {
      $('#displayCheckedCount').text(null);
      $('#stickTopBar').hide();
    }
  }

  function onEditDbnote() {
    adjustMode('edit');
    styleDGDbnote('edit');
  }

  function onCancelDbnote() {
    if (segment2 == 'edit' && !validateDbnote()) return;

    adjustMode('view');
    styleDGDbnote('view');

    if (segment2 == 'edit') return;

    let redirecturl = "{{ url('commercial-invoice/index') }}";

    let url = "{{ url('api/commercial-invoice/destroy/:dbnotepk') }}";
    url = url.replace(':dbnotepk', dbnotepk);

    $.ajax({
      url: url,
      method: 'GET',
      success: function(response) {
        console.log(`success onCancelDbnote here >>>>>>>>>>>`);
        if (segment2 = 'create') window.location.href = redirecturl;
      },
      error: function(xhr, status, error) {
        console.log(`error onCancelDbnote here >>>>>>>>>>>`);
      },
    });
  }

  function resetErMsgDbnote() {
    $('#ermsg-alamatbyr').text(null);
    $('#ermsg-kontakbyr').text(null);
    $('#ermsg-billpk').text(null);
    $('#ermsg-dbno').text(null);
    $('#ermsg-tgldb').text(null);
    $('#ermsg-season').text(null);
    $('#ermsg-brand').text(null);
    $('#ermsg-dbnote').text(null);
  }

  function validateDbnote() {
    console.log(`validateDbnote is running...`);

    var alamatbyr = $('#alamatbyr').val();
    if (!alamatbyr) $('#ermsg-alamatbyr').text('Alamat buyer wajib diisi');

    var kontakbyr = $('#kontakbyr').val();
    if (!kontakbyr) $('#ermsg-kontakbyr').text('Kontak buyer wajib diisi');

    var billpk = $('#billpk').val();
    if (!billpk) $('#ermsg-billpk').text('Bill wajib dipilih');

    var alamatbill = $('#alamatbill').val();
    if (!alamatbill) $('#ermsg-billpk').text('Alamat Bill wajib diisi');

    var dbno = $('#dbno').val();
    if (!dbno) $('#ermsg-dbno').text('Commercial Invoice wajib diisi');

    var tgldb = $('#tgldb').val();
    if (!tgldb) $('#ermsg-tgldb').text('Tanggal wajib diisi');

    var season = $('#season').val();
    if (!season) $('#ermsg-season').text('Season wajib diisi');

    var brand = $('#brand').val();
    if (!brand) $('#ermsg-brand').text('Brand wajib diisi');

    var paydate = $('#paydate').val();
    var prepareby = $('#prepareby').val();

    var allRows = $('#dg-dbnote').datagrid('getRows').length;
    if (!allRows) $('#ermsg-dbnote').text('Masukkan minimal 1 invoice');
    

    if (!alamatbyr||!kontakbyr||!billpk||!alamatbill||!dbno||!tgldb||!season||!brand) {
      console.log(`validateDbnote is not valid`);
      setTimeout(() => {
        resetErMsgDbnote()
      }, 5000);
      return false;
    }
    console.log(`validateDbnote is valid`);
    return true;
  }

  function onSubmitDbnote() {
    console.log(`onSubmitDbnote is running...`);

    if (!validateDbnote()) return false;

    let formdata = new FormData();
    formdata.append('alamatbyr', $('#alamatbyr').val());
    formdata.append('kontakbyr', $('#kontakbyr').val());
    formdata.append('billpk', $('#billpk').val());
    formdata.append('alamatbill', $('#alamatbill').val());
    formdata.append('dbno', $('#dbno').val());
    formdata.append('tgldb', $('#tgldb').val());
    formdata.append('season', $('#season').val());
    formdata.append('brand', $('#brand').val());
    formdata.append('paydate', $('#paydate').val());
    formdata.append('prepareby', $('#prepareby').val());
    formdata.append('bankcharge', $('#bankcharge').val() ?? null);
    formdata.append('sxspo', $('#sxspo').val());
    formdata.append('userpk', userpk);

    let redirecturl = "{{ url('commercial-invoice/edit/:dbnotepk') }}";
    redirecturl = redirecturl.replace(':dbnotepk', dbnotepk);

    let url = "{{ url('api/commercial-invoice/update/:dbnotepk') }}";
    url = url.replace(':dbnotepk', dbnotepk);
    console.log(`url: ${url}`);

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
        console.log(`success onSubmitDbnote here >>>>>`);
        console.log(response);

        getDbnote();
        adjustMode('view');
        $('#dg-dbnote').datagrid('reload');
        if (segment2 == 'create') window.location.href = redirecturl;
      },
      error: function(xhr, status, error) {
        console.log(`error onSubmitDbnote here >>>>>`);
        console.log(xhr);

        // $('#errorNewBill').text(error);
        // if (xhr.responseJSON && xhr.responseJSON.message) $('#errorNewBill').text(xhr.responseJSON.message);
      }
    });
  }

  function onRemoveDbnote() {
    console.log(`onRemoveDbnote is running...`);

    var getAllDbnoteChecked = $('#dg-dbnote').datagrid('getChecked');
    console.log(getAllDbnoteChecked);

    let url = "{{ url('api/commercial-invoice/remove/:dbnotepk') }}";
    url = url.replace(':dbnotepk', dbnotepk);

    $.ajax({
      url: url,
      method: 'POST',
      data: { dtinvs: getAllDbnoteChecked },
      success: function(response) {
        console.log(`success remove dbnote here >>>>>>>>>>>>`);
        console.log(response);

        getDbnote();
        $('#stickTopBar').hide();
        $('#dg-dbnote').datagrid('reload');
      },
      error: function(xhr, status, error) {
        console.log(`error remove dbnote here >>>>>>>>>>>>`);
        console.log(xhr);
      }
    });
  }

  function onPDF() {
    $('#konfirmasiJudul').modal('show');
  }

  function onSimpanPDF() {
    let dbnotepk = "{{ request()->segment(3) }}";
    let judul = document.querySelector('input[name="judul"]:checked').value;
    let judulinv = document.querySelector('input[name="judulinv"]:checked').value;

    console.log(`dbnotepk : ${dbnotepk}`);
    console.log(judul);
    console.log(judulinv);

    let url = "{{ route('commercial-invoice.pdf', ['dbnotepk' => request()->segment(3)]) }}"
      + "?judul=" + encodeURIComponent(judul)
      + "&judulinv=" + encodeURIComponent(judulinv);

    window.open(url);
  }

  /* End Dbnote Manipulation */

  $(function() {
    $('#dg-invs').datagrid({
      onLoadSuccess: function(data) {
        checkSelectedInvs(false);
      },
      onCheckAll: function(rows) {
        console.log("onCheckAll dg-invs >>>>>>>>>>>>>>>>CHECKED>>>>>>>>>>>>>>>>");
        checkSelectedInvs(false);
      },
      onUncheckAll: function(rows) {
        console.log("onUncheckAll dg-invs >>>>>>>>>>>>>>>>CHECKED>>>>>>>>>>>>>>>>");
        checkSelectedInvs(true);
      },
      onCheck: function(index, row) {
        console.log("onCheck dg-invs >>>>>>>>>>>>>>>>CHECKED>>>>>>>>>>>>>>>>");
        console.log(`invspk: ${row.invspk}`);
        console.log(row);
        checkSelectedInvs(false);
        var filterByByrpk = $('#byrpk').val();
        if (!filterByByrpk) {
          $('#byrpk').val(row.byrpk);
          loadDGInvsByFilter();
        }
      },
      onUncheck: async function(index, row) {
        console.log("onUncheck dg-invs >>>>>>>>>>>>>>>>CHECKED>>>>>>>>>>>>>>>>");
        console.log(`invspk: ${row.invspk}`);
        checkSelectedInvs(true);
      },
    });

    $('#dg-dbnote').datagrid({
      onCheckAll: function(index, row) {
        checkSelectedDbnote();
      },
      onUncheckAll: function(index, row) {
        checkSelectedDbnote();
      },
      onCheck: function(index, row) {
        checkSelectedDbnote();
      },
      onUncheck: async function(index, row) {
        checkSelectedDbnote();
      },
      onLoadSuccess: function(data) {
        console.log(`onLoadSuccess dg-dbnnote here >>>>>`);
        styleDGDbnote(mode);

        if (data.total > 0) $('#wrap-dg-dbnote').removeClass('d-none');
        else $('#wrap-dg-dbnote').addClass('d-none');

        $(this).datagrid('resize');
      },
    });
  })

  $(function() {
    adjustMode(mode);
    getDbnote();
  })
</script>
@endsection

@section('content')
<input type="text" id="byrpk" value="{{ $byrpk ?? null }}" hidden>
<input type="text" id="listdbpk" hidden>

<div id="stickTopBar" class="sticky-md-top sticky-top-custom px-5" tabindex="1000" style="display:none; min-height:48px;">
  <div class="d-flex">
    <div class="me-auto pl-custom p-2"><span id="displayCheckedCount" style="font-weight:700;"></span> <span style="font-weight:700;">item terpilih</span></div>
    <div class="p-2"><a onclick="onRemoveDbnote()" style="cursor: pointer; font-weight:700;">Delete</a></div>
    <div class="p-2"><a onclick="$('#stickTopBar').hide(); $('#dg-dbnote').datagrid('clearChecked');" style="cursor: pointer; font-weight:700;">Tutup Menu</a></div>
  </div>
</div>

<div class="main-wrapper">
  <div class="container-fluid p-4">
    <div class="d-grid m-3 mt-0">
      <div class="d-flex align-items-center p-2 flex-wrap">
        <div class="flex-grow-1 align-items-center d-flex">
          <button type="button" class="btn border-0 p-2" onclick="onReturnBack()">
            <img src="{{ asset('public/css/images/arrow-back.png') }}" alt=""> 
          </button>
          <span class="txt-000000-500-24-18-100">Back</span>
        </div>
        <div class="p-0 d-flex gap-2 flex-wrap">
          <button class="btn btn-sm btn-light px-5 dbnote-edit d-none" onclick="onCancelDbnote()">Cancel</button>
          <button class="btn btn-sm btn-dark px-5 dbnote-edit d-none" onclick="onSubmitDbnote()">Submit</button>
          @if (Session::get('guserid') == 'FU' || Session::get('guserid') == 'SPR')
          <button class="btn btn-sm btn-dark px-5 dbnote-view d-none" onclick="onEditDbnote()">Edit</button>
          @endif
          <a class="btn btn-sm btn-dark px-5 dbnote-view d-none" href="javascript:void(0)" onclick="onPDF()">Pdf</a>
        </div>
      </div>
      <div class="d-flex justify-content-end pb-2">
        <span id="ermsg-dbnote" class="text-danger text-blink"></span>
      </div>
      <div class="d-grid ps-5">
        <div class="d-flex flex-wrap d-none dbnote-edit" style="font-size: 15px;">
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 pe-3" style="flex-basis: 55%;">
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BUYER : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-byrnm"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <textarea id="alamatbyr" class="form-control overflow-hidden"></textarea>
                <span id="ermsg-alamatbyr" class="text-danger text-blink"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">CONTACT : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="kontakbyr" type="text" class="form-control">
                <span id="ermsg-kontakbyr" class="text-danger text-blink"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SHIP TO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <textarea id="alamatshipto" type="text" class="form-control" disabled></textarea>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">CONTACT : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <input id="kontakship" type="text" class="form-control" disabled>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BILL TO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <select id="billpk" class="select2-bill"></select>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <textarea id="alamatbill" class="form-control overflow-hidden" disabled></textarea>
                <span id="ermsg-billpk" class="text-danger text-blink"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SHIPPER : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <textarea id="shipper" class="form-control overflow-hidden" disabled></textarea>
              </div>
            </div>
          </div>
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 ps-3" style="flex-basis: 45%;">
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">COMMERCIAL<br>INVOICE NO : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="dbno" type="text" class="form-control" disabled>
                <span id="ermsg-dbno" class="text-danger text-blink"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">DATE : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="tgldb" type="date" class="form-control" value="{{ $dbnote->tgldb }}">
                <span id="ermsg-tgldb" class="text-danger text-blink"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SXS PO NO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <input id="sxspo" type="text" class="form-control">
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SEASON : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="season" type="text" class="form-control" onkeyup="setDbnm()">
                <span id="ermsg-season" class="text-danger text-blink"></span>
              </div>
            </div>
            {{-- <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SBU : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <input type="text" class="form-control">
              </div>
            </div> --}}
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BRAND : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="brand" type="text" class="form-control" onkeyup="setDbnm()">
                <span id="ermsg-brand" class="text-danger text-blink"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0 d-grid" style="flex-basis: 26%;">
                <span class="text-nowrap">PAYMENT<br>DUE DATE : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <input id="paydate" type="date" class="form-control">
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">PREPARED BY : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="prepareby" type="text" class="form-control">
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BANK CHARGE : </span>
              </div>
              <div class="flex-grow-0 d-grid" style="flex-basis: 74%;">
                <input id="bankcharge" type="number" class="form-control">
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex flex-wrap d-none dbnote-view" style="font-size: 15px;">
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 pe-3" style="flex-basis: 55%;">
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BUYER : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-alamatbyr" style="word-break: break-word;"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">CONTACT : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-kontakbyr"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SHIP TO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-alamatshipto" style="word-break: break-word;"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">CONTACT : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-kontakship"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BILL TO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-alamatbill" style="word-break: break-word;"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SHIPPER : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-shipper" style="word-break: break-word;"></span>
              </div>
            </div>
          </div>
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 ps-3" style="flex-basis: 45%;">
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">COMMERCIAL<br>INVOICE NO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-dbno"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">DATE : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-tgldb"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SXS PO NO : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-sxspo" style="word-break: break-word;"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SEASON : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-season"></span>
              </div>
            </div>
            {{-- <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">SBU : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-sbu"></span>
              </div>
            </div> --}}
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BRAND : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-brand"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">PAYMENT<br>DUE DATE : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-paydate"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">PREPARED BY : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-prepareby"></span>
              </div>
            </div>
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">BANK CHARGE : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <span class="dt-bankcharge"></span>
              </div>
            </div>
          </div>
        </div>
        <div class="d-flex flex-wrap pt-5" style="font-size: 15px;">
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 pe-3" style="flex-basis: 55%;">
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap"></span>
              </div>
              <div class="flex-grow-0 dbnote-edit d-none" style="flex-basis: 74%;">
                <input id="dbnm" class="form-control" type="text">
              </div>
              <div class="flex-grow-0 dbnote-view d-none card text-center" style="flex-basis: 74%;">
                <span class="card-body p-2 dt-dbnm"></span>
              </div>
            </div>
          </div>
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 pe-3 pt-3 dbnote-edit" style="flex-basis: 55%;">
            <div class="d-flex flex-wrap align-items-start">
              <div class="pe-2 flex-grow-0" style="flex-basis: 26%;">
                <span class="text-nowrap">List Invoice : </span>
              </div>
              <div class="flex-grow-0" style="flex-basis: 74%;">
                <button onclick="onOpenListInvs()" class="btn btn-sm btn-dark dbnote-edit d-none">Lookup Invoice</button>
              </div>
            </div>
          </div>
          <div class="d-flex flex-column gap-2 flex-grow-0 p-1 ps-3" style="flex-basis: 45%;">
          </div>
        </div>
        <div id="wrap-dg-dbnote" class="p-2 d-grid d-none">
          <table id="dg-dbnote" class="easyui-datagrid" title="" style="width:auto;" align="center" toolbar="#tb" striped="false" idField="invsdtpk" pagination="true" rownumbers="false" multiple="true" collapsible="true" method="get" url="{{ url('api/commercial-invoice/list-detail/' . request()->segment(3)) }}" data-options="multiSort:true, remoteSort:false, border:false, checkOnSelect:false">
            <thead>
              <tr>
                <th field="ck" width="auto" styler="styler1" checkbox="true"></th>
                {{-- <th field="no" width="auto" styler="styler2">No</th> --}}
                <th field="noinvs" width="auto" styler="styler2">No Invoice</th>
                <th field="po" width="auto" styler="styler2">PO</th>
                <th field="style" width="auto" styler="styler2">Style</th>
                <th field="color" width="auto" styler="styler2">Color</th>
                <th field="desc" width="auto" styler="styler2">Desc</th>
                <th field="product" width="auto" styler="styler2">Product</th>
                <th field="destination" width="auto" styler="styler2">Destination</th>
                <th field="senddate" width="auto" styler="styler2">Send Date</th>
                <th field="qty" width="auto" styler="styler2">Qty</th>
                <th field="unit" width="auto" styler="styler2">Unit</th>
                <th field="price" width="auto" styler="styler2">Unit Price<br>US$</th>
                <th field="amount" width="auto" styler="styler2">Total Amount<br>US$</th>
                <th field="shipno" width="auto" styler="styler3">AWB SHIPPING #</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="listInvs" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="listInvsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h1 class="modal-title fs-5" id="listInvsLabel">List Invoice</h1>
        <button type="button" class="btn-close" onclick="onCloseListInvs()"></button>
      </div>
      <div class="modal-body scrollbar-grey">
        <div class="d-flex flex-column gap-2">
          <div class="p-0">
            <div class="input-group" style="height:34px;">
              <span class="input-group-text search">
                <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
              </span>
              <input type="text" class="form-control search" id="searchByInput" placeholder="Search..." onkeyup="loadDGInvsByFilter()">
            </div>
          </div>
          <div class="p-0 pt-2 d-grid">
            <table id="dg-invs" class="easyui-datagrid" title="" style="width:auto;" align="center" toolbar="#tb" striped="false" idField="invspk" pagination="false" rownumbers="false" multiple="true" collapsible="true" data-options="multiSort:true, remoteSort:false, border:false">
              <thead>
                <tr>
                  <th field="ck" width="auto" checkbox="true"></th>
                  <th field="tglinvs" width="auto">Tanggal</th>
                  <th field="noinvs" width="auto">Invoice No</th>
                  <th field="byrnm" width="auto">Buyer</th>
                  <th field="shipno" width="auto">Shipment No</th>
                  <th field="shiptonm" width="auto">Ship To</th>
                  <th field="tqty" width="auto">Qty</th>
                </tr>
              </thead>
            </table>
          </div>
          <div class="p-0">
            <span id="errorTemplateSR" class="text-danger"></span>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button id="btn-submit-invs" class="btn btn-dark disabled" onclick="onSubmitListInvs()">
          <span>Get</span>
          <span id="loading-submit-invs" class="spinner-grow spinner-grow-sm d-none" aria-hidden="true"></span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="newBill" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newBillLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title">New Bill Information</h5>
        <button type="button" class="btn-close" onclick="onCloseNewBill()"></button>
      </div>
      <div class="modal-body">
        <div class="d-grid">
          <div class="p-0 d-flex txt-000000-400-16-20-38">
            Silahkan lengkapi data dibawah ini
          </div>
          <form id="form-newbill">
            <div class="mt-3">
              <select id="new-byrpk" class="select2-buyer"></select>
            </div>
            <div class="form-floating mt-3">
              <input type="text" class="form-control" id="billnm" onkeyup="validateNewBill()" placeholder="" aria-required="true" autocomplete="true">
              <label for="billnm">Bill name</label>
            </div>
            <div class="form-floating mt-3">
              <input type="text" class="form-control" id="telepon" onkeyup="validateNewBill()" placeholder="" aria-required="true" autocomplete="true">
              <label for="telepon">Telp</label>
            </div>
            <div class="form-floating mt-3">
              <input type="text" class="form-control" id="kontakbill" onkeyup="validateNewBill()" placeholder="" aria-required="true" autocomplete="true">
              <label for="kontakbill">Contact name</label>
            </div>
            <div class="form-floating mt-3">
              <input type="text" class="form-control" id="negara" onkeyup="validateNewBill()" placeholder="" aria-required="true" autocomplete="true">
              <label for="negara">Country</label>
            </div>
            <div class="form-floating mt-3">
              <textarea class="form-control overflow-hidden" placeholder="" id="alamat" aria-required="true" onkeyup="validateNewBill()"></textarea>
              <label for="alamat">Address</label>
            </div>
          </form>
          <div>
            <span id="errorNewBill" class="text-danger"></span>
          </div>
        </div>
        <div class="p-0 d-flex justify-content-end pt-4 gap-2">
          <button id="btn-submit-newbill" class="btn btn-dark disabled" onclick="onSubmitNewBill()">Submit</button>
        </div>
      </div>
      <div class="modal-footer border-0 p-0">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="konfirmasiJudul" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="konfirmasiJudulLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex gap-2">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="judul" id="ci" value="COMMERCIAL INVOICE" checked>
            <label class="form-check-label" for="ci">COMMERCIAL INVOICE</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="judul" id="dbnote" value="DEBIT NOTE">
            <label class="form-check-label" for="dbnote">DEBIT NOTE</label>
          </div>
        </div>
        <div class="d-flex gap-2">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="judulinv" id="noci" value="INVOICE NO" checked>
            <label class="form-check-label" for="ci">INVOICE NO</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="judulinv" id="dbnote" value="DEBIT NOTE NO">
            <label class="form-check-label" for="nodbnote">DEBIT NOTE NO</label>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 p-2">
        <button class="btn btn-dark btn-sm" onclick="onSimpanPDF()">Simpan Format</button>
      </div>
    </div>
  </div>
</div>
@endsection