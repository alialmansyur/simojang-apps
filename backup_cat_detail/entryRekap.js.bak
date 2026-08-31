$(document).ready(function () {


    const tbody = $('#usulanTableBody');
    const addRowBtn = $('#addRowBtn');
    const rekapModal = $('#DataModal');

    const dropdownOptions = {
        nip: ['123456', '234567', '345678'],
        jenis: ['Golongan', 'Pendidikan', 'PMK', 'Unor PPPK'],
        tt: ['Disetujui', 'BTS', 'TMS'],
        status: ['Tambah', 'Ubah', 'Hapus', 'Unor']
    };

    function createDropdown(options, name) {
        return `<select class="form-select select2" name="${name}[]" required>
                    ${options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                </select>`;
    }

    function initInstansiSelect(select, modal) {
        if (!select || !select.length) return;

        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }

        select.select2({
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
    }

    $('#DataModalDetail').on('hidden.bs.modal', function () {
        const form = $('#form-usulan-edit');
        form[0].reset();
        form.find('.select-instansi').each(function () {
            $(this).val(null).trigger('change');
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
    });

    $('#DataModal').on('hidden.bs.modal', function () {
        const form = $('#form-usulan');
        tbody.find('.select-instansi').each(function () {
            $(this).val(null).trigger('change');
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        tbody.empty();
        form[0].reset();
    });

    $(document).on('shown.bs.modal', function (e) {
        const modal = $(e.target);

        modal.find('.select-instansi').each(function () {
            initInstansiSelect($(this), modal);
        });
    });

    function addRow() {

        const lastRow = tbody.find('tr:last');

        let refInstansiId = '';
        let refInstansiText = '';
        let refTanggal = '';

        if (lastRow.length) {
            const instansiSelect = lastRow.find('.select-instansi');

            refInstansiId = instansiSelect.val();
            refInstansiText = instansiSelect.find('option:selected').text();
            refTanggal = lastRow.find('input[name="tanggal[]"]').val();
        }

        const newRow = $(`
            <tr>
                <td>
                    <select name="instansi[]" class="form-control select-instansi" required>
                        <option value="">Pilih Instansi</option>
                    </select>
                </td>
                <td>
                    <input type="date" name="tanggal[]" class="form-control" value="${refTanggal}" required>
                </td>
                <td>
                    <input type="text" name="sesi[]" class="form-control" placeholder="Sesi" required>
                </td>
                <td>
                    <input type="number" name="nilai_min[]" class="form-control" placeholder="0">
                </td>
                <td>
                    <input type="number" name="nilai_max[]" class="form-control" placeholder="100">
                </td>
                <td>
                    <input type="number" name="hadir[]" class="form-control" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_hadir[]" class="form-control" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="reschedule[]" class="form-control" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="memenuhi[]" class="form-control" placeholder="0" min="0">
                </td>
                <td>
                    <input type="number" name="tidak_memenuhi[]" class="form-control" placeholder="0" min="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light-danger btn-delete-row">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        tbody.append(newRow);

        const selectInstansi = newRow.find('.select-instansi');
        initInstansiSelect(selectInstansi, rekapModal);

        if (refInstansiId) {
            const option = new Option(refInstansiText, refInstansiId, true, true);
            selectInstansi.append(option).trigger('change');
        }
    }


    addRowBtn.on('click', function () {
        addRow();
    });

    tbody.on('click', '.btn-delete-row', function () {
        $(this).closest('tr').remove();
    });

    $(document).on('click', '.btn-submit-form', function () {
        $('#form-usulan').submit();
    });

    $('#form-usulan').on('submit', function (e) {
        e.preventDefault();
        $('#DataModal').modal('hide');
        swlwaitProsessing();
        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-tilok-rekap',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    tbody.empty();
                    $('#form-usulan')[0].reset();
                    $('#dataTable').DataTable().ajax.reload();
                    swlSuccess();
                } else {
                    swlErrorHandler(response.message);
                }
            }
        });
    });
});
