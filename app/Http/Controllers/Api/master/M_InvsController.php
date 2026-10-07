<?php

namespace App\Http\Controllers\Api\master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Invs;

class M_InvsController extends Controller
{
    public function get_all(Request $request)
    {
        $result = array();
        $searchByInput = $request->searchByInput;
        $filterByByrpk = $request->filterByByrpk;
        $isNullDbnotepk = $request->isNullDbnotepk;

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
            'invs.noinvs',
            'invs.season',
            'invs.brand',
            'byr.byrnm',
            'invs.shipno',
            'shipto.shiptonm',
            'shipto.byrpk',
            'invs.invspk',
            'invs.shiptopk',
        )
        ->groupBy('invs.invspk')
        ->where('invs.stsinvs', '!=', 1)
        ->whereNotNull('invs.noinvs')
        ->where('invs.noinvs', '!=', '');

        if (!empty($isNullDbnotepk)) {
            $query_invs = $query_invs->whereNull('invs.dbnotepk'); 
        }

        if (!empty($filterByByrpk)) {
            $query_invs = $query_invs->where('shipto.byrpk', $filterByByrpk);
        }

        if (!empty($searchByInput)) {
            $concatenatedValue = DB::raw("CONCAT(
                IFNULL(invs.noinvs, ''),
                IFNULL(invs.shipno, ''),
                IFNULL(byr.byrnm, ''),
                IFNULL(shipto.shiptonm, '')
            )");
            $query_invs = $query_invs->where(DB::raw($concatenatedValue), 'like', '%' . $searchByInput . '%');
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
                'invspk' => $d->invspk,
                'shiptopk' => $d->shiptopk,
                'byrpk' => $d->byrpk,
                'noinvs' => $d->noinvs,
                'tglinvs' => $d->tglinvs,
                'sendate' => $d->sendate,
                'byrnm' => $d->byrnm,
                'shipno' => $d->shipno,
                'shiptonm' => $d->shiptonm,
                'tqty' => $d->qty,
                'season' => $d->season,
                'brand' => $d->brand,
            );
        }

        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }
    
    public function get_year()
    {
        $year = Invs::selectRaw('YEAR(tglinvs) as year')
        ->groupBy('year')
        ->orderBy('year', 'desc')
        ->get();

        return response()->json($year);
    }
}
