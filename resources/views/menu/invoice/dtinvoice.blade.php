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

        .bg-white {
            color: #000;
            background: #fff;
            border: 1px solid black;
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

        .bg-black {
            color: #fff;
            background: #000;
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

        .color-view-blue {
            color: rgb(53, 157, 217);
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

        /* .datagrid-body {
            overflow-x: hidden;
            overflow-y: hidden;
        } */

          .datagrid-body {
            overflow-y: auto !important;
            max-height: 300px;
            /* Sesuaikan tinggi */
        }

        .text-biru {
            color: #359DD9;
            font-weight: 700;
            cursor: pointer;
        }

        @keyframes blink {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0;
            }

            100% {
                opacity: 1;
            }
        }

        .warning-blink {
            font-size: 12px;
            font-weight: bold;
            color: red;
            text-align: center;
            animation: blink 2s infinite;
        }

        .warning-blink-1 {
            font-size: 14px;
            font-weight: bold;
            color: red;
            text-align: center;
        }

        .font-warning {
            font-size: 12px;
            font-weight: bold;
            color: red;
            text-align: left;
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

        $(function () {
            var pathArray = window.location.pathname.split("/");
            var pathname3 = pathArray[4];

            var nilai = (pathname3 === 'input');
            var invspk = $('#invspk').val();

            $('#dg').edatagrid({
                saveUrl: '{{ route("insert_list_dtinvoice", '') }}/' + invspk,
                updateUrl: '{{ route("update_list_dtinvoice") }}',
                editing: nilai,
                onSuccess: function (index, row) {
                    $(this).edatagrid('saveRow');
                    $(this).datagrid('reload');
                    closemenu();
                    console.log("ROW : " + row.invsdtpk);
                    console.log("nilai : " + nilai);
                    // $('#savedt').addClass('d-none');
                },
                onDblClickRow: function (index, row) {
                    var isEdit = $('#isEdit-' + row.invsdtpk).val();
                    var invsdtpk = row.invsdtpk;

                    if (isEdit == 'true') {
                        document.getElementById('isEdit-' + row.invsdtpk).value = false;
                        hiddenSave(index, invsdtpk);
                        $(this).datagrid('endEdit', index);
                    } else {
                        console.log('onedit : ' + row.invsdtpk);
                        $('#isEdit-' + row.invsdtpk).val('true');

                        if ($(this).edatagrid('options').editing) {
                            $(this).datagrid('beginEdit', index);
                            console.log("Double click edit mode");
                        }
                    }
                },
                onBeforeEdit: function (index, row) {
                    $('#savedt').removeClass('d-none');
                    var token = $('meta[name="csrf-token"]').attr('content');
                    row._token = token;
                },
                onBeforeSave: function (index) {
                    // $('#savedt').removeClass('d-none');
                    var editors = $('#dg').datagrid('getEditors', index);
                    var ponoEditor = editors.find(editor => editor.field === 'pono');
                    if (ponoEditor) {
                        var ponoValue = $(ponoEditor.target).val();
                        if (!ponoValue) {
                            // alert('PO Number is required!');
                            warning();
                            return false; // Prevent saving
                        }
                        $('#savedt').addClass('d-none');
                        $('#warningtable').addClass('d-none');
                        $('#warningtable2').addClass('d-none');
                    }
                }
            });
        });

        function AttrDtgdtpk(value, row) {
            var hiddenSave = '<input type="text" id="isEdit-' + row.invsdtpk + '" value="false">';
            return hiddenSave;
        }

        $(function () {
            var dg = $('#dg').datagrid();
            dg.datagrid('getPanel').find('div.datagrid-header').css('background', 'transparent');
            dg.datagrid('getPanel').find('div.datagrid-header').css('border', '1px solid #858585');
            dg.datagrid('getPanel').find('div.datagrid-header').css('border-radius', '8px');
            dg.datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
            dg.datagrid('getPanel').find('div.datagrid-body').css('overflow-y', 'hidden');
            dg.datagrid('getPanel').css('border', 'none');
            dg.datagrid('getPanel').addClass('lines-no');
            dg.datagrid('getPanel').addClass('lines-no3');
        });

        $(function () {
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
            $('#dgSOM').datagrid('getPanel').css('border', 'none');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
            $('#dgSOM').datagrid('getPanel').find('div.datagrid-header').addClass('col');
            $('#dgSOM').datagrid('getPanel').addClass('lines-no');
            $('#dgSOM').datagrid('getPanel').addClass('lines-no3');
            // $('#dg_tableSOM').datagrid('getPanel').addClass('col');
        });

        $(function () {
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
            $('#dgGIS').datagrid('getPanel').css('border', 'none');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
            $('#dgGIS').datagrid('getPanel').find('div.datagrid-header').addClass('col');
            $('#dgGIS').datagrid('getPanel').addClass('lines-no');
            $('#dgGIS').datagrid('getPanel').addClass('lines-no3');
            // $('#dgGIS').datagrid('getPanel').addClass('col');
        });

        //tambahan untuk menu copy replace
        /*menu*/
        function menu() {
            var cp = document.getElementById('copy').value;
            var row = $('#dg').datagrid('getChecked');
            var rows = $('#dg').datagrid('getSelected');
            var tchek = $('#dg').datagrid('getChecked').length;
            if (cp == 1) {
                $('#myHeader').show();
                $('#count').hide();
                $('#count2').show();
                $('#mreplace1').hide();
                $('#mreplace2').show();
                $('#mcancopy').show();
                $('#mdelete').hide();
                $('#mclose').hide();
                $('#count2').css('margin-right', '55%');
                edit_data();
            } else {
                var cc = [];
                var rows2 = $('#dg').datagrid('getSelections');
                for (var i = 0; i < rows2.length; i++) {
                    var nos = rows2[i]['nos'];
                    if ($.inArray(nos, cc) == -1) { // not found
                        cc.push(nos);
                    }
                }

                if (rows) {
                    if (tchek > 1) {
                        if (cc.length > 1) {
                            $('#myHeader').show();
                            $('#count').show();
                            $('#count2').hide();
                            $('#mreplace1').hide();
                            $('#mtd').hide();
                            $('#mud').hide();
                            $('#mcancopy').hide();
                            $('#mdelete').show();
                            $('#count').css('margin-right', '65%');
                            count_row();
                        } else {
                            $('#myHeader').show();
                            $('#count').show();
                            $('#count2').hide();
                            $('#mreplace1').hide();
                            if (rows.nos == 'T/D') {
                                $('#mtd').hide();
                                $('#mud').show();
                            } else {
                                $('#mtd').show();
                                $('#mud').hide();
                            }
                            $('#mcancopy').hide();
                            $('#mdelete').show();
                            $('#count').css('margin-right', '60%');
                            count_row();
                        }
                    } else {
                        $('#myHeader').show();
                        $('#count').show();
                        $('#count2').hide();
                        $('#mreplace1').show();
                        if (rows.nos == 'T/D') {
                            $('#mtd').hide();
                            $('#mud').show();
                        } else {
                            $('#mtd').show();
                            $('#mud').hide();
                        }
                        $('#mcancopy').hide();
                        $('#mdelete').show();
                        $('#count').css('margin-right', '55%');
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
                document.getElementById("totreq").value = count;
                document.getElementById("totreq2").value = count;
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
            // alert('test');
            $('#myHeader').hide();
            $('#dg').datagrid('clearSelections');
            $('#dg').datagrid('clearChecked');
        }

        $(function () {
            //show hidden kolom checkbox di awal
            var pathArray = window.location.pathname.split("/");
            var pathname3 = pathArray[4];
            if (pathname3 == 'input') {
                $('#dg').datagrid('showColumn', 'ck');
            } else {
                $('#dg').datagrid('hideColumn', 'ck');
            }

            $('#SOMModal').on('shown.bs.modal', function () {
                $('#dgSOM').datagrid('resize');
            })
            $('#GISModal').on('shown.bs.modal', function () {
                $('#dgGIS').datagrid('resize');
                // button_get();
            })
            $('#INVModal').on('shown.bs.modal', function () {
                $('#dgINV').datagrid('resize');
                // button_get();
            })
            $('#DTINVSModal').on('shown.bs.modal', function () {
                $('#dgDTINVS').datagrid('resize');
                // button_get();
            })
        });

        $(function () {
            editDisplay();
            // button_get();
        });

        function editDisplay() {
            var pathArray = window.location.pathname.split("/");
            var pathname3 = pathArray[4];
            var shipno = $('#shipno').val();
            var guserpk = $('#guserpk').val();
            var userpk = $('#userpk').val();

            if (pathname3 == 'edit') {
                var guserpk = $('#guserpk').val();
                var dbnotepk = $('#dbnotepk').val();
                console.log("dbnotepk : ", dbnotepk);

                if (guserpk == 4 || guserpk == 5 || guserpk == 30) {
                    if (dbnotepk > 0) {
                        $('#edit_data').addClass('d-none');
                        $('#print2').removeClass('d-none');
                    } else {
                        //memunculkan edit apabila shipno tidak kosong. 
                        if (shipno > 0) {
                            if (guserpk == 5) {
                                $('#edit_data').removeClass('d-none');
                                $('#print2').addClass('d-none');
                            } else if (guserpk == 30) {
                                var url = window.location.pathname.split('/');
                                var segment1 = url[6];
                                if (segment1 == userpk) {
                                    $('#edit_data').removeClass('d-none');
                                    $('#print2').addClass('d-none');
                                } else {
                                    $('#edit_data').addClass('d-none');
                                    $('#print2').removeClass('d-none');
                                }
                            } else {
                                $('#edit_data').removeClass('d-none');
                                $('#print2').addClass('d-none');
                            }

                        } else {
                            $('#edit_data').removeClass('d-none');
                            $('#print2').addClass('d-none');
                        }
                    }
                } else {
                    if (guserpk == 7) {
                        $('#edit_data').removeClass('d-none');
                        $('#print2').addClass('d-none');
                    } else {
                        $('#edit_data').addClass('d-none');
                        $('#print2').removeClass('d-none');
                    }
                }
                $('#lookup').removeClass('d-none');
                $('#importbyexcel').removeClass('d-none');
                $('#statuspage').val('edit');
                $('#add').addClass('d-none');

                // $('#tglinv').datebox('disable');
                $('#shipto').combobox('disable');
                $('#alamat').textbox('disable');
                $('#telp').attr('disabled', true);
                $('#acc').attr('disabled', true);
                $('#shipno').attr('disabled', true);
                $('#from').attr('disabled', true);
                $('#to').attr('disabled', true);
                $('#tglsend').datebox('disable');
                $('#gw').attr('disabled', true);
                $('#sizebox').attr('disabled', true);
                $('#kurir').attr('disabled', true);
                $('#packtype').combobox('disable');
                $('#qtypack').attr('disabled', true);
                $('#addnew').addClass('d-none');
                $('#lookup').addClass('d-none');
                $('#importbyexcel').addClass('d-none');
                $('#volume').attr('disabled', true);
                $('#nw').attr('disabled', true);

                $('#back_edit').removeClass('d-none');
                $('#back_input').addClass('d-none');

                $('#shipto').combobox({
                    onChange: function () {
                        var shiptopk = $(this).combobox('getValue');
                        var url = "{{ route('shipto', ['shiptopk' => ':shiptopk']) }}";
                        url = url.replace(':shiptopk', shiptopk);
                        $.ajax({
                            type: "GET",
                            url: url,
                            data: {},
                            success: function (data) {
                                var tdata = data.length;
                                if (tdata > 0) {
                                    data.forEach(function (item) {
                                        console.log('Alamat:', item.alamat);
                                        $('#alamat').textbox('setValue', item.alamat);
                                        $('#telp').val(item.telepon);
                                        $('#shiptopk').val(item.shiptopk);
                                        $('#to').val(item.kontakship);
                                        $('#brand').val(item.byrnm);
                                    });
                                } else {
                                    $('#alamat').val('');
                                    $('#telp').val('');
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }
                        });
                        cek_input_header();
                    }
                });

            } else if (pathname3 == 'input') {
                if (guserpk == 5) {
                    if (userpk == 39 || userpk == 46) {
                        $('#shipno').attr('disabled', false);
                        $('#kurir').attr('disabled', false);
                        $('#upload_awb').addClass('d-none');
                        $('#volume').attr('disabled', true);
                        $('#nw').attr('disabled', true);
                    } else {
                        $('#shipno').attr('disabled', false);
                        $('#kurir').attr('disabled', true);
                        $('#upload_awb').addClass('d-none');
                        $('#volume').attr('disabled', true);
                        $('#nw').attr('disabled', true);
                    }
                } else if (guserpk == 7) {
                    $('#shipno').attr('disabled', true);
                    $('#kurir').attr('disabled', true);
                    $('#volume').attr('disabled', false);
                    $('#nw').attr('disabled', false);
                    $('#upload_awb').addClass('d-none');
                } else {
                    $('#shipno').attr('disabled', false);
                    $('#kurir').attr('disabled', false);
                    $('#volume').attr('disabled', true);
                    $('#nw').attr('disabled', true);
                    $('#upload_awb').addClass('d-none');
                }
                $('#cancel_submit').removeClass('d-none');
                $('#statuspage').val('input');
                $('#add').removeClass('d-none');

                $('#back_edit').addClass('d-none');
                $('#back_input').removeClass('d-none');

                // shipto
                $('#shipto').combobox({
                    onChange: function () {
                        var shiptopk = $(this).combobox('getValue');
                        var url = "{{ route('shipto', ['shiptopk' => ':shiptopk']) }}";
                        url = url.replace(':shiptopk', shiptopk);
                        $.ajax({
                            type: "GET",
                            url: url,
                            data: {},
                            success: function (data) {
                                var tdata = data.length;
                                if (tdata > 0) {
                                    data.forEach(function (item) {
                                        console.log('Alamat:', item.alamat);
                                        $('#alamat').textbox('setValue', item.alamat);
                                        $('#telp').val(item.telepon);
                                        $('#shiptopk').val(item.shiptopk);
                                        $('#to').val(item.kontakship);
                                        $('#brand').val(item.byrnm);
                                    });
                                } else {
                                    $('#alamat').val('');
                                    $('#telp').val('');
                                }
                            },
                            error: function (xhr) {
                                console.log(xhr.responseText);
                            }
                        });
                        cek_input_header();
                    }
                });
            } else {
                $('#cancel_submit').addClass('d-none');
                $('#edit_data').addClass('d-none');
                $('#statuspage').val('none');
            }
        }

        function edit_data() {
            var guserpk = $('#guserpk').val();
            var userpk = $('#userpk').val();
            var resi = $('#nilairesi').val();
            $('#edit_data').addClass('d-none');
            $('#cancel_save_edit').removeClass('d-none');

            if (guserpk == 4) {
                // $('#tglinv').datebox('enable');
                $('#shipto').combobox('enable');
                $('#alamat').textbox('disable');
                $('#telp').attr('disabled', false);
                $('#acc').attr('disabled', false);
                $('#from').attr('disabled', false);
                $('#to').attr('disabled', false);
                $('#tglsend').datebox('enable');
                $('#gw').attr('disabled', false);
                $('#sizebox').attr('disabled', false);
                $('#packtype').combobox('enable');
                $('#qtypack').attr('disabled', false);
                $('#addnew').removeClass('d-none');
                $('#lookup').removeClass('d-none');
                $('#importbyexcel').removeClass('d-none');
                $('#shipno').attr('disabled', false);
                $('#kurir').attr('disabled', false);
                $('#upload_awb').addClass('d-none');
                $('#add').removeClass('d-none');
                $('#dg').edatagrid('options').editing = true;
                $('#dg').datagrid('showColumn', 'ck');
                $('#volume').attr('disabled', true);
                $('#nw').attr('disabled', true);
            } else if (guserpk == 7) {
                $('#shipto').combobox('disable');
                $('#alamat').textbox('disable');
                $('#telp').attr('disabled', true);
                $('#acc').attr('disabled', true);
                $('#from').attr('disabled', true);
                $('#to').attr('disabled', true);
                $('#tglsend').datebox('disable');
                $('#gw').attr('disabled', true);
                $('#sizebox').attr('disabled', true);
                $('#packtype').combobox('disable');
                $('#qtypack').attr('disabled', true);
                $('#addnew').addClass('d-none');
                $('#lookup').addClass('d-none');
                $('#importbyexcel').addClass('d-none');
                $('#shipno').attr('disabled', true);
                $('#kurir').attr('disabled', true);
                $('#upload_awb').addClass('d-none');
                $('#add').addClass('d-none');
                $('#dg').edatagrid('options').editing = false;
                $('#dg').datagrid('hideColumn', 'ck');
                $('#volume').attr('disabled', false);
                $('#nw').attr('disabled', false);
            } else {
                if (userpk == 39 || userpk == 46 || userpk == 24) {
                    // $('#tglinv').datebox('enable');
                    $('#shipto').combobox('enable');
                    $('#alamat').textbox('disable');
                    $('#telp').attr('disabled', true);
                    $('#acc').attr('disabled', false);
                    $('#from').attr('disabled', false);
                    $('#to').attr('disabled', true);
                    $('#tglsend').datebox('enable');
                    $('#gw').attr('disabled', false);
                    $('#sizebox').attr('disabled', false);
                    $('#packtype').combobox('enable');
                    $('#qtypack').attr('disabled', false);
                    $('#addnew').removeClass('d-none');
                    $('#lookup').removeClass('d-none');
                    $('#importbyexcel').removeClass('d-none');
                    $('#shipno').attr('disabled', false);
                    $('#kurir').attr('disabled', false);
                    $('#upload_awb').addClass('d-none');
                    $('#add').removeClass('d-none');
                    $('#dg').edatagrid('options').editing = true;
                    $('#dg').datagrid('showColumn', 'ck');
                    $('#volume').attr('disabled', true);
                    $('#nw').attr('disabled', true);
                } else {
                    // $('#tglinv').datebox('enable');
                    $('#shipto').combobox('disable');
                    $('#alamat').textbox('disable');
                    $('#telp').attr('disabled', true);
                    $('#acc').attr('disabled', true);
                    $('#from').attr('disabled', true);
                    $('#to').attr('disabled', true);
                    $('#tglsend').datebox('disable');
                    $('#gw').attr('disabled', true);
                    $('#sizebox').attr('disabled', true);
                    $('#packtype').combobox('disable');
                    $('#qtypack').attr('disabled', true);
                    $('#addnew').addClass('d-none');
                    $('#lookup').addClass('d-none');
                    $('#importbyexcel').addClass('d-none');
                    $('#shipno').attr('disabled', false);
                    $('#kurir').attr('disabled', true);
                    $('#upload_awb').removeClass('d-none');
                    $('#add').addClass('d-none');
                    $('#dg').edatagrid('options').editing = false;
                    $('#dg').datagrid('hideColumn', 'ck');
                    $('#volume').attr('disabled', true);
                    $('#nw').attr('disabled', true);

                    if (resi == "1") {
                        $('#rplcresi').removeClass('d-none');
                    } else {
                        $('#upldresi').removeClass('d-none');
                    }
                }

            }

        }

        function cancel_edit() {
            var shiptopk = $('#shiptopkb').val();
            $('#shipto').combobox('setValue', shiptopk);
            var url = "{{ route('shipto', ['shiptopk' => ':shiptopk']) }}";
            url = url.replace(':shiptopk', shiptopk);
            $.ajax({
                type: "GET",
                url: url,
                data: {},
                success: function (data) {
                    var tdata = data.length;
                    if (tdata > 0) {
                        data.forEach(function (item) {
                            console.log('Alamat:', item.alamat);
                            $('#alamat').textbox('setValue', item.alamat);
                            // $('#telp').val(item.telepon);
                            $('#shiptopk').val(item.shiptopk);
                            // $('#to').val(item.kontakship);
                            $('#brand').val(item.byrnm);
                        });
                    } else {
                        $('#alamat').val('');
                        $('#telp').val('');
                    }
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                }
            });

            $('#cancel_save_edit').addClass('d-none');
            $('#add').addClass('d-none');
            // $('#myHeader').hide();
            var pathArray = window.location.pathname.split("/");
            var pathname3 = pathArray[4];
            if (pathname3 == 'edit') {
                $('#edit_data').removeClass('d-none');
            } else {
                $('#edit_data').addClass('d-none');
            }
            $('#statuspage').val('edit');

            $('#dg').edatagrid('options').editing = false;
            $('#dg').datagrid('hideColumn', 'ck');

            var rows = $('#dg').datagrid('getRows');
            for (var i = 0; i < rows.length; i++) {
                $('#dg').datagrid('endEdit', i);
            }

            // $('#tglinv').datebox('disable');
            $('#shipto').combobox('disable');
            $('#alamat').textbox('disable');
            $('#telp').attr('disabled', true);
            $('#acc').attr('disabled', true);
            $('#shipno').attr('disabled', true);
            $('#from').attr('disabled', true);
            $('#to').attr('disabled', true);
            $('#tglsend').datebox('disable');
            $('#gw').attr('disabled', true);
            $('#sizebox').attr('disabled', true);
            $('#kurir').attr('disabled', true);
            $('#packtype').combobox('disable');
            $('#qtypack').attr('disabled', true);
            $('#addnew').addClass('d-none');
            $('#lookup').addClass('d-none');
            $('#importbyexcel').addClass('d-none');
            $('#upload_awb').addClass('d-none');
            $('#volume').attr('disabled', true);
        }

        function showSOM() {
            $('#ChooseModal').modal('hide');
            $('#SOMModal').modal('show');
        }

        function showGIS() {
            $('#ChooseModal').modal('hide');
            $('#GISModal').modal('show');
        }

        function showINV() {
            $('#ChooseModal').modal('hide');
            $('#INVModal').modal('show');
        }

        function SearchSOM() {
            $('#dgSOM').datagrid('load', {
                searchByInputSOM: $('#searchByInputSOM').val(),
            });
        }

        function SearchGIS() {
            $('#dgGIS').datagrid('load', {
                searchByInputGIS: $('#searchByInputGIS').val(),
            });
        }

        function SearchINV() {
            $('#dgINV').datagrid('load', {
                searchByInputINV: $('#searchByInputINV').val(),
            });
        }

        function invsdt(dgname) {
            if (dgname == 'SOM') {
                var selectedRow = $('#dgSOM').datagrid('getSelected');
                if (!selectedRow) {
                    $.messager.alert('Warning', 'No data selected', 'warning');
                    return;
                }

                var checkedRows = $('#dgSOM').datagrid('getChecked');
                if (!checkedRows || checkedRows.length === 0) {
                    $.messager.alert('Warning', 'No data selected', 'warning');
                    return;
                }
                var rows = $('#dgSOM').datagrid('getChecked');
            } else if (dgname == 'GIS') {
                var selectedRow = $('#dgGIS').datagrid('getSelected');
                if (!selectedRow) {
                    $.messager.alert('Warning', 'No data selected', 'warning');
                    return;
                }

                var checkedRows = $('#dgGIS').datagrid('getChecked');
                if (!checkedRows || checkedRows.length === 0) {
                    $.messager.alert('Warning', 'No data selected', 'warning');
                    return;
                }
                var rows = $('#dgGIS').datagrid('getChecked');
            } else {
                var selectedRow = $('#dgDTINVS').datagrid('getSelected');
                if (!selectedRow) {
                    $.messager.alert('Warning', 'No data selected', 'warning');
                    return;
                }

                var checkedRows = $('#dgDTINVS').datagrid('getChecked');
                if (!checkedRows || checkedRows.length === 0) {
                    $.messager.alert('Warning', 'No data selected', 'warning');
                    return;
                }
                var rows = $('#dgDTINVS').datagrid('getChecked');
            }


            var formData = {
                invspk: $('#invspk').val(),
                shipto: $('#shipto').val(),
                acc: $('#acc').val(),
                shipno: $('#shipno').val(),
                tglsend: $('#tglsend').val(),
                gw: $('#gw').val(),
                sizebox: $('#sizebox').val(),
                from: $('#from').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            if (rows.length > 0) {
                console.log("Rows selected: " + rows.length);
                console.log("Selected rows: " + JSON.stringify(rows));
            } else {
                console.log("No rows are selected.");
            }

            formData.rows = rows;

            if (dgname == 'SOM') {
                url = "{{ route('get_lookup_som') }}";
            } else if (dgname == 'GIS') {
                url = "{{ route('get_lookup_gis') }}";
            } else {
                url = "{{ route('get_lookup_invsdt') }}";
            }

            $.ajax({
                type: "POST",
                url: url,
                data: formData,
                success: function (data) {
                    console.log("Success:", data);
                    window.location.reload();
                    // $('#dg').datagrid('reload');

                    // cek_input_header(data.total_dtgdt);
                },
                error: function (xhr, status, error) {
                    console.log("Error:", error);
                    console.log(xhr);
                }
            });
        }

        $(function () {
            var invspk = $('#invspk').val(); // Make sure this input has a value
            var url = "{{ route('detail_invoice', ['invspk' => ':invspk']) }}";
            url = url.replace(':invspk', invspk);

            $('#dg').datagrid({
                url: url,
                method: 'get',
                onLoadSuccess: function (data) {
                    console.log('Data loaded successfully:', data);
                },
                onLoadError: function () {
                    console.error('Failed to load data.');
                }
            });
        });

        function tambahrow() {
            $('#dg').edatagrid('saveRow');
            $('#dg').edatagrid('addRow');
            var index = $('#dg').edatagrid('getRows').length - 1; // the editing row index
            var ed = $('#dg').edatagrid('getEditors', index)[0]; // get the first editor
            $(ed.target).focus();
        }

        function saverow() {
            $('#dg').edatagrid('saveRow');
            closemenu();
        }

        $(function () {
            cek_input_header();
        })

        $(document).ready(function () {
            $('#to').on('input', cek_input_header);
        });

        function cek_input_header() {
            var pathArray = window.location.pathname.split("/");
            var pathname3 = pathArray[4];

            // var tglinv = $('#tglinv').val().trim();
            var shipto = $('#shipto').val().trim();
            var alamat = $('#alamat').val().trim();
            var shipno = $('#shipno').val().trim();
            var tglsend = $('#tglsend').val().trim();
            var from = $('#from').val().trim();
            var gw = $('#gw').val().trim();
            var sizebox = $('#sizebox').val().trim();
            var to = $('#to').val().trim();


            var baris = $('#dg').datagrid('getData').total;

            if (pathname3 == 'input') {
                //if (shipto && alamat && shipno && to) {
                $('#save_submit').removeClass('d-none');
                // } else {
                //     $('#save_submit').addClass('d-none');
                // }
            } else {
                // if (shipto && alamat && shipno && to) {
                $('#save_edit').removeClass('d-none');
                // } else {
                //     $('#save_edit').addClass('d-none');
                // }
            }
        }

        function submit_invs(nilai) {
            $('#nilai_btn').val(nilai);
            var totalRows = $('#dg').datagrid('getRows').length;
            if (nilai == "submit") {
                if (!totalRows) {
                    $('#warningtable').removeClass('d-none');
                    return;
                }
            }
            var formElement = document.getElementById("form_invs");
            var requiredFields = ["invspk"]; // Add the required field IDs here
            var isValid = true;
            var errorMessage = "Please fill in all required fields:\n";

            requiredFields.forEach(function (fieldId) {
                var field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    isValid = false;
                    errorMessage += `- ${fieldId}\n`;
                }
            });

            if (!isValid) {
                return;
            }

            var formData = new FormData(formElement);

            $.ajax({
                method: "POST",
                url: "{{ route('submit_invs') }}",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function (response) {
                    console.log("COBA RESPONSE" + JSON.stringify(response));

                    if (nilai == 'submit') {
                        window.location.href = "{{ route('list.invoice') }}";
                    } else {
                        // window.location.reload();
                        $('#shiptopkb').val(response.shiptopk)
                        cancel_edit();

                    }
                },
                error: function (xhr, status, error) {
                    console.log("error add cuti HERE >>>>>>>>>>>>>>>> " + JSON.stringify(xhr));

                    if (xhr.responseJSON && xhr.responseJSON.message) showAlert(400, xhr.responseJSON.message);
                    else showAlert(400, JSON.stringify(error))
                }
            });
        }

        function submit_invs_lookup() {
            // $('#nilai_btn').val(nilai);
            // var totalRows = $('#dg').datagrid('getRows').length;
            // if(nilai == "submit"){
            //     if(!totalRows){
            //         $('#warningtable').removeClass('d-none');
            //         return;
            //     }
            // }
            var formElement = document.getElementById("form_invs");
            var requiredFields = ["invspk"]; // Add the required field IDs here
            var isValid = true;
            var errorMessage = "Please fill in all required fields:\n";

            requiredFields.forEach(function (fieldId) {
                var field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    isValid = false;
                    errorMessage += `- ${fieldId}\n`;
                }
            });

            if (!isValid) {
                return;
            }

            var formData = new FormData(formElement);

            $.ajax({
                method: "POST",
                url: "{{ route('submit_invs') }}",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,
                success: function (response) {
                    console.log("COBA RESPONSE" + JSON.stringify(response));

                    // if (nilai == 'submit') {
                    //     window.location.href = "{{ route('list.invoice') }}";
                    // } else {
                    //     // window.location.reload();
                    //     $('#shiptopkb').val(response.shiptopk)
                    //     cancel_edit();

                    // }
                },
                error: function (xhr, status, error) {
                    console.log("error add cuti HERE >>>>>>>>>>>>>>>> " + JSON.stringify(xhr));

                    if (xhr.responseJSON && xhr.responseJSON.message) showAlert(400, xhr.responseJSON.message);
                    else showAlert(400, JSON.stringify(error))
                }
            });
        }

        function back_input_barang() {
            $('#ConfirmCancel').modal('show');
            $('#dg').datagrid('reload');
        }

        function ConfirmDelete() {
            localStorage.setItem('returnBack', true);
            // localStorage.setItem('returnBack', true);
            var url = window.location.pathname;
            var segments = url.split('/');

            var invspk = $('#invspk').val();

            var segment3 = segments[4];

            var formData = {
                segment3: segment3,
                invspk: invspk,
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            console.log('invspk : ' + invspk + ' segment : ' + segment3)

            $.ajax({
                type: "POST",
                url: "{{ route('back_listinvs') }}",
                data: formData,
                success: function (data) {
                    console.log("Success:", data);
                    window.location.href = "{{ route('list.invoice') }}";
                },
                error: function (xhr, status, error) {
                    console.log("Error:", error);
                }
            });

        }

        function modaladdship() {
            $('#addshipto').modal('show');
        }

        function add_shipto() {
            var formElement = document.getElementById("form_add_shipto");

            var requiredFields = ["shipnm", "shipalamat", "shipnegara", "shipkontak"];
            var isValid = true;
            var errorMessage = "Please fill in all required fields:\n";

            requiredFields.forEach(function (fieldId) {
                var field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    isValid = false;
                    errorMessage += `- ${fieldId}\n`;
                }
            });

            if (!isValid) {
                return;
            }

            var formData = new FormData(formElement);

            $.ajax({
                method: "POST",
                url: "{{ route('insert_shipto') }}",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,

                success: function (response) {
                    $('#addshipto').modal('hide');
                    window.location.reload();
                    // console.log("COBA RESPONSE" + JSON.stringify(response));
                    // $('#addshipto').modal('hide');
                    // $('#shipto').combobox('setValue', response.shiptopk);
                    // $('#alamat').textbox('setValue', response.alamat);
                    // $('#telp').val(response.telepon);
                    // $('#shiptopk').val(response.shiptopk);
                    // $('#to').val(response.kontakship);
                    // $('#brand').val(response.byrnm);
                },
                error: function (xhr, status, error) {
                    console.log("error add cuti HERE >>>>>>>>>>>>>>>> " + JSON.stringify(xhr));

                    if (xhr.responseJSON && xhr.responseJSON.message) showAlert(400, xhr.responseJSON.message);
                    else showAlert(400, JSON.stringify(error))
                }
            });
        }


        function modaleditship() {
            var shiptopk = $('#shipto').val();
            if (shiptopk) {
                var url = "{{ route('shipto', ['shiptopk' => ':shiptopk']) }}";
                url = url.replace(':shiptopk', shiptopk);
                $.ajax({
                    type: "GET",
                    url: url,
                    data: {},
                    success: function (data) {
                        $('#editshipto').modal('show');
                        $('#shiptopkedt').val(shiptopk);
                        var tdata = data.length;
                        if (tdata > 0) {
                            data.forEach(function (item) {
                                console.log('Alamat:', item.alamat);
                                $('#shipbyredt').combobox('setValue', item.byrpk)
                                $('#shipalamatedt').textbox('setValue', item.alamat);
                                $('#shiptonmedt').val(item.shiptonm);
                                $('#shipnegaraedt').val(item.negara);
                                $('#shipphoneedt').val(item.telepon);
                                $('#shipkontakedt').val(item.kontakship);
                                // $('#brand').val(item.byrnm);
                            });
                        } else {
                            $('#alamat').val('');
                            $('#telp').val('');
                        }
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                    }
                });

            } else {
                $.messager.alert('Warning', 'Pilih shipment to yang akan di edit', 'warning');
                return;
            }
        }

        function edit_shipto() {
            var formElement = document.getElementById("form_edit_shipto");

            var requiredFields = ["shipbyredt", "shiptopkedt", "shiptonmedt", "shipalamatedt", "shipnegaraedt", "shipphoneedt", "shipkontakedt"];
            var isValid = true;
            var errorMessage = "Please fill in all required fields:\n";

            requiredFields.forEach(function (fieldId) {
                var field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    isValid = false;
                    errorMessage += `- ${fieldId}\n`;
                }
            });

            if (!isValid) {
                return;
            }

            var formData = new FormData(formElement);

            $.ajax({
                method: "POST",
                url: "{{ route('edit_shipto') }}",
                data: formData,
                processData: false,
                contentType: false,
                cache: false,

                success: function (response) {
                    $('#editshipto').modal('hide');
                    window.location.reload();
                },
                error: function (xhr, status, error) {
                    console.log("error add cuti HERE >>>>>>>>>>>>>>>> " + JSON.stringify(xhr));

                    if (xhr.responseJSON && xhr.responseJSON.message) showAlert(400, xhr.responseJSON.message);
                    else showAlert(400, JSON.stringify(error))
                }
            });
        }


        function delete_list_invsdt() {
            var selectedRow = $('#dg').datagrid('getSelected');
            if (!selectedRow) {
                // $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }

            var checkedRows = $('#dg').datagrid('getChecked');
            if (!checkedRows || checkedRows.length === 0) {
                // $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }

            var ids = checkedRows.map(function (row) {
                return row.invsdtpk; // Ambil dtgdtpk dari setiap baris yang dicentang
            });

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Tambahkan dialog konfirmasi
            $.messager.confirm('Confirm', 'Are you sure you want to delete the selected items?', function (r) {
                if (r) {
                    // Jika pengguna mengkonfirmasi penghapusan
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('delete_invsdt') }}",
                        data: {
                            ids: ids
                        }, // Kirim sebagai array
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function (response) {
                            $('#dg').datagrid('reload');
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
                            $('#myHeader').hide();
                        },
                        error: function (xhr, status, error) {
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
                        }
                    });
                }
            });
        }

        function replace1() {
            var invspk = $('#invspk').val();
            var row = $('#dg').datagrid('getSelected');
            if (!row) return $('#myHeader').hide();

            var url = "{{ route('update_copy', ':invsdtpk') }}".replace(':invsdtpk', row.invsdtpk);

            // Ambil CSRF token dari meta tag
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            $.ajax({
                type: "POST",
                url: url,
                data: {
                    invsdtpk: row.invsdtpk,
                    invspk: invspk
                },
                headers: {
                    'X-CSRF-TOKEN': csrfToken // Sertakan token CSRF
                },
                success: function () {
                    $('#dg').datagrid('reload');
                    $('#dg').datagrid('uncheckAll');
                    menu();
                },
                error: function (xhr, status, error) {
                    console.error("Error:", error);
                }
            });
        }

        //STICKY
        window.onscroll = function () {
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

        function warning() {
            // $('#warning').modal('show');
            $('#warningtable2').removeClass('d-none');
        }

        function formatter1(value, row) {
            return `<div class='blue pointer' onclick='list_invsdt(${row.invspk})'>Select</div>`;
        }

        function list_invsdt(invspk) {
            var url = "{{ route('detail_invoice', ['invspk' => ':invspk']) }}";
            url = url.replace(':invspk', invspk);
            $('#dgDTINVS').datagrid({
                url: url,
                method: 'GET',
                onLoadSuccess: function (data) {
                    $('#INVModal').modal('hide');
                    $('#DTINVSModal').modal('show');
                },
                onLoadError: function () {
                    console.log("Gagal memuat data.");
                }
            });
        }

        function cetak(nilai) {
            var invspk = $('#invspk').val();
            let url = "{{ route('get_invoice_cetak_pdf', ['invspk' => ':invspk', 'nilai' => ':nilai']) }}"
                .replace(':invspk', encodeURIComponent(invspk))
                .replace(':nilai', encodeURIComponent(nilai));
            window.open(url, '_blank');
        }

        function onmodalresi() {
            $('#Modalresi').modal('show');
            // var kurir = $('#kurir').val();
            var awb = $('#shipno').val();
            // $('#kurirresi').val(kurir);
            $('#awbresi').val(awb);
        }

        function onupresi() {
            var resifile = document.getElementById('resi').files[0].name;
            document.getElementById('namafileresi').value = resifile;
            $('#resiupdiv').removeClass('d-none');
        }

        function onrouteresi() {
            var formElement = document.getElementById("ResiForm");
            var formData = new FormData(formElement);

            $.ajax({
                type: 'POST',
                url: '{{ route("resi") }}',
                data: formData,
                processData: false,
                contentType: false,
                cache: false,

                success: function (data) {
                    $.messager.show({
                        title: '',
                        msg: '<div class="text-infohijau">Success updated &#10004; </div>',
                        timeout: 3000,
                        showType: 'slide',
                        icon: 'info',
                        width: 252,
                        height: 55,
                        style: {
                            left: 0,
                            right: '',
                            top: '',
                            bottom: -document.body.scrollTop - document.documentElement.scrollTop
                        }
                    });
                    $('#Modalresi').modal('hide');
                    $('#ResiForm').form('reset');
                    // window.location.reload();
                    if (data.nilairesi == 1) {
                        $('#viewfile2').val(data.file);
                        $('#rplcresi').removeClass('d-none');
                        $('#upldresi').addClass('d-none');
                    } else {
                        $('#upldresi').addClass('d-none');
                    }
                }
            });
        }

        function onresi() {
            // alert('test');
            var fileku = $('#viewfile2').val();
            var fileUrl = "{{ asset('public/resi') }}/" + fileku;
            window.open(fileUrl, '_blank', 'fullscreen=yes');
            return false;
        }

        function set_td(nilai) {
            var selectedRow = $('#dg').datagrid('getSelected');
            if (!selectedRow) {
                // $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }

            var checkedRows = $('#dg').datagrid('getChecked');
            if (!checkedRows || checkedRows.length === 0) {
                // $.messager.alert('Warning', 'No data selected', 'warning');
                return;
            }

            var ids = checkedRows.map(function (row) {
                return row.invsdtpk; // Ambil dtgdtpk dari setiap baris yang dicentang
            });

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Tambahkan dialog konfirmasi
            $.messager.confirm('Confirm', 'Are you sure to change status the selected items?', function (r) {
                if (r) {
                    // Jika pengguna mengkonfirmasi penghapusan
                    $.ajax({
                        type: 'POST',
                        url: "{{ route('status_td') }}",
                        data: {
                            ids: ids,
                            nilai: nilai
                        }, // Kirim sebagai array
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function (response) {
                            $('#dg').datagrid('reload');
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
                            $('#myHeader').hide();
                            closemenu();
                        },
                        error: function (xhr, status, error) {
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
                        }
                    });
                }
            });
        }

        function styler1(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;border-left:1px solid #ededed;border-top-left-radius:5px;' +
                'border-bottom-left-radius:5px;height:35px;';
        }

        function styler2(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;';
        }

        function styler3(index, row) {
            return 'border-top:1px solid #ededed;border-bottom:3px solid #ededed !important;border-right:1px solid #ededed !important;border-top-right-radius:5px;' +
                'border-bottom-right-radius:5px;';
        }

        function validate_import() {
            var totalRows = $('#dg').datagrid('getRows').length;
            if (!totalRows) {
                onSubmitNewUpload();
            } else {
                $('#validate-import-excel').modal('show');
                $('#ModalImportExcel').modal('hide');
                return;
            }
        }

        function cancel_upload() {
            $('#validate-import-excel').modal('hide');
            $('#ModalImportExcel').modal('show');
        }

        function ImportExcel() {
            $('#ModalImportExcel').modal('show');
        }

        function resetFormUpload() {
            document.getElementById('id-form-change-upload').reset();
            $('#ModalImportExcel').modal('hide');
        }

        function onSubmitNewUpload() {
            submit_invs_lookup();
            var invspk = $('#invspk').val();

            //IMPORT FROM Excel
            const fileInput = document.getElementById('uploadfile');
            const file = fileInput.files[0];
            if (!file) {
                $.messager.alert('Warning', 'Please select a file to upload', 'warning');
                return;
            }

            let url_delete = "{{ route('api.invoice-delete-excel', ['invspk' => ':invspk']) }}"
                .replace(':invspk', encodeURIComponent(invspk));

            $.ajax({
                url: url_delete,
                type: 'POST',
                success: function (response) {

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    let url = "{{ route('api.invoice-upload-excel', ['invspk' => ':invspk']) }}"
                        .replace(':invspk', encodeURIComponent(invspk));

                    const formData = new FormData();
                    formData.append('uploadfile', file);
                    formData.append('_token', csrfToken);
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,

                        success: function (response, textStatus, xhr) {
                            const statusCode = xhr.status;
                            console.log("HTTP Status Code:", statusCode);
                            $('#id-form-change-upload')[0].reset();
                            $('#ModalImportExcel').modal('hide');
                            $('#validate-import-excel').modal('hide');
                            showAlert(statusCode, response.message);
                            setTimeout(function () {
                                location.reload();
                            }, 2000);
                        },

                        error: function (xhr, status, error) {
                            console.error("Error detail:", xhr.responseText);
                            alert('An error occurred. Please try again.');
                        }
                    });

                },

                error: function (xhr, status, error) {
                    console.error("Error detail:", xhr.responseText);
                    alert('An error occurred. Please try again.');
                }
            });
        }
    </script>
@endsection

@section('content')

    <div class="main-wrapper min-vh-100">
        <div class="container-fluid">
            <div class="d-flex flex-column p-4 m-3">

                <!-- FORM ATAS -->
                <form id="form_invs">
                    @csrf
                    <input type="hidden" id="invspk" name="invspk" value="{{ $invspk }}">
                    <input type="hidden" id="shiptopk" name="shiptopk" value="">
                    <input type="hidden" id="shiptopkb" name="shiptopkb" value="{{ $shiptopk }}">
                    <input type="hidden" id="statuspage" name="statuspage">
                    <input type="hidden" name="copy" id="copy">
                    <input type="hidden" name="brand" id="brand" value="{{ $byrnm }}">
                    <input type="hidden" name="dbnotepk" id="dbnotepk" value="{{ $dbnotepk }}">
                    <input type="hidden" name="guserpk" id="guserpk" value="{{ Session::get('guserpk') }}">
                    <input type="hidden" name="userpk" id="userpk" value="{{ Session::get('userpk') }}">
                    <input type="hidden" name="nilairesi" id="nilairesi" value="{{ $nilairesi }}">
                    <input type="hidden" name="model" id="model" value="{{ request()->segment(3) }}">
                    <input type="hidden" name="nilai_btn" id="nilai_btn" value="">

                    <div class="d-flex gap-3">
                        <div id="back_edit" class="d-flex pointer" onclick="ConfirmDelete()"><img
                                src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                        </div>
                        <div id="back_input" class=" d-none d-flex pointer" onclick="back_input_barang()"><img
                                src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                        </div>
                        <div class="d-flex flex-column flex-fill">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex fw-bold ">Input Data Invoice</div>
                                <div class="d-flex gap-4 d-none" id="cancel_submit">
                                    <button class="btn-transparent border-0 fw-bold"
                                        onclick="event.preventDefault(); back_input_barang()">Cancel</button>
                                    <button class="btn-black rounded px-3 d-none" id="save_submit"
                                        onclick="event.preventDefault(); submit_invs('submit');">Submit</button>
                                </div>
                                <div class="d-flex gap-4 d-none" id="edit_data">
                                    <button class="btn-black rounded px-5" id="edit_btn"
                                        onclick="event.preventDefault(); edit_data();">Edit</button>
                                    <button class="btn-black rounded px-5"
                                        onclick="event.preventDefault(); cetak('1');">Pdf</button>
                                </div>
                                <div class="d-flex gap-4 d-none" id="print2">
                                    <button class="btn-black rounded px-5"
                                        onclick="event.preventDefault(); cetak('1');">Pdf</button>
                                </div>
                                <div class="d-flex gap-4 d-none" id="cancel_save_edit">
                                    <button class="btn-transparent border-0 fw-bold"
                                        onclick="event.preventDefault(); cancel_edit()">Cancel</button>
                                    <button class="btn-black rounded px-5 d-none" id="save_edit"
                                        onclick="event.preventDefault(); submit_invs('save');">Save</button>
                                </div>
                            </div>
                            <div class="d-flex my-3">
                                <div class="d-flex flex-column flex-fill">
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Tgl. Invoice</div>
                                        <div>: {{ \Carbon\Carbon::parse($tglinvs)->format('d/m/Y') }} </div>
                                        <!-- <input id="tglinv" name="tglinv" class="easyui-datebox" style="width:210px;" placeholder="YYYY-MM-DD"
                                            data-options="formatter:myformatter, parser:myparser" value="{{ $tglinvs }}" disable="true"> -->
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Ship To</div>
                                        <select name="shipto" id="shipto" class="easyui-combobox col-2"
                                            data-options="editable:true, prompt:'Ship to', limitToList:'true', onChange: function () { cek_input_header(); }">
                                            <option value="{{ $shiptopk }}">{{ $shiptonm }}</option>
                                            @foreach ($shipto as $item)
                                                <option value="{{ $item->shiptopk }}">{{ $item->shiptonm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="d-flex p-1" id="addnew">
                                        <div class="p-0 w-25"></div>
                                        <div class="text-biru" onclick="event.preventDefault(); modaladdship();">Add Shipto
                                        </div>
                                        <div class="text-biru ps-3" onclick="event.preventDefault(); modaleditship();">Edit
                                            Shipto</div>
                                    </div>
                                    <div class="d-flex p-1" id="dtalamat">
                                        <div class="p-0 w-25">Alamat</div>
                                        <input class="easyui-textbox" multiline="true" style="height:85px;width:210px;"
                                            id="alamat" name="alamat" type="text" style="width:210px;" value="{{ $alamat }}"
                                            disabled>
                                    </div>
                                    <div class="d-flex p-1" id="dttelp">
                                        <div class="p-0 w-25">Telepon</div>
                                        <input id="telp" class="easyui-validatebox height_input" name="telp" type="text"
                                            value="{{ $telepon }}">
                                    </div>

                                    <!-- <div class="d-flex p-1" id="lookup">
                                        <div class="p-0 w-25">Lookup Data</div>
                                        <button class="px-3 py-1 btn-black rounded" id="btn_lookup" data-bs-toggle="modal"
                                            data-bs-target="#ChooseModal"
                                            onclick="event.preventDefault(); submit_invs_lookup()">Lookup</button>
                                    </div> -->
                                </div>
                                <div class="d-flex flex-column flex-fill">
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">No. Acc</div>
                                        <input id="acc" class="easyui-validatebox height_input" name="acc" type="text"
                                            value="{{ $acc }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Shipped By</div>
                                        <input id="kurir" class="easyui-validatebox height_input" name="kurir" type="text"
                                            value="{{ $shipby }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">AWB</div>
                                        <input id="shipno" class="easyui-validatebox height_input" name="shipno" type="text"
                                            value="{{ $shipno}}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25"></div>
                                        <div class="{{ $resi }}" onclick="{{ $onresi }}" id="viewfile"><input type="text"
                                                id="viewfile2"
                                                style="outline:none;border:none;color:#359DD9;cursor:pointer;"
                                                value="{{ $shipfile }}" readonly></div>
                                    </div>
                                    <div class="d-flex p-1 d-none" id="upload_awb">
                                        <div class="p-0 w-25"></div>
                                        <!-- <div class="{{ $resi }}" onclick="{{ $onresi }}" id="viewresi">View</div> -->
                                        <div class="p-0 ps-0 border-2 w-50 color-view-blue pointer d-none" id="rplcresi"
                                            onclick="onmodalresi()">Replace
                                        </div>
                                        <div class="p-0 ps-0 border-2 w-50 color-view-blue pointer d-none" id="upldresi"
                                            onclick="onmodalresi()">Upload
                                        </div>
                                        <!-- <div class="text-biru"
                                            onclick="event.preventDefault();">Upload</div> -->
                                    </div>
                                    <div class="d-flex p-1 ">
                                        <div class="p-0 w-25">From</div>
                                        <input id="from" class="easyui-validatebox height_input" name="from" type="text"
                                            value="{{ $funm }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">To</div>
                                        <input id="to" class="easyui-validatebox height_input" name="to" type="text"
                                            value="{{ $kontakship }}">
                                    </div>
                                </div>
                                <div class="d-flex flex-column flex-fill">
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Send Date</div>
                                        <input id="tglsend" name="tglsend" class="easyui-datebox" style="width:210px;"
                                            placeholder="YYYY-MM-DD" data-options="formatter:myformatter, parser:myparser"
                                            value="{{ \Carbon\Carbon::parse($tglsend)->format('d/m/Y') }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Gross Weight</div>
                                        <input id="gw" class="easyui-validatebox height_input" name="gw" value="{{ $gw }}"
                                            type="text" value="">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Size Box</div>
                                        <input id="sizebox" class="easyui-validatebox height_input" name="sizebox"
                                            type="text" value="{{ $sizebox }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Packaging</div>
                                        <select name="packtype" id="packtype" class="easyui-combobox col-2"
                                            style="width:105px;"
                                            data-options="editable:false, prompt:'Packaging', limitToList:'true', onChange: function () { cek_input_header(); }">
                                            <option value="{{ $packtype }}"> {{ $packtype }} </option>
                                            <option value="BOX">BOX</option>
                                            <option value="FLYER">FLYER</option>
                                            <option value="ENVELOPE">ENVELOPE</option>
                                        </select>
                                        <div class="ps-1">Qty :</div>
                                        <input id="qtypack" class="easyui-validatebox height_input" style="width:75px;"
                                            name="qtypack" type="text" value="{{ $jmlpack }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Volume</div>
                                        <input id="volume" class="easyui-validatebox height_input" name="volume" type="text"
                                            value="{{ $volume }}">
                                    </div>
                                    <div class="d-flex p-1">
                                        <div class="p-0 w-25">Nett Width</div>
                                        <input id="nw" class="easyui-validatebox height_input" name="nw" type="text"
                                            value="{{ $nw }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- TAmbah baris -->
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div id="add" class="p-0 d-none d-flex gap-3">
                        <div class="p-0 font-5 blue pointer fw-bold" id="adddt" onclick="tambahrow();">Add Data</div>
                        <div class="ps-4 p-0 font-5 blue pointer fw-bold d-none" id="savedt" onclick="saverow();">Save</div>
                    </div>

                    <div id="lookup" class="flex-grow-0">
                        <button onclick="event.preventDefault(); submit_invs_lookup()" data-bs-toggle="modal"
                            data-bs-target="#ChooseModal" class="btn btn-sm btn-dark">Lookup</button>
                    </div>

                    <div id="importbyexcel" class="flex-grow-0">
                        <button onclick="ImportExcel();" class="btn btn-sm btn-dark">Import by Excel</button>
                    </div>
                </div>
                <div id="warningtable" class="font-warning pb-3 d-none">
                    *) Belum ada list detail invoice pada table list invoice. Silahkan lengkapi data dan masukan list
                    invoice terlebih dahulu!
                </div>
                <div id="warningtable2" class="font-warning pb-3 d-none">
                    *) Nomor PO harus diisi!
                </div>

                <div class="p-0">
                    <table id="dg" class="easyui-datagrid" title="" align="center" toolbar="#tb" pagination="false"
                        method="get" striped="false" pageSize="100" pageList="[100,200,300,500]" rownumbers="false"
                        singleSelect="false" collapsible="true" fitColumns="true" idField="invsdtpk" data-options="onCheck:function(){menu();},onCheckAll:function(){menu();},onUncheck:function(){menu();},onUncheckAll:function(){menu2();},
                        multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false,
                        rowStyler:function(index,row){
                            if (row.copy==3){
                                return 'color:#000000;background-color:#D6D6D6;';
                            }
                        }">
                        <thead>
                            <tr>
                                <th field="ck" checkbox="true" hidden="true" styler="styler1"></th>
                                <th field="invsdtpk" width="50" hidden="true" formatter="AttrDtgdtpk">invsdtpk</th>
                                <th field="nos" width="120" styler="styler1">Status</th>
                                <th field="style" editor="text" width="120" styler="styler2">Style</th>
                                <th field="pono" width="120" styler="styler2" data-options="editor: {
                                    type: 'text',
                                    options: {
                                        required: true,
                                        missingMessage: 'PO Number is required!'
                                    }
                                }">PO</th>
                                <th field="itemnm" editor="text" width="110" styler="styler2">Items</th>
                                <th field="samplenm" editor="text" width="110" styler="styler2">Sample Type</th>
                                <th field="hscode" editor="text" width="100" styler="styler2">HTS Code</th>
                                <th field="colnm" editor="text" width="110" styler="styler2">Color</th>
                                <th field="fabnm" editor="text" width="120" styler="styler2">Fabric Content</th>
                                <th field="sizenm" editor="text" width="100" styler="styler2">Size</th>
                                <th field="qty" editor="text" width="100" styler="styler2">Qty</th>
                                <!-- <th field="jmlflyer" editor="text" width="100">Flyer</th> -->
                                <th field="price" editor="text" width="100" styler="styler2">Unit Price</th>
                                <th field="amount" width="100" styler="styler3">Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- menu header -->
                <div class="header" id="myHeader" onclick="" style="display:none;">
                    <div id="count" style="display:none;"></div>
                    <div id="count2" style="display:none;">Select items to replace</div>
                    <div class="delete" id="mtd" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true"
                            onclick="set_td('1');" style="color: #FFFFFF; text-decoration: none;"> Set T/D </a></div>
                    <div class="delete" id="mud" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true"
                            onclick="set_td('2');" style="color: #FFFFFF; text-decoration: none;"> Set U/D </a></div>
                    <div class="delete" id="mreplace1" style="width:auto;display:none;"><a href="javascript:void(0)"
                            plain="true" onclick="replace1();" style="color: #FFFFFF; text-decoration: none;"> Copy </a>
                    </div>
                    <div class="delete" id="mreplace2" style="width:auto;display:none;"><a href="javascript:void(0)"
                            plain="true" onclick="replace2();" style="color: #FFFFFF; text-decoration: none;"> Replace data
                        </a></div>
                    <div class="delete" id="mcancopy" style="width:auto;display:none;"><a href="javascript:void(0)"
                            plain="true" onclick="cancopy();" style="color: #FFFFFF; text-decoration: none;"> Cancel </a>
                    </div>
                    <div class="delete" id="mdelete" style="width:auto;display:none;"><a href="javascript:void(0)"
                            plain="true" onclick="delete_list_invsdt();" style="color: #FFFFFF; text-decoration: none;">
                            Delete </a></div>
                    <div class="delete" id="mclose" style="width:auto;"><a href="javascript:void(0)" plain="true"
                            onclick="$('#myHeader').hide(); closemenu();" style="color: #FFFFFF; text-decoration: none;">
                            Close menu </a></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ambil modal -->
    @include('menu.invoice.modal.modal-import-excel')

    <!-- modal pilihan -->
    <div class="modal fade" id="ChooseModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 position-relative">
                <!-- Tombol Close di Sebelah Kanan Atas -->
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                    aria-label="Close"></button>

                <!-- Tombol Pilihan -->
                <div class="d-flex justify-content-center gap-3 pt-4">
                    <button type="button" class="btn btn-primary btn-lg" data-bs-target="#SOMModal"
                        onclick="event.preventDefault(); showSOM();">LOOKUP SOM</button>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-target="#GISModal"
                        onclick="event.preventDefault(); showGIS();">LOOKUP GIS</button>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-target="#INVModal"
                        onclick="event.preventDefault(); showINV();">LOOKUP INVOICE</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal lookup SOM -->
    <div class="modal fade" id="SOMModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content p-4 gap-1">
                <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                    <div class="container-fluid">
                        <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal"
                            data-bs-target="#ChooseModal">
                            <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="25" height="25">
                            <span>BACK</span>
                        </div>
                    </div>
                </div>
                <div class="p-0">
                    <div class="p-0 fw-bold h5">Lookup From SOM</div>
                </div>
                <div class="p-0">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                        </span>
                        <input type="text" class="form-control search" id="searchByInputSOM" onkeyup="SearchSOM()"
                            placeholder="Search...">
                    </div>
                </div>
                <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                    <input type="hidden" id="popk1" name="popk1">
                    <input type="hidden" id="hspec6" name="hspec6">
                    <div>
                        <table id="dgSOM" class="easyui-datagrid" title="" style="width:100%;height:auto"
                            url="{{ route('lookupsom') }}" align="center" toolbar="#tb" striped="true" pagination="true"
                            pageSize="100" fitColumns="true" pageList="[100,200,300,500]" method="get" rownumbers="false"
                            multiple="true" collapsible="true"
                            data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                            <thead>
                                <tr>
                                    <th field="ck" width="35" checkbox="true"></th>
                                    <th field="srno" width="70">No SR</th>
                                    <th field="byrnm" width="100">Buyer</th>
                                    <th field="style" width="100">Style</th>
                                    <th field="category" width="100">Category</th>
                                    <th field="description" width="250">Description</th>
                                    <th field="season" width="100">Season</th>
                                    <th field="stagenm" width="100">Stage</th>
                                    <th field="color" width="100">Color</th>
                                    <th field="size" width="100">Size</th>
                                    <th field="qty" width="100">Qty</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer m-0 p-0">
                    <input type="hidden" id="nilai_unit_filter" name="nilai_unit_filter">
                    <button type="button" id="get_button" class="btn btn-secondary my-2 py-1"
                        onclick="invsdt('SOM')">GET</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal lookup GIS -->
    <div class="modal fade" id="GISModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content p-4 gap-1">
                <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                    <div class="container-fluid">
                        <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal"
                            data-bs-target="#ChooseModal">
                            <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="25" height="25">
                            <span>BACK</span>
                        </div>
                    </div>
                </div>
                <div class="p-0">
                    <div class="p-0 fw-bold h5">Lookup From GIS</div>
                </div>
                <div class="p-0">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                        </span>
                        <input type="text" class="form-control search" id="searchByInputGIS" onkeyup="SearchGIS()"
                            placeholder="Search...">
                    </div>
                </div>
                <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                    <input type="hidden" id="popk1" name="popk1">
                    <input type="hidden" id="hspec6" name="hspec6">
                    <div>
                        <table id="dgGIS" class="easyui-datagrid" title="" style="width:100%;height:auto"
                            url="{{ route('lookupgis') }}" align="center" toolbar="#tb" striped="true" pagination="true"
                            pageSize="100" fitColumns="true" pageList="[100,200,300,500]" method="get" rownumbers="false"
                            multiple="true" collapsible="true"
                            data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                            <thead>
                                <tr>
                                    <th field="ck" width="35" checkbox="true"></th>
                                    <th field="ordno" width="100">No OP</th>
                                    <th field="srno" width="100">No SR</th>
                                    <th field="pono" width="120">No PO</th>
                                    <th field="byrnm" width="120">Buyer</th>
                                    <th field="artno" width="110">Style</th>
                                    <th field="prodes" width="175">Description</th>
                                    <th field="season" width="100">Season</th>
                                    <th field="stage" width="100">Stage</th>
                                    <th field="qty" width="100">Qty</th>
                                    <th field="deldate" width="100">Deliv. Date</th>
                                    <!-- <th field="action" width="100" formatter="formatter1">Action</th> -->
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer m-0 p-0">
                    <input type="hidden" id="nilai_unit_filter" name="nilai_unit_filter">
                    <button type="button" id="get_button" class="btn btn-secondary my-2 py-1"
                        onclick="invsdt('GIS')">GET</button>
                </div>
            </div>
        </div>
    </div>

    <!-- /.modal-Inovice -->
    <div class="modal fade" id="INVModal" tabindex="-1" aria-labelledby="INVModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content p-4 gap-1">
                <div class="modal-header d-flex m-0 p-0 py-2 border-0 align-items-start">
                    <div class="d-flex flex-column gap-2">
                        <div class="p-0">
                            <div class="p-0 fw-bold h5">Lookup Invoice</div>
                            <div class="p-0">Please choose a Invoice to view detail items</div>
                        </div>
                        <div class="p-0">
                            <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                                <span class="input-group-text search">
                                    <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px"
                                        height="18px">
                                </span>
                                <input type="text" class="form-control search" id="searchByInputINV" onkeyup="SearchINV()"
                                    placeholder="Search...">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div>
                        <table id="dgINV" class="easyui-datagrid" style="width:97%;height:auto" align="center" toolbar="#tb"
                            striped="true" pagination="true" pageSize="100" pageList="[100,200,300,500]" method="get"
                            url="{{ route('lookupinv') }}" rownumbers="false" multiple="true" collapsible="true"
                            fitColumns="true"
                            data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false, singleSelect:true">
                            <thead>
                                <tr>
                                    <th field="no" width="30">No</th>
                                    <th field="noinvs" width="110">No Invs</th>
                                    <th field="tglinvs" width="100">Tgl Invs</th>
                                    <th field="byrnm" width="150">Buyer</th>
                                    <th field="shiptonm" width="200">Shipto</th>
                                    <th field="funm" width="150">MD</th>
                                    <th field="action" width="75" formatter="formatter1">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal lookup detail invoice -->
    <div class="modal fade" id="DTINVSModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content p-4 gap-1">
                <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                    <div class="container-fluid">
                        <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal"
                            data-bs-target="#INVModal">
                            <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="25" height="25">
                            <span>BACK</span>
                        </div>
                    </div>
                </div>
                <div class="p-0">
                    <div class="p-0 fw-bold h5">Lookup From Invoice</div>
                </div>
                <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                    <input type="hidden" id="popk1" name="popk1">
                    <input type="hidden" id="hspec6" name="hspec6">
                    <div>
                        <table id="dgDTINVS" class="easyui-datagrid" title="" style="width:100%;height:auto" align="center"
                            toolbar="#tb" striped="true" pagination="true" pageSize="100" fitColumns="true"
                            pageList="[100,200,300,500]" rownumbers="false" multiple="true" collapsible="true"
                            data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                            <thead>
                                <tr>
                                    <th field="ck" width="35" checkbox="true"></th>
                                    <th field="ordpk" width="50" hidden="true">ordpk</th>
                                    <th field="statuspk" width="50" hidden="true">statuspk</th>
                                    <th field="price2" width="50" hidden="true">price2</th>
                                    <th field="unitnm" width="50" hidden="true">unitnm</th>
                                    <th field="nos" width="120">Status</th>
                                    <th field="style" editor="text" width="120">Style</th>
                                    <th field="pono" width="120">PO</th>
                                    <th field="itemnm" editor="text" width="110">Items</th>
                                    <th field="samplenm" editor="text" width="110">Sample Type</th>
                                    <th field="hscode" editor="text" width="100">HS Code</th>
                                    <th field="colnm" editor="text" width="110">Color</th>
                                    <th field="fabnm" editor="text" width="120">Fabric Content</th>
                                    <th field="sizenm" editor="text" width="100">Size</th>
                                    <th field="qty" editor="text" width="100">Qty</th>
                                    <th field="price" editor="text" width="100">Unit Price</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <div class="modal-footer m-0 p-0">
                    <input type="hidden" id="nilai_unit_filter" name="nilai_unit_filter">
                    <button type="button" id="get_button" class="btn btn-secondary my-2 py-1"
                        onclick="invsdt('INVSDT')">GET</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown Modal -->
    <div class="modal fade" id="BDOWNModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content p-4 gap-1">

                <!-- Modal Header -->
                <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                    <div class="container-fluid">
                        <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal"
                            data-bs-target="#GISModal">
                            <img src="{{ asset('public/css/images/arrow-back.png') }}" width="25" height="25" alt="Back">
                            <span>BACK</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Title -->
                <div class="p-0">
                    <div class="p-0 fw-bold h5">Lookup From Breakdown</div>
                </div>

                <!-- Search Input
                <div class="p-0">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" alt="Search" width="18" height="18">
                        </span>
                        <input type="text" class="form-control search" id="searchByInputGIS" onkeyup="SearchGIS()" placeholder="Search...">
                    </div>
                </div> -->

                <!-- Modal Body -->
                <div class="modal-body d-flex flex-column gap-3 m-0 p-0">

                    <!-- Hidden Inputs -->
                    <input type="hidden" id="popk1" name="popk1">
                    <input type="hidden" id="hspec6" name="hspec6">

                    <!-- Table -->
                    <div>
                        <table id="dg4g" class="easyui-datagrid" style="width:99.5%;height:540px;" align="center"
                            toolbar="#tb2" striped="false" showFooter="true" pagination="false" rownumbers="false"
                            singleSelect="true" collapsible="false" fitColumns="true">
                            <thead>
                                <tr>
                                    <th field="jenis" width="35" hidden>Jenis</th>
                                    <th field="country" width="100">Place</th>
                                    <th field="pono" width="100">PO no.</th>
                                    <th field="opno" width="100">OP no.</th>
                                    <th field="style" width="100">Style</th>
                                    <th field="color" width="100">Color</th>
                                    <th field="gac" width="100">Ex Factory</th>

                                    <!-- Dynamically Generated Fields -->
                                    @for ($i = 1; $i <= 40; $i++)
                                        @php
                                            $hidden = empty($dth['s' . $i]) ? 'hidden' : '';
                                        @endphp
                                        <th field="s{{ $i }}" width="50" {{ $hidden }}>
                                        </th>
                                    @endfor

                                    <th field="tot" width="70">Total</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer m-0 p-0">
                    <input type="hidden" id="nilai_unit_filter" name="nilai_unit_filter">
                    <button type="button" id="get_button" class="btn btn-secondary my-2 py-1"
                        onclick="invsdt('GIS')">GET</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Konfirmasi cancel -->
    <div class="modal fade" id="ConfirmCancel" tabindex="-1" aria-labelledby="ConfirmCancelLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog  modal-dialog-scrollable">
            <div class="modal-content p-4">
                <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                    <p class="p-0 m-0 fw-bold h5">Confirmation</p>
                </div>
                <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                    <div class="d-flex text-left">
                        Data yang sudah diisi akan hilang jika Anda keluar dari halaman ini.
                        Mohon konfirmasinya.
                    </div>
                </div>
                <div class="modal-footer m-0 p-0 border-0">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="px-3 py-1 border-0 btn-transparent"
                                data-bs-dismiss="modal">Close</button>
                            <button type="button" class="px-3 py-1 btn-black rounded" data-bs-dismiss="modal"
                                onclick="ConfirmDelete();">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add New Shipment -->
    <div class="modal fade" id="addshipto" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="width:50%">
            <form id="form_add_shipto">
                @csrf
                <input type="hidden" id="shipinvspk" name="shipinvspk" value="{{ $invspk }}">
                <div class="modal-content p-4 gap-1">
                    <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                        <div class="container-fluid">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="p-0">
                        <div class="p-0 fw-bold h5">Add New Shipment To</div>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                        <input type="hidden" id="popk1" name="popk1">
                        <input type="hidden" id="hspec6" name="hspec6">
                        <div class="d-flex flex-column flex-fill">
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Buyer</div>
                                <input class="easyui-combobox height_input" id="shipbyr" name="shipbyr" method="get"
                                    data-options="valueField:'byrpk',textField:'byrnm',url:'{{ route('buyer') }}',prompt:'Buyer'"
                                    style="width:210px;height:34px;">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Shipment To</div>
                                <input id="shipnm" class="easyui-validatebox height_input" name="shipnm" value=""
                                    type="text" value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Address</div>
                                <input class="easyui-textbox" multiline="true" style="height:85px;width:210px;"
                                    id="shipalamat" name="shipalamat" type="text" style="width:210px;" value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Country</div>
                                <input id="shipnegara" class="easyui-validatebox height_input" name="shipnegara" type="text"
                                    value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Phone</div>
                                <input id="shipphone" class="easyui-validatebox height_input" name="shipphone" type="text"
                                    value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Contact Ship</div>
                                <input id="shipkontak" class="easyui-validatebox height_input" name="shipkontak" type="text"
                                    value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer m-0 p-0">
                        <button type="button" class="btn btn-secondary my-2 py-1" onclick="add_shipto();">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Shipment to -->
    <div class="modal fade" id="editshipto" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="width:50%">
            <form id="form_edit_shipto">
                @csrf
                <input type="hidden" id="shiptopkedt" name="shiptopkedt">
                <div class="modal-content p-4 gap-1">
                    <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                        <div class="container-fluid">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-3"
                                data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="p-0">
                        <div class="p-0 fw-bold h5">Edit Shipment To</div>
                    </div>
                    <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                        <div class="d-flex flex-column flex-fill">
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Buyer</div>
                                <input class="easyui-combobox height_input" id="shipbyredt" name="shipbyredt" method="get"
                                    data-options="valueField:'byrpk',textField:'byrnm',url:'{{ route('buyer') }}',prompt:'Buyer'"
                                    style="width:210px;height:34px;">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Shipto</div>
                                <input id="shiptonmedt" class="easyui-validatebox height_input" name="shiptonmedt"
                                    type="text" value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Address</div>
                                <input class="easyui-textbox" multiline="true" style="height:85px;width:210px;"
                                    id="shipalamatedt" name="shipalamatedt" type="text" style="width:210px;" value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Country</div>
                                <input id="shipnegaraedt" class="easyui-validatebox height_input" name="shipnegaraedt"
                                    type="text" value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Phone</div>
                                <input id="shipphoneedt" class="easyui-validatebox height_input" name="shipphoneedt"
                                    type="text" value="">
                            </div>
                            <div class="d-flex p-1">
                                <div class="p-0 w-25">Contact Ship</div>
                                <input id="shipkontakedt" class="easyui-validatebox height_input" name="shipkontakedt"
                                    type="text" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer m-0 p-0">
                        <button type="button" class="btn btn-secondary my-2 py-1" onclick="edit_shipto();">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- modal alert PONO-->
    <div class="modal fade" id="warning" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 position-relative">
                <!-- <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button> -->
                <div class="justify-content-center gap-3 pt-4">
                    <div class="warning-blink pb-3">
                        Nomor PO pada table wajib diisi!
                    </div>
                    <center><button type="button" style="margin: 0 auto;" class="btn btn-outline-primary"
                            data-bs-dismiss="modal" aria-label="Close">OK</button></center>
                </div>
            </div>
        </div>
    </div>

    <!-- modal alert ISIAN TABEL INVSDT-->
    <div class="modal fade" id="tbinvsdt" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4 position-relative">
                <!-- <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button> -->
                <div class="justify-content-center gap-3 pt-4">
                    <div class="warning-blink pb-3">
                        Belum ada list detail invoice pada table list invoice. Silahkan lengkapi data dan masukan list
                        invoice terlebih dahulu!
                    </div>
                    <center><button type="button" style="margin: 0 auto;" class="btn btn-outline-primary"
                            data-bs-dismiss="modal" aria-label="Close">OK</button></center>
                </div>
            </div>
        </div>
    </div>

    <!-- modal alert upload resi -->
    <div class="modal fade" id="Modalresi" aria-hidden="true" data-bs-backdrop="static" aria-labelledby="ModalLabelResi"
        tabindex="-1">
        <div class="modal-dialog">
            <div class="p-4 modal-content p-3">
                <div class="p-0 mb-2 modal-header">
                    <h4 class="modal-title fw-bold" id="ModalLabelResi">Add File (Resi Kurir)</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="$('#ResiForm').form('reset');"
                        aria-label="Close"></button>
                </div>
                <div class="p-0 modal-body">
                    <form id="ResiForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="invspkresi" name="invspkresi" value="{{ $invspk }}">
                        <input type="hidden" id="awbresi" name="awbresi" value="">
                        <!-- <input type="hidden" id="kurirresi" name="kurirresi" value=""> -->
                        <div class="d-flex flex-column gap-2 p-0 m-0">
                            <div class="d-flex justify-content-center">
                                <div class="p-0 w-100">
                                    <label class="p-1 w-100 bg-white pointer rounded text-center" for="resi">Upload</label>
                                </div>
                                <div class="p-0 d-none">
                                    <input type="file" name="resi" id="resi" onchange="onupresi();">
                                </div>
                            </div>
                            <div class="p-0 m-0 d-flex d-none" id="resiupdiv">
                                <input type="text" class="p-0 m-0 form-control w-100 border-0" id="namafileresi" readonly>
                            </div>
                            <div class="d-flex">
                                <div class="p-0">File Extension : .pdf/ .jpg/ .jpeg</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <div class="px-4 py-1 font-4 pointer" id="resicancel" onclick="$('#ResiForm').form('reset');"
                        data-bs-dismiss="modal">Cancel</div>
                    <div class="px-4 py-1 font-4 pointer bg-black rounded" id="resisave" onclick="onrouteresi();">Save</div>
                </div>
            </div>
        </div>
    </div>
@endsection