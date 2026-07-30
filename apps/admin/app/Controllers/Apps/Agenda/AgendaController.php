<?php

namespace App\Controllers\Apps\Agenda;

use App\Controllers\BaseController;
use App\Models\Apps\Agenda\AgendaModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class AgendaController extends BaseController
{
    protected AgendaModel $agenda;

    public function __construct()
    {
        $this->agenda = new AgendaModel();
    }

    public function dashboard()
    {
        return $this->renderView('agenda/dashboard', [
            'title' => 'Dashboard Agenda',
        ]);
    }

    public function agendaPersonal()
    {
        $nip = (string) session()->get('username');

        return $this->renderView('agenda/personal', [
            'title'    => 'Agenda Saya',
            'datalist' => $this->agenda->getDataAgenda($nip, 'byperson'),
        ]);
    }

    public function newAgenda()
    {
        return $this->renderView('agenda/entry_choice', [
            'title' => 'Agenda Baru',
        ]);
    }

    public function entryAgenda(string $type)
    {
        $allowed = ['int', 'ext'];
        if (!in_array($type, $allowed, true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'        => 'Agenda Baru',
            'lovgrpenvent' => $this->agenda->getEventCategories($type),
            'lovbidang'    => $this->agenda->getLOVBidang(),
            'lovinstansi'  => $this->agenda->getLOVInstansi(),
            'lovpegawai'   => $this->agenda->getLOVPegawai(),
            'param'        => $type,
        ];

        $view = $type === 'int' ? 'agenda/entry_int' : 'agenda/entry_ext';
        return $this->renderView($view, $data);
    }

    public function riwayatAgenda()
    {
        $tempStart = (string) ($this->request->getPost('start') ?? date('Y-m-d'));
        $tempEnd = (string) ($this->request->getPost('end') ?? date('Y-m-d'));
        $start = date('Y-m-d', strtotime($tempStart));
        $end = date('Y-m-d', strtotime($tempEnd));

        $data = [
            'title'    => 'Riwayat Agenda',
            'start'    => $tempStart,
            'end'      => $tempEnd,
            'datalist' => [],
        ];

        if ($this->request->getMethod() === 'post') {
            $data['datalist'] = $this->agenda->findSchedule($start, $end, 'bydate');
        }

        return $this->renderView('agenda/riwayat', $data);
    }

    public function findAgenda()
    {
        $data = [
            'title'    => 'Cari Data Agenda',
            'datalist' => [],
            'datanip'  => null,
        ];

        if ($this->request->getMethod() === 'post') {
            $filNip = (string) $this->request->getPost('filnip');
            $filDate = (string) $this->request->getPost('fildate');
            $data['datalist'] = $this->agenda->findSchedule($filNip, $filDate, 'bynip');
            $data['datanip'] = $this->agenda->getUserInfo($filNip);
        }

        return $this->renderView('agenda/cari', $data);
    }

    public function dataPegawai()
    {
        return $this->renderView('agenda/data_pegawai', [
            'title'    => 'Master Data Pegawai',
            'datalist' => $this->agenda->getDataList('data_pegawai'),
        ]);
    }

    public function dataBidang()
    {
        return $this->renderView('agenda/data_bidang', [
            'title'    => 'Master Data Bidang',
            'datalist' => $this->agenda->getDataList('data_bidang'),
        ]);
    }

    public function dataInstansi()
    {
        return $this->renderView('agenda/data_instansi', [
            'title'    => 'Master Data Instansi',
            'datalist' => $this->agenda->getDataList('data_instansi'),
        ]);
    }

    public function dataRuangan()
    {
        return $this->renderView('agenda/data_ruangan', [
            'title'    => 'Master Data Ruangan',
            'datalist' => $this->agenda->getDataList('data_room'),
        ]);
    }

    public function agendaBidang(string $uid)
    {
        $bidang = $this->agenda->getBidangInfo($uid);
        $active = $this->agenda->dataBidangActive($uid);

        if (!$bidang || empty($active)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $tempStart = (string) ($this->request->getPost('start') ?? date('Y-m-d'));
        $tempEnd = (string) ($this->request->getPost('end') ?? date('Y-m-d'));

        return $this->renderView('agenda/bidang', [
            'title'      => 'Agenda ' . ($bidang->initial ?? ''),
            'categories' => $bidang,
            'start'      => date('Y-m-d', strtotime($tempStart)),
            'end'        => date('Y-m-d', strtotime($tempEnd)),
        ]);
    }

    public function agendaDetail(string $uid)
    {
        if (empty($this->agenda->checkAgendaData($uid))) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderView('agenda/detail', [
            'title' => 'Detail Agenda',
        ]);
    }

    public function notulensiAgenda(string $uid)
    {
        if (empty($this->agenda->checkAgendaData($uid))) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderView('agenda/notulensi', [
            'title' => 'Notulensi Agenda',
        ]);
    }

    public function ruangDiskusiAgenda(string $uid)
    {
        if (empty($this->agenda->checkAgendaData($uid))) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->renderView('agenda/diskusi', [
            'title' => 'Ruang Diskusi',
        ]);
    }
}
