<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function index()
    {
        Session::flush();
        Session::regenerate();

        return view('pages.login');
    }

    public function login(Request $request)
    {
        // $nrk = $request->input('nrk');
        $user = $request->input('username');
        $pass = $request->input('password');
        $url = null;

        $users = DB::connection('mysql_akses')->table('user')
            ->leftJoin('userdt', 'userdt.userpk', '=', 'user.userpk')
            ->leftJoin('guser', 'guser.guserpk', '=', 'user.guserpk')
            ->leftJoin('grup', 'grup.gruppk', '=', 'user.gruppk')
            ->where(['user.username' => $user])
            ->where(['user.password' => $pass])
            ->where('userdt.webpk', 13)
            ->first();

        if (empty($users)) return response()->json(['success' => false, 'message' => "Informasi salah, silahkan coba lagi", 'route' => null], 400);

        if (!empty($users->userpk)) Session::put('userpk', $users->userpk);
        if (!empty($users->guserpk)) Session::put('guserpk', $users->guserpk);
        if (!empty($users->gruppk)) Session::put('gruppk', $users->gruppk);
        if (!empty($users->guserid)) Session::put('guserid', $users->guserid);
        if (!empty($users->fupkgis)) Session::put('fupkgis', $users->fupkgis);
        if (!empty($users->webpk)) Session::put('webpk', $users->webpk);
        if (!empty($users->stsakses)) Session::put('stsakses', $users->stsakses);
        if (!empty($users->login)) Session::put('login', $users->login);
        if (!empty($users->username)) Session::put('username', $users->username);
        if (!empty($users->pos)) Session::put('pos', $users->pos);
        if (!empty($users->pos)) Session::put('mif', $users->pos);
        if ($users->password) Session::put('password', $users->password);

        if ($users->stsakses == 1) {
            if ($users->fupkgis > 0) $url = "order";
            elseif ($users->guserpk == 5 || $users->guserpk == 30) $url = "order";
            // elseif ($users->datechange < now()) $url = "expired-change-date";
            else {
                // if ($users->userpk == 18) {
                //     $url = "packing";
                // } else {
                    if ($users->guserpk == 42 || $users->guserpk == 44 || $users->guserpk == 45) {
                        $url = "exim-export/shipplan/index";
                    } else {
                        $url = "order";
                    }
                // }
            };

            DB::connection('mysql_akses')->table('user')
                ->where('userpk', $users->userpk)
                ->update([
                    'datelogin' => NOW()
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $users,
            'route' => $url
        ], 200);
    }
    public function logout()
    {
        Session::flush();
        Session::regenerate();

        return redirect('/');
    }

    public function password()
    {
        return view('pages.password');
    }

    public function ExpiredChangeDate()
    {
        return view('pages.expired-changedate');
    }


    public function updtpswdb(Request $request)
    {

        $cnfrm = $request->cnfrm;
        $date = date("Y-m-d");
        $next = date("Y-m-d", strtotime("+6 month", strtotime($date)));

        $username = Session::get('username');
        DB::table('user')
            ->where('username', $username)
            ->update([
                'password' => $cnfrm,
                'datechange' => $next
            ]);

        DB::connection('mysql_sig')->table('user')
            ->where('username', $username)
            ->update([
                'password' => $cnfrm,
                'datechange' => $next
            ]);

        DB::connection('mysql_sample')->table('user')
            ->leftJoin('fu', 'fu.fupk', '=', 'user.fupk')
            ->where('fu.email', $username)
            ->update([
                'user.password' => $cnfrm,
                'user.datechange' => $next,
            ]);

        DB::connection('mysql_akses')->table('user')
            ->where('username', $username)
            ->update([
                'password' => $cnfrm,
                'datechange' => $next,
                'dateupdate' => NOW()
            ]);

        return response()->json(['success' => true, 'message' => 'Data saved successfully']);
    }

    public function updtpswd2b(Request $request)
    {

        $cnfrm = $request->input('cnfrm');
        $date = date("Y-m-d");
        $next = date("Y-m-d", strtotime("+6 month", strtotime($date)));

        $username = Session::get('username');
        DB::table('user')
            ->where('username', $username)
            ->update([
                'password' => $cnfrm,
                'datechange' => $next
            ]);

        DB::connection('mysql_sig')->table('user')
            ->where('username', $username)
            ->update([
                'password' => $cnfrm,
                'datechange' => $next
            ]);

        DB::connection('mysql_sample')->table('user')
            ->leftJoin('fu', 'fu.fupk', '=', 'user.fupk')
            ->where('fu.email', $username)
            ->update([
                'user.password' => $cnfrm,
                'user.datechange' => $next,
            ]);

        DB::connection('mysql_akses')->table('user')
            ->where('username', $username)
            ->update([
                'password' => $cnfrm,
                'datechange' => $next,
                'dateupdate' => NOW()
            ]);

        return response()->json(['success' => true, 'message' => 'Data saved successfully']);
    }
}
