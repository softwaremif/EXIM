<?php

namespace App\Http\Controllers\Security;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

/**
 * Lokasi stuffing di tablet Security Gate (table cont_stuffing, lihat GateController::STUFFING_TABLE):
 *  - aktifkan  container masuk ke pintu di pos MIF security (juga "terima" container pindahan)
 *  - pindah    container pindah ke MIF lain, cukup dicatat jamnya (dari pintu, atau dari area tanpa pintu)
 *  - batal     salah pilih pintu: kunjungan ditutup tanpa dianggap pindah
 * Keluar pabrik (gate out) menutup kunjungan otomatis di GateController::checkout.
 */
class GateDoorController extends GateController
{
    // halaman Pintu (ikon di header): papan pintu per MIF, container menuju / menunggu pintu, riwayat hari ini
    public function board()
    {
        \Carbon\Carbon::setLocale('id');
        abort_unless($this->doorsReady(), 503, 'Table lokasi stuffing (cont_stuffing) belum ada. Hubungi IT.');
        $pos = $this->posMif();
        $board = $this->doorBoard();
        // MIF pos security tampil paling depan
        $mifs = array_keys(self::DOORS);
        if ($pos !== null) {
            usort($mifs, fn($a, $b) => ($b === $pos) <=> ($a === $pos));
        }

        return view('menu.exim-export.security.dock', [
            'board'   => $board,
            'mifs'    => $mifs,
            'posMif'  => $pos,
            'history' => $this->dockHistory(),
            'login'   => Session::get('login'),
        ]);
    }

    public function activate(Request $request, $contpk)
    {
        $cont = $this->stuffableCont($contpk);
        $v = $request->validate([
            'mif'   => ['required', 'integer', Rule::in(array_keys(self::DOORS))],
            'pintu' => 'required|integer|min:1',
        ]);
        $mif = (int) $v['mif'];
        $pintu = (int) $v['pintu'];
        abort_if($pintu > self::DOORS[$mif], 422, "MIF {$mif} hanya punya " . self::DOORS[$mif] . ' pintu.');
        $this->ensurePos($mif);

        $active = DB::table(self::STUFFING_TABLE)->where('contpk', $cont->contpk)->where('aktif', 1)->first();
        if ($active) {
            abort(422, "Container ini masih aktif di MIF {$active->mif} pintu {$active->pintu}. Pindahkan atau batalkan dulu.");
        }
        $this->ensureDoorFree($mif, $pintu);

        try {
            DB::table(self::STUFFING_TABLE)->insert([
                'contpk'       => $cont->contpk,
                'exportpk'     => $cont->exportpk,
                'mif'          => $mif,
                'pintu'        => $pintu,
                'masuk_at'     => now(),
                'masuk_userpk' => Session::get('userpk'),
                'status'       => 'aktif',
                'aktif'        => 1,
            ]);
        } catch (QueryException $e) {
            // kunci unik: tablet lain baru saja mengisi pintu ini / container ini
            if (($e->errorInfo[0] ?? null) === '23000') {
                $this->ensureDoorFree($mif, $pintu);
                abort(422, 'Container ini baru saja diaktifkan di pintu lain. Muat ulang halaman.');
            }
            throw $e;
        }

        return response()->json(['success' => true, 'message' => "Container aktif di MIF {$mif} pintu {$pintu}."]);
    }

    public function move(Request $request, $contpk)
    {
        $cont = $this->stuffableCont($contpk);
        $v = $request->validate([
            'tujuan_mif' => ['required', 'integer', Rule::in(array_keys(self::DOORS))],
            'catatan'    => 'nullable|string|max:255',
        ], ['catatan.max' => 'Catatan maksimal 255 karakter.']);
        $state = $this->stuffingState($cont);
        $from = $state->mif ?? $this->posMif();
        abort_if($from === null, 422, 'Posisi container belum diketahui. Login dengan akun security pos MIF.');
        abort_if((int) $v['tujuan_mif'] === $from, 422, "Container sudah di MIF {$from}.");
        $this->ensurePos($from);

        $note = isset($v['catatan']) && trim($v['catatan']) !== '' ? trim($v['catatan']) : null;
        $close = ['tujuan_mif' => (int) $v['tujuan_mif'], 'catatan' => $note];
        if ($state->active) {
            $this->closeVisit($cont->contpk, 'pindah', now(), $close);
        } else {
            // belum masuk pintu (mis. barang belum siap): dicatat di area factory (pintu 0) lalu pindah
            $since = $state->transit ? $state->last->keluar : ($cont->actcontdate ?: now());
            DB::table(self::STUFFING_TABLE)->insert($close + [
                'contpk'        => $cont->contpk,
                'exportpk'      => $cont->exportpk,
                'mif'           => $from,
                'pintu'         => 0,
                'masuk_at'      => $since,
                'masuk_userpk'  => Session::get('userpk'),
                'keluar_at'     => now(),
                'keluar_userpk' => Session::get('userpk'),
                'status'        => 'pindah',
                'aktif'         => null,
            ]);
        }

        return response()->json(['success' => true, 'message' => "Container dicatat pindah dari MIF {$from} ke MIF {$v['tujuan_mif']}."]);
    }

    public function cancel(Request $request, $contpk)
    {
        $cont = $this->stuffableCont($contpk);
        $active = DB::table(self::STUFFING_TABLE)->where('contpk', $cont->contpk)->where('aktif', 1)->first();
        abort_unless($active, 422, 'Container ini tidak sedang di pintu.');
        $this->ensurePos((int) $active->mif);
        $this->closeVisit($cont->contpk, 'batal', now());

        return response()->json(['success' => true, 'message' => "Pintu {$active->pintu} MIF {$active->mif} dikosongkan."]);
    }

    // container sudah gate in, lulus pemeriksaan, belum keluar pabrik
    protected function stuffableCont($contpk)
    {
        abort_unless($this->doorsReady(), 503, 'Table lokasi stuffing (cont_stuffing) belum ada. Hubungi IT.');
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat (gate in).');
        abort_if(!empty($cont->gate_out_at), 422, 'Container ini sudah keluar pabrik.');
        abort_if($this->isRejected($cont), 422, 'Container tidak lulus pemeriksaan, tidak ada stuffing.');
        return $cont;
    }

    // security hanya mencatat untuk pos MIF-nya sendiri (akun lain bebas)
    protected function ensurePos(int $mif): void
    {
        $pos = $this->posMif();
        abort_if($pos !== null && $pos !== $mif, 403, "Akun ini untuk pos MIF {$pos}. Container ini dicatat oleh security MIF {$mif}.");
    }

    protected function ensureDoorFree(int $mif, int $pintu): void
    {
        $busy = DB::table(self::STUFFING_TABLE . ' as s')->join('cont', 'cont.contpk', '=', 's.contpk')
            ->where('s.mif', $mif)->where('s.pintu', $pintu)->where('s.aktif', 1)
            ->first(['cont.contpk', 'cont.contno', 'cont.type', 'cont.nopol']);
        abort_if($busy, 422, "Pintu {$pintu} MIF {$mif} sedang dipakai " . ($busy ? $this->identOf($busy) : '') . '. Pilih pintu lain.');
    }
}
