<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

// Worked answer. Drills 6 and 7. (Drill 4 is in routes/web.php.)
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
        return view('todos.show', ['todo' => $this->findTodo($request, $id)]);
    }

    public function toggle(Request $request, int $id)
    {
        $todo = $this->findTodo($request, $id);
        $todo->update(['done' => ! $todo->done]);

        return back();
    }

    public function destroy(Request $request, int $id)
    {
        $this->findTodo($request, $id)->delete();

        return redirect()->route('todos.index');
    }

    // DRILL 6 fixed: search only the logged-in user's rows. Someone else's id is not found: 404.
    private function findTodo(Request $request, int $id): Todo
    {
        return $request->user()->todos()->findOrFail($id);
    }
}
