<x-layout title="To-dos">
    <h1>To-dos</h1>

    <form method="GET" action="{{ route('todos.index') }}" class="search">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search titles" aria-label="Search to-dos">
        <button type="submit">Search</button>
        @if ($q !== '')
            <a href="{{ route('todos.index') }}">Clear</a>
        @endif
    </form>

    @if ($q !== '')
        <p class="note">Results for <strong>{{ $q }}</strong>.</p>
    @endif

    <ul class="todos">
        @forelse ($todos as $todo)
            <li class="{{ $todo->done ? 'done' : '' }}">
                <a href="/todos/{{ $todo->id }}">{{ $todo->title }}</a>
                <span class="status">{{ $todo->done ? 'done' : 'active' }}</span>
            </li>
        @empty
            <li class="empty">No to-dos match.</li>
        @endforelse
    </ul>

    <h2>Add a to-do</h2>
    {{-- DRILL 3 fixed: @csrf adds the hidden token the server checks. --}}
    <form method="POST" action="{{ route('todos.store') }}" class="add">
        @csrf
        <input type="text" name="title" placeholder="New to-do" aria-label="New to-do title" required>
        <button type="submit">Add</button>
    </form>
</x-layout>
