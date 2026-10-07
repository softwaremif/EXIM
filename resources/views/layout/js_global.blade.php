<script type="text/javascript" src="{!! asset('public/datepicker/jquery.min.js') !!}"></script>
<script type="text/javascript" src="{!! asset('public/datepicker/moment.min.js') !!}"></script>
<script type="text/javascript" src="{!! asset('public/datepicker/daterangepicker.min.js') !!}"></script>
<script type="text/javascript" src="{!! asset('public/bootstrap-5.3.2-dist/js/bootstrap.bundle.min.js') !!}"></script>
<script type="text/javascript" src="{!! asset('public/media/jquery-easyui-1.9.15/jquery.easyui.min.js') !!}"></script>
<script type="text/javascript" src="{!! asset('public/media/jquery-easyui-1.9.15/jquery.edatagrid.js') !!}"></script>
<script type="text/javascript" src="{!! asset('public/css/ckeditor_fullpage/ckeditor.js') !!}"></script>

<script>
    function changePassword() {
        $('#passwordModal').modal('show');
    }

    function cancelChangePassword() {
        $('#formChangePassword').form('reset');
    }

    function showHiddenPassword(index) {
        const currentPassword = document.querySelector("#password-" + index);
        const iEyeCurrentPassword = document.querySelector("#i-eye-password-" + index);
        iEyeCurrentPassword.addEventListener("click", function() {
            const inputType = currentPassword.getAttribute("type") === "password" ? "text" : "password";
            currentPassword.setAttribute("type", inputType);
            this.classList.toggle("bi-eye");
        });
    }

    function changepswd() {
        // ... (biarkan isi fungsi ini seperti aslinya, tidak berubah)
    }

    function showAlert(index, message) {
        if (index == 200 || index == 201 || index == 202 || index == 204) {
            document.getElementById("success-message").textContent = message;
            $('#alert-success').toast('show');
            setTimeout(() => { $('#alert-success').toast('hide'); }, 3000);
        } else {
            document.getElementById("error-message").textContent = message;
            $('#alert-error').toast('show');
            setTimeout(() => { $('#alert-error').toast('hide'); }, 3000);
        }
    }

    // =========================
    // NOTIFICATION DROPDOWN
    // =========================
    function toggleNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }

    async function loadNotifications() {
        try {
            const res = await fetch(`{{ route('exim.import.notifications') }}`);
            const data = await res.json();

            const bellIcon = document.getElementById('notifBellIcon');
            bellIcon.src = data.total > 0
                ? `{{ asset('public/css/images/notif2.jpeg') }}`
                : `{{ asset('public/css/images/notif.jpeg') }}`;

            renderNotificationList(data.notifications);

            document.getElementById('notificationShowMore').style.display = data.show_more ? 'block' : 'none';
        } catch (err) {
            console.error(err);
        }
    }

    function renderNotificationList(rows) {
        const container = document.getElementById('notificationList');

        if (!rows.length) {
            container.innerHTML = `<div style="padding:12px 0; color:#999;">Tidak ada notifikasi.</div>`;
            return;
        }

        container.innerHTML = rows.map(row => `
            <div>
                <div style="font-size:14px;">${row.pur} made approved PO.</div>
                <div style="font-weight:700; font-size:14px;">
                    PO ${row.pono}, ${row.byrnm}
                </div>
                <div style="font-size:14px;">${row.item}</div>
                <div style="font-size:12px; color:rgba(0,0,0,.6);">${row.dateapv}</div>

                <div style="text-align:right; margin-top:-40px; padding:15px;">
                    <a href="javascript:void(0)" data-popk="${row.popk}" class="notif-view-link"
                        style="text-decoration:none; color:#359DD9; font-size:14px;">
                        View
                    </a>
                </div>

                <hr style="height:2px; background-color:#BDBDBD; color:#BDBDBD; border:none; margin-top:-4px;">
            </div>
        `).join('');

        container.querySelectorAll('.notif-view-link').forEach(link => {
            const popk = link.dataset.popk;
            link.href = `{{ route('exim.import.notifications.view', ':popk') }}`.replace(':popk', popk);
        });
    }

    // =========================
    // MODAL "SHOW MORE" - Approved PO From Purchasing
    // =========================
    let notifModalInstance = null;
    let notifCurrentPage = 1;
    let notifPageSize = 100;
    let notifLastPage = 1;
    let notifSortColumn = 'dateapv';
    let notifSortOrder = 'desc';
    let notifSearchKeyword = '';
    let revisedFilterTimer = null;

    function openNotifModal() {
        document.getElementById('notificationDropdown').style.display = 'none';

        const modalEl = document.getElementById('notifModal');
        if (!notifModalInstance) {
            notifModalInstance = new bootstrap.Modal(modalEl);
        }
        notifModalInstance.show();

        notifCurrentPage = 1;
        notifSearchKeyword = '';
        document.getElementById('notifModalSearch').value = '';

        loadRevisedPoList();
    }

    function filterRevisedPoDelay() {
        clearTimeout(revisedFilterTimer);
        revisedFilterTimer = setTimeout(() => {
            notifSearchKeyword = document.getElementById('notifModalSearch').value;
            notifCurrentPage = 1;
            loadRevisedPoList();
        }, 300);
    }

    function sortRevisedPo(column) {
        if (notifSortColumn === column) {
            notifSortOrder = notifSortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            notifSortColumn = column;
            notifSortOrder = 'asc';
        }
        notifCurrentPage = 1;
        loadRevisedPoList();
    }

    function onNotifPageSizeChange() {
        notifPageSize = parseInt(document.getElementById('notifModalPageSize').value, 10);
        notifCurrentPage = 1;
        loadRevisedPoList();
    }

    function goToNotifPage(page) {
        if (page < 1 || page > notifLastPage) return;
        notifCurrentPage = page;
        loadRevisedPoList();
    }

    function loadRevisedPoList() {
        const params = new URLSearchParams({
            carim: notifSearchKeyword,
            page: notifCurrentPage,
            rows: notifPageSize,
            sort: notifSortColumn,
            order: notifSortOrder,
        });

        fetch(`{{ route('exim.import.revisedPoList') }}?${params.toString()}`)
            .then(res => res.json())
            .then(res => {
                renderRevisedPoTable(res.rows);
                updateNotifPagination(res.total, res.page, res.rowsPerPage);
                updateSortIcons();
            })
            .catch(err => console.error(err));
    }

    function renderRevisedPoTable(rows) {
        const tbody = document.getElementById('notifModalBody');

        if (!rows.length) {
            tbody.innerHTML = `<tr><td colspan="6" style="color:#999;">Tidak ada data.</td></tr>`;
            return;
        }

        tbody.innerHTML = rows.map(row => `
            <tr>
                <td>${row.pur}</td>
                <td>${row.pono}</td>
                <td>${row.byrnm}</td>
                <td>${row.brg}</td>
                <td>${row.dateapv}</td>
                <td><a href="javascript:void(0)" data-popk="${row.popk}" class="revised-view-link" style="color:#359DD9; text-decoration:none;">View</a></td>
            </tr>
        `).join('');

        tbody.querySelectorAll('.revised-view-link').forEach(link => {
            const popk = link.dataset.popk;
            link.href = `{{ route('exim.import.notifications.view', ':popk') }}`.replace(':popk', popk);
        });
    }

    function updateNotifPagination(total, page, rowsPerPage) {
        notifCurrentPage = page;
        notifLastPage = Math.max(1, Math.ceil(total / rowsPerPage));

        const start = total === 0 ? 0 : (page - 1) * rowsPerPage + 1;
        const end = Math.min(page * rowsPerPage, total);

        document.getElementById('notifModalInfo').textContent =
            `Displaying ${start} to ${end} of ${total} items`;

        const pageNumbersEl = document.getElementById('notifPageNumbers');
        let html = '';

        const maxVisible = 10;
        let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
        let endPage = Math.min(notifLastPage, startPage + maxVisible - 1);
        startPage = Math.max(1, endPage - maxVisible + 1);

        for (let i = startPage; i <= endPage; i++) {
            html += `<button type="button" class="btn btn-sm ${i === page ? 'btn-dark' : 'btn-light'}" onclick="goToNotifPage(${i})">${i}</button>`;
        }

        pageNumbersEl.innerHTML = html;
    }

    function updateSortIcons() {
        ['pur', 'pono', 'byrnm', 'brg', 'dateapv'].forEach(col => {
            const el = document.getElementById(`sort-icon-${col}`);
            if (!el) return;
            el.textContent = col === notifSortColumn ? (notifSortOrder === 'asc' ? '▲' : '▼') : '';
        });
    }

    // =========================
    // INIT
    // =========================
    document.addEventListener('DOMContentLoaded', function() {
        // guard: hanya jalan kalau elemen notifikasi ada di halaman ini
        // (halaman login tidak punya elemen ini, jadi tidak akan fetch di sana)
        if (document.getElementById('notifBellIcon')) {
            loadNotifications();

            document.getElementById('notificationShowMore').addEventListener('click', openNotifModal);

            document.addEventListener('click', function(e) {
                const dropdown = document.getElementById('notificationDropdown');
                const bell = document.getElementById('notifBellIcon');
                if (dropdown && bell && !dropdown.contains(e.target) && e.target !== bell) {
                    dropdown.style.display = 'none';
                }
            });
        }
    });
