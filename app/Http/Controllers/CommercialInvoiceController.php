<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\Shipper;
use App\Models\Dbnote;
use App\Models\Dbnotedt;
use App\Models\Invs;
use App\Models\Invsdt;
use App\Models\Bill;
use App\Models\Byr;
use App\Models\Shipto;
use PDF;
use App\Exports\CinvoiceExport;

class CommercialInvoiceController extends Controller
{
    public function index()
	{
		return view('menu.cinvoice.index');
	}

	public function indexPerbaikan()
	{
		return view('menu.cinvoice.index-perbaikan');
	}

	public function create($dbnotepk)
	{
		$alamat = Shipper::alamat(null);
		$dbnote = Dbnote::find($dbnotepk);
		$lastDbnotedt = Dbnotedt::where('dbnotepk', $dbnotepk)->orderBy('dbnotedtpk', 'desc')->first();
		$lastInvs = !empty($lastDbnotedt) ? Invs::find($lastDbnotedt->invspk) : null;

		return view('menu.cinvoice.detail', ['alamat' => $alamat, 'dbnote' => $dbnote, 'invs' => $lastInvs,  'byrpk' => null]);
	}

	public function createPerbaikan($dbnotepk)
	{
		$alamat = Shipper::alamat(null);
		$dbnote = Dbnote::find($dbnotepk);
		$lastDbnotedt = Dbnotedt::where('dbnotepk', $dbnotepk)->orderBy('dbnotedtpk', 'desc')->first();
		$lastInvs = !empty($lastDbnotedt) ? Invs::find($lastDbnotedt->invspk) : null;

		return view('menu.cinvoice.detail-perbaikan', ['alamat' => $alamat, 'dbnote' => $dbnote, 'invs' => $lastInvs,  'byrpk' => null]);
	}

	public function edit($dbnotepk)
	{
		$alamat = Shipper::alamat(null);
		$dbnote = Dbnote::find($dbnotepk);
		if (empty($dbnote)) return redirect()->route('debitnote.index');
		$lastDbnotedt = Dbnotedt::where('dbnotepk', $dbnotepk)->orderBy('dbnotedtpk', 'desc')->first();
		$lastInvs = !empty($lastDbnotedt) ? Invs::find($lastDbnotedt->invspk) : null;
        $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
        $shiptopkarr = Invs::whereIn('invspk', $invspkarr)->pluck('shiptopk')->toArray();
        $byrpk = Shipto::whereIn('shiptopk', $shiptopkarr)->value('byrpk');

		return view('menu.cinvoice.detail', ['alamat' => $alamat, 'dbnote' => $dbnote, 'invs' => $lastInvs, 'byrpk' => $byrpk]);
	}

	public function store()
	{
		// $dbnote = Dbnote::find(1);
		$dbnote = new Dbnote;
		$dbnote->tgldb = now();
		$dbnote->save();
		
		return redirect()->route('commercial-invoice.create', ['dbnotepk' => $dbnote->dbnotepk]);
	}

	public function storePerbaikan()
	{
		// $dbnote = Dbnote::find(1);
		$dbnote = new Dbnote;
		$dbnote->tgldb = now();
		$dbnote->save();
		
		return redirect()->route('commercial-invoice.create-perbaikan', ['dbnotepk' => $dbnote->dbnotepk]);
	}

