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
                    <a href="{{ asset('public/upload/contoh-baku/contoh-baku-import.xls') }}" target="_blank" style="text-decoration:none; font-size:16px;color:#359DD9; font-weight:400; line-height:20px; letter-spacing:0em; text-align: left;">Contoh Baku Import Excel</a>
                </div>

                <div class="modal-footer" style="border:none;">
                    <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="resetFormUpload()"><b>Cancel</b></a>
                    <button type="button" class="btn btn-dark" onclick="onSubmitNewUpload()">Confirm</button>
                </div>
            </div>
        </form>
    </div>
</div>