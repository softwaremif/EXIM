/*
 * Security Gate: layar penuh "menetap" (mode kiosk) di tablet security.
 * Browser keluar dari layar penuh setiap pindah halaman dan hanya boleh masuk lagi lewat sentuhan pengguna.
 * Setelah petugas menyalakan layar penuh (tombol di header), pilihan disimpan per perangkat
 * (localStorage 'sg-fullscreen' = 'on'); di halaman berikutnya sentuhan pertama di mana saja
 * langsung masuk layar penuh lagi. Mode mati hanya lewat tombol keluar layar penuh (SgFullscreen.off()).
 * Dibuka dari ikon aplikasi terinstal (display-mode fullscreen / standalone) tidak perlu apa-apa.
 */
(function () {
    var KEY = 'sg-fullscreen';
    var root = document.documentElement;
    var request = root.requestFullscreen || root.webkitRequestFullscreen;
    var exit = document.exitFullscreen || document.webkitExitFullscreen;
    var current = function () { return document.fullscreenElement || document.webkitFullscreenElement; };
    var installed = window.matchMedia('(display-mode: fullscreen), (display-mode: standalone)').matches || navigator.standalone === true;
    var supported = !!request && (document.fullscreenEnabled || document.webkitFullscreenEnabled);

    var store = {
        get: function () { try { return localStorage.getItem(KEY) === 'on'; } catch (e) { return false; } },
        set: function (on) { try { on ? localStorage.setItem(KEY, 'on') : localStorage.removeItem(KEY); } catch (e) {} }
    };

    function enter() {
        if (current() || !supported) return Promise.resolve();
        try {
            var p = request.call(root, { navigationUI: 'hide' });
            return p && p.catch ? p.catch(function () {}) : Promise.resolve();
        } catch (e) { return Promise.resolve(); }
    }

    // sentuhan / klik / tombol keyboard = aktivasi pengguna yang diizinkan browser untuk masuk layar penuh
    function onGesture() {
        if (store.get() && !current()) enter();
    }

    window.SgFullscreen = {
        supported: supported && !installed,
        active: function () { return !!current(); },
        enabled: store.get,
        on: function () { store.set(true); return enter(); },
        off: function () {
            store.set(false);
            if (current() && exit) { try { var p = exit.call(document); if (p && p.catch) p.catch(function () {}); } catch (e) {} }
        }
    };

    if (!supported || installed) return;
    ['click', 'touchend', 'keydown'].forEach(function (type) {
        document.addEventListener(type, onGesture, true);
    });
})();
