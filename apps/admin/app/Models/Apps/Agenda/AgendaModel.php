<?php

namespace App\Models\Apps\Agenda;

use CodeIgniter\Model;

class AgendaModel extends Model
{
    protected $table = 'events';

    public function storeData(array $data, string $table)
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function updateData(array $data, int $id, string $table): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        return (bool) $this->db->table($table)->where('id', $id)->update($data);
    }

    public function removeData(int $id, string $table): bool
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        if ($table === 'events') {
            $this->db->table('events')->where('id', $id)->delete();
            $childTables = [
                'events_certificate',
                'events_note',
                'event_attendance',
                'event_documents',
                'event_participants',
                'event_reminders',
            ];

            foreach ($childTables as $childTable) {
                if ($this->tableExists($childTable)) {
                    $this->db->table($childTable)->where('event_id', $id)->delete();
                }
            }

            return true;
        }

        return (bool) $this->db->table($table)->where('id', $id)->delete();
    }

    public function insertBatchData(array $data, string $table)
    {
        if (!$this->tableExists($table)) {
            return false;
        }

        return $this->db->table($table)->insertBatch($data);
    }

    public function dataBidangActive(string $uid): array
    {
        if (!$this->tableExists('data_bidang')) {
            return [];
        }

        return $this->db->query(
            'SELECT uid FROM data_bidang WHERE uid = ? AND is_sort = 2 AND is_status = 1',
            [$uid]
        )->getResultArray();
    }

    public function dataCurrentBidang(): array
    {
        if (!$this->tableExists('data_bidang')) {
            return [];
        }

        return $this->db->query('SELECT * FROM data_bidang WHERE is_status = 1 ORDER BY nama ASC')->getResultArray();
    }

    public function getBidangInfo(string $uid)
    {
        if (!$this->tableExists('data_bidang')) {
            return null;
        }

        return $this->db->query('SELECT * FROM data_bidang WHERE uid = ?', [$uid])->getRow();
    }

    public function getMemberData(): array
    {
        if (!$this->tableExists('data_pegawai')) {
            return [];
        }

        return $this->db->query(
            "SELECT * FROM data_pegawai WHERE COALESCE(nip, '') <> '' ORDER BY nama ASC"
        )->getResultArray();
    }

    public function getUserInfo(string $nip)
    {
        if (!$this->tableExists('data_pegawai')) {
            return null;
        }

        if ($this->tableExists('data_bidang')) {
            return $this->db->query(
                "SELECT p.*, b.nama AS bidang, b.initial
                 FROM data_pegawai p
                 LEFT JOIN data_bidang b ON b.id = p.unit_id
                 WHERE TRIM(p.nip) = TRIM(?)
                 LIMIT 1",
                [$nip]
            )->getRow();
        }

        return $this->db->query(
            'SELECT p.* FROM data_pegawai p WHERE TRIM(p.nip) = TRIM(?) LIMIT 1',
            [$nip]
        )->getRow();
    }

    public function getCategories(): array
    {
        if (!$this->tableExists('event_categories')) {
            return [];
        }

        return $this->db->query(
            'SELECT * FROM event_categories ORDER BY category_name ASC'
        )->getResultArray();
    }

    public function getEventCategories(string $type): array
    {
        if (!$this->tableExists('event_categories')) {
            return [];
        }

        $group = strtolower($type) === 'int' ? 'Internal' : 'External';

        return $this->db->query(
            'SELECT * FROM event_categories WHERE LOWER(group_event) = LOWER(?) ORDER BY category_name ASC',
            [$group]
        )->getResultArray();
    }

    public function getLOVBidang(): array
    {
        if (!$this->tableExists('data_bidang')) {
            return [];
        }

        return $this->db->query('SELECT * FROM data_bidang WHERE is_status <> 0 ORDER BY nama ASC')->getResultArray();
    }

    public function getLOVInstansi(): array
    {
        if (!$this->tableExists('data_instansi')) {
            return [];
        }

        return $this->db->query('SELECT * FROM data_instansi WHERE kanreg = 3 ORDER BY nama ASC')->getResultArray();
    }

    public function getLOVPegawai(): array
    {
        return $this->getMemberData();
    }

    public function checkAgendaData(string $uid): array
    {
        if (!$this->tableExists('events')) {
            return [];
        }

        return $this->db->query('SELECT * FROM events WHERE uid = ?', [$uid])->getResultArray();
    }

    public function getDataAgenda($param, string $mode): array
    {
        if (
            !$this->tableExists('events') ||
            !$this->tableExists('event_categories') ||
            !$this->tableExists('event_participants')
        ) {
            return [];
        }

        $where = '';
        $params = [];

        if ($mode === 'byunit') {
            $where = $param ? ' WHERE xx.unit_id = ? AND xx.event_start_date >= CURDATE()' : '';
            if ($where !== '') {
                $params[] = (int) $param;
            }
        } elseif ($mode === 'byuid') {
            $where = $param ? ' WHERE xx.uid = ?' : '';
            if ($where !== '') {
                $params[] = (string) $param;
            }
        } elseif ($mode === 'byperson') {
            $where = $param
                ? " WHERE xx.event_start_date >= CURDATE() AND (xx.event_participants = 'Semua Pegawai' OR COALESCE(xx.participants_nip, '') LIKE ? )"
                : '';
            if ($where !== '') {
                $params[] = '%' . trim((string) $param) . '%';
            }
        } elseif ($mode === 'bycurdate') {
            $where = ' WHERE xx.event_start_date = CURDATE()';
        }

        $sql = "
            SELECT xx.*
            FROM (
                SELECT
                    a.id,
                    a.uid,
                    a.description,
                    a.title,
                    a.event_type,
                    a.unit_id,
                    c.group_event,
                    c.category_name,
                    COALESCE(b.initial, '-') AS initial,
                    a.event_start_date,
                    a.event_end_date,
                    a.event_start_time,
                    a.event_end_time,
                    CONCAT(
                        DAY(a.event_start_date), ' ',
                        CASE MONTH(a.event_start_date)
                            WHEN 1 THEN 'Januari'
                            WHEN 2 THEN 'Februari'
                            WHEN 3 THEN 'Maret'
                            WHEN 4 THEN 'April'
                            WHEN 5 THEN 'Mei'
                            WHEN 6 THEN 'Juni'
                            WHEN 7 THEN 'Juli'
                            WHEN 8 THEN 'Agustus'
                            WHEN 9 THEN 'September'
                            WHEN 10 THEN 'Oktober'
                            WHEN 11 THEN 'November'
                            WHEN 12 THEN 'Desember'
                        END,
                        ' ', YEAR(a.event_start_date), ', ',
                        DATE_FORMAT(a.event_start_time, '%H.%i'),
                        ' s.d ',
                        DATE_FORMAT(a.event_end_time, '%H.%i')
                    ) AS jadwal_format,
                    CONCAT(
                        DATE_FORMAT(a.event_start_time, '%H.%i'),
                        ' s.d ',
                        DATE_FORMAT(a.event_end_time, '%H.%i')
                    ) AS waktu_format,
                    a.event_participants,
                    IFNULL(COUNT(d.nip), 0) AS total_participants,
                    GROUP_CONCAT(TRIM(d.nip)) AS participants_nip,
                    CASE
                        WHEN a.event_participants = 'Semua Pegawai' THEN 'Semua Pegawai'
                        ELSE GROUP_CONCAT(COALESCE(f.nama, TRIM(d.nip)) SEPARATOR ', ')
                    END AS participants,
                    CASE
                        WHEN a.organizer != 'Instansi' THEN a.organizer
                        ELSE COALESCE(e.nama, a.organizer)
                    END AS organizer,
                    a.instance_id,
                    CASE
                        WHEN a.location IS NULL OR a.location = '' THEN 'Zoom Meeting'
                        ELSE a.location
                    END AS location,
                    a.online_link,
                    a.online_code,
                    a.online_pass,
                    a.is_status,
                    a.is_notulensi,
                    a.is_reminder,
                    a.is_documentation,
                    a.is_questions,
                    a.created_at,
                    a.created_by
                FROM events a
                LEFT JOIN data_bidang b ON b.id = a.unit_id
                LEFT JOIN event_categories c ON c.id = a.event_categories
                LEFT JOIN event_participants d ON d.event_id = a.id
                LEFT JOIN data_instansi e ON e.kodeins = a.instance_id
                LEFT JOIN data_pegawai f ON TRIM(f.nip) = TRIM(d.nip)
                GROUP BY a.id
                ORDER BY a.created_at DESC
            ) xx
            {$where}
        ";

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function getSettingEmail(): array
    {
        if (!$this->tableExists('email_settings')) {
            return [];
        }

        return $this->db->query('SELECT * FROM email_settings ORDER BY id ASC')->getResultArray();
    }

    public function getSMTPEmail()
    {
        if (!$this->tableExists('email_smtp_settings')) {
            return null;
        }

        return $this->db->query('SELECT * FROM email_smtp_settings ORDER BY id ASC LIMIT 1')->getRow();
    }

    public function getEmailTemplate()
    {
        if (!$this->tableExists('email_templates')) {
            return null;
        }

        return $this->db->query('SELECT * FROM email_templates ORDER BY id ASC LIMIT 1')->getRow();
    }

    public function getLogEmail(): array
    {
        if (!$this->tableExists('email_logs')) {
            return [];
        }

        return $this->db->query(
            'SELECT * FROM email_logs ORDER BY id DESC LIMIT 100'
        )->getResultArray();
    }

    public function getDataList(string $type): array
    {
        $tableMap = [
            'data_bidang'  => 'data_bidang',
            'data_instansi'=> 'data_instansi',
            'data_pegawai' => 'data_pegawai',
            'data_room'    => 'data_room',
        ];

        if (!isset($tableMap[$type])) {
            return [];
        }

        $table = $tableMap[$type];
        if (!$this->tableExists($table)) {
            return [];
        }

        $sorting = $table === 'data_bidang' ? 'is_sort' : 'nama';

        return $this->db->query("SELECT * FROM {$table} ORDER BY {$sorting} ASC")->getResultArray();
    }

    public function findSchedule(string $filter1, string $filter2, string $mode): array
    {
        if (
            !$this->tableExists('events') ||
            !$this->tableExists('event_categories') ||
            !$this->tableExists('event_participants')
        ) {
            return [];
        }

        $where = '';
        $params = [];

        if ($mode === 'bynip') {
            $where = ' WHERE xx.event_start_date = ? AND COALESCE(xx.participants_nip, \"\") LIKE ?';
            $params[] = $filter2;
            $params[] = '%' . trim($filter1) . '%';
        } elseif ($mode === 'bydate') {
            $where = ' WHERE xx.event_start_date >= ? AND xx.event_start_date <= ?';
            $params[] = $filter1;
            $params[] = $filter2;
        }

        $sql = "
            SELECT xx.*
            FROM (
                SELECT
                    a.id,
                    a.uid,
                    a.description,
                    a.title,
                    a.event_type,
                    a.unit_id,
                    c.group_event,
                    c.category_name,
                    COALESCE(b.initial, '-') AS initial,
                    a.event_start_date,
                    a.event_end_date,
                    a.event_start_time,
                    a.event_end_time,
                    CONCAT(
                        DAY(a.event_start_date), ' ',
                        CASE MONTH(a.event_start_date)
                            WHEN 1 THEN 'Januari'
                            WHEN 2 THEN 'Februari'
                            WHEN 3 THEN 'Maret'
                            WHEN 4 THEN 'April'
                            WHEN 5 THEN 'Mei'
                            WHEN 6 THEN 'Juni'
                            WHEN 7 THEN 'Juli'
                            WHEN 8 THEN 'Agustus'
                            WHEN 9 THEN 'September'
                            WHEN 10 THEN 'Oktober'
                            WHEN 11 THEN 'November'
                            WHEN 12 THEN 'Desember'
                        END,
                        ' ', YEAR(a.event_start_date), ', ',
                        DATE_FORMAT(a.event_start_time, '%H.%i'),
                        ' s.d ',
                        DATE_FORMAT(a.event_end_time, '%H.%i')
                    ) AS jadwal_format,
                    CONCAT(
                        DATE_FORMAT(a.event_start_time, '%H.%i'),
                        ' s.d ',
                        DATE_FORMAT(a.event_end_time, '%H.%i')
                    ) AS waktu_format,
                    a.event_participants,
                    IFNULL(COUNT(d.nip), 0) AS total_participants,
                    GROUP_CONCAT(TRIM(d.nip)) AS participants_nip,
                    CASE
                        WHEN a.event_participants = 'Semua Pegawai' THEN 'Semua Pegawai'
                        ELSE GROUP_CONCAT(COALESCE(f.nama, TRIM(d.nip)) SEPARATOR ', ')
                    END AS participants,
                    CASE
                        WHEN a.organizer != 'Instansi' THEN a.organizer
                        ELSE COALESCE(e.nama, a.organizer)
                    END AS organizer,
                    a.instance_id,
                    CASE
                        WHEN a.location IS NULL OR a.location = '' THEN 'Zoom Meeting'
                        ELSE a.location
                    END AS location,
                    a.online_link,
                    a.online_code,
                    a.online_pass,
                    a.is_status,
                    a.is_notulensi,
                    a.is_reminder,
                    a.is_documentation,
                    a.is_questions,
                    a.created_at,
                    a.created_by
                FROM events a
                LEFT JOIN data_bidang b ON b.id = a.unit_id
                LEFT JOIN event_categories c ON c.id = a.event_categories
                LEFT JOIN event_participants d ON d.event_id = a.id
                LEFT JOIN data_instansi e ON e.kodeins = a.instance_id
                LEFT JOIN data_pegawai f ON TRIM(f.nip) = TRIM(d.nip)
                GROUP BY a.id
                ORDER BY a.created_at DESC
            ) xx
            {$where}
        ";

        return $this->db->query($sql, $params)->getResultArray();
    }

    public function getMemberReady(string $date1, string $date2, string $time1, string $time2): array
    {
        if (!$this->tableExists('data_pegawai')) {
            return [];
        }

        if (!$this->tableExists('events') || !$this->tableExists('event_participants')) {
            return $this->db->query(
                "SELECT nip, nama FROM data_pegawai WHERE COALESCE(nip, '') <> '' ORDER BY nama ASC"
            )->getResultArray();
        }

        $candidateStart = trim($date1 . ' ' . $time1);
        $candidateEnd = trim($date2 . ' ' . $time2);

        return $this->db->query(
            "SELECT dm.nip, dm.nama
             FROM data_pegawai dm
             WHERE COALESCE(dm.nip, '') <> ''
               AND NOT EXISTS (
                    SELECT 1
                    FROM events a
                    INNER JOIN event_participants ep ON ep.event_id = a.id
                    WHERE TRIM(ep.nip) = TRIM(dm.nip)
                      AND TIMESTAMP(a.event_start_date, a.event_start_time) <= ?
                      AND TIMESTAMP(a.event_end_date, a.event_end_time) >= ?
               )
             ORDER BY dm.nama ASC",
            [$candidateEnd, $candidateStart]
        )->getResultArray();
    }

    public function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }
}
