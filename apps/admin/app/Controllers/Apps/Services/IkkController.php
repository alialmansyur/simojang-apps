<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\DataTablesLib;
use App\Libraries\ExcelUploader;
use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\IkkModel;

class IkkController extends BaseController
{
    protected $apps;
    protected $ikk;
    protected $dataTables;

    public function __construct()
    {
        $this->apps = new AppsModel();
        $this->ikk = new IkkModel();
        $this->dataTables = new DataTablesLib();
    }

    /**
     * Pengecekan otorisasi pengguna terhadap Layanan Manajemen IKK (Layanan ID: 43)
     */
    protected function checkServiceAccess(): bool
    {
        $nip = (string) (session()->get('username') ?? '');
        if ($nip === '') {
            return false;
        }

        $spm = new \App\Models\Auth\ServicePermissionModel();
        $layananId = $this->apps->getLayananIdByUrl('apps-ikk', 43);
        return $spm->canNipAccessService($nip, $layananId);
    }

    protected function unauthorizedResponse()
    {
        return $this->response->setStatusCode(403)->setJSON([
            'status'  => 'error',
            'message' => 'Akses layanan ditolak. Anda tidak memiliki izin untuk mengelola data IKK.',
        ]);
    }

    public function index()
    {
        return $this->renderView('Apps/pages/services/ikk/main', [
            'title'  => 'Manajemen Capaian IKK',
            'seslog' => session()->get(),
        ]);
    }

