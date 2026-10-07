<div class="modal fade" id="ModalInvoice" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <b>Print Invoice</b>
            </div>
            <div class="modal-body">

                <div class="form-group d-none">
                    <label for="invpk">INVPK:</label>
                    <input type="text" id="invpk2" class="form-control" readonly>
                </div>

                <div class="form-group d-none">
                    <label for="spbpk">SPBPK:</label>
                    <input type="text" id="spbpk2" class="form-control" readonly>
                </div>

                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="cek" value="1" checked>
                    <label class="form-check-label">PDF Invoice Beacukai</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="cek" value="2">
                    <label class="form-check-label">PDF Invoice Finance</label>
                </div>
            </div>
            <div class="modal-footer">
                <a href="javascript:void(0)" style="color:#359DD9; text-decoration:none;" onclick="CancelModalPrint()"><b>Cancel</b></a>
                <button type="button" class="btn btn-dark" onclick="cetak();">Export</button>
            </div>
        </div>
    </div>
</div>