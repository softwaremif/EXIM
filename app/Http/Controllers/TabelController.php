<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class TabelController extends Controller
{
	public function ListGudang()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.tabel.list-gudang');
	}

	public function ListAng()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.tabel.list-ang');
	}

	public function ListJang()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.tabel.list-jang');
	}

	public function ListFwd()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.tabel.list-fwd');
	}


	public function ListEmkl()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.tabel.list-emkl');
	}

	public function ListBuyer()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.tabel.list-buyer');
	}
}
