<html>

<head>
    <link rel="shortcut icon" href="{!! asset('public/css/images/morich.gif') !!}" />
    <title>Morich Indo Fashion</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" type="text/css" media="screen" href="{!! asset('public/css/styleku.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! asset('public/media/jquery-easyui-1.9.15/themes/gray/easyui.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! asset('public/media/jquery-easyui-1.9.15/themes/icon.css') !!}">
    <link rel="stylesheet" type="text/css" href="{!! asset('public/media/jquery-easyui-1.9.15/themes/color.css') !!}">
    <script type="text/javascript" src="{!! asset('public/media/jquery-easyui-1.9.15/jquery.min.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('public/media/jquery-easyui-1.9.15/jquery.easyui.min.js') !!}"></script>
    <script type="text/javascript" src="{!! asset('public/media/jquery-easyui-1.9.15/jquery.edatagrid.js') !!}"></script>
    <script type="text/javascript">
        $(function() {
            $('#dialog-formpswd').dialog('open').dialog('setTitle', '');

            @if(Session::has('success'))
            $('#dialog-formpswd').dialog('close');
            $('#dialog-formok').dialog('open').dialog('setTitle', '');
            var s = '<div class="text-infohijau" style="width:300px; text-align:center;"> Password successfully updated &nbsp; &#10004; </div>';
            $.messager.show({
                title: '',
                msg: s,
                timeout: 3000,
                showType: 'slide',
                icon: 'info',
                border: 'thin',
                width: 302,
                height: 42,
                style: {
                    left: 0,
                    right: '',
                    top: '',
                    bottom: -document.body.scrollTop - document.documentElement.scrollTop
                }
            });
            @endif
        });

        function cfrm() {
            window.location.href = "surat/index";
            $('#dialog-formok').dialog('close');
        }

        function back() {
            window.location.href = "logout";
        }

        function showpswd() {
            const togglePassword = document.querySelector("#togglePassword");
            const password = document.querySelector("#pswd");

            togglePassword.addEventListener("click", function() {
                const type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);
                this.classList.toggle("bi-eye");
            });
        }

        function showpswd2() {
            const togglePassword = document.querySelector("#togglePassword2");
            const password = document.querySelector("#cnfrm");

            togglePassword.addEventListener("click", function() {
                const type = password.getAttribute("type") === "password" ? "text" : "password";
                password.setAttribute("type", type);
                this.classList.toggle("bi-eye");
            });
        }

        function changepswd2() {
            var lowerCaseLetters = /[a-z]/g;
            var upperCaseLetters = /[A-Z]/g;
            var numbers = /[0-9]/g;
            var myInput = document.getElementById("pswd");
            var password = $("#pswd").val();
            var confirmPassword = $("#cnfrm").val();
            if (password != confirmPassword) {
                if (password == '') {
                    $("#eror2").show();
                } else {
                    $("#eror2").hide();
                }

                if (confirmPassword == '') {
                    $("#eror3").show();
                } else {
                    $("#eror3").hide();
                }

                if (!lowerCaseLetters || !upperCaseLetters || !numbers || myInput.value.length < 8) {
                    $("#eror1").show();
                } else {
                    $("#eror1").hide();
                }
                $("#eror4").show();
            } else {
                $('#eror5').hide();
                $("#eror4").hide();
                if (password == '') {
                    $("#eror2").show();
                } else {
                    $("#eror2").hide();
                }

                if (confirmPassword == '') {
                    $("#eror3").show();
                } else {
                    $("#eror3").hide();
                }

                if (!lowerCaseLetters || !upperCaseLetters || !numbers || myInput.value.length < 8) {
                    $("#eror1").show();
                } else {
                    var usrpk = $('#userpk').val();
                    var cnfrm = $('#cnfrm').val();
                    if (confirmPassword) {
                        var formData = $('#formpswd').serialize();
                        $('#formpswd').form('submit', {
                            type: 'POST',
                            url: '{{ route('updtpswdb') }}',
                            data: formData,
                            onSubmit: function() {
                                return $(this).form('validate');
                            },
                            success: function(data) {
                                $('#dialog-formpswd').dialog('close');
                                $('#formpswd').form('reset');
                                $("#eror1").hide();
                                $("#eror2").hide();
                                $("#eror3").hide();
                                $("#eror4").hide();
                                $("#eror5").hide();
                                $('#dialog-formok').dialog('open').dialog('setTitle', '');
                            }
                        });
                    }
                }
            }
        }
    </script>
