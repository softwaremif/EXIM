<?php

namespace App\Http\Controllers\Shipplan;

use App\Exports\ShipmentPackingListExport;
use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Export;
use App\Models\Exportdt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ShipplanController extends Controller
{
    public function index()
    {
        return view(
            'menu.exim-export.shipplan.index'
        );
    }

    // public function getListExport(Request $request)
    // {
    //     $page = (int) $request->input('page', 1);
    //     $rows = (int) $request->input('rows', 10);
    //     $search = $request->input('q');
    //     $buyer = $request->input('buyer');
    //     $vassal = $request->input('vassal');
    //     $status = $request->input('status');
    //     $searchByRangeDay = $request->input('searchByRangeDay');
    //     $sortlistByDate = $request->input('sortlistByDate');

    //     $query = DB::table('export')
    //         ->leftJoin('exportdt', 'export.exportpk', '=', 'exportdt.exportpk')
    //         ->leftJoin('vassal', 'vassal.vassalpk', '=', 'export.vassalpk')
    //         ->leftJoin('cont', 'cont.contpk', '=', 'exportdt.contpk')
    //         ->select(
    //             'export.exportpk',
    //             'export.datecreate',
    //             'export.ordpk',
    //             'export.POno',
    //             'export.OP',
    //             'export.buyer',
    //             'export.pebno',
    //             'export.stsexp',
    //             'export.lockship',
    //             'export.exdate as extdate',
    //             'export.GAC',
    //             'export.startship',
    //             'export.endship',
    //             DB::raw('SUM(exportdt.qty) AS totqty'),
    //             'export.totctn',
    //             'vassal.typenm as vassal',
    //             'cont.vessname'
    //         )
    //         ->groupBy(
    //             'export.exportpk'
    //         );

    //     if ($search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('export.ordpk', 'like', "%{$search}%")
    //                 ->orWhere('export.POno', 'like', "%{$search}%")
    //                 ->orWhere('export.OP', 'like', "%{$search}%")
    //                 ->orWhere('export.buyer', 'like', "%{$search}%")
    //                 ->orWhere('export.pebno', 'like', "%{$search}%")
    //                 ->orWhere('cont.vessname', 'like', "%{$search}%");
    //         });
    //     }

    //     if ($buyer) {
    //         $query->where('export.buyer', $buyer);
    //     }

    //     if ($vassal) {
    //         $query->where('export.vassalpk', $vassal);
    //     }

    //     if ($status) {
    //         if ($status == 1) {
    //             $query->where(function ($q) {
    //                 $q->whereNull('export.stsexp')
    //                     ->orWhere('export.stsexp', 0);
    //             });
    //         } else if ($status == 2) {
    //             $query->where('stsexp', 1)->whereNull('lockship');
    //         } else if ($status == 3) {
    //             $query->where('stsexp', 1)->where('lockship', 1);
    //         } else {
    //             $query;
    //         }
    //     }

    //     if (!empty($searchByRangeDay)) {

    //         switch ($searchByRangeDay) {

    //             case 1:
    //                 $query->whereDate('export.exdate', Carbon::today());
    //                 break;

    //             case 2:
    //                 $query->whereBetween('export.exdate', [
    //                     Carbon::now()->startOfWeek(),
    //                     Carbon::now()->endOfWeek()
    //                 ]);
    //                 break;

    //             case 3:
    //                 $query->whereMonth('export.exdate', Carbon::now()->month)
    //                     ->whereYear('export.exdate', Carbon::now()->year);
    //                 break;

    //             case 4:
    //                 $query->whereBetween('export.exdate', [
    //                     Carbon::today(),
    //                     Carbon::today()->addMonths(3)
    //                 ]);
    //                 break;

    //             case 5:
    //                 $query->whereYear('export.exdate', Carbon::now()->year);
    //                 break;
    //         }
    //     }

    //     if (!empty($sortlistByDate)) {
    //         if ($sortlistByDate == 11) {
    //             $query->orderBy('export.datecreate', 'desc');
    //         } else if ($sortlistByDate == 12) {
    //             $query->orderBy('export.datecreate', 'asc');
    //         } else {
    //             $query->orderBy('export.datecreate', 'desc');
    //         }
    //     } else {
    //         $query->orderBy('export.datecreate', 'desc');
    //     }

    //     $total = DB::query()->fromSub($query, 'sub')->count();

    //     $result = $query
    //         ->limit($rows)
    //         ->offset(($page - 1) * $rows)
    //         ->get();

    //     $this->addOrderImageToRows($result);

    //     // ambil semua ordno sekaligus dalam 1 query (bukan query per baris di dalam loop)
    //     $ordpkList = $result->pluck('ordpk')->filter()->unique()->values();

    //     //ORD
    //     $ordRows = DB::connection('mysql_sig')
    //         ->table('ord')
    //         ->whereIn('ordpk', $ordpkList)
    //         ->get([
    //             'ordpk',
    //             'ordno',
    //             'season',
    //             'artno',
    //         ]);

    //     $ordMap = $ordRows->keyBy('ordpk'); // hasil: [ordpk => ordno, ...]


    //     // map hasil query utama, tempelkan ordno dari $ordnoMap
    //     $result = $result->values()->map(function ($row, $index) use ($ordMap, $page, $rows) {
    //         $ord = $ordMap->get($row->ordpk);
    //         $row->no = (($page - 1) * $rows) + $index + 1;
    //         $row->ordno = $row->OP ? ($row->OP ?? '-') : ($ord->ordno ?? '-');
    //         $row->style = $ord->artno ?? '-';
    //         $row->season = $ord->season ?? '-';
    //         $row->idship = 'SP' . str_pad($row->exportpk, 4, '0', STR_PAD_LEFT);

    //         $pack = DB::connection('mysql_andon')
    //             ->table('pack')
    //             ->where('exportpk', $row->exportpk)
    //             ->first();

    //         $row->part = $pack->part ?? '-';

    //         return $row;
    //     });

    //     return response()->json([
    //         'total' => $total,
    //         'rows'  => $result,
    //     ]);
    // }

    public function getListExport(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $rows = max(1, (int) $request->input('rows', 10));
        $offset = ($page - 1) * $rows;

        $search = $request->input('q');
        $buyer = $request->input('buyer');
        $vassal = $request->input('vassal');
        $status = $request->input('status');
        $searchByRangeDay = $request->input('searchByRangeDay');
        $sortlistByDate = $request->input('sortlistByDate');

        /*
    |--------------------------------------------------------------------------
    | MAIN QUERY
    |--------------------------------------------------------------------------
    */
        $query = DB::table('poexport')
            ->leftJoin('export', 'export.exportpk', '=', 'poexport.exportpk')
            ->leftJoin('vassal', 'vassal.vassalpk', '=', 'export.vassalpk')
            // ->leftJoin('cont', 'cont.contpk', '=', 'exportdt.contpk')
            ->select(
                'export.exportpk',
                'export.datecreate',
                'export.ordpk',
                'poexport.POno',
                'poexport.OP',
                'poexport.poexportpk',
                'poexport.invs',
                'export.buyer',
                'export.pebno',
                'export.stsexp',
                'export.lockship',
                'export.exdate as extdate',
                'export.GAC',
                'export.startship',
                'export.endship',
                'export.contdate',
                'export.totctn',
                'export.qty_list',
                'export.vassalpk',
                'export.etdvess',
                'export.exdate',
                'export.actexdate',
                'poexport.qty',
                'poexport.mif',
                'vassal.typenm as vassal',
                // 'cont.vessname'
                // DB::raw('SUM(exportdt.qty) AS totqty')
            );
        // ->groupBy('export.exportpk');

        /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('export.ordpk', 'like', "%{$search}%")
                    ->orWhere('poexport.POno', 'like', "%{$search}%")
                    ->orWhere('poexport.OP', 'like', "%{$search}%")
                    ->orWhere('export.buyer', 'like', "%{$search}%")
                    ->orWhere('export.pebno', 'like', "%{$search}%")
                    ->orWhere('poexport.invs', 'like', "%{$search}%");
            });
        }

        if ($buyer) {
            $query->where('export.buyer', $buyer);
        }

        if ($vassal) {
            $query->where('export.vassalpk', $vassal);
        }

        /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */
        if ($status == 1) {
            // PLAN
            // $query->where(function ($q) {
            //     $q->whereNotNull('export.condate')
            //         ->orWhereNotNull('export.etdvess')
            //         ->orWhereNotNull('export.vassalpk');
            // })
            $query->WhereNotNull('export.vassalpk')
                ->whereNull('export.exdate')
                ->whereNull('export.actexdate');
        } elseif ($status == 2) {
            // CONFIRM
            $query->whereNotNull('export.exdate')
                ->whereNull('export.actexdate');
        } elseif ($status == 3) {
            // COMPLETE
            $query->whereNotNull('export.actexdate');
        }

        /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */
        if (!empty($searchByRangeDay)) {

            switch ((int) $searchByRangeDay) {

                case 1:
                    $query->whereDate('export.exdate', Carbon::today());
                    break;

                case 2:
                    $query->whereBetween('export.exdate', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                    break;

                case 3:
                    $query->whereMonth('export.exdate', Carbon::now()->month)
                        ->whereYear('export.exdate', Carbon::now()->year);
                    break;

                case 4:
                    $query->whereBetween('export.exdate', [
                        Carbon::today(),
                        Carbon::today()->addMonths(3)
                    ]);
                    break;

                case 5:
                    $query->whereYear('export.exdate', Carbon::now()->year);
                    break;
            }
        }

        /*
    |--------------------------------------------------------------------------
    | SORT
    |--------------------------------------------------------------------------
    */
        $query->orderBy(
            'export.datecreate',
            $sortlistByDate == 12 ? 'asc' : 'desc'
        );

        /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */
        $total = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
        $result = $query
            ->limit($rows)
            ->offset($offset)
            ->get();

        /*
    |--------------------------------------------------------------------------
    | Jika tidak ada data
    |--------------------------------------------------------------------------
    */
        if ($result->isEmpty()) {
            return response()->json([
                'total' => $total,
                'rows' => [],
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | ORD - SATU QUERY
    |--------------------------------------------------------------------------
    */
        $ordpkList = $result
            ->pluck('ordpk')
            ->filter()
            ->unique()
            ->values();

        $ordMap = collect();

        if ($ordpkList->isNotEmpty()) {

            $ordRows = DB::connection('mysql_sig')
                ->table('ord')
                ->whereIn('ordpk', $ordpkList)
                ->get([
                    'ordpk',
                    'ordno',
                    'season',
                    'artno',
                    'srno',
                    'foto',
                    'foto2',
                    'stsfoto',
                ]);

            $ordMap = $ordRows->keyBy('ordpk');
        }

        /*
    |--------------------------------------------------------------------------
    | PACK - SATU QUERY
    |--------------------------------------------------------------------------
    */
        $exportpkList = $result
            ->pluck('exportpk')
            ->filter()
            ->unique()
            ->values();

        $packMap = collect();

        if ($exportpkList->isNotEmpty()) {

            $packRows = DB::connection('mysql_andon')
                ->table('pack')
                ->whereIn('exportpk', $exportpkList)
                ->select(
                    'exportpk',
                    'part'
                )
                ->orderBy('packpk')
                ->get();

            // Ambil part pertama untuk setiap exportpk
            $packMap = $packRows
                ->groupBy('exportpk')
                ->map(function ($items) {
                    return $items->first();
                });
        }

        /*
    |--------------------------------------------------------------------------
    | MAPPING
    |--------------------------------------------------------------------------
    */
        $result = $result->values()->map(function ($row, $index) use (
            $ordMap,
            $packMap,
            $offset
        ) {


            $ord = $ordMap->get($row->ordpk);
            $pack = $packMap->get($row->exportpk);

            $row->no = $offset + $index + 1;

            $row->ordno = !empty($row->OP)
                ? $row->OP
                : ($ord->ordno ?? '-');

            $row->style = $ord->artno ?? '-';
            $row->season = $ord->season ?? '-';

            $row->idship = 'SP' . str_pad(
                $row->exportpk,
                4,
                '0',
                STR_PAD_LEFT
            );

            $row->part = $pack->part ?? '-';

            return $row;
        });

        /*
    |--------------------------------------------------------------------------
    | IMAGE
    |--------------------------------------------------------------------------
    */
        $this->addOrderImageToRows($result);

        return response()->json([
            'total' => $total,
            'rows' => $result,
        ]);
    }

    private function addOrderImageToRows($rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $gisFotoBase        = rtrim(config('services.foto.gis_base'), '/');
        $productionFotoBase = rtrim(config('services.foto.production_base'), '/');
        $sampleFotoBase     = rtrim(config('services.foto.sample_base'), '/');

        $noImageUrl = asset('public/css/images/no-img.png');

        foreach ($rows as $r) {
            $r->order_image = $noImageUrl;
        }

        $ordpks = $rows->pluck('ordpk')->filter()->unique()->values()->all();
        if (empty($ordpks)) {
            return;
        }

        $ordRows = DB::connection('mysql_sig')->table('ord')
            ->whereIn('ordpk', $ordpks)
            ->get(['ordpk', 'srno', 'foto', 'foto2', 'stsfoto']);

        $ordByOrdpk = $ordRows->keyBy('ordpk');

        $srnos = $ordRows->pluck('srno')->filter()->unique()->values()->all();

        $srpkBySrno = [];
        $fotoBySrpk = [];

        if (!empty($srnos)) {
            $reqRows = DB::connection('mysql_sample')->table('request')
                ->whereIn('srno', $srnos)
                ->get(['srno', 'srpk']);

            foreach ($reqRows as $r) {
                $srpkBySrno[$r->srno] = $r->srpk;
            }

            $srpks = array_values(array_unique(array_values($srpkBySrno)));

            if (!empty($srpks)) {
                $statusRows = DB::connection('mysql_sample')->table('status')
                    ->whereIn('srpk', $srpks)
                    ->orderByDesc('statuspk')
                    ->get(['srpk', 'foto', 'statuspk']);

                foreach ($statusRows as $r) {
                    if (!isset($fotoBySrpk[$r->srpk])) {
                        $fotoBySrpk[$r->srpk] = $r->foto;
                    }
                }
            }
        }

        foreach ($rows as $r) {
            $ord = $ordByOrdpk->get($r->ordpk);
            if (!$ord) {
                continue;
            }

            $stsfoto = $ord->stsfoto ?? null;
            $foto1   = $ord->foto ?? null;
            $srno    = $ord->srno ?? null;

            $srpk  = $srno ? ($srpkBySrno[$srno] ?? null) : null;
            $foto2 = $srpk ? ($fotoBySrpk[$srpk] ?? null) : ($ord->foto2 ?? null);

            if ($stsfoto == 1) {
                $r->order_image = !empty($foto1) ? "{$gisFotoBase}/{$foto1}" : $noImageUrl;
            } elseif ($stsfoto == 2) {
                $r->order_image = !empty($foto1) ? "{$productionFotoBase}/{$foto1}" : $noImageUrl;
            } else {
                $r->order_image = !empty($foto2) ? "{$sampleFotoBase}/{$foto2}" : $noImageUrl;
            }
        }
    }

    public function removeList(Request $request)
    {
        $validated = $request->validate([
            'exportpk' => 'required|integer|exists:export,exportpk',
        ]);

        // dd($validated['exportpk']);

        DB::beginTransaction();
        try {
            $export = DB::table('export')->where('exportpk', $validated['exportpk'])->first();

            if (empty($export)) {
                throw new \Exception('Data carton tidak ditemukan.');
            }

            DB::connection('mysql_andon')->table('pack')->where('exportpk', $export->exportpk)->update([
                'exportpk'      => null,
                'exportdtpk'    => null,
                'contpk'        => null,
                'part'          => null,
            ]);

            // Ambil semua packpk yang terkait dengan export
            $packpkList = DB::table('exportdt')
                ->where('exportpk', $export->exportpk)
                ->whereNotNull('packpk')
                ->pluck('packpk')
                ->unique()
                ->values();

            // Hapus ship berdasarkan packpk
            if ($packpkList->isNotEmpty()) {
                DB::connection('mysql_andon')
                    ->table('ship')
                    ->whereIn('packpk', $packpkList)
                    ->delete();
            }

            Exportdt::where('exportpk', $validated['exportpk'])->delete();

            DB::table('poexport')->where('exportpk', $validated['exportpk'])->delete();

            Export::where('exportpk', $validated['exportpk'])->update([
                'stscancel'      => 1,
            ]);

            DB::commit();

            return response()->json(['message' => 'Data berhasil dihapus dari list.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            // report($e);
            // return response()->json(['message' => 'Gagal menghapus data.'], 500);
        }
    }

    public function detail_ship($exportpk)
    {
        $header = DB::table('export')
            ->leftJoin('vassal', 'vassal.vassalpk', 'export.vassalpk')
            ->leftJoin('plb', 'plb.plbpk', 'export.plbpk')
            ->where('exportpk', $exportpk)
            ->first();

        if (!$header) {
            abort(404, 'Data export tidak ditemukan.');
        }

        $header->ordno = $header->OP ?? '-';

        // if (!empty($header->ordpk)) {
        //     $header->ordno = DB::connection('mysql_sig')
        //         ->table('ord')
        //         ->where('ordpk', $header->ordpk)
        //         ->value('ordno');
        // }

        $header->exdate = !empty($header->exdate)
            ? \Carbon\Carbon::parse($header->exdate)->format('d/m/Y')
            : '';
        $header->GAC = !empty($header->GAC)
            ? \Carbon\Carbon::parse($header->GAC)->format('d/m/Y')
            : '';
        $header->etdvess = !empty($header->etdvess)
            ? \Carbon\Carbon::parse($header->etdvess)->format('d/m/Y')
            : '';
        $header->etavess = !empty($header->etavess)
            ? \Carbon\Carbon::parse($header->etavess)->format('d/m/Y')
            : '';

        $header->contdate = !empty($header->contdate)
            ? \Carbon\Carbon::parse($header->contdate)->format('d/m/Y')
            : '';

        $header->actcontdate = !empty($header->actcontdate)
            ? \Carbon\Carbon::parse($header->actcontdate)->format('d/m/Y')
            : '';

        $header->closetime = !empty($header->closetime)
            ? \Carbon\Carbon::parse($header->closetime)->format('d/m/Y H:i:s')
            : '';

        //Untuk container
        $container = DB::table('cont')->where('exportpk', $exportpk)->get();

        // ambil semua carton yang sudah dimuat, dikelompokkan per contpk
        $loadedCartons = DB::connection('mysql_andon')
            ->table('pack')
            ->where('exportpk', $exportpk)
            ->select(
                'contpk',
                'exportdtpk',
                'carton',
                'material',
                'pcsp',
                DB::raw('(panjang * lebar * tinggi) / 1000000 AS t_cbm')
            )
            ->orderBy('carton')
            ->get()
            ->groupBy('contpk');

        return view(
            'menu.exim-export.shipplan.detail_ship',
            compact('header', 'container', 'exportpk', 'loadedCartons')
        );
    }

    // public function set_shipplan(Request $request)
    // {
    //     $ordpk = $request->input('ordpk');
    //     $buyer = $request->input('buyer');
    //     $POno = $request->input('POno');
    //     $OP = $request->input('OP') ?? null;
    //     $popks = $request->input('popks') ?? null;
    //     $totqty = $request->input('totqty');
    //     $exdate = $request->input('exdate');
    //     $totctn = $request->input('totctn');

    //     try {
    //         $exportpk = DB::table('export')->insertGetId([
    //             'datecreate' => now(),
    //             'ordpk' => $ordpk,
    //             'buyer' => $buyer,
    //             'POno' => $POno,
    //             'OP' => $OP,
    //             // 'totqty' => $totqty,
    //             'GAC' => $exdate,
    //             // 'totctn' => $totctn,
    //         ]);

    //         return response()->json([
    //             'success'  => true,
    //             'exportpk' => $exportpk,
    //         ]);
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function set_shipplan(Request $request)
    {
        $validated = $request->validate([
            'ordpk'   => 'required|string',
            'buyer'   => 'nullable|string',
            'POno'    => 'nullable|string',
            'OP'      => 'nullable|string',
            'factory' => 'nullable|string',
            'popks'   => 'nullable|string',
            'GAC'     => 'required|date',
            'contdate'  => 'required|date',   // Ex Factory Date
            'etd'     => 'required|date|after_or_equal:exdate',
            'vassal'  => 'required|string',
            'totqty'  => 'required|numeric',
            'qty_list' => 'nullable|string',
            // 'totctn'  => 'required|numeric',
        ]);

        $ordpks = collect(explode(',', $validated['ordpk']))
            ->map(fn($v) => trim($v))->filter()->unique()->values();

        $popks = collect(explode(',', $validated['popks2'] ?? ''))
            ->map(fn($v) => trim($v))->filter()->unique()->values();

        if ($ordpks->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada order yang dipilih.']);
        }

        DB::beginTransaction();
        try {
            $exportpk = DB::table('export')->insertGetId([
                'buyer'      => $validated['buyer'],
                'pono'       => $validated['POno'],
                'op'         => $validated['OP'],
                'factory'    => $validated['factory'],
                'totqty'     => $validated['totqty'],
                'qty_list'   => $validated['qty_list'],
                'ordpk'      => $ordpks->implode(','),
                'GAC'        => $validated['GAC'],
                'contdate'   => $validated['contdate'],
                'etdvess'    => $validated['etd'],
                'vassalpk'   => $validated['vassal'],
                'popks'      => $validated['popks'],
                'datecreate' => now()
            ]);

            $selectedRows = json_decode($request->selectedRows, true);
            $poexports = [];

            foreach ($selectedRows as $row) {
                $poexports[] = [
                    'exportpk' => $exportpk,
                    'POno'     => $row['POno'],
                    'OP'       => $row['OP'],
                    'qty'      => $row['qty'],
                    'mif'      => $row['mif'],
                    'invs'     => null,
                    'vassalpk' => $validated['vassal'],
                ];
            }

            DB::table('poexport')->insert($poexports);

            DB::commit();

            return response()->json(['success' => true, 'exportpk' => $exportpk]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            // report($e);
            // return response()->json(['success' => false, 'message' => 'Gagal membuat export.']);
        }
    }

    public function set_invs(Request $request)
    {
        $validated = $request->validate([
            'poexportpk' => 'required|string',
            'invs'       => 'nullable|string',
        ]);

        $poexportpk = collect(explode(',', $validated['poexportpk']))
            ->map(fn($v) => trim($v))
            ->filter(fn($v) => $v !== '')
            ->unique()
            ->values();

        if ($poexportpk->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada list yang dipilih.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            $updated = DB::table('poexport')
                ->whereIn('poexportpk', $poexportpk->all())
                ->update([
                    'invs' => $validated['invs'] ?: null,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil disimpan.',
                'updated' => $updated,
                'poexportpk' => $poexportpk->values()->all(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            dd($e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan invoice.',
            ], 500);
        }
    }

    public function getFwd()
    {
        $data = DB::table('fwd')
            ->select('fwdpk', 'fwdnm')
            ->orderBy('fwdnm', 'ASC')
            ->get();

        return response()->json($data);
    }

    public function getVassal()
    {
        $data = DB::table('vassal')
            ->select('vassalpk', 'typenm')
            ->orderBy('vassalpk', 'ASC')
            ->get();

        return response()->json($data);
    }

    public function getPlb()
    {
        $data = DB::table('plb')
            ->select('plbpk', 'plbnm')
            ->orderBy('plbpk', 'ASC')
            ->get();

        return response()->json($data);
    }

    public function getByr()
    {
        $data = DB::connection('mysql_sig')
            ->table('byr')
            ->select('byrnm', 'byrnm')
            ->orderBy('byrnm', 'ASC')
            ->get();

        return response()->json($data);
    }

    function filterday()
    {
        $data = [
            ['value' => '1', 'text' => 'Hari ini'],
            ['value' => '2', 'text' => 'Minggu ini'],
            ['value' => '3', 'text' => 'Bulan ini'],
            ['value' => '4', 'text' => '3 Bulan depan'],
            ['value' => '5', 'text' => 'Tahun ini'],
        ];
        return response()->json($data);
    }

    /**
     * Endpoint utama yang dipanggil AJAX dari modal.
     * Alurnya: validasi -> update tabel export -> insert tabel container -> commit.
     */
    public function store_containers(Request $request)
    {
        // $validated = $request->validate([
        //     'exportpk'                       => 'required|integer|exists:export,exportpk',
        //     'etdvess'                        => 'nullable|date',
        //     'etavess'                        => 'nullable|date|after_or_equal:etd',
        //     'notes'                          => 'nullable|string',
        //     'totqty'                         => 'nullable|number',
        //     'totctn'                         => 'nullable|number',
        //     'totnw'                         => 'nullable|number',

        // 'containers.*.contpk'        => 'nullable|integer|exists:cont,contpk',
        //     'containers'                     => 'required|array|min:1',
        //     'containers.*.container_no'      => 'required|string|max:20',
        //     'containers.*.type'              => 'required|in:20ft,40ft,40hc',
        //     'containers.*.vessel_name'       => 'nullable|string|max:150',
        // ]);

        $data = $request->all();
        try {
            DB::beginTransaction();

            // 1. update data header (tabel export) sesuai exportpk & inputan form
            $this->updateExport($data);

            // 2. simpan container baru sesuai jumlah array yang dikirim
            $totalContainer = $this->storeContainers($data);

            DB::commit();

            return response()->json([
                'message'         => 'Data export berhasil diperbarui dan ' . $totalContainer . ' container berhasil disimpan.',
                'total_container' => $totalContainer,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e; // otomatis balas 422 dengan format errors, ditangkap JS di frontend

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data.',
                'debug'   => [
                    'error' => $e->getMessage(),
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                ],
            ], 500);
        }
    }

    /**
     * Update tabel export berdasarkan exportpk, hanya kolom header
     * (bukan detail per container).
     */
    private function updateExport(array $data): void
    {
        $exdate = $data['exdate']
            ? Carbon::createFromFormat('d/m/Y H:i:s', $data['exdate'])->format('Y-m-d H:i:s')
            : null;

        $closetime = $data['closetime']
            ? Carbon::createFromFormat('d/m/Y H:i:s', $data['closetime'])->format('Y-m-d H:i:s')
            : null;

        $etdvess = $data['etdvess']
            ? Carbon::createFromFormat('d/m/Y', $data['etdvess'])->format('Y-m-d')
            : null;

        $etavess = $data['etavess']
            ? Carbon::createFromFormat('d/m/Y', $data['etavess'])->format('Y-m-d')
            : null;

        $contdate = $data['contdate']
            ? Carbon::createFromFormat('d/m/Y', $data['contdate'])->format('Y-m-d')
            : null;

        $actcontdate = $data['actcontdate']
            ? Carbon::createFromFormat('d/m/Y', $data['actcontdate'])->format('Y-m-d')
            : null;

        Export::where('exportpk', $data['exportpk'])->update([
            'etdvess'       => $etdvess ?? null,
            'etavess'       => $etavess ?? null,
            'exdate'        => $exdate ?? null,
            'dateupdt'      => now(),
            'pebno'         => $data['pebno'] ?? null,
            // 'totqty'        => $data['totqty'] ?? null,
            // 'totctn'        => $data['totctn'] ?? null,
            'ukctn'         => $data['ukctn'] ?? null,
            'totvol'        => $data['totvol'] ?? null,
            'totnw'         => $data['totnw'] ?? null,
            'totgw'         => $data['totvol'] ?? null,
            'vassalpk'      => $data['vassal'] ?? null,
            'plbpk'         => $data['plb'] ?? null,
            'contdate'      => $contdate  ?? null,
            'actcontdate'   => $actcontdate   ?? null,
            'closetime'     => $closetime ?? null,
            'vessname'      => $data['containers'][0]['vessel_name'] ?? null,
            'remark'        => $data['remark'] ?? null,
            'inv'           => $data['invno'] ?? null,
        ]);
    }

    /**
     * Ambil total array containers[] dari request, lalu insert
     * sebanyak itu ke tabel container, terhubung ke exportpk.
     */
    // private function storeContainers(array $data): int
    // {
    //     $containers    = $data['containers'];
    //     $totalContainer = count($containers);

    //     $rows = [];
    //     foreach ($containers as $c) {
    //         $rows[] = [
    //             'exportpk'      => $data['exportpk'],
    //             'contno'        => $c['contno'],
    //             'type'          => $c['type'],
    //             'fwdpk'         => $data['fwd'] ?? null,
    //             'vessname'      => $c['vessname'] ?? null,
    //             'remark'        => null,
    //         ];
    //     }

    //     // insert semua sekaligus (lebih efisien daripada looping ->create())
    //     Container::insert($rows);

    //     // opsional: sinkronkan jumlah total container ke tabel export
    //     // Export::where('pk', $data['exportpk'])->update([
    //     //     'total_container' => $totalContainer,
    //     // ]);

    //     return $totalContainer;
    // }

    private function storeContainers(array $data): int
    {
        $containers = $data['containers'];
        $totalContainer = count($containers);

        $insertRows = [];

        foreach ($containers as $c) {
            if($c['type'] == '20ft'){
                $cbm = 33;
            }else if($c['type'] == '40ft'){
                $cbm = 67;
            }else if($c['type'] == '40hc'){
                $cbm = 76;
            }else{
                $cbm = $c['volume'];
            }
            $payload = [
                'exportpk' => $data['exportpk'],
                'contno'   => $c['contno'],
                'type'     => $c['type'],
                'volume'   => $cbm ?? null,
                'fwdpk'    => $c['fwd'] ?? $data['fwd'] ?? null,
                'vessname' => $c['vessname'] ?? null,
                'remark'   => null,
            ];

            $contpk = $c['contpk'] ?? null;

            if (!empty($contpk)) {
                // sudah ada contpk -> UPDATE row yang sudah ada
                Container::where('contpk', $contpk)->update($payload);
            } else {
                // belum ada contpk -> kumpulkan dulu, insert sekaligus di akhir (lebih efisien)
                $insertRows[] = $payload;
            }
        }

        if (!empty($insertRows)) {
            Container::insert($insertRows);
        }

        return $totalContainer;
    }

    // public function available_cartons($exportpk, $contpk)
    // {
    //     $export = DB::table('export')
    //         ->where('exportpk', $exportpk)
    //         ->first();

    //     if (!$export) {
    //         return response()->json([
    //             'total' => 0,
    //             'rows' => [],
    //         ]);
    //     }

    //     $export->ordno = null;

    //     if (!empty($export->ordpk)) {
    //         $export->ordno2 = DB::connection('mysql_sig')
    //             ->table('ord')
    //             ->where('ordpk', $export->ordpk)
    //             ->value('ordno');
    //     }

    //     $export->ordno = $export->OP ? ($export->OP ?? null) : ($export->ordno2 ?? null);

    //     $select = [
    //         'pack.packpk',
    //         'pack.popk',
    //         'pack.carton',
    //         'pack.POno',
    //         'pack.OP',
    //         'pack.status',
    //         'pack.exportdtpk',
    //         'pack.material', // sesuaikan nama field color
    //     ];

    //     for ($i = 1; $i <= 40; $i++) {
    //         $select[] = "po.size{$i}";
    //         $select[] = "pack.qtyp{$i}";
    //     }

    //     $query = DB::connection('mysql_andon')
    //         ->table('pack')
    //         ->leftJoin('po', 'po.popk', '=', 'pack.popk')
    //         ->where('pack.POno', $export->POno)
    //         ->where('pack.OP', $export->ordno);

    //     if ($search = request('q')) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('pack.carton', 'like', "%{$search}%");
    //         });
    //     }

    //     $packs = $query
    //         ->select($select)
    //         ->get();

    //     /*
    // |--------------------------------------------------------------------------
    // | GROUP BY CARTON
    // |--------------------------------------------------------------------------
    // */
    //     $grouped = $packs->groupBy('carton');

    //     $rows = [];

    //     foreach ($grouped as $carton => $cartonPacks) {

    //         $sizes = [];
    //         $material = [];
    //         $pcsp = 0;

    //         foreach ($cartonPacks as $pack) {

    //             $packpks = $cartonPacks
    //                 ->pluck('packpk')
    //                 ->filter()
    //                 ->unique()
    //                 ->values()
    //                 ->toArray();

    //             // Simpan color yang berbeda
    //             if (
    //                 isset($pack->material) &&
    //                 $pack->material !== null &&
    //                 $pack->material !== ''
    //             ) {
    //                 $materials[] = $pack->material;
    //             }

    //             for ($i = 1; $i <= 40; $i++) {

    //                 $size = $pack->{'size' . $i};
    //                 $qtyp = $pack->{'qtyp' . $i};

    //                 if (
    //                     $size !== null &&
    //                     $size !== '' &&
    //                     $qtyp !== null &&
    //                     (float) $qtyp > 0
    //                 ) {
    //                     $qty = (float) $qtyp;

    //                     /*
    //                  * Kalau size yang sama muncul di beberapa
    //                  * record dalam carton yang sama, jumlahkan.
    //                  */
    //                     if (!isset($sizes[$size])) {
    //                         $sizes[$size] = 0;
    //                     }

    //                     $sizes[$size] += $qty;

    //                     $pcsp += $qty;
    //                 }
    //             }
    //         }

    //         // Carton tanpa qty tidak dimasukkan
    //         if ($pcsp <= 0) {
    //             continue;
    //         }

    //         /*
    //      * Ubah associative array menjadi format:
    //      *
    //      * [
    //      *     ['size' => 'S', 'qty' => 10],
    //      *     ['size' => 'M', 'qty' => 20],
    //      * ]
    //      */
    //         $sizeRows = [];

    //         foreach ($sizes as $size => $qty) {
    //             $sizeRows[] = [
    //                 'size' => $size,
    //                 'qty'  => $qty,
    //             ];
    //         }

    //         // Hilangkan color duplicate
    //         $materials = array_values(array_unique($materials));

    //         $firstPack = $cartonPacks->first();

    //         $rows[] = [
    //             'packpk' => $packpks,
    //             'exportpk' => $firstPack->exportpk = $exportpk,
    //             'contpk'   => $firstPack->contpk = $contpk,
    //             'popk'   => $firstPack->popk,
    //             'carton' => $carton,
    //             'status' => $firstPack->status,
    //             'exportdtpk' => $firstPack->exportdtpk,
    //             'pcsp'   => $pcsp,
    //             'materials' => $materials,
    //             'sizes'  => $sizeRows,
    //         ];
    //     }

    //     return response()->json([
    //         'total' => count($rows),
    //         'rows'  => $rows,
    //     ]);
    // }

    public function available_cartons($exportpk, $contpk)
    {
        $filterPo = request('filter_po');
        $filterOrdno = request('filter_ordno');
        // ============================================================
        // GET EXPORT
        // ============================================================
        $export = DB::table('export')
            ->where('exportpk', $exportpk)
            ->first();

        if (!$export) {
            return response()->json([
                'total' => 0,
                'rows'  => [],
            ]);
        }

        // ============================================================
        // GET ORDNO
        // ============================================================
        $ordno2 = null;

        if (!empty($export->ordpk)) {
            $ordno2 = DB::connection('mysql_sig')
                ->table('ord')
                ->where('ordpk', $export->ordpk)
                ->value('ordno');
        }

        $ordno = !empty($export->OP)
            ? $export->OP
            : $ordno2;


        // ============================================================
        // SELECT
        // ============================================================
        $select = [
            'pack.packpk',
            'pack.popk',
            'pack.carton',
            'pack.POno',
            'pack.OP',
            'pack.status',
            'pack.exportdtpk',
            'pack.material',
            'pack.part',
        ];

        for ($i = 1; $i <= 40; $i++) {
            $select[] = "po.size{$i}";
            $select[] = "pack.qtyp{$i}";
        }

        // ============================================================
        // QUERY
        // ============================================================
        $popks = $export->popks;
        if (!is_array($popks)) {
            $popks = explode(',', $popks);
        }

        $query = DB::connection('mysql_andon')
            ->table('pack')
            ->leftJoin('po', 'po.popk', '=', 'pack.popk')
            ->whereIn('pack.popk', $popks);

        // ============================================================
        // SEARCH CARTON
        // ============================================================
        if ($filterPo) {
            $query->where('po.POno', $filterPo);
        }

        if ($filterOrdno) {
            $query->where('po.OP', $filterOrdno); // sesuaikan kolom Order No aslinya
        }

        if ($search = request('q')) {
            $query->where(
                'pack.carton',
                'like',
                "%{$search}%"
            );
        }

        // ============================================================
        // GET PACK
        // ============================================================
        $packs = $query
            ->select($select)
            ->get();

        // ============================================================
        // GROUP BY CARTON
        // ============================================================
        $grouped = $packs->groupBy(function ($pack) {
            return $pack->carton . '|' . $pack->POno . '|' . $pack->OP;
        });

        $rows = [];

        // ============================================================
        // LOOP CARTON
        // ============================================================
        foreach ($grouped as $carton => $cartonPacks) {

            $details = [];
            $packpks = [];
            $pcsp = 0;

            // ========================================================
            // LOOP PACK DALAM CARTON
            // ========================================================
            foreach ($cartonPacks as $pack) {
                // Simpan packpk unik
                if (!empty($pack->packpk)) {
                    $packpks[] = $pack->packpk;
                }
                // ====================================================
                // LOOP SIZE 1 - 40
                // ====================================================
                for ($i = 1; $i <= 40; $i++) {

                    $size = $pack->{'size' . $i};
                    $qtyp = $pack->{'qtyp' . $i};

                    // Skip size kosong
                    if (
                        $size === null ||
                        $size === ''
                    ) {
                        continue;
                    }

                    // Skip qty kosong / 0
                    if ($qtyp === null || (float) $qtyp <= 0) {
                        continue;
                    }

                    $qty = (float) $qtyp;

                    $pcsp += $qty;

                    // =================================================
                    // SIMPAN DETAIL
                    // =================================================
                    $details[] = [
                        'material' => $pack->material,
                        'size'     => $size,
                        'qty'      => $qty,
                        'packpk'   => $pack->packpk,
                    ];
                }
            }

            // ========================================================
            // UNIQUE PACKPK
            // ========================================================
            $packpks = array_values(
                array_unique($packpks)
            );

            // ========================================================
            // CARTON TANPA QTY
            // ========================================================
            if ($pcsp <= 0) {
                continue;
            }

            // ========================================================
            // DATA CARTON
            // ========================================================
            $firstPack = $cartonPacks->first();
            $rows[] = [
                'packpk'     => $packpks,
                'exportpk'    => $exportpk,
                'contpk'      => $contpk,
                'popk'        => $firstPack->popk,
                'carton'      => $carton,
                'status'      => $firstPack->status,
                'exportdtpk'  => $firstPack->exportdtpk,
                'part'        => $firstPack->part,
                'POno'        => $firstPack->POno,
                'OP'        => $firstPack->OP,
                'pcsp'        => $pcsp,
                'details'     => $details,
            ];
        }

        // ============================================================
        // SORT CARTON
        // ============================================================
        usort(
            $rows,
            function ($a, $b) {
                return strnatcasecmp(
                    (string) $a['carton'],
                    (string) $b['carton']
                );
            }
        );

        // ============================================================
        // RESPONSE
        // ============================================================
        return response()->json([
            'total' => count($rows),
            'rows'  => $rows,
        ]);
    }

    public function load_container(Request $request)
    {
        $validated = $request->validate([
            'contpk'         => 'required|integer',
            'exportpk'       => 'required|integer',
            'ordpk'          => 'required|integer',
            'packpk'         => 'required|array|min:1',
            'packpk.*'       => 'integer|exists:mysql_andon.pack,packpk',
        ]);

        try {
            DB::beginTransaction();

            // 1. ambil data pack berdasarkan packpk yang dipilih
            $packs = DB::connection('mysql_andon')->table('pack')
                ->whereIn('packpk', $validated['packpk'])
                ->get();

            if ($packs->isEmpty()) {
                throw new \Exception('Data carton tidak ditemukan.');
            }

            //hitung cbm
            $packs->each(function ($pack) {
                if (
                    $pack->panjang !== null &&
                    $pack->lebar !== null &&
                    $pack->tinggi !== null
                ) {
                    $pack->t_cbm = (
                        $pack->panjang *
                        $pack->lebar *
                        $pack->tinggi
                    ) / 1000000;
                } else {
                    $pack->t_cbm = 0;
                }
            });

            // cache nomor part terakhir per poppk, supaya konsisten walau
            // ada beberapa pack dengan poppk sama dalam satu kali submit
            $lastPartCache = [];
            $loadedItems = [];

            /*
            |--------------------------------------------------------------------------
            | PRE-HITUNG INFO CARTON PER POPK (sebelum loop utama)
            |--------------------------------------------------------------------------
            | Untuk tiap popk yang terlibat di request ini, hitung:
            | - total  = total carton unik di PO tersebut
            | - loaded = carton yang SUDAH termuat sebelumnya (exportpk sudah terisi)
            | - now    = carton yang akan dimuat di request SEKARANG
            |
            | Kalau (loaded + now) >= total, berarti proses load kali ini akan
            | menghabiskan seluruh carton PO -> part final = 10.
            */
            $popks = $packs->pluck('popk')->unique();
            $popkCartonInfo = [];

            foreach ($popks as $popk) {
                $totalCarton = DB::connection('mysql_andon')->table('pack')
                    ->where('popk', $popk)
                    ->distinct()
                    ->count('carton');

                $loadedCarton = DB::connection('mysql_andon')->table('pack')
                    ->where('popk', $popk)
                    ->whereNotNull('exportpk')
                    ->distinct()
                    ->count('carton');

                $loadingNowCarton = $packs->where('popk', $popk)->pluck('carton')->unique()->count();

                $popkCartonInfo[$popk] = [
                    'total'  => $totalCarton,
                    'loaded' => $loadedCarton,
                    'now'    => $loadingNowCarton,
                ];
            }

            foreach ($packs as $pack) {
                $mif = DB::connection('mysql_andon')
                    ->table('po')
                    ->where('popk', $pack->popk)
                    ->value('mif');

                // 2. insert ke exportdt
                $exportdtpk = DB::table('exportdt')->insertGetId([
                    'exportpk'   => $validated['exportpk'],
                    'contpk'     => $validated['contpk'],
                    'packpk'     => $pack->packpk,
                    'carton'     => $pack->carton,
                    'mif'        => $mif,
                    'qty'        => $pack->pcsp,
                ]);

                // 3. tentukan nomor part berikutnya berdasarkan popk
                $carton = $pack->carton;

                if (!array_key_exists($carton, $lastPartCache)) {

                    // ambil semua pack dalam BATCH LOAD INI yang punya carton sama (bisa lebih dari 1 popk)
                    $packsInCarton = $packs->where('carton', $carton);
                    $popksInCarton = $packsInCarton->pluck('popk')->unique();

                    // final HANYA kalau SEMUA popk di carton ini akan habis dimuat semua cartonnya setelah load ini
                    $isFinalLoad = true;
                    foreach ($popksInCarton as $p) {
                        $info = $popkCartonInfo[$p];
                        if (($info['loaded'] + $info['now']) < $info['total']) {
                            $isFinalLoad = false;
                            break;
                        }
                    }

                    if ($isFinalLoad) {
                        $lastPartCache[$carton] = 10;
                    } else {
                        // hitung part normal untuk TIAP popk yang ada di carton ini, lalu ambil yang PALING TINGGI
                        // supaya konsisten satu nilai untuk seluruh carton, dan tidak bentrok dengan part popk lain
                        $candidateParts = [];

                        foreach ($popksInCarton as $p) {
                            $existingPart = DB::connection('mysql_andon')->table('pack')
                                ->where('popk', $p)
                                ->where('exportpk', $validated['exportpk'])
                                ->value('part');

                            if ($existingPart) {
                                $candidateParts[] = $existingPart;
                            } else {
                                $maxPart = DB::connection('mysql_andon')->table('pack')
                                    ->where('popk', $p)
                                    ->where('part', '!=', 10)
                                    ->max('part');

                                $candidateParts[] = ($maxPart ?? 0) + 1;
                            }
                        }

                        $lastPartCache[$carton] = max($candidateParts);
                    }
                }

                $part = $lastPartCache[$carton];

                DB::connection('mysql_andon')->table('pack')
                    ->where('packpk', $pack->packpk)
                    ->update([
                        'exportpk'   => $validated['exportpk'],
                        'exportdtpk' => $exportdtpk,
                        'contpk'     => $validated['contpk'],
                        'part'       => $part,
                    ]);

                $insertColumns = [
                    'packpk',
                    'popk',
                    'urut',
                    'carton',
                    'nobar',
                    'OP',
                    'POno',
                    'customer',
                    'material',
                    'nw',
                    'gw',
                    'meas',
                    'secsz',
                ];

                // qtyp1 - qtyp40
                for ($i = 1; $i <= 40; $i++) {
                    $insertColumns[] = "qtyp{$i}";
                }

                $insertColumns[] = 'pcsp';

                // qty1 - qty40
                for ($i = 1; $i <= 40; $i++) {
                    $insertColumns[] = "qty{$i}";
                }

                $insertColumns = array_merge($insertColumns, [
                    'pcs',
                    'jmlpcs',
                    'waktu',
                    'tanggal',
                    'status',
                    'part',
                    'keterangan',
                    'pinjam',
                    'kembali',
                    'fca',
                ]);

                DB::connection('mysql_andon')
                    ->table('ship')
                    ->insertUsing(
                        $insertColumns,
                        DB::connection('mysql_andon')
                            ->table('pack')
                            ->select($insertColumns)
                            ->where('packpk', $pack->packpk)
                    );

                // simpan info ringkas untuk dikirim balik ke frontend
                $loadedItems[] = [
                    'exportdtpk' => $exportdtpk,
                    'carton'     => $pack->carton,
                    'pcsp'       => $pack->pcsp,
                    't_cbm'      => $pack->t_cbm ?? 0,
                ];
            }

            // ================= UPDATE totqty & totctn di tabel export =================
            $summary = DB::table('exportdt')
                ->where('exportpk', $validated['exportpk'])
                ->selectRaw('SUM(qty) as totqty, COUNT(DISTINCT carton) AS totctn')
                ->first();

            DB::table('export')
                ->where('exportpk', $validated['exportpk'])
                ->update([
                    'totqty' => $summary->totqty ?? 0,
                    'totctn' => $summary->totctn ?? 0,
                ]);


            // ================= UPDATE totqty & totctn di tabel export =================
            $summary = DB::table('exportdt')
                ->where('exportpk', $validated['exportpk'])
                ->selectRaw('SUM(qty) as totqty, COUNT(DISTINCT carton) AS totctn')
                ->first();

            // dd($summary->totctn);

            DB::table('export')
                ->where('exportpk', $validated['exportpk'])
                ->update([
                    'totqty' => $summary->totqty ?? 0,
                    'totctn' => $summary->totctn ?? 0,
                ]);

            DB::commit();

            $t_ctn = $packs->groupBy('carton');

            return response()->json([
                'message' => count($t_ctn) . ' carton berhasil dimuat ke container.',
                'loaded_items' => $loadedItems,
                'totqty'  => $summary->totqty ?? 0,
                'totctn'  => $summary->totctn ?? 0,
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            // dd($e->getMessage());
            report($e);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.',
            ], 500);
        }
    }

    // dipanggil saat klik "x" pada chip carton, untuk membatalkan muat
    // public function unload_carton(Request $request)
    // {
    //     $validated = $request->validate([
    //         'exportdtpk' => 'required|integer|exists:exportdt,exportdtpk',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         $exportdt = DB::table('exportdt')->where('exportdtpk', $validated['exportdtpk'])->first();

    //         if (empty($exportdt)) {
    //             throw new \Exception('Data carton tidak ditemukan.');
    //         }

    //         $exportpk = $exportdt->exportpk;

    //         DB::connection('mysql_andon')->table('pack')->where('packpk', $exportdt->packpk)->update([
    //             'exportpk'      => null,
    //             'exportdtpk'    => null,
    //             'contpk'        => null,
    //             'part'          => null,
    //         ]);

    //         DB::connection('mysql_andon')->table('ship')->where('packpk', $exportdt->packpk)->delete();

    //         Exportdt::where('exportdtpk', $validated['exportdtpk'])->delete();

    //         // ================= UPDATE totqty & totctn di tabel export =================
    //         $summary = DB::table('exportdt')
    //             ->where('exportpk', $exportpk)
    //             ->selectRaw('SUM(qty) as totqty, COUNT(packpk) as totctn')
    //             ->first();

    //         // dd($summary->totctn);

    //         DB::table('export')
    //             ->where('exportpk', $exportpk)
    //             ->update([
    //                 'totqty' => $summary->totqty ?? 0,
    //                 'totctn' => $summary->totctn ?? 0,
    //             ]);

    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Carton berhasil dikeluarkan dari container.',
    //             'totqty'  => $summary->totqty ?? 0,
    //             'totctn'  => $summary->totctn ?? 0,
    //         ]);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         dd($e->getMessage());
    //         // report($e);
    //         // return response()->json(['message' => 'Gagal mengeluarkan carton.'], 500);
    //     }
    // }

    public function unload_carton(Request $request)
    {
        $validated = $request->validate([
            'exportdtpk'   => 'required|array|min:1',
            'exportdtpk.*' => 'integer|exists:exportdt,exportdtpk',
        ]);

        DB::beginTransaction();
        try {
            $exportdtRows = DB::table('exportdt')->whereIn('exportdtpk', $validated['exportdtpk'])->get();

            if ($exportdtRows->isEmpty()) {
                throw new \Exception('Data carton tidak ditemukan.');
            }

            $exportpk = $exportdtRows->first()->exportpk;
            $packpks = $exportdtRows->pluck('packpk')->toArray();

            DB::connection('mysql_andon')->table('pack')->whereIn('packpk', $packpks)->update([
                'exportpk'   => null,
                'exportdtpk' => null,
                'contpk'     => null,
                'part'       => null,
            ]);

            DB::connection('mysql_andon')->table('ship')->whereIn('packpk', $packpks)->delete();

            DB::table('exportdt')->whereIn('exportdtpk', $validated['exportdtpk'])->delete();

            // ================= UPDATE totqty & totctn di tabel export =================
            $summary = DB::table('exportdt')
                ->where('exportpk', $exportpk)
                ->selectRaw('SUM(qty) as totqty, COUNT(DISTINCT carton) AS totctn')
                ->first();

            DB::table('export')
                ->where('exportpk', $exportpk)
                ->update([
                    'totqty' => $summary->totqty ?? 0,
                    'totctn' => $summary->totctn ?? 0,
                ]);

            DB::commit();

            return response()->json([
                'message' => count($validated['exportdtpk']) . ' carton berhasil dikeluarkan dari container.',
                'totqty'  => $summary->totqty ?? 0,
                'totctn'  => $summary->totctn ?? 0,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            // report($e);
            // return response()->json(['message' => 'Gagal mengeluarkan carton.'], 500);
        }
    }

    public function unload_all_carton(Request $request)
    {
        $request->validate([
            'contpk' => 'required|integer',
            'exportdtpks' => 'required|array|min:1',
            'exportdtpks.*' => 'integer',
        ]);

        DB::beginTransaction();
        try {
            $exportdtRows = DB::table('exportdt')->whereIn('exportdtpk', $request->exportdtpks)->get();
            if ($exportdtRows->isEmpty()) {
                throw new \Exception('Data carton tidak ditemukan.');
            }
            $exportpk = $exportdtRows->first()->exportpk;
            $packpks = $exportdtRows->pluck('packpk')->toArray();

            DB::connection('mysql_andon')->table('pack')->whereIn('packpk', $packpks)->update([
                'exportpk'   => null,
                'exportdtpk' => null,
                'contpk'     => null,
                'part'       => null,
            ]);

            DB::connection('mysql_andon')->table('ship')->whereIn('packpk', $packpks)->delete();

            DB::table('exportdt')
                ->where('contpk', $request->contpk)
                ->whereIn('exportdtpk', $request->exportdtpks)
                ->delete();

            // ================= UPDATE totqty & totctn di tabel export =================
            $summary = DB::table('exportdt')
                ->where('exportpk', $exportpk)
                ->selectRaw('SUM(qty) as totqty, COUNT(DISTINCT carton) AS totctn')
                ->first();

            DB::table('export')
                ->where('exportpk', $exportpk)
                ->update([
                    'totqty' => $summary->totqty ?? 0,
                    'totctn' => $summary->totctn ?? 0,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Semua carton berhasil dikeluarkan.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            // report($e);
            // return response()->json(['message' => 'Gagal mengeluarkan carton.'], 500);
        }
    }

    public function detail_json($exportpk)
    {
        $export = DB::table('export')->where('exportpk', $exportpk)->first();

        if (!$export) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $containers = DB::table('cont')
            ->where('exportpk', $exportpk)
            ->select('contpk', 'contno', 'type', 'fwdpk', 'vessname', 'volume')
            ->get();

        return response()->json([
            'pebno'      => $export->pebno,
            'vassalpk'   => $export->vassalpk,
            'fwdpk'      => $containers->first()->fwdpk ?? null,
            'etdvess'    => $export->etdvess,
            'etavess'    => $export->etavess,
            'totqty'    => $export->totqty,
            'totnw'     => $export->totnw,
            'totgw'     => $export->totgw,
            'totctn'     => $export->totctn,
            'totvol'     => $export->totvol,
            'ukctn'     => $export->ukctn,
            'exdate'     => $export->exdate,
            'closetime'  => $export->closetime,
            'contdate'    => $export->contdate,
            'actcontdate'    => $export->actcontdate,
            'remark'  => $export->remark,
            'plbpk'  => $export->plbpk,
            'containers' => $containers,
        ]);
    }

    public function togglePublish(Request $request)
    {
        $validated = $request->validate([
            'exportpk' => 'required|integer|exists:export,exportpk',
            'stsexp'   => 'required|in:0,1',
        ]);

        DB::table('export')
            ->where('exportpk', $validated['exportpk'])
            ->update(['stsexp' => $validated['stsexp']]);

        $message = $validated['stsexp'] == 1
            ? 'Shipment plan berhasil di-publish.'
            : 'Shipment plan berhasil di-unpublish.';

        return response()->json(['message' => $message]);
    }

    public function toggleLock(Request $request)
    {
        $validated = $request->validate([
            'exportpk' => 'required|integer|exists:export,exportpk',
        ]);
        $exportpk = $validated['exportpk'];
        $exportRow = DB::table('export')->where('exportpk', $exportpk)->first();

        $packpks = DB::table('exportdt')
            ->where('exportpk', $exportpk)
            ->whereNotNull('packpk')
            ->pluck('packpk')
            ->toArray();

        if (empty($packpks)) {
            return response()->json([
                'success' => false,
                'icon'    => 'warning',
                'message' => 'Tidak ada carton (packpk) pada export ini.',
            ], 422);
        }

        $packingCallOk  = false;
        $packingMessage = null;
        $packingResults = [];
        $packingErrors  = [];

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->asJson()
                ->post('http://192.168.0.8/packinglist_v2/exim/lock-shipment', [
                    'exportpk' => $exportpk,
                    'exdate'   => $exportRow->exdate,
                    'packpks'  => $packpks,
                ]);

            $body = $response->json() ?? [];

            $packingCallOk  = $response->successful() || $response->status() === 207;
            $packingMessage = $body['message'] ?? null;
            $packingResults = $body['results'] ?? [];
            $packingErrors  = $body['errors'] ?? [];

            if (!$response->successful() && $response->status() !== 207) {
                $packingMessage = $packingMessage ?? 'Sistem Packing menolak permintaan (HTTP ' . $response->status() . ').';
            }
        } catch (\Throwable $e) {
            $packingCallOk  = false;
            $packingMessage = 'Sistem Packing & Finishing tidak dapat dihubungi: ' . $e->getMessage();
        }

        if (!$packingCallOk || !empty($packingErrors)) {
            $errorDetail = !empty($packingErrors)
                ? implode('; ', $packingErrors)
                : ($packingMessage ?? 'tidak diketahui');

            return response()->json([
                'success'      => false,
                'icon'         => 'error',
                'message'      => "Gagal posting ke database LPB, shipplan TIDAK dilock: {$errorDetail}. Silakan coba lagi.",
                'opit_posted'  => false,
                'opit_results' => $packingResults,
                'opit_errors'  => $packingErrors,
            ], 422);
        }

        DB::table('export')->where('exportpk', $exportpk)->update([
            'lockship' => 1,
        ]);

        return response()->json([
            'success'      => true,
            'icon'         => 'success',
            'message'      => 'Shipplan berhasil dilock & data berhasil di-posting ke database LPB.',
            'opit_posted'  => true,
            'opit_results' => $packingResults,
            'opit_errors'  => [],
        ]);
    }

    public function exportExcel($exportpk)
    {
        return Excel::download(
            new ShipmentPackingListExport($exportpk),
            "Packing_List_Export_{$exportpk}.xlsx"
        );
    }
}
