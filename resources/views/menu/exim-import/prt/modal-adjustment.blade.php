<div id="dialog-backdrop"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
           background:rgba(0,0,0,0.5); z-index:10000;">

    <div id="dialog-forma"
        style="display:none; width:1017px; height:600px; padding:5px; top:15px; overflow:hidden; border-radius:8px; background:white; border:1px solid #ddd; position:fixed; z-index:10001; left:50%; transform:translateX(-50%);">

        <form id="forma" method="post">
            <div style="width:1000px; height:585px;">

                <div id="header-dialog" style="margin-top:5px;">
                    <input type="hidden" name="popk" id="popka">
                    <input type="hidden" name="importpk" id="importpk">
                    <input type="hidden" name="ck1a" id="ck1a" value="0">
                    <input type="hidden" name="dtqty" id="dtqty" value="">

                    <span style="margin-left:10px; font-size:18px; line-height:24px; font-weight:700;">
                        Adjustment shipment quantity
                    </span>

                    <br>

                    <span style="margin-left:10px; font-size:14px; line-height:18px; font-weight:400;">
                        Enter the material quantity that will be shipped in this shipment in the "input qty to ship"
                        form.
                    </span>
                    <div style="display:flex;">
                        <a href="javascript:void(0)" class="button-cancel"
                            style="text-decoration:none; position:absolute; right:190px; top:24px;"
                            onclick="closeShipmentModal();">
                            Cancel
                        </a>

                        <a href="javascript:void(0)" class="button-request-po"
                            style="
                                text-decoration:none;
                                position:absolute !important;
                                left:850px !important;
                                top:24px !important;
                                bottom:auto !important;"
                            onclick="openCreateShipmentModal();">
                            Submit shipment
                        </a>
                    </div>

                    <br><br>

                    &nbsp;&nbsp;
                    <input type="checkbox" name="ck1" id="ck1">
                    <span style="font-size:14px; line-height:18px; font-weight:400;">
                        Ship entire PO
                    </span>
                </div>

                <div style="width:99%; height:75%; overflow-y:auto; overflow-x:hidden;">
                    <div id="tabel2" style="height:500px; margin-top:15px; margin-left:10px; margin-bottom:64px;">
                        <table class="table table-bordered" style="width:98%; font-size:13px;">
                            <thead class="material-ship-thead">
                                <tr>
                                    <th>Item</th>
                                    <th style="width:192px">Description</th>
                                    <th>Supplier</th>
                                    <th>Color</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Balance</th>
                                    <th>Input qty to ship now</th>
                                </tr>
                            </thead>

                            <tbody id="poShipBody">
                                <tr>
                                    <td colspan="8" style="text-align:center;">
                                        Select PO first
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>