</head>

<body>
    <div id="dialog-formpswd" class="easyui-dialog" closed="true" buttons="#dialog-buttons" data-options="border:false,resizable:false,modal:true,closable:false,title:false"
        style="width:425px;height:450px;padding:5px 5px;display:none;top:20px">
        <form name="formpswd" id="formpswd" method="post" action="">
            {{ csrf_field() }}
            <input type="hidden" name="userpk" id="userpk" size="8" value="{{Session::get('userpk')}}">
            <!-- <input type="hidden" name="fupk" id="fupk" size="8" value="{{Session::get('fupk')}}"> -->
            <input type="hidden" name="username" id="username" size="8" value="{{Session::get('username')}}">
            <!-- <input type="hidden" name="guser" id="guser" size="8" value="{{Session::get('gusernm')}}"> -->
            <div class="modal-content">
                <div class="border-modal" style="border:none;">
                    <div style="padding:5px; margin-top:-85px;font-size:17px;">
                        <br>
                        <div style="font-family: 'Arial';font-style: normal;font-weight: 700;font-size: 18px;line-height: 24px;">
                            Renew your password
                        </div>
                        <div style="text-align:justify; margin-top:10px; font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px;">
                            For security reasons, please renew your password. Password renewal is required every 6 months.
                        </div>
                        <br>
                        <div style="margin-bottom:5px; font-family:'Arial'; font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;">New password</div>
                        <input type="password" name="pswd" id="pswd" class="easyui-validatebox" style="width: 244px;height: 34px;" value="{{ old('pswd') }}">
                        <i class="bi bi-eye-slash" id="togglePassword" style="margin-left: -30px; cursor: pointer;" onmouseover="showpswd();" onmouseout="showpswd();"></i>
                        <div style="margin-bottom:5px; margin-top:10px; font-family:'Arial'; font-style: normal;font-weight: 700;font-size: 14px;line-height: 18px;">Confirm new password</div>
                        <input type="password" name="cnfrm" id="cnfrm" class="easyui-validatebox" style="width: 244px;height: 34px;" value="{{ old('cnfrm') }}">
                        <i class="bi bi-eye-slash" id="togglePassword2" style="margin-left: -30px; cursor: pointer;" onmouseover="showpswd2();" onmouseout="showpswd2();"></i>
                    </div>
                    <br>

                    <div id="eror1" style="text-align:justify; margin-left:15px; font-family:'Arial'; font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; color:red;display:none;">
                        <li>Password must be at least 8 characters and contain upper case, lowercase, numbers, and special characters.</li>
                    </div>
                    <div id="eror2" style="text-align:justify; margin-left:15px; font-family:'Arial'; font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; color:red;display:none;">
                        <li>New password is required.</li>
                    </div>
                    <div id="eror3" style="text-align:justify; margin-left:15px; font-family:'Arial'; font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; color:red;display:none;">
                        <li>Confirm new password is required.</li>
                    </div>
                    <div id="eror4" style="text-align:justify; margin-left:15px; font-family:'Arial'; font-style: normal;font-weight: 400;font-size: 14px;line-height: 18px; color:red;display:none;">
                        <li>Password confirmation does not match.</li>
                    </div>
                </div>
                <a style="text-decoration:none; left:150px; top:375px;" href="javascript:void(0);" class="button-cancel" onclick="back();">Cancel</a>
                <a style="text-decoration:none; left:220px; top:375px;" href="javascript:void(0);" class="button-submit" onclick="changepswd2();">Submit</a>
            </div>
        </form>
    </div>

    <div id="dialog-formok" class="easyui-dialog" closed="true" buttons="#dialog-buttons" data-options="border:false,resizable:false,modal:true,closable:false,title:false"
        style="width:425px;height:250px;padding:5px 5px;display:none;top:20px">
        <div class="modal-content">
            <div style="font-family: 'Arial';font-style: normal;font-weight: 400;font-size: 16px;line-height: 20px;margin-top:50px;margin-left: 90px;">
                <img src="{!! asset('public/css/images/centang.png') !!}" width="30" height="30" style="margin-left:100px;"><br><br>
                Password successfully updated.
            </div>
            <div style="padding:5px; margin-top:-85px;font-size: 17px;">
                <a style="text-decoration:none; left:125px; top:150px;" href="javascript:void(0)" class="button-submit" onclick="cfrm();">Continue</a>
            </div>
        </div>
    </div>
</body>

</html>