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
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(15)
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
                ] + ($contRow && property_exists($contRow, 'gate_open_at')
                    // Jam buka container (Security Gate) -- syarat tombol Mulai stuffing di sistem Packing List
                    ? ['gate_open_at' => $contRow->gate_open_at] : []));
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

    // GET api/dockBoard : loading dock Security Gate (table cont_stuffing) untuk layar TV stuffing.
    //   doors    {mif: jumlah dock}
    //   active   container yang sedang di dock: [{contpk, exportpk, mif, pintu, masuk_at}]
    //   incoming container pindah menuju MIF lain, belum masuk dock: [{contpk, exportpk, mif (asal), tujuan_mif, at}]
    //   waiting  sudah gate in & lulus, belum keluar, tidak di dock / tidak pindah (<= 14 hari): [{contpk, exportpk, mif|null, at}]
    public function dockBoard()
    {
        $gate = \App\Http\Controllers\Security\GateController::class;
        $table = $gate::STUFFING_TABLE;
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            return response()->json(['success' => false, 'message' => 'Table loading dock belum ada.'], 404);
        }
        $active = DB::table($table . ' as s')->join('cont', 'cont.contpk', '=', 's.contpk')
            ->where('s.aktif', 1)->whereNull('cont.gate_out_at')
            ->get(['s.contpk', 'cont.exportpk', 's.mif', 's.pintu', 's.masuk_at']);
        $latest = DB::table($table)->selectRaw('MAX(stfpk) AS stfpk')->groupBy('contpk');
        $last = DB::table($table . ' as s')->joinSub($latest, 'x', 'x.stfpk', '=', 's.stfpk')
            ->join('cont', 'cont.contpk', '=', 's.contpk')->whereNull('cont.gate_out_at')
            ->get(['s.contpk', 'cont.exportpk', 's.mif', 's.status', 's.tujuan_mif', 's.keluar_at'])->keyBy('contpk');
        $incoming = $last->filter(fn($r) => $r->status === 'pindah' && !$active->contains('contpk', $r->contpk))
            ->map(fn($r) => ['contpk' => (int) $r->contpk, 'exportpk' => (int) $r->exportpk, 'mif' => (int) $r->mif, 'tujuan_mif' => (int) $r->tujuan_mif, 'at' => $r->keluar_at])
            ->values();
        $waiting = DB::table('cont')->join('export', 'export.exportpk', '=', 'cont.exportpk')
            ->whereNotNull('cont.actcontdate')->whereNull('cont.gate_out_at')
            ->where('cont.actcontdate', '>=', now()->subDays(14))
            ->where(fn($q) => $q->whereNull('cont.gate_result')->orWhere('cont.gate_result', '<>', 0))
            ->where(fn($q) => $q->whereNull('export.stscancel')->orWhere('export.stscancel', '<>', 1))
            ->whereNotIn('cont.contpk', $active->pluck('contpk'))
            ->whereNotIn('cont.contpk', $incoming->pluck('contpk'))
            ->orderBy('cont.actcontdate')
            ->get(['cont.contpk', 'cont.exportpk', 'cont.actcontdate'])
            ->map(fn($r) => ['contpk' => (int) $r->contpk, 'exportpk' => (int) $r->exportpk,
                'mif' => isset($last[$r->contpk]) ? (int) $last[$r->contpk]->mif : null, 'at' => $r->actcontdate]);

        return response()->json([
            'success'  => true,
            'doors'    => $gate::DOORS,
            'active'   => $active->map(fn($r) => ['contpk' => (int) $r->contpk, 'exportpk' => (int) $r->exportpk, 'mif' => (int) $r->mif, 'pintu' => (int) $r->pintu, 'masuk_at' => $r->masuk_at])->values(),
            'incoming' => $incoming,
            'waiting'  => $waiting->values(),
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

        // Mulai stuffing -> isi juga "Jam mulai muat" Security Gate (berita acara stuffing),
        // hanya kalau masih kosong (isian security tidak ditimpa) & container belum keluar.
        if (!empty($updateData['start_ship']) && \Illuminate\Support\Facades\Schema::hasColumn('cont', 'gate_load_at')
            && empty($contRow->gate_load_at) && empty($contRow->gate_out_at ?? null)) {
            $updateData['gate_load_at'] = $updateData['start_ship'];
        }
        // Tim stuffing boleh mulai tanpa menunggu security: "Jam buka container" yang masih kosong
        // diisi = jam mulai stuffing; kalau security sudah mengisi, jam bukanya tidak ditimpa.
        if (!empty($updateData['start_ship']) && property_exists($contRow, 'gate_open_at')
            && empty($contRow->gate_open_at) && empty($contRow->gate_out_at ?? null)) {
            $updateData['gate_open_at'] = $updateData['start_ship'];
        }

        DB::table('cont')->where('contpk', $validated['contpk'])->update($updateData);

        // truk LCL dipakai beberapa SP: jam buka / mulai muat berlaku untuk satu truk, isi SP lain yang masih kosong
        $trip = (int) ($contRow->{\App\Http\Controllers\Security\GateController::TRIP_COLUMN} ?? 0);
        if ($trip > 0) {
            foreach (array_intersect_key($updateData, array_flip(\App\Http\Controllers\Security\GateController::TRIP_EARLIEST)) as $col => $at) {
                DB::table('cont')->where(\App\Http\Controllers\Security\GateController::TRIP_COLUMN, $trip)
                    ->whereNull($col)->whereNull('gate_out_at')->update([$col => $at]);
            }
        }

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
                ->leftJoin('fwd', 'fwd.fwdpk', 'cont.fwdpk')
                ->whereIn('cont.exportpk', $exports->pluck('exportpk'))
                ->orderBy('cont.contno')
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
