{{-- Supplied -- leave it alone. The form posts email and password to POST /login. --}}
<x-layout title="Log in">
    <h1>Log in</h1>

    <form method="POST" action="/login" class="stack">
        @csrf
        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </label>
        @error('email') <p class="error" role="alert">{{ $message }}</p> @enderror

        <label>Password
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        @error('password') <p class="error" role="alert">{{ $message }}</p> @enderror

        <button type="submit">Log in</button>
    </form>

    <p class="note">Seeded accounts: <code>alex@example.edu</code> and <code>priya@example.edu</code>,
        password <code>password</code>; <code>admin@example.edu</code>, password <code>admin-pass</code>.
        No account? <a href="{{ route('register') }}">Register</a>.</p>
</x-layout>
