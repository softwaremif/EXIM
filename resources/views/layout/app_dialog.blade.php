{{-- Dialog global (Bootstrap 5 modal) pengganti alert() / confirm() / $.messager.alert.
     Pakai (keduanya mengembalikan Promise):
        appAlert('Data tersimpan.', { type: 'success' });
        appAlert('Silakan pilih data terlebih dahulu.', { type: 'warning', title: 'Belum ada data' });
        if (await appConfirm('Hapus data ini?', { type: 'danger', okText: 'Ya, hapus' })) { ... }
     type: info | success | warning | error | danger | question. Pesan ditampilkan sebagai teks (baris baru = \n).
     Modal dibuat sekali saat pertama dipakai; bisa tampil di atas modal lain yang sedang terbuka. --}}
<style>
    .app-dialog .modal-dialog { max-width: 400px; }
    .app-dialog .modal-content { border: 0; border-radius: 18px; box-shadow: 0 24px 64px rgba(16, 24, 40, .22); overflow: hidden; }
    .app-dialog__body { padding: 28px 24px 8px; text-align: center; }
    .app-dialog__icon { width: 56px; height: 56px; margin: 0 auto 16px; border-radius: 50%; display: grid; place-items: center; font-size: 26px; }
    .app-dialog__icon i { line-height: 1; }
    .app-dialog__title { margin: 0 0 6px; font-size: 18px; font-weight: 600; color: #101828; }
    .app-dialog__msg { margin: 0; font-size: 14px; line-height: 1.55; color: #475467; white-space: pre-line; overflow-wrap: anywhere; }
    .app-dialog__foot { display: flex; gap: 10px; padding: 20px 24px 24px; }
    .app-dialog__foot .btn { flex: 1; min-height: 44px; border-radius: 10px; font-weight: 600; font-size: 14px; }
    .app-dialog__cancel { background: #fff; border: 1px solid #d0d5dd; color: #344054; }
    .app-dialog__cancel:hover { background: #f9fafb; color: #101828; border-color: #d0d5dd; }
    .app-dialog.is-info .app-dialog__icon     { background: #eff4ff; color: #2e5bff; box-shadow: 0 0 0 8px #f5f8ff; }
    .app-dialog.is-success .app-dialog__icon  { background: #ecfdf3; color: #079455; box-shadow: 0 0 0 8px #f6fef9; }
    .app-dialog.is-warning .app-dialog__icon  { background: #fffaeb; color: #dc6803; box-shadow: 0 0 0 8px #fffcf5; }
    .app-dialog.is-error .app-dialog__icon,
    .app-dialog.is-danger .app-dialog__icon   { background: #fef3f2; color: #d92d20; box-shadow: 0 0 0 8px #fffbfa; }
    .app-dialog.is-question .app-dialog__icon { background: #f4f3ff; color: #6938ef; box-shadow: 0 0 0 8px #fafaff; }
    .app-dialog .modal-dialog { transform: scale(.94); transition: transform .18s ease-out; }
    .app-dialog.show .modal-dialog { transform: none; }
    @media (prefers-reduced-motion: reduce) { .app-dialog .modal-dialog { transition: none; } }
</style>
<script>
    (function () {
        const TYPES = {
            info:     { icon: 'bi-info-lg',            title: 'Informasi',  btn: 'btn-primary' },
            success:  { icon: 'bi-check-lg',           title: 'Berhasil',   btn: 'btn-success' },
            warning:  { icon: 'bi-exclamation-lg',     title: 'Perhatian',  btn: 'btn-warning' },
            error:    { icon: 'bi-x-lg',               title: 'Gagal',      btn: 'btn-danger' },
            danger:   { icon: 'bi-trash3',             title: 'Konfirmasi', btn: 'btn-danger' },
            question: { icon: 'bi-question-lg',        title: 'Konfirmasi', btn: 'btn-primary' },
        };
        let el = null, modal = null, resolver = null, result = false;
        const queue = [];

        function build() {
            el = document.createElement('div');
            el.className = 'modal fade app-dialog';
            el.tabIndex = -1;
            el.setAttribute('role', 'alertdialog');
            el.setAttribute('aria-modal', 'true');
            el.setAttribute('aria-labelledby', 'appDialogTitle');
            el.setAttribute('aria-describedby', 'appDialogMsg');
            el.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="app-dialog__body">
                            <div class="app-dialog__icon"><i class="bi"></i></div>
                            <h5 class="app-dialog__title" id="appDialogTitle"></h5>
                            <p class="app-dialog__msg" id="appDialogMsg"></p>
                        </div>
                        <div class="app-dialog__foot">
                            <button type="button" class="btn app-dialog__cancel" data-result="0"></button>
                            <button type="button" class="btn app-dialog__ok" data-result="1"></button>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(el);
            modal = new bootstrap.Modal(el, { backdrop: 'static', keyboard: true });
            el.addEventListener('click', e => {
                const btn = e.target.closest('[data-result]');
                if (!btn) return;
                result = btn.dataset.result === '1';
                modal.hide();
            });
            // tampil di atas modal lain yang sedang terbuka (mis. modal container)
            el.addEventListener('show.bs.modal', () => {
                const open = document.querySelectorAll('.modal.show').length;
                el.style.zIndex = 1060 + open * 10;
                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    const last = backdrops[backdrops.length - 1];
                    if (last) last.style.zIndex = 1055 + open * 10;
                });
            });
            el.addEventListener('shown.bs.modal', () => el.querySelector('.app-dialog__ok').focus());
            el.addEventListener('hidden.bs.modal', () => {
                // modal lain masih terbuka: body tetap terkunci scroll
                if (document.querySelector('.modal.show')) document.body.classList.add('modal-open');
                const done = resolver;
                resolver = null;
                if (done) done(result);
                if (queue.length) open(queue.shift());
            });
        }

        function open(job) {
            if (!el) build();
            if (resolver) { queue.push(job); return; }   // satu dialog dalam satu waktu, sisanya antre
            const t = TYPES[job.type] || TYPES.info;
            el.className = 'modal fade app-dialog is-' + (TYPES[job.type] ? job.type : 'info');
            el.querySelector('.app-dialog__icon i').className = 'bi ' + t.icon;
            el.querySelector('.app-dialog__title').textContent = job.title || t.title;
            el.querySelector('.app-dialog__msg').textContent = job.message == null ? '' : String(job.message);
            const ok = el.querySelector('.app-dialog__ok');
            ok.className = 'btn app-dialog__ok ' + t.btn;
            ok.textContent = job.okText;
            const cancel = el.querySelector('.app-dialog__cancel');
            cancel.hidden = !job.cancelText;
            cancel.textContent = job.cancelText || '';
            result = false;   // tutup lewat Esc = batal
            resolver = job.resolve;
            modal.show();
        }

        window.appDialog = opts => new Promise(resolve => open(Object.assign({ okText: 'OK', cancelText: null }, opts, { resolve })));
        window.appAlert = (message, opts) => window.appDialog(Object.assign({ type: 'info' }, opts, { message }));
        window.appConfirm = (message, opts) => window.appDialog(Object.assign({ type: 'question', okText: 'Ya', cancelText: 'Batal' }, opts, { message }));
    })();
</script>
