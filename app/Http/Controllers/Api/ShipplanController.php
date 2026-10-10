<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Export;
use App\Models\Exportdt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShipplanController extends Controller
{
    private function packingBaseUrl(): string
    {
        return rtrim(config('services.packing.url'), '/');
    }
    
    public function getListExport(Request $request)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->get($this->packingBaseUrl().'/api/packing/all-loaded-cartons');
            $packingRows = $response->successful() ? collect($response->json('rows') ?? []) : collect();
        } catch (\Throwable $e) {
            report($e);
            $packingRows = collect();
        }

        $exports = DB::table('export')
            ->leftJoin('vassal', 'vassal.vassalpk', '=', 'export.vassalpk')
            ->select('export.*', 'vassal.typenm')
            ->get()
            ->keyBy('exportpk');

        $conts = DB::table('cont')->get()->keyBy('contpk');

        // Susun ulang: 1 baris per (contpk, carton) -- SAMA bentuk keluaran
        // seperti sebelumnya (groupBy exportdt.contpk, exportdt.carton),
        // tapi sumbernya SEKARANG dari $packingRows (live), bukan exportdt.
        $result = $packingRows
            ->filter(fn ($r) => $exports->has($r['exportpk']))
            ->groupBy(fn ($r) => $r['contpk'] . '|' . $r['carton'] . '|' . $r['popk'])
            ->map(function ($rowsForCartonContainer) use ($exports, $conts) {
                $first = $rowsForCartonContainer->first();
                $exportRow = $exports->get($first['exportpk']);
                $contRow = $conts->get($first['contpk']);

                return (object) array_merge((array) $exportRow, [
                    'exportdtpk'  => null,
                    'contpk'      => $first['contpk'],
                    'qty'         => $rowsForCartonContainer->sum('pcsp'),
                    'packpk'      => $first['packpk'],
                    'popk'        => $first['popk'],
                    'contno'      => $contRow->contno ?? null,
                    'type'        => $contRow->type ?? null,
                    'actcontdate' => $contRow->actcontdate ?? null,
                    'start_ship'  => $contRow->start_ship ?? null,
                    'end_ship'    => $contRow->end_ship ?? null,
                    'segel'       => $contRow->segel ?? null,
                    'carton'      => $first['carton'],
                    'mif'         => $first['mif'],
                ]);
            })
            ->values();

        $total = $result->count();

        $ordpkList = $result->pluck('ordpk')->filter()->unique()->values();
        $ordnoMap = DB::connection('mysql_sig')
            ->table('ord')
            ->whereIn('ordpk', $ordpkList)
            ->pluck('ordno', 'ordpk');

        $result = $result->values()->map(function ($row) use ($ordnoMap) {
            $row->ordno = $row->OP ? ($row->OP ?? '-') : ($ordnoMap[$row->ordpk] ?? '-');
            return $row;
        });

        return response()->json([
            'total' => $total,
            'rows'  => $result,
        ]);
    }

    public function updateShipment(Request $request)
    {
        $validated = $request->validate([
            'exportpk'   => 'required|integer|exists:export,exportpk',
            'contpk'     => 'required|integer|exists:cont,contpk', 
            'start_ship' => 'nullable|date',
            'end_ship'   => 'nullable|date',
        ]);

        if (empty($validated['start_ship']) && empty($validated['end_ship'])) {
            return response()->json([
                'success' => false,
                'message' => 'Kirim minimal salah satu: start_ship atau end_ship.',
            ], 422);
        }

        $contRow = DB::table('cont')
            ->where('contpk', $validated['contpk'])
            ->where('exportpk', $validated['exportpk'])
            ->first();

        if (!$contRow) {
            return response()->json([
                'success' => false,
                'message' => 'Container tidak ditemukan / tidak terdaftar pada shipment plan ini.',
            ], 404);
        }

        $updateData = [];
        if (!empty($validated['start_ship'])) {
            $updateData['start_ship'] = Carbon::parse($validated['start_ship'])->toDateTimeString();
        }
        if (!empty($validated['end_ship'])) {
            $updateData['end_ship'] = Carbon::parse($validated['end_ship'])->toDateTimeString();
        }

        DB::table('cont')->where('contpk', $validated['contpk'])->update($updateData);

        DB::table('export')->where('exportpk', $validated['exportpk'])
            ->update(['dateupdt' => now()->toDateTimeString()]);

        return response()->json([
            'success'  => true,
            'message'  => 'Container berhasil diperbarui.',
            'exportpk' => $validated['exportpk'],
            'contpk'   => $validated['contpk'],
            'updated'  => $updateData,
        ]);
    }

    public function apiGetAllLoadedCartons(Request $request)
    {
        $rows = collect();
        foreach (['mysql_andon', 'mysql'] as $conn) {
            $mifValue = $conn === 'mysql_andon' ? 1 : 2;
            $found = DB::connection($conn)->table('pack')
                ->whereNotNull('exportpk')
                ->whereNotNull('contpk')
                ->get(['packpk', 'popk', 'carton', 'exportpk', 'contpk', 'pcsp'])
                ->map(function ($r) use ($mifValue) {
                    $r->mif = $mifValue;
                    return $r;
                });
            $rows = $rows->merge($found);
        }
    
        $rows = $rows->unique('packpk')->values();
    
        return response()->json(['rows' => $rows]);
    }


    public function getListShipment(Request $request)
    {
        $year = (int) $request->query('year', 0);
 
        $query = DB::table('export')
            ->leftJoin('vassal', 'vassal.vassalpk', '=', 'export.vassalpk')
            ->leftJoin('plb', 'plb.plbpk', '=', 'export.plbpk')
            ->select('export.*', 'vassal.typenm', 'plb.plbnm');
 
        if ($year > 0) {
            $query->whereYear('export.exdate', $year);
        }
 
        $exports = $query
            ->whereNull('export.stscancel')
            ->orderBy('export.exdate', 'desc')
            ->orderBy('export.exportpk', 'desc')
            ->get();
 
        $contsByExport = $exports->isEmpty()
            ? collect()
            : DB::table('cont')
                ->whereIn('exportpk', $exports->pluck('exportpk'))
                ->orderBy('contno')
                ->get()                      
                ->groupBy('exportpk');
 
        $rows = $exports->map(function ($e) use ($contsByExport) {
            $row = (array) $e;
            $row['containers'] = ($contsByExport->get($e->exportpk) ?? collect())
                ->map(fn ($c) => (array) $c)
                ->values();
            return $row;
        })->values();
 
        return response()->json([
            'success' => true,
            'total'   => $rows->count(),
            'rows'    => $rows,
        ]);
    }
}
