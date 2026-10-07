<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Exports\ShipExport;
use Maatwebsite\Excel\Facades\Excel;


class EximImportController extends Controller
{
public function order(Request $request)
{
    $orders = $this->buildOrderQuery($request);
    [$paymentTerms, $handles] = $this->getOrderFilterOptions();
    $filterViewData = $this->buildOrderFilterViewData($request, $paymentTerms, $handles);

    return view('menu.exim-import.order', array_merge(
        compact('orders', 'paymentTerms', 'handles'),
        $filterViewData
    ));
}

public function orderData(Request $request)
{
    $orders = $this->buildOrderQuery($request);
    [$paymentTerms, $handles] = $this->getOrderFilterOptions();
    $filterViewData = $this->buildOrderFilterViewData($request, $paymentTerms, $handles);

    $filterHtml = view('menu.exim-import.prt.filter-order', array_merge(
        compact('paymentTerms', 'handles'),
        $filterViewData
    ))->render();

    $sortHtml = view('menu.exim-import.prt.sort-order')->render();

    $tableHtml = view('menu.exim-import.prt.table-order', array_merge(
        compact('orders'),
        $filterViewData
    ))->render();

    return response()->json([
        'filterHtml' => $filterHtml,
        'sortHtml' => $sortHtml,
        'tableHtml' => $tableHtml,
    ]);
}

private function buildOrderQuery(Request $request)
{

    // ==== SINKRONKAN po.stsimp SEBELUM QUERY UTAMA, PERSIS POLA NATIVE ====
    // DB::connection('mysql_sig')->statement("
    //         UPDATE po
    //     LEFT JOIN (
    //         SELECT popk, SUM(qtys) AS shipped_qty
    //         FROM importdt
    //         WHERE stsdt = '0'
    //         GROUP BY popk
    //     ) shipped ON shipped.popk = po.popk
    //     LEFT JOIN (
    //         SELECT popk, SUM(pocons) AS po_qty
    //         FROM podt
    //         GROUP BY popk
    //     ) podt_sum ON podt_sum.popk = po.popk
    //     SET po.stsimp = CASE
    //         WHEN COALESCE(shipped.shipped_qty, 0) <= 0 THEN 0
    //         WHEN shipped.shipped_qty < podt_sum.po_qty THEN 1
    //         ELSE 2
    //     END

    // ");
    $orders = DB::connection('mysql_sig')->table('po')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        // ->leftJoin('list', 'list.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->leftJoin('cur', 'cur.curpk', '=', 'po.curpk')
        ->leftJoin('fu', 'fu.fupk', '=', 'ord.fupk')
        ->select(
            'po.popk',
            'po.pono',
            // 'ord.ordno',
            DB::raw('GROUP_CONCAT(DISTINCT ord.ordno ORDER BY ord.ordno SEPARATOR ", ") as ordno'),
            'sup.supnm',
            'byr.byrnm',
            DB::raw('SUM(podt.pocons) as total'),
            'podt.unit',
            'po.potop',
            'po.freightterm',
            'po.paydate',
            // 'list.tglbayar',
            'po.etd',
            'po.stsimp',
            'po.dateupdt',
            'po.tpo2d',
            'po.tglpo',

        DB::raw("
            CASE
                WHEN ? = 1 THEN DATE(po.etd)
                WHEN ? = 1 THEN DATE(po.etd)
                WHEN ? = 1 THEN DATE(po.dateupdt)
                ELSE DATE(po.tglpo)
            END as tglapv
        ")
        )
        ->addBinding([
            $request->cari8 == 1 ? 1 : 0,
            $request->cari9 == 1 ? 1 : 0,
            $request->cari7 == 1 ? 1 : 0,
        ], 'select')
        ->where('po.tglpo', '<>', '')
        ->where('podt.ordpk', '>', '0')
        // ->where('po.etd', '<>', '0000-00-00')
        ->where('po.polipk', '2');

    if ($request->filled('cari1')) {
        $cari1 = $request->cari1;

        $orders->whereRaw(
            "CONCAT(po.pono, ord.ordno, sup.supnm, byr.byrnm) LIKE ?",
            ["%{$cari1}%"]
        );
    }
    // if ($request->filled('cari1')) {
    //     $cari1 = $request->cari1;

    //     $orders->where(function ($q) use ($cari1) {
    //         $q->where('po.pono', 'like', "%{$cari1}%")
    //             ->orWhere('ord.ordno', 'like', "%{$cari1}%")
    //             ->orWhere('sup.supnm', 'like', "%{$cari1}%")
    //             ->orWhere('byr.byrnm', 'like', "%{$cari1}%");
    //     });
    // }

    if ($request->filled('cari2')) {
        $orders->whereIn('po.stsimp', (array) $request->cari2);
    }

    if ($request->filled('cari3')) {
        $orders->where('po.mpaypk', $request->cari3);
    }

    if ($request->filled('md')) {
        $orders->where('fu.fupk', $request->md);
    }

    // $dateRangeCode = $request->input('cari4', '');

    // if ($dateRangeCode === '') {
    //     // No Supplier ETD range explicitly chosen — fall back to PO year (this
    //     // year onward) instead of filtering by Supplier ETD, so POs that don't
    //     // have a Supplier ETD filled in yet still show up in the list.
    //     $orders->where('po.tglpo', '>=', Carbon::now()->startOfYear()->toDateString());
    // } elseif ($dateRangeCode !== 'all') {
    //     $today = Carbon::now();

    //     switch ($dateRangeCode) {
    //         case 'today':
    //             $orders->whereDate('po.etd', $today->toDateString());
    //             break;
    //         case '1w':
    //             $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addWeek()->toDateString()]);
    //             break;
    //         case '2w':
    //             $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addWeeks(2)->toDateString()]);
    //             break;
    //         case '1m':
    //             $orders->whereBetween('po.etd', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()]);
    //             break;
    //         case '1mn':
    //             $orders->whereBetween('po.etd', [
    //                 $today->copy()->addMonthNoOverflow()->startOfMonth()->toDateString(),
    //                 $today->copy()->addMonthNoOverflow()->endOfMonth()->toDateString(),
    //             ]);
    //             break;
    //         case '3m':
    //             $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addMonths(3)->toDateString()]);
    //             break;
    //         case '6m':
    //             $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addMonths(6)->toDateString()]);
    //             break;
    //         case 'year':
    //         default:
    //             // Explicit "Tahun ini" pick — this year onward, based on Supplier ETD.
    //             $orders->where('po.etd', '>=', $today->copy()->startOfYear()->toDateString());
    //             break;
    //     }
    // } else {
    //     // 'all' → no date bound, but only POs that actually have a Supplier ETD filled in.
    //     $orders->whereNotNull('po.etd')
    //         ->where('po.etd', '<>', '')
    //         ->where('po.etd', '<>', '0000-00-00');
    // }

    $dateRangeCode = $request->input('cari4', '');

    // Kalau cari4 bukan salah satu opsi valid (misal nyangkut garbage value
    // lama seperti "2026"), anggap saja belum dipilih.
    $validDateRangeCodes = ['all', 'today', '1w', '2w', '1m', '1mn', '3m', '6m', 'year'];
    if ($dateRangeCode !== '' && !in_array($dateRangeCode, $validDateRangeCodes, true)) {
        $dateRangeCode = '';
    }

    // Kalau user sedang search spesifik (cari1 terisi), jangan batasi hasil
    // berdasarkan tanggal sama sekali — biar PO yang cocok selalu ketemu,
    // nggak peduli ETD/tglpo-nya kapan.
    if ($request->filled('cari1')) {
        // no date filter
    } elseif ($dateRangeCode === '') {
        // No Supplier ETD range explicitly chosen — fall back to PO year (this
        // year onward) instead of filtering by Supplier ETD, so POs that don't
        // have a Supplier ETD filled in yet still show up in the list.
        $orders->where('po.tglpo', '>=', Carbon::now()->startOfYear()->toDateString());
    } elseif ($dateRangeCode !== 'all') {
        $today = Carbon::now();

        switch ($dateRangeCode) {
            case 'today':
                $orders->whereDate('po.etd', $today->toDateString());
                break;
            case '1w':
                $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addWeek()->toDateString()]);
                break;
            case '2w':
                $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addWeeks(2)->toDateString()]);
                break;
            case '1m':
                $orders->whereBetween('po.etd', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()]);
                break;
            case '1mn':
                $orders->whereBetween('po.etd', [
                    $today->copy()->addMonthNoOverflow()->startOfMonth()->toDateString(),
                    $today->copy()->addMonthNoOverflow()->endOfMonth()->toDateString(),
                ]);
                break;
            case '3m':
                $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addMonths(3)->toDateString()]);
                break;
            case '6m':
                $orders->whereBetween('po.etd', [$today->toDateString(), $today->copy()->addMonths(6)->toDateString()]);
                break;
            case 'year':
            default:
                // Explicit "Tahun ini" pick — this year onward, based on Supplier ETD.
                $orders->where('po.etd', '>=', $today->copy()->startOfYear()->toDateString());
                break;
        }
    } else {
        // 'all' → no date bound, but only POs that actually have a Supplier ETD filled in.
        $orders->whereNotNull('po.etd')
            ->where('po.etd', '<>', '')
            ->where('po.etd', '<>', '0000-00-00');
    }

    $orders = $orders->groupBy('po.popk');

    // if ($request->cari5 == 1) {
    //     $orders->orderByDesc('po.tglpo')
    //         ->orderByDesc('po.pono');
    // } elseif ($request->cari6 == 1) {
    //     $orders->orderBy('po.pono')
    //         ->orderBy('po.pono');
    // } elseif ($request->cari7 == 1) {
    //     $orders->orderByDesc('po.dateupdt');
    // } elseif ($request->cari8 == 1) {
    //     $orders->orderByDesc('po.etd');
    // } else {
    //     $orders->orderByDesc('po.tglpo')
    //         ->orderByDesc('po.pono');
    // }

    if ($request->cari5 == 1) {
    $orders->orderByDesc('po.tglpo')
        ->orderByDesc('po.pono');
    } elseif ($request->cari6 == 1) {
        $orders->orderBy('po.pono')
            ->orderBy('po.pono');
    } elseif ($request->cari7 == 1) {
        $orders->orderByDesc('po.dateupdt');
    } elseif ($request->cari8 == 1) {
        $orders->orderBy('po.etd')
        ->orderByDesc('po.pono');       // ETD (Asc)
    } elseif ($request->cari9 == 1) {
        $orders->orderByDesc('po.etd')
        ->orderByDesc('po.pono');   // ETD (Dsc)
    } else {
        $orders->orderByDesc('po.tglpo')
            ->orderByDesc('po.pono');
    }

    
    // return $orders->get();
    $perPage = (int) $request->input('rows', 50);
    return $orders->paginate($perPage)->withQueryString();
}

private function getOrderFilterOptions()
{
    $paymentTerms = DB::connection('mysql_sig')
        ->table('mpay')
        ->select('mpaypk', 'paynm')
        ->groupBy('mpaypk', 'paynm')
        ->orderBy('mpaypk')
        ->get();

    $thn = date('Y');

    $handles = DB::connection('mysql_sig')
        ->table('po')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('fu', 'fu.fupk', '=', 'ord.fupk')
        ->select('fu.fupk','fu.funm')
        ->where('po.tglpo', '<>', '')
        ->whereYear('po.tglpo', '>=', $thn)
        ->where('po.etd', '<>', '0000-00-00')
        // ->where('stsapv', '1')
        ->where('po.polipk', '2')
        ->groupBy('fu.fupk')
        ->orderBy('fu.funm')
        ->get();

    return [$paymentTerms, $handles];
}

private function buildOrderFilterViewData(Request $request, $paymentTerms, $handles)
{
    $statusOptions = [
        '0' => 'On Hold',
        '2' => 'Processed',
        '1' => 'Partial ship.',
    ];
    $currentStatuses = array_map('strval', (array) $request->input('cari2', []));

    if (count($currentStatuses) === 0) {
        $statusDropdownLabel = 'Status Planning';
    } elseif (count($currentStatuses) === 1) {
        $statusDropdownLabel = $statusOptions[$currentStatuses[0]] ?? 'Status Planning';
    } else {
        $statusDropdownLabel = 'Status (' . count($currentStatuses) . ')';
    }

    $currentTerm = $request->input('cari3', '');
    $currentTermLabel = 'Payment Term';

    if ($request->filled('cari3')) {
        foreach ($paymentTerms as $term) {
            if (!empty($term->paynm) && (string) $term->mpaypk === (string) $currentTerm) {
                $currentTermLabel = $term->paynm;
                break;
            }
        }
    }

    $currentHandle = $request->input('md', '');
    $currentHandleLabel = 'Handle';

    if ($request->filled('md')) {
        foreach ($handles as $handle) {
            if (!empty($handle->fupk) && (string) $handle->fupk === (string) $currentHandle) {
                $currentHandleLabel = $handle->funm;
                break;
            }
        }
    }

    $dateRangeOptions = [
        'all' => 'All Time',
        'today' => 'Hari ini',
        '1w' => 'Minggu depan',
        '2w' => '2 Minggu depan',
        '1m' => 'Bulan ini',
        '1mn' => 'Bulan depan',
        '3m' => '3 Bulan depan',
        '6m' => '6 Bulan depan',
        'year' => 'Tahun ini',
    ];
    // Keep the raw (possibly empty) value here — the "this year onward" default
    // is only a server-side fallback in buildOrderQuery() when cari4 is absent.
    // Pre-filling this with 'year' would make the hidden form field silently
    // bake cari4=year into the URL the moment any other filter/sort is submitted.
    $currentDateRange = $request->input('cari4', '');
    $currentDateRangeLabel = $currentDateRange !== ''
        ? ($dateRangeOptions[$currentDateRange] ?? 'Supplier ETD')
        : 'Supplier ETD';

    $activeFilters = [];

    // if ($request->filled('cari1')) {
    //     $activeFilters[] = [
    //         'label' => 'Search: "' . $request->input('cari1') . '"',
    //         'query' => $request->except('cari1'),
    //     ];
    // }

    // foreach ($currentStatuses as $statusValue) {
    //     $activeFilters[] = [
    //         'label' => $statusOptions[$statusValue] ?? $statusValue,
    //         'query' => array_merge($request->except('cari2'), [
    //             'cari2' => array_values(array_diff($currentStatuses, [$statusValue])),
    //         ]),
    //     ];
    // }

    // if ($request->filled('cari3')) {
    //     $activeFilters[] = [
    //         'label' => $currentTermLabel,
    //         'query' => $request->except('cari3'),
    //     ];
    // }

    // if ($request->filled('md')) {
    //     $activeFilters[] = [
    //         'label' => $currentHandleLabel,
    //         'query' => $request->except('md'),
    //     ];
    // }

    // if ($request->filled('cari4')) {
    //     $activeFilters[] = [
    //         'label' => $currentDateRangeLabel,
    //         'query' => $request->except('cari4'),
    //     ];
    // }

    if ($request->filled('cari1')) {
        $activeFilters[] = [
            'label' => 'Search: "' . $request->input('cari1') . '"',
            'query' => $request->except('cari1', 'page'),
        ];
    }

    foreach ($currentStatuses as $statusValue) {
        $activeFilters[] = [
            'label' => $statusOptions[$statusValue] ?? $statusValue,
            'query' => array_merge($request->except('cari2', 'page'), [
                'cari2' => array_values(array_diff($currentStatuses, [$statusValue])),
            ]),
        ];
    }

    if ($request->filled('cari3')) {
        $activeFilters[] = [
            'label' => $currentTermLabel,
            'query' => $request->except('cari3', 'page'),
        ];
    }

    if ($request->filled('md')) {
        $activeFilters[] = [
            'label' => $currentHandleLabel,
            'query' => $request->except('md', 'page'),
        ];
    }

    if ($request->filled('cari4')) {
        $activeFilters[] = [
            'label' => $currentDateRangeLabel,
            'query' => $request->except('cari4', 'page'),
        ];
    }

    return compact(
        'statusOptions', 'currentStatuses', 'statusDropdownLabel',
        'currentTerm', 'currentTermLabel',
        'currentHandle', 'currentHandleLabel',
        'dateRangeOptions', 'currentDateRange', 'currentDateRangeLabel',
        'activeFilters'
    );
}

public function clearOrderNotice($popk)
{
    DB::connection('mysql_sig')
        ->table('po')
        ->where('popk', $popk)
        ->update(['tpo2d' => '0']);

    return response()->json(['success' => true]);
}

public function getPoShip(Request $request)
{
    $popks = $request->input('popks', []);

    if (empty($popks)) {
        return response()->json([
            'success' => false,
            'message' => 'No PO selected',
            'data' => []
        ]);
    }

    $connection = DB::connection('mysql_sig');

    // Subquery: total qty yang sudah dialokasikan ke shipment plan lain (draft, stsdt='0')
    $allocSubquery = $connection->table('importdt')
        ->select('podtpk', DB::raw('SUM(qtys) as allocated_qty'))
        ->where('stsdt', '0')
        ->groupBy('podtpk');

    $items = $connection->table('podt')
        ->leftJoin('po', 'po.popk', '=', 'podt.popk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('rkb', 'rkb.popk', '=', 'po.popk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->leftJoin('cur', 'cur.curpk', '=', 'po.curpk')
        ->leftJoinSub($allocSubquery, 'alloc', function ($join) {
            $join->on('alloc.podtpk', '=', 'podt.podtpk');
        })
        ->select(
            'po.popk',
            'po.pono',
            'sup.supnm',
            'ord.ordno',
            'byr.byrnm',
            'podt.podtpk',
            'podt.pocons',
            'podt.unit',
            'podt.spec',
            'podt.price',
            'podt.colname',
            'rkb.item',
            'cur.curid',
            'cur.cursign',
            DB::raw('COALESCE(alloc.allocated_qty, 0) as allocated_qty')
        )
        ->whereIn('po.popk', $popks)
        ->groupBy('podt.podtpk')
        ->orderByDesc('po.popk')
        ->get();

    // Hitung balance = qty PO dikurangi qty yang sudah dialokasikan ke shipment lain
    $items = $items->map(function ($item) {
        $item->balance = $item->pocons - $item->allocated_qty;
        return $item;
    });

    return response()->json([
        'success' => true,
        'data' => $items
    ]);
}

public function detpopur($po)
{
    $detail = DB::connection('mysql_sig')->table('po')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->leftJoin('poli', 'poli.polipk', '=', 'po.polipk')
        ->leftJoin('pourg', 'pourg.pourgpk', '=', 'po.pourgpk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('cur', 'cur.curpk', '=', 'podt.curpk')
        ->select(
            'po.popk',
            'po.pono',
            'po.surcharge',
            'po.surcharge2',
            'po.note',
            'po.piamount',
            'po.piform',
            'po.ket',
            'po.etd',
            'po.brg',
            'po.mpaypk',
            'po.potop',
            'po.tgldel',
            'po.eihl',
            'po.postatpk',
            'po.tglpo',
            'po.pidate',
            'po.paydate',
            'po.invoiceno',
            'po.freightterm',
            'po.etdvl',
            'po.etdal',
            'sup.supnm',
            'sup.suppk',
            'poli.polinm',
            'poli.polipk',
            'pourg.pourgnm',
            'pourg.pourgpk',
            'podt.curpk',
            'podt.unit2',
            'podt.hspec1',
            'podt.hspec2',
            'podt.hspec3',
            'podt.hspec4',
            'podt.hspec5',
            'podt.hspec6',
            'podt.hspec7',
            'podt.hspec8',
            'podt.spec1',
            'podt.spec2',
            'podt.spec3',
            'podt.spec4',
            'podt.spec5',
            'podt.spec6',
            'podt.spec7',
            'podt.spec8',
            'podt.tspec',
            'cur.curid',
            'cur.cursign',
            'ord.season',
            'ord.stage',
            'byr.byrnm',
            DB::raw('SUM(podt.pocons * podt.price) as amount')
        )
        ->where('po.popk', $po)
        ->groupBy('po.popk')
        ->first();

    if (!$detail) {
        abort(404);
    }

    // Ambil shipment plan terakhir untuk PO ini (fallback ETDV/ETDA/EIH)
    $lastShipment = DB::connection('mysql_sig')->table('importdt')
        ->leftJoin('import', 'import.importpk', '=', 'importdt.importpk')
        ->select('import.ETDV', 'import.ETDA', 'import.EIH')
        ->where('importdt.popk', $po)
        ->where('import.arrivalpk', '>', 0)
        ->orderByDesc('import.EIH')
        ->first();

    // Logic sesuai native: kalau polipk==1 pakai field PO langsung, selain itu pakai shipment plan terakhir
    if ($detail->polipk == 1) {
        $etdv2 = $detail->etdvl;
        $eta2  = $detail->etdal;
        $eih2  = $detail->eihl;
    } else {
        $etdv2 = $lastShipment->ETDV ?? null;
        $eta2  = $lastShipment->ETDA ?? null;
        $eih2  = $lastShipment->EIH ?? null;
    }

    // $materials = DB::connection('mysql_sig')->table('podt')
    //     ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
    //     ->leftJoin('rkb', 'rkb.popk', '=', 'podt.popk')
    //     ->select(
    //         'podt.podtpk',
    //         'ord.ordno',
    //         'rkb.item',
    //         'podt.spec',
    //         'podt.colname',
    //         'podt.colcode',
    //         'podt.sizegar',
    //         'podt.unit',
    //         'podt.pocons',
    //         'podt.price',
    //         DB::raw('(podt.pocons * podt.price) as total')
    //     )
    //     ->where('podt.popk', $po)
    //     ->groupBy('podt.podtpk')
    //     ->get();

    // ================= MATERIALS (dengan RKB comparison) =================

    $rawMaterials = DB::connection('mysql_sig')->table('podt')
        ->leftJoin('po', 'po.popk', '=', 'podt.popk')
        ->leftJoin('rkb', 'podt.rkbpk', '=', 'rkb.rkbpk') // FIX: join by rkbpk, bukan popk
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('cur', 'cur.curpk', '=', 'podt.curpk')
        ->select(
            'rkb.rkbpk',
            'podt.podtpk',
            'podt.item',
            'podt.spec',
            'podt.colname',
            'podt.colcode',
            'podt.sizegar',
            'podt.unit',
            'podt.pocons',
            'podt.qrs',
            'podt.price',
            'podt.curpk',
            'podt.delsts',
            'cur.curid',
            'cur.cursign',
            'ord.ordno',
            'rkb.pocons as rpocons',
            'rkb.qrs as rqrs',
            'rkb.price as rprice',
            'rkb.curpk as rcurpk',
            'rkb.stspk',
            'po.postatpk'
        )
        ->where('podt.popk', $po)
        ->where('podt.stspk', '>=', 1)
        ->groupBy('podt.rkbpk')
        ->orderBy('ord.ordno')
        ->get();

    $kurs = DB::connection('mysql_sig')->table('kurs')->orderByDesc('kurspk')->first();

    $unitsNoRound = ['KG', 'DZN', 'RIM', 'GROSS'];

    $materials = $rawMaterials->values()->map(function ($data, $index) use ($kurs, $unitsNoRound) {

        $qrs  = $data->qrs ?? 0;
        $rqrs = $data->rqrs ?? 0;

        // Qty ditampilkan (dikurangi PPS), sesuai native
        if (in_array($data->unit, $unitsNoRound)) {
            $pocons1  = $data->pocons - $qrs;
            $rpocons1 = ($data->rpocons ?? 0) - $rqrs;
        } else {
            $pocons1  = ceil($data->pocons - $qrs);
            $rpocons1 = ceil(($data->rpocons ?? 0) - $rqrs);
        }

        $cek = $pocons1 > $rpocons1 ? 1 : 0;

        // Currency sign
        $dtcursign = ($data->rcurpk == 3) ? '€' : ($data->cursign ?? '');
        $cursign   = ($data->curpk == 3) ? '€' : ($data->cursign ?? '');

        // RKB price & total (total pakai qty MENTAH, bukan yang sudah dikurangi PPS -- sesuai native)
        if (!$data->rprice || $data->rprice == 0) {
            $rprice = '-';
            $rtotal = '-';
        } else {
            $rQtyForTotal = in_array($data->unit, $unitsNoRound) ? $data->rpocons : ceil($data->rpocons ?? 0);
            $decimals = ($data->rcurpk == 1) ? 2 : 5; // curpk 1 = IDR
            $rprice = number_format($data->rprice, $decimals, '.', ',');
            $rtot   = $data->rprice * $rQtyForTotal;
            $rtotal = $dtcursign . number_format($rtot, $decimals, '.', ',');
        }

        // PO price & total
        if (!$data->price || $data->price == 0) {
            $price = '-';
            $total = '-';
        } else {
            $qtyForTotal = in_array($data->unit, $unitsNoRound) ? $data->pocons : ceil($data->pocons);
            $decimals = ($data->curid == 'IDR') ? 2 : 5;
            $price = number_format($data->price, $decimals, '.', ',');
            $tot   = $data->price * $qtyForTotal;
            $total = $cursign . number_format($tot, $decimals, '.', ',');
        }

        // Over price check (cek2)
        $cek2 = 0;
        if ($data->price > 0) {
            if ($data->rcurpk == 2) {
                if ($data->curpk == 1) {
                    $pprice2 = number_format($data->price, 2, '.', '');
                    $rprice2 = number_format($data->rprice * $kurs->rupiah, 2, '.', '');
                } else {
                    $pprice2 = number_format($data->price, 5, '.', '');
                    $rprice2 = number_format($data->rprice, 5, '.', '');
                }
            } else {
                $pprice2 = number_format($data->price, 2, '.', '');
                $rprice2 = number_format($data->rprice, 2, '.', '');
            }

            if ($pprice2 > $rprice2) {
                $cek2 = 1;
            }
        }

        // Status text (Subject)
        if ($data->stspk == 1) { $rev = 'Req PO'; }
        elseif ($data->stspk == 2) { $rev = 'PO Ready'; }
        elseif ($data->stspk == 4) { $rev = 'Req Revised'; }
        elseif ($data->stspk == 5) { $rev = 'Ready Revised'; }
        elseif ($data->stspk == 6) { $rev = 'Req Cancel'; }
        elseif ($data->stspk == 7) { $rev = 'Cancel PO'; }
        else { $rev = ($data->postatpk == 2) ? 'Req Revised' : ''; }

        // Row background color
        if ($data->delsts > 0) {
            $rowColor = 'color:#EF3B42; background-color:#ffffff;';
        } elseif ($cek == 1 && $cek2 == 1) {
            $rowColor = 'color:#000000; background-color:#FFCCCB;';
        } elseif ($cek == 1) {
            $rowColor = 'color:#000000; background-color:#FFE4B5;';
        } elseif ($cek2 == 1) {
            $rowColor = 'color:#000000; background-color:#FFCCCB;';
        } else {
            $rowColor = 'color:#000000; background-color:#ffffff;';
        }

        return (object) [
            'no'       => $index + 1,
            'rev'      => $rev,
            'ordno'    => $data->ordno,
            'item'     => $data->item,
            'spec'     => $data->spec,
            'colname'  => trim($data->colname ?? ''),
            'colcode'  => trim($data->colcode ?? ''),
            'sizegar'  => $data->sizegar,
            'unit'     => $data->unit,
            'rpocons'  => number_format($rpocons1, 2, '.', ','),
            'rqrs'     => $rqrs,
            'rprice'   => $rprice,
            'rtotal'   => $rtotal,
            'pocons'   => number_format($pocons1, 2, '.', ','),
            'qrs'      => $qrs,
            'price'    => $price,
            'total'    => $total,
            'rowColor' => $rowColor,
        ];
    });


    $orders = DB::connection('mysql_sig')
        ->table('podt')
        ->select('ordpk')
        ->where('popk', $po)
        ->groupBy('ordpk')
        ->get();

    $bqty = 0;

    foreach ($orders as $order) {

        $qty = DB::connection('mysql_sig')
            ->table('bdown')
            ->where('ordpk', $order->ordpk)
            ->where('jenis', 'like', '%DETAIL%')
            ->sum('tot');

        $bqty += $qty;
    }

    // ================= OVER PRICE =================

    $items = DB::connection('mysql_sig')
        ->table('podt')
        ->leftJoin('po', 'po.popk', '=', 'podt.popk')
        ->leftJoin('rkb', 'podt.rkbpk', '=', 'rkb.rkbpk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('cur', 'cur.curpk', '=', 'podt.curpk')
        ->select(
            'podt.unit',
            'podt.pocons',
            'podt.qrs',
            'podt.price',
            'podt.curpk',
            'rkb.pocons as rpocons',
            'rkb.qrs as rqrs',
            'rkb.price as rprice',
            'rkb.curpk as rcurpk'
        )
        ->where('podt.popk', $po)
        ->where('podt.stspk', '>=', 1)
        ->where('podt.delsts', '<>', 1)
        ->groupBy('podt.rkbpk')
        ->get();

    $kurs = DB::connection('mysql_sig')
        ->table('kurs')
        ->orderByDesc('kurspk')
        ->first();

    // ================= TOTAL RKB =================

    // IDR
    $totrp1 = DB::connection('mysql_sig')
        ->table('rkb')
        ->where('popk', $po)
        ->where('curpk', 1)
        ->sum(DB::raw('pocons * price'));

    $totrp2 = $totrp1 / $kurs->rupiah;

    // EUR
    $toteu = DB::connection('mysql_sig')
        ->table('rkb')
        ->where('popk', $po)
        ->where('curpk', 3)
        ->sum(DB::raw('CEIL(pocons) * price'));

    // USD
    $totd1 = DB::connection('mysql_sig')
        ->table('rkb')
        ->where('popk', $po)
        ->where('curpk', 2)
        ->sum(DB::raw('pocons * price'));

    $totd2 = $totd1 * $kurs->rupiah;

    if ($detail->curpk == 1) {
    $totrkb3 = $totrp1 + $totd2;
    }

    if ($detail->curpk == 2) {
        $totrkb3 = $totrp2 + $totd1;
    }

    if ($detail->curpk == 3) {
        $totrkb3 = $toteu;
    }

    // ================= PI AMOUNT =================

    $piamount = $detail->amount;

    // ================= PER GARMENT =================

    $perRkb = 'N/A';
    $perPi = 'N/A';
    $perRkb1 = 0;
    $perPi1 = 0;

        // Pindahkan penentuan cursign ke luar, supaya selalu ada nilainya
        if ($detail->curpk == 3) {
            $cursign = "€";
        } else {
            $cursign = $detail->cursign;
        }

        if ($bqty > 0) {

        $perRkb1 = $totrkb3 / $bqty;
        $perPi1  = $piamount / $bqty;

        if ($detail->curid == "IDR") {

            $perRkb = $cursign . number_format($perRkb1, 0, ".", ",");
            $perPi  = $cursign . number_format($perPi1, 0, ".", ",");

        } else {

            $perRkb = $cursign . number_format($perRkb1, 2, ".", ",");
            $perPi  = $cursign . number_format($perPi1, 2, ".", ",");

        }
    }

    // if ($bqty > 0) {

    //     $perRkb1 = $totrkb3 / $bqty;
    //     $perPi1  = $piamount / $bqty;

    //     if ($detail->curpk == 3) {
    //         $cursign = "€";
    //     } else {
    //         $cursign = $detail->cursign;
    //     }

    //     if ($detail->curid == "IDR") {

    //         $perRkb = $cursign . number_format($perRkb1, 0, ".", ",");
    //         $perPi  = $cursign . number_format($perPi1, 0, ".", ",");

    //     } else {

    //         $perRkb = $cursign . number_format($perRkb1, 2, ".", ",");
    //         $perPi  = $cursign . number_format($perPi1, 2, ".", ",");

    //     }
    // }

    if ($detail->curid == "IDR") {

    $totpi2  = number_format($piamount, 0, ".", ",");
    $totrkb2 = number_format($totrkb3, 0, ".", ",");

    } else {

        $totpi2  = number_format($piamount, 2, ".", ",");
        $totrkb2 = number_format($totrkb3, 2, ".", ",");

    }

    $profitPerGarment = null;
    $profitPerGarmentText = '';
    $profitColor = '#19B715'; // default hijau

    if ($bqty > 0 && $perRkb1 > 0 && $perPi1 > 0) {

        $profit1a = $perRkb1 - $perPi1;

        if ($totrkb2 != $totpi2) {

            // ===== MERAH =====
            if ($profit1a < 0) {

                $profit1b = abs($profit1a);

                $profitPercent = ($profit1b / $perRkb1) * 100;

                $profitPerGarment = -$profit1b;
                $profitColor = '#EF3B42';

                $profitPerGarmentText =
                    '-' .
                    number_format($profit1b, 2, '.', ',') .
                    '(' .
                    number_format($profitPercent, 2, '.', ',') .
                    '%)';

            }

            // ===== HIJAU =====
            elseif ($profit1a > 0) {

                $profitPercent = ($profit1a / $perRkb1) * 100;

                $profitPerGarment = $profit1a;
                $profitColor = '#19B715';

                $profitPerGarmentText =
                    '+' .
                    number_format($profit1a, 2, '.', ',') .
                    '(' .
                    number_format($profitPercent, 2, '.', ',') .
                    '%) profit';
            }

        }

        // ===== ABU-ABU (SAMA PERSIS DENGAN NATIVE) =====
        else {

            $profitPercent = ($profit1a / $perRkb1) * 100;

            $profitPerGarment = 0;
            $profitColor = '#00000061';

            $profitPerGarmentText =
                '+' .
                number_format($profit1a, 2, '.', ',') .
                '(' .
                number_format($profitPercent, 2, '.', ',') .
                '%) profit';
        }
    }


   // ================= BULK PURCHASE =================
    $totpi1 = $piamount;

    if ($detail->curid == "IDR") {

        $bulkRkb = $cursign . number_format($totrkb3, 0, ".", ",");
        $bulkPi  = $cursign . number_format($totpi1, 0, ".", ",");

    } else {

        $bulkRkb = $cursign . number_format($totrkb3, 2, ".", ",");
        $bulkPi  = $cursign . number_format($totpi1, 2, ".", ",");

    }

    $profitBulk = null;
    $profitBulkText = '';
    $profitBulkColor = '#19B715'; // default hijau
        if ($totrkb3 > 0 && $totpi1 > 0) {

        $profit2a = $totrkb3 - $totpi1;

        if ($totrkb2 != $totpi2) {

            if ($profit2a < 0) {

                $profit2b = abs($profit2a);

                $profitPercent = ($profit2b / $totrkb3) * 100;

                $profitBulk = -$profit2b;

                $profitBulkColor = '#EF3B42';

                $profitBulkText =
                    '-' .
                    number_format($profit2b, 2, '.', ',') .
                    '(' .
                    number_format($profitPercent, 2, '.', ',') .
                    '%)';

            } elseif ($profit2a > 0) {
                
                $profitBulkColor = '#19B715';

                $profitPercent = ($profit2a / $totrkb3) * 100;

                $profitBulk = $profit2a;

                $profitBulkText =
                    '+' .
                    number_format($profit2a, 2, '.', ',') .
                    '(' .
                    number_format($profitPercent, 2, '.', ',') .
                    '%) profit';

            }

        } else {

            $profitPercent = ($profit2a / $totrkb3) * 100;

            $profitBulk = 0;

            $profitBulkColor = '#00000061';

            $profitBulkText =
                '+' .
                number_format($profit2a, 2, '.', ',') .
                '(' .
                number_format($profitPercent, 2, '.', ',') .
                '%) profit';

        }

    }

    $overPrice = 0;

    foreach ($items as $item) {

        if ($item->price > 0) {

            if ($item->rcurpk == 2) {

                if ($item->curpk == 1) {

                    $pprice2 = number_format($item->price, 2, ".", "");
                    $rprice2 = number_format($item->rprice * $kurs->rupiah, 2, ".", "");

                } else {

                    $pprice2 = number_format($item->price, 5, ".", "");
                    $rprice2 = number_format($item->rprice, 5, ".", "");

                }

            } else {

                $pprice2 = number_format($item->price, 2, ".", "");
                $rprice2 = number_format($item->rprice, 2, ".", "");

            }

            if ($pprice2 > $rprice2) {
                $overPrice++;
            }
        }
    }

    // ================= OVER QTY =================

    $overQty = 0;

    foreach ($items as $item) {

        if (in_array(trim($item->unit), ['KG', 'DZN', 'RIM', 'GROSS'])) {

            $poCons = $item->pocons - $item->qrs;
            $rkbCons = $item->rpocons - $item->rqrs;

        } else {

            $poCons = ceil($item->pocons - $item->qrs);
            $rkbCons = ceil($item->rpocons - $item->rqrs);

        }

        if ($poCons > $rkbCons) {
            $overQty++;
        }
    }

    return view('menu.exim-import.detpopur', compact('detail', 'materials', 'overPrice', 
                                                    'overQty', 'bqty', 'perRkb', 'perPi', 
                                                    'profitPerGarment', 'profitPerGarmentText', 'profitColor', 
                                                    'bulkRkb', 'bulkPi', 'profitBulk', 'profitBulkText',
                                                    'profitBulkColor', 'etdv2', 'eta2', 'eih2'));
}

public function shipdetDetpopur($po)
{
        $detail = DB::connection('mysql_sig')->table('po')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->leftJoin('poli', 'poli.polipk', '=', 'po.polipk')
        ->leftJoin('pourg', 'pourg.pourgpk', '=', 'po.pourgpk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('cur', 'cur.curpk', '=', 'podt.curpk')
        ->select(
            'po.popk',
            'po.pono',
            'po.surcharge',
            'po.surcharge2',
            'po.note',
            'po.piamount',
            'po.piform',
            'po.ket',
            'po.etd',
            'po.brg',
            'po.mpaypk',
            'po.potop',
            'po.tgldel',
            'po.eihl',
            'po.postatpk',
            'po.tglpo',
            'po.pidate',
            'po.paydate',
            'po.invoiceno',
            'po.freightterm',
            'po.etdvl',
            'po.etdal',
            'sup.supnm',
            'sup.suppk',
            'poli.polinm',
            'poli.polipk',
            'pourg.pourgnm',
            'pourg.pourgpk',
            'podt.curpk',
            'podt.unit2',
            'podt.hspec1',
            'podt.hspec2',
            'podt.hspec3',
            'podt.hspec4',
            'podt.hspec5',
            'podt.hspec6',
            'podt.hspec7',
            'podt.hspec8',
            'podt.spec1',
            'podt.spec2',
            'podt.spec3',
            'podt.spec4',
            'podt.spec5',
            'podt.spec6',
            'podt.spec7',
            'podt.spec8',
            'podt.tspec',
            'cur.curid',
            'cur.cursign',
            'ord.season',
            'ord.stage',
            'byr.byrnm',
            DB::raw('SUM(podt.pocons * podt.price) as amount')
        )
        ->where('po.popk', $po)
        ->groupBy('po.popk')
        ->first();

        if (!$detail) {
            abort(404);
        }

        // Ambil shipment plan terakhir untuk PO ini (fallback ETDV/ETDA/EIH)
        $lastShipment = DB::connection('mysql_sig')->table('importdt')
            ->leftJoin('import', 'import.importpk', '=', 'importdt.importpk')
            ->select('import.ETDV', 'import.ETDA', 'import.EIH')
            ->where('importdt.popk', $po)
            ->where('import.arrivalpk', '>', 0)
            ->orderByDesc('import.EIH')
            ->first();

        // Logic sesuai native: kalau polipk==1 pakai field PO langsung, selain itu pakai shipment plan terakhir
        if ($detail->polipk == 1) {
            $etdv2 = $detail->etdvl;
            $eta2  = $detail->etdal;
            $eih2  = $detail->eihl;
        } else {
            $etdv2 = $lastShipment->ETDV ?? null;
            $eta2  = $lastShipment->ETDA ?? null;
            $eih2  = $lastShipment->EIH ?? null;
        }

        $tpo = DB::connection('mysql_sig')
            ->table('importdt')
            ->where('popk', $po)
            ->distinct()
            ->count('importpk');

        if ($tpo == 0) {
            $materials = collect();
            $shipmentMap = [];
            $vassalDetail = null;
            $shipmentDetails = collect();
            $shipmentPoDetails = collect();

            return view('menu.exim-import.shipdet_detpopur', compact(
                'detail',
                'tpo',
                'materials',
                'shipmentMap',
                'vassalDetail',
                'shipmentDetails',
                'shipmentPoDetails',
                'etdv2',
                'eta2',
                'eih2'));
        }


        $materials = DB::connection('mysql_sig')
        ->table('podt')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('rkb', 'rkb.rkbpk', '=', 'podt.rkbpk')
        ->select(
            'podt.podtpk',
            'podt.popk',
            'podt.spec',
            'podt.colname',
            'podt.unit',
            'podt.pocons'
        )
        ->where('podt.popk', $po)
        ->where('podt.stspk', '>', 1)
        ->orderBy('podt.podtpk')
        ->get();

        $shipments = DB::connection('mysql_sig')
        ->table('importdt')
        ->select(
            'importpk',
            'podtpk',
            'qtys'
        )
        ->where('popk', $po)
        ->orderBy('importpk')
        ->get();

        $shipments = DB::connection('mysql_sig')
        ->table('importdt')
        ->select('importpk', 'podtpk', 'qtys')
        ->where('popk', $po)
        ->orderBy('importpk')
        ->get();

        $shipmentMap = [];

        foreach ($shipments as $shipment) {
            $shipmentMap[$shipment->podtpk][] = $shipment->qtys;
        }

        $vassalDetail = DB::connection('mysql_sig')
        ->table('importdt')
        ->leftJoin('import', 'import.importpk', '=', 'importdt.importpk')
        ->select(
            'importdt.popk',
            'import.importpk',
            'import.AWB',
            'import.ETDV',
            'import.ETDA',
            'import.EIH',
            'import.vassalpk',
            'import.arrivalpk',
            'import.portpk'
        )
        ->where('importdt.popk', $po)
        ->first();

        // $shipmentDetails = DB::connection('mysql_sig')
        // ->table('importdt')
        // ->leftJoin('import', 'import.importpk', '=', 'importdt.importpk')
        // ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
        // ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
        // ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
        // ->select(
        //     'import.importpk',
        //     'import.stsimpk',
        //     'import.AWB',
        //     'import.vassalnm',
        //     'import.datecreate',
        //     'import.ETDV',
        //     'import.ETDA',
        //     'import.EIH',
        //     'import.vassalpk',
        //     'import.arrivalpk',
        //     'import.portpk',
        //     'vassal.typenm as vassal_type',
        //     'arrival.city as arrival_city',
        //     'port.portnm as port_name'
        // )
        // ->where('importdt.popk', $po)
        // ->groupBy(
        //     'import.importpk',
        //     'import.stsimpk',
        //     'import.AWB',
        //     'import.vassalnm',
        //     'import.datecreate',
        //     'import.ETDV',
        //     'import.ETDA',
        //     'import.EIH',
        //     'import.vassalpk',
        //     'import.arrivalpk',
        //     'import.portpk',
        //     'vassal.typenm',
        //     'arrival.city',
        //     'port.portnm'
        // )
        // ->orderBy('import.importpk', 'asc')
        // ->get();

        
        $shipmentDetails = DB::connection('mysql_sig')
        ->table('importdt')
        ->leftJoin('import', 'import.importpk', '=', 'importdt.importpk')
        ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
        ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
        ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
        ->select(
            'import.importpk',
            'import.stsimpk',
            'import.AWB',
            'import.vassalnm',
            'import.datecreate',
            'import.ETDV',
            'import.ETDA',
            'import.EIH',
            'import.ATD',
            'import.ATA',
            'import.vassalpk',
            'import.arrivalpk',
            'import.portpk',
            'vassal.typenm as vassal_type',
            'arrival.city as arrival_city',
            'port.portnm as port_name'
        )
        ->where('importdt.popk', $po)
        ->groupBy(
            'import.importpk',
            'import.stsimpk',
            'import.AWB',
            'import.vassalnm',
            'import.datecreate',
            'import.ETDV',
            'import.ETDA',
            'import.EIH',
            'import.ATD',
            'import.ATA',
            'import.vassalpk',
            'import.arrivalpk',
            'import.portpk',
            'vassal.typenm',
            'arrival.city',
            'port.portnm'
        )
        ->orderBy('import.importpk', 'asc')
        ->get();

        // ==== Hitung AIH (Actual In House) per shipment dari WMS (mysql_mif) ====
        $importpksForAih = $shipmentDetails->pluck('importpk')->toArray();

        $aihMap = [];

        if (!empty($importpksForAih)) {
            $importdtForAih = DB::connection('mysql_sig')->table('importdt')
                ->whereIn('importpk', $importpksForAih)
                ->whereNotNull('podtpk')
                ->where('podtpk', '!=', 0)
                ->select('importpk', 'podtpk')
                ->get();

            if ($importdtForAih->isNotEmpty()) {
                $podtpksForAih = $importdtForAih->pluck('podtpk')->unique()->values()->all();

                $arrivedRows = DB::connection('mysql_mif')->table('dtgdt')
                    ->leftJoin('dtg', 'dtg.dtgpk', '=', 'dtgdt.dtgpk')
                    ->whereIn('dtgdt.podtpk', $podtpksForAih)
                    ->where('dtgdt.qtydtg', '>', 0)
                    ->select('dtgdt.podtpk', 'dtg.tgldtg')
                    ->get();

                $arrivedLookup = $arrivedRows
                    ->filter(fn($r) => !empty($r->tgldtg) && $r->tgldtg != '0000-00-00')
                    ->groupBy('podtpk')
                    ->map(fn($rows) => $rows->min('tgldtg'));

                foreach ($importdtForAih as $row) {
                    if ($arrivedLookup->has($row->podtpk)) {
                        $tgl = $arrivedLookup[$row->podtpk];
                        if (!isset($aihMap[$row->importpk]) || $tgl < $aihMap[$row->importpk]) {
                            $aihMap[$row->importpk] = $tgl;
                        }
                    }
                }
            }
        }

        // Tempelkan aih_date ke setiap baris $shipmentDetails
        $shipmentDetails = $shipmentDetails->map(function ($ship) use ($aihMap) {
            $ship->aih_date = $aihMap[$ship->importpk] ?? null;
            return $ship;
        });

        $shipmentPoDetails = DB::connection('mysql_sig')
        ->table('importdt')
        ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
        ->select(
            'importdt.importpk',
            'importdt.popk',
            'po.pono'
        )
        ->whereIn('importdt.importpk', $shipmentDetails->pluck('importpk')->toArray())
        ->groupBy('importdt.importpk', 'importdt.popk', 'po.pono')
        ->orderBy('importdt.importpk')
        ->get()
        ->groupBy('importpk')
        ->map(function ($rows) {
            return $rows->map(function ($row) {
                return [
                    'popk' => $row->popk,
                    'pono' => $row->pono,
                ];
            })->values();
        });
        

        return view('menu.exim-import.shipdet_detpopur', compact('detail', 'tpo', 'materials' , 
                                                                'shipments', 'shipmentMap', 'vassalDetail',
                                                                'shipmentDetails', 'shipmentPoDetails', 'etdv2', 
                                                                'eta2', 'eih2'));
}

public function getDetailPo($importpk)
{
    $rows = DB::connection('mysql_sig')->table('importdt')
        ->leftJoin('podt', 'podt.podtpk', '=', 'importdt.podtpk')
        ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        // TODO: sesuaikan join ini kalau kolom "Item" ternyata dari tabel rkb,
        // misal: ->leftJoin('rkb', 'rkb.rkbpk', '=', 'podt.rkbpk')
        ->select(
            'po.pono',
            'podt.spec as item',        // TODO: ganti ke kolom Item asli jika beda dari spec
            'podt.spec',
            'sup.supnm',
            'podt.colname',
            'podt.price',
            'podt.pocons as qtypo',
            'podt.unit',
            'importdt.qtys as shipment_qty'
        )
        ->where('importdt.importpk', $importpk)
        ->orderBy('po.pono')
        ->get();

    return response()->json([
        'success'    => true,
        'count_pono' => $rows->pluck('pono')->unique()->count(),
        'data'       => $rows,
    ]);
}

public function getOverPrice($po)
{
            $items = DB::connection('mysql_sig')
                ->table('podt')
                ->leftJoin('po', 'po.popk', '=', 'podt.popk')
                ->leftJoin('rkb', 'podt.rkbpk', '=', 'rkb.rkbpk')
                ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
                ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
                ->leftJoin('cur', 'cur.curpk', '=', 'podt.curpk')
                ->select(
                    'podt.unit',
                    'podt.pocons',
                    'podt.qrs',
                    'podt.price',
                    'podt.curpk',

                    'rkb.pocons as rpocons',
                    'rkb.qrs as rqrs',
                    'rkb.price as rprice',
                    'rkb.curpk as rcurpk'
                )
                ->where('podt.popk', $po)
                ->where('podt.stspk', '>=', 1)
                ->where('podt.delsts', '<>', 1)
                ->groupBy('podt.rkbpk')
                ->get();

            $kurs = DB::connection('mysql_sig')
                ->table('kurs')
                ->orderByDesc('kurspk')
                ->first();

            $totcek = 0;

            foreach ($items as $item) {

            if ($item->price > 0) {

                    if ($item->rcurpk == 2) {

                        if ($item->curpk == 1) {

                            $pprice = $item->price;
                            $rprice = $item->rprice * $kurs->rupiah;

                            $pprice2 = number_format($pprice, 2, ".", "");
                            $rprice2 = number_format($rprice, 2, ".", "");

                        } else {

                            $pprice = $item->price;
                            $rprice = $item->rprice;

                            $pprice2 = number_format($pprice, 5, ".", "");
                            $rprice2 = number_format($rprice, 5, ".", "");

                        }

                    } else {

                        $pprice = $item->price;
                        $rprice = $item->rprice;

                        $pprice2 = number_format($pprice, 2, ".", "");
                        $rprice2 = number_format($rprice, 2, ".", "");

                    }

                    // ===== SAMA PERSIS DENGAN NATIVE =====

                    if ($pprice2 > $rprice2) {
                        $cek = 1;
                    } else {
                        $cek = 0;
                    }

                    $totcek += $cek;
                }

            }

            return response()->json([
                'total_overprice' => $totcek
            ]);
}

public function getOverQty($po)
{
        $rows = DB::connection('mysql_sig')
            ->table('podt')
            ->leftJoin('rkb', 'podt.rkbpk', '=', 'rkb.rkbpk')
            ->select(
                'podt.unit',
                'podt.pocons',
                'podt.qrs',
                'rkb.pocons as rpocons',
                'rkb.qrs as rqrs'
            )
            ->where('podt.popk', $po)
            ->where('podt.stspk', '>=', 1)
            ->where('podt.delsts', '<>', 1)
            ->groupBy('podt.rkbpk')
            ->get();

        $totalOverQty = 0;

        foreach ($rows as $row) {

            if (in_array(trim($row->unit), ['KG', 'DZN', 'RIM', 'GROSS'])) {

                $poCons = $row->pocons - $row->qrs;
                $rkbCons = $row->rpocons - $row->rqrs;

            } else {

                $poCons = ceil($row->pocons - $row->qrs);
                $rkbCons = ceil($row->rpocons - $row->rqrs);

            }

            if ($poCons > $rkbCons) {
                $totalOverQty++;
            }
        }

        return response()->json([
            'total_overqty' => $totalOverQty
        ]);
}

public function ship(Request $request)
{
    ['ships' => $ships, 'chek' => $chek] = $this->buildShipData($request);
    $filterViewData = $this->buildShipFilterViewData($request);

    return view('menu.exim-import.ship', array_merge(
        compact('ships', 'chek'),
        $filterViewData
    ));
}

public function shipData(Request $request)
{
    ['ships' => $ships, 'chek' => $chek] = $this->buildShipData($request);
    $filterViewData = $this->buildShipFilterViewData($request);

    $filterHtml = view('menu.exim-import.prt.filter-ship', $filterViewData)->render();
    $sortHtml = view('menu.exim-import.prt.sort-ship')->render();
    $tableHtml = view('menu.exim-import.prt.table-ship', array_merge(
        compact('ships', 'chek'),
        $filterViewData
    ))->render();

    return response()->json([
        'filterHtml' => $filterHtml,
        'sortHtml' => $sortHtml,
        'tableHtml' => $tableHtml,
    ]);
}

// private function buildShipData(Request $request)
private function buildShipData(Request $request, bool $paginate = true)
{
    $candidateImportpks = DB::connection('mysql_sig')
            ->table('import')
            ->where('stsimpk', '!=', 3)
            ->pluck('importpk')
            ->all();

    $this->syncInHouseStatus($candidateImportpks);

    $cari1 = $request->query('cari1');
    $cari2 = $request->query('cari2');          // status (array)
    $cari3 = $request->query('cari3');          // month po.tglpo
    $dateRangeCode = $request->query('cari4', ''); // Shipment ETD range
    $chek = $request->query('chek', '0');       // 0=date, 1=supplier

    /*
    |--------------------------------------------------------------------------
    | Sorting
    |--------------------------------------------------------------------------
    */
    // if ($request->query('cari6')) {
    //     $orderCol = 'import.dateim';
    //     $orderDir = 'asc';
    // } elseif ($request->query('cari7')) {
    //     $orderCol = 'import.dateupdtim';
    //     $orderDir = 'desc';
    // } elseif ($request->query('cari8')) {
    //     $orderCol = 'import.ETDV';
    //     $orderDir = 'desc';
    // } else {
    //     $orderCol = 'import.dateim';
    //     $orderDir = 'desc';
    // }

    // if ($request->query('cari6')) {
    // $orderCol = 'import.dateim';
    // $orderDir = 'asc';
    // } elseif ($request->query('cari7')) {
    //     $orderCol = 'import.dateupdtim';
    //     $orderDir = 'desc';
    // } elseif ($request->query('cari8')) {
    //     $orderCol = 'import.ETDV';
    //     $orderDir = 'asc';       // ETD (Asc)
    // } elseif ($request->query('cari9')) {
    //     $orderCol = 'import.ETDV';
    //     $orderDir = 'desc';      // ETD (Dsc)
    // } else {
    //     $orderCol = 'import.dateim';
    //     $orderDir = 'desc';
    // }
    if ($request->query('cari6')) {
        $orderCol = 'import.datecreate';   // ⬅️ diganti dari dateim
        $orderDir = 'asc';                 // Oldest entry
    } elseif ($request->query('cari7')) {
        $orderCol = 'import.dateupdtim';
        $orderDir = 'desc';                // Recently modify
    } elseif ($request->query('cari8')) {
        $orderCol = 'import.ETDV';
        $orderDir = 'asc';
    } elseif ($request->query('cari9')) {
        $orderCol = 'import.ETDV';
        $orderDir = 'desc';
    } else {
        $orderCol = 'import.datecreate';   // ⬅️ diganti dari dateim (Newest entry / default)
        $orderDir = 'desc';
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 1: Filter dulu import yang relevan (importpk saja)
    |--------------------------------------------------------------------------
    | Tujuan: batasi scope SEBELUM masuk ke subquery agregat berat.
    | Filter tahun ETD, bulan ETD, dan status dikerjakan di sini karena
    | semuanya kolom milik tabel `import` sendiri (tidak butuh join lain).
    |--------------------------------------------------------------------------
    */
    $importIdsQuery = DB::connection('mysql_sig')
        ->table('import');

    if ($dateRangeCode === '') {
        // No Shipment ETD range explicitly chosen — fall back to the import's
        // creation year (this year onward) instead of filtering by Shipment ETD,
        // so shipments that don't have a Shipment ETD filled in yet still show up.
        $importIdsQuery->where('import.dateim', '>=', Carbon::now()->startOfYear()->toDateString());
    } elseif ($dateRangeCode !== 'all') {
        $today = Carbon::now();

        switch ($dateRangeCode) {
            case 'today':
                $importIdsQuery->whereDate('import.ETDV', $today->toDateString());
                break;
            case '1w':
                $importIdsQuery->whereBetween('import.ETDV', [$today->toDateString(), $today->copy()->addWeek()->toDateString()]);
                break;
            case '2w':
                $importIdsQuery->whereBetween('import.ETDV', [$today->toDateString(), $today->copy()->addWeeks(2)->toDateString()]);
                break;
            case '1m':
                $importIdsQuery->whereBetween('import.ETDV', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()]);
                break;
            case '1mn':
                $importIdsQuery->whereBetween('import.ETDV', [
                    $today->copy()->addMonthNoOverflow()->startOfMonth()->toDateString(),
                    $today->copy()->addMonthNoOverflow()->endOfMonth()->toDateString(),
                ]);
                break;
            case '3m':
                $importIdsQuery->whereBetween('import.ETDV', [$today->toDateString(), $today->copy()->addMonths(3)->toDateString()]);
                break;
            case '6m':
                $importIdsQuery->whereBetween('import.ETDV', [$today->toDateString(), $today->copy()->addMonths(6)->toDateString()]);
                break;
            case 'year':
            default:
                // Explicit "Tahun ini" pick — this year onward, based on Shipment ETD.
                $importIdsQuery->where('import.ETDV', '>=', $today->copy()->startOfYear()->toDateString());
                break;
        }
    } else {
        // 'all' → no date bound, but only shipments that actually have a Shipment ETD filled in.
        $importIdsQuery->whereNotNull('import.ETDV')
            ->where('import.ETDV', '<>', '')
            ->where('import.ETDV', '<>', '0000-00-00');
    }

    $importIdsQuery->when(!empty($cari2), function ($q) use ($cari2) {
        $q->whereIn('import.stsimpk', (array) $cari2);
    });

    /*
    |--------------------------------------------------------------------------
    | Filter month PO (cari3) & text search (cari1) butuh join ke tabel lain,
    | jadi tetap pakai whereExists supaya tidak menggandakan baris `import`
    | pada tahap filter awal ini.
    |--------------------------------------------------------------------------
    */
    $importIdsQuery->when($cari3, function ($q) use ($cari3) {
        $q->whereExists(function ($sub) use ($cari3) {
            $sub->select(DB::raw(1))
                ->from('importdt')
                ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
                ->whereColumn('importdt.importpk', 'import.importpk')
                ->whereMonth('po.tglpo', $cari3);
        });
    });

    // $importIdsQuery->when($cari1, function ($q) use ($cari1) {
    //     $q->where(function ($q2) use ($cari1) {
    //         $q2->where('import.vassalnm', 'like', "%{$cari1}%")
    //             ->orWhere('import.AWB', 'like', "%{$cari1}%")
    //             ->orWhereExists(function ($sub) use ($cari1) {
    //                 $sub->select(DB::raw(1))
    //                     ->from('importdt')
    //                     ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
    //                     ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
    //                     ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
    //                     ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
    //                     ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
    //                     ->whereColumn('importdt.importpk', 'import.importpk')
    //                     ->where(function ($q3) use ($cari1) {
    //                         $q3->where('sup.supnm', 'like', "%{$cari1}%")
    //                             ->orWhere('po.pono', 'like', "%{$cari1}%")
    //                             ->orWhere('po.potop', 'like', "%{$cari1}%")
    //                             ->orWhere('byr.byrnm', 'like', "%{$cari1}%");
    //                     });
    //             });
    //     });
    // });

    // // vassal.typenm butuh join, cek terpisah supaya query cari1 tetap ringkas
    // if ($cari1) {
    //     $importIdsQuery->orWhereExists(function ($sub) use ($cari1) {
    //         $sub->select(DB::raw(1))
    //             ->from('vassal')
    //             ->whereColumn('vassal.vassalpk', 'import.vassalpk')
    //             ->where('vassal.typenm', 'like', "%{$cari1}%");
    //     });
    // }

    $importIdsQuery->when($cari1, function ($q) use ($cari1) {
        $q->where(function ($q2) use ($cari1) {
            $q2->where('import.vassalnm', 'like', "%{$cari1}%")
                ->orWhere('import.AWB', 'like', "%{$cari1}%")
                ->orWhereExists(function ($sub) use ($cari1) {
                    $sub->select(DB::raw(1))
                        ->from('vassal')
                        ->whereColumn('vassal.vassalpk', 'import.vassalpk')
                        ->where('vassal.typenm', 'like', "%{$cari1}%");
                })
                ->orWhereExists(function ($sub) use ($cari1) {
                    $sub->select(DB::raw(1))
                        ->from('importdt')
                        ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
                        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
                        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
                        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
                        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
                        ->whereColumn('importdt.importpk', 'import.importpk')
                        ->where(function ($q3) use ($cari1) {
                            $q3->where('sup.supnm', 'like', "%{$cari1}%")
                                ->orWhere('po.pono', 'like', "%{$cari1}%")
                                ->orWhere('po.potop', 'like', "%{$cari1}%")
                                ->orWhere('byr.byrnm', 'like', "%{$cari1}%");
                        });
                });
        });
    });

    $importIds = $importIdsQuery->pluck('import.importpk');

    // Kalau tidak ada hasil, langsung return kosong (hindari query lanjutan sia-sia)
    // if ($importIds->isEmpty()) {
    //     $ships = collect();
    //     return compact('ships', 'chek');
    // }

    if ($importIds->isEmpty()) {
    $ships = new \Illuminate\Pagination\LengthAwarePaginator(
        collect(),
        0,
        (int) $request->input('rows', 50),
        (int) $request->input('page', 1),
        ['path' => $request->url(), 'query' => $request->query()]
    );
    return compact('ships', 'chek');
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2: Subquery aggregate detail shipment, DIBATASI hanya importpk
    | yang sudah lolos filter di STEP 1
    |--------------------------------------------------------------------------
    */
    // $detailAgg = DB::connection('mysql_sig')
    //     ->table('importdt')
    //     ->whereIn('importdt.importpk', $importIds)
    //     ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
    //     ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
    //     ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
    //     ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
    //     ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
    //     ->selectRaw('
    //         importdt.importpk,
    //         COUNT(DISTINCT importdt.popk) as total_po,
    //         SUM(COALESCE(importdt.qtys,0)) as qtys,
    //         MIN(po.pono) as pono,
    //         MIN(po.potop) as potop,
    //         MIN(sup.supnm) as supnm,
    //         MIN(byr.byrnm) as byrnm,
    //         MIN(podt.unit) as unit
    //     ')
    //     ->groupBy('importdt.importpk');

    // Subquery 1: qty, total_po, pono, potop, supnm — TANPA join podt (supaya SUM tidak duplikat)
    $detailAgg1 = DB::connection('mysql_sig')
        ->table('importdt')
        ->whereIn('importdt.importpk', $importIds)
        ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->selectRaw('
            importdt.importpk,
            COUNT(DISTINCT importdt.popk) as total_po,
            SUM(COALESCE(importdt.qtys,0)) as qtys,
            GROUP_CONCAT(DISTINCT po.pono ORDER BY po.pono SEPARATOR ", ") as pono,
            MIN(po.potop) as potop,
            MIN(po.etd) as etd,
            MIN(sup.supnm) as supnm
        ')
        ->groupBy('importdt.importpk');

    // Subquery 2: unit & byrnm — boleh join podt, karena cuma pakai MIN() (aman dari duplikasi)
    $detailAgg2 = DB::connection('mysql_sig')
        ->table('importdt')
        ->whereIn('importdt.importpk', $importIds)
        ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->selectRaw('
            importdt.importpk,
            MIN(podt.unit) as unit,
            MIN(byr.byrnm) as byrnm
        ')
        ->groupBy('importdt.importpk');

    /*
    |--------------------------------------------------------------------------
    | STEP 3: Query utama, JUGA dibatasi whereIn importpk supaya planner
    | tidak perlu scan ulang whereNotNull/whereYear dkk (sudah pasti subset)
    |--------------------------------------------------------------------------
    */
    // $query = DB::connection('mysql_sig')
    //     ->table('import')
    //     ->whereIn('import.importpk', $importIds)
    //     ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
    //     ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
    //     ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
    //     ->leftJoinSub($detailAgg, 'detail', function ($join) {
    //         $join->on('detail.importpk', '=', 'import.importpk');
    //     })
    //     ->select(
    //         'import.importpk',
    //         'import.AWB',
    //         'import.vassalnm',
    //         'import.ETDV',
    //         'import.ETDA',
    //         'import.EIH',
    //         'import.GW',
    //         'import.DL',
    //         'import.DP',
    //         'import.DT',
    //         'import.totcoli',
    //         'import.dateim',
    //         'import.dateupdtim',
    //         'import.stsimpk',

    //         'vassal.typenm',
    //         'arrival.city',
    //         'port.portnm',

    //         'detail.pono',
    //         'detail.potop',
    //         'detail.supnm',
    //         'detail.byrnm',
    //         'detail.unit',
    //         'detail.total_po',
    //         'detail.qtys'
    //     )
    //     ->orderBy($orderCol, $orderDir);

    $query = DB::connection('mysql_sig')
    ->table('import')
    ->whereIn('import.importpk', $importIds)
    ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
    ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
    ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
    ->leftJoinSub($detailAgg1, 'detail1', function ($join) {
        $join->on('detail1.importpk', '=', 'import.importpk');
    })
    ->leftJoinSub($detailAgg2, 'detail2', function ($join) {
        $join->on('detail2.importpk', '=', 'import.importpk');
    })
    ->select(
        'import.importpk',
        'import.AWB',
        'import.vassalnm',
        'import.ETDV',
        'import.ETDA',
        'import.EIH',
        'import.ATD',
        'import.ATA',
        'import.GW',
        'import.DL',
        'import.DP',
        'import.DT',
        'import.totcoli',
        'import.datecreate', 
        'import.dateim',
        'import.dateupdtim',
        'import.stsimpk',

        'vassal.typenm',
        'arrival.city',
        'port.portnm',

        'detail1.pono',
        'detail1.potop',
        'detail1.etd', 
        'detail1.supnm',
        'detail1.total_po',
        'detail1.qtys',
        'detail2.unit',
        'detail2.byrnm'
    )
    ->orderBy($orderCol, $orderDir);

    // $ships = $query->get()->map(function ($row) use ($chek, $request) {
    //     // label PO untuk list
    //     $row->pono_display = ($row->total_po ?? 0) > 1
    //         ? $row->total_po . ' POs'
    //         : ($row->pono ?: '-');

    //     // tentukan kolom tanggal grouping sesuai sort aktif (samakan dengan ORDER BY)
    //     if ($request->query('cari8') || $request->query('cari9')) {
    //         $tglSource = $row->ETDV;       // ETD (Asc) & ETD (Dsc)
    //     } elseif ($request->query('cari7')) {
    //         $tglSource = $row->dateupdtim; // Recently modify
    //     } else {
    //         $tglSource = $row->dateim;     // Newest / Oldest / default
    //     }

    //     // normalisasi ke tanggal saja (Y-m-d), buang komponen jam
    //     // supaya baris dengan tanggal sama tidak kepisah grup gara-gara beda jam
    //     if (!empty($tglSource) && !str_starts_with($tglSource, '0000-00-00')) {
    //         $tglSource = substr($tglSource, 0, 10); // ambil 'YYYY-MM-DD' saja
    //     }

    //     // grouping helper
    //     $row->group_key = $chek == '1'
    //         ? ($row->supnm ?: '-')
    //         : ($tglSource ?: null);

    //     // status label helper
    //     switch ((int) $row->stsimpk) {
    //         case 1:
    //             $row->status_text = 'En Route';
    //             $row->status_color = '#333333';
    //             break;
    //         case 2:
    //             $row->status_text = 'Arrived IDN';
    //             $row->status_color = '#359DD9';
    //             break;
    //         case 3:
    //             $row->status_text = 'In house';
    //             $row->status_color = '#19B715';
    //             break;
    //         default:
    //             $row->status_text = 'Schedule';
    //             $row->status_color = '#BDBDBD';
    //             break;
    //     }

    //     return $row;
    // });

    // return compact('ships', 'chek');

    // $perPage = (int) $request->input('rows', 50);
    // $ships = $query->paginate($perPage)->withQueryString();

    // // ==== AMBIL TANGGAL IN HOUSE ACTUAL (tgldtg) DARI DB MIF ====
    // $poNumbers = $ships->getCollection()
    //     ->flatMap(function ($row) {
    //         return $row->pono ? array_map('trim', explode(',', $row->pono)) : [];
    //     })
    //     ->filter()
    //     ->unique()
    //     ->values();

    // $ihaMap = [];

    // if ($poNumbers->isNotEmpty()) {
    //     $ihaRows = DB::connection('mysql_mif')
    //         ->table('dtg')
    //         ->leftJoin('dtgdt', 'dtgdt.dtgpk', '=', 'dtg.dtgpk')
    //         ->whereIn('dtgdt.nopo', $poNumbers)
    //         ->selectRaw('dtgdt.nopo, MIN(dtg.tgldtg) as tgldtg')
    //         ->groupBy('dtgdt.nopo')
    //         ->get();

    //     foreach ($ihaRows as $row) {
    //         $ihaMap[$row->nopo] = $row->tgldtg;
    //     }
    // }

    // $ships->getCollection()->transform(function ($row) use ($ihaMap) {
    //     $poList = $row->pono ? array_map('trim', explode(',', $row->pono)) : [];

    //     $ihaDate = null;
    //     foreach ($poList as $po) {
    //         if (isset($ihaMap[$po])) {
    //             if (!$ihaDate || $ihaMap[$po] < $ihaDate) {
    //                 $ihaDate = $ihaMap[$po];
    //             }
    //         }
    //     }

    //     $row->iha_actual = $ihaDate;

    //     return $row;
    // });

    // // ==== LANJUT PROSES SEPERTI SEBELUMNYA ====
    // $ships = $ships->through(function ($row) use ($chek, $request) {
    //     $row->pono_display = $row->pono ?: '-';

    //     if ($request->query('cari8') || $request->query('cari9')) {
    //         $tglSource = $row->ETDV;
    //     } elseif ($request->query('cari7')) {
    //         $tglSource = $row->dateupdtim;
    //     } else {
    //         // $tglSource = $row->dateim;
    //         $tglSource = $row->datecreate;
    //     }

    //     if (!empty($tglSource) && !str_starts_with($tglSource, '0000-00-00')) {
    //         $tglSource = substr($tglSource, 0, 10);
    //     }

    //     $row->group_key = $chek == '1'
    //         ? ($row->supnm ?: '-')
    //         : ($tglSource ?: null);

    //     switch ((int) $row->stsimpk) {
    //         case 1:
    //             $row->status_text = 'En Route';
    //             $row->status_color = '#333333';
    //             break;
    //         case 2:
    //             $row->status_text = 'Arrived IDN';
    //             $row->status_color = '#359DD9';
    //             break;
    //         case 3:
    //             $row->status_text = 'In house';
    //             $row->status_color = '#19B715';
    //             break;
    //         default:
    //             $row->status_text = 'Schedule';
    //             $row->status_color = '#BDBDBD';
    //             break;
    //     }

    //     return $row;
    // });

    // return compact('ships', 'chek');

    if ($paginate) {
    $perPage = (int) $request->input('rows', 50);
    $ships = $query->paginate($perPage)->withQueryString();
    $collection = $ships->getCollection();
    } else {
        // untuk export: ambil semua baris yang match filter, tanpa limit halaman
        $collection = $query->get();
    }

    // ==== AMBIL TANGGAL IN HOUSE ACTUAL (tgldtg) DARI DB MIF ====
    $poNumbers = $collection
        ->flatMap(function ($row) {
            return $row->pono ? array_map('trim', explode(',', $row->pono)) : [];
        })
        ->filter()
        ->unique()
        ->values();

    $ihaMap = [];

    if ($poNumbers->isNotEmpty()) {
        $ihaRows = DB::connection('mysql_mif')
            ->table('dtg')
            ->leftJoin('dtgdt', 'dtgdt.dtgpk', '=', 'dtg.dtgpk')
            ->whereIn('dtgdt.nopo', $poNumbers)
            ->selectRaw('dtgdt.nopo, MIN(dtg.tgldtg) as tgldtg')
            ->groupBy('dtgdt.nopo')
            ->get();

        foreach ($ihaRows as $row) {
            $ihaMap[$row->nopo] = $row->tgldtg;
        }
    }

    $collection = $collection->map(function ($row) use ($ihaMap) {
        $poList = $row->pono ? array_map('trim', explode(',', $row->pono)) : [];

        $ihaDate = null;
        foreach ($poList as $po) {
            if (isset($ihaMap[$po])) {
                if (!$ihaDate || $ihaMap[$po] < $ihaDate) {
                    $ihaDate = $ihaMap[$po];
                }
            }
        }

        $row->iha_actual = $ihaDate;

        return $row;
    });

    // ==== LANJUT PROSES SEPERTI SEBELUMNYA ====
    $collection = $collection->map(function ($row) use ($chek, $request) {
        $row->pono_display = $row->pono ?: '-';

        if ($request->query('cari8') || $request->query('cari9')) {
            $tglSource = $row->ETDV;
        } elseif ($request->query('cari7')) {
            $tglSource = $row->dateupdtim;
        } else {
            $tglSource = $row->datecreate;
        }

        if (!empty($tglSource) && !str_starts_with($tglSource, '0000-00-00')) {
            $tglSource = substr($tglSource, 0, 10);
        }

        $row->group_key = $chek == '1'
            ? ($row->supnm ?: '-')
            : ($tglSource ?: null);

        switch ((int) $row->stsimpk) {
            case 1:
                $row->status_text = 'En Route';
                $row->status_color = '#333333';
                break;
            case 2:
                $row->status_text = 'Arrived IDN';
                $row->status_color = '#359DD9';
                break;
            case 3:
                $row->status_text = 'In house';
                $row->status_color = '#19B715';
                break;
            default:
                $row->status_text = 'Schedule';
                $row->status_color = '#BDBDBD';
                break;
        }

        return $row;
    });

    if ($paginate) {
        $ships->setCollection($collection);
    } else {
        $ships = $collection;
    }

    return compact('ships', 'chek');
}

private function buildShipFilterViewData(Request $request)
{
    $statusOptions = [
        '0' => 'On Hold',
        '1' => 'En Route',
        '2' => 'Arrived at Port',
        '3' => 'In house',
    ];
    $currentStatuses = array_map('strval', (array) $request->input('cari2', []));

    if (count($currentStatuses) === 0) {
        $statusDropdownLabel = 'Status Shipment';
    } elseif (count($currentStatuses) === 1) {
        $statusDropdownLabel = $statusOptions[$currentStatuses[0]] ?? 'Status Shipment';
    } else {
        $statusDropdownLabel = 'Status (' . count($currentStatuses) . ')';
    }

    $dateRangeOptions = [
        'all' => 'All Time',
        'today' => 'Hari ini',
        '1w' => 'Minggu depan',
        '2w' => '2 Minggu depan',
        '1m' => 'Bulan ini',
        '1mn' => 'Bulan depan',
        '3m' => '3 Bulan depan',
        '6m' => '6 Bulan depan',
        'year' => 'Tahun ini',
    ];
    // Keep the raw (possibly empty) value here — the "this year onward" default
    // is only a server-side fallback in buildShipData() when cari4 is absent.
    // Pre-filling this with 'year' would make the hidden form field silently
    // bake cari4=year into the URL the moment any other filter/sort is submitted.
    $currentDateRange = $request->input('cari4', '');
    $currentDateRangeLabel = $currentDateRange !== ''
        ? ($dateRangeOptions[$currentDateRange] ?? 'Shipment ETD')
        : 'Shipment ETD';

    $activeFilters = [];

    if ($request->filled('cari1')) {
        $activeFilters[] = [
            'label' => 'Search: "' . $request->input('cari1') . '"',
            'query' => $request->except('cari1', 'page'),
        ];
    }

    foreach ($currentStatuses as $statusValue) {
        $activeFilters[] = [
            'label' => $statusOptions[$statusValue] ?? $statusValue,
            'query' => array_merge($request->except('cari2', 'page'), [
                'cari2' => array_values(array_diff($currentStatuses, [$statusValue])),
            ]),
        ];
    }

    if ($request->filled('cari4')) {
        $activeFilters[] = [
            'label' => $currentDateRangeLabel,
            'query' => $request->except('cari4', 'page'),
        ];
    }

    return compact(
        'statusOptions', 'currentStatuses', 'statusDropdownLabel',
        'dateRangeOptions', 'currentDateRange', 'currentDateRangeLabel',
        'activeFilters'
    );
}

public function changeShipStatus(Request $request, $importpk)
{
    $status = $request->status;

    if (!in_array((int)$status, [1, 2, 3])) {
        return response()->json([
            'success' => false,
            'message' => 'Status tidak valid'
        ], 422);
    }

    DB::connection('mysql_sig')
        ->table('import')
        ->where('importpk', $importpk)
        ->update([
            'stsimpk' => $status,
            'dateupdtim' => now(),
        ]);

    return response()->json([
        'success' => true,
        'message' => 'Status shipment berhasil diupdate'
    ]);
}

public function cancelShip($importpk)
{
    $conn = DB::connection('mysql_sig');

    try {
        $conn->transaction(function () use ($conn, $importpk) {

            // Ambil semua popk yang terkait shipment ini, lalu kembalikan status po ke 0 (belum ada shipment plan)
            $popks = $conn->table('importdt')
                ->where('importpk', $importpk)
                ->distinct()
                ->pluck('popk');

            foreach ($popks as $popk) {
                $conn->table('po')->where('popk', $popk)->update([
                    'stsimp' => 0,
                ]);
            }

            // Hapus data shipment plan sepenuhnya, sesuai native
            $conn->table('importdt')->where('importpk', $importpk)->where('stsdt', '0')->delete();
            $conn->table('import')->where('importpk', $importpk)->delete();
            $conn->table('logim1')->where('importpk', $importpk)->delete();
            $conn->table('logim2')->where('importpk', $importpk)->delete();
            $conn->table('logim3')->where('importpk', $importpk)->delete();
            $conn->table('logim4')->where('importpk', $importpk)->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Shipment plan cancelled'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to cancel shipment: ' . $e->getMessage()
        ], 500);
    }
}

public function viewShip($importpk)
{
    // Sync status In House untuk shipment ini sebelum ditampilkan
    $this->syncInHouseStatus([$importpk]);

    $conn = DB::connection('mysql_sig');

    $shipment = $conn->table('import')
        ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
        ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
        ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
        ->where('import.importpk', $importpk)
        ->select(
            'import.importpk',
            'import.AWB',
            'import.vassalpk',
            'import.vassalnm',
            'import.arrivalpk',
            'import.portpk',
            'import.ETDV',
            'import.ETDA',
            'import.EIH',
            'import.ATD',
            'import.ATA',
            'import.GW',
            'import.DL',
            'import.DP',
            'import.DT',
            'import.totcoli',
            'vassal.typenm as vassal_type',
            'arrival.city as arrival_city',
            'port.portnm as port_name'
        )
        ->first();

    if (!$shipment) {
        return response()->json([
            'success' => false,
            'message' => 'Data shipment tidak ditemukan'
        ], 404);
    }

    $supplier = $conn->table('importdt')
        ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->where('importdt.importpk', $importpk)
        ->groupBy('sup.supnm')
        ->value('sup.supnm');

    $poCount = $conn->table('importdt')
        ->where('importpk', $importpk)
        ->distinct()
        ->count('popk');

    /*
    |--------------------------------------------------------------------------
    | DETAIL PO / ITEM — tambahkan importdtpk & popk untuk keperluan
    | edit qty, delete material, dan tombol +Materials per grup PO
    |--------------------------------------------------------------------------
    */
    // $poDetails = $conn->table('importdt')
    //     ->leftJoin('podt', 'podt.podtpk', '=', 'importdt.podtpk')
    //     ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
    //     ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
    //     ->leftJoin('rkb', 'rkb.rkbpk', '=', 'podt.rkbpk')
    //     ->where('importdt.importpk', $importpk)
    //     ->select(
    //         'importdt.importdtpk',
    //         'po.popk',
    //         'po.pono',
    //         'rkb.item',
    //         'podt.spec',
    //         'sup.supnm as supplier',
    //         'podt.colname',
    //         'podt.price',
    //         'podt.pocons as total',
    //         'importdt.qtys'
    //     )
    //     ->orderBy('po.pono')
    //     ->get();

    $poDetails = $conn->table('importdt')
    ->leftJoin('podt', 'podt.podtpk', '=', 'importdt.podtpk')
    ->leftJoin('po', 'po.popk', '=', 'importdt.popk')
    ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
    ->leftJoin('rkb', 'rkb.rkbpk', '=', 'podt.rkbpk')
    ->leftJoin('cur', 'cur.curpk', '=', 'po.curpk')
    ->where('importdt.importpk', $importpk)
    ->select(
        'importdt.importdtpk',
        'po.popk',
        'po.pono',
        'rkb.item',
        'podt.spec',
        'sup.supnm as supplier',
        'podt.colname',
        'podt.price',
        'podt.pocons as total',
        'podt.unit',
        'importdt.qtys',
        'cur.curid',
        'cur.cursign'
    )
    ->orderBy('po.pono')
    ->get();

    // ==== AMBIL TANGGAL IN HOUSE ACTUAL (AIH) DARI DB MIF ====
    $poNumbersForAih = $poDetails
        ->pluck('pono')
        ->filter()
        ->unique()
        ->values();

    $aihDate = null;

    if ($poNumbersForAih->isNotEmpty()) {
        $aihDate = DB::connection('mysql_mif')
            ->table('dtg')
            ->leftJoin('dtgdt', 'dtgdt.dtgpk', '=', 'dtg.dtgpk')
            ->whereIn('dtgdt.nopo', $poNumbersForAih)
            ->min('dtg.tgldtg');
    }

    // ==== SHIPMENT LOG ====
    $logSources = [
        ['table' => 'logim1', 'date' => 'logdate1', 'text' => 'logtype'],
        ['table' => 'logim1', 'date' => 'logdate1', 'text' => 'logvassal'],
        ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'logd'],
        ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'logamount'],
        ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'loggw'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logetd'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logeta'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logeih'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logatd'],   // <-- BARU
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logata'],   // <-- BARU
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logaih'],   // <-- BARU
        ['table' => 'logim4', 'date' => 'logdate4', 'text' => 'lognm'],
    ];

    $logs = collect();

    foreach ($logSources as $src) {
        $rows = $conn->table($src['table'])
            ->where('importpk', $importpk)
            ->select(
                "{$src['date']} as datelog",
                "{$src['text']} as logchange"
            )
            ->get();

        $logs = $logs->merge($rows);
    }

    $logs = $logs
        ->filter(fn($row) => !empty($row->logchange))
        ->unique(fn($row) => $row->datelog . '|' . $row->logchange)
        ->sortByDesc('datelog')
        ->values()
        ->map(function ($row) {
            return [
                'date'    => $row->datelog,
                'changes' => $row->logchange,
            ];
        });

    $dimensionDisplay = collect([$shipment->DL, $shipment->DP, $shipment->DT])
        ->filter(fn($v) => $v !== null && $v !== '')
        ->implode(' x ');

    $dimensionDisplay = sprintf(
        '(P) %s x (L) %s x (T) %s cm',
        number_format((float) ($shipment->DP ?? 0), 2),
        number_format((float) ($shipment->DL ?? 0), 2),
        number_format((float) ($shipment->DT ?? 0), 2)
    );

    $data = [
        'importpk'      => $shipment->importpk,
        'supplier'      => $supplier ?: '-',
        'po_count'      => $poCount,

        // ==== field TERFORMAT untuk tampilan view mode ====
        'vassal_type'   => $shipment->vassal_type ?: '-',
        'vassal_name'   => $shipment->vassalnm ?: '-',
        'awb'           => $shipment->AWB ?: '-',
        'arrival_city'  => $shipment->arrival_city ?: '-',
        'port_name'     => $shipment->port_name ?: '-',
        // 'gw'            => $shipment->GW ?? '-',
        // 'dimension'     => $dimensionDisplay,
        // 'total_package' => $shipment->totcoli ?? '-',
        'gw'            => $shipment->GW !== null && $shipment->GW !== '' 
                            ? number_format((float) $shipment->GW, 2, '.', ',') 
                            : '-',
        'dimension'     => $dimensionDisplay,
        'total_package' => $shipment->totcoli !== null && $shipment->totcoli !== '' 
                            ? number_format((float) $shipment->totcoli, 2, '.', ',') 
                            : '-',
        // 'etd'           => $shipment->ETDV ? \Carbon\Carbon::parse($shipment->ETDV)->format('d/m/Y') : '',
        // 'eta'           => $shipment->ETDA ? \Carbon\Carbon::parse($shipment->ETDA)->format('d/m/Y') : '',
        // 'eih'           => $shipment->EIH ? \Carbon\Carbon::parse($shipment->EIH)->format('d/m/Y') : '',
        'etd'           => $this->formatShipDate($shipment->ETDV),
        'eta'           => $this->formatShipDate($shipment->ETDA),
        'eih'           => $this->formatShipDate($shipment->EIH),
        'atd'           => $this->formatShipDate($shipment->ATD), // TAMBAHAN
        'ata'           => $this->formatShipDate($shipment->ATA), // TAMBAHAN
        'aih'           => $this->formatShipDate($aihDate), // TAMBAHAN

        // ==== field MENTAH untuk keperluan edit mode (dropdown/input) ====
        'vassalpk'          => $shipment->vassalpk,
        'arrivalpk'         => $shipment->arrivalpk,
        'portpk'            => $shipment->portpk,
        'awb_raw'           => $shipment->AWB,
        'gw_raw'            => $shipment->GW,
        'dp_raw'            => $shipment->DP,
        'dl_raw'            => $shipment->DL,
        'dt_raw'            => $shipment->DT,
        'total_package_raw' => $shipment->totcoli,

        // 'po_details' => $poDetails->map(function ($row) {
        //     return [
        //         'importdtpk'        => $row->importdtpk,
        //         'popk'              => $row->popk,
        //         'pono'              => $row->pono ?? '-',
        //         'item'              => $row->item ?? '-',
        //         'description'       => $row->spec ?? '-',
        //         'supplier'          => $row->supplier ?? '-',
        //         'color'             => $row->colname ?? '-',
        //         'price'             => $row->price !== null ? number_format((float) $row->price, 2) : '-',
        //         'qty_po'            => $row->total !== null ? number_format((float) $row->total, 0, '.', ',') : '-',
        //         'shipment_qty'      => $row->qtys !== null ? number_format((float) $row->qtys, 0, '.', ',') : '-',
        //         'shipment_qty_raw'  => $row->qtys ?? 0,
        //     ];
        // })->values(),

        'po_details' => $poDetails->map(function ($row) {
            $unit = $row->unit ?? '';
            $pocons = (float) ($row->total ?? 0);
            $price = (float) ($row->price ?? 0);

            // Currency sign, sesuai native get_dtimport.php
            $cursign = $row->cursign ?? '';
            if ($row->curid == 'USD') { $cursign = '$'; }
            elseif ($row->curid == 'EUR') { $cursign = '€'; }
            elseif ($row->curid == 'HKD') { $cursign = '$'; }
            elseif ($row->curid == 'GBP') { $cursign = '£'; }

            $decimals = ($row->curid == 'IDR') ? 2 : 5;

            // Qty efektif untuk hitung total price, sesuai native (unit KG/DZN/RIM tidak dibulatkan)
            $unitsNoRound = ['KG', 'DZN', 'RIM'];
            $qtyForPrice = in_array($unit, $unitsNoRound) ? $pocons : ceil($pocons);

            $priceTotal = $row->price !== null
                ? $cursign . ' ' . number_format($price * $qtyForPrice, $decimals, '.', ',')
                : '-';

            $qtyPoDisplay = $row->total !== null
                ? number_format($pocons, 0, '.', ',') . ' ' . $unit
                : '-';

            // Shipment Qty: raw value 2 desimal, TANPA pemisah ribuan (sesuai native)
            $shipmentQtyDisplay = $row->qtys !== null
                ? number_format((float) $row->qtys, 2, '.', '')
                : '0.00';

            return [
                'importdtpk'        => $row->importdtpk,
                'popk'              => $row->popk,
                'pono'              => $row->pono ?? '-',
                'item'              => $row->item ?? '-',
                'description'       => $row->spec ?? '-',
                'supplier'          => $row->supplier ?? '-',
                'color'             => $row->colname ?? '-',
                'price'             => $priceTotal,
                'qty_po'            => $qtyPoDisplay,
                'shipment_qty'      => $shipmentQtyDisplay,
                'shipment_qty_raw'  => $row->qtys ?? 0,
            ];
        })->values(),

        'logs' => $logs,
    ];

    return response()->json([
        'success' => true,
        'data'    => $data
    ]);
}

// public function deleteShipmentMaterial($importdtpk)
// {

//     // if (Session::get('login') !== 'exim') {
//     //     return response()->json([
//     //         'success' => false,
//     //         'message' => 'You do not have permission to delete this material'
//     //     ], 403);
//     // }
//     if (!in_array(Session::get('login'), ['exim', 'import'])) {
//         return response()->json([
//             'success' => false,
//             'message' => 'You do not have permission to update this shipment plan'
//         ], 403);
//     }

//     DB::connection('mysql_sig')
//         ->table('importdt')
//         ->where('importdtpk', $importdtpk)
//         ->delete();

//     return response()->json([
//         'success' => true,
//         'message' => 'The materials have been deleted from list.'
//     ]);
// }
public function deleteShipmentMaterial($importdtpk)
{
    if (!in_array(Session::get('login'), ['exim', 'import'])) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to update this shipment plan'
        ], 403);
    }

    $conn = DB::connection('mysql_sig');

    $importpk = $conn->table('importdt')->where('importdtpk', $importdtpk)->value('importpk');

    $conn->table('importdt')
        ->where('importdtpk', $importdtpk)
        ->delete();

    if ($importpk) {
        $conn->table('import')->where('importpk', $importpk)->update(['dateupdtim' => now()]);
    }

    return response()->json([
        'success' => true,
        'message' => 'The materials have been deleted from list.'
    ]);
}

public function getMaterialsForPo($popk)
{
    $conn = DB::connection('mysql_sig');

    $rows = $conn->table('podt')
        ->leftJoin('po', 'po.popk', '=', 'podt.popk')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('rkb', 'rkb.popk', '=', 'po.popk')
        ->leftJoin('cur', 'cur.curpk', '=', 'po.curpk')
        ->where('podt.popk', $popk)
        ->groupBy('podt.podtpk')
        ->select(
            'podt.podtpk',
            'rkb.item',
            'podt.spec',
            'sup.supnm as supplier',
            'podt.colname as color',
            'podt.price',
            'podt.pocons as qty_po',
            'podt.unit',
            'cur.curid',
            'cur.cursign'
        )
        ->get();

    $data = $rows->map(function ($row) use ($conn) {
        $shipped = $conn->table('importdt')
            ->where('podtpk', $row->podtpk)
            ->sum('qtys');

        // ==== CURRENCY SIGN ====
        $cursign = $row->cursign;
        switch ($row->curid) {
            case 'USD':
            case 'HKD':
                $cursign = '$';
                break;
            case 'EUR':
                $cursign = '€';
                break;
            case 'GBP':
                $cursign = '£';
                break;
        }

        // ==== QTY untuk perhitungan total, persis pola native ($piqty1) ====
        $isVolumetricUnit = in_array(trim($row->unit), ['KG', 'DZN', 'RIM']);
        $piqty1 = $isVolumetricUnit ? $row->qty_po : ceil($row->qty_po);

        // ==== PRICE = TOTAL (price x qty), BUKAN harga satuan ====
        $decimals = $row->curid === 'IDR' ? 2 : 5;

        if (empty($row->price) || (float) $row->price == 0) {
            $priceFormatted = '-';
        } else {
            $total = (float) $row->price * $piqty1;
            $priceFormatted = $cursign . ' ' . number_format($total, $decimals, '.', ',');
        }

        // ==== QTY PO & QTY SHIPMENT, dengan suffix unit ====
        $qtyPoFormatted = number_format((float) $row->qty_po, 0, '.', ',') . ' ' . $row->unit;
        $shippedFormatted = number_format((float) $shipped, 0, '.', ',') . ' ' . $row->unit;

        // ==== BALANCE = shipped - qty_po ====
        $balanceRaw = $shipped - $row->qty_po;
        $balanceFormatted = number_format($balanceRaw, 0, '.', ',') . ' ' . $row->unit;

        return [
            'podtpk'       => $row->podtpk,
            'item'         => $row->item ?? '-',
            'description'  => $row->spec ?? '-',
            'supplier'     => $row->supplier ?? '-',
            'color'        => $row->color ?? '-',
            'price'        => $priceFormatted,
            'qty_po'       => $qtyPoFormatted,
            'qty_shipment' => $shippedFormatted,
            'balance'      => $balanceFormatted,
            'balance_raw'  => $balanceRaw,
        ];
    });

    return response()->json([
        'success' => true,
        'data'    => $data,
    ]);
}

// public function addMaterialsToShipment(Request $request, $importpk)
// {

//     // if (Session::get('login') !== 'exim') {
//     //     return response()->json([
//     //         'success' => false,
//     //         'message' => 'You do not have permission to add materials to this shipment'
//     //     ], 403);
//     // }
//     if (!in_array(Session::get('login'), ['exim', 'import'])) {
//         return response()->json([
//             'success' => false,
//             'message' => 'You do not have permission to update this shipment plan'
//         ], 403);
//     }

//     $conn = DB::connection('mysql_sig');
//     $podtpks = $request->input('podtpks', []); // array of podtpk yang dicentang

//     if (empty($podtpks)) {
//         return response()->json([
//             'success' => false,
//             'message' => 'No material data selected'
//         ]);
//     }

//     foreach ($podtpks as $podtpk) {
//         $item = $conn->table('podt')
//             ->where('podtpk', $podtpk)
//             ->select('pocons', 'popk', 'podtpk')
//             ->first();

//         if (!$item) {
//             continue;
//         }

//         $shipped = $conn->table('importdt')
//             ->where('podtpk', $podtpk)
//             ->sum('qtys');

//         $balance = $item->pocons - $shipped;

//         $conn->table('importdt')->insert([
//             'importpk' => $importpk,
//             'popk'     => $item->popk,
//             'podtpk'   => $item->podtpk,
//             'qtys'     => $balance,
//             'stsdt'    => 0,
//         ]);
//     }

//     return response()->json([
//         'success' => true,
//         'message' => count($podtpks) . ' materials added to shipment plan'
//     ]);
// }
public function addMaterialsToShipment(Request $request, $importpk)
{
    if (!in_array(Session::get('login'), ['exim', 'import'])) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to update this shipment plan'
        ], 403);
    }

    $conn = DB::connection('mysql_sig');
    $podtpks = $request->input('podtpks', []);

    if (empty($podtpks)) {
        return response()->json([
            'success' => false,
            'message' => 'No material data selected'
        ]);
    }

    $addedCount = 0;

    foreach ($podtpks as $podtpk) {
        $item = $conn->table('podt')
            ->where('podtpk', $podtpk)
            ->select('pocons', 'popk', 'podtpk')
            ->first();

        if (!$item) {
            continue;
        }

        $shipped = $conn->table('importdt')
            ->where('podtpk', $podtpk)
            ->sum('qtys');

        $balance = $item->pocons - $shipped;

        $conn->table('importdt')->insert([
            'importpk' => $importpk,
            'popk'     => $item->popk,
            'podtpk'   => $item->podtpk,
            'qtys'     => $balance,
            'stsdt'    => 0,
        ]);

        $addedCount++;
    }

    if ($addedCount > 0) {
        $conn->table('import')->where('importpk', $importpk)->update(['dateupdtim' => now()]);
    }

    return response()->json([
        'success' => true,
        'message' => $addedCount . ' materials added to shipment plan'
    ]);
}

// public function updateShipmentItemQty(Request $request, $importdtpk)
// {

//     // if (Session::get('login') !== 'exim') {
//     //     return response()->json([
//     //         'success' => false,
//     //         'message' => 'You do not have permission to update this item quantity'
//     //     ], 403);
//     // }
//     if (!in_array(Session::get('login'), ['exim', 'import'])) {
//         return response()->json([
//             'success' => false,
//             'message' => 'You do not have permission to update this shipment plan'
//         ], 403);
//     }

//     DB::connection('mysql_sig')
//         ->table('importdt')
//         ->where('importdtpk', $importdtpk)
//         ->update(['qtys' => $request->input('qtys')]);

//     return response()->json(['success' => true]);
// }
public function updateShipmentItemQty(Request $request, $importdtpk)
{
    if (!in_array(Session::get('login'), ['exim', 'import'])) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to update this shipment plan'
        ], 403);
    }

    $conn = DB::connection('mysql_sig');

    $importpk = $conn->table('importdt')->where('importdtpk', $importdtpk)->value('importpk');

    $conn->table('importdt')
        ->where('importdtpk', $importdtpk)
        ->update(['qtys' => $request->input('qtys')]);

    if ($importpk) {
        $conn->table('import')->where('importpk', $importpk)->update(['dateupdtim' => now()]);
    }

    return response()->json(['success' => true]);
}

// public function updateShipmentPlan(Request $request, $importpk)
// {
//     if (Session::get('login') !== 'exim') {
//         return response()->json([
//             'success' => false,
//             'message' => 'You do not have permission to update this shipment plan'
//         ], 403);
//     }

//     $conn = DB::connection('mysql_sig');

//     $old = $conn->table('import')
//         ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
//         ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
//         ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
//         ->where('import.importpk', $importpk)
//         ->select(
//             'import.vassalpk', 'import.vassalnm', 'import.AWB',
//             'import.arrivalpk', 'import.portpk',
//             'import.GW', 'import.DP', 'import.DL', 'import.DT', 'import.totcoli',
//             'import.ETDV', 'import.ETDA', 'import.EIH',
//             'vassal.typenm as vassal_type_old',
//             'arrival.city as arrival_city_old',
//             'port.portnm as port_name_old'
//         )
//         ->first();

//     if (!$old) {
//         return response()->json(['success' => false, 'message' => 'Shipment tidak ditemukan'], 404);
//     }

//     // ==== input baru dari form ====
//     $newVassalpk   = $request->input('vassalpk');
//     $newVassalType = $conn->table('vassal')->where('vassalpk', $newVassalpk)->value('typenm');
//     $newVassalnm   = $request->input('vassal_name');
//     $newAwb        = $request->input('awb');
//     $newArrivalpk  = $request->input('arrivalpk');
//     $newArrivalCity= $conn->table('arrival')->where('arrivalpk', $newArrivalpk)->value('city');
//     $newPortpk     = $request->input('portpk');
//     $newPortName   = $conn->table('port')->where('portpk', $newPortpk)->value('portnm');
//     $newGw         = $request->input('gw');
//     $newDp         = $request->input('dp');
//     $newDl         = $request->input('dl');
//     $newDt         = $request->input('dt');
//     $newAmount     = $request->input('total_package');

//     $newEtdText = $request->input('etd');
//     $newEtaText = $request->input('eta');
//     $newEihText = $request->input('eih');

//     $newEtd = $newEtdText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newEtdText)->format('Y-m-d') : null;
//     $newEta = $newEtaText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newEtaText)->format('Y-m-d') : null;
//     $newEih = $newEihText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newEihText)->format('Y-m-d') : null;

//     $oldEtdText = $old->ETDV ? \Carbon\Carbon::parse($old->ETDV)->format('d/m/Y') : null;
//     $oldEtaText = $old->ETDA ? \Carbon\Carbon::parse($old->ETDA)->format('d/m/Y') : null;
//     $oldEihText = $old->EIH ? \Carbon\Carbon::parse($old->EIH)->format('d/m/Y') : null;

//     $now = now();

//     // ==== VASSAL TYPE ====
//     if ($this->normStr($old->vassal_type_old) !== $this->normStr($newVassalType)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'vassalpk' => $newVassalpk, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim1')->insert([
//             'typenm1' => $old->vassal_type_old,
//             'typenm2' => $newVassalType,
//             'logtype' => "Vassal type changed from {$old->vassal_type_old} to {$newVassalType}",
//             'logdate1' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== VASSAL NAME ====
//     if ($this->normStr($old->vassalnm) !== $this->normStr($newVassalnm)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'vassalnm' => $newVassalnm, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim1')->insert([
//             'vassalnm1' => $old->vassalnm,
//             'vassalnm2' => $newVassalnm,
//             'logvassal' => "Items moved from {$old->vassalnm} to {$newVassalnm}",
//             'logdate1' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== AWB ====
//     if ($this->normStr($old->AWB) !== $this->normStr($newAwb)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'AWB' => $newAwb, 'dateupdtim' => $now,
//         ]);
//     }

//     // ==== ARRIVAL CITY ====
//     if ($this->normStr($old->arrival_city_old) !== $this->normStr($newArrivalCity)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'arrivalpk' => $newArrivalpk, 'dateupdtim' => $now,
//         ]);
//     }

//     // ==== PORT NAME ====
//     if ($this->normStr($old->port_name_old) !== $this->normStr($newPortName)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'portpk' => $newPortpk, 'dateupdtim' => $now,
//         ]);
//     }

//     // ==== GROSS WEIGHT ====
//     if (!$this->isSameNumber($old->GW, $newGw)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'GW' => $newGw, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim2')->insert([
//             'GW1' => $old->GW, 'GW2' => $newGw,
//             'loggw' => "Gross weight changed from {$old->GW}kg to {$newGw}kg",
//             'logdate2' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== DIMENSION ====
//     if (!$this->isSameNumber($old->DP, $newDp) || !$this->isSameNumber($old->DL, $newDl) || !$this->isSameNumber($old->DT, $newDt)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'DP' => $newDp, 'DL' => $newDl, 'DT' => $newDt, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim2')->insert([
//             'DP1' => $old->DP, 'DP2' => $newDp,
//             'DL1' => $old->DL, 'DL2' => $newDl,
//             'DT1' => $old->DT, 'DT2' => $newDt,
//             'logd' => "Dimension changed from {$old->DP}x{$old->DL}x{$old->DT} to {$newDp}x{$newDl}x{$newDt}",
//             'logdate2' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== TOTAL PACKAGE ====
//     if (!$this->isSameNumber($old->totcoli, $newAmount)) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'totcoli' => $newAmount, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim2')->insert([
//             'totcoli1' => $old->totcoli, 'totcoli2' => $newAmount,
//             'logamount' => "Total package changed from {$old->totcoli} to {$newAmount}",
//             'logdate2' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== ETD ====
//     if ($newEtd && $old->ETDV != $newEtd) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'ETDV' => $newEtd, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim3')->insert([
//             'ETDV1' => $old->ETDV, 'ETDV2' => $newEtd,
//             'logetd' => "ETD changed from {$oldEtdText} to {$newEtdText}",
//             'logdate3' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== ETA ====
//     if ($newEta && $old->ETDA != $newEta) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'ETDA' => $newEta, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim3')->insert([
//             'ETA1' => $old->ETDA, 'ETA2' => $newEta,
//             'logeta' => "ETA changed from {$oldEtaText} to {$newEtaText}",
//             'logdate3' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     // ==== EIH ====
//     if ($newEih && $old->EIH != $newEih) {
//         $conn->table('import')->where('importpk', $importpk)->update([
//             'EIH' => $newEih, 'dateupdtim' => $now,
//         ]);
//         $conn->table('logim3')->insert([
//             'EIH1' => $old->EIH, 'EIH2' => $newEih,
//             'logeih' => "Estimate In-House changed from {$oldEihText} to {$newEihText}",
//             'logdate3' => $now,
//             'importpk' => $importpk,
//         ]);
//     }

//     return response()->json([
//         'success' => true,
//         'message' => 'Shipment plan updated'
//     ]);
// }
public function updateShipmentPlan(Request $request, $importpk)
{
    if (!in_array(Session::get('login'), ['exim', 'import'])) {
        return response()->json([
            'success' => false,
            'message' => 'You do not have permission to update this shipment plan'
        ], 403);
    }

    $conn = DB::connection('mysql_sig');

    $old = $conn->table('import')
        ->leftJoin('vassal', 'vassal.vassalpk', '=', 'import.vassalpk')
        ->leftJoin('arrival', 'arrival.arrivalpk', '=', 'import.arrivalpk')
        ->leftJoin('port', 'port.portpk', '=', 'import.portpk')
        ->where('import.importpk', $importpk)
        ->select(
            'import.vassalpk', 'import.vassalnm', 'import.AWB',
            'import.arrivalpk', 'import.portpk',
            'import.GW', 'import.DP', 'import.DL', 'import.DT', 'import.totcoli',
            'import.ETDV', 'import.ETDA', 'import.EIH',
            'import.ATD', 'import.ATA',
            'import.stsimpk', 
            'vassal.typenm as vassal_type_old',
            'arrival.city as arrival_city_old',
            'port.portnm as port_name_old'
        )
        ->first();

    if (!$old) {
        return response()->json(['success' => false, 'message' => 'Shipment tidak ditemukan'], 404);
    }

    // ==== input baru dari form ====
    $newVassalpk   = $request->input('vassalpk');
    $newVassalType = $conn->table('vassal')->where('vassalpk', $newVassalpk)->value('typenm');
    $newVassalnm   = $request->input('vassal_name');
    $newAwb        = $request->input('awb');
    $newArrivalpk  = $request->input('arrivalpk');
    $newArrivalCity= $conn->table('arrival')->where('arrivalpk', $newArrivalpk)->value('city');
    $newPortpk     = $request->input('portpk');
    $newPortName   = $conn->table('port')->where('portpk', $newPortpk)->value('portnm');
    $newGw         = $request->input('gw');
    $newDp         = $request->input('dp');
    $newDl         = $request->input('dl');
    $newDt         = $request->input('dt');
    $newAmount     = $request->input('total_package');

    $newEtdText = $request->input('etd');
    $newEtaText = $request->input('eta');
    $newEihText = $request->input('eih');

    $newEtd = $newEtdText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newEtdText)->format('Y-m-d') : null;
    $newEta = $newEtaText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newEtaText)->format('Y-m-d') : null;
    $newEih = $newEihText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newEihText)->format('Y-m-d') : null;

    $oldEtdText = $old->ETDV ? \Carbon\Carbon::parse($old->ETDV)->format('d/m/Y') : null;
    $oldEtaText = $old->ETDA ? \Carbon\Carbon::parse($old->ETDA)->format('d/m/Y') : null;
    $oldEihText = $old->EIH ? \Carbon\Carbon::parse($old->EIH)->format('d/m/Y') : null;

    $now = now();

    // ==== VASSAL TYPE ====
    if ($this->normStr($old->vassal_type_old) !== $this->normStr($newVassalType)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'vassalpk' => $newVassalpk, 'dateupdtim' => $now,
        ]);
        $conn->table('logim1')->insert([
            'typenm1' => $old->vassal_type_old,
            'typenm2' => $newVassalType,
            'logtype' => "Vassal type changed from {$old->vassal_type_old} to {$newVassalType}",
            'logdate1' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== VASSAL NAME ====
    if ($this->normStr($old->vassalnm) !== $this->normStr($newVassalnm)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'vassalnm' => $newVassalnm, 'dateupdtim' => $now,
        ]);
        $conn->table('logim1')->insert([
            'vassalnm1' => $old->vassalnm,
            'vassalnm2' => $newVassalnm,
            'logvassal' => "Items moved from {$old->vassalnm} to {$newVassalnm}",
            'logdate1' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== AWB ====
    if ($this->normStr($old->AWB) !== $this->normStr($newAwb)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'AWB' => $newAwb, 'dateupdtim' => $now,
        ]);
    }

    // ==== ARRIVAL CITY ====
    if ($this->normStr($old->arrival_city_old) !== $this->normStr($newArrivalCity)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'arrivalpk' => $newArrivalpk, 'dateupdtim' => $now,
        ]);
    }

    // ==== PORT NAME ====
    if ($this->normStr($old->port_name_old) !== $this->normStr($newPortName)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'portpk' => $newPortpk, 'dateupdtim' => $now,
        ]);
    }

    // ==== GROSS WEIGHT ====
    if (!$this->isSameNumber($old->GW, $newGw)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'GW' => $newGw, 'dateupdtim' => $now,
        ]);
        $conn->table('logim2')->insert([
            'GW1' => $old->GW, 'GW2' => $newGw,
            'loggw' => "Gross weight changed from {$old->GW}kg to {$newGw}kg",
            'logdate2' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== DIMENSION ====
    if (!$this->isSameNumber($old->DP, $newDp) || !$this->isSameNumber($old->DL, $newDl) || !$this->isSameNumber($old->DT, $newDt)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'DP' => $newDp, 'DL' => $newDl, 'DT' => $newDt, 'dateupdtim' => $now,
        ]);
        $conn->table('logim2')->insert([
            'DP1' => $old->DP, 'DP2' => $newDp,
            'DL1' => $old->DL, 'DL2' => $newDl,
            'DT1' => $old->DT, 'DT2' => $newDt,
            'logd' => "Dimension changed from {$old->DP}x{$old->DL}x{$old->DT} to {$newDp}x{$newDl}x{$newDt}",
            'logdate2' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== TOTAL PACKAGE ====
    if (!$this->isSameNumber($old->totcoli, $newAmount)) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'totcoli' => $newAmount, 'dateupdtim' => $now,
        ]);
        $conn->table('logim2')->insert([
            'totcoli1' => $old->totcoli, 'totcoli2' => $newAmount,
            'logamount' => "Total package changed from {$old->totcoli} to {$newAmount}",
            'logdate2' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== ETD ====
    if ($newEtd && $old->ETDV != $newEtd) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'ETDV' => $newEtd, 'dateupdtim' => $now,
        ]);
        $conn->table('logim3')->insert([
            'ETDV1' => $old->ETDV, 'ETDV2' => $newEtd,
            'logetd' => "ETD changed from {$oldEtdText} to {$newEtdText}",
            'logdate3' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== ETA ====
    if ($newEta && $old->ETDA != $newEta) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'ETDA' => $newEta, 'dateupdtim' => $now,
        ]);
        $conn->table('logim3')->insert([
            'ETA1' => $old->ETDA, 'ETA2' => $newEta,
            'logeta' => "ETA changed from {$oldEtaText} to {$newEtaText}",
            'logdate3' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== EIH ====
    if ($newEih && $old->EIH != $newEih) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'EIH' => $newEih, 'dateupdtim' => $now,
        ]);
        $conn->table('logim3')->insert([
            'EIH1' => $old->EIH, 'EIH2' => $newEih,
            'logeih' => "Estimate In-House changed from {$oldEihText} to {$newEihText}",
            'logdate3' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== ATD & ATA ====
    // CATATAN: tabel logim3 belum punya kolom untuk ATD/ATA, jadi perubahan
    // disimpan ke kolom import.ATD/import.ATA tapi TIDAK dicatat di Shipment Log
    // untuk saat ini (sesuai keputusan yang sudah dikonfirmasi).
    // $newAtdText = $request->input('atd');
    // $newAtaText = $request->input('ata');

    // $newAtd = $newAtdText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newAtdText)->format('Y-m-d') : null;
    // $newAta = $newAtaText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newAtaText)->format('Y-m-d') : null;

    // if ($newAtd && $old->ATD != $newAtd) {
    //     $conn->table('import')->where('importpk', $importpk)->update([
    //         'ATD' => $newAtd, 'dateupdtim' => $now,
    //     ]);
    // }

    // if ($newAta && $old->ATA != $newAta) {
    //     $conn->table('import')->where('importpk', $importpk)->update([
    //         'ATA' => $newAta, 'dateupdtim' => $now,
    //     ]);
    // }

    $newAtdText = $request->input('atd');
    $newAtaText = $request->input('ata');

    $newAtd = $newAtdText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newAtdText)->format('Y-m-d') : null;
    $newAta = $newAtaText ? \Carbon\Carbon::createFromFormat('d/m/Y', $newAtaText)->format('Y-m-d') : null;

    $oldAtdText = $old->ATD ? \Carbon\Carbon::parse($old->ATD)->format('d/m/Y') : null;
    $oldAtaText = $old->ATA ? \Carbon\Carbon::parse($old->ATA)->format('d/m/Y') : null;

    // ==== ATD ====
    if ($newAtd && $old->ATD != $newAtd) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'ATD' => $newAtd, 'dateupdtim' => $now,
        ]);
        $conn->table('logim3')->insert([
            'ATD1' => $old->ATD, 'ATD2' => $newAtd,
            'logatd' => "ATD changed from {$oldAtdText} to {$newAtdText}",
            'logdate3' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== ATA ====
    if ($newAta && $old->ATA != $newAta) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'ATA' => $newAta, 'dateupdtim' => $now,
        ]);
        $conn->table('logim3')->insert([
            'ATA1' => $old->ATA, 'ATA2' => $newAta,
            'logata' => "ATA changed from {$oldAtaText} to {$newAtaText}",
            'logdate3' => $now,
            'importpk' => $importpk,
        ]);
    }

    // ==== STATUS SHIPMENT (stsimpk) — otomatis berdasarkan ATD & ATA ====
    // ATD terisi -> En-route (1)
    // ATA terisi -> Arrived at Port (2), prioritas lebih tinggi dari ATD
    // Kalau keduanya kosong -> status tidak berubah (tetap default lama)
    $finalAtd = $newAtd ?: $old->ATD;
    $finalAta = $newAta ?: $old->ATA;

    $newStsimpk = $this->resolveStatusFromDates($finalAtd, $finalAta, $old->stsimpk);

    if ($old->stsimpk != $newStsimpk) {
        $conn->table('import')->where('importpk', $importpk)->update([
            'stsimpk' => $newStsimpk, 'dateupdtim' => $now,
        ]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Shipment plan updated'
    ]);
}

public function shipOptions()
{
    $conn = DB::connection('mysql_sig');

    return response()->json([
        'vassal'  => $conn->table('vassal')->select('vassalpk', 'typenm')->orderBy('vassalpk')->get(),
        'arrival' => $conn->table('arrival')->select('arrivalpk', 'city')->orderBy('arrivalpk')->get(),
        'port'    => $conn->table('port')->select('portpk', 'portnm', 'arrivalpk')->orderBy('portpk')->get(),
    ]);
}

public function getApprovedPoNotifications()
{
    $conn = DB::connection('mysql_sig');

    $totalPending = $conn->table('po')->where('tpo2d', '1')->count();

    $rows = $conn->table('po')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('fu', 'fu.fupk', '=', 'ord.fupk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->where('po.tpo2d', '1')
        ->groupBy('po.popk')
        ->orderByDesc('po.tpo2d')
        ->orderByDesc('po.dateapv')
        ->limit(5)
        ->select(
            'po.pur',
            'po.pono',
            'byr.byrnm',
            'podt.item',
            'po.dateapv',
            'po.popk',
            'fu.funm'
        )
        ->get()
        ->map(function ($row) {
            return [
                'pur'     => $row->pur ?: ($row->funm ?: '-'),
                'pono'    => $row->pono ?? '-',
                'byrnm'   => $row->byrnm ?? '-',
                'item'    => $row->item ?? '-',
                'dateapv' => $row->dateapv,
                'popk'    => $row->popk,
            ];
        });

    return response()->json([
        'total'         => $totalPending,
        'show_more'     => $totalPending > 5,
        'notifications' => $rows,
    ]);
}

public function viewApprovedPo(Request $request, $popk)
{
    // setara action native x=mempo: tandai PO sudah dilihat
    DB::connection('mysql_sig')
        ->table('po')
        ->where('popk', $popk)
        ->update(['tpo2d' => '0']);

    return redirect()->route('exim.import.detpopur', ['po' => $popk]);
}

public function getRevisedPoList(Request $request)
{
    $conn = DB::connection('mysql_sig');
    $keyword = $request->input('carim');
    $page = (int) $request->input('page', 1);
    $rows = (int) $request->input('rows', 100);
    $sort = $request->input('sort', 'dateapv');
    $order = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';

    // whitelist kolom sort supaya aman dari SQL injection lewat parameter
    $sortMap = [
        'pur'     => 'po.pur',
        'pono'    => 'po.pono',
        'byrnm'   => 'byr.byrnm',
        'brg'     => 'po.brg',
        'dateapv' => 'po.dateapv',
    ];
    $sortColumn = $sortMap[$sort] ?? 'po.dateapv';

    $baseQuery = $conn->table('po')
        ->leftJoin('sup', 'sup.suppk', '=', 'po.suppk')
        ->leftJoin('podt', 'podt.popk', '=', 'po.popk')
        ->leftJoin('ord', 'ord.ordpk', '=', 'podt.ordpk')
        ->leftJoin('fu', 'fu.fupk', '=', 'ord.fupk')
        ->leftJoin('byr', 'byr.byrpk', '=', 'ord.byrpk')
        ->where('po.tpo2d', '1');

    if ($keyword) {
        $baseQuery->whereRaw("CONCAT(po.pono, byr.byrnm, po.brg) LIKE ?", ["%{$keyword}%"]);
    }

    // total (distinct popk, karena join podt bisa gandakan baris)
    $total = (clone $baseQuery)->distinct()->count('po.popk');

    $offset = ($page - 1) * $rows;

    $data = $baseQuery
        ->groupBy('po.popk')
        ->orderBy($sortColumn, $order)
        ->offset($offset)
        ->limit($rows)
        ->select(
            'po.pur',
            'po.pono',
            'byr.byrnm',
            'po.brg',
            'po.dateapv',
            'po.popk',
            'fu.funm'
        )
        ->get()
        ->map(function ($row) {
            return [
                'pur'     => $row->pur ?: ($row->funm ?: '-'),
                'pono'    => $row->pono,
                'byrnm'   => $row->byrnm,
                'brg'     => $row->brg,
                'dateapv' => $row->dateapv,
                'popk'    => $row->popk,
            ];
        });

    return response()->json([
        'rows'  => $data,
        'total' => $total,
        'page'  => $page,
        'rowsPerPage' => $rows,
    ]);
}

// public function createShipmentDraft(Request $request)
// {
//     $popks = $request->input('popks', []);

//     if (empty($popks)) {
//         return response()->json([
//             'success' => false,
//             'message' => 'No PO selected'
//         ], 422);
//     }

//     $conn = DB::connection('mysql_sig');
//     $userpk = session('user'); // TODO: sesuaikan dengan key session user id kamu

//     try {
//         $importpk = $conn->transaction(function () use ($conn, $popks, $userpk) {

//             // Replikasi logic native: ambil importpk terakhir, +1
//             $lastImportpk = $conn->table('import')
//                 ->orderByDesc('importpk')
//                 ->lockForUpdate()
//                 ->value('importpk');

//             $newImportpk = ($lastImportpk ?? 0) + 1;

//             $conn->table('import')->insert([
//                 'importpk'   => $newImportpk,
//                 'userpk'     => $userpk,
//                 'datecreate' => now(),
//             ]);

//             foreach ($popks as $popk) {
//                 $podtItems = $conn->table('podt')
//                     ->where('popk', $popk)
//                     ->orderBy('podtpk')
//                     ->get(['podtpk', 'pocons']);

//                 foreach ($podtItems as $podt) {
//                     $allocatedQty = $conn->table('importdt')
//                         ->where('podtpk', $podt->podtpk)
//                         ->where('stsdt', '0')
//                         ->sum('qtys');

//                     $balance = $podt->pocons - $allocatedQty;
//                     if ($balance < 0) {
//                         $balance = 0;
//                     }

//                     $conn->table('importdt')->insert([
//                         'importpk' => $newImportpk,
//                         'popk'     => $popk,
//                         'podtpk'   => $podt->podtpk,
//                         'qtys'     => $balance,
//                         'stsdt'    => '1', // draft
//                     ]);
//                 }
//             }

//             return $newImportpk;
//         });

//         return response()->json([
//             'success'  => true,
//             'importpk' => $importpk,
//         ]);

//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Failed to create shipment draft: ' . $e->getMessage()
//         ], 500);
//     }
// }

public function createShipmentDraft(Request $request)
{
    $popks = $request->input('popks', []);
    $qtys  = $request->input('qtys', []); // BARU: qty yang diketik user, key = podtpk

    if (empty($popks)) {
        return response()->json([
            'success' => false,
            'message' => 'No PO selected'
        ], 422);
    }

    $conn = DB::connection('mysql_sig');
    $userpk = session('user'); // TODO: sesuaikan dengan key session user id kamu

    try {
        $importpk = $conn->transaction(function () use ($conn, $popks, $qtys, $userpk) {

            // Replikasi logic native: ambil importpk terakhir, +1
            $lastImportpk = $conn->table('import')
                ->orderByDesc('importpk')
                ->lockForUpdate()
                ->value('importpk');

            $newImportpk = ($lastImportpk ?? 0) + 1;

            $conn->table('import')->insert([
                'importpk'   => $newImportpk,
                'userpk'     => $userpk,
                'datecreate' => now(),
            ]);

            foreach ($popks as $popk) {
                $podtItems = $conn->table('podt')
                    ->where('popk', $popk)
                    ->orderBy('podtpk')
                    ->get(['podtpk', 'pocons']);

                foreach ($podtItems as $podt) {
                    // BARU: kalau user mengisi qty untuk podtpk ini, pakai itu.
                    // Kalau tidak ada (misal baris tidak dikirim dari frontend), fallback ke balance seperti sebelumnya.
                    if (array_key_exists($podt->podtpk, $qtys)) {
                        $qty = (int) $qtys[$podt->podtpk];
                    } else {
                        $allocatedQty = $conn->table('importdt')
                            ->where('podtpk', $podt->podtpk)
                            ->where('stsdt', '0')
                            ->sum('qtys');

                        $qty = $podt->pocons - $allocatedQty;
                        if ($qty < 0) {
                            $qty = 0;
                        }
                    }

                    $conn->table('importdt')->insert([
                        'importpk' => $newImportpk,
                        'popk'     => $popk,
                        'podtpk'   => $podt->podtpk,
                        'qtys'     => $qty, // BARU: pakai qty dari input user
                        'stsdt'    => '1', // draft
                    ]);
                }
            }

            return $newImportpk;
        });

        return response()->json([
            'success'  => true,
            'importpk' => $importpk,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to create shipment draft: ' . $e->getMessage()
        ], 500);
    }
}

public function confirmShipmentPlan(Request $request, $importpk)
{
    $conn = DB::connection('mysql_sig');

    try {
        $conn->transaction(function () use ($conn, $importpk, $request) {

            $etdText = $request->input('etd1');
            $etaText = $request->input('eta1');
            $eihText = $request->input('eih1');

            $etd = $etdText ? \Carbon\Carbon::createFromFormat('d/m/Y', $etdText)->format('Y-m-d') : null;
            $eta = $etaText ? \Carbon\Carbon::createFromFormat('d/m/Y', $etaText)->format('Y-m-d') : null;
            $eih = $eihText ? \Carbon\Carbon::createFromFormat('d/m/Y', $eihText)->format('Y-m-d') : null;

            $conn->table('import')->where('importpk', $importpk)->update([
                'arrivalpk'  => $request->input('arrivalpk1'),
                'vassalpk'   => $request->input('vastype1'),
                'vassalnm'   => $request->input('vastnm1'),
                'portpk'     => $request->input('portpk1'),
                'AWB'        => $request->input('bill1'),
                'GW'         => $request->input('weight1'),
                'DP'         => $request->input('dp1'),
                'DL'         => $request->input('dl1'),
                'DT'         => $request->input('dt1'),
                'totcoli'    => $request->input('amount1'),
                'ETDV'       => $etd,
                'ETDA'       => $eta,
                'EIH'        => $eih,
                'dateim'     => now(),
                'stsimpk'    => '0',
            ]);

            // Ambil semua popk unik yang terkait importpk ini
            $popks = $conn->table('importdt')
                ->where('importpk', $importpk)
                ->distinct()
                ->pluck('popk');

            foreach ($popks as $popk) {
                $totalQtys = $conn->table('importdt')
                    ->where('importpk', $importpk)
                    ->where('popk', $popk)
                    ->sum('qtys');

                $totalPoQty = $conn->table('podt')
                    ->where('popk', $popk)
                    ->sum('pocons');

                $newStatus = ($totalQtys < $totalPoQty) ? '1' : '2'; // 1=partial, 2=full

                $conn->table('po')->where('popk', $popk)->update([
                    'stsimp' => $newStatus,
                ]);
            }

            // Finalisasi: draft -> confirmed
            $conn->table('importdt')->where('importpk', $importpk)->update([
                'stsdt' => '0',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Shipment plan data saved'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to save shipment plan: ' . $e->getMessage()
        ], 500);
    }
}

public function getShipmentLogs($importpk)
{
    $conn = DB::connection('mysql_sig');

    // $logSources = [
    //     ['table' => 'logim1', 'date' => 'logdate1', 'text' => 'logtype'],
    //     ['table' => 'logim1', 'date' => 'logdate1', 'text' => 'logvassal'],
    //     ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'logd'],
    //     ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'logamount'],
    //     ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'loggw'],
    //     ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logetd'],
    //     ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logeta'],
    //     ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logeih'],
    //     ['table' => 'logim4', 'date' => 'logdate4', 'text' => 'lognm'],
    // ];
    $logSources = [
        ['table' => 'logim1', 'date' => 'logdate1', 'text' => 'logtype'],
        ['table' => 'logim1', 'date' => 'logdate1', 'text' => 'logvassal'],
        ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'logd'],
        ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'logamount'],
        ['table' => 'logim2', 'date' => 'logdate2', 'text' => 'loggw'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logetd'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logeta'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logeih'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logatd'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logata'],
        ['table' => 'logim3', 'date' => 'logdate3', 'text' => 'logaih'],
        ['table' => 'logim4', 'date' => 'logdate4', 'text' => 'lognm'],
    ];

    $logs = collect();

    foreach ($logSources as $src) {
        $rows = $conn->table($src['table'])
            ->where('importpk', $importpk)
            ->select(
                "{$src['date']} as datelog",
                "{$src['text']} as logchange"
            )
            ->get();

        $logs = $logs->merge($rows);
    }

    $logs = $logs
        ->filter(fn($row) => !empty($row->logchange))
        ->unique(fn($row) => $row->datelog . '|' . $row->logchange)
        ->sortByDesc('datelog')
        ->values()
        ->map(function ($row) {
            return [
                'date'    => $row->datelog,
                'changes' => $row->logchange,
            ];
        });

    return response()->json([
        'success' => true,
        'logs'    => $logs,
    ]);
}

public function deleteShipmentDraft($importpk)
{
    $conn = DB::connection('mysql_sig');

    // Cuma hapus kalau memang masih draft (stsdt='1'), supaya tidak
    // tidak sengaja menghapus shipment yang sudah confirmed
    $isDraft = $conn->table('importdt')
        ->where('importpk', $importpk)
        ->where('stsdt', '1')
        ->exists();

    if ($isDraft) {
        $conn->table('importdt')->where('importpk', $importpk)->delete();
        $conn->table('import')->where('importpk', $importpk)->delete();
    }

    return response()->json(['success' => true]);
}

public function exportShip(Request $request)
{
    ['ships' => $ships] = $this->buildShipData($request, false);

    $filename = 'Shipment-planning_(' . now()->format('d-M-y_H-i-s') . ').xlsx';

    return Excel::download(new ShipExport($ships), $filename);
}

public function syncPoStatusManual()
{
    DB::connection('mysql_sig')->statement("
        UPDATE po
        LEFT JOIN (
            SELECT popk, SUM(qtys) AS shipped_qty
            FROM importdt
            WHERE stsdt = '0'
            GROUP BY popk
        ) shipped ON shipped.popk = po.popk
        LEFT JOIN (
            SELECT popk, SUM(pocons) AS po_qty
            FROM podt
            GROUP BY popk
        ) podt_sum ON podt_sum.popk = po.popk
        SET po.stsimp = CASE
            WHEN COALESCE(shipped.shipped_qty, 0) <= 0 THEN 0
            WHEN shipped.shipped_qty < podt_sum.po_qty THEN 1
            ELSE 2
        END
        WHERE po.polipk = 2
    ");

    return response()->json([
        'success' => true,
        'message' => 'Status PO berhasil disinkronkan',
    ]);
}


// private function 
private function formatShipDate($date)
{
    if (empty($date) || $date === '0000-00-00') {
        return '';
    }

    return \Carbon\Carbon::parse($date)->format('d/m/Y');
}
private function normStr($value)
{
    if ($value === null) return '';
    $v = trim((string) $value);
    return ($v === '-') ? '' : $v;
}

private function isSameNumber($a, $b)
{
    $numA = is_numeric($a) ? (float) $a : null;
    $numB = is_numeric($b) ? (float) $b : null;

    if ($numA === null && $numB === null) return true;
    if ($numA === null || $numB === null) return false;

    return $numA === $numB;
}

private function resolveStatusFromDates($atd, $ata, $currentStatus = null)
{
    $atdFilled = !empty($atd) && $atd != '0000-00-00';
    $ataFilled = !empty($ata) && $ata != '0000-00-00';

    if ($ataFilled) {
        return 2; // Arrived at Port
    }

    if ($atdFilled) {
        return 1; // En-route
    }

    return $currentStatus; // default: tidak berubah
}

// private function syncInHouseStatus(array $importpks)
// {
//     if (empty($importpks)) {
//         return;
//     }

//     $sigConn = DB::connection('mysql_sig');
//     $mifConn = DB::connection('mysql_mif');

//     $importdtRows = $sigConn->table('importdt')
//         ->whereIn('importpk', $importpks)
//         ->whereNotNull('podtpk')
//         ->where('podtpk', '!=', 0)
//         ->select('importpk', 'podtpk')
//         ->get();

//     if ($importdtRows->isEmpty()) {
//         return;
//     }

//     $allPodtpks = $importdtRows->pluck('podtpk')->unique()->values()->all();

//     $arrivedPodtpks = $mifConn->table('dtgdt')
//         ->whereIn('podtpk', $allPodtpks)
//         ->where('qtydtg', '>', 0)
//         ->distinct()
//         ->pluck('podtpk')
//         ->all();

//     if (empty($arrivedPodtpks)) {
//         return;
//     }

//     $arrivedLookup = array_flip($arrivedPodtpks);

//     $importpksToUpdate = $importdtRows
//         ->filter(fn($row) => isset($arrivedLookup[$row->podtpk]))
//         ->pluck('importpk')
//         ->unique()
//         ->values()
//         ->all();

//     if (!empty($importpksToUpdate)) {
//         $sigConn->table('import')
//             ->whereIn('importpk', $importpksToUpdate)
//             ->where('stsimpk', '!=', 3) // skip HANYA kalau sudah In House
//             ->update([
//                 'stsimpk'    => 3,
//                 'dateupdtim' => now(),
//             ]);
//     }
// }

private function syncInHouseStatus(array $importpks)
{
    if (empty($importpks)) {
        return;
    }

    $sigConn = DB::connection('mysql_sig');
    $mifConn = DB::connection('mysql_mif');

    $importdtRows = $sigConn->table('importdt')
        ->whereIn('importpk', $importpks)
        ->whereNotNull('podtpk')
        ->where('podtpk', '!=', 0)
        ->select('importpk', 'podtpk')
        ->get();

    if ($importdtRows->isEmpty()) {
        return;
    }

    $allPodtpks = $importdtRows->pluck('podtpk')->unique()->values()->all();

    // Ambil podtpk yang sudah datang + tanggal kedatangannya (tgldtg)
    $arrivedRows = $mifConn->table('dtgdt')
        ->leftJoin('dtg', 'dtg.dtgpk', '=', 'dtgdt.dtgpk')
        ->whereIn('dtgdt.podtpk', $allPodtpks)
        ->where('dtgdt.qtydtg', '>', 0)
        ->select('dtgdt.podtpk', 'dtg.tgldtg')
        ->get();

    if ($arrivedRows->isEmpty()) {
        return;
    }

    // podtpk => tanggal kedatangan PALING AWAL
    $arrivedLookup = $arrivedRows
        ->filter(fn($r) => !empty($r->tgldtg) && $r->tgldtg != '0000-00-00')
        ->groupBy('podtpk')
        ->map(fn($rows) => $rows->min('tgldtg'));

    if ($arrivedLookup->isEmpty()) {
        return;
    }

    // importpk => tanggal AIH (paling awal dari semua podtpk yang cocok di shipment itu)
    $importAihMap = [];
    foreach ($importdtRows as $row) {
        if ($arrivedLookup->has($row->podtpk)) {
            $tgl = $arrivedLookup[$row->podtpk];
            if (!isset($importAihMap[$row->importpk]) || $tgl < $importAihMap[$row->importpk]) {
                $importAihMap[$row->importpk] = $tgl;
            }
        }
    }

    if (empty($importAihMap)) {
        return;
    }

    // Filter ulang: hanya shipment yang statusnya BELUM In House
    $eligibleImportpks = $sigConn->table('import')
        ->whereIn('importpk', array_keys($importAihMap))
        ->where('stsimpk', '!=', 3)
        ->pluck('importpk')
        ->all();

    if (empty($eligibleImportpks)) {
        return;
    }

    $now = now();

    foreach ($eligibleImportpks as $importpk) {
        $newAih = $importAihMap[$importpk];

        $sigConn->table('import')->where('importpk', $importpk)->update([
            'stsimpk'    => 3,
            'dateupdtim' => $now,
        ]);

        $newAihText = \Carbon\Carbon::parse($newAih)->format('d/m/Y');

        $sigConn->table('logim3')->insert([
            'AIH2'     => $newAih,
            'logaih'   => "Actual In-House recorded on {$newAihText}",
            'logdate3' => $now,
            'importpk' => $importpk,
        ]);
    }
}
}