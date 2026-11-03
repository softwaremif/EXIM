<?php

namespace App\Console\Commands;

use App\Mail\StuffingCompleteReminder;
use App\Services\StuffingProgress;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyStuffingComplete extends Command
{
    protected $signature = 'shipplan:notify-stuffing-complete
                            {--dry-run : Tampilkan shipment yang akan dinotifikasi tanpa mengirim email}
                            {--force : Abaikan batas 1x sehari per shipment}';

    protected $description = 'Email ke PIC EXIM: shipment yang stuffing-nya 100% tapi status masih Confirm';

    public function handle()
    {
        $onlyUsers = config('shipplan.stuffing_notif.only_users', []);
        $redirectTo = config('shipplan.stuffing_notif.redirect_to');
        $ccList = config('shipplan.stuffing_notif.cc', []);

        $userpks = null;
        if (!empty($onlyUsers)) {
            $userpks = DB::connection('mysql_akses')->table('user')
                ->whereIn('email', $onlyUsers)
                ->pluck('userpk')
                ->all();

            if (empty($userpks)) {
                $this->warn('User di STUFFING_NOTIF_ONLY_USERS tidak ditemukan: ' . implode(', ', $onlyUsers));
                return 0;
            }
        }

        // status Confirm = SAMA dengan kolom Status di list shipment plan:
        // tidak cancel, belum semua container ber-segel (Complete), Ex-Factory terisi
        $candidates = DB::table('export')
            ->leftJoin('cont', 'cont.exportpk', '=', 'export.exportpk')
            ->where(function ($q) {
                $q->whereNull('export.stscancel')->orWhere('export.stscancel', '<>', 1);
            })
            ->whereNotNull('export.exdate')
            ->when($userpks !== null, fn($q) => $q->whereIn('export.userpk', $userpks))
            ->groupBy('export.exportpk')
            ->havingRaw('NOT (COUNT(cont.contpk) > 0 AND COUNT(cont.segel) = COUNT(cont.contpk))')
            ->select('export.exportpk', 'export.userpk', 'export.OP', 'export.buyer', 'export.closetime')
            ->selectRaw("GROUP_CONCAT(CASE WHEN cont.segel IS NULL THEN cont.contno END ORDER BY cont.contno SEPARATOR '||') AS unsealed")
            ->get();

        $progress = StuffingProgress::forExports($candidates->pluck('exportpk'));
        $today = Carbon::today()->toDateString();

        $due = $candidates->filter(function ($row) use ($progress, $today) {
            $p = $progress->get((int) $row->exportpk);
            if (!$p || $p['total'] === 0 || $p['loaded'] < $p['total']) {
                return false;
            }

            return $this->option('force') || Cache::get($this->cacheKey($row->exportpk)) !== $today;
        });

        if ($due->isEmpty()) {
            $this->info('Tidak ada shipment stuffing 100% berstatus Confirm yang perlu dinotifikasi.');
            return 0;
        }

        // alamat tujuan = akses.user.email milik PIC shipment (export.userpk)
        $users = DB::connection('mysql_akses')->table('user')
            ->whereIn('userpk', $due->pluck('userpk')->unique()->all())
            ->get(['userpk', 'login', 'email'])
            ->keyBy('userpk');

        foreach ($due->groupBy('userpk') as $userpk => $rows) {
            $user = $users->get($userpk);
            $email = trim((string) ($user->email ?? ''));

            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->warn("Lewati userpk {$userpk}: kolom email kosong / tidak valid ({$email}).");
                continue;
            }

            $shipments = $rows->map(function ($row) use ($progress) {
                $p = $progress->get((int) $row->exportpk);

                return [
                    'exportpk'  => $row->exportpk,
                    'idship'    => 'SP' . str_pad($row->exportpk, 4, '0', STR_PAD_LEFT),
                    'op'        => implode(', ', array_unique(array_filter(array_map('trim', explode(',', (string) $row->OP))))),
                    'buyer'     => $row->buyer,
                    'unsealed'  => array_values(array_filter(explode('||', (string) $row->unsealed))),
                    'loaded'    => $p['loaded'],
                    'total'     => $p['total'],
                    'closetime' => $row->closetime ? Carbon::parse($row->closetime)->format('d/m/Y H:i') : null,
                    'url'       => route('shipplan.detail_ship', $row->exportpk),
                ];
            })->values()->all();

            $to = $redirectTo ?: $email;
            // alamat CC yang sama dengan penerima tidak perlu dikirim dobel
            $cc = array_values(array_filter($ccList, fn($addr) => strcasecmp($addr, $to) !== 0));
            $this->line(sprintf(
                '%s -> %s%s: %s',
                $email,
                $to,
                $cc ? ' (cc: ' . implode(', ', $cc) . ')' : '',
                implode(', ', array_column($shipments, 'idship'))
            ));

            if ($this->option('dry-run')) {
                continue;
            }

            try {
                Mail::to($to)
                    ->cc($cc)
                    ->send(new StuffingCompleteReminder($user, $shipments, $redirectTo ? $email : null));
            } catch (\Throwable $e) {
                report($e);
                $this->error("Gagal kirim ke {$to}: " . $e->getMessage());
                continue;
            }

            foreach ($shipments as $s) {
                Cache::put($this->cacheKey($s['exportpk']), $today, Carbon::tomorrow()->addDay());
            }

            Log::info('Notif stuffing 100% terkirim', [
                'pic'       => $email,
                'to'        => $to,
                'shipments' => array_column($shipments, 'idship'),
            ]);
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run -- tidak ada email yang dikirim.');
        }

        return 0;
    }

    private function cacheKey($exportpk): string
    {
        return 'shipplan:stuffing-notif:' . $exportpk;
    }
}
