const emptyStateMarkup = `
<div class="d-flex flex-column align-items-center justify-content-center text-center mt-5 mb-5 pb-4 tw-animate-entry">
    <img src="${AppConfig.initGlobal}apps/assets/images/empty-content-profile.png" alt="Tidak Ditemukan" style="max-width: 320px; margin-bottom: 2rem;">
    <h5 class="fw-bold" style="color: #1a202c; font-size: 1.35rem;">Pencarian Tidak Ditemukan</h5>
    <p class="text-muted mb-0" style="font-size: 1.05rem; max-width: 450px; margin: 0 auto; line-height: 1.6;">Tidak ada rekap data yang cocok dengan pencarian Anda.</p>
</div>
`;
const processingState = (window.ServiceTableUI && ServiceTableUI.createProcessingState)
    ? ServiceTableUI.createProcessingState('Memuat data...')
    : '<div class="text-center text-muted py-4">Memuat data...</div>';

$(document).ready(function () {
    let dtTable;
    let selectedBulan = [];

    function initTable() {
        dtTable = $('#dataTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: AppConfig.initGlobal + 'apps-kompetensi/get-data',
                type: 'POST',
                data: function (d) {
                    d.bulan = selectedBulan;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'instansi_nama', name: 'd.nama' },
                {
                    data: 'tanggal',
                    name: 'a.tanggal',
                    render: function (data) {
                        return ServiceTableUI.formatDateToIndonesian(data);
                    }
                },
                { data: 'metode', name: 'a.metode' },
                {
                    data: 'total_peserta',
                    name: 'a.total_peserta',
                    className: 'text-center',
                    render: function (data) { return ServiceTableUI.formatNumber(data || 0); }
                },
                { data: 'created_by', name: 'a.created_by' },
                {
                    data: 'created_at',
                    name: 'a.created_at',
                    render: function (data) {
                        return ServiceTableUI.formatDateTime(data);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row) {
                        return `
                            <button class="btn btn-sm btn-light-danger btn-delete" data-uid="${row.uid}" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            order: [[6, 'desc']],
            buttons: ['copy', 'excel', 'pdf', 'print'],
            language: {
                emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                processing: processingState
            },
            initComplete: function () {
                if (window.ServiceTableUI) {
                    ServiceTableUI.setup({
                        key: 'kpid',
                        table: dtTable,
                        initialPageSkeleton: false,
                        initialTableSkeleton: false,
                        initialBackdrop: false,
                        pageSkeleton: false,
                        tableSkeleton: false,
                        backdrop: false,
                        disableRecap: true,
                        reloadSummaryOnClick: false,
                        loadSummary: updateServiceUI
                    });
                }
                updateServiceUI();
            }
        });

        dtTable.on('xhr.dt', function () { updateServiceUI(); });
    }

    function updateServiceUI() {
        $.ajax({
            url: AppConfig.initGlobal + 'apps-kompetensi/get-summary',
            type: 'POST',
            data: { bulan: selectedBulan },
            dataType: 'json',
            success: function (response) {
                if (response.status && response.summary) {
                    let s = response.summary;
                    $('#kpid-total-rekap').text(ServiceTableUI.formatNumber(s.total_rekap || 0));
                    $('#kpid-total-instansi').text(ServiceTableUI.formatNumber(s.total_instansi || 0));
                    $('#kpid-total-peserta-cact').text(ServiceTableUI.formatNumber(s.total_peserta_cact || 0));
                    $('#kpid-total-peserta-proasn').text(ServiceTableUI.formatNumber(s.total_peserta_proasn || 0));
                    $('#kpid-total-peserta-integrasi').text(ServiceTableUI.formatNumber(s.total_peserta_integrasi || 0));
                    $('#kpid-last-update').text(ServiceTableUI.formatDateTime(s.last_update));
                }
            }
        });
    }

    window.updateServiceUI = updateServiceUI;

    $('#dataTable').on('click', '.btn-delete', function () {
        const uid = $(this).data('uid');
        swalConfirmDelete("Data ini akan dihapus permanen!", function() {
            swlwaitProsessing();
            $.ajax({
                url: AppConfig.initGlobal + 'apps-kompetensi/remove-data',
                type: 'POST',
                data: { key: uid },
                dataType: 'json',
                success: function (res) {
                    if (res.status) {
                        dtTable.ajax.reload(null, false);
                        updateServiceUI();
                        swlSuccess(res.message);
                    } else {
                        swlErrorHandler(res.message);
                    }
                },
                error: function () {
                    swlErrorHandler("Terjadi kesalahan saat menghapus data.");
                }
            });
        });
    });

    $('.js-service-reload').on('click', function () {
        selectedBulan = [];
        $('.month-checkbox').prop('checked', false);
        $('#dropdownBulan').text('Pilih Bulan');
        
        if (dtTable) {
            dtTable.ajax.reload(null, false);
        }
        updateActiveFiltersLabel();
        updateServiceUI();
    });

    const months = [
        "Januari", "Februari", "Maret", "April", "Mei", "Juni",
        "Juli", "Agustus", "September", "Oktober", "November", "Desember"
    ];
    let monthHtml = '';
    months.forEach((m, i) => {
        monthHtml += `
            <div class="form-check">
                <input class="form-check-input month-checkbox" type="checkbox" value="${i + 1}" id="month_${i + 1}">
                <label class="form-check-label" for="month_${i + 1}">${m}</label>
            </div>
        `;
    });
    $('#bulanList').html(monthHtml);

    $('#bulanDropdown').on('click', function(e) {
        e.stopPropagation();
    });

    $('#applyBulan').on('click', function() {
        selectedBulan = [];
        $('.month-checkbox:checked').each(function() {
            selectedBulan.push($(this).val());
        });

        if (selectedBulan.length > 6) {
            swlErrorHandler("Maksimal 6 bulan diperbolehkan untuk difilter.");
            return;
        }

        if (selectedBulan.length > 0) {
            $('#dropdownBulan').html(`<i class="bi bi-funnel-fill me-1"></i> ${selectedBulan.length} Bulan dipilih`);
        } else {
            $('#dropdownBulan').html('Pilih Bulan');
        }

        $('#dropdownBulan').dropdown('toggle');
        updateActiveFiltersLabel();
        dtTable.ajax.reload(null, false);
        updateServiceUI();
    });

    function updateActiveFiltersLabel() {
        let hasFilter = false;
        const $container = $('.active-filters-container');
        
        $container.find('.filter-badge').remove();
        
        if (selectedBulan && selectedBulan.length > 0) {
            const labels = selectedBulan.map(val => months[val - 1]);
                
            $container.append(`<span class="badge bg-light text-primary border border-primary mb-1 filter-badge" style="font-weight: 500;">Bulan: ${labels.join(', ')}</span>`);
            hasFilter = true;
        }
        
        if (hasFilter) {
            $container.addClass('d-flex').show();
        } else {
            $container.removeClass('d-flex').hide();
        }
    }

    initTable();
});
