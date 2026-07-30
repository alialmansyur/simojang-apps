<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\AppsModel;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class DataMasterController extends BaseController
{

    public function __construct()
    {
        $sess = session()->get();
        $this->apps = new AppsModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();               
    }

    public function datapegawai(){
        $data = array(
            'title'     => 'Kelola Pegawai',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/data/pegawai', $data);
    }

    public function datainstansi(){
        $data = array(
            'title'     => 'Kelola Instansi',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/data/instansi', $data);
    }

    public function datass(){
        $data = array(
            'title'     => 'Kelola SS/IKU',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/_blank', $data);
    }

    public function datalayanan(){
        $data = array(
            'title'     => 'Kelola Layanan',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/_blank', $data);
    }

    public function datahakakses(){
        $data = array(
            'title'     => 'Hak Akses',
            'seslog'    => session()->get(),
        );
        return $this->renderView('Apps/pages/_blank', $data);
    }

    public function datalog(){
        $data = array(
            'title'     => 'Log Pengguna',
            'seslog'    => session()->get(),
        );
        return $this->renderView('Apps/pages/_blank', $data);
    }

    public function getPegawai(){
        $sess       = session()->get();
        $builder    = $this->apps->getDataPegawai();
        $columns    = ['id','nip', 'nama', 'gender', 'menikah','agama','pendidikan','gol','pangkat','tgl_lahir','phone','email','is_status','nama_formatted','updated_at'];
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getInstansi(){
        $sess       = session()->get();
        $builder    = $this->apps->getDataInstansi();
        $columns    = ['id','kodeins', 'nama', 'kanreg', 'is_status', 'updated_at'];
        $result     = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }    

}
