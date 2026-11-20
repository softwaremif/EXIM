<?php

namespace App\Http\Controllers\Security;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

/**
 * Kalender ETA container (Security Gate): jadwal kedatangan container per hari
 * berdasarkan ETA shipment plan (export.contdate), lengkap dengan jam tiba aktual
 * (cont.actcontdate) dan jam keluar (cont.gate_out_at).
 * Status container sama dengan daftar gate (GateController::decorate).
 * ?embed=exim: tampil di modal halaman Shipment Plan EXIM (tanpa tombol kembali ke security,
 * link container ke Detail Shipment EXIM).
 */
class GateCalendarController extends GateController
{
    // kalender dibuka security & EXIM (modal Shipment Plan), semua akun melihat semua shipment plan;
    // login tetap wajib lewat middleware SessionVerified.
    // Tidak memakai batasan "khusus security" dari GateController::__construct.
    public function __construct()
    {
    }

    public function calendar(Request $request)
    {
        Carbon::setLocale('id');

        return view('menu.exim-export.security.calendar', [
            'month' => $this->monthParam($request)->format('Y-m'),
            'day'   => $this->dayParam($request),
            'view'  => in_array($request->query('view'), ['month', 'week', 'agenda'], true) ? $request->query('view') : 'month',
            'login' => Session::get('login'),
            'embed' => $this->embedExim($request),
        ]);
    }

    protected function embedExim(Request $request): bool
    {
        return $request->query('embed') === 'exim';
    }

    // data satu bulan, termasuk hari minggu sebelum/sesudahnya yang tampil di grid (Senin - Minggu)
    public function data(Request $request)
    {
        $month = $this->monthParam($request);
        $from = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $to = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $ready = $this->gateColumnsReady();
        $hasOut = in_array('gate_out_at', $this->contColumns(), true);

        $rows = $this->containerQuery($ready, false)
            ->addSelect('export.closetime', 'export.etdvess', 'export.OP', 'cont.vessname')
            ->where(function ($q) use ($from, $to, $hasOut) {
                $q->whereBetween('export.contdate', [$from->toDateString(), $to->toDateString()])
                    ->orWhereBetween('cont.actcontdate', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
                if ($hasOut) {
                    $q->orWhereBetween('cont.gate_out_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
                }
            })
            ->orderBy('export.contdate')
            ->orderBy('cont.contpk')
            ->get();

        $embed = $this->embedExim($request);
        $containers = $rows->map(function ($c) use ($ready, $embed) {
            // decorate tanpa foto/checklist (ringan); status "belum lengkap" dihitung ulang dari gate_draft_at
            $c = $this->decorate($c, false);
            if (in_array($c->status, ['late', 'today', 'upcoming'], true) && $ready && !empty($c->gate_draft_at)) {
                $c->status = 'incomplete';
            }

            $arriveDiff = $c->arrived_at && $c->eta
                ? $c->eta->diffInDays($c->arrived_at->copy()->startOfDay(), false)
                : null;

            return [
                'contpk'    => $c->contpk,
                'ident'     => $c->ident,
                'is_lcl'    => $c->is_lcl,
                'type'      => $c->type_label,
                'contno'    => $c->contno_fmt,
                'nopol'     => $c->nopol,
                'noseal'    => $c->noseal,
                'idship'    => $c->idship,
                'buyer'     => $c->buyer,
                'po'        => $c->POno,
                'op'        => $c->OP,
                'fwd'       => $c->fwdnm,
                'ekspedisi' => $c->ekspedisinm ?? null,
                'pic'       => $c->pic,
                'pic_email' => $c->pic_email,
                'vessel'    => $c->vessname,
                'eta'       => $c->eta ? $c->eta->toDateString() : null,
                'eta_days'  => $c->eta ? $c->eta_days : null,
                'arrived'   => $c->arrived_at ? $c->arrived_at->format('Y-m-d H:i') : null,
                'departed'  => $c->departed_at ? $c->departed_at->format('Y-m-d H:i') : null,
                'closing'   => !empty($c->closetime) ? Carbon::parse($c->closetime)->format('Y-m-d H:i') : null,
                'etd'       => !empty($c->etdvess) ? Carbon::parse($c->etdvess)->toDateString() : null,
                'diff'      => $arriveDiff,
                'status'    => $c->status,
                // dari EXIM: ke Detail Shipment; dari security: ke data gate container
                'url'       => $embed ? route('shipplan.detail_ship', $c->exportpk) : route('security.gate.index', ['cont' => $c->contpk]),
            ];
        })->values();

        return response()->json([
            'month'      => $month->format('Y-m'),
            'from'       => $from->toDateString(),
            'to'         => $to->toDateString(),
            'today'      => Carbon::today()->toDateString(),
            'updated'    => now()->format('H:i'),
            'containers' => $containers,
        ]);
    }

    protected function monthParam(Request $request): Carbon
    {
        $m = (string) $request->query('month', '');
        return preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m)
            ? Carbon::createFromFormat('Y-m-d', $m . '-01')->startOfDay()
            : Carbon::today()->startOfMonth();
    }

    protected function dayParam(Request $request): ?string
    {
        $d = (string) $request->query('day', '');
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) ? $d : null;
    }
}
