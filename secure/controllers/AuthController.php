<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    /** Verified against when the account doesn't exist, so response time doesn't reveal valid usernames. */
    private const DUMMY_HASH = '$2y$10$6J5yXx5Om7jB0qVOQrps6OkEfVAkMXof9umcxl/Hv6lhrfAF0jsv6';

    public function login(): void
    {
        $this->handleLogin('user', 'login', [
            'title'     => 'Secure Login',
            'heading'   => 'Secure Login',
            'subtitle'  => 'Use your username or email and password.',
            'formRoute' => 'login',
            'isAdmin'   => false,
        ]);
    }

    public function adminLogin(): void
    {
        $this->handleLogin('admin', 'admin_login', [
            'title'     => 'Admin log in',
            'heading'   => 'Admin log in',
            'subtitle'  => 'Administrators only. Failed attempts and SQL injection payloads are logged.',
            'formRoute' => 'admin/login',
            'isAdmin'   => true,
        ]);
    }

    public function register(): void
    {
        $this->redirectIfLoggedIn();

        $errors = [];
        $old = ['username' => '', 'first_name' => '', 'last_name' => '', 'email' => ''];

        if ($this->isPost()) {
            $old = [
                'username'   => trim($this->post('username')),
                'first_name' => trim($this->post('first_name')),
                'last_name'  => trim($this->post('last_name')),
                'email'      => trim($this->post('email')),
            ];
            $password = $this->post('password');
            $confirm  = $this->post('confirm_password');

            // Log attack attempts before anything else, regardless of CSRF/validation outcome.
            $this->inspectInput('register', $old + ['password' => $password]);

            if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
                $errors[] = 'Your session expired. Please try again.';
            } else {
                $errors = $this->validateRegistration($old, $password, $confirm);
            }

            if ($errors === []) {
                $users = new User();
                $taken = $users->exists($old['username'], $old['email']);
                if ($taken['username']) {
                    $errors[] = 'That username is already taken.';
                }
                if ($taken['email']) {
                    $errors[] = 'That email is already registered.';
                }
            }

            if ($errors === []) {
                try {
                    $users->create(
                        $old['username'],
                        $old['first_name'],
                        $old['last_name'],
                        $old['email'],
                        password_hash($password, PASSWORD_DEFAULT)
                    );
                    Session::flash('success', 'Account created. You can log in now.');
                    $this->redirect('login');
                } catch (PDOException $e) {
                    if ($e->getCode() !== '23000') { // duplicate key race — anything else is a real error
                        throw $e;
                    }
                    $errors[] = 'That username or email is already registered.';
                }
            }
        }

        $this->render('auth/register', ['title' => 'Register', 'errors' => $errors, 'old' => $old]);
    }

    public function logout(): void
    {
        if ($this->isPost() && Session::verifyCsrf($_POST['csrf'] ?? null)) {
            $wasAdmin = (Session::user()['role'] ?? '') === 'admin';
            Session::logout();
            Session::start(); // fresh session so the flash message can be shown
            Session::flash('success', 'You have been logged out.');
            $this->redirect($wasAdmin ? 'admin/login' : 'login');
        }
        $this->redirect('login');
    }

    private function handleLogin(string $role, string $endpoint, array $view): void
    {
        $this->redirectIfLoggedIn();

        $errors = [];
        $identifier = '';

        if ($this->isPost()) {
            $identifier = trim($this->post('identifier'));
            $password   = $this->post('password');

            $this->inspectInput($endpoint, ['identifier' => $identifier, 'password' => $password]);

            if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
                $errors[] = 'Your session expired. Please try again.';
            } else {
                $account = (new User())->findForLogin($identifier, $role);
                $hash    = $account['password_hash'] ?? self::DUMMY_HASH;
                $valid   = password_verify($password, $hash) && $account !== null;

                if ($valid) {
                    Session::login($account);
                    $this->redirect($role === 'admin' ? 'admin' : 'dashboard');
                }
                $errors[] = 'Invalid username or password.'; // same message for every failure
            }
        }

        $this->render('auth/login', $view + ['errors' => $errors, 'identifier' => $identifier]);
    }

    private function validateRegistration(array $in, string $password, string $confirm): array
    {
        $errors = [];

        if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $in['username'])) {
            $errors[] = 'Username must be 3–50 characters: letters, numbers, dot, dash or underscore.';
        }
        foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $key => $label) {
            if (!preg_match('/^\p{L}[\p{L}\s\'.\-]{0,49}$/u', $in[$key])) {
                $errors[] = "$label must be 1–50 letters (spaces, apostrophes, dots and dashes allowed).";
            }
        }
        if (strlen($in['email']) > 100 || !filter_var($in['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address (max 100 characters).';
        }
        if (strlen($password) < 8 || strlen($password) > 72) {
            $errors[] = 'Password must be 8–72 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        return $errors;
    }

    private function redirectIfLoggedIn(): void
    {
        $user = Session::user();
        if ($user !== null) {
            $this->redirect($user['role'] === 'admin' ? 'admin' : 'dashboard');
        }
    }
}
