<?php

namespace App\Http\Controllers\Api\master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Byr;

class M_ByrController extends Controller
{
    public function get_all(Request $request) {
        $byr = Byr::orderBy('byrnm', 'asc');

        if (!empty($request->get('q'))) {
            $concatenatedValue = DB::raw("CONCAT(
                IFNULL(byr.byrnm, '')
            )");

            $byr = $byr->where(DB::raw($concatenatedValue), 'like', '%' . $request->get('q') . '%');
        }

        $byr = $byr->get();

        return response()->json($byr);
    }
}
