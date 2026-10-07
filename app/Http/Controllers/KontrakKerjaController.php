<?php

namespace App\Http\Controllers;

use App\Models\Kk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Helpers\GenerateNumber;
use Illuminate\Support\Facades\DB;

class KontrakKerjaController extends Controller
{

    public function index()
    {
        $subkon = DB::table('subkon')
            ->orderBy('subnm')
            ->get();

        return view('menu.kontrakkerja.index', compact('subkon'));
    }


    public function list(Request $request)
    {
        // Ambil data login user
        $guserid = $request->guserid;
        $userpk  = $request->userpk;

        // Filter bulan dan tahun
        $month = $request->filterByMonth ?? date('m');
        $year  = $request->filterByYear ?? date('Y');
        $sort  = $request->sortlistByDate;
        $search = trim($request->search);

        // Pagination sederhana
        $page = (int) $request->input('page', 1);
        $rows = (int) $request->input('rows', 100);
        $offset = ($page - 1) * $rows;

        // Query KK dengan join ke tabel user
        $query = Kk::query()
            ->leftJoin('user as u', 'u.userpk', '=', 'kk.userpk')
            ->leftJoin('subkon', 'subkon.subkonpk', '=', 'kk.subkonpk')
            ->whereYear('kk.tglkk', $year)
            ->whereMonth('kk.tglkk', $month)
            ->select('kk.*', 'u.login as username', 'subkon.subnm as subkon_nama');

        // Jika bukan admin, batasi hanya data user yang login
        if (!in_array($guserid, ['PPIC'])) {
            $query->where('kk.userpk', $userpk);
        }

        if (!empty($search)) {
            $query->whereRaw("
                CONCAT(
                    IFNULL(kk.kkno, ''), ' ',
                    IFNULL(kk.funm, ''), ' ',
                    IFNULL(kk.ketkk, ''), ' ',
                    IFNULL(subkon.subnm, ''), ' ',
                    IFNULL(u.login, '')
                ) LIKE ?
            ", ["%{$search}%"]);
        }

        // Sorting
        if ($sort == 11) {
            $query->orderBy('tglkk', 'asc')->orderBy('kkno', 'asc');
        } elseif ($sort == 12) {
            $query->orderBy('tglkk', 'desc')->orderBy('kkno', 'asc');
        } else {
            $query->orderBy('tglkk', 'desc')->orderBy('kkno', 'desc');
        }

        // Total data sebelum pagination
        $total = $query->count();

        // Ambil data sesuai page
        $list = $query->offset($offset)
            ->limit($rows)
            ->get();

        $rowsData = [];
        $no = $offset + 1;

        foreach ($list as $d) {
            $users = DB::connection('mysql_akses')->table('user')
                ->leftJoin('userdt', 'userdt.userpk', '=', 'user.userpk')
                ->leftJoin('guser', 'guser.guserpk', '=', 'user.guserpk')
                ->where(['user.userpk' => $d->userpk])
                ->first();
            // dd($users);
            $formatted_nomor = (strlen($d->kkno) < 3) ? str_pad($d->kkno, 3, '0', STR_PAD_LEFT) : $d->kkno;
            $kkno = $formatted_nomor . "/MIF-SUBKON/" . Carbon::parse($d->datecreate)->format('Y');

            $rowsData[] = [
                'no'           => $no++,
                'kkpk'         => $d->kkpk,
                'tglkk'        => Carbon::parse($d->tglkk)->format('d-m-Y'),
                'kkno'         => $kkno,
                'funm'         => $d->funm,
                'subkon'       => $d->subkon_nama,
                'ketkk'        => $d->ketkk,
                'stsmat'       => $d->stsmat,
                // 'stsmat_label' => $d->stsmat ? 'Iya / '+ $d->jmlmat : 'Tidak',
                'stsmat_label' => $d->stsmat ? "Iya / {$d->jmlmat} pcs" : 'Tidak',
                'jmlmat'       => $d->stsmat ? $d->jmlmat : null,
                'username'     => $users->login,
            ];
        }

        return response()->json([
            'total' => $total,
            'rows'  => $rowsData,
        ]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $guserid = $request->guserid;

            // $tglkk = now();

            $nomor = GenerateNumber::byCobaKk(now());

            $listkk = new Kk;
            $listkk->subkonpk = $request->subkonpk;
            $listkk->tglkk    = now();
            $listkk->kkno     = $nomor;
            $listkk->ketkk    = $request->ketkk;
            $listkk->funm     = $request->funm;
            $listkk->stsmat   = $request->stsmat;
            $listkk->jmlmat   = $request->jmlmat;

            if (!in_array($guserid, ['PPIC'])) {
                $listkk->userpk = session('userpk');
            }

            $listkk->save();

            return response()->json([
                'success' => true,
                'message' => 'Data Kontrak Kerja Created Successfully!',
                'data' => $listkk,
            ]);
        });
    }

