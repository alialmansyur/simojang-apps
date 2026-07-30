<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use App\Libraries\DataTablesLib;
use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\PembinaanDisiplinBudayaCitraModel;
use App\Models\Apps\Services\PembinaanKompetensiKarierModel;
use App\Models\Apps\Services\PembinaanKinerjaModel;

class PembinaanASNController extends BaseController
{
    protected $appsModel;
    protected $dataTables;
    protected $kinerjaModel;
    protected $disiplinModel;
    protected $kompetensiModel;
    protected $layananId = 14;

    public function __construct()
    {
        $this->appsModel = new AppsModel();
        $this->dataTables = new DataTablesLib();
        $this->kinerjaModel = new PembinaanKinerjaModel();
        $this->disiplinModel = new PembinaanDisiplinBudayaCitraModel();
        $this->kompetensiModel = new PembinaanKompetensiKarierModel();
    }

    private function normalizeMonthsFromPost(string $key = 'period_months'): array
    {
        $raw = $this->request->getPost($key);
        if ($raw === null || $raw === '') {
            return [];
        }

        if (!is_array($raw)) {
            $raw = explode(',', (string) $raw);
        }

        $months = array_map(static fn($v) => (int) $v, (array) $raw);
        $months = array_values(array_filter(array_unique($months), static fn($m) => $m >= 1 && $m <= 12));
        sort($months);

        return $months;
    }

    private function renderPlaceholder(string $serviceName)
    {
        return $this->renderView('Apps/pages/services/pembinaanmanajemenasn/main', [
            'title' => $serviceName,
            'seslog' => session()->get(),
            'service_name' => $serviceName,
        ]);
    }

    public function kinerja()
    {
        $this->layananId = $this->appsModel->getLayananIdByUrl('apps-pembinaan-kinerja', 14);

        return $this->renderView('Apps/pages/services/pembinaankinerja/main', [
            'title' => 'Pembinaan Kinerja dan Penghargaan',
            'seslog' => session()->get(),
        ]);
    }

