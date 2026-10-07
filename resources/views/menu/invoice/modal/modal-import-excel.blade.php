<div class="modal fade" id="ModalImportExcel" tabindex="-1" aria-labelledby="ModalImportExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <form id="id-form-change-upload">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: none;">
                    <h1 class="modal-title fs-5" id="ModalImportExcelLabel">Form Upload Excel</h1>
                </div>

                <div class="modal-body">
                    <label>Input file:</label>
                    <input type="file" id="uploadfile" name="uploadfile" class="form-control" required>
                </div>

                <div class="ps-3">
                    <a href="{{ asset('public/upload/contoh-baku/contoh-baku-import-invoice-shipment.xls') }}" target="_blank" style="text-decoration:none; font-size:16px;color:#359DD9; font-weight:400; line-height:20px; letter-spacing:0em; text-align: left;">Contoh Baku Import Excel</a>
                </div>
                <div class="modal-footer" style="border:none;">
                    <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="resetFormUpload()"><b>Cancel</b></a>
                    <button type="button" class="btn btn-dark" onclick="validate_import();">Confirm</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- modal alert sudah ada data belum untuk upload-->
<div class="modal fade" id="validate-import-excel" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 position-relative">
            <!-- <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button> -->
            <div class="justify-content-center gap-3 pt-4">
                <div class="warning-blink-1 pb-3">
                    List detail invoice akan digantikan dengan data upload excel yang baru!
                </div>
                <!-- <center><button type="button" style="margin: 0 auto;" class="btn btn-outline-primary" data-bs-dismiss="modal" aria-label="Close" onclick="validasi_import();">Confirm</button></center> -->
            </div>
            <div class="modal-footer">
                <div class="px-4 py-1 font-4 pointer" onclick="cancel_upload();"
                    data-bs-dismiss="modal">Cancel</div>
                <div class="px-4 py-1 font-4 pointer bg-black rounded" onclick="onSubmitNewUpload();">Confirm</div>
            </div>
        </div>
    </div>
</div>