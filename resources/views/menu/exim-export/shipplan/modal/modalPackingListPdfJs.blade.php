{{-- Script modal packing list PDF per PO (markup: modalPackingListPdf). Di section js_custom karena
     jQuery & Bootstrap dimuat sesudah konten. --}}
<script>
    (function() {
        const PL = { ctx: null, docs: [], mode: 'actual', req: null };
        const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
        const num = n => Number(n || 0).toLocaleString('id-ID');
        const plDoc = () => PL.docs[Number($('#plDoc').val() || 0)] || null;
        const modal = () => bootstrap.Modal.getOrCreateInstance(document.getElementById('modalPackingListPdf'));

        // el = ikon PDF di baris PO (data-po, data-op, data-poref, data-exportpk, data-idship)
        window.openPackingListModal = function(el) {
            PL.ctx = {
                po: el.dataset.po || '', op: el.dataset.op || '', poref: el.dataset.poref || '',
                exportpk: String(el.dataset.exportpk || ''), idship: el.dataset.idship || ''
            };
            PL.docs = [];
            $('#plSub').text('PO ' + PL.ctx.po + ' · OP ' + PL.ctx.op + (PL.ctx.idship ? ' · ' + PL.ctx.idship : ''));
            $('#plLoading').removeClass('d-none');
            $('#plContent, #plEmpty').addClass('d-none');
            $('#plErr').addClass('d-none').text('');
            $('#plSubmit').prop('disabled', true);
            modal().show();

            if (PL.req) PL.req.abort();
            PL.req = $.get("{{ route('export.packing-list-options') }}", { po: PL.ctx.po, op: PL.ctx.op, poref: PL.ctx.poref })
                .done(function(res) {
                    PL.docs = (res && res.documents) || [];
                    $('#plLoading').addClass('d-none');
                    if (!PL.docs.length) {
                        $('#plEmpty').removeClass('d-none').text((res && res.error) || 'Belum ada packing list untuk PO ini di finishing-packing.');
                        return;
                    }
                    // Label: PO · OP · POref (tanpa unit); PO/OP/POref sama di 2 unit -> nomor urut
                    const lbl = PL.docs.map(d => 'PO ' + d.po + ' · OP ' + d.op + ' · ' + (d.poref ? 'POref ' + d.poref : 'tanpa POref'));
                    const seen = {};
                    $('#plDoc').html(PL.docs.map(function(d, k) {
                        const dup = lbl.filter(x => x === lbl[k]).length > 1;
                        seen[lbl[k]] = (seen[lbl[k]] || 0) + 1;
                        return '<option value="' + k + '">' + esc(lbl[k] + (dup ? ' (' + seen[lbl[k]] + ')' : '')) + '</option>';
                    }).join(''));
                    $('#plDocWrap').toggleClass('d-none', PL.docs.length < 2);
                    $('#plContent').removeClass('d-none');
                    plMode(PL.mode);
                })
                .fail(function(xhr, st) {
                    if (st === 'abort') return;
                    $('#plLoading').addClass('d-none');
                    $('#plEmpty').removeClass('d-none').text('Gagal memuat daftar packing list.');
                });
        };

        window.plMode = function(mode) {
            PL.mode = mode;
            $('#modalPackingListPdf .pl-mode button').removeClass('on').filter('[data-mode="' + mode + '"]').addClass('on');
            plRender();
        };

        window.plRender = function() {
            const d = plDoc();
            $('#plErr').addClass('d-none');
            if (!d) return;
            const custom = PL.mode === 'custom';
            $('#plActual').toggleClass('d-none', custom);
            $('#plCustom').toggleClass('d-none', !custom);
            $('#modalPackingListPdf .pl-note').addClass('d-none').filter('[data-note="' + (custom ? 'custom' : 'actual') + '"]').removeClass('d-none');
            $('#plSubmit').prop('disabled', false);
            $('#plSubmitText').text(custom ? 'Buka PDF Custom QTY' : 'Buka PDF Actual');

            // Actual: session — yang milik ship plan ini dicentang lebih dulu (kalau ada); warna semua dicentang
            $('#plExpanded').toggleClass('d-none', !d.isExpanded);
            const sg = d.shipgroups || [];
            const mine = sg.filter(o => String(o.value).split('|')[0] === PL.ctx.exportpk);
            $('#plSessList').html(sg.map(function(o) {
                const on = !mine.length || mine.indexOf(o) >= 0;
                return '<label class="list-group-item d-flex align-items-center gap-2 py-1">' +
                    '<input type="checkbox" class="form-check-input pl-sess-cb" value="' + esc(o.value) + '"' + (on ? ' checked' : '') + '>' +
                    '<div style="font-size:12.5px;">' + o.label +   // label dari finishing-packing (berisi &middot;)
                    ' <span class="text-muted" style="font-size:11px;">&middot; ' + num(o.ctn) + ' ctn &middot; ' + num(o.pcs) + ' pcs</span></div></label>';
            }).join(''));
            $('#plSessWrap').toggleClass('d-none', sg.length < 2);
            $('#plSessHint').text(mine.length && sg.length > 1
                ? 'Session ship plan ini (' + (PL.ctx.idship || 'SP') + ') sudah dicentang. Centang session lain kalau perlu.'
                : 'Pilih pengiriman yang mau dicetak packing list-nya.');
            $('#plColorList').html((d.combos || []).map(function(c) {
                return '<label class="list-group-item d-flex align-items-center gap-2">' +
                    '<input type="checkbox" class="form-check-input pl-color-cb" value="' + esc(c.key) + '" checked>' +
                    '<div><div class="fw-semibold" style="font-size:13px;">' + esc(c.material) + (c.secsz ? ' &middot; ' + esc(c.secsz) : '') + '</div>' +
                    '<div class="text-muted" style="font-size:11px;">' + esc(c.customer) + '</div></div></label>';
            }).join('') || '<div class="text-muted py-2" style="font-size:12.5px;">Tidak ada data warna untuk dokumen ini.</div>');

            // Custom QTY: data terbaru (semua warna) + semua versi tersimpan
            const label = function(key) {
                const c = (d.combos || []).find(x => x.key === key);
                const p = String(key).split('||');
                return c ? String(c.material).trim() + (String(c.secsz || '').trim() ? ' · ' + String(c.secsz).trim() : '')
                    : (p[1] || key) + (p[2] ? ' · ' + p[2] : '');
            };
            const sess = v => sg.filter(o => v.indexOf(o.value) >= 0).map(o => $('<div>').html(o.label).text()).join(', ');
            const vers = d.versions || [];
            $('#plVerList').html(
                '<label class="list-group-item d-flex align-items-start gap-2">' +
                '<input type="radio" class="form-check-input mt-1" name="plVer" value="" checked>' +
                '<div><div class="fw-semibold" style="font-size:13px;">Tanpa versi — data terbaru</div>' +
                '<div class="pl-ver-meta">Semua warna &middot; qty dari packing plan</div></div></label>' +
                vers.map(function(v) {
                    const warna = (v.combos || []).length ? v.combos.map(label).join(', ') : 'Semua warna';
                    const s = (v.shipgroups || []).length ? sess(v.shipgroups) : '';
                    return '<label class="list-group-item d-flex align-items-start gap-2">' +
                        '<input type="radio" class="form-check-input mt-1" name="plVer" value="' + esc(v.id) + '">' +
                        '<div><div class="fw-semibold" style="font-size:13px;">' + esc(v.name) + '</div>' +
                        '<div style="font-size:12px;">' + esc(warna) + (s ? ' &middot; ' + esc(s) : '') + '</div>' +
                        '<div class="pl-ver-meta">' + esc(v.created_at || '') + ' &middot; ' + esc(v.user || '-') +
                        (v.carton_count != null ? ' &middot; ' + num(v.carton_count) + ' ctn' : '') + '</div></div></label>';
                }).join('') +
                (vers.length ? '' : '<div class="text-muted px-1 pt-2" style="font-size:12px;">Belum ada versi Custom QTY yang disimpan untuk PO ini.</div>'));
        };

        window.plAllColors = function(on) {
            $('.pl-color-cb').prop('checked', on);
        };

        const plErr = msg => $('#plErr').removeClass('d-none').text(msg);

        window.plSubmit = function() {
            const d = plDoc();
            if (!d) return;
            const q = { po: d.po, op: d.op, poref: d.poref, mif: d.mif };

            if (PL.mode === 'custom') {
                q.exim = 1;
                const ver = $('input[name="plVer"]:checked').val();
                if (ver) q.version = ver;
            } else {
                // sama dengan finishing-packing: dikirim hanya bila tidak semua dicentang
                const all = $('.pl-color-cb'), on = all.filter(':checked');
                if (!on.length) return plErr('Pilih minimal 1 warna, atau klik "Pilih Semua".');
                if (on.length < all.length) q.combos = on.map(function() { return this.value; }).get();
                const sAll = $('.pl-sess-cb'), sOn = sAll.filter(':checked');
                if (sAll.length > 1 && !sOn.length) return plErr('Pilih minimal 1 Session.');
                if (sAll.length > 1 && sOn.length < sAll.length) q.shipgroups = sOn.map(function() { return this.value; }).get();
            }

            // jendela dibuka langsung saat klik (tidak diblok popup blocker), alamat diisi setelah link jadi
            const w = window.open('', '_blank');
            if (w) w.document.write('<p style="font-family:sans-serif;color:#6b7280;">Menyiapkan packing list...</p>');
            $('#plSubmit').prop('disabled', true);
            $.get("{{ route('export.packing-list-link') }}", q)
                .done(function(res) {
                    if (res && res.url) {
                        if (w) w.location.href = res.url; else window.open(res.url, '_blank');
                        modal().hide();
                    } else {
                        if (w) w.close();
                        plErr((res && res.error) || 'Link PDF tidak dapat dibuat.');
                    }
                })
                .fail(function() {
                    if (w) w.close();
                    plErr('Link PDF tidak dapat dibuat.');
                })
                .always(function() { $('#plSubmit').prop('disabled', false); });
        };
    })();
</script>
