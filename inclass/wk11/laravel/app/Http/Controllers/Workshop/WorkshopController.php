<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Process;

// The drills page and its Check result button -- supplied, leave this file alone.
//
// Each drill's Check result button runs that drill's test in tests/Feature/Drills/ --
// the same file `docker compose run --rm test` runs. The checks for drills 1, 2 and 6 use
// an in-memory database and the live app; the checks for drills 3, 4 and 5 read the files
// you changed (the form, the config, the lockfile), because those fixes are about what is
// written down, not what a request returns. Drill 7 is optional, for pairs who finish.
class WorkshopController extends Controller
{
    public const DRILLS = [
        1 => [
            'group' => 'Input', 'title' => 'SQL injection: a search box that reads the users table', 'open' => '/todos',
            'file' => 'app/Http/Controllers/TodoController.php · index()',
            'goal' => 'The search filters to-dos by title and nothing else -- a crafted search cannot reach other tables.',
            'owasp' => 'A05:2025 Injection',
            'attack' => "Open /todos and type this into the search box:\n' UNION SELECT id, email, 0 FROM users WHERE email LIKE '%\nThe list fills with every user's email. Your search box just read the users table -- the same trick reaches password hashes.",
            'idea' => 'The value you type is pasted into the SQL as if it were SQL. A bound parameter (?) sends the value separately, so the database treats it only as text to compare, never as a command. This is the same prepare()/? you used in week 3, and what every Eloquent where() does for you.',
            'hint' => "\$todos = Todo::where('title', 'like', \"%\$q%\")->orderBy('title')->get();\n\n// or, still raw but bound:\n// DB::select('select id, title, done from todos where title like ? order by title', [\"%\$q%\"]);",
        ],
        2 => [
            'group' => 'Input', 'title' => 'XSS: a note that runs in everyone\'s browser', 'open' => '/todos/3',
            'file' => 'resources/views/todos/show.blade.php',
            'goal' => 'A note is shown as text. A <script> tag in a note appears on the page; it does not run.',
            'owasp' => 'A05:2025 Injection (Cross-Site Scripting)',
            'attack' => "Open /todos/3. The 'Pantry potluck' note holds <script>alert(document.cookie)</script>, typed by whoever last edited the shared list. The starter page runs it: the alert is their script, in your browser, with your cookies.",
            'idea' => '{!! !!} prints the value as raw HTML, so a <script> becomes a real script. {{ }} escapes it first: < becomes &lt;, and the browser shows the characters instead of running them. Blade escapes by default; the bug is asking it not to.',
            'hint' => "Change\n    {!! \$todo->notes !!}\nto\n    {{ \$todo->notes }}",
        ],
        3 => [
            'group' => 'Requests', 'title' => 'CSRF: another site adds to-dos to your list', 'open' => '/attack',
            'file' => 'resources/views/todos/index.blade.php and bootstrap/app.php',
            'goal' => 'The add-to-do form carries a CSRF token, and the route is no longer excluded from the check.',
            'owasp' => 'A01:2025 Broken Access Control (CSRF)',
            'attack' => "Open the Attack demo at http://127.0.0.1:8000/attack (127.0.0.1, not localhost: to your browser that is a different site, which is the point). Without you clicking anything, it posts to /todos on the app and a new to-do appears on your list. The server accepted a POST that no page of its own sent.",
            'idea' => "A hidden, per-session token in the form is something an attacker's page cannot read (the browser keeps it from other origins) and so cannot copy. @csrf adds it; Laravel checks it on every POST unless you switch the check off. The starter switched it off for /todos in bootstrap/app.php. After the fix the same page gets 419 Page Expired.",
            'hint' => "In resources/views/todos/index.blade.php, add @csrf as the first line inside the <form>.\n\nIn bootstrap/app.php, delete the \$middleware->validateCsrfTokens(except: ['todos']) line so the check runs again.",
        ],
        4 => [
            'group' => 'Requests', 'title' => 'A secret committed in code', 'open' => '/settings',
            'file' => 'config/pantry.php and .env.example',
            'goal' => 'The API key is read from the environment, not written in a file that git tracks.',
            'owasp' => 'A02:2025 Security Misconfiguration',
            'attack' => "Open config/pantry.php. The API key is right there, in a file in git. Everyone with the repository has the key -- and deleting it later does not help, because git keeps the history. /settings shows the app still reading it.",
            'idea' => 'Secrets go in .env, which is listed in .gitignore and never committed. Code reads them with env(), and .env.example records the NAME of each secret (with a placeholder) so teammates know what to set. The key lives on each machine, not in the repository.',
            'hint' => "In config/pantry.php:\n    'api_key' => env('PANTRY_API_KEY'),\n\nIn .env.example (committed) and .env (not committed), add:\n    PANTRY_API_KEY=pantry_demo_key_not_real_0000",
        ],
        5 => [
            'group' => 'The platform', 'title' => 'A dependency with a published advisory', 'open' => '/settings',
            'file' => 'composer.json / composer.lock (you run a command)',
            'goal' => 'The app no longer depends on a package version with a known security advisory.',
            'owasp' => 'A03:2025 Software Supply Chain Failures',
            'attack' => "In a second terminal:\n    docker compose exec app composer audit\nThe app depends on firebase/php-jwt 5.5.1, which has a published advisory (one rated critical). You did not write that code, but it ships in your app.",
            'idea' => 'Your app is mostly other people\'s code. composer audit (and npm audit for JavaScript) checks every installed version against a public list of advisories. When one is flagged, you update past it or remove the package. Here php-jwt is not even used, so remove it.',
            'hint' => "This package is unused, so remove it. In a second terminal, on your machine (not docker compose exec -- that would change only the container's copy):\n    docker run --rm -v \"\$PWD\":/app -w /app composer:2 composer remove firebase/php-jwt --no-install --no-scripts\n\n(If a package were in use, you would update instead: composer update vendor/name.)\nChanging composer.lock rebuilds the image under docker compose watch -- wait for Rebuilding to finish, then check.",
        ],
        6 => [
            'group' => 'The platform', 'title' => 'Security headers', 'open' => '/',
            'file' => 'app/Http/Middleware/SecurityHeaders.php (you generate it), routes/web.php, then bootstrap/app.php',
            'goal' => 'Every response carries the four headers that tell the browser what to refuse.',
            'owasp' => 'A02:2025 Security Misconfiguration',
            'attack' => "The app sends no security headers. A browser is left to guess: it will render the page inside another site's <iframe> (clickjacking), run scripts from anywhere, and try to sniff a file's type. Each missing header is a default that favours the attacker.",
            'idea' => "Four headers, four refusals: X-Content-Type-Options stops MIME sniffing, X-Frame-Options stops framing, Content-Security-Policy limits where scripts may come from, Strict-Transport-Security forces HTTPS. These are the four MDN guides you read. A middleware sets them on every response.",
            'hint' => "Four steps, as on the Laravel Middleware page (laravel.com/framework/docs/13.x/middleware).\n\nSTEP 1 - Generate the class. In a second terminal, in inclass/wk11/laravel:\n    docker compose run --rm test php artisan make:middleware SecurityHeaders\nIt prints \"Middleware [app/Http/Middleware/SecurityHeaders.php] created successfully\". Open that file: its handle() method is one line, return \$next(\$request);\n\nSTEP 2 - Set the headers. Replace that one line with these seven, so the response is kept, changed, then returned:\n    \$response = \$next(\$request);\n\n    \$response->headers->set('X-Content-Type-Options', 'nosniff');\n    \$response->headers->set('X-Frame-Options', 'DENY');\n    \$response->headers->set('Content-Security-Policy', \"default-src 'self'\");\n    \$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');\n\n    return \$response;\nSave (the watch terminal prints Syncing). Nothing changes yet: the class exists but nothing runs it.\n\nSTEP 3 - One route. In routes/web.php, find the todos.index line and chain ->middleware(...) onto it:\n    Route::get('/todos', [TodoController::class, 'index'])\n        ->middleware(\\App\\Http\\Middleware\\SecurityHeaders::class)\n        ->name('todos.index');\nSave, then in the second terminal:\n    curl -si http://localhost:8000/todos | grep -i -E '^(x-content-type-options|x-frame-options|content-security-policy|strict-transport-security)'\n    curl -si http://localhost:8000/ | grep -i -E '^(x-content-type-options|x-frame-options|content-security-policy|strict-transport-security)'\nThe first prints four header lines; the second prints nothing. Press Check result: it fails, and says the headers are on /todos only.\n\nSTEP 4 - Every route. Undo step 3 (put the todos.index line back as it was, with no ->middleware). Then in bootstrap/app.php, inside withMiddleware, replace the commented DRILL 6 line with:\n    \$middleware->append(\\App\\Http\\Middleware\\SecurityHeaders::class);\nSave. Both curl commands now print the four lines, and so does a URL that does not exist. Check result passes.",
        ],
        7 => [
            'group' => 'If you finish', 'title' => 'Rate limiting: a script that never gets told no', 'open' => '/todos?q=rice',
            'file' => 'app/Providers/AppServiceProvider.php and routes/web.php',
            'goal' => 'Twenty searches a minute from one address are answered; the twenty-first gets 429 Too Many Requests.',
            'owasp' => 'A07:2025 Authentication Failures (no limit on automated guessing)',
            'attack' => "In a second terminal.\nMac or Linux:\n    for i in \$(seq 1 25); do curl -s -o /dev/null -w '%{http_code} ' 'http://localhost:8000/todos?q=guess'; done; echo\nWindows PowerShell (curl.exe, with the .exe; plain curl is a different command there):\n    1..25 | ForEach-Object { curl.exe -s -o NUL -w '%{http_code} ' 'http://localhost:8000/todos?q=guess' }; \"\"\nTwenty-five 200s. A script can search as fast as it likes, and nothing counts. Point the same loop at a login form and it is a password guesser (week 12).",
            'idea' => "A limit is a middleware with an argument: throttle:search means Laravel's ThrottleRequests class, using the limit you named 'search'. It counts requests per key (here the address) in the cache and answers 429 with a Retry-After header once the count is over, before your controller runs. Week 12 puts throttle:login on the login route, keyed on email and address.",
            'hint' => "In app/Providers/AppServiceProvider.php, inside boot():\n    RateLimiter::for('search', fn (Request \$request) => Limit::perMinute(20)->by(\$request->ip()));\n(the use lines for Limit, Request and RateLimiter are already at the top).\n\nIn routes/web.php, on the todos.index route:\n    ->middleware('throttle:search')\n\nShorthand that skips the naming: ->middleware('throttle:20,1').",
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
        // Drill 3's attack page must be opened from a different origin than the app, or the
        // browser tells Laravel the request is same-origin and it is let through without a token.
        $drills = self::DRILLS;
        $drills[3]['open'] = 'http://127.0.0.1:'.request()->getPort().'/attack';
        $drills[3]['attack'] = str_replace('127.0.0.1:8000', '127.0.0.1:'.request()->getPort(), $drills[3]['attack']);
        $drills[7]['attack'] = str_replace('localhost:8000', 'localhost:'.request()->getPort(), $drills[7]['attack']);
        $drills[6]['hint'] = str_replace('localhost:8000', 'localhost:'.request()->getPort(), $drills[6]['hint']);

        return view('workshop.index', ['drills' => $drills]);
    }

    // A small "settings" page, so drills 4 and 5 have somewhere to point. Supplied.
    public function settings()
    {
        return view('workshop.settings', [
            'key' => config('pantry.api_key'),
            'hasJwt' => class_exists(\Firebase\JWT\JWT::class),
        ]);
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
