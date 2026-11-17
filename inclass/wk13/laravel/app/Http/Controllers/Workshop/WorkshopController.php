<?php

namespace App\Http\Controllers\Workshop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// The drills page -- supplied, leave this file alone. Each drill's text is below; the page
// shows it with links to open.
class WorkshopController extends Controller
{
    public const DRILLS = [
        1 => [
            'group' => 'A copy that strangers use', 'title' => 'An error page that shows strangers your code', 'open' => '/boom',
            'file' => 'config/app.php',
            'goal' => 'The app asks the environment whether to show error details. With APP_DEBUG=false, a failing page says "Server Error" and nothing else.',
            'tag' => 'Twelve-Factor III. Config · OWASP A02:2025 Security Misconfiguration',
            'attack' => "Open /boom. The page asks the database for a table that does not exist, as a bug would. You get Laravel's debug page: the SQL, the database's host and name, the file and line, every step of the stack. On your laptop that page is a gift.\n\nNow look at docker-compose.yml. The environment: block gives this container production's settings: APP_ENV=production, APP_DEBUG=false. Ask the container (second terminal, in inclass/wk13/laravel):\n    docker compose exec app printenv APP_DEBUG\nIt prints false. The environment said no, and the app showed a stranger everything anyway.",
            'idea' => "config/app.php decides, and it has true typed into it. A value typed into code is the same in every copy of the app: your laptop, a teammate's, Azure. The twelve-factor rule: anything that differs from place to place lives in the environment, and the code asks for it with env(). One image, many places, and nothing is rebuilt to change a setting. env()'s second argument is the answer when the environment says nothing; false means a copy that forgot the setting fails safe.",
            'hint' => "In config/app.php, change the debug line to:\n\n    'debug' => (bool) env('APP_DEBUG', false),\n\nSave, reload /boom: \"500 Server Error\". The details still exist, in the log, where only you can read them (drill 5 reads it, if you get that far).",
        ],
        2 => [
            'group' => 'A copy that strangers use', 'title' => 'Nobody can ask the app whether it is ready', 'open' => '/up',
            'file' => 'bootstrap/app.php · withRouting()',
            'goal' => 'GET /up answers 200 once the app has started.',
            'tag' => "Health checks · Homework 11's check 1",
            'attack' => "Open /up: 404. On Azure, a deploy starts a new copy of your app beside the old one, and Azure has to decide when visitors may be sent to the new copy. It asks the new copy a question. This app has no answer, so a platform cannot tell a copy that started from one that crashed on its first line.",
            'idea' => "A health route answers 200 only if the app booted: PHP started, the configuration loaded, the framework is up. The platform keeps the old copy serving until the new one answers, so a copy that cannot start never meets a visitor. In Homework 11 the last step of your team's pipeline asks /up as well, and marks the deploy failed if nothing answers within two minutes.",
            'hint' => "In bootstrap/app.php, inside withRouting( ... ), add a third line:\n\n    health: '/up',\n\nSave and reload /up: \"Application up\".",
        ],
        3 => [
            'group' => 'Deploys that keep the data', 'title' => 'Every start erases the database', 'open' => '/todos',
            'file' => 'start.sh',
            'goal' => 'Starting the container runs only the migrations the database has not seen (migrate --force). It never drops tables and never seeds.',
            'tag' => 'Twelve-Factor V. Build, release, run',
            'attack' => "1. Log in as alex@example.edu / password and add a to-do: Deploy on Friday.\n\n2. Second terminal, in inclass/wk13/laravel:\n    docker compose restart app\n   A deploy does exactly this: the container starts again. So does waking from zero.\n\n3. When the first terminal says \"Server running\", reload /todos. Your to-do is gone.\n\nOpen start.sh: it is what the container runs at every start. Its first command is migrate:fresh --seed.",
            'idea' => "migrate:fresh drops every table and rebuilds them; --seed then writes the sample rows. Weeks 9 to 12 did that at every start on purpose: a lab wants the same data each time. A deployed app holds real people's rows, and it restarts often. `php artisan migrate` is the production command: it looks in the migrations table, runs only the files that database has not run, and touches nothing else. The MySQL rows were never in the app container; they live in a volume, which is why they can outlive it.",
            'hint' => "In start.sh, change the migrate line to:\n\n    php artisan migrate --force\n\nSave. Watch copies the file in and restarts the container for you. Add \"Deploy on Friday\" again, run  docker compose restart app  again, reload: still there.\n\n--force, because APP_ENV is production: artisan stops to ask \"Are you sure?\", and nobody is there to answer.",
        ],
        4 => [
            'group' => 'Behind the proxy', 'title' => 'The app does not know it is on https', 'open' => '/request',
            'file' => 'bootstrap/app.php · withMiddleware()',
            'goal' => 'A request that reached the proxy over https counts as https in the app: the links and redirects it writes start with https://.',
            'tag' => "TLS termination · Homework 11's checks 8 and 9",
            'attack' => "On Azure the browser speaks https to Azure's edge, a proxy. The edge holds the certificate, decrypts the request, and hands it to your container as plain http, adding headers that say how it really arrived. Send a request the way the edge would (second terminal, one line):\n\n    docker compose exec app curl -s -H \"X-Forwarded-Proto: https\" -H \"X-Forwarded-Host: todo.example.edu\" http://localhost:8000/request\n\n/request prints what the app believes. It says scheme http, host localhost:8000, and a link that starts http://. It ignored both headers, so every redirect it sends (after a login, say) would send the browser to http://.",
            'idea' => "It ignores them for a good reason: anyone can type a header. An app that visitors reach directly must not believe a stranger who claims to have arrived over https, or from someone else's address. Your container on Azure is different: only the edge can reach it, so those headers can only be the edge's. trustProxies says so to Laravel. Then \$request->isSecure() is true, url() and redirect() write https://, the session cookie's Secure flag is honoured, and \$request->ip() is the visitor's address, not the proxy's.",
            'hint' => "In bootstrap/app.php, inside withMiddleware, add:\n\n    \$middleware->trustProxies(at: '*');\n\nRun the curl line again: scheme https, host todo.example.edu, and the link starts https://todo.example.edu. In Homework 11 you add this same line to your team's api/.",
        ],
        5 => [
            'group' => 'If you finish: a new column', 'title' => 'A new column, without emptying the table', 'open' => '/todos?sort=priority',
            'file' => 'a NEW file: database/migrations/2026_11_17_000001_add_priority_to_todos_table.php',
            'goal' => 'The todos table gains a priority column with the default "normal", and every to-do that was there before is still there.',
            'tag' => 'Migrations in production · Twelve-Factor XI. Logs',
            'attack' => "Do drill 3 first. Then log in and click Sort by priority: \"500 Server Error\". Drill 1 is why the page says no more. The reason went to the log (second terminal):\n    docker compose logs app --since 2m\nScroll up to the line that starts production.ERROR and read it: Unknown column 'priority'. The sixty lines under it are the stack trace, the same one drill 1 stopped showing to strangers. A teammate wrote the sorting code and it was deployed. The table it needs was never changed.",
            'idea' => "Code is replaced whole on every deploy. The database is not: it moves forward one migration at a time, and its migrations table remembers which files have run. Two tempting fixes fail on a deployed database. Adding the column to the create_todos migration does nothing, because that file already ran there and never runs again. migrate:fresh would run it, and erase everyone's to-dos (drill 3). What works everywhere is a NEW migration that changes the table that exists, with a default so the rows already there have a value.",
            'hint' => "Make a new file, database/migrations/2026_11_17_000001_add_priority_to_todos_table.php. A migration is a file; the date in its name puts it after the ones that already ran.\n\n<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration\n{\n    public function up(): void\n    {\n        Schema::table('todos', function (Blueprint \$table) {\n            \$table->string('priority')->default('normal');\n        });\n    }\n\n    public function down(): void\n    {\n        Schema::table('todos', function (Blueprint \$table) {\n            \$table->dropColumn('priority');\n        });\n    }\n};\n\nSave, then run it, as the next deploy's start.sh would (second terminal):\n    docker compose exec app php artisan migrate --force\nRun it a second time: \"Nothing to migrate\". Reload the page: sorted, and \"Deploy on Friday\" is still there, at normal priority.",
        ],
        6 => [
            'group' => 'If you finish: the pipeline', 'title' => 'A failing test that deploys anyway', 'open' => '',
            'file' => 'pipeline/deploy.yml',
            'goal' => 'The deploy job waits for the test job, and is skipped when a test fails.',
            'tag' => 'CI/CD · the gate',
            'attack' => "Open pipeline/deploy.yml. It has two jobs, test and deploy. Predict before you read on: a push breaks a test. What happens to the live site?\n\nGitHub starts every job in a workflow at the same moment unless one says it depends on another. test goes red after a minute. deploy never looked at it, and has already put the broken code in front of users. The tests ran, and protected nothing.",
            'idea' => "needs: turns two jobs that run side by side into a line: deploy does not start until test has finished, and is skipped if test failed. A skipped deploy changes nothing, so the site keeps running the last good version. That one line is the whole \"gate\" in a deployment pipeline. Homework 11's file has it too: needs: [php, web].",
            'hint' => "Under deploy: add one line, indented like runs-on:\n\n      deploy:\n        needs: [test]\n        runs-on: ubuntu-latest",
        ],
    ];

    public function index()
    {
        return view('workshop.index', ['drills' => self::DRILLS]);
    }

    // Drill 1: a page with a bug. It asks for a table that does not exist.
    public function boom()
    {
        return DB::table('invoices')->count();
    }

    // Drill 4: what the app believes about the request it just received, as plain text.
    public function request(Request $request)
    {
        return response(implode("\n", [
            'scheme:   '.$request->getScheme(),
            'host:     '.$request->getHttpHost(),
            'secure:   '.($request->isSecure() ? 'yes' : 'no'),
            'client:   '.$request->ip(),
            'a link:   '.url('/todos'),
            '',
        ]), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
}
