<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;

// The drills page and its Check result button -- supplied, leave this file alone.
//
// Each drill's Check result button runs that drill's test in tests/Feature/Drills/ --
// the same file `docker compose run --rm test` runs. Every check builds its own in-memory
// database from the migrations and sends requests to your code, the way an attacker would.
// Drill 7 is optional, for pairs who finish.
class WorkshopController extends Controller
{
    public const DRILLS = [
        1 => [
            'group' => 'Who are you?', 'title' => 'Passwords stored as typed', 'open' => '/leak',
            'file' => 'app/Models/User.php (casts) and AuthController::login()',
            'goal' => 'The password column holds a bcrypt hash, never what was typed, and logging in still works.',
            'owasp' => 'A04:2025 Cryptographic Failures',
            'attack' => "Open /leak. It is the users table as anyone with a copy of your database sees it: a stolen backup, a lost laptop, or last week's SQL injection. Every password is right there in plain text. People reuse passwords, so this one table unlocks their email and their bank too.",
            'idea' => "Store something you can check but cannot reverse. A hash function (bcrypt here) turns 'password' into a 60-character string; the same input always checks against it, but nothing turns the hash back into the password. Bcrypt adds a random salt per row, so Alex and Priya, who both chose 'password', get different hashes, and it is slow on purpose (12 rounds), so guessing billions is not practical. You never compare passwords; you compare hashes, with Hash::check().",
            'hint' => "1. In app/Models/User.php, inside casts():\n       'password' => 'hashed',\n\n2. In AuthController::login(), change the comparison\n       if (\$credentials['password'] !== \$user->password) {\n   to\n       if (! Hash::check(\$credentials['password'], \$user->password)) {\n\n3. Write the seeded rows again, now hashed (second terminal):\n       docker compose exec app php artisan migrate:fresh --seed\n   Reload /leak: two different hashes for the same password.",
        ],
        2 => [
            'group' => 'Who are you?', 'title' => 'Two error messages tell a stranger who has an account', 'open' => '/login',
            'file' => 'app/Http/Controllers/AuthController.php · login()',
            'goal' => 'A wrong password and an unknown email get exactly the same message.',
            'owasp' => 'A07:2025 Authentication Failures (account enumeration)',
            'attack' => "Open /login. Log in as nobody@example.edu with any password: \"No account uses that email address.\" Now alex@example.edu with a wrong password: \"Wrong password for that account.\" The form just told a stranger that alex@example.edu is a real account here -- the first half of guessing a login, and a fine list to send phishing email to.",
            'idea' => "An attacker needs a username and a password. Different messages give away the username for free. One message for both cases -- the OWASP Authentication Cheat Sheet suggests \"These credentials do not match our records\" -- leaves them guessing both. Laravel's own Auth::attempt() does the lookup and the check in one step, which is why it can only ever say one thing.",
            'hint' => "Replace the two if blocks in login() with one:\n\n    if (! \$user || ! Hash::check(\$credentials['password'], \$user->password)) {\n        return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');\n    }\n\n(Or the Laravel way, which is the same thing: if (! Auth::attempt(\$credentials)) { ... } -- then the User::where() line and Auth::login(\$user) go, because attempt() does both.)",
        ],
        3 => [
            'group' => 'Who are you?', 'title' => 'Unlimited guesses', 'open' => '/login',
            'file' => 'app/Providers/AppServiceProvider.php and routes/web.php',
            'goal' => 'Five wrong passwords a minute for one email are answered; the sixth gets 429 Too Many Requests.',
            'owasp' => 'A07:2025 Authentication Failures (automated attacks)',
            'attack' => "In a second terminal, in inclass/wk12/laravel:\n    docker compose exec app php artisan attack guess\nA script fetches the login page like a browser would (cookie and CSRF token included), then tries thirty common passwords on priya@example.edu, one after another. Nothing counts the tries, so it finds hers. The CSRF token did not stop it: a script can read your own form.",
            'idea' => "Last week's drill 7 again, on the route where it matters most. throttle:login is a middleware with an argument: it counts POST /login requests per key in the cache and answers 429 with a Retry-After header once the count is over, before your controller runs. The key is the email plus the address, so one attacker cannot lock everyone out, and a slow attacker gets five guesses a minute instead of thousands.",
            'hint' => "1. In app/Providers/AppServiceProvider.php, boot(), un-comment the limit:\n       RateLimiter::for('login', fn (Request \$request) => Limit::perMinute(5)\n           ->by(strtolower((string) \$request->input('email')).'|'.\$request->ip()));\n\n2. In routes/web.php, on the POST /login route:\n       Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');\n\nRun the attack again: the sixth try gets 429 and the script gives up.",
        ],
        4 => [
            'group' => 'What may you do?', 'title' => 'A route outside the login', 'open' => '/todos',
            'file' => 'routes/web.php',
            'goal' => 'A visitor who is not logged in cannot delete a to-do: the request is sent to /login and the row stays.',
            'owasp' => 'A01:2025 Broken Access Control (missing authentication)',
            'attack' => "Log out. You see no Delete button anywhere -- the pages only show it to someone logged in. Now, in a second terminal:\n    docker compose exec app php artisan attack delete 2\nWith no login at all, the script deletes Alex's to-do 2. The button was hidden; the route was not protected. (To get the to-do back: docker compose exec app php artisan migrate:fresh --seed)",
            'idea' => "Hiding a button is not access control: the attacker does not use your page, they send the request. The 'auth' middleware is the check, and it runs on the server before the controller. A route outside the Route::middleware('auth') group has no check at all. Your team project's check script asks the same question: POST /api/<resource> without a cookie must be 401.",
            'hint' => "In routes/web.php, move the last line,\n    Route::delete('/todos/{id}', [TodoController::class, 'destroy'])->whereNumber('id')->name('todos.destroy');\ninside the Route::middleware('auth')->group(function () { ... }); block, next to the other to-do routes.\n\nCheck which routes have 'auth' (second terminal):\n    docker compose exec app php artisan route:list -v",
        ],
        5 => [
            'group' => 'What may you do?', 'title' => 'Register yourself as an admin', 'open' => '/register',
            'file' => 'AuthController::register() and app/Models/User.php (#[Fillable])',
            'goal' => 'A role sent with the registration form is ignored: every new account is an ordinary user.',
            'owasp' => 'A01:2025 Broken Access Control (mass assignment)',
            'attack' => "Two ways. In the browser: open /register, open the developer tools, right-click the <form> and choose Edit as HTML, add <input name=\"role\" value=\"admin\">, register. Or in a second terminal:\n    docker compose exec app php artisan attack admin\nEither way the new account has an Admin link and can open /admin -- every user's list. The form never had a role field; the server took it anyway.",
            'idea' => "User::create(\$request->all()) copies every field the browser sent into the new row, as long as the model's #[Fillable] list allows the column -- and 'role' was on the list. Two fixes, each enough on its own and better together: create the user from the validated fields only (validate() returns just the fields it has rules for), and take 'role' off the allow-list so no array can ever set it.",
            'hint' => "1. In AuthController::register(), keep what validate() returns and create from that:\n       \$fields = \$request->validate([ ...the same rules... ]);\n       \$user = User::create(\$fields);\n\n2. In app/Models/User.php:\n       #[Fillable(['name', 'email', 'password'])]",
        ],
        6 => [
            'group' => 'What may you do?', 'title' => "Someone else's to-do, by its number", 'open' => '/todos/2',
            'file' => 'app/Http/Controllers/TodoController.php · findTodo()',
            'goal' => "A logged-in user who asks for another user's to-do gets 404, for show, toggle and delete.",
            'owasp' => 'A01:2025 Broken Access Control (insecure direct object reference)',
            'attack' => "Log in as priya@example.edu (password: password). Her list has to-dos 5 and 6. Now type /todos/2 into the address bar: Alex's \"Email the TA\", with his note about his midterm grade. Press Mark done, or Delete. You are logged in -- the 'auth' middleware is happy -- but nothing asked whose to-do it is.",
            'idea' => "Logged in answers \"who are you?\"; it does not answer \"may you have this one?\". Todo::findOrFail(\$id) searches every row in the table. \$request->user()->todos()->findOrFail(\$id) adds where user_id = <you> to the query, so another user's to-do is simply not found. 404, not 403, on purpose: a private list should not confirm that to-do 2 exists. (Your team project is different: reviews are public, so a policy answers 403 to the wrong user.)",
            'hint' => "In TodoController::findTodo():\n    return \$request->user()->todos()->findOrFail(\$id);\n\nOne line, and show(), toggle() and destroy() are all fixed, because all three look the to-do up here.",
        ],
        7 => [
            'group' => 'If you finish', 'title' => 'The search box forgets whose list it is', 'open' => '/todos?q=e',
            'file' => 'app/Http/Controllers/TodoController.php · index()',
            'goal' => "A search finds only your own to-dos.",
            'owasp' => 'A01:2025 Broken Access Control',
            'attack' => "Logged in as Priya, search for the letter e. Her two to-dos are there -- and so are Alex's four. The list was scoped to the login; the search, added later, starts from Todo:: and searches everyone's.",
            'idea' => "Drill 6's idea in a second place. Every query that reads to-dos must start from the user, and it is easy to forget one. That is why apps with many users' data in one table (multi-tenant apps) often add the user_id condition to every query automatically -- a global scope. Homework 10 asks for exactly this list, built by you.",
            'hint' => "In index(), start the search from the user, like the list:\n    \$todos = \$request->user()->todos()->where('title', 'like', \"%\$q%\")->orderBy('title')->get();",
        ],
    ];

