<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\AnggaranModel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AnggaranData extends BaseController
{
    protected AnggaranModel $anggaranModel;
    protected AppsModel $apps;

    public function __construct()
    {
        $this->anggaranModel = new AnggaranModel();
        $this->apps = new AppsModel();
    }

    public function index()
    {
        return $this->renderView('Apps/pages/services/anggaran/main', [
            'seslog' => session()->get(),
        ]);
    }

    public function getData()
    {
        $result = $this->anggaranModel->getRealisasiDataTable(
            $this->collectFilters(),
            [
                'draw' => (int) ($this->request->getPost('draw') ?? 1),
                'start' => max(0, (int) ($this->request->getPost('start') ?? 0)),
                'length' => (int) ($this->request->getPost('length') ?? 10),
                'search' => is_array($this->request->getPost('search'))
                    ? trim((string) (($this->request->getPost('search'))['value'] ?? ''))
                    : '',
                'order' => $this->request->getPost('order'),
            ]
        );

        return $this->response->setJSON($result);
    }

    public function getDataDetail()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        if ($key <= 0) {
            return $this->respondError('Kunci data realisasi tidak valid.');
        }

        $detail = $this->anggaranModel->getRealisasiDetail($key);
        if (!$detail) {
            return $this->respondError('Data realisasi tidak ditemukan.');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $detail,
        ]);
    }

    public function getSummary()
    {
        $filters = $this->collectFilters();

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->anggaranModel->getSummary(
                (int) ($filters['tahun'] ?? 0),
                $filters['date_mode'] ?? null,
                $filters['date_start'] ?? null,
                $filters['date_end'] ?? null
            ),
        ]);
    }

    public function getOptions()
    {
        $tahun = (int) $this->request->getPost('tahun');
        $tahunId = (int) $this->request->getPost('tahun_id');
        $search = trim((string) $this->request->getPost('search'));
        $realisasiId = (int) $this->request->getPost('realisasi_id');

        if ($tahun <= 0 && $tahunId > 0) {
            $yearRow = $this->anggaranModel->getYearById($tahunId);
            $tahun = (int) ($yearRow['tahun'] ?? 0);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'years' => $this->anggaranModel->getYearOptions(),
            'akun_options' => $this->anggaranModel->getAkunOptions($tahun > 0 ? $tahun : null, $search, $realisasiId),
        ]);
    }

    public function getSettings()
    {
        $tahunMaster = (int) $this->request->getPost('tahun_master');

        return $this->response->setJSON([
            'status' => 'success',
            'years' => $this->anggaranModel->getYearOptions(),
            'level_order' => $this->anggaranModel->getLevelOrder(),
            'struktur' => $this->anggaranModel->getStrukturTree($tahunMaster > 0 ? $tahunMaster : null),
        ]);
    }

    public function searchStrukturSelect2()
    {
        $search = trim((string) $this->request->getPost('search'));
        $tahun = (int) $this->request->getPost('tahun');
        $tahunId = (int) $this->request->getPost('tahun_id');
        $limit = max(1, min(100, (int) ($this->request->getPost('limit') ?? 50)));

        if ($tahun <= 0 && $tahunId > 0) {
            $yearRow = $this->anggaranModel->getYearById($tahunId);
            $tahun = (int) ($yearRow['tahun'] ?? 0);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'results' => array_map(static function (array $row): array {
                $kode = trim((string) ($row['kode'] ?? ''));
                $nama = trim((string) ($row['nama'] ?? ''));
                $tahun = trim((string) ($row['tahun'] ?? ''));
                $parentKode = trim((string) ($row['parent_kode'] ?? ''));
                $parentNama = trim((string) ($row['parent_nama'] ?? ''));
                $parentLevel = strtolower(trim((string) ($row['parent_level'] ?? '')));
                $text = trim(($kode !== '' ? $kode . ' - ' : '') . $nama);
                if ($parentNama !== '') {
                    $parentLabel = trim(($parentKode !== '' ? $parentKode . ' - ' : '') . $parentNama);
                    $prefix = $parentLevel === 'sub_komponen' ? 'Sub Komponen' : 'Parent';
                    $text .= ' | ' . $prefix . ': ' . $parentLabel;
                }
                if ($tahun !== '') {
                    $text .= ' [' . $tahun . ']';
                }

                return [
                    'id' => (int) ($row['id'] ?? 0),
                    'text' => $text,
                    'kode' => $kode,
                    'nama' => $nama,
                    'parent_kode' => $parentKode,
                    'parent_nama' => $parentNama,
                    'parent_level' => $parentLevel,
                    'tahun' => $tahun,
                ];
            }, $this->anggaranModel->searchAkunSelect2($search, $limit, $tahun > 0 ? $tahun : null)),
        ]);
    }

    public function storeData()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        $tahunId = (int) $this->request->getPost('tahun_id');
        $periodDate = $this->normalizeDate((string) $this->request->getPost('period_date'), true);
        $spmDate = $this->normalizeDate((string) $this->request->getPost('spm_date'));
        $sp2dDate = $this->normalizeDate((string) $this->request->getPost('sp2d_date'));
        $noSpm = $this->normalizeDocumentNumber((string) $this->request->getPost('no_spm'));
        $noSp2d = $this->normalizeDocumentNumber((string) $this->request->getPost('no_sp2d'));
        $keterangan = trim((string) $this->request->getPost('keterangan'));
        $status = strtoupper(trim((string) $this->request->getPost('status')));
        $status = $status === 'DRAFT' ? 'PENDING' : $status;

        if ($tahunId <= 0 || $periodDate === null || $spmDate === null || $sp2dDate === null) {
            return $this->respondError('Form realisasi belum lengkap.');
        }

        if ($noSpm === '' || $noSp2d === '') {
            return $this->respondError('Nomor SPM dan SP2D wajib diisi.');
        }

        $duplicateSpm = $this->anggaranModel->findDuplicateRealisasiDocument('no_spm', $noSpm, $key);
        if ($duplicateSpm) {
            return $this->respondError($this->buildDuplicateDocumentMessage('No. SPM', $noSpm, $duplicateSpm));
        }

        $duplicateSp2d = $this->anggaranModel->findDuplicateRealisasiDocument('no_sp2d', $noSp2d, $key);
        if ($duplicateSp2d) {
            return $this->respondError($this->buildDuplicateDocumentMessage('No. SP2D', $noSp2d, $duplicateSp2d));
        }

        $items = $this->collectRealisasiItems();
        if (empty($items)) {
            return $this->respondError('Minimal satu item realisasi wajib diisi.');
        }

        $result = $this->anggaranModel->saveRealisasi(
            $key,
            [
                'tahun_id' => $tahunId,
                'period_date' => $periodDate,
                'no_spm' => $noSpm,
                'spm_date' => $spmDate,
                'no_sp2d' => $noSp2d,
                'sp2d_date' => $sp2dDate,
                'keterangan' => $keterangan,
                'status' => $status,
            ],
            $items,
            (string) session()->get('username')
        );

        return $this->response->setJSON($result);
    }

    public function storeYear()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        $tahun = (int) $this->request->getPost('tahun');
        $targetPersen = (float) $this->request->getPost('target_persen');
        $isActive = (int) $this->request->getPost('is_active') === 1 ? 1 : 0;

        if ($tahun < 2000 || $tahun > 2100) {
            return $this->respondError('Tahun tidak valid. Gunakan format 4 digit.');
        }

        if ($targetPersen < 0 || $targetPersen > 100) {
            return $this->respondError('Target persen harus di antara 0 sampai 100.');
        }

        $existingByYear = $this->anggaranModel->getYearByValue($tahun);
        $idToUpdate = 0;

        if ($key > 0) {
            $existingById = $this->anggaranModel->getYearById($key);
            if (!$existingById) {
                return $this->respondError('Data tahun yang akan diubah tidak ditemukan.');
            }
            if ($existingByYear && (int) $existingByYear['id'] !== $key) {
                return $this->respondError('Tahun anggaran ' . $tahun . ' sudah digunakan oleh data lain.');
            }
            $idToUpdate = (int) $existingById['id'];
        } else {
            if ($existingByYear) {
                return $this->respondError('Tahun anggaran ' . $tahun . ' sudah tersedia. Silakan gunakan fitur ubah.');
            }
        }

        $payload = [
            'tahun' => $tahun,
            'target_persen' => $targetPersen,
            'is_active' => $isActive,
        ];

        if ($idToUpdate > 0) {
            $this->apps->updateData($payload, $idToUpdate, 'txn_anggaran_tahun');
            $savedId = $idToUpdate;
        } else {
            $savedId = (int) $this->apps->storeData($payload, 'txn_anggaran_tahun');
        }

        if ($isActive === 1) {
            $this->anggaranModel->setOnlyActiveYear($savedId);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Master tahun anggaran berhasil disimpan.',
        ]);
    }

    public function removeYear()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        if ($key <= 0) {
            return $this->respondError('Kunci tahun tidak valid.');
        }

        $year = $this->anggaranModel->getYearById($key);
        if (!$year) {
            return $this->respondError('Data tahun tidak ditemukan.');
        }

        $hasRealisasi = $this->anggaranModel->hasRealisasiForYearId($key);
        $hasStruktur = $this->anggaranModel->hasStrukturForYear((int) $year['tahun']);

        if ($hasRealisasi || $hasStruktur) {
            return $this->respondError('Tahun tidak bisa dihapus karena sudah dipakai data struktur atau realisasi.');
        }

        $this->apps->removeData($key, 'txn_anggaran_tahun');

        if ((int) $year['is_active'] === 1) {
            $latest = $this->anggaranModel->getLatestYear();
            if ($latest) {
                $this->anggaranModel->setOnlyActiveYear((int) $latest['id']);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Master tahun berhasil dihapus.',
        ]);
    }

    public function storeStruktur()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        $parentId = (int) $this->request->getPost('parent_id');
        $tahun = (int) $this->request->getPost('tahun');
        $level = strtolower(trim((string) $this->request->getPost('level')));
        $kode = trim((string) $this->request->getPost('kode'));
        $nama = trim((string) $this->request->getPost('nama'));
        $paguRevisi = (float) $this->request->getPost('pagu_revisi');
        $lockPagu = (float) $this->request->getPost('lock_pagu');

        if ($tahun < 2000 || $tahun > 2100) {
            return $this->respondError('Tahun struktur tidak valid.');
        }

        if ($nama === '') {
            return $this->respondError('Nama struktur wajib diisi.');
        }

        $existing = null;
        if ($key > 0) {
            $existing = $this->anggaranModel->getStrukturById($key);
            if (!$existing) {
                return $this->respondError('Data struktur yang akan diubah tidak ditemukan.');
            }
        }

        $isLegacyRoot = $existing
            && trim((string) ($existing['level'] ?? '')) === ''
            && $parentId <= 0
            && $level === '';

        if (!$isLegacyRoot && !$this->anggaranModel->isValidLevel($level)) {
            return $this->respondError('Level struktur tidak valid.');
        }

        if ($parentId > 0) {
            if ($key > 0 && $parentId === $key) {
                return $this->respondError('Parent tidak boleh sama dengan struktur itu sendiri.');
            }

            if ($key > 0 && $this->anggaranModel->isDescendant($parentId, $key)) {
                return $this->respondError('Parent tidak valid karena menyebabkan siklus struktur.');
            }
        }

        $parent = null;
        if ($parentId > 0) {
            $parent = $this->anggaranModel->getStrukturById($parentId);
            if (!$parent) {
                return $this->respondError('Parent struktur tidak ditemukan.');
            }

            if ((int) $parent['tahun'] !== $tahun) {
                return $this->respondError('Tahun child harus sama dengan tahun parent.');
            }
        }

        if (!$isLegacyRoot) {
            $parentLevel = $parent['level'] ?? null;
            if (!$this->anggaranModel->isValidLevelTransition($parentLevel, $level)) {
                $next = $this->anggaranModel->getNextLevel($parentLevel);
                if ($parentLevel === null || $parentLevel === '') {
                    return $this->respondError('Root struktur hanya boleh level UNIT.');
                }
                if ($next === null) {
                    return $this->respondError('Level parent sudah AKUN, tidak bisa ditambahkan child.');
                }
                return $this->respondError('Level child untuk parent ini harus ' . strtoupper($next) . '.');
            }
        }

        if ($existing && (string) $existing['level'] !== $level && $this->anggaranModel->hasStrukturChildren($key)) {
            return $this->respondError('Level tidak boleh diubah karena data memiliki child.');
        }

        if ($level !== 'akun') {
            $paguRevisi = 0;
            $lockPagu = 0;
        }

        if ($lockPagu > $paguRevisi) {
            return $this->respondError('Lock pagu tidak boleh lebih besar dari pagu revisi.');
        }

        $yearRow = $this->anggaranModel->getYearByValue($tahun);
        if (!$yearRow) {
            $this->apps->storeData([
                'tahun' => $tahun,
                'target_persen' => 0,
                'is_active' => 0,
            ], 'txn_anggaran_tahun');
        }

        $payload = [
            'parent_id' => $parentId > 0 ? $parentId : null,
            'kode' => $kode !== '' ? $kode : null,
            'nama' => $nama,
            'level' => $level !== '' ? $level : '',
            'tahun' => $tahun,
            'pagu_revisi' => $paguRevisi,
            'lock_pagu' => $lockPagu,
        ];

        if ($key > 0) {
            $this->apps->updateData($payload, $key, 'txn_anggaran_struktur');
        } else {
            if ($payload['level'] === '') {
                return $this->respondError('Level struktur tidak valid.');
            }
            $this->apps->storeData($payload, 'txn_anggaran_struktur');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Struktur anggaran berhasil disimpan.',
        ]);
    }

    public function removeStruktur()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        if ($key <= 0) {
            return $this->respondError('Kunci struktur tidak valid.');
        }

        $struktur = $this->anggaranModel->getStrukturById($key);
        if (!$struktur) {
            return $this->respondError('Data struktur tidak ditemukan.');
        }

        if ($this->anggaranModel->hasStrukturChildren($key)) {
            return $this->respondError('Struktur tidak bisa dihapus karena masih memiliki child.');
        }

        if ($this->anggaranModel->isStrukturUsed($key)) {
            return $this->respondError('Struktur tidak bisa dihapus karena sudah dipakai transaksi realisasi.');
        }

        $this->apps->removeData($key, 'txn_anggaran_struktur');

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Struktur anggaran berhasil dihapus.',
        ]);
    }

    public function removeData()
    {
        $key = (int) trim((string) $this->request->getPost('key'));
        if ($key <= 0) {
            return $this->respondError('Kunci data realisasi tidak valid.');
        }

        $existing = $this->anggaranModel->getRealisasiById($key);
        if (!$existing) {
            return $this->respondError('Data realisasi tidak ditemukan.');
        }

        $deleted = $this->anggaranModel->deleteRealisasi($key);
        if (!$deleted) {
            return $this->respondError('Gagal menghapus data realisasi.');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data realisasi berhasil dihapus.',
        ]);
    }

    public function exportExcel()
    {
        $filters = $this->collectFilters();
        $search = trim((string) $this->request->getGet('search'));
        $orderColumn = (int) $this->request->getGet('order_column');
        $orderDir = strtolower(trim((string) $this->request->getGet('order_dir'))) === 'asc' ? 'asc' : 'desc';

        $rows = $this->anggaranModel->getRealisasiExportRows(
            $filters,
            $search,
            [
                'column' => $orderColumn,
                'dir' => $orderDir,
            ]
        );

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Realisasi Anggaran');

        $headers = [
            'No',
            'Tahun',
            'Periode',
            'No. SPM',
            'Tgl SPM',
            'No. SP2D',
            'Tgl SP2D',
            'Total Item',
            'Total Realisasi',
            'Status',
            'Keterangan Header',
            'Ringkasan Item',
            'Update',
        ];

        $lastColumn = Coordinate::stringFromColumnIndex(count($headers));

        $sheet->mergeCells('A1:' . $lastColumn . '1');
        $sheet->mergeCells('A2:' . $lastColumn . '2');
        $sheet->setCellValue('A1', 'Realisasi Anggaran');
        $sheet->setCellValue('A2', 'Filter aktif: ' . $this->buildExportFilterLabel($filters, $search));

        $sheet->fromArray($headers, null, 'A4');

        $rowNumber = 5;
        foreach ($rows as $index => $row) {
            $sheet->setCellValue('A' . $rowNumber, $index + 1);
            $sheet->setCellValue('B' . $rowNumber, (int) ($row['tahun'] ?? 0));
            $sheet->setCellValue('C' . $rowNumber, $this->formatExportPeriod((string) ($row['period_date'] ?? '')));
            $sheet->setCellValue('D' . $rowNumber, (string) ($row['no_spm'] ?? '-'));
            $sheet->setCellValue('E' . $rowNumber, $this->formatExportDate((string) ($row['spm_date'] ?? '')));
            $sheet->setCellValue('F' . $rowNumber, (string) ($row['no_sp2d'] ?? '-'));
            $sheet->setCellValue('G' . $rowNumber, $this->formatExportDate((string) ($row['sp2d_date'] ?? '')));
            $sheet->setCellValue('H' . $rowNumber, (int) ($row['item_count'] ?? 0));
            $sheet->setCellValue('I' . $rowNumber, (float) ($row['total_nominal'] ?? 0));
            $sheet->setCellValue('J' . $rowNumber, (string) ($row['status'] ?? '-'));
            $sheet->setCellValue('K' . $rowNumber, (string) ($row['keterangan'] ?? '-'));
            $sheet->setCellValue('L' . $rowNumber, str_replace('||', "\n", (string) ($row['item_summary'] ?? '-')));
            $sheet->setCellValue('M' . $rowNumber, (string) ($row['updated_at'] ?? '-'));
            $rowNumber++;
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true);
        $sheet->getStyle('A1:' . $lastColumn . '2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A4:' . $lastColumn . '4')->getFont()->setBold(true);
        $sheet->getStyle('A4:' . $lastColumn . '4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2E8F0');

        $lastDataRow = max(4, $rowNumber - 1);
        $sheet->freezePane('A5');
        $sheet->setAutoFilter('A4:' . $lastColumn . $lastDataRow);
        if ($lastDataRow >= 5) {
            $sheet->getStyle('I5:I' . $lastDataRow)
                ->getNumberFormat()
                ->setFormatCode('#,##0');
            $sheet->getStyle('L5:L' . $lastDataRow)
                ->getAlignment()
                ->setWrapText(true);
        }

        for ($i = 1; $i <= count($headers); $i++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = (string) ob_get_clean();
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $fileName = 'realisasi-anggaran-' . date('Ymd_His') . '.xlsx';
        return $this->response->download($fileName, $binary, true);
    }

    private function collectFilters(): array
    {
        return [
            'tahun' => (int) $this->request->getVar('tahun'),
            'date_mode' => strtolower(trim((string) $this->request->getVar('date_mode'))),
            'date_start' => $this->normalizeDate((string) $this->request->getVar('date_start')),
            'date_end' => $this->normalizeDate((string) $this->request->getVar('date_end')),
        ];
    }

    private function collectRealisasiItems(): array
    {
        $strukturIds = $this->request->getPost('item_struktur_id');
        $nominals = $this->request->getPost('item_nominal');
        $keterangans = $this->request->getPost('item_keterangan');

        if (!is_array($strukturIds) || !is_array($nominals)) {
            return [];
        }

        $items = [];
        foreach ($strukturIds as $index => $strukturId) {
            $nominalValue = $nominals[$index] ?? null;
            $keteranganValue = is_array($keterangans) ? ($keterangans[$index] ?? '') : '';

            $items[] = [
                'struktur_id' => (int) $strukturId,
                'nominal' => (float) $nominalValue,
                'keterangan' => trim((string) $keteranganValue),
            ];
        }

        return $items;
    }

    private function normalizeDate(string $value, bool $allowMonth = false): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if ($allowMonth && preg_match('/^\d{4}-\d{2}$/', $value)) {
            $value .= '-01';
        }

        $time = strtotime($value);
        if ($time === false) {
            return null;
        }

        return date('Y-m-d', $time);
    }

    private function normalizeDocumentNumber(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value));
        return $normalized !== null ? $normalized : trim($value);
    }

    private function buildDuplicateDocumentMessage(string $label, string $value, array $duplicate): string
    {
        $tahun = trim((string) ($duplicate['tahun'] ?? ''));
        $period = $this->formatExportPeriod((string) ($duplicate['period_date'] ?? ''));
        $context = [];

        if ($tahun !== '') {
            $context[] = 'tahun ' . $tahun;
        }

        if ($period !== '-') {
            $context[] = 'periode ' . $period;
        }

        $suffix = empty($context) ? '' : ' pada ' . implode(', ', $context);
        return $label . ' "' . $value . '" sudah digunakan pada data realisasi lain' . $suffix . '.';
    }

    private function buildExportFilterLabel(array $filters, string $search = ''): string
    {
        $parts = [];

        if (!empty($filters['tahun'])) {
            $parts[] = 'Tahun ' . (int) $filters['tahun'];
        } else {
            $parts[] = 'Semua Tahun';
        }

        $dateMode = strtolower(trim((string) ($filters['date_mode'] ?? '')));
        if ($dateMode === 'spm') {
            $parts[] = 'Mode tanggal SPM';
        } elseif ($dateMode === 'sp2d') {
            $parts[] = 'Mode tanggal SP2D';
        } else {
            $parts[] = 'Semua Tanggal';
        }

        $dateStart = (string) ($filters['date_start'] ?? '');
        $dateEnd = (string) ($filters['date_end'] ?? '');
        if ($dateStart !== '' || $dateEnd !== '') {
            $parts[] = 'Rentang ' . ($this->formatExportDate($dateStart) ?: '-') . ' s.d. ' . ($this->formatExportDate($dateEnd) ?: '-');
        }

        if ($search !== '') {
            $parts[] = 'Pencarian: ' . $search;
        }

        return implode(' | ', $parts);
    }

    private function formatExportDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '-';
        }

        $time = strtotime($value);
        if ($time === false) {
            return $value;
        }

        return date('d-m-Y', $time);
    }

    private function formatExportPeriod(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '-';
        }

        $time = strtotime($value);
        if ($time === false) {
            return $value;
        }

        return date('m/Y', $time);
    }

    private function respondError(string $message)
    {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => $message,
        ]);
    }
}
