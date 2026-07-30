<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface; 
use App\Models\Apps\AppsModel;
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use DateTime;

class AjxController extends BaseController
{

    use ResponseTrait;

    public function __construct()
    {
        $this->apps = new AppsModel();
        $sess = session()->get();
    }

    public function killData(){
        $sess = session()->get();
        $key  = $this->request->getPost('key', FILTER_SANITIZE_STRING);
        $tableinfo  = $this->request->getPost('tableinfo', FILTER_SANITIZE_STRING);
        $tableDest  = "data_".$tableinfo;
        $this->apps->removeData($key,$tableDest);
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);
    }

    public function killDataUploader(){
        $key = (int) $this->request->getPost('key');
        if ($key <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => false,
                'message' => 'Kunci data tidak valid',
            ]);
        }

        $this->apps->removeDataByField('upload_id', $key, 'txn_activity_upload_detail');
        $this->apps->removeData($key,'txn_activity_upload_logs');
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Data Berhasil di hapus',
        ]);        
    }

    public function statusData(){
        $sess   = session()->get();
        $key    = $this->request->getPost('key', FILTER_SANITIZE_STRING);
        $status = $this->request->getPost('status',FILTER_SANITIZE_STRING);
        $tableinfo  = $this->request->getPost('tableinfo',FILTER_SANITIZE_STRING);
        $tableDest  = "data_".$tableinfo;
        $this->apps->updateData(array('is_status' => $status),$key,$tableDest);
        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Status Telah di Perbaharui',
        ]);
    }    

    public function updateActiveMenu($menuId = null){
        session()->set('active_menus', $menuId);
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'message' => 'Active menu updated',
            'active_menus' => $menuId
        ]);        
    }

    public function getOS() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $osArray = [
            'Windows'   => 'Windows',
            'Macintosh' => 'Mac OS',
            'Linux'     => 'Linux',
            'Ubuntu'    => 'Ubuntu',
            'iPhone'    => 'iOS',
            'Android'   => 'Android',
        ];
    
        foreach ($osArray as $os => $name) {
            if (stripos($userAgent, $os) !== false) {
                return $name;
            }
        }
    
        return 'Unknown OS';
    }
    
    public function getBrowser() {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $browserArray = [
            'Edge'       => 'Edge',
            'Chrome'     => 'Chrome',
            'Safari'     => 'Safari',
            'Firefox'    => 'Firefox',
            'Opera'      => 'Opera',
            'MSIE'       => 'Internet Explorer',
            'Trident'    => 'Internet Explorer',
        ];
    
        foreach ($browserArray as $browser => $name) {
            if (stripos($userAgent, $browser) !== false) {
                return $name;
            }
        }

        return 'Unknown Browser';
    }

}
