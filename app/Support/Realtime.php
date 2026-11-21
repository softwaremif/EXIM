<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Realtime halaman Shipment, scanner HP & Security Gate (EXIM) lewat websocket:
 * server laravel-websockets (protokol Pusher) yang sama dengan SPS, lihat REALTIME_* di .env.
 *
 * Server hanya mengirim "colekan" kecil ke 1 channel bersama (REALTIME_CHANNEL, harus SAMA dengan
 * pasangan EXIM-nya): event "pack" (data carton berubah, dari packinglist) & "cont" (container berubah,
 * dari EXIM), isi {exportpk, contpk, by}. Halaman lalu memuat ulang datanya sendiri (pulse / daftar);
 * polling tetap jalan pelan sebagai cadangan kalau websocket putus. REALTIME_CHANNEL kosong = nonaktif.
 */
class Realtime
{
    private static array $sent = [];

    private static function cfg(): array
    {
        return (array) config('services.realtime', []);
    }

    public static function enabled(): bool
    {
        $c = self::cfg();
        return !empty($c['channel']) && !empty($c['app_id']) && !empty($c['key']) && !empty($c['secret']) && !empty($c['host']);
    }

    /** Penanda perangkat (hash session) -- halaman mengabaikan colekan dari aksinya sendiri bila perlu. */
    public static function by(): ?string
    {
        try {
            return session()->isStarted() ? substr(sha1(session()->getId()), 0, 12) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Konfigurasi untuk browser (tanpa secret); null = realtime nonaktif. */
    public static function client(): ?array
    {
        if (!self::enabled()) {
            return null;
        }
        $c = self::cfg();
        return [
            'key'     => $c['key'],
            'host'    => $c['host'],
            'port'    => (int) $c['port'],
            'tls'     => $c['scheme'] === 'https',
            'channel' => $c['channel'],
            'me'      => self::by(),
        ];
    }

    /** Kirim colekan (sekali per event+isi per request); gagal / server mati diabaikan. */
    public static function push(string $event, array $data = []): void
    {
        if (!self::enabled()) {
            return;
        }
        $dedupe = $event . json_encode($data);
        if (isset(self::$sent[$dedupe])) {
            return;
        }
        self::$sent[$dedupe] = true;

        $c    = self::cfg();
        $path = '/apps/' . $c['app_id'] . '/events';
        $body = json_encode(['name' => $event, 'channels' => [$c['channel']], 'data' => json_encode($data)]);
        $q    = ['auth_key' => $c['key'], 'auth_timestamp' => time(), 'auth_version' => '1.0', 'body_md5' => md5($body)];
        $q['auth_signature'] = hash_hmac('sha256', "POST\n{$path}\n" . http_build_query($q), $c['secret']);
        // server websocket sempat gagal dihubungi: lewati 30 dtk supaya aksi tidak ikut lambat
        try {
            if (Cache::get('realtime.down')) {
                return;
            }
        } catch (\Throwable $e) {
        }
        try {
            Http::withOptions(['connect_timeout' => 0.5])->timeout(1.5)->withBody($body, 'application/json')
                ->post($c['scheme'] . '://' . $c['host'] . ':' . $c['port'] . $path . '?' . http_build_query($q))
                ->throw();
        } catch (\Throwable $e) {
            // websocket mati: halaman tetap sinkron lewat polling cadangan
            try {
                Cache::put('realtime.down', 1, 30);
            } catch (\Throwable $e) {
            }
        }
    }
}
