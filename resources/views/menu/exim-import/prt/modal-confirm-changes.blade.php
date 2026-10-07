<div class="modal fade" id="confirmChangesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px; border:none;">
            <div class="modal-body" style="padding:24px;">
                <div style="font-size:20px; font-weight:700; color:#000; margin-bottom:8px;">
                    Confirm changes
                </div>
                <div style="color:#555; margin-bottom:4px;">
                    Please confirm the following changes before proceeding.
                </div>
                <div style="color:#555; margin-bottom:20px;">
                    This will be announced to all related parties once confirmed.
                </div>

                 <div id="confirmChangesInfo" style="color:#359DD9; font-weight:600; margin-bottom:16px; display:none;">
                    {{-- diisi JS, khusus utk bulk edit dates --}}
                </div>

                <div id="confirmChangesList" style="margin-bottom:24px;">
                    {{-- diisi JS --}}
                </div>

                <div style="display:flex; justify-content:flex-end; gap:16px;">
                    <a href="javascript:void(0)" data-bs-dismiss="modal"
                        style="text-decoration:none; color:#000; font-weight:600; align-self:center;">
                        Cancel
                    </a>
                    <button type="button" id="confirmChangesSaveBtn"
                        style="min-width:170px; height:38px; border:none; border-radius:10px; background:#000; color:#fff; font-weight:600; padding:0 20px;">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>