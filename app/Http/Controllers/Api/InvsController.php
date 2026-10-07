<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InvsController extends Controller
{
    public function get_invs(Request $request)
    {
        $result = array();
        $searchByInput = $request->searchByInput;
        $filterByShipto = $request->filterByShipto;

        $page = $request->input('page') ?? 1;
        $rows = $request->input('rows') ?? 100;

        $query_invs = DB::table('invs')
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
            'invs.invspk'
        )
        ->groupBy('invs.invspk');

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                IFNULL(invs.noinvs, ''),
                IFNULL(invs.shipno, ''),
                IFNULL(byr.byrnm, ''),
                IFNULL(shipto.shiptonm, '')
            )");
            $query_invs = $query_invs->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
        }

        if (!empty($filterByShipto)) {
            $query_invs = $query_invs->where('shiptopk', $$filterByShipto);
        }

        $query_invs = $query_invs->orderBy('invs.tglinvs', 'asc');

        $result['total'] = $query_invs->get()->count();

        $offset = ($page - 1) * $rows;
        $dtinvs = $query_invs->skip($offset)->take($rows)->get();

        $row = array();
        $no = 0;

        foreach ($dtinvs as $d) {
            $no++;

            $row[] = array(
                'no' => $no,
                'noinvs' => $d->noinvs,
                'tglinvs' => $d->tglinvs,
                'sendate' => $d->sendate,
                'byrnm' => $d->byrnm,
                'shipno' => $d->shipno,
                'shiptonm' => $d->shiptonm,
                'tqty' => $d->qty
            );
        }

        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }
}
