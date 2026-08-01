const tbody = $('#usulanTableBody');
const addRowBtn = $('#addRowBtn');
const pdmItemCount = $('#pdmItemCount');
const pdmItemAcc = $('#pdmItemAcc');
const pdmItemBtl = $('#pdmItemBtl');
const pdmItemTms = $('#pdmItemTms');
const pdmItemEmpty = $('#pdmItemEmpty');
let isSubmittingPdm = false;

const dropdownOptions = {
    jenis: ['Golongan', 'Pendidikan', 'PMK', 'Unor PPPK', 'Pendidikan PPPK'],
};
    
function createDropdown(options, name, selected = '') {
    return `<select class="form-select" name="${name}[]" required>
                <option value="">Pilih jenis</option>
                ${options.map(opt => `<option value="${opt}" ${String(selected) === String(opt) ? 'selected' : ''}>${opt}</option>`).join('')}
            </select>`;
}

function formatPdmNumber(value) {
    return new Intl.NumberFormat('id-ID').format(Number(value || 0));
}

function updateItemSummary() {
    let count = 0;
    let totalAcc = 0;
    let totalBtl = 0;
    let totalTms = 0;

    tbody.find('tr').each(function () {
        count += 1;
        totalAcc += Number($(this).find('.js-pdm-acc').val() || 0);
        totalBtl += Number($(this).find('.js-pdm-btl').val() || 0);
        totalTms += Number($(this).find('.js-pdm-tms').val() || 0);
    });

    pdmItemCount.text(formatPdmNumber(count));
    pdmItemAcc.text(formatPdmNumber(totalAcc));
    pdmItemBtl.text(formatPdmNumber(totalBtl));
    pdmItemTms.text(formatPdmNumber(totalTms));
    pdmItemEmpty.toggleClass('d-none', count > 0);
}

function resetPdmForm() {
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
            <td><input type="number" name="acc[]" class="form-control js-pdm-acc" min="0" step="1" value="${data.acc || ''}"></td>
            <td><input type="number" name="btl[]" class="form-control js-pdm-btl" min="0" step="1" value="${data.btl || ''}"></td>
            <td><input type="number" name="tms[]" class="form-control js-pdm-tms" min="0" step="1" value="${data.tms || ''}"></td>
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

tbody.on('input change', '.js-pdm-acc, .js-pdm-btl, .js-pdm-tms, select[name="jenis[]"]', function () {
    updateItemSummary();
});

$('#form-usulan').on('submit', function (e) {
    e.preventDefault();
    if (isSubmittingPdm) {
        return;
    }

    isSubmittingPdm = true;
    const submitButton = $(this).find('.btn-submit-form');
    submitButton.prop('disabled', true);
    swlwaitProsessing();

    $.ajax({
        url: AppConfig.initGlobal + 'store/save-data-peremajaan',
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
            swlErrorHandler('Terjadi kesalahan saat menyimpan data peremajaan.');
        },
        complete: function () {
            isSubmittingPdm = false;
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
    resetPdmForm();
});

$('#DataModal').on('hidden.bs.modal', function () {
    resetPdmForm();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open').css('overflow', '');
});
