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
use Carbon\Carbon;

class ShipplanController extends Controller
{
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

        $this->addOrderImageToRows($result);
        $this->addUsernameToRows($result);

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

    private function addUsernameToRows($rows): void
    {
        // ambil semua userpk unik dari hasil query, buang null/kosong
        $userpks = collect($rows)
            ->pluck('userpk')
            ->filter()
            ->unique()
            ->values();

        if ($userpks->isEmpty()) {
            return;
        }

        // satu query untuk semua userpk sekaligus, hindari N+1
        $users = DB::connection('mysql_akses')
            ->table('user')
            ->whereIn('userpk', $userpks)
            ->pluck('login', 'userpk'); // hasil: [userpk => username, ...]

        foreach ($rows as $row) {
            $row->login = $users[$row->userpk] ?? null;
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
        // ambil semua baris pack terkait, sekaligus join carton_bundle untuk data bundle
        $packs = DB::connection('mysql_andon')->table('pack')
            ->leftJoin('carton_bundle', 'carton_bundle.bundlepk', '=', 'pack.bundlepk')
            ->where('pack.exportpk', $exportpk)
            ->when($contpk, fn($q) => $q->where('pack.contpk', $contpk))
            ->select(
                'pack.packpk',
                'pack.carton',
                'pack.bundlepk',
                'pack.POno',
                'pack.OP',
                'pack.pcsp',
                'pack.nw as pack_nw',
                'pack.gw as pack_gw',
                'pack.panjang as pack_panjang',
                'pack.lebar as pack_lebar',
                'pack.tinggi as pack_tinggi',
                'carton_bundle.nw as bundle_nw',
                'carton_bundle.gw as bundle_gw',
                'carton_bundle.panjang as bundle_panjang',
                'carton_bundle.lebar as bundle_lebar',
                'carton_bundle.tinggi as bundle_tinggi'
            )
            ->get();

        if ($packs->isEmpty()) {
            return [
                'totqty'  => 0,
                'totctn'  => 0,
                'totnw'   => 0,
                'totgw'   => 0,
                'totvol'  => 0,
            ];
        }

        /*
    |----------------------------------------------------------------
    | 3. TOTAL QTY — sum pcsp SEMUA baris pack, tanpa dedup sama sekali.
    | (tiap baris pcsp memang mewakili qty asli di carton itu, baik
    |  carton itu bagian dari bundle atau tidak, tidak boleh di-dedup)
    |----------------------------------------------------------------
    */
        $totqty = $packs->sum('pcsp');

        /*
    |----------------------------------------------------------------
    | GROUP jadi UNIT FISIK: 1 bundle = 1 unit, 1 carton non-bundle = 1 unit
    | (dedup baris duplikat kalau 1 carton punya beberapa baris pack,
    |  misal beda material/size dalam carton yang sama)
    |----------------------------------------------------------------
    */
        $units = $packs->groupBy(function ($pack) {
            if (!empty($pack->bundlepk)) {
                return 'BUNDLE|' . $pack->bundlepk;
            }
            // non-bundle: dedup berdasarkan carton+POno+OP, supaya carton dengan
            // nomor sama dari PO berbeda tidak ikut tergabung jadi 1 unit
            return 'CARTON|' . $pack->carton . '|' . $pack->POno . '|' . $pack->OP . '|' . ($pack->part ?? '-');
        });

        /*
    |----------------------------------------------------------------
    | 4. TOTAL CARTON — jumlah unit fisik (bundle dihitung 1, carton
    |    non-bundle dihitung 1 per nomor carton unik)
    |----------------------------------------------------------------
    */
        $totctn = $units->count();

        /*
    |----------------------------------------------------------------
    | 1 & 2. TOTAL NW, GW, VOLUME — per unit, ambil SATU SAJA nilainya
    | (bukan dijumlah dari semua baris duplikat dalam unit yang sama),
    | lalu baru dijumlah lintas unit.
    |----------------------------------------------------------------
    */
        $totnw = 0;
        $totgw = 0;
        $totvol = 0;

        foreach ($units as $unitKey => $rowsInUnit) {
            $first = $rowsInUnit->first();
            $isBundle = !empty($first->bundlepk);

            if ($isBundle) {
                // ambil dari carton_bundle (nw, gw, dan volume dari panjang/lebar/tinggi bundle)
                $nw = (float) ($first->bundle_nw ?? 0);
                $gw = (float) ($first->bundle_gw ?? 0);

                $panjang = $first->bundle_panjang;
                $lebar   = $first->bundle_lebar;
                $tinggi  = $first->bundle_tinggi;
            } else {
                // ambil dari pack langsung, SATU BARIS SAJA (first), meski ada beberapa
                // baris pack dengan carton sama (misal beda material/size)
                $nw = (float) ($first->pack_nw ?? 0);
                $gw = (float) ($first->pack_gw ?? 0);

                $panjang = $first->pack_panjang;
                $lebar   = $first->pack_lebar;
                $tinggi  = $first->pack_tinggi;
            }

            $vol = ($panjang !== null && $lebar !== null && $tinggi !== null)
                ? ($panjang * $lebar * $tinggi) / 1000000
                : 0;

            $totnw  += $nw;
            $totgw  += $gw;
            $totvol += $vol;
        }

        return [
            'totqty'  => $totqty,
            'totctn'  => $totctn,
            'totnw'   => round($totnw, 2),
            'totgw'   => round($totgw, 2),
            'totvol'  => round($totvol, 3),
        ];
    }

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
        $containers = $data['containers'];
        $totalContainer = count($containers);

        $insertRows = [];

        foreach ($containers as $index => $c) {
            if ($c['type'] == '20ft') {
                $cbm = 33;
            } else if ($c['type'] == '40ft') {
                $cbm = 67;
            } else if ($c['type'] == '40hc') {
                $cbm = 76;
            } else {
                $cbm = $c['volume'];
            }

            // nama field dari form adalah containers[i][actcontdate]
            $actcontdate = !empty($c['actcontdate'])
                ? Carbon::createFromFormat('d/m/Y H:i', $c['actcontdate'])->format('Y-m-d H:i')
                : null;

            $payload = [
                'exportpk'      => $data['exportpk'],
                'contno'        => $c['contno'],
                'nopol'         => $c['nopol'] ?? null,
                'noseal'        => $c['noseal'] ?? null,
                'actcontdate'   => $actcontdate,
                'type'          => $c['type'],
                'volume'        => $cbm ?? null,
                'fwdpk'         => $c['fwd'] ?? $data['fwd'] ?? null,
                'vessname'      => $c['vessname'] ?? null,
                'remark'        => null,
            ];

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

        // ============================================================
        // GET EXPORT
        // ============================================================
        $export = DB::table('export')
            ->where('exportpk', $exportpk)
            ->first();

        if (!$export) {
            return response()->json(['total' => 0, 'rows' => []]);
        }

        // ============================================================
        // SELECT
        // ============================================================
        $select = [
            'pack.packpk',
            'pack.popk',
            'pack.carton',
            'pack.bundlepk',
            'cb.bundle_carton',
            'pack.POno',
            'pack.OP',
            'pack.status',
            'pack.exportpk',
            'pack.material',
            'pack.part',
        ];
        for ($i = 1; $i <= 40; $i++) {
            $select[] = "po.size{$i}";
            $select[] = "pack.qtyp{$i}";
        }

        // ============================================================
        // QUERY — TANPA filter PO/OP/search di sini, ambil SEMUA data
        // dulu dalam scope popks, supaya bundle tidak terpotong
        // ============================================================
        $popks = $export->popks;
        if (!is_array($popks)) {
            $popks = explode(',', $popks);
        }

        $packs = DB::connection('mysql_andon')
            ->table('pack')
            ->leftJoin('po', 'po.popk', '=', 'pack.popk')
            ->leftJoin('carton_bundle as cb', 'cb.bundlepk', '=', 'pack.bundlepk')
            ->whereIn('pack.popk', $popks)
            ->select($select)
            ->get();

        // ============================================================
        // GROUP BY CARTON (atau BUNDLE kalau bundlepk terisi)
        // ============================================================
        $grouped = $packs->groupBy(function ($pack) {
            if (!empty($pack->bundlepk)) {
                return 'BUNDLE|' . $pack->bundlepk;
            }
            return $pack->carton . '|' . $pack->POno . '|' . $pack->OP . '|' . ($pack->part ?? '');
        });

        // ============================================================
        // FILTER DI LEVEL GRUP — kalau bundle, cukup SATU anggota cocok
        // filter maka SELURUH bundle tetap ditampilkan utuh
        // ============================================================
        if ($filterPo || $filterOrdno || $search) {
            $grouped = $grouped->filter(function ($cartonPacks) use ($filterPo, $filterOrdno, $search) {
                return $cartonPacks->contains(function ($pack) use ($filterPo, $filterOrdno, $search) {
                    $matchPo = !$filterPo || $pack->POno === $filterPo;
                    $matchOrdno = !$filterOrdno || $pack->OP === $filterOrdno;
                    $matchSearch = !$search
                        || str_contains((string) $pack->carton, $search)
                        || str_contains((string) $pack->bundle_carton, $search);

                    return $matchPo && $matchOrdno && $matchSearch;
                });
            });
        }

        $rows = [];

        foreach ($grouped as $groupKey => $cartonPacks) {

            $details = [];
            $packpks = [];
            $pcsp = 0;
            $smallCartons = [];

            $firstPack = $cartonPacks->first();
            $isBundle = !empty($firstPack->bundlepk);

            foreach ($cartonPacks as $pack) {
                if (!empty($pack->packpk)) {
                    $packpks[] = $pack->packpk;
                }

                if ($isBundle && !empty($pack->carton)) {
                    $smallCartons[] = [
                        'carton' => $pack->carton,
                        'POno'   => $pack->POno,
                        'OP'     => $pack->OP,
                    ];
                }

                for ($i = 1; $i <= 40; $i++) {
                    $size = $pack->{'size' . $i};
                    $qtyp = $pack->{'qtyp' . $i};

                    if ($size === null || $size === '') continue;
                    if ($qtyp === null || (float) $qtyp <= 0) continue;

                    $qty = (float) $qtyp;
                    $pcsp += $qty;

                    $details[] = [
                        'material' => $pack->material,
                        'size'     => $size,
                        'qty'      => $qty,
                        'packpk'   => $pack->packpk,
                        'carton'   => $pack->carton,
                        'POno'     => $pack->POno,
                        'OP'       => $pack->OP,
                    ];
                }
            }

            $packpks = array_values(array_unique($packpks));

            // dedup small cartons berdasarkan carton+POno+OP, urutkan natural
            $uniqueSmallCartons = collect($smallCartons)
                ->unique(fn($sc) => $sc['carton'] . '|' . $sc['POno'] . '|' . $sc['OP'])
                ->values()
                ->all();
            usort($uniqueSmallCartons, fn($a, $b) => strnatcasecmp((string) $a['carton'], (string) $b['carton']));

            if ($pcsp <= 0) {
                continue;
            }

            $displayCarton = $isBundle ? $firstPack->bundle_carton : $firstPack->carton;

            $rows[] = [
                'packpk'         => $packpks,
                'exportpk'       => $exportpk,
                'contpk'         => $contpk,
                'popk'           => $firstPack->popk,
                'carton'         => $displayCarton,
                'is_bundle'      => $isBundle,
                'bundle_cartons' => $isBundle ? $uniqueSmallCartons : [],
                'status'         => $firstPack->status,
                'exportdtpk'     => !empty($firstPack->exportpk) ? 1 : 0,
                'part'           => $firstPack->part,
                'POno'           => $firstPack->POno,
                'OP'             => $firstPack->OP,
                'pcsp'           => $pcsp,
                'details'        => $details,
            ];
        }

        usort(
            $rows,
            fn($a, $b) => strnatcasecmp((string) $a['carton'], (string) $b['carton'])
        );

        return response()->json([
            'total' => count($rows),
            'rows'  => $rows,
        ]);
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
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->post('http://192.168.0.8/packinglist_v2/api/packing/load-carton-to-container', [
                    'contpk'   => $validated['contpk'],
                    'exportpk' => $validated['exportpk'],
                    'packpk'   => $validated['packpk'],
                ]);

            if (!$response->successful()) {
                $body = $response->json();
                return response()->json([
                    'message' => $body['message'] ?? 'Gagal memuat carton ke container.',
                ], $response->status());
                // dd([
                //     'status' => $response->status(),
                //     'body'   => $response->body(),
                //     'json'   => $response->json(),
                // ]);
            }

            $body = $response->json();

            if (isset($body['totqty']) && isset($body['totctn']) && isset($body['totnw']) && isset($body['totgw']) && isset($body['totvol'])) {
                DB::table('export')
                    ->where('exportpk', $validated['exportpk'])
                    ->update([
                        'totqty' => $body['totqty'],
                        'totctn' => $body['totctn'],
                        'totnw' => $body['totnw'],
                        'totgw' => $body['totgw'],
                        'totvol' => $body['totvol'],
                    ]);
            }

            return response()->json($body);
        } catch (\Throwable $e) {
            // report($e);
            dd($e->getMessage());
            // return response()->json([
            //     'message' => 'Sistem Packing tidak dapat terhubung.',
            // ], 502);
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
                ->get('http://192.168.0.8/packinglist_v2/api/packing/loaded-cartons-by-container', [
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
            $response = \Illuminate\Support\Facades\Http::timeout(15)
                ->post('http://192.168.0.8/packinglist_v2/api/packing/unload-carton-from-container', $validated);

            if (!$response->successful()) {
                $body = $response->json();
                return response()->json([
                    'success' => false,
                    'message' => $body['message'] ?? 'Gagal mengeluarkan carton.',
                ], $response->status());
            }

            // FIX: dulu $body cuma di-set di cabang gagal, jadi blok update di bawah
            // tidak pernah jalan dan total header kembali ke angka lama setelah reload.
            $body = $response->json();

            if (isset($body['totqty']) && isset($body['totctn'])) {
                DB::table('export')
                    ->where('exportpk', $validated['exportpk'])
                    ->update([
                        'totqty' => $body['totqty'],
                        'totnw' => $body['totnw'],
                        'totgw' => $body['totgw'],
                        'totvol' => $body['totvol'],
                        'totctn' => $body['totctn'],
                    ]);
            }

            return response()->json($response->json());
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'Sistem Packing tidak dapat terhubung.',
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
            'OP'  => $export->OP,
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
        $cont = DB::table('cont')->where('contpk', $contpk)->first();
        if (!$cont) {
            return response()->json(['message' => 'Container tidak ditemukan.'], 404);
        }

        $packpks = DB::connection('mysql_andon')->table('pack')
            ->where('contpk', $contpk)
            ->pluck('packpk');

        if ($packpks->isEmpty()) {
            return response()->json(['cartons' => []]);
        }

        $ships = DB::connection('mysql_andon')->table('pack')
            ->leftJoin('carton_bundle as cb', 'cb.bundlepk', '=', 'pack.bundlepk')
            ->whereIn('pack.packpk', $packpks)
            ->select(
                'pack.packpk',
                'pack.carton',
                'pack.bundlepk',
                'cb.bundle_carton',
                'pack.POno',
                'pack.OP',
                'pack.pcsp',
                'pack.status',
                'pack.part'
            )
            ->get();

        $grouped = $ships->groupBy(function ($ship) {
            if (!empty($ship->bundlepk)) {
                return 'BUNDLE|' . $ship->bundlepk;
            }
            return $ship->carton . '|' . $ship->POno . '|' . $ship->OP;
        });

        $cartons = $grouped->map(function ($items) {
            $first = $items->first();
            $isBundle = !empty($first->bundlepk);

            $shipStatus = (int) $first->status;
            if ($shipStatus <= 5) {
                $status = 'processing';
            } elseif ($shipStatus <= 6) {
                $status = 'loaded';
            } else {
                $status = 'shipped';
            }

            $displayCarton = $isBundle ? $first->bundle_carton : $first->carton;

            $smallCartons = [];
            if ($isBundle) {
                // GANTI: sub-group berdasarkan carton + POno + OP, BUKAN cuma carton,
                // supaya 1 nomor carton kecil dengan PO/OP campuran tetap terpisah entri-nya
                $subGrouped = $items->groupBy(function ($it) {
                    return $it->carton . '|' . $it->POno . '|' . $it->OP;
                });

                foreach ($subGrouped as $subItems) {
                    $subFirst = $subItems->first();
                    $smallCartons[] = [
                        'carton' => $subFirst->carton,
                        'POno'   => $subFirst->POno,
                        'OP'     => $subFirst->OP,
                        'pcsp'   => $subItems->sum('pcsp'),
                    ];
                }

                // urutkan berdasarkan nomor carton kecil, natural sort
                usort($smallCartons, fn($a, $b) => strnatcasecmp((string) $a['carton'], (string) $b['carton']));
            }

            // cek apakah bundle ini berisi campuran PO/OP (berdasarkan sub-grouping di atas)
            $uniquePoOp = collect($smallCartons)->map(fn($sc) => $sc['POno'] . '|' . $sc['OP'])->unique();
            if ($isBundle && $uniquePoOp->count() > 1) {
                $displayPoNo = 'Multiple PO';
                $displayOp = '';
            } else {
                $displayPoNo = $first->POno;
                $displayOp = $first->OP;
            }

            return [
                'carton'         => $displayCarton,
                'POno'           => $displayPoNo,
                'OP'             => $displayOp,
                'pcsp'           => $items->sum('pcsp'),
                'status'         => $status,
                'is_bundle'      => $isBundle,
                'bundle_cartons' => $smallCartons,
            ];
        })->values();

        return response()->json(['cartons' => $cartons]);
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
                'poexport.poexportpk',
                'poexport.invs',
                'export.buyer',
                'export.pebno',
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
                'poexport.mif',
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

        if ($guserpk == 42) {
            $query->where('export.userpk', $userpk);
        }

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

        $exportpkList = $result->pluck('exportpk')->filter()->unique()->values();
        $packMap = collect();

        if ($exportpkList->isNotEmpty()) {
            $packRows = DB::connection('mysql_andon')
                ->table('pack')
                ->whereIn('exportpk', $exportpkList)
                ->select('exportpk', 'part')
                ->orderBy('packpk')
                ->get();

            $packMap = $packRows->groupBy('exportpk')->map(fn($items) => $items->first());
        }

        return $result->values()->map(function ($row, $index) use ($ordMap, $packMap, $offset) {
            $ord = $ordMap->get($row->ordpk);
            $pack = $packMap->get($row->exportpk);

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
}
