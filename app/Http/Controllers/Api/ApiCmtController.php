<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Exports\KwitansiExport;
use App\Exports\AllExportKwitansiDebitNoteTagihan;
use App\Exports\ExportListShipment;
use App\Exports\DebitNoteExport;
use App\Exports\TagihanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use PDF;

class ApiCmtController extends Controller
{
    public function GetCmt(Request $request)
    {
        $tahun = date("Y");
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        // $filterByMonth = $request->filterByMonth;
        $filterByMonth = $request->filterByMonth ?? date('m');
        $filterByYear = $request->filterByYear;
        $sortlistByDate = $request->sortlistByDate;

        if (!empty($filterByYear)) $tahun = $filterByYear;

        $data_cmt = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.cur',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
                DB::raw('DATE_FORMAT(invoice.tglkk, "%d %b %Y") as tglkk'),
            )->where('invoice.noinv', '!=', '');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(invoice.noinv, ''),
                COALESCE(invoice.nokk, ''),
                COALESCE(spb.nobukti, ''),
                COALESCE(CAST(invoice.totqty AS CHAR), ''),
                COALESCE(CAST(invoice.pricemat AS CHAR), ''),
                COALESCE(CAST(invoice.ppn AS CHAR), ''),
                COALESCE(CAST(invoice.kursp AS CHAR), ''),
                COALESCE(gud.gudnm, '')
            )");
            $data_cmt = $data_cmt->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        // if (!empty($sortlistByDate)) {
        if ($sortlistByDate == 11) {
            $data_cmt = $data_cmt->whereYear('invoice.tglinv', $tahun)->orderBy('invoice.tglinv', 'desc')->orderBy('invoice.noinv', 'DESC');
        } else if ($sortlistByDate == 12) {
            $data_cmt = $data_cmt->whereYear('invoice.tglinv', $tahun)->orderBy('invoice.tglinv', 'asc')->orderBy('invoice.noinv', 'DESC');
        } else {
            $data_cmt = $data_cmt->whereYear('invoice.tglinv', $tahun)->orderBy('invoice.tglinv', 'desc')->orderBy('invoice.noinv', 'DESC');
        }
        // }

        if (!empty($filterByMonth)) {
            $data_cmt = $data_cmt->whereMonth('invoice.tglinv', '=', $filterByMonth);
        }

        if (!empty($filterByYear)) {
            $data_cmt = $data_cmt->whereYEAR('invoice.tglinv', '=', $tahun);
        }

        $AllDataCmt = $data_cmt->get();

        $offset = ($page - 1) * $rows;
        $data_cmt = $data_cmt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_cmt as $d) {

            $jmlqty = DB::table('invoicedt')
                ->where('invpk', $d->invpk)
                ->sum('qty');
            
            $dt_pricemat = $d->pricemat !== null ? 'Rp' . number_format($d->pricemat, 3, '.', ',') : '-';
            if($d->cur == 1){
                $dt_pricemat = $d->pricemat !== null ? '$' . number_format($d->pricemat, 3, '.', ',') : '-';
            }

            $row[] = array(
                'index' => $index,
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'tglkk' => $d->tglkk ?? '-',
                'gudang' => $d->gudnm ?? '-',
                'noinv' => $d->noinv,
                'noinv' => '<a href="' . route('detail.page.finance', $d->invpk) . '" target="_blank" style="color:#000; cursor:pointer; text-decoration:none;">' . $d->noinv . '</a>',
                'nobukti' => $d->nobukti ?? '-',
                'nokk' => $d->nokk ?? '-',
                // 'totqty' => number_format($d->totqty, 0, '.', ','),
                'totqty' => !empty($jmlqty) ? number_format($jmlqty, 0, '.', ',') : '-',
                // 'priceppn' => $d->priceppn !== null ? '$' . number_format($d->priceppn, 3, '.', ',') : '-',
                'pricemat' => $dt_pricemat,
                'ppn' => $d->ppn ? $d->ppn . '%' : '-',
                'kursppn' => $d->kursp ? number_format($d->kursp, 0, '.', ',') : '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function GetCmtDetail($invpk)
    {
        $DtCmt = DB::table('invoice')
            ->where('invpk', '=', $invpk)
            ->select(
                'invpk',
                'spbpk',
                'noinv',
                'nokk',
                'totqty',
                'pricemat',
                'ppn',
                'kursp',
                DB::raw('DATE_FORMAT(tglinv, "%d %b %Y") as tglinv'),
                DB::raw('DATE_FORMAT(tglinv, "%d %b %Y") as tglkk'),
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $DtCmt
        ]);
    }

    public function GetInvoiceDetail(Request $request, $invpk)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';

        $data_invdt = DB::table('invoicedt')
            ->select(
                'invoicedt.invdtpk',
                'invoicedt.invpk',
                'invoicedt.mpo',
                'invoicedt.mstyle',
                'invoicedt.mprodes',
                'invoicedt.qty',
                'invoicedt.pricedt',
                'invoicedt.spbdtpk',
            )
            ->where('invpk', '=', $invpk);
        // ->get();


        $AllDataCmt = $data_invdt->get();

        $offset = ($page - 1) * $rows;
        $data_invdt = $data_invdt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_invdt as $d) {

            //     $getInvpk = DB::table('invoice')
            // 	->select('invpk', 'spbpk')
            // 	->where('invpk', '=', $d->invpk)
            // 	->first();

            //     $dtspbdt = DB::table('spbdt')
            // 	->select('spbdtpk', 'spbpk', 'noop', 'orderno', 'art', 'jmlsatuan')
            // 	->where('spbpk', '=', $getInvpk->spbpk)
            // 	->get();

            // // Kelompokkan berdasarkan 'noop'
            // $grouped = $dtspbdt->groupBy('noop');

            // $cleanNoop = trim($dtspbdt->first()->noop);
            // $getSIG = DB::connection('mysql_sig')->table('ord')
            // 	->select('ordpk', 'ordno', 'prodes')
            // 	->where('ordno', 'like', "%" . $cleanNoop . "%")
            // 	->get();
            // } 

            // $ordpk = $getSIG ? $getSIG->ordpk : 'N/A';
            // $ordno = $getSIG ? $getSIG->ordno : 'N/A';
            // $prodes = $getSIG ? $getSIG->prodes : 'N/A';
            // $artno = $getSIG ? $getSIG->artno : 'N/A';

            $row[] = array(
                'index' => $index,
                'invdtpk' => $d->invdtpk,
                'invpk' => $d->invpk,
                'mpo' => $d->mpo,
                'mstyle' => $d->mstyle,
                'mprodes' => $d->mprodes,
                // 'qty' => number_format($d->qty, 0, '.', ','),
                'qty' => $d->qty,
                'pricedt' => $d->pricedt ?? '-',
                'spbdtpk' => $d->spbdtpk,
                // 'artno' => $artno,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function InsertInvoicedt(Request $request, $invpk)
    {
        // $invpk = $request->input('invpk');
        $mpo = $request->input('mpo');
        $mstyle = $request->input('mstyle');
        $mprodes = $request->input('mprodes');
        $qty = $request->input('qty');
        $pricedt = $request->input('pricedt');

        DB::table('invoicedt')->insert([
            'invpk' => $invpk,
            'mpo' => $mpo,
            'mstyle' => $mstyle,
            'mprodes' => $mprodes,
            'qty' => $qty,
            'pricedt' => $pricedt,
            // 'spbdtpk' => null,
        ]);

        return response()->json([
            'invpk' => $invpk,
            'mpo' => $mpo,
            'mstyle' => $mstyle,
            'mprodes' => $mprodes,
            'qty' => $qty,
            'pricedt' => $pricedt,
        ]);
    }

    public function UpdateInvoicedt(Request $request)
    {
        $invdtpk = $request->input('invdtpk');
        $mpo = $request->input('mpo') ?? '';
        $mstyle = $request->input('mstyle') ?? '';
        $mprodes = $request->input('mprodes') ?? '';
        $qty = $request->input('qty') ?? '';
        $pricedt = $request->input('pricedt') ?? '';

        DB::table('invoicedt')
            ->where('invdtpk', $invdtpk)
            ->update([
                'mpo' => $mpo,
                'mstyle' => $mstyle,
                'mprodes' => $mprodes,
                'qty' => $qty,
                'pricedt' => $pricedt,
            ]);

        return response()->json([
            'respon' => 'ini sukses',
            'mpo' => $mpo,
            'mstyle' => $mstyle,
            'mprodes' => $mprodes,
            'qty' => $qty,
            'pricedt' => $pricedt,
        ]);
    }

    public function SaveEditHeader(Request $request)
    {
        $noinv = $request->input('noinv');
        $nokk = $request->input('nokk');
        $nofp = $request->input('nofp');
        $ppn = $request->input('ppn');
        $tglinv = $request->input('tglinv') ?? '00/00/0000';
        $tglinv = Carbon::createFromFormat('d/m/Y', $tglinv)->format('Y-m-d');
        $tglkk = $request->input('tglkk') ?? '00/00/0000';
        $tglkk = Carbon::createFromFormat('d/m/Y', $tglkk)->format('Y-m-d');
        $kursp = $request->input('kursp') ? str_replace(',', '', $request->input('kursp')) : null;
        // $priceppn = $request->input('priceppn');

        DB::table('invoice')
            ->where('invpk', $request->invpk)
            ->update([
                'tglinv' => $tglinv,
                'noinv' => $noinv,
                'nokk' => $nokk,
                'nofp' => $nofp,
                'tglkk' => $tglkk,
                'ppn' => $ppn,
                'kursp' => $kursp,
                // 'priceppn' => str_replace(',', '', $request->priceppn),
                // 'kursp' => str_replace(',', '', $request->kursp),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'noinv' => $noinv,
            'nokk' => $nokk,
            'nofp' => $nofp,
            'kursp' => $kursp,
            // 'priceppn' => $priceppn,
        ]);
    }

    public function DeleteInvDt(Request $request)
    {

        // Ambil array ID dari permintaan
        $ids = $request->input('ids');

        if (!is_array($ids) || count($ids) === 0) {
            return response()->json([
                'success' => false,
                'msg' => "<div class='text-infomerah' style='width:415px;'>" . $ids . "Invalid data received &nbsp; &#x2573;</div>",
            ]);
        }

        // Proses pembaruan untuk setiap ID
        foreach ($ids as $id) {
            $result = DB::table('invoicedt')->where('invdtpk', '=', $id)->delete();

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

    //Edit pada modal list depan//
    public function EditCmt(Request $request)
    {
        try {
            DB::table('invoice')
                ->where('invpk', $request->invpk)
                ->update([
                    'nokk' => $request->nokk,
                    // 'totqty' => str_replace(',', '', $request->totqty),
                    'priceppn' => str_replace(',', '', $request->priceppn),
                    'ppn' => $request->ppn,
                    'kursp' => str_replace(',', '', $request->kursp),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'invpk' => $request->invpk,
                'spbpk' => $request->spbpk,
                'nokk' => $request->nokk,
                // 'totqty' => $request->totqty,
                'priceppn' => $request->priceppn,
                'ppn' => $request->ppn,
                'kursp' => $request->kursp,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating Invoice: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function GetCmtModalExport(Request $request)
    {

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $SelectGudang = $request->SelectGudang;
        $SelectStart = $request->SelectStart;
        $SelectFinish = $request->SelectFinish;

        $data_cmt = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.cur',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.nofp',
                'invoice.kursp',
                'invoicedt.qty',
                'invoicedt.pricedt',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )
            ->where('invoice.nokk', '!=', '')
            ->where('invoice.noinv', '!=', '')
            ->orderBy('invoice.invpk', 'DESC');


        if (!empty($SelectGudang)) {
            $data_cmt = $data_cmt->where('gud.gudpk', $SelectGudang);
        }

        // if (!empty($SelectStart)) {
        //     $data_cmt = $data_cmt->whereDate('invoice.tglinv', '>=', $SelectStart);
        // }

        // if (!empty($SelectFinish)) {
        //     $data_cmt = $data_cmt->whereDate('invoice.tglinv', '<=', $SelectFinish);
        // }

        // if ($SelectStart && $SelectFinish) {
        //     $data_cmt->whereBetween('invoice.tglinv', [$SelectStart, $SelectFinish]);
        // }

        if (!empty($SelectStart) && !empty($SelectFinish)) {
            $formattedDateStart = Carbon::createFromFormat('d/m/Y', $SelectStart)->format('Y-m-d');
            $formattedDateFinish = Carbon::createFromFormat('d/m/Y', $SelectFinish)->format('Y-m-d');

            $data_cmt->whereBetween('invoice.tglinv', [$formattedDateStart, $formattedDateFinish]);
        }

        $AllDataCmt = $data_cmt->get();

        $offset = ($page - 1) * $rows;
        $data_cmt = $data_cmt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_cmt as $d) {
            if($d->cur == 1){
                $dt_pricedt = $d->pricedt !== null ? '$ ' . number_format($d->pricedt, 3, '.', ',') : '-';
                // $dt_amountlistpay = '$' . number_format(($d->qty * round($d->pricedt, 3)), 0, '.', ',');
                // $dt_totamountinv = '$' . number_format((($d->qty * round($d->pricedt, 3)) * 1.11), 3, '.', ',');
                $dt_amountlistpay = ($d->qty * round($d->pricedt, 3)) != 0 ? '$ ' . number_format(($d->qty * round($d->pricedt, 3)), 0, '.', ',') : '-';
                $dt_totamountinv = (($d->qty * round($d->pricedt, 3)) * 1.11) != 0 ? '$ ' . number_format((($d->qty * round($d->pricedt, 3)) * 1.11), 3, '.', ',') : '-';
            }else{
                $dt_pricedt = $d->pricedt !== null ? 'Rp ' . number_format($d->pricedt, 0, '.', ',') : '-';
                // $dt_amountlistpay = 'Rp' . number_format(($d->qty * round($d->pricedt, 3)), 0, '.', ',');
                // $dt_totamountinv = 'Rp' . number_format((($d->qty * round($d->pricedt, 3)) * 1.11), 3, '.', ',');
                $dt_amountlistpay = ($d->qty * round($d->pricedt, 3)) != 0 ? 'Rp ' . number_format(($d->qty * round($d->pricedt, 3)), 0, '.', ',') : '-';
                $dt_totamountinv = (($d->qty * round($d->pricedt, 3)) * 1.11) != 0 ? 'Rp ' . number_format((($d->qty * round($d->pricedt, 3)) * 1.11), 3, '.', ',') : '-';
            }

            $row[] = array(
                'index' => $index,
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'nofp' => $d->nofp ?? '-',
                'nokk' => $d->nokk ?? '-',
                'totqty' => number_format($d->totqty, 0, '.', ','),
                'pricemat' => $d->pricemat !== null ? '$' . number_format($d->pricemat, 3, '.', ',') : '-',
                // 'priceppn' => $d->priceppn !== null ? '$' . number_format($d->priceppn, 3, '.', ',') : '-',
                'ppn' => $d->ppn ? $d->ppn . '%' : '-',
                'kursppn' => $d->kursp ? number_format($d->kursp, 0, '.', ',') : '-',
                'qty' => number_format($d->qty, 0, '.', ','),
                // 'pricedt' => '$' . ' ' . $d->pricedt, 3, '.', ',',
                // 'amountlistpay' => '$' . ' ' . number_format(($d->qty * round($d->pricedt, 3)), 0, '.', ','),
                // 'totamountinv' => '$' . ' ' . number_format((($d->qty * round($d->pricedt, 3)) * 1.11), 3, '.', ','),
                'pricedt' => $dt_pricedt,
                'amountlistpay' => $dt_amountlistpay,
                'totamountinv' => $dt_totamountinv,

            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    // public function export_kwitansi(Request $request)
        // {
        //     $tahun = date("Y");
        //     $page = $request->input('page') ?? '1';
        //     $rows = $request->input('rows') ?? '100';
        //     $searchByInput = $request->searchByInput;
        //     $filterByMonth = $request->filterByMonth;
        //     $filterByYear = $request->filterByYear;

        //     if (!empty($filterByYear)) $tahun = $filterByYear;

        //     $dt_downtime = DB::table('invoice')
        //         ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
        //         ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
        //         ->select(
        //             'invoice.invpk',
        //             'invoice.spbpk',
        //             'spb.nobukti',
        //             'invoice.noinv',
        //             'invoice.nokk',
        //             'invoice.totqty',
        //             'invoice.pricemat',
        //             'invoice.ppn',
        //             'invoice.kursp',
        //             'spb.spbpk',
        //             'gud.gudpk',
        //             'gud.gudnm',
        //             DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
        //         );

        //     if (!empty($searchByInput)) {
        //         $concatenatedValue = DB::raw("CONCAT(
        //             IFNULL(invoice.noinv, ''),
        //             IFNULL(invoice.nokk, ''),
        //             IFNULL(spb.nobukti, ''),
        //             IFNULL(invoice.totqty, ''),
        //             IFNULL(invoice.pricemat, ''),
        //             IFNULL(invoice.ppn, ''),
        //             IFNULL(invoice.kursp, ''),
        //             IFNULL(gud.gudnm, '')
        //         )");
        //         $dt_downtime = $dt_downtime->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        //     }


        //     if (!empty($filterByMonth)) {
        //         $dt_downtime = $dt_downtime->whereMonth('invoice.tglinv', '=', $filterByMonth);
        //     }

        //     if (!empty($filterByYear)) {
        //         $dt_downtime = $dt_downtime->whereYEAR('invoice.tglinv', '=', $tahun);
        //     }

        //     $dtmesinAll = $dt_downtime->get();

        //     $offset = ($page - 1) * $rows;
        //     $dt_downtime = $dt_downtime->skip($offset)->take($rows)->get();

        //     $result = array();

        //     $result['total'] = $dtmesinAll->count();
        //     $result['page'] = $page;
        //     $result['rows'] = $rows;
        //     $result['offset'] = $offset;
        //     $row = array();

        //     $index = 1;

        //     foreach ($dt_downtime as $d) {

        //         $current_time = Carbon::now()->format('d-M-y H-i-s');

        //         $row[] = array(
        //             'ppn' => 11,
        //             'nokk' => $d->nokk,
        //             'gudnm' => $d->gudnm,
        //             'noinv' => $d->noinv,
        //             'style' => 'style',
        //             'qty' => number_format($d->totqty, 0, '.', ','),
        //             'pricemat' => number_format($d->pricemat, 3, '.', ','),
        //             'amount' => number_format($d->totqty *  $d->pricemat, 3, '.', ','),
        //         );
        //         $index++;
        //     }

        //     return Excel::download(new KwitansiExport($row), 'Kwitansi (' . $current_time . ').xlsx');
    // }

    public function export_kwitansi_by_modal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SelectGudangKwitansi' => 'required|string',
            // 'SelectStartKwitansi' => ['nullable', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            // 'SelectFinishKwitansi' => ['nullable', 'regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'SelectStartKwitansi' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'SelectFinishKwitansi' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $page = $request->input('page', 1);
        $rows = $request->input('rows', 100);
        $SelectGudangKwitansi = $request->input('SelectGudangKwitansi');
        $SelectStartKwitansi = $request->input('SelectStartKwitansi');
        $SelectFinishKwitansi = $request->input('SelectFinishKwitansi');
        $searchByInputOnModalExportExcel = $request->searchByInputOnModalExportExcel;


        // Query utama
        $query = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.cur',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'invoice.nofp',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                'gud.alamat',
                'invoicedt.mpo',
                'invoicedt.qty',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )->where('invoice.nokk', '!=', null);

        $query->where('gud.gudpk', $SelectGudangKwitansi);

        if (!empty($searchByInputOnModalExportExcel)) {
            $concatenatedValue = DB::raw("CONCAT(spb.nobukti,'',invoice.noinv,'',invoice.nokk,'')");
            $query = $query->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInputOnModalExportExcel) . '%');
        }

        if (!empty($SelectStartKwitansi) && !empty($SelectFinishKwitansi)) {
            $formattedDateStart = Carbon::parse($SelectStartKwitansi)->format('Y-m-d');
            $formattedDateFinish = Carbon::parse($SelectFinishKwitansi)->format('Y-m-d');
            $query->whereBetween('invoice.tglinv', [$formattedDateStart, $formattedDateFinish]);
        }

        // Pagination
        $offset = ($page - 1) * $rows;
        $data = $query->skip($offset)->take($rows)->get();

        // Format data untuk export
        $formattedData = [];
        foreach ($data as $d) {

            $getdtinvdt = DB::table('invoicedt')
                ->select('invpk', 'mstyle', 'pricedt')
                ->where('invpk', '=', $d->invpk)
                ->whereNotNull('mstyle')
                ->first();

            $getMstyle = $getdtinvdt->mstyle ?? null;
            $forPricePpn = $getdtinvdt->pricedt ?? null;

            $dt_cur = 'Rp';
            if($d->cur == 1){
                $dt_cur = '$';
            }

            $formattedData[] = [
                'ppn' => 11,
                'nokk' => $d->nokk,
                'gudnm' => $d->gudnm,
                'alamat' => $d->alamat,
                'mpo' => $d->mpo,
                'noinv' => $d->noinv,
                'style' => $getMstyle,
                'cur' => $dt_cur,
                'qty' => number_format($d->qty, 0, '.', ','),
                'pricedt' => number_format($forPricePpn, 3, '.', ','),
                'amount' => number_format($d->qty * $forPricePpn, 3, '.', ','),
            ];
        }

        // Generate nama file dengan timestamp
        $current_time = Carbon::now()->format('d-M-y_H-i-s');
        $fileName = "Kwitansi_{$current_time}.xlsx";

        $gudang = DB::table('gud')->where('gudpk', $SelectGudangKwitansi)->first();
        $gudnm = $gudang ? $gudang->gudnm : 'Nama Gudang Tidak Ditemukan';
        $gudpk = $gudang ? $gudang->gudpk : 'Pk Gudang Tidak Ditemukan';
        $alamat = $gudang ? $gudang->alamat : 'Alamat Gudang Tidak Ditemukan';

        // Export ke Excel All(Kwitansi, Tagihan, Debit note)
        return Excel::download(new AllExportKwitansiDebitNoteTagihan($formattedData, $gudnm, $gudpk, $alamat), $fileName);
    }

    public function GetLookupDetailFinance(request $request)
    {
        // $tahun = date("Y");
        $searchByInput = $request->searchByInputOrder;
        $now2 = Carbon::now('Asia/Jakarta');
        $threeMonthsAgo = $now2->copy()->subMonths(6);
        $now2Formatted = $now2->format('Y-m-d'); // Format tanggal sekarang
        $threeMonthsAgoFormatted = $threeMonthsAgo->format('Y-m-d'); // Format 3 bulan ke belakang

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
                DB::raw('DATE_FORMAT(ord.datecreate, "%d %b %Y") as datecreate'),

            )
            ->where('ord.byrpk', '>', 0)
            ->where('ord.fupk', '>', 0)
            ->where('ord.ordqty', '>', 0);

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
                'ordqty' => $d->ordqty,
                'stage' => $d->stage,
                'datecreate' => $d->datecreate,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function AddLookupDetailFinance(Request $request)
    {
        $spbpk = $request->input('spbpk');
        $rows = $request->input('rows');
        $invpk = $request->input('invpk');

        $count = count($rows);
        $misal = [];
        $processedpodtpk = [];

        for ($i = 0; $i < $count; $i++) {

            $rowpodtpk = $rows[$i]['ordpk'] ?? null;
            // $ambilbyrpk = $rows[$i]['byrpk'] ?? null;
            $processedpodtpk[] = $rowpodtpk;

            // $get = DB::connection('mysql_sig')->table('byr')->where('byrpk', $ambilbyrpk)->first();
            // $get2 = DB::table('byr')->where('byrnm', $get->byrnm)->first();
            // $byrpk = $get2->byrpk ?? null;

            // $ambilinv = DB::table('invoice')->where('spbpk', $spbpk)->first();
            // $ambilinvpk = DB::table('invoice')->where('spbpk', $spbpk)->first();

            $get = DB::connection('mysql_sig')->table('bdown')
                ->where('ordpk', $rowpodtpk)
                ->where('jenis', '=', 'DETAIL')
                ->orderBy('bdownpk', 'DESC')->first();
            $poref = $get->poref ?? null;

            $getOrd = DB::connection('mysql_sig')->table('ord')->where('ordpk', $rowpodtpk)->first();
            $artno = $getOrd->artno ?? null;
            $prodes = $getOrd->prodes ?? null;

            DB::table('invoicedt')->insert([
                'invpk' => $invpk,
                // 'mpo' => $rows[$i]['prodes'] ?? null,
                'mpo' => $poref,
                'mstyle' => $artno,
                'mprodes' => $prodes,
                'qty' => $rows[$i]['ordqty'] ?? null,
            ]);
            $misal[] = "Processed row " . ($i + 1);
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Data processed successfully',
            'spbpk' => $spbpk,
            'rows' => $rows,
            'misal' => $misal,
            'processedDtgpk' => $processedpodtpk,
            // 'total_spbdt' => $total_spbdt,
        ]);
    }

    public function GetSpbForInvReq(Request $request)
    {
        $tahun = date("Y");
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInputReqInv = $request->searchByInputReqInv;
        $filterByMonthReqInv = $request->filterByMonthReqInv ?? date('m');
        $filterByYearReqInv = $request->filterByYearReqInv;
        $sortlistByDateReqInv = $request->sortlistByDateReqInv;


        if (!empty($filterByYearReqInv))
            $tahun = $filterByYearReqInv;

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
            );


        if (!empty($searchByInputReqInv)) {
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

            $data_spb = $data_spb->where($concatenatedValue, 'like', '%' . $searchByInputReqInv . '%');
        }

        if (!empty($filterByYearReqInv)) {
            $data_spb = $data_spb->whereYear('spb.tgl', '=', $filterByYearReqInv);
        } else {
            $data_spb = $data_spb->orderBy('nobukti', 'DESC')->whereYear('spb.tgl', '=', $tahun);
        }

        if (!empty($filterByMonthReqInv)) {
            $data_spb = $data_spb->whereMonth('spb.tgl', '=', $filterByMonthReqInv);
        }

        if ($sortlistByDateReqInv == 11) {
            $data_spb = $data_spb->orderBy('spb.nobukti', 'desc');
        } elseif ($sortlistByDateReqInv == 12) {
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
                'nobukti' => $d->nobukti,
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

    public function AddReqInv(Request $request)
    {
        $spbpkList = $request->input('spbpk');
        $noinv = $request->input('noinv');

        if (!$spbpkList || !is_array($spbpkList) || empty($noinv)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid data received'
            ], 400);
        }

        // Cek apakah No. INV sudah ada di tabel invoice
        $existingInvoice = DB::table('invoice')->where('noinv', $noinv)->exists();

        if ($existingInvoice) {
            return response()->json([
                'status' => 'error',
                'message' => 'No. INV sudah terdaftar, gunakan nomor yang berbeda!'
            ], 400);
        }

        // Jika tidak duplikat, lakukan insert ke dalam database
        foreach ($spbpkList as $spbpk) {
            DB::table('invoice')->insert([
                'spbpk' => $spbpk,
                'tglinv' => now(),
                'noinv' => $noinv,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Request Invoice Created Successfully',
            'spbpk' => $spbpkList,
        ]);
    }

    public function GetDataModalKwitansi(Request $request)
    {

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $SelectGudangKwitansi = $request->SelectGudangKwitansi;
        $SelectStartKwitansi = $request->SelectStartKwitansi;
        $SelectFinishKwitansi = $request->SelectFinishKwitansi;
        $searchByInputOnModalExportExcel = $request->searchByInputOnModalExportExcel;

        $data_cmt = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.cur',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'invoicedt.qty',
                'invoicedt.pricedt',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )
            ->where('invoice.nokk', '!=', '')
            ->where('invoice.noinv', '!=', '')
            ->where('invoice.totqty', '>=', 1)
            ->orderBy('invoice.invpk', 'DESC');


        if (!empty($SelectGudangKwitansi)) {
            $data_cmt = $data_cmt->where('gud.gudpk', $SelectGudangKwitansi);
        }

        if (!empty($searchByInputOnModalExportExcel)) {
            $concatenatedValue = DB::raw("CONCAT(spb.nobukti,'',invoice.noinv,'',invoice.nokk,'')");
            $data_cmt = $data_cmt->where(DB::raw($concatenatedValue), 'like', '%' . strtolower($searchByInputOnModalExportExcel) . '%');
        }

        if (!empty($SelectStartKwitansi) && !empty($SelectFinishKwitansi)) {
            $formattedDateStart = Carbon::createFromFormat('d/m/Y', $SelectStartKwitansi)->format('Y-m-d');
            $formattedDateFinish = Carbon::createFromFormat('d/m/Y', $SelectFinishKwitansi)->format('Y-m-d');

            $data_cmt->whereBetween('invoice.tglinv', [$formattedDateStart, $formattedDateFinish]);
        }

        $AllDataCmt = $data_cmt->get();

        $offset = ($page - 1) * $rows;
        $data_cmt = $data_cmt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_cmt as $d) {

            if($d->cur == 1){
                $dt_pricemat = $d->pricemat !== null ? '$' . number_format($d->pricemat, 3, '.', ',') : '-';
            }else{
                $dt_pricemat = $d->pricemat !== null ? 'Rp ' . number_format($d->pricemat, 3, '.', ',') : '-';
            }

            $row[] = array(
                'index' => $index,
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'nofp' => $d->nofp ?? '-',
                'nokk' => $d->nokk ?? '-',
                'totqty' => number_format($d->totqty, 0, '.', ','),
                'pricemat' => $dt_pricemat,
                'ppn' => $d->ppn ? $d->ppn . '%' : '-',
                'kursppn' => $d->kursp ? number_format($d->kursp, 0, '.', ',') : '-',
                'qty' => number_format($d->qty, 0, '.', ','),
                'pricedt' => '$' . ' ' . $d->pricedt, 3, '.', ',',
                'amountlistpay' => '$' . ' ' . number_format(($d->qty * round($d->pricedt, 3)), 0, '.', ','),
                'totamountinv' => '$' . ' ' . number_format((($d->qty * round($d->pricedt, 3)) * 1.11), 3, '.', ','),
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function GetExportDebitNote(Request $request)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $SelectGudangDebitNote = $request->SelectGudangDebitNote;
        $SelectStartDebitNote = $request->SelectStartDebitNote;
        $SelectFinishDebitNote = $request->SelectFinishDebitNote;

        $data_cmt = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                'gud.alamat',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )
            ->where('invoice.nokk', '!=', '')
            ->where('invoice.noinv', '!=', '')
            ->orderBy('invoice.invpk', 'DESC');


        if (!empty($SelectGudangDebitNote)) {
            $data_cmt = $data_cmt->where('gud.gudpk', $SelectGudangDebitNote);
        }

        if ($SelectStartDebitNote && $SelectFinishDebitNote) {
            $data_cmt->whereBetween('invoice.tglinv', [$SelectStartDebitNote, $SelectFinishDebitNote]);
        }

        $AllDataCmt = $data_cmt->get();

        $offset = ($page - 1) * $rows;
        $data_cmt = $data_cmt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_cmt as $d) {
            $row[] = array(
                'index' => $index,
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'nokk' => $d->nokk ?? '-',
                'totqty' => number_format($d->totqty, 0, '.', ','),
                'pricemat' => $d->pricemat !== null ? '$' . number_format($d->pricemat, 3, '.', ',') : '-',
                'ppn' => $d->ppn ? $d->ppn . '%' : '-',
                'kursppn' => $d->kursp ? number_format($d->kursp, 0, '.', ',') : '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function export_debitnote_by_modal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SelectGudangDebitNote' => 'required|string',
            'SelectStartDebitNote' => 'nullable|date',
            'SelectFinishDebitNote' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $page = $request->input('page', 1);
        $rows = $request->input('rows', 100);
        $SelectGudangDebitNote = $request->input('SelectGudangDebitNote');
        $SelectStartDebitNote = $request->input('SelectStartDebitNote');
        $SelectFinishDebitNote = $request->input('SelectFinishDebitNote');

        $query = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                'gud.alamat',
                'invoicedt.mpo',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            );

        $query->where('gud.gudpk', $SelectGudangDebitNote);

        if ($SelectStartDebitNote && $SelectFinishDebitNote) {
            $query->whereBetween('invoice.tglinv', [$SelectStartDebitNote, $SelectFinishDebitNote]);
        }

        $offset = ($page - 1) * $rows;
        $data = $query->skip($offset)->take($rows)->get();

        $formattedData = [];
        foreach ($data as $d) {

            $getdtinvdt = DB::table('invoicedt')
                ->select('invpk', 'mstyle', 'pricedt')
                ->where('invpk', '=', $d->invpk)
                ->whereNotNull('mstyle')
                ->first();

            $getMstyle = $getdtinvdt->mstyle ?? null;
            $forPricePpn = $getdtinvdt->pricedt ?? null;

            $formattedData[] = [
                'ppn' => 11,
                'nokk' => $d->nokk,
                'gudnm' => $d->gudnm,
                'alamat' => $d->alamat,
                'mpo' => $d->mpo,
                'noinv' => $d->noinv,
                'style' => $getMstyle,
                'qty' => number_format($d->totqty, 0, '.', ','),
                'pricedt' => number_format($forPricePpn, 3, '.', ','),
                'amount' => number_format($d->totqty * $forPricePpn, 3, '.', ','),
            ];
        }

        $current_time = Carbon::now()->format('d-M-y_H-i-s');
        $fileName = "Debit_Note_{$current_time}.xlsx";

        $gudang = DB::table('gud')->where('gudpk', $SelectGudangDebitNote)->first();
        $gudnm = $gudang ? $gudang->gudnm : 'Nama Gudang Tidak Ditemukan';
        $alamat = $gudang ? $gudang->alamat : 'Alamat Gudang Tidak Ditemukan';


        return Excel::download(new DebitNoteExport($formattedData, $gudnm, $alamat), $fileName);
    }


    public function GetExportTagihan(Request $request)
    {
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $SelectGudangTagihan = $request->SelectGudangTagihan;
        $SelectStartTagihan = $request->SelectStartTagihan;
        $SelectFinishTagihan = $request->SelectFinishTagihan;

        $data_cmt = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )
            ->where('invoice.nokk', '!=', '')
            ->where('invoice.noinv', '!=', '')
            ->orderBy('invoice.invpk', 'DESC');


        if (!empty($SelectGudangTagihan)) {
            $data_cmt = $data_cmt->where('gud.gudpk', $SelectGudangTagihan);
        }

        if ($SelectStartTagihan && $SelectFinishTagihan) {
            $data_cmt->whereBetween('invoice.tglinv', [$SelectStartTagihan, $SelectFinishTagihan]);
        }

        $AllDataCmt = $data_cmt->get();

        $offset = ($page - 1) * $rows;
        $data_cmt = $data_cmt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_cmt as $d) {
            $row[] = array(
                'index' => $index,
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'nokk' => $d->nokk ?? '-',
                'totqty' => number_format($d->totqty, 0, '.', ','),
                'pricemat' => $d->pricemat !== null ? '$' . number_format($d->pricemat, 3, '.', ',') : '-',
                'ppn' => $d->ppn ? $d->ppn . '%' : '-',
                'kursppn' => $d->kursp ? number_format($d->kursp, 0, '.', ',') : '-',
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function export_tagihan_by_modal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SelectGudangTagihan' => 'required|string',
            'SelectStartTagihan' => 'nullable|date',
            'SelectFinishTagihan' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $page = $request->input('page', 1);
        $rows = $request->input('rows', 100);
        $SelectGudangTagihan = $request->input('SelectGudangTagihan');
        $SelectStartTagihan = $request->input('SelectStartTagihan');
        $SelectFinishTagihan = $request->input('SelectFinishTagihan');

        $query = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.ppn',
                'invoice.kursp',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                'invoicedt.mpo',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            );

        $query->where('gud.gudpk', $SelectGudangTagihan);

        if ($SelectStartTagihan && $SelectFinishTagihan) {
            $query->whereBetween('invoice.tglinv', [$SelectStartTagihan, $SelectFinishTagihan]);
        }


        $offset = ($page - 1) * $rows;
        $data = $query->skip($offset)->take($rows)->get();

        $formattedData = [];
        foreach ($data as $d) {

            $getdtinvdt = DB::table('invoicedt')
                ->select('invpk', 'mstyle', 'pricedt')
                ->where('invpk', '=', $d->invpk)
                ->whereNotNull('mstyle')
                ->first();

            $getMstyle = $getdtinvdt->mstyle ?? null;
            $forPricePpn = $getdtinvdt->pricedt ?? null;

            $formattedData[] = [
                'ppn' => 11,
                'nokk' => $d->nokk,
                'gudnm' => $d->gudnm,
                'noinv' => $d->noinv,
                'mpo' => $d->mpo,
                'style' => $getMstyle,
                'qty' => number_format($d->totqty, 0, '.', ','),
                'pricedt' => number_format($forPricePpn, 3, '.', ','),
                'amount' => number_format($d->totqty * $forPricePpn, 3, '.', ','),
            ];
        }

        $current_time = Carbon::now()->format('d-M-Y H-i-s'); // Format dengan spasi
        $fileName = "Tagihan $current_time.xlsx";

        $gudang = DB::table('gud')->where('gudpk', $SelectGudangTagihan)->first();
        $gudnm = $gudang ? $gudang->gudnm : 'Nama Gudang Tidak Ditemukan';

        return Excel::download(new TagihanExport($formattedData, $gudnm), $fileName);
    }

    public function pdf_list_payment_by_modal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SelectGudang' => 'required|string',
            'SelectStart' => 'nullable|date',
            'SelectFinish' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $SelectGudang = $request->input('SelectGudang');
        $SelectStart = $request->input('SelectStart');
        $SelectFinish = $request->input('SelectFinish');

        $query = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.nofp',
                'invoicedt.qty',
                'invoicedt.pricedt',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            );

        $query->where('gud.gudpk', $SelectGudang);

        if (!empty($SelectStart) && !empty($SelectFinish)) {
            $formattedDateStart = Carbon::parse($SelectStart)->format('Y-m-d');
            $formattedDateFinish = Carbon::parse($SelectFinish)->format('Y-m-d');

            $query->whereBetween('invoice.tglinv', [$formattedDateStart, $formattedDateFinish]);
        }

        $data = $query->get();
        $formattedData = [];

        foreach ($data as $index => $d) {
            $formattedData[] = [
                'index' => $index + 1,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'nofp' => $d->nofp ?? '-',
                'nokk' => $d->nokk ?? '-',
                'amountlistpay' => ($d->qty * round($d->pricedt, 3)),
                'totamountinv' => (($d->qty * round($d->pricedt, 3)) * 1.11),
                // 'totamountinv' => '$' . (($d->qty * round($d->pricedt, 3))  * 1.11),

            ];
        }

        $gudang = DB::table('gud')->where('gudpk', $SelectGudang)->first();
        $gudnm = $gudang ? $gudang->gudnm : 'Nama Gudang Tidak Ditemukan';
        $current_time = Carbon::now()->format('d-M-Y H-i-s'); // Format dengan spasi

        // $pdf = Pdf::loadView('menu.finance.pdf-list-payment', compact('formattedData', 'gudnm'));
        // return $pdf->download("List Payment {$current_time}.pdf");

        $pdf = Pdf::loadView('menu.finance.pdf-list-payment', compact('formattedData', 'gudnm'))->setPaper('a4', 'landscape');
        return $pdf->stream('List-Payment.pdf');
    }

    public function GetDataModalExportListShipment(Request $request)
    {

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $SelectGudangExport = $request->SelectGudangExport;
        $SelectStartExport = $request->SelectStartExport;
        $SelectFinishExport = $request->SelectFinishExport;

        $data_cmt = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.cur',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'invoicedt.qty',
                'invoicedt.pricedt',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )
            ->where('invoice.nokk', '!=', '')
            ->where('invoice.noinv', '!=', '')
            ->where('invoice.totqty', '>=', 1)
            ->orderBy('invoice.invpk', 'DESC');


        if (!empty($SelectGudangExport)) {
            $data_cmt = $data_cmt->where('gud.gudpk', $SelectGudangExport);
        }

        // if ($SelectStartKwitansi && $SelectFinishKwitansi) {
        //     $data_cmt->whereBetween('invoice.tglinv', [$SelectStartKwitansi, $SelectFinishKwitansi]);
        // }

        if (!empty($SelectStartExport) && !empty($SelectFinishExport)) {
            $formattedDateStart = Carbon::createFromFormat('d/m/Y', $SelectStartExport)->format('Y-m-d');
            $formattedDateFinish = Carbon::createFromFormat('d/m/Y', $SelectFinishExport)->format('Y-m-d');

            $data_cmt->whereBetween('invoice.tglinv', [$formattedDateStart, $formattedDateFinish]);
        }

        $AllDataCmt = $data_cmt->get();

        $offset = ($page - 1) * $rows;
        $data_cmt = $data_cmt->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataCmt->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_cmt as $d) {

            if($d->cur == 1){
                $dt_pricedt = $d->pricedt !== null ? '$' . number_format($d->pricedt, 3, '.', ',') : '-';
                $dt_amount = ($d->totqty * round($d->pricedt, 3)) != 0 ? '$ ' . number_format(($d->totqty * round($d->pricedt, 3)), 0, '.', ',') : '-';
            }else{
                $dt_pricedt = $d->pricedt !== null ? 'Rp ' . number_format($d->pricedt, 0, '.', ',') : '-';
                $dt_amount = ($d->totqty * round($d->pricedt, 3)) != 0 ? 'Rp ' .number_format(($d->totqty * round($d->pricedt, 3)), 0, '.', ',') : '-';
            }

            $row[] = array(
                'index' => $index,
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'totqty' => number_format($d->totqty, 0, '.', ','),
                // 'pricedt' => number_format($d->pricedt, 0, '.', ','),
                'pricedt' => $dt_pricedt,
                // 'amount' => number_format(($d->totqty * round($d->pricedt, 3)), 0, '.', ','),
                'amount' => $dt_amount,

            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }

    public function export_list_shipment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'SelectGudangExport' => 'required|string',
            'SelectStartExport' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
            'SelectFinishExport' => ['nullable', 'regex:/^\d{4}-\d{2}-\d{2}$/'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        $page = $request->input('page', 1);
        $rows = $request->input('rows', 100);
        $SelectGudangExport = $request->input('SelectGudangExport');
        $SelectStartExport = $request->input('SelectStartExport');
        $SelectFinishExport = $request->input('SelectFinishExport');

        // Query utama
        $query = DB::table('invoice')
            ->leftJoin('spb', 'spb.spbpk', '=', 'invoice.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('invoicedt', 'invoicedt.invpk', '=', 'invoice.invpk')
            ->select(
                'invoice.invpk',
                'invoice.spbpk',
                'spb.nobukti',
                'invoice.noinv',
                'invoice.nokk',
                'invoice.totqty',
                'invoice.cur',
                'invoice.pricemat',
                'invoice.ppn',
                'invoice.kursp',
                'invoicedt.qty',
                'invoicedt.pricedt',
                'spb.spbpk',
                'gud.gudpk',
                'gud.gudnm',
                DB::raw('DATE_FORMAT(invoice.tglinv, "%d %b %Y") as tanggal'),
            )
            ->where('invoice.nokk', '!=', '')
            ->where('invoice.noinv', '!=', '')
            ->where('invoice.totqty', '>=', 1)
            ->orderBy('invoice.invpk', 'DESC');;

        $query->where('gud.gudpk', $SelectGudangExport);

        if (!empty($SelectStartExport) && !empty($SelectFinishExport)) {
            $formattedDateStart = Carbon::parse($SelectStartExport)->format('Y-m-d');
            $formattedDateFinish = Carbon::parse($SelectFinishExport)->format('Y-m-d');
            $query->whereBetween('invoice.tglinv', [$formattedDateStart, $formattedDateFinish]);
        }

        // Pagination
        $offset = ($page - 1) * $rows;
        $data = $query->skip($offset)->take($rows)->get();

        // Format data untuk export
        $formattedData = [];
        foreach ($data as $d) {

            if($d->cur == 1){
                $dt_pricedt = $d->pricedt !== null ? '$' . number_format($d->pricedt, 3, '.', ',') : '-';
                $dt_amount = ($d->totqty * round($d->pricedt, 3)) != 0 ? '$ ' . number_format(($d->totqty * round($d->pricedt, 3)), 0, '.', ',') : '-';
            }else{
                $dt_pricedt = $d->pricedt !== null ? 'Rp ' . number_format($d->pricedt, 0, '.', ',') : '-';
                $dt_amount = ($d->totqty * round($d->pricedt, 3)) != 0 ? 'Rp ' .number_format(($d->totqty * round($d->pricedt, 3)), 0, '.', ',') : '-';
            }

            $formattedData[] = [
                'invpk' => $d->invpk,
                'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'gudang' => $d->gudnm,
                'noinv' => $d->noinv,
                'nobukti' => $d->nobukti,
                'totqty' => number_format($d->totqty, 0, '.', ','),
                // 'pricedt' => number_format($d->pricedt, 3, '.', ','),
                // 'amount' => number_format(($d->totqty * round($d->pricedt, 3)), 0, '.', ','),
                'pricedt' => $dt_pricedt,
                'amount' => $dt_amount,
            ];
        }

        // Generate nama file dengan timestamp
        $current_time = Carbon::now()->format('d-M-y_H-i-s');
        $fileName = "Export-list-shipment-{$current_time}.xlsx";
        return Excel::download(new ExportListShipment($formattedData), $fileName);
    }
}
