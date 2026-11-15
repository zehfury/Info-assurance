<?php
declare(strict_types=1);

final class XssController extends Controller
{
    public function index(): void
    {
        $message = $this->isPost() ? $this->post('message') : (is_string($_GET['message'] ?? null) ? $_GET['message'] : '');
        $this->logAction('xss_lab', null, null, $message === '' ? 'view' : 'reflected input submitted');

        $this->render('xss/index', [
            'title'   => 'Cross-site scripting lab',
            'message' => $message,
        ]);
    }
}
