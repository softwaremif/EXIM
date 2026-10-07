<nav class="navbar pb-1 pt-1">
    <div class="container-fluid" style="background-color: white;">
        <div class="navbar-brand d-inline-flex d-inline-flex-custom" href="#" style="padding-left:10px;">
            <div class="p-1 align-self-center">
                <div div class="input-group">
                    <div class="align-self-center" style="margin-top: -1%;">
                        <img id="logo-brand" src="{!! asset('public/css/images/morich.png') !!}">
                    </div>
                    <div class="align-self-center" align=center>
                        <span class="nav-title">EXIM</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-inline-flex d-inline-flex-custom align-self-center gap-2" style="padding-right: 20px;">
            <div class="p-0 d-inline-flex flex-wrap gap-1" style="margin-right: 180px;">

                {{-- EXPORT: tampil untuk gruppk 21 dan 6 --}}
                @if (in_array((int) Session::get('guserpk'), [42, 44, 45, 7]))
                    <div class="align-self-center">
                        <a href="{{ route('shipplan.index') }}" class="link"
                            style="border:none; font-weight:700; color:{{ request()->is('exim-export/shipplan/*') ? '#000000' : '' }};">
                            Export
                        </a>
                    </div>
                @endif

                {{-- IMPORT: tampil untuk gruppk 22 dan 6 --}}
                @if (in_array((int) Session::get('guserpk'), [43, 7]))
                    <div class="align-self-center">
                        <a href="{{ route('exim.import.order') }}" class="link"
                            style="border:none; font-weight:700; color:{{ request()->routeIs('exim.import.*') ? '#000000' : '' }};">
                            Import
                        </a>
                    </div>
                @endif

            </div>

            {{-- Notifikasi --}}
            {{-- <div class="p-1 align-self-center d-flex align-items-center" style="position:relative;">

                <input type="image" id="notifBellIcon" src="{{ asset('public/css/images/notif.jpeg') }}" width="30"
                    height="30" alt="Notification" onclick="toggleNotificationDropdown()"
                    style="transform: matrix(-1, 0, 0, 1, 0, 0);">

                <div id="notificationDropdown" style="
                            display:none;
                            position:absolute;
                            top:45px;
                            right:-60px;
                            width:400px;
                            background:#FFFFFF;
                            border-radius:8px;
                            box-shadow:0px 4px 18px rgba(0,0,0,.15);
                            padding:18px 18px 12px 18px;
                            z-index:99999;
                            text-align:left;">

                    <div id="notificationList"></div>

                    <div id="notificationShowMore"
                        style="display:none; color:#359DD9; font-size:18px; cursor:pointer; margin-top:10px;"
                        onclick="openNotifModal()">
                        Show More
                    </div>
                </div>

            </div> --}}

            {{-- Notifikasi --}}
            @if (Session::get('login') == 'exim' || Session::get('login') == 'import')
                <div class="p-1 align-self-center d-flex align-items-center" style="position:relative;">

                    <input type="image" id="notifBellIcon" src="{{ asset('public/css/images/notif.jpeg') }}" width="30"
                        height="30" alt="Notification" onclick="toggleNotificationDropdown()"
                        style="transform: matrix(-1, 0, 0, 1, 0, 0);">

                    {{-- Dropdown notif --}}
                    <div id="notificationDropdown" style="
                                        display:none;
                                        position:absolute;
                                        top:45px;
                                        right:-20px;
                                        width:400px;
                                        background:#FFFFFF;
                                        border-radius:8px;
                                        box-shadow:0px 4px 18px rgba(0,0,0,.15);
                                        padding:8px;
                                        z-index:99999;
                                        text-align:left;">

                        <div id="notificationList"></div>

                        <div id="notificationShowMore"
                            style="display:none; color:#359DD9; font-size:18px; cursor:pointer; margin-top:10px;"
                            onclick="openNotifModal()">
                            Show More
                        </div>
                    </div>

                </div>
            @endif

            {{-- User Dropdown --}}
            <div class="p-1 align-self-center d-flex align-items-center">
                @if (!empty(Session::get('login')))
                    <div class="btn-group p-0">
                        <button type="button" class="btn dropdown-toggle-custom p-0" data-bs-toggle="dropdown"
                            aria-expanded="false">

                            {{ Str::limit(ucwords(strtolower(Session::get('login'))), 8) }}

                            <img id="polygon" src="{{ asset('public/css/images/Polygon.png') }}" style="margin-top: -2px;">
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom-global p-0">
                            <li>
                                <a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-start p-2"
                                    onclick="changePassword()">
                                    Password
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider p-0 m-0">
                            </li>

                            <li>
                                <a class="dropdown-item dropdown-item-custom-global dropdown-item-custom-global-end p-2"
                                    href="{{ route('logout') }}">
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif
            </div>

        </div>
</nav>