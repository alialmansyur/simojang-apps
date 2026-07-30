(function () {
    const apiBase = AppConfig.initGlobal + 'api/apps-tapnip/events';
    const picker = $('#docCategoryPicker');
    const hiddenCategory = $('#doc_category');
    const tableBody = $('#tapnipEventTableBody');
    const eventForm = $('#tapnipEventForm');
    const eventIdInput = $('#tapnip_event_id');
    const eventNameInput = $('#tapnip_event_name');
    const eventSubmitBtn = $('#tapnipEventSubmitBtn');
    const eventResetBtn = $('#tapnipEventResetBtn');
    const patchWarning = $('#tapnipEventPatchWarning');

    const state = {
        tableReady: false,
        events: [],
    };

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getCurrentCategory() {
        const pickerValue = String(picker.val() || '').trim();
        if (pickerValue) return pickerValue;
        return String(hiddenCategory.val() || '').trim();
    }

    function resetForm() {
        eventIdInput.val('');
        eventNameInput.val('');
        eventSubmitBtn.text('Simpan Event');
    }

    function setEditMode(row) {
        eventIdInput.val(row.id || '');
        eventNameInput.val(String(row.nama || ''));
        eventSubmitBtn.text('Update Event');
        eventNameInput.trigger('focus');
    }

    function renderRows() {
        if (!state.events.length) {
            tableBody.html('<tr><td colspan="2" class="text-center text-muted py-3">Belum ada event.</td></tr>');
            return;
        }

        const html = state.events.map((row) => {
            const name = escapeHtml(row.nama || '');
            const isReadonly = !state.tableReady || !row.id;
            const editBtn = isReadonly
                ? '<button type="button" class="btn btn-sm btn-outline-secondary" disabled>Edit</button>'
                : `<button type="button" class="btn btn-sm btn-outline-primary js-event-edit" data-id="${row.id}">Edit</button>`;
            const deleteBtn = isReadonly
                ? '<button type="button" class="btn btn-sm btn-outline-secondary" disabled>Hapus</button>'
                : `<button type="button" class="btn btn-sm btn-outline-danger js-event-delete" data-id="${row.id}" data-name="${name}">Hapus</button>`;

            return `
                <tr>
                    <td>${name}</td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1">${editBtn}${deleteBtn}</div>
                    </td>
                </tr>
            `;
        }).join('');

        tableBody.html(html);
    }

    function syncDropdown(triggerChange) {
        if (!picker.length) return;

        const currentValue = getCurrentCategory();
        const options = state.events
            .map((row) => String(row.nama || '').trim())
            .filter((name) => name.length > 0);
        const uniqueOptions = [...new Set(options)];

        if (!uniqueOptions.length) return;

        picker.empty();
        uniqueOptions.forEach((name) => {
            picker.append(`<option value="${escapeHtml(name)}">${escapeHtml(name)}</option>`);
        });

        const selectedValue = uniqueOptions.includes(currentValue) ? currentValue : uniqueOptions[0];
        picker.val(selectedValue);
        hiddenCategory.val(selectedValue);

        if (triggerChange) {
            picker.trigger('change');
        }
    }

    function setCrudAvailability() {
        if (state.tableReady) {
            patchWarning.addClass('d-none');
            eventNameInput.prop('disabled', false);
            eventSubmitBtn.prop('disabled', false);
            eventResetBtn.prop('disabled', false);
            return;
        }

        patchWarning.removeClass('d-none');
        eventNameInput.prop('disabled', true);
        eventSubmitBtn.prop('disabled', true);
        eventResetBtn.prop('disabled', true);
    }

    function loadEvents(triggerChange) {
        $.ajax({
            url: apiBase,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                state.tableReady = Boolean(response && response.table_ready);
                state.events = Array.isArray(response && response.data) ? response.data : [];
                setCrudAvailability();
                renderRows();
                syncDropdown(Boolean(triggerChange));
            },
            error: function () {
                swlErrorHandler('Gagal memuat event TapNIP.');
            }
        });
    }

    function createOrUpdateEvent(payload) {
        const isUpdate = Boolean(payload.id);
        $.ajax({
            url: isUpdate ? apiBase + '/update' : apiBase,
            type: 'POST',
            dataType: 'json',
            data: payload,
            success: function (response) {
                if (!response || response.status !== 'success') {
                    swlErrorHandler(response && response.message ? response.message : 'Gagal menyimpan event.');
                    return;
                }
                resetForm();
                loadEvents(true);
                swlSuccess();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal menyimpan event.';
                swlErrorHandler(message);
            }
        });
    }

    function removeEvent(id, name) {
        Swal.fire({
            text: `Hapus event "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: apiBase + '/delete',
                type: 'POST',
                dataType: 'json',
                data: { id: id },
                success: function (response) {
                    if (!response || response.status !== 'success') {
                        swlErrorHandler(response && response.message ? response.message : 'Gagal menghapus event.');
                        return;
                    }
                    resetForm();
                    loadEvents(true);
                    swlSuccess();
                },
                error: function (xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Gagal menghapus event.';
                    swlErrorHandler(message);
                }
            });
        });
    }

    eventForm.on('submit', function (e) {
        e.preventDefault();

        if (!state.tableReady) {
            swlErrorHandler('Table event belum siap. Jalankan SQL patch terlebih dahulu.');
            return;
        }

        const id = Number(eventIdInput.val() || 0);
        const name = String(eventNameInput.val() || '').trim();

        if (!name) {
            swlErrorHandler('Nama event wajib diisi.');
            return;
        }

        createOrUpdateEvent({
            id: id > 0 ? id : '',
            nama: name,
        });
    });

    eventResetBtn.on('click', function () {
        resetForm();
    });

    tableBody.on('click', '.js-event-edit', function () {
        const id = Number($(this).data('id') || 0);
        const row = state.events.find((item) => Number(item.id) === id);
        if (!row) return;
        setEditMode(row);
    });

    tableBody.on('click', '.js-event-delete', function () {
        const id = Number($(this).data('id') || 0);
        const name = String($(this).data('name') || '').trim();
        if (id <= 0) return;
        removeEvent(id, name);
    });

    $('#tapnipEventModal').on('shown.bs.modal', function () {
        loadEvents(false);
    });

    $(document).ready(function () {
        loadEvents(false);
    });
})();

