(function () {
    const serviceBody = document.getElementById('smServiceBody');
    const modalEl = document.getElementById('smServiceModal');
    const currentServiceIdInput = document.getElementById('smCurrentServiceId');
    const detailNameEl = document.getElementById('smDetailName');
    const detailUrlEl = document.getElementById('smDetailUrl');
    const accessModeEl = document.getElementById('smAccessMode');
    const saveModeBtn = document.getElementById('smSaveModeBtn');
    const pegawaiSelect = document.getElementById('smPegawaiSelect');
    const addAssignBtn = document.getElementById('smAddAssignBtn');
    const assignedListEl = document.getElementById('smAssignedList');

    if (!serviceBody || !modalEl || !currentServiceIdInput || !detailNameEl || !detailUrlEl || !accessModeEl || !saveModeBtn || !pegawaiSelect || !addAssignBtn || !assignedListEl) {
        return;
    }

    const modal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;
    

    function showToast(message, type) {
        if (!message) return;
        if (type === 'success') {
            notifySuccess(message);
            return;
        }
        if (type === 'danger') {
            notifyError(message);
            return;
        }
        notifyInfo(message);
    }

    function escapeHtml(s) {
        return String(s)
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

    function renderAssignments(assignments) {
        if (!Array.isArray(assignments) || !assignments.length) {
            assignedListEl.innerHTML = (
                '<li class="list-group-item text-center py-4">' +
                    '<div class="d-flex flex-column justify-content-center align-items-center">' +
                        '<img src="' + (window.AppConfig ? AppConfig.initGlobal : '/') + 'apps/assets/media/illustrations/empty-content-profile.png" alt="Empty" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.85;">' +
                        '<h5 class="fw-bolder text-dark mb-1">Pencarian Tidak Ditemukan</h5><p class="text-muted mb-0 mx-auto" style="max-width: 400px; font-size: .95rem;">Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.</p>' +
                    '</div>' +
                '</li>'
            );
            return;
        }

        assignedListEl.innerHTML = assignments.map(function (item) {
            const nip = item.nip || '-';
            const nama = item.nama || '-';
            const nipEscaped = escapeHtml(nip);

            return (
                '<li class="list-group-item d-flex justify-content-between align-items-center" data-nip="' + nipEscaped + '">' +
                    '<div>' +
                        '<div class="fw-semibold">' + nipEscaped + '</div>' +
                        '<small class="text-muted">' + escapeHtml(nama) + '</small>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger js-sm-remove-assign" data-nip="' + nipEscaped + '">' +
                        '<i class="bi bi-trash"></i>' +
                    '</button>' +
                '</li>'
            );
        }).join('');
    }

    function renderServiceRows(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            serviceBody.innerHTML = (
                '<tr>' +
                    '<td colspan="6" class="text-center py-4">' +
                        '<div class="d-flex flex-column justify-content-center align-items-center">' +
                            '<img src="' + (window.AppConfig ? AppConfig.initGlobal : '/') + 'apps/assets/media/illustrations/empty-content-profile.png" alt="Empty" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.85;">' +
                            '<h5 class="fw-bolder text-dark mb-1">Pencarian Tidak Ditemukan</h5><p class="text-muted mb-0 mx-auto" style="max-width: 400px; font-size: .95rem;">Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.</p>' +
                        '</div>' +
                    '</td>' +
                '</tr>'
            );
            return;
        }

        serviceBody.innerHTML = rows.map(function (row) {
            const id = row.id || '';
            const mode = row.access_mode || 'everyone';
            const totalAssigned = Number(row.total_assigned || 0);
            return (
                '<tr data-service-id="' + escapeHtml(id) + '">' +
                    '<td>' + escapeHtml(id) + '</td>' +
                    '<td>' + escapeHtml(row.nama_layanan || '-') + '</td>' +
                    '<td><code>' + escapeHtml(row.url || '-') + '</code></td>' +
                    '<td class="js-sm-mode">' + escapeHtml(mode) + '</td>' +
                    '<td class="text-center js-sm-assigned">' + escapeHtml(totalAssigned) + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-primary js-sm-select" data-service-id="' + escapeHtml(id) + '">Pilih</button>' +
                    '</td>' +
                '</tr>'
            );
        }).join('');
    }

    async function fetchServiceList() {
        const res = await fetch(buildAppUrl('api/manage-layanan/list'), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat daftar layanan');
        }
        return Array.isArray(json.data) ? json.data : [];
    }

    function updateServiceRow(service) {
        if (!service || !service.id) {
            return;
        }

        const row = serviceBody.querySelector('tr[data-service-id="' + service.id + '"]');
        if (!row) {
            return;
        }

        const modeCell = row.querySelector('.js-sm-mode');
        const assignedCell = row.querySelector('.js-sm-assigned');

        if (modeCell) {
            modeCell.textContent = service.access_mode || 'everyone';
        }
        if (assignedCell) {
            assignedCell.textContent = String(service.total_assigned || 0);
        }
    }

    async function fetchServiceDetail(serviceId) {
        const res = await fetch(buildAppUrl('api/manage-layanan/detail?service_id=' + encodeURIComponent(serviceId)), {
            credentials: 'same-origin',
        });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat detail layanan');
        }
        return json.data || {};
    }

    function renderServiceDetail(data) {
        const service = data.service || {};
        const assignments = data.assignments || [];

        currentServiceIdInput.value = String(service.id || '');
        detailNameEl.textContent = service.nama_layanan || '-';
        detailUrlEl.textContent = service.url || '-';
        accessModeEl.value = service.access_mode || 'everyone';

        renderAssignments(assignments);
        updateServiceRow(service);
    }

    async function openServiceModal(serviceId) {
        detailNameEl.textContent = 'Memuat...';
        detailUrlEl.textContent = '-';
        assignedListEl.innerHTML = '<li class="list-group-item text-center text-muted">Memuat data...</li>';

        if (modal) {
            modal.show();
        }

        const data = await fetchServiceDetail(serviceId);
        renderServiceDetail(data);
    }

    async function saveMode() {
        const serviceId = currentServiceIdInput.value;
        const mode = accessModeEl.value;
        if (!serviceId) {
            throw new Error('Layanan belum dipilih');
        }

        const body = new URLSearchParams();
        body.set('layanan_id', serviceId);
        body.set('access_mode', mode);

        saveModeBtn.disabled = true;
        const res = await fetch(buildAppUrl('api/manage-layanan/mode'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
            credentials: 'same-origin',
        });
        const json = await res.json();
        saveModeBtn.disabled = false;

        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal menyimpan mode akses');
        }

        const detailData = await fetchServiceDetail(serviceId);
        renderServiceDetail(detailData);
        showToast(json.message || 'Mode akses layanan diperbarui.', 'success');
    }

    async function addAssignee() {
        const serviceId = currentServiceIdInput.value;
        const selectedNip = pegawaiSelect.value;
        if (!serviceId) {
            throw new Error('Layanan belum dipilih');
        }
        if (!selectedNip) {
            throw new Error('Pilih pegawai terlebih dahulu');
        }

        const body = new URLSearchParams();
        body.set('layanan_id', serviceId);
        body.set('nip', selectedNip);

        addAssignBtn.disabled = true;
        const res = await fetch(buildAppUrl('api/manage-layanan/assign/add'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
            credentials: 'same-origin',
        });
        const json = await res.json();
        addAssignBtn.disabled = false;

        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal menambahkan NIP assign');
        }

        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
            jQuery(pegawaiSelect).val(null).trigger('change');
        } else {
            pegawaiSelect.value = '';
        }

        const detailData = await fetchServiceDetail(serviceId);
        renderServiceDetail(detailData);
        showToast(json.message || 'NIP berhasil ditambahkan.', 'success');
    }

    async function removeAssignee(nip) {
        const serviceId = currentServiceIdInput.value;
        if (!serviceId) {
            throw new Error('Layanan belum dipilih');
        }

        const body = new URLSearchParams();
        body.set('layanan_id', String(serviceId));
        body.set('nip', String(nip));

        const res = await fetch(buildAppUrl('api/manage-layanan/assign/delete'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
            credentials: 'same-origin',
        });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal menghapus NIP assign');
        }

        const detailData = await fetchServiceDetail(serviceId);
        renderServiceDetail(detailData);
        showToast(json.message || 'NIP berhasil dihapus.', 'success');
    }

    function initPegawaiSelect2() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 !== 'function') {
            return;
        }

        const $select = jQuery(pegawaiSelect);
        const hasSelect2 = !!$select.data('select2');

        // Jika select2 sudah terpasang, cukup bersihkan elemen duplikat yang sempat tertinggal.
        if (hasSelect2) {
            const $rendered = $select.siblings('.select2');
            if ($rendered.length > 1) {
                $rendered.slice(1).remove();
            }
            return;
        }

        // Bersihkan jejak inisialisasi lama agar tidak memunculkan 2 select visual.
        $select.siblings('.select2').remove();

        $select.select2({
            width: '100%',
            theme: 'bootstrap-5',
            dropdownParent: jQuery(modalEl),
            placeholder: 'Cari NIP / nama pegawai...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                transport: function (params, success, failure) {
                    const term = (params.data && params.data.term) ? params.data.term : '';
                    fetch(buildAppUrl('api/manage-layanan/pegawai?q=' + encodeURIComponent(term)), { credentials: 'same-origin' })
                        .then(function (res) { return res.json(); })
                        .then(function (json) {
                            if (!json.status) {
                                throw new Error(json.message || 'Gagal memuat data pegawai');
                            }

                            const list = Array.isArray(json.data) ? json.data : [];
                            success({
                                results: list.map(function (item) {
                                    const nip = item.nip || '';
                                    const nama = item.nama || '';
                                    return {
                                        id: nip,
                                        text: (nip ? nip + ' - ' : '') + nama,
                                    };
                                }),
                            });
                        })
                        .catch(function (err) {
                            showToast((err && err.message) ? err.message : 'Gagal memuat data pegawai', 'danger');
                            failure(err);
                        });
                },
                processResults: function (data) {
                    return data;
                },
                delay: 250,
            },
        });
    }

    modalEl.addEventListener('shown.bs.modal', function () {
        initPegawaiSelect2();
    });

    serviceBody.addEventListener('click', async function (e) {
        const btn = e.target.closest('.js-sm-select');
        if (!btn) {
            return;
        }

        const serviceId = btn.getAttribute('data-service-id');
        if (!serviceId) {
            return;
        }

        try {
            await openServiceModal(serviceId);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });

    saveModeBtn.addEventListener('click', async function () {
        try {
            await saveMode();
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });

    addAssignBtn.addEventListener('click', async function () {
        try {
            await addAssignee();
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });

    assignedListEl.addEventListener('click', async function (e) {
        const btn = e.target.closest('.js-sm-remove-assign');
        if (!btn) {
            return;
        }

        const nip = btn.getAttribute('data-nip');
        if (!nip) {
            return;
        }

        try {
            await removeAssignee(nip);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });

    (async function init() {
        try {
            serviceBody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Memuat data...</td></tr>';
            const rows = await fetchServiceList();
            renderServiceRows(rows);
        } catch (err) {
            renderServiceRows([]);
            showToast(err.message || 'Gagal memuat daftar layanan', 'danger');
        }
    })();
})();



