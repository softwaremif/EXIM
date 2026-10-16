<div class="modal fade" id="modalCreateContainer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content mc-content">

            <!-- HEADER -->
            <style>
                /* Header modal: teks panjang (Order / PO) wajib wrap supaya
                   tidak mendorong tombol close keluar modal. */
                #modalCreateContainer .mc-header {
                    align-items: flex-start;
                }

                #modalCreateContainer .mc-header-text {
                    flex: 1 1 auto;
                    min-width: 0;
                }

                #modalCreateContainer .mc-subtitle {
                    display: flex;
                    flex-direction: column;
                    gap: 2px;
                    margin-top: 6px;
                    max-height: 96px;
                    overflow-y: auto;
                    padding-right: 4px;
                }

                #modalCreateContainer .mc-subtitle > span {
                    display: block;
                    overflow-wrap: anywhere;
                    line-height: 1.45;
                }

                #modalCreateContainer .mc-subtitle > span:empty {
                    display: none;
                }

                #modalCreateContainer .mc-subtitle::-webkit-scrollbar {
                    width: 4px;
                }

                #modalCreateContainer .mc-subtitle::-webkit-scrollbar-thumb {
                    background: rgba(255, 255, 255, .35);
                    border-radius: 4px;
                }

                #modalCreateContainer .mc-close {
                    flex-shrink: 0;
                }
            </style>
            <div class="mc-header">
                <div class="mc-header-icon"><i class="bi bi-box-seam"></i></div>
                <div class="mc-header-text">
                    <h5 class="mc-title">Edit Shipment Plan</h5>
                    <div class="mc-subtitle">
                        <span id="ordnop"></span>
                        <span id="buyerp"></span>
                        <span id="POnop"></span>
                    </div>
                </div>
                <button type="button" class="mc-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- TAB NAV -->
            <div class="mc-tabs" id="mcTabs">
                <button type="button" class="mc-tab active" data-tab="shipplan">
                    <i class="bi bi-journal-bookmark-fill"></i> First Plan Shipment
                </button>
                <button type="button" class="mc-tab" data-tab="capacity">
                    <i class="bi bi-speedometer2"></i> Forwarder Confirm
                </button>
                <button type="button" class="mc-tab" data-tab="info">
                    <i class="bi bi-truck"></i> Container in Factory
                </button>
                <button type="button" class="mc-tab" data-tab="schedule">
                    <i class="bi bi-calendar-week"></i> Container Leave
                </button>
                <span class="mc-tab-indicator"></span>
            </div>

            <form id="formCreateContainer">
                <div class="modal-body mc-body">
                    <input type="hidden" name="exportpk" id="exportpk" value="">

                    <!-- TAB 1: Shipplan -->
                    <div class="mc-pane active" data-pane="shipplan">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-calendar-event"></i>Estimasi Container
                                    arrival</label>
                                <input class="easyui-datebox mc-input" style="width:250px;height:40px;" id="contdate"
                                    name="contdate" data-options="formatter:myformatter,parser:myparser">
                            </div>
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-person-lines-fill"></i> Shipment
                                    Method</label>
                                <input class="easyui-combobox fwd-combo mc-input" name="vassal" id="vassal"
                                    style="width:250px;height:40px;" data-options="
                                    valueField:'vassalpk',
                                    textField:'typenm',
                                    url:'{{ route('getVassal') }}',
                                    method:'get',
                                    prompt:'Pilih Vassal',
                                    panelHeight:'auto',
                                    panelMaxHeight:200
                                ">
                            </div>
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-file-text"></i> No Booking</label>
                                <div class="mc-input-suffix">
                                    <input type="text" name="nobook" id="nobook" class="mc-input"
                                        placeholder="cth. G2344SG">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="mc-label"><i class="bi bi-chat-left-text"></i> Catatan</label>
                                <textarea class="mc-input" name="remark" id="remark" rows="3"
                                    placeholder="Catatan tambahan untuk tim shipment..."></textarea>
                            </div>

                            <div class="mc-pane-toolbar">
                                <div class="mc-pane-toolbar-text">
                                    <i class="bi bi-info-circle"></i>
                                    Satu shipment plan bisa berisi lebih dari satu container
                                </div>
                                <span class="mc-count-pill" id="containerCountPill">1 Container</span>
                            </div>

                            <div id="containerList" class="container-list"></div>

                            <button type="button" class="btn-add-container" id="btnAddContainer">
                                <i class="bi bi-plus-circle-fill"></i> Tambah Container
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: KAPASITAS -->
                    <div class="mc-pane" data-pane="capacity">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-calendar-event"></i>Ex-Factory Deadline</label>
                                <input class="easyui-datetimebox mc-input" style="width:250px;height:40px;" id="exdate"
                                    name="exdate" data-options="formatter:myformatter2,parser:myparser3">
                            </div>
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-calendar-event"></i>Forwarder Close Time</label>
                                <input class="easyui-datetimebox mc-input" style="width:250px;height:40px;"
                                    id="closetime" name="closetime"
                                    data-options="formatter:myformatter2,parser:myparser2,editable: false">
                            </div>
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-person-lines-fill"></i> Forwarder</label>
                                <input class="easyui-combobox fwd-combo mc-input" name="fwd" id="fwd"
                                    style="width:250px;height:40px;" data-options="
                                valueField:'fwdpk',
                                textField:'fwdnm',
                                url:'{{ route('getFwd') }}',
                                method:'get',
                                prompt:'Pilih Forwarder',
                                panelHeight:'auto',
                                panelMaxHeight:200
                            ">
                            </div>
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-person-lines-fill"></i>Port of Loading</label>
                                <input class="easyui-combobox fwd-combo mc-input" name="plb" id="plb"
                                    style="width:250px;height:40px;" data-options="
                                valueField:'plbpk',
                                textField:'plbnm',
                                url:'{{ route('getPlb') }}',
                                method:'get',
                                prompt:'Pilih Port',
                                panelHeight:'auto',
                                panelMaxHeight:200
                            ">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: INFO CONTAINER (sinkron dengan container dari tab shipplan) -->
                    <div class="mc-pane" data-pane="info">
                        <div class="mc-pane-toolbar">
                            <div class="mc-pane-toolbar-text">
                                <i class="bi bi-info-circle"></i>
                                Lengkapi No. Container untuk setiap container saat sudah tiba
                            </div>
                            <span class="mc-count-pill" id="containerArrivalCountPill">0 Container</span>
                        </div>

                        <div id="containerArrivalList" class="container-list"></div>
                    </div>

                    <!-- TAB 3: JADWAL -->
                    <div class="mc-pane" data-pane="schedule">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-calendar-event"></i> Vessel ETD (Estimasi
                                    Berangkat)</label>
                                <input class="easyui-datebox mc-input" style="width:250px;height:40px;" id="etdvess"
                                    name="etdvess" data-options="formatter:myformatter,parser:myparser">
                            </div>
                            <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-calendar-check"></i> Vessel ETA (Estimasi
                                    Tiba)</label>
                                <input class="easyui-datebox mc-input" style="width:250px;height:40px;" id="etavess"
                                    name="etavess" data-options="formatter:myformatter,parser:myparser">
                            </div>
                            {{-- <div class="col-md-6">
                                <label class="mc-label"><i class="bi bi-file-text"></i> PEB Number</label>
                                <div class="mc-input-suffix">
                                    <input type="text" name="pebno" id="pebno" class="mc-input"
                                        placeholder="cth. SL-88213">
                                </div>
                            </div> --}}

                            <div class="mc-pane-toolbar mt-4">
                                <div class="mc-pane-toolbar-text">
                                    <i class="bi bi-camera-fill"></i>
                                    Upload foto kondisi tiap container
                                </div>
                                <span class="mc-count-pill" id="containerFotoCountPill">0 Container</span>
                            </div>

                            <div id="containerFotoList" class="container-list"></div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer mc-footer">
                    <div class="mc-step-dots">
                        <span class="dot" active data-dot="shipplan"></span>
                        <span class="dot" data-dot="capacity"></span>
                        <span class="dot" data-dot="info"></span>
                        <span class="dot" data-dot="schedule"></span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="mc-btn mc-btn-ghost" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="mc-btn mc-btn-outline" id="mcPrevBtn" onclick="mcNav(-1)" disabled>
                            <i class="bi bi-arrow-left"></i> Kembali
                        </button>
                        <button type="button" class="mc-btn mc-btn-primary" id="mcNextBtn" onclick="mcNav(1)">
                            Lanjut <i class="bi bi-arrow-right"></i>
                        </button>
                        <button type="submit" class="mc-btn mc-btn-success d-none" id="mcSubmitBtn">
                            <i class="bi bi-check2-circle"></i> Buat Container
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TEMPLATE 1 CARD CONTAINER Untuk tab 1 (disembunyikan, dipakai untuk cloning via JS) -->
<template id="containerCardTemplate">
    <div class="container-item">
        <div class="container-item-header">
            <span class="container-badge">
                <i class="bi bi-box-seam"></i> Container <span class="container-badge-num">1</span>
            </span>
            <button type="button" class="btn-remove-container" title="Hapus container ini">
                <i class="bi bi-trash3"></i>
            </button>
        </div>

        <input type="text" class="mc-input d-none" data-field="start_ship">

        <div class="row g-3">
            <div class="col-md-6">
                <label class="mc-label"><i class="bi bi-rulers"></i> Tipe Container</label>
                <div class="mc-choice-group">
                    <label class="mc-choice">
                        <input type="radio" name="ctype" value="20ft">
                        <span>20 ft</span>
                    </label>
                    <label class="mc-choice">
                        <input type="radio" name="ctype" value="40ft">
                        <span>40 ft</span>
                    </label>
                    <label class="mc-choice">
                        <input type="radio" name="ctype" value="40hc">
                        <span>40 ft HC</span>
                    </label>
                    <label class="mc-choice">
                        <input type="radio" name="ctype" value="45ft">
                        <span>45 ft</span>
                    </label>
                    <label class="mc-choice">
                        <input type="radio" name="ctype" value="LCL">
                        <span>LCL</span>
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="mc-label"><i class="bi bi-truck"></i> Vessel Name</label>
                <input type="text" class="mc-input" data-field="vessname" placeholder="cth. Rotterdam, Belanda">
            </div>

            <div class="col-md-6 d-none" id="volumeContainer">
                <label class="mc-label"><i class="bi bi-box-seam"></i> Total Volume Merchandise (CBM)</label>
                <div class="mc-input-suffix">
                    <input type="number" step="any" min="0" class="mc-input" data-field="volume" placeholder="cth. 10.5">
                    <span>Cbm</span>
                </div>
            </div>
        </div>
    </div>
