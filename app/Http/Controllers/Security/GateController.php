<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Services\SimOcr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Security Gate: security mencatat kedatangan aktual (actcontdate) container
 * dari shipment plan EXIM, lengkap dengan data truk, driver, checklist pemeriksaan
 * kontainer/trailer/hama, foto & video; lalu mencatat pemasangan seal dan jam keluar.
 * Data disimpan di table cont (kolom gate_*), lihat GATE_COLUMNS.
 */
class GateController extends Controller
{
    const GUSER_SECURITY = 46;
    const CHUNK_SIZE = 1024 * 1024;          // 1 MB per potongan (upload_max_filesize server 2 MB)
    const MAX_FOTO_BYTES = 10 * 1024 * 1024;
    // video dikompres di perangkat; batas ini untuk file asli kalau perangkat tidak bisa mengompres
    const MAX_VIDEO_BYTES = 400 * 1024 * 1024;
    const MAX_VIDEO_SECONDS = 300;           // video kondisi container saat gate in, maks 5 menit (dicek di perangkat)
    const MAX_FOTO_SIDE = 1600;              // foto yang lolos tanpa kompres di perangkat dikecilkan di server
    const FOTO_QUALITY = 75;

    /**
     * Foto container kosong saat gate in: 1 foto per sisi, semua wajib, ambil ulang menimpa.
     * Kolom gate_foto_cont: {"depan":"f.jpg","kanan":...,"lama":[...]}.
     * Data sebelum ada 5 sisi berupa daftar ["f.jpg",...] dan dibaca sebagai "lama" (sah kalau min. 3 foto).
     */
    const CONT_PHOTO_SLOTS = [
        'depan' => ['Depan', 'Tampak depan, satu sisi penuh'],
        'kanan' => ['Samping kanan', 'Seluruh sisi kanan'],
        'kiri'  => ['Samping kiri', 'Seluruh sisi kiri'],
        'tutup' => ['Belakang pintu tutup', 'Nomor container terbaca'],
        'buka'  => ['Belakang pintu buka', 'Bagian dalam container terlihat'],
    ];
    const MIN_FOTO_LAMA = 3;
    const DEFAULT_UNIT = 'Finishing';

    const TYPE_LABELS = [
        '20ft' => "20' Dry", '40ft' => "40' Dry", '40hc' => "40' High Cube",
        '45ft' => "45' High Cube", 'lcl' => 'LCL',
    ];

    // semua kolom gate di table cont; form baru aktif kalau semuanya sudah dibuat
    const GATE_COLUMNS = [
        'gate_driver', 'gate_driver_hp', 'gate_foto_id', 'gate_foto_cont', 'gate_video', 'gate_userpk', 'gate_draft_at',
        'gate_transporter', 'gate_unit', 'gate_sim_no', 'gate_sim_exp',
        'gate_checklist', 'gate_result', 'gate_result_note',
        'gate_seal_check', 'gate_seal_receiver', 'gate_seal_installer', 'gate_seal_supervisor', 'gate_seal_inspector',
        'gate_seal_receiver_nrk', 'gate_seal_installer_nrk', 'gate_seal_supervisor_nrk', 'gate_seal_inspector_nrk',
        'gate_out_at', 'gate_out_userpk',
    ];

    /**
     * Petugas seal diisi lewat scan ID card karyawan (QR berisi karid / NRK).
     * Nama diambil dari HRD (mysql_hrd.kar) dan disimpan di kolom ini, NRK di kolom {kolom}_nrk.
     */
    const SEAL_PEOPLE = [
        'gate_seal_receiver'   => 'Penerima seal',
        'gate_seal_installer'  => 'Petugas pemasangan seal',
        'gate_seal_supervisor' => 'Petugas pengawas pemasangan seal',
        'gate_seal_inspector'  => 'Petugas pemeriksaan seal',
    ];

    // tanda tangan digital saat catat keluar: {"pemeriksa":"sign_pemeriksa_....png", ...}
    // kolom terpisah dari GATE_COLUMNS supaya gate in & edit tetap jalan walau kolom ini belum dibuat
    const SIGN_COLUMN = 'gate_signs';
    const SIGNERS = [
        'pemeriksa' => 'Petugas pemeriksa',
        'finishing' => 'Kabag Finishing',
        'fm'        => 'Manajer Produksi',
        'exim'      => 'Exim',
    ];
    const MAX_SIGN_BYTES = 300 * 1024;

    /**
     * Data muat (form kertas "Berita Acara Stuffing" & "Dokumen Pengiriman Barang").
     * gate_loaders / gate_guards: JSON [{"karid":"051798","nama":"..."}] dari scan ID card.
     * Terpisah dari GATE_COLUMNS supaya gate in & edit tetap jalan walau kolom ini belum dibuat.
     */
    const STUFF_COLUMNS = [
        'gate_lokasi', 'gate_country', 'gate_open_at', 'gate_load_at', 'gate_close_at', 'gate_port_at',
        'gate_stuffing_note', 'gate_loaders', 'gate_guards', 'gate_escort_nrk', 'gate_escort',
    ];
    /**
     * Sumber petugas muat / security (scan ID card atau cari nama / NRK). Nilai input gate_loaders[] / gate_guards[]:
     *   "046761"       karyawan HRD
     *   "hl:012345"    karyawan harian lepas (database hl), khusus petugas muat
     * Tersimpan: {"karid","nama","depnm","src":"hrd|hl"}; data lama tanpa src = HRD.
     */
    const PEOPLE_SOURCES = [
        'gate_loaders'   => ['hl' => true],
        'gate_guards'    => ['hl' => false],
        'gate_in_guards' => ['hl' => false],
    ];
    /**
     * Petugas security saat gate in (scan ID card / cari, HRD, maks MAX_GUARDS).
     * Kolom cont.gatein_guard (& cont_gate_history untuk arsip container yang diganti), varchar(100):
     * berisi daftar NRK "046761,004787"; nama & department dibaca dari HRD saat ditampilkan (inGuards).
     * Selama kolom belum ada, bagian petugas security di Gate In tidak ditampilkan.
     */
    const IN_GUARDS_COLUMN = 'gatein_guard';

    /**
     * Lokasi stuffing: container per factory & pintu (table cont_stuffing, satu baris per kunjungan ke pintu).
     * aktif = 1 selama container di pintu, NULL setelah pindah / keluar / batal; kunci unik (mif, pintu, aktif)
     * dan (contpk, aktif) menjaga satu pintu satu container & satu container satu pintu.
     * pintu 0 = di area factory tanpa pintu (mis. barang belum siap lalu langsung pindah factory).
     * Aksi dicatat security di pos masing-masing: GateDoorController.
     */
    const STUFFING_TABLE = 'cont_stuffing';
    const DOORS = [1 => 2, 2 => 3];                              // MIF => jumlah pintu stuffing
    const SECURITY_POS = ['security1' => 1, 'security2' => 2];   // akun login security => pos MIF
    const MAX_LOADERS = 20;          // petugas muat (scan ID card); dokumen pengiriman mencetak min. 10 baris
    const MAX_GUARDS = 4;

    /**
     * Dokumentasi muat & keluar (container lulus): foto selesai stuffing s/d seal terpasang + video seal (tab Muat & seal),
     * foto container keluar (tab Gate out).
     * Kolom gate_out_media: {"out_stuffing":["f.jpg"],"out_half":[...],"out_closed":[...],"out_seal":[...],"out_exit":[...],
     *   "out_video":"v.mp4","out_exit_at":"2026-11-12 14:23:05"}; out_exit_at = waktu foto container keluar terakhir, dipakai sebagai jam keluar.
     * Foto seal terpasang terakhir mengisi gate_close_at (jam tutup container).
     * Ditampilkan juga di EXIM (modal shipment plan, tab Container Leave).
     * kind => [label, petunjuk, wajib]
     */
    const OUT_MEDIA_COLUMN = 'gate_out_media';
    const OUT_PHOTO_KINDS = [
        'out_stuffing' => ['Selesai stuffing', 'Pintu dibuka, isi container terlihat', true],
        'out_half'     => ['Pintu ditutup separo', 'Satu daun pintu tertutup', false],
        'out_closed'   => ['Pintu ditutup full', 'Kedua daun pintu tertutup rapat', true],
        'out_seal'     => ['Seal terpasang', 'Nomor seal terbaca jelas', true],
        'out_exit'     => ['Container keluar', 'Truk & container di gerbang MIF', true],
    ];
    // foto yang tampil di tab Gate out; jenis lain di tab Muat & seal
    const OUT_EXIT_KINDS = ['out_exit'];
    const MAX_OUT_PHOTOS = 3;

    // jejak edit setelah data tersimpan (opsional: dipakai kalau kolomnya sudah dibuat)
    const EDIT_COLUMNS = ['gate_edit_at', 'gate_edit_userpk'];

    // riwayat container yang ditolak lalu diganti EXIM (ShipplanController::replaceContainer)
    const HISTORY_TABLE = 'cont_gate_history';

    // langkah pemasangan seal (VVTT)
    const SEAL_STEPS = [
        'view'   => ['View', 'Lihat seal & lubang pengunci'],
        'verify' => ['Verify', 'Cocokkan nomor seal dengan dokumen'],
        'tug'    => ['Tug', 'Tarik seal, pastikan terkunci'],
        'twist'  => ['Twist', 'Putar seal, pastikan tidak lepas'],
    ];

