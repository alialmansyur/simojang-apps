let selectedData = [];
let filterList = [];

const filterContainer = document.getElementById('filterList');

$.ajax({
    url: AppConfig.initGlobal + 'timkerja-list',
    method: 'POST',
    dataType: 'json',
    success: function (res) {
        filterList = res;
        filterContainer.innerHTML = '';

        filterList.forEach(data => {
            filterContainer.insertAdjacentHTML('beforeend', `
                <div class="form-check py-1">
                    <input class="form-check-input data-check"
                           type="checkbox"
                           value="${data.text}"
                           id="data${data.val}">
                    <label class="form-check-label fw-semibold"
                           for="data${data.val}">
                        ${data.text}
                    </label>
                </div>
            `);
        });
    }
});

$('#applyFilter').on('click', function () {

    selectedData = $('.data-check:checked')
        .map(function () {
            return this.value; // STRING
        })
        .get();

    if (selectedData.length) {
        const label = filterList
            .filter(f => selectedData.includes(String(f.text)))
            .map(f => f.text)
            .join(', ');

        $('#dropdownFilterBtn').text(label);
    } else {
        $('#dropdownFilterBtn').text('Pilih Tim Kerja / Bidang');
    }

    loadData(selectedData);
    table.ajax.reload();
    if (typeof loadSummaryStatistikInternal === 'function') {
        loadSummaryStatistikInternal();
    }
});
