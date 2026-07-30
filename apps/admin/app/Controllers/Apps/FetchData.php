<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\AppsModel;
use CodeIgniter\HTTP\ResponseInterface; 
use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use DateTime;

class FetchData extends BaseController
{
    use ResponseTrait;
    public function __construct()
    {
        $this->apps = new AppsModel();
        $sess = session()->get();

    }

    public function fetchLayanan(){
        $sess = session()->get();
        $user = $sess['username'];

        $request = $this->request->getJSON();
        $keyword = $request->keyword ?? '';
        $unit    = $request->unit ?? 0;
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'list'  => $this->apps->getLayananData($user, $keyword, $unit)
        ]);
    }

    public function fetchTimKerja(){
        $sess = session()->get();
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'list'  => $this->apps->getTimkerja()
        ]);
    }

    public function fetchLayananTimKerja(){
        $sess       = session()->get();
        $request    = $this->request->getJSON();
        $keyword    = $request->keyword ?? '';        
        $layananID  = $request->layanan_id ?? ''; 
        return $this->response->setStatusCode(200)->setJSON([
            'key'   => $layananID, 
            'status' => 'success',
            'list'  => $this->apps->getLayananTimkerja($layananID, $keyword), 
            'progress' => $this->apps->progressLayananDaily($layananID)
        ]);        
    }

    public function fetchNSPKData(){
        $sess       = session()->get();
        $request    = $this->request->getJSON();
        $keyword    = $request->keyword ?? '';        
        $layananID  = $request->layanan_id ?? ''; 
        return $this->response->setStatusCode(200)->setJSON([
            'key'   => $layananID,
            'status' => 'success',
            'list'  => $this->apps->getNPSKData(), 
        ]);             
    }

    public function fetchIntegrasiData(){
        $sess       = session()->get();
        $request    = $this->request->getJSON();
        $keyword    = $request->keyword ?? '';        
        $jenisID    = $request->jenisID ?? ''; 
        return $this->response->setStatusCode(200)->setJSON([
            'key'   => $jenisID,
            'keyword'   => $keyword,
            'status' => 'success',
            'list'  => $this->apps->getIntegrasiData($keyword,$jenisID),
            'progress'  => $this->apps->getIntegrasiDataTotal($jenisID),
        ]);            
    }

    public function fetchLayananByNIP(){
        $sess = session()->get();
        $user = $sess['username'];
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'list'  => $this->apps->getLayananEnrolledData($user)
        ]);
    }

    public function enrolltask(){
        $sess = session()->get();
        $user = $sess['username'];

        $request    = $this->request->getJSON();
        $enroll_id  = $request->enrolled ?? '';

        $validate   = $this->apps->validateEnrolled($user,$enroll_id);
        if (!empty($validate)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Task sudah di enroll, silahkan cek pada menu My Task'
            ]);
        }

        $data = array('nip' => $user,'layanan_id' => $enroll_id);
        $this->apps->storeData($data, 'txn_enroll');
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'message' => 'Task has been enrolled',
        ]);
    }
 
    public function getInstansi()
    {
        $search = trim((string) $this->request->getPost('search'));
        $data = $this->apps->getInstansi($search); 

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id'   => $row['kodeins'],
                'text' => $row['nama']
            ];
        }

        return $this->response->setJSON($options);
    }

    public function getNaskah()
    {
        $search  = trim($this->request->getPost('search'));
        $jenisId = $this->request->getPost('jenis_id');

        $data = $this->apps->getNaskahData($search, $jenisId);

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id'   => $row['id'],
                'text' => $row['nama']
            ];
        }

        return $this->response->setJSON($options);
    }

    public function getEvent()
    {
        $search = trim($this->request->getPost('search'));
        $data = $this->apps->getEventData($search);

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id'   => $row['id'],
                'text' => $row['nama']
            ];
        }

        return $this->response->setJSON($options);
    }

    public function getTK()
    {
        $search = trim($this->request->getPost('search'));
        $data = $this->apps->getTKData($search);

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id'   => $row['id'],
                'text' => $row['nama']
            ];
        }

        return $this->response->setJSON($options);
    }


    public function getStepMT(){
        $data = $this->apps->getStepMT();

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id' => $row['id'],
                'text' => $row['step_name']
            ];
        }

        return $this->response->setJSON($options);
    }

    public function getStepIntegrasi(){
        $data = $this->apps->getStepIntegrasi();

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id' => $row['id'],
                'text' => $row['jenis']
            ];
        }

        return $this->response->setJSON($options);
    }    

    public function getSelect2List()
    {
        $search = trim($this->request->getPost('search'));
        $source = $this->request->getPost('source');

        if (!$source) {
            return $this->response->setJSON([]);
        }

        $data = $this->apps->getSelect2Data($source, $search);

        $options = [];
        foreach ($data as $row) {
            $options[] = [
                'id'   => $row['id'],
                'text' => $row['nama']
            ];
        }

        return $this->response->setJSON($options);
    }

}
