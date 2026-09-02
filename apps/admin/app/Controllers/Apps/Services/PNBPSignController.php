<?php

namespace App\Controllers\Apps\Services;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use App\Models\Apps\Services\PNBPSignatureModel;
use App\Models\Apps\Services\PNBPDocumentModel;
use App\Models\Apps\Services\PNBPDocTypeModel;

class PNBPSignController extends BaseController
{
    protected PNBPSignatureModel $sigModel;
    protected PNBPDocumentModel $docModel;
    protected PNBPDocTypeModel $docTypeModel;

    public function __construct()
    {
        $this->sigModel     = new PNBPSignatureModel();
        $this->docModel     = new PNBPDocumentModel();
        $this->docTypeModel = new PNBPDocTypeModel();
    }

    /**
     * Halaman Web / Mobile untuk Membubuhkan Tanda Tangan Digital (/pnbp-sign/{token})
     */
    public function signView(string $token)
    {
        $signature = $this->sigModel->getSignatureWithDocument($token);
        if (!$signature) {
            throw PageNotFoundException::forPageNotFound('Tautan tanda tangan tidak ditemukan atau tidak valid.');
        }

        $labels = $this->docTypeModel->getDocTypeLabels(false);
        if (empty($labels)) {
            $labels = PNBPDocumentModel::$docTypeLabels;
        }

        return view('Apps/pages/services/pnbp/sign_page', [
            'sig'           => $signature,
            'docTypeLabels' => $labels,
        ]);
    }

    /**
     * Endpoint AJAX: Submit Tanda Tangan dari Signature Canvas
     */
    public function submitSignature()
    {
        $token     = trim((string) $this->request->getPost('token'));
        $imageData = $this->request->getPost('signature_image'); // Base64 PNG
        $signerNip = trim((string) $this->request->getPost('nip'));
        $signerNama= trim((string) $this->request->getPost('nama'));

        if ($token === '' || empty($imageData)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Goresan tanda tangan tidak boleh kosong.',
            ]);
        }

        $signature = $this->sigModel->where('sign_token', $token)->first();
        if (!$signature) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'error',
                'message' => 'Data tanda tangan tidak ditemukan.',
            ]);
        }

        // Decode Base64 PNG
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $type = strtolower($type[1]); // png

            $decoded = base64_decode($imageData);
            if ($decoded === false) {
                return $this->response->setStatusCode(422)->setJSON([
                    'status'  => 'error',
                    'message' => 'Format gambar tanda tangan tidak valid.',
                ]);
            }
        } else {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Data gambar tidak valid.',
            ]);
        }

        // Folder penyimpanan tanda tangan
        $year = date('Y');
        $uploadDir = WRITEPATH . 'uploads/signatures/' . $year;
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $fileName = 'sig_' . $token . '.png';
        $relPath  = 'uploads/signatures/' . $year . '/' . $fileName;
        $fullPath = $uploadDir . '/' . $fileName;
        @file_put_contents($fullPath, $decoded);

        $ipAddress = $this->request->getIPAddress();
        $userAgent = $this->request->getUserAgent() ? $this->request->getUserAgent()->getAgentString() : 'Unknown';
        $now = date('Y-m-d H:i:s');
        $hash = hash('sha256', $token . $now . $ipAddress . $signerNama);

        // Update record signature
        $updatePayload = [
            'sign_status'          => 'signed',
            'signature_image_path' => $relPath,
            'signed_at'            => $now,
            'signer_ip'            => $ipAddress,
            'signer_user_agent'    => $userAgent,
            'verification_hash'    => $hash,
        ];

        if ($signerNama !== '') {
            $updatePayload['nama'] = $signerNama;
        }
        if ($signerNip !== '') {
            $updatePayload['nip'] = $signerNip;
        }

        $this->sigModel->update($signature['id'], $updatePayload);

        // Touch timestamp dokumen induk untuk refresh cache PDF
        if (!empty($signature['document_id'])) {
            $docModel = new \App\Models\Apps\Services\PNBPDocumentModel();
            $docModel->update((int) $signature['document_id'], ['updated_at' => $now]);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Tanda tangan digital berhasil dibubuhkan.',
            'signed_at'=> $now,
            'hash'     => substr($hash, 0, 16),
        ]);
    }

    /**
     * Halaman Publik Verifikasi Keabsahan Dokumen (/pnbp-verify/{token})
     */
    public function verifyDocument(string $token)
    {
        $signature = $this->sigModel->getSignatureWithDocument($token);
        if (!$signature) {
            throw PageNotFoundException::forPageNotFound('Dokumen tidak terdaftar dalam sistem verifikasi keaslian.');
        }

        $labels = $this->docTypeModel->getDocTypeLabels(false);
        if (empty($labels)) {
            $labels = PNBPDocumentModel::$docTypeLabels;
        }

        return view('Apps/pages/services/pnbp/verify_page', [
            'sig'           => $signature,
            'docTypeLabels' => $labels,
        ]);
    }
}
