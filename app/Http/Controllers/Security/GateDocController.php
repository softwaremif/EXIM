<?php

namespace App\Http\Controllers\Security;

use Carbon\Carbon;

/**
 * Dokumen cetak dari data Security Gate: checklist pemeriksaan kontainer, berita acara stuffing
 * dan dokumen pengiriman barang. Dibuka dari EXIM (modal shipment plan, tab Container Leave)
 * maupun dari halaman security, jadi tidak dibatasi grup security seperti GateController.
 */
class GateDocController extends GateController
{
    const DOCS = [
        'checklist'  => 'Check List Pemeriksaan Kontainer',
        'stuffing'   => 'Berita Acara Stuffing',
        'pengiriman' => 'Dokumen Pengiriman Barang',
    ];

    // tanpa middleware khusus security (route tetap di grup login 'check')
    public function __construct()
    {
    }

    public function show($contpk, $doc)
    {
        abort_unless(isset(self::DOCS[$doc]), 404);
        Carbon::setLocale('id');

        $ready = $this->gateColumnsReady();
        $row = $this->containerQuery($ready, false)->where('cont.contpk', (int) $contpk)->first();
        abort_unless($row, 404, 'Container tidak ditemukan.');
        $c = $this->decorate($row, $ready);
        abort_unless($c->arrived_at, 404, 'Container belum tiba, dokumen belum tersedia.');
        abort_if($doc !== 'checklist' && $this->isRejected($c), 404, 'Container tidak lulus pemeriksaan, tidak ada pemuatan.');

        $c->is_rejected = $this->isRejected($c);
        $c->seal_people = $ready ? $this->sealPeople($c) : [];
        $c->in_guards = $this->inGuards($c);
        if ($doc !== 'checklist') {
            $this->stuffingInfo($c);
        }

        return view('menu.exim-export.security.docs.' . $doc, [
            's'         => $c,
            'title'     => self::DOCS[$doc],
            'checklist' => self::CHECKLIST,
            'sealSteps' => self::SEAL_STEPS,
            'sealRoles' => self::SEAL_PEOPLE,
            'signers'   => self::SIGNERS,
            'media'     => fn($f) => $f ? asset('public/upload/gate/' . (int) $c->contpk . '/' . $f) : null,
        ]);
    }
}
