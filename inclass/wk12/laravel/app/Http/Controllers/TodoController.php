<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

// Your to-do list. Every route that reaches this controller is meant to be behind the login
// (routes/web.php), so $request->user() is the person who is logged in. Four drills:
//   DRILL 4 (logged out)         -> routes/web.php: one route is outside the auth group
//   DRILL 6 (someone else's)     -> findTodo() at the bottom
//   DRILL 7 (the search)         -> index()
//   DRILL 8 (a policy)           -> findTodo() again, and app/Policies/TodoPolicy.php
class TodoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            // Your list: $request->user()->todos() can only find rows with your user_id.
            $todos = $request->user()->todos()->orderBy('title')->get();
        } else {
            // DRILL 7: the search was added later, and it starts from Todo:: -- every row in
            // the table, everyone's. Start it from $request->user()->todos() like the list.
            $todos = Todo::where('title', 'like', "%$q%")->orderBy('title')->get();
        }

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

    // The one place show(), toggle() and destroy() look a to-do up. $ability says what the
    // caller is about to do with it: 'view', 'update' or 'delete'. Drill 6 does not need it;
    // drill 8 does.
    //
    // DRILL 6: Todo::findOrFail($id) searches the whole table, so any logged-in user who
    // types /todos/2 gets to-do 2, whoever owns it. Search only your own rows instead:
    //     return $request->user()->todos()->findOrFail($id);
    // Someone else's id is then "not found" -- a 404, as if it did not exist.
    //
    // DRILL 8 (after drill 6): the admin must be able to read any to-do, which "only your own
    // rows" cannot allow. Find it in the whole table again, then ask the policy:
    //     $todo = Todo::findOrFail($id);
    //     Gate::authorize($ability, $todo);   // TodoPolicy::view/update/delete; 403 or 404 if no
    //     return $todo;
    private function findTodo(Request $request, int $id, string $ability): Todo
    {
        return Todo::findOrFail($id);
    }
}
