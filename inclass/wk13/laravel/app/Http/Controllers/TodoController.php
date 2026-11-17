<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

// Supplied -- leave it alone. The to-do pages from week 12, with one new feature: the list
// can be sorted by priority. The code for it is here. The column is not in the table yet
// (drill 5, if you finish).
class TodoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // NEW this week: ?sort=priority orders by a priority column.
        $sort = $request->query('sort') === 'priority' ? 'priority' : 'title';

        // The list and the search both start from the user's own rows (week 12).
        $todos = $request->user()->todos()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%$q%"))
            ->orderBy($sort)
            ->orderBy('title')
            ->get();

        return view('todos.index', ['todos' => $todos, 'q' => $q, 'sort' => $sort]);
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

    // Find the to-do anywhere, then let the policy say yes, 403 or 404 (week 12, drill 8).
    private function findTodo(Request $request, int $id, string $ability): Todo
    {
        $todo = Todo::findOrFail($id);
        Gate::authorize($ability, $todo);

        return $todo;
    }
}
