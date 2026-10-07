<div class="header" id="myHeader"
    style="
        display:none;
        background:#359DD9;
        color:white;
        padding:16px;
        gap:24px;
        width:100%;
        height:48px;
        box-shadow:0px 0px 16px -4px rgba(0,0,0,.12);
        justify-content: end;
        position: fixed;
        top: 57px;
        left: 0;
        z-index: 9999;
     ">
    <div id="countp" style="margin-right:auto; margin-left:32px;">
        0 items selected
    </div>

    @if (Session::get('login') == 'exim' || Session::get('login') == 'import')
        <div class="detail" id="editDatesBtn" style="margin-right:8px; width:150px;">
            <a href="javascript:void(0)" onclick="openBulkEditDatesModal();" style="color:#FFFFFF; text-decoration:none;">
                Edit ETA & EIH Dates
            </a>
        </div>
    @endif

    <div class="detail" id="cls">
        <a href="javascript:void(0)" style="color:#FFFFFF; text-decoration:none;">
            Close Menu
        </a>
    </div>
</div>
