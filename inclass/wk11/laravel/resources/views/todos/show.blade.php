<x-layout title="{{ $todo->title }}">
    <p><a href="{{ route('todos.index') }}">&larr; All to-dos</a></p>
    <h1>{{ $todo->title }}</h1>

    <dl class="facts">
        <dt>Status</dt><dd>{{ $todo->done ? 'done' : 'active' }}</dd>
        <dt>Due</dt><dd>{{ $todo->due?->format('M j, Y') ?? 'no date' }}</dd>
    </dl>

    <h2>Notes</h2>
    {{-- DRILL 2: {!! !!} prints the note as raw HTML, so a <script> in it runs. Change it to
         {{ $todo->notes }}, which escapes the text so the browser shows it instead. --}}
    <div class="notes">{!! $todo->notes ?: 'No notes.' !!}</div>
</x-layout>
