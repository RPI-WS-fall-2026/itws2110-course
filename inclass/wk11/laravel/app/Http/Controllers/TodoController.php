<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// The to-do pages. Two drills live in this file and its views:
//   DRILL 1 (SQL injection) -> index() below.
//   DRILL 2 (XSS)           -> resources/views/todos/show.blade.php.
//   DRILL 3 (CSRF)          -> store() below + resources/views/todos/index.blade.php + bootstrap/app.php.
class TodoController extends Controller
{
    // The list, with a search box. The whole app "logs in" as user 1 for this lab.
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            $todos = Todo::orderBy('title')->get();
        } else {
            // DRILL 1: the search text is pasted straight into the SQL string. A visitor who
            // types a crafted ' UNION SELECT ...  can make this query return anything in the database,
            // including other users' emails and password hashes. Change it to a bound query:
            // the value must travel separately from the SQL, so it can only ever be a value,
            // never more SQL. See the drills page for the one-line fix.
            $sql = "select id, title, done from todos where title like '%$q%' order by title";
            $todos = collect(DB::select($sql));
        }

        return view('todos.index', ['todos' => $todos, 'q' => $q]);
    }

    public function show(Todo $todo)
    {
        return view('todos.show', ['todo' => $todo]);
    }

    // DRILL 3: add a to-do. The form that posts here (todos/index.blade.php) has no CSRF
    // token, and bootstrap/app.php excludes this route from CSRF checks -- so ANY page on the
    // web can post to it on your behalf. Open /attack to see it happen. The fix is in the
    // form and in bootstrap/app.php, not here.
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        Todo::create([
            'user_id' => 1,
            'title' => $data['title'],
            'done' => false,
        ]);

        return redirect('/todos');
    }
}
