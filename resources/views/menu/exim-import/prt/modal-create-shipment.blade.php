<div id="dialog-formb"
    style="display:none; width:818px; height:594px; padding:5px; top:15px; overflow:hidden; border-radius:8px; background:white; border:1px solid #ddd; position:fixed; z-index:10001; left:50%; transform:translateX(-50%);">

    <form id="formb" method="post">
        <input type="hidden" name="importpkb" id="importpkb" value="">

        <div style="margin-left:15px; margin-top:15px;">
            <div
                onclick="document.getElementById('dialog-formb').style.display='none'; document.getElementById('dialog-forma').style.display='block';">
                <img src="{{ asset('public/css/images/Vector2.png') }}"
                    style="width:27px; height:27px; cursor:pointer;">
            </div>

            <div style="margin-left:45px; margin-top:-25px; color:black; font-size:18px; font-weight:700;">
                Create shipment plan
            </div>

            <div style="margin-top:10px; color:rgba(0,0,0,.60); font-size:14px; font-weight:700;">
                Order details
            </div>

            <div style="color:rgba(0,0,0,.38); font-size:14px;">
                Supplier <span id="suppa" style="color:#000000;">BUCK MASON</span>
            </div>

            <div style="color:rgba(0,0,0,.38); font-size:14px;">
                POs <span id="poa" style="color:#000000;">IN030322</span>
            </div>

            <div style="margin-top:10px; color:rgba(0,0,0,.60); font-size:14px; font-weight:700;">
                Shipment details
            </div>
        </div>

        <table border="0" style="margin-left:15px; margin-top:10px;">
            <tr>
                <th width="250" align="left">Vassal type <span style="color:red;">*</span></th>
                <th width="350" align="left">Vassal name</th>
                <th width="350" align="left">AirwayBill Number</th>
            </tr>
            <tr>
                <td>
                    <div class="custom-dropdown" id="vastype-dropdown" style="width:150px;">
                        <input type="hidden" id="vastype" name="vastype" value="">

                        <button type="button" style="width:100%;" class="custom-dropdown-trigger"
                            onclick="toggleCustomDropdown(event, 'vastypeMenu')">
                            <span id="vastypeLabel">Choose vassal</span>
                            <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                        </button>

                        <div id="vastypeMenu" class="custom-dropdown-menu" style="width:100%;"></div>
                    </div>
                    <span id="vastype-error" class="field-error-text" style="display:none;">Vassal type is
                        required</span>
                </td>

                <td>
                    <input type="text" id="vastnm" name="vastnm" placeholder="Please input vassal name..."
                        style="width:250px; border-radius:8px; border: 1px solid; height: 34px;">
                </td>
                <td>
                    <input type="text" id="bill" name="bill" placeholder="Please input AirWayBill..."
                        style="width:250px; border-radius:8px; border: 1px solid; height: 34px;">
                </td>
            </tr>

            <tr>
                <th width="250" align="left">Arrival city</th>
                <th width="350" align="left">Port name</th>
            </tr>
            <tr>
                <td>
                    <div class="custom-dropdown" id="arrival-dropdown" style="width:150px;">
                        <input type="hidden" id="arrival" name="arrival" value="">

                        <button type="button" style="width:100%;" class="custom-dropdown-trigger"
                            onclick="toggleCustomDropdown(event, 'arrivalMenu')">
                            <span id="arrivalLabel">Choose arrival</span>
                            <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                        </button>

                        <div id="arrivalMenu" class="custom-dropdown-menu" style="width:100%;"></div>
                    </div>
                </td>
                <td>
                    <div class="custom-dropdown" id="port-dropdown" style="width:200px;">
                        <input type="hidden" id="port" name="port" value="">

                        <button type="button" style="width:100%;" class="custom-dropdown-trigger"
                            onclick="toggleCustomDropdown(event, 'portMenu')">
                            <span id="portLabel">Choose port</span>
                            <i class="bi bi-chevron-down custom-dropdown-icon"></i>
                        </button>

                        <div id="portMenu" class="custom-dropdown-menu" style="width:100%;"></div>
                    </div>
                </td>
            </tr>
        </table>

        <br>

        <div style="margin-left:15px; color:rgba(0,0,0,.60); font-size:14px; font-weight:700;">
            Item details
        </div>

        <table border="0" style="margin-left:15px;">
            <tr>
                <th width="200" align="left">Gross weight</th>
                <th width="350" align="left">Dimension of boxes</th>
                {{-- <th width="300" align="left">Total Package</th> --}}
                <th width="300" align="left">Total Karton</th>
            </tr>
            <tr>
                <td>
                    <input type="text" id="weight" name="weight" placeholder="How many Kg?"
                        style="width:150px; border-radius:8px; border: 1px solid; height: 34px;">
                </td>
                {{-- <td>
                    P <input type="text" id="dp" name="dp" placeholder="Input..."
                        style="width:65px; border-radius:8px; border: 1px solid; height: 34px;">
                    L <input type="text" id="dl" name="dl" placeholder="Input..."
                        style="width:65px; border-radius:8px; border: 1px solid; height: 34px;">
                    T <input type="text" id="dt" name="dt" placeholder="Input..."
                        style="width:65px; border-radius:8px; border: 1px solid; height: 34px;"> cm
                </td> --}}
                <td>
                    P <input type="text" id="dp" name="dp" placeholder="0.00"
                        inputmode="decimal"
                        oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/([.,].*)[.,]/g, '$1')"
                        style="width:65px; border-radius:8px; border: 1px solid; height: 34px;">
                    L <input type="text" id="dl" name="dl" placeholder="0.00"
                        inputmode="decimal"
                        oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/([.,].*)[.,]/g, '$1')"
                        style="width:65px; border-radius:8px; border: 1px solid; height: 34px;">
                    T <input type="text" id="dt" name="dt" placeholder="0.00"
                        inputmode="decimal"
                        oninput="this.value = this.value.replace(/[^0-9.,]/g, '').replace(/([.,].*)[.,]/g, '$1')"
                        style="width:65px; border-radius:8px; border: 1px solid; height: 34px;"> cm
                </td>
                <td>
                    <input type="text" id="amount" name="amount" placeholder="Input karton..."
                        style="width:250px; border-radius:8px; border: 1px solid; height: 34px;">
                </td>
            </tr>
        </table>

        <br>

        <div style="margin-left:15px; color:rgba(0,0,0,.60); font-size:14px; font-weight:700;">
            Shipment dates
        </div>

        <div class="row mb-1" style="margin-left:0; max-width:600px;">
            <div class="col" style="color:#00000061;">ETD <span style="color:red;">*</span></div>
            <div class="col" style="color:#00000061;">ETA</div>
            <div class="col" style="color:#00000061;">EIH (Estimate in house)</div>
        </div>

        <div class="row" style="margin-left:0; max-width:600px;">
            <div class="col">
                <div style="position:relative; display:flex; align-items:center;">
                    <input type="text" id="etd" name="etd" class="form-control form-control-sm"
                        readonly style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                    <span class="date-icon-trigger" data-target="etd"
                        style="position:absolute; right:8px; cursor:pointer; color:#888;">
                        📅
                    </span>
                </div>
                <span id="etd-error" class="field-error-text" style="display:none;">ETD is required</span>
            </div>

            {{-- <div class="col">
                <div style="position:relative; display:flex; align-items:center;">
                    <input type="text" id="eta" name="eta" class="form-control form-control-sm"
                        readonly style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                    <span class="date-icon-trigger" data-target="eta"
                        style="position:absolute; right:8px; cursor:pointer; color:#888;">
                        📅
                    </span>
                </div>
            </div>

            <div class="col">
                <div style="position:relative; display:flex; align-items:center;">
                    <input type="text" id="eih" name="eih" class="form-control form-control-sm"
                        readonly style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                    <span class="date-icon-trigger" data-target="eih"
                        style="position:absolute; right:8px; cursor:pointer; color:#888;">
                        📅
                    </span>
                </div>
            </div> --}}

            <div class="col">
                <div style="position:relative; display:flex; align-items:center;">
                    <input type="text" id="eta" name="eta" class="form-control form-control-sm"
                        readonly style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                    <span class="date-icon-trigger" data-target="eta"
                        style="position:absolute; right:8px; cursor:pointer; color:#888;">
                        📅
                    </span>
                </div>
                <span id="eta-error" class="field-error-text" style="display:none;"></span>
            </div>

            <div class="col">
                <div style="position:relative; display:flex; align-items:center;">
                    <input type="text" id="eih" name="eih" class="form-control form-control-sm"
                        readonly style="padding-right:32px; border-radius:8px; border: 1px solid; height: 34px;">
                    <span class="date-icon-trigger" data-target="eih"
                        style="position:absolute; right:8px; cursor:pointer; color:#888;">
                        📅
                    </span>
                </div>
                <span id="eih-error" class="field-error-text" style="display:none;"></span>
            </div>
        </div>

        <div style="display: flex; margin-top:20px; margin-left:15px;">
            <a href="javascript:void(0)" class="button-cancel"
                style="text-decoration:none; position:absolute; right:220px; bottom:35px;"
                onclick="closeCreateShipmentModal();">
                Cancel
            </a>

            <a href="javascript:void(0)" class="button-request-po"
                style="
                        text-decoration:none;
                        position:absolute;
                        left:auto !important;
                        right:30px !important;
                        bottom:35px;"
                onclick="openConfirmShipmentModal();">
                Continue
            </a>
        </div>
    </form>
</div>
