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
    const searchInput = document.getElementById('searchService');
    const refreshBtn = document.getElementById('btnRefreshServices');

    if (!serviceBody || !modalEl || !currentServiceIdInput || !detailNameEl || !detailUrlEl || !accessModeEl || !saveModeBtn || !pegawaiSelect || !addAssignBtn || !assignedListEl) {
        return;
    }

    let allServices = [];
    const modal = typeof bootstrap !== 'undefined' ? new bootstrap.Modal(modalEl) : null;

    function showToast(message, type) {
        if (!message) return;
        if (type === 'success') {
            if (typeof notifySuccess === 'function') {
                notifySuccess(message);
            } else if (typeof notify === 'function') {
                notify(message, 'success');
            }
            return;
        }
        if (type === 'danger' || type === 'error') {
            if (typeof notifyError === 'function') {
                notifyError(message);
            } else if (typeof notify === 'function') {
                notify(message, 'error');
            }
            return;
        }
        if (typeof notifyInfo === 'function') {
            notifyInfo(message);
        } else if (typeof notify === 'function') {
            notify(message, 'info');
        }
    }

    function escapeHtml(s) {
        return String(s || '')
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

    function updateBannerStats(rows) {
        const totalEl = document.getElementById('statTotalServices');
        const publicEl = document.getElementById('statPublicServices');
        const assignedEl = document.getElementById('statAssignedServices');

        if (!Array.isArray(rows)) return;

        let total = rows.length;
        let publicCount = 0;
        let assignedCount = 0;

        rows.forEach(function (r) {
            if (r.access_mode === 'assigned') {
                assignedCount++;
            } else {
                publicCount++;
            }
        });

        if (totalEl) totalEl.textContent = String(total);
        if (publicEl) publicEl.textContent = String(publicCount);
        if (assignedEl) assignedEl.textContent = String(assignedCount);
    }

    function renderAssignments(assignments) {
        if (!Array.isArray(assignments) || !assignments.length) {
            assignedListEl.innerHTML = (
                '<li class="list-group-item text-center py-4 text-muted">' +
                    '<i class="bi bi-people fs-2 d-block mb-1 text-secondary opacity-50"></i>' +
                    '<span class="fw-semibold">Belum ada pegawai yang ditugaskan khusus</span>' +
                '</li>'
            );
            return;
        }

        assignedListEl.innerHTML = assignments.map(function (item) {
            const nip = item.nip || '-';
            const nama = item.nama || '-';
            const nipEscaped = escapeHtml(nip);

            return (
                '<li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" data-nip="' + nipEscaped + '">' +
                    '<div>' +
                        '<div class="fw-bold text-dark font-monospace" style="font-size: 0.95rem;">' + nipEscaped + '</div>' +
                        '<div class="text-secondary small">' + escapeHtml(nama) + '</div>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger js-sm-remove-assign d-inline-flex align-items-center gap-1" data-nip="' + nipEscaped + '" title="Hapus Penugasan NIP">' +
                        '<i class="bi bi-trash3"></i> <span class="d-none d-sm-inline">Hapus</span>' +
                    '</button>' +
                '</li>'
            );
        }).join('');
    }

    function renderServiceRows(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            serviceBody.innerHTML = (
                '<tr>' +
                    '<td colspan="6" class="text-center py-5 text-muted">' +
                        '<i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>' +
                        '<h6 class="fw-bold text-dark mb-1">Tidak ada data layanan yang ditemukan</h6>' +
                        '<small>Silakan periksa kembali kata kunci pencarian Anda.</small>' +
                    '</td>' +
                '</tr>'
            );
            return;
        }

        serviceBody.innerHTML = rows.map(function (row) {
            const id = row.id || '';
            const mode = row.access_mode || 'everyone';
            const totalAssigned = Number(row.total_assigned || 0);

            let modeBadge = '';
            if (mode === 'assigned') {
                modeBadge = '<span class="mode-badge-assigned"><i class="bi bi-person-lock"></i> Pegawai Tertentu</span>';
            } else {
                modeBadge = '<span class="mode-badge-everyone"><i class="bi bi-globe2"></i> Semua Pegawai</span>';
            }

            let assignedText = '';
            if (mode === 'assigned') {
                assignedText = '<span class="badge bg-primary rounded-pill px-2 py-1">' + totalAssigned + ' Pegawai</span>';
            } else {
                assignedText = '<span class="text-muted small">Semua</span>';
            }

            return (
                '<tr data-service-id="' + escapeHtml(id) + '">' +
                    '<td class="text-center fw-bold text-muted">' + escapeHtml(id) + '</td>' +
                    '<td><span class="fw-bold text-dark">' + escapeHtml(row.nama_layanan || '-') + '</span></td>' +
                    '<td><code class="px-2 py-1 bg-light border rounded text-primary font-monospace">' + escapeHtml(row.url || '-') + '</code></td>' +
                    '<td class="text-center js-sm-mode">' + modeBadge + '</td>' +
                    '<td class="text-center js-sm-assigned">' + assignedText + '</td>' +
                    '<td class="text-center">' +
                        '<button type="button" class="btn btn-sm btn-outline-primary js-sm-select fw-bold px-3 d-inline-flex align-items-center gap-1" data-service-id="' + escapeHtml(id) + '" style="border-radius: 6px;">' +
                            '<i class="bi bi-sliders"></i> Atur Akses' +
                        '</button>' +
                    '</td>' +
                '</tr>'
            );
        }).join('');
    }

    function filterServices() {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        if (!query) {
            renderServiceRows(allServices);
            return;
        }

        const filtered = allServices.filter(function (s) {
            const name = String(s.nama_layanan || '').toLowerCase();
            const url = String(s.url || '').toLowerCase();
            const mode = String(s.access_mode || '').toLowerCase();
            return name.includes(query) || url.includes(query) || mode.includes(query);
        });

        renderServiceRows(filtered);
    }

    async function fetchServiceList() {
        const res = await fetch(buildAppUrl('api/manage-layanan/list'), { credentials: 'same-origin' });
        const json = await res.json();
        if (!res.ok || !json.status) {
            throw new Error(json.message || 'Gagal memuat daftar layanan');
        }
        allServices = Array.isArray(json.data) ? json.data : [];
        updateBannerStats(allServices);
        return allServices;
    }

    function updateServiceRow(service) {
        if (!service || !service.id) {
            return;
        }

        // Update in cache array
        const idx = allServices.findIndex(function (s) { return String(s.id) === String(service.id); });
        if (idx !== -1) {
            allServices[idx] = Object.assign({}, allServices[idx], service);
            updateBannerStats(allServices);
        }

        const row = serviceBody.querySelector('tr[data-service-id="' + service.id + '"]');
        if (!row) {
            return;
        }

        const modeCell = row.querySelector('.js-sm-mode');
        const assignedCell = row.querySelector('.js-sm-assigned');

        const mode = service.access_mode || 'everyone';
        const totalAssigned = Number(service.total_assigned || 0);

        if (modeCell) {
            if (mode === 'assigned') {
                modeCell.innerHTML = '<span class="mode-badge-assigned"><i class="bi bi-person-lock"></i> Pegawai Tertentu</span>';
            } else {
                modeCell.innerHTML = '<span class="mode-badge-everyone"><i class="bi bi-globe2"></i> Semua Pegawai</span>';
            }
        }

        if (assignedCell) {
            if (mode === 'assigned') {
                assignedCell.innerHTML = '<span class="badge bg-primary rounded-pill px-2 py-1">' + totalAssigned + ' Pegawai</span>';
            } else {
                assignedCell.innerHTML = '<span class="text-muted small">Semua</span>';
            }
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
        assignedListEl.innerHTML = '<li class="list-group-item text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-1"></span> Memuat data...</li>';

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
        const originalText = saveModeBtn.innerHTML;
        saveModeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

        try {
            const res = await fetch(buildAppUrl('api/manage-layanan/mode'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString(),
                credentials: 'same-origin',
            });
            const json = await res.json();

            if (!res.ok || !json.status) {
                throw new Error(json.message || 'Gagal menyimpan mode akses');
            }

            const detailData = await fetchServiceDetail(serviceId);
            renderServiceDetail(detailData);
            showToast(json.message || 'Mode akses layanan diperbarui.', 'success');
        } finally {
            saveModeBtn.disabled = false;
            saveModeBtn.innerHTML = originalText;
        }
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
        const originalText = addAssignBtn.innerHTML;
        addAssignBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';

        try {
            const res = await fetch(buildAppUrl('api/manage-layanan/assign/add'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString(),
                credentials: 'same-origin',
            });
            const json = await res.json();

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
        } finally {
            addAssignBtn.disabled = false;
            addAssignBtn.innerHTML = originalText;
        }
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

        if (hasSelect2) {
            const $rendered = $select.siblings('.select2');
            if ($rendered.length > 1) {
                $rendered.slice(1).remove();
            }
            return;
        }

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
        if (!btn) return;

        const serviceId = btn.getAttribute('data-service-id');
        if (!serviceId) return;

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
        if (!btn) return;

        const nip = btn.getAttribute('data-nip');
        if (!nip) return;

        try {
            await removeAssignee(nip);
        } catch (err) {
            showToast(err.message, 'danger');
        }
    });

    if (searchInput) {
        searchInput.addEventListener('input', filterServices);
    }

    if (refreshBtn) {
        refreshBtn.addEventListener('click', async function () {
            try {
                serviceBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span> Memuat ulang data...</td></tr>';
                const rows = await fetchServiceList();
                filterServices();
                showToast('Daftar layanan berhasil dimuat ulang', 'success');
            } catch (err) {
                renderServiceRows([]);
                showToast(err.message || 'Gagal memuat daftar layanan', 'danger');
            }
        });
    }

    (async function init() {
        try {
            serviceBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-1"></span> Memuat data layanan...</td></tr>';
            const rows = await fetchServiceList();
            renderServiceRows(rows);
        } catch (err) {
            renderServiceRows([]);
            showToast(err.message || 'Gagal memuat daftar layanan', 'danger');
        }
    })();
})();
