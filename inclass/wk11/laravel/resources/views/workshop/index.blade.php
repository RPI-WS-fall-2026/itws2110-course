{{-- The drills page -- supplied, leave it alone. --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Week 11 · Security lab</title>
    <link rel="stylesheet" href="/workshop.css">
</head>
<body>
<main class="page workshop">
    <p class="kicker">ITWS 2110 · Laravel lab · week 11</p>
    <h1>Security: find it, then fix it</h1>
    <p>Six holes in one to-do app. Each drill names the file to edit. <strong>See the attack
        first</strong> (the Attack panel says how), then make the fix, save, and press
        <strong>Check result</strong> — it runs that drill's test against your files right now.
        <strong>Open</strong> shows the page in a new tab.</p>
    <p class="note">The checks build their own database each time, from your migrations. The
        pages you open use MySQL, which only changes when you run
        <code>docker compose exec app php artisan migrate:fresh --seed</code>. Two drills ask you
        to run a command in a second terminal; the rest are edits that sync in on save.</p>

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

    <p class="foot">All six at once, in a terminal: <code>docker compose run --rm test</code>.
        Worked answers: the <code>answers/</code> folder.</p>
</main>
{{-- The Check result buttons. A file under public/, not an inline script: after drill 6 the
     Content-Security-Policy header (default-src 'self') allows scripts served by this app and
     blocks inline ones, so an inline version would stop working the moment drill 6 is fixed. --}}
<script src="/workshop.js"></script>
</body>
</html>
