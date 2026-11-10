{{-- The shared layout -- supplied, leave it alone. --}}
@props(['title' => 'Login lab'])
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
        <a href="{{ route('todos.index') }}">My to-dos</a>
        @auth
            @if (auth()->user()->isAdmin())
                <a href="{{ route('admin') }}">Admin</a>
            @endif
        @endauth
        <span class="who">
            {{-- @auth / @guest ask the same question as the 'auth' middleware: is anyone logged in? --}}
            @auth
                Logged in as <strong>{{ auth()->user()->name }}</strong>
                @if (auth()->user()->isAdmin()) <span class="tag">admin</span> @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="link">Log out</button>
                </form>
            @endauth
            @guest
                <a href="{{ route('login') }}">Log in</a>
                <a href="{{ route('register') }}">Register</a>
            @endguest
        </span>
    </nav>
    <main class="page">
        {{ $slot }}
    </main>
</body>
</html>
