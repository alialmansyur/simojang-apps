<?php

namespace App\Models\Apps;

use CodeIgniter\Model;

class AppsModel extends Model
{
    protected $table = 'data_pegawai';

    public function __construct(){
        parent::__construct();
    }

    public function storeData($data, $table){
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    public function updateData($data, $id, $table){
        return $this->db->table($table)->where('id', $id)->update($data);
    }

    public function updateDataByField($field, $value, $data, $table){
        return $this->db->table($table)->where($field, $value)->update($data);
    }

    public function removeData($id, $table){
        return $this->db->table($table)->where('id', $id)->delete();
    }

    public function removeDataByField($field, $value, $table){
        return $this->db->table($table)->where($field, $value)->delete();
    }

    public function removeDataLogStatistik($id, $table){
        return $this->db->table($table)->where('asn_log_id', $id)->delete();
    }    

    public function removeDataLogIntegrasi($id, $table){
        return $this->db->table($table)->where('integrasi_log_id', $id)->delete();
    }    

    public function removeDataLogDMS($id, $table){
        return $this->db->table($table)->where('dms_log_id', $id)->delete();
    }  
    
    public function removeDataLogTakah($id, $table){
        return $this->db->table($table)->where('takah_log_id', $id)->delete();
    }      

    public function removeDataLogDisparitas($id, $table){
        return $this->db->table($table)->where('disparitas_log_id', $id)->delete();
    }  

    public function removeDataLogMT($id, $table){
        return $this->db->table($table)->where('mt_id', $id)->delete();
    }      

    public function removeDataLogIKPA($id, $table){
        return $this->db->table($table)->where('ikpa_log_id', $id)->delete();
    }      

    public function removeDataLogEKIN($id, $table){
        return $this->db->table($table)->where('ekin_log_id', $id)->delete();
    }          

    public function insertBatchData($data,$table){
        $this->table = $table;
        return $this->db->table($this->table)->insertBatch($data);
    }

    public function updateBatchData($data, $table, $primaryKey){
        $this->table = $table;
        return $this->db->table($this->table)->updateBatch($data, $primaryKey);
    }

    public function getAvatar($user){
        $data = $this->db->query("
            SELECT * FROM auth_users WHERE id = ?
        ", [$user])->getRow();
        return $data->userimage ?? null;
    }

    public function getLayananData($nip, $keyword = '', $unit = '0') {
        $builder = $this->db->table('data_layanan a');
        $builder->select("a.*, (CASE WHEN b.id IS NULL THEN 0 ELSE 1 END) AS enrolled");
        $builder->join('txn_enroll b', 'b.layanan_id = a.id AND b.nip = ' . $this->db->escape($nip), 'left');
        $builder->orderBy('a.status', 'DESC');

        if ($unit !== '0') {
            $builder->where('a.bidang_id', $unit);
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('a.nama_layanan', $keyword)
                ->orLike('a.alias', $keyword)
                ->orLike('a.kategori', $keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getTimkerja(){ 
        return $this->db->query("SELECT * FROM data_timkerja WHERE is_show <> 0 ORDER BY id desc")->getResultArray();  
    }

public function getLayananTimkerja($param, $keyword = '')
{
    $tw = $this->db->table('data_timkerja')
                   ->where('uid', $param)
                   ->get()
                   ->getRow();

    if (!$tw) { return []; }

    $builder = $this->db->table('data_timkerja_layanan a')
                ->select([
                    'a.*',
                    'd.nama timkerja',
                    "SUM(CASE WHEN c.created_at >= CURDATE() AND c.created_at < CURDATE() + INTERVAL 1 DAY THEN 1 ELSE 0 END) AS uploads_today",
                    "IF(SUM(CASE WHEN c.created_at >= CURDATE() AND c.created_at < CURDATE() + INTERVAL 1 DAY THEN 1 ELSE 0 END) > 0, 1, 0) AS has_today",
                    "MAX(c.created_at) AS last_upload_at"
                ])
                ->join('activity_daily_logs c', 'c.layanan_id = a.id', 'left')
                ->join('data_timkerja d', 'd.id = a.timkerja_id', 'left')
                ->where('a.timkerja_id', $tw->id)
                ->where('a.is_show', 1)
                ->groupBy('a.id')
                ->orderBy('a.id', 'DESC');

    $keyword = is_string($keyword) ? trim($keyword) : null;
    if (!empty($keyword)) {
        $builder->groupStart()
                ->like('a.nama_layanan', $keyword)
                ->orLike('a.alias', $keyword)
                ->groupEnd();
    }

    return $builder->get()->getResultArray();
}


    public function validateEnrolled($param,$enroll){
        $builder = $this->db->table('txn_enroll');
        $builder->where('nip', $param);
        $builder->where('layanan_id', $enroll);
        return $builder->get()->getResultArray();
    }

    public function getLayananEnrolledData($user){
        return $this->db->query("
            SELECT 
                b.*, a.created_at AS enrolled_at 
            FROM txn_enroll a 
            LEFT JOIN data_layanan b ON b.id = a.layanan_id
            WHERE a.nip = ?
        ", [$user])->getResultArray();
    }

    public function getLayananIdByUrl(string $url, int $fallback = 14): int
    {
        $row = $this->db->table('data_timkerja_layanan')
            ->select('id')
            ->where('url', $url)
            ->get()
            ->getRowArray();

        return isset($row['id']) ? (int) $row['id'] : $fallback;
    }

    public function getInstansiID($param){
        // Check if param is already a numeric ID
        if (is_numeric($param)) {
            $check = $this->db->table('data_instansi')->select('kodeins')->where('kodeins', $param)->get()->getRow();
            if ($check) {
                return $check->kodeins;
            }
        }

        $normalized = strtolower(str_replace('.', '', (string) $param));
        $escapedPattern = '%' . $this->db->escapeLikeString($normalized) . '%';

        $row = $this->db->table('data_instansi a')
            ->select('a.kodeins')
            ->where(
                "LOWER(REPLACE(a.nama, '.', '')) LIKE " . $this->db->escape($escapedPattern) . " ESCAPE '!'",
                null,
                false
            )
            ->orderBy('LENGTH(a.nama)', 'ASC', false)
            ->limit(1)
            ->get()
            ->getRow();

        return $row ? $row->kodeins : null;
    }

    public function progressLayananDaily($param){
        return $this->db->query("
            SELECT
            SUM(total) total, SUM(uploaded) uploaded, ROUND((SUM(uploaded)/SUM(total)*100),1) percent
            FROM (
                SELECT COUNT(*) AS total, 0 uploaded
                FROM data_timkerja a
                LEFT JOIN data_timkerja_layanan b ON b.timkerja_id = a.id AND b.is_show = 1
                WHERE a.uid = ?
                AND b.url IS NOT NULL
                UNION all
                SELECT 0 total, COUNT(DISTINCT c.layanan_id) AS uploaded_today
                FROM data_timkerja a
                LEFT JOIN data_timkerja_layanan b ON b.timkerja_id = a.id AND b.is_show = 1
                JOIN activity_daily_logs c ON c.layanan_id = b.id
                WHERE a.uid = ?
                AND c.created_at >= CURDATE()
                AND c.created_at < CURDATE() + INTERVAL 1 DAY
                AND b.url IS NOT NULL
            ) xx        
        ", [$param, $param])->getRow();
    }

    public function getInstansi($search = null)
    {
        $builder = $this->db->table('data_instansi');
        $builder->select('kodeins, nama');
        $builder->whereIn('kanreg', [0, 3]);

        if (!empty($search)) {
            $builder->like('nama', $search);
        }

        return $builder
            ->orderBy('nama', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getEventData($search = null)
    {
        $builder = $this->db->table('data_support_jenis_tes');
        $builder->select('*');

        if (!empty($search)) {
            $builder->like('nama', $search);
        }

        return $builder
            ->orderBy('nama', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getCatJenisTes(?string $search = null): array
    {
        $builder = $this->db->table('data_support_jenis_tes')
            ->select('id, kode, nama');

        $search = trim((string) $search);
        if ($search !== '') {
            $builder->groupStart()
                ->like('kode', $search)
                ->orLike('nama', $search)
                ->groupEnd();
        }

        return $builder
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function isCatJenisKodeExists(string $kode, ?int $excludeId = null): bool
    {
        $kode = trim($kode);
        if ($kode === '') {
            return false;
        }
        $normalized = strtolower($kode);

        if ($excludeId !== null) {
            $row = $this->db->query(
                'SELECT id FROM data_support_jenis_tes WHERE LOWER(TRIM(kode)) = ? AND id != ? LIMIT 1',
                [$normalized, $excludeId]
            )->getRowArray();
            return !empty($row);
        }

        $row = $this->db->query(
            'SELECT id FROM data_support_jenis_tes WHERE LOWER(TRIM(kode)) = ? LIMIT 1',
            [$normalized]
        )->getRowArray();

        return !empty($row);
    }

    public function isCatJenisNamaExists(string $nama, ?int $excludeId = null): bool
    {
        $nama = trim($nama);
        if ($nama === '') {
            return false;
        }
        $normalized = strtolower($nama);

        if ($excludeId !== null) {
            $row = $this->db->query(
                'SELECT id FROM data_support_jenis_tes WHERE LOWER(TRIM(nama)) = ? AND id != ? LIMIT 1',
                [$normalized, $excludeId]
            )->getRowArray();
            return !empty($row);
        }

        $row = $this->db->query(
            'SELECT id FROM data_support_jenis_tes WHERE LOWER(TRIM(nama)) = ? LIMIT 1',
            [$normalized]
        )->getRowArray();

        return !empty($row);
    }

    public function createCatJenisTes(string $kode, string $nama)
    {
        $ok = $this->db->table('data_support_jenis_tes')->insert([
            'kode' => trim($kode),
            'nama' => trim($nama),
        ]);

        return $ok ? $this->db->insertID() : false;
    }

    public function updateCatJenisTes(int $id, string $kode, string $nama): bool
    {
        return (bool) $this->db->table('data_support_jenis_tes')
            ->where('id', $id)
            ->update([
                'kode' => trim($kode),
                'nama' => trim($nama),
            ]);
    }

    public function countCatTilokByJenisTes(int $jenisTesId): int
    {
        return (int) $this->db->table('txn_cat_tilok')
            ->where('jenis_tes_id', $jenisTesId)
            ->countAllResults();
    }

    public function deleteCatJenisTes(int $id): bool
    {
        return (bool) $this->db->table('data_support_jenis_tes')
            ->where('id', $id)
            ->delete();
    }

    public function getServiceEventOptions(int $layananId, array $fallback = []): array
    {
        $rows = $this->getServiceEvents($layananId);
        if (!empty($rows)) {
            $options = [];
            foreach ($rows as $row) {
                $name = trim((string) ($row['nama'] ?? ''));
                if ($name === '') {
                    continue;
                }
                $options[$name] = $name;
            }
            if (!empty($options)) {
                return $options;
            }
        }

        return $fallback;
    }

    public function getServiceEvents(int $layananId): array
    {
        if (!$this->db->tableExists('data_support_service_events')) {
            return [];
        }

        return $this->db->table('data_support_service_events')
            ->select('id, nama, layanan_id, sort_order, is_active, created_at, updated_at')
            ->where('layanan_id', $layananId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function serviceEventsTableExists(): bool
    {
        return $this->db->tableExists('data_support_service_events');
    }

    public function createServiceEvent(int $layananId, string $nama, ?string $user = null)
    {
        if (!$this->serviceEventsTableExists()) {
            return false;
        }

        $builder = $this->db->table('data_support_service_events');

        $maxSort = $builder
            ->selectMax('sort_order', 'max_sort')
            ->where('layanan_id', $layananId)
            ->get()
            ->getRowArray();

        $nextSort = (int) ($maxSort['max_sort'] ?? 0) + 1;

        $ok = $builder->insert([
            'layanan_id' => $layananId,
            'nama'       => $nama,
            'sort_order' => $nextSort,
            'is_active'  => 1,
            'created_by' => $user,
            'updated_by' => $user,
        ]);

        return $ok ? $this->db->insertID() : false;
    }

    public function updateServiceEvent(int $id, int $layananId, string $nama, ?string $user = null): bool
    {
        if (!$this->serviceEventsTableExists()) {
            return false;
        }

        return (bool) $this->db->table('data_support_service_events')
            ->where('id', $id)
            ->where('layanan_id', $layananId)
            ->update([
                'nama'       => $nama,
                'updated_by' => $user,
            ]);
    }

    public function deleteServiceEvent(int $id, int $layananId): bool
    {
        if (!$this->serviceEventsTableExists()) {
            return false;
        }

        return (bool) $this->db->table('data_support_service_events')
            ->where('id', $id)
            ->where('layanan_id', $layananId)
            ->delete();
    }

    public function hasDuplicateServiceEvent(int $layananId, string $nama, ?int $excludeId = null): bool
    {
        if (!$this->serviceEventsTableExists()) {
            return false;
        }

        $normalized = strtolower(trim($nama));

        if ($excludeId !== null) {
            $row = $this->db->query(
                'SELECT id FROM data_support_service_events WHERE layanan_id = ? AND LOWER(TRIM(nama)) = ? AND id != ? LIMIT 1',
                [$layananId, $normalized, $excludeId]
            )->getRowArray();

            return !empty($row);
        }

        $row = $this->db->query(
            'SELECT id FROM data_support_service_events WHERE layanan_id = ? AND LOWER(TRIM(nama)) = ? LIMIT 1',
            [$layananId, $normalized]
        )->getRowArray();

        return !empty($row);
    }

    public function getTKData($search = null)
    {
        $builder = $this->db->table('data_pegawai_unit_kerja');
        $builder->select('*');

        if (!empty($search)) {
            $builder->like('nama', $search);
        }

        return $builder
            ->orderBy('nama', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }
 
    public function getNaskahData($search = null, $jenisId = null)
    {
        $builder = $this->db->table('data_support_naskah_klasifikasi')
            ->select('id, nama');

        if (!empty($jenisId)) {
            $builder->where('jenis_id', $jenisId);
        }

        if (!empty($search)) {
            $builder->like('nama', $search);
        }

        return $builder
            ->orderBy('nama', 'ASC')
            ->limit(20)
            ->get()
            ->getResultArray();
    }

    public function getDataPegawai(){
        return $this->db->query("
            SELECT 
                a.*,
                CONCAT(
                    CONCAT(UCASE(LEFT(SUBSTRING_INDEX(a.nama, ',', 1), 1)), 
                        LCASE(SUBSTRING(SUBSTRING_INDEX(a.nama, ',', 1), 2))
                    ),
                    IF(
                        LOCATE(',', a.nama) > 0,
                        CONCAT(', ', SUBSTRING_INDEX(a.nama, ',', -1)),
                        ''
                    )
                ) AS nama_formatted
            FROM data_member a
            WHERE nip <> '199707252024211004'
            ORDER BY a.nama ASC
        ")->getResultArray();
    }

    // public function getDataPegawai(){
    //     $rawSql = "
    //         SELECT 
    //             a.*,
    //             CONCAT(
    //                 CONCAT(UCASE(LEFT(SUBSTRING_INDEX(a.nama, ',', 1), 1)), 
    //                     LCASE(SUBSTRING(SUBSTRING_INDEX(a.nama, ',', 1), 2))
    //                 ),
    //                 IF(
    //                     LOCATE(',', a.nama) > 0,
    //                     CONCAT(', ', SUBSTRING_INDEX(a.nama, ',', -1)),
    //                     ''
    //                 )
    //             ) AS nama_formatted
    //         FROM data_member a
    //         WHERE nip <> '199707252024211004'
    //         ORDER BY a.nama ASC
    //     ";
    //     return $this->db->table("($rawSql) AS recap");
    // }

    public function getDataInstansi(){
        $rawSql = "
            SELECT 
                a.*
            FROM data_instansi a
            WHERE kanreg IN (0,3)
            ORDER BY a.nama ASC
        ";
        return $this->db->table("($rawSql) AS recap");
    }    

    public function getStepMT(){
        return $this->db->query("
            SELECT * FROM data_support_mt ORDER BY id ASC
        ")->getResultArray();        
    }

    public function getStepIntegrasi(){
        return $this->db->query("
            SELECT * FROM data_support_integrasi ORDER BY id ASC
        ")->getResultArray();        
    }

    public function getSelect2Data($source, $search = '')
    {
        $builder = $this->db->table($source)
                ->select('id, nama');

            if (!empty($search)) {
                $builder->like('nama', $search);
            }

            return $builder
                ->orderBy('nama', 'ASC')
                ->limit(20)
                ->get()
                ->getResultArray();
    }

    public function getNPSKData(){
        return $this->db->query("
            SELECT 
                a.kodeins, a.nama, a.kanreg, UPPER(a.wilayah) wilayah, a.logo,
                b.`level`
            FROM data_instansi a
            LEFT JOIN txn_nspk_data b 
                ON b.instansi_id = a.id 
                AND b.period = 2025
            WHERE a.kanreg = 3
        ")->getResultArray();
    }

    public function getIntegrasiData($keyword, $jenis) 
    {
        $divisor = ($jenis !== '0') ? 1 : 8;
        $builder = $this->db->table('txn_activity_upload_logs a')
            ->select("
                a.id,
                a.period, 
                a.period_date, 
                d.nama, 
                d.wilayah, 
                d.logo,
                c.id AS jenis_id,
                c.jenis,
                COUNT(c.jenis) AS total_accept,
                ROUND((COUNT(c.jenis)/{$divisor}*100),2) AS percent
            ")
            ->join('txn_activity_integrasi b', 'b.upload_id = a.id', 'left')
            ->join('data_support_integrasi c', 'c.id = b.jenis_integrasi_id', 'left')
            ->join('data_instansi d', 'd.kodeins = b.instansi_id', 'left')
            ->where('a.layanan_id', 24)
            ->where('a.id = (SELECT MAX(id) FROM txn_activity_upload_logs WHERE layanan_id = 24)', null, false)
            ->groupBy('d.kodeins');
            // ->orderBy('percent', 'DESC');

        // filter by jenis
        if ($jenis !== '0') {
            $builder->where('c.id', $jenis);
        }

        // filter by keyword
        if (!empty($keyword)) {
            $builder->groupStart()
                ->like('d.nama', $keyword)
                ->groupEnd();
        }

        return $builder->get()->getResultArray();
    }

    public function getIntegrasiDataTotal($jenis) 
    {
        $divisor = ($jenis !== '0') ? 37 : 296;
        $builder = $this->db->table('txn_activity_upload_logs a')
            ->select("
                a.id,
                a.period, 
                a.period_date, 
                COUNT(c.jenis) AS total_accept,
                ROUND((COUNT(c.jenis)/{$divisor}*100),2) AS percent
            ")
            ->join('txn_activity_integrasi b', 'b.upload_id = a.id', 'left')
            ->join('data_support_integrasi c', 'c.id = b.jenis_integrasi_id', 'left')
            ->join('data_instansi d', 'd.kodeins = b.instansi_id', 'left')
            ->where('a.layanan_id', 24)
            ->where('a.id = (SELECT MAX(id) FROM txn_activity_upload_logs WHERE layanan_id = 24)', null, false);

        if ($jenis !== '0') {
            $builder->where('c.id', $jenis);
        }

        return $builder->get()->getRow();
    }    


}
