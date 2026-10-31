<?php
declare(strict_types=1);

final class User extends Model
{
    /** Account that may log in with the given role (prepared statement). */
    public function findForLogin(string $identifier, string $role): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, first_name, last_name, email, password_hash, role
               FROM users
              WHERE (username = :username OR email = :email)
                AND role = :role
                AND password_hash IS NOT NULL
              LIMIT 1'
        );
        $stmt->execute([
            ':username' => $identifier,
            ':email'    => $identifier,
            ':role'     => $role,
        ]);
        return $stmt->fetch() ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, first_name, last_name, email, role, created_at
               FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** @return array{username: bool, email: bool} */
    public function exists(string $username, string $email): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                EXISTS(SELECT 1 FROM users WHERE username = :username) AS username_taken,
                EXISTS(SELECT 1 FROM users WHERE email = :email)       AS email_taken'
        );
        $stmt->execute([':username' => $username, ':email' => $email]);
        $row = $stmt->fetch();
        return ['username' => (bool) $row['username_taken'], 'email' => (bool) $row['email_taken']];
    }

    public function create(string $username, string $first, string $last, string $email, string $passwordHash): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, first_name, last_name, email, password_hash, role)
             VALUES (:username, :first, :last, :email, :hash, 'user')"
        );
        $stmt->execute([
            ':username' => $username,
            ':first'    => $first,
            ':last'     => $last,
            ':email'    => $email,
            ':hash'     => $passwordHash,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT id, username, first_name, last_name, email, role,
                    (password_hash IS NOT NULL) AS can_login, created_at
               FROM users ORDER BY id'
        )->fetchAll();
    }

    /** @return array{total: int, registered: int, admins: int} */
    public function counts(): array
    {
        $row = $this->db->query(
            "SELECT COUNT(*) AS total,
                    SUM(password_hash IS NOT NULL) AS registered,
                    SUM(role = 'admin') AS admins
               FROM users"
        )->fetch();
        return [
            'total'      => (int) $row['total'],
            'registered' => (int) $row['registered'],
            'admins'     => (int) $row['admins'],
        ];
    }
}
