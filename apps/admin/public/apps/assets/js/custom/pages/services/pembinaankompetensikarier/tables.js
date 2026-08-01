(function (window, $) {
    'use strict';

    if (!window || !$ || !$.fn || !$.fn.DataTable) return;

    const PKKApp = window.PKKApp || (window.PKKApp = {});
    const processingState = ServiceTableUI.createProcessingState('Memuat data kompetensi dan karier...');

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Number(value || 0));
    }

    function formatDateTime(value) {
        if (!value) return '-';
        return ServiceTableUI.formatDateTime(value);
    }

    function loadSummary() {
        return $.ajax({
            url: AppConfig.initGlobal + 'fetch/summary-pembinaan-kompetensi-karier',
            type: 'POST',
            dataType: 'json',
            data: {
                period_year: PKKApp.selectedYear,
                period_months: PKKApp.selectedMonths || []
            }
        }).done((response) => {
            if (!response || response.status !== 'success') return;
            const summary = response.summary || {};
            $('#pkk-recap-total').text(formatNumber(summary.total_kegiatan || 0));
            $('#pkk-recap-partisipan').text(formatNumber(summary.total_partisipan || 0));
            $('#pkk-recap-update').text(formatDateTime(summary.last_update));
        });
    }

    function initTable() {
        const emptyLottie = ServiceTableUI.createEmptyLottie();
        const table = $('#pkkTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            order: [[3, 'desc']],
            ajax: {
                url: AppConfig.initGlobal + 'fetch/data-pembinaan-kompetensi-karier',
                type: 'POST',
                data: function (d) {
                    d.period_year = PKKApp.selectedYear;
                    d.period_months = PKKApp.selectedMonths || [];
                    return d;
                }
            },
            columns: [
                {
                    data: null,
                    render: function (_d, _t, _r, meta) {
                        const info = meta.settings._iDisplayStart || 0;
                        return info + meta.row + 1;
                    }
                },
                { data: 'judul_kegiatan' },
                { data: 'materi' },
                {data: 'tanggal_kegiatan_label'},
                {
                    data: 'total_partisipan',
                    className: 'fw-semibold',
                    render: function (data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'metode',
                    render: function (data) {
                        return `<span class="badge text-bg-primary">${data || '-'}</span>`;
                    }
                },
                {
                    data: 'eviden_link',
                    render: function (data) {
                        if (!data) return '-';
                        return `<a href="${data}" target="_blank" rel="noopener noreferrer">Lihat</a>`;
                    }
                },
                { data: 'updated_at_label' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    
                    render: function (_data, _type, row) {
                        return `
                            <button class="btn btn-sm btn-primary pkk-btn-edit me-1" data-id="${row.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger pkk-btn-remove" data-id="${row.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        `;
                    }
                }
            ],
            language: {
                emptyTable: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                zeroRecords: (window.ServiceTableUI ? ServiceTableUI.createEmptyState() : 'Tidak ada data'),
                processing: processingState
            }
        });

        PKKApp.table = table;
        PKKApp.refreshSummary = loadSummary;

        ServiceTableUI.setup({
            key: 'pkk',
            table,
            disableRecap: true,
            loadSummary,
            reloadSummaryOnClick: false,
            processingText: 'Memuat data kompetensi dan karier...'
        });

        table.on('xhr.dt', function () {
            loadSummary();
        });

        loadSummary();
    }

    $(document).on('click', '#pkkTable .pkk-btn-remove', function () {
        const key = Number($(this).data('id') || 0);
        if (!key) return;

        Swal.fire({
            text: 'Apa anda yakin akan menghapus data ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d63031',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Tidak'
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                type: 'POST',
                url: AppConfig.initGlobal + 'kill/data-pembinaan-kompetensi-karier',
                data: { key },
                dataType: 'json'
            }).done((response) => {
                if (response?.status !== 'success') {
                    swlErrorHandler(response?.message || 'Gagal menghapus data.');
                    return;
                }

                swlSuccess();
                if (PKKApp.table) PKKApp.table.ajax.reload(null, false);
                if (typeof PKKApp.refreshSummary === 'function') PKKApp.refreshSummary();
            }).fail(() => {
                swlErrorHandler('Terjadi kendala saat menghapus data.');
            });
        });
    });

    $(document).on('click', '#pkkTable .pkk-btn-edit', function () {
        const key = Number($(this).data('id') || 0);
        if (!key || !PKKApp.table) return;

        let tr = $(this).closest('tr');
        if (tr.hasClass('child')) tr = tr.prev('.parent');
        const row = PKKApp.table.row(tr).data();
        if (!row) return;

        if (typeof PKKApp.openEditModal === 'function') {
            PKKApp.openEditModal(row);
        }
    });

    $(document).ready(function () {
        initTable();
    });
})(window, window.jQuery);
