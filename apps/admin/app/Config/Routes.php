<?php

namespace Config;
 
use CodeIgniter\Routing\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes = Services::routes(); 

if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
} 

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Auth');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('/', 'Auth\Auth::index');
$routes->get('/login', 'Auth\Auth::index');
$routes->post('/authprocess', 'Auth\Auth::authprocess');
$routes->get('/logout', 'Auth\Auth::logout');

// $routes->get('backup-database', 'Apps\AppsController::backupDatabase');

$routes->group('', ['filter' => ['jwtauth', 'rbac', 'serviceaccess']], function ($routes) {
    // ----------------------------------------------------------------
    // Route untuk Main Services
    $routes->get('/home', 'Apps\AppsController::dashboard');
    $routes->get('/explore-task', 'Apps\AppsController::explore');
    $routes->get('/resume-task', 'Apps\AppsController::resume');
    $routes->get('/history-task', 'Apps\AppsController::history');
    $routes->get('/timkerja', 'Apps\AppsController::timkerja');
    $routes->get('/timkerja-layanan/(:any)', 'Apps\AppsController::timkerjalayanan/$1');    
    $routes->post('/fetch-layanan', 'Apps\FetchData::fetchLayanan');
    $routes->post('/fetch-timkerja', 'Apps\FetchData::fetchTimKerja'); 
    $routes->post('/fetch-layanan-timkerja', 'Apps\FetchData::fetchLayananTimKerja');
    $routes->post('/fetch-layanan-enrolled', 'Apps\FetchData::fetchLayananByNIP');
    $routes->post('/store-enroll', 'Apps\FetchData::enrolltask');        
    
    // Galeri Kegiatan
    $routes->get('/activity-gallery', 'Apps\ActivityGalleryController::index');
    $routes->post('/activity-gallery/get-data', 'Apps\ActivityGalleryController::getData');
    $routes->post('/activity-gallery/store', 'Apps\ActivityGalleryController::store');
    $routes->post('/activity-gallery/delete', 'Apps\ActivityGalleryController::delete');

    // Kalender Kegiatan
    $routes->get('/calendar-event', 'Apps\CalendarEventController::index');

    // $routes->post('/fetch-nspk-data', 'Apps\FetchData::fetchNSPKData');
    // $routes->post('/fetch-integrasi-data', 'Apps\FetchData::fetchIntegrasiData');
    // ----------------------------------------------------------------
    // Kelola Data 
    $routes->get('/manage-pegawai', 'Apps\DataMasterController::datapegawai');
    $routes->get('/manage-instansi', 'Apps\DataMasterController::datainstansi');
    $routes->get('/manage-iku', 'Apps\DataMasterController::datalayanan');
    $routes->get('/manage-layanan', 'Apps\SettingManagerController::serviceManager');
    $routes->get('/manage-role', 'Apps\SettingManagerController::roleManager');
    $routes->get('/manage-log', 'Apps\DataMasterController::datass'); 
    $routes->get('/manage-setting', 'Apps\SettingManagerController::systemSetting');
    $routes->get('/manage-smtp', 'Apps\SettingManagerController::smtpSetting');
    $routes->get('/download/database-backup', 'Apps\DatabaseBackupController::download');
    $routes->get('/download/schema-rebuild-090326', 'Apps\DatabaseBackupController::downloadRebuildScript090326');
    $routes->get('/execute/schema-rebuild-090326', 'Apps\DatabaseBackupController::executeRebuildScript090326');
    $routes->get('/audit/database-schema-090326', 'Apps\DatabaseBackupController::auditSchema090326');
    $routes->get('/migrate/database-schema-090326', 'Apps\DatabaseBackupController::migrateSchema090326');
    $routes->post('/migrate/database-schema-090326', 'Apps\DatabaseBackupController::migrateSchema090326');
    $routes->get('/ref', 'Apps\RefController::index');
    $routes->get('/ref/(:segment)', 'Apps\RefController::detail/$1');
    $routes->get('/api/ref/tables', 'Apps\RefApiController::tables');
    $routes->get('/api/ref/(:segment)/schema', 'Apps\RefApiController::schema/$1');
    $routes->get('/api/ref/(:segment)', 'Apps\RefApiController::list/$1');
    $routes->post('/api/ref/(:segment)', 'Apps\RefApiController::create/$1');
    $routes->put('/api/ref/(:segment)/(:segment)', 'Apps\RefApiController::update/$1/$2');
    $routes->delete('/api/ref/(:segment)/(:segment)', 'Apps\RefApiController::delete/$1/$2');
    $routes->get('/api/manage-role/users', 'Apps\SettingManagerController::getRoleUsers');
    $routes->get('/api/manage-role/tree', 'Apps\SettingManagerController::getRoleTree');
    $routes->post('/api/manage-role/toggle', 'Apps\SettingManagerController::toggleRolePermission');
    $routes->get('/api/menus', 'Apps\AccessManagerApiController::menus');
    $routes->post('/api/menus', 'Apps\AccessManagerApiController::createMenu');
    $routes->put('/api/menus/(:num)', 'Apps\AccessManagerApiController::updateMenu/$1');
    $routes->delete('/api/menus/(:num)', 'Apps\AccessManagerApiController::deleteMenu/$1');
    $routes->get('/api/users/search', 'Apps\AccessManagerApiController::usersSearch');
    $routes->post('/api/users', 'Apps\AccessManagerApiController::createUser');
    $routes->get('/api/users/(:num)/permissions', 'Apps\AccessManagerApiController::userPermissions/$1');
    $routes->put('/api/users/(:num)/permissions', 'Apps\AccessManagerApiController::syncUserPermissions/$1');
    $routes->get('/api/services', 'Apps\AccessManagerApiController::services');
    $routes->get('/api/services/(:num)', 'Apps\AccessManagerApiController::serviceDetail/$1');
    $routes->put('/api/services/(:num)/access', 'Apps\AccessManagerApiController::updateServiceAccess/$1');
    $routes->get('/api/services/(:num)/assignees', 'Apps\AccessManagerApiController::serviceAssignees/$1');
    $routes->put('/api/services/(:num)/assignees', 'Apps\AccessManagerApiController::syncServiceAssignees/$1');
    $routes->get('/api/manage-layanan/list', 'Apps\SettingManagerController::getServiceList');
    $routes->get('/api/manage-layanan/detail', 'Apps\SettingManagerController::getServiceDetail');
    $routes->get('/api/manage-layanan/pegawai', 'Apps\SettingManagerController::getServicePegawai');
    $routes->post('/api/manage-layanan/mode', 'Apps\SettingManagerController::saveServiceModeApi');
    $routes->post('/api/manage-layanan/assign/add', 'Apps\SettingManagerController::addServiceAssigneeApi');
    $routes->post('/api/manage-layanan/assign/delete', 'Apps\SettingManagerController::removeServiceAssigneeApi');
    $routes->get('/api/manage-setting/data', 'Apps\SettingManagerController::getSystemSettingData');
    $routes->post('/api/manage-setting/save', 'Apps\SettingManagerController::saveSystemSettingApi');
    $routes->get('/api/manage-smtp/data', 'Apps\SettingManagerController::getSmtpSettingData');
    $routes->post('/api/manage-smtp/save', 'Apps\SettingManagerController::saveSmtpSettingApi');
    $routes->post('/store/pull-datalist-pegawai', 'Apps\DataMasterController::getPegawai');
    $routes->post('/store/pull-datalist-instansi', 'Apps\DataMasterController::getInstansi');    
    $routes->post('/store/system-setting', 'Apps\SettingManagerController::saveSystemSetting');
    $routes->post('/store/smtp-setting', 'Apps\SettingManagerController::saveSmtpSetting');
    $routes->post('/setting/service/mode', 'Apps\SettingManagerController::saveServiceMode');
    $routes->post('/setting/service/assign/add', 'Apps\SettingManagerController::addServiceAssignee');
    $routes->post('/setting/service/assign/delete', 'Apps\SettingManagerController::removeServiceAssignee');
    // ---------------------------------------------------------------- 
    // Fetch Data LOV Options
    $routes->post('/instansi-list', 'Apps\FetchData::getInstansi');
    $routes->post('/naskah-list', 'Apps\FetchData::getNaskah');
    $routes->post('/timkerja-list', 'Apps\FetchData::getTK');
    $routes->post('/event-list', 'Apps\FetchData::getEvent');
    $routes->post('/step-mt-list', 'Apps\FetchData::getStepMT');
    $routes->post('/select2/list', 'Apps\FetchData::getSelect2List');
    $routes->post('/step-integrasi-list', 'Apps\FetchData::getStepIntegrasi');
    // ----------------------------------------------------------------
    // Function General
    $routes->post('/change-password', 'Auth\Auth::changePassword');
    $routes->post('/update-profil', 'Apps\AjxController::updateProfile');
    $routes->post('/remove-data', 'Apps\AjxController::killData');
    $routes->post('/status-data', 'Apps\AjxController::statusData');
    $routes->post('/reset-data', 'Apps\AjxController::resetPassword');
    $routes->post('/uploadAvatar', 'Apps\AjxController::uploadAvatar');    
    // ----------------------------------------------------------------
    // Route untuk Modules Services (Layanan General method nya sama semua) 
    $routes->get('/apps-tapnip', 'Apps\Modules\MainController::tapnip');
    $routes->get('/api/apps-tapnip/events', 'Apps\Modules\TapnipEventController::index');
    $routes->post('/api/apps-tapnip/events', 'Apps\Modules\TapnipEventController::create');
    $routes->post('/api/apps-tapnip/events/update', 'Apps\Modules\TapnipEventController::update');
    $routes->post('/api/apps-tapnip/events/delete', 'Apps\Modules\TapnipEventController::delete');
    $routes->get('/apps-cltn', 'Apps\Modules\MainController::cltn');       
    $routes->get('/apps-pi', 'Apps\Modules\MainController::pindahInstansi');    
    $routes->get('/apps-pg', 'Apps\Modules\MainController::pencantumanGelar');    
    $routes->get('/apps-pmk', 'Apps\Modules\MainController::peninjauanMasaKerja');    
    $routes->get('/apps-kp', 'Apps\Modules\MainController::kenaikanPangkat');    
    $routes->get('/apps-asn-aktif', 'Apps\Modules\MainController::pengaktifanASN');    
    $routes->get('/apps-cpns-pns', 'Apps\Modules\MainController::pengangkatanCPNS');    
    $routes->get('/apps-pertek-pensiun', 'Apps\Modules\MainController::pertekPensiun');    
    $routes->get('/apps-sk-pensiun', 'Apps\Modules\MainController::skPensiun');  
    $routes->get('/apps-regis-pensiun', 'Apps\Modules\MainController::regisPensiun');   
    $routes->post('/apps-tapnip', 'Apps\Modules\MainController::tapnip');
    $routes->post('/apps-cltn', 'Apps\Modules\MainController::cltn');       
    // ----------------------------------------------------------------          
    $routes->post('/kill/data-uploaders', 'Apps\AjxController::killDataUploader'); 
    $routes->post('/store/import-excel', 'Apps\Modules\ImpExlsController::importData');    
    $routes->post('/fetch/data', 'Apps\Modules\DTController::getData');    
    $routes->post('/fetch/summary-uploaders', 'Apps\Modules\DTController::getSummary');
    $routes->post('/fetch/data-detail', 'Apps\Modules\DTController::getDataDetail');   
    // ----------------------------------------------------------------
    // Layanan Disparitas Data
    $routes->get('/apps-dispa', 'Apps\Services\DisparitasData::index');     
    $routes->post('/store/import-disparitas', 'Apps\Services\DisparitasData::storeData');
    $routes->post('/fetch/data-disparitas', 'Apps\Services\DisparitasData::getData');   
    $routes->post('/fetch/summary-disparitas', 'Apps\Services\DisparitasData::getSummary');
    $routes->post('/fetch/detail-disparitas', 'Apps\Services\DisparitasData::getDataDetail');    
    $routes->post('/kill/data-disparitas', 'Apps\Services\DisparitasData::removeData');   
    // ----------------------------------------------------------------
    // Layanan Manajemen Talenta
    $routes->get('/apps-mt', 'Apps\Services\MTController::index');     
    $routes->post('/store/add-mt', 'Apps\Services\MTController::storeMTData');
    $routes->post('/fetch/data-mt', 'Apps\Services\MTController::getData');    
    $routes->post('/kill/data-mt', 'Apps\Services\MTController::removeData');
    // Layanan Pembinaan Manajemen ASN
    $routes->get('/apps-pembinaan-kinerja', 'Apps\Services\PembinaanASNController::kinerja');
    $routes->get('/apps-pembinaan-kompetensi-karier', 'Apps\Services\PembinaanASNController::kompetensiKarier');
    $routes->get('/apps-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::disiplinBudayaCitra');
    $routes->post('/fetch/options-pembinaan-kinerja', 'Apps\Services\PembinaanASNController::getKinerjaOptions');
    $routes->post('/fetch/data-pembinaan-kinerja', 'Apps\Services\PembinaanASNController::getKinerjaData');
    $routes->post('/fetch/summary-pembinaan-kinerja', 'Apps\Services\PembinaanASNController::getKinerjaSummary');
    $routes->post('/store/save-data-pembinaan-kinerja', 'Apps\Services\PembinaanASNController::storeKinerjaData');
    $routes->post('/kill/data-pembinaan-kinerja', 'Apps\Services\PembinaanASNController::removeKinerjaData');
    $routes->post('/fetch/options-pembinaan-kompetensi-karier', 'Apps\Services\PembinaanASNController::getKompetensiOptions');
    $routes->post('/fetch/data-pembinaan-kompetensi-karier', 'Apps\Services\PembinaanASNController::getKompetensiData');
    $routes->post('/fetch/summary-pembinaan-kompetensi-karier', 'Apps\Services\PembinaanASNController::getKompetensiSummary');
    $routes->post('/store/save-data-pembinaan-kompetensi-karier', 'Apps\Services\PembinaanASNController::storeKompetensiData');
    $routes->post('/kill/data-pembinaan-kompetensi-karier', 'Apps\Services\PembinaanASNController::removeKompetensiData');
    $routes->post('/fetch/options-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::getDisiplinOptions');
    $routes->post('/fetch/data-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::getDisiplinData');
    $routes->post('/fetch/detail-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::getDisiplinDetail');
    $routes->post('/fetch/summary-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::getDisiplinSummary');
    $routes->post('/store/save-data-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::storeDisiplinData');
    $routes->post('/kill/data-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::removeDisiplinData');
    $routes->post('/fetch/kategori-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::getDisiplinKategoriData');
    $routes->post('/store/kategori-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::storeDisiplinKategori');
    $routes->post('/kill/kategori-pembinaan-disiplin-budaya-citra', 'Apps\Services\PembinaanASNController::removeDisiplinKategori');
    // ----------------------------------------------------------------
    // Layanan Peremajaan Data
    $routes->get('/apps-pdm', 'Apps\Services\PeremajaanData::index');    
    $routes->post('/store/save-data-peremajaan', 'Apps\Services\PeremajaanData::storeData');    
    $routes->post('/fetch/data-peremajaan', 'Apps\Services\PeremajaanData::getDataV2'); 
    $routes->post('/fetch/summary-peremajaan', 'Apps\Services\PeremajaanData::getSummary');
    $routes->post('/fetch/resume-peremajaan', 'Apps\Services\PeremajaanData::getResume');            
    $routes->post('/kill/data-peremajaan', 'Apps\Services\PeremajaanData::removeData');     
    // ----------------------------------------------------------------
    // Layanan NSPK
    $routes->get('/apps-nspk', 'Apps\Services\NSPKController::index');    
    $routes->post('/store/save-data-nspk', 'Apps\Services\NSPKController::storeData');    
    $routes->post('/fetch/data-nspk', 'Apps\Services\NSPKController::getData');        
    $routes->post('/fetch/summary-nspk', 'Apps\Services\NSPKController::getSummary');
    $routes->post('/kill/data-nspk', 'Apps\Services\NSPKController::removeData');
    // ----------------------------------------------------------------
    // Layanan Sistem Merit
    $routes->get('/apps-merit', 'Apps\Services\MeritController::index');    
    $routes->post('/store/save-data-merit', 'Apps\Services\MeritController::storeData');    
    $routes->post('/fetch/data-merit', 'Apps\Services\MeritController::getData');        
    $routes->post('/fetch/summary-merit', 'Apps\Services\MeritController::getSummary');
    $routes->post('/kill/data-merit', 'Apps\Services\MeritController::removeData'); 
    // ----------------------------------------------------------------
    // Layanan Pengawasan (WASDAL)
    $routes->get('/apps-wasdal', 'Apps\Services\WasdalController::index');    
    $routes->post('/store/save-data-wasdal', 'Apps\Services\WasdalController::storeData');    
    $routes->post('/fetch/data-wasdal', 'Apps\Services\WasdalController::getData');        
    $routes->post('/fetch/summary-wasdal', 'Apps\Services\WasdalController::getSummary');
    $routes->post('/kill/data-wasdal', 'Apps\Services\WasdalController::removeData'); 
    // ----------------------------------------------------------------
    // Layanan Integrasi
    $routes->get('/apps-integrasi', 'Apps\Services\IntegrasiData::index');    
    $routes->post('/store/save-data-integrasi', 'Apps\Services\IntegrasiData::storeData');    
    $routes->post('/fetch/data-integrasi', 'Apps\Services\IntegrasiData::getData');        
    $routes->post('/fetch/summary-integrasi', 'Apps\Services\IntegrasiData::getSummary');
    $routes->post('/kill/data-integrasi', 'Apps\Services\IntegrasiData::removeData'); 
    // ----------------------------------------------------------------
    // Layanan DMS         
    $routes->get('/apps-dms', 'Apps\Services\DMSData::index');
    $routes->post('/store/import-dms-data', 'Apps\Services\DMSData::importData');
    $routes->post('/store/save-data-dms', 'Apps\Services\DMSData::storeData');
    $routes->post('/fetch/data-dms', 'Apps\Services\DMSData::getData');     
    $routes->post('/fetch/summary-dms', 'Apps\Services\DMSData::getSummary');
    $routes->post('/fetch/detail-dms', 'Apps\Services\DMSData::getDataDetail');          
    $routes->post('/kill/data-dms', 'Apps\Services\DMSData::removeData'); 
    // ----------------------------------------------------------------
    // Layanan Takah
    $routes->get('/apps-takah', 'Apps\Services\TakahData::index');
    $routes->post('/store/import-takah-data', 'Apps\Services\TakahData::importData');
    $routes->post('/fetch/data-takah', 'Apps\Services\TakahData::getData');     
    $routes->post('/fetch/summary-takah', 'Apps\Services\TakahData::getSummary');
    $routes->post('/fetch/detail-takah', 'Apps\Services\TakahData::getDataDetail');          
    $routes->post('/kill/data-takah', 'Apps\Services\TakahData::removeData');   
    // ----------------------------------------------------------------
    // Layanan Statistik 
    $routes->get('/apps-statistik', 'Apps\Services\StatistikData::index');  
    $routes->post('/apps-statistik', 'Apps\Services\StatistikData::index');   
    $routes->post('/store/import-excel-statistik', 'Apps\Services\StatistikData::storeData');    
    $routes->post('/fetch/data-statistik', 'Apps\Services\StatistikData::getData');  
    $routes->post('/fetch/summary-statistik', 'Apps\Services\StatistikData::getSummary');
    $routes->post('/fetch/detail-statistik', 'Apps\Services\StatistikData::getDataDetail');           
    $routes->post('/kill/data-statistik', 'Apps\Services\StatistikData::removeData');       
    // ----------------------------------------------------------------
    // Layanan Fasilitas CAT
    $routes->get('/apps-cat', 'Apps\Services\FasilitasiCAT::index');  
    $routes->get('/apps-cat-tilok/(:any)', 'Apps\Services\FasilitasiCAT::tilok/$1');
    $routes->get('/apps-cat-detail/(:any)', 'Apps\Services\FasilitasiCAT::detail/$1');
    $routes->get('/api/apps-cat/events', 'Apps\Services\FasilitasiCAT::getJenisTesList');
    $routes->post('/api/apps-cat/events', 'Apps\Services\FasilitasiCAT::createJenisTes');
    $routes->post('/api/apps-cat/events/update', 'Apps\Services\FasilitasiCAT::updateJenisTes');
    $routes->post('/api/apps-cat/events/delete', 'Apps\Services\FasilitasiCAT::deleteJenisTes');
    $routes->post('/store/save-data-seleksi-cat', 'Apps\Services\FasilitasiCAT::storeDataSeleksi');
    $routes->post('/fetch/data-seleksi-cat', 'Apps\Services\FasilitasiCAT::getDataSeleksi');
    $routes->post('/kill/data-seleksi-cat', 'Apps\Services\FasilitasiCAT::removeDataSeleksi');
    $routes->post('/store/save-data-tilok-cat', 'Apps\Services\FasilitasiCAT::storeDataTilok');    
    $routes->post('/store/save-data-tilok-rekap', 'Apps\Services\FasilitasiCAT::storeDataRekap');    
    $routes->post('/store/update-data-hasil-cat', 'Apps\Services\FasilitasiCAT::updateDataHasil');    
    $routes->post('/fetch/data-tilok-cat', 'Apps\Services\FasilitasiCAT::getDataTilok');  
    $routes->post('/fetch/summary-tilok-cat', 'Apps\Services\FasilitasiCAT::getSummaryTilok');
    $routes->post('/fetch/data-tilok-detail', 'Apps\Services\FasilitasiCAT::getDataDetailTilok');           
    $routes->post('/fetch/meta-tilok-detail', 'Apps\Services\FasilitasiCAT::getMetaDetailTilok');
    $routes->post('/fetch/summary-tilok-detail', 'Apps\Services\FasilitasiCAT::getSummaryDetailTilok');
    $routes->post('/kill/data-tilok-cat', 'Apps\Services\FasilitasiCAT::removeDataTilok');    
    $routes->post('/kill/data-tilok-rekap', 'Apps\Services\FasilitasiCAT::removeDataRekap');    
    // ----------------------------------------------------------------
    // Layanan Manajemen Asset
    $routes->get('/apps-manage-assets', 'Apps\Services\ManageAssetsController::index');
    $routes->get('/apps-manage-assets-detail/(:segment)', 'Apps\Services\ManageAssetsController::detail/$1');
    $routes->post('/fetch/data-manage-assets', 'Apps\Services\ManageAssetsController::getData');
    $routes->post('/fetch/summary-manage-assets', 'Apps\Services\ManageAssetsController::getSummary');
    // ----------------------------------------------------------------
    // Layanan Manajemen Proyek
    $routes->get('/apps-manage-project', 'Apps\Services\ManageProjectController::index');
    $routes->get('/apps-manage-project-detail/(:segment)', 'Apps\Services\ManageProjectController::detail/$1');
    $routes->post('/fetch/data-manage-project', 'Apps\Services\ManageProjectController::getData');
    $routes->post('/fetch/project-overview', 'Apps\Services\ManageProjectController::getProjectOverview');
    $routes->post('/store/save-project', 'Apps\Services\ManageProjectController::storeProject');
    $routes->post('/store/update-project', 'Apps\Services\ManageProjectController::updateProject');
    $routes->post('/store/save-project-progress', 'Apps\Services\ManageProjectController::storeProgress');
    $routes->post('/store/save-project-budget', 'Apps\Services\ManageProjectController::storeBudget');
    $routes->post('/kill/data-project', 'Apps\Services\ManageProjectController::removeProject');
    // ----------------------------------------------------------------
    // Layanan Sistem Kehumasan
    $routes->get('/apps-humas', 'Apps\Services\HumasController::index');    
    $routes->post('/store/save-data-humas', 'Apps\Services\HumasController::storeData');    
    $routes->post('/fetch/data-humas', 'Apps\Services\HumasController::getData');        
    $routes->post('/fetch/summary-humas', 'Apps\Services\HumasController::getSummary');
    $routes->post('/kill/data-humas', 'Apps\Services\HumasController::removeData'); 
    // ----------------------------------------------------------------
    // Layanan Sistem Konsultasi
    $routes->get('/apps-konsultasi', 'Apps\Services\KonsulController::index');    
    $routes->post('/store/save-data-konsultasi', 'Apps\Services\KonsulController::storeData');    
    $routes->post('/fetch/data-konsultasi', 'Apps\Services\KonsulController::getData');        
    $routes->post('/fetch/summary-konsultasi', 'Apps\Services\KonsulController::getSummary');
    $routes->post('/kill/data-konsultasi', 'Apps\Services\KonsulController::removeData');  
    // ----------------------------------------------------------------      
    // Layanan IKM
    $routes->get('/apps-ikm', 'Apps\Services\IKMController::index');    
    $routes->post('/store/save-data-ikm', 'Apps\Services\IKMController::storeData');    
    $routes->post('/fetch/data-ikm', 'Apps\Services\IKMController::getData');        
    $routes->post('/fetch/summary-ikm', 'Apps\Services\IKMController::getSummary');
    $routes->post('/kill/data-ikm', 'Apps\Services\IKMController::removeData');       
    // ----------------------------------------------------------------      
    // Layanan Persuratan
    $routes->get('/apps-surat', 'Apps\Services\SuratController::index');    
    $routes->post('/store/save-data-surat', 'Apps\Services\SuratController::storeData');    
    $routes->post('/fetch/data-surat', 'Apps\Services\SuratController::getData');        
    $routes->post('/fetch/summary-surat', 'Apps\Services\SuratController::getSummary');
    $routes->post('/kill/data-surat', 'Apps\Services\SuratController::removeData');          
    // ----------------------------------------------------------------     
    // Layanan Statistik Internal
    $routes->get('/apps-statistik-internal', 'Apps\Services\StatistikInternal::index');    
    $routes->get('fetch/master-pendidikan', 'Apps\Services\StatistikInternal::getMasterPendidikan');
    $routes->get('fetch/master-unit-kerja', 'Apps\Services\StatistikInternal::getMasterUnitKerja');
    $routes->get('fetch/master-unit-sk', 'Apps\Services\StatistikInternal::getMasterUnitSK');
    $routes->get('fetch/master-jenis-jabatan', 'Apps\Services\StatistikInternal::getMasterJenisJabatan');
    $routes->get('fetch/master-jenis-pegawai', 'Apps\Services\StatistikInternal::getMasterJenisPegawai');
    $routes->get('fetch/master-agama', 'Apps\Services\StatistikInternal::getMasterAgama');    
    $routes->get('fetch/master-golongan', 'Apps\Services\StatistikInternal::getMasterGolongan');    
    $routes->get('fetch/master-pangkat', 'Apps\Services\StatistikInternal::getMasterPangkat');    
    $routes->get('fetch/master-jabatan', 'Apps\Services\StatistikInternal::getMasterJabatan');
    $routes->post('/store/save-data-statistik-internal', 'Apps\Services\StatistikInternal::storeData');    
    $routes->post('/store/save-data-master-statistik', 'Apps\Services\StatistikInternal::storeDataMaster');    
    $routes->post('store/delete-data-master-statistik', 'Apps\Services\StatistikInternal::deleteDataMaster');
    $routes->post('/fetch/data-statistik-accum', 'Apps\Services\StatistikInternal::getDataAccum');        
    $routes->post('/fetch/summary-statistik-internal', 'Apps\Services\StatistikInternal::getSummary');
    $routes->post('/fetch/data-statistik-pegawai', 'Apps\Services\StatistikInternal::getDataPegawai');        
    $routes->post('/fetch/data-statistik-internal', 'Apps\Services\StatistikInternal::getData');        
    $routes->post('/kill/data-statistik-internal', 'Apps\Services\StatistikInternal::removeData');          
    // ----------------------------------------------------------------    
    // Layanan EKIN
    $routes->get('/apps-ekin', 'Apps\Services\EKINData::index');
    $routes->post('/store/import-ekin-data', 'Apps\Services\EKINData::importData');
    $routes->post('/fetch/data-ekin', 'Apps\Services\EKINData::getData');     
    $routes->post('/fetch/child-ekin', 'Apps\Services\EKINData::getDataChild');
    $routes->post('/fetch/summary-ekin', 'Apps\Services\EKINData::getSummary');
    $routes->post('/fetch/detail-ekin', 'Apps\Services\EKINData::getDataDetail');          
    $routes->post('/kill/data-ekin', 'Apps\Services\EKINData::removeData'); 
    // ----------------------------------------------------------------     
    // Layanan IKPA
    $routes->get('/apps-ikpa', 'Apps\Services\IKPAData::index');
    $routes->post('/store/import-ikpa-data', 'Apps\Services\IKPAData::importData');
    $routes->post('/fetch/data-ikpa', 'Apps\Services\IKPAData::getData');     
    $routes->post('/fetch/summary-ikpa', 'Apps\Services\IKPAData::getSummary');
    $routes->post('/fetch/detail-ikpa', 'Apps\Services\IKPAData::getDataDetail');          
    $routes->post('/kill/data-ikpa', 'Apps\Services\IKPAData::removeData'); 
    // ----------------------------------------------------------------
    // Layanan Pagu & Realisasi Anggaran
    $routes->get('/apps-anggaran', 'Apps\Services\AnggaranData::index');
    $routes->post('/fetch/data-anggaran', 'Apps\Services\AnggaranData::getData');
    $routes->post('/fetch/detail-anggaran', 'Apps\Services\AnggaranData::getDataDetail');
    $routes->post('/fetch/summary-anggaran', 'Apps\Services\AnggaranData::getSummary'); 
    $routes->post('/fetch/options-anggaran', 'Apps\Services\AnggaranData::getOptions');
    $routes->post('/fetch/settings-anggaran', 'Apps\Services\AnggaranData::getSettings');
    $routes->post('/fetch/select2-struktur-anggaran', 'Apps\Services\AnggaranData::searchStrukturSelect2');
    $routes->get('/export/excel-anggaran', 'Apps\Services\AnggaranData::exportExcel');
    $routes->post('/store/save-data-anggaran', 'Apps\Services\AnggaranData::storeData');
    $routes->post('/store/save-tahun-anggaran', 'Apps\Services\AnggaranData::storeYear');
    $routes->post('/store/save-struktur-anggaran', 'Apps\Services\AnggaranData::storeStruktur');
    $routes->post('/kill/data-anggaran', 'Apps\Services\AnggaranData::removeData');
    $routes->post('/kill/tahun-anggaran', 'Apps\Services\AnggaranData::removeYear');
    $routes->post('/kill/struktur-anggaran', 'Apps\Services\AnggaranData::removeStruktur');
    // ----------------------------------------------------------------         
    // Layanan Alih Media
    // $routes->get('/upload-alihmedia', 'Apps\Services\AlihMediaData::upload');
    // $routes->get('/entry-alihmedia', 'Apps\Services\AlihMediaData::entry');
    // $routes->get('/info-alihmedia', 'Apps\Services\AlihMediaData::info');   
    // $routes->post('/store/save-data-alihmedia', 'Apps\Services\AlihMediaData::storeData');    
    // $routes->post('/store/import-excel-alihmedia', 'Apps\Services\AlihMediaData::importData');    
    // $routes->post('/store/pull-datalist-alihmedia', 'Apps\Services\AlihMediaData::getData');   
    // $routes->post('/store/pull-datalist-alihmedia2', 'Apps\Services\AlihMediaData::getDataUpload');   
    // $routes->post('/store/remove-data-alihmedia', 'Apps\Services\AlihMediaData::removeData');           
    // $routes->post('/fetch/resume-alihmedia', 'Apps\Services\AlihMediaData::getResume');   
    // ----------------------------------------------------------------
    // Layanan DMS         
    // $routes->get('/apps-dms', 'Apps\Services\DMSData::index');
    // $routes->get('/entry-dms', 'Apps\Services\DMSData::entry');
    // $routes->get('/info-dms', 'Apps\Services\DMSData::info');   
    // $routes->post('/store/save-data-dms', 'Apps\Services\DMSData::storeData');    
    // $routes->post('/store/import-excel-dms', 'Apps\Services\DMSData::importData');    
    // $routes->post('/store/import-excel-dms-v2', 'Apps\Services\DMSData::importDataV2');    
    // $routes->post('/store/pull-datalist-dms', 'Apps\Services\DMSData::getData');   
    // $routes->post('/store/pull-datalist-dms2', 'Apps\Services\DMSData::getDataUpload');       
    // $routes->post('/store/remove-data-dms', 'Apps\Services\DMSData::removeData');      
    // $routes->post('/fetch/datalist-dms', 'Apps\Services\DMSData::getDataV2');            
    // $routes->post('/fetch/resume-dms', 'Apps\Services\DMSData::getResume');
    // ----------------------------------------------------------------          
    // Layanan Takah
    // $routes->get('/upload-katalog', 'Apps\Services\KatalogData::upload');
    // $routes->get('/entry-katalog', 'Apps\Services\KatalogData::entry');
    // $routes->get('/info-katalog', 'Apps\Services\KatalogData::info');   
    // $routes->post('/store/save-data-katalog', 'Apps\Services\KatalogData::storeData');    
    // $routes->post('/store/import-excel-katalog', 'Apps\Services\KatalogData::importData');       
    // $routes->post('/store/pull-datalist-katalog', 'Apps\Services\KatalogData::getData');   
    // $routes->post('/store/pull-datalist-katalog2', 'Apps\Services\KatalogData::getDataUpload');      
    // $routes->post('/store/remove-data-katalog', 'Apps\Services\KatalogData::removeData');                   
    // $routes->post('/fetch/resume-katalog', 'Apps\Services\KatalogData::getResume');            
    // ----------------------------------------------------------------
    // $routes->get('/upload-integrasi', 'Apps\Services\IntegrasiData::upload');
    // $routes->post('/upload-integrasi', 'Apps\Services\IntegrasiData::upload');
    // $routes->get('/entry-integrasi', 'Apps\Services\IntegrasiData::entry');
    // $routes->get('/info-integrasi', 'Apps\Services\IntegrasiData::info');   
    // $routes->get('/detail-upload-integrasi/(:any)','Apps\Services\IntegrasiData::detail/$1');  
    // $routes->post('/store/import-excel-integrasi', 'Apps\Services\IntegrasiData::storeData');    
    // $routes->post('/store/pull-datalist-integrasi', 'Apps\Services\IntegrasiData::getData');    
    // $routes->post('/store/pull-detail-integrasi', 'Apps\Services\IntegrasiData::getDataDetail');    
    // $routes->post('/store/remove-data-integrasi', 'Apps\Services\IntegrasiData::removeData');         
    // ----------------------------------------------------------------
});
