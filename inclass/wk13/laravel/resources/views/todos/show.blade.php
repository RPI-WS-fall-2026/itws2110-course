{{-- Supplied -- leave it alone. One to-do: whichever one findTodo() returned. --}}
<x-layout :title="$todo->title">
    <p><a href="{{ route('todos.index') }}">&larr; My to-dos</a></p>
    <h1>{{ $todo->title }}</h1>

    <dl class="facts">
        <dt>Status</dt><dd>{{ $todo->done ? 'done' : 'active' }}</dd>
        <dt>Due</dt><dd>{{ $todo->due?->format('M j, Y') ?? 'no date' }}</dd>
        <dt>Owner</dt><dd>{{ $todo->user->name }}</dd>
    </dl>

    <h2>Notes</h2>
    <div class="notes">{{ $todo->notes ?: 'No notes.' }}</div>

    <div class="actions">
        <form method="POST" action="{{ route('todos.toggle', ['id' => $todo->id]) }}" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit">Mark {{ $todo->done ? 'not done' : 'done' }}</button>
        </form>
        <form method="POST" action="{{ route('todos.destroy', ['id' => $todo->id]) }}" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit">Delete</button>
        </form>
    </div>
</x-layout>
