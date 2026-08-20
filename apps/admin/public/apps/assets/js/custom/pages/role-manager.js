/**
 * Role Manager Page JavaScript
 * Handles Role Dropdown, Tree Mapping Table, Expand/Collapse Arrows, AJAX Checkbox Toggles, Live Filter & Modals
 */

$(document).ready(function () {
    let currentRoleId = parseInt($('#selectRole').val()) || 0;
    let currentRoleData = null;
    let currentTreeData = [];
    let isTreeLoading = false;
    const collapsedNodes = new Set();

    // Inisialisasi awal: load tree role pertama
    if (currentRoleId > 0) {
        loadRoleTree(currentRoleId);
    }

    // 1. Event Role Dropdown Change
    $('#selectRole').on('change', function () {
        const roleId = parseInt($(this).val()) || 0;
        if (roleId > 0 && roleId !== currentRoleId) {
            currentRoleId = roleId;
            $('#searchMenu').val('');
            collapsedNodes.clear();
            loadRoleTree(currentRoleId);
        }
    });

    // 2. Event Live Search Filter pada Menu Tree
    let searchTimer = null;
    $('#searchMenu').on('input', function () {
        clearTimeout(searchTimer);
        const query = $(this).val().toLowerCase().trim();
        searchTimer = setTimeout(() => {
            filterTreeTable(query);
        }, 150);
    });

    $('#btnResetSearchRole').on('click', function () {
        $('#searchMenu').val('').trigger('input');
    });


    // 3. Event Expand/Collapse Submenu Arrow
    $(document).on('click', '.btn-tree-toggle, .tree-title.clickable-title', function (e) {
        e.stopPropagation();
        const nodeId = parseInt($(this).data('node-id')) || 0;
        if (nodeId <= 0) return;
        toggleNodeCollapse(nodeId);
    });

    // 4. Event Toggle All Nodes (Buka/Tutup Semua)
    $('#btnToggleAllNodes').on('click', function () {
        const allParentIds = [];
        $('#treeTableBody tr[data-has-children="1"]').each(function () {
            const id = parseInt($(this).data('id')) || 0;
            if (id > 0) allParentIds.push(id);
        });

        if (allParentIds.length === 0) return;

        if (collapsedNodes.size === 0) {
            // Tutup semua parent
            allParentIds.forEach((id) => collapsedNodes.add(id));
            $('.btn-tree-toggle').addClass('collapsed');
        } else {
            // Buka semua parent
            collapsedNodes.clear();
            $('.btn-tree-toggle').removeClass('collapsed');
        }

        applyTreeVisibility();
    });

    function toggleNodeCollapse(nodeId) {
        const $btn = $(`.btn-tree-toggle[data-node-id="${nodeId}"]`);
        if (collapsedNodes.has(nodeId)) {
            collapsedNodes.delete(nodeId);
            $btn.removeClass('collapsed');
            $btn.attr('title', 'Tutup Submenu');
        } else {
            collapsedNodes.add(nodeId);
            $btn.addClass('collapsed');
            $btn.attr('title', 'Buka Submenu');
        }

        applyTreeVisibility();
    }

    function applyTreeVisibility() {
        const searchQuery = $('#searchMenu').val().toLowerCase().trim();
        if (searchQuery !== '') {
            // Jika dalam mode search, jangan override visibility search
            return;
        }

        $('#treeTableBody tr').each(function () {
            const ancestors = String($(this).data('ancestors') || '').split(' ').filter(Boolean);
            const isHiddenByAncestor = ancestors.some((ancId) => collapsedNodes.has(parseInt(ancId)));

            if (isHiddenByAncestor) {
                $(this).addClass('d-none');
            } else {
                $(this).removeClass('d-none');
            }
        });

        // Update Button Tutup/Buka Semua state
        if (collapsedNodes.size === 0) {
            $('#lblToggleAllNodes').text('Tutup Semua');
            $('#iconToggleAllNodes').removeClass('bi-chevron-bar-expand').addClass('bi-chevron-bar-contract');
        } else {
            $('#lblToggleAllNodes').text('Buka Semua');
            $('#iconToggleAllNodes').removeClass('bi-chevron-bar-contract').addClass('bi-chevron-bar-expand');
        }
    }

    // 5. Event Checkbox Toggle Permission (AJAX with Rollback & Loading Spinner)
    $(document).on('change', '.perm-switch', function (e) {
        const $checkbox = $(this);
        const menuId = parseInt($checkbox.data('menu-id')) || 0;
        const isAllowed = $checkbox.is(':checked');
        const previousState = !isAllowed;
        const $row = $checkbox.closest('tr');
        const $spinner = $row.find('.perm-spinner');
        const $label = $row.find('.status-label');

        if (menuId <= 0 || currentRoleId <= 0) {
            $checkbox.prop('checked', previousState);
            return;
        }

        // Loading State: disable checkbox, tampilkan spinner & highlight row
        $checkbox.prop('disabled', true);
        $spinner.removeClass('d-none');
        $row.addClass('tree-row-processing');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/toggle',
            type: 'POST',
            data: {
                role_id: currentRoleId,
                menu_id: menuId,
                allowed: isAllowed ? 1 : 0,
                cascade: 1,
            },
            dataType: 'json',
            success: function (response) {
                $checkbox.prop('disabled', false);
                $spinner.addClass('d-none');
                $row.removeClass('tree-row-processing');

                if (response && response.status) {
                    // Success State
                    updateRowVisual($row, isAllowed);

                    // Cascade Visual Update untuk affected IDs
                    if (response.data && Array.isArray(response.data.affected_ids)) {
                        response.data.affected_ids.forEach((affId) => {
                            if (affId !== menuId) {
                                const $affRow = $(`#tree-row-${affId}`);
                                if ($affRow.length > 0) {
                                    const $affCheck = $affRow.find('.perm-switch');
                                    $affCheck.prop('checked', isAllowed);
                                    updateRowVisual($affRow, isAllowed);
                                }
                            }
                        });
                    }

                    // Perbarui counter menu aktif pada banner
                    updateActiveMenusCount();

                    // Notifikasi sukses
                    if (typeof notifySuccess === 'function') {
                        notifySuccess(response.message || 'Hak akses berhasil diperbarui');
                    }
                } else {
                    // Rollback State
                    $checkbox.prop('checked', previousState);
                    updateRowVisual($row, previousState);
                    const errMsg = (response && response.message) ? response.message : 'Gagal memperbarui hak akses';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                // Rollback State saat AJAX error
                $checkbox.prop('disabled', false);
                $spinner.addClass('d-none');
                $row.removeClass('tree-row-processing');
                $checkbox.prop('checked', previousState);
                updateRowVisual($row, previousState);

                let errMsg = 'Terjadi kesalahan sistem saat memperbarui hak akses';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    });

    // 6. Helper Update Tampilan Visual Row saat Toggle
    function updateRowVisual($row, isChecked) {
        const $label = $row.find('.status-label');
        if (isChecked) {
            $label.text('Diizinkan').removeClass('text-muted').addClass('text-success');
        } else {
            $label.text('Ditolak').removeClass('text-success').addClass('text-muted');
        }
    }

    // 7. Fungsi Load Tree Mapping untuk Role
    function loadRoleTree(roleId) {
        if (roleId <= 0 || isTreeLoading) return;
        isTreeLoading = true;

        $('#treeTableBody').empty();
        $('#treeSkeleton').removeClass('d-none');
        $('#treeEmptyState').addClass('d-none');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/tree',
            type: 'GET',
            data: { role_id: roleId },
            dataType: 'json',
            success: function (response) {
                $('#treeSkeleton').addClass('d-none');
                isTreeLoading = false;

                if (response && response.status && response.data) {
                    currentRoleData = response.data.role;
                    currentTreeData = response.data.tree || [];

                    updateRoleBanner(currentRoleData);
                    renderTreeTable(currentTreeData);
                } else {
                    $('#treeTableBody').html(`
                        <tr>
                            <td colspan="4" class="text-center text-danger py-4">
                                <i class="bi bi-exclamation-triangle me-2"></i> Gagal memuat mapping menu role.
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
                            <i class="bi bi-exclamation-triangle me-2"></i> Terjadi kesalahan saat memuat data.
                        </td>
                    </tr>
                `);
            }
        });
    }

    // 8. Update Role Summary Banner
    function updateRoleBanner(role) {
        if (!role) return;

        $('#cardRoleName').text(role.role_name || 'Role');
        $('#cardRoleDescription').text(role.description || 'Tidak ada deskripsi.');
        $('#statTotalUsers').text(role.total_users || 0);

        // Tombol delete hanya untuk custom role (bukan ADM atau USR)
        const isSystemRole = ['ADM', 'USR'].includes((role.role_code || '').toUpperCase());
        if (isSystemRole) {
            $('#btnDeleteRole').addClass('d-none');
        } else {
            $('#btnDeleteRole').removeClass('d-none');
        }

        $('#modalUsersRoleName').text(`${role.role_name} (${role.role_code})`);
    }

    // 9. Render Tree Table secara Hirarkis
    function renderTreeTable(treeNodes) {
        const $tbody = $('#treeTableBody');
        $tbody.empty();

        if (!treeNodes || treeNodes.length === 0) {
            $tbody.html(`
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        Belum ada data menu terdaftar di sistem.
                    </td>
                </tr>
            `);
            $('#statActiveMenus').text('0');
            $('#statTotalMenus').text('/ 0');
            return;
        }

        let totalAllowed = 0;
        let totalItems = 0;

        function traverse(nodes, parentId = null, ancestorIds = []) {
            const count = nodes.length;
            nodes.forEach((node, index) => {
                totalItems++;
                if (node.allowed) {
                    totalAllowed++;
                }

                const isLast = (index === count - 1);
                const currentAncestors = [...ancestorIds];
                if (parentId !== null) {
                    currentAncestors.push(parentId);
                }

                const rowHtml = buildRowHtml(node, isLast, currentAncestors);
                $tbody.append(rowHtml);

                if (node.children && node.children.length > 0) {
                    traverse(node.children, node.id, currentAncestors);
                }
            });
        }

        traverse(treeNodes);

        $('#statActiveMenus').text(totalAllowed);
        $('#statTotalMenus').text(`/ ${totalItems}`);

        applyTreeVisibility();
    }

    // 10. Build HTML Row untuk Setiap Item Tree (Tanpa Nomor Kolom, Submenu seragam dengan Icon Garis Panah)
    function buildRowHtml(node, isLast, ancestors) {
        const level = node.level || 0;
        const levelClass = `tree-row-level-${Math.min(level, 2)}`;
        const ancestorsStr = ancestors.join(' ');
        const hasChildren = node.children && node.children.length > 0;
        const isCollapsed = collapsedNodes.has(node.id);

        // Tree Indentation
        let indentHtml = '';
        if (level > 0) {
            for (let i = 0; i < level; i++) {
                indentHtml += `<span class="tree-indent"></span>`;
            }
        }

        // Icon & Toggle Structure
        let nodeIconHtml = '';
        if (level === 0) {
            // Menu Utama (Level 0)
            let toggleHtml = '';
            if (hasChildren) {
                toggleHtml = `
                    <button type="button" class="btn-tree-toggle me-1 ${isCollapsed ? 'collapsed' : ''}" data-node-id="${node.id}" title="${isCollapsed ? 'Buka Submenu' : 'Tutup Submenu'}">
                        <i class="bi bi-chevron-down tree-chevron"></i>
                    </button>
                `;
            } else {
                toggleHtml = `<span class="tree-toggle-spacer me-1"></span>`;
            }

            const iconClass = 'icon-main';
            const iconHtml = node.icon
                ? `<i class="${escapeHtml(node.icon)} tree-icon ${iconClass}"></i>`
                : `<i class="bi bi-folder2-open tree-icon ${iconClass}"></i>`;

            nodeIconHtml = `${toggleHtml}${iconHtml}`;
        } else {
            // Submenu / Child Turunan (Level >= 1)
            // Seluruh submenu seragam menggunakan icon garis panah turunan (↳)
            if (hasChildren) {
                nodeIconHtml = `
                    <button type="button" class="btn-tree-toggle me-1 ${isCollapsed ? 'collapsed' : ''}" data-node-id="${node.id}" title="${isCollapsed ? 'Buka Submenu' : 'Tutup Submenu'}">
                        <i class="bi bi-chevron-down tree-chevron"></i>
                    </button>
                    <i class="bi bi-arrow-return-right tree-branch-icon"></i>
                `;
            } else {
                nodeIconHtml = `<i class="bi bi-arrow-return-right tree-branch-icon"></i>`;
            }
        }

        // Badge Level
        let badgeHtml = '';
        if (level === 0) {
            badgeHtml = `<span class="badge badge-level level-main">Menu Utama</span>`;
        } else if (level === 1) {
            badgeHtml = `<span class="badge badge-level level-sub">Submenu</span>`;
        } else {
            badgeHtml = `<span class="badge badge-level level-child">Child Submenu</span>`;
        }

        // Route URL Code
        const urlDisplay = node.url && node.url !== '#' ? node.url : (node.url === '#' ? 'Group Header' : '-');
        const isChecked = node.allowed === true;
        const clickableClass = hasChildren ? 'clickable-title' : '';

        return `
            <tr id="tree-row-${node.id}" class="${levelClass}" data-id="${node.id}" data-parent-id="${node.parent_id || ''}" data-ancestors="${ancestorsStr}" data-has-children="${hasChildren ? '1' : '0'}" data-name="${escapeHtml(node.name.toLowerCase())}" data-url="${escapeHtml((node.url || '').toLowerCase())}">
                <td>
                    <div class="tree-node-wrapper">
                        ${indentHtml}
                        ${nodeIconHtml}
                        <span class="tree-title fw-bold ${clickableClass}" ${hasChildren ? `data-node-id="${node.id}"` : ''}>${escapeHtml(node.name)}</span>
                    </div>
                </td>
                <td class="text-center">${badgeHtml}</td>
                <td>
                    <code class="url-code">${escapeHtml(urlDisplay)}</code>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <div class="form-check form-switch custom-role-switch mb-0">
                            <input class="form-check-input perm-switch" type="checkbox" role="switch" id="perm_${node.id}" data-menu-id="${node.id}" data-parent-id="${node.parent_id || ''}" data-level="${level}" ${isChecked ? 'checked' : ''}>
                        </div>
                        <span class="spinner-border spinner-border-sm text-primary perm-spinner d-none" role="status" aria-hidden="true"></span>
                        <span class="status-label fw-semibold ${isChecked ? 'text-success' : 'text-muted'}">${isChecked ? 'Diizinkan' : 'Ditolak'}</span>
                    </div>
                </td>
            </tr>
        `;
    }

    // 11. Update Active Menus Count pada Banner
    function updateActiveMenusCount() {
        const total = $('#treeTableBody .perm-switch').length;
        const active = $('#treeTableBody .perm-switch:checked').length;
        $('#statActiveMenus').text(active);
        $('#statTotalMenus').text(`/ ${total}`);
    }

    // 12. Live Search Filter Table Function
    function filterTreeTable(query) {
        if (!query) {
            applyTreeVisibility();
            $('#treeEmptyState').addClass('d-none');
            return;
        }

        let visibleCount = 0;
        const matchedIds = new Set();

        // 1. Cari row yang cocok
        $('#treeTableBody tr').each(function () {
            const name = $(this).data('name') || '';
            const url = $(this).data('url') || '';
            const id = parseInt($(this).data('id')) || 0;
            const ancestors = String($(this).data('ancestors') || '').split(' ').filter(Boolean);

            if (name.includes(query) || url.includes(query)) {
                matchedIds.add(id);
                ancestors.forEach((ancId) => matchedIds.add(parseInt(ancId)));
            }
        });

        // 2. Tampilkan yang cocok dan ancestor-nya
        $('#treeTableBody tr').each(function () {
            const id = parseInt($(this).data('id')) || 0;
            if (matchedIds.has(id)) {
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

    // 13. Modal Tambah Role Baru - Simpan Role
    $('#btnSaveRole').on('click', function () {
        const form = $('#formAddRole')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const roleCode = $('#inputRoleCode').val().toUpperCase().trim();
        const roleName = $('#inputRoleName').val().trim();
        const description = $('#inputRoleDesc').val().trim();
        const copyFromRoleId = $('#selectCopyRole').val();

        if (typeof swlwaitProsessing === 'function') {
            swlwaitProsessing();
        }

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/create-role',
            type: 'POST',
            data: {
                role_code: roleCode,
                role_name: roleName,
                description: description,
                copy_from_role_id: copyFromRoleId,
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.status && response.data) {
                    const newRole = response.data;
                    $('#modalAddRole').modal('hide');
                    form.reset();

                    // Tambahkan option baru ke dropdown selectRole
                    const newOption = `<option value="${newRole.id}" data-code="${escapeHtml(newRole.role_code)}" selected>Role: ${escapeHtml(newRole.role_name)} (${escapeHtml(newRole.role_code)})</option>`;
                    $('#selectRole').append(newOption);
                    $('#selectCopyRole').append(`<option value="${newRole.id}">${escapeHtml(newRole.role_name)} (${escapeHtml(newRole.role_code)})</option>`);

                    // Pilih role baru dan muat tree
                    currentRoleId = newRole.id;
                    $('#selectRole').val(currentRoleId);
                    loadRoleTree(currentRoleId);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Role baru berhasil ditambahkan dan siap dikonfigurasi.',
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    } else if (typeof notifySuccess === 'function') {
                        notifySuccess('Role baru berhasil ditambahkan');
                    }
                } else {
                    const errMsg = (response && response.message) ? response.message : 'Gagal menambahkan role';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                let errMsg = 'Gagal menambahkan role baru';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    });

    // 14. Modal Edit Role
    $('#btnEditRole').on('click', function () {
        if (!currentRoleData) return;

        $('#editRoleId').val(currentRoleData.id);
        $('#editRoleName').val(currentRoleData.role_name);
        $('#editRoleDesc').val(currentRoleData.description || '');
        $('#editRoleActive').prop('checked', currentRoleData.is_active);

        $('#modalEditRole').modal('show');
    });

    $('#btnUpdateRole').on('click', function () {
        const form = $('#formEditRole')[0];
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const roleId = $('#editRoleId').val();
        const roleName = $('#editRoleName').val().trim();
        const description = $('#editRoleDesc').val().trim();
        const isActive = $('#editRoleActive').is(':checked') ? 1 : 0;

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/update-role',
            type: 'POST',
            data: {
                role_id: roleId,
                role_name: roleName,
                description: description,
                is_active: isActive,
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.status && response.data) {
                    $('#modalEditRole').modal('hide');
                    currentRoleData = response.data;
                    updateRoleBanner(currentRoleData);

                    // Update nama role di dropdown
                    $(`#selectRole option[value="${roleId}"]`).text(`Role: ${currentRoleData.role_name} (${currentRoleData.role_code})`);

                    if (typeof notifySuccess === 'function') {
                        notifySuccess('Informasi role berhasil diperbarui');
                    }
                } else {
                    const errMsg = (response && response.message) ? response.message : 'Gagal memperbarui role';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                let errMsg = 'Gagal memperbarui role';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    });

    // 15. Hapus Role Custom
    $('#btnDeleteRole').on('click', function () {
        if (!currentRoleData || ['ADM', 'USR'].includes((currentRoleData.role_code || '').toUpperCase())) {
            return;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `Hapus Role "${currentRoleData.role_name}"?`,
                text: 'Role yang dihapus tidak dapat dikembalikan dan seluruh hak aksesnya akan dibersihkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Role',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteRoleAjax(currentRoleData.id);
                }
            });
        } else {
            if (confirm(`Yakin ingin menghapus role "${currentRoleData.role_name}"?`)) {
                deleteRoleAjax(currentRoleData.id);
            }
        }
    });

    function deleteRoleAjax(roleId) {
        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/delete-role',
            type: 'POST',
            data: { role_id: roleId },
            dataType: 'json',
            success: function (response) {
                if (response && response.status) {
                    // Hapus option dari dropdown
                    $(`#selectRole option[value="${roleId}"]`).remove();
                    $(`#selectCopyRole option[value="${roleId}"]`).remove();

                    // Switch ke role pertama (ADM)
                    const firstRoleId = parseInt($('#selectRole option:first').val()) || 0;
                    currentRoleId = firstRoleId;
                    $('#selectRole').val(currentRoleId);
                    loadRoleTree(currentRoleId);

                    if (typeof notifySuccess === 'function') {
                        notifySuccess(response.message || 'Role berhasil dihapus');
                    }
                } else {
                    const errMsg = (response && response.message) ? response.message : 'Gagal menghapus role';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                let errMsg = 'Gagal menghapus role';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    }

    // 16. Manage Users Modal (Daftar User & Assign User)
    $('#btnOpenManageUsers').on('click', function () {
        if (currentRoleId <= 0) return;
        $('#panelAssignNewUser').addClass('d-none');
        $('#searchRoleUser').val('');
        $('#modalManageUsers').modal('show');
        loadRoleUsers(currentRoleId);
    });

    $('#btnToggleAssignNew').on('click', function () {
        $('#panelAssignNewUser').toggleClass('d-none');
    });

    let userSearchTimer = null;
    $('#searchRoleUser').on('input', function () {
        clearTimeout(userSearchTimer);
        const q = $(this).val().trim();
        userSearchTimer = setTimeout(() => {
            loadRoleUsers(currentRoleId, q);
        }, 200);
    });

    function loadRoleUsers(roleId, search = '') {
        const $container = $('#roleUsersListContainer');
        $container.html('<div class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Memuat pengguna...</div>');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/users',
            type: 'GET',
            data: { role_id: roleId, q: search },
            dataType: 'json',
            success: function (response) {
                if (response && response.status && response.data) {
                    const assigned = response.data.assigned_users || [];
                    const available = response.data.available_users || [];

                    renderAssignedUsers(assigned);
                    populateAvailableUsersSelect(available);
                } else {
                    $container.html('<div class="text-center py-3 text-muted">Belum ada data user.</div>');
                }
            },
            error: function () {
                $container.html('<div class="text-center py-3 text-danger">Gagal memuat daftar user.</div>');
            }
        });
    }

    function renderAssignedUsers(users) {
        const $container = $('#roleUsersListContainer');
        $container.empty();

        if (!users || users.length === 0) {
            $container.html(`
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-people mb-2 d-block fs-3"></i>
                    Belum ada pengguna yang memiliki role ini.
                </div>
            `);
            return;
        }

        users.forEach((user) => {
            const initials = getInitials(user.fullname || user.username || 'U');
            const nipDisplay = user.nip || user.username || '-';

            const itemHtml = `
                <div class="user-list-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar-circle">${escapeHtml(initials)}</div>
                        <div>
                            <div class="fw-bold text-dark" style="font-size: 0.92rem;">${escapeHtml(user.fullname || user.username)}</div>
                            <div class="text-muted small">
                                <span class="me-2"><i class="bi bi-person-badge me-1"></i>${escapeHtml(nipDisplay)}</span>
                                <span><i class="bi bi-envelope me-1"></i>${escapeHtml(user.email || '-')}</span>
                            </div>
                        </div>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">Role Aktif</span>
                </div>
            `;
            $container.append(itemHtml);
        });
    }

    function populateAvailableUsersSelect(availableUsers) {
        const $select = $('#selectAvailableUser');
        $select.empty().append('<option value="">Pilih pengguna dari role lain...</option>');

        if (!availableUsers || availableUsers.length === 0) return;

        availableUsers.forEach((u) => {
            const currentRole = u.current_role_name || u.role || 'Lainnya';
            $select.append(`<option value="${u.id}">${escapeHtml(u.fullname || u.username)} (${escapeHtml(u.username)}) - Role saat ini: ${escapeHtml(currentRole)}</option>`);
        });
    }

    // 17. Assign Pengguna ke Role Ini
    $('#btnConfirmAssignUser').on('click', function () {
        const userId = parseInt($('#selectAvailableUser').val()) || 0;
        if (userId <= 0 || currentRoleId <= 0) {
            if (typeof notifyError === 'function') {
                notifyError('Pilih pengguna terlebih dahulu');
            }
            return;
        }

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-role/assign-user',
            type: 'POST',
            data: {
                user_id: userId,
                role_id: currentRoleId,
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.status) {
                    if (typeof notifySuccess === 'function') {
                        notifySuccess(response.message || 'Pengguna berhasil dipindahkan ke role ini');
                    }
                    loadRoleUsers(currentRoleId);
                    // Update counter user di banner
                    if (currentRoleData) {
                        currentRoleData.total_users = (currentRoleData.total_users || 0) + 1;
                        $('#statTotalUsers').text(currentRoleData.total_users);
                    }
                } else {
                    const errMsg = (response && response.message) ? response.message : 'Gagal memindahkan pengguna';
                    if (typeof notifyError === 'function') {
                        notifyError(errMsg);
                    }
                }
            },
            error: function (xhr) {
                let errMsg = 'Gagal memindahkan pengguna';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errMsg = xhr.responseJSON.message;
                }
                if (typeof notifyError === 'function') {
                    notifyError(errMsg);
                }
            }
        });
    });

    // Helper Utility Functions
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, (m) => map[m]);
    }

    function getInitials(name) {
        if (!name) return 'U';
        const parts = name.trim().split(/\s+/);
        let res = '';
        for (let i = 0; i < Math.min(2, parts.length); i++) {
            if (parts[i].length > 0) {
                res += parts[i][0].toUpperCase();
            }
        }
        return res || 'U';
    }
});
