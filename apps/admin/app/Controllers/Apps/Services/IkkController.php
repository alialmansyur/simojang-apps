<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\DataTablesLib;
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

    public function index()
    {
        return $this->renderView('Apps/pages/services/ikk/main', [
            'seslog' => session()->get(),
        ]);
    }

    // --- Sasaran ---
    public function getDataSasaran()
    {
        $builder = $this->ikk->getSasaranBuilder();
        $columns = ['id', 'no_urut', 'nama_sasaran', 'created_at'];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function storeSasaran()
    {
        $key = $this->request->getPost('key');
        $nama_sasaran = $this->request->getPost('nama_sasaran');
        $no_urut = $this->request->getPost('no_urut');

        $rules = [
            'nama_sasaran' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'nama_sasaran' => $nama_sasaran,
            'no_urut' => $no_urut,
        ];

        if (!empty($key)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->apps->updateData($data, $key, 'data_ikk_sasaran');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->apps->storeData($data, 'data_ikk_sasaran');
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Data Sasaran berhasil disimpan.'
        ]);
    }

    public function deleteSasaran()
    {
        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) {
            return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        }
        $this->apps->removeData($key, 'data_ikk_sasaran');
        return $this->response->setJSON(['status' => true, 'message' => 'Data berhasil dihapus.']);
    }

    // --- Indikator ---
    public function getDataIndikator()
    {
        $builder = $this->ikk->getIndikatorBuilder();
        $sasaran_id = $this->request->getPost('sasaran_id');
        if (!empty($sasaran_id)) {
            $builder->where('a.sasaran_id', $sasaran_id);
        }
        $columns = [
            'id', 
            ['data' => 'nama_sasaran', 'search' => 'b.nama_sasaran'],
            'no_urut', 
            ['data' => 'nama_indikator', 'search' => 'a.nama_indikator'],
            'satuan', 
            'target_volume'
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function storeIndikator()
    {
        $key = $this->request->getPost('key');
        $sasaran_id = $this->request->getPost('sasaran_id');
        $nama_indikator = $this->request->getPost('nama_indikator');
        $no_urut = $this->request->getPost('no_urut');
        $satuan = $this->request->getPost('satuan');
        $target_volume = $this->request->getPost('target_volume');

        $rules = [
            'sasaran_id' => 'required',
            'nama_indikator' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'sasaran_id' => $sasaran_id,
            'nama_indikator' => $nama_indikator,
            'no_urut' => $no_urut,
            'satuan' => $satuan,
            'target_volume' => $target_volume,
        ];

        if (!empty($key)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->apps->updateData($data, $key, 'data_ikk_indikator');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->apps->storeData($data, 'data_ikk_indikator');
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Data Indikator berhasil disimpan.']);
    }

    public function deleteIndikator()
    {
        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        $this->apps->removeData($key, 'data_ikk_indikator');
        return $this->response->setJSON(['status' => true, 'message' => 'Data berhasil dihapus.']);
    }

    // --- Kegiatan ---
    public function getDataKegiatan()
    {
        $builder = $this->ikk->getKegiatanBuilder();
        $indikator_id = $this->request->getPost('indikator_id');
        if (!empty($indikator_id)) {
            $builder->where('a.indikator_id', $indikator_id);
        }
        $columns = [
            'id', 
            ['data' => 'nama_indikator', 'search' => 'b.nama_indikator'],
            ['data' => 'nama_sasaran', 'search' => 'c.nama_sasaran'],
            'no_urut', 
            ['data' => 'nama_kegiatan', 'search' => 'a.nama_kegiatan'],
            'satuan', 
            'target_volume'
        ];
        $result = $this->dataTables->render($builder, $columns);
        
        // Add assigned pengampu to the response data
        $data = $result['data'];
        foreach ($data as &$row) {
            $pengampuList = $this->apps->db->table('data_ikk_pengampu p')
                ->select('t.nama_timkerja')
                ->join('data_timkerja t', 'p.timkerja_id = t.id')
                ->where('p.tipe_relasi', 'kegiatan')
                ->where('p.relasi_id', $row['id'])
                ->get()->getResultArray();
            $row['pengampu_list'] = implode(', ', array_column($pengampuList, 'nama_timkerja'));
        }
        $result['data'] = $data;

        return $this->response->setJSON($result);
    }

    public function storeKegiatan()
    {
        $key = $this->request->getPost('key');
        $indikator_id = $this->request->getPost('indikator_id');
        $nama_kegiatan = $this->request->getPost('nama_kegiatan');
        $no_urut = $this->request->getPost('no_urut');
        $satuan = $this->request->getPost('satuan');
        $target_volume = $this->request->getPost('target_volume');

        $rules = [
            'indikator_id' => 'required',
            'nama_kegiatan' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'indikator_id' => $indikator_id,
            'nama_kegiatan' => $nama_kegiatan,
            'no_urut' => $no_urut,
            'satuan' => $satuan,
            'target_volume' => $target_volume,
        ];

        if (!empty($key)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->apps->updateData($data, $key, 'data_ikk_kegiatan');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->apps->storeData($data, 'data_ikk_kegiatan');
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Data Kegiatan berhasil disimpan.']);
    }

    public function deleteKegiatan()
    {
        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        $this->apps->removeData($key, 'data_ikk_kegiatan');
        return $this->response->setJSON(['status' => true, 'message' => 'Data berhasil dihapus.']);
    }

    // --- Pengampu ---
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

    public function storePengampu()
    {
        $key = $this->request->getPost('key');
        $tipe_relasi = $this->request->getPost('tipe_relasi'); // 'indikator' or 'kegiatan'
        $relasi_id = $this->request->getPost('relasi_id');
        $timkerja_id = $this->request->getPost('timkerja_id'); // array for multiselect

        $rules = [
            'tipe_relasi' => 'required',
            'relasi_id' => 'required',
            'timkerja_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        if (!is_array($timkerja_id)) {
            $timkerja_id = [$timkerja_id];
        }

        if (!empty($key)) {
            // Edit mode (typically just single timkerja_id selected)
            $tid = $timkerja_id[0];
            if ($this->ikk->checkDuplicatePengampu($tipe_relasi, $relasi_id, $tid, $key)) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Tim Kerja ini sudah menjadi pengampu pada entitas tersebut.']);
            }
            $this->apps->updateData(['tipe_relasi' => $tipe_relasi, 'relasi_id' => $relasi_id, 'timkerja_id' => $tid], $key, 'data_ikk_pengampu');
        } else {
            // Create mode (can be multiple)
            foreach ($timkerja_id as $tid) {
                if (!$this->ikk->checkDuplicatePengampu($tipe_relasi, $relasi_id, $tid)) {
                    $this->apps->storeData(['tipe_relasi' => $tipe_relasi, 'relasi_id' => $relasi_id, 'timkerja_id' => $tid], 'data_ikk_pengampu');
                }
            }
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Pengampu berhasil disimpan.']);
    }

    public function deletePengampu()
    {
        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        $this->apps->removeData($key, 'data_ikk_pengampu');
        return $this->response->setJSON(['status' => true, 'message' => 'Data berhasil dihapus.']);
    }

    // --- Transaksi Harian ---
    public function getDataTransaksi()
    {
        $builder = $this->ikk->getTransaksiBuilder();
        $kegiatan_id = $this->request->getPost('kegiatan_id');
        if (!empty($kegiatan_id)) {
            $builder->where('a.kegiatan_id', $kegiatan_id);
        }
        $columns = [
            'id', 
            ['data' => 'pelaksana_kegiatan_harian', 'search' => 'a.pelaksana_kegiatan_harian'],
            ['data' => 'nama_kegiatan', 'search' => 'b.nama_kegiatan'],
            ['data' => 'nama_indikator', 'search' => 'c.nama_indikator'],
            ['data' => 'nama_sasaran', 'search' => 'd.nama_sasaran'],
            'period_start_date', 'period_end_date', 'target_mingguan', 'jumlah_realisasi', 'progres_realisasi'
        ];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function storeTransaksi()
    {
        $key = $this->request->getPost('key');
        $kegiatan_id = $this->request->getPost('kegiatan_id');
        $pelaksana_kegiatan_harian = $this->request->getPost('pelaksana_kegiatan_harian');
        $period_start_date = $this->request->getPost('period_start_date');
        $period_end_date = $this->request->getPost('period_end_date');
        $target_mingguan = $this->request->getPost('target_mingguan');
        $jumlah_realisasi = $this->request->getPost('jumlah_realisasi');
        $progres_realisasi = $this->request->getPost('progres_realisasi');
        $bukti_pekerjaan = $this->request->getPost('bukti_pekerjaan');

        $rules = [
            'kegiatan_id' => 'required',
            'pelaksana_kegiatan_harian' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
            ]);
        }

        $data = [
            'kegiatan_id' => $kegiatan_id,
            'pelaksana_kegiatan_harian' => $pelaksana_kegiatan_harian,
            'period_start_date' => empty($period_start_date) ? null : $period_start_date,
            'period_end_date' => empty($period_end_date) ? null : $period_end_date,
            'target_mingguan' => $target_mingguan,
            'jumlah_realisasi' => $jumlah_realisasi,
            'progres_realisasi' => $progres_realisasi,
            'bukti_pekerjaan' => $bukti_pekerjaan,
        ];

        if (!empty($key)) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $this->apps->updateData($data, $key, 'txn_ikk_harian');
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->apps->storeData($data, 'txn_ikk_harian');
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Transaksi berhasil disimpan.']);
    }

    public function deleteTransaksi()
    {
        $key = trim((string) $this->request->getPost('key'));
        if (empty($key)) return $this->response->setJSON(['status' => false, 'message' => 'ID tidak valid.']);
        $this->apps->removeData($key, 'txn_ikk_harian');
        return $this->response->setJSON(['status' => true, 'message' => 'Data berhasil dihapus.']);
    }

    // --- Options API for cascading dropdowns ---
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
}
