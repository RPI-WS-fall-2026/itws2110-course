<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

// Worked answer. Drills 6, 7 and 8. (Drill 4 is in routes/web.php; drill 8's rule is in
// app/Policies/TodoPolicy.php.)
class TodoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // DRILL 7 fixed: the list and the search both start from the user's own rows.
        $todos = $request->user()->todos()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%$q%"))
            ->orderBy('title')
            ->get();

        return view('todos.index', ['todos' => $todos, 'q' => $q]);
    }

    public function store(Request $request)
    {
        $fields = $request->validate(['title' => ['required', 'string', 'max:255']]);

        // The owner comes from the login, never from the form.
        $request->user()->todos()->create($fields);

        return redirect()->route('todos.index');
    }

    public function show(Request $request, int $id)
    {
        return view('todos.show', ['todo' => $this->findTodo($request, $id, 'view')]);
    }

    public function toggle(Request $request, int $id)
    {
        $todo = $this->findTodo($request, $id, 'update');
        $todo->update(['done' => ! $todo->done]);

        return back();
    }

    public function destroy(Request $request, int $id)
    {
        $this->findTodo($request, $id, 'delete')->delete();

        return redirect()->route('todos.index');
    }

    // DRILL 6 fixed it with a scope: search only the logged-in user's rows.
    //     return $request->user()->todos()->findOrFail($id);
    // DRILL 8 replaces that, because the admin may now read any to-do: find it anywhere, then
    // let the policy say yes, 403 or 404. Priya still gets 404 for Alex's to-do (drill 6's
    // check still passes); the admin gets 200 to read it and 403 to change it.
    private function findTodo(Request $request, int $id, string $ability): Todo
    {
        $todo = Todo::findOrFail($id);
        Gate::authorize($ability, $todo);

        return $todo;
    }
}
