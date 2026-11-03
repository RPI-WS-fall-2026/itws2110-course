{{-- The shared layout -- supplied, leave it alone. --}}
@props(['title' => 'Security lab'])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="/workshop.css">
</head>
<body>
    <nav class="site-nav">
        <a href="/">Drills</a>
        <a href="{{ route('todos.index') }}">To-dos</a>
        <a href="/settings">Settings</a>
        <a href="/attack">Attack demo</a>
    </nav>
    <main class="page">
        {{ $slot }}
    </main>
</body>
</html>
