<?php
declare(strict_types=1);

abstract class Controller
{
    protected SqliLog $logs;

    public function __construct(protected string $route)
    {
        $this->logs = new SqliLog();
    }

    /** Render a view inside the main layout. Views only receive what is passed here. */
    protected function render(string $view, array $data = []): void
    {
        $data += [
            'title'        => 'SQLi Training Lab',
            'auth'         => Session::user(),
            'flash'        => Session::pullFlash(),
            'csrf'         => Session::csrfToken(),
            'currentRoute' => $this->route,
        ];
        extract($data, EXTR_SKIP);

        ob_start();
        require VIEW_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        require VIEW_PATH . '/layout/main.php';
    }

    protected function redirect(string $route, array $params = []): never
    {
        header('Location: ' . url($route, $params));
        exit;
    }

    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /** Read a POST field as a string (arrays and missing fields become ''). */
    protected function post(string $key): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? $value : '';
    }

    protected function requireLogin(string $loginRoute = 'login'): array
    {
        $user = Session::user();
        if ($user === null) {
            $this->redirect($loginRoute);
        }
        return $user;
    }

    protected function requireRole(string $role): array
    {
        $user = $this->requireLogin($role === 'admin' ? 'admin/login' : 'login');
        if ($user['role'] !== $role) {
            http_response_code(403);
            $this->render('errors/403', ['title' => 'Forbidden']);
            exit;
        }
        return $user;
    }

    /** Log every field that looks like an SQL injection attempt, then alert the admin by email (throttled). */
    protected function inspectInput(string $endpoint, array $fields): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua  = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $uri = $_SERVER['REQUEST_URI'] ?? null;
        $logged = [];

        foreach ($fields as $name => $value) {
            $hits = SqliDetector::detect((string) $value);
            if ($hits === []) {
                continue;
            }
            try {
                $id = $this->logs->record($endpoint, (string) $name, (string) $value, $hits, $ip, $ua, $uri);
                $logged[] = ['id' => $id, 'field' => (string) $name, 'payload' => (string) $value, 'patterns' => $hits];
            } catch (Throwable $e) {
                error_log('SQLi log write failed: ' . $e->getMessage());
            }
        }

        if ($logged !== []) {
            try {
                AlertMailer::notify($this->logs, $endpoint, $logged, $ip, $ua, $uri);
            } catch (Throwable $e) {
                error_log('SQLi alert failed: ' . $e->getMessage());
            }
        }
    }
}