    // Laravel's dev tools print test results as JSON when they think an AI coding agent is
    // running them. A person is reading this page, so the check always asks for the ordinary
    // output. false removes a variable for that one run.
    private const NO_AGENT = [
        'AI_AGENT' => false, 'CLAUDECODE' => false, 'CLAUDE_CODE' => false, 'CLAUDE_CODE_IS_COWORK' => false,
        'CURSOR_AGENT' => false, 'CODEX_CI' => false, 'CODEX_SANDBOX' => false, 'CODEX_THREAD_ID' => false,
        'ANTIGRAVITY_AGENT' => false, 'AUGMENT_AGENT' => false, 'KIRO_AGENT_PATH' => false, 'PI_CODING_AGENT' => false,
    ];

    public function index()
    {
        return view('workshop.index', ['drills' => self::DRILLS]);
    }

    // Drill 1's attack: the users table, as someone with a copy of the database sees it.
    // Supplied for the lab; a real app would never have this page.
    public function leak()
    {
        return view('workshop.leak', ['users' => DB::table('users')->orderBy('id')->get()]);
    }

    public function check(int $number)
    {
        abort_unless(isset(self::DRILLS[$number]), 404);

        $result = Process::path(base_path())
            ->env(['APP_ENV' => 'testing', 'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array',
                   'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] + self::NO_AGENT)
            ->timeout(60)
            ->run([$this->php(), 'vendor/bin/phpunit', '--colors=never', '--filter', "Drill{$number}Test"]);

        return response()->json([
            'passed' => $result->successful(),
            'saw' => $result->successful() ? '' : $this->firstFailure($result->output().$result->errorOutput()),
        ]);
    }

    private function php(): string
    {
        $found = \Illuminate\Support\php_binary();
        if ($found !== 'php') {
            return $found;
        }
        $beside = dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'php';

        return PHP_BINARY !== '' && is_executable($beside) ? $beside : 'php';
    }

    private function firstFailure(string $output): string
    {
        $root = preg_quote(base_path().DIRECTORY_SEPARATOR, '/');
        $text = preg_match('/^1\) .+?\R(.+?)\R'.$root.'/ms', $output, $m) ? $m[1] : substr($output, -800);
        $text = preg_replace("/that '.{20,}?'(?: \\[[^\\]]*\\]\\(length: \\d+\\))? ((?:does not )?contain)/s", 'that the page $1', $text);
        $text = preg_replace('/ \[[A-Z0-9-]+\]\(length: \d+\)/', '', $text);

        return trim(mb_strimwidth(trim($text), 0, 600, '…'));
    }
}
