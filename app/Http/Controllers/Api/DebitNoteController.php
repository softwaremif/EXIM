<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\GenerateNumber;
use App\Models\Listdb;
use Carbon\Carbon;

class DebitNoteController extends Controller
{
    public function list(Request $request) {
        $guserid = $request->guserid;
        $userpk = $request->userpk;
        $filterByMonth = $request->filterByMonth;
        $filterByYear = $request->filterByYear;
        $sortlistByDate = $request->sortlistByDate;

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        
        $month = $filterByMonth ?? date('m');
        $year = $filterByYear ?? date('Y');

        $row = array();
        $result = array();

        $query_dbnote = Listdb::leftJoin('dbnote', 'dbnote.dbnotepk', 'listdb.dbnotepk')
        ->where(DB::raw('YEAR(listdb.datecreate)'), $year)
        ->where(DB::raw('MONTH(listdb.datecreate)'), $month);

        if (!empty($userpk) && !empty($guserid) && $guserid != 'SPR' && $guserid != 'FIN') {
            $query_dbnote = $query_dbnote->where('listdb.userpk', $userpk)->orWhere('dbnote.userpk', $userpk);
        }
        
        if ($sortlistByDate == 11) {
            $query_dbnote = $query_dbnote->orderBy('listdb.nomor', 'asc');
        } else if ($sortlistByDate == 12) {
            $query_dbnote = $query_dbnote->orderBy('listdb.nomor', 'desc');
        }

        $result['total'] = $query_dbnote->count();

        $offset = ($page - 1) * $rows;

        $query_dbnote = $query_dbnote->offset($offset);
        $query_dbnote = $query_dbnote->limit($rows);

        $dbnote = $query_dbnote->get();

        $no = 1;
        foreach ($dbnote as $d) {
            
            $formatted_nomor = (strlen($d->nomor) < 3) ? str_pad($d->nomor, 3, '0', STR_PAD_LEFT) : $d->nomor;
            $dbno = $formatted_nomor . "/KEU/" . Carbon::parse($d->datecreate)->format('y');

            $row[] = array(
                'no' => $no,
                'listdbpk' => $d->listdb,
                'dbnotepk' => $d->dbnotepk,
                'nomor' => $d->nomor,
                'dbno' => $dbno,
                'jenis' => $d->jenis ?? null,
                'datecreate' => !empty($d->datecreate) ? Carbon::parse($d->datecreate)->format('d-m-Y') : null,
                'keterangan' => $d->keterangan,
                'karid' => $d->karid,
                'user' => ucwords(strtolower($d->user)),
            );
            $no++;
        }
        
        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }

    public function detail($listdbpk) {
        $listdb = Listdb::find($listdbpk);

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully',
            'data' => $listdb,
        ]);
    }

    public function store(Request $request) {
        $nomor = GenerateNumber::byDbnote();

        $listdb = new Listdb;
        $listdb->nomor = $nomor;
        $listdb->datecreate = now();
        $listdb->keterangan = $request->keterangan;
        $listdb->user = $request->user;
        $listdb->jenis = $request->jenis;
        if ($request->filled('userpk')) $listdb->userpk = $request->userpk;
        $listdb->save();

        return response()->json([
            'success' => true,
            'message' => 'Data created successfully',
            'data' => $listdb,
        ]);
    }

    public function update($listdbpk, Request $request) {
        $listdb = Listdb::find($listdbpk);
        $listdb->keterangan = $request->keterangan;
        $listdb->user = $request->user;
        $listdb->jenis = $request->jenis;
        $listdb->update();

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $listdb,
        ]);
    }

    public function destroy($listdbpk) {
        Listdb::find($listdbpk)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully',
            'listdbpk' => $listdbpk
        ]);
    }
}
