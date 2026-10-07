<div class="modal fade" id="notifModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-body" style="padding:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div style="font-size:20px; font-weight:700;">Approved PO From Purchasing</div>
                    <a href="javascript:void(0)" data-bs-dismiss="modal"
                        style="text-decoration:none; color:#000; font-size:20px;">✕</a>
                </div>

                <div class="container-cari-inline" style="margin-bottom:16px;">
                    <div class="frame-cari-inline">
                        <img src="{{ asset('public/css/images/Shape.png') }}" width="18" height="18">

                        <input id="notifModalSearch" name="notifModalSearch" placeholder="Search..." class="input"
                            onkeyup="filterRevisedPoDelay()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" style="font-size:13px; width:100%;">
                        <thead class="ship-thead">
                            <tr>
                                <th style="cursor:pointer;" onclick="sortRevisedPo('pur')">From <span
                                        id="sort-icon-pur"></span></th>
                                <th style="cursor:pointer;" onclick="sortRevisedPo('pono')">PO no <span
                                        id="sort-icon-pono"></span></th>
                                <th style="cursor:pointer;" onclick="sortRevisedPo('byrnm')">Buyer <span
                                        id="sort-icon-byrnm"></span></th>
                                <th style="cursor:pointer;" onclick="sortRevisedPo('brg')">Item <span
                                        id="sort-icon-brg"></span></th>
                                <th style="cursor:pointer;" onclick="sortRevisedPo('dateapv')">Approved date <span
                                        id="sort-icon-dateapv"></span></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="notifModalBody"></tbody>
                    </table>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <select id="notifModalPageSize" class="form-select form-select-sm" style="width:80px;"
                            onchange="onNotifPageSizeChange()">
                            <option value="100" selected>100</option>
                            <option value="200">200</option>
                            <option value="300">300</option>
                            <option value="500">500</option>
                        </select>

                        <button type="button" class="btn btn-sm btn-light" onclick="goToNotifPage(1)">«</button>
                        <button type="button" class="btn btn-sm btn-light"
                            onclick="goToNotifPage(notifCurrentPage - 1)">‹</button>

                        <div id="notifPageNumbers" style="display:flex; gap:4px;"></div>

                        <button type="button" class="btn btn-sm btn-light"
                            onclick="goToNotifPage(notifCurrentPage + 1)">›</button>
                        <button type="button" class="btn btn-sm btn-light"
                            onclick="goToNotifPage(notifLastPage)">»</button>
                        <button type="button" class="btn btn-sm btn-light" onclick="loadRevisedPoList()">⟳</button>
                    </div>

                    <div id="notifModalInfo" style="color:#666; font-size:13px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
