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

    updateActiveFiltersLabel();
    loadData(selectedData);
    table.ajax.reload();
    if (typeof loadSummaryStatistikInternal === 'function') {
        loadSummaryStatistikInternal();
    }
});

function updateActiveFiltersLabel() {
    const $container = $('#activeFilterContainer');
    const $list = $container.find('.active-filters-list');
    $list.empty();
    
    let hasFilters = false;

    if (selectedData.length > 0) {
        hasFilters = true;
        const labels = filterList
            .filter(f => selectedData.includes(String(f.text)))
            .map(f => f.text);
        
        $list.append(`<span class="badge bg-light text-primary border border-primary mb-1 filter-badge" style="font-weight: 500;">Tim/Bidang: ${labels.join(', ')}</span>`);
    }

    if (hasFilters) {
        $container.addClass('d-flex').show();
    } else {
        $container.removeClass('d-flex').hide();
    }
}
