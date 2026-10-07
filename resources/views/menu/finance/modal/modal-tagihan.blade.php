<!-- Awal Modal Tagihan -->
<div class="modal fade" id="KlikModalTagihan" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content p-4 gap-1">
            <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetFormTagihan()"></button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="p-0">
                    Gudang
                    <input style="width: 159px; height: 28px;" id="SelectGudangTagihan" required name="gudpk" class="easyui-combobox" method="get"
                        data-options="valueField:'gudpk', textField:'gudnm', prompt:'Pilih Gudang', url:'{{ route('api.get-gudang') }}', onChange:function(){ loadDataByFilterModalTagihan(); updateExportButtonStateTagihan(); }, limitToList:'true'">
                </div>
                <div class="p-0">
                    Periode:
                    <input id="SelectStartTagihan" name="SelectStartTagihan" class="easyui-datebox" style="width: 110px; height: 28px;" data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalTagihan,">
                </div>
                <div class="p-0">
                    Sampai:
                    <input id="SelectFinishTagihan" name="SelectFinishTagihan" class="easyui-datebox" style="width: 110px; height: 28px;" data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalTagihan,">
                </div>
                <a id="exportButtonTagihan" href="javascript:void(0)" onclick="TagihanexportFilteredData();" style="font-size:14px;" class="btn btn-sm btn-dark" disabled>Export Tagihan</a>
            </div>
            <br>

            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div>
                    <table id="dgTagihan" class="easyui-datagrid" title="" style="width:100%;" url="{{ route('api.get-export-tagihan') }}"
                        align="center" toolbar="#tb" striped="true" pagination="true" pageSize="100" fitColumns="false"
                        pageList="[100,200,300,500]" method="get" rownumbers="false" multiple="true" collapsible="true"
                        data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                        <thead>
                            <tr>
                                <th field="tanggal" width="90">Tanggal</th>
                                <th field="gudang" width="100">Gudang</th>
                                <th field="noinv" width="120">No Inv</th>
                                <th field="nokk" width="100">No KK</th>
                                <th field="nobukti" width="100">No SPB</th>
                                <th field="totqty" width="50">Qty</th>
                                <th field="ppn" width="50">PPN</th>
                                <th field="kursppn" width="50">Kurs Pajak</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Akhir Modal Tagihan -->