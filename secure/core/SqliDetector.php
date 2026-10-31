<?php
declare(strict_types=1);

/**
 * Heuristic SQL-injection *detector* used for logging only.
 * It is NOT the defence — prepared statements are. A missed pattern still can't
 * inject anything; a false positive only creates a log row.
 */
final class SqliDetector
{
    private const PATTERNS = [
        'Boolean tautology'      => '/(?:(?:\bor\b|\band\b|\|\||&&)\s*[\'"(]*\s*\w+\s*[\'")]*\s*(?:=|<>|!=|\blike\b)\s*[\'"(]*\s*\w+)|(?:\b(?:or|and)\b\s*[\'"]+\s*(?:=|\blike\b)\s*[\'"]+)/i',
        'Quote + comment'        => '/[\'"`]\s*(--|#|\/\*)/',
        'UNION SELECT'           => '/\bunion\b(\s+(all|distinct))?\s+select\b/i',
        'SELECT ... FROM'        => '/\bselect\b.+\bfrom\b/i',
        'Stacked query'          => '/;\s*(select|insert|update|delete|drop|alter|create|truncate|exec|grant)\b/i',
        'Schema probing'         => '/\binformation_schema\b|\bmysql\.user\b|\bsys\.\w+/i',
        'Time-based blind'       => '/\b(sleep|benchmark|pg_sleep)\s*\(|\bwaitfor\s+delay\b/i',
        'Server info function'   => '/\b(version|database|user|current_user|schema)\s*\(\s*\)|@@version/i',
        'File access'            => '/\bload_file\s*\(|\binto\s+(out|dump)file\b/i',
        'Error-based extraction' => '/\b(extractvalue|updatexml|group_concat)\s*\(/i',
        'Encoded/char payload'   => '/\bchar\s*\(\s*\d+|\b0x[0-9a-f]{8,}\b/i',
        'Quote probe'            => '/^[\'"`]+$/',
    ];

    private const MAX_LENGTH = 2000;

    /** @return string[] labels of every pattern the input matched */
    public static function detect(string $input): array
    {
        if ($input === '') {
            return [];
        }

        $normalized = self::normalize(substr($input, 0, self::MAX_LENGTH));
        $hits = [];
        foreach (self::PATTERNS as $label => $regex) {
            if (preg_match($regex, $normalized) === 1) {
                $hits[] = $label;
            }
        }
        return $hits;
    }

    /** Undo common evasions: URL encoding, inline comments, odd whitespace. */
    private static function normalize(string $s): string
    {
        $s = urldecode($s);
        $s = preg_replace('/\/\*.*?\*\//s', ' ', $s) ?? $s; // UNION/**/SELECT
        $s = preg_replace('/\s+/', ' ', $s) ?? $s;
        return trim($s);
    }
}
