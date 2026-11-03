<x-layout title="{{ $todo->title }}">
    <p><a href="{{ route('todos.index') }}">&larr; All to-dos</a></p>
    <h1>{{ $todo->title }}</h1>

    <dl class="facts">
        <dt>Status</dt><dd>{{ $todo->done ? 'done' : 'active' }}</dd>
        <dt>Due</dt><dd>{{ $todo->due?->format('M j, Y') ?? 'no date' }}</dd>
    </dl>

    <h2>Notes</h2>
    {{-- DRILL 2 fixed: {{ }} escapes the note, so a <script> is shown, not run. --}}
    <div class="notes">{{ $todo->notes ?: 'No notes.' }}</div>
</x-layout>
