{{-- Packing list PDF per PO (Ship Plan) — 1 ikon PDF per PO membuka modal ini. Tampilan sama dengan
     modal "Pilih Warna untuk Print" finishing-packing dan SPS (dialog Packing & stuffing by PO).
     Data & link: ShipplanController::packingListOptions / packingListLink → API finishing-packing
     /api/sps/packing-list*. Script: modalPackingListPdfJs (di section js_custom). --}}
<div class="modal fade" id="modalPackingListPdf" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:480px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:12px;">
            <div class="modal-header py-3 border-0">
                <div>
                    <h5 class="modal-title fw-bold" style="font-size:15px;">
                        <i class="bi bi-file-earmark-pdf me-2 text-primary"></i>Pilih Warna untuk Print
                    </h5>
                    <div class="pl-sub" id="plSub"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div id="plLoading" class="text-center text-muted py-4">
                    <span class="spinner-border spinner-border-sm me-2"></span>Memuat daftar warna...
                </div>
                <div id="plEmpty" class="d-none text-center text-muted py-4"></div>

                <div id="plContent" class="d-none">
                    {{-- 1 PO bisa punya beberapa packing list (OP / POref berbeda, atau di 2 unit) --}}
                    <div id="plDocWrap" class="d-none mb-3">
                        <div class="fw-semibold mb-1" style="font-size:12.5px;">Dokumen</div>
                        <div class="text-muted mb-1" style="font-size:11.5px;">
                            PO ini punya lebih dari satu packing list di finishing-packing (dipisah per OP / POref).
                            Pilih packing list yang mau dicetak &mdash; warna, session, dan versi di bawah mengikuti pilihan ini.
                        </div>
                        <select class="form-select form-select-sm" id="plDoc" onchange="plRender()"></select>
                    </div>

                    <div class="pl-mode mb-2">
                        <button type="button" data-mode="actual" onclick="plMode('actual')">PDF Actual</button>
                        <button type="button" data-mode="custom" onclick="plMode('custom')">PDF Custom QTY</button>
                    </div>

                    {{-- Penjelasan isi tiap jenis PDF (teks diganti sesuai pilihan) --}}
                    <div class="pl-note pl-note-actual mb-3" data-note="actual">
                        <div class="pl-note-h">PDF Actual &mdash; hasil packing sebenarnya</div>
                        Berisi carton yang akan dikirim beserta <b>isi sebenarnya</b> di dalam carton, yaitu qty yang
                        sudah diinput tim finishing saat packing (data terkini). Selama packing masih berjalan, isi
                        dokumen ini masih bisa bertambah atau berubah.
                    </div>
                    <div class="pl-note pl-note-custom mb-3 d-none" data-note="custom">
                        <div class="pl-note-h">PDF Custom QTY &mdash; rencana packing</div>
                        Berisi <b>rencana carton</b> yang akan dikirim beserta <b>qty yang direncanakan</b> di setiap
                        carton, bukan hasil packing. Hasil sebenarnya nanti bisa dilihat di <b>PDF Actual</b> setelah
                        tim finishing selesai mengisi carton.
                    </div>

                    {{-- ===== PDF Actual: session & warna ===== --}}
                    <div id="plActual">
                        <div id="plExpanded" class="d-none alert alert-warning py-2 px-3 mb-3" style="font-size:11.5px;">
                            <i class="bi bi-info-circle me-1"></i>Dokumen ini mencakup carton Mix/Bundle lintas PO/OP/Poref.
                        </div>
                        <div id="plSessWrap" class="d-none mb-3">
                            <div class="fw-semibold mb-1" style="font-size:12.5px;"><i class="bi bi-truck me-1 text-primary"></i>Session / Shipment ID</div>
                            <div class="text-muted mb-1" style="font-size:11.5px;" id="plSessHint">Pilih pengiriman yang mau dicetak packing list-nya.</div>
                            <div class="list-group" id="plSessList"></div>
                        </div>
                        <div class="fw-semibold mb-1" style="font-size:12.5px;"><i class="bi bi-palette me-1 text-primary"></i>Warna</div>
                        <div class="text-muted mb-2" style="font-size:12px;">
                            Centang warna yang mau di-print. Carton Mix/Bundle tetap muncul selama salah satu warnanya
                            dipilih -- warna lain di carton yang sama tetap ditampilkan sebagai info, bukan dihilangkan.
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="plAllColors(true)">
                                <i class="bi bi-check2-all me-1"></i>Pilih Semua</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="plAllColors(false)">
                                <i class="bi bi-x-lg me-1"></i>Batal Semua</button>
                        </div>
                        <div class="list-group mb-2" id="plColorList"></div>
                    </div>

                    {{-- ===== PDF Custom QTY: versi yang pernah disimpan (semua warna) ===== --}}
                    <div id="plCustom" class="d-none">
                        <div class="fw-semibold mb-1" style="font-size:12.5px;">Versi</div>
                        <div class="text-muted mb-2" style="font-size:12px;">
                            Semua versi Custom QTY yang sudah dibuat untuk PO ini, dari semua warna. Versi dibuka dengan
                            warna &amp; session yang dipilih saat versi disimpan; carton baru sejak versi itu ditandai kuning.
                        </div>
                        <div class="list-group mb-2" id="plVerList"></div>
                    </div>
                    <div class="pl-err d-none" id="plErr"></div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-dark" id="plSubmit" onclick="plSubmit()" disabled>
                    <i class="bi bi-printer me-1"></i><span id="plSubmitText">Buka PDF</span></button>
            </div>
        </div>
    </div>
</div>

<style>
    #modalPackingListPdf .pl-sub { color: #6b7280; font-size: 12.5px; margin-top: 2px; }
    #modalPackingListPdf .pl-mode { display: flex; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
    #modalPackingListPdf .pl-mode button { flex: 1; border: 0; background: #fff; padding: 7px 10px; font-size: 13px; font-weight: 600; color: #374151; }
    #modalPackingListPdf .pl-mode button + button { border-left: 1px solid #d1d5db; }
    #modalPackingListPdf .pl-mode button.on { background: #212529; color: #fff; }
    #modalPackingListPdf .pl-note { border-radius: 8px; padding: 9px 12px; font-size: 12.5px; line-height: 1.5; color: #374151; }
    #modalPackingListPdf .pl-note-h { font-weight: 700; margin-bottom: 2px; }
    #modalPackingListPdf .pl-note-actual { background: #eff6ff; border: 1px solid #bfdbfe; }
    #modalPackingListPdf .pl-note-actual .pl-note-h { color: #1d4ed8; }
    #modalPackingListPdf .pl-note-custom { background: #fff7ed; border: 1px solid #fed7aa; }
    #modalPackingListPdf .pl-note-custom .pl-note-h { color: #c2410c; }
    #modalPackingListPdf .pl-ver-meta { color: #6b7280; font-size: 11.5px; }
    #modalPackingListPdf .pl-err { color: #b91c1c; font-size: 12.5px; margin-top: 6px; }
</style>
