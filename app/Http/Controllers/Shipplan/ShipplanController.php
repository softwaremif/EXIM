<?php

namespace App\Http\Controllers\Shipplan;

use App\Exports\ShipmentPackingListExport;
use App\Exports\ShipmentPlanExport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Models\Container;
use App\Models\Export;
use App\Models\Exportdt;
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

        $loadedCartons = collect();

        foreach ($container as $c) {

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
            compact('header', 'container', 'exportpk', 'loadedCartons', 'total')
        );
    }

    public function getContainerSummary($exportpk, $contpk = null)
    {
        //  query LANGSUNG ke mysql_andon
        // (database Packing). SEKARANG delegasikan ke API Packing.
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)
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
                'nopol'         => $c['nopol'] ?? null,
                'noseal'        => $c['noseal'] ?? null,
                'actcontdate'   => $actcontdate,
                'fwdpk'         => $c['fwd'] ?? $data['fwd'] ?? null,
                'vessname'      => $c['vessname'] ?? null,
                'remark'        => null,
            ];

            if (!empty($type)) {
                $payload['type']   = $type;
                $payload['volume'] = $cbm;
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
            $response = \Illuminate\Support\Facades\Http::timeout(15)
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

            return response()->json($response->json());
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

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60) //  
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

    private function getLoadedCartonsFromPacking(int $exportpk, int $contpk)
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
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
            $response = \Illuminate\Support\Facades\Http::timeout(60) //  
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

    public function detail_json($exportpk)
    {
        $export = DB::table('export')->where('exportpk', $exportpk)->first();

        if (!$export) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $containers = DB::table('cont')
            ->where('exportpk', $exportpk)
            ->select(
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
                'actcontdate'
            )
            ->get()
            ->map(function ($c) {
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
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->get($this->packingBaseUrl() . "/api/packing/container-progress/{$contpk}");

            if (!$response->successful()) {
                $body = $response->json();
                return response()->json([
                    'cartons' => [],
                    'message' => $body['message'] ?? 'Gagal memuat data progres dari sistem Packing.',
                ], $response->status());
            }

            return response()->json($response->json());
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
                'poexport.pebno',
                'poexport.invs',
                'export.buyer',
                // 'export.pebno',
                'export.nobook',
                'export.stscancel',
                'export.lockship',
                'export.exdate as extdate',
                // 'export.actexdate', // dibutuhkan buat hitung status_label
                'export.GAC',
                'export.contdate',
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
            ")
            )
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
            $query->where(function ($q) use ($search) {
                $q->where('export.ordpk', 'like', "%{$search}%")
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
            // CANCEL SHIPMENT
            $query->where('export.stscancel', 1);
        }

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

        $query->orderBy(
            'export.datecreate',
            $sortlistByDate == 12 ? 'asc' : 'desc'
        );

        return $query;
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

        return $result->values()->map(function ($row, $index) use ($ordMap, $packMap, $mifMap, $offset) {
            $ord = $ordMap->get($row->ordpk);
            $pack = $packMap->get($row->POno);

            $row->mif = $mifMap->get($row->POno . '|' . $row->OP) ?: 'MIF 2';

            $row->no = $offset + $index + 1;
            $row->ordno = !empty($row->OP) ? $row->OP : ($ord->ordno ?? '-');
            $row->style = $ord->artno ?? '-';
            $row->season = $ord->season ?? '-';
            $row->idship = 'SP' . str_pad($row->exportpk, 4, '0', STR_PAD_LEFT);
            $row->part = $pack->part ?? '-';

            // dipakai di kolom "Status" export Excel
            $row->status_label = $this->resolveStatusLabel($row);

            return $row;
        });
    }

    // turunkan label status dari data row (bukan dari parameter filter),
    // supaya export "semua status" tetap menampilkan status masing-masing baris
    private function resolveStatusLabel($row)
    {
        $isCancelled = (int) ($row->stscancel ?? 0) === 1;

        if ($isCancelled) {
            return 'Cancel Shipment';
        }

        if (!empty($row->is_segel)) {
            return 'Complete';
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

    public function exportSI(Request $request, $exportpk)
    {
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
        $groups = $packRows->groupBy(function ($row) {
            return ($row->POno ?? '') . '|' . ($row->OP ?? '');
        })->map(function ($rows) use ($poMap, $cbmOf) {
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
                'cbm' => (float) $rows->sum($cbmOf),
            ];
        })->values();

        $totalCartons = $groups->sum('cartons');
        $totalPcs = $groups->sum('pcs');
        $totalGw = (float) $packRows->sum('gw');
        $totalNw = (float) $packRows->sum('nw');
        $totalCbm = (float) $packRows->sum($cbmOf);

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

    public function availablePo(Request $request, $exportpk)
    {
        $andon  = DB::connection('mysql_andon');
        $search = trim((string) $request->input('q', ''));

        $export = DB::table('export')->where('exportpk', $exportpk)->first(['exportpk', 'buyer']);
        if (!$export) {
            return response()->json(['success' => false, 'message' => 'Shipment plan tidak ditemukan.'], 404);
        }
        $buyer = trim((string) $export->buyer);
        if ($buyer === '') {
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
            ->where('po.' . self::PACK_BUYER_COL, $buyer)
            ->groupBy('p.POno', 'p.OP', 'po.poref')
            ->selectRaw('p.POno, p.OP, po.poref, COUNT(*) as ctn, SUM(p.pcsp) as qty')
            ->orderBy('p.OP', 'DESC')
            ->limit(300);

        // B) PO yang belum ada packnya sama sekali -> langsung dari tabel po
        $qb = $andon->table('po')
            ->where('po.' . self::PO_BUYER_COL, $buyer)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('pack')->whereRaw('pack.popk = po.popk');
            })
            ->groupBy('po.POno', 'po.OP', 'po.poref')
            ->selectRaw('po.POno, po.OP, po.poref, 0 as ctn, 0 as qty')
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
                if (isset($existing[$key]) || isset($seen[$key])) continue;
                $seen[$key] = true;
                $rows[] = $r;
            }
        }

        return response()->json(['success' => true, 'buyer' => $buyer, 'data' => $rows]);
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
            'items.*.poref' => 'required|string',
        ]);

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
        $andon->beginTransaction();

        try {
            DB::table('export')->where('exportpk', $exportpk)->lockForUpdate()->first();

            $addedPo = [];
            $totCtn  = 0;
            $totQty  = 0;
            $skipped = [];

            $buyer = trim((string) $export->buyer);
            if ($buyer === '') {
                DB::rollBack();
                $andon->rollBack();
                return response()->json(['success' => false, 'message' => 'Buyer shipment plan ini belum terisi.'], 422);
            }

            foreach ($items as $it) {
                $pono  = trim($it['POno']);
                $op    = isset($it['OP']) ? trim($it['OP']) : '';
                $poref = trim($it['poref']);

                $dupe = DB::table('poexport')
                    ->where('exportpk', $exportpk)->where('POno', $pono)->where('OP', $op)
                    ->exists();
                if ($dupe) {
                    $skipped[] = $pono . ' (sudah ada di shipment plan ini)';
                    continue;
                }

                $cartons = function () use ($andon, $pono, $op, $poref, $buyer) {
                    return $andon->table('pack')
                        ->whereNull('exportpk')
                        ->where('POno', $pono)
                        ->where('OP', $op)
                        ->whereIn('popk', function ($q) use ($poref, $buyer) {
                            $q->select('popk')->from('po')
                                ->where('poref', $poref)
                                ->where(self::PO_BUYER_COL, $buyer);   // buyer dari tabel po
                        });
                };

                $ctn = $cartons()->count();
                $qty = 0;

                if ($ctn > 0) {
                    $qty = (int) $cartons()->sum('pcsp');
                    $cartons()->update(['exportpk' => $exportpk]);   // assign carton ke shipment plan
                } else {
                    // belum ada carton bebas: valid hanya kalau PO ada di tabel po (buyer sama)
                    // dan memang belum punya pack sama sekali
                    $poOk = $andon->table('po')
                        ->where('poref', $poref)->where('POno', $pono)->where('OP', $op)
                        ->where('po.' . self::PO_BUYER_COL, $buyer)
                        ->exists();

                    $hasAnyPack = $andon->table('pack')
                        ->where('POno', $pono)->where('OP', $op)
                        ->whereIn('popk', function ($q) use ($poref) {
                            $q->select('popk')->from('po')->where('poref', $poref);
                        })
                        ->exists();

                    if (!$poOk) {
                        $skipped[] = $pono . ' (PO tidak ditemukan / beda buyer)';
                        continue;
                    }
                    if ($hasAnyPack) {
                        $skipped[] = $pono . ' (carton sudah dipakai shipment plan lain)';
                        continue;
                    }
                }

                DB::table('poexport')->insert([
                    'exportpk' => $exportpk,
                    'POno'     => $pono,
                    'OP'       => $op,
                    'poref'    => $poref,
                    'qty'      => $qty,
                ]);

                $addedPo[] = $pono;
                $totCtn   += $ctn;
                $totQty   += $qty;
            }

            if (empty($addedPo)) {
                DB::rollBack();
                $andon->rollBack();
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

            DB::commit();
            $andon->commit();

            return response()->json([
                'success' => true,
                'message' => count($addedPo) . ' PO ditambahkan (' . implode(', ', $addedPo) . ').'
                    . (empty($skipped) ? '' : ' Dilewati: ' . implode('; ', $skipped)),
                'added'   => $addedPo,
                'skipped' => $skipped,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $andon->rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menambah PO: ' . $e->getMessage()], 500);
        }
    }
}
