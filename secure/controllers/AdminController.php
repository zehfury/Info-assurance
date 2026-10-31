<?php
declare(strict_types=1);

final class AdminController extends Controller
{
    public function __construct(string $route)
    {
        parent::__construct($route);
        $this->requireRole('admin');
    }

    public function dashboard(): void
    {
        $users = new User();

        $this->render('admin/dashboard', [
            'title'      => 'Admin dashboard',
            'userCounts' => $users->counts(),
            'totalLogs'  => $this->logs->total(),
            'last24h'    => $this->logs->last24Hours(),
            'byEndpoint' => $this->logs->countsByEndpoint(),
            'topSources' => $this->logs->topSources(5),
            'recent'     => $this->logs->page(10, 0),
            'endpoints'  => SqliLog::ENDPOINTS,
        ]);
    }

    public function logs(): void
    {
        $perPage  = (int) config('logs_per_page', 15);
        $endpoint = $_GET['endpoint'] ?? '';
        $endpoint = is_string($endpoint) && isset(SqliLog::ENDPOINTS[$endpoint]) ? $endpoint : null; // whitelist

        $total = $this->logs->total($endpoint);
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($pages, max(1, (int) ($_GET['page'] ?? 1)));

        $this->render('admin/logs', [
            'title'     => 'SQL injection logs',
            'rows'      => $this->logs->page($perPage, ($page - 1) * $perPage, $endpoint),
            'total'     => $total,
            'page'      => $page,
            'pages'     => $pages,
            'endpoint'  => $endpoint,
            'endpoints' => SqliLog::ENDPOINTS,
        ]);
    }

    public function clearLogs(): void
    {
        if ($this->isPost() && Session::verifyCsrf($_POST['csrf'] ?? null)) {
            $deleted = $this->logs->clear();
            Session::flash('success', "Cleared $deleted log entries.");
        } else {
            Session::flash('error', 'Could not clear logs: invalid request.');
        }
        $this->redirect('admin/logs');
    }

    public function users(): void
    {
        $this->render('admin/users', [
            'title' => 'Users',
            'users' => (new User())->all(),
        ]);
    }
}
