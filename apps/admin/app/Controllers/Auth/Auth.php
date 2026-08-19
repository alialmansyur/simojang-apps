<?php

namespace App\Controllers\Auth;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\API\ResponseTrait;
use App\Models\Auth\AuthModel;
use App\Models\Auth\UserModel;
use App\Models\Apps\AppsModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class Auth extends ResourceController
{
    use ResponseTrait; 

    public function index()
    {        
        return view('Auth/login');
    }

    public function authprocess()
    {
        helper(['form']);
        $rules = [
            'o_userlogin' => 'required',
            'o_password' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // $recaptchaResponse = $this->request->getPost('g-recaptcha-response');
        // if (!$this->validateRecaptcha($recaptchaResponse)) {
        //     return $this->failUnauthorized('reCAPTCHA verification failed');
        // }

        $username = trim((string) $this->request->getPost('o_userlogin'));
        $password = (string) $this->request->getPost('o_password');
        $rememberLogin = (string) $this->request->getPost('remember_login') === '1';
        $model = new UserModel();
        $userdata = $model->where('LOWER(username)', strtolower($username))->first();

        if (!$userdata) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Username tidak ditemukan',
            ], 404);
        }

        if (!password_verify($password, $userdata['password'])) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Password salah',
            ], 422);
        }

        // Catat waktu last_login ke tabel auth_users
        try {
            $model->recordLastLogin((int) $userdata['id']);
        } catch (Throwable $e) {
            log_message('warning', 'Auth recordLastLogin failed: {message}', ['message' => $e->getMessage()]);
        }

        $fingerprintJson = $this->request->getPost('fingerprint');
        if ($fingerprintJson) { 
            try {
                $fingerprintData = json_decode((string) $fingerprintJson, true);
                if (is_array($fingerprintData)) {
                    $ipAddress = $this->request->getIPAddress();
                    $agentModel = new AppsModel();
                    $userAgent = [
                        'username'        => $userdata['username'],
                        'user_agent'      => $fingerprintData['user_agent'] ?? null,
                        'user_agent_hash' => hash('sha256', (string) ($fingerprintData['user_agent'] ?? '')),
                        'language'        => $fingerprintData['language'] ?? null,
                        'platform'        => $fingerprintData['platform'] ?? null,
                        'cpu_cores'       => $fingerprintData['cpu_cores'] ?? null,
                        'device_memory'   => $fingerprintData['device_memory'] ?? null,
                        'screen_width'    => $fingerprintData['screen_width'] ?? null,
                        'screen_height'   => $fingerprintData['screen_height'] ?? null,
                        'timezone'        => $fingerprintData['timezone'] ?? null,
                        'touch_support'   => $fingerprintData['touch_support'] ?? 0,
                        'ip_address'      => $ipAddress,
                        'created_at'      => date('Y-m-d H:i:s'),
                    ];
                    $agentModel->storeData($userAgent, 'auth_useragent');
                }
            } catch (Throwable $e) {
                log_message('warning', 'Auth fingerprint logging failed: {message}', ['message' => $e->getMessage()]);
            }
        }

        $key = getenv('JWT_TOKEN_SECRET') ?: (env('JWT_TOKEN_SECRET') ?: 'simojang_default_jwt_secret_key_2026');
        $loc = getenv('LOCATIONIQ_SECRET_KEY');
        $issuedAt = time();
        $tokenTtl = $rememberLogin ? (7 * 24 * 60 * 60) : 3600; // 7 hari atau 1 jam
        $expirationTime = $issuedAt + $tokenTtl;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'nbf' => $issuedAt,
            'user_id' => $userdata['id'],
            'user_name' => $userdata['username'],
            'user_fullname' => $userdata['fullname'],
            'role' => $userdata['role'] ?? 'USR',
            'remember' => $rememberLogin ? 1 : 0,
        ];

        $tokenjwt = JWT::encode($payload, $key, 'HS256');
        session()->set('jwt_auth_token', $tokenjwt);
        session()->set('userid', $userdata['id']);
        session()->set('username', $userdata['username']);
        session()->set('fullname', $userdata['fullname']);
        session()->set('role', $userdata['role'] ?? 'USR');
        session()->set('login_remember', $rememberLogin ? 1 : 0);
        session()->set('jwt_expired_at', $expirationTime);

        return $this->respond([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $tokenjwt,
            'role'  => $userdata['role'],
            'locationiq' => $loc,
            'expires_in' => $tokenTtl,
            'remember_login' => $rememberLogin ? 1 : 0,
        ]);
    }

    public function changePassword()
    {
        $model      = new UserModel();
        $password1 = htmlspecialchars($this->request->getPost('o_password1'));
        $password2 = htmlspecialchars($this->request->getPost('o_password2'));
        $password3 = htmlspecialchars($this->request->getPost('o_password3'));

        if ($password2 !== $password3) {
            return $this->failValidationErrors('Kata sandi tidak cocok.');
        }

        if ($password2 == $password1) {
            return $this->failValidationErrors('Kata sandi baru tidak boleh sama dengan yang lama.');
        }


        if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password2)) {
            return $this->failValidationErrors('Kata sandi harus terdiri dari minimal 8 karakter, mengandung setidaknya satu huruf besar, satu huruf kecil, satu angka, dan satu simbol.');
        }


        $sess       = session()->get();
        $username   = $sess['username'];
        $sessID     = $sess['userid'];
        $userdata   = $model->where("username", $username)->first();

        if (!password_verify($password1, $userdata['password'])) {
            return $this->failUnauthorized('Password Lama anda tidak sesuai');
        }

        $hashedPassword = password_hash($password2, PASSWORD_BCRYPT);
        $model->updatePassword($sessID, $hashedPassword);
        return $this->respond([
            'status' => 'success',
            'messages' => 'Kata sandi berhasil diubah.'
        ]);
    }

    private function validateRecaptcha($recaptchaResponse)
    {
        $secretKey = getenv('RECAPTCHA_SECRET_KEY');
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $secretKey,
            'response' => $recaptchaResponse
        ]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 3);
        $response = curl_exec($curl);
        curl_close($curl);
        $responseData = json_decode($response);
        return isset($responseData->success) && $responseData->success;
    }

    public function logout()
    {
        session()->remove([
            'jwt_auth_token',
            'userid',
            'username',
            'fullname',
            'role',
            'login_remember',
            'jwt_expired_at',
            'active_menus',
            'active_submenu',
        ]);
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have logged out successfully');
    }
}
