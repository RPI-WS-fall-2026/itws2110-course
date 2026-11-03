<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

// Worked answer. Drills 1 and 3 live here.
class TodoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        // DRILL 1 fixed: where() sends the value as a bound parameter, separate from the SQL,
        // so it can only ever be text to match -- never more SQL.
        $todos = Todo::query()
            ->when($q !== '', fn ($query) => $query->where('title', 'like', "%$q%"))
            ->orderBy('title')
            ->get();

        return view('todos.index', ['todos' => $todos, 'q' => $q]);
    }

    public function show(Todo $todo)
    {
        return view('todos.show', ['todo' => $todo]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);

        Todo::create(['user_id' => 1, 'title' => $data['title'], 'done' => false]);

        return redirect('/todos');
    }
}
