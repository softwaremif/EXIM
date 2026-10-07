<div class="modal fade" id="KlikExportKwitansi2" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content p-4 gap-1">
            <!-- <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                <div class="container-fluid">
                    <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal" data-bs-target="#ChooseModal">
                        <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="25" height="25">
                        <span>BACK</span>
                    </div>
                </div>
            </div> -->
            <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start">
                <!-- <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1> -->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetFormKwitansi()"></button>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="p-0">
                    Gudang
                    <input style="width: 159px; height: 28px;" id="SelectGudangKwitansi" required name="gudpk"
                        class="easyui-combobox" method="get" data-options="valueField:'gudpk', textField:'gudnm', prompt:'Pilih Gudang', url:'{{ route('api.get-gudang') }}', onChange:function(){ loadDataByFilterModalKwitansi(); ChangeKwitansiExportButtonState(); }, limitToList:'true'">
                </div>
                <div class="p-0">
                    Periode:
                    <input id="SelectStartKwitansi" name="SelectStartKwitansi" class="easyui-datebox" style="width: 110px; height: 28px;"
                        data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalKwitansi">
                </div>
                <div class="p-0">
                    Sampai:
                    <input id="SelectFinishKwitansi" name="SelectFinishKwitansi" class="easyui-datebox" style="width: 110px; height: 28px;"
                        data-options="formatter:myformatter, parser:myparser, onChange:loadDataByFilterModalKwitansi">
                </div>

                <div class="d-flex flex-wrap align-items-center">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                        </span>
                        <input type="text" class="form-control search" id="searchByInputOnModalExportExcel" onkeyup="SearchOnModalExportExcel()" placeholder="Search...">
                    </div>
                </div>

                <a id="ButtonKlikExportKwitansi" href="javascript:void(0);" onclick="OnClickExportKwitansi();" style="font-size:14px;" class="btn btn-sm btn-dark" disabled>Export Excel Kwitansi</a>
            </div>

            <br>

            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div>
                    <table id="dgModal" class="easyui-datagrid" title="" style="width:100%;" url="{{ route('api.get-export-kwitansi') }}"
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
                                <th field="pricemat" width="100">Price Material</th>
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