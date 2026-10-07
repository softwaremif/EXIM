<div class="modal fade" id="shipmentPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px; border:none;">

            <div class="modal-body" style="padding:18px 20px 20px 20px;">
                <input type="hidden" id="modal_importpk">

                {{-- HEADER --}}
                <div id="shipmentPlanHeader"
                    style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <div>
                        <div style="font-size:20px; font-weight:700; color:#000;">Shipment plan</div>
                    </div>

                    <div style="display:flex; align-items:center; gap:16px;">
                        <a href="javascript:void(0)" id="shipmentPlanCloseBtn" data-bs-dismiss="modal"
                            style="text-decoration:none; color:#000; font-weight:600;">
                            Close
                        </a>
                        <a href="javascript:void(0)" id="shipmentPlanCancelBtn"
                            style="display:none; text-decoration:none; color:#000; font-weight:600;">
                            Cancel
                        </a>
                        @if (Session::get('login') == 'exim' || Session::get('login') == 'import')
                            <button type="button" id="shipmentPlanEditBtn"
                                style="min-width:170px; height:38px; border:none; border-radius:10px; background:#000; color:#fff; font-weight:600; padding:0 20px;">
                                Edit
                            </button>
                        @endif
                        <button type="button" id="shipmentPlanSaveBtn"
                            style="display:none; min-width:170px; height:38px; border:none; border-radius:10px; background:#000; color:#fff; font-weight:600; padding:0 20px;">
                            Save
                        </button>
                    </div>
                </div>

                {{-- ORDER DETAILS --}}
                <div style="margin-bottom:18px;">
                    <div style="font-size:16px; font-weight:700; color:#666; margin-bottom:6px;">
                        Order details
                    </div>

                    <div style="display:flex; gap:8px; line-height:1.5;">
                        <div style="width:70px; color:#9A9A9A;">Supplier</div>
                        <div id="modal_supplier">-</div>
                    </div>

                    <div style="display:flex; gap:8px; line-height:1.5; align-items:center;">
                        <div style="width:70px; color:#9A9A9A;">
                            POs <span id="countpono" style="color:#000000;">0</span>
                        </div>

                        <div>
                            <a href="javascript:void(0)" id="shw" onclick="viewdtl()"
                                style="text-decoration:none; color:#359DD9;">
                                View detail
                            </a>

                            <a href="javascript:void(0)" id="hdn" onclick="viewdtl()"
                                style="display:none; text-decoration:none; color:#359DD9;">
                                Hide detail
                            </a>
                        </div>
                    </div>
                </div>

                {{-- INNER CARD --}}
                <div
                    style="
                            background:#fff;
                            border-radius:14px;
                            box-shadow:0 2px 14px rgba(0,0,0,.06);
                            padding:18px 18px 6px 18px;
                        ">
                    {{-- DETAIL PO TABLE --}}
                    {{-- <div id="tabel2b" style="display:none; margin-bottom:18px;">
                        <div class="table-responsive">
                            <table id="DgImport" class="table table-bordered align-middle mb-0"
                                style="width:100%; table-layout:fixed; font-size:13px;">
                                <thead class="ship-thead">
                                    <tr>
                                        <th style="width:12%;">Item</th>
                                        <th style="width:26%;">Description</th>
                                        <th style="width:12%;">Supplier</th>
                                        <th style="width:8%;">Color</th>
                                        <th style="width:10%;">Price</th>
                                        <th style="width:10%;">Qty PO</th>
                                        <th style="width:12%;">Shipment Qty</th>
                                        <th id="dgImportActHead" style="width:6%; display:none;">Act</th>
                                    </tr>
                                </thead>
                                <tbody id="dgImportBody">
                                    <tr>
                                        <td colspan="8" style="color:#999;">Belum ada detail PO.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div> --}}
                    <div id="tabel2b" style="display:none; margin-bottom:18px;">
                        <div class="table-responsive material-table-scroll">
                            <table id="DgImport" class="table table-bordered align-middle mb-0"
                                style="width:100%; table-layout:fixed; font-size:13px;">
                                <thead class="ship-thead">
                                    <tr>
                                        <th style="width:12%;">Item</th>
                                        <th style="width:26%;">Description</th>
                                        <th style="width:12%;">Supplier</th>
                                        <th style="width:8%;">Color</th>
                                        <th style="width:10%;">Price</th>
                                        <th style="width:10%;">Qty PO</th>
                                        <th style="width:12%;">Shipment Qty</th>
                                        <th id="dgImportActHead" style="width:6%; display:none;">Act</th>
                                    </tr>
                                </thead>
                                <tbody id="dgImportBody">
                                    <tr>
                                        <td colspan="8" style="color:#999;">Belum ada detail PO.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>


                    {{-- SHIPMENT DETAILS --}}
                    <div style="font-size:16px; font-weight:700; color:rgba(0,0,0,.6); margin-bottom:1px;">
                        Shipment details
                    </div>
                    <div class="row mb-1">
                        <div class="col" style="color:#00000061;">Vassal type</div>
                        <div class="col" style="color:#00000061;">Vassal name</div>
                        <div class="col" style="color:#00000061;">AirwayBill Number</div>
                        <div class="col" style="color:#00000061;">Arrival city</div>
                        <div class="col" style="color:#00000061;">Port name</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col">
                            <span id="modal_vassal_type" class="view-mode">-</span>
                            <div class="custom-dropdown edit-mode" id="modal_vassal_type_wrap"
                                style="display:none; width:100%;">
                                <input type="hidden" id="modal_vassal_type_input" value="">

                                <button type="button" class="custom-dropdown-trigger"
                                    onclick="toggleModalDropdown(event, 'modal_vassal_type_menu')">
                                    <span id="modal_vassal_type_label">Choose vassal</span>
                                    <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                                </button>

                                <div id="modal_vassal_type_menu" class="custom-dropdown-menu" style="width:100%;"></div>
                            </div>
                        </div>

                        <div class="col">
                            <span id="modal_vassal_name" class="view-mode">-</span>
                            <input id="modal_vassal_name_input" class="edit-mode form-control form-control-sm"
                                style="display:none; border-radius:8px; border: 1px solid; height: 34px;">
                        </div>
                        <div class="col">
                            <span id="modal_awb" class="view-mode">-</span>
                            <input id="modal_awb_input" class="edit-mode form-control form-control-sm"
                                style="display:none; border-radius:8px; border: 1px solid; height: 34px;">
                        </div>

                        <div class="col">
                            <span id="modal_arrival_city" class="view-mode">-</span>
                            <div class="custom-dropdown edit-mode" id="modal_arrival_city_wrap"
                                style="display:none; width:100%;">
                                <input type="hidden" id="modal_arrival_city_input" value="">

                                <button type="button" class="custom-dropdown-trigger"
                                    onclick="toggleModalDropdown(event, 'modal_arrival_city_menu')">
                                    <span id="modal_arrival_city_label">Choose arrival</span>
                                    <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                                </button>

                                <div id="modal_arrival_city_menu" class="custom-dropdown-menu" style="width:100%;">
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <span id="modal_port_name" class="view-mode">-</span>
                            <div class="custom-dropdown edit-mode" id="modal_port_name_wrap"
                                style="display:none; width:100%;">
                                <input type="hidden" id="modal_port_name_input" value="">

                                <button type="button" class="custom-dropdown-trigger"
                                    onclick="toggleModalDropdown(event, 'modal_port_name_menu')">
                                    <span id="modal_port_name_label">Choose port</span>
                                    <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                                </button>

                                <div id="modal_port_name_menu" class="custom-dropdown-menu" style="width:100%;">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITEM DETAILS --}}
                    <div style="font-size:16px; font-weight:700; color:rgba(0,0,0,.6); margin-bottom:1px;">
                        Item details
                    </div>
                    <div class="row mb-1">
                        <div class="col" style="color:#00000061;">Gross weight</div>
                        <div class="col" style="color:#00000061;">Dimension of boxes</div>
                        <div class="col" style="color:#00000061;">Total Package</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col">
                            <span id="modal_gw" class="view-mode">-</span>
                            <input id="modal_gw_input" class="edit-mode form-control form-control-sm"
                                style="display:none; border-radius:8px; border: 1px solid; height: 34px;">
                        </div>
                            {{-- <div class="col">
                                <span id="modal_dimension" class="view-mode">-</span>
                                <div class="edit-mode" data-display="flex"
                                    style="display:none; gap:4px; align-items:center;">
                                    P <input id="modal_dp_input" class="form-control form-control-sm"
                                        style="width:60px;border-radius:8px; border: 1px solid; height: 34px;">
                                    L <input id="modal_dl_input" class="form-control form-control-sm"
                                        style="width:60px;border-radius:8px; border: 1px solid; height: 34px;">
                                    T <input id="modal_dt_input" class="form-control form-control-sm"
                                        style="width:60px;border-radius:8px; border: 1px solid; height: 34px;"> cm
                                </div>
                            </div> --}}
                            <div class="col">
                                <span id="modal_dimension" class="view-mode">-</span>
                                <div class="edit-mode" data-display="flex"
                                    style="display:none; gap:4px; align-items:center;">
                                    P <input id="modal_dp_input" class="form-control form-control-sm"
                                        inputmode="decimal"
                                        oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/([.,].*)[.,]/g, '$1')"
                                        style="width:60px;border-radius:8px; border: 1px solid; height: 34px;">
                                    L <input id="modal_dl_input" class="form-control form-control-sm"
                                        inputmode="decimal"
                                        oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/([.,].*)[.,]/g, '$1')"
                                        style="width:60px;border-radius:8px; border: 1px solid; height: 34px;">
                                    T <input id="modal_dt_input" class="form-control form-control-sm"
                                        inputmode="decimal"
                                        oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/([.,].*)[.,]/g, '$1')"
                                        style="width:60px;border-radius:8px; border: 1px solid; height: 34px;"> cm
                                </div>
                            </div>
                        <div class="col">
                            <span id="modal_tpackage" class="view-mode">-</span>
                            <input id="modal_tpackage_input" class="edit-mode form-control form-control-sm"
                                style="display:none; border-radius:8px; border: 1px solid; height: 34px;">
                        </div>
                    </div>

                    {{-- SHIPMENT DATES --}}
                    {{-- <div style="font-size:16px; font-weight:700; color:rgba(0,0,0,.6); margin-bottom:1px;">
                        Shipment dates
                    </div>
                    <div class="row mb-1">
                        <div class="col" style="color:#00000061;">ETD</div>
                        <div class="col" style="color:#00000061;">ETA</div>
                        <div class="col" style="color:#00000061;">EIH (Estimate in house)</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col">
                            <span id="modal_etd" class="view-mode">-</span>
                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_etd_input" class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                                <span class="date-icon-trigger" data-target="modal_etd_input"
                                    style="position:absolute; right:8px; cursor:pointer; color:#888;">
                                    📅
                                </span>
                            </div>
                        </div>
                        <div class="col">
                            <span id="modal_eta" class="view-mode">-</span>
                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_eta_input" class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                                <span class="date-icon-trigger" data-target="modal_eta_input"
                                    style="position:absolute; right:8px; cursor:pointer; color:#888;">
                                    📅
                                </span>
                            </div>
                        </div>
                        <div class="col">
                            <span id="modal_eih" class="view-mode">-</span>
                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_eih_input" class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                                <span class="date-icon-trigger" data-target="modal_eih_input"
                                    style="position:absolute; right:8px; cursor:pointer; color:#888;">
                                    📅
                                </span>
                            </div>
                        </div>
                    </div> --}}

                     {{-- SHIPMENT DATES --}}
                    <div style="font-size:16px; font-weight:700; color:rgba(0,0,0,.6); margin-bottom:1px;">
                        Shipment dates
                    </div>

                    {{-- ETD - ETA - EIH --}}
                    <div class="row mb-3">
                        {{-- ETD --}}
                        <div class="col-md-4">
                            <label class="mb-1" style="color:#00000061;">ETD</label>

                            <span id="modal_etd" class="view-mode">-</span>

                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_etd_input"
                                    class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; height:34px;">
                                <span class="date-icon-trigger" data-target="modal_etd_input"
                                    style="position:absolute; right:8px; cursor:pointer;">
                                    📅
                                </span>
                            </div>

                            <span id="modal_etd-error" class="field-error-text" style="display:none;"></span>
                        </div>

                        {{-- ETA --}}
                        <div class="col-md-4">
                            <label class="mb-1" style="color:#00000061;">ETA</label>

                            <span id="modal_eta" class="view-mode">-</span>

                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_eta_input"
                                    class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; height:34px;">
                                <span class="date-icon-trigger" data-target="modal_eta_input"
                                    style="position:absolute; right:8px; cursor:pointer;">
                                    📅
                                </span>
                            </div>

                            <span id="modal_eta-error" class="field-error-text" style="display:none;"></span>
                        </div>

                        {{-- EIH --}}
                        <div class="col-md-4">
                            <label class="mb-1" style="color:#00000061;">EIH (Estimate in house)</label>

                            <span id="modal_eih" class="view-mode">-</span>

                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_eih_input"
                                    class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; height:34px;">
                                <span class="date-icon-trigger" data-target="modal_eih_input"
                                    style="position:absolute; right:8px; cursor:pointer;">
                                    📅
                                </span>
                            </div>

                            <span id="modal_eih-error" class="field-error-text" style="display:none;"></span>
                        </div>
                    </div>

                    {{-- ATD - ATA --}}
                    <div class="row mb-3">
                        {{-- ATD --}}
                        <div class="col-md-4">
                            <label class="mb-1" style="color:#00000061;">
                                ATD (Actual Time of Delivery)
                            </label>

                            <span id="modal_atd" class="view-mode">-</span>

                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_atd_input"
                                    class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; height:34px;">
                                <span class="date-icon-trigger" data-target="modal_atd_input"
                                    style="position:absolute; right:8px; cursor:pointer;">
                                    📅
                                </span>
                            </div>

                            <span id="modal_atd-error" class="field-error-text" style="display:none;"></span>
                        </div>

                        {{-- ATA --}}
                        <div class="col-md-4">
                            <label class="mb-1" style="color:#00000061;">
                                ATA (Actual Time of Arrival)
                            </label>

                            <span id="modal_ata" class="view-mode">-</span>

                            <div class="edit-mode" data-display="flex"
                                style="display:none; position:relative; align-items:center;">
                                <input type="text" id="modal_ata_input"
                                    class="form-control form-control-sm"
                                    style="padding-right:32px; border-radius:8px; height:34px;">
                                <span class="date-icon-trigger" data-target="modal_ata_input"
                                    style="position:absolute; right:8px; cursor:pointer;">
                                    📅
                                </span>
                            </div>

                            <span id="modal_ata-error" class="field-error-text" style="display:none;"></span>
                        </div>

                      
                        {{-- AIH --}}
                        <div class="col-md-4">
                            <label class="mb-1" style="color:#00000061;">
                                AIH (Actual In House)
                            </label>

                            <div>
                                <span id="modal_aih">-</span>
                            </div>
                        </div>
                    </div>

                    {{-- SHIPMENT LOG --}}
                    <div style="font-size:16px; font-weight:700; color:rgba(0,0,0,.6); margin-bottom:1px;">
                        Shipment Log
                    </div>

                    <div class="table-responsive">
                        <table class="table mb-0" style="font-size:14px;">
                            <thead>
                                <tr>
                                    <th style="width:220px;">Date</th>
                                    <th>Changes</th>
                                </tr>
                            </thead>
                            <tbody id="shipment-log-body">
                                <tr>
                                    <td colspan="2" style="color:#999;">Belum ada log.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> {{-- end inner card --}}
            </div>
        </div>
    </div>
