(function () {
    const userSelect = document.getElementById('rmUserSelect');
    const btnLoadTree = document.getElementById('rmLoadTree');
    const btnAddUser = document.getElementById('rmAddUserBtn');
    const treeBody = document.getElementById('rmTreeBody');
    const userModalEl = document.getElementById('rmUserModal');
    const userForm = document.getElementById('rmUserForm');
    const userSubmitBtn = document.getElementById('rmUserSubmitBtn');
    
    let userModal = null;
    let treeState = {
        parentMap: new Map(),
        childrenMap: new Map(),
        descendantsMap: new Map(),
        rowMap: new Map(),
    };
    let isTreeUpdating = false;

    if (!userSelect || !btnLoadTree || !treeBody || !userModalEl || !userForm) {
        return;
    }

    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
        userModal = bootstrap.Modal.getOrCreateInstance(userModalEl);
    }

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

    function isApiSuccess(json) {
        return !!(json && (json.success === true || json.status === true));
    }

    function getApiMessage(json, fallback) {
        return (json && json.message) ? json.message : fallback;
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

    async function fetchUsers(query) {
        const url = buildAppUrl('api/manage-role/users?q=' + encodeURIComponent(query || ''));
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) {
            throw new Error('Gagal memuat user');
        }
        const json = await res.json();
        return json.data || [];
    }

    function renderUsers(list, keepSelected, selectedId) {
        const old = userSelect.value;
        userSelect.innerHTML = '<option value="">Pilih user...</option>';
        list.forEach((u) => {
            const opt = document.createElement('option');
            opt.value = String(u.id);
            const fullname = (u.fullname || '').trim();
            opt.textContent = (fullname ? fullname + ' - ' : '') + u.username;
            userSelect.appendChild(opt);
        });
        const finalSelected = selectedId ? String(selectedId) : (keepSelected ? old : '');
        if (finalSelected) {
            userSelect.value = finalSelected;
        }

        if (typeof jQuery !== 'undefined') {
            jQuery(userSelect).trigger('change.select2');
        }
    }

    function flattenTree(tree, level, parentId, rows, state) {
        (tree || []).forEach((node) => {
            const nodeId = String(node.id);
            const children = Array.isArray(node.children) ? node.children : [];
            rows.push({
                id: nodeId,
                parentId: parentId,
                name: node.name,
                url: node.url,
                level: level,
                allowed: !!node.allowed,
                hasChildren: children.length > 0,
            });

            if (parentId !== null) {
                state.parentMap.set(nodeId, parentId);
            }
            if (!state.childrenMap.has(nodeId)) {
                state.childrenMap.set(nodeId, []);
            }
            if (parentId !== null) {
                if (!state.childrenMap.has(parentId)) {
                    state.childrenMap.set(parentId, []);
                }
                state.childrenMap.get(parentId).push(nodeId);
            }

            if (children.length) {
                flattenTree(children, level + 1, nodeId, rows, state);
            }
        });
    }

    function buildDescendantsMap(childrenMap) {
        const descendantsMap = new Map();

        const collect = function (id) {
            const directChildren = childrenMap.get(id) || [];
            const all = [];
            directChildren.forEach(function (childId) {
                all.push(childId);
                all.push.apply(all, collect(childId));
            });
            return all;
        };

        childrenMap.forEach(function (_children, id) {
            descendantsMap.set(id, collect(id));
        });

        return descendantsMap;
    }

    function getRowByMenuId(menuId) {
        return treeState.rowMap.get(String(menuId)) || null;
    }

    function getCheckboxByMenuId(menuId) {
        const row = getRowByMenuId(menuId);
        return row ? row.querySelector('.rm-toggle') : null;
    }

    function getDescendantIds(menuId) {
        return treeState.descendantsMap.get(String(menuId)) || [];
    }

    function getAncestorIds(menuId) {
        const ancestors = [];
        let parentId = treeState.parentMap.get(String(menuId)) || null;

        while (parentId) {
            ancestors.push(parentId);
            parentId = treeState.parentMap.get(parentId) || null;
        }

        return ancestors;
    }

    function refreshTreeVisibility() {
        const rows = treeBody.querySelectorAll('tr[data-menu-id]');

        rows.forEach(function (row) {
            const menuId = row.getAttribute('data-menu-id');
            let parentId = treeState.parentMap.get(String(menuId)) || null;
            let visible = true;

            while (parentId) {
                const parentRow = getRowByMenuId(parentId);
                if (!parentRow || parentRow.getAttribute('data-collapsed') === '1' || parentRow.hidden) {
                    visible = false;
                    break;
                }
                parentId = treeState.parentMap.get(parentId) || null;
            }

            row.hidden = !visible;
        });
    }

    function setCollapsed(menuId, collapsed) {
        const row = getRowByMenuId(menuId);
        if (!row) {
            return;
        }

        row.setAttribute('data-collapsed', collapsed ? '1' : '0');
        const btn = row.querySelector('.rm-collapse-toggle');
        if (btn) {
            const icon = btn.querySelector('i');
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            if (icon) {
                icon.className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
            }
        }

        refreshTreeVisibility();
    }

    function setTreeBusy(busy) {
        isTreeUpdating = !!busy;
        treeBody.classList.toggle('is-busy', !!busy);
    }

    function renderTree(tree) {
        const rows = [];
        const nextState = {
            parentMap: new Map(),
            childrenMap: new Map(),
            descendantsMap: new Map(),
            rowMap: new Map(),
        };

        flattenTree(tree, 0, null, rows, nextState);
        nextState.descendantsMap = buildDescendantsMap(nextState.childrenMap);
        treeState = nextState;

        if (!rows.length) {
            treeBody.innerHTML = createEmptyTableRow(3, 'Tidak ada data menu untuk user ini');
            return;
        }

        treeBody.innerHTML = rows.map((r) => {
            const parentAttr = r.parentId ? ' data-parent-id="' + escapeHtml(r.parentId) + '"' : '';
            const collapseButton = r.hasChildren
                ? '<button type="button" class="rm-collapse-toggle" data-menu-id="' + escapeHtml(r.id) + '" aria-expanded="true" title="Collapse/Expand"><i class="bi bi-chevron-down"></i></button>'
                : '<span class="rm-node-spacer"></span>';

            return (
                '<tr class="rm-row ' + (r.hasChildren ? 'rm-parent' : 'rm-child') + '" data-menu-id="' + escapeHtml(r.id) + '" data-level="' + r.level + '" data-collapsed="0"' + parentAttr + '>' +
                    '<td class="rm-menu-cell">' +
                        '<div class="rm-menu-wrap" style="padding-left:' + (r.level * 1.15) + 'rem">' +
                            collapseButton +
                            '<span class="rm-node-label">' + escapeHtml(r.name || '') + '</span>' +
                        '</div>' +
                    '</td>' +
                    '<td><code>' + escapeHtml(r.url || '') + '</code></td>' +
                    '<td class="text-center">' +
                        '<input type="checkbox" class="form-check-input rm-toggle" ' + (r.allowed ? 'checked' : '') + ' />' +
                    '</td>' +
                '</tr>'
            );
        }).join('');

        treeBody.querySelectorAll('tr[data-menu-id]').forEach(function (rowEl) {
            const id = rowEl.getAttribute('data-menu-id');
            if (id) {
                treeState.rowMap.set(id, rowEl);
            }
        });

        refreshTreeVisibility();
    }

    function createEmptyTableRow(colspan, text) {
        return (
            '<tr>' +
                '<td colspan="' + colspan + '" class="text-center py-4">' +
                    '<div class="d-flex flex-column justify-content-center align-items-center">' +
                        '<img src="' + (window.AppConfig ? AppConfig.initGlobal : '/') + 'apps/assets/media/illustrations/empty-content-profile.png" alt="Empty" class="img-fluid mb-3" style="max-width: 180px; opacity: 0.85;">' +
                        '<p class="text-muted mt-2 mb-0">' + 'Pencarian Tidak Ditemukan' + '</p><p class="text-muted mb-0 mx-auto" style="max-width: 400px; font-size: .95rem;">Maaf, kami tidak dapat menemukan data yang Anda cari. Silakan periksa kembali kata kunci atau filter pencarian Anda.' + '</p>' +
                    '</div>' +
                '</td>' +
            '</tr>'
        );
    }

    function escapeHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function loadTree() {
        const userId = userSelect.value;
        if (!userId) {
            showToast('Pilih user terlebih dahulu.', 'warning');
            return;
        }

        treeBody.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Memuat data...</td></tr>';
        const res = await fetch(buildAppUrl('api/manage-role/tree?user_id=' + encodeURIComponent(userId)), {
            credentials: 'same-origin',
        });
        const json = await res.json();
        if (!res.ok || !isApiSuccess(json)) {
            throw new Error(getApiMessage(json, 'Gagal memuat tree permission'));
        }
        renderTree(json.data || []);
    }

    async function togglePermission(menuId, allowed, checkboxEl) {
        const userId = userSelect.value;
        const body = new URLSearchParams();
        body.set('user_id', userId);
        body.set('menu_id', menuId);
        body.set('allowed', allowed ? '1' : '0');

        checkboxEl.disabled = true;
        const res = await fetch(buildAppUrl('api/manage-role/toggle'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString(),
            credentials: 'same-origin',
        });
        const json = await res.json();
        checkboxEl.disabled = false;

        if (!res.ok || !isApiSuccess(json)) {
            throw new Error(getApiMessage(json, 'Gagal update permission'));
        }
    }

    async function applyPermissionChanges(changes) {
        if (!Array.isArray(changes) || !changes.length) {
            return;
        }

        const uniqueMap = new Map();
        changes.forEach(function (item) {
            if (!item || !item.menuId || !item.checkboxEl) return;
            uniqueMap.set(String(item.menuId), item);
        });
        const queue = Array.from(uniqueMap.values());
        if (!queue.length) return;

        setTreeBusy(true);
        try {
            for (const item of queue) {
                await togglePermission(String(item.menuId), !!item.allowed, item.checkboxEl);
            }
            showToast('Permission berhasil diperbarui.', 'success');
        } catch (err) {
            showToast(err.message || 'Gagal update permission', 'danger');
            try {
                await loadTree();
            } catch (_err) {
                // noop
            }
        } finally {
            setTreeBusy(false);
        }
    }

    function initUserSelectSearchable() {
        if (typeof jQuery === 'undefined' || typeof jQuery.fn.select2 !== 'function') {
            return;
        }

        const $select = jQuery(userSelect);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            width: '100%',
            theme: 'bootstrap-5',
            placeholder: 'Pilih user...',
            allowClear: true,
            minimumInputLength: 0,
            ajax: {
                transport: function (params, success, failure) {
                    const term = (params.data && params.data.term) ? params.data.term : '';
                    fetchUsers(term)
                        .then(function (list) {
                            success({ results: list.map(function (u) {
                                const fullname = (u.fullname || '').trim();
                                return {
                                    id: String(u.id),
                                    text: (fullname ? fullname + ' - ' : '') + u.username,
                                };
                            }) });
                        })
                        .catch(failure);
                },
                processResults: function (data) {
                    return data;
                },
                delay: 250,
            },
        });
    }

    function clearFormErrors() {
        const fields = userForm.querySelectorAll('.form-control, .form-select');
        fields.forEach((el) => {
            el.classList.remove('is-invalid');
            const feedback = el.parentElement ? el.parentElement.querySelector('.invalid-feedback') : null;
            if (feedback) {
                feedback.textContent = '';
            }
        });
    }

    function setFieldError(name, message) {
        const field = userForm.querySelector('[name="' + name + '"]');
        if (!field) return;
        field.classList.add('is-invalid');
        const feedback = field.parentElement ? field.parentElement.querySelector('.invalid-feedback') : null;
        if (feedback) {
            feedback.textContent = message || 'Input tidak valid';
        }
    }

    function resetUserForm() {
        userForm.reset();
        const statusField = userForm.querySelector('[name="status"]');
        const roleField = userForm.querySelector('[name="role"]');
        if (statusField) statusField.value = '1';
        if (roleField) roleField.value = 'USR';
        clearFormErrors();
    }

    async function createUser(payload) {
        const res = await fetch(buildAppUrl('api/users'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });
        const json = await res.json();
        return { res, json };
    }

    async function submitCreateUser(event) {
        event.preventDefault();
        clearFormErrors();

        const formData = new FormData(userForm);
        const payload = {
            username: String(formData.get('username') || '').trim(),
            fullname: String(formData.get('fullname') || '').trim(),
            email: String(formData.get('email') || '').trim(),
            password: String(formData.get('password') || ''),
            status: String(formData.get('status') || '1'),
            role: String(formData.get('role') || 'USR'),
            active: '1',
        };

        if (!payload.username || !payload.fullname || !payload.email || !payload.password) {
            showToast('Lengkapi data user terlebih dahulu.', 'warning');
            return;
        }

        if (userSubmitBtn) userSubmitBtn.disabled = true;
        if (typeof showLoading === 'function') showLoading('Memproses data...');

        try {
            const response = await createUser(payload);
            if (!response.res.ok || !isApiSuccess(response.json)) {
                const errors = response.json && response.json.errors ? response.json.errors : {};
                Object.keys(errors).forEach((key) => {
                    setFieldError(key, errors[key]);
                });
                throw new Error(getApiMessage(response.json, 'Gagal membuat user baru'));
            }

            const created = response.json.data || null;
            const users = await fetchUsers('');
            renderUsers(users, false, created && created.id ? String(created.id) : '');

            if (userModal) {
                userModal.hide();
            }
            showToast(getApiMessage(response.json, 'User berhasil dibuat'), 'success');
        } catch (error) {
            showToast(error.message || 'Gagal membuat user', 'danger');
        } finally {
            if (typeof hideLoading === 'function') hideLoading();
            if (userSubmitBtn) userSubmitBtn.disabled = false;
        }
    }

    btnLoadTree.addEventListener('click', async function () {
        try {
            await loadTree();
        } catch (e) {
            treeBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">' + escapeHtml(e.message) + '</td></tr>';
            showToast(e.message, 'danger');
        }
    });

    treeBody.addEventListener('change', async function (e) {
        const target = e.target;
        if (!target.classList.contains('rm-toggle') || isTreeUpdating) {
            return;
        }

        const row = target.closest('tr');
        const menuId = row ? row.getAttribute('data-menu-id') : null;
        if (!menuId) {
            return;
        }

        const allowed = target.checked;
        const changes = [{
            menuId: menuId,
            allowed: allowed,
            checkboxEl: target,
        }];

        const descendants = getDescendantIds(menuId);
        if (descendants.length > 0) {
            descendants.forEach(function (childId) {
                const childCheckbox = getCheckboxByMenuId(childId);
                if (!childCheckbox || childCheckbox.checked === allowed) {
                    return;
                }

                childCheckbox.checked = allowed;
                changes.push({
                    menuId: childId,
                    allowed: allowed,
                    checkboxEl: childCheckbox,
                });
            });
        } else if (allowed) {
            const ancestors = getAncestorIds(menuId);
            ancestors.forEach(function (ancestorId) {
                const parentCheckbox = getCheckboxByMenuId(ancestorId);
                if (!parentCheckbox || parentCheckbox.checked) {
                    return;
                }

                parentCheckbox.checked = true;
                changes.push({
                    menuId: ancestorId,
                    allowed: true,
                    checkboxEl: parentCheckbox,
                });
            });
        }

        try {
            await applyPermissionChanges(changes);
        } catch (_err) {
            // handled in applyPermissionChanges
        }
    });

    treeBody.addEventListener('click', function (e) {
        const collapseBtn = e.target.closest('.rm-collapse-toggle');
        if (!collapseBtn) {
            return;
        }

        const menuId = collapseBtn.getAttribute('data-menu-id');
        if (!menuId) {
            return;
        }

        const row = getRowByMenuId(menuId);
        if (!row) {
            return;
        }

        const isCollapsed = row.getAttribute('data-collapsed') === '1';
        setCollapsed(menuId, !isCollapsed);
    });

    if (btnAddUser) {
        btnAddUser.addEventListener('click', function () {
            resetUserForm();
            if (userModal) {
                userModal.show();
            }
        });
    }

    userModalEl.addEventListener('hidden.bs.modal', function () {
        resetUserForm();
    });

    userForm.addEventListener('submit', submitCreateUser);

    (async function init() {
        try {
            const users = await fetchUsers('');
            renderUsers(users, false);
            initUserSelectSearchable();
        } catch (e) {
            showToast(e.message, 'danger');
        }
    })();
})();

