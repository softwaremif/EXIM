<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use PDF;
use App\Helpers\FormatRupiahHelper;

class SpbController extends Controller
{
	public function index()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.spb.list');
	}

	public function DetailSpb($spbpk)
	{
		if (!Session::get('userpk')) return redirect('/');
		// $dt_spb = DB::table('spb')->where('spbpk', '=', $spbpk)->first();
		$dt_spb = DB::table('spb')
			// ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->leftJoin('jang', 'jang.jangpk', '=', 'spb.jangpk')
			->leftJoin('ang', 'ang.angpk', '=', 'spb.angpk')
			->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
			->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
			->select(
				'spb.spbpk',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'spb.nobukti',
				'spb.tgl',
				'spb.att',
				'jang.jangpk',
				'jang.jangnm',
				'ang.angpk',
				'ang.angnm',
				'spb.contno',
				'spb.sealno',
				'spb.notruk',
				'fwd.fwdpk',
				'fwd.fwdnm',
				'emkl.emklpk',
				'emkl.emklnm',
				'spb.totsatuan',
				'spb.totkarton',
				'spb.totberat',
				'spb.totkubikasi',
				'spb.tips',
				DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();


		$jml_satuan = DB::table('spbdt')->where('spbpk', $spbpk)->sum('jmlsatuan');
		$jml_karton = DB::table('spbdt')->where('spbpk', $spbpk)->sum('jmlkarton');
		$jml_berat = DB::table('spbdt')->where('spbpk', $spbpk)->sum('berat');
		$jml_kubikasi = DB::table('spbdt')->where('spbpk', $spbpk)->sum('kubikasi');

		$getspbpk = DB::table('invoice')->select('invpk', 'spbpk')->where('spbpk', $spbpk)->get();
		$getspbpk2 = DB::table('invoice')->select('invpk', 'spbpk')->where('spbpk', $spbpk)->first();
		$getspbdtpk = DB::table('spbdt')->where('spbpk', $spbpk)->get();

		$UpdateSPB = DB::table('spb')
			->where('spbpk', $spbpk)
			->update([
				'totsatuan' => $jml_satuan,
				'totkarton' => $jml_karton,
				'totberat' => $jml_berat,
				'totkubikasi' => $jml_kubikasi,
			]);

		$dt_gud = DB::table('gud')->orderBy('gudnm', 'asc')->get()->toArray();
		$dt_gud = array_map(function ($item) {
			$item->gudnm = trim($item->gudnm);
			return $item;
		}, $dt_gud);

		return view('menu.spb.detail-spb', compact('dt_spb', 'dt_gud', 'jml_satuan', 'jml_karton', 'jml_berat', 'jml_kubikasi', 'getspbpk2'));
	}

	public function gudtest(Request $request)
	{
		$gudpk = $request->gudpk;

		$data = DB::table('gud')
			->where('gudpk', '=', $gudpk)
			->get();

		return response()->json($data);
	}

	public function PostCreateInvAwal(Request $request)
	{
		try {
			// $existingInvoice = DB::table('invoice')->where('spbpk', $request->spbpk)->first();

			// $getYears = DB::table('spb')->select('spbpk', DB::raw('YEAR(tgl) as year')) ->where('spbpk', $request->spbpk)->first();
			// $inputNumber = $request->noinv;
			// if (strpos($inputNumber, '/AKB/MIF/') === false) {
			// 	$formattedNoInv = $inputNumber . '/AKB/MIF/' . $getYears->year;
			// } else {
			// 	$formattedNoInv = $inputNumber;
			// }

			$existingInvoice = DB::table('invoice')->where('spbpk', $request->spbpk)->first();

			// Ambil tahun dari tabel spb
			$getYears = DB::table('spb')
				->select('spbpk', DB::raw('YEAR(tgl) as year'))
				->where('spbpk', $request->spbpk)
				->first();

			// Cari invoice terakhir yang memiliki format yang sesuai
			$lastInvoice = DB::table('invoice')
				->where('noinv', 'LIKE', '%/AKB/MIF/' . $getYears->year)
				->orderBy('noinv', 'desc')
				->first();

			// Jika ada invoice sebelumnya, ambil nomor terakhirnya
			if ($lastInvoice) {
				preg_match('/^(\d+)/', $lastInvoice->noinv, $matches);
				$lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
			} else {
				$lastNumber = 0;
			}

			// Tambahkan 1 ke nomor terakhir
			$newNumber = sprintf('%04d', $lastNumber + 1);

			// Format nomor invoice baru
			$formattedNoInv = $newNumber . '/AKB/MIF/' . $getYears->year;

			$pricemat = str_replace(',', '', $request->pricemat);
			$pricemat = number_format((float) $pricemat, 3, '.', '');

			if ($existingInvoice) {

				DB::table('invoice')
					->where('spbpk', $request->spbpk)
					->update([
						'tglinv' => $request->tgl,
						// 'noinv' => $formattedNoInv,
						// 'totqty' => $request->totsatuan,
						'cur' => $request->cur,
						'pricemat' => str_replace(',', '', $request->pricemat),
					]);
				$dt_inv = $existingInvoice->spbpk;

				// DB::table('spbdt') ->where('spbpk', $request->spbpk) ->update(['pricemat' => $pricemat]);


				$message = 'Invoice updated successfully';
			} else {

				$dt_inv = DB::table('invoice')->insertGetId([
					'spbpk' => $request->spbpk,
					'tglinv' => $request->tgl,
					'noinv' => $formattedNoInv,
					'totqty' => $request->totsatuan,
					'cur' => $request->cur,
					'pricemat' => str_replace(',', '', $request->pricemat),
					'ppn' => 12,
				]);

				// untuk mendapatkan noinv dan invpk di spbdt
				$get_invoice = DB::table('invoice')->select('invpk', 'spbpk', 'noinv')->where('spbpk', $request->spbpk)->first();

				$UpdateSPB = DB::table('spbdt')
					->where('spbpk', $request->spbpk)
					->update([
						'noinv' => $get_invoice->noinv,
						'invpk' => $get_invoice->invpk,
					]);

				// DB::table('spbdt') ->where('spbpk', $request->spbpk) ->update(['pricemat' => $pricemat]);

				$dtspbdt = DB::table('spbdt')
					->select('spbdtpk', 'noop', DB::raw('SUM(jmlsatuan) as total_jmlsatuan'))
					->where('spbpk', '=', $request->spbpk)
					->groupBy('noop')
					->get();

				$results = [];

				foreach ($dtspbdt as $noopData) {
					$cleanNoop = trim($noopData->noop);
					// $getSIG = DB::connection('mysql_sig')
					// 	->table('ord')
					// 	->select('ordpk', 'ordno', 'prodes', 'artno')
					// 	->where('ordno', 'like', "%" . $cleanNoop . "%")
					// 	->first();
					// $ordpk = $getSIG ? $getSIG->ordpk : null;
					// $prodes = $getSIG ? $getSIG->prodes : null;


					$getBdown = DB::connection('mysql_sig')
						->table('bdown')
						->select('bdownpk', 'ordpk', 'poref', 'opno', 'style')
						// ->where('ordpk', $ordpk)
						->where('opno', 'like', "%" . $cleanNoop . "%")
						->where('jenis', '=', 'DETAIL')
						->whereNotNull('poref')
						// ->orderBy('bdownpk', 'DESC')
						->first();

					$ordpk = $getBdown ? $getBdown->ordpk : null;
					$poref = $getBdown ? $getBdown->poref : null;
					$opno = $getBdown ? $getBdown->opno : null;
					$style = $getBdown ? $getBdown->style : null;
					$spbdtpk = $noopData->spbdtpk;

					$getSIG = DB::connection('mysql_sig')
						->table('ord')
						->select('ordpk', 'prodes')
						// ->where('ordno', 'like', "%" . $cleanNoop . "%")
						->where('ordpk', $ordpk)
						->first();
					$prodes = $getSIG ? $getSIG->prodes : null;

					$results[$noopData->noop] = [
						'poref' => $poref,
						'opno' => $opno,
						'prodes' => $prodes,
						'style' => $style,
						'total_jmlsatuan' => $noopData->total_jmlsatuan,
						'spbdtpk' => $spbdtpk,
					];
				}

				foreach ($results as $noop => $result) {
					// foreach ($results as $noopData => $result) {
					DB::table('invoicedt')->insert([
						'invpk' => $dt_inv,
						'mpo' => $result['poref'],
						'mprodes' => $result['prodes'],
						'mstyle' => $result['style'],
						'qty' => $result['total_jmlsatuan'],
						'spbdtpk' => $result['spbdtpk'],
					]);
				}

				$message = 'Invoice created successfully';
			}

			return response()->json([
				'success' => true,
				'dt_inv' => $dt_inv,
				'message' => $message,
				'spbpk' => $request->spbpk,
				'nobukti' => $request->nobukti,
				'noinv' => $request->noinv,
				'cur' => $request->cur,
				'pricemat' => $request->pricemat,
			]);
		} catch (\Exception $e) {
			\Log::error('Error creating or updating Invoice: ' . $e->getMessage());
			return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
		}
	}

	public function PostCreateInv(Request $request)
	{
		try {
			DB::beginTransaction();

			$existingInvoice = DB::table('invoice')->where('spbpk', $request->spbpk)->first();

			$getYears = DB::table('spb')
				->select('spbpk', DB::raw('YEAR(tgl) as year'))
				->where('spbpk', $request->spbpk)
				->first();

			// Lock baris terkait saat mengambil invoice terakhir
			$lastInvoice = DB::table('invoice')
				->where('noinv', 'LIKE', '%/AKB/MIF/' . $getYears->year)
				->orderBy('noinv', 'desc')
				->lockForUpdate() // <-- mencegah race condition
				->first();

			if ($lastInvoice) {
				preg_match('/^(\d+)/', $lastInvoice->noinv, $matches);
				$lastNumber = isset($matches[1]) ? intval($matches[1]) : 0;
			} else {
				$lastNumber = 0;
			}

			$newNumber = sprintf('%04d', $lastNumber + 1);
			$formattedNoInv = $newNumber . '/AKB/MIF/' . $getYears->year;

			$pricemat = str_replace(',', '', $request->pricemat);
			$pricemat = number_format((float) $pricemat, 3, '.', '');

			if ($existingInvoice) {
				DB::table('invoice')
					->where('spbpk', $request->spbpk)
					->update([
						'tglinv' => $request->tgl,
						'cur' => $request->cur,
						'pricemat' => $pricemat,
					]);
				$dt_inv = $existingInvoice->spbpk;

				$message = 'Invoice updated successfully';
			} else {
				$dt_inv = DB::table('invoice')->insertGetId([
					'spbpk' => $request->spbpk,
					'tglinv' => $request->tgl,
					'noinv' => $formattedNoInv,
					'totqty' => $request->totsatuan,
					'cur' => $request->cur,
					'pricemat' => $pricemat,
					'ppn' => 12,
				]);

				$get_invoice = DB::table('invoice')->select('invpk', 'spbpk', 'noinv')->where('spbpk', $request->spbpk)->first();

				DB::table('spbdt')
					->where('spbpk', $request->spbpk)
					->update([
						'noinv' => $get_invoice->noinv,
						'invpk' => $get_invoice->invpk,
					]);

				$dtspbdt = DB::table('spbdt')
					->select('spbdtpk', 'noop', DB::raw('SUM(jmlsatuan) as total_jmlsatuan'))
					->where('spbpk', '=', $request->spbpk)
					->groupBy('noop')
					->get();

				$results = [];

				foreach ($dtspbdt as $noopData) {
					$cleanNoop = trim($noopData->noop);

					$getBdown = DB::connection('mysql_sig')
						->table('bdown')
						->select('bdownpk', 'ordpk', 'poref', 'opno', 'style')
						->where('opno', 'like', "%" . $cleanNoop . "%")
						->where('jenis', '=', 'DETAIL')
						->whereNotNull('poref')
						->first();

					$ordpk = $getBdown ? $getBdown->ordpk : null;
					$poref = $getBdown ? $getBdown->poref : null;
					$opno = $getBdown ? $getBdown->opno : null;
					$style = $getBdown ? $getBdown->style : null;
					$spbdtpk = $noopData->spbdtpk;

					$getSIG = DB::connection('mysql_sig')
						->table('ord')
						->select('ordpk', 'prodes')
						->where('ordpk', $ordpk)
						->first();
					$prodes = $getSIG ? $getSIG->prodes : null;

					$results[$noopData->noop] = [
						'poref' => $poref,
						'opno' => $opno,
						'prodes' => $prodes,
						'style' => $style,
						'total_jmlsatuan' => $noopData->total_jmlsatuan,
						'spbdtpk' => $spbdtpk,
					];
				}

				foreach ($results as $noop => $result) {
					DB::table('invoicedt')->insert([
						'invpk' => $dt_inv,
						'mpo' => $result['poref'],
						'mprodes' => $result['prodes'],
						'mstyle' => $result['style'],
						'qty' => $result['total_jmlsatuan'],
						'spbdtpk' => $result['spbdtpk'],
					]);
				}

				$message = 'Invoice created successfully';
			}

			DB::commit();

			return response()->json([
				'success' => true,
				'dt_inv' => $dt_inv,
				'message' => $message,
				'spbpk' => $request->spbpk,
				'nobukti' => $request->nobukti,
				'noinv' => $formattedNoInv,
				'cur' => $request->cur,
				'pricemat' => $request->pricemat,
			]);
		} catch (\Exception $e) {
			DB::rollBack(); // Jangan lupa rollback!
			\Log::error('Error creating or updating Invoice: ' . $e->getMessage());
			return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
		}
	}


	public function gudang(Request $request)
	{
		$gudpk = $request->gudpk;

		$dt_gudang = DB::table('gud')
			// ->select('shipto.alamat', 'shipto.alamat', 'shipto.negara', 'shipto.telepon', 'shipto.kontakship', 'byr.byrnm', 'shipto.byrpk')
			// ->leftJoin('byr', 'byr.byrpk','=','shipto.byrpk')
			->where('gudpk', '=', $gudpk)
			->get();

		return response()->json($dt_gudang);
	}

	public function ListFinance()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.finance.list-finance');
	}

	public function PrintSpb($spbpk)
	{
		$dtstyle = DB::table('spb')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->leftJoin('jang', 'jang.jangpk', '=', 'spb.jangpk')
			->leftJoin('ang', 'ang.angpk', '=', 'spb.angpk')
			->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
			->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
			->select(
				'spb.spbpk',
				'spb.nobukti',
				'spb.contno',
				'spb.sealno',
				'spb.notruk',
				'spb.tgl',
				'spb.att',
				'spb.totsatuan',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'jang.jangpk',
				'jang.jangnm',
				'ang.angpk',
				'ang.angnm',
				'fwd.fwdpk',
				'fwd.fwdnm',
				'emkl.emklpk',
				'emkl.emklnm',
				DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();

		$dtspbdt = DB::table('spbdt')
			->leftJoin('byr', 'byr.byrpk', '=', 'spbdt.byrpk')
			->select('spbdt.spbdtpk', 'spbdt.spbpk', 'spbdt.noop', 'spbdt.orderno', 'spbdt.art', 'spbdt.jmlsatuan', 'spbdt.jmlkarton', 'byr.byrpk', 'byr.byrnm', 'spbdt.berat', 'spbdt.kubikasi', 'spbdt.negara', 'spbdt.smarks')
			->where('spbdt.spbpk', '=', $spbpk)
			->orderBy('spbdt.spbdtpk', 'ASC')
			->get();

		$dtdatenow = Carbon::now()->translatedFormat('l, d F Y');

		$pdf = PDF::loadview('menu.spb.print-spb', compact('dtstyle', 'dtspbdt', 'dtdatenow'));
		return $pdf->stream();
	}

	public function PrintTips($spbpk)
	{
		$dtstyle = DB::table('spb')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->select(
				'spb.spbpk',
				'spb.contno',
				'spb.notruk',
				'spb.tgl',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'spb.tips',

				DB::raw('DATE_FORMAT(spb.tgl, "%e %M %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();

		$terbilang = FormatRupiahHelper::terbilang($dtstyle->tips) . " Rupiah";

		$dtspbdt = DB::table('spbdt')
			->leftJoin('byr', 'byr.byrpk', '=', 'spbdt.byrpk')
			->select('spbdt.spbdtpk', 'spbdt.spbpk',  'byr.byrpk', 'byr.byrnm')
			->where('spbdt.spbpk', '=', $spbpk)
			->first();

		$nama_buyer = $dtspbdt->byrnm;

		$pdf = PDF::loadview('menu.spb.print-tips-stuffing', compact('dtstyle', 'terbilang', 'nama_buyer'));
		return $pdf->stream();
	}

	function ExportPdf($spbpk)
	{

		$dtstyle = DB::table('spb')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->leftJoin('jang', 'jang.jangpk', '=', 'spb.jangpk')
			->leftJoin('ang', 'ang.angpk', '=', 'spb.angpk')
			->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
			->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
			->select(
				'spb.spbpk',
				'spb.nobukti',
				'spb.contno',
				'spb.sealno',
				'spb.notruk',
				'spb.tgl',
				'spb.totsatuan',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'jang.jangpk',
				'jang.jangnm',
				'ang.angpk',
				'ang.angnm',
				'fwd.fwdpk',
				'fwd.fwdnm',
				'emkl.emklpk',
				'emkl.emklnm',
				DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();

		$dt_inv = DB::table('invoice')
			->select('spbpk', 'invpk', 'kursp', 'nokk', 'tglinv', 'cur',
			DB::raw('ROUND(invoice.pricemat, 2) as pricemat'),
			'tglkk', 
			'noinv', 
			DB::raw('DATE_FORMAT(tglinv, "%d %M %Y") as tglinv'))
			->where('spbpk', '=', $spbpk)
			->first();

		$dt_invdt = DB::table('invoicedt')
			->where('invpk', '=', $dt_inv->invpk)
			->first();

		// $dtspbdt = DB::table('spbdt')->where('spbpk', '=', $spbpk)->first();
		// $dtspbdt = DB::table('spbdt')->select('spbdtpk', 'spbpk', 'noop', 'orderno', 'art', 'jmlsatuan')->where('spbpk', '=', $spbpk)
		// ->get();
		// dd($dtspbdt->first()->noop);

		$dtspbdt = DB::table('spbdt')
			->select('spbdtpk', 'spbpk', 'noop', 'orderno', 'art', 'jmlsatuan')
			->where('spbpk', '=', $spbpk)
			->get();

		$getSpbdt2 = DB::table('spbdt')
			->select('prodes', 'buyer')
			->where('spbpk', '=', $spbpk)
			->first();

		$kursp = DB::table('invoice')
			->where('spbpk', '=', $spbpk)
			->first();

		// Kelompokkan berdasarkan 'noop'
		$grouped = $dtspbdt->groupBy('noop');

		// if ($dtspbdt) {
		// $cleanNoop = trim($dtspbdt->first()->noop);
		// $getSIG = DB::connection('mysql_sig')->table('ord')
		// 	->select('ordpk', 'ordno', 'prodes')
		// 	->where('ordno', 'like', "%" . $cleanNoop . "%")
		// 	->get();
		// } else {
		// 	// dd("No data found in spbdt.");
		// }

		$pdf = PDF::loadview('menu.spb.print-invoice-beacukai-cp2', compact('dtstyle', 'dtspbdt', 'dt_inv', 'dt_invdt', 'grouped', 'getSpbdt2', 'kursp'));
		return $pdf->stream();
	}

	function ExportPdfFinance($spbpk)
	{
		// $priceppn = 3.3855;

		$dtstyle = DB::table('spb')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->leftJoin('jang', 'jang.jangpk', '=', 'spb.jangpk')
			->leftJoin('ang', 'ang.angpk', '=', 'spb.angpk')
			->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
			->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
			->select(
				'spb.spbpk',
				'spb.nobukti',
				'spb.contno',
				'spb.sealno',
				'spb.notruk',
				'spb.tgl',
				'spb.totsatuan',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'jang.jangpk',
				'jang.jangnm',
				'ang.angpk',
				'ang.angnm',
				'fwd.fwdpk',
				'fwd.fwdnm',
				'emkl.emklpk',
				'emkl.emklnm',
				DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();

		$dt_inv = DB::table('invoice')
			->select('spbpk', 'invpk', 'nokk', 'ppn', 'priceppn', 'kursp', 'noinv', DB::raw('DATE_FORMAT(tglinv, "%d %M %Y") as tglinv'))
			->where('spbpk', '=', $spbpk)
			->first();

		$dtspbdt = DB::table('spbdt')
			->select('spbdtpk', 'spbpk', 'noop', 'orderno', 'art', 'jmlsatuan')
			->where('spbpk', '=', $spbpk)
			->get();

		$grouped = $dtspbdt->groupBy('noop');

		$loopInvDt = DB::table('invoicedt')
			->where('invpk', '=', $dt_inv->invpk)
			->get();

		// $pdf = PDF::loadview('menu.finance.print-inv-finance-cp3(modal & non create list spb)', compact('dtstyle', 'dtspbdt', 'priceppn', 'dt_inv', 'grouped',));
		$pdf = PDF::loadview('menu.finance.print-inv-finance', compact('dtstyle', 'dtspbdt', 'dt_inv', 'grouped', 'loopInvDt'));
		return $pdf->stream();
	}

	function ExportPdfFinanceDetail($spbpk)
	{
		// $priceppn = 3.3855;

		$dtstyle = DB::table('spb')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->leftJoin('jang', 'jang.jangpk', '=', 'spb.jangpk')
			->leftJoin('ang', 'ang.angpk', '=', 'spb.angpk')
			->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
			->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
			->select(
				'spb.spbpk',
				'spb.nobukti',
				'spb.contno',
				'spb.sealno',
				'spb.notruk',
				'spb.tgl',
				'spb.totsatuan',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'jang.jangpk',
				'jang.jangnm',
				'ang.angpk',
				'ang.angnm',
				'fwd.fwdpk',
				'fwd.fwdnm',
				'emkl.emklpk',
				'emkl.emklnm',
				DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();

		$dt_inv = DB::table('invoice')
			->select('spbpk', 'invpk', 'nokk', 'ppn', 
			'pricemat', 'cur',
				// DB::raw('ROUND(pricemat, 2) as pricemat')
			 'kursp', 
			 'noinv', DB::raw('DATE_FORMAT(tglinv, "%d %M %Y") as tglinv'))
			->where('spbpk', '=', $spbpk)
			->first();

		$dtspbdt = DB::table('spbdt')
			->select('spbdtpk', 'spbpk', 'noop', 'orderno', 'art', 'jmlsatuan')
			->where('spbpk', '=', $spbpk)
			->get();

		$grouped = $dtspbdt->groupBy('noop');

		$dt_invoicedt = DB::table('invoicedt')
			->where('invpk', '=', $dt_inv->invpk)
			->get();


		$pdf = PDF::loadview('menu.finance.print-inv-finance-detail', compact('dtstyle', 'dtspbdt', 'dt_inv', 'grouped', 'dt_invoicedt'));
		return $pdf->stream();
	}

	function DetailFinance($invpk)
	{
		if (!Session::get('userpk')) return redirect('/');
		$dt_invoice = DB::table('invoice')
			->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->select(
				'invoice.invpk',
				'invoice.spbpk',
				'spb.nobukti',
				'spb.totsatuan',
				'invoice.noinv',
				'invoice.nokk',
				'invoice.nofp',
				'invoice.totqty',
				'invoice.cur',
				'invoice.pricemat',
				'invoice.ppn',
				'invoice.kursp',
				'spb.spbpk',
				'gud.gudpk',
				'gud.gudnm',
				'invoice.tglinv',
				'invoice.tglkk',
				DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
			)
			->where('invoice.invpk', '=', $invpk)
			->first();

		$get_invoice = DB::table('invoice')->select('invpk', 'spbpk', 'noinv')->where('spbpk', $dt_invoice->spbpk)->first();

		$UpdateSPB = DB::table('spbdt')
			->where('spbpk', $dt_invoice->spbpk)
			->update([
				'noinv' => $get_invoice->noinv,
				'invpk' => $get_invoice->invpk,
			]);

		$UpdateQtyInv = DB::table('invoice')->where('spbpk', $dt_invoice->spbpk)->update(['totqty' => $dt_invoice->totsatuan]);

		return view('menu.finance.detail-finance', compact('dt_invoice'));
	}

	public function GetLastSpb(request $request)
	{

		// $lastId = DB::table('spb')->orderBy('spbpk', 'DESC')->value('spbpk');

		$lastId = DB::table('spb')->insertGetId([
			'tgl' => now(),
		]);

		return response()->json([
			'spbpk' => $lastId,
		]);
	}

	function AddSpb($spbpk)
	{
		$dt_spb = DB::table('spb')
			// ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
			->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
			->leftJoin('jang', 'jang.jangpk', '=', 'spb.jangpk')
			->leftJoin('ang', 'ang.angpk', '=', 'spb.angpk')
			->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
			->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
			->select(
				'spb.spbpk',
				'gud.gudpk',
				'gud.gudnm',
				'gud.alamat',
				'spb.nobukti',
				'spb.tgl',
				'spb.att',
				'jang.jangpk',
				'jang.jangnm',
				'ang.angpk',
				'ang.angnm',
				'spb.contno',
				'spb.sealno',
				'spb.notruk',
				'fwd.fwdpk',
				'fwd.fwdnm',
				'emkl.emklpk',
				'emkl.emklnm',
				'spb.totsatuan',
				'spb.totkarton',
				'spb.totberat',
				'spb.totkubikasi',
				'spb.tips',
				DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
			)
			->where('spb.spbpk', '=', $spbpk)
			->first();

		$getspbpk2 = DB::table('invoice')->select('invpk', 'spbpk')->where('spbpk', $spbpk)->first();

		$dt_gudang = DB::table('gud')->get();

		return view('menu.spb.detail-spb', compact('dt_spb', 'dt_gudang', 'getspbpk2'));
	}

	public function BackSpb(Request $request)
	{
		$spbpk = $request->input('spbpk');
		$segment3 = $request->input('segment3');

		if ($segment3 == 'add-spb') {
			DB::table('spb')->where('spbpk', $spbpk)->delete();
			DB::table('spbdt')->where('spbpk', $spbpk)->delete();
		}

		return response()->json([
			'status' => 'success',
			'spbpk' => $spbpk,
			'segment3' => $segment3,
		]);
	}

	public function ListShipment()
	{
		if (!Session::get('userpk')) return redirect('/');
		return view('menu.spb.list-shipment');
	}
}
