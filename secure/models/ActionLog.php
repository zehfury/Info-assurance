<?php
declare(strict_types=1);

final class ActionLog extends Model
{
    public const ACTIONS = [
        'login_success' => 'Login succeeded',
        'login_failed'  => 'Login failed',
        'register'      => 'Registration',
        'logout'        => 'Logout',
        'xss_lab'       => 'XSS lab viewed',
    ];

    public function record(string $action, ?int $userId, ?string $identifier, ?string $details): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO action_logs
                (action, user_id, identifier, details, ip_address, user_agent, request_uri)
             VALUES (:action, :user_id, :identifier, :details, :ip, :user_agent, :uri)'
        );
        $stmt->execute([
            ':action'     => $action,
            ':user_id'    => $userId,
            ':identifier' => $identifier === null ? null : mb_substr($identifier, 0, 200),
            ':details'    => $details === null ? null : mb_substr($details, 0, 500),
            ':ip'         => mb_substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45),
            ':user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? mb_substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
            ':uri'        => isset($_SERVER['REQUEST_URI']) ? mb_substr((string) $_SERVER['REQUEST_URI'], 0, 255) : null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function total(?string $action = null): int
    {
        if ($action === null) {
            return (int) $this->db->query('SELECT COUNT(*) FROM action_logs')->fetchColumn();
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM action_logs WHERE action = :action');
        $stmt->execute([':action' => $action]);
        return (int) $stmt->fetchColumn();
    }

    public function page(int $limit, int $offset, ?string $action = null): array
    {
        $sql = 'SELECT id, action, user_id, identifier, details, ip_address, user_agent, request_uri, created_at
                  FROM action_logs'
             . ($action === null ? '' : ' WHERE action = :action')
             . ' ORDER BY id DESC LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        if ($action !== null) {
            $stmt->bindValue(':action', $action);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function clear(): int
    {
        return $this->db->exec('DELETE FROM action_logs');
    }
}
