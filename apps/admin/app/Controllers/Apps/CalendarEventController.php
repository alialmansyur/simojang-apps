<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use App\Models\Apps\Agenda\AgendaModel;

class CalendarEventController extends BaseController
{
    protected AgendaModel $agenda;

    public function __construct()
    {
        $this->agenda = new AgendaModel();
    }

    public function index()
    {
        $data = [
            'title'    => 'Kalender Kegiatan',
            'seslog'   => session()->get(),
        ];
        return $this->renderView('Apps/pages/calendar-event', $data);
    }

    public function getEvents()
    {
        $rawEvents = $this->agenda->getDataAgenda(null, '');
        $events = [];
        
        foreach ($rawEvents as $ev) {
            $status = 'Belum Mulai';
            $now = date('Y-m-d H:i:s');
            $startDate = $ev['event_start_date'] ?: date('Y-m-d');
            $startTime = $ev['event_start_time'] ?: '00:00:00';
            $endDate = $ev['event_end_date'] ?: $startDate;
            $endTime = $ev['event_end_time'] ?: '23:59:59';
            
            $start = $startDate . ' ' . $startTime;
            $end = $endDate . ' ' . $endTime;
            
            if ($now >= $start && $now <= $end) {
                $status = 'Berlangsung';
            } elseif ($now > $end) {
                $status = 'Selesai';
            }

            $participants = [];
            if (!empty($ev['participants'])) {
                $parts = explode(',', $ev['participants']);
                foreach ($parts as $p) {
                    $participants[] = ['nama' => trim($p)];
                }
            }
            
            $events[] = [
                'id' => $ev['id'],
                'title' => $ev['title'],
                'start' => $start,
                'end' => $end,
                'description' => $ev['description'],
                'category' => $ev['category_name'] ?? 'Lainnya',
                'status' => $status,
                'location' => $ev['location'],
                'stNumber' => '-', 
                'team' => $ev['initial'] ?? $ev['organizer'],
                'participants' => $participants
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $events]);
    }

    public function getKpi()
    {
        $rawEvents = $this->agenda->getDataAgenda(null, '');
        
        $total = count($rawEvents);
        $selesai = 0;
        $akanDatang = 0;
        $hariIni = 0;
        $today = date('Y-m-d');
        
        foreach ($rawEvents as $ev) {
            $now = date('Y-m-d H:i:s');
            $startDate = $ev['event_start_date'] ?: date('Y-m-d');
            $startTime = $ev['event_start_time'] ?: '00:00:00';
            $endDate = $ev['event_end_date'] ?: $startDate;
            $endTime = $ev['event_end_time'] ?: '23:59:59';
            
            $start = $startDate . ' ' . $startTime;
            $end = $endDate . ' ' . $endTime;
            
            if ($now > $end) {
                $selesai++;
            } elseif ($now < $start) {
                $akanDatang++;
            }
            
            if ($ev['event_start_date'] == $today) {
                $hariIni++;
            }
        }
        
        return $this->response->setJSON([
            'status' => 'success', 
            'data' => [
                'total_kegiatan' => $total,
                'kegiatan_selesai' => $selesai,
                'akan_datang' => $akanDatang,
                'hari_ini' => $hariIni
            ]
        ]);
    }
}
