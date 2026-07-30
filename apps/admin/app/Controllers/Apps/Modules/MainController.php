<?php

namespace App\Controllers\Apps\Modules;
use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MainController extends BaseController
{

    public function __construct(){
        $sess = session()->get();
    }

    public function tapnip(){
        $fallbackEvents = [
            '2025 - Penetapan NIP CPNS' => '2025 - Penetapan NIP CPNS',
            '2025 - Penetapan NI PPPK Tahap I' => '2025 - Penetapan NI PPPK Tahap I',
            '2025 - Penetapan NI PPPK Tahap II' => '2025 - Penetapan NI PPPK Tahap II',
            '2025 - Penetapan NI PPPK Paruh Waktu' => '2025 - Penetapan NI PPPK Paruh Waktu',
        ];
        $categoryOptions = $this->apps->getServiceEventOptions(2, $fallbackEvents);

        return $this->renderView('Apps/pages/services/tapnip/main', [
            'seslog' => session()->get(),
            'categoryOptions' => $categoryOptions,
            'docCategoryDefault' => array_key_first($categoryOptions) ?: '',
            'eventCrudEnabled' => $this->apps->serviceEventsTableExists(),
        ]);
    }

    public function pindahInstansi(){
        return $this->renderView('Apps/pages/services/pindahinstansi/main', [
            'seslog' => session()->get(),
        ]);        
    }

    public function pencantumanGelar(){
        return $this->renderView('Apps/pages/services/pencantumangelar/main', [
            'seslog' => session()->get(),
        ]);        
    } 

    public function peninjauanMasaKerja(){
        return $this->renderView('Apps/pages/services/peninjauanmasakerja/main', [
            'seslog' => session()->get(),
        ]);        
    } 

    public function kenaikanPangkat(){
        return $this->renderView('Apps/pages/services/kenaikanpangkat/main', [
            'seslog' => session()->get(),
        ]);        
    }     

    public function cltn(){
        return $this->renderView('Apps/pages/services/cltn/main', [
            'seslog' => session()->get(),
        ]);
    }    

    public function pengaktifanASN(){
        return $this->renderView('Apps/pages/services/pengaktifanasn/main', [
            'seslog' => session()->get(),
        ]);
    }    

    public function pengangkatanCPNS(){
        return $this->renderView('Apps/pages/services/pengangkatancpns/main', [
            'seslog' => session()->get(),
        ]);        
    }

    public function pertekPensiun(){
        return $this->renderView('Apps/pages/services/pertekpensiun/main', [
            'seslog' => session()->get(),
        ]);        
    }   
    
    public function skPensiun(){
        return $this->renderView('Apps/pages/services/skpensiun/main', [
            'seslog' => session()->get(),
        ]);        
    }   
    
    public function regisPensiun(){
        return $this->renderView('Apps/pages/services/regispensiun/main', [
            'seslog' => session()->get(),
        ]);        
    }       

}
