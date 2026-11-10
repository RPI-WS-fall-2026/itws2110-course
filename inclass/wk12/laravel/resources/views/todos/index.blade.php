{{-- Supplied -- leave it alone. Your list, a search box, and an add form. --}}
<x-layout title="My to-dos">
    <h1>{{ auth()->user()->name }}'s to-dos</h1>

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
                <a href="{{ route('todos.show', ['id' => $todo->id]) }}">{{ $todo->title }}</a>
                <span class="row-actions">
                    <span class="status">#{{ $todo->id }} · {{ $todo->done ? 'done' : 'active' }}</span>
                    <form method="POST" action="{{ route('todos.destroy', ['id' => $todo->id]) }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link">Delete</button>
                    </form>
                </span>
            </li>
        @empty
            <li class="empty">No to-dos{{ $q !== '' ? ' match' : ' yet' }}.</li>
        @endforelse
    </ul>

    <h2>Add a to-do</h2>
    <form method="POST" action="{{ route('todos.store') }}" class="add">
        @csrf
        <input type="text" name="title" placeholder="New to-do" aria-label="New to-do title" required>
        <button type="submit">Add</button>
    </form>
</x-layout>
