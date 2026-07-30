const anggaranState = {
    years: [],
    akunOptions: [],
    strukturTree: [],
    strukturById: {},
    strukturChildMap: {},
    collapsedNodes: {},
    tahunById: {},
    levelOrder: [],
    itemRowSeq: 0,
    currentRealisasiId: null,
    strukturMode: {
        parentId: null,
        parentLevel: null,
        editMode: false
    }
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatCurrency(num) {
    const n = Number(num || 0);
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(n);
}

function normalizeNumericDecimalString(value) {
    const plain = String(value ?? '').trim().replace(/\s+/g, '');
    if (plain === '') return null;

    if (/^-?\d+$/.test(plain)) {
        return plain.replace(/^(-?)0+(?=\d)/, '$1');
    }

    if (/^-?\d+[.,]\d{1,2}$/.test(plain)) {
        const parsed = Number(plain.replace(',', '.'));
        return Number.isFinite(parsed) ? String(Math.round(parsed)) : null;
    }

    return null;
}

function normalizeCurrencyRaw(value) {
    const normalizedNumeric = normalizeNumericDecimalString(value);
    if (normalizedNumeric !== null) {
        return normalizedNumeric;
    }

    const raw = String(value ?? '').replace(/[^0-9]/g, '');
    return raw === '' ? '' : raw;
}

function formatRupiahInput(value) {
    const raw = normalizeCurrencyRaw(value);
    if (raw === '') return '';
    return `Rp ${new Intl.NumberFormat('id-ID').format(Number(raw))}`;
}

function setCurrencyInputValue($input, value) {
    if (!$input || !$input.length) return;
    const formatted = formatRupiahInput(String(value ?? ''));
    $input.val(formatted);
}

function snapshotCurrencyState($form) {
    const snapshot = [];
    $form.find('.js-currency').each(function () {
        snapshot.push({
            el: this,
            value: $(this).val()
        });
    });
    return snapshot;
}

function sanitizeCurrencyInputs($form) {
    const snapshot = snapshotCurrencyState($form);
    $form.find('.js-currency').each(function () {
        const raw = normalizeCurrencyRaw($(this).val());
        $(this).val(raw === '' ? '0' : raw);
    });
    return snapshot;
}

function restoreCurrencySnapshot(snapshot) {
    (snapshot || []).forEach((item) => {
        $(item.el).val(item.value);
    });
}

function bindCurrencyInputMask() {
    $(document).on('input', '.js-currency', function () {
        const formatted = formatRupiahInput($(this).val());
        $(this).val(formatted);
    });
}

function formatPercent(num) {
    return `${Number(num || 0).toFixed(2)}%`;
}

function formatYearMonth(value) {
    if (!value) return '-';
    const dt = new Date(value);
    if (Number.isNaN(dt.getTime())) return value;
    return `${dt.toLocaleString('id-ID', { month: 'long' })} ${dt.getFullYear()}`;
}

function formatShortDate(value) {
    if (!value) return '-';
    const dt = new Date(value);
    if (Number.isNaN(dt.getTime())) return value;
    return dt.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function getYearValueById(yearId) {
    const row = (anggaranState.years || []).find((x) => Number(x.id) === Number(yearId));
    return row ? Number(row.tahun) : null;
}

function getAkunOptionMarkup(selectedValue = '') {
    return getAkunOptionMarkupWithFallback(selectedValue);
}

function getAkunOptionMarkupWithFallback(selectedValue = '', fallbackData = {}) {
    const options = ['<option value="">Pilih akun struktur</option>'];
    const normalizedSelected = String(selectedValue ?? '').trim();
    let hasSelectedOption = false;

    (anggaranState.akunOptions || []).forEach((row) => {
        const optionValue = String(row.id ?? '').trim();
        const selected = normalizedSelected !== '' && normalizedSelected === optionValue ? 'selected' : '';
        const kode = String(row.kode || '').trim();
        const nama = String(row.nama || '').trim();
        const parentKode = String(row.parent_kode || '').trim();
        const parentNama = String(row.parent_nama || '').trim();
        const parentLevel = String(row.parent_level || '').trim();
        const tahun = String(row.tahun || '').trim();
        const label = getAkunOptionLabel({
            kode,
            nama,
            parent_kode: parentKode,
            parent_nama: parentNama,
            parent_level: parentLevel
        });

        if (selected !== '') {
            hasSelectedOption = true;
        }

        options.push(
            `<option value="${escapeHtml(optionValue)}" ${selected} data-kode="${escapeHtml(kode)}" data-nama="${escapeHtml(nama)}" data-parent-kode="${escapeHtml(parentKode)}" data-parent-nama="${escapeHtml(parentNama)}" data-parent-level="${escapeHtml(parentLevel)}" data-tahun="${escapeHtml(tahun)}" data-pagu-efektif="${escapeHtml(row.pagu_efektif || 0)}" data-posted-realisasi="${escapeHtml(row.posted_realisasi || 0)}" data-sisa-anggaran="${escapeHtml(row.sisa_anggaran || 0)}">${escapeHtml(label)}</option>`
        );
    });

    if (normalizedSelected !== '' && !hasSelectedOption) {
        const kode = String(fallbackData.struktur_kode || fallbackData.kode || '').trim();
        const nama = String(fallbackData.struktur_nama || fallbackData.nama || '').trim();
        const parentKode = String(fallbackData.parent_kode || '').trim();
        const parentNama = String(fallbackData.parent_nama || '').trim();
        const parentLevel = String(fallbackData.parent_level || '').trim();
        const tahun = String(fallbackData.tahun || '').trim();
        const label = getAkunOptionLabel(fallbackData) || normalizedSelected;

        options.push(
            `<option value="${escapeHtml(normalizedSelected)}" selected data-kode="${escapeHtml(kode)}" data-nama="${escapeHtml(nama)}" data-parent-kode="${escapeHtml(parentKode)}" data-parent-nama="${escapeHtml(parentNama)}" data-parent-level="${escapeHtml(parentLevel)}" data-tahun="${escapeHtml(tahun)}" data-pagu-efektif="${escapeHtml(fallbackData.pagu_efektif || 0)}" data-posted-realisasi="${escapeHtml(fallbackData.posted_realisasi || 0)}" data-sisa-anggaran="${escapeHtml(fallbackData.sisa_anggaran || 0)}">${escapeHtml(label)}</option>`
        );
    }

    return options.join('');
}

function getAkunOptionLabel(data = {}) {
    if (data.struktur_label) return String(data.struktur_label);

    const kode = String(data.struktur_kode || data.kode || '').trim();
    const nama = String(data.struktur_nama || data.nama || '').trim();
    const parentKode = String(data.parent_kode || '').trim();
    const parentNama = String(data.parent_nama || '').trim();
    const parentLevel = String(data.parent_level || '').trim().toLowerCase();
    const baseLabel = kode !== '' && nama !== '' ? `${kode} - ${nama}` : (nama !== '' ? nama : kode);
    const parentLabelBase = parentKode !== '' && parentNama !== '' ? `${parentKode} - ${parentNama}` : (parentNama !== '' ? parentNama : parentKode);

    if (baseLabel !== '' && parentLabelBase !== '') {
        const parentPrefix = parentLevel === 'sub_komponen' ? 'Sub Komponen' : 'Parent';
        return `${baseLabel} | ${parentPrefix}: ${parentLabelBase}`;
    }

    if (baseLabel !== '') return baseLabel;
    return '';
}

function buildInitialAkunOptionMarkup(data = {}) {
    return getAkunOptionMarkupWithFallback(data.struktur_id || '', data);
}

function getSelectedAkunFallbackData($select) {
    if (!$select || !$select.length) {
        return {};
    }

    const $option = $select.find('option:selected');
    if (!$option.length) {
        return {};
    }

    return {
        struktur_id: String($option.val() || '').trim(),
        struktur_kode: String($option.data('kode') || '').trim(),
        struktur_nama: String($option.data('nama') || '').trim(),
        parent_kode: String($option.data('parent-kode') || '').trim(),
        parent_nama: String($option.data('parent-nama') || '').trim(),
        parent_level: String($option.data('parent-level') || '').trim(),
        tahun: String($option.data('tahun') || '').trim(),
        pagu_efektif: Number($option.data('pagu-efektif') || 0),
        posted_realisasi: Number($option.data('posted-realisasi') || 0),
        sisa_anggaran: Number($option.data('sisa-anggaran') || 0),
        struktur_label: String($option.text() || '').trim()
    };
}

function initAnggaranItemSelect2(scope = '#anggaranItemTableBody') {
    $(scope).find('.anggaran-item-struktur').each(function () {
        const $select = $(this);
        const currentVal = String($select.val() || '').trim();

        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        if (typeof $.fn.select2 === 'function') {
            $select.select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#AnggaranModal'),
                width: '100%',
                placeholder: 'Pilih akun struktur',
                allowClear: true,
                language: {
                    noResults: function () {
                        return 'Akun struktur tidak ditemukan';
                    },
                    searching: function () {
                        return 'Mencari akun struktur...';
                    }
                }
            });

            $select.off('select2:open.anggaran').on('select2:open.anggaran', function () {
                const searchField = document.querySelector('.select2-container--open .select2-search__field');
                if (searchField) {
                    searchField.focus();
                }
            });
        }

        if (currentVal !== '') {
            $select.val(currentVal).trigger('change.select2');
        }
    });
}

