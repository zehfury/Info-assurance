// script.js — UI helper for the SQLi Training Lab (copy-to-clipboard on the SQL code block).
// No SQL logic lives here; this only affects presentation.

document.addEventListener('click', function (event) {
    var btn = event.target.closest('.copy-btn');
    if (!btn) return;

    var targetId = btn.getAttribute('data-copy-target');
    var target = targetId ? document.getElementById(targetId) : null;
    if (!target) return;

    var text = target.textContent;

    var showCopied = function () {
        var original = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('copied');
        setTimeout(function () {
            btn.textContent = original;
            btn.classList.remove('copied');
        }, 1500);
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(showCopied);
    } else {
        var textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showCopied();
    }
});

// Lesson: add a "Try it" button to each payload row that runs it in the search box.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('table.sqli-payloads').forEach(function (table) {
        var base = table.getAttribute('data-try-url') || 'reps.php';
        table.querySelectorAll('tr').forEach(function (row, i) {
            if (i === 0) {
                var th = document.createElement('th');
                row.appendChild(th);
                return;
            }
            var code = row.querySelector('td code');
            var cell = document.createElement('td');
            if (code) {
                var link = document.createElement('a');
                link.className = 'sqli-try';
                link.href = base + '?id=' + encodeURIComponent(code.textContent.trim());
                link.textContent = 'Try it';
                cell.appendChild(link);
            }
            row.appendChild(cell);
        });
    });
});
