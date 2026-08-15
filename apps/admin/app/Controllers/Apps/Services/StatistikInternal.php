<?php

namespace App\Controllers\Apps\Services;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

use App\Models\Apps\AppsModel;
use App\Models\Apps\Services\STKInternalModel;

class StatistikInternal extends BaseController
{
    protected $apps;
    protected $simodel;
    protected $uploader;
    protected $dataTables;

    public function __construct()
    {
        $this->apps = new AppsModel();
        $this->simodel = new STKInternalModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function index()
    {
        return $this->renderView('Apps/pages/services/statistikinternal/main', [
            'seslog' => session()->get(),
        ]);
    }

    public function storeData()
    {
        $sess = session()->get();
        $key = $this->request->getPost('key');
        $nip = $this->request->getPost('nip');

        $rules = [
            'nama' => 'required|min_length[3]',
            'gender' => 'required',
            // 'status_pegawai' => 'required',
            'tgl_lahir' => 'required|valid_date',
            'unit_kerja' => 'required',
            'jenis_jabatan' => 'required',
            'email' => 'permit_empty|valid_email'
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $this->validator->getErrors()),
                'data' => $this->request->getPost()
            ]);
        }

        $dataInsert = [
            'nip' => $nip,
            'nama' => $this->request->getPost('nama'),
            'gender' => $this->request->getPost('gender'),
            'tgl_lahir' => $this->request->getPost('tgl_lahir'),
            'menikah' => $this->request->getPost('menikah'),
            'status_pegawai_id' => $this->request->getPost('status_pegawai'),
            'agama_id' => $this->request->getPost('agama'),
            'pendidikan_id' => $this->request->getPost('pendidikan'),
            'jabatan_id' => $this->request->getPost('jabatan'),
            'gol_id' => $this->request->getPost('golongan'),
            'pangkat_id' => $this->request->getPost('pangkat'),
            'tmt_gol' => $this->request->getPost('tmt_gol'),
            'unit_kerja_id' => is_array($this->request->getPost('unit_kerja')) ? implode(',', $this->request->getPost('unit_kerja')) : $this->request->getPost('unit_kerja'),
            'unit_sk_id' => $this->request->getPost('unit_sk'),
            'jenis_jabatan_id' => $this->request->getPost('jenis_jabatan'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'is_status' => 1
        ];

        if (!empty($key)) {
            $this->apps->updateData($dataInsert, $key, 'data_pegawai');
        } else {

            if ($this->simodel->isDuplicateIntegrasi($nip) > 0) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'NIP/Data sudah ada (duplikat)'
                ]);
            }

            $this->apps->storeData($dataInsert, 'data_pegawai');
            $this->apps->storeData(
                [
                    'layanan_id' => 35,
                    'tanggal' => date('Y-m-d'),
                    'created_by' => $sess['username']
                ],
                'activity_daily_logs'
            );
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'message' => 'Data berhasil disimpan.',
            'key' => $key
        ]);
    }

    public function removeData()
    {
        $sess = session()->get();
        $key = trim((string) $this->request->getPost('key'));
        if ($key === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }
        $this->apps->removeData($key, 'data_pegawai');
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function getDataPegawai()
    {
        $sess = session()->get();
        $unit = $this->request->getPost('unit');
        $mode = $this->request->getPost('mode') ?? 'pegawai';

        if (!is_array($unit)) {
            $unit = [];
        }

        $builder = $this->simodel->getBuilder('pegawai', [
            'unit' => $unit,
            'mode' => $mode
        ]);

        $columns = $this->simodel->getColumns('pegawai');
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getDataPetaJabatan()
    {
        $unit = $this->request->getPost('unit');
        if (!is_array($unit)) {
            $unit = [];
        }

        $builder = $this->simodel->getBuilder('peta_jabatan', [
            'unit' => $unit
        ]);

        $columns = $this->simodel->getColumns('peta_jabatan');
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getDataAccum()
    {
        $request = $this->request->getJSON();
        $unit = $request->unit ?? []; // pastikan array

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'list' => $this->simodel->getAccumulation($unit),
            'unit' => $unit
        ]);
    }

    public function getSummary()
    {
        $unit = $this->request->getPost('unit');
        $mode = $this->request->getPost('mode') ?? 'pegawai';

        if (!is_array($unit)) {
            $unit = [];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->simodel->getSummary($unit, $mode),
        ]);
    }

    public function getMasterPendidikan()
    {
        $data = $this->simodel->getMasterData('data_pegawai_pendidikan');
        return $this->response->setJSON($data);
    }

    public function getMasterUnitKerja()
    {
        $data = $this->simodel->getMasterData('data_pegawai_unit_kerja');
        return $this->response->setJSON($data);
    }

    public function getMasterUnitSK()
    {
        $data = $this->simodel->getMasterData('data_pegawai_unit_sk');
        return $this->response->setJSON($data);
    }

    public function getMasterJenisJabatan()
    {
        $data = $this->simodel->getMasterData('data_pegawai_jenis_jabatan');
        return $this->response->setJSON($data);
    }

    public function getMasterJenisPegawai()
    {
        $data = $this->simodel->getMasterData('data_pegawai_jenis_pegawai');
        return $this->response->setJSON($data);
    }

    public function getMasterAgama()
    {
        $data = $this->simodel->getMasterData('data_pegawai_agama');
        return $this->response->setJSON($data);
    }

    public function getMasterGolongan()
    {
        $data = $this->simodel->getMasterData('data_pegawai_golongan');
        return $this->response->setJSON($data);
    }

    public function getMasterPangkat()
    {
        $data = $this->simodel->getMasterData('data_pegawai_pangkat');
        return $this->response->setJSON($data);
    }

    public function getMasterJabatan()
    {
        $data = $this->simodel->getMasterData('data_pegawai_jabatan');
        return $this->response->setJSON($data);
    }

    public function storeDataMaster()
    {
        $type = $this->request->getPost('type');
        $id = $this->request->getPost('id');
        $nama = $this->request->getPost('nama');

        $map = [
            'data_pegawai_pendidikan' => 'data_pegawai_pendidikan',
            'data_pegawai_unit_kerja' => 'data_pegawai_unit_kerja',
            'data_pegawai_unit_sk' => 'data_pegawai_unit_sk',
            'data_pegawai_jenis_jabatan' => 'data_pegawai_jenis_jabatan',
            'data_pegawai_jenis_pegawai' => 'data_pegawai_jenis_pegawai',
            'data_pegawai_agama' => 'data_pegawai_agama',
            'data_pegawai_jabatan' => 'data_pegawai_jabatan'
        ];

        if (!isset($map[$type])) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error']);
        }

        $table = $map[$type];

        // Build data array — always include nama
        $data = ['nama' => $nama];

        // Handle extra columns for data_pegawai_jabatan
        if ($type === 'data_pegawai_jabatan') {
            $data['kelas_jabatan'] = $this->request->getPost('kelas_jabatan');
            $data['kebutuhan'] = $this->request->getPost('kebutuhan');
        }

        if ($id) {
            $this->apps->updateData($data, $id, $table);
        } else {
            $this->apps->storeData($data, $table);
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    public function deleteDataMaster()
    {
        $type = $this->request->getPost('type');
        $id = $this->request->getPost('id');

        $map = [
            'data_pegawai_pendidikan' => 'data_pegawai_pendidikan',
            'data_pegawai_unit_kerja' => 'data_pegawai_unit_kerja',
            'data_pegawai_unit_sk' => 'data_pegawai_unit_sk',
            'data_pegawai_jenis_jabatan' => 'data_pegawai_jenis_jabatan',
            'data_pegawai_jenis_pegawai' => 'data_pegawai_jenis_pegawai',
            'data_pegawai_agama' => 'data_pegawai_agama',
            'data_pegawai_jabatan' => 'data_pegawai_jabatan'
        ];

        if (!isset($map[$type]) || !$id) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Data tidak valid'
            ]);
        }

        $this->apps->removeData($id, $map[$type]);

        return $this->response->setJSON([
            'status' => 'success'
        ]);
    }



}