    public function getKinerjaOptions()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'kategori' => $this->kinerjaModel->getKategoriOptions(),
            'years' => $this->kinerjaModel->getPeriodYears(),
        ]);
    }

    public function getKinerjaData()
    {

        $kategori = (int) ($this->request->getPost('kategori_id') ?? 0);
        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $months = $this->normalizeMonthsFromPost('period_months');

        $builder = $this->kinerjaModel->getBuilder('recap', [
            'kategori_id' => $kategori,
            'period_year' => $tahun,
            'period_months' => $months,
        ]);

        $columns = $this->kinerjaModel->getColumns('recap', [
            'kategori_id' => $kategori,
            'period_year' => $tahun,
        ]);

        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getKinerjaSummary()
    {

        $kategori = (int) ($this->request->getPost('kategori_id') ?? 0);
        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $months = $this->normalizeMonthsFromPost('period_months');

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->kinerjaModel->getSummary([
                'kategori_id' => $kategori,
                'period_year' => $tahun,
                'period_months' => $months,
            ]),
            'kategori_breakdown' => $this->kinerjaModel->getKategoriBreakdown([
                'period_year' => $tahun,
                'period_months' => $months,
            ]),
        ]);
    }

    public function storeKinerjaData()
    {
        $this->layananId = $this->appsModel->getLayananIdByUrl('apps-pembinaan-kinerja', 14);

        $sess = session()->get();
        $key = (int) ($this->request->getPost('key') ?? 0);

        $rules = [
            'instansi' => 'required',
            'kategori_id' => 'required|integer',
            'period_year' => 'required|integer|greater_than[2000]',
            'period_date' => 'required',
            'capaian_percent' => 'required|decimal',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $capaian = (float) $this->request->getPost('capaian_percent');
        $statusId = $this->kinerjaModel->resolveStatusId($capaian);
        $now = date('Y-m-d H:i:s');

        $payload = [
            'layanan_id' => $this->layananId,
            'instansi_id' => trim((string) $this->request->getPost('instansi')),
            'kategori_id' => (int) $this->request->getPost('kategori_id'),
            'status_id' => $statusId,
            'period_year' => (int) $this->request->getPost('period_year'),
            'period_date' => $this->request->getPost('period_date'),
            'capaian_percent' => $capaian,
            'pendampingan_date' => $this->request->getPost('pendampingan_date') ?: null,
            'catatan' => $this->request->getPost('catatan') ?: null,
            'kegiatan' => $this->request->getPost('kegiatan_code') ?: null,
            'aplikasi' => $this->request->getPost('aplikasi_code') ?: null,
            'capaian_persen' => $capaian,
            'updated_by' => $sess['username'] ?? null,
            'updated_at' => $now,
        ];

        $existingId = $this->kinerjaModel->findExistingTxnId(
            $payload['instansi_id'],
            (int) $payload['kategori_id'],
            (int) $payload['period_year']
        );
        if ($existingId && $existingId !== $key) {
            $key = $existingId;
        }

        if ($key > 0) {
            $this->appsModel->updateData($payload, $key, 'txn_pembinaan_kinerja');
        } else {
            $payload['created_by'] = $sess['username'] ?? null;
            $payload['created_at'] = $now;
            $key = (int) $this->appsModel->storeData($payload, 'txn_pembinaan_kinerja');
        }

        $this->appsModel->storeData(
            [
                'layanan_id' => $this->layananId,
                'tanggal' => date('Y-m-d'),
                'created_by' => $sess['username'] ?? null,
            ],
            'activity_daily_logs'
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data pembinaan kinerja berhasil disimpan.',
            'id' => $key,
        ]);
    }

    public function removeKinerjaData()
    {
        $key = (int) $this->request->getPost('key');
        if ($key <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid.',
            ]);
        }

        $this->appsModel->removeData($key, 'txn_pembinaan_kinerja');

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    public function getDisiplinOptions()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'kategori' => $this->disiplinModel->getKategoriOptions(true),
            'years' => $this->disiplinModel->getPeriodYears(),
        ]);
    }

    public function getDisiplinData()
    {

        $kategori = (int) ($this->request->getPost('kategori_id') ?? 0);
        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $jenis = strtoupper(trim((string) ($this->request->getPost('jenis_layanan') ?? 'ALL')));
        $months = $this->normalizeMonthsFromPost('period_months');

        $builder = $this->disiplinModel->getBuilder('recap', [
            'kategori_id' => $kategori,
            'period_year' => $tahun,
            'jenis_layanan' => $jenis,
            'period_months' => $months,
        ]);

        $columns = $this->disiplinModel->getColumns('recap');
        $result = $this->dataTables->render($builder, $columns);

        return $this->response->setJSON($result);
    }

    public function getDisiplinSummary()
    {

        $kategori = (int) ($this->request->getPost('kategori_id') ?? 0);
        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $jenis = strtoupper(trim((string) ($this->request->getPost('jenis_layanan') ?? 'ALL')));
        $months = $this->normalizeMonthsFromPost('period_months');

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->disiplinModel->getSummary([
                'kategori_id' => $kategori,
                'period_year' => $tahun,
                'jenis_layanan' => $jenis,
                'period_months' => $months,
            ]),
            'jenis_breakdown' => $this->disiplinModel->getJenisBreakdown([
                'period_year' => $tahun,
                'kategori_id' => $kategori,
                'jenis_layanan' => $jenis,
                'period_months' => $months,
            ]),
            'kategori_breakdown' => $this->disiplinModel->getKategoriBreakdown([
                'period_year' => $tahun,
                'jenis_layanan' => $jenis,
                'period_months' => $months,
            ]),
        ]);
    }

    public function getDisiplinDetail()
    {

        $instansiId = trim((string) ($this->request->getPost('instansi_id') ?? ''));
        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $jenis = strtoupper(trim((string) ($this->request->getPost('jenis_layanan') ?? 'ALL')));
        $kategori = (int) ($this->request->getPost('kategori_id') ?? 0);
        $months = $this->normalizeMonthsFromPost('period_months');

        if ($instansiId === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Instansi tidak valid.',
                'list' => [],
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'list' => $this->disiplinModel->getDetailByInstansi([
                'instansi_id' => $instansiId,
                'period_year' => $tahun,
                'jenis_layanan' => $jenis,
                'kategori_id' => $kategori,
                'period_months' => $months,
            ]),
        ]);
    }

    public function storeDisiplinData()
    {
        $this->layananId = $this->appsModel->getLayananIdByUrl('apps-pembinaan-disiplin-budaya-citra', 15);

        $sess = session()->get();
        $key = (int) ($this->request->getPost('key') ?? 0);

        $rules = [
            'instansi' => 'required',
            'kategori_id' => 'required|integer',
            'period_year' => 'required|integer|greater_than[2000]',
            'period_date' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $kategoriId = (int) $this->request->getPost('kategori_id');
        $kategori = $this->disiplinModel->getKategoriById($kategoriId);
        if (!$kategori || (int) ($kategori['is_active'] ?? 0) !== 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kategori tidak valid atau nonaktif.',
            ]);
        }

        $jenis = strtoupper((string) ($kategori['jenis_layanan'] ?? ''));
        $sourceKonsultasi = strtoupper(trim((string) ($this->request->getPost('source_konsultasi') ?? '')));
        $tempat = trim((string) ($this->request->getPost('tempat_kegiatan') ?? ''));
        $judul = trim((string) ($this->request->getPost('judul_kegiatan') ?? ''));
        $noSurat = trim((string) ($this->request->getPost('no_surat_kegiatan') ?? ''));
        $pegawaiIdsRaw = $this->request->getPost('pegawai_ids') ?? [];
        $pegawaiIds = array_values(array_unique(array_filter(array_map('intval', (array) $pegawaiIdsRaw), static fn($id) => $id > 0)));

        if ($jenis === 'KONSULTASI' && !in_array($sourceKonsultasi, ['SURAT_MASUK', 'ZOOM', 'PPT'], true)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Sumber konsultasi wajib dipilih (Surat Masuk, Zoom, atau PPT).',
            ]);
        }

        if (in_array($jenis, ['ASISTENSI', 'PEMBINAAN'], true)) {
            if ($tempat === '' || $judul === '' || $noSurat === '') {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Tempat, judul kegiatan, dan nomor surat wajib diisi.',
                ]);
            }
            if (empty($pegawaiIds)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Minimal satu pegawai wajib dipilih.',
                ]);
            }
        }

        $now = date('Y-m-d H:i:s');

        $payload = [
            'layanan_id' => $this->layananId,
            'instansi_id' => trim((string) $this->request->getPost('instansi')),
            'kategori_id' => $kategoriId,
            'period_year' => (int) $this->request->getPost('period_year'),
            'period_date' => $this->request->getPost('period_date'),
            'source_konsultasi' => $jenis === 'KONSULTASI' ? $sourceKonsultasi : null,
            'tempat_kegiatan' => in_array($jenis, ['ASISTENSI', 'PEMBINAAN'], true) ? $tempat : null,
            'judul_kegiatan' => in_array($jenis, ['ASISTENSI', 'PEMBINAAN'], true) ? $judul : null,
            'no_surat_kegiatan' => in_array($jenis, ['ASISTENSI', 'PEMBINAAN'], true) ? $noSurat : null,
            'catatan' => $this->request->getPost('catatan') ?: null,
            'updated_by' => $sess['username'] ?? null,
            'updated_at' => $now,
        ];

        if ($key > 0) {
            $this->appsModel->updateData($payload, $key, 'txn_pembinaan_disiplin_budaya_citra');
        } else {
            $payload['created_by'] = $sess['username'] ?? null;
            $payload['created_at'] = $now;
            $key = (int) $this->appsModel->storeData($payload, 'txn_pembinaan_disiplin_budaya_citra');
        }

        $this->disiplinModel->syncPegawaiByTxn($key, in_array($jenis, ['ASISTENSI', 'PEMBINAAN'], true) ? $pegawaiIds : []);

        $this->appsModel->storeData(
            [
                'layanan_id' => $this->layananId,
                'tanggal' => date('Y-m-d'),
                'created_by' => $sess['username'] ?? null,
            ],
            'activity_daily_logs'
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data pembinaan disiplin berhasil disimpan.',
            'id' => $key,
        ]);
    }

    public function removeDisiplinData()
    {
        $key = (int) ($this->request->getPost('key') ?? 0);
        if ($key <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid.',
            ]);
        }

        $this->disiplinModel->removePegawaiByTxn($key);
        $this->appsModel->removeData($key, 'txn_pembinaan_disiplin_budaya_citra');
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    public function getDisiplinKategoriData()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'list' => $this->disiplinModel->getKategoriOptions(false),
        ]);
    }

    public function storeDisiplinKategori()
    {
        $sess = session()->get();
        $key = (int) ($this->request->getPost('key') ?? 0);

        $rules = [
            'nama' => 'required|min_length[3]',
            'jenis_layanan' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $nama = trim((string) $this->request->getPost('nama'));
        $jenis = strtoupper(trim((string) $this->request->getPost('jenis_layanan')));
        $code = strtoupper(trim((string) ($this->request->getPost('code') ?? '')));
        if ($code === '') {
            $code = preg_replace('/[^A-Z0-9_]/', '', str_replace(' ', '_', strtoupper($nama)));
        }
        if ($code === '') {
            $code = 'KATEGORI_' . date('YmdHis');
        }

        $payload = [
            'code' => $code,
            'nama' => $nama,
            'jenis_layanan' => in_array($jenis, ['ASISTENSI', 'KONSULTASI', 'PEMBINAAN'], true) ? $jenis : 'ASISTENSI',
            'deskripsi' => $this->request->getPost('deskripsi') ?: null,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active' => (int) (($this->request->getPost('is_active') ?? 1) ? 1 : 0),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($key > 0) {
            if ($this->disiplinModel->kategoriCodeExists($code, $key)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Kode kategori sudah digunakan.',
                ]);
            }

            $this->appsModel->updateData($payload, $key, 'data_support_pembinaan_disiplin_kategori');
        } else {
            if ($this->disiplinModel->kategoriCodeExists($code)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Kode kategori sudah digunakan.',
                ]);
            }

            $payload['created_at'] = date('Y-m-d H:i:s');
            $payload['updated_at'] = date('Y-m-d H:i:s');
            $key = (int) $this->appsModel->storeData($payload, 'data_support_pembinaan_disiplin_kategori');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Kategori berhasil disimpan.',
            'id' => $key,
            'updated_by' => $sess['username'] ?? null,
        ]);
    }

    public function removeDisiplinKategori()
    {
        $key = (int) ($this->request->getPost('key') ?? 0);
        if ($key <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kategori tidak valid.',
            ]);
        }

        if ($this->disiplinModel->countUsageByKategoriId($key) > 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kategori sudah digunakan pada data transaksi.',
            ]);
        }

        $this->appsModel->removeData($key, 'data_support_pembinaan_disiplin_kategori');
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }

    public function kompetensiKarier()
    {
        $this->layananId = $this->appsModel->getLayananIdByUrl('apps-pembinaan-kompetensi-karier', 16);

        return $this->renderView('Apps/pages/services/pembinaankompetensikarier/main', [
            'title' => 'Pembinaan Pengembangan Kompetensi dan Karier',
            'seslog' => session()->get(),
        ]);
    }

    public function getKompetensiOptions()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'years' => $this->kompetensiModel->getPeriodYears(),
        ]);
    }

    public function getKompetensiData()
    {

        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $months = $this->normalizeMonthsFromPost('period_months');
        if (empty($months)) {
            $single = (int) ($this->request->getPost('period_month') ?? 0);
            if ($single >= 1 && $single <= 12) {
                $months = [$single];
            }
        }

        $builder = $this->kompetensiModel->getBuilder('recap', [
            'period_year' => $tahun,
            'period_months' => $months,
        ]);

        $columns = $this->kompetensiModel->getColumns('recap');
        $result = $this->dataTables->render($builder, $columns);

        return $this->response->setJSON($result);
    }

    public function getKompetensiSummary()
    {

        $tahun = (int) ($this->request->getPost('period_year') ?? date('Y'));
        $months = $this->normalizeMonthsFromPost('period_months');
        if (empty($months)) {
            $single = (int) ($this->request->getPost('period_month') ?? 0);
            if ($single >= 1 && $single <= 12) {
                $months = [$single];
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->kompetensiModel->getSummary([
                'period_year' => $tahun,
                'period_months' => $months,
            ]),
        ]);
    }

    public function storeKompetensiData()
    {
        $this->layananId = $this->appsModel->getLayananIdByUrl('apps-pembinaan-kompetensi-karier', 16);

        $sess = session()->get();
        $key = (int) ($this->request->getPost('key') ?? 0);

        $rules = [
            'period_year' => 'required|integer|greater_than[2000]',
            'tanggal_kegiatan' => 'required',
            'judul_kegiatan' => 'required|min_length[5]',
            'materi' => 'required|min_length[5]',
            'total_partisipan' => 'required|integer',
            'metode' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $payload = [
            'layanan_id' => $this->layananId,
            'period_year' => (int) $this->request->getPost('period_year'),
            'tanggal_kegiatan' => $this->request->getPost('tanggal_kegiatan'),
            'judul_kegiatan' => trim((string) $this->request->getPost('judul_kegiatan')),
            'materi' => trim((string) $this->request->getPost('materi')),
            'total_partisipan' => max(0, (int) $this->request->getPost('total_partisipan')),
            'metode' => trim((string) $this->request->getPost('metode')),
            'lokasi' => $this->request->getPost('lokasi') ?: null,
            'penyelenggara' => $this->request->getPost('penyelenggara') ?: null,
            'eviden_link' => $this->request->getPost('eviden_link') ?: null,
            'catatan' => $this->request->getPost('catatan') ?: null,
            'updated_by' => $sess['username'] ?? null,
            'updated_at' => $now,
        ];

        if ($key > 0) {
            $this->appsModel->updateData($payload, $key, 'txn_pembinaan_kompetensi_karier');
        } else {
            $payload['created_by'] = $sess['username'] ?? null;
            $payload['created_at'] = $now;
            $key = (int) $this->appsModel->storeData($payload, 'txn_pembinaan_kompetensi_karier');
        }

        $this->appsModel->storeData(
            [
                'layanan_id' => $this->layananId,
                'tanggal' => date('Y-m-d'),
                'created_by' => $sess['username'] ?? null,
            ],
            'activity_daily_logs'
        );

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data pembinaan kompetensi berhasil disimpan.',
            'id' => $key,
        ]);
    }

    public function removeKompetensiData()
    {
        $key = (int) ($this->request->getPost('key') ?? 0);
        if ($key <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid.',
            ]);
        }

        $this->appsModel->removeData($key, 'txn_pembinaan_kompetensi_karier');
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil dihapus.',
        ]);
    }

    public function disiplinBudayaCitra()
    {
        $this->layananId = $this->appsModel->getLayananIdByUrl('apps-pembinaan-disiplin-budaya-citra', 15);

        return $this->renderView('Apps/pages/services/pembinaandisiplinbudayacitra/main', [
            'title' => 'Pembinaan Disiplin, Budaya Kerja, dan Citra Institusi',
            'seslog' => session()->get(),
        ]);
    }
}
