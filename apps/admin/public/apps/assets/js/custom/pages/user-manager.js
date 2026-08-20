/**
 * User Manager Custom JavaScript
 * SIMOJANG Apps - User Management System (Clean Table + 1:1 Solid Squircle Action Buttons & /apps-ikpa Footer)
 */

$(document).ready(function () {
    'use strict';

    // 1. State Variables
    let currentPage = 1;
    let currentSearch = '';
    let currentRole = '';
    let currentStatus = '';
    let perPage = 15;
    let searchDebounceTimer = null;
    let lookupDebounceTimer = null;

    // 2. Initial Load
    loadUsers(1);

    // 3. Search & Filter Handlers
    $('#userSearchInput').on('input', function () {
        const val = $(this).val().trim();
        if (val !== '') {
            $('#btnClearSearch').removeClass('d-none');
        } else {
            $('#btnClearSearch').addClass('d-none');
        }

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
            currentSearch = val;
            currentPage = 1;
            loadUsers(1);
        }, 350);
    });

    $('#btnClearSearch').on('click', function () {
        $('#userSearchInput').val('').focus();
        $(this).addClass('d-none');
        currentSearch = '';
        currentPage = 1;
        loadUsers(1);
    });

    $('#filterRole').on('change', function () {
        currentRole = $(this).val();
        currentPage = 1;
        loadUsers(1);
    });

    $('#filterStatus').on('change', function () {
        currentStatus = $(this).val();
        currentPage = 1;
        loadUsers(1);
    });

    $('#userPerPageSelect').on('change', function () {
        perPage = parseInt($(this).val()) || 15;
        currentPage = 1;
        loadUsers(1);
    });

    $('#btnResetFilter').on('click', function () {
        $('#userSearchInput').val('');
        $('#btnClearSearch').addClass('d-none').removeClass('d-flex');
        $('#filterRole').val('');
        $('#filterStatus').val('');
        $('#userPerPageSelect').val('15');
        perPage = 15;
        currentSearch = '';
        currentRole = '';
        currentStatus = '';
        currentPage = 1;
        loadUsers(1);
    });

    // 4. Pagination Click Handler
    $(document).on('click', '.user-page-link', function (e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (page && page !== currentPage) {
            loadUsers(page);
        }
    });

    // 5. Function: Load Users Data from API
    function loadUsers(page = 1) {
        currentPage = page;
        renderSkeletonLoading();

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/list',
            type: 'GET',
            data: {
                search: currentSearch,
                role: currentRole,
                status: currentStatus,
                page: currentPage,
                per_page: perPage,
            },
            dataType: 'json',
            success: function (response) {
                if (response && response.status && response.data) {
                    const users = response.data.data || [];
                    const total = response.data.total || 0;
                    const totalPages = response.data.total_pages || 1;
                    const stats = response.data.stats || {};

                    updateStats(stats);
                    renderUsersTable(users, total);
                    renderPagination(totalPages, currentPage, total);
                } else {
                    renderEmptyTable('Gagal memuat data pengguna');
                }
            },
            error: function () {
                renderEmptyTable('Terjadi kesalahan koneksi server');
            }
        });
    }

    // 6. Function: Render Users Table (Clean, No Icon, Nama + NIP, 1:1 Solid Squircle Buttons)
    function renderUsersTable(users, total) {
        const $tbody = $('#userTableBody');
        $tbody.empty();
        $('#userSkeleton').addClass('d-none');
        $('#userEmptyState').addClass('d-none');
        $('#userTable').removeClass('d-none');

        if (users.length === 0) {
            renderEmptyTable('Pencarian Tidak Ditemukan', 'Data tidak ditemukan. Silakan periksa kembali kata kunci atau filter pencarian.');
            return;
        }

        users.forEach(function (user) {
            const roleCode = (user.role || 'USR').toUpperCase();
            let roleBadgeClass = 'badge-role-usr';

            if (roleCode === 'ADM') {
                roleBadgeClass = 'badge-role-adm';
            } else if (roleCode !== 'USR') {
                roleBadgeClass = 'badge-role-custom';
            }

            const createdDate = formatDate(user.created_at);

            const rowHtml = `
                <tr id="user-row-${user.id}">
                    <td class="align-middle">
                        <div>
                            <div class="user-name-title">${escapeHtml(user.display_name)}</div>
                            <div class="user-nip-text">NIP: ${escapeHtml(user.username || '-')}</div>
                        </div>
                    </td>
                    <td class="align-middle">
                        <div class="user-email-text">${escapeHtml(user.email || '-')}</div>
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge-role ${roleBadgeClass}">
                            ${escapeHtml(user.role_name || user.role)}
                        </span>
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <div class="form-check form-switch custom-user-switch mb-0">
                                <input class="form-check-input user-status-switch" type="checkbox" role="switch" id="status_${user.id}" data-id="${user.id}" ${user.active ? 'checked' : ''}>
                            </div>
                            <span class="status-label fw-semibold small ${user.active ? 'text-success' : 'text-muted'}">
                                ${user.active ? 'Aktif' : 'Non-Aktif'}
                            </span>
                        </div>
                    </td>
                    <td class="align-middle">
                        ${formatDateTime(user.last_login)}
                    </td>
                    <td class="text-center align-middle">
                        <div class="d-inline-flex align-items-center justify-content-center gap-1">
                            <button type="button" class="btn btn-action-sq btn-primary btn-edit-user" data-id="${user.id}" title="Edit Data Pengguna">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-action-sq btn-warning btn-reset-pwd text-white" data-id="${user.id}" data-name="${escapeHtml(user.display_name)}" data-username="${escapeHtml(user.username)}" title="Reset Password">
                                <i class="bi bi-key-fill"></i>
                            </button>
                            <button type="button" class="btn btn-action-sq btn-danger btn-delete-user" data-id="${user.id}" data-name="${escapeHtml(user.display_name)}" title="Hapus Pengguna">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;

            $tbody.append(rowHtml);
        });

        const from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
        const to = Math.min(currentPage * perPage, total);
        $('#userEntriesInfo').html(`Menampilkan <strong>${from} - ${to}</strong> dari <strong>${total}</strong> data`);
    }

    // 7. Function: Render Pagination (/apps-ikpa Style: Previous, 1, 2, ..., Next)
    function renderPagination(totalPages, current, total) {
        const $pagination = $('#userPagination');
        $pagination.empty();

        if (total === 0) {
            return;
        }

        // Previous Button
        $pagination.append(`
            <li class="page-item prev-item ${current <= 1 ? 'disabled' : ''}">
                <a class="page-link user-page-link" href="#" data-page="${current - 1}" aria-label="Previous">Previous</a>
            </li>
        `);

        // If only 1 page, render page 1 button
        if (totalPages <= 1) {
            $pagination.append(`
                <li class="page-item active">
                    <a class="page-link user-page-link" href="#" data-page="1">1</a>
                </li>
            `);
        } else {
            let startPage = Math.max(1, current - 2);
            let endPage = Math.min(totalPages, current + 2);

            if (startPage > 1) {
                $pagination.append(`<li class="page-item"><a class="page-link user-page-link" href="#" data-page="1">1</a></li>`);
                if (startPage > 2) {
                    $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                $pagination.append(`
                    <li class="page-item ${i === current ? 'active' : ''}">
                        <a class="page-link user-page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    $pagination.append(`<li class="page-item disabled"><span class="page-link">...</span></li>`);
                }
                $pagination.append(`<li class="page-item"><a class="page-link user-page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`);
            }
        }

        // Next Button
        $pagination.append(`
            <li class="page-item next-item ${current >= totalPages ? 'disabled' : ''}">
                <a class="page-link user-page-link" href="#" data-page="${current + 1}" aria-label="Next">Next</a>
            </li>
        `);
    }

    // 8. Function: Update Stats Counter
    function updateStats(stats) {
        if (!stats) return;
        if (stats.total_users !== undefined) $('#statTotalUsers').text(stats.total_users);
        if (stats.active_users !== undefined) $('#statActiveUsers').text(stats.active_users);
        if (stats.inactive_users !== undefined) $('#statInactiveUsers').text(stats.inactive_users);
        if (stats.total_roles !== undefined) $('#statTotalRoles').text(stats.total_roles);
    }

    // 9. Function: Render Skeleton Loading
    function renderSkeletonLoading() {
        const $tbody = $('#userTableBody');
        $tbody.empty();
        for (let i = 0; i < 5; i++) {
            $tbody.append(`
                <tr>
                    <td class="align-middle" style="padding-left: 1.5rem;">
                        <div>
                            <div class="skeleton-box mb-1" style="width: 140px; height: 14px;"></div>
                            <div class="skeleton-box" style="width: 100px; height: 11px;"></div>
                        </div>
                    </td>
                    <td class="align-middle"><div class="skeleton-box" style="width: 140px; height: 14px;"></div></td>
                    <td class="text-center align-middle"><div class="skeleton-box" style="width: 80px; height: 22px; border-radius: 6px;"></div></td>
                    <td class="text-center align-middle"><div class="skeleton-box" style="width: 60px; height: 22px; border-radius: 12px;"></div></td>
                    <td class="align-middle"><div class="skeleton-box" style="width: 80px; height: 14px;"></div></td>
                    <td class="text-center align-middle" style="padding-right: 1.5rem;"><div class="skeleton-box" style="width: 90px; height: 28px; border-radius: 6px;"></div></td>
                </tr>
            `);
        }
    }

    // 10. Function: Render Empty Table (Matching /timkerja-layanan)
    function renderEmptyTable(title = 'Pengguna Tidak Ditemukan', desc = 'Maaf, kami tidak dapat menemukan data pengguna yang cocok dengan kata kunci atau filter pencarian Anda.') {
        const imgUrl = (typeof AppConfig !== 'undefined' && AppConfig.initGlobal ? AppConfig.initGlobal : '/') + 'apps/assets/images/empty-content-profile.png';
        $('#userTableBody').html(`
            <tr>
                <td colspan="6" class="text-center border-0 p-0">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry" style="white-space: normal !important; width: 100%;">
                        <img src="${imgUrl}" alt="Tidak Ditemukan" style="max-width: 280px; margin-bottom: 1.5rem;">
                        <h5 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.35rem;">${escapeHtml(title)}</h5>
                        <p class="small mb-3" style="max-width: 450px; margin: 0 auto; color: #475569; line-height: 1.6; font-size: 0.95rem; white-space: normal !important; word-wrap: break-word;">${escapeHtml(desc)}</p>
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary px-4 py-2 fw-semibold" onclick="$('#btnResetFilter').trigger('click')" style="border-radius: 8px;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        `);
        $('#userPagination').empty();
        $('#userEntriesInfo').html('Menampilkan <strong>0 - 0</strong> dari <strong>0</strong> data');
    }


    // 11. Toggle User Status (AJAX)
    $(document).on('change', '.user-status-switch', function () {
        const $switch = $(this);
        const userId = parseInt($switch.data('id')) || 0;
        const isActive = $switch.is(':checked');
        const $label = $switch.closest('td').find('.status-label');

        if (userId <= 0) return;

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/toggle-status',
            type: 'POST',
            data: JSON.stringify({
                id: userId,
                active: isActive ? 1 : 0
            }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                if (response && response.status) {
                    $label.text(isActive ? 'Aktif' : 'Non-Aktif')
                        .toggleClass('text-success', isActive)
                        .toggleClass('text-muted', !isActive);

                    showToast(response.message || 'Status pengguna berhasil diperbarui', 'success');
                    if (response.data && response.data.stats) {
                        updateStats(response.data.stats);
                    }
                } else {
                    $switch.prop('checked', !isActive);
                    showToast((response && response.message) || 'Gagal memperbarui status', 'error');
                }
            },
            error: function () {
                $switch.prop('checked', !isActive);
                showToast('Terjadi kesalahan koneksi server', 'error');
            }
        });
    });

    // 12. Quick Pegawai Autocomplete Lookup in Modal Tambah
    $('#addLookupPegawai').on('input', function () {
        const query = $(this).val().trim();
        const $dropdown = $('#lookupDropdown');

        clearTimeout(lookupDebounceTimer);
        if (query.length < 2) {
            $dropdown.hide().empty();
            return;
        }

        lookupDebounceTimer = setTimeout(function () {
            $.ajax({
                url: AppConfig.initGlobal + 'api/manage-user/pegawai-lookup',
                type: 'GET',
                data: { q: query },
                dataType: 'json',
                success: function (response) {
                    if (response && response.status && response.data && response.data.length > 0) {
                        $dropdown.empty();
                        response.data.forEach(function (pegawai) {
                            const badge = pegawai.has_user
                                ? '<span class="badge bg-secondary-subtle text-secondary ms-2 small">Sudah Punya Akun</span>'
                                : '<span class="badge bg-success-subtle text-success ms-2 small">Belum Punya Akun</span>';

                            const itemHtml = `
                                <div class="lookup-item" data-nip="${escapeHtml(pegawai.nip)}" data-nama="${escapeHtml(pegawai.nama)}" data-email="${escapeHtml(pegawai.email || '')}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="lookup-item-title">${escapeHtml(pegawai.nama)}</span>
                                        ${badge}
                                    </div>
                                    <div class="lookup-item-sub">NIP: ${escapeHtml(pegawai.nip)} ${pegawai.jabatan ? '• ' + escapeHtml(pegawai.jabatan) : ''}</div>
                                </div>
                            `;
                            $dropdown.append(itemHtml);
                        });
                        $dropdown.show();
                    } else {
                        $dropdown.html('<div class="p-3 text-muted text-center small">Data pegawai tidak ditemukan</div>').show();
                    }
                },
                error: function () {
                    $dropdown.hide().empty();
                }
            });
        }, 300);
    });

    $(document).on('click', '.lookup-item', function () {
        const nip = $(this).data('nip');
        const nama = $(this).data('nama');
        const email = $(this).data('email');

        $('#addUsername').val(nip);
        $('#addFullname').val(nama);
        if (email) {
            $('#addEmail').val(email);
        }

        $('#lookupDropdown').hide().empty();
        $('#addLookupPegawai').val('');
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#addLookupPegawai, #lookupDropdown').length) {
            $('#lookupDropdown').hide();
        }
    });

    // Password generator in Add User Modal
    $('#btnGenAddPwd').on('click', function () {
        const pwd = generateRandomPassword(8);
        $('#addPassword').val(pwd).attr('type', 'text');
        $('#btnToggleAddPwd').html('<i class="bi bi-eye-slash"></i>');
    });

    $('#btnToggleAddPwd').on('click', function () {
        const $input = $('#addPassword');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $(this).html('<i class="bi bi-eye-slash"></i>');
        } else {
            $input.attr('type', 'password');
            $(this).html('<i class="bi bi-eye"></i>');
        }
    });

    // Auto-clear invalid state when user modifies input
    $(document).on('input change', '.modal form input, .modal form select', function () {
        $(this).removeClass('is-invalid');
    });

    // Helper: Display Validation Error with SweetAlert & Highlight Field
    function handleFormValidationError(formId, xhr, defaultTitle = 'Validasi Gagal') {
        let errMsg = 'Terjadi kesalahan saat memproses data.';
        let errField = null;
        try {
            const res = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
            if (res) {
                if (res.message) errMsg = res.message;
                if (res.errors && typeof res.errors === 'object') {
                    const keys = Object.keys(res.errors);
                    if (keys.length > 0) {
                        errField = keys[0];
                        if (res.errors[errField]) {
                            errMsg = res.errors[errField];
                        }
                    }
                }
            }
        } catch (e) { }

        // Highlight field in modal
        if (formId && errField) {
            const fieldCap = errField.charAt(0).toUpperCase() + errField.slice(1);
            const $field = $(`#${formId} #add${fieldCap}, #${formId} #edit${fieldCap}, #${formId} [name="${errField}"]`);
            if ($field.length) {
                $field.addClass('is-invalid').focus();
            }
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: defaultTitle,
                html: `<div class="text-secondary text-center px-2 py-1" style="font-size: 1rem; line-height: 1.5;">${escapeHtml(errMsg)}</div>`,
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Periksa Kembali'
            });
        } else {
            showToast(errMsg, 'error');
        }
    }

    // 13. Submit Form Tambah Pengguna
    $('#formAddUser').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        $form.find('.is-invalid').removeClass('is-invalid');

        const $btn = $('#btnSaveAddUser');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        const payload = {
            username: $('#addUsername').val().trim(),
            fullname: $('#addFullname').val().trim(),
            email: $('#addEmail').val().trim(),
            role: $('#addRole').val(),
            password: $('#addPassword').val(),
            active: $('#addActive').is(':checked') ? 1 : 0
        };

        // Client-side quick check
        if (!payload.username) {
            $btn.prop('disabled', false).text('Simpan Data');
            $('#addUsername').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Username / NIP wajib diisi.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        if (!payload.fullname) {
            $btn.prop('disabled', false).text('Simpan Data');
            $('#addFullname').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Nama lengkap pengguna wajib diisi.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        if (!payload.email) {
            $btn.prop('disabled', false).text('Simpan Data');
            $('#addEmail').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Alamat email wajib diisi.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        if (!payload.password || payload.password.length < 6) {
            $btn.prop('disabled', false).text('Simpan Data');
            $('#addPassword').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Password Kurang',
                text: 'Password minimal terdiri dari 6 karakter.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/create',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                $btn.prop('disabled', false).text('Simpan Data');
                if (response && response.status) {
                    $('#modalAddUser').modal('hide');
                    $form[0].reset();
                    showToast(response.message || 'Pengguna berhasil dibuat', 'success');
                    loadUsers(1);
                } else {
                    const msg = (response && response.message) || 'Gagal menyimpan data pengguna';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            html: `<div class="text-secondary text-center px-2" style="font-size: 1rem;">${escapeHtml(msg)}</div>`,
                            confirmButtonColor: '#1040c1',
                            confirmButtonText: 'Periksa Kembali'
                        });
                    } else {
                        showToast(msg, 'error');
                    }
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Simpan Data');
                handleFormValidationError('formAddUser', xhr, 'Validasi Pengguna Gagal');
            }
        });
    });

    // 14. Edit User Modal & Submit
    $(document).on('click', '.btn-edit-user', function () {
        const userId = parseInt($(this).data('id')) || 0;
        if (userId <= 0) return;

        $('#formEditUser').find('.is-invalid').removeClass('is-invalid');

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/detail',
            type: 'GET',
            data: { id: userId },
            dataType: 'json',
            success: function (response) {
                if (response && response.status && response.data) {
                    const user = response.data;
                    $('#editUserId').val(user.id);
                    $('#editUsername').val(user.username);
                    $('#editFullname').val(user.fullname || user.pegawai_nama || '');
                    $('#editEmail').val(user.email || '');
                    $('#editRole').val(user.role || 'USR');
                    $('#editPassword').val('');
                    $('#editActive').prop('checked', parseInt(user.active) === 1);
                    $('#modalEditUser').modal('show');
                } else {
                    showToast((response && response.message) || 'Data pengguna tidak ditemukan', 'error');
                }
            },
            error: function () {
                showToast('Gagal memuat detail pengguna', 'error');
            }
        });
    });

    $('#formEditUser').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        $form.find('.is-invalid').removeClass('is-invalid');

        const $btn = $('#btnSaveEditUser');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        const payload = {
            id: parseInt($('#editUserId').val()) || 0,
            fullname: $('#editFullname').val().trim(),
            email: $('#editEmail').val().trim(),
            role: $('#editRole').val(),
            password: $('#editPassword').val(),
            active: $('#editActive').is(':checked') ? 1 : 0
        };

        if (!payload.fullname) {
            $btn.prop('disabled', false).text('Simpan Perubahan');
            $('#editFullname').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Nama lengkap pengguna wajib diisi.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        if (!payload.email) {
            $btn.prop('disabled', false).text('Simpan Perubahan');
            $('#editEmail').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                text: 'Alamat email wajib diisi.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/update',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                $btn.prop('disabled', false).text('Simpan Perubahan');
                if (response && response.status) {
                    $('#modalEditUser').modal('hide');
                    showToast(response.message || 'Pengguna berhasil diperbarui', 'success');
                    loadUsers(currentPage);
                } else {
                    const msg = (response && response.message) || 'Gagal memperbarui pengguna';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validasi Gagal',
                            html: `<div class="text-secondary text-center px-2" style="font-size: 1rem;">${escapeHtml(msg)}</div>`,
                            confirmButtonColor: '#1040c1',
                            confirmButtonText: 'Periksa Kembali'
                        });
                    } else {
                        showToast(msg, 'error');
                    }
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Simpan Perubahan');
                handleFormValidationError('formEditUser', xhr, 'Perbaruan Pengguna Gagal');
            }
        });
    });

    // 15. Reset Password Modal & Action
    $(document).on('click', '.btn-reset-pwd', function () {
        const userId = parseInt($(this).data('id')) || 0;
        const name = $(this).data('name') || '';
        const username = $(this).data('username') || '';

        $('#resetUserId').val(userId);
        $('#resetUserDisplay').text(`${name} (${username})`);
        $('#resetNewPassword').val(generateRandomPassword(8)).removeClass('is-invalid');
        $('#modalResetPassword').modal('show');
    });

    $('#btnGenResetPwd').on('click', function () {
        $('#resetNewPassword').val(generateRandomPassword(8)).removeClass('is-invalid');
    });

    $('#btnCopyResetPwd').on('click', function () {
        const pwd = $('#resetNewPassword').val();
        if (pwd) {
            navigator.clipboard.writeText(pwd).then(function () {
                showToast('Password disalin ke clipboard', 'success');
            }).catch(function () {
                showToast('Gagal menyalin password', 'error');
            });
        }
    });

    $('#formResetPassword').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btnSaveResetPwd');
        const pwd = $('#resetNewPassword').val().trim();

        if (pwd.length < 6) {
            $('#resetNewPassword').addClass('is-invalid').focus();
            Swal.fire({
                icon: 'warning',
                title: 'Password Kurang',
                text: 'Password baru minimal terdiri dari 6 karakter.',
                confirmButtonColor: '#1040c1',
                confirmButtonText: 'Tutup'
            });
            return;
        }

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

        const payload = {
            id: parseInt($('#resetUserId').val()) || 0,
            password: pwd
        };

        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/reset-password',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                $btn.prop('disabled', false).text('Simpan Password Baru');
                if (response && response.status) {
                    $('#modalResetPassword').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Password Berhasil Direset',
                            html: `Password baru untuk akun ini adalah: <br><strong class="fs-4 text-primary d-block my-2">${escapeHtml(payload.password)}</strong><small class="text-muted">Pastikan telah mencatat password sebelum menutup notifikasi ini.</small>`,
                            confirmButtonColor: '#1040c1',
                            confirmButtonText: 'Selesai'
                        });
                    } else {
                        showToast('Password pengguna berhasil direset', 'success');
                    }
                } else {
                    showToast((response && response.message) || 'Gagal mereset password', 'error');
                }
            },
            error: function (xhr) {
                $btn.prop('disabled', false).text('Simpan Password Baru');
                handleFormValidationError('formResetPassword', xhr, 'Reset Password Gagal');
            }
        });
    });

    // 16. Delete User Handler
    $(document).on('click', '.btn-delete-user', function () {
        const userId = parseInt($(this).data('id')) || 0;
        const name = $(this).data('name') || 'Pengguna';

        if (userId <= 0) return;

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: `Apakah Anda yakin ingin menghapus akun pengguna "${name}"? Tindakan ini tidak dapat dibatalkan.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Pengguna',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteUser(userId);
                }
            });
        } else {
            if (confirm(`Apakah Anda yakin ingin menghapus akun "${name}"?`)) {
                deleteUser(userId);
            }
        }
    });

    function deleteUser(userId) {
        $.ajax({
            url: AppConfig.initGlobal + 'api/manage-user/delete',
            type: 'POST',
            data: JSON.stringify({ id: userId }),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                if (response && response.status) {
                    showToast(response.message || 'Pengguna berhasil dihapus', 'success');
                    loadUsers(currentPage);
                } else {
                    showToast((response && response.message) || 'Gagal menghapus pengguna', 'error');
                }
            },
            error: function (xhr) {
                let errMsg = 'Terjadi kesalahan sistem saat menghapus';
                try {
                    const res = JSON.parse(xhr.responseText);
                    if (res && res.message) errMsg = res.message;
                } catch (e) { }
                showToast(errMsg, 'error');
            }
        });
    }

    // Helper functions
    function getInitials(name) {
        if (!name) return 'U';
        const parts = String(name).trim().split(/\s+/);
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
    }

    function formatDateTime(dateStr) {
        if (!dateStr || dateStr === '0000-00-00 00:00:00') {
            return '<span class="text-muted small fst-italic">Belum pernah</span>';
        }
        const d = new Date(String(dateStr).replace(/-/g, '/'));
        if (isNaN(d.getTime())) {
            return escapeHtml(dateStr);
        }
        const datePart = d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        const timePart = d.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        }).replace('.', ':');
        return `<span class="text-secondary small fw-medium">${datePart} <small class="text-muted">(${timePart})</small></span>`;
    }

    function generateRandomPassword(length = 8) {
        const charset = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
        let pwd = '';
        for (let i = 0; i < length; i++) {
            pwd += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return pwd;
    }

    function showToast(message, type = 'success') {
        if (typeof Toastify === 'function') {
            const bg = type === 'success' ? '#10b981' : (type === 'warning' ? '#f59e0b' : '#ef4444');
            Toastify({
                text: message,
                duration: 3500,
                gravity: 'top',
                position: 'right',
                backgroundColor: bg,
                stopOnFocus: true,
            }).showToast();
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'error'),
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            alert(message);
        }
    }
});
