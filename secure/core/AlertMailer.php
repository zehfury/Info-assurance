<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once APP_PATH . '/lib/PHPMailer/Exception.php';
require_once APP_PATH . '/lib/PHPMailer/PHPMailer.php';
require_once APP_PATH . '/lib/PHPMailer/SMTP.php';

/**
 * Emails the admin when SQL injection is detected.
 *
 * Throttled per source IP: at most one email per `mail.throttle_minutes`. The next email after a quiet
 * window reports how many attempts were suppressed in between, so nothing is lost, it's just batched.
 * Failures are written to the PHP error log and never surface to the visitor.
 */
final class AlertMailer
{
    /** Throttle state lives outside the web root so it can't be downloaded. */
    private static function stateFile(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'sqli_lab_alert_state.json';
    }

    /**
     * @param array<int, array{id: int, field: string, payload: string, patterns: string[]}> $hits  rows just logged
     */
    public static function notify(SqliLog $logs, string $endpoint, array $hits, string $ip, ?string $userAgent, ?string $requestUri): void
    {
        $cfg = config('mail', []);
        if ($hits === [] || empty($cfg['enabled'])) {
            return;
        }
        if (trim((string) ($cfg['app_password'] ?? '')) === '') {
            error_log('SQLi alert email skipped: mail.app_password is empty in secure/config/config.php');
            return;
        }

        $windowSeconds = max(1, (int) ($cfg['throttle_minutes'] ?? 10)) * 60;
        $currentMinId  = min(array_column($hits, 'id'));

        // Reserve this IP's slot under an exclusive lock so parallel requests don't double-send.
        $fh = @fopen(self::stateFile(), 'c+');
        if ($fh === false || !flock($fh, LOCK_EX)) {
            error_log('SQLi alert email skipped: cannot open throttle state ' . self::stateFile());
            return;
        }
        $state = json_decode((string) stream_get_contents($fh), true);
        $state = is_array($state) ? $state : [];
        $now   = time();

        foreach ($state as $k => $entry) { // forget IPs that have been quiet for a day
            if (!is_array($entry) || $now - (int) ($entry['sent_at'] ?? 0) > 86400) {
                unset($state[$k]);
            }
        }

        $prev = $state[$ip] ?? null;
        if ($prev !== null && $now - (int) $prev['sent_at'] < $windowSeconds) {
            flock($fh, LOCK_UN);
            fclose($fh);
            return; // still inside this IP's quiet window; the next alert will include these rows
        }

        $state[$ip] = ['sent_at' => $now, 'last_id' => max(array_column($hits, 'id'))];
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, (string) json_encode($state));
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);

        $suppressed = $prev === null ? 0 : $logs->countFromIpBetween($ip, (int) $prev['last_id'], $currentMinId);

        try {
            self::send($cfg, $endpoint, $hits, $ip, $userAgent, $requestUri, $suppressed, $windowSeconds);
        } catch (Throwable $e) {
            error_log('SQLi alert email failed: ' . $e->getMessage());
        }
    }

    private static function send(array $cfg, string $endpoint, array $hits, string $ip, ?string $ua, ?string $uri, int $suppressed, int $window): void
    {
        $h        = static fn(?string $s): string => htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $label    = SqliLog::ENDPOINTS[$endpoint] ?? $endpoint;
        $time     = date('Y-m-d H:i:s T');
        $logsUrl  = (string) config('app_url', '') . '?' . http_build_query(['r' => 'admin/logs']);
        $minutes  = intdiv($window, 60);
        $outcome = $endpoint === 'vulnerable_login'
            ? 'The attempt was logged. The vulnerable lab allows SQL injection; review the lab result to determine whether login was bypassed.'
            : 'The attempt was blocked (prepared statements) and logged.';

        $rowsHtml = '';
        $rowsText = '';
        foreach ($hits as $hit) {
            $patterns  = implode(', ', $hit['patterns']);
            $rowsHtml .= '<tr><td style="padding:6px 10px;border:1px solid #e5e7eb">' . $h($hit['field']) . '</td>'
                       . '<td style="padding:6px 10px;border:1px solid #e5e7eb;font-family:monospace;color:#b91c1c;word-break:break-all">' . $h(mb_substr($hit['payload'], 0, 500)) . '</td>'
                       . '<td style="padding:6px 10px;border:1px solid #e5e7eb">' . $h($patterns) . '</td></tr>';
            $rowsText .= "  - {$hit['field']}: " . mb_substr($hit['payload'], 0, 500) . "\n    detected as: $patterns\n";
        }

        $note = $suppressed > 0
            ? "$suppressed more attempt(s) from this IP were logged since the previous alert."
            : '';

        $html = '<div style="font-family:Arial,sans-serif;font-size:14px;color:#111">'
              . '<h2 style="color:#b91c1c;margin:0 0 12px">SQL injection attempt detected</h2>'
              . '<table style="border-collapse:collapse;margin-bottom:14px">'
              . '<tr><td style="padding:3px 12px 3px 0;color:#555">Time</td><td>' . $h($time) . '</td></tr>'
              . '<tr><td style="padding:3px 12px 3px 0;color:#555">Entry point</td><td>' . $h($label) . '</td></tr>'
              . '<tr><td style="padding:3px 12px 3px 0;color:#555">Source IP</td><td style="font-family:monospace">' . $h($ip) . '</td></tr>'
              . '<tr><td style="padding:3px 12px 3px 0;color:#555">User agent</td><td>' . $h($ua) . '</td></tr>'
              . '<tr><td style="padding:3px 12px 3px 0;color:#555">Request</td><td style="font-family:monospace">' . $h($uri) . '</td></tr>'
              . '</table>'
              . '<table style="border-collapse:collapse"><tr style="background:#f3f4f6">'
              . '<th style="padding:6px 10px;border:1px solid #e5e7eb;text-align:left">Field</th>'
              . '<th style="padding:6px 10px;border:1px solid #e5e7eb;text-align:left">Payload</th>'
              . '<th style="padding:6px 10px;border:1px solid #e5e7eb;text-align:left">Detected as</th></tr>'
              . $rowsHtml . '</table>'
              . ($note !== '' ? '<p style="color:#92400e">' . $h($note) . '</p>' : '')
              . '<p>' . $h($outcome) . ' '
              . '<a href="' . $h($logsUrl) . '">Open SQLi logs</a></p>'
              . '<p style="color:#6b7280;font-size:12px">Further alerts from this IP are paused for ' . $minutes . ' minutes; attempts are still logged.</p>'
              . '</div>';

        $text = "SQL injection attempt detected\n\n"
              . "Time:        $time\nEntry point: $label\nSource IP:   $ip\nUser agent:  $ua\nRequest:     $uri\n\n"
              . "Payloads:\n$rowsText\n"
              . ($note !== '' ? "$note\n\n" : '')
              . "$outcome SQLi logs: $logsUrl\n"
              . "Further alerts from this IP are paused for $minutes minutes.\n";

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = (string) $cfg['host'];
        $mail->Port       = (int) $cfg['port'];
        $mail->SMTPAuth   = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username   = (string) $cfg['username'];
        $mail->Password   = str_replace(' ', '', (string) $cfg['app_password']); // Google shows it as "abcd efgh ..."
        $mail->Timeout    = (int) ($cfg['timeout'] ?? 10);
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;

        $mail->setFrom((string) $cfg['from'], (string) ($cfg['from_name'] ?? 'SQLi Lab Alerts'));
        $mail->addAddress((string) $cfg['to']);
        $mail->Subject = "[SQLi Lab] Injection attempt on $label from $ip";
        $mail->isHTML(true);
        $mail->Body    = $html;
        $mail->AltBody = $text;
        $mail->send();
    }
}
