const tbody = $('#usulanTableBody');
const addRowBtn = $('#addRowBtn');
const dmsItemCount = $('#dmsItemCount');
const dmsItemTotal = $('#dmsItemTotal');
const dmsItemEmpty = $('#dmsItemEmpty');
let isSubmittingDms = false;

const dropdownOptions = {
    jenis: [
        'D2NIP',
        'IJAZAH',
        'AKTA',
        'DRH',
        'SK CPNS',
        'SK PNS',
        'SK PINDAH INSTANSI',
        'SK KP',
        'SK JABATAN',
        'SK PEMBERHETINA',
        'SK PENSIUN'
    ],
};

function createDropdown(options, name, selected = '') {
    return `<select class="form-select" name="${name}[]" required>
                <option value="">Pilih jenis dokumen</option>
                ${options.map(opt => `<option value="${opt}" ${String(selected) === String(opt) ? 'selected' : ''}>${opt}</option>`).join('')}
            </select>`;
}

function formatDmsNumber(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value || 0));
}

function updateItemSummary() {
    let count = 0;
    let total = 0;

    tbody.find('tr').each(function () {
        count += 1;
        total += Number($(this).find('.js-dms-total').val() || 0);
    });

    dmsItemCount.text(formatDmsNumber(count));
    dmsItemTotal.text(formatDmsNumber(total));
    dmsItemEmpty.toggleClass('d-none', count > 0);
}

function resetDmsForm() {
    tbody.empty();
    const form = $('#form-usulan')[0];
    if (form) form.reset();
    addRow();
    updateItemSummary();
}

function addRow(data = {}) {
    const newRow = $(`
        <tr>
            <td>${createDropdown(dropdownOptions.jenis, 'jenis', data.jenis || '')}</td>
            <td><input type="number" name="total[]" class="form-control js-dms-total" min="0" step="1" value="${data.total || ''}" required></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `);
    tbody.append(newRow);
    updateItemSummary();
}

addRowBtn.on('click', function () {
    addRow();
});

tbody.on('click', '.btn-delete-row', function () {
    $(this).closest('tr').remove();
    if (!tbody.find('tr').length) {
        addRow();
    } else {
        updateItemSummary();
    }
});

tbody.on('input change', '.js-dms-total, select[name="jenis[]"]', function () {
    updateItemSummary();
});

$('#form-usulan').on('submit', function (e) {
    e.preventDefault();
    if (isSubmittingDms) {
        return;
    }

    isSubmittingDms = true;
    const submitButton = $(this).find('.btn-submit-form');
    submitButton.prop('disabled', true);
    swlwaitProsessing();

    $.ajax({
        url: AppConfig.initGlobal + 'store/save-data-dms',
        method: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#DataModal').modal('hide');
                $('#dataTable').DataTable().ajax.reload(null, false);
                swlSuccess();
            } else {
                swlErrorHandler(response.message);
            }
        },
        error: function () {
            swlErrorHandler('Terjadi kesalahan saat menyimpan data DMS.');
        },
        complete: function () {
            isSubmittingDms = false;
            submitButton.prop('disabled', false);
        }
    });
});

function getCurrentDateTime() {
    const now = new Date();
    const dd = String(now.getDate()).padStart(2, '0');
    const mm = String(now.getMonth() + 1).padStart(2, '0'); // bulan mulai dari 0
    const yyyy = now.getFullYear();
    const hh = String(now.getHours()).padStart(2, '0');
    const ii = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');
    return `${dd}-${mm}-${yyyy} ${hh}:${ii}:${ss}`;
}

$(document).ready(function () {
    resetDmsForm();

    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);

        modal.find('.select-instansi').each(function () {
            if ($(this).hasClass("select2-hidden-accessible")) {
                $(this).select2('destroy');
            }

            $(this).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: modal,
                minimumInputLength: 0,
                ajax: {
                    url: AppConfig.initGlobal + 'instansi-list',
                    type: 'POST',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        return {
                            search: params.term 
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
            
        });
    });
});

$('#DataModal').on('hidden.bs.modal', function () {
    const form = $('#form-usulan');
    resetDmsForm();
    form.find('.select-instansi').each(function () {
        $(this).val(null).trigger('change');
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2('destroy');
        }
    });
});
