<?php
declare(strict_types=1);

final class SqliLog extends Model
{
    public const ENDPOINTS = ['login' => 'User login', 'admin_login' => 'Admin login', 'register' => 'Registration'];

    /** @param string[] $patterns */
    public function record(
        string $endpoint,
        string $field,
        string $payload,
        array $patterns,
        string $ip,
        ?string $userAgent,
        ?string $requestUri
    ): void {
        $stmt = $this->db->prepare(
            'INSERT INTO sqli_logs (endpoint, field_name, payload, matched_patterns, ip_address, user_agent, request_uri)
             VALUES (:endpoint, :field, :payload, :patterns, :ip, :ua, :uri)'
        );
        $stmt->execute([
            ':endpoint' => $endpoint,
            ':field'    => mb_substr($field, 0, 50),
            ':payload'  => mb_substr($payload, 0, 1000),
            ':patterns' => mb_substr(implode(', ', $patterns), 0, 255),
            ':ip'       => mb_substr($ip, 0, 45),
            ':ua'       => $userAgent === null ? null : mb_substr($userAgent, 0, 255),
            ':uri'      => $requestUri === null ? null : mb_substr($requestUri, 0, 255),
        ]);
    }

    public function total(?string $endpoint = null): int
    {
        if ($endpoint === null) {
            return (int) $this->db->query('SELECT COUNT(*) FROM sqli_logs')->fetchColumn();
        }
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM sqli_logs WHERE endpoint = :endpoint');
        $stmt->execute([':endpoint' => $endpoint]);
        return (int) $stmt->fetchColumn();
    }

    public function last24Hours(): int
    {
        return (int) $this->db->query(
            'SELECT COUNT(*) FROM sqli_logs WHERE created_at >= (NOW() - INTERVAL 1 DAY)'
        )->fetchColumn();
    }

    public function page(int $limit, int $offset, ?string $endpoint = null): array
    {
        $sql = 'SELECT id, endpoint, field_name, payload, matched_patterns, ip_address, user_agent, created_at
                  FROM sqli_logs'
             . ($endpoint === null ? '' : ' WHERE endpoint = :endpoint')
             . ' ORDER BY id DESC LIMIT :limit OFFSET :offset';

        $stmt = $this->db->prepare($sql);
        if ($endpoint !== null) {
            $stmt->bindValue(':endpoint', $endpoint);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<int, array{ip_address: string, attempts: int}> */
    public function topSources(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT ip_address, COUNT(*) AS attempts
               FROM sqli_logs GROUP BY ip_address ORDER BY attempts DESC LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** @return array<string, int> attempts per endpoint */
    public function countsByEndpoint(): array
    {
        $rows = $this->db->query('SELECT endpoint, COUNT(*) AS n FROM sqli_logs GROUP BY endpoint')->fetchAll();
        return array_column($rows, 'n', 'endpoint');
    }

    public function clear(): int
    {
        return $this->db->exec('DELETE FROM sqli_logs');
    }
}
