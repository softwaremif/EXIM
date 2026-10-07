<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Exports\InvoiceExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use PDF;

class InvoiceController extends Controller
{
	public function index()
	{
		// if (!Session::get('userpk')) return redirect('/');
		return view('menu.invoice.list-invoice');
	}

	public function listinvoice(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $userpk = Session::get('userpk');
        $searchByInput = $request->searchByInput;
        $filterByMonth = $request->bulan;
        $filterByYear = $request->tahun;
        $sortlistByDate = $request->sortlistByDate;
        $baseUrl = url('/');
		$fupk = Session::get('fupkgis');
		$userpk = Session::get('userpk');
		$funm = Session::get('login');

        if (!empty($filterByYear)) {
            $year = $filterByYear;
        } else {
            $year = $year;
        }

        if (!empty($filterByMonth)) {
            $month = $filterByMonth;
        } else {
            $month = $month;
        }


        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $invs= DB::table('invs')
            ->leftJoin('invsdt', 'invsdt.invspk', '=', 'invs.invspk')
			->leftJoin('shipto', 'shipto.shiptopk', '=', 'invs.shiptopk')
			->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
			->leftJoin('dbnotedt', 'dbnotedt.invspk', '=', 'invs.invspk')
			->leftJoin('dbnote', 'dbnote.dbnotepk', '=', 'dbnotedt.dbnotepk')
            ->select(
                DB::raw('SUM(invsdt.qty) as qty'),
                DB::raw("DATE_FORMAT(invs.tglinvs, '%d/%m/%Y') as tglinvs"),
                DB::raw("DATE_FORMAT(invs.sendate, '%d/%m/%Y') as sendate"),
				'invs.tglinvs as tglinvs2',
                'invs.noinvs',
                'byr.byrnm',
                'invs.shipno',
                'shipto.shiptonm',
				'invs.invspk',
				'invs.funm',
				'invs.shipby',
				'invs.shipfile',
				'invs.stsinvs',
				'invs.userpk',
				'dbnote.dbno'
            )
            ->whereMonth('invs.tglinvs', $month)
            ->whereYear('invs.tglinvs', $year)
			->where('invs.noinvs', '!=', '');
			if($fupk){
				$invs = $invs->where('invs.fupk', $fupk)->groupBy('invs.invspk');
			}else{
				if($userpk == 39 || $userpk == 46){
					$invs = $invs->where('invs.userpk', $userpk )->groupBy('invs.invspk');
				}else{
					$invs = $invs->groupBy('invs.invspk');
				}
			}

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(invs.noinvs,'',byr.byrnm,'',invs.shipno,'shipto.shiptonm')");
            $invs = $invs->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInput) . '%');
        }


        if (!empty($sortlistByDate)) {
            if ($sortlistByDate == 11) {
                $invs = $invs->orderBy('invs.tglinvs', 'desc');
            } else if ($sortlistByDate == 12) {
                $invs = $invs->orderBy('invs.tglinvs', 'asc');
            } else {
                $invs = $invs->orderBy('invs.tglinvs', 'desc')->orderBy('invs.noinvs', 'desc');
            }
        }

        $invs = $invs->orderBy('invs.tglinvs', 'desc')->orderBy('invs.noinvs', 'desc');

        $invs2b = $invs->get()->count();

        $offset = ($page - 1) * $rows;
        $invs = $invs->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $invs2b;
        $row = array();
        $no = 0;

        foreach ($invs as $d) {
            $no++;
			$nmra = explode('/', $d->noinvs)[0];
			$tahuna = date('Y', strtotime($d->tglinvs2)); 
			$funm = strtoupper($d->funm ?? '');

			$act = $d->stsinvs == 0
			? "<a href='" . url("/list-invoice/dtinvoice/edit/{$d->invspk}/{$d->userpk}") . "'>
					<img src='{$baseUrl}/public/css/images/Carrot.png' width='15px' height='15px' style='cursor:pointer;'>
			   </a>"
			: "";

            $invsb = DB::table('invsdt')
                ->select('*')
                ->leftJoin('invs', 'invs.invspk', '=', 'invsdt.invspk')
                ->where('invs.invspk', $d->invspk)
                ->orderBy('invsdt.invsdtpk', 'ASC')
                ->get();

            $total_dtgdt = $invsb->count() ?? 0;

			if(!empty($d->shipfile)) $style="color:#359DD9;font-weight:700;cursor:pointer"; else $style="display:none";
			if (!empty($d->shipno)) {
				$shipno = $d->shipby . " #" . $d->shipno . 
						  " <span style='" . $style . 
						  "' onclick='onresi(\"" . $d->shipfile . "\");'>View</span>";
			}else $shipno = $d->shipby.'<span style="color:red;font-weight:700;"> Waiting from purcashing! </span>';

            // $act = "<img src='$baseUrl/public/css/images/Carrot.png' onclick='detaildtg(" . $d->nolpb . ")' width='15px' height='15px'>";

            // if ($total_dtgdt > 0) {
				// $nomor = $d->noinvs;
				// $parts = explode('/', $nomor);
				// $angka = (int) $parts[0];
				// $angkaFormatted = str_pad($angka, 4, '0', STR_PAD_LEFT);
				// $newNomor = $angkaFormatted . '/' . $parts[1] . '/' . $parts[2];

                $row[] = array(
                    'no' => $no,
					'invspk' => $d->invspk,
					'noinvs' => $d->noinvs,
					'dbno' => $d->dbno,
                    'tglinvs' => $d->tglinvs,
					'sendate' => $d->sendate,
                    'byrnm' => $d->byrnm,
                    'shipno' => $shipno,
                    'shiptonm' => $d->shiptonm,
					'tqty' => $d->qty,
					'funm' => $funm,
					'stsinvs' => $d->stsinvs,
					'userpk' => $d->userpk,
                    'act' => $act
                );
            //}
        }

		//sinkron
		// $data = DB::table('invs')
		// ->select('invspk')
		// ->where('noinvs', null)
		// ->get();

		// foreach ($data as $row){
		// 	$del1 = DB::table('invs')
        //         ->where('invs', '=', $row->invspk)
        //         ->delete();
		// }

        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }

	public function DetailSpb($spbpk)
	{
		$dt_spb = DB::table('spb')
			->where('spbpk', '=', $spbpk)
			->first();
		return view('menu.spb.detail-spb', compact('dt_spb'));
	}

	// public function input_noinv(request $request)
    // {
	// 	$tahun_sekarang = date('Y');
	// 	$fupk = Session::get('fupkgis') ?? null;
		
	// 	$dt = DB::table('invs')
	// 	->select(DB::raw("SUBSTRING_INDEX(noinvs, '/', 1) as nomor"))
	// 	->whereYear('tglinvs', $tahun_sekarang)
	// 	->orderBy(DB::raw("CAST(SUBSTRING_INDEX(noinvs, '/', 1) AS UNSIGNED)"), 'desc')
	// 	->first();
		
	// 	if ($dt) {
	// 		$nourut = (int)$dt->nomor + 1;
	// 	} else {
	// 		$nourut = 1;
	// 	}

	// 	$noinv = $nourut.'/MIF/'.$tahun_sekarang;

	// 	DB::table('invs')->insert([
	// 		'noinvs' => $noinv,
	// 		'tglinvs' => NOW(),
	// 		'fupk' => $fupk
	// 	]);

    //     return response()->json([
    //         'nourut' => $nourut,
	// 		'noinv' => $noinv,
    //     ]);
    // }


	public function input_noinv(request $request)
    {
		$tahun_sekarang = date('Y');
		$fupk = Session::get('fupkgis') ?? null;
		$userpk = Session::get('userpk') ?? null;
		
		$id = DB::table('invs')->insertGetId([
			'tglinvs' => now(),
			'fupk' => $fupk,
			'userpk' => $userpk
		]);

        return response()->json([
            'invspk' => $id,
        ]);
    }

	// public function dtinvoice($mode, $noinv, $tahun)
	// {
	// 	$tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');

	// 	$shipto = DB::table('shipto')
	// 	->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
	// 	->groupBy('shipto.shiptonm')
	// 	->orderBy('shipto.shiptonm')
	// 	->get();
	
	// 	$inv = DB::table('invs')
	// 		->select(
	// 			DB::raw("SUBSTRING_INDEX(noinvs, '/', 1) as nomor"),
	// 			'invs.invspk',
	// 			'invs.tglinvs', 
	// 			'invs.noinvs',
	// 			'invs.accno',
	// 			'invs.shipno',
	// 			'shipto.shiptopk',
	// 			'shipto.shiptonm',
	// 			'shipto.alamat',
	// 			'shipto.telepon',
	// 			'shipto.kontakship',
	// 			'byr.byrnm',
	// 			'invs.sendate',
	// 			'invs.GW',
	// 			'invs.sizebox',
	// 			'invs.funm',
	// 			'invs.dbnotepk',
	// 			'invs.shipby',
	// 			'invs.packtype',
	// 			'invs.jmlpack',
	// 			'invs.shipfile'
	// 			)
	// 		->leftJoin('shipto', 'shipto.shiptopk', '=', 'invs.shiptopk')
	// 		->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
	// 		->where(DB::raw("SUBSTRING_INDEX(invs.noinvs, '/', 1)"), $noinv)
	// 		->whereYear('invs.tglinvs', $tahun)
	// 		->first();
	
	// 	$tglinvs = $inv->tglinvs ?? $tanggal;
	// 	$tglsend = $inv->sendate ?? '';

	// 	$nomor = $inv->noinvs;
	// 	$parts = explode('/', $nomor);
	// 	$angka = (int) $parts[0];
	// 	$angkaFormatted = str_pad($angka, 4, '0', STR_PAD_LEFT);
	// 	$noinvoice = $angkaFormatted . '/' . $parts[1] . '/' . $parts[2];

	// 	$invspk = $inv->invspk ?? '';
	// 	$acc = $inv->accno ?? '';
	// 	$shipno = $inv->shipno ?? '';
	// 	$shiptonm = $inv->shiptonm ?? '';
	// 	$shiptopk = $inv->shiptopk ?? '';
	// 	$alamat = $inv->alamat?? '';
	// 	$telepon = $inv->telepon?? '';
	// 	$kontakship = $inv->kontakship?? '';
	// 	$byrnm = $inv->byrnm ?? '';
	// 	$gw = $inv->GW ?? '';
	// 	$sizebox = $inv->sizebox ?? '';
	// 	$funm = $inv->funm ?? strtoupper(Session::get('login'));
	// 	$dbnotepk = $inv->dbnotepk ?? '';
	// 	$shipby = $inv->shipby ?? '';
	// 	$packtype = $inv->packtype ?? '';
	// 	$jmlpack = $inv->jmlpack ?? '';
	// 	$shipfile = $inv->shipfile ?? '';

	// 	$resi = $shipfile ? "color-view-blue pointer" : "color-view";
    //     $onresi = $shipfile ? "onresi('$shipfile');" : "";
	// 	$nilairesi = $shipfile ? "1" : "2";

	// 	return view(
	// 		'menu.invoice.dtinvoice',
	// 		compact(
	// 			'tglinvs',
	// 			'noinvoice',
	// 			'invspk',
	// 			'shipto',
	// 			'acc',
	// 			'shipno',
	// 			'shiptonm',
	// 			'shiptopk',
	// 			'alamat',
	// 			'telepon',
	// 			'kontakship',
	// 			'byrnm',
	// 			'tglsend',
	// 			'gw',
	// 			'sizebox',
	// 			'funm',
	// 			'dbnotepk',
	// 			'packtype',
	// 			'jmlpack',
    //             'shipby',
	// 			'resi',
	// 			'onresi',
	// 			'nilairesi',
    //             'shipfile'
	// 			)
	// 	);
	// }

	public function dtinvoice($mode, $invspk, $userpk)
	{
		$tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');

		$shipto = DB::table('shipto')
		->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
		->groupBy('shipto.shiptonm')
		->orderBy('shipto.shiptonm')
		->get();
	
		$inv = DB::table('invs')
			->select(
				DB::raw("SUBSTRING_INDEX(noinvs, '/', 1) as nomor"),
				'invs.invspk',
				'invs.tglinvs', 
				'invs.noinvs',
				'invs.accno',
				'invs.shipno',
				'shipto.shiptopk',
				'shipto.shiptonm',
				'shipto.alamat',
				'shipto.telepon',
				'invs.telpship',
				DB::raw("shipto.kontakship as kontakship1"),
				DB::raw("invs.kontakship as kontakship2"),
				'byr.byrnm',
				'invs.sendate',
				'invs.GW',
				'invs.sizebox',
				'invs.funm',
				'invs.dbnotepk',
				'invs.shipby',
				'invs.packtype',
				'invs.jmlpack',
				'invs.shipfile',
				'invs.volume',
				'invs.nw'
				)
			->leftJoin('shipto', 'shipto.shiptopk', '=', 'invs.shiptopk')
			->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
			->where('invs.invspk', '=', $invspk)
			->first();
	
		$tglinvs = $inv->tglinvs ?? $tanggal;
		$tglsend = $inv->sendate ?? '';

		// $nomor = $inv->noinvs;
		// $parts = explode('/', $nomor);
		// $angka = (int) $parts[0];
		// $angkaFormatted = str_pad($angka, 4, '0', STR_PAD_LEFT);
		// $noinvoice = $angkaFormatted . '/' . $parts[1] . '/' . $parts[2];

		$invspk = $inv->invspk ?? '';
		$acc = $inv->accno ?? '';
		$shipno = $inv->shipno ?? '';
		$shiptonm = $inv->shiptonm ?? '';
		$shiptopk = $inv->shiptopk ?? '';
		$alamat = $inv->alamat?? '';
		// $telepon = $inv->telepon?? '';
		$telepon1 = $inv->telepon ?? '';
		$telepon2 = $inv->telpship ?? '';
		if($telepon2 != ''){
			$telepon = $telepon2;
		}else{
			$telepon = $telepon1;
		}
		$kontakship1 = $inv->kontakship1 ?? '';
		$kontakship2 = $inv->kontakship2 ?? '';
		if($kontakship2 != ''){
			$kontakship = $kontakship2;
		}else{
			$kontakship = $kontakship1;
		}
		$byrnm = $inv->byrnm ?? '';
		$gw = $inv->GW ?? '';
		$sizebox = $inv->sizebox ?? '';
		$funm = $inv->funm ?? strtoupper(Session::get('login'));
		$dbnotepk = $inv->dbnotepk ?? '';
		$shipby = $inv->shipby ?? '';
		$packtype = $inv->packtype ?? '';
		$jmlpack = $inv->jmlpack ?? '';
		$shipfile = $inv->shipfile ?? '';
		$volume = $inv->volume ?? '';
		$nw = $inv->nw ?? '';

		$resi = $shipfile ? "color-view-blue pointer" : "color-view";
        $onresi = $shipfile ? "onresi('$shipfile');" : "";
		$nilairesi = $shipfile ? "1" : "2";

		return view(
			'menu.invoice.dtinvoice',
			compact(
				'tglinvs',
				// 'noinvoice',
				'invspk',
				'shipto',
				'acc',
				'shipno',
				'shiptonm',
				'shiptopk',
				'alamat',
				'telepon',
				'kontakship',
				'byrnm',
				'tglsend',
				'gw',
				'sizebox',
				'funm',
				'dbnotepk',
				'packtype',
				'jmlpack',
                'shipby',
				'resi',
				'onresi',
				'nilairesi',
                'shipfile',
				'volume',
                'nw',
				)
		);
	}

	public function buyer(){
		$byr = DB::table('byr')
		->select('byrpk', DB::raw('TRIM(byrnm) as byrnm'))
		->where('byrnm','!=','')
		->get();

		return response()->json($byr);
	}

	public function lookupsom(request $request){
		// $tahun = date("Y");
		$searchByInput = $request->searchByInputSOM;
		$now2 = Carbon::now('Asia/Jakarta');
        $threeMonthsAgo = $now2->copy()->subMonths(6);
        $now2Formatted = $now2->format('Y-m-d'); // Format tanggal sekarang
        $threeMonthsAgoFormatted = $threeMonthsAgo->format('Y-m-d'); // Format 3 bulan ke belakang

		$page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

		$dtstyle = DB::connection('mysql_sample')->table('status')
		->leftJoin('request', 'request.srpk', '=', 'status.srpk')
		->leftJoin('buyer', 'buyer.buyerpk', '=', 'status.buyerpk2')
		->leftJoin('user', 'user.userpk', '=', 'status.userpk')
		->leftJoin('stage', 'stage.stagepk', '=', 'status.stagepk')
		->leftJoin('progres', 'progres.progrespk', '=', 'status.progrespk')
		->leftJoin('size', 'size.statuspk', '=', 'status.statuspk')
		->select(
			'size.cw',
			'size.size',
			'status.description',
			'status.stagepk',
			'stage.stagenm',
			'status.progrespk',
			'progres.progresnm',
			'status.statuspk',
			'status.srpk',
			'status.stagepk',
			'status.date',
			'status.cekdate',
			DB::raw('SUM(size.qty) as qty'),
			'status.yy',
			'status.sam',
			'status.techpack',
			'status.pattern',
			'status.pattern2',
			'status.foto',
			'status.samplenm',
			'status.style',
			'status.season',
			'status.statuspk',
			'buyer.buyerpk',
			'buyer.buyernm',
			'status.reject',
			'status.cansts',
			'request.srno',
			'status.userpk',
			'status.category'
		)
		->whereBetween('status.date', [$threeMonthsAgoFormatted, $now2Formatted])
		->where('status.buyerpk2', '>', 0)
		->groupBy('status.statuspk');

		if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(request.srno,'',buyer.buyernm,'',status.style,'',status.description,'',stage.stagenm)");
            $dtstyle = $dtstyle->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInput) . '%');
        }

        $dtstyle = $dtstyle->orderBy('status.date', 'DESC');

		$dtstyleTotal = $dtstyle->get()->count();

        $offset = ($page - 1) * $rows;
        $dtstyle = $dtstyle->skip($offset)->take($rows)->get();

        $result = array();
        $row = array();
        
        $result['total'] = $dtstyleTotal;
        
		$index = 0;
        foreach ($dtstyle as $d) {

			$srno = 'SR '.$d->srno ?? '';

            $row[] = array(
                'userpk' => $d->userpk,
                'statuspk' => $d->statuspk,
                'srpk' => $d->srpk,
                'stagepk' => $d->stagepk,
                'reject' => $d->reject,
                'cansts' => $d->cansts,
                'dstyle' => $d->style,
                'index' => $index,
                'style' => $d->style,
                'stagenm' => $d->stagenm,
				'byrnm' => $d->buyernm,
				'srno' => $srno,
				'description' => $d->description,
				'season' => $d->season,
				'qty' => $d->qty,
				'color' => $d->cw,
				'size' => $d->size,
				'category'=>$d->category,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
	}

	public function get_lookup_som(Request $request)
    {
        $invspk = $request->input('invspk');
        $rows = $request->input('rows');

        $count = count($rows);
        $misal = [];
        $processedpodtpk = [];

        for ($i = 0; $i < $count; $i++) {
            $rowpodtpk = $rows[$i]['statuspk'] ?? null;
            $processedpodtpk[] = $rowpodtpk;

            $dtgdt_lpb1 = DB::table('invsdt')->insert([
                'invspk' => $invspk,
                'style' => $rows[$i]['style'] ?? null,
                'itemnm' => $rows[$i]['category'] ?? null,
				'deskripsi' => $rows[$i]['description'] ?? null,
                'samplenm' => $rows[$i]['stagenm'] ?? null,
                'sizenm' => $rows[$i]['size'] ?? null,
                'qty' => $rows[$i]['qty'] ?? null,
				'colnm' => $rows[$i]['color'] ?? null,
				'statuspk' => $rows[$i]['statuspk'] ?? null,
				'nos'=> 'U/D',
            ]);
            $misal[] = "Processed row " . ($i + 1);
        }

        $invsdt = DB::table('invsdt')
            ->select('*')
            ->leftJoin('invs', 'invs.invspk', '=', 'invsdt.invspk')
            ->where('invs.invspk', $invspk)
            ->orderBy('invsdt.invsdtpk', 'ASC')
            ->get();

        $total_dtgdt = $invsdt->count() ?? 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Data processed successfully',
            'invspk' => $invspk,
            'rows' => $rows,
            'misal' => $misal,
            'processedDtgpk' => $processedpodtpk,
            'total_dtgdt' => $total_dtgdt,
        ]);
    }

	public function lookupgis(request $request){
		// $tahun = date("Y");
		$searchByInput = $request->searchByInputGIS;
		$now2 = Carbon::now('Asia/Jakarta');
        $threeMonthsAgo = $now2->copy()->subMonths(12);
        $now2Formatted = $now2->format('Y-m-d'); // Format tanggal sekarang
        $threeMonthsAgoFormatted = $threeMonthsAgo->format('Y-m-d'); // Format 3 bulan ke belakang

		$page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

		$dtstyle = DB::connection('mysql_sig')->table('ord')
		->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
		->leftJoin('fu', 'fu.fupk', '=', 'ord.fupk')
		->leftJoin('user', 'user.userpk', '=', 'ord.userpk')
		->leftJoin('bdown', 'bdown.ordpk', '=', 'ord.ordpk')
		->select(
			'byr.byrnm', 
			'fu.funm', 
			'ord.fupk', 
			'ord.srno', 
			'ord.ordpk', 
			'ord.ordno', 
			'ord.artno', 
			'ord.prodes', 
			'ord.stage',
			'ord.season',
			'ord.deldate', 
			'ord.codate', 
			'ord.statuspk',
			DB::raw("GROUP_CONCAT(DISTINCT TRIM(bdown.pono) ORDER BY bdown.pono ASC) as pono"),
			DB::raw("GROUP_CONCAT(DISTINCT TRIM(bdown.color) ORDER BY bdown.color ASC) as color")
		)
		->whereBetween('ord.datecreate', [$threeMonthsAgoFormatted, $now2Formatted])
		->where('ord.datecreate', '>', 0)
		->where('ord.fupk',  Session::get('fupkgis'))
		->groupBy('ord.ordpk');

		if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(ord.ordno,'',byr.byrnm,'',ord.artno,'',ord.srno,'',ord.stage,'',ord.season,'',ord.prodes)");
            $dtstyle = $dtstyle->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInput) . '%');
        }

        $dtstyle = $dtstyle->orderBy('ord.datecreate', 'DESC');

		$dtstyleTotal = $dtstyle->get()->count();

        $offset = ($page - 1) * $rows;
        $dtstyle = $dtstyle->skip($offset)->take($rows)->get();

        $result = array();
        $row = array();
        
        $result['total'] = $dtstyleTotal;
        
		$index = 0;
        foreach ($dtstyle as $d) {

			if($d->deldate > 0 || $d->deldate != '0000-00-00'){
				$deldate = \Carbon\Carbon::parse($d->deldate)->format('d/m/Y');
			}else{
				$deldate = '-';
			}
			$x2b = DB::connection('mysql_sig')->table('bdown')
			->select(DB::raw("SUM(tot) as tot"))
			->where('ordpk', $d->ordpk)
			->where('jenis', 'DETAIL')
			->where('color', '!=', '')
			->groupBy('ordpk')
			->first();

			$qty = $x2b->tot ?? 0;
			if($qty>0){
				$row[] = array(
					'byrnm' => $d->byrnm,
					'funm' => $d->funm,
					'fupk' => $d->fupk,
					'srno' => $d->srno,
					'ordpk'=> $d->ordpk, 
					'pono' => $d->pono,
					'ordno' => $d->ordno, 
					'artno' => $d->artno,
					'prodes' => $d->prodes, 
					'stage' => $d->stage,
					'season' => $d->season,
					'deldate'=> $deldate,
					'codate'=> $d->codate,
					'statuspk'=>$d->statuspk,
					'qty' => $qty,
					'color' => $d->color
				);
			}
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
	}

	public function get_lookup_gis(Request $request)
    {
        $invspk = $request->input('invspk');
        $rows = $request->input('rows');

        $count = count($rows);
        $misal = [];
        $processedpodtpk = [];

        for ($i = 0; $i < $count; $i++) {
            $rowpodtpk = $rows[$i]['statuspk'] ?? null;
            $processedpodtpk[] = $rowpodtpk;

            $dtgdt_lpb1 = DB::table('invsdt')->insert([
                'invspk' => $invspk,
                'style' => $rows[$i]['artno'] ?? null,
                'itemnm' => $rows[$i]['prodes'] ?? null,
				'deskripsi' => $rows[$i]['prodes'] ?? null,
                'samplenm' => $rows[$i]['stage'] ?? null,
                // 'sizenm' => $rows[$i]['size'] ?? null,
                'qty' => $rows[$i]['qty'] ?? null,
				'colnm' => $rows[$i]['color'] ?? null,
				'statuspk' => $rows[$i]['statuspk'] ?? null,
				'ordpk' => $rows[$i]['ordpk'] ?? null,
				'pono' => $rows[$i]['pono'] ?? null,
				'nos'=> 'U/D',
            ]);
            $misal[] = "Processed row " . ($i + 1);
        }

        $invsdt = DB::table('invsdt')
            ->select('*')
            ->leftJoin('invs', 'invs.invspk', '=', 'invsdt.invspk')
            ->where('invs.invspk', $invspk)
            ->orderBy('invsdt.invsdtpk', 'ASC')
            ->get();

        $total_dtgdt = $invsdt->count() ?? 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Data processed successfully',
            'invspk' => $invspk,
            'rows' => $rows,
            'misal' => $misal,
            'processedDtgpk' => $processedpodtpk,
            'total_dtgdt' => $total_dtgdt,
        ]);
    }

	public function lookupinv(Request $request)
    {
		$today = Carbon::today()->toDateString();
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $userpk = Session::get('userpk');
        $searchByInput = $request->searchByInputINV;
        $baseUrl = url('/');
		$fupk = Session::get('fupkgis');
		$userpk = Session::get('userpk');
		$funm = Session::get('login');

		$page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $invs= DB::table('invs')
            ->leftJoin('invsdt', 'invsdt.invspk', '=', 'invs.invspk')
			->leftJoin('shipto', 'shipto.shiptopk', '=', 'invs.shiptopk')
			->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
            ->select(
                DB::raw('SUM(invsdt.qty) as qty'),
                DB::raw("DATE_FORMAT(invs.tglinvs, '%d/%m/%Y') as tglinvs"),
                DB::raw("DATE_FORMAT(invs.sendate, '%d/%m/%Y') as sendate"),
				'invs.tglinvs as tglinvs2',
                'invs.noinvs',
                'byr.byrnm',
                'invs.shipno',
                'shipto.shiptonm',
				'invs.invspk',
				'invs.funm',
				'invs.shipby',
				'invs.shipfile',
				'invs.stsinvs',
            )
            // ->whereMonth('invs.tglinvs', $month)
            // ->whereYear('invs.tglinvs', $year)
			->where('invs.noinvs', '!=', '')
			->where('invs.fupk', $fupk)->groupBy('invs.invspk');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(invs.noinvs,'',byr.byrnm,'',invs.shipno,'shipto.shiptonm')");
            $invs = $invs->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInput) . '%');
        }

        $invs = $invs->orderBy('invs.tglinvs', 'desc')->orderBy('invs.noinvs', 'desc');

        $invs2b = $invs->get()->count();

        $offset = ($page - 1) * $rows;
        $invs = $invs->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $invs2b;
        $row = array();
        $no = 0;

        foreach ($invs as $d) {
            $no++;
			$nmra = explode('/', $d->noinvs)[0];
			$tahuna = date('Y', strtotime($d->tglinvs2)); 
			$funm = strtoupper($d->funm ?? '');


            $invsb = DB::table('invsdt')
                ->select('*')
                ->leftJoin('invs', 'invs.invspk', '=', 'invsdt.invspk')
                ->where('invs.invspk', $d->invspk)
                ->orderBy('invsdt.invsdtpk', 'ASC')
                ->get();

            $total_dtgdt = $invsb->count() ?? 0;

			if(!empty($d->shipfile)) $style="color:#359DD9;font-weight:700;cursor:pointer"; else $style="display:none";
			if (!empty($d->shipno)) {
				$shipno = $d->shipby . " #" . $d->shipno . 
						  " <span style='" . $style . 
						  "' onclick='onresi(\"" . $d->shipfile . "\");'>View</span>";
			}else $shipno = '<span style="color:red;font-weight:700;">Waiting from purcashing!</span>';

                $row[] = array(
                    'no' => $no,
					'invspk' => $d->invspk,
					'noinvs' => $d->noinvs,
                    'tglinvs' => $d->tglinvs,
					'sendate' => $d->sendate,
                    'byrnm' => $d->byrnm,
                    'shipno' => $shipno,
                    'shiptonm' => $d->shiptonm,
					'tqty' => $d->qty,
					'funm' => $funm,
					'stsinvs' => $d->stsinvs,
                );
        }

        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }

	public function get_lookup_invsdt(Request $request)
    {
        $invspk = $request->input('invspk');
        $rows = $request->input('rows');

        $count = count($rows);
        $misal = [];
        $processedpodtpk = [];

        for ($i = 0; $i < $count; $i++) {
            $rowpodtpk = $rows[$i]['statuspk'] ?? null;
            $processedpodtpk[] = $rowpodtpk;

            $dtgdt_lpb1 = DB::table('invsdt')->insert([
                'invspk' => $invspk,
                'style' => $rows[$i]['style'] ?? null,
                'itemnm' => $rows[$i]['itemnm'] ?? null,
				'deskripsi' => $rows[$i]['deskripsi'] ?? null,
                'samplenm' => $rows[$i]['samplenm'] ?? null,
                'sizenm' => $rows[$i]['sizenm'] ?? null,
                'qty' => $rows[$i]['qty'] ?? null,
				'colnm' => $rows[$i]['colnm'] ?? null,
				'statuspk' => $rows[$i]['statuspk'] ?? null,
				'ordpk' => $rows[$i]['ordpk'] ?? null,
				'pono' => $rows[$i]['pono'] ?? null,
				'price' => $rows[$i]['price2'] ?? null,
				'jmlflyer' => $rows[$i]['jmlflyer'] ?? null,
				'hscode' => $rows[$i]['hscode'] ?? null,
				'nos' => $rows[$i]['nos'] ?? null,
				'fabnm' => $rows[$i]['fabnm'] ?? null,
				'unitnm' => $rows[$i]['unitnm'] ?? null,
            ]);
            $misal[] = "Processed row " . ($i + 1);
        }

        $invsdt = DB::table('invsdt')
            ->select('*')
            ->leftJoin('invs', 'invs.invspk', '=', 'invsdt.invspk')
            ->where('invs.invspk', $invspk)
            ->orderBy('invsdt.invsdtpk', 'ASC')
            ->get();

        $total_dtgdt = $invsdt->count() ?? 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Data processed successfully',
            'invspk' => $invspk,
            'rows' => $rows,
            'misal' => $misal,
            'processedDtgpk' => $processedpodtpk,
            'total_dtgdt' => $total_dtgdt,
        ]);
    }


	public function detail_invoice($invspk, Request $request){
		$page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

		$dtinvs = DB::table('invsdt')
		->where('invspk', $invspk)
		->orderBy('invsdtpk', 'asc');

		$dtinvs2b = $dtinvs->get()->count();

        $offset = ($page - 1) * $rows;
        $dtinvs = $dtinvs->skip($offset)->take($rows)->get();

		$result = array();
        $result['total'] = $dtinvs2b;
        $row = array();

		foreach ($dtinvs as $d) {
			$price = $d->price;
			$qty = $d->qty;
			// if($d->nos == "T/D"){
			// 	$amount = "0.01";
			// }else{
				$amount = (float)$price * (float)$qty;
			//}

			$price2 = '$'.number_format($price, 3, '.', ',');;
			$amount2 = '$'.number_format($amount, 3, '.', ',');;
			$row[] = array(
                'style' => $d->style,
				'ordpk' => $d->ordpk,
				'statuspk' => $d->ordpk,
                'pono' => $d->pono,
                'itemnm' => $d->itemnm,
                'samplenm' => $d->samplenm,
                'colnm' => $d->colnm,
                'fabnm' => $d->fabnm,
                'sizenm' => $d->sizenm,
				'qty' => $qty,
				'jmlflyer' => $d->jmlflyer,
				'price' => $price2,
				'price2' => $d->price,
				'unitnm' => $d->unitnm,
				'deskripsi' => $d->deskripsi,
                'invsdtpk' => $d->invsdtpk,
                'invspk' => $d->invspk,
				'nos' => $d->nos,
				'amount' => $amount2,
				'hscode' => $d->hscode
            );
		}

		$result = array_merge($result, array('rows' => $row));
        return json_encode($result);
	}

	public function update_list_dtinvoice(Request $request)
    {
        $invsdtpk = $request->input('invsdtpk');
        // $nos = $request->input('nos') ?? '';
        $style = $request->input('style') ?? '';
        $pono = $request->input('pono') ?? '';
        $itemnm = $request->input('itemnm') ?? '';
		$samplenm = $request->input('samplenm') ?? '';
        $colnm = $request->input('colnm') ?? '';
		$fabnm = $request->input('fabnm') ?? '';
		$sizenm = $request->input('sizenm') ?? '';
		$qty = $request->input('qty') ?? 0;
        $jmlflyer = $request->input('jmlflyer') ?? 0;
        $price = $request->input('price') ?? 0;
		$hscode = $request->input('hscode') ?? '';

		$price2 = str_replace('$', '', $price);

		// if($price2 > 0.01){
		// 	$nos = "U/D"; 
		// }else if($price2 <= 0.01){
		// 	$nos = "T/D"; 
		// }else{
		// 	$nos = "U/D"; 
		// }
        
        // $tglfaktur = $request->input('tglfaktur');
        // $tglfaktur = Carbon::createFromFormat('d/m/Y', $tglfaktur)->format('Y-m-d');

        // $qtybalance = max(0, $qtypo - $qtydtg);

        DB::table('invsdt')
            ->where('invsdtpk', $invsdtpk)
            ->update([
                // 'nos' => $nos,
                'style' => $style,
                'pono' => $pono,
                'itemnm' => $itemnm,
                'colnm' => $colnm,
				'samplenm' => $samplenm,
                'fabnm' => $fabnm,
                'sizenm' => $sizenm,
                'qty' => $qty,
				'jmlflyer' => $jmlflyer,
				'price' => $price2,
				'hscode' => $hscode
            ]);

        return response()->json([
            'respon' => 'ini sukses',
            'invsdtpk' => $invsdtpk,
          	// 'nos' => $nos,
			'style' => $style,
			'pono' => $pono,
			'itemnm' => $itemnm,
			'samplenm' => $samplenm,
			'colnm' => $colnm,
			'fabnm' => $fabnm,
			'sizenm' => $sizenm,
			'qty' => $qty,
			'jmlflyer' => $jmlflyer,
			'price' => $price,
        ]);
    }

	public function insert_list_dtinvoice($invspk, Request $request)
    {
        $nos = 'U/D';
        $style = $request->input('style') ?? '';
        $pono = $request->input('pono') ?? '';
        $itemnm = $request->input('itemnm') ?? '';
		$samplenm = $request->input('samplenm') ?? '';
        $colnm = $request->input('colnm') ?? '';
		$fabnm = $request->input('fabnm') ?? '';
		$sizenm = $request->input('sizenm') ?? '';
		$qty = $request->input('qty') ?? 0;
        $jmlflyer = $request->input('jmlflyer') ?? 0;
        $price = $request->input('price') ?? 0;
		$hscode = $request->input('hscode') ?? '';

		$price2 = str_replace('$', '', $price);
        
        // $tglfaktur = $request->input('tglfaktur');
        // $tglfaktur = Carbon::createFromFormat('d/m/Y', $tglfaktur)->format('Y-m-d');

        // $qtybalance = max(0, $qtypo - $qtydtg);

        DB::table('invsdt')->insert([
				'invspk' => $invspk,
                'nos' => $nos,
                'style' => $style,
                'pono' => $pono,
                'itemnm' => $itemnm,
                'colnm' => $colnm,
				'samplenm' => $samplenm,
                'fabnm' => $fabnm,
                'sizenm' => $sizenm,
                'qty' => $qty,
				'jmlflyer' => $jmlflyer,
				'price' => $price2,
				'hscode' => $hscode
            ]);

        return response()->json([
            'respon' => 'ini sukses'
        ]);
    }

	public function shipto_combobox($byrpk, Request $request){
		$shipto = DB::table('shipto')
		->select('shipto.shiptonm', 'shipto.shiptopk')
		->leftJoin('byr', 'byr.byrpk','=','shipto.byrpk')
		->where('shipto.byrpk', '=', $byrpk)
		->groupBy('shipto.shiptonm')
		->orderBy('shipto.shiptonm', 'asc')
		->get();

		return response()->json($shipto);
	}

	public function shipto(Request $request){
		$shiptopk = $request->shiptopk;

		$shipto = DB::table('shipto')
		->select('shipto.shiptonm', 'shipto.alamat', 'shipto.negara', 'shipto.telepon', 'shipto.kontakship', 'byr.byrnm', 'shipto.byrpk')
		->leftJoin('byr', 'byr.byrpk','=','shipto.byrpk')
		->where('shipto.shiptopk','=', $shiptopk)
		->get();

		return response()->json($shipto);
	}

    public function submit_invs(Request $request)
    {
        $invspk = $request->input('invspk');
		$guserpk = $request->input('guserpk');
		$userpk = $request->input('userpk');
		if($guserpk==4 || $userpk == 39 || $userpk == 46 || $userpk == 24){
			$shiptopk = $request->input('shipto');
		}else{
			$shiptopk = $request->input('shiptopkb');
		}
		$tglsend = $request->input('tglsend') ?? '00/00/0000';
		$tglsend = Carbon::createFromFormat('d/m/Y', $tglsend)->format('Y-m-d');
		$kontakship = $request->input('to') ?? '';
		$telpship = $request->input('telp') ?? '';
        $acc = $request->input('acc') ?? '';
		$from = $request->input('from') ?? '';
		$gw = $request->input('gw') ?? '';
		$sizebox = $request->input('sizebox') ?? '';
		$brand = $request->input('brand') ?? '';
		$packtype = $request->input('packtype') ?? '';
		$jmlpack = $request->input('qtypack') ?? '';
		$model = $request->input('model') ?? '';
		$nilai = $request->input('nilai_btn') ?? '';
		//purchasing
		$shipno = $request->input('shipno') ?? '';
		$kurir = $request->input('kurir') ?? '';
		$nw = $request->input('nw') ?? '';
		$volume = $request->input('volume') ?? '';

		//NOMOR INVOICE
		$tahun_sekarang = date('Y');

		$dt = DB::table('invs')
		->lockForUpdate()
		->select(DB::raw("SUBSTRING_INDEX(noinvs, '/', 1) as nomor"))
		->whereYear('tglinvs', $tahun_sekarang)
		->orderBy(DB::raw("CAST(SUBSTRING_INDEX(noinvs, '/', 1) AS UNSIGNED)"), 'desc')
		->first();
		
		if ($dt) {
			$nourut = (int)$dt->nomor + 1;
		} else {
			$nourut = 1;
		}

		$noinv = $nourut.'/MIF/'.$tahun_sekarang;


		$updateData = [
			'accno' => $acc,
			'shiptopk' => $shiptopk,
			'sendate' => $tglsend,
			'GW' => $gw,
			'sizebox' => $sizebox,
			'funm' => $from,
			'brand' => $brand,
			'packtype' => $packtype,
			'jmlpack' => $jmlpack,
			'shipby' => $kurir,
			'shipno' => $shipno,
			'kontakship' => $kontakship,
			'telpship' => $telpship,
		];
		
		if ($guserpk == '4' || $userpk == '39' || $userpk == '46' || $userpk == '24') {
			if ($model == 'input' && $nilai == 'submit') {
				$updateData['noinvs'] = $noinv;
			}
		} else if($guserpk == '7'){
			$updateData = [
				'volume' => $volume,
				'nw' => $nw,
			];
		}else {
			$updateData = [
				'shipno' => $shipno,
			];
		}
		
		DB::table('invs')->where('invspk', $invspk)->update($updateData);

        return response()->json([
			'invspk'=> $invspk,
			'acc' => $acc,
			'shiptopk' => $shiptopk,
			'shipno' => $shipno,
			// 'tglinv' => $tglinv,
			'tglsend' => $tglsend,
			'GW' => $gw,
			'sizebox' => $sizebox,
			'funm' => $from,
			'kurir' => $kurir,
			'packtype' => $packtype,
			'jmlpack' => $jmlpack,
			'shipby' => $kurir,
			'volume' => $volume,
			'nw' => $nw,
        ]);
    }
    public function back_listinvs(Request $request)
    {
        $invspk = $request->input('invspk');
        $segment3 = $request->input('segment3');

        if ($segment3 == 'input') {
            DB::table('invs')->where('invspk', $invspk)->delete();
            DB::table('invsdt')->where('invspk', $invspk)->delete();
        }

        return response()->json([
            'status' => 'success',
            'invspk' => $invspk,
            'segment3' => $segment3,
        ]);
    }

	public function insert_shipto(Request $request)
    {
        $shipinvspk = $request->input('shipinvspk');
		$byrpk = $request->input('shipbyr');
        $shiptonm = $request->input('shipnm');
        $alamat = $request->input('shipalamat');
		$telepon = $request->input('shipphone');
		$negara = $request->input('shipnegara');
		$kontakship = $request->input('shipkontak');

		$shiptopk = DB::table('shipto')->insertGetId([
			'shiptonm' => $shiptonm,
			'byrpk' => $byrpk,
			'alamat' => $alamat,
			'telepon' => $telepon,
			'negara' => $negara,
			'kontakship' => $kontakship,
		]);

        DB::table('invs')
            ->where('invspk', $shipinvspk)
            ->update([
                'shiptopk' => $shiptopk,
            ]);
		
			$byrnm = DB::table('shipto')
			->select('byr.byrnm')
			->leftJoin('byr', 'byr.byrpk', '=', 'shipto.shiptopk')
			->where('shipto.shiptopk', '=', $shiptopk)
			->first();

        return response()->json([
			'status' => 'success',
			'shiptonm' => $shiptonm,
			'shiptopk' => $shiptopk,
			'byrpk' => $byrpk,
			'alamat' => $alamat,
			'telepon' => $telepon,
            'negara' => $negara,
            'kontakship' => $kontakship,
			'byrnm' => $byrnm
        ]);
    }

	public function edit_shipto(Request $request)
    {
		$shiptopk = $request->input('shiptopkedt');
		$shiptonm = $request->input('shiptonmedt');
        $alamat = $request->input('shipalamatedt');
        $negara= $request->input('shipnegaraedt');
		$telepon = $request->input('shipphoneedt');
		$kontakship = $request->input('shipkontakedt');
		$byrpk = $request->input('shipbyredt');

        DB::table('shipto')
            ->where('shiptopk', $shiptopk)
            ->update([
				'shiptonm' => $shiptonm,
				'alamat' => $alamat,
				'telepon' => $telepon,
				'negara' => $negara,
				'kontakship' => $kontakship,
				'byrpk' => $byrpk
            ]);
		
			$byrnm = DB::table('shipto')
			->select('byr.byrnm')
			->leftJoin('byr', 'byr.byrpk', '=', 'shipto.shiptopk')
			->where('shipto.shiptopk', '=', $shiptopk)
			->first();

        return response()->json([
			'status' => 'success',
			'shiptopk' => $shiptopk,
			'shiptonm' => $shiptonm,
			'alamat' => $alamat,
			'telepon' => $telepon,
            'negara' => $negara,
            'kontakship' => $kontakship,
			'byrnm' => $byrnm,
			'byrpk' => $byrpk,
        ]);
    }

	public function delete_invsdt(Request $request)
    {
        // Ambil array ID dari permintaan
        $ids = $request->input('ids');

        // Validasi untuk memastikan data diterima adalah array
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'msg' => "<div class='text-infomerah' style='width:415px;'>" . $ids . "Invalid data received &nbsp; &#x2573;</div>",
            ]);
        }

        // Proses pembaruan untuk setiap ID
        foreach ($ids as $id) {
            $result = DB::table('invsdt')
                ->where('invsdtpk', '=', $id)
                ->delete();

            if (!$result) {
                // Jika salah satu pembaruan gagal, hentikan dan kembalikan respons error
                return response()->json([
                    'success' => false,
                    'msg' => "<div class='text-infomerah' style='width:415px;'>Cannot process replace data material for ID: $id &nbsp; &#x2573;</div>",
                ]);
            }
        }

        // Jika semua pembaruan berhasil
        return response()->json([
            'success' => true,
            'msg' => "<div class='text-infohijau' style='width:415px;'>Delete successfully. &nbsp; &#10004;</div>",
        ]);
    }

	public function update_copy(Request $request)
    {
		$invsdtpk = $request->invsdtpk;
		$invspk = $request->invspk;
        $data = DB::table('invsdt')
            ->where('invsdtpk', $invsdtpk)
            ->first();

        if ($data) {
			$insert = DB::table('invsdt')->insert([
				'invspk' => $invspk ?? 0,
				'nos' => $data->nos ?? '',
				'style' => $data->style ?? '',
				'pono' => $data->pono ?? '',
				'itemnm' => $data->itemnm ?? '',
				'samplenm' => $data-> samplenm ?? '',
				'colnm' => $data->colnm ?? '',
				'sizenm' => $data->sizenm ?? '',
				'qty' => $data->qty ?? '',
				'price' => $data->price ?? '',
				'fabnm' => $data->fabnm ?? '',
				'jmlflyer' => $data->jmlflyer ?? 0
			]);

			return response()->json([
				'success' => true,
				'invspk' => $invspk ?? 0,
				'nos' => $data->nos ?? '',
				'style' => $data->style ?? '',
				'pono' => $data->pono ?? '',
				'itemnm' => $data->itemnm ?? '',
				'samplenm' => $data-> samplenm ?? '',
				'colnm' => $data->colnm ?? '',
				'sizenm' => $data->sizenm ?? '',
				'qty' => $data->qty ?? '',
				'price' => $data->price ?? '',
				'fabnm' => $data->fabnm ?? '',
				'jmlflyer' => $data->jmlflyer ?? 0
			]);
		}

		return response()->json(['error' => 'No data found'], 404);
    }

	public function get_invoice_cetak_pdf($invspk, $nilai)
    {
		$dtinvs = DB::table('invsdt')
		->select('*', DB::raw('(qty * price) as amount'))
		->where('invspk', $invspk)
		->orderBy('invsdtpk', 'asc')
		->get();

		$invs= DB::table('invs')
		->leftJoin('shipto', 'shipto.shiptopk', '=', 'invs.shiptopk')
		->leftJoin('byr', 'byr.byrpk', '=', 'shipto.byrpk')
		->select(
			DB::raw("DATE_FORMAT(invs.tglinvs, '%d/%m/%Y') as tglinvs"),
			DB::raw("DATE_FORMAT(invs.sendate, '%d/%m/%Y') as sendate"),
			'invs.tglinvs as tglinvs2',
			'invs.noinvs',
			'byr.byrnm',
			'invs.shipno',
			'invs.shipby',
			'shipto.shiptonm',
			'shipto.alamat',
			'shipto.telepon',
			'invs.telpship',
			DB::raw("shipto.kontakship as kontakship1"),
			DB::raw("invs.kontakship as kontakship2"),
			'shipto.negara',
			'invs.invspk',
			'invs.accno',
			'invs.funm',
			'invs.GW',
			'invs.sizebox',
			'invs.packtype',
			'invs.jmlpack',
			'invs.volume',
			'invs.nw'
		)
		->where('invs.invspk', $invspk)
		->first();

		if ($invs) {
			$noinvs = $invs->noinvs;
		} else {
			$noinvs = $invs->invspk;
		}

		$kontakship1 = $invs->kontakship1 ?? '';
		$kontakship2 = $invs->kontakship2 ?? '';
		if($kontakship2 != ''){
			$kontakship = $kontakship2;
		}else{
			$kontakship = $kontakship1;
		}

		$telepon1 = $invs->telepon ?? '';
		$telepon2 = $invs->telpship ?? '';
		if($telepon2 != ''){
			$telepon = $telepon2;
		}else{
			$telepon = $telepon1;
		}

        $pdf = PDF::loadView('menu.invoice.invoice_pdf', ['dtinvs'=>$dtinvs  ,'telepon'=>$telepon, 'kontakship'=>$kontakship, 'invs'=>$invs, 'nilai'=>$nilai])
                ->setPaper('A4', 'potrait');

        return $pdf->stream('invoice_'.$noinvs.'.pdf');
    }

	public function  get_invoice_cetak_excel($tahun, $bulan)
	{
		return Excel::download(new InvoiceExport($tahun, $bulan), 'Jurnal_Penjualan.xlsx');
		
	}

	public function getBdown(Request $request)
    {
        $ord = '37526';//$request->input('ord'); // Mengambil parameter dari request

        // Query database dengan Query Builder Laravel
        $data = DB::connection('mysql_sig')->table('bdown')
            ->select('*')
            ->where('ordpk', $ord)
            ->where(function ($query) {
                $query->whereIn('jenis', ['DETAIL', 'HEADER', 'FOOTER1'])
                    ->where(function ($subQuery) {
                        $subQuery->where('jenis', '<>', 'DETAIL')
                            ->orWhere(function ($detailQuery) {
                                $detailQuery->where('jenis', 'DETAIL')
                                    ->where('country', '<>', '')
                                    ->where('color', '<>', '');
                            });
                    });
            })
            ->groupBy(DB::raw("CASE WHEN bdown.jenis = 'HEADER' THEN bdown.jenis ELSE bdown.bdownpk END"))
            ->orderByRaw("
                CASE
                    WHEN jenis = 'HEADER' THEN 1
                    WHEN jenis = 'DETAIL' THEN 2
                    WHEN jenis = 'FOOTER1' THEN 3
                END ASC, 
                CASE
                    WHEN jenis = 'DETAIL' THEN bdownpk
                    WHEN jenis = 'FOOTER1' THEN sumby
                    WHEN jenis = 'HEADER' THEN nour
                END ASC
            ")
            ->get();

        $result = [];
        $k = -1;
		$br = $data->count();

        // Loop untuk memproses data
        foreach ($data as $row) {
            $jenis = trim($row->jenis);
            $po = '';
            $op = '';
            $style = '';
            $color = '';
            $gac = '';
            $tot = '';

            if ($jenis === 'HEADER') {
                $po = $op = $style = $color = $gac = $tot = '';
                for ($i = 1; $i <= 40; $i++) {
                    ${"s$i"} = $row->{"s$i"};
                }
            } elseif ($jenis === 'DETAIL') {
                $po = $row->pono;
                $op = $row->opno;
                $style = $row->style;
                $color = $row->color;
                $gac = $row->gac === '0000-00-00' || $row->gac === '' ? '' : $row->gac;
                for ($i = 1; $i <= 40; $i++) {
                    ${"s$i"} = $row->{"s$i"} < 1 ? '' : $row->{"s$i"};
                }
                $tot = $row->tot < 1 ? '' : $row->tot;
            } elseif ($jenis === 'FOOTER1') {
                $po = $op = $style = $color = '';
                $gac = $row->sumdata !== '' ? $row->sumdata : '<b>TOTAL</b>';
                for ($i = 1; $i <= 40; $i++) {
                    ${"s$i"} = $row->{"s$i"} < 1 ? '' : $row->{"s$i"};
                }
                $tot = $row->tot < 1 ? '' : $row->tot;
            }

            $k++;

			if ($jenis === 'HEADER') {
				for ($i = 1; $i <= 40; $i++) {
					${"s{$i}a"} = ${"s$i"};
                }
				$tota= $tot;
			}else{
				for ($i = 1; $i <= 40; $i++) {
					${"s{$i}a"}  = "<input type='text' name='s{$i}a' id='s{$i}a{$i}' value='${"s$i"}' onblur='test();' readonly>";
                }
				$tota="<input type='text' name='tota' id='tota1' value='$tot' onblur='test();' style='font-weight: bold;' readonly>";
			}
            $result[] = [
                'po' => $po,
                'op' => $op,
                'style' => $style,
                'color' => $color,
                'gac' => $gac,
				's1'=>$s1a,
				's2'=>$s2a,
				's3'=>$s3a,
				's4'=>$s4a,
				's5'=>$s5a,
				's6'=>$s6a,
				's7'=>$s7a,
				's8'=>$s8a,
				's9'=>$s9a,
				's10'=>$s10a,
				's11'=>$s11a,
				's12'=>$s12a,
				's13'=>$s13a,
				's14'=>$s14a,
				's15'=>$s15a,
				's16'=>$s16a,
				's17'=>$s17a,
				's18'=>$s18a,
				's19'=>$s19a,
				's20'=>$s20a,
				's21'=>$s21a,
				's22'=>$s22a,
				's23'=>$s23a,
				's24'=>$s24a,
				's25'=>$s25a,
				's26'=>$s26a,
				's27'=>$s27a,
				's28'=>$s28a,
				's29'=>$s29a,
				's30'=>$s30a,
				's31'=>$s31a,
				's32'=>$s32a,
				's33'=>$s33a,
				's34'=>$s34a,
				's35'=>$s35a,
				's36'=>$s36a,
				's37'=>$s37a,
				's38'=>$s38a,
				's39'=>$s39a,
				's40'=>$s40a,
				'tot'=>$tota,
				'br'=>$br,
				// 'sizes' => array_map(function ($index) use ($row) {
				// 	return $row->{"s$index"};
				// }, range(1, 40)),
            ];
        }

        return response()->json($result);
    }

	public function bulan()
	{
		$months = [];
		$monthNames = [
			1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
			5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
			9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
		];
	
		foreach ($monthNames as $key => $name) {
			$months[] = [
				'bulan' => $name,
				'value' => $key,
			];
		}
	
		return response()->json($months);
	}

	// public function bulan()
	// {
	// 	$months = [];
	// 	$monthNames = [
	// 		1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
	// 		5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
	// 		9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
	// 	];
	
	// 	$currentMonth = date('n'); // Dapatkan bulan saat ini (1-12)
	
	// 	foreach ($monthNames as $key => $name) {
	// 		$months[] = [
	// 			'bulan' => $name,
	// 			'value' => $key,
	// 			'selected' => ($key == $currentMonth) // Tandai bulan sekarang
	// 		];
	// 	}
	
	// 	return response()->json($months);
	// }

	public function resi(Request $request)
    {
        $invspk = $request->invspkresi;
		// $shipby = $request->kurirresi;
		$shipno = $request->awbresi;
 
        if ($request->hasFile('resi')) {
            $file = $request->file('resi');

            if ($file->isValid()) {
                $filename = $invspk . '_RESI_' . time() . '_' . $file->getClientOriginalName();

                try {
                    $file->move(public_path('resi'), $filename);

                    $updinvs = DB::table('invs')
                        ->where('invspk', $invspk)
                        ->update([
                            'shipfile' => $filename,
							// 'shipby' => $shipby,
							'shipno' => $shipno
                        ]);

                    return response()->json(['success' => true, 'message' => '', 'nilairesi' => 1, 'file' => $filename]);
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());

                    return response()->json(['success' => false, 'message' => 'File upload failed.']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Uploaded file is not valid.']);
            }
        }

        return response()->json(['success' => false, 'message' => 'No file was uploaded.']);
    }

	public function status_td(Request $request)
    {
        // Ambil array ID dari permintaan
        $ids = $request->input('ids');
		$nilai = $request->input('nilai');

        // Validasi untuk memastikan data diterima adalah array
        if (!is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'msg' => "<div class='text-infomerah' style='width:415px;'>" . $ids . "Invalid data received &nbsp; &#x2573;</div>",
            ]);
        }

        // Proses pembaruan untuk setiap ID
        foreach ($ids as $id) {
			if($nilai == 1){
			$result = DB::table('invsdt')
				->where('invsdtpk', $id)
				->update([
					'nos' => 'T/D',
					'price' => '0.01'
				]);
			}else{
				$result = DB::table('invsdt')
				->where('invsdtpk', $id)
				->update([
					'nos' => 'U/D'
					// 'price' => '0.01'
				]);
			}

            if (!$result) {
                // Jika salah satu pembaruan gagal, hentikan dan kembalikan respons error
                return response()->json([
                    'success' => false,
                    'msg' => "<div class='text-infomerah' style='width:415px;'>Data ini tidak dapat diproses : $id &nbsp; &#x2573;</div>",
                ]);
            }
        }

        // Jika semua pembaruan berhasil
        return response()->json([
            'success' => true,
            'msg' => "<div class='text-infohijau' style='width:415px;'>Data has been change successfully. &nbsp; &#10004;</div>",
        ]);
    }

	public function cancelinvs(Request $request)
    {
        $invspk = $request->input('invspk');
		$sts = $request->input('sts');

            $result = DB::table('invs')
			->where('invspk', $invspk)
			->update([
				'stsinvs' => $sts
			]);

            if (!$result) {
                // Jika salah satu pembaruan gagal, hentikan dan kembalikan respons error
                return response()->json([
                    'success' => false,
                    'msg' => "<div class='text-infomerah' style='width:415px;'>Cannot process replace data material for ID: $invspk &nbsp; &#x2573;</div>",
                ]);
            }

			if($sts == 1){
				$msg = "<div class='text-infohijau' style='width:415px;'>Cancel process successfully. &nbsp; &#10004;</div>";
			}else{
				$msg = "<div class='text-infohijau' style='width:415px;'>Uncancel process successfully. &nbsp; &#10004;</div>";
			}

        // Jika semua pembaruan berhasil
        return response()->json([
            'success' => true,
            'msg' => $msg,
        ]);
    }
}
