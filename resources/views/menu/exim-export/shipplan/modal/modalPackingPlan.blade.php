<!-- Modal -->
<div class="modal fade" id="PackingPlanModal" data-bs-backdrop="static" aria-labelledby="PackingPlanModalLabel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="PackingPlanModalLabel">Packing Plan</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="page-wrap ps-3 pt-0">
                    <x-table-default id="dgOrder" title="Daftar Data OP" search search-name="search"
                        search-placeholder="Search..." buyer buyer-name="buyer" buyer-url="{{ route('buyer-list') }}"
                        buyer-value-field="buyer" buyer-text-field="buyer_name" buyer-mode="remote" year
                        year-name="year" exfactory exfactory-name="ex_factory" sort-dropdown
                        sort-asc-label="Earliest Ex Factory" sort-desc-label="Latest Ex Factory">

                        <table id="dgOrder" class="easyui-datagrid" style="width:100%;height:450px"
                            url="{{ route('packing.list') }}" method="get" pagination="true" pageSize="50" idField="popk"
                            pageList="[25,50,100,200,500]" rownumbers="false" singleSelect="false" fitColumns="false"
                            border="false"
                            data-options="onCheck:function(){menu();},onCheckAll:function(){menu();},onUncheck:function(){menu();},onUncheckAll:function(){menu();}">
                            <thead frozen="true">
                                <tr>
                                    <th field="ck" checkbox="true" align="center" rowspan="2">Aksi</th>
                                    <th field="popks2" rowspan="2" hidden="true">popk</th>
                                    <th field="OP" width="230" formatter="formatOrderInfo" rowspan="2">Order
                                        Information</th>
                                    <th field="POno" width="150" formatter="formatPOno" rowspan="2">PO No</th>
                                    <th field="GAC" width="100" align="center" formatter="formatExFactory"
                                        rowspan="2">Ex
                                        Factory</th>
                                    <th field="exportpks" width="110" rowspan="2" formatter="formatShipID">Ship ID</th>
                                </tr>
                                <tr></tr>
                            </thead>
                            <thead>
                                <tr>
                                    <th field="poref" width="150" rowspan="2">License<br>PO Ref</th>
                                    <th field="packing_plan_status" width="120" align="center" rowspan="2"
                                        formatter="formatPackingPlanStatus">Packing Plan</th>
                                    <th width="90" colspan="3" align="right">Packing /Pcs</th>
                                    <th field="ctn_summary" width="250" align="left" rowspan="2"
                                        formatter="formatCtnSummary">
                                        CTN</th>
                                </tr>
                                <tr>
                                    <th field="packing_qty_plan" width="80" align="right" formatter="formatNumber">
                                        Plan</th>
                                    <th field="packing_qty" width="80" align="right" formatter="formatNumber">
                                        Actual</th>
                                    <th field="packing_qty_balance" width="80" align="right"
                                        formatter="formatBalanceCell">Balance
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </x-table-default>
                </div>
            </div>
            <!-- menu header -->
            <div class="header d-flex p-4 justify-content-between align-items-center w-100 d-none" id="myHeader"
                style="">
                <div id="count" class="d-none"></div>
                <div class="d-flex gap-4">
                    <div class="delete" id="setship" style="width:auto;display:none;">
                        <a href="javascript:void(0)" plain="true" onclick="set_shipplan();"
                            style="color: #FFFFFF; text-decoration: none;">
                            Set Shipment Plan
                        </a>
                    </div>
                    <div class="delete" id="mclose" style="width:auto;">
                        <a href="javascript:void(0)" plain="true" onclick="close_all();"
                            style="color: #FFFFFF; text-decoration: none;"> Close menu
                        </a>
                    </div>
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
        <h5 class="cr-title">Apakah anda yakin akan membatalkan shipment ini?</h5>
        <p class="cr-desc">
            Pastikan terlebih dulu data yang akan di cancel.
        </p>
      </div>
      <div class="cr-footer">
        <button type="button" class="cr-btn cr-btn-ghost" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="cr-btn cr-btn-danger" id="crConfirmBtn">
            <i class="bi bi-x-octagon-fill"></i> Ya, Cancel!
        </button>
      </div>
    </div>
  </div>
</div>

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

<!--Modal ini untuk munculkan set_shipment Ex date, etd, dll-->
<div class="modal fade" id="modalShipmentDetail" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content mc-content">

      <div class="mc-header">
        <div class="mc-header-icon"><i class="bi bi-calendar-week"></i></div>
        <div class="flex-fill">
          <h5 class="mc-title">First Planning</h5>
          <p class="mc-subtitle">
            <span id="ssOrderCount">0</span> order dipilih ·
            Total <span id="ssTotalCtn">0</span> CTN
          </p>
        </div>
        <button type="button" class="mc-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>

      <div class="modal-body" style="padding: 24px 28px;">
        <div class="row g-3">
          <div class="col-12">
            <label class="mc-label"><i class="bi bi-calendar-event"></i> Estimasi Container Arrival</label>
            <input type="date" class="mc-input" id="ssExFactoryDate" required>
          </div>

          <div class="col-12">
            <label class="mc-label"><i class="bi bi-calendar-check"></i> Vassal ETD</label>
            <input type="date" class="mc-input" id="ssEtd" required>
          </div>

          <div class="col-12">
            <label class="mc-label"><i class="bi bi-ship"></i> Shipment Method</label>
            <select class="mc-input" id="ssVassal" required>
              <option value="" selected disabled>Pilih Shipment Method</option>
              <option value="1">By Sea</option>
              <option value="2">By Air</option>
              <option value="3">By Courier</option>
              <option value="4">LCL</option>
            </select>
          </div>
        </div>
      </div>

      <div class="modal-footer" style="padding: 16px 28px; border-top: 1px solid #EAECF0;">
        <button type="button" class="mc-btn mc-btn-ghost" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="mc-btn mc-btn-primary" id="btnSubmitShipmentDetail">
          <i class="bi bi-check2-circle"></i> Simpan &amp; Lanjutkan
        </button>
      </div>

    </div>
  </div>
</div>

<!--Modal ini untuk munculkan set_shipment Ex date, etd, dll-->
<div class="modal fade" id="modalInvoice" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content mc-content">

      <div class="mc-header">
        <div class="mc-header-icon"><i class="bi bi-calendar-week"></i></div>
        <div class="flex-fill">
          <h5 class="mc-title">Input Nomor Invoice Shipment</h5>
          <p class="mc-subtitle">
            <span id="ssListCount">0</span> list dipilih
          </p>
        </div>
        <button type="button" class="mc-close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>

      <div class="modal-body" style="padding: 24px 28px;">
        <div class="row g-3">
          <div class="col-12">
            <label class="mc-label"><i class="bi bi-calendar-check"></i> Invoice Shipment</label>
            <input type="text" class="mc-input" id="ssInvs" placeholder="cth. 001/INV/2026" required>
          </div>

        </div>
      </div>

      <div class="modal-footer" style="padding: 16px 28px; border-top: 1px solid #EAECF0;">
        <button type="button" class="mc-btn mc-btn-ghost" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="mc-btn mc-btn-primary" id="btnSubmitInvoice">
          <i class="bi bi-check2-circle"></i> Simpan &amp; Lanjutkan
        </button>
      </div>

    </div>
  </div>
</div>

