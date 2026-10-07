<div class="modal fade" id="modalLoadCarton" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="width: 700px; max-width: 700px;">
        <div class="modal-content ld-modal">

            <div class="ld-modal-header">
                <div class="ld-modal-header-icon"><i class="bi bi-boxes"></i></div>
                <div class="flex-fill">
                    <h5 class="ld-modal-title">Pilih Carton untuk Dimuat</h5>
                    <p class="ld-modal-subtitle">
                        Container: <span id="modalContainerNo" class="ld-mono">-</span>
                        &nbsp;·&nbsp;
                        PO: <span id="modalPoNo" class="ld-mono">-</span>
                        &nbsp;·&nbsp;
                        Order No: <span id="modalNoOp" class="ld-mono">-</span>
                    </p>
                </div>
                <button type="button" class="ld-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>

            <div class="ld-modal-toolbar">
                <div class="ld-search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="cartonSearch" placeholder="Cari No. Carton / Style..."
                        onkeyup="onCartonSearch(event)">
                </div>
                
                <select id="cartonFilterOrdno" class="sp-filter-select" onchange="reloadCartonGrid()">
                    <option value="">Semua Order No</option>
                </select>
                <select id="cartonFilterPo" class="sp-filter-select" onchange="reloadCartonGrid()">
                    <option value="">Semua PO</option>
                </select>

            </div>
            <div class="ld-modal-toolbar">
                <div class="ld-range-box">
                    <i class="bi bi-hash"></i>
                    <input type="text" id="cartonRangeInput" placeholder="cth. 1-50 atau 1,5,10-20"
                        onkeydown="if(event.key==='Enter'){ event.preventDefault(); selectCartonByRange(); }">
                    <button type="button" class="ld-range-apply" onclick="selectCartonByRange()"
                        title="Pilih carton sesuai range">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>
                <button type="button" class="ld-select-all" id="btnSelectAll" onclick="toggleSelectAllTest()">
                    <i class="bi bi-check2-square"></i> Pilih Semua
                </button>
                <button type="button" class="ld-refresh-btn" onclick="reloadCartonGrid()" title="Muat ulang">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>

            <div class="modal-body ld-modal-body">
                <table id="dgDetailCarton"></table>
            </div>

            <div class="modal-footer ld-modal-footer">
                <div class="ld-selected-summary">
                    <span class="ld-selected-badge" id="selectedCount">0 carton dipilih</span>
                    <span class="ld-selected-sub" id="selectedTotals">Total 0 pcs</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="ld-btn ld-btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="ld-btn ld-btn-primary" id="btnLoadSubmit" onclick="submitLoadCarton()"
                        disabled>
                        <i class="bi bi-box-arrow-in-down"></i> Muat ke Container
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal confirm delete -->

<div class="modal fade" id="modalConfirmRemove" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content cr-modal">
            <div class="cr-body">
                <div class="cr-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h5 class="cr-title" id="crModalTitle">Keluarkan Carton?</h5>
                <p class="cr-desc" id="crModalDesc">
                    Carton <span id="crCartonNo" class="ld-mono fw-bold">-</span> akan dikeluarkan dari container.
                </p>
            </div>
            <div class="cr-footer">
                <button type="button" class="cr-btn cr-btn-ghost" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="cr-btn cr-btn-danger" id="crConfirmBtn">
                    <i class="bi bi-trash3"></i> Ya, Keluarkan
                </button>
            </div>
        </div>
    </div>
</div>
