<?php

namespace App\Http\Controllers\Shipplan;

use App\Exports\ShipmentPackingListExport;
use App\Exports\ShipmentPlanExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Security\GateController;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use App\Models\Container;
use App\Models\Export;
use App\Models\Exportdt;
use App\Services\StuffingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Carbon\Carbon;

class ShipplanController extends Controller
{
    private const PACK_BUYER_COL = 'buyer';
    private const PO_BUYER_COL   = 'buyer';

    public function index()
    {
        return view(
            'menu.exim-export.shipplan.index'
        );
    }

    public function getListExport(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $rows = max(1, (int) $request->input('rows', 100));
        $offset = ($page - 1) * $rows;

        $userpk = Session::get('userpk');
        $guserpk = Session::get('guserpk');

        $query = $this->applyExportFilters($this->baseExportQuery(), $request, $userpk, $guserpk);

        $total = DB::query()
            ->fromSub(clone $query, 'sub')
            ->count();

        $result = $query
            ->limit($rows)
            ->offset($offset)
            ->get();

        if ($result->isEmpty()) {
            return response()->json([
                'total' => $total,
                'rows' => [],
            ]);
        }

        $result = $this->enrichExportRows($result, $offset);
        $result = $this->enrichPoCount($result);

        $this->addOrderImageToRows($result);
        $this->addUsernameToRows($result);

        return response()->json([
            'total' => $total,
            'rows' => $result,
        ]);
    }

    // ============================================================
    // LIST SHIPMENT PLAN - 1 baris per shipment (exportpk), daftar
    // OP/PO-nya dikirim di 'pos' untuk ditampilkan saat baris di-expand.
    // Paging dihitung per shipment supaya 1 shipment tidak terpotong
    // di 2 halaman. Filter & enrichment sama dengan getListExport.
    // ============================================================
    public function getListExportGrouped(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, (int) $request->input('rows', 100));
        $offset = ($page - 1) * $perPage;

        $userpk = Session::get('userpk');
        $guserpk = Session::get('guserpk');

        $query = $this->applyExportFilters($this->baseExportQuery(), $request, $userpk, $guserpk);

        $exportQuery = DB::query()
            ->fromSub(clone $query, 'sub')
            ->select(
                'sub.exportpk',
                DB::raw('MAX(sub.datecreate) as datecreate'),
                DB::raw('MIN(sub.closetime) as closetime'),
                DB::raw('MIN(sub.contdate) as contdate')
            )
            ->whereNotNull('sub.exportpk')
            ->groupBy('sub.exportpk');

        $total = DB::query()->fromSub(clone $exportQuery, 'exp')->count();

        $this->applyExportSort($exportQuery, $request->input('sortlistByDate'), 'sub.exportpk', 'datecreate', 'closetime', 'contdate');

        $exportpks = $exportQuery
            ->orderBy('sub.exportpk', 'desc')
            ->limit($perPage)
            ->offset($offset)
            ->pluck('exportpk');

        if ($exportpks->isEmpty()) {
            return response()->json(['total' => $total, 'rows' => []]);
        }

        $result = (clone $query)
            ->whereIn('export.exportpk', $exportpks->all())
            ->orderBy('poexport.poexportpk')
            ->get();

        $result = $this->enrichExportRows($result);
        $result = $this->enrichPoCount($result);

        $this->addOrderImageToRows($result);
        $this->addUsernameToRows($result);

        $byExport = $result->groupBy('exportpk');

        $rows = $exportpks->values()->map(function ($exportpk, $index) use ($byExport, $offset) {
            $pos = $byExport->get($exportpk, collect())->values();
            $first = $pos->first();

            $row = clone $first;
            $row->no = $offset + $index + 1;
            $row->qty = $pos->sum(fn($p) => (float) $p->qty);
            $row->po_count = $pos->count();
            $row->ops = $pos->pluck('OP')->filter()->unique()->values()->all();
            $row->ponos = $pos->pluck('POno')->filter()->unique()->values()->all();
            $row->mif = $pos->pluck('mif')
                ->flatMap(fn($m) => explode(' & ', (string) $m))
                ->filter()->unique()->sort()->implode(' & ') ?: 'MIF 2';
            $row->pos = $pos->all();

            return $row;
        });

        $this->addStuffingToRows($rows);

