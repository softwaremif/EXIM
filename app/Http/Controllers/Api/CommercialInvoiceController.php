<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Helpers\Shipper;
use App\Helpers\GenerateNumber;
use App\Models\Dbnote;
use App\Models\Dbnotedt;
use App\Models\Invs;
use App\Models\Invsdt;
use App\Models\Shipto;
use App\Models\Byr;
use App\Models\Bill;
use App\Models\Listdb;
use Carbon\Carbon;

class CommercialInvoiceController extends Controller
{
    public function list(Request $request) {
        $fupkgis = $request->fupkgis;
        $guserid = $request->guserid;
        $filterByMonth = $request->filterByMonth;
        $filterByYear = $request->filterByYear;
        $sortlistByDate = $request->sortlistByDate;

        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        
        $month = $filterByMonth ?? date('m');
        $year = $filterByYear ?? date('Y');

        $row = array();
        $result = array();

        $query_dbnote = Dbnote::leftJoin('dbnotedt', 'dbnotedt.dbnotepk', '=', 'dbnote.dbnotepk')
        ->leftJoin('invs', 'invs.invspk', '=', 'dbnotedt.invspk')
        ->leftJoin('listdb', 'listdb.dbnotepk', '=', 'dbnotedt.dbnotepk')
        ->whereNotNull('invs.fupk')
        ->where(DB::raw('YEAR(tgldb)'), $year)
        ->where(DB::raw('MONTH(tgldb)'), $month)
        ->groupBy('dbnote.dbnotepk');

        if (!empty($fupkgis) && !empty($guserid) && $guserid == 'FU') {
            $query_dbnote = $query_dbnote->where('invs.fupk', $fupkgis);
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
            $invspkarr = Dbnotedt::where('dbnotepk', $d->dbnotepk)->pluck('invspk')->toArray();
            $sxspo = Invsdt::whereIn('invspk', $invspkarr)->pluck('pono')->unique()->toArray();

            $sxspoArray = array_map(function($item) {
                return explode(',', $item);
            }, $sxspo);
            
            $sxspoMerged = array_merge(...($sxspoArray ?: [[]]));
            $pobyr = implode(', ', $sxspoMerged) ?: '';

            $row[] = array(
                'no' => $no,
                'dbnotepk' => $d->dbnotepk,
                'dbno' => $d->dbno,
                'tgldb' => !empty($d->tgldb) ? Carbon::parse($d->tgldb)->format('d-m-Y') : null,
                'brand' => $d->brand,
                'alamatbill' => $d->alamatbill,
                'sxspo' => $sxspo,
                'pobyr' => $pobyr,
                // 'act' => $act
            );
            $no++;
        }
        
        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }

    public function list_detail($dbnotepk, Request $request) {
        $dbnote = Dbnote::find($dbnotepk);
        $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
        $invs = count($invspkarr) > 0 ? Invs::whereIn('invspk', $invspkarr)->whereNotNull('shiptopk')->orderBy('invspk', 'desc')->first() : null;
        $shipto = !empty($invs) ? Shipto::find($invs->shiptopk) : null;
        
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $result = array();
        $row = array();
        $sxspo = array();
        $tamount = 0;

        $invsdt = Invsdt::whereIn('invspk', $invspkarr);

        $result['total'] = $invsdt->count();

        $offset = ($page - 1) * $rows;

        $invsdt = $invsdt->offset($offset);
        $invsdt = $invsdt->limit($rows);

        $invsdt = $invsdt->get();

        $no = 1;
        foreach ($invsdt as $d) {
            $invs = Invs::find($d->invspk);

            if (!in_array(strval($d->pono), $sxspo)) array_push($sxspo, strval($d->pono));
            $amount = ($d->price ?? 0) * ($d->qty ?? 0);

            $row[] = array(
                'no' => $no,
                'invspk' => $d->invspk,
                'invsdtpk' => $d->invsdtpk,
                'noinvs' => $invs->noinvs,
                'po' => $d->pono,
                'style' => $d->style,
                'color' => $d->colnm,
                'desc' => $d->deskripsi,
                'product' => $d->itemnm,
                'destination' => $shipto->negara,
                'senddate' => !empty($invs->sendate) ? Carbon::parse($invs->sendate)->format('d-m-Y') : null,
                'qty' => $d->qty,
                'unit' => 'PCS',
                'price' => "$".($d->price ?? 0),
                'amount' => "$$amount",
                'shipno' => $invs->shipno,
            );
            $no++;
            $tamount += floatval($amount);
        }

        $result['sxspo'] = count($sxspo) > 0 ? implode(', ', $sxspo) : null;
        $result['season'] = $dbnote->season;
        $result['alamatbyr'] = $dbnote->alamatbyr;
        $result['kontakbyr'] = $dbnote->kontakbyr;
        $result['alamatbill'] = $dbnote->alamatbill;
        $result['tamount'] = $tamount;
        $result['shipto'] = $shipto;

        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }

