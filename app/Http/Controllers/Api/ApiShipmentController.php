<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiShipmentController extends Controller
{
    public function GetShipment(Request $request)
    {
        $tahun = date("Y");
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $searchByInput = $request->searchByInput;
        $filterByMonth = $request->filterByMonth ?? date('m');
        $filterByYear = $request->filterByYear;
        $sortlistByDate = $request->sortlistByDate;

        if (!empty($filterByYear)) $tahun = $filterByYear;

        $data_shipment = DB::table('spbdt')
            ->leftJoin('byr', 'byr.byrpk', '=', 'spbdt.byrpk')
            ->leftJoin('spb', 'spb.spbpk', '=', 'spbdt.spbpk')
            ->leftJoin('gud', 'gud.gudpk', '=', 'spb.gudpk')
            ->leftJoin('fwd', 'fwd.fwdpk', '=', 'spb.fwdpk')
            ->leftJoin('emkl', 'emkl.emklpk', '=', 'spb.emklpk')
            ->select(
                'spbdt.spbdtpk',
                'spbdt.spbpk',
                'spbdt.noop',
                'byr.byrpk',
                'byr.byrnm',
                'spbdt.negara',
                'spbdt.orderno',
                'spbdt.jmlsatuan',
                'spbdt.jmlkarton',
                'fwd.fwdpk',
                'fwd.fwdnm',
                'gud.gudpk',
                'gud.gudnm',
                'emkl.emklpk',
                'emkl.emklnm',
                'spb.contno',
                'spb.sealno',
                'spb.tgl',
                DB::raw('DATE_FORMAT(spb.tgl, "%d %b %Y") as tanggal'),
            );

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                COALESCE(spbdt.noop, ''),
                COALESCE(byr.byrnm, ''),
                COALESCE(spbdt.negara, ''),
                COALESCE(spbdt.orderno, ''),
                COALESCE(spbdt.jmlsatuan, ''),
                COALESCE(spbdt.jmlkarton, ''),
                COALESCE(fwd.fwdnm, ''),
                COALESCE(gud.gudnm, ''),
                COALESCE(spb.contno, ''),
                COALESCE(spb.sealno, '')
            )");
            $data_shipment = $data_shipment->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if ($sortlistByDate == 11) {
            $data_shipment = $data_shipment->whereYear('spb.tgl', $tahun)->orderBy('spb.tgl', 'desc');
        } else if ($sortlistByDate == 12) {
            $data_shipment = $data_shipment->whereYear('spb.tgl', $tahun)->orderBy('spb.tgl', 'asc');
        } else {
            $data_shipment = $data_shipment->whereYear('spb.tgl', $tahun)->orderBy('spbdt.spbdtpk', 'DESC')->orderBy('spb.tgl', 'desc');
        }

        if (!empty($filterByMonth)) {
            $data_shipment = $data_shipment->whereMonth('spb.tgl', '=', $filterByMonth);
        }

        if (!empty($filterByYear)) {
            $data_shipment = $data_shipment->whereYEAR('spb.tgl', '=', $tahun);
        }

        $AllDataShipment = $data_shipment->get();

        $offset = ($page - 1) * $rows;
        $data_shipment = $data_shipment->skip($offset)->take($rows)->get();

        $result = array();
        $result['total'] = $AllDataShipment->count();
        $result['page'] = $page;
        $result['rows'] = $rows;
        $result['offset'] = $offset;
        $row = array();
        $index = $offset + 1;

        foreach ($data_shipment as $d) {
            $cleanNoop = trim($d->noop);
            $cleanOrderNo = trim($d->orderno);
            $cleanByrName = trim($d->byrnm);
            $cleanNegara = trim($d->negara);
            $cleanGudang = trim($d->gudnm);
            $cleanFwd = trim($d->fwdnm);
            $cleanEmkl = trim($d->emklnm);

            $dtcontno = 'CONT. NO' . ' ' . $d->contno;
            if ($d->contno == null) {
                $dtcontno = '';
            }

            $dtsealno = 'SEAL. NO' . ' ' . $d->sealno;
            if ($d->sealno == null) {
                $dtsealno = '';
            }

            if($d->byrpk == null){
                $DtByr = '';
            }else{
                $DtByr = $cleanByrName . '-' . $cleanNegara;
            }

            $row[] = array(
                'index' => $index,
                // 'spbdtpk' => $d->spbdtpk,
                // 'spbpk' => $d->spbpk,
                'tanggal' => $d->tanggal,
                'est_ship' => '- -',
                'noop' => $cleanNoop,
                'buyer' => $DtByr,
                'orderno' => $cleanOrderNo,
                'qty' => number_format($d->jmlsatuan, 0, '.', ',') . ' PCS',
                'carton' => number_format($d->jmlkarton, 0, '.', ',') . ' CTN',
                'gudang' => $cleanGudang ?? '-',
                'forwarder' => $cleanFwd ?? '-',
                'emkl' => $cleanEmkl ?? '-',
                'truck' => $dtcontno . ' ' . $dtsealno,
            );
            $index++;
        }
        $result = array_merge($result, array('rows' => $row));
        return json_encode($result);
    }
}