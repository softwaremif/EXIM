<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\Invoicedt;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SpbdtImport;
use Str;

class ApiSpbController extends Controller
{
    public function GetTotSemua($spbpk)
    {
        $getpcs = DB::table('spbdt')
            ->select('jmlsatuan', 'jmlkarton', 'berat', 'kubikasi')
            ->where('spbpk', $spbpk)
            ->get();

        $totalpcs = 0;
        $totalkar = 0;
        $totalbrt = 0;
        $totalkub = 0;

        foreach ($getpcs as $row) {
            $totalpcs += $row->jmlsatuan;
            $totalkar += $row->jmlkarton;
            $totalbrt += $row->berat;
            $totalkub += $row->kubikasi;
        }

        return response()->json([
            'success' => true,
            'totalpcs' => $totalpcs,
            'totalkar' => $totalkar,
            'totalbrt' => $totalbrt,
            'totalkub' => $totalkub,
            'data' => $getpcs
        ]);
    }

    public function updateTotSemua(Request $request)
    {
        $request->validate([
            'spbpk' => 'required|numeric',
            'totpcs' => 'required|numeric',
            'totkar' => 'required|numeric',
            'totbrt' => 'required|numeric',
            'totkub' => 'required|numeric'
        ]);

        $updated = DB::table('spb')
            ->where('spbpk', $request->spbpk)
            ->update([
                'totsatuan' => $request->totpcs, 
                'totkarton' => $request->totkar, 
                'totberat' => $request->totbrt, 
                'totkubikasi' => $request->totkub
            ]);

        if ($updated) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau tidak berubah.']);
        }
    }

    // public function GetTotAll($spbpk)
    // {
    //     $gettotal = DB::table('spbdt')
    //     ->select('spbdtpk','spbpk','jmlsatuan', 'jmlkarton', 'berat', 'kubikasi')
    //     ->where('spbpk', $spbpk)
    //     ->get();
    //     $totalPcs = $gettotal->sum('jmlsatuan');
    //     $totalKar = $gettotal->sum('jmlkarton');
    //     $totalBrt = $gettotal->sum('berat');
    //     $totalKub = $gettotal->sum('kubikasi');

    //     return response()->json([
    //         'success' => true,
    //         'total_pcs' => number_format($totalPcs, 0, '.', ','),
    //         'total_kar' => number_format($totalKar, 0, '.', ','),
    //         'total_brt' => number_format($totalBrt, 0, '.', ','),
    //         'total_kub' => number_format($totalKub, 0, '.', ','),
    //         'data' => $gettotal
    //     ]);
    // }

    // public function updateTotPcs(Request $request)
    // {
    //     $request->validate([
    //         'spbpk' => 'required|numeric',
    //         'totpcs' => 'required|numeric'
    //     ]);

    //     $updated = DB::table('spb')
    //         ->where('spbpk', $request->spbpk)
    //         ->update(['totsatuan' => $request->totpcs]);

    //     if ($updated) {
    //         return response()->json(['success' => true]);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau tidak berubah.']);
    //     }
    // }

    // public function updateTotKar(Request $request)
    // {
    //     $request->validate([
    //         'spbpk' => 'required|numeric',
    //         'totkar' => 'required|numeric'
    //     ]);

    //     $updated = DB::table('spb')
    //         ->where('spbpk', $request->spbpk)
    //         ->update(['totkarton' => $request->totkar]);

    //     if ($updated) {
    //         return response()->json(['success' => true]);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau tidak berubah.']);
    //     }
    // }

    // public function updateTotBrt(Request $request)
    // {
    //     $request->validate([
    //         'spbpk' => 'required|numeric',
    //         'totbrt' => 'required|numeric'
    //     ]);

    //     $updated = DB::table('spb')
    //         ->where('spbpk', $request->spbpk)
    //         ->update(['totberat' => $request->totbrt]);

    //     if ($updated) {
    //         return response()->json(['success' => true]);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau tidak berubah.']);
    //     }
    // }

    // public function updateTotKub(Request $request)
    // {
    //     $request->validate([
    //         'spbpk' => 'required|numeric',
    //         'totkub' => 'required|numeric'
    //     ]);

    //     $updated = DB::table('spb')
    //         ->where('spbpk', $request->spbpk)
    //         ->update(['totkubikasi' => $request->totkub]);

    //     if ($updated) {
    //         return response()->json(['success' => true]);
    //     } else {
    //         return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau tidak berubah.']);
    //     }
    // }
    
    public function GetSpb(Request $request)
    {
        $tahun = date("Y");
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $filterByMonth = $request->filterByMonth ?? date('m');
        $filterByYear = $request->filterByYear;
        $sortlistByDate = $request->sortlistByDate;

        if (!empty($filterByYear))
            $tahun = $filterByYear;

        $data_spb = DB::table('spb')
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
                'jang.jangpk',
                'jang.jangnm',
                'ang.angpk',
                'ang.angnm',
                'fwd.fwdpk',
                'fwd.fwdnm',
                'emkl.emklpk',
                'emkl.emklnm',
                DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
            )->whereNotNull('spb.nobukti');


        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(spb.nobukti, ''),
                COALESCE(spb.contno, ''),
                COALESCE(spb.sealno, ''),
                COALESCE(spb.notruk, ''),
                COALESCE(CAST(spb.totsatuan AS CHAR), ''),
                COALESCE(gud.gudnm, ''),
                COALESCE(jang.jangnm, ''),
                COALESCE(ang.angnm, ''),
                COALESCE(fwd.fwdnm, ''),
                COALESCE(emkl.emklnm, '')
            )");

            $data_spb = $data_spb->where($concatenatedValue, 'like', '%' . $searchByInput . '%');
        }

        // Filter berdasarkan tahun
        if (!empty($filterByYear)) {
            $data_spb = $data_spb->whereYear('spb.tgl', '=', $filterByYear);
        } else {
            // Jika tidak ada filter tahun, gunakan tahun saat ini
            $data_spb = $data_spb->whereYear('spb.tgl', '=', $tahun);
        }

        // Filter berdasarkan bulan
        if (!empty($filterByMonth)) {
            $data_spb = $data_spb->whereMonth('spb.tgl', '=', $filterByMonth);
        }

        // Pengurutan berdasarkan tanggal
        if ($sortlistByDate == 11) {
            $data_spb = $data_spb->orderBy('spb.nobukti', 'desc');
        } elseif ($sortlistByDate == 12) {
            $data_spb = $data_spb->orderBy('spb.nobukti', 'asc');
        } else {
            $data_spb = $data_spb->orderBy('spb.nobukti', 'desc'); // Default pengurutan
        }

        $AllDataSpb = $data_spb->get();

        $offset = ($page - 1) * $rows;
        $data_spb = $data_spb->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataSpb->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_spb as $d) {

            // $dt_spbdt = DB::table('spbdt')
            // ->where('spbdtpk', $d->spbpk)
            // ->selectRaw('spbpk, SUM(jmlsatuan) as total_jmlsatuan')
            // ->groupBy('spbpk')
            // ->first();

            // $dt_invoice = DB::table('invoice')
            // ->where('spbpk', $d->spbpk)
            // ->select('spbpk', 'noinv')
            // ->orderBy('spbpk', 'DESC')
            // ->first();

            // if ($dt_invoice) {
            //     $noinv = $dt_invoice->noinv; // Ambil nilai noinv
            // } else {
            //     $noinv = null; // Jika tidak ditemukan, set ke null
            // }

            $noinv = DB::table('invoice')
                ->where('spbpk', $d->spbpk)
                ->orderBy('spbpk', 'DESC')
                ->value('noinv');

            $spbdt = DB::table('spbdt')
                ->where('spbpk', $d->spbpk)
                ->groupBy('spbpk')
                ->sum('jmlsatuan');

            $row[] = array(



                'spbpk' => $d->spbpk,
                'index' => $index,
                'tanggal' => $d->tanggal,
                'noinv' => $noinv ?? '-',
                // 'nobukti' => $d->nobukti,
                'nobukti' => '<a href="' . route('detail.spb', $d->spbpk) . '" target="_blank" style="color:#000; cursor:pointer; text-decoration:none;">' . $d->nobukti . '</a>',

                // 'totalpcs' => number_format($dt_spbdt->jmlsatuan, 0, ',', '.'),
                // 'totalpcs' => isset($dt_spbdt) && is_object($dt_spbdt) && isset($dt_spbdt->jmlsatuan)
                //     ? number_format($dt_spbdt->total_jmlsatuan, 0, ',', '.')
                //     : '0',

                // 'totalsatuan' => $d->totsatuan,
                // 'totalsatuan' => number_format($d->totsatuan, 0, '.', ','),

                'totalsatuan' => number_format($spbdt, 0, '.', ','),
                'gudang' => $d->gudnm,
                'jangkutan' => $d->jangnm ?? '-',
                'pangkutan' => $d->angnm ?? '-',
                'contno' => $d->contno ?? '-',
                'sealno' => $d->sealno ?? '-',
                'notruk' => $d->notruk ?? '-',
                'fwdnm' => $d->fwdnm ?? '-',
                'emklnm' => $d->emklnm ?? '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    // public function GetSpbDetail($spbpk)
    // {
    //     $DtSpbDetail = DB::table('spb')
    //         ->leftJoin('invoice', 'invoice.spbpk', '=', 'spb.spbpk')
    //         ->leftJoin('spbdt', 'spbdt.spbpk', '=', 'spbdt.spbpk')
    //         ->select(
    //             'spb.spbpk',
    //             'spb.nobukti',
    //             'spb.tgl',
    //             'spb.totsatuan',
    //             'invoice.noinv',
    //             'invoice.pricemat'
    //         )
    //         ->where('spb.spbpk', '=', $spbpk)
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $DtSpbDetail,
    //     ]);
    // }


    public function GetSpbDetail($spbpk)
    {
        $DtSpbDetail = DB::table('spb')
            ->leftJoin('invoice', 'invoice.spbpk', '=', 'spb.spbpk')
            ->leftJoin('spbdt', 'spbdt.spbpk', '=', 'spb.spbpk') // sudah benar sekarang
            ->select(
                'spb.spbpk',
                'spb.nobukti',
                'spb.tgl',
                'spb.totsatuan',
                'invoice.noinv',
                'invoice.cur',
                // 'invoice.pricemat'
                DB::raw('ROUND(invoice.pricemat, 2) as pricemat')
                )
            ->where('spb.spbpk', '=', $spbpk)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $DtSpbDetail,
        ]);
    }

    public function GetSpbForDocBC($spbpk)
    {
        $DtSpbDetail = DB::table('spb')
            ->leftJoin('invoice', 'invoice.spbpk', '=', 'spb.spbpk')
            ->leftJoin('spbdt', 'spbdt.spbpk', '=', 'spbdt.spbpk')
            ->select(
                'spb.spbpk',
                'spb.nobukti',
                'invoice.noinv',
            )
            ->where('spb.spbpk', '=', $spbpk)
            ->first();

        $DtSpb = DB::table('spbdt')
            ->select(
                'spbdt.spbpk',
                'spbdt.prodes',
                'spbdt.buyer',
            )
            ->where('spbdt.spbpk', '=', $spbpk)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $DtSpbDetail,
            'data2' => $DtSpb
        ]);
    }

    public function PostUpdateDocBC(Request $request)
    {
        try {
            \Log::info('Received update request', $request->all());

            $updated = DB::table('spbdt')
                ->where('spbpk', $request->spbpk)
                ->update([
                    'prodes' => $request->prodes,
                    'buyer' => $request->buyer,
                ]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Update successful',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No rows updated. Maybe spbpk is incorrect?',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error updating data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function GetSpbdt(Request $request, $spbpk)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';

        $data_spbdt = DB::table('spbdt')
            ->leftJoin('byr', 'byr.byrpk', '=', 'spbdt.byrpk')
            ->select(
                'spbdt.spbdtpk',
                'spbdt.ordpk',
                'spbdt.bdownpk',
                'spbdt.spbpk',
                'spbdt.noop',
                'spbdt.orderno',
                'spbdt.art',
                'spbdt.prodes',
                'byr.byrpk',
                'byr.byrnm',
                'spbdt.negara',
                'spbdt.jmlsatuan',
                'spbdt.jmlkarton',
                'spbdt.berat',
                'spbdt.kubikasi',
                'spbdt.smarks',
                'spbdt.hrgsat',
                'spbdt.jmlhrg',
                'spbdt.noinv',
                'spbdt.invpk',
            )
            ->where('spbpk', '=', $spbpk)->orderBy('spbdt.spbdtpk', 'ASC');

        $AllDataCmt = $data_spbdt->get();

        $offset = ($page - 1) * $rows;
        $data_spbdt = $data_spbdt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_spbdt as $d) {

            $row[] = array(
                'index' => $index,
                'spbdtpk' => $d->spbdtpk,
                'spbpk' => $d->spbpk,
                'noop' => $d->noop,
                'orderno' => $d->orderno,
                'art' => $d->art,
                'style' => $d->prodes,
                'byrpk' => $d->byrpk,
                'byrnm' => $d->byrnm,
                'negara' => $d->negara,
                'jmlsatuan' => $d->jmlsatuan,
                // 'jmlsatuan' => number_format($d->jmlsatuan, 0, '.', ','),
                'jmlkarton' => $d->jmlkarton,
                'berat' => $d->berat,
                'kubikasi' => $d->kubikasi,
                'smarks' => $d->smarks,
                'hrgsat' => $d->hrgsat,
                'jmlhrg' => $d->jmlhrg,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function InsertSpbdt(Request $request, $spbpk)
    {
        $noop = $request->input('noop');
        $orderno = $request->input('orderno');
        $art = $request->input('art');
        $style = $request->input('style');
        $byrpk = $request->input('byrpk');
        $negara = $request->input('negara');
        $jmlsatuan = $request->input('jmlsatuan');
        $jmlkarton = $request->input('jmlkarton');
        $berat = $request->input('berat');
        $kubikasi = $request->input('kubikasi');
        $smarks = $request->input('smarks');
        $hrgsat = $request->input('hrgsat');
        $jmlhrg = $request->input('jmlhrg');

        $spbdtpk = DB::table('spbdt')->insertGetId([
            'spbpk' => $spbpk,
            'noop' => $noop,
            'orderno' => $orderno,
            'art' => $art,
            'prodes' => $style,
            'byrpk' => $byrpk,
            'negara' => $negara,
            'jmlsatuan' => $jmlsatuan,
            'jmlkarton' => $jmlkarton,
            'berat' => $berat,
            'kubikasi' => $kubikasi,
            'smarks' => $smarks,
            'hrgsat' => $hrgsat,
            'jmlhrg' => $jmlhrg,
        ]);

        // $getInv = DB::table('invoice')->where('spbpk', $spbpk)->first();
        // $ambilinvpk = $getInv->invpk;

        // $tahun_sekarang = date('Y');
        // $dt = DB::table('invoice')
        //     ->select(DB::raw("SUBSTRING_INDEX(noinv, '/', 1) as nomor"))
        //     ->whereYear('tglinv', $tahun_sekarang)
        //     ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(noinv, '/', 1) AS UNSIGNED)"), 'desc')
        //     ->first();

        // if ($dt) {
        //     $nourut = (int) $dt->nomor + 1;
        // } else {
        //     $nourut = 1;
        // }

        // $nourut_padded = str_pad($nourut, 4, '0', STR_PAD_LEFT);
        // $hasilnoinv = $nourut_padded . '/AKB/MIF/' . $tahun_sekarang;

        // $poref = DB::connection('mysql_sig')->table('bdown')
        //     ->where('opno', $noop)
        //     ->where('jenis', '=', 'DETAIL')
        //     ->orderBy('bdownpk', 'DESC')
        //     ->value('poref');

        // $existingInvoice = DB::table('invoice')->where('spbpk', $spbpk)->first();
        // $insertOrUpdateInvoice = !empty($existingInvoice) ? Invoice::find($existingInvoice->invpk) : new Invoice;
        // $insertOrUpdateInvoice->spbpk = $spbpk;
        // $insertOrUpdateInvoice->tglinv = now();
        // $insertOrUpdateInvoice->noinv = $hasilnoinv;
        // if (!empty($existingInvoice))
        //     $insertOrUpdateInvoice->update();
        // else
        //     $insertOrUpdateInvoice->save();

        // $inv = $insertOrUpdateInvoice;

        // $existingInvoicedt = DB::table('invoicedt')->where('spbdtpk', $spbdtpk)->first();
        // $insertOrUpdateInvoicedt = !empty($existingInvoicedt) ? Invoicedt::find($existingInvoicedt->spbdtpk) : new Invoicedt;
        // $insertOrUpdateInvoicedt->invpk = $insertOrUpdateInvoice->invpk;
        // $insertOrUpdateInvoicedt->spbdtpk = $spbdtpk;
        // $insertOrUpdateInvoicedt->mpo = $poref;
        // $insertOrUpdateInvoicedt->qty = $jmlsatuan;
        // if (!empty($existingInvoicedt))
        //     $insertOrUpdateInvoicedt->update();
        // else
        //     $insertOrUpdateInvoicedt->save();


        // ===========================

        $get = DB::connection('mysql_sig')->table('bdown')
            ->where('opno', $noop)
            ->where('jenis', '=', 'DETAIL')
            ->orderBy('bdownpk', 'DESC')->first();
        $poref = $get->poref ?? null;
        $style = $get->style ?? null;
        $ordpk = $get->ordpk ?? null;
        $bdownpk = $get->bdownpk ?? null;

        $getOrd = DB::connection('mysql_sig')->table('ord')
            ->where('ordpk', $ordpk)->first();
        $prodes = $getOrd->prodes ?? null;

        // if ($spbdtpk) {
        // DB::table('invoicedt')->insert([
        //     'spbdtpk' => $spbdtpk,
        //     'invpk' => $ambilinvpk,
        //     'mpo' => $poref,
        //     'mstyle' => $style,
        //     'mprodes' => $prodes,
        //     'qty' => $jmlsatuan,
        // ]);

        $dapatkan = DB::table('spbdt')->where('spbdtpk', $spbdtpk)->update([
            'ordpk' => $ordpk,
            'bdownpk' => $bdownpk,
            // 'art' => $prodes,
            // 'prodes' => $style,
        ]);
        // }

        return response()->json([
            'dapatkan' =>  $dapatkan,
            'spbpk' => $spbpk,
            'noop' => $noop,
            'orderno' => $orderno,
            'art' => $art,
            'negara' => $negara,
            'jmlsatuan' => $jmlsatuan,
            'jmlkarton' => $jmlkarton,
            'berat' => $berat,
            'kubikasi' => $kubikasi,
            'smarks' => $smarks,
            'hrgsat' => $hrgsat,
            'jmlhrg' => $jmlhrg,
        ]);
    }

    public function UpdateSpbdt(Request $request)
    {
        $spbdtpk = $request->input('spbdtpk');
        $noop = $request->input('noop') ?? '';
        $orderno = $request->input('orderno') ?? '';
        $art = $request->input('art') ?? '';
        $style = $request->input('style') ?? '';
        $negara = $request->input('negara') ?? '';
        $jmlsatuan = $request->input('jmlsatuan') ?? '';
        $jmlkarton = $request->input('jmlkarton') ?? '';
        $berat = $request->input('berat') ?? '';
        $kubikasi = $request->input('kubikasi') ?? '';
        $smarks = $request->input('smarks') ?? '';
        $hrgsat = $request->input('hrgsat') ?? '';
        $jmlhrg = $request->input('jmlhrg') ?? '';
        $byrpk = $request->input('byrpk') ?? '';


        DB::table('spbdt')
            ->where('spbdtpk', $spbdtpk)
            ->update([
                'noop' => $noop,
                'orderno' => $orderno,
                'art' => $art,
                'prodes' => $style,
                'byrpk' => $byrpk,
                'negara' => $negara,
                'jmlsatuan' => $jmlsatuan,
                'jmlkarton' => $jmlkarton,
                'berat' => $berat,
                'kubikasi' => $kubikasi,
                'smarks' => $smarks,
                'hrgsat' => $hrgsat,
                'jmlhrg' => $jmlhrg,
            ]);

        $cleanNoop = trim($noop);
        $get = DB::connection('mysql_sig')->table('bdown')
            ->where('opno', $cleanNoop)
            ->where('jenis', 'DETAIL')
            ->orderBy('bdownpk', 'DESC')
            ->first();
        $getIdOrdpk = $get->ordpk;
        $getIdBdownpk = $get->bdownpk;

        DB::table('spbdt')->where('spbdtpk', $spbdtpk)->update([
            'ordpk' => $getIdOrdpk,
            'bdownpk' => $getIdBdownpk,
        ]);

        return response()->json([
            'respon' => 'ini sukses',
            'noop' => $noop,
            'orderno' => $orderno,
            'art' => $art,
            'prodes' => $style,
            'negara' => $negara,
            'jmlsatuan' => $jmlsatuan,
            'jmlkarton' => $jmlkarton,
            'berat' => $berat,
            'kubikasi' => $kubikasi,
            'smarks' => $smarks,
            'hrgsat' => $hrgsat,
            'jmlhrg' => $jmlhrg,
        ]);
    }

    public function SaveEditHeaderSpbdt(Request $request)
    {
        try {
            // Ambil input dari request
            $tgl = $request->input('tgl') ?? '00/00/0000';
            $tgl = Carbon::createFromFormat('d/m/Y', $tgl)->format('Y-m-d');
            $gudpk = $request->input('gudpk');
            $jangpk = $request->input('jangpk');
            $angpk = $request->input('angpk');
            $fwdpk = $request->input('fwdpk');
            $emklpk = $request->input('emklpk');
            $contno = $request->input('contno');
            $sealno = $request->input('sealno');
            $notruk = $request->input('notruk');
            $att = $request->input('att');
            $totsatuan = $request->input('totsatuan');
            $totkarton = $request->input('totkarton');
            $totberat = $request->input('totberat');
            $totkubikasi = $request->input('totkubikasi');
            $tips = $request->input('tips');
            $inv = null;

            // Generate nomor bukti
            $tahun_sekarang = date('Y');
            $dt = DB::table('spb')
                ->select(DB::raw("SUBSTRING_INDEX(nobukti, '/', 1) as nomor"))
                ->whereYear('tgl', $tahun_sekarang)
                ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(nobukti, '/', 1) AS UNSIGNED)"), 'desc')
                ->first();

            if ($dt) {
                $nourut = (int) $dt->nomor + 1;
            } else {
                $nourut = 1;
            }

            $nourut_padded = str_pad($nourut, 4, '0', STR_PAD_LEFT);
            $nobukti = $nourut_padded . '/SPB/' . $tahun_sekarang;

            // Update data di tabel spb
            // DB::table('spb')
            //     ->where('spbpk', $request->spbpk)
            //     ->update([
            //         'tgl' => $tgl,
            //         // 'nobukti' => $nobukti,
            //         'gudpk' => $gudpk,
            //         'jangpk' => $jangpk,
            //         'angpk' => $angpk,
            //         'fwdpk' => $fwdpk,
            //         'emklpk' => $emklpk,
            //         'contno' => $contno,
            //         'notruk' => $notruk,
            //         'sealno' => $sealno,
            //         'att' => $att,
            //         'totsatuan' => $totsatuan,
            //         'totkarton' => $totkarton,
            //         'totberat' => $totberat,
            //         'totkubikasi' => $totkubikasi,
            //         'tips' => $tips,
            //     ]);

            // Update data di tabel spb
            $existingSpb = DB::table('spb')
                ->where('spbpk', $request->spbpk)
                ->first();

            if ($existingSpb) {
                // Jika nobukti sudah ada, jangan update nilainya
                $nobuktiToUpdate = $existingSpb->nobukti ? $existingSpb->nobukti : $nobukti;

                DB::table('spb')
                    ->where('spbpk', $request->spbpk)
                    ->update([
                        'tgl' => $tgl,
                        'nobukti' => $nobuktiToUpdate,
                        'gudpk' => $gudpk,
                        'jangpk' => $jangpk,
                        'angpk' => $angpk,
                        'fwdpk' => $fwdpk,
                        'emklpk' => $emklpk,
                        'contno' => $contno,
                        'notruk' => $notruk,
                        'sealno' => $sealno,
                        'att' => $att,
                        'totsatuan' => $totsatuan,
                        'totkarton' => $totkarton,
                        'totberat' => $totberat,
                        'totkubikasi' => $totkubikasi,
                        'tips' => $tips,
                    ]);
            }

            // $tahun_sekarang = date('Y');
            // $dt = DB::table('invoice')
            //     ->select(DB::raw("SUBSTRING_INDEX(noinv, '/', 1) as nomor"))
            //     ->whereYear('tglinv', $tahun_sekarang)
            //     ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(noinv, '/', 1) AS UNSIGNED)"), 'desc')
            //     ->first();

            // if ($dt) {
            //     $nourut = (int) $dt->nomor + 1;
            // } else {
            //     $nourut = 1;
            // }

            // $nourut_padded = str_pad($nourut, 4, '0', STR_PAD_LEFT);
            // $hasilnoinv = $nourut_padded . '/AKB/MIF/' . $tahun_sekarang;

            // $poref = DB::connection('mysql_sig')->table('bdown')
            //     ->where('opno', $noop)
            //     ->where('jenis', '=', 'DETAIL')
            //     ->orderBy('bdownpk', 'DESC')
            //     ->value('poref');

            // $existingInvoice = DB::table('invoice')->where('spbpk', $request->spbpk)->first();
            // $insertOrUpdateInvoice = !empty($existingInvoice) ? Invoice::find($existingInvoice->invpk) : new Invoice;

            // $insertOrUpdateInvoice->spbpk = $request->spbpk;
            // $insertOrUpdateInvoice->tglinv = now();

            // // Hanya set noinv jika invoice belum ada sebelumnya
            // if (empty($existingInvoice)) {
            //     $insertOrUpdateInvoice->noinv = $hasilnoinv;
            // }

            // $insertOrUpdateInvoice->totqty = $totsatuan;

            // if (!empty($existingInvoice)) {
            //     $insertOrUpdateInvoice->update();
            // } else {
            //     $insertOrUpdateInvoice->save();
            // }

            // $inv = $insertOrUpdateInvoice;


            // ===========================

            // $existingInvoicedt = DB::table('invoicedt')->where('spbdtpk', $spbdtpk)->first();
            // $insertOrUpdateInvoicedt = !empty($existingInvoicedt) ? Invoicedt::find($existingInvoicedt->spbdtpk) : new Invoicedt;
            // $insertOrUpdateInvoicedt->invpk = $insertOrUpdateInvoice->invpk;
            // $insertOrUpdateInvoicedt->spbdtpk = $spbdtpk;
            // $insertOrUpdateInvoicedt->mpo = $poref;
            // $insertOrUpdateInvoicedt->qty = $jmlsatuan;
            // if (!empty($existingInvoicedt))
            //     $insertOrUpdateInvoicedt->update();
            // else
            //     $insertOrUpdateInvoicedt->save();    

            // $existingInvoice = DB::table('invoice')->where('spbpk', $request->spbpk)->first();
            // if ($existingInvoice) {
            //     DB::table('invoice')
            //         ->where('spbpk', $request->spbpk)
            //         ->update([
            //             'tglinv' => $tgl,
            //             'totqty' => $totsatuan,
            //         ]);

            //     // $dt_inv = $existingInvoice->spbpk;

            //     $existingInvoice2 = DB::table('invoice')->where('spbpk', $request->spbpk)->first();
            //     if ($existingInvoice2) {
            //         DB::table('invoice')
            //             ->where('spbpk', $request->spbpk)
            //             ->update([
            //                 'tglinv' => $tgl,
            //                 'totqty' => $totsatuan,
            //             ]);
            //         // $dt_inv = $existingInvoice->spbpk;
            //     }

            // } else {

            //     $tahun_sekarang = date('Y');
            //     $dt = DB::table('invoice')
            //         ->select(DB::raw("SUBSTRING_INDEX(noinv, '/', 1) as nomor"))
            //         ->whereYear('tglinv', $tahun_sekarang)
            //         ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(noinv, '/', 1) AS UNSIGNED)"), 'desc')
            //         ->first();

            //     if ($dt) {
            //         $nourut = (int) $dt->nomor + 1;
            //     } else {
            //         $nourut = 1;
            //     }

            //     $nourut_padded = str_pad($nourut, 4, '0', STR_PAD_LEFT);
            //     $hasilnoinv = $nourut_padded . '/AKB/MIF/' . $tahun_sekarang;

            //     $dt_inv = DB::table('invoice')->insert([
            //         'spbpk' => $request->spbpk,
            //         'tglinv' => $tgl,
            //         'totqty' => $totsatuan,
            //         'noinv' => $hasilnoinv,
            //     ]);



            //     $dt_invdt = DB::table('invoicedt')->insert([
            //         'invpk' => $dt_inv->invpk,
            //     ]);


            // }

            // Kembalikan respons JSON
            return response()->json([
                'status' => 'success',
                'message' => 'SPB updated successfully',
                'data' => [
                    'nobukti' => $nobukti,
                    'gudpk' => $gudpk,
                    'jangpk' => $jangpk,
                    'angpk' => $angpk,
                    'fwdpk' => $fwdpk,
                    'emklpk' => $emklpk,
                    'contno' => $contno,
                    'notruk' => $notruk,
                    'att' => $att,
                    'inv' => $inv,
                ]
            ]);
        } catch (\Exception $e) {
            // Tangani error
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function DeleteSpbdt(Request $request)
    {

        $ids = $request->input('ids');

        if (!is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'msg' => "<div class='text-infomerah' style='width:415px;'>" . $ids . "Invalid data received &nbsp; &#x2573;</div>",
            ]);
        }

        // Proses pembaruan untuk setiap ID
        foreach ($ids as $id) {
            $result = DB::table('spbdt')->where('spbdtpk', '=', $id)->delete();
            // $result2 = DB::table('invoicedt')->where('spbdtpk', '=', $id)->delete();
            if (!$result) {
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

    // public function SpbdtCopy(Request $request)
    // {
    //     $spbdtpk = $request->spbdtpk;
    //     $spbpk = $request->spbpk;

    //     $data = DB::table('spbdt')->where('spbdtpk', $spbdtpk)->first();

    //     if ($data) {
    //         // Insert ke spbdt, hanya mendapatkan ID
    //         $insertId = DB::table('spbdt')->insertGetId([
    //             'spbpk' => $spbpk ?? null,
    //             'ordpk' => $data->ordpk ?? null,
    //             'bdownpk' => $data->bdownpk ?? null,
    //             'noop' => $data->noop ?? null,
    //             'orderno' => $data->orderno ?? null,
    //             'art' => $data->art ?? null,
    //             'prodes' => $data->prodes ?? null,
    //             'byrpk' => $data->byrpk ?? null,
    //             'negara' => $data->negara ?? null,
    //             'jmlsatuan' => $data->jmlsatuan ?? 0,
    //             'jmlkarton' => $data->jmlkarton ?? 0,
    //             'berat' => $data->berat ?? 0,
    //             'kubikasi' => $data->kubikasi ?? 0,
    //             'smarks' => $data->smarks ?? null,
    //             'hrgsat' => $data->hrgsat ?? 0,
    //             'jmlhrg' => $data->jmlhrg ?? 0
    //         ]);

    //         // Ambil kembali data dari spbdt yang baru dimasukkan
    //         $insertedData = DB::table('spbdt')->where('spbdtpk', $insertId)->first();

    //         // Cek apakah invoice ada
    //         $getInv = DB::table('invoice')->where('spbpk', $spbpk)->first();
    //         if (!$getInv) {
    //             return response()->json(['error' => 'Invoice not found for this spbpk'], 404);
    //         }

    //         // Gunakan noop dari data yang sudah di-insert
    //         $cleanNoop = trim($insertedData->noop);

    //         // Ambil data dari bdown berdasarkan noop
    //         $get = DB::connection('mysql_sig')->table('bdown')
    //             ->where('opno', $cleanNoop)
    //             ->where('jenis', 'DETAIL')
    //             ->orderBy('bdownpk', 'DESC')
    //             ->first();

    //         $poref = $get ? $get->poref : null;

    //         if (!$poref) {
    //             return response()->json(['error' => 'No PO reference found'], 404);
    //         }

    //         // Insert ke invoicedt
    //         DB::table('invoicedt')->insert([
    //             'invpk' => $getInv->invpk,
    //             'mpo' => $poref,
    //             'qty' => $insertedData->jmlsatuan ?? 0,
    //             'spbdtpk' => $insertId,
    //         ]);

    //         return response()->json($insertedData);
    //     }

    //     return response()->json(['error' => 'No data found'], 404);
    // }

    public function SpbdtCopy(Request $request)
    {
        $spbdtpk = $request->spbdtpk;
        $spbpk = $request->spbpk;

        $data = DB::table('spbdt')->where('spbdtpk', $spbdtpk)->first();

        if (!$data) {
            return response()->json(['error' => 'No data found'], 404);
        }

        // Insert ke spbdt, hanya mendapatkan ID
        $insertId = DB::table('spbdt')->insertGetId([
            'spbpk' => $spbpk ?? null,
            'ordpk' => $data->ordpk ?? null,
            'bdownpk' => $data->bdownpk ?? null,
            'noop' => $data->noop ?? null,
            'orderno' => $data->orderno ?? null,
            'art' => $data->art ?? null,
            'prodes' => $data->prodes ?? null,
            'byrpk' => $data->byrpk ?? null,
            'negara' => $data->negara ?? null,
            'jmlsatuan' => $data->jmlsatuan ?? null,
            'jmlkarton' => $data->jmlkarton ?? null,
            'berat' => $data->berat ?? null,
            'kubikasi' => $data->kubikasi ?? null,
            'smarks' => $data->smarks ?? null,
            'hrgsat' => $data->hrgsat ?? null,
            'jmlhrg' => $data->jmlhrg ?? null
        ]);

        // Cek apakah ada invoice dengan spbpk ini
        $getInv = DB::table('invoice')->where('spbpk', $spbpk)->first();

        if (!$getInv) {
            // Jika invoice tidak ditemukan, langsung return insertId saja
            return response()->json(['message' => 'No invoice found, only inserted spbdt', 'insertId' => $insertId]);
        }

        // Ambil kembali data dari spbdt yang baru dimasukkan
        $insertedData = DB::table('spbdt')->where('spbdtpk', $insertId)->first();

        // Gunakan noop dari data yang sudah di-insert
        $cleanNoop = trim($insertedData->noop);

        // Ambil data dari bdown berdasarkan noop
        $get = DB::connection('mysql_sig')->table('bdown')
            ->where('opno', $cleanNoop)
            ->where('jenis', 'DETAIL')
            ->orderBy('bdownpk', 'DESC')
            ->first();

        $poref = $get ? $get->poref : null;

        if (!$poref) {
            return response()->json(['message' => 'No PO reference found, inserted spbdt only', 'insertId' => $insertId]);
        }

        // Insert ke invoicedt
        DB::table('invoicedt')->insert([
            'invpk' => $getInv->invpk,
            'mpo' => $poref,
            'qty' => $insertedData->jmlsatuan ?? 0,
            'spbdtpk' => $insertId,
        ]);

        return response()->json(['message' => 'Inserted spbdt and invoicedt', 'insertedData' => $insertedData]);
    }

    public function GetLookupOrder(request $request)
    {
        // $tahun = date("Y");
        $searchByInput = $request->searchByInputOrder;
        // $now2 = Carbon::now('Asia/Jakarta');
        // $threeMonthsAgo = $now2->copy()->subMonths(6);
        // $now2Formatted = $now2->format('Y-m-d'); // Format tanggal sekarang
        // $threeMonthsAgoFormatted = $threeMonthsAgo->format('Y-m-d'); // Format 3 bulan ke belakang

        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $dtstyle = DB::connection('mysql_sig')->table('ord')
            ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
            ->leftJoin('fu', 'fu.fupk', '=', 'ord.fupk')
            ->select(
                'ord.ordpk',
                'ord.ordno',
                'byr.byrpk',
                'byr.byrnm',
                'ord.artno',
                'ord.prodes',
                'ord.season',
                'ord.srno',
                'ord.ordqty',
                'fu.fupk',
                'fu.funm',
                'ord.stage',
                // 'ord.datecreate',
                DB::raw('DATE_FORMAT(ord.datecreate, "%d %b %Y") as datecreate'),

            )
            // ->whereBetween('status.date', [$threeMonthsAgoFormatted, $now2Formatted])
            ->where('ord.byrpk', '>', 0)
            ->where('ord.fupk', '>', 0)
            ->where('ord.ordqty', '>', 0);


        // ->groupBy('status.statuspk');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(ord.ordno,'',byr.byrnm,'',ord.artno,'',ord.prodes,'',ord.season,'',ord.srno,'',fu.funm,'',ord.ordqty,'',ord.stage)");
            $dtstyle = $dtstyle->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInput) . '%');
        }

        $dtstyle = $dtstyle->orderBy('ord.ordpk', 'DESC');
        $dtstyleTotal = $dtstyle->get()->count();
        $offset = ($page - 1) * $rows;
        $dtstyle = $dtstyle->skip($offset)->take($rows)->get();
        $result = array();
        $row = array();
        $result['total'] = $dtstyleTotal;
        $index = 0;

        foreach ($dtstyle as $d) {
            $row[] = array(
                'ordpk' => $d->ordpk,
                'ordno' => $d->ordno,
                'byrnm' => $d->byrnm,
                'byrpk' => $d->byrpk,
                'artno' => $d->artno,
                'prodes' => $d->prodes,
                'season' => $d->season,
                'srno' => $d->srno,
                'funm' => $d->funm,
                'ordqty' => number_format($d->ordqty, 0, '.', ','),
                'stage' => $d->stage,
                'datecreate' => $d->datecreate,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function AddLookupOrder(Request $request)
    {
        $spbpk = $request->input('spbpk');
        $rows = $request->input('rows');
        $invpk = $request->input('invpk');
        $inv = null;

        $count = count($rows);
        // $misal = [];
        $processedpodtpk = [];

        for ($i = 0; $i < $count; $i++) {
            $rowpodtpk = $rows[$i]['ordpk'] ?? null;
            $ambilbyrpk = $rows[$i]['byrpk'] ?? null;
            $processedpodtpk[] = $rowpodtpk;

            $poref = DB::connection('mysql_sig')->table('bdown')
                ->where('ordpk', $rowpodtpk)
                ->where('jenis', '=', 'DETAIL')
                ->orderBy('bdownpk', 'DESC')
                ->value('poref');

            $get = DB::connection('mysql_sig')->table('byr')->where('byrpk', $ambilbyrpk)->first();
            $get2 = DB::table('byr')->where('byrnm', $get->byrnm)->first();
            $byrpk = $get2->byrpk ?? null;

            $spbdtpk = DB::table('spbdt')->insertGetId([
                'spbpk' => $spbpk,
                'ordpk' => $rows[$i]['ordpk'] ?? null,
                'noop' => $rows[$i]['ordno'] ?? null,
                'art' => $rows[$i]['prodes'] ?? null,
                'prodes' => $rows[$i]['artno'] ?? null,
                'jmlsatuan' => $rows[$i]['ordqty'] ?? null,
                'byrpk' => $byrpk,
            ]);

            // $tahun_sekarang = date('Y');
            // $dt = DB::table('invoice')
            //     ->select(DB::raw("SUBSTRING_INDEX(noinv, '/', 1) as nomor"))
            //     ->whereYear('tglinv', $tahun_sekarang)
            //     ->orderBy(DB::raw("CAST(SUBSTRING_INDEX(noinv, '/', 1) AS UNSIGNED)"), 'desc')
            //     ->first();

            // if ($dt) {
            //     $nourut = (int) $dt->nomor + 1;
            // } else {
            //     $nourut = 1;
            // }

            // $nourut_padded = str_pad($nourut, 4, '0', STR_PAD_LEFT);
            // $hasilnoinv = $nourut_padded . '/AKB/MIF/' . $tahun_sekarang;

            // $existingInvoice = DB::table('invoice')->where('spbpk', $spbpk)->first();
            // $insertOrUpdateInvoice = !empty($existingInvoice) ? Invoice::find($existingInvoice->invpk) : new Invoice;
            // $insertOrUpdateInvoice->spbpk = $spbpk;
            // $insertOrUpdateInvoice->tglinv = now();
            // $insertOrUpdateInvoice->noinv = $hasilnoinv;
            // if (!empty($existingInvoice))
            //     $insertOrUpdateInvoice->update();
            // else
            //     $insertOrUpdateInvoice->save();

            // $inv = $insertOrUpdateInvoice;

            // $existingInvoicedt = DB::table('invoicedt')->where('spbdtpk', $spbdtpk)->first();
            // $insertOrUpdateInvoicedt = !empty($existingInvoicedt) ? Invoicedt::find($existingInvoicedt->spbdtpk) : new Invoicedt;
            // $insertOrUpdateInvoicedt->invpk = $insertOrUpdateInvoice->invpk;
            // $insertOrUpdateInvoicedt->spbdtpk = $spbdtpk;
            // $insertOrUpdateInvoicedt->mpo = $poref;
            // $insertOrUpdateInvoicedt->qty = $rows[$i]['ordqty'] ?? null;
            // if (!empty($existingInvoicedt))
            //     $insertOrUpdateInvoicedt->update();
            // else
            //     $insertOrUpdateInvoicedt->save();



            // ===================================

            // if ($existingInvoice) {
            //     $intoinvoice = DB::table('invoice')->insertGetId([
            //         'spbpk' => $spbpk,
            //         'tglinv' => now(),
            //         'noinv' => $hasilnoinv,
            //     ]);

            //     $intoinvoicedt = DB::table('invoicedt')->insert([
            //         'invpk' => $intoinvoice->invpk,
            //         'mpo' => 'apa',
            //         // 'qty' => $rows[$i]['ordqty'] ?? null,
            //     ]);
            // }



            // if ($spbdtpk) {
            //     DB::table('invoicedt')->insert([
            //         'spbdtpk' => $spbdtpk,
            //         'invpk' => $invpk,
            //         'mpo' => $poref,
            //         'qty' => $rows[$i]['ordqty'] ?? null,
            //     ]);
            // }

            // $getInv = DB::table('invoice')->where('spbpk', $spbpk)->first();
            // $ambilinvpk = $getInv->invpk;

            // $get = DB::connection('mysql_sig')->table('bdown')
            //     ->where('opno', $rows[$i]['ordno'])
            //     ->where('jenis', '=', 'DETAIL')
            //     ->orderBy('bdownpk', 'DESC')->first();
            // $poref = $get->poref ?? null;
            // $style = $get->style ?? null;
            // $ordpk = $get->ordpk ?? null;
            // $bdownpk = $get->bdownpk ?? null;

            // $getOrd = DB::connection('mysql_sig')->table('ord')
            //     ->where('ordpk', $ordpk)->first();
            // $prodes = $getOrd->prodes ?? null;

            // if ($spbdtpk) {
            //     DB::table('invoicedt')->insert([
            //         'spbdtpk' => $spbdtpk,
            //         'invpk' => $ambilinvpk,
            //         'mpo' => $poref,
            //         'mstyle' => $style,
            //         'mprodes' => $prodes,
            //         'qty' => $rows[$i]['ordqty'] ?? null,
            //     ]);

            //     DB::table('spbdt')->where('spbdtpk', $spbdtpk)->update([
            //         'ordpk' => $ordpk,
            //         'bdownpk' => $bdownpk,
            //         'art' => $style,
            //         'prodes' => $prodes,
            //     ]);
            // }

            // $misal[] = "Processed row " . ($i + 1);
        }

        $spbdt = DB::table('spbdt')
            ->select('*')
            ->leftJoin('spb', 'spb.spbpk', '=', 'spbdt.spbpk')
            ->where('spb.spbpk', $spbpk)
            ->orderBy('spbdt.spbdtpk', 'ASC')
            ->get();

        $total_spbdt = $spbdt->count() ?? 0;

        return response()->json([
            'status' => 'success',
            'message' => 'Data processed successfully',
            'spbpk' => $spbpk,
            'rows' => $rows,
            'inv' => $inv,
            // 'misal' => $misal,
            'processedDtgpk' => $processedpodtpk,
            'total_spbdt' => $total_spbdt,
        ]);
    }

    public function UploadExcel(Request $request, $spbpk)
    {
        $request->validate([
            'uploadfile' => 'required|file|mimes:xls,xlsx|max:10240',
        ]);

        // Cek apakah file ada
        if (!$request->hasFile('uploadfile')) {
            return response()->json(['success' => false, 'message' => 'No file received by server.'], 400);
        }

        // Simpan file
        $files = $request->file('uploadfile');
        $original_name = $files->getClientOriginalName();
        $ext = strtolower($files->getClientOriginalExtension());
        $current_time = Carbon::now()->format('H-i-s');
        $image_full_name = $spbpk . '-' . $current_time . '.' . Str::slug(pathinfo($original_name, PATHINFO_FILENAME)) . '.' . $ext;
        $destinationPath = public_path('upload/spbdt/');

        // Pastikan direktori ada
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $files->move($destinationPath, $image_full_name);
        $filePath = $destinationPath . $image_full_name;

        try {
            Excel::import(new SpbdtImport($spbpk), $filePath);
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan saat mengimpor file: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'File uploaded and imported successfully.'], 200);
    }
}