</template>

<!--UNTUK PAGE 3 container-->
<template id="containerArrivalCardTemplate">
    <div class="container-item container-item-readonly">
        <div class="container-item-header">
            <span class="container-badge">
                <i class="bi bi-box-seam"></i> Container <span class="container-arrival-num">1</span>
            </span>
            <span class="container-arrival-vessel text-muted small"></span>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="mc-label"><i class="bi bi-calendar-event"></i> Actual Container Arrival in Fty</label>
                {{-- isi <template> tidak pernah ikut di-parse easyui saat DOM ready.
                    widget-nya diinisialisasi manual di syncContainerArrivalTab() setelah kartu di-append. --}}
                    <input class="easyui-datetimebox mc-input" style="width:250px;height:40px;" data-field="actcontdate"
                        data-options="formatter:myformatter2,parser:myparser2">
            </div>
            <div class="col-md-6">
                <label class="mc-label"><i class="bi bi-upc-scan"></i> No. Container</label>
                <input type="text" class="mc-input" data-field="contno" placeholder="cth. TCLU1234567">
            </div>

            <div class="col-md-6">
                <label class="mc-label"><i class="bi bi-truck"></i> No. Truck</label>
                <input type="text" class="mc-input" data-field="nopol" placeholder="cth. AD 1111 PE">
            </div>
        </div>
    </div>
