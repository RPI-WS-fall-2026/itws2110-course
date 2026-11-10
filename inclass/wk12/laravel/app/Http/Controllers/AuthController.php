<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Register, log in, log out. Three drills live in this file:
//   DRILL 1 (passwords)    -> login(), the password comparison (and the User model's casts)
//   DRILL 2 (two messages) -> login(), the two error messages
//   DRILL 5 (role=admin)   -> register(), User::create() (and the User model's allow-list)
// Drill 3's limit goes on the POST /login route, not in here.
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

        $user = User::where('email', $credentials['email'])->first();

        // DRILL 2: two different messages. A stranger who types an email learns whether it
        // has an account here -- and that is the first half of guessing a login. Say one
        // thing for both cases: 'These credentials do not match our records.'
        if (! $user) {
            return back()->withErrors(['email' => 'No account uses that email address.'])->onlyInput('email');
        }

        // DRILL 1: this compares what was typed with what is stored, as plain text -- which
        // only works because the password column holds plain text. Once the User model hashes
        // passwords, compare with   Hash::check($credentials['password'], $user->password)
        // which hashes what was typed the same way and compares the hashes.
        if ($credentials['password'] !== $user->password) {
            return back()->withErrors(['email' => 'Wrong password for that account.'])->onlyInput('email');
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
        $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // DRILL 5: $request->all() is everything the browser sent -- including fields your
        // form does not have. Add <input name="role" value="admin"> in the developer tools
        // and you are an admin. Create the user from the validated fields only:
        //     $fields = $request->validate([...]);   then   User::create($fields);
        $user = User::create($request->all());

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
