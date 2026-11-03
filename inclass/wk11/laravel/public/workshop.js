// The Check result buttons on the drills page -- supplied, leave it alone.
//
// This lives in its own file, not inline in the page, on purpose: after drill 6 every
// response carries Content-Security-Policy: default-src 'self', which lets the browser run
// scripts served from this app (a file like this one) and blocks scripts written inline in
// the HTML. An inline version of this file would stop working the moment drill 6 is fixed.
document.querySelectorAll('button.check').forEach(button => {
    button.addEventListener('click', async () => {
        const n = button.dataset.drill;
        const status = document.getElementById('status-' + n);
        const saw = document.getElementById('saw-' + n);
        status.textContent = 'Checking…';
        status.className = 'status';
        saw.hidden = true;
        try {
            const response = await fetch('/drills/' + n + '/check');
            const result = await response.json();
            status.textContent = result.passed ? 'PASS — fixed.' : 'TRY AGAIN — the check says:';
            status.className = 'status ' + (result.passed ? 'pass' : 'fail');
            if (!result.passed) { saw.textContent = result.saw; saw.hidden = false; }
        } catch (error) {
            status.textContent = 'The check itself failed to run: ' + error.message;
            status.className = 'status fail';
        }
    });
});
