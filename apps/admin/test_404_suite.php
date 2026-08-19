<?php

/**
 * SIMOJANG 404 Error & Exception Handler Test Suite
 */

ob_start();

require_once __DIR__ . '/vendor/codeigniter4/framework/system/Test/bootstrap.php';

$session = \Config\Services::session();

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\HTTP\Response;
use Firebase\JWT\JWT;
use Config\Services;

class NotFoundTestSuite
{
    private string $jwtSecret;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->jwtSecret = getenv('JWT_TOKEN_SECRET') ?: (env('JWT_TOKEN_SECRET') ?: 'simojang_default_jwt_secret_key_2026');
    }

    public function run()
    {
        ob_end_clean();
        echo "========================================================\n";
        echo "  SIMOJANG CI4 Global 404 Exception Handler Test Suite  \n";
        echo "========================================================\n\n";

        $this->test404WhenUserNotLoggedIn();
        $this->test404WhenUserIsLoggedIn();
        $this->test404WhenUserTokenExpired();
        $this->test404WhenUserTokenInvalid();
        $this->testRouterThrows404OnTypoRoute();
        $this->testRouterThrows404OnNestedInvalidRoute();
        $this->testRouterPassesValidRoutes();
        $this->test404PageContainsRequiredUIElements();
        $this->test404PageDoesNotExposeInternalData();
        $this->testAssetUrlsRenderProperlyOn404();

        echo "\n========================================================\n";
        echo "  Test Summary: {$this->passed} Passed, {$this->failed} Failed\n";
        echo "========================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function assert($condition, string $scenario): void
    {
        if ($condition) {
            $this->passed++;
            echo " [PASS] {$scenario}\n";
        } else {
            $this->failed++;
            echo " [FAIL] {$scenario}\n";
        }
    }

    private function render404View(string $message = 'Cannot find route'): string
    {
        $viewFile = APPPATH . 'Views/Errors/html/error_404.php';
        $code = 404;
        $title = 'CodeIgniter\Exceptions\PageNotFoundException';
        $type = 'CodeIgniter\Exceptions\PageNotFoundException';
        $file = __FILE__;
        $line = __LINE__;
        $trace = [];

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

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
        if (session_status() === PHP_SESSION_ACTIVE) {
            session()->destroy();
        }
    }

    public function test404WhenUserNotLoggedIn(): void
    {
        $this->cleanSession();
        $html = $this->render404View();

        $hasLoginBtn = str_contains($html, 'Login') && str_contains($html, 'login');
        $hasNoHomeBtn = !str_contains($html, 'Kembali ke Home');

        $this->assert($hasLoginBtn && $hasNoHomeBtn, 'Skenario 1: User belum login melihat tombol "Login" mengarah ke /login');
    }

    public function test404WhenUserIsLoggedIn(): void
    {
        $this->cleanSession();

        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'nbf' => $issuedAt,
            'user_id' => 999,
            'user_name' => '199001012020011001',
            'user_fullname' => 'Administrator Simojang',
            'role' => 'ADM',
        ];
        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        session()->set('jwt_auth_token', $token);
        session()->set('userid', 999);
        session()->set('username', '199001012020011001');
        session()->set('fullname', 'Administrator Simojang');
        session()->set('role', 'ADM');

        $html = $this->render404View();

        $hasHomeBtn = str_contains($html, 'Kembali ke Home') && str_contains($html, 'dashboard');
        $hasNoLoginBtn = !preg_match('/<a[^>]*class="btn-action-primary"[^>]*>[\s\S]*?Login[\s\S]*?<\/a>/i', $html);

        $this->cleanSession();
        $this->assert($hasHomeBtn && $hasNoLoginBtn, 'Skenario 2: User yang sudah login melihat tombol "Kembali ke Home" mengarah ke /dashboard');
    }

    public function test404WhenUserTokenExpired(): void
    {
        $this->cleanSession();

        $issuedAt = time() - 7200;
        $expirationTime = time() - 3600; // Expired 1 hour ago
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'nbf' => $issuedAt,
            'user_id' => 999,
            'user_name' => '199001012020011001',
            'user_fullname' => 'User Expired',
            'role' => 'USR',
        ];
        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        session()->set('jwt_auth_token', $token);
        session()->set('userid', 999);

        $html = $this->render404View();

        $hasLoginBtn = str_contains($html, 'Login') && str_contains($html, 'login');
        $hasNoHomeBtn = !str_contains($html, 'Kembali ke Home');

        $this->cleanSession();
        $this->assert($hasLoginBtn && $hasNoHomeBtn, 'Skenario 3: Token expired aman ditangani dan menampilkan tombol "Login"');
    }

    public function test404WhenUserTokenInvalid(): void
    {
        $this->cleanSession();

        session()->set('jwt_auth_token', 'invalid.corrupted.jwt.token.string');
        session()->set('userid', 123);

        $html = $this->render404View();

        $hasLoginBtn = str_contains($html, 'Login') && str_contains($html, 'login');
        $hasNoHomeBtn = !str_contains($html, 'Kembali ke Home');

        $this->cleanSession();
        $this->assert($hasLoginBtn && $hasNoHomeBtn, 'Skenario 4: Token invalid aman ditangani dan menampilkan tombol "Login"');
    }

    public function testRouterThrows404OnTypoRoute(): void
    {
        $routes = Services::routes();
        require APPPATH . 'Config/Routes.php';

        $uri = new URI('http://localhost:8080/dashboardd2');
        $request = new IncomingRequest(new \Config\App(), $uri, null, new UserAgent());
        $router = Services::router($routes, $request);

        $threw404 = false;
        try {
            $router->handle('dashboardd2');
        } catch (PageNotFoundException $e) {
            $threw404 = true;
        }

        $this->assert($threw404, 'Skenario 5: Router melempar PageNotFoundException untuk route typo (/dashboardd2)');
    }

    public function testRouterThrows404OnNestedInvalidRoute(): void
    {
        $routes = Services::routes();
        require APPPATH . 'Config/Routes.php';

        $uri = new URI('http://localhost:8080/services/layanan-tidak-ada/detail/123');
        $request = new IncomingRequest(new \Config\App(), $uri, null, new UserAgent());
        $router = Services::router($routes, $request);

        $threw404 = false;
        try {
            $router->handle('services/layanan-tidak-ada/detail/123');
        } catch (PageNotFoundException $e) {
            $threw404 = true;
        }

        $this->assert($threw404, 'Skenario 6: Router melempar PageNotFoundException untuk URL nested yang tidak terdaftar');
    }

    public function testRouterPassesValidRoutes(): void
    {
        $routes = Services::routes();
        require APPPATH . 'Config/Routes.php';

        $uri = new URI('http://localhost:8080/dashboard');
        $request = new IncomingRequest(new \Config\App(), $uri, null, new UserAgent());
        $router = Services::router($routes, $request);

        $controller = $router->handle('dashboard');
        $this->assert(!empty($controller) && str_contains($controller, 'DashboardController'), 'Skenario 7: Route valid (/dashboard) tetap ter-routing secara normal');
    }

    public function test404PageContainsRequiredUIElements(): void
    {
        $html = $this->render404View();

        $hasSvg = str_contains($html, '<svg') && str_contains($html, 'warning-illustration');
        $hasHeading = str_contains($html, 'Halaman Tidak Ditemukan');
        $hasDescription = str_contains($html, 'Maaf, halaman yang Anda tuju tidak ditemukan');
        $hasPrimaryColor = str_contains($html, '#1040c1') || str_contains($html, '#1040C1');

        $this->assert($hasSvg && $hasHeading && $hasDescription && $hasPrimaryColor, 'Skenario 8: Halaman 404 memiliki SVG illustration, Heading, Deskripsi, dan styling #1040c1');
    }

    public function test404PageDoesNotExposeInternalData(): void
    {
        $internalMessage = "Can't find a route for 'GET: dashboardd2'";
        $html = $this->render404View($internalMessage);

        $noLeakMessage = !str_contains($html, "Can't find a route");
        $noLeakTrace = !str_contains($html, "Trace:") && !str_contains($html, "Call Stack") && !str_contains($html, "CodeIgniter\\");

        $this->assert($noLeakMessage && $noLeakTrace, 'Skenario 9: Halaman 404 tidak mengekspos informasi internal/stack trace');
    }

    public function testAssetUrlsRenderProperlyOn404(): void
    {
        $html = $this->render404View();

        $hasFavicon = str_contains($html, 'favicon.png');
        $hasCustomCss = str_contains($html, 'custom.css');
        $hasAppCss = str_contains($html, 'app.css');

        $this->assert($hasFavicon && $hasCustomCss && $hasAppCss, 'Skenario 10: Asset CSS, icons, dan favicon dimuat dengan benar');
    }
}

$suite = new NotFoundTestSuite();
$suite->run();