    /**
     * Mengambil ringkasan KPI untuk kartu metrik atas
     */
    public function getSummary()
    {
        $filter = [
            'tahun'    => $this->request->getPost('tahun'),
            'triwulan' => $this->request->getPost('triwulan'),
        ];
        $stats = $this->ikk->getSummaryStats($filter);
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $stats,
        ]);
    }

    // =========================================================================
    // 1. SASARAN STRATEGIS
    // =========================================================================
    public function getDataSasaran()
    {
        $builder = $this->ikk->getSasaranBuilder();
        $columns = [
            'id', 
            'no_urut', 
            ['data' => 'nama_sasaran', 'search' => 's.nama_sasaran'], 
            'total_indikator',
            'created_at'
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function storeSasaran()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key          = $this->request->getPost('key');
        $nama_sasaran = trim((string) $this->request->getPost('nama_sasaran'));
        $no_urut      = $this->request->getPost('no_urut');

        $rules = [
            'nama_sasaran' => 'required|min_length[3]',
            'no_urut'      => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'nama_sasaran' => $nama_sasaran,
            'no_urut'      => ($no_urut !== '' && $no_urut !== null) ? (int) $no_urut : null,
        ];

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            if (!empty($key)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->apps->updateData($data, $key, 'data_ikk_sasaran');
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->apps->storeData($data, 'data_ikk_sasaran');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan data Sasaran. Terjadi kesalahan basis data.',
                ]);
            }

            $db->transCommit();
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Data Sasaran berhasil disimpan.',
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteSasaran()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        // Restrict Check: cegah penghapusan jika masih ada anak Indikator
        $childCheck = $this->ikk->countSasaranChildren((int) $key);
        if ($childCheck['indikator'] > 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Tidak dapat menghapus sasaran ini karena masih memiliki {$childCheck['indikator']} indikator aktif. Hapus terlebih dahulu seluruh indikator terkait untuk mencegah kehilangan data berantai.",
            ]);
        }

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            $this->apps->removeData($key, 'data_ikk_sasaran');

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data Sasaran.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Sasaran berhasil dihapus.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // 2. INDIKATOR KINERJA KEGIATAN (IKK)
    // =========================================================================
    public function getDataIndikator()
    {
        $sasaran_id = $this->request->getPost('sasaran_id');
        $tahun      = $this->request->getPost('tahun');
        $triwulan   = $this->request->getPost('triwulan');

        $filter = [];
        if (!empty($tahun)) {
            $filter['tahun'] = (int) $tahun;
        }
        if (!empty($triwulan)) {
            $filter['triwulan'] = (int) $triwulan;
        }

        $builder = $this->ikk->getIndikatorBuilder($filter);

        if (!empty($sasaran_id)) {
            $builder->where('a.sasaran_id', (int) $sasaran_id);
        }

        $columns = [
            'id', 
            ['data' => 'nama_sasaran', 'search' => 'b.nama_sasaran'],
            'no_urut', 
            ['data' => 'nama_indikator', 'search' => 'a.nama_indikator'],
            'satuan', 
            'target_volume',
            'total_kegiatan',
            'periode_progres',
            'periode_realisasi',
            'periode_target',
            'realisasi_tw',
            'realisasi_tahun',
            'realisasi_bukti',
            'total_realisasi_periods',
            'pengampu_list',
            'total_pengampu'
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    /**
     * Mengambil matriks realisasi 4 Triwulan untuk modal/detail indikator
     */
    public function getRealisasiIndikator()
    {
        $indikator_id = (int) $this->request->getPost('indikator_id');
        $tahun = (int) ($this->request->getPost('tahun') ?: date('Y'));

        if (empty($indikator_id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Indikator ID tidak valid.']);
        }

        $matrix = $this->ikk->getIndikatorMatrix($indikator_id, $tahun);
        $assist = $this->ikk->calcAggregatedKegiatanProgress($indikator_id, $tahun);

        // Ambil riwayat log seluruh periode untuk indikator ini
        $logs = $this->ikk->db->table('txn_ikk_indikator')
            ->where('indikator_id', $indikator_id)
            ->orderBy('tahun', 'DESC')
            ->orderBy('triwulan', 'DESC')
            ->get()->getResultArray();

        // Info indikator dan pengampu terupdate
        $builder = $this->ikk->getIndikatorBuilder();
        $indikator = $builder->where('a.id', $indikator_id)->get()->getRowArray();

        return $this->response->setJSON([
            'status'    => 'success',
            'matrix'    => $matrix,
            'assist'    => $assist,
            'tahun'     => $tahun,
            'logs'      => $logs,
            'indikator' => $indikator,
        ]);
    }

    /**
     * Simpan / perbarui realisasi capaian indikator per periode
     */
    public function storeRealisasiIndikator()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $indikator_id       = (int) $this->request->getPost('indikator_id');
        $tahun              = (int) $this->request->getPost('tahun');
        $triwulan           = (int) $this->request->getPost('triwulan');
        $target_periode     = trim((string) $this->request->getPost('target_periode'));
        $jumlah_realisasi   = $this->request->getPost('jumlah_realisasi');
        $progres_capaian    = $this->request->getPost('progres_capaian');
        $penjelasan_capaian = trim((string) $this->request->getPost('penjelasan_capaian'));
        $bukti_dukung       = trim((string) $this->request->getPost('bukti_dukung'));

        $rules = [
            'indikator_id'    => 'required|is_not_unique[data_ikk_indikator.id]',
            'tahun'           => 'required|numeric|greater_than[2020]|less_than[2100]',
            'triwulan'        => 'required|in_list[1,2,3,4]',
            'progres_capaian' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[150]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $periodeKey = sprintf('%04d-TW%d', $tahun, $triwulan);
        $sessionUser = session()->get('nama') ?? session()->get('username') ?? 'Pengampu IKK';
        $now = date('Y-m-d H:i:s');

        $data = [
            'indikator_id'       => $indikator_id,
            'tahun'              => $tahun,
            'triwulan'           => $triwulan,
            'periode_key'        => $periodeKey,
            'target_periode'     => $target_periode ?: null,
            'jumlah_realisasi'   => ($jumlah_realisasi !== '' && $jumlah_realisasi !== null) ? (float) $jumlah_realisasi : null,
            'progres_capaian'    => (float) $progres_capaian,
            'penjelasan_capaian' => $penjelasan_capaian ?: null,
            'bukti_dukung'       => $bukti_dukung ?: null,
            'updated_at'         => $now,
        ];

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            $existing = $db->table('txn_ikk_indikator')
                ->where('indikator_id', $indikator_id)
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan)
                ->get()->getRowArray();

            if ($existing) {
                $db->table('txn_ikk_indikator')
                    ->where('id', $existing['id'])
                    ->update($data);
            } else {
                $data['created_by'] = $sessionUser;
                $data['created_at'] = $now;
                $db->table('txn_ikk_indikator')->insert($data);
            }

            $this->apps->storeData([
                'layanan_id' => 43,
                'tanggal'    => date('Y-m-d'),
                'created_by' => session()->get('username') ?? 'system',
            ], 'activity_daily_logs');

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan realisasi indikator.']);
            }

            $db->transCommit();
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => "Realisasi Indikator Triwulan {$triwulan} Tahun {$tahun} berhasil disimpan.",
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus record realisasi indikator
     */
    public function deleteRealisasiIndikator()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $id = (int) $this->request->getPost('id');
        if (empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID realisasi tidak valid.']);
        }

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            $db->table('txn_ikk_indikator')->where('id', $id)->delete();

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data realisasi indikator.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data realisasi indikator berhasil dihapus.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Hitung estimasi capaian dari kegiatan operasional (Smart Assist helper)
     */
    public function calcFromKegiatan()
    {
        $indikator_id = (int) $this->request->getPost('indikator_id');
        $tahun        = $this->request->getPost('tahun') ? (int) $this->request->getPost('tahun') : null;
        $triwulan     = $this->request->getPost('triwulan') ? (int) $this->request->getPost('triwulan') : null;

        if (empty($indikator_id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Indikator ID tidak valid.']);
        }

        $assist = $this->ikk->calcAggregatedKegiatanProgress($indikator_id, $tahun, $triwulan);
        return $this->response->setJSON(array_merge(['status' => 'success'], $assist));
    }

    public function storeIndikator()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key            = $this->request->getPost('key');
        $sasaran_id     = $this->request->getPost('sasaran_id');
        $nama_indikator = trim((string) $this->request->getPost('nama_indikator'));
        $no_urut        = $this->request->getPost('no_urut');
        $satuan         = trim((string) $this->request->getPost('satuan'));
        $target_volume  = trim((string) $this->request->getPost('target_volume'));

        $rules = [
            'sasaran_id'     => 'required|is_not_unique[data_ikk_sasaran.id]',
            'nama_indikator' => 'required|min_length[3]',
            'no_urut'        => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'sasaran_id'     => (int) $sasaran_id,
            'nama_indikator' => $nama_indikator,
            'no_urut'        => ($no_urut !== '' && $no_urut !== null) ? (int) $no_urut : null,
            'satuan'         => $satuan ?: null,
            'target_volume'  => $target_volume ?: null,
        ];

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            if (!empty($key)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->apps->updateData($data, $key, 'data_ikk_indikator');
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->apps->storeData($data, 'data_ikk_indikator');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data Indikator.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Indikator berhasil disimpan.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function deleteIndikator()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        // Restrict Check: cegah penghapusan jika masih ada kegiatan
        $childCheck = $this->ikk->countIndikatorChildren((int) $key);
        if ($childCheck['kegiatan'] > 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Tidak dapat menghapus indikator ini karena masih memiliki {$childCheck['kegiatan']} kegiatan operasional aktif. Hapus terlebih dahulu seluruh kegiatan terkait.",
            ]);
        }

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            // Bersihkan relasi pengampu jika ada
            $this->ikk->cleanupPengampu('indikator', (int) $key);

            $this->apps->removeData($key, 'data_ikk_indikator');

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data Indikator.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Indikator berhasil dihapus.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // 3. KEGIATAN OPERASIONAL & PENGAMPU
    // =========================================================================
    public function getDataKegiatan()
    {
        $builder      = $this->ikk->getKegiatanBuilder();
        $sasaran_id   = $this->request->getPost('sasaran_id');
        $indikator_id = $this->request->getPost('indikator_id');
        $timkerja_id  = $this->request->getPost('timkerja_id');

        if (!empty($sasaran_id)) {
            $builder->where('b.sasaran_id', (int) $sasaran_id);
        }
        if (!empty($indikator_id)) {
            $builder->where('a.indikator_id', (int) $indikator_id);
        }
        if (!empty($timkerja_id)) {
            $builder->where("EXISTS (
                SELECT 1 FROM data_ikk_pengampu p2 
                WHERE p2.tipe_relasi = 'kegiatan' 
                  AND p2.relasi_id = a.id 
                  AND p2.timkerja_id = " . (int) $timkerja_id . "
            )", null, false);
        }

        $columns = [
            'id', 
            ['data' => 'nama_indikator', 'search' => 'b.nama_indikator'],
            ['data' => 'nama_sasaran', 'search' => 'c.nama_sasaran'],
            'no_urut', 
            ['data' => 'nama_kegiatan', 'search' => 'a.nama_kegiatan'],
            'satuan', 
            'target_volume',
            'pengampu_list',
            'total_pengampu',
            'total_transaksi'
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function storeKegiatan()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key           = $this->request->getPost('key');
        $indikator_id  = $this->request->getPost('indikator_id');
        $nama_kegiatan = trim((string) $this->request->getPost('nama_kegiatan'));
        $no_urut       = $this->request->getPost('no_urut');
        $satuan        = trim((string) $this->request->getPost('satuan'));
        $target_volume = trim((string) $this->request->getPost('target_volume'));
        $timkerja_ids  = $this->request->getPost('timkerja_id');

        $rules = [
            'indikator_id'  => 'required|is_not_unique[data_ikk_indikator.id]',
            'nama_kegiatan' => 'required|min_length[3]',
            'no_urut'       => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'indikator_id'  => (int) $indikator_id,
            'nama_kegiatan' => $nama_kegiatan,
            'no_urut'       => ($no_urut !== '' && $no_urut !== null) ? (int) $no_urut : null,
            'satuan'        => $satuan ?: null,
            'target_volume' => $target_volume ?: null,
        ];

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            if (!empty($key)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->apps->updateData($data, $key, 'data_ikk_kegiatan');
                $kegiatanId = (int) $key;
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $kegiatanId = (int) $this->apps->storeData($data, 'data_ikk_kegiatan');
            }

            // Sync Tim Kerja Pengampu jika field dikirim
            if ($timkerja_ids !== null) {
                if (!is_array($timkerja_ids)) {
                    $timkerja_ids = ($timkerja_ids !== '' && $timkerja_ids !== null) ? [$timkerja_ids] : [];
                }
                $this->ikk->syncPengampu('kegiatan', $kegiatanId, $timkerja_ids);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan data Kegiatan.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Kegiatan dan Tim Pengampu berhasil disimpan.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function deleteKegiatan()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        // Restrict Check: cegah penghapusan jika masih ada transaksi tercatat
        $childCheck = $this->ikk->countKegiatanChildren((int) $key);
        if ($childCheck['transaksi'] > 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => "Tidak dapat menghapus kegiatan ini karena masih memiliki {$childCheck['transaksi']} catatan transaksi/realisasi kinerja harian. Hapus riwayat transaksi terlebih dahulu untuk menjaga integritas akuntabilitas.",
            ]);
        }

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            // Bersihkan relasi pengampu di data_ikk_pengampu
            $this->ikk->cleanupPengampu('kegiatan', (int) $key);

            $this->apps->removeData($key, 'data_ikk_kegiatan');

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data Kegiatan.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data Kegiatan berhasil dihapus.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // 4. MANAJEMEN PENGAMPU (UNIT/TIM KERJA)
    // =========================================================================
    public function getDataPengampu()
    {
        $builder = $this->ikk->getPengampuBuilder();
        $columns = [
            'id', 
            ['data' => 'tipe_relasi', 'search' => 'a.tipe_relasi'],
            ['data' => 'nama_relasi', 'search' => ['c.nama_indikator', 'd.nama_kegiatan']],
            ['data' => 'nama_timkerja', 'search' => 'b.nama_timkerja']
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getAssignedPengampu()
    {
        $relasi_id   = (int) $this->request->getPost('relasi_id');
        $tipe_relasi = $this->request->getPost('tipe_relasi') ?: 'kegiatan';

        $assignedIds = $this->ikk->getAssignedPengampuIds($tipe_relasi, $relasi_id);
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $assignedIds,
        ]);
    }

    public function storePengampu()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $tipe_relasi = $this->request->getPost('tipe_relasi') ?: 'kegiatan';
        $relasi_id   = (int) $this->request->getPost('relasi_id');
        $timkerja_id = $this->request->getPost('timkerja_id');

        if ($relasi_id <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kegiatan tidak valid.']);
        }

        if (!is_array($timkerja_id)) {
            $timkerja_id = ($timkerja_id !== '' && $timkerja_id !== null) ? [$timkerja_id] : [];
        }

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            $this->ikk->syncPengampu($tipe_relasi, $relasi_id, $timkerja_id);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memperbarui penugasan Tim Kerja.']);
            }

            $db->transCommit();
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Tim Kerja Pengampu berhasil diperbarui.',
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function deletePengampu()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        $this->apps->removeData($key, 'data_ikk_pengampu');
        return $this->response->setJSON(['status' => 'success', 'message' => 'Penugasan Pengampu berhasil dihapus.']);
    }

    // =========================================================================
    // 5. TRANSAKSI HARIAN / REALISASI MINGGUAN
    // =========================================================================
    public function getDataTransaksi()
    {
        $builder      = $this->ikk->getTransaksiBuilder();
        $sasaran_id   = $this->request->getPost('sasaran_id');
        $indikator_id = $this->request->getPost('indikator_id');
        $kegiatan_id  = $this->request->getPost('kegiatan_id');
        $timkerja_id  = $this->request->getPost('timkerja_id');
        $tahun        = $this->request->getPost('tahun');
        $triwulan     = $this->request->getPost('triwulan');

        if (!empty($sasaran_id)) {
            $builder->where('d.id', (int) $sasaran_id);
        }
        if (!empty($indikator_id)) {
            $builder->where('c.id', (int) $indikator_id);
        }
        if (!empty($kegiatan_id)) {
            $builder->where('a.kegiatan_id', (int) $kegiatan_id);
        }
        if (!empty($tahun)) {
            $builder->where('a.tahun', (int) $tahun);
        }
        if (!empty($triwulan)) {
            $builder->where('a.triwulan', (int) $triwulan);
        }
        if (!empty($timkerja_id)) {
            $builder->where("EXISTS (
                SELECT 1 FROM data_ikk_pengampu p3 
                WHERE p3.tipe_relasi = 'kegiatan' 
                  AND p3.relasi_id = a.kegiatan_id 
                  AND p3.timkerja_id = " . (int) $timkerja_id . "
            )", null, false);
        }

        $columns = [
            'id', 
            ['data' => 'pelaksana_kegiatan_harian', 'search' => 'a.pelaksana_kegiatan_harian'],
            ['data' => 'nama_kegiatan', 'search' => 'b.nama_kegiatan'],
            ['data' => 'nama_indikator', 'search' => 'c.nama_indikator'],
            ['data' => 'nama_sasaran', 'search' => 'd.nama_sasaran'],
            'period_start_date', 
            'period_end_date', 
            'tahun',
            'triwulan',
            'periode_key',
            'target_mingguan', 
            'jumlah_realisasi', 
            'progres_realisasi',
            'bukti_pekerjaan',
            'pengampu_list'
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function storeTransaksi()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key                       = $this->request->getPost('key');
        $kegiatan_id               = (int) $this->request->getPost('kegiatan_id');
        $pelaksana_kegiatan_harian = trim((string) $this->request->getPost('pelaksana_kegiatan_harian'));
        $period_start_date         = $this->request->getPost('period_start_date');
        $period_end_date           = $this->request->getPost('period_end_date');
        $target_mingguan           = trim((string) $this->request->getPost('target_mingguan'));
        $jumlah_realisasi          = $this->request->getPost('jumlah_realisasi');
        $progres_realisasi         = $this->request->getPost('progres_realisasi');
        $bukti_pekerjaan           = trim((string) $this->request->getPost('bukti_pekerjaan'));

        $rules = [
            'kegiatan_id'               => 'required|is_not_unique[data_ikk_kegiatan.id]',
            'pelaksana_kegiatan_harian' => 'required|min_length[3]',
            'period_start_date'         => 'permit_empty|valid_date[Y-m-d]',
            'period_end_date'           => 'permit_empty|valid_date[Y-m-d]',
            'progres_realisasi'         => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'jumlah_realisasi'          => 'permit_empty|numeric',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        // Business Rule: Kegiatan WAJIB memiliki Pengampu sebelum input transaksi
        if (!$this->ikk->hasPengampu($kegiatan_id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Kegiatan ini belum memiliki Tim Kerja Pengampu. Harap tentukan Tim Pengampu pada kegiatan terkait terlebih dahulu sebelum mencatat transaksi realisasi.',
            ]);
        }

        // Logic check: tanggal selesai tidak boleh mendahului tanggal mulai
        if (!empty($period_start_date) && !empty($period_end_date)) {
            if ($period_end_date < $period_start_date) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai periode.',
                ]);
            }
        }

        // Auto derive metadata periode dari tanggal
        $periodInfo = \App\Models\Apps\Services\IkkModel::parsePeriodFromDate($period_start_date, $period_end_date);

        $data = [
            'kegiatan_id'               => $kegiatan_id,
            'pelaksana_kegiatan_harian' => $pelaksana_kegiatan_harian,
            'period_start_date'         => !empty($period_start_date) ? $period_start_date : null,
            'period_end_date'           => !empty($period_end_date) ? $period_end_date : null,
            'tahun'                     => $periodInfo['tahun'],
            'triwulan'                  => $periodInfo['triwulan'],
            'bulan'                     => $periodInfo['bulan'],
            'periode_key'               => $periodInfo['periode_key'],
            'target_mingguan'           => $target_mingguan ?: null,
            'jumlah_realisasi'          => ($jumlah_realisasi !== '' && $jumlah_realisasi !== null) ? (float) $jumlah_realisasi : null,
            'progres_realisasi'         => ($progres_realisasi !== '' && $progres_realisasi !== null) ? (float) $progres_realisasi : null,
            'bukti_pekerjaan'           => $bukti_pekerjaan ?: null,
        ];

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            if (!empty($key)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->apps->updateData($data, $key, 'txn_ikk_harian');
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->apps->storeData($data, 'txn_ikk_harian');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menyimpan transaksi harian.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi harian berhasil disimpan.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function deleteTransaksi()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID tidak valid.']);
        }

        $db = $this->ikk->db;
        $db->transBegin();

        try {
            $this->apps->removeData($key, 'txn_ikk_harian');

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data transaksi.']);
            }

            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data transaksi berhasil dihapus.']);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // 6. HELPER API & OPTIONS UNTUK DROPDOWN
    // =========================================================================
    public function getOptionsSasaran()
    {
        return $this->response->setJSON($this->ikk->getOptionsSasaran());
    }

    public function getOptionsIndikator()
    {
        $sasaran_id = $this->request->getPost('sasaran_id');
        return $this->response->setJSON($this->ikk->getOptionsIndikator($sasaran_id));
    }

    public function getOptionsKegiatan()
    {
        $indikator_id = $this->request->getPost('indikator_id');
        return $this->response->setJSON($this->ikk->getOptionsKegiatan($indikator_id));
    }

    public function getOptionsTimKerja()
    {
        return $this->response->setJSON($this->ikk->getOptionsTimKerja());
    }

    public function getHierarchyTree()
    {
        $filter = [
            'tahun'    => $this->request->getPost('tahun'),
            'triwulan' => $this->request->getPost('triwulan'),
        ];
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $this->ikk->getHierarchyTree($filter),
        ]);
    }

    public function getOptionsTahun()
    {
        return $this->response->setJSON($this->ikk->getAvailableYears());
    }

    /**
     * Download format sample template Excel untuk import transaksi
     */
    public function downloadSample()
    {
        $filePath = FCPATH . 'apps/samples/sample-transaksi-ikk.xlsx';
        if (!file_exists($filePath)) {
            $filePath = ROOTPATH . 'public/apps/samples/sample-transaksi-ikk.xlsx';
        }

        if (!file_exists($filePath)) {
            return $this->response->setStatusCode(404)->setBody('File template sample tidak ditemukan.');
        }

        return $this->response->download($filePath, null)->setFileName('sample-transaksi-ikk.xlsx');
    }

    /**
     * Import data transaksi kinerja dari file Excel
     */
    public function importTransaksi()
    {
        if (!$this->checkServiceAccess()) {
            return $this->unauthorizedResponse();
        }

        $file = $this->request->getFile('file_excel');
        if (!$file || !$file->isValid()) {
            $file = $this->request->getFile('file');
        }

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File tidak valid atau belum diunggah. Silakan pilih file Excel terlebih dahulu.',
            ]);
        }

        $uploader = new ExcelUploader();
        try {
            $uploader->validateFile($file);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        try {
            $rows = $uploader->parseExcel($file->getTempName());
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Gagal membaca format file Excel: ' . $e->getMessage(),
            ]);
        }

        if (!is_array($rows) || count($rows) === 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File Excel kosong atau tidak memiliki data.',
            ]);
        }

        // Deteksi baris header secara dinamis
        $headerIndex = -1;
        foreach ($rows as $idx => $r) {
            if (isset($r[0]) && (stripos((string) $r[0], 'id kegiatan') !== false || stripos((string) $r[0], 'kegiatan_id') !== false)) {
                $headerIndex = $idx;
                break;
            }
        }

        $startRow = ($headerIndex !== -1) ? $headerIndex + 1 : 0;
        if ($startRow >= count($rows)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'File Excel hanya memuat header dan tidak memiliki baris data untuk diimpor.',
            ]);
        }

        // Prefetch data kegiatan & pengampu untuk validasi berjenjang cepat
        $db = $this->ikk->db;
        $kegiatanRows = $db->table('data_ikk_kegiatan')->select('id, nama_kegiatan')->get()->getResultArray();
        $validKegiatan = [];
        foreach ($kegiatanRows as $k) {
            $validKegiatan[$k['id']] = $k['nama_kegiatan'];
        }

        $pengampuRows = $db->table('data_ikk_pengampu')->select('kegiatan_id')->distinct()->get()->getResultArray();
        $kegWithPengampu = [];
        foreach ($pengampuRows as $p) {
            $kegWithPengampu[$p['kegiatan_id']] = true;
        }

        $sessionUser = session()->get('nama') ?? session()->get('username') ?? 'Pelaksana IKK';
        $now = date('Y-m-d H:i:s');
        $dataBatch = [];
        $warnings = [];

        for ($i = $startRow; $i < count($rows); $i++) {
            $row = $rows[$i];
            $excelLineNumber = $i + 1;

            // Lewati baris yang benar-benar kosong
            $nonEmptyCols = array_filter($row, function ($v) {
                return $v !== null && trim((string) $v) !== '';
            });
            if (empty($nonEmptyCols)) {
                continue;
            }

            $kegiatanId = isset($row[0]) ? trim((string) $row[0]) : '';
            if ($kegiatanId === '') {
                $warnings[] = "Baris {$excelLineNumber}: Kolom ID Kegiatan kosong (baris dilewati).";
                continue;
            }

            // Validasi keberadaan kegiatan di DB
            if (!isset($validKegiatan[$kegiatanId])) {
                $warnings[] = "Baris {$excelLineNumber}: ID Kegiatan '{$kegiatanId}' tidak ditemukan dalam master data kegiatan (baris dilewati).";
                continue;
            }

            // Validasi aturan bisnis: Kegiatan wajib memiliki pengampu
            if (!isset($kegWithPengampu[$kegiatanId])) {
                $warnings[] = "Baris {$excelLineNumber}: Kegiatan ID '{$kegiatanId}' belum memiliki Tim Pengampu. Wajib tentukan tim pengampu terlebih dahulu (baris dilewati).";
                continue;
            }

            // Pelaksana kegiatan
            $pelaksana = isset($row[2]) ? trim((string) $row[2]) : '';
            if ($pelaksana === '') {
                $pelaksana = $sessionUser;
            }

            // Tanggal pelaksanaan
            $rawStart = isset($row[3]) ? $row[3] : null;
            $rawEnd = isset($row[4]) ? $row[4] : null;
            $periodStartDate = ExcelUploader::excelDate($rawStart);
            $periodEndDate = ExcelUploader::excelDate($rawEnd);

            if (!empty($periodStartDate) && !empty($periodEndDate) && $periodEndDate < $periodStartDate) {
                $temp = $periodStartDate;
                $periodStartDate = $periodEndDate;
                $periodEndDate = $temp;
            }

            // Derive periode metadata (tahun, triwulan, bulan, periode_key)
            $periodInfo = IkkModel::parsePeriodFromDate($periodStartDate, $periodEndDate);

            // Target & Realisasi
            $targetMingguan = isset($row[5]) ? trim((string) $row[5]) : null;
            
            $rawRealisasi = isset($row[6]) ? trim((string) $row[6]) : '';
            $jumlahRealisasi = null;
            if ($rawRealisasi !== '') {
                $cleanNum = str_replace([' ', ','], ['', '.'], $rawRealisasi);
                if (is_numeric($cleanNum)) {
                    $jumlahRealisasi = (float) $cleanNum;
                }
            }

            $rawProgres = isset($row[7]) ? trim((string) $row[7]) : '';
            $progresRealisasi = null;
            if ($rawProgres !== '') {
                $cleanProg = str_replace(['%', ' ', ','], ['', '', '.'], $rawProgres);
                if (is_numeric($cleanProg)) {
                    $valProg = (float) $cleanProg;
                    // Normalisasi jika persentase ditulis format rasio desimal 0.0 - 1.0
                    if ($valProg > 0 && $valProg <= 1.0 && strpos($rawProgres, '%') === false && (float)$cleanProg < 1.0) {
                        $valProg = round($valProg * 100, 2);
                    }
                    $progresRealisasi = max(0, min(100, $valProg));
                }
            }

            // Bukti pekerjaan
            $buktiPekerjaan = isset($row[8]) ? trim((string) $row[8]) : null;

            $dataBatch[] = [
                'kegiatan_id'               => $kegiatanId,
                'pelaksana_kegiatan_harian' => $pelaksana,
                'period_start_date'         => $periodStartDate ?: null,
                'period_end_date'           => $periodEndDate ?: null,
                'tahun'                     => $periodInfo['tahun'],
                'triwulan'                  => $periodInfo['triwulan'],
                'bulan'                     => $periodInfo['bulan'],
                'periode_key'               => $periodInfo['periode_key'],
                'target_mingguan'           => $targetMingguan ?: null,
                'jumlah_realisasi'          => $jumlahRealisasi,
                'progres_realisasi'         => $progresRealisasi,
                'bukti_pekerjaan'           => $buktiPekerjaan ?: null,
                'created_at'                => $now,
            ];
        }

        if (empty($dataBatch)) {
            $msg = 'Tidak ada baris data transaksi valid yang dapat diimpor.';
            if (!empty($warnings)) {
                $msg .= ' Alasan: ' . implode('; ', array_slice($warnings, 0, 3));
            }
            return $this->response->setJSON([
                'status'   => 'error',
                'message'  => $msg,
                'warnings' => $warnings,
            ]);
        }

        $db->transBegin();
        try {
            $this->apps->insertBatchData($dataBatch, 'txn_ikk_harian');

            // Log ke activity daily logs
            $this->apps->storeData([
                'layanan_id' => 43,
                'tanggal'    => date('Y-m-d'),
                'created_by' => session()->get('username') ?? 'system',
            ], 'activity_daily_logs');

            if ($db->transStatus() === false) {
                $db->transRollback();
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyimpan transaksi ke database.',
                ]);
            }

            $db->transCommit();

            $status = !empty($warnings) ? 'warning' : 'success';
            $msg = 'Berhasil mengimpor ' . count($dataBatch) . ' data transaksi kinerja IKK.';
            if (!empty($warnings)) {
                $msg .= ' (' . count($warnings) . ' baris dilewati).';
            }

            return $this->response->setJSON([
                'status'         => $status,
                'message'        => $msg,
                'imported_count' => count($dataBatch),
                'warnings'       => $warnings,
            ]);
        } catch (\Throwable $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat menyimpan data import: ' . $e->getMessage(),
            ]);
        }
    }
}
