@extends('layout.main')

@section('css_custom')
<style>
  .button-add {
    justify-content: center;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    border-radius: 8px;
    background: #000;
    color: #FFF;
    height: 30px;
  }

  #noinv {
    width: 40%;
  }

  #dynamic-year {
    width: 60%;
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
</style>
@endsection

@section('js_custom')
<script src="{{ (asset('public/assets/easy-number-separator.js' )) }}"></script>
<script>
  $(function() {
    $('#dg-spb-shadow').datagrid('getPanel').find('div.datagrid-view').css('display', 'none');
    $('#dg-spb-shadow').datagrid('getPanel').find('div.datagrid-pager').css('display', 'none');

    var dgspb = $('#dg-spb').datagrid();
    dgspb.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
    dgspb.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
    dgspb.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
    dgspb.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
    dgspb.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
    dgspb.datagrid('getPanel').css('border', 'none');
    dgspb.datagrid('getPanel').addClass('lines-no');
    dgspb.datagrid('getPanel').addClass('lines-no3');

    $('#dg-spb-mobile').datagrid('getPanel').find('div.datagrid-header').css('visibility', 'hidden');
    $('#dg-spb-mobile').datagrid('getPanel').find('div.datagrid-header').addClass('h-0');
    $('#dg-spb-mobile').datagrid('getPanel').css('border', 'none');
    $('#dg-spb-mobile').datagrid('getPanel').addClass('lines-no');
    $('#dg-spb-mobile').datagrid('getPanel').addClass('lines-no3');
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
      @if(Session::get('guserpk') == '7')
      '<li><a class="dropdown-item dropdown-item-custom" id="btn-create-' + (row.index || '') + '" onclick="CreateInv(' + (row.spbpk || 'null') + ');" style="color:#359DD9;">Create Invoice</a></li>' +

      // hr +
      // '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-' + (row.index || '') + '" onclick="KlikEditBc(' + (row.spbpk || 'null') + ');" style="color:#359DD9;">Edit PDF Beacukai</a></li>' +
      hr +
      @endif
      '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-' + (row.index || '') + '" onclick="KlikDetail(' + (row.spbpk || 'null') + ');" style="color:#359DD9;">Detail SPB</a></li>' +
      // '<li><a class="dropdown-item disabled">Detail SPB</a></li>' +
      // hr +
      // '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-' + (row.index || '') + '" onclick="KlikPrintSpb(' + (row.spbpk || 'null') + ');" style="color:#359DD9;">Print SPB</a></li>' +
      hr + '<li><a class="dropdown-item dropdown-item-custom" id="btn-print-' + (row.index || '') + '" onclick="KlikPrint(' + (row.spbpk || 'null') + ')" style="color:#359DD9;">Print SPB</a></li>' +
      '</ul>' +
      '</div>';
  }

  function loadDataByFilter() {
    console.log(`loadDataByFilter RUNNING >>>>>>>>>>>>>>`);
    var searchByInput = $('#searchByInput').val();
    var filterByYear = $('#filterByYear').val();
    var filterByMonth = $('#filterByMonth').val();
    var sortlistByDate = $('#sortlistByDate').val();

    console.log(`searchByInput : ${searchByInput}`);
    $('#dg-spb-shadow').datagrid('load', {
      searchByInput: searchByInput,
      filterByYear: filterByYear,
      filterByMonth: filterByMonth,
      sortlistByDate: sortlistByDate,
    });

    $('#dg-spb').datagrid('load', {
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
    // const baseUrl = "http://192.168.0.22/SPB/";
    const baseUrl = "{{ url('/')}}";
    // window.open(baseUrl + "/detail-spb/" + spbpk);
    window.location.href = baseUrl + "/detail-spb/" + spbpk;
  }

  function CreateInv(spbpk) {
    $.ajax({
      url: '/SPB/api/get-spb/' + spbpk,
      method: 'GET',
      success: function(response) {
        console.log("Response received:", response);
        if (response.success && response.data.length > 0) {
          const userDetails = response.data[0];
          console.log("User details:", userDetails);
          $('#spbpk').val(userDetails.spbpk || '');
          $('#tgl').val(userDetails.tgl || '');
          $('#totsatuan').val(userDetails.totsatuan || '');
          $('#nobukti').val(userDetails.nobukti || '');
          // $('#priceppn').val(userDetails.priceppn || '');
          $('#pricemat').val(userDetails.pricemat || '');
          $('#cur').val(userDetails.cur || '');
          $('#noinv').val(userDetails.noinv || '');
          const tgl = userDetails.tgl || ''; // Contoh: "2023-12-04"
          const year = tgl ? new Date(tgl).getFullYear() : new Date().getFullYear(); // Ambil tahun dari tgl atau gunakan tahun sekarang
          const noinv = userDetails.noinv || ''; // Contoh: "0061/AKB/MIF/2025"
          const splitNoInv = noinv.split('/'); // Pisahkan berdasarkan "/"
          const noinvPrefix = splitNoInv[0] || ''; // Contoh: "0061"
          const noinvSuffix = '/AKB/MIF/' + year; // Hasilkan bagian "/AKB/MIF/{year}"

          // if (response.data2.length > 0) {
          //   const pricematDetails = response.data2[0];
          //   $('#pricemat').val(pricematDetails.pricemat || '');
          // } else {
          //   $('#pricemat').val(''); // Kosongkan jika tidak ada data
          // }
          // $('#pricemat').prop('disabled', false);

          $('#noinv').val(noinvPrefix);
          $('#dynamic-year').val(noinvSuffix);
          $('#ModalCreateInv').modal('show');
        } else {
          alert('Data user tidak ditemukan');
        }
      },
      error: function(error) {
        console.error("Gagal mengambil data pengguna:", error);
        alert('Terjadi kesalahan saat mengambil data');
      }
    });
  }

  function onSubmit() {
    const currency = $('#cur').val();
    // const price = $('#pricemat').val().trim();

    // if (currency === "" || price === "") {
    if (currency === "") {
      showAlert('error', 'Harap pilih currency wajib.');
      return;
    }

    const noinvPrefix = $('#noinv').val(); // Nomor awal (contoh: "0061")
    const noinvYear = $('#dynamic-year').val().split('/')[3]; // Tahun diambil dari "/AKB/MIF/2025"
    const noinvSuffix = '/AKB/MIF/' + noinvYear; // Format lengkap: "/AKB/MIF/2025"

    // Gabungkan nomor dan format lengkap
    const fullNoInv = noinvPrefix + noinvSuffix;

    var formdata = new FormData();
    formdata.append('spbpk', $('#spbpk').val()); // Ambil value dari input text dengan id spbpk
    formdata.append('tgl', $('#tgl').val());
    formdata.append('totsatuan', $('#totsatuan').val());
    formdata.append('noinv', fullNoInv); // Kirimkan fullNoInv
    formdata.append('pricemat', $('#pricemat').val());
    formdata.append('cur', $('#cur').val());
    console.log("FormData to save user >>>>>>>>>>>>>>>>>>>>>");
    for (let pair of formdata.entries()) {
      console.log(pair[0] + ': ' + pair[1]);
    }

    let url = "{{ route('post.create-inv') }}";
    console.log("URL save user: " + url);

    $.ajax({
      url: url,
      method: 'POST',
      data: formdata,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response, textStatus, xhr) {
        console.log("Success saving user >>>>>>>>>>>>>>>>>>>");
        console.log(response);
        const statusCode = xhr.status; // Ambil status HTTP untuk debugging
        console.log('INI LIHAT : ' + JSON.stringify(response));

        if (response.success) {
          showAlert(200, response.message);
          $('#ModalCreateInv').modal('hide');
          setTimeout(function() {
            location.reload();
          }, 2000);
        } else {
          showAlert('error', response.message || 'Gagal memperbarui data.');
        }
      },
      error: function(xhr, status, error) {
        console.error("Error saving user >>>>>>>>>>>>>>>>>>>");
        console.error("Status:", status);
        console.error("Error:", error);
        console.error("Response:", xhr.responseText);
        showAlert('error', 'Terjadi kesalahan saat menyimpan data.');
      }
    });
    // untuk botton save jadi disabled
      function validateFormFields() {
        const currency = document.getElementById('cur').value;
        // const price = document.getElementById('pricemat').value.trim();
        const saveButton = document.getElementById('btn-save');

        if (currency !== "") {
          saveButton.disabled = false;
        } else {
          saveButton.disabled = true;
        }
      }

      // Pasang event listener
      document.getElementById('cur').addEventListener('change', validateFormFields);
      // document.getElementById('pricemat').addEventListener('input', validateFormFields);
    // tutup button disabled
  }

  function KlikEditBc(spbpk) {
    $.ajax({
      url: '/SPB/api/get-spb/doc-bc/' + spbpk,
      method: 'GET',
      success: function(response) {
        console.log("Response received:", response);
        console.log("No Bukti:", response.data ? response.data.nobukti : "Tidak ada");

        if (response.success) {
          if (response.data) {
            $('#spbpkbc').val(response.data.spbpk || '');
            $('#nobuktibc').val(response.data.nobukti || '');
            $('#noinvbc').val(response.data.noinv || '');
          }
          if (response.data2) {
            $('#prodes').val(response.data2.prodes || '');
            $('#buyer').val(response.data2.buyer || '');
          }
          $('#ModalEditBc').modal('show');
        } else {
          alert('Data tidak ditemukan');
        }
      },
      error: function(error) {
        console.error("Gagal mengambil data pengguna:", error);
        alert('Terjadi kesalahan saat mengambil data');
      }
    });
  }


  function onSubmitEditBc() {
    var formdata = new FormData();
    formdata.append('spbpk', $('#spbpkbc').val());
    formdata.append('prodes', $('#prodes').val());
    formdata.append('buyer', $('#buyer').val());

    console.log("FormData to save data >>>>>>>>>>>>>>>>>>>>>");
    for (let pair of formdata.entries()) {
      console.log(pair[0] + ': ' + pair[1]);
    }

    let url = "{{ route('post.update-doc-bc') }}";
    console.log("URL save user: " + url);

    $.ajax({
      url: url,
      method: 'POST',
      data: formdata,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response, textStatus, xhr) {
        console.log("Success saving data >>>>>>>>>>>>>>>>>>>");
        console.log(response);
        const statusCode = xhr.status;
        console.log('INI LIHAT : ' + JSON.stringify(response));

        if (response.success) {
          showAlert(200, response.message);
          $('#ModalEditBc').modal('hide');
          setTimeout(function() {
            location.reload();
          }, 2000);
        } else {
          showAlert('error', response.message || 'Gagal memperbarui data.');
        }
      },
      error: function(xhr, status, error) {
        console.error("Error saving data >>>>>>>>>>>>>>>>>>>");
        console.error("Status:", status);
        console.error("Error:", error);
        console.error("Response:", xhr.responseText);
        showAlert('error', 'Terjadi kesalahan saat menyimpan data.');
      }
    });
  }


  function CancelModalBc() {
    $('#ModalEditBc').modal('hide');
  }

  function CancelModalCreateInv() {
    $('#ModalCreateInv').modal('hide');
  }

  $(function() {
    $('#dg-spb-shadow').datagrid({
      onLoadSuccess: function(data) {
        settingTableHeight(data.total);
      },
    })
  })

  function settingTableHeight(length) {
    var element = document.querySelector('.navbar');
    var computedStyle = window.getComputedStyle(element);
    var height = parseInt(computedStyle.getPropertyValue('height'));

    var screenWidth = window.innerWidth;
    var windowHeight = window.innerHeight - height * 5;

    console.log('height:', height);
    console.log('windowHeight:', windowHeight);

    if (length <= 6) {
      console.log("here <= 6");
      $('#dg-spb').datagrid('getPanel').find('div.datagrid-view2').css('height', windowHeight);
      $('#dg-spb-mobile').datagrid('getPanel').find('div.datagrid-view2').css('height', windowHeight);
    } else {
      console.log("here auto");
      $('#dg-spb').datagrid('getPanel').find('div.datagrid-view2').css('height', 'auto');
      $('#dg-spb-mobile').datagrid('getPanel').find('div.datagrid-view2').css('height', 'auto');
    }
  }

  function AddSpb() {
    $.ajax({
      type: "POST",
      url: "{{ route('get.lastspb') }}",
      data: {
        _token: $('meta[name="csrf-token"]').attr('content')
      },
      success: function(data) {
        console.log(data);
        // const baseUrl = "http://192.168.0.22/SPB/";
        const baseUrl = "{{ url('/')}}";
        window.location.href = baseUrl + "/add-spb/" + data.spbpk;
      },
      error: function(error) {
        console.log(error);
      }
    });
  }

  function KlikPrintSpb(spbpk) {
    const baseUrl = "{{ url('/')}}";
    window.open(baseUrl + "/print-spb/" + spbpk, "_blank");
    // window.open = baseUrl + "/print-spb/" + spbpk;
  }

  function KlikPrint(spbpk) {
    $.ajax({
      url: '/SPB/api/get-spb/' + spbpk,
      method: 'GET',
      success: function(response) {
        console.log("Response received:", response);
        if (response.success && response.data.length > 0) {
          const InvoicePrint = response.data[0];
          console.log("Invoice details:", InvoicePrint);
          $('#spbpk').val(InvoicePrint.spbpk || '');
          $('#spbpk2').val(InvoicePrint.spbpk || '');
          $('#ModalInvoice').modal('show');
        } else {
          alert('Data Invoice tidak ditemukan');
        }
      },
      error: function(error) {
        console.error("Gagal mengambil data Invoice:", error);
        alert('Terjadi kesalahan saat mengambil data');
      }
    });
  }

  function CancelModalPrint() {
    $('#ModalInvoice').modal('hide');
  }

  function cetak() {
    var cek = $('input[name="cek"]:checked').val();
    var spbpk = $('#spbpk2').val();
    if (!spbpk) {
      alert('SPBPK tidak boleh kosong');
      return;
    }

    // var url = '';
    // if (cek == 1) {
    //     url = "http://192.168.0.22/SPB/print-spb/" + spbpk ;
    // } else if (cek == 2) {
    //     url = "http://192.168.0.22/SPB/print-tips-stuffing/" + spbpk;
    // }
    // window.open(url, '_blank');

    var baseUrl = "{{ url('/') }}"; // Pastikan ini berjalan di Blade atau diganti dengan variabel JavaScript

    var url = "";
    if (cek == 1) {
      url = baseUrl + "/print-spb/" + spbpk;
    } else if (cek == 2) {
      url = baseUrl + "/print-tips-stuffing/" + spbpk;
    } else {
      alert('Silakan pilih opsi cetak');
      return;
    }

    window.open(url, '_blank');
  }
</script>
@endsection

@section('content')
<div class="main-wrapper">
  <div class="container-fluid p-4">
    <div class="d-flex flex-column">
      <div class="p-2">
        <span class="txt-000000-700-24-28">List SPB</span>
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
          <select class="easyui-combobox" id="filterByYear" data-options="editable:false, panelHeight:'auto', onChange:loadDataByFilter"
            style="width:100px;">
            <?php
            for ($i = date('Y'); $i >= date('Y') - 6; $i -= 1) { ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php } ?>
          </select>
        </div>

        @if(Session::get('guserpk') == '7' || Session::get('guserpk') == '2')
        <!-- <a onclick="AddSpb();">
          <input type="button" name="submit" value="&#10010; Create SPB" class="button-add">
        </a> -->

        <button onclick="AddSpb();" type="button" class="btn btn-dark" style="height:30px; line-height:30px; padding:0 10px;">&#10010; Create SPB</button>


        <div class="dropdown">
          <button class="btn btn-dark dropdown-toggle" style="height:30px; line-height:30px; padding:0 10px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Tabel
          </button>
          <ul class="dropdown-menu">
            <li>
              <a class="dropdown-item" href="{{ route('list.tab-gudang') }}">Gudang</a>
            </li>
              <hr style="margin:-2px 0;">
            <li>
              <a class="dropdown-item" href="{{ route('list.tab-jang') }}">Jenis Angkutan</a>
            </li>
            <hr style="margin:-1px 0;">
            <li>
              <a class="dropdown-item" href="{{ route('list.tab-ang') }}">Perusahaan Angkutan</a>
            </li>
            <hr style="margin:-1px 0;">
            <li>
              <a class="dropdown-item" href="{{ route('list.tab-fwd') }}">Forwarder</a>
            </li>
            <hr style="margin:-1px 0;">
            <li>
              <a class="dropdown-item" href="{{ route('list.tab-emkl') }}">EMKL</a>
            </li>
            <hr style="margin:-2px 0;">
            <li>
              <a class="dropdown-item" href="{{ route('list.tab-byr') }}">Buyer</a>
            </li>
          </ul>
        </div>
        @endif

        <div class="ms-auto">
          <div id="sortlist-grup" class="p-1 pb-0 responsive">
            <input type="hidden" id="sortlistByDate" name="sortlistByDate" size="5">
            <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
              <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
              <span id="Invdatedesc">No SPB (desc)</span>
              <span id="Invdateasc" class="d-none">No SPB (asc)</span>
            </button>
            <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
              <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByDate(11)">No SPB (descending)</a></li>
              <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByDate(12)">No SPB (ascending)</a></li>
            </ul>
          </div>
        </div>

      </div>

      <div class="d-none">
        <table id="dg-spb-shadow" class="easyui-datagrid" title="" style="width:100%;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="spbpk" pagination="true" rownumbers="false" multiple="true" collapsible="true" method="get"
          url="{{ route('api.get-spb') }}">
        </table>
      </div>

      <div class="p-0">
        <table id="dg-spb" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="spbpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
          url="{{ route('api.get-spb')}}" data-options="fitColumns:true, sortable:true,">
          <thead>
            <tr>
              <th field="index" styler="styler1">No</th>
              <th field="tanggal" styler="styler2">Tanggal</th>
              <th field="noinv" styler="styler2">No INV</th>
              <th field="nobukti" styler="styler2">No SPB</th>
              <th field="totalsatuan" styler="styler2">Total Pcs</th>
              <th field="gudang" styler="styler2">Gudang</th>
              <th field="jangkutan" styler="styler2">Jenis Angkutan</th>
              <th field="pangkutan" styler="styler2">Perusahaan Angkutan</th>
              <th field="contno" styler="styler2">Cont No</th>
              <th field="sealno" styler="styler2">Seal No</th>
              <!-- <th field="notruk" styler="styler2">No Truk</th> -->
              <!-- <th field="fwdnm" styler="styler2">Forwarder</th> -->
              <!-- <th field="emklnm" styler="styler2">EMKL</th> -->
              <th field="act" styler="styler3" formatter="formatAttribute" align="right">Act</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ModalInvoice" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <b>Print Invoice</b>
      </div>
      <div class="modal-body">

        <div class="form-group d-none">
          <label for="invpk">SPBPK1:</label>
          <input type="text" id="spbpk" class="form-control" readonly>
        </div>

        <div class="form-group d-none">
          <label for="spbpk">SPBPK:</label>
          <input type="text" id="spbpk2" class="form-control" readonly>
        </div>

        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="cek" value="1" checked>
          <label class="form-check-label">Surat Pengantar Barang</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="cek" value="2">
          <label class="form-check-label">Tips Stuffing</label>
        </div>
      </div>
      <div class="modal-footer">
        <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="CancelModalPrint()"><b>Cancel</b></a>
        <button type="button" class="btn btn-dark" onclick="cetak();">Export</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="ModalCreateInv" tabindex="-1" aria-labelledby="ModalDetailLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="border: none;">
        <h1 class="modal-title fs-5" id="ModalDetailLabel" style="font-weight: 700;">Create Invoice</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="text" id="spbpk" hidden>
        <input type="date" id="tgl" hidden>
        <input type="number" id="totsatuan" hidden>

        <div class="row mt-3">

          <div class="col-md-6">
            <label for="nobukti" class="form-label">No SPB</label>
            <input type="text" class="form-control" id="nobukti" disabled>
          </div>

          <div class="col-md-6">
            <label for="noinv" class="form-label">No INV</label>
            <div class="input-group mb-3">
              <input type="text" class="form-control col-4" name="noinv" id="noinv" placeholder="No" aria-label="NoInv" disabled>
              <input type="text" class="form-control col-8" id="dynamic-year" value="/AKB/MIF/2024" disabled>
            </div>
          </div>

          <div class="col-md-6">
            <label for="cur" class="form-label">Select Currency</label>
            <div class="input-group mb-3">
              <select name="cur" id="cur" class="form-select" required>
                <option value="">Select Currency</option> <!-- Kosongkan value agar dianggap tidak valid -->
                <option value="1">USD Dollar</option>
                <option value="2">IDR Rupiah</option>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <label for="pricemat" class="form-label">Price Material</label>
            <div class="input-group mb-3">
              <input type="text" class="form-control" name="pricemat" id="pricemat" required>
            </div>
          </div>

          <!-- <div class="col-md-6">
            <label for="pricematXX" class="form-label">Price Material</label>
            <div class="input-group mb-3">
              <span class="input-group-text">USD</span>
              <input type="text" class="form-control" name="pricematXX" id="pricematXX" required="true">
            </div>
          </div> -->

        </div>
      </div>

      <div class="modal-footer" style="border:none;">
        <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="CancelModalCreateInv()"><b>Cancel</b></a>
        <button type="submit" onclick="onSubmit();" class="btn btn-dark">Save</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="ModalEditBc" tabindex="-1" aria-labelledby="ModalDetailLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="border: none;">
        <h1 class="modal-title fs-5" id="ModalDetailLabel" style="font-weight: 700;">Edit PDF Beacukai</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <input type="text" id="spbpkbc" hidden>

        <div class="row mt-2">

          <div class="col-md-6">
            <label for="nobuktibc" class="form-label"><b>No SPB</b></label>
            <input type="text" class="form-control" id="nobuktibc" disabled>
          </div>

          <div class="col-md-6">
            <label for="noinvbc" class="form-label"><b>No INV</b></label>
            <div class="input-group mb-3">
              <input type="text" class="form-control" id="noinvbc" disabled>
            </div>
          </div>

        </div>


        <div class="row mt-3">

          <div class="col-md-12">
            <label for="prodes" class="form-label"><b>Description</b></label>
            <div class="input-group mb-3">
              <input type="text" class="form-control" name="prodes" id="prodes" required="true">
            </div>
          </div>

          <div class="col-md-12">
            <label for="buyer" class="form-label"><b>Style</b></label>
            <div class="input-group mb-3">
              <input type="text" class="form-control" name="buyer" id="buyer" required="true">
            </div>
          </div>

        </div>
      </div>

      <div class="modal-footer" style="border:none;">
        <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="CancelModalBc()"><b>Cancel</b></a>
        <button type="submit" onclick="onSubmitEditBc();" class="btn btn-dark">Save</button>
      </div>

    </div>
  </div>
</div>
@endsection