</div>

{{-- Modal Shipment Dates --}}
<div class="modal fade" id="shipmentDatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-body" style="padding:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div style="font-size:20px; font-weight:700; color:#000;">Shipment dates</div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <input type="hidden" id="sd_importpk">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="mb-1" style="color:#00000061;">ETD</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="sd_etd_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="sd_etd_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="sd_etd-error" class="field-error-text" style="display:none;"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="mb-1" style="color:#00000061;">ETA</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="sd_eta_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="sd_eta_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="sd_eta-error" class="field-error-text" style="display:none;"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="mb-1" style="color:#00000061;">EIH (Estimate in house)</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="sd_eih_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="sd_eih_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="sd_eih-error" class="field-error-text" style="display:none;"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="mb-1" style="color:#00000061;">ATD (Actual Time of Delivery)</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="sd_atd_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="sd_atd_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="sd_atd-error" class="field-error-text" style="display:none;"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="mb-1" style="color:#00000061;">ATA (Actual Time of Arrival)</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="sd_ata_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="sd_ata_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="sd_ata-error" class="field-error-text" style="display:none;"></span>
                    </div>

                    <div class="col-md-4">
                        <label class="mb-1" style="color:#00000061;">AIH (Actual In House)</label>
                        <div><span id="sd_aih">-</span></div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:16px; margin-top:8px;">
                    <a href="javascript:void(0)" data-bs-dismiss="modal"
                        style="text-decoration:none; color:#000; font-weight:600; align-self:center;">
                        Cancel
                    </a>
                    <button type="button" id="shipmentDatesSaveBtn"
                        style="min-width:170px; height:38px; border:none; border-radius:10px; background:#000; color:#fff; font-weight:600; padding:0 20px;">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Shipment Dates By Vname --}}
