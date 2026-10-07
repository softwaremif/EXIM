<!-- <div class="modal fade" id="ModalRequestInv" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="border: none;">
                <h5 class="modal-title">Form Request No Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="d-flex align-items-center gap-3 mt-3">
                <div class="p-0">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                        </span>
                        <input type="text" class="form-control search" id="searchByInputOrder" onkeyup="SearchOrder()" placeholder="Search...">
                    </div>
                </div>
            </div>

            <br>

            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div>
                    <table id="dgOrder" class="easyui-datagrid" title="" style="width:100%; height:auto" url="{{ route('api.get-spb-for-invreq') }}"
                        align="center" toolbar="#tb" striped="true" pagination="true" pageSize="100" fitColumns="true" idField="spbpk"
                        pageList="[100,200,300,500]" method="get" rownumbers="false" multiple="false" collapsible="true"
                        data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false, singleSelect:true">
                        <thead>
                            <tr>
                                <th field="ck" width="35" checkbox="true"></th>
                                <th width="100" field="tanggal">Tanggal</th>
                                <th width="100" field="noinv">No INV</th>
                                <th width="100" field="nobukti">No SPB</th>
                                <th width="100" field="totalsatuan">Total Pcs</th>
                                <th width="100" field="gudang">Gudang</th>
                                <th width="100" field="jangkutan">Jenis Angkutan</th>
                                <th width="100" field="pangkutan">Perusahaan Angkutan</th>
                                <th width="100" field="contno">Cont No</th>
                                <th width="100" field="sealno">Seal No</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer m-0 p-0">
                <div class="p-0">
                    No. Inv
                    <input id="noinv" class="easyui-validatebox height_input" name="noinv" type="text" placeholder="Ex : 0060/AKB/MIF/2025">
                </div>
                <button type="button" class="btn btn-secondary my-2 py-1" onclick="SubmitReqInv()">Save</button>
            </div>
        </div>
    </div>
</div> -->

<div class="modal fade" id="ModalRequestInv" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content p-4 gap-1">

            <div class="modal-header" style="border: none;">
                <h5 class="modal-title">Form Request No Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="p-0">
                    <div class="input-group flex-nowrap input-group-search" style="height:34px;">
                        <span class="input-group-text search">
                            <img src="{{ asset('public/css/images/Shape.png') }}" plain="true" width="18px" height="18px">
                        </span>
                        <input type="text" class="form-control search" id="searchByInputReqInv" onkeyup="loadDataByFilterReqInv()" placeholder="Search...">
                    </div>
                </div>


                <select class="easyui-combobox" id="filterByMonthReqInv" data-options="editable:false, onChange:loadDataByFilterReqInv" style="width:150px; height:34px;">
                    <?php
                    $months = [
                        '01' => 'January',
                        '02' => 'February',
                        '03' => 'March',
                        '04' => 'April',
                        '05' => 'May',
                        '06' => 'June',
                        '07' => 'July',
                        '08' => 'August',
                        '09' => 'September',
                        '10' => 'October',
                        '11' => 'November',
                        '12' => 'December',
                    ];

                    $currentMonth = date('m');
                    foreach ($months as $key => $month) {
                        $selected = ($key == $currentMonth) ? 'selected' : '';
                        echo "<option value='$key' $selected>$month</option>";
                    }
                    ?>
                </select>

                <select class="easyui-combobox" id="filterByYearReqInv" data-options="editable:false, panelHeight:'auto', onChange:loadDataByFilterReqInv"
                    style="width:100px;">
                    <?php
                    for ($i = date('Y'); $i >= date('Y') - 6; $i -= 1) { ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php } ?>
                </select>

                <div class="ms-auto">
                    <div id="sortlist-grup" class="p-1 pb-0 responsive">
                        <input type="hidden" id="sortlistByDateReqInv" name="sortlistByDateReqInv" size="5">
                        <button class="btn btn-sortlist dropdown-toggle-custom-sortlist pt-0" data-bs-toggle="dropdown" data-bs-target="#menu-sortlist" aria-expanded="true">
                            <img src="{{ asset('public/css/images/sort.png') }}" alt="" class="imgsort">
                            <span id="InvDateDescReqInv">Invoice date (desc)</span>
                            <span id="InvDateAscReqInv" class="d-none">Invoice date (asc)</span>
                        </button>
                        <ul id="menu-sortlist" class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global dropdown-menu-sortlist pt-0 pb-0">
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2" onclick="sortlistByDateReqInv(11)">Invoice date (descending)</a></li>
                            <li><a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2" onclick="sortlistByDateReqInv(12)">Invoice date (ascending)</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="modal-body d-flex flex-column gap-3 m-0 p-0">
                <div>
                    <table id="dgOrder" class="easyui-datagrid" title="" style="width:100%; height:auto" url="{{ route('api.get-spb-for-invreq') }}"
                        align="center" toolbar="#tb" striped="true" pagination="true" fitColumns="true" idField="spbpk"
                        pageList="[100,200,300,500]" pageSize="100" method="get" rownumbers="false" multiple="true" collapsible="true"
                        data-options="multiSort:true, remoteSort:false, border:true, dnd:true, checkOnSelect:false, singleSelect:true">
                        <thead>
                            <tr>
                                <th field="ck" width="35" checkbox="true"></th>
                                <th width="100" field="tanggal">Tanggal</th>
                                <th width="100" field="noinv">No INV</th>
                                <th width="100" field="nobukti">No SPB</th>
                                <th width="100" field="totalsatuan">Total Pcs</th>
                                <th width="100" field="gudang">Gudang</th>
                                <th width="100" field="jangkutan">Jenis Angkutan</th>
                                <th width="100" field="pangkutan">Perusahaan Angkutan</th>
                                <th width="100" field="contno">Cont No</th>
                                <th width="100" field="sealno">Seal No</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            
            <div class="modal-footer m-0 p-0">
                <div class="p-0">
                    No. Inv
                    <input id="noinv" class="easyui-validatebox height_input" name="noinv" type="text" placeholder="Ex : 0060/AKB/MIF/2025">
                </div>
                <button type="button" class="btn btn-secondary my-2 py-1" onclick="SubmitReqInv()">Save</button>
            </div>
        </div>
    </div>
</div>