</script>

<!-- ================= TOAST GLOBAL ================= -->
<div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 1080;">
    <div id="ldToast" class="toast ld-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="ld-toast-body">
            <span class="ld-toast-icon"><i class="bi bi-check-lg"></i></span>
            <div class="ld-toast-text">
                <div class="ld-toast-title">Berhasil</div>
                <div class="ld-toast-message" id="ldToastMessage">-</div>
            </div>
            <button type="button" class="ld-toast-close" data-bs-dismiss="toast" aria-label="Close">
                <i class="bi bi-x"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .ld-toast {
        border: none;
        border-radius: 14px;
        box-shadow: 0 12px 32px rgba(16,24,40,.18);
        overflow: hidden;
        min-width: 320px;
    }
    .ld-toast-body {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 14px 14px 16px;
        background: #fff;
        border-left: 4px solid #12A150;
    }
    .ld-toast.ld-toast-error .ld-toast-body { border-left-color: #D92D20; }

    .ld-toast-icon {
        width: 32px; height: 32px; border-radius: 50%;
        background: #E9F9EE; color: #12A150;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px; flex-shrink: 0;
    }
    .ld-toast.ld-toast-error .ld-toast-icon { background: #FEF3F2; color: #D92D20; }

    .ld-toast-text { flex: 1; min-width: 0; }
    .ld-toast-title { font-weight: 700; font-size: .88rem; color: #101828; }
    .ld-toast-message { font-size: .8rem; color: #667085; margin-top: 2px; }

    .ld-toast-close {
        border: none; background: transparent; color: #98A2B3;
        font-size: 16px; line-height: 1; cursor: pointer; flex-shrink: 0;
        padding: 2px;
    }
    .ld-toast-close:hover { color: #475467; }
</style>

<script>
    window.showToast = function (message, type = 'success') {
        const toastEl = document.getElementById('ldToast');
        if (!toastEl) {
            console.warn('#ldToast tidak ditemukan di halaman ini.');
            return;
        }

        const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3500 });

        toastEl.classList.remove('ld-toast-error');
        const icon = toastEl.querySelector('.ld-toast-icon i');
        const title = toastEl.querySelector('.ld-toast-title');

        if (type === 'error') {
            toastEl.classList.add('ld-toast-error');
            icon.className = 'bi bi-x-lg';
            title.textContent = 'Gagal';
        } else {
            icon.className = 'bi bi-check-lg';
            title.textContent = 'Berhasil';
        }

        document.getElementById('ldToastMessage').textContent = message;
        bsToast.show();
    };
</script>