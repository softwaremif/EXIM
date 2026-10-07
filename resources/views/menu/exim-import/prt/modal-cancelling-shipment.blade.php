<div id="dialog-cancel-backdrop"
    style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,.20);
        z-index:10001;">

    <div
        style="
            width:391px;
            height:175px;
            padding:5px;
            background:white;
            border-radius:8px;
            border:1px solid #ddd;
            position:absolute;
            top:175px;
            left:50%;
            transform:translateX(-50%);">

        <div
            style="margin-top:15px; margin-left:10px; width:250px; color:black; font-size:18px; font-weight:700; line-height:24px;">
            Cancelling shipment
        </div>

        <div
            style="margin-top:10px; margin-left:10px; width:359px; color:black; font-size:14px; font-weight:400; line-height:18px;">
            The purchase orders within this plan will be returned back to the purchase order list waiting for a new
            assignment. Would you like to proceed?
        </div>

        <a href="javascript:void(0)" class="button-cancel"
            style="text-decoration:none; position:absolute; right:205px; bottom:25px;"
            onclick="closeCancelShipmentModal();">
            Cancel
        </a>

        <a href="javascript:void(0)" id="cancelConfirmBtn" class="button-request-po" onclick="confirmCancelShipment();">
            Confirm
        </a>
    </div>
</div>

<div id="dialog-delete-material-backdrop"
    style="
        display:none;
        position:fixed;
        top:0;
        left:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,.20);
        z-index:10050;">

    <div
        style="
            width:340px;
            padding:0;
            background:white;
            border-radius:6px;
            border:1px solid #ddd;
            position:absolute;
            top:200px;
            left:50%;
            transform:translateX(-50%);
            box-shadow:0 4px 16px rgba(0,0,0,.15);">

        <div
            style="display:flex; justify-content:space-between; align-items:center; padding:10px 14px; border-bottom:1px solid #eee;">
            <span style="font-weight:700; font-size:15px;">Confirm</span>
            <a href="javascript:void(0)" onclick="closeDeleteMaterialConfirm();"
                style="text-decoration:none; color:#666; font-size:16px;">✕</a>
        </div>

        <div style="display:flex; gap:12px; align-items:flex-start; padding:18px 16px;">
            <span style="font-size:28px; line-height:1;">❓</span>
            <span style="font-size:14px; color:#000; padding-top:4px;">Are you sure you want to delete this PO?</span>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:8px; padding:10px 14px; border-top:1px solid #eee;">
            <button type="button" onclick="confirmDeleteMaterial();"
                style="padding:6px 18px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; cursor:pointer; font-size:14px;">
                Ok
            </button>
            <button type="button" onclick="closeDeleteMaterialConfirm();"
                style="padding:6px 18px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; cursor:pointer; font-size:14px;">
                Cancel
            </button>
        </div>
    </div>
</div>

<div id="toastNotifShip"
    style="
        display:none;
        position:fixed;
        left:16px;
        bottom:16px;
        width:auto;
        padding:10px;
        background:#19B715;
        color:#FFFFFF;
        border-radius:4px;
        z-index:99999;
        font-family:Arial;
        font-size:16px;
        font-weight:80;
        line-height:24px;">

        <span id="toastNotifShipMessage">Item successful updated</span>

        {{-- <span style="
        font-size:16px;
        font-weight:80;
        margin-left:4px;">
            ✓
        </span> --}}
</div>
