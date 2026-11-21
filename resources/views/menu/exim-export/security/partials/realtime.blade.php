{{--
    Realtime websocket (App\Support\Realtime): window.ShipRT
      ShipRT.on((event, data) => ...)   event "pack" (carton berubah) / "cont" (container EXIM berubah),
                                        data {exportpk, contpk, by, self} -- self = dari sesi ini sendiri
      ShipRT.onState(connected => ...)  websocket tersambung / putus
      ShipRT.connected                  true = colekan realtime aktif (polling boleh diperlambat)
    Tanpa konfigurasi / server mati: connected tetap false, halaman memakai polling biasa.
--}}
@php($rtCfg = \App\Support\Realtime::client())
@if ($rtCfg)
    <script src="{{ asset('public/js/vendor/pusher-8.4.0.min.js') }}"></script>
@endif
<script>
    window.ShipRT = (function(cfg) {
        const subs = [], states = [];
        const rt = {
            connected: false,
            on(fn) { subs.push(fn); },
            onState(fn) { states.push(fn); },
        };
        if (!cfg || !window.Pusher) return rt;
        const setState = function(on) {
            if (rt.connected === on) return;
            rt.connected = on;
            states.forEach(function(f) { try { f(on); } catch (e) {} });
        };
        try {
            const p = new Pusher(cfg.key, {
                wsHost: cfg.host, wsPort: cfg.port, wssPort: cfg.port, forceTLS: cfg.tls,
                enabledTransports: ['ws', 'wss'], disableStats: true, cluster: 'mt1',
            });
            const ch = p.subscribe(cfg.channel);
            ch.bind('pusher:subscription_succeeded', function() { setState(true); });
            p.connection.bind('state_change', function(s) {
                if (s.current !== 'connected') setState(false);
                else if (ch.subscribed) setState(true);
            });
            ['pack', 'cont'].forEach(function(ev) {
                ch.bind(ev, function(d) {
                    d = d || {};
                    d.self = !!(d.by && d.by === cfg.me);
                    subs.forEach(function(f) { try { f(ev, d); } catch (e) {} });
                });
            });
        } catch (e) {}
        return rt;
    })(@json($rtCfg));
</script>