function createAnggaranItemRow(data = {}) {
    anggaranState.itemRowSeq += 1;
    const rowId = `anggaran-item-${anggaranState.itemRowSeq}`;
    return `
        <tr data-row-id="${rowId}" class="anggaran-item-row">
            <td class="anggaran-item-cell anggaran-item-cell-structure">
                <select class="form-select form-select-sm select2 anggaran-item-struktur" name="item_struktur_id[]" data-placeholder="Pilih akun struktur" required>
                    ${buildInitialAkunOptionMarkup(data)}
                </select>
                <div class="anggaran-item-budget-meta">
                    <span>Pagu Efektif: <strong class="js-budget-pagu">Rp0</strong></span>
                    <span>Sisa Anggaran: <strong class="js-budget-sisa">Rp0</strong></span>
                </div>
            </td>
            <td class="anggaran-item-cell anggaran-item-cell-nominal">
                <div class="anggaran-item-field-stack">
                <input type="text" class="form-control form-control-sm js-currency anggaran-item-nominal" name="item_nominal[]" inputmode="numeric" autocomplete="off" placeholder="Rp 0" value="${escapeHtml(formatRupiahInput(data.nominal || ''))}" required>
                    <div class="anggaran-item-budget-note js-budget-note">Pilih akun struktur untuk melihat sisa anggaran.</div>
                </div>
            </td>
            <td class="anggaran-item-cell anggaran-item-cell-keterangan">
                <input type="text" class="form-control form-control-sm" name="item_keterangan[]" placeholder="Keterangan item" value="${escapeHtml(data.keterangan || '')}">
            </td>
            <td class="text-center anggaran-item-cell anggaran-item-cell-action">
                <div class="anggaran-item-action-wrap">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-anggaran-item" aria-label="Hapus item">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
}

function updateAnggaranItemSummary() {
    let count = 0;
    let total = 0;

    $('#anggaranItemTableBody tr').each(function () {
        count += 1;
        const nominal = normalizeCurrencyRaw($(this).find('.anggaran-item-nominal').val());
        total += Number(nominal || 0);
    });

    $('#anggaranItemCount').text(new Intl.NumberFormat('id-ID').format(count));
    $('#anggaranItemTotal').text(formatCurrency(total));
    $('#anggaranItemEmpty').toggleClass('d-none', count > 0);
    updateAllAnggaranItemBudgetInfo();
}

function appendAnggaranItemRow(data = {}) {
    $('#anggaranItemTableBody').append(createAnggaranItemRow(data));
    initAnggaranItemSelect2(`#anggaranItemTableBody tr[data-row-id="anggaran-item-${anggaranState.itemRowSeq}"]`);
    updateAnggaranItemSummary();
}

function refreshAnggaranItemSelectOptions() {
    $('#anggaranItemTableBody .anggaran-item-struktur').each(function () {
        const $select = $(this);
        const selected = String($select.val() || '').trim();
        const selectedRow = (anggaranState.akunOptions || []).find((row) => Number(row.id) === Number(selected)) || getSelectedAkunFallbackData($select);

        $select.html(getAkunOptionMarkupWithFallback(selected, selectedRow));
        if (selected !== '') {
            $select.val(selected);
        }
    });
    initAnggaranItemSelect2();
    updateAllAnggaranItemBudgetInfo();
}

