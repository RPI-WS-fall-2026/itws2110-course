<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Log in, register, log out -- the session way (Sanctum's "SPA authentication").
 * The React app and the API share one address (Vite forwards /api), so Laravel keeps the
 * login in a session cookie, as Breeze does for Blade pages (10/30), and the browser sends
 * it with every request. No token is ever stored in JavaScript, where an XSS bug could read it.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate(
            ['email' => ['required', 'email'], 'password' => ['required', 'string']],
            ['email.required' => 'The email is required.', 'password.required' => 'The password is required.']
        );

        if (! Auth::attempt($credentials)) {
            // One message for a wrong password and an unknown email, so the form does not
            // tell a stranger which emails have accounts.
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }
        $request->session()->regenerate();   // a new session id after log in: stops session fixation

        return ['data' => $request->user()];
    }

    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'The name is required.',
            'email.unique' => 'That email already has an account.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        // Only name, email and password are used: a `role` in the request is ignored.
        // fresh(): reload from the database, so the reply includes `role`, which the
        // database fills in with its default. Without it the reply had no role at all --
        // caught by web/tests/api/accounts.spec.js, "registering logs you in as an ordinary resident".
        $user = User::create($fields)->fresh();
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['data' => $user], 201);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    public function user(Request $request)
    {
        return ['data' => $request->user()];
    }
}
