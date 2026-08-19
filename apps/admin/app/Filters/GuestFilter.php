<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class GuestFilter implements FilterInterface
{
    /**
     * Filter for guest routes (e.g. /login, /).
     * If user already has an active and valid session, redirect to /dashboard.
     * If session is missing, expired, or invalid, allow access to login page without loops.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $jwtToken = session()->get('jwt_auth_token');
        if (empty($jwtToken)) {
            return;
        }

        $key = getenv('JWT_TOKEN_SECRET') ?: (env('JWT_TOKEN_SECRET') ?: 'simojang_default_jwt_secret_key_2026');

        try {
            $decoded = JWT::decode($jwtToken, new Key($key, 'HS256'));

            // Synchronize session details from valid token
            session()->set('userid', $decoded->user_id ?? session()->get('userid'));
            session()->set('username', $decoded->user_name ?? session()->get('username'));
            session()->set('fullname', $decoded->user_fullname ?? session()->get('fullname'));
            if (isset($decoded->role)) {
                session()->set('role', $decoded->role);
            }

            // If already authenticated and accessing guest route, redirect to /dashboard
            if ($request->isAJAX()) {
                return service('response')->setJSON([
                    'status'   => true,
                    'redirect' => site_url('dashboard'),
                ]);
            }

            return redirect()->to('/dashboard');
        } catch (ExpiredException $e) {
            // Clean up expired session data so user can log in cleanly
            $this->cleanSession();
            return;
        } catch (Exception $e) {
            // Clean up invalid session data so user can log in cleanly
            $this->cleanSession();
            return;
        }
    }

    /**
     * Clean up session data on invalid/expired token.
     */
    private function cleanSession(): void
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
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