function resetFormAnggaran() {
    const form = $('#form-anggaran')[0];
    if (form) form.reset();

    anggaranState.currentRealisasiId = null;
    $('#anggaran_key').val('');
    $('#AnggaranModalLabel').text('Tambah Realisasi Anggaran');
    $('#anggaran_status').val('PENDING');
    $('#anggaran_keterangan').val('');
    $('#anggaranItemTableBody').empty();
    appendAnggaranItemRow();
}

function populateFormAnggaran(detail) {
    const header = detail?.header || {};
    const items = detail?.items || [];
    const form = $('#form-anggaran');

    anggaranState.currentRealisasiId = Number(header.id || 0) || null;
    $('#AnggaranModalLabel').text('Ubah Realisasi Anggaran');
    $('#anggaran_key').val(header.id || '');
    form.find('[name="tahun_id"]').val(header.tahun_id || '');
    form.find('[name="period_date"]').val((header.period_date || '').substring(0, 7));
    form.find('[name="no_spm"]').val(header.no_spm || '');
    form.find('[name="spm_date"]').val(header.spm_date || '');
    form.find('[name="no_sp2d"]').val(header.no_sp2d || '');
    form.find('[name="sp2d_date"]').val(header.sp2d_date || '');
    form.find('[name="status"]').val(header.status || 'PENDING');
    form.find('[name="keterangan"]').val(header.keterangan || '');

    $('#anggaranItemTableBody').empty();
    if (!items.length) {
        appendAnggaranItemRow();
        return;
    }

    items.forEach((item) => {
        appendAnggaranItemRow({
            struktur_id: item.struktur_id,
            nominal: item.nominal,
            keterangan: item.keterangan,
            struktur_kode: item.struktur_kode,
            struktur_nama: item.struktur_nama,
            parent_kode: item.parent_kode,
            parent_nama: item.parent_nama,
            parent_level: item.parent_level,
            pagu_efektif: item.pagu_efektif || 0,
            posted_realisasi: item.posted_realisasi || 0,
            sisa_anggaran: item.sisa_anggaran || 0
        });
    });
}

function getAkunOptionData($select) {
    if (!$select || !$select.length) return null;

    const selectedValue = String($select.val() || '').trim();
    if (selectedValue === '') {
        return null;
    }

    return (anggaranState.akunOptions || []).find((row) => String(row.id || '') === selectedValue)
        || getSelectedAkunFallbackData($select);
}

function getCurrentFormNominalTotalsByStruktur() {
    const totals = {};

    $('#anggaranItemTableBody tr').each(function () {
        const strukturId = String($(this).find('.anggaran-item-struktur').val() || '').trim();
        if (strukturId === '') {
            return;
        }

        const nominal = Number(normalizeCurrencyRaw($(this).find('.anggaran-item-nominal').val()) || 0);
        totals[strukturId] = Number(totals[strukturId] || 0) + nominal;
    });

    return totals;
}

function updateAllAnggaranItemBudgetInfo() {
    const nominalTotals = getCurrentFormNominalTotalsByStruktur();

    $('#anggaranItemTableBody tr').each(function () {
        const $row = $(this);
        const $select = $row.find('.anggaran-item-struktur');
        const akunData = getAkunOptionData($select);
        const $pagu = $row.find('.js-budget-pagu');
        const $sisa = $row.find('.js-budget-sisa');
        const $note = $row.find('.js-budget-note');

        if (!akunData) {
            $pagu.text(formatCurrency(0));
            $sisa.text(formatCurrency(0));
            $note
                .removeClass('is-warning')
                .text('Pilih akun struktur untuk melihat sisa anggaran.');
            return;
        }

        const strukturId = String(akunData.struktur_id || akunData.id || '').trim();
        const paguEfektif = Number(akunData.pagu_efektif || 0);
        const sisaAnggaran = Number(akunData.sisa_anggaran || 0);
        const totalNominalStruktur = Number(nominalTotals[strukturId] || 0);
        const sisaSetelahInput = sisaAnggaran - totalNominalStruktur;
        const isOverBudget = sisaSetelahInput < 0;

        $pagu.text(formatCurrency(paguEfektif));
        $sisa.text(formatCurrency(sisaAnggaran));
        $note
            .toggleClass('is-warning', isOverBudget)
            .text(
                isOverBudget
                    ? `Input melebihi sisa anggaran sebesar ${formatCurrency(Math.abs(sisaSetelahInput))}.`
                    : `Sisa setelah input akun ini: ${formatCurrency(sisaSetelahInput)}`
            );
    });
}

function resetTahunForm() {
    const form = $('#form-tahun-anggaran')[0];
    if (form) form.reset();
    $('#tahun_key').val('');
    $('#tahun_is_active').prop('checked', false);
    $('#AnggaranYearEditorLabel').text('Tambah Master Tahun');
}

function formatLevelLabel(level) {
    const raw = String(level ?? '').trim();
    if (raw === '' || raw === 'root') return 'ROOT';
    return raw.toUpperCase().replace(/_/g, ' ');
}

function buildLevelOptions(allowedLevels, selected = '') {
    const $level = $('#struktur_level');
    $level.empty();

    (allowedLevels || []).forEach((level) => {
        const normalized = typeof level === 'string' ? level : String(level?.value ?? '');
        const label = typeof level === 'string' ? formatLevelLabel(level) : String(level?.label ?? formatLevelLabel(normalized));
        const isSelected = String(normalized) === String(selected) ? 'selected' : '';
        $level.append(`<option value="${normalized}" ${isSelected}>${label}</option>`);
    });
}

function updateStrukturHint(text) {
    $('#strukturParentHint').text(text);
}

function toggleAkunBudgetFields(level) {
    const isAkun = String(level) === 'akun';
    const $pagu = $('#form-struktur-anggaran [name="pagu_revisi"]');
    const $lock = $('#form-struktur-anggaran [name="lock_pagu"]');
    $pagu.prop('readonly', !isAkun);
    $lock.prop('readonly', !isAkun);
    $pagu.toggleClass('bg-light', !isAkun);
    $lock.toggleClass('bg-light', !isAkun);
}

function setupStrukturRootMode() {
    anggaranState.strukturMode = {
        parentId: null,
        parentLevel: null,
        editMode: false
    };

    $('#struktur_parent_id').val('');
    $('#struktur_key').val('');
    $('#AnggaranStrukturEditorLabel').text('Tambah Struktur Root');
    buildLevelOptions(['unit'], 'unit');
    toggleAkunBudgetFields('unit');
    setCurrencyInputValue($('#form-struktur-anggaran [name="pagu_revisi"]'), '');
    setCurrencyInputValue($('#form-struktur-anggaran [name="lock_pagu"]'), '');
    updateStrukturHint('Mode: tambah root (level UNIT).');
}

