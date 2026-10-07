<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

//use Illuminate\Validation\Rule;
//use Session;
//use Validator;
use Carbon\Carbon;
use App\Models\Mesin;
use App\Models\JMesin;
use App\Models\Notran;
use App\Imports\MesinImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Linep;


class SimpanController extends Controller
{
	public function index()
	{
		return view('login');
	}

	

	function add_mesin(Request $request) {
		$mifValue = Session::get('mif');

		$data_sewa = DB::table('mesin')
			->where('mesinid', '>=', 300000)
			->orderBy('mesinid', 'DESC')
			->limit(1)
			->first();

		$jmesin = DB::table('jmesin')
			->where('jmesinpk', $request->jmesinpk)
			->first();

		$linep = Linep::find($request->linepk);

		$lastMesinIdSewa = !empty($data_sewa) ? intval($data_sewa->mesinid) + 1 : 300000;

		$lastMesinIdMif = null;
		$data_notran = Notran::where('tblnm', 'mesin');

		$sewa = $request->ownership_sewa;
		
		if ($mifValue == 1 && $sewa != '1') {
			$data_notran = $data_notran->select('mif1')->first();
			$lastMesinIdMif = $data_notran->mif1;
		} else if ($mifValue == 2 && $sewa != '1') {
			$data_notran = $data_notran->select('mif2')->first();
			$lastMesinIdMif = $data_notran->mif2;
		}

		$Ambil = $sewa == '1' ? $lastMesinIdSewa : $lastMesinIdMif;

		$status = 5;

		if ($linep->linenm == 'STOCK1' || $linep->linenm == 'STOCK2') $status = 1;

		$data = new Mesin;
		$data['mesinid'] = $Ambil;
		$data['mif'] = $mifValue;
		$data['supplier'] = $request->supplier;
		$data['mesinnm'] = $jmesin->jmesinnm;
		$data['pobeli'] = $request->pobeli;
		$data['curpk'] = $request->curpk;
		$data['tipe'] = $request->tipe;
		$data['jmesinpk'] = $request->jmesinpk;
		$data['merkpk'] = $request->merkpk;				
		$data['noseri'] = $request->noseri;				
		$data['dateinput'] = $request->dateinput;				
		$data['tahun'] = $request->tahun;	
		$data['status'] = $status;	
		$data['linepk'] = $request->linepk;			
		$data['deskripsi'] = $request->description;
		$data['dateinput'] = Carbon::now();
		$data['dateupdt'] = Carbon::now();
		$data['aktif'] = $request->ownership_sewa;

		$data->save();

		if($mifValue == 1 && $sewa != '1'){
			DB::table('notran')
			->where('tblnm', 'mesin')
			->update([
				'mif1' => $Ambil + 1,
			]);
		}

		if($mifValue == 2 && $sewa != '1'){
			DB::table('notran')
			->where('tblnm', 'mesin')
			->update([
				'mif2' => $Ambil + 1,
			]);
		}

		// return redirect()->back();

		return response()->json([
			'message' => 'Data Berhasil Ditambahkan',
			'mif' => $mifValue,
			'idsewalast' => $data_sewa->mesinid,
			'sewa' => $request->ownership_sewa,
			'lastMesinIdSewa' => $lastMesinIdSewa,
			'lastMesinIdMif' => $lastMesinIdMif,
			'ambil' => $Ambil,
		], 200);
	}



	public function upload_excel(Request $request){
		$loggedInUser = DB::table('user')
		->where('userpk', '=', Session::get('userpk'))
		->select('mif')
		->first();

		if (empty($loggedInUser)) {
			$loggedInUser = DB::table('mekanik')
			->where('mekanikpk', '=', Session::get('mekanikpk'))
			->select('mif')
			->first();
		}

		if (empty($loggedInUser)) return response()->json(['success' => false, 'message' => "You don't have access to take this action"], 400);

		$mifValue = Session::get('mif');

		$fileb = $request->file('uploadmesin');

		if (empty($fileb)) return response()->json(['success' => false, 'message' => "Please upload file first"], 400);

		$originalName = $fileb->getClientOriginalName();
		$timestamp = now()->format('Ymd-H:i:s');
		$nama_file = $timestamp . '-mif('.$mifValue.')' . '-' . $originalName;

		$tujuan_upload = 'public/upload/mesin-excel';
		$fileb->move($tujuan_upload, $nama_file);

		$import = new MesinImport($mifValue);

		Excel::import($import, public_path('/upload/mesin-excel/'.$nama_file));

		$Amblikterakhir = DB::table('mesin')->where('mif', Session::get('mif'))->whereNull('aktif')->orderBy('mesinpk', 'DESC')->take(1)->get();
		$firstMesinId = $Amblikterakhir->first()->mesinid;

		if (Session::get('mif') == 1) {
			DB::table('notran')
			->where('tblnm', 'mesin')
			->update([
				'mif1' => $firstMesinId + 1,
			]);
		} else if (Session::get('mif') == 2) {
			DB::table('notran')
			->where('tblnm', 'mesin')
			->update([
				'mif2' => $firstMesinId + 1,
			]);
		}

		return response()->json([
			'success' => true,
			'message' => 'Machine successfully saved.',
			'loggedInUser' => $loggedInUser,
			'mifValue' => $mifValue
		], 200);
	}


	public function get_mesin($mesinid)
    {
 		$mesin = DB::table('mesin')
		 ->where('mesinid', $mesinid)
		 ->leftJoin('merk', 'merk.merkpk', '=', 'mesin.merkpk')
		 ->leftJoin('jmesin', 'jmesin.jmesinpk', '=', 'mesin.jmesinpk')
		 ->leftJoin('linep', 'linep.linepk', '=', 'mesin.linepk')
		 ->leftJoin('dep', 'linep.deppk', '=', 'dep.deppk')
		 ->leftJoin('cur', 'cur.curpk', '=', 'mesin.curpk')
		 ->select(
			'mesin.mesinid',
			'mesin.tipe',
			'mesin.mesinnm',
			'mesin.mesinpk',
			'mesin.noseri',
			'mesin.tahun',
			'mesin.status',
			'mesin.mesinid',
			'mesin.dateinput',
			'jmesin.jmesinnm',
			'merk.merknm',
			'linep.linenm',
			'dep.dep',
			)
		 ->first();

        return view('layout.modal.detail_modal_mesin', compact('mesin'));
    }

	public function get_mesin2($mesinid)
    {
 		$mesin = DB::table('mesin')
        ->where('mesinid', $mesinid)->first();

        return view('layout.modal.detail_modal_mesin', compact('mesin'));
    }
}
