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