	public function pdf($dbnotepk, Request $request) 
    {
		$data = array();
        
		$dbnote = Dbnote::find($dbnotepk);
		$bill = !empty($dbnote->billpk) ? Bill::find($dbnote->billpk) : null;
		$byr = !empty($dbnote->byrpk) ? Byr::find($dbnote->byrpk) : null;
					
		$invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
		$invspkarrInvsdtpk = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();

		// $invsdt = Invsdt::whereIn('invspk', $invspkarr)->get();
		$invsdtPerbaikan = Invsdt::whereIn('invspk', $invspkarrInvsdtpk)->get();

		$sxspo = Invsdt::whereIn('invspk', $invspkarr)->pluck('pono')->unique()->toArray();
		$invs = count($invspkarr) > 0 ? Invs::whereIn('invspk', $invspkarr)->whereNotNull('shiptopk')->orderBy('invspk', 'desc')->first() : null;
		$shipto = !empty($invs) ? Shipto::find($invs->shiptopk) : null;
		$shipper = !empty($invs->funm) ? Shipper::alamat($invs->funm) : Shipper::alamat(null);
		if (empty($byr)) $byr = !empty($shipto) ? Byr::find($shipto->byrpk) : null;
		$address = "{$shipper['company']}\n{$shipper['st']}\n{$shipper['city']}\n{$shipper['country']}\n{$shipper['telp']}";

		// $sxspo = Invsdt::whereIn('invspk', $invspkarr)->pluck('pono')->unique()->toArray();

        $sxspoArray = array_map(function($item) {
            return explode(',', $item);
        }, $sxspo);
        
        $sxspoMerged = array_merge(...($sxspoArray ?: [[]]));
        $pobyr = implode(', ', $sxspoMerged) ?: '';
        
        $row = array();
		$result = array();
		$tamount = 0;
		// foreach ($invsdt as $d) {
		foreach ($invsdtPerbaikan as $d) {
			$dtinvs = Invs::find($d->invspk);

			if (!in_array(strval($d->pono), $sxspo)) array_push($sxspo, strval($d->pono));
			$amount = ($d->price ?? 0) * ($d->qty ?? 0);

			$row[] = array(
				'po' => preg_replace('/\s*,\s*/', ', ', $d->pono),
				'invsdtpk' => $d->invsdtpk,
				'style' => $d->style,
				'color' => $d->colnm,
				'desc' => $d->deskripsi,
				'product' => $d->itemnm,
				'destination' => $shipto->negara,
				'senddate' => !empty($dtinvs->sendate) ? Carbon::parse($dtinvs->sendate)->format('M d, y') : null,
				'qty' => $d->qty,
				'unit' => 'PCS',
				'price' => "$".($d->price ?? 0),
				'amount' => "$$amount",
				'shipno' => $dtinvs->shipno,
			);
			$tamount += floatval($amount);
		}
		$result['row'] = $row;
        if (!empty($dbnote->bankcharge)) $tamount += $dbnote->bankcharge;
		$result['tamount'] = $tamount;

		$data['dbnote'] = $dbnote;
		$data['bill'] = $bill;
		$data['billnm'] = $bill->billnm;
		$data['byr'] = $byr;
		$data['invs'] = $invs;
		$data['shipto'] = $shipto;
		$data['shipper'] = $address;
		$data['sxspo'] = !empty($dbnote->pobyr) ? $dbnote->pobyr : (count($sxspo) > 0 ? implode(', ',$sxspo) : null);
		$data['pobyr'] = !empty($dbnote->pobyr) ? $dbnote->pobyr : $pobyr;
		$data['paydate'] = $dbnote->paydate;
		$data['invsdt'] = $result;
		$data['judul'] = $request->judul ?? 'COMMERCIAL INVOICE';
		$data['judulinv'] = $request->judulinv ?? 'INVOICE NO';

		$pdf = PDF::loadView('menu.cinvoice.pdf', $data)->setPaper('A4', 'potrait');

		return $pdf->stream('filename.pdf');
	}

	public function editPerbaikan($dbnotepk)
	{
		$alamat = Shipper::alamat(null);
		$dbnote = Dbnote::find($dbnotepk);
		if (empty($dbnote)) return redirect()->route('debitnote.index');
		$lastDbnotedt = Dbnotedt::where('dbnotepk', $dbnotepk)->orderBy('dbnotedtpk', 'desc')->first();
		$lastInvs = !empty($lastDbnotedt) ? Invs::find($lastDbnotedt->invspk) : null;
        $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
        $shiptopkarr = Invs::whereIn('invspk', $invspkarr)->pluck('shiptopk')->toArray();
        $byrpk = Shipto::whereIn('shiptopk', $shiptopkarr)->value('byrpk');

		return view('menu.cinvoice.detail-perbaikan', ['alamat' => $alamat, 'dbnote' => $dbnote, 'invs' => $lastInvs, 'byrpk' => $byrpk]);
	}

