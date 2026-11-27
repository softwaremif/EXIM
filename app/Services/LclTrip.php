<?php

namespace App\Services;

use App\Http\Controllers\Security\GateController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Truk LCL dipakai beberapa shipment plan (lihat GateController::TRIP_COLUMN).
 * Grup truk ditentukan Manager EXIM di daftar shipment plan (ShipplanController::truckSave / truckRelease);
 * Security Gate mengisi data truk sekali di SP induk, lalu sync() menyalinnya ke semua SP anggota supaya
 * EXIM / Packing yang membaca per contpk tetap benar.
 */
class LclTrip
{
    private static $ready = null;

    public static function ready(): bool
    {
        return self::$ready ?? (self::$ready = Schema::hasColumn('cont', GateController::TRIP_COLUMN));
    }

    // kolom data truk yang disalin dari SP induk ke SP anggota (gate_transporter per SP, lihat sync)
    public static function truckColumns(): array
    {
        $cols = array_diff(array_merge(['actcontdate'], GateController::GATE_COLUMNS, GateController::EDIT_COLUMNS,
            [GateController::SIGN_COLUMN, GateController::OUT_MEDIA_COLUMN, GateController::IN_GUARDS_COLUMN], GateController::STUFF_COLUMNS),
            ['gate_transporter']);
        return array_values(array_intersect($cols, Schema::getColumnListing('cont')));
    }

    /**
     * Salin data truk dari SP induk ke semua SP anggota. Jam buka / mulai muat yang masih kosong di induk
     * diambil dari SP anggota (diisi sistem stuffing per SP), paling awal. Angkutan = forwarder masing-masing SP.
     */
    public static function sync($leaderPk): void
    {
        if (!self::ready() || !$leaderPk) {
            return;
        }
        $col = GateController::TRIP_COLUMN;
        $rows = DB::table('cont')->leftJoin('fwd', 'fwd.fwdpk', '=', 'cont.fwdpk')
            ->where('cont.' . $col, (int) $leaderPk)->get(['cont.*', 'fwd.fwdnm']);
        $leader = $rows->firstWhere('contpk', (int) $leaderPk);
        if ($rows->count() < 2 || !$leader) {
            return;
        }
        $cols = self::truckColumns();
        $data = [];
        foreach ($cols as $c) {
            $data[$c] = $leader->{$c};
        }
        foreach (array_intersect(GateController::TRIP_EARLIEST, $cols) as $c) {
            $data[$c] = $leader->{$c} ?: $rows->pluck($c)->filter()->min();
        }
        DB::table('cont')->where($col, (int) $leaderPk)->update($data);

        if (!empty($leader->gate_transporter)) {
            foreach ($rows as $r) {
                if ((int) $r->contpk !== (int) $leaderPk && empty($r->gate_transporter) && trim((string) $r->fwdnm) !== '') {
                    DB::table('cont')->where('contpk', $r->contpk)->update(['gate_transporter' => trim($r->fwdnm)]);
                }
            }
        }
    }

    // no. seal truk: seal per truk, ambil yang diisi EXIM di salah satu SP (SP induk dulu)
    public static function seal($c): ?string
    {
        $own = trim((string) ($c->noseal ?? ''));
        $trip = (int) ($c->{GateController::TRIP_COLUMN} ?? 0);
        if ($own !== '' || !self::ready() || $trip <= 0) {
            return $own !== '' ? $own : null;
        }
        $seal = DB::table('cont')->where(GateController::TRIP_COLUMN, $trip)->whereNotNull('noseal')->where('noseal', '<>', '')
            ->orderByRaw('contpk = ? DESC', [$trip])->orderBy('contpk')->value('noseal');
        return $seal ? trim($seal) : null;
    }

    // security sudah mulai mengisi truk ini (draft / foto / tiba)
    public static function hasGateData($cont): bool
    {
        return !empty($cont->actcontdate) || !empty($cont->gate_draft_at) || !empty($cont->gate_foto_id)
            || !empty($cont->gate_foto_cont) || !empty($cont->gate_video);
    }

    // alasan truk tidak bisa menerima SP baru (sudah keluar / ditolak / seal terpasang), null = boleh
    public static function closedReason($leader): ?string
    {
        if (!empty($leader->gate_out_at)) {
            return 'Truk sudah keluar MIF.';
        }
        if (isset($leader->gate_result) && $leader->gate_result !== null && (int) $leader->gate_result === 0) {
            return 'Truk ditolak security (tidak lulus pemeriksaan).';
        }
        $out = json_decode($leader->{GateController::OUT_MEDIA_COLUMN} ?? '[]', true) ?: [];
        if (!empty($leader->gate_seal_check) || !empty($leader->gate_close_at) || !empty($out['out_seal'])) {
            return 'Seal truk sudah dipasang.';
        }
        return null;
    }
}