<div class="modal fade" id="bulkEditDatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-body" style="padding:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <div style="font-size:20px; font-weight:700; color:#000;">Edit shipment dates</div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div id="bulkEditSelectedInfo" style="color:#666; font-size:13px; margin-bottom:16px;"></div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="mb-1" style="color:#00000061;">ETA</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="bed_eta_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="bed_eta_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="bed_eta-error" class="field-error-text" style="display:none;"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="mb-1" style="color:#00000061;">EIH (Estimate in house)</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="bed_eih_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="bed_eih_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="bed_eih-error" class="field-error-text" style="display:none;"></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="mb-1" style="color:#00000061;">ATA (Actual Time of Arrival)</label>
                        <div style="position:relative; display:flex; align-items:center;">
                            <input type="text" id="bed_ata_input" class="form-control form-control-sm"
                                style="padding-right:32px; border-radius:8px; height:34px;">
                            <span class="date-icon-trigger" data-target="bed_ata_input"
                                style="position:absolute; right:8px; cursor:pointer;">📅</span>
                        </div>
                        <span id="bed_ata-error" class="field-error-text" style="display:none;"></span>
                    </div>

                    <div class="col-md-6">
                        <label class="mb-1" style="color:#00000061;">AIH (Actual In House)</label>
                        <div style="height:34px; display:flex; align-items:center;">
                            <span id="bed_aih">-</span>
                        </div>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:16px; margin-top:8px;">
                    <a href="javascript:void(0)" data-bs-dismiss="modal"
                        style="text-decoration:none; color:#000; font-weight:600; align-self:center;">
                        Cancel
                    </a>
                    <button type="button" id="bulkEditDatesSaveBtn"
                        style="min-width:170px; height:38px; border:none; border-radius:10px; background:#000; color:#fff; font-weight:600; padding:0 20px;">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
