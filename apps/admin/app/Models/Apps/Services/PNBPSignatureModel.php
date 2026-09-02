<?php

namespace App\Models\Apps\Services;

use CodeIgniter\Model;

class PNBPSignatureModel extends Model
{
    protected $table            = 'txn_pnbp_doc_signatures';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'sign_position',
        'sign_role',
        'sign_title',
        'nip',
        'nama',
        'pangkat_gol',
        'jabatan',
        'sign_token',
        'sign_status',
        'signature_image_path',
        'signed_at',
        'signer_ip',
        'signer_user_agent',
        'verification_hash',
        'sort_order',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function generateUniqueToken(): string
    {
        return bin2hex(random_bytes(16)) . time();
    }

    /**
     * Ambil daftar tanda tangan berdasarkan document_id
     */
    public function getSignaturesByDocumentId(int $docId): array
    {
        return $this->where('document_id', $docId)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Cari tanda tangan berdasarkan sign_token beserta info dokumen untuk halaman penandatanganan
     */
    public function getSignatureWithDocument(string $token): ?array
    {
        return $this->db->table('txn_pnbp_doc_signatures s')
            ->select('
                s.*,
                d.uid AS document_uid,
                d.doc_type,
                d.doc_number,
                d.doc_date,
                d.title AS document_title,
                d.status AS document_status,
                sel.nama_seleksi,
                sel.periode AS seleksi_periode,
                til.nama_tilok,
                til.period_start_date,
                til.period_end_date,
                ins.nama AS instansi_nama
            ')
            ->join('txn_pnbp_documents d', 'd.id = s.document_id', 'inner')
            ->join('txn_cat_seleksi sel', 'sel.id = d.seleksi_id', 'left')
            ->join('txn_cat_tilok til', 'til.id = d.tilok_id', 'left')
            ->join('data_instansi ins', 'ins.kodeins = d.instansi_id', 'left')
            ->where('s.sign_token', $token)
            ->get()
            ->getRowArray();
    }

    /**
     * Ambil data penandatangan terakhir berdasarkan jenis dokumen (untuk default transaksi baru)
     */
    public function getLatestSignaturesByDocType(string $docType): array
    {
        // 1. Cari dari transaksi dokumen terakhir untuk jenis dokumen tersebut
        $latestDoc = $this->db->table('txn_pnbp_documents')
            ->select('id')
            ->where('doc_type', $docType)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();

        if ($latestDoc) {
            $sigs = $this->where('document_id', $latestDoc['id'])
                ->orderBy('sort_order', 'ASC')
                ->findAll();

            if (!empty($sigs)) {
                $result = [];
                foreach ($sigs as $s) {
                    $roleKey = $s['sign_role'] ?: $s['sign_position'];
                    $result[$roleKey] = $s;
                }
                return $result;
            }
        }

        // 2. Fallback ke cfg_pnbp_signers
        $signerModel = new PNBPSignerModel();
        return $signerModel->getDefaultSignersMap();
    }

    /**
     * Inisialisasi blok tanda tangan default untuk dokumen yang baru dibuat
     */
    public function initDefaultSignatures(int $documentId, string $docType, array $customSigners = []): void
    {
        $latestSigners = $this->getLatestSignaturesByDocType($docType);
        $signerModel   = new PNBPSignerModel();
        $defaultMap    = $signerModel->getDefaultSignersMap();

        $signatures = [];

        switch ($docType) {
            case 'sp':
            case 'st':
                // 1 Kolom TTD: Kakanreg
                $kakanreg = $latestSigners['kakanreg'] ?? ($defaultMap['kakanreg'] ?? null);
                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'right',
                    'sign_role'     => 'kakanreg',
                    'sign_title'    => $customSigners['kakanreg']['sign_title'] ?? ($kakanreg['sign_title'] ?? 'Yang Memerintahkan,'),
                    'nip'           => $customSigners['kakanreg']['nip'] ?? ($kakanreg['nip'] ?? ($kakanreg['default_nip'] ?? '')),
                    'nama'          => $customSigners['kakanreg']['nama'] ?? ($kakanreg['nama'] ?? ($kakanreg['default_nama'] ?? 'Kepala Kantor Regional')),
                    'pangkat_gol'   => $customSigners['kakanreg']['pangkat_gol'] ?? ($kakanreg['pangkat_gol'] ?? ($kakanreg['default_pangkat_gol'] ?? '')),
                    'jabatan'       => $customSigners['kakanreg']['jabatan'] ?? ($kakanreg['jabatan'] ?? ($kakanreg['default_jabatan'] ?? 'Kepala Kantor Regional III BKN')),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 1,
                ];
                break;

            case 'nominatif':
                // 2 Kolom TTD: PPK (Kiri/Mengetahui) & Bendahara (Kanan)
                $ppk = $latestSigners['ppk_nominatif'] ?? ($defaultMap['ppk_nominatif'] ?? ($latestSigners['ppk'] ?? ($defaultMap['ppk'] ?? null)));
                $bendahara = $latestSigners['bendahara_nominatif'] ?? ($defaultMap['bendahara_nominatif'] ?? ($latestSigners['bendahara'] ?? ($defaultMap['bendahara'] ?? null)));

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'left',
                    'sign_role'     => 'ppk_nominatif',
                    'sign_title'    => $customSigners['ppk']['sign_title'] ?? ($ppk['sign_title'] ?? 'Mengetahui'),
                    'nip'           => $customSigners['ppk']['nip'] ?? ($ppk['nip'] ?? ($ppk['default_nip'] ?? '197104241992032001')),
                    'nama'          => $customSigners['ppk']['nama'] ?? ($ppk['nama'] ?? ($ppk['default_nama'] ?? 'LESTARI PRASETIJANI, SE, MM')),
                    'pangkat_gol'   => $customSigners['ppk']['pangkat_gol'] ?? ($ppk['pangkat_gol'] ?? ($ppk['default_pangkat_gol'] ?? '')),
                    'jabatan'       => $customSigners['ppk']['jabatan'] ?? ($ppk['jabatan'] ?? ($ppk['default_jabatan'] ?? 'Analis Pengelolaan Keuangan APBN Ahli Madya sebagai Pejabat Pembuat Komitmen Pusat Pengembangan Sistem Rekrutmen (PNBP)')),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 1,
                ];

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'right',
                    'sign_role'     => 'bendahara_nominatif',
                    'sign_title'    => $customSigners['bendahara']['sign_title'] ?? ($bendahara['sign_title'] ?? "Jakarta, ..................................................\nDiajukan ke Kuasa Pengguna Anggaran BKN\nPada tanggal..............................."),
                    'nip'           => $customSigners['bendahara']['nip'] ?? ($bendahara['nip'] ?? ($bendahara['default_nip'] ?? '199009062014022001')),
                    'nama'          => $customSigners['bendahara']['nama'] ?? ($bendahara['nama'] ?? ($bendahara['default_nama'] ?? 'FITRIANI PANJAITAN, S.Kom.')),
                    'pangkat_gol'   => $customSigners['bendahara']['pangkat_gol'] ?? ($bendahara['pangkat_gol'] ?? ($bendahara['default_pangkat_gol'] ?? '')),
                    'jabatan'       => $customSigners['bendahara']['jabatan'] ?? ($bendahara['jabatan'] ?? ($bendahara['default_jabatan'] ?? 'Bendahara Pengeluaran')),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 2,
                ];
                break;

            case 'kwitansi':
            case 'kwitansi_jamuan':
                // 3 Kolom TTD: PPK (Kiri), Bendahara (Tengah), Penerima (Kanan)
                $ppk = $defaultSigners['ppk'] ?? null;
                $bendahara = $defaultSigners['bendahara'] ?? null;

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'left',
                    'sign_role'     => 'ppk',
                    'sign_title'    => 'Setuju Dibayar,',
                    'nip'           => $customSigners['ppk']['nip'] ?? ($ppk['default_nip'] ?? ''),
                    'nama'          => $customSigners['ppk']['nama'] ?? ($ppk['default_nama'] ?? 'Pejabat Pembuat Komitmen'),
                    'pangkat_gol'   => $customSigners['ppk']['pangkat_gol'] ?? ($ppk['default_pangkat_gol'] ?? ''),
                    'jabatan'       => $customSigners['ppk']['jabatan'] ?? ($ppk['default_jabatan'] ?? 'Pejabat Pembuat Komitmen'),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 1,
                ];

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'center',
                    'sign_role'     => 'bendahara',
                    'sign_title'    => 'Lunas Dibayar,',
                    'nip'           => $customSigners['bendahara']['nip'] ?? ($bendahara['default_nip'] ?? ''),
                    'nama'          => $customSigners['bendahara']['nama'] ?? ($bendahara['default_nama'] ?? 'Bendahara Pengeluaran'),
                    'pangkat_gol'   => $customSigners['bendahara']['pangkat_gol'] ?? ($bendahara['default_pangkat_gol'] ?? ''),
                    'jabatan'       => $customSigners['bendahara']['jabatan'] ?? ($bendahara['default_jabatan'] ?? 'Bendahara Pengeluaran'),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 2,
                ];

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'right',
                    'sign_role'     => 'penerima',
                    'sign_title'    => 'Yang Menerima,',
                    'nip'           => $customSigners['penerima']['nip'] ?? '',
                    'nama'          => $customSigners['penerima']['nama'] ?? 'Penerima Uang',
                    'pangkat_gol'   => $customSigners['penerima']['pangkat_gol'] ?? '',
                    'jabatan'       => $customSigners['penerima']['jabatan'] ?? ($docType === 'kwitansi_jamuan' ? 'Penyedia Katering' : 'Petugas Pelaksana'),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 3,
                ];
                break;

            case 'hadir':
            case 'hadir_jamuan':
            case 'surat_jalan':
            case 'faktur':
            default:
                // 2 Kolom TTD: Koordinator Tilok / Pengirim & Penerima
                $koordinator = $defaultSigners['koordinator'] ?? null;

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'left',
                    'sign_role'     => 'pengirim',
                    'sign_title'    => 'Yang Menyerahkan / Pengirim,',
                    'nip'           => $customSigners['pengirim']['nip'] ?? '',
                    'nama'          => $customSigners['pengirim']['nama'] ?? 'Pihak Penyedia',
                    'pangkat_gol'   => '',
                    'jabatan'       => 'Penyedia / Petugas Konsumsi',
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 1,
                ];

                $signatures[] = [
                    'document_id'   => $documentId,
                    'sign_position' => 'right',
                    'sign_role'     => 'koordinator',
                    'sign_title'    => 'Yang Menerima / Mengetahui,',
                    'nip'           => $customSigners['koordinator']['nip'] ?? ($koordinator['default_nip'] ?? ''),
                    'nama'          => $customSigners['koordinator']['nama'] ?? ($koordinator['default_nama'] ?? 'Koordinator Tilok'),
                    'pangkat_gol'   => $customSigners['koordinator']['pangkat_gol'] ?? ($koordinator['default_pangkat_gol'] ?? ''),
                    'jabatan'       => $customSigners['koordinator']['jabatan'] ?? ($koordinator['default_jabatan'] ?? 'Koordinator Titik Lokasi'),
                    'sign_token'    => $this->generateUniqueToken(),
                    'sign_status'   => 'pending',
                    'sort_order'    => 2,
                ];
                break;
        }

        if (!empty($signatures)) {
            $this->insertBatch($signatures);
        }
    }

    /**
     * Update data snapshot penandatangan untuk dokumen tertentu, dan perbarui default signer untuk dokumen baru berikutnya
     */
    public function updateDocumentSigners(int $documentId, string $docType, array $signers): bool
    {
        foreach ($signers as $sig) {
            $sigId = (int) ($sig['id'] ?? 0);
            $role  = $sig['sign_role'] ?? '';
            $pos   = $sig['sign_position'] ?? '';

            $updateData = [
                'nama'        => trim((string) ($sig['nama'] ?? '')),
                'nip'         => trim((string) ($sig['nip'] ?? '')),
                'jabatan'     => trim((string) ($sig['jabatan'] ?? '')),
                'pangkat_gol' => trim((string) ($sig['pangkat_gol'] ?? '')),
                'sign_title'  => trim((string) ($sig['sign_title'] ?? '')),
            ];

            if ($sigId > 0) {
                $this->update($sigId, $updateData);
            } else {
                $this->where('document_id', $documentId)
                    ->where('sign_position', $pos)
                    ->set($updateData)
                    ->update();
            }

            // Update default config signer untuk transaksi baru berikutnya jika jenisnya nominatif
            if ($docType === 'nominatif') {
                $configKode = ($role === 'ppk' || $pos === 'left') ? 'ppk_nominatif' : 'bendahara_nominatif';
                $existingCfg = $this->db->table('cfg_pnbp_signers')->where('kode', $configKode)->get()->getRowArray();
                if ($existingCfg) {
                    $this->db->table('cfg_pnbp_signers')->where('kode', $configKode)->update([
                        'default_nama'        => $updateData['nama'],
                        'default_nip'         => $updateData['nip'],
                        'default_jabatan'     => $updateData['jabatan'],
                        'default_pangkat_gol' => $updateData['pangkat_gol'],
                        'updated_at'          => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return true;
    }
}
