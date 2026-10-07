@extends('layout.main')

@section('css_custom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    .small-button {
        display: inline-block;
        padding: 3px 8px;
        font-size: 12px;
        color: #343a40;
        background-color: transparent;
        border: 1px solid #343a40;
        text-decoration: none;
        border-radius: 3px;
    }

    .small-button:hover {
        background-color: black;
        color: white;
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
<script>
    $(function() {
        $('#dg-cmt-shadow').datagrid('getPanel').find('div.datagrid-view').css('display', 'none');
        $('#dg-cmt-shadow').datagrid('getPanel').find('div.datagrid-pager').css('display', 'none');

        $('#dg-cmt').datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
        $('#dg-cmt').datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
        $('#dg-cmt').datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
        $('#dg-cmt').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg-cmt').datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
        $('#dg-cmt').datagrid('getPanel').css('border', 'none');
        $('#dg-cmt').datagrid('getPanel').addClass('lines-no');
        $('#dg-cmt').datagrid('getPanel').addClass('lines-no3');

        $('#dg-cmt-mobile').datagrid('getPanel').find('div.datagrid-header').css('visibility', 'hidden');
        $('#dg-cmt-mobile').datagrid('getPanel').find('div.datagrid-header').addClass('h-0');
        $('#dg-cmt-mobile').datagrid('getPanel').css('border', 'none');
        $('#dg-cmt-mobile').datagrid('getPanel').addClass('lines-no');
        $('#dg-cmt-mobile').datagrid('getPanel').addClass('lines-no3');
    })

    $(function() {
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dgModal').datagrid('getPanel').css('border', 'none');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dgModal').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dgModal').datagrid('getPanel').addClass('lines-no');
        $('#dgModal').datagrid('getPanel').addClass('lines-no3');
    });

    $(function() {
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dgExportListShipment').datagrid('getPanel').css('border', 'none');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dgExportListShipment').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dgExportListShipment').datagrid('getPanel').addClass('lines-no');
        $('#dgExportListShipment').datagrid('getPanel').addClass('lines-no3');
    });

    $(function() {
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dgDebitNote').datagrid('getPanel').css('border', 'none');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dgDebitNote').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dgDebitNote').datagrid('getPanel').addClass('lines-no');
        $('#dgDebitNote').datagrid('getPanel').addClass('lines-no3');
    });

    $(function() {
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dgTagihan').datagrid('getPanel').css('border', 'none');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dgTagihan').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dgTagihan').datagrid('getPanel').addClass('lines-no');
        $('#dgTagihan').datagrid('getPanel').addClass('lines-no3');
    });

    $(function() {
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dgListPayment').datagrid('getPanel').css('border', 'none');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dgListPayment').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dgListPayment').datagrid('getPanel').addClass('lines-no');
        $('#dgListPayment').datagrid('getPanel').addClass('lines-no3');
    });

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

        var invpk = row.invpk || 'null';
        var spbpk = row.spbpk || 'null';

        return '<div>' +
            '<button id="button-action-' + (row.index || '') + '" type="button" class="btn dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown">' +
            '<img src="' + imageUrl + '" height="15px" width="5px">' +
            '</button>' +
            (value || '') +
            '<ul id="dropdown-menu-action-' + (row.index || '') + '" class="dropdown-menu pt-1 pb-1">' +
            // '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-" id="btn-detail-' + (row.index || '') + '" onclick="KlikEdit(' + (row.invpk || 'null') + ');" style="color:#359DD9;">Edit</a></li>' +
            // hr + '<li><a class="dropdown-item disabled">Detail</a></li>' +
            '<li><a class="dropdown-item dropdown-item-custom" id="btn-detail-' + (row.index || '') + '" onclick="KlikDetail(' + (row.invpk || 'null') + ');" style="color:#359DD9;">Detail</a></li>' +
            // hr + '<li><a class="dropdown-item dropdown-item-custom" onclick="KlikPrint(' + (row.invpk || 'null') + ')" style="color:#359DD9;">Print Invoice</a></li>' +
            hr + '<li><a class="dropdown-item dropdown-item-custom" id="btn-print-' + (row.index || '') + '" onclick="KlikPrint(' + invpk + ', ' + spbpk + ')" style="color:#359DD9;">Print Invoice</a></li>' +
            '</ul>' +
            '</div>';
    }

    function KlikDetail(invpk) {
        const baseUrl = "{{ url('/')}}";
        // window.open(baseUrl + "/detail-finance/" + invpk);
        window.location.href = baseUrl + "/detail-finance/" + invpk;
    }

    function loadDataByFilter() {
        console.log(`loadDataByFilter RUNNING >>>>>>>>>>>>>>`);
        var searchByInput = $('#searchByInput').val();
        var filterByYear = $('#filterByYear').val();
        var filterByMonth = $('#filterByMonth').val();
        var sortlistByDate = $('#sortlistByDate').val();

        $('#dg-cmt-shadow').datagrid('load', {
            searchByInput: searchByInput,
            filterByYear: filterByYear,
            filterByMonth: filterByMonth,
            sortlistByDate: sortlistByDate,
        });

        console.log(`searchByInput : ${searchByInput}`);
        $('#dg-cmt').datagrid('load', {
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
            $('#InvDateDesc').removeClass('d-none');
            $('#InvDateAsc').addClass('d-none');
        } else if (value == 12) {
            $('#InvDateDesc').addClass('d-none');
            $('#InvDateAsc').removeClass('d-none');
        } else {
            $('#InvDateDesc').removeClass('d-none');
            $('#InvDateAsc').addClass('d-none');
        }
        loadDataByFilter();
    }

    function KlikEdit(invpk) {
        $.ajax({
            url: '/SPB/api/get-cmt/' + invpk,
            method: 'GET',
            success: function(response) {
                console.log("Response received:", response);
                if (response.success && response.data.length > 0) {
                    const InvoiceDetails = response.data[0];
                    console.log("Invoice details:", InvoiceDetails);
                    $('#invpk').val(InvoiceDetails.invpk || '');
                    $('#spbpk').val(InvoiceDetails.spbpk || '');
                    $('#tglinv').val(InvoiceDetails.tglinv || '');
                    $('#noinv').val(InvoiceDetails.noinv || '');
                    $('#nokk').val(InvoiceDetails.nokk || '');
                    $('#totqty').val(InvoiceDetails.totqty || '');
                    // $('#priceppn').val(InvoiceDetails.priceppn || '');
                    $('#pricemat').val(InvoiceDetails.pricemat || '');
                    $('#ppn').val(InvoiceDetails.ppn || '11');
                    $('#kursp').val(InvoiceDetails.kursp || '');
                    $('#ModalEdit').modal('show');
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

    function onSubmit() {
        var formdata = new FormData();
        // formdata.append('spbpk', $('#ModalCreateInv').data('spbpk'));
        formdata.append('invpk', $('#invpk').val()); // Ambil value dari input text dengan id spbpk
        formdata.append('spbpk', $('#spbpk').val());
        formdata.append('nokk', $('#nokk').val());
        // formdata.append('totqty', $('#totqty').val());
        // formdata.append('priceppn', $('#priceppn').val());
        formdata.append('pricemat', $('#pricemat').val());
        formdata.append('ppn', $('#ppn').val());
        formdata.append('kursp', $('#kursp').val());

        console.log("FormData to save Data >>>>>>>>>>>>>>>>>>>>>");
        for (let pair of formdata.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        let url = "{{ route('api.edit-cmt') }}";
        console.log("URL save Data: " + url);

        $.ajax({
            url: url,
            method: 'POST',
            data: formdata,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            // success: function(response) {
            //     console.log("Success saving Data >>>>>>>>>>>>>>>>>>>");
            //     console.log(response);

            //     if (response.success) {
            //         // alert('Data berhasil diperbarui');
            //         $('#ModalEdit').modal('hide');
            //         location.reload();
            //     } else {
            //         alert('Gagal memperbarui data: ' + response.message);
            //     }
            // },
            success: function(response, textStatus, xhr) {
                console.log("Success saving Data >>>>>>>>>>>>>>>>>>>");
                console.log(response);

                const statusCode = xhr.status; // Ambil status HTTP untuk debugging

                if (response.success) {
                    showAlert(200, response.message);
                    $('#ModalEdit').modal('hide');
                    // $('#form-create-inv')[0].reset();
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('error', response.message || 'Gagal memperbarui data.');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error saving Data >>>>>>>>>>>>>>>>>>>");
                console.error("Status:", status);
                console.error("Error:", error);
                console.error("Response:", xhr.responseText);
                // alert('Terjadi kesalahan saat menyimpan data');
            }
        });
    }

    function CancelModalEdit() {
        $('#ModalEdit').modal('hide');
    }

    function KlikPrint(invpk) {
        $.ajax({
            url: '/SPB/api/get-cmt/' + invpk,
            method: 'GET',
            success: function(response) {
                console.log("Response received:", response);
                if (response.success && response.data.length > 0) {
                    const InvoicePrint = response.data[0];
                    console.log("Invoice details:", InvoicePrint);
                    $('#invpk2').val(InvoicePrint.invpk || '');
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

    function cetak() {
        var cek = $('input[name="cek"]:checked').val();
        var spbpk = $('#spbpk2').val();
        if (!spbpk) {
            alert('SPBPK tidak boleh kosong');
            return;
        }
        var baseUrl = "{{ url('/') }}";;
        if (cek == 1) {
            url = baseUrl + "/detail/" + spbpk + "/pdf";
        } else if (cek == 2) {
            // url = "http://192.168.0.22/SPB/detail/" + spbpk + "/finance";
            url = baseUrl + "/detail/" + spbpk + "/finance/pdf";
        }
        window.open(url, '_blank');
    }

    function CancelModalPrint() {
        $('#ModalInvoice').modal('hide');
    }

    $(function() {
        $('#dg-cmt-shadow').datagrid({
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
            $('#dg-cmt').datagrid('getPanel').find('div.datagrid-view2').css('height', windowHeight);
            $('#dg-cmt-mobile').datagrid('getPanel').find('div.datagrid-view2').css('height', windowHeight);
        } else {
            console.log("here auto");
            $('#dg-cmt').datagrid('getPanel').find('div.datagrid-view2').css('height', 'auto');
            $('#dg-cmt-mobile').datagrid('getPanel').find('div.datagrid-view2').css('height', 'auto');
        }
    }

    function myformatter(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return (d < 10 ? '0' + d : d) + '/' + (m < 10 ? '0' + m : m) + '/' + y; // Format DD/MM/YYYY
    }

    function myparser(s) {
        if (!s) return new Date();

        var ss = s.split('/'); // Ubah split ke '/'
        var d = parseInt(ss[0], 10); // Hari
        var m = parseInt(ss[1], 10); // Bulan
        var y = parseInt(ss[2], 10); // Tahun

        if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
            return new Date(y, m - 1, d); // Pastikan bulan dikurangi 1 (0-indexed)
        } else {
            return new Date();
        }
    }

    // kwitansi export awal
    function KlikExportKwitansi() {
        const params = {
            searchByInput: $('#searchByInput').val(),
            filterByMonth: $('#filterByMonth').val(),
            filterByYear: $('#filterByYear').val(),
        };

        const query = Object.entries(params)
            .map(([key, val]) => `${key}=${encodeURIComponent(val)}`)
            .join('&');

        window.location.href = `{{ route('export.kwitansi') }}?${query}`;
    }
    // tutup kwitansi export awal

    //modal Export Kwintansi Versi 2//
    function KlikExportKwitansi2() {
        $('#KlikExportKwitansi2').modal('show');
    }

    $(document).ready(function() {
        ChangeKwitansiExportButtonState(); // Set tombol Export ke disabled saat halaman pertama kali dimuat
    });

    function loadDataByFilterModalKwitansi() {
        $('#dgModal').datagrid('load', {
            SelectGudangKwitansi: $('#SelectGudangKwitansi').combobox('getValue'), // Menggunakan combobox('getValue')
            SelectStartKwitansi: $('#SelectStartKwitansi').datebox('getValue'),
            SelectFinishKwitansi: $('#SelectFinishKwitansi').datebox('getValue'),
        });

        // Pastikan tombol diupdate setiap perubahan filter
        setTimeout(ChangeKwitansiExportButtonState, 100);
    }

    function ChangeKwitansiExportButtonState() {
        const selectedGudangKwitansi = $('#SelectGudangKwitansi').combobox('getValue').trim(); // Ambil nilai Gudang
        const ButtonKlikExportKwitansi = $('#ButtonKlikExportKwitansi');

        if (selectedGudangKwitansi) {
            ButtonKlikExportKwitansi.prop('disabled', false).removeClass('disabled'); // Aktifkan tombol
        } else {
            ButtonKlikExportKwitansi.prop('disabled', true).addClass('disabled'); // Nonaktifkan tombol
        }
    }

    function formatToIso(dateString) {
        const parts = dateString.split('/');
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    function OnClickExportKwitansi() {
        const selectedGudangKwitansi = $('#SelectGudangKwitansi').combobox('getValue').trim();
        const searchByInputOnModalExportExcel = $('#searchByInputOnModalExportExcel').val().trim();


        if (!selectedGudangKwitansi) {
            alert('Silakan pilih Gudang terlebih dahulu!'); // Mencegah klik jika Gudang belum dipilih
            return;
        }

        const params = {
            SelectGudangKwitansi: selectedGudangKwitansi,
            searchByInputOnModalExportExcel: searchByInputOnModalExportExcel,
            SelectStartKwitansi: formatToIso($('#SelectStartKwitansi').datebox('getValue')),
            SelectFinishKwitansi: formatToIso($('#SelectFinishKwitansi').datebox('getValue')),
        };

        const query = Object.entries(params)
            .map(([key, val]) => `${key}=${encodeURIComponent(val)}`)
            .join('&');

        window.location.href = `{{ route('export.kwitans-by-modal') }}?${query}`;
    }

    function resetFormKwitansi() {
        $('#SelectGudangKwitansi').combobox('clear');
        $('#SelectStartKwitansi').datebox('clear');
        $('#SelectFinishKwitansi').datebox('clear');
    }
    //tutup modal Export Kwintansi Versi 2//



    // Modal Export list Shipment - Andre Finance
    $(document).ready(function() {
        ChangeKwitansiExportListShipmentButtonState(); // Set tombol Export ke disabled saat halaman pertama kali dimuat
    });

    function loadDataByFilterModalExportListShipment() {
        $('#dgExportListShipment').datagrid('load', {
            SelectGudangExport: $('#SelectGudangExport').combobox('getValue'), // Menggunakan combobox('getValue')
            SelectStartExport: $('#SelectStartExport').datebox('getValue'),
            SelectFinishExport: $('#SelectFinishExport').datebox('getValue'),
        });

        // Pastikan tombol diupdate setiap perubahan filter
        setTimeout(ChangeKwitansiExportListShipmentButtonState, 100);
    }

    function ChangeKwitansiExportListShipmentButtonState() {
        const selectedGudangExport = $('#selectedGudangExport').combobox('getValue').trim(); // Ambil nilai Gudang
        const ButtonKlikExportListShipment = $('#ButtonKlikExportListShipment');

        if (selectedGudangExport) {
            ButtonKlikExportListShipment.prop('disabled', false).removeClass('disabled'); // Aktifkan tombol
        } else {
            ButtonKlikExportListShipment.prop('disabled', true).addClass('disabled'); // Nonaktifkan tombol
        }
    }

    function OnClickExportListShipment() {
        const selectedGudangExport = $('#SelectGudangExport').combobox('getValue').trim();

        if (!selectedGudangExport) {
            alert('Silakan pilih Gudang terlebih dahulu!'); // Mencegah klik jika Gudang belum dipilih
            return;
        }

        const params = {
            SelectGudangExport: selectedGudangExport,
            SelectStartExport: formatToIso($('#SelectStartExport').datebox('getValue')),
            SelectFinishExport: formatToIso($('#SelectFinishExport').datebox('getValue')),
        };

        const query = Object.entries(params)
            .map(([key, val]) => `${key}=${encodeURIComponent(val)}`)
            .join('&');

        window.location.href = `{{ route('export.list-shipment') }}?${query}`;
    }

    //tutup modal export list shipment

    //Klik Modal DebitNote//
    function KlikDebitNote() {
        $('#KlikModalDebitNote').modal('show');
    }

    $(document).ready(function() {
        updateExportButtonStateDebitNote(); // Set tombol Export ke disabled saat halaman pertama kali dimuat
    });

    function loadDataByFilterModalDebitNote() {
        $('#dgDebitNote').datagrid('load', {
            SelectGudangDebitNote: $('#SelectGudangDebitNote').combobox('getValue'), // Menggunakan combobox('getValue')
            SelectStartDebitNote: $('#SelectStartDebitNote').datebox('getValue'),
            SelectFinishDebitNote: $('#SelectFinishDebitNote').datebox('getValue'),
        });

        setTimeout(updateExportButtonStateDebitNote, 100); // Pastikan tombol diupdate
    }

    function updateExportButtonStateDebitNote() {
        const selectedGudangDebitNote = $('#SelectGudangDebitNote').combobox('getValue').trim(); // Ambil nilai Gudang
        const exportButtonDebitNote = $('#exportButtonDebitNote');

        if (selectedGudangDebitNote) {
            exportButtonDebitNote.prop('disabled', false).removeClass('disabled'); // Aktifkan tombol
        } else {
            exportButtonDebitNote.prop('disabled', true).addClass('disabled'); // Nonaktifkan tombol
        }
    }

    function DebitNoteexportFilteredData() {
        const selectedGudangDebitNote = $('#SelectGudangDebitNote').combobox('getValue').trim();

        if (!selectedGudangDebitNote) {
            alert('Silakan pilih Gudang terlebih dahulu!'); // Mencegah klik jika Gudang belum dipilih
            return;
        }

        const params = {
            SelectGudangDebitNote: selectedGudangDebitNote,
            SelectStartDebitNote: $('#SelectStartDebitNote').datebox('getValue'),
            SelectFinishDebitNote: $('#SelectFinishDebitNote').datebox('getValue'),
        };

        const query = Object.entries(params)
            .map(([key, val]) => `${key}=${encodeURIComponent(val)}`)
            .join('&');

        window.location.href = `{{ route('export.excel-debitnote') }}?${query}`;
    }

    function resetFormDebitNote() {
        $('#SelectGudangDebitNote').combobox('clear');
        $('#SelectStartDebitNote').datebox('clear');
        $('#SelectFinishDebitNote').datebox('clear');
    }
    //Tutup JS Modal DebitNote


    // Klik Modal Tagihan
    function KlikTagihan() {
        $('#KlikModalTagihan').modal('show');
    }

    $(document).ready(function() {
        updateExportButtonStateTagihan(); // Set tombol Export ke disabled saat halaman pertama kali dimuat
    });

    function loadDataByFilterModalTagihan() {
        $('#dgTagihan').datagrid('load', {
            SelectGudangTagihan: $('#SelectGudangTagihan').combobox('getValue'), // Menggunakan combobox('getValue')
            SelectStartTagihan: $('#SelectStartTagihan').datebox('getValue'),
            SelectFinishTagihan: $('#SelectFinishTagihan').datebox('getValue'),
        });

        setTimeout(updateExportButtonStateTagihan, 100); // Pastikan tombol diupdate
    }

    function updateExportButtonStateTagihan() {
        const selectedGudangTagihan = $('#SelectGudangTagihan').combobox('getValue').trim(); // Ambil nilai Gudang
        const exportButtonTagihan = $('#exportButtonTagihan');

        if (selectedGudangTagihan) {
            exportButtonTagihan.prop('disabled', false).removeClass('disabled'); // Aktifkan tombol
        } else {
            exportButtonTagihan.prop('disabled', true).addClass('disabled'); // Nonaktifkan tombol
        }
    }

    function TagihanexportFilteredData() {
        const selectedGudangTagihan = $('#SelectGudangTagihan').combobox('getValue').trim();

        if (!selectedGudangTagihan) {
            alert('Silakan pilih Gudang terlebih dahulu!'); // Mencegah klik jika Gudang belum dipilih
            return;
        }

        const params = {
            SelectGudangTagihan: selectedGudangTagihan,
            SelectStartTagihan: $('#SelectStartTagihan').datebox('getValue'),
            SelectFinishTagihan: $('#SelectFinishTagihan').datebox('getValue'),
        };

        const query = Object.entries(params)
            .map(([key, val]) => `${key}=${encodeURIComponent(val)}`)
            .join('&');

        window.location.href = `{{ route('export.excel-tagihan') }}?${query}`;
    }


    function resetFormTagihan() {
        $('#SelectGudangTagihan').combobox('clear');
        $('#SelectStartTagihan').datebox('clear');
        $('#SelectFinishTagihan').datebox('clear');
    }
    //Tutup Modal Tagihan

    // JS Modal List Payment
    function KlikExportListPayment() {
        $('#KlikModalListFinance').modal('show');
    }

    $(document).ready(function() {
        PdfExportButtonState(); // Set tombol Export ke disabled saat halaman pertama kali dimuat
    });

    function loadDataByFilterModalListPay() {
        $('#dgListPayment').datagrid('load', {
            SelectGudang: $('#SelectGudang').combobox('getValue'), // Menggunakan combobox('getValue')
            SelectStart: $('#SelectStart').datebox('getValue'),
            SelectFinish: $('#SelectFinish').datebox('getValue'),
        });

        setTimeout(PdfExportButtonState, 100); // Pastikan tombol diupdate setelah perubahan
    }

    function PdfExportButtonState() {
        const selectedGudang = $('#SelectGudang').combobox('getValue').trim(); // Ambil nilai Gudang
        const exportButton = $('#exportButton');

        if (selectedGudang) {
            exportButton.prop('disabled', false).removeClass('disabled'); // Aktifkan tombol
        } else {
            exportButton.prop('disabled', true).addClass('disabled'); // Nonaktifkan tombol
        }
    }

    function formatToIsoPdf(dateStr) {
        const parts = dateStr.split('/');
        if (parts.length !== 3) return '';
        const [day, month, year] = parts;
        return `${year}-${month}-${day}`;
    }

    function PdfFilteredData() {
        const selectedGudang = $('#SelectGudang').combobox('getValue').trim();

        if (!selectedGudang) {
            alert('Silakan pilih Gudang terlebih dahulu!'); // Mencegah klik jika Gudang belum dipilih
            return;
        }

        const params = {
            // SelectGudang: selectedGudang,
            // SelectStart: $('#SelectStart').datebox('getValue'),
            // SelectFinish: $('#SelectFinish').datebox('getValue'),
            SelectGudang: selectedGudang,
            SelectStart: formatToIsoPdf($('#SelectStart').datebox('getValue')),
            SelectFinish: formatToIsoPdf($('#SelectFinish').datebox('getValue')),
        };

        const query = Object.entries(params)
            .map(([key, val]) => `${key}=${encodeURIComponent(val)}`)
            .join('&');

        window.location.href = `{{ route('pdf-list-payment-by-modal') }}?${query}`;
    }

    function resetFormListPayment() {
        $('#SelectGudang').combobox('clear');
        $('#SelectStart').datebox('clear');
        $('#SelectFinish').datebox('clear');
    }

    // tutup JS Modal List Payment

    $(function() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];
        if (pathname3 == 'list-finance') {
            $('#dg').datagrid('showColumn', 'ck');
        } else {
            $('#dg').datagrid('hideColumn', 'ck');
        }

        $('#KlikExportKwitansi2').on('shown.bs.modal', function() {
            $('#dgModal').datagrid('resize');
        })

        $('#KlikModalExportListShipment').on('shown.bs.modal', function() {
            $('#dgExportListShipment').datagrid('resize');
        })

        $('#KlikModalDebitNote').on('shown.bs.modal', function() {
            $('#dgDebitNote').datagrid('resize');
        })

        $('#KlikModalTagihan').on('shown.bs.modal', function() {
            $('#dgTagihan').datagrid('resize');
        })

        $('#KlikModalListFinance').on('shown.bs.modal', function() {
            $('#dgListPayment').datagrid('resize');
        })

        $('#ModalRequestInv').on('shown.bs.modal', function() {
            $('#dgOrder').datagrid('resize');
        })

    });

    function KlikRequestInv() {
        $('#ModalRequestInv').modal('show');
    }

    function sortlistByDateReqInv(value) {
        document.getElementById('sortlistByDateReqInv').value = value;
        if (value == 11) {
            $('#InvDateDescReqInv').removeClass('d-none');
            $('#InvDateAscReqInv').addClass('d-none');
        } else if (value == 12) {
            $('#InvDateDescReqInv').addClass('d-none');
            $('#InvDateAscReqInv').removeClass('d-none');
        } else {
            $('#InvDateDescReqInv').removeClass('d-none');
            $('#InvDateAscReqInv').addClass('d-none');
        }
        loadDataByFilterReqInv();
    }

    function loadDataByFilterReqInv() {
        console.log(`loadDataByFilter RUNNING >>>>>>>>>>>>>>`);
        var filterByYearReqInv = $('#filterByYearReqInv').val();
        var filterByMonthReqInv = $('#filterByMonthReqInv').val();
        var sortlistByDateReqInv = $('#sortlistByDateReqInv').val();
        var searchByInputReqInv = $('#searchByInputReqInv').val();

        console.log(`searchByInput : ${searchByInputReqInv}`);
        $('#dgOrder').datagrid('load', {
            filterByYearReqInv: filterByYearReqInv,
            filterByMonthReqInv: filterByMonthReqInv,
            sortlistByDateReqInv: sortlistByDateReqInv,
            searchByInputReqInv: searchByInputReqInv,
        })

        console.log(`loadDataByFilter END >>>>>>>>>>>>>>`);
    }

    function SubmitReqInv() {
        var selectedRow = $('#dgOrder').datagrid('getSelected');
        if (!selectedRow) {
            $.messager.alert('Warning', 'No data selected', 'warning');
            return;
        }

        var noinv = $('#noinv').val().trim();
        if (!noinv) {
            $.messager.alert('Warning', 'Please enter No. Inv', 'warning');
            return;
        }

        var checkedRows = $('#dgOrder').datagrid('getChecked');
        if (!checkedRows || checkedRows.length === 0) {
            $.messager.alert('Warning', 'No data selected', 'warning');
            return;
        }

        var spbpkList = checkedRows.map(row => row.spbpk);

        var formData = {
            spbpk: spbpkList,
            noinv: noinv,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log("Data sent:", formData);

        $.ajax({
            type: "POST",
            url: "{{ route('api.add-req-inv') }}",
            data: formData,
            success: function(response, textStatus, xhr) {
                console.log("Success saving user >>>>>>>>>>>>>>>>>>>");
                console.log(response);
                const statusCode = xhr.status;
                console.log('INI LIHAT : ' + JSON.stringify(response));

                if (response.status === 'success') {
                    showAlert(200, response.message);
                    $('#ModalRequestInv').modal('hide');
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

                var errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                showAlert('error', errorMessage);
            }
        });
    }

    function KlikExportListShipment() {
        $('#KlikModalExportListShipment').modal('show');
    }

    function SearchOnModalExportExcel() {
        // $('#dgModal').datagrid('load', {
        //     searchByInputOnModalExportExcel: $('#searchByInputOnModalExportExcel').val(),
        // });

        const gudpk = $('#SelectGudangKwitansi').combobox('getValue').trim();
        const start = $('#SelectStartKwitansi').datebox('getValue');
        const finish = $('#SelectFinishKwitansi').datebox('getValue');
        const search = $('#searchByInputOnModalExportExcel').val().trim();

        $('#dgModal').datagrid('load', {
            SelectGudangKwitansi: gudpk,
            SelectStartKwitansi: start,
            SelectFinishKwitansi: finish,
            searchByInputOnModalExportExcel: search
        });
    }
</script>
@endsection

@section('content')
<div class="main-wrapper">
    <div class="container-fluid p-4">
        <div class="d-flex flex-column">
            <div class="p-2">
                <span class="txt-000000-700-24-28">List Invoice CMT</span>
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

                <!-- <button type="button" class="btn btn-dark btn-sm" onclick="KlikExportKwitansi();"><i class="fas fa-file-excel"></i> Export Kwitansi</button> -->
                <!-- <button type="button" class="btn btn-dark btn-sm" onclick="KlikExportKwitansi2();"><i class="fas fa-file-excel"></i> Export Kwitansi</button> -->
                @if(Session::get('guserpk') == 7 || Session::get('guserpk') == '2')
                <button type="button" class="btn btn-dark btn-sm" onclick="KlikRequestInv();">Request No Inv</button>
                @endif

                @if(Session::get('guserpk') == 10 || Session::get('guserpk') == '2')
                <div class="dropdown">
                    <button class="btn btn-dark dropdown-toggle" style="height:30px; line-height:30px; padding:0 10px;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Master Export
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="KlikExportKwitansi2();"><i class="fas fa-file-excel"></i> Export Kwitansi</a>
                        </li>
                        <hr style="margin:-2px 0;">
                        <!-- <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="KlikDebitNote();"><i class="fas fa-file-excel"></i> Export Debit Note</a>
                        </li>
                        <hr style="margin:-1px 0;">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="KlikTagihan();"><i class="fas fa-file-excel"></i> Export Tagihan</a>
                        </li>
                        <hr style="margin:-1px 0;"> -->
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="KlikExportListPayment();"><i class="fas fa-file-pdf"></i> Export List Payment</a>
                        </li>
                        <hr style="margin:-2px 0;">
                        <li>
                            <a class="dropdown-item" href="javascript:void(0);" onclick="KlikExportListShipment();"><i class="fas fa-file-excel"></i> Export List Shipment</a>
                        </li>
                    </ul>
                </div>
                @endif

                <div class="ms-auto">
                    <div id="sortlist-grup" class="p-1 pb-0 responsive">
                        <input type="hidden" id="sortlistByDate" name="sortlistByDate" size="5">
                        <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
                            <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
                            <span id="InvDateDesc">Invoice date (desc)</span>
                            <span id="InvDateAsc" class="d-none">Invoice date (asc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByDate(11)">Invoice date (descending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByDate(12)">Invoice date (ascending)</a></li>
                        </ul>
                    </div>
                </div>

            </div>

            <div class="d-none">
                <table id="dg-cmt-shadow" class="easyui-datagrid" title="" style="width:100%;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="invpk" pagination="true" rownumbers="false" multiple="true" collapsible="true" method="get"
                    url="{{ route('api.get-cmt') }}">
                </table>
            </div>

            <div class="p-0">
                <table id="dg-cmt" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pageSize="100" pageList="[100,200,300,500]" idField="invpk" pagination="true" rownumbers="false" singleSelect="true" collapsible="false" method="get"
                    url="{{ route('api.get-cmt')}}" data-options="fitColumns:true, sortable:true,">
                    <thead>
                        <tr>
                            <th field="index" styler="styler1">No</th>
                            <th field="tanggal" styler="styler2">Tanggal</th>
                            <th field="noinv" styler="styler2">No INV</th>
                            <th field="nobukti" styler="styler2">No SPB</th>
                            <th field="gudang" styler="styler2">Gudang</th>
                            <th field="nokk" styler="styler2">No KK</th>
                            <th field="tglkk" styler="styler2">Tanggal KK</th>
                            <th field="totqty" styler="styler2">Total Qty</th>
                            <!-- <th field="priceppn" styler="styler2">Price PPN</th> -->
                            <th field="pricemat" styler="styler2">Price Material (Exim)</th>
                            <th field="ppn" styler="styler2">PPN</th>
                            <th field="kursppn" styler="styler2">Kurs Pajak</th>
                            <th field="act" styler="styler3" formatter="formatAttribute" align="left">Act</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@include('menu.finance.modal.modal-list-payment')
@include('menu.finance.modal.modal-req-inv')
@include('menu.finance.modal.modal-kwitansi')
@include('menu.finance.modal.modal-debitnote')
@include('menu.finance.modal.modal-tagihan')
@include('menu.finance.modal.modal-edit-invoice')
@include('menu.finance.modal.modal-print-invoice')
@include('menu.finance.modal.modal-export-list-shipment')
@endsection