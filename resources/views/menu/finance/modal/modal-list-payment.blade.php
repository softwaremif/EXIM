<div class="modal fade" id="KlikModalListFinance" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-fullscreen-xxl-down modal-dialog-scrollable">
        <div class="modal-content p-4 gap-1">
            <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetFormListPayment();"></button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="p-0">
                    Gudang
                    <input style="width: 159px; height: 28px;" id="SelectGudang" required name="gudpk" class="easyui-combobox" method="get"
                        data-options="valueField:'gudpk', textField:'gudnm', prompt:'Pilih Gudang', url:'{{ route('api.get-gudang') }}', onChange:function(){ loadDataByFilterModalListPay(); PdfExportButtonState(); }, limitToList:'true'">
                </div>
                <div class="p-0">
                    Periode:
                    <input id="SelectStart" name="SelectStart" class="easyui-datebox" style="width: 110px; height: 28px;" data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalListPay,">
                </div>
                <div class="p-0">
                    Sampai:
                    <input id="SelectFinish" name="SelectFinish" class="easyui-datebox" style="width: 110px; height: 28px;" data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalListPay,">
                </div>
                <a id="exportButton" href="javascript:void(0)" onclick="PdfFilteredData();" style="font-size:14px;" class="btn btn-sm btn-dark" disabled>Export PDF</a>
            </div>
            <br>

            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div>
                    <table id="dgListPayment" class="easyui-datagrid" title="" style="width:100%;" url="{{ route('api.get-cmt-modal-export') }}"
                        align="center" toolbar="#tb" striped="true" pagination="true" pageSize="100" fitColumns="false"
                        pageList="[100,200,300,500]" method="get" rownumbers="false" multiple="true" collapsible="true"
                        data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                        <thead>
                            <tr>
                                <th field="index" width="30">No</th>
                                <th field="gudang" width="100">GUDANG</th>
                                <th field="nokk" width="100">NO KONTRAK</th>
                                <th field="tanggal" width="100">TGL INV</th>
                                <th field="noinv" width="100">NO INVOICE</th>
                                <th field="nofp" width="100">NO FAKTUR PAJAK</th>
                                <th field="qty" width="100">QTY</th>
                                <th field="pricedt" width="100">PRICE UNIT</th>
                                <th field="amountlistpay" width="100">AMOUNT</th>
                                <th field="totamountinv" width="100">TOTAL AMOUNT<br>INVOICE</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>