{{-- The drills page -- supplied, leave it alone. --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Week 12 · Login lab</title>
    <link rel="stylesheet" href="/workshop.css">
</head>
<body>
<main class="page workshop">
    <p class="kicker">ITWS 2110 · Laravel lab · week 12 · optional</p>
    <h1>Logging in: break it, then fix it</h1>
    <p>The to-do app from week 11, with last week's six fixes in and a login added. The login
        has six holes, and a seventh for when you finish. Each drill names the file to edit.
        <strong>Run the attack first</strong> (the Attack panel says how), then make the fix, save,
        and press <strong>Check result</strong>: it runs that drill's test against your files right now.</p>

    <section class="warmup">
        <h2>Warm-up, 5 minutes: the cookie is the login</h2>
        <ol>
            <li><a href="/login">Log in</a> as <code>alex@example.edu</code> / <code>password</code>.</li>
            <li>Open the developer tools: <strong>Application</strong> (Chrome, Edge) or <strong>Storage</strong>
                (Firefox, Safari) &rarr; <strong>Cookies</strong>. Find <code>login-lab-session</code> (Laravel names it
                after <code>APP_NAME</code>, here "Login Lab"). Note its <em>HttpOnly</em> tick.</li>
            <li>Click the <strong>Console</strong> tab of the developer tools (not the Filter box above the
                cookie table). At the <code>&gt;</code> prompt, type <code>document.cookie</code> and press Enter:
                one line in quotes, starting <code>XSRF-TOKEN=eyJ…</code>. Then type
                <code>document.cookie.includes('login-lab-session')</code>: it prints <strong>false</strong>.
                The cookie table showed two cookies; JavaScript sees one. HttpOnly hides the session cookie
                from every script, so an XSS script can't steal it. <code>XSRF-TOKEN</code> is visible on
                purpose: it holds the CSRF token, not your login.</li>
            <li>Delete the cookie and reload <a href="/todos">My to-dos</a>. You are sent to log in: the
                server forgot nothing, but your browser no longer says who you are.</li>
        </ol>
        <p>The server keeps the session (here, a file); the browser keeps only its id. Whoever
            holds the id <em>is</em> you, which is why logging out must throw the server's copy away
            (<code>AuthController::logout()</code> does, and is worth reading).</p>
    </section>

    <p class="note">The checks build their own database each time. The pages use MySQL, which only
        changes when you run <code>docker compose exec app php artisan migrate:fresh --seed</code>
        (after drill 1, and to undo an attack). Three attacks run in a second terminal; the rest are in
        the browser.</p>

    @foreach (collect($drills)->groupBy('group', true) as $group => $items)
        <h2>{{ $group }}</h2>
        @foreach ($items as $number => $drill)
            <section class="drill" id="drill-{{ $number }}">
                <h3>{{ $number }}. {{ $drill['title'] }}</h3>
                <p class="owasp">{{ $drill['owasp'] }}</p>
                <p><strong>Goal:</strong> {{ $drill['goal'] }}</p>
                <p class="file">Edit <code>{{ $drill['file'] }}</code></p>
                <div class="actions">
                    <button type="button" class="check" data-drill="{{ $number }}">Check result</button>
                    <a class="open" href="{{ $drill['open'] }}" target="_blank">Open {{ $drill['open'] }}</a>
                </div>
                <p class="status" role="status" id="status-{{ $number }}">Not checked yet.</p>
                <pre class="saw" id="saw-{{ $number }}" hidden></pre>
                <details><summary>See the attack</summary><pre>{{ $drill['attack'] }}</pre></details>
                <details><summary>Why it works</summary><p>{{ $drill['idea'] }}</p></details>
                <details><summary>The fix</summary><pre>{{ $drill['hint'] }}</pre></details>
            </section>
        @endforeach
    @endforeach

    <p class="foot">Every check at once, in a terminal: <code>docker compose run --rm test</code>.
        Worked answers: the <code>answers/</code> folder.</p>
</main>
{{-- The Check result buttons. A file under public/, not an inline script: after drill 6 the
     Content-Security-Policy header (default-src 'self') allows scripts served by this app and
     blocks inline ones, so an inline version would stop working the moment drill 6 is fixed. --}}
<script src="/workshop.js"></script>
</body>
</html>