        return response()->json([
            'total' => $total,
            'rows' => $rows,
        ]);
    }

    // kolom "Stuffing" di list -- detail perhitungan lihat StuffingProgress
    private function addStuffingToRows($rows): void
    {
        $progress = StuffingProgress::forExports($rows->pluck('exportpk'));

        foreach ($rows as $row) {
            $p = $progress->get((int) $row->exportpk, ['loaded' => 0, 'total' => 0, 'locked' => 0]);
            $row->ctn_loaded = $p['loaded'];
            $row->ctn_total = $p['total'];
            $row->ctn_locked = $p['locked'];
        }
    }

    /**
     * Set Complete Shipment (menu titik tiga di list): hanya kalau semua carton shipment sudah masuk
     * container (stuffing 100%).
     *  1. Packing (lewat API /api/packing/complete-shipment): pack.status 6 -> 7, po.sts = 1 kalau part
     *     terakhir popk = 10.
     *  2. EXIM: semua container shipment ditandai selesai & tersegel (cont.start_ship / end_ship / segel),
     *     karena status list "Complete" (is_segel), status container "Shipped" dan kunci kartu container
     *     di detail_ship dihitung dari kolom ini. Jam diambil dari jam tutup container Security Gate
     *     (gate_close_at) kalau ada, selain itu saat ini; nilai yang sudah terisi tidak diubah.
     * Bisa dijalankan ulang (mis. data container belum ikut ter-update): carton yang sudah status 7 dilewati.
     */
    public function completeShipment(Request $request)
    {
        $v = $request->validate(['exportpk' => 'required|integer']);
        $exportpk = (int) $v['exportpk'];

        $export = DB::table('export')->where('exportpk', $exportpk)->first(['exportpk', 'stscancel']);
        if (!$export) {
            return response()->json(['message' => 'Shipment plan tidak ditemukan.'], 404);
        }
        if ((int) $export->stscancel === 1) {
            return response()->json(['message' => 'Shipment plan ini sudah dibatalkan.'], 422);
        }

        $p = StuffingProgress::forExports([$exportpk])->get($exportpk, ['loaded' => 0, 'total' => 0, 'locked' => 0]);
        if ($p['total'] === 0 || $p['loaded'] < $p['total']) {
            return response()->json([
                'message' => "Belum bisa complete: carton baru {$p['loaded']} dari {$p['total']} yang masuk container.",
            ], 422);
        }

        // container yang ditolak security harus diganti dulu (tidak boleh ditandai shipped)
        $contCols = Schema::getColumnListing('cont');
        if (in_array('gate_result', $contCols, true)
            && DB::table('cont')->where('exportpk', $exportpk)->whereNotNull('actcontdate')->where('gate_result', 0)->exists()) {
            return response()->json([
                'message' => 'Masih ada container yang ditolak security. Ganti container tersebut dulu sebelum complete shipment.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(60)
                ->post($this->packingBaseUrl() . '/api/packing/complete-shipment', [
                    'exportpk' => $exportpk,
                    'user'     => (string) Session::get('login'), // dicatat di log aktivitas Packing List
                ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Sistem Packing tidak dapat terhubung. Shipment belum di-complete.'], 502);
        }

        $body = $response->json() ?? [];
        if (!$response->successful()) {
            return response()->json(['message' => $body['message'] ?? 'Gagal set complete shipment.'], $response->status());
        }

        // container EXIM: selesai muat & tersegel (status list Complete, container Shipped, kartu terkunci)
        $closeCol = in_array('gate_close_at', $contCols, true) ? 'gate_close_at' : null;
        $sealed = 0;
        foreach (DB::table('cont')->where('exportpk', $exportpk)->get() as $c) {
            $at = ($closeCol && !empty($c->{$closeCol})) ? $c->{$closeCol} : now();
            $set = array_filter([
                'start_ship' => empty($c->start_ship) ? $at : null,
                'end_ship'   => empty($c->end_ship) ? $at : null,
                'segel'      => empty($c->segel) ? $at : null,
            ]);
            if ($set) {
                DB::table('cont')->where('contpk', $c->contpk)->update($set);
                $sealed++;
            }
        }

        $body['containers'] = $sealed;
        $body['message'] = trim(($body['message'] ?? 'Shipment complete.') . ($sealed ? " {$sealed} container ditandai shipped." : ''));

        return response()->json($body);
    }

    /**
     * Uncomplete Shipment (menu titik tiga di list): barang yang sudah dikirim dikembalikan. Kebalikan completeShipment:
     *  1. Packing (lewat API /api/packing/uncomplete-shipment): pack.status 7 -> 6, po.sts 1 -> 0 (popk part terakhir 10).
     *  2. EXIM: segel & selesai muat container dikosongkan (cont.end_ship, cont.segel) -> status list kembali dari
     *     Complete, container kembali "Start Loading" dan kartunya bisa diubah lagi. Jam mulai muat (start_ship) tetap.
     * Ditolak kalau shipment sudah di-Lock Shipment (sudah diposting ke database LPB).
     */
    public function uncompleteShipment(Request $request)
    {
        $v = $request->validate(['exportpk' => 'required|integer']);
        $exportpk = (int) $v['exportpk'];

        $export = DB::table('export')->where('exportpk', $exportpk)->first(['exportpk', 'stscancel', 'lockship']);
        if (!$export) {
            return response()->json(['message' => 'Shipment plan tidak ditemukan.'], 404);
        }
        if ((int) $export->stscancel === 1) {
            return response()->json(['message' => 'Shipment plan ini sudah dibatalkan.'], 422);
        }
        if ((int) $export->lockship === 1) {
            return response()->json(['message' => 'Shipment sudah di-Lock Shipment (data sudah diposting ke database LPB), tidak bisa di-uncomplete.'], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(60)
                ->post($this->packingBaseUrl() . '/api/packing/uncomplete-shipment', [
                    'exportpk' => $exportpk,
                    'user'     => (string) Session::get('login'), // dicatat di log aktivitas Packing List
                ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Sistem Packing tidak dapat terhubung. Shipment belum di-uncomplete.'], 502);
        }

        $body = $response->json() ?? [];
        if (!$response->successful()) {
            return response()->json(['message' => $body['message'] ?? 'Gagal uncomplete shipment.'], $response->status());
        }

        $opened = DB::table('cont')
            ->where('exportpk', $exportpk)
            ->where(fn($q) => $q->whereNotNull('segel')->orWhereNotNull('end_ship'))
            ->update(['segel' => null, 'end_ship' => null]);

        $body['containers'] = $opened;
        $body['message'] = trim(($body['message'] ?? 'Shipment uncomplete.') . ($opened ? " {$opened} container dibuka kembali." : ''));

        return response()->json($body);
    }

    // private function addOrderImageToRows($rows): void
    // {
    //     if ($rows->isEmpty()) {
    //         return;
    //     }

    //     $gisFotoBase        = rtrim(config('services.foto.gis_base'), '/');
    //     $productionFotoBase = rtrim(config('services.foto.production_base'), '/');
    //     $sampleFotoBase     = rtrim(config('services.foto.sample_base'), '/');

    //     $noImageUrl = asset('public/css/images/no-img.png');

    //     foreach ($rows as $r) {
    //         $r->order_image = $noImageUrl;
    //     }

    //     // $ordpks = $rows->pluck('ordpk')->filter()->unique()->values()->all();
    //     // if (empty($ordpks)) {
    //     //     return;
    //     // }

    //     // $ordRows = DB::connection('mysql_sig')->table('ord')
    //     //     ->whereIn('ordpk', $ordpks)
    //     //     ->get(['ordpk', 'srno', 'foto', 'foto2', 'stsfoto']);

    //     $ordpks = DB::connection('mysql_andon')->table('po')->where('OP', $r->OP)->value('ordpk');
    //     if (empty($ordpks)) {
    //         return;
    //     }

    //     $ordRows = DB::connection('mysql_sig')->table('ord')
    //         ->where('ordpk', $ordpks)
    //         ->get(['ordpk', 'srno', 'foto', 'foto2', 'stsfoto']);

    //     $ordByOrdpk = $ordRows->keyBy('ordpk');

    //     $srnos = $ordRows->pluck('srno')->filter()->unique()->values()->all();

    //     $srpkBySrno = [];
    //     $fotoBySrpk = [];

    //     if (!empty($srnos)) {
    //         $reqRows = DB::connection('mysql_sample')->table('request')
    //             ->whereIn('srno', $srnos)
    //             ->get(['srno', 'srpk']);

    //         foreach ($reqRows as $r) {
    //             $srpkBySrno[$r->srno] = $r->srpk;
    //         }

    //         $srpks = array_values(array_unique(array_values($srpkBySrno)));

    //         if (!empty($srpks)) {
    //             $statusRows = DB::connection('mysql_sample')->table('status')
    //                 ->whereIn('srpk', $srpks)
    //                 ->orderByDesc('statuspk')
    //                 ->get(['srpk', 'foto', 'statuspk']);

    //             foreach ($statusRows as $r) {
    //                 if (!isset($fotoBySrpk[$r->srpk])) {
    //                     $fotoBySrpk[$r->srpk] = $r->foto;
    //                 }
    //             }
    //         }
    //     }

    //     foreach ($rows as $r) {
    //         $ord = $ordByOrdpk->get($r->ordpk);
    //         if (!$ord) {
    //             continue;
    //         }

    //         $stsfoto = $ord->stsfoto ?? null;
    //         $foto1   = $ord->foto ?? null;
    //         $srno    = $ord->srno ?? null;

    //         $srpk  = $srno ? ($srpkBySrno[$srno] ?? null) : null;
    //         $foto2 = $srpk ? ($fotoBySrpk[$srpk] ?? null) : ($ord->foto2 ?? null);


    //         if ($stsfoto == 1) {
    //             $r->order_image = !empty($foto1) ? "{$gisFotoBase}/{$foto1}" : $noImageUrl;
    //         } elseif ($stsfoto == 2) {
    //             $r->order_image = !empty($foto1) ? "{$productionFotoBase}/{$foto1}" : $noImageUrl;
    //         } else {
    //             $r->order_image = !empty($foto2) ? "{$sampleFotoBase}/{$foto2}" : $noImageUrl;
    //         }
    //     }
    // }


    private function addOrderImageToRows($rows): void
    {
        if ($rows->isEmpty()) {
            return;
        }

        $gisFotoBase        = rtrim(config('services.foto.gis_base'), '/');
        $productionFotoBase = rtrim(config('services.foto.production_base'), '/');
        $sampleFotoBase     = rtrim(config('services.foto.sample_base'), '/');

        $noImageUrl = asset('css/images/no-img.png');

        // default semua baris: no image
        foreach ($rows as $row) {
            $row->order_image = $noImageUrl;
        }

        // ============================================================
        // 1. PETAKAN OP -> ordpk (SATU query untuk semua OP di halaman ini)
        // ============================================================
        $ops = $rows->pluck('OP')->filter()->unique()->values()->all();

        $opToOrdpk = [];
        if (!empty($ops)) {
            $opToOrdpk = DB::connection('mysql_andon')->table('po')
                ->whereIn('OP', $ops)
                ->whereNotNull('ordpk')
                ->pluck('ordpk', 'OP')   // hasil: ['26-0680' => 40078, ...]
                ->all();
        }

        // tentukan ordpk final per baris: utamakan dari mapping OP,
        // fallback ke $row->ordpk bawaan export kalau OP tidak ketemu di tabel po
        $rowOrdpk = [];
        foreach ($rows as $idx => $row) {
            $rowOrdpk[$idx] = $opToOrdpk[$row->OP] ?? $row->ordpk ?? null;
        }

        $ordpks = collect($rowOrdpk)->filter()->unique()->values()->all();
        if (empty($ordpks)) {
            return;
        }

        // ============================================================
        // 2. AMBIL DATA ord UNTUK SEMUA ordpk SEKALIGUS
        // ============================================================
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

            // pakai nama variabel loop BERBEDA supaya tidak menimpa variabel lain
            foreach ($reqRows as $req) {
                $srpkBySrno[$req->srno] = $req->srpk;
            }

            $srpks = array_values(array_unique(array_values($srpkBySrno)));

            if (!empty($srpks)) {
                $statusRows = DB::connection('mysql_sample')->table('status')
                    ->whereIn('srpk', $srpks)
                    ->orderByDesc('statuspk')
                    ->get(['srpk', 'foto', 'statuspk']);

                foreach ($statusRows as $st) {
                    if (!isset($fotoBySrpk[$st->srpk])) {
                        $fotoBySrpk[$st->srpk] = $st->foto;
                    }
                }
            }
        }

        // ============================================================
        // 3. TENTUKAN URL GAMBAR PER BARIS, berdasarkan ordpk MILIK BARIS ITU
        // ============================================================
        foreach ($rows as $idx => $row) {
            $ordpk = $rowOrdpk[$idx] ?? null;
            if (!$ordpk) {
                continue;
            }

            $ord = $ordByOrdpk->get($ordpk);
            if (!$ord) {
                continue;
            }

            $stsfoto = $ord->stsfoto ?? null;
            $foto1   = $ord->foto ?? null;
            $srno    = $ord->srno ?? null;

            $srpk  = $srno ? ($srpkBySrno[$srno] ?? null) : null;
            $foto2 = $srpk ? ($fotoBySrpk[$srpk] ?? null) : ($ord->foto2 ?? null);

            if ($stsfoto == 1) {
                $row->order_image = !empty($foto1) ? "{$gisFotoBase}/{$foto1}" : $noImageUrl;
            } elseif ($stsfoto == 2) {
                $row->order_image = !empty($foto1) ? "{$productionFotoBase}/{$foto1}" : $noImageUrl;
            } else {
                $row->order_image = !empty($foto2) ? "{$sampleFotoBase}/{$foto2}" : $noImageUrl;
            }
        }
    }
    private function addUsernameToRows($rows)
    {
        $userpks = collect($rows)->pluck('userpk')->filter()->unique()->values();

        if ($userpks->isEmpty()) {
            return;
        }

        // ambil baris LENGKAP (login + guserpk), lalu keyBy userpk supaya gampang di-lookup
        $users = DB::connection('mysql_akses')
            ->table('user')
            ->whereIn('userpk', $userpks)
            ->select('userpk', 'guserpk', 'login')
            ->get()
            ->keyBy('userpk'); // hasil: [userpk => {userpk, guserpk, login}, ...]

        foreach ($rows as $row) {
            $user = $users[$row->userpk] ?? null;

            $row->login = $user->login ?? null;
            $row->guserpk = $user->guserpk ?? null;
        }
    }

    public function removeList(Request $request)
    {
        $validated = $request->validate([
            'exportpk' => 'required|integer|exists:export,exportpk',
        ]);

        // dd($validated['exportpk']);

        // kalau masih ada carton yang di-load di container, tidak boleh cancel (sama seperti deletePo)
        $loaded = DB::connection('mysql_andon')->table('pack')
            ->where('exportpk', $validated['exportpk'])
            ->whereNotNull('contpk')
            ->selectRaw('contpk, COUNT(*) AS total')
            ->groupBy('contpk')
            ->pluck('total', 'contpk');

        if ($loaded->isNotEmpty()) {
            $contnos = Container::whereIn('contpk', $loaded->keys())->pluck('contno', 'contpk');
            $names = $loaded->keys()
                ->map(fn($contpk) => ($contnos[$contpk] ?? null) ?: ('#' . $contpk))
                ->implode(', ');

            return response()->json([
                'message' => "Masih ada {$loaded->sum()} carton yang sudah di-load di container ({$names}). "
                    . 'Unload dulu carton dari container, baru shipment bisa di-cancel.'
            ], 422);
        }

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

            // Soft cancel: exportdt & poexport TIDAK dihapus supaya data yang
            // di-cancel tetap tampil di list dengan status "Cancelled".
            Export::where('exportpk', $validated['exportpk'])->update([
                'stscancel'      => 1,
            ]);

            DB::commit();

            return response()->json(['message' => 'Shipment berhasil di-cancel.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Gagal cancel shipment.'], 500);
        }
    }

    private function packingBaseUrl(): string
    {
        return rtrim(config('services.packing.url'), '/');
    }

    public function detail_ship($exportpk)
    {
        $header = DB::table('export')
            ->leftJoin('vassal', 'vassal.vassalpk', 'export.vassalpk')
            ->leftJoin('plb', 'plb.plbpk', 'export.plbpk')
            // ->leftJoin('fwd', 'fwd.fwdpk', 'export.fwdpk')
            ->where('exportpk', $exportpk)
            ->first();

        if (!$header) {
            abort(404, 'Data export tidak ditemukan.');
        }

        $dtcont = DB::table('cont')->where('exportpk', $exportpk)->whereNotNull('start_ship')->count();
        $header->lockship = $dtcont ?? 0;

        $header->guserpk = DB::connection('mysql_akses')->table('user')->where('userpk', $header->userpk)->value('guserpk');

        $header->ordno = implode(', ', array_unique(
            array_map('trim', explode(',', $header->OP))
        ));

        $header->exdate = !empty($header->exdate)
            ? \Carbon\Carbon::parse($header->exdate)->format('d/m/Y H:i:s')
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

        //untuk perhitungan 
        $total = $this->getContainerSummary((int) $exportpk);

        // Untuk container
        $container = DB::table('cont')
            ->leftJoin('fwd', 'fwd.fwdpk', 'cont.fwdpk')
            ->where('cont.exportpk', $exportpk)
            ->get();

        // riwayat container yang ditolak security lalu diganti (lihat replaceContainer)
        $gateHistory = Schema::hasTable(GateController::HISTORY_TABLE)
            ? DB::table(GateController::HISTORY_TABLE)->whereIn('contpk', $container->pluck('contpk'))->orderBy('histpk')->get()->groupBy('contpk')
            : collect();

        $loadedCartons = collect();

        foreach ($container as $c) {
            $c->is_rejected = !empty($c->actcontdate) && isset($c->gate_result) && (int) $c->gate_result === 0;
            $c->gate_history = $gateHistory->get($c->contpk, collect());

            $cartonsForThisContainer = $this->getLoadedCartonsFromPacking(
                (int) $exportpk,
                (int) $c->contpk
            );

            if (!empty($c->start_ship) && empty($c->end_ship) && empty($c->segel)) {
                $c->loading_status = 'start';
            } elseif (!empty($c->start_ship) && !empty($c->end_ship) && empty($c->segel)) {
                $c->loading_status = 'finish';
            } elseif (!empty($c->start_ship) && !empty($c->end_ship) && !empty($c->segel)) {
                $c->loading_status = 'shipped';
            } else {
                $c->loading_status = 'plan';
            }

            $loadedCartons->put(
                $c->contpk,
                $cartonsForThisContainer
            );
        }

        return view(
            'menu.exim-export.shipplan.detail_ship',
            compact('header', 'container', 'exportpk', 'loadedCartons', 'total') + [
                // mode lihat saja kalau shipment dibuat grup lain (kecuali Manager EXIM), lihat ShipplanAccess
                'canEdit' => \App\Services\ShipplanAccess::canEdit($exportpk),
            ]
        );
    }

    public function getContainerSummary($exportpk, $contpk = null)
    {
        //  query LANGSUNG ke mysql_andon
        // (database Packing). SEKARANG delegasikan ke API Packing.
        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(15)
                ->get($this->packingBaseUrl() . '/api/packing/container-summary', [
                    'exportpk' => $exportpk,
                    'contpk'   => $contpk,
                ]);

            if (!$response->successful()) {
                report(new \Exception('apiContainerSummary gagal: ' . $response->body()));
                return ['totqty' => 0, 'totctn' => 0, 'totnw' => 0, 'totgw' => 0, 'totvol' => 0];
            }

            return $response->json();
        } catch (\Throwable $e) {
            report($e);
            return ['totqty' => 0, 'totctn' => 0, 'totnw' => 0, 'totgw' => 0, 'totvol' => 0];
        }
    }

    public function set_shipplan(Request $request)
    {
        $gac = $request->input('GAC');

        if (empty($gac) || !strtotime($gac)) {
            $request->merge([
                'GAC' => null
            ]);
        }
        
        $validated = $request->validate([
            'ordpk'   => 'required|string',
            'buyer'   => 'nullable|string',
            'POno'    => 'nullable|string',
            'OP'      => 'nullable|string',
            'poref'   => 'nullable|string',
            'factory' => 'nullable|string',
            'popks'   => 'nullable|string',
            'GAC'     => 'nullable|date',
            'contdate'  => 'required|date',
            'etd'     => 'required|date|after_or_equal:exdate',
            'vassal'  => 'required|string',
            'totqty'  => 'required|numeric',
            'qty_list' => 'nullable|string',
            // 'totctn'  => 'required|numeric',
        ]);

        $userpk = Session::get('userpk');

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
                // 'totqty'     => $validated['totqty'],
                'qty_list'   => $validated['qty_list'],
                'ordpk'      => $ordpks->implode(','),
                'GAC'        => $validated['GAC'],
                'contdate'   => $validated['contdate'],
                'etdvess'    => $validated['etd'],
                'vassalpk'   => $validated['vassal'],
                'popks'      => $validated['popks'],
                'userpk'     => $userpk ?? null,
                'datecreate' => now()
            ]);

            $selectedRows = json_decode($request->selectedRows, true);
            $poexports = [];

            foreach ($selectedRows as $row) {
                $poexports[] = [
                    'exportpk' => $exportpk,
                    'POno'     => $row['POno'],
                    'OP'       => $row['OP'],
                    'poref'    => $row['poref'],
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

    public function set_peb(Request $request)
    {
        $validated = $request->validate([
            'poexportpk' => 'required|string',
            'pebno'       => 'nullable|string',
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
                    'pebno' => $validated['pebno'] ?: null,
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

    // ekspedisi = perusahaan angkutan (tabel ang) -> cont.angpk; kolom opsional (belum ada di semua database):
    // baca & simpan hanya kalau sudah dibuat
    private function ekspedisiReady(): bool
    {
        static $ready = null;
        return $ready ?? ($ready = Schema::hasColumn('cont', 'angpk'));
    }

    // pilihan ekspedisi di modal edit shipment plan dari tabel ang (nama dirapikan, kolom char berisi spasi)
    public function getEkspedisi()
    {
        $data = DB::table('ang')
            ->selectRaw('angpk, TRIM(angnm) AS angnm')
            ->whereRaw("TRIM(IFNULL(angnm, '')) <> ''")
            ->orderByRaw('TRIM(angnm) ASC')
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
            ['value' => '0', 'text' => 'All Time'],
            ['value' => '1', 'text' => 'Hari ini'],
            ['value' => '2', 'text' => 'Minggu ini'],
            ['value' => '3', 'text' => 'Minggu depan'],
            ['value' => '4', 'text' => '2 Minggu depan'],
            ['value' => '5', 'text' => 'Bulan kemarin'],
            ['value' => '6', 'text' => 'Bulan ini'],
            ['value' => '7', 'text' => 'Bulan depan'],
            ['value' => '8', 'text' => '3 Bulan depan'],
            ['value' => '9', 'text' => '6 Bulan depan'],
            ['value' => '10', 'text' => 'Tahun ini'],
        ];
        return response()->json($data);
    }

    // Rentang tanggal untuk tiap value filterday(); null = tanpa filter (All Time)
    private function dayRange($value): ?array
    {
        $today = Carbon::today();

        switch ((int) $value) {
            case 1: // Hari ini
                return [$today->copy(), $today->copy()->endOfDay()];
            case 2: // Minggu ini
                return [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
            case 3: // Minggu depan
                return [$today->copy()->addWeek()->startOfWeek(), $today->copy()->addWeek()->endOfWeek()];
            case 4: // 2 Minggu depan (hari ini s/d 2 minggu ke depan)
                return [$today->copy(), $today->copy()->addWeeks(2)->endOfDay()];
            case 5: // Bulan kemarin
                return [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()];
            case 6: // Bulan ini
                return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];
            case 7: // Bulan depan
                return [$today->copy()->addMonthNoOverflow()->startOfMonth(), $today->copy()->addMonthNoOverflow()->endOfMonth()];
            case 8: // 3 Bulan depan (hari ini s/d 3 bulan ke depan)
                return [$today->copy(), $today->copy()->addMonthsNoOverflow(3)->endOfDay()];
            case 9: // 6 Bulan depan (hari ini s/d 6 bulan ke depan)
                return [$today->copy(), $today->copy()->addMonthsNoOverflow(6)->endOfDay()];
            case 10: // Tahun ini
                return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
            default: // 0 / kosong = All Time
                return null;
        }
    }

    private function applyDayRangeFilter($query, string $column, $value): void
    {
        $range = $this->dayRange($value);
        if ($range) {
            $query->whereBetween($column, [$range[0]->toDateTimeString(), $range[1]->toDateTimeString()]);
        }
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
            $totalContainer = $this->storeContainers($data, $request);

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
            ? Carbon::createFromFormat('d/m/Y H:i', $data['exdate'])->format('Y-m-d H:i')
            : null;

        $closetime = $data['closetime']
            ? Carbon::createFromFormat('d/m/Y H:i', $data['closetime'])->format('Y-m-d H:i')
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

        // $actcontdate = $data['actcontdate']
        //     ? Carbon::createFromFormat('d/m/Y', $data['actcontdate'])->format('Y-m-d')
        //     : null;

        Export::where('exportpk', $data['exportpk'])->update([
            'etdvess'       => $etdvess ?? null,
            'etavess'       => $etavess ?? null,
            'exdate'        => $exdate ?? null,
            'dateupdt'      => now(),
            'pebno'         => $data['pebno'] ?? null,
            'nobook'        => $data['nobook'] ?? null,
            // 'totqty'        => $data['totqty'] ?? null,
            // 'totctn'        => $data['totctn'] ?? null,
            'ukctn'         => $data['ukctn'] ?? null,
            'totvol'        => $data['totvol'] ?? null,
            'totnw'         => $data['totnw'] ?? null,
            'totgw'         => $data['totgw'] ?? null,
            'vassalpk'      => $data['vassal'] ?? null,
            'plbpk'         => $data['plb'] ?? null,
            'contdate'      => $contdate  ?? null,
            // 'actcontdate'   => $actcontdate   ?? null,
            'closetime'     => $closetime ?? null,
            // 'vessname'      => $data['containers'][0]['vessel_name'] ?? null,
            'remark'        => $data['remark'] ?? null,
            // 'inv'           => $data['invno'] ?? null,
        ]);
    }

    /**
     * Mapping slot foto di form -> kolom di tabel cont.
     * foto1 memakai kolom `foto` (nama lama), sisanya foto2..foto5.
     */
    private const FOTO_COLUMNS = [
        'foto1' => 'foto',
        'foto2' => 'foto2',
        'foto3' => 'foto3',
        'foto4' => 'foto4',
        'foto5' => 'foto5',
    ];

    /** Hapus file foto lama dari disk kalau memang ada. */
    private function deleteFotoFile(string $folder, ?string $filename): void
    {
        if (!$filename) {
            return;
        }

        $path = $folder . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Cek apakah container masih punya foto setelah payload ini diterapkan.
     * Kolom yang tidak ikut diubah dibaca dari row lamanya.
     */
    private function hasAnyFoto(array $payload, ?Container $existingRow): bool
    {
        foreach (self::FOTO_COLUMNS as $column) {
            $value = array_key_exists($column, $payload)
                ? $payload[$column]
                : ($existingRow->{$column} ?? null);

            if (!empty($value)) {
                return true;
            }
        }

        return false;
    }

    private function storeContainers(array $data, Request $request): int
    {
        // Entri tanpa contpk & tanpa field card sama sekali (mis. hanya berisi
        // foto dari index card yang sudah dihapus) bukan container -> abaikan.
        $containers = array_filter($data['containers'] ?? [], function ($c) {
            return is_array($c) && (
                !empty($c['contpk']) || array_key_exists('type', $c) || array_key_exists('contno', $c)
            );
        });
        $totalContainer = count($containers);

        // Container baru WAJIB punya tipe; container lama yang tipenya kosong
        // cukup tidak diubah tipenya.
        $errors = [];
        foreach ($containers as $index => $c) {
            if (empty($c['type']) && empty($c['contpk'])) {
                $errors["containers.{$index}.type"] = ['Tipe container #' . ($index + 1) . ' wajib dipilih.'];
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        // Container lama yang card-nya dihapus di form tidak ikut terkirim -> hapus dari DB.
        $this->deleteRemovedContainers($data['exportpk'], $containers);

        $insertRows = [];

        foreach ($containers as $index => $c) {
            $type = $c['type'] ?? null;

            if ($type == '20ft') {
                $cbm = 33;
            } else if ($type == '40ft') {
                $cbm = 67;
            } else if ($type == '40hc') {
                $cbm = 76;
            } else if ($type == '45ft') {
                $cbm = 86;
            }else {
                // LCL: volume diisi manual (boleh desimal, mis. 10.13)
                $cbm = isset($c['volume']) && $c['volume'] !== '' ? (float) $c['volume'] : null;
            }

            // nama field dari form adalah containers[i][actcontdate]
            $actcontdate = !empty($c['actcontdate'])
                ? Carbon::createFromFormat('d/m/Y H:i', $c['actcontdate'])->format('Y-m-d H:i')
                : null;

            $payload = [
                'exportpk'      => $data['exportpk'],
                'contno'        => $c['contno'] ?? null,
                // no. truck diisi EXIM (security hanya melihat): huruf kapital, spasi tunggal
                'nopol'         => isset($c['nopol']) && trim($c['nopol']) !== '' ? strtoupper(preg_replace('/\s+/', ' ', trim($c['nopol']))) : null,
                'noseal'        => $c['noseal'] ?? null,
                'actcontdate'   => $actcontdate,
                'fwdpk'         => $c['fwd'] ?? $data['fwd'] ?? null,

                'vessname'      => $c['vessname'] ?? null,
                'remark'        => null,
            ];

            // ekspedisi dipilih di modal (sama untuk semua container shipment, seperti forwarder)
            if ($this->ekspedisiReady()) {
                $payload['angpk'] = ($c['ekspedisi'] ?? $data['ekspedisi'] ?? null) ?: null;
            }

            if (!empty($type)) {
                $payload['type']   = $type;
                $payload['volume'] = $cbm;
            }

            // jam tiba bisa diisi / diedit EXIM di modal (juga diisi security saat gate in);
            // dikosongkan di modal = jam tiba yang sudah ada tetap (data gate in security tidak terhapus)
            if (!empty($c['contpk']) && empty($payload['actcontdate'])) {
                unset($payload['actcontdate']);
            }

            // ================= HANDLE UPLOAD FOTO (5 SLOT) =================
            // tiap container punya 5 slot foto. slot foto1 disimpan di kolom `foto`
            // (nama kolom lama, dipertahankan supaya data existing tetap terbaca),
            // slot berikutnya di kolom foto2..foto5.
            // file diakses lewat $request->file(), BUKAN dari $data (array biasa tidak bisa bawa file)
            $folder = public_path('upload/foto_container');
            $existingContpk = $c['contpk'] ?? null;

            // ambil sekali saja, dipakai untuk hapus file lama sekaligus cek sisa foto di akhir
            $existingRow = $existingContpk
                ? Container::where('contpk', $existingContpk)
                ->first(array_values(self::FOTO_COLUMNS))
                : null;

            $fotoUploaded = false; // ada minimal 1 slot yang diisi foto baru
            $fotoRemoved  = false; // ada minimal 1 slot yang sengaja dikosongkan

            foreach (self::FOTO_COLUMNS as $field => $column) {
                $file    = $request->file("containers.{$index}.{$field}");
                $removed = $request->input("containers.{$index}.{$field}_removed");

                if ($file) {
                    if (!file_exists($folder)) {
                        mkdir($folder, 0755, true);
                    }

                    // hapus file lama di slot yang sama dulu, supaya tidak menumpuk di disk
                    $this->deleteFotoFile($folder, $existingRow->{$column} ?? null);

                    // nama file harus mengandung slot: tanpa itu, 5 foto dengan nama asli
                    // yang sama (mis. "image.jpg" dari kamera) akan saling menimpa
                    $filename = ($existingContpk ?: 'new') . '_' . $field . '_' . time() . '_'
                        . preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());

                    $file->move($folder, $filename);

                    $payload[$column] = $filename;
                    $fotoUploaded = true;
                } elseif ($removed) {
                    $this->deleteFotoFile($folder, $existingRow->{$column} ?? null);

                    $payload[$column] = null;
                    $fotoRemoved = true;
                }
            }

            // container dianggap tersegel begitu ada foto yang masuk, dan baru dibatalkan
            // kalau SEMUA slot fotonya berakhir kosong (bukan cuma salah satu yang dihapus)
            $fotoLeft = $this->hasAnyFoto($payload, $existingRow);

            if ($fotoUploaded) {
                $payload['segel'] = now();
                $payload['end_ship'] = now();
            } elseif ($fotoRemoved && !$fotoLeft) {
                $payload['segel'] = null;
                $payload['end_ship'] = null;
            }

            $contpk = $c['contpk'] ?? null;

            if (!empty($contpk)) {
                // sudah ada contpk -> UPDATE row yang sudah ada
                Container::where('contpk', $contpk)->update($payload);
                if ($fotoUploaded) {
                    DB::connection('mysql_andon')->table('pack')->where('contpk', $contpk)->update(['status' => 7]);
                } elseif ($fotoRemoved && !$fotoLeft) {
                    DB::connection('mysql_andon')->table('pack')->where('contpk', $contpk)->update(['status' => 6]);
                }
            } else {
                // belum ada contpk -> kumpulkan dulu, insert sekaligus di akhir (lebih efisien)
                $insertRows[] = $payload;
            }
        }

        if (!empty($insertRows)) {
            // insert massal butuh kolom yang sama di setiap baris (foto/segel
            // bisa hanya ada di sebagian card) -> lengkapi dengan null.
            $columns = array_fill_keys(array_unique(array_merge(...array_map('array_keys', $insertRows))), null);
            $insertRows = array_map(fn($row) => array_merge($columns, $row), $insertRows);

            Container::insert($insertRows);
        }

        return $totalContainer;
    }

    /**
     * Hapus container milik export ini yang contpk-nya tidak ada lagi di form.
     * Container yang masih berisi carton ditolak, user harus unload dulu.
     */
    private function deleteRemovedContainers($exportpk, array $containers): void
    {
        $keptContpks = array_values(array_filter(array_column($containers, 'contpk')));

        $removed = Container::where('exportpk', $exportpk)
            ->when($keptContpks, fn($q) => $q->whereNotIn('contpk', $keptContpks))
            ->get(array_merge(['contpk', 'contno'], array_values(self::FOTO_COLUMNS)));

        if ($removed->isEmpty()) {
            return;
        }

        $loaded = DB::connection('mysql_andon')->table('pack')
            ->whereIn('contpk', $removed->pluck('contpk'))
            ->distinct()
            ->pluck('contpk');

        if ($loaded->isNotEmpty()) {
            $names = $removed->whereIn('contpk', $loaded->all())
                ->map(fn($r) => $r->contno ?: '#' . $r->contpk)
                ->implode(', ');

            throw ValidationException::withMessages([
                'containers' => ['Container ' . $names . ' masih berisi carton. Unload carton terlebih dahulu sebelum menghapus container.'],
            ]);
        }

        $folder = public_path('upload/foto_container');
        foreach ($removed as $row) {
            foreach (self::FOTO_COLUMNS as $column) {
                $this->deleteFotoFile($folder, $row->{$column});
            }
        }

        Container::whereIn('contpk', $removed->pluck('contpk'))->delete();
    }

    /**
     * Ganti container yang ditolak security (cont.gate_result = 0).
     * Carton yang sudah dimuat dikeluarkan dulu (kembali belum dimuat).
     * Data pemeriksaan container lama dipindah ke cont_gate_history, lalu baris cont
     * yang sama dikosongkan untuk container pengganti. contpk tidak berubah, jadi
     * packing, stuffing, SI/CLP, dan notifikasi tetap memakai baris yang sama.
     * Tipe, volume, forwarder & vessel dipertahankan; security memeriksa ulang saat tiba.
     */
    public function replaceContainer(Request $request, $contpk)
    {
        $v = $request->validate(['contno' => 'nullable|string|max:100']);

        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        if (!$cont) {
            return response()->json(['success' => false, 'message' => 'Container tidak ditemukan.'], 404);
        }
        if (!isset($cont->gate_result) || (int) $cont->gate_result !== 0) {
            return response()->json(['success' => false, 'message' => 'Hanya container yang ditolak security yang bisa diganti.'], 422);
        }
        if (!Schema::hasTable(GateController::HISTORY_TABLE)) {
            return response()->json(['success' => false, 'message' => 'Tabel ' . GateController::HISTORY_TABLE . ' belum dibuat. Hubungi IT.'], 503);
        }
        // carton yang sudah dimuat ke container lama dikeluarkan dulu lewat API Packing
        // (pack.exportpk, contpk, part -> null), sama seperti tombol "Keluarkan semua carton".
        // Carton status >= 6 (shipped/tersegel) tidak boleh dikeluarkan, sama seperti di UI.
        $packs = DB::connection('mysql_andon')->table('pack')->where('contpk', $cont->contpk)->get(['packpk', 'status']);
        if ($packs->contains(fn($p) => (int) $p->status >= 6)) {
            return response()->json([
                'success' => false,
                'message' => 'Ada carton di container ini yang sudah berstatus shipped, jadi tidak bisa dikeluarkan otomatis. Periksa data container ini dulu.',
            ], 422);
        }
        $unloadMessage = '';
        if ($packs->isNotEmpty()) {
            $unload = $this->callUnloadApi([
                'packpk'   => $packs->pluck('packpk')->map(fn($v) => (int) $v)->all(),
                'exportpk' => (int) $cont->exportpk,
                'contpk'   => (int) $cont->contpk,
            ]);
            $unloadBody = $unload->getData(true);
            if (!$unload->isSuccessful() || ($unloadBody['success'] ?? true) === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Carton gagal dikeluarkan dari container lama: ' . ($unloadBody['message'] ?? 'Sistem Packing error.') . ' Container belum diganti.',
                ], $unload->isSuccessful() ? 500 : $unload->status());
            }
            $unloadMessage = ($unloadBody['message'] ?? '') . ' ';
        }

        $gateCols = GateController::GATE_COLUMNS;
        // tanda tangan & data muat ikut diarsipkan kalau kolomnya sudah ada di kedua table
        $histCols = Schema::getColumnListing(GateController::HISTORY_TABLE);
        $contCols = Schema::getColumnListing('cont');
        foreach (array_merge([GateController::SIGN_COLUMN, GateController::IN_GUARDS_COLUMN], GateController::STUFF_COLUMNS) as $col) {
            if (in_array($col, $contCols, true) && in_array($col, $histCols, true)) {
                $gateCols[] = $col;
            }
        }
        $history = ['replaced_at' => now(), 'replaced_userpk' => Session::get('userpk')];
        foreach (array_merge(['contpk', 'exportpk', 'contno', 'type', 'nopol', 'noseal', 'actcontdate'], $gateCols) as $col) {
            $history[$col] = $cont->{$col} ?? null;
        }
        // jejak edit hanya ada di cont (tidak diarsipkan), tetap dikosongkan untuk container pengganti
        $editCols = array_values(array_intersect(GateController::EDIT_COLUMNS, $contCols));
        $reset = array_fill_keys(array_merge(['nopol', 'noseal', 'actcontdate'], $gateCols, $editCols), null);
        $reset['contno'] = !empty($v['contno']) ? strtoupper(preg_replace('/\s+/', '', $v['contno'])) : null;

        DB::transaction(function () use ($cont, $history, $reset) {
            DB::table(GateController::HISTORY_TABLE)->insert($history);
            DB::table('cont')->where('contpk', $cont->contpk)->update($reset);
        });

        return response()->json([
            'success' => true,
            'message' => 'Container diganti. ' . $unloadMessage . 'Security akan memeriksa container pengganti saat tiba.',
        ]);
    }

    // public function available_cartons($exportpk, $contpk)
    // {
    //     $filterPo = request('filter_po');
    //     $filterOrdno = request('filter_ordno');

    //     // ============================================================
    //     // GET EXPORT
    //     // ============================================================
    //     $export = DB::table('export')
    //         ->where('exportpk', $exportpk)
    //         ->first();

    //     if (!$export) {
    //         return response()->json([
    //             'total' => 0,
    //             'rows'  => [],
    //         ]);
    //     }

    //     // ============================================================
    //     // SELECT
    //     // ============================================================
    //     $select = [
    //         'pack.packpk',
    //         'pack.popk',
    //         'pack.carton',
    //         'pack.bundlepk',       // BARU
    //         'cb.bundle_carton',    // BARU -- nomor carton besar hasil bundling
    //         'pack.POno',
    //         'pack.OP',
    //         'pack.status',
    //         'pack.exportpk',
    //         'pack.material',
    //         'pack.part',
    //     ];
    //     for ($i = 1; $i <= 40; $i++) {
    //         $select[] = "po.size{$i}";
    //         $select[] = "pack.qtyp{$i}";
    //     }

    //     // ============================================================
    //     // QUERY
    //     // ============================================================
    //     $popks = $export->popks;
    //     if (!is_array($popks)) {
    //         $popks = explode(',', $popks);
    //     }

    //     $query = DB::connection('mysql_andon')
    //         ->table('pack')
    //         ->leftJoin('po', 'po.popk', '=', 'pack.popk')
    //         ->leftJoin('carton_bundle as cb', 'cb.bundlepk', '=', 'pack.bundlepk') // BARU
    //         ->whereIn('pack.popk', $popks);

    //     // ============================================================
    //     // SEARCH CARTON
    //     // ============================================================
    //     if ($filterPo) {
    //         $query->where('po.POno', $filterPo);
    //     }

    //     if ($filterOrdno) {
    //         $query->where('po.OP', $filterOrdno);
    //     }

    //     if ($search = request('q')) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('pack.carton', 'like', "%{$search}%")
    //                 ->orWhere('cb.bundle_carton', 'like', "%{$search}%"); // BARU -- pencarian juga cocok ke nomor bundle
    //         });
    //     }

    //     // ============================================================
    //     // GET PACK
    //     // ============================================================
    //     $packs = $query
    //         ->select($select)
    //         ->get();

    //     // ============================================================
    //     // GROUP BY CARTON (atau BUNDLE kalau bundlepk terisi)
    //     // ============================================================
    //     $grouped = $packs->groupBy(function ($pack) {
    //         // kalau bundlepk ada -> group HANYA berdasarkan bundlepk (tanpa POno/OP),
    //         // karena bundle akan tampil sebagai 1 carton walau small carton-nya
    //         // berasal dari PO/OP yang berbeda-beda.
    //         if (!empty($pack->bundlepk)) {
    //             return 'BUNDLE|' . $pack->bundlepk;
    //         }
    //         // kalau tidak ada bundlepk -> group normal seperti biasa, per nomor carton asli
    //         return $pack->carton . '|' . $pack->POno . '|' . $pack->OP . '|' . ($pack->part ?? '');
    //     });

    //     $rows = [];

    //     // ============================================================
    //     // LOOP CARTON / BUNDLE
    //     // ============================================================
    //     foreach ($grouped as $groupKey => $cartonPacks) {

    //         $details = [];
    //         $packpks = [];
    //         $pcsp = 0;
    //         $smallCartons = []; // daftar nomor carton kecil asli, khusus kalau ini bundle

    //         $firstPack = $cartonPacks->first();
    //         $isBundle = !empty($firstPack->bundlepk);

    //         // ========================================================
    //         // LOOP PACK DALAM CARTON / BUNDLE
    //         // ========================================================
    //         foreach ($cartonPacks as $pack) {
    //             if (!empty($pack->packpk)) {
    //                 $packpks[] = $pack->packpk;
    //             }

    //             // simpan carton kecil beserta POno & OP masing-masing. Key digabung
    //             // carton+POno+OP (bukan carton saja) supaya nomor carton yang sama
    //             // tapi berasal dari POno/OP berbeda TETAP jadi baris terpisah,
    //             // tidak saling menimpa.
    //             if ($isBundle && !empty($pack->carton)) {
    //                 $smallCartonKey = $pack->carton . '|' . $pack->POno . '|' . $pack->OP;
    //                 $smallCartons[$smallCartonKey] = [
    //                     'carton' => $pack->carton,
    //                     'POno'   => $pack->POno,
    //                     'OP'     => $pack->OP,
    //                 ];
    //             }

    //             for ($i = 1; $i <= 40; $i++) {
    //                 $size = $pack->{'size' . $i};
    //                 $qtyp = $pack->{'qtyp' . $i};

    //                 if ($size === null || $size === '') continue;
    //                 if ($qtyp === null || (float) $qtyp <= 0) continue;

    //                 $qty = (float) $qtyp;
    //                 $pcsp += $qty;

    //                 $details[] = [
    //                     'material' => $pack->material,
    //                     'size'     => $size,
    //                     'qty'      => $qty,
    //                     'packpk'   => $pack->packpk,
    //                     'carton'   => $pack->carton,
    //                     'POno'     => $pack->POno, // per detail juga disertakan
    //                     'OP'       => $pack->OP,
    //                 ];
    //             }
    //         }

    //         $packpks = array_values(array_unique($packpks));

    //         // karena $smallCartons sekarang keyed by carton (mencegah duplikat otomatis), tinggal ambil values
    //         $smallCartons = array_values($smallCartons);

    //         // urutkan berdasarkan nomor carton kecil secara natural
    //         usort($smallCartons, fn($a, $b) => strnatcasecmp((string) $a['carton'], (string) $b['carton']));

    //         if ($pcsp <= 0) {
    //             continue;
    //         }

    //         // nomor carton yang DITAMPILKAN: bundle_carton kalau bundle, carton asli kalau normal
    //         $displayCarton = $isBundle ? $firstPack->bundle_carton : $firstPack->carton;

    //         $rows[] = [
    //             'packpk'         => $packpks,
    //             'exportpk'       => $exportpk,
    //             'contpk'         => $contpk,
    //             'popk'           => $firstPack->popk,
    //             'carton'         => $displayCarton,
    //             'is_bundle'      => $isBundle,
    //             'bundle_cartons' => $isBundle ? $smallCartons : [], // berisi [{carton, POno, OP}, ...]
    //             'status'         => $firstPack->status,
    //             'exportdtpk'     => !empty($firstPack->exportpk) ? 1 : 0,
    //             'part'           => $firstPack->part,
    //             'POno'           => $firstPack->POno,
    //             'OP'             => $firstPack->OP,
    //             'pcsp'           => $pcsp,
    //             'details'        => $details,
    //         ];
    //     }

    //     // ============================================================
    //     // SORT CARTON
    //     // ============================================================
    //     usort(
    //         $rows,
    //         function ($a, $b) {
    //             return strnatcasecmp(
    //                 (string) $a['carton'],
    //                 (string) $b['carton']
    //             );
    //         }
    //     );

    //     // ============================================================
    //     // RESPONSE
    //     // ============================================================
    //     return response()->json([
    //         'total' => count($rows),
    //         'rows'  => $rows,
    //     ]);
    // }

    public function available_cartons($exportpk, $contpk)
    {
        $filterPo = request('filter_po');
        $filterOrdno = request('filter_ordno');
        $filterColor = trim((string) request('filter_color', ''));
        $search = request('q');

        // 'export' adalah tabel milik EXIM sendiri -- ini SATU-SATUNYA akses
        // database langsung yang boleh dilakukan di sini.
        $export = DB::table('export')
            ->where('exportpk', $exportpk)
            ->first();

        if (!$export) {
            return response()->json(['total' => 0, 'rows' => []]);
        }

        $popks = $export->popks;
        if (!is_array($popks)) {
            $popks = explode(',', $popks);
        }
        $popks = collect($popks)->map(fn($v) => (int) trim($v))->filter()->unique()->values();

        if ($popks->isEmpty()) {
            return response()->json(['total' => 0, 'rows' => []]);
        }

        //  method ini query LANGSUNG ke tabel
        // pack/po/carton_bundle -- itu database milik sistem PACKING, bukan
        // EXIM. SEKARANG delegasikan ke API Packing (SAMA pola dengan
        // load_container()/unload_carton() yang sudah lebih dulu benar),
        // Packing yang mengerjakan query & grouping-nya sendiri di
        // database-nya sendiri.
        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(15)
                ->get($this->packingBaseUrl() . '/api/packing/available-cartons', [
                    'popks'        => $popks->implode(','),
                    'exportpk'     => $exportpk,
                    'contpk'       => $contpk,
                    'filter_po'    => $filterPo,
                    'filter_ordno' => $filterOrdno,
                    'q'            => $search,
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                return response()->json([
                    'total'   => 0,
                    'rows'    => [],
                    'message' => $body['message'] ?? 'Gagal memuat daftar carton dari sistem Packing.',
                ], $response->status());
            }

            $data = $response->json() ?? [];
            $rows = collect($data['rows'] ?? []);

            // color (material) per carton diambil dari details[] -- carton Mix/Bundle
            // bisa berisi lebih dari 1 color
            $colorsOf = fn($row) => collect($row['details'] ?? [])
                ->pluck('material')
                ->push($row['material'] ?? null)
                ->map(fn($v) => trim((string) $v))
                ->filter()
                ->unique()
                ->values();

            // opsi dropdown color diambil SEBELUM filter color diterapkan
            $data['colors'] = $rows->flatMap($colorsOf)->unique()->sort()->values()->all();

            if ($filterColor !== '') {
                $rows = $rows->filter(fn($row) => $colorsOf($row)->contains($filterColor))->values();
                $data['rows'] = $rows->all();
                $data['total'] = $rows->count();
            }

            return response()->json($data);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'total'   => 0,
                'rows'    => [],
                'message' => 'Sistem Packing tidak dapat terhubung.',
            ], 502);
        }
    }

    public function load_container(Request $request)
    {
        $validated = $request->validate([
            'contpk'   => 'required|integer',
            'exportpk' => 'required|integer',
            'ordpk'    => 'required|integer',
            'packpk'   => 'required|array|min:1',
            'packpk.*' => 'integer',
        ]);

        $cont = DB::table('cont')->where('contpk', $validated['contpk'])->first();
        if ($cont && isset($cont->gate_result) && (int) $cont->gate_result === 0) {
            return response()->json([
                'message' => 'Container ini ditolak security dan tidak boleh dimuat. Ganti container terlebih dahulu.',
            ], 422);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(60) //
                ->post($this->packingBaseUrl() . '/api/packing/load-carton-to-container', [
                    'contpk'   => $validated['contpk'],
                    'exportpk' => $validated['exportpk'],
                    'packpk'   => $validated['packpk'],
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                return response()->json([
                    'message' => $body['message'] ?? 'Gagal memuat carton ke container.',
                ], $response->status());
            }

            $body = $response->json();

            if (isset($body['totqty']) && isset($body['totctn']) && isset($body['totnw']) && isset($body['totgw']) && isset($body['totvol'])) {
                DB::table('export')
                    ->where('exportpk', $validated['exportpk'])
                    ->update([
                        'totqty' => $body['totqty'],
                        'totctn' => $body['totctn'],
                        'totnw'  => $body['totnw'],
                        'totgw'  => $body['totgw'],
                        'totvol' => $body['totvol'],
                    ]);
            }

            return response()->json($body);
        } catch (\Throwable $e) {
            //  dd() DIHAPUS. dd() menghentikan eksekusi dan
            // membuang raw HTML dump, BUKAN JSON -- frontend yang expect JSON
            // ($response.json()) langsung gagal parse ("Server mengembalikan
            // respons tidak valid"), dan pesan error ASLI-nya tidak pernah
            // sampai ke user maupun ke log secara terstruktur.
            report($e); // tetap tercatat di log Laravel utk debugging

            return response()->json([
                'message' => 'Sistem Packing tidak dapat terhubung: ' . $e->getMessage(),
            ], 502);
        }
    }

    // public function load_container(Request $request)
    // {
    //     $validated = $request->validate([
    //         'contpk'   => 'required|integer',
    //         'exportpk' => 'required|integer',
    //         'ordpk'    => 'required|integer',
    //         'packpk'   => 'required|array|min:1',
    //         'packpk.*' => 'integer|exists:mysql_andon.pack,packpk',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $packs = DB::connection('mysql_andon')->table('pack')
    //             ->whereIn('packpk', $validated['packpk'])
    //             ->get();

    //         if ($packs->isEmpty()) {
    //             throw new \Exception('Data carton tidak ditemukan.');
    //         }

    //         $popks = $packs->pluck('popk')->unique();

    //         // hitung nomor part AWAL untuk tiap popk (belum tentu final, cuma penomoran berjalan)
    //         $popkPartCache = [];
    //         foreach ($popks as $popk) {
    //             $existingPart = DB::connection('mysql_andon')->table('pack')
    //                 ->where('popk', $popk)
    //                 ->where('exportpk', $validated['exportpk'])
    //                 ->value('part');

    //             if ($existingPart) {
    //                 $popkPartCache[$popk] = $existingPart;
    //             } else {
    //                 $maxPart = DB::connection('mysql_andon')->table('pack')
    //                     ->where('popk', $popk)
    //                     ->where('part', '!=', 10)
    //                     ->max('part');

    //                 $popkPartCache[$popk] = ($maxPart ?? 0) + 1;
    //             }
    //         }

    //         $loadedItems = [];

    //         foreach ($packs as $pack) {
    //             $mif = DB::connection('mysql_andon')
    //                 ->table('po')
    //                 ->where('popk', $pack->popk)
    //                 ->value('mif');

    //             $exportdtpk = DB::table('exportdt')->insertGetId([
    //                 'exportpk' => $validated['exportpk'],
    //                 'contpk'   => $validated['contpk'],
    //                 'packpk'   => $pack->packpk,
    //                 'carton'   => $pack->carton,
    //                 'mif'      => $mif,
    //                 'qty'      => $pack->pcsp,
    //             ]);

    //             $part = $popkPartCache[$pack->popk];

    //             DB::connection('mysql_andon')->table('pack')
    //                 ->where('packpk', $pack->packpk)
    //                 ->update([
    //                     'exportpk'   => $validated['exportpk'],
    //                     'exportdtpk' => $exportdtpk,
    //                     'contpk'     => $validated['contpk'],
    //                     'part'       => $part,
    //                 ]);

    //             $loadedItems[] = [
    //                 'exportdtpk' => $exportdtpk,
    //                 'carton'     => $pack->carton,
    //                 'pcsp'       => $pack->pcsp,
    //                 't_cbm'      => $pack->t_cbm ?? 0,
    //             ];

    //             $insertColumns = [
    //                 'packpk',
    //                 'popk',
    //                 'urut',
    //                 'carton',
    //                 'nobar',
    //                 'OP',
    //                 'POno',
    //                 'customer',
    //                 'material',
    //                 'nw',
    //                 'gw',
    //                 'meas',
    //                 'secsz',
    //             ];
    //             for ($i = 1; $i <= 40; $i++) $insertColumns[] = "qtyp{$i}";
    //             $insertColumns[] = 'pcsp';
    //             for ($i = 1; $i <= 40; $i++) $insertColumns[] = "qty{$i}";
    //             $insertColumns = array_merge($insertColumns, [
    //                 'pcs',
    //                 'jmlpcs',
    //                 'waktu',
    //                 'tanggal',
    //                 'status',
    //                 'part',
    //                 'keterangan',
    //                 'pinjam',
    //                 'kembali',
    //                 'fca',
    //             ]);

    //             DB::connection('mysql_andon')
    //                 ->table('ship')
    //                 ->insertUsing(
    //                     $insertColumns,
    //                     DB::connection('mysql_andon')
    //                         ->table('pack')
    //                         ->select($insertColumns)
    //                         ->where('packpk', $pack->packpk)
    //                 );
    //         }

    //         /*
    //     |----------------------------------------------------------------
    //     | RECALCULATION: cek ulang apakah ada popk yang JADI COMPLETE
    //     | setelah proses load ini. Kalau ya, naikkan SEMUA pack popk
    //     | tersebut (yang exportpk-nya sama dgn shipment plan ini,
    //     | mencakup container manapun) jadi part=10.
    //     |----------------------------------------------------------------
    //     */
    //         foreach ($popks as $popk) {
    //             $totalCarton = DB::connection('mysql_andon')->table('pack')
    //                 ->where('popk', $popk)
    //                 ->distinct()
    //                 ->count('carton');

    //             $totalLoadedNow = DB::connection('mysql_andon')->table('pack')
    //                 ->where('popk', $popk)
    //                 ->whereNotNull('exportpk')
    //                 ->distinct()
    //                 ->count('carton');

    //             if ($totalLoadedNow >= $totalCarton) {
    //                 // ambil semua packpk milik popk ini yang statusnya sudah tertaut ke exportpk ini
    //                 $packpksToFinalize = DB::connection('mysql_andon')->table('pack')
    //                     ->where('popk', $popk)
    //                     ->where('exportpk', $validated['exportpk'])
    //                     ->pluck('packpk');

    //                 DB::connection('mysql_andon')->table('pack')
    //                     ->whereIn('packpk', $packpksToFinalize)
    //                     ->update(['part' => 10]);

    //                 DB::connection('mysql_andon')->table('ship')
    //                     ->whereIn('packpk', $packpksToFinalize)
    //                     ->update(['part' => 10]);
    //             }
    //         }

    //         // ================= UPDATE totqty & totctn di tabel export =================
    //         $summary = DB::table('exportdt')
    //             ->where('exportpk', $validated['exportpk'])
    //             ->selectRaw('SUM(qty) as totqty, COUNT(packpk) as totctn')
    //             ->first();

    //         DB::table('export')
    //             ->where('exportpk', $validated['exportpk'])
    //             ->update([
    //                 'totqty' => $summary->totqty ?? 0,
    //                 'totctn' => $summary->totctn ?? 0,
    //             ]);

    //         DB::commit();

    //         return response()->json([
    //             'message'      => count($packs) . ' carton berhasil dimuat ke container.',
    //             'loaded_items' => $loadedItems,
    //             'totqty'       => $summary->totqty ?? 0,
    //             'totctn'       => $summary->totctn ?? 0,
    //         ]);
    //     } catch (ValidationException $e) {
    //         DB::rollBack();
    //         throw $e;
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         dd($e->getMessage());
    //         // report($e);
    //         // return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data.'], 500);
    //     }
    // }

    // PDF packing list diambil server EXIM dari API finishing-packing lalu
    // diteruskan ke browser, jadi user tidak perlu login di finishing-packing
    public function packingListPdf(Request $request)
    {
        $validated = $request->validate([
            'op'    => 'required|string',
            'po'    => 'nullable|string',
            'poref' => 'nullable|string',
            'mif'   => 'required|integer',
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(120)
                ->get(rtrim(config('services.packing.url'), '/') . '/api/packing/laporan-global/pdf', $validated);
        } catch (\Throwable $e) {
            report($e);
            abort(502, 'Gagal menghubungi aplikasi finishing-packing.');
        }

        if (!$response->successful()) {
            Log::warning('Download packing list PDF gagal', ['params' => $validated, 'status' => $response->status()]);
            abort($response->status() === 404 ? 404 : 502, 'Gagal mengambil packing list.');
        }

        // laporan berupa halaman HTML siap print -> diteruskan apa adanya
        return response($response->body(), 200, array_filter([
            'Content-Type'        => $response->header('Content-Type') ?: 'text/html; charset=UTF-8',
            'Content-Disposition' => $response->header('Content-Disposition'),
        ]));
    }

    // ---- Packing list PDF per PO (1 ikon -> modal "Pilih Warna untuk Print") ----
    // Sama dengan SPS: API finishing-packing Api\SpsPackingListController. Dokumen (PO/OP/POref
    // per unit), warna, session, dan versi Custom QTY PO ini; link PDF = signed URL 24 jam yang
    // dibuka browser langsung di finishing-packing tanpa login.
    public function packingListOptions(Request $request)
    {
        $validated = $request->validate([
            'op'    => 'required|string|max:100',
            'po'    => 'nullable|string|max:100',
            'poref' => 'nullable|string|max:100',
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(30)->acceptJson()
                ->get(rtrim(config('services.packing.url'), '/') . '/api/sps/packing-list', [
                    'op'    => trim($validated['op']),
                    'pono'  => trim((string) ($validated['po'] ?? '')),
                    'poref' => trim((string) ($validated['poref'] ?? '')),
                ]);
        } catch (\Throwable $e) {
            report($e);
            $response = null;
        }

        if (!$response || !$response->successful() || !is_array($response->json('documents'))) {
            return response()->json(['documents' => [], 'error' => 'Daftar packing list tidak dapat dibaca dari finishing-packing.']);
        }

        return response()->json(['documents' => $response->json('documents'), 'error' => null]);
    }

    public function packingListLink(Request $request)
    {
        $validated = $request->validate([
            'op'           => 'required|string|max:100',
            'po'           => 'nullable|string|max:100',
            'poref'        => 'nullable|string|max:100',
            'mif'          => 'required|integer',
            'combos'       => 'nullable|array',
            'combos.*'     => 'nullable|string|max:300',
            'shipgroups'   => 'nullable|array',
            'shipgroups.*' => 'nullable|string|max:50',
            'exim'         => 'nullable|boolean',
            'version'      => ['nullable', 'regex:/^[A-Za-z0-9_-]{1,64}$/'],
        ]);
        $params = array_filter([
            'po'         => (string) ($validated['po'] ?? ''),
            'op'         => $validated['op'],
            'poref'      => (string) ($validated['poref'] ?? ''),
            'mif'        => (int) $validated['mif'],
            'combos'     => $validated['combos'] ?? [],
            'shipgroups' => $validated['shipgroups'] ?? [],
            'exim'       => $request->boolean('exim') ? 1 : null,
            'version'    => $validated['version'] ?? null,
        ], fn($v) => $v !== null && $v !== []);

        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(30)->acceptJson()
                ->get(rtrim(config('services.packing.url'), '/') . '/api/sps/packing-list/url', $params);
        } catch (\Throwable $e) {
            report($e);
            $response = null;
        }

        $url = $response && $response->successful() ? $response->json('url') : null;
        if (!$url) {
            Log::warning('Link packing list PDF gagal', ['params' => $params, 'status' => $response ? $response->status() : null]);
            return response()->json(['url' => null, 'error' => 'Link PDF tidak dapat dibuat oleh finishing-packing.']);
        }

        return response()->json(['url' => $url, 'error' => null]);
    }

    private function getLoadedCartonsFromPacking(int $exportpk, int $contpk)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(10)
                ->get($this->packingBaseUrl() . '/api/packing/loaded-cartons-by-container', [
                    'exportpk' => $exportpk,
                    'contpk'   => $contpk,
                ]);
            return $response->successful() ? collect($response->json('cartons') ?? []) : collect();
        } catch (\Throwable $e) {
            report($e);
            return collect();
        }
    }

    public function unload_carton(Request $request)
    {
        $validated = $request->validate([
            'packpk'   => 'required|array|min:1',
            'packpk.*' => 'integer',
            'exportpk' => 'required|integer',
            'contpk'   => 'required|integer',
        ]);

        return $this->callUnloadApi($validated);
    }

    public function unload_all_carton(Request $request)
    {
        $validated = $request->validate([
            'packpk'   => 'required|array|min:1',
            'packpk.*' => 'integer',
            'exportpk' => 'required|integer',
            'contpk'   => 'required|integer',
        ]);

        return $this->callUnloadApi($validated);
    }

    private function callUnloadApi(array $validated)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(60) //  
                ->post($this->packingBaseUrl() . '/api/packing/unload-carton-from-container', $validated);

            //  FIX UTAMA (OPTIMASI): decode JSON SEKALI SAJA, dipakai
            // ulang utk cabang gagal MAUPUN sukses  $response->json()
            // dipanggil 2x -- 1x buat cek 'message' di cabang gagal, 1x lagi di
            // baris return response()->json($response->json()) -- keduanya
            // decode payload yang SAMA persis, cuma buang waktu/CPU percuma).
            $body = $response->json();

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => $body['message'] ?? 'Gagal mengeluarkan carton.',
                ], $response->status());
            }

            if (isset($body['totqty']) && isset($body['totctn'])) {
                DB::table('export')
                    ->where('exportpk', $validated['exportpk'])
                    ->update([
                        'totqty' => $body['totqty'],
                        'totnw'  => $body['totnw'],
                        'totgw'  => $body['totgw'],
                        'totvol' => $body['totvol'],
                        'totctn' => $body['totctn'],
                    ]);
            }

            return response()->json($body);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Sistem Packing tidak dapat terhubung.' . $e->getMessage(),
            ], 502);
        }
    }

    /**
     * Data tab Container Leave di modal shipment plan (baca saja, diisi security):
     * jam keluar, foto & video dokumentasi keluar, dan link dokumen cetak.
     */
    private function gateLeaveInfo($c): array
    {
        $url = fn($f) => is_string($f) && $f !== '' ? asset('public/upload/gate/' . (int) $c->contpk . '/' . $f) : null;
        $raw = json_decode($c->{GateController::OUT_MEDIA_COLUMN} ?? '[]', true) ?: [];
        $media = [];
        foreach (GateController::OUT_PHOTO_KINDS as $kind => [$label]) {
            $media[] = ['label' => $label, 'urls' => array_values(array_filter(array_map($url, (array) ($raw[$kind] ?? []))))];
        }

        $rejected = isset($c->gate_result) && (int) $c->gate_result === 0;
        $docs = [];
        if (!empty($c->actcontdate)) {
            $docs[] = ['label' => 'Checklist pemeriksaan container', 'url' => route('container.doc', [$c->contpk, 'checklist'])];
            if (!$rejected) {
                $docs[] = ['label' => 'Berita acara stuffing', 'url' => route('container.doc', [$c->contpk, 'stuffing'])];
                $docs[] = ['label' => 'Dokumen pengiriman barang', 'url' => route('container.doc', [$c->contpk, 'pengiriman'])];
            }
        }

        return [
            'out_at'   => !empty($c->gate_out_at) ? Carbon::parse($c->gate_out_at)->format('d/m/Y H:i') : null,
            'arrived'  => !empty($c->actcontdate),
            'rejected' => $rejected,
            'media'    => $media,
            'video'    => $url($raw['out_video'] ?? null),
            'docs'     => $docs,
        ];
    }

    public function detail_json($exportpk)
    {
        $export = DB::table('export')->where('exportpk', $exportpk)->first();

        if (!$export) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // data keluar dari security (tab Container Leave), hanya kolom yang sudah ada
        $gateCols = array_values(array_intersect(['gate_out_at', 'gate_result', GateController::OUT_MEDIA_COLUMN], Schema::getColumnListing('cont')));

        $containers = DB::table('cont')
            ->where('exportpk', $exportpk)
            ->select(array_merge([
                'contpk',
                'contno',
                'type',
                'fwdpk',
                'vessname',
                'volume',
                'foto',
                'foto2',
                'foto3',
                'foto4',
                'foto5',
                'start_ship',
                'end_ship',
                'nopol',
                'noseal',
                'actcontdate',
            ], $gateCols, $this->ekspedisiReady() ? ['angpk'] : []))
            ->get()
            ->map(function ($c) {
                $c->gate = $this->gateLeaveInfo($c);

                // dikirim sebagai map slot -> url, supaya JS modal bisa merestore tiap slot foto
                $fotoUrls = [];

                foreach (self::FOTO_COLUMNS as $field => $column) {
                    $fotoUrls[$field] = $c->{$column}
                        ? asset('public/upload/foto_container/' . $c->{$column})
                        : null;
                }

                $c->foto_urls = $fotoUrls;
                $c->foto_url = $fotoUrls['foto1']; // dipertahankan untuk pemakai lama

                // datetimebox di kartu arrival memakai format d/m/Y H:i (myformatter2/myparser2)
                $c->actcontdate = $c->actcontdate
                    ? \Carbon\Carbon::parse($c->actcontdate)->format('d/m/Y H:i')
                    : null;

                return $c;
            });

        return response()->json([
            'pebno'      => $export->pebno,
            'nobook'      => $export->nobook,
            'vassalpk'   => $export->vassalpk,
            'fwdpk'      => $containers->first()->fwdpk ?? null,
            'angpk'      => $containers->first()->angpk ?? null,   // ekspedisi (tabel ang)
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
            'remark'  => $export->remark,
            'plbpk'  => $export->plbpk,
            'containers' => $containers,
            'OP'  => implode(', ', array_unique(array_map('trim', explode(',', $export->OP)))),
            'POno'  => $export->POno,
            'buyer' => implode(', ', array_unique(array_map('trim', explode(',', $export->buyer)))),
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
                ->post($this->packingBaseUrl() . '/exim/lock-shipment', [
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
            $packingMessage = 'Sistem Packing & Finishing tidak dapat terhubung: ' . $e->getMessage();
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

    public function refreshContainerCards($exportpk)
    {
        $header = DB::table('export')->where('exportpk', $exportpk)->first();
        if (!$header) {
            return response()->json(['message' => 'Data export tidak ditemukan.'], 404);
        }
        $header->ordno = $header->OP ?? '-';
        $isLocked = !empty($header->lockship) && (int) $header->lockship === 1;

        $container = DB::table('cont')->where('exportpk', $exportpk)->get();

        $loadedCartons = collect();
        foreach ($container as $c) {
            $cartonsForThisContainer = $this->getLoadedCartonsFromPacking((int) $exportpk, (int) $c->contpk);
            $loadedCartons->put($c->contpk, $cartonsForThisContainer);
        }

        $totqty = 0;
        $uniqueCartonKeys = collect();
        foreach ($loadedCartons as $cartons) {
            $totqty += $cartons->sum('pcsp');
            $uniqueCartonKeys = $uniqueCartonKeys->merge(
                $cartons->map(fn($item) => ($item['carton'] ?? '') . '|' . ($item['POno'] ?? '') . '|' . ($item['OP'] ?? ''))
            );
        }
        $totctn = $uniqueCartonKeys->unique()->count();

        DB::table('export')->where('exportpk', $exportpk)->update([
            'totqty' => $totqty,
            'totctn' => $totctn,
        ]);

        $html = view('menu.exim-export.shipplan.partials.container-cards', [
            'container' => $container,
            'loadedCartons' => $loadedCartons,
            'header' => $header,
            'isLocked' => $isLocked,
        ])->render();

        return response()->json([
            'html'    => $html,
            'totqty'  => $totqty,
            'totctn'  => $totctn,
        ]);
    }

    public function containerProgress($contpk)
    {
        // 'cont' adalah tabel milik EXIM sendiri -- akses langsung ini SAH.
        $cont = DB::table('cont')->where('contpk', $contpk)->first();
        if (!$cont) {
            return response()->json(['message' => 'Container tidak ditemukan.'], 404);
        }

        //  method ini query LANGSUNG ke
        // mysql_andon (database Packing). SEKARANG delegasikan ke API
        // Packing, SAMA pola dengan available_cartons()/load_container().
        try {
            $response = \Illuminate\Support\Facades\Http::packing()->timeout(15)
                ->get($this->packingBaseUrl() . "/api/packing/container-progress/{$contpk}");

            if (!$response->successful()) {
                $body = $response->json();
                return response()->json([
                    'cartons' => [],
                    'message' => $body['message'] ?? 'Gagal memuat data progres dari sistem Packing.',
                ], $response->status());
            }

            $data = $response->json() ?? [];

            // BARU -- tempel t_cbm per carton dari loaded-cartons-by-container (sumber
            // yang SAMA dengan total CBM di kartu container) supaya view 3D konsisten.
            // Kedua API memakai grouping & label tampilan yang sama -> cocokkan via
            // carton|POno|OP|poref (antrian per key utk antisipasi key kembar).
            $cbmQueue = [];
            foreach ($this->getLoadedCartonsFromPacking((int) $cont->exportpk, (int) $contpk) as $lc) {
                $key = ($lc['carton'] ?? '') . '|' . ($lc['POno'] ?? '') . '|' . ($lc['OP'] ?? '') . '|' . ($lc['poref'] ?? '');
                $cbmQueue[$key][] = (float) ($lc['t_cbm'] ?? 0);
            }
            foreach ($data['cartons'] ?? [] as $i => $c) {
                $key = ($c['carton'] ?? '') . '|' . ($c['POno'] ?? '') . '|' . ($c['OP'] ?? '') . '|' . ($c['poref'] ?? '');
                $data['cartons'][$i]['t_cbm'] = !empty($cbmQueue[$key]) ? array_shift($cbmQueue[$key]) : null;
            }

            // BARU -- tipe & volume container ikut dikirim utk view 3D
            return response()->json(array_merge($data, [
                'container' => [
                    'type'   => $cont->type,
                    'volume' => $cont->volume,
                ],
            ]));
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'cartons' => [],
                'message' => 'Sistem Packing tidak dapat terhubung.',
            ], 502);
        }
    }

    //EXPORT EXCEL LIST SHIPMENT
    // ============================================================
    // QUERY DASAR (join + select + groupBy) - dipakai bareng oleh
    // getListExport (JSON, paginated) dan exportExcel (semua baris)
    // ============================================================
    private function baseExportQuery()
    {
        return DB::table('poexport')
            ->leftJoin('export', 'export.exportpk', '=', 'poexport.exportpk')
            ->leftJoin('vassal', 'vassal.vassalpk', '=', 'export.vassalpk')
            ->leftJoin('cont', 'cont.exportpk', '=', 'export.exportpk')
            ->select(
                'export.exportpk',
                'export.datecreate',
                'export.ordpk',
                'poexport.POno',
                'poexport.OP',
                'poexport.poref',
                'poexport.poexportpk',
                // 'poexport.pebno',
                'poexport.invs',
                'export.buyer',
                // 'export.pebno',
                'export.nobook',
                'export.stscancel',
                'export.remark',   // catatan "Digabung ke SPxxxx" (gabung shipment) -> status Gabung
                'export.lockship',
                'export.exdate as extdate',
                // 'export.actexdate', // dibutuhkan buat hitung status_label
                'export.GAC',
                'export.contdate',
                'export.closetime',
                'export.totctn',
                'export.qty_list',
                'export.userpk',
                'export.vassalpk',
                'export.etdvess',
                'export.exdate',
                'poexport.qty',
                // mif tidak lagi dari poexport.mif - diisi di enrichExportRows()
                // dari pack.mif (mysql_andon) per POno
                'vassal.typenm as vassal',
                'cont.vessname',
                DB::raw('GROUP_CONCAT(cont.type ORDER BY cont.type SEPARATOR ", ") AS type'),
                DB::raw("
                CASE
                    WHEN MAX(
                        CASE
                            WHEN cont.start_ship IS NOT NULL
                                AND cont.start_ship != ''
                            THEN 1
                            ELSE 0
                        END
                    ) = 1
                    THEN 1
                    ELSE 0
                END AS stsstart
            "),
                DB::raw("
               CASE
                    WHEN COUNT(cont.contpk) > 0
                        AND COUNT(cont.segel) = COUNT(cont.contpk)
                    THEN 1
                    ELSE 0
                END AS is_segel
            "),
                // Cont Leave: semua container shipment sudah tercatat keluar pabrik oleh Security (gate out)
                DB::raw("
               CASE
                    WHEN COUNT(cont.contpk) > 0
                        AND COUNT(cont.gate_out_at) = COUNT(cont.contpk)
                    THEN 1
                    ELSE 0
                END AS is_leave
            "),
                DB::raw('MAX(cont.gate_out_at) AS leave_at')
            )
            ->selectRaw("COALESCE(NULLIF(poexport.pebno, ''), export.pebno) AS pebno")
            ->groupBy('poexport.poexportpk');
    }

    // ============================================================
    // FILTER + SORT (search, buyer, vassal, status, range tanggal, sort)
    // - dipakai bareng oleh getListExport & exportExcel
    // ============================================================
    private function applyExportFilters($query, Request $request, $userpk, $guserpk)
    {
        $search = $request->input('q');
        $buyer = $request->input('buyer');
        $vassal = $request->input('vassal');
        $status = $request->input('status');
        $searchByRangeDay = $request->input('searchByRangeDay');
        $sortlistByDate = $request->input('sortlistByDate');

        if (in_array($guserpk, [42, 44, 45])) {
            $userpks = DB::connection('mysql_akses')
                ->table('user')
                ->where('guserpk', $guserpk)
                ->pluck('userpk');

            $query->whereIn('export.userpk', $userpks);
        }

        if ($search) {
            // ID Shipplan = 'SP' + exportpk (pad 4), mis. SP0123 / sp123 / 123
            $idShip = preg_match('/^\s*(?:SP)?0*(\d+)\s*$/i', $search, $m) ? (int) $m[1] : null;

            $query->where(function ($q) use ($search, $idShip) {
                if ($idShip !== null) {
                    $q->orWhere('export.exportpk', $idShip);
                }

                $q->orWhere('export.ordpk', 'like', "%{$search}%")
                    ->orWhere('poexport.POno', 'like', "%{$search}%")
                    ->orWhere('poexport.OP', 'like', "%{$search}%")
                    ->orWhere('export.buyer', 'like', "%{$search}%")
                    ->orWhere('export.pebno', 'like', "%{$search}%")
                    ->orWhere('export.nobook', 'like', "%{$search}%")
                    ->orWhere('poexport.invs', 'like', "%{$search}%")
                    ->orWhere('poexport.pebno', 'like', "%{$search}%");
            });
        }

        if ($buyer) {
            $query->where('export.buyer', $buyer);
        }

        if ($vassal) {
            $query->where('export.vassalpk', $vassal);
        }

        $notCancelled = function ($q) {
            $q->whereNull('export.stscancel')
                ->orWhere('export.stscancel', '<>', 1);
        };
        // shipment asal hasil Gabung Shipment: di-cancel dengan catatan remark "Digabung ke SPxxxx" (lihat mergedTo)
        $isMerged = fn($q) => $q->where('export.remark', 'like', '%Digabung ke SP%');
        $notMerged = fn($q) => $q->where(fn($w) => $w->whereNull('export.remark')->orWhere('export.remark', 'not like', '%Digabung ke SP%'));

        if ($status == 1) {
            // PLAN
            $query->whereNotNull('export.vassalpk')
                ->whereNull('export.exdate')
                // ->whereNull('export.actexdate')
                ->where($notCancelled);
        } elseif ($status == 2) {
            // CONFIRM
            $query->whereNotNull('export.exdate')
                // ->whereNull('export.actexdate')
                ->where($notCancelled);
        } elseif ($status == 3) {
            // COMPLETE
            $query->having('is_segel', '=', 1)
                ->where($notCancelled);
        } elseif ($status == 4) {
            // CANCEL SHIPMENT (cancel biasa, bukan hasil gabung)
            $query->where('export.stscancel', 1)->where($notMerged);
        } elseif ($status == 6) {
            // GABUNG: shipment asal yang digabung ke shipment lain (mergeShipment)
            $query->where('export.stscancel', 1)->where($isMerged);
        } elseif ($status == 5) {
            // CONT LEAVE: semua container sudah keluar pabrik, tapi shipment belum di-Set Complete
            $query->having('is_leave', '=', 1)
                ->having('is_segel', '=', 0)
                ->where($notCancelled);
        } else {
            // checkbox toolbar "Tampilkan cancel" (cancel biasa) & "Tampilkan gabung" (shipment asal hasil gabung);
            // tidak dicentang = disembunyikan, tanpa parameter = tampil (seperti sebelumnya)
            $hideCancel = $request->input('show_cancel') === '0';
            $hideMerge = $request->input('show_merge') === '0';
            if ($hideCancel || $hideMerge) {
                $query->where(function ($q) use ($notCancelled, $notMerged, $isMerged, $hideCancel, $hideMerge) {
                    $q->where($notCancelled);
                    if (!$hideCancel) {
                        $q->orWhere(fn($w) => $w->where('export.stscancel', 1)->where($notMerged));
                    }
                    if (!$hideMerge) {
                        $q->orWhere(fn($w) => $w->where('export.stscancel', 1)->where($isMerged));
                    }
                });
            }
        }

        // filter Ex-Factory -> kolom Buyer Ex-Factory (export.GAC)
        $this->applyDayRangeFilter($query, 'export.GAC', $searchByRangeDay);

        // filter ECA -> kolom ECA / Estimasi Container Arrival (export.contdate)
        $this->applyDayRangeFilter($query, 'export.contdate', $request->input('searchByRangeEca'));

        $this->applyExportSort($query, $sortlistByDate, 'export.exportpk', 'export.datecreate', 'export.closetime', 'export.contdate');

        return $query;
    }

    // Urutan list shipment plan (nilai sortlistByDate):
    // 11 Newest Entry (default), 12 Oldest Entry, 13 Earliest Closing Due,
    // 14 Earliest ECA, 15 Most Container. Tanggal kosong selalu ditaruh di akhir.
    private function applyExportSort($query, $sort, string $exportpkCol, string $datecreateCol, string $closetimeCol, string $contdateCol): void
    {
        switch ((int) $sort) {
            case 12:
                $query->orderBy($datecreateCol, 'asc');
                break;
            case 13:
                $query->orderByRaw("{$closetimeCol} IS NULL")->orderBy($closetimeCol, 'asc')
                    ->orderBy($datecreateCol, 'desc');
                break;
            case 14:
                $query->orderByRaw("{$contdateCol} IS NULL")->orderBy($contdateCol, 'asc')
                    ->orderBy($datecreateCol, 'desc');
                break;
            case 15:
                $query->orderByRaw("(SELECT COUNT(*) FROM cont c WHERE c.exportpk = {$exportpkCol}) DESC")
                    ->orderBy($datecreateCol, 'desc');
                break;
            default:
                $query->orderBy($datecreateCol, 'desc');
        }
    }

    // ============================================================
    // ENRICHMENT (ordno/style/season/idship/part/status_label)
    // - dipakai bareng oleh getListExport & exportExcel
    // ============================================================
    private function enrichExportRows($result, int $offset = 0)
    {
        if ($result->isEmpty()) {
            return $result;
        }

        $ordpkList = $result->pluck('ordpk')->filter()->unique()->values();
        $ordMap = collect();

        if ($ordpkList->isNotEmpty()) {
            $ordRows = DB::connection('mysql_sig')
                ->table('ord')
                ->whereIn('ordpk', $ordpkList)
                ->get(['ordpk', 'ordno', 'season', 'artno', 'srno', 'foto', 'foto2', 'stsfoto']);

            $ordMap = $ordRows->keyBy('ordpk');
        }

        // pack diambil berdasarkan POno saja (bukan exportpk), karena
        // pack.exportpk sering masih kosong sampai proses load carton
        $ponoList = $result->pluck('POno')->filter()->unique()->values();
        $packMap = collect();
        $mifMap = collect();

        if ($ponoList->isNotEmpty()) {
            $packRows = DB::connection('mysql_andon')
                ->table('pack')
                ->whereIn('POno', $ponoList)
                ->select('POno', 'OP', 'part', 'mif')
                ->orderBy('packpk')
                ->get();

            $packMap = $packRows->groupBy('POno')->map(fn($items) => $items->first());

            // MIF per POno + OP dari pack.mif:
            // mif 1 = MIF 1, mif 2 = MIF 2; kalau ada keduanya -> "MIF 1 & MIF 2"
            // (tidak ada pack / mif kosong -> default "MIF 2")
            $mifMap = $packRows->groupBy(fn($item) => $item->POno . '|' . $item->OP)
                ->map(function ($items) {
                    return $items->pluck('mif')
                        ->map(fn($mif) => (int) $mif)
                        ->filter(fn($mif) => in_array($mif, [1, 2], true))
                        ->unique()
                        ->sort()
                        ->map(fn($mif) => 'MIF ' . $mif)
                        ->implode(' & ');
                });
        }

        // MIF per POno + OP + poref dari po.mif (mysql_andon) -- dipakai
        // tombol download packing list (parameter mif). po.mif kosong -> 2
        $poMifMap = collect();
        $poGacMap = collect();

        if ($ponoList->isNotEmpty()) {
            $poRows = DB::connection('mysql_andon')
                ->table('po')
                ->whereIn('POno', $ponoList)
                ->get(['POno', 'OP', 'poref', 'mif', 'GAC'])
                ->groupBy(fn($item) => $item->POno . '|' . $item->OP . '|' . (string) $item->poref);

            $poMifMap = $poRows->map(fn($items) => $items->pluck('mif')
                ->map(fn($mif) => (int) $mif ?: 2)
                ->unique()
                ->sort()
                ->values()
                ->all());

            // Buyer Ex-Factory per PO dari po.GAC (varchar) -- hanya yang
            // berformat tanggal valid (Y-m-d), selain itu dianggap kosong
            $poGacMap = $poRows->map(fn($items) => $items->pluck('GAC')
                ->map(fn($gac) => trim((string) $gac))
                ->filter(fn($gac) => preg_match('/^\d{4}-\d{2}-\d{2}/', $gac))
                ->sort()
                ->first());
        }

        return $result->values()->map(function ($row, $index) use ($ordMap, $packMap, $mifMap, $poMifMap, $poGacMap, $offset) {
            $ord = $ordMap->get($row->ordpk);
            $pack = $packMap->get($row->POno);

            $row->mif = $mifMap->get($row->POno . '|' . $row->OP) ?: 'MIF 2';
            $row->po_mif = $poMifMap->get($row->POno . '|' . $row->OP . '|' . (string) $row->poref) ?: [2];
            // po.GAC kosong -> pakai Buyer Ex-Factory di header shipment (export.GAC)
            $row->po_gac = $poGacMap->get($row->POno . '|' . $row->OP . '|' . (string) $row->poref) ?: ($row->GAC ?? null);

            $row->no = $offset + $index + 1;
            $row->ordno = !empty($row->OP) ? $row->OP : ($ord->ordno ?? '-');
            $row->style = $ord->artno ?? '-';
            $row->season = $ord->season ?? '-';
            $row->idship = 'SP' . str_pad($row->exportpk, 4, '0', STR_PAD_LEFT);
            $row->part = $pack->part ?? '-';

            // dipakai di kolom "Status" export Excel
            // shipment asal hasil gabung: di-cancel dengan catatan "Digabung ke SPxxxx" (mergeShipment)
            $row->merged_to = $this->mergedTo($row);
            $row->status_label = $this->resolveStatusLabel($row);

            return $row;
        });
    }

    // turunkan label status dari data row (bukan dari parameter filter),
    // supaya export "semua status" tetap menampilkan status masing-masing baris
    // ID shipment tujuan kalau shipment ini di-cancel karena digabung (catatan remark dari mergeShipment)
    private function mergedTo($row): ?string
    {
        if ((int) ($row->stscancel ?? 0) !== 1) {
            return null;
        }
        return preg_match_all('/Digabung ke (SP\d+)/', (string) ($row->remark ?? ''), $m) ? end($m[1]) : null;
    }

    private function resolveStatusLabel($row)
    {
        $isCancelled = (int) ($row->stscancel ?? 0) === 1;

        if ($isCancelled) {
            return !empty($row->merged_to) ? 'Gabung ke ' . $row->merged_to : 'Cancel Shipment';
        }

        if (!empty($row->is_segel)) {
            return 'Complete';
        }

        // container sudah keluar pabrik tapi shipment belum di-Set Complete
        if (!empty($row->is_leave)) {
            return 'Cont Leave';
        }

        if (!empty($row->exdate) && empty($row->actexdate)) {
            return 'Confirm';
        }

        if (!empty($row->vassalpk) && empty($row->exdate) && empty($row->actexdate)) {
            return 'Plan';
        }

        return '-';
    }

    /*
|--------------------------------------------------------------------------
| exportExcel() - BARU. Filter query PERSIS sama dengan getListExport
| (search, buyer, vassal, status, searchByRangeDay, sort), bedanya:
| tanpa limit/offset - ambil SEMUA baris yang cocok filter, lalu di-export
| jadi file .xlsx via Maatwebsite\Excel.
|--------------------------------------------------------------------------
*/
    public function exportExcel2(Request $request)
    {
        $userpk = Session::get('userpk');
        $guserpk = Session::get('guserpk');
        $query = $this->applyExportFilters($this->baseExportQuery(), $request, $userpk, $guserpk);

        $result = $query->get();

        if ($result->isNotEmpty()) {
            $result = $this->enrichExportRows($result);
        }

        // NOTE: addOrderImageToRows() sengaja TIDAK dipanggil di sini - itu
        // menyiapkan URL gambar untuk ditampilkan di tabel HTML, bukan untuk
        // ditaruh ke sel Excel. Kalau kamu mau foto produk ikut ter-embed di
        // Excel, itu perlu implementasi terpisah (download tiap gambar +
        // \Maatwebsite\Excel\Concerns\WithDrawings) - bilang aja kalau itu
        // yang dimaksud, saya tambahkan.

        $filename = 'shipment_plan_export_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ShipmentPlanExport($result), $filename);
    }

    /**
     * Riwayat download Shipping Instruction per PO / OP (tanda "sudah masuk SI" di modal pilih PO / OP).
     * 1 download = 1 batch (SI ke-n). Tabel opsional; selama belum dibuat, SI tetap bisa di-download tanpa riwayat:
     *   CREATE TABLE export_si_log (
     *       silogpk    INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
     *       exportpk   INT NOT NULL,
     *       batch      INT NOT NULL,              -- SI ke-n untuk shipment ini
     *       POno       VARCHAR(100) NULL,
     *       OP         VARCHAR(100) NULL,
     *       userpk     INT NULL,
     *       login      VARCHAR(50) NULL,
     *       created_at DATETIME NOT NULL,
     *       KEY idx_export_si_log_exportpk (exportpk)
     *   );
     */
    private const SI_LOG_TABLE = 'export_si_log';

    private function siLogReady(): bool
    {
        static $ready = null;
        return $ready ?? ($ready = Schema::hasTable(self::SI_LOG_TABLE));
    }

    /**
     * GET /export/si/{exportpk}/options
     * PO / OP yang bisa dimasukkan ke Shipping Instruction (dari carton di pack) + riwayat SI yang sudah di-download.
     */
    public function siOptions($exportpk)
    {
        $export = DB::table('export')->where('exportpk', $exportpk)->first(['exportpk', 'buyer']);
        if (!$export) {
            return response()->json(['success' => false, 'message' => 'Shipment plan tidak ditemukan.'], 404);
        }

        $packRows = DB::connection('mysql_andon')->table('pack')->where('exportpk', $exportpk)
            ->get(['POno', 'OP', 'popk', 'carton', 'bundlepk', 'pcsp', 'contpk']);
        $styles = DB::connection('mysql_andon')->table('po')
            ->whereIn('popk', $packRows->pluck('popk')->filter()->unique()->values())
            ->pluck('style', 'popk');

        $items = $packRows->groupBy(fn($r) => ($r->POno ?? '') . '|' . ($r->OP ?? ''))
            ->map(function ($rows, $key) use ($styles) {
                $first = $rows->first();
                return [
                    'key'     => $key,
                    'POno'    => $first->POno ?: '-',
                    'OP'      => $first->OP ?: '-',
                    'style'   => $styles[$first->popk] ?? '-',
                    'cartons' => $rows->whereNull('bundlepk')->pluck('carton')->unique()->count()
                        + $rows->whereNotNull('bundlepk')->pluck('bundlepk')->unique()->count(),
                    'pcs'     => (int) $rows->sum('pcsp'),
                    'loaded'  => $rows->whereNotNull('contpk')->count(),
                    'total'   => $rows->count(),
                ];
            })->values();

        // PO di shipment ini yang belum punya carton: tampil tapi tidak bisa dipilih
        $have = $items->pluck('key')->flip();
        $noCarton = DB::table('poexport')->where('exportpk', $exportpk)->orderBy('poexportpk')->get(['POno', 'OP'])
            ->filter(fn($r) => !isset($have[($r->POno ?? '') . '|' . ($r->OP ?? '')]))
            ->map(fn($r) => ['key' => ($r->POno ?? '') . '|' . ($r->OP ?? ''), 'POno' => $r->POno ?: '-', 'OP' => $r->OP ?: '-'])
            ->unique('key')->values();

        // riwayat: batch => {no, at, login, keys[]}
        $batches = [];
        if ($this->siLogReady()) {
            foreach (DB::table(self::SI_LOG_TABLE)->where('exportpk', $exportpk)->orderBy('batch')->orderBy('silogpk')->get() as $l) {
                $b = &$batches[$l->batch];
                $b = $b ?? ['no' => (int) $l->batch, 'at' => Carbon::parse($l->created_at)->format('d/m/Y H:i'), 'login' => $l->login, 'keys' => []];
                $b['keys'][] = ($l->POno ?? '') . '|' . ($l->OP ?? '');
                unset($b);
            }
        }

        return response()->json([
            'success'   => true,
            'idship'    => 'SP' . str_pad($export->exportpk, 4, '0', STR_PAD_LEFT),
            'buyer'     => implode(', ', $this->buyerList($export->buyer)),
            'tracking'  => $this->siLogReady(),
            'items'     => $items,
            'noCarton'  => $noCarton,
            'batches'   => array_values($batches),
        ]);
    }

    public function exportSI(Request $request, $exportpk)
    {
        // PO / OP yang dipilih di modal (po[] = "POno|OP"); kosong = semua PO / OP shipment
        $picked = collect((array) $request->input('po', []))->map(fn($k) => (string) $k)->filter(fn($k) => $k !== '')->unique()->values();

        $export = DB::table('export')
            ->leftJoin('vassal', 'vassal.vassalpk', '=', 'export.vassalpk')
            ->where('export.exportpk', $exportpk)
            ->select('export.*', 'vassal.typenm')
            ->first();

        if (!$export) {
            abort(404, 'Export tidak ditemukan');
        }

        // BARU -- 1 export bisa punya LEBIH DARI 1 container, jadi ambil
        // semuanya (bukan cuma 1 lewat contpk seperti sebelumnya).
        $conts = DB::table('cont')
            ->where('exportpk', $exportpk)
            ->orderBy('contpk')
            ->get();

        if ($conts->isEmpty()) {
            abort(404, 'Container untuk export ini belum ada');
        }

        $plb = $export->plbpk
            ? DB::table('plb')->where('plbpk', $export->plbpk)->first()
            : null;

        // To Freight Forwarder - diambil dari cont.fwdpk -> fwd.fwdnm.
        // Kalau container lebih dari 1, cukup pakai container pertama.
        $firstFwdpk = $conts->first()->fwdpk ?? null;
        $fwdNm = $firstFwdpk
            ? DB::table('fwd')->where('fwdpk', $firstFwdpk)->value('fwdnm')
            : null;

        // =========================================================
        // Ambil semua carton (pack) untuk EXPORT ini (gabungan semua
        // container-nya) - koneksi mysql_andon, sama seperti flow
        // load/unload carton.
        // =========================================================
        $packRows = DB::connection('mysql_andon')->table('pack')
            ->where('exportpk', $exportpk)
            ->get();

        if ($picked->isNotEmpty()) {
            $pickedSet = $picked->flip();
            $packRows = $packRows->filter(fn($r) => isset($pickedSet[($r->POno ?? '') . '|' . ($r->OP ?? '')]))->values();
            if ($packRows->isEmpty()) {
                abort(422, 'PO / OP yang dipilih belum punya carton di shipment ini.');
            }
            // container hanya yang berisi carton PO / OP terpilih (kalau carton-nya sudah di-load)
            $usedConts = $packRows->pluck('contpk')->filter()->unique();
            if ($usedConts->isNotEmpty()) {
                $conts = $conts->whereIn('contpk', $usedConts->all())->values();
            }
        }

        // Style & deskripsi (silhouette) per PO diambil dari table po
        // (mysql_andon) - popk dari pack.
        $popkList = $packRows->pluck('popk')->filter()->unique()->values();
        $poMap = collect();

        if ($popkList->isNotEmpty()) {
            $poMap = DB::connection('mysql_andon')->table('po')
                ->whereIn('popk', $popkList)
                ->get(['popk', 'style', 'silhouette'])
                ->keyBy('popk');
        }

        // CBM per carton dari panjang x lebar x tinggi di table pack:
        // measpk 1 = cm (/ 1.000.000), measpk 2 = inch (/ 61.024).
        $cbmOf = function ($row) {
            if ($row->panjang === null || $row->lebar === null || $row->tinggi === null) {
                return 0;
            }

            $volume = $row->panjang * $row->lebar * $row->tinggi;

            if ((int) $row->measpk === 1) {
                return $volume / 1000000;
            }

            if ((int) $row->measpk === 2) {
                return $volume / 61024;
            }

            return 0;
        };

        // =========================================================
        // Group per PO No + OP (gabungan semua container) - 1 group = 1
        // blok "P.O. NO / STYLE" di SI. Carton fisik: carton non-bundle
        // dihitung per nomor carton unik; carton BUNDLE dihitung 1 per
        // bundlepk (beberapa carton kecil = 1 carton fisik besar).
        // =========================================================
        // GW/NW/CBM tersimpan per carton & diulang di tiap baris pack carton itu (carton ratio,
        // carton mix, carton CMT MIF + subkon) -> dijumlah 1x per carton fisik, bukan per baris.
        $physKey = fn($r) => !empty($r->mixno) ? 'M|' . $r->mixno . '|' . $r->carton
            : 'C|' . ($r->POno ?? '') . '|' . ($r->OP ?? '') . '|' . ($r->poref ?? '') . '|' . $r->carton . '|' . ($r->part ?? 0);
        $groups = $packRows->groupBy(function ($row) {
            return ($row->POno ?? '') . '|' . ($row->OP ?? '');
        })->map(function ($rows) use ($poMap, $cbmOf, $physKey) {
            $nonBundleCartons = $rows->whereNull('bundlepk')->pluck('carton')->unique();
            $bundleCount = $rows->whereNotNull('bundlepk')->pluck('bundlepk')->unique()->count();

            $first = $rows->first();
            $po = $poMap->get($first->popk ?? null);

            return [
                'POno' => $first->POno ?? '-',
                'OP' => $first->OP ?? '-',
                'style' => $po->style ?? '-',
                'descr' => strtoupper($po->silhouette ?? '-'),
                'cartons' => $nonBundleCartons->count() + $bundleCount,
                'pcs' => (int) $rows->sum('pcsp'),
                'cbm' => (float) $rows->unique($physKey)->sum($cbmOf),
            ];
        })->values();

        $totalCartons = $groups->sum('cartons');
        $totalPcs = $groups->sum('pcs');
        $physRows = $packRows->unique($physKey);
        $totalGw = (float) $physRows->sum('gw');
        $totalNw = (float) $physRows->sum('nw');
        $totalCbm = (float) $physRows->sum($cbmOf);

        // =========================================================
        // No SI - format 0018/SI/I/2026 (no urut / SI / bulan romawi / tahun)
        // =========================================================
        $tanggalSi = $export->contdate ? Carbon::parse($export->contdate) : now();
        $bulanRomawi = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$tanggalSi->month - 1];
        $noUrut = str_pad($export->exportpk, 4, '0', STR_PAD_LEFT);
        $noSi = $noUrut . '/SI/' . $bulanRomawi . '/' . $tanggalSi->format('Y');

        // Vessel diambil dari kumpulan container (biasanya semua container
        // dalam 1 export naik kapal yang sama; kalau beda-beda digabung " / ").
        $vesselName = $conts->pluck('vessname')->filter()->unique()->implode(' / ');

        // Ukuran container: 20ft -> 1X20GP, 40ft -> 1X40GP, 40hc -> 1X40HC
        $typeLabels = ['20ft' => '1X20GP', '40ft' => '1X40GP', '40hc' => '1X40HC', '45ft' => '1X45GP', 'lcl' => 'LCL'];
        $containerLines = $conts->values()->map(function ($c) use ($typeLabels) {
            $type = $typeLabels[strtolower($c->type ?? '')] ?? strtoupper($c->type ?? '');

            return [
                'contno' => trim(($c->contno ?? '-') . ($type ? " // {$type}" : '')),
                'segel' => $c->segel ?? '-',
            ];
        });

        // Service type diambil dari vassal.typenm (export.vassalpk -> vassal).
        $serviceType = $export->typenm ?? '';

        // =========================================================
        // Sheet dibangun langsung (tanpa file template) mengikuti format
        // SHIPPING ORDER / MATE'S RECEIPT. Kolom:
        // A-B Marks & Numbers | C No. of Packages / PO NO | D STYLE |
        // E CTN | F PCS | G CBM | H DESCR / Gross Wt | I Measurement
        // =========================================================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SI');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(9);

        foreach (['A' => 16, 'B' => 16, 'C' => 12, 'D' => 9, 'E' => 9, 'F' => 9, 'G' => 9, 'H' => 36, 'I' => 13] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $thin = ['borderStyle' => Border::BORDER_THIN];
        $box = fn(string $range) => $sheet->getStyle($range)->getBorders()->getOutline()->applyFromArray($thin);
        $grid = fn(string $range) => $sheet->getStyle($range)->getBorders()->getAllBorders()->applyFromArray($thin);
        $bold = fn(string $range) => $sheet->getStyle($range)->getFont()->setBold(true);
        $fill = fn(string $range, string $rgb) => $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rgb);
        $center = fn(string $range) => $sheet->getStyle($range)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $shipperLines = [
            'PT. MORICH INDO FASHION',
            'JL.RAYA KARANGJATI KM. 25',
            'DS.GEMBONGAN KLEPU SEMARANG 50552',
            'INDONESIA',
        ];

        // ---- Judul ----
        $sheet->setCellValue('A1', 'SHIPPING ORDER/');
        $sheet->setCellValue('A2', "MATE'S RECEIPT");
        $sheet->setCellValue('D1', 'INTERNATIONAL SUPPLY CHAIN');
        $sheet->setCellValue('G3', 'ATTN: ' . trim($fwdNm ?? ''));
        $bold('A1:D2');
        $sheet->getStyle('G3:I3')->getBorders()->getBottom()->applyFromArray($thin);

        // ---- Account Code / Shipping Order Number / S/O NO ----
        $sheet->setCellValue('A5', 'Account Code:');
        $sheet->setCellValue('D5', 'Shipping Order Number:');
        $sheet->setCellValue('F5', $noSi);
        $sheet->setCellValue('H5', 'S/O NO : ' . ($export->nobook ?? ''));
        $bold('A5:D5');
        $box('A5:C5');
        $box('D5:I5');

        // ---- Shipper & Manufacturer ----
        $sheet->setCellValue('A6', 'Shipper (Name & Address):');
        $sheet->setCellValue('D6', 'Manufacturer (Name & Address):');
        $bold('A6:D6');
        foreach ($shipperLines as $i => $line) {
            $sheet->setCellValue('A' . (7 + $i), $line);
            $sheet->setCellValue('D' . (7 + $i), $line);
        }
        $box('A6:C11');
        $box('D6:I11');

        foreach (['A' => 'C', 'D' => 'I'] as $start => $end) {
            $mid = $start === 'A' ? 'B' : 'H';
            $sheet->setCellValue($start . '12', 'Email: eko@ptmorich.com');
            $sheet->setCellValue($mid . '12', 'Contact: EKO');
            $sheet->setCellValue($start . '13', 'Tel: 0298 523523');
            $sheet->setCellValue($mid . '13', 'Fax: 0298 522095');
            $box("{$start}12:{$end}13");
        }

        // ---- Consignee / Country of Origin / P.O. No / Notify Party ----
        // Consignee, Notify, Also Notify, Port of Discharge, Place of Delivery
        // dan Marks sengaja dikosongkan - diisi manual sesuai buyer.
        $sheet->setCellValue('A14', 'Consignee (Name & Address):');
        $sheet->setCellValue('D14', 'Country of Origin:');
        $sheet->setCellValue('D15', 'INDONESIA');
        $sheet->setCellValue('G14', 'P.O. No.:');
        $sheet->setCellValue('H14', 'AS BELLOW');
        $sheet->setCellValue('D16', 'Notify Party (Name & Address):');
        $bold('A14:G16');
        $box('A14:C21');
        $box('D14:F15');
        $box('G14:I15');
        $box('D16:I21');
        $fill('D16:I21', 'EBF1A5');

        // ---- Vessel / Also Notify Party ----
        $sheet->setCellValue('A22', 'Vessel/Voyage:');
        $sheet->setCellValue('A23', $vesselName);
        $sheet->setCellValue('D22', 'Also Notify Party (Name & Address):');
        $bold('A22:D22');
        $box('A22:C24');
        $box('D22:I26');
        $fill('D22:I26', 'EBF1A5');

        // ---- Port of Loading / Discharge ----
        $sheet->setCellValue('A25', 'Port of Loading:');
        $sheet->setCellValue('A26', $plb->plbnm ?? '');
        $sheet->setCellValue('B25', 'Port of Discharge:');
        $bold('A25:B25');
        $box('A25:A26');
        $box('B25:C26');

        // ---- Place of Delivery / Shipping Term / Service Type ----
        $sheet->setCellValue('A27', 'Place of Delivery:');
        $sheet->setCellValue('D27', 'Shipping Term:');
        $sheet->setCellValue('G27', 'Service Type:');
        $sheet->setCellValue('H28', $serviceType);
        $bold('A27:G27');
        $box('A27:C28');
        $box('D27:F28');
        $box('G27:I28');

        // ---- Header kolom barang ----
        $sheet->mergeCells('A29:B30')->setCellValue('A29', 'Marks & Numbers');
        $sheet->mergeCells('C29:C30')->setCellValue('C29', "No. of\nPackages");
        $sheet->mergeCells('D29:G30')->setCellValue('D29', 'Description of Pkg. & Goods Particulars');
        $sheet->mergeCells('H29:H30')->setCellValue('H29', "Gross Wt.\n(KGS)");
        $sheet->mergeCells('I29:I30')->setCellValue('I29', "Measurement\n(CBM)");
        $bold('A29:I30');
        $center('A29:I30');
        $sheet->getStyle('A29:I30')->getAlignment()->setWrapText(true);
        $grid('A29:I30');

        // ---- Ringkasan total ----
        $sheet->mergeCells('D31:G31')->setCellValue('D31', "{$totalCartons}  CARTONS");
        $sheet->mergeCells('D32:G32')->setCellValue('D32', "{$totalPcs} PIECES OF APPAREL GOODS");
        $sheet->setCellValue('H31', round($totalGw, 2));
        $sheet->setCellValue('H33', round($totalNw, 2));
        $sheet->setCellValue('I31', round($totalCbm, 2));
        $sheet->getStyle('H31:I33')->getNumberFormat()->setFormatCode('0.00');
        $bold('D31:I33');
        $center('D31:I33');
        $fill('H31:H33', 'FFFF00');

        // ---- Tabel PO ----
        $tableHeaderRow = 35;
        foreach (['C' => 'PO NO', 'D' => 'STYLE', 'E' => 'CTN', 'F' => 'PCS', 'G' => 'CBM', 'H' => 'DESCR'] as $col => $label) {
            $sheet->setCellValue($col . $tableHeaderRow, $label);
        }
        $bold("C{$tableHeaderRow}:H{$tableHeaderRow}");

        $firstDataRow = $tableHeaderRow + 2;
        $dataRowCount = max($groups->count(), $containerLines->count() * 2, 1);

        foreach ($groups as $i => $group) {
            $row = $firstDataRow + $i;
            $sheet->setCellValueExplicit('C' . $row, $group['POno'], DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('D' . $row, $group['style'], DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $group['cartons']);
            $sheet->setCellValue('F' . $row, $group['pcs']);
            $sheet->setCellValue('G' . $row, round($group['cbm'], 2));
            $sheet->setCellValue('H' . $row, $group['descr']);
        }

        // Container No & Seal di kolom Marks, sejajar dengan baris PO
        foreach ($containerLines as $i => $line) {
            $row = $firstDataRow + ($i * 2);
            $sheet->setCellValue('A' . $row, 'CONTAINER NO : ' . $line['contno']);
            $sheet->setCellValue('A' . ($row + 1), 'SEAL NO : ' . $line['segel']);
        }

        $lastDataRow = $firstDataRow + $dataRowCount - 1;
        $totalRow = $lastDataRow + 2;

        $sheet->mergeCells("C{$totalRow}:D{$totalRow}")->setCellValue("C{$totalRow}", 'TOTAL');
        $sheet->setCellValue('E' . $totalRow, $totalCartons);
        $sheet->setCellValue('F' . $totalRow, $totalPcs);
        $sheet->setCellValue('G' . $totalRow, round($totalCbm, 2));
        $bold("C{$totalRow}:G{$totalRow}");

        $grid("C{$tableHeaderRow}:H" . ($totalRow - 1));
        $grid("C{$totalRow}:G{$totalRow}");
        $center("C{$tableHeaderRow}:H{$totalRow}");
        $sheet->getStyle("G{$firstDataRow}:G{$totalRow}")->getNumberFormat()->setFormatCode('0.00');

        // ---- Pilihan YES / NO ----
        $optRow = $totalRow + 1;
        $sheet->setCellValue('H' . $optRow, '** Select one of the below:');
        $bold('H' . $optRow);

        $options = [
            ['Solid Wood Packing Material:', 'YES       OR       NO'],
            ['EXPORT LICENCE:', 'YES       OR       NO'],
            ['FREIGHT:', 'COLLECT'],
        ];
        foreach ($options as $i => [$label, $value]) {
            $row = $optRow + 1 + $i;
            $sheet->mergeCells("D{$row}:G{$row}")->setCellValue('D' . $row, $label);
            $sheet->setCellValue('H' . $row, $value);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . $row)->getFont()->setBold(true)->getColor()->setRGB('1F4E79');
            $sheet->getStyle('H' . $row)->getFont()->setBold(true)->getColor()->setRGB('C00000');
            $center('H' . $row);
        }

        // ---- Est. Cargo Ready Date ----
        $readyRow = $optRow + 2;
        $sheet->setCellValue('A' . $readyRow, 'Est. Cargo Ready Date:');
        $sheet->setCellValue('A' . ($readyRow + 1), $export->contdate ? Carbon::parse($export->contdate)->format('j-M-y') : '');
        $sheet->getStyle('A' . $readyRow . ':A' . ($readyRow + 1))->getFont()->setBold(true)->setItalic(true);
        $center('A' . ($readyRow + 1));
        $fill('A' . ($readyRow + 1), '92D050');
        $box("A{$readyRow}:B" . ($readyRow + 1));

        $sheet->setCellValue('D' . ($optRow + 4), 'above details according to the declaration of the shipper/exporter');
        $sheet->getStyle('D' . ($optRow + 4))->getFont()->setItalic(true);

        $box("A29:I" . ($optRow + 4));
        foreach (['B', 'C', 'G', 'H'] as $col) {
            $sheet->getStyle("{$col}31:{$col}" . ($optRow + 4))->getBorders()->getRight()->applyFromArray($thin);
        }

        // ---- Shipper Declaration ----
        $declRow = $optRow + 5;
        $declarations = [
            'Shipper Declaration:',
            '1. We hereby expressely declare that the above particular furished by us are correct and complete and that we will be fully responsible for the contents of such declaration',
            '2. We further agree to settle all prepaid charges in accordance with the above mentioned shipping term',
            '3. We further agree ECS to issue an original cargo receipt for the above shipment unless as special instruction are given',
            '4. The Shipping Order is governed by the terms and conditions set out on ECS Cargo Receipt',
        ];
        foreach ($declarations as $i => $text) {
            $sheet->setCellValue('A' . ($declRow + $i), $text);
        }
        $sheet->getStyle('A' . $declRow)->getFont()->setBold(true)->setItalic(true)->setUnderline(true);
        $box("A{$declRow}:I" . ($declRow + 4));

        // ---- Chop & Signature / Date / L.C.No ----
        $signRow = $declRow + 5;
        $sheet->setCellValue('A' . $signRow, "Shipper's Chop and Signature:");
        $sheet->setCellValue('D' . $signRow, 'Date:');
        $sheet->setCellValue('G' . $signRow, 'L.C.No.:');
        $bold("A{$signRow}:G{$signRow}");
        $box("A{$signRow}:F" . ($signRow + 2));
        $box("G{$signRow}:I" . ($signRow + 2));

        // ---- Closing Date / Ship Window / Booking Approved ----
        $closeRow = $signRow + 3;
        $sheet->setCellValue('A' . $closeRow, 'Closing Date/Time:');
        $sheet->setCellValue('A' . ($closeRow + 1), $export->closetime ? Carbon::parse($export->closetime)->format('d-M-y H:i') : '');
        $sheet->setCellValue('C' . $closeRow, 'Ship Window :');
        $sheet->setCellValue('G' . $closeRow, 'Booking Approved By:');
        $sheet->setCellValue('G' . ($closeRow + 1), 'Date:');
        $bold("A{$closeRow}:G" . ($closeRow + 1));
        $box("A{$closeRow}:B" . ($closeRow + 2));
        $box("C{$closeRow}:F" . ($closeRow + 2));
        $box("G{$closeRow}:I" . ($closeRow + 2));

        // ---- Total # of Packages Received ----
        $pkgRow = $closeRow + 3;
        $sheet->setCellValue('A' . $pkgRow, 'Total # of Packages Received (IN WORDS):');
        $sheet->setCellValue('A' . ($pkgRow + 1), "     {$totalCartons}  CARTONS");
        $bold("A{$pkgRow}:A" . ($pkgRow + 1));
        $box("A{$pkgRow}:I" . ($pkgRow + 2));

        // ---- Remarks / Cargo at Warehouse ----
        $remarkRow = $pkgRow + 3;
        $sheet->setCellValue('A' . $remarkRow, 'Remarks:');
        $sheet->setCellValue('D' . $remarkRow, 'Cargo at Warehouse');
        $sheet->setCellValue('H' . $remarkRow, 'Date:');
        $sheet->setCellValue('D' . ($remarkRow + 2), 'Received By:');
        $sheet->setCellValue('A' . ($remarkRow + 2), 'NPE NO :              TANGGAL :');
        $sheet->setCellValue('A' . ($remarkRow + 3), 'PEB NO : ' . ($export->pebno ?? '') . '    TANGGAL :');
        $bold("A{$remarkRow}:H{$remarkRow}");
        $fill('A' . ($remarkRow + 2) . ':B' . ($remarkRow + 3), 'FFFF00');
        $box("A{$remarkRow}:C" . ($remarkRow + 7));
        $box("D{$remarkRow}:I" . ($remarkRow + 7));

        // ---- NB ----
        $nbRow = $remarkRow + 8;
        $sheet->setCellValue('A' . $nbRow, 'NB:');
        $sheet->setCellValue('B' . $nbRow, "1. Exel Consolidation Services Ltd. (ECS) shall not be responsible for shut-out shipments after official CFS closing and insurance");
        $sheet->setCellValue('B' . ($nbRow + 1), '    of goods during staging at ECS\'s warehouse.');
        $sheet->setCellValue('B' . ($nbRow + 2), '2. ECS shall charge the shipper for storage cost if goods are delivered before the approved ship date or prior to ship window.');
        $sheet->getStyle("A{$nbRow}:B" . ($nbRow + 2))->getFont()->setBold(true)->setItalic(true);
        $box("A{$nbRow}:I" . ($nbRow + 2));

        // ---- Pengaturan cetak: A4 portrait, muat 1 halaman lebar ----
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $sheet->getPageSetup()->setPrintArea('A1:I' . ($nbRow + 2));

        $filename = 'SI_' . str_replace('/', '-', $noSi) . '.xlsx';
        $tempPath = storage_path('app/tmp/' . $filename);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        // riwayat download SI per PO / OP (lihat SI_LOG_TABLE)
        if ($this->siLogReady()) {
            $batch = (int) DB::table(self::SI_LOG_TABLE)->where('exportpk', $exportpk)->max('batch') + 1;
            $now = now();
            DB::table(self::SI_LOG_TABLE)->insert($groups->map(fn($g) => [
                'exportpk'   => (int) $exportpk,
                'batch'      => $batch,
                'POno'       => $g['POno'] === '-' ? null : $g['POno'],
                'OP'         => $g['OP'] === '-' ? null : $g['OP'],
                'userpk'     => Session::get('userpk'),
                'login'      => Session::get('login'),
                'created_at' => $now,
            ])->all());
        }

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function exportCLP(Request $request, $exportpk)
    {
        $export = DB::table('export')->where('exportpk', $exportpk)->first();

        if (!$export) {
            abort(404, 'Export tidak ditemukan');
        }

        $conts = DB::table('cont')
            ->where('exportpk', $exportpk)
            ->orderBy('contpk')
            ->get();

        if ($conts->isEmpty()) {
            abort(404, 'Container untuk export ini belum ada');
        }

        // Carton per container diambil dari API Packing (sumber data yang
        // sama dengan load/progress container) -- BUKAN query langsung ke
        // mysql_andon, karena DB itu belum tentu sama dengan DB Packing.
        $cartonsByCont = [];
        foreach ($conts as $c) {
            try {
                $response = \Illuminate\Support\Facades\Http::packing()->timeout(30)
                    ->get($this->packingBaseUrl() . '/api/packing/loaded-cartons-by-container', [
                        'exportpk' => $exportpk,
                        'contpk'   => $c->contpk,
                    ]);
            } catch (\Throwable $e) {
                report($e);
                abort(502, 'Sistem Packing tidak dapat terhubung.');
            }

            if (!$response->successful()) {
                abort(502, $response->json('message') ?? 'Gagal memuat data carton dari sistem Packing.');
            }

            $cartonsByCont[$c->contpk] = collect($response->json('cartons') ?? []);
        }

        // Style per PO + OP dari table po (popk dari export.popks).
        $popks = collect(explode(',', (string) $export->popks))
            ->map(fn($v) => (int) trim($v))->filter()->unique()->values();
        $styleMap = $popks->isNotEmpty()
            ? DB::connection('mysql_andon')->table('po')
                ->whereIn('popk', $popks)
                ->get(['POno', 'OP', 'style'])
                ->mapWithKeys(fn($po) => [($po->POno ?? '') . '|' . ($po->OP ?? '') => $po->style])
            : collect();

        // GW / NW tidak dikirim oleh API Packing -> ambil per packpk dari table pack.
        $packpks = collect($cartonsByCont)->flatten(1)->pluck('packpks')->flatten()->filter()->unique()->values();
        $weightMap = collect();
        foreach ($packpks->chunk(1000) as $chunk) {
            $weightMap = $weightMap->union(
                DB::connection('mysql_andon')->table('pack')
                    ->whereIn('packpk', $chunk->all())
                    ->get(['packpk', 'popk', 'carton', 'part', 'mixno', 'gw', 'nw'])
                    ->keyBy('packpk')
            );
        }

        // Ukuran container: 20ft -> 20GP, 40ft -> 40GP, 40hc -> 40HC
        $typeLabels = ['20ft' => '20GP', '40ft' => '40GP', '40hc' => '40HC', '45ft' => '45GP', 'lcl' => 'LCL'];

        // =========================================================
        // 1 blok = 1 container, isinya baris per PO (POno + OP) yang
        // di-load ke container tsb. 1 item dari API = 1 carton fisik
        // (BUNDLE = 1 carton besar berisi beberapa carton kecil).
        // =========================================================
        $blocks = [];

        foreach ($conts as $c) {
            $cartons = $cartonsByCont[$c->contpk];
            if ($cartons->isEmpty()) {
                continue;
            }

            $lines = [];
            $lineOf = function ($poNo, $op) use (&$lines, $styleMap) {
                $key = ($poNo ?? '') . '|' . ($op ?? '');
                if (!isset($lines[$key])) {
                    $lines[$key] = [
                        'POno'    => $poNo ?? '-',
                        'style'   => $styleMap->get($key) ?? '-',
                        'ctns'    => 0,
                        'qty'     => 0,
                        'gw'      => 0.0,
                        'nw'      => 0.0,
                        'cbm'     => 0.0,
                        'numbers' => [],
                    ];
                }
                return $key;
            };

            foreach ($cartons as $ctn) {
                $key = $lineOf($ctn['POno'] ?? null, $ctn['OP'] ?? null);
                $lines[$key]['ctns']++;
                $lines[$key]['cbm'] += (float) ($ctn['t_cbm'] ?? 0);

                // berat tersimpan per carton & diulang di tiap baris pack carton yang sama
                // (ratio / mix / CMT MIF + subkon) -> 1x per carton fisik (carton kecil bundle tetap masing-masing)
                $seenWeight = [];
                foreach ($ctn['packpks'] ?? [] as $packpk) {
                    $w = $weightMap->get($packpk);
                    if (!$w) continue;
                    $wk = !empty($w->mixno) ? 'M|' . $w->mixno . '|' . $w->carton : 'C|' . $w->popk . '|' . $w->carton . '|' . ($w->part ?? 0);
                    if (isset($seenWeight[$wk])) continue;
                    $seenWeight[$wk] = true;
                    $lines[$key]['gw'] += (float) ($w->gw ?? 0);
                    $lines[$key]['nw'] += (float) ($w->nw ?? 0);
                }

                if (!empty($ctn['is_bundle'])) {
                    // qty & nomor carton dari carton kecil di dalam bundle
                    // (bundle mix bisa berisi beberapa PO).
                    foreach ($ctn['small_cartons'] ?? [] as $small) {
                        $smallKey = $lineOf($small['POno'] ?? null, $small['OP'] ?? null);
                        $lines[$smallKey]['qty'] += (int) ($small['pcsp'] ?? 0);
                        $lines[$smallKey]['numbers'][] = (int) $small['carton'];
                    }
                } else {
                    $lines[$key]['qty'] += (int) ($ctn['pcsp'] ?? 0);
                    $lines[$key]['numbers'][] = (int) $ctn['carton'];
                }
            }

            $lines = collect($lines)->map(function ($line) {
                $min = $line['numbers'] ? min($line['numbers']) : null;
                $max = $line['numbers'] ? max($line['numbers']) : null;
                $line['range'] = $min === $max ? (string) $min : "{$min} – {$max}";
                $line['minCtn'] = (int) $min;
                return $line;
            })
                ->sortBy([['POno', 'asc'], ['minCtn', 'asc']])
                ->values();

            $type = $typeLabels[strtolower($c->type ?? '')] ?? strtoupper($c->type ?? '');

            $blocks[] = [
                'equipment' => 'CONTAINER NO : ' . ($c->contno ?? '') . " /\n" . trim($type . ' SEAL NO : ' . ($c->noseal ?? '')),
                'lines'     => $lines,
            ];
        }

        // =========================================================
        // Sheet: A NO | B PO NO | C STYLE | D CTNS | E QTY | F GW |
        // G NW | H MEAS | I CARTONS NOS & RANGE | J EQUIPMENT
        // =========================================================
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('CLP');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        foreach (['A' => 6, 'B' => 13, 'C' => 18, 'D' => 9, 'E' => 10, 'F' => 12, 'G' => 12, 'H' => 10, 'I' => 16, 'J' => 42] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $sheet->mergeCells('A1:J1')->setCellValue('A1', 'CONTAINER LOAD PLAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headerRow = 3;
        $headers = ['NO.', 'PO NO', 'STYLE', 'CTNS', 'QTY', 'GW', 'NW', 'MEAS', "CARTONS NOS &\nRANGE", 'EQUIPMENT'];
        foreach ($headers as $i => $label) {
            $sheet->setCellValue(chr(ord('A') + $i) . $headerRow, $label);
        }
        $headerStyle = $sheet->getStyle("A{$headerRow}:J{$headerRow}");
        $headerStyle->getFont()->setBold(true)->setUnderline(true);
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B8CCE4');
        $headerStyle->getAlignment()->setWrapText(true);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        $row = $headerRow + 1;
        $no = 1;

        foreach ($blocks as $block) {
            $blockStart = $row;

            foreach ($block['lines'] as $line) {
                $sheet->setCellValue('A' . $row, $no++);
                $sheet->setCellValueExplicit('B' . $row, $line['POno'], DataType::TYPE_STRING);
                $sheet->setCellValueExplicit('C' . $row, $line['style'], DataType::TYPE_STRING);
                $sheet->setCellValue('D' . $row, $line['ctns']);
                $sheet->setCellValue('E' . $row, $line['qty']);
                $sheet->setCellValue('F' . $row, round($line['gw'], 2));
                $sheet->setCellValue('G' . $row, round($line['nw'], 2));
                $sheet->setCellValue('H' . $row, round($line['cbm'], 3));
                $sheet->setCellValueExplicit('I' . $row, $line['range'], DataType::TYPE_STRING);
                $sheet->getRowDimension($row)->setRowHeight(24);
                $row++;
            }

            $blockEnd = $row - 1;
            if ($blockEnd > $blockStart) {
                $sheet->mergeCells("J{$blockStart}:J{$blockEnd}");
            }
            $sheet->setCellValue('J' . $blockStart, $block['equipment']);
        }

        $lastDataRow = $row - 1;
        $firstDataRow = $headerRow + 1;
        $totalRow = $row;

        $sheet->mergeCells("A{$totalRow}:C{$totalRow}")->setCellValue('A' . $totalRow, 'TOTAL');
        if ($lastDataRow >= $firstDataRow) {
            foreach (['D', 'E', 'F', 'G', 'H'] as $col) {
                $sheet->setCellValue($col . $totalRow, "=SUM({$col}{$firstDataRow}:{$col}{$lastDataRow})");
            }
        }
        $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
        $sheet->getRowDimension($totalRow)->setRowHeight(24);

        // ---- Styling tabel ----
        $tableRange = "A{$headerRow}:J{$totalRow}";
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_DOTTED);
        $sheet->getStyle($tableRange)->getBorders()->getOutline()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($tableRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("J{$firstDataRow}:J{$totalRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("B{$firstDataRow}:B{$lastDataRow}")->getFont()->setBold(true);
        $sheet->getStyle("I{$firstDataRow}:J{$lastDataRow}")->getFont()->setBold(true);

        $sheet->getStyle("D{$firstDataRow}:E{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("F{$firstDataRow}:G{$lastDataRow}")->getNumberFormat()->setFormatCode('0.00');
        $sheet->getStyle("F{$totalRow}:G{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("H{$firstDataRow}:H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.000');

        // ---- Pengaturan cetak: A4 landscape, muat 1 halaman lebar ----
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $sheet->getPageSetup()->setPrintArea("A1:J{$totalRow}");

        // Nama file sementara dibuat unik supaya tidak bentrok dengan download
        // lain / sisa file lama; nama yang diterima user tetap $filename.
        $filename = "CLP_Export_{$exportpk}.xlsx";
        $tempPath = storage_path('app/tmp/' . uniqid('clp_', true) . '.xlsx');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    //SET INVOICE BARU
    // Daftar nomor invoice yang sudah ada (distinct) + jumlah PO yang memakainya
    public function invoiceOptions(Request $request)
    {
        $q = $request->get('q');

        $list = DB::table('poexport')
            ->whereNotNull('invs')
            ->where('invs', '<>', '')
            ->when($q, function ($query) use ($q) {
                $query->where('invs', 'like', "%{$q}%");
            })
            ->select('invs', DB::raw('COUNT(*) as total_po'))
            ->groupBy('invs')
            ->orderByRaw("CAST(SUBSTRING_INDEX(invs, '/', -1) AS UNSIGNED) DESC") // tahun
            ->orderByRaw("CAST(SUBSTRING_INDEX(invs, '/', 1) AS UNSIGNED) DESC")  // nomor urut
            ->limit(200)
            ->get();

        return response()->json($list);
    }

    // Generate nomor berikutnya dari nomor terakhir tahun berjalan
    public function generateInvoiceNumber()
    {
        $year = now()->year;

        $lastInvs = DB::table('poexport')
            ->where('invs', 'like', "%/INV/{$year}")
            ->orderByRaw("CAST(SUBSTRING_INDEX(invs, '/', 1) AS UNSIGNED) DESC")
            ->value('invs');

        if ($lastInvs) {
            $prefix = explode('/', $lastInvs)[0];
            $next = ((int) $prefix) + 1;
            $width = max(3, strlen($prefix)); // ikuti lebar nomor terakhir (min 3 digit)
        } else {
            $next = 1;
            $width = 3;
        }

        return response()->json([
            'invs' => str_pad($next, $width, '0', STR_PAD_LEFT) . '/INV/' . $year,
            'last' => $lastInvs,
        ]);
    }

    public function deletePo(Request $request)
    {
        $po = DB::table('poexport')
            ->where('poexportpk', $request->input('poexportpk'))
            ->first();

        if (!$po) {
            return response()->json(['success' => false, 'message' => 'Data PO tidak ditemukan.'], 404);
        }

        $exportpk = $po->exportpk;

        // export dengan 1 PO tidak boleh dihapus lewat sini
        $totalPo = DB::table('poexport')->where('exportpk', $exportpk)->count();
        if ($totalPo <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment plan ini hanya punya 1 PO, tidak bisa dihapus dari sini.'
            ], 422);
        }

        $andon = DB::connection('mysql_andon');

        // query dasar: carton milik PO ini di export ini (POno + OP + poref via tabel po)
        $poCartons = function () use ($andon, $exportpk, $po) {
            return $andon->table('pack')
                ->where('exportpk', $exportpk)
                ->where('POno', $po->POno)
                ->where('OP', $po->OP)
                ->whereIn('popk', function ($q) use ($po) {
                    $q->select('popk')
                        ->from('po')
                        ->where('poref', $po->poref);
                });
        };

        // ATURAN BARU: kalau masih ada carton PO ini yang di-load di container, tidak boleh hapus
        $loaded = $poCartons()->whereNotNull('contpk')->count();
        if ($loaded > 0) {
            return response()->json([
                'success' => false,
                'message' => "Carton PO {$po->POno} sudah di-load di container ({$loaded} carton). "
                    . 'Unload dulu carton PO ini dari container, baru PO bisa dihapus.'
            ], 422);
        }

        $newPOno = '';
        $newOP = '';
        $unassigned = 0;

        DB::beginTransaction();
        $andon->beginTransaction();

        try {
            // carton yang tersisa (belum masuk container): lepas dari export
            $unassigned = $poCartons()->update([
                'exportpk' => null,
                'contpk' => null,
            ]);

            DB::table('poexport')->where('poexportpk', $po->poexportpk)->delete();

            // susun ulang POno & OP di export dari poexport yang tersisa
            $remaining = DB::table('poexport')
                ->where('exportpk', $exportpk)
                ->orderBy('poexportpk')
                ->get(['POno', 'OP']);

            $export = DB::table('export')->where('exportpk', $exportpk)->lockForUpdate()->first();

            $sep = (strpos((string) ($export->POno ?? ''), ', ') !== false
                || strpos((string) ($export->OP ?? ''), ', ') !== false) ? ', ' : ',';

            $newPOno = implode($sep, $remaining->pluck('POno')->filter()->unique()->all());
            $newOP = implode($sep, $remaining->pluck('OP')->filter()->unique()->all());

            DB::table('export')->where('exportpk', $exportpk)->update([
                'POno' => $newPOno,
                'OP' => $newOP,
            ]);

            // popk PO ini (POno + OP + poref) dikeluarkan dari export.popks
            $removedPopks = $andon->table('po')
                ->where('POno', $po->POno)->where('OP', $po->OP)
                ->where(function ($q) use ($po) {
                    if ((string) $po->poref === '') {
                        $q->whereNull('poref')->orWhere('poref', '');
                    } else {
                        $q->where('poref', $po->poref);
                    }
                })
                ->pluck('popk')->all();

            $this->syncExportPopks($exportpk, [], $removedPopks);

            DB::commit();
            $andon->commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $andon->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus PO: ' . $e->getMessage()
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "PO {$po->POno} berhasil dihapus dari shipment plan."
                . ($unassigned > 0 ? " {$unassigned} carton dilepas dari shipment plan." : ''),
            'unassigned' => $unassigned,
            'POno' => $newPOno,
            'OP' => $newOP,
        ]);
    }

    // export.popks dipakai available_cartons() sebagai daftar popk yang
    // carton-nya boleh tampil -- wajib ikut berubah saat PO ditambah/dihapus.
    private function syncExportPopks($exportpk, array $add = [], array $remove = []): void
    {
        $current = DB::table('export')->where('exportpk', $exportpk)->value('popks');

        $remove = collect($remove)->map(fn($v) => (int) $v)->all();

        $popks = collect(explode(',', (string) $current))
            ->merge($add)
            ->map(fn($v) => (int) trim($v))
            ->filter()
            ->reject(fn($v) => in_array($v, $remove, true))
            ->unique()
            ->values();

        DB::table('export')->where('exportpk', $exportpk)->update([
            'popks' => $popks->implode(','),
        ]);
    }

    private function enrichPoCount($result)
    {
        $exportpkList = $result->pluck('exportpk')->filter()->unique()->values();

        $poCounts = DB::table('poexport')
            ->whereIn('exportpk', $exportpkList)
            ->select('exportpk', DB::raw('COUNT(*) as c'))
            ->groupBy('exportpk')
            ->pluck('c', 'exportpk');

        return $result->map(function ($row) use ($poCounts) {
            $row->po_count = (int) ($poCounts[$row->exportpk] ?? 1);
            return $row;
        });
    }

    /**
     * GET /export/{exportpk}/merge-candidates?q=
     * Shipment plan lain yang bisa digabung ke shipment ini (tujuan). Yang tidak bisa tetap tampil dengan alasannya:
     * sudah di-cancel / lock tidak ditampilkan; carton sudah Complete Shipment (pack.status 7) atau
     * container sudah keluar pabrik (cont.gate_out_at) ditolak.
     */
    public function mergeCandidates(Request $request, $exportpk)
    {
        $target = DB::table('export')->where('exportpk', $exportpk)->first(['exportpk', 'buyer', 'stscancel', 'lockship']);
        if (!$target) {
            return response()->json(['success' => false, 'message' => 'Shipment plan tidak ditemukan.'], 404);
        }
        if ($problem = $this->mergeTargetProblem($target)) {
            return response()->json(['success' => false, 'message' => $problem], 422);
        }

        $search = trim((string) $request->input('q', ''));
        $idShip = preg_match('/^\s*(?:SP)?0*(\d+)\s*$/i', $search, $m) ? (int) $m[1] : null;
        $rows = DB::table('export')
            ->where('exportpk', '<>', $target->exportpk)
            ->where(fn($q) => $q->whereNull('stscancel')->orWhere('stscancel', '<>', 1))
            ->where(fn($q) => $q->whereNull('lockship')->orWhere('lockship', '<>', 1))
            ->when($search !== '', fn($q) => $q->where(function ($w) use ($search, $idShip) {
                if ($idShip !== null) $w->orWhere('exportpk', $idShip);
                $w->orWhere('POno', 'like', "%{$search}%")->orWhere('OP', 'like', "%{$search}%")->orWhere('buyer', 'like', "%{$search}%");
            }))
            ->orderByDesc('exportpk')
            ->limit(150)
            ->get(['exportpk', 'buyer', 'POno', 'OP', 'totctn', 'qty_list', 'contdate', 'GAC']);

        $blocked = $this->mergeSourceProblems($rows->pluck('exportpk')->all());
        $loaded = DB::connection('mysql_andon')->table('pack')
            ->whereIn('exportpk', $rows->pluck('exportpk')->all())->whereNotNull('contpk')
            ->selectRaw('exportpk, COUNT(*) n')->groupBy('exportpk')->pluck('n', 'exportpk');
        $poCount = DB::table('poexport')->whereIn('exportpk', $rows->pluck('exportpk')->all())
            ->selectRaw('exportpk, COUNT(*) n')->groupBy('exportpk')->pluck('n', 'exportpk');
        $targetBuyers = $this->buyerList($target->buyer);

        return response()->json([
            'success' => true,
            'idship'  => 'SP' . str_pad($target->exportpk, 4, '0', STR_PAD_LEFT),
            'buyer'   => implode(', ', $targetBuyers),
            'data'    => $rows->map(fn($r) => [
                'exportpk'   => $r->exportpk,
                'idship'     => 'SP' . str_pad($r->exportpk, 4, '0', STR_PAD_LEFT),
                'buyer'      => implode(', ', $this->buyerList($r->buyer)),
                'same_buyer' => (bool) array_intersect($this->buyerList($r->buyer), $targetBuyers),
                'POno'       => $r->POno,
                'OP'         => $r->OP,
                'po_count'   => (int) ($poCount[$r->exportpk] ?? 0),
                'totctn'     => (int) $r->totctn,
                'qty'        => (int) $r->qty_list,
                'loaded'     => (int) ($loaded[$r->exportpk] ?? 0),
                'eca'        => $r->contdate,
                'blocked'    => $blocked[$r->exportpk] ?? null,
            ])->values(),
        ]);
    }

    /**
     * POST /export/merge  body: exportpk (tujuan), sources[] (shipment yang digabung)
     * - PO / OP shipment sumber dipindah ke tujuan (poexport disalin, yang sudah ada di tujuan dilewati)
     * - buyer, popks, POno / OP, totctn & qty_list (daftar qty per PO) tujuan diperbarui
     * - carton shipment sumber di-unload (pack.exportpk / exportdtpk / contpk / part = null; status 6 -> 4,
     *   ceklis security dibatalkan seperti batal ceklis), data ship (sistem lama) dihapus -- sama dengan cancel shipment
     * - shipment sumber jadi Cancel, remark diberi catatan "Digabung ke SPxxxx"
     */
    public function mergeShipment(Request $request)
    {
        $v = $request->validate([
            'exportpk'  => 'required|integer',
            'sources'   => 'required|array|min:1|max:50',
            'sources.*' => 'integer|distinct',
        ], ['sources.required' => 'Pilih minimal 1 shipment plan yang akan digabung.']);

        $targetId = (int) $v['exportpk'];
        $sourceIds = array_values(array_unique(array_map('intval', $v['sources'])));
        if (in_array($targetId, $sourceIds, true)) {
            return response()->json(['success' => false, 'message' => 'Shipment tujuan tidak bisa digabung ke dirinya sendiri.'], 422);
        }

        $andon = DB::connection('mysql_andon');
        DB::beginTransaction();
        $andon->beginTransaction();
        try {
            $target = DB::table('export')->where('exportpk', $targetId)->lockForUpdate()->first();
            if (!$target) {
                throw new \RuntimeException('Shipment plan tujuan tidak ditemukan.');
            }
            if ($problem = $this->mergeTargetProblem($target)) {
                throw new \RuntimeException($problem);
            }
            $sources = DB::table('export')->whereIn('exportpk', $sourceIds)->lockForUpdate()->get()->keyBy('exportpk');
            $idOf = fn($id) => 'SP' . str_pad($id, 4, '0', STR_PAD_LEFT);
            $errors = [];
            foreach ($sourceIds as $id) {
                $src = $sources[$id] ?? null;
                if (!$src) {
                    $errors[] = $idOf($id) . ': tidak ditemukan';
                } elseif ((int) $src->stscancel === 1) {
                    $errors[] = $idOf($id) . ': sudah di-cancel';
                } elseif ((int) $src->lockship === 1) {
                    $errors[] = $idOf($id) . ': sudah Lock Shipment';
                }
            }
            foreach ($this->mergeSourceProblems($sourceIds) as $id => $why) {
                $errors[] = $idOf($id) . ': ' . $why;
            }
            if ($errors) {
                throw new \RuntimeException('Shipment tidak bisa digabung. ' . implode('; ', array_unique($errors)) . '.');
            }

            // 1) PO / OP pindah ke tujuan
            $exists = [];
            foreach (DB::table('poexport')->where('exportpk', $targetId)->get(['POno', 'OP', 'poref']) as $r) {
                $exists[$r->POno . '|' . $r->OP . '|' . trim((string) $r->poref)] = true;
            }
            $moved = 0;
            $skipped = [];
            foreach (DB::table('poexport')->whereIn('exportpk', $sourceIds)->orderBy('exportpk')->orderBy('poexportpk')->get() as $r) {
                $key = $r->POno . '|' . $r->OP . '|' . trim((string) $r->poref);
                if (isset($exists[$key])) {
                    $skipped[] = $r->POno . ($r->OP ? ' / ' . $r->OP : '');
                    continue;
                }
                $exists[$key] = true;
                $row = (array) $r;
                unset($row['poexportpk']);
                $row['exportpk'] = $targetId;
                DB::table('poexport')->insert($row);
                $moved++;
            }

            // 2) header tujuan: buyer, POno / OP, popks, total carton & qty
            $buyers = $this->buyerList($target->buyer);
            foreach ($sources as $src) {
                $buyers = array_merge($buyers, $this->buyerList($src->buyer));
            }
            $sep = (strpos((string) $target->POno, ', ') !== false) ? ', ' : ',';
            $rows = DB::table('poexport')->where('exportpk', $targetId)->orderBy('poexportpk')->get(['POno', 'OP', 'qty']);
            DB::table('export')->where('exportpk', $targetId)->update([
                'buyer'    => implode(',', array_values(array_unique($buyers))),
                'POno'     => implode($sep, array_values(array_unique(array_filter($rows->pluck('POno')->all(), 'strlen')))),
                'OP'       => implode($sep, array_values(array_unique(array_filter($rows->pluck('OP')->all(), 'strlen')))),
                'totctn'   => DB::raw('COALESCE(totctn,0) + ' . (int) $sources->sum('totctn')),
                // qty_list = daftar qty per PO (urutan poexport), mis. "1440,120,527"
                'qty_list' => $rows->pluck('qty')->map(fn($q) => (int) $q)->implode(','),
                'dateupdt' => now(),
            ]);
            $srcPopks = $sources->pluck('popks')->flatMap(fn($p) => explode(',', (string) $p))->all();
            $this->syncExportPopks($targetId, $srcPopks);

            // 3) carton shipment sumber di-unload (sama dengan cancel shipment) + batal ceklis security
            $packpks = DB::table('exportdt')->whereIn('exportpk', $sourceIds)->whereNotNull('packpk')->pluck('packpk')->unique()->values();
            $unloaded = $andon->table('pack')->whereIn('exportpk', $sourceIds)->whereNotNull('contpk')->count();
            $andon->table('pack')->whereIn('exportpk', $sourceIds)->where('status', 6)
                ->update(['status' => 4, 'cekdate2' => null, 'datescan' => null]);
            $andon->table('pack')->whereIn('exportpk', $sourceIds)->update([
                'exportpk' => null, 'exportdtpk' => null, 'contpk' => null, 'part' => null,
            ]);
            if ($packpks->isNotEmpty()) {
                $andon->table('ship')->whereIn('packpk', $packpks)->delete();
            }

            // 4) shipment sumber jadi Cancel (soft cancel: poexport & exportdt tetap, seperti cancel shipment)
            $note = 'Digabung ke ' . $idOf($targetId) . ' oleh ' . (Session::get('login') ?: 'EXIM') . ' ' . now()->format('d/m/Y H:i');
            foreach ($sources as $src) {
                DB::table('export')->where('exportpk', $src->exportpk)->update([
                    'stscancel' => 1,
                    'remark'    => trim(trim((string) $src->remark) . "\n" . $note),
                    'dateupdt'  => now(),
                ]);
            }

            $andon->commit();
            DB::commit();
        } catch (\RuntimeException $e) {
            $andon->rollBack();
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            $andon->rollBack();
            DB::rollBack();
            report($e);
            return response()->json(['success' => false, 'message' => 'Gagal menggabung shipment: ' . $e->getMessage()], 500);
        }

        $names = implode(', ', array_map($idOf, $sourceIds));
        return response()->json([
            'success' => true,
            'message' => $names . ' digabung ke ' . $idOf($targetId) . ": {$moved} PO dipindah"
                . ($unloaded ? ", {$unloaded} carton di-unload dari container" : '')
                . '. Shipment asal sudah Cancel.'
                . ($skipped ? ' PO yang sudah ada di tujuan dilewati: ' . implode(', ', array_unique($skipped)) . '.' : ''),
        ]);
    }

    // shipment tujuan penggabungan harus aktif, belum Lock Shipment, belum Complete / container belum keluar pabrik
    private function mergeTargetProblem($target): ?string
    {
        if ((int) $target->stscancel === 1) return 'Shipment tujuan sudah di-cancel.';
        if ((int) $target->lockship === 1) return 'Shipment tujuan sudah Lock Shipment, tidak bisa menerima PO lain.';
        if ($why = $this->mergeSourceProblems([$target->exportpk])[$target->exportpk] ?? null) {
            return 'Shipment tujuan tidak bisa menerima PO lain: ' . $why . '.';
        }
        return null;
    }

    // alasan shipment tidak bisa digabung (carton sudah Complete Shipment / container sudah keluar pabrik)
    private function mergeSourceProblems(array $ids): array
    {
        if (!$ids) return [];
        $out = [];
        $done = DB::connection('mysql_andon')->table('pack')->whereIn('exportpk', $ids)->where('status', 7)
            ->selectRaw('exportpk, COUNT(*) n')->groupBy('exportpk')->pluck('n', 'exportpk');
        foreach ($done as $id => $n) {
            $out[$id] = "{$n} carton sudah Complete Shipment (Uncomplete dulu)";
        }
        if (in_array('gate_out_at', Schema::getColumnListing('cont'), true)) {
            $left = DB::table('cont')->whereIn('exportpk', $ids)->whereNotNull('gate_out_at')
                ->selectRaw('exportpk, COUNT(*) n')->groupBy('exportpk')->pluck('n', 'exportpk');
            foreach ($left as $id => $n) {
                $out[$id] = $out[$id] ?? "{$n} container sudah keluar pabrik";
            }
        }
        return $out;
    }

    // export.buyer bisa berisi beberapa buyer dipisah koma ("PERRY ELLIS,PE NIKE") -> ['PERRY ELLIS', 'PE NIKE']
    private function buyerList($buyer): array
    {
        return array_values(array_unique(array_filter(array_map('trim', explode(',', (string) $buyer)), fn($b) => $b !== '')));
    }

    public function availablePo(Request $request, $exportpk)
    {
        $andon  = DB::connection('mysql_andon');
        $search = trim((string) $request->input('q', ''));

        $export = DB::table('export')->where('exportpk', $exportpk)->first(['exportpk', 'buyer']);
        if (!$export) {
            return response()->json(['success' => false, 'message' => 'Shipment plan tidak ditemukan.'], 404);
        }
        $buyer = trim((string) $export->buyer);
        // shipment bisa berisi beberapa buyer (export.buyer "PERRY ELLIS,PE NIKE"): PO dari salah satunya boleh ditambah
        $buyers = $this->buyerList($buyer);
        if (!$buyers) {
            return response()->json(['success' => false, 'message' => 'Buyer shipment plan ini belum terisi.'], 422);
        }

        $existing = [];
        foreach (DB::table('poexport')->where('exportpk', $exportpk)->get(['POno', 'OP']) as $r) {
            $existing[$r->POno . '|' . $r->OP] = true;
        }

        // A) PO yang sudah punya carton bebas di pack
        $qa = $andon->table('pack as p')
            ->join('po', 'po.popk', '=', 'p.popk')
            ->whereNull('p.exportpk')
            ->whereIn('po.' . self::PACK_BUYER_COL, $buyers)
            ->groupBy('p.POno', 'p.OP', 'po.poref')
            ->selectRaw('p.POno, p.OP, po.poref, MAX(po.' . self::PO_BUYER_COL . ') as buyer, COUNT(*) as ctn, SUM(p.pcsp) as qty')
            ->orderBy('p.OP', 'DESC')
            ->limit(300);

        // B) PO yang belum ada packnya sama sekali -> langsung dari tabel po
        $qb = $andon->table('po')
            ->whereIn('po.' . self::PO_BUYER_COL, $buyers)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('pack')->whereRaw('pack.popk = po.popk');
            })
            ->groupBy('po.POno', 'po.OP', 'po.poref')
            ->selectRaw('po.POno, po.OP, po.poref, MAX(po.' . self::PO_BUYER_COL . ') as buyer, 0 as ctn, 0 as qty')
            ->orderBy('po.OP', 'DESC')
            ->limit(300);

        if ($search !== '') {
            $like = '%' . $search . '%';

            $qa->where(function ($q) use ($like) {
                $q->where('p.POno', 'like', $like)
                    ->orWhere('p.OP', 'like', $like);
            });

            $qb->where(function ($q) use ($like) {
                $q->where('po.POno', 'like', $like)
                    ->orWhere('po.OP', 'like', $like);
            });
        }

        $rows = [];
        $seen = [];
        foreach ([$qa->get(), $qb->get()] as $set) {      // A dulu, B hanya yang belum muncul di A
            foreach ($set as $r) {
                $key = $r->POno . '|' . $r->OP;
                if (isset($seen[$key])) continue;
                $seen[$key] = true;
                // PO yang sudah ada di shipment ini tetap ditampilkan sebagai penanda
                // (tidak bisa dipilih lagi), PO di shipment lain tetap bisa dipilih (partial)
                $r->in_this = isset($existing[$key]);
                $rows[] = $r;
            }
        }

        // ID shipment plan lain yang sudah memuat PO ini (mis. partial shipment),
        // shipment yang sudah di-cancel tidak dihitung
        $shipplanMap = [];
        $ponos = collect($rows)->pluck('POno')->filter()->unique()->values();
        if ($ponos->isNotEmpty()) {
            DB::table('poexport')
                ->join('export', 'export.exportpk', '=', 'poexport.exportpk')
                ->whereIn('poexport.POno', $ponos->all())
                ->where(function ($q) {
                    $q->whereNull('export.stscancel')->orWhere('export.stscancel', '<>', 1);
                })
                ->orderBy('poexport.exportpk')
                ->get(['poexport.POno', 'poexport.OP', 'poexport.exportpk'])
                ->each(function ($r) use (&$shipplanMap) {
                    $shipplanMap[$r->POno . '|' . $r->OP][] = 'SP' . str_pad($r->exportpk, 4, '0', STR_PAD_LEFT);
                });
        }

        foreach ($rows as $r) {
            $r->buyer = trim((string) $r->buyer) ?: $buyer;
            $r->shipplans = array_values(array_unique($shipplanMap[$r->POno . '|' . $r->OP] ?? []));
        }

        return response()->json([
            'success' => true,
            'buyer'   => implode(', ', $buyers),
            'idship'  => 'SP' . str_pad($export->exportpk, 4, '0', STR_PAD_LEFT),
            'data'    => $rows,
        ]);
    }

    /**
     * POST /export/po-add
     * body: exportpk, items[] = {POno, OP, poref}
     */
    public function addPo(Request $request)
    {
        $this->validate($request, [
            'exportpk'      => 'required|integer',
            'items'         => 'required|array|min:1',
            'items.*.POno'  => 'required|string',
            'items.*.OP'    => 'nullable|string',
            'items.*.poref' => 'nullable|string',
        ]);

        // poref boleh kosong: cocokkan dengan poref NULL / '' di tabel po
        $wherePoref = function ($q, $poref) {
            if ($poref === '') {
                $q->where(function ($w) {
                    $w->whereNull('poref')->orWhere('poref', '');
                });
            } else {
                $q->where('poref', $poref);
            }
            return $q;
        };

        $exportpk = (int) $request->input('exportpk');
        $items    = $request->input('items');
        $andon    = DB::connection('mysql_andon');

        $export = DB::table('export')->where('exportpk', $exportpk)->first();
        if (!$export) {
            return response()->json(['success' => false, 'message' => 'Shipment plan tidak ditemukan.'], 404);
        }
        if (!empty($export->stscancel) || !empty($export->lockship)) {
            return response()->json(['success' => false, 'message' => 'Shipment plan sudah di-lock / dibatalkan, PO tidak bisa ditambahkan.'], 422);
        }

        DB::beginTransaction();

        try {
            DB::table('export')->where('exportpk', $exportpk)->lockForUpdate()->first();

            $addedPo    = [];
            $addedPopks = [];
            $totCtn     = 0;
            $totQty  = 0;
            $skipped = [];

            $buyers = $this->buyerList($export->buyer);   // bisa lebih dari satu buyer (dipisah koma)
            if (!$buyers) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Buyer shipment plan ini belum terisi.'], 422);
            }

            foreach ($items as $it) {
                $pono  = trim($it['POno']);
                $op    = isset($it['OP']) ? trim($it['OP']) : '';
                $poref = trim((string) ($it['poref'] ?? ''));

                $dupe = DB::table('poexport')
                    ->where('exportpk', $exportpk)->where('POno', $pono)->where('OP', $op)
                    ->exists();
                if ($dupe) {
                    $skipped[] = $pono . ' (sudah ada di shipment plan ini)';
                    continue;
                }

                // PO harus ada di tabel po dengan buyer yang sama
                $poOk = $wherePoref($andon->table('po'), $poref)
                    ->where('POno', $pono)->where('OP', $op)
                    ->whereIn('po.' . self::PO_BUYER_COL, $buyers)
                    ->exists();

                if (!$poOk) {
                    $skipped[] = $pono . ' (PO tidak ditemukan / beda buyer)';
                    continue;
                }

                // tabel pack hanya dibaca untuk total ctn/qty, tidak diubah --
                // pack.exportpk diisi oleh sistem Packing saat carton di-load
                $cartons = $andon->table('pack')
                    ->whereNull('exportpk')
                    ->where('POno', $pono)
                    ->where('OP', $op)
                    ->whereIn('popk', function ($q) use ($poref, $buyers, $wherePoref) {
                        $q->select('popk')->from('po')
                            ->whereIn(self::PO_BUYER_COL, $buyers);   // buyer dari tabel po
                        $wherePoref($q, $poref);
                    })
                    ->selectRaw('COUNT(*) as ctn, COALESCE(SUM(pcsp),0) as qty')
                    ->first();

                $ctn = (int) $cartons->ctn;
                $qty = (int) $cartons->qty;

                DB::table('poexport')->insert([
                    'exportpk' => $exportpk,
                    'POno'     => $pono,
                    'OP'       => $op,
                    'poref'    => $poref,
                    'qty'      => $qty,
                ]);

                $addedPopks = array_merge(
                    $addedPopks,
                    $wherePoref($andon->table('po'), $poref)
                        ->where('POno', $pono)->where('OP', $op)
                        ->whereIn(self::PO_BUYER_COL, $buyers)
                        ->pluck('popk')->all()
                );

                $addedPo[] = $pono;
                $totCtn   += $ctn;
                $totQty   += $qty;
            }

            if (empty($addedPo)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada PO yang ditambahkan. ' . implode('; ', $skipped),
                ], 422);
            }

            // 3) rebuild export.POno / export.OP dari poexport
            $sep = (strpos((string) $export->POno, ', ') !== false) ? ', ' : ',';

            $rows  = DB::table('poexport')->where('exportpk', $exportpk)->orderBy('poexportpk')->get(['POno', 'OP']);
            $ponos = [];
            $ops   = [];
            foreach ($rows as $r) {
                if ($r->POno !== null && $r->POno !== '') $ponos[] = $r->POno;
                if ($r->OP   !== null && $r->OP   !== '') $ops[]   = $r->OP;
            }

            DB::table('export')->where('exportpk', $exportpk)->update([
                'POno'     => implode($sep, array_values(array_unique($ponos))),
                'OP'       => implode($sep, array_values(array_unique($ops))),
                'totctn'   => DB::raw('COALESCE(totctn,0) + ' . (int) $totCtn),
                'qty_list' => DB::raw('COALESCE(qty_list,0) + ' . (int) $totQty),
            ]);

            $this->syncExportPopks($exportpk, $addedPopks);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($addedPo) . ' PO ditambahkan (' . implode(', ', $addedPo) . ').'
                    . (empty($skipped) ? '' : ' Dilewati: ' . implode('; ', $skipped)),
                'added'   => $addedPo,
                'skipped' => $skipped,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menambah PO: ' . $e->getMessage()], 500);
        }
    }
}
