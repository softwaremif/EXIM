<?php

namespace App\Http\Middleware;

use App\Services\ShipplanAccess;
use Closure;
use Illuminate\Http\Request;

/**
 * Endpoint ubah shipment plan hanya untuk grup pembuat shipment & Manager EXIM (lihat ShipplanAccess).
 */
class ShipplanOwner
{
    public function handle(Request $request, Closure $next)
    {
        $denied = ShipplanAccess::deniedExportpks($request);
        if ($denied) {
            $ids = implode(', ', array_map(fn($id) => 'SP' . str_pad($id, 4, '0', STR_PAD_LEFT), $denied));
            return response()->json([
                'success' => false,
                'message' => "Shipment plan {$ids} dibuat oleh grup lain. Hanya grup pembuat dan Manager EXIM yang bisa mengubahnya.",
            ], 403);
        }

        return $next($request);
    }
}
