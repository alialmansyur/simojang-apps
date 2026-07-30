<?php

namespace App\Models\Auth;

use CodeIgniter\Model;

class AuthModel extends Model
{
    protected $table = 'auth_logins';
    protected $allowedFields = ['user_id', 'login_time', 'ip_address', 'user_agent','flag'];

    public function logLogin($userId)
    {
        return $this->insert([
            'user_id'   => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'flag'    => 1
        ]);
    }
    
}
