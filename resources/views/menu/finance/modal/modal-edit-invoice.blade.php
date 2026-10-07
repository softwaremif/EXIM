<div class="modal fade" id="ModalEdit" tabindex="-1" aria-labelledby="ModalEditLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="border: none;">
                <h1 class="modal-title fs-5" id="ModalDetailLabel" style="font-weight: 700;">Edit Invoice</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="number" id="invpk" hidden>
                <input type="number" id="spbpk" hidden>

                <div class="col-md-12">
                    <label for="tglinv" class="form-label">Tanggal INV</label>
                    <input type="text" class="form-control" id="tglinv" disabled>
                </div>

                <div class="col-md-12 mt-2">
                    <label for="noinv" class="form-label">No INV</label>
                    <input type="text" class="form-control" id="noinv" disabled>
                </div>

                <div class="col-md-12 mt-2">
                    <label for="nokk" class="form-label">No KK</label>
                    <input type="text" class="form-control" id="nokk" name="nokk">
                </div>

                <!-- <div class="row mt-3">
                    <div class="col-md-12">
                        <label for="priceppn" class="form-label">Price Ppn</label>
                        <input type="number" name="priceppn" id="priceppn" required="true" class="form-control">
                    </div>
                </div> -->

                <div class="row mt-3">
                    <div class="col-md-4">
                        <label for="priceppn" class="form-label">Price PPN</label>
                        <input type="number" name="priceppn" id="priceppn" required="true" disabled class="form-control">
                    </div>

                    <div class="col-md-4">
                        <label for="ppn" class="form-label">PPN</label>
                        <div class="input-group mb-3">
                            <input type="number" class="form-control" name="ppn" value="11" id="ppn">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="kursp" class="form-label">Kurs Pajak</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">IDR</span>
                            <input type="text" autocomplete="off" class="form-control" name="kursp" id="kursp" required="true">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="border: none;">
                <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="CancelModalEdit()"><b>Cancel</b></a>
                <button type="submit" onclick="onSubmit();" class="btn btn-dark">Save</button>
            </div>
        </div>
    </div>
</div>