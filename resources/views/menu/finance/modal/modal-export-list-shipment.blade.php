<div class="modal fade" id="KlikModalExportListShipment" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-fullscreen-xxl-down modal-dialog-scrollable">
        <div class="modal-content p-4 gap-1">

            <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetFormExportKwitansi()"></button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="p-0">
                    Gudang
                    <input style="width: 159px; height: 28px;" id="SelectGudangExport" required name="gudpk"
                        class="easyui-combobox" method="get" data-options="valueField:'gudpk', textField:'gudnm', prompt:'Pilih Gudang', url:'{{ route('api.get-gudang') }}', onChange:function(){ loadDataByFilterModalExportListShipment(); ChangeKwitansiExportButtonState(); }, limitToList:'true'">
                </div>
                <div class="p-0">
                    Periode:
                    <input id="SelectStartExport" name="SelectStartExport" class="easyui-datebox" style="width: 110px; height: 28px;"
                        data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalExportListShipment">
                </div>
                <div class="p-0">
                    Sampai:
                    <input id="SelectFinishExport" name="SelectFinishExport" class="easyui-datebox" style="width: 110px; height: 28px;"
                        data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalExportListShipment">
                </div>
                <a id="ButtonKlikExportListShipment" href="javascript:void(0);" onclick="OnClickExportListShipment();" style="font-size:14px;" class="btn btn-sm btn-dark" disabled>Export List Shipment</a>
            </div>

            <br>

            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div>
                    <table id="dgExportListShipment" class="easyui-datagrid" title="" style="width:100%;" url="{{ route('api.get-export-list-shipment') }}"
                        align="center" toolbar="#tb" striped="true" pagination="true" pageSize="100" fitColumns="false"
                        pageList="[100,200,300,500]" method="get" rownumbers="false" singleSelect="true" collapsible="true">
                        <thead>
                            <tr>
                                <th field="tanggal" width="70">Tanggal</th>
                                <th field="noinv" width="125">No Inv</th>
                                <th field="nobukti" width="120">No SPB</th>
                                <th field="gudang" width="80">Gudang</th>
                                <th field="totqty" width="50">Qty</th>
                                <th field="pricedt" width="70">Price Unit</th>
                                <th field="amount" width="110">Grand total</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>