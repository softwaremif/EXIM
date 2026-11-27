<?php

namespace App\Http\Controllers\Security;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Lokasi stuffing di tablet Security Gate (table cont_stuffing, lihat GateController::STUFFING_TABLE):
 *  - aktifkan  container masuk ke pintu di pos MIF security (juga "terima" container pindahan)
 *  - pindah    container pindah ke MIF lain, cukup dicatat jamnya (dari pintu, atau dari area tanpa pintu)
 *  - batal     salah pilih pintu: kunjungan ditutup tanpa dianggap pindah
 *  - selesai   selesai muat: truk meninggalkan pintu (pintu kosong untuk truk lain), menunggu gate out di pabrik
 *  - keluar    keluar cepat dengan jam pilihan tanpa proses gate out, hanya urutan jam yang dicek (quickOut)
 * Keluar pabrik (gate out) menutup kunjungan otomatis di GateController::checkout.
 */
class GateDoorController extends GateController
{
    // alasan keluar cepat (halaman Pintu); "Lainnya" wajib diberi catatan
    const QUICK_OUT_REASONS = [
        'Truk keluar tanpa dicatat di sistem',
        'Tablet / sistem bermasalah saat truk keluar',
        'Pintu dibutuhkan truk lain',
        'Batal muat',
        'Lainnya',
    ];

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

    public function finish(Request $request, $contpk)
    {
        $cont = $this->stuffableCont($contpk);
        $active = DB::table(self::STUFFING_TABLE)->where('contpk', $cont->contpk)->where('aktif', 1)->first();
        abort_unless($active, 422, 'Container ini tidak sedang di pintu.');
        $this->ensurePos((int) $active->mif);
        $this->closeVisit($cont->contpk, 'selesai', now());

        return response()->json(['success' => true, 'message' => "Selesai muat. Pintu {$active->pintu} MIF {$active->mif} kosong, container menunggu gate out."]);
    }

    /**
     * Keluar cepat dari halaman Pintu: truk sudah masuk lewat sistem tapi keluar tanpa proses gate out, pintu perlu
     * dipakai truk lain. Hanya jam keluar yang dicek (urut setelah gate in / mulai muat / tutup container, tidak di masa
     * depan); data seal, foto/video & tanda tangan tidak diperiksa. Alasan (wajib) & catatan masuk riwayat lokasi.
     */
    public function quickOut(Request $request, $contpk)
    {
        abort_unless($this->gateColumnsReady(), 503, 'Kolom gate pada table cont belum lengkap. Hubungi IT.');
        $v = $request->validate([
            'gate_out_at' => 'required|date',
            'alasan'      => ['required', Rule::in(self::QUICK_OUT_REASONS)],
            'catatan'     => 'nullable|required_if:alasan,Lainnya|string|max:180',
        ], [
            'gate_out_at.required' => 'Pilih jam keluar.',
            'gate_out_at.date'     => 'Jam keluar tidak valid.',
            'alasan.required'      => 'Pilih alasan keluar.',
            'alasan.in'            => 'Alasan keluar tidak valid.',
            'catatan.required_if'  => 'Alasan Lainnya: isi catatan keluar.',
            'catatan.max'          => 'Catatan maksimal 180 karakter.',
        ]);
        $cont = $this->truckCont($contpk);
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat (gate in).');
        abort_if(!empty($cont->gate_out_at), 422, 'Container ini sudah tercatat keluar.');
        $last = $this->doorsReady()
            ? DB::table(self::STUFFING_TABLE)->where('contpk', $cont->contpk)->orderByDesc('stfpk')->first()
            : null;
        if ($last) {
            $this->ensurePos((int) $last->mif);
        }
        $at = $this->chosenExitTime($request, $cont);
        // riwayat lokasi: "Keluar cepat · <alasan>: <catatan>" (kolom catatan maks. 255)
        $text = trim(preg_replace('/\s+/', ' ', (string) ($v['catatan'] ?? '')));
        $note = Str::limit('Keluar cepat · ' . $v['alasan'] . ($text !== '' ? ': ' . $text : ''), 255, '');
        // posisi terakhir (untuk baris riwayat kalau truk tidak sedang di pintu)
        $mif = $last ? (int) ($last->status === 'pindah' && $last->tujuan_mif ? $last->tujuan_mif : $last->mif) : $this->posMif();

        DB::transaction(function () use ($cont, $at, $last, $note, $mif) {
            DB::table('cont')->where('contpk', $cont->contpk)->update(['gate_out_at' => $at, 'gate_out_userpk' => Session::get('userpk')]);
            $this->syncTrip($cont->contpk);
            // masih di pintu: kunjungan ditutup "keluar" dengan catatan; tidak di pintu (selesai muat / area factory):
            // baris riwayat baru "keluar" tanpa pintu supaya catatan tetap tercatat di riwayat lokasi
            if (!$this->closeVisit($cont->contpk, 'keluar', $at, ['catatan' => $note]) && $mif && $this->doorsReady()) {
                DB::table(self::STUFFING_TABLE)->insert([
                    'contpk'        => $cont->contpk,
                    'exportpk'      => $cont->exportpk,
                    'mif'           => $mif,
                    'pintu'         => 0,
                    'masuk_at'      => $last && $last->keluar_at ? $last->keluar_at : $at,
                    'masuk_userpk'  => Session::get('userpk'),
                    'keluar_at'     => $at,
                    'keluar_userpk' => Session::get('userpk'),
                    'status'        => 'keluar',
                    'catatan'       => $note,
                    'aktif'         => null,
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Keluar tersimpan (' . $at->isoFormat('D MMM HH:mm') . ').']);
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