function setupStrukturAppendMode(parentRow) {
    if (!parentRow) {
        setupStrukturRootMode();
        return;
    }

    const nextLevel = parentRow.next_level;
    if (!nextLevel) {
        swlErrorHandler('Level ini sudah AKUN, tidak bisa ditambah child lagi.');
        return;
    }

    anggaranState.strukturMode = {
        parentId: Number(parentRow.id),
        parentLevel: String(parentRow.level || ''),
        editMode: false
    };

    $('#AnggaranStrukturEditorLabel').text('Tambah Child Struktur');
    $('#struktur_key').val('');
    $('#struktur_parent_id').val(parentRow.id);
    $('#struktur_tahun').val(parentRow.tahun || '');
    buildLevelOptions([nextLevel], nextLevel);
    toggleAkunBudgetFields(nextLevel);
    setCurrencyInputValue($('#form-struktur-anggaran [name="pagu_revisi"]'), '');
    setCurrencyInputValue($('#form-struktur-anggaran [name="lock_pagu"]'), '');
    updateStrukturHint(`Mode: append child dari ${parentRow.nama} (${formatLevelLabel(parentRow.display_level || parentRow.level)})`);
}

function setupStrukturEditMode(row) {
    const parentRow = row.parent_id ? anggaranState.strukturById[Number(row.parent_id)] : null;

    anggaranState.strukturMode = {
        parentId: row.parent_id ? Number(row.parent_id) : null,
        parentLevel: parentRow ? String(parentRow.level || '') : null,
        editMode: true
    };

    $('#AnggaranStrukturEditorLabel').text('Ubah Struktur Anggaran');
    const form = $('#form-struktur-anggaran');
    form.find('[name="key"]').val(row.id);
    form.find('[name="parent_id"]').val(row.parent_id || '');
    form.find('[name="tahun"]').val(row.tahun || '');
    form.find('[name="kode"]').val(row.kode || '');
    form.find('[name="nama"]').val(row.nama || '');
    setCurrencyInputValue(form.find('[name="pagu_revisi"]'), row.pagu_revisi || 0);
    setCurrencyInputValue(form.find('[name="lock_pagu"]'), row.lock_pagu || 0);

    buildLevelOptions([
        {
            value: row.level || '',
            label: formatLevelLabel(row.display_level || row.level)
        }
    ], row.level || '');
    toggleAkunBudgetFields(row.level || '');

    if (parentRow) {
        updateStrukturHint(`Mode: edit data child dari ${parentRow.nama}.`);
    } else {
        updateStrukturHint('Mode: edit data root.');
    }
}

function resetStrukturForm() {
    const form = $('#form-struktur-anggaran')[0];
    if (form) form.reset();
    $('#struktur_key').val('');
    $('#struktur_parent_id').val('');
    setupStrukturRootMode();
}

function renderYearSelectors() {
    const years = [...(anggaranState.years || [])].sort((a, b) => Number(b.tahun || 0) - Number(a.tahun || 0));
    const filterValue = $('#filterTahun').val();
    const masterFilterValue = $('#masterFilterTahun').val();
    const formYearId = $('#anggaran_tahun_id').val();

    const $filter = $('#filterTahun');
    const $masterFilter = $('#masterFilterTahun');
    const $formYear = $('#anggaran_tahun_id');

    $filter.html('<option value="">Semua Tahun</option>');
    $masterFilter.html('<option value="">Semua Tahun</option>');
    $formYear.html('<option value="">Pilih Tahun</option>');

    years.forEach((row) => {
        const yearVal = Number(row.tahun || 0);
        const activeTag = Number(row.is_active || 0) === 1 ? ' (Aktif)' : '';
        $filter.append(`<option value="${yearVal}">${yearVal}</option>`);
        $masterFilter.append(`<option value="${yearVal}">${yearVal}</option>`);
        $formYear.append(`<option value="${row.id}" data-tahun="${yearVal}">${yearVal}${activeTag}</option>`);
    });

    if (filterValue && years.some((x) => Number(x.tahun) === Number(filterValue))) {
        $filter.val(filterValue);
    }

    if (masterFilterValue && years.some((x) => Number(x.tahun) === Number(masterFilterValue))) {
        $masterFilter.val(masterFilterValue);
    }

    if (formYearId && years.some((x) => Number(x.id) === Number(formYearId))) {
        $formYear.val(formYearId);
    } else {
        const activeYear = years.find((x) => Number(x.is_active || 0) === 1);
        if (activeYear) {
            $formYear.val(activeYear.id);
        }
    }
}

function renderSummaryCards(summary) {
    const s = summary || {};
    $('#sumTotalRecord').text(new Intl.NumberFormat('id-ID').format(Number(s.total_record || 0)));
    $('#sumTotalAkun').text(new Intl.NumberFormat('id-ID').format(Number(s.total_struktur_akun || 0)));
    $('#sumPaguRevisi').text(formatCurrency(s.total_pagu_revisi || 0));
    $('#sumLockPagu').text(formatCurrency(s.total_lock_pagu || 0));
    $('#sumPaguEfektif').text(formatCurrency(s.total_pagu_efektif || 0));
    $('#sumRealisasi').text(formatCurrency(s.total_realisasi || 0));
    $('#sumCapaian').text(formatPercent(s.realisasi_persen || 0));
    $('#sumTarget').text(formatPercent(s.target_persen || 0));
    $('#sumGapTarget').text(formatPercent(s.gap_target_persen || 0));
}

function renderAnggaranDetailSummary(header = {}) {
    const summaryHtml = `
        <div class="anggaran-detail-card">
            <span class="anggaran-detail-card-label">Tahun</span>
            <div class="anggaran-detail-card-value">${escapeHtml(header.tahun || '-')}</div>
        </div>
        <div class="anggaran-detail-card">
            <span class="anggaran-detail-card-label">Periode</span>
            <div class="anggaran-detail-card-value">${escapeHtml(formatYearMonth(header.period_date || ''))}</div>
        </div>
        <div class="anggaran-detail-card">
            <span class="anggaran-detail-card-label">SPM / SP2D</span>
            <div class="anggaran-detail-card-value">${escapeHtml(header.no_spm || '-')} / ${escapeHtml(header.no_sp2d || '-')}</div>
        </div>
        <div class="anggaran-detail-card">
            <span class="anggaran-detail-card-label">Status</span>
            <div class="anggaran-detail-card-value">${escapeHtml(header.status || '-')}</div>
        </div>
    `;

    $('#anggaranDetailSummary').html(summaryHtml);
}

