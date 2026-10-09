{{-- The list of to-dos. Supplied and working; Parts 3 and 4 add to it.
     The route (later, your controller) passes in $todos: an array of to-dos, each an
     array with the keys id, title, done, due and notes. --}}
<x-layout title="To-dos">
    <h1>To-dos</h1>

    {{-- TODO Part 3: <p>Showing N of M</p>, then the three filter links --
         All, Active, Done -- inside <nav aria-label="Filter"> ... </nav> --}}

    <ul class="todos">
        @foreach ($todos as $todo)
            <li class="{{ $todo['done'] ? 'done' : '' }}">
                {{-- TODO Part 4: make the title a link to this to-do's own page --}}
                <span class="title">{{ $todo['title'] }}</span>
                <span class="status">{{ $todo['done'] ? 'done' : 'active' }}</span>
            </li>
        @endforeach
    </ul>
</x-layout>
