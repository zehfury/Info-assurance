<?php
declare(strict_types=1);

/**
 * XSS lab: the same message is rendered twice, unsafely (raw) and safely (escaped).
 *
 * The unsafe render is served by frame(), which is loaded in an <iframe>. That response carries its own
 * CSP with the `sandbox` directive, so even when opened directly it runs in an isolated, opaque origin:
 * injected scripts can pop alerts but cannot read the app's session, cookies, or pages, and cannot
 * load anything from the network. The rest of the app keeps its strict CSP.
 */
final class XssController extends Controller
{
    private const MAX_LENGTH = 1000;

    /** Example payloads shown as one-click suggestions and in the lesson table. */
    public const EXAMPLES = [
        [
            'payload' => '<b>Hello</b> <i>world</i>',
            'name'    => 'HTML injection',
            'what'    => 'Plain formatting tags. Harmless, but proves the page treats your text as HTML.',
            'expect'  => 'Unsafe: bold/italic text. Safe: the tags are shown as text.',
        ],
        [
            'payload' => '<h1 style="color:#dc2626">This site has been hacked!</h1>',
            'name'    => 'Defacement',
            'what'    => 'Injects a big heading with styling, changing how the page looks to every visitor.',
            'expect'  => 'Unsafe: a large red headline. Safe: plain text.',
        ],
        [
            'payload' => "<script>alert('XSS! This script ran in your browser')</script>",
            'name'    => 'Script popup',
            'what'    => 'The classic test: a script tag that runs JavaScript as soon as the page loads.',
            'expect'  => 'Unsafe: an alert popup. Safe: the script is shown as text.',
        ],
        [
            'payload' => '<img src="x" onerror="alert(\'No script tag needed!\')">',
            'name'    => 'Event handler',
            'what'    => 'A broken image whose onerror attribute runs JavaScript. Shows why blocking only "<script>" is not enough.',
            'expect'  => 'Unsafe: an alert popup. Safe: the tag is shown as text.',
        ],
        [
            'payload' => '<button onclick="alert(\'You clicked a malicious button\')">Claim your prize</button>',
            'name'    => 'Click bait',
            'what'    => 'A fake button that runs JavaScript when the victim clicks it, combining XSS with social engineering.',
            'expect'  => 'Unsafe: a real button; click it for a popup. Safe: the tag is shown as text.',
        ],
    ];

    public function index(): void
    {
        $message  = $this->message();
        $findings = $message === '' ? [] : self::analyse($message);

        if ($message !== '') { // log only actual renders, not page views
            $user   = Session::user();
            $labels = implode(', ', array_column($findings, 'label'));
            $this->logAction('xss_lab', isset($user['id']) ? (int) $user['id'] : null, $user['username'] ?? null, "$labels: $message");
        }

        // Remember which suggestions have been tried this session, for the "tried 3 of 5" tracker.
        $tried = array_values(array_filter((array) ($_SESSION['xss_tried'] ?? []), 'is_int'));
        foreach (self::EXAMPLES as $i => $ex) {
            if ($message === $ex['payload'] && !in_array($i, $tried, true)) {
                $tried[] = $i;
            }
        }
        $_SESSION['xss_tried'] = $tried;

        $this->render('xss/index', [
            'title'    => 'Cross-site scripting lab',
            'message'  => $message,
            'findings' => $findings,
            'examples' => self::EXAMPLES,
            'tried'    => $tried,
        ]);
    }

    /** Body of the demo frames. ?mode=unsafe prints the message raw; anything else escapes it. */
    public function frame(): void
    {
        $message = $this->message();
        $unsafe  = ($_GET['mode'] ?? '') === 'unsafe';

        header('Content-Type: text/html; charset=UTF-8');
        header('X-Frame-Options: SAMEORIGIN'); // replaces the app-wide DENY so the lab page can embed it
        header("Content-Security-Policy: sandbox allow-scripts allow-modals; default-src 'none'; "
             . "script-src 'unsafe-inline'; style-src 'unsafe-inline'; img-src data:; frame-ancestors 'self'");

        // INTENTIONALLY VULNERABLE when $unsafe — teaching example, contained by the sandbox above.
        $body = $unsafe ? $message : e($message);

        echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><style>'
           . 'body{font:14px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif;margin:0;color:#0f172a;background:#fff}'
           . '.bar{background:#1e293b;color:#fff;padding:8px 14px;font-weight:600;font-size:13px}'
           . '.post{margin:12px 14px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;word-break:break-word}'
           . '.who{font-size:12px;color:#64748b;margin-bottom:4px}'
           . '</style></head><body>'
           . '<div class="bar">Community Guestbook</div>'
           . '<div class="post"><div class="who">Posted by <strong>guest</strong> just now</div>'
           . '<div class="comment">' . $body . '</div></div>'
           . '</body></html>';
    }

    private function message(): string
    {
        $value = $_GET['message'] ?? '';
        return is_string($value) ? mb_substr($value, 0, self::MAX_LENGTH) : '';
    }

    /** @return array<int, array{label: string, detail: string}> what the input would do if printed raw */
    private static function analyse(string $message): array
    {
        $checks = [
            ['/<\s*script\b/i',        'Script tag',            'A <script> tag. The browser runs the code inside it as soon as the page loads.'],
            ['/\bon[a-z]+\s*=/i',      'Event handler',         'An on...= attribute such as onerror or onclick. It runs code when something happens (an image fails, a click), so no <script> tag is needed.'],
            ['/javascript\s*:/i',      'javascript: URL',       'A javascript: link. Clicking it runs code instead of opening a page.'],
            ['/\bstyle\s*=/i',         'Inline style',          'A style attribute. It changes how the page looks, enough to deface it or hide real content.'],
            ['/<\s*[a-z!\/]/i',        'HTML tags',             'Tags in < >. The browser treats them as real parts of the page instead of text.'],
        ];

        $found = [];
        foreach ($checks as [$regex, $label, $detail]) {
            if (preg_match($regex, $message) === 1) {
                $found[] = ['label' => $label, 'detail' => $detail];
            }
        }
        return $found !== [] ? $found : [[
            'label'  => 'Plain text',
            'detail' => 'No HTML found, so both sides look the same. Try one of the suggestions to see the difference.',
        ]];
    }
}