function renderAnggaranDetailItems(items = []) {
    const $tbody = $('#anggaranDetailTableBody');
    $tbody.empty();

    if (!items.length) {
        $tbody.html('<tr><td colspan="4" class="text-center text-muted">Belum ada item realisasi.</td></tr>');
        $('#anggaranDetailItemCount').text('0');
        $('#anggaranDetailTotalNominal').text(formatCurrency(0));
        return;
    }

    let totalNominal = 0;
    items.forEach((item, index) => {
        totalNominal += Number(item.nominal || 0);
        const akunLabel = escapeHtml(getAkunOptionLabel(item) || '-');
        const keterangan = escapeHtml(item.keterangan || '-');

        $tbody.append(`
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${akunLabel}</td>
                <td class="text-end">${formatCurrency(item.nominal || 0)}</td>
                <td>${keterangan}</td>
            </tr>
        `);
    });

    $('#anggaranDetailItemCount').text(new Intl.NumberFormat('id-ID').format(items.length));
    $('#anggaranDetailTotalNominal').text(formatCurrency(totalNominal));
}

function renderLevelFilterOptions() {
    const currentValue = $('#masterLevelFilter').val();
    const $levelFilter = $('#masterLevelFilter');
    if (!$levelFilter.length) return;

    $levelFilter.html('<option value="">Semua Level</option>');
    (anggaranState.levelOrder || []).forEach((level) => {
        $levelFilter.append(`<option value="${level}">${formatLevelLabel(level)}</option>`);
    });

    if (currentValue && (anggaranState.levelOrder || []).includes(currentValue)) {
        $levelFilter.val(currentValue);
    }
}

function getFilteredYearRows() {
    const keyword = String($('#tahunSearchFilter').val() || '').trim().toLowerCase();
    const status = String($('#tahunStatusFilter').val() || '').trim();
    const rows = [...(anggaranState.years || [])].sort((a, b) => Number(b.tahun || 0) - Number(a.tahun || 0));

    return rows.filter((row) => {
        const isActive = Number(row.is_active || 0) === 1;
        if (status === 'active' && !isActive) return false;
        if (status === 'inactive' && isActive) return false;

        if (!keyword) return true;

        const searchBlob = [
            row.tahun,
            row.target_persen,
            isActive ? 'aktif' : 'nonaktif'
        ].join(' ').toLowerCase();

        return searchBlob.includes(keyword);
    });
}

