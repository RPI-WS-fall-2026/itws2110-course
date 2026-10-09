<?php

namespace App\Todos;

// The to-dos -- supplied, leave this file alone.
//
// There is no database yet (that is week 9), so the list is written out here. Ask for
// a TodoList in a route or a controller method and Laravel hands you one:
//
//     public function index(TodoList $todos) { ... $todos->all() ... }
//
// The grader swaps this file for one with DIFFERENT to-dos, so a page that has
// "Buy rice" typed into it, instead of reading it from here, will not pass.
class TodoList
{
    private const TODOS = [
        ['id' => 1, 'title' => 'Buy rice',                'done' => false, 'due' => '2026-10-12', 'notes' => 'Brown rice, the two-pound bag.'],
        ['id' => 2, 'title' => 'Soak beans',              'done' => true,  'due' => '2026-10-09', 'notes' => 'Overnight, in the big pot.'],
        ['id' => 3, 'title' => 'Wash apples',             'done' => false, 'due' => '2026-10-10', 'notes' => 'Use the <b>cold</b> tap.'],
        ['id' => 4, 'title' => 'Call Sam & Lee',          'done' => true,  'due' => '2026-10-08', 'notes' => 'Ask who is bringing dessert.'],
        ['id' => 5, 'title' => 'Return library books',    'done' => false, 'due' => '2026-10-15', 'notes' => ''],
        ['id' => 6, 'title' => 'Plan Friday dinner',      'done' => false, 'due' => '2026-10-16', 'notes' => 'Six people. One is vegetarian.'],
    ];

    /** Every to-do, in order. Each is an array with the keys id, title, done, due and notes. */
    public function all(): array
    {
        return self::TODOS;
    }

    /** The to-do with this id, or null when there isn't one. */
    public function find(int $id): ?array
    {
        foreach (self::TODOS as $todo) {
            if ($todo['id'] === $id) {
                return $todo;
            }
        }

        return null;
    }
}
