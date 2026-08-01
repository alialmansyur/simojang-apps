const STAT_JENIS_SAMPLE = {
    'Jumlah ASN': 'apps/samples/statistik_jumlah_asn.xlsx',
    'Golongan ASN': 'apps/samples/statistik_golongan_asn.xlsx',
    'Jenis Kelamin ASN': 'apps/samples/statistik_jenis_kelamin_asn.xlsx',
    'Pendidikan ASN': 'apps/samples/statistik_pendidikan_asn.xlsx',
    'Usia ASN': 'apps/samples/statistik_usia_asn.xlsx',
    'Generasi ASN': 'apps/samples/statistik_generasi_asn.xlsx',
    'Kelompok Jabatan ASN': 'apps/samples/statistik_kelompok_jabatan_asn.xlsx',
    'Masa Kerja ASN': 'apps/samples/statistik_masa_kerja_asn.xlsx'
};

window.statistikState = window.statistikState || {
    jenis: '',
    sampleMap: STAT_JENIS_SAMPLE
};

const inputElement = document.querySelector('.basic-filepond');
const pond = inputElement ? FilePond.create(inputElement, {
    credits: false,
    instantUpload: false,
    allowMultiple: false,
    acceptedFileTypes: [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ],
    labelIdle: 'Hanya file Excel (.xls, .xlsx) <span class="filepond--label-action">Browse</span>',
    labelFileTypeNotAllowed: 'File hanya boleh Excel (.xls, .xlsx)',
    fileValidateTypeLabelExpectedTypes: 'Hanya file Excel (.xls, .xlsx) yang diperbolehkan',
    fileValidateTypeDetectType: (source, type) => new Promise((resolve) => resolve(type))
}) : null;

function resolveSamplePath(jenis) {
    const sample = window.statistikState.sampleMap[jenis] || '';
    return sample ? (AppConfig.initGlobal + sample.replace(/^\/+/, '')) : '#';
}

function updateSampleLinks(jenis) {
    const sampleUrl = resolveSamplePath(jenis);
    const hasJenis = Boolean(jenis);

    $('#doc_category').val(jenis || '');

    $('#sampleFormatLink')
        .attr('href', sampleUrl)
        .toggleClass('disabled', !hasJenis)
        .attr('aria-disabled', hasJenis ? 'false' : 'true')
        .attr('tabindex', hasJenis ? '0' : '-1');

    $('#modalSampleLink').attr('href', sampleUrl);
}

$(document).ready(function () {
    $('#applyJenis').on('click', function () {
        const jenis = String($('#statJenis').val() || '').trim();
        if (!jenis) {
            swlErrorHandler('Pilih kategori data terlebih dahulu.');
            return;
        }

        window.statistikState.jenis = jenis;
        updateSampleLinks(jenis);

        // Update active filter badge
        $('#filterCategoryBadge').text(jenis);
        $('#activeFilterContainer').css('display', 'flex').show();

        if ($.fn.DataTable.isDataTable('#dataTable')) {
            $('#dataTable').DataTable().ajax.reload(null, false);
        }

        if (typeof window.refreshStatistikSummary === 'function') {
            window.refreshStatistikSummary();
        }
    });

    $('#openUploadModal').on('click', function (e) {
        if (!window.statistikState.jenis) {
            e.preventDefault();
            swlErrorHandler('Pilih kategori data terlebih dahulu sebelum upload.');
            return;
        }
        updateSampleLinks(window.statistikState.jenis);
    });

    $('.sbmt').on('click', function (e) {
        e.preventDefault();

        if (!window.statistikState.jenis) {
            swlErrorHandler('Kategori data belum dipilih.');
            return;
        }

        const form = document.getElementById('UploadData');
        if (!pond || pond.getFiles().length === 0) {
            swlErrorHandler('Silakan pilih file Excel terlebih dahulu.');
            return;
        }

        const fd = new FormData(form);
        fd.set('doc_category', window.statistikState.jenis);

        pond.getFiles().forEach((item) => {
            fd.append('file', item.file, item.file.name);
        });

        swlwaitProsessing();

        $.ajax({
            url: AppConfig.initGlobal + 'store/import-excel-statistik',
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.status === 'error' || response.status === false) {
                    swlErrorHandler(response.message || 'Upload gagal diproses.');
                    return;
                }

                if ($.fn.DataTable.isDataTable('#dataTable')) {
                    $('#dataTable').DataTable().ajax.reload(null, false);
                }
                if (typeof window.refreshStatistikSummary === 'function') {
                    window.refreshStatistikSummary();
                }

                pond.removeFiles();
                form.reset();
                updateSampleLinks(window.statistikState.jenis);
                $('#exampleModalFullscreen').modal('hide');
                swlSuccess();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Gagal mengunggah data statistik.';
                swlErrorHandler(message);
            }
        });
    });

    updateSampleLinks('');

    $('#exampleModalFullscreen').on('hidden.bs.modal', function () {
        $('#UploadData')[0].reset();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('overflow', '');
    });
});
