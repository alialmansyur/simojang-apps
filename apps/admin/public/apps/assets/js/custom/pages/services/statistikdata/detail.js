let dtDetail = null;
let currentKey = null;
let currentJenis = '';

const DETAIL_FIELD_MAP = {
    'Jumlah ASN': ['pns', 'pppk', 'jumlah'],
    'Golongan ASN': [
        'pns_gol_i', 'pns_gol_ii', 'pns_gol_iii', 'pns_gol_iv',
        'pppk_gol_i', 'pppk_gol_ii', 'pppk_gol_iii', 'pppk_gol_iv',
        'pppk_gol_v', 'pppk_gol_vi', 'pppk_gol_vii', 'pppk_gol_viii',
        'pppk_gol_ix', 'pppk_gol_x', 'pppk_gol_xi', 'pppk_gol_xii',
        'pppk_gol_xiii', 'pppk_gol_xiv', 'pppk_gol_xv', 'pppk_gol_xvi',
        'pppk_gol_xvii', 'jumlah'
    ],
    'Jenis Kelamin ASN': ['pns_pria', 'pns_wanita', 'pppk_pria', 'pppk_wanita', 'jumlah'],
    'Pendidikan ASN': [
        'pns_sd', 'pns_smp', 'pns_sma', 'pns_d1', 'pns_d2', 'pns_d3', 'pns_s1', 'pns_s2', 'pns_s3',
        'pppk_sd', 'pppk_smp', 'pppk_sma', 'pppk_d1', 'pppk_d2', 'pppk_d3', 'pppk_s1', 'pppk_s2', 'pppk_s3', 'jumlah'
    ],
    'Usia ASN': [
        'pns_kurang_sama_31', 'pns_31_40', 'pns_41_50', 'pns_lebih_sama_51',
        'pppk_kurang_sama_31', 'pppk_31_40', 'pppk_41_50', 'pppk_lebih_sama_51', 'jumlah'
    ]
};

function toTitle(text) {
    return String(text || '')
        .replace(/_/g, ' ')
        .replace(/\b\w/g, function (m) { return m.toUpperCase(); });
}

function buildDetailColumns(row, jenis) {
    const base = ['logo', 'kode_instansi', 'nama_instansi'];
    const fields = DETAIL_FIELD_MAP[jenis] || [];
    const tail = ['upload_by', 'tanggal_upload'];

    let keys = [...base, ...fields, ...tail];

    if (row && typeof row === 'object') {
        const rowKeys = Object.keys(row);
        keys = keys.filter(function (key) { return rowKeys.includes(key); });

        if (keys.length === 0) {
            keys = rowKeys.filter(function (key) {
                return !['id', 'log_id', 'asn_log_id', 'instansi_id', 'uid'].includes(key);
            });
        }
    }

    return keys.map(function (key) {
        if (key === 'logo') {
            return {
                data: 'logo',
                title: 'Logo',
                className: 'text-center',
                render: function (data) {
                    if (data) {
                        return '<img src="apps/assets/images/instansi/' + data + '" alt="logo" style="height:20px;">';
                    }
                    return '<span class="text-muted">No Logo</span>';
                }
            };
        }

        return {
            data: key,
            title: toTitle(key),
            className: 'text-center'
        };
    });
}

function renderDetailHead(columns) {
    const header = columns.map(function (col) {
        return '<th class="text-center"><strong>' + col.title + '</strong></th>';
    }).join('');

    $('#dataTableDetailHead').html('<tr>' + header + '</tr>');
}

function openDetailModal() {
    bootstrap.Modal.getOrCreateInstance('#fileDetailModal').show();
}

function initOrReloadDetailTable(columns) {
    renderDetailHead(columns);

    if (dtDetail) {
        dtDetail.destroy();
        dtDetail = null;
    }

    dtDetail = $('#dataTableDetail').DataTable({
        autoWidth: false,
        processing: true,
        serverSide: true,
        order: [[1, 'asc']],
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print'],
        ajax: {
            url: AppConfig.initGlobal + 'fetch/detail-statistik',
            type: 'POST',
            data: function (d) {
                d.key = currentKey;
                return d;
            }
        },
        columns,
        drawCallback: function () {
            if (dtDetail) {
                dtDetail.columns.adjust().responsive.recalc();
            }
        }
    });
}

function prepareDetailColumns() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/detail-statistik',
        type: 'POST',
        dataType: 'json',
        data: {
            draw: 1,
            start: 0,
            length: 1,
            key: currentKey
        }
    }).then(function (response) {
        const firstRow = Array.isArray(response.data) && response.data.length ? response.data[0] : null;
        return buildDetailColumns(firstRow, currentJenis);
    }).catch(function () {
        return buildDetailColumns(null, currentJenis);
    });
}

$('#dataTable tbody').on('click', '.btn-detail', function (e) {
    e.preventDefault();

    currentKey = $(this).data('id');
    currentJenis = $(this).data('jenis') || (window.statistikState && window.statistikState.jenis) || '';

    if (!currentKey) {
        swlErrorHandler('Data detail tidak valid.');
        return;
    }

    openDetailModal();
    prepareDetailColumns().then(function (columns) {
        initOrReloadDetailTable(columns);
    });
});

$('#fileDetailModal').on('hidden.bs.modal', function () {
    if (dtDetail) {
        dtDetail.columns.adjust();
    }
});
