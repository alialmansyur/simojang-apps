<?php

namespace App\Controllers\Apps\Modules;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\Modules\FetchModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Libraries\ExcelUploader;
use App\Libraries\DataTablesLib;

class DTController extends BaseController
{
    public function __construct()
    {
        $this->fetchdata = new FetchModel();
        $this->uploader = new ExcelUploader();
        $this->dataTables = new DataTablesLib();
        $sess = session()->get();
    }

    public function getData(){
        $layanan = (int) $this->request->getPost('layanan');
        $bulan = $this->request->getPost('bulan');
        $docCategory = trim((string) $this->request->getPost('doc_category'));

        if ($layanan <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Layanan tidak valid'
            ]);
        }

        if (!is_array($bulan)) {
            $bulan = [];
        }
        if (count($bulan) > 2) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Maksimal 2 bulan diperbolehkan'
            ]);
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        $builder = $this->fetchdata->getDataUploader($layanan, $bulan, $docCategory);
        $columns = ['id','uid','layanan_id','period','period_date','remarks','doc_type','doc_category','file_name','file_size','mime_type','path_local','created_by','created_at','updated_at'];

        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }

    public function getSummary()
    {
        $layanan = (int) $this->request->getPost('layanan');
        $bulan = $this->request->getPost('bulan');
        $docCategory = trim((string) $this->request->getPost('doc_category'));

        if ($layanan <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Layanan tidak valid'
            ]);
        }

        if (!is_array($bulan)) {
            $bulan = [];
        }

        $bulan = array_values(array_filter(array_map('intval', $bulan), static function ($item) {
            return $item >= 1 && $item <= 12;
        }));

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => $this->fetchdata->getUploaderSummary($layanan, $bulan, $docCategory)
        ]);
    }

    public function getDataDetail(){
        $key = (int) $this->request->getPost('key');
        if ($key <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'error' => 'Kunci data detail tidak valid'
            ]);
        }

        $builder = $this->fetchdata->getDataUploaderDetail($key);
        $columns = ['id','nama','logo','target_tahun','target_bulan','formasi','usul_masuk','ms','bts','tms','sisa','sudah_cetak','belum_cetak','sk_cpppk_proses','sk_cpppk_done','usul_input','ni_proses','ni_done','sk_cetak_proses','sk_cetak_done','jadwal_wait','sk_pppk_done','sla_bawah','sla_atas','keterangan','created_by','created_at'];
        $result = $this->dataTables->render($builder, $columns);
        return $this->response->setJSON($result);
    }    
}
