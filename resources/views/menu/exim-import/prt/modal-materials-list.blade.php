<div class="modal fade" id="materialsListModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-body" style="padding:18px 20px 20px 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                    <div style="font-size:18px; font-weight:700;">
                        List of material for shipment planning PO <span id="materialsListPono"></span>
                    </div>
                    <div style="display:flex; gap:16px; align-items:center;">
                        <a href="javascript:void(0)" data-bs-dismiss="modal"
                            style="text-decoration:none; color:#000; font-weight:600;">Cancel</a>
                        <a href="javascript:void(0)" id="materialsListAddBtn"
                            style="text-decoration:none; color:#359DD9; font-weight:600;">Add</a>
                    </div>
                </div>

                <div class="container-cari-inline" style="margin-bottom:16px;">
                    <div class="frame-cari-inline">
                        <img src="{{ asset('public/css/images/Shape.png') }}" width="18" height="18">

                        <input id="materialsListSearch" name="materialsListSearch" placeholder="Search..."
                            class="input" onkeyup="filterMaterialsListDelay()">
                    </div>
                </div>

                <div class="table-responsive" id="materialsListTableWrapper">
                    <table class="table table-bordered" style="width:100%; font-size:13px;">
                        <thead class="ship-thead">
                            <tr>
                                <th style="text-align:center; width:30px;"><input type="checkbox"
                                        id="materialsListCheckAll"></th>
                                <th style="width:8%;">Item</th>
                                <th style="width:28%;">Description</th>
                                <th style="width:12%;">Supplier</th>
                                <th style="width:8%;">Color</th>
                                <th style="width:10%;">Price</th>
                                <th style="width:10%;">Qty PO</th>
                                <th style="width:10%;">Qty Shipment</th>
                                <th style="width:10%;">Balance</th>
                            </tr>
                        </thead>
                        <tbody id="materialsListBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