    public function list_detail_perbaikan($dbnotepk, Request $request) {
        $dbnote = Dbnote::find($dbnotepk);
        $dbnotedts = Dbnotedt::where('dbnotepk', $dbnotepk)
            ->get(['invspk', 'dbnotedtpk']);

        $invspkarr = $dbnotedts->pluck('invspk')->toArray();

        // bikin mapping invspk => dbnotedtpk
        // $mapDbnote = $dbnotedts->pluck('dbnotedtpk', 'invspk')->toArray();
        $invs = count($invspkarr) > 0 ? Invs::whereIn('invspk', $invspkarr)->whereNotNull('shiptopk')->orderBy('invspk', 'desc')->first() : null;
        $shipto = !empty($invs) ? Shipto::find($invs->shiptopk) : null;
        
        $page = $request->input('page') ?? '1';
        $rows = $request->input('rows') ?? '100';
        $result = array();
        $row = array();
        $sxspo = array();
        $tamount = 0;

        // $invsdt = Invsdt::whereIn('invspk', $invspkarr);
        // $invsdt = Invsdt::select('invsdt.*', 'dbnotedt.dbnotedtpk')
        // ->join('invs', 'invs.invspk', '=', 'invsdt.invspk')
        // ->join('dbnotedt', 'dbnotedt.invspk', '=', 'invs.invspk')
        // ->where('dbnotedt.dbnotepk', $dbnotepk);

        $invsdt = Dbnotedt::select(
                'dbnotedt.dbnotedtpk',
                'invsdt.*'
            )
            ->leftJoin('invsdt', function ($join) {
                $join->on('dbnotedt.invsdtpk', '=', 'invsdt.invsdtpk')
                    ->orOn(function ($q) {
                        $q->on('dbnotedt.invspk', '=', 'invsdt.invspk')
                        ->whereNull('dbnotedt.invsdtpk');
                    });
            })
            ->where('dbnotedt.dbnotepk', $dbnotepk);

        // $invsdt = Invsdt::select('invsdt.*', 'dbnotedt.dbnotedtpk')
        //     ->join('dbnotedt', function ($join) {
        //         $join->on('dbnotedt.invsdtpk', '=', 'invsdt.invsdtpk')
        //             ->orOn('dbnotedt.invspk', '=', 'invsdt.invspk');
        //     })
        //     ->where('dbnotedt.dbnotepk', $dbnotepk);

        // $invsdt = Invsdt::select('invsdt.*', 'dbnotedt.dbnotedtpk')
        // ->join('dbnotedt', function ($join) {
        //     // $join->on('dbnotedt.invsdtpk', '=', 'invsdt.invsdtpk')
        //          $join->on('dbnotedt.invspk', '=', 'invsdt.invspk');
        // })
        // ->where('dbnotedt.dbnotepk', $dbnotepk)
        // ->groupBy('invsdt.invsdtpk');

        $result['total'] = $invsdt->count();

        $offset = ($page - 1) * $rows;

        $invsdt = $invsdt->offset($offset);
        $invsdt = $invsdt->limit($rows);

        $invsdt = $invsdt->get();

        $no = 1;
        foreach ($invsdt as $d) {
            $invs = Invs::find($d->invspk);

            if (!in_array(strval($d->pono), $sxspo)) array_push($sxspo, strval($d->pono));
            $amount = ($d->price ?? 0) * ($d->qty ?? 0);

            $row[] = array(
                'no' => $no,
                'invspk' => $d->invspk,
                'invsdtpk' => $d->invsdtpk,
                'dbnotedtpk' => $d->dbnotedtpk,
                'noinvs' => $invs->noinvs,
                'po' => $d->pono,
                'style' => $d->style,
                'color' => $d->colnm,
                'desc' => $d->deskripsi,
                'product' => $d->itemnm,
                'destination' => $shipto->negara,
                'senddate' => !empty($invs->sendate) ? Carbon::parse($invs->sendate)->format('d-m-Y') : null,
                'qty' => $d->qty,
                'unit' => 'PCS',
                'price' => "$".($d->price ?? 0),
                'amount' => "$$amount",
                'shipno' => $invs->shipno,
            );
            $no++;
            $tamount += floatval($amount);
        }

        $result['sxspo'] = count($sxspo) > 0 ? implode(', ', $sxspo) : null;
        $result['season'] = $dbnote->season;
        $result['alamatbyr'] = $dbnote->alamatbyr;
        $result['kontakbyr'] = $dbnote->kontakbyr;
        $result['alamatbill'] = $dbnote->alamatbill;
        $result['tamount'] = $tamount;
        $result['shipto'] = $shipto;

        $result = array_merge($result, ['rows' => $row]);
        return response()->json($result);
    }