</template>

<!--UNTUK PAGE 4 container-->
<template id="containerFotoCardTemplate">
    <div class="container-item container-item-readonly">
        <div class="container-item-header">
            <span class="container-badge">
                <i class="bi bi-box-seam"></i> Container <span class="container-foto-num">1</span>
            </span>
            <span class="container-foto-vessel text-muted small"></span>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="mc-label"><i class="bi bi-upc-scan"></i> No. Seal</label>
                <input type="text" class="mc-input" data-field="noseal" placeholder="cth. EMCXFC5984">
            </div>
        </div>

        {{-- diisi applyFotoLock(): alasan tombol foto disabled (carton belum semua masuk container) --}}
        <div class="container-foto-lock-note small text-danger mt-2 d-none"></div>

        {{-- BARU: 5 slot foto - cuma Foto 1 yang tampil, sisanya d-none sampai tombol + diklik --}}
        <div class="row g-3 mt-1">
            <div class="col-md-6 container-foto-slot" data-photo-slot="foto1">
                <label class="mc-label"><i class="bi bi-camera"></i> Foto 1</label>
                <div class="d-flex justify-content-start align-items-center gap-2 py-2">
                    <button type="button" class="btn btn-primary btn-camera" style="width: 150px;">
                        Upload Foto
                    </button>
                    <input type="file" class="input-camera" data-field="foto1" accept="image/*" capture="environment"
                        hidden>
                    <span class="file-name-camera" style="color:rgb(15, 112, 223); cursor: pointer;"></span>
                    <img src="{{ asset('public/css/images/trash_red.png') }}" class="close-camera d-none" width="15"
                        height="15" style="cursor:pointer;">
                </div>
                <div class="container-foto-preview mt-2"></div>
            </div>

            <div class="col-md-6 container-foto-slot d-none" data-photo-slot="foto2">
                <label class="mc-label"><i class="bi bi-camera"></i> Foto 2</label>
                <div class="d-flex justify-content-start align-items-center gap-2 py-2">
                    <button type="button" class="btn btn-primary btn-camera" style="width: 150px;">
                        Upload Foto
                    </button>
                    <input type="file" class="input-camera" data-field="foto2" accept="image/*" capture="environment"
                        hidden>
                    <span class="file-name-camera" style="color:rgb(15, 112, 223); cursor: pointer;"></span>
                    <img src="{{ asset('public/css/images/trash_red.png') }}" class="close-camera d-none" width="15"
                        height="15" style="cursor:pointer;">
                </div>
                <div class="container-foto-preview mt-2"></div>
            </div>

            <div class="col-md-6 container-foto-slot d-none" data-photo-slot="foto3">
                <label class="mc-label"><i class="bi bi-camera"></i> Foto 3</label>
                <div class="d-flex justify-content-start align-items-center gap-2 py-2">
                    <button type="button" class="btn btn-primary btn-camera" style="width: 150px;">
                        Upload Foto
                    </button>
                    <input type="file" class="input-camera" data-field="foto3" accept="image/*" capture="environment"
                        hidden>
                    <span class="file-name-camera" style="color:rgb(15, 112, 223); cursor: pointer;"></span>
                    <img src="{{ asset('public/css/images/trash_red.png') }}" class="close-camera d-none" width="15"
                        height="15" style="cursor:pointer;">
                </div>
                <div class="container-foto-preview mt-2"></div>
            </div>

            <div class="col-md-6 container-foto-slot d-none" data-photo-slot="foto4">
                <label class="mc-label"><i class="bi bi-camera"></i> Foto 4</label>
                <div class="d-flex justify-content-start align-items-center gap-2 py-2">
                    <button type="button" class="btn btn-primary btn-camera" style="width: 150px;">
                        Upload Foto
                    </button>
                    <input type="file" class="input-camera" data-field="foto4" accept="image/*" capture="environment"
                        hidden>
                    <span class="file-name-camera" style="color:rgb(15, 112, 223); cursor: pointer;"></span>
                    <img src="{{ asset('public/css/images/trash_red.png') }}" class="close-camera d-none" width="15"
                        height="15" style="cursor:pointer;">
                </div>
                <div class="container-foto-preview mt-2"></div>
            </div>

            <div class="col-md-6 container-foto-slot d-none" data-photo-slot="foto5">
                <label class="mc-label"><i class="bi bi-camera"></i> Foto 5</label>
                <div class="d-flex justify-content-start align-items-center gap-2 py-2">
                    <button type="button" class="btn btn-primary btn-camera" style="width: 150px;">
                        Upload Foto
                    </button>
                    <input type="file" class="input-camera" data-field="foto5" accept="image/*" capture="environment"
                        hidden>
                    <span class="file-name-camera" style="color:rgb(15, 112, 223); cursor: pointer;"></span>
                    <img src="{{ asset('public/css/images/trash_red.png') }}" class="close-camera d-none" width="15"
                        height="15" style="cursor:pointer;">
                </div>
                <div class="container-foto-preview mt-2"></div>
            </div>

            {{-- BARU: tombol buat munculkan slot foto berikutnya satu per satu --}}
            <div class="col-md-6 container-foto-add-wrap">
                <label class="mc-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-primary btn-add-foto" style="width:150px;height:40px;">
                    <i class="bi bi-plus-circle"></i> Tambah Foto
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Modal untuk confirm publish  -->
<div class="modal fade" id="modalConfirmPublish" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content cr-modal">
            <div class="cr-body">
                <div class="cr-icon" id="cpIcon"><i class="bi bi-send-fill"></i></div>
                <h5 class="cr-title" id="cpTitle">Publish Shipment Plan?</h5>
                <p class="cr-desc" id="cpDesc">
                    Shipment plan ini akan dikirim ke tim gudang dan mulai bisa diproses.
                </p>
            </div>
            <div class="cr-footer">
                <button type="button" class="cr-btn cr-btn-ghost" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="cr-btn" id="cpConfirmBtn">
                    <i class="bi bi-check-lg"></i> Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>


