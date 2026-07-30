<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Apps\AppsModel;

class AppsController extends BaseController
{

    public function __construct()
    {
        $this->apps = new AppsModel();
        $sess = session()->get();
    }
    
    public function backupDatabase()
    {
        $db = \Config\Database::connect();

        $databaseName = $db->database;
        $fileName = 'backup_' . $databaseName . '_' . date('Ymd_His') . '.sql';

        $backupPath = WRITEPATH . 'backups/';

        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filePath = $backupPath . $fileName;

        $sql  = "-- Backup Database: {$databaseName}\n";
        $sql .= "-- Generated at: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $db->listTables();

        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";

            $createTable = $db->query("SHOW CREATE TABLE `{$table}`")->getRowArray();

            if (isset($createTable['Create Table'])) {
                $sql .= $createTable['Create Table'] . ";\n\n";
            }

            $rows = $db->table($table)->get()->getResultArray();

            foreach ($rows as $row) {
                $columns = array_map(function ($column) {
                    return '`' . $column . '`';
                }, array_keys($row));

                $values = array_map(function ($value) use ($db) {
                    if ($value === null) {
                        return 'NULL';
                    }

                    return $db->escape($value);
                }, array_values($row));

                $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }

            $sql .= "\n\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filePath, $sql);

        return $this->response
            ->download($filePath, null)
            ->setFileName($fileName);
    }

    public function dashboard()
    {
        $data = array(
            'title'     => 'My Task',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/taskme', $data);
    }

    public function timkerja()
    {
        $db = \Config\Database::connect();
        $instansiName = 'Taspen Jakarta Selatan';

        $existingInstansi = $db->table('data_instansi')
            ->select('id')
            ->where('nama', $instansiName)
            ->get()
            ->getRowArray();

        if (!$existingInstansi) {
            $maxKodeins = $db->table('data_instansi')
                ->selectMax('kodeins', 'max_kodeins')
                ->get()
                ->getRowArray();

            $db->table('data_instansi')->insert([
                'kodeins' => ((int) ($maxKodeins['max_kodeins'] ?? 0)) + 1,
                'nama' => $instansiName,
                'kanreg' => 0,
                'wilayah' => null,
                'is_status' => 1,
                'logo' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $data = array(
            'title'     => 'Tim Kerja',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/teamwork', $data);
    }

    public function timkerjalayanan($param){
        $data = array(
            'title'     => 'Detail Layanan',
            'seslog'    => session()->get(),
            'layanan_key' => $param,
        );
        return $this->renderView('Apps/pages/teamworkService', $data);
    }

    public function explore()
    {
        $data = array(
            'title'     => 'Explore Task',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/taskexplore', $data);
    }

    public function resume()
    {
        $data = array(
            'title'     => 'Resume Task',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/taskresume', $data);
    }

    public function history()
    {
        $data = array(
            'title'     => 'History Task',
            'seslog'    => session()->get()
        );
        return $this->renderView('Apps/pages/taskhistory', $data);
    }

    public function profil()
    {
        $data = array(
            'title'     => 'Profil Pegawai',
            'seslog'    => session()->get(),
            'profil'    => $this->apps->getInfoProfil(session()->get('username'))
        );
        return $this->renderView('Apps/PresensiProfil', $data);
    }

}
