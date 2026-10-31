<?php
declare(strict_types=1);

final class UserController extends Controller
{
    public function dashboard(): void
    {
        $session = $this->requireLogin();
        if ($session['role'] === 'admin') {
            $this->redirect('admin');
        }

        $profile = (new User())->find($session['id']);
        if ($profile === null) { // account removed while logged in
            Session::logout();
            Session::start();
            $this->redirect('login');
        }

        $this->render('user/dashboard', ['title' => 'My account', 'profile' => $profile]);
    }
}
