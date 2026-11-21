<?php

namespace App\Http\Middleware;

use App\Support\Realtime;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Setelah aksi tulis (POST/PUT/DELETE) yang berhasil dan mengubah container (Security Gate,
 * mulai / selesai stuffing, shipment plan, muat / bongkar container), colek halaman Shipment &
 * scanner packinglist serta Security Gate lain lewat websocket (event "cont").
 */
class PushRealtime
{
    private const PATHS = [
        'security/gate/*',
        'api/updateShipment',
        'containers/*',
        'export/*/containers',
        'exim-export/set_shipplan',
        'shipment-plan/*',
    ];

    // upload foto / video dikirim per potongan -- tidak mengubah status container
    private const SKIP = [
        'security/gate/*/upload',
        'security/gate/*/media-delete',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if ($request->isMethodSafe() || $response->getStatusCode() >= 400
                || !$request->is(...self::PATHS) || $request->is(...self::SKIP) || !Realtime::enabled()) {
                return $response;
            }
            if ($response instanceof JsonResponse) {
                $d = $response->getData(true);
                if (is_array($d) && (($d['success'] ?? null) === false || ($d['ok'] ?? null) === false || ($d['status'] ?? null) === 'error')) {
                    return $response;
                }
            }
            $route    = $request->route();
            $contpk   = (int) (($route ? $route->parameter('contpk') : null) ?: $request->input('contpk'));
            $exportpk = (int) (($route ? $route->parameter('exportpk') : null) ?: $request->input('exportpk'));
            if (!$exportpk && $contpk) {
                $exportpk = (int) DB::table('cont')->where('contpk', $contpk)->value('exportpk');
            }
            Realtime::push('cont', [
                'exportpk' => $exportpk ?: null,
                'contpk'   => $contpk ?: null,
                'by'       => Realtime::by(),
            ]);
        } catch (\Throwable $e) {
            // colekan realtime tidak boleh menggagalkan aksi
        }

        return $response;
    }
}