    public function update(Request $request, $kkpk)
    {
        try {
            $kk = Kk::findOrFail($kkpk);

            $validated = $request->validate([
                'subkonpk' => 'required',
                'tglkk'    => 'required|date',
                'funm'     => 'required|string',
                'ketkk'    => 'nullable|string',
                'stsmat'   => 'nullable|boolean',
                'jmlmat'   => 'nullable|numeric|min:0',
                'userpk'   => 'nullable|string'
            ]);

            $stsmat = $request->boolean('stsmat');

            $kk->subkonpk = $validated['subkonpk'];
            $kk->tglkk    = \Carbon\Carbon::parse($validated['tglkk'])->format('Y-m-d');
            $kk->funm     = $validated['funm'];
            $kk->ketkk    = $validated['ketkk'] ?? null;
            $kk->stsmat   = $stsmat;
            $kk->jmlmat   = $stsmat ? ($validated['jmlmat'] ?? 0) : 0;

            if (!empty($validated['userpk'])) {
                $kk->userpk = $validated['userpk'];
            }

            $kk->save();

            return response()->json([
                'status'  => true,
                'message' => 'Kontrak kerja berhasil diperbarui',
                'data'    => ['kkpk' => $kk->kkpk]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Gagal update kontrak kerja',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function detail($kkpk)
    {
        $kk = Kk::findOrFail($kkpk);

        $users = DB::connection('mysql_akses')->table('user')
            ->leftJoin('userdt', 'userdt.userpk', '=', 'user.userpk')
            ->leftJoin('guser', 'guser.guserpk', '=', 'user.guserpk')
            ->where(['user.userpk' => $kk->userpk])
            ->first();

        $formatted_nomor = (strlen($kk->kkno) < 3) ? str_pad($kk->kkno, 3, '0', STR_PAD_LEFT) : $kk->kkno;
        $kkno = $formatted_nomor . "/MIF-SUBKON/" . Carbon::parse($kk->datecreate)->format('Y');

        return response()->json([
            'status' => true,
            'data' => [
                'kkpk'     => $kk->id,
                'tglkk'    => Carbon::parse($kk->tglkk)->format('Y-m-d'),
                'funm'     => $kk->funm,
                'kkno' => $kkno,
                'ketkk'    => $kk->ketkk,
                'subkonpk' => $kk->subkonpk,
                'stsmat'   => $kk->stsmat,
                'jmlmat'   => $kk->jmlmat,
                'username'     => $users->login,

            ]
        ]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'subnm' => 'required|string|max:255'
        ]);

        $existing = DB::table('subkon')
            ->whereRaw('LOWER(subnm) = ?', [strtolower($request->subnm)])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Sub kontrak sudah ada'
            ], 422);
        }

        $id = DB::table('subkon')->insertGetId([
            'subnm' => trim($request->subnm)
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'subkonpk' => $id,
                'subnm' => trim($request->subnm)
            ]
        ]);
    }

    public function listSubkon()
    {
        return DB::table('subkon')
            ->orderBy('subnm')
            ->get();
    }
}
