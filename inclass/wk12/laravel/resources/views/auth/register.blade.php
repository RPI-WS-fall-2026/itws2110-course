{{-- Supplied -- leave it alone. Three fields and a confirmation. There is no "role" field:
     drill 5 is what happens when the server trusts the browser to leave one out. --}}
<x-layout title="Register">
    <h1>Register</h1>

    <form method="POST" action="/register" class="stack">
        @csrf
        <label>Name
            <input type="text" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
        </label>
        @error('name') <p class="error" role="alert">{{ $message }}</p> @enderror

        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
        </label>
        @error('email') <p class="error" role="alert">{{ $message }}</p> @enderror

        <label>Password (8 or more characters)
            <input type="password" name="password" autocomplete="new-password" required>
        </label>
        @error('password') <p class="error" role="alert">{{ $message }}</p> @enderror

        <label>Password again
            <input type="password" name="password_confirmation" autocomplete="new-password" required>
        </label>

        <button type="submit">Register</button>
    </form>
</x-layout>
