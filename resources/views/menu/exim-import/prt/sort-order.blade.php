<div id="frame-filter" style="width:auto;">
    <div id="frame-filter-newest" style="display:flex; align-items:center; gap:8px; width:auto; height:20px; z-index:20;">
        
        <div class="dropdwn" style="position:relative; display:flex; align-items:center; margin-left:0;">

            <input onclick="myFunction(event)" type="image" class="imgsort"
                src="{{ asset('public/css/images/sort.png') }}" alt="Sort"
                style="width:32px; height:32px; margin:0; padding:0; display:block;">

            <div id="myDropdown" class="dropdwn-content" style="width:200px; height:200px;">
                <div class="new-entry">
                    <a href="javascript:void(0)" onclick="Searcha();">Newest entry</a>
                </div>

                <div class="old-entry">
                    <a href="javascript:void(0)" onclick="Searchb();">Oldest entry</a>
                </div>

                <div class="new-entry">
                    <a href="javascript:void(0)" onclick="Searchd();">Recently modify</a>
                </div>

                <div class="new-entry">
                    <a href="javascript:void(0)" onclick="Searche();">Supplier ETD (Awal)</a>
                </div>

                <div class="new-entry">
                    <a href="javascript:void(0)" onclick="Searchf();">Supplier ETD (Akhir)</a>
                </div>
            </div>
        </div>

        <a href="javascript:void(0);" id="orderSortTrigger" onclick="myFunction(event)"
            style="text-decoration:none; color:#000000; display:flex; align-items:center; margin:0; padding:0;">

            <span style="font-family:Arial; font-size:14px; line-height:20px;">
                @if (request('cari6') == 1)
                    Oldest entry
                @elseif(request('cari7') == 1)
                    Recently modify
                @elseif(request('cari8') == 1)
                    Supplier ETD (Awal)
                @elseif(request('cari9') == 1)
                    Supplier ETD (Akhir)
                @else
                    Newest entry
                @endif
            </span>
        </a>

    </div>
</div>
