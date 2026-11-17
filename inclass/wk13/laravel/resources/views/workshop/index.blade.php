{{-- The drills page -- supplied, leave it alone. --}}
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Week 13 · Deploy drills</title>
    <link rel="stylesheet" href="/workshop.css">
</head>
<body>
<main class="page workshop">
    <p class="kicker">ITWS 2110 · Laravel lab · week 13</p>
    <h1>Ready for strangers: four changes before a deploy</h1>
    <p>The to-do app from weeks 11 and 12, with every fix from those labs in. It works on a
        laptop. This container has been given <strong>production's settings</strong>
        (<code>docker-compose.yml</code>, the <code>environment:</code> block), and four things
        about the app are still a laptop's. Each drill names the file to edit.
        <strong>See it break first</strong>, then make the change, save, and see it work.
        Nothing here is graded: push your copy at the end of class.</p>

    <p class="note">Do 1 to 4 in order, then build the production image (the README's last
        step). 5 and 6 are for anyone who finishes early. Accounts:
        <code>alex@example.edu</code> / <code>password</code>. Commands go in a second terminal,
        in <code>inclass/wk13/laravel</code>, while <code>docker compose watch</code> keeps running
        in the first. Homework 11 makes changes 2, 3 and 4 to your team's project.</p>

    @foreach (collect($drills)->groupBy('group', true) as $group => $items)
        <h2>{{ $group }}</h2>
        @foreach ($items as $number => $drill)
            <section class="drill" id="drill-{{ $number }}">
                <h3>{{ $number }}. {{ $drill['title'] }}</h3>
                <p class="owasp">{{ $drill['tag'] }}</p>
                <p><strong>Goal:</strong> {{ $drill['goal'] }}</p>
                <p class="file">Edit <code>{{ $drill['file'] }}</code></p>
                @if ($drill['open'] !== '')
                    <div class="actions">
                        <a class="open" href="{{ $drill['open'] }}" target="_blank">Open {{ $drill['open'] }}</a>
                    </div>
                @endif
                <details><summary>See it break</summary><pre>{{ $drill['attack'] }}</pre></details>
                <details><summary>Why it matters</summary><p>{{ $drill['idea'] }}</p></details>
                <details><summary>The change</summary><pre>{{ $drill['hint'] }}</pre></details>
            </section>
        @endforeach
    @endforeach

    <p class="foot">Worked answers: the <code>answers/</code> folder.</p>
</main>
</body>
</html>
