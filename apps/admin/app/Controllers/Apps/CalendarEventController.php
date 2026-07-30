<?php

namespace App\Controllers\Apps;

use App\Controllers\BaseController;

class CalendarEventController extends BaseController
{
    public function index()
    {
        $data = [
            'title'    => 'Kalender Kegiatan',
            'seslog'   => session()->get(),
        ];
        return $this->renderView('Apps/pages/calendar-event', $data);
    }
}
