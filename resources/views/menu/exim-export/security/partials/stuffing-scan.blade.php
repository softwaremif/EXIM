{{-- Scan stuffing: QR container dari sistem stuffing (Packing List > Shipment > QR cek security)
     -> langsung buka halaman container + ceklis carton. Sumber scan: kamera live (HTTPS), foto QR (HTTP),
     scanner barcode HID (Bluetooth/USB, mode keyboard) di mana saja, atau ketik no container.
     Barcode yang bukan QR container diteruskan ke ceklis carton (window.sgCartonScan) kalau ada. --}}
<style>
    .stf-row { display: flex; gap: var(--space-2); align-items: center; }
    .stf-row form { flex: 1; min-width: 0; }
    .stf-row .sg-btn { flex: none; }
    .stf-hint { margin: 0; font-size: 13px; line-height: 19px; color: var(--ink-muted); }
</style>

<div class="sg-scan" id="stfScan" role="dialog" aria-modal="true" aria-labelledby="stfTitle" hidden
    data-go="{{ route('security.gate.index') }}" data-lib="{{ asset('public/js/vendor/jsQR.min.js') }}">
    <div class="sg-scan__panel">
        <div class="sg-scan__head">
            <h2 class="sg-scan__title" id="stfTitle">Scan stuffing<small>QR container dari sistem stuffing</small></h2>
            <button type="button" class="sg-btn sg-btn--ghost sg-btn--icon" data-stf-close aria-label="Tutup scanner">@include('menu.exim-export.security.partials.icon', ['name' => 'close'])</button>
        </div>
        <div class="sg-scan__view" data-stf-live hidden>
            <video playsinline muted></video>
            <span class="sg-scan__frame"></span>
        </div>
        <label class="sg-btn sg-btn--primary sg-btn--lg sg-btn--block" data-stf-photo hidden>
            @include('menu.exim-export.security.partials.icon', ['name' => 'camera']) Foto QR container
            <input class="sg-scan__file" type="file" accept="image/*" capture="environment">
        </label>
        <p class="stf-hint">Scanner barcode Bluetooth/USB bisa langsung dipakai tanpa membuka jendela ini.</p>
        <p class="sg-scan__status" data-stf-status role="status"></p>
        <form class="sg-scan__manual" data-stf-manual novalidate>
            <label class="sg-label" for="stfCode">QR rusak? Ketik no container</label>
            <div class="sg-scan__row">
                <input class="sg-input sg-mono" id="stfCode" autocomplete="off" autocapitalize="characters" placeholder="TCLU 1234567">
                <button type="submit" class="sg-btn">Cari</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const dlg = document.getElementById('stfScan');
    const video = dlg.querySelector('video');
    const liveBox = dlg.querySelector('[data-stf-live]');
    const photoBtn = dlg.querySelector('[data-stf-photo]');
    const photoInput = photoBtn.querySelector('input');
    const statusEl = dlg.querySelector('[data-stf-status]');
    const manualForm = dlg.querySelector('[data-stf-manual]');
    const manualInput = manualForm.querySelector('input');
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const canLive = window.isSecureContext && !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
    let detector = null;
    try { if ('BarcodeDetector' in window) detector = new window.BarcodeDetector({ formats: ['qr_code'] }); } catch (e) {}
    let stream = null, timer = null, libLoading = null;

    // QR stuffing berisi link ".../security/gate?cont=123&ck=1"; terima juga kode "STF-123".
    function parseCont(raw) {
        const t = String(raw || '').trim();
        let m = t.match(/[?&]cont=(\d+)/i);
        if (m) return parseInt(m[1], 10);
        m = t.match(/^(?:STF|CONT)[-:](\d+)$/i);
        return m ? parseInt(m[1], 10) : null;
    }
    function goCont(contpk) {
        setStatus('Membuka container...', '');
        window.location.href = dlg.dataset.go + '?cont=' + contpk + '&ck=1';
    }
    function setStatus(msg, kind) {
        statusEl.textContent = msg;
        statusEl.className = 'sg-scan__status' + (kind ? ' is-' + kind : '');
    }

    // Satu pintu semua hasil scan: QR container -> pindah halaman; selain itu -> ceklis carton.
    window.sgHandleScan = function (code) {
        code = String(code || '').trim();
        if (!code) return;
        const contpk = parseCont(code);
        if (contpk) {
            const here = new URLSearchParams(location.search).get('cont');
            if (String(contpk) === here && window.sgCartonScan) return window.sgCartonScan(null); // container ini: buka ceklis
            return goCont(contpk);
        }
        if (window.sgCartonScan) return window.sgCartonScan(code);
        open();
        setStatus(`"${code}" bukan QR container stuffing. Scan QR container dulu.`, 'error');
    };

    // ---- scanner HID: ketikan cepat + Enter saat tidak sedang mengetik di input ----
    let buf = '', last = 0;
    document.addEventListener('keydown', function (e) {
        const a = document.activeElement;
        if (a && (a.matches('input, textarea, select') || a.isContentEditable)) return;
        if (document.querySelector('.sg-scan:not([hidden]):not(#stfScan), .ck-sheet:not([hidden])')) return;
        const now = Date.now();
        if (now - last > 100) buf = '';
        last = now;
        if (e.key === 'Enter') {
            if (buf.length >= 4) { e.preventDefault(); const code = buf; buf = ''; window.sgHandleScan(code); }
            buf = '';
            return;
        }
        if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) buf += e.key;
    });

    // ---- kamera / foto QR ----
    const loadLib = () => window.jsQR ? Promise.resolve() : (libLoading = libLoading || new Promise((resolve, reject) => {
        const tag = document.createElement('script');
        tag.src = dlg.dataset.lib;
        tag.onload = resolve;
        tag.onerror = () => { libLoading = null; tag.remove(); reject(new Error('Pembaca QR gagal dimuat. Periksa sinyal lalu coba lagi.')); };
        document.head.appendChild(tag);
    }));
    async function readSource(src, w, h) {
        if (detector) {
            try { const f = await detector.detect(src); if (f.length && f[0].rawValue) return f[0].rawValue.trim(); } catch (e) {}
        }
        await loadLib();
        const scale = Math.min(1, 1000 / Math.max(w, h));
        canvas.width = Math.round(w * scale);
        canvas.height = Math.round(h * scale);
        ctx.drawImage(src, 0, 0, canvas.width, canvas.height);
        const img = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = window.jsQR(img.data, canvas.width, canvas.height, { inversionAttempts: 'attemptBoth' });
        return code && code.data ? code.data.trim() : null;
    }
    function found(code) {
        const contpk = parseCont(code);
        if (!contpk) return setStatus('QR ini bukan QR container stuffing.', 'error'), false;
        stopLive();
        goCont(contpk);
        return true;
    }
    async function startLive() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
            video.srcObject = stream;
            await video.play();
            liveBox.hidden = false;
            setStatus('Arahkan kamera ke QR container.', '');
            const tick = async () => {
                if (!stream) return;
                if (video.videoWidth) {
                    const code = await readSource(video, video.videoWidth, video.videoHeight).catch(() => null);
                    if (code && found(code)) return;
                }
                timer = setTimeout(tick, 250);
            };
            tick();
        } catch (e) {
            liveBox.hidden = true;
            photoBtn.hidden = false;
            setStatus('Kamera tidak bisa dibuka. Pakai foto QR.', 'warn');
        }
    }
    function stopLive() {
        clearTimeout(timer);
        if (stream) stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
    photoInput.addEventListener('change', async function () {
        const file = photoInput.files[0];
        photoInput.value = '';
        if (!file) return;
        setStatus('Membaca QR...', '');
        try {
            const bmp = window.createImageBitmap ? await createImageBitmap(file) : null;
            if (!bmp) throw new Error('Browser tidak mendukung baca foto.');
            const code = await readSource(bmp, bmp.width, bmp.height);
            if (bmp.close) bmp.close();
            if (!code) return setStatus('QR tidak terbaca. Foto lebih dekat & terang.', 'error');
            found(code);
        } catch (e) {
            setStatus(e.message || 'Foto tidak bisa dibaca.', 'error');
        }
    });
    manualForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const v = manualInput.value.trim();
        if (!v) return;
        const contpk = parseCont(v);
        if (contpk) return goCont(contpk);
        // no container -> cari di daftar
        window.location.href = dlg.dataset.go + '?filter=arrived&q=' + encodeURIComponent(v);
    });

    function open() {
        if (!dlg.hidden) return;
        dlg.hidden = false;
        document.body.classList.add('sg-noscroll');
        setStatus('', '');
        manualInput.value = '';
        if (canLive) { photoBtn.hidden = true; startLive(); } else { liveBox.hidden = true; photoBtn.hidden = false; }
        (window.requestIdleCallback || (fn => setTimeout(fn, 300)))(() => loadLib().catch(() => {}));
    }
    function close() {
        stopLive();
        dlg.hidden = true;
        if (!document.querySelector('.ck-modal:not([hidden])')) document.body.classList.remove('sg-noscroll');
    }
    dlg.querySelector('[data-stf-close]').addEventListener('click', close);
    dlg.addEventListener('click', e => { if (e.target === dlg) close(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && !dlg.hidden) close(); });
    document.querySelectorAll('[data-stf-open]').forEach(b => b.addEventListener('click', open));
})();
</script>
