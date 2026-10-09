{{-- The shared layout. A page uses it as <x-layout title="To-dos"> ... </x-layout>, and
     whatever is between those tags arrives here as $slot.

     Supplied, and finished except for one line: the TODO in the nav (Part 4). --}}
@props(['title' => 'To-do list'])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="/app.css">
</head>
<body>
    <nav class="site-nav" aria-label="Site">
        <a href="/">Home</a>
        {{-- TODO Part 4: link to the list by its route name instead of a typed-in URL --}}
        <a href="/todos">To-dos</a>
    </nav>
    <main class="page">
        {{ $slot }}
    </main>
</body>
</html>