    public function detail($dbnotepk) {
        $data = array();
        
        $dbnote = Dbnote::find($dbnotepk);
        $bill = !empty($dbnote->billpk) ? Bill::find($dbnote->billpk) : null;
        $byr = !empty($dbnote->byrpk) ? Byr::find($dbnote->byrpk) : null;
        $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
        $sxspo = Invsdt::whereIn('invspk', $invspkarr)->pluck('pono')->unique()->toArray();
        $invs = count($invspkarr) > 0 ? Invs::whereIn('invspk', $invspkarr)->whereNotNull('shiptopk')->orderBy('invspk', 'desc')->first() : null;
        $shipto = !empty($invs) ? Shipto::find($invs->shiptopk) : null;
        $shipper = Shipper::alamat(null);
        if (empty($byr)) $byr = !empty($shipto) ? Byr::find($shipto->byrpk) : null;

        $data['dbnote'] = $dbnote;
        $data['bill'] = $bill;
        $data['byr'] = $byr;
        $data['invs'] = $invs;
        $data['shipto'] = $shipto;
        $data['shipper'] = $shipper;
        $data['sxspo'] = !empty($dbnote->pobyr) ? $dbnote->pobyr : (count($sxspo) > 0 ? implode(', ',$sxspo) : null);

        return response()->json($data);
    }

