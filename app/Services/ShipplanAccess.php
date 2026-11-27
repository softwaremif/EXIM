<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Hak ubah shipment plan: hanya user dengan grup (guserpk) yang sama dengan pembuat shipment
 * (export.userpk -> mysql_akses.user.guserpk). Manager EXIM (MANAGER_USERPK) boleh mengubah semua.
 * Shipment tanpa data pembuat (data lama, export.userpk kosong) boleh diubah semua user EXIM.
 * Dipakai middleware 'shipplan.owner' (semua endpoint ubah shipment) & halaman detail_ship (mode lihat saja).
 */
class ShipplanAccess
{
    const MANAGER_USERPK = 563;

    private static $ownerGroups = [];

    // Manager EXIM (eko@ptmorich.com): satu-satunya yang boleh menyatukan / melepas SP LCL dalam satu truk
    public static function isManager(): bool
    {
        return (int) Session::get('userpk') === self::MANAGER_USERPK;
    }

    public static function canEdit($exportpk): bool
    {
        if ((int) Session::get('userpk') === self::MANAGER_USERPK) {
            return true;
        }
        $owner = self::ownerGroup($exportpk);
        return $owner === null || $owner === (int) Session::get('guserpk');
    }

    // grup pembuat shipment; null = tidak diketahui (shipment tidak ada / tanpa pembuat)
    public static function ownerGroup($exportpk): ?int
    {
        $exportpk = (int) $exportpk;
        if (!array_key_exists($exportpk, self::$ownerGroups)) {
            $userpk = DB::table('export')->where('exportpk', $exportpk)->value('userpk');
            $group = $userpk ? DB::connection('mysql_akses')->table('user')->where('userpk', $userpk)->value('guserpk') : null;
            self::$ownerGroups[$exportpk] = $group !== null ? (int) $group : null;
        }
        return self::$ownerGroups[$exportpk];
    }

    /**
     * Semua shipment yang disentuh request: route {exportpk} / {contpk}, input exportpk / contpk,
     * poexportpk (satu atau dipisah koma), sources[] (gabung shipment: shipment asal ikut diubah).
     */
    public static function exportpksFromRequest(Request $request): array
    {
        $ids = [];
        $add = function ($v) use (&$ids) {
            if (is_numeric($v) && (int) $v > 0) $ids[] = (int) $v;
        };

        $add($request->route('exportpk'));
        $add($request->input('exportpk'));
        foreach ((array) $request->input('sources', []) as $v) $add($v);

        $contpks = array_filter([(int) $request->route('contpk'), (int) $request->input('contpk')]);
        if ($contpks) {
            foreach (DB::table('cont')->whereIn('contpk', $contpks)->pluck('exportpk') as $v) $add($v);
        }

        $poexportpks = array_filter(array_map('intval', explode(',', (string) $request->input('poexportpk', ''))));
        if ($poexportpks) {
            foreach (DB::table('poexport')->whereIn('poexportpk', $poexportpks)->pluck('exportpk') as $v) $add($v);
        }

        return array_values(array_unique($ids));
    }

    // shipment yang tidak boleh diubah user ini (untuk pesan penolakan)
    public static function deniedExportpks(Request $request): array
    {
        return array_values(array_filter(self::exportpksFromRequest($request), fn($id) => !self::canEdit($id)));
    }
}
