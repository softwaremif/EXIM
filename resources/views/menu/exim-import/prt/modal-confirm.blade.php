<div id="dialog-formc"
    style="
            display:none;
            position:fixed;
            top:0;
            left:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,.20);
            z-index:10001;">

    <form id="formc" method="post">
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

            <input type="hidden" name="importpkc" id="importpkc">
            <input type="hidden" name="vastype1" id="vastype1">
            <input type="hidden" name="vastnm1" id="vastnm1">
            <input type="hidden" name="bill1" id="bill1">
            <input type="hidden" name="arrivalpk1" id="arrivalpk1">
            <input type="hidden" name="portpk1" id="portpk1">
            <input type="hidden" name="weight1" id="weight1">
            <input type="hidden" name="dp1" id="dp1">
            <input type="hidden" name="dl1" id="dl1">
            <input type="hidden" name="dt1" id="dt1">
            <input type="hidden" name="amount1" id="amount1">
            <input type="hidden" name="etd1" id="etd1">
            <input type="hidden" name="eta1" id="eta1">
            <input type="hidden" name="eih1" id="eih1">
            <input type="hidden" name="popkall" id="popkall">

            <div
                style="margin-top:15px; margin-left:10px; width:250px; color:black; font-size:18px; font-weight:700; line-height:24px;">
                Confirm shipment plan
            </div>

            <div
                style="margin-top:10px; margin-left:10px; width:359px; color:black; font-size:14px; font-weight:400; line-height:18px;">
                Please confirm your shipment plan before proceeding. This shipment plan will be notified to all related
                parties.
            </div>

            <a href="javascript:void(0)" class="button-cancel"
                style="
                        text-decoration:none;
                        position:absolute !important;
                        left:auto !important;
                        right:205px !important;
                        bottom:25px !important;"
                onclick="document.getElementById('dialog-formc').style.display='none';">
                Cancel
            </a>

            <a href="javascript:void(0)" class="button-request-po"
                style="
                        text-decoration:none;
                        position:absolute !important;
                        left:auto !important;
                        right:35px !important;
                        bottom:25px !important;
                        width:150px !important;"
                onclick="createShipmentPlan();">
                Create shipment plan
            </a>
        </div>
    </form>
</div>
<div id="toastNotif"
    style="
        display:none;
        position:fixed;
        left:16px;
        bottom:16px;
        width:226px;
        padding:10px;
        background:#19B715;
        color:#FFFFFF;
        border-radius:4px;
        z-index:99999;
        font-family:Arial;
        font-size:16px;
        font-weight:80;
        line-height:24px;">

    <span>Shipment plan data saved</span>

    {{-- <span style="
        font-size:16px;
        font-weight:80;
        margin-left:4px;">
        ✓
    </span> --}}
</div>