    /**
     * Checklist pemeriksaan sesuai form kertas "Check List Pemeriksaan Kontainer".
     * Tiap item: kode => [pertanyaan, jawaban aman]. 'Y' = Ya, 'T' = Tidak.
     * Jawaban yang tidak sama dengan jawaban aman = temuan, wajib diberi catatan.
     * Kode disimpan di gate_checklist, jangan diubah setelah dipakai.
     */
    const CHECKLIST = [
        'A' => ['title' => 'Bagian kontainer', 'groups' => [
            ['title' => 'Bawah luar kontainer', 'items' => [
                'a1_1' => ['Ada sesuatu yang dimasukkan / diselipkan di bawah container?', 'T'],
                'a1_2' => ['Pengunci container dengan trailer terpasang dengan baik?', 'Y'],
            ]],
            ['title' => 'Dalam dan luar pintu', 'items' => [
                'a2_1' => ['Pintu dan sistem pengunci pintu container baik?', 'Y'],
                'a2_2' => ['Celah antar pintu rapat?', 'Y'],
            ]],
            ['title' => 'Dinding sebelah kanan', 'items' => [
                'a3_1' => ['Dinding kanan rusak, penyok, atau hancur?', 'T'],
                'a3_2' => ['Ada ruang tambahan, cat baru, atau las baru di dalam container?', 'T'],
            ]],
            ['title' => 'Dinding sebelah kiri', 'items' => [
                'a4_1' => ['Dinding kiri rusak, penyok, atau hancur?', 'T'],
                'a4_2' => ['Ada ruang tambahan, cat baru, atau las baru di dalam container?', 'T'],
            ]],
            ['title' => 'Dinding depan', 'items' => [
                'a5_1' => ['Ada lubang, banyak karat, atau penyok?', 'T'],
                'a5_2' => ['Ada ruang tambahan, cat baru, atau las baru?', 'T'],
            ]],
            ['title' => 'Atap', 'items' => [
                'a6_1' => ['Atap berlubang?', 'T'],
                'a6_2' => ['Atap banyak karat atau penyok?', 'T'],
                'a6_3' => ['Ada ruang tambahan atau las baru di atap?', 'T'],
            ]],
            ['title' => 'Lantai dalam kontainer', 'items' => [
                'a7_1' => ['Lantai berubah bentuk, bergelombang, atau lainnya?', 'T'],
                'a7_2' => ['Lantai basah?', 'T'],
            ]],
        ]],
        'B' => ['title' => 'Bagian trailer', 'groups' => [
            ['title' => 'Bamper', 'items' => [
                'b1_1' => ['Bamper terpasang dengan baik?', 'Y'],
                'b1_2' => ['Ada benda asing menempel / sengaja disimpan di balik bamper?', 'T'],
            ]],
            ['title' => 'Mesin', 'items' => [
                'b2_1' => ['Ada barang berbahaya di tempat mesin?', 'T'],
            ]],
            ['title' => 'Roda', 'items' => [
                'b3_1' => ['Roda layak pakai dan aman?', 'Y'],
            ]],
            ['title' => 'Lantai', 'items' => [
                'b4_1' => ['Ada barang mencurigakan di lantai / bawah karpet?', 'T'],
            ]],
            ['title' => 'Tangki bahan bakar', 'items' => [
                'b5_1' => ['Ada barang mencurigakan di tangki? (ketuk palu & cek dalam)', 'T'],
            ]],
            ['title' => 'Kabin', 'items' => [
                'b6_1' => ['Ada barang berbahaya di tempat penyimpanan kabin?', 'T'],
            ]],
            ['title' => 'Tangki udara', 'items' => [
                'b7_1' => ['Ada barang di dalam tangki udara, atau las / cat baru? (pukul palu)', 'T'],
            ]],
            ['title' => 'Knalpot', 'items' => [
                'b9_1' => ['Ada barang mencurigakan di sekitar / dalam knalpot?', 'T'],
            ]],
            ['title' => 'Ban cadangan', 'items' => [
                'b10_1' => ['Dilengkapi ban cadangan?', 'Y'],
            ]],
            ['title' => 'Rangka', 'items' => [
                'b11_1' => ['Ada barang mencurigakan di rangka? (ketuk hollow)', 'T'],
            ]],
        ]],
        'C' => ['title' => 'Pemeriksaan hama dalam kontainer', 'groups' => [
            ['title' => null, 'items' => [
                'c1' => ['Bagian dalam container kotor?', 'T'],
                'c2' => ['Dalam container basah atau lembab?', 'T'],
                'c3' => ['Ditemukan sarang serangga di dalam container?', 'T'],
                'c4' => ['Ada serangga (rayap, teter, lebah kayu, dll) atau telur serangga di lantai kayu?', 'T'],
            ]],
        ]],
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless((int) Session::get('guserpk') === self::GUSER_SECURITY, 403, 'Menu ini khusus security.');
            return $next($request);
        });
    }

    // ====================================================================
    // Halaman
    // ====================================================================

    public function index(Request $request)
    {
        Carbon::setLocale('id');
        $ready = $this->gateColumnsReady();
        $filter = $request->query('filter', 'today');
        $q = trim((string) $request->query('q', ''));

        $all = $this->containerQuery($ready)->get()->map(fn($c) => $this->decorate($c, $ready));

        if ($q !== '') {
            $needle = Str::upper(preg_replace('/\s+/', '', $q));
            $all = $all->filter(function ($c) use ($needle) {
                $hay = Str::upper(preg_replace('/\s+/', '', implode('|', [
                    $c->contno, $c->nopol, $c->idship, $c->POno, $c->buyer, $c->noseal, $c->pic,
                ])));
                return Str::contains($hay, $needle);
            });
        }

        // container yang sedang di pintu stuffing: penanda "MIF 1 · Pintu 2" di card
        $doorsReady = $this->doorsReady();
        $activeVisits = $doorsReady ? DB::table(self::STUFFING_TABLE)->where('aktif', 1)->get()->keyBy('contpk') : collect();
        foreach ($all as $c) {
            $c->door = $activeVisits->get($c->contpk);
        }

        $counts = [];
        foreach (array_keys($this->filters()) as $key) {
            $counts[$key] = $all->filter(fn($c) => $this->matchesFilter($c, $key))->count();
        }

        $containers = $all->filter(fn($c) => $this->matchesFilter($c, $filter))
            ->sortBy(fn($c) => $c->sort_key)
            ->values();

        $selected = null;
        if ($request->filled('cont')) {
            $row = $this->containerQuery($ready, false)->where('cont.contpk', (int) $request->query('cont'))->first();
            abort_unless($row, 404, 'Container tidak ditemukan.');
            $selected = $this->decorate($row, $ready);
            // container pengganti: tampilkan container sebelumnya yang ditolak
            $selected->replaced = $ready && !$selected->arrived_at
                ? DB::table(self::HISTORY_TABLE)->where('contpk', $selected->contpk)->orderByDesc('histpk')->first()
                : null;
            $selected->seal_people = $ready ? $this->sealPeople($selected) : [];
            $selected->in_guards = $this->inGuards($selected);
            $selected->stuffing = $doorsReady && $selected->arrived_at ? $this->stuffingState($selected) : null;
            $this->stuffingInfo($selected);
            $selected->editor = !empty($selected->gate_edit_userpk)
                ? DB::connection('mysql_akses')->table('user')->where('userpk', $selected->gate_edit_userpk)->value('login')
                : null;
        }
        // mode edit: perbaiki data container yang sudah tercatat tiba / keluar
        $editing = $selected && $ready && $selected->arrived_at && $request->boolean('edit');

        return view('menu.exim-export.security.index', [
            'containers' => $containers,
            'counts'     => $counts,
            'filters'    => $this->filters(),
            'filter'     => $filter,
            'q'          => $q,
            'selected'   => $selected,
            'editing'    => $editing,
            'ready'      => $ready,
            'missing'    => $ready ? [] : array_values(array_diff(self::GATE_COLUMNS, $this->contColumns())),
            'login'      => Session::get('login'),
            'posMif'     => $this->posMif(),
            // ikon Pintu di header: pintu terisi / jumlah pintu (pos MIF security, akun lain semua MIF)
            'dockBadge'  => $doorsReady ? $this->dockBadge($activeVisits) : null,
            'contSlots'  => self::CONT_PHOTO_SLOTS,
            'maxVideoSec' => self::MAX_VIDEO_SECONDS,
            'chunkSize'  => self::CHUNK_SIZE,
            'checklist'  => self::CHECKLIST,
            'sealSteps'  => self::SEAL_STEPS,
            'sealRoles'  => self::SEAL_PEOPLE,
            'signers'    => self::SIGNERS,
            'signReady'  => $ready && $this->signReady(),
            'stuffReady' => $ready && $this->stuffReady(),
            'outKinds'   => self::OUT_PHOTO_KINDS,
            'outExitKinds' => self::OUT_EXIT_KINDS,
            'outReady'   => $ready && $this->outMediaReady(),
            'maxOutFoto' => self::MAX_OUT_PHOTOS,
            'maxLoaders' => self::MAX_LOADERS,
            'maxGuards'  => self::MAX_GUARDS,
            'inGuardsReady' => $this->inGuardsReady(),
            'suggest'    => $selected && $ready ? $this->suggestions() : [],
        ]);
    }

    // ====================================================================
    // Aksi (AJAX / form)
    // ====================================================================

    // simpan data truk, driver & checklist tanpa mencatat kedatangan
    public function draft(Request $request, $contpk)
    {
        $cont = $this->editableCont($contpk);
        $data = $this->validateTruck($request, false);

        DB::table('cont')->where('contpk', $cont->contpk)->update($data + [
            'gate_draft_at' => now(),
            'gate_userpk'   => Session::get('userpk'),
        ]);

        return response()->json(['success' => true, 'message' => 'Draft tersimpan.']);
    }

    // catat kedatangan: jam diambil dari server saat submit
    public function store(Request $request, $contpk)
    {
        $cont = $this->editableCont($contpk);
        $data = $this->validateTruck($request, true);

        if (empty($cont->contno) && empty($data['contno']) && !$this->isLcl($cont)) {
            return response()->json(['success' => false, 'message' => 'Data belum lengkap.', 'errors' => ['contno' => ['Isi nomor container sesuai pintu container.']]], 422);
        }

        $errors = $this->completenessErrors($cont, $data);
        if ($errors) {
            // data truk & checklist tetap disimpan sebagai draft supaya tidak hilang
            DB::table('cont')->where('contpk', $cont->contpk)->update($data + ['gate_draft_at' => now()]);
            return response()->json(['success' => false, 'message' => 'Data belum lengkap.', 'errors' => ['media' => $errors]], 422);
        }

        DB::table('cont')->where('contpk', $cont->contpk)->update($data + [
            'actcontdate'   => now(),
            'gate_userpk'   => Session::get('userpk'),
            'gate_draft_at' => null,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Kedatangan tersimpan.',
            'redirect' => route('security.gate.index', ['cont' => $cont->contpk, 'filter' => 'arrived', 'saved' => 1]),
        ]);
    }

    // catat keluar MIF; container yang lulus wajib sudah dipasang seal (VVTT)
    public function checkout(Request $request, $contpk)
    {
        abort_unless($this->gateColumnsReady(), 503, 'Kolom gate pada table cont belum lengkap. Hubungi IT.');

        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat.');
        abort_if(!empty($cont->gate_out_at), 422, 'Container ini sudah tercatat keluar.');
        abort_unless($this->signReady(), 503, 'Kolom tanda tangan (gate_signs) belum ada. Hubungi IT.');

        $data = ['gate_out_at' => now(), 'gate_out_userpk' => Session::get('userpk')];

        if (!$this->isRejected($cont)) {
            abort_unless($this->stuffReady(), 503, 'Kolom data muat belum ada. Hubungi IT.');
            abort_unless($this->outMediaReady(), 503, 'Kolom dokumentasi keluar (gate_out_media) belum ada. Hubungi IT.');
            $data += $this->validateSeal($request, $cont);
            $data += $this->validateStuffing($request, true, $cont);
            // jam mulai muat dari finishing (action muat) wajib ada sebelum truk keluar
            if (empty($cont->gate_load_at)) {
                throw ValidationException::withMessages(['gate_load_at' => ['Jam mulai muat belum terisi. Tunggu finishing melakukan action muat, lalu muat ulang halaman.']]);
            }
            $this->requireOutMedia($cont);
            $data['gate_out_at'] = $this->exitTime($cont);
        }
        // semua data dicek dulu, baru file tanda tangan disimpan (yang sudah tersimpan lewat Simpan seal tetap dipakai)
        $signs = $this->validateSigns($request, $cont, true);
        $data[self::SIGN_COLUMN] = json_encode($this->saveSigns($cont, $signs));

        DB::transaction(function () use ($cont, $data) {
            DB::table('cont')->where('contpk', $cont->contpk)->update($data);
            // container keluar pabrik: pintu stuffing yang masih dipakai otomatis kosong
            $this->closeVisit($cont->contpk, 'keluar', Carbon::parse($data['gate_out_at']));
        });

        return response()->json([
            'success'  => true,
            'message'  => 'Keluar tersimpan.',
            'redirect' => route('security.gate.index', ['cont' => $cont->contpk, 'filter' => 'departed', 'out' => 1]),
        ]);
    }

    // simpan data muat bertahap (jam buka, petugas muat, dst.) sebelum catat keluar
    public function stuffing(Request $request, $contpk)
    {
        abort_unless($this->gateColumnsReady() && $this->stuffReady(), 503, 'Kolom data muat belum ada. Hubungi IT.');
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat.');
        abort_if(!empty($cont->gate_out_at), 422, 'Container ini sudah tercatat keluar. Ubah lewat menu Edit.');
        abort_if($this->isRejected($cont), 422, 'Container tidak lulus pemeriksaan, tidak ada pemuatan.');

        DB::table('cont')->where('contpk', $cont->contpk)->update($this->validateStuffing($request, false, $cont));

        return response()->json(['success' => true, 'message' => 'Data muat tersimpan.']);
    }

    // simpan data seal bertahap (langkah VVTT, petugas seal, tanda tangan) sebelum catat keluar;
    // yang belum diisi boleh kosong, kelengkapan dicek saat catat keluar
    public function seal(Request $request, $contpk)
    {
        abort_unless($this->gateColumnsReady() && $this->signReady(), 503, 'Kolom data seal / tanda tangan belum ada. Hubungi IT.');
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat.');
        abort_if(!empty($cont->gate_out_at), 422, 'Container ini sudah tercatat keluar. Ubah lewat menu Edit.');
        abort_if($this->isRejected($cont), 422, 'Container tidak lulus pemeriksaan, tidak ada pemasangan seal.');

        $data = $this->validateSeal($request, $cont, false);
        $signs = $this->saveSigns($cont, $this->validateSigns($request, $cont, false));
        $data[self::SIGN_COLUMN] = $signs ? json_encode($signs) : null;

        DB::table('cont')->where('contpk', $cont->contpk)->update($data);

        return response()->json(['success' => true, 'message' => 'Data seal tersimpan.']);
    }

    // ====================================================================
    // Ceklis carton oleh security (data di sistem Packing List, lewat API)
    //   Tim stuffing scan/proses carton (cekdate1) -> security ceklis di sini (cekdate2)
    //   -> carton baru dianggap masuk container (status 6). Lihat Packing List
    //   Api\SecurityCheckController.
    // ====================================================================

    // GET security/gate/{contpk}/cartons : daftar carton container + status ceklis.
    public function cartons($contpk)
    {
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        try {
            $res = \Illuminate\Support\Facades\Http::packing()->timeout(20)
                ->get(rtrim(config('services.packing.url'), '/') . '/api/packing/security/container-cartons', ['contpk' => $cont->contpk]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Sistem Packing List tidak bisa dihubungi.'], 502);
        }
        if (!$res->successful()) {
            return response()->json(['message' => $res->json('message') ?: 'Data carton tidak bisa dimuat dari sistem Packing List.'], 502);
        }
        // Sudah keluar MIF -> ceklis dikunci (hanya lihat).
        return response()->json($res->json() + ['locked' => !empty($cont->gate_out_at)]);
    }

    // GET security/gate/{contpk}/cartons/detail?key= : isi 1 carton (PO, warna, qty per size).
    public function cartonDetail(Request $request, $contpk)
    {
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        try {
            $res = \Illuminate\Support\Facades\Http::packing()->timeout(20)
                ->get(rtrim(config('services.packing.url'), '/') . '/api/packing/security/carton-detail', [
                    'contpk' => $cont->contpk, 'key' => (string) $request->query('key', ''),
                ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Sistem Packing List tidak bisa dihubungi.'], 502);
        }
        return response()->json($res->json() ?? ['message' => 'Detail carton tidak bisa dimuat.'], $res->successful() ? 200 : 422);
    }

    // POST security/gate/{contpk}/cartons : ceklis / batal ceklis {keys[] | all=1, checked=1|0}.
    public function checkCartons(Request $request, $contpk)
    {
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat.');
        abort_if(!empty($cont->gate_out_at), 422, 'Container ini sudah tercatat keluar, ceklis carton tidak bisa diubah.');
        $v = $request->validate([
            'keys'    => 'nullable|array',
            'keys.*'  => 'string|max:2000',
            'all'     => 'nullable|boolean',
            'checked' => 'required|boolean',
        ]);
        try {
            $res = \Illuminate\Support\Facades\Http::packing()->timeout(30)->acceptJson()
                ->post(rtrim(config('services.packing.url'), '/') . '/api/packing/security/check', [
                    'contpk'  => (int) $cont->contpk,
                    'keys'    => $v['keys'] ?? [],
                    'all'     => (bool) ($v['all'] ?? false),
                    'checked' => (bool) $v['checked'],
                    'user'    => (string) Session::get('login'), // dicatat di log aktivitas Packing List
                ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Sistem Packing List tidak bisa dihubungi. Ceklis belum tersimpan.'], 502);
        }
        $json = $res->json() ?? [];
        if (!$res->successful()) {
            $msg = $json['message'] ?? 'Ceklis gagal disimpan.';
            if (!empty($json['skipped'])) {
                $msg .= ' ' . implode('; ', array_slice($json['skipped'], 0, 5));
            }
            return response()->json(['message' => $msg], 422);
        }
        return response()->json($json);
    }

    /**
     * Perbaiki data container yang sudah tercatat tiba / keluar (salah input).
     * Jam kedatangan & jam keluar tidak berubah; foto/video langsung tersimpan lewat upload().
     * Setelah truk keluar, hasil pemeriksaan dikunci dan data seal ikut bisa diperbaiki.
     */
    public function update(Request $request, $contpk)
    {
        $cont = $this->editableCont($contpk, true);
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat.');

        $data = $this->validateTruck($request, true, false);
        $departed = !empty($cont->gate_out_at);

        if ($departed && $cont->gate_result !== null && (int) $data['gate_result'] !== (int) $cont->gate_result) {
            return response()->json(['success' => false, 'message' => 'Data belum lengkap.', 'errors' => ['gate_result' => ['Hasil pemeriksaan tidak bisa diubah karena truk sudah keluar.']]], 422);
        }
        if ($departed && !$this->isRejected($cont)) {
            $data = $this->validateSeal($request, $cont) + $data;
            if ($this->stuffReady()) {
                $data += $this->validateStuffing($request, true, $cont);
            }
        }

        $errors = $this->completenessErrors($cont, $data);
        if ($errors) {
            return response()->json(['success' => false, 'message' => 'Data belum lengkap.', 'errors' => ['media' => $errors]], 422);
        }

        if ($this->editColumnsReady()) {
            $data += ['gate_edit_at' => now(), 'gate_edit_userpk' => Session::get('userpk')];
        }
        DB::table('cont')->where('contpk', $cont->contpk)->update($data);

        return response()->json([
            'success'  => true,
            'message'  => 'Perubahan tersimpan.',
            'redirect' => route('security.gate.index', ['cont' => $cont->contpk, 'filter' => $departed ? 'departed' : 'arrived', 'edited' => 1]),
        ]);
    }

    // data karyawan dari hasil scan ID card (QR berisi karid / NRK)
    // scan ID card / ketik NRK: HRD dulu, lalu harian lepas (hl=1, khusus petugas muat)
    /**
     * Baca SIM (OCR Tesseract, App\Services\SimOcr) untuk dialog Foto SIM driver: dari foto yang baru diambil (foto,
     * belum disimpan) atau foto identitas yang sudah tersimpan. Hasil hanya usulan; security memeriksa sebelum menyimpan.
     */
    public function ocrSim(Request $request, $contpk)
    {
        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first(['contpk', 'gate_foto_id']);
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        if (!SimOcr::available()) {
            return response()->json(['success' => false, 'message' => 'Pembaca SIM (OCR) belum tersedia di server.'], 503);
        }
        if ($request->hasFile('foto')) {
            $request->validate(['foto' => 'file|image|max:15360'], ['foto.image' => 'File bukan foto.', 'foto.max' => 'Foto terlalu besar (maks 15 MB).']);
            $path = $request->file('foto')->getRealPath();
        } else {
            $file = (string) $cont->gate_foto_id;
            $path = $this->mediaFolder($cont->contpk) . '/' . $file;
            if ($file === '' || basename($file) !== $file || !is_file($path)) {
                return response()->json(['success' => false, 'message' => 'Foto identitas driver belum ada.'], 422);
            }
        }
        try {
            $r = SimOcr::read($path);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Foto SIM tidak bisa dibaca. Isi data driver manual.'], 422);
        }

        return response()->json([
            'success' => true,
            'nama'    => $r['nama'],
            'sim_no'  => $r['sim_no'],
            'sim_exp' => $r['sim_exp'],
            'found'   => count(array_filter([$r['nama'], $r['sim_no'], $r['sim_exp']])),
        ]);
    }

    public function employee(Request $request)
    {
        $v = $request->validate(['karid' => 'required|string|max:100'], ['karid.required' => 'NRK kosong. Scan ulang ID card.']);
        $karid = $this->normalizeKarid($v['karid']);
        $withHl = $request->boolean('hl');

        try {
            $emp = $this->employees([$karid])[$karid] ?? null;
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Database HRD tidak bisa diakses. Coba lagi atau hubungi IT.'], 503);
        }
        if (!$emp && $withHl) {
            try {
                $emp = $this->employeesHl([$karid])[$karid] ?? null;
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => "NRK {$karid} tidak ada di HRD, dan database harian lepas tidak bisa diakses. Hubungi IT."], 503);
            }
        }
        if (!$emp) {
            return response()->json(['success' => false, 'message' => "NRK {$karid} tidak terdaftar di HRD" . ($withHl ? ' maupun harian lepas.' : '.')], 404);
        }
        if (!$emp->active) {
            return response()->json(['success' => false, 'message' => "{$emp->nama} (NRK {$karid}) sudah tidak aktif."], 422);
        }

        return response()->json(['success' => true, 'employee' => $this->employeePayload($emp)]);
    }

    /**
     * Cari karyawan aktif per nama / NRK untuk petugas tanpa ID card (min. 2 huruf, maks. 15 per sumber).
     * hl=1: ikut cari karyawan harian lepas. Database hl mati: hasil HRD tetap dikirim + peringatan.
     */
    public function employeeSearch(Request $request)
    {
        $q = trim(preg_replace('/\s+/', ' ', (string) $request->query('q', '')));
        if (mb_strlen($q) < 2) {
            return response()->json(['success' => true, 'results' => [], 'warning' => null]);
        }

        $results = [];
        $warning = null;
        try {
            $results = $this->searchEmployees('mysql_hrd', $q, 'hrd');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Database HRD tidak bisa diakses. Coba lagi atau hubungi IT.'], 503);
        }
        if ($request->boolean('hl')) {
            try {
                $results = array_merge($results, $this->searchEmployees('mysql_hl', $q, 'hl'));
            } catch (\Throwable $e) {
                $warning = 'Data karyawan harian lepas tidak bisa diakses, hasil hanya dari HRD.';
            }
        }

        return response()->json([
            'success' => true,
            'results' => array_map(fn($e) => $this->employeePayload($e), $results),
            'warning' => $warning,
        ]);
    }

    protected function searchEmployees(string $conn, string $q, string $src): array
    {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
        return $this->karQuery($conn)
            ->where('kar.aktif', 0)
            ->where(fn($w) => $w->where('kar.nama', 'like', $like)->orWhere('kar.karid', 'like', $like))
            ->orderBy('kar.nama')
            ->limit(15)
            ->get()
            ->map(fn($e) => $this->employeeRow($e, $src))
            ->all();
    }

    protected function employeePayload($emp): array
    {
        return [
            'karid' => $emp->karid,
            'nama'  => $emp->nama,
            'depnm' => $emp->depnm,
            'src'   => $emp->src,
            'token' => ($emp->src === 'hl' ? 'hl:' : '') . $emp->karid,
        ];
    }

    // nilai input daftar petugas (lihat PEOPLE_SOURCES)
    public static function personToken(array $p): string
    {
        return (($p['src'] ?? 'hrd') === 'hl' ? 'hl:' : '') . $p['karid'];
    }

    // keterangan di bawah nama petugas: NRK · department (harian lepas ditandai)
    public static function personMeta(array $p): string
    {
        return 'NRK ' . $p['karid'] . (($p['src'] ?? 'hrd') === 'hl' ? ' · Harian lepas' : '') . (!empty($p['depnm']) ? ' · ' . $p['depnm'] : '');
    }

    /**
     * Upload bertahap (chunk 1 MB) untuk foto identitas, foto container, dan video.
     * Client mengirim potongan berurutan dengan upload_id yang sama; potongan
     * terakhir memindahkan file ke public/upload/gate/{contpk} dan mengisi kolom cont.
     */
    public function upload(Request $request, $contpk)
    {
        $cont = $this->editableCont($contpk, true);

        $v = $request->validate([
            'kind'      => 'required|in:id,cont,video,' . implode(',', array_keys(self::OUT_PHOTO_KINDS)) . ',out_video',
            'slot'      => 'required_if:kind,cont|nullable|in:' . implode(',', array_keys(self::CONT_PHOTO_SLOTS)),
            'upload_id' => 'required|regex:/^[a-z0-9]{12,40}$/',
            'index'     => 'required|integer|min:0',
            'total'     => 'required|integer|min:1|max:' . (int) ceil(self::MAX_VIDEO_BYTES / self::CHUNK_SIZE),
            'chunk'     => 'required|file|max:' . (int) (self::CHUNK_SIZE / 1024 + 64),
        ]);

        $kind = $v['kind'];
        $isOut = Str::startsWith($kind, 'out_');
        $isVideo = in_array($kind, ['video', 'out_video'], true);
        if ($isOut) {
            $this->checkOutMediaAllowed($cont);
            if (!$isVideo && (int) $v['index'] === 0 && count($this->outMedia($cont)[$kind]) >= self::MAX_OUT_PHOTOS) {
                return response()->json(['success' => false, 'message' => 'Maksimal ' . self::MAX_OUT_PHOTOS . ' foto ' . Str::lower(self::OUT_PHOTO_KINDS[$kind][0]) . '. Hapus salah satu dulu.'], 422);
            }
        }

        $dir = storage_path('app/gate-chunks');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $part = "{$dir}/{$cont->contpk}_{$v['upload_id']}.part";

        // potongan pertama memulai file baru; berikutnya harus urut
        if ((int) $v['index'] === 0) {
            @unlink($part);
        } elseif (!file_exists($part)) {
            return response()->json(['success' => false, 'message' => 'Upload terputus. Ulangi pengambilan.'], 422);
        }
        file_put_contents($part, file_get_contents($request->file('chunk')->getRealPath()), FILE_APPEND);

        $limit = $isVideo ? self::MAX_VIDEO_BYTES : self::MAX_FOTO_BYTES;
        if (filesize($part) > $limit) {
            @unlink($part);
            return response()->json(['success' => false, 'message' => 'Ukuran file melebihi ' . round($limit / 1048576) . ' MB.'], 422);
        }

        if ((int) $v['index'] + 1 < (int) $v['total']) {
            return response()->json(['success' => true, 'done' => false]);
        }

        // potongan terakhir: cek jenis file dari isinya, bukan dari nama
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($part) ?: '';
        $allowed = $isVideo
            ? ['video/mp4' => 'mp4', 'video/quicktime' => 'mov', 'video/webm' => 'webm', 'video/3gpp' => '3gp', 'video/x-matroska' => 'mkv']
            : ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            @unlink($part);
            return response()->json(['success' => false, 'message' => $isVideo ? 'File harus berupa video.' : 'File harus berupa foto (JPG/PNG).'], 422);
        }

        // foto yang tidak terkompres di perangkat (terlalu besar) dikecilkan di sini
        if (!$isVideo) {
            $ext = $this->compactImage($part, $mime) ?: $allowed[$mime];
        } else {
            $ext = $allowed[$mime];
        }

        $folder = $this->mediaFolder($cont->contpk);
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }
        $prefix = $kind === 'cont' ? 'cont_' . $v['slot'] : $kind;
        $filename = $prefix . '_' . date('YmdHis') . '_' . Str::lower(Str::random(6)) . '.' . $ext;
        rename($part, "{$folder}/{$filename}");

        // dibaca ulang dengan kunci: beberapa foto bisa selesai diunggah bersamaan
        DB::transaction(function () use ($cont, $kind, $isOut, $isVideo, $filename, $v) {
            $cont = DB::table('cont')->where('contpk', $cont->contpk)->lockForUpdate()->first();

            // container yang sudah tiba (mode edit) bukan draft lagi; dokumentasi keluar baru dihitung
            // sebagai edit kalau truk sudah keluar
            $edited = $this->editColumnsReady() && (!$isOut || !empty($cont->gate_out_at))
                ? ['gate_edit_at' => now(), 'gate_edit_userpk' => Session::get('userpk')] : [];
            $update = empty($cont->actcontdate) ? ['gate_draft_at' => now(), 'gate_userpk' => Session::get('userpk')] : $edited;
            if ($isOut) {
                $media = $this->outMedia($cont);
                if ($isVideo) {
                    $this->deleteMedia($cont->contpk, $media['out_video']);
                    $media['out_video'] = $filename;
                } else {
                    $media[$kind][] = $filename;
                }
                // foto container keluar = jam keluar, foto seal terpasang = jam tutup container;
                // setelah truk keluar jam tidak berubah lagi
                if ($kind === 'out_exit' && empty($cont->gate_out_at)) {
                    $media['out_exit_at'] = now()->toDateTimeString();
                }
                if ($kind === 'out_seal' && empty($cont->gate_out_at) && $this->stuffReady()) {
                    $update['gate_close_at'] = now()->format('Y-m-d H:i:00');
                }
                $update[self::OUT_MEDIA_COLUMN] = json_encode($media);
            } elseif ($kind === 'id') {
                $this->deleteMedia($cont->contpk, $cont->gate_foto_id);
                $update['gate_foto_id'] = $filename;
            } elseif ($kind === 'video') {
                $this->deleteMedia($cont->contpk, $cont->gate_video);
                $update['gate_video'] = $filename;
            } else {
                $photos = $this->contPhotos($cont);
                $this->deleteMedia($cont->contpk, $photos[$v['slot']]);
                $photos[$v['slot']] = $filename;
                $update['gate_foto_cont'] = $this->encodeContPhotos($photos);
            }
            DB::table('cont')->where('contpk', $cont->contpk)->update($update);
        });

        return response()->json(['success' => true, 'done' => true] + $this->mediaPayload($cont->contpk));
    }

    public function deleteFoto(Request $request, $contpk)
    {
        $cont = $this->editableCont($contpk, true);
        $v = $request->validate(['kind' => 'required|in:id,cont,video,' . implode(',', array_keys(self::OUT_PHOTO_KINDS)) . ',out_video', 'file' => 'nullable|string']);

        if (Str::startsWith($v['kind'], 'out_')) {
            return $this->deleteOutMedia($cont, $v['kind'], $v['file'] ?? null);
        }

        // data yang sudah tersimpan tidak boleh kurang dari syarat minimal;
        // foto 5 sisi container hanya diganti (ambil ulang), yang bisa dihapus hanya foto lama
        if (!empty($cont->actcontdate)) {
            if ($v['kind'] !== 'cont') {
                return response()->json(['success' => false, 'message' => 'Foto identitas dan video hanya bisa diganti, tidak bisa dihapus.'], 422);
            }
            if ($this->contPhotosMissing($cont) && count($this->contPhotos($cont)['lama']) <= self::MIN_FOTO_LAMA) {
                return response()->json(['success' => false, 'message' => 'Foto lama masih dipakai sebagai bukti. Lengkapi foto 5 sisi container dulu, baru hapus.'], 422);
            }
        }

        $update = [];
        if ($v['kind'] === 'id') {
            $this->deleteMedia($cont->contpk, $cont->gate_foto_id);
            $update['gate_foto_id'] = null;
        } elseif ($v['kind'] === 'video') {
            $this->deleteMedia($cont->contpk, $cont->gate_video);
            $update['gate_video'] = null;
        } else {
            $photos = $this->contPhotos($cont);
            if (in_array($v['file'], $photos['lama'], true)) {
                $this->deleteMedia($cont->contpk, $v['file']);
                $photos['lama'] = array_values(array_diff($photos['lama'], [$v['file']]));
            }
            $update['gate_foto_cont'] = $this->encodeContPhotos($photos);
        }
        DB::table('cont')->where('contpk', $cont->contpk)->update($update);

        return response()->json(['success' => true] + $this->mediaPayload($cont->contpk));
    }

    // ====================================================================
    // Helper
    // ====================================================================

    // kolom gate_* dibuat manual di database; selama belum lengkap, form tidak bisa dipakai
    protected function contColumns(): array
    {
        static $cols = null;
        return $cols ?? ($cols = Schema::getColumnListing('cont'));
    }

    protected function gateColumnsReady(): bool
    {
        static $ready = null;
        return $ready ?? ($ready = !array_diff(self::GATE_COLUMNS, $this->contColumns())
            && Schema::hasTable(self::HISTORY_TABLE));
    }

    // petugas security saat gate in (kolom gatein_guard, lihat IN_GUARDS_COLUMN)
    protected function inGuardsReady(): bool
    {
        return in_array(self::IN_GUARDS_COLUMN, $this->contColumns(), true);
    }

    protected function signReady(): bool
    {
        static $ready = null;
        return $ready ?? ($ready = in_array(self::SIGN_COLUMN, $this->contColumns(), true)
            && Schema::hasColumn(self::HISTORY_TABLE, self::SIGN_COLUMN));
    }

    // cukup di table cont: data muat hanya ada di container lulus, sedangkan histori hanya untuk
    // container ditolak yang diganti (ShipplanController::replaceContainer mengarsipkan kalau kolomnya ada)
    protected function stuffReady(): bool
    {
        static $ready = null;
        return $ready ?? ($ready = !array_diff(self::STUFF_COLUMNS, $this->contColumns()));
    }

    protected function outMediaReady(): bool
    {
        return in_array(self::OUT_MEDIA_COLUMN, $this->contColumns(), true);
    }

    // dokumentasi keluar tersimpan, lengkap dengan semua jenis (kosong kalau belum ada)
    protected function outMedia($c): array
    {
        $raw = json_decode($c->{self::OUT_MEDIA_COLUMN} ?? '[]', true) ?: [];
        $media = ['out_video' => is_string($raw['out_video'] ?? null) ? $raw['out_video'] : null];
        foreach (array_keys(self::OUT_PHOTO_KINDS) as $kind) {
            $media[$kind] = array_values(array_filter((array) ($raw[$kind] ?? []), 'is_string'));
        }
        $at = $raw['out_exit_at'] ?? null;
        $media['out_exit_at'] = is_string($at) && strtotime($at) ? $at : null;
        return $media;
    }

    // dokumentasi keluar hanya untuk container lulus yang sudah tiba
    protected function checkOutMediaAllowed($cont): void
    {
        abort_unless($this->outMediaReady(), 503, 'Kolom dokumentasi keluar (gate_out_media) belum ada. Hubungi IT.');
        abort_if(empty($cont->actcontdate), 422, 'Kedatangan container ini belum tercatat.');
        abort_if($this->isRejected($cont), 422, 'Container tidak lulus pemeriksaan, tidak ada dokumentasi muat.');
    }

    // syarat catat keluar: tiap jenis foto wajib minimal 1 dan ada video seal.
    // error dipisah per tab: out_media (Muat & seal) dan out_exit_media (Gate out)
    protected function requireOutMedia($cont): void
    {
        $media = $this->outMedia($cont);
        $missing = [];
        foreach (self::OUT_PHOTO_KINDS as $kind => [$label, , $required]) {
            if ($required && !$media[$kind]) {
                $missing[in_array($kind, self::OUT_EXIT_KINDS, true) ? 'out_exit_media' : 'out_media'][] = "Ambil foto {$label}.";
            }
        }
        if (!$media['out_video']) {
            $missing['out_media'][] = 'Rekam video seal sudah terpasang dengan baik.';
        }
        if ($missing) {
            throw ValidationException::withMessages($missing);
        }
    }

    // jam keluar container lulus = waktu foto container keluar (wajar: setelah tiba, tidak di masa depan)
    protected function exitTime($cont): Carbon
    {
        $at = $this->outMedia($cont)['out_exit_at'];
        $at = $at ? Carbon::parse($at) : null;
        return $at && $at->lte(now()) && $at->gte(Carbon::parse($cont->actcontdate)) ? $at : now();
    }

    protected function deleteOutMedia($cont, string $kind, ?string $file)
    {
        $this->checkOutMediaAllowed($cont);
        $media = $this->outMedia($cont);
        $departed = !empty($cont->gate_out_at);

        if ($kind === 'out_video') {
            if ($departed) {
                return response()->json(['success' => false, 'message' => 'Video dokumentasi hanya bisa diganti setelah truk keluar, tidak bisa dihapus.'], 422);
            }
            $this->deleteMedia($cont->contpk, $media['out_video']);
            $media['out_video'] = null;
        } elseif (in_array($file, $media[$kind], true)) {
            if ($departed && count($media[$kind]) <= 1) {
                return response()->json(['success' => false, 'message' => 'Minimal 1 foto ' . Str::lower(self::OUT_PHOTO_KINDS[$kind][0]) . '. Tambah foto pengganti dulu, baru hapus yang salah.'], 422);
            }
            $this->deleteMedia($cont->contpk, $file);
            $media[$kind] = array_values(array_diff($media[$kind], [$file]));
            if ($kind === 'out_exit' && !$media['out_exit'] && !$departed) {
                $media['out_exit_at'] = null;
            }
        }
        $update = [self::OUT_MEDIA_COLUMN => json_encode($media)];
        // semua foto seal terpasang dihapus sebelum keluar: jam tutup container ikut kosong
        if ($kind === 'out_seal' && !$media['out_seal'] && !$departed && $this->stuffReady()) {
            $update['gate_close_at'] = null;
        }
        DB::table('cont')->where('contpk', $cont->contpk)->update($update);

        return response()->json(['success' => true] + $this->mediaPayload($cont->contpk));
    }

    protected function editColumnsReady(): bool
    {
        return !array_diff(self::EDIT_COLUMNS, $this->contColumns());
    }

    protected function filters(): array
    {
        return [
            'today'      => 'Hari ini',
            'late'       => 'Terlambat',
            'upcoming'   => 'Akan datang',
            'incomplete' => 'Belum lengkap',
            'arrived'    => 'Di area MIF',
            'departed'   => 'Sudah keluar',
        ];
    }

    protected function matchesFilter($c, string $filter): bool
    {
        switch ($filter) {
            case 'today':      return $c->status === 'today' || ($c->status === 'incomplete' && $c->eta_days === 0);
            case 'late':       return $c->status === 'late' || ($c->status === 'incomplete' && $c->eta_days < 0);
            case 'upcoming':   return $c->status === 'upcoming';
            case 'incomplete': return $c->status === 'incomplete';
            case 'arrived':    return in_array($c->status, ['arrived', 'rejected'], true);
            case 'departed':   return $c->status === 'departed';
        }
        return false;
    }

    // container dari shipment plan yang belum dibatalkan; untuk daftar ($window) dibatasi
    // yang belum tiba (ETA 30 hari ke belakang s/d 7 hari ke depan) atau tiba 7 hari terakhir
    protected function containerQuery(bool $ready, bool $window = true)
    {
        $today = Carbon::today();
        $cols = [
            'cont.contpk', 'cont.exportpk', 'cont.contno', 'cont.type', 'cont.nopol', 'cont.noseal',
            'cont.actcontdate', 'cont.fwdpk', 'cont.start_ship', 'export.contdate', 'export.buyer', 'export.POno', 'export.userpk',
            'fwd.fwdnm',
        ];
        // kolom gate yang sudah ada tetap diambil walau belum lengkap ($ready false),
        // supaya status keluar / ditolak di daftar tetap benar
        foreach (array_intersect(array_merge(self::GATE_COLUMNS, self::EDIT_COLUMNS, [self::SIGN_COLUMN, self::OUT_MEDIA_COLUMN, self::IN_GUARDS_COLUMN], self::STUFF_COLUMNS), $this->contColumns()) as $col) {
            $cols[] = 'cont.' . $col;
        }

        // ekspedisi dipilih EXIM di modal edit shipment plan (cont.angpk -> ang, perusahaan angkutan), kalau kolomnya sudah ada
        $withEkspedisi = in_array('angpk', $this->contColumns(), true);
        if ($withEkspedisi) {
            $cols[] = DB::raw('TRIM(ang.angnm) AS ekspedisinm');
        }

        return DB::table('cont')
            ->join('export', 'export.exportpk', '=', 'cont.exportpk')
            ->leftJoin('fwd', 'fwd.fwdpk', '=', 'cont.fwdpk')
            ->when($withEkspedisi, fn($q) => $q->leftJoin('ang', 'ang.angpk', '=', 'cont.angpk'))
            ->where(fn($q) => $q->whereNull('export.stscancel')->orWhere('export.stscancel', '<>', 1))
            ->when($window, fn($query) => $query->where(function ($q) use ($today) {
                $q->where(fn($w) => $w->whereNull('cont.actcontdate')
                    ->whereBetween('export.contdate', [$today->copy()->subDays(30)->toDateString(), $today->copy()->addDays(7)->toDateString()]))
                    ->orWhere('cont.actcontdate', '>=', $today->copy()->subDays(7));
            }))
            ->select($cols);
    }

    protected function decorate($c, bool $ready)
    {
        $today = Carbon::today();
        $eta = $c->contdate ? Carbon::parse($c->contdate)->startOfDay() : null;

        $c->idship = 'SP' . str_pad($c->exportpk, 4, '0', STR_PAD_LEFT);
        $c->type_label = self::TYPE_LABELS[strtolower((string) $c->type)] ?? ($c->type ?: 'Tipe belum diisi');
        $c->contno_fmt = $this->formatContNo($c->contno);
        // LCL tidak punya no. container: acuannya plat nomor truk
        $c->is_lcl = $this->isLcl($c);
        $c->ident = $c->is_lcl ? $c->nopol : $c->contno_fmt;
        $c->eta = $eta;
        $c->eta_days = $eta ? $today->diffInDays($eta, false) : 0;
        $c->arrived_at = $c->actcontdate ? Carbon::parse($c->actcontdate) : null;
        $c->departed_at = !empty($c->gate_out_at) ? Carbon::parse($c->gate_out_at) : null;
        $c->fotos = $ready ? $this->fotoList($c) : [];
        $c->cont_photos = $ready ? $this->contPhotos($c) : $this->contPhotos((object) []);
        $c->is_draft = $ready && !empty($c->gate_draft_at);
        $c->answers = $ready ? (json_decode($c->gate_checklist ?? '[]', true) ?: []) : [];
        $c->issues = $this->checklistIssues($c->answers);
        $c->seal_steps = $ready ? (json_decode($c->gate_seal_check ?? '[]', true) ?: []) : [];
        $c->edited_at = !empty($c->gate_edit_at) ? Carbon::parse($c->gate_edit_at) : null;
        $c->signs = json_decode($c->gate_signs ?? '[]', true) ?: [];
        $c->loaders = json_decode($c->gate_loaders ?? '[]', true) ?: [];
        $c->out_media = $this->outMedia($c);
        $c->guards = json_decode($c->gate_guards ?? '[]', true) ?: [];
        // PIC EXIM = user yang membuat shipment plan (export.userpk -> database akses)
        $pic = $this->exportPic($c->userpk ?? null);
        $c->pic = $pic->login ?? null;
        $c->pic_email = $pic->username ?? null;
        // NRK petugas security gate in; nama dari HRD diisi lewat inGuards() hanya untuk container yang dibuka
        $c->in_guard_nrks = array_values(array_filter(array_map('trim', explode(',', (string) ($c->{self::IN_GUARDS_COLUMN} ?? '')))));
        $c->in_guards = [];

        if ($c->departed_at) {
            $c->status = 'departed';
        } elseif ($c->arrived_at) {
            $c->status = $this->isRejected($c) ? 'rejected' : 'arrived';
        } elseif ($c->is_draft) {
            $c->status = 'incomplete';
        } elseif ($eta && $c->eta_days < 0) {
            $c->status = 'late';
        } elseif ($eta && $c->eta_days === 0) {
            $c->status = 'today';
        } else {
            $c->status = 'upcoming';
        }

        // urutan: Terlambat -> Hari ini -> Belum lengkap -> Akan datang -> Di area -> Keluar (terbaru dulu)
        $rank = ['late' => 0, 'today' => 1, 'incomplete' => 2, 'upcoming' => 3, 'arrived' => 4, 'rejected' => 4, 'departed' => 5][$c->status];
        $moment = $c->departed_at ?: $c->arrived_at;
        $c->sort_key = $rank . '|' . ($moment
            ? (9999999999 - $moment->timestamp)
            : ($eta ? $eta->format('Ymd') : '99999999')) . '|' . str_pad($c->contpk, 10, '0', STR_PAD_LEFT);

        return $c;
    }

    // user EXIM per userpk (login & email), disimpan selama request supaya daftar container tidak query berulang
    protected function exportPic($userpk): ?object
    {
        static $cache = [];
        if (!$userpk) {
            return null;
        }
        if (!array_key_exists($userpk, $cache)) {
            try {
                $cache[$userpk] = DB::connection('mysql_akses')->table('user')->where('userpk', $userpk)->first(['login', 'username']);
            } catch (\Throwable $e) {
                $cache[$userpk] = null;   // database akses tidak bisa diakses: PIC kosong, halaman tetap tampil
            }
        }
        return $cache[$userpk];
    }

    protected function isLcl($c): bool
    {
        return strtolower(trim((string) ($c->type ?? ''))) === 'lcl';
    }

    // hasil pemeriksaan "Tidak lulus" (gate_result = 0); null = data lama sebelum ada checklist
    protected function isRejected($c): bool
    {
        return isset($c->gate_result) && (int) $c->gate_result === 0;
    }

    // kode => [pertanyaan, jawaban aman, judul bagian, judul kelompok]
    protected function checklistItems(): array
    {
        static $items = null;
        if ($items === null) {
            $items = [];
            foreach (self::CHECKLIST as $section) {
                foreach ($section['groups'] as $group) {
                    foreach ($group['items'] as $code => [$question, $expect]) {
                        $items[$code] = [$question, $expect, $section['title'], $group['title']];
                    }
                }
            }
        }
        return $items;
    }

    // kode item yang jawabannya bukan jawaban aman
    protected function checklistIssues(array $answers): array
    {
        $issues = [];
        foreach ($this->checklistItems() as $code => $item) {
            if (isset($answers[$code]['v']) && $answers[$code]['v'] !== $item[1]) {
                $issues[] = $code;
            }
        }
        return $issues;
    }

    // jawaban dari client: {"a1_1":{"v":"T","n":"catatan"}}; kode/jawaban yang tidak dikenal dibuang
    protected function parseChecklist(?string $json): array
    {
        $raw = json_decode((string) $json, true);
        $answers = [];
        foreach ($this->checklistItems() as $code => $item) {
            $v = $raw[$code]['v'] ?? null;
            if ($v !== 'Y' && $v !== 'T') {
                continue;
            }
            $answers[$code] = ['v' => $v];
            $note = trim((string) ($raw[$code]['n'] ?? ''));
            if ($note !== '') {
                $answers[$code]['n'] = Str::limit($note, 200, '');
            }
        }
        return $answers;
    }

    // syarat simpan kedatangan / perubahan: checklist lengkap, temuan bercatatan, foto & video ada
    protected function completenessErrors($cont, array $data): array
    {
        $errors = [];
        $answers = json_decode($data['gate_checklist'] ?? '[]', true) ?: [];
        $missing = count(array_diff_key($this->checklistItems(), $answers));
        if ($missing) {
            $errors[] = "Jawab semua pertanyaan pemeriksaan ({$missing} belum dijawab).";
        }
        $noNote = count(array_filter($this->checklistIssues($answers), fn($code) => empty($answers[$code]['n'])));
        if ($noNote) {
            $errors[] = "Isi catatan untuk setiap temuan ({$noNote} belum ada catatan).";
        }
        if (empty($cont->gate_foto_id)) {
            $errors[] = 'Ambil foto identitas driver (KTP/SIM).';
        }
        // container lama (sebelum ada 5 sisi) yang sudah tercatat tiba tetap sah dengan foto lamanya
        $emptySlots = $this->contPhotosMissing($cont);
        $legacyOk = !empty($cont->actcontdate) && count($this->contPhotos($cont)['lama']) >= self::MIN_FOTO_LAMA;
        if ($emptySlots && !$legacyOk) {
            $errors[] = 'Ambil foto container: ' . Str::lower(implode(', ', $emptySlots)) . '.';
        }
        if (empty($cont->gate_video)) {
            $errors[] = 'Rekam video kondisi container sebelum menyimpan.';
        }
        return $errors;
    }

    // data pemasangan seal (VVTT) saat catat keluar / edit setelah keluar ($final: semua wajib)
    // atau simpan seal bertahap (boleh sebagian); no. seal diisi EXIM di shipment plan, security hanya mencocokkan
    protected function validateSeal(Request $request, $cont, bool $final = true): array
    {
        $rules = [
            'seal_check'   => $final ? 'required|array|size:' . count(self::SEAL_STEPS) : 'nullable|array|max:' . count(self::SEAL_STEPS),
            'seal_check.*' => 'distinct|in:' . implode(',', array_keys(self::SEAL_STEPS)),
        ];
        $messages = [
            'seal_check.required' => 'Centang keempat langkah pemasangan seal.',
            'seal_check.size'     => 'Centang keempat langkah pemasangan seal.',
        ];
        foreach (self::SEAL_PEOPLE as $col => $label) {
            $rules["{$col}_nrk"] = ($final ? 'required' : 'nullable') . '|string|max:100';
            $messages["{$col}_nrk.required"] = 'Scan ID card ' . Str::lower($label) . '.';
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function ($validator) use ($cont, $final) {
            if ($final && trim((string) $cont->noseal) === '') {
                $validator->errors()->add('noseal', 'No. seal belum diisi EXIM di shipment plan. Hubungi EXIM.');
            }
        });
        $v = $validator->validate();

        $data = [
            'gate_seal_check' => !empty($v['seal_check']) ? json_encode(array_values($v['seal_check'])) : null,
        ];

        // nama selalu dari HRD, bukan dari client; petugas yang belum di-scan (simpan bertahap) dikosongkan
        $nrks = [];
        foreach (array_keys(self::SEAL_PEOPLE) as $col) {
            if (empty($v["{$col}_nrk"])) {
                $data[$col] = $data["{$col}_nrk"] = null;
                continue;
            }
            $nrks[$col] = $this->normalizeKarid($v["{$col}_nrk"]);
        }
        try {
            $emps = $this->employees(array_values($nrks));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['general' => ['Database HRD tidak bisa diakses, data petugas seal belum bisa dicek. Coba lagi atau hubungi IT.']]);
        }
        $errors = [];
        foreach ($nrks as $col => $nrk) {
            $emp = $emps[$nrk] ?? null;
            if (!$emp) {
                $errors["{$col}_nrk"] = ["NRK {$nrk} tidak terdaftar di HRD. Scan ulang ID card."];
            } elseif (!$emp->active) {
                $errors["{$col}_nrk"] = ["{$emp->nama} (NRK {$nrk}) sudah tidak aktif."];
            } else {
                $data[$col] = Str::limit($emp->nama, 100, '');
                $data["{$col}_nrk"] = $emp->karid;
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    /**
     * Data muat dari form. $final (catat keluar / edit): semua wajib kecuali tiba di pelabuhan,
     * catatan & pengawal. Petugas muat, security & pengawal dari scan ID card (NRK), nama dari HRD.
     * Jam mulai muat & tutup container tidak dari form: mulai muat terisi saat finishing mulai muat
     * (Api\ShipplanController), tutup container saat foto seal terpasang diunggah (upload()).
     */
    protected function validateStuffing(Request $request, bool $final, $cont): array
    {
        $req = $final ? 'required' : 'nullable';
        $v = $request->validate([
            'gate_lokasi'        => "{$req}|string|max:50",
            'gate_country'       => "{$req}|string|max:100",
            'gate_open_at'       => "{$req}|date",
            'gate_port_at'       => 'nullable|date',
            'gate_stuffing_note' => 'nullable|string|max:255',
            'gate_loaders'       => ($final ? 'required|' : 'nullable|') . 'array|max:' . self::MAX_LOADERS,
            'gate_loaders.*'     => 'string|max:400',
            'gate_guards'        => ($final ? 'required|' : 'nullable|') . 'array|max:' . self::MAX_GUARDS,
            'gate_guards.*'      => 'string|max:400',
            'gate_escort_nrk'    => 'nullable|string|max:100',
        ], [
            'gate_lokasi.required'   => 'Isi lokasi muat.',
            'gate_country.required'  => 'Isi negara tujuan.',
            'gate_open_at.required'  => 'Isi jam buka container.',
            'gate_loaders.required'  => 'Scan ID card minimal 1 petugas muat.',
            'gate_loaders.max'       => 'Petugas muat maksimal ' . self::MAX_LOADERS . ' orang.',
            'gate_guards.required'   => 'Scan ID card minimal 1 petugas security.',
            'gate_guards.max'        => 'Petugas security maksimal ' . self::MAX_GUARDS . ' orang.',
        ]);

        $time = fn($key) => !empty($v[$key]) ? Carbon::parse($v[$key])->format('Y-m-d H:i:00') : null;
        $data = [
            'gate_lokasi'        => isset($v['gate_lokasi']) ? Str::upper(trim($v['gate_lokasi'])) : null,
            'gate_country'       => isset($v['gate_country']) ? Str::upper(trim($v['gate_country'])) : null,
            'gate_open_at'       => $time('gate_open_at'),
            'gate_port_at'       => $time('gate_port_at'),
            'gate_stuffing_note' => isset($v['gate_stuffing_note']) && trim($v['gate_stuffing_note']) !== '' ? trim($v['gate_stuffing_note']) : null,
        ];

        // urutan jam: buka <= muat <= tutup <= tiba di pelabuhan. Jam muat & tutup otomatis (tidak bisa diubah
        // security), jadi error ditaruh di jam yang bisa diubah; muat > tutup hanya dicek saat catat keluar / edit
        $errors = [];
        $stored = fn($col) => !empty($cont->$col) ? Carbon::parse($cont->$col)->format('Y-m-d H:i:00') : null;
        $times = $data + ['gate_load_at' => $stored('gate_load_at'), 'gate_close_at' => $stored('gate_close_at')];
        $order = ['gate_open_at' => 'buka container', 'gate_load_at' => 'mulai muat', 'gate_close_at' => 'tutup container', 'gate_port_at' => 'tiba di pelabuhan'];
        $auto = ['gate_load_at' => 'mulai muat dari finishing', 'gate_close_at' => 'tutup container dari foto seal terpasang'];
        $prev = null;
        foreach ($order as $key => $label) {
            if (!$times[$key]) {
                continue;
            }
            if ($prev && $times[$key] < $times[$prev[0]]) {
                $at = Carbon::parse($times[$key])->format('H:i');
                if (!isset($auto[$key])) {
                    $errors[$key] = ["Jam {$label} tidak boleh sebelum jam {$prev[1]}."];
                } elseif (!isset($auto[$prev[0]])) {
                    $errors[$prev[0]] = ["Jam {$prev[1]} tidak boleh setelah jam {$auto[$key]} ({$at})."];
                } elseif ($final) {
                    $errors['out_media'] = ["Foto seal terpasang ({$at}) diambil sebelum jam mulai muat dari finishing. Ambil ulang foto seal terpasang."];
                }
            }
            $prev = [$key, $label];
        }

        // petugas: NRK dicek ke HRD / harian lepas, nama & department disimpan dari database karyawan
        $lists = [
            'gate_loaders'    => $this->parsePeople($v['gate_loaders'] ?? [], self::PEOPLE_SOURCES['gate_loaders'], 'gate_loaders', $errors),
            'gate_guards'     => $this->parsePeople($v['gate_guards'] ?? [], self::PEOPLE_SOURCES['gate_guards'], 'gate_guards', $errors),
            'gate_escort_nrk' => !empty($v['gate_escort_nrk']) ? [['src' => 'hrd', 'karid' => $this->normalizeKarid($v['gate_escort_nrk'])]] : [],
        ];
        $people = $this->resolvePeople($lists, $errors);
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $data['gate_loaders'] = $people['gate_loaders'] ? json_encode($people['gate_loaders']) : null;
        $data['gate_guards'] = $people['gate_guards'] ? json_encode($people['gate_guards']) : null;
        $data['gate_escort_nrk'] = $people['gate_escort_nrk'][0]['karid'] ?? null;
        $data['gate_escort'] = $people['gate_escort_nrk'][0]['nama'] ?? null;

        return $data;
    }

    /**
     * Data otomatis untuk form muat: jumlah carton & lokasi (MIF) dari carton yang dimuat EXIM,
     * pelabuhan / bandara tujuan dari shipment plan, dan pengawal yang tersimpan.
     */
    protected function stuffingInfo($c): void
    {
        $c->carton_count = null;
        $c->lokasi_auto = null;
        try {
            $pack = DB::connection('mysql_andon')->table('pack')->where('contpk', $c->contpk)
                ->selectRaw("COUNT(DISTINCT CASE WHEN bundlepk IS NOT NULL AND bundlepk <> 0 THEN CONCAT('b', bundlepk)
                    WHEN mixno IS NOT NULL AND mixno <> '' THEN CONCAT('m', mixno)
                    ELSE CONCAT_WS('|', carton, POno, OP) END) AS cartons, GROUP_CONCAT(DISTINCT mif ORDER BY mif) AS mif")
                ->first();
            $c->carton_count = (int) ($pack->cartons ?? 0);
            $mifs = $pack->mif ?? null;
        } catch (\Throwable $e) {
            $mifs = null;   // database packing tidak bisa diakses: jumlah carton tidak tampil
        }
        $exp = DB::table('export')->leftJoin('plb', 'plb.plbpk', '=', 'export.plbpk')
            ->where('export.exportpk', $c->exportpk)->first(['export.factory', 'plb.plbnm', 'plb.kota']);
        $mifs = $mifs ?: ($exp->factory ?? null);
        if ($mifs) {
            $c->lokasi_auto = collect(explode(',', $mifs))->map(fn($m) => trim($m))->filter()->unique()
                ->map(fn($m) => 'MIF ' . $m)->implode(', ');
        }
        $c->port_name = $exp && $exp->plbnm ? $exp->plbnm . ($exp->kota ? ', ' . $exp->kota : '') : null;
        $c->escort = null;
        if (!empty($c->gate_escort_nrk)) {
            try {
                $depnm = $this->employees([$c->gate_escort_nrk])[$c->gate_escort_nrk]->depnm ?? null;
            } catch (\Throwable $e) {
                $depnm = null;
            }
            $c->escort = (object) ['karid' => $c->gate_escort_nrk, 'nama' => $c->gate_escort, 'depnm' => $depnm];
        }
    }

    // tanda tangan dari canvas (data URL PNG), satu per penanda tangan di SIGNERS. Yang tidak dikirim tetap
    // memakai yang sudah tersimpan (Simpan seal); $final: tiap penanda tangan wajib ada, baru atau tersimpan
    protected function validateSigns(Request $request, $cont, bool $final): array
    {
        $saved = $this->savedSigns($cont);
        $errors = [];
        $images = [];
        foreach (self::SIGNERS as $key => $label) {
            $raw = (string) $request->input("sign_{$key}", '');
            if ($raw === '') {
                if ($final && empty($saved[$key])) {
                    $errors["sign_{$key}"] = ["Tanda tangan {$label} belum diisi."];
                }
                continue;
            }
            $png = preg_match('#^data:image/png;base64,([A-Za-z0-9+/=]+)$#', $raw, $m) ? base64_decode($m[1], true) : false;
            if ($png === false || strlen($png) > self::MAX_SIGN_BYTES || (@getimagesizefromstring($png)[2] ?? null) !== IMAGETYPE_PNG) {
                $errors["sign_{$key}"] = ["Tanda tangan {$label} tidak valid. Ulangi tanda tangan."];
                continue;
            }
            $images[$key] = $png;
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
        return $images;
    }

    // tanda tangan baru menimpa yang tersimpan (file lama dihapus); mengembalikan semua tanda tangan
    protected function saveSigns($cont, array $images): array
    {
        $files = $this->savedSigns($cont);
        if (!$images) {
            return $files;
        }
        $folder = $this->mediaFolder($cont->contpk);
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }
        foreach ($images as $key => $png) {
            $this->deleteMedia($cont->contpk, $files[$key] ?? null);
            $files[$key] = "sign_{$key}_" . date('YmdHis') . '_' . Str::lower(Str::random(6)) . '.png';
            file_put_contents("{$folder}/{$files[$key]}", $png);
        }
        return $files;
    }

    // tanda tangan tersimpan: penanda tangan => file
    protected function savedSigns($cont): array
    {
        $raw = json_decode($cont->{self::SIGN_COLUMN} ?? '[]', true);
        return array_filter(array_intersect_key(is_array($raw) ? $raw : [], self::SIGNERS), 'is_string');
    }

    // isi QR ID card -> NRK: tanpa spasi; lebih dari 6 karakter diambil 6 terakhir (NRK 6 digit)
    protected function normalizeKarid(string $raw): string
    {
        $karid = preg_replace('/\s+/', '', $raw);
        return strlen($karid) > 6 ? substr($karid, -6) : $karid;
    }

    /**
     * Karyawan dari HRD per karid: karid, nama, depnm, active.
     * kar.aktif = 0 berarti masih aktif (karyawan baru masuk tercatat 0).
     */
    protected function employees(array $karids): array
    {
        return $this->employeesFrom('mysql_hrd', $karids, 'hrd');
    }

    // karyawan harian lepas (database hl, table kar & dep sama dengan HRD)
    protected function employeesHl(array $karids): array
    {
        return $this->employeesFrom('mysql_hl', $karids, 'hl');
    }

    protected function employeesFrom(string $conn, array $karids, string $src): array
    {
        $karids = array_values(array_unique(array_filter($karids, fn($k) => $k !== null && $k !== '')));
        if (!$karids) {
            return [];
        }

        return $this->karQuery($conn)
            ->whereIn('kar.karid', $karids)
            ->get()
            ->mapWithKeys(fn($e) => [$e->karid => $this->employeeRow($e, $src)])
            ->all();
    }

    // data karyawan yang ditampilkan: nama, NRK, department
    protected function karQuery(string $conn)
    {
        return DB::connection($conn)->table('kar')
            ->leftJoin('dep', 'dep.deppk', '=', 'kar.deppk')
            ->select('kar.karid', 'kar.nama', 'kar.aktif', 'dep.depnm');
    }

    protected function employeeRow($e, string $src): object
    {
        $clean = fn($v) => ($v = trim(preg_replace('/\s+/', ' ', (string) $v))) !== '' ? $v : null;
        return (object) [
            'karid'  => $e->karid,
            'nama'   => $clean($e->nama),
            'depnm'  => $clean($e->depnm),
            'active' => (int) $e->aktif === 0,
            'src'    => $src,
        ];
    }

    /**
     * Daftar petugas hasil parsePeople -> data karyawan dari HRD / harian lepas: [key => [{karid, nama, depnm, src}]].
     * NRK yang tidak terdaftar / tidak aktif dicatat di $errors[key]; database tidak bisa diakses langsung dilempar.
     */
    protected function resolvePeople(array $lists, array &$errors): array
    {
        $idsOf = fn(string $src) => collect($lists)->flatten(1)->where('src', $src)->pluck('karid')->unique()->values()->all();
        try {
            $emps = $this->employees($idsOf('hrd'));
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['general' => ['Database HRD tidak bisa diakses, data petugas belum bisa dicek. Coba lagi atau hubungi IT.']]);
        }
        $hlEmps = [];
        if ($idsOf('hl')) {
            try {
                $hlEmps = $this->employeesHl($idsOf('hl'));
            } catch (\Throwable $e) {
                throw ValidationException::withMessages(['gate_loaders' => ['Database karyawan harian lepas tidak bisa diakses, petugas harian lepas belum bisa dicek. Coba lagi atau hubungi IT.']]);
            }
        }
        $people = [];
        foreach ($lists as $key => $items) {
            $people[$key] = [];
            foreach ($items as $item) {
                $nrk = $item['karid'];
                $emp = ($item['src'] === 'hl' ? $hlEmps : $emps)[$nrk] ?? null;
                $where = $item['src'] === 'hl' ? 'data harian lepas' : 'HRD';
                if (!$emp) {
                    $errors[$key][] = "NRK {$nrk} tidak terdaftar di {$where}.";
                } elseif (!$emp->active) {
                    $errors[$key][] = "{$emp->nama} (NRK {$nrk}) sudah tidak aktif.";
                } else {
                    $people[$key][] = ['karid' => $emp->karid, 'nama' => Str::limit($emp->nama, 100, ''), 'depnm' => $emp->depnm, 'src' => $item['src']];
                }
            }
        }
        return $people;
    }

    /**
     * Nilai input daftar petugas -> [{src, karid}], urutan dipertahankan, tanpa dobel.
     * Harian lepas di daftar yang tidak mengizinkannya ($allow, lihat PEOPLE_SOURCES) dicatat sebagai error.
     */
    protected function parsePeople(array $tokens, array $allow, string $key, array &$errors): array
    {
        $out = [];
        foreach ($tokens as $token) {
            $token = trim((string) $token);
            if ($token === '') {
                continue;
            }
            $src = Str::startsWith($token, 'hl:') ? 'hl' : 'hrd';
            if ($src === 'hl' && !$allow['hl']) {
                $errors[$key][] = 'Karyawan harian lepas tidak bisa dipakai di daftar ini.';
                continue;
            }
            $karid = $this->normalizeKarid($src === 'hl' ? substr($token, 3) : $token);
            if ($karid === '') {
                continue;
            }
            $out[$src . '|' . $karid] = ['src' => $src, 'karid' => $karid];
        }
        if (!empty($errors[$key])) {
            $errors[$key] = array_values(array_unique($errors[$key]));   // pesan yang sama cukup sekali
        }
        return array_values($out);
    }

    // ---------- lokasi stuffing (factory & pintu) ----------

    protected function doorsReady(): bool
    {
        static $ready = null;
        return $ready ?? ($ready = Schema::hasTable(self::STUFFING_TABLE));
    }

    // pos MIF dari akun login security (Security1 = MIF 1, Security2 = MIF 2); akun lain: null (boleh semua MIF)
    protected function posMif(?string $login = null): ?int
    {
        return self::SECURITY_POS[Str::lower(trim((string) ($login ?? Session::get('login'))))] ?? null;
    }

    // acuan container di papan pintu: no. container, LCL pakai plat truk
    protected function identOf($c): string
    {
        $ident = $this->isLcl($c) ? $c->nopol : $this->formatContNo($c->contno);
        return $ident ?: ('#' . $c->contpk);
    }

    /**
     * Posisi & riwayat lokasi stuffing satu container:
     *   active  kunjungan yang masih di pintu (null kalau tidak di pintu)
     *   mif     factory tempat container sekarang (aktif / tujuan pindah terakhir / pos security gate in)
     *   transit true kalau kunjungan terakhir "pindah" (sedang menuju / sudah sampai factory tujuan, belum di pintu)
     *   history semua kunjungan, terbaru dulu; occupied [mif][pintu] => ident container lain yang sedang di pintu
     */
    protected function stuffingState($c): object
    {
        $visits = DB::table(self::STUFFING_TABLE)->where('contpk', $c->contpk)->orderByDesc('stfpk')->get();
        $active = $visits->firstWhere('aktif', 1);
        $last = $visits->first();
        if ($active) {
            $mif = (int) $active->mif;
        } elseif ($last && $last->status === 'pindah' && $last->tujuan_mif) {
            $mif = (int) $last->tujuan_mif;
        } elseif ($last) {
            $mif = (int) $last->mif;
        } else {
            $gateLogin = !empty($c->gate_userpk)
                ? DB::connection('mysql_akses')->table('user')->where('userpk', $c->gate_userpk)->value('login')
                : null;
            $mif = $this->posMif($gateLogin);
        }

        $logins = DB::connection('mysql_akses')->table('user')
            ->whereIn('userpk', $visits->pluck('masuk_userpk')->merge($visits->pluck('keluar_userpk'))->filter()->unique())
            ->pluck('login', 'userpk');
        $history = $visits->map(function ($v) use ($logins) {
            $v->masuk = Carbon::parse($v->masuk_at);
            $v->keluar = $v->keluar_at ? Carbon::parse($v->keluar_at) : null;
            $v->masuk_by = $logins[$v->masuk_userpk] ?? null;
            $v->keluar_by = $logins[$v->keluar_userpk] ?? null;
            return $v;
        });

        $occupied = [];
        $others = DB::table(self::STUFFING_TABLE . ' as s')->join('cont', 'cont.contpk', '=', 's.contpk')
            ->where('s.aktif', 1)->where('s.contpk', '<>', $c->contpk)
            ->get(['s.mif', 's.pintu', 'cont.contpk', 'cont.contno', 'cont.type', 'cont.nopol']);
        foreach ($others as $o) {
            $occupied[(int) $o->mif][(int) $o->pintu] = $this->identOf($o);
        }

        return (object) [
            'active'   => $active ? $history->firstWhere('stfpk', $active->stfpk) : null,
            'mif'      => $mif,
            'transit'  => !$active && $last && $last->status === 'pindah',
            'last'     => $last ? $history->first() : null,
            'history'  => $history,
            'occupied' => $occupied,
        ];
    }

    /**
     * Papan pintu (halaman Pintu): [mif => ['doors' => [pintu => container|null], 'incoming' => [...], 'waiting' => [...]]]
     *   incoming  kunjungan terakhir "pindah" ke MIF itu, belum di pintu & belum keluar pabrik
     *   waiting   sudah gate in & lulus, belum keluar, tidak di pintu & tidak sedang pindah (posisi: MIF kunjungan
     *             terakhir, atau pos security yang mencatat gate in); maks. 14 hari sejak gate in
     * Container yang posisinya tidak diketahui masuk ke ['unplaced'].
     */
    protected function doorBoard(): array
    {
        $cols = ['s.stfpk', 's.contpk', 's.mif', 's.pintu', 's.masuk_at', 's.keluar_at', 's.tujuan_mif', 's.status',
            'cont.contno', 'cont.type', 'cont.nopol', 'cont.exportpk', 'export.buyer'];
        $visits = fn() => DB::table(self::STUFFING_TABLE . ' as s')->join('cont', 'cont.contpk', '=', 's.contpk')
            ->join('export', 'export.exportpk', '=', 'cont.exportpk');
        $active = $visits()->where('s.aktif', 1)->get($cols);
        $latest = DB::table(self::STUFFING_TABLE)->selectRaw('MAX(stfpk) AS stfpk')->groupBy('contpk');
        $last = $visits()->joinSub($latest, 'x', 'x.stfpk', '=', 's.stfpk')->whereNull('cont.gate_out_at')->get($cols)->keyBy('contpk');

        $board = ['unplaced' => []];
        foreach (self::DOORS as $mif => $count) {
            $board[$mif] = ['doors' => array_fill_keys(range(1, $count), null), 'incoming' => [], 'waiting' => []];
        }
        $item = fn($r, $at) => (object) [
            'contpk' => $r->contpk, 'ident' => $this->identOf($r), 'pintu' => (int) ($r->pintu ?? 0), 'from' => (int) ($r->mif ?? 0),
            'idship' => 'SP' . str_pad($r->exportpk, 4, '0', STR_PAD_LEFT), 'buyer' => $r->buyer, 'at' => $at ? Carbon::parse($at) : null,
        ];
        foreach ($active as $r) {
            // slot pintu kosong bernilai null: pakai array_key_exists (isset() menganggap null tidak ada)
            if (isset($board[(int) $r->mif]) && array_key_exists((int) $r->pintu, $board[(int) $r->mif]['doors'])) {
                $board[(int) $r->mif]['doors'][(int) $r->pintu] = $item($r, $r->masuk_at);
            }
        }
        foreach ($last as $r) {
            if ($r->status === 'pindah' && isset($board[(int) $r->tujuan_mif])) {
                $board[(int) $r->tujuan_mif]['incoming'][] = $item($r, $r->keluar_at);
            }
        }

        // menunggu pintu: sudah gate in & lulus, belum keluar, tidak di pintu / tidak sedang pindah
        $waiting = DB::table('cont')->join('export', 'export.exportpk', '=', 'cont.exportpk')
            ->whereNotNull('cont.actcontdate')->whereNull('cont.gate_out_at')
            ->where('cont.actcontdate', '>=', now()->subDays(14))
            ->where(fn($q) => $q->whereNull('cont.gate_result')->orWhere('cont.gate_result', '<>', 0))
            ->where(fn($q) => $q->whereNull('export.stscancel')->orWhere('export.stscancel', '<>', 1))
            ->whereNotIn('cont.contpk', $active->pluck('contpk'))
            ->orderBy('cont.actcontdate')
            ->get(['cont.contpk', 'cont.contno', 'cont.type', 'cont.nopol', 'cont.exportpk', 'cont.actcontdate', 'cont.gate_userpk', 'export.buyer']);
        $gateLogins = DB::connection('mysql_akses')->table('user')
            ->whereIn('userpk', $waiting->pluck('gate_userpk')->filter()->unique())->pluck('login', 'userpk');
        foreach ($waiting as $r) {
            $v = $last->get($r->contpk);
            if ($v && $v->status === 'pindah') {
                continue;   // sudah tampil di "menuju MIF"
            }
            $mif = $v ? (int) $v->mif : $this->posMif($gateLogins[$r->gate_userpk] ?? null);
            $entry = $item($r, $r->actcontdate);
            if ($mif && isset($board[$mif])) {
                $board[$mif]['waiting'][] = $entry;
            } else {
                $board['unplaced'][] = $entry;
            }
        }
        return $board;
    }

    protected function dockBadge($activeVisits): string
    {
        $pos = $this->posMif();
        $mifs = $pos !== null ? [$pos] : array_keys(self::DOORS);
        $total = array_sum(array_intersect_key(self::DOORS, array_flip($mifs)));
        return $activeVisits->whereIn('mif', $mifs)->count() . '/' . $total;
    }

    // riwayat pintu hari ini (masuk pintu, pindah, kosongkan, keluar), terbaru dulu
    protected function dockHistory(): array
    {
        $today = Carbon::today();
        $rows = DB::table(self::STUFFING_TABLE . ' as s')->join('cont', 'cont.contpk', '=', 's.contpk')
            ->where(fn($q) => $q->where('s.masuk_at', '>=', $today)->orWhere('s.keluar_at', '>=', $today))
            ->get(['s.*', 'cont.contno', 'cont.type', 'cont.nopol']);
        $logins = DB::connection('mysql_akses')->table('user')
            ->whereIn('userpk', $rows->pluck('masuk_userpk')->merge($rows->pluck('keluar_userpk'))->filter()->unique())
            ->pluck('login', 'userpk');
        $events = [];
        foreach ($rows as $r) {
            $base = ['contpk' => $r->contpk, 'ident' => $this->identOf($r), 'mif' => (int) $r->mif, 'pintu' => (int) $r->pintu];
            if ((int) $r->pintu > 0 && Carbon::parse($r->masuk_at)->gte($today)) {
                $events[] = $base + ['at' => Carbon::parse($r->masuk_at), 'kind' => 'masuk', 'text' => 'Masuk pintu ' . $r->pintu, 'by' => $logins[$r->masuk_userpk] ?? null, 'note' => null];
            }
            if ($r->keluar_at && Carbon::parse($r->keluar_at)->gte($today)) {
                $text = [
                    'pindah' => 'Pindah ke MIF ' . $r->tujuan_mif . ((int) $r->pintu ? '' : ' (tanpa pintu)'),
                    'keluar' => 'Keluar pabrik',
                    'batal'  => 'Pintu dikosongkan (salah pilih)',
                ][$r->status] ?? $r->status;
                $events[] = $base + ['at' => Carbon::parse($r->keluar_at), 'kind' => $r->status, 'text' => $text, 'by' => $logins[$r->keluar_userpk] ?? null, 'note' => $r->catatan];
            }
        }
        usort($events, fn($a, $b) => $b['at'] <=> $a['at']);
        return array_map(fn($e) => (object) $e, $events);
    }

    // tutup kunjungan pintu yang masih aktif (dipanggil saat container keluar pabrik)
    protected function closeVisit(int $contpk, string $status, Carbon $at, array $extra = []): int
    {
        if (!$this->doorsReady()) {
            return 0;
        }
        return DB::table(self::STUFFING_TABLE)->where('contpk', $contpk)->where('aktif', 1)->update($extra + [
            'keluar_at'     => $at,
            'keluar_userpk' => Session::get('userpk'),
            'status'        => $status,
            'aktif'         => null,
        ]);
    }

    // petugas security gate in yang tersimpan (NRK) -> [{karid, nama, depnm, src}]; HRD tidak bisa diakses: NRK saja
    protected function inGuards($c): array
    {
        if (empty($c->in_guard_nrks)) {
            return [];
        }
        try {
            $emps = $this->employees($c->in_guard_nrks);
        } catch (\Throwable $e) {
            $emps = [];
        }
        return array_map(fn($nrk) => [
            'karid' => $nrk,
            'nama'  => $emps[$nrk]->nama ?? 'NRK ' . $nrk,
            'depnm' => $emps[$nrk]->depnm ?? null,
            'src'   => 'hrd',
        ], $c->in_guard_nrks);
    }

    // petugas seal yang tersimpan: kolom => {karid, nama, depnm}; data lama tanpa NRK hanya punya nama
    protected function sealPeople($c): array
    {
        try {
            $emps = $this->employees(array_map(fn($col) => $c->{"{$col}_nrk"} ?? null, array_keys(self::SEAL_PEOPLE)));
        } catch (\Throwable $e) {
            $emps = [];   // HRD tidak bisa diakses: tampilkan nama yang tersimpan saja
        }

        $people = [];
        foreach (array_keys(self::SEAL_PEOPLE) as $col) {
            $nrk = $c->{"{$col}_nrk"} ?? null;
            $name = $c->{$col} ?? null;
            $people[$col] = $nrk || $name ? (object) [
                'karid' => $nrk,
                'nama'  => $name ?: ($emps[$nrk]->nama ?? null),
                'depnm' => $nrk ? ($emps[$nrk]->depnm ?? null) : null,
            ] : null;
        }
        return $people;
    }

    // isian yang sering dipakai, untuk saran ketik (datalist)
    protected function suggestions(): array
    {
        $recent = function (array $cols) {
            $values = collect();
            foreach ($cols as $col) {
                $values = $values->merge(DB::table('cont')->whereNotNull($col)->where($col, '<>', '')
                    ->orderByDesc('contpk')->limit(200)->pluck($col));
            }
            return $values->map(fn($v) => trim($v))->unique()->take(30)->values();
        };

        return [
            'unit'        => $recent(['gate_unit'])->prepend(self::DEFAULT_UNIT)->unique()->values(),
            'country'     => in_array('gate_country', $this->contColumns(), true) ? $recent(['gate_country']) : collect(),
        ];
    }

    // MSKU1234567 -> "MSKU 123456 7" (prefix, nomor seri, check digit)
    protected function formatContNo($no): ?string
    {
        if (!$no) {
            return null;
        }
        $clean = Str::upper(preg_replace('/\s+/', '', $no));
        return preg_match('/^([A-Z]{4})(\d{6})(\d)$/', $clean, $m) ? "{$m[1]} {$m[2]} {$m[3]}" : $no;
    }

    // $guardsRequired: petugas security gate in wajib (simpan kedatangan); edit data lama boleh kosong
    protected function validateTruck(Request $request, bool $final, ?bool $guardsRequired = null): array
    {
        $guardsRequired = $guardsRequired ?? $final;
        $req = $final ? 'required' : 'nullable';
        $v = $request->validate([
            'contno'           => 'nullable|string|max:100',
            'gate_unit'        => 'nullable|string|max:100',
            'gate_driver'      => "{$req}|string|max:150",
            'gate_driver_hp'   => ['nullable', 'string', 'max:30', function ($attr, $value, $fail) {
                if (strlen(preg_replace('/\D/', '', $value)) < 10) {
                    $fail('Nomor HP minimal 10 digit.');
                }
            }],
            'gate_sim_no'      => "{$req}|string|max:50",
            'gate_sim_exp'     => "{$req}|date",
            'checklist'        => 'nullable|string|max:20000',
            'gate_result'      => "{$req}|in:0,1",
            'gate_result_note' => ($final ? 'required_if:gate_result,0|' : '') . 'nullable|string|max:255',
        ], [
            'gate_driver.required'         => 'Isi nama driver.',
            'gate_sim_no.required'         => 'Isi nomor SIM driver.',
            'gate_sim_exp.required'        => 'Isi masa berlaku SIM.',
            'gate_sim_exp.date'            => 'Tanggal masa berlaku SIM tidak valid.',
            'gate_result.required'         => 'Pilih hasil pemeriksaan.',
            'gate_result_note.required_if' => 'Tulis alasan container tidak lulus.',
        ]);

        $text = fn($key) => isset($v[$key]) && trim($v[$key]) !== '' ? trim($v[$key]) : null;
        $answers = $this->parseChecklist($v['checklist'] ?? null);

        // plat nomor truk (nopol) diisi EXIM di shipment plan, tidak diubah security
        $data = [
            // perusahaan angkutan = forwarder dari shipment plan; unit pemuat bawaan Finishing
            'gate_transporter' => $this->forwarderName($request->route('contpk')),
            'gate_unit'        => $text('gate_unit') ?: self::DEFAULT_UNIT,
            'gate_driver'      => $text('gate_driver'),
            'gate_driver_hp'   => $text('gate_driver_hp'),
            'gate_sim_no'      => isset($v['gate_sim_no']) ? Str::upper(trim($v['gate_sim_no'])) : null,
            'gate_sim_exp'     => !empty($v['gate_sim_exp']) ? Carbon::parse($v['gate_sim_exp'])->toDateString() : null,
            'gate_checklist'   => $answers ? json_encode($answers) : null,
            'gate_result'      => isset($v['gate_result']) && $v['gate_result'] !== '' ? (int) $v['gate_result'] : null,
            'gate_result_note' => $text('gate_result_note'),
        ];
        // petugas security saat gate in (wajib saat simpan kedatangan, kalau kolomnya sudah ada)
        if ($this->inGuardsReady()) {
            $request->validate([
                'gate_in_guards'   => ($guardsRequired ? 'required|' : 'nullable|') . 'array|max:' . self::MAX_GUARDS,
                'gate_in_guards.*' => 'string|max:100',
            ], [
                'gate_in_guards.required' => 'Scan ID card / cari minimal 1 petugas security.',
                'gate_in_guards.max'      => 'Petugas security maksimal ' . self::MAX_GUARDS . ' orang.',
            ]);
            $errors = [];
            $lists = ['gate_in_guards' => $this->parsePeople((array) $request->input('gate_in_guards', []), self::PEOPLE_SOURCES['gate_in_guards'], 'gate_in_guards', $errors)];
            $people = $this->resolvePeople($lists, $errors);
            if ($errors) {
                throw ValidationException::withMessages($errors);
            }
            // cukup NRK (kolom varchar 100); nama & department dibaca ulang dari HRD saat ditampilkan
            $data[self::IN_GUARDS_COLUMN] = $people['gate_in_guards'] ? implode(',', array_column($people['gate_in_guards'], 'karid')) : null;
        }
        // no. container dari shipment plan; security hanya boleh mengisi kalau masih kosong
        if (!empty($v['contno'])) {
            $data['contno_new'] = Str::upper(preg_replace('/\s+/', '', $v['contno']));
        }

        return $this->applyContNo($request->route('contpk'), $data);
    }

    protected function forwarderName($contpk): ?string
    {
        $name = DB::table('cont')->leftJoin('fwd', 'fwd.fwdpk', '=', 'cont.fwdpk')
            ->where('cont.contpk', (int) $contpk)->value('fwd.fwdnm');
        return trim((string) $name) !== '' ? trim($name) : null;
    }

    protected function applyContNo($contpk, array $data): array
    {
        if (isset($data['contno_new'])) {
            $current = DB::table('cont')->where('contpk', $contpk)->value('contno');
            if (empty($current)) {
                $data['contno'] = $data['contno_new'];
            }
            unset($data['contno_new']);
        }
        return $data;
    }

    // container yang bisa diubah security: ada, kolom gate siap, belum tercatat tiba
    // ($afterArrival: boleh juga yang sudah tiba / keluar, untuk mode edit)
    protected function editableCont($contpk, bool $afterArrival = false)
    {
        abort_unless($this->gateColumnsReady(), 503, 'Kolom gate pada table cont belum lengkap. Hubungi IT.');

        $cont = DB::table('cont')->where('contpk', (int) $contpk)->first();
        abort_unless($cont, 404, 'Container tidak ditemukan.');
        abort_if(!$afterArrival && !empty($cont->actcontdate), 422, 'Kedatangan container ini sudah tercatat.');

        return $cont;
    }

    // gate_foto_cont -> ["depan" => file|null, ..., "lama" => [file, ...]] (lihat CONT_PHOTO_SLOTS)
    protected function contPhotos($cont): array
    {
        $raw = json_decode($cont->gate_foto_cont ?? '', true);
        $raw = is_array($raw) ? $raw : [];
        $isList = $raw && array_keys($raw) === range(0, count($raw) - 1);

        $photos = [];
        foreach (array_keys(self::CONT_PHOTO_SLOTS) as $slot) {
            $photos[$slot] = !$isList && is_string($raw[$slot] ?? null) ? $raw[$slot] : null;
        }
        $old = $isList ? $raw : ($raw['lama'] ?? []);
        $photos['lama'] = array_values(array_filter(is_array($old) ? $old : [], 'is_string'));
        return $photos;
    }

    protected function encodeContPhotos(array $photos): ?string
    {
        $data = array_filter(array_intersect_key($photos, self::CONT_PHOTO_SLOTS));
        if ($photos['lama']) {
            $data['lama'] = array_values($photos['lama']);
        }
        return $data ? json_encode($data) : null;
    }

    // label sisi container yang belum difoto
    protected function contPhotosMissing($cont): array
    {
        $photos = $this->contPhotos($cont);
        $missing = [];
        foreach (self::CONT_PHOTO_SLOTS as $slot => [$label]) {
            if (!$photos[$slot]) {
                $missing[] = $label;
            }
        }
        return $missing;
    }

    // semua file foto container: sisi yang sudah difoto + foto lama
    protected function fotoList($cont): array
    {
        $photos = $this->contPhotos($cont);
        return array_merge(array_values(array_filter(array_intersect_key($photos, self::CONT_PHOTO_SLOTS))), $photos['lama']);
    }

    /**
     * Kompres foto di server (GD) kalau masih lebih besar dari MAX_FOTO_SIDE atau > 600 KB,
     * mis. perangkat gagal mengompres. Hasil JPEG; mengembalikan ekstensi baru, null kalau tidak diubah.
     */
    protected function compactImage(string $path, string $mime): ?string
    {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }
        $size = @getimagesize($path);
        if (!$size || (max($size[0], $size[1]) <= self::MAX_FOTO_SIDE && filesize($path) <= 600 * 1024)) {
            return null;
        }
        $img = @imagecreatefromstring(file_get_contents($path));
        if (!$img) {
            return null;
        }

        // foto kamera menyimpan arah putar di EXIF; GD tidak membacanya
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $angle = [3 => 180, 6 => -90, 8 => 90][(int) (@exif_read_data($path)['Orientation'] ?? 1)] ?? 0;
            if ($angle) {
                $img = imagerotate($img, $angle, 0);
            }
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::MAX_FOTO_SIDE / max($w, $h));
        $nw = (int) round($w * $scale);
        $nh = (int) round($h * $scale);
        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));   // latar putih untuk PNG transparan
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $ok = imagejpeg($out, $path, self::FOTO_QUALITY);
        imagedestroy($img);
        imagedestroy($out);
        clearstatcache(true, $path);

        return $ok ? 'jpg' : null;
    }

    protected function mediaFolder($contpk): string
    {
        return public_path('upload/gate/' . (int) $contpk);
    }

    protected function deleteMedia($contpk, ?string $file): void
    {
        if ($file && basename($file) === $file) {
            @unlink($this->mediaFolder($contpk) . '/' . $file);
        }
    }

    protected function mediaPayload($contpk): array
    {
        $cols = array_merge(['gate_foto_id', 'gate_foto_cont', 'gate_video'], $this->outMediaReady() ? [self::OUT_MEDIA_COLUMN] : [],
            $this->stuffReady() ? ['gate_close_at'] : []);
        $cont = DB::table('cont')->where('contpk', $contpk)->first($cols);
        $url = fn($f) => $f ? asset('public/upload/gate/' . (int) $contpk . '/' . $f) : null;

        $out = $this->outMedia($cont);
        $outPayload = [
            'out_video'    => $url($out['out_video']),
            'out_exit_at'  => $out['out_exit_at'] ? Carbon::parse($out['out_exit_at'])->isoFormat('HH:mm, D MMM YYYY') : null,
            'out_close_at' => !empty($cont->gate_close_at) ? Carbon::parse($cont->gate_close_at)->isoFormat('HH:mm, D MMM YYYY') : null,
        ];
        foreach (array_keys(self::OUT_PHOTO_KINDS) as $kind) {
            $outPayload[$kind] = array_map(fn($f) => ['file' => $f, 'url' => $url($f)], $out[$kind]);
        }

        $photos = $this->contPhotos($cont);
        return [
            'id'       => $url($cont->gate_foto_id),
            'video'    => $url($cont->gate_video),
            'cont'     => array_map($url, array_intersect_key($photos, self::CONT_PHOTO_SLOTS)),
            'cont_old' => array_map(fn($f) => ['file' => $f, 'url' => $url($f)], $photos['lama']),
            'out'      => $outPayload,
        ];
    }
}
