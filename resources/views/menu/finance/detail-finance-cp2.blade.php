@extends('layout.main')

@section('css_custom')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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

    // function dateFormatter(date) {
    //     var y = date.getFullYear();
    //     var m = date.getMonth() + 1;
    //     var d = date.getDate();
    //     return y + '-' + (m < 10 ? ('0' + m) : m) + '-' + (d < 10 ? ('0' + d) : d); // YYYY-MM-DD format
    // }

    // function dateParser(s) {
    //     if (!s) return new Date();
    //     var parts = s.split('-');
    //     var y = parseInt(parts[0], 10);
    //     var m = parseInt(parts[1], 10) - 1;
    //     var d = parseInt(parts[2], 10);
    //     return new Date(y, m, d);
    // }


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
        // $('#dg').datagrid('getPanel').addClass('col');
    });

    $(function() {
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg_tablePO').datagrid('getPanel').css('border', 'none');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dg_tablePO').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dg_tablePO').datagrid('getPanel').addClass('lines-no');
        $('#dg_tablePO').datagrid('getPanel').addClass('lines-no3');
        // $('#dg_tablePO').datagrid('getPanel').addClass('col');
    });

    $(function() {
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('background', '#FFFFFF');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-top', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-top-left-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-bottom', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-left-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-top-right-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-bottom-right-radius', '8px');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-left', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').css('border-right', '1px solid #858585');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header td[field]').css('border-right', 'none');
        $('#dg_tablePodt').datagrid('getPanel').css('border', 'none');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').addClass('lines-no3');
        $('#dg_tablePodt').datagrid('getPanel').find('div.datagrid-header').addClass('col');
        $('#dg_tablePodt').datagrid('getPanel').addClass('lines-no');
        $('#dg_tablePodt').datagrid('getPanel').addClass('lines-no3');
        // $('#dg_tablePodt').datagrid('getPanel').addClass('col');
    });

    $(function() {
        //show hidden kolom checkbox di awal
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[4];
        if (pathname3 == 'input') {
            $('#dg').datagrid('showColumn', 'ck');
        } else {
            $('#dg').datagrid('hideColumn', 'ck');
        }

        $('#POModal').on('shown.bs.modal', function() {
            $('#dg_tablePO').datagrid('resize');
        })
        $('#PodtModal').on('shown.bs.modal', function() {
            $('#dg_tablePodt').datagrid('resize');
            button_get();
        })
    });

    function Search() {
        $('#dg_tablePO').datagrid('load', {
            searchByInput: $('#searchByInput').val(),
        });
    }

    // function list_podt(popk) {
    //     if (!popk) {
    //         console.error('popk is undefined or null');
    //         return;
    //     }

    //     $('#popk_filter').val(popk);

    //     console.log('ini popk pertama : ' + popk);

    //     var url = "";
    //     url = url.replace(':popk', popk);

    //     console.log('ini url : ' + url);

    //     $.ajax({
    //         type: "GET",
    //         url: url,
    //         data: {
    //             _token: $('meta[name="csrf-token"]').attr('content'),
    //             nolpb: ,
    //         },
    //         success: function (data) {
    //             console.log("ini data : " + JSON.stringify(data));

    //             let combinedUnitSelect = $('#combined_unit');

    //             combinedUnitSelect.empty();
    //             combinedUnitSelect.append(`<option value="${data.filterUnitSelected}" disabled>${data.filterUnitSelected}</option>`);

    //             data.filterUnit.forEach(function (unit) {
    //                 console.log("unit filter : " + unit);
    //                 combinedUnitSelect.append(
    //                     `<option value="${unit}">${unit}</option>`
    //                 );
    //             });

    //             $('#popk').val(data.popk_first);
    //             $('#kelnm').val(data.kelnm_first);
    //             $('#popk1').val(data.popk_first);
    //             $('#suppk_first').val(data.suppk_first);
    //             $('#pono_first').text(data.pono_first);
    //             $('#pono').val(data.pono_first);
    //             $('#byrnm_first').text(data.byrnm_first);
    //             $('#supplier_first').text(data.supplier_first);
    //             $('#invoiceno_first').text(data.invoiceno_first);
    //             $('#piform_name').text(data.download);
    //             $('#hspec6').val(data.hspec1);

    //             console.log('popk ' + data.popk_first + ' hspec1 ' + data.hspec1 + ' hspec2 ' + data.hspec2 + ' hspec3 ' + data.hspec3 + ' hspec4 ' + data.hspec4 + ' hspec5 ' + data.hspec5)

    //             $('#piform').text(data.download ? 'Download' : '');

    //             if (data.download) {
    //                 $('#piform').attr('onclick', `download_piform('${data.download}')`);
    //                 $('#piform').attr('class', 'blue pointer');
    //             } else {
    //                 $('#piform').removeAttr('onclick');
    //                 $('#piform').removeAttr('class');
    //             }

    //             var suppk = $('#suppkInput').val();

    //             if (suppk != data.suppk_first && suppk) {
    //                 $('#POModal').modal('hide');
    //                 $('#supplier_ga_sama').modal('show');
    //                 return
    //             }

    //             $('#POModal').modal('hide');
    //             $('#PodtModal').modal('show');

    //             $('#dg_tablePodt').datagrid({
    //                 url: url,
    //                 method: 'get'
    //             });

    //             unitFilter();

    //             $('#hspec1').text(data.hspec1 ?? 'coba 1');
    //             $('#hspec2').text(data.hspec2 ?? 'coba 2');
    //             $('#hspec3').text(data.hspec3 ?? 'coba 3');
    //             $('#hspec4').text(data.hspec4 ?? 'coba 4');
    //             $('#hspec5').text(data.hspec5 ?? 'coba 5');

    //             console.log('nila tspec global : ' + data.tspec)

    //             if (!data.hspec1) {
    //                 $('#dg_tablePodt').datagrid('hideColumn', 'spec1');
    //             }

    //             if (!data.hspec2) {
    //                 $('#dg_tablePodt').datagrid('hideColumn', 'spec2');
    //             }

    //             if (!data.hspec3) {
    //                 $('#dg_tablePodt').datagrid('hideColumn', 'spec3');
    //             }

    //             if (!data.hspec4) {
    //                 $('#dg_tablePodt').datagrid('hideColumn', 'spec4');
    //             }

    //             if (!data.hspec5) {
    //                 $('#dg_tablePodt').datagrid('hideColumn', 'spec5');
    //             }
    //         },
    //         error: function (error) {
    //             console.log(error);
    //         }
    //     });
    // }

    // $(function () {
    //     $.ajax({
    //         url: '',
    //         method: 'GET',
    //         success: function (data) {
    //             console.log(JSON.stringify(data));
    //             console.log(data.suppk)
    //             if (data.suppk) {
    //                 $('#suppkInput').val(data.suppk);
    //             } else {
    //                 console.log('Suppk does not exist or is null.');
    //             }

    //             if (data.rows && data.rows.length > 0) {
    //                 const poValues = data.rows.map(row => row.po);
    //                 console.log('PO Values:', poValues);

    //                 const poList = poValues.join(', ');
    //                 $('#nopo_datagrid').val(poList);
    //             } else {
    //                 console.log('No rows found.');
    //             }

    //             if (data.rows && data.rows.length > 0) {
    //                 const PodtpkValues = data.rows.map(row => row.podtpk);
    //                 console.log('podtpk Values:', PodtpkValues);

    //                 const podtpkList = PodtpkValues.join(', ');
    //                 $('#podtpk_datagrid').val(podtpkList);
    //             } else {
    //                 console.log('No rows found.');
    //             }
    //         },
    //         error: function (xhr) {
    //             console.error('Error fetching data:', xhr);
    //         }
    //     });
    // })


    $(function() {
        let firstCheckedSuppk = null;
        $('#dg_tablePodt').datagrid({
            onCheck: function(index, row) {
                $('#ckall').val('0');
                console.log("Checked suppk: " + row.podtpk);
            },
            onUncheck: function(index, row) {
                $('#ckall').val('0');
                console.log("Checked suppk: " + row.podtpk);
            },
            onCheckAll: function(rows) {
                $('#ckall').val('1');
                firstCheckedSuppk = rows[0].suppk;
                console.log("All checked suppk: " + firstCheckedSuppk);
            },
            onUncheckAll: function() {
                $('#ckall').val('0');
                firstCheckedSuppk = null;

                console.log("Unchecked all.");
            }
        });
    });

    // function dtgdt() {
    //     var nilai_unit_filter = $('#nilai_unit_filter').val();

    //     var rows = $('#dg_tablePodt').datagrid('getChecked');

    //     var formData = {
    //         dtgpk: $('#dtgpk').val(),
    //         suppk: $('#suppk_first').val(),
    //         pono: $('#pono').val(),
    //         popk: $('#popk').val(),
    //         kelnm: $('#kelnm').val(),
    //         unit_pocons_filter: $('#unit_pocons_filter').val(),
    //         _token: $('meta[name="csrf-token"]').attr('content')
    //     };

    //     if (rows.length > 0) {
    //         console.log("Rows selected: " + rows.length);
    //         console.log("Selected rows: " + JSON.stringify(rows));
    //     } else {
    //         console.log("No rows are selected.");
    //     }

    //     formData.rows = rows;

    //     $.ajax({
    //         type: "POST",
    //         url: "",
    //         data: formData,
    //         success: function (data) {
    //             console.log("Success:", data);
    //             window.location.reload();
    //             cek_input_header(data.total_dtgdt);
    //         },
    //         error: function (xhr, status, error) {
    //             console.log("Error:", error);
    //             console.log(xhr);
    //         }
    //     });
    // }

    $(function() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[4];

        var nilai = (pathname3 === 'input');
        var invpk = $('#invpk').val();

        $('#dg').edatagrid({
            saveUrl: '{{ route("api.insert-invoicedt", "") }}/' + {{ $dt_invoice->invpk }},
            updateUrl: '{{ route("api.update-invoicedt") }}',
            editing: nilai,
            onSuccess: function(index, row) {
                $(this).edatagrid('saveRow');
                $(this).datagrid('reload');
                console.log("ROW : " + row.invdtpk);
                console.log("nilai : " + nilai);
            },
            onDblClickRow: function(index, row) {
                var isEdit = $('#isEdit-' + row.invdtpk).val();
                var invdtpk = row.invdtpk;

                if (isEdit == 'true') {
                    document.getElementById('isEdit-' + row.invdtpk).value = false;
                    hiddenSave(index, invdtpk);
                    $(this).datagrid('endEdit', index);
                } else {
                    console.log('onedit : ' + row.invdtpk)
                    $('#isEdit-' + row.invdtpk).val('true');
                    if (row.copy == 3) {
                        return false;
                    } else {
                        if ($(this).edatagrid('options').editing) {
                            $(this).datagrid('beginEdit', index);
                            console.log("Double click edit mode");

                            var mpo = $(this).datagrid('getEditor', {
                                index: index,
                                field: 'mpo'
                            });
                            if (mpo) {
                                $(mpo.target).datebox({
                                    formatter: myformatter,
                                    parser: myparser
                                });
                            }
                        }
                    }
                }
            },
            onBeforeEdit: function(index, row) {
                row._token = $('meta[name="csrf-token"]').attr('content');
            }
        });
    });

    function AttrDtgdtpk(value, row) {
        var hiddenSave = '<input type="text" id="isEdit-' + row.invdtpk + '" value="false">';
        return hiddenSave;
    }

    // function hiddenSave(index, invdtpk) {
    //     var edTglfaktur = $('#dg').datagrid('getEditor', { index: index, field: 'tglfaktur' });
    //     var tglfaktur = $(edTglfaktur.target).datebox('getValue');

    //     var edNofaktur = $('#dg').datagrid('getEditor', { index: index, field: 'nofaktur' });
    //     var nofaktur = $(edNofaktur.target).val();

    //     var edQtydtg = $('#dg').datagrid('getEditor', { index: index, field: 'qtydtg' });
    //     var qtydtg = $(edQtydtg.target).val();

    //     var rows = $('#dg').datagrid('getRows');
    //     var kodebrgdok = rows[index].kodebrgdok;
    //     var po = rows[index].po;
    //     var qtypo = rows[index].qtypo;

    //     var url = '';

    //     $.ajax({
    //         url: url,
    //         type: 'POST',
    //         data: {
    //             _token: $('meta[name="csrf-token"]').attr('content'),
    //             tglfaktur,
    //             dtgdtpk,
    //             nofaktur,
    //             qtydtg,
    //             kodebrgdok,
    //             po,
    //             qtypo,
    //         },
    //         success: function (response) {
    //             console.log('Data updated successfully:', response);
    //         },
    //         error: function (xhr, status, error) {
    //             console.error('Error updating data:', error);
    //         }
    //     });

    //     console.log('url: ' + url + ', dtgdtpk: ' + dtgdtpk);
    // }


    function submit_dtg(nilai) {
        var formElement = document.getElementById("form_dtg");

        var requiredFields = ["nokk", "ppn", "kursp"]; // Add the required field IDs here
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
            return;
        }

        var formData = new FormData(formElement);

        $.ajax({
            method: "POST",
            url: "{{ route('api.edit-save-header') }}",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,

            success: function(response) {
                console.log("COBA RESPONSE" + JSON.stringify(response));

                if (nilai == 'submit') {
                    window.location.href = "{{ route('list.finance') }}";
                } else {
                    // window.location.reload();
                }
            },
            error: function(xhr, status, error) {
                console.log("error add cuti HERE >>>>>>>>>>>>>>>> " + JSON.stringify(xhr));

                if (xhr.responseJSON && xhr.responseJSON.message) showAlert(400, xhr.responseJSON.message);
                else showAlert(400, JSON.stringify(error))
            }
        });
    }


    function unitFilter() {
        var combined_unit = $('#combined_unit').val();
        var popk = $('#popk_filter').val();
        var url = "";
        url = url.replace(':popk', popk);

        $.ajax({
            type: "GET",
            url: url,
            data: {
                combined_unit: combined_unit,
            },
            success: function(response) {
                console.log("unitfilter" + JSON.stringify(response))
                if (response.rows) {
                    $('#dg_tablePodt').datagrid('loadData', response.rows);
                }

                console.log("nilai filter : " + response.nilai);
                $('#downloadPO').empty();

                if (response.nilai == 1) {
                    $('#dg_tablePodt').datagrid('hideColumn', 'unit2');
                    $('#dg_tablePodt').datagrid('showColumn', 'unit');

                    $('#dg_tablePodt').datagrid('hideColumn', 'pocons2');
                    $('#dg_tablePodt').datagrid('showColumn', 'pocons');

                    $('#dg_tablePodt').datagrid('hideColumn', 'qrs2');
                    $('#dg_tablePodt').datagrid('showColumn', 'qrs');

                    $('#dg_tablePodt').datagrid('hideColumn', 'qtyih2');
                    $('#dg_tablePodt').datagrid('showColumn', 'qtyih1');

                    $('#dg_tablePodt').datagrid('hideColumn', 'balqty2');
                    $('#dg_tablePodt').datagrid('showColumn', 'balqty1');
                    $('#unit_pocons_filter').val(response.nilai);

                    if (response.pono) {
                        $('#downloadPO').append(
                            $('<a>', {
                                href: 'http://192.168.0.22/GIS/pdf/pdf_po?cek=1&po=' + response.pono,
                                target: '_blank',
                                text: 'Download ' + response.pono,
                                style: 'color: rgba(53, 157, 217, 1); text-decoration:none'
                            })
                        );
                    }

                } else if (response.nilai == 2) {
                    $('#dg_tablePodt').datagrid('hideColumn', 'unit');
                    $('#dg_tablePodt').datagrid('showColumn', 'unit2');

                    $('#dg_tablePodt').datagrid('hideColumn', 'pocons');
                    $('#dg_tablePodt').datagrid('showColumn', 'pocons2');

                    $('#dg_tablePodt').datagrid('hideColumn', 'qrs');
                    $('#dg_tablePodt').datagrid('showColumn', 'qrs2');

                    $('#dg_tablePodt').datagrid('hideColumn', 'qtyih1');
                    $('#dg_tablePodt').datagrid('showColumn', 'qtyih2');

                    $('#dg_tablePodt').datagrid('hideColumn', 'balqty1');
                    $('#dg_tablePodt').datagrid('showColumn', 'balqty2');

                    $('#unit_pocons_filter').val(response.nilai);

                    if (response.pono) {
                        $('#downloadPO').append(
                            $('<a>', {
                                href: 'http://192.168.0.22/GIS/pdf/pdf_po_unit2?cek=1&po=' + response.pono,
                                target: '_blank',
                                text: 'Download ' + response.pono,
                                style: 'color: rgba(53, 157, 217, 1); text-decoration:none'
                            })
                        );
                    }
                }

                var nilai_unit_filter = $('#nilai_unit_filter').val(response.combined_unit);

                button_get();
            },
            error: function(xhr, status, error) {
                console.log("Error:", error);
            }
        });
    }

    function edit_data() {
        $('#edit_data').addClass('d-none');
        $('#cancel_save_edit').removeClass('d-none');
        $('#add').removeClass('d-none');

        $('#dg').edatagrid('options').editing = true;
        $('#dg').datagrid('showColumn', 'ck');

        $('#tglinv').attr('disabled', false);
        $('#nokk').combobox('enable');
        $('#priceppn').combobox('enable');
        $('#kursp').combobox('enable');
    }

    function cancel_edit() {
        $('#cancel_save_edit').addClass('d-none');
        $('#add').addClass('d-none');
        $('#myHeader').hide();

        $('#edit_data').removeClass('d-none');
        $('#statuspage').val('edit');

        $('#dg').edatagrid('options').editing = false;
        $('#dg').datagrid('hideColumn', 'ck');

        var rows = $('#dg').datagrid('getRows');
        for (var i = 0; i < rows.length; i++) {
            $('#dg').datagrid('endEdit', i);
        }

        $('#tglinv').attr('disabled', true);
        $('#nokk').combobox('disable');
        $('#priceppn').combobox('disable');
        $('#kursp').combobox('disable');
    }

    $(function() {
        cek_input_header();
    })

    $(document).ready(function() {
        $('#nodokumen').on('input', cek_input_header);
    });

    function cek_input_header() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[4];

        var tglinv = $('#tglinv').val().trim();
        var nokk = $('#nokk').val().trim();
        var priceppn = $('#priceppn').val().trim();
        var kursp = $('#kursp').val().trim();

        var baris = $('#dg').datagrid('getData').total;


        if (pathname3 == 'input') {
            if (tglinv && nokk && priceppn && kursp > 0) {
                $('#save_submit').removeClass('d-none');
            } else {
                $('#save_submit').addClass('d-none');
            }
        } else {
            if (tglinv && nokk && priceppn && kursp > 0) {
                $('#save_edit').removeClass('d-none');
            } else {
                $('#save_edit').addClass('d-none');
            }
        }
    }

    function editDisplay() {
        var pathArray = window.location.pathname.split("/");
        var pathname3 = pathArray[4];

        if (pathname3 == 'edit') {
            var posting = $('#posting').val();
            if (posting == '1') {
                $('#edit_data').addClass('d-none');
                $('#lookup').addClass('d-none');
            } else {
                $('#edit_data').removeClass('d-none');
                $('#lookup').removeClass('d-none');
            }
            $('#statuspage').val('edit');
            $('#add').addClass('d-none');

            $('#tglinv').attr('disabled', true);
            $('#nokk').combobox('disable');
            $('#priceppn').combobox('disable');
            $('#kursp').combobox('disable');

        } else if (pathname3 == 'input') {
            $('#cancel_submit').removeClass('d-none');
            $('#statuspage').val('input');
            $('#add').removeClass('d-none');

        } else {
            $('#cancel_submit').addClass('d-none');
            $('#edit_data').addClass('d-none');
            $('#statuspage').val('none');
        }
    }

    function tambahrow() {
        $('#dg').edatagrid('addRow');

        var index = $('#dg').edatagrid('getRows').length - 1;
        var ed = $('#dg').edatagrid('getEditors', index)[0];

        $('#dg').datagrid('selectRow', index).datagrid('beginEdit', index);

        var mpo = $('#dg').datagrid('getEditor', {
            index: index,
            field: 'mpo'
        });
        if (mpo) {
            $(mpo.target).datebox({
                formatter: myformatter,
                parser: myparser
            });
        }

        $(ed.target).focus();
    }

    function formatter1(value, row) {
        const inputValue = $('#nopo_datagrid').val();
        const poList = inputValue.split(',').map(item => item.trim());
        return `<div class='blue pointer' onclick='list_podt(${row.popk})'>Select</div>`;
    }

    $(function() {
        $('#dg_tablePodt').datagrid({
            onLoadSuccess: function(data) {
                const podtpkValue = $('#podtpk_datagrid').val();
                const podtpkList = podtpkValue.split(',').map(item => item.trim());

                const rows = $('#dg_tablePodt').datagrid('getRows');

                rows.forEach(function(r, i) {
                    console.log("r.podtpk : " + r.podtpk);
                    console.log("podtpkList : " + podtpkList);
                    console.log("podtpkValue : " + podtpkValue);

                    var checkbox = $('#dg_tablePodt').datagrid('getPanel')
                        .find('div.datagrid-view2 div.datagrid-body tr[datagrid-row-index="' + i + '"] input[type="checkbox"]'); // Dapatkan checkbox per baris

                    if (podtpkList.includes(r.podtpk.toString())) {
                        console.log('Sama, podtpk : ' + r.podtpk);
                        checkbox.prop('disabled', true);
                    } else {
                        console.log('Tidak sama, podtpk : ' + r.podtpk);
                        checkbox.prop('disabled', false);
                    }
                });
            }
        });
    });

    function button_get() {
        const nilai_unit_filter = $('#nilai_unit_filter').val();

        if (!nilai_unit_filter || nilai_unit_filter.trim() === '') {
            $('#get_button').addClass('d-none');
            $('#dg_tablePodt').datagrid('getPanel')
                .find('div.datagrid-view2 div.datagrid-body tr input[type="checkbox"]')
                .prop('disabled', true);
        } else {
            $('#get_button').removeClass('d-none');
            $('#dg_tablePodt').datagrid({
                onLoadSuccess: function(data) {
                    const podtpkValue = $('#podtpk_datagrid').val();
                    const podtpkList = podtpkValue.split(',').map(item => item.trim());

                    const rows = $('#dg_tablePodt').datagrid('getRows');

                    rows.forEach(function(r, i) {
                        console.log("r.podtpk : " + r.podtpk);
                        console.log("podtpkList : " + podtpkList);
                        console.log("podtpkValue : " + podtpkValue);

                        var checkbox = $('#dg_tablePodt').datagrid('getPanel')
                            .find('div.datagrid-view2 div.datagrid-body tr[datagrid-row-index="' + i + '"] input[type="checkbox"]'); // Dapatkan checkbox per baris

                        if (podtpkList.includes(r.podtpk.toString())) {
                            console.log('Sama, podtpk : ' + r.podtpk);
                            checkbox.prop('disabled', true);
                        } else {
                            console.log('Tidak sama, podtpk : ' + r.podtpk);
                            checkbox.prop('disabled', false);
                        }
                    });
                }
            });
        }
    }

    $(function() {
        editDisplay();
        button_get();
    });


    //tambahan untuk menu copy replace
    /*menu*/
    function menu() {
        var cp = document.getElementById('copy').value;
        var row = $('#dg').datagrid('getChecked');
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
            $('#count2').css('margin-right', '60%');
            edit_data();
        } else {
            if (row) {
                if (tchek > 1) {
                    $('#myHeader').show();
                    $('#count').show();
                    $('#count2').hide();
                    $('#mreplace1').hide();
                    $('#mcancopy').hide();
                    $('#mdelete').show();
                    $('#count').css('margin-right', '70%');
                    count_row();
                } else {
                    $('#myHeader').show();
                    $('#count').show();
                    $('#count2').hide();
                    $('#mreplace1').show();
                    $('#mcancopy').hide();
                    $('#mdelete').show();
                    $('#count').css('margin-right', '60%');
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
        $('#myHeader').hide();
    }

    function replace1() {
        var row = $('#dg').datagrid('getSelected');
        if (!row) return $('#myHeader').hide();

        var url = "";

        // Ambil CSRF token dari meta tag
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        $.ajax({
            type: "POST",
            url: url,
            data: {
                dtgdtpk: row.dtgdtpk,
                sts: 1
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken // Sertakan token CSRF
            },
            success: function() {
                $('#dg').datagrid('reload');
                $('#dg').datagrid('uncheckAll');
                $('#copy').val(1);
                menu();
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    }

    $(function() {
        $('#dg').datagrid({
            onLoadSuccess: function(data) {
                for (var i = 0; i < data.rows.length; i++) {
                    var row = data.rows[i];
                    if (row.copy == 3) {
                        var checkbox = $('tr[datagrid-row-index="' + i + '"] input[type="checkbox"]');
                        checkbox.prop('disabled', true);
                    } else {
                        var checkbox = $('tr[datagrid-row-index="' + i + '"] input[type="checkbox"]');
                        checkbox.prop('disabled', false);
                    }
                }
            },
            onClickRow: function(index, row) {
                if (row.copy == 3) {
                    var checkbox = $(this).datagrid('getPanel').find('div.datagrid-view2 div.datagrid-body tr[datagrid-row-index="' + index + '"] input[type="checkbox"]');
                    checkbox.prop('disabled', true);
                } else {
                    var checkbox = $(this).datagrid('getPanel').find('div.datagrid-view2 div.datagrid-body tr[datagrid-row-index="' + index + '"] input[type="checkbox"]');
                    // checkbox.prop('checked', false);
                    checkbox.prop('disabled', false);
                }
            },
        });
    });

    $(function() {
        var formData = {
            dtgpk: $('#dtgpk').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        $.ajax({
            type: "POST",
            url: "",
            data: formData,
            success: function(response) {
                document.getElementById('copy').value = response.copy;
                if (response.copy == 1) {
                    menu();
                }
            },
        });
    });

    function cancopy() {
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var url = "";

        $.ajax({
            type: "POST",
            url: url,
            data: {
                dtgpk: $('#dtgpk').val(),
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken // Sertakan token CSRF
            },
            success: function() {
                $('#dg').datagrid('reload');
                $('#dg').datagrid('uncheckAll');
                $('#copy').val(0);
                menu();
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    }

    function replace2() {
        var selectedRow = $('#dg').datagrid('getSelected');
        if (!selectedRow) {
            $.messager.alert('Warning', 'No data selected', 'warning');
            return;
        }

        var checkedRows = $('#dg').datagrid('getChecked');
        if (!checkedRows || checkedRows.length === 0) {
            $.messager.alert('Warning', 'No data selected', 'warning');
            return;
        }

        var ids = checkedRows.map(function(row) {
            return row.dtgdtpk; // Ambil dtgdtpk dari setiap baris yang dicentang
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Kirim semua `ids` dalam satu permintaan
        $.ajax({
            type: 'POST',
            url: "",
            data: {
                ids: ids
            }, // Kirim sebagai array
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            success: function(response) {
                if (response.success) {
                    $('#copydata').modal('show');
                    ambil_data_copy();
                } else {
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
                }
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
            }
        });
    }

    $(function() {
        const confirmButton = document.getElementById('confirmBtn');
        $('#all').click(function(event) {
            if (this.checked) {
                $('#ceek1').prop('checked', true);
                $('#ceek2').prop('checked', true);
                $('#ceek3').prop('checked', true);
                $('#ceek4').prop('checked', true);
                confirmButton.disabled = false;
                confirmButton.addClass('px-3 py-1 btn-black rounded');
            } else {
                $('#ceek1').prop('checked', false);
                $('#ceek2').prop('checked', false);
                $('#ceek3').prop('checked', false);
                $('#ceek4').prop('checked', false);
                confirmButton.disabled = true;
                confirmButton.addClass('px-3 py-1 rounded');
            }
        });
    });

    function cheek() {
        const confirmButton = document.getElementById('confirmBtn');
        var ceek1 = document.getElementById('ceek1');
        var ceek2 = document.getElementById('ceek2');
        var ceek3 = document.getElementById('ceek3');
        var ceek4 = document.getElementById('ceek4');
        if (ceek1.checked || ceek2.checked || ceek3.checked || ceek4.checked) {
            confirmButton.disabled = false;
            confirmButton.addClass('px-3 py-1 btn-black rounded');
        } else {
            confirmButton.disabled = true;
            confirmButton.addClass('px-3 py-1 rounded');
        }
    }

    function ambil_data_copy() {
        var dtgpk = $('#dtgpk').val();
        var url = "";
        url = url.replace(':dtgpk', dtgpk);

        $.ajax({
            type: 'GET',
            url: url,
            success: function(response) {
                // Pastikan response adalah objek JSON
                if (response.error) {
                    console.error(response.error);
                    // alert('Data not found');
                } else {
                    $('#tglfak').text(response.tglfak);
                    $('#nofak').text(response.nofak);
                    $('#kdbrg').text(response.kdbrg);
                    $('#qtydtg').text(response.qtydtg);

                    console.log(`Tgl Faktur: ${tglfak}, No Faktur: ${nofak}, Kode Barang: ${kdbrg}, Qty datang: ${qtydtg}`);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseJSON?.error || 'Unknown error');
            }
        });
    }

    function savereplace() {
        // Ambil CSRF token dari meta tag
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var url = "";
        $.ajax({
            type: "POST",
            url: url,
            data: {
                dtgpk: $('#dtgpk').val(),
                ceek1: $('#ceek1').val(),
                ceek2: $('#ceek2').val(),
                ceek3: $('#ceek3').val(),
                ceek4: $('#ceek4').val()
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken // Sertakan token CSRF
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
                $('#copydata').modal('hide');
                $('#dg').datagrid('reload');
                $('#dg').datagrid('uncheckAll');
                $('#myHeader').hide();
                document.getElementById('copy').value = 0;
                $('#all').prop('checked', false);
                $('#ceek1').prop('checked', false);
                $('#ceek2').prop('checked', false);
                $('#ceek3').prop('checked', false);
                $('#ceek4').prop('checked', false);
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    }

    function cancelcopy() {
        // Ambil CSRF token dari meta tag
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var url = "";
        $.ajax({
            type: "POST",
            url: url,
            data: {
                dtgpk: $('#dtgpk').val(),
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken // Sertakan token CSRF
            },
            success: function() {
                $('#copydata').modal('hide');
                $('#dg').datagrid('reload');
                $('#dg').datagrid('uncheckAll');
                $('#all').prop('checked', false);
                $('#ceek1').prop('checked', false);
                $('#ceek2').prop('checked', false);
                $('#ceek3').prop('checked', false);
                $('#ceek4').prop('checked', false);
                menu();
            },
            error: function(xhr, status, error) {
                console.error("Error:", error);
            }
        });
    }


    function delete_list_barang() {
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

        var ids = checkedRows.map(function(row) {
            return row.dtgdtpk; // Ambil dtgdtpk dari setiap baris yang dicentang
        });

        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Tambahkan dialog konfirmasi
        $.messager.confirm('Confirm', 'Are you sure you want to delete the selected items?', function(r) {
            if (r) {
                // Jika pengguna mengkonfirmasi penghapusan
                $.ajax({
                    type: 'POST',
                    url: "",
                    data: {
                        ids: ids
                    }, // Kirim sebagai array
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
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
                    }
                });
            }
        });
    }

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

    function formatter_balance(val, row) {
        if (val > 0) {
            return '<span style="color:#39cc02;"> + ' + val + '</span>';
        } else if (val < 0) {
            return '<span style="color:#fd5047;"> - ' + val + '</span>';
        } else {
            return val;
        }
    }


    function KlikPrint() {
        var url = "http://192.168.0.22/SPB/detail/" + {{$dt_invoice->spbpk}} + "/finance/pdf";
        window.open(url, '_blank');
    }

    function GoBack() {
            window.location.href = "{{ URL::to('/list-finance')}}";
    }
</script>
@endsection

@section('content')

<div class="main-wrapper min-vh-100">
    <div class="container-fluid">
        <div class="d-flex flex-column p-4 m-3">
            <form id="form_dtg">
                @csrf

                <!-- <input type="hidden" id="dtgpk" name="dtgpk" value="dtgpk"> -->
                <input type="hidden" id="invdtpk" name="invdtpk" value="invdtpk">


                <div class="d-flex gap-3">
                    <div id="back_input" class="d-flex pointer" onclick="GoBack()"><img
                            src="{!! asset('public/css/images/arrow-back.png') !!}" width="27" height="27">
                    </div>
                    <div class="d-flex flex-column flex-fill">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex fw-bold ">No. Inv : {{ $dt_invoice->noinv}}</div>

                            <button type="button" class="gap-4 btn btn-sm btn-success" onclick="KlikPrint();">
                                <i class="fa-solid fa-print"></i> Pdf
                            </button>

                            <div class="d-flex gap-4 d-none" id="cancel_submit">
                                <button class="btn-transparent border-0 fw-bold" onclick="event.preventDefault(); back_input_barang()">Cancel</button>
                                <button class="btn-black rounded px-3 d-none" id="save_submit" onclick="event.preventDefault(); submit_dtg('submit');">Submit</button>
                            </div>

                            <div class="d-flex gap-4 d-none" id="edit_data">
                                <button class="btn-black rounded px-5" onclick="event.preventDefault(); edit_data();">Edit</button>
                            </div>

                            <div class="d-flex gap-4 d-none" id="cancel_save_edit">
                                <button class="btn-transparent border-0 fw-bold" onclick="event.preventDefault(); cancel_edit()">Cancel</button>
                                <button class="btn-black rounded px-3 d-none" id="save_edit" onclick="event.preventDefault(); submit_dtg('save');">Save</button>
                            </div>
                        </div>

                        <div class="d-flex my-3">
                            <div class="d-flex flex-column flex-fill">
                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Tgl. Invoice</div>
                                    <div class="mb-2">&nbsp; : &nbsp;{{ $dt_invoice->tanggal }}</div>
                                    <!-- <input id="tgldtg" name="tgldtg" class="easyui-datebox" placeholder="YYYY-MM-DD" data-options="formatter:myformatter, parser:myparser" value=""> -->
                                </div>

                                <div class="d-flex p-1" id="lookup">
                                    <div class="p-0 w-25">No. SPB</div>
                                    <div class="mb-2">&nbsp; : &nbsp;{{ $dt_invoice->nobukti }}</div>
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Gudang</div>
                                    <div class="mb-2">&nbsp; : &nbsp;{{ $dt_invoice->gudnm }} </div>
                                </div>

                            </div>

                            <div class="d-flex flex-column flex-fill">

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">No KK</div>
                                    <input id="nokk" class="easyui-validatebox height_input" name="nokk" type="text" value="nokk">
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Price + PPN</div>
                                    <input id="priceppn" class="easyui-validatebox height_input" name="priceppn" type="text" value="priceppn" disabled>
                                </div>

                                <div class="d-flex p-1">
                                    <div class="p-0 w-25">Kurs Pajak</div>
                                    <input id="kursp" class="easyui-validatebox height_input" name="kursp" type="text" value="kursp">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div id="add" class="p-0 d-none">
                <div class="p-0 font-5 color-view-blue pointer fw-bold" id="adddt" onclick="tambahrow();">Add Data</div>
            </div>

            <div class="p-0">
                <table id="dg" class="easyui-datagrid" title="" style="width:100%; height:auto;" align="center" toolbar="#tb" striped="false" pagination="false" method="get" url="{{ route('api.get-invoice-detail', $dt_invoice->invpk)}}"
                    rownumbers="false" singleSelect="false" collapsible="true" fitColumns="true" idField="invdtpk"
                    data-options="onCheck:function(){menu();},onCheckAll:function(){menu();},onUncheck:function(){menu();},onUncheckAll:function(){menu2();},
                    multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false,
                    rowStyler:function(index,row){
                        if (row.copy==3){
                            return 'color:#000000;background-color:#D6D6D6;';
                        }
                    }">
                    <thead>
                        <tr>
                            <th field="ck" checkbox="true" hidden="true"></th>
                            <th field="invdtpk" width="50" hidden="true">dtgdtpk</th>
                            <th field="index" width="40">No</th>
                            <th field="mpo" width="150" editor="text">MPO</th>
                            <th field="qty" width="100" editor="text">QTY</th>
                            <th field="pricedt" width="100" editor="text">Price</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- menu header -->
<div class="header" id="myHeader" onclick="" style="display:none;">
    <div id="count" style="display:none;"></div>
    <div id="count2" style="display:none;">Select items to replace</div>
    <div class="delete" id="mreplace1" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true" onclick="replace1();" style="color: #FFFFFF; text-decoration: none;"> Copy & Replace </a></div>
    <div class="delete" id="mreplace2" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true" onclick="replace2();" style="color: #FFFFFF; text-decoration: none;"> Replace data </a></div>
    <div class="delete" id="mcancopy" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true" onclick="cancopy();" style="color: #FFFFFF; text-decoration: none;"> Cancel </a></div>
    <div class="delete" id="mdelete" style="width:auto;display:none;"><a href="javascript:void(0)" plain="true" onclick="delete_list_barang();" style="color: #FFFFFF; text-decoration: none;"> Delete </a></div>
    <div class="delete" id="mclose" style="width:auto;">
        <a href="javascript:void(0)" plain="true" onclick="$('#myHeader').hide(); $('#dg').datagrid('clearSelections'); $('#dg').datagrid('clearChecked'); closemenu();" style="color: #FFFFFF; text-decoration: none;"> Close menu </a>
    </div>
</div>


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

<div class="modal fade" id="copydata" tabindex="-1" aria-labelledby="copydataLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content p-4">
            <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                <p class="p-0 m-0 fw-bold h6">Pilih data mana yang akan di copy.</p>
            </div>
            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div class="d-flex text-left">
                    <table>
                        <tr style="height:25px;">
                            <td>
                                <a href="Javascript:void(0);" style="text-decoration:none;">
                                    <input type="checkbox" name="all" value="99" id="all" onclick="">
                                    <span
                                        style="font-weight:700; font-size:14px; line-height:18px; color:rgba(0,0,0,0.38);">
                                        All </span></a>
                            </td>
                        </tr>
                        <tr style="height:25px;">
                            <td>
                                <a href="Javascript:void(0);" style="text-decoration:none;">
                                    <input type="checkbox" name="ceek1" value="1" id="ceek1" onclick="cheek();">
                                    <span
                                        style="font-weight:700; font-size:14px; line-height:18px; color:rgba(0,0,0,0.38);">
                                        Tgl. Faktur </span></a>
                            </td>
                            <td> :
                                <span id="tglfak"
                                    style="font-weight:400; font-size:16px; line-height:20px; color:#000000;"></span>
                            </td>
                        </tr>
                        <tr style="height:25px;">
                            <td>
                                <a href="Javascript:void(0);" style="text-decoration:none;">
                                    <input type="checkbox" name="ceek2" value="1" id="ceek2" onclick="cheek();">
                                    <span
                                        style="font-weight:700; font-size:14px; line-height:18px; color:rgba(0,0,0,0.38);">
                                        No. Faktur </span></a>
                            </td>
                            <td> :
                                <span id="nofak"
                                    style="font-weight:400; font-size:16px; line-height:20px; color:#000000;"></span>
                            </td>
                        </tr>
                        <tr style="height:25px;">
                            <td>
                                <a href="Javascript:void(0);" style="text-decoration:none;">
                                    <input type="checkbox" name="ceek3" value="1" id="ceek3" onclick="cheek();">
                                    <span
                                        style="font-weight:700; font-size:14px; line-height:18px; color:rgba(0,0,0,0.38);">
                                        Kode Barang </span></a>
                            </td>
                            <td> :
                                <span id="kdbrg"
                                    style="font-weight:400; font-size:16px; line-height:20px; color:#000000;"></span>
                            </td>
                        </tr>
                        <tr style="height:25px;">
                            <td>
                                <a href="Javascript:void(0);" style="text-decoration:none;">
                                    <input type="checkbox" name="ceek4" value="1" id="ceek4" onclick="cheek();">
                                    <span
                                        style="font-weight:700; font-size:14px; line-height:18px; color:rgba(0,0,0,0.38);">
                                        Qty Surat Jalan </span></a>
                            </td>
                            <td> :
                                <span id="qtydtg"
                                    style="font-weight:400; font-size:16px; line-height:20px; color:#000000;"></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="modal-footer m-0 p-0 border-0">
                <div class="container-fluid">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="px-3 py-1 border-0 btn-transparent" data-bs-dismiss="modal"
                            onclick="cancelcopy();">Close</button>
                        <button id="confirmBtn" type="button" class="px-3 py-1 rounded" data-bs-dismiss="modal"
                            onclick="savereplace();" disabled>
                            Confirm
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection