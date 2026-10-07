<?php

namespace App\Http\Controllers\Packing;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PackingController extends Controller
{
    private const CONNECTION = 'mysql_andon';

    // Batas jumlah popk per query WHERE IN supaya query tetap ringan.
    private const CHUNK_SIZE = 5000;

    // Normalisasi po.GAC (varchar, format campur) jadi DATE di SQL:
    // "2024-01-21" / "2024-1-2", "23/10/2024", "28/Feb/2025", serial Excel "45433".
    // Format lain (kosong, "--", "0000-00-00", dst) -> NULL.
    private const GAC_DATE_SQL = "(CASE
        WHEN po.GAC REGEXP '^[1-9][0-9]{3}-[0-9]{1,2}-[0-9]{1,2}' THEN STR_TO_DATE(LEFT(po.GAC, 10), '%Y-%m-%d')
        WHEN po.GAC REGEXP '^[0-9]{1,2}/[0-9]{1,2}/[1-9][0-9]{3}' THEN STR_TO_DATE(po.GAC, '%d/%m/%Y')
        WHEN po.GAC REGEXP '^[0-9]{1,2}/[A-Za-z]{3}/[1-9][0-9]{3}' THEN STR_TO_DATE(po.GAC, '%d/%b/%Y')
        WHEN po.GAC REGEXP '^[0-9]{5}$' THEN DATE_ADD('1899-12-30', INTERVAL po.GAC DAY)
    END)";

    private function db()
    {
        return DB::connection(self::CONNECTION);
    }

    public function index()
    {
        $gabung = $this->db()->table('gabung')
            ->select('gabungpk', 'keterangan')
            ->orderBy('gabungpk')
            ->get();
        return view('menu.exim-export.packing.index', compact('gabung'));
    }

    public function getList(Request $request)
    {
        $page   = max(1, (int) $request->page);
        $rows   = max(1, (int) $request->rows);
        $offset = ($page - 1) * $rows;
        $sortDir = $request->input('sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $isSuper = session('guserpk') == 34 || session('guserpk') == 7;

        // Super user: mif 1 & 2 diambil dalam SATU query.
        $mifs     = $isSuper ? [1, 2] : [session('pos') == 1 ? 1 : 2];
        $combined = $this->fetchAll($mifs, $request);

        // Agregasi per PO+OP. Filter search/buyer/year/ex_factory sudah
        // diterapkan di SQL, sisanya hanya filter yang butuh hasil agregasi.
        $aggregated = $this->applyPostAggregationFilters(
            $this->aggregateByPoOp($combined),
            $request
        );

        // gac_date sudah dinormalisasi di SQL (Y-m-d / null), jadi cukup
        // di-sort sebagai string. Null dianggap paling awal.
        $aggregated = $aggregated
            ->when(
                $sortDir === 'asc',
                fn($c) => $c->sortBy(fn($r) => $r->gac_date ?? ''),       // Earliest Ex Factory dulu
                fn($c) => $c->sortByDesc(fn($r) => $r->gac_date ?? '')    // Latest Ex Factory dulu
            )
            ->values();

        $total = $aggregated->count();
        $data  = $aggregated->slice($offset, $rows)->values();

        // Gambar & breakdown CTN cukup untuk baris di halaman aktif saja.
        $this->addOrderImageToRows($data);
        $this->addCtnBreakdownToRows($data);

        foreach ($data as $i => $row) {
            $row->no = $offset + $i + 1;
            unset($row->_popks); // field internal, tidak perlu dikirim ke frontend
            $row->popks2 = $row->_popks2;
        }

        return response()->json([
            'total' => $total,
            'rows'  => $data,
        ]);
    }

    // Ambil baris PO (sudah difilter) + total pack per popk.
    private function fetchAll(array $mifs, Request $request)
    {
        $query = $this->db()->table('po')
            ->selectRaw("po.popk, po.ordpk, po.sts, po.gabung, po.shipdate1, po.shipdate2,
                po.customer, po.season, po.POno, po.OP, po.poref, po.mif, po.GAC,
                po.buyer, po.style, po.qty, po.silhouette, po.ctn AS ctn,
                " . self::GAC_DATE_SQL . " AS gac_date")
            ->where('po.qty', '>', 0)
            ->where('po.OP', '<>', '')
            ->whereIn('po.mif', $mifs);

        $this->applyListFilters($query, $request);

        $rows = $query->orderBy('po.mif')->orderBy('po.popk')->get();

        $this->addPackTotalsToRows($rows);

        if ($request->fin) {
            $rows = $rows->filter(fn($r) => $r->packing_qty > 0)->values();
        }

        return $rows;
    }

    // Agregasi tabel pack (3jt+ baris) HANYA untuk popk hasil filter, dalam
    // satu pass (sebelumnya 4 subquery GROUP BY atas seluruh tabel pack).
    private function addPackTotalsToRows($rows): void
    {
        $totals = [];
        $popks = $rows->pluck('popk')->unique()->values()->all();

        foreach (array_chunk($popks, self::CHUNK_SIZE) as $chunk) {
            $agg = $this->db()->table('pack')
                ->selectRaw("popk,
                    SUM(pcs)  AS packing_qty,
                    SUM(pcsp) AS packing_qty_plan,
                    SUM(CASE WHEN status >= 4 THEN jmlpcs ELSE 0 END) AS packing_ctn,
                    GROUP_CONCAT(DISTINCT exportpk ORDER BY exportpk) AS exportpks")
                ->whereIn('popk', $chunk)
                ->groupBy('popk')
                ->get();

            foreach ($agg as $a) {
                $totals[$a->popk] = $a;
            }
        }

        foreach ($rows as $r) {
            $t = $totals[$r->popk] ?? null;
            $r->packing_qty_plan    = (float) ($t->packing_qty_plan ?? 0);
            $r->packing_qty         = (float) ($t->packing_qty ?? 0);
            $r->packing_qty_balance = $r->packing_qty - $r->packing_qty_plan;
            $r->packing_ctn         = (float) ($t->packing_ctn ?? 0);
            $r->ctn_balance         = $r->packing_ctn - (float) $r->ctn;
            $r->exportpks           = (string) ($t->exportpks ?? '');
        }
    }

    private function applyListFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('po.POno', 'like', "%{$search}%")
                    ->orWhere('po.OP', 'like', "%{$search}%")
                    ->orWhere('po.customer', 'like', "%{$search}%")
                    ->orWhere('po.season', 'like', "%{$search}%")
                    ->orWhere('po.style', 'like', "%{$search}%")
                    ->orWhere('po.material', 'like', "%{$search}%")
                    ->orWhere('po.buyer', 'like', "%{$search}%")
                    ->orWhere('po.silhouette', 'like', "%{$search}%")
                    ->orWhere('po.secsz', 'like', "%{$search}%")
                    ->orWhere('po.poref', 'like', "%{$search}%");
            });
        }

        if ($request->filled('buyer')) {
            $query->where('po.buyer', $request->buyer);
        }

        if ($request->filled('year')) {
            $year = (int) $request->year;
            $shortYear = $year - 2000;
            $query->whereRaw("LEFT(TRIM(po.OP),2) = ?", [sprintf('%02d', $shortYear)]);
        }

        // Filter Ex Factory (po.GAC) berdasarkan rentang tanggal; boleh
        // hanya salah satu sisi (dari / sampai).
        [$from, $to] = $this->resolveExFactoryRange($request);
        if ($from) {
            $query->whereRaw(self::GAC_DATE_SQL . ' >= ?', [$from]);
        }
        if ($to) {
            $query->whereRaw(self::GAC_DATE_SQL . ' <= ?', [$to]);
        }
    }

    // Baca ex_factory_from / ex_factory_to (Y-m-d). Nilai tidak valid
    // diabaikan, dan kalau terbalik otomatis ditukar.
    private function resolveExFactoryRange(Request $request): array
    {
        $parse = function ($value) {
            if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            $date = Carbon::createFromFormat('!Y-m-d', $value);
            return $date && $date->format('Y-m-d') === $value ? $value : null;
        };

        $from = $parse($request->input('ex_factory_from'));
        $to   = $parse($request->input('ex_factory_to'));

        if ($from && $to && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
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

    // Penjumlahan nilai kolom QTY, Packing /Pcs, CTN per PO+OP.
    private function aggregateByPoOp($collection)
    {
        return $collection
            ->groupBy(fn($row) => ($row->mif ?? '') . '|' . $row->POno . '|' . $row->OP . '|' . ($row->poref ?? ''))
            ->map(function ($group) {
                $representative = clone $group->sortByDesc('popk')->first();
                $representative->qty                 = (int) $group->sum('qty');
                $representative->packing_qty_plan    = (int) $group->sum('packing_qty_plan');
                $representative->packing_qty         = (int) $group->sum('packing_qty');
                $representative->ctn                 = (int) $group->sum('ctn');
                $representative->packing_ctn         = (int) $group->sum('packing_ctn');
                $representative->packing_qty_balance = $representative->packing_qty - $representative->packing_qty_plan;
                $representative->ctn_balance         = $representative->packing_ctn - $representative->ctn;

                $representative->_popks = $group->pluck('popk')->unique()->values()->all();
                $representative->_popks2 = $group
                    ->pluck('popk')
                    ->filter()
                    ->unique()
                    ->values()
                    ->implode(',');

                // Status pembuatan Packing List (Plan) -- Pending (belum
                // ada plan sama sekali), Partial (plan sudah dibuat SEBAGIAN,
                // belum menutupi Order Qty), Complete (plan >= Order Qty).
                $representative->packing_plan_status = $this->resolvePackingPlanStatus(
                    $representative->qty,
                    $representative->packing_qty_plan
                );

                return $representative;
            })
            ->values();
    }

    private function resolvePackingPlanStatus($qty, $planQty): string
    {
        $qty     = (float) $qty;
        $planQty = (float) $planQty;

        if ($planQty <= 0) {
            return 'pending';
        }
        if ($planQty < $qty) {
            return 'partial';
        }
        return 'complete';
    }

    // Filter yang hanya bisa dihitung SETELAH agregasi per PO+OP.
    private function applyPostAggregationFilters($rows, Request $request)
    {
        // Filter Status Packing List (Pending/Partial/Complete).
        if ($request->filled('packing_plan_status')) {
            $status = $request->packing_plan_status;
            $rows = $rows->filter(fn($r) => $r->packing_plan_status === $status);
        }

        return $rows->values();
    }

    private function addCtnBreakdownToRows($rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        foreach ($rows as $r) {
            $r->ctn_plan_count    = 0;
            $r->ctn_partial_count = 0;
            $r->ctn_full_count    = 0;
            $r->ctn_sealed_count  = 0;
        }

        $allPopks = $rows
            ->flatMap(fn($r) => $r->_popks ?? [$r->popk])
            ->unique()
            ->values()
            ->all();

        if (empty($allPopks)) {
            return;
        }

        // Hanya kolom yang dipakai getCtnRowStatus() (tabel pack 150+ kolom).
        $columns = ['popk', 'carton', 'segel', 'pcs'];
        for ($i = 1; $i <= 40; $i++) {
            $columns[] = "qty{$i}";
            $columns[] = "qtyp{$i}";
        }

        $packRowsByPopk = $this->db()->table('pack')
            ->whereIn('popk', $allPopks)
            ->get($columns)
            ->groupBy('popk');

        foreach ($rows as $r) {
            $popksForGroup = $r->_popks ?? [$r->popk];

            $groupPackRows = collect();
            foreach ($popksForGroup as $pk) {
                $groupPackRows = $groupPackRows->concat($packRowsByPopk->get($pk, collect()));
            }

            $byCarton = $groupPackRows->groupBy('carton');

            $r->ctn_plan_count = $byCarton->count(); // total carton yang SUDAH diplanningkan

            foreach ($byCarton as $cartonRows) {
                $status = $this->getCtnGroupStatus($cartonRows);

                if ($status === 'packing') {
                    $r->ctn_partial_count++;
                } elseif ($status === 'complete') {
                    $r->ctn_full_count++;
                } elseif ($status === 'sealed') {
                    $r->ctn_sealed_count++;
                }
                // 'planned' -- tidak masuk hitungan lain, cuma masuk total Plan (ctn_plan_count).
            }
        }
    }

    private function getCtnGroupStatus($cartonRows): string
    {
        $statuses = $cartonRows->map(fn($r) => $this->getCtnRowStatus($r));

        if ($statuses->contains('sealed')) {
            return 'sealed';
        }
        if ($statuses->isNotEmpty() && $statuses->every(fn($s) => $s === 'complete')) {
            return 'complete';
        }
        if ($statuses->contains(fn($s) => $s === 'packing' || $s === 'complete')) {
            return 'packing';
        }

        return 'planned';
    }

    private function getCtnRowStatus($row): string
    {
        if ((int) ($row->segel ?? 0) === 1) {
            return 'sealed';
        }

        $allMatch = true;
        $foundPlan = false;

        for ($i = 1; $i <= 40; $i++) {
            $plan = (float) ($row->{"qtyp{$i}"} ?? 0);
            if ($plan <= 0) continue;

            $foundPlan = true;
            $actual = (float) ($row->{"qty{$i}"} ?? 0);
            if ($actual !== $plan) {
                $allMatch = false;
                break;
            }
        }

        if ($foundPlan && $allMatch) {
            return 'complete';
        }

        if ((float) ($row->pcs ?? 0) > 0) {
            return 'packing';
        }

        return 'planned';
    }

    public function buyerList(Request $request)
    {
        $q = $request->q;

        $buyers = $this->db()->table('po')
            ->select('buyer')
            ->whereNotNull('buyer')
            ->where('buyer', '<>', '')
            ->when($q, function ($query) use ($q) {
                $query->where('buyer', 'like', "%{$q}%");
            })
            ->distinct()
            ->orderBy('buyer')
            ->get()
            ->toArray();

        array_unshift($buyers, (object) [
            'buyer' => '',
            'buyer_name' => 'All Buyer'
        ]);

        foreach ($buyers as $item) {
            if (!isset($item->buyer_name)) {
                $item->buyer_name = $item->buyer;
            }
        }

        return response()->json($buyers);
    }
}
