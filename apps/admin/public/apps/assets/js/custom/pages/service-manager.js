/**
 * Service Manager Page JavaScript
 * Handles Pegawai Dropdown, Tree Mapping Table (Tim Kerja -> Layanan),
 * Expand/Collapse Arrows, AJAX Checkbox Toggles with Cascade,
 * Live Filter, Reset Default, Copy Permissions & Modals.
 */

$(document).ready(function () {
    let currentNip = String($('#selectPegawai').val() || '').trim();
    let currentPegawaiData = null;
    let currentTreeData = [];
    let isTreeLoading = false;
    const collapsedNodes = new Set();

    if ($.fn.select2) {
        $('#selectPegawai').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Cari & Pilih Pegawai --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: AppConfig.initGlobal + 'api/manage-service/pegawai-list',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        limit: 20
                    };
                },
                processResults: function (data) {
                    let results = [];
                    if (data && data.status && data.data) {
                        results = data.data.map(function(item) {
                            return {
                                id: item.nip,
                                text: item.nama + ' (' + item.nip + ') - ' + (item.unit_kerja_nama || '-')
                            };
                        });
                    }
                    return { results: results };
                },
                cache: true
            },
            minimumInputLength: 0
        });

        $('#selectSourcePegawai').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalCopyPermission'),
            placeholder: '-- Cari & Pilih Pegawai Sumber --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: AppConfig.initGlobal + 'api/manage-service/pegawai-list',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term || '',
                        limit: 20
                    };
                },
                processResults: function (data) {
                    let results = [];
                    if (data && data.status && data.data) {
                        results = data.data.map(function(item) {
                            return {
                                id: item.nip,
                                text: item.nama + ' (' + item.nip + ') - ' + (item.unit_kerja_nama || '-')
                            };
                        });
                    }
                    return { results: results };
                },
                cache: true
            },
            minimumInputLength: 0
        });
    }

    // Inisialisasi awal
    updatePageState(currentNip);
    if (currentNip !== '') {
        loadServiceTree(currentNip);
    }

    function updatePageState(nip) {
        if (!nip || nip === '') {
            $('#serviceSummaryCard').addClass('d-none');
            $('#treeTableWrapper').addClass('d-none');
            $('#treeSkeleton').addClass('d-none');
            $('#treeEmptyState').addClass('d-none');
            $('#treeSelectPrompt').removeClass('d-none');
            $('#btnResetDefault').prop('disabled', true).addClass('opacity-50');
            $('#btnOpenCopyModal').prop('disabled', true).addClass('opacity-50');
            $('#searchService').prop('disabled', true).val('');
        } else {
            $('#treeSelectPrompt').addClass('d-none');
            $('#serviceSummaryCard').removeClass('d-none');
            $('#treeTableWrapper').removeClass('d-none');
            $('#btnResetDefault').prop('disabled', false).removeClass('opacity-50');
            $('#btnOpenCopyModal').prop('disabled', false).removeClass('opacity-50');
            $('#searchService').prop('disabled', false);
        }
    }

    // 1. Event Pegawai Dropdown Change
    $('#selectPegawai').on('change', function () {
        const nip = String($(this).val() || '').trim();
        currentNip = nip;
        $('#searchService').val('');
        collapsedNodes.clear();
        updatePageState(currentNip);

        if (currentNip !== '') {
            loadServiceTree(currentNip);
        }
    });

    // 2. Event Live Search Filter pada Service Tree
    let searchTimer = null;
    $('#searchService').on('input', function () {
        clearTimeout(searchTimer);
        const query = $(this).val().toLowerCase().trim();
        searchTimer = setTimeout(() => {
            filterTreeTable(query);
        }, 150);
    });

    $('#btnResetSearchService').on('click', function () {
        $('#searchService').val('').trigger('input');
    });


    // 3. Event Expand/Collapse Tim Kerja Node Arrow
    $(document).on('click', '.btn-tree-toggle, .tree-title.clickable-title', function (e) {
        e.stopPropagation();
        const nodeId = String($(this).data('node-id') || '').trim();
        if (nodeId === '') return;
        toggleNodeCollapse(nodeId);
    });

    function toggleNodeCollapse(nodeId) {
        const $btn = $(`.btn-tree-toggle[data-node-id="${nodeId}"]`);
        if (collapsedNodes.has(nodeId)) {
            collapsedNodes.delete(nodeId);
            $btn.removeClass('collapsed');
            $btn.attr('title', 'Tutup Tim Kerja');
        } else {
            collapsedNodes.add(nodeId);
            $btn.addClass('collapsed');
            $btn.attr('title', 'Buka Tim Kerja');
        }

        applyTreeVisibility();
    }

    function applyTreeVisibility() {
        const searchQuery = $('#searchService').val().toLowerCase().trim();
        if (searchQuery !== '') {
            return;
        }

        $('#treeTableBody tr').each(function () {
            const ancestors = String($(this).data('ancestors') || '').split(' ').filter(Boolean);
            const isHiddenByAncestor = ancestors.some((ancId) => collapsedNodes.has(ancId));

            if (isHiddenByAncestor) {
                $(this).addClass('d-none');
            } else {
                $(this).removeClass('d-none');
            }
        });
    }

    // 4. Event Checkbox Toggle Permission (AJAX with Rollback & Loading Spinner)
    $(document).on('change', '.perm-switch', function () {
        const $checkbox = $(this);
        const nodeId = $checkbox.data('id');
        const rawId = $checkbox.data('raw-id');
        const nodeType = $checkbox.data('type') || 'service';
        const isAllowed = $checkbox.is(':checked');
        const previousState = !isAllowed;
        const $row = $checkbox.closest('tr');
        const $spinner = $row.find('.perm-spinner');
        const parentId = $checkbox.data('parent-id');

        if (!currentNip || !nodeId) {
            $checkbox.prop('checked', previousState);
            return;
        }

        $checkbox.prop('disabled', true);
        $spinner.removeClass('d-none');
        $row.addClass('tree-row-processing');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-service/toggle',
            type: 'POST',
            data: {
                nip: currentNip,
                node_id: rawId || nodeId,
                node_type: nodeType,
                allowed: isAllowed ? 1 : 0,
            },
            dataType: 'json',
            success: function (response) {
                $checkbox.prop('disabled', false);
                $spinner.addClass('d-none');
                $row.removeClass('tree-row-processing');

                if (response && response.status) {
                    updateRowVisual($row, isAllowed);

                    // Cascade Down jika yang ditoggle adalah Tim Kerja (Level 0)
                    if (nodeType === 'timkerja') {
                        const childRows = $(`#treeTableBody tr[data-parent-id="${nodeId}"]`);
                        childRows.each(function () {
                            const $childCheckbox = $(this).find('.perm-switch');
                            $childCheckbox.prop('checked', isAllowed);
                            updateRowVisual($(this), isAllowed);
                        });
                    }

                    // Cascade Up jika yang ditoggle adalah Layanan (Level 1)
                    if (nodeType === 'service' && parentId) {
                        const totalSiblingCount = $(`#treeTableBody tr[data-parent-id="${parentId}"]`).length;
                        const activeSiblingCount = $(`#treeTableBody tr[data-parent-id="${parentId}"] .perm-switch:checked`).length;
                        const $parentRow = $(`#tree-row-${parentId}`);
                        const $parentCheckbox = $parentRow.find('.perm-switch');

                        if (activeSiblingCount > 0) {
                            $parentCheckbox.prop('checked', true);
                            updateRowVisual($parentRow, true);
                        } else {
                            $parentCheckbox.prop('checked', false);
                            updateRowVisual($parentRow, false);
                        }
                    }

                    updateActiveServicesCount();

                    if (typeof notifySuccess === 'function') {
                        notifySuccess(response.message || 'Izin akses layanan berhasil diperbarui');
                    }
                } else {
                    $checkbox.prop('checked', previousState);
                    updateRowVisual($row, previousState);
                    const errMsg = (response && response.message) ? response.message : 'Gagal memperbarui izin';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                $checkbox.prop('disabled', false);
                $spinner.addClass('d-none');
                $row.removeClass('tree-row-processing');
                $checkbox.prop('checked', previousState);
                updateRowVisual($row, previousState);

                let errMsg = 'Terjadi kesalahan sistem saat menyimpan izin.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    });

    function updateRowVisual($row, isChecked) {
        const $label = $row.find('.status-label');
        if (isChecked) {
            $label.text('Diizinkan').removeClass('text-muted').addClass('text-success');
        } else {
            $label.text('Ditolak').removeClass('text-success').addClass('text-muted');
        }
    }

    // 5. Load Tree Mapping untuk Pegawai
    function loadServiceTree(nip) {
        if (!nip || isTreeLoading) return;
        isTreeLoading = true;

        $('#treeTableBody').empty();
        $('#treeSkeleton').removeClass('d-none');
        $('#treeEmptyState').addClass('d-none');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-service/tree',
            type: 'GET',
            data: { nip: nip },
            dataType: 'json',
            success: function (response) {
                $('#treeSkeleton').addClass('d-none');
                isTreeLoading = false;

                if (response && response.status && response.data) {
                    currentPegawaiData = response.data.detail;
                    currentTreeData = response.data.tree || [];

                    updatePegawaiBanner(currentPegawaiData);
                    renderTreeTable(currentTreeData);
                } else {
                    $('#treeTableBody').html(`
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle me-2"></i> Gagal memuat struktur layanan pegawai.
                            </td>
                        </tr>
                    `);
                }
            },
            error: function () {
                $('#treeSkeleton').addClass('d-none');
                isTreeLoading = false;
                $('#treeTableBody').html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger py-4">
                            <i class="bi bi-exclamation-triangle me-2"></i> Terjadi kesalahan koneksi saat memuat data.
                        </td>
                    </tr>
                `);
            }
        });
    }

    // 6. Update Pegawai Summary Banner
    function updatePegawaiBanner(detail) {
        if (!detail) return;

        $('#cardPegawaiName').text(detail.nama || 'Pegawai');
        $('#cardPegawaiNip').text(detail.nip || '-');
        $('#cardUnitKerjaBadge').text(detail.unit_kerja_nama || '-');
        $('#cardPegawaiRole').text(detail.role_name || 'User');
        $('#statActiveServices').text(detail.total_allowed_services || 0);
        $('#statTotalServices').text(`/ ${detail.total_active_services || 0} Modul`);
        $('#statActiveTimKerja').text(detail.total_allowed_timkerja || 0);
        $('#statTotalTimKerja').text(`/ ${detail.total_timkerja || 0} Tim`);

        $('#copyTargetName').text(detail.nama || '-');
        $('#copyTargetNip').text(detail.nip || '-');
    }

    // 7. Render Tree Table
    function renderTreeTable(treeNodes) {
        const $tbody = $('#treeTableBody');
        $tbody.empty();

        if (!treeNodes || treeNodes.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Belum ada modul layanan yang terdaftar di sistem.
                    </td>
                </tr>
            `);
            $('#statActiveServices').text('0');
            return;
        }

        let totalAllowedServices = 0;
        let totalServices = 0;

        treeNodes.forEach((parent) => {
            const children = parent.children || [];
            const hasChildren = children.length > 0;
            const parentRowHtml = buildRowHtml(parent, false, [], hasChildren);
            $tbody.append(parentRowHtml);

            if (hasChildren) {
                children.forEach((child, cIdx) => {
                    totalServices++;
                    if (child.allowed) {
                        totalAllowedServices++;
                    }
                    const isLastChild = cIdx === children.length - 1;
                    const childRowHtml = buildRowHtml(child, isLastChild, [parent.id], false);
                    $tbody.append(childRowHtml);
                });
            }
        });

        $('#statActiveServices').text(totalAllowedServices);

        applyTreeVisibility();
    }

    // 8. Build HTML Row untuk Tree
    function buildRowHtml(node, isLast, ancestors, hasChildren) {
        const level = node.level || 0;
        const levelClass = `tree-row-level-${Math.min(level, 1)}`;
        const ancestorsStr = ancestors.join(' ');
        const isCollapsed = collapsedNodes.has(node.id);

        let indentHtml = '';
        if (level > 0) {
            for (let i = 0; i < level; i++) {
                indentHtml += `<span class="tree-indent"></span>`;
            }
        }

        let nodeIconHtml = '';
        if (level === 0) {
            let toggleHtml = '';
            if (hasChildren) {
                toggleHtml = `
                    <button type="button" class="btn-tree-toggle me-1 ${isCollapsed ? 'collapsed' : ''}" data-node-id="${node.id}" title="${isCollapsed ? 'Buka Tim Kerja' : 'Tutup Tim Kerja'}">
                        <i class="bi bi-chevron-down tree-chevron"></i>
                    </button>
                `;
            } else {
                toggleHtml = `<span class="tree-toggle-spacer me-1"></span>`;
            }

            const iconHtml = node.icon
                ? `<i class="${escapeHtml(node.icon)} tree-icon icon-timkerja"></i>`
                : `<i class="bi bi-folder2-open tree-icon icon-timkerja"></i>`;

            nodeIconHtml = `${toggleHtml}${iconHtml}`;
        } else {
            nodeIconHtml = `<i class="bi bi-arrow-return-right tree-branch-icon"></i>`;
        }

        let badgeHtml = '';
        if (level === 0) {
            badgeHtml = `<span class="badge badge-level level-timkerja">Tim Kerja</span>`;
        } else {
            badgeHtml = `<span class="badge badge-level level-service">Layanan</span>`;
        }

        const urlDisplay = node.url && node.url !== '#' ? node.url : (level === 0 ? 'Grup Tim Kerja' : '-');
        const isChecked = node.allowed === true;
        const clickableClass = hasChildren ? 'clickable-title' : '';

        return `
            <tr id="tree-row-${node.id}" class="${levelClass}" data-id="${node.id}" data-raw-id="${node.raw_id}" data-type="${node.type}" data-parent-id="${node.parent_id || ''}" data-ancestors="${ancestorsStr}" data-has-children="${hasChildren ? '1' : '0'}" data-name="${escapeHtml(node.name.toLowerCase())}" data-url="${escapeHtml((node.url || '').toLowerCase())}">
                <td>
                    <div class="tree-node-wrapper">
                        ${indentHtml}
                        ${nodeIconHtml}
                        <span class="tree-title fw-bold ${clickableClass}" ${hasChildren ? `data-node-id="${node.id}"` : ''} style="color: #0f172a;">${escapeHtml(node.name)}</span>
                        ${(node.alias) ? `<small class="ms-2" style="color: #64748b; font-weight: 500;">(${escapeHtml(node.alias)})</small>` : ''}
                    </div>
                </td>
                <td class="text-center">${badgeHtml}</td>
                <td>
                    <code class="url-code">${escapeHtml(urlDisplay)}</code>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <div class="form-check form-switch custom-service-switch mb-0">
                            <input class="form-check-input perm-switch" type="checkbox" role="switch" id="perm_${node.id}" data-id="${node.id}" data-raw-id="${node.raw_id}" data-type="${node.type}" data-parent-id="${node.parent_id || ''}" data-level="${level}" ${isChecked ? 'checked' : ''}>
                        </div>
                        <span class="spinner-border spinner-border-sm text-primary perm-spinner d-none" role="status" aria-hidden="true"></span>
                        <span class="status-label fw-semibold ${isChecked ? 'text-success' : 'text-muted'}">${isChecked ? 'Diizinkan' : 'Ditolak'}</span>
                    </div>
                </td>
            </tr>
        `;
    }

    // 9. Update Active Services Count
    function updateActiveServicesCount() {
        const total = $('#treeTableBody tr[data-type="service"] .perm-switch').length;
        const active = $('#treeTableBody tr[data-type="service"] .perm-switch:checked').length;
        $('#statActiveServices').text(active);
        $('#statTotalServices').text(`/ ${total} Modul`);
    }

    // 10. Live Search Filter Table Function
    function filterTreeTable(query) {
        if (!query) {
            applyTreeVisibility();
            $('#treeEmptyState').addClass('d-none');
            return;
        }

        let visibleCount = 0;
        const matchedIds = new Set();

        $('#treeTableBody tr').each(function () {
            const name = $(this).data('name') || '';
            const url = $(this).data('url') || '';
            const id = String($(this).data('id') || '');
            const ancestors = String($(this).data('ancestors') || '').split(' ').filter(Boolean);

            if (name.includes(query) || url.includes(query)) {
                matchedIds.add(id);
                ancestors.forEach((ancId) => matchedIds.add(ancId));
            }
        });

        $('#treeTableBody tr').each(function () {
            const id = String($(this).data('id') || '');
            const parentId = String($(this).data('parent-id') || '');

            if (matchedIds.has(id) || (parentId && matchedIds.has(parentId))) {
                $(this).removeClass('d-none');
                visibleCount++;
            } else {
                $(this).addClass('d-none');
            }
        });

        if (visibleCount === 0) {
            $('#treeEmptyState').removeClass('d-none');
        } else {
            $('#treeEmptyState').addClass('d-none');
        }
    }

    // 11. Modal Daftar Pegawai (Load & Search)
    $('#btnOpenPegawaiModal').on('click', function () {
        $('#searchPegawaiModal').val('');
        loadPegawaiListModal('');
        $('#modalPegawaiList').modal('show');
    });

    let pegawaiSearchTimer = null;
    $('#searchPegawaiModal').on('input', function () {
        clearTimeout(pegawaiSearchTimer);
        const search = $(this).val().trim();
        pegawaiSearchTimer = setTimeout(() => {
            loadPegawaiListModal(search);
        }, 200);
    });

    function loadPegawaiListModal(search = '') {
        const $container = $('#pegawaiListContainer');
        $container.html(`
            <div class="text-center py-4">
                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                <span class="ms-2 text-muted small">Memuat daftar pegawai...</span>
            </div>
        `);

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-service/pegawai-list',
            type: 'GET',
            data: { search: search },
            dataType: 'json',
            success: function (response) {
                if (response && response.status && Array.isArray(response.data)) {
                    renderPegawaiListModal(response.data);
                } else {
                    $container.html('<p class="text-muted text-center py-3">Tidak ada data pegawai.</p>');
                }
            },
            error: function () {
                $container.html('<p class="text-danger text-center py-3">Gagal memuat data pegawai.</p>');
            }
        });
    }

    function renderPegawaiListModal(list) {
        const $container = $('#pegawaiListContainer');
        $container.empty();

        if (list.length === 0) {
            $container.html('<p class="text-muted text-center py-4">Tidak ada data pegawai yang cocok.</p>');
            return;
        }

        list.forEach((p) => {
            const initial = (p.nama || 'P').charAt(0).toUpperCase();
            const isSelected = p.nip === currentNip;
            const itemHtml = `
                <div class="pegawai-list-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pegawai-avatar-circle">${escapeHtml(initial)}</div>
                        <div>
                            <h6 class="mb-0 fw-bold" style="color: #0f172a; font-size: 0.95rem;">${escapeHtml(p.nama)}</h6>
                            <p class="small mb-0" style="color: #475569;">
                                NIP: <span class="fw-bold" style="color: #0f172a;">${escapeHtml(p.nip)}</span> &bull; 
                                <span>${escapeHtml(p.unit_kerja_nama || '-')}</span>
                            </p>
                        </div>
                    </div>
                    <div>
                        ${
                            isSelected
                                ? `<span class="badge bg-success-subtle text-success px-3 py-2 fw-bold" style="border: 1px solid #bbf7d0; border-radius: 6px;">Dipilih</span>`
                                : `<button type="button" class="btn btn-outline-primary btn-sm px-3 fw-bold btn-select-pegawai" data-nip="${escapeHtml(p.nip)}" data-nama="${escapeHtml(p.nama)}" data-unit="${escapeHtml(p.unit_kerja_nama || '-')}" style="border-radius: 6px;">Pilih</button>`
                        }
                    </div>
                </div>
            `;
            $container.append(itemHtml);
        });
    }

    $(document).on('click', '.btn-select-pegawai', function () {
        const nip = $(this).data('nip');
        const nama = $(this).data('nama') || nip;
        const unit = $(this).data('unit') || '-';
        if (nip) {
            const $select = $('#selectPegawai');
            if ($select.find("option[value='" + nip + "']").length) {
                $select.val(nip).trigger('change');
            } else {
                const newOption = new Option(nama + ' (' + nip + ') - ' + unit, nip, true, true);
                $select.append(newOption).trigger('change');
            }
            $('#modalPegawaiList').modal('hide');
        }
    });

    // 12. Reset Permission to Default
    $('#btnResetDefault').on('click', function () {
        if (!currentNip) return;

        const employeeName = currentPegawaiData ? currentPegawaiData.nama : currentNip;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Reset Izin ke Default?',
                text: `Apakah Anda yakin ingin mereset seluruh izin akses layanan untuk ${employeeName} kembali ke standar unit kerjanya?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#1040c1',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Reset Izin',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    performResetDefault();
                }
            });
        } else {
            if (confirm(`Reset seluruh izin akses layanan untuk ${employeeName} kembali ke standar unit kerjanya?`)) {
                performResetDefault();
            }
        }
    });

    function performResetDefault() {
        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-service/reset-default',
            type: 'POST',
            data: { nip: currentNip },
            dataType: 'json',
            success: function (response) {
                if (response && response.status) {
                    if (typeof notifySuccess === 'function') {
                        notifySuccess(response.message || 'Izin akses berhasil direset ke default');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('Berhasil', response.message, 'success');
                    }
                    loadServiceTree(currentNip);
                } else {
                    const errMsg = (response && response.message) ? response.message : 'Gagal mereset izin';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                let errMsg = 'Terjadi kesalahan sistem saat mereset izin.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    }

    // 13. Modal Salin Izin
    $('#btnOpenCopyModal').on('click', function () {
        if (!currentNip) return;
        if (currentPegawaiData) {
            $('#copyTargetName').text(currentPegawaiData.nama || '-');
            $('#copyTargetNip').text(currentPegawaiData.nip || '-');
        }
        $('#modalCopyPermission').modal('show');
    });

    $('#modalCopyPermission').on('shown.bs.modal', function () {
        const $sourceSelect = $('#selectSourcePegawai');
        $sourceSelect.val(null).trigger('change');
    });

    $('#btnConfirmCopy').on('click', function () {
        $('#formCopyPermission').trigger('submit');
    });

    $('#formCopyPermission').on('submit', function (e) {
        e.preventDefault();
        const sourceNip = $('#selectSourcePegawai').val();
        if (!sourceNip || !currentNip) {
            if (typeof notifyWarning === 'function') {
                notifyWarning('Silakan pilih pegawai sumber terlebih dahulu.');
            }
            return;
        }

        if (sourceNip === currentNip) {
            if (typeof notifyWarning === 'function') {
                notifyWarning('Pegawai sumber dan tujuan tidak boleh sama.');
            }
            return;
        }

        const $btn = $('#btnConfirmCopy');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyalin...');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-service/copy-permission',
            type: 'POST',
            data: {
                source_nip: sourceNip,
                target_nip: currentNip,
            },
            dataType: 'json',
            success: function (response) {
                $btn.prop('disabled', false).text('Salin Izin Sekarang');
                if (response && response.status) {
                    $('#modalCopyPermission').modal('hide');
                    if (typeof notifySuccess === 'function') {
                        notifySuccess(response.message || 'Izin layanan berhasil disalin');
                    } else if (typeof Swal !== 'undefined') {
                        Swal.fire('Berhasil', response.message, 'success');
                    }
                    loadServiceTree(currentNip);
                } else {
                    const errMsg = (response && response.message) ? response.message : 'Gagal menyalin izin';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Salin Izin Sekarang');
                let errMsg = 'Terjadi kesalahan sistem saat menyalin izin.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    });

    function escapeHtml(string) {
        if (!string) return '';
        const entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;'
        };
        return String(string).replace(/[&<>"'\/]/g, function (s) {
            return entityMap[s];
        });
    }
});
