<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

/**
 * Aplikasi layar utama Security Gate: tablet/HP security bisa "Instal aplikasi" lalu membukanya
 * layar penuh tanpa bar browser. Cakupan aplikasi hanya security/gate*, jadi login & logout security
 * juga di bawah security/gate (halaman di luar cakupan membuat Chrome menampilkan bar lagi).
 * Semua method di sini tanpa middleware login: browser mengambil manifest tanpa cookie sesi.
 */
class GateAppController extends Controller
{
    // halaman login yang sama dengan login EXIM, tetapi di dalam cakupan aplikasi
    public function login()
    {
        Session::flush();
        Session::regenerate();

        return view('pages.login');
    }

    public function logout()
    {
        Session::flush();
        Session::regenerate();

        return redirect()->route('security.gate.login');
    }

    /**
     * Service worker aplikasi (cakupan security/gate). Syarat Chrome agar aplikasi bisa diinstal penuh
     * (bersama HTTPS & manifest). Data selalu dari jaringan (tidak di-cache), kecuali halaman "offline"
     * yang ditampilkan kalau sinyal putus saat membuka halaman.
     */
    public function serviceWorker()
    {
        $offline = json_encode(view('menu.exim-export.security.offline')->render());
        $js = <<<JS
const OFFLINE = {$offline};
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', e => e.waitUntil(self.clients.claim()));
// hanya navigasi halaman yang ditangani; foto, upload & API tetap langsung ke jaringan
self.addEventListener('fetch', e => {
    if (e.request.mode !== 'navigate') return;
    e.respondWith(fetch(e.request).catch(() => new Response(OFFLINE, { headers: { 'Content-Type': 'text/html; charset=utf-8' } })));
});
JS;
        return response($js, 200, [
            'Content-Type'           => 'application/javascript; charset=utf-8',
            'Cache-Control'          => 'no-cache',
            // cakupan = security/gate (sama dengan manifest), termasuk halaman utama tanpa garis miring
            'Service-Worker-Allowed' => parse_url(route('security.gate.index'), PHP_URL_PATH),
        ]);
    }

    public function manifest()
    {
        $icon = fn($file, $size, $purpose = 'any') => [
            'src' => asset('public/img/security-gate/' . $file), 'sizes' => "{$size}x{$size}", 'type' => 'image/png', 'purpose' => $purpose,
        ];

        return response()->json([
            'id'               => route('security.gate.index'),
            'name'             => 'Security Gate · Morich Indo Fashion',
            'short_name'       => 'Security Gate',
            'description'      => 'Gate in, muat & seal, dan gate out container.',
            'lang'             => 'id',
            'start_url'        => route('security.gate.index'),
            // hanya bagian Security Gate (termasuk login/logout security); halaman EXIM lain tetap dibuka di Chrome biasa
            'scope'            => route('security.gate.index'),
            'display'          => 'fullscreen',
            'display_override' => ['fullscreen', 'standalone'],
            'orientation'      => 'any',
            'background_color' => '#f4f3ef',
            'theme_color'      => '#ffffff',
            'icons'            => [
                $icon('icon-192.png', 192),
                $icon('icon-512.png', 512),
                $icon('icon-maskable-512.png', 512, 'maskable'),
            ],
        ], 200, ['Content-Type' => 'application/manifest+json'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
