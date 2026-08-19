<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
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
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'You must log in first',
                    ]);
            }
            return redirect()->to('/login')->with('error', 'You must log in first');
        }
    
        try {
            $key = getenv('JWT_TOKEN_SECRET') ?: (env('JWT_TOKEN_SECRET') ?: 'simojang_default_jwt_secret_key_2026');
            $decoded = JWT::decode($jwtToken, new Key($key, 'HS256'));
            session()->set('userid', $decoded->user_id ?? session()->get('userid'));
            session()->set('username', $decoded->user_name ?? session()->get('username'));
            session()->set('fullname', $decoded->user_fullname ?? session()->get('fullname'));
            if (isset($decoded->role)) {
                session()->set('role', $decoded->role);
            }
        } catch (ExpiredException $e) {
            $this->cleanSession();
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'Token expired, please login again',
                    ]);
            }
            return redirect()->to('/login')->with('error', 'Token expired, please login again');
        } catch (Exception $e) {
            $this->cleanSession();
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                    ->setJSON([
                        'status'  => false,
                        'message' => 'Invalid token, please login again',
                    ]);
            }
            return redirect()->to('/login')->with('error', 'Invalid token, please login again');
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
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
