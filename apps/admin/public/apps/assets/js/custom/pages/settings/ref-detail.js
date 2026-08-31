(function () {
    const segments = String(window.location.pathname || '')
        .split('/')
        .map(function (s) { return s.trim(); })
        .filter(Boolean);
    const slug = segments.length > 1 ? segments[segments.length - 1] : '';
    if (!slug) return;

    const form = document.getElementById('refForm');
    const modalEl = document.getElementById('refFormModal');
    const modalTitle = document.getElementById('refModalTitle');
    const btnSave = document.getElementById('refBtnSave');
    const pageTitle = document.getElementById('refPageTitle');
    const pageSubtitle = document.getElementById('refPageSubtitle');
    const cardTitle = document.getElementById('refCardTitle');

    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    let state = {
        schema: null,
        mode: 'create',
        editId: null
    };

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

    function getWritableColumns() {
        if (!state.schema || !Array.isArray(state.schema.columns)) return [];
        return state.schema.columns.filter((c) => c.is_writable);
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

    function swlErrorHandler(msg) {
        Swal.fire({
            toast: true,
            position: 'top',
            icon: 'error',
            title: msg,
            timer: 1500,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
    }

    function swlSuccess(msg) {
        Swal.fire({
            toast: true,
            position: 'top',
            icon: 'success',
            title: msg || 'Data berhasil di simpan',
            timer: 1500,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
    }

    async function submitForm() {
        const payload = getPayloadFromForm();
        const validationError = validatePayload(payload);
        if (validationError) {
            swlErrorHandler(validationError);
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
            swlSuccess(json.message);
            $('#dataTable').DataTable().ajax.reload(null, false);
        } catch (err) {
            swlErrorHandler(err.message);
        } finally {
            btnSave.disabled = false;
        }
    }

    function statusData(key, sts) {
        
        $.ajax({
            type: "POST",
            url: buildAppUrl("status-data"),
            data: {
                key: key,
                status: sts,
                tableinfo: slug,
            },
            dataType: 'json',
            success: function (response) {
                swlSuccess('Status berhasil diubah');
                $('#dataTable').DataTable().ajax.reload(null, false);
            },
            error: function () {
                swlErrorHandler('Gagal mengubah status');
                $('#dataTable').DataTable().ajax.reload(null, false);
            }
        });
    }

    async function initDataTables() {
        const res = await fetch(buildAppUrl('api/ref/' + encodeURIComponent(slug) + '/schema'), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            swlErrorHandler(json.message || 'Gagal memuat schema');
            return;
        }
        state.schema = json.data;

        if (pageTitle) pageTitle.textContent = 'Referensi ' + (slug.replaceAll('_', ' '));

        const theadTr = document.querySelector('#refHead tr');
        theadTr.innerHTML = '<th></th>'; // For dtr-control

        let dtColumns = [
            { data: null, defaultContent: '', className: 'dtr-control', orderable: false, searchable: false }
        ];

        state.schema.columns.forEach((col) => {
            if (col.name === 'id') return; // Hide standard id if it exists, but wait, usually we want to hide it or keep it? We hide PK if it's just 'id' or we can just show it. Let's show everything except maybe PK if it's just id.
            
            theadTr.innerHTML += `<th>${escapeHtml(col.label)}</th>`;

            if (col.name === 'is_status') {
                dtColumns.push({
                    data: 'is_status',
                    render: function(data, type, row) {
                        let checked = data == 1 ? 'checked' : '';
                        let label = data == 1 ? 'Aktif' : 'Non-Aktif';
                        return `
                            <div class="form-check form-switch d-flex align-items-center gap-2">
                                <input class="form-check-input btn-status m-0" type="checkbox"
                                    id="switch-${row[state.schema.pk]}"
                                    ${checked}
                                    name="status_col" data-key="${row[state.schema.pk]}" style="cursor: pointer;">
                                <label class="form-check-label mb-0" for="switch-${row[state.schema.pk]}" style="cursor: pointer;">${label}</label>
                            </div>`;
                    }
                });
            } else {
                dtColumns.push({ 
                    data: col.name,
                    render: function(data) {
                        return escapeHtml(data || '');
                    }
                });
            }
        });

        // Add action column
        theadTr.innerHTML += `<th></th>`;
        dtColumns.push({
            data: null,
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                return `<button class="btn btn-sm btn-light-danger btn-remove" data-id="${row[state.schema.pk]}">
                            <i class='bi bi-trash'></i>
                        </button>
                        <button class="btn btn-sm btn-light-primary btn-update" data-id="${row[state.schema.pk]}">
                            <i class='bi bi-pencil'></i>
                        </button>`;
            }
        });

        $('#dataTable').on('processing.dt', function (e, settings, processing) {
            var tbody = $(this).find('tbody');
            if (processing && tbody.find('td.dataTables_empty').length === 0 && tbody.find('tr').length === 1 && tbody.find('td').attr('colspan') > 1) {
                tbody.html(`
                <tr>
                    <td colspan="${dtColumns.length}" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    </td>
                </tr>
                `);
            }
        });

        $('#dataTable').DataTable({
            responsive: {
                details: {
                    type: 'column',
                    target: 'td.dtr-control'
                }
            },
            processing: true,
            serverSide: true,
            pageLength: 25,
            order: [[1, 'asc']], // Order by first visible column (index 1)
            buttons: ['copy', 'excel', 'pdf', 'print'],
            ajax: function(data, callback, settings) {
                const sortColIndex = data.order && data.order.length > 0 ? data.order[0].column : 1;
                const sortColName = dtColumns[sortColIndex].data || state.schema.pk;
                const sortDir = data.order && data.order.length > 0 ? data.order[0].dir : 'asc';
                const search = data.search ? data.search.value : '';

                const params = {
                    page: (data.start / data.length) + 1,
                    per_page: data.length,
                    search: search,
                    sort_by: sortColName,
                    sort_dir: sortDir
                };

                $.ajax({
                    url: buildAppUrl('api/ref/' + encodeURIComponent(slug)),
                    type: "GET",
                    data: params,
                    success: function(res) {
                        callback({
                            draw: data.draw,
                            recordsTotal: res.meta.total,
                            recordsFiltered: res.meta.total,
                            data: res.data
                        });
                    },
                    error: function(xhr) {
                        swlErrorHandler('Gagal memuat data tabel');
                        callback({
                            draw: data.draw,
                            recordsTotal: 0,
                            recordsFiltered: 0,
                            data: []
                        });
                    }
                });
            },
            columns: dtColumns
        });

        // Event listeners for Datatable buttons
        $('#dataTable tbody').on('click', 'tr td .btn-status', function (e) {
            e.stopPropagation();
            var $checkbox = $(this);
            var key = $checkbox.attr('data-key');
            var sts = $checkbox.prop('checked') ? 1 : 0;
            if (sts == 0) {
                Swal.fire({
                    text: "Apa anda yakin akan menonaktifkan data ini? Saat data non-aktif maka data tidak akan muncul.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d63031",
                    confirmButtonText: "Ya",
                    cancelButtonText: "Tidak"
                }).then((result) => {
                    if (result.isConfirmed) {
                        statusData(key, sts);
                    } else {
                        $checkbox.prop('checked', true);
                    }
                });
            } else {
                statusData(key, sts);
            }
        });

        $('#dataTable tbody').on('click', 'tr td .btn-remove', function (e) {
            e.stopPropagation();
            var key = $(this).attr('data-id');
            Swal.fire({
                text: "Apa anda yakin akan mengahapus data ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d63031",
                confirmButtonText: "Ya",
                cancelButtonText: "Tidak"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "DELETE",
                        url: buildAppUrl("api/ref/" + encodeURIComponent(slug) + "/" + encodeURIComponent(key)),
                        success: function (response) {
                            if (response && response.status) {
                                swlSuccess('Data berhasil dihapus');
                                $('#dataTable').DataTable().ajax.reload(null, false);
                            } else {
                                swlErrorHandler(response.message || 'Gagal menghapus data');
                            }
                        },
                        error: function (xhr) {
                            var res = xhr.responseJSON;
                            swlErrorHandler(res && res.message ? res.message : 'Gagal menghapus data');
                        }
                    });
                }
            });
        });

        $('#dataTable tbody').on('click', 'tr td .btn-update', function (e) {
            e.stopPropagation();
            var data = $('#dataTable').DataTable().row($(this).parents('tr')).data();
            if (!data) return;
            
            state.mode = 'edit';
            state.editId = data[state.schema.pk];
            modalTitle.textContent = 'Edit Data';
            buildForm(data);
            modal.show();
        });
    }

    document.getElementById('refBtnAdd').addEventListener('click', function(e) {
        e.preventDefault();
        state.mode = 'create';
        state.editId = null;
        modalTitle.textContent = 'Tambah Data';
        buildForm(null);
        modal.show();
    });

    btnSave.addEventListener('click', submitForm);

    $('#refFormModal').on('hidden.bs.modal', function () {
        $('#refForm')[0].reset();
        modalTitle.textContent = 'Tambah Data';
    });

    initDataTables();

})();
