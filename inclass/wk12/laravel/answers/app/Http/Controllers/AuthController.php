<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Worked answer. Drills 1, 2 and 5. (Drill 3's limit is on the route, in routes/web.php.)
class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // DRILLS 1 and 2 fixed: one lookup, one hash comparison, one message. A stranger cannot
        // tell an unknown email from a wrong password. Auth::attempt($credentials) is Laravel's
        // name for exactly these lines plus Auth::login().
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        Auth::login($user);
        $request->session()->regenerate();   // a new session id now that you are someone

        return redirect()->intended('/todos');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // DRILL 5 fixed: validate() returns only the fields it has rules for, so a 'role' in
        // the request never reaches create(). (The User model's allow-list refuses it too.)
        $fields = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create($fields);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/todos');
    }

    // Supplied, and correct: forget the user, throw the session away on the server, and
    // issue a new CSRF token, so a copy of the old cookie is worth nothing.
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