    public function update($dbnotepk, Request $request) {
        $r_dtinvs = $request->has('dtinvs') ? json_decode($request->dtinvs) : array();
        
        $dbnote = Dbnote::find($dbnotepk);

        if ($request->filled('alamatbyr')) $dbnote->alamatbyr = $request->alamatbyr;
        if ($request->filled('kontakbyr')) $dbnote->kontakbyr = $request->kontakbyr;
        if ($request->filled('billpk')) $dbnote->billpk = $request->billpk;
        if ($request->filled('alamatbill')) $dbnote->alamatbill = $request->alamatbill;
        if ($request->filled('dbno')) $dbnote->dbno = $request->dbno;
        if ($request->filled('tgldb')) $dbnote->tgldb = $request->tgldb;
        if ($request->filled('season')) $dbnote->season = $request->season;
        if ($request->filled('brand')) $dbnote->brand = $request->brand;
        if ($request->filled('paydate')) $dbnote->paydate = $request->paydate;
        if ($request->filled('prepareby')) $dbnote->prepareby = $request->prepareby;
        if ($request->filled('sxspo')) $dbnote->pobyr = $request->sxspo;
        if ($request->filled('userpk')) $dbnote->userpk = $request->userpk;
        $dbnote->bankcharge = $request->bankcharge;

        $byrpk = $dbnote->byrpk ?? null;

        foreach ($r_dtinvs as $d) {
            if (!Dbnotedt::where('invspk', $d->invspk)->exists()) {
                $dbnotedt = new Dbnotedt;
                $dbnotedt->dbnotepk = $dbnotepk;
                $dbnotedt->invspk = $d->invspk;
                $dbnotedt->save();
            }

            Invs::where('invspk', $d->invspk)->update(['dbnotepk' => $dbnotepk]);

            $byr = !empty($d->byrpk) ? Byr::find($d->byrpk) : null;
            if (!empty($byr)) {
                if (empty(trim($dbnote->alamatbyr)) || $dbnote->byrpk != $d->byrpk) $dbnote->alamatbyr = trim($byr->alamattxt) ?? (trim($byr->alamat) ?? $dbnote->alamatbyr);
                if (empty(trim($dbnote->kontakbyr)) || $dbnote->byrpk != $d->byrpk) $dbnote->kontakbyr = trim($byr->kontakbyr) ?? $dbnote->kontakbyr;
                $dbnote->byrpk = $byr->byrpk;
                $byrpk = $byr->byrpk;
            }

            if (empty($dbnote->brand)) $dbnote->brand = $d->brand ?? $dbnote->brand;
            if (empty($dbnote->season)) $dbnote->season = $d->season ?? $dbnote->season;
        }

        $dbnote->dbnm = $dbnote->brand . " - " . $dbnote->season;

        $listdb = Listdb::where('dbnotepk', $dbnotepk)->first();
        if (empty($listdb)) {
            $year = now()->format('Y');
            $nomor = GenerateNumber::byDbnote();
            $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
            $funm = Invs::whereIn('invspk', $invspkarr)->whereNotNull('funm')->value('funm');

            $dbnote->dbno = $nomor . "/KEU/" . $year;

            $listdb = new Listdb;
            $listdb->dbnotepk = $dbnotepk;
            $listdb->nomor = $nomor;
            $listdb->datecreate = now();
            $listdb->keterangan = "commercial invoice shipment " . $dbnote->dbno;
            $listdb->user = $funm;
            $listdb->userpk = $request->userpk;
            $listdb->save();
        } else {
            $listdb = Listdb::find($listdb->listdb);
            $listdb->userpk = $request->userpk;
            $listdb->update();
        }

        $dbnote->update();

        $byr = !empty($byrpk) ? Byr::find($byrpk) : null;
        if (!empty($byr) && (empty($byr->alamat) || (!empty($byr->alamat) && $byr->alamat == ''))) {
            $byr->alamat = $request->alamatbyr;
            $byr->alamattxt = $request->alamatbyr;
            $byr->update();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dbnote,
            'r_dtinvs' => $r_dtinvs,
            'byr' => $byr,
        ]);
    }

    public function updatePerbaikan($dbnotepk, Request $request) {
        $r_dtinvs = $request->has('dtinvs') ? json_decode($request->dtinvs) : array();
        
        $dbnote = Dbnote::find($dbnotepk);

        if ($request->filled('alamatbyr')) $dbnote->alamatbyr = $request->alamatbyr;
        if ($request->filled('kontakbyr')) $dbnote->kontakbyr = $request->kontakbyr;
        if ($request->filled('billpk')) $dbnote->billpk = $request->billpk;
        if ($request->filled('alamatbill')) $dbnote->alamatbill = $request->alamatbill;
        if ($request->filled('dbno')) $dbnote->dbno = $request->dbno;
        if ($request->filled('tgldb')) $dbnote->tgldb = $request->tgldb;
        if ($request->filled('season')) $dbnote->season = $request->season;
        if ($request->filled('brand')) $dbnote->brand = $request->brand;
        if ($request->filled('paydate')) $dbnote->paydate = $request->paydate;
        if ($request->filled('prepareby')) $dbnote->prepareby = $request->prepareby;
        if ($request->filled('sxspo')) $dbnote->pobyr = $request->sxspo;
        if ($request->filled('userpk')) $dbnote->userpk = $request->userpk;
        $dbnote->bankcharge = $request->bankcharge;

        $byrpk = $dbnote->byrpk ?? null;

        foreach ($r_dtinvs as $d) {
            $invsdtList = Invsdt::where('invspk', $d->invspk)->get();

            // if (!Dbnotedt::where('invspk', $d->invspk)->exists()) {
            //     $dbnotedt = new Dbnotedt;
            //     $dbnotedt->dbnotepk = $dbnotepk;
            //     $dbnotedt->invspk = $d->invspk;
            //     $dbnotedt->save();
            // }

            foreach ($invsdtList as $item) {
                if (!Dbnotedt::where('invspk', $d->invspk)
                    ->where('invsdtpk', $item->invsdtpk)
                    ->exists()) {

                    $dbnotedt = new Dbnotedt;
                    $dbnotedt->dbnotepk = $dbnotepk;
                    $dbnotedt->invspk = $d->invspk;
                    $dbnotedt->invsdtpk = $item->invsdtpk;

                    $dbnotedt->save();
                }
            }

            Invs::where('invspk', $d->invspk)->update(['dbnotepk' => $dbnotepk]);

            $byr = !empty($d->byrpk) ? Byr::find($d->byrpk) : null;
            if (!empty($byr)) {
                if (empty(trim($dbnote->alamatbyr)) || $dbnote->byrpk != $d->byrpk) $dbnote->alamatbyr = trim($byr->alamattxt) ?? (trim($byr->alamat) ?? $dbnote->alamatbyr);
                if (empty(trim($dbnote->kontakbyr)) || $dbnote->byrpk != $d->byrpk) $dbnote->kontakbyr = trim($byr->kontakbyr) ?? $dbnote->kontakbyr;
                $dbnote->byrpk = $byr->byrpk;
                $byrpk = $byr->byrpk;
            }

            if (empty($dbnote->brand)) $dbnote->brand = $d->brand ?? $dbnote->brand;
            if (empty($dbnote->season)) $dbnote->season = $d->season ?? $dbnote->season;
        }

        $dbnote->dbnm = $dbnote->brand . " - " . $dbnote->season;

        $listdb = Listdb::where('dbnotepk', $dbnotepk)->first();
        if (empty($listdb)) {
            $year = now()->format('Y');
            $nomor = GenerateNumber::byDbnote();
            $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();
            $funm = Invs::whereIn('invspk', $invspkarr)->whereNotNull('funm')->value('funm');

            $dbnote->dbno = $nomor . "/KEU/" . $year;

            $listdb = new Listdb;
            $listdb->dbnotepk = $dbnotepk;
            $listdb->nomor = $nomor;
            $listdb->datecreate = now();
            $listdb->keterangan = "commercial invoice shipment " . $dbnote->dbno;
            $listdb->user = $funm;
            $listdb->userpk = $request->userpk;
            $listdb->save();
        } else {
            $listdb = Listdb::find($listdb->listdb);
            $listdb->userpk = $request->userpk;
            $listdb->update();
        }

        $dbnote->update();

        $byr = !empty($byrpk) ? Byr::find($byrpk) : null;
        if (!empty($byr) && (empty($byr->alamat) || (!empty($byr->alamat) && $byr->alamat == ''))) {
            $byr->alamat = $request->alamatbyr;
            $byr->alamattxt = $request->alamatbyr;
            $byr->update();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data updated successfully',
            'data' => $dbnote,
            'r_dtinvs' => $r_dtinvs,
            'byr' => $byr,
        ]);
    }

    // public function removePerbaikan($dbnotepk, Request $request) {
    //     $r_dtinvs = $request->has('dtinvs') ? $request->dtinvs : array();

    //     $invspkarr = collect($r_dtinvs)->pluck('invspk')->toArray();
    //     Invs::whereIn('invspk', $invspkarr)->update(['dbnotepk' => null]);
    //     Dbnotedt::whereIn('invspk', $invspkarr)->delete();

    //     $dbnotedtcount = Dbnotedt::where('dbnotepk', $dbnotepk)->count();

    //     if ($dbnotedtcount < 1) {
    //         $dbnote = Dbnote::find($dbnotepk);
    //         $dbnote->byrpk = null;
    //         $dbnote->alamatbyr = null;
    //         $dbnote->kontakbyr = null;
    //         $dbnote->season = null;
    //         $dbnote->brand = null;
    //         $dbnote->dbnm = null;
    //         $dbnote->dbnm = null;
    //         $dbnote->update();
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Data removed successfully',
    //         'r_dtinvs' => $r_dtinvs,
    //     ]);
    // }

    public function removePerbaikan(Request $request) {
        $dbnotedtpkArr = $request->dbnotedtpk ?? [];

        if (empty($dbnotedtpkArr)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data dipilih'
            ]);
        }

        // ambil invspk dari dbnotedt yang dipilih
        $dbnotedts = Dbnotedt::whereIn('dbnotedtpk', $dbnotedtpkArr)->get();

        $invspkarr = $dbnotedts->pluck('invspk')->toArray();
        $dbnotepk = optional($dbnotedts->first())->dbnotepk;

        // update invs
        Invs::whereIn('invspk', $invspkarr)->update(['dbnotepk' => null]);

        // delete dbnotedt berdasarkan PK
        Dbnotedt::whereIn('dbnotedtpk', $dbnotedtpkArr)->delete();

        // cek sisa data
        $dbnotedtcount = Dbnotedt::where('dbnotepk', $dbnotepk)->count();

        if ($dbnotedtcount < 1 && $dbnotepk) {
            $dbnote = Dbnote::find($dbnotepk);
            if ($dbnote) {
                $dbnote->update([
                    'byrpk' => null,
                    'alamatbyr' => null,
                    'kontakbyr' => null,
                    'season' => null,
                    'brand' => null,
                    'dbnm' => null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data removed successfully',
            'deleted_ids' => $dbnotedtpkArr,
        ]);
    }

    public function remove($dbnotepk, Request $request) {
        $r_dtinvs = $request->has('dtinvs') ? $request->dtinvs : array();

        $invspkarr = collect($r_dtinvs)->pluck('invspk')->toArray();
        Invs::whereIn('invspk', $invspkarr)->update(['dbnotepk' => null]);
        Dbnotedt::whereIn('invspk', $invspkarr)->delete();

        $dbnotedtcount = Dbnotedt::where('dbnotepk', $dbnotepk)->count();

        if ($dbnotedtcount < 1) {
            $dbnote = Dbnote::find($dbnotepk);
            $dbnote->byrpk = null;
            $dbnote->alamatbyr = null;
            $dbnote->kontakbyr = null;
            $dbnote->season = null;
            $dbnote->brand = null;
            $dbnote->dbnm = null;
            $dbnote->dbnm = null;
            $dbnote->update();
        }

        return response()->json([
            'success' => true,
            'message' => 'Data removed successfully',
            'r_dtinvs' => $r_dtinvs,
        ]);
    }

    public function destroy($dbnotepk) {
        $invspkarr = Dbnotedt::where('dbnotepk', $dbnotepk)->pluck('invspk')->toArray();

        Invs::whereIn('invspk', $invspkarr)->update(['dbnotepk' => null]);
        Dbnotedt::where('dbnotepk', $dbnotepk)->delete();
        Listdb::where('dbnotepk', $dbnotepk)->delete();
        Dbnote::where('dbnotepk', $dbnotepk)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data deleted successfully',
            'dbnotepk' => $dbnotepk,
            'invspkarr' => $invspkarr,
        ]);
    }
}
