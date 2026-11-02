<?php
declare(strict_types=1);

final class VulnerableAuthController extends Controller
{
    public function login(): void
    {
        $username = '';
        $errors = [];
        $account = null;

        if ($this->isPost()) {
            unset($_SESSION['lab_user']);
            $username = $this->post('username');
            $password = $this->post('password');
            $this->inspectInput('vulnerable_login', ['username' => $username, 'password' => $password]);

            if ($username === '' || $password === '') {
                $errors[] = 'Enter a username and password.';
            } else {
                $sql = "SELECT id, username, CONCAT(first_name, ' ', last_name) AS full_name, role FROM users WHERE username = '$username' AND fake_password_hash = '$password' LIMIT 1";
                try {
                    $account = Database::connection()->query($sql)->fetch() ?: null;
                    if ($account !== null) {
                        session_regenerate_id(true);
                        $_SESSION['lab_user'] = $account;
                    } else {
                        $errors[] = 'Invalid username or password.';
                    }
                } catch (PDOException $exception) {
                    $errors[] = 'SQL error: ' . $exception->getMessage();
                }
            }
        }

        $this->render('auth/vulnerable_login', [
            'title' => 'Vulnerable Login',
            'username' => $username,
            'errors' => $errors,
            'account' => $account,
        ]);
    }
}