	public function excel($dbnotepk)
	{
		$dbnote     = Dbnote::find($dbnotepk);
		$bill       = !empty($dbnote->billpk) ? Bill::find($dbnote->billpk) : null;
		$byr        = !empty($dbnote->byrpk) ? Byr::find($dbnote->byrpk) : null;
		$invspkarr  = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
		$invspkarrInvsdtpk = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();

		// $invsdt = Invsdt::whereIn('invspk', $invspkarr)->get();
		$invsdtPerbaikan = Invsdt::whereIn('invspk', $invspkarrInvsdtpk)->get();

		$sxspo      = Invsdt::whereIn('invspk', $invspkarr)->pluck('pono')->unique()->toArray();
		$invs       = count($invspkarr) > 0
						? Invs::whereIn('invspk', $invspkarr)->whereNotNull('shiptopk')->orderBy('invspk', 'desc')->first()
						: null;
		$shipto     = !empty($invs) ? Shipto::find($invs->shiptopk) : null;
		$shipper    = !empty($invs->funm) ? Shipper::alamat($invs->funm) : Shipper::alamat(null);
		if (empty($byr)) $byr = !empty($shipto) ? Byr::find($shipto->byrpk) : null;

		$address = "{$shipper['company']}\n{$shipper['st']}\n{$shipper['city']}\n{$shipper['country']}\n{$shipper['telp']}";

		$sxspoArray  = array_map(fn($item) => explode(',', $item), $sxspo);
		$sxspoMerged = array_merge(...($sxspoArray ?: [[]]));
		$pobyr       = implode(', ', $sxspoMerged) ?: '';

		$row     = [];
		$tamount = 0;

		// foreach ($invsdt as $d) {
		foreach ($invsdtPerbaikan as $d) {

			$dtinvs = Invs::find($d->invspk);

			if (!in_array(strval($d->pono), $sxspo)) {
				array_push($sxspo, strval($d->pono));
			}

			$amount = ($d->price ?? 0) * ($d->qty ?? 0);

			$row[] = [
				'po'          => preg_replace('/\s*,\s*/', ', ', $d->pono),
				'invsdtpk'    => $d->invsdtpk,
				'style'       => $d->style,
				'color'       => $d->colnm,
				'desc'        => $d->deskripsi,
				'product'     => $d->itemnm,
				'destination' => $shipto->negara ?? '',
				'senddate'    => !empty($dtinvs->sendate)
									? Carbon::parse($dtinvs->sendate)->format('M d, y')
									: null,
				'qty'         => $d->qty,
				'unit'        => 'PCS',
				'price'       => $d->price ?? 0,
				'amount'      => $amount,
				'shipno'      => $dtinvs->shipno,
			];

			$tamount += floatval($amount);
		}

		if (!empty($dbnote->bankcharge)) $tamount += $dbnote->bankcharge;

		$result = [
			'row'     => $row,
			'tamount' => $tamount,
		];

		$dbnote->buyer   = $byr->nm ?? ($byr->nama ?? '');
		$dbnote->sxspono = !empty($dbnote->pobyr)
							? $dbnote->pobyr
							: (count($sxspo) > 0 ? implode(', ', $sxspo) : null);

		$judul    = request()->query('judul', 'COMMERCIAL INVOICE');
		$judulinv = request()->query('judulinv', 'INVOICE NO');

		return (new CinvoiceExport($dbnote, $result, $address, $pobyr, $judul, $judulinv, $shipto, $bill))
			->download('commercial-invoice-' . $dbnotepk . '.xlsx');
	}
}