<!-- MODAL PROGRESS LOADING -->
<div class="modal fade" id="modalContainerProgress" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content cp-modal">

            <div class="cp-header">
                <div class="cp-header-icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
                <div class="flex-fill">
                    <h5 class="cp-title">Progres Muat Container</h5>
                    <p class="cp-subtitle">Container: <span id="cpContno" class="ld-mono">-</span></p>
                </div>
                <button type="button" class="cp-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="cp-legend">
                <span class="cp-legend-item"><span class="cp-dot cp-dot-loading"></span> Sedang Diproses</span>
                <span class="cp-legend-item"><span class="cp-dot cp-dot-loaded"></span> Sudah Masuk Container</span>
                <span class="cp-legend-item"><span class="cp-dot cp-dot-shipped"></span> Sudah Shipped</span>
            </div>

            <div class="modal-body cp-body">
                <div id="cpLoadingState" class="cp-loading-state">
                    <div class="spinner-border" role="status"></div>
                    <p>Memuat data progres...</p>
                </div>

                <div id="cpEmptyState" class="cp-empty-state d-none">
                    <i class="bi bi-inbox"></i>
                    <p>Belum ada carton di container ini.</p>
                </div>

                <div id="cpCartonGrid" class="cp-carton-grid"></div>
            </div>

            <div class="modal-footer cp-footer">
                <div class="cp-summary">
                    <span id="cpSummaryText">0 carton total</span>
                </div>
                <button type="button" class="mc-btn mc-btn-ghost" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<!-- tooltip detail carton, muncul saat hover kotak -->
<div id="cpTooltip" class="cp-tooltip d-none"></div>