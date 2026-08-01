$(document).ready(function () {
    const tbody = $('#usulanTableBody');
    const addRowBtn = $('#addRowBtn');
    const dataModal = $('#DataModal');

    const dropdownOptions = {
        jenis_penilaian: ['UD Tk.I', 'UD Tk.II', 'UPKP SLTP/SMA', 'UPKP S1', 'UPKP S2']
    };

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
        
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });

    $(document).off('shown.bs.modal', '#DataModal');
    $('#DataModal').on('shown.bs.modal', function (e) {
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
        let refJenis = '';

        if (lastRow.length) {
            const instansiSelect = lastRow.find('.select-instansi');
            refInstansiId = instansiSelect.val();
            refInstansiText = instansiSelect.find('option:selected').text();
            refTanggal = lastRow.find('input[name="tanggal[]"]').val();
            refJenis = lastRow.find('select[name="jenis_penilaian[]"]').val();
        }

        const optionsHtml = dropdownOptions.jenis_penilaian.map(opt => `<option value="${opt}" ${opt === refJenis ? 'selected' : ''}>${opt}</option>`).join('');

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
                    <select name="jenis_penilaian[]" class="form-select select2" required>
                        <option value="">Pilih</option>
                        ${optionsHtml}
                    </select>
                </td>
                <td><input type="number" name="total_peserta[]" class="form-control" placeholder="0" min="0" required></td>
                <td><input type="number" name="memenuhi[]" class="form-control" placeholder="0" min="0" required></td>
                <td><input type="number" name="tidak_memenuhi[]" class="form-control" placeholder="0" min="0" required></td>
                <td><input type="number" name="lulus[]" class="form-control" placeholder="0" min="0" required></td>
                <td><input type="number" name="tidak_lulus[]" class="form-control" placeholder="0" min="0" required></td>
                <td><input type="number" name="tidak_hadir[]" class="form-control" placeholder="0" min="0" required></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light-danger btn-delete-row">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);

        tbody.append(newRow);

        const selectInstansi = newRow.find('.select-instansi');
        initInstansiSelect(selectInstansi, dataModal);

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
        
        if (tbody.find('tr').length === 0) {
            swlErrorHandler("Harap tambahkan minimal satu baris data.");
            return;
        }

        $('#DataModal').modal('hide');
        swlwaitProsessing();
        $.ajax({
            url: AppConfig.initGlobal + 'apps-karier/store-data',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    tbody.empty();
                    $('#form-usulan')[0].reset();
                    if ($('#dataTable').length) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }
                    if (typeof updateServiceUI === 'function') {
                        updateServiceUI();
                    }
                    swlSuccess();
                } else {
                    swlErrorHandler(response.message);
                }
            },
            error: function (xhr) {
                swlErrorHandler("Terjadi kesalahan sistem.");
            }
        });
    });

    // Import Excel Handler
    $('.btn-submit-import').on('click', function () {
        $('#form-import').submit();
    });

    $('#form-import').on('submit', function (e) {
        e.preventDefault();
        const fileInput = $(this).find('input[name="file"]');
        if (!fileInput.val()) {
            swlErrorHandler("Silakan pilih file Excel terlebih dahulu.");
            return;
        }

        let formData = new FormData(this);
        
        $('#ImportModal').modal('hide');
        swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'apps-karier/import-data',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    $('#form-import')[0].reset();
                    if ($('#dataTable').length) {
                        $('#dataTable').DataTable().ajax.reload(null, false);
                    }
                    if (typeof updateServiceUI === 'function') {
                        updateServiceUI();
                    }
                    swlSuccess(response.message);
                } else {
                    swlErrorHandler(response.message);
                }
            },
            error: function (xhr) {
                swlErrorHandler("Terjadi kesalahan sistem saat mengunggah file.");
            }
        });
    });

});
