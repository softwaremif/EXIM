<?php

namespace App\Http\Controllers\Api\master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bill;

class M_BillController extends Controller
{
    public function get_all(Request $request) {
        $bill = Bill::orderBy('billnm', 'asc');

        if (!empty($request->get('q'))) {
            $concatenatedValue = DB::raw("CONCAT(
                IFNULL(bill.billnm, '')
            )");

            $bill = $bill->where(DB::raw($concatenatedValue), 'like', '%' . $request->get('q') . '%');
        }

        $bill = $bill->get();

        return response()->json($bill);
    }

    public function store(Request $request) {
        $bill = new Bill;
        $bill->billnm = $request->billnm;
        $bill->byrpk = $request->byrpk;
        $bill->alamat = $request->alamat;
        $bill->telepon = $request->telepon;
        $bill->negara = $request->negara;
        $bill->kontakbill = $request->kontakbill;
        $bill->save();

        return response()->json([
            'success' => true,
            'message' => 'Data added successfully',
            'data' => $bill,
            'request' => $request->all(),
        ], 200);
    }
}
