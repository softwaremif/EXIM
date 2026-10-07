<div class="modal fade" id="ModalOrder" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <!-- <div class="modal-header d-flex flex-column m-0 p-0 py-2 border-0 align-items-start"> -->
            <div class="modal-header" style="border:none;">

                <!-- <div class="container-fluid">
                    <div class="d-flex gap-2 mb-3" data-bs-dismiss="modal" aria-label="Close" data-bs-toggle="modal" data-bs-target="#ChooseModal">
                        <img src="{!! asset('public/css/images/arrow-back.png') !!}" width="25" height="25">
                        <span>BACK</span>
                    </div>
                </div> -->
                <h5>Lookup From Order List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="KlikCloseModal();"></button>
            </div>


            <div class="d-flex flex-wrap ps-3 pb-2 gap-2 align-items-center">
                <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                    <span class="input-group-text search">
                        <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                    </span>
                    <input type="text" class="form-control search" id="searchByInputOrder" onkeyup="SearchOrder()" placeholder="Search...">
                </div>
            </div>

            <input type="hidden" name="invpk2" id="invpk2" value="{{ $getspbpk2->invpk ?? '' }}">

            <div class="modal-body">
                <div>
                    <table id="dgOrder" class="easyui-datagrid" title="" style="width:100%; height:auto" url="{{ route('api.get-lookup-order') }}"
                        align="center" toolbar="#tb" striped="true" pagination="true" fitColumns="true"
                        pageList="[100,200,300,500]"  pageSize="100" method="get" rownumbers="false" multiple="true" collapsible="true"
                        data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false">
                        <thead>
                            <tr>
                                <th field="ck" width="35" checkbox="true"></th>
                                <th field="funm" width="100">MD</th>
                                <th field="ordno" width="100">Order No</th>
                                <th field="byrnm" width="100">Buyer</th>
                                <th field="artno" width="100">Style</th>
                                <th field="prodes" width="250">Description</th>
                                <th field="season" width="100">Season</th>
                                <th field="srno" width="100">SR NO</th>
                                <th field="ordqty" width="100">Qty</th>
                                <th field="stage" width="100">Stage</th>
                                <th field="datecreate" width="100">Date Create</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer m-0 p-0">
                <!-- <input type="hidden" id="nilai_unit_filter" name="nilai_unit_filter"> -->
                <!-- <button type="button" id="get_button" class="btn btn-secondary my-2 py-1" onclick="SubmitModalLookup('ORDERLIST')">GET</button> -->
                <button type="button" id="get_button" class="btn btn-secondary my-2 py-1" onclick="SubmitModalLookup('ORDERLIST')">
                    <i class="fa fa-square-check"></i> Pilih
                </button>
            </div>
        </div>
    </div>
</div>