function renderYearTable() {
    const rows = getFilteredYearRows();
    const $tbody = $('#tahunTable tbody');
    $tbody.empty();
    anggaranState.tahunById = {};

    if (!rows.length) {
        $tbody.html('<tr><td colspan="5" class="text-center text-muted">Master tahun belum tersedia atau tidak sesuai filter.</td></tr>');
        return;
    }

    (anggaranState.years || []).forEach((row) => {
        anggaranState.tahunById[Number(row.id)] = row;
    });

    rows.forEach((row) => {
        const badge = Number(row.is_active || 0) === 1
            ? '<span class="badge text-bg-success">Aktif</span>'
            : '<span class="badge text-bg-secondary">Nonaktif</span>';

        $tbody.append(`
            <tr>
                <td>${row.tahun}</td>
                <td>${formatPercent(row.target_persen || 0)}</td>
                <td>${badge}</td>
                <td>${row.created_at || '-'}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary me-1 btn-edit-tahun" data-id="${row.id}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-del-tahun" data-id="${row.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });
}

function buildStrukturChildMap(rows) {
    const childMap = {};
    (rows || []).forEach((row) => {
        const parentId = Number(row.parent_id || 0);
        if (!childMap[parentId]) {
            childMap[parentId] = [];
        }
        childMap[parentId].push(Number(row.id));
    });
    anggaranState.strukturChildMap = childMap;
}

function pruneCollapsedNodes() {
    const validIds = new Set((anggaranState.strukturTree || []).map((row) => Number(row.id)));
    Object.keys(anggaranState.collapsedNodes).forEach((id) => {
        if (!validIds.has(Number(id))) {
            delete anggaranState.collapsedNodes[id];
        }
    });
}

function isStrukturRowVisible(row) {
    let parentId = Number(row.parent_id || 0);
    while (parentId > 0) {
        if (anggaranState.collapsedNodes[parentId]) {
            return false;
        }

        const parentRow = anggaranState.strukturById[parentId];
        if (!parentRow) {
            break;
        }

        parentId = Number(parentRow.parent_id || 0);
    }
    return true;
}

function getStrukturVisibleIdsByFilter() {
    const keyword = String($('#masterSearchStruktur').val() || '').trim().toLowerCase();
    const level = String($('#masterLevelFilter').val() || '').trim();

    if (keyword === '' && level === '') {
        return null;
    }

    const visibleIds = new Set();

    (anggaranState.strukturTree || []).forEach((row) => {
        const rowLevel = String(row.level || '').trim();
        const searchBlob = [
            row.kode,
            row.nama,
            row.tahun,
            formatLevelLabel(row.display_level || row.level)
        ].join(' ').toLowerCase();

        const keywordMatch = keyword === '' || searchBlob.includes(keyword);
        const levelMatch = level === '' || rowLevel === level;

        if (!keywordMatch || !levelMatch) {
            return;
        }

        let currentId = Number(row.id || 0);
        while (currentId > 0) {
            visibleIds.add(currentId);
            const currentRow = anggaranState.strukturById[currentId];
            currentId = currentRow ? Number(currentRow.parent_id || 0) : 0;
        }
    });

    return visibleIds;
}

function applyStrukturCollapseVisibility() {
    const filteredIds = getStrukturVisibleIdsByFilter();
    $('#strukturTable tbody tr').each(function () {
        const id = Number($(this).data('id'));
        const row = anggaranState.strukturById[id];
        let visible = row ? isStrukturRowVisible(row) : true;
        if (filteredIds instanceof Set) {
            visible = filteredIds.has(id);
        }
        $(this).toggleClass('row-hidden', !visible);
    });
}

function setCollapseForAll(shouldCollapse) {
    Object.keys(anggaranState.strukturChildMap || {}).forEach((parentId) => {
        if (Number(parentId) <= 0) return;
        const children = anggaranState.strukturChildMap[parentId] || [];
        if (!children.length) return;
        anggaranState.collapsedNodes[parentId] = shouldCollapse;
    });
    renderStrukturTable();
}

function renderStrukturTable() {
    const rows = anggaranState.strukturTree || [];
    const $tbody = $('#strukturTable tbody');
    $tbody.empty();
    anggaranState.strukturById = {};
    buildStrukturChildMap(rows);
    pruneCollapsedNodes();

    if (!rows.length) {
        $tbody.html('<tr><td colspan="8" class="text-center text-muted">Data struktur belum tersedia.</td></tr>');
        return;
    }

    rows.forEach((row) => {
        anggaranState.strukturById[Number(row.id)] = row;
    });

    rows.forEach((row) => {
        const rowId = Number(row.id);
        const parentId = Number(row.parent_id || 0);
        const depth = Number(row.depth || 0);
        const indent = depth * 20;
        const children = anggaranState.strukturChildMap[rowId] || [];
        const hasChildren = children.length > 0;
        const isCollapsed = hasChildren && Boolean(anggaranState.collapsedNodes[rowId]);
        const toggleIcon = isCollapsed ? 'bi-chevron-right' : 'bi-chevron-down';
        const rowVisibleClass = isStrukturRowVisible(row) ? '' : 'row-hidden';
        const appendDisabled = row.next_level ? '' : 'disabled';
        const toggleButton = hasChildren
            ? `<button type="button" class="tree-toggle btn-toggle-struktur" data-id="${rowId}" aria-label="Toggle Child">
                    <i class="bi ${toggleIcon}"></i>
               </button>`
            : `<span class="tree-toggle-placeholder"></span>`;
        const codeText = escapeHtml(row.kode || '-');
        const nameText = escapeHtml(row.nama || '-');

        $tbody.append(`
            <tr class="${rowVisibleClass}" data-id="${rowId}" data-parent-id="${parentId}" data-depth="${depth}">
                <td>
                    <div class="tree-label">
                        <span class="tree-indent" style="width:${indent}px"></span>
                        ${toggleButton}
                        <div class="tree-meta">
                            <span class="tree-node">${nameText}</span>
                            <span class="tree-meta-code">${codeText}</span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge text-bg-light border tree-level-badge">${formatLevelLabel(row.display_level || row.level)}</span>
                </td>
                <td class="text-center">${row.tahun || '-'}</td>
                <td class="text-end">${formatCurrency(row.realisasi || 0)}</td>
                <td class="text-end">${formatCurrency(row.pagu_revisi || 0)}</td>
                <td class="text-end">${formatCurrency(row.lock_pagu || 0)}</td>
                <td class="text-end">${formatCurrency(row.pagu_efektif || 0)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-primary me-1 btn-edit-struktur" data-id="${row.id}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary me-1 btn-append-struktur" data-id="${row.id}" ${appendDisabled}>
                        <i class="bi bi-plus"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger btn-del-struktur" data-id="${row.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `);
    });

    applyStrukturCollapseVisibility();
}

function confirmDelete(message, callback) {
    Swal.fire({
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d63031',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) callback();
    });
}

function getAnggaranFilterPayload() {
    return {
        tahun: $('#filterTahun').val(),
        date_mode: $('#filterDateMode').val(),
        date_start: $('#filterDateStart').val(),
        date_end: $('#filterDateEnd').val()
    };
}

function loadSummaryAnggaran() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/summary-anggaran',
        type: 'POST',
        dataType: 'json',
        data: getAnggaranFilterPayload(),
        success: function (response) {
            if (response?.status !== 'success') return;
            renderSummaryCards(response.summary || {});
        }
    });
}

function loadAnggaranOptions(tahun = null, realisasiId = null) {
    const activeRealisasiId = realisasiId ?? anggaranState.currentRealisasiId ?? ($('#anggaran_key').val() || '');
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/options-anggaran',
        type: 'POST',
        dataType: 'json',
        data: {
            tahun: tahun || $('#filterTahun').val(),
            realisasi_id: activeRealisasiId
        },
        success: function (response) {
            if (response?.status !== 'success') return;
            anggaranState.years = response.years || [];
            anggaranState.akunOptions = response.akun_options || [];
            renderYearSelectors();
            refreshAnggaranItemSelectOptions();
        }
    });
}

function loadAnggaranSettings() {
    return $.ajax({
        url: AppConfig.initGlobal + 'fetch/settings-anggaran',
        type: 'POST',
        dataType: 'json',
        data: {
            tahun_master: $('#masterFilterTahun').val()
        },
        success: function (response) {
            if (response?.status !== 'success') return;
            anggaranState.years = response.years || [];
            anggaranState.levelOrder = response.level_order || [];
            anggaranState.strukturTree = response.struktur || [];

            renderYearSelectors();
            renderLevelFilterOptions();
            renderYearTable();
            renderStrukturTable();
        }
    });
}

function reloadAnggaranTable() {
    if (window.dtAnggaran && $.fn.DataTable.isDataTable('#dataTableAnggaran')) {
        window.dtAnggaran.ajax.reload(null, false);
    }
}

function reloadAnggaranPageData() {
    reloadAnggaranTable();
    loadAnggaranSettings().then(() => {
        const selectedYear = getYearValueById($('#anggaran_tahun_id').val());
        loadAnggaranOptions(selectedYear);
    });
}

function syncDateFilterState() {
    const active = $('#filterDateMode').val() !== '';
    $('#filterDateStart, #filterDateEnd').prop('disabled', !active);
    if (!active) {
        $('#filterDateStart, #filterDateEnd').val('');
    }
}

function openCreateTahunModal() {
    resetTahunForm();
    $('#AnggaranYearEditorModal').modal('show');
}

function openEditTahunModal(row) {
    if (!row) return;

    resetTahunForm();
    $('#AnggaranYearEditorLabel').text('Ubah Master Tahun');
    $('#tahun_key').val(row.id);
    $('#form-tahun-anggaran [name="tahun"]').val(row.tahun || '');
    $('#form-tahun-anggaran [name="target_persen"]').val(row.target_persen || 0);
    $('#tahun_is_active').prop('checked', Number(row.is_active || 0) === 1);
    $('#AnggaranYearEditorModal').modal('show');
}

function openStrukturRootModal() {
    resetStrukturForm();
    setupStrukturRootMode();
    $('#AnggaranStrukturEditorModal').modal('show');
}

function openStrukturAppendModal(row) {
    if (!row) return;
    const form = $('#form-struktur-anggaran')[0];
    if (form) form.reset();
    $('#struktur_key').val('');
    setupStrukturAppendMode(row);
    $('#AnggaranStrukturEditorModal').modal('show');
}

function openStrukturEditModal(row) {
    if (!row) return;
    resetStrukturForm();
    setupStrukturEditMode(row);
    $('#AnggaranStrukturEditorModal').modal('show');
}

function openCreateRealisasi() {
    resetFormAnggaran();
    const selectedYear = getYearValueById($('#anggaran_tahun_id').val());
    loadAnggaranOptions(selectedYear).always(function () {
        $('#AnggaranModal').modal('show');
    });
}

function openEditRealisasi(row) {
    if (!row) return;

    $.ajax({
        url: AppConfig.initGlobal + 'fetch/detail-anggaran',
        type: 'POST',
        dataType: 'json',
        data: { key: row.id },
        success: function (response) {
            if (response?.status !== 'success') {
                swlErrorHandler(response?.message || 'Gagal memuat detail realisasi.');
                return;
            }

            const detail = response.data || {};
            const yearValue = Number(detail?.header?.tahun || 0) || getYearValueById(detail?.header?.tahun_id) || null;
            loadAnggaranOptions(yearValue, detail?.header?.id || null).always(function () {
                resetFormAnggaran();
                populateFormAnggaran(detail);
                $('#AnggaranModal').modal('show');
            });
        },
        error: function () {
            swlErrorHandler('Terjadi kesalahan saat memuat detail realisasi.');
        }
    });
}

function openViewRealisasi(row) {
    if (!row) return;

    $.ajax({
        url: AppConfig.initGlobal + 'fetch/detail-anggaran',
        type: 'POST',
        dataType: 'json',
        data: { key: row.id },
        success: function (response) {
            if (response?.status !== 'success') {
                swlErrorHandler(response?.message || 'Gagal memuat detail realisasi.');
                return;
            }

            const detail = response.data || {};
            renderAnggaranDetailSummary(detail.header || {});
            renderAnggaranDetailItems(detail.items || []);
            $('#AnggaranViewModal').modal('show');
        },
        error: function () {
            swlErrorHandler('Terjadi kesalahan saat memuat detail realisasi.');
        }
    });
}

window.formatCurrency = formatCurrency;
window.formatYearMonth = formatYearMonth;
window.formatShortDate = formatShortDate;
window.openEditRealisasi = openEditRealisasi;
window.openViewRealisasi = openViewRealisasi;
window.loadSummaryAnggaran = loadSummaryAnggaran;
window.loadAnggaranSettings = loadAnggaranSettings;
window.loadAnggaranOptions = loadAnggaranOptions;
window.reloadAnggaranPageData = reloadAnggaranPageData;
window.confirmDelete = confirmDelete;

function buildAnggaranExportUrl() {
    const params = new URLSearchParams();
    const filters = getAnggaranFilterPayload();

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== null && value !== undefined && String(value).trim() !== '') {
            params.set(key, value);
        }
    });

    if (window.dtAnggaran && typeof window.dtAnggaran.search === 'function') {
        const searchValue = String(window.dtAnggaran.search() || '').trim();
        if (searchValue !== '') {
            params.set('search', searchValue);
        }

        const order = typeof window.dtAnggaran.order === 'function' ? window.dtAnggaran.order() : [];
        if (Array.isArray(order) && order.length && Array.isArray(order[0])) {
            params.set('order_column', order[0][0]);
            params.set('order_dir', order[0][1]);
        }
    }

    return `${AppConfig.initGlobal}export/excel-anggaran?${params.toString()}`;
}

$(document).ready(function () {
    bindCurrencyInputMask();
    setupStrukturRootMode();
    resetTahunForm();
    resetFormAnggaran();
    syncDateFilterState();

    loadAnggaranSettings().then(() => {
        const selectedYear = getYearValueById($('#anggaran_tahun_id').val());
        loadAnggaranOptions(selectedYear);
        loadSummaryAnggaran();
    });

    $('.btn-save-anggaran').on('click', function () {
        $('#form-anggaran').trigger('submit');
    });

    $('#openCreateAnggaran').on('click', function () {
        openCreateRealisasi();
    });

    $('#anggaran-export-excel').on('click', function () {
        window.open(buildAnggaranExportUrl(), '_blank');
    });

    $('#addAnggaranItemRow').on('click', function () {
        appendAnggaranItemRow();
    });

    $('#anggaranItemTableBody').on('click', '.btn-remove-anggaran-item', function () {
        $(this).closest('tr').remove();
        updateAnggaranItemSummary();
    });

    $('#anggaranItemTableBody').on('input change', '.anggaran-item-nominal, .anggaran-item-struktur, [name="item_keterangan[]"]', function () {
        updateAnggaranItemSummary();
    });

    $('#filterTahun').on('change', function () {
        reloadAnggaranTable();
        loadAnggaranOptions($(this).val());
    });

    $('#filterDateMode').on('change', function () {
        syncDateFilterState();
        reloadAnggaranTable();
    });

    $('#filterDateStart, #filterDateEnd').on('change', function () {
        if (!$('#filterDateMode').val()) return;
        reloadAnggaranTable();
    });

    $('#anggaran_tahun_id').on('change', function () {
        const tahun = getYearValueById($(this).val());
        loadAnggaranOptions(tahun, anggaranState.currentRealisasiId);
    });

    $('#form-anggaran').on('submit', function (e) {
        e.preventDefault();
        swlwaitProsessing();
        const $form = $(this);
        const currencySnapshot = sanitizeCurrencyInputs($form);

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-data-anggaran',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
            success: function (response) {
                if (response?.status === 'error') {
                    swlErrorHandler(response.message || 'Gagal menyimpan data realisasi.');
                    return;
                }
                $('#AnggaranModal').modal('hide');
                reloadAnggaranPageData();
                swlSuccess(response?.message || 'Data berhasil disimpan.');
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan saat menyimpan data realisasi.');
            },
            complete: function () {
                restoreCurrencySnapshot(currencySnapshot);
            }
        });
    });

    $('#AnggaranModal').on('hidden.bs.modal', function () {
        resetFormAnggaran();
        const tahun = getYearValueById($('#anggaran_tahun_id').val());
        loadAnggaranOptions(tahun);
    });

    $('#AnggaranModal').on('shown.bs.modal', function () {
        initAnggaranItemSelect2();
        updateAllAnggaranItemBudgetInfo();
    });

    $('#openCreateTahun').on('click', function () {
        openCreateTahunModal();
    });

    $('#refreshTahunMaster').on('click', function () {
        loadAnggaranSettings();
    });

    $('#submitTahunForm').on('click', function () {
        $('#form-tahun-anggaran').trigger('submit');
    });

    $('#resetTahunForm').on('click', function () {
        resetTahunForm();
    });

    $('#tahunSearchFilter, #tahunStatusFilter').on('input change', function () {
        renderYearTable();
    });

    $('#form-tahun-anggaran').on('submit', function (e) {
        e.preventDefault();
        const payload = $(this).serializeArray().filter((item) => item.name !== 'is_active');
        const checked = $('#tahun_is_active').is(':checked');
        payload.push({ name: 'is_active', value: checked ? 1 : 0 });

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-tahun-anggaran',
            type: 'POST',
            dataType: 'json',
            data: $.param(payload),
            success: function (response) {
                if (response?.status === 'error') {
                    swlErrorHandler(response.message || 'Gagal menyimpan master tahun.');
                    return;
                }

                $('#AnggaranYearEditorModal').modal('hide');
                resetTahunForm();
                reloadAnggaranPageData();
                swlSuccess(response?.message || 'Master tahun berhasil disimpan.');
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan saat menyimpan master tahun.');
            }
        });
    });

    $('#tahunTable').on('click', '.btn-edit-tahun', function () {
        const row = anggaranState.tahunById[Number($(this).data('id'))];
        if (!row) return;
        openEditTahunModal(row);
    });

    $('#tahunTable').on('click', '.btn-del-tahun', function () {
        const key = Number($(this).data('id'));
        confirmDelete('Hapus master tahun ini?', function () {
            $.post(AppConfig.initGlobal + 'kill/tahun-anggaran', { key }, function (response) {
                if (response?.status === 'error') {
                    swlErrorHandler(response.message || 'Gagal menghapus master tahun.');
                    return;
                }
                resetTahunForm();
                reloadAnggaranPageData();
                swlSuccess(response?.message || 'Master tahun berhasil dihapus.');
            }, 'json');
        });
    });

    $('#masterSearchStruktur').on('input', function () {
        applyStrukturCollapseVisibility();
    });

    $('#masterLevelFilter').on('change', function () {
        applyStrukturCollapseVisibility();
    });

    $('#masterFilterTahun').on('change', function () {
        loadAnggaranSettings();
    });

    $('#AnggaranMasterModal').on('shown.bs.modal', function () {
        const strukturTab = document.querySelector('#anggaranMasterTab button[data-bs-target="#tabMasterStruktur"]');
        if (strukturTab && window.bootstrap?.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(strukturTab).show();
        }

        setCollapseForAll(true);
    });

    $('#anggaranMasterTab button[data-bs-target="#tabMasterStruktur"]').on('shown.bs.tab', function () {
        setCollapseForAll(true);
    });

    $('#appendRootStruktur').on('click', function () {
        openStrukturRootModal();
    });

    $('#refreshStrukturMaster').on('click', function () {
        loadAnggaranSettings();
    });

    $('#expandAllStruktur').on('click', function () {
        setCollapseForAll(false);
    });

    $('#collapseAllStruktur').on('click', function () {
        setCollapseForAll(true);
    });

    $('#resetStrukturForm').on('click', function () {
        resetStrukturForm();
    });

    $('#submitStrukturForm').on('click', function () {
        $('#form-struktur-anggaran').trigger('submit');
    });

    $('#struktur_level').on('change', function () {
        toggleAkunBudgetFields($(this).val());
    });

    $('#form-struktur-anggaran').on('submit', function (e) {
        e.preventDefault();
        const $form = $(this);
        const currencySnapshot = sanitizeCurrencyInputs($form);

        $.ajax({
            url: AppConfig.initGlobal + 'store/save-struktur-anggaran',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize(),
            success: function (response) {
                if (response?.status === 'error') {
                    swlErrorHandler(response.message || 'Gagal menyimpan struktur.');
                    return;
                }

                $('#AnggaranStrukturEditorModal').modal('hide');
                resetStrukturForm();
                reloadAnggaranPageData();
                swlSuccess(response?.message || 'Struktur anggaran berhasil disimpan.');
            },
            error: function () {
                swlErrorHandler('Terjadi kesalahan saat menyimpan struktur.');
            },
            complete: function () {
                restoreCurrencySnapshot(currencySnapshot);
            }
        });
    });

    $('#strukturTable').on('click', '.btn-edit-struktur', function () {
        const row = anggaranState.strukturById[Number($(this).data('id'))];
        if (!row) return;
        openStrukturEditModal(row);
    });

    $('#strukturTable').on('click', '.btn-toggle-struktur', function () {
        const id = Number($(this).data('id'));
        if (!id) return;
        anggaranState.collapsedNodes[id] = !Boolean(anggaranState.collapsedNodes[id]);
        renderStrukturTable();
    });

    $('#strukturTable').on('click', '.btn-append-struktur', function () {
        const row = anggaranState.strukturById[Number($(this).data('id'))];
        if (!row) return;
        openStrukturAppendModal(row);
    });

    $('#strukturTable').on('click', '.btn-del-struktur', function () {
        const key = Number($(this).data('id'));
        confirmDelete('Hapus struktur ini?', function () {
            $.post(AppConfig.initGlobal + 'kill/struktur-anggaran', { key }, function (response) {
                if (response?.status === 'error') {
                    swlErrorHandler(response.message || 'Gagal menghapus struktur.');
                    return;
                }

                resetStrukturForm();
                reloadAnggaranPageData();
                swlSuccess(response?.message || 'Struktur berhasil dihapus.');
            }, 'json');
        });
    });

    $('#AnggaranYearEditorModal').on('hidden.bs.modal', function () {
        resetTahunForm();
    });

    $('#AnggaranStrukturEditorModal').on('hidden.bs.modal', function () {
        resetStrukturForm();
    });
});
