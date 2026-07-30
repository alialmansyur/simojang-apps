(function () {
    const segments = String(window.location.pathname || '')
        .split('/')
        .map(function (s) { return s.trim(); })
        .filter(Boolean);
    const slug = segments.length > 1 ? segments[segments.length - 1] : '';
    if (!slug) return;

    const head = document.getElementById('refHead');
    const body = document.getElementById('refBody');
    const form = document.getElementById('refForm');
    const modalEl = document.getElementById('refFormModal');
    const modalTitle = document.getElementById('refModalTitle');
    const btnSave = document.getElementById('refBtnSave');
    const btnAdd = document.getElementById('refBtnAdd');
    const btnSearch = document.getElementById('refBtnSearch');
    const inputSearch = document.getElementById('refSearch');
    const btnPrev = document.getElementById('refPrev');
    const btnNext = document.getElementById('refNext');
    const pagingInfo = document.getElementById('refPagingInfo');
    const pageTitle = document.getElementById('refPageTitle');
    const pageSubtitle = document.getElementById('refPageSubtitle');
    

    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const state = {
        schema: null,
        rows: [],
        page: 1,
        perPage: 10,
        totalPage: 1,
        total: 0,
        search: '',
        sortBy: '',
        sortDir: 'desc',
        mode: 'create',
        editId: null
    };

    function showToast(message, type) {
        if (!message) return;
        if (type === 'success') {
            notifySuccess(message);
            return;
        }
        if (type === 'warning') {
            notifyInfo(message);
            return;
        }
        notifyError(message);
    }

    function getEmptySrc() {
        

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildAppUrl(path) {
        const rawBase = window.AppConfig && AppConfig.initGlobal ? String(AppConfig.initGlobal) : '/';
        if (/^https?:\/\//i.test(String(path || ''))) {
            return String(path);
        }
        const base = rawBase.endsWith('/') ? rawBase : (rawBase + '/');
        const cleanPath = String(path || '').replace(/^\/+/, '');
        return base + cleanPath;
    }

    function debounce(fn, delay) {
        let t = null;
        return function () {
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(function () {
                fn.apply(null, args);
            }, delay);
        };
    }

    function getWritableColumns() {
        if (!state.schema || !Array.isArray(state.schema.columns)) return [];
        return state.schema.columns.filter((c) => c.is_writable);
    }

    function renderEmpty(message) {
        const totalCol = Math.max(2, (state.schema?.columns || []).length + 1);
        body.innerHTML = ''
            + '<tr><td colspan="' + totalCol + '" class="text-center py-4">'
            + '<div class="d-flex flex-column justify-content-center align-items-center">'
            + '<iframe src="' + getEmptySrc() + '" style="width:210px; height:210px; border:none;" title="Tidak ada data"></iframe>'
            + '<p class="text-muted mt-2 mb-0">' + escapeHtml(message) + '</p>'
            + '</div>'
            + '</td></tr>';
    }

    function renderTable() {
        if (!state.schema) return;
        const columns = state.schema.columns || [];
        if (columns.length === 0) {
            head.innerHTML = '<tr><th>Data</th></tr>';
            renderEmpty('Kolom tabel tidak ditemukan.');
            return;
        }

        head.innerHTML = '<tr>'
            + columns.map((c) => {
                const active = state.sortBy === c.name;
                const icon = active ? (state.sortDir === 'asc' ? '▲' : '▼') : '';
                return '<th><button type="button" class="btn btn-sm p-0 border-0 bg-transparent ref-sort" data-col="' + escapeHtml(c.name) + '">' + escapeHtml(c.label) + ' <span class="text-muted small">' + icon + '</span></button></th>';
            }).join('')
            + '<th style="width:120px;" class="text-center">Aksi</th>'
            + '</tr>';

        if (!Array.isArray(state.rows) || state.rows.length === 0) {
            renderEmpty('Tidak ada data untuk saat ini.');
            return;
        }

        const pk = state.schema.pk;
        body.innerHTML = state.rows.map((row, index) => {
            const rowCells = columns.map((c) => '<td>' + escapeHtml(row[c.name] ?? '') + '</td>').join('');
            const rowId = row[pk];
            return '<tr data-index="' + index + '" data-id="' + escapeHtml(rowId ?? '') + '">'
                + rowCells
                + '<td class="text-center">'
                + '<button type="button" class="btn btn-sm btn-outline-primary me-1 ref-edit">Edit</button>'
                + '<button type="button" class="btn btn-sm btn-outline-danger ref-delete">Hapus</button>'
                + '</td>'
                + '</tr>';
        }).join('');
    }

    function renderPaging() {
        pagingInfo.textContent = 'Halaman ' + state.page + ' dari ' + state.totalPage + ' | Total: ' + state.total;
        btnPrev.disabled = state.page <= 1;
        btnNext.disabled = state.page >= state.totalPage;
    }

    function getPayloadFromForm() {
        const payload = {};
        const columns = getWritableColumns();
        columns.forEach((col) => {
            const el = form.querySelector('[name="' + col.name + '"]');
            if (!el) return;
            payload[col.name] = el.value;
        });
        return payload;
    }

    function validatePayload(payload) {
        const columns = getWritableColumns();
        for (let i = 0; i < columns.length; i++) {
            const col = columns[i];
            const required = !col.is_nullable && col.default === null;
            if (required && String(payload[col.name] ?? '').trim() === '') {
                return col.label + ' wajib diisi.';
            }
        }
        return null;
    }

    function buildForm(row) {
        const cols = getWritableColumns();
        if (cols.length === 0) {
            form.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">Tidak ada kolom yang dapat diinput.</div></div>';
            return;
        }

        form.innerHTML = cols.map((col) => {
            const value = row ? (row[col.name] ?? '') : '';
            const inputType = col.input_type || 'text';
            const required = (!col.is_nullable && col.default === null) ? 'required' : '';
            if (inputType === 'textarea') {
                return '<div class="col-md-6"><label class="form-label">' + escapeHtml(col.label) + '</label><textarea class="form-control" name="' + escapeHtml(col.name) + '" ' + required + '>' + escapeHtml(value) + '</textarea></div>';
            }
            return '<div class="col-md-6"><label class="form-label">' + escapeHtml(col.label) + '</label><input class="form-control" type="' + escapeHtml(inputType) + '" name="' + escapeHtml(col.name) + '" value="' + escapeHtml(value) + '" ' + required + '></div>';
        }).join('');
    }

    async function fetchSchema() {
        const res = await fetch(buildAppUrl('api/ref/' + encodeURIComponent(slug) + '/schema'), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat schema');
        }
        state.schema = json.data || null;
        state.sortBy = state.schema?.pk || '';
        pageTitle.textContent = 'Referensi - ' + (slug.replaceAll('_', ' '));
        if (pageSubtitle) {
            pageSubtitle.innerHTML = 'Tabel: <code>' + escapeHtml(slug) + '</code>';
        }
    }

    async function fetchList() {
        const query = new URLSearchParams({
            page: String(state.page),
            per_page: String(state.perPage),
            search: state.search,
            sort_by: state.sortBy,
            sort_dir: state.sortDir
        });

        const res = await fetch(buildAppUrl('api/ref/' + encodeURIComponent(slug) + '?' + query.toString()), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat data');
        }
        state.rows = json.data || [];
        const meta = json.meta || {};
        state.total = Number(meta.total || 0);
        state.totalPage = Math.max(1, Number(meta.total_page || 1));
        renderTable();
        renderPaging();
    }

    async function submitForm() {
        const payload = getPayloadFromForm();
        const validationError = validatePayload(payload);
        if (validationError) {
            showToast(validationError, 'warning');
            return;
        }

        btnSave.disabled = true;
        try {
            let res;
            if (state.mode === 'edit' && state.editId !== null) {
                res = await fetch(buildAppUrl('api/ref/' + encodeURIComponent(slug) + '/' + encodeURIComponent(String(state.editId))), {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
            } else {
                res = await fetch(buildAppUrl('api/ref/' + encodeURIComponent(slug)), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
            }
            const json = await res.json();
            if (!res.ok || !json.status) {
                throw new Error(json.message || 'Gagal menyimpan data');
            }
            modal.hide();
            await fetchList();
            showToast(json.message || 'Data berhasil disimpan.', 'success');
        } catch (err) {
            showToast(err.message, 'danger');
        } finally {
            btnSave.disabled = false;
        }
    }

    async function deleteRow(rowId) {
        const ok = confirm('Hapus data ini?');
        if (!ok) return;
        try {
            const res = await fetch(buildAppUrl('api/ref/' + encodeURIComponent(slug) + '/' + encodeURIComponent(String(rowId))), {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            const json = await res.json();
            if (!res.ok || !json.status) {
                throw new Error(json.message || 'Gagal menghapus data');
            }
            await fetchList();
            showToast(json.message || 'Data berhasil dihapus.', 'success');
        } catch (err) {
            showToast(err.message, 'danger');
        }
    }

    function openAddModal() {
        state.mode = 'create';
        state.editId = null;
        modalTitle.textContent = 'Tambah Data';
        buildForm(null);
        modal.show();
    }

    function openEditModal(index) {
        const row = state.rows[index];
        if (!row || !state.schema) return;
        state.mode = 'edit';
        state.editId = row[state.schema.pk];
        modalTitle.textContent = 'Edit Data';
        buildForm(row);
        modal.show();
    }

    function bindEvents() {
        btnAdd.addEventListener('click', openAddModal);
        btnSave.addEventListener('click', submitForm);
        btnSearch.addEventListener('click', function () {
            state.page = 1;
            state.search = inputSearch.value.trim();
            fetchList().catch((err) => showToast(err.message, 'danger'));
        });

        inputSearch.addEventListener('input', debounce(function () {
            state.page = 1;
            state.search = inputSearch.value.trim();
            fetchList().catch((err) => showToast(err.message, 'danger'));
        }, 350));

        btnPrev.addEventListener('click', function () {
            if (state.page <= 1) return;
            state.page -= 1;
            fetchList().catch((err) => showToast(err.message, 'danger'));
        });

        btnNext.addEventListener('click', function () {
            if (state.page >= state.totalPage) return;
            state.page += 1;
            fetchList().catch((err) => showToast(err.message, 'danger'));
        });

        head.addEventListener('click', function (e) {
            const btn = e.target.closest('.ref-sort');
            if (!btn) return;
            const col = btn.getAttribute('data-col') || '';
            if (!col) return;
            if (state.sortBy === col) {
                state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sortBy = col;
                state.sortDir = 'asc';
            }
            fetchList().catch((err) => showToast(err.message, 'danger'));
        });

        body.addEventListener('click', function (e) {
            const row = e.target.closest('tr');
            if (!row) return;
            const index = Number(row.getAttribute('data-index'));
            if (e.target.classList.contains('ref-edit')) {
                openEditModal(index);
                return;
            }
            if (e.target.classList.contains('ref-delete')) {
                if (!state.schema) return;
                const data = state.rows[index];
                const rowId = data ? data[state.schema.pk] : null;
                if (rowId === null || rowId === undefined || rowId === '') return;
                deleteRow(rowId);
            }
        });
    }

    async function init() {
        try {
            await fetchSchema();
            await fetchList();
            bindEvents();
        } catch (err) {
            renderEmpty('Gagal memuat data referensi.');
            showToast(err.message, 'danger');
        }
    }

    init();
})();

