<div id="atas">

    {{-- <div id="judul" style="width: 500px;">
        @if(request()->routeIs('exim.import.order'))
            List of materials awaiting for shipment
        @elseif(request()->routeIs('exim.import.ship'))
            List of material for shipment planning
        @endif
    </div> --}}

    <div id="judul" style="width: 500px;">
        @if(request()->routeIs('exim.import.order'))
            Awaiting for shipment
        @elseif(request()->routeIs('exim.import.ship'))
            Shipment planning
        @endif
    </div>

    <div id="frame-menu-detail" style="margin-left:15%; margin-top:0px; width:100%; height:auto;">

        <a href="{{ route('exim.import.order') }}"
           class="link2"
           style="{{ request()->routeIs('exim.import.order') ? 'border-bottom:3px solid #000000; color:#000000;' : '' }}">
            Purchases orders
        </a>

        <a href="{{ route('exim.import.ship') }}"
           class="link2"
           style="{{ request()->routeIs('exim.import.ship') ? 'border-bottom:3px solid #000000; color:#000000;' : '' }}">
            Shipment Planning
        </a>

    </div>

</div>