@extends('layout.main')

@section('css_custom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .toast-container,
    .toast {
        z-index: 11000 !important;
        position: fixed !important;
    }

    .font-warning {
        font-size: 12px;
        font-weight: bold;
        color: red;
        text-align: left;
    }

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

    .datagrid-body {
        overflow-x: hidden;
        overflow-y: hidden;
    }

    .text-biru {
        color: #359DD9;
        font-weight: 700;
        cursor: pointer;
    }

    .datagrid-editable-input33 input {
        width: 200px !important;
    }
</style>
@endsection

@section('js_custom')
<script>
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
    });

    $(function() {
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dgOrder').datagrid('getPanel').css('border', 'none');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dgOrder').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dgOrder').datagrid('getPanel').addClass('lines-no');
        $('#dgOrder').datagrid('getPanel').addClass('lines-no3');
    });

    $(function() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];
        var nilai = (pathname3 === 'detail-spb' || pathname3 === 'add-spb');
        console.log(`nilai : ${nilai}`);
        var spbpk = $('#spbpk').val();
        var lastIndex = undefined;

        $('#dg').edatagrid({
            saveUrl: '{{ route("api.insert-spbdt", "") }}/' + spbpk,
            updateUrl: '{{ route("api.update-spbdt") }}',
            editing: nilai,
            onSuccess: function(index, row) {
                $(this).edatagrid('saveRow');
                $(this).datagrid('reload');
                console.log("ROW : " + row);
                console.log("nilai : " + nilai);
            },
            onDblClickRow: function(index, row) {
                var isEdit = $('#isEdit-' + row.spbdtpk).val();
                var spbdtpk = row.spbdtpk;

                console.log('isEdit:', isEdit);
                console.log('row:', row);

                if (isEdit == 'true') {
                    console.log(`here masuk sini 3`);
                    document.getElementById('isEdit-' + row.spbdtpk).value = 'false';
                    $(this).edatagrid('endEdit', index);
                } else {
                    console.log(`here masuk sini 1`);
                    console.log('onedit : ' + row.spbdtpk);
                    $('#isEdit-' + row.spbdtpk).val('true');

                    if (!$(this).edatagrid('options').editing) return;


                    $(this).edatagrid('beginEdit', index);
                    console.log("Double click edit mode");

                    var edByr = $(this).datagrid('getEditor', {
                        index: index,
                        field: 'byrpk'
                    });
                    if (edByr) {
                        $(edByr.target).combobox({
                            valueField: 'byrpk',
                            textField: 'byrnm',
                            method: 'get',
                            url: '{{ route("api.master.byr.get-all") }}',
                            mode: 'remote',
                            prompt: 'Nama Pembeli',
                            filter: function(q, row) {
                                var opts = $(this).combobox('options');
                                return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) >= 0;
                            },
                            onSelect: function(record) {
                                var dg = $('#dg');
                                var row = dg.datagrid('getRows')[index];

                                row.byrpk = record.byrpk;
                                row.byrnm = record.byrnm;

                                dg.datagrid('updateRow', {
                                    index: index,
                                    row: row
                                });
                                saveEditedRow(index);
                            }
                        });
                    } else {
                        console.error('Editor for byrpk not found');
                    }
                }
            },
            onBeforeEdit: function(index, row) {
                $('#savedt').removeClass('d-none');
                var token = $('meta[name="csrf-token"]').attr('content');
                row._token = token;
            },
            onBeforeSave: function(index) {
                $('#savedt').addClass('d-none');
            }
        });
    });

    function saveEditedRow(index) {
        var row = $('#dg').datagrid('getRows')[index];

        $.ajax({
            url: '{{ route("api.update-spbdt") }}',
            type: 'POST',
            data: row,
            success: function(response) {
                if (response.success) {
                    console.log('Data berhasil diperbarui:', response);
                    $('#dg').datagrid('reload');
                } else {
                    console.log('Gagal menyimpan data:', response);
                }
            },
            error: function(xhr) {
                console.log('Terjadi kesalahan dalam menyimpan data:', xhr.responseText);
            }
        });
    }

    // function tambahrow() {
    //     $('#dg').edatagrid('addRow');
    //     var index = $('#dg').edatagrid('getRows').length - 1;
    //     var ed = $('#dg').edatagrid('getEditors', index)[0];

    //     var edByr = $('#dg').datagrid('getEditor', {
    //         index: index,
    //         field: 'byrpk'
    //     });
    //     $(edByr.target).combobox({
    //         valueField: 'byrpk',
    //         textField: 'byrnm',
    //         method: 'get',
    //         url: '{{ env("APP_URL") . "/api/master/byr/get-all" }}',
    //         required: true
    //     });

    //     $(ed.target).focus();
    // }

    function tambahrow() {
        $('#dg').edatagrid('addRow');
        var index = $('#dg').edatagrid('getRows').length - 1; // Ambil index terakhir (baris baru)
        
        setTimeout(function() { // Gunakan timeout agar editor sudah aktif sebelum set combobox
            var edByr = $('#dg').datagrid('getEditor', {
                index: index,
                field: 'byrpk'
            });

            if (edByr) {
                $(edByr.target).combobox({
                    valueField: 'byrpk',
                    textField: 'byrnm',
                    method: 'get',
                    url: '{{ route("api.master.byr.get-all") }}',
                    mode: 'remote',
                    prompt: 'Cari Nama Pembeli...',
                    filter: function(q, row) {
                        var opts = $(this).combobox('options');
                        return row[opts.textField].toLowerCase().indexOf(q.toLowerCase()) >= 0;
                    },
                    onSelect: function(record) {
                        var dg = $('#dg');
                        var row = dg.datagrid('getRows')[index];

                        row.byrpk = record.byrpk;
                        row.byrnm = record.byrnm;

                        dg.datagrid('updateRow', {
                            index: index,
                            row: row
                        });
                        saveEditedRow(index);
                    }
                });
                $(edByr.target).combobox('showPanel');
            } else {
                console.error('Editor untuk byrpk tidak ditemukan');
            }
        }, 200);
    }

    $(function() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];
        if (pathname3 == 'add-spb') {
            $('#dg').datagrid('showColumn', 'ck');
        } else {
            $('#dg').datagrid('hideColumn', 'ck');
        }

        $('#ModalOrder').on('shown.bs.modal', function() {
            $('#dgOrder').datagrid('resize');
        })
    });

    $(function() {
        editDisplay();
    });

    function editDisplay() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[2];

        if (pathname3 == 'detail-spb') {
            console.log(`masuk sini editDisplay detail...`);
            $('#lookupgis').removeClass('d-none');
            $('#importbyexcel').removeClass('d-none');
            $('#dg').edatagrid('options').editing = false;
            $('#edit_data').removeClass('d-none');
            $('#tgl').datebox('disable');
            $('#jang').combobox('disable');
            $('#ang').combobox('disable');
            $('#contno').attr('disabled', true);
            $('#sealno').attr('disabled', true);
            $('#notruk').attr('disabled', true);
            $('#fwdnm').combobox('disable');
            $('#emklnm').combobox('disable');
            $('#gudnm').combobox('disable');
            $('#alamat').textbox('disable');

            $('#gudtest').combobox('disable');
            $('#alamattest').textbox('disable');

            $('#att').attr('disabled', true);
            $('#tips').attr('disabled', true);
            $('#totsatuan').attr('disabled', true);
            $('#totkarton').attr('disabled', true);
            $('#totberat').attr('disabled', true);
            $('#totkubikasi').attr('disabled', true);
            $('#addnew').addClass('d-none');
            $('#lookupgis').addClass('d-none');
            $('#importbyexcel').addClass('d-none');
            $('#gudnm').combobox({
                onChange: function() {
                    var gudpk = $(this).combobox('getValue');
                    var url = "{{ route('gudang', ['gudpk' => ':gudpk']) }}";
                    url = url.replace(':gudpk', gudpk);
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {},
                        success: function(data) {
                            var tdata = data.length;
                            if (tdata > 0) {
                                data.forEach(function(item) {
                                    console.log('Alamat:', item.alamat);
                                    $('#alamat').textbox('setValue', item.alamat);
                                    $('#gudpk').val(item.gudpk);
                                });
                            } else {
                                $('#alamat').val('');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                    cek_input_header();
                }
            });

            $('#gudtest').combobox({
                onChange: function() {
                    var gudpk = $(this).combobox('getValue');
                    var url = "{{ route('gudtest', ['gudpk' => ':gudpk']) }}";
                    url = url.replace(':gudpk', gudpk);
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {},
                        success: function(data) {
                            var tdata = data.length;
                            if (tdata > 0) {
                                data.forEach(function(item) {
                                    console.log('Alamat:', item.alamat);
                                    $('#alamattest').textbox('setValue', item.alamat);
                                    $('#gudpk').val(item.gudpk);
                                });
                            } else {
                                $('#alamattest').val('');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                    cek_input_header();
                }
            });

        } else if (pathname3 == 'add-spb') {
            console.log(`masuk sini editDisplay add...`);
            // edit_data();
            $('#dg').edatagrid('options').editing = true;
            $('#dg').datagrid('showColumn', 'ck');
            $('#cancel_submit').removeClass('d-none');
            $('#save_submit').removeClass('d-none');
            $('#add').removeClass('d-none');
            $('#lookupgis').removeClass('d-none');
            $('#importbyexcel').removeClass('d-none');
            $('#totsatuan').attr('disabled', true);
            $('#totkarton').attr('disabled', true);
            $('#totberat').attr('disabled', true);
            $('#totkubikasi').attr('disabled', true);

            $('#gudnm').combobox({
                onChange: function() {
                    var gudpk = $(this).combobox('getValue');
                    var url = "{{ route('gudang', ['gudpk' => ':gudpk']) }}";
                    url = url.replace(':gudpk', gudpk);
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {},
                        success: function(data) {
                            var tdata = data.length;
                            if (tdata > 0) {
                                data.forEach(function(item) {
                                    console.log('Alamat:', item.alamat);
                                    $('#alamat').textbox('setValue', item.alamat);
                                    $('#gudpk').val(item.gudpk);
                                });
                            } else {
                                $('#alamat').val('');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                }
            });

            $('#gudtest').combobox({
                onChange: function() {
                    var gudpk = $(this).combobox('getValue');
                    var url = "{{ route('gudtest', ['gudpk' => ':gudpk']) }}";
                    url = url.replace(':gudpk', gudpk);
                    $.ajax({
                        type: "GET",
                        url: url,
                        data: {},
                        success: function(data) {
                            var tdata = data.length;
                            if (tdata > 0) {
                                data.forEach(function(item) {
                                    console.log('Alamat:', item.alamat);
                                    $('#alamattest').textbox('setValue', item.alamat);
                                    $('#gudpk').val(item.gudpk);
                                });
                            } else {
                                $('#alamattest').val('');
                            }
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                        }
                    });
                    // cek_input_header();
                }
            });
        } else {
            $('#cancel_submit').addClass('d-none');
            $('#edit_data').addClass('d-none');
        }
    }

    function edit_data() {
        $('#edit_data').addClass('d-none');
        $('#cancel_save_edit').removeClass('d-none');
        $('#savedt').addClass('d-none');
        $('#add').removeClass('d-none');
        $('#lookupgis').removeClass('d-none');
        $('#importbyexcel').removeClass('d-none');
        $('#dg').edatagrid('options').editing = true;
        $('#dg').datagrid('showColumn', 'ck');
        $('#tgl').datebox('enable');
        $('#jang').combobox('enable');
        $('#ang').combobox('enable');
        $('#contno').attr('disabled', false);
        $('#sealno').attr('disabled', false);
        $('#notruk').attr('disabled', false);
        $('#fwdnm').combobox('enable');
        $('#emklnm').combobox('enable');
        $('#gudnm').combobox('enable');
        $('#alamat').textbox('disable');

        $('#gudtest').combobox('enable');
        $('#alamattest').textbox('disable');

        $('#att').attr('disabled', false);
        $('#tips').attr('disabled', false);
        $('#totsatuan').attr('disabled', true);
        $('#totkarton').attr('disabled', true);
        $('#totberat').attr('disabled', true);
        $('#totkubikasi').attr('disabled', true);
        $('#addnew').removeClass('d-none');
    }

    function cancel_edit() {
        var gudpk = $('#gudpkb').val();
        $('#gudnm').combobox('setValue', gudpk);
        var url = "{{ route('gudang', ['gudpk' => ':gudpk']) }}";
        url = url.replace(':gudpk', gudpk);
        $.ajax({
            type: "GET",
            url: url,
            data: {},
            success: function(data) {
                var tdata = data.length;
                if (tdata > 0) {
                    data.forEach(function(item) {
                        console.log('Alamat:', item.alamat);
                        $('#alamat').textbox('setValue', item.alamat);
                        $('#gudpk').val(item.gudpk);
                    });
                } else {
                    $('#alamat').val('');
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });

        var gudpk = $('#gudpkb').val();
        $('#gudtest').combobox('setValue', gudpk);
        var url = "{{ route('gudtest', ['gudpk' => ':gudpk']) }}";
        url = url.replace(':gudpk', gudpk);
        $.ajax({
            type: "GET",
            url: url,
            data: {},
            success: function(data) {
                var tdata = data.length;
                if (tdata > 0) {
                    data.forEach(function(item) {
                        console.log('Alamat:', item.alamat);
                        $('#alamattest').textbox('setValue', item.alamat);
                        $('#gudpk').val(item.gudpk);
                    });
                } else {
                    $('#alamattest').val('');
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
            }
        });

        $('#edit_data').removeClass('d-none');
        $('#dg').edatagrid('options').editing = false;
        $('#dg').datagrid('hideColumn', 'ck');
        $('#cancel_save_edit').addClass('d-none');
        $('#add').addClass('d-none');
        $('#lookupgis').addClass('d-none');
        $('#importbyexcel').addClass('d-none');

        var rows = $('#dg').datagrid('getRows');
        for (var i = 0; i < rows.length; i++) {
            $('#dg').datagrid('endEdit', i);
        }
        $('#tgl').datebox('disable');
        $('#jang').combobox('disable');
        $('#ang').combobox('disable');
        $('#contno').attr('disabled', true);
        $('#sealno').attr('disabled', true);
        $('#notruk').attr('disabled', true);
        $('#fwdnm').combobox('disable');
        $('#emklnm').combobox('disable');
        $('#gudnm').combobox('disable');
        $('#alamat').textbox('disable');

        $('#gudtest').combobox('disable');
        $('#alamattest').textbox('disable');

        $('#att').attr('disabled', true);
        $('#tips').attr('disabled', true);
        $('#totsatuan').attr('disabled', true);
        $('#totkarton').attr('disabled', true);
        $('#totberat').attr('disabled', true);
        $('#totkubikasi').attr('disabled', true);
        $('#addnew').addClass('d-none');
    }

    $(function() {
        var spbpk = $('#spbpk').val();
        var url = "{{ route('api.get-spbdt', ['spbpk' => ':spbpk']) }}";
        url = url.replace(':spbpk', spbpk);

        $('#dg').datagrid({
            url: url,
            method: 'get',
            onLoadSuccess: function(data) {
                console.log('Data loaded successfully:', data);
            },
            onLoadError: function() {
                console.error('Failed to load data.');
            }
        });
    });

    function saverow() {
        console.log(`saverow is running...`);
        $('#dg').edatagrid('saveRow');
    }

    function AttrSpbdtpk(value, row) {
        var hiddenSave = '<input type="text" id="isEdit-' + row.spbdtpk + '" value="false">';
        return hiddenSave;
    }

    function submit_spb(nilai) {
        var formElement = document.getElementById("form_header");

        var jangpk = $('#jang').combobox('getValue');
        var angpk = $('#ang').combobox('getValue');
        var fwdpk = $('#fwdnm').combobox('getValue');
        var emklpk = $('#emklnm').combobox('getValue');
        var gudpk = $('#gudnm').combobox('getValue');

        $('#jang').val(jangpk);
        $('#ang').val(angpk);
        $('#fwdnm').val(fwdpk);
        $('#emklnm').val(emklpk);
        $('#gudnm').val(gudpk);

        var requiredFields = ["spbpk"]; // Add the required field IDs here
        var isValid = true;
        var errorMessage = "Please fill in all required fields:\n";
        requiredFields.forEach(function(fieldId) {
            var field = document.getElementById(fieldId);
            if (!field || !field.value.trim()) {
                isValid = false;
                errorMessage += `- ${fieldId}\n`;
            }
        });
        if (!isValid) {
            alert(errorMessage); // Show error message if validation fails
            return;
        }

        var formData = new FormData(formElement);
        $.ajax({
            method: "POST",
            url: "{{ route('api.edit-save-header-spbdt') }}",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                console.log("Response:", response);
                if (response.status === "success" && response.data && response.data.nobukti) {
                    var nobukti = response.data.nobukti;
                    var spbpk = {!! json_encode($dt_spb->spbpk) !!};
                    $("#exampleModalInfoNoBukti").text("No SPB: " + nobukti);
                    if (nilai === 'submit') {
                        // saverow();
                        $('#dg').datagrid('acceptChanges');
                        hitungTotalJumlahSemua();
                        simpanTotal();
                        saverow();
                        $('#ModalInfoNoBukti').modal('show');
                        setTimeout(function() {
                            $('#ModalInfoNoBukti').modal('hide');
                            // window.location.href = "{{ route('index.spb') }}";
                            var baseUrl = "{{ route('detail.spb', ':spbpk') }}".replace(':spbpk', spbpk);
                            window.location.href = baseUrl;
                        }, 3000);
                    } else {
                        cancel_edit();
                    }
                } else {
                    alert("Failed to retrieve nobukti from server.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error:", xhr);
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    showAlert(400, xhr.responseJSON.message);
                } else {
                    showAlert(400, error);
                }
            }
        });
    }


    function warning() {
        $('#warningtable2').removeClass('d-none');
    }

    function menu() {
        var cp = document.getElementById('copy').value;
        var row = $('#dg').datagrid('getChecked');
        var tchek = $('#dg').datagrid('getChecked').length;
        if (cp == 1) {
            $('#myHeader').show();
            $('#count').hide();
            $('#count2').show();
            $('#mcopy').hide();
            $('#mdelete').hide();
            $('#mclose').hide();
            $('#count2').css('margin-right', '60%');
            edit_data();
        } else {
            if (row) {
                if (tchek > 1) {
                    $('#myHeader').show();
                    $('#count').show();
                    $('#count2').hide();
                    $('#mcopy').show();
                    $('#mdelete').show();
                    $('#count').css('margin-right', '65%');
                    count_row();
                } else {
                    $('#myHeader').show();
                    $('#count').show();
                    $('#count2').hide();
                    $('#mcopy').show();
                    $('#mdelete').show();
                    $('#count').css('margin-right', '65%');
                    count_row();
                }
            } else {
                $('#myHeader').hide();
                $('#dg').datagrid('clearSelections');
            }
        }
    }

    function count_row() {
        var count = $('#dg').datagrid('getChecked').length;
        if (count) {
            $('#myHeader').show();
            document.getElementById("count").innerHTML = count + " items selected";
            // document.getElementById("totreq").value = count;
            // document.getElementById("totreq2").value = count;
        } else {
            $('#myHeader').hide();
            $('#dg').datagrid('clearSelections');
            document.getElementById("count").innerHTML = 0 + " items selected";
        }
    }

    function menu2() {
        var cp = document.getElementById('copy').value;
        if (cp == 1) {
            $('#myHeader').show();
            menu();
        } else {
            $('#myHeader').hide();
        }
    }

    function closemenu() {
        $('#myHeader').hide();
        $('#dg').datagrid('clearSelections'); 
        $('#dg').datagrid('clearChecked');
    }

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

    // Copy Multi row data
    function copy() {
        var spbpk = $('#spbpk').val();
        var rows = $('#dg').datagrid('getSelections');

        if (!rows || rows.length === 0) {
            $('#myHeader').hide();
            return;
        }

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        rows.forEach(function(row) {
            var url = "{{ route('api.spbdt-copy', ':spbdtpk') }}".replace(':spbdtpk', row.spbdtpk);

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    spbdtpk: row.spbdtpk,
                    spbpk: spbpk
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function() {
                    $('#dg').datagrid('reload');
                    $('#dg').datagrid('uncheckAll');
                    menu();
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                }
            });
        });
    }

    function delete_list_spbdt() {
        var selectedRow = $('#dg').datagrid('getSelected');
        if (!selectedRow) {
            return;
        }

        var checkedRows = $('#dg').datagrid('getChecked');
        if (!checkedRows || checkedRows.length === 0) {
            return;
        }

        var ids = checkedRows.map(function(row) {
            return row.spbdtpk;
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        $.messager.confirm('Confirm', 'Are you sure you want to delete the selected items?', function(r) {
            if (r) {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.delete-spbdt') }}",
                    data: {
                        ids: ids
                    },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        $('#dg').datagrid('reload');
                        $('#dg').datagrid('uncheckAll');
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
                        $('#dg').datagrid('reload');
                        $('#dg').datagrid('uncheckAll');
                        $('#myHeader').hide();
                        setTimeout(function() {
                            hitungTotalJumlahSemua();
                            simpanTotal();
                        }, 500);
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
                        $('#dg').datagrid('reload');
                        $('#dg').datagrid('uncheckAll');
                    }
                });
            }
        });
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

    function GoBack() {
        window.location.href = "{{ URL::to('/surat/index')}}";
    }

    function cancel_spb() {
        $('#ConfirmCancel').modal('show');
        $('#dg').datagrid('reload');
    }

    function ConfirmDelete() {
        localStorage.setItem('returnBack', true);
        var url = window.location.pathname;
        var segments = url.split('/');
        var spbpk = $('#spbpk').val();
        var segment3 = segments[2];

        var formData = {
            segment3: segment3,
            spbpk: spbpk,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        console.log('spbpk : ' + spbpk + ' segment : ' + segment3)

        $.ajax({
            type: "POST",
            url: "{{ route('back.spb') }}",
            data: formData,
            success: function(data) {
                console.log("Success:", data);
                window.location.href = "{{ route('index.spb') }}";
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
            }
        });

    }

    function KlikLoopUp(nilai) {
        $('#nilai_btn').val(nilai);
        var totalRows = $('#dg').datagrid('getRows').length;
        if (nilai == "submit") {
            if (!totalRows) {
                $('#warningtable').removeClass('d-none');
                return;
            }
        }
        $('#ModalOrder').modal('show');
    }

    function SearchOrder() {
        $('#dgOrder').datagrid('load', {
            searchByInputOrder: $('#searchByInputOrder').val(),
        });
    }

    function SubmitModalLookup(dgname) {
        var invpk = $('#invpk2').val();
        if (dgname == 'ORDERLIST') {
            var selectedRow = $('#dgOrder').datagrid('getSelected');
            if (!selectedRow) {
                $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }

            var checkedRows = $('#dgOrder').datagrid('getChecked');
            if (!checkedRows || checkedRows.length === 0) {
                $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }
            var rows = $('#dgOrder').datagrid('getChecked');
        }

        var formData = {
            spbpk: $('#spbpk').val(),
            invpk: invpk,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        if (rows.length > 0) {
            console.log("Rows selected: " + rows.length);
            console.log("Selected rows: " + JSON.stringify(rows));
        } else {
            console.log("No rows are selected.");
        }

        formData.rows = rows;

        if (dgname == 'ORDERLIST') {
            url = "{{ route('api.add-lookup-order') }}";
        }

        $.ajax({
            type: "POST",
            url: url,
            data: formData,
            success: function(data) {
                console.log("Success:", data);
                // let invpk = data.inv.invpk ?? null;
                // $('#invpk2').val(invpk);
                // window.location.reload();
                setTimeout(() => $('#dgOrder').datagrid('reload'), 1200);
                $('#dg').datagrid('reload');
            },

             success: function(response, textStatus, xhr) {
                const statusCode = xhr.status;
                // Tunggu modal ke-4 tertutup
                // $('#ModalMultipleData').one('hidden.bs.modal', function() {
                    showAlert(statusCode, response.message);

                // });
                // Tutup modal 4
                setTimeout(() => $('#dgOrder').datagrid('reload'), 1200);
                $('#dg').datagrid('reload');
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
                console.log(xhr);
            }
        });
    }

    function KlikCloseModal() {
        // Kosongkan input pencarian
        $('#searchByInputOrder').val('');

        // Muat ulang data tanpa filter (data default)
        $('#dgOrder').datagrid('load', {
            searchByInputOrder: ''
        });

        // Bersihkan seleksi dan centang
        $('#dgOrder').datagrid('clearSelections').datagrid('clearChecked');
    }

    $(document).ready(function() {
        $('#get_button').prop('disabled', true).removeClass('btn-dark').addClass('btn-secondary');

        // Event listener untuk perubahan checkbox di EasyUI datagrid
        $('#dgOrder').datagrid({
            onCheck: toggleGetButton,
            onUncheck: toggleGetButton,
            onCheckAll: toggleGetButton,
            onUncheckAll: toggleGetButton
        });

        function toggleGetButton() {
            const checked = $('#dgOrder').datagrid('getChecked');
            if (checked.length > 0) {
                $('#get_button').prop('disabled', false).removeClass('btn-secondary').addClass('btn-dark');
            } else {
                $('#get_button').prop('disabled', true).removeClass('btn-dark').addClass('btn-secondary');
            }
        }
    });

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

    function ImportExcel(){
        $('#ModalImportExcel').modal('show');
    }

    function resetFormUpload() {
        document.getElementById('id-form-change-upload').reset();
        $('#ModalImportExcel').modal('hide');
    }

    function onSubmitNewUpload() {
        const fileInput = document.getElementById('uploadfile');
        const file = fileInput.files[0];
        if (!file) {
        alert('Please select a file to upload.');
        return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const formData = new FormData();
        formData.append('uploadfile', file);
        formData.append('_token', csrfToken);
        $.ajax({
        url: "{{ route('api.spb-upload-excel', $dt_spb->spbpk) }}",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        
        success: function(response, textStatus, xhr) {
            const statusCode = xhr.status;
            console.log("HTTP Status Code:", statusCode);
            $('#id-form-change-upload')[0].reset();
            $('#ModalImportExcel').modal('hide');
            showAlert(statusCode, response.message);
            setTimeout(function() {
            location.reload();
            }, 2000);
        },

        error: function(xhr, status, error) {
            console.error("Error detail:", xhr.responseText);
            alert('An error occurred. Please try again.');
        }
        });
    }

    $(document).ready(function() {
        var spbpk = $('#spbpk').val();
        if (spbpk) {
            loadTotalPcs(spbpk);
        }
    });

    function loadTotalPcs(spbpk) {
        var baseUrl = '{{ url('api/get-totalsemua') }}';
        var url = baseUrl + '/' + encodeURIComponent(spbpk);

        console.log(url);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#totalPcs').val(Number(response.totalpcs).toLocaleString('en-US'));
                    $('#totalKar').val(Number(response.totalkar).toLocaleString('en-US'));
                    $('#totalBrt').val(parseFloat(response.totalbrt).toFixed(2));
                    $('#totalKub').val(parseFloat(response.totalkub).toFixed(3));
                } else {
                    $('#totalPcs').text("Gagal menghitung total.");
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", error);
                $('#totalPcs').text("Error mengambil data.");
            }
        });
    }

    function hitungTotalJumlahSemua() {
        var rows = $('#dg').datagrid('getRows');
        var total = 0;
        var totalkar = 0;
        var totalbrt = 0;
        var totalkub = 0;

        for (var i = 0; i < rows.length; i++) {
            var qty = parseFloat(rows[i].jmlsatuan) || 0;
            total += qty;

            var kar = parseFloat(rows[i].jmlkarton) || 0;
            totalkar += kar;

            var brt = parseFloat(rows[i].berat) || 0;
            totalbrt += brt;

            var kub = parseFloat(rows[i].kubikasi) || 0;
            totalkub += kub;

            $('#dg').datagrid('updateRow', {
                index: i,
                row: {
                    total: qty,
                    totalkar: kar,
                    totalbrt: brt,
                    totalkub: kub
                }
            });
        }

        // $('#totalPcs').val(total);
        // $('#totalKar').val(totalkar);
        $('#totalPcs').val(total.toLocaleString('en-US'));
        $('#totalKar').val(totalkar.toLocaleString('en-US'));
        $('#totalBrt').val(totalbrt.toFixed(2));
        $('#totalKub').val(totalkub.toFixed(3));
    }

    function simpanTotal() {
        var spbpk = $('#spbpk').val();
        // var total = $('#totalPcs').val();
        // var totalkar = $('#totalKar').val();
        var total = $('#totalPcs').val().replace(/,/g, '');
        var totalkar = $('#totalKar').val().replace(/,/g, '');
        var totalbrt = $('#totalBrt').val();
        var totalkub = $('#totalKub').val();

        $.ajax({
            url: '{{ route('api.update-totsemua') }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                spbpk: spbpk,
                totpcs: total,
                totkar: totalkar,
                totbrt: totalbrt,
                totkub: totalkub
            },
            success: function(response) {
                if (!response.success) {
                    console.warn('Gagal update total:', response.message);
                }
            },
            error: function(xhr) {
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    }

</script>
@endsection

@section('content')

<div class="main-wrapper min-vh-100">
    <div class="container-fluid">

        <div class="d-flex flex-column p-4 m-3">
            <form id="form_header">
                @csrf
                <input type="hidden" id="spbpk" name="spbpk" value="{{ $dt_spb->spbpk }}">

                <input type="hidden" id="gudpk" name="gudpk" value="">
                <input type="hidden" id="gudpkb" name="gudpkb" value="{{ $dt_spb->gudpk }}">
                
                <input type="hidden" name="nilai_btn" id="nilai_btn" value="">
                <input type="hidden" name="copy" id="copy">
                
                <div class="d-flex gap-3">
                    @if(request()->segment(1) == 'detail-spb')
                    <div class="d-flex pointer" onclick="GoBack()">
                        <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                    </div>
                    @elseif(request()->segment(1) == 'add-spb')
                    <div class="d-flex pointer" onclick="cancel_spb()">
                        <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                    </div>
                    @endif

                    <div class="d-flex flex-column flex-fill">
                        <div class="d-flex justify-content-between">
                            @if( $dt_spb->nobukti != null)
                            <div class="d-flex fw-bold">No. SPB : {{ $dt_spb->nobukti}}</div>
                            @else
                            <div class="d-flex fw-bold ">Input Data SPB</div>
                            @endif

                            <div class="d-flex gap-4 d-none" id="cancel_submit">
                                <button class="btn-transparent border-0 fw-bold" onclick="event.preventDefault(); cancel_spb()">Cancel</button>
                                <button class="btn-black rounded px-3 d-none" id="save_submit" onclick="event.preventDefault(); submit_spb('submit');">Save</button>
                            </div>

                            <div class="d-flex gap-4 d-none" id="edit_data">
                                 @if(Session::get('guserpk') == 7)
                                <button class="btn-black rounded px-3" onclick="event.preventDefault(); edit_data();">Edit</button>
                                @endif
                                <button class="btn-black rounded px-3 d-none" onclick="event.preventDefault(); KlikPrint();"><i class="fa-solid fa-print"></i> Pdf</button>
                            </div>

                            <div class="d-flex gap-4 d-none" id="cancel_save_edit">
                                <button class="btn-transparent border-0 fw-bold" onclick="event.preventDefault(); cancel_edit()">Cancel</button>
                                <button class="btn-black rounded px-3" id="save_edit" onclick="event.preventDefault(); submit_spb('save');">Save</button>
                            </div>
                        </div>


                        <div class="d-flex my-3">
                            <div class="d-flex flex-column flex-fill">

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Tgl SPB</div>
                                    <input id="tgl" name="tgl" class="easyui-datebox" style="width:210px;" placeholder="YYYY-MM-DD" data-options="formatter:myformatter, parser:myparser" value="{{ \Carbon\Carbon::parse($dt_spb->tgl)->format('d/m/Y') }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Jenis Angkutan</div>
                                    <input id="jang" class="easyui-combobox col-2" name="jangpk" method="get" value="{{ $dt_spb->jangpk }}" data-options="valueField:'jangpk', textField:'jangnm', prompt:'{{ addslashes($dt_spb->jangnm) }}', url:'{{ route('api.get-jang') }}', editable:true, limitToList:'true'">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Perusahaan Angkutan</div>
                                    <input id="ang" class="easyui-combobox col-2" style="height: 30px;" name="angpk" method="get" value="{{ $dt_spb->angpk }}" data-options="valueField:'angpk', textField:'angnm', prompt:'{{ addslashes($dt_spb->angnm) }}', url:'{{ route('api.get-ang') }}', editable:true, limitToList:'true'">
                                </div>


                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No Container</div>
                                    <input id="contno" class="easyui-validatebox height_input" name="contno" type="text" value="{{ $dt_spb->contno }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No Seal</div>
                                    <input id="sealno" class="easyui-validatebox height_input" name="sealno" type="text" value="{{ $dt_spb->sealno }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No Truk</div>
                                    <input id="notruk" class="easyui-validatebox height_input" name="notruk" type="text" value="{{ $dt_spb->notruk }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Booking Forwarder</div>
                                    <input id="fwdnm" class="easyui-combobox col-2" name="fwdpk" method="get" value="{{ $dt_spb->fwdpk }}" data-options="valueField:'fwdpk', textField:'fwdnm', prompt:'{{ addslashes($dt_spb->fwdnm) }}', url:'{{ route('api.get-fwd') }}', editable:true, limitToList:'true'">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">EMKL</div>
                                    <input id="emklnm" class="easyui-combobox col-2" name="emklpk" method="get" value="{{ $dt_spb->emklpk }}" data-options="valueField:'emklpk', textField:'emklnm', prompt:'{{ addslashes($dt_spb->emklnm) }}', url:'{{ route('api.get-emkl') }}', editable:true, limitToList:'true'">
                                </div>

                            </div>

                            <div class="d-flex flex-column flex-fill">

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Gudang</div>
                                    <input id="gudnm" class="easyui-combobox col-2" name="gudpk" method="get" value="{{ $dt_spb->gudpk }}" data-options="valueField:'gudpk', textField:'gudnm', prompt:'{{ addslashes($dt_spb->gudnm) }}', url:'{{ route('api.get-gudang') }}', editable:true, limitToList:'true'">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Alamat</div>
                                    <input class="easyui-textbox" multiline="true" style="height:100px; width:210px;" id="alamat" name="alamat" type="text" value="{{ $dt_spb->alamat }}" disabled>
                                </div>


                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Attention</div>
                                    <textarea id="att" name="att" class="easyui-validatebox" style="border-radius: 8px;" rows="4" cols="22">{{ $dt_spb->att }}</textarea>
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Tips Stuffing</div>
                                    <input id="tips" class="easyui-validatebox height_input" name="tips" type="text" value="{{ $dt_spb->tips }}">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Total Pcs</div>
                                    <!-- <input id="totsatuan" class="easyui-validatebox height_input" name="totsatuan" type="text" value="{{ $jml_satuan ?? '' }}"> -->
                                    <input id="totalPcs" class="easyui-validatebox height_input" readonly style="background-color: #f0f0f0; color:#575757; border: 1px solid #aaa; border-radius: 8px; padding: 8px 12px; text-align: left;">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Total Karton</div>
                                    <!-- <input id="totkarton" class="easyui-validatebox height_input" name="totkarton" type="text" value="{{ $jml_karton ?? '' }}"> -->
                                    <input id="totalKar" class="easyui-validatebox height_input" readonly style="background-color: #f0f0f0; color:#575757; border: 1px solid #aaa; border-radius: 8px; padding: 8px 12px; text-align: left;">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Total Berat</div>
                                    <!-- <input id="totberat" class="easyui-validatebox height_input" name="totberat" type="text" value="{{ $jml_berat ?? '' }}"> -->
                                    <input id="totalBrt" class="easyui-validatebox height_input" readonly style="background-color: #f0f0f0; color:#575757; border: 1px solid #aaa; border-radius: 8px; padding: 8px 12px; text-align: left;">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Total Kubikasi</div>
                                    <!-- <input id="totkubikasi" class="easyui-validatebox height_input" name="totkubikasi" type="text" value="{{ $jml_kubikasi ?? '' }}"> -->
                                    <input id="totalKub" class="easyui-validatebox height_input" readonly style="background-color: #f0f0f0; color:#575757; border: 1px solid #aaa; border-radius: 8px; padding: 8px 12px; text-align: left;">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </form>

            <div class="d-flex align-items-center gap-2">

                <div class="d-flex justify-content-end align-items-center gap-3 mb-3">
                    <div id="add" class="p-0 d-none d-flex gap-3">
                        <div class="font-5 color-view-blue pointer fw-bold" onclick="tambahrow();" style="color:#359DD9; text-decoration:none;">Add Data</div>
                        <div class="font-5 blue pointer fw-bold d-none" id="savedt" onclick="saverow();">Save</div>
                    </div>

                    <div id="lookupgis" class="flex-grow-0 d-none">
                        <button onclick="KlikLoopUp()" class="btn btn-sm btn-dark">Lookup Order GIS</button>
                    </div>
                    
                    <div id="importbyexcel" class="flex-grow-0 d-none">
                        <button onclick="ImportExcel();" class="btn btn-sm btn-dark">Import by Excel</button>
                    </div>
                </div>


                <div id="warningtable" class="font-warning pb-3 d-none">
                    *) Belum ada list detail spb pada table list spb. Silahkan lengkapi data dan masukan list spb terlebih dahulu!
                </div>

            </div>

            <div class="p-0">
                <table id="dg" class="easyui-datagrid" title="" align="center" toolbar="#tb" striped="false" pagination="true" method="get"
                    rownumbers="false"  pageList="[100,200,300,500]"  pageSize="100" singleSelect="false" collapsible="true" fitColumns="true" idField="spbdtpk"
                   data-options="onAfterEdit: function(index, row, changes) {hitungTotalJumlahSemua(); simpanTotal();}, onCheck:function(){menu();}, onCheckAll:function(){menu();}, onUncheck:function(){menu();}, onUncheckAll:function(){},
                    multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false,
                    rowStyler:function(index,row){
                        if (row.copy==3){
                            return 'color:#000000;background-color:#D6D6D6;';
                        }
                    }">
                    <thead>
                        <tr>
                            <th field="spbdtpk" hidden="true" formatter="AttrSpbdtpk"></th>
                            <th field="ck" width="auto" styler="styler1" checkbox="true" hidden="true"></th>
                            <th field="noop" width="9%" styler="styler2" editor="text">No OP</th>
                            <th field="orderno" width="13%" styler="styler2" editor="text">Order No</th>
                            <th field="style" width="13%" styler="styler2" editor="text">Style</th>
                            <th field="art" width="13%" styler="styler2" editor="text">Description</th>
                            <th width="10%" styler="styler2" data-options="field:'byrpk',width:100, formatter:function(value,row){ return row.byrnm; },  editor:{ type:'text',}">Nama Pembeli</th>
                            <th field="negara" width="auto" styler="styler2" editor="text">Negara Tujuan</th>
                            <th field="jmlsatuan" width="auto" styler="styler2" editor="text">Jumlah Satuan</th>
                            <th field="jmlkarton" width="auto" styler="styler2" editor="text">Jumlah Karton</th>
                            <th field="berat" width="auto" styler="styler2" editor="text">Berat Bruto</th>
                            <th field="kubikasi" width="auto" styler="styler2" editor="text">Kubikasi</th>
                            <th field="hrgsat" width="auto" styler="styler2" editor="text">Harga Satuan</th>
                            <th field="jmlhrg" width="auto" styler="styler2" editor="text">Jumlah Harga</th>
                            <th field="smarks" width="auto" styler="styler3" editor="text">Shipping Marks</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <div class="header" id="myHeader" onclick="" style="display:none;">
                <div id="count" style="display:none;"></div>

                <div class="delete" id="mcopy" style="width:auto; display:none;">
                    <a href="javascript:void(0)" plain="true" onclick="copy();" style="color: #FFFFFF; text-decoration: none;"> Copy </a>
                </div>

                <div class="delete" id="mdelete" style="width:auto; display:none;">
                    <a href="javascript:void(0)" plain="true" onclick="delete_list_spbdt();" style="color: #FFFFFF; text-decoration: none;"> Delete </a>
                </div>

                <div class="delete" id="mclose" style="width:auto;"><a href="javascript:void(0)" plain="true" onclick="$('#myHeader').hide(); $('#dg').datagrid('clearSelections'); $('#dg').datagrid('clearChecked'); closemenu();" style="color: #FFFFFF; text-decoration: none;"> Close menu </a></div>
            </div>



            <!-- modal pilihan -->
            <!-- <div class="modal fade" id="ModalPilihan" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content p-4 position-relative">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-primary btn-lg" data-bs-target="#ModalOrder" onclick="event.preventDefault(); showOrder();">Lookup Order</button>
                        </div>
                    </div>
                </div>
            </div> -->
            @include('menu.spb.modal.modal-dapat-nospb')
            @include('menu.spb.modal.modal-confirm-cancel')
            @include('menu.spb.modal.modal-import-excel')
            @include('menu.spb.modal.modal-lookup-sig')
        </div>
    </div>
</div>
@endsection