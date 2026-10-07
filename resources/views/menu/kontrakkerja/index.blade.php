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
            // loadDataByFilter();

            $('.dropdown-menu-sortlist a').on('click', function(event) {
                event.preventDefault();

                var buttonText = $(this).text();
                var imagePath = "{{ asset('public/css/images/sort.jpg') }}";

                $('#title-sortlistByDate').text(buttonText);
            });

            $('#filterByMonth').combobox({
                onChange: function(value) {
                    if ($(this).data('ignoreChange')) return false;
                    loadDataByFilter();
                    // console.log(`filterByMonth onchange running with value: ${value}`);
                },
                onBeforeLoad: function() {
                    // console.log(`filterByMonth url ${$(this).combobox('options').url}`);
                    const month = (new Date().getMonth() + 1).toString().padStart(2, '0');
                    $(this).data('ignoreChange', true);
                    $(this).combobox('setValue', month);
                    $(this).data('ignoreChange', false);
                }
            })

            $('#filterByYear').combobox({
                onChange: function(value) {
                    if ($(this).data('ignoreChange')) return false;
                    loadDataByFilter();
                    // console.log(`filterByYear onchange running with value: ${value}`);
                },
                onBeforeLoad: function() {
                    // console.log(`filterByYear url ${$(this).combobox('options').url}`);
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
            $('#dg-listdb').datagrid('getPanel').find('div.datagrid-header .datagrid-header-inner .datagrid-htable')
                .addClass('w-100');

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
            return `<a type="button" class="txt-359DD9-400-14-18-100" onclick="onEditListdb('${row.kkpk}')">edit</a>`;
        }

        function onCreateListdb() {
            console.log(`onCreateListdb is running...`);

            $('#createOrEditListdb').modal('show');

            $('.listdb-create').removeClass('d-none');
            $('.listdb-edit').addClass('d-none');

        }

        function onEditListdb(kkpk) {
            console.log('onEditListdb is running...', kkpk);

            if (!kkpk) {
                alert('ID kontrak kerja tidak valid');
                return;
            }

            // buka modal
            $('#createOrEditListdb').modal('show');

            // mode EDIT
            $('#inputKk').removeClass('d-none');
            $('.listdb-edit').removeClass('d-none');
            $('.listdb-create').addClass('d-none');

            // simpan kkpk ke hidden input
            $('#listdbpk').val(kkpk);

            let url = "{{ url('kontrakKerja/detail/:kkpk') }}".replace(':kkpk', kkpk);

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    console.log('success onEditListdb >>>', response);

                    if (!response || !response.data) {
                        alert('Data kontrak kerja tidak ditemukan');
                        return;
                    }

                    let data = response.data;

                    // tanggal (format yyyy-mm-dd)
                    $('#tglkk').val(data.tglkk);

                    $('#kkno').val(data.kkno);

                    // subkon combobox
                    if (data.subkonpk) {
                        $('#subkonpk').combobox('setValue', data.subkonpk);
                    }

                    // tampilkan user yang buat kontrak
                    $('#userr').val(data.username || '');

                    // factory tujuan
                    $('#funm').val(data.funm);

                    $('#username').val(data.username);

                    // keterangan
                    $('#ketkk').val(data.ketkk);

                    // materai
                    if (data.stsmat == 1) {
                        $('#stsmat').prop('checked', true);
                        $('#container-jmlmat').removeClass('d-none');
                        $('#jmlmat').val(data.jmlmat);
                    } else {
                        $('#stsmat').prop('checked', false);
                        $('#container-jmlmat').addClass('d-none');
                        $('#jmlmat').val(0);
                    }

                },
                error: function(xhr) {
                    console.error('error onEditListdb >>>', xhr);
                    alert('Gagal memuat data kontrak kerja');
                }
            });
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
            $('#inputKk').addClass('d-none');
            $('#ermsg-keterangan').text(null);
            $('#ermsg-userr').text(null);
            $('#loading-edit-listdb').addClass('d-none');
            $('#loading-create-listdb').addClass('d-none');
        }

        function validateListdb() {
            console.log('validateListdb is running...');

            let valid = true;
            const isSpecialUser = (guserid === 'FIN' || guserid === 'SPR');

            // reset error message
            $('.text-blink').text('');

            /* =========================
               SUB KONTRAK
            ========================= */
            let subkonValue = $('#subkonpk').combobox('getValue');
            let subkonText = $('#subkonpk').combobox('getText');

            if (!subkonValue && !subkonText) {
                $('#ermsg-subkonpk').text('Factory tujuan wajib diisi');
                valid = false;
            }

            /* =========================
               TANGGAL KONTRAK
            ========================= */
            let tglkk = $('#tglkk').val();
            if (!tglkk) {
                $('#ermsg-tglkk').text('Tanggal kontrak wajib diisi');
                valid = false;
            }

            /* =========================
               Order By
            ========================= */
            let funm = $('#funm').val().trim();
            if (!funm) {
                $('#ermsg-funm').text('Order By wajib diisi');
                valid = false;
            }

            /* =========================
               KETERANGAN
            ========================= */
            let ketkk = $('#ketkk').val().trim();
            if (!isSpecialUser && !ketkk) {
                $('#ermsg-ketkk').text('Keterangan wajib diisi');
                valid = false;
            }

            /* =========================
               USER LOGIN (PENTING)
            ========================= */
            if (typeof userpk === 'undefined' || !userpk) {
                $('#ermsg-userr').text('User tidak valid, silakan login ulang');
                valid = false;
            }

            /* =========================
               MATERAI
            ========================= */
            if ($('#stsmat').is(':checked')) {
                let jmlmat = $('#jmlmat').val();
                if (!jmlmat || parseInt(jmlmat) <= 0) {
                    $('#ermsg-jmlmat').text('Jumlah materai wajib diisi');
                    valid = false;
                }
            }

            /* =========================
               AUTO CLEAR ERROR
            ========================= */
            if (!valid) {
                setTimeout(() => {
                    $('.text-blink').text('');
                }, 5000);
            }

            console.log(valid ? 'validateListdb is valid' : 'validateListdb is invalid');
            return valid;
        }

        function onUpdateListdb() {
            console.log('onUpdateListdb running...');

            var kkpk = $('#listdbpk').val();
            if (!kkpk) {
                alert('ID kontrak kerja tidak valid');
                return;
            }

            if (!validateListdb()) return;

            $('#loading-edit-listdb').removeClass('d-none');

            let url = "{{ url('kontrakKerja/update/:kkpk') }}".replace(':kkpk', kkpk);

            // ambil data dari form
            let formdata = new FormData();
            formdata.append('subkonpk', $('#subkonpk').combobox('getValue'));
            formdata.append('tglkk', $('#tglkk').val());
            formdata.append('funm', $('#funm').val());
            formdata.append('ketkk', $('#ketkk').val() || '');
            formdata.append('stsmat', $('#stsmat').is(':checked') ? 1 : 0);
            formdata.append('jmlmat', $('#jmlmat').val() || 0);

            // NOTE: userpk tidak dikirim supaya tetap user asli yang buat kontrak

            $.ajax({
                url: url,
                type: 'POST',
                data: formdata,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    console.log('success update >>>', res);

                    if (!res || !res.status) {
                        alert(res.message || 'Gagal update kontrak kerja');
                        return;
                    }

                    onCancelListdb();
                    $('#dg-listdb').datagrid('reload');
                },
                error: function(xhr) {
                    console.error('error update >>>', xhr);
                    let msg = 'Gagal update kontrak kerja';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg += '\n' + JSON.stringify(xhr.responseJSON.errors);
                    }
                    alert(msg);
                },
                complete: function() {
                    $('#loading-edit-listdb').addClass('d-none');
                }
            });
        }


        function onSubmitListdb() {
            console.log('onSubmitKk is running...');

            if (!validateListdb()) return;

            $('#loading-create-listdb').removeClass('d-none');

            let url = "{{ url('kontrakKerja/store') }}";
            console.log('url:', url);

            let formdata = new FormData();

            /* SUBKON */
            let subkonpk = $('#subkonpk').combobox('getValue');
            if (!subkonpk) {
                alert('Factory Tujuan wajib dipilih');
                $('#loading-create-listdb').addClass('d-none');
                return;
            }
            formdata.append('subkonpk', subkonpk);

            /* FIELD UTAMA */
            formdata.append('tglkk', $('#tglkk').val());
            formdata.append('funm', $('#funm').val());
            formdata.append('ketkk', $('#ketkk').val());

            /* MATERAI */
            let stsmat = $('#stsmat').is(':checked') ? 1 : 0;
            let jmlmat = stsmat ? ($('#jmlmat').val() || 0) : 0;

            formdata.append('stsmat', stsmat);
            formdata.append('jmlmat', jmlmat);

            $.ajax({
                url: url,
                type: 'POST',
                data: formdata,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('success onSubmitKk >>>', response);

                    if (!response || !response.data || !response.data.kkno) {
                        alert('Data berhasil disimpan, tapi nomor kontrak tidak ditemukan');
                        return;
                    }

                    onCancelListdb();
                    $('#dg-listdb').datagrid('reload');

                    // FORMAT NOMOR KONTRAK
                    let nomor = response.data.kkno.toString().padStart(3, '0');
                    let tahun = $('#tglkk').val().substring(0, 4);
                    let formatted = `${nomor}/MIF-SUBKON/${tahun}`;

                    $('.dt-nomor').text(formatted);
                    $('#informationAlert').modal('show');
                },
                error: function(xhr) {
                    console.error('error onSubmitKk >>>', xhr);
                    alert('Gagal menyimpan kontrak kerja');
                },
                complete: function() {
                    $('#loading-create-listdb').addClass('d-none');
                }
            });
        }

        function sortlistByDate(value) {
            document.getElementById("sortlistByDate").value = value;

            loadDataByFilter();
        }

        function loadDataByFilter() {
            console.log('loadDataByFilter is running');

            const filterByMonth = $('#filterByMonth').combobox('getValue') || '';
            const filterByYear = $('#filterByYear').combobox('getValue') || '';
            const sortlistByDate = $('#sortlistByDate').val() || '';
            const search = $('#searchByInput').val();

            $('#dg-listdb').datagrid('load', {
                userpk: userpk,
                guserid: guserid,
                filterByMonth: filterByMonth,
                filterByYear: filterByYear,
                sortlistByDate: sortlistByDate,
                search: search
            });
        }

        $(function() {
            $('#dg-listdb').datagrid({
                url: "{{ url('kontrakKerja/list') }}",
                method: 'get',
                onLoadSuccess: function(data) {
                    styleDGListdb();
                    $(this).datagrid('resize');
                }
            });

            loadDataByFilter();
        });

        function formatMaterai(value, row, index) {
            // pakai field stsmat_label
            return row.stsmat_label ?? (value ? 'Iya' : 'Tidak');
        }

        function openModalSubkon() {
            $('#subkon_nm').val('');
            $('#ermsg-subkon_nm').text('');
            $('#modalSubkon').modal('show');
        }

        function storeSubkon() {
            let name = $('#subkon_nm').val().trim();

            if (!name) {
                $('#ermsg-subkon_nm').text('Nama sub kontrak wajib diisi');
                return;
            }

            $.ajax({
                url: "{{ url('subkon/create') }}",
                method: 'POST',
                data: {
                    subnm: name,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {

                    // Reload datasource combobox
                    $('#subkonpk').combobox('reload');

                    // Setelah reload selesai → set value
                    $('#subkonpk').combobox({
                        onLoadSuccess: function() {
                            $('#subkonpk').combobox('setValue', res.data.subkonpk);
                        }
                    });

                    // Tutup modal subkon
                    $('#modalSubkon').modal('hide');

                    // Kembali ke modal KK
                    $('#modalSubkon').one('hidden.bs.modal', function() {
                        $('#createOrEditListdb').modal('show');
                        $('#subkonpk').combobox('textbox').focus();
                    });
                },
                error: function(xhr) {
                    alert('Sub kontrak sudah ada / gagal disimpan');
                }
            });
        }

        function toggleJumlahMaterai() {
            const checkbox = document.getElementById('stsmat');
            const container = document.getElementById('container-jmlmat');
            const inputJmlmat = document.getElementById('jmlmat');

            if (checkbox.checked) {
                container.classList.remove('d-none'); // tampilkan label + input
                inputJmlmat.value = ''; // siap diisi user
            } else {
                container.classList.add('d-none'); // sembunyikan semuanya
                inputJmlmat.value = 0; // reset nilai
            }
        }

        // event saat checkbox di klik
        document.getElementById('stsmat').addEventListener('change', toggleJumlahMaterai);

        // set kondisi awal saat halaman pertama kali dibuka
        toggleJumlahMaterai();
    </script>
@endsection

@section('content')
    <div class="main-wrapper">
        <div class="container-fluid p-4">
            <div class="d-grid">
                <div class="p-2 pe-0">
                    <span class="txt-000000-700-24-28-100">List Kontrak Kerja</span>
                </div>
                <div class="d-flex flex-wrap">
                    <div class="d-flex flex-grow-1 flex-wrap p-2 pe-0 pt-0 gap-2 align-items-center">
                        <div class="p-0">
                            <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                                <span class="input-group-text search">
                                    <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px"
                                        height="18px">
                                </span>
                                <input type="text" class="form-control search" id="searchByInput"
                                    onkeyup="loadDataByFilter()" placeholder="Search...">
                            </div>
                        </div>
                        <div class="p-0">
                            <input id="filterByMonth" class="easyui-combobox" style="width: 100%;" method="get"
                                data-options="valueField:'monthval',textField:'monthnm',url:'{{ url('api/utility/get-month') }}',editable:false">
                        </div>
                        <div class="p-0">
                            <input id="filterByYear" class="easyui-combobox" style="width: 100%;" method="get"
                                data-options="valueField:'year',textField:'year',url:'{{ url('api/master/invs/get-year') }}',editable:false">
                        </div>
                        <div class="p-0">
                            <button onclick="onCreateListdb('create')" class="btn btn-dark btn-sm">&#10010; Create Kontrak
                                Kerja</button>
                        </div>
                    </div>
                    <div class="p-0 d-flex align-items-center">
                        <div class="btn-group">
                            <input type="hidden" id="sortlistByDate" name="sortlistByDate" value="{{ 11 }}"
                                size="5">
                            <div class="btn-group">
                                <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0"
                                    data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
                                    <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
                                    <span id="title-sortlistByDate">Tanggal (asc)</span>
                                </button>
                                <ul id="menu-sortlist"
                                    class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                                    <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2"
                                            onclick="sortlistByDate(11)">Tanggal (asc)</a></li>
                                    <li>
                                        <hr class="dropdown-divider mt-0 mb-0">
                                    </li>
                                    <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2"
                                            onclick="sortlistByDate(12)">Tanggal (desc)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-grid p-2 pe-0">
                    <table id="dg-listdb" class="easyui-datagrid" title="" style="height: auto;" align="center"
                        toolbar="#tb" striped="false" idField="kkpk" pageSize="100" pageList="[100,200,300,500]"
                        pagination="true" rownumbers="false" singleSelect="true" collapsible="true"
                        data-options="autoRowHeight:true, fitColumns:true, multiSort:true, remoteSort:false, border:false">
                        <thead>
                            <tr>
                                <th field="no" width="auto" styler="styler1">No</th>
                                <th field="tglkk" width="auto" styler="styler2">Tanggal</th>
                                <th field="kkno" width="auto" styler="styler2" sortable="true">Kontrak Kerja No</th>
                                <th field="ketkk" width="auto" styler="styler2">Perihal</th>
                                <th field="subkon" width="auto" styler="styler2">Factory Tujuan</th>
                                <th field="funm" width="auto" styler="styler2">Order By</th>
                                <th field="username" width="auto" styler="styler2">User</th>
                                <th field="stsmat" width="auto" styler="styler2" formatter="formatMaterai">Materai</th>
                                <th field="act" width="auto" styler="styler3" formatter="attrAct">Act</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createOrEditListdb" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="createOrEditListdbLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h1 class="txt-000000-700-18-24-100" id="createOrEditListdbLabel"><span
                            class="listdb-create d-none">Create New</span><span class="listdb-edit d-none">Edit</span>
                        Kontrak Kerja</h1>
                </div>
                <div class="modal-body">
                    <div class="container-fluid p-0">
                        <div class="d-flex flex-column gap-2">
                            <form id="formCreateOrEditListdb" class="d-flex flex-column gap-2">
                                <input type="hidden" id="listdbpk">

                                <div id="inputKk" class="p-0 d-flex flex-column d-none">
                                    <label for="kkno">Kontrak Kerja<span class="text-danger">*</span></label>
                                    <input type="text" id="kkno"class="form-control" readonly>
                                    <span id="ermsg-kkno" class="text-danger text-blink"></span>
                                </div>

                                <div class="p-0 d-flex flex-column">
                                    <label for="tglkk">Tanggal<span class="text-danger">*</span></label>
                                    <input type="date" id="tglkk" class="form-control"
                                        value="{{ date('Y-m-d') }}" readonly>
                                    <span id="ermsg-tglkk" class="text-danger text-blink"></span>
                                </div>

                                <div class="p-0 d-flex flex-column">
                                    <div class="me-2" style="width:150px">
                                        Factory Tujuan<span class="text-danger">*</span>
                                    </div>
                                    <div class="d-flex align-items-center">

                                        <select id="subkonpk" class="easyui-combobox"
                                            style="width:300px; height:38px; line-height:38px;"
                                            data-options="
                                            url:'{{ url('subkon/list') }}',
                                            method:'get',
                                            valueField:'subkonpk',
                                            textField:'subnm',
                                            editable:true,
                                            panelHeight:'auto'
                                        ">
                                        </select>

                                        <button type="button"
                                            class="btn btn-sm btn-link ms-2 p-0 txt-359DD9-400-14-18-100"
                                            onclick="openModalSubkon()">
                                            add new
                                        </button>

                                        <span id="ermsg-subkonpk" class="text-danger text-blink ms-2"></span>
                                    </div>

                                </div>

                                <div class="p-0 d-flex flex-column">
                                    <label for="funm">Order By<span class="text-danger">*</span></label>
                                    <input type="text" id="funm"class="form-control">
                                    <span id="ermsg-funm" class="text-danger text-blink"></span>
                                </div>
                                <div class="p-0 d-flex flex-column">
                                    <label for="ketkk">Keterangan<span class="text-danger">*</span></label>
                                    <input type="text" id="ketkk"class="form-control">
                                    <span id="ermsg-ketkk" class="text-danger text-blink"></span>
                                </div>

                                <div class="p-0 d-flex align-items-center gap-2">
                                    <input type="checkbox" id="stsmat" value="1"
                                        style="width: 18px; height: 18px;">
                                    <label for="stsmat" class="mb-0">Materai</label>
                                </div>

                                <div id="container-jmlmat" class="p-0 d-flex flex-column d-none">
                                    <label for="jmlmat">Jumlah Materai<span class="text-danger">*</span></label>
                                    <input type="number" id="jmlmat" class="form-control" value="0"
                                        min="0">
                                </div>

                                <?php
                                    $guserid = Session::get('guserid');
                                ?>

                                <div class="p-0 d-flex flex-column">
                                    <label for="userr">User<span class="text-danger">*</span></label>
                                    <input type="text" id="username" class="form-control" 
                                    value="{{ Session::get('login');}}"
                                    readonly>
                                </div>
                                {{-- <div class="p-0 d-flex flex-column">
                                    <label for="userr">User<span class="text-danger">*</span></label>
                                    <input type="text" id="userr" class="form-control" readonly>
                                </div> --}}



                            </form>

                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" onclick="onCancelListdb()">Cancel</button>
                    <button type="button" class="btn btn-dark listdb-create d-none" onclick="onSubmitListdb()">Submit
                        <span id="loading-create-listdb" class="spinner-grow spinner-grow-sm d-none"
                            aria-hidden="true"></span>
                    </button>
                    <button type="button" class="btn btn-dark listdb-edit d-none" onclick="onUpdateListdb()">Update
                        <span id="loading-edit-listdb" class="spinner-grow spinner-grow-sm d-none"
                            aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="informationAlert" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="informationAlertLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h1 class="txt-000000-700-18-24-100" id="informationAlertLabel">Information</h1>
                </div>
                <div class="modal-body pt-0">
                    <div class="container-fluid p-0">
                        <div class="d-flex flex-column gap-2">
                            <span class="text-center">Berikut nomor kontrak kerja yang Anda dapatkan</span>
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
    <div class="modal fade" id="modalSubkon" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Tambah Sub Kontrak</h6>
                </div>

                <div class="modal-body">
                    <label>Nama Sub Kontrak</label>
                    <input type="text" id="subkon_nm" class="form-control">
                    <span id="ermsg-subkon_nm" class="text-danger text-blink"></span>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-dark btn-sm" onclick="storeSubkon()">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection
