<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;
use Config\Services;

class CalendarEventController extends BaseController
{
    private const SIMANJA_EVENTS_URL = 'https://kanreg3.id/simanja-api/public/dashboard/events';
    private const SIMANJA_KPI_URL    = 'https://kanreg3.id/simanja-api/public/dashboard/kpi';

    public function index()
    {
        $data = [
            'title'  => 'Kalender Kegiatan',
            'seslog' => session()->get(),
        ];
        return $this->renderView('Apps/pages/calendar-event', $data);
    }

    public function getEvents()
    {
        $rawEvents = $this->fetchFromSimanja(self::SIMANJA_EVENTS_URL);
        $events = [];
        $now = date('Y-m-d H:i:s');

        foreach ($rawEvents as $ev) {
            $startDate = !empty($ev['start_date_raw']) ? $ev['start_date_raw'] : date('Y-m-d');
            $startTime = !empty($ev['start_time_raw']) ? $ev['start_time_raw'] : '00:00:00';
            $endDate   = !empty($ev['end_date_raw']) ? $ev['end_date_raw'] : $startDate;
            $endTime   = !empty($ev['end_time_raw']) ? $ev['end_time_raw'] : '23:59:59';

            $startIso = !empty($ev['start']) ? $ev['start'] : ($startDate . 'T' . $startTime);
            $endIso   = !empty($ev['end']) ? $ev['end'] : ($endDate . 'T' . $endTime);

            $startCmp = str_replace('T', ' ', substr($startIso, 0, 19));
            $endCmp   = str_replace('T', ' ', substr($endIso, 0, 19));

            $status = 'Belum Mulai';
            if ($now >= $startCmp && $now <= $endCmp) {
                $status = 'Berlangsung';
            } elseif ($now > $endCmp) {
                $status = 'Selesai';
            }

            $participants = [];
            if (!empty($ev['participants'])) {
                if (is_array($ev['participants'])) {
                    foreach ($ev['participants'] as $p) {
                        if (is_array($p)) {
                            $nama  = $p['name'] ?? ($p['nama'] ?? '');
                            $pos   = $p['position'] ?? ($p['jabatan'] ?? '');
                            $label = $pos ? ($pos . ': ' . $nama) : $nama;
                            $participants[] = ['nama' => $label ?: '-'];
                        } elseif (is_string($p)) {
                            $participants[] = ['nama' => trim($p)];
                        }
                    }
                } elseif (is_string($ev['participants'])) {
                    $parts = explode(',', $ev['participants']);
                    foreach ($parts as $p) {
                        $participants[] = ['nama' => trim($p)];
                    }
                }
            }

            $events[] = [
                'id'           => $ev['id'] ?? ($ev['uuid'] ?? rand(1000, 9999)),
                'title'        => $ev['title'] ?? 'Tanpa Judul',
                'start'        => $startIso,
                'end'          => $endIso,
                'description'  => $ev['description'] ?? '-',
                'category'     => $ev['category'] ?? ($ev['type'] ?? 'Lainnya'),
                'status'       => $status,
                'location'     => $ev['location'] ?? '-',
                'stNumber'     => $ev['stNumber'] ?? '-',
                'team'         => $ev['team'] ?? ($ev['organizer'] ?? '-'),
                'participants' => $participants,
            ];
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $events]);
    }

    public function getKpi()
    {
        $rawEvents = $this->fetchFromSimanja(self::SIMANJA_EVENTS_URL);
        
        $total = count($rawEvents);
        $selesai = 0;
        $akanDatang = 0;
        $hariIni = 0;
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');
        
        foreach ($rawEvents as $ev) {
            $startDate = !empty($ev['start_date_raw']) ? $ev['start_date_raw'] : date('Y-m-d');
            $startTime = !empty($ev['start_time_raw']) ? $ev['start_time_raw'] : '00:00:00';
            $endDate   = !empty($ev['end_date_raw']) ? $ev['end_date_raw'] : $startDate;
            $endTime   = !empty($ev['end_time_raw']) ? $ev['end_time_raw'] : '23:59:59';
            
            $startCmp = $startDate . ' ' . $startTime;
            $endCmp   = $endDate . ' ' . $endTime;
            
            if ($now > $endCmp) {
                $selesai++;
            } elseif ($now < $startCmp) {
                $akanDatang++;
            }
            
            if ($today >= $startDate && $today <= $endDate) {
                $hariIni++;
            }
        }
        
        return $this->response->setJSON([
            'status' => 'success', 
            'data'   => [
                'total_kegiatan'   => $total,
                'kegiatan_selesai' => $selesai,
                'akan_datang'      => $akanDatang,
                'hari_ini'         => $hariIni,
            ],
        ]);
    }

    private function fetchFromSimanja(string $url): array
    {
        try {
            $client = Services::curlrequest([
                'timeout'     => 10,
                'http_errors' => false,
                'verify'      => false,
            ]);
            $response = $client->get($url);
            if ($response->getStatusCode() === 200) {
                $json = json_decode($response->getBody(), true);
                if (isset($json['data']) && is_array($json['data'])) {
                    return $json['data'];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Fetch Simanja API error [' . $url . ']: ' . $e->getMessage());
        }

        // Fallback using file_get_contents
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout' => 10,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $raw = @file_get_contents($url, false, $ctx);
            if ($raw !== false) {
                $json = json_decode($raw, true);
                if (isset($json['data']) && is_array($json['data'])) {
                    return $json['data'];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Fallback file_get_contents Simanja error: ' . $e->getMessage());
        }

        return [];
    }